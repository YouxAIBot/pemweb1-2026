@extends('layouts.learning')

@section('title', 'Quiz Room - YoLearning')

@section('content')
<div class="quiz-page">
    <header class="quiz-top">
        <a href="{{ route('learning.games') }}" class="quiz-back">← Turnamen</a>
        <div class="quiz-chip">{{ $profile->language?->name ?? 'Bahasa aktif' }}</div>
    </header>

    <main class="quiz-shell">
        <section class="quiz-head">
            <small>Quiz Room / Kahoot</small>
            <h1>Room Quiz Simpel</h1>
            <p>Mode ini tidak memakai leaderboard global. Skor hanya berlaku di dalam room dan history pertandingan tetap tersimpan.</p>
        </section>

        @if (session('learning_success')) <div class="quiz-alert">{{ session('learning_success') }}</div> @endif

        <div class="quiz-grid">
            <section class="quiz-panel">
                <small>Buat Room</small>
                <form method="POST" action="{{ route('learning.quiz.store') }}" class="quiz-form">
                    @csrf
                    <label>Judul Room
                        <input type="text" name="title" placeholder="Contoh: Kuis Bahasa Inggris Bab 1" required>
                    </label>
                    <label>Deskripsi
                        <textarea name="description" placeholder="Opsional"></textarea>
                    </label>
                    <button type="submit">Buat Room</button>
                </form>
            </section>

            <section class="quiz-panel">
                <small>Masuk Room</small>
                <form method="POST" action="{{ route('learning.quiz.join') }}" class="quiz-form">
                    @csrf
                    <label>Kode Room
                        <input type="text" name="code" placeholder="ABC123" required>
                        @error('code') <span class="quiz-error">{{ $message }}</span> @enderror
                    </label>
                    <button type="submit" class="quiz-ghost">Masuk</button>
                </form>
            </section>
        </div>

        <div class="quiz-grid" style="margin-top:1rem">
            <section class="quiz-panel">
                <small>Room Saya</small>
                <div class="quiz-list">
                    @forelse ($myRooms as $room)
                        <a href="{{ route('learning.quiz.room', $room) }}" class="quiz-row">
                            <div><b>{{ $room->title }}</b><span>Kode {{ $room->code }} • {{ $room->questions_count }} soal • {{ $room->members_count }} anggota</span></div>
                            <em>{{ strtoupper($room->status) }}</em>
                        </a>
                    @empty
                        <p class="quiz-muted">Belum membuat room.</p>
                    @endforelse
                </div>
            </section>

            <section class="quiz-panel">
                <small>History Room</small>
                <div class="quiz-list">
                    @forelse ($joinedRooms as $room)
                        <a href="{{ route('learning.quiz.room', $room) }}" class="quiz-row">
                            <div><b>{{ $room->title }}</b><span>Owner {{ $room->owner?->name ?? 'User' }} • {{ $room->questions_count }} soal</span></div>
                            <em>{{ strtoupper($room->status) }}</em>
                        </a>
                    @empty
                        <p class="quiz-muted">Belum ada history room.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </main>
</div>
@endsection

@push('styles')
<style>
    html, body { min-height:100%; overflow-y:auto; }
    .quiz-page { min-height:100vh; padding:1rem; background:#080d18; }
    .quiz-top, .quiz-shell { width:min(1080px,100%); margin-inline:auto; }
    .quiz-top { display:flex; justify-content:space-between; align-items:center; gap:1rem; margin-bottom:1rem; }
    .quiz-back, .quiz-chip { display:inline-flex; align-items:center; gap:.55rem; border:1px solid var(--border); border-radius:999px; background:rgba(255,255,255,.055); padding:.72rem 1rem; font-weight:950; }
    .quiz-shell { border:1px solid var(--border); border-radius:30px; background:rgba(18,24,38,.86); box-shadow:var(--shadow); padding:clamp(1rem,4vw,2rem); }
    .quiz-head { margin-bottom:1.2rem; }
    .quiz-head small, .quiz-panel small { color:var(--cyan); font-weight:950; letter-spacing:.13em; text-transform:uppercase; font-size:.74rem; }
    .quiz-head h1 { font-size:clamp(2rem,6vw,4rem); letter-spacing:-.08em; line-height:.96; margin:.45rem 0; }
    .quiz-head p, .quiz-muted { color:var(--muted); font-weight:760; line-height:1.6; }
    .quiz-grid { display:grid; grid-template-columns:.85fr 1.15fr; gap:1rem; align-items:start; }
    .quiz-panel { border:1px solid var(--border); border-radius:24px; background:rgba(255,255,255,.045); padding:1rem; }
    .quiz-form { display:grid; gap:.75rem; margin-top:.85rem; }
    .quiz-form label { display:grid; gap:.35rem; color:var(--muted); font-weight:950; font-size:.84rem; }
    .quiz-form input, .quiz-form textarea, .quiz-form select { width:100%; border:1px solid var(--border); border-radius:15px; background:rgba(255,255,255,.06); color:var(--text); padding:.85rem; outline:none; font-weight:850; }
    .quiz-form textarea { min-height:90px; resize:vertical; }
    .quiz-form input[type=file] { padding:.65rem; }
    .quiz-form button, .quiz-btn { border:0; border-radius:999px; background:linear-gradient(135deg,var(--cyan),var(--primary)); color:#07101f; padding:.88rem 1rem; font-weight:950; cursor:pointer; text-align:center; }
    .quiz-ghost { border:1px solid var(--border); background:rgba(255,255,255,.06); color:var(--text); }
    .quiz-list { display:grid; gap:.7rem; margin-top:.85rem; }
    .quiz-row { display:flex; justify-content:space-between; align-items:center; gap:1rem; border:1px solid rgba(255,255,255,.07); border-radius:18px; padding:.85rem; background:rgba(255,255,255,.035); }
    .quiz-row b { display:block; }
    .quiz-row span, .quiz-row em { color:var(--muted); font-weight:850; font-style:normal; font-size:.84rem; }
    .quiz-alert { border:1px solid rgba(73,211,139,.32); background:rgba(73,211,139,.1); padding:.82rem 1rem; border-radius:17px; margin-bottom:1rem; font-weight:900; }
    .quiz-error { color:#ffb4c2; font-weight:850; font-size:.83rem; }
    .quiz-progress { display:grid; gap:.55rem; margin-top:.8rem; }
    .quiz-progress-row { display:grid; grid-template-columns:36px 1fr auto; gap:.75rem; align-items:center; border:1px solid rgba(255,255,255,.07); border-radius:15px; padding:.7rem; background:rgba(255,255,255,.035); }
    .quiz-progress-row span:first-child { color:var(--cyan); font-weight:950; }
    @media (max-width:860px){ .quiz-grid{grid-template-columns:1fr;} .quiz-top{align-items:flex-start;flex-direction:column;} .quiz-row{align-items:flex-start;flex-direction:column;} }
</style>
@endpush
