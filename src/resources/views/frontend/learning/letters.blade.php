@extends('layouts.learning')

@section('title', 'Huruf - YoLearning')

@section('content')
<div class="app-frame">
    @include('frontend.learning.partials.sidebar')

    <main class="main-panel">
        <div class="main-topbar">
            <div>
                <h1>Huruf {{ $profile->language?->name ?? 'Bahasa' }}</h1>
                <p>Klik huruf untuk mendengar pelafalan dan melihat contoh penggunaan.</p>
            </div>
            <a href="{{ route('dashboard') }}" class="logout-link">Dashboard</a>
        </div>

        <div class="content-area">
            <section class="letters-hero">
                <small>Latihan Huruf</small>
                <h2>{{ $profile->language?->native_name ?: $profile->language?->name }}</h2>
                <p>Gunakan halaman ini untuk mengenali huruf, cara baca, contoh kata, dan suara dari bahasa aktifmu.</p>
            </section>

            <section class="letters-grid" data-letters-grid data-speech-lang="{{ data_get($profile->language?->settings, 'speech_lang', 'en-US') }}">
                @foreach ($letters as $letter)
                    @php
                        $audioUrl = method_exists($letter, 'publicAudioUrl') ? $letter->publicAudioUrl() : ($letter->audio_url ?? null);
                    @endphp
                    <button
                        type="button"
                        class="letter-card"
                        data-letter-card
                        data-symbol="{{ $letter->symbol }}"
                        data-reading="{{ $letter->reading }}"
                        data-audio-url="{{ $audioUrl }}"
                    >
                        <span>{{ $letter->symbol }}</span>
                        <b>{{ $letter->reading ?: 'Klik dengar' }}</b>
                        @if ($letter->example_word)
                            <em>{{ $letter->example_word }}</em>
                        @endif
                        @if ($letter->example_translation)
                            <small>{{ $letter->example_translation }}</small>
                        @endif
                    </button>
                @endforeach
            </section>
        </div>
    </main>

    @include('frontend.learning.partials.right-panel')
</div>
@endsection

@push('styles')
<style>
    .letters-hero {
        border: 1px solid var(--border);
        border-radius: 26px;
        background: linear-gradient(145deg, rgba(29, 36, 52, .92), rgba(10, 16, 29, .86));
        padding: clamp(1rem, 3vw, 1.6rem);
        box-shadow: var(--shadow);
    }

    .letters-hero small {
        color: var(--cyan);
        font-weight: 950;
        letter-spacing: .13em;
        text-transform: uppercase;
    }

    .letters-hero h2 {
        margin: .35rem 0;
        font-size: clamp(2rem, 5vw, 4rem);
        letter-spacing: -.07em;
    }

    .letters-hero p {
        color: var(--muted);
        max-width: 680px;
        font-weight: 760;
        line-height: 1.6;
    }

    .letters-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(132px, 1fr));
        gap: .85rem;
        margin-top: 1rem;
    }

    .letter-card {
        min-height: 148px;
        display: grid;
        align-content: center;
        justify-items: center;
        gap: .28rem;
        border: 1px solid rgba(255, 255, 255, .1);
        border-radius: 22px;
        background: rgba(255, 255, 255, .055);
        color: var(--text);
        padding: 1rem;
        cursor: pointer;
        transition: transform .18s ease, border-color .18s ease, background .18s ease;
    }

    .letter-card:hover,
    .letter-card.is-playing {
        transform: translateY(-3px);
        border-color: rgba(102, 232, 247, .45);
        background: rgba(102, 232, 247, .09);
    }

    .letter-card span {
        font-size: clamp(2.4rem, 8vw, 4rem);
        font-weight: 950;
        line-height: 1;
    }

    .letter-card b {
        color: var(--cyan);
        font-size: .92rem;
    }

    .letter-card em,
    .letter-card small {
        color: var(--muted);
        font-style: normal;
        font-weight: 800;
        text-align: center;
    }
</style>
@endpush

@push('scripts')
<script>
(() => {
    const grid = document.querySelector('[data-letters-grid]');

    if (!grid) {
        return;
    }

    const speechLang = grid.dataset.speechLang || 'en-US';
    let activeAudio = null;

    const speak = (text) => {
        if (!('speechSynthesis' in window) || !text) {
            return;
        }

        window.speechSynthesis.cancel();
        const utterance = new SpeechSynthesisUtterance(text);
        utterance.lang = speechLang;
        utterance.rate = 0.82;
        window.speechSynthesis.speak(utterance);
    };

    grid.querySelectorAll('[data-letter-card]').forEach((card) => {
        card.addEventListener('click', () => {
            grid.querySelectorAll('.is-playing').forEach((item) => item.classList.remove('is-playing'));
            card.classList.add('is-playing');

            if (activeAudio) {
                activeAudio.pause();
                activeAudio = null;
            }

            const audioUrl = card.dataset.audioUrl;
            const text = card.dataset.reading || card.dataset.symbol;

            if (audioUrl) {
                activeAudio = new Audio(audioUrl);
                activeAudio.addEventListener('ended', () => card.classList.remove('is-playing'), { once: true });
                activeAudio.play().catch(() => speak(text));
                return;
            }

            speak(text);
            window.setTimeout(() => card.classList.remove('is-playing'), 700);
        });
    });
})();
</script>
@endpush
