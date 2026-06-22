@extends('layouts.learning')

@section('title', 'Arena Duel 1v1 - YoLearning')

@section('content')
<div class="duel-room-page"
    data-state-url="{{ route('api.duel.state', $session) }}"
    data-answer-url="{{ route('api.duel.answer', $session) }}"
    data-finish-url="{{ route('api.duel.finish', $session) }}"
    data-lobby-url="{{ route('learning.duel.lobby') }}"
    data-csrf="{{ csrf_token() }}"
>
    <header class="duel-room-top">
        <a href="{{ route('learning.duel.lobby') }}" class="duel-room-link">← Lobby</a>
        <div>
            <small>Room</small>
            <b>#{{ $session->code }}</b>
        </div>
    </header>

    <main class="duel-arena-card">
        <section class="duel-vs-stage" data-screen="vs">
            <div class="duel-profile-card left" data-player-card="0">
                <span class="duel-avatar">?</span>
                <small>Player A</small>
                <h2>{{ $session->playerOne?->name ?? 'Player A' }}</h2>
                <p data-player-score="0">0 pts</p>
            </div>

            <div class="duel-vs-center">
                <span class="duel-lightning">⚡</span>
                <strong>VS</strong>
                <em data-countdown-text>Siap?</em>
            </div>

            <div class="duel-profile-card right" data-player-card="1">
                <span class="duel-avatar">?</span>
                <small>Player B</small>
                <h2>{{ $session->playerTwo?->name ?? 'Player B' }}</h2>
                <p data-player-score="1">0 pts</p>
            </div>
        </section>

        <section class="duel-quiz-stage" data-screen="quiz" hidden>
            <div class="duel-quiz-head">
                <div>
                    <small data-question-type>Mix</small>
                    <h1 data-question-order>Soal 1/10</h1>
                </div>
                <div class="duel-timer">
                    <span data-timer-bar></span>
                    <b data-timer-text>10</b>
                </div>
            </div>

            <article class="duel-question-box">
                <p data-question-prompt>Prompt</p>
                <h2 data-question-text>Question</h2>
            </article>

            <div class="duel-options-grid" data-options-grid></div>

            <div class="duel-feedback" data-feedback>Jawab secepat mungkin untuk bonus poin.</div>

            <div class="duel-live-score">
                <div data-mini-player="0">
                    <span>Player A</span>
                    <b>0</b>
                </div>
                <div data-mini-player="1">
                    <span>Player B</span>
                    <b>0</b>
                </div>
            </div>
        </section>

        <section class="duel-result-stage" data-screen="result" hidden>
            <span class="duel-result-badge" data-result-label>Hasil</span>
            <h1 data-result-title>Match selesai</h1>
            <p data-result-subtitle>Menunggu hasil akhir...</p>

            <div class="duel-result-scoreboard" data-result-scoreboard></div>

            <a href="{{ route('learning.duel.lobby') }}" class="duel-finish-btn">Kembali ke Lobby</a>
        </section>
    </main>
</div>
@endsection

@push('styles')
<style>
    .duel-room-page {
        min-height: 100vh;
        padding: 1rem;
        background:
            radial-gradient(circle at 18% 10%, rgba(102, 232, 247, .14), transparent 24rem),
            radial-gradient(circle at 84% 16%, rgba(110, 124, 247, .2), transparent 30rem),
            radial-gradient(circle at 50% 92%, rgba(73, 211, 139, .08), transparent 25rem),
            #050814;
    }

    .duel-room-top,
    .duel-arena-card {
        width: min(1100px, 100%);
        margin-inline: auto;
    }

    .duel-room-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .duel-room-link,
    .duel-room-top > div {
        border: 1px solid var(--border);
        border-radius: 999px;
        background: rgba(255, 255, 255, .055);
        padding: .72rem 1rem;
        color: var(--text);
        font-weight: 950;
    }

    .duel-room-top small {
        color: var(--muted);
        margin-right: .4rem;
        font-weight: 900;
    }

    .duel-arena-card {
        min-height: calc(100vh - 7rem);
        display: grid;
        align-items: center;
        border: 1px solid var(--border);
        border-radius: 36px;
        background: linear-gradient(145deg, rgba(25, 33, 49, .88), rgba(8, 13, 26, .9));
        box-shadow: var(--shadow);
        padding: clamp(1rem, 4vw, 2rem);
        overflow: hidden;
        position: relative;
    }

    .duel-arena-card::before {
        content: "";
        position: absolute;
        inset: -30%;
        background:
            radial-gradient(circle at 25% 30%, rgba(102, 232, 247, .16), transparent 20%),
            radial-gradient(circle at 75% 30%, rgba(110, 124, 247, .18), transparent 24%);
        animation: arenaGlow 5s ease-in-out infinite alternate;
    }

    @keyframes arenaGlow {
        from { transform: translateX(-2%) scale(1); opacity: .65; }
        to { transform: translateX(2%) scale(1.04); opacity: .95; }
    }

    .duel-vs-stage,
    .duel-quiz-stage,
    .duel-result-stage {
        position: relative;
        z-index: 1;
    }

    .duel-vs-stage {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 220px minmax(0, 1fr);
        gap: 1rem;
        align-items: center;
    }

    .duel-profile-card {
        min-height: 390px;
        display: grid;
        place-items: center;
        text-align: center;
        border: 1px solid var(--border);
        border-radius: 34px;
        background: rgba(255, 255, 255, .055);
        padding: 1.4rem;
        animation: profileIn .75s cubic-bezier(.2,.8,.2,1) both;
    }

    .duel-profile-card.right {
        animation-delay: .12s;
    }

    @keyframes profileIn {
        from { opacity: 0; transform: translateY(2rem) scale(.96); filter: blur(12px); }
        to { opacity: 1; transform: translateY(0) scale(1); filter: blur(0); }
    }

    .duel-avatar {
        display: grid;
        place-items: center;
        width: 8.2rem;
        height: 8.2rem;
        border-radius: 2.4rem;
        background: linear-gradient(135deg, var(--cyan), var(--primary));
        color: #07101f;
        font-size: 3.5rem;
        font-weight: 950;
        box-shadow: 0 24px 60px rgba(102, 232, 247, .18);
    }

    .duel-profile-card small {
        color: var(--cyan);
        font-weight: 950;
        letter-spacing: .16em;
        text-transform: uppercase;
    }

    .duel-profile-card h2 {
        font-size: clamp(1.7rem, 4vw, 3rem);
        letter-spacing: -.07em;
    }

    .duel-profile-card p {
        color: var(--muted);
        font-weight: 950;
    }

    .duel-vs-center {
        display: grid;
        place-items: center;
        gap: .4rem;
        text-align: center;
    }

    .duel-lightning {
        display: grid;
        place-items: center;
        width: 4rem;
        height: 4rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, .08);
        border: 1px solid var(--border);
        animation: pulseVs 1s infinite alternate;
    }

    @keyframes pulseVs {
        from { transform: scale(.96); box-shadow: 0 0 0 rgba(102,232,247,0); }
        to { transform: scale(1.06); box-shadow: 0 0 34px rgba(102,232,247,.25); }
    }

    .duel-vs-center strong {
        font-size: clamp(3rem, 8vw, 6.5rem);
        line-height: .85;
        letter-spacing: -.12em;
        background: linear-gradient(135deg, #fff, var(--cyan), var(--primary));
        -webkit-background-clip: text;
        color: transparent;
    }

    .duel-vs-center em {
        color: var(--muted);
        font-style: normal;
        font-weight: 950;
    }

    .duel-quiz-stage {
        width: min(820px, 100%);
        margin-inline: auto;
    }

    .duel-quiz-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .duel-quiz-head small {
        color: var(--cyan);
        font-size: .78rem;
        font-weight: 950;
        letter-spacing: .16em;
        text-transform: uppercase;
    }

    .duel-quiz-head h1 {
        font-size: clamp(1.5rem, 4vw, 2.6rem);
        letter-spacing: -.06em;
    }

    .duel-timer {
        position: relative;
        width: 92px;
        height: 92px;
        display: grid;
        place-items: center;
        border: 1px solid var(--border);
        border-radius: 999px;
        background: rgba(255, 255, 255, .06);
        overflow: hidden;
    }

    .duel-timer span {
        position: absolute;
        inset: auto 0 0 0;
        height: 100%;
        background: linear-gradient(180deg, rgba(102, 232, 247, .22), rgba(110, 124, 247, .34));
        transform-origin: bottom;
    }

    .duel-timer b {
        position: relative;
        z-index: 1;
        font-size: 2rem;
    }

    .duel-question-box {
        border: 1px solid var(--border);
        border-radius: 28px;
        background: rgba(255, 255, 255, .06);
        padding: clamp(1rem, 3vw, 1.5rem);
        margin-bottom: 1rem;
    }

    .duel-question-box p {
        color: var(--cyan);
        font-weight: 950;
        text-transform: uppercase;
        letter-spacing: .13em;
        font-size: .75rem;
        margin-bottom: .6rem;
    }

    .duel-question-box h2 {
        font-size: clamp(1.3rem, 4vw, 2.2rem);
        letter-spacing: -.04em;
        line-height: 1.18;
    }

    .duel-options-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .75rem;
    }

    .duel-option-btn {
        min-height: 82px;
        border: 1px solid var(--border);
        border-radius: 22px;
        background: rgba(255, 255, 255, .055);
        color: var(--text);
        padding: 1rem;
        text-align: left;
        font-size: 1rem;
        font-weight: 900;
        cursor: pointer;
        transition: .18s ease;
    }

    .duel-option-btn:hover:not(:disabled) {
        transform: translateY(-3px);
        border-color: rgba(102, 232, 247, .45);
        background: rgba(102, 232, 247, .08);
    }

    .duel-option-btn:disabled {
        cursor: not-allowed;
        opacity: .75;
    }

    .duel-option-btn.is-correct {
        border-color: rgba(73, 211, 139, .7);
        background: rgba(73, 211, 139, .15);
    }

    .duel-option-btn.is-wrong {
        border-color: rgba(255, 107, 138, .7);
        background: rgba(255, 107, 138, .13);
    }

    .duel-feedback {
        margin-top: 1rem;
        min-height: 48px;
        color: var(--muted);
        font-weight: 850;
    }

    .duel-live-score {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: .75rem;
        margin-top: .5rem;
    }

    .duel-live-score div {
        border: 1px solid var(--border);
        border-radius: 18px;
        background: rgba(255, 255, 255, .05);
        padding: .85rem;
    }

    .duel-live-score span {
        display: block;
        color: var(--muted);
        font-weight: 800;
        font-size: .82rem;
    }

    .duel-live-score b {
        font-size: 1.35rem;
    }

    .duel-result-stage {
        width: min(760px, 100%);
        margin-inline: auto;
        text-align: center;
    }

    .duel-result-badge {
        display: inline-flex;
        margin-bottom: .8rem;
        border: 1px solid rgba(102,232,247,.3);
        border-radius: 999px;
        background: rgba(102,232,247,.08);
        color: var(--cyan);
        padding: .5rem .8rem;
        font-weight: 950;
    }

    .duel-result-stage h1 {
        font-size: clamp(2rem, 6vw, 4.2rem);
        letter-spacing: -.08em;
        line-height: .95;
    }

    .duel-result-stage p {
        color: var(--muted);
        margin-top: .8rem;
        font-weight: 850;
    }

    .duel-result-scoreboard {
        display: grid;
        gap: .7rem;
        margin: 1.4rem 0;
    }

    .duel-result-row {
        display: grid;
        grid-template-columns: 1fr auto auto;
        align-items: center;
        gap: 1rem;
        border: 1px solid var(--border);
        border-radius: 20px;
        background: rgba(255,255,255,.055);
        padding: .95rem;
        text-align: left;
    }

    .duel-result-row b {
        font-size: 1.2rem;
    }

    .duel-result-row em {
        font-style: normal;
        color: var(--cyan);
        font-weight: 950;
    }

    .duel-finish-btn {
        display: inline-flex;
        border-radius: 999px;
        background: linear-gradient(135deg, var(--cyan), var(--primary));
        color: #07101f;
        padding: .9rem 1.2rem;
        font-weight: 950;
    }

    @media (max-width: 820px) {
        .duel-vs-stage,
        .duel-options-grid,
        .duel-live-score {
            grid-template-columns: 1fr;
        }

        .duel-vs-center {
            order: -1;
        }

        .duel-profile-card {
            min-height: 260px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
(() => {
    const root = document.querySelector('.duel-room-page');
    const urls = {
        state: root.dataset.stateUrl,
        answer: root.dataset.answerUrl,
        finish: root.dataset.finishUrl,
        lobby: root.dataset.lobbyUrl,
    };
    const csrf = root.dataset.csrf;

    let state = null;
    let questions = [];
    let currentIndex = 0;
    let secondsPerQuestion = 10;
    let timer = null;
    let tickStartedAt = 0;
    let answered = false;
    let statePoller = null;

    const screens = {
        vs: document.querySelector('[data-screen="vs"]'),
        quiz: document.querySelector('[data-screen="quiz"]'),
        result: document.querySelector('[data-screen="result"]'),
    };

    const showScreen = (name) => {
        Object.entries(screens).forEach(([key, el]) => el.hidden = key !== name);
    };

    const fetchState = async () => {
        const res = await fetch(urls.state, { headers: { 'Accept': 'application/json' } });
        state = await res.json();
        questions = state.questions || [];
        secondsPerQuestion = Number(state.session.seconds_per_question || 10);
        renderPlayers();
        return state;
    };

    const renderPlayers = () => {
        if (!state) return;
        state.players.forEach((player, index) => {
            const card = document.querySelector(`[data-player-card="${index}"]`);
            const mini = document.querySelector(`[data-mini-player="${index}"]`);
            if (card) {
                card.querySelector('.duel-avatar').textContent = player.initial || '?';
                card.querySelector('h2').textContent = player.name;
                card.querySelector('p').textContent = `${player.score} pts`;
            }
            if (mini) {
                mini.querySelector('span').textContent = player.name;
                mini.querySelector('b').textContent = player.score;
            }
        });
    };

    const countdown = async () => {
        showScreen('vs');
        const text = document.querySelector('[data-countdown-text]');
        const steps = ['3', '2', '1', 'Mulai!'];

        for (const step of steps) {
            text.textContent = step;
            text.style.transform = 'scale(1.22)';
            window.setTimeout(() => text.style.transform = 'scale(1)', 180);
            await new Promise(resolve => window.setTimeout(resolve, 850));
        }

        startQuiz();
    };

    const startQuiz = () => {
        showScreen('quiz');
        currentIndex = 0;
        renderQuestion();
    };

    const renderQuestion = () => {
        answered = false;

        if (currentIndex >= questions.length) {
            finishMatch();
            return;
        }

        const q = questions[currentIndex];
        document.querySelector('[data-question-type]').textContent = q.type.replaceAll('_', ' ');
        document.querySelector('[data-question-order]').textContent = `Soal ${currentIndex + 1}/${questions.length}`;
        document.querySelector('[data-question-prompt]').textContent = q.prompt || 'Mix Question';
        document.querySelector('[data-question-text]').textContent = q.question_text;
        document.querySelector('[data-feedback]').textContent = 'Jawab secepat mungkin untuk bonus poin.';

        const grid = document.querySelector('[data-options-grid]');
        grid.innerHTML = '';

        q.options.forEach((option, index) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'duel-option-btn';
            btn.textContent = `${String.fromCharCode(65 + index)}. ${option}`;
            btn.addEventListener('click', () => submitAnswer(option, btn));
            grid.appendChild(btn);
        });

        startTimer();
    };

    const startTimer = () => {
        window.clearInterval(timer);
        tickStartedAt = Date.now();
        let remaining = secondsPerQuestion;
        const timerText = document.querySelector('[data-timer-text]');
        const timerBar = document.querySelector('[data-timer-bar]');

        const paint = () => {
            timerText.textContent = remaining;
            timerBar.style.transform = `scaleY(${remaining / secondsPerQuestion})`;
        };

        paint();

        timer = window.setInterval(() => {
            const elapsed = Math.floor((Date.now() - tickStartedAt) / 1000);
            remaining = Math.max(secondsPerQuestion - elapsed, 0);
            paint();

            if (remaining <= 0) {
                window.clearInterval(timer);
                if (!answered) {
                    submitAnswer(null, null);
                }
            }
        }, 200);
    };

    const submitAnswer = async (selected, clickedButton) => {
        if (answered) return;
        answered = true;
        window.clearInterval(timer);

        document.querySelectorAll('.duel-option-btn').forEach(btn => btn.disabled = true);

        const elapsedMs = Math.min(Date.now() - tickStartedAt, secondsPerQuestion * 1000);
        const q = questions[currentIndex];

        const res = await fetch(urls.answer, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                question_id: q.id,
                selected_answer: selected,
                answer_time_ms: Math.round(elapsedMs),
            }),
        });

        const payload = await res.json();

        document.querySelectorAll('.duel-option-btn').forEach(btn => {
            const raw = btn.textContent.replace(/^[A-D]\.\s/, '');
            if (raw === payload.correct_answer) {
                btn.classList.add('is-correct');
            }
        });

        if (clickedButton && !payload.is_correct) {
            clickedButton.classList.add('is-wrong');
        }

        const feedback = document.querySelector('[data-feedback]');

        if (payload.is_correct) {
            feedback.textContent = `Benar! +${payload.score_awarded} poin. ${payload.explanation || ''}`;
        } else if (selected === null) {
            feedback.textContent = `Waktu habis. Jawaban benar: ${payload.correct_answer}`;
        } else {
            feedback.textContent = `Salah. Jawaban benar: ${payload.correct_answer}`;
        }

        await fetchState();

        window.setTimeout(() => {
            currentIndex += 1;
            renderQuestion();
        }, 950);
    };

    const finishMatch = async () => {
        showScreen('result');
        document.querySelector('[data-result-title]').textContent = 'Menghitung hasil...';
        document.querySelector('[data-result-subtitle]').textContent = 'Menunggu jawaban lawan jika belum selesai.';

        const res = await fetch(urls.finish, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
            },
        });

        state = await res.json();
        renderResult();

        window.clearInterval(statePoller);
        statePoller = window.setInterval(async () => {
            await fetchState();

            if (state.session.status === 'finished') {
                window.clearInterval(statePoller);
                renderResult();
            }
        }, 1600);
    };

    const renderResult = () => {
        showScreen('result');
        const me = state.players.find(p => Number(p.user_id) === Number(state.current_user_id));
        const title = document.querySelector('[data-result-title]');
        const subtitle = document.querySelector('[data-result-subtitle]');
        const label = document.querySelector('[data-result-label]');

        if (state.session.status !== 'finished') {
            label.textContent = 'Waiting';
            title.textContent = 'Menunggu lawan';
            subtitle.textContent = 'Kalau lawan belum selesai, hasil final akan muncul otomatis.';
        } else if (me?.result === 'win') {
            label.textContent = 'Victory';
            title.textContent = 'Kamu Menang!';
            subtitle.textContent = 'Rating dan history duel sudah diperbarui.';
        } else if (me?.result === 'lose') {
            label.textContent = 'Defeat';
            title.textContent = 'Kamu Kalah';
            subtitle.textContent = 'Coba lagi untuk naik rank.';
        } else {
            label.textContent = 'Draw';
            title.textContent = 'Seri';
            subtitle.textContent = 'Skor sama kuat.';
        }

        const board = document.querySelector('[data-result-scoreboard]');
        board.innerHTML = '';
        state.players.forEach(player => {
            const row = document.createElement('div');
            row.className = 'duel-result-row';
            row.innerHTML = `
                <b>${player.name}</b>
                <span>${player.correct_count} benar</span>
                <em>${player.score} pts</em>
            `;
            board.appendChild(row);
        });
    };

    fetchState()
        .then(countdown)
        .catch(() => {
            document.querySelector('[data-countdown-text]').textContent = 'Gagal memuat duel.';
        });
})();
</script>
@endpush
