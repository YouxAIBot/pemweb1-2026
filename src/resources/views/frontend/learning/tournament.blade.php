@extends('layouts.learning')

@section('title', 'Turnamen Cepat - YoLearning')

@section('content')
<div class="tournament-frame">
    <main class="tournament-panel">
        <header class="tournament-topbar">
            <div>
                <h1>Turnamen Cepat</h1>
                <p>{{ $profile->language?->name ?? 'Bahasa' }} • challenge cepat</p>
            </div>
            <a href="{{ route('learning.games') }}" class="logout-link">Turnamen</a>
        </header>

        <section class="tournament-wrap">
            <div class="tournament-hero">
                <small>Turnamen Cepat</small>
                <h2>Jawab cepat, naik peringkat.</h2>
                <p>Mode ringan untuk latihan kompetitif. Ambil 5 soal acak dari bahasa yang sedang kamu pelajari.</p>
            </div>

            @if ($result)
                <div class="tournament-result">
                    <div>
                        <small>Skor terakhir</small>
                        <h3>{{ $result['score'] }}%</h3>
                    </div>
                    <p>{{ $result['correct_count'] }}/{{ $result['total_questions'] }} benar • {{ $result['duration_seconds'] }} detik</p>
                </div>
            @endif

            @if ($questions->isEmpty())
                <div class="tournament-empty">
                    <small>Belum bisa mulai</small>
                    <h3>Soal turnamen belum tersedia</h3>
                    <p>Tambahkan soal dengan pilihan jawaban dari admin panel terlebih dahulu. Turnamen akan mengambil soal acak dari bahasa aktif user.</p>
                </div>
            @else
                <form method="POST" action="{{ route('learning.tournament.submit') }}" class="tournament-sheet" data-tournament-form>
                    @csrf
                    <input type="hidden" name="duration_seconds" value="0" data-duration-input>

                    <div class="tournament-play-head">
                        <div>
                            <small>Soal <b data-current-question>1</b> / <b>{{ $questions->count() }}</b></small>
                            <h3 data-active-question-title>Jawab secepat mungkin.</h3>
                        </div>
                        <div class="tournament-speed-pill"><span data-answered-count>0</span> terjawab</div>
                    </div>

                    <div class="tournament-progress-track">
                        <span data-tournament-progress style="width:0%"></span>
                    </div>

                    @foreach ($questions as $question)
                        <section class="tournament-question" data-tournament-question data-question-index="{{ $loop->index }}" {{ $loop->first ? '' : 'hidden' }}>
                            <input type="hidden" name="question_ids[]" value="{{ $question->id }}">
                            <div class="tournament-question-head">
                                <h3>{{ $question->question_text ?: $question->instruction }}</h3>
                            </div>

                            <div class="tournament-options">
                                @foreach ($question->options as $option)
                                    <label>
                                        <input type="radio" name="answers[{{ $question->id }}]" value="{{ $option->id }}">
                                        <span>{{ $option->option_text }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </section>
                    @endforeach

                    <div class="tournament-action-bar">
                        <button type="button" class="tournament-skip" data-skip-tournament>Lompati</button>
                        <button type="submit" class="tournament-submit" data-submit-tournament hidden>
                            Selesai & Simpan Skor
                        </button>
                    </div>
                </form>
            @endif

            <section class="leaderboard-simple">
                <div class="leaderboard-title">
                    <h3>Leaderboard</h3>
                    @if ($myBest)
                        <span>Best kamu: {{ $myBest->score }}%</span>
                    @endif
                </div>

                <div class="leaderboard-list">
                    @forelse ($leaderboard as $attempt)
                        <div class="leaderboard-row">
                            <span>{{ $loop->iteration }}</span>
                            <strong>{{ $attempt->user?->name ?? 'User' }}</strong>
                            <em>{{ $attempt->score }}%</em>
                        </div>
                    @empty
                        <p class="leaderboard-empty">Belum ada skor. Jadilah yang pertama.</p>
                    @endforelse
                </div>
            </section>
        </section>
    </main>
</div>
@endsection

@push('styles')
<style>
    html,
    body {
        min-height: 100%;
        overflow-y: auto;
    }

    .tournament-frame {
        min-height: 100vh;
        background: #0b0f17;
        color: var(--text);
    }

    html.tournament-focus-active,
    html.tournament-focus-active body {
        height: 100%;
        overflow: hidden;
    }

    .tournament-panel {
        min-height: 100vh;
    }

    .tournament-topbar {
        position: sticky;
        top: 0;
        z-index: 20;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        min-height: 72px;
        padding: 1rem clamp(1rem, 4vw, 2rem);
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        background: rgba(11, 15, 23, 0.94);
        backdrop-filter: blur(14px);
    }

    .tournament-topbar h1 {
        font-size: 1.2rem;
        letter-spacing: -0.04em;
    }

    .tournament-topbar p {
        color: var(--muted);
        font-weight: 800;
        font-size: 0.88rem;
    }

    .tournament-wrap {
        width: min(920px, calc(100% - 2rem));
        margin: 0 auto;
        padding: clamp(1rem, 4vw, 2rem) 0 3rem;
        display: grid;
        gap: 1rem;
    }

    .tournament-hero {
        padding: 0.4rem 0 0.8rem;
    }

    .tournament-hero small,
    .tournament-result small,
    .tournament-empty small {
        color: var(--cyan);
        font-size: 0.76rem;
        font-weight: 950;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    .tournament-hero h2 {
        margin-top: 0.45rem;
        font-size: clamp(1.8rem, 5vw, 3.2rem);
        letter-spacing: -0.07em;
        line-height: 0.98;
    }

    .tournament-hero p {
        color: var(--muted);
        max-width: 620px;
        margin-top: 0.7rem;
        line-height: 1.65;
        font-weight: 700;
    }

    .tournament-result,
    .tournament-empty,
    .leaderboard-simple {
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 22px;
        background: #131924;
        padding: 1rem;
    }

    .tournament-result {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
    }

    .tournament-result h3 {
        font-size: 2.2rem;
        line-height: 1;
        letter-spacing: -0.06em;
    }

    .tournament-result p,
    .tournament-empty p {
        color: var(--muted);
        font-weight: 800;
    }

    .tournament-sheet {
        display: grid;
        gap: 0;
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 22px;
        background: #131924;
        overflow: hidden;
    }

    .tournament-play-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.07);
    }

    .tournament-play-head small {
        color: var(--cyan);
        font-size: 0.74rem;
        font-weight: 950;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    .tournament-play-head h3 {
        margin-top: 0.25rem;
        font-size: clamp(1.2rem, 3vw, 1.8rem);
        letter-spacing: -0.04em;
    }

    .tournament-speed-pill {
        border: 1px solid rgba(102, 232, 247, 0.2);
        border-radius: 999px;
        background: rgba(102, 232, 247, 0.08);
        padding: 0.55rem 0.8rem;
        font-weight: 950;
        white-space: nowrap;
    }

    .tournament-progress-track {
        height: 7px;
        background: rgba(255, 255, 255, 0.07);
        overflow: hidden;
    }

    .tournament-progress-track span {
        display: block;
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, var(--cyan), var(--primary));
        transition: width 0.25s ease;
    }

    .tournament-question {
        padding: 1.05rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.07);
    }

    .tournament-question[hidden] {
        display: none;
    }

    .tournament-question-head {
        display: flex;
        align-items: flex-start;
        gap: 0.8rem;
        margin-bottom: 0.9rem;
    }

    .tournament-question-head > span {
        width: 30px;
        height: 30px;
        flex: 0 0 auto;
        display: grid;
        place-items: center;
        border-radius: 999px;
        background: rgba(102, 232, 247, 0.1);
        color: var(--cyan);
        font-weight: 950;
    }

    .tournament-question h3 {
        font-size: clamp(1.1rem, 3vw, 1.55rem);
        letter-spacing: -0.04em;
        line-height: 1.2;
    }

    .tournament-options {
        display: grid;
        gap: 0.55rem;
    }

    .tournament-options label {
        display: flex;
        align-items: center;
        gap: 0.7rem;
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 16px;
        background: #1d2431;
        padding: 0.85rem 0.9rem;
        cursor: pointer;
        transition: 0.16s ease;
        font-weight: 800;
    }

    .tournament-options label:has(input:checked) {
        border-color: rgba(102, 232, 247, 0.55);
        background: rgba(102, 232, 247, 0.12);
    }

    .tournament-options input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .tournament-action-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    .tournament-submit,
    .tournament-skip {
        min-width: 160px;
        border: 0;
        border-radius: 18px;
        padding: 0.9rem 1.05rem;
        font-weight: 950;
        cursor: pointer;
    }

    .tournament-submit {
        background: linear-gradient(135deg, var(--cyan), var(--primary));
        color: #07101f;
    }

    .tournament-skip {
        border: 1px solid rgba(255, 255, 255, 0.12);
        background: rgba(255, 255, 255, 0.04);
        color: #cbd5e1;
    }

    .tournament-frame.is-playing {
        height: 100dvh;
        min-height: 100dvh;
        overflow: hidden;
    }

    .tournament-frame.is-playing .tournament-panel {
        height: 100dvh;
        min-height: 100dvh;
        overflow: hidden;
    }

    .tournament-frame.is-playing .tournament-topbar {
        position: relative;
        min-height: 58px;
        padding: 0.55rem clamp(1rem, 3vw, 2rem);
    }

    .tournament-frame.is-playing .tournament-topbar h1 {
        font-size: 1.02rem;
    }

    .tournament-frame.is-playing .tournament-topbar p,
    .tournament-frame.is-playing .tournament-hero,
    .tournament-frame.is-playing .tournament-result,
    .tournament-frame.is-playing .leaderboard-simple {
        display: none;
    }

    .tournament-frame.is-playing .tournament-wrap {
        width: min(980px, calc(100% - 2rem));
        height: calc(100dvh - 58px);
        display: flex;
        flex-direction: column;
        padding: 0.75rem 0 0.9rem;
        overflow: hidden;
    }

    .tournament-frame.is-playing .tournament-sheet {
        flex: 1 1 auto;
        min-height: 0;
        display: grid;
        grid-template-rows: auto 7px minmax(0, 1fr) auto;
        border: 0;
        border-radius: 0;
        background: transparent;
    }

    .tournament-frame.is-playing .tournament-play-head {
        padding: 0.2rem 0 0.55rem;
        border-bottom: 0;
    }

    .tournament-frame.is-playing .tournament-question {
        min-height: 0;
        display: grid;
        grid-template-rows: minmax(0, 1fr) auto;
        gap: 0.75rem;
        padding: 0.75rem 0 0;
        border-bottom: 0;
        overflow: hidden;
    }

    .tournament-frame.is-playing .tournament-question[hidden] {
        display: none;
    }

    .tournament-frame.is-playing .tournament-question-head {
        min-height: 0;
        overflow-y: auto;
        align-items: center;
        margin-bottom: 0;
        scrollbar-width: thin;
    }

    .tournament-frame.is-playing .tournament-question h3 {
        font-size: clamp(1.65rem, 5vw, 3.2rem);
        line-height: 1.04;
    }

    .tournament-frame.is-playing .tournament-options {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.65rem;
        padding-top: 0.72rem;
        border-top: 1px solid rgba(255, 255, 255, 0.12);
        overflow-y: auto;
        scrollbar-width: thin;
    }

    .tournament-frame.is-playing .tournament-options label {
        min-height: 54px;
        border-radius: 16px;
        padding: 0.78rem 0.9rem;
    }

    .tournament-frame.is-playing .tournament-action-bar {
        padding: 0.75rem 0 0;
    }

    .leaderboard-title,
    .leaderboard-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
    }

    .leaderboard-title {
        margin-bottom: 0.8rem;
    }

    .leaderboard-title h3 {
        font-size: 1.2rem;
    }

    .leaderboard-title span,
    .leaderboard-empty {
        color: var(--muted);
        font-weight: 800;
    }

    .leaderboard-list {
        display: grid;
        gap: 0.45rem;
    }

    .leaderboard-row {
        padding: 0.65rem 0;
        border-top: 1px solid rgba(255, 255, 255, 0.06);
    }

    .leaderboard-row span {
        color: var(--cyan);
        font-weight: 950;
        width: 28px;
    }

    .leaderboard-row strong {
        flex: 1;
    }

    .leaderboard-row em {
        font-style: normal;
        font-weight: 950;
    }

    @media (max-width: 700px) {
        .tournament-result,
        .tournament-topbar {
            align-items: flex-start;
            flex-direction: column;
        }

        .tournament-frame.is-playing .tournament-topbar {
            align-items: center;
            flex-direction: row;
            min-height: 54px;
        }

        .tournament-frame.is-playing .tournament-wrap {
            width: min(100% - 1rem, 980px);
            height: calc(100dvh - 54px);
            padding-block: 0.55rem 0.7rem;
        }

        .tournament-frame.is-playing .tournament-options {
            grid-template-columns: 1fr;
        }

        .tournament-play-head,
        .tournament-action-bar {
            align-items: stretch;
            flex-direction: column;
        }

        .tournament-frame.is-playing .tournament-play-head,
        .tournament-frame.is-playing .tournament-action-bar {
            align-items: center;
            flex-direction: row;
        }

        .tournament-submit,
        .tournament-skip {
            min-width: 120px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    (() => {
        const form = document.querySelector('[data-tournament-form]');

        if (!form) {
            return;
        }

        const frame = document.querySelector('.tournament-frame');
        const questions = Array.from(form.querySelectorAll('[data-tournament-question]'));
        const currentQuestion = form.querySelector('[data-current-question]');
        const activeQuestionTitle = form.querySelector('[data-active-question-title]');
        const answeredCount = form.querySelector('[data-answered-count]');
        const progress = form.querySelector('[data-tournament-progress]');
        const skipButton = form.querySelector('[data-skip-tournament]');
        const submitButton = form.querySelector('[data-submit-tournament]');
        const startedAt = Date.now();
        const durationInput = form.querySelector('[data-duration-input]');
        const answered = new Set();
        let activeIndex = 0;

        frame?.classList.add('is-playing');
        document.documentElement.classList.add('tournament-focus-active');

        const updateState = () => {
            questions.forEach((question, index) => {
                question.hidden = index !== activeIndex;
            });

            const isLast = activeIndex >= questions.length - 1;

            currentQuestion.textContent = String(Math.min(activeIndex + 1, questions.length));
            activeQuestionTitle.textContent = isLast ? 'Soal terakhir, simpan skor setelah menjawab.' : 'Pilih jawaban paling tepat.';
            answeredCount.textContent = String(answered.size);
            progress.style.width = `${questions.length === 0 ? 0 : (answered.size / questions.length) * 100}%`;
            skipButton.hidden = isLast;
            submitButton.hidden = !isLast;
        };

        const goNext = () => {
            if (activeIndex < questions.length - 1) {
                activeIndex += 1;
                updateState();
            }
        };

        questions.forEach((question, index) => {
            question.querySelectorAll('label').forEach((label) => {
                const input = label.querySelector('input');

                label.addEventListener('click', () => {
                    answered.add(index);
                    window.setTimeout(() => {
                        if (index < questions.length - 1) {
                            goNext();
                        } else {
                            updateState();
                        }
                    }, 420);
                });

                input?.addEventListener('change', () => {
                    question.querySelectorAll('label').forEach((item) => item.classList.remove('is-selected'));
                    label.classList.add('is-selected');
                });
            });
        });

        skipButton?.addEventListener('click', () => {
            answered.add(activeIndex);
            goNext();
        });

        form.addEventListener('submit', () => {
            durationInput.value = Math.max(0, Math.round((Date.now() - startedAt) / 1000));
            document.documentElement.classList.remove('tournament-focus-active');
        });

        updateState();
    })();
</script>
@endpush
