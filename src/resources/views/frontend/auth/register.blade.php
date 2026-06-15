@extends('layouts.frontend')

@section('title', 'Register - ' . ($homepageSettings->site_name ?? 'YoLearning'))
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

                    @if ($errors->any())
                        <div class="auth-alert auth-alert-danger">
                            Periksa kembali data yang kamu isi.
                        </div>
                    @endif

                    <form method="POST" action="{{ route('register.store') }}" class="auth-form">
                        @csrf

                        <div class="auth-field">
                            <label for="name" class="auth-label">{{ $authSetting->name_label ?? 'Nama Lengkap' }}</label>
                            <input
                                id="name"
                                name="name"
                                type="text"
                                value="{{ old('name') }}"
                                class="auth-input"
                                placeholder="Masukkan nama"
                                autocomplete="name"
                                required
                            >
                            @error('name')
                                <p class="auth-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="auth-field">
                            <label for="email" class="auth-label">{{ $authSetting->email_label ?? 'Email' }}</label>
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
                                placeholder="Minimal 8 karakter"
                                autocomplete="new-password"
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
                            {{ $authSetting->submit_label ?? 'Daftar' }}
                        </button>

                        <div class="auth-actions-row">
                            <span>
                                {{ $authSetting->login_prompt ?? 'Sudah punya akun?' }}
                                <a href="{{ route('login') }}" class="auth-link">{{ $authSetting->login_link_label ?? 'Login' }}</a>
                            </span>
                        </div>
                    </form>
                </div>

                <a href="{{ route('home') }}" class="auth-muted-link">← {{ $authSetting->back_home_label ?? 'Kembali ke homepage' }}</a>
            </div>
        </div>
    </section>
@endsection
