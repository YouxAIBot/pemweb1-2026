@extends('layouts.learning')

@section('title', 'Riwayat Quiz Room - YoLearning')

@section('content')
<div class="quiz-history-page">
    <header class="quiz-history-top">
        <a href="{{ route('learning.quiz.index') }}" class="quiz-history-back">&larr; Quiz Room</a>
        <span class="quiz-history-chip">{{ $profile->language?->name ?? 'Bahasa aktif' }}</span>
    </header>

    <main class="quiz-history-shell">
        <section class="quiz-history-head">
            <small>Quiz Room</small>
            <h1>Riwayat Pertandingan</h1>
            <p>Riwayat ini hanya menampilkan room yang pernah kamu ikuti untuk bahasa aktif. Tidak ada leaderboard permanen global.</p>
        </section>

        <section class="quiz-history-metrics">
            <div><span>Room Selesai</span><strong>{{ $summary['rooms'] }}</strong></div>
            <div><span>Total Skor</span><strong>{{ $summary['total_score'] }}</strong></div>
            <div><span>Jawaban Benar</span><strong>{{ $summary['correct'] }}</strong></div>
            <div><span>Posisi Terbaik</span><strong>{{ $summary['best_position'] ? '#' . $summary['best_position'] : '-' }}</strong></div>
        </section>

        <section class="quiz-history-card">
            <div class="quiz-history-card-head">
                <div>
                    <small>History</small>
                    <h2>Room Terakhir</h2>
                </div>
                <span>{{ $histories->total() }} data</span>
            </div>

            <div class="quiz-history-list">
                @forelse ($histories as $history)
                    <article class="quiz-history-row">
                        <div>
                            <strong>{{ $history->room_code }}</strong>
                            <h3>{{ $history->room_title }}</h3>
                            <p>{{ $history->played_at?->format('d M Y H:i') ?? $history->created_at->format('d M Y H:i') }}</p>
                        </div>
                        <div class="quiz-history-score">
                            <span>Posisi #{{ $history->final_position ?? '-' }}</span>
                            <b>{{ $history->final_score }} pts</b>
                            <em>{{ $history->correct_count }} benar / {{ $history->wrong_count }} salah</em>
                        </div>
                    </article>
                @empty
                    <p class="quiz-history-muted">Belum ada riwayat quiz room untuk bahasa aktif.</p>
                @endforelse
            </div>

            <div class="quiz-history-pagination">
                {{ $histories->links() }}
            </div>
        </section>
    </main>
</div>
@endsection

@push('styles')
<style>
    html, body { min-height: 100%; overflow-y: auto; }
    .quiz-history-page { min-height: 100vh; padding: 1rem; background: #080d18; }
    .quiz-history-top, .quiz-history-shell { width: min(1120px, 100%); margin-inline: auto; }
    .quiz-history-top { display: flex; justify-content: space-between; align-items: center; gap: 1rem; margin-bottom: 1rem; }
    .quiz-history-back, .quiz-history-chip { display: inline-flex; border: 1px solid var(--border); border-radius: 999px; background: rgba(255,255,255,.055); padding: .72rem 1rem; font-weight: 950; }
    .quiz-history-shell { border: 1px solid var(--border); border-radius: 30px; background: rgba(18,24,38,.86); box-shadow: var(--shadow); padding: clamp(1rem,4vw,2rem); }
    .quiz-history-head small, .quiz-history-card small { color: var(--cyan); font-size: .76rem; font-weight: 950; letter-spacing: .13em; text-transform: uppercase; }
    .quiz-history-head h1 { margin: .45rem 0; font-size: clamp(2.1rem,6vw,4.2rem); letter-spacing: -.08em; line-height: .96; }
    .quiz-history-head p, .quiz-history-muted, .quiz-history-row p, .quiz-history-score em { color: var(--muted); font-weight: 760; line-height: 1.6; }
    .quiz-history-metrics { display: grid; grid-template-columns: repeat(4,minmax(0,1fr)); gap: .75rem; margin: 1rem 0; }
    .quiz-history-metrics div, .quiz-history-card { border: 1px solid var(--border); border-radius: 22px; background: rgba(255,255,255,.045); padding: 1rem; }
    .quiz-history-metrics span { display: block; color: var(--muted); font-weight: 850; }
    .quiz-history-metrics strong { display: block; margin-top: .35rem; font-size: 1.6rem; letter-spacing: -.04em; }
    .quiz-history-card-head { display: flex; align-items: end; justify-content: space-between; gap: 1rem; margin-bottom: .75rem; }
    .quiz-history-card-head h2 { margin-top: .25rem; font-size: 1.45rem; letter-spacing: -.05em; }
    .quiz-history-card-head span { color: var(--muted); font-weight: 900; }
    .quiz-history-list { display: grid; gap: .7rem; }
    .quiz-history-row { display: flex; justify-content: space-between; align-items: center; gap: 1rem; border: 1px solid rgba(255,255,255,.08); border-radius: 18px; background: rgba(255,255,255,.035); padding: .9rem; }
    .quiz-history-row strong { color: var(--cyan); font-size: .78rem; letter-spacing: .12em; }
    .quiz-history-row h3 { margin: .2rem 0; font-size: 1.08rem; }
    .quiz-history-score { display: grid; gap: .2rem; text-align: right; }
    .quiz-history-score span { color: var(--cyan); font-weight: 950; }
    .quiz-history-score b { font-size: 1.15rem; }
    .quiz-history-score em { font-style: normal; }
    .quiz-history-pagination { margin-top: 1rem; }
    @media (max-width: 820px) { .quiz-history-metrics { grid-template-columns: 1fr 1fr; } .quiz-history-row { align-items: flex-start; flex-direction: column; } .quiz-history-score { text-align: left; } }
    @media (max-width: 560px) { .quiz-history-top { align-items: flex-start; flex-direction: column; } .quiz-history-metrics { grid-template-columns: 1fr; } }
</style>
@endpush
