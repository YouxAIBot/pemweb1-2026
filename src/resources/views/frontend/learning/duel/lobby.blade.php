@extends('layouts.learning')

@section('title', 'Duel 1v1 - YoLearning')

@section('content')
<div class="duel-lobby-page">
    <header class="duel-topbar">
        <a href="{{ route('learning.games') }}" class="duel-back">← Turnamen</a>
        <div class="duel-brand">
            <span>{{ $setting->brand_initial ?? 'Y' }}</span>
            <strong>{{ $setting->brand_text ?? 'YoLearning' }}</strong>
        </div>
    </header>

    <main class="duel-lobby-shell">
        <section class="duel-hero-card">
            <div class="duel-hero-copy">
                <span class="duel-kicker">Mode Kompetitif</span>
                <h1>Duel 1v1</h1>
                <p>Cari lawan, masuk arena VS, countdown 3 detik, lalu jawab 10 soal mix. Setiap soal punya waktu 10 detik.</p>
            </div>

            <div class="duel-score-rules">
                <div>
                    <b>+100</b>
                    <span>Jawaban benar</span>
                </div>
                <div>
                    <b>+0-50</b>
                    <span>Bonus cepat</span>
                </div>
                <div>
                    <b>+0</b>
                    <span>Jawaban salah</span>
                </div>
            </div>

            <form class="duel-find-panel" data-duel-find-form>
                <label>Difficulty Arena</label>
                <select name="difficulty" class="duel-select">
                    <option value="normal">Normal Arena</option>
                    <option value="easy">Easy Arena</option>
                    <option value="hard">Hard Arena</option>
                </select>
                <p class="duel-match-note">
                    Match dicari untuk bahasa {{ $profile->language?->name ?? 'aktif' }} dan difficulty yang sama. Untuk test 2 akun, buka akun kedua di incognito atau browser lain.
                </p>

                <button type="submit" class="duel-primary-btn" data-find-button>
                    Cari Lawan
                </button>

                <button type="button" class="duel-ghost-btn" data-cancel-button hidden>
                    Batalkan
                </button>

                <p class="duel-status-text" data-duel-status>Siap mencari lawan.</p>
            </form>
        </section>

        <section class="duel-grid">
            <article class="duel-stat-card">
                <small>Statistik Saya</small>
                <h2>{{ $stats->rank_label }}</h2>
                <div class="duel-stat-row">
                    <span>Rating</span>
                    <b>{{ $stats->rating }}</b>
                </div>
                <div class="duel-stat-row">
                    <span>Match</span>
                    <b>{{ $stats->matches }}</b>
                </div>
                <div class="duel-stat-row">
                    <span>Win / Lose</span>
                    <b>{{ $stats->wins }} / {{ $stats->losses }}</b>
                </div>
                <div class="duel-stat-row">
                    <span>Best Score</span>
                    <b>{{ $stats->best_score }}</b>
                </div>
            </article>

            <article class="duel-card">
                <div class="duel-card-head">
                    <div>
                        <small>Leaderboard</small>
                        <h3>Duel Rank</h3>
                    </div>
                    <span>Top 12</span>
                </div>

                <div class="duel-leaderboard-list">
                    @forelse ($leaderboard as $row)
                        <div class="duel-leaderboard-row">
                            <span>#{{ $loop->iteration }}</span>
                            <strong>{{ $row->user?->name ?? 'User' }}</strong>
                            <em>{{ $row->rank_label }} · {{ $row->rating }}</em>
                        </div>
                    @empty
                        <p class="duel-empty">Belum ada leaderboard duel.</p>
                    @endforelse
                </div>
            </article>

            <article class="duel-card">
                <div class="duel-card-head">
                <div>
                    <small>History</small>
                    <h3>Match Terakhir</h3>
                </div>
                <a href="{{ route('learning.duel.history') }}">Lihat Semua</a>
            </div>

                <div class="duel-history-list">
                    @forelse ($history as $match)
                        @php
                            $isPlayerOne = (int) $match->player_one_id === (int) auth()->id();
                            $opponent = $isPlayerOne ? $match->playerTwo : $match->playerOne;
                            $myPlayer = $match->players->firstWhere('user_id', auth()->id());
                            $opponentPlayer = $match->players->firstWhere('user_id', $opponent?->id);
                        @endphp
                        <div class="duel-history-row">
                            <div>
                                <b>{{ strtoupper($myPlayer?->result ?? '-') }}</b>
                                <span>vs {{ $opponent?->name ?? 'User' }}</span>
                            </div>
                            <em>{{ $myPlayer?->score ?? 0 }} - {{ $opponentPlayer?->score ?? 0 }}</em>
                        </div>
                    @empty
                        <p class="duel-empty">Belum ada history duel.</p>
                    @endforelse
                </div>
            </article>
        </section>
    </main>
</div>
@endsection

@push('styles')
<style>
    .duel-lobby-page {
        min-height: 100vh;
        padding: 1rem;
        background:
            radial-gradient(circle at 15% 8%, rgba(102, 232, 247, .13), transparent 25rem),
            radial-gradient(circle at 84% 15%, rgba(110, 124, 247, .18), transparent 30rem),
            #070b16;
    }

    .duel-topbar,
    .duel-lobby-shell {
        width: min(1120px, 100%);
        margin-inline: auto;
    }

    .duel-topbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .duel-back,
    .duel-brand {
        display: inline-flex;
        align-items: center;
        gap: .65rem;
        border: 1px solid var(--border);
        border-radius: 999px;
        background: rgba(255, 255, 255, .055);
        color: var(--text);
        padding: .7rem .95rem;
        font-weight: 950;
    }

    .duel-brand span {
        display: grid;
        place-items: center;
        width: 2rem;
        height: 2rem;
        border-radius: 999px;
        background: linear-gradient(135deg, var(--cyan), var(--primary));
        color: #081120;
    }

    .duel-hero-card,
    .duel-card,
    .duel-stat-card {
        border: 1px solid var(--border);
        border-radius: 30px;
        background: linear-gradient(145deg, rgba(29, 36, 52, .88), rgba(11, 17, 31, .84));
        box-shadow: var(--shadow);
    }

    .duel-hero-card {
        display: grid;
        grid-template-columns: minmax(0, 1.35fr) minmax(220px, .8fr) minmax(260px, .85fr);
        gap: 1rem;
        align-items: center;
        padding: clamp(1.2rem, 4vw, 2rem);
        overflow: hidden;
        position: relative;
    }

    .duel-hero-card::before {
        content: "";
        position: absolute;
        inset: -40%;
        background:
            radial-gradient(circle at 20% 30%, rgba(102, 232, 247, .18), transparent 20%),
            radial-gradient(circle at 80% 20%, rgba(110, 124, 247, .2), transparent 24%);
        opacity: .8;
        pointer-events: none;
    }

    .duel-hero-card > * {
        position: relative;
        z-index: 1;
    }

    .duel-kicker,
    .duel-card small,
    .duel-stat-card small {
        color: var(--cyan);
        font-size: .78rem;
        font-weight: 950;
        letter-spacing: .14em;
        text-transform: uppercase;
    }

    .duel-hero-copy h1 {
        margin-top: .45rem;
        font-size: clamp(2.2rem, 6vw, 4.8rem);
        letter-spacing: -.08em;
        line-height: .92;
    }

    .duel-hero-copy p {
        max-width: 560px;
        margin-top: .9rem;
        color: var(--muted);
        font-weight: 750;
        line-height: 1.6;
    }

    .duel-score-rules {
        display: grid;
        gap: .65rem;
    }

    .duel-score-rules div {
        border: 1px solid rgba(255, 255, 255, .1);
        border-radius: 20px;
        padding: .95rem;
        background: rgba(255, 255, 255, .055);
    }

    .duel-score-rules b {
        display: block;
        font-size: 1.5rem;
        color: #eaffff;
    }

    .duel-score-rules span {
        display: block;
        color: var(--muted);
        font-size: .86rem;
        font-weight: 850;
    }

    .duel-find-panel {
        display: grid;
        gap: .8rem;
        border: 1px solid rgba(255, 255, 255, .1);
        border-radius: 24px;
        background: rgba(3, 7, 18, .28);
        padding: 1rem;
    }

    .duel-find-panel label {
        color: var(--muted);
        font-size: .82rem;
        font-weight: 950;
    }

    .duel-select {
        width: 100%;
        border: 1px solid var(--border);
        border-radius: 16px;
        background: rgba(255, 255, 255, .07);
        color: var(--text);
        padding: .85rem;
        font-weight: 900;
        outline: none;
    }

    .duel-select option {
        color: #111827;
    }

    .duel-primary-btn,
    .duel-ghost-btn {
        border: 0;
        border-radius: 999px;
        padding: .9rem 1rem;
        font-weight: 950;
        cursor: pointer;
    }

    .duel-primary-btn {
        background: linear-gradient(135deg, var(--cyan), var(--primary));
        color: #07101f;
        box-shadow: 0 16px 42px rgba(102, 232, 247, .15);
    }

    .duel-ghost-btn {
        border: 1px solid var(--border);
        background: rgba(255, 255, 255, .06);
        color: var(--text);
    }

    .duel-status-text {
        min-height: 1.5rem;
        color: var(--muted);
        font-weight: 850;
    }

    .duel-match-note {
        margin: -.15rem 0 0;
        color: var(--muted);
        font-size: .78rem;
        font-weight: 800;
        line-height: 1.45;
    }

    .duel-grid {
        display: grid;
        grid-template-columns: .78fr 1.1fr 1.1fr;
        gap: 1rem;
        margin-top: 1rem;
    }

    .duel-card,
    .duel-stat-card {
        padding: 1.1rem;
    }

    .duel-stat-card h2 {
        margin: .3rem 0 1rem;
        font-size: 2rem;
        letter-spacing: -.06em;
    }

    .duel-stat-row,
    .duel-leaderboard-row,
    .duel-history-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .8rem;
        padding: .72rem 0;
        border-top: 1px solid rgba(255, 255, 255, .07);
    }

    .duel-stat-row span,
    .duel-leaderboard-row em,
    .duel-history-row span,
    .duel-empty {
        color: var(--muted);
        font-style: normal;
        font-weight: 800;
    }

    .duel-card-head {
        display: flex;
        align-items: end;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: .5rem;
    }

    .duel-card-head h3 {
        margin-top: .2rem;
        font-size: 1.35rem;
        letter-spacing: -.04em;
    }

    .duel-card-head span,
    .duel-card-head a {
        color: var(--muted);
        font-weight: 900;
        font-size: .85rem;
    }

    .duel-leaderboard-row span {
        color: var(--cyan);
        font-weight: 950;
        width: 38px;
    }

    .duel-leaderboard-row strong {
        flex: 1;
    }

    .duel-history-row b {
        color: var(--cyan);
        margin-right: .5rem;
    }

    @media (max-width: 960px) {
        .duel-hero-card,
        .duel-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@push('scripts')
<script>
(() => {
    const form = document.querySelector('[data-duel-find-form]');
    const statusEl = document.querySelector('[data-duel-status]');
    const findBtn = document.querySelector('[data-find-button]');
    const cancelBtn = document.querySelector('[data-cancel-button]');
    let poller = null;

    const csrf = '{{ csrf_token() }}';

    const setWaiting = (waiting) => {
        findBtn.disabled = waiting;
        cancelBtn.hidden = !waiting;
        findBtn.textContent = waiting ? 'Mencari...' : 'Cari Lawan';
    };

    const redirectIfMatched = (payload) => {
        if (payload.status === 'matched' && payload.redirect_url) {
            statusEl.textContent = 'Lawan ditemukan! Masuk arena...';
            window.location.href = payload.redirect_url;
            return true;
        }
        return false;
    };

    const startPolling = () => {
        window.clearInterval(poller);
        poller = window.setInterval(async () => {
            const res = await fetch('{{ route('learning.duel.queue.status') }}', {
                headers: { 'Accept': 'application/json' }
            });
            const payload = await res.json();

            if (redirectIfMatched(payload)) {
                window.clearInterval(poller);
                return;
            }

            if (payload.status === 'expired' || payload.status === 'idle') {
                statusEl.textContent = 'Antrean habis. Coba cari lawan lagi.';
                setWaiting(false);
                window.clearInterval(poller);
            }
        }, 1500);
    };

    form?.addEventListener('submit', async (event) => {
        event.preventDefault();
        setWaiting(true);
        statusEl.textContent = 'Mencari lawan... buka akun lain di incognito untuk testing.';

        const formData = new FormData(form);

        const res = await fetch('{{ route('learning.duel.find') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
            },
            body: formData,
        });

        if (!res.ok) {
            statusEl.textContent = 'Matchmaking gagal diproses. Cek koneksi atau coba ulang.';
            setWaiting(false);
            return;
        }

        const payload = await res.json();

        if (redirectIfMatched(payload)) {
            return;
        }

        statusEl.textContent = payload.message || 'Menunggu lawan...';
        startPolling();
    });

    cancelBtn?.addEventListener('click', async () => {
        await fetch('{{ route('learning.duel.queue.cancel') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
            },
        });

        statusEl.textContent = 'Pencarian dibatalkan.';
        setWaiting(false);
        window.clearInterval(poller);
    });
})();
</script>
@endpush
