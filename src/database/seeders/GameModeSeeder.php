<?php

namespace Database\Seeders;

use App\Models\GameMode;
use Illuminate\Database\Seeder;

class GameModeSeeder extends Seeder
{
    public function run(): void
    {
        $games = [
            [
                'key' => 'tournament',
                'title' => 'Turnamen Cepat',
                'subtitle' => 'Challenge 5 soal',
                'description' => 'Jawab 5 soal acak, kumpulkan skor, dan naik leaderboard.',
                'icon_label' => '⚡',
                'route_name' => 'learning.tournament',
                'button_label' => 'Mulai',
                'status' => 'active',
                'sort_order' => 1,
            ],
            [
                'key' => 'duel_1v1',
                'title' => 'Duel 1 vs 1',
                'subtitle' => 'Adu cepat lawan teman',
                'description' => 'Mode duel real-time untuk menjawab soal melawan user lain.',
                'icon_label' => '⚔',
                'route_name' => 'learning.duel.lobby',
                'button_label' => 'Cari Lawan',
                'status' => 'active',
                'sort_order' => 2,
            ],
            [
                'key' => 'kahoot_quiz',
                'title' => 'Quiz Room',
                'subtitle' => 'Mode seperti Kahoot',
                'description' => 'Buat room, tampilkan PIN, lalu peserta menjawab soal bersama.',
                'icon_label' => '🎯',
                'route_name' => 'learning.quiz.index',
                'button_label' => 'Buka Room',
                'status' => 'active',
                'sort_order' => 3,
            ],
            [
                'key' => 'video_question',
                'title' => 'Video Question',
                'subtitle' => 'Soal dari video',
                'description' => 'User menonton video pendek lalu menjawab pertanyaan setelahnya.',
                'icon_label' => '▶',
                'route_name' => 'learning.video-question',
                'button_label' => 'Mulai Video',
                'status' => 'disabled',
                'sort_order' => 4,
                'is_active' => false,
            ],
            [
                'key' => 'daily_boss',
                'title' => 'Daily Boss',
                'subtitle' => 'Tantangan harian',
                'description' => 'Satu challenge sulit per hari untuk mengejar XP dan ranking.',
                'icon_label' => '👑',
                'route_name' => null,
                'button_label' => 'Segera Hadir',
                'status' => 'coming_soon',
                'sort_order' => 5,
                'is_active' => false,
            ],
        ];

        foreach ($games as $game) {
            GameMode::updateOrCreate([
                'key' => $game['key'],
            ], $game + [
                'is_active' => $game['is_active'] ?? true,
                'settings' => [],
            ]);
        }
    }
}
