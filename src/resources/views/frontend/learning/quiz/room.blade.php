@extends('layouts.learning')

@section('title', ($room->title ?? 'Quiz Room') . ' - Quiz Room')

@section('content')
@php
    $questionEditorData = collect($questionEditorData ?? []);
    $isPlaying = $room->status === 'playing';
    $isFinished = $room->status === 'finished';
    $myProgress = collect($progress)->firstWhere('user_id', auth()->id());
    $myScore = (int) ($myProgress['score'] ?? $member->score ?? 0);
@endphp

<div
    class="quiz-page {{ $isPlaying ? 'is-playing' : '' }} {{ $isFinished ? 'is-finished' : '' }}"
    data-answer-url="/api/turnamen/quiz/{{ $room->id }}/answer"
    data-state-url="/api/turnamen/quiz/{{ $room->id }}/state"
    data-current-user-id="{{ auth()->id() }}"
    data-csrf="{{ csrf_token() }}"
>
    @if (! $isPlaying && ! $isFinished)
        <header class="quiz-top">
            <a href="{{ route('learning.quiz.index') }}" class="quiz-back">Kembali ke Quiz Room</a>
            <div class="quiz-chip">Kode Room: <b>{{ $room->code }}</b></div>
        </header>
    @endif

    @if ($isFinished)
        <main class="quiz-final-stage" data-final-stage data-progress='@json($progress)'>
            <section class="quiz-final-announce" data-final-announce>
                <p>Final Result</p>
                <h1 data-final-text>Menyiapkan pemenang</h1>
            </section>

            <section class="quiz-final-board" data-final-board hidden>
                <div class="quiz-final-top">
                    <a href="{{ route('learning.quiz.index') }}" class="quiz-back">Kembali</a>
                    <div class="quiz-chip">Room <b>{{ $room->code }}</b></div>
                </div>
                <h1>Leaderboard</h1>
                <div class="quiz-leaderboard-clean" data-final-list>
                    @forelse ($progress as $row)
                        <div class="quiz-leader-row">
                            <span>#{{ $loop->iteration }}</span>
                            <strong>{{ $row['name'] }}</strong>
                            <em>{{ $row['score'] }} pts</em>
                            <small>{{ $row['correct_count'] ?? 0 }} benar</small>
                        </div>
                    @empty
                        <p class="quiz-muted">Belum ada peserta.</p>
                    @endforelse
                </div>
            </section>
        </main>
    @elseif ($isPlaying)
        <main class="quiz-play-stage"
            data-play-stage
            data-room-id="{{ $room->id }}"
            @if ($currentQuestion)
                data-question-id="{{ $currentQuestion->id }}"
                data-limit="{{ $currentQuestion->seconds_limit }}"
            @endif
        >
            @if ($currentQuestion)
                <section class="quiz-countdown" data-countdown>
                    <span>3</span>
                </section>

                <section class="quiz-play-ui" data-play-ui hidden>
                    <div class="quiz-play-top">
                        <div>
                            <small>Soal {{ $currentQuestion->question_order }} / {{ $room->questions->count() }}</small>
                            <div class="quiz-line-timer"><span data-timer-bar></span></div>
                        </div>
                        <strong data-self-score>{{ $myScore }} pts</strong>
                    </div>

                    <article class="quiz-main-question">
                        <p>{{ $room->language?->name ?? 'Bahasa' }}</p>
                        <h1>{{ $currentQuestion->question_text }}</h1>
                        @if ($currentQuestion->image_path)
                            <img src="{{ asset('storage/' . $currentQuestion->image_path) }}" alt="Gambar soal">
                        @endif
                    </article>

                    <div class="quiz-answer-grid">
                        @foreach ($currentQuestion->options as $option)
                            <button type="button" class="quiz-answer-card" data-option-id="{{ $option->id }}">
                                @if ($option->image_path)
                                    <img src="{{ asset('storage/' . $option->image_path) }}" alt="Gambar jawaban">
                                @endif
                                <span>{{ $option->answer_text ?: 'Jawaban gambar' }}</span>
                            </button>
                        @endforeach
                    </div>

                    <p class="quiz-play-feedback" data-answer-feedback>Jawab secepat mungkin untuk bonus poin.</p>
                    <div class="quiz-timer-number" data-timer-text>{{ $currentQuestion->seconds_limit }}</div>
                </section>
            @else
                <section class="quiz-wait-stage" data-wait-stage>
                    <h1>Jawaban kamu sudah masuk.</h1>
                    <p>Menunggu owner menyelesaikan quiz dan membuka leaderboard.</p>
                    @if ($isOwner)
                        <form method="POST" action="{{ route('learning.quiz.finish', $room) }}">
                            @csrf
                            <button class="quiz-primary-btn" type="submit">Selesaikan dan Tampilkan Leaderboard</button>
                        </form>
                    @endif
                </section>
            @endif
        </main>
    @else
        <main class="quiz-shell">
            <section class="quiz-head">
                <small>{{ $room->language?->name ?? 'Bahasa' }} - {{ strtoupper($room->status) }}</small>
                <h1>{{ $room->title }}</h1>
                <p>{{ $room->description ?: 'Owner dapat membuat soal sendiri. Peserta menjawab lalu melihat progress skor sementara setelah setiap soal.' }}</p>
            </section>

            @if (session('learning_success')) <div class="quiz-alert">{{ session('learning_success') }}</div> @endif
            @if (session('learning_error')) <div class="quiz-alert is-error">{{ session('learning_error') }}</div> @endif

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
                        <div class="quiz-form">
                            @if ($room->status === 'draft')
                                <form method="POST" action="{{ route('learning.quiz.start', $room) }}">
                                    @csrf
                                    <button class="quiz-primary-btn" type="submit">Mulai Quiz</button>
                                </form>
                            @endif
                        </div>
                    @endif
                </section>

                <section class="quiz-panel quiz-editor-panel">
                    <small>{{ $isOwner && $room->status === 'draft' ? 'Simpan Soal' : 'Arena Soal' }}</small>

                    @if ($isOwner && $room->status === 'draft')
                        <form method="POST" action="{{ route('learning.quiz.questions.store', $room) }}" enctype="multipart/form-data" class="quiz-form" id="quizQuestionForm" data-create-url="{{ route('learning.quiz.questions.store', $room) }}">
                            @csrf
                            <div class="editor-mode">
                                <span id="editorModeLabel">Soal Baru</span>
                                <button type="button" id="newQuestionButton">Soal Baru</button>
                            </div>

                            <label>Pertanyaan
                                <textarea name="question_text" required placeholder="Tulis pertanyaan" data-question-text>{{ old('question_text') }}</textarea>
                            </label>
                            <label>Gambar Pertanyaan <input type="file" name="question_image" accept="image/*"></label>
                            <label>Waktu per soal <input type="number" name="seconds_limit" value="{{ old('seconds_limit', 20) }}" min="5" max="120" data-seconds-limit></label>
                            @for ($i = 0; $i < 4; $i++)
                                <label>Jawaban {{ chr(65 + $i) }}
                                    <input type="text" name="options[{{ $i }}][answer_text]" placeholder="Teks jawaban {{ chr(65 + $i) }}" data-option-text="{{ $i }}" value="{{ old('options.' . $i . '.answer_text') }}">
                                    <input type="file" name="options[{{ $i }}][image]" accept="image/*">
                                </label>
                            @endfor
                            <label>Jawaban Benar
                                <select name="correct_option" required data-correct-option>
                                    <option value="0">A</option>
                                    <option value="1">B</option>
                                    <option value="2">C</option>
                                    <option value="3">D</option>
                                </select>
                            </label>
                            <button type="submit" id="saveQuestionButton">Simpan Soal</button>
                        </form>
                    @else
                        <div class="quiz-wait-inline" data-wait-stage>
                            <h2>Menunggu Quiz Dimulai</h2>
                            <p class="quiz-muted">Tetap di halaman ini. Saat owner menekan mulai, halaman akan masuk ke layar soal otomatis.</p>
                        </div>
                    @endif
                </section>

                @if ($isOwner && $room->status === 'draft')
                    <aside class="quiz-panel question-sidebar">
                        <small>Daftar Soal</small>
                        <p class="quiz-muted">Klik soal untuk mengubah isi pertanyaan dan jawabannya.</p>
                        <div class="question-mini-list">
                            @forelse ($room->questions as $question)
                                <button type="button" class="question-mini-card" data-edit-question="{{ $question->id }}">
                                    <b>SOAL {{ $question->question_order }}</b>
                                    <span>"{{ \Illuminate\Support\Str::limit($question->question_text, 86) }}"</span>
                                </button>
                            @empty
                                <p class="quiz-muted">Belum ada soal di room ini.</p>
                            @endforelse
                        </div>
                    </aside>
                @endif
            </div>
        </main>
    @endif
</div>
@endsection

@push('styles')
<style>
    html, body { min-height: 100%; overflow-y: auto; }
    html.quiz-focus-active, html.quiz-focus-active body { height: 100%; overflow: hidden; }
    .quiz-page { min-height: 100vh; padding: 1rem; background: #080d18; }
    .quiz-top, .quiz-shell { width: min(1240px, 100%); margin-inline: auto; }
    .quiz-top { display: flex; justify-content: space-between; align-items: center; gap: 1rem; margin-bottom: 1rem; }
    .quiz-back, .quiz-chip { display: inline-flex; align-items: center; gap: .55rem; border: 1px solid var(--border); border-radius: 999px; background: rgba(255,255,255,.055); padding: .72rem 1rem; font-weight: 950; }
    .quiz-shell { border: 1px solid var(--border); border-radius: 30px; background: rgba(18,24,38,.86); box-shadow: var(--shadow); padding: clamp(1rem,4vw,2rem); }
    .quiz-head { margin-bottom: 1.2rem; }
    .quiz-head small, .quiz-panel small { color: var(--cyan); font-weight: 950; letter-spacing: .13em; text-transform: uppercase; font-size: .74rem; }
    .quiz-head h1 { font-size: clamp(2rem,6vw,4rem); letter-spacing: -.08em; line-height: .96; margin: .45rem 0; }
    .quiz-head p, .quiz-muted { color: var(--muted); font-weight: 760; line-height: 1.6; }
    .quiz-grid { display: grid; grid-template-columns: .72fr 1.08fr .82fr; gap: 1rem; align-items: start; }
    .quiz-panel { border: 1px solid var(--border); border-radius: 24px; background: rgba(255,255,255,.045); padding: 1rem; }
    .quiz-form { display: grid; gap: .75rem; margin-top: .85rem; }
    .quiz-form label { display: grid; gap: .35rem; color: var(--muted); font-weight: 950; font-size: .84rem; }
    .quiz-form input, .quiz-form textarea, .quiz-form select { width: 100%; border: 1px solid var(--border); border-radius: 15px; background: rgba(255,255,255,.06); color: var(--text); padding: .85rem; outline: none; font-weight: 850; }
    .quiz-form textarea { min-height: 90px; resize: vertical; }
    .quiz-form input[type=file] { padding: .65rem; }
    .quiz-form button, .quiz-primary-btn { border: 0; border-radius: 999px; background: linear-gradient(135deg,var(--cyan),var(--primary)); color: #07101f; padding: .88rem 1rem; font-weight: 950; cursor: pointer; text-align: center; }
    .quiz-alert { border: 1px solid rgba(73,211,139,.32); background: rgba(73,211,139,.1); padding: .82rem 1rem; border-radius: 17px; margin-bottom: 1rem; font-weight: 900; }
    .quiz-alert.is-error { border-color: rgba(255,107,138,.35); background: rgba(255,107,138,.1); }
    .quiz-progress { display: grid; gap: .55rem; margin-top: .8rem; }
    .quiz-progress-row { display: grid; grid-template-columns: 36px 1fr auto; gap: .75rem; align-items: center; border: 1px solid rgba(255,255,255,.07); border-radius: 15px; padding: .7rem; background: rgba(255,255,255,.035); }
    .quiz-progress-row span:first-child { color: var(--cyan); font-weight: 950; }
    .question-sidebar { position: sticky; top: 1rem; }
    .question-mini-list { display: grid; gap: .7rem; margin-top: .85rem; }
    .question-mini-card { width: 100%; border: 1px solid rgba(255,255,255,.07); border-radius: 18px; background: rgba(255,255,255,.035); color: var(--text); padding: .9rem; text-align: left; cursor: pointer; transition: .2s ease; }
    .question-mini-card:hover, .question-mini-card.active { border-color: rgba(102,232,247,.35); background: rgba(102,232,247,.08); transform: translateY(-2px); }
    .question-mini-card b { display: block; color: var(--cyan); font-size: .78rem; letter-spacing: .08em; margin-bottom: .35rem; }
    .question-mini-card span { display: block; color: var(--text); font-weight: 850; line-height: 1.45; }
    .editor-mode { display: flex; justify-content: space-between; align-items: center; gap: .75rem; border: 1px solid rgba(255,255,255,.07); background: rgba(255,255,255,.035); padding: .7rem; border-radius: 16px; }
    .editor-mode span { color: var(--cyan); font-weight: 950; letter-spacing: .08em; text-transform: uppercase; font-size: .78rem; }
    .editor-mode button { width: auto; padding: .55rem .75rem; background: rgba(255,255,255,.08); color: var(--text); }
    .quiz-wait-inline h2 { margin: .7rem 0 .35rem; font-size: 1.7rem; letter-spacing: -.05em; }

    .quiz-page.is-playing { height: 100dvh; min-height: 100dvh; overflow: hidden; padding: 0; background: #050814; }
    .quiz-play-stage { width: min(1120px, 100%); height: 100dvh; margin-inline: auto; display: grid; place-items: center; padding: clamp(1rem, 3vw, 2rem); position: relative; }
    .quiz-countdown { position: fixed; inset: 0; display: grid; place-items: center; background: #050814; z-index: 10; }
    .quiz-countdown span { font-size: clamp(4rem, 16vw, 12rem); font-weight: 950; letter-spacing: -.08em; animation: quizPop .7s ease both; }
    @keyframes quizPop { from { opacity: 0; transform: scale(.7); } to { opacity: 1; transform: scale(1); } }
    .quiz-play-ui { width: 100%; height: 100%; display: grid; grid-template-rows: auto minmax(0, 1fr) auto auto; gap: clamp(.8rem, 2vw, 1.1rem); }
    .quiz-play-ui[hidden] { display: none !important; }
    .quiz-play-top { display: flex; justify-content: space-between; align-items: center; gap: 1rem; }
    .quiz-play-top small { color: var(--cyan); font-weight: 950; letter-spacing: .14em; text-transform: uppercase; }
    .quiz-play-top strong { border: 1px solid rgba(102,232,247,.28); border-radius: 999px; background: rgba(102,232,247,.08); padding: .65rem 1rem; font-size: 1.05rem; }
    .quiz-line-timer { width: min(520px, 58vw); height: .45rem; margin-top: .55rem; border-radius: 999px; background: rgba(255,255,255,.08); overflow: hidden; }
    .quiz-line-timer span { display: block; width: 100%; height: 100%; border-radius: inherit; background: linear-gradient(90deg, var(--cyan), var(--primary)); transform-origin: left; }
    .quiz-main-question { align-self: center; min-height: 0; overflow: auto; scrollbar-width: thin; }
    .quiz-main-question p { margin: 0 0 .6rem; color: var(--cyan); font-weight: 950; letter-spacing: .14em; text-transform: uppercase; }
    .quiz-main-question h1 { max-width: 980px; font-size: clamp(2rem, 7vw, 5.3rem); line-height: .98; letter-spacing: -.08em; }
    .quiz-main-question img { max-width: min(520px, 100%); max-height: 34vh; object-fit: contain; border-radius: 22px; margin-top: 1rem; }
    .quiz-answer-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .9rem; border-top: 1px solid rgba(255,255,255,.1); padding-top: 1rem; }
    .quiz-answer-card { min-height: 94px; border: 2px solid rgba(255,255,255,.18); border-radius: 4px; background: rgba(255,255,255,.055); color: var(--text); padding: 1rem; display: grid; place-items: center; text-align: center; font-size: clamp(1rem, 2vw, 1.35rem); font-weight: 950; cursor: pointer; transition: transform .18s ease, border-color .18s ease, background .18s ease; }
    .quiz-answer-card:hover:not(:disabled) { transform: translateY(-3px); border-color: rgba(102,232,247,.68); background: rgba(102,232,247,.09); }
    .quiz-answer-card:disabled { cursor: not-allowed; opacity: .78; }
    .quiz-answer-card.is-correct { border-color: rgba(73,211,139,.9); background: rgba(73,211,139,.16); }
    .quiz-answer-card.is-wrong { border-color: rgba(255,107,138,.9); background: rgba(255,107,138,.15); }
    .quiz-answer-card img { max-width: 160px; max-height: 90px; object-fit: contain; margin-bottom: .6rem; border-radius: 10px; }
    .quiz-play-feedback { min-height: 2rem; color: var(--muted); font-weight: 850; }
    .quiz-timer-number { position: fixed; right: 1.2rem; bottom: 1rem; color: rgba(255,255,255,.42); font-size: clamp(2rem, 6vw, 4rem); font-weight: 950; pointer-events: none; }
    .quiz-wait-stage { width: min(820px, 100%); text-align: center; display: grid; gap: 1rem; }
    .quiz-wait-stage h1 { font-size: clamp(2rem, 7vw, 5rem); letter-spacing: -.08em; line-height: .95; }
    .quiz-wait-stage p { color: var(--muted); font-weight: 850; }

    .quiz-page.is-finished { min-height: 100dvh; padding: 0; background: #050814; }
    .quiz-final-stage { min-height: 100dvh; display: grid; place-items: center; padding: clamp(1rem, 3vw, 2rem); }
    .quiz-final-announce { text-align: center; }
    .quiz-final-announce p { color: var(--cyan); font-weight: 950; letter-spacing: .16em; text-transform: uppercase; }
    .quiz-final-announce h1 { margin-top: .7rem; max-width: 1000px; font-size: clamp(3rem, 10vw, 8rem); line-height: .88; letter-spacing: -.09em; opacity: 0; transform: translateY(22px) scale(.96); transition: .42s ease; }
    .quiz-final-announce h1.show { opacity: 1; transform: translateY(0) scale(1); }
    .quiz-final-board { width: min(860px, 100%); display: grid; gap: 1rem; }
    .quiz-final-board[hidden] { display: none !important; }
    .quiz-final-top { display: flex; justify-content: space-between; align-items: center; gap: .8rem; }
    .quiz-final-board h1 { font-size: clamp(2.4rem, 7vw, 5rem); letter-spacing: -.08em; line-height: .95; margin: .5rem 0; }
    .quiz-leaderboard-clean { display: grid; gap: .65rem; }
    .quiz-leader-row { display: grid; grid-template-columns: 58px 1fr auto auto; gap: 1rem; align-items: center; border-bottom: 1px solid rgba(255,255,255,.12); padding: 1rem 0; }
    .quiz-leader-row span { color: var(--cyan); font-weight: 950; font-size: 1.25rem; }
    .quiz-leader-row strong { font-size: clamp(1.1rem, 3vw, 1.6rem); }
    .quiz-leader-row em, .quiz-leader-row small { color: var(--muted); font-style: normal; font-weight: 900; }

    @media (max-width: 1080px) {
        .quiz-grid { grid-template-columns: 1fr; }
        .question-sidebar { position: static; }
    }
    @media (max-width: 720px) {
        .quiz-top, .quiz-final-top { align-items: flex-start; flex-direction: column; }
        .quiz-answer-grid { grid-template-columns: 1fr; }
        .quiz-answer-card { min-height: 76px; }
        .quiz-leader-row { grid-template-columns: 44px 1fr; }
        .quiz-leader-row em, .quiz-leader-row small { grid-column: 2; }
    }
</style>
@endpush

@push('scripts')
<script>
(() => {
    const form = document.getElementById('quizQuestionForm');
    if (!form) return;

    const questions = @json($questionEditorData);
    const modeLabel = document.getElementById('editorModeLabel');
    const saveButton = document.getElementById('saveQuestionButton');
    const newButton = document.getElementById('newQuestionButton');
    const questionText = form.querySelector('[data-question-text]');
    const secondsLimit = form.querySelector('[data-seconds-limit]');
    const correctOption = form.querySelector('[data-correct-option]');
    const optionInputs = [...form.querySelectorAll('[data-option-text]')];
    const fileInputs = [...form.querySelectorAll('input[type="file"]')];
    const editButtons = [...document.querySelectorAll('[data-edit-question]')];

    function clearFiles() {
        fileInputs.forEach((input) => input.value = '');
    }

    function setActiveButton(questionId = null) {
        editButtons.forEach((button) => {
            button.classList.toggle('active', questionId && Number(button.dataset.editQuestion) === Number(questionId));
        });
    }

    function resetForm() {
        form.action = form.dataset.createUrl;
        questionText.value = '';
        secondsLimit.value = 20;
        correctOption.value = 0;
        optionInputs.forEach((input) => input.value = '');
        clearFiles();
        modeLabel.textContent = 'Soal Baru';
        saveButton.textContent = 'Simpan Soal';
        setActiveButton(null);
        questionText.focus();
    }

    function loadQuestion(questionId) {
        const question = questions.find((item) => Number(item.id) === Number(questionId));
        if (!question) return;

        form.action = question.update_url;
        questionText.value = question.question_text || '';
        secondsLimit.value = question.seconds_limit || 20;
        optionInputs.forEach((input, index) => {
            input.value = question.options?.[index]?.answer_text || '';
        });

        const correctIndex = question.options?.findIndex((option) => option.is_correct) ?? 0;
        correctOption.value = correctIndex >= 0 ? correctIndex : 0;
        clearFiles();
        modeLabel.textContent = `Edit SOAL ${question.order}`;
        saveButton.textContent = 'Simpan Perubahan Soal';
        setActiveButton(question.id);
        form.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    editButtons.forEach((button) => {
        button.addEventListener('click', () => loadQuestion(button.dataset.editQuestion));
    });

    newButton?.addEventListener('click', resetForm);
})();

(() => {
    const root = document.querySelector('.quiz-page');
    const stage = document.querySelector('[data-play-stage]');
    if (!root || !stage || !stage.dataset.questionId) return;

    document.documentElement.classList.add('quiz-focus-active');

    const countdown = document.querySelector('[data-countdown]');
    const countdownText = countdown?.querySelector('span');
    const playUi = document.querySelector('[data-play-ui]');
    const buttons = [...document.querySelectorAll('.quiz-answer-card')];
    const feedback = document.querySelector('[data-answer-feedback]');
    const timerText = document.querySelector('[data-timer-text]');
    const timerBar = document.querySelector('[data-timer-bar]');
    const scoreEl = document.querySelector('[data-self-score]');
    const limitSeconds = Math.max(Number(stage.dataset.limit || 20), 5);
    const limitMs = limitSeconds * 1000;
    const countdownKey = `quiz-room-countdown-${stage.dataset.roomId}`;

    let timer = null;
    let startedAt = 0;
    let submitted = false;

    const showPlay = () => {
        countdown?.setAttribute('hidden', 'hidden');
        playUi.hidden = false;
        buttons.forEach((button) => button.disabled = false);
        startTimer();
    };

    const runCountdown = async () => {
        if (sessionStorage.getItem(countdownKey) === 'done') {
            showPlay();
            return;
        }

        const steps = ['3', '2', '1', 'Mulai'];
        for (const step of steps) {
            if (countdownText) {
                countdownText.textContent = step;
                countdownText.style.animation = 'none';
                countdownText.offsetHeight;
                countdownText.style.animation = '';
            }
            await new Promise((resolve) => window.setTimeout(resolve, step === 'Mulai' ? 720 : 820));
        }

        sessionStorage.setItem(countdownKey, 'done');
        showPlay();
    };

    const startTimer = () => {
        window.clearInterval(timer);
        startedAt = Date.now();

        const paint = () => {
            const elapsed = Date.now() - startedAt;
            const remainingMs = Math.max(limitMs - elapsed, 0);
            const ratio = remainingMs / limitMs;
            timerText.textContent = Math.ceil(remainingMs / 1000);
            timerBar.style.transform = `scaleX(${ratio})`;

            if (remainingMs <= 0) {
                submitAnswer(null, null);
            }
        };

        paint();
        timer = window.setInterval(paint, 120);
    };

    const updateSelfScore = (progress) => {
        const me = progress.find((row) => Number(row.user_id) === Number(root.dataset.currentUserId));
        if (me && scoreEl) {
            scoreEl.textContent = `${me.score} pts`;
        }
    };

    const submitAnswer = async (optionId, clickedButton) => {
        if (submitted) return;
        submitted = true;
        window.clearInterval(timer);
        buttons.forEach((button) => button.disabled = true);
        feedback.textContent = optionId ? 'Mengirim jawaban...' : 'Waktu habis. Mengirim otomatis...';

        try {
            const response = await fetch(root.dataset.answerUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': root.dataset.csrf,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    question_id: stage.dataset.questionId,
                    option_id: optionId,
                    answer_time_ms: Math.round(Math.min(Date.now() - startedAt, limitMs)),
                }),
            });

            const payload = await response.json();

            if (!response.ok) {
                throw new Error(payload.message || 'Jawaban gagal dikirim.');
            }

            buttons.forEach((button) => {
                if (Number(button.dataset.optionId) === Number(payload.correct_option_id)) {
                    button.classList.add('is-correct');
                }
            });

            if (clickedButton && !payload.is_correct) {
                clickedButton.classList.add('is-wrong');
            }

            if (Array.isArray(payload.progress)) {
                updateSelfScore(payload.progress);
            }

            feedback.textContent = payload.is_correct
                ? `Benar! +${payload.score_awarded} poin.`
                : 'Belum tepat. Lanjut ke soal berikutnya.';

            window.setTimeout(() => window.location.reload(), 900);
        } catch (error) {
            submitted = false;
            feedback.textContent = error.message || 'Jawaban gagal dikirim. Coba lagi.';
            buttons.forEach((button) => button.disabled = false);
            startTimer();
        }
    };

    buttons.forEach((button) => {
        button.disabled = true;
        button.addEventListener('click', () => submitAnswer(button.dataset.optionId, button));
    });

    runCountdown();
})();

(() => {
    const root = document.querySelector('.quiz-page');
    const waitStage = document.querySelector('[data-wait-stage]');
    if (!root || !waitStage) return;

    const poller = window.setInterval(async () => {
        try {
            const response = await fetch(root.dataset.stateUrl, { headers: { 'Accept': 'application/json' } });
            const payload = await response.json();
            const status = payload.room?.status;

            if (status === 'playing' || status === 'finished') {
                window.clearInterval(poller);
                window.location.reload();
            }
        } catch (error) {
            window.clearInterval(poller);
        }
    }, 1800);
})();

(() => {
    const stage = document.querySelector('[data-final-stage]');
    if (!stage) return;

    document.documentElement.classList.add('quiz-focus-active');

    const announce = stage.querySelector('[data-final-announce]');
    const textEl = stage.querySelector('[data-final-text]');
    const board = stage.querySelector('[data-final-board]');
    const progress = JSON.parse(stage.dataset.progress || '[]');
    const winner = progress[0] || { name: 'Belum ada pemenang' };
    const sequence = ['Quiz selesai', 'Pemenangnya', String(winner.name || 'Belum ada pemenang')];

    const sleep = (ms) => new Promise((resolve) => window.setTimeout(resolve, ms));

    const run = async () => {
        for (const line of sequence) {
            textEl.classList.remove('show');
            await sleep(220);
            textEl.textContent = line;
            textEl.classList.add('show');
            await sleep(1250);
        }

        announce.hidden = true;
        board.hidden = false;
        document.documentElement.classList.remove('quiz-focus-active');
    };

    run();
})();
</script>
@endpush
