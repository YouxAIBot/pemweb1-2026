@extends('layouts.learning')

@section('title', 'History Duel - YoLearning')

@section('content')
<div class="duel-history-page">
    <header class="duel-history-top">
        <a href="{{ route('learning.duel.lobby') }}" class="duel-history-back">&larr; Duel 1v1</a>
        <span class="duel-history-chip">{{ $profile->language?->name ?? 'Bahasa aktif' }}</span>
    </header>

    <main class="duel-history-shell">
        <section class="duel-history-head">
            <small>Duel 1v1</small>
            <h1>History & Leaderboard</h1>
            <p>Pantau ranking, skor, hasil match, dan perkembangan rating untuk bahasa aktif.</p>
        </section>

        <section class="duel-history-metrics">
            <div><span>Rank</span><strong>{{ $stats->rank_label }}</strong></div>
            <div><span>Rating</span><strong>{{ $stats->rating }}</strong></div>
            <div><span>Win</span><strong>{{ $stats->wins }}</strong></div>
            <div><span>Lose</span><strong>{{ $stats->losses }}</strong></div>
            <div><span>Draw</span><strong>{{ $stats->draws }}</strong></div>
            <div><span>Best Score</span><strong>{{ $stats->best_score }}</strong></div>
        </section>

        <div class="duel-history-layout">
            <section class="duel-history-card">
                <div class="duel-history-card-head">
                    <div>
                        <small>Riwayat Saya</small>
                        <h2>Match Selesai</h2>
                    </div>
                    <span>{{ $matches->total() }} match</span>
                </div>

                <div class="duel-history-list-full">
                    @forelse ($matches as $match)
                        @php
                            $isPlayerOne = (int) $match->player_one_id === (int) auth()->id();
                            $opponent = $isPlayerOne ? $match->playerTwo : $match->playerOne;
                            $myPlayer = $match->players->firstWhere('user_id', auth()->id());
                            $opponentPlayer = $match->players->firstWhere('user_id', $opponent?->id);
                        @endphp
                        <article class="duel-history-match">
                            <div>
                                <strong>{{ strtoupper($myPlayer?->result ?? '-') }}</strong>
                                <h3>vs {{ $opponent?->name ?? 'User' }}</h3>
                                <p>{{ $match->difficulty }} arena - {{ $match->ended_at?->format('d M Y H:i') ?? $match->updated_at->format('d M Y H:i') }}</p>
                            </div>
                            <div class="duel-history-score">
                                <b>{{ $myPlayer?->score ?? 0 }}</b>
                                <span>-</span>
                                <b>{{ $opponentPlayer?->score ?? 0 }}</b>
                            </div>
                        </article>
                    @empty
                        <p class="duel-history-muted">Belum ada match selesai.</p>
                    @endforelse
                </div>

                <div class="duel-history-pagination">
                    {{ $matches->links() }}
                </div>
            </section>

            <aside class="duel-history-card">
                <div class="duel-history-card-head">
                    <div>
                        <small>Top Player</small>
                        <h2>Leaderboard</h2>
                    </div>
                    <span>Top 25</span>
                </div>

                <div class="duel-history-rank-list">
                    @forelse ($leaderboard as $row)
                        <div class="duel-history-rank-row {{ (int) $row->user_id === (int) auth()->id() ? 'is-me' : '' }}">
                            <span>#{{ $loop->iteration }}</span>
                            <strong>{{ $row->user?->name ?? 'User' }}</strong>
                            <em>{{ $row->rating }} - {{ $row->rank_label }}</em>
                        </div>
                    @empty
                        <p class="duel-history-muted">Leaderboard masih kosong.</p>
                    @endforelse
                </div>
            </aside>
        </div>
    </main>
</div>
@endsection

@push('styles')
<style>
    html, body { min-height: 100%; overflow-y: auto; }
    .duel-history-page { min-height: 100vh; padding: 1rem; background: #080d18; }
    .duel-history-top, .duel-history-shell { width: min(1180px, 100%); margin-inline: auto; }
    .duel-history-top { display: flex; justify-content: space-between; align-items: center; gap: 1rem; margin-bottom: 1rem; }
    .duel-history-back, .duel-history-chip { display: inline-flex; border: 1px solid var(--border); border-radius: 999px; background: rgba(255,255,255,.055); padding: .72rem 1rem; font-weight: 950; }
    .duel-history-shell { border: 1px solid var(--border); border-radius: 30px; background: rgba(18,24,38,.86); box-shadow: var(--shadow); padding: clamp(1rem,4vw,2rem); }
    .duel-history-head small, .duel-history-card small { color: var(--cyan); font-size: .76rem; font-weight: 950; letter-spacing: .13em; text-transform: uppercase; }
    .duel-history-head h1 { margin: .45rem 0; font-size: clamp(2.1rem,6vw,4.2rem); letter-spacing: -.08em; line-height: .96; }
    .duel-history-head p, .duel-history-muted, .duel-history-match p { color: var(--muted); font-weight: 760; line-height: 1.6; }
    .duel-history-metrics { display: grid; grid-template-columns: repeat(6,minmax(0,1fr)); gap: .75rem; margin: 1rem 0; }
    .duel-history-metrics div, .duel-history-card { border: 1px solid var(--border); border-radius: 22px; background: rgba(255,255,255,.045); padding: 1rem; }
    .duel-history-metrics span { display: block; color: var(--muted); font-weight: 850; }
    .duel-history-metrics strong { display: block; margin-top: .35rem; font-size: 1.45rem; letter-spacing: -.04em; }
    .duel-history-layout { display: grid; grid-template-columns: minmax(0,1fr) 360px; gap: 1rem; align-items: start; }
    .duel-history-card-head { display: flex; align-items: end; justify-content: space-between; gap: 1rem; margin-bottom: .75rem; }
    .duel-history-card-head h2 { margin-top: .25rem; font-size: 1.45rem; letter-spacing: -.05em; }
    .duel-history-card-head span { color: var(--muted); font-weight: 900; }
    .duel-history-list-full, .duel-history-rank-list { display: grid; gap: .65rem; }
    .duel-history-match { display: flex; justify-content: space-between; align-items: center; gap: 1rem; border: 1px solid rgba(255,255,255,.08); border-radius: 18px; background: rgba(255,255,255,.035); padding: .9rem; }
    .duel-history-match strong { color: var(--cyan); font-size: .78rem; letter-spacing: .12em; }
    .duel-history-match h3 { margin: .2rem 0; font-size: 1.05rem; }
    .duel-history-score { display: flex; align-items: center; gap: .55rem; font-size: 1.2rem; font-weight: 950; }
    .duel-history-score span { color: var(--muted); }
    .duel-history-rank-row { display: grid; grid-template-columns: 42px 1fr auto; gap: .7rem; align-items: center; border-top: 1px solid rgba(255,255,255,.07); padding-top: .65rem; }
    .duel-history-rank-row.is-me { color: var(--cyan); }
    .duel-history-rank-row span { color: var(--cyan); font-weight: 950; }
    .duel-history-rank-row em { color: var(--muted); font-style: normal; font-weight: 850; }
    .duel-history-pagination { margin-top: 1rem; }
    @media (max-width: 980px) { .duel-history-layout, .duel-history-metrics { grid-template-columns: 1fr 1fr; } }
    @media (max-width: 640px) { .duel-history-top, .duel-history-match { align-items: flex-start; flex-direction: column; } .duel-history-layout, .duel-history-metrics { grid-template-columns: 1fr; } }
</style>
@endpush
