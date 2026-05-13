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
<body x-data="authPortal">
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

    <!-- Global Biometric Identity Scanner -->
    <template x-teleport="body">
         <div x-show="scannerOpen" 
             style="position: fixed !important; top: 0 !important; left: 0 !important; width: 100vw !important; height: 100vh !important; z-index: 2147483647 !important; background: rgba(255, 255, 255, 0.75) !important;"
             x-transition:enter="transition ease-out duration-400"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-cloak>
            
            <div class="card" 
                 style="position: absolute !important; top: 50% !important; left: 50% !important; transform: translate(-50%, -50%) !important; width: 95% !important; max-width: 960px !important; background: #fff !important; padding: 0 !important; overflow: hidden !important; border-radius: 40px !important; box-shadow: 0 40px 100px rgba(0, 0, 0, 0.1) !important; border: 1px solid rgba(0, 0, 0, 0.05) !important;">
                
                <div class="scanner-card" style="padding: 2.5rem 3.5rem; border-bottom: 2px solid rgba(0, 0, 0, 0.03); display: flex; justify-content: space-between; align-items: center; background: #fff;">
                    <div style="display: flex; align-items: center; gap: 24px;">
                        <div style="width: 56px; height: 56px; background: var(--accent); border-radius: 16px; display: grid; place-items: center; box-shadow: 0 10px 25px rgba(59, 130, 246, 0.2);">
                            <i data-lucide="scan-eye" style="width: 32px; height: 32px; color: #fff;"></i>
                        </div>
                        <div>
                            <h3 style="margin: 0; font-size: 1.85rem; font-weight: 950; color: #0f172a; letter-spacing: -0.04em;">Identity Intelligence Scan</h3>
                            <p style="margin: 0; font-size: 0.875rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.15em; font-weight: 700;">Neural Engine v4.0</p>
                        </div>
                    </div>
                    <button @click="closeScanner()" style="background: #f1f5f9; border: none; cursor: pointer; color: #64748b; width: 56px; height: 56px; border-radius: 50%; display: grid; place-items: center; transition: background-color 0.3s cubic-bezier(0.4, 0, 0.2, 1), color 0.3s cubic-bezier(0.4, 0, 0.2, 1), transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);">
                        <i data-lucide="x" style="width: 28px; height: 28px;"></i>
                    </button>
                </div>

                <div style="position: relative; aspect-ratio: 16/10; background: #f8fafc; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                    <video id="auth-video" autoplay muted playsinline style="width: 100%; height: 100%; object-fit: cover; transform: scaleX(-1); filter: brightness(1.15) contrast(1.1) saturate(1.1);"></video>
                    <canvas id="auth-canvas" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 15; pointer-events: none; transform: scaleX(-1);"></canvas>
                    
                    <!-- Neural Targeting HUD -->
                    <div style="position: absolute; inset: 60px; border: 1px solid rgba(0, 0, 0, 0.05); border-radius: 40px; pointer-events: none;">
                        <div style="position: absolute; top: 0; left: 0; width: 80px; height: 80px; border-top: 5px solid var(--accent); border-left: 5px solid var(--accent); border-radius: 30px 0 0 0; box-shadow: -10px -10px 25px rgba(59, 130, 246, 0.1);"></div>
                        <div style="position: absolute; top: 0; right: 0; width: 80px; height: 80px; border-top: 5px solid var(--accent); border-right: 5px solid var(--accent); border-radius: 0 30px 0 0; box-shadow: 10px -10px 25px rgba(59, 130, 246, 0.1);"></div>
                        <div style="position: absolute; bottom: 0; left: 0; width: 80px; height: 80px; border-bottom: 5px solid var(--accent); border-left: 5px solid var(--accent); border-radius: 0 0 0 30px; box-shadow: -10px 10px 25px rgba(59, 130, 246, 0.1);"></div>
                        <div style="position: absolute; bottom: 0; right: 0; width: 80px; height: 80px; border-bottom: 5px solid var(--accent); border-right: 5px solid var(--accent); border-radius: 0 0 30px 0; box-shadow: 10px 10px 25px rgba(59, 130, 246, 0.1);"></div>
                    </div>

                    <div style="position: absolute; inset: 0; border: 10px solid transparent; transition: border-color 0.6s cubic-bezier(0.4, 0, 0.2, 1), background-color 0.6s cubic-bezier(0.4, 0, 0.2, 1); pointer-events: none;"
                         :style="faceDetected ? 'border-color: #22c55e; background: rgba(34, 197, 94, 0.05);' : 'border-color: rgba(59, 130, 246, 0.2);'">
                        
                        <!-- Neural Laser Sweep -->
                        <div class="scanning-laser" x-show="!isAuthenticating"></div>
                        
                        <div x-show="!faceDetected && !isAuthenticating" style="position: absolute; bottom: 2rem; left: 50%; transform: translateX(-50%); background: #fff; backdrop-filter: blur(25px); padding: 1rem 2rem; border-radius: 20px; color: #0f172a; font-size: 1rem; font-weight: 800; display: flex; align-items: center; gap: 1rem; border: 1px solid rgba(0, 0, 0, 0.05); box-shadow: 0 15px 35px rgba(0,0,0,0.1); z-index: 20;">
                            <div style="width: 12px; height: 12px; border-radius: 50%; background: #ef4444;"></div>
                            <span>Seeking Biometric Data...</span>
                        </div>
                        <template x-if="faceDetected && !isAuthenticating">
                            <div style="position: absolute; bottom: 2rem; left: 50%; transform: translateX(-50%); background: #fff; padding: 0.5rem 1rem 0.5rem 0.5rem; border-radius: 99px; color: #0f172a; font-size: 1rem; font-weight: 800; display: flex; align-items: center; gap: 0.75rem; border: 1px solid rgba(0, 0, 0, 0.05); box-shadow: 0 15px 35px rgba(0,0,0,0.1); z-index: 20;">
                                <template x-if="userAvatar">
                                    <img :src="userAvatar" alt="" style="width: 36px; height: 36px; border-radius: 50%; object-fit: cover; border: 2px solid #22c55e; flex-shrink: 0;">
                                </template>
                                <template x-if="!userAvatar">
                                    <div style="width: 36px; height: 36px; border-radius: 50%; background: #22c55e; flex-shrink: 0; display: grid; place-items: center; color: #fff; font-size: 14px; font-weight: 700; border: 2px solid #22c55e;">
                                        <span x-text="userName.charAt(0).toUpperCase()"></span>
                                    </div>
                                </template>
                                <span x-text="'Welcome, ' + userName"></span>
                            </div>
                        </template>
                    </div>

                    <!-- Authorization Flow Overlay -->
                    <div x-show="isAuthenticating" 
                         style="position: absolute; inset: 0; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(25px); display: flex; flex-direction: column; align-items: center; justify-content: center; color: #1e293b; z-index: 200;"
                         x-transition:enter="transition ease-in-out duration-500"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100">
                        
                        <template x-if="!authSuccess">
                            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 4rem; padding-top: 4rem;">
                                <div class="neural-spinner"></div>
                                <div style="text-align: center;">
                                    <h4 style="font-weight: 950; font-size: 3rem; margin: 0; letter-spacing: 0.2em; color: var(--accent); text-shadow: 0 10px 30px rgba(59, 130, 246, 0.15);">ACCESS GRANTED</h4>
                                    <p style="font-size: 1.25rem; color: #64748b; margin-top: 1.5rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.15em;">Establishing Secure Bridge...</p>
                                </div>
                            </div>
                        </template>

                        <template x-if="authSuccess">
                            <div style="text-align: center; animation: zoom-in-up 0.8s cubic-bezier(0.34, 1.56, 0.64, 1); padding-top: 4rem;">
                                <template x-if="userAvatar">
                                    <img :src="userAvatar" alt="" style="width: 140px; height: 140px; border-radius: 50%; object-fit: cover; margin: 0 auto 2rem; border: 5px solid #22c55e; box-shadow: 0 20px 60px rgba(34, 197, 94, 0.3);">
                                </template>
                                <template x-if="!userAvatar">
                                    <div style="width: 140px; height: 140px; border-radius: 50%; background: linear-gradient(135deg, #22c55e, #16a34a); margin: 0 auto 2rem; border: 5px solid #fff; box-shadow: 0 20px 60px rgba(34, 197, 94, 0.3); display: grid; place-items: center;">
                                        <span style="font-size: 4rem; font-weight: 900; color: #fff; font-family: system-ui, sans-serif; line-height: 1;" x-text="userName.charAt(0).toUpperCase()"></span>
                                    </div>
                                </template>
                                <h4 style="font-weight: 950; font-size: clamp(2.5rem, 10vw, 4.5rem); margin: 0; letter-spacing: -0.04em; color: #0f172a; line-height: 1;">WELCOME</h4>
                                <p style="font-size: clamp(1.5rem, 6vw, 2.5rem); font-weight: 400; color: #16a34a; margin-top: 1rem; font-family: 'Instrument Serif', serif; letter-spacing: 0.02em;" x-text="userName"></p>
                                
                                <div style="margin-top: 3.5rem; width: min(80vw, 300px); height: 6px; background: #f1f5f9; border-radius: 3px; margin-left: auto; margin-right: auto; overflow: hidden; position: relative;">
                                    <div class="success-progress-bar"></div>
                                </div>
                                <p style="margin-top: 1.25rem; font-size: 0.875rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.3em; font-weight: 800; animation: pulse 1s infinite;">Synchronizing Workspace...</p>
                            </div>
                        </template>
                    </div>

                    <style>
                        .scanner-card { padding: 2.5rem 3.5rem; }
                        @media (max-width: 480px) { .scanner-card { padding: 1.25rem !important; } }
                        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
                        @keyframes zoom-in-up {
                            0% { opacity: 0; transform: scale(0.5) translateY(40px); }
                            100% { opacity: 1; transform: scale(1) translateY(0); }
                        }
                        @keyframes scan-sweep {
                            0%, 100% { top: 0%; opacity: 0; }
                            5%, 95% { opacity: 1; }
                            50% { top: 100%; }
                        }
                        .neural-spinner {
                            width: 12rem; height: 12rem; border: 10px solid #f1f5f9; border-top-color: var(--accent); border-radius: 50%; 
                            animation: spin 0.8s linear infinite; box-shadow: 0 0 80px rgba(59, 130, 246, 0.1);
                        }
                        .scanning-laser {
                            position: absolute; left: 0; right: 0; height: 4px; z-index: 10;
                            background: linear-gradient(90deg, transparent, var(--accent), transparent);
                            box-shadow: 0 0 40px var(--accent);
                            animation: scan-sweep 3s ease-in-out infinite;
                            pointer-events: none;
                        }
                        .success-progress-bar {
                            position: absolute; left: 0; top: 0; height: 100%; background: #22c55e; width: 0%;
                            animation: progress-load 2.5s cubic-bezier(0.4, 0, 0.2, 1) forwards;
                        }
                        @keyframes progress-load { 0% { width: 0%; } 100% { width: 100%; } }
                        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
                    </style>
                </div>
            </div>
        </div>
    </template>

    @stack('scripts')
    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('authPortal', () => ({
                scannerOpen: false,
                faceDetected: false,
                isScanningFace: false,
                isAuthenticating: false,
                isLoadingModels: false,
                authSuccess: false,
                userName: '',
                userAvatar: '',
                video: null,
                modelsLoaded: false,

                async openScanner() {
                    if (this.isLoadingModels) return;
                    
                    this.isLoadingModels = true;
                    try {
                        await this.loadModels();
                        this.scannerOpen = true;
                        this.isLoadingModels = false;
                        this.startCamera();
                    } catch (err) {
                        console.error("Face API Initialization Error:", err);
                        Swal.fire('Neural Engine Error', 'Failed to load face recognition models.', 'error');
                        this.isLoadingModels = false;
                    }
                },

                async loadModels() {
                    if (this.modelsLoaded) return;
                    const MODEL_URL = 'https://cdn.jsdelivr.net/gh/justadudewhohacks/face-api.js@v0.22.2/weights';
                    await Promise.all([
                        faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
                        faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                        faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
                    ]);
                    this.modelsLoaded = true;
                },

                async startCamera() {
                    // Wait for Alpine to finish x-teleport + x-show DOM updates
                    await this.$nextTick();
                    await this.$nextTick();
                    // Small extra delay for transition to start
                    await new Promise(r => setTimeout(r, 100));

                    this.video = document.getElementById('auth-video');
                    if (!this.video) {
                        // If teleport hasn't finished yet, retry after a longer delay
                        await new Promise(r => setTimeout(r, 300));
                        this.video = document.getElementById('auth-video');
                    }

                    if (this.video) {
                        await this.initStream();
                    } else {
                        Swal.fire('Camera Error', 'Could not find camera element. Please try again.', 'error');
                        this.closeScanner();
                    }
                },

                async initStream() {
                    try {
                        const stream = await navigator.mediaDevices.getUserMedia({ video: true });
                        if (this.video) {
                            this.video.srcObject = stream;
                            // Explicit play() required — autoplay is blocked after x-teleport
                            await this.video.play();
                            this.startDetection();
                        }
                    } catch (err) {
                        Swal.fire('Camera Error', 'Face recognition requires camera access.', 'error');
                        this.closeScanner();
                    }
                },

                startDetection() {
                    const canvas = document.getElementById('auth-canvas');
                    if (!canvas) return;

                    let missCount = 0;
                    const MISS_GRACE = 12;
                    let lastDescriptor = null;

                    const detectorOptions = new faceapi.TinyFaceDetectorOptions({
                        inputSize: 320,
                        scoreThreshold: 0.05
                    });

                    const detectFrame = async () => {
                        if (!this.scannerOpen || this.isAuthenticating) {
                            canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
                            return;
                        }

                        if (!this.modelsLoaded || !this.video || this.video.readyState < 2) {
                            setTimeout(detectFrame, 100);
                            return;
                        }

                        const displaySize = { width: this.video.clientWidth, height: this.video.clientHeight };
                        if (displaySize.width === 0) {
                            setTimeout(detectFrame, 100);
                            return;
                        }

                        faceapi.matchDimensions(canvas, displaySize);

                        try {
                            const detection = await faceapi.detectSingleFace(this.video, detectorOptions)
                                                           .withFaceLandmarks()
                                                           .withFaceDescriptor();

                            if (detection) {
                                missCount = 0;
                                this.faceDetected = true;
                                lastDescriptor = detection.descriptor;

                                const resized = faceapi.resizeResults(detection, displaySize);
                                const ctx = canvas.getContext('2d');
                                ctx.clearRect(0, 0, canvas.width, canvas.height);

                                const landmarks = resized.landmarks;
                                const parts = [
                                    { pts: landmarks.getJawOutline(),   close: false, color: 'rgba(59,130,246,0.7)' },
                                    { pts: landmarks.getNose(),         close: false, color: 'rgba(59,130,246,0.7)' },
                                    { pts: landmarks.getMouth(),        close: true,  color: 'rgba(99,102,241,0.8)' },
                                    { pts: landmarks.getLeftEye(),      close: true,  color: 'rgba(59,130,246,0.9)' },
                                    { pts: landmarks.getRightEye(),     close: true,  color: 'rgba(59,130,246,0.9)' },
                                    { pts: landmarks.getLeftEyeBrow(),  close: false, color: 'rgba(99,102,241,0.7)' },
                                    { pts: landmarks.getRightEyeBrow(), close: false, color: 'rgba(99,102,241,0.7)' },
                                ];

                                parts.forEach(({ pts, close, color }) => {
                                    if (!pts || pts.length < 2) return;
                                    ctx.save();
                                    ctx.shadowColor = '#3B82F6';
                                    ctx.shadowBlur = 8;
                                    ctx.strokeStyle = color;
                                    ctx.lineWidth = 2.5;
                                    ctx.beginPath();
                                    ctx.moveTo(pts[0].x, pts[0].y);
                                    for (let i = 1; i < pts.length; i++) ctx.lineTo(pts[i].x, pts[i].y);
                                    if (close) ctx.closePath();
                                    ctx.stroke();
                                    ctx.restore();
                                });

                                landmarks.positions.forEach(pt => {
                                    ctx.save();
                                    ctx.shadowColor = '#60A5FA';
                                    ctx.shadowBlur = 6;
                                    ctx.strokeStyle = 'rgba(147,197,253,0.9)';
                                    ctx.lineWidth = 1;
                                    ctx.beginPath();
                                    ctx.arc(pt.x, pt.y, 4, 0, 2 * Math.PI);
                                    ctx.stroke();
                                    ctx.fillStyle = '#3B82F6';
                                    ctx.beginPath();
                                    ctx.arc(pt.x, pt.y, 2.5, 0, 2 * Math.PI);
                                    ctx.fill();
                                    ctx.restore();
                                });

                                const box = resized.detection.box;
                                const cSize = 20;
                                ctx.save();
                                ctx.shadowColor = '#22C55E';
                                ctx.shadowBlur = 12;
                                ctx.strokeStyle = '#22C55E';
                                ctx.lineWidth = 3;
                                ctx.beginPath(); ctx.moveTo(box.x, box.y + cSize); ctx.lineTo(box.x, box.y); ctx.lineTo(box.x + cSize, box.y); ctx.stroke();
                                ctx.beginPath(); ctx.moveTo(box.x + box.width - cSize, box.y); ctx.lineTo(box.x + box.width, box.y); ctx.lineTo(box.x + box.width, box.y + cSize); ctx.stroke();
                                ctx.beginPath(); ctx.moveTo(box.x, box.y + box.height - cSize); ctx.lineTo(box.x, box.y + box.height); ctx.lineTo(box.x + cSize, box.y + box.height); ctx.stroke();
                                ctx.beginPath(); ctx.moveTo(box.x + box.width - cSize, box.y + box.height); ctx.lineTo(box.x + box.width, box.y + box.height); ctx.lineTo(box.x + box.width, box.y + box.height - cSize); ctx.stroke();
                                ctx.restore();

                            } else {
                                missCount++;
                                if (missCount >= MISS_GRACE) {
                                    this.faceDetected = false;
                                    lastDescriptor = null;
                                    canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
                                }
                            }

                            if (this.faceDetected && !this.isAuthenticating && !this.isScanningFace && lastDescriptor) {
                                this.isScanningFace = true;
                                const frozenDescriptor = lastDescriptor;
                                setTimeout(() => {
                                    if (this.scannerOpen && lastDescriptor) {
                                        this.verifyIdentity(frozenDescriptor);
                                    } else {
                                        this.isScanningFace = false;
                                    }
                                }, 3000);
                            }
                        } catch (e) {
                            console.error("Detection frame error:", e);
                        }

                        setTimeout(detectFrame, 100);
                    };

                    detectFrame();
                },

                async verifyIdentity(descriptor) {
                    this.isAuthenticating = true;
                    
                    try {
                        const response = await fetch("{{ route('login.biometrics') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ descriptor: Array.from(descriptor) })
                        });

                        const result = await response.json();

                        if (response.ok && result.success) {
                            this.userName = result.name;
                            this.userAvatar = result.avatar_url || '';
                            this.authSuccess = true;
                            
                            // Animated success greeting delay
                            setTimeout(() => {
                                window.location.href = result.redirect;
                            }, 2500);
                        } else {
                            this.isAuthenticating = false;
                            this.faceDetected = false;
                            this.isScanningFace = false;
                            setTimeout(() => this.startDetection(), 2000);
                        }
                    } catch (err) {
                        this.isAuthenticating = false;
                        this.isScanningFace = false;
                        console.error(err);
                    }
                },

                closeScanner() {
                    if (this.video?.srcObject) {
                        this.video.srcObject.getTracks().forEach(track => track.stop());
                    }
                    this.scannerOpen = false;
                    this.isAuthenticating = false;
                    this.isScanningFace = false;
                    this.authSuccess = false;
                    this.userName = '';
                    this.userAvatar = '';
                }
            }));
        });

        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });
    </script>
</body>
</html>
