<nav class="navbar" role="navigation" aria-label="Main navigation">
    <div class="container navbar__inner">
        <a href="{{ route('home') }}" class="navbar__brand">
            <span class="navbar__logo">
                <i data-lucide="layout" class="w-5 h-5"></i>
            </span>
            ConstructHub
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
                <x-profile-dropdown />
            @else
                <a href="{{ route('login') }}" class="btn btn--ghost btn--sm">
                    Sign in
                </a>
                <a href="{{ route('register') }}" class="btn btn--primary btn--sm">
                    Get Started
                </a>
            @endif

            <button class="navbar__mobile-toggle" aria-label="Open menu">
                <i data-lucide="menu" class="w-5 h-5"></i>
            </button>
        </div>
    </div>
</nav>
