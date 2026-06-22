@extends('layouts.frontend')

@section('title', 'Login - ' . ($homepageSettings->site_name ?? 'YoLearning'))
@section('body_class', 'auth-page')
@section('hide_navbar', 'true')
@section('hide_footer', 'true')

@section('content')
    <section class="auth-section section-reveal">
        <div class="container auth-shell">
            <div class="auth-panel">
                <a href="{{ route('home') }}" class="auth-brand-minimal" aria-label="{{ $homepageSettings->brand_text ?? 'YoLearning' }} Home">
                    @if (! empty($homepageSettings->brand_logo_path))
                        <img src="{{ asset('storage/' . $homepageSettings->brand_logo_path) }}" alt="{{ $homepageSettings->brand_text ?? 'YoLearning' }}" class="brand-logo">
                    @else
                        <span class="brand-mark">{{ $homepageSettings->brand_initial ?? 'Y' }}</span>
                    @endif
                    <span>{{ $homepageSettings->brand_text ?? 'YoLearning' }}</span>
                </a>

                <div class="auth-card">
                    <div class="auth-header">
                        @if ($authSetting->kicker)
                            <p class="section-kicker">{{ $authSetting->kicker }}</p>
                        @endif

                        <h1 class="auth-title">{{ $authSetting->title }}</h1>

                        @if ($authSetting->description)
                            <p class="auth-description">{{ $authSetting->description }}</p>
                        @endif
                    </div>

                    @if (session('auth_success'))
                        <div class="auth-alert">{{ session('auth_success') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="auth-alert auth-alert-danger">
                            Periksa kembali data yang kamu isi.
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login.store') }}" class="auth-form">
                        @csrf

                        <div class="auth-field">
                            <label for="email" class="auth-label">{{ $authSetting->identifier_label ?? 'Email' }}</label>
                            <input
                                id="email"
                                name="email"
                                type="email"
                                value="{{ old('email') }}"
                                class="auth-input"
                                placeholder="nama@email.com"
                                autocomplete="email"
                                required
                            >
                            <p class="auth-hint">Login wajib memakai email supaya tidak tertukar dengan akun lain yang punya nama sama.</p>
                            @error('email')
                                <p class="auth-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="auth-field">
                            <label for="password" class="auth-label">{{ $authSetting->password_label ?? 'Password' }}</label>
                            <input
                                id="password"
                                name="password"
                                type="password"
                                class="auth-input"
                                placeholder="Password"
                                autocomplete="current-password"
                                required
                            >
                            @error('password')
                                <p class="auth-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="auth-field">
                            <label for="captcha_answer" class="auth-label">{{ $authSetting->captcha_label ?? 'Verifikasi' }}</label>
                            <div class="auth-input-group">
                                <input
                                    id="captcha_answer"
                                    name="captcha_answer"
                                    type="number"
                                    inputmode="numeric"
                                    class="auth-input"
                                    placeholder="Jawaban"
                                    required
                                >
                                <span class="captcha-chip">{{ $captchaQuestion }} = ?</span>
                            </div>
                            @error('captcha_answer')
                                <p class="auth-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" class="button button-primary auth-submit">
                            {{ $authSetting->submit_label ?? 'Masuk' }}
                        </button>

                        <div class="auth-actions-row is-between">
                            <a href="{{ route('password.request.frontend') }}" class="auth-link">
                                {{ $authSetting->forgot_password_label ?? 'Lupa password?' }}
                            </a>
                            <span>
                                {{ $authSetting->register_prompt ?? 'Belum punya akun?' }}
                                <a href="{{ route('register') }}" class="auth-link">{{ $authSetting->register_link_label ?? 'Daftar' }}</a>
                            </span>
                        </div>
                    </form>
                </div>

                <a href="{{ route('home') }}" class="auth-muted-link">← {{ $authSetting->back_home_label ?? 'Kembali ke homepage' }}</a>
            </div>
        </div>
    </section>
@endsection
