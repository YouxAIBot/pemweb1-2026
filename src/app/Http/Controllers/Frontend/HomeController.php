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
                'kicker' => 'Belajar bahasa interaktif dengan progress nyata',
                'title' => 'Belajar Bahasa Bersama YoLearning',
                'description' => 'YoLearning membantu kamu belajar bahasa asing secara bertahap lewat kosakata, huruf, listening, reading story, latihan soal, XP, dan pembahasan yang tersimpan di setiap akun.',
                'primary_button_label' => 'Daftar Sekarang',
                'primary_button_url' => '/register',
                'secondary_button_label' => 'Masuk Akun',
                'secondary_button_url' => '/login',
                'image_path' => null,
                'items' => collect(),
            ],
            (object) [
                'section_key' => 'languages',
                'name' => 'Section 2 - Pilih Bahasa',
                'kicker' => 'Pilih Bahasa',
                'title' => 'Pelajari bahasa yang kamu inginkan',
                'description' => 'Pilih bahasa aktif dan mulai dari dasar. Setiap bahasa memiliki bagian, level, latihan huruf, audio, dan soal yang disusun bertahap dari sapaan sampai percakapan.',
                'primary_button_label' => null,
                'primary_button_url' => null,
                'secondary_button_label' => null,
                'secondary_button_url' => null,
                'image_path' => null,
                'items' => collect([
                    (object) ['title' => 'Mandarin', 'accent_text' => 'Ni hao', 'description' => 'Kosakata dasar, pinyin, nada, listening, dan dialog harian untuk pemula.', 'badge_text' => 'Tersedia', 'url' => '#', 'image_path' => null],
                    (object) ['title' => 'Korea', 'accent_text' => 'Annyeong', 'description' => 'Hangul, pelafalan, kosakata, dan percakapan ringan sehari-hari.', 'badge_text' => 'Tersedia', 'url' => '#', 'image_path' => null],
                    (object) ['title' => 'Jepang', 'accent_text' => 'Konnichiwa', 'description' => 'Hiragana, frasa dasar, kosakata, dan latihan membaca sederhana.', 'badge_text' => 'Tersedia', 'url' => '#', 'image_path' => null],
                    (object) ['title' => 'Inggris', 'accent_text' => 'Hello', 'description' => 'Vocabulary, grammar, listening, reading story, dan percakapan dasar.', 'badge_text' => 'Tersedia', 'url' => '#', 'image_path' => null],
                    (object) ['title' => 'Arab', 'accent_text' => 'Marhaban', 'description' => 'Huruf Arab, pelafalan, kosakata, dan kalimat harian bertahap.', 'badge_text' => 'Tersedia', 'url' => '#', 'image_path' => null],
                    (object) ['title' => 'Prancis', 'accent_text' => 'Bonjour', 'description' => 'Frasa populer, pengucapan, kosakata, dan dialog ringan.', 'badge_text' => 'Tersedia', 'url' => '#', 'image_path' => null],
                ]),
            ],
            (object) [
                'section_key' => 'tournament',
                'name' => 'Section 3 - Tournament',
                'kicker' => 'Challenge Mode',
                'title' => 'Bertanding, ukur kemampuan, dan naikkan peringkat',
                'description' => 'Uji pemahaman lewat turnamen cepat, duel 1v1, dan Quiz Room. Setiap mode memakai skor, timer, leaderboard, dan riwayat agar latihan terasa lebih hidup.',
                'primary_button_label' => null,
                'primary_button_url' => null,
                'secondary_button_label' => null,
                'secondary_button_url' => null,
                'image_path' => null,
                'items' => collect([
                    (object) ['label' => '1', 'title' => 'Turnamen Cepat', 'description' => 'Jawab soal acak berbatas waktu untuk melatih kecepatan dan pemahaman.'],
                    (object) ['label' => '2', 'title' => 'Duel 1v1', 'description' => 'Cari lawan dengan bahasa dan tingkat kesulitan yang sama, lalu adu skor secara langsung.'],
                    (object) ['label' => '3', 'title' => 'Quiz Room', 'description' => 'Buat room, bagikan kode, dan kerjakan soal bersama seperti kuis kelas.'],
                ]),
            ],
            (object) [
                'section_key' => 'cta',
                'name' => 'Section 4 - CTA',
                'kicker' => 'Mulai Sekarang',
                'title' => 'Mulai belajar dan simpan progressmu.',
                'description' => 'Daftar untuk memilih bahasa, menyimpan XP, membuka level berikutnya, mengikuti mode kompetitif, dan memakai premium saat ingin belajar tanpa iklan.',
                'primary_button_label' => 'Daftar Sekarang',
                'primary_button_url' => '/register',
                'secondary_button_label' => 'Masuk Akun',
                'secondary_button_url' => '/login',
                'image_path' => null,
                'items' => collect(),
            ],
        ];
    }
}
