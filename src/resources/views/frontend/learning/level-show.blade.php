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
            $audioPath = $segment['audio_path'] ?? null;

            return [
                'text' => $segment['text'] ?? '',
                'audioUrl' => learningAudioUrl($audioPath),
            ];
        })->values();

        $listeningFlow = collect($settings['listening_flow'] ?? [])->map(function ($item, $itemIndex) {
            $type = $item['type'] ?? ($item['item_type'] ?? 'story');
            $data = $item['data'] ?? $item;

            if ($type === 'question') {
                $questionAudioPath = $data['question_audio_manual_path'] ?? ($data['question_audio_path'] ?? null);
                $options = collect($data['options'] ?? [])->map(function ($option, $optionIndex) {
                    return [
                        'id' => $optionIndex + 1,
                        'text' => $option['text'] ?? '',
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
                    return [
                        'id' => $optionIndex + 1,
                        'text' => $option['text'] ?? '',
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

        return [
            'id' => $question->id,
            'type' => $question->type,
            'typeLabel' => \App\Models\LearningLevel::TYPES[$question->type] ?? str($question->type)->headline()->toString(),
            'instruction' => $question->instruction ?: 'Jawab pertanyaan berikut.',
            'questionText' => $question->question_text,
            'audioUrl' => learningAudioUrl($question->audio_path),
            'imageUrl' => $question->image_path ? asset('storage/' . $question->image_path) : null,
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
                'storySegments' => $storySegments,
                'listeningFlow' => $listeningFlow,
                'wordPairs' => $wordPairs,
                'videoUrl' => learningAudioUrl($settings['video_path'] ?? null) ?: ($settings['video_url'] ?? null),
                'videoTranscript' => $settings['video_transcript'] ?? null,
                'mustWatchSeconds' => (int) ($settings['must_watch_seconds'] ?? 5),
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

                        <div class="quiz-score-pill">
                            <span data-correct-count>0</span> benar
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
        max-width: 920px;
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

    .quiz-score-pill {
        border: 1px solid rgba(102, 232, 247, 0.22);
        border-radius: 999px;
        background: rgba(102, 232, 247, 0.08);
        color: #eaffff;
        padding: 0.55rem 0.8rem;
        font-weight: 950;
        white-space: nowrap;
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
        min-height: 420px;
        padding: clamp(1rem, 3vw, 1.5rem);
        padding-bottom: 5rem;
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
        let locked = false;
        let exitAdShown = false;
        let activeAdTimer = null;
        const questionResults = new Map();

        totalNumber.textContent = questions.length;
        totalInput.value = questions.length;

        const escapeHtml = (value) => {
            return String(value || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        };

        const youtubeEmbedUrl = (url) => {
            if (!url) {
                return null;
            }

            try {
                const parsed = new URL(url);
                const host = parsed.hostname.replace(/^www\./, '');
                let videoId = null;

                if (host === 'youtu.be') {
                    videoId = parsed.pathname.split('/').filter(Boolean)[0];
                }

                if (host === 'youtube.com' || host === 'm.youtube.com') {
                    videoId = parsed.searchParams.get('v');

                    if (!videoId && parsed.pathname.startsWith('/shorts/')) {
                        videoId = parsed.pathname.split('/').filter(Boolean)[1];
                    }

                    if (!videoId && parsed.pathname.startsWith('/embed/')) {
                        videoId = parsed.pathname.split('/').filter(Boolean)[1];
                    }
                }

                return videoId ? `https://www.youtube.com/embed/${encodeURIComponent(videoId)}` : null;
            } catch (error) {
                return null;
            }
        };

        const shuffle = (items) => {
            return [...items].sort(() => Math.random() - 0.5);
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
            const question = questions[index];
            const progress = questions.length === 0 ? 0 : (index / questions.length) * 100;

            currentNumber.textContent = Math.min(index + 1, questions.length);
            questionTitle.textContent = question?.instruction || 'Jawab pertanyaan berikut.';
            questionType.textContent = question?.typeLabel || 'Soal';
            correctCountNode.textContent = correctCount;
            progressBar.style.width = `${progress}%`;
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
                        <p>Kamu menjawab ${correctCount} dari ${questions.length} soal dengan benar. Durasi belajar dihitung dari waktu kamu berada di halaman level ini.</p>
                    </div>
                `;
                questionTitle.textContent = 'Semua soal selesai';
                questionType.textContent = 'Selesai';
                currentNumber.textContent = questions.length;
                progressBar.style.width = '100%';
                studySecondsInput.value = Math.max(0, Math.round((Date.now() - startedAt) / 1000));
                correctInput.value = correctCount;
                totalInput.value = questions.length;
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

            if (index >= questions.length) {
                finishQuiz();
                return;
            }

            renderQuestion();
        };

        const answerQuestion = (isCorrect, explanation = '', wrongButton = null) => {
            if (locked) {
                return;
            }

            recordQuestionResult(questions[index], isCorrect, selectedAnswerText(wrongButton));
            syncQuestionResults();
            locked = true;

            if (isCorrect) {
                correctCount += 1;
                correctCountNode.textContent = correctCount;
                correctInput.value = correctCount;

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

            const message = explanation
                ? `Belum tepat. ${explanation}`
                : 'Belum tepat. Coba lagi sampai benar.';

            showFeedback(false, message);

            window.setTimeout(() => {
                locked = false;
                questionCard.classList.remove('is-wrong');
                feedback.className = 'quiz-feedback';
                feedback.textContent = '';

                if (wrongButton) {
                    wrongButton.classList.remove('is-wrong');
                    wrongButton.disabled = false;
                }

                questionBody.querySelectorAll('[data-answer-option]').forEach((button) => {
                    button.disabled = false;
                });
            }, 900);
        };

        const renderOptions = (question) => {
            if (!question.options || question.options.length === 0) {
                return '<p class="quiz-prompt">Belum ada opsi jawaban untuk soal ini.</p>';
            }

            const optionLabels = ['A', 'B', 'C', 'D', 'E', 'F'];
            const options = question.type === 'word_match' ? question.options : shuffle(question.options);

            return `
                <div class="interactive-options">
                    ${options.map((option, optionIndex) => `
                        <button class="answer-option" type="button" data-answer-option data-correct="${option.isCorrect ? 'true' : 'false'}">
                            <span><strong style="margin-right:.45rem;">${optionLabels[optionIndex] ?? optionIndex + 1}.</strong> ${escapeHtml(option.text)}</span>
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

                            if (item.dataset.correct === 'true') {
                                item.classList.add('is-correct');
                            }
                        });
                    } else {
                        button.disabled = true;
                        button.classList.add('is-wrong');
                    }

                    answerQuestion(isCorrect, question.explanation || '', button);
                });
            });
        };

        const renderVideoQuestion = (question) => {
            const videoUrl = question.settings?.videoUrl || '';
            const embedUrl = youtubeEmbedUrl(videoUrl);
            const mustWatchSeconds = Math.max(Number(question.settings?.mustWatchSeconds || 0), 0);
            const videoMarkup = embedUrl
                ? `<iframe src="${escapeHtml(embedUrl)}" title="Video question" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>`
                : (videoUrl
                    ? `<video controls playsinline src="${escapeHtml(videoUrl)}"></video>`
                    : `<div class="quiz-context-box"><strong>Video belum tersedia</strong><p>Admin belum mengisi upload video atau video URL.</p></div>`);

            questionBody.innerHTML = `
                <div class="video-question-panel ${mustWatchSeconds > 0 ? 'is-locked' : ''}" data-video-question-panel>
                    <p class="quiz-prompt">${escapeHtml(question.instruction)}</p>
                    <div class="video-frame">${videoMarkup}</div>
                    <p class="video-question-status" data-video-status>
                        ${mustWatchSeconds > 0 ? `Pilihan jawaban terbuka dalam ${mustWatchSeconds} detik.` : 'Jawab pertanyaan setelah menonton video.'}
                    </p>
                    ${question.settings?.videoTranscript ? `<div class="quiz-context-box"><strong>Catatan Video</strong><p>${escapeHtml(question.settings.videoTranscript)}</p></div>` : ''}
                    <h4 class="quiz-question-text">${escapeHtml(question.questionText)}</h4>
                    <div data-video-options>
                        ${renderOptions(question)}
                    </div>
                </div>
            `;

            attachOptionEvents(question);

            if (mustWatchSeconds <= 0) {
                return;
            }

            locked = true;
            const panel = questionBody.querySelector('[data-video-question-panel]');
            const status = questionBody.querySelector('[data-video-status]');
            let remaining = mustWatchSeconds;

            const timer = window.setInterval(() => {
                remaining -= 1;

                if (remaining <= 0) {
                    window.clearInterval(timer);
                    locked = false;
                    panel?.classList.remove('is-locked');
                    if (status) {
                        status.textContent = 'Pilihan jawaban sudah terbuka.';
                    }
                    return;
                }

                if (status) {
                    status.textContent = `Pilihan jawaban terbuka dalam ${remaining} detik.`;
                }
            }, 1000);
        };

        const renderStandardQuestion = (question) => {
            const context = question.settings?.scenarioContext
                ? `<div class="quiz-context-box"><strong>Konteks</strong><p>${escapeHtml(question.settings.scenarioContext)}</p></div>`
                : '';

            const idealResponse = question.settings?.idealResponse
                ? `<div class="quiz-context-box"><strong>Respons Ideal</strong><p>${escapeHtml(question.settings.idealResponse)}</p></div>`
                : '';

            const mixNote = question.settings?.mixNote
                ? `<div class="quiz-context-box"><strong>Catatan</strong><p>${escapeHtml(question.settings.mixNote)}</p></div>`
                : '';

            questionBody.innerHTML = `
                <p class="quiz-prompt">${escapeHtml(question.instruction)}</p>
                ${context}
                ${question.audioUrl ? `<audio class="quiz-audio" controls src="${question.audioUrl}"></audio>` : ''}
                ${question.imageUrl ? `<img class="quiz-image" src="${question.imageUrl}" alt="Gambar soal">` : ''}
                <h4 class="quiz-question-text">${escapeHtml(question.questionText)}</h4>
                ${idealResponse}
                ${mixNote}
                ${renderOptions(question)}
            `;

            attachOptionEvents(question);
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
            const configuredFlow = question.settings?.listeningFlow || [];
            const fallbackFlow = [];

            if (question.settings?.audioTranscript || question.audioUrl) {
                fallbackFlow.push({
                    type: 'story',
                    storyText: question.settings?.audioTranscript || 'Cerita akan diputar otomatis.',
                    storyAudioUrl: question.audioUrl || '',
                });
            }

            fallbackFlow.push({
                type: 'question',
                questionText: question.questionText || '',
                questionAudioUrl: question.settings?.questionAudioUrl || '',
                explanation: question.explanation || '',
                options: question.options || [],
            });

            const listeningFlow = configuredFlow.length > 0 ? configuredFlow : fallbackFlow;
            const startLabel = question.settings?.storyButtonLabel || 'Mulai';
            let flowIndex = 0;
            let listeningCorrectCounted = false;

            questionBody.innerHTML = `
                <div class="listening-stage simple-listening">
                    <p class="listening-instruction">${escapeHtml(question.instruction)}</p>

                    <button type="button" class="story-action-button" data-start-listening>${escapeHtml(startLabel)}</button>

                    <div class="listening-history" data-listening-history></div>

                    <button type="button" class="listening-manual-button" data-manual-continue hidden>Lanjut</button>

                    <audio class="hidden-audio" data-main-audio></audio>
                    <audio class="hidden-audio" data-question-audio></audio>
                </div>
            `;

            const startButton = questionBody.querySelector('[data-start-listening]');
            const manualButton = questionBody.querySelector('[data-manual-continue]');
            const history = questionBody.querySelector('[data-listening-history]');
            const mainAudio = questionBody.querySelector('[data-main-audio]');
            const questionAudio = questionBody.querySelector('[data-question-audio]');

            const scrollToLatest = () => {
                const latest = history.lastElementChild;

                if (latest) {
                    latest.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            };

            const completeListeningQuestion = () => {
                if (history.querySelector('[data-listening-finish]')) {
                    return;
                }

                const finishBox = document.createElement('div');
                finishBox.className = 'listening-finish-box';
                finishBox.dataset.listeningFinish = 'true';
                finishBox.innerHTML = `
                    <small>Listening selesai</small>
                    <h4>Semua bagian listening sudah selesai.</h4>
                    <p class="listening-muted-status">Tekan tombol selesai untuk lanjut ke soal berikutnya atau menyimpan progress.</p>
                    <button type="button" class="listening-finish-button" data-finish-listening>Selesai</button>
                `;

                history.appendChild(finishBox);
                scrollToLatest();

                finishBox.querySelector('[data-finish-listening]').addEventListener('click', () => {
                    if (!listeningCorrectCounted) {
                        listeningCorrectCounted = true;
                        correctCount += 1;
                        correctCountNode.textContent = correctCount;
                        correctInput.value = correctCount;
                        recordQuestionResult(question, true, 'listening_completed');
                        syncQuestionResults();
                    }

                    goNext();
                });
            };

            const createStoryLine = (flowItem) => {
                const line = document.createElement('div');
                line.className = 'listening-history-card is-story';
                line.innerHTML = `
                    <p class="story-text">${escapeHtml(flowItem.storyText || '')}</p>
                    <p class="listening-muted-status" data-story-status hidden></p>
                `;

                history.appendChild(line);
                scrollToLatest();

                return line;
            };

            const createQuestionBlock = (flowItem) => {
                const block = document.createElement('div');
                block.className = 'listening-history-card is-question listening-question-plain';
                block.innerHTML = `
                    <h4 class="quiz-question-text">${escapeHtml(flowItem.questionText || question.questionText || '')}</h4>
                    <p class="listening-muted-status" data-question-status hidden></p>
                    <div data-listening-options hidden></div>
                `;

                history.appendChild(block);
                scrollToLatest();

                return block;
            };

            const renderFlowOptions = (flowItem, block) => {
                const optionsWrap = block.querySelector('[data-listening-options]');
                const questionStatus = block.querySelector('[data-question-status]');
                const flowQuestionData = {
                    ...question,
                    options: flowItem.options || [],
                };

                optionsWrap.innerHTML = renderOptions(flowQuestionData);
                optionsWrap.hidden = true;

                optionsWrap.querySelectorAll('[data-answer-option]').forEach((button) => {
                    button.addEventListener('click', () => {
                        if (locked) {
                            return;
                        }

                        const isCorrect = button.dataset.correct === 'true';

                        if (isCorrect) {
                            optionsWrap.querySelectorAll('[data-answer-option]').forEach((item) => {
                                item.disabled = true;

                                if (item.dataset.correct === 'true') {
                                    item.classList.add('is-correct');
                                }
                            });

                            locked = true;
                            block.classList.add('is-done');
                            questionStatus.hidden = true;
                            showFeedback(true, flowItem.explanation || question.explanation || 'Benar.');

                            window.setTimeout(() => {
                                locked = false;
                                feedback.className = 'quiz-feedback';
                                feedback.textContent = '';
                                questionCard.classList.remove('is-correct');
                                flowIndex += 1;
                                playFlowItem(1000);
                            }, 1000);

                            return;
                        }

                        button.disabled = true;
                        button.classList.add('is-wrong');
                        questionStatus.textContent = 'Belum tepat. Coba lagi.';
                        questionStatus.hidden = false;
                        showFeedback(false, 'Belum tepat. Coba lagi sampai benar.');

                        window.setTimeout(() => {
                            questionCard.classList.remove('is-wrong');
                            feedback.className = 'quiz-feedback';
                            feedback.textContent = '';
                            button.classList.remove('is-wrong');
                            button.disabled = false;
                        }, 850);
                    });
                });
            };

            const revealOptions = (block) => {
                const optionsWrap = block.querySelector('[data-listening-options]');
                const questionStatus = block.querySelector('[data-question-status]');

                if (questionStatus) {
                    questionStatus.hidden = true;
                }

                window.setTimeout(() => {
                    optionsWrap.hidden = false;
                    scrollToLatest();
                }, 1000);
            };

            const showQuestionItem = (flowItem) => {
                resetFeedback();

                const block = createQuestionBlock(flowItem);
                const questionStatus = block.querySelector('[data-question-status]');
                renderFlowOptions(flowItem, block);

                if (!flowItem.questionAudioUrl) {
                    revealOptions(block);
                    return;
                }

                questionStatus.textContent = 'Dengarkan pertanyaan terlebih dahulu.';
                questionStatus.hidden = false;
                questionAudio.src = flowItem.questionAudioUrl;

                playHiddenAudio(questionAudio, () => {
                    revealOptions(block);
                }, () => {
                    questionStatus.textContent = 'Audio pertanyaan diblokir browser. Tekan lanjut untuk menampilkan pilihan jawaban.';
                    questionStatus.hidden = false;
                    manualButton.hidden = false;
                    manualButton.dataset.mode = 'options';
                    manualButton.onclick = () => {
                        manualButton.hidden = true;
                        revealOptions(block);
                    };
                    scrollToLatest();
                });
            };

            const showStoryItem = (flowItem) => {
                resetFeedback();

                const line = createStoryLine(flowItem);
                const storyStatus = line.querySelector('[data-story-status]');

                const continueToNextItem = () => {
                    storyStatus.hidden = true;
                    flowIndex += 1;
                    playFlowItem(0);
                };

                if (!flowItem.storyAudioUrl) {
                    window.setTimeout(continueToNextItem, 1000);
                    return;
                }

                mainAudio.src = flowItem.storyAudioUrl;

                playHiddenAudio(mainAudio, continueToNextItem, () => {
                    storyStatus.textContent = 'Audio diblokir. Tekan lanjut untuk melanjutkan.';
                    storyStatus.hidden = false;
                    manualButton.hidden = false;
                    manualButton.dataset.mode = 'next-flow';
                    manualButton.onclick = () => {
                        manualButton.hidden = true;
                        continueToNextItem();
                    };
                    scrollToLatest();
                });
            };

            const playFlowItem = (delay = 0) => {
                if (flowIndex >= listeningFlow.length) {
                    completeListeningQuestion();
                    return;
                }

                const flowItem = listeningFlow[flowIndex];
                manualButton.hidden = true;
                manualButton.onclick = null;

                window.setTimeout(() => {
                    if (flowItem.type === 'question') {
                        showQuestionItem(flowItem);
                        return;
                    }

                    showStoryItem(flowItem);
                }, delay);
            };

            startButton.addEventListener('click', () => {
                startButton.hidden = true;

                const prepLine = document.createElement('p');
                prepLine.className = 'listening-muted-status';
                prepLine.textContent = 'Bersiap... cerita akan mulai dalam 2 detik.';
                history.appendChild(prepLine);
                scrollToLatest();

                window.setTimeout(() => {
                    prepLine.remove();
                    playFlowItem(0);
                }, 2000);
            });
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
                                <button type="button" class="match-tile" data-left-id="${pair.id}">
                                    ${escapeHtml(pair.left)}
                                    ${pair.audioUrl ? `<audio controls src="${pair.audioUrl}" style="width:100%;height:30px;margin-top:.45rem"></audio>` : ''}
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
                        window.setTimeout(() => {
                            selectedLeft?.classList.remove('is-wrong');
                            tile.classList.remove('is-wrong');
                        }, 600);
                    }
                });
            });
        };

        const renderReadingStoryQuestion = (question) => {
            const segments = question.settings?.storySegments || [];

            if (segments.length === 0) {
                renderStandardQuestion(question);
                return;
            }

            questionBody.innerHTML = `
                <p class="quiz-prompt">${escapeHtml(question.instruction)}</p>
                <div class="story-panel" data-story-panel>
                    <p class="story-text" data-story-text>Tekan mulai untuk memulai cerita.</p>
                    <div class="story-segment-count" data-story-count>Segmen 0 / ${segments.length}</div>
                    <p class="story-status" data-story-status></p>
                    <button type="button" class="story-action-button" data-start-story>${escapeHtml(question.settings?.storyButtonLabel || 'Mulai')}</button>
                    <button type="button" class="story-next-button" data-next-story hidden>Lanjut</button>
                    <audio class="hidden-audio" data-story-audio></audio>
                </div>
                <div data-reading-question hidden>
                    <h4 class="quiz-question-text">${escapeHtml(question.questionText)}</h4>
                    ${renderOptions(question)}
                </div>
            `;

            const storyText = questionBody.querySelector('[data-story-text]');
            const storyAudio = questionBody.querySelector('[data-story-audio]');
            const storyCount = questionBody.querySelector('[data-story-count]');
            const storyStatus = questionBody.querySelector('[data-story-status]');
            const startButton = questionBody.querySelector('[data-start-story]');
            const nextButton = questionBody.querySelector('[data-next-story]');
            const readingQuestion = questionBody.querySelector('[data-reading-question]');
            let segmentIndex = 0;

            const revealQuestion = () => {
                storyCount.textContent = 'Cerita selesai';
                storyStatus.textContent = 'Sekarang jawab pertanyaan berikut.';
                startButton.hidden = true;
                nextButton.hidden = true;
                readingQuestion.hidden = false;
                attachOptionEvents(question);
            };

            const playSegment = () => {
                if (segmentIndex >= segments.length) {
                    revealQuestion();
                    return;
                }

                const segment = segments[segmentIndex];
                storyText.textContent = segment.text || '';
                storyCount.textContent = `Segmen ${segmentIndex + 1} / ${segments.length}`;
                storyStatus.textContent = 'Dengarkan cerita...';
                startButton.hidden = true;
                nextButton.hidden = true;

                if (segment.audioUrl) {
                    storyAudio.src = segment.audioUrl;
                    playHiddenAudio(storyAudio, () => {
                        segmentIndex += 1;
                        playSegment();
                    }, () => {
                        storyStatus.textContent = 'Audio diblokir browser. Tekan lanjut untuk pindah ke bagian berikutnya.';
                        nextButton.hidden = false;
                    });
                } else {
                    window.setTimeout(() => {
                        segmentIndex += 1;
                        playSegment();
                    }, 1800);
                }
            };

            startButton.addEventListener('click', () => {
                storyStatus.textContent = 'Bersiap...';
                window.setTimeout(() => {
                    segmentIndex = 0;
                    playSegment();
                }, 2000);
            });

            nextButton.addEventListener('click', () => {
                segmentIndex += 1;
                playSegment();
            });
        };

        const renderQuestion = () => {
            resetFeedback();
            locked = false;
            finishPanel.hidden = true;
            const question = questions[index];

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

            if (question.type === 'reading_story') {
                renderReadingStoryQuestion(question);
                return;
            }

            if (question.type === 'video_question') {
                renderVideoQuestion(question);
                return;
            }

            renderStandardQuestion(question);
        };

        completeForm.addEventListener('submit', () => {
            studySecondsInput.value = Math.max(0, Math.round((Date.now() - startedAt) / 1000));
            correctInput.value = correctCount;
            totalInput.value = questions.length;
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
