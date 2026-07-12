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
            $extraQuery = clone $baseQuery;
            $this->applyFallbackDifficultyFilter($extraQuery, $session->difficulty);

            $extraQuestions = $extraQuery
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
        $this->excludeStoryAndVideoQuestions($query);

        if ($difficulty === 'easy') {
            $query
                ->where(function ($difficultyQuery) {
                    $difficultyQuery
                        ->where('settings->difficulty', 'easy')
                        ->orWhere('settings->difficulty', 'beginner')
                        ->orWhere('points', '<=', 10);
                })
                ->whereHas('level.part', fn ($partQuery) => $partQuery->where('sort_order', '<=', 2))
                ->whereHas('level', fn ($levelQuery) => $levelQuery->where('sort_order', '<=', 2));

            return;
        }

        if ($difficulty === 'hard') {
            $query
                ->where(function ($difficultyQuery) {
                    $difficultyQuery
                        ->where('settings->difficulty', 'hard')
                        ->orWhere('points', '>=', 15)
                        ->orWhere('type', 'listening');
                })
                ->where(function ($nestedQuery) {
                    $nestedQuery
                        ->whereHas('level.part', fn ($partQuery) => $partQuery->where('sort_order', '>=', 2))
                        ->orWhereHas('level', fn ($levelQuery) => $levelQuery->where('sort_order', '>=', 3));
                });

            return;
        }

        $query
            ->where(function ($difficultyQuery) {
                $difficultyQuery
                    ->where('settings->difficulty', 'normal')
                    ->orWhereNull('settings->difficulty')
                    ->orWhereBetween('points', [10, 15]);
            })
            ->whereHas('level.part', fn ($partQuery) => $partQuery->whereBetween('sort_order', [1, 4]))
            ->whereHas('level', fn ($levelQuery) => $levelQuery->whereBetween('sort_order', [1, 3]));
    }

    private function applyFallbackDifficultyFilter($query, string $difficulty): void
    {
        $this->excludeStoryAndVideoQuestions($query);

        if ($difficulty === 'easy') {
            $query
                ->whereNotIn('type', ['reading_story'])
                ->whereHas('level.part', fn ($partQuery) => $partQuery->where('sort_order', '<=', 3))
                ->whereHas('level', fn ($levelQuery) => $levelQuery->where('sort_order', '<=', 3));

            return;
        }

        if ($difficulty === 'hard') {
            $query->where(function ($nestedQuery) {
                $nestedQuery
                    ->where('points', '>=', 15)
                    ->orWhereHas('level.part', fn ($partQuery) => $partQuery->where('sort_order', '>=', 2))
                    ->orWhereHas('level', fn ($levelQuery) => $levelQuery->where('sort_order', '>=', 3));
            });

            return;
        }

        $query
            ->whereNotIn('type', ['reading_story'])
            ->whereHas('level.part', fn ($partQuery) => $partQuery->where('sort_order', '<=', 4));
    }

    private function excludeStoryAndVideoQuestions($query): void
    {
        $query
            ->whereNotIn('type', ['reading_story'])
            ->whereDoesntHave('level.part', function ($partQuery) {
                $partQuery
                    ->where('slug', 'bagian-4-cerita-pendek')
                    ->orWhere('title', 'like', '%cerita%');
            })
            ->whereDoesntHave('level', function ($levelQuery) {
                $levelQuery->where('title', 'like', '%cerita%');
            });
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
            'listening' => 'Listening',
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
                $this->fallback('vocabulary', 'Kosakata', 'Which word means "buku"?', ['book', 'bag', 'door', 'chair'], 'book', '"Book" berarti buku.'),
                $this->fallback('translation', 'Arti', 'Apa arti kata "school"?', ['sekolah', 'rumah', 'jalan', 'pasar'], 'sekolah', '"School" berarti sekolah.'),
                $this->fallback('grammar', 'Grammar', 'I ____ happy today.', ['am', 'is', 'are', 'be'], 'am', 'Subject "I" memakai "am".'),
                $this->fallback('dialogue', 'Sapaan', 'A: "Good night." B: "____"', ['Good night', 'Good morning', 'Thank you', 'I am fine'], 'Good night', 'Sapaan "Good night" dapat dibalas dengan ungkapan yang sama.'),
                $this->fallback('vocabulary', 'Kosakata', 'Which word means "teman"?', ['friend', 'father', 'food', 'floor'], 'friend', '"Friend" berarti teman.'),
            ],
            'hard' => [
                $this->fallback('reading', 'Reading', 'Text: "Although the train was late, Mina still arrived before class started." What happened?', ['Mina arrived before class', 'Mina missed the class', 'The train arrived early', 'Class was cancelled'], 'Mina arrived before class', 'Teks menyebut Mina tetap tiba sebelum kelas dimulai.'),
                $this->fallback('grammar', 'Tenses', 'By the time I arrived, they ____ dinner.', ['had finished', 'finish', 'were finish', 'finishing'], 'had finished', 'Kalimat ini memakai past perfect untuk kejadian yang selesai lebih dulu.'),
                $this->fallback('multiple_choice', 'Situasi', 'Kamu ingin meminta lawan bicara mengulang dengan sopan. Pilih kalimat terbaik.', ['Could you repeat that, please?', 'Repeat now!', 'You say again fast!', 'Why talk?'], 'Could you repeat that, please?', 'Kalimat ini sopan karena memakai "could you" dan "please".'),
                $this->fallback('vocabulary', 'Sinonim', 'Which word is closest in meaning to "difficult"?', ['challenging', 'simple', 'empty', 'cheap'], 'challenging', '"Challenging" dekat maknanya dengan sulit.'),
                $this->fallback('translation', 'Terjemahan', 'Translate: "Saya sudah menyelesaikan tugas sebelum makan malam."', ['I had finished the task before dinner', 'I finish task after dinner', 'I am finishing dinner task', 'I finished before task dinner'], 'I had finished the task before dinner', 'Past perfect cocok untuk tindakan yang selesai sebelum kejadian lain di masa lalu.'),
                $this->fallback('grammar', 'Conditionals', 'If she had studied harder, she ____ the exam.', ['would have passed', 'will pass', 'passes', 'would pass now'], 'would have passed', 'Third conditional memakai pola "would have + past participle".'),
                $this->fallback('reading', 'Inference', 'Text: "Raka kept checking the clock while waiting for the announcement." What can be inferred?', ['Raka was anxious', 'Raka was sleeping', 'Raka forgot the time', 'Raka ignored the announcement'], 'Raka was anxious', 'Kebiasaan mengecek jam menunjukkan ia cemas atau menunggu dengan tegang.'),
                $this->fallback('vocabulary', 'Nuance', 'Which word best replaces "brief" in "a brief explanation"?', ['short', 'angry', 'unclear', 'expensive'], 'short', '"Brief" berarti singkat.'),
                $this->fallback('grammar', 'Passive Voice', 'The report ____ by the team before noon.', ['was submitted', 'submitted', 'was submit', 'submitting'], 'was submitted', 'Kalimat pasif lampau memakai "was/were + past participle".'),
                $this->fallback('multiple_choice', 'Formal Email', 'Choose the most appropriate closing for a formal email.', ['Sincerely,', 'Yo!', 'Later bro', 'Bye now!!!'], 'Sincerely,', '"Sincerely" umum dipakai untuk penutup email formal.'),
            ],
            default => [
                $this->fallback('translation', 'Translate', 'Apa arti kalimat: "I usually drink water after breakfast"?', ['Saya biasanya minum air setelah sarapan', 'Saya selalu makan nasi saat malam', 'Dia minum kopi sebelum tidur', 'Mereka sarapan di sekolah'], 'Saya biasanya minum air setelah sarapan', '"Usually" berarti biasanya, dan "after breakfast" berarti setelah sarapan.'),
                $this->fallback('grammar', 'Complete', 'They ____ football every Sunday.', ['play', 'plays', 'playing', 'played by'], 'play', 'Subject "they" memakai verb dasar pada simple present.'),
                $this->fallback('reading', 'Reading', 'Text: "The library is quiet. Students read books there." What is the place like?', ['Quiet', 'Noisy', 'Dangerous', 'Expensive'], 'Quiet', 'Teks menyebut "The library is quiet".'),
                $this->fallback('multiple_choice', 'Situasi', 'Kamu ingin memesan makanan dengan sopan. Kalimat mana yang paling tepat?', ['Can I have fried rice, please?', 'Give me rice now!', 'Rice I want fast!', 'You food me!'], 'Can I have fried rice, please?', 'Kalimat ini sopan karena memakai "Can I have..." dan "please".'),
                $this->fallback('grammar', 'Article', 'I saw ____ elephant at the zoo.', ['an', 'a', 'the only', 'many'], 'an', 'Elephant diawali bunyi vokal, maka memakai "an".'),
                $this->fallback('vocabulary', 'Kosakata', 'Which word is opposite of "clean"?', ['dirty', 'fresh', 'bright', 'safe'], 'dirty', 'Lawan kata "clean" adalah "dirty".'),
                $this->fallback('dialogue', 'Respons', 'A: "How are you?" B: "____"', ['I am fine, thanks', 'It is a book', 'At seven o clock', 'No, I do not'], 'I am fine, thanks', 'Pertanyaan ini menanyakan kabar.'),
                $this->fallback('grammar', 'Preposition', 'The keys are ____ the table.', ['on', 'eat', 'blue', 'quickly'], 'on', 'Preposisi "on" dipakai untuk benda di atas permukaan.'),
                $this->fallback('translation', 'Terjemahan', 'Translate: "Dia pergi ke sekolah setiap hari."', ['He goes to school every day', 'He go school yesterday', 'She went market tomorrow', 'They school every night'], 'He goes to school every day', 'Untuk kebiasaan memakai simple present.'),
                $this->fallback('reading', 'Reading', 'Text: "Nina brought an umbrella because the sky was dark." Why did Nina bring an umbrella?', ['She expected rain', 'She wanted to sleep', 'She lost her bag', 'She was hungry'], 'She expected rain', 'Langit gelap memberi petunjuk kemungkinan hujan.'),
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
