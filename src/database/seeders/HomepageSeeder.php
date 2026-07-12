<?php

namespace Database\Seeders;

use App\Models\HomepageNavItem;
use App\Models\HomepageSection;
use App\Models\HomepageSectionItem;
use App\Models\HomepageSetting;
use Illuminate\Database\Seeder;

class HomepageSeeder extends Seeder
{
    public function run(): void
    {
        HomepageSetting::updateOrCreate([
            'id' => 1,
        ], [
            'site_name' => 'YoLearning',
            'brand_text' => 'YoLearning',
            'brand_initial' => 'Y',
            'meta_title' => 'YoLearning - Belajar Bahasa Interaktif',
            'meta_description' => 'YoLearning adalah platform belajar bahasa asing berbasis level, audio, quiz, progress XP, premium, dan mode kompetitif.',
            'footer_left' => '(c) 2026 YoLearning. Progress belajar tersimpan rapi.',
            'footer_right' => 'Belajar bahasa | Quiz | Tournament',
            'cursor_glow_enabled' => true,
            'cursor_glow_size' => 18,
        ]);

        $navItems = [
            ['label' => 'Home', 'url' => '#home', 'style' => 'link', 'sort_order' => 1],
            ['label' => 'Bahasa', 'url' => '#languages', 'style' => 'link', 'sort_order' => 2],
            ['label' => 'Tournament', 'url' => '#tournament', 'style' => 'link', 'sort_order' => 3],
            ['label' => 'Daftar', 'url' => '/register', 'style' => 'soft', 'sort_order' => 4],
            ['label' => 'Login', 'url' => '/login', 'style' => 'primary', 'sort_order' => 5],
        ];

        foreach ($navItems as $item) {
            HomepageNavItem::updateOrCreate([
                'label' => $item['label'],
            ], $item + ['is_active' => true]);
        }

        $sections = [
            'hero' => [
                'name' => 'Section 1 - Hero',
                'kicker' => 'Belajar bahasa interaktif dengan progress nyata',
                'title' => 'Belajar Bahasa Bersama YoLearning',
                'description' => 'YoLearning membantu kamu belajar bahasa asing secara bertahap lewat kosakata, huruf, listening, reading story, latihan soal, XP, dan pembahasan yang tersimpan di setiap akun.',
                'primary_button_label' => 'Daftar Sekarang',
                'primary_button_url' => '/register',
                'secondary_button_label' => 'Masuk Akun',
                'secondary_button_url' => '/login',
                'sort_order' => 1,
            ],
            'languages' => [
                'name' => 'Section 2 - Pilih Bahasa',
                'kicker' => 'Pilih Bahasa',
                'title' => 'Pelajari bahasa yang kamu inginkan',
                'description' => 'Pilih bahasa aktif dan mulai dari dasar. Setiap bahasa memiliki bagian, level, latihan huruf, audio, dan soal yang disusun bertahap dari sapaan sampai percakapan.',
                'sort_order' => 2,
            ],
            'tournament' => [
                'name' => 'Section 3 - Tournament',
                'kicker' => 'Challenge Mode',
                'title' => 'Bertanding, ukur kemampuan, dan naikkan peringkat',
                'description' => 'Uji pemahaman lewat turnamen cepat, duel 1v1, dan Quiz Room. Setiap mode memakai skor, timer, leaderboard, dan riwayat agar latihan terasa lebih hidup.',
                'sort_order' => 3,
            ],
            'cta' => [
                'name' => 'Section 4 - CTA',
                'kicker' => 'Mulai Sekarang',
                'title' => 'Mulai belajar dan simpan progressmu.',
                'description' => 'Daftar untuk memilih bahasa, menyimpan XP, membuka level berikutnya, mengikuti mode kompetitif, dan memakai premium saat ingin belajar tanpa iklan.',
                'primary_button_label' => 'Daftar Sekarang',
                'primary_button_url' => '/register',
                'secondary_button_label' => 'Masuk Akun',
                'secondary_button_url' => '/login',
                'sort_order' => 4,
            ],
        ];

        $sectionModels = [];
        foreach ($sections as $key => $section) {
            $sectionModels[$key] = HomepageSection::updateOrCreate([
                'section_key' => $key,
            ], $section + ['is_active' => true]);
        }

        $languageItems = [
            ['item_key' => 'mandarin', 'title' => 'Mandarin', 'accent_text' => 'Ni hao', 'description' => 'Kosakata dasar, pinyin, nada, listening, dan dialog harian untuk pemula.', 'badge_text' => 'Tersedia', 'sort_order' => 1],
            ['item_key' => 'korea', 'title' => 'Korea', 'accent_text' => 'Annyeong', 'description' => 'Hangul, pelafalan, kosakata, dan percakapan ringan sehari-hari.', 'badge_text' => 'Tersedia', 'sort_order' => 2],
            ['item_key' => 'jepang', 'title' => 'Jepang', 'accent_text' => 'Konnichiwa', 'description' => 'Hiragana, frasa dasar, kosakata, dan latihan membaca sederhana.', 'badge_text' => 'Tersedia', 'sort_order' => 3],
            ['item_key' => 'inggris', 'title' => 'Inggris', 'accent_text' => 'Hello', 'description' => 'Vocabulary, grammar, listening, reading story, dan percakapan dasar.', 'badge_text' => 'Tersedia', 'sort_order' => 4],
            ['item_key' => 'arab', 'title' => 'Arab', 'accent_text' => 'Marhaban', 'description' => 'Huruf Arab, pelafalan, kosakata, dan kalimat harian bertahap.', 'badge_text' => 'Tersedia', 'sort_order' => 5],
            ['item_key' => 'prancis', 'title' => 'Prancis', 'accent_text' => 'Bonjour', 'description' => 'Frasa populer, pengucapan, kosakata, dan dialog ringan.', 'badge_text' => 'Tersedia', 'sort_order' => 6],
        ];

        foreach ($languageItems as $item) {
            HomepageSectionItem::updateOrCreate([
                'homepage_section_id' => $sectionModels['languages']->id,
                'item_key' => $item['item_key'],
            ], $item + ['is_active' => true]);
        }

        $tournamentItems = [
            ['item_key' => 'fast-battle', 'label' => '1', 'title' => 'Turnamen Cepat', 'description' => 'Jawab soal acak berbatas waktu untuk melatih kecepatan dan pemahaman.', 'sort_order' => 1],
            ['item_key' => 'duel-1v1', 'label' => '2', 'title' => 'Duel 1v1', 'description' => 'Cari lawan dengan bahasa dan tingkat kesulitan yang sama, lalu adu skor secara langsung.', 'sort_order' => 2],
            ['item_key' => 'quiz-room', 'label' => '3', 'title' => 'Quiz Room', 'description' => 'Buat room, bagikan kode, dan kerjakan soal bersama seperti kuis kelas.', 'sort_order' => 3],
        ];

        foreach ($tournamentItems as $item) {
            HomepageSectionItem::updateOrCreate([
                'homepage_section_id' => $sectionModels['tournament']->id,
                'item_key' => $item['item_key'],
            ], $item + ['is_active' => true]);
        }
    }
}
