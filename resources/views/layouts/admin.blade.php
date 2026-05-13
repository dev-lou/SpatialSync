<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='20' fill='%230066FF'/><path d='M50 20 L80 38 L80 62 L50 80 L20 62 L20 38 Z' fill='none' stroke='white' stroke-width='5'/><path d='M50 20 L50 80 M20 38 L80 62 M80 38 L20 62' stroke='white' stroke-width='3' opacity='0.5'/></svg>">
    <title>@yield('title', 'Admin') — SpatialSync OS</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    <!-- ONLY Admin OS styles — no conflicts with user-facing CSS -->
    <link rel="stylesheet" href="{{ asset('css/admin-os.css') }}">

    <!-- Icons & Libraries -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
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
            font-family: 'Inter', system-ui, sans-serif !important;
            border-radius: 20px !important;
            padding: 2rem !important;
            border: 1px solid rgba(0,0,0,0.06) !important;
            box-shadow: 0 25px 60px rgba(0,0,0,0.12) !important;
        }
        .swal-global-title { font-weight: 700 !important; letter-spacing: -0.02em !important; }
        .swal-global-body { font-size: 0.95rem !important; line-height: 1.6 !important; }
    </style>

    <style>
        @media (max-width: 768px) {
            .os-mobile-trigger { display: flex !important; }
            .os-mobile-toggle { display: flex !important; }
            .os-breadcrumb-prefix,
            .os-breadcrumb-prefix + span { display: none !important; }
            .os-breadcrumb-section { display: none !important; }
            .os-breadcrumb-section + span { display: none !important; }
        }
    </style>
    @stack('styles')
</head>
<body x-data="{ searchOpen: false, mobNavOpen: false, theme: localStorage.getItem('admin-theme') || 'light' }" @open-search.window="searchOpen=true" :class="theme" x-init="$watch('theme', val => { document.documentElement.classList.toggle('dark', val === 'dark'); localStorage.setItem('admin-theme', val); })">

    {{-- SIDEBAR --}}
    <x-admin.sidebar />

    {{-- MOBILE NAV TOGGLE --}}
    <button class="os-mobile-toggle" @click="mobNavOpen = !mobNavOpen" aria-label="Toggle navigation" style="display:none;position:fixed;bottom:16px;left:16px;z-index:999;width:48px;height:48px;border-radius:50%;background:var(--c-accent);color:#fff;border:none;box-shadow:0 4px 16px rgba(0,102,255,0.3);cursor:pointer;align-items:center;justify-content:center;">
        <i data-lucide="menu" style="width:20px;height:20px;"></i>
    </button>

    {{-- MOBILE NAV DRAWER --}}
    <div x-show="mobNavOpen" x-cloak @click.self="mobNavOpen=false" @keydown.escape.window="mobNavOpen=false" style="position:fixed;inset:0;z-index:9990;background:rgba(0,0,0,0.4);" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
        <div style="position:absolute;top:0;left:0;bottom:0;width:min(85vw,300px);background:var(--c-surface);padding:24px 16px;box-shadow:4px 0 24px rgba(0,0,0,0.1);overflow-y:auto;" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;padding-bottom:16px;border-bottom:1px solid var(--c-border);">
                <span style="font-size:15px;font-weight:700;color:var(--c-text);">Admin OS</span>
                <button @click="mobNavOpen=false" style="width:32px;height:32px;display:grid;place-items:center;border:none;background:var(--c-bg);border-radius:8px;cursor:pointer;color:var(--c-muted);">
                    <i data-lucide="x" style="width:16px;height:16px;"></i>
                </button>
            </div>
            @foreach([
                ['icon'=>'layout-dashboard', 'label'=>'Mission Control',  'route'=>'admin.dashboard'],
                ['icon'=>'users',             'label'=>'Users & Tiers',    'route'=>'admin.users'],
                ['icon'=>'layers',            'label'=>'All Builds',       'route'=>'admin.builds'],
                ['icon'=>'package',           'label'=>'Asset Library',    'route'=>'admin.presets'],
                ['icon'=>'shield-check',      'label'=>'Biometric Auth',   'route'=>'admin.security'],
            ] as $item)
            <a href="{{ route($item['route']) }}" @click="mobNavOpen=false" style="display:flex;align-items:center;gap:10px;padding:12px 12px;border-radius:10px;text-decoration:none;color:var(--c-text);font-size:14px;font-weight:500;margin-bottom:4px;transition:background .15s;" @mouseenter="$el.style.background='var(--c-bg)'" @mouseleave="$el.style.background=''">
                <i data-lucide="{{ $item['icon'] }}" style="width:16px;height:16px;color:var(--c-muted);flex-shrink:0;"></i>
                {{ $item['label'] }}
            </a>
            @endforeach
        </div>
    </div>

    {{-- TOP BAR --}}
    <header class="os-topbar">
        <div style="display:flex;align-items:center;gap:8px;">
            <button class="os-mobile-trigger" @click="mobNavOpen = true" aria-label="Open navigation" style="display:none;width:32px;height:32px;border:none;background:var(--c-bg);border-radius:8px;cursor:pointer;color:var(--c-muted);align-items:center;justify-content:center;flex-shrink:0;">
                <i data-lucide="menu" style="width:16px;height:16px;"></i>
            </button>
            <span class="os-breadcrumb-prefix" style="font-size:13px;color:var(--c-muted);">SpatialSync</span>
            <span style="color:var(--c-border);font-size:13px;">/</span>
            <span class="os-breadcrumb-section" style="font-size:13px;color:var(--c-muted);">Admin OS</span>
            <span style="color:var(--c-border);font-size:13px;">/</span>
            <span style="font-size:13px;font-weight:600;color:var(--c-text);">@yield('title', 'Overview')</span>
        </div>

        <div style="display:flex;align-items:center;gap:8px;">
            {{-- Search trigger --}}
            <button @click="searchOpen=true" class="os-icon-btn" title="Search (Ctrl+K)">
                <i data-lucide="search" style="width:14px;height:14px;"></i>
            </button>

            {{-- Notifications --}}
            <button class="os-icon-btn" title="Notifications">
                <i data-lucide="bell" style="width:14px;height:14px;"></i>
            </button>

            {{-- Theme toggle --}}
            <button class="os-icon-btn" title="Theme" @click="theme = theme === 'dark' ? 'light' : 'dark'">
                <i data-lucide="sun" style="width:14px;height:14px;"></i>
            </button>

            <div style="width:1px;height:20px;background:var(--c-border);margin:0 4px;"></div>

            {{-- Admin Avatar --}}
            <div style="
                width:30px;height:30px;border-radius:50%;
                background:linear-gradient(135deg,#0066FF,#7C3AED);
                display:flex;align-items:center;justify-content:center;
                font-size:11px;font-weight:700;color:white;cursor:pointer;
                font-family:var(--font-sans);
            ">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}</div>
        </div>
    </header>

    {{-- MAIN --}}
    <main class="os-main">
        <div class="os-page-header">
            <div>
                <h1 class="os-page-title">@yield('title', 'Overview')</h1>
                <p class="os-page-subtitle">@yield('subtitle', '')</p>
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
                @yield('actions')
            </div>
        </div>

        @yield('content')
    </main>

    {{-- COMMAND SEARCH OVERLAY --}}
    <div
        x-show="searchOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click.self="searchOpen=false"
        @keydown.escape.window="searchOpen=false"
        style="position:fixed;inset:0;background:rgba(0,0,0,0.4);backdrop-filter:blur(4px);z-index:999;display:flex;align-items:flex-start;justify-content:center;padding-top:12vh;">
        <div style="
            background:var(--c-surface);border:1px solid var(--c-border);
            border-radius:16px;width:100%;max-width:560px;
            box-shadow:0 25px 50px rgba(0,0,0,0.25);overflow:hidden;
        ">
            <div style="display:flex;align-items:center;gap:12px;padding:14px 16px;border-bottom:1px solid var(--c-border);">
                <i data-lucide="search" style="width:16px;height:16px;color:var(--c-muted);flex-shrink:0;"></i>
                <input type="text" placeholder="Search builds, users, assets…"
                    style="flex:1;border:none;outline:none;font-size:14px;color:var(--c-text);background:transparent;font-family:var(--font-sans);"
                    x-ref="searchInput" x-init="$watch('searchOpen', v => v && $nextTick(() => $refs.searchInput?.focus()))">
                <kbd style="font-family:var(--font-sans);font-size:10px;font-weight:700;color:var(--c-muted);background:var(--c-bg);border:1px solid var(--c-border);padding:2px 5px;border-radius:5px;">ESC</kbd>
            </div>
            <div style="padding:8px;">
                @foreach([
                    ['icon' => 'layout-dashboard', 'label' => 'Mission Control',  'route' => 'admin.dashboard', 'type' => 'Page'],
                    ['icon' => 'users',             'label' => 'Users & Tiers',    'route' => 'admin.users',     'type' => 'Page'],
                    ['icon' => 'layers',            'label' => 'All Builds',       'route' => 'admin.builds',    'type' => 'Page'],
                    ['icon' => 'package',           'label' => 'Asset Library',    'route' => 'admin.presets',   'type' => 'Page'],
                    ['icon' => 'shield-check',      'label' => 'Biometric Auth',   'route' => 'admin.security',  'type' => 'Page'],
                ] as $item)
                <a href="{{ route($item['route']) }}" @click="searchOpen=false" style="
                    display:flex;align-items:center;gap:10px;padding:9px 10px;
                    border-radius:8px;text-decoration:none;color:var(--c-text);
                    font-size:13.5px;font-weight:500;transition:background .1s;
                " @mouseenter="$el.style.background='var(--c-bg)'" @mouseleave="$el.style.background=''">
                    <i data-lucide="{{ $item['icon'] }}" style="width:15px;height:15px;color:var(--c-muted);flex-shrink:0;"></i>
                    {{ $item['label'] }}
                    <span style="margin-left:auto;font-size:10px;font-weight:700;color:var(--c-muted);background:var(--c-bg);border:1px solid var(--c-border);padding:1px 6px;border-radius:4px;">{{ $item['type'] }}</span>
                </a>
                @endforeach
            </div>
            <div style="padding:10px 16px;background:var(--c-bg);border-top:1px solid var(--c-border);display:flex;align-items:center;gap:16px;">
                <span style="font-size:11px;color:var(--c-muted);display:flex;align-items:center;gap:5px;"><kbd style="background:var(--c-surface);border:1px solid var(--c-border);padding:1px 4px;border-radius:3px;font-family:var(--font-sans);">↵</kbd> Select</span>
                <span style="font-size:11px;color:var(--c-muted);display:flex;align-items:center;gap:5px;"><kbd style="background:var(--c-surface);border:1px solid var(--c-border);padding:1px 4px;border-radius:3px;font-family:var(--font-sans);">ESC</kbd> Close</span>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });
        document.addEventListener('keydown', e => {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                // Alpine v3 compatible — dispatch a custom event the body x-data can listen for
                document.body.dispatchEvent(new CustomEvent('open-search'));
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
