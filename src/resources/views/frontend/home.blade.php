@extends('layouts.frontend')

@section('title', 'YoLearning - Belajar Bahasa Interaktif')

@section('content')
    <section class="section hero-section" id="home">
        <div class="container hero-grid">
            <div>
                <span class="eyebrow">
                    <span class="pulse-dot"></span>
                    Belajar bahasa berbasis quiz & progress
                </span>

                <h1 class="hero-title">
                    Welcome to <span class="text-gradient">YoLearning</span> Students
                </h1>

                <p class="hero-copy">
                    Pilih bahasa yang kamu inginkan, masuk ke mode belajar, kerjakan quiz, lalu lihat skor dan pembahasanmu. Desain ini mengikuti wireframe awal, tetapi dibuat dengan nuansa biru gelap, neon lembut, dan atmosfer seperti kunang-kunang di kegelapan.
                </p>

                <div class="hero-actions">
                    <a href="#languages" class="button button-primary">Mulai Belajar</a>
                    <a href="#tournament" class="button button-ghost">Lihat Tournament</a>
                </div>
            </div>

            <div class="hero-card" aria-label="Preview dashboard YoLearning">
                <div class="hero-card-content">
                    <div class="study-window">
                        <div class="window-bar">
                            <span class="window-dot"></span>
                            <span class="window-dot"></span>
                            <span class="window-dot"></span>
                        </div>

                        <div class="window-body">
                            <div class="lesson-preview">
                                <div class="lesson-row">
                                    <div>
                                        <strong>Mandarin Basic</strong><br>
                                        <span>Listening Practice</span>
                                    </div>
                                    <div class="lesson-score">92%</div>
                                </div>

                                <div class="lesson-row">
                                    <div>
                                        <strong>Korea Daily Talk</strong><br>
                                        <span>Conversation Quiz</span>
                                    </div>
                                    <div class="lesson-score">86%</div>
                                </div>

                                <div class="lesson-row">
                                    <div>
                                        <strong>Grammar Mix</strong><br>
                                        <span>10 soal interaktif</span>
                                    </div>
                                    <div class="lesson-score">+50 XP</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="floating-badge">
                        <p>Live Progress</p>
                        <p>Skor, XP, dan pembahasan dibuat siap untuk disambungkan ke database Laravel.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section split-section" id="languages">
        <div class="container split-grid">
            <div>
                <p class="section-kicker">Pilih Bahasa</p>
                <h2 class="section-title">Pelajari bahasa yang kamu inginkan</h2>
                <p class="section-copy">
                    Kartu bahasa di bawah masih menggunakan data dummy dari controller. Nanti saat CMS dibuat, bagian ini tinggal membaca data dari tabel <strong>languages</strong> tanpa mengubah desain halaman.
                </p>
                <div class="hero-actions">
                    <a href="#start" class="button button-primary">Daftar Sekarang</a>
                </div>
            </div>

            <div class="language-cloud" aria-label="Daftar bahasa">
                @foreach ($languages as $language)
                    <a href="#" class="language-card">
                        <div class="language-accent">{{ $language['accent'] }}</div>
                        <div class="language-name">{{ $language['name'] }}</div>
                        <p class="language-desc">{{ $language['description'] }}</p>
                        <span class="language-status">{{ $language['status'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section tournament-section" id="tournament">
        <div class="container tournament-grid">
            <div class="arena-card" aria-label="Gambar turnamen">
                <div class="arena-ring"></div>
                <div class="arena-trophy">
                    <div>
                        <div class="trophy-icon">🏆</div>
                        <p class="trophy-label">Gambar Tournament</p>
                    </div>
                </div>
            </div>

            <div>
                <p class="section-kicker">Challenge Mode</p>
                <h2 class="section-title">Bertandinglah dengan user lain dan jadilah nomor satu</h2>
                <p class="section-copy">
                    Section ini disiapkan untuk fitur battle mode seperti Kahoot. Untuk tahap awal, tampilannya sudah siap; logic real-time bisa dibuat setelah quiz solo stabil.
                </p>

                <div class="feature-list">
                    <div class="feature-item">
                        <span class="feature-icon">1</span>
                        <div>
                            <strong>Room Challenge</strong>
                            <p>User dapat masuk ke room, menjawab soal, dan mengumpulkan skor.</p>
                        </div>
                    </div>

                    <div class="feature-item">
                        <span class="feature-icon">2</span>
                        <div>
                            <strong>Leaderboard</strong>
                            <p>Ranking ditampilkan berdasarkan jawaban benar, skor, dan kecepatan.</p>
                        </div>
                    </div>

                    <div class="feature-item">
                        <span class="feature-icon">3</span>
                        <div>
                            <strong>Review Jawaban</strong>
                            <p>Setelah bermain, user tetap bisa belajar dari pembahasan soal.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section cta-section" id="start">
        <div class="container">
            <div class="cta-card">
                <p class="section-kicker">Mulai Sekarang</p>
                <h2 class="section-title">Mulai perjalananmu dengan kami. Daftar sekarang.</h2>
                <p class="section-copy" style="margin-left: auto; margin-right: auto;">
                    Halaman ini sudah dibuat sebagai landing page awal. Setelah ini kita bisa lanjut satu per satu ke page Language Detail, Mode Detail, Lesson Detail, Quiz, Result, dan Review.
                </p>
                <div class="hero-actions" style="justify-content: center;">
                    <a href="#languages" class="button button-primary">Pilih Bahasa</a>
                    <a href="/admin" class="button button-ghost">Masuk Admin</a>
                </div>
            </div>
        </div>
    </section>
@endsection
