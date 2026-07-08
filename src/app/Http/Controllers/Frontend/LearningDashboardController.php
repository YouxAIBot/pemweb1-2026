<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\DashboardMenu;
use App\Models\DashboardSetting;
use App\Models\GameMode;
use App\Models\LanguageLetter;
use App\Models\LearningLanguage;
use App\Models\LearningLevel;
use App\Models\LearningPart;
use App\Models\LearningQuestion;
use App\Models\TournamentAttempt;
use App\Models\User;
use App\Models\UserLearningProfile;
use App\Models\UserLevelProgress;
use App\Models\UserQuestionProgress;
use App\Services\DailyMissionProgressService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class LearningDashboardController extends Controller
{
    public function welcome(Request $request): View
    {
        $profile = $request->user()->learningProfile;
        $targetUrl = $profile?->onboarding_completed_at
            ? route('dashboard')
            : route('learning.onboarding');

        return view('frontend.learning.welcome', [
            'setting' => $this->setting(),
            'targetUrl' => $targetUrl,
            'user' => $request->user(),
        ]);
    }

    public function onboarding(Request $request): View
    {
        $user = $request->user();
        $profile = $user->learningProfile;
        $selectedLanguageIds = $this->selectedLanguageIds($profile);

        return view('frontend.learning.onboarding', [
            'setting' => $this->setting(),
            'languages' => $this->languages(),
            'abilityOptions' => $this->abilityOptions(),
            'profile' => $profile,
            'selectedLanguageIds' => $selectedLanguageIds,
            'activeLanguageId' => $profile?->learning_language_id,
            'isReturningUser' => (bool) $profile?->onboarding_completed_at,
        ]);
    }

    public function storeOnboarding(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'learning_language_id' => ['required', 'exists:learning_languages,id'],
            'ability_level' => ['required', 'in:beginner,intermediate,master'],
        ], [
            'learning_language_id.required' => 'Pilih satu bahasa terlebih dahulu.',
            'ability_level.required' => 'Pilih tingkat kemampuan terlebih dahulu.',
        ]);

        $user = $request->user();
        $language = LearningLanguage::query()->active()->findOrFail($data['learning_language_id']);

        $profile = UserLearningProfile::firstOrCreate([
            'user_id' => $user->id,
        ], [
            'settings' => [],
        ]);

        $alreadyHadLanguage = in_array($language->id, $this->selectedLanguageIds($profile), true);

        if ($profile->learning_language_id) {
            $this->storeCurrentLanguageSnapshot($profile);
        }

        $resolvedState = $this->resolveLanguageState($user, $profile, $language, $data['ability_level']);

        $profile->fill([
            'learning_language_id' => $language->id,
            'current_part_id' => $resolvedState['current_part_id'],
            'current_level_id' => $resolvedState['current_level_id'],
            'ability_level' => $resolvedState['ability_level'],
            'start_level_number' => $resolvedState['start_level_number'],
            'start_part_number' => $resolvedState['start_part_number'],
            'total_xp' => $resolvedState['total_xp'],
            'streak' => $resolvedState['streak'],
            'onboarding_completed_at' => now(),
            'settings' => $this->mergeLanguageSelectionSettings($profile, $language->id),
        ])->save();

        if ($profile->currentLevel && $profile->language) {
            $this->syncProgressForUser($user->id, $profile->language, $profile->currentLevel);
        }

        app(DailyMissionProgressService::class)->missionsForUser($user);

        $message = $alreadyHadLanguage
            ? 'Bahasa aktif berhasil diganti ke ' . $language->name . '.'
            : 'Bahasa ' . $language->name . ' berhasil ditambahkan.';

        return redirect()
            ->route('dashboard')
            ->with('learning_success', $message);
    }

    public function dashboard(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $profile = $user->learningProfile()->with(['language.parts.levels', 'currentPart', 'currentLevel'])->first();

        if (! $profile?->onboarding_completed_at) {
            return redirect()->route('learning.onboarding');
        }

        $this->ensureProgressRecords($profile);

        $language = $profile->language;
        $parts = collect();

        if ($language) {
            $parts = $language->parts()
                ->active()
                ->with(['levels' => fn ($query) => $query->active()->orderBy('sort_order')])
                ->orderBy('sort_order')
                ->get();
        }

        $progressByLevel = $this->progressByLevel($user->id, $parts->pluck('levels')->flatten()->pluck('id')->all());

        return view('frontend.learning.dashboard', [
            'setting' => $this->setting(),
            'profile' => $profile->refresh()->load(['language', 'currentPart', 'currentLevel']),
            'language' => $language,
            'languages' => $this->languages(),
            'parts' => $parts,
            'progressByLevel' => $progressByLevel,
            'menus' => $this->menus(),
            'missions' => $this->missions($user),
            'friends' => $this->friends($user),
            'selectedLanguageIds' => $this->selectedLanguageIds($profile),
        ]);
    }

    public function switchLanguage(Request $request): RedirectResponse
    {
        $user = $request->user();
        $profile = $user->learningProfile;

        if (! $profile?->onboarding_completed_at) {
            return redirect()->route('learning.onboarding');
        }

        $data = $request->validate([
            'learning_language_id' => ['required', 'exists:learning_languages,id'],
        ], [
            'learning_language_id.required' => 'Pilih bahasa terlebih dahulu.',
        ]);

        $language = LearningLanguage::query()->active()->findOrFail($data['learning_language_id']);
        $selectedIds = $this->selectedLanguageIds($profile);

        if (! in_array((int) $language->id, $selectedIds, true)) {
            return redirect()
                ->route('learning.onboarding')
                ->with('learning_error', 'Bahasa ini belum ada di kursusmu. Tambahkan dulu dan pilih tingkat kemampuan.');
        }

        if ((int) $profile->learning_language_id === (int) $language->id) {
            return redirect()->route('dashboard');
        }

        $this->storeCurrentLanguageSnapshot($profile);

        $resolvedState = $this->resolveLanguageState(
            $user,
            $profile->refresh(),
            $language,
            $profile->ability_level ?? 'beginner'
        );

        $profile->fill([
            'learning_language_id' => $language->id,
            'current_part_id' => $resolvedState['current_part_id'],
            'current_level_id' => $resolvedState['current_level_id'],
            'ability_level' => $resolvedState['ability_level'],
            'start_level_number' => $resolvedState['start_level_number'],
            'start_part_number' => $resolvedState['start_part_number'],
            'total_xp' => $resolvedState['total_xp'],
            'streak' => $resolvedState['streak'],
        ])->save();

        if ($profile->currentLevel && $profile->language) {
            $this->syncProgressForUser($user->id, $profile->language, $profile->currentLevel);
        }

        return redirect()
            ->route('dashboard')
            ->with('learning_success', 'Berpindah ke kursus ' . $language->name . '.');
    }

    public function showPart(Request $request, LearningPart $part): View|RedirectResponse
    {
        $user = $request->user();
        $profile = $user->learningProfile;

        if (! $profile?->onboarding_completed_at) {
            return redirect()->route('learning.onboarding');
        }

        if ((int) $part->learning_language_id !== (int) $profile->learning_language_id) {
            return redirect()
                ->route('dashboard')
                ->with('learning_error', 'Bagian ini bukan bagian dari bahasa yang sedang kamu pelajari.');
        }

        $this->ensureProgressRecords($profile);

        $part->load(['language', 'levels' => fn ($query) => $query->active()->withCount('questions')->orderBy('sort_order')]);
        $progressByLevel = $this->progressByLevel($user->id, $part->levels->pluck('id')->all());

        return view('frontend.learning.part-map', [
            'setting' => $this->setting(),
            'profile' => $profile->refresh()->load(['language', 'currentLevel']),
            'part' => $part,
            'levels' => $part->levels,
            'progressByLevel' => $progressByLevel,
            'menus' => $this->menus(),
            'missions' => $this->missions($user),
            'friends' => $this->friends($user),
        ]);
    }

    public function showLevel(Request $request, LearningPart $part, LearningLevel $level): View|RedirectResponse
    {
        $user = $request->user();
        $profile = $user->learningProfile;

        if (! $profile?->onboarding_completed_at) {
            return redirect()->route('learning.onboarding');
        }

        if ((int) $level->learning_part_id !== (int) $part->id || (int) $part->learning_language_id !== (int) $profile->learning_language_id) {
            abort(404);
        }

        $this->ensureProgressRecords($profile);

        $progress = UserLevelProgress::query()
            ->where('user_id', $user->id)
            ->where('learning_level_id', $level->id)
            ->first();

        if (! $progress || $progress->status === 'locked') {
            return redirect()
                ->route('learning.parts.show', $part)
                ->with('learning_error', 'Selesaikan level sebelumnya terlebih dahulu.');
        }

        if ($level->is_premium && ! $user->isPremium()) {
            return redirect()
                ->route('learning.premium')
                ->with('learning_error', 'Level ini khusus premium. Upgrade premium untuk membuka akses tanpa iklan.');
        }

        if ($progress->status === 'available') {
            $progress->update([
                'status' => 'in_progress',
            ]);
        }

        $level->load(['part.language', 'questions' => fn ($query) => $query->active()->with('options')->orderBy('sort_order')]);

        return view('frontend.learning.level-show', [
            'setting' => $this->setting(),
            'profile' => $profile->refresh()->load('language'),
            'part' => $part,
            'level' => $level,
            'levelProgress' => $progress->refresh(),
            'shouldShowAds' => ! $user->isPremium(),
            'entryAd' => $this->adForPlacement('level_entry'),
            'exitAd' => $this->adForPlacement('level_exit'),
            'menus' => $this->menus(),
            'missions' => $this->missions($user),
            'friends' => $this->friends($user),
        ]);
    }

    public function completeLevel(Request $request, LearningPart $part, LearningLevel $level): RedirectResponse
    {
        $user = $request->user();
        $profile = $user->learningProfile;

        if (! $profile?->onboarding_completed_at) {
            return redirect()->route('learning.onboarding');
        }

        if ((int) $level->learning_part_id !== (int) $part->id || (int) $part->learning_language_id !== (int) $profile->learning_language_id) {
            abort(404);
        }

        $this->ensureProgressRecords($profile);

        $progress = UserLevelProgress::query()
            ->where('user_id', $user->id)
            ->where('learning_level_id', $level->id)
            ->first();

        if (! $progress || $progress->status === 'locked') {
            return redirect()
                ->route('learning.parts.show', $part)
                ->with('learning_error', 'Level ini masih terkunci.');
        }

        $data = $request->validate([
            'study_seconds' => ['nullable', 'integer', 'min:0'],
            'correct_count' => ['nullable', 'integer', 'min:0'],
            'total_questions' => ['nullable', 'integer', 'min:1'],
            'question_results' => ['nullable', 'string'],
        ]);

        $wasCompleted = $progress->status === 'completed';
        $questionCount = max((int) ($data['total_questions'] ?? $level->questions()->active()->count()), 1);
        $correctCount = max((int) ($data['correct_count'] ?? $questionCount), 0);
        $studySeconds = max((int) ($data['study_seconds'] ?? 0), 0);
        $studyMinutes = max(1, (int) ceil($studySeconds / 60));
        $score = min(100, (int) round(($correctCount / $questionCount) * 100));

        $progress->update([
            'status' => 'completed',
            'best_score' => max((int) $progress->best_score, $score),
            'attempts' => (int) $progress->attempts + 1,
            'completed_at' => $progress->completed_at ?? now(),
        ]);

        $nextLevel = $this->nextLevelAfter($level);

        if ($nextLevel) {
            $nextProgress = UserLevelProgress::firstOrCreate([
                'user_id' => $user->id,
                'learning_level_id' => $nextLevel->id,
            ], [
                'status' => 'available',
            ]);

            if ($nextProgress->status !== 'completed') {
                $nextProgress->update([
                    'status' => 'available',
                ]);
            }
        }

        if (! $wasCompleted) {
            $profile->forceFill([
                'total_xp' => (int) $profile->total_xp + (int) $level->xp_reward,
                'streak' => max((int) $profile->streak, 1),
                'current_part_id' => $nextLevel?->learning_part_id ?? $part->id,
                'current_level_id' => $nextLevel?->id ?? $level->id,
            ])->save();

            $this->storeCurrentLanguageSnapshot($profile->refresh());

            app(DailyMissionProgressService::class)->addProgress($user, 'questions_answered', $questionCount);
            app(DailyMissionProgressService::class)->addProgress($user, 'study_minutes', $studyMinutes);
            app(DailyMissionProgressService::class)->addProgress($user, 'levels_completed', 1);
        }

        $this->storeQuestionProgress($user->id, (string) ($data['question_results'] ?? ''));

        return redirect()
            ->route('learning.parts.show', $part)
            ->with('learning_success', $nextLevel ? 'Level selesai. Level berikutnya sudah terbuka.' : 'Level selesai. Semua level di bagian ini sudah kamu buka.');
    }

    public function games(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $profile = $user->learningProfile()->with('language')->first();

        if (! $profile?->onboarding_completed_at) {
            return redirect()->route('learning.onboarding');
        }

        $games = GameMode::query()
            ->active()
            ->whereNotIn('key', ['daily_boss'])
            ->orderBy('sort_order')
            ->get();

        if ($games->isEmpty()) {
            $games = collect([
                new GameMode([
                    'key' => 'tournament',
                    'title' => 'Turnamen Cepat',
                    'subtitle' => 'Challenge 5 soal',
                    'description' => 'Jawab 5 soal acak, kumpulkan skor, dan naik leaderboard.',
                    'icon_label' => '⚡',
                    'route_name' => 'learning.tournament',
                    'button_label' => 'Mulai',
                    'status' => 'active',
                    'is_active' => true,
                ]),
            ]);
        }

        return view('frontend.learning.games', [
            'setting' => $this->setting(),
            'profile' => $profile,
            'games' => $games,
            'menus' => $this->menus(),
            'missions' => $this->missions($user),
            'friends' => $this->friends($user),
        ]);
    }

    public function letters(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $profile = $user->learningProfile()->with('language')->first();

        if (! $profile?->onboarding_completed_at) {
            return redirect()->route('learning.onboarding');
        }

        $letters = LanguageLetter::query()
            ->active()
            ->where('learning_language_id', $profile->learning_language_id)
            ->orderBy('sort_order')
            ->get();

        if ($letters->isEmpty()) {
            $letters = collect($this->fallbackLettersFor($profile->language?->name ?? ''));
        }

        return view('frontend.learning.letters', [
            'setting' => $this->setting(),
            'profile' => $profile,
            'letters' => $letters,
            'menus' => $this->menus(),
            'missions' => $this->missions($user),
            'friends' => $this->friends($user),
        ]);
    }

    public function apiGameModes(Request $request)
    {
        $user = $request->user();
        $profile = $user?->learningProfile;

        $games = GameMode::query()
            ->active()
            ->whereNotIn('key', ['daily_boss'])
            ->orderBy('sort_order')
            ->get()
            ->map(function (GameMode $game) {
                $playable = $game->isPlayable() && \Illuminate\Support\Facades\Route::has($game->route_name);

                return [
                    'key' => $game->key,
                    'title' => $game->title,
                    'subtitle' => $game->subtitle,
                    'description' => $game->description,
                    'icon_label' => $game->icon_label,
                    'status' => $game->status,
                    'button_label' => $game->button_label,
                    'url' => $playable ? route($game->route_name) : null,
                    'playable' => $playable,
                ];
            })
            ->values();

        return response()->json([
            'language_id' => $profile?->learning_language_id,
            'games' => $games,
        ]);
    }

    public function apiTournamentLeaderboard(Request $request)
    {
        $profile = $request->user()?->learningProfile;
        $languageId = $profile?->learning_language_id;

        $leaderboard = TournamentAttempt::query()
            ->with('user:id,name')
            ->when($languageId, fn ($query) => $query->where('learning_language_id', $languageId))
            ->where('mode', 'tournament')
            ->orderByDesc('score')
            ->orderBy('duration_seconds')
            ->latest()
            ->take(10)
            ->get()
            ->map(fn (TournamentAttempt $attempt) => [
                'name' => $attempt->user?->name ?? 'User',
                'score' => $attempt->score,
                'correct_count' => $attempt->correct_count,
                'total_questions' => $attempt->total_questions,
                'duration_seconds' => $attempt->duration_seconds,
            ])
            ->values();

        return response()->json([
            'language_id' => $languageId,
            'leaderboard' => $leaderboard,
        ]);
    }

    public function tournament(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $profile = $user->learningProfile()->with('language')->first();

        if (! $profile?->onboarding_completed_at) {
            return redirect()->route('learning.onboarding');
        }

        $questions = $this->randomLearningQuestionsForMode(
            (int) $profile->learning_language_id,
            (int) $user->id,
            'tournament',
            5,
        );

        $leaderboard = TournamentAttempt::query()
            ->with('user')
            ->where('learning_language_id', $profile->learning_language_id)
            ->where('mode', 'tournament')
            ->orderByDesc('score')
            ->orderBy('duration_seconds')
            ->latest()
            ->take(10)
            ->get();

        $myBest = TournamentAttempt::query()
            ->where('user_id', $user->id)
            ->where('learning_language_id', $profile->learning_language_id)
            ->where('mode', 'tournament')
            ->orderByDesc('score')
            ->orderBy('duration_seconds')
            ->first();

        return view('frontend.learning.tournament', [
            'setting' => $this->setting(),
            'profile' => $profile,
            'questions' => $questions,
            'leaderboard' => $leaderboard,
            'myBest' => $myBest,
            'menus' => $this->menus(),
            'missions' => $this->missions($user),
            'friends' => $this->friends($user),
            'result' => session('tournament_result'),
        ]);
    }

    public function videoQuestion(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $profile = $user->learningProfile()->with('language')->first();

        if (! $profile?->onboarding_completed_at) {
            return redirect()->route('learning.onboarding');
        }

        $questions = LearningQuestion::query()
            ->active()
            ->where('type', 'video_question')
            ->whereHas('level.part', function ($query) use ($profile) {
                $query->where('learning_language_id', $profile->learning_language_id);
            })
            ->whereHas('options')
            ->with(['options' => fn ($query) => $query->orderBy('sort_order')])
            ->inRandomOrder()
            ->limit(5)
            ->get();

        $leaderboard = TournamentAttempt::query()
            ->with('user:id,name')
            ->where('learning_language_id', $profile->learning_language_id)
            ->where('mode', 'video_question')
            ->orderByDesc('score')
            ->orderBy('duration_seconds')
            ->latest()
            ->take(10)
            ->get();

        $myBest = TournamentAttempt::query()
            ->where('user_id', $user->id)
            ->where('learning_language_id', $profile->learning_language_id)
            ->where('mode', 'video_question')
            ->orderByDesc('score')
            ->orderBy('duration_seconds')
            ->first();

        return view('frontend.learning.video-question', [
            'setting' => $this->setting(),
            'profile' => $profile,
            'questions' => $questions,
            'leaderboard' => $leaderboard,
            'myBest' => $myBest,
            'result' => session('video_question_result'),
        ]);
    }

    public function submitVideoQuestion(Request $request): RedirectResponse
    {
        $user = $request->user();
        $profile = $user->learningProfile;

        if (! $profile?->onboarding_completed_at) {
            return redirect()->route('learning.onboarding');
        }

        $data = $request->validate([
            'question_ids' => ['required', 'array', 'min:1'],
            'question_ids.*' => ['integer', 'exists:learning_questions,id'],
            'answers' => ['nullable', 'array'],
            'answers.*' => ['nullable', 'integer'],
            'duration_seconds' => ['nullable', 'integer', 'min:0'],
        ]);

        $questionIds = array_map('intval', $data['question_ids']);
        $questions = LearningQuestion::query()
            ->active()
            ->where('type', 'video_question')
            ->whereIn('id', $questionIds)
            ->whereHas('level.part', function ($query) use ($profile) {
                $query->where('learning_language_id', $profile->learning_language_id);
            })
            ->with('options')
            ->get()
            ->keyBy('id');

        $correctCount = 0;
        $answerLog = [];

        foreach ($questionIds as $questionId) {
            $question = $questions->get($questionId);

            if (! $question) {
                continue;
            }

            $selectedOptionId = (int) (($data['answers'][$questionId] ?? 0));
            $correctOption = $question->options->firstWhere('is_correct', true);
            $isCorrect = $correctOption && (int) $correctOption->id === $selectedOptionId;

            if ($isCorrect) {
                $correctCount += 1;
            }

            $answerLog[] = [
                'question_id' => $question->id,
                'selected_option_id' => $selectedOptionId ?: null,
                'correct_option_id' => $correctOption?->id,
                'is_correct' => (bool) $isCorrect,
            ];
        }

        $totalQuestions = max($questions->count(), 1);
        $score = (int) round(($correctCount / $totalQuestions) * 100);
        $durationSeconds = max((int) ($data['duration_seconds'] ?? 0), 0);

        $attempt = TournamentAttempt::create([
            'user_id' => $user->id,
            'learning_language_id' => $profile->learning_language_id,
            'mode' => 'video_question',
            'score' => $score,
            'correct_count' => $correctCount,
            'total_questions' => $totalQuestions,
            'duration_seconds' => $durationSeconds,
            'answers' => $answerLog,
        ]);

        app(DailyMissionProgressService::class)->addProgress($user, 'questions_answered', $totalQuestions);
        app(DailyMissionProgressService::class)->addProgress($user, 'study_minutes', max(1, (int) ceil($durationSeconds / 60)));

        return redirect()
            ->route('learning.video-question')
            ->with('video_question_result', [
                'score' => $attempt->score,
                'correct_count' => $attempt->correct_count,
                'total_questions' => $attempt->total_questions,
                'duration_seconds' => $attempt->duration_seconds,
            ]);
    }

    public function submitTournament(Request $request): RedirectResponse
    {
        $user = $request->user();
        $profile = $user->learningProfile;

        if (! $profile?->onboarding_completed_at) {
            return redirect()->route('learning.onboarding');
        }

        $data = $request->validate([
            'question_ids' => ['required', 'array', 'min:1'],
            'question_ids.*' => ['integer', 'exists:learning_questions,id'],
            'answers' => ['nullable', 'array'],
            'answers.*' => ['nullable', 'integer'],
            'duration_seconds' => ['nullable', 'integer', 'min:0'],
        ]);

        $questionIds = array_map('intval', $data['question_ids']);
        $questions = LearningQuestion::query()
            ->active()
            ->whereIn('id', $questionIds)
            ->whereHas('level.part', function ($query) use ($profile) {
                $query->where('learning_language_id', $profile->learning_language_id);
            })
            ->with('options')
            ->get()
            ->keyBy('id');

        $correctCount = 0;
        $answerLog = [];

        foreach ($questionIds as $questionId) {
            $question = $questions->get($questionId);

            if (! $question) {
                continue;
            }

            $selectedOptionId = (int) (($data['answers'][$questionId] ?? 0));
            $correctOption = $question->options->firstWhere('is_correct', true);
            $isCorrect = $correctOption && (int) $correctOption->id === $selectedOptionId;

            if ($isCorrect) {
                $correctCount += 1;
            }

            $answerLog[] = [
                'question_id' => $question->id,
                'selected_option_id' => $selectedOptionId ?: null,
                'correct_option_id' => $correctOption?->id,
                'is_correct' => (bool) $isCorrect,
            ];
        }

        $totalQuestions = max($questions->count(), 1);
        $score = (int) round(($correctCount / $totalQuestions) * 100);
        $durationSeconds = max((int) ($data['duration_seconds'] ?? 0), 0);

        $attempt = TournamentAttempt::create([
            'user_id' => $user->id,
            'learning_language_id' => $profile->learning_language_id,
            'mode' => 'tournament',
            'score' => $score,
            'correct_count' => $correctCount,
            'total_questions' => $totalQuestions,
            'duration_seconds' => $durationSeconds,
            'answers' => $answerLog,
        ]);

        app(DailyMissionProgressService::class)->addProgress($user, 'questions_answered', $totalQuestions);
        app(DailyMissionProgressService::class)->addProgress($user, 'study_minutes', max(1, (int) ceil($durationSeconds / 60)));

        return redirect()
            ->route('learning.tournament')
            ->with('tournament_result', [
                'score' => $attempt->score,
                'correct_count' => $attempt->correct_count,
                'total_questions' => $attempt->total_questions,
                'duration_seconds' => $attempt->duration_seconds,
            ]);
    }

    private function randomLearningQuestionsForMode(int $languageId, int $userId, string $mode, int $limit)
    {
        $baseQuery = LearningQuestion::query()
            ->active()
            ->whereHas('level.part', function ($query) use ($languageId) {
                $query->where('learning_language_id', $languageId);
            })
            ->whereDoesntHave('level.part', function ($query) {
                $query->where('slug', 'bagian-4-cerita-pendek');
            })
            ->whereHas('options')
            ->with(['options' => fn ($query) => $query->orderBy('sort_order')]);

        $recentQuestionIds = $this->recentAttemptQuestionIds($userId, $languageId, $mode);

        $questions = (clone $baseQuery)
            ->when($recentQuestionIds !== [], fn ($query) => $query->whereNotIn('id', $recentQuestionIds))
            ->inRandomOrder()
            ->limit($limit)
            ->get();

        if ($questions->count() < $limit) {
            $fallbackQuestions = (clone $baseQuery)
                ->whereNotIn('id', $questions->pluck('id')->all())
                ->inRandomOrder()
                ->limit($limit - $questions->count())
                ->get();

            $questions = $questions->concat($fallbackQuestions)->values();
        }

        return $this->shuffleQuestionOptions($questions);
    }

    private function recentAttemptQuestionIds(int $userId, int $languageId, string $mode): array
    {
        return TournamentAttempt::query()
            ->where('user_id', $userId)
            ->where('learning_language_id', $languageId)
            ->where('mode', $mode)
            ->latest()
            ->take(3)
            ->get()
            ->flatMap(fn (TournamentAttempt $attempt) => collect($attempt->answers ?? [])->pluck('question_id'))
            ->filter()
            ->map(fn ($questionId) => (int) $questionId)
            ->unique()
            ->values()
            ->all();
    }

    private function shuffleQuestionOptions($questions)
    {
        return $questions->map(function (LearningQuestion $question) {
            $question->setRelation('options', $question->options->shuffle()->values());

            return $question;
        });
    }

    private function abilityOptions(): array
    {
        return [
            'beginner' => ['label' => 'Pemula', 'description' => 'Mulai dari Level 1 Bagian 1.', 'target' => 'Level 1 • Bagian 1'],
            'intermediate' => ['label' => 'Paham', 'description' => 'Langsung ke latihan bagian tengah.', 'target' => 'Level 1 • Bagian 3'],
            'master' => ['label' => 'Master', 'description' => 'Masuk ke tantangan lebih lanjut.', 'target' => 'Level 1 • Bagian 5'],
        ];
    }

    private function fallbackLettersFor(string $languageName): array
    {
        $name = str($languageName)->lower()->toString();

        if (str_contains($name, 'mandarin') || str_contains($name, 'chinese')) {
            return [
                (object) ['symbol' => '你', 'reading' => 'ni', 'example_word' => '你好', 'example_translation' => 'halo', 'audio_url' => null, 'audio_path' => null],
                (object) ['symbol' => '好', 'reading' => 'hao', 'example_word' => '很好', 'example_translation' => 'sangat baik', 'audio_url' => null, 'audio_path' => null],
                (object) ['symbol' => '人', 'reading' => 'ren', 'example_word' => '中国人', 'example_translation' => 'orang China', 'audio_url' => null, 'audio_path' => null],
                (object) ['symbol' => '口', 'reading' => 'kou', 'example_word' => '口语', 'example_translation' => 'bahasa lisan', 'audio_url' => null, 'audio_path' => null],
                (object) ['symbol' => '日', 'reading' => 'ri', 'example_word' => '日子', 'example_translation' => 'hari', 'audio_url' => null, 'audio_path' => null],
                (object) ['symbol' => '月', 'reading' => 'yue', 'example_word' => '月亮', 'example_translation' => 'bulan', 'audio_url' => null, 'audio_path' => null],
            ];
        }

        return collect(range('A', 'Z'))
            ->map(fn (string $letter) => (object) [
                'symbol' => $letter,
                'reading' => strtolower($letter),
                'example_word' => null,
                'example_translation' => null,
                'audio_url' => null,
                'audio_path' => null,
            ])
            ->all();
    }

    private function setting(): DashboardSetting
    {
        try {
            if (Schema::hasTable('dashboard_settings')) {
                return DashboardSetting::current();
            }
        } catch (\Throwable) {
            // Keep page usable before migrations run.
        }

        return DashboardSetting::current();
    }

    private function languages()
    {
        try {
            if (Schema::hasTable('learning_languages')) {
                $languages = LearningLanguage::query()->active()->orderBy('sort_order')->get();

                if ($languages->isNotEmpty()) {
                    return $languages;
                }
            }
        } catch (\Throwable) {
            // Fallback below.
        }

        return collect([
            new LearningLanguage(['id' => 1, 'name' => 'Inggris', 'slug' => 'inggris', 'native_name' => 'Hello', 'flag_label' => 'EN', 'description' => 'Grammar, listening, dan real-case practice.']),
            new LearningLanguage(['id' => 2, 'name' => 'Mandarin', 'slug' => 'mandarin', 'native_name' => '你好', 'flag_label' => 'CN', 'description' => 'Percakapan dasar dan listening pemula.']),
        ]);
    }

    private function menus()
    {
        try {
            if (Schema::hasTable('dashboard_menus')) {
                $menus = DashboardMenu::query()->active()->orderBy('sort_order')->get();

                if ($menus->isNotEmpty()) {
                    return $menus;
                }
            }
        } catch (\Throwable) {
            // fallback below
        }

        return collect([
            (object) ['label' => 'Bahasa', 'url' => route('dashboard'), 'icon_label' => '文'],
            (object) ['label' => 'Huruf', 'url' => route('learning.letters'), 'icon_label' => 'Aa'],
            (object) ['label' => 'Toko', 'url' => route('learning.store'), 'icon_label' => '◈'],
            (object) ['label' => 'Misi', 'url' => '#', 'icon_label' => '✓'],
            (object) ['label' => 'Turnamen', 'url' => route('learning.games'), 'icon_label' => '⚡'],
            (object) ['label' => 'Pengaturan', 'url' => route('learning.settings'), 'icon_label' => 'S'],
        ]);
    }

    private function missions(User $user)
    {
        try {
            return app(DailyMissionProgressService::class)->missionsForUser($user);
        } catch (\Throwable) {
            return collect([
                (object) ['title' => 'Kerjakan 5 soal', 'mission_type' => 'questions_answered', 'target' => 5, 'progress_value' => 0, 'unit_label' => 'soal', 'is_completed' => false],
                (object) ['title' => 'Belajar 10 menit', 'mission_type' => 'study_minutes', 'target' => 10, 'progress_value' => 0, 'unit_label' => 'menit', 'is_completed' => false],
                (object) ['title' => 'Kerjakan 20 soal', 'mission_type' => 'questions_answered', 'target' => 20, 'progress_value' => 0, 'unit_label' => 'soal', 'is_completed' => false],
            ]);
        }
    }

    private function friends(User $user)
    {
        try {
            return User::query()
                ->whereKeyNot($user->id)
                ->latest()
                ->take(6)
                ->get();
        } catch (\Throwable) {
            return collect();
        }
    }

    private function adForPlacement(string $placement): ?Ad
    {
        try {
            return Ad::query()
                ->activeForPlacement($placement)
                ->orderBy('sort_order')
                ->inRandomOrder()
                ->first();
        } catch (\Throwable) {
            return null;
        }
    }

    private function storeQuestionProgress(int $userId, string $payload): void
    {
        if (blank($payload)) {
            return;
        }

        $items = json_decode($payload, true);

        if (! is_array($items)) {
            return;
        }

        foreach ($items as $item) {
            $questionId = (int) ($item['question_id'] ?? 0);

            if ($questionId <= 0) {
                continue;
            }

            $isCorrect = (bool) ($item['is_correct'] ?? false);

            UserQuestionProgress::updateOrCreate([
                'user_id' => $userId,
                'learning_question_id' => $questionId,
            ], [
                'is_correct' => $isCorrect,
                'selected_answer' => isset($item['selected_answer']) ? (string) $item['selected_answer'] : null,
                'attempts' => max((int) ($item['attempts'] ?? 1), 1),
                'answered_at' => now(),
            ]);
        }
    }

    private function progressByLevel(int $userId, array $levelIds)
    {
        if (empty($levelIds)) {
            return collect();
        }

        return UserLevelProgress::query()
            ->where('user_id', $userId)
            ->whereIn('learning_level_id', $levelIds)
            ->get()
            ->keyBy('learning_level_id');
    }

    private function ensureProgressRecords(UserLearningProfile $profile): void
    {
        if (! $profile->language || ! $profile->currentLevel) {
            return;
        }

        $this->syncProgressForUser($profile->user_id, $profile->language, $profile->currentLevel);
    }

    private function syncProgressForUser(int $userId, LearningLanguage $language, LearningLevel $currentLevel): void
    {
        $language->load(['parts' => fn ($query) => $query->active()->orderBy('sort_order'), 'parts.levels' => fn ($query) => $query->active()->orderBy('sort_order')]);
        $currentLevel->loadMissing('part');

        foreach ($language->parts as $part) {
            foreach ($part->levels as $level) {
                $existing = UserLevelProgress::query()
                    ->where('user_id', $userId)
                    ->where('learning_level_id', $level->id)
                    ->first();

                if ($existing?->status === 'completed') {
                    continue;
                }

                $status = 'locked';

                if ((int) $level->id === (int) $currentLevel->id) {
                    $status = $existing?->status === 'in_progress' ? 'in_progress' : 'available';
                }

                $isBeforeCurrentPart = (int) $part->sort_order < (int) $currentLevel->part->sort_order;
                $isBeforeCurrentLevel = (int) $part->id === (int) $currentLevel->learning_part_id && (int) $level->sort_order < (int) $currentLevel->sort_order;

                if ($isBeforeCurrentPart || $isBeforeCurrentLevel) {
                    $status = 'completed';
                }

                UserLevelProgress::updateOrCreate([
                    'user_id' => $userId,
                    'learning_level_id' => $level->id,
                ], [
                    'status' => $status,
                    'best_score' => $status === 'completed' ? 100 : (int) ($existing?->best_score ?? 0),
                    'completed_at' => $status === 'completed' ? ($existing?->completed_at ?? now()) : $existing?->completed_at,
                ]);
            }
        }
    }

    private function nextLevelAfter(LearningLevel $level): ?LearningLevel
    {
        $level->loadMissing('part.language');

        $nextInPart = LearningLevel::query()
            ->active()
            ->where('learning_part_id', $level->learning_part_id)
            ->where('sort_order', '>', $level->sort_order)
            ->orderBy('sort_order')
            ->first();

        if ($nextInPart) {
            return $nextInPart;
        }

        $nextPart = LearningPart::query()
            ->active()
            ->where('learning_language_id', $level->part->learning_language_id)
            ->where('sort_order', '>', $level->part->sort_order)
            ->orderBy('sort_order')
            ->first();

        return $nextPart?->levels()
            ->active()
            ->orderBy('sort_order')
            ->first();
    }

    private function selectedLanguageIds(?UserLearningProfile $profile): array
    {
        if (! $profile) {
            return [];
        }

        $ids = collect(data_get($profile->settings, 'enabled_languages', []))
            ->merge([$profile->learning_language_id])
            ->filter()
            ->map(fn ($value) => (int) $value)
            ->unique()
            ->values()
            ->all();

        return $ids;
    }

    private function mergeLanguageSelectionSettings(UserLearningProfile $profile, int $languageId): array
    {
        $settings = $profile->settings ?? [];
        $enabled = collect($settings['enabled_languages'] ?? [])
            ->merge([$languageId])
            ->filter()
            ->map(fn ($value) => (int) $value)
            ->unique()
            ->values()
            ->all();

        $settings['enabled_languages'] = $enabled;

        return $settings;
    }

    private function storeCurrentLanguageSnapshot(UserLearningProfile $profile): void
    {
        if (! $profile->learning_language_id) {
            return;
        }

        $settings = $profile->settings ?? [];
        $settings['language_profiles'] = $settings['language_profiles'] ?? [];
        $settings['language_profiles'][(string) $profile->learning_language_id] = [
            'current_part_id' => $profile->current_part_id,
            'current_level_id' => $profile->current_level_id,
            'ability_level' => $profile->ability_level,
            'start_level_number' => $profile->start_level_number,
            'start_part_number' => $profile->start_part_number,
            'total_xp' => $profile->total_xp,
            'streak' => $profile->streak,
            'onboarding_completed_at' => $profile->onboarding_completed_at?->toISOString(),
        ];

        $profile->forceFill([
            'settings' => $settings,
        ])->save();
    }

    private function resolveLanguageState(User $user, UserLearningProfile $profile, LearningLanguage $language, string $abilityLevel): array
    {
        $snapshot = data_get($profile->settings, 'language_profiles.' . $language->id);

        $part = null;
        $level = null;

        if ($snapshot) {
            $part = LearningPart::query()
                ->where('learning_language_id', $language->id)
                ->whereKey($snapshot['current_part_id'] ?? null)
                ->first();

            $level = LearningLevel::query()
                ->whereKey($snapshot['current_level_id'] ?? null)
                ->whereHas('part', fn ($query) => $query->where('learning_language_id', $language->id))
                ->first();

            if (! $part && $level) {
                $part = $level->part;
            }
        }

        if (! $part || ! $level) {
            $startMap = [
                'beginner' => 1,
                'intermediate' => 3,
                'master' => 5,
            ];

            $targetIndex = $snapshot['start_level_number'] ?? ($startMap[$abilityLevel] ?? 1);
            $part = $language->parts()->active()->orderBy('sort_order')->first();

            if ($part) {
                $level = $part->levels()
                    ->active()
                    ->orderBy('sort_order')
                    ->skip(max($targetIndex - 1, 0))
                    ->first()
                    ?? $part->levels()->active()->orderByDesc('sort_order')->first();
            }

            $existingCompleted = UserLevelProgress::query()
                ->where('user_id', $user->id)
                ->whereHas('level.part', fn ($query) => $query->where('learning_language_id', $language->id))
                ->where('status', 'completed')
                ->with('level')
                ->get()
                ->sortByDesc(fn ($progress) => $progress->level?->sort_order ?? 0)
                ->first();

            if ($existingCompleted?->level) {
                $level = $this->nextLevelAfter($existingCompleted->level) ?? $existingCompleted->level;
                $part = $level->part;
            }

            return [
                'current_part_id' => $part?->id,
                'current_level_id' => $level?->id,
                'ability_level' => $snapshot['ability_level'] ?? $abilityLevel,
                'start_level_number' => $targetIndex,
                'start_part_number' => $snapshot['start_part_number'] ?? 1,
                'total_xp' => (int) ($snapshot['total_xp'] ?? 0),
                'streak' => (int) ($snapshot['streak'] ?? 0),
            ];
        }

        return [
            'current_part_id' => $part?->id,
            'current_level_id' => $level?->id,
            'ability_level' => $snapshot['ability_level'] ?? $abilityLevel,
            'start_level_number' => (int) ($snapshot['start_level_number'] ?? 1),
            'start_part_number' => (int) ($snapshot['start_part_number'] ?? 1),
            'total_xp' => (int) ($snapshot['total_xp'] ?? 0),
            'streak' => (int) ($snapshot['streak'] ?? 0),
        ];
    }
}
