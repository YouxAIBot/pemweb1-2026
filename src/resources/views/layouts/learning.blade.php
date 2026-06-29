<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'YoLearning Dashboard')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    <style>
        :root {
            --bg-0:#060914;
            --bg-1:#0b1020;
            --bg-2:#101827;
            --panel:#171b23;
            --panel-2:#1e2430;
            --panel-3:#252b36;
            --border:rgba(255,255,255,.08);
            --border-2:rgba(255,255,255,.14);
            --text:#f4f7fb;
            --muted:#aab1c3;
            --muted-2:#7d8799;
            --primary:#6e7cf7;
            --cyan:#66e8f7;
            --green:#49d38b;
            --yellow:#fff3a8;
            --danger:#ff6b8a;
            --shadow:0 24px 60px rgba(0,0,0,.35);
            --radius:22px;
        }

        *{box-sizing:border-box;margin:0;padding:0}
        html{scroll-behavior:smooth}
        body{
            min-height:100vh;
            font-family:Manrope,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;
            background:
                radial-gradient(circle at 18% 8%, rgba(90,124,255,.16), transparent 28rem),
                radial-gradient(circle at 88% 20%, rgba(102,232,247,.08), transparent 26rem),
                linear-gradient(135deg,#050812 0%, #0a1020 42%, #070914 100%);
            color:var(--text);
            overflow-x:hidden;
        }
        a{color:inherit;text-decoration:none} button,input{font-family:inherit}
        .learning-shell{min-height:100vh;position:relative;isolation:isolate}
        .learning-shell::before{
            content:"";position:fixed;inset:0;z-index:-2;pointer-events:none;
            background-image:linear-gradient(rgba(255,255,255,.025) 1px, transparent 1px),linear-gradient(90deg,rgba(255,255,255,.025) 1px,transparent 1px);
            background-size:58px 58px;mask-image:linear-gradient(to bottom,black,transparent 95%);
        }
        .learning-shell::after{
            content:"";position:fixed;inset:auto -12rem -16rem -12rem;height:38rem;z-index:-1;pointer-events:none;
            background:radial-gradient(circle at 24% 20%,rgba(76,111,255,.18),transparent 30%),radial-gradient(circle at 70% 10%,rgba(102,232,247,.12),transparent 30%);
            filter:blur(20px);
        }
        .firefly{position:fixed;z-index:100;left:22px;top:62px;width:7px;height:7px;border-radius:999px;background:#fff6ac;box-shadow:0 0 16px #fff6ac,0 0 32px rgba(102,232,247,.72);pointer-events:none;animation:firefly 6s ease-in-out infinite alternate}
        @keyframes firefly{0%{transform:translate(0,0)}50%{transform:translate(34px,18px)}100%{transform:translate(12px,52px)}}
        .auth-flash{position:fixed;right:1rem;bottom:1rem;z-index:200;padding:.9rem 1rem;border-radius:18px;border:1px solid rgba(102,232,247,.24);background:rgba(21,32,45,.88);box-shadow:var(--shadow);color:#eafcff;font-weight:850;animation:toastIn .35s ease both;transition:opacity .35s ease,transform .35s ease}
        .auth-flash.is-hiding{opacity:0;transform:translateY(1rem) scale(.98);pointer-events:none}
        @keyframes toastIn{from{opacity:0;transform:translateY(1rem) scale(.98)}to{opacity:1;transform:translateY(0) scale(1)}}

        /* ONBOARDING */
        .onboarding-page{min-height:100vh;padding:4rem 1rem 6rem;display:grid;place-items:center;position:relative;overflow:hidden}
        .intro-stage{position:fixed;inset:0;z-index:80;display:grid;place-items:center;background:rgba(5,8,18,.94);backdrop-filter:blur(16px);animation:introStage 4.1s ease forwards}
        .intro-word{position:absolute;text-align:center;font-size:clamp(2.1rem,7vw,6rem);font-weight:950;letter-spacing:-.07em;line-height:.95;text-shadow:0 20px 60px rgba(78,117,255,.28)}
        .intro-word.one{animation:introOne 4.1s ease forwards}.intro-word.two{opacity:0;animation:introTwo 4.1s ease forwards}
        @keyframes introStage{0%,82%{opacity:1;visibility:visible}100%{opacity:0;visibility:hidden;pointer-events:none}}
        @keyframes introOne{0%{opacity:0;filter:blur(16px);transform:translateY(16px) scale(.96)}12%,36%{opacity:1;filter:blur(0);transform:translateY(0) scale(1)}50%,100%{opacity:0;filter:blur(18px);transform:translateY(-22px) scale(1.03)}}
        @keyframes introTwo{0%,45%{opacity:0;filter:blur(18px);transform:translateY(22px) scale(.96)}58%,78%{opacity:1;filter:blur(0);transform:translateY(0) scale(1)}100%{opacity:0;filter:blur(16px);transform:translateY(-18px) scale(1.03)}}
        .onboarding-card{width:min(980px,100%);border:1px solid var(--border);border-radius:34px;background:linear-gradient(145deg,rgba(27,35,49,.86),rgba(12,17,30,.8));box-shadow:var(--shadow);padding:clamp(1.2rem,4vw,3rem);animation:riseIn .9s 3.7s cubic-bezier(.2,.8,.2,1) both}
        @keyframes riseIn{from{opacity:0;filter:blur(16px);transform:translateY(2rem) scale(.98)}to{opacity:1;filter:blur(0);transform:translateY(0) scale(1)}}
        .onboarding-head{text-align:center;margin-bottom:2.2rem}.onboarding-head span{color:var(--cyan);font-size:.82rem;font-weight:950;letter-spacing:.16em;text-transform:uppercase}.onboarding-head h1{font-size:clamp(1.8rem,5vw,3.5rem);letter-spacing:-.06em;margin-top:.6rem}.onboarding-head p{color:var(--muted);margin-top:.8rem;font-weight:650}
        .language-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:1rem}.language-option{position:relative;min-height:126px;border:1px solid var(--border);border-radius:24px;background:rgba(255,255,255,.045);padding:1rem;text-align:left;color:var(--text);cursor:pointer;overflow:hidden;transition:.24s ease;animation:cardPop .65s cubic-bezier(.2,.8,.2,1) both;animation-delay:calc(var(--i,0) * .08s + 4s)}.language-option::before{content:"";position:absolute;inset:-40%;background:radial-gradient(circle at 20% 20%,rgba(102,232,247,.18),transparent 28%);opacity:0;transition:.24s}.language-option:hover,.language-option.is-selected{transform:translateY(-5px);border-color:rgba(102,232,247,.45);background:rgba(102,232,247,.08)}.language-option.is-selected::before{opacity:1}.language-option b{display:block;font-size:1.2rem;letter-spacing:-.03em}.language-option small{position:relative;z-index:1;color:var(--cyan);font-weight:950}.language-option p{position:relative;z-index:1;color:var(--muted);font-size:.86rem;margin-top:.42rem;line-height:1.45}.flag-chip{position:absolute;right:.85rem;top:.85rem;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.08);border-radius:999px;padding:.28rem .5rem;font-size:.72rem;font-weight:950;color:#eafcff}
        @keyframes cardPop{from{opacity:0;transform:translateY(1.5rem) rotateX(12deg)}to{opacity:1;transform:translateY(0) rotateX(0)}}
        .ability-step{margin-top:2rem;padding-top:2rem;border-top:1px solid var(--border);opacity:.3;pointer-events:none;filter:blur(3px);transform:translateY(1rem);transition:.35s ease}.ability-step.is-ready{opacity:1;pointer-events:auto;filter:blur(0);transform:none}.ability-grid{display:grid;gap:.8rem}.ability-option{display:flex;align-items:center;justify-content:space-between;gap:1rem;border:1px solid var(--border);border-radius:20px;background:rgba(255,255,255,.04);padding:1rem 1.1rem;color:var(--text);cursor:pointer;transition:.24s}.ability-option:hover,.ability-option.is-selected{background:rgba(110,124,247,.12);border-color:rgba(110,124,247,.5);transform:translateX(6px)}.ability-option strong{font-size:1rem}.ability-option p{color:var(--muted);font-size:.84rem;margin-top:.18rem}.ability-option span{color:var(--cyan);font-weight:950;font-size:.8rem;white-space:nowrap}.onboarding-submit{width:100%;margin-top:1.2rem;border:0;border-radius:999px;padding:1rem 1.2rem;background:#edf7ff;color:#07101f;font-weight:950;cursor:pointer;transition:.22s}.onboarding-submit:hover{transform:translateY(-2px);box-shadow:0 20px 48px rgba(102,232,247,.18)}.onboarding-submit:disabled{opacity:.45;cursor:not-allowed;box-shadow:none;transform:none}.field-error{margin-top:.85rem;color:#ffb4c2;font-weight:800;text-align:center}

        /* DASHBOARD */
        .app-frame{height:100vh;display:grid;grid-template-columns:280px minmax(0,1fr) 280px;background:#11131a;overflow:hidden}.side-panel,.right-panel{background:#17191f;border-right:1px solid var(--border);padding:1rem;overflow:auto}.right-panel{border-right:0;border-left:1px solid var(--border)}.brand-mini{display:flex;align-items:center;gap:.7rem;font-weight:950;font-size:1.35rem;letter-spacing:-.05em;margin-bottom:1rem}.brand-mini .mark{width:42px;height:42px;border-radius:15px;background:linear-gradient(145deg,#65d7ff,#6e7cf7);display:grid;place-items:center;color:#07101f;font-weight:950;box-shadow:0 18px 42px rgba(80,120,255,.25)}.profile-tile{display:flex;align-items:center;gap:.75rem;padding:.72rem;border-radius:18px;background:#242730;border:1px solid var(--border);margin-bottom:1.2rem;color:var(--text);text-decoration:none}.avatar{width:48px;height:48px;border-radius:999px;background:linear-gradient(135deg,#65d7ff,#6e7cf7);display:grid;place-items:center;color:#06101f;font-weight:950;box-shadow:0 10px 30px rgba(80,120,255,.22);flex:0 0 auto;overflow:hidden}.avatar img{width:100%;height:100%;object-fit:cover}.profile-tile h3{font-size:1rem;line-height:1.1}.profile-tile p{font-size:.72rem;color:var(--muted);font-weight:800}.panel-title{font-weight:950;letter-spacing:-.04em;font-size:1.12rem;margin:1.3rem 0 .7rem}.friend-list{display:grid;gap:.45rem}.friend-card{display:flex;align-items:center;gap:.65rem;padding:.55rem;border-radius:14px;color:var(--muted);transition:.2s;background:transparent}.friend-card:hover{background:#22252d;color:var(--text)}.friend-card .mini-avatar{width:34px;height:34px;border-radius:999px;background:#2e3440;display:grid;place-items:center;color:#eafcff;font-size:.78rem;font-weight:900;position:relative}.friend-card .mini-avatar::after{content:"";position:absolute;right:-1px;bottom:-1px;width:9px;height:9px;border-radius:999px;border:2px solid #17191f;background:var(--green)}.friend-card.offline .mini-avatar::after{background:#667085}.menu-list{display:grid;gap:.48rem;margin-top:1rem}.menu-item{display:flex;align-items:center;gap:.7rem;border-radius:14px;padding:.72rem .75rem;color:var(--muted);font-weight:850;transition:.2s}.menu-item:hover,.menu-item.active{background:#2a2d36;color:var(--text);transform:translateX(3px)}.menu-icon{width:28px;height:28px;display:grid;place-items:center;border-radius:10px;background:#222733;color:var(--cyan);font-size:.76rem;font-weight:950}
        .main-panel{background:linear-gradient(135deg,#10131b,#0c1018 60%,#11101c);overflow:auto;position:relative}.main-topbar{position:sticky;top:0;z-index:20;height:64px;display:flex;align-items:center;justify-content:space-between;padding:0 1.4rem;border-bottom:1px solid var(--border);background:rgba(16,19,27,.82);backdrop-filter:blur(20px)}.main-topbar h1{font-size:1.05rem;letter-spacing:-.03em}.main-topbar p{color:var(--muted);font-size:.82rem;font-weight:800}.logout-link{border:1px solid var(--border);background:#222733;color:var(--text);font-weight:900;border-radius:999px;padding:.65rem .9rem;cursor:pointer;transition:.2s}.logout-link:hover{background:#2d3340}.content-area{padding:1.5rem;max-width:980px;margin:0 auto}.hero-learning{border:1px solid var(--border);border-radius:28px;background:linear-gradient(145deg,rgba(38,45,60,.8),rgba(23,27,36,.8));padding:1.4rem;margin-bottom:1.1rem;box-shadow:var(--shadow);animation:dashIn .65s ease both}.hero-learning small{color:var(--cyan);font-weight:950;letter-spacing:.12em;text-transform:uppercase}.hero-learning h2{font-size:clamp(1.5rem,4vw,3rem);letter-spacing:-.06em;line-height:1.02;margin:.55rem 0}.hero-learning p{color:var(--muted);font-weight:650;line-height:1.6}.parts-grid{display:grid;gap:.9rem}.part-card{position:relative;display:block;border:1px solid var(--border);border-radius:24px;background:#1a1d25;padding:1rem;overflow:hidden;transition:.24s;animation:dashIn .65s ease both;animation-delay:calc(var(--i,0) * .08s)}.part-card::before{content:"";position:absolute;inset:-60% -20%;background:radial-gradient(circle at 30% 20%,rgba(102,232,247,.12),transparent 32%);opacity:0;transition:.2s}.part-card:hover{transform:translateY(-5px);border-color:rgba(102,232,247,.28);background:#202530}.part-card:hover::before{opacity:1}.part-card h3{font-size:1.3rem;letter-spacing:-.04em;position:relative}.part-card p{position:relative;color:var(--muted);font-weight:650;margin:.45rem 0 .9rem}.progress-track{position:relative;height:9px;border-radius:999px;background:#0e1118;overflow:hidden}.progress-fill{height:100%;border-radius:inherit;background:linear-gradient(90deg,#66e8f7,#6e7cf7);box-shadow:0 0 20px rgba(102,232,247,.35)}.part-meta{display:flex;justify-content:space-between;gap:1rem;margin-top:.8rem;color:var(--muted);font-size:.8rem;font-weight:900}.part-badge{display:inline-flex;align-items:center;border:1px solid rgba(102,232,247,.2);background:rgba(102,232,247,.08);color:#dffcff;padding:.32rem .55rem;border-radius:999px;font-size:.74rem;font-weight:950}
        @keyframes dashIn{from{opacity:0;filter:blur(12px);transform:translateY(1rem)}to{opacity:1;filter:blur(0);transform:translateY(0)}}
        .stat-card{border:1px solid var(--border);background:#22252d;border-radius:20px;padding:1rem;margin-bottom:1rem}.stat-card h3{font-size:1.05rem}.stat-row{display:flex;align-items:center;justify-content:space-between;margin-top:.72rem;color:var(--muted);font-weight:900}.mission-card{border:1px solid var(--border);border-radius:16px;background:#22252d;padding:.75rem;margin:.55rem 0}.mission-card strong{font-size:.84rem}.mission-progress{height:8px;background:#11141b;border-radius:999px;overflow:hidden;margin-top:.55rem}.mission-progress span{display:block;height:100%;background:linear-gradient(90deg,#fff3a8,#66e8f7)}

        /* MAP */
        .map-wrap{position:relative;min-height:720px;border:1px solid var(--border);border-radius:28px;background:radial-gradient(circle at 50% 20%,rgba(110,124,247,.12),transparent 30%),#151922;overflow:hidden;box-shadow:var(--shadow);animation:dashIn .65s ease both}.map-title{position:absolute;top:1.4rem;left:1.6rem;z-index:4}.map-title small{color:var(--cyan);font-weight:950;letter-spacing:.12em;text-transform:uppercase}.map-title h2{margin-top:.3rem;font-size:1.8rem;letter-spacing:-.05em}.level-lines{position:absolute;inset:0;width:100%;height:100%;z-index:1;opacity:.65}.level-node{position:absolute;left:calc(var(--x) * 1%);top:calc(var(--y) * 1%);z-index:2;transform:translate(-50%,-50%);width:86px;height:86px;border-radius:999px;display:grid;place-items:center;background:#e8eef6;color:#111827;font-weight:950;font-size:1.6rem;box-shadow:0 18px 50px rgba(0,0,0,.28);transition:.26s;animation:nodeIn .7s cubic-bezier(.2,.8,.2,1) both;animation-delay:calc(var(--i,0) * .08s)}.level-node:hover{transform:translate(-50%,-50%) scale(1.08)}.level-node.locked{background:#303641;color:#8791a3;filter:saturate(.6)}.level-node.completed{background:linear-gradient(145deg,#a4ffe0,#66e8f7);color:#052028}.level-node.available,.level-node.in_progress{background:linear-gradient(145deg,#fff,#d8e8ff);box-shadow:0 0 0 8px rgba(102,232,247,.07),0 22px 55px rgba(102,232,247,.18)}.level-tooltip{position:absolute;left:50%;top:100%;transform:translateX(-50%) translateY(12px);width:170px;border:1px solid var(--border);border-radius:16px;background:rgba(21,25,34,.95);color:var(--text);padding:.65rem;opacity:0;pointer-events:none;transition:.2s;font-size:.75rem;box-shadow:var(--shadow)}.level-node:hover .level-tooltip{opacity:1;transform:translateX(-50%) translateY(8px)}.level-tooltip b{display:block;font-size:.8rem}.level-tooltip span{color:var(--cyan);font-weight:900}.level-tooltip p{color:var(--muted);margin-top:.18rem;line-height:1.35}.map-legend{position:absolute;right:1.2rem;bottom:1.2rem;display:flex;gap:.5rem;z-index:4;flex-wrap:wrap}.legend-pill{border:1px solid var(--border);background:rgba(255,255,255,.06);border-radius:999px;padding:.45rem .6rem;font-size:.72rem;font-weight:900;color:var(--muted)}@keyframes nodeIn{from{opacity:0;transform:translate(-50%,-50%) scale(.55);filter:blur(10px)}to{opacity:1;transform:translate(-50%,-50%) scale(1);filter:blur(0)}}
        .questions-list{display:grid;gap:1rem}.question-card{border:1px solid var(--border);border-radius:24px;background:#1b1f29;padding:1rem;animation:dashIn .65s ease both}.question-card small{color:var(--cyan);font-weight:950}.question-card h3{margin:.35rem 0 .7rem}.question-card p{color:var(--muted);line-height:1.55}.audio-player{width:100%;margin:.7rem 0;filter:invert(1) hue-rotate(180deg);opacity:.9}.option-list{display:grid;gap:.5rem;margin-top:.75rem}.option-chip{display:flex;align-items:center;justify-content:space-between;gap:.8rem;border:1px solid var(--border);border-radius:16px;background:#242936;padding:.65rem .75rem}.option-chip.correct{border-color:rgba(73,211,139,.35)}


        /* REVISION: per-user progress, lightweight welcome, clean scroll, and safer map spacing */
        html,
        body,
        .main-panel,
        .side-panel,
        .right-panel,
        .learning-shell {
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        html::-webkit-scrollbar,
        body::-webkit-scrollbar,
        .main-panel::-webkit-scrollbar,
        .side-panel::-webkit-scrollbar,
        .right-panel::-webkit-scrollbar,
        .learning-shell::-webkit-scrollbar {
            width: 0;
            height: 0;
            display: none;
        }

        .mission-card.completed {
            border-color: rgba(73, 211, 139, 0.32);
            background: linear-gradient(145deg, rgba(73, 211, 139, 0.10), rgba(34, 37, 45, 0.96));
        }

        .mission-card.completed strong::after {
            content: " ✓";
            color: var(--green);
        }

        .level-complete-form {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-top: 1rem;
            border: 1px solid var(--border);
            border-radius: 24px;
            background: linear-gradient(145deg, rgba(102, 232, 247, 0.08), rgba(27, 31, 41, 0.95));
            padding: 1rem;
            box-shadow: var(--shadow);
            animation: dashIn 0.65s ease both;
        }

        .level-complete-form small {
            color: var(--cyan);
            font-weight: 950;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .level-complete-form h3 {
            margin-top: 0.25rem;
            font-size: 1.1rem;
            letter-spacing: -0.04em;
        }

        .level-complete-form p {
            margin-top: 0.25rem;
            color: var(--muted);
            font-weight: 700;
            line-height: 1.5;
        }

        .complete-level-button {
            flex: 0 0 auto;
            border: 0;
            border-radius: 999px;
            padding: 0.85rem 1rem;
            background: #edf7ff;
            color: #07101f;
            font-weight: 950;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .complete-level-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 42px rgba(102, 232, 247, 0.20);
        }

        .welcome-gate {
            --welcome-y: -55px;
            --welcome-second-y: 55px;
            min-height: 100vh;
            display: grid;
            place-items: center;
            position: relative;
            overflow: hidden;
            background:
                radial-gradient(
                    circle at 50% 34%,
                    rgba(102, 232, 247, 0.08),
                    transparent 22rem
                ),
                linear-gradient(
                    135deg,
                    #070b16,
                    #0b1020 55%,
                    #070914
                );
        }

        .welcome-copy {
            position: relative;
            z-index: 2;
            width: min(920px, 92vw);
            padding: 2rem;
            text-align: center;
            transform: translateY(var(--welcome-y));
            animation: welcomeIn 0.55s ease both;
            will-change: opacity, transform;
        }

        .welcome-kicker {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(102, 232, 247, 0.24);
            border-radius: 999px;
            padding: 0.45rem 0.8rem;
            background: rgba(102, 232, 247, 0.07);
            color: var(--cyan);
            font-size: 0.8rem;
            font-weight: 950;
            letter-spacing: 0.18em;
            text-transform: uppercase;
        }

        .welcome-line {
            font-weight: 950;
            letter-spacing: -0.075em;
            line-height: 0.95;
            text-wrap: balance;
            text-shadow: 0 22px 70px rgba(78, 117, 255, 0.20);
        }

        .welcome-line-one {
            margin-top: 1rem;
            font-size: clamp(2.2rem, 8vw, 6.5rem);
            animation: welcomeFirst 5.2s ease both;
            will-change: opacity, transform;
        }

        .welcome-line-two {
            position: absolute;
            left: 50%;
            top: 50%;
            width: 100%;
            opacity: 0;
            font-size: clamp(2rem, 7vw, 6rem);
            transform: translate(
                -50%,
                calc(-35% + var(--welcome-y) + var(--welcome-second-y))
            );
            animation: welcomeSecond 5.2s ease both;
            will-change: opacity, transform;
        }

        .welcome-caption {
            margin-top: 1.1rem;
            color: var(--muted);
            font-weight: 800;
            animation: welcomeCaption 5.2s ease both;
            will-change: opacity, transform;
        }

        .welcome-orb {
            position: absolute;
            border-radius: 999px;
            opacity: 0.28;
            pointer-events: none;
            transform: translateZ(0);
        }

        .orb-one {
            width: 14rem;
            height: 14rem;
            left: 12%;
            top: 16%;
            background: radial-gradient(circle, rgba(102, 232, 247, 0.18), transparent 65%);
        }

        .orb-two {
            width: 17rem;
            height: 17rem;
            right: 10%;
            bottom: 10%;
            background: radial-gradient(circle, rgba(110, 124, 247, 0.16), transparent 65%);
        }

        body.welcome-leaving .welcome-gate {
            animation: welcomeLeave 0.45s ease both;
        }

        @keyframes welcomeIn {
            from {
                opacity: 0;
                transform: translateY(calc(var(--welcome-y) + 18px)) scale(0.985);
            }

            to {
                opacity: 1;
                transform: translateY(var(--welcome-y)) scale(1);
            }
        }

        @keyframes welcomeFirst {
            0% {
                opacity: 0;
                transform: translateY(18px) scale(0.985);
            }

            14%,
            44% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }

            58%,
            100% {
                opacity: 0;
                transform: translateY(-16px) scale(1.01);
            }
        }

        @keyframes welcomeSecond {
            0%,
            50% {
                opacity: 0;
                transform: translate(
                    -50%,
                    calc(-20% + var(--welcome-y) + var(--welcome-second-y))
                ) scale(0.985);
            }

            66%,
            88% {
                opacity: 1;
                transform: translate(
                    -50%,
                    calc(-35% + var(--welcome-y) + var(--welcome-second-y))
                ) scale(1);
            }

            100% {
                opacity: 0;
                transform: translate(
                    -50%,
                    calc(-48% + var(--welcome-y) + var(--welcome-second-y))
                ) scale(1.01);
            }
        }

        @keyframes welcomeCaption {
            0%,
            58% {
                opacity: 0;
                transform: translateY(10px);
            }

            70%,
            90% {
                opacity: 1;
                transform: translateY(0);
            }

            100% {
                opacity: 0;
                transform: translateY(-8px);
            }
        }

        @keyframes welcomeLeave {
            to {
                opacity: 0;
                transform: scale(1.01);
            }
        }

        .map-wrap {
            min-height: 760px;
            overflow: hidden;
            background:
                radial-gradient(
                    circle at 50% 12%,
                    rgba(102, 232, 247, 0.08),
                    transparent 18rem
                ),
                radial-gradient(
                    circle at 50% 42%,
                    rgba(110, 124, 247, 0.12),
                    transparent 28rem
                ),
                #151922;
        }

        .map-title {
            position: relative;
            top: auto;
            left: auto;
            z-index: 5;
            max-width: 620px;
            padding: 1.25rem 1.6rem 0;
            pointer-events: none;
        }

        .map-title h2 {
            font-size: clamp(1.45rem, 3vw, 2rem);
        }

        .map-title p {
            max-width: 520px;
            line-height: 1.45;
        }

        .map-stage {
            position: absolute;
            inset: 0;
            z-index: 2;
        }

        .level-lines {
            z-index: 1;
        }

        .level-node {
            z-index: 3;
        }

        @media (max-width: 760px) {
            .welcome-gate {
                --welcome-y: -35px;
                --welcome-second-y: 45px;
            }

            .map-title {
                padding: 1rem 1rem 0;
            }

            .map-wrap {
                min-height: 680px;
            }

            .level-complete-form {
                align-items: stretch;
                flex-direction: column;
            }
        }

        @media (max-width:1100px){.app-frame{grid-template-columns:240px minmax(0,1fr)}.right-panel{display:none}.language-grid{grid-template-columns:repeat(2,1fr)}}
        @media (max-width:760px){.app-frame{display:block;height:auto;min-height:100vh}.side-panel{position:relative;border-right:0;border-bottom:1px solid var(--border)}.main-panel{min-height:100vh}.language-grid{grid-template-columns:1fr}.onboarding-page{padding:1rem}.map-wrap{min-height:620px}.level-node{width:68px;height:68px;font-size:1.25rem}.content-area{padding:1rem}.main-topbar{height:auto;align-items:flex-start;gap:.7rem;flex-direction:column;padding:1rem}}
    </style>
    @stack('styles')
</head>
<body>
    <div class="learning-shell">
        <div class="firefly"></div>
        @if (session('learning_success') || session('auth_success'))
            <div class="auth-flash" data-auto-dismiss-toast>{{ session('learning_success') ?? session('auth_success') }}</div>
        @endif
        @if (session('learning_error'))
            <div class="auth-flash" data-auto-dismiss-toast>{{ session('learning_error') }}</div>
        @endif
        @yield('content')
    </div>
    <script>
        document.querySelectorAll('[data-auto-dismiss-toast]').forEach((toast) => {
            window.setTimeout(() => {
                toast.classList.add('is-hiding');
            }, 3200);

            window.setTimeout(() => {
                toast.remove();
            }, 3700);
        });
    </script>
    @stack('scripts')
</body>
</html>
