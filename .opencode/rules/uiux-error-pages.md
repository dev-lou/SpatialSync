---
description: Custom error pages (404, 403, 419, 429, 500, 503). Each uses design tokens and includes shared navigation. For Laravel: resources/views/errors/*.blade.php.
---

# UI/UX Error Pages

> Custom error pages that match your design system.
> Professional, helpful, and token-aware.

---

## OVERVIEW

This skill covers custom error pages:
- **404** - Page Not Found
- **403** - Forbidden
- **419** - Session Expired (Laravel)
- **429** - Too Many Requests
- **500** - Server Error
- **503** - Service Unavailable

All pages share navbar/footer and use design tokens.

---

## LARAVEL STRUCTURE

```
resources/views/
├── errors/
│   ├── 404.blade.php
│   ├── 403.blade.php
│   ├── 419.blade.php
│   ├── 429.blade.php
│   ├── 500.blade.php
│   ├── 503.blade.php
│   └── layout.blade.php
└── layouts/
    └── app.blade.php
```

---

## ERROR LAYOUT

```blade
{{-- resources/views/errors/layout.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') — {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    
    {{-- Error page specific styles --}}
    <style>
        .error-page {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .error-page__content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--space-8);
        }
        
        .error-page__container {
            max-width: 560px;
            text-align: center;
        }
        
        .error-page__code {
            font-size: clamp(6rem, 20vw, 10rem);
            font-weight: 800;
            line-height: 1;
            color: var(--muted);
            opacity: 0.5;
            margin-bottom: var(--space-4);
        }
        
        .error-page__title {
            font-size: var(--text-3xl);
            font-weight: 700;
            color: var(--text);
            margin-bottom: var(--space-4);
        }
        
        .error-page__message {
            font-size: var(--text-lg);
            color: var(--text-secondary);
            margin-bottom: var(--space-8);
            line-height: 1.6;
        }
        
        .error-page__actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: var(--space-4);
        }
        
        .error-page__search {
            margin-top: var(--space-8);
            max-width: 400px;
            margin-inline: auto;
        }
        
        .error-page__search-form {
            display: flex;
            gap: var(--space-2);
        }
        
        .error-page__search-input {
            flex: 1;
            padding: var(--space-3) var(--space-4);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            font-size: var(--text-base);
        }
        
        .error-page__illustration {
            max-width: 300px;
            margin: 0 auto var(--space-8);
        }
        
        .error-page__illustration svg {
            width: 100%;
            height: auto;
        }
    </style>
</head>
<body class="error-page">
    {{-- Include shared navbar --}}
    @include('partials.navbar')
    
    <main class="error-page__content">
        <div class="error-page__container">
            @yield('content')
        </div>
    </main>
    
    {{-- Include shared footer --}}
    @include('partials.footer')
    
    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
```

---

## 404 - PAGE NOT FOUND

```blade
{{-- resources/views/errors/404.blade.php --}}
@extends('errors.layout')

@section('title', 'Page Not Found')

@section('content')
    <div class="error-page__illustration" aria-hidden="true">
        {{-- SVG illustration or Lottie animation --}}
        <svg viewBox="0 0 400 300" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="200" cy="150" r="100" fill="var(--surface)" stroke="var(--border)" stroke-width="2"/>
            <text x="200" y="160" text-anchor="middle" font-size="48" fill="var(--muted)">?</text>
        </svg>
    </div>
    
    <p class="error-page__code">404</p>
    <h1 class="error-page__title">Page not found</h1>
    <p class="error-page__message">
        Sorry, we couldn't find the page you're looking for. 
        It might have been moved, deleted, or never existed.
    </p>
    
    <div class="error-page__actions">
        <a href="{{ route('home') }}" class="btn btn--primary">
            <i data-lucide="home"></i>
            Go to Homepage
        </a>
        <button onclick="history.back()" class="btn btn--ghost">
            <i data-lucide="arrow-left"></i>
            Go Back
        </button>
    </div>
    
    {{-- Search form --}}
    <div class="error-page__search">
        <p class="text-muted text-sm mb-3">Or try searching:</p>
        <form action="{{ route('search') }}" method="GET" class="error-page__search-form">
            <input 
                type="search" 
                name="q" 
                placeholder="Search..." 
                class="error-page__search-input"
                aria-label="Search"
            >
            <button type="submit" class="btn btn--secondary">
                <i data-lucide="search"></i>
            </button>
        </form>
    </div>
@endsection
```

---

## 403 - FORBIDDEN

```blade
{{-- resources/views/errors/403.blade.php --}}
@extends('errors.layout')

@section('title', 'Access Denied')

@section('content')
    <div class="error-page__illustration" aria-hidden="true">
        <svg viewBox="0 0 400 300" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="200" cy="150" r="100" fill="var(--surface)" stroke="var(--color-error)" stroke-width="2"/>
            <path d="M200 100v60M200 180v10" stroke="var(--color-error)" stroke-width="8" stroke-linecap="round"/>
        </svg>
    </div>
    
    <p class="error-page__code">403</p>
    <h1 class="error-page__title">Access denied</h1>
    <p class="error-page__message">
        {{ $exception->getMessage() ?: "You don't have permission to access this page. If you believe this is an error, please contact support." }}
    </p>
    
    <div class="error-page__actions">
        <a href="{{ route('home') }}" class="btn btn--primary">
            <i data-lucide="home"></i>
            Go to Homepage
        </a>
        @guest
            <a href="{{ route('login') }}" class="btn btn--ghost">
                <i data-lucide="log-in"></i>
                Sign In
            </a>
        @endguest
    </div>
@endsection
```

---

## 419 - SESSION EXPIRED

```blade
{{-- resources/views/errors/419.blade.php --}}
@extends('errors.layout')

@section('title', 'Session Expired')

@section('content')
    <div class="error-page__illustration" aria-hidden="true">
        <svg viewBox="0 0 400 300" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="200" cy="150" r="100" fill="var(--surface)" stroke="var(--border)" stroke-width="2"/>
            <path d="M200 100v50l30 30" stroke="var(--muted)" stroke-width="6" stroke-linecap="round"/>
        </svg>
    </div>
    
    <p class="error-page__code">419</p>
    <h1 class="error-page__title">Session expired</h1>
    <p class="error-page__message">
        Your session has expired due to inactivity. 
        Please refresh the page and try again.
    </p>
    
    <div class="error-page__actions">
        <button onclick="location.reload()" class="btn btn--primary">
            <i data-lucide="refresh-cw"></i>
            Refresh Page
        </button>
        <a href="{{ route('login') }}" class="btn btn--ghost">
            <i data-lucide="log-in"></i>
            Sign In Again
        </a>
    </div>
@endsection
```

---

## 429 - TOO MANY REQUESTS

```blade
{{-- resources/views/errors/429.blade.php --}}
@extends('errors.layout')

@section('title', 'Too Many Requests')

@section('content')
    <div class="error-page__illustration" aria-hidden="true">
        <svg viewBox="0 0 400 300" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="200" cy="150" r="100" fill="var(--surface)" stroke="var(--color-warning)" stroke-width="2"/>
            <text x="200" y="165" text-anchor="middle" font-size="60" fill="var(--color-warning)">⏳</text>
        </svg>
    </div>
    
    <p class="error-page__code">429</p>
    <h1 class="error-page__title">Too many requests</h1>
    <p class="error-page__message">
        You've made too many requests in a short period. 
        Please wait a moment before trying again.
    </p>
    
    {{-- Countdown timer --}}
    <div class="error-page__countdown" id="countdown">
        <p class="text-muted">You can try again in <span id="countdown-time">60</span> seconds</p>
        <div class="progress-bar mt-4" style="height: 4px; background: var(--border); border-radius: var(--radius-full);">
            <div id="countdown-progress" style="height: 100%; background: var(--accent); border-radius: var(--radius-full); width: 100%; transition: width 1s linear;"></div>
        </div>
    </div>
    
    <div class="error-page__actions mt-8">
        <a href="{{ route('home') }}" class="btn btn--ghost">
            <i data-lucide="home"></i>
            Go to Homepage
        </a>
    </div>
    
    <script>
        (function() {
            let seconds = {{ $exception->getHeaders()['Retry-After'] ?? 60 }};
            const total = seconds;
            const timeEl = document.getElementById('countdown-time');
            const progressEl = document.getElementById('countdown-progress');
            
            const interval = setInterval(() => {
                seconds--;
                timeEl.textContent = seconds;
                progressEl.style.width = ((seconds / total) * 100) + '%';
                
                if (seconds <= 0) {
                    clearInterval(interval);
                    location.reload();
                }
            }, 1000);
        })();
    </script>
@endsection
```

---

## 500 - SERVER ERROR

```blade
{{-- resources/views/errors/500.blade.php --}}
@extends('errors.layout')

@section('title', 'Server Error')

@section('content')
    <div class="error-page__illustration" aria-hidden="true">
        <svg viewBox="0 0 400 300" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="200" cy="150" r="100" fill="var(--surface)" stroke="var(--color-error)" stroke-width="2"/>
            <path d="M160 120l80 80M240 120l-80 80" stroke="var(--color-error)" stroke-width="8" stroke-linecap="round"/>
        </svg>
    </div>
    
    <p class="error-page__code">500</p>
    <h1 class="error-page__title">Something went wrong</h1>
    <p class="error-page__message">
        We're experiencing technical difficulties. Our team has been notified and is working on it. 
        Please try again in a few minutes.
    </p>
    
    <div class="error-page__actions">
        <button onclick="location.reload()" class="btn btn--primary">
            <i data-lucide="refresh-cw"></i>
            Try Again
        </button>
        <a href="{{ route('home') }}" class="btn btn--ghost">
            <i data-lucide="home"></i>
            Go to Homepage
        </a>
    </div>
    
    @if(config('app.support_email'))
    <p class="text-sm text-muted mt-8">
        If the problem persists, please contact 
        <a href="mailto:{{ config('app.support_email') }}" class="text-accent">support</a>.
    </p>
    @endif
@endsection
```

---

## 503 - SERVICE UNAVAILABLE

```blade
{{-- resources/views/errors/503.blade.php --}}
@extends('errors.layout')

@section('title', 'Under Maintenance')

@section('content')
    <div class="error-page__illustration" aria-hidden="true">
        <svg viewBox="0 0 400 300" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="200" cy="150" r="100" fill="var(--surface)" stroke="var(--accent)" stroke-width="2"/>
            <text x="200" y="165" text-anchor="middle" font-size="50" fill="var(--accent)">🔧</text>
        </svg>
    </div>
    
    <p class="error-page__code">503</p>
    <h1 class="error-page__title">
        {{ $exception->getMessage() ?: 'Under maintenance' }}
    </h1>
    <p class="error-page__message">
        We're currently performing scheduled maintenance to improve our service. 
        We'll be back shortly. Thank you for your patience.
    </p>
    
    {{-- Estimated time --}}
    @if(config('app.maintenance_end'))
    <div class="error-page__eta">
        <p class="text-muted">
            Estimated completion: 
            <strong>{{ \Carbon\Carbon::parse(config('app.maintenance_end'))->format('g:i A T') }}</strong>
        </p>
    </div>
    @endif
    
    <div class="error-page__actions">
        <button onclick="location.reload()" class="btn btn--primary">
            <i data-lucide="refresh-cw"></i>
            Check Again
        </button>
    </div>
    
    {{-- Status page link --}}
    @if(config('app.status_page_url'))
    <p class="text-sm text-muted mt-8">
        Check our <a href="{{ config('app.status_page_url') }}" class="text-accent" target="_blank" rel="noopener">status page</a> for updates.
    </p>
    @endif
@endsection
```

---

## REACT/NEXT.JS ERROR PAGES

### 404 Page

```tsx
// app/not-found.tsx (Next.js 13+)
import Link from 'next/link';
import { Home, ArrowLeft, Search } from 'lucide-react';

export default function NotFound() {
  return (
    <div className="error-page">
      <main className="error-page__content">
        <div className="error-page__container">
          <p className="error-page__code">404</p>
          <h1 className="error-page__title">Page not found</h1>
          <p className="error-page__message">
            Sorry, we couldn't find the page you're looking for.
          </p>
          
          <div className="error-page__actions">
            <Link href="/" className="btn btn--primary">
              <Home size={18} />
              Go to Homepage
            </Link>
            <button onClick={() => history.back()} className="btn btn--ghost">
              <ArrowLeft size={18} />
              Go Back
            </button>
          </div>
        </div>
      </main>
    </div>
  );
}
```

### Error Boundary

```tsx
// app/error.tsx (Next.js 13+)
'use client';

import { useEffect } from 'react';
import { RefreshCw, Home } from 'lucide-react';

export default function Error({
  error,
  reset,
}: {
  error: Error & { digest?: string };
  reset: () => void;
}) {
  useEffect(() => {
    // Log error to reporting service
    console.error(error);
  }, [error]);

  return (
    <div className="error-page">
      <main className="error-page__content">
        <div className="error-page__container">
          <p className="error-page__code">500</p>
          <h1 className="error-page__title">Something went wrong</h1>
          <p className="error-page__message">
            We're experiencing technical difficulties. Please try again.
          </p>
          
          <div className="error-page__actions">
            <button onClick={reset} className="btn btn--primary">
              <RefreshCw size={18} />
              Try Again
            </button>
            <a href="/" className="btn btn--ghost">
              <Home size={18} />
              Go to Homepage
            </a>
          </div>
        </div>
      </main>
    </div>
  );
}
```

---

## CHECKLIST

- [ ] All error pages use shared navbar/footer
- [ ] Token-aware styling throughout
- [ ] Helpful error messages
- [ ] Clear call-to-action buttons
- [ ] Search form on 404
- [ ] Countdown timer on 429
- [ ] Maintenance message on 503
- [ ] Contact support link on 500
- [ ] Accessible heading structure
- [ ] Works without JavaScript
- [ ] Mobile-responsive layout
