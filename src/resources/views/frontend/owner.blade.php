@extends('layouts.frontend')

@section('title', 'Owner YoLearning - Portfolio')
@section('hide_navbar', true)
@section('hide_footer', true)

@push('styles')
    .owner-page {
        min-height: 100vh;
        padding: 1.25rem;
    }

    .owner-container {
        width: min(1160px, 100%);
        margin: 0 auto;
    }

    .owner-nav {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 0 1.4rem;
    }

    .owner-brand,
    .owner-back {
        display: inline-flex;
        align-items: center;
        gap: 0.7rem;
        border: 1px solid var(--line);
        background: rgba(255, 255, 255, 0.055);
        color: var(--ink);
        font-weight: 900;
        padding: 0.7rem 0.9rem;
        border-radius: 0.65rem;
    }

    .owner-brand-mark {
        width: 2.1rem;
        height: 2.1rem;
        display: grid;
        place-items: center;
        border-radius: 0.65rem;
        background: linear-gradient(145deg, var(--cyan), var(--indigo));
        color: #07101f;
        font-weight: 950;
    }

    .owner-hero {
        min-height: calc(100vh - 6rem);
        display: grid;
        grid-template-columns: minmax(0, 1.1fr) minmax(300px, 0.9fr);
        align-items: center;
        gap: clamp(2rem, 6vw, 5rem);
        padding: clamp(2rem, 8vw, 5.5rem) 0;
    }

    .owner-kicker,
    .owner-section-kicker {
        color: var(--cyan);
        font-size: 0.8rem;
        font-weight: 950;
        letter-spacing: 0.18em;
        text-transform: uppercase;
    }

    .owner-title {
        margin-top: 0.9rem;
        font-size: clamp(3.6rem, 10vw, 8rem);
        line-height: 0.86;
        letter-spacing: -0.08em;
    }

    .owner-title span {
        color: #9ee9ff;
    }

    .owner-role {
        margin-top: 1.3rem;
        color: var(--muted);
        font-size: clamp(1.15rem, 2.4vw, 1.75rem);
        font-weight: 850;
    }

    .owner-role strong {
        color: var(--ink);
    }

    .owner-copy {
        max-width: 680px;
        margin-top: 1.1rem;
        color: var(--muted);
        font-size: 1.03rem;
        font-weight: 650;
        line-height: 1.8;
    }

    .owner-actions {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.85rem;
        margin-top: 1.6rem;
    }

    .owner-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 3rem;
        padding: 0.85rem 1.1rem;
        border-radius: 0.65rem;
        border: 1px solid var(--line);
        background: rgba(255, 255, 255, 0.06);
        color: var(--ink);
        font-weight: 950;
    }

    .owner-button.primary {
        border: 0;
        background: linear-gradient(135deg, var(--cyan), var(--indigo));
        color: #07101f;
    }

    .owner-card {
        border: 1px solid var(--line);
        background: linear-gradient(145deg, rgba(18, 28, 45, 0.9), rgba(10, 15, 28, 0.82));
        box-shadow: 0 28px 80px rgba(0, 0, 0, 0.32);
        border-radius: 0.75rem;
        padding: 1.1rem;
    }

    .owner-profile-card {
        min-height: 420px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
        overflow: hidden;
    }

    .owner-profile-card::before {
        content: "";
        position: absolute;
        inset: -30% -20% auto;
        height: 18rem;
        background: radial-gradient(circle, rgba(102, 232, 247, 0.2), transparent 62%);
        pointer-events: none;
    }

    .owner-photo {
        position: relative;
        width: 100%;
        aspect-ratio: 1 / 0.78;
        display: grid;
        place-items: center;
        border: 1px solid var(--line);
        border-radius: 0.75rem;
        background:
            radial-gradient(circle at 30% 30%, rgba(102, 232, 247, 0.22), transparent 30%),
            linear-gradient(135deg, rgba(43, 66, 108, 0.7), rgba(36, 24, 80, 0.55));
        font-size: clamp(3rem, 9vw, 6rem);
        font-weight: 950;
        color: #07101f;
    }

    .owner-profile-meta {
        position: relative;
        margin-top: 1rem;
        display: flex;
        align-items: end;
        justify-content: space-between;
        gap: 1rem;
    }

    .owner-profile-meta h2 {
        font-size: clamp(1.6rem, 4vw, 2.5rem);
        letter-spacing: -0.06em;
    }

    .owner-profile-meta p {
        margin-top: 0.25rem;
        color: var(--muted);
        font-weight: 750;
    }

    .owner-status {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        white-space: nowrap;
        color: #baf8df;
        font-size: 0.82rem;
        font-weight: 900;
    }

    .owner-status::before {
        content: "";
        width: 0.55rem;
        height: 0.55rem;
        border-radius: 999px;
        background: #49d38b;
        box-shadow: 0 0 20px rgba(73, 211, 139, 0.62);
    }

    .owner-section {
        padding: clamp(3.5rem, 8vw, 6.5rem) 0;
        border-top: 1px solid var(--line);
    }

    .owner-section-head {
        max-width: 720px;
        margin-bottom: 1.6rem;
    }

    .owner-section-title {
        margin-top: 0.55rem;
        font-size: clamp(2.2rem, 6vw, 4.4rem);
        line-height: 0.95;
        letter-spacing: -0.07em;
    }

    .owner-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.9rem;
    }

    .owner-skill,
    .owner-project,
    .owner-contact {
        border: 1px solid var(--line);
        background: rgba(255, 255, 255, 0.045);
        border-radius: 0.75rem;
        padding: 1rem;
    }

    .owner-skill strong,
    .owner-project strong {
        display: block;
        color: var(--ink);
        font-size: 1.05rem;
    }

    .owner-skill span,
    .owner-project p,
    .owner-contact p {
        display: block;
        margin-top: 0.38rem;
        color: var(--muted);
        font-size: 0.92rem;
        font-weight: 650;
        line-height: 1.55;
    }

    .owner-project {
        min-height: 14rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .owner-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 0.42rem;
        margin-top: 1rem;
    }

    .owner-tags span {
        border: 1px solid rgba(119, 232, 247, 0.22);
        background: rgba(119, 232, 247, 0.08);
        color: #dffcff;
        border-radius: 0.55rem;
        padding: 0.35rem 0.55rem;
        font-size: 0.72rem;
        font-weight: 900;
    }

    .owner-about {
        display: grid;
        grid-template-columns: minmax(0, 0.9fr) minmax(0, 1.1fr);
        gap: 1rem;
        align-items: stretch;
    }

    .owner-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.7rem;
        margin-top: 1.2rem;
    }

    .owner-stat {
        border: 1px solid var(--line);
        border-radius: 0.75rem;
        padding: 0.9rem;
        background: rgba(255, 255, 255, 0.04);
    }

    .owner-stat b {
        display: block;
        font-size: 1.65rem;
        letter-spacing: -0.04em;
    }

    .owner-stat span {
        color: var(--muted);
        font-size: 0.78rem;
        font-weight: 800;
    }

    .owner-contact-grid {
        display: grid;
        grid-template-columns: minmax(0, 0.95fr) minmax(0, 1.05fr);
        gap: 1rem;
    }

    .owner-contact a {
        color: var(--cyan);
        font-weight: 950;
        word-break: break-word;
    }

    .owner-footer {
        padding: 2rem 0 1rem;
        color: var(--muted);
        font-weight: 750;
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        border-top: 1px solid var(--line);
    }

    @media (max-width: 900px) {
        .owner-hero,
        .owner-about,
        .owner-contact-grid {
            grid-template-columns: 1fr;
        }

        .owner-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 620px) {
        .owner-page {
            padding: 0.85rem;
        }

        .owner-nav,
        .owner-footer {
            align-items: stretch;
            flex-direction: column;
        }

        .owner-grid,
        .owner-stats {
            grid-template-columns: 1fr;
        }

        .owner-profile-meta {
            align-items: start;
            flex-direction: column;
        }
    }
@endpush

@section('content')
    @php
        $skills = [
            ['name' => 'Laravel', 'subtitle' => 'Backend, Blade, routing, auth, admin panel'],
            ['name' => 'UI/UX Design', 'subtitle' => 'Clean layout, responsive flow, user journey'],
            ['name' => 'Tailwind & CSS', 'subtitle' => 'Modern interface, spacing, typography'],
            ['name' => 'MySQL/MariaDB', 'subtitle' => 'Relasi data, progress, payment, leaderboard'],
            ['name' => 'Docker & VPS', 'subtitle' => 'Local environment, deployment, Nginx proxy'],
            ['name' => 'GitHub', 'subtitle' => 'Version control, branch flow, production update'],
        ];

        $projects = [
            ['title' => 'YoLearning', 'description' => 'Platform belajar bahasa asing dengan level, audio, reading story, premium, iklan, duel 1v1, turnamen cepat, dan Quiz Room.', 'tags' => ['Laravel', 'Blade', 'MariaDB']],
            ['title' => 'Portfolio Website', 'description' => 'Website personal untuk menampilkan profil, skill, proyek, dan kontak dengan tampilan modern.', 'tags' => ['PHP', 'CSS', 'Laravel']],
            ['title' => 'Competitive Learning Mode', 'description' => 'Mode belajar kompetitif yang memakai skor, timer, matchmaking, leaderboard, dan history permainan.', 'tags' => ['Realtime Flow', 'Quiz', 'Scoring']],
        ];
    @endphp

    <div class="owner-page">
        <div class="owner-container">
            <nav class="owner-nav" aria-label="Navigasi portfolio owner">
                <a href="{{ route('home') }}" class="owner-brand">
                    <span class="owner-brand-mark">Y</span>
                    <span>YoLearning Owner</span>
                </a>

                <a href="{{ route('home') }}" class="owner-back">Kembali ke YoLearning</a>
            </nav>

            <section class="owner-hero" id="beranda">
                <div>
                    <p class="owner-kicker">Halo, saya</p>
                    <h1 class="owner-title">Firaas<br><span>Ferdinal</span></h1>
                    <p class="owner-role">Seorang <strong>Web Developer & UI/UX Designer</strong></p>
                    <p class="owner-copy">
                        Saya membangun YoLearning sebagai platform belajar bahasa yang lebih interaktif, terstruktur, dan kompetitif. Fokus saya adalah membuat fitur berjalan stabil sekaligus menjaga tampilan tetap modern, mudah dipahami, dan nyaman digunakan.
                    </p>

                    <div class="owner-actions">
                        <a href="#proyek" class="owner-button primary">Lihat Project</a>
                        <a href="#kontak" class="owner-button">Hubungi Owner</a>
                    </div>
                </div>

                <aside class="owner-card owner-profile-card">
                    <div class="owner-photo">FF</div>
                    <div class="owner-profile-meta">
                        <div>
                            <h2>Firaas Ferdinal</h2>
                            <p>@YouxAIBot</p>
                        </div>
                        <span class="owner-status">Online</span>
                    </div>
                </aside>
            </section>

            <section class="owner-section" id="tentang">
                <div class="owner-about">
                    <div class="owner-card">
                        <p class="owner-section-kicker">Tentang Owner</p>
                        <h2 class="owner-section-title">Kode, desain, dan pengalaman belajar.</h2>
                    </div>

                    <div class="owner-card">
                        <p class="owner-copy" style="margin-top:0;">
                            Saya menggabungkan logika backend, desain antarmuka, dan pengalaman pengguna untuk membangun aplikasi web yang tidak hanya bisa dipakai, tetapi juga nyaman dipelajari. Di YoLearning, fokus utamanya adalah progress belajar, latihan audio, soal bertahap, premium, iklan, dan fitur kompetitif.
                        </p>

                        <div class="owner-stats">
                            <div class="owner-stat">
                                <b>3+</b>
                                <span>Tahun belajar web</span>
                            </div>
                            <div class="owner-stat">
                                <b>20+</b>
                                <span>Fitur dikembangkan</span>
                            </div>
                            <div class="owner-stat">
                                <b>1</b>
                                <span>Produk utama: YoLearning</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="owner-section" id="skill">
                <div class="owner-section-head">
                    <p class="owner-section-kicker">Skill</p>
                    <h2 class="owner-section-title">Toolstack yang dipakai.</h2>
                </div>

                <div class="owner-grid">
                    @foreach ($skills as $skill)
                        <div class="owner-skill">
                            <strong>{{ $skill['name'] }}</strong>
                            <span>{{ $skill['subtitle'] }}</span>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="owner-section" id="proyek">
                <div class="owner-section-head">
                    <p class="owner-section-kicker">Project</p>
                    <h2 class="owner-section-title">Project yang ditampilkan.</h2>
                </div>

                <div class="owner-grid">
                    @foreach ($projects as $project)
                        <article class="owner-project">
                            <div>
                                <strong>{{ $project['title'] }}</strong>
                                <p>{{ $project['description'] }}</p>
                            </div>

                            <div class="owner-tags">
                                @foreach ($project['tags'] as $tag)
                                    <span>{{ $tag }}</span>
                                @endforeach
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="owner-section" id="kontak">
                <div class="owner-contact-grid">
                    <div class="owner-card">
                        <p class="owner-section-kicker">Kontak</p>
                        <h2 class="owner-section-title">Mari terhubung.</h2>
                        <p class="owner-copy">
                            Halaman ini dibuat sebagai jembatan dari YoLearning ke portofolio owner. Pengunjung bisa melihat profil pembuat dan menghubungi owner jika ingin berdiskusi tentang project.
                        </p>
                    </div>

                    <div class="owner-contact">
                        <strong>Email</strong>
                        <p><a href="mailto:yoiyouka@gmail.com">yoiyouka@gmail.com</a></p>

                        <br>

                        <strong>WhatsApp</strong>
                        <p><a href="https://wa.me/6281311965417" target="_blank" rel="noopener">+62 813 1196 5417</a></p>

                        <br>

                        <strong>GitHub</strong>
                        <p><a href="https://github.com/YouxAIBot" target="_blank" rel="noopener">@YouxAIBot</a></p>
                    </div>
                </div>
            </section>

            <footer class="owner-footer">
                <span>(c) {{ date('Y') }} Firaas Ferdinal. Portfolio owner YoLearning.</span>
                <a href="#beranda">Kembali ke atas</a>
            </footer>
        </div>
    </div>
@endsection
