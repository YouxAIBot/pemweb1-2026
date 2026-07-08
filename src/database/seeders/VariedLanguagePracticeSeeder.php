<?php

namespace Database\Seeders;

use App\Models\LearningLanguage;
use App\Models\LearningLevel;
use App\Models\LearningQuestion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VariedLanguagePracticeSeeder extends Seeder
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

                foreach ($this->plans($languageData) as $plan) {
                    $level = LearningLevel::query()
                        ->where('slug', $plan['level_slug'])
                        ->whereHas('part', function ($query) use ($language, $plan) {
                            $query
                                ->where('learning_language_id', $language->id)
                                ->where('slug', $plan['part_slug']);
                        })
                        ->first();

                    if (! $level) {
                        continue;
                    }

                    foreach ($plan['questions'] as $questionIndex => $questionData) {
                        $this->seedQuestion($level, $questionData, 100 + $questionIndex + 1);
                    }
                }
            }
        });
    }

    private function seedQuestion(LearningLevel $level, array $data, int $sortOrder): void
    {
        $question = LearningQuestion::updateOrCreate(
            [
                'learning_level_id' => $level->id,
                'question_text' => $data['question_text'],
            ],
            [
                'type' => $data['type'],
                'instruction' => $data['instruction'],
                'correct_answer' => $data['correct_answer'],
                'explanation' => $data['explanation'],
                'points' => $data['difficulty'] === 'hard' ? 20 : 10,
                'time_limit' => $data['difficulty'] === 'hard' ? 35 : 25,
                'sort_order' => $sortOrder,
                'is_active' => true,
                'settings' => [
                    'seed_source' => 'varied_language_practice',
                    'difficulty' => $data['difficulty'],
                    'content_type' => 'quick_practice',
                    'competitive_ready' => true,
                ],
            ],
        );

        $question->options()->delete();

        foreach ($data['options'] as $optionIndex => $option) {
            $question->options()->create([
                'option_text' => $option['text'],
                'is_correct' => (bool) $option['correct'],
                'sort_order' => $optionIndex + 1,
            ]);
        }
    }

    private function plans(array $language): array
    {
        return [
            $this->plan('bagian-1-introduction', 'level-1-introduction', $this->termQuestions($language, 'greetings', 'easy')),
            $this->plan('bagian-1-introduction', 'level-2-basic-vocabulary', $this->termQuestions($language, 'basic_words', 'easy')),
            $this->plan('bagian-1-introduction', 'level-3-simple-sentence', $this->phraseQuestions($language, 'starter_sentences', 'normal')),
            $this->plan('bagian-2-kosakata-harian', 'level-1-orang-keluarga', $this->termQuestions($language, 'people', 'easy')),
            $this->plan('bagian-2-kosakata-harian', 'level-2-tempat-sehari-hari', $this->termQuestions($language, 'places', 'normal')),
            $this->plan('bagian-2-kosakata-harian', 'level-3-waktu-dasar', $this->termQuestions($language, 'time', 'normal')),
            $this->plan('bagian-3-percakapan-situasi', 'level-1-kalimat-harian', $this->phraseQuestions($language, 'daily', 'normal')),
            $this->plan('bagian-3-percakapan-situasi', 'level-2-jalan-belanja', $this->phraseQuestions($language, 'travel', 'normal')),
            $this->plan('bagian-3-percakapan-situasi', 'level-3-tantangan-konteks', $this->phraseQuestions($language, 'challenge', 'hard')),
        ];
    }

    private function plan(string $partSlug, string $levelSlug, array $questions): array
    {
        return [
            'part_slug' => $partSlug,
            'level_slug' => $levelSlug,
            'questions' => $questions,
        ];
    }

    private function termQuestions(array $language, string $group, string $difficulty): array
    {
        $items = $language['terms'][$group];
        $pool = $this->flatten($language['terms']);

        return collect($items)
            ->values()
            ->map(function (array $item, int $index) use ($language, $pool, $difficulty): array {
                if ($index % 2 === 0) {
                    return $this->question(
                        'multiple_choice',
                        'Pilih arti yang paling tepat.',
                        'Latihan cepat: apa arti "' . $item['foreign'] . '"?',
                        $item['translation'],
                        $this->distractors($pool, 'translation', $item['translation']),
                        '"' . $item['foreign'] . '" berarti ' . $item['translation'] . '.',
                        $difficulty,
                    );
                }

                return $this->question(
                    'multiple_choice',
                    'Pilih kosakata bahasa asing yang paling tepat.',
                    'Latihan cepat: pilih kata ' . $language['name'] . ' untuk "' . $item['translation'] . '".',
                    $item['foreign'],
                    $this->distractors($pool, 'foreign', $item['foreign']),
                    'Kosakata yang tepat adalah "' . $item['foreign'] . '".',
                    $difficulty,
                );
            })
            ->all();
    }

    private function phraseQuestions(array $language, string $group, string $difficulty): array
    {
        $items = $language['phrases'][$group];
        $pool = $this->flatten($language['phrases']);

        return collect($items)
            ->values()
            ->map(function (array $item, int $index) use ($language, $pool, $difficulty): array {
                if ($index % 2 === 0) {
                    return $this->question(
                        $difficulty === 'hard' ? 'real_case' : 'multiple_choice',
                        'Pahami kalimat pendek lalu pilih arti yang tepat.',
                        'Pilih arti kalimat "' . $item['foreign'] . '".',
                        $item['translation'],
                        $this->distractors($pool, 'translation', $item['translation']),
                        'Kalimat itu berarti ' . $item['translation'] . '.',
                        $difficulty,
                    );
                }

                return $this->question(
                    $difficulty === 'hard' ? 'real_case' : 'multiple_choice',
                    'Pilih ungkapan yang paling natural.',
                    'Pilih ungkapan ' . $language['name'] . ' yang cocok untuk "' . $item['translation'] . '".',
                    $item['foreign'],
                    $this->distractors($pool, 'foreign', $item['foreign']),
                    'Ungkapan yang tepat adalah "' . $item['foreign'] . '".',
                    $difficulty,
                );
            })
            ->all();
    }

    private function question(string $type, string $instruction, string $questionText, string $correct, array $wrongOptions, string $explanation, string $difficulty): array
    {
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
            'question_text' => $questionText,
            'correct_answer' => $correct,
            'explanation' => $explanation,
            'difficulty' => $difficulty,
            'options' => $options->shuffle()->values()->all(),
        ];
    }

    private function flatten(array $groups): array
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
                'terms' => [
                    'greetings' => [
                        ['foreign' => 'nice to meet you', 'translation' => 'senang bertemu denganmu'],
                        ['foreign' => 'excuse me', 'translation' => 'permisi'],
                        ['foreign' => 'please', 'translation' => 'tolong/silakan'],
                        ['foreign' => 'good afternoon', 'translation' => 'selamat siang'],
                    ],
                    'basic_words' => [
                        ['foreign' => 'chair', 'translation' => 'kursi'],
                        ['foreign' => 'door', 'translation' => 'pintu'],
                        ['foreign' => 'bag', 'translation' => 'tas'],
                        ['foreign' => 'pencil', 'translation' => 'pensil'],
                    ],
                    'people' => [
                        ['foreign' => 'sister', 'translation' => 'saudara perempuan'],
                        ['foreign' => 'brother', 'translation' => 'saudara laki-laki'],
                        ['foreign' => 'classmate', 'translation' => 'teman sekelas'],
                        ['foreign' => 'neighbor', 'translation' => 'tetangga'],
                    ],
                    'places' => [
                        ['foreign' => 'classroom', 'translation' => 'ruang kelas'],
                        ['foreign' => 'park', 'translation' => 'taman'],
                        ['foreign' => 'restaurant', 'translation' => 'restoran'],
                        ['foreign' => 'airport', 'translation' => 'bandara'],
                    ],
                    'time' => [
                        ['foreign' => 'afternoon', 'translation' => 'siang/sore'],
                        ['foreign' => 'weekend', 'translation' => 'akhir pekan'],
                        ['foreign' => 'now', 'translation' => 'sekarang'],
                        ['foreign' => 'later', 'translation' => 'nanti'],
                    ],
                ],
                'phrases' => [
                    'starter_sentences' => [
                        ['foreign' => 'I live near school', 'translation' => 'saya tinggal dekat sekolah'],
                        ['foreign' => 'This bag is mine', 'translation' => 'tas ini milik saya'],
                        ['foreign' => 'Can you help me?', 'translation' => 'bisakah kamu membantu saya?'],
                        ['foreign' => 'The door is open', 'translation' => 'pintu itu terbuka'],
                    ],
                    'daily' => [
                        ['foreign' => 'I want to rest', 'translation' => 'saya ingin istirahat'],
                        ['foreign' => 'I must do homework', 'translation' => 'saya harus mengerjakan PR'],
                        ['foreign' => 'I need a pencil', 'translation' => 'saya membutuhkan pensil'],
                        ['foreign' => 'Please open the door', 'translation' => 'tolong buka pintunya'],
                    ],
                    'travel' => [
                        ['foreign' => 'Where can I buy a ticket?', 'translation' => 'di mana saya bisa membeli tiket?'],
                        ['foreign' => 'The airport is far', 'translation' => 'bandara itu jauh'],
                        ['foreign' => 'I am waiting at the bus stop', 'translation' => 'saya menunggu di halte bus'],
                        ['foreign' => 'Please turn right', 'translation' => 'tolong belok kanan'],
                    ],
                    'challenge' => [
                        ['foreign' => 'She has been studying since morning', 'translation' => 'dia sudah belajar sejak pagi'],
                        ['foreign' => 'The meeting was moved to tomorrow', 'translation' => 'rapat dipindahkan ke besok'],
                        ['foreign' => 'Please remind me before class starts', 'translation' => 'tolong ingatkan saya sebelum kelas dimulai'],
                        ['foreign' => 'I forgot where I put my phone', 'translation' => 'saya lupa di mana menaruh ponsel saya'],
                    ],
                ],
            ],
            [
                'slug' => 'mandarin',
                'name' => 'Mandarin',
                'terms' => [
                    'greetings' => [
                        ['foreign' => '很高兴认识你 (hen gaoxing renshi ni)', 'translation' => 'senang bertemu denganmu'],
                        ['foreign' => '不好意思 (buhaoyisi)', 'translation' => 'permisi/maaf'],
                        ['foreign' => '请 (qing)', 'translation' => 'tolong/silakan'],
                        ['foreign' => '下午好 (xiawu hao)', 'translation' => 'selamat siang'],
                    ],
                    'basic_words' => [
                        ['foreign' => '椅子 (yizi)', 'translation' => 'kursi'],
                        ['foreign' => '门 (men)', 'translation' => 'pintu'],
                        ['foreign' => '包 (bao)', 'translation' => 'tas'],
                        ['foreign' => '铅笔 (qianbi)', 'translation' => 'pensil'],
                    ],
                    'people' => [
                        ['foreign' => '姐姐 (jiejie)', 'translation' => 'kakak perempuan'],
                        ['foreign' => '哥哥 (gege)', 'translation' => 'kakak laki-laki'],
                        ['foreign' => '同学 (tongxue)', 'translation' => 'teman sekelas'],
                        ['foreign' => '邻居 (linju)', 'translation' => 'tetangga'],
                    ],
                    'places' => [
                        ['foreign' => '教室 (jiaoshi)', 'translation' => 'ruang kelas'],
                        ['foreign' => '公园 (gongyuan)', 'translation' => 'taman'],
                        ['foreign' => '餐厅 (canting)', 'translation' => 'restoran'],
                        ['foreign' => '机场 (jichang)', 'translation' => 'bandara'],
                    ],
                    'time' => [
                        ['foreign' => '下午 (xiawu)', 'translation' => 'siang/sore'],
                        ['foreign' => '周末 (zhoumo)', 'translation' => 'akhir pekan'],
                        ['foreign' => '现在 (xianzai)', 'translation' => 'sekarang'],
                        ['foreign' => '以后 (yihou)', 'translation' => 'nanti'],
                    ],
                ],
                'phrases' => [
                    'starter_sentences' => [
                        ['foreign' => '我住在学校附近 (wo zhu zai xuexiao fujin)', 'translation' => 'saya tinggal dekat sekolah'],
                        ['foreign' => '这个包是我的 (zhege bao shi wo de)', 'translation' => 'tas ini milik saya'],
                        ['foreign' => '你能帮我吗? (ni neng bang wo ma?)', 'translation' => 'bisakah kamu membantu saya?'],
                        ['foreign' => '门开着 (men kai zhe)', 'translation' => 'pintu itu terbuka'],
                    ],
                    'daily' => [
                        ['foreign' => '我想休息 (wo xiang xiuxi)', 'translation' => 'saya ingin istirahat'],
                        ['foreign' => '我必须做作业 (wo bixu zuo zuoye)', 'translation' => 'saya harus mengerjakan PR'],
                        ['foreign' => '我需要一支铅笔 (wo xuyao yi zhi qianbi)', 'translation' => 'saya membutuhkan pensil'],
                        ['foreign' => '请开门 (qing kai men)', 'translation' => 'tolong buka pintunya'],
                    ],
                    'travel' => [
                        ['foreign' => '我在哪里买票? (wo zai nali mai piao?)', 'translation' => 'di mana saya bisa membeli tiket?'],
                        ['foreign' => '机场很远 (jichang hen yuan)', 'translation' => 'bandara itu jauh'],
                        ['foreign' => '我在公交站等 (wo zai gongjiao zhan deng)', 'translation' => 'saya menunggu di halte bus'],
                        ['foreign' => '请向右转 (qing xiang you zhuan)', 'translation' => 'tolong belok kanan'],
                    ],
                    'challenge' => [
                        ['foreign' => '她从早上开始一直在学习 (ta cong zaoshang kaishi yizhi zai xuexi)', 'translation' => 'dia sudah belajar sejak pagi'],
                        ['foreign' => '会议改到明天了 (huiyi gai dao mingtian le)', 'translation' => 'rapat dipindahkan ke besok'],
                        ['foreign' => '上课前请提醒我 (shangke qian qing tixing wo)', 'translation' => 'tolong ingatkan saya sebelum kelas dimulai'],
                        ['foreign' => '我忘了手机放在哪里 (wo wangle shouji fang zai nali)', 'translation' => 'saya lupa di mana menaruh ponsel saya'],
                    ],
                ],
            ],
            [
                'slug' => 'korea',
                'name' => 'Korea',
                'terms' => [
                    'greetings' => [
                        ['foreign' => '만나서 반가워요 (mannaseo bangawoyo)', 'translation' => 'senang bertemu denganmu'],
                        ['foreign' => '실례합니다 (sillyehamnida)', 'translation' => 'permisi'],
                        ['foreign' => '제발 (jebal)', 'translation' => 'tolong'],
                        ['foreign' => '좋은 오후예요 (joeun ohuyeyo)', 'translation' => 'selamat siang'],
                    ],
                    'basic_words' => [
                        ['foreign' => '의자 (uija)', 'translation' => 'kursi'],
                        ['foreign' => '문 (mun)', 'translation' => 'pintu'],
                        ['foreign' => '가방 (gabang)', 'translation' => 'tas'],
                        ['foreign' => '연필 (yeonpil)', 'translation' => 'pensil'],
                    ],
                    'people' => [
                        ['foreign' => '언니/누나 (eonni/nuna)', 'translation' => 'kakak perempuan'],
                        ['foreign' => '오빠/형 (oppa/hyeong)', 'translation' => 'kakak laki-laki'],
                        ['foreign' => '반 친구 (ban chingu)', 'translation' => 'teman sekelas'],
                        ['foreign' => '이웃 (iut)', 'translation' => 'tetangga'],
                    ],
                    'places' => [
                        ['foreign' => '교실 (gyosil)', 'translation' => 'ruang kelas'],
                        ['foreign' => '공원 (gongwon)', 'translation' => 'taman'],
                        ['foreign' => '식당 (sikdang)', 'translation' => 'restoran'],
                        ['foreign' => '공항 (gonghang)', 'translation' => 'bandara'],
                    ],
                    'time' => [
                        ['foreign' => '오후 (ohu)', 'translation' => 'siang/sore'],
                        ['foreign' => '주말 (jumal)', 'translation' => 'akhir pekan'],
                        ['foreign' => '지금 (jigeum)', 'translation' => 'sekarang'],
                        ['foreign' => '나중에 (najunge)', 'translation' => 'nanti'],
                    ],
                ],
                'phrases' => [
                    'starter_sentences' => [
                        ['foreign' => '저는 학교 근처에 살아요 (jeoneun hakgyo geuncheoe sarayo)', 'translation' => 'saya tinggal dekat sekolah'],
                        ['foreign' => '이 가방은 제 거예요 (i gabangeun je geoyeyo)', 'translation' => 'tas ini milik saya'],
                        ['foreign' => '저를 도와줄 수 있어요? (jeoreul dowajul su isseoyo?)', 'translation' => 'bisakah kamu membantu saya?'],
                        ['foreign' => '문이 열려 있어요 (muni yeollyeo isseoyo)', 'translation' => 'pintu itu terbuka'],
                    ],
                    'daily' => [
                        ['foreign' => '쉬고 싶어요 (swigo sipeoyo)', 'translation' => 'saya ingin istirahat'],
                        ['foreign' => '숙제를 해야 해요 (sukjereul haeya haeyo)', 'translation' => 'saya harus mengerjakan PR'],
                        ['foreign' => '연필이 필요해요 (yeonpiri piryohaeyo)', 'translation' => 'saya membutuhkan pensil'],
                        ['foreign' => '문을 열어 주세요 (muneul yeoreo juseyo)', 'translation' => 'tolong buka pintunya'],
                    ],
                    'travel' => [
                        ['foreign' => '표를 어디에서 살 수 있어요? (pyoreul eodieseo sal su isseoyo?)', 'translation' => 'di mana saya bisa membeli tiket?'],
                        ['foreign' => '공항은 멀어요 (gonghangeun meoreoyo)', 'translation' => 'bandara itu jauh'],
                        ['foreign' => '버스 정류장에서 기다리고 있어요 (beoseu jeongnyujangeseo gidarigo isseoyo)', 'translation' => 'saya menunggu di halte bus'],
                        ['foreign' => '오른쪽으로 도세요 (oreunjjogeuro doseyo)', 'translation' => 'tolong belok kanan'],
                    ],
                    'challenge' => [
                        ['foreign' => '그녀는 아침부터 공부하고 있어요 (geunyeoneun achimbuteo gongbuhago isseoyo)', 'translation' => 'dia sudah belajar sejak pagi'],
                        ['foreign' => '회의가 내일로 옮겨졌어요 (hoeui-ga naeillo omgyeojyeosseoyo)', 'translation' => 'rapat dipindahkan ke besok'],
                        ['foreign' => '수업 시작 전에 알려 주세요 (sueop sijak jeone allyeo juseyo)', 'translation' => 'tolong ingatkan saya sebelum kelas dimulai'],
                        ['foreign' => '휴대폰을 어디에 두었는지 잊었어요 (hyudaeponeul eodie dueonneunji ijeosseoyo)', 'translation' => 'saya lupa di mana menaruh ponsel saya'],
                    ],
                ],
            ],
            [
                'slug' => 'jepang',
                'name' => 'Jepang',
                'terms' => [
                    'greetings' => [
                        ['foreign' => 'はじめまして (hajimemashite)', 'translation' => 'senang bertemu denganmu'],
                        ['foreign' => 'すみません (sumimasen)', 'translation' => 'permisi/maaf'],
                        ['foreign' => 'お願いします (onegaishimasu)', 'translation' => 'tolong'],
                        ['foreign' => 'こんにちは (konnichiwa)', 'translation' => 'selamat siang/halo'],
                    ],
                    'basic_words' => [
                        ['foreign' => '椅子 (isu)', 'translation' => 'kursi'],
                        ['foreign' => 'ドア (doa)', 'translation' => 'pintu'],
                        ['foreign' => 'かばん (kaban)', 'translation' => 'tas'],
                        ['foreign' => '鉛筆 (enpitsu)', 'translation' => 'pensil'],
                    ],
                    'people' => [
                        ['foreign' => '姉 (ane)', 'translation' => 'kakak perempuan'],
                        ['foreign' => '兄 (ani)', 'translation' => 'kakak laki-laki'],
                        ['foreign' => 'クラスメート (kurasumeeto)', 'translation' => 'teman sekelas'],
                        ['foreign' => '隣人 (rinjin)', 'translation' => 'tetangga'],
                    ],
                    'places' => [
                        ['foreign' => '教室 (kyoushitsu)', 'translation' => 'ruang kelas'],
                        ['foreign' => '公園 (kouen)', 'translation' => 'taman'],
                        ['foreign' => 'レストラン (resutoran)', 'translation' => 'restoran'],
                        ['foreign' => '空港 (kuukou)', 'translation' => 'bandara'],
                    ],
                    'time' => [
                        ['foreign' => '午後 (gogo)', 'translation' => 'siang/sore'],
                        ['foreign' => '週末 (shuumatsu)', 'translation' => 'akhir pekan'],
                        ['foreign' => '今 (ima)', 'translation' => 'sekarang'],
                        ['foreign' => '後で (ato de)', 'translation' => 'nanti'],
                    ],
                ],
                'phrases' => [
                    'starter_sentences' => [
                        ['foreign' => '学校の近くに住んでいます (gakkou no chikaku ni sunde imasu)', 'translation' => 'saya tinggal dekat sekolah'],
                        ['foreign' => 'このかばんは私のです (kono kaban wa watashi no desu)', 'translation' => 'tas ini milik saya'],
                        ['foreign' => '手伝ってくれますか? (tetsudatte kuremasu ka?)', 'translation' => 'bisakah kamu membantu saya?'],
                        ['foreign' => 'ドアが開いています (doa ga aite imasu)', 'translation' => 'pintu itu terbuka'],
                    ],
                    'daily' => [
                        ['foreign' => '休みたいです (yasumitai desu)', 'translation' => 'saya ingin istirahat'],
                        ['foreign' => '宿題をしなければなりません (shukudai o shinakereba narimasen)', 'translation' => 'saya harus mengerjakan PR'],
                        ['foreign' => '鉛筆が必要です (enpitsu ga hitsuyou desu)', 'translation' => 'saya membutuhkan pensil'],
                        ['foreign' => 'ドアを開けてください (doa o akete kudasai)', 'translation' => 'tolong buka pintunya'],
                    ],
                    'travel' => [
                        ['foreign' => 'どこで切符を買えますか? (doko de kippu o kaemasu ka?)', 'translation' => 'di mana saya bisa membeli tiket?'],
                        ['foreign' => '空港は遠いです (kuukou wa tooi desu)', 'translation' => 'bandara itu jauh'],
                        ['foreign' => 'バス停で待っています (basutei de matte imasu)', 'translation' => 'saya menunggu di halte bus'],
                        ['foreign' => '右に曲がってください (migi ni magatte kudasai)', 'translation' => 'tolong belok kanan'],
                    ],
                    'challenge' => [
                        ['foreign' => '彼女は朝から勉強しています (kanojo wa asa kara benkyou shite imasu)', 'translation' => 'dia sudah belajar sejak pagi'],
                        ['foreign' => '会議は明日に変更されました (kaigi wa ashita ni henkou saremashita)', 'translation' => 'rapat dipindahkan ke besok'],
                        ['foreign' => '授業が始まる前に思い出させてください (jugyou ga hajimaru mae ni omoidasasete kudasai)', 'translation' => 'tolong ingatkan saya sebelum kelas dimulai'],
                        ['foreign' => '携帯をどこに置いたか忘れました (keitai o doko ni oita ka wasuremashita)', 'translation' => 'saya lupa di mana menaruh ponsel saya'],
                    ],
                ],
            ],
            [
                'slug' => 'arab',
                'name' => 'Arab',
                'terms' => [
                    'greetings' => [
                        ['foreign' => 'تشرفت بلقائك (tasharraftu biliqaik)', 'translation' => 'senang bertemu denganmu'],
                        ['foreign' => 'من فضلك (min fadlik)', 'translation' => 'tolong/silakan'],
                        ['foreign' => 'عذرا (udhuran)', 'translation' => 'permisi/maaf'],
                        ['foreign' => 'مساء الخير (masa al-khayr)', 'translation' => 'selamat sore/malam'],
                    ],
                    'basic_words' => [
                        ['foreign' => 'كرسي (kursi)', 'translation' => 'kursi'],
                        ['foreign' => 'باب (bab)', 'translation' => 'pintu'],
                        ['foreign' => 'حقيبة (haqibah)', 'translation' => 'tas'],
                        ['foreign' => 'قلم رصاص (qalam rasas)', 'translation' => 'pensil'],
                    ],
                    'people' => [
                        ['foreign' => 'أخت (ukht)', 'translation' => 'saudara perempuan'],
                        ['foreign' => 'أخ (akh)', 'translation' => 'saudara laki-laki'],
                        ['foreign' => 'زميل صف (zamil saff)', 'translation' => 'teman sekelas'],
                        ['foreign' => 'جار (jar)', 'translation' => 'tetangga'],
                    ],
                    'places' => [
                        ['foreign' => 'فصل (fasl)', 'translation' => 'ruang kelas'],
                        ['foreign' => 'حديقة (hadiqah)', 'translation' => 'taman'],
                        ['foreign' => 'مطعم (matam)', 'translation' => 'restoran'],
                        ['foreign' => 'مطار (matar)', 'translation' => 'bandara'],
                    ],
                    'time' => [
                        ['foreign' => 'بعد الظهر (bad az-zuhr)', 'translation' => 'siang/sore'],
                        ['foreign' => 'نهاية الأسبوع (nihayat al-usbu)', 'translation' => 'akhir pekan'],
                        ['foreign' => 'الآن (al-an)', 'translation' => 'sekarang'],
                        ['foreign' => 'لاحقا (lahiqan)', 'translation' => 'nanti'],
                    ],
                ],
                'phrases' => [
                    'starter_sentences' => [
                        ['foreign' => 'أسكن قرب المدرسة (askun qurb al-madrasah)', 'translation' => 'saya tinggal dekat sekolah'],
                        ['foreign' => 'هذه الحقيبة لي (hadhihi al-haqibah li)', 'translation' => 'tas ini milik saya'],
                        ['foreign' => 'هل يمكنك مساعدتي؟ (hal yumkinuka musaadati?)', 'translation' => 'bisakah kamu membantu saya?'],
                        ['foreign' => 'الباب مفتوح (al-bab maftuh)', 'translation' => 'pintu itu terbuka'],
                    ],
                    'daily' => [
                        ['foreign' => 'أريد أن أستريح (urid an astarih)', 'translation' => 'saya ingin istirahat'],
                        ['foreign' => 'يجب أن أفعل واجبي (yajib an afal wajibi)', 'translation' => 'saya harus mengerjakan PR'],
                        ['foreign' => 'أحتاج إلى قلم رصاص (ahtaju ila qalam rasas)', 'translation' => 'saya membutuhkan pensil'],
                        ['foreign' => 'افتح الباب من فضلك (iftah al-bab min fadlik)', 'translation' => 'tolong buka pintunya'],
                    ],
                    'travel' => [
                        ['foreign' => 'أين يمكنني شراء تذكرة؟ (ayna yumkinuni shira tadhkirah?)', 'translation' => 'di mana saya bisa membeli tiket?'],
                        ['foreign' => 'المطار بعيد (al-matar baid)', 'translation' => 'bandara itu jauh'],
                        ['foreign' => 'أنا أنتظر في موقف الحافلات (ana antazir fi mawqif al-hafilat)', 'translation' => 'saya menunggu di halte bus'],
                        ['foreign' => 'انعطف يمينا من فضلك (inatif yaminan min fadlik)', 'translation' => 'tolong belok kanan'],
                    ],
                    'challenge' => [
                        ['foreign' => 'هي تدرس منذ الصباح (hiya tadrus mundhu as-sabah)', 'translation' => 'dia sudah belajar sejak pagi'],
                        ['foreign' => 'تم نقل الاجتماع إلى الغد (tam naql al-ijtima ila al-ghad)', 'translation' => 'rapat dipindahkan ke besok'],
                        ['foreign' => 'ذكرني قبل بدء الدرس (dhakkirni qabl bad ad-dars)', 'translation' => 'tolong ingatkan saya sebelum kelas dimulai'],
                        ['foreign' => 'نسيت أين وضعت هاتفي (nasitu ayna wadatu hatifi)', 'translation' => 'saya lupa di mana menaruh ponsel saya'],
                    ],
                ],
            ],
            [
                'slug' => 'prancis',
                'name' => 'Prancis',
                'terms' => [
                    'greetings' => [
                        ['foreign' => 'enchanté', 'translation' => 'senang bertemu denganmu'],
                        ['foreign' => 'excusez-moi', 'translation' => 'permisi/maaf'],
                        ['foreign' => "s'il vous plaît", 'translation' => 'tolong/silakan'],
                        ['foreign' => 'bon après-midi', 'translation' => 'selamat siang'],
                    ],
                    'basic_words' => [
                        ['foreign' => 'chaise', 'translation' => 'kursi'],
                        ['foreign' => 'porte', 'translation' => 'pintu'],
                        ['foreign' => 'sac', 'translation' => 'tas'],
                        ['foreign' => 'crayon', 'translation' => 'pensil'],
                    ],
                    'people' => [
                        ['foreign' => 'soeur', 'translation' => 'saudara perempuan'],
                        ['foreign' => 'frère', 'translation' => 'saudara laki-laki'],
                        ['foreign' => 'camarade de classe', 'translation' => 'teman sekelas'],
                        ['foreign' => 'voisin', 'translation' => 'tetangga'],
                    ],
                    'places' => [
                        ['foreign' => 'salle de classe', 'translation' => 'ruang kelas'],
                        ['foreign' => 'parc', 'translation' => 'taman'],
                        ['foreign' => 'restaurant', 'translation' => 'restoran'],
                        ['foreign' => 'aéroport', 'translation' => 'bandara'],
                    ],
                    'time' => [
                        ['foreign' => 'après-midi', 'translation' => 'siang/sore'],
                        ['foreign' => 'week-end', 'translation' => 'akhir pekan'],
                        ['foreign' => 'maintenant', 'translation' => 'sekarang'],
                        ['foreign' => 'plus tard', 'translation' => 'nanti'],
                    ],
                ],
                'phrases' => [
                    'starter_sentences' => [
                        ["foreign" => "J'habite près de l'école", 'translation' => 'saya tinggal dekat sekolah'],
                        ["foreign" => "Ce sac est à moi", 'translation' => 'tas ini milik saya'],
                        ["foreign" => "Pouvez-vous m'aider ?", 'translation' => 'bisakah kamu membantu saya?'],
                        ["foreign" => "La porte est ouverte", 'translation' => 'pintu itu terbuka'],
                    ],
                    'daily' => [
                        ["foreign" => "Je veux me reposer", 'translation' => 'saya ingin istirahat'],
                        ["foreign" => "Je dois faire mes devoirs", 'translation' => 'saya harus mengerjakan PR'],
                        ["foreign" => "J'ai besoin d'un crayon", 'translation' => 'saya membutuhkan pensil'],
                        ["foreign" => "Ouvrez la porte, s'il vous plaît", 'translation' => 'tolong buka pintunya'],
                    ],
                    'travel' => [
                        ["foreign" => "Où puis-je acheter un billet ?", 'translation' => 'di mana saya bisa membeli tiket?'],
                        ["foreign" => "L'aéroport est loin", 'translation' => 'bandara itu jauh'],
                        ["foreign" => "J'attends à l'arrêt de bus", 'translation' => 'saya menunggu di halte bus'],
                        ["foreign" => "Tournez à droite, s'il vous plaît", 'translation' => 'tolong belok kanan'],
                    ],
                    'challenge' => [
                        ["foreign" => "Elle étudie depuis ce matin", 'translation' => 'dia sudah belajar sejak pagi'],
                        ["foreign" => "La réunion a été déplacée à demain", 'translation' => 'rapat dipindahkan ke besok'],
                        ["foreign" => "Rappelez-le-moi avant le début du cours", 'translation' => 'tolong ingatkan saya sebelum kelas dimulai'],
                        ["foreign" => "J'ai oublié où j'ai mis mon téléphone", 'translation' => 'saya lupa di mana menaruh ponsel saya'],
                    ],
                ],
            ],
        ];
    }
}
