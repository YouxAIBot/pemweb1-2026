<?php

namespace Database\Seeders;

use App\Models\LearningLanguage;
use App\Models\LearningLevel;
use App\Models\LearningPart;
use App\Models\LearningQuestion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StarterLanguageQuestionBankSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            foreach ($this->languageBank() as $languageData) {
                $language = LearningLanguage::updateOrCreate(
                    ['slug' => $languageData['slug']],
                    [
                        'name' => $languageData['name'],
                        'native_name' => $languageData['native_name'],
                        'flag_label' => $languageData['flag_label'],
                        'description' => $languageData['description'],
                        'accent_color' => $languageData['accent_color'],
                        'sort_order' => $languageData['sort_order'],
                        'is_active' => true,
                    ],
                );

                $part = LearningPart::updateOrCreate(
                    [
                        'learning_language_id' => $language->id,
                        'slug' => 'bagian-1-introduction',
                    ],
                    [
                        'title' => 'Bagian 1 Dasar ' . $languageData['name'],
                        'subtitle' => 'Mulai dari salam, kosakata, dan kalimat harian.',
                        'description' => 'Starter lesson untuk pemula agar pengguna bisa langsung latihan tanpa admin membuat soal manual dari nol.',
                        'badge_text' => 'Bagian 1',
                        'level_number' => 1,
                        'sort_order' => 1,
                        'is_active' => true,
                    ],
                );

                foreach ($languageData['levels'] as $levelIndex => $levelData) {
                    $level = LearningLevel::updateOrCreate(
                        [
                            'learning_part_id' => $part->id,
                            'slug' => $levelData['slug'],
                        ],
                        [
                            'title' => $levelData['title'],
                            'type' => 'mixed',
                            'short_label' => (string) ($levelIndex + 1),
                            'description' => $levelData['description'],
                            'sort_order' => $levelIndex + 1,
                            'xp_reward' => 20,
                            'passing_score' => 70,
                            'position_x' => $levelData['position_x'],
                            'position_y' => $levelData['position_y'],
                            'is_active' => true,
                            'settings' => ['seed_source' => 'starter_language_question_bank'],
                        ],
                    );

                    foreach ($levelData['questions'] as $questionIndex => $questionData) {
                        $this->seedQuestion($level, $questionData, $questionIndex + 1);
                    }
                }
            }
        });
    }

    private function seedQuestion(LearningLevel $level, array $data, int $sortOrder): void
    {
        $settings = $data['settings'] ?? [];
        $settings['seed_source'] = 'starter_language_question_bank';

        $question = LearningQuestion::updateOrCreate(
            [
                'learning_level_id' => $level->id,
                'question_text' => $data['question_text'],
            ],
            [
                'type' => $data['type'] ?? 'multiple_choice',
                'instruction' => $data['instruction'] ?? 'Pilih jawaban yang paling tepat.',
                'correct_answer' => $data['correct_answer'] ?? null,
                'explanation' => $data['explanation'] ?? null,
                'points' => $data['points'] ?? 10,
                'time_limit' => $data['time_limit'] ?? 30,
                'sort_order' => $sortOrder,
                'is_active' => true,
                'settings' => $settings,
            ],
        );

        $question->options()->delete();

        foreach (($data['options'] ?? []) as $optionIndex => $option) {
            $question->options()->create([
                'option_text' => $option['text'],
                'is_correct' => (bool) ($option['correct'] ?? false),
                'sort_order' => $optionIndex + 1,
            ]);
        }
    }

    private function languageBank(): array
    {
        return [
            [
                'name' => 'Inggris',
                'slug' => 'inggris',
                'native_name' => 'Hello',
                'flag_label' => 'EN',
                'description' => 'Vocabulary, grammar, listening, dan real-case practice.',
                'accent_color' => '#66e8f7',
                'sort_order' => 1,
                'levels' => [
                    $this->level(
                        'level-1-introduction',
                        'Level 1 - Salam Dasar',
                        'Mengenal salam, ucapan terima kasih, dan respons pendek.',
                        16,
                        24,
                        [
                            $this->choice('Apa arti kata "Hello"?', 'Halo', ['Sampai jumpa', 'Terima kasih', 'Maaf'], '"Hello" adalah sapaan umum yang berarti halo.'),
                            $this->choice('Bahasa Inggris dari "terima kasih" adalah ...', 'Thank you', ['Good night', 'Sorry', 'See you'], '"Thank you" digunakan untuk mengucapkan terima kasih.'),
                            $this->choice('Sapaan yang tepat untuk pagi hari adalah ...', 'Good morning', ['Good night', 'Goodbye', 'See you later'], '"Good morning" berarti selamat pagi.'),
                            $this->match('Cocokkan sapaan bahasa Inggris dengan artinya.', [
                                ['left' => 'Hello', 'right' => 'Halo'],
                                ['left' => 'Thank you', 'right' => 'Terima kasih'],
                                ['left' => 'Goodbye', 'right' => 'Sampai jumpa'],
                                ['left' => 'Sorry', 'right' => 'Maaf'],
                            ]),
                        ],
                    ),
                    $this->level(
                        'level-2-basic-vocabulary',
                        'Level 2 - Kosakata Dasar',
                        'Latihan benda, orang, dan tempat yang sering dipakai.',
                        45,
                        42,
                        [
                            $this->choice('Apa arti kata "water"?', 'Air', ['Api', 'Buku', 'Sekolah'], '"Water" berarti air.'),
                            $this->choice('Bahasa Inggris dari "buku" adalah ...', 'Book', ['Food', 'Door', 'Chair'], '"Book" berarti buku.'),
                            $this->choice('Kata yang berarti "guru" adalah ...', 'Teacher', ['Student', 'Friend', 'House'], '"Teacher" berarti guru.'),
                            $this->match('Cocokkan kosakata bahasa Inggris berikut.', [
                                ['left' => 'School', 'right' => 'Sekolah'],
                                ['left' => 'Friend', 'right' => 'Teman'],
                                ['left' => 'Food', 'right' => 'Makanan'],
                                ['left' => 'Book', 'right' => 'Buku'],
                            ]),
                        ],
                    ),
                    $this->level(
                        'level-3-simple-sentence',
                        'Level 3 - Kalimat Sederhana',
                        'Menyusun makna kalimat harian dengan struktur dasar.',
                        72,
                        28,
                        [
                            $this->choice('Kalimat yang benar untuk "Saya adalah siswa" adalah ...', 'I am a student.', ['I student am.', 'I is a student.', 'Me am student.'], 'Subject "I" memakai "am".'),
                            $this->choice('Lengkapi kalimat: She ____ a book every night.', 'reads', ['read', 'reading', 'are read'], 'Subject "she" pada simple present memakai verb+s.'),
                            $this->choice('Respons yang tepat untuk "How are you?" adalah ...', 'I am fine, thank you.', ['It is a book.', 'At school.', 'See you yesterday.'], '"How are you?" menanyakan kabar.'),
                            $this->choice('Kamu ingin meminta air dengan sopan. Pilih kalimat yang tepat.', 'Can I have water, please?', ['Give water now!', 'Water me fast!', 'I water you!'], 'Kalimat ini sopan karena memakai "Can I have..." dan "please".', 'multiple_choice', 'Baca situasi lalu pilih respons paling natural.'),
                        ],
                    ),
                ],
            ],
            [
                'name' => 'Mandarin',
                'slug' => 'mandarin',
                'native_name' => '你好',
                'flag_label' => 'CN',
                'description' => 'Grammar, listening, dan percakapan dasar Mandarin untuk pemula.',
                'accent_color' => '#6e7cf7',
                'sort_order' => 2,
                'levels' => [
                    $this->level('level-1-introduction', 'Level 1 - Salam Mandarin', 'Mengenal salam dan ungkapan sopan paling dasar.', 16, 24, [
                        $this->choice('Apa arti "你好"?', 'Halo', ['Terima kasih', 'Selamat tinggal', 'Buku'], '"你好" berarti halo atau hai.'),
                        $this->choice('Mandarin dari "terima kasih" adalah ...', '谢谢', ['水', '老师', '再见'], '"谢谢" berarti terima kasih.'),
                        $this->choice('Ungkapan untuk "sampai jumpa" adalah ...', '再见', ['你好', '学生', '茶'], '"再见" berarti sampai jumpa.'),
                        $this->match('Cocokkan salam Mandarin berikut.', [
                            ['left' => '你好', 'right' => 'Halo'],
                            ['left' => '谢谢', 'right' => 'Terima kasih'],
                            ['left' => '再见', 'right' => 'Sampai jumpa'],
                            ['left' => '不客气', 'right' => 'Sama-sama'],
                        ]),
                    ]),
                    $this->level('level-2-basic-vocabulary', 'Level 2 - Kosakata Mandarin', 'Latihan kosakata benda dan orang sehari-hari.', 45, 42, [
                        $this->choice('Apa arti "水"?', 'Air', ['Buku', 'Guru', 'Makanan'], '"水" berarti air.'),
                        $this->choice('Mandarin dari "buku" adalah ...', '书', ['茶', '饭', '我'], '"书" berarti buku.'),
                        $this->choice('Kata "学生" berarti ...', 'Siswa', ['Guru', 'Teman', 'Rumah'], '"学生" berarti siswa atau pelajar.'),
                        $this->match('Cocokkan kosakata Mandarin berikut.', [
                            ['left' => '老师', 'right' => 'Guru'],
                            ['left' => '学生', 'right' => 'Siswa'],
                            ['left' => '茶', 'right' => 'Teh'],
                            ['left' => '饭', 'right' => 'Nasi/makanan'],
                        ]),
                    ]),
                    $this->level('level-3-simple-sentence', 'Level 3 - Kalimat Mandarin', 'Memahami kalimat Mandarin sangat sederhana.', 72, 28, [
                        $this->choice('Kalimat "我是学生" berarti ...', 'Saya adalah siswa.', ['Saya minum air.', 'Dia guru.', 'Ini buku.'], '"我" berarti saya, "是" berarti adalah, dan "学生" berarti siswa.'),
                        $this->choice('Mandarin dari "Saya minum air" adalah ...', '我喝水', ['我是老师', '你好吗', '她看书'], '"喝" berarti minum dan "水" berarti air.'),
                        $this->choice('Ungkapan untuk menanyakan "Apa kabar?" adalah ...', '你好吗？', ['谢谢', '再见', '不客气'], '"你好吗？" digunakan untuk menanyakan kabar.'),
                        $this->choice('Seseorang berkata "谢谢". Respons sopan yang tepat adalah ...', '不客气', ['我是学生', '喝水', '老师'], '"不客气" berarti sama-sama.', 'multiple_choice', 'Baca situasi lalu pilih respons paling natural.'),
                    ]),
                ],
            ],
            [
                'name' => 'Korea',
                'slug' => 'korea',
                'native_name' => '안녕',
                'flag_label' => 'KR',
                'description' => 'Hangul, kosakata, dan dialog sehari-hari.',
                'accent_color' => '#9d7cff',
                'sort_order' => 3,
                'levels' => [
                    $this->level('level-1-introduction', 'Level 1 - Salam Korea', 'Mengenal sapaan sopan dan respons dasar.', 16, 24, [
                        $this->choice('Apa arti "안녕하세요"?', 'Halo', ['Maaf', 'Buku', 'Air'], '"안녕하세요" adalah salam sopan yang berarti halo.'),
                        $this->choice('Korea dari "terima kasih" adalah ...', '감사합니다', ['물', '책', '학교'], '"감사합니다" berarti terima kasih.'),
                        $this->choice('Ungkapan santai untuk "halo/dah" adalah ...', '안녕', ['학생', '친구', '선생님'], '"안녕" bisa berarti halo atau dah dalam situasi santai.'),
                        $this->match('Cocokkan salam Korea berikut.', [
                            ['left' => '안녕하세요', 'right' => 'Halo'],
                            ['left' => '감사합니다', 'right' => 'Terima kasih'],
                            ['left' => '미안해요', 'right' => 'Maaf'],
                            ['left' => '안녕', 'right' => 'Halo/dah'],
                        ]),
                    ]),
                    $this->level('level-2-basic-vocabulary', 'Level 2 - Kosakata Korea', 'Latihan kata benda dan orang dalam bahasa Korea.', 45, 42, [
                        $this->choice('Apa arti "물"?', 'Air', ['Buku', 'Teman', 'Sekolah'], '"물" berarti air.'),
                        $this->choice('Korea dari "buku" adalah ...', '책', ['밥', '집', '차'], '"책" berarti buku.'),
                        $this->choice('Kata "학생" berarti ...', 'Siswa', ['Guru', 'Teman', 'Makanan'], '"학생" berarti siswa.'),
                        $this->match('Cocokkan kosakata Korea berikut.', [
                            ['left' => '학교', 'right' => 'Sekolah'],
                            ['left' => '친구', 'right' => 'Teman'],
                            ['left' => '선생님', 'right' => 'Guru'],
                            ['left' => '밥', 'right' => 'Nasi/makanan'],
                        ]),
                    ]),
                    $this->level('level-3-simple-sentence', 'Level 3 - Kalimat Korea', 'Memahami kalimat Korea pemula.', 72, 28, [
                        $this->choice('Kalimat "저는 학생이에요" berarti ...', 'Saya adalah siswa.', ['Saya minum air.', 'Ini buku.', 'Dia guru.'], '"저는" berarti saya dan "학생이에요" berarti adalah siswa.'),
                        $this->choice('Korea dari "Saya minum air" adalah ...', '저는 물을 마셔요', ['저는 학생이에요', '책이에요', '감사합니다'], '"물을 마셔요" berarti minum air.'),
                        $this->choice('Respons natural untuk "감사합니다" adalah ...', '천만에요', ['학교', '책', '물'], '"천만에요" berarti sama-sama.'),
                        $this->choice('Kamu bertemu guru. Sapaan yang paling sopan adalah ...', '안녕하세요', ['안녕', '밥', '책'], '"안녕하세요" lebih sopan untuk guru atau orang yang dihormati.', 'multiple_choice', 'Baca situasi lalu pilih respons paling natural.'),
                    ]),
                ],
            ],
            [
                'name' => 'Jepang',
                'slug' => 'jepang',
                'native_name' => 'こんにちは',
                'flag_label' => 'JP',
                'description' => 'Hiragana, frasa dasar, dan budaya praktis.',
                'accent_color' => '#ff9bb3',
                'sort_order' => 4,
                'levels' => [
                    $this->level('level-1-introduction', 'Level 1 - Salam Jepang', 'Mengenal sapaan dan ungkapan sopan sederhana.', 16, 24, [
                        $this->choice('Apa arti "こんにちは"?', 'Halo/selamat siang', ['Terima kasih', 'Buku', 'Air'], '"こんにちは" adalah salam umum di siang hari.'),
                        $this->choice('Jepang dari "terima kasih" adalah ...', 'ありがとう', ['水', '本', '学生'], '"ありがとう" berarti terima kasih.'),
                        $this->choice('Ungkapan untuk "sampai jumpa" adalah ...', 'さようなら', ['先生', '水', 'はい'], '"さようなら" berarti sampai jumpa atau selamat tinggal.'),
                        $this->match('Cocokkan salam Jepang berikut.', [
                            ['left' => 'こんにちは', 'right' => 'Halo'],
                            ['left' => 'ありがとう', 'right' => 'Terima kasih'],
                            ['left' => 'さようなら', 'right' => 'Sampai jumpa'],
                            ['left' => 'すみません', 'right' => 'Permisi/maaf'],
                        ]),
                    ]),
                    $this->level('level-2-basic-vocabulary', 'Level 2 - Kosakata Jepang', 'Latihan kata benda dan orang dalam bahasa Jepang.', 45, 42, [
                        $this->choice('Apa arti "水"?', 'Air', ['Buku', 'Guru', 'Sekolah'], '"水" berarti air.'),
                        $this->choice('Jepang dari "buku" adalah ...', '本', ['先生', '学生', '友だち'], '"本" berarti buku.'),
                        $this->choice('Kata "学生" berarti ...', 'Siswa', ['Guru', 'Teman', 'Rumah'], '"学生" berarti siswa atau pelajar.'),
                        $this->match('Cocokkan kosakata Jepang berikut.', [
                            ['left' => '先生', 'right' => 'Guru'],
                            ['left' => '学生', 'right' => 'Siswa'],
                            ['left' => '学校', 'right' => 'Sekolah'],
                            ['left' => '友だち', 'right' => 'Teman'],
                        ]),
                    ]),
                    $this->level('level-3-simple-sentence', 'Level 3 - Kalimat Jepang', 'Memahami kalimat Jepang pemula.', 72, 28, [
                        $this->choice('Kalimat "私は学生です" berarti ...', 'Saya adalah siswa.', ['Saya minum air.', 'Ini buku.', 'Dia guru.'], '"私は" berarti saya dan "学生です" berarti adalah siswa.'),
                        $this->choice('Jepang dari "Saya minum air" adalah ...', '水を飲みます', ['私は先生です', 'ありがとう', '本です'], '"飲みます" berarti minum.'),
                        $this->choice('Ungkapan untuk "Apa kabar?" adalah ...', 'お元気ですか？', ['さようなら', '本', '水'], '"お元気ですか？" digunakan untuk menanyakan kabar.'),
                        $this->choice('Seseorang berkata "ありがとう". Respons sopan yang tepat adalah ...', 'どういたしまして', ['学校', '水を飲みます', '学生'], '"どういたしまして" berarti sama-sama.', 'multiple_choice', 'Baca situasi lalu pilih respons paling natural.'),
                    ]),
                ],
            ],
            [
                'name' => 'Arab',
                'slug' => 'arab',
                'native_name' => 'مرحبا',
                'flag_label' => 'AR',
                'description' => 'Huruf, kosakata, dan kalimat harian bahasa Arab.',
                'accent_color' => '#49d38b',
                'sort_order' => 5,
                'levels' => [
                    $this->level('level-1-introduction', 'Level 1 - Salam Arab', 'Mengenal salam dan ungkapan sopan dasar.', 16, 24, [
                        $this->choice('Apa arti "مرحبا"?', 'Halo', ['Terima kasih', 'Buku', 'Air'], '"مرحبا" berarti halo.'),
                        $this->choice('Arab dari "terima kasih" adalah ...', 'شكرا', ['ماء', 'كتاب', 'طالب'], '"شكرا" berarti terima kasih.'),
                        $this->choice('Ungkapan untuk "selamat tinggal" adalah ...', 'وداعا', ['مرحبا', 'معلم', 'بيت'], '"وداعا" berarti selamat tinggal.'),
                        $this->match('Cocokkan salam Arab berikut.', [
                            ['left' => 'مرحبا', 'right' => 'Halo'],
                            ['left' => 'شكرا', 'right' => 'Terima kasih'],
                            ['left' => 'وداعا', 'right' => 'Selamat tinggal'],
                            ['left' => 'عفوا', 'right' => 'Sama-sama/maaf'],
                        ]),
                    ]),
                    $this->level('level-2-basic-vocabulary', 'Level 2 - Kosakata Arab', 'Latihan kata benda dan orang dalam bahasa Arab.', 45, 42, [
                        $this->choice('Apa arti "ماء"?', 'Air', ['Buku', 'Guru', 'Sekolah'], '"ماء" berarti air.'),
                        $this->choice('Arab dari "buku" adalah ...', 'كتاب', ['طالب', 'معلم', 'بيت'], '"كتاب" berarti buku.'),
                        $this->choice('Kata "طالب" berarti ...', 'Siswa', ['Guru', 'Teman', 'Makanan'], '"طالب" berarti siswa.'),
                        $this->match('Cocokkan kosakata Arab berikut.', [
                            ['left' => 'معلم', 'right' => 'Guru'],
                            ['left' => 'طالب', 'right' => 'Siswa'],
                            ['left' => 'مدرسة', 'right' => 'Sekolah'],
                            ['left' => 'كتاب', 'right' => 'Buku'],
                        ]),
                    ]),
                    $this->level('level-3-simple-sentence', 'Level 3 - Kalimat Arab', 'Memahami kalimat Arab pemula.', 72, 28, [
                        $this->choice('Kalimat "أنا طالب" berarti ...', 'Saya adalah siswa.', ['Saya minum air.', 'Ini buku.', 'Dia guru.'], '"أنا" berarti saya dan "طالب" berarti siswa.'),
                        $this->choice('Arab dari "Saya minum air" adalah ...', 'أشرب الماء', ['أنا معلم', 'شكرا', 'هذا كتاب'], '"أشرب" berarti saya minum dan "الماء" berarti air.'),
                        $this->choice('Ungkapan untuk "Apa kabar?" adalah ...', 'كيف حالك؟', ['وداعا', 'كتاب', 'مدرسة'], '"كيف حالك؟" digunakan untuk menanyakan kabar.'),
                        $this->choice('Seseorang berkata "شكرا". Respons sopan yang tepat adalah ...', 'عفوا', ['ماء', 'طالب', 'بيت'], '"عفوا" dapat digunakan sebagai sama-sama.', 'multiple_choice', 'Baca situasi lalu pilih respons paling natural.'),
                    ]),
                ],
            ],
            [
                'name' => 'Prancis',
                'slug' => 'prancis',
                'native_name' => 'Bonjour',
                'flag_label' => 'FR',
                'description' => 'Frasa populer dan percakapan ringan bahasa Prancis.',
                'accent_color' => '#fff3a8',
                'sort_order' => 6,
                'levels' => [
                    $this->level('level-1-introduction', 'Level 1 - Salam Prancis', 'Mengenal salam dan ungkapan sopan sederhana.', 16, 24, [
                        $this->choice('Apa arti "Bonjour"?', 'Halo/selamat pagi', ['Terima kasih', 'Buku', 'Air'], '"Bonjour" adalah salam umum dalam bahasa Prancis.'),
                        $this->choice('Prancis dari "terima kasih" adalah ...', 'Merci', ['Eau', 'Livre', 'Ami'], '"Merci" berarti terima kasih.'),
                        $this->choice('Ungkapan untuk "sampai jumpa" adalah ...', 'Au revoir', ['Bonjour', 'Professeur', 'Maison'], '"Au revoir" berarti sampai jumpa.'),
                        $this->match('Cocokkan salam Prancis berikut.', [
                            ['left' => 'Bonjour', 'right' => 'Halo'],
                            ['left' => 'Merci', 'right' => 'Terima kasih'],
                            ['left' => 'Au revoir', 'right' => 'Sampai jumpa'],
                            ['left' => 'Pardon', 'right' => 'Maaf/permisi'],
                        ]),
                    ]),
                    $this->level('level-2-basic-vocabulary', 'Level 2 - Kosakata Prancis', 'Latihan kata benda dan orang dalam bahasa Prancis.', 45, 42, [
                        $this->choice('Apa arti "eau"?', 'Air', ['Buku', 'Guru', 'Sekolah'], '"Eau" berarti air.'),
                        $this->choice('Prancis dari "buku" adalah ...', 'Livre', ['Maison', 'Ami', 'Ecole'], '"Livre" berarti buku.'),
                        $this->choice('Kata "étudiant" berarti ...', 'Siswa', ['Guru', 'Teman', 'Makanan'], '"Étudiant" berarti siswa atau mahasiswa.'),
                        $this->match('Cocokkan kosakata Prancis berikut.', [
                            ['left' => 'Professeur', 'right' => 'Guru'],
                            ['left' => 'Étudiant', 'right' => 'Siswa'],
                            ['left' => 'École', 'right' => 'Sekolah'],
                            ['left' => 'Ami', 'right' => 'Teman'],
                        ]),
                    ]),
                    $this->level('level-3-simple-sentence', 'Level 3 - Kalimat Prancis', 'Memahami kalimat Prancis pemula.', 72, 28, [
                        $this->choice('Kalimat "Je suis étudiant" berarti ...', 'Saya adalah siswa.', ['Saya minum air.', 'Ini buku.', 'Dia guru.'], '"Je suis" berarti saya adalah.'),
                        $this->choice('Prancis dari "Saya minum air" adalah ...', "Je bois de l'eau", ['Je suis professeur', 'Merci', 'Ceci est un livre'], '"Je bois" berarti saya minum.'),
                        $this->choice('Ungkapan untuk "Apa kabar?" adalah ...', 'Comment ça va ?', ['Au revoir', 'Livre', 'Eau'], '"Comment ça va ?" digunakan untuk menanyakan kabar.'),
                        $this->choice('Seseorang berkata "Merci". Respons sopan yang tepat adalah ...', 'De rien', ['Ecole', 'Livre', 'Bonjour'], '"De rien" berarti sama-sama.', 'multiple_choice', 'Baca situasi lalu pilih respons paling natural.'),
                    ]),
                ],
            ],
        ];
    }

    private function level(string $slug, string $title, string $description, int $positionX, int $positionY, array $questions): array
    {
        return [
            'slug' => $slug,
            'title' => $title,
            'description' => $description,
            'position_x' => $positionX,
            'position_y' => $positionY,
            'questions' => $questions,
        ];
    }

    private function choice(
        string $question,
        string $correct,
        array $wrongOptions,
        string $explanation,
        string $type = 'multiple_choice',
        string $instruction = 'Pilih jawaban yang paling tepat.'
    ): array {
        $options = array_map(fn (string $option): array => ['text' => $option, 'correct' => false], $wrongOptions);
        $options[] = ['text' => $correct, 'correct' => true];

        return [
            'type' => $type,
            'instruction' => $instruction,
            'question_text' => $question,
            'correct_answer' => $correct,
            'explanation' => $explanation,
            'options' => $options,
        ];
    }

    private function match(string $question, array $pairs): array
    {
        return [
            'type' => 'word_match',
            'instruction' => 'Cocokkan kata dengan arti yang benar.',
            'question_text' => $question,
            'correct_answer' => 'Semua pasangan kata cocok.',
            'explanation' => 'Bagus. Semua pasangan kata sudah sesuai.',
            'settings' => [
                'word_pairs' => $pairs,
            ],
        ];
    }
}
