<?php

namespace Database\Seeders;

use App\Models\LearningLanguage;
use App\Models\LearningLevel;
use App\Models\LearningPart;
use Illuminate\Database\Seeder;

class LearningCmsSeeder extends Seeder
{
    public function run(): void
    {
        $languages = [
            ['name' => 'Inggris', 'slug' => 'inggris', 'native_name' => 'Hello', 'flag_label' => 'EN', 'description' => 'Vocabulary, grammar, listening, dan real-case practice.', 'accent_color' => '#66e8f7', 'sort_order' => 1],
            ['name' => 'Mandarin', 'slug' => 'mandarin', 'native_name' => '你好', 'flag_label' => 'CN', 'description' => 'Grammar, listening, dan percakapan dasar untuk pemula.', 'accent_color' => '#6e7cf7', 'sort_order' => 2],
            ['name' => 'Korea', 'slug' => 'korea', 'native_name' => '안녕', 'flag_label' => 'KR', 'description' => 'Hangul, kosakata, dan dialog sehari-hari.', 'accent_color' => '#9d7cff', 'sort_order' => 3],
            ['name' => 'Jepang', 'slug' => 'jepang', 'native_name' => 'こんにちは', 'flag_label' => 'JP', 'description' => 'Hiragana, frasa dasar, dan budaya praktis.', 'accent_color' => '#ff9bb3', 'sort_order' => 4],
            ['name' => 'Arab', 'slug' => 'arab', 'native_name' => 'مرحبا', 'flag_label' => 'AR', 'description' => 'Huruf, kosakata, dan kalimat harian.', 'accent_color' => '#49d38b', 'sort_order' => 5],
            ['name' => 'Prancis', 'slug' => 'prancis', 'native_name' => 'Bonjour', 'flag_label' => 'FR', 'description' => 'Frasa populer dan percakapan ringan.', 'accent_color' => '#fff3a8', 'sort_order' => 6],
        ];

        foreach ($languages as $languageData) {
            $language = LearningLanguage::updateOrCreate([
                'slug' => $languageData['slug'],
            ], $languageData + ['is_active' => true]);

            $part = LearningPart::updateOrCreate([
                'learning_language_id' => $language->id,
                'slug' => 'bagian-1-introduction',
            ], [
                'title' => 'Bagian 1 Introduction',
                'subtitle' => 'Start your first language mission',
                'description' => 'Bagian awal untuk mengenal kosakata, kalimat dasar, listening, dan skenario nyata.',
                'badge_text' => 'Bagian 1',
                'level_number' => 1,
                'sort_order' => 1,
                'is_active' => true,
            ]);

            if ($languageData['slug'] !== 'inggris') {
                continue;
            }

            $levels = [
                [
                    'title' => 'Level 1 - Introduction',
                    'slug' => 'level-1-introduction',
                    'type' => 'mixed',
                    'short_label' => '1',
                    'description' => 'Level awal untuk mengenal kosakata dan instruksi dasar.',
                    'sort_order' => 1,
                    'position_x' => 16,
                    'position_y' => 24,
                ],
                [
                    'title' => 'Level 2 - Basic Vocabulary',
                    'slug' => 'level-2-basic-vocabulary',
                    'type' => 'mixed',
                    'short_label' => '2',
                    'description' => 'Latihan kosakata dasar dengan beberapa jenis soal.',
                    'sort_order' => 2,
                    'position_x' => 45,
                    'position_y' => 42,
                ],
                [
                    'title' => 'Level 3 - Simple Sentence',
                    'slug' => 'level-3-simple-sentence',
                    'type' => 'mixed',
                    'short_label' => '3',
                    'description' => 'Menyusun dan memahami kalimat sederhana.',
                    'sort_order' => 3,
                    'position_x' => 72,
                    'position_y' => 28,
                ],
                [
                    'title' => 'Level 4 - Listening Practice',
                    'slug' => 'level-4-listening-practice',
                    'type' => 'mixed',
                    'short_label' => '4',
                    'description' => 'Latihan listening menggunakan alur cerita dan pertanyaan.',
                    'sort_order' => 4,
                    'position_x' => 56,
                    'position_y' => 64,
                ],
                [
                    'title' => 'Level 5 - Daily Conversation',
                    'slug' => 'level-5-daily-conversation',
                    'type' => 'mixed',
                    'short_label' => '5',
                    'description' => 'Latihan percakapan harian dan skenario nyata.',
                    'sort_order' => 5,
                    'position_x' => 25,
                    'position_y' => 76,
                ],
            ];

            foreach ($levels as $levelData) {
                LearningLevel::updateOrCreate([
                    'learning_part_id' => $part->id,
                    'slug' => $levelData['slug'],
                ], $levelData + [
                    'xp_reward' => 20,
                    'passing_score' => 70,
                    'is_active' => true,
                ]);
            }
        }
    }
}
