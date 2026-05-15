<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DanialMVC — Framework for Artisans</title>
    <style>
        :root {
            --black: #0a0a0a;
            --white: #ffffff;
            --gray-50: #fafafa;
            --gray-100: #f5f5f5;
            --gray-200: #e5e5e5;
            --gray-300: #d4d4d4;
            --gray-400: #a3a3a3;
            --gray-500: #737373;
            --gray-600: #525252;
            --gray-700: #404040;
            --gray-800: #262626;
            --gray-900: #171717;
            --indigo: #4f46e5;
            --indigo-light: #6366f1;
            --indigo-dark: #4338ca;
            --emerald: #059669;
            --emerald-light: #10b981;
            --red: #dc2626;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', 'Inter', system-ui, -apple-system, sans-serif;
            background: var(--white);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
            position: relative;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Animated background blobs */
        .bg-decoration {
            position: fixed;
            border-radius: 50%;
            filter: blur(120px);
            opacity: 0.15;
            pointer-events: none;
            z-index: 0;
            animation: blobFloat 20s ease-in-out infinite;
        }

        .bg-blob-1 {
            width: 600px;
            height: 600px;
            background: var(--indigo);
            top: -200px;
            right: -200px;
            animation-delay: 0s;
        }

        .bg-blob-2 {
            width: 500px;
            height: 500px;
            background: var(--emerald);
            bottom: -150px;
            left: -150px;
            animation-delay: -7s;
            animation-duration: 23s;
        }

        .bg-blob-3 {
            width: 400px;
            height: 400px;
            background: #a855f7;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            animation-delay: -14s;
            animation-duration: 25s;
            opacity: 0.1;
        }

        @keyframes blobFloat {

            0%,
            100% {
                transform: translate(0, 0) scale(1);
            }

            25% {
                transform: translate(30px, -30px) scale(1.05);
            }

            50% {
                transform: translate(-20px, 20px) scale(0.95);
            }

            75% {
                transform: translate(-30px, -10px) scale(1.02);
            }
        }

        /* Grid pattern */
        .grid-pattern {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image:
                linear-gradient(var(--gray-200) 1px, transparent 1px),
                linear-gradient(90deg, var(--gray-200) 1px, transparent 1px);
            background-size: 64px 64px;
            opacity: 0.5;
            pointer-events: none;
            z-index: 0;
            mask-image: radial-gradient(ellipse at center, black 40%, transparent 70%);
        }

        .container {
            position: relative;
            z-index: 1;
            max-width: 820px;
            width: 100%;
        }

        /* Hero section */
        .hero {
            text-align: center;
            margin-bottom: 40px;
        }

        .logo-wrapper {
            display: inline-block;
            position: relative;
            margin-bottom: 32px;
        }

        .logo-hexagon {
            width: 100px;
            height: 100px;
            background: var(--black);
            border-radius: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            position: relative;
            z-index: 1;
            box-shadow:
                0 0 0 1px rgba(0, 0, 0, 0.05),
                0 8px 24px -8px rgba(0, 0, 0, 0.12),
                0 20px 48px -16px rgba(0, 0, 0, 0.15),
                0 32px 64px -24px rgba(0, 0, 0, 0.2);
            animation: logoFloat 4s ease-in-out infinite;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .logo-hexagon:hover {
            transform: translateY(-4px) scale(1.03);
            box-shadow:
                0 0 0 1px rgba(0, 0, 0, 0.05),
                0 12px 32px -8px rgba(0, 0, 0, 0.15),
                0 28px 56px -16px rgba(0, 0, 0, 0.2),
                0 40px 80px -24px rgba(0, 0, 0, 0.25);
        }

        .logo-ring {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 120px;
            height: 120px;
            border: 2px dashed var(--gray-300);
            border-radius: 34px;
            animation: ringRotate 20s linear infinite;
        }

        @keyframes logoFloat {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        @keyframes ringRotate {
            0% {
                transform: translate(-50%, -50%) rotate(0deg);
            }

            100% {
                transform: translate(-50%, -50%) rotate(360deg);
            }
        }

        h1 {
            font-size: clamp(2.5rem, 5vw, 4rem);
            font-weight: 800;
            letter-spacing: -0.04em;
            line-height: 1.1;
            color: var(--black);
            margin-bottom: 12px;
        }

        h1 .highlight {
            position: relative;
            color: var(--indigo);
            display: inline-block;
        }

        h1 .highlight::after {
            content: '';
            position: absolute;
            bottom: 4px;
            left: 0;
            width: 100%;
            height: 8px;
            background: var(--indigo);
            opacity: 0.15;
            border-radius: 4px;
            z-index: -1;
        }

        .hero-subtitle {
            font-size: 1.2rem;
            color: var(--gray-500);
            line-height: 1.7;
            max-width: 560px;
            margin: 0 auto;
            font-weight: 450;
        }

        .hero-subtitle strong {
            color: var(--black);
            font-weight: 650;
        }

        /* Main card */
        .main-card {
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: 24px;
            padding: 48px;
            box-shadow:
                0 1px 2px rgba(0, 0, 0, 0.04),
                0 8px 16px -6px rgba(0, 0, 0, 0.06),
                0 24px 48px -12px rgba(0, 0, 0, 0.08);
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }

        .main-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--indigo), var(--emerald), #a855f7);
            opacity: 0.8;
        }

        .main-card:hover {
            box-shadow:
                0 1px 2px rgba(0, 0, 0, 0.04),
                0 12px 24px -8px rgba(0, 0, 0, 0.08),
                0 32px 64px -16px rgba(0, 0, 0, 0.12);
            transform: translateY(-2px);
        }

        .status-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 32px;
            flex-wrap: wrap;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 100px;
            font-size: 0.9rem;
            font-weight: 600;
            letter-spacing: 0.01em;
        }

        .status-badge.success {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .status-badge.info {
            background: #eef2ff;
            color: #3730a3;
            border: 1px solid #c7d2fe;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
        }

        .status-dot.green {
            background: var(--emerald-light);
            animation: dotPulse 2s ease-in-out infinite;
        }

        .status-dot.indigo {
            background: var(--indigo-light);
        }

        @keyframes dotPulse {

            0%,
            100% {
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4);
            }

            50% {
                box-shadow: 0 0 0 8px rgba(16, 185, 129, 0);
            }
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--black);
            text-align: center;
            margin-bottom: 8px;
        }

        .card-desc {
            text-align: center;
            color: var(--gray-500);
            font-size: 0.95rem;
            margin-bottom: 40px;
            font-weight: 450;
        }

        /* Action buttons */
        .actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 14px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 14px 28px;
            border-radius: 16px;
            font-size: 0.95rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            letter-spacing: 0.01em;
            position: relative;
            overflow: hidden;
        }

        .btn-primary {
            background: var(--black);
            color: var(--white);
            border: 2px solid var(--black);
        }

        .btn-primary:hover {
            background: var(--gray-900);
            border-color: var(--gray-900);
            transform: translateY(-3px);
            box-shadow:
                0 8px 24px -6px rgba(0, 0, 0, 0.2),
                0 16px 40px -12px rgba(0, 0, 0, 0.3);
        }

        .btn-outline {
            background: var(--white);
            color: var(--black);
            border: 2px solid var(--gray-200);
        }

        .btn-outline:hover {
            background: var(--gray-50);
            border-color: var(--black);
            transform: translateY(-3px);
            box-shadow:
                0 8px 24px -6px rgba(0, 0, 0, 0.08),
                0 16px 32px -12px rgba(0, 0, 0, 0.12);
        }

        .btn svg {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }

        .btn-primary svg {
            stroke: var(--white);
        }

        .btn-outline svg {
            stroke: var(--black);
        }

        /* Footer */
        .footer-section {
            text-align: center;
            margin-top: 40px;
        }

        .version-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            background: var(--gray-100);
            border: 1px solid var(--gray-200);
            border-radius: 100px;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--gray-600);
            letter-spacing: 0.02em;
        }

        .version-pill::before {
            content: '';
            width: 6px;
            height: 6px;
            background: var(--emerald-light);
            border-radius: 50%;
        }

        .footer-text {
            margin-top: 16px;
            color: var(--gray-400);
            font-size: 0.9rem;
            font-weight: 450;
        }

        .footer-text a {
            color: var(--indigo);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }

        .footer-text a:hover {
            color: var(--indigo-dark);
            text-decoration: underline;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-card {
                padding: 32px 24px;
                border-radius: 20px;
            }

            h1 {
                font-size: 2.2rem;
            }

            .logo-hexagon {
                width: 80px;
                height: 80px;
                font-size: 2.5rem;
                border-radius: 22px;
            }

            .logo-ring {
                width: 100px;
                height: 100px;
                border-radius: 28px;
            }

            .actions {
                flex-direction: column;
                align-items: stretch;
            }

            .btn {
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 20px 16px;
            }

            h1 {
                font-size: 1.8rem;
            }

            .hero-subtitle {
                font-size: 1rem;
            }

            .main-card {
                padding: 24px 16px;
            }

            .btn {
                padding: 12px 20px;
                font-size: 0.9rem;
            }
        }
    </style>
</head>

<body>
    <!-- Animated Background -->
    <div class="bg-decoration bg-blob-1"></div>
    <div class="bg-decoration bg-blob-2"></div>
    <div class="bg-decoration bg-blob-3"></div>
    <div class="grid-pattern"></div>

    <div class="container">
        <!-- Hero -->
        <div class="hero">
            <div class="logo-wrapper">
                <div class="logo-ring"></div>
                <div class="logo-hexagon">⚡</div>
            </div>
            <h1>
                Danial<span class="highlight">MVC</span>
            </h1>
            <p class="hero-subtitle">
                A <strong>lightning-fast</strong>, <strong>elegant</strong> MVC framework
                crafted with obsessive attention to detail by
                <strong>Danial Jamshidi</strong>.
            </p>
        </div>

        <!-- Main Card -->
        <div class="main-card">
            <div class="status-row">
                <span class="status-badge success">
                    <span class="status-dot green"></span> System Online
                </span>
                <span class="status-badge info">
                    <span class="status-dot indigo"></span> Ready to Build
                </span>
            </div>

            <h2 class="card-title">Your application is ready.</h2>
            <p class="card-desc">Explore the documentation or dive into your code editor.</p>

            <div class="actions">
                <a href="https://github.com/DanialJamshidi" target="_blank" class="btn btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22" />
                    </svg>
                    GitHub
                </a>
                <a href="https://danialjamshidi.ir" target="_blank" class="btn btn-outline">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="2" y1="12" x2="22" y2="12" />
                        <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" />
                    </svg>
                    Website
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer-section">
            <span class="version-pill">v2.0.0 — Stable</span>
            <p class="footer-text">
                Built with passion by
                <a href="https://github.com/DanialJamshidi" target="_blank">Danial Jamshidi</a>
                · © 2026
            </p>
        </div>
    </div>
</body>

</html>