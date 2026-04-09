---
description: App configuration patterns using environment variables. Covers app name, logo, dynamic titles, copyright, and named routes.
---

# UI/UX Site Configuration

> Configure app name, logo, titles, and navigation from environment.
> No hardcoded strings. Dynamic and maintainable.

---

## OVERVIEW

This skill covers:
1. App name from `.env`
2. Logo configuration (URL or text fallback)
3. Dynamic page titles
4. Auto-updating copyright year
5. Named routes (no hardcoded paths)

---

## ENVIRONMENT VARIABLES

### .env Configuration

```env
# Application
APP_NAME="MyBrand"
APP_URL=https://mybrand.com
APP_LOGO_URL=/images/logo.svg
APP_LOGO_DARK_URL=/images/logo-dark.svg

# Company Info
APP_COMPANY_NAME="MyBrand Inc."
APP_SUPPORT_EMAIL=support@mybrand.com
APP_PHONE="+1 (555) 123-4567"

# Social Links
APP_TWITTER_URL=https://twitter.com/mybrand
APP_LINKEDIN_URL=https://linkedin.com/company/mybrand
APP_GITHUB_URL=https://github.com/mybrand

# Legal
APP_LEGAL_ENTITY="MyBrand Inc."
APP_LEGAL_ADDRESS="123 Main St, City, Country"
```

### .env.example (Template)

```env
# Application
APP_NAME="App Name"
APP_URL=http://localhost
APP_LOGO_URL=
APP_LOGO_DARK_URL=

# Company Info
APP_COMPANY_NAME="Company Name"
APP_SUPPORT_EMAIL=support@example.com
APP_PHONE=

# Social Links (leave empty to hide)
APP_TWITTER_URL=
APP_LINKEDIN_URL=
APP_GITHUB_URL=

# Legal
APP_LEGAL_ENTITY="Company Name LLC"
APP_LEGAL_ADDRESS=
```

---

## LARAVEL IMPLEMENTATION

### Config File

```php
// config/app.php (add to existing)
return [
    'name' => env('APP_NAME', 'Laravel'),
    'url' => env('APP_URL', 'http://localhost'),
    
    // Add custom keys
    'logo_url' => env('APP_LOGO_URL'),
    'logo_dark_url' => env('APP_LOGO_DARK_URL'),
    'company_name' => env('APP_COMPANY_NAME', env('APP_NAME', 'Company')),
    'support_email' => env('APP_SUPPORT_EMAIL'),
    'phone' => env('APP_PHONE'),
    
    // Social
    'social' => [
        'twitter' => env('APP_TWITTER_URL'),
        'linkedin' => env('APP_LINKEDIN_URL'),
        'github' => env('APP_GITHUB_URL'),
    ],
    
    // Legal
    'legal' => [
        'entity' => env('APP_LEGAL_ENTITY'),
        'address' => env('APP_LEGAL_ADDRESS'),
    ],
];
```

### Logo Component (Blade)

```blade
{{-- resources/views/components/logo.blade.php --}}
@props(['class' => '', 'link' => true])

@php
    $logoUrl = config('app.logo_url');
    $logoDarkUrl = config('app.logo_dark_url');
    $appName = config('app.name');
@endphp

@if($link)
<a href="{{ route('home') }}" class="logo {{ $class }}" aria-label="{{ $appName }} - Go to homepage">
@else
<span class="logo {{ $class }}">
@endif

    @if($logoUrl)
        {{-- Image logo --}}
        <picture>
            @if($logoDarkUrl)
                <source srcset="{{ $logoDarkUrl }}" media="(prefers-color-scheme: dark)">
            @endif
            <img 
                src="{{ $logoUrl }}" 
                alt="{{ $appName }}" 
                class="logo__image"
                width="120" 
                height="32"
                loading="eager"
            >
        </picture>
    @else
        {{-- Text fallback --}}
        <span class="logo__text">{{ $appName }}</span>
    @endif

@if($link)
</a>
@else
</span>
@endif
```

### Logo Styles

```css
.logo {
  display: inline-flex;
  align-items: center;
  text-decoration: none;
}

.logo__image {
  height: 32px;
  width: auto;
  object-fit: contain;
}

.logo__text {
  font-size: var(--text-xl);
  font-weight: 700;
  color: var(--text);
  letter-spacing: -0.02em;
}

/* Dark mode logo swap (if not using picture element) */
[data-theme="dark"] .logo__image--light {
  display: none;
}

[data-theme="dark"] .logo__image--dark {
  display: block;
}

[data-theme="light"] .logo__image--dark {
  display: none;
}
```

---

## DYNAMIC PAGE TITLES

### Blade Layout

```blade
{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <title>@yield('title', config('app.name'))</title>
    
    {{-- Or with separator --}}
    <title>
        @hasSection('title')
            @yield('title') — {{ config('app.name') }}
        @else
            {{ config('app.name') }}
        @endif
    </title>
    
    {{-- Meta tags --}}
    <meta name="description" content="@yield('description', 'Default description')">
    <meta property="og:title" content="@yield('title', config('app.name'))">
    <meta property="og:site_name" content="{{ config('app.name') }}">
</head>
```

### Page Usage

```blade
{{-- resources/views/dashboard.blade.php --}}
@extends('layouts.app')

@section('title', 'Dashboard')
@section('description', 'Your personal dashboard')

@section('content')
    <h1>Dashboard</h1>
@endsection
```

---

## COPYRIGHT FOOTER

### Blade Component

```blade
{{-- resources/views/components/copyright.blade.php --}}
<p class="copyright">
    &copy; {{ date('Y') }} {{ config('app.legal.entity', config('app.name')) }}. 
    All rights reserved.
</p>
```

### Multi-Year Copyright

```blade
@php
    $startYear = 2020;
    $currentYear = date('Y');
    $yearDisplay = $startYear == $currentYear 
        ? $currentYear 
        : "{$startYear}–{$currentYear}";
@endphp

<p class="copyright">
    &copy; {{ $yearDisplay }} {{ config('app.legal.entity') }}
</p>
```

### Footer Component

```blade
{{-- resources/views/components/footer.blade.php --}}
<footer class="footer" role="contentinfo">
    <div class="footer__container">
        <div class="footer__brand">
            <x-logo :link="true" />
            <p class="footer__tagline">Making things better since 2020.</p>
        </div>
        
        <nav class="footer__nav" aria-label="Footer navigation">
            <div class="footer__column">
                <h3 class="footer__heading">Product</h3>
                <ul class="footer__links">
                    <li><a href="{{ route('features') }}">Features</a></li>
                    <li><a href="{{ route('pricing') }}">Pricing</a></li>
                    <li><a href="{{ route('changelog') }}">Changelog</a></li>
                </ul>
            </div>
            
            <div class="footer__column">
                <h3 class="footer__heading">Company</h3>
                <ul class="footer__links">
                    <li><a href="{{ route('about') }}">About</a></li>
                    <li><a href="{{ route('careers') }}">Careers</a></li>
                    <li><a href="{{ route('contact') }}">Contact</a></li>
                </ul>
            </div>
            
            <div class="footer__column">
                <h3 class="footer__heading">Legal</h3>
                <ul class="footer__links">
                    <li><a href="{{ route('privacy') }}">Privacy Policy</a></li>
                    <li><a href="{{ route('terms') }}">Terms of Service</a></li>
                    <li><a href="{{ route('cookies') }}">Cookie Policy</a></li>
                </ul>
            </div>
        </nav>
        
        <div class="footer__bottom">
            <x-copyright />
            
            @if(config('app.social.twitter') || config('app.social.linkedin') || config('app.social.github'))
            <div class="footer__social">
                @if(config('app.social.twitter'))
                <a href="{{ config('app.social.twitter') }}" target="_blank" rel="noopener noreferrer" aria-label="Twitter">
                    <i data-lucide="twitter"></i>
                </a>
                @endif
                
                @if(config('app.social.linkedin'))
                <a href="{{ config('app.social.linkedin') }}" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn">
                    <i data-lucide="linkedin"></i>
                </a>
                @endif
                
                @if(config('app.social.github'))
                <a href="{{ config('app.social.github') }}" target="_blank" rel="noopener noreferrer" aria-label="GitHub">
                    <i data-lucide="github"></i>
                </a>
                @endif
            </div>
            @endif
        </div>
    </div>
</footer>
```

---

## NAMED ROUTES

### Routes Definition

```php
// routes/web.php
use App\Http\Controllers\PageController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;

// Static pages
Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/features', [PageController::class, 'features'])->name('features');
Route::get('/pricing', [PageController::class, 'pricing'])->name('pricing');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');

// Legal pages
Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy');
Route::get('/terms', [PageController::class, 'terms'])->name('terms');
Route::get('/cookies', [PageController::class, 'cookies'])->name('cookies');
Route::get('/accessibility', [PageController::class, 'accessibility'])->name('accessibility');

// Auth
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Dashboard
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [DashboardController::class, 'profile'])->name('profile');
    Route::get('/settings', [DashboardController::class, 'settings'])->name('settings');
});
```

### Using Named Routes

```blade
{{-- ✅ Correct: Use named routes --}}
<a href="{{ route('home') }}">Home</a>
<a href="{{ route('dashboard') }}">Dashboard</a>
<a href="{{ route('profile') }}">{{ Auth::user()->name }}</a>

{{-- ❌ Wrong: Hardcoded paths --}}
<a href="/home">Home</a>
<a href="/dashboard">Dashboard</a>
```

### Active Link Detection

```blade
{{-- Navigation item with active state --}}
<a 
    href="{{ route('dashboard') }}" 
    class="nav-link {{ request()->routeIs('dashboard') ? 'nav-link--active' : '' }}"
    @if(request()->routeIs('dashboard')) aria-current="page" @endif
>
    Dashboard
</a>

{{-- Or with wildcard --}}
<a 
    href="{{ route('settings') }}" 
    class="nav-link {{ request()->routeIs('settings*') ? 'nav-link--active' : '' }}"
>
    Settings
</a>
```

### Navigation Component

```blade
{{-- resources/views/components/navbar.blade.php --}}
@props(['transparent' => false])

<header class="navbar {{ $transparent ? 'navbar--transparent' : '' }}" role="banner">
    <div class="navbar__container">
        <x-logo />
        
        <nav class="navbar__nav" aria-label="Main navigation">
            <a 
                href="{{ route('features') }}" 
                class="navbar__link {{ request()->routeIs('features') ? 'navbar__link--active' : '' }}"
            >
                Features
            </a>
            <a 
                href="{{ route('pricing') }}" 
                class="navbar__link {{ request()->routeIs('pricing') ? 'navbar__link--active' : '' }}"
            >
                Pricing
            </a>
            <a 
                href="{{ route('about') }}" 
                class="navbar__link {{ request()->routeIs('about') ? 'navbar__link--active' : '' }}"
            >
                About
            </a>
        </nav>
        
        <div class="navbar__actions">
            @auth
                <a href="{{ route('dashboard') }}" class="btn btn--ghost">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="btn btn--ghost">Login</a>
                <a href="{{ route('register') }}" class="btn btn--primary">Get Started</a>
            @endauth
        </div>
        
        <button class="navbar__toggle" aria-label="Toggle menu" aria-expanded="false">
            <i data-lucide="menu"></i>
        </button>
    </div>
</header>
```

---

## REACT/NEXT.JS IMPLEMENTATION

### Environment Variables

```env
# .env.local
NEXT_PUBLIC_APP_NAME="MyBrand"
NEXT_PUBLIC_APP_URL=https://mybrand.com
NEXT_PUBLIC_LOGO_URL=/images/logo.svg
NEXT_PUBLIC_LOGO_DARK_URL=/images/logo-dark.svg
```

### Config Hook

```typescript
// hooks/useConfig.ts
export function useConfig() {
  return {
    appName: process.env.NEXT_PUBLIC_APP_NAME || 'App',
    appUrl: process.env.NEXT_PUBLIC_APP_URL || 'http://localhost:3000',
    logoUrl: process.env.NEXT_PUBLIC_LOGO_URL,
    logoDarkUrl: process.env.NEXT_PUBLIC_LOGO_DARK_URL,
  };
}
```

### Logo Component

```tsx
// components/Logo.tsx
import Link from 'next/link';
import { useConfig } from '@/hooks/useConfig';

interface LogoProps {
  className?: string;
  link?: boolean;
}

export function Logo({ className = '', link = true }: LogoProps) {
  const { appName, logoUrl, logoDarkUrl } = useConfig();
  
  const content = logoUrl ? (
    <picture>
      {logoDarkUrl && (
        <source srcSet={logoDarkUrl} media="(prefers-color-scheme: dark)" />
      )}
      <img 
        src={logoUrl} 
        alt={appName} 
        width={120} 
        height={32}
        className="logo__image"
      />
    </picture>
  ) : (
    <span className="logo__text">{appName}</span>
  );
  
  if (link) {
    return (
      <Link href="/" className={`logo ${className}`} aria-label={`${appName} - Go to homepage`}>
        {content}
      </Link>
    );
  }
  
  return <span className={`logo ${className}`}>{content}</span>;
}
```

### Dynamic Title

```tsx
// app/layout.tsx (Next.js 13+)
import { Metadata } from 'next';

export const metadata: Metadata = {
  title: {
    template: `%s — ${process.env.NEXT_PUBLIC_APP_NAME}`,
    default: process.env.NEXT_PUBLIC_APP_NAME || 'App',
  },
};

// In page:
export const metadata: Metadata = {
  title: 'Dashboard', // Becomes "Dashboard — MyBrand"
};
```

---

## CHECKLIST

- [ ] App name in `.env`, not hardcoded
- [ ] Logo URL in `.env`, with text fallback
- [ ] Dark mode logo variant configured
- [ ] Dynamic page titles with app name
- [ ] Copyright year auto-updates
- [ ] All navigation uses named routes
- [ ] Active state on current route
- [ ] Social links conditional (hide if empty)
- [ ] Legal entity name from config
- [ ] `.env.example` documented
