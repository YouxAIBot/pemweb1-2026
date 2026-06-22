<?php

namespace App\Services\Duel;

use App\Models\DuelQuestion;
use App\Models\DuelSession;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class DuelQuestionGeneratorService
{
    public function generateForSession(DuelSession $session, int $count = 10): void
    {
        if ($session->questions()->exists()) {
            return;
        }

        $pool = collect($this->questionPool())
            ->shuffle()
            ->values();

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
                'source' => 'local_generator',
            ]);
        }
    }

    private function questionPool(): array
    {
        return [
            [
                'type' => 'translation',
                'prompt' => 'Translate',
                'question' => 'Apa arti kalimat: "I usually drink water after breakfast"?',
                'options' => ['Saya biasanya minum air setelah sarapan', 'Saya selalu makan nasi saat malam', 'Dia minum kopi sebelum tidur', 'Mereka sarapan di sekolah'],
                'answer' => 'Saya biasanya minum air setelah sarapan',
                'explanation' => '"Usually" berarti biasanya, dan "after breakfast" berarti setelah sarapan.',
            ],
            [
                'type' => 'grammar',
                'prompt' => 'Complete the sentence',
                'question' => 'She ____ to school every morning.',
                'options' => ['goes', 'go', 'going', 'gone'],
                'answer' => 'goes',
                'explanation' => 'Subject "she" memakai verb+s pada simple present.',
            ],
            [
                'type' => 'vocabulary',
                'prompt' => 'Vocabulary',
                'question' => 'Which word means "cepat" in English?',
                'options' => ['fast', 'slow', 'late', 'heavy'],
                'answer' => 'fast',
                'explanation' => '"Fast" berarti cepat.',
            ],
            [
                'type' => 'dialogue',
                'prompt' => 'Dialogue',
                'question' => 'A: "Thank you." B: "____"',
                'options' => ["You're welcome", 'Good night', 'I am hungry', 'See yesterday'],
                'answer' => "You're welcome",
                'explanation' => 'Balasan umum untuk "Thank you" adalah "You’re welcome".',
            ],
            [
                'type' => 'listening_style',
                'prompt' => 'Listening style',
                'question' => 'Kalimat mana yang paling tepat untuk memperkenalkan diri?',
                'options' => ['My name is Raka', 'I name Raka', 'Me Raka name', 'Raka my is name'],
                'answer' => 'My name is Raka',
                'explanation' => 'Struktur yang benar adalah "My name is ...".',
            ],
            [
                'type' => 'reading',
                'prompt' => 'Reading',
                'question' => 'Text: "Lina wakes up at six and studies English before school." What does Lina do before school?',
                'options' => ['Studies English', 'Plays football', 'Sleeps again', 'Cooks dinner'],
                'answer' => 'Studies English',
                'explanation' => 'Pada teks tertulis Lina belajar English sebelum sekolah.',
            ],
            [
                'type' => 'grammar',
                'prompt' => 'Choose the correct word',
                'question' => 'They ____ football every Sunday.',
                'options' => ['play', 'plays', 'playing', 'played by'],
                'answer' => 'play',
                'explanation' => 'Subject "they" memakai verb dasar pada simple present.',
            ],
            [
                'type' => 'translation',
                'prompt' => 'Translate',
                'question' => 'Translate: "Saya sedang belajar bahasa Inggris."',
                'options' => ['I am learning English', 'I learn yesterday English', 'She is English learning', 'I am learn English'],
                'answer' => 'I am learning English',
                'explanation' => 'Present continuous memakai am/is/are + verb-ing.',
            ],
            [
                'type' => 'vocabulary',
                'prompt' => 'Meaning',
                'question' => 'What is the meaning of "morning"?',
                'options' => ['pagi', 'malam', 'sore', 'kemarin'],
                'answer' => 'pagi',
                'explanation' => '"Morning" berarti pagi.',
            ],
            [
                'type' => 'real_case',
                'prompt' => 'Real case',
                'question' => 'Kamu ingin memesan makanan dengan sopan. Kalimat mana yang paling tepat?',
                'options' => ['Can I have fried rice, please?', 'Give me rice now!', 'Rice I want fast!', 'You food me!'],
                'answer' => 'Can I have fried rice, please?',
                'explanation' => 'Kalimat ini sopan karena memakai "Can I have..." dan "please".',
            ],
            [
                'type' => 'grammar',
                'prompt' => 'Past tense',
                'question' => 'Yesterday, I ____ a movie.',
                'options' => ['watched', 'watch', 'watches', 'watching'],
                'answer' => 'watched',
                'explanation' => '"Yesterday" menunjukkan past tense.',
            ],
            [
                'type' => 'dialogue',
                'prompt' => 'Response',
                'question' => 'A: "How are you?" B: "____"',
                'options' => ['I am fine, thank you', 'It is a book', 'At seven o’clock', 'Because red'],
                'answer' => 'I am fine, thank you',
                'explanation' => 'Pertanyaan "How are you?" menanyakan kabar.',
            ],
            [
                'type' => 'reading',
                'prompt' => 'Reading',
                'question' => 'Text: "The library is quiet. Students read books there." What is the place like?',
                'options' => ['Quiet', 'Noisy', 'Dangerous', 'Expensive'],
                'answer' => 'Quiet',
                'explanation' => 'Teks menyebut "The library is quiet".',
            ],
            [
                'type' => 'vocabulary',
                'prompt' => 'Antonym',
                'question' => 'What is the opposite of "hot"?',
                'options' => ['cold', 'warm', 'big', 'new'],
                'answer' => 'cold',
                'explanation' => 'Lawan kata hot adalah cold.',
            ],
            [
                'type' => 'grammar',
                'prompt' => 'Article',
                'question' => 'I saw ____ elephant at the zoo.',
                'options' => ['an', 'a', 'the only', 'many'],
                'answer' => 'an',
                'explanation' => 'Elephant diawali bunyi vokal, maka memakai "an".',
            ],
        ];
    }
}
