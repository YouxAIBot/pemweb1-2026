@extends('layouts.learning')

@section('title', 'Turnamen - YoLearning')

@section('content')
<div class="turnamen-choice-page">
    <header class="turnamen-choice-top">
        <a href="{{ route('dashboard') }}" class="brand-mini">
            <span class="mark">Y</span>
            <span>{{ $setting->brand_text }}</span>
        </a>

        <a href="{{ route('dashboard') }}" class="turnamen-back">Kembali</a>
    </header>

    <main class="turnamen-choice-card" data-modes-api="{{ route('api.tournament.modes') }}" data-leaderboard-api="{{ route('api.tournament.leaderboard') }}">
        <section class="onboarding-head turnamen-choice-head">
            <span>Tournament Center</span>
            <h1>Pilih Mode Turnamen</h1>
            <p>Mode turnamen diatur dari admin panel. Aktifkan, urutkan, atau tambah mode baru lewat GAME CMS.</p>
        </section>

        <section class="language-grid turnamen-mode-grid" data-game-modes-list>
            @forelse ($games as $game)
                @php
                    $playable = $game->isPlayable() && Route::has($game->route_name);
                    $href = $playable ? route($game->route_name) : '#';
                @endphp

                <a href="{{ $href }}" class="language-option turnamen-mode-option {{ $playable ? 'is-playable' : 'is-disabled' }}" style="--i: {{ $loop->index }}">
                    <span class="flag-chip">{{ $game->icon_label ?: '•' }}</span>
                    <small>{{ $game->status === 'active' ? 'Aktif' : 'Segera Hadir' }}</small>
                    <b>{{ $game->title }}</b>
                    <p>{{ $game->subtitle ?: $game->description }}</p>
                    <span class="turnamen-mode-action">
                        {{ $playable ? $game->button_label : ($game->button_label ?: 'Segera Hadir') }}
                    </span>
                </a>
            @empty
                <article class="language-option turnamen-mode-option">
                    <small>Belum ada mode</small>
                    <b>Turnamen belum disiapkan</b>
                    <p>Admin bisa menambahkan mode dari GAME CMS → Game Modes.</p>
                </article>
            @endforelse
        </section>

        <section class="turnamen-leaderboard">
            <div class="turnamen-leaderboard-head">
                <div>
                    <small>Leaderboard</small>
                    <h3>Top Turnamen</h3>
                </div>
                <span>{{ $profile->language?->name ?? 'Bahasa' }}</span>
            </div>

            <div class="turnamen-rank-list" data-tournament-leaderboard>
                @forelse ($leaderboard as $attempt)
                    <div class="turnamen-rank-row">
                        <span>{{ $loop->iteration }}</span>
                        <strong>{{ $attempt->user?->name ?? 'User' }}</strong>
                        <em>{{ $attempt->score }}%</em>
                    </div>
                @empty
                    <p class="turnamen-muted">Belum ada skor turnamen.</p>
                @endforelse
            </div>
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

    .turnamen-choice-page {
        min-height: 100vh;
        padding: 1rem;
        background:
            radial-gradient(circle at 20% 10%, rgba(102, 232, 247, 0.12), transparent 24rem),
            radial-gradient(circle at 80% 10%, rgba(110, 124, 247, 0.14), transparent 28rem),
            #080d18;
    }

    .turnamen-choice-top {
        width: min(1080px, 100%);
        margin: 0 auto 1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
    }

    .turnamen-choice-top .brand-mini {
        margin: 0;
    }

    .turnamen-back,
    .turnamen-choice-top button {
        border: 1px solid var(--border);
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.055);
        color: var(--text);
        padding: 0.68rem 0.95rem;
        font-weight: 950;
        cursor: pointer;
    }

    .turnamen-choice-card {
        width: min(1080px, 100%);
        margin: 0 auto 3rem;
        border: 1px solid var(--border);
        border-radius: 34px;
        background: linear-gradient(145deg, rgba(27, 35, 49, 0.86), rgba(12, 17, 30, 0.8));
        box-shadow: var(--shadow);
        padding: clamp(1.2rem, 4vw, 3rem);
        animation: dashIn 0.65s ease both;
    }

    .turnamen-choice-head {
        margin-bottom: 1.6rem;
    }

    .turnamen-mode-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .turnamen-mode-option {
        min-height: 178px;
    }

    .turnamen-mode-option.is-disabled {
        opacity: 0.58;
        cursor: not-allowed;
    }

    .turnamen-mode-action {
        position: relative;
        z-index: 1;
        display: inline-flex;
        width: fit-content;
        margin-top: 0.85rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.08);
        color: #eafcff;
        padding: 0.55rem 0.75rem;
        font-size: 0.82rem;
        font-weight: 950;
    }

    .turnamen-mode-option.is-playable .turnamen-mode-action {
        background: linear-gradient(135deg, var(--cyan), var(--primary));
        color: #07101f;
    }

    .turnamen-leaderboard {
        margin-top: 1.2rem;
        padding-top: 1.2rem;
        border-top: 1px solid var(--border);
    }

    .turnamen-leaderboard-head {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 0.7rem;
    }

    .turnamen-leaderboard-head small {
        color: var(--cyan);
        font-size: 0.76rem;
        font-weight: 950;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    .turnamen-leaderboard-head h3 {
        margin-top: 0.25rem;
        font-size: 1.45rem;
        letter-spacing: -0.05em;
    }

    .turnamen-leaderboard-head span,
    .turnamen-muted {
        color: var(--muted);
        font-weight: 850;
    }

    .turnamen-rank-list {
        display: grid;
        gap: 0.25rem;
    }

    .turnamen-rank-row {
        display: grid;
        grid-template-columns: 34px minmax(0, 1fr) auto;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 0;
        border-top: 1px solid rgba(255, 255, 255, 0.06);
    }

    .turnamen-rank-row span {
        color: var(--cyan);
        font-weight: 950;
    }

    .turnamen-rank-row em {
        font-style: normal;
        font-weight: 950;
    }

    @media (max-width: 760px) {
        .turnamen-choice-top,
        .turnamen-leaderboard-head {
            align-items: flex-start;
            flex-direction: column;
        }

        .turnamen-mode-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    (() => {
        const wrap = document.querySelector('[data-modes-api]');

        if (!wrap) {
            return;
        }

        const modesApi = wrap.dataset.modesApi;
        const leaderboardApi = wrap.dataset.leaderboardApi;
        const gameList = document.querySelector('[data-game-modes-list]');
        const rankList = document.querySelector('[data-tournament-leaderboard]');

        const escapeHtml = (value) => String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');

        if (modesApi && gameList) {
            fetch(modesApi, { headers: { 'Accept': 'application/json' } })
                .then((response) => response.ok ? response.json() : null)
                .then((payload) => {
                    if (!payload || !Array.isArray(payload.games) || payload.games.length === 0) {
                        return;
                    }

                    gameList.innerHTML = payload.games.map((game, index) => {
                        const playable = Boolean(game.playable && game.url);
                        return `
                            <a href="${playable ? game.url : '#'}" class="language-option turnamen-mode-option ${playable ? 'is-playable' : 'is-disabled'}" style="--i:${index}">
                                <span class="flag-chip">${escapeHtml(game.icon_label || '•')}</span>
                                <small>${game.status === 'active' ? 'Aktif' : 'Segera Hadir'}</small>
                                <b>${escapeHtml(game.title)}</b>
                                <p>${escapeHtml(game.subtitle || game.description || '')}</p>
                                <span class="turnamen-mode-action">${escapeHtml(playable ? game.button_label : (game.button_label || 'Segera Hadir'))}</span>
                            </a>
                        `;
                    }).join('');
                })
                .catch(() => {});
        }

        if (leaderboardApi && rankList) {
            fetch(leaderboardApi, { headers: { 'Accept': 'application/json' } })
                .then((response) => response.ok ? response.json() : null)
                .then((payload) => {
                    if (!payload || !Array.isArray(payload.leaderboard) || payload.leaderboard.length === 0) {
                        return;
                    }

                    rankList.innerHTML = payload.leaderboard.map((row, index) => `
                        <div class="turnamen-rank-row">
                            <span>${index + 1}</span>
                            <strong>${escapeHtml(row.name)}</strong>
                            <em>${row.score}%</em>
                        </div>
                    `).join('');
                })
                .catch(() => {});
        }
    })();
</script>
@endpush
