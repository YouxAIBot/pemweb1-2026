<?php

namespace Database\Seeders;

use App\Models\LearningLanguage;
use App\Models\LearningLevel;
use App\Models\LearningPart;
use App\Models\LearningQuestion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StructuredFoundationCurriculumSeeder extends Seeder
{
    private string $seedSource = 'structured_foundation_curriculum';

    public function run(): void
    {
        DB::transaction(function (): void {
            foreach ($this->languages() as $languageData) {
                $language = LearningLanguage::query()
                    ->where('slug', $languageData['slug'])
                    ->first();

                if (! $language) {
                    continue;
                }

                foreach ($this->parts($languageData) as $partData) {
                    $part = LearningPart::updateOrCreate(
                        [
                            'learning_language_id' => $language->id,
                            'slug' => $partData['slug'],
                        ],
                        [
                            'title' => $partData['title'],
                            'subtitle' => $partData['subtitle'],
                            'description' => $partData['description'],
                            'badge_text' => $partData['badge_text'],
                            'level_number' => $partData['level_number'],
                            'sort_order' => $partData['sort_order'],
                            'is_active' => true,
                            'settings' => ['seed_source' => $this->seedSource],
                        ],
                    );

                    foreach ($partData['levels'] as $levelIndex => $levelData) {
                        $level = LearningLevel::updateOrCreate(
                            [
                                'learning_part_id' => $part->id,
                                'slug' => $levelData['slug'],
                            ],
                            [
                                'title' => $levelData['title'],
                                'type' => $levelData['type'],
                                'short_label' => $partData['level_number'] . '.' . ($levelIndex + 1),
                                'description' => $levelData['description'],
                                'sort_order' => $levelIndex + 1,
                                'xp_reward' => $levelData['xp_reward'],
                                'passing_score' => 70,
                                'position_x' => $levelData['position_x'],
                                'position_y' => $levelData['position_y'],
                                'is_active' => true,
                                'settings' => [
                                    'seed_source' => $this->seedSource,
                                    'difficulty' => $levelData['difficulty'],
                                    'learning_goal' => $levelData['learning_goal'],
                                ],
                            ],
                        );

                        LearningQuestion::query()
                            ->where('learning_level_id', $level->id)
                            ->where('settings->seed_source', $this->seedSource)
                            ->get()
                            ->each(function (LearningQuestion $question): void {
                                $question->options()->delete();
                                $question->delete();
                            });

                        foreach ($levelData['questions'] as $questionIndex => $questionData) {
                            $this->seedQuestion($level, $questionData, $questionIndex + 1);
                        }
                    }
                }
            }
        });
    }

    private function seedQuestion(LearningLevel $level, array $data, int $sortOrder): void
    {
        $settings = array_merge($data['settings'] ?? [], [
            'seed_source' => $this->seedSource,
            'difficulty' => $data['difficulty'] ?? 'easy',
        ]);

        $question = LearningQuestion::create([
            'learning_level_id' => $level->id,
            'type' => $data['type'],
            'instruction' => $data['instruction'],
            'question_text' => $data['question_text'],
            'correct_answer' => $data['correct_answer'] ?? null,
            'explanation' => $data['explanation'] ?? null,
            'points' => $data['points'] ?? 10,
            'time_limit' => $data['time_limit'] ?? 30,
            'sort_order' => $sortOrder,
            'is_active' => true,
            'settings' => $settings,
        ]);

        foreach (($data['options'] ?? []) as $optionIndex => $option) {
            $question->options()->create([
                'option_text' => $option['text'],
                'is_correct' => (bool) ($option['correct'] ?? false),
                'sort_order' => $optionIndex + 1,
            ]);
        }
    }

    private function parts(array $language): array
    {
        return [
            [
                'slug' => 'bagian-1-pondasi-sapaan',
                'title' => 'Bagian 1 Pondasi Sapaan ' . $language['name'],
                'subtitle' => 'Mulai dari salam, ucapan sopan, dan respons pendek.',
                'description' => 'Bagian ini mengajarkan pengguna mengenali kosakata sapaan sebelum masuk ke soal campuran.',
                'badge_text' => 'Dasar 1',
                'level_number' => 1,
                'sort_order' => 1,
                'levels' => [
                    $this->vocabularyLevel($language, 'greetings', 'level-1-kosakata-sapaan', 'Level 1 - Kosakata Sapaan', 'Pelajari salam dan ucapan sopan paling dasar.', 18, 26),
                    $this->sentenceLevel($language, 'greeting_sentences', 'level-2-susun-sapaan', 'Level 2 - Susun Sapaan', 'Latihan menyusun kalimat sapaan pendek.', 50, 50),
                    $this->storyLevel($language, 'greeting_story', 'level-3-reading-sapaan', 'Level 3 - Reading Sapaan', 'Baca dialog sapaan singkat lalu jawab pertanyaan pemahaman.', 82, 26),
                ],
            ],
            [
                'slug' => 'bagian-2-pondasi-perkenalan',
                'title' => 'Bagian 2 Pondasi Perkenalan ' . $language['name'],
                'subtitle' => 'Nama, asal, kabar, dan kalimat perkenalan diri.',
                'description' => 'Bagian ini melanjutkan pondasi sapaan menuju dialog perkenalan yang lebih natural.',
                'badge_text' => 'Dasar 2',
                'level_number' => 2,
                'sort_order' => 2,
                'levels' => [
                    $this->vocabularyLevel($language, 'introductions', 'level-1-kosakata-perkenalan', 'Level 1 - Kosakata Perkenalan', 'Pelajari kata dan frasa yang dipakai saat memperkenalkan diri.', 18, 26),
                    $this->sentenceLevel($language, 'intro_sentences', 'level-2-susun-perkenalan', 'Level 2 - Susun Perkenalan', 'Latihan menyusun kalimat nama, asal, dan kabar.', 50, 50),
                    $this->storyLevel($language, 'intro_story', 'level-3-reading-perkenalan', 'Level 3 - Reading Perkenalan', 'Baca dialog perkenalan dan jawab pertanyaan pemahaman.', 82, 26),
                ],
            ],
        ];
    }

    private function vocabularyLevel(array $language, string $group, string $slug, string $title, string $description, int $x, int $y): array
    {
        $items = $language[$group];
        $questions = [];

        foreach ($items as $index => $item) {
            $distractors = collect($items)
                ->where('target', '!=', $item['target'])
                ->pluck('translation')
                ->shuffle()
                ->take(3)
                ->values()
                ->all();

            $questions[] = [
                'type' => 'multiple_choice',
                'instruction' => 'Pilih arti yang paling tepat.',
                'question_text' => 'Apa arti kata "' . $item['target'] . '"?',
                'correct_answer' => $item['translation'],
                'explanation' => 'Kata "' . $item['target'] . '" berarti "' . $item['translation'] . '".',
                'difficulty' => 'easy',
                'settings' => [
                    'learning_phrase_text' => $item['target'],
                    'learning_phrase_translation' => $item['translation'],
                ],
                'options' => $this->options($item['translation'], $distractors),
            ];
        }

        $questions[] = [
            'type' => 'word_match',
            'instruction' => 'Cocokkan kata dengan arti yang benar.',
            'question_text' => 'Cocokkan kosakata dasar ' . $language['name'] . ' dengan artinya.',
            'correct_answer' => 'matched',
            'explanation' => 'Semua pasangan kosakata sudah sesuai.',
            'difficulty' => 'easy',
            'settings' => [
                'word_pairs' => collect($items)->take(4)->map(fn (array $item, int $index): array => [
                    'left' => $item['target'],
                    'right' => $item['translation'],
                    'audio_path' => null,
                ])->values()->all(),
            ],
            'options' => [],
        ];

        return $this->level($slug, $title, $description, 'mixed', 'Mengenal kosakata sebelum menjawab soal.', $x, $y, 20, 'easy', $questions);
    }

    private function sentenceLevel(array $language, string $group, string $slug, string $title, string $description, int $x, int $y): array
    {
        $questions = collect($language[$group])->map(function (array $sentence): array {
            return [
                'type' => 'sentence_order',
                'instruction' => 'Urutkan kata menjadi kalimat yang benar.',
                'question_text' => $sentence['prompt'],
                'correct_answer' => $sentence['target'],
                'explanation' => 'Kalimat yang natural adalah "' . $sentence['target'] . '".',
                'difficulty' => 'normal',
                'settings' => [
                    'learning_phrase_text' => $sentence['target'],
                    'learning_phrase_translation' => $sentence['translation'],
                    'sentence_tokens' => collect($sentence['tokens'])->map(fn (string $token, int $index): array => [
                        'id' => $index + 1,
                        'text' => $token,
                    ])->values()->all(),
                ],
                'options' => [],
            ];
        })->values()->all();

        return $this->level($slug, $title, $description, 'sentence_order', 'Menyusun kalimat pendek dari kosakata yang sudah dipelajari.', $x, $y, 24, 'normal', $questions);
    }

    private function storyLevel(array $language, string $storyKey, string $slug, string $title, string $description, int $x, int $y): array
    {
        $story = $language[$storyKey];

        $question = [
            'type' => 'reading_story',
            'instruction' => 'Baca dialog, lalu jawab pertanyaan pemahaman.',
            'question_text' => $story['title'],
            'correct_answer' => 'completed',
            'explanation' => 'Reading selesai.',
            'difficulty' => 'normal',
            'points' => 25,
            'time_limit' => 90,
            'settings' => [
                'story_button_label' => 'Mulai Reading',
                'story_segments' => collect($story['segments'])->map(fn (string $text, int $index): array => [
                    'id' => $index + 1,
                    'text' => $text,
                    'audio_path' => null,
                    'audio_manual_path' => null,
                ])->values()->all(),
                'story_questions' => collect($story['questions'])->map(fn (array $item, int $index): array => [
                    'id' => $index + 1,
                    'question_text' => $item['question'],
                    'explanation' => $item['explanation'],
                    'options' => $this->settingsOptions($item['answer'], $item['wrong']),
                ])->values()->all(),
            ],
            'options' => [],
        ];

        return $this->level($slug, $title, $description, 'reading_story', 'Memahami dialog sebagai satu konteks utuh.', $x, $y, 30, 'normal', [$question]);
    }

    private function level(string $slug, string $title, string $description, string $type, string $goal, int $x, int $y, int $xp, string $difficulty, array $questions): array
    {
        return [
            'slug' => $slug,
            'title' => $title,
            'type' => $type,
            'description' => $description,
            'learning_goal' => $goal,
            'position_x' => $x,
            'position_y' => $y,
            'xp_reward' => $xp,
            'difficulty' => $difficulty,
            'questions' => $questions,
        ];
    }

    private function options(string $answer, array $wrong): array
    {
        return collect($wrong)
            ->take(3)
            ->map(fn (string $text): array => ['text' => $text, 'correct' => false])
            ->push(['text' => $answer, 'correct' => true])
            ->shuffle()
            ->values()
            ->all();
    }

    private function settingsOptions(string $answer, array $wrong): array
    {
        return collect($wrong)
            ->take(3)
            ->map(fn (string $text): array => ['text' => $text, 'is_correct' => false])
            ->push(['text' => $answer, 'is_correct' => true])
            ->shuffle()
            ->values()
            ->all();
    }

    private function languages(): array
    {
        return [
            $this->language('inggris', 'Inggris', [
                ['target' => 'Hello', 'translation' => 'Halo'],
                ['target' => 'Good morning', 'translation' => 'Selamat pagi'],
                ['target' => 'Thank you', 'translation' => 'Terima kasih'],
                ['target' => 'Sorry', 'translation' => 'Maaf'],
                ['target' => 'Goodbye', 'translation' => 'Sampai jumpa'],
            ], [
                ['target' => 'My name is', 'translation' => 'Nama saya'],
                ['target' => 'I am from', 'translation' => 'Saya berasal dari'],
                ['target' => 'Nice to meet you', 'translation' => 'Senang bertemu denganmu'],
                ['target' => 'How are you?', 'translation' => 'Apa kabar?'],
                ['target' => 'I am fine', 'translation' => 'Saya baik-baik saja'],
            ], [
                ['prompt' => 'Susun sapaan pagi.', 'target' => 'Good morning', 'translation' => 'Selamat pagi', 'tokens' => ['Good', 'morning']],
                ['prompt' => 'Susun ucapan terima kasih.', 'target' => 'Thank you', 'translation' => 'Terima kasih', 'tokens' => ['Thank', 'you']],
                ['prompt' => 'Susun kalimat perpisahan.', 'target' => 'See you later', 'translation' => 'Sampai jumpa nanti', 'tokens' => ['See', 'you', 'later']],
            ], [
                ['prompt' => 'Susun kalimat nama.', 'target' => 'My name is Rina', 'translation' => 'Nama saya Rina', 'tokens' => ['My', 'name', 'is', 'Rina']],
                ['prompt' => 'Susun kalimat asal.', 'target' => 'I am from Indonesia', 'translation' => 'Saya berasal dari Indonesia', 'tokens' => ['I', 'am', 'from', 'Indonesia']],
                ['prompt' => 'Susun respons kabar.', 'target' => 'I am fine', 'translation' => 'Saya baik-baik saja', 'tokens' => ['I', 'am', 'fine']],
            ], [
                'title' => 'Dialog sapaan di pagi hari',
                'segments' => ['Ari: Good morning, Sinta.', 'Sinta: Good morning, Ari.', 'Ari: How are you?', 'Sinta: I am fine, thank you.'],
                'questions' => [
                    ['question' => 'Kapan Ari menyapa Sinta?', 'answer' => 'Pagi hari', 'wrong' => ['Malam hari', 'Siang hari', 'Saat pulang sekolah'], 'explanation' => 'Ari mengatakan "Good morning".'],
                    ['question' => 'Bagaimana kabar Sinta?', 'answer' => 'Sinta baik-baik saja', 'wrong' => ['Sinta sedang marah', 'Sinta sakit', 'Sinta pulang'], 'explanation' => 'Sinta menjawab "I am fine".'],
                ],
            ], [
                'title' => 'Dialog perkenalan di kelas',
                'segments' => ['Rina: Hello, my name is Rina.', 'Bima: Hi Rina, I am Bima.', 'Rina: Nice to meet you.', 'Bima: Nice to meet you too.'],
                'questions' => [
                    ['question' => 'Siapa yang memperkenalkan diri lebih dulu?', 'answer' => 'Rina', 'wrong' => ['Bima', 'Guru', 'Teman kelas'], 'explanation' => 'Rina membuka percakapan dengan menyebut namanya.'],
                    ['question' => 'Apa respons Bima?', 'answer' => 'Bima juga memperkenalkan dirinya', 'wrong' => ['Bima pergi', 'Bima meminta maaf', 'Bima bertanya harga'], 'explanation' => 'Bima mengatakan namanya setelah Rina.'],
                ],
            ]),
            $this->language('mandarin', 'Mandarin', [
                ['target' => '你好', 'translation' => 'Halo'],
                ['target' => '早上好', 'translation' => 'Selamat pagi'],
                ['target' => '谢谢', 'translation' => 'Terima kasih'],
                ['target' => '对不起', 'translation' => 'Maaf'],
                ['target' => '再见', 'translation' => 'Sampai jumpa'],
            ], [
                ['target' => '我叫', 'translation' => 'Nama saya'],
                ['target' => '我来自', 'translation' => 'Saya berasal dari'],
                ['target' => '很高兴认识你', 'translation' => 'Senang bertemu denganmu'],
                ['target' => '你好吗？', 'translation' => 'Apa kabar?'],
                ['target' => '我很好', 'translation' => 'Saya baik-baik saja'],
            ], [
                ['prompt' => 'Susun sapaan halo.', 'target' => '你 好', 'translation' => 'Halo', 'tokens' => ['你', '好']],
                ['prompt' => 'Susun ucapan terima kasih.', 'target' => '谢 谢', 'translation' => 'Terima kasih', 'tokens' => ['谢', '谢']],
                ['prompt' => 'Susun salam perpisahan.', 'target' => '再 见', 'translation' => 'Sampai jumpa', 'tokens' => ['再', '见']],
            ], [
                ['prompt' => 'Susun kalimat nama.', 'target' => '我 叫 林娜', 'translation' => 'Nama saya Lina', 'tokens' => ['我', '叫', '林娜']],
                ['prompt' => 'Susun kalimat asal.', 'target' => '我 来自 印尼', 'translation' => 'Saya berasal dari Indonesia', 'tokens' => ['我', '来自', '印尼']],
                ['prompt' => 'Susun respons kabar.', 'target' => '我 很 好', 'translation' => 'Saya sangat baik', 'tokens' => ['我', '很', '好']],
            ], [
                'title' => 'Dialog sapaan Mandarin',
                'segments' => ['阿里: 你好，林娜。', '林娜: 你好，阿里。', '阿里: 你好吗？', '林娜: 我很好，谢谢。'],
                'questions' => [
                    ['question' => 'Apa sapaan yang dipakai Ali?', 'answer' => '你好', 'wrong' => ['谢谢', '再见', '对不起'], 'explanation' => 'Ali membuka percakapan dengan 你好.'],
                    ['question' => 'Bagaimana kabar Lina?', 'answer' => 'Lina baik-baik saja', 'wrong' => ['Lina marah', 'Lina pergi', 'Lina belajar Jepang'], 'explanation' => 'Lina menjawab 我很好.'],
                ],
            ], [
                'title' => 'Dialog perkenalan Mandarin',
                'segments' => ['林娜: 你好，我叫林娜。', '阿里: 你好，我叫阿里。', '林娜: 很高兴认识你。', '阿里: 我也很高兴。'],
                'questions' => [
                    ['question' => 'Siapa nama orang pertama?', 'answer' => 'Lina', 'wrong' => ['Ali', 'Guru', 'Bima'], 'explanation' => 'Kalimat pertama menyebut 我叫林娜.'],
                    ['question' => 'Apa maksud 很高兴认识你?', 'answer' => 'Senang bertemu denganmu', 'wrong' => ['Sampai jumpa', 'Terima kasih banyak', 'Saya dari Indonesia'], 'explanation' => 'Frasa itu dipakai saat berkenalan.'],
                ],
            ]),
            $this->language('korea', 'Korea', [
                ['target' => '안녕하세요', 'translation' => 'Halo'],
                ['target' => '좋은 아침이에요', 'translation' => 'Selamat pagi'],
                ['target' => '감사합니다', 'translation' => 'Terima kasih'],
                ['target' => '미안합니다', 'translation' => 'Maaf'],
                ['target' => '안녕히 가세요', 'translation' => 'Sampai jumpa'],
            ], [
                ['target' => '제 이름은', 'translation' => 'Nama saya'],
                ['target' => '저는 ...에서 왔어요', 'translation' => 'Saya berasal dari'],
                ['target' => '만나서 반갑습니다', 'translation' => 'Senang bertemu denganmu'],
                ['target' => '잘 지내요?', 'translation' => 'Apa kabar?'],
                ['target' => '잘 지내요', 'translation' => 'Saya baik-baik saja'],
            ], [
                ['prompt' => 'Susun sapaan halo.', 'target' => '안녕 하세요', 'translation' => 'Halo', 'tokens' => ['안녕', '하세요']],
                ['prompt' => 'Susun ucapan terima kasih.', 'target' => '감사 합니다', 'translation' => 'Terima kasih', 'tokens' => ['감사', '합니다']],
                ['prompt' => 'Susun respons maaf.', 'target' => '미안 합니다', 'translation' => 'Maaf', 'tokens' => ['미안', '합니다']],
            ], [
                ['prompt' => 'Susun kalimat nama.', 'target' => '제 이름은 리나 입니다', 'translation' => 'Nama saya Rina', 'tokens' => ['제', '이름은', '리나', '입니다']],
                ['prompt' => 'Susun kalimat asal.', 'target' => '저는 인도네시아 에서 왔어요', 'translation' => 'Saya dari Indonesia', 'tokens' => ['저는', '인도네시아', '에서', '왔어요']],
                ['prompt' => 'Susun respons perkenalan.', 'target' => '만나서 반갑습니다', 'translation' => 'Senang bertemu denganmu', 'tokens' => ['만나서', '반갑습니다']],
            ], [
                'title' => 'Dialog sapaan Korea',
                'segments' => ['민수: 안녕하세요, 리나.', '리나: 안녕하세요, 민수.', '민수: 잘 지내요?', '리나: 잘 지내요. 감사합니다.'],
                'questions' => [
                    ['question' => 'Apa sapaan yang dipakai Minsu?', 'answer' => '안녕하세요', 'wrong' => ['감사합니다', '미안합니다', '안녕히 가세요'], 'explanation' => 'Minsu membuka percakapan dengan 안녕하세요.'],
                    ['question' => 'Bagaimana kabar Rina?', 'answer' => 'Rina baik-baik saja', 'wrong' => ['Rina sedih', 'Rina pulang', 'Rina meminta maaf'], 'explanation' => 'Rina menjawab 잘 지내요.'],
                ],
            ], [
                'title' => 'Dialog perkenalan Korea',
                'segments' => ['리나: 제 이름은 리나입니다.', '민수: 저는 민수입니다.', '리나: 만나서 반갑습니다.', '민수: 저도 반갑습니다.'],
                'questions' => [
                    ['question' => 'Siapa yang bernama Rina?', 'answer' => 'Orang pertama', 'wrong' => ['Orang kedua', 'Guru', 'Teman ketiga'], 'explanation' => 'Kalimat pertama menyebut 제 이름은 리나입니다.'],
                    ['question' => 'Apa maksud 만나서 반갑습니다?', 'answer' => 'Senang bertemu denganmu', 'wrong' => ['Selamat pagi', 'Sampai jumpa', 'Saya lapar'], 'explanation' => 'Frasa ini dipakai saat berkenalan.'],
                ],
            ]),
            $this->language('jepang', 'Jepang', [
                ['target' => 'こんにちは', 'translation' => 'Halo'],
                ['target' => 'おはようございます', 'translation' => 'Selamat pagi'],
                ['target' => 'ありがとうございます', 'translation' => 'Terima kasih'],
                ['target' => 'すみません', 'translation' => 'Maaf'],
                ['target' => 'さようなら', 'translation' => 'Sampai jumpa'],
            ], [
                ['target' => '私の名前は', 'translation' => 'Nama saya'],
                ['target' => 'インドネシアから来ました', 'translation' => 'Saya berasal dari Indonesia'],
                ['target' => 'よろしくお願いします', 'translation' => 'Senang berkenalan'],
                ['target' => '元気ですか', 'translation' => 'Apa kabar?'],
                ['target' => '元気です', 'translation' => 'Saya baik-baik saja'],
            ], [
                ['prompt' => 'Susun sapaan halo.', 'target' => 'こんにちは', 'translation' => 'Halo', 'tokens' => ['こんにちは']],
                ['prompt' => 'Susun ucapan terima kasih.', 'target' => 'ありがとう ございます', 'translation' => 'Terima kasih', 'tokens' => ['ありがとう', 'ございます']],
                ['prompt' => 'Susun perpisahan.', 'target' => 'さよう なら', 'translation' => 'Sampai jumpa', 'tokens' => ['さよう', 'なら']],
            ], [
                ['prompt' => 'Susun kalimat nama.', 'target' => '私 の 名前 は リナ です', 'translation' => 'Nama saya Rina', 'tokens' => ['私', 'の', '名前', 'は', 'リナ', 'です']],
                ['prompt' => 'Susun kalimat asal.', 'target' => 'インドネシア から 来ました', 'translation' => 'Saya datang dari Indonesia', 'tokens' => ['インドネシア', 'から', '来ました']],
                ['prompt' => 'Susun respons perkenalan.', 'target' => 'よろしく お願いします', 'translation' => 'Senang berkenalan', 'tokens' => ['よろしく', 'お願いします']],
            ], [
                'title' => 'Dialog sapaan Jepang',
                'segments' => ['アリ: こんにちは、リナ。', 'リナ: こんにちは、アリ。', 'アリ: 元気ですか。', 'リナ: 元気です。ありがとうございます。'],
                'questions' => [
                    ['question' => 'Apa sapaan yang digunakan Ari?', 'answer' => 'こんにちは', 'wrong' => ['すみません', 'さようなら', 'ありがとうございます'], 'explanation' => 'Ari menggunakan こんにちは.'],
                    ['question' => 'Bagaimana kabar Rina?', 'answer' => 'Rina baik-baik saja', 'wrong' => ['Rina pergi', 'Rina marah', 'Rina meminta bantuan'], 'explanation' => 'Rina menjawab 元気です.'],
                ],
            ], [
                'title' => 'Dialog perkenalan Jepang',
                'segments' => ['リナ: 私の名前はリナです。', 'アリ: 私はアリです。', 'リナ: よろしくお願いします。', 'アリ: よろしくお願いします。'],
                'questions' => [
                    ['question' => 'Siapa nama orang pertama?', 'answer' => 'Rina', 'wrong' => ['Ari', 'Sensei', 'Budi'], 'explanation' => 'Kalimat pertama menyebut リナ.'],
                    ['question' => 'Kapan よろしくお願いします digunakan?', 'answer' => 'Saat berkenalan', 'wrong' => ['Saat membayar', 'Saat tidur', 'Saat makan'], 'explanation' => 'Frasa ini umum dipakai saat perkenalan.'],
                ],
            ]),
            $this->language('arab', 'Arab', [
                ['target' => 'مرحبا', 'translation' => 'Halo'],
                ['target' => 'صباح الخير', 'translation' => 'Selamat pagi'],
                ['target' => 'شكرا', 'translation' => 'Terima kasih'],
                ['target' => 'آسف', 'translation' => 'Maaf'],
                ['target' => 'مع السلامة', 'translation' => 'Sampai jumpa'],
            ], [
                ['target' => 'اسمي', 'translation' => 'Nama saya'],
                ['target' => 'أنا من', 'translation' => 'Saya berasal dari'],
                ['target' => 'تشرفت بمعرفتك', 'translation' => 'Senang bertemu denganmu'],
                ['target' => 'كيف حالك؟', 'translation' => 'Apa kabar?'],
                ['target' => 'أنا بخير', 'translation' => 'Saya baik-baik saja'],
            ], [
                ['prompt' => 'Susun sapaan pagi.', 'target' => 'صباح الخير', 'translation' => 'Selamat pagi', 'tokens' => ['صباح', 'الخير']],
                ['prompt' => 'Susun ucapan terima kasih.', 'target' => 'شكرا لك', 'translation' => 'Terima kasih kepadamu', 'tokens' => ['شكرا', 'لك']],
                ['prompt' => 'Susun salam perpisahan.', 'target' => 'مع السلامة', 'translation' => 'Sampai jumpa', 'tokens' => ['مع', 'السلامة']],
            ], [
                ['prompt' => 'Susun kalimat nama.', 'target' => 'اسمي رينا', 'translation' => 'Nama saya Rina', 'tokens' => ['اسمي', 'رينا']],
                ['prompt' => 'Susun kalimat asal.', 'target' => 'أنا من إندونيسيا', 'translation' => 'Saya dari Indonesia', 'tokens' => ['أنا', 'من', 'إندونيسيا']],
                ['prompt' => 'Susun respons kabar.', 'target' => 'أنا بخير', 'translation' => 'Saya baik-baik saja', 'tokens' => ['أنا', 'بخير']],
            ], [
                'title' => 'Dialog sapaan Arab',
                'segments' => ['علي: مرحبا يا رينا.', 'رينا: مرحبا يا علي.', 'علي: كيف حالك؟', 'رينا: أنا بخير، شكرا.'],
                'questions' => [
                    ['question' => 'Apa sapaan yang digunakan Ali?', 'answer' => 'مرحبا', 'wrong' => ['شكرا', 'آسف', 'مع السلامة'], 'explanation' => 'Ali membuka percakapan dengan مرحبا.'],
                    ['question' => 'Bagaimana kabar Rina?', 'answer' => 'Rina baik-baik saja', 'wrong' => ['Rina pergi', 'Rina meminta maaf', 'Rina belajar'], 'explanation' => 'Rina menjawab أنا بخير.'],
                ],
            ], [
                'title' => 'Dialog perkenalan Arab',
                'segments' => ['رينا: مرحبا، اسمي رينا.', 'علي: مرحبا، اسمي علي.', 'رينا: تشرفت بمعرفتك.', 'علي: وأنا أيضا.'],
                'questions' => [
                    ['question' => 'Siapa nama orang pertama?', 'answer' => 'Rina', 'wrong' => ['Ali', 'Guru', 'Teman'], 'explanation' => 'Orang pertama mengatakan اسمي رينا.'],
                    ['question' => 'Apa maksud تشرفت بمعرفتك?', 'answer' => 'Senang bertemu denganmu', 'wrong' => ['Sampai jumpa', 'Saya lapar', 'Terima kasih'], 'explanation' => 'Frasa ini dipakai saat berkenalan.'],
                ],
            ]),
            $this->language('prancis', 'Prancis', [
                ['target' => 'Bonjour', 'translation' => 'Halo / Selamat pagi'],
                ['target' => 'Bonsoir', 'translation' => 'Selamat malam'],
                ['target' => 'Merci', 'translation' => 'Terima kasih'],
                ['target' => 'Pardon', 'translation' => 'Maaf'],
                ['target' => 'Au revoir', 'translation' => 'Sampai jumpa'],
            ], [
                ['target' => 'Je m\'appelle', 'translation' => 'Nama saya'],
                ['target' => 'Je viens de', 'translation' => 'Saya berasal dari'],
                ['target' => 'Enchanté', 'translation' => 'Senang berkenalan'],
                ['target' => 'Comment ça va ?', 'translation' => 'Apa kabar?'],
                ['target' => 'Ça va bien', 'translation' => 'Saya baik-baik saja'],
            ], [
                ['prompt' => 'Susun sapaan pagi.', 'target' => 'Bonjour', 'translation' => 'Halo / Selamat pagi', 'tokens' => ['Bonjour']],
                ['prompt' => 'Susun ucapan terima kasih.', 'target' => 'Merci beaucoup', 'translation' => 'Terima kasih banyak', 'tokens' => ['Merci', 'beaucoup']],
                ['prompt' => 'Susun salam perpisahan.', 'target' => 'Au revoir', 'translation' => 'Sampai jumpa', 'tokens' => ['Au', 'revoir']],
            ], [
                ['prompt' => 'Susun kalimat nama.', 'target' => 'Je m\'appelle Rina', 'translation' => 'Nama saya Rina', 'tokens' => ['Je', 'm\'appelle', 'Rina']],
                ['prompt' => 'Susun kalimat asal.', 'target' => 'Je viens de Indonésie', 'translation' => 'Saya berasal dari Indonesia', 'tokens' => ['Je', 'viens', 'de', 'Indonésie']],
                ['prompt' => 'Susun respons kabar.', 'target' => 'Ça va bien', 'translation' => 'Saya baik-baik saja', 'tokens' => ['Ça', 'va', 'bien']],
            ], [
                'title' => 'Dialog sapaan Prancis',
                'segments' => ['Ari: Bonjour, Rina.', 'Rina: Bonjour, Ari.', 'Ari: Comment ça va ?', 'Rina: Ça va bien, merci.'],
                'questions' => [
                    ['question' => 'Apa sapaan yang digunakan Ari?', 'answer' => 'Bonjour', 'wrong' => ['Merci', 'Pardon', 'Au revoir'], 'explanation' => 'Ari membuka percakapan dengan Bonjour.'],
                    ['question' => 'Bagaimana kabar Rina?', 'answer' => 'Rina baik-baik saja', 'wrong' => ['Rina marah', 'Rina pulang', 'Rina meminta maaf'], 'explanation' => 'Rina menjawab Ça va bien.'],
                ],
            ], [
                'title' => 'Dialog perkenalan Prancis',
                'segments' => ['Rina: Bonjour, je m\'appelle Rina.', 'Ari: Bonjour, je m\'appelle Ari.', 'Rina: Enchanté.', 'Ari: Enchanté aussi.'],
                'questions' => [
                    ['question' => 'Siapa nama orang pertama?', 'answer' => 'Rina', 'wrong' => ['Ari', 'Guru', 'Teman'], 'explanation' => 'Orang pertama mengatakan je m\'appelle Rina.'],
                    ['question' => 'Apa arti Enchanté?', 'answer' => 'Senang berkenalan', 'wrong' => ['Sampai jumpa', 'Terima kasih', 'Selamat malam'], 'explanation' => 'Enchanté dipakai saat berkenalan.'],
                ],
            ]),
        ];
    }

    private function language(
        string $slug,
        string $name,
        array $greetings,
        array $introductions,
        array $greetingSentences,
        array $introSentences,
        array $greetingStory,
        array $introStory,
    ): array {
        return [
            'slug' => $slug,
            'name' => $name,
            'greetings' => $greetings,
            'introductions' => $introductions,
            'greeting_sentences' => $greetingSentences,
            'intro_sentences' => $introSentences,
            'greeting_story' => $greetingStory,
            'intro_story' => $introStory,
        ];
    }
}
