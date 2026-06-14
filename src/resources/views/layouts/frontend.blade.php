<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="YoLearning adalah platform belajar bahasa berbasis quiz, progress, dan tantangan.">
    <title>@yield('title', 'YoLearning - Language Learning Platform')</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900" rel="stylesheet" />

    <style>
        :root {
            --navy-950: #020617;
            --navy-900: #07111f;
            --navy-850: #09182b;
            --navy-800: #0b1f38;
            --blue-700: #1d4ed8;
            --blue-500: #3b82f6;
            --cyan-300: #67e8f9;
            --indigo-300: #a5b4fc;
            --violet-400: #a78bfa;
            --rose-300: #f9a8d4;
            --slate-100: #f1f5f9;
            --slate-300: #cbd5e1;
            --slate-400: #94a3b8;
            --white: #ffffff;
            --glass: rgba(255, 255, 255, 0.08);
            --glass-border: rgba(255, 255, 255, 0.16);
            --max-width: 1180px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            min-height: 100vh;
            overflow-x: hidden;
            background:
                radial-gradient(circle at 18% 12%, rgba(59, 130, 246, 0.34), transparent 34rem),
                radial-gradient(circle at 84% 30%, rgba(167, 139, 250, 0.22), transparent 28rem),
                linear-gradient(180deg, #020617 0%, #07111f 45%, #030712 100%);
            color: var(--slate-100);
            font-family: 'Figtree', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            cursor: none;
        }

        a, button {
            cursor: none;
        }

        img, svg {
            display: block;
            max-width: 100%;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .site-shell {
            position: relative;
            min-height: 100vh;
            isolation: isolate;
        }

        .site-shell::before {
            content: '';
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: -2;
            background-image:
                linear-gradient(rgba(148, 163, 184, 0.035) 1px, transparent 1px),
                linear-gradient(90deg, rgba(148, 163, 184, 0.035) 1px, transparent 1px);
            background-size: 72px 72px;
            mask-image: linear-gradient(to bottom, rgba(0, 0, 0, 0.85), transparent 88%);
        }

        .aurora {
            position: fixed;
            inset: auto -10rem -14rem -10rem;
            z-index: -1;
            height: 32rem;
            pointer-events: none;
            background:
                radial-gradient(circle at 20% 30%, rgba(59, 130, 246, 0.22), transparent 30%),
                radial-gradient(circle at 50% 10%, rgba(103, 232, 249, 0.14), transparent 32%),
                radial-gradient(circle at 78% 40%, rgba(249, 168, 212, 0.12), transparent 28%);
            filter: blur(12px);
        }

        .container {
            width: min(var(--max-width), calc(100% - 2rem));
            margin-inline: auto;
        }

        .navbar {
            position: sticky;
            top: 0;
            z-index: 50;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(2, 6, 23, 0.74);
            backdrop-filter: blur(18px);
        }

        .navbar-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 4.6rem;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 900;
            letter-spacing: -0.04em;
            font-size: clamp(1.1rem, 2vw, 1.4rem);
        }

        .brand-mark {
            width: 2.35rem;
            height: 2.35rem;
            display: grid;
            place-items: center;
            border-radius: 0.9rem;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.92), rgba(103, 232, 249, 0.68));
            box-shadow: 0 0 30px rgba(59, 130, 246, 0.42);
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 0.7rem;
        }

        .nav-link {
            color: rgba(241, 245, 249, 0.82);
            font-size: 0.95rem;
            font-weight: 800;
            padding: 0.75rem 0.95rem;
            border-radius: 999px;
            transition: 180ms ease;
        }

        .nav-link:hover {
            color: var(--white);
            background: rgba(255, 255, 255, 0.08);
        }

        .nav-button {
            color: var(--navy-950);
            background: linear-gradient(135deg, var(--cyan-300), var(--blue-500));
            box-shadow: 0 14px 34px rgba(59, 130, 246, 0.32);
        }

        .nav-button:hover {
            color: var(--navy-950);
            transform: translateY(-1px);
            background: linear-gradient(135deg, #a5f3fc, #60a5fa);
        }

        .section {
            position: relative;
            padding: clamp(4.5rem, 9vw, 7.5rem) 0;
        }

        .hero-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.03fr) minmax(20rem, 0.97fr);
            gap: clamp(2rem, 5vw, 5rem);
            align-items: center;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            padding: 0.48rem 0.8rem;
            border: 1px solid rgba(103, 232, 249, 0.26);
            border-radius: 999px;
            background: rgba(8, 47, 73, 0.34);
            color: #cffafe;
            font-size: 0.83rem;
            font-weight: 800;
            letter-spacing: 0.02em;
        }

        .pulse-dot {
            width: 0.55rem;
            height: 0.55rem;
            border-radius: 999px;
            background: var(--cyan-300);
            box-shadow: 0 0 16px rgba(103, 232, 249, 0.9);
        }

        .hero-title {
            margin-top: 1.35rem;
            max-width: 58rem;
            font-size: clamp(2.8rem, 7vw, 6.8rem);
            line-height: 0.91;
            letter-spacing: -0.075em;
            font-weight: 950;
        }

        .text-gradient {
            background: linear-gradient(135deg, #ffffff 0%, #bfdbfe 30%, #67e8f9 64%, #a5b4fc 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .hero-copy {
            margin-top: 1.5rem;
            max-width: 39rem;
            color: rgba(203, 213, 225, 0.88);
            font-size: clamp(1rem, 2vw, 1.16rem);
            line-height: 1.78;
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.9rem;
            margin-top: 2rem;
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.55rem;
            min-height: 3.15rem;
            border: 1px solid transparent;
            border-radius: 999px;
            padding: 0.8rem 1.25rem;
            font-weight: 900;
            transition: 180ms ease;
        }

        .button-primary {
            color: #03111f;
            background: linear-gradient(135deg, #67e8f9, #3b82f6 52%, #a78bfa);
            box-shadow: 0 24px 55px rgba(37, 99, 235, 0.35);
        }

        .button-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 28px 64px rgba(37, 99, 235, 0.48);
        }

        .button-ghost {
            border-color: rgba(255, 255, 255, 0.18);
            background: rgba(255, 255, 255, 0.06);
            color: rgba(248, 250, 252, 0.92);
        }

        .button-ghost:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        .hero-card {
            position: relative;
            min-height: 32rem;
            border: 1px solid var(--glass-border);
            border-radius: 2.2rem;
            overflow: hidden;
            background:
                radial-gradient(circle at 30% 12%, rgba(103, 232, 249, 0.18), transparent 25%),
                linear-gradient(145deg, rgba(15, 23, 42, 0.9), rgba(8, 47, 73, 0.52));
            box-shadow: 0 40px 100px rgba(0, 0, 0, 0.38), inset 0 1px rgba(255, 255, 255, 0.1);
        }

        .hero-card::before {
            content: '';
            position: absolute;
            inset: -7rem auto auto -5rem;
            width: 18rem;
            height: 18rem;
            border-radius: 999px;
            background: rgba(59, 130, 246, 0.33);
            filter: blur(18px);
        }

        .hero-card-content {
            position: relative;
            display: grid;
            height: 100%;
            min-height: 32rem;
            padding: clamp(1.25rem, 4vw, 2rem);
        }

        .study-window {
            align-self: start;
            border: 1px solid rgba(255, 255, 255, 0.13);
            border-radius: 1.5rem;
            background: rgba(2, 6, 23, 0.42);
            overflow: hidden;
            backdrop-filter: blur(12px);
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.24);
        }

        .window-bar {
            display: flex;
            gap: 0.42rem;
            padding: 0.85rem 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .window-dot {
            width: 0.67rem;
            height: 0.67rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.3);
        }

        .window-body {
            padding: 1.1rem;
        }

        .lesson-preview {
            display: grid;
            gap: 0.9rem;
        }

        .lesson-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.95rem;
            border-radius: 1rem;
            background: rgba(255, 255, 255, 0.07);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .lesson-row strong {
            font-size: 0.9rem;
        }

        .lesson-row span {
            color: rgba(203, 213, 225, 0.72);
            font-size: 0.78rem;
            font-weight: 700;
        }

        .lesson-score {
            color: #67e8f9;
            font-weight: 950;
        }

        .floating-badge {
            position: absolute;
            right: 1.4rem;
            bottom: 1.35rem;
            max-width: 16rem;
            padding: 1.05rem;
            border: 1px solid rgba(103, 232, 249, 0.24);
            border-radius: 1.35rem;
            background: rgba(8, 47, 73, 0.56);
            backdrop-filter: blur(12px);
        }

        .floating-badge p:first-child {
            color: #cffafe;
            font-size: 0.78rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.12em;
        }

        .floating-badge p:last-child {
            margin-top: 0.45rem;
            color: rgba(241, 245, 249, 0.9);
            line-height: 1.45;
            font-weight: 700;
        }

        .split-section {
            overflow: hidden;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            background:
                radial-gradient(circle at 80% 20%, rgba(103, 232, 249, 0.14), transparent 30rem),
                linear-gradient(135deg, rgba(8, 47, 73, 0.36), rgba(2, 6, 23, 0.4));
        }

        .split-grid {
            display: grid;
            grid-template-columns: minmax(0, 0.86fr) minmax(20rem, 1.14fr);
            gap: clamp(2rem, 6vw, 5rem);
            align-items: center;
        }

        .section-kicker {
            color: var(--cyan-300);
            text-transform: uppercase;
            letter-spacing: 0.16em;
            font-size: 0.78rem;
            font-weight: 950;
        }

        .section-title {
            margin-top: 0.85rem;
            font-size: clamp(2rem, 5vw, 4.4rem);
            line-height: 0.98;
            letter-spacing: -0.055em;
            font-weight: 950;
        }

        .section-copy {
            margin-top: 1.15rem;
            max-width: 39rem;
            color: rgba(203, 213, 225, 0.84);
            line-height: 1.75;
            font-size: 1.02rem;
        }

        .language-cloud {
            position: relative;
            min-height: 29rem;
        }

        .language-card {
            position: absolute;
            width: 12.5rem;
            min-height: 9rem;
            padding: 1.05rem;
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: 1.4rem;
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 26px 70px rgba(0, 0, 0, 0.25);
            backdrop-filter: blur(14px);
            transition: 220ms ease;
        }

        .language-card:hover {
            transform: translateY(-7px) rotate(0deg) !important;
            border-color: rgba(103, 232, 249, 0.46);
            background: rgba(255, 255, 255, 0.12);
        }

        .language-card:nth-child(1) { top: 0.2rem; left: 8%; transform: rotate(-28deg); }
        .language-card:nth-child(2) { top: 2rem; left: 43%; transform: rotate(13deg); }
        .language-card:nth-child(3) { top: 9.5rem; left: 19%; transform: rotate(-10deg); }
        .language-card:nth-child(4) { top: 11.2rem; left: 58%; transform: rotate(24deg); }
        .language-card:nth-child(5) { top: 20rem; left: 5%; transform: rotate(13deg); }
        .language-card:nth-child(6) { top: 20.6rem; left: 43%; transform: rotate(-17deg); }

        .language-accent {
            color: #e0f2fe;
            font-weight: 950;
            font-size: 1.05rem;
        }

        .language-name {
            margin-top: 0.65rem;
            font-weight: 950;
            font-size: 1.12rem;
        }

        .language-desc {
            margin-top: 0.35rem;
            color: rgba(203, 213, 225, 0.76);
            font-size: 0.78rem;
            line-height: 1.4;
        }

        .language-status {
            display: inline-flex;
            margin-top: 0.8rem;
            padding: 0.28rem 0.58rem;
            border-radius: 999px;
            background: rgba(103, 232, 249, 0.12);
            color: #cffafe;
            font-size: 0.68rem;
            font-weight: 950;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .tournament-section {
            background:
                radial-gradient(circle at 22% 25%, rgba(30, 64, 175, 0.28), transparent 24rem),
                radial-gradient(circle at 80% 80%, rgba(124, 58, 237, 0.12), transparent 28rem),
                rgba(3, 7, 18, 0.72);
        }

        .tournament-grid {
            display: grid;
            grid-template-columns: minmax(19rem, 0.88fr) minmax(0, 1.12fr);
            gap: clamp(2rem, 6vw, 5rem);
            align-items: center;
        }

        .arena-card {
            position: relative;
            min-height: 26rem;
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: 2rem;
            overflow: hidden;
            background:
                linear-gradient(145deg, rgba(15, 23, 42, 0.82), rgba(30, 64, 175, 0.2)),
                radial-gradient(circle at 35% 30%, rgba(103, 232, 249, 0.18), transparent 18rem);
            box-shadow: 0 38px 90px rgba(0, 0, 0, 0.34);
        }

        .arena-ring {
            position: absolute;
            inset: 3rem;
            border: 1px solid rgba(103, 232, 249, 0.2);
            border-radius: 50%;
            box-shadow: inset 0 0 54px rgba(59, 130, 246, 0.2), 0 0 44px rgba(59, 130, 246, 0.18);
        }

        .arena-trophy {
            position: absolute;
            inset: 0;
            display: grid;
            place-items: center;
            text-align: center;
            padding: 2rem;
        }

        .trophy-icon {
            display: grid;
            place-items: center;
            width: 8.5rem;
            height: 8.5rem;
            margin-inline: auto;
            border-radius: 2rem;
            background: linear-gradient(135deg, rgba(103, 232, 249, 0.2), rgba(59, 130, 246, 0.12));
            border: 1px solid rgba(255, 255, 255, 0.14);
            font-size: 3.4rem;
            box-shadow: 0 22px 60px rgba(59, 130, 246, 0.25);
        }

        .trophy-label {
            margin-top: 1.25rem;
            color: rgba(241, 245, 249, 0.9);
            font-weight: 950;
            letter-spacing: -0.03em;
        }

        .feature-list {
            display: grid;
            gap: 1rem;
            margin-top: 1.8rem;
        }

        .feature-item {
            display: flex;
            align-items: flex-start;
            gap: 0.85rem;
            padding: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.11);
            border-radius: 1.15rem;
            background: rgba(255, 255, 255, 0.055);
        }

        .feature-icon {
            width: 2.3rem;
            height: 2.3rem;
            flex: 0 0 auto;
            display: grid;
            place-items: center;
            border-radius: 0.8rem;
            background: rgba(103, 232, 249, 0.12);
            color: var(--cyan-300);
            font-weight: 950;
        }

        .feature-item strong {
            display: block;
            font-size: 1rem;
        }

        .feature-item p {
            margin-top: 0.22rem;
            color: rgba(203, 213, 225, 0.78);
            line-height: 1.55;
            font-size: 0.94rem;
        }

        .cta-section {
            text-align: center;
            background:
                linear-gradient(135deg, rgba(29, 78, 216, 0.26), rgba(2, 6, 23, 0.4)),
                radial-gradient(circle at 50% 30%, rgba(103, 232, 249, 0.16), transparent 30rem);
        }

        .cta-card {
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: 2rem;
            padding: clamp(2rem, 5vw, 4rem);
            background: rgba(255, 255, 255, 0.07);
            box-shadow: 0 36px 90px rgba(0, 0, 0, 0.28);
        }

        .cta-card::before {
            content: '';
            position: absolute;
            inset: -8rem 28% auto;
            height: 14rem;
            background: rgba(103, 232, 249, 0.18);
            filter: blur(54px);
        }

        .cta-card > * {
            position: relative;
        }

        .footer {
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(2, 6, 23, 0.9);
        }

        .footer-inner {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1.65rem 0;
            color: rgba(203, 213, 225, 0.76);
            font-weight: 700;
            font-size: 0.92rem;
        }

        .cursor-glow,
        .cursor-dot {
            position: fixed;
            left: 0;
            top: 0;
            pointer-events: none;
            z-index: 9999;
            transform: translate(-50%, -50%);
            opacity: 0;
            transition: opacity 160ms ease, transform 80ms ease;
        }

        .cursor-glow {
            width: 3.6rem;
            height: 3.6rem;
            border-radius: 999px;
            background:
                radial-gradient(circle, rgba(255, 255, 220, 0.72) 0%, rgba(103, 232, 249, 0.44) 18%, rgba(59, 130, 246, 0.22) 39%, transparent 70%);
            filter: blur(2px);
            mix-blend-mode: screen;
        }

        .cursor-dot {
            width: 0.42rem;
            height: 0.42rem;
            border-radius: 999px;
            background: #fef9c3;
            box-shadow: 0 0 10px #fef9c3, 0 0 18px rgba(103, 232, 249, 0.75);
        }

        body.cursor-ready .cursor-glow,
        body.cursor-ready .cursor-dot {
            opacity: 1;
        }

        body.cursor-click .cursor-glow {
            transform: translate(-50%, -50%) scale(0.76);
        }

        @media (max-width: 920px) {
            body,
            a,
            button {
                cursor: auto;
            }

            .cursor-glow,
            .cursor-dot {
                display: none;
            }

            .hero-grid,
            .split-grid,
            .tournament-grid {
                grid-template-columns: 1fr;
            }

            .hero-card {
                min-height: 26rem;
            }

            .hero-card-content {
                min-height: 26rem;
            }

            .language-cloud {
                min-height: auto;
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 1rem;
            }

            .language-card {
                position: relative;
                inset: auto !important;
                width: auto;
                min-height: 10rem;
                transform: none !important;
            }
        }

        @media (max-width: 620px) {
            .container {
                width: min(100% - 1rem, var(--max-width));
            }

            .navbar-inner {
                min-height: 4.15rem;
            }

            .brand span:last-child {
                font-size: 1rem;
            }

            .brand-mark {
                width: 2rem;
                height: 2rem;
                border-radius: 0.75rem;
            }

            .nav-links {
                gap: 0.25rem;
            }

            .nav-link {
                padding: 0.58rem 0.62rem;
                font-size: 0.8rem;
            }

            .section {
                padding: 3.6rem 0;
            }

            .hero-title {
                font-size: clamp(2.65rem, 17vw, 4.4rem);
            }

            .hero-actions {
                align-items: stretch;
                flex-direction: column;
            }

            .button {
                width: 100%;
            }

            .floating-badge {
                left: 1rem;
                right: 1rem;
            }

            .language-cloud {
                grid-template-columns: 1fr;
            }

            .arena-card {
                min-height: 22rem;
            }

            .footer-inner {
                justify-content: center;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="cursor-glow" aria-hidden="true"></div>
    <div class="cursor-dot" aria-hidden="true"></div>

    <div class="site-shell">
        <div class="aurora" aria-hidden="true"></div>

        <header class="navbar">
            <div class="container navbar-inner">
                <a href="{{ route('home') }}" class="brand" aria-label="YoLearning Home">
                    <span class="brand-mark">Y</span>
                    <span>YoLearning</span>
                </a>

                <nav class="nav-links" aria-label="Navigasi utama">
                    <a href="#languages" class="nav-link">Daftar</a>
                    <a href="/admin" class="nav-link nav-button">Login</a>
                </nav>
            </div>
        </header>

        <main>
            @yield('content')
        </main>

        <footer class="footer">
            <div class="container footer-inner">
                <p>© {{ date('Y') }} YoLearning. Semua progres belajar tersimpan rapi.</p>
                <p>Belajar bahasa • Quiz • Tournament</p>
            </div>
        </footer>
    </div>

    <script>
        (() => {
            const finePointer = window.matchMedia('(pointer: fine)').matches;
            if (!finePointer) return;

            const glow = document.querySelector('.cursor-glow');
            const dot = document.querySelector('.cursor-dot');
            if (!glow || !dot) return;

            let mouseX = window.innerWidth / 2;
            let mouseY = window.innerHeight / 2;
            let glowX = mouseX;
            let glowY = mouseY;

            const moveCursor = () => {
                glowX += (mouseX - glowX) * 0.18;
                glowY += (mouseY - glowY) * 0.18;

                glow.style.left = `${glowX}px`;
                glow.style.top = `${glowY}px`;
                dot.style.left = `${mouseX}px`;
                dot.style.top = `${mouseY}px`;

                requestAnimationFrame(moveCursor);
            };

            window.addEventListener('pointermove', (event) => {
                mouseX = event.clientX;
                mouseY = event.clientY;
                document.body.classList.add('cursor-ready');
            }, { passive: true });

            window.addEventListener('pointerdown', () => {
                document.body.classList.add('cursor-click');
            });

            window.addEventListener('pointerup', () => {
                document.body.classList.remove('cursor-click');
            });

            window.addEventListener('pointerleave', () => {
                document.body.classList.remove('cursor-ready');
            });

            window.addEventListener('pointerenter', () => {
                document.body.classList.add('cursor-ready');
            });

            moveCursor();
        })();
    </script>
</body>
</html>
