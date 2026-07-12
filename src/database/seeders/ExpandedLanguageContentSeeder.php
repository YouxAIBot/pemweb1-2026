<?php

namespace Database\Seeders;

use App\Models\LearningLanguage;
use App\Models\LearningLevel;
use App\Models\LearningPart;
use App\Models\LearningQuestion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExpandedLanguageContentSeeder extends Seeder
{
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

                foreach ($this->partsFor($languageData) as $partData) {
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
                            'settings' => ['seed_source' => 'expanded_language_content'],
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
                                'type' => 'mixed',
                                'short_label' => $partData['level_number'] . '.' . ($levelIndex + 1),
                                'description' => $levelData['description'],
                                'sort_order' => $levelIndex + 1,
                                'xp_reward' => $levelData['xp_reward'],
                                'passing_score' => 70,
                                'position_x' => $levelData['position_x'],
                                'position_y' => $levelData['position_y'],
                                'is_active' => true,
                                'settings' => [
                                    'seed_source' => 'expanded_language_content',
                                    'difficulty' => $levelData['difficulty'],
                                ],
                            ],
                        );

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
        $settings = $data['settings'] ?? [];
        $settings['seed_source'] = 'expanded_language_content';
        $settings['difficulty'] = $data['difficulty'] ?? 'normal';

        $question = LearningQuestion::updateOrCreate(
            [
                'learning_level_id' => $level->id,
                'question_text' => $data['question_text'],
            ],
            [
                'type' => $data['type'] ?? 'multiple_choice',
                'instruction' => $data['instruction'] ?? 'Pilih jawaban yang paling tepat.',
                'correct_answer' => $data['correct_answer'],
                'explanation' => $data['explanation'],
                'points' => $data['points'] ?? 10,
                'time_limit' => $data['time_limit'] ?? 30,
                'sort_order' => $sortOrder,
                'is_active' => true,
                'settings' => $settings,
            ],
        );

        $question->options()->delete();

        foreach ($data['options'] as $optionIndex => $option) {
            $question->options()->create([
                'option_text' => $option['text'],
                'is_correct' => (bool) ($option['correct'] ?? false),
                'sort_order' => $optionIndex + 1,
            ]);
        }
    }

    private function partsFor(array $language): array
    {
        return [
            [
                'slug' => 'bagian-2-kosakata-harian',
                'title' => 'Bagian 2 Kosakata Harian ' . $language['name'],
                'subtitle' => 'Orang, tempat, dan waktu.',
                'description' => 'Latihan tambahan untuk memperkaya kosakata yang sering muncul di level, turnamen, dan duel.',
                'badge_text' => 'Bagian 2',
                'level_number' => 2,
                'sort_order' => 2,
                'levels' => [
                    $this->wordLevel($language, 'people', 'level-1-orang-keluarga', 'Level 1 - Orang & Keluarga', 'Mengenal kata untuk keluarga, teman, guru, dan pelajar.', 18, 24, 'easy'),
                    $this->wordLevel($language, 'places', 'level-2-tempat-sehari-hari', 'Level 2 - Tempat Sehari-hari', 'Mengenal tempat umum seperti sekolah, pasar, dan stasiun.', 50, 45, 'normal'),
                    $this->wordLevel($language, 'time', 'level-3-waktu-dasar', 'Level 3 - Waktu Dasar', 'Mengenal kata waktu untuk membangun kalimat sederhana.', 78, 30, 'normal'),
                ],
            ],
            [
                'slug' => 'bagian-3-percakapan-situasi',
                'title' => 'Bagian 3 Percakapan & Situasi ' . $language['name'],
                'subtitle' => 'Frasa praktis untuk konteks nyata.',
                'description' => 'Bagian ini menambah latihan pemahaman kalimat agar turnamen dan duel tidak terasa mengulang soal yang sama.',
                'badge_text' => 'Bagian 3',
                'level_number' => 3,
                'sort_order' => 3,
                'levels' => [
                    $this->phraseLevel($language, 'daily', 'level-1-kalimat-harian', 'Level 1 - Kalimat Harian', 'Memahami frasa pendek untuk kebutuhan harian.', 18, 24, 'normal'),
                    $this->phraseLevel($language, 'travel', 'level-2-jalan-belanja', 'Level 2 - Jalan & Belanja', 'Latihan frasa saat bepergian, bertanya arah, dan membeli sesuatu.', 50, 45, 'normal'),
                    $this->phraseLevel($language, 'challenge', 'level-3-tantangan-konteks', 'Level 3 - Tantangan Konteks', 'Kalimat lebih panjang untuk latihan kompetitif tingkat sulit.', 78, 30, 'hard'),
                ],
            ],
        ];
    }

    private function wordLevel(array $language, string $group, string $slug, string $title, string $description, int $x, int $y, string $difficulty): array
    {
        $items = $language['vocabulary'][$group];
        $allItems = $this->flatItems($language['vocabulary']);
        $questions = [];

        foreach ($items as $index => $item) {
            if ($index % 2 === 0) {
                $questions[] = $this->choice(
                    'Apa arti "' . $item['foreign'] . '"?',
                    $item['translation'],
                    $this->distractors($allItems, 'translation', $item['translation']),
                    '"' . $item['foreign'] . '" berarti ' . $item['translation'] . '.',
                    $difficulty,
                );
            } else {
                $questions[] = $this->choice(
                    'Terjemahan ' . $language['name'] . ' untuk "' . $item['translation'] . '" adalah ...',
                    $item['foreign'],
                    $this->distractors($allItems, 'foreign', $item['foreign']),
                    'Jawaban yang tepat adalah "' . $item['foreign'] . '".',
                    $difficulty,
                );
            }
        }

        return $this->level($slug, $title, $description, $x, $y, $difficulty, $questions);
    }

    private function phraseLevel(array $language, string $group, string $slug, string $title, string $description, int $x, int $y, string $difficulty): array
    {
        $items = $language['phrases'][$group];
        $allItems = $this->flatItems($language['phrases']);
        $questions = [];

        foreach ($items as $index => $item) {
            if ($index % 2 === 0) {
                $questions[] = $this->choice(
                    'Apa arti kalimat "' . $item['foreign'] . '"?',
                    $item['translation'],
                    $this->distractors($allItems, 'translation', $item['translation']),
                    'Kalimat "' . $item['foreign'] . '" berarti ' . $item['translation'] . '.',
                    $difficulty,
                    'Baca kalimat lalu pilih arti yang paling tepat.',
                    'multiple_choice',
                );
            } else {
                $questions[] = $this->choice(
                    'Kalimat ' . $language['name'] . ' untuk "' . $item['translation'] . '" adalah ...',
                    $item['foreign'],
                    $this->distractors($allItems, 'foreign', $item['foreign']),
                    'Ungkapan yang tepat adalah "' . $item['foreign'] . '".',
                    $difficulty,
                    'Pilih kalimat yang paling natural.',
                    'multiple_choice',
                );
            }
        }

        return $this->level($slug, $title, $description, $x, $y, $difficulty, $questions);
    }

    private function level(string $slug, string $title, string $description, int $positionX, int $positionY, string $difficulty, array $questions): array
    {
        $xp = match ($difficulty) {
            'hard' => 30,
            'normal' => 24,
            default => 18,
        };

        return [
            'slug' => $slug,
            'title' => $title,
            'description' => $description,
            'position_x' => $positionX,
            'position_y' => $positionY,
            'difficulty' => $difficulty,
            'xp_reward' => $xp,
            'questions' => $questions,
        ];
    }

    private function choice(
        string $question,
        string $correct,
        array $wrongOptions,
        string $explanation,
        string $difficulty,
        string $instruction = 'Pilih jawaban yang paling tepat.',
        string $type = 'multiple_choice',
    ): array {
        $options = collect($wrongOptions)
            ->filter(fn (string $option): bool => $option !== $correct)
            ->unique()
            ->take(3)
            ->map(fn (string $option): array => ['text' => $option, 'correct' => false])
            ->values();

        $options->push(['text' => $correct, 'correct' => true]);

        return [
            'type' => $type,
            'instruction' => $instruction,
            'question_text' => $question,
            'correct_answer' => $correct,
            'explanation' => $explanation,
            'points' => $difficulty === 'hard' ? 20 : 10,
            'time_limit' => $difficulty === 'hard' ? 45 : 30,
            'difficulty' => $difficulty,
            'options' => $options->shuffle()->values()->all(),
        ];
    }

    private function flatItems(array $groups): array
    {
        return collect($groups)->flatMap(fn (array $items): array => $items)->values()->all();
    }

    private function distractors(array $items, string $field, string $correct): array
    {
        return collect($items)
            ->pluck($field)
            ->filter(fn (string $value): bool => $value !== $correct)
            ->unique()
            ->shuffle()
            ->take(3)
            ->values()
            ->all();
    }

    private function languages(): array
    {
        return [
            [
                'slug' => 'inggris',
                'name' => 'Inggris',
                'vocabulary' => [
                    'people' => [
                        ['foreign' => 'mother', 'translation' => 'ibu'],
                        ['foreign' => 'father', 'translation' => 'ayah'],
                        ['foreign' => 'friend', 'translation' => 'teman'],
                        ['foreign' => 'teacher', 'translation' => 'guru'],
                        ['foreign' => 'student', 'translation' => 'siswa'],
                    ],
                    'places' => [
                        ['foreign' => 'school', 'translation' => 'sekolah'],
                        ['foreign' => 'library', 'translation' => 'perpustakaan'],
                        ['foreign' => 'market', 'translation' => 'pasar'],
                        ['foreign' => 'station', 'translation' => 'stasiun'],
                        ['foreign' => 'hospital', 'translation' => 'rumah sakit'],
                    ],
                    'time' => [
                        ['foreign' => 'morning', 'translation' => 'pagi'],
                        ['foreign' => 'night', 'translation' => 'malam'],
                        ['foreign' => 'today', 'translation' => 'hari ini'],
                        ['foreign' => 'tomorrow', 'translation' => 'besok'],
                        ['foreign' => 'yesterday', 'translation' => 'kemarin'],
                    ],
                ],
                'phrases' => [
                    'daily' => [
                        ['foreign' => 'I am hungry', 'translation' => 'saya lapar'],
                        ['foreign' => 'I need help', 'translation' => 'saya butuh bantuan'],
                        ['foreign' => 'This is my book', 'translation' => 'ini buku saya'],
                        ['foreign' => 'She is my teacher', 'translation' => 'dia guru saya'],
                        ['foreign' => 'We study together', 'translation' => 'kami belajar bersama'],
                    ],
                    'travel' => [
                        ['foreign' => 'Where is the station?', 'translation' => 'di mana stasiun?'],
                        ['foreign' => 'How much is this?', 'translation' => 'berapa harganya?'],
                        ['foreign' => 'I want to buy water', 'translation' => 'saya ingin membeli air'],
                        ['foreign' => 'Turn left', 'translation' => 'belok kiri'],
                        ['foreign' => 'Go straight', 'translation' => 'jalan lurus'],
                    ],
                    'challenge' => [
                        ['foreign' => 'The room is cleaner than before', 'translation' => 'ruangan itu lebih bersih dari sebelumnya'],
                        ['foreign' => 'I have already finished my homework', 'translation' => 'saya sudah menyelesaikan PR saya'],
                        ['foreign' => 'Could you speak more slowly?', 'translation' => 'bisakah kamu bicara lebih pelan?'],
                        ['foreign' => 'They were waiting at the station', 'translation' => 'mereka sedang menunggu di stasiun'],
                        ['foreign' => 'I will call you after class', 'translation' => 'saya akan meneleponmu setelah kelas'],
                    ],
                ],
            ],
            [
                'slug' => 'mandarin',
                'name' => 'Mandarin',
                'vocabulary' => [
                    'people' => [
                        ['foreign' => '妈妈 (mama)', 'translation' => 'ibu'],
                        ['foreign' => '爸爸 (baba)', 'translation' => 'ayah'],
                        ['foreign' => '朋友 (pengyou)', 'translation' => 'teman'],
                        ['foreign' => '老师 (laoshi)', 'translation' => 'guru'],
                        ['foreign' => '学生 (xuesheng)', 'translation' => 'siswa'],
                    ],
                    'places' => [
                        ['foreign' => '学校 (xuexiao)', 'translation' => 'sekolah'],
                        ['foreign' => '图书馆 (tushuguan)', 'translation' => 'perpustakaan'],
                        ['foreign' => '市场 (shichang)', 'translation' => 'pasar'],
                        ['foreign' => '车站 (chezhan)', 'translation' => 'stasiun'],
                        ['foreign' => '医院 (yiyuan)', 'translation' => 'rumah sakit'],
                    ],
                    'time' => [
                        ['foreign' => '早上 (zaoshang)', 'translation' => 'pagi'],
                        ['foreign' => '晚上 (wanshang)', 'translation' => 'malam'],
                        ['foreign' => '今天 (jintian)', 'translation' => 'hari ini'],
                        ['foreign' => '明天 (mingtian)', 'translation' => 'besok'],
                        ['foreign' => '昨天 (zuotian)', 'translation' => 'kemarin'],
                    ],
                ],
                'phrases' => [
                    'daily' => [
                        ['foreign' => '我饿了 (wo e le)', 'translation' => 'saya lapar'],
                        ['foreign' => '我需要帮助 (wo xuyao bangzhu)', 'translation' => 'saya butuh bantuan'],
                        ['foreign' => '这是我的书 (zhe shi wo de shu)', 'translation' => 'ini buku saya'],
                        ['foreign' => '她是我的老师 (ta shi wo de laoshi)', 'translation' => 'dia guru saya'],
                        ['foreign' => '我们一起学习 (women yiqi xuexi)', 'translation' => 'kami belajar bersama'],
                    ],
                    'travel' => [
                        ['foreign' => '车站在哪里? (chezhan zai nali?)', 'translation' => 'di mana stasiun?'],
                        ['foreign' => '这个多少钱? (zhege duoshao qian?)', 'translation' => 'berapa harganya?'],
                        ['foreign' => '我要买水 (wo yao mai shui)', 'translation' => 'saya ingin membeli air'],
                        ['foreign' => '向左转 (xiang zuo zhuan)', 'translation' => 'belok kiri'],
                        ['foreign' => '一直走 (yizhi zou)', 'translation' => 'jalan lurus'],
                    ],
                    'challenge' => [
                        ['foreign' => '这个房间比以前干净 (zhege fangjian bi yiqian ganjing)', 'translation' => 'ruangan itu lebih bersih dari sebelumnya'],
                        ['foreign' => '我已经完成作业了 (wo yijing wancheng zuoye le)', 'translation' => 'saya sudah menyelesaikan PR saya'],
                        ['foreign' => '请说慢一点 (qing shuo man yidian)', 'translation' => 'tolong bicara lebih pelan'],
                        ['foreign' => '他们在车站等 (tamen zai chezhan deng)', 'translation' => 'mereka sedang menunggu di stasiun'],
                        ['foreign' => '下课后我给你打电话 (xiake hou wo gei ni da dianhua)', 'translation' => 'saya akan meneleponmu setelah kelas'],
                    ],
                ],
            ],
            [
                'slug' => 'korea',
                'name' => 'Korea',
                'vocabulary' => [
                    'people' => [
                        ['foreign' => '엄마 (eomma)', 'translation' => 'ibu'],
                        ['foreign' => '아빠 (appa)', 'translation' => 'ayah'],
                        ['foreign' => '친구 (chingu)', 'translation' => 'teman'],
                        ['foreign' => '선생님 (seonsaengnim)', 'translation' => 'guru'],
                        ['foreign' => '학생 (haksaeng)', 'translation' => 'siswa'],
                    ],
                    'places' => [
                        ['foreign' => '학교 (hakgyo)', 'translation' => 'sekolah'],
                        ['foreign' => '도서관 (doseogwan)', 'translation' => 'perpustakaan'],
                        ['foreign' => '시장 (sijang)', 'translation' => 'pasar'],
                        ['foreign' => '역 (yeok)', 'translation' => 'stasiun'],
                        ['foreign' => '병원 (byeongwon)', 'translation' => 'rumah sakit'],
                    ],
                    'time' => [
                        ['foreign' => '아침 (achim)', 'translation' => 'pagi'],
                        ['foreign' => '밤 (bam)', 'translation' => 'malam'],
                        ['foreign' => '오늘 (oneul)', 'translation' => 'hari ini'],
                        ['foreign' => '내일 (naeil)', 'translation' => 'besok'],
                        ['foreign' => '어제 (eoje)', 'translation' => 'kemarin'],
                    ],
                ],
                'phrases' => [
                    'daily' => [
                        ['foreign' => '배고파요 (baegopayo)', 'translation' => 'saya lapar'],
                        ['foreign' => '도움이 필요해요 (doumi piryohaeyo)', 'translation' => 'saya butuh bantuan'],
                        ['foreign' => '이것은 제 책이에요 (igeoseun je chaegieyo)', 'translation' => 'ini buku saya'],
                        ['foreign' => '그녀는 제 선생님이에요 (geunyeoneun je seonsaengnimieyo)', 'translation' => 'dia guru saya'],
                        ['foreign' => '우리는 같이 공부해요 (urineun gachi gongbuhaeyo)', 'translation' => 'kami belajar bersama'],
                    ],
                    'travel' => [
                        ['foreign' => '역이 어디예요? (yeogi eodiyeyo?)', 'translation' => 'di mana stasiun?'],
                        ['foreign' => '이거 얼마예요? (igeo eolmayeyo?)', 'translation' => 'berapa harganya?'],
                        ['foreign' => '물을 사고 싶어요 (mureul sago sipeoyo)', 'translation' => 'saya ingin membeli air'],
                        ['foreign' => '왼쪽으로 도세요 (oenjjogeuro doseyo)', 'translation' => 'belok kiri'],
                        ['foreign' => '똑바로 가세요 (ttokbaro gaseyo)', 'translation' => 'jalan lurus'],
                    ],
                    'challenge' => [
                        ['foreign' => '방이 전보다 더 깨끗해요 (bangi jeonboda deo kkaekkeuthaeyo)', 'translation' => 'ruangan itu lebih bersih dari sebelumnya'],
                        ['foreign' => '숙제를 이미 끝냈어요 (sukjereul imi kkeutnaesseoyo)', 'translation' => 'saya sudah menyelesaikan PR saya'],
                        ['foreign' => '조금 더 천천히 말해 주세요 (jogeum deo cheoncheonhi malhae juseyo)', 'translation' => 'tolong bicara lebih pelan'],
                        ['foreign' => '그들은 역에서 기다리고 있었어요 (geudeureun yeogeseo gidarigo isseosseoyo)', 'translation' => 'mereka sedang menunggu di stasiun'],
                        ['foreign' => '수업 후에 전화할게요 (sueop hue jeonhwahalgeyo)', 'translation' => 'saya akan meneleponmu setelah kelas'],
                    ],
                ],
            ],
            [
                'slug' => 'jepang',
                'name' => 'Jepang',
                'vocabulary' => [
                    'people' => [
                        ['foreign' => 'お母さん (okaasan)', 'translation' => 'ibu'],
                        ['foreign' => 'お父さん (otousan)', 'translation' => 'ayah'],
                        ['foreign' => '友だち (tomodachi)', 'translation' => 'teman'],
                        ['foreign' => '先生 (sensei)', 'translation' => 'guru'],
                        ['foreign' => '学生 (gakusei)', 'translation' => 'siswa'],
                    ],
                    'places' => [
                        ['foreign' => '学校 (gakkou)', 'translation' => 'sekolah'],
                        ['foreign' => '図書館 (toshokan)', 'translation' => 'perpustakaan'],
                        ['foreign' => '市場 (ichiba)', 'translation' => 'pasar'],
                        ['foreign' => '駅 (eki)', 'translation' => 'stasiun'],
                        ['foreign' => '病院 (byouin)', 'translation' => 'rumah sakit'],
                    ],
                    'time' => [
                        ['foreign' => '朝 (asa)', 'translation' => 'pagi'],
                        ['foreign' => '夜 (yoru)', 'translation' => 'malam'],
                        ['foreign' => '今日 (kyou)', 'translation' => 'hari ini'],
                        ['foreign' => '明日 (ashita)', 'translation' => 'besok'],
                        ['foreign' => '昨日 (kinou)', 'translation' => 'kemarin'],
                    ],
                ],
                'phrases' => [
                    'daily' => [
                        ['foreign' => 'お腹がすきました (onaka ga sukimashita)', 'translation' => 'saya lapar'],
                        ['foreign' => '助けが必要です (tasuke ga hitsuyou desu)', 'translation' => 'saya butuh bantuan'],
                        ['foreign' => 'これは私の本です (kore wa watashi no hon desu)', 'translation' => 'ini buku saya'],
                        ['foreign' => '彼女は私の先生です (kanojo wa watashi no sensei desu)', 'translation' => 'dia guru saya'],
                        ['foreign' => '私たちは一緒に勉強します (watashitachi wa issho ni benkyou shimasu)', 'translation' => 'kami belajar bersama'],
                    ],
                    'travel' => [
                        ['foreign' => '駅はどこですか? (eki wa doko desu ka?)', 'translation' => 'di mana stasiun?'],
                        ['foreign' => 'これはいくらですか? (kore wa ikura desu ka?)', 'translation' => 'berapa harganya?'],
                        ['foreign' => '水を買いたいです (mizu o kaitai desu)', 'translation' => 'saya ingin membeli air'],
                        ['foreign' => '左に曲がってください (hidari ni magatte kudasai)', 'translation' => 'belok kiri'],
                        ['foreign' => 'まっすぐ行ってください (massugu itte kudasai)', 'translation' => 'jalan lurus'],
                    ],
                    'challenge' => [
                        ['foreign' => '部屋は前よりきれいです (heya wa mae yori kirei desu)', 'translation' => 'ruangan itu lebih bersih dari sebelumnya'],
                        ['foreign' => '宿題はもう終わりました (shukudai wa mou owarimashita)', 'translation' => 'saya sudah menyelesaikan PR saya'],
                        ['foreign' => 'もう少しゆっくり話してください (mou sukoshi yukkuri hanashite kudasai)', 'translation' => 'tolong bicara lebih pelan'],
                        ['foreign' => '彼らは駅で待っていました (karera wa eki de matte imashita)', 'translation' => 'mereka sedang menunggu di stasiun'],
                        ['foreign' => '授業の後で電話します (jugyou no ato de denwa shimasu)', 'translation' => 'saya akan meneleponmu setelah kelas'],
                    ],
                ],
            ],
            [
                'slug' => 'arab',
                'name' => 'Arab',
                'vocabulary' => [
                    'people' => [
                        ['foreign' => 'أمي (ummi)', 'translation' => 'ibu'],
                        ['foreign' => 'أبي (abi)', 'translation' => 'ayah'],
                        ['foreign' => 'صديق (sadiq)', 'translation' => 'teman'],
                        ['foreign' => 'معلم (muallim)', 'translation' => 'guru'],
                        ['foreign' => 'طالب (talib)', 'translation' => 'siswa'],
                    ],
                    'places' => [
                        ['foreign' => 'مدرسة (madrasah)', 'translation' => 'sekolah'],
                        ['foreign' => 'مكتبة (maktabah)', 'translation' => 'perpustakaan'],
                        ['foreign' => 'سوق (suq)', 'translation' => 'pasar'],
                        ['foreign' => 'محطة (mahatta)', 'translation' => 'stasiun'],
                        ['foreign' => 'مستشفى (mustashfa)', 'translation' => 'rumah sakit'],
                    ],
                    'time' => [
                        ['foreign' => 'صباح (sabah)', 'translation' => 'pagi'],
                        ['foreign' => 'ليل (layl)', 'translation' => 'malam'],
                        ['foreign' => 'اليوم (al-yawm)', 'translation' => 'hari ini'],
                        ['foreign' => 'غدا (ghadan)', 'translation' => 'besok'],
                        ['foreign' => 'أمس (ams)', 'translation' => 'kemarin'],
                    ],
                ],
                'phrases' => [
                    'daily' => [
                        ['foreign' => 'أنا جائع (ana jai)', 'translation' => 'saya lapar'],
                        ['foreign' => 'أحتاج إلى مساعدة (ahtaju ila musaadah)', 'translation' => 'saya butuh bantuan'],
                        ['foreign' => 'هذا كتابي (hadha kitabi)', 'translation' => 'ini buku saya'],
                        ['foreign' => 'هي معلمتي (hiya muallimati)', 'translation' => 'dia guru saya'],
                        ['foreign' => 'نحن ندرس معا (nahnu nadrusu maan)', 'translation' => 'kami belajar bersama'],
                    ],
                    'travel' => [
                        ['foreign' => 'أين المحطة؟ (ayna al-mahatta?)', 'translation' => 'di mana stasiun?'],
                        ['foreign' => 'كم ثمن هذا؟ (kam thaman hadha?)', 'translation' => 'berapa harganya?'],
                        ['foreign' => 'أريد شراء ماء (urid shira ma)', 'translation' => 'saya ingin membeli air'],
                        ['foreign' => 'انعطف يسارا (inatif yasaran)', 'translation' => 'belok kiri'],
                        ['foreign' => 'اذهب مباشرة (idhhab mubasharatan)', 'translation' => 'jalan lurus'],
                    ],
                    'challenge' => [
                        ['foreign' => 'الغرفة أنظف من قبل (al-ghurfah anzaf min qabl)', 'translation' => 'ruangan itu lebih bersih dari sebelumnya'],
                        ['foreign' => 'لقد أنهيت واجبي (laqad anhaytu wajibii)', 'translation' => 'saya sudah menyelesaikan PR saya'],
                        ['foreign' => 'تحدث ببطء من فضلك (tahaddath bibut min fadlik)', 'translation' => 'tolong bicara lebih pelan'],
                        ['foreign' => 'كانوا ينتظرون في المحطة (kanu yantazirun fi al-mahatta)', 'translation' => 'mereka sedang menunggu di stasiun'],
                        ['foreign' => 'سأتصل بك بعد الدرس (saatasilu bika bad ad-dars)', 'translation' => 'saya akan meneleponmu setelah kelas'],
                    ],
                ],
            ],
            [
                'slug' => 'prancis',
                'name' => 'Prancis',
                'vocabulary' => [
                    'people' => [
                        ['foreign' => 'mère', 'translation' => 'ibu'],
                        ['foreign' => 'père', 'translation' => 'ayah'],
                        ['foreign' => 'ami', 'translation' => 'teman'],
                        ['foreign' => 'professeur', 'translation' => 'guru'],
                        ['foreign' => 'étudiant', 'translation' => 'siswa'],
                    ],
                    'places' => [
                        ['foreign' => 'école', 'translation' => 'sekolah'],
                        ['foreign' => 'bibliothèque', 'translation' => 'perpustakaan'],
                        ['foreign' => 'marché', 'translation' => 'pasar'],
                        ['foreign' => 'gare', 'translation' => 'stasiun'],
                        ['foreign' => 'hôpital', 'translation' => 'rumah sakit'],
                    ],
                    'time' => [
                        ['foreign' => 'matin', 'translation' => 'pagi'],
                        ['foreign' => 'nuit', 'translation' => 'malam'],
                        ['foreign' => "aujourd'hui", 'translation' => 'hari ini'],
                        ['foreign' => 'demain', 'translation' => 'besok'],
                        ['foreign' => 'hier', 'translation' => 'kemarin'],
                    ],
                ],
                'phrases' => [
                    'daily' => [
                        ['foreign' => "J'ai faim", 'translation' => 'saya lapar'],
                        ['foreign' => "J'ai besoin d'aide", 'translation' => 'saya butuh bantuan'],
                        ['foreign' => "C'est mon livre", 'translation' => 'ini buku saya'],
                        ['foreign' => "Elle est mon professeur", 'translation' => 'dia guru saya'],
                        ['foreign' => 'Nous étudions ensemble', 'translation' => 'kami belajar bersama'],
                    ],
                    'travel' => [
                        ['foreign' => 'Où est la gare ?', 'translation' => 'di mana stasiun?'],
                        ['foreign' => "C'est combien ?", 'translation' => 'berapa harganya?'],
                        ['foreign' => "Je veux acheter de l'eau", 'translation' => 'saya ingin membeli air'],
                        ['foreign' => 'Tournez à gauche', 'translation' => 'belok kiri'],
                        ['foreign' => 'Allez tout droit', 'translation' => 'jalan lurus'],
                    ],
                    'challenge' => [
                        ['foreign' => 'La chambre est plus propre qu avant', 'translation' => 'ruangan itu lebih bersih dari sebelumnya'],
                        ['foreign' => "J'ai déjà fini mes devoirs", 'translation' => 'saya sudah menyelesaikan PR saya'],
                        ['foreign' => 'Pouvez-vous parler plus lentement ?', 'translation' => 'tolong bicara lebih pelan'],
                        ['foreign' => 'Ils attendaient à la gare', 'translation' => 'mereka sedang menunggu di stasiun'],
                        ['foreign' => "Je t'appellerai après le cours", 'translation' => 'saya akan meneleponmu setelah kelas'],
                    ],
                ],
            ],
        ];
    }
}
