@extends('layouts.learning')

@section('title', 'Mulai Petualangan - YoLearning')

@section('content')
<div class="intro-stage" aria-hidden="true">
    <div class="intro-word one">{{ $setting->welcome_text }}</div>
    <div class="intro-word two">{{ $setting->adventure_text }}</div>
</div>

<main class="onboarding-page">
    <form method="POST" action="{{ route('learning.onboarding.store') }}" class="onboarding-card" id="onboardingForm">
        @csrf
        <input type="hidden" name="learning_language_id" id="languageInput" value="{{ old('learning_language_id') }}">
        <input type="hidden" name="ability_level" id="abilityInput" value="{{ old('ability_level') }}">

        <div class="onboarding-head">
            <span>YoLearning Setup</span>
            <h1>{{ $setting->language_title }}</h1>
            <p>Pilih satu bahasa dulu. Data pilihan ini tersimpan khusus untuk akun kamu.</p>
        </div>

        <div class="language-grid">
            @foreach ($languages as $language)
                <button type="button" class="language-option" style="--i: {{ $loop->index }}" data-language-id="{{ $language->id }}">
                    <span class="flag-chip">{{ $language->flag_label ?: strtoupper(substr($language->name, 0, 2)) }}</span>
                    <small>{{ $language->native_name ?: $language->name }}</small>
                    <b>{{ $language->name }}</b>
                    <p>{{ $language->description ?: 'Belajar bahasa dengan misi, level, dan latihan interaktif.' }}</p>
                </button>
            @endforeach
        </div>

        @error('learning_language_id')
            <div class="field-error">{{ $message }}</div>
        @enderror

        <section class="ability-step" id="abilityStep">
            <div class="onboarding-head" style="margin-bottom:1.2rem">
                <span>Placement</span>
                <h1 style="font-size:clamp(1.5rem,3.4vw,2.55rem)">{{ $setting->ability_title }}</h1>
            </div>

            <div class="ability-grid">
                @foreach ($abilityOptions as $key => $option)
                    <button type="button" class="ability-option" data-ability="{{ $key }}">
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

            <button type="submit" class="onboarding-submit" id="submitButton" disabled>Mulai Belajar</button>
        </section>
    </form>
</main>
@endsection

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

    if (languageInput.value) {
        document.querySelector(`[data-language-id="${languageInput.value}"]`)?.classList.add('is-selected');
        abilityStep.classList.add('is-ready');
    }

    if (abilityInput.value) {
        document.querySelector(`[data-ability="${abilityInput.value}"]`)?.classList.add('is-selected');
    }

    syncSubmitState();
</script>
@endpush
