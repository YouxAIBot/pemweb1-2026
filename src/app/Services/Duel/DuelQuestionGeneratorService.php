<?php

namespace App\Services\Duel;

use App\Models\DuelQuestion;
use App\Models\DuelSession;
use App\Models\LearningQuestion;
use Illuminate\Support\Collection;

class DuelQuestionGeneratorService
{
    public function generateForSession(DuelSession $session, int $count = 10): void
    {
        if ($session->questions()->exists()) {
            return;
        }

        $pool = $this->learningQuestionPool($session, $count);

        if ($pool->count() < $count) {
            $pool = $pool
                ->concat(collect($this->fallbackQuestionPool($session->difficulty))->shuffle())
                ->values();
        }

        if ($pool->isEmpty()) {
            return;
        }

        for ($i = 1; $i <= $count; $i++) {
            $item = $pool[($i - 1) % $pool->count()];
            $options = collect($item['options'])->shuffle()->values()->all();

            DuelQuestion::create([
                'duel_session_id' => $session->id,
                'question_order' => $i,
                'question_type' => $item['type'],
                'prompt' => $item['prompt'],
                'question_text' => $item['question'],
                'options' => $options,
                'correct_answer' => $item['answer'],
                'explanation' => $item['explanation'] ?? null,
                'difficulty' => $session->difficulty,
                'source' => $item['source'] ?? 'learning_bank',
            ]);
        }
    }

    private function learningQuestionPool(DuelSession $session, int $count): Collection
    {
        if (! $session->learning_language_id) {
            return collect();
        }

        $baseQuery = LearningQuestion::query()
            ->active()
            ->whereHas('level.part', function ($query) use ($session) {
                $query->where('learning_language_id', $session->learning_language_id);
            })
            ->whereDoesntHave('level.part', function ($query) {
                $query->where('slug', 'bagian-4-cerita-pendek');
            })
            ->whereHas('options')
            ->with([
                'level.part',
                'options' => fn ($query) => $query->orderBy('sort_order'),
            ]);

        $difficultyQuery = clone $baseQuery;
        $this->applyDifficultyFilter($difficultyQuery, $session->difficulty);

        $questions = $difficultyQuery
            ->inRandomOrder()
            ->limit($count)
            ->get();

        if ($questions->count() < $count) {
            $extraQuestions = (clone $baseQuery)
                ->whereNotIn('id', $questions->pluck('id')->all())
                ->inRandomOrder()
                ->limit($count - $questions->count())
                ->get();

            $questions = $questions->concat($extraQuestions)->values();
        }

        return $questions
            ->map(fn (LearningQuestion $question) => $this->mapLearningQuestion($question))
            ->filter()
            ->values();
    }

    private function applyDifficultyFilter($query, string $difficulty): void
    {
        if ($difficulty === 'easy') {
            $query
                ->whereHas('level.part', fn ($partQuery) => $partQuery->where('sort_order', '<=', 2))
                ->whereHas('level', fn ($levelQuery) => $levelQuery->where('sort_order', '<=', 2));

            return;
        }

        if ($difficulty === 'hard') {
            $query->where(function ($nestedQuery) {
                $nestedQuery
                    ->whereHas('level.part', fn ($partQuery) => $partQuery->where('sort_order', '>=', 2))
                    ->orWhereHas('level', fn ($levelQuery) => $levelQuery->where('sort_order', '>=', 3));
            });

            return;
        }

        $query
            ->whereHas('level.part', fn ($partQuery) => $partQuery->where('sort_order', '<=', 4))
            ->whereHas('level', fn ($levelQuery) => $levelQuery->where('sort_order', '<=', 2));
    }

    private function mapLearningQuestion(LearningQuestion $question): ?array
    {
        $correctOption = $question->options->firstWhere('is_correct', true);
        $answer = $correctOption?->option_text ?: $question->correct_answer;
        $options = $question->options
            ->pluck('option_text')
            ->filter()
            ->unique()
            ->values();

        if (blank($answer)) {
            return null;
        }

        if (! $options->contains($answer)) {
            $options->push($answer);
        }

        if ($options->count() < 2) {
            return null;
        }

        return [
            'type' => $question->type ?: 'multiple_choice',
            'prompt' => $this->promptFor($question),
            'question' => $question->question_text,
            'options' => $options->values()->all(),
            'answer' => $answer,
            'explanation' => $question->explanation,
            'source' => 'learning_question:' . $question->id,
        ];
    }

    private function promptFor(LearningQuestion $question): string
    {
        return match ($question->type) {
            'real_case' => 'Situasi',
            'listening' => 'Listening',
            'video_question' => 'Video',
            default => str($question->instruction ?: 'Pilih jawaban')->limit(38)->toString(),
        };
    }

    private function fallbackQuestionPool(string $difficulty): array
    {
        return match ($difficulty) {
            'easy' => [
                $this->fallback('vocabulary', 'Kosakata', 'What is the meaning of "morning"?', ['pagi', 'malam', 'sore', 'kemarin'], 'pagi', '"Morning" berarti pagi.'),
                $this->fallback('vocabulary', 'Kosakata', 'Which word means "air" in English?', ['water', 'book', 'teacher', 'market'], 'water', '"Water" berarti air.'),
                $this->fallback('dialogue', 'Respons', 'A: "Thank you." B: "____"', ["You're welcome", 'Good night', 'I am hungry', 'See yesterday'], "You're welcome", 'Balasan umum untuk "Thank you" adalah "You are welcome".'),
                $this->fallback('grammar', 'Grammar', 'She ____ a book every night.', ['reads', 'read', 'reading', 'are read'], 'reads', 'Subject "she" pada simple present memakai verb+s.'),
                $this->fallback('translation', 'Arti', 'Apa arti kata "teacher"?', ['guru', 'siswa', 'teman', 'sekolah'], 'guru', '"Teacher" berarti guru.'),
            ],
            'hard' => [
                $this->fallback('reading', 'Reading', 'Text: "Although the train was late, Mina still arrived before class started." What happened?', ['Mina arrived before class', 'Mina missed the class', 'The train arrived early', 'Class was cancelled'], 'Mina arrived before class', 'Teks menyebut Mina tetap tiba sebelum kelas dimulai.'),
                $this->fallback('grammar', 'Tenses', 'By the time I arrived, they ____ dinner.', ['had finished', 'finish', 'were finish', 'finishing'], 'had finished', 'Kalimat ini memakai past perfect untuk kejadian yang selesai lebih dulu.'),
                $this->fallback('real_case', 'Situasi', 'Kamu ingin meminta lawan bicara mengulang dengan sopan. Pilih kalimat terbaik.', ['Could you repeat that, please?', 'Repeat now!', 'You say again fast!', 'Why talk?'], 'Could you repeat that, please?', 'Kalimat ini sopan karena memakai "could you" dan "please".'),
                $this->fallback('vocabulary', 'Sinonim', 'Which word is closest in meaning to "difficult"?', ['challenging', 'simple', 'empty', 'cheap'], 'challenging', '"Challenging" dekat maknanya dengan sulit.'),
                $this->fallback('translation', 'Terjemahan', 'Translate: "Saya sudah menyelesaikan tugas sebelum makan malam."', ['I had finished the task before dinner', 'I finish task after dinner', 'I am finishing dinner task', 'I finished before task dinner'], 'I had finished the task before dinner', 'Past perfect cocok untuk tindakan yang selesai sebelum kejadian lain di masa lalu.'),
            ],
            default => [
                $this->fallback('translation', 'Translate', 'Apa arti kalimat: "I usually drink water after breakfast"?', ['Saya biasanya minum air setelah sarapan', 'Saya selalu makan nasi saat malam', 'Dia minum kopi sebelum tidur', 'Mereka sarapan di sekolah'], 'Saya biasanya minum air setelah sarapan', '"Usually" berarti biasanya, dan "after breakfast" berarti setelah sarapan.'),
                $this->fallback('grammar', 'Complete', 'They ____ football every Sunday.', ['play', 'plays', 'playing', 'played by'], 'play', 'Subject "they" memakai verb dasar pada simple present.'),
                $this->fallback('reading', 'Reading', 'Text: "The library is quiet. Students read books there." What is the place like?', ['Quiet', 'Noisy', 'Dangerous', 'Expensive'], 'Quiet', 'Teks menyebut "The library is quiet".'),
                $this->fallback('real_case', 'Situasi', 'Kamu ingin memesan makanan dengan sopan. Kalimat mana yang paling tepat?', ['Can I have fried rice, please?', 'Give me rice now!', 'Rice I want fast!', 'You food me!'], 'Can I have fried rice, please?', 'Kalimat ini sopan karena memakai "Can I have..." dan "please".'),
                $this->fallback('grammar', 'Article', 'I saw ____ elephant at the zoo.', ['an', 'a', 'the only', 'many'], 'an', 'Elephant diawali bunyi vokal, maka memakai "an".'),
            ],
        };
    }

    private function fallback(string $type, string $prompt, string $question, array $options, string $answer, string $explanation): array
    {
        return [
            'type' => $type,
            'prompt' => $prompt,
            'question' => $question,
            'options' => $options,
            'answer' => $answer,
            'explanation' => $explanation,
            'source' => 'local_fallback',
        ];
    }
}
