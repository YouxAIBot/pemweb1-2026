<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\DashboardSetting;
use App\Models\DuelAnswer;
use App\Models\DuelMatchmakingQueue;
use App\Models\DuelPlayer;
use App\Models\DuelPlayerStat;
use App\Models\DuelQuestion;
use App\Models\DuelSession;
use App\Services\Duel\DuelQuestionGeneratorService;
use App\Services\DailyMissionProgressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DuelController extends Controller
{
    private const QUEUE_STALE_SECONDS = 20;
    private const MATCHED_QUEUE_STALE_SECONDS = 45;

    public function lobby(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $profile = $user->learningProfile()->with('language')->first();

        if (! $profile?->onboarding_completed_at) {
            return redirect()->route('learning.onboarding');
        }

        $stats = $this->statsForUserLanguage($user->id, $profile->learning_language_id);
        $leaderboard = DuelPlayerStat::query()
            ->with('user:id,name,email')
            ->where('learning_language_id', $profile->learning_language_id)
            ->orderByDesc('rating')
            ->orderByDesc('wins')
            ->take(12)
            ->get();

        $history = DuelSession::query()
            ->with(['playerOne:id,name,email', 'playerTwo:id,name,email', 'winner:id,name', 'players'])
            ->where('status', 'finished')
            ->where('learning_language_id', $profile->learning_language_id)
            ->where(function ($query) use ($user) {
                $query->where('player_one_id', $user->id)
                    ->orWhere('player_two_id', $user->id);
            })
            ->latest()
            ->take(8)
            ->get();

        return view('frontend.learning.duel.lobby', [
            'setting' => $this->setting(),
            'profile' => $profile,
            'stats' => $stats,
            'leaderboard' => $leaderboard,
            'history' => $history,
        ]);
    }

    public function history(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $profile = $user->learningProfile()->with('language')->first();

        if (! $profile?->onboarding_completed_at) {
            return redirect()->route('learning.onboarding');
        }

        $stats = $this->statsForUserLanguage($user->id, $profile->learning_language_id);
        $leaderboard = DuelPlayerStat::query()
            ->with('user:id,name,email')
            ->where('learning_language_id', $profile->learning_language_id)
            ->orderByDesc('rating')
            ->orderByDesc('wins')
            ->take(25)
            ->get();

        $matches = DuelSession::query()
            ->with(['playerOne:id,name,email', 'playerTwo:id,name,email', 'winner:id,name', 'players'])
            ->where('status', 'finished')
            ->where('learning_language_id', $profile->learning_language_id)
            ->where(function ($query) use ($user) {
                $query->where('player_one_id', $user->id)
                    ->orWhere('player_two_id', $user->id);
            })
            ->latest('ended_at')
            ->paginate(12);

        return view('frontend.learning.duel.history', [
            'setting' => $this->setting(),
            'profile' => $profile,
            'stats' => $stats,
            'leaderboard' => $leaderboard,
            'matches' => $matches,
        ]);
    }

    public function findMatch(Request $request, DuelQuestionGeneratorService $generator): JsonResponse
    {
        $user = $request->user();
        $profile = $user->learningProfile()->with('language')->first();

        if (! $profile?->onboarding_completed_at) {
            return response()->json(['message' => 'Onboarding belum selesai.'], 422);
        }

        $data = $request->validate([
            'difficulty' => ['nullable', 'in:easy,normal,hard'],
        ]);

        $difficulty = $data['difficulty'] ?? 'normal';
        $languageId = $profile->learning_language_id;

        $session = DB::transaction(function () use ($user, $languageId, $difficulty, $generator) {
            $matchedQueue = DuelMatchmakingQueue::query()
                ->where('user_id', $user->id)
                ->where('status', 'matched')
                ->whereNotNull('duel_session_id')
                ->where('updated_at', '>=', now()->subSeconds(self::MATCHED_QUEUE_STALE_SECONDS))
                ->latest()
                ->lockForUpdate()
                ->first();

            if ($matchedQueue) {
                $matchedSession = DuelSession::query()
                    ->whereKey($matchedQueue->duel_session_id)
                    ->where('status', '!=', 'finished')
                    ->first();

                if ($matchedSession) {
                    return $matchedSession;
                }
            }

            DuelMatchmakingQueue::query()
                ->where('status', 'waiting')
                ->where('updated_at', '<', now()->subSeconds(self::QUEUE_STALE_SECONDS))
                ->update(['status' => 'expired']);

            $existingWaiting = DuelMatchmakingQueue::query()
                ->where('user_id', $user->id)
                ->where('status', 'waiting')
                ->latest()
                ->lockForUpdate()
                ->first();

            if (
                $existingWaiting
                && (int) $existingWaiting->learning_language_id === (int) $languageId
                && $existingWaiting->difficulty === $difficulty
                && $existingWaiting->updated_at?->gte(now()->subSeconds(self::QUEUE_STALE_SECONDS))
            ) {
                $existingWaiting->touch();

                return null;
            }

            if ($existingWaiting) {
                $existingWaiting->update(['status' => 'cancelled']);
            }

            $opponentQueue = DuelMatchmakingQueue::query()
                ->where('status', 'waiting')
                ->where('user_id', '!=', $user->id)
                ->where('difficulty', $difficulty)
                ->where('updated_at', '>=', now()->subSeconds(self::QUEUE_STALE_SECONDS))
                ->when($languageId, fn ($query) => $query->where('learning_language_id', $languageId))
                ->oldest()
                ->lockForUpdate()
                ->first();

            if (! $opponentQueue) {
                DuelMatchmakingQueue::create([
                    'user_id' => $user->id,
                    'learning_language_id' => $languageId,
                    'difficulty' => $difficulty,
                    'status' => 'waiting',
                ]);

                return null;
            }

            $session = DuelSession::create([
                'code' => Str::upper(Str::random(8)),
                'learning_language_id' => $languageId,
                'player_one_id' => $opponentQueue->user_id,
                'player_two_id' => $user->id,
                'difficulty' => $difficulty,
                'question_count' => 10,
                'seconds_per_question' => 10,
                'status' => 'preparing',
                'settings' => [
                    'score_base' => 100,
                    'speed_bonus_max' => 50,
                    'generator' => 'local_mixed',
                ],
                'started_at' => now(),
            ]);

            DuelPlayer::create([
                'duel_session_id' => $session->id,
                'user_id' => $opponentQueue->user_id,
                'joined_at' => now(),
            ]);

            DuelPlayer::create([
                'duel_session_id' => $session->id,
                'user_id' => $user->id,
                'joined_at' => now(),
            ]);

            $opponentQueue->update([
                'status' => 'matched',
                'duel_session_id' => $session->id,
                'matched_at' => now(),
            ]);

            DuelMatchmakingQueue::create([
                'user_id' => $user->id,
                'learning_language_id' => $languageId,
                'difficulty' => $difficulty,
                'status' => 'matched',
                'duel_session_id' => $session->id,
                'matched_at' => now(),
            ]);

            $generator->generateForSession($session, 10);

            $session->update(['status' => 'playing']);

            return $session;
        });

        if (! $session) {
            return response()->json([
                'status' => 'waiting',
                'message' => 'Menunggu lawan...',
                'language' => $profile->language?->name,
                'difficulty' => $difficulty,
            ]);
        }

        return response()->json([
            'status' => 'matched',
            'session_id' => $session->id,
            'redirect_url' => route('learning.duel.room', $session),
        ]);
    }

    public function queueStatus(Request $request): JsonResponse
    {
        $queue = DuelMatchmakingQueue::query()
            ->where('user_id', $request->user()->id)
            ->whereIn('status', ['waiting', 'matched'])
            ->latest()
            ->first();

        if (! $queue) {
            return response()->json(['status' => 'idle']);
        }

        if ($queue->status === 'matched' && $queue->duel_session_id) {
            return response()->json([
                'status' => 'matched',
                'session_id' => $queue->duel_session_id,
                'redirect_url' => route('learning.duel.room', $queue->duel_session_id),
            ]);
        }

        if ($queue->updated_at?->lt(now()->subSeconds(self::QUEUE_STALE_SECONDS))) {
            $queue->update(['status' => 'expired']);

            return response()->json(['status' => 'expired']);
        }

        $queue->touch();

        return response()->json([
            'status' => 'waiting',
            'language_id' => $queue->learning_language_id,
            'difficulty' => $queue->difficulty,
        ]);
    }

    public function cancelQueue(Request $request): JsonResponse
    {
        DuelMatchmakingQueue::query()
            ->where('user_id', $request->user()->id)
            ->where('status', 'waiting')
            ->update(['status' => 'cancelled']);

        return response()->json(['status' => 'cancelled']);
    }

    public function room(Request $request, DuelSession $duelSession): View|RedirectResponse
    {
        if (! $duelSession->hasUser($request->user())) {
            abort(403);
        }

        $duelSession->load(['playerOne:id,name,email', 'playerTwo:id,name,email']);

        return view('frontend.learning.duel.room', [
            'setting' => $this->setting(),
            'session' => $duelSession,
        ]);
    }

    public function state(Request $request, DuelSession $duelSession): JsonResponse
    {
        if (! $duelSession->hasUser($request->user())) {
            abort(403);
        }

        $duelSession->load([
            'players.user:id,name,email',
            'questions',
            'answers',
            'winner:id,name',
        ]);

        $players = $duelSession->players->map(function (DuelPlayer $player) use ($duelSession) {
            $answersCount = $duelSession->answers->where('user_id', $player->user_id)->count();

            return [
                'user_id' => $player->user_id,
                'name' => $player->user?->name ?? 'User',
                'initial' => Str::upper(Str::substr($player->user?->name ?? 'U', 0, 1)),
                'score' => $player->score,
                'correct_count' => $player->correct_count,
                'wrong_count' => $player->wrong_count,
                'answers_count' => $answersCount,
                'result' => $player->result,
            ];
        })->values();

        $myAnswers = $duelSession->answers
            ->where('user_id', $request->user()->id)
            ->mapWithKeys(fn (DuelAnswer $answer) => [
                $answer->duel_question_id => [
                    'selected_answer' => $answer->selected_answer,
                    'is_correct' => $answer->is_correct,
                    'score_awarded' => $answer->score_awarded,
                ],
            ]);

        return response()->json([
            'session' => [
                'id' => $duelSession->id,
                'code' => $duelSession->code,
                'status' => $duelSession->status,
                'difficulty' => $duelSession->difficulty,
                'question_count' => $duelSession->question_count,
                'seconds_per_question' => $duelSession->seconds_per_question,
                'winner_user_id' => $duelSession->winner_user_id,
                'winner_name' => $duelSession->winner?->name,
            ],
            'current_user_id' => $request->user()->id,
            'players' => $players,
            'questions' => $duelSession->questions->map(fn (DuelQuestion $question) => [
                'id' => $question->id,
                'order' => $question->question_order,
                'type' => $question->question_type,
                'prompt' => $question->prompt,
                'question_text' => $question->question_text,
                'options' => $question->options,
            ])->values(),
            'my_answers' => $myAnswers,
        ]);
    }

    public function answer(Request $request, DuelSession $duelSession): JsonResponse
    {
        if (! $duelSession->hasUser($request->user())) {
            abort(403);
        }

        if ($duelSession->status === 'finished') {
            return response()->json(['message' => 'Duel sudah selesai.'], 422);
        }

        $data = $request->validate([
            'question_id' => ['required', 'exists:duel_questions,id'],
            'selected_answer' => ['nullable', 'string', 'max:255'],
            'answer_time_ms' => ['required', 'integer', 'min:0', 'max:20000'],
        ]);

        $question = DuelQuestion::query()
            ->where('duel_session_id', $duelSession->id)
            ->findOrFail($data['question_id']);

        $answer = DB::transaction(function () use ($request, $duelSession, $question, $data) {
            $existing = DuelAnswer::query()
                ->where('duel_session_id', $duelSession->id)
                ->where('duel_question_id', $question->id)
                ->where('user_id', $request->user()->id)
                ->first();

            if ($existing) {
                return $existing;
            }

            $selected = $data['selected_answer'] ?? null;
            $isCorrect = filled($selected) && trim((string) $selected) === trim((string) $question->correct_answer);
            $elapsedMs = min(max((int) $data['answer_time_ms'], 0), 10000);
            $score = $this->scoreForAnswer($isCorrect, $elapsedMs, (int) $duelSession->seconds_per_question);

            $answer = DuelAnswer::create([
                'duel_session_id' => $duelSession->id,
                'duel_question_id' => $question->id,
                'user_id' => $request->user()->id,
                'selected_answer' => $selected,
                'is_correct' => $isCorrect,
                'score_awarded' => $score,
                'answer_time_ms' => $elapsedMs,
                'answered_at' => now(),
            ]);

            $player = DuelPlayer::query()
                ->where('duel_session_id', $duelSession->id)
                ->where('user_id', $request->user()->id)
                ->first();

            if ($player) {
                $player->increment('score', $score);
                $player->increment($isCorrect ? 'correct_count' : 'wrong_count');
                $player->increment('total_time_ms', $elapsedMs);
            }

            return $answer;
        });

        $this->finishIfReady($duelSession->fresh());

        return response()->json([
            'is_correct' => $answer->is_correct,
            'score_awarded' => $answer->score_awarded,
            'correct_answer' => $question->correct_answer,
            'explanation' => $question->explanation,
        ]);
    }

    public function finish(Request $request, DuelSession $duelSession): JsonResponse
    {
        if (! $duelSession->hasUser($request->user())) {
            abort(403);
        }

        $allowForce = $duelSession->started_at?->lt(now()->subMinutes(5)) ?? false;

        $this->finishIfReady($duelSession, force: $allowForce);

        return $this->state($request, $duelSession->fresh());
    }

    private function scoreForAnswer(bool $isCorrect, int $elapsedMs, int $secondsPerQuestion): int
    {
        if (! $isCorrect) {
            return 0;
        }

        $limitMs = max($secondsPerQuestion, 1) * 1000;
        $remainingMs = max($limitMs - $elapsedMs, 0);
        $speedBonus = (int) min(50, round(($remainingMs / $limitMs) * 50));

        return 100 + $speedBonus;
    }

    private function finishIfReady(DuelSession $session, bool $force = false): void
    {
        $session->loadMissing(['players', 'questions']);

        if ($session->status === 'finished') {
            return;
        }

        $requiredAnswers = $session->questions->count() * $session->players->count();
        $answersCount = DuelAnswer::query()
            ->where('duel_session_id', $session->id)
            ->count();

        if (! $force && $answersCount < $requiredAnswers) {
            return;
        }

        DB::transaction(function () use ($session) {
            $lockedSession = DuelSession::query()->lockForUpdate()->find($session->id);

            if (! $lockedSession || $lockedSession->status === 'finished') {
                return;
            }

            $players = DuelPlayer::query()
                ->where('duel_session_id', $lockedSession->id)
                ->orderByDesc('score')
                ->get();

            if ($players->count() < 2) {
                return;
            }

            $topScore = $players->max('score');
            $winners = $players->where('score', $topScore);
            $winnerUserId = $winners->count() === 1 ? $winners->first()->user_id : null;

            foreach ($players as $player) {
                $result = $winnerUserId === null
                    ? 'draw'
                    : ((int) $player->user_id === (int) $winnerUserId ? 'win' : 'lose');

                $player->update([
                    'result' => $result,
                    'finished_at' => now(),
                ]);

                $this->applyStats($player, $result);
            }

            $lockedSession->update([
                'status' => 'finished',
                'winner_user_id' => $winnerUserId,
                'ended_at' => now(),
            ]);

            foreach ($players as $player) {
                app(DailyMissionProgressService::class)->addProgress($player->user, 'questions_answered', 10);
                app(DailyMissionProgressService::class)->addProgress($player->user, 'study_minutes', 2);
            }
        });
    }

    private function applyStats(DuelPlayer $player, string $result): void
    {
        $languageId = DuelSession::query()->whereKey($player->duel_session_id)->value('learning_language_id');

        $stats = $this->statsForUserLanguage($player->user_id, $languageId);

        $rating = (int) $stats->rating;
        $rating += match ($result) {
            'win' => 25,
            'lose' => -15,
            default => 5,
        };

        $rating = max(100, $rating);

        $stats->matches += 1;
        $stats->wins += $result === 'win' ? 1 : 0;
        $stats->losses += $result === 'lose' ? 1 : 0;
        $stats->draws += $result === 'draw' ? 1 : 0;
        $stats->rating = $rating;
        $stats->rank_label = DuelPlayerStat::rankFromRating($rating);
        $stats->total_score += (int) $player->score;
        $stats->best_score = max((int) $stats->best_score, (int) $player->score);
        $stats->save();
    }

    private function statsForUserLanguage(int $userId, ?int $languageId): DuelPlayerStat
    {
        if ($languageId) {
            $stats = DuelPlayerStat::query()
                ->where('user_id', $userId)
                ->where('learning_language_id', $languageId)
                ->first();

            if ($stats) {
                return $stats;
            }

            $legacyStats = DuelPlayerStat::query()
                ->where('user_id', $userId)
                ->whereNull('learning_language_id')
                ->first();

            if ($legacyStats) {
                $legacyStats->update(['learning_language_id' => $languageId]);

                return $legacyStats;
            }
        }

        return DuelPlayerStat::firstOrCreate([
            'user_id' => $userId,
            'learning_language_id' => $languageId,
        ]);
    }

    private function setting(): DashboardSetting
    {
        return DashboardSetting::query()->first() ?? new DashboardSetting([
            'brand_text' => 'YoLearning',
            'brand_initial' => 'Y',
        ]);
    }
}
