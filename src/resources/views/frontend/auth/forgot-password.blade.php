@extends('layouts.frontend')

@section('title', 'Forgot Password - ' . ($homepageSettings->site_name ?? 'YoLearning'))
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
                            Periksa kembali email yang kamu isi.
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.email.frontend') }}" class="auth-form">
                        @csrf

                        <div class="auth-field">
                            <label for="email" class="auth-label">{{ $authSetting->email_label ?? 'Email' }}</label>
                            <input
                                id="email"
                                name="email"
                                type="email"
                                value="{{ old('email') }}"
                                class="auth-input"
                                placeholder="nama@email.com"
                                required
                            >
                            @error('email')
                                <p class="auth-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" class="button button-primary auth-submit">
                            {{ $authSetting->submit_label ?? 'Cek Email' }}
                        </button>

                        <div class="auth-actions-row">
                            <a href="{{ route('login') }}" class="auth-link">← {{ $authSetting->login_link_label ?? 'Kembali ke login' }}</a>
                        </div>
                    </form>
                </div>

                <a href="{{ route('home') }}" class="auth-muted-link">← {{ $authSetting->back_home_label ?? 'Kembali ke homepage' }}</a>
            </div>
        </div>
    </section>
@endsection
