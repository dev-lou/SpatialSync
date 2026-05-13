<nav class="navbar" role="navigation" aria-label="Main navigation"
     x-data="{ mobileOpen: false }">
    <div class="container navbar__inner">
        <a href="{{ route('home') }}" class="navbar__brand">
            <span class="navbar__logo">
                <i data-lucide="box" class="w-5 h-5"></i>
            </span>
            SpatialSync
        </a>

        <div class="navbar__nav">
            <a href="{{ route('home') }}" class="navbar__link {{ request()->routeIs('home') ? 'navbar__link--active' : '' }}">
                Home
            </a>
            <a href="{{ route('features') }}" class="navbar__link {{ request()->routeIs('features') ? 'navbar__link--active' : '' }}">
                Features
            </a>
            <a href="{{ route('pricing') }}" class="navbar__link {{ request()->routeIs('pricing') ? 'navbar__link--active' : '' }}">
                Pricing
            </a>
            <a href="{{ route('about') }}" class="navbar__link {{ request()->routeIs('about') ? 'navbar__link--active' : '' }}">
                About
            </a>
        </div>

        <div class="navbar__actions">
            @if($auth_user)
                @if(!request()->routeIs('dashboard'))
                    <a href="{{ route('dashboard') }}" class="btn btn--ghost btn--sm">
                        <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                        Dashboard
                    </a>
                @endif
                
                @php
                    $plan = $auth_user->plan ?? 'free';
                @endphp
                <div class="navbar__plan-badge navbar__plan-badge--{{ $plan }}">
                    {{ strtoupper($plan) }}
                </div>
                
                <x-profile-dropdown />
            @else
                <a href="{{ route('login') }}" class="btn btn--ghost btn--sm">
                    Sign in
                </a>
                <a href="{{ route('register') }}" class="btn btn--primary btn--sm">
                    Get Started
                </a>
            @endif

            <button class="navbar__mobile-toggle" aria-label="Open menu" @click="mobileOpen = true">
                <i data-lucide="menu" class="w-5 h-5"></i>
            </button>
        </div>
    </div>

    {{-- Teleport drawer to <body> to escape navbar's sticky stacking context --}}
    <template x-teleport="body">
        <div x-show="mobileOpen" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             style="position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.4);"
             @click.self="mobileOpen = false">
            <div style="position:absolute;top:0;right:0;bottom:0;width:min(85vw,380px);background:var(--surface,#fff);box-shadow:-8px 0 40px rgba(0,0,0,0.1);display:flex;flex-direction:column;padding:2rem 1.5rem;overflow-y:auto;animation:mobileSlideIn 0.25s cubic-bezier(0.16,1,0.3,1);">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:2rem;">
                    <a href="{{ route('home') }}" style="display:flex;align-items:center;gap:0.5rem;font-weight:700;font-size:1.125rem;text-decoration:none;color:var(--text-primary);">
                        <span style="width:32px;height:32px;border-radius:10px;background:var(--accent);display:grid;place-items:center;"><i data-lucide="box" style="width:18px;height:18px;color:#fff;"></i></span>
                        SpatialSync
                    </a>
                    <button @click="mobileOpen = false" style="width:36px;height:36px;display:grid;place-items:center;border:none;background:var(--bg-secondary);border-radius:8px;cursor:pointer;color:var(--text-secondary);">
                        <i data-lucide="x" style="width:18px;height:18px;"></i>
                    </button>
                </div>
                <div style="display:flex;flex-direction:column;gap:0.75rem;margin-bottom:2.5rem;">
                    <a href="{{ route('home') }}" style="display:flex;align-items:center;gap:0.75rem;padding:1rem;border-radius:12px;font-size:1rem;font-weight:600;text-decoration:none;{{ request()->routeIs('home') ? 'background:var(--accent-light);color:var(--accent);' : 'color:var(--text-primary);' }}" @click="mobileOpen = false">
                        <i data-lucide="home" style="width:18px;height:18px;"></i> Home
                    </a>
                    <a href="{{ route('features') }}" style="display:flex;align-items:center;gap:0.75rem;padding:1rem;border-radius:12px;font-size:1rem;font-weight:600;text-decoration:none;{{ request()->routeIs('features') ? 'background:var(--accent-light);color:var(--accent);' : 'color:var(--text-primary);' }}" @click="mobileOpen = false">
                        <i data-lucide="layers" style="width:18px;height:18px;"></i> Features
                    </a>
                    <a href="{{ route('pricing') }}" style="display:flex;align-items:center;gap:0.75rem;padding:1rem;border-radius:12px;font-size:1rem;font-weight:600;text-decoration:none;{{ request()->routeIs('pricing') ? 'background:var(--accent-light);color:var(--accent);' : 'color:var(--text-primary);' }}" @click="mobileOpen = false">
                        <i data-lucide="credit-card" style="width:18px;height:18px;"></i> Pricing
                    </a>
                    <a href="{{ route('about') }}" style="display:flex;align-items:center;gap:0.75rem;padding:1rem;border-radius:12px;font-size:1rem;font-weight:600;text-decoration:none;{{ request()->routeIs('about') ? 'background:var(--accent-light);color:var(--accent);' : 'color:var(--text-primary);' }}" @click="mobileOpen = false">
                        <i data-lucide="info" style="width:18px;height:18px;"></i> About
                    </a>
                </div>
                <div style="height:1px;background:var(--border-default);margin:1rem 0;"></div>
                <div style="display:flex;flex-direction:column;gap:0.75rem;margin-top:auto;">
                    @if($auth_user)
                        <a href="{{ route('dashboard') }}" class="btn btn--primary btn--lg" style="justify-content:center;" @click="mobileOpen = false">
                            <i data-lucide="layout-dashboard" style="width:18px;height:18px;"></i> Dashboard
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="btn btn--primary btn--lg" style="justify-content:center;" @click="mobileOpen = false">
                            Get Started Free
                        </a>
                        <a href="{{ route('login') }}" class="btn btn--ghost btn--lg" style="justify-content:center;" @click="mobileOpen = false">
                            Sign In
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </template>
</nav>
<style>
    @keyframes mobileSlideIn {
        from { transform: translateX(100%); }
        to { transform: translateX(0); }
    }
</style>

