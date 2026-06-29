@extends('layouts.learning')

@section('title', $room->title . ' - Quiz Room')

@section('content')
<div class="quiz-page" data-answer-url="{{ route('api.quiz.answer', $room) }}" data-state-url="{{ route('api.quiz.state', $room) }}" data-csrf="{{ csrf_token() }}">
    <header class="quiz-top">
        <a href="{{ route('learning.quiz.index') }}" class="quiz-back">← Quiz Room</a>
        <div class="quiz-chip">Kode Room: <b>{{ $room->code }}</b></div>
    </header>

    <main class="quiz-shell">
        <section class="quiz-head">
            <small>{{ $room->language?->name ?? 'Bahasa' }} • {{ strtoupper($room->status) }}</small>
            <h1>{{ $room->title }}</h1>
            <p>{{ $room->description ?: 'Owner dapat membuat soal sendiri. Peserta menjawab lalu melihat progress skor sementara setelah setiap soal.' }}</p>
        </section>

        @if (session('learning_success')) <div class="quiz-alert">{{ session('learning_success') }}</div> @endif
        @if (session('learning_error')) <div class="quiz-alert" style="border-color:rgba(255,107,138,.35);background:rgba(255,107,138,.1)">{{ session('learning_error') }}</div> @endif

        <div class="quiz-grid">
            <section class="quiz-panel">
                <small>Progress Skor Sementara</small>
                <div class="quiz-progress" data-progress-list>
                    @forelse ($progress as $row)
                        <div class="quiz-progress-row"><span>#{{ $loop->iteration }}</span><b>{{ $row['name'] }}</b><em>{{ $row['score'] }} pts</em></div>
                    @empty
                        <p class="quiz-muted">Belum ada peserta.</p>
                    @endforelse
                </div>

                @if ($isOwner)
                    <div class="quiz-form" style="margin-top:1rem">
                        @if ($room->status === 'draft')
                            <form method="POST" action="{{ route('learning.quiz.start', $room) }}">@csrf<button class="quiz-btn" type="submit">Mulai Quiz</button></form>
                        @elseif ($room->status === 'playing')
                            <form method="POST" action="{{ route('learning.quiz.finish', $room) }}">@csrf<button class="quiz-btn quiz-ghost" type="submit">Selesaikan dan Simpan History</button></form>
                        @endif
                    </div>
                @endif
            </section>

            <section class="quiz-panel">
                <small>{{ $isOwner && $room->status === 'draft' ? 'Tambah Soal' : 'Arena Soal' }}</small>

                @if ($isOwner && $room->status === 'draft')
                    <form method="POST" action="{{ route('learning.quiz.questions.store', $room) }}" enctype="multipart/form-data" class="quiz-form">
                        @csrf
                        <label>Pertanyaan
                            <textarea name="question_text" required placeholder="Tulis pertanyaan"></textarea>
                        </label>
                        <label>Gambar Pertanyaan <input type="file" name="question_image" accept="image/*"></label>
                        <label>Waktu per soal <input type="number" name="seconds_limit" value="20" min="5" max="120"></label>
                        @for ($i = 0; $i < 4; $i++)
                            <label>Jawaban {{ chr(65 + $i) }}
                                <input type="text" name="options[{{ $i }}][answer_text]" placeholder="Teks jawaban {{ chr(65 + $i) }}">
                                <input type="file" name="options[{{ $i }}][image]" accept="image/*">
                            </label>
                        @endfor
                        <label>Jawaban Benar
                            <select name="correct_option" required>
                                <option value="0">A</option><option value="1">B</option><option value="2">C</option><option value="3">D</option>
                            </select>
                        </label>
                        <button type="submit">Tambah Soal</button>
                    </form>
                @else
                    @if ($room->status === 'draft')
                        <p class="quiz-muted">Menunggu owner menambahkan soal dan memulai quiz.</p>
                    @elseif ($room->status === 'finished')
                        <h2>Quiz selesai</h2>
                        <p class="quiz-muted">History pertandingan sudah tersimpan. Lihat posisi akhir di progress skor.</p>
                    @elseif ($currentQuestion)
                        <article class="quiz-question" data-question-id="{{ $currentQuestion->id }}" data-start-at="{{ now()->timestamp }}" data-limit="{{ $currentQuestion->seconds_limit }}">
                            <p class="quiz-muted">Soal {{ $currentQuestion->question_order }} dari {{ $room->questions->count() }}</p>
                            <h2>{{ $currentQuestion->question_text }}</h2>
                            @if ($currentQuestion->image_path)
                                <img src="{{ asset('storage/' . $currentQuestion->image_path) }}" alt="Gambar soal" style="max-width:100%;border-radius:18px;margin:.8rem 0">
                            @endif
                            <div class="quiz-list">
                                @foreach ($currentQuestion->options as $option)
                                    @php $alreadyAnswered = $answers->has($currentQuestion->id); @endphp
                                    <button type="button" class="quiz-row quiz-answer-btn" data-option-id="{{ $option->id }}" {{ $alreadyAnswered ? 'disabled' : '' }}>
                                        <div>
                                            <b>{{ chr(64 + $loop->iteration) }}. {{ $option->answer_text ?: 'Jawaban gambar' }}</b>
                                            @if ($option->image_path)
                                                <img src="{{ asset('storage/' . $option->image_path) }}" alt="Gambar jawaban" style="max-width:160px;border-radius:14px;margin-top:.5rem">
                                            @endif
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                            <p class="quiz-muted" data-answer-feedback>{{ $answers->has($currentQuestion->id) ? 'Kamu sudah menjawab soal ini.' : 'Pilih jawaban untuk melihat progress skor sementara.' }}</p>
                        </article>
                    @else
                        <p class="quiz-muted">Semua soal sudah kamu jawab atau belum ada soal yang tersedia.</p>
                    @endif
                @endif
            </section>
        </div>

        @if ($isOwner)
            <section class="quiz-panel" style="margin-top:1rem">
                <small>Daftar Soal</small>
                <div class="quiz-list">
                    @forelse ($room->questions as $question)
                        <div class="quiz-row"><div><b>{{ $question->question_order }}. {{ $question->question_text }}</b><span>{{ $question->options->count() }} jawaban • {{ $question->seconds_limit }} detik</span></div></div>
                    @empty
                        <p class="quiz-muted">Belum ada soal di room ini.</p>
                    @endforelse
                </div>
            </section>
        @endif
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

@push('scripts')
<script>
(() => {
    const root = document.querySelector('.quiz-page');
    const buttons = document.querySelectorAll('.quiz-answer-btn');
    const question = document.querySelector('[data-question-id]');
    const feedback = document.querySelector('[data-answer-feedback]');
    const progressList = document.querySelector('[data-progress-list]');
    if (!root || !question || buttons.length === 0) return;

    const started = Date.now();
    const escapeHtml = (value) => String(value || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');

    buttons.forEach((btn) => btn.addEventListener('click', async () => {
        buttons.forEach(item => item.disabled = true);
        feedback.textContent = 'Mengirim jawaban...';

        const response = await fetch(root.dataset.answerUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': root.dataset.csrf,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                question_id: question.dataset.questionId,
                option_id: btn.dataset.optionId,
                answer_time_ms: Math.round(Date.now() - started),
            }),
        });

        const payload = await response.json();
        if (!response.ok) {
            feedback.textContent = payload.message || 'Jawaban gagal dikirim.';
            return;
        }

        if (Number(btn.dataset.optionId) === Number(payload.correct_option_id)) {
            btn.style.borderColor = 'rgba(73,211,139,.7)';
        } else {
            btn.style.borderColor = 'rgba(255,107,138,.7)';
        }
        feedback.textContent = payload.is_correct ? `Benar! +${payload.score_awarded} poin.` : 'Salah. Progress skor sudah diperbarui.';

        if (Array.isArray(payload.progress)) {
            progressList.innerHTML = payload.progress.map((row, index) => `
                <div class="quiz-progress-row"><span>#${index + 1}</span><b>${escapeHtml(row.name)}</b><em>${row.score} pts</em></div>
            `).join('');
        }
    }));
})();
</script>
@endpush
