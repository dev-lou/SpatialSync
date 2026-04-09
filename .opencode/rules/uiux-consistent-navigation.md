---
description: Navbar and footer must be shared components. Active link highlighting uses route matching. Consistent styling across all pages.
---

# UI/UX Consistent Navigation

> Navbar and footer as shared components.
> Active states, responsive design, token-aware styling.

---

## OVERVIEW

This skill ensures:
1. Single source of truth for navigation
2. Shared navbar component across all pages
3. Shared footer component across all pages
4. Active link highlighting with route matching
5. Consistent styling (font, spacing, colors)

---

## GOLDEN RULES

1. **Never duplicate navigation markup** - use partials/components
2. **Active state comes from route matching** - not manual classes
3. **Font sizes and spacing identical** - use design tokens
4. **Mobile menu consistent** - same items as desktop
5. **Footer on every page** - with `role="contentinfo"`

---

## LARAVEL BLADE STRUCTURE

```
resources/views/
├── layouts/
│   └── app.blade.php
├── partials/
│   ├── navbar.blade.php
│   ├── navbar-mobile.blade.php
│   └── footer.blade.php
└── pages/
    ├── home.blade.php
    ├── about.blade.php
    └── ...
```

---

## SHARED NAVBAR (Laravel)

```blade
{{-- resources/views/partials/navbar.blade.php --}}
@props(['transparent' => false])

<header 
    class="navbar {{ $transparent ? 'navbar--transparent' : '' }}" 
    role="banner"
    x-data="{ mobileMenuOpen: false }"
>
    <div class="navbar__container">
        {{-- Logo --}}
        <x-logo class="navbar__logo" />
        
        {{-- Desktop Navigation --}}
        <nav class="navbar__nav" aria-label="Main navigation">
            @php
                $navItems = [
                    ['route' => 'features', 'label' => 'Features'],
                    ['route' => 'pricing', 'label' => 'Pricing'],
                    ['route' => 'about', 'label' => 'About'],
                    ['route' => 'contact', 'label' => 'Contact'],
                ];
            @endphp
            
            @foreach($navItems as $item)
                <a 
                    href="{{ route($item['route']) }}" 
                    class="navbar__link {{ request()->routeIs($item['route'] . '*') ? 'navbar__link--active' : '' }}"
                    @if(request()->routeIs($item['route'])) aria-current="page" @endif
                >
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>
        
        {{-- Actions --}}
        <div class="navbar__actions">
            @auth
                <a href="{{ route('dashboard') }}" class="btn btn--ghost btn--sm">
                    Dashboard
                </a>
                <div class="navbar__user" x-data="{ open: false }">
                    <button 
                        @click="open = !open" 
                        class="navbar__avatar"
                        aria-haspopup="true"
                        :aria-expanded="open"
                    >
                        <img 
                            src="{{ Auth::user()->avatar_url ?? '/placeholder-avatar.jpg' }}" 
                            alt="{{ Auth::user()->name }}"
                            width="32"
                            height="32"
                        >
                    </button>
                    <div 
                        x-show="open" 
                        @click.away="open = false"
                        class="navbar__dropdown"
                    >
                        <a href="{{ route('profile') }}">Profile</a>
                        <a href="{{ route('settings') }}">Settings</a>
                        <hr>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit">Sign Out</button>
                        </form>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}" class="btn btn--ghost btn--sm">
                    Sign In
                </a>
                <a href="{{ route('register') }}" class="btn btn--primary btn--sm">
                    Get Started
                </a>
            @endauth
        </div>
        
        {{-- Mobile Menu Toggle --}}
        <button 
            class="navbar__toggle"
            @click="mobileMenuOpen = !mobileMenuOpen"
            :aria-expanded="mobileMenuOpen"
            aria-label="Toggle navigation menu"
        >
            <i x-show="!mobileMenuOpen" data-lucide="menu"></i>
            <i x-show="mobileMenuOpen" data-lucide="x"></i>
        </button>
    </div>
    
    {{-- Mobile Menu --}}
    <div 
        class="navbar__mobile-menu"
        x-show="mobileMenuOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
    >
        @include('partials.navbar-mobile', ['navItems' => $navItems])
    </div>
</header>
```

### Mobile Menu Partial

```blade
{{-- resources/views/partials/navbar-mobile.blade.php --}}
<nav class="mobile-nav" aria-label="Mobile navigation">
    @foreach($navItems as $item)
        <a 
            href="{{ route($item['route']) }}" 
            class="mobile-nav__link {{ request()->routeIs($item['route'] . '*') ? 'mobile-nav__link--active' : '' }}"
            @if(request()->routeIs($item['route'])) aria-current="page" @endif
        >
            {{ $item['label'] }}
        </a>
    @endforeach
    
    <hr class="mobile-nav__divider">
    
    @auth
        <a href="{{ route('dashboard') }}" class="mobile-nav__link">Dashboard</a>
        <a href="{{ route('profile') }}" class="mobile-nav__link">Profile</a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="mobile-nav__link mobile-nav__link--danger">
                Sign Out
            </button>
        </form>
    @else
        <a href="{{ route('login') }}" class="mobile-nav__link">Sign In</a>
        <a href="{{ route('register') }}" class="btn btn--primary btn--block mt-4">
            Get Started
        </a>
    @endauth
</nav>
```

---

## SHARED FOOTER (Laravel)

```blade
{{-- resources/views/partials/footer.blade.php --}}
<footer class="footer" role="contentinfo">
    <div class="footer__container">
        {{-- Brand Column --}}
        <div class="footer__brand">
            <x-logo :link="false" />
            <p class="footer__tagline">
                Building better experiences since 2020.
            </p>
            
            {{-- Social Links --}}
            <div class="footer__social">
                @if(config('app.social.twitter'))
                    <a href="{{ config('app.social.twitter') }}" target="_blank" rel="noopener noreferrer" aria-label="Twitter">
                        <i data-lucide="twitter"></i>
                    </a>
                @endif
                @if(config('app.social.github'))
                    <a href="{{ config('app.social.github') }}" target="_blank" rel="noopener noreferrer" aria-label="GitHub">
                        <i data-lucide="github"></i>
                    </a>
                @endif
                @if(config('app.social.linkedin'))
                    <a href="{{ config('app.social.linkedin') }}" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn">
                        <i data-lucide="linkedin"></i>
                    </a>
                @endif
            </div>
        </div>
        
        {{-- Navigation Columns --}}
        <nav class="footer__nav" aria-label="Footer navigation">
            <div class="footer__column">
                <h3 class="footer__heading">Product</h3>
                <ul class="footer__links">
                    <li><a href="{{ route('features') }}">Features</a></li>
                    <li><a href="{{ route('pricing') }}">Pricing</a></li>
                    <li><a href="{{ route('changelog') }}">Changelog</a></li>
                    <li><a href="{{ route('docs') }}">Documentation</a></li>
                </ul>
            </div>
            
            <div class="footer__column">
                <h3 class="footer__heading">Company</h3>
                <ul class="footer__links">
                    <li><a href="{{ route('about') }}">About</a></li>
                    <li><a href="{{ route('careers') }}">Careers</a></li>
                    <li><a href="{{ route('blog') }}">Blog</a></li>
                    <li><a href="{{ route('contact') }}">Contact</a></li>
                </ul>
            </div>
            
            <div class="footer__column">
                <h3 class="footer__heading">Legal</h3>
                <ul class="footer__links">
                    <li><a href="{{ route('privacy') }}">Privacy Policy</a></li>
                    <li><a href="{{ route('terms') }}">Terms of Service</a></li>
                    <li><a href="{{ route('cookies') }}">Cookie Policy</a></li>
                    <li><a href="{{ route('accessibility') }}">Accessibility</a></li>
                </ul>
            </div>
        </nav>
        
        {{-- Bottom Bar --}}
        <div class="footer__bottom">
            <p class="footer__copyright">
                &copy; {{ date('Y') }} {{ config('app.legal.entity', config('app.name')) }}. 
                All rights reserved.
            </p>
            
            {{-- Back to Top --}}
            <a href="#top" class="footer__back-to-top" aria-label="Back to top">
                <i data-lucide="arrow-up"></i>
                <span>Back to top</span>
            </a>
        </div>
    </div>
</footer>
```

---

## LAYOUT USAGE

```blade
{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light" id="top">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    {{-- Skip Link --}}
    <a href="#main-content" class="skip-link">Skip to main content</a>
    
    {{-- Shared Navbar --}}
    @include('partials.navbar', ['transparent' => $transparentNav ?? false])
    
    {{-- Main Content --}}
    <main id="main-content">
        @yield('content')
    </main>
    
    {{-- Shared Footer --}}
    @include('partials.footer')
</body>
</html>
```

---

## REACT COMPONENTS

### Navbar Component

```tsx
// components/Navbar.tsx
'use client';

import Link from 'next/link';
import { usePathname } from 'next/navigation';
import { useState } from 'react';
import { Menu, X } from 'lucide-react';
import { Logo } from './Logo';

const navItems = [
  { href: '/features', label: 'Features' },
  { href: '/pricing', label: 'Pricing' },
  { href: '/about', label: 'About' },
  { href: '/contact', label: 'Contact' },
];

export function Navbar() {
  const pathname = usePathname();
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  
  const isActive = (href: string) => {
    return pathname === href || pathname.startsWith(`${href}/`);
  };
  
  return (
    <header className="navbar" role="banner">
      <div className="navbar__container">
        <Logo className="navbar__logo" />
        
        <nav className="navbar__nav" aria-label="Main navigation">
          {navItems.map((item) => (
            <Link
              key={item.href}
              href={item.href}
              className={`navbar__link ${isActive(item.href) ? 'navbar__link--active' : ''}`}
              aria-current={isActive(item.href) ? 'page' : undefined}
            >
              {item.label}
            </Link>
          ))}
        </nav>
        
        <div className="navbar__actions">
          <Link href="/login" className="btn btn--ghost btn--sm">
            Sign In
          </Link>
          <Link href="/register" className="btn btn--primary btn--sm">
            Get Started
          </Link>
        </div>
        
        <button
          className="navbar__toggle"
          onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
          aria-expanded={mobileMenuOpen}
          aria-label="Toggle navigation menu"
        >
          {mobileMenuOpen ? <X /> : <Menu />}
        </button>
      </div>
      
      {mobileMenuOpen && (
        <div className="navbar__mobile-menu">
          <nav className="mobile-nav" aria-label="Mobile navigation">
            {navItems.map((item) => (
              <Link
                key={item.href}
                href={item.href}
                className={`mobile-nav__link ${isActive(item.href) ? 'mobile-nav__link--active' : ''}`}
                onClick={() => setMobileMenuOpen(false)}
              >
                {item.label}
              </Link>
            ))}
          </nav>
        </div>
      )}
    </header>
  );
}
```

### Footer Component

```tsx
// components/Footer.tsx
import Link from 'next/link';
import { Logo } from './Logo';
import { Twitter, Github, Linkedin, ArrowUp } from 'lucide-react';

const footerLinks = {
  product: [
    { href: '/features', label: 'Features' },
    { href: '/pricing', label: 'Pricing' },
    { href: '/changelog', label: 'Changelog' },
    { href: '/docs', label: 'Documentation' },
  ],
  company: [
    { href: '/about', label: 'About' },
    { href: '/careers', label: 'Careers' },
    { href: '/blog', label: 'Blog' },
    { href: '/contact', label: 'Contact' },
  ],
  legal: [
    { href: '/privacy', label: 'Privacy Policy' },
    { href: '/terms', label: 'Terms of Service' },
    { href: '/cookies', label: 'Cookie Policy' },
    { href: '/accessibility', label: 'Accessibility' },
  ],
};

export function Footer() {
  return (
    <footer className="footer" role="contentinfo">
      <div className="footer__container">
        <div className="footer__brand">
          <Logo link={false} />
          <p className="footer__tagline">
            Building better experiences since 2020.
          </p>
        </div>
        
        <nav className="footer__nav" aria-label="Footer navigation">
          <div className="footer__column">
            <h3 className="footer__heading">Product</h3>
            <ul className="footer__links">
              {footerLinks.product.map((link) => (
                <li key={link.href}>
                  <Link href={link.href}>{link.label}</Link>
                </li>
              ))}
            </ul>
          </div>
          
          <div className="footer__column">
            <h3 className="footer__heading">Company</h3>
            <ul className="footer__links">
              {footerLinks.company.map((link) => (
                <li key={link.href}>
                  <Link href={link.href}>{link.label}</Link>
                </li>
              ))}
            </ul>
          </div>
          
          <div className="footer__column">
            <h3 className="footer__heading">Legal</h3>
            <ul className="footer__links">
              {footerLinks.legal.map((link) => (
                <li key={link.href}>
                  <Link href={link.href}>{link.label}</Link>
                </li>
              ))}
            </ul>
          </div>
        </nav>
        
        <div className="footer__bottom">
          <p className="footer__copyright">
            &copy; {new Date().getFullYear()} Company Name. All rights reserved.
          </p>
          
          <a href="#top" className="footer__back-to-top" aria-label="Back to top">
            <ArrowUp size={16} />
            <span>Back to top</span>
          </a>
        </div>
      </div>
    </footer>
  );
}
```

---

## CSS STYLES

```css
/* Navbar */
.navbar {
  position: sticky;
  top: 0;
  z-index: 100;
  background: var(--bg);
  border-bottom: 1px solid var(--border);
}

.navbar--transparent {
  background: transparent;
  border-bottom: none;
  position: absolute;
  width: 100%;
}

.navbar__container {
  display: flex;
  align-items: center;
  justify-content: space-between;
  max-width: var(--container-lg);
  margin: 0 auto;
  padding: var(--space-4) var(--space-6);
}

.navbar__nav {
  display: none;
  gap: var(--space-1);
}

@media (min-width: 768px) {
  .navbar__nav {
    display: flex;
  }
}

.navbar__link {
  padding: var(--space-2) var(--space-3);
  color: var(--text-secondary);
  text-decoration: none;
  font-size: var(--text-sm);
  font-weight: 500;
  border-radius: var(--radius-md);
  transition: color var(--dur-micro), background var(--dur-micro);
}

.navbar__link:hover {
  color: var(--text);
  background: var(--bg-hover);
}

.navbar__link--active {
  color: var(--accent);
  background: var(--accent-soft);
}

.navbar__actions {
  display: none;
  gap: var(--space-3);
}

@media (min-width: 768px) {
  .navbar__actions {
    display: flex;
  }
}

.navbar__toggle {
  display: flex;
  padding: var(--space-2);
  background: none;
  border: none;
  color: var(--text);
  cursor: pointer;
}

@media (min-width: 768px) {
  .navbar__toggle {
    display: none;
  }
}

/* Mobile Menu */
.navbar__mobile-menu {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: var(--bg);
  border-bottom: 1px solid var(--border);
  padding: var(--space-4);
}

.mobile-nav {
  display: flex;
  flex-direction: column;
}

.mobile-nav__link {
  padding: var(--space-3) var(--space-4);
  color: var(--text);
  text-decoration: none;
  font-size: var(--text-base);
  border-radius: var(--radius-md);
}

.mobile-nav__link--active {
  color: var(--accent);
  background: var(--accent-soft);
}

/* Footer */
.footer {
  background: var(--surface);
  border-top: 1px solid var(--border);
  padding: var(--space-16) 0 var(--space-8);
}

.footer__container {
  max-width: var(--container-lg);
  margin: 0 auto;
  padding: 0 var(--space-6);
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-12);
}

@media (min-width: 768px) {
  .footer__container {
    grid-template-columns: 2fr 3fr;
  }
}

.footer__brand {
  max-width: 280px;
}

.footer__tagline {
  color: var(--muted);
  font-size: var(--text-sm);
  margin-top: var(--space-4);
}

.footer__nav {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
  gap: var(--space-8);
}

.footer__heading {
  font-size: var(--text-sm);
  font-weight: 600;
  color: var(--text);
  margin-bottom: var(--space-4);
}

.footer__links {
  list-style: none;
  padding: 0;
  margin: 0;
}

.footer__links li {
  margin-bottom: var(--space-3);
}

.footer__links a {
  color: var(--muted);
  text-decoration: none;
  font-size: var(--text-sm);
  transition: color var(--dur-micro);
}

.footer__links a:hover {
  color: var(--text);
}

.footer__bottom {
  grid-column: 1 / -1;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: var(--space-8);
  border-top: 1px solid var(--border);
}

.footer__copyright {
  color: var(--muted);
  font-size: var(--text-sm);
}

.footer__back-to-top {
  display: flex;
  align-items: center;
  gap: var(--space-2);
  color: var(--muted);
  text-decoration: none;
  font-size: var(--text-sm);
}

.footer__back-to-top:hover {
  color: var(--text);
}
```

---

## CHECKLIST

- [ ] Navbar is a shared partial/component
- [ ] Footer is a shared partial/component
- [ ] Active states use route matching
- [ ] `aria-current="page"` on active links
- [ ] Mobile menu has same items as desktop
- [ ] Skip link present for accessibility
- [ ] Footer has `role="contentinfo"`
- [ ] Back-to-top link in footer
- [ ] Social links conditional (hide if not configured)
- [ ] All links use named routes
- [ ] Design tokens used throughout
- [ ] Works without JavaScript
