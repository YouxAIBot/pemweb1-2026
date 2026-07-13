@extends('layouts.learning')

@section('title', $level->title . ' - YoLearning')

@php
    function learningAudioUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $path = ltrim($path, '/');

        if (str_starts_with($path, 'storage/')) {
            return asset($path);
        }

        return asset('storage/' . $path);
    }

    $quizQuestions = $level->questions->map(function ($question) {
        $settings = $question->settings ?? [];

        $storySegments = collect($settings['story_segments'] ?? [])->map(function ($segment) {
            $audioPath = $segment['audio_manual_path'] ?? ($segment['audio_path'] ?? null);

            return [
                'speaker' => $segment['speaker'] ?? null,
                'side' => $segment['side'] ?? null,
                'text' => $segment['text'] ?? '',
                'audioUrl' => learningAudioUrl($audioPath),
            ];
        })->values();

        $storyFlow = collect($settings['story_flow'] ?? [])->map(function ($item, $itemIndex) {
            $type = $item['type'] ?? ($item['item_type'] ?? null);
            $data = $item['data'] ?? $item;

            if ($type === 'question') {
                $options = collect($data['options'] ?? [])->map(function ($option, $optionIndex) {
                    return [
                        'id' => $optionIndex + 1,
                        'text' => $option['text'] ?? '',
                        'isCorrect' => (bool) ($option['is_correct'] ?? false),
                    ];
                })->filter(fn ($option) => filled($option['text']))->values();

                return [
                    'id' => $itemIndex + 1,
                    'type' => 'question',
                    'questionText' => $data['question_text'] ?? '',
                    'options' => $options,
                    'explanation' => $data['explanation'] ?? '',
                ];
            }

            $audioPath = $data['audio_manual_path'] ?? ($data['audio_path'] ?? null);

            return [
                'id' => $itemIndex + 1,
                'type' => 'dialogue',
                'speaker' => $data['speaker'] ?? ('Tokoh ' . ($itemIndex + 1)),
                'side' => in_array(($data['side'] ?? 'left'), ['left', 'right'], true) ? $data['side'] : 'left',
                'text' => $data['text'] ?? '',
                'audioUrl' => learningAudioUrl($audioPath),
            ];
        })->filter(function ($item) {
            if (($item['type'] ?? null) === 'question') {
                return filled($item['questionText'] ?? null);
            }

            return filled($item['text'] ?? null);
        })->values();

        $storyQuestions = collect($settings['story_questions'] ?? [])->map(function ($storyQuestion, $storyQuestionIndex) {
            $options = collect($storyQuestion['options'] ?? [])->map(function ($option, $optionIndex) {
                return [
                    'id' => $optionIndex + 1,
                    'text' => $option['text'] ?? '',
                    'isCorrect' => (bool) ($option['is_correct'] ?? false),
                ];
            })->filter(fn ($option) => filled($option['text']))->values();

            return [
                'id' => $storyQuestionIndex + 1,
                'questionText' => $storyQuestion['question_text'] ?? '',
                'options' => $options,
                'explanation' => $storyQuestion['explanation'] ?? '',
            ];
        })->filter(fn ($storyQuestion) => filled($storyQuestion['questionText']))->values();

        $listeningFlow = collect($settings['listening_flow'] ?? [])->map(function ($item, $itemIndex) {
            $type = $item['type'] ?? ($item['item_type'] ?? 'story');
            $data = $item['data'] ?? $item;

            if ($type === 'question') {
                $questionAudioPath = $data['question_audio_manual_path'] ?? ($data['question_audio_path'] ?? null);
                $options = collect($data['options'] ?? [])->map(function ($option, $optionIndex) {
                    $optionAudioPath = $option['audio_manual_path'] ?? ($option['audio_path'] ?? null);

                    return [
                        'id' => $optionIndex + 1,
                        'text' => $option['text'] ?? '',
                        'audioUrl' => learningAudioUrl($optionAudioPath),
                        'isCorrect' => (bool) ($option['is_correct'] ?? false),
                    ];
                })->values();

                return [
                    'id' => $itemIndex + 1,
                    'type' => 'question',
                    'questionText' => $data['question_text'] ?? '',
                    'questionAudioUrl' => learningAudioUrl($questionAudioPath),
                    'explanation' => $data['explanation'] ?? '',
                    'options' => $options,
                ];
            }

            $storyAudioPath = $data['story_audio_manual_path'] ?? ($data['story_audio_path'] ?? null);

            return [
                'id' => $itemIndex + 1,
                'type' => 'story',
                'storyText' => $data['story_text'] ?? '',
                'storyAudioUrl' => learningAudioUrl($storyAudioPath),
            ];
        })->values();

        if ($listeningFlow->isEmpty() && ! empty($settings['listening_steps'])) {
            $listeningFlow = collect($settings['listening_steps'])->flatMap(function ($step, $stepIndex) {
                $storyAudioPath = $step['story_audio_path'] ?? null;
                $questionAudioPath = $step['question_audio_path'] ?? null;
                $options = collect($step['options'] ?? [])->map(function ($option, $optionIndex) {
                    $optionAudioPath = $option['audio_manual_path'] ?? ($option['audio_path'] ?? null);

                    return [
                        'id' => $optionIndex + 1,
                        'text' => $option['text'] ?? '',
                        'audioUrl' => learningAudioUrl($optionAudioPath),
                        'isCorrect' => (bool) ($option['is_correct'] ?? false),
                    ];
                })->values();

                return [
                    [
                        'id' => ($stepIndex * 2) + 1,
                        'type' => 'story',
                        'storyText' => $step['story_text'] ?? '',
                        'storyAudioUrl' => learningAudioUrl($storyAudioPath),
                    ],
                    [
                        'id' => ($stepIndex * 2) + 2,
                        'type' => 'question',
                        'questionText' => $step['question_text'] ?? '',
                        'questionAudioUrl' => learningAudioUrl($questionAudioPath),
                        'explanation' => $step['explanation'] ?? '',
                        'options' => $options,
                    ],
                ];
            })->values();
        }

        $wordPairs = collect($settings['word_pairs'] ?? [])->map(function ($pair, $index) {
            $audioPath = $pair['audio_path'] ?? null;

            return [
                'id' => $index + 1,
                'left' => $pair['left'] ?? '',
                'right' => $pair['right'] ?? '',
                'audioUrl' => learningAudioUrl($audioPath),
            ];
        })->values();

        $sentenceTokens = collect($settings['sentence_tokens'] ?? [])->map(function ($token, $index) {
            $audioPath = is_array($token)
                ? ($token['audio_manual_path'] ?? ($token['audio_path'] ?? null))
                : null;

            return [
                'id' => $index + 1,
                'text' => is_array($token) ? ($token['text'] ?? '') : $token,
                'audioUrl' => learningAudioUrl($audioPath),
            ];
        })->filter(fn ($token) => filled($token['text']))->values();

        return [
            'id' => $question->id,
            'type' => $question->type,
            'typeLabel' => \App\Models\LearningLevel::TYPES[$question->type] ?? str($question->type)->headline()->toString(),
            'instruction' => $question->instruction ?: 'Jawab pertanyaan berikut.',
            'questionText' => $question->question_text,
            'audioUrl' => learningAudioUrl($question->audio_path),
            'imageUrl' => $question->image_path ? asset('storage/' . $question->image_path) : null,
            'correctAnswer' => $question->correct_answer,
            'points' => (int) $question->points,
            'timeLimit' => $question->time_limit,
            'explanation' => $question->explanation,
            'settings' => [
                'audioTranscript' => $settings['audio_transcript'] ?? null,
                'questionAudioUrl' => learningAudioUrl($settings['question_audio_manual_path'] ?? ($settings['question_audio_path'] ?? null)),
                'scenarioContext' => $settings['scenario_context'] ?? null,
                'idealResponse' => $settings['ideal_response'] ?? null,
                'mixNote' => $settings['mix_note'] ?? null,
                'storyButtonLabel' => $settings['story_button_label'] ?? 'Mulai',
                'learningPhrase' => $settings['learning_phrase_text'] ?? null,
                'learningPhraseTranslation' => $settings['learning_phrase_translation'] ?? null,
                'learningPhraseAudioUrl' => learningAudioUrl($settings['learning_phrase_audio_manual_path'] ?? ($settings['learning_phrase_audio_path'] ?? null)) ?: learningAudioUrl($question->audio_path),
                'correctSentence' => $settings['correct_sentence'] ?? null,
                'storyFlow' => $storyFlow,
                'storySegments' => $storySegments,
                'storyQuestions' => $storyQuestions,
                'listeningFlow' => $listeningFlow,
                'wordPairs' => $wordPairs,
                'sentenceTokens' => $sentenceTokens,
            ],
            'options' => $question->options->map(function ($option) {
                return [
                    'id' => $option->id,
                    'text' => $option->option_text,
                    'audioUrl' => learningAudioUrl($option->audio_path),
                    'imageUrl' => $option->image_path ? asset('storage/' . $option->image_path) : null,
                    'isCorrect' => (bool) $option->is_correct,
                ];
            })->values(),
        ];
    })->values();

    $adPayload = function (?App\Models\Ad $ad, string $placement) {
        $defaultTitle = $placement === 'level_entry' ? 'Iklan sebelum level' : 'Iklan setelah level';
        $defaultDescription = $placement === 'level_entry'
            ? 'Tonton iklan singkat ini untuk membuka sesi latihan.'
            : 'Tonton iklan singkat ini sebelum progress kamu disimpan.';

        return [
            'id' => $ad?->id,
            'title' => $ad?->title ?? $defaultTitle,
            'description' => $ad?->description ?? $defaultDescription,
            'placement' => $placement,
            'videoUrl' => $ad?->publicVideoUrl(),
            'targetUrl' => $ad?->target_url,
            'duration' => max((int) ($ad?->duration_seconds ?? 15), 15),
        ];
    };

    $entryAdPayload = ($shouldShowAds ?? false) ? $adPayload($entryAd ?? null, 'level_entry') : null;
    $exitAdPayload = ($shouldShowAds ?? false) ? $adPayload($exitAd ?? null, 'level_exit') : null;
@endphp

@section('content')
<div class="app-frame quiz-focus-mode">
    <main class="main-panel quiz-main-panel">
        <div class="main-topbar quiz-topbar">
            <div>
                <h1>{{ $level->title }}</h1>
                <p>{{ $part->title }} • {{ $level->typeLabel() }} • {{ $level->xp_reward }} XP</p>
            </div>
            <a href="{{ route('learning.parts.show', $part) }}" class="logout-link">Peta Level</a>
        </div>

        <div class="content-area quiz-content-area">
            <section class="quiz-brief">
                <small>{{ $level->typeLabel() }}</small>
                <h2>{{ $level->title }}</h2>
                <p>{{ $level->description ?: 'Kerjakan soal satu per satu. Jika jawaban salah, coba lagi sampai benar, lalu lanjut ke soal berikutnya.' }}</p>
            </section>

            @if ($quizQuestions->isEmpty())
                <article class="question-card empty-question-state">
                    <small>Belum ada soal</small>
                    <h3>Konten level belum dibuat</h3>
                    <p>Admin bisa menambahkan soal lewat LEARNING CMS → Questions. Setiap jenis soal akan punya tampilan tersendiri.</p>
                </article>
            @else
                <section
                    class="quiz-engine"
                    id="quizEngine"
                    data-questions='@json($quizQuestions)'
                    data-completed="{{ ($levelProgress?->status ?? 'available') === 'completed' ? 'true' : 'false' }}"
                    data-show-ads="{{ ($shouldShowAds ?? false) ? 'true' : 'false' }}"
                    data-entry-ad='@json($entryAdPayload)'
                    data-exit-ad='@json($exitAdPayload)'
                    data-level-id="{{ $level->id }}"
                >
                    <div class="quiz-top-row">
                        <div>
                            <span class="quiz-eyebrow">Soal <b data-current-number>1</b> / <b data-total-number>{{ $quizQuestions->count() }}</b></span>
                            <h3 data-question-title>Memuat soal...</h3>
                        </div>

                        <div class="quiz-top-meta">
                            <div class="quiz-life-pill" data-life-pill>
                                <span>Nyawa</span>
                                <div class="quiz-life-dots" data-life-dots aria-label="Sisa nyawa"></div>
                            </div>

                            <div class="quiz-score-pill">
                                <span data-correct-count>0</span> selesai
                            </div>
                        </div>
                    </div>

                    <div class="quiz-progress-track">
                        <span data-progress-bar style="width:0%"></span>
                    </div>

                    <article class="quiz-card" data-question-card>
                        <div class="quiz-type-badge" data-question-type></div>
                        <div class="quiz-body" data-question-body></div>
                        <div class="quiz-feedback" data-feedback></div>
                    </article>

                    <form method="POST" action="{{ route('learning.levels.complete', [$part, $level]) }}" class="quiz-complete-form" data-complete-form>
                        @csrf
                        <input type="hidden" name="study_seconds" value="0" data-study-seconds>
                        <input type="hidden" name="correct_count" value="0" data-correct-input>
                        <input type="hidden" name="total_questions" value="{{ $quizQuestions->count() }}" data-total-input>
                        <input type="hidden" name="question_results" value="[]" data-question-results>

                        <div class="quiz-finish-panel" data-finish-panel hidden>
                            <small>Level selesai</small>
                            <h3>Progress kamu siap disimpan</h3>
                            <p>
                                Klik tombol di bawah untuk menyimpan skor, menghitung durasi belajar berdasarkan waktu kamu berada di level ini, menambah misi harian, dan membuka level berikutnya.
                            </p>
                            <button type="submit" class="complete-level-button">
                                Simpan Progress & Buka Level Berikutnya
                            </button>
                        </div>
                    </form>
                </section>
            @endif
        </div>
    </main>
</div>

@if ($shouldShowAds ?? false)
    <div
        class="level-ad-overlay"
        data-level-ad
        data-impression-url="{{ route('api.ads.impressions.store') }}"
        data-csrf="{{ csrf_token() }}"
        hidden
    >
        <div class="level-ad-dialog" role="dialog" aria-modal="true" aria-labelledby="levelAdTitle">
            <div class="level-ad-media" data-ad-media>
                <video data-ad-video playsinline muted></video>
                <div class="level-ad-fallback" data-ad-fallback>
                    <span>YoLearning</span>
                </div>
            </div>

            <div class="level-ad-copy">
                <small>Iklan</small>
                <h3 id="levelAdTitle" data-ad-title>Iklan singkat</h3>
                <p data-ad-description>Tunggu sampai hitungan selesai untuk melanjutkan.</p>
            </div>

            <div class="level-ad-footer">
                <span data-ad-countdown>15 detik</span>
                <button type="button" class="level-ad-continue" data-ad-continue disabled>Lanjut</button>
            </div>
        </div>
    </div>
@endif
@endsection

@push('styles')
<style>
    html,
    body {
        min-height: 100%;
        overflow-y: auto;
    }

    .quiz-focus-mode {
        display: block;
        min-height: 100vh;
        height: auto;
        overflow: visible;
        background: #0b0f17;
    }

    .quiz-focus-mode.app-frame {
        height: auto;
        min-height: 100vh;
        overflow: visible;
    }

    .quiz-main-panel {
        min-height: 100vh;
        height: auto;
        overflow: visible;
        background: #0b0f17;
    }

    .quiz-topbar {
        padding-inline: clamp(1rem, 4vw, 2rem);
        background: rgba(11, 15, 23, 0.94);
        backdrop-filter: blur(14px);
    }

    .quiz-content-area {
        max-width: 1040px;
        padding: clamp(1rem, 4vw, 2rem);
    }

    .quiz-brief {
        margin-bottom: 1rem;
        padding: 0.2rem 0 0.4rem;
    }

    .quiz-brief small,
    .quiz-eyebrow,
    .quiz-type-badge,
    .quiz-finish-panel small,
    .quiz-context-box strong,
    .story-status-label,
    .listening-step-counter {
        color: var(--cyan);
        font-size: 0.76rem;
        font-weight: 950;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    .quiz-brief h2 {
        margin: 0.5rem 0 0.3rem;
        font-size: clamp(1.5rem, 4vw, 2.6rem);
        letter-spacing: -0.06em;
        line-height: 1;
    }

    .quiz-brief p {
        color: var(--muted);
        line-height: 1.65;
        font-weight: 700;
        max-width: 720px;
    }

    .quiz-engine {
        display: grid;
        gap: 0.95rem;
        animation: dashIn 0.45s ease both;
    }

    .quiz-top-row,
    .quiz-card,
    .quiz-finish-panel,
    .empty-question-state {
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 24px;
        background: #131924;
    }

    .quiz-top-row {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.1rem;
    }

    .quiz-top-row h3 {
        margin-top: 0.35rem;
        font-size: clamp(1.1rem, 3vw, 1.55rem);
        letter-spacing: -0.04em;
    }

    .quiz-top-meta {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.65rem;
        flex-wrap: wrap;
    }

    .quiz-life-pill,
    .quiz-score-pill {
        border: 1px solid rgba(102, 232, 247, 0.22);
        border-radius: 999px;
        background: rgba(102, 232, 247, 0.08);
        color: #eaffff;
        padding: 0.55rem 0.8rem;
        font-weight: 950;
        white-space: nowrap;
    }

    .quiz-life-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .quiz-life-pill.is-free-mode {
        border-color: rgba(255, 255, 255, 0.12);
        background: rgba(255, 255, 255, 0.06);
        color: var(--muted);
    }

    .quiz-life-dots {
        display: inline-flex;
        gap: 0.28rem;
    }

    .quiz-life-dot {
        width: 0.62rem;
        height: 0.62rem;
        border-radius: 999px;
        background: linear-gradient(135deg, #7cf06b, #66e8f7);
        box-shadow: 0 0 16px rgba(102, 232, 247, 0.28);
    }

    .quiz-life-dot.is-lost {
        background: rgba(255, 255, 255, 0.14);
        box-shadow: none;
    }

    .quiz-progress-track {
        height: 8px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.06);
        overflow: hidden;
    }

    .quiz-progress-track span {
        display: block;
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, var(--cyan), var(--primary));
        transition: width 0.35s ease;
    }

    .quiz-card {
        position: relative;
        min-height: 520px;
        padding: clamp(1rem, 3vw, 1.5rem);
        padding-bottom: 5.5rem;
        transition: border-color 0.22s ease, transform 0.22s ease, box-shadow 0.22s ease;
        box-shadow: 0 20px 48px rgba(0, 0, 0, 0.18);
    }

    .quiz-card.is-correct {
        border-color: rgba(73, 211, 139, 0.58);
        box-shadow: 0 0 0 6px rgba(73, 211, 139, 0.08), 0 20px 48px rgba(0, 0, 0, 0.18);
        animation: correctPulse 0.5s ease both;
    }

    .quiz-card.is-wrong {
        border-color: rgba(255, 107, 138, 0.62);
        box-shadow: 0 0 0 6px rgba(255, 107, 138, 0.08), 0 20px 48px rgba(0, 0, 0, 0.18);
        animation: wrongShake 0.38s ease both;
    }

    .quiz-type-badge {
        display: inline-flex;
        border: 1px solid rgba(102, 232, 247, 0.22);
        border-radius: 999px;
        background: rgba(102, 232, 247, 0.08);
        padding: 0.45rem 0.72rem;
        margin-bottom: 1rem;
    }

    .quiz-body,
    .listening-stage,
    .listening-step-shell,
    .listening-question-block {
        display: grid;
        gap: 1rem;
    }

    .quiz-prompt,
    .quiz-context-box p,
    .story-panel p,
    .story-status,
    .quiz-finish-panel p {
        color: var(--muted);
        line-height: 1.6;
        font-weight: 700;
    }

    .quiz-question-text {
        font-size: clamp(1.5rem, 4vw, 2.4rem);
        line-height: 1.08;
        letter-spacing: -0.06em;
    }

    .study-shell {
        display: grid;
        gap: 1.1rem;
        animation: studyIn 0.42s ease both;
    }

    .study-prompt-grid {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr);
        gap: 1rem;
        align-items: center;
    }

    .study-mentor {
        width: 86px;
        aspect-ratio: 1;
        border: 1px solid rgba(102, 232, 247, 0.2);
        border-radius: 28px;
        display: grid;
        place-items: center;
        background:
            radial-gradient(circle at 30% 22%, rgba(102, 232, 247, 0.34), transparent 32%),
            linear-gradient(145deg, #172033, #101722);
        color: #eaffff;
        font-weight: 1000;
        box-shadow: 0 18px 36px rgba(0, 0, 0, 0.22);
        transform-origin: bottom center;
        animation: mentorFloat 3.8s ease-in-out infinite;
    }

    .study-phrase-card {
        position: relative;
        display: grid;
        gap: 0.72rem;
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 22px;
        background: linear-gradient(145deg, rgba(29, 36, 49, 0.96), rgba(16, 23, 34, 0.96));
        padding: 1rem;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.04);
    }

    .study-card-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.8rem;
        color: var(--muted);
        font-size: 0.78rem;
        font-weight: 900;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .study-phrase {
        position: relative;
        width: fit-content;
        max-width: 100%;
        border: 0;
        background: transparent;
        color: #eef7ff;
        padding: 0;
        text-align: left;
        font-size: clamp(1.6rem, 5vw, 3.6rem);
        font-weight: 1000;
        letter-spacing: 0;
        line-height: 1.05;
        cursor: help;
    }

    .study-phrase::after {
        content: '';
        position: absolute;
        left: 0;
        right: 0;
        bottom: -0.25rem;
        height: 3px;
        border-radius: 999px;
        background: linear-gradient(90deg, var(--cyan), var(--primary));
        opacity: 0.72;
        transform: scaleX(0.62);
        transform-origin: left;
        transition: transform 0.2s ease, opacity 0.2s ease;
    }

    .study-phrase:hover::after,
    .study-phrase:focus-visible::after {
        transform: scaleX(1);
        opacity: 1;
    }

    .translation-popover {
        position: absolute;
        left: 0;
        top: calc(100% + 0.75rem);
        z-index: 5;
        min-width: min(340px, 82vw);
        border: 1px solid rgba(102, 232, 247, 0.22);
        border-radius: 18px;
        background: rgba(13, 21, 31, 0.98);
        color: #e9f5ff;
        padding: 0.85rem 0.95rem;
        font-size: 0.98rem;
        font-weight: 850;
        line-height: 1.45;
        box-shadow: 0 18px 42px rgba(0, 0, 0, 0.34);
        opacity: 0;
        pointer-events: none;
        transform: translateY(-0.35rem) scale(0.98);
        transition: opacity 0.18s ease, transform 0.18s ease;
    }

    .translation-popover::before {
        content: '';
        position: absolute;
        left: 1.1rem;
        top: -7px;
        width: 12px;
        height: 12px;
        rotate: 45deg;
        background: inherit;
        border-left: 1px solid rgba(102, 232, 247, 0.22);
        border-top: 1px solid rgba(102, 232, 247, 0.22);
    }

    .study-phrase:hover .translation-popover,
    .study-phrase:focus-visible .translation-popover {
        opacity: 1;
        transform: translateY(0) scale(1);
    }

    .audio-chip,
    .answer-clear {
        border: 1px solid rgba(102, 232, 247, 0.22);
        border-radius: 999px;
        background: rgba(102, 232, 247, 0.09);
        color: #dffbff;
        padding: 0.48rem 0.72rem;
        font-weight: 950;
        cursor: pointer;
        transition: transform 0.18s ease, border-color 0.18s ease, background 0.18s ease;
    }

    .audio-chip:hover,
    .answer-clear:hover {
        transform: translateY(-2px);
        border-color: rgba(102, 232, 247, 0.42);
        background: rgba(102, 232, 247, 0.15);
    }

    .study-question-copy {
        display: grid;
        gap: 0.45rem;
    }

    .answer-workbench {
        display: grid;
        gap: 1rem;
        margin-top: 0.2rem;
    }

    .answer-zone {
        display: grid;
        gap: 0.65rem;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        padding: 0.9rem 0;
    }

    .answer-zone-head,
    .answer-actions,
    .answer-action-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.8rem;
        flex-wrap: wrap;
    }

    .answer-zone-label {
        color: var(--muted);
        font-size: 0.82rem;
        font-weight: 950;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .answer-slot {
        min-height: 58px;
        display: flex;
        align-items: center;
        border: 1px dashed rgba(255, 255, 255, 0.18);
        border-radius: 18px;
        background: rgba(255, 255, 255, 0.04);
        padding: 0.78rem 0.9rem;
        color: rgba(226, 232, 240, 0.68);
        font-weight: 950;
        line-height: 1.35;
        transition: border-color 0.18s ease, background 0.18s ease, color 0.18s ease, transform 0.18s ease;
    }

    .answer-slot.is-clickable {
        border-style: solid;
        cursor: pointer;
    }

    .answer-slot.is-clickable:hover {
        transform: translateY(-2px);
        border-color: rgba(102, 232, 247, 0.5);
    }

    .answer-slot.is-filled {
        border-style: solid;
        border-color: rgba(102, 232, 247, 0.36);
        background: rgba(102, 232, 247, 0.1);
        color: #effcff;
        animation: answerDrop 0.28s ease both;
    }

    .answer-option-bank {
        display: flex;
        flex-wrap: wrap;
        gap: 0.7rem;
    }

    .answer-token {
        min-height: 54px;
        border: 1px solid rgba(255, 255, 255, 0.12);
        border-radius: 16px;
        background: #1d2431;
        color: var(--text);
        padding: 0.78rem 0.92rem;
        font-weight: 1000;
        cursor: pointer;
        transition: transform 0.18s ease, border-color 0.18s ease, background 0.18s ease, opacity 0.18s ease;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.12);
    }

    .answer-token:hover:not(:disabled) {
        transform: translateY(-3px);
        border-color: rgba(102, 232, 247, 0.36);
        background: #263143;
    }

    .answer-token.is-selected {
        border-color: rgba(102, 232, 247, 0.58);
        background: rgba(102, 232, 247, 0.14);
        transform: translateY(-3px);
    }

    .answer-token[hidden] {
        display: none;
    }

    .answer-token.is-correct {
        border-color: rgba(73, 211, 139, 0.7);
        background: rgba(73, 211, 139, 0.16);
    }

    .answer-token.is-wrong {
        border-color: rgba(255, 107, 138, 0.7);
        background: rgba(255, 107, 138, 0.15);
    }

    .answer-token:disabled {
        cursor: not-allowed;
        opacity: 0.72;
    }

    .answer-token small {
        margin-left: 0.42rem;
        color: var(--cyan);
        font-size: 0.7rem;
        font-weight: 1000;
    }

    .answer-token img {
        display: block;
        width: 74px;
        height: 52px;
        margin-top: 0.45rem;
        border-radius: 12px;
        object-fit: cover;
    }

    .answer-actions,
    .answer-action-bar {
        margin-top: 0.2rem;
    }

    .answer-action-bar {
        border-top: 1px solid rgba(255, 255, 255, 0.12);
        padding-top: 0.85rem;
    }

    .sentence-answer-row {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        flex-wrap: wrap;
        min-height: 58px;
    }

    .sentence-selected-token {
        border: 1px solid rgba(102, 232, 247, 0.36);
        border-radius: 14px;
        background: rgba(102, 232, 247, 0.1);
        color: #effcff;
        padding: 0.72rem 0.86rem;
        font-weight: 1000;
        cursor: pointer;
        animation: answerDrop 0.22s ease both;
    }

    .sentence-selected-token:hover {
        background: rgba(102, 232, 247, 0.16);
    }

    .skip-question-button,
    .check-answer-button {
        min-width: 150px;
        border: 0;
        border-radius: 18px;
        padding: 0.95rem 1.15rem;
        font-weight: 1000;
        cursor: pointer;
        transition: transform 0.18s ease, opacity 0.18s ease, filter 0.18s ease;
    }

    .skip-question-button {
        border: 1px solid rgba(255, 255, 255, 0.12);
        background: rgba(255, 255, 255, 0.04);
        color: #cbd5e1;
    }

    .check-answer-button {
        background: linear-gradient(135deg, #a3e635, #4ade80);
        color: #07101f;
        box-shadow: 0 14px 24px rgba(74, 222, 128, 0.16);
    }

    .skip-question-button:hover:not(:disabled),
    .check-answer-button:hover:not(:disabled) {
        transform: translateY(-2px);
    }

    .skip-question-button:disabled,
    .check-answer-button:disabled {
        cursor: not-allowed;
        opacity: 0.52;
        filter: grayscale(0.25);
    }

    .check-answer-button.is-loading {
        position: relative;
        padding-left: 2.55rem;
    }

    .check-answer-button.is-loading::before {
        content: '';
        position: absolute;
        left: 1rem;
        top: 50%;
        width: 17px;
        height: 17px;
        margin-top: -8.5px;
        border: 3px solid rgba(7, 16, 31, 0.22);
        border-top-color: #07101f;
        border-radius: 50%;
        animation: spin 0.7s linear infinite;
    }

    .answer-workbench.is-checking .answer-slot {
        border-color: rgba(163, 230, 53, 0.44);
        background: rgba(163, 230, 53, 0.08);
    }

    .quiz-context-box,
    .story-panel,
    .word-match-board,
    .listening-question-block {
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 20px;
        background: #171e2a;
        padding: 1rem;
    }

    .quiz-context-box strong {
        display: block;
        margin-bottom: 0.35rem;
    }

    .quiz-audio {
        width: 100%;
        margin: 0.4rem 0 1rem;
        filter: invert(1) hue-rotate(180deg);
        opacity: 0.92;
    }

    .quiz-image {
        width: 100%;
        max-height: 260px;
        object-fit: cover;
        border-radius: 18px;
        border: 1px solid rgba(255, 255, 255, 0.08);
    }

    .video-question-panel {
        display: grid;
        gap: 1rem;
    }

    .video-frame {
        position: relative;
        width: 100%;
        aspect-ratio: 16 / 9;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 20px;
        background: #050812;
    }

    .video-frame iframe,
    .video-frame video {
        width: 100%;
        height: 100%;
        border: 0;
        display: block;
        object-fit: cover;
    }

    .video-question-status {
        color: var(--muted);
        font-weight: 850;
        line-height: 1.55;
    }

    .video-question-panel.is-locked .answer-option {
        opacity: .48;
        cursor: not-allowed;
    }

    .video-question-panel.is-locked .answer-token,
    .video-question-panel.is-locked .check-answer-button {
        opacity: .48;
        cursor: not-allowed;
    }

    .listening-instruction {
        font-size: 1rem;
        font-weight: 800;
        color: #dfe7f7;
        line-height: 1.55;
    }

    .story-text {
        color: #f4f7ff;
        font-size: clamp(1.18rem, 3vw, 1.8rem);
        letter-spacing: -0.035em;
        line-height: 1.45;
        white-space: pre-line;
    }

    .story-status {
        min-height: 1.4rem;
    }

    .reading-story-stage {
        min-height: 100%;
        display: flex;
        flex-direction: column;
        gap: 1rem;
        padding-bottom: 1.25rem;
        overflow: visible;
    }

    .reading-start {
        display: grid;
        gap: 0.8rem;
        align-content: center;
        min-height: 42vh;
    }

    .reading-flow {
        display: grid;
        gap: 1.45rem;
        padding: 0.2rem clamp(0.25rem, 2vw, 1rem) 5rem;
        overflow: visible;
    }

    .reading-line {
        width: min(42rem, 70%);
        max-width: 100%;
        display: grid;
        gap: 0.32rem;
        animation: studyIn 0.28s ease both;
        overflow-wrap: anywhere;
        word-break: normal;
    }

    .reading-line.is-left {
        justify-self: start;
    }

    .reading-line.is-right {
        justify-self: end;
        text-align: right;
        margin-right: clamp(0.25rem, 1.5vw, 0.75rem);
    }

    .reading-line-speaker {
        color: var(--cyan);
        font-size: 0.78rem;
        font-weight: 950;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    .reading-line-text {
        margin: 0;
        color: #f6f9ff;
        font-size: clamp(1.15rem, 2.25vw, 2.15rem);
        font-weight: 900;
        letter-spacing: -0.025em;
        line-height: 1.28;
        white-space: pre-line;
        max-width: 100%;
        overflow-wrap: anywhere;
        word-break: break-word;
    }

    .reading-question-inline {
        width: min(880px, 100%);
        display: grid;
        gap: 1rem;
        justify-self: center;
        padding: 1.15rem 0 0.45rem;
        border-top: 1px solid rgba(255, 255, 255, 0.12);
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        animation: studyIn 0.28s ease both;
    }

    .reading-question-inline h4 {
        margin: 0;
        color: #f8fbff;
        font-size: clamp(1.35rem, 3vw, 2.3rem);
        line-height: 1.15;
        letter-spacing: -0.04em;
    }

        .reading-answer-grid,
        .listening-token-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.7rem;
    }

    .reading-answer-button {
        min-height: 56px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 16px;
        background: #1d2431;
        color: var(--text);
        padding: 0.85rem 1rem;
        text-align: center;
        font-weight: 900;
        cursor: pointer;
        transition: transform 0.18s ease, border-color 0.18s ease, background 0.18s ease;
    }

    .reading-answer-button:hover:not(:disabled) {
        transform: translateY(-2px);
        border-color: rgba(102, 232, 247, 0.34);
        background: #242d3d;
    }

    .reading-answer-button.is-correct {
        border-color: rgba(73, 211, 139, 0.58);
        background: rgba(73, 211, 139, 0.13);
    }

    .reading-answer-button.is-wrong {
        border-color: rgba(255, 107, 138, 0.62);
        background: rgba(255, 107, 138, 0.13);
    }

    .reading-inline-status {
        min-height: 1.3rem;
        color: var(--muted);
        font-weight: 850;
    }

    .simple-listening-stage {
        min-height: 100%;
        display: grid;
        align-content: center;
        gap: 1.25rem;
    }

    .listening-mic-area {
        display: grid;
        justify-items: center;
        gap: 0.85rem;
        text-align: center;
    }

        .listening-mic-button {
            width: 124px;
            height: 124px;
            display: grid;
            place-items: center;
        border: 1px solid rgba(102, 232, 247, 0.28);
        border-radius: 999px;
            background: radial-gradient(circle at 28% 22%, rgba(102, 232, 247, 0.25), rgba(105, 119, 255, 0.18) 58%, #1d2431);
            color: #eff6ff;
            font-size: 1.3rem;
            font-weight: 950;
            letter-spacing: 0.16em;
            cursor: pointer;
            box-shadow: 0 22px 50px rgba(102, 232, 247, 0.1);
            transition: transform 0.18s ease, border-color 0.18s ease;
    }

    .listening-mic-button:hover:not(:disabled) {
        transform: translateY(-3px);
        border-color: rgba(102, 232, 247, 0.54);
    }

    .listening-mic-button:disabled {
        cursor: wait;
        opacity: 0.68;
    }

    .listening-order-workbench {
        display: grid;
        gap: 0.85rem;
    }

    .hidden-audio {
        display: none;
    }

    .simple-listening {
        gap: 1.15rem;
    }

    .listening-history {
        display: grid;
        gap: 1.25rem;
    }

    .listening-history-card {
        border: 0;
        border-radius: 0;
        background: transparent;
        padding: 0;
    }

    .listening-history-card.is-story,
    .listening-history-card.is-question,
    .listening-history-card.is-done {
        border: 0;
        background: transparent;
    }

    .listening-history-card small {
        display: none;
    }

    .listening-history-card .story-text {
        margin: 0;
        color: #eef4ff;
        font-size: clamp(1.28rem, 3vw, 2rem);
        font-weight: 800;
        letter-spacing: -0.04em;
        line-height: 1.45;
    }

    .listening-question-plain {
        display: grid;
        gap: 0.9rem;
        margin-top: 0.2rem;
    }

    .listening-question-plain .quiz-question-text {
        margin: 0;
    }

    .listening-finish-box {
        display: grid;
        gap: 0.75rem;
        margin-top: 0.5rem;
        padding-top: 1rem;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
        background: transparent;
    }

    .listening-finish-box small {
        display: block;
        color: var(--cyan);
        font-size: 0.76rem;
        font-weight: 950;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    .listening-finish-box h4 {
        font-size: clamp(1.2rem, 3vw, 1.7rem);
        letter-spacing: -0.04em;
    }

    .listening-muted-status {
        color: var(--muted);
        font-size: 0.92rem;
        font-weight: 700;
        line-height: 1.55;
    }

    .story-action-button,
    .story-next-button,
    .complete-level-button,
    .listening-manual-button,
    .listening-finish-button {
        width: fit-content;
        border: 0;
        border-radius: 999px;
        background: linear-gradient(135deg, var(--cyan), var(--primary));
        color: #07101f;
        padding: 0.84rem 1.1rem;
        font-weight: 950;
        cursor: pointer;
        transition: transform 0.18s ease, opacity 0.18s ease;
        box-shadow: 0 14px 28px rgba(102, 232, 247, 0.14);
    }

    .story-action-button:hover,
    .story-next-button:hover,
    .complete-level-button:hover,
    .listening-manual-button:hover,
    .listening-finish-button:hover {
        transform: translateY(-2px);
    }

    .interactive-options {
        display: grid;
        gap: 0.75rem;
    }

    .answer-option {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 18px;
        background: #1d2431;
        color: var(--text);
        padding: 0.95rem 1rem;
        text-align: left;
        cursor: pointer;
        transition: transform 0.18s ease, border-color 0.18s ease, background 0.18s ease, opacity 0.18s ease;
    }

    .answer-option:hover:not(:disabled) {
        transform: translateY(-2px);
        border-color: rgba(102, 232, 247, 0.32);
        background: #242d3d;
    }

    .answer-option:disabled {
        cursor: not-allowed;
        opacity: 0.95;
    }

    .answer-option.is-correct {
        border-color: rgba(73, 211, 139, 0.62);
        background: rgba(73, 211, 139, 0.14);
    }

    .answer-option.is-wrong {
        border-color: rgba(255, 107, 138, 0.64);
        background: rgba(255, 107, 138, 0.13);
    }

    .answer-option img {
        width: 74px;
        height: 52px;
        object-fit: cover;
        border-radius: 12px;
    }

    .answer-option audio {
        max-width: 180px;
        height: 32px;
    }

    .quiz-feedback {
        position: absolute;
        left: 1rem;
        right: 1rem;
        bottom: 1rem;
        display: none;
        border-radius: 16px;
        padding: 0.85rem 0.95rem;
        font-weight: 900;
        box-shadow: 0 18px 42px rgba(0, 0, 0, 0.18);
    }

    .quiz-feedback.is-visible {
        display: block;
        animation: feedbackIn 0.24s ease both;
    }

    .quiz-feedback.correct {
        border: 1px solid rgba(73, 211, 139, 0.34);
        background: rgba(17, 56, 39, 0.96);
        color: #d8ffe9;
    }

    .quiz-feedback.wrong {
        border: 1px solid rgba(255, 107, 138, 0.34);
        background: rgba(64, 25, 37, 0.96);
        color: #ffe4eb;
    }

    .word-match-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.8rem;
    }

    .match-column {
        display: grid;
        gap: 0.55rem;
    }

    .match-tile {
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 16px;
        background: #1d2431;
        color: var(--text);
        padding: 0.75rem;
        text-align: left;
        cursor: pointer;
        transition: 0.18s ease;
        font-weight: 900;
    }

    .match-tile small {
        display: block;
        margin-top: 0.35rem;
        color: var(--accent);
        font-size: 0.72rem;
        letter-spacing: 0;
        opacity: 0.86;
    }

    .match-tile:hover:not(:disabled),
    .match-tile.is-selected {
        border-color: rgba(102, 232, 247, 0.34);
        background: #252f40;
        transform: translateY(-2px);
    }

    .match-tile.is-matched {
        border-color: rgba(73, 211, 139, 0.52);
        background: rgba(73, 211, 139, 0.13);
        cursor: default;
    }

    .match-tile.is-wrong {
        border-color: rgba(255, 107, 138, 0.6);
        background: rgba(255, 107, 138, 0.13);
    }

    .story-segment-count {
        color: var(--muted);
        font-size: 0.82rem;
        font-weight: 850;
    }

    .quiz-finish-panel {
        padding: 1.2rem;
        animation: finishIn 0.48s ease both;
    }

    .empty-question-state {
        margin-top: 1rem;
        padding: 1rem;
    }

    .level-ad-overlay {
        position: fixed;
        inset: 0;
        z-index: 80;
        display: grid;
        place-items: center;
        padding: 1.25rem;
        background: rgba(6, 10, 18, 0.86);
        backdrop-filter: blur(14px);
    }

    .level-ad-overlay[hidden] {
        display: none;
    }

    .level-ad-dialog {
        width: min(520px, 100%);
        border: 1px solid rgba(255, 255, 255, 0.14);
        border-radius: 24px;
        background: #111827;
        box-shadow: 0 28px 80px rgba(0, 0, 0, 0.45);
        overflow: hidden;
    }

    .level-ad-media {
        position: relative;
        aspect-ratio: 16 / 9;
        background: #0b1220;
        overflow: hidden;
    }

    .level-ad-media video,
    .level-ad-fallback {
        width: 100%;
        height: 100%;
    }

    .level-ad-media video {
        display: block;
        object-fit: cover;
    }

    .level-ad-fallback {
        display: grid;
        place-items: center;
        background:
            radial-gradient(circle at 28% 20%, rgba(45, 212, 191, 0.28), transparent 32%),
            linear-gradient(135deg, #111827, #1e293b 56%, #0f172a);
        color: #e0f2fe;
        font-size: clamp(2rem, 7vw, 3.8rem);
        font-weight: 900;
    }

    .level-ad-copy,
    .level-ad-footer {
        padding: 1.1rem 1.2rem;
    }

    .level-ad-copy small {
        color: var(--cyan);
        font-weight: 900;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .level-ad-copy h3 {
        margin: 0.35rem 0;
        color: #fff;
        font-size: 1.35rem;
    }

    .level-ad-copy p {
        margin: 0;
        color: var(--muted);
        font-weight: 700;
        line-height: 1.55;
    }

    .level-ad-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
    }

    .level-ad-footer span {
        color: #e2e8f0;
        font-weight: 900;
    }

    .level-ad-continue {
        border: 0;
        border-radius: 999px;
        padding: 0.72rem 1.1rem;
        color: #07111f;
        background: linear-gradient(135deg, var(--cyan), #bbf7d0);
        font-weight: 900;
        cursor: pointer;
    }

    .level-ad-continue:disabled {
        cursor: wait;
        opacity: 0.55;
        filter: grayscale(0.4);
    }

    @media (min-width: 761px) {
        html,
        body {
            height: 100%;
            overflow: hidden;
        }

        .quiz-focus-mode,
        .quiz-focus-mode.app-frame,
        .quiz-main-panel {
            height: 100dvh;
            min-height: 100dvh;
            overflow: hidden;
        }

        .quiz-topbar {
            min-height: 62px;
            padding: 0.6rem clamp(1rem, 3vw, 2rem);
        }

        .quiz-topbar h1 {
            font-size: clamp(1rem, 2vw, 1.28rem);
            letter-spacing: -0.03em;
        }

        .quiz-topbar p {
            font-size: 0.78rem;
        }

        .quiz-content-area {
            width: min(1120px, calc(100vw - 2rem));
            height: calc(100dvh - 62px);
            max-width: 1120px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            padding: 0.75rem 1rem 0.9rem;
            margin-inline: auto;
        }

        .quiz-brief {
            display: none;
        }

        .quiz-engine {
            height: 100%;
            min-height: 0;
            display: grid;
            grid-template-rows: auto 6px minmax(0, 1fr) auto;
            gap: 0.62rem;
        }

        .quiz-top-row {
            align-items: center;
            padding: 0.25rem 0 0;
            border: 0;
            border-radius: 0;
            background: transparent;
        }

        .quiz-top-row h3 {
            margin-top: 0.22rem;
            font-size: clamp(1.25rem, 2.6vw, 2rem);
            line-height: 1.08;
        }

        .quiz-eyebrow {
            font-size: 0.72rem;
        }

        .quiz-score-pill {
            padding: 0.48rem 0.76rem;
            border-radius: 16px;
        }

        .quiz-progress-track {
            height: 6px;
        }

        .quiz-card {
            height: 100%;
            min-height: 0;
            display: grid;
            grid-template-rows: auto minmax(0, 1fr) auto;
            overflow: hidden;
            border: 0;
            border-radius: 0;
            background: transparent;
            box-shadow: none;
            padding: 0.1rem 0 0;
        }

        .quiz-card.is-correct,
        .quiz-card.is-wrong {
            box-shadow: none;
        }

        .quiz-type-badge {
            width: fit-content;
            margin-bottom: 0.35rem;
            padding: 0.34rem 0.58rem;
            font-size: 0.68rem;
        }

        .quiz-body {
            min-height: 0;
            height: 100%;
            overflow: hidden;
        }

        .quiz-body.is-reading-flow {
            height: auto;
            min-height: 0;
            overflow: visible;
            scrollbar-width: none;
        }

        .quiz-body.is-reading-flow::-webkit-scrollbar {
            display: none;
        }

        body:has(.quiz-engine.is-reading-story-mode) .quiz-focus-mode,
        body:has(.quiz-engine.is-reading-story-mode) .quiz-focus-mode.app-frame,
        body:has(.quiz-engine.is-reading-story-mode) .quiz-main-panel {
            height: auto;
            min-height: 100dvh;
            overflow: visible;
        }

        body:has(.quiz-engine.is-reading-story-mode) .quiz-content-area {
            height: auto;
            min-height: calc(100dvh - 62px);
            overflow: visible;
        }

        .quiz-engine.is-reading-story-mode {
            height: auto;
            min-height: calc(100dvh - 84px);
            grid-template-rows: auto 6px auto auto;
        }

        .quiz-engine.is-reading-story-mode .quiz-card {
            height: auto;
            min-height: 58vh;
            overflow: visible;
        }

        .study-shell {
            height: 100%;
            min-height: 0;
            display: flex;
            flex-direction: column;
            gap: 0.58rem;
            animation: studyIn 0.32s ease both;
        }

        .study-shell > .quiz-prompt,
        .video-question-panel > .quiz-prompt {
            display: none;
        }

        .study-prompt-grid {
            display: block;
        }

        .study-mentor {
            display: none;
        }

        .study-phrase-card {
            gap: 0.35rem;
            border: 0;
            border-radius: 0;
            background: transparent;
            box-shadow: none;
            padding: 0.15rem 0 0.25rem;
        }

        .study-card-top {
            justify-content: flex-start;
            gap: 0.7rem;
            font-size: 0.66rem;
        }

        .study-phrase {
            font-size: clamp(2rem, 6vw, 4.35rem);
            line-height: 0.98;
        }

        .audio-chip,
        .answer-clear {
            padding: 0.34rem 0.58rem;
            font-size: 0.76rem;
        }

        .study-question-copy {
            gap: 0.22rem;
            padding-top: 0.05rem;
        }

        .answer-zone-label {
            font-size: 0.68rem;
        }

        .quiz-question-text {
            font-size: clamp(1.65rem, 4vw, 3rem);
            line-height: 1.02;
        }

        .quiz-audio {
            display: none;
        }

        .quiz-context-box,
        .story-panel,
        .word-match-board,
        .listening-question-block {
            border-radius: 14px;
            padding: 0.7rem;
        }

        .answer-workbench {
            flex: 1 1 auto;
            height: 100%;
            min-height: 0;
            display: grid;
            grid-template-rows: auto minmax(0, 1fr) auto;
            gap: 0.55rem;
            margin-top: 0;
            padding-top: 0.1rem;
        }

        .answer-zone {
            gap: 0.38rem;
            padding: 0.55rem 0 0.52rem;
            border-top: 1px solid rgba(255, 255, 255, 0.11);
            border-bottom: 0;
        }

        .answer-zone-head {
            min-height: 1.25rem;
        }

        .answer-slot {
            min-height: 46px;
            border-radius: 14px;
            padding: 0.62rem 0.75rem;
            font-size: 0.92rem;
        }

        .answer-option-bank {
            align-content: start;
            gap: 0.58rem;
            min-height: 0;
            max-height: 100%;
            overflow-y: auto;
            padding: 0.52rem 0 0.35rem;
            border-top: 1px solid rgba(255, 255, 255, 0.09);
            scrollbar-width: thin;
        }

        .answer-token {
            min-height: 46px;
            border-radius: 14px;
            padding: 0.62rem 0.78rem;
            font-size: 0.9rem;
            box-shadow: none;
        }

        .answer-action-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.13);
            padding-top: 0.72rem;
            margin-top: 0.1rem;
        }

        .skip-question-button,
        .check-answer-button {
            min-width: 158px;
            border-radius: 16px;
            padding: 0.78rem 1rem;
        }

        .quiz-feedback {
            position: static;
            left: auto;
            right: auto;
            bottom: auto;
            margin-top: 0.52rem;
            padding: 0.68rem 0.78rem;
            border-radius: 14px;
        }

        .quiz-finish-panel {
            padding: 0.95rem;
        }
    }

    @media (min-width: 761px) and (max-height: 760px) {
        .quiz-topbar {
            min-height: 54px;
            padding-block: 0.45rem;
        }

        .quiz-content-area {
            height: calc(100dvh - 54px);
            padding-block: 0.5rem 0.65rem;
        }

        .quiz-engine {
            gap: 0.45rem;
        }

        .quiz-top-row h3 {
            font-size: clamp(1.08rem, 2.2vw, 1.65rem);
        }

        .study-phrase {
            font-size: clamp(1.72rem, 5vw, 3.4rem);
        }

        .quiz-question-text {
            font-size: clamp(1.35rem, 3.2vw, 2.25rem);
        }

        .answer-slot,
        .answer-token {
            min-height: 40px;
            padding-block: 0.5rem;
        }

        .skip-question-button,
        .check-answer-button {
            padding-block: 0.68rem;
        }
    }

    @keyframes correctPulse {
        0% { transform: scale(1); }
        45% { transform: scale(1.012); }
        100% { transform: scale(1); }
    }

    @keyframes wrongShake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        55% { transform: translateX(5px); }
        78% { transform: translateX(-3px); }
    }

    @keyframes feedbackIn {
        from { opacity: 0; transform: translateY(0.45rem); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes studyIn {
        from { opacity: 0; transform: translateY(0.8rem); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes mentorFloat {
        0%, 100% { transform: translateY(0) rotate(-1deg); }
        50% { transform: translateY(-7px) rotate(1deg); }
    }

    @keyframes answerDrop {
        from { opacity: 0; transform: translateY(-0.35rem) scale(0.98); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    @keyframes finishIn {
        from { opacity: 0; transform: translateY(1rem) scale(0.98); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    @media (max-width: 760px) {
        .quiz-top-row {
            align-items: flex-start;
            flex-direction: column;
        }

        .word-match-grid {
            grid-template-columns: 1fr;
        }

        .reading-line {
            width: 100%;
            max-width: 100%;
        }

        .reading-line.is-right {
            margin-right: 0;
        }

        .reading-answer-grid,
        .listening-token-grid {
            grid-template-columns: 1fr;
        }

        .study-prompt-grid {
            grid-template-columns: 1fr;
        }

        .study-mentor {
            width: 68px;
            border-radius: 22px;
        }

        .skip-question-button,
        .check-answer-button {
            width: 100%;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    (() => {
        const quizEngine = document.getElementById('quizEngine');

        if (!quizEngine) {
            return;
        }

        const parseJson = (value, fallback = null) => {
            try {
                return JSON.parse(value || 'null') ?? fallback;
            } catch (error) {
                return fallback;
            }
        };

        const questions = parseJson(quizEngine.dataset.questions, []);
        const questionQueue = questions.map((question, originalIndex) => ({
            ...question,
            originalIndex,
            queueKey: `${question.id || originalIndex}-0`,
            retryRound: 0,
        }));
        const initialQuestionTotal = questions.length;
        const noLifeQuestionTypes = new Set(['reading_story', 'listening']);
        const maxLives = 5;
        const startedAt = Date.now();
        const currentNumber = quizEngine.querySelector('[data-current-number]');
        const totalNumber = quizEngine.querySelector('[data-total-number]');
        const questionTitle = quizEngine.querySelector('[data-question-title]');
        const questionType = quizEngine.querySelector('[data-question-type]');
        const questionBody = quizEngine.querySelector('[data-question-body]');
        const questionCard = quizEngine.querySelector('[data-question-card]');
        const feedback = quizEngine.querySelector('[data-feedback]');
        const progressBar = quizEngine.querySelector('[data-progress-bar]');
        const correctCountNode = quizEngine.querySelector('[data-correct-count]');
        const lifePill = quizEngine.querySelector('[data-life-pill]');
        const lifeDots = quizEngine.querySelector('[data-life-dots]');
        const finishPanel = quizEngine.querySelector('[data-finish-panel]');
        const completeForm = quizEngine.querySelector('[data-complete-form]');
        const studySecondsInput = quizEngine.querySelector('[data-study-seconds]');
        const correctInput = quizEngine.querySelector('[data-correct-input]');
        const totalInput = quizEngine.querySelector('[data-total-input]');
        const questionResultsInput = quizEngine.querySelector('[data-question-results]');
        const adOverlay = document.querySelector('[data-level-ad]');
        const adVideo = adOverlay?.querySelector('[data-ad-video]');
        const adFallback = adOverlay?.querySelector('[data-ad-fallback]');
        const adTitle = adOverlay?.querySelector('[data-ad-title]');
        const adDescription = adOverlay?.querySelector('[data-ad-description]');
        const adCountdown = adOverlay?.querySelector('[data-ad-countdown]');
        const adContinue = adOverlay?.querySelector('[data-ad-continue]');
        const adSettings = {
            enabled: quizEngine.dataset.showAds === 'true',
            entry: parseJson(quizEngine.dataset.entryAd),
            exit: parseJson(quizEngine.dataset.exitAd),
        };

        let index = 0;
        let correctCount = 0;
        let lives = maxLives;
        let locked = false;
        let exitAdShown = false;
        let activeAdTimer = null;
        const questionResults = new Map();
        const completedQuestionIds = new Set();
        const queuedRetryKeys = new Set();

        totalNumber.textContent = initialQuestionTotal;
        totalInput.value = initialQuestionTotal;

        const escapeHtml = (value) => {
            return String(value || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        };

        const shuffle = (items) => {
            return [...items].sort(() => Math.random() - 0.5);
        };

        const normalizeAnswer = (value) => {
            return String(value || '')
                .toLowerCase()
                .replace(/[^\p{L}\p{N}\s]/gu, '')
                .replace(/\s+/g, ' ')
                .trim();
        };

        let transientAudio = null;

        const playAudioUrl = (url) => {
            if (!url) {
                return;
            }

            try {
                transientAudio?.pause();
                transientAudio = new Audio(url);
                transientAudio.currentTime = 0;
                transientAudio.play().catch(() => {});
            } catch (error) {
                // Audio is optional learning support, so a blocked file should not stop the quiz.
            }
        };

        const attachAudioTriggers = (scope) => {
            scope.querySelectorAll('[data-play-audio]').forEach((button) => {
                button.addEventListener('click', (event) => {
                    event.stopPropagation();
                    playAudioUrl(button.dataset.playAudio || '');
                });
            });
        };

        const quotedPhraseFromQuestion = (question) => {
            const text = String(question?.questionText || '');
            const quoted = text.match(/["“”']([^"“”']+)["“”']/);

            return quoted?.[1] || '';
        };

        const translationFallback = (question) => {
            const text = String(question?.questionText || '').toLowerCase();

            if (text.includes('apa arti') || text.includes('what is the meaning')) {
                return question?.correctAnswer || '';
            }

            return '';
        };

        const renderLearningFocus = (question) => {
            const phrase = question.settings?.learningPhrase || quotedPhraseFromQuestion(question);
            const translation = question.settings?.learningPhraseTranslation || translationFallback(question);
            const audioUrl = question.settings?.learningPhraseAudioUrl || question.audioUrl || '';

            if (!phrase && !translation && !audioUrl) {
                return '';
            }

            return `
                <div class="study-prompt-grid">
                    <div class="study-mentor" aria-hidden="true">Yo</div>
                    <div class="study-phrase-card">
                        <div class="study-card-top">
                            <span>Fokus bahasa asing</span>
                            ${audioUrl ? `<button type="button" class="audio-chip" data-play-audio="${escapeHtml(audioUrl)}">Dengar</button>` : ''}
                        </div>
                        <button type="button" class="study-phrase" ${audioUrl ? `data-play-audio="${escapeHtml(audioUrl)}"` : ''}>
                            ${escapeHtml(phrase || 'Dengarkan audio')}
                            ${translation ? `<span class="translation-popover">${escapeHtml(translation)}</span>` : ''}
                        </button>
                    </div>
                </div>
            `;
        };

        const selectedAnswerText = (button) => {
            if (!button) {
                return null;
            }

            const textNode = button.querySelector('span');
            return (textNode?.textContent || button.textContent || '').replace(/\s+/g, ' ').trim() || null;
        };

        const recordQuestionResult = (question, isCorrect, selectedAnswer = null) => {
            if (!question?.id) {
                return;
            }

            const existing = questionResults.get(question.id) || {
                question_id: question.id,
                attempts: 0,
                is_correct: false,
                selected_answer: null,
            };

            existing.attempts += 1;
            existing.is_correct = Boolean(existing.is_correct || isCorrect);
            existing.selected_answer = selectedAnswer || existing.selected_answer;
            questionResults.set(question.id, existing);
        };

        const syncQuestionResults = () => {
            if (!questionResultsInput) {
                return;
            }

            questionResultsInput.value = JSON.stringify(Array.from(questionResults.values()));
        };

        const questionIdentity = (question) => String(question?.id ?? question?.originalIndex ?? question?.queueKey ?? '');

        const isNoLifeQuestion = (question) => noLifeQuestionTypes.has(question?.type);

        const updateLives = (question = questionQueue[index]) => {
            if (!lifePill || !lifeDots) {
                return;
            }

            if (isNoLifeQuestion(question)) {
                lifePill.classList.add('is-free-mode');
                lifePill.firstElementChild.textContent = 'Bebas nyawa';
                lifeDots.innerHTML = '';
                return;
            }

            lifePill.classList.remove('is-free-mode');
            lifePill.firstElementChild.textContent = 'Nyawa';
            lifeDots.innerHTML = Array.from({ length: maxLives }).map((_, dotIndex) => (
                `<span class="quiz-life-dot ${dotIndex >= lives ? 'is-lost' : ''}"></span>`
            )).join('');
        };

        const completeQuestion = (question) => {
            const key = questionIdentity(question);

            if (!completedQuestionIds.has(key)) {
                completedQuestionIds.add(key);
                correctCount = completedQuestionIds.size;
                correctCountNode.textContent = correctCount;
                correctInput.value = correctCount;
            }
        };

        const queueRetryQuestion = (question) => {
            if (!question || isNoLifeQuestion(question)) {
                return;
            }

            const key = questionIdentity(question);

            if (completedQuestionIds.has(key) || queuedRetryKeys.has(key)) {
                return;
            }

            queuedRetryKeys.add(key);
            questionQueue.push({
                ...question,
                retryRound: (question.retryRound || 0) + 1,
                queueKey: `${key}-${(question.retryRound || 0) + 1}`,
                isRetry: true,
            });
        };

        const clearRetryFlag = (question) => {
            queuedRetryKeys.delete(questionIdentity(question));
        };

        const failLevel = () => {
            locked = true;
            resetFeedback();
            questionTitle.textContent = 'Nyawa habis';
            questionType.textContent = 'Ulangi level';
            progressBar.style.width = '0%';
            updateLives(questionQueue[index]);
            questionBody.innerHTML = `
                <div class="quiz-context-box">
                    <strong>Level belum selesai</strong>
                    <p>Kesempatanmu habis. Ulangi level dari awal supaya pondasinya benar-benar kuat.</p>
                </div>
                <div class="answer-action-bar">
                    <button type="button" class="check-answer-button" data-restart-level>Ulangi Level</button>
                    <a class="skip-question-button" href="{{ route('learning.parts.show', $part) }}">Kembali ke Peta</a>
                </div>
            `;
            questionBody.querySelector('[data-restart-level]')?.addEventListener('click', () => {
                window.location.reload();
            });
        };

        const registerWrongAttempt = (question, selectedAnswer = null) => {
            recordQuestionResult(question, false, selectedAnswer);
            syncQuestionResults();

            if (isNoLifeQuestion(question)) {
                return true;
            }

            lives = Math.max(0, lives - 1);
            updateLives(question);
            queueRetryQuestion(question);

            if (lives <= 0) {
                failLevel();
                return false;
            }

            return true;
        };

        const renderAnswerWorkspace = (question) => {
            if (!question.options || question.options.length === 0) {
                return `
                    <div class="answer-workbench" data-answer-workbench data-empty-workbench="true">
                        <p class="quiz-prompt">Belum ada opsi jawaban untuk soal ini.</p>
                        <div class="answer-action-bar">
                            <button type="button" class="skip-question-button" data-skip-question>Lompati</button>
                        </div>
                    </div>
                `;
            }

            const options = shuffle(question.options);

            return `
                <div class="answer-workbench" data-answer-workbench>
                    <div class="answer-zone">
                        <div class="answer-zone-head">
                            <span class="answer-zone-label">Kolom jawaban</span>
                            <button type="button" class="answer-clear" data-clear-answer hidden>Ganti jawaban</button>
                        </div>
                        <button type="button" class="answer-slot" data-answer-slot>Pilih jawaban dari opsi di bawah.</button>
                    </div>

                    <div class="answer-option-bank">
                        ${options.map((option, optionIndex) => `
                            <button
                                type="button"
                                class="answer-token"
                                data-choice-token
                                data-correct="${option.isCorrect ? 'true' : 'false'}"
                                data-audio-url="${escapeHtml(option.audioUrl || '')}"
                            >
                                <span data-option-text>${escapeHtml(option.text)}</span>
                                ${option.audioUrl ? '<small>Audio</small>' : ''}
                                ${option.imageUrl ? `<img src="${escapeHtml(option.imageUrl)}" alt="Gambar opsi">` : ''}
                            </button>
                        `).join('')}
                    </div>

                    <div class="answer-action-bar">
                        <button type="button" class="skip-question-button" data-skip-question>Lompati</button>
                        <button type="button" class="check-answer-button" data-check-answer disabled>Periksa</button>
                    </div>
                </div>
            `;
        };

        const attachAnswerWorkspaceEvents = (question, scope = questionBody) => {
            const workspace = scope.querySelector('[data-answer-workbench]');

            if (!workspace) {
                return;
            }

            const slot = workspace.querySelector('[data-answer-slot]');
            const clearButton = workspace.querySelector('[data-clear-answer]');
            const checkButton = workspace.querySelector('[data-check-answer]');
            const skipButton = workspace.querySelector('[data-skip-question]');
            const tokens = Array.from(workspace.querySelectorAll('[data-choice-token]'));
            let selected = null;

            if (!slot || !clearButton || !checkButton) {
                skipButton?.addEventListener('click', () => {
                    if (locked) {
                        return;
                    }

                    locked = true;
                    const canContinue = registerWrongAttempt(question, 'skipped');

                    if (!canContinue) {
                        return;
                    }

                    showFeedback(false, isNoLifeQuestion(question) ? 'Belum selesai. Coba lagi dengan tenang.' : 'Soal ini akan muncul lagi nanti.');

                    window.setTimeout(() => {
                        locked = false;
                        goNext();
                    }, 520);
                });

                return;
            }

            const resetSelection = () => {
                if (selected?.button) {
                    selected.button.hidden = false;
                }

                selected = null;
                slot.textContent = 'Pilih jawaban dari opsi di bawah.';
                slot.classList.remove('is-filled', 'is-clickable');
                clearButton.hidden = true;
                checkButton.disabled = true;
                tokens.forEach((token) => {
                    token.hidden = false;
                    token.classList.remove('is-selected', 'is-wrong');
                });
            };

            tokens.forEach((token) => {
                token.addEventListener('click', () => {
                    if (locked) {
                        return;
                    }

                    if (selected?.button && selected.button !== token) {
                        selected.button.hidden = false;
                        selected.button.classList.remove('is-selected', 'is-wrong');
                    }

                    tokens.forEach((item) => item.classList.remove('is-selected', 'is-wrong'));
                    token.classList.add('is-selected');
                    token.hidden = true;

                    const optionText = token.querySelector('[data-option-text]')?.textContent?.trim() || token.textContent.trim();
                    selected = {
                        button: token,
                        text: optionText,
                        isCorrect: token.dataset.correct === 'true',
                    };

                    slot.textContent = optionText;
                    slot.classList.add('is-filled', 'is-clickable');
                    clearButton.hidden = false;
                    checkButton.disabled = false;
                    playAudioUrl(token.dataset.audioUrl || '');
                });
            });

            slot.addEventListener('click', () => {
                if (!locked && selected) {
                    resetSelection();
                }
            });

            clearButton.addEventListener('click', () => {
                if (!locked) {
                    resetSelection();
                }
            });

            skipButton.addEventListener('click', () => {
                if (locked) {
                    return;
                }

                locked = true;
                const canContinue = registerWrongAttempt(question, 'skipped');

                if (!canContinue) {
                    return;
                }

                showFeedback(false, isNoLifeQuestion(question) ? 'Belum selesai. Coba lagi dengan tenang.' : 'Soal ini akan muncul lagi nanti.');

                window.setTimeout(() => {
                    locked = false;
                    goNext();
                }, 520);
            });

            checkButton.addEventListener('click', () => {
                if (locked || !selected) {
                    return;
                }

                locked = true;
                workspace.classList.add('is-checking');
                checkButton.classList.add('is-loading');
                checkButton.textContent = 'Memeriksa...';
                checkButton.disabled = true;
                skipButton.disabled = true;
                tokens.forEach((token) => {
                    token.disabled = true;
                });

                window.setTimeout(() => {
                    workspace.classList.remove('is-checking');
                    checkButton.classList.remove('is-loading');
                    checkButton.textContent = 'Periksa';

                    if (selected.isCorrect) {
                        recordQuestionResult(question, true, selected.text);
                        syncQuestionResults();
                        clearRetryFlag(question);
                        completeQuestion(question);
                        selected.button.classList.add('is-correct');
                        showFeedback(true, question.explanation ? `Benar. ${question.explanation}` : 'Benar. Lanjut ke soal berikutnya.');

                        window.setTimeout(() => {
                            locked = false;
                            goNext();
                        }, 950);

                        return;
                    }

                    selected.button.classList.add('is-wrong');
                    const canContinue = registerWrongAttempt(question, selected.text);

                    if (!canContinue) {
                        return;
                    }

                    const message = isNoLifeQuestion(question)
                        ? 'Belum tepat. Coba pahami lagi ceritanya.'
                        : 'Belum tepat. Soal ini akan muncul lagi nanti.';

                    showFeedback(false, message);

                    window.setTimeout(() => {
                        locked = false;
                        goNext();
                    }, 900);
                }, 2000);
            });
        };

        const recordAdImpression = (ad) => {
            if (!adOverlay || !adSettings.enabled || !ad?.placement) {
                return;
            }

            fetch(adOverlay.dataset.impressionUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': adOverlay.dataset.csrf || '',
                },
                body: JSON.stringify({
                    ad_id: ad.id || null,
                    placement: ad.placement,
                    context_type: 'learning_level',
                    context_id: Number(quizEngine.dataset.levelId || 0) || null,
                }),
            }).catch(() => {});
        };

        const showAd = (ad, onDone) => {
            if (!adSettings.enabled || !adOverlay || !ad) {
                onDone?.();
                return;
            }

            window.clearInterval(activeAdTimer);

            const duration = Math.max(Number(ad.duration) || 15, 15);
            let remaining = duration;
            let completed = false;

            adTitle.textContent = ad.title || 'Iklan singkat';
            adDescription.textContent = ad.description || 'Tunggu sampai hitungan selesai untuk melanjutkan.';
            adCountdown.textContent = `${remaining} detik`;
            adContinue.disabled = true;
            adContinue.textContent = 'Tunggu';

            if (ad.videoUrl) {
                adVideo.hidden = false;
                adFallback.hidden = true;
                adVideo.src = ad.videoUrl;
                adVideo.currentTime = 0;
                adVideo.play().catch(() => {});
            } else {
                adVideo.removeAttribute('src');
                adVideo.hidden = true;
                adFallback.hidden = false;
            }

            adOverlay.hidden = false;
            recordAdImpression(ad);

            const closeAd = () => {
                if (completed || remaining > 0) {
                    return;
                }

                completed = true;
                window.clearInterval(activeAdTimer);
                adVideo.pause();
                adOverlay.hidden = true;
                onDone?.();
            };

            adContinue.onclick = closeAd;

            activeAdTimer = window.setInterval(() => {
                remaining -= 1;

                if (remaining <= 0) {
                    window.clearInterval(activeAdTimer);
                    adCountdown.textContent = 'Iklan selesai';
                    adContinue.disabled = false;
                    adContinue.textContent = 'Lanjut';
                    return;
                }

                adCountdown.textContent = `${remaining} detik`;
            }, 1000);
        };

        const updateHeader = () => {
            const question = questionQueue[index];
            const progress = initialQuestionTotal === 0 ? 0 : (completedQuestionIds.size / initialQuestionTotal) * 100;

            currentNumber.textContent = Math.min(completedQuestionIds.size + 1, initialQuestionTotal || 0);
            totalNumber.textContent = initialQuestionTotal;
            questionTitle.textContent = question?.instruction || 'Jawab pertanyaan berikut.';
            questionType.textContent = question?.typeLabel || 'Soal';
            correctCountNode.textContent = correctCount;
            progressBar.style.width = `${progress}%`;
            updateLives(question);
        };

        const resetFeedback = () => {
            questionCard.classList.remove('is-correct', 'is-wrong');
            feedback.className = 'quiz-feedback';
            feedback.textContent = '';
        };

        const showFeedback = (isCorrect, message) => {
            questionCard.classList.remove('is-correct', 'is-wrong');
            questionCard.classList.add(isCorrect ? 'is-correct' : 'is-wrong');
            feedback.className = `quiz-feedback is-visible ${isCorrect ? 'correct' : 'wrong'}`;
            feedback.textContent = message;
        };

        const finishQuiz = () => {
            resetFeedback();

            const revealFinishPanel = () => {
                questionBody.innerHTML = `
                    <div class="quiz-context-box">
                        <strong>Ringkasan</strong>
                        <p>Kamu menuntaskan ${correctCount} dari ${initialQuestionTotal} materi. Soal yang sempat salah sudah diulang sebelum level dinyatakan selesai.</p>
                    </div>
                `;
                questionTitle.textContent = 'Semua soal selesai';
                questionType.textContent = 'Selesai';
                currentNumber.textContent = initialQuestionTotal;
                totalNumber.textContent = initialQuestionTotal;
                progressBar.style.width = '100%';
                studySecondsInput.value = Math.max(0, Math.round((Date.now() - startedAt) / 1000));
                correctInput.value = correctCount;
                totalInput.value = initialQuestionTotal;
                syncQuestionResults();
                finishPanel.hidden = false;
                finishPanel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            };

            if (adSettings.enabled && adSettings.exit && !exitAdShown) {
                exitAdShown = true;
                showAd(adSettings.exit, revealFinishPanel);
                return;
            }

            revealFinishPanel();
        };

        const goNext = () => {
            index += 1;

            if (index >= questionQueue.length) {
                finishQuiz();
                return;
            }

            renderQuestion();
        };

        const answerQuestion = (isCorrect, explanation = '', wrongButton = null) => {
            if (locked) {
                return;
            }

            const activeQuestion = questionQueue[index];
            locked = true;

            if (isCorrect) {
                recordQuestionResult(activeQuestion, true, selectedAnswerText(wrongButton));
                syncQuestionResults();
                clearRetryFlag(activeQuestion);
                completeQuestion(activeQuestion);

                const message = explanation
                    ? `Benar. ${explanation}`
                    : 'Benar. Lanjut ke soal berikutnya.';

                showFeedback(true, message);

                window.setTimeout(() => {
                    locked = false;
                    goNext();
                }, 650);

                return;
            }

            const canContinue = registerWrongAttempt(activeQuestion, selectedAnswerText(wrongButton));

            if (!canContinue) {
                return;
            }

            const message = isNoLifeQuestion(activeQuestion)
                ? 'Belum tepat. Baca atau dengarkan ulang, lalu coba lagi.'
                : 'Belum tepat. Soal ini akan muncul lagi nanti.';

            showFeedback(false, message);

            window.setTimeout(() => {
                locked = false;
                if (isNoLifeQuestion(activeQuestion)) {
                    renderQuestion();
                    return;
                }

                goNext();
            }, 900);
        };

        const renderOptions = (question) => {
            if (!question.options || question.options.length === 0) {
                return '<p class="quiz-prompt">Belum ada opsi jawaban untuk soal ini.</p>';
            }

            const options = question.type === 'word_match' ? question.options : shuffle(question.options);

            return `
                <div class="interactive-options">
                    ${options.map((option) => `
                        <button class="answer-option" type="button" data-answer-option data-correct="${option.isCorrect ? 'true' : 'false'}">
                            <span>${escapeHtml(option.text)}</span>
                            ${option.imageUrl ? `<img src="${option.imageUrl}" alt="Gambar opsi">` : ''}
                            ${option.audioUrl ? `<audio controls src="${option.audioUrl}"></audio>` : ''}
                        </button>
                    `).join('')}
                </div>
            `;
        };

        const attachOptionEvents = (question) => {
            questionBody.querySelectorAll('[data-answer-option]').forEach((button) => {
                button.addEventListener('click', () => {
                    if (locked) {
                        return;
                    }

                    const isCorrect = button.dataset.correct === 'true';

                    if (isCorrect) {
                        questionBody.querySelectorAll('[data-answer-option]').forEach((item) => {
                            item.disabled = true;
                        });
                        button.classList.add('is-correct');
                    } else {
                        button.disabled = true;
                        button.classList.add('is-wrong');
                    }

                    answerQuestion(isCorrect, question.explanation || '', button);
                });
            });
        };

        const renderSentenceOrderQuestion = (question) => {
            const configuredTokens = question.settings?.sentenceTokens || [];
            const fallbackTokens = question.correctAnswer
                ? String(question.correctAnswer).split(/\s+/).filter(Boolean).map((text, tokenIndex) => ({ id: tokenIndex + 1, text }))
                : (question.options || []).map((option, tokenIndex) => ({ id: tokenIndex + 1, text: option.text }));

            const tokens = configuredTokens.length > 0 ? configuredTokens : fallbackTokens;
            const correctSentence = question.settings?.correctSentence || question.correctAnswer || tokens.map((token) => token.text).join(' ');

            if (tokens.length === 0) {
                renderStandardQuestion(question);
                return;
            }

            const shuffledTokens = shuffle(tokens).map((token, tokenIndex) => ({
                ...token,
                tokenKey: `${token.id || tokenIndex}-${tokenIndex}`,
            }));

            questionBody.innerHTML = `
                <div class="study-shell">
                    <p class="quiz-prompt">${escapeHtml(question.instruction)}</p>
                    ${renderLearningFocus(question)}
                    <div class="study-question-copy">
                        <span class="answer-zone-label">Pertanyaan</span>
                        <h4 class="quiz-question-text">${escapeHtml(question.questionText)}</h4>
                    </div>

                    <div class="answer-workbench" data-sentence-workbench>
                        <div class="answer-zone">
                            <div class="answer-zone-head">
                                <span class="answer-zone-label">Kolom jawaban</span>
                                <button type="button" class="answer-clear" data-clear-sentence hidden>Ulang susunan</button>
                            </div>
                            <div class="answer-slot sentence-answer-row" data-sentence-slot>
                                <span data-empty-sentence>Pilih kata dari bawah.</span>
                            </div>
                        </div>

                        <div class="answer-option-bank">
                            ${shuffledTokens.map((token) => `
                                <button type="button" class="answer-token" data-sentence-token data-token-key="${escapeHtml(token.tokenKey)}">
                                    <span>${escapeHtml(token.text)}</span>
                                    ${token.audioUrl ? `<small data-play-audio="${escapeHtml(token.audioUrl)}">Dengar</small>` : ''}
                                </button>
                            `).join('')}
                        </div>

                        <div class="answer-action-bar">
                            <button type="button" class="skip-question-button" data-skip-sentence>Lompati</button>
                            <button type="button" class="check-answer-button" data-check-sentence disabled>Periksa</button>
                        </div>
                    </div>
                </div>
            `;

            attachAudioTriggers(questionBody);

            const slot = questionBody.querySelector('[data-sentence-slot]');
            const emptyText = questionBody.querySelector('[data-empty-sentence]');
            const clearButton = questionBody.querySelector('[data-clear-sentence]');
            const checkButton = questionBody.querySelector('[data-check-sentence]');
            const skipButton = questionBody.querySelector('[data-skip-sentence]');
            const tokenButtons = Array.from(questionBody.querySelectorAll('[data-sentence-token]'));
            const selectedTokens = [];

            const syncSentenceState = () => {
                emptyText.hidden = selectedTokens.length > 0;
                clearButton.hidden = selectedTokens.length === 0;
                checkButton.disabled = selectedTokens.length === 0;
                checkButton.textContent = 'Periksa';
            };

            const returnToken = (selectedToken) => {
                const indexToRemove = selectedTokens.findIndex((item) => item.key === selectedToken.key);

                if (indexToRemove >= 0) {
                    selectedTokens.splice(indexToRemove, 1);
                }

                selectedToken.button.hidden = false;
                selectedToken.node.remove();
                syncSentenceState();
            };

            tokenButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    if (locked) {
                        return;
                    }

                    const key = button.dataset.tokenKey;
                    const text = button.querySelector('span')?.textContent.trim() || button.textContent.trim();
                    const chip = document.createElement('button');
                    chip.type = 'button';
                    chip.className = 'sentence-selected-token';
                    chip.textContent = text;

                    const selectedToken = { key, text, button, node: chip };
                    selectedTokens.push(selectedToken);
                    button.hidden = true;
                    slot.appendChild(chip);
                    syncSentenceState();

                    chip.addEventListener('click', () => {
                        if (!locked) {
                            returnToken(selectedToken);
                        }
                    });
                });
            });

            clearButton.addEventListener('click', () => {
                if (locked) {
                    return;
                }

                [...selectedTokens].forEach(returnToken);
            });

            skipButton.addEventListener('click', () => {
                if (locked) {
                    return;
                }

                locked = true;
                const canContinue = registerWrongAttempt(question, 'skipped');

                if (!canContinue) {
                    return;
                }

                showFeedback(false, 'Soal susun kalimat ini akan muncul lagi nanti.');
                window.setTimeout(() => {
                    locked = false;
                    goNext();
                }, 900);
            });

            checkButton.addEventListener('click', () => {
                if (locked || selectedTokens.length === 0) {
                    return;
                }

                locked = true;
                const selectedSentence = selectedTokens.map((token) => token.text).join(' ');
                const isCorrect = normalizeAnswer(selectedSentence) === normalizeAnswer(correctSentence);

                if (isCorrect) {
                    recordQuestionResult(question, true, selectedSentence);
                    syncQuestionResults();
                    clearRetryFlag(question);
                    completeQuestion(question);
                    showFeedback(true, question.explanation ? `Benar. ${question.explanation}` : 'Benar. Lanjut ke soal berikutnya.');

                    window.setTimeout(() => {
                        locked = false;
                        goNext();
                    }, 900);

                    return;
                }

                const canContinue = registerWrongAttempt(question, selectedSentence);

                if (!canContinue) {
                    return;
                }

                showFeedback(false, 'Belum tepat. Soal ini akan muncul lagi nanti.');
                window.setTimeout(() => {
                    locked = false;
                    goNext();
                }, 900);
            });

            syncSentenceState();
        };

        const renderStandardQuestion = (question) => {
            const context = question.settings?.scenarioContext
                ? `<div class="quiz-context-box"><strong>Konteks</strong><p>${escapeHtml(question.settings.scenarioContext)}</p></div>`
                : '';

            const mixNote = question.settings?.mixNote
                ? `<div class="quiz-context-box"><strong>Catatan</strong><p>${escapeHtml(question.settings.mixNote)}</p></div>`
                : '';

            questionBody.innerHTML = `
                <div class="study-shell">
                    <p class="quiz-prompt">${escapeHtml(question.instruction)}</p>
                    ${context}
                    ${renderLearningFocus(question)}
                    ${question.audioUrl ? `<audio class="quiz-audio" controls src="${question.audioUrl}"></audio>` : ''}
                    ${question.imageUrl ? `<img class="quiz-image" src="${question.imageUrl}" alt="Gambar soal">` : ''}
                    <div class="study-question-copy">
                        <span class="answer-zone-label">Pertanyaan</span>
                        <h4 class="quiz-question-text">${escapeHtml(question.questionText)}</h4>
                    </div>
                    ${mixNote}
                    ${renderAnswerWorkspace(question)}
                </div>
            `;

            attachAudioTriggers(questionBody);
            attachAnswerWorkspaceEvents(question);
        };

        const playHiddenAudio = (audioElement, onEnded, onBlocked) => {
            if (!audioElement || !audioElement.src) {
                onEnded?.();
                return;
            }

            const done = () => {
                audioElement.removeEventListener('ended', done);
                onEnded?.();
            };

            audioElement.currentTime = 0;
            audioElement.addEventListener('ended', done, { once: true });
            audioElement.play().catch(() => {
                audioElement.removeEventListener('ended', done);
                onBlocked?.();
            });
        };

        const renderListeningQuestion = (question) => {
            const configuredTokens = question.settings?.sentenceTokens || [];
            const fallbackTokens = question.correctAnswer
                ? String(question.correctAnswer).split(/\s+/).filter(Boolean).map((text, tokenIndex) => ({ id: tokenIndex + 1, text }))
                : (question.options || []).map((option, tokenIndex) => ({ id: tokenIndex + 1, text: option.text }));
            const tokens = configuredTokens.length > 0 ? configuredTokens : fallbackTokens;
            const correctSentence = question.settings?.correctSentence || question.correctAnswer || tokens.map((token) => token.text).join(' ');
            const audioUrl = question.settings?.questionAudioUrl || question.audioUrl || '';

            if (tokens.length === 0) {
                renderStandardQuestion(question);
                return;
            }

            const shuffledTokens = shuffle(tokens).map((token, tokenIndex) => ({
                ...token,
                tokenKey: `${token.id || tokenIndex}-${tokenIndex}`,
            }));

            questionBody.innerHTML = `
                <div class="simple-listening-stage">
                    <p class="quiz-prompt">${escapeHtml(question.instruction)}</p>
                    <div class="listening-mic-area">
                        <button type="button" class="listening-mic-button" data-play-listening aria-label="Putar audio listening">MIC</button>
                        <p class="listening-muted-status" data-listening-status>${audioUrl ? 'Klik mikrofon untuk mendengar kalimat.' : 'Audio belum tersedia. Susun kalimat dari token yang tersedia.'}</p>
                    </div>
                    <div class="listening-order-workbench" data-listening-workbench ${audioUrl ? 'hidden' : ''}>
                        <div class="study-question-copy">
                            <span class="answer-zone-label">Listening</span>
                            <h4 class="quiz-question-text">${escapeHtml(question.questionText || 'Susun kalimat yang kamu dengar.')}</h4>
                        </div>
                        <div class="answer-zone">
                            <div class="answer-zone-head">
                                <span class="answer-zone-label">Kolom jawaban</span>
                                <button type="button" class="answer-clear" data-clear-listening hidden>Ulang susunan</button>
                            </div>
                            <div class="answer-slot sentence-answer-row" data-listening-slot>
                                <span data-empty-listening>Pilih kata dari bawah.</span>
                            </div>
                        </div>
                        <div class="answer-option-bank">
                            ${shuffledTokens.map((token) => `
                                <button type="button" class="answer-token" data-listening-token data-token-key="${escapeHtml(token.tokenKey)}">
                                    <span>${escapeHtml(token.text)}</span>
                                    ${token.audioUrl ? `<small data-play-audio="${escapeHtml(token.audioUrl)}">Dengar</small>` : ''}
                                </button>
                            `).join('')}
                        </div>
                        <div class="answer-action-bar">
                            <button type="button" class="story-next-button" data-replay-listening ${audioUrl ? '' : 'hidden'}>Putar Ulang</button>
                            <button type="button" class="check-answer-button" data-check-listening disabled>Periksa</button>
                        </div>
                    </div>
                    <audio class="hidden-audio" data-listening-audio></audio>
                </div>
            `;

            attachAudioTriggers(questionBody);

            const playButton = questionBody.querySelector('[data-play-listening]');
            const status = questionBody.querySelector('[data-listening-status]');
            const workbench = questionBody.querySelector('[data-listening-workbench]');
            const audio = questionBody.querySelector('[data-listening-audio]');
            const slot = questionBody.querySelector('[data-listening-slot]');
            const emptyText = questionBody.querySelector('[data-empty-listening]');
            const clearButton = questionBody.querySelector('[data-clear-listening]');
            const checkButton = questionBody.querySelector('[data-check-listening]');
            const replayButton = questionBody.querySelector('[data-replay-listening]');
            const tokenButtons = Array.from(questionBody.querySelectorAll('[data-listening-token]'));
            const selectedTokens = [];

            const revealWorkbench = () => {
                workbench.hidden = false;
                status.textContent = 'Susun kalimat sesuai audio.';
            };

            const playListeningAudio = () => {
                if (!audioUrl) {
                    revealWorkbench();
                    return;
                }

                playButton.disabled = true;
                status.textContent = 'Memutar audio...';
                audio.src = audioUrl;

                playHiddenAudio(audio, () => {
                    playButton.disabled = false;
                    revealWorkbench();
                }, () => {
                    playButton.disabled = false;
                    revealWorkbench();
                    status.textContent = 'Audio diblokir browser. Kamu tetap bisa menyusun kalimat.';
                });
            };

            const syncListeningState = () => {
                emptyText.hidden = selectedTokens.length > 0;
                clearButton.hidden = selectedTokens.length === 0;
                checkButton.disabled = selectedTokens.length !== tokens.length;
                checkButton.textContent = selectedTokens.length === tokens.length ? 'Periksa' : 'Susun semua kata';
            };

            const returnToken = (selectedToken) => {
                const indexToRemove = selectedTokens.findIndex((item) => item.key === selectedToken.key);

                if (indexToRemove >= 0) {
                    selectedTokens.splice(indexToRemove, 1);
                }

                selectedToken.button.hidden = false;
                selectedToken.node.remove();
                syncListeningState();
            };

            tokenButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    if (locked) {
                        return;
                    }

                    const key = button.dataset.tokenKey;
                    const text = button.textContent.trim();
                    const chip = document.createElement('button');
                    chip.type = 'button';
                    chip.className = 'sentence-selected-token';
                    chip.textContent = text;

                    const selectedToken = { key, text, button, node: chip };
                    selectedTokens.push(selectedToken);
                    button.hidden = true;
                    slot.appendChild(chip);
                    syncListeningState();

                    chip.addEventListener('click', () => {
                        if (!locked) {
                            returnToken(selectedToken);
                        }
                    });
                });
            });

            clearButton.addEventListener('click', () => {
                if (!locked) {
                    [...selectedTokens].forEach(returnToken);
                }
            });

            checkButton.addEventListener('click', () => {
                if (locked || selectedTokens.length !== tokens.length) {
                    return;
                }

                locked = true;
                const selectedSentence = selectedTokens.map((token) => token.text).join(' ');
                const isCorrect = normalizeAnswer(selectedSentence) === normalizeAnswer(correctSentence);

                if (isCorrect) {
                    clearRetryFlag(question);
                    completeQuestion(question);
                    recordQuestionResult(question, true, selectedSentence);
                    syncQuestionResults();
                    showFeedback(true, question.explanation ? `Benar. ${question.explanation}` : 'Benar. Lanjut ke soal berikutnya.');

                    window.setTimeout(() => {
                        locked = false;
                        goNext();
                    }, 900);

                    return;
                }

                showFeedback(false, 'Belum tepat. Dengarkan lagi dan susun ulang.');
                window.setTimeout(() => {
                    locked = false;
                    questionCard.classList.remove('is-wrong');
                    feedback.className = 'quiz-feedback';
                    feedback.textContent = '';
                }, 900);
            });

            playButton.addEventListener('click', playListeningAudio);
            replayButton?.addEventListener('click', playListeningAudio);
            syncListeningState();

            if (!audioUrl) {
                revealWorkbench();
            }
        };

        const renderWordMatchQuestion = (question) => {
            const pairs = question.settings?.wordPairs || [];

            if (pairs.length === 0) {
                renderStandardQuestion(question);
                return;
            }

            const rightItems = shuffle(pairs);

            questionBody.innerHTML = `
                <p class="quiz-prompt">${escapeHtml(question.instruction)}</p>
                <h4 class="quiz-question-text">${escapeHtml(question.questionText)}</h4>
                <div class="word-match-board">
                    <div class="word-match-grid">
                        <div class="match-column">
                            ${pairs.map((pair) => `
                                <button type="button" class="match-tile" data-left-id="${pair.id}" data-audio-url="${escapeHtml(pair.audioUrl || '')}">
                                    <span>${escapeHtml(pair.left)}</span>
                                    ${pair.audioUrl ? '<small>Dengar audio</small>' : ''}
                                </button>
                            `).join('')}
                        </div>
                        <div class="match-column">
                            ${rightItems.map((pair) => `
                                <button type="button" class="match-tile" data-right-id="${pair.id}">
                                    ${escapeHtml(pair.right)}
                                </button>
                            `).join('')}
                        </div>
                    </div>
                </div>
            `;

            let selectedLeft = null;
            let matched = 0;

            const clearWrong = () => {
                questionBody.querySelectorAll('.match-tile.is-wrong').forEach((tile) => {
                    tile.classList.remove('is-wrong');
                });
            };

            questionBody.querySelectorAll('[data-left-id]').forEach((tile) => {
                tile.addEventListener('click', () => {
                    if (tile.classList.contains('is-matched')) {
                        return;
                    }

                    playAudioUrl(tile.dataset.audioUrl || '');
                    clearWrong();
                    questionBody.querySelectorAll('[data-left-id]').forEach((item) => item.classList.remove('is-selected'));
                    selectedLeft = tile;
                    tile.classList.add('is-selected');
                });
            });

            questionBody.querySelectorAll('[data-right-id]').forEach((tile) => {
                tile.addEventListener('click', () => {
                    if (!selectedLeft || tile.classList.contains('is-matched')) {
                        return;
                    }

                    clearWrong();

                    if (selectedLeft.dataset.leftId === tile.dataset.rightId) {
                        selectedLeft.classList.remove('is-selected');
                        selectedLeft.classList.add('is-matched');
                        tile.classList.add('is-matched');
                        selectedLeft.disabled = true;
                        tile.disabled = true;
                        selectedLeft = null;
                        matched += 1;

                        if (matched >= pairs.length) {
                            answerQuestion(true, question.explanation || 'Semua pasangan kata sudah cocok.');
                        }
                    } else {
                        selectedLeft.classList.add('is-wrong');
                        tile.classList.add('is-wrong');
                        locked = true;
                        showFeedback(false, 'Belum cocok. Coba pasangan lain.');
                        window.setTimeout(() => {
                            selectedLeft?.classList.remove('is-selected', 'is-wrong');
                            tile.classList.remove('is-wrong');
                            selectedLeft = null;
                            locked = false;
                            feedback.className = 'quiz-feedback';
                            feedback.textContent = '';
                            questionCard.classList.remove('is-wrong');
                        }, 750);
                    }
                });
            });
        };

        const renderReadingStoryQuestion = (question) => {
            const configuredFlow = question.settings?.storyFlow || [];
            const legacySegments = question.settings?.storySegments || [];
            const legacyQuestions = question.settings?.storyQuestions || [];
            const flow = configuredFlow.length > 0
                ? configuredFlow
                : [
                    ...legacySegments.map((segment, segmentIndex) => ({
                        type: 'dialogue',
                        speaker: segment.speaker || `Tokoh ${segmentIndex + 1}`,
                        side: segment.side || (segmentIndex % 2 === 0 ? 'left' : 'right'),
                        text: segment.text || '',
                        audioUrl: segment.audioUrl || '',
                    })),
                    ...(legacyQuestions.length > 0 ? legacyQuestions : [{
                        id: 1,
                        questionText: question.questionText,
                        options: question.options || [],
                        explanation: question.explanation || '',
                    }]).map((storyQuestion) => ({
                        type: 'question',
                        questionText: storyQuestion.questionText || '',
                        options: storyQuestion.options || [],
                        explanation: storyQuestion.explanation || '',
                    })),
                ];

            if (flow.length === 0) {
                renderStandardQuestion(question);
                return;
            }

            questionBody.innerHTML = `
                <div class="reading-story-stage">
                    <div class="reading-start" data-reading-start>
                        <p class="quiz-prompt">${escapeHtml(question.instruction)}</p>
                        <h4 class="quiz-question-text">${escapeHtml(question.questionText || 'Reading Story')}</h4>
                        <button type="button" class="story-action-button" data-start-story>${escapeHtml(question.settings?.storyButtonLabel || 'Mulai Reading')}</button>
                    </div>
                    <div class="reading-flow" data-reading-flow hidden></div>
                    <audio class="hidden-audio" data-story-audio></audio>
                </div>
            `;

            questionBody.classList.add('is-reading-flow');
            quizEngine.classList.add('is-reading-story-mode');
            const startPanel = questionBody.querySelector('[data-reading-start]');
            const flowWrap = questionBody.querySelector('[data-reading-flow]');
            const storyAudio = questionBody.querySelector('[data-story-audio]');
            const startButton = questionBody.querySelector('[data-start-story]');
            let flowIndex = 0;
            let storyCorrectCount = 0;

            const scrollToLatest = () => {
                const latest = flowWrap.lastElementChild;

                if (latest) {
                    latest.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            };

            const completeReadingStory = () => {
                clearRetryFlag(question);
                completeQuestion(question);
                recordQuestionResult(question, true, `reading_story_completed:${storyCorrectCount}`);
                syncQuestionResults();
                showFeedback(true, question.explanation ? `Reading selesai. ${question.explanation}` : 'Reading selesai. Kamu memahami dialog ini.');

                window.setTimeout(() => {
                    locked = false;
                    goNext();
                }, 1000);
            };

            const continueFlow = (delay = 1000) => {
                window.setTimeout(() => {
                    flowIndex += 1;
                    playFlowItem();
                }, delay);
            };

            const showQuestionItem = (flowItem) => {
                locked = false;
                const options = (flowItem.options || []).length > 0 ? flowItem.options : (question.options || []);
                const block = document.createElement('div');
                block.className = 'reading-question-inline';
                block.innerHTML = `
                    <h4>${escapeHtml(flowItem.questionText || question.questionText || '')}</h4>
                    <div class="reading-answer-grid">
                        ${options.map((option, optionIndex) => `
                            <button
                                type="button"
                                class="reading-answer-button"
                                data-reading-answer
                                data-correct="${option.isCorrect ? 'true' : 'false'}"
                            >
                                ${escapeHtml(option.text || `Jawaban ${optionIndex + 1}`)}
                            </button>
                        `).join('')}
                    </div>
                    <p class="reading-inline-status" data-reading-question-status></p>
                `;

                flowWrap.appendChild(block);
                scrollToLatest();

                const status = block.querySelector('[data-reading-question-status]');
                block.querySelectorAll('[data-reading-answer]').forEach((button) => {
                    button.addEventListener('click', () => {
                        if (locked) {
                            return;
                        }

                        const isCorrect = button.dataset.correct === 'true';
                        const selectedText = selectedAnswerText(button);

                        if (isCorrect) {
                            locked = true;
                            storyCorrectCount += 1;
                            button.classList.add('is-correct');
                            block.querySelectorAll('[data-reading-answer]').forEach((item) => {
                                item.disabled = true;
                            });
                            status.textContent = flowItem.explanation ? `Benar. ${flowItem.explanation}` : 'Benar. Lanjut.';
                            window.setTimeout(() => {
                                continueFlow(0);
                            }, 1000);

                            return;
                        }

                        button.classList.add('is-wrong');
                        status.textContent = 'Belum tepat. Coba pahami dialognya lagi.';

                        window.setTimeout(() => {
                            locked = false;
                            button.classList.remove('is-wrong');
                            questionCard.classList.remove('is-wrong');
                            feedback.className = 'quiz-feedback';
                            feedback.textContent = '';
                        }, 650);
                    });
                });
            };

            const showDialogueItem = (flowItem) => {
                const line = document.createElement('div');
                const side = flowItem.side === 'right' ? 'right' : 'left';
                line.className = `reading-line is-${side}`;
                line.innerHTML = `
                    <span class="reading-line-speaker">${escapeHtml(flowItem.speaker || (side === 'right' ? 'Tokoh B' : 'Tokoh A'))}</span>
                    <p class="reading-line-text">${escapeHtml(flowItem.text || '')}</p>
                `;

                flowWrap.appendChild(line);
                scrollToLatest();

                const next = () => continueFlow(1000);

                if (!flowItem.audioUrl) {
                    continueFlow(1000);
                    return;
                }

                storyAudio.src = flowItem.audioUrl;
                playHiddenAudio(storyAudio, next, next);
            };

            function playFlowItem() {
                if (flowIndex >= flow.length) {
                    completeReadingStory();
                    return;
                }

                const flowItem = flow[flowIndex];

                if (flowItem.type === 'question') {
                    showQuestionItem(flowItem);
                    return;
                }

                showDialogueItem(flowItem);
            }

            startButton.addEventListener('click', () => {
                startPanel.hidden = true;
                flowWrap.hidden = false;

                const prep = document.createElement('p');
                prep.className = 'reading-line-text';
                prep.textContent = 'Mulai';
                flowWrap.appendChild(prep);

                window.setTimeout(() => {
                    prep.remove();
                    flowIndex = 0;
                    playFlowItem();
                }, 900);
            });
        };

        const renderQuestion = () => {
            resetFeedback();
            locked = false;
            questionBody.classList.remove('is-reading-flow');
            quizEngine.classList.remove('is-reading-story-mode');
            finishPanel.hidden = true;
            const question = questionQueue[index];

            if (question?.isRetry) {
                clearRetryFlag(question);
            }

            updateHeader();

            if (!question) {
                finishQuiz();
                return;
            }

            if (question.type === 'listening') {
                renderListeningQuestion(question);
                return;
            }

            if (question.type === 'word_match') {
                renderWordMatchQuestion(question);
                return;
            }

            if (question.type === 'sentence_order') {
                renderSentenceOrderQuestion(question);
                return;
            }

            if (question.type === 'reading_story') {
                renderReadingStoryQuestion(question);
                return;
            }

            renderStandardQuestion(question);
        };

        completeForm.addEventListener('submit', () => {
            studySecondsInput.value = Math.max(0, Math.round((Date.now() - startedAt) / 1000));
            correctInput.value = correctCount;
            totalInput.value = initialQuestionTotal;
            syncQuestionResults();
        });

        if (adSettings.enabled && adSettings.entry) {
            showAd(adSettings.entry, renderQuestion);
            return;
        }

        renderQuestion();
    })();
</script>
@endpush
