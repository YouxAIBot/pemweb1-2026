@extends('layouts.frontend')

@section('title', $homepageSettings->meta_title ?? 'YoLearning - Belajar Bahasa Interaktif')

@section('content')
    @php
        $hero = $sections->get('hero');
        $languagesSection = $sections->get('languages');
        $tournament = $sections->get('tournament');
        $cta = $sections->get('cta');

        $renderTitle = fn (?string $title, string $fallback = '') => str_replace('YoLearning', '<span class="text-gradient">YoLearning</span>', e($title ?: $fallback));
        $storageUrl = fn (?string $path) => $path ? asset('storage/' . $path) : null;
    @endphp

    @if (session('auth_success'))
        <div class="container" style="padding-top: 1rem;">
            <div class="auth-alert">{{ session('auth_success') }}</div>
        </div>
    @endif

    @if ($hero)
        <section class="section hero-section section-reveal" id="home">
            <div class="container hero-grid">
                <div>
                    @if ($hero->kicker)
                        <span class="eyebrow">
                            <span class="pulse-dot"></span>
                            {{ $hero->kicker }}
                        </span>
                    @endif

                    <h1 class="hero-title">
                        {!! $renderTitle($hero->title, 'Belajar Bahasa Bersama YoLearning') !!}
                    </h1>

                    @if ($hero->description)
                        <p class="hero-copy">
                            {{ $hero->description }}
                        </p>
                    @endif

                    @if ($hero->primary_button_label || $hero->secondary_button_label)
                        <div class="hero-actions">
                            @if ($hero->primary_button_label && $hero->primary_button_url)
                                <a href="{{ $hero->primary_button_url }}" class="button button-primary">{{ $hero->primary_button_label }}</a>
                            @endif

                            @if ($hero->secondary_button_label && $hero->secondary_button_url)
                                <a href="{{ $hero->secondary_button_url }}" class="button button-soft">{{ $hero->secondary_button_label }}</a>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="hero-card" aria-label="Preview dashboard YoLearning">
                    @if ($storageUrl($hero->image_path))
                        <img src="{{ $storageUrl($hero->image_path) }}" alt="{{ $hero->name }}" class="hero-custom-image">
                    @else
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
                                <p>Progress belajar tersimpan otomatis: skor, XP, riwayat level, dan pembahasan siap kamu cek setelah latihan.</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    @endif

    @if ($languagesSection)
        <section class="section split-section section-reveal" id="languages">
            <div class="container split-grid">
                <div class="language-copy">
                    @if ($languagesSection->kicker)
                        <p class="section-kicker">{{ $languagesSection->kicker }}</p>
                    @endif

                    <h2 class="section-title">{{ $languagesSection->title }}</h2>

                    @if ($languagesSection->description)
                        <p class="section-copy">
                            {{ $languagesSection->description }}
                        </p>
                    @endif
                </div>

                <div class="language-cloud" aria-label="Daftar bahasa">
                    @forelse ($languagesSection->items as $language)
                        <a href="{{ $language->url ?: '#' }}" class="language-card">
                            @if ($storageUrl($language->image_path))
                                <img src="{{ $storageUrl($language->image_path) }}" alt="{{ $language->title }}" class="language-card-image">
                            @endif

                            @if ($language->accent_text)
                                <div class="language-accent">{{ $language->accent_text }}</div>
                            @endif

                            <div class="language-name">{{ $language->title }}</div>

                            @if ($language->description)
                                <p class="language-desc">{{ $language->description }}</p>
                            @endif

                            @if ($language->badge_text)
                                <span class="language-status">{{ $language->badge_text }}</span>
                            @endif
                        </a>
                    @empty
                        <div class="language-card" style="position: relative; inset: auto; transform: none; opacity: 1;">
                            <div class="language-name">Belum ada bahasa</div>
                            <p class="language-desc">Bahasa belum tersedia. Silakan kembali nanti atau pilih bahasa lain yang sudah aktif.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
    @endif

    @if ($tournament)
        <section class="section tournament-section section-reveal" id="tournament">
            <div class="container tournament-grid">
                <div class="arena-card" aria-label="Gambar turnamen">
                    @if ($storageUrl($tournament->image_path))
                        <img src="{{ $storageUrl($tournament->image_path) }}" alt="{{ $tournament->name }}" class="section-image">
                    @else
                        <div class="tournament-visual">
                            <div class="podium-card">
                                <div class="podium-top">
                                    <span>Battle Room</span>
                                    <span class="podium-icon">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M8 21h8M12 17v4M7 4h10v3.5a5 5 0 0 1-10 0V4Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M7 6H4.8a1.8 1.8 0 0 0 0 3.6H7M17 6h2.2a1.8 1.8 0 0 1 0 3.6H17" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        </svg>
                                    </span>
                                </div>

                                <div class="podium-bars" aria-hidden="true">
                                    <div class="podium-bar" style="--h: 4.9rem;">2</div>
                                    <div class="podium-bar" style="--h: 7.4rem;">1</div>
                                    <div class="podium-bar" style="--h: 3.8rem;">3</div>
                                </div>

                                <p class="podium-caption">Tournament Preview</p>
                            </div>
                        </div>
                    @endif
                </div>

                <div>
                    @if ($tournament->kicker)
                        <p class="section-kicker">{{ $tournament->kicker }}</p>
                    @endif

                    <h2 class="section-title">{{ $tournament->title }}</h2>

                    @if ($tournament->description)
                        <p class="section-copy">
                            {{ $tournament->description }}
                        </p>
                    @endif

                    <div class="feature-list">
                        @foreach ($tournament->items as $feature)
                            <div class="feature-item">
                                <span class="feature-icon">{{ $feature->label ?: $loop->iteration }}</span>
                                <div>
                                    <strong>{{ $feature->title }}</strong>
                                    @if ($feature->description)
                                        <p>{{ $feature->description }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if ($cta)
        <section class="section cta-section section-reveal" id="start">
            <div class="container">
                <div class="cta-card">
                    @if ($storageUrl($cta->image_path))
                        <img src="{{ $storageUrl($cta->image_path) }}" alt="{{ $cta->name }}" class="cta-background-image" aria-hidden="true">
                    @endif

                    @if ($cta->kicker)
                        <p class="section-kicker">{{ $cta->kicker }}</p>
                    @endif

                    <h2 class="section-title">{{ $cta->title }}</h2>

                    @if ($cta->description)
                        <p class="section-copy" style="margin-left: auto; margin-right: auto;">
                            {{ $cta->description }}
                        </p>
                    @endif

                    @if ($cta->primary_button_label || $cta->secondary_button_label)
                        <div class="hero-actions" style="justify-content: center;">
                            @if ($cta->primary_button_label && $cta->primary_button_url)
                                <a href="{{ $cta->primary_button_url }}" class="button button-primary">{{ $cta->primary_button_label }}</a>
                            @endif

                            @if ($cta->secondary_button_label && $cta->secondary_button_url)
                                <a href="{{ $cta->secondary_button_url }}" class="button button-ghost">{{ $cta->secondary_button_label }}</a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </section>
    @endif

    <section class="section cta-section section-reveal" id="owner">
        <div class="container">
            <div class="cta-card">
                <p class="section-kicker">Owner Portfolio</p>
                <h2 class="section-title">Kenali pembuat YoLearning.</h2>
                <p class="section-copy" style="margin-left: auto; margin-right: auto;">
                    Lihat profil, skill, dan project dari owner yang mengembangkan YoLearning sebagai platform belajar bahasa interaktif berbasis level, audio, progress, premium, dan mode kompetitif.
                </p>

                <div class="hero-actions" style="justify-content: center;">
                    <a href="{{ route('owner') }}" class="button button-primary">Buka Portofolio Owner</a>
                    <a href="#home" class="button button-ghost">Kembali ke Atas</a>
                </div>
            </div>
        </div>
    </section>
@endsection
