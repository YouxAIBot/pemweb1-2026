<?php

namespace Database\Seeders;

use App\Models\DashboardDailyMission;
use App\Models\DashboardMenu;
use App\Models\DashboardSetting;
use Illuminate\Database\Seeder;

class DashboardSeeder extends Seeder
{
    public function run(): void
    {
        DashboardSetting::updateOrCreate(['id' => 1], [
            'brand_text' => 'YoLearning',
            'welcome_text' => 'Selamat datang',
            'adventure_text' => 'Ayo mulai petualanganmu',
            'language_title' => 'Pilih bahasa untuk dipelajari',
            'ability_title' => 'Seberapa jauh Anda memahami bahasa ini?',
            'dashboard_title' => 'Petualangan belajar kamu',
            'dashboard_subtitle' => 'Pilih bagian, lanjutkan progress, dan naikkan level bahasa. Setiap akun punya progress sendiri-sendiri.',
            'part_button_label' => 'Buka Bagian',
            'theme' => 'discord_dark',
            'animations_enabled' => true,
        ]);

        $menus = [
            ['label' => 'Bahasa', 'url' => '/dashboard', 'icon_label' => '文', 'sort_order' => 1],
            ['label' => 'Huruf', 'url' => '#', 'icon_label' => 'Aa', 'sort_order' => 2],
            ['label' => 'Toko', 'url' => '#', 'icon_label' => '◈', 'sort_order' => 3],
            ['label' => 'Misi', 'url' => '#', 'icon_label' => '✓', 'sort_order' => 4],
            ['label' => 'Turnamen', 'url' => '/turnamen', 'icon_label' => '⚡', 'sort_order' => 5],
            ['label' => 'Pengaturan', 'url' => '#', 'icon_label' => '⚙', 'sort_order' => 6],
        ];

        DashboardMenu::whereIn('label', ['Games', 'Turnamen & Games'])->update(['is_active' => false]);

        foreach ($menus as $menu) {
            DashboardMenu::updateOrCreate(['label' => $menu['label']], $menu + ['menu_group' => 'main', 'is_active' => true]);
        }

        $missions = [
            ['title' => 'Kerjakan 5 soal', 'mission_type' => 'questions_answered', 'target' => 5, 'default_progress' => 0, 'unit_label' => 'soal', 'sort_order' => 1],
            ['title' => 'Belajar selama 10 menit', 'mission_type' => 'study_minutes', 'target' => 10, 'default_progress' => 0, 'unit_label' => 'menit', 'sort_order' => 2],
            ['title' => 'Kerjakan 20 soal', 'mission_type' => 'questions_answered', 'target' => 20, 'default_progress' => 0, 'unit_label' => 'soal', 'sort_order' => 3],
        ];

        foreach ($missions as $mission) {
            DashboardDailyMission::updateOrCreate(['title' => $mission['title']], $mission + ['is_active' => true]);
        }
    }
}
