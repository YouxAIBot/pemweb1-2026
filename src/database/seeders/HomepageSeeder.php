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
            'meta_description' => 'YoLearning adalah platform belajar bahasa berbasis quiz, progress, dan tantangan.',
            'footer_left' => '© 2026 YoLearning. Semua progres belajar tersimpan rapi.',
            'footer_right' => 'Belajar bahasa • Quiz • Tournament',
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
                'kicker' => 'Belajar bahasa berbasis quiz & progress',
                'title' => 'Welcome to YoLearning Students',
                'description' => 'Pilih bahasa yang kamu inginkan, masuk ke mode belajar, kerjakan quiz, lalu lihat skor dan pembahasanmu. Nuansa halaman dibuat lebih clean, gelap, modern, dan tetap punya efek cahaya kecil seperti kunang-kunang.',
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
                'description' => 'Kartu bahasa sekarang bisa dikontrol dari admin panel. Admin dapat mengubah nama, aksen, deskripsi, status, urutan, dan gambar kartu tanpa menyentuh kode frontend.',
                'sort_order' => 2,
            ],
            'tournament' => [
                'name' => 'Section 3 - Tournament',
                'kicker' => 'Challenge Mode',
                'title' => 'Bertandinglah dengan user lain dan jadilah nomor satu',
                'description' => 'Section ini disiapkan untuk fitur battle mode seperti Kahoot. Untuk tahap awal, tampilannya sudah siap; logic real-time bisa dibuat setelah quiz solo stabil.',
                'sort_order' => 3,
            ],
            'cta' => [
                'name' => 'Section 4 - CTA',
                'kicker' => 'Mulai Sekarang',
                'title' => 'Mulai perjalananmu dengan kami. Daftar sekarang.',
                'description' => 'Halaman ini sudah dibuat sebagai landing page awal. Setelah ini kita bisa lanjut satu per satu ke page Language Detail, Mode Detail, Lesson Detail, Quiz, Result, dan Review.',
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
            ['item_key' => 'mandarin', 'title' => 'Mandarin', 'accent_text' => '你好', 'description' => 'Grammar, listening, dan percakapan dasar untuk pemula.', 'badge_text' => 'Tersedia', 'sort_order' => 1],
            ['item_key' => 'korea', 'title' => 'Korea', 'accent_text' => '안녕', 'description' => 'Latihan hangul, kosakata, dan dialog sehari-hari.', 'badge_text' => 'Tersedia', 'sort_order' => 2],
            ['item_key' => 'jepang', 'title' => 'Jepang', 'accent_text' => 'こんにちは', 'description' => 'Materi hiragana, frasa dasar, dan budaya praktis.', 'badge_text' => 'Segera', 'sort_order' => 3],
            ['item_key' => 'inggris', 'title' => 'Inggris', 'accent_text' => 'Hello', 'description' => 'Vocabulary, grammar, listening, dan speaking practice.', 'badge_text' => 'Segera', 'sort_order' => 4],
            ['item_key' => 'arab', 'title' => 'Arab', 'accent_text' => 'مرحبا', 'description' => 'Belajar huruf, kosakata, dan kalimat harian.', 'badge_text' => 'Segera', 'sort_order' => 5],
            ['item_key' => 'prancis', 'title' => 'Prancis', 'accent_text' => 'Bonjour', 'description' => 'Frasa populer, pengucapan, dan percakapan ringan.', 'badge_text' => 'Segera', 'sort_order' => 6],
        ];

        foreach ($languageItems as $item) {
            HomepageSectionItem::firstOrCreate([
                'homepage_section_id' => $sectionModels['languages']->id,
                'item_key' => $item['item_key'],
            ], $item + ['is_active' => true]);
        }

        $tournamentItems = [
            ['item_key' => 'room-challenge', 'label' => '1', 'title' => 'Room Challenge', 'description' => 'User dapat masuk ke room, menjawab soal, dan mengumpulkan skor.', 'sort_order' => 1],
            ['item_key' => 'leaderboard', 'label' => '2', 'title' => 'Leaderboard', 'description' => 'Ranking ditampilkan berdasarkan jawaban benar, skor, dan kecepatan.', 'sort_order' => 2],
            ['item_key' => 'review-jawaban', 'label' => '3', 'title' => 'Review Jawaban', 'description' => 'Setelah bermain, user tetap bisa belajar dari pembahasan soal.', 'sort_order' => 3],
        ];

        foreach ($tournamentItems as $item) {
            HomepageSectionItem::firstOrCreate([
                'homepage_section_id' => $sectionModels['tournament']->id,
                'item_key' => $item['item_key'],
            ], $item + ['is_active' => true]);
        }
    }
}
