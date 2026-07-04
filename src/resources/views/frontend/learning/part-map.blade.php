@extends('layouts.learning')

@section('title', $part->title . ' - YoLearning')

@section('content')
@php
    $safeMapY = function ($level): float {
        $baseY = (float) ($level->position_y ?? 50);

        // Position is controlled from admin. Keep it close to the saved coordinate
        // so the map returns to the original layout and nodes do not jump down.
        return max(18, min(88, $baseY));
    };
@endphp
<div class="app-frame">
    @include('frontend.learning.partials.sidebar')

    <main class="main-panel">
        <div class="main-topbar">
            <div>
                <h1>{{ $part->title }}</h1>
                <p>{{ $part->language->name }} • {{ $levels->count() }} level</p>
            </div>
            <a href="{{ route('dashboard') }}" class="logout-link">Kembali</a>
        </div>

        <div class="content-area">
            <section class="map-wrap">
                <div class="map-title">
                    <small>{{ $part->language->name }}</small>
                    <h2>{{ strtoupper($part->title) }}</h2>
                    @if ($part->description)
                        <p style="color:var(--muted);max-width:420px;margin-top:.45rem;font-weight:700">{{ $part->description }}</p>
                    @endif
                </div>

                <svg class="level-lines" viewBox="0 0 100 100" preserveAspectRatio="none">
                    @foreach ($levels as $level)
                        @php $next = $levels->get($loop->index + 1); @endphp
                        @if ($next)
                            <line x1="{{ $level->position_x }}" y1="{{ $safeMapY($level) }}" x2="{{ $next->position_x }}" y2="{{ $safeMapY($next) }}" stroke="rgba(238,247,255,.35)" stroke-width=".12" />
                        @endif
                    @endforeach
                </svg>

                @forelse ($levels as $level)
                    @php
                        $progress = $progressByLevel->get($level->id);
                        $status = $progress?->status ?? ($loop->first ? 'available' : 'locked');
                        $locked = $status === 'locked';
                        $premiumLocked = $level->is_premium && ! auth()->user()->isPremium();
                        $levelUrl = $locked
                            ? '#'
                            : ($premiumLocked ? route('learning.premium') : route('learning.levels.show', [$part, $level]));
                    @endphp
                    <a href="{{ $levelUrl }}"
                       class="level-node {{ $status }} {{ $premiumLocked ? 'premium-locked' : '' }}"
                       style="--x: {{ $level->position_x }}; --y: {{ $safeMapY($level) }}; --i: {{ $loop->index }}"
                       aria-label="{{ $level->title }}">
                        {{ $level->short_label ?: $loop->iteration }}
                        <span class="level-tooltip">
                            <b>{{ $level->title }}</b>
                            <span>{{ $level->typeLabel() }} • {{ $level->questions_count }} soal</span>
                            <p>{{ $locked ? 'Level ini masih terkunci.' : ($premiumLocked ? 'Upgrade premium untuk membuka level ini tanpa iklan.' : ($level->description ?: 'Klik untuk membuka latihan level ini.')) }}</p>
                        </span>
                    </a>
                @empty
                    <div class="map-title" style="top:45%;left:50%;transform:translate(-50%,-50%);text-align:center">
                        <small>Belum ada level</small>
                        <h2>Admin belum membuat level</h2>
                    </div>
                @endforelse

                <div class="map-legend">
                    <span class="legend-pill">Selesai</span>
                    <span class="legend-pill">Tersedia</span>
                    <span class="legend-pill">Terkunci</span>
                </div>
            </section>
        </div>
    </main>

    @include('frontend.learning.partials.right-panel')
</div>
@endsection

@push('styles')
<style>
    .level-node.premium-locked {
        border-color: rgba(250, 204, 21, 0.72);
        background:
            radial-gradient(circle at 28% 22%, rgba(250, 204, 21, 0.42), transparent 34%),
            linear-gradient(135deg, rgba(15, 23, 42, 0.98), rgba(30, 41, 59, 0.96));
        color: #fef3c7;
        box-shadow: 0 16px 40px rgba(250, 204, 21, 0.16);
    }

    .level-node.premium-locked::after {
        content: 'Premium';
        position: absolute;
        left: 50%;
        top: calc(100% + 0.35rem);
        transform: translateX(-50%);
        border-radius: 999px;
        padding: 0.18rem 0.5rem;
        background: rgba(250, 204, 21, 0.16);
        color: #fde68a;
        font-size: 0.64rem;
        font-weight: 900;
        line-height: 1;
        white-space: nowrap;
    }
</style>
@endpush
