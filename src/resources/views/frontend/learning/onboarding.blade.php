@extends('layouts.learning')

@section('title', 'Mulai Petualangan - YoLearning')

@section('content')
@php
    $availableLanguages = $isReturningUser ? $languages->reject(fn ($item) => in_array((int) $item->id, $selectedLanguageIds, true))->values() : $languages;
@endphp
<div class="intro-stage" aria-hidden="true">
    <div class="intro-word one">{{ $setting->welcome_text }}</div>
    <div class="intro-word two">{{ $setting->adventure_text }}</div>
</div>

<main class="onboarding-page">
    <form method="POST" action="{{ route('learning.onboarding.store') }}" class="onboarding-card" id="onboardingForm">
        @csrf
        <input type="hidden" name="learning_language_id" id="languageInput" value="{{ old('learning_language_id', $isReturningUser ? '' : $activeLanguageId) }}">
        <input type="hidden" name="ability_level" id="abilityInput" value="{{ old('ability_level', $isReturningUser ? '' : $profile?->ability_level) }}">

        <div class="onboarding-toolbar">
            <a href="{{ route('dashboard') }}" class="onboarding-back">← Dashboard</a>
            @if ($isReturningUser)
                <div class="onboarding-picked">
                    <span>Bahasa tersimpan:</span>
                    <div>
                        @foreach ($languages->whereIn('id', $selectedLanguageIds) as $pickedLanguage)
                            <em>{{ $pickedLanguage->name }}</em>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="onboarding-head">
            <span>YoLearning Setup</span>
            <h1>{{ $setting->language_title }}</h1>
            <p>{{ $isReturningUser ? 'Pilih bahasa baru yang ingin ditambahkan ke kursusmu. Untuk berpindah ke bahasa yang sudah ada, gunakan dropdown Kursusku di dashboard supaya tidak perlu memilih difficulty lagi.' : 'Pilih satu bahasa dulu. Data pilihan ini tersimpan khusus untuk akun kamu.' }}</p>
        </div>

        <div class="language-grid">
            @forelse ($availableLanguages as $language)
                <button
                    type="button"
                    class="language-option {{ (int) old('learning_language_id', $isReturningUser ? 0 : $activeLanguageId) === (int) $language->id ? 'is-selected' : '' }} {{ in_array((int) $language->id, $selectedLanguageIds, true) ? 'is-owned' : '' }}"
                    style="--i: {{ $loop->index }}"
                    data-language-id="{{ $language->id }}"
                >
                    <span class="flag-chip">{{ $language->flag_label ?: strtoupper(substr($language->name, 0, 2)) }}</span>
                    @if (in_array((int) $language->id, $selectedLanguageIds, true))
                        <span class="owned-chip">{{ (int) $activeLanguageId === (int) $language->id ? 'Aktif' : 'Tersimpan' }}</span>
                    @endif
                    <small>{{ $language->native_name ?: $language->name }}</small>
                    <b>{{ $language->name }}</b>
                    <p>{{ $language->description ?: 'Belajar bahasa dengan misi, level, dan latihan interaktif.' }}</p>
                </button>
            @empty
                <div class="no-language-left">
                    <h3>Semua bahasa sudah ada di kursusmu.</h3>
                    <p>Kembali ke dashboard lalu gunakan dropdown Kursusku untuk berpindah bahasa tanpa memilih difficulty lagi.</p>
                    <a href="{{ route('dashboard') }}">Kembali ke Dashboard</a>
                </div>
            @endforelse
        </div>

        @error('learning_language_id')
            <div class="field-error">{{ $message }}</div>
        @enderror

        <section class="ability-step {{ old('learning_language_id', $isReturningUser ? null : $activeLanguageId) ? 'is-ready' : '' }}" id="abilityStep">
            <div class="onboarding-head" style="margin-bottom:1.2rem">
                <span>Placement</span>
                <h1 style="font-size:clamp(1.5rem,3.4vw,2.55rem)">{{ $setting->ability_title }}</h1>
            </div>

            <div class="ability-grid">
                @foreach ($abilityOptions as $key => $option)
                    <button type="button" class="ability-option {{ old('ability_level', $isReturningUser ? null : $profile?->ability_level) === $key ? 'is-selected' : '' }}" data-ability="{{ $key }}">
                        <div>
                            <strong>{{ $option['label'] }}</strong>
                            <p>{{ $option['description'] }}</p>
                        </div>
                        <span>{{ $option['target'] }}</span>
                    </button>
                @endforeach
            </div>

            @error('ability_level')
                <div class="field-error">{{ $message }}</div>
            @enderror

            <button type="submit" class="onboarding-submit" id="submitButton" disabled>{{ $isReturningUser ? 'Simpan Bahasa Aktif' : 'Mulai Belajar' }}</button>
        </section>
    </form>
</main>
@endsection

@push('styles')
<style>
    .onboarding-toolbar { display:flex; align-items:center; justify-content:space-between; gap:1rem; margin-bottom:1rem; }
    .onboarding-back { display:inline-flex; align-items:center; padding:.8rem 1rem; border-radius:999px; border:1px solid var(--border); background:rgba(255,255,255,.05); font-weight:900; }
    .onboarding-picked { display:flex; align-items:center; gap:.8rem; flex-wrap:wrap; color:var(--muted); font-weight:800; }
    .onboarding-picked div { display:flex; gap:.5rem; flex-wrap:wrap; }
    .onboarding-picked em, .owned-chip { font-style:normal; display:inline-flex; align-items:center; justify-content:center; border-radius:999px; background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.06); color:var(--text); padding:.4rem .7rem; font-size:.74rem; font-weight:900; }
    .owned-chip { position:absolute; top:1rem; right:1rem; color:var(--cyan); background:rgba(102,232,247,.08); border-color:rgba(102,232,247,.16); }
    .language-option { position:relative; }
    .language-option.is-owned { border-color:rgba(255,255,255,.08); }
    .no-language-left {
        grid-column:1 / -1;
        border:1px solid var(--border);
        border-radius:24px;
        background:rgba(255,255,255,.045);
        padding:1.2rem;
        text-align:center;
    }
    .no-language-left h3 { font-size:1.35rem; letter-spacing:-.04em; }
    .no-language-left p { color:var(--muted); margin:.45rem auto 1rem; max-width:620px; line-height:1.6; font-weight:760; }
    .no-language-left a { display:inline-flex; border-radius:999px; background:linear-gradient(135deg,var(--cyan),var(--primary)); color:#07101f; padding:.8rem 1rem; font-weight:950; }
    @media (max-width:760px){ .onboarding-toolbar{align-items:flex-start; flex-direction:column;} }
</style>
@endpush

@push('scripts')
<script>
    const languageButtons = document.querySelectorAll('[data-language-id]');
    const abilityButtons = document.querySelectorAll('[data-ability]');
    const languageInput = document.getElementById('languageInput');
    const abilityInput = document.getElementById('abilityInput');
    const abilityStep = document.getElementById('abilityStep');
    const submitButton = document.getElementById('submitButton');

    function syncSubmitState() {
        submitButton.disabled = !(languageInput.value && abilityInput.value);
    }

    languageButtons.forEach((button) => {
        button.addEventListener('click', () => {
            languageButtons.forEach((item) => item.classList.remove('is-selected'));
            button.classList.add('is-selected');
            languageInput.value = button.dataset.languageId;
            abilityStep.classList.add('is-ready');
            setTimeout(() => abilityStep.scrollIntoView({ behavior: 'smooth', block: 'center' }), 120);
            syncSubmitState();
        });
    });

    abilityButtons.forEach((button) => {
        button.addEventListener('click', () => {
            abilityButtons.forEach((item) => item.classList.remove('is-selected'));
            button.classList.add('is-selected');
            abilityInput.value = button.dataset.ability;
            syncSubmitState();
        });
    });

    syncSubmitState();
</script>
@endpush
