<!DOCTYPE html>
<html lang="en" prefix="og: http://ogp.me/ns#">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='20' fill='%230066FF'/><path d='M50 20 L80 38 L80 62 L50 80 L20 62 L20 38 Z' fill='none' stroke='white' stroke-width='5'/><path d='M50 20 L50 80 M20 38 L80 62 M80 38 L20 62' stroke='white' stroke-width='3' opacity='0.5'/></svg>">
    <link rel="canonical" href="{{ url()->current() }}">
    <!-- Primary Meta Tags -->
    <title>@yield('title', 'SpatialSync') — Identity-Driven Architecture</title>
    <meta name="title" content="SpatialSync — Identity-Driven Architecture">
    <meta name="description" content="Collaborate in real-time on premium 3D architectural blueprints with identity-driven security.">

    <!-- Schema.org for Google+ / Apps -->
    <meta itemprop="name" content="SpatialSync — Identity-Driven Architecture">
    <meta itemprop="description" content="Collaborate in real-time on premium 3D architectural blueprints.">
    <meta itemprop="image" content="{{ url('/images/og-meta.png') }}">

    <!-- Open Graph / Social Media -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="SpatialSync">
    <meta property="og:title" content="SpatialSync — Identity-Driven Architecture">
    <meta property="og:description" content="Collaborate in real-time on premium 3D architectural blueprints with identity-driven security.">
    <meta property="og:image" content="{{ url('/images/og-meta.png') }}">
    <meta property="og:image:secure_url" content="{{ url('/images/og-meta.png') }}">
    <meta property="og:image:type" content="image/png">
    <meta property="og:image:alt" content="SpatialSync — Collaborative 3D architecture and design platform">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:locale" content="en_US">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="SpatialSync — Identity-Driven Architecture">
    <meta property="twitter:description" content="Collaborate in real-time on premium 3D architectural blueprints with identity-driven security.">
    <meta property="twitter:image" content="{{ url('/images/og-meta.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="stylesheet" href="https://fonts.bunny.net/css?family=plus-jakarta-sans:300,400,500,600,700|instrument-serif:400">

    <!-- Design Tokens -->
    <link rel="stylesheet" href="{{ asset('css/tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components.css') }}">

    <!-- Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const _SwalBase = Swal;
        window.Swal = _SwalBase.mixin({
            background: '#ffffff',
            color: '#1a1a2e',
            confirmButtonColor: '#0066FF',
            cancelButtonColor: '#64748b',
            iconColor: '#0066FF',
            customClass: {
                popup: 'swal-global-popup',
                title: 'swal-global-title',
                htmlContainer: 'swal-global-body'
            }
        });
    </script>
    <style>
        .swal-global-popup {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif !important;
            border-radius: 24px !important;
            padding: 2rem !important;
            border: 1px solid rgba(0,0,0,0.06) !important;
            box-shadow: 0 25px 60px rgba(0,0,0,0.12) !important;
        }
        .swal-global-title { font-weight: 800 !important; letter-spacing: -0.02em !important; }
        .swal-global-body { font-size: 1rem !important; line-height: 1.6 !important; }
    </style>

    <style>
        *, *::before, *::after {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            display: flex;
            background: var(--bg);
            margin: 0;
        }

        .auth-layout {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        /* ── LEFT PANE: FORM ───────────────────────────── */
        .auth-pane-form {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: var(--space-8);
            max-width: 100%;
            position: relative;
            background: var(--bg);
            animation: pane-in-left 0.8s var(--ease-out);
        }

        @keyframes pane-in-left {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }

        @media (min-width: 1024px) {
            .auth-pane-form {
                flex: 0 0 520px;
                padding: var(--space-8) min(80px, var(--space-16));
            }
        }

        .auth-form-wrapper {
            margin: auto 0;
            width: 100%;
            max-width: 400px;
            align-self: center;
        }

        /* ── RIGHT PANE: VISUAL ────────────────────────── */
        .auth-pane-visual {
            display: none;
            flex: 1;
            position: relative;
            background: radial-gradient(circle at 50% 50%, #1e1b4b 0%, #0f172a 100%);
            overflow: hidden;
            padding: var(--space-12);
        }

        @media (min-width: 1024px) {
            .auth-pane-visual {
                display: flex;
                align-items: center;
                justify-content: center;
            }
        }

        .auth-pane-visual::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: 
                radial-gradient(circle at 20% 50%, rgba(59, 130, 246, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 80% 30%, rgba(139, 92, 246, 0.08) 0%, transparent 40%);
            pointer-events: none;
        }

        /* Mesh Background Grid */
        .auth-mesh {
            position: absolute;
            inset: 0;
            background-size: 40px 40px;
            background-image: 
                linear-gradient(to right, rgba(255, 255, 255, 0.02) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.02) 1px, transparent 1px);
            perspective: 1000px;
            transform-style: preserve-3d;
            transform: rotateX(60deg) translateY(-100px) translateZ(-200px);
            opacity: 0.5;
        }

        .auth-glass-card {
            position: relative;
            z-index: 10;
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: var(--space-12);
            border-radius: 32px;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.1);
            max-width: 480px;
            overflow: hidden;
        }

        .auth-glass-card::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(125deg, rgba(255,255,255,0.1) 0%, transparent 50%);
            opacity: 0.5;
            pointer-events: none;
        }

        .auth-header {
            margin-bottom: var(--space-10);
        }

        .auth-logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, var(--accent) 0%, #8B5CF6 100%);
            border-radius: 14px;
            color: #fff;
            margin-bottom: var(--space-8);
            text-decoration: none;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .auth-logo:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(59, 130, 246, 0.4);
        }

        .auth-title {
            font-family: var(--font-display);
            font-size: clamp(2.5rem, 5vw, 3.2rem);
            font-weight: 900;
            letter-spacing: -0.02em;
            color: var(--text-primary);
            margin-bottom: var(--space-2);
            line-height: 1;
        }

        .auth-title span {
            background: linear-gradient(to right, var(--accent), #9333EA, var(--accent));
            background-size: 200% auto;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: text-shine 4s linear infinite;
        }

        @keyframes text-shine {
            to { background-position: 200% center; }
        }

        .auth-subtitle {
            font-size: var(--text-base);
            color: var(--text-secondary);
            line-height: 1.6;
            opacity: 0;
            animation: fade-in 0.6s var(--ease-out) 0.3s forwards;
        }

        @keyframes fade-in {
            to { opacity: 1; }
        }

        .auth-form-stagger > * {
            opacity: 0;
            transform: translateY(12px);
            animation: fade-up 0.5s var(--ease-out) forwards;
        }

        @keyframes fade-up {
            to { opacity: 1; transform: translateY(0); }
        }

        .auth-form {
            display: flex;
            flex-direction: column;
            gap: var(--space-4);
        }

        .auth-footer {
            text-align: center;
            margin-top: var(--space-6);
            padding-top: var(--space-6);
            border-top: 1px solid var(--border-default);
            font-size: var(--text-sm);
            color: var(--text-secondary);
        }

        .auth-footer a {
            color: var(--accent);
            font-weight: 500;
            text-decoration: none;
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }
    <!-- Alpine x-cloak style -->
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body>
    <div class="auth-layout">
        <!-- Left: Form -->
        <main class="auth-pane-form">
            <div class="auth-form-wrapper">
                <div class="auth-header">
                    <a href="{{ route('home') }}" class="auth-logo">
                        <i data-lucide="box" class="w-7 h-7"></i>
                    </a>
                    <h1 class="auth-title">
                        @php
                            $title = View::getSection('title', 'Welcome Back');
                            $parts = explode(' ', $title, 2);
                        @endphp
                        @if(count($parts) > 1)
                            {{ $parts[0] }} <span>{{ $parts[1] }}</span>
                        @else
                            {{ $title }}
                        @endif
                    </h1>
                    @hasSection('subtitle')
                        <p class="auth-subtitle">@yield('subtitle')</p>
                    @endif
                </div>

                <div class="auth-form-stagger">
                    @yield('content')
                </div>
            </div>
        </main>

        <!-- Right: Visual -->
        <aside class="auth-pane-visual">
            <div class="auth-mesh"></div>
            
            <div class="auth-glass-card">
                <div style="margin-bottom: var(--space-6);">
                    <i data-lucide="layers" style="width: 32px; height: 32px; color: var(--accent);"></i>
                </div>
                <h2 style="font-family: var(--font-display); font-size: 2rem; font-weight: 800; color: #fff; margin-bottom: var(--space-4); line-height: 1.1; letter-spacing: -0.02em;">
                    Design the <span style="background: linear-gradient(to right, var(--accent), #A855F7); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Impossible.</span>
                </h2>
                <p style="font-size: 1.125rem; color: rgba(255,255,255,0.7); line-height: 1.6; max-width: 400px;">
                    Join the world's most advanced spatial design engine. Built for the next generation of architects and builders to collaborate in real-time.
                </p>
                
                <div style="display: flex; align-items: center; gap: var(--space-4); margin-top: var(--space-8); padding-top: var(--space-6); border-top: 1px solid rgba(255,255,255,0.1);">
                    <div style="display: flex; align-items: center;">
                        <!-- Avatar Stack -->
                        <div style="width: 32px; height: 32px; border-radius: 50%; background: #4F46E5; border: 2px solid #1E293B; display: grid; place-items: center; color: white; font-size: 10px; font-weight: bold; margin-left: 0; z-index: 3;">A</div>
                        <div style="width: 32px; height: 32px; border-radius: 50%; background: #059669; border: 2px solid #1E293B; display: grid; place-items: center; color: white; font-size: 10px; font-weight: bold; margin-left: -8px; z-index: 2;">B</div>
                        <div style="width: 32px; height: 32px; border-radius: 50%; background: #D97706; border: 2px solid #1E293B; display: grid; place-items: center; color: white; font-size: 10px; font-weight: bold; margin-left: -8px; z-index: 1;">C</div>
                    </div>
                    <span style="font-size: 0.875rem; color: rgba(255,255,255,0.6); font-weight: 500;">Trusted by 10,000+ spatial engineers</span>
                </div>
            </div>
        </aside>
    </div>

    @stack('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });
    </script>
</body>
</html>
