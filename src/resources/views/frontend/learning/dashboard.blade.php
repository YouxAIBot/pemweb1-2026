@extends('layouts.learning')

@section('title', 'Dashboard Belajar - YoLearning')

@section('content')
<div class="app-frame">
    @include('frontend.learning.partials.sidebar')

    <main class="main-panel">
        <div class="main-topbar">
            <div>
                <h1>{{ $setting->dashboard_title }}</h1>
                <p>{{ $language?->name ?? 'Bahasa belum dipilih' }} • {{ ucfirst($profile->ability_level ?? 'pemula') }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="logout-link" type="submit">Logout</button>
            </form>
        </div>

        <div class="content-area">
            <section class="hero-learning">
                <small>Current Language</small>
                <h2>{{ $language?->name ?? 'Belum ada bahasa' }}</h2>
                <p>{{ $setting->dashboard_subtitle ?: 'Pilih bagian, lanjutkan progress, dan naikkan level bahasa sesuai akunmu. Semua progress tersimpan khusus untuk akun ini.' }}</p>
            </section>

            <div class="parts-grid">
                @forelse ($parts as $part)
                    @php
                        $total = max($part->levels->count(), 1);
                        $completed = $part->levels->filter(fn ($level) => optional($progressByLevel->get($level->id))->status === 'completed')->count();
                        $percent = round(($completed / $total) * 100);
                    @endphp
                    <a href="{{ route('learning.parts.show', $part) }}" class="part-card" style="--i: {{ $loop->index }}">
                        <span class="part-badge">{{ $part->badge_text ?: 'Bagian ' . $part->sort_order }}</span>
                        <h3>{{ $part->title }}</h3>
                        <p>{{ $part->description ?: 'Masuk ke peta level dan selesaikan latihan satu per satu.' }}</p>
                        <div class="progress-track"><div class="progress-fill" style="width: {{ $percent }}%"></div></div>
                        <div class="part-meta">
                            <span>{{ $completed }}/{{ $total }} level selesai</span>
                            <span>{{ $setting->part_button_label }}</span>
                        </div>
                    </a>
                @empty
                    <section class="hero-learning">
                        <small>Belum ada bagian</small>
                        <h2>Konten belum disiapkan</h2>
                        <p>Admin bisa menambahkan bahasa, bagian, level, soal, dan audio dari Filament group LEARNING CMS.</p>
                    </section>
                @endforelse
            </div>
        </div>
    </main>

    @include('frontend.learning.partials.right-panel')
</div>
@endsection
