{{-- Logo Cloud Component - Trusted by brands --}}
@props(['title' => 'Trusted by teams at'])

<section class="logo-cloud reveal">
    <p class="logo-cloud__title">{{ $title }}</p>
    <div class="logo-cloud__grid">
        {{-- Placeholder logos - abstract shapes representing brands --}}
        <div class="logo-cloud__item">
            <svg viewBox="0 0 120 40" fill="currentColor" class="logo-cloud__logo">
                <rect x="10" y="12" width="16" height="16" rx="3"/>
                <rect x="32" y="8" width="78" height="6" rx="2"/>
                <rect x="32" y="18" width="50" height="6" rx="2"/>
                <rect x="32" y="28" width="65" height="4" rx="2"/>
            </svg>
        </div>
        <div class="logo-cloud__item">
            <svg viewBox="0 0 120 40" fill="currentColor" class="logo-cloud__logo">
                <circle cx="20" cy="20" r="12"/>
                <rect x="40" y="14" width="70" height="12" rx="2"/>
            </svg>
        </div>
        <div class="logo-cloud__item">
            <svg viewBox="0 0 120 40" fill="currentColor" class="logo-cloud__logo">
                <polygon points="20,5 35,35 5,35"/>
                <rect x="45" y="10" width="65" height="8" rx="2"/>
                <rect x="45" y="22" width="45" height="8" rx="2"/>
            </svg>
        </div>
        <div class="logo-cloud__item">
            <svg viewBox="0 0 120 40" fill="currentColor" class="logo-cloud__logo">
                <rect x="5" y="5" width="30" height="30" rx="6"/>
                <rect x="45" y="12" width="70" height="6" rx="2"/>
                <rect x="45" y="22" width="55" height="6" rx="2"/>
            </svg>
        </div>
        <div class="logo-cloud__item">
            <svg viewBox="0 0 120 40" fill="currentColor" class="logo-cloud__logo">
                <path d="M20,5 L35,20 L20,35 L5,20 Z"/>
                <rect x="45" y="14" width="65" height="12" rx="2"/>
            </svg>
        </div>
        <div class="logo-cloud__item">
            <svg viewBox="0 0 120 40" fill="currentColor" class="logo-cloud__logo">
                <ellipse cx="20" cy="20" rx="15" ry="12"/>
                <rect x="42" y="10" width="68" height="8" rx="2"/>
                <rect x="42" y="22" width="50" height="8" rx="2"/>
            </svg>
        </div>
    </div>
</section>
