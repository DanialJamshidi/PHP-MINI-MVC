<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>MVC | Framework</title>

    <link rel="shortcut icon" href="<?= urlPath("/assets/img/3.png"); ?>" type="image/x-icon">

    <style>
    :root{--bg:#050505;--surface:#0b0b0b;--surface-2:#101010;--border:#1d1d1d;--border-light:#292929;--white:#fff;--gray-1:#e5e5e5;--gray-2:#a3a3a3;--gray-3:#666;--green:#00ff9c;--green-dark:#00c97a;--purple:#8b5cf6;--blue:#38bdf8}*{margin:0;padding:0;box-sizing:border-box}html{scroll-behavior:smooth}body{font-family:'Fira Code','JetBrains Mono','Inter',monospace;background:var(--bg);color:var(--white);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:40px 20px;overflow-x:hidden;-webkit-font-smoothing:antialiased}.bg-grid{position:fixed;inset:0;background-image:linear-gradient(rgb(255 255 255 / .035) 1px,transparent 1px),linear-gradient(90deg,rgb(255 255 255 / .035) 1px,transparent 1px);background-size:50px 50px;mask-image:linear-gradient(to bottom,black,transparent 90%);pointer-events:none}.glow{position:fixed;width:500px;height:500px;border-radius:50%;filter:blur(140px);opacity:.09;pointer-events:none}.glow-green{background:var(--green);top:-250px;right:-180px}.glow-purple{background:var(--purple);bottom:-300px;left:-200px}.container{position:relative;z-index:2;width:100%;max-width:1000px}.topbar{display:flex;align-items:center;justify-content:space-between;margin-bottom:70px;color:var(--gray-3);font-size:.75rem;letter-spacing:.08em}.brand-mini{display:flex;align-items:center;gap:10px;color:var(--gray-1);font-weight:700}.brand-dot{width:8px;height:8px;border-radius:50%;background:var(--green);box-shadow:0 0 14px var(--green)}.top-version{padding:7px 12px;border:1px solid var(--border);border-radius:6px;background:rgb(255 255 255 / .02)}.hero{display:grid;grid-template-columns:1.2fr .8fr;gap:70px;align-items:center}.eyebrow{display:inline-flex;align-items:center;gap:9px;color:var(--green);font-size:.8rem;margin-bottom:25px;letter-spacing:.08em}.eyebrow::before{content:'>';color:var(--gray-3)}.logo-title{display:flex;align-items:center;gap:18px;line-height:1}.logo-title-text{font-family:Inter,system-ui,sans-serif;font-size:clamp(3.5rem,8vw,7rem);font-weight:900;letter-spacing:-.07em;color:var(--white);white-space:nowrap}.logo-title-svg{display:block;width:clamp(200px,25vw,260px);height:auto;object-fit:contain;flex-shrink:0}.hero-desc{max-width:570px;color:var(--gray-2);font-family:Inter,system-ui,sans-serif;font-size:1.05rem;line-height:1.8}.hero-desc strong{color:var(--white)}.actions{display:flex;gap:12px;margin-top:35px;flex-wrap:wrap}.btn{display:inline-flex;align-items:center;justify-content:center;gap:10px;padding:14px 20px;border-radius:7px;text-decoration:none;font-family:inherit;font-size:.8rem;font-weight:700;transition:.3s ease}.btn svg{width:17px;height:17px}.btn-primary{background:var(--green);color:#00150d;border:1px solid var(--green)}.btn-primary:hover{background:#42ffb6;transform:translateY(-3px);box-shadow:0 12px 35px rgb(0 255 156 / .18)}.btn-secondary{background:var(--surface);color:var(--gray-1);border:1px solid var(--border-light)}.btn-secondary:hover{border-color:var(--gray-3);background:var(--surface-2);transform:translateY(-3px)}.terminal{background:rgb(11 11 11 / .88);border:1px solid var(--border-light);border-radius:12px;box-shadow:0 30px 80px rgb(0 0 0 / .5),0 0 0 1px rgb(255 255 255 / .015);overflow:hidden;transform:rotate(1deg);transition:.4s ease}.terminal:hover{transform:rotate(0) translateY(-5px);border-color:#333}.terminal-head{height:42px;display:flex;align-items:center;gap:7px;padding:0 15px;border-bottom:1px solid var(--border);background:#0e0e0e}.terminal-dot{width:8px;height:8px;border-radius:50%;background:#292929}.terminal-title{margin-left:auto;margin-right:auto;color:#444;font-size:.65rem}.terminal-body{padding:24px 22px;font-size:.75rem;line-height:2;color:#777}.terminal-line{display:block}.terminal-line .prompt{color:var(--green)}.terminal-line .command{color:var(--gray-1)}.terminal-line .value{color:var(--purple)}.terminal-line .success{color:var(--green)}.terminal-line.empty{height:10px}.cursor{display:inline-block;width:7px;height:14px;background:var(--green);vertical-align:middle;margin-left:4px;animation:blink 1s infinite}@keyframes blink{0%,45%{opacity:1}46%,100%{opacity:0}}.features{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-top:80px}.feature{background:var(--surface);border:1px solid var(--border);border-radius:9px;padding:22px;transition:.3s ease}.feature:hover{border-color:#303030;background:#0e0e0e;transform:translateY(-4px)}.feature-number{font-size:.65rem;color:var(--green);margin-bottom:20px}.feature h3{font-family:Inter,system-ui,sans-serif;font-size:.9rem;margin-bottom:9px}.feature p{color:var(--gray-3);font-size:.7rem;line-height:1.7}.bottom{display:flex;align-items:center;justify-content:space-between;margin-top:55px;padding-top:20px;border-top:1px solid var(--border);color:#444;font-size:.65rem}.bottom a{color:#777;text-decoration:none;transition:.2s}.bottom a:hover{color:var(--green)}.status{display:flex;align-items:center;gap:7px}.status-dot{width:6px;height:6px;background:var(--green);border-radius:50%;box-shadow:0 0 10px var(--green)}@media (max-width:800px){.hero{grid-template-columns:1fr;gap:45px}.terminal{max-width:600px;margin:auto;width:100%}.features{grid-template-columns:1fr}.topbar{margin-bottom:50px}.logo-title-text{font-size:clamp(3.5rem,16vw,6rem)}.logo-title-svg{width:clamp(130px,25vw,200px)}}@media (max-width:480px){body{padding:25px 16px}.topbar{font-size:.6rem}.hero-desc{font-size:.9rem}.logo-title{gap:10px;align-items:center}.logo-title-text{font-size:clamp(2.7rem,15vw,4.5rem)}.logo-title-svg{width:clamp(90px,25vw,130px)}.actions{flex-direction:column}.btn{width:100%}.terminal-body{padding:18px 15px;font-size:.65rem}.bottom{flex-direction:column;gap:15px;text-align:center;margin-top:40px}}
    </style>
</head>

<body>
    <div class="bg-grid"></div>
    <div class="glow glow-green"></div>
    <div class="glow glow-purple"></div>
    <main class="container">
        <section class="hero">
            <div class="hero-content">
                <div class="eyebrow">
                    FRAMEWORK
                </div>
                <h1 class="logo-title">
                    <span class="logo-title-text">
                        Danial
                    </span>
                    <img
                        src="<?= urlPath('/assets/img/4.svg'); ?>"
                        class="logo-title-svg"
                        alt="DanialMVC Logo"
                    >
                </h1>
                <p class="hero-desc">
                    A lightweight, elegant and
                    <strong>
                        developer-focused MVC framework
                    </strong>
                    built to keep your PHP applications fast,
                    clean and beautifully simple.

                </p>
                <div class="actions">
                    <a
                        href="https://github.com/DanialJamshidi"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="btn btn-primary"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2.2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path d="
                                M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87
                                a3.37 3.37 0 0 0-.94-2.61
                                c3.14-.35 6.44-1.54 6.44-7
                                A5.44 5.44 0 0 0 20 4.77
                                5.07 5.07 0 0 0 19.91 1
                                S18.73.65 16 2.48
                                a13.38 13.38 0 0 0-7 0
                                C6.27.65 5.09 1 5.09 1
                                A5.07 5.07 0 0 0 5 4.77
                                a5.44 5.44 0 0 0-1.5 3.78
                                c0 5.42 3.3 6.61 6.44 7
                                A3.37 3.37 0 0 0 9 18.13V22
                            "/>
                        </svg>
                        GitHub
                    </a>
                    <a
                        href="https://danialjamshidi.github.io/"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="btn btn-secondary"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <circle
                                cx="12"
                                cy="12"
                                r="10"
                            />
                            <line
                                x1="2"
                                y1="12"
                                x2="22"
                                y2="12"
                            />
                            <path d="
                                M12 2
                                a15.3 15.3 0 0 1 4 10
                                15.3 15.3 0 0 1-4 10
                                15.3 15.3 0 0 1-4-10
                                15.3 15.3 0 0 1 4-10z
                            "/>
                        </svg>
                        Website
                    </a>
                </div>
            </div>
            <div class="terminal">
                <div class="terminal-head">
                    <span class="terminal-dot"></span>
                    <span class="terminal-dot"></span>
                    <span class="terminal-dot"></span>
                    <span class="terminal-title">
                        FRAMEWORK
                    </span>
                </div>
                <div class="terminal-body">
                    <span class="terminal-line">
                        <span class="prompt">
                            $
                        </span>
                        <span class="command">
                            php
                        </span>
                    </span>
                    <span class="terminal-line">
                        <span class="prompt">
                            ›
                        </span>
                        <span class="command">
                            version 3
                        </span>
                    </span>
                    <span class="terminal-line empty"></span>
                    <span class="terminal-line">
                        framework:
                        <span class="value">
                            MVC
                        </span>
                    </span>
                    <span class="terminal-line">
                        version:
                        <span class="value">
                            3.0.0
                        </span>
                    </span>
                    <span class="terminal-line">
                        language:
                        <span class="value">
                            PHP
                        </span>
                    </span>
                    <span class="terminal-line">
                        architecture:
                        <span class="value">
                            MVC
                        </span>
                    </span>
                    <span class="terminal-line empty"></span>
                    <span class="terminal-line">
                        status:
                        <span class="success">
                            READY
                        </span>
                    </span>
                    <span class="terminal-line">
                        <span class="prompt">
                            $
                        </span>
                        <span class="cursor"></span>
                    </span>
                </div>
            </div>
        </section>
    </main>
</body>
</html>