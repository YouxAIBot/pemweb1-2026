<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\HomepageSection;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $sections = $this->homepageSections();

        return view('frontend.home', [
            'sections' => $sections,
        ]);
    }

    private function homepageSections()
    {
        try {
            if (Schema::hasTable('homepage_sections')) {
                $sections = HomepageSection::query()
                    ->active()
                    ->with(['items' => fn ($query) => $query->active()->orderBy('sort_order')])
                    ->orderBy('sort_order')
                    ->get()
                    ->keyBy('section_key');

                if ($sections->isNotEmpty()) {
                    return $sections;
                }
            }
        } catch (\Throwable) {
            // Fallback keeps the frontend alive before migration/seed runs.
        }

        return collect($this->fallbackSections())->keyBy('section_key');
    }

    private function fallbackSections(): array
    {
        return [
            (object) [
                'section_key' => 'hero',
                'name' => 'Section 1 - Hero',
                'kicker' => 'Belajar bahasa berbasis quiz & progress',
                'title' => 'Welcome to YoLearning Students',
                'description' => 'Pilih bahasa yang kamu inginkan, masuk ke mode belajar, kerjakan quiz, lalu lihat skor dan pembahasanmu. Nuansa halaman dibuat lebih clean, gelap, modern, dan tetap punya efek cahaya kecil seperti kunang-kunang.',
                'primary_button_label' => 'Mulai Belajar',
                'primary_button_url' => '#languages',
                'secondary_button_label' => 'Login',
                'secondary_button_url' => '/admin',
                'image_path' => null,
                'items' => collect(),
            ],
            (object) [
                'section_key' => 'languages',
                'name' => 'Section 2 - Pilih Bahasa',
                'kicker' => 'Pilih Bahasa',
                'title' => 'Pelajari bahasa yang kamu inginkan',
                'description' => 'Kartu bahasa sekarang bisa dikontrol dari admin panel. Admin dapat mengubah nama, aksen, deskripsi, status, urutan, dan gambar kartu tanpa menyentuh kode frontend.',
                'primary_button_label' => null,
                'primary_button_url' => null,
                'secondary_button_label' => null,
                'secondary_button_url' => null,
                'image_path' => null,
                'items' => collect([
                    (object) ['title' => 'Mandarin', 'accent_text' => '你好', 'description' => 'Grammar, listening, dan percakapan dasar untuk pemula.', 'badge_text' => 'Tersedia', 'url' => '#', 'image_path' => null],
                    (object) ['title' => 'Korea', 'accent_text' => '안녕', 'description' => 'Latihan hangul, kosakata, dan dialog sehari-hari.', 'badge_text' => 'Tersedia', 'url' => '#', 'image_path' => null],
                    (object) ['title' => 'Jepang', 'accent_text' => 'こんにちは', 'description' => 'Materi hiragana, frasa dasar, dan budaya praktis.', 'badge_text' => 'Segera', 'url' => '#', 'image_path' => null],
                    (object) ['title' => 'Inggris', 'accent_text' => 'Hello', 'description' => 'Vocabulary, grammar, listening, dan speaking practice.', 'badge_text' => 'Segera', 'url' => '#', 'image_path' => null],
                    (object) ['title' => 'Arab', 'accent_text' => 'مرحبا', 'description' => 'Belajar huruf, kosakata, dan kalimat harian.', 'badge_text' => 'Segera', 'url' => '#', 'image_path' => null],
                    (object) ['title' => 'Prancis', 'accent_text' => 'Bonjour', 'description' => 'Frasa populer, pengucapan, dan percakapan ringan.', 'badge_text' => 'Segera', 'url' => '#', 'image_path' => null],
                ]),
            ],
            (object) [
                'section_key' => 'tournament',
                'name' => 'Section 3 - Tournament',
                'kicker' => 'Challenge Mode',
                'title' => 'Bertandinglah dengan user lain dan jadilah nomor satu',
                'description' => 'Section ini disiapkan untuk fitur battle mode seperti Kahoot. Untuk tahap awal, tampilannya sudah siap; logic real-time bisa dibuat setelah quiz solo stabil.',
                'primary_button_label' => null,
                'primary_button_url' => null,
                'secondary_button_label' => null,
                'secondary_button_url' => null,
                'image_path' => null,
                'items' => collect([
                    (object) ['label' => '1', 'title' => 'Room Challenge', 'description' => 'User dapat masuk ke room, menjawab soal, dan mengumpulkan skor.'],
                    (object) ['label' => '2', 'title' => 'Leaderboard', 'description' => 'Ranking ditampilkan berdasarkan jawaban benar, skor, dan kecepatan.'],
                    (object) ['label' => '3', 'title' => 'Review Jawaban', 'description' => 'Setelah bermain, user tetap bisa belajar dari pembahasan soal.'],
                ]),
            ],
            (object) [
                'section_key' => 'cta',
                'name' => 'Section 4 - CTA',
                'kicker' => 'Mulai Sekarang',
                'title' => 'Mulai perjalananmu dengan kami. Daftar sekarang.',
                'description' => 'Halaman ini sudah dibuat sebagai landing page awal. Setelah ini kita bisa lanjut satu per satu ke page Language Detail, Mode Detail, Lesson Detail, Quiz, Result, dan Review.',
                'primary_button_label' => 'Pilih Bahasa',
                'primary_button_url' => '#languages',
                'secondary_button_label' => 'Masuk Admin',
                'secondary_button_url' => '/admin',
                'image_path' => null,
                'items' => collect(),
            ],
        ];
    }
}
