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

                    @foreach ($questions as $question)
                        <section class="tournament-question">
                            <input type="hidden" name="question_ids[]" value="{{ $question->id }}">
                            <div class="tournament-question-head">
                                <span>{{ $loop->iteration }}</span>
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

                    <button type="submit" class="tournament-submit">
                        Selesai & Simpan Skor
                    </button>
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

    .tournament-question {
        padding: 1.05rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.07);
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
        accent-color: #66e8f7;
    }

    .tournament-submit {
        width: calc(100% - 2rem);
        margin: 1rem;
        border: 0;
        border-radius: 999px;
        background: linear-gradient(135deg, var(--cyan), var(--primary));
        color: #07101f;
        padding: 0.95rem 1.1rem;
        font-weight: 950;
        cursor: pointer;
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

        const startedAt = Date.now();
        const durationInput = form.querySelector('[data-duration-input]');

        form.addEventListener('submit', () => {
            durationInput.value = Math.max(0, Math.round((Date.now() - startedAt) / 1000));
        });
    })();
</script>
@endpush
