<?php

namespace Database\Seeders;

use App\Models\LearningLanguage;
use App\Models\LearningLevel;
use App\Models\LearningPart;
use App\Models\UserLearningProfile;
use Illuminate\Database\Seeder;

class ResetLearningLevelsSeeder extends Seeder
{
    public function run(): void
    {
        $english = LearningLanguage::query()->where('slug', 'inggris')->first();

        if (! $english) {
            $this->call(LearningCmsSeeder::class);
            $english = LearningLanguage::query()->where('slug', 'inggris')->first();
        }

        if (! $english) {
            return;
        }

        // Keep languages and parts, but remove seeded/dummy levels so admin can focus building content clearly.
        LearningLevel::query()->delete();

        $part = LearningPart::updateOrCreate([
            'learning_language_id' => $english->id,
            'slug' => 'bagian-1-introduction',
        ], [
            'title' => 'Bagian 1 Introduction',
            'subtitle' => 'Start your first English mission',
            'description' => 'Bagian awal untuk mengenal kosakata, kalimat dasar, listening, dan skenario nyata.',
            'badge_text' => 'Bagian 1',
            'level_number' => 1,
            'sort_order' => 1,
            'is_active' => true,
        ]);

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

        $firstLevel = null;

        foreach ($levels as $levelData) {
            $level = LearningLevel::create($levelData + [
                'learning_part_id' => $part->id,
                'xp_reward' => 20,
                'passing_score' => 70,
                'is_active' => true,
            ]);

            $firstLevel ??= $level;
        }

        UserLearningProfile::query()
            ->where('learning_language_id', $english->id)
            ->update([
                'current_part_id' => $part->id,
                'current_level_id' => $firstLevel?->id,
            ]);
    }
}
