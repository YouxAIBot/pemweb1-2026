<?php

namespace Database\Seeders;

use App\Models\LearningLanguage;
use App\Models\LearningLevel;
use App\Models\LearningPart;
use App\Models\LearningQuestion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StoryLanguageQuestionSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            foreach ($this->stories() as $languageData) {
                $language = LearningLanguage::query()
                    ->where('slug', $languageData['slug'])
                    ->first();

                if (! $language) {
                    continue;
                }

                $part = LearningPart::updateOrCreate(
                    [
                        'learning_language_id' => $language->id,
                        'slug' => 'bagian-4-cerita-pendek',
                    ],
                    [
                        'title' => 'Bagian 4 Cerita Pendek ' . $languageData['name'],
                        'subtitle' => 'Baca cerita singkat, pahami konteks, lalu jawab.',
                        'description' => 'Latihan reading comprehension ala aplikasi bahasa: cerita pendek, pertanyaan konteks, dan pilihan jawaban.',
                        'badge_text' => 'Bagian 4',
                        'level_number' => 4,
                        'sort_order' => 4,
                        'is_active' => true,
                        'settings' => ['seed_source' => 'story_language_question'],
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
                            'short_label' => '4.' . ($levelIndex + 1),
                            'description' => $levelData['description'],
                            'sort_order' => $levelIndex + 1,
                            'xp_reward' => $levelData['difficulty'] === 'hard' ? 36 : 28,
                            'passing_score' => 70,
                            'position_x' => $levelData['position_x'],
                            'position_y' => $levelData['position_y'],
                            'is_active' => true,
                            'settings' => [
                                'seed_source' => 'story_language_question',
                                'difficulty' => $levelData['difficulty'],
                                'content_type' => 'short_story',
                            ],
                        ],
                    );

                    foreach ($levelData['questions'] as $questionIndex => $story) {
                        $this->seedStoryQuestion($level, $story, $questionIndex + 1, $levelData['difficulty']);
                    }
                }
            }
        });
    }

    private function seedStoryQuestion(LearningLevel $level, array $story, int $sortOrder, string $difficulty): void
    {
        $questionText = "Cerita:\n" . $story['story'] . "\n\nPertanyaan: " . $story['question'];
        $options = collect($story['wrong'])
            ->map(fn (string $option): array => ['text' => $option, 'correct' => false])
            ->push(['text' => $story['answer'], 'correct' => true])
            ->shuffle()
            ->values();

        $question = LearningQuestion::updateOrCreate(
            [
                'learning_level_id' => $level->id,
                'question_text' => $questionText,
            ],
            [
                'type' => 'real_case',
                'instruction' => 'Baca cerita pendek, lalu jawab pertanyaannya.',
                'correct_answer' => $story['answer'],
                'explanation' => $story['explanation'],
                'points' => $difficulty === 'hard' ? 20 : 15,
                'time_limit' => $difficulty === 'hard' ? 60 : 45,
                'sort_order' => $sortOrder,
                'is_active' => true,
                'settings' => [
                    'seed_source' => 'story_language_question',
                    'difficulty' => $difficulty,
                    'story_text' => $story['story'],
                    'story_question' => $story['question'],
                ],
            ],
        );

        $question->options()->delete();

        foreach ($options as $optionIndex => $option) {
            $question->options()->create([
                'option_text' => $option['text'],
                'is_correct' => (bool) $option['correct'],
                'sort_order' => $optionIndex + 1,
            ]);
        }
    }

    private function level(string $slug, string $title, string $description, int $x, int $y, string $difficulty, array $questions): array
    {
        return [
            'slug' => $slug,
            'title' => $title,
            'description' => $description,
            'position_x' => $x,
            'position_y' => $y,
            'difficulty' => $difficulty,
            'questions' => $questions,
        ];
    }

    private function q(string $story, string $question, string $answer, array $wrong, string $explanation): array
    {
        return compact('story', 'question', 'answer', 'wrong', 'explanation');
    }

    private function stories(): array
    {
        return [
            [
                'slug' => 'inggris',
                'name' => 'Inggris',
                'levels' => [
                    $this->level('level-1-cerita-harian', 'Level 1 - Cerita Harian', 'Cerita pendek tentang sekolah, teman, dan rutinitas.', 18, 24, 'normal', [
                        $this->q('Maya wakes up at six. She drinks water and reads a book before school.', 'What does Maya do before school?', 'She reads a book', ['She cooks dinner', 'She plays football', 'She buys shoes'], 'Cerita menyebut Maya membaca buku sebelum sekolah.'),
                        $this->q('Raka is new in class. Ben says hello and shows him the library.', 'Who helps Raka?', 'Ben', ['Maya', 'The teacher', 'His father'], 'Ben menyapa Raka dan menunjukkan perpustakaan.'),
                        $this->q('Nina forgets her pencil. Her friend gives her one, so Nina can write again.', 'What does Nina receive?', 'A pencil', ['A book', 'A bag', 'A ticket'], 'Temannya memberi Nina pensil.'),
                        $this->q('The teacher writes "market" and "station" on the board. The students repeat the words.', 'Where does the teacher write the words?', 'On the board', ['In a book', 'At the station', 'At home'], 'Kata-kata ditulis di papan.'),
                    ]),
                    $this->level('level-2-cerita-perjalanan', 'Level 2 - Cerita Perjalanan', 'Cerita singkat tentang arah, belanja, dan tempat umum.', 50, 45, 'normal', [
                        $this->q('Lina wants to go to the station. A man says, "Go straight and turn left."', 'What should Lina do after going straight?', 'Turn left', ['Turn right', 'Buy water', 'Go home'], 'Instruksi setelah jalan lurus adalah belok kiri.'),
                        $this->q('Doni is at the market. He buys water and bread for his mother.', 'Where is Doni?', 'At the market', ['At school', 'At the hospital', 'At the library'], 'Cerita menyebut Doni berada di pasar.'),
                        $this->q('Sari cannot find the hospital. She asks a woman for help politely.', 'What is Sari looking for?', 'The hospital', ['The market', 'The book', 'The classroom'], 'Sari mencari rumah sakit.'),
                        $this->q('A train arrives late, but Arman still reaches class before the lesson starts.', 'Does Arman arrive before class starts?', 'Yes, he does', ['No, he misses class', 'He goes to the market', 'He stays at the station'], 'Cerita menyebut Arman tiba sebelum pelajaran dimulai.'),
                    ]),
                    $this->level('level-3-cerita-tantangan', 'Level 3 - Cerita Tantangan', 'Cerita lebih panjang untuk pemahaman konteks.', 78, 30, 'hard', [
                        $this->q('Tia wants to join an English club, but she is nervous. Her friend tells her to speak slowly and practice every day.', 'What advice does Tia get?', 'Speak slowly and practice every day', ['Stop learning English', 'Wait at the station', 'Buy a new bag'], 'Temannya menyarankan bicara pelan dan latihan tiap hari.'),
                        $this->q('Before dinner, Rio finishes his homework. After dinner, he calls his friend to study together.', 'When does Rio finish his homework?', 'Before dinner', ['After dinner', 'At midnight', 'At school tomorrow'], 'Rio menyelesaikan PR sebelum makan malam.'),
                        $this->q('The library is quiet today because students are preparing for a test. Nobody speaks loudly.', 'Why is the library quiet?', 'Students are preparing for a test', ['The library is closed', 'There is a football match', 'The train is late'], 'Perpustakaan tenang karena siswa mempersiapkan ujian.'),
                        $this->q('Mina misses the first bus, so she walks faster to the station. She arrives just in time.', 'What problem does Mina have first?', 'She misses the first bus', ['She loses her book', 'She forgets her name', 'She buys too much food'], 'Masalah awal Mina adalah ketinggalan bus pertama.'),
                    ]),
                ],
            ],
            [
                'slug' => 'mandarin',
                'name' => 'Mandarin',
                'levels' => [
                    $this->level('level-1-cerita-harian', 'Level 1 - Cerita Harian', 'Cerita pendek Mandarin dengan pinyin dan konteks harian.', 18, 24, 'normal', [
                        $this->q('小明早上六点起床 (Xiao Ming zaoshang liu dian qichuang). 他喝水, 然后去学校.', 'Kapan Xiao Ming bangun?', 'Jam enam pagi', ['Jam enam malam', 'Setelah sekolah', 'Besok pagi'], 'Kalimat pertama menyebut Xiao Ming bangun jam enam pagi.'),
                        $this->q('丽丽有一本书 (Lili you yi ben shu). 她在图书馆看书.', 'Di mana Lili membaca buku?', 'Di perpustakaan', ['Di pasar', 'Di rumah sakit', 'Di stasiun'], 'Cerita menyebut Lili membaca di 图书馆, perpustakaan.'),
                        $this->q('老师说: 你好. 学生回答: 老师好.', 'Siapa yang menjawab guru?', 'Siswa', ['Ibu', 'Teman lama', 'Petugas stasiun'], '学生 berarti siswa.'),
                        $this->q('妈妈买水和面包 (mama mai shui he mianbao). 她在市场.', 'Apa yang dibeli ibu?', 'Air dan roti', ['Buku dan tas', 'Tiket dan teh', 'Pensil dan nasi'], '妈妈 membeli 水 dan 面包, yaitu air dan roti.'),
                    ]),
                    $this->level('level-2-cerita-perjalanan', 'Level 2 - Cerita Perjalanan', 'Cerita Mandarin tentang arah dan tempat umum.', 50, 45, 'normal', [
                        $this->q('安娜问: 车站在哪里? 一个男人说: 一直走, 然后向左转.', 'Apa yang harus dilakukan Anna setelah jalan lurus?', 'Belok kiri', ['Belok kanan', 'Membeli air', 'Pulang ke rumah'], '向左转 berarti belok kiri.'),
                        $this->q('王明在医院等朋友 (Wang Ming zai yiyuan deng pengyou). 朋友迟到了.', 'Di mana Wang Ming menunggu?', 'Di rumah sakit', ['Di sekolah', 'Di pasar', 'Di perpustakaan'], '医院 berarti rumah sakit.'),
                        $this->q('她想买一本书, 但是钱不够 (ta xiang mai yi ben shu, danshi qian bugou).', 'Apa masalahnya?', 'Uangnya tidak cukup', ['Bukunya hilang', 'Stasiunnya jauh', 'Gurunya datang'], '钱不够 berarti uangnya tidak cukup.'),
                        $this->q('他们明天去学校练习中文 (tamen mingtian qu xuexiao lianxi Zhongwen).', 'Kapan mereka pergi ke sekolah?', 'Besok', ['Hari ini', 'Kemarin', 'Malam ini'], '明天 berarti besok.'),
                    ]),
                    $this->level('level-3-cerita-tantangan', 'Level 3 - Cerita Tantangan', 'Cerita Mandarin lebih panjang untuk latihan sulit.', 78, 30, 'hard', [
                        $this->q('小雨已经完成作业了. 下课后, 她给朋友打电话一起学习.', 'Apa yang sudah diselesaikan Xiao Yu?', 'Pekerjaan rumah', ['Makan malam', 'Belanja di pasar', 'Perjalanan kereta'], '完成作业 berarti menyelesaikan PR.'),
                        $this->q('图书馆很安静, 因为学生们准备考试. 老师让大家慢慢读.', 'Mengapa perpustakaan tenang?', 'Siswa sedang menyiapkan ujian', ['Kereta datang terlambat', 'Pasar sudah tutup', 'Mereka sedang makan'], 'Cerita menyebut siswa mempersiapkan ujian.'),
                        $this->q('他错过了第一班车, 所以跑到车站. 最后他准时到了.', 'Apa masalah pertama yang terjadi?', 'Dia ketinggalan kendaraan pertama', ['Dia kehilangan buku', 'Dia membeli air', 'Dia lupa makan'], '错过了第一班车 berarti ketinggalan kendaraan pertama.'),
                        $this->q('老师说中文有点快. 学生说: 请说慢一点.', 'Apa yang diminta siswa?', 'Bicara lebih pelan', ['Menulis lebih besar', 'Membeli buku', 'Pergi ke pasar'], '请说慢一点 berarti tolong bicara lebih pelan.'),
                    ]),
                ],
            ],
            [
                'slug' => 'korea',
                'name' => 'Korea',
                'levels' => [
                    $this->level('level-1-cerita-harian', 'Level 1 - Cerita Harian', 'Cerita pendek Korea dengan romanisasi dan konteks harian.', 18, 24, 'normal', [
                        $this->q('민수는 아침 여섯 시에 일어나요 (Minsu-neun achim yeoseot sie ireonayo). 물을 마시고 학교에 가요.', 'Kapan Minsu bangun?', 'Jam enam pagi', ['Jam enam malam', 'Besok siang', 'Setelah kelas'], '아침 여섯 시 berarti jam enam pagi.'),
                        $this->q('지아는 도서관에서 책을 읽어요. 도서관은 조용해요.', 'Di mana Jia membaca?', 'Di perpustakaan', ['Di pasar', 'Di rumah sakit', 'Di stasiun'], '도서관 berarti perpustakaan.'),
                        $this->q('선생님이 안녕하세요라고 말해요. 학생들이 인사해요.', 'Siapa yang memberi salam kembali?', 'Siswa', ['Ibu', 'Teman lama', 'Petugas pasar'], '학생들이 berarti para siswa.'),
                        $this->q('엄마는 시장에서 물과 빵을 사요.', 'Apa yang dibeli ibu?', 'Air dan roti', ['Buku dan tas', 'Tiket dan teh', 'Pensil dan nasi'], '물 dan 빵 berarti air dan roti.'),
                    ]),
                    $this->level('level-2-cerita-perjalanan', 'Level 2 - Cerita Perjalanan', 'Cerita Korea tentang arah dan tempat umum.', 50, 45, 'normal', [
                        $this->q('하나는 역을 찾고 있어요. 남자가 말해요: 똑바로 가고 왼쪽으로 도세요.', 'Setelah jalan lurus, Hana harus apa?', 'Belok kiri', ['Belok kanan', 'Membeli air', 'Pulang'], '왼쪽으로 도세요 berarti belok kiri.'),
                        $this->q('준호는 병원에서 친구를 기다려요. 친구가 조금 늦어요.', 'Di mana Junho menunggu?', 'Di rumah sakit', ['Di sekolah', 'Di pasar', 'Di perpustakaan'], '병원 berarti rumah sakit.'),
                        $this->q('수진은 책을 사고 싶어요. 하지만 돈이 부족해요.', 'Apa masalah Sujin?', 'Uangnya tidak cukup', ['Bukunya hilang', 'Stasiunnya jauh', 'Gurunya datang'], '돈이 부족해요 berarti uangnya tidak cukup.'),
                        $this->q('그들은 내일 학교에서 한국어를 연습해요.', 'Kapan mereka latihan di sekolah?', 'Besok', ['Hari ini', 'Kemarin', 'Malam ini'], '내일 berarti besok.'),
                    ]),
                    $this->level('level-3-cerita-tantangan', 'Level 3 - Cerita Tantangan', 'Cerita Korea lebih panjang untuk latihan sulit.', 78, 30, 'hard', [
                        $this->q('유나는 숙제를 이미 끝냈어요. 수업 후에 친구에게 전화해서 같이 공부해요.', 'Apa yang sudah Yuna selesaikan?', 'Pekerjaan rumah', ['Makan malam', 'Belanja di pasar', 'Perjalanan kereta'], '숙제 berarti PR.'),
                        $this->q('도서관은 오늘 조용해요. 학생들이 시험을 준비하고 있어서 크게 말하지 않아요.', 'Mengapa perpustakaan tenang?', 'Siswa sedang menyiapkan ujian', ['Kereta datang terlambat', 'Pasar sudah tutup', 'Mereka sedang makan'], 'Cerita menyebut siswa mempersiapkan ujian.'),
                        $this->q('민재는 첫 버스를 놓쳤어요. 그래서 역까지 빨리 걸어가서 제시간에 도착했어요.', 'Apa masalah pertama Minjae?', 'Dia ketinggalan bus pertama', ['Dia kehilangan buku', 'Dia membeli air', 'Dia lupa makan'], '첫 버스를 놓쳤어요 berarti ketinggalan bus pertama.'),
                        $this->q('선생님이 조금 빠르게 말해요. 학생이 말해요: 조금 더 천천히 말해 주세요.', 'Apa yang diminta siswa?', 'Bicara lebih pelan', ['Menulis lebih besar', 'Membeli buku', 'Pergi ke pasar'], '천천히 berarti pelan-pelan.'),
                    ]),
                ],
            ],
            [
                'slug' => 'jepang',
                'name' => 'Jepang',
                'levels' => [
                    $this->level('level-1-cerita-harian', 'Level 1 - Cerita Harian', 'Cerita pendek Jepang dengan romaji dan konteks harian.', 18, 24, 'normal', [
                        $this->q('ミナは朝六時に起きます (Mina wa asa rokuji ni okimasu). 水を飲んで学校へ行きます.', 'Kapan Mina bangun?', 'Jam enam pagi', ['Jam enam malam', 'Besok siang', 'Setelah kelas'], '朝六時 berarti jam enam pagi.'),
                        $this->q('ユウタは図書館で本を読みます. 図書館は静かです.', 'Di mana Yuta membaca?', 'Di perpustakaan', ['Di pasar', 'Di rumah sakit', 'Di stasiun'], '図書館 berarti perpustakaan.'),
                        $this->q('先生がこんにちはと言います. 学生たちは返事をします.', 'Siapa yang menjawab guru?', 'Siswa', ['Ibu', 'Teman lama', 'Petugas pasar'], '学生たち berarti para siswa.'),
                        $this->q('お母さんは市場で水とパンを買います.', 'Apa yang dibeli ibu?', 'Air dan roti', ['Buku dan tas', 'Tiket dan teh', 'Pensil dan nasi'], '水 dan パン berarti air dan roti.'),
                    ]),
                    $this->level('level-2-cerita-perjalanan', 'Level 2 - Cerita Perjalanan', 'Cerita Jepang tentang arah dan tempat umum.', 50, 45, 'normal', [
                        $this->q('サキは駅を探しています. 男の人が言います: まっすぐ行って、左に曲がってください.', 'Setelah jalan lurus, Saki harus apa?', 'Belok kiri', ['Belok kanan', 'Membeli air', 'Pulang'], '左に曲がってください berarti belok kiri.'),
                        $this->q('ケンは病院で友だちを待っています. 友だちは少し遅れています.', 'Di mana Ken menunggu?', 'Di rumah sakit', ['Di sekolah', 'Di pasar', 'Di perpustakaan'], '病院 berarti rumah sakit.'),
                        $this->q('アイは本を買いたいです. でもお金が足りません.', 'Apa masalah Ai?', 'Uangnya tidak cukup', ['Bukunya hilang', 'Stasiunnya jauh', 'Gurunya datang'], 'お金が足りません berarti uangnya tidak cukup.'),
                        $this->q('彼らは明日学校で日本語を練習します.', 'Kapan mereka latihan di sekolah?', 'Besok', ['Hari ini', 'Kemarin', 'Malam ini'], '明日 berarti besok.'),
                    ]),
                    $this->level('level-3-cerita-tantangan', 'Level 3 - Cerita Tantangan', 'Cerita Jepang lebih panjang untuk latihan sulit.', 78, 30, 'hard', [
                        $this->q('リオは宿題をもう終わりました. 授業の後で友だちに電話して、一緒に勉強します.', 'Apa yang sudah Rio selesaikan?', 'Pekerjaan rumah', ['Makan malam', 'Belanja di pasar', 'Perjalanan kereta'], '宿題 berarti PR.'),
                        $this->q('図書館は今日静かです. 学生たちはテストの準備をしているので、大きな声で話しません.', 'Mengapa perpustakaan tenang?', 'Siswa sedang menyiapkan ujian', ['Kereta datang terlambat', 'Pasar sudah tutup', 'Mereka sedang makan'], 'Cerita menyebut siswa mempersiapkan ujian.'),
                        $this->q('ハルは最初のバスに乗り遅れました. だから駅まで早く歩いて、時間通りに着きました.', 'Apa masalah pertama Haru?', 'Dia ketinggalan bus pertama', ['Dia kehilangan buku', 'Dia membeli air', 'Dia lupa makan'], '最初のバスに乗り遅れました berarti ketinggalan bus pertama.'),
                        $this->q('先生は少し速く話します. 学生は言います: もう少しゆっくり話してください.', 'Apa yang diminta siswa?', 'Bicara lebih pelan', ['Menulis lebih besar', 'Membeli buku', 'Pergi ke pasar'], 'ゆっくり berarti pelan-pelan.'),
                    ]),
                ],
            ],
            [
                'slug' => 'arab',
                'name' => 'Arab',
                'levels' => [
                    $this->level('level-1-cerita-harian', 'Level 1 - Cerita Harian', 'Cerita pendek Arab dengan transliterasi dan konteks harian.', 18, 24, 'normal', [
                        $this->q('يستيقظ سامي في الساعة السادسة صباحا (yastayqiz Sami fi as-sadisah sabahan). يشرب الماء ثم يذهب إلى المدرسة.', 'Kapan Sami bangun?', 'Jam enam pagi', ['Jam enam malam', 'Besok siang', 'Setelah kelas'], 'الساعة السادسة صباحا berarti jam enam pagi.'),
                        $this->q('تقرأ ليلى كتابا في المكتبة (taqra Layla kitaban fi al-maktabah). المكتبة هادئة.', 'Di mana Layla membaca?', 'Di perpustakaan', ['Di pasar', 'Di rumah sakit', 'Di stasiun'], 'المكتبة berarti perpustakaan.'),
                        $this->q('يقول المعلم: مرحبا. يجيب الطلاب: مرحبا يا معلم.', 'Siapa yang menjawab guru?', 'Siswa', ['Ibu', 'Teman lama', 'Petugas pasar'], 'الطلاب berarti para siswa.'),
                        $this->q('تشتري الأم ماء وخبزا من السوق.', 'Apa yang dibeli ibu?', 'Air dan roti', ['Buku dan tas', 'Tiket dan teh', 'Pensil dan nasi'], 'ماء dan خبزا berarti air dan roti.'),
                    ]),
                    $this->level('level-2-cerita-perjalanan', 'Level 2 - Cerita Perjalanan', 'Cerita Arab tentang arah dan tempat umum.', 50, 45, 'normal', [
                        $this->q('تبحث نور عن المحطة. يقول رجل: اذهبي مباشرة ثم انعطفي يسارا.', 'Setelah jalan lurus, Nur harus apa?', 'Belok kiri', ['Belok kanan', 'Membeli air', 'Pulang'], 'انعطفي يسارا berarti belok kiri.'),
                        $this->q('ينتظر عمر صديقه في المستشفى. صديقه متأخر قليلا.', 'Di mana Omar menunggu?', 'Di rumah sakit', ['Di sekolah', 'Di pasar', 'Di perpustakaan'], 'المستشفى berarti rumah sakit.'),
                        $this->q('تريد سارة شراء كتاب, لكن المال لا يكفي.', 'Apa masalah Sarah?', 'Uangnya tidak cukup', ['Bukunya hilang', 'Stasiunnya jauh', 'Gurunya datang'], 'المال لا يكفي berarti uangnya tidak cukup.'),
                        $this->q('هم يذهبون غدا إلى المدرسة ليتدربوا على العربية.', 'Kapan mereka latihan di sekolah?', 'Besok', ['Hari ini', 'Kemarin', 'Malam ini'], 'غدا berarti besok.'),
                    ]),
                    $this->level('level-3-cerita-tantangan', 'Level 3 - Cerita Tantangan', 'Cerita Arab lebih panjang untuk latihan sulit.', 78, 30, 'hard', [
                        $this->q('أنهى ريو واجبه بالفعل. بعد الدرس يتصل بصديقه ليدرسا معا.', 'Apa yang sudah Rio selesaikan?', 'Pekerjaan rumah', ['Makan malam', 'Belanja di pasar', 'Perjalanan kereta'], 'واجب berarti PR atau tugas.'),
                        $this->q('المكتبة هادئة اليوم لأن الطلاب يستعدون للاختبار ولا يتكلمون بصوت عال.', 'Mengapa perpustakaan tenang?', 'Siswa sedang menyiapkan ujian', ['Kereta datang terlambat', 'Pasar sudah tutup', 'Mereka sedang makan'], 'Cerita menyebut siswa mempersiapkan ujian.'),
                        $this->q('فاتت مريم الحافلة الأولى, لذلك مشت بسرعة إلى المحطة ووصلت في الوقت المناسب.', 'Apa masalah pertama Maryam?', 'Dia ketinggalan bus pertama', ['Dia kehilangan buku', 'Dia membeli air', 'Dia lupa makan'], 'فاتت الحافلة الأولى berarti ketinggalan bus pertama.'),
                        $this->q('يتكلم المعلم بسرعة قليلا. يقول الطالب: تحدث ببطء من فضلك.', 'Apa yang diminta siswa?', 'Bicara lebih pelan', ['Menulis lebih besar', 'Membeli buku', 'Pergi ke pasar'], 'تحدث ببطء berarti bicara pelan.'),
                    ]),
                ],
            ],
            [
                'slug' => 'prancis',
                'name' => 'Prancis',
                'levels' => [
                    $this->level('level-1-cerita-harian', 'Level 1 - Cerita Harian', 'Cerita pendek Prancis dengan konteks harian.', 18, 24, 'normal', [
                        $this->q('Maya se lève à six heures. Elle boit de l eau et lit un livre avant l école.', 'Kapan Maya bangun?', 'Jam enam pagi', ['Jam enam malam', 'Besok siang', 'Setelah kelas'], 'à six heures berarti jam enam.'),
                        $this->q('Lucas lit un livre à la bibliothèque. La bibliothèque est calme.', 'Di mana Lucas membaca?', 'Di perpustakaan', ['Di pasar', 'Di rumah sakit', 'Di stasiun'], 'bibliothèque berarti perpustakaan.'),
                        $this->q('Le professeur dit bonjour. Les étudiants répondent bonjour.', 'Siapa yang menjawab guru?', 'Siswa', ['Ibu', 'Teman lama', 'Petugas pasar'], 'Les étudiants berarti para siswa.'),
                        $this->q('La mère achète de l eau et du pain au marché.', 'Apa yang dibeli ibu?', 'Air dan roti', ['Buku dan tas', 'Tiket dan teh', 'Pensil dan nasi'], 'eau dan pain berarti air dan roti.'),
                    ]),
                    $this->level('level-2-cerita-perjalanan', 'Level 2 - Cerita Perjalanan', 'Cerita Prancis tentang arah dan tempat umum.', 50, 45, 'normal', [
                        $this->q('Nina cherche la gare. Un homme dit: allez tout droit puis tournez à gauche.', 'Setelah jalan lurus, Nina harus apa?', 'Belok kiri', ['Belok kanan', 'Membeli air', 'Pulang'], 'tournez à gauche berarti belok kiri.'),
                        $this->q('Omar attend son ami à l hôpital. Son ami est un peu en retard.', 'Di mana Omar menunggu?', 'Di rumah sakit', ['Di sekolah', 'Di pasar', 'Di perpustakaan'], 'hôpital berarti rumah sakit.'),
                        $this->q('Sara veut acheter un livre, mais elle n a pas assez d argent.', 'Apa masalah Sara?', 'Uangnya tidak cukup', ['Bukunya hilang', 'Stasiunnya jauh', 'Gurunya datang'], 'pas assez d argent berarti uangnya tidak cukup.'),
                        $this->q('Ils vont demain à l école pour pratiquer le français.', 'Kapan mereka latihan di sekolah?', 'Besok', ['Hari ini', 'Kemarin', 'Malam ini'], 'demain berarti besok.'),
                    ]),
                    $this->level('level-3-cerita-tantangan', 'Level 3 - Cerita Tantangan', 'Cerita Prancis lebih panjang untuk latihan sulit.', 78, 30, 'hard', [
                        $this->q('Rio a déjà fini ses devoirs. Après le cours, il appelle son ami pour étudier ensemble.', 'Apa yang sudah Rio selesaikan?', 'Pekerjaan rumah', ['Makan malam', 'Belanja di pasar', 'Perjalanan kereta'], 'devoirs berarti PR atau tugas.'),
                        $this->q('La bibliothèque est calme aujourd hui parce que les étudiants préparent un test.', 'Mengapa perpustakaan tenang?', 'Siswa sedang menyiapkan ujian', ['Kereta datang terlambat', 'Pasar sudah tutup', 'Mereka sedang makan'], 'Cerita menyebut siswa mempersiapkan ujian.'),
                        $this->q('Mina manque le premier bus, alors elle marche vite vers la gare et arrive à l heure.', 'Apa masalah pertama Mina?', 'Dia ketinggalan bus pertama', ['Dia kehilangan buku', 'Dia membeli air', 'Dia lupa makan'], 'manque le premier bus berarti ketinggalan bus pertama.'),
                        $this->q('Le professeur parle un peu vite. L étudiant dit: pouvez-vous parler plus lentement ?', 'Apa yang diminta siswa?', 'Bicara lebih pelan', ['Menulis lebih besar', 'Membeli buku', 'Pergi ke pasar'], 'plus lentement berarti lebih pelan.'),
                    ]),
                ],
            ],
        ];
    }
}
