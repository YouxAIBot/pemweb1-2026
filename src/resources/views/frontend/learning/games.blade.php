@extends('layouts.learning')

@section('title', 'Turnamen - YoLearning')

@section('content')
@php
    $visuals = [
        'tournament' => ['emoji' => '⚡', 'label' => 'Fast Battle', 'class' => 'visual-fast'],
        'duel_1v1' => ['emoji' => '⚔', 'label' => '1v1 Match', 'class' => 'visual-duel'],
        'kahoot_quiz' => ['emoji' => '🎯', 'label' => 'Quiz Room', 'class' => 'visual-quiz'],
    ];
@endphp
<div class="simple-page">
    <header class="simple-topbar">
        <a href="{{ route('dashboard') }}" class="simple-back">← Dashboard</a>
        <div class="simple-brand"><span>{{ $setting->brand_initial ?? 'Y' }}</span><b>{{ $setting->brand_text ?? 'YoLearning' }}</b></div>
    </header>

    <main class="simple-shell">
        <section class="simple-head">
            <small>{{ $profile->language?->name ?? 'Bahasa aktif' }}</small>
            <h1>Pilih Mode Turnamen</h1>
            <p>Desain dibuat lebih simpel, fokus, dan nyaman. Mode pada halaman ini otomatis mengikuti bahasa aktif yang kamu pilih di dashboard.</p>
        </section>

        <section class="simple-mode-grid">
            @forelse ($games as $game)
                @php
                    $playable = $game->isPlayable() && Route::has($game->route_name);
                    $href = $playable ? route($game->route_name) : '#';
                    $visual = $visuals[$game->key] ?? ['emoji' => $game->icon_label ?: '•', 'label' => 'Mode', 'class' => 'visual-default'];
                @endphp
                <a href="{{ $href }}" class="mode-showcase-card {{ $playable ? '' : 'is-disabled' }}">
                    <div class="mode-visual {{ $visual['class'] }}">
                        <span>{{ $visual['emoji'] }}</span>
                        <small>{{ $visual['label'] }}</small>
                    </div>
                    <div class="mode-content">
                        <div>
                            <small>{{ $game->status === 'active' ? 'Aktif' : 'Segera hadir' }}</small>
                            <h2>{{ $game->title }}</h2>
                            <p>{{ $game->description ?: $game->subtitle }}</p>
                        </div>
                        <em>{{ $playable ? ($game->button_label ?: 'Mulai') : 'Segera Hadir' }}</em>
                    </div>
                </a>
            @empty
                <article class="mode-showcase-card">
                    <div class="mode-visual visual-default"><span>⚡</span><small>Mode</small></div>
                    <div class="mode-content">
                        <div>
                            <small>Kosong</small>
                            <h2>Belum ada mode</h2>
                            <p>Admin bisa menambahkan mode dari GAME CMS.</p>
                        </div>
                    </div>
                </article>
            @endforelse
        </section>
    </main>
</div>
@endsection

@push('styles')
<style>
    html, body { min-height: 100%; overflow-y: auto; }
    .simple-page { min-height: 100vh; padding: 1rem; background: #080d18; }
    .simple-topbar, .simple-shell { width: min(1120px, 100%); margin-inline: auto; }
    .simple-topbar { display: flex; justify-content: space-between; align-items: center; gap: 1rem; margin-bottom: 1rem; }
    .simple-back, .simple-brand { display: inline-flex; align-items: center; gap: .65rem; border: 1px solid var(--border); border-radius: 999px; background: rgba(255,255,255,.055); padding: .72rem 1rem; font-weight: 950; }
    .simple-brand span { width: 2rem; height: 2rem; display: grid; place-items: center; border-radius: 999px; background: linear-gradient(135deg, var(--cyan), var(--primary)); color: #07101f; }
    .simple-shell { border: 1px solid var(--border); border-radius: 30px; background: rgba(18, 24, 38, .82); box-shadow: var(--shadow); padding: clamp(1rem, 4vw, 2rem); }
    .simple-head { max-width: 760px; margin-bottom: 1.2rem; }
    .simple-head small, .mode-content small { color: var(--cyan); font-weight: 950; letter-spacing: .13em; text-transform: uppercase; font-size: .74rem; }
    .simple-head h1 { margin-top: .45rem; font-size: clamp(2rem, 6vw, 4rem); letter-spacing: -.08em; line-height: .96; }
    .simple-head p, .mode-content p { color: var(--muted); font-weight: 750; line-height: 1.6; }
    .simple-mode-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1rem; }
    .mode-showcase-card { display:grid; gap:.95rem; border: 1px solid var(--border); border-radius: 26px; background: rgba(255,255,255,.045); padding: 1rem; transition: .2s ease; }
    .mode-showcase-card:hover { transform: translateY(-4px); border-color: rgba(102,232,247,.32); background: rgba(102,232,247,.06); }
    .mode-showcase-card.is-disabled { opacity: .55; cursor: not-allowed; }
    .mode-visual { min-height: 185px; border-radius: 22px; padding: 1rem; display:flex; flex-direction:column; justify-content:space-between; overflow:hidden; position:relative; border:1px solid rgba(255,255,255,.08); }
    .mode-visual::after, .mode-visual::before { content:""; position:absolute; border-radius:999px; filter:blur(4px); }
    .mode-visual::before { width:130px; height:130px; right:-20px; top:-20px; background:rgba(255,255,255,.12); }
    .mode-visual::after { width:110px; height:110px; left:-20px; bottom:-35px; background:rgba(255,255,255,.08); }
    .mode-visual span { position:relative; z-index:1; font-size:2.6rem; width:72px; height:72px; border-radius:24px; display:grid; place-items:center; background:rgba(10,15,24,.3); border:1px solid rgba(255,255,255,.12); }
    .mode-visual small { position:relative; z-index:1; font-size:1.05rem; letter-spacing:-.03em; text-transform:none; color:#fff; }
    .visual-fast { background:linear-gradient(135deg, rgba(0,117,255,.28), rgba(15,20,38,.3) 45%, rgba(76,0,255,.35)); }
    .visual-duel { background:linear-gradient(135deg, rgba(255,114,32,.22), rgba(15,20,38,.3) 45%, rgba(255,60,120,.28)); }
    .visual-quiz { background:linear-gradient(135deg, rgba(52,211,153,.22), rgba(15,20,38,.3) 45%, rgba(6,182,212,.28)); }
    .visual-default { background:linear-gradient(135deg, rgba(102,232,247,.18), rgba(17,24,39,.35)); }
    .mode-content { display:flex; flex-direction:column; justify-content:space-between; gap:1rem; min-height:170px; }
    .mode-content h2 { margin:.3rem 0 .45rem; font-size:1.5rem; letter-spacing:-.05em; }
    .mode-content em { display:inline-flex; align-self:flex-start; border-radius:999px; background: linear-gradient(135deg, var(--cyan), var(--primary)); color: #07101f; padding: .65rem .95rem; font-weight: 950; font-style: normal; }
    @media (max-width: 980px) { .simple-mode-grid { grid-template-columns: 1fr; } }
    @media (max-width: 760px) { .simple-topbar { align-items: flex-start; flex-direction: column; } }
</style>
@endpush
