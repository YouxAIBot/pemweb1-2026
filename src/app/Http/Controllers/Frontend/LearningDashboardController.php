<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\DashboardMenu;
use App\Models\DashboardSetting;
use App\Models\GameMode;
use App\Models\LearningLanguage;
use App\Models\LearningLevel;
use App\Models\LearningPart;
use App\Models\TournamentAttempt;
use App\Models\User;
use App\Models\UserLearningProfile;
use App\Models\UserLevelProgress;
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

    public function onboarding(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $profile = $user->learningProfile;

        if ($profile?->onboarding_completed_at) {
            return redirect()->route('dashboard');
        }

        return view('frontend.learning.onboarding', [
            'setting' => $this->setting(),
            'languages' => $this->languages(),
            'abilityOptions' => $this->abilityOptions(),
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

        $language = LearningLanguage::query()->active()->findOrFail($data['learning_language_id']);
        $startMap = [
            'beginner' => 1,
            'intermediate' => 3,
            'master' => 5,
        ];

        $targetIndex = $startMap[$data['ability_level']] ?? 1;
        $part = $language->parts()->active()->orderBy('sort_order')->first();
        $level = null;

        if ($part) {
            $level = $part->levels()
                ->active()
                ->orderBy('sort_order')
                ->skip(max($targetIndex - 1, 0))
                ->first()
                ?? $part->levels()->active()->orderByDesc('sort_order')->first();
        }

        $profile = UserLearningProfile::updateOrCreate([
            'user_id' => $request->user()->id,
        ], [
            'learning_language_id' => $language->id,
            'current_part_id' => $part?->id,
            'current_level_id' => $level?->id,
            'ability_level' => $data['ability_level'],
            'start_level_number' => $targetIndex,
            'start_part_number' => 1,
            'onboarding_completed_at' => now(),
        ]);

        if ($level) {
            $this->syncProgressForUser($request->user()->id, $language, $level);
        }

        app(DailyMissionProgressService::class)->missionsForUser($request->user());

        return redirect()
            ->route('dashboard')
            ->with('learning_success', 'Petualangan belajar berhasil disiapkan.');
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
            'parts' => $parts,
            'progressByLevel' => $progressByLevel,
            'menus' => $this->menus(),
            'missions' => $this->missions($user),
            'friends' => $this->friends($user),
        ]);
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

            app(DailyMissionProgressService::class)->addProgress($user, 'questions_answered', $questionCount);
            app(DailyMissionProgressService::class)->addProgress($user, 'study_minutes', $studyMinutes);
            app(DailyMissionProgressService::class)->addProgress($user, 'levels_completed', 1);
        }

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
            ->orderBy('sort_order')
            ->get();

        if ($games->isEmpty()) {
            $games = collect([
                new GameMode([
                    'key' => 'tournament',
                    'title' => 'Turnamen',
                    'subtitle' => 'Challenge cepat',
                    'description' => 'Jawab soal acak, kumpulkan skor, dan naik leaderboard.',
                    'icon_label' => '⚡',
                    'route_name' => 'learning.tournament',
                    'button_label' => 'Mulai Turnamen',
                    'status' => 'active',
                    'is_active' => true,
                ]),
            ]);
        }

        $leaderboard = TournamentAttempt::query()
            ->with('user')
            ->where('learning_language_id', $profile->learning_language_id)
            ->orderByDesc('score')
            ->orderBy('duration_seconds')
            ->latest()
            ->take(5)
            ->get();

        return view('frontend.learning.games', [
            'setting' => $this->setting(),
            'profile' => $profile,
            'games' => $games,
            'leaderboard' => $leaderboard,
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

        $questions = LearningQuestion::query()
            ->active()
            ->whereHas('level.part', function ($query) use ($profile) {
                $query->where('learning_language_id', $profile->learning_language_id);
            })
            ->whereHas('options')
            ->with(['options' => fn ($query) => $query->orderBy('sort_order')])
            ->inRandomOrder()
            ->limit(5)
            ->get();

        $leaderboard = TournamentAttempt::query()
            ->with('user')
            ->where('learning_language_id', $profile->learning_language_id)
            ->orderByDesc('score')
            ->orderBy('duration_seconds')
            ->latest()
            ->take(10)
            ->get();

        $myBest = TournamentAttempt::query()
            ->where('user_id', $user->id)
            ->where('learning_language_id', $profile->learning_language_id)
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


    private function abilityOptions(): array
    {
        return [
            'beginner' => ['label' => 'Pemula', 'description' => 'Mulai dari Level 1 Bagian 1.', 'target' => 'Level 1 • Bagian 1'],
            'intermediate' => ['label' => 'Paham', 'description' => 'Langsung ke latihan bagian tengah.', 'target' => 'Level 1 • Bagian 3'],
            'master' => ['label' => 'Master', 'description' => 'Masuk ke tantangan lebih lanjut.', 'target' => 'Level 1 • Bagian 5'],
        ];
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
            (object) ['label' => 'Huruf', 'url' => '#', 'icon_label' => 'Aa'],
            (object) ['label' => 'Toko', 'url' => '#', 'icon_label' => '◈'],
            (object) ['label' => 'Misi', 'url' => '#', 'icon_label' => '✓'],
            (object) ['label' => 'Turnamen', 'url' => route('learning.games'), 'icon_label' => '⚡'],
            (object) ['label' => 'Pengaturan', 'url' => '#', 'icon_label' => '⚙'],
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
}
