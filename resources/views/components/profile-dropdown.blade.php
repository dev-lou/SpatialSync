{{-- Premium Profile Dropdown Component --}}
<div class="profile-dropdown" x-data="{ open: false }" @click.away="open = false" @keydown.escape.window="open = false">
    {{-- Trigger Button --}}
    <button 
        type="button" 
        class="profile-dropdown__trigger" 
        @click="open = !open"
        :aria-expanded="open"
        aria-label="User menu"
    >
        <div class="profile-dropdown__avatar">
            @if(auth()->user()->profile_photo_path)
                <img src="{{ auth()->user()->profile_photo_url }}" alt="{{ auth()->user()->name }}" class="profile-dropdown__avatar-img">
            @else
                <span class="profile-dropdown__initials">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}{{ strtoupper(substr(explode(' ', auth()->user()->name)[1] ?? auth()->user()->name, 0, 1)) }}
                </span>
            @endif
        </div>
        <i data-lucide="chevron-down" class="profile-dropdown__chevron" :class="{ 'rotate-180': open }"></i>
    </button>

    {{-- Dropdown Menu --}}
    <div 
        class="profile-dropdown__menu" 
        x-show="open"
        x-transition:enter="profile-dropdown-enter"
        x-transition:enter-start="profile-dropdown-enter-start"
        x-transition:enter-end="profile-dropdown-enter-end"
        x-transition:leave="profile-dropdown-leave"
        x-transition:leave-start="profile-dropdown-leave-start"
        x-transition:leave-end="profile-dropdown-leave-end"
        x-cloak
    >
        {{-- User Info Header --}}
        <div class="profile-dropdown__header">
            <div class="profile-dropdown__name">{{ auth()->user()->name }}</div>
            <div class="profile-dropdown__email">{{ auth()->user()->email }}</div>
        </div>

        <div class="profile-dropdown__divider"></div>

        {{-- Navigation Links --}}
        <a href="{{ route('home') }}" class="profile-dropdown__item" @click="open = false">
            <i data-lucide="home" class="profile-dropdown__icon"></i>
            <span>Home</span>
        </a>

        <a href="{{ route('dashboard') }}" class="profile-dropdown__item" @click="open = false">
            <i data-lucide="layout-dashboard" class="profile-dropdown__icon"></i>
            <span>Dashboard</span>
        </a>

        <a href="{{ route('profile.show') }}" class="profile-dropdown__item" @click="open = false">
            <i data-lucide="settings" class="profile-dropdown__icon"></i>
            <span>Settings</span>
        </a>

        @if(auth()->user()->is_admin ?? false)
            <a href="{{ route('admin.dashboard') }}" class="profile-dropdown__item profile-dropdown__item--admin" @click="open = false">
                <i data-lucide="shield" class="profile-dropdown__icon"></i>
                <span>Admin Dashboard</span>
                <span class="profile-dropdown__badge">Admin</span>
            </a>
        @endif

        <div class="profile-dropdown__divider"></div>

        {{-- Logout --}}
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="profile-dropdown__item profile-dropdown__item--logout">
                <i data-lucide="log-out" class="profile-dropdown__icon"></i>
                <span>Logout</span>
            </button>
        </form>
    </div>
</div>
