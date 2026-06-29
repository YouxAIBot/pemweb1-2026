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
            <div class="top-actions">
                <div class="course-dropdown">
                    <button type="button" class="course-trigger">
                        <span class="course-flag">{{ $language?->flag_label ?: strtoupper(substr($language?->name ?? 'Y', 0, 2)) }}</span>
                        <strong>{{ $selectedLanguageIds ? count($selectedLanguageIds) : 1 }}</strong>
                    </button>
                    <div class="course-popover">
                        <h4>Kursusku</h4>
                        @foreach ($languages->whereIn('id', $selectedLanguageIds) as $item)
                            <form method="POST" action="{{ route('learning.language.switch') }}">
                                @csrf
                                <input type="hidden" name="learning_language_id" value="{{ $item->id }}">
                                <button type="submit" class="course-option {{ (int) $item->id === (int) $profile->learning_language_id ? 'active' : '' }}">
                                    <span class="course-flag small">{{ $item->flag_label ?: strtoupper(substr($item->name, 0, 2)) }}</span>
                                    <b>{{ $item->name }}</b>
                                </button>
                            </form>
                        @endforeach

                        <a href="{{ route('learning.onboarding') }}" class="course-add">
                            <span>＋</span>
                            <b>Tambahkan kursus baru</b>
                        </a>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="logout-link" type="submit">Logout</button>
                </form>
            </div>
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

@push('styles')
<style>
    .profile-tile-link { color:inherit; text-decoration:none; }
    .avatar img { width:100%; height:100%; object-fit:cover; border-radius:inherit; }
    .top-actions { display:flex; gap:.55rem; align-items:center; flex-wrap:wrap; position:relative; }
    .course-dropdown { position:relative; }
    .course-trigger {
        border:1px solid var(--border);
        background:#222733;
        color:var(--text);
        font-weight:950;
        border-radius:22px;
        padding:.58rem .8rem;
        cursor:pointer;
        display:flex;
        align-items:center;
        gap:.65rem;
        min-width:116px;
        justify-content:center;
        transition:.2s;
    }
    .course-trigger:hover { background:#2d3340; }
    .course-flag {
        width:44px;
        height:34px;
        display:grid;
        place-items:center;
        border-radius:12px;
        border:2px solid rgba(255,255,255,.72);
        background:linear-gradient(135deg,#ff5d5d,#ef4444);
        color:#fff;
        font-size:.78rem;
        font-weight:950;
        letter-spacing:.03em;
        box-shadow:0 10px 22px rgba(0,0,0,.18);
    }
    .course-flag.small { width:42px; height:32px; font-size:.72rem; flex:0 0 auto; }
    .course-popover {
        position:absolute;
        top:calc(100% + 12px);
        right:0;
        width:330px;
        border:1px solid rgba(141,162,176,.35);
        border-radius:22px;
        background:#122025;
        box-shadow:0 24px 80px rgba(0,0,0,.42);
        overflow:hidden;
        z-index:80;
        opacity:0;
        visibility:hidden;
        transform:translateY(-8px);
        transition:.2s ease;
    }
    .course-popover::before {
        content:"";
        position:absolute;
        top:-9px;
        right:58px;
        width:18px;
        height:18px;
        background:#122025;
        border-left:1px solid rgba(141,162,176,.35);
        border-top:1px solid rgba(141,162,176,.35);
        transform:rotate(45deg);
    }
    .course-dropdown:hover .course-popover,
    .course-dropdown:focus-within .course-popover {
        opacity:1;
        visibility:visible;
        transform:translateY(0);
    }
    .course-popover h4 {
        padding:1.05rem 1.35rem;
        color:#7d8b93;
        text-transform:uppercase;
        font-size:.95rem;
        letter-spacing:.06em;
        border-bottom:1px solid rgba(141,162,176,.28);
    }
    .course-option,
    .course-add {
        width:100%;
        display:flex;
        align-items:center;
        gap:1rem;
        padding:1rem 1.35rem;
        border:0;
        border-bottom:1px solid rgba(141,162,176,.28);
        background:transparent;
        color:var(--text);
        text-align:left;
        cursor:pointer;
        font:inherit;
        font-weight:950;
    }
    .course-option.active,
    .course-option:hover,
    .course-add:hover { background:rgba(102,232,247,.08); color:#38bdf8; }
    .course-add { color:#eaf2ff; text-decoration:none; }
    .course-add span {
        width:42px;
        height:42px;
        display:grid;
        place-items:center;
        border:2px solid rgba(141,162,176,.46);
        border-radius:10px;
        color:#8ba0ad;
        font-size:1.4rem;
    }
    @media (max-width:760px){
        .course-popover { right:auto; left:0; width:min(330px, calc(100vw - 2rem)); }
    }
</style>
@endpush
