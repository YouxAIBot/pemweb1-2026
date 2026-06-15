<?php

namespace Database\Seeders;

use App\Models\LearningLanguage;
use App\Models\LearningLevel;
use App\Models\LearningPart;
use App\Models\LearningQuestion;
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

            $levels = [
                ['title' => 'Pilihan Ganda Dasar', 'slug' => 'pilihan-ganda-dasar', 'type' => 'multiple_choice', 'short_label' => '1', 'description' => 'Pilih jawaban yang paling tepat.', 'sort_order' => 1, 'position_x' => 16, 'position_y' => 22],
                ['title' => 'Sambung Kata', 'slug' => 'sambung-kata', 'type' => 'word_match', 'short_label' => '2', 'description' => 'Cocokkan kata dengan arti yang benar.', 'sort_order' => 2, 'position_x' => 48, 'position_y' => 18],
                ['title' => 'Listening Pemula', 'slug' => 'listening-pemula', 'type' => 'listening', 'short_label' => '3', 'description' => 'Dengarkan audio lalu pilih jawaban.', 'sort_order' => 3, 'position_x' => 78, 'position_y' => 32],
                ['title' => 'Soal Situasi Nyata', 'slug' => 'soal-situasi-nyata', 'type' => 'real_case', 'short_label' => '4', 'description' => 'Jawab berdasarkan konteks kehidupan nyata.', 'sort_order' => 4, 'position_x' => 54, 'position_y' => 55],
                ['title' => 'Mix Challenge', 'slug' => 'mix-challenge', 'type' => 'mixed', 'short_label' => '5', 'description' => 'Gabungan semua tipe latihan.', 'sort_order' => 5, 'position_x' => 18, 'position_y' => 48],
                ['title' => 'Mini Review', 'slug' => 'mini-review', 'type' => 'mixed', 'short_label' => '6', 'description' => 'Review cepat sebelum naik bagian.', 'sort_order' => 6, 'position_x' => 24, 'position_y' => 78],
                ['title' => 'Final Mission', 'slug' => 'final-mission', 'type' => 'real_case', 'short_label' => '7', 'description' => 'Selesaikan misi terakhir bagian ini.', 'sort_order' => 7, 'position_x' => 55, 'position_y' => 84],
                ['title' => 'Boss Level', 'slug' => 'boss-level', 'type' => 'mixed', 'short_label' => '8', 'description' => 'Tantangan akhir bagian 1.', 'sort_order' => 8, 'position_x' => 78, 'position_y' => 70],
            ];

            foreach ($levels as $levelData) {
                $level = LearningLevel::updateOrCreate([
                    'learning_part_id' => $part->id,
                    'slug' => $levelData['slug'],
                ], $levelData + [
                    'xp_reward' => 20,
                    'passing_score' => 70,
                    'is_active' => true,
                ]);

                $question = LearningQuestion::firstOrCreate([
                    'learning_level_id' => $level->id,
                    'sort_order' => 1,
                ], [
                    'type' => $level->type,
                    'instruction' => $level->type === 'listening' ? 'Dengarkan audio dan pilih arti yang benar.' : 'Pilih jawaban terbaik.',
                    'question_text' => $level->type === 'real_case'
                        ? 'Kamu bertemu orang baru. Respons mana yang paling natural untuk memulai percakapan?'
                        : 'Apa jawaban yang paling tepat untuk latihan ini?',
                    'correct_answer' => 'Jawaban A',
                    'explanation' => 'Pembahasan ini bisa diganti admin dari Filament. Untuk listening, admin juga bisa upload file suara.',
                    'points' => 10,
                    'is_active' => true,
                ]);

                if ($question->options()->count() === 0) {
                    $question->options()->createMany([
                        ['option_text' => 'Jawaban A', 'is_correct' => true, 'sort_order' => 1],
                        ['option_text' => 'Jawaban B', 'is_correct' => false, 'sort_order' => 2],
                        ['option_text' => 'Jawaban C', 'is_correct' => false, 'sort_order' => 3],
                    ]);
                }
            }
        }
    }
}
