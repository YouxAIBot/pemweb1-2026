<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\DashboardSetting;
use App\Models\QuizRoom;
use App\Models\QuizRoomAnswer;
use App\Models\QuizRoomMember;
use App\Models\QuizRoomOption;
use App\Models\QuizRoomQuestion;
use App\Services\DailyMissionProgressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class QuizRoomController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $profile = $user->learningProfile()->with('language')->first();

        if (! $profile?->onboarding_completed_at) {
            return redirect()->route('learning.onboarding');
        }

        $myRooms = QuizRoom::query()
            ->withCount(['questions', 'members'])
            ->where('owner_id', $user->id)
            ->where('learning_language_id', $profile->learning_language_id)
            ->latest()
            ->take(8)
            ->get();

        $joinedRooms = QuizRoom::query()
            ->with(['owner:id,name'])
            ->withCount(['questions', 'members'])
            ->whereHas('members', fn ($q) => $q->where('user_id', $user->id))
            ->where('learning_language_id', $profile->learning_language_id)
            ->latest()
            ->take(8)
            ->get();

        return view('frontend.learning.quiz.index', [
            'setting' => $this->setting(),
            'profile' => $profile,
            'myRooms' => $myRooms,
            'joinedRooms' => $joinedRooms,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $profile = $request->user()->learningProfile;

        if (! $profile?->onboarding_completed_at) {
            return redirect()->route('learning.onboarding');
        }

        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $room = QuizRoom::create([
            'learning_language_id' => $profile->learning_language_id,
            'owner_id' => $request->user()->id,
            'code' => $this->makeCode(),
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'status' => 'draft',
        ]);

        QuizRoomMember::firstOrCreate([
            'quiz_room_id' => $room->id,
            'user_id' => $request->user()->id,
        ], ['joined_at' => now()]);

        return redirect()->route('learning.quiz.room', $room)->with('learning_success', 'Room berhasil dibuat. Tambahkan soal dulu sebelum mulai.');
    }

    public function join(Request $request): RedirectResponse
    {
        $profile = $request->user()->learningProfile;
        $data = $request->validate(['code' => ['required', 'string', 'max:12']]);

        $room = QuizRoom::query()
            ->where('code', strtoupper(trim($data['code'])))
            ->where('learning_language_id', $profile?->learning_language_id)
            ->first();

        if (! $room) {
            return back()->withErrors(['code' => 'Room tidak ditemukan untuk bahasa yang sedang dipilih.']);
        }

        QuizRoomMember::firstOrCreate([
            'quiz_room_id' => $room->id,
            'user_id' => $request->user()->id,
        ], ['joined_at' => now()]);

        return redirect()->route('learning.quiz.room', $room);
    }

    public function show(Request $request, QuizRoom $room): View|RedirectResponse
    {
        $user = $request->user();
        $profile = $user->learningProfile;

        if ((int) $room->learning_language_id !== (int) $profile?->learning_language_id) {
            return redirect()->route('learning.quiz.index')->with('learning_error', 'Room ini bukan untuk bahasa yang sedang kamu pilih.');
        }

        $member = QuizRoomMember::firstOrCreate([
            'quiz_room_id' => $room->id,
            'user_id' => $user->id,
        ], ['joined_at' => now()]);

        $room->load(['language', 'owner:id,name', 'questions.options', 'members.user:id,name,avatar_url']);

        $answers = QuizRoomAnswer::query()
            ->where('quiz_room_id', $room->id)
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('quiz_room_question_id');

        $currentQuestion = $room->questions->first(fn ($question) => ! $answers->has($question->id));
        $progress = $this->progress($room);

        return view('frontend.learning.quiz.room', [
            'setting' => $this->setting(),
            'room' => $room,
            'member' => $member,
            'isOwner' => $room->isOwner($user),
            'answers' => $answers,
            'currentQuestion' => $currentQuestion,
            'progress' => $progress,
        ]);
    }

    public function addQuestion(Request $request, QuizRoom $room): RedirectResponse
    {
        abort_unless($room->isOwner($request->user()), 403);

        if ($room->status !== 'draft') {
            return back()->with('learning_error', 'Soal hanya bisa ditambah sebelum room dimulai.');
        }

        $data = $request->validate([
            'question_text' => ['required', 'string', 'max:1000'],
            'question_image' => ['nullable', 'image', 'max:3072'],
            'seconds_limit' => ['nullable', 'integer', 'min:5', 'max:120'],
            'options' => ['required', 'array', 'min:2'],
            'options.*.answer_text' => ['nullable', 'string', 'max:500'],
            'options.*.image' => ['nullable', 'image', 'max:3072'],
            'correct_option' => ['required', 'integer', 'min:0'],
        ]);

        $options = $data['options'];
        $correctIndex = (int) $data['correct_option'];

        if (! array_key_exists($correctIndex, $options)) {
            return back()->withErrors(['correct_option' => 'Pilih jawaban benar yang tersedia.'])->withInput();
        }

        $hasFilledOption = false;
        foreach ($options as $index => $option) {
            if (filled($option['answer_text'] ?? null) || $request->hasFile("options.$index.image")) {
                $hasFilledOption = true;
                break;
            }
        }

        if (! $hasFilledOption) {
            return back()->withErrors(['options' => 'Minimal satu jawaban harus berisi teks atau gambar.'])->withInput();
        }

        DB::transaction(function () use ($request, $room, $data, $options, $correctIndex) {
            $question = QuizRoomQuestion::create([
                'quiz_room_id' => $room->id,
                'question_order' => $room->questions()->count() + 1,
                'question_text' => $data['question_text'],
                'image_path' => $request->hasFile('question_image') ? $request->file('question_image')->store('quiz-room/questions', 'public') : null,
                'seconds_limit' => $data['seconds_limit'] ?? 20,
                'points' => 100,
            ]);

            foreach ($options as $index => $option) {
                if (! filled($option['answer_text'] ?? null) && ! $request->hasFile("options.$index.image")) {
                    continue;
                }

                QuizRoomOption::create([
                    'quiz_room_question_id' => $question->id,
                    'answer_text' => $option['answer_text'] ?? null,
                    'image_path' => $request->hasFile("options.$index.image") ? $request->file("options.$index.image")->store('quiz-room/options', 'public') : null,
                    'is_correct' => $index === $correctIndex,
                    'sort_order' => $index + 1,
                ]);
            }
        });

        return back()->with('learning_success', 'Soal berhasil ditambahkan.');
    }

    public function start(Request $request, QuizRoom $room): RedirectResponse
    {
        abort_unless($room->isOwner($request->user()), 403);

        if ($room->questions()->count() < 1) {
            return back()->with('learning_error', 'Tambahkan minimal 1 soal sebelum mulai.');
        }

        $room->update([
            'status' => 'playing',
            'current_question_order' => 1,
            'started_at' => now(),
        ]);

        return back()->with('learning_success', 'Quiz dimulai. Bagikan kode room ke peserta.');
    }

    public function finish(Request $request, QuizRoom $room): RedirectResponse
    {
        abort_unless($room->isOwner($request->user()), 403);

        $ranked = $room->members()->orderByDesc('score')->orderBy('updated_at')->get();
        foreach ($ranked as $index => $member) {
            $member->update([
                'position' => $index + 1,
                'finished_at' => $member->finished_at ?? now(),
            ]);
        }

        $room->update([
            'status' => 'finished',
            'finished_at' => now(),
        ]);

        return back()->with('learning_success', 'Quiz selesai. History pertandingan sudah tersimpan.');
    }

    public function answer(Request $request, QuizRoom $room): JsonResponse
    {
        $user = $request->user();
        $member = QuizRoomMember::firstOrCreate([
            'quiz_room_id' => $room->id,
            'user_id' => $user->id,
        ], ['joined_at' => now()]);

        if ($room->status !== 'playing') {
            return response()->json(['message' => 'Room belum dimulai atau sudah selesai.'], 422);
        }

        $data = $request->validate([
            'question_id' => ['required', 'exists:quiz_room_questions,id'],
            'option_id' => ['nullable', 'exists:quiz_room_options,id'],
            'answer_time_ms' => ['nullable', 'integer', 'min:0', 'max:120000'],
        ]);

        $question = QuizRoomQuestion::query()
            ->where('quiz_room_id', $room->id)
            ->with('options')
            ->findOrFail($data['question_id']);

        $option = $question->options->firstWhere('id', (int) ($data['option_id'] ?? 0));
        $isCorrect = (bool) ($option?->is_correct);
        $limitMs = max((int) $question->seconds_limit, 5) * 1000;
        $elapsedMs = min(max((int) ($data['answer_time_ms'] ?? $limitMs), 0), $limitMs);
        $speedBonus = $isCorrect ? (int) round(max($limitMs - $elapsedMs, 0) / $limitMs * 50) : 0;
        $score = $isCorrect ? ((int) $question->points + $speedBonus) : 0;

        $answer = QuizRoomAnswer::query()
            ->where('quiz_room_question_id', $question->id)
            ->where('user_id', $user->id)
            ->first();

        if (! $answer) {
            $answer = QuizRoomAnswer::create([
                'quiz_room_id' => $room->id,
                'quiz_room_question_id' => $question->id,
                'quiz_room_option_id' => $option?->id,
                'user_id' => $user->id,
                'is_correct' => $isCorrect,
                'score_awarded' => $score,
                'answer_time_ms' => $elapsedMs,
                'answered_at' => now(),
            ]);

            $member->increment('score', $score);
            $member->increment($isCorrect ? 'correct_count' : 'wrong_count');
            app(DailyMissionProgressService::class)->addProgress($user, 'questions_answered', 1);
        }

        return response()->json([
            'is_correct' => $answer->is_correct,
            'score_awarded' => $answer->score_awarded,
            'correct_option_id' => $question->options->firstWhere('is_correct', true)?->id,
            'progress' => $this->progress($room->fresh()),
        ]);
    }

    public function state(Request $request, QuizRoom $room): JsonResponse
    {
        $room->load(['questions.options', 'members.user:id,name']);

        return response()->json([
            'room' => [
                'id' => $room->id,
                'code' => $room->code,
                'status' => $room->status,
                'current_question_order' => $room->current_question_order,
                'questions_count' => $room->questions->count(),
            ],
            'progress' => $this->progress($room),
        ]);
    }

    private function progress(QuizRoom $room): array
    {
        return $room->members()
            ->with('user:id,name')
            ->orderByDesc('score')
            ->orderBy('updated_at')
            ->get()
            ->values()
            ->map(fn (QuizRoomMember $member, int $index) => [
                'position' => $member->position ?? $index + 1,
                'name' => $member->user?->name ?? 'User',
                'score' => $member->score,
                'correct_count' => $member->correct_count,
            ])
            ->all();
    }

    private function makeCode(): string
    {
        do {
            $code = Str::upper(Str::random(6));
        } while (QuizRoom::where('code', $code)->exists());

        return $code;
    }

    private function setting(): DashboardSetting
    {
        return DashboardSetting::query()->first() ?? new DashboardSetting(['brand_text' => 'YoLearning', 'brand_initial' => 'Y']);
    }
}
