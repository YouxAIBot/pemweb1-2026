<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $homepageSettings->meta_description ?? 'YoLearning adalah platform belajar bahasa berbasis quiz, progress, dan tantangan.' }}">
    <title>@yield('title', $homepageSettings->meta_title ?? 'YoLearning - Language Learning Platform')</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800,900&display=swap" rel="stylesheet" />

    <style>
        :root {
            --navy-980: #030713;
            --navy-950: #050b18;
            --navy-925: #071121;
            --navy-900: #0a1628;
            --navy-850: #0d1f35;
            --ink: #edf4ff;
            --muted: #aab8cb;
            --soft: #d8e6f7;
            --cyan: #77e8f7;
            --sky: #58baf4;
            --blue: #346ee8;
            --indigo: #6c7bf4;
            --line: rgba(210, 230, 255, 0.12);
            --line-strong: rgba(210, 230, 255, 0.18);
            --glass: rgba(255, 255, 255, 0.055);
            --glass-strong: rgba(255, 255, 255, 0.085);
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
                radial-gradient(circle at 8% 8%, rgba(51, 122, 232, 0.28), transparent 28rem),
                radial-gradient(circle at 88% 14%, rgba(103, 232, 249, 0.12), transparent 24rem),
                radial-gradient(circle at 62% 88%, rgba(83, 68, 190, 0.18), transparent 30rem),
                linear-gradient(180deg, #040815 0%, #071426 40%, #030713 100%);
            color: var(--ink);
            font-family: 'Manrope', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        body.has-custom-cursor {
            cursor: none;
        }

        body.has-custom-cursor a,
        body.has-custom-cursor button {
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
                linear-gradient(rgba(148, 163, 184, 0.032) 1px, transparent 1px),
                linear-gradient(90deg, rgba(148, 163, 184, 0.032) 1px, transparent 1px);
            background-size: 72px 72px;
            mask-image: linear-gradient(to bottom, rgba(0, 0, 0, 0.9), transparent 92%);
        }

        .aurora {
            position: fixed;
            inset: auto -12rem -16rem -12rem;
            z-index: -1;
            height: 34rem;
            pointer-events: none;
            background:
                radial-gradient(circle at 18% 34%, rgba(50, 118, 236, 0.2), transparent 30%),
                radial-gradient(circle at 54% 12%, rgba(100, 221, 238, 0.11), transparent 34%),
                radial-gradient(circle at 78% 45%, rgba(91, 95, 206, 0.13), transparent 28%);
            filter: blur(16px);
        }

        .container {
            width: min(var(--max-width), calc(100% - 2rem));
            margin-inline: auto;
        }

        .navbar {
            position: sticky;
            top: 0;
            z-index: 50;
            border-bottom: 1px solid rgba(210, 230, 255, 0.09);
            background: rgba(3, 7, 19, 0.66);
            backdrop-filter: blur(22px);
        }

        .navbar-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 4.7rem;
            gap: 1rem;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 0.72rem;
            font-weight: 900;
            letter-spacing: -0.045em;
            font-size: clamp(1.08rem, 2vw, 1.34rem);
        }

        .brand-mark {
            position: relative;
            width: 2.25rem;
            height: 2.25rem;
            display: grid;
            place-items: center;
            border-radius: 0.8rem;
            color: #eaf7ff;
            background: linear-gradient(145deg, rgba(77, 177, 239, 0.88), rgba(52, 110, 232, 0.78));
            box-shadow: inset 0 1px rgba(255, 255, 255, 0.18), 0 14px 34px rgba(34, 103, 214, 0.24);
        }

        .brand-mark::after {
            content: '';
            position: absolute;
            inset: -0.18rem;
            border-radius: inherit;
            border: 1px solid rgba(119, 232, 247, 0.16);
        }

        .brand-logo {
            width: 2.25rem;
            height: 2.25rem;
            object-fit: cover;
            border-radius: 0.8rem;
            box-shadow: 0 14px 34px rgba(34, 103, 214, 0.22);
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 0.55rem;
            padding: 0.32rem;
            border: 1px solid rgba(210, 230, 255, 0.08);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.035);
        }

        .nav-link {
            color: rgba(237, 244, 255, 0.82);
            font-size: 0.88rem;
            font-weight: 850;
            line-height: 1;
            padding: 0.78rem 1rem;
            border-radius: 999px;
            transition: transform 180ms ease, background 180ms ease, border-color 180ms ease, color 180ms ease;
        }

        .nav-link:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.07);
        }

        .nav-button {
            color: #031121;
            background: #eef7ff;
            box-shadow: 0 10px 24px rgba(92, 178, 244, 0.16);
        }

        .nav-button:hover {
            color: #031121;
            background: #ffffff;
            transform: translateY(-1px);
        }

        .nav-soft {
            color: rgba(237, 244, 255, 0.92);
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .nav-soft:hover,
        .nav-ghost:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.1);
        }

        .nav-ghost {
            color: rgba(237, 244, 255, 0.86);
            background: transparent;
        }

        .section {
            position: relative;
            padding: clamp(4.7rem, 9vw, 7.6rem) 0;
        }

        .section-reveal {
            opacity: 0;
            transform: translateY(2.25rem) scale(0.985);
            filter: blur(10px);
            transition: opacity 760ms cubic-bezier(.2,.8,.2,1), transform 760ms cubic-bezier(.2,.8,.2,1), filter 760ms cubic-bezier(.2,.8,.2,1);
        }

        .section-reveal.is-visible {
            opacity: 1;
            transform: translateY(0) scale(1);
            filter: blur(0);
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
            color: var(--cyan);
            font-size: 0.78rem;
            font-weight: 900;
            letter-spacing: 0.13em;
            text-transform: uppercase;
        }

        .pulse-dot {
            width: 0.46rem;
            height: 0.46rem;
            border-radius: 999px;
            background: #fff7b0;
            box-shadow: 0 0 12px rgba(255, 247, 176, 0.95), 0 0 22px rgba(119, 232, 247, 0.42);
        }

        .hero-title {
            margin-top: 1.35rem;
            max-width: 58rem;
            font-size: clamp(2.8rem, 7vw, 6.75rem);
            line-height: 0.91;
            letter-spacing: -0.075em;
            font-weight: 900;
        }

        .text-gradient {
            background: linear-gradient(135deg, #ffffff 0%, #d9ebff 32%, #8dddf5 68%, #aac2ff 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .hero-copy {
            margin-top: 1.5rem;
            max-width: 39rem;
            color: rgba(207, 218, 233, 0.82);
            font-size: clamp(1rem, 2vw, 1.12rem);
            line-height: 1.82;
            font-weight: 500;
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.85rem;
            margin-top: 2rem;
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.55rem;
            min-height: 3.1rem;
            border: 1px solid transparent;
            border-radius: 999px;
            padding: 0.82rem 1.35rem;
            font-weight: 900;
            letter-spacing: -0.02em;
            transition: transform 180ms ease, background 180ms ease, border-color 180ms ease, color 180ms ease, box-shadow 180ms ease;
        }

        .button-primary {
            color: #071322;
            background: #edf7ff;
            border-color: rgba(255, 255, 255, 0.16);
            box-shadow: 0 18px 40px rgba(42, 132, 230, 0.18);
        }

        .button-primary:hover {
            transform: translateY(-2px);
            background: #ffffff;
            box-shadow: 0 24px 52px rgba(42, 132, 230, 0.24);
        }

        .button-soft {
            color: #dff7ff;
            background: rgba(119, 232, 247, 0.09);
            border-color: rgba(119, 232, 247, 0.18);
        }

        .button-soft:hover {
            transform: translateY(-2px);
            background: rgba(119, 232, 247, 0.13);
            border-color: rgba(119, 232, 247, 0.28);
        }

        .button-ghost {
            color: rgba(248, 250, 252, 0.9);
            background: rgba(255, 255, 255, 0.045);
            border-color: rgba(255, 255, 255, 0.13);
        }

        .button-ghost:hover {
            background: rgba(255, 255, 255, 0.085);
            transform: translateY(-2px);
        }

        .hero-card {
            position: relative;
            min-height: 32rem;
            border: 1px solid var(--line);
            border-radius: 2rem;
            overflow: hidden;
            background:
                radial-gradient(circle at 30% 12%, rgba(119, 232, 247, 0.12), transparent 25%),
                linear-gradient(145deg, rgba(14, 25, 43, 0.86), rgba(7, 17, 33, 0.66));
            box-shadow: 0 36px 90px rgba(0, 0, 0, 0.34), inset 0 1px rgba(255, 255, 255, 0.08);
        }

        .hero-card::before {
            content: '';
            position: absolute;
            inset: -7rem auto auto -5rem;
            width: 18rem;
            height: 18rem;
            border-radius: 999px;
            background: rgba(56, 148, 232, 0.22);
            filter: blur(24px);
        }

        .hero-card-content {
            position: relative;
            display: grid;
            height: 100%;
            min-height: 32rem;
            padding: clamp(1.25rem, 4vw, 2rem);
        }

        .hero-custom-image,
        .section-image {
            width: 100%;
            height: 100%;
            min-height: inherit;
            object-fit: cover;
            border-radius: inherit;
        }

        .hero-custom-image {
            position: absolute;
            inset: 0;
        }

        .section-image {
            position: absolute;
            inset: 0;
        }

        .study-window {
            align-self: start;
            border: 1px solid rgba(255, 255, 255, 0.105);
            border-radius: 1.45rem;
            background: rgba(2, 8, 23, 0.42);
            overflow: hidden;
            backdrop-filter: blur(12px);
            box-shadow: 0 22px 58px rgba(0, 0, 0, 0.24);
        }

        .window-bar {
            display: flex;
            gap: 0.42rem;
            padding: 0.85rem 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.075);
        }

        .window-dot {
            width: 0.64rem;
            height: 0.64rem;
            border-radius: 999px;
            background: rgba(229, 239, 250, 0.28);
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
            background: rgba(255, 255, 255, 0.055);
            border: 1px solid rgba(255, 255, 255, 0.075);
        }

        .lesson-row strong {
            font-size: 0.9rem;
        }

        .lesson-row span {
            color: rgba(203, 213, 225, 0.68);
            font-size: 0.78rem;
            font-weight: 700;
        }

        .lesson-score {
            color: var(--cyan);
            font-weight: 900;
        }

        .floating-badge {
            position: absolute;
            right: 1.4rem;
            bottom: 1.35rem;
            max-width: 16rem;
            padding: 1.05rem;
            border: 1px solid rgba(119, 232, 247, 0.18);
            border-radius: 1.25rem;
            background: rgba(8, 28, 50, 0.58);
            backdrop-filter: blur(12px);
        }

        .floating-badge p:first-child {
            color: #d7fbff;
            font-size: 0.76rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.12em;
        }

        .floating-badge p:last-child {
            margin-top: 0.45rem;
            color: rgba(237, 244, 255, 0.85);
            line-height: 1.45;
            font-weight: 700;
        }

        .split-section {
            overflow: hidden;
            border-top: 1px solid rgba(255, 255, 255, 0.075);
            border-bottom: 1px solid rgba(255, 255, 255, 0.075);
            background:
                radial-gradient(circle at 80% 20%, rgba(119, 232, 247, 0.11), transparent 30rem),
                linear-gradient(135deg, rgba(9, 35, 60, 0.38), rgba(2, 7, 18, 0.45));
        }

        .split-grid {
            display: grid;
            grid-template-columns: minmax(0, 0.86fr) minmax(20rem, 1.14fr);
            gap: clamp(2rem, 6vw, 5rem);
            align-items: center;
        }

        .language-copy {
            opacity: 0;
            transform: translateX(-3rem);
            filter: blur(10px);
            transition: opacity 850ms cubic-bezier(.2,.8,.2,1), transform 850ms cubic-bezier(.2,.8,.2,1), filter 850ms cubic-bezier(.2,.8,.2,1);
        }

        .split-section.is-visible .language-copy {
            opacity: 1;
            transform: translateX(0);
            filter: blur(0);
        }

        .section-kicker {
            color: var(--cyan);
            text-transform: uppercase;
            letter-spacing: 0.16em;
            font-size: 0.78rem;
            font-weight: 900;
        }

        .section-title {
            margin-top: 0.85rem;
            font-size: clamp(2rem, 5vw, 4.35rem);
            line-height: 0.98;
            letter-spacing: -0.06em;
            font-weight: 900;
        }

        .section-copy {
            margin-top: 1.15rem;
            max-width: 39rem;
            color: rgba(203, 213, 225, 0.8);
            line-height: 1.78;
            font-size: 1.02rem;
            font-weight: 500;
        }

        .language-cloud {
            position: relative;
            min-height: 34rem;
        }

        .language-card {
            --stack-rot: 0deg;
            position: absolute;
            top: 50%;
            left: 50%;
            width: 12.5rem;
            min-height: 9rem;
            padding: 1.05rem;
            border: 1px solid rgba(255, 255, 255, 0.115);
            border-radius: 1.35rem;
            background: rgba(255, 255, 255, 0.062);
            box-shadow: 0 26px 70px rgba(0, 0, 0, 0.24);
            backdrop-filter: blur(14px);
            opacity: 0.52;
            transform: translate(-50%, -50%) rotate(var(--stack-rot)) scale(0.9);
            transition:
                top 920ms cubic-bezier(.2,.8,.2,1),
                left 920ms cubic-bezier(.2,.8,.2,1),
                opacity 680ms ease,
                transform 920ms cubic-bezier(.2,.8,.2,1),
                background 220ms ease,
                border-color 220ms ease;
        }

        .language-cloud.is-visible .language-card {
            top: var(--top);
            left: var(--left);
            opacity: 1;
            transform: translate(0, 0) rotate(var(--rotate)) scale(1);
        }

        .language-card:hover {
            border-color: rgba(119, 232, 247, 0.34);
            background: rgba(255, 255, 255, 0.095);
        }

        .language-card-image {
            width: 100%;
            height: 4.2rem;
            object-fit: cover;
            border-radius: 0.9rem;
            margin-bottom: 0.75rem;
            opacity: 0.9;
        }

        .language-card:nth-child(1) { --top: 0.3rem; --left: 8%; --rotate: -28deg; --stack-rot: -6deg; }
        .language-card:nth-child(2) { --top: 2rem; --left: 43%; --rotate: 13deg; --stack-rot: 5deg; }
        .language-card:nth-child(3) { --top: 9.6rem; --left: 19%; --rotate: -10deg; --stack-rot: -2deg; }
        .language-card:nth-child(4) { --top: 11.2rem; --left: 58%; --rotate: 24deg; --stack-rot: 7deg; }
        .language-card:nth-child(5) { --top: 20rem; --left: 5%; --rotate: 13deg; --stack-rot: 3deg; }
        .language-card:nth-child(6) { --top: 20.7rem; --left: 43%; --rotate: -17deg; --stack-rot: -5deg; }
        .language-card:nth-child(7) { --top: 6.7rem; --left: 76%; --rotate: -9deg; --stack-rot: 4deg; }
        .language-card:nth-child(8) { --top: 18.2rem; --left: 72%; --rotate: 11deg; --stack-rot: -3deg; }
        .language-card:nth-child(9) { --top: 28.4rem; --left: 18%; --rotate: -8deg; --stack-rot: 6deg; }
        .language-card:nth-child(10) { --top: 29rem; --left: 56%; --rotate: 8deg; --stack-rot: -4deg; }
        .language-card:nth-child(n+11) { --top: 50%; --left: 50%; --rotate: 0deg; --stack-rot: 0deg; }

        .language-accent {
            color: #e3f8ff;
            font-weight: 900;
            font-size: 1.05rem;
        }

        .language-name {
            margin-top: 0.65rem;
            font-weight: 900;
            font-size: 1.12rem;
            letter-spacing: -0.035em;
        }

        .language-desc {
            margin-top: 0.35rem;
            color: rgba(203, 213, 225, 0.72);
            font-size: 0.78rem;
            line-height: 1.45;
        }

        .language-status {
            display: inline-flex;
            margin-top: 0.8rem;
            padding: 0.28rem 0.58rem;
            border-radius: 999px;
            background: rgba(119, 232, 247, 0.105);
            color: #d7fbff;
            font-size: 0.68rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .tournament-section {
            background:
                radial-gradient(circle at 20% 22%, rgba(35, 96, 186, 0.18), transparent 25rem),
                radial-gradient(circle at 82% 80%, rgba(56, 44, 122, 0.13), transparent 28rem),
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
            border: 1px solid rgba(210, 230, 255, 0.11);
            border-radius: 2rem;
            overflow: hidden;
            background:
                linear-gradient(145deg, rgba(15, 25, 42, 0.88), rgba(11, 20, 35, 0.68)),
                radial-gradient(circle at 42% 28%, rgba(119, 232, 247, 0.08), transparent 17rem);
            box-shadow: 0 34px 84px rgba(0, 0, 0, 0.34), inset 0 1px rgba(255,255,255,0.07);
        }

        .arena-card::before {
            content: '';
            position: absolute;
            inset: 3rem;
            border: 1px solid rgba(180, 205, 235, 0.12);
            border-radius: 50%;
        }

        .tournament-visual {
            position: absolute;
            inset: 0;
            display: grid;
            place-items: center;
            padding: 2rem;
        }

        .podium-card {
            position: relative;
            width: min(18rem, 72%);
            padding: 1.25rem;
            border: 1px solid rgba(210, 230, 255, 0.13);
            border-radius: 1.55rem;
            background: rgba(255, 255, 255, 0.055);
            backdrop-filter: blur(14px);
            box-shadow: 0 24px 66px rgba(0,0,0,0.3);
        }

        .podium-card::after {
            content: '';
            position: absolute;
            inset: auto 1.4rem -0.45rem;
            height: 0.9rem;
            border-radius: 999px;
            background: rgba(119, 232, 247, 0.12);
            filter: blur(10px);
        }

        .podium-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: rgba(237, 244, 255, 0.72);
            font-weight: 900;
            font-size: 0.78rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .podium-icon {
            width: 2.35rem;
            height: 2.35rem;
            display: grid;
            place-items: center;
            border: 1px solid rgba(119, 232, 247, 0.18);
            border-radius: 0.85rem;
            color: #e6fbff;
            background: rgba(119, 232, 247, 0.075);
        }

        .podium-bars {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            align-items: end;
            gap: 0.55rem;
            height: 8rem;
            margin-top: 1.2rem;
        }

        .podium-bar {
            display: flex;
            align-items: flex-end;
            justify-content: center;
            min-height: var(--h);
            border: 1px solid rgba(210, 230, 255, 0.1);
            border-radius: 0.8rem 0.8rem 0.45rem 0.45rem;
            background: linear-gradient(180deg, rgba(237, 244, 255, 0.16), rgba(119, 232, 247, 0.07));
            color: rgba(237, 244, 255, 0.82);
            font-weight: 900;
            padding-bottom: 0.58rem;
        }

        .podium-caption {
            margin-top: 1rem;
            color: rgba(237, 244, 255, 0.86);
            font-weight: 900;
            letter-spacing: -0.03em;
            text-align: center;
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
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 1.12rem;
            background: rgba(255, 255, 255, 0.047);
            transition: transform 180ms ease, background 180ms ease, border-color 180ms ease;
        }

        .feature-item:hover {
            transform: translateX(4px);
            background: rgba(255,255,255,0.065);
            border-color: rgba(119, 232, 247, 0.18);
        }

        .feature-icon {
            width: 2.25rem;
            height: 2.25rem;
            flex: 0 0 auto;
            display: grid;
            place-items: center;
            border-radius: 0.75rem;
            background: rgba(119, 232, 247, 0.095);
            color: var(--cyan);
            font-weight: 900;
        }

        .feature-item strong {
            display: block;
            font-size: 1rem;
        }

        .feature-item p {
            margin-top: 0.22rem;
            color: rgba(203, 213, 225, 0.76);
            line-height: 1.55;
            font-size: 0.94rem;
        }

        .cta-section {
            text-align: center;
            background:
                radial-gradient(circle at 50% 18%, rgba(119, 232, 247, 0.09), transparent 28rem),
                linear-gradient(180deg, rgba(7, 17, 33, 0.3), rgba(3, 7, 18, 0.78));
        }

        .cta-card {
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(210, 230, 255, 0.12);
            border-radius: 2rem;
            padding: clamp(2rem, 5vw, 4.1rem);
            background:
                linear-gradient(145deg, rgba(255,255,255,0.07), rgba(255,255,255,0.035)),
                radial-gradient(circle at 50% 0%, rgba(119, 232, 247, 0.1), transparent 18rem);
            box-shadow: 0 32px 80px rgba(0, 0, 0, 0.3), inset 0 1px rgba(255,255,255,0.075);
        }

        .cta-card::before,
        .cta-card::after {
            content: '';
            position: absolute;
            pointer-events: none;
            border-radius: 999px;
        }

        .cta-card::before {
            inset: -7rem 28% auto;
            height: 12rem;
            background: rgba(119, 232, 247, 0.12);
            filter: blur(58px);
        }

        .cta-card::after {
            width: 0.55rem;
            height: 0.55rem;
            right: 12%;
            top: 18%;
            background: #fff7b0;
            box-shadow: 0 0 18px rgba(255, 247, 176, 0.82), 0 0 34px rgba(119, 232, 247, 0.32);
        }

        .cta-background-image {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0.16;
            filter: saturate(0.8);
        }

        .cta-card > * {
            position: relative;
        }

        .footer {
            border-top: 1px solid rgba(255, 255, 255, 0.075);
            background: rgba(2, 6, 23, 0.88);
        }

        .footer-inner {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1.65rem 0;
            color: rgba(203, 213, 225, 0.72);
            font-weight: 750;
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
            width: var(--cursor-glow-size, 2.85rem);
            height: var(--cursor-glow-size, 2.85rem);
            border-radius: 999px;
            background:
                radial-gradient(circle, rgba(255, 255, 205, 0.72) 0%, rgba(119, 232, 247, 0.36) 20%, rgba(52, 110, 232, 0.18) 44%, transparent 72%);
            filter: blur(2px);
            mix-blend-mode: screen;
        }

        .cursor-dot {
            width: 0.36rem;
            height: 0.36rem;
            border-radius: 999px;
            background: #fff7b0;
            box-shadow: 0 0 9px #fff7b0, 0 0 16px rgba(119, 232, 247, 0.7);
        }

        body.cursor-ready .cursor-glow,
        body.cursor-ready .cursor-dot {
            opacity: 1;
        }

        body.cursor-click .cursor-glow {
            transform: translate(-50%, -50%) scale(0.76);
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.001ms !important;
                animation-iteration-count: 1 !important;
                scroll-behavior: auto !important;
                transition-duration: 0.001ms !important;
            }

            .section-reveal,
            .language-copy,
            .language-card {
                opacity: 1;
                filter: none;
                transform: none;
            }
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

            .language-copy {
                opacity: 1;
                transform: none;
                filter: none;
            }

            .language-cloud {
                min-height: auto;
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 1rem;
            }

            .language-card,
            .language-cloud.is-visible .language-card {
                position: relative;
                inset: auto !important;
                width: auto;
                min-height: 10rem;
                opacity: 1;
                transform: none !important;
            }
        }

        @media (max-width: 620px) {
            .container {
                width: min(100% - 1rem, var(--max-width));
            }

            .navbar-inner {
                min-height: 4.1rem;
            }

            .brand span:last-child {
                font-size: 1rem;
            }

            .brand-mark {
                width: 2rem;
                height: 2rem;
                border-radius: 0.72rem;
            }

            .nav-links {
                gap: 0.22rem;
                padding: 0.24rem;
            }

            .nav-link {
                padding: 0.62rem 0.68rem;
                font-size: 0.78rem;
            }

            .section {
                padding: 3.6rem 0;
            }

            .hero-title {
                font-size: clamp(2.65rem, 17vw, 4.35rem);
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

            .podium-card {
                width: min(18rem, 86%);
            }

            .footer-inner {
                justify-content: center;
                text-align: center;
            }
        }
    </style>
</head>
<body
    class="{{ ($homepageSettings->cursor_glow_enabled ?? true) ? 'has-custom-cursor' : '' }}"
    style="--cursor-glow-size: {{ max(8, min(40, (int) ($homepageSettings->cursor_glow_size ?? 18))) * 0.16 }}rem;"
>
    @if ($homepageSettings->cursor_glow_enabled ?? true)
        <div class="cursor-glow" aria-hidden="true"></div>
        <div class="cursor-dot" aria-hidden="true"></div>
    @endif

    <div class="site-shell">
        <div class="aurora" aria-hidden="true"></div>

        <header class="navbar">
            <div class="container navbar-inner">
                <a href="{{ route('home') }}" class="brand" aria-label="{{ $homepageSettings->brand_text ?? 'YoLearning' }} Home">
                    @if (! empty($homepageSettings->brand_logo_path))
                        <img src="{{ asset('storage/' . $homepageSettings->brand_logo_path) }}" alt="{{ $homepageSettings->brand_text ?? 'YoLearning' }}" class="brand-logo">
                    @else
                        <span class="brand-mark">{{ $homepageSettings->brand_initial ?? 'Y' }}</span>
                    @endif
                    <span>{{ $homepageSettings->brand_text ?? 'YoLearning' }}</span>
                </a>

                <nav class="nav-links" aria-label="Navigasi utama">
                    @foreach ($homepageNavItems as $navItem)
                        <a href="{{ $navItem->url }}" class="nav-link @if ($navItem->style === 'primary') nav-button @elseif ($navItem->style === 'soft') nav-soft @elseif ($navItem->style === 'ghost') nav-ghost @endif">
                            {{ $navItem->label }}
                        </a>
                    @endforeach
                </nav>
            </div>
        </header>

        <main>
            @yield('content')
        </main>

        <footer class="footer section-reveal">
            <div class="container footer-inner">
                <p>{{ $homepageSettings->footer_left ?? '© ' . date('Y') . ' YoLearning. Semua progres belajar tersimpan rapi.' }}</p>
                <p>{{ $homepageSettings->footer_right ?? 'Belajar bahasa • Quiz • Tournament' }}</p>
            </div>
        </footer>
    </div>

    <script>
        (() => {
            if (!document.body.classList.contains('has-custom-cursor')) return;
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
                glowX += (mouseX - glowX) * 0.16;
                glowY += (mouseY - glowY) * 0.16;

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

        (() => {
            const animatedElements = document.querySelectorAll('.section-reveal, .language-cloud');

            if (!('IntersectionObserver' in window)) {
                animatedElements.forEach((element) => element.classList.add('is-visible'));
                return;
            }

            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    entry.target.classList.toggle('is-visible', entry.isIntersecting);
                });
            }, {
                threshold: 0.28,
                rootMargin: '-8% 0px -12% 0px',
            });

            animatedElements.forEach((element) => observer.observe(element));
        })();
    </script>
</body>
</html>
