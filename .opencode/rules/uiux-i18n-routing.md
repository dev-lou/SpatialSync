---
description: URL structure for multiple languages with Next.js, Laravel, and Vue Router. Includes language switcher without full page reload.
---

# Internationalization Routing Skill

> URL-based language routing with seamless switching.

---

## URL PATTERNS

**Recommended**: Language prefix in URL path.

```
https://example.com/en/about      → English
https://example.com/fr/about      → French
https://example.com/de/about      → German
https://example.com/about         → Default language (redirects to /en/about)
```

---

## NEXT.JS APP ROUTER

### Folder Structure

```
app/
├── [lang]/
│   ├── layout.tsx
│   ├── page.tsx
│   ├── about/
│   │   └── page.tsx
│   └── contact/
│       └── page.tsx
├── dictionaries/
│   ├── en.json
│   ├── fr.json
│   └── de.json
└── middleware.ts
```

### Middleware for Language Detection

```typescript
// middleware.ts
import { NextRequest, NextResponse } from 'next/server';

const locales = ['en', 'fr', 'de', 'es'];
const defaultLocale = 'en';

function getLocale(request: NextRequest): string {
  // Check cookie first
  const cookieLocale = request.cookies.get('NEXT_LOCALE')?.value;
  if (cookieLocale && locales.includes(cookieLocale)) {
    return cookieLocale;
  }

  // Check Accept-Language header
  const acceptLanguage = request.headers.get('Accept-Language');
  if (acceptLanguage) {
    const preferred = acceptLanguage
      .split(',')
      .map(lang => lang.split(';')[0].trim().substring(0, 2))
      .find(lang => locales.includes(lang));
    
    if (preferred) return preferred;
  }

  return defaultLocale;
}

export function middleware(request: NextRequest) {
  const { pathname } = request.nextUrl;

  // Check if pathname already has locale
  const pathnameHasLocale = locales.some(
    locale => pathname.startsWith(`/${locale}/`) || pathname === `/${locale}`
  );

  if (pathnameHasLocale) return;

  // Skip static files and API routes
  if (
    pathname.startsWith('/_next') ||
    pathname.startsWith('/api') ||
    pathname.includes('.')
  ) {
    return;
  }

  // Redirect to locale-prefixed path
  const locale = getLocale(request);
  const newUrl = new URL(`/${locale}${pathname}`, request.url);
  
  return NextResponse.redirect(newUrl);
}

export const config = {
  matcher: ['/((?!_next|api|.*\\..*).*)'],
};
```

### Dictionary Loader

```typescript
// app/dictionaries/index.ts
import 'server-only';

const dictionaries = {
  en: () => import('./en.json').then(m => m.default),
  fr: () => import('./fr.json').then(m => m.default),
  de: () => import('./de.json').then(m => m.default),
};

export async function getDictionary(locale: string) {
  const loader = dictionaries[locale as keyof typeof dictionaries];
  if (!loader) {
    return dictionaries.en();
  }
  return loader();
}
```

### Layout with Language

```tsx
// app/[lang]/layout.tsx
import { getDictionary } from '../dictionaries';
import { LanguageSwitcher } from '@/components/LanguageSwitcher';

export async function generateStaticParams() {
  return [{ lang: 'en' }, { lang: 'fr' }, { lang: 'de' }];
}

export default async function Layout({
  children,
  params,
}: {
  children: React.ReactNode;
  params: { lang: string };
}) {
  const dict = await getDictionary(params.lang);

  return (
    <html lang={params.lang}>
      <body>
        <header>
          <LanguageSwitcher currentLang={params.lang} />
        </header>
        {children}
      </body>
    </html>
  );
}
```

### Language Switcher (Client Component)

```tsx
// components/LanguageSwitcher.tsx
'use client';

import { usePathname, useRouter } from 'next/navigation';
import { useTransition } from 'react';

const languages = [
  { code: 'en', name: 'English', flag: '🇬🇧' },
  { code: 'fr', name: 'Français', flag: '🇫🇷' },
  { code: 'de', name: 'Deutsch', flag: '🇩🇪' },
];

export function LanguageSwitcher({ currentLang }: { currentLang: string }) {
  const pathname = usePathname();
  const router = useRouter();
  const [isPending, startTransition] = useTransition();

  const switchLanguage = (newLang: string) => {
    // Replace current locale in path
    const newPath = pathname.replace(`/${currentLang}`, `/${newLang}`);
    
    // Save preference
    document.cookie = `NEXT_LOCALE=${newLang}; path=/; max-age=31536000`;
    
    startTransition(() => {
      router.push(newPath);
    });
  };

  return (
    <div className="lang-switcher" role="navigation" aria-label="Language selection">
      {languages.map(lang => (
        <button
          key={lang.code}
          onClick={() => switchLanguage(lang.code)}
          className={`lang-switcher__btn ${currentLang === lang.code ? 'active' : ''}`}
          aria-current={currentLang === lang.code ? 'true' : undefined}
          disabled={isPending}
        >
          <span aria-hidden="true">{lang.flag}</span>
          <span className="lang-switcher__label">{lang.name}</span>
        </button>
      ))}
    </div>
  );
}
```

---

## LARAVEL

### Route Setup

```php
// routes/web.php
use Illuminate\Support\Facades\Route;

$locales = ['en', 'fr', 'de', 'es'];

// Redirect root to default locale
Route::get('/', function () {
    $locale = session('locale', config('app.locale'));
    return redirect("/{$locale}");
});

// Locale-prefixed routes
Route::prefix('{locale}')
    ->where(['locale' => implode('|', $locales)])
    ->middleware('set-locale')
    ->group(function () {
        Route::get('/', [HomeController::class, 'index'])->name('home');
        Route::get('/about', [PageController::class, 'about'])->name('about');
        Route::get('/contact', [PageController::class, 'contact'])->name('contact');
    });
```

### Locale Middleware

```php
// app/Http/Middleware/SetLocale.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    protected $locales = ['en', 'fr', 'de', 'es'];

    public function handle(Request $request, Closure $next)
    {
        $locale = $request->route('locale');

        if (!in_array($locale, $this->locales)) {
            $locale = config('app.locale');
        }

        app()->setLocale($locale);
        session(['locale' => $locale]);

        return $next($response = $next($request));
    }
}

// Register in app/Http/Kernel.php
protected $middlewareAliases = [
    'set-locale' => \App\Http\Middleware\SetLocale::class,
];
```

### Language Switcher Component

```blade
{{-- resources/views/components/language-switcher.blade.php --}}
@php
$languages = [
    'en' => ['name' => 'English', 'flag' => '🇬🇧'],
    'fr' => ['name' => 'Français', 'flag' => '🇫🇷'],
    'de' => ['name' => 'Deutsch', 'flag' => '🇩🇪'],
];
$currentLocale = app()->getLocale();
@endphp

<nav class="lang-switcher" aria-label="{{ __('Language selection') }}">
    @foreach ($languages as $code => $lang)
        @php
            // Build URL with new locale
            $currentPath = request()->path();
            $newPath = preg_replace('/^[a-z]{2}\//', "{$code}/", $currentPath);
            if ($newPath === $currentPath) {
                $newPath = "{$code}/{$currentPath}";
            }
        @endphp
        
        <a 
            href="{{ url($newPath) }}"
            class="lang-switcher__link {{ $currentLocale === $code ? 'active' : '' }}"
            @if($currentLocale === $code) aria-current="true" @endif
            hreflang="{{ $code }}"
        >
            <span aria-hidden="true">{{ $lang['flag'] }}</span>
            <span class="lang-switcher__label">{{ $lang['name'] }}</span>
        </a>
    @endforeach
</nav>
```

### Helper for Localized Routes

```php
// app/Helpers/helpers.php
if (!function_exists('localized_route')) {
    function localized_route(string $name, array $params = [], ?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        return route($name, array_merge(['locale' => $locale], $params));
    }
}

// Usage in Blade:
<a href="{{ localized_route('about') }}">{{ __('About') }}</a>
```

---

## VUE ROUTER

### Router Setup

```typescript
// router/index.ts
import { createRouter, createWebHistory, RouteRecordRaw } from 'vue-router';
import { useI18n } from 'vue-i18n';

const locales = ['en', 'fr', 'de'];
const defaultLocale = 'en';

const routes: RouteRecordRaw[] = [
  {
    path: '/',
    redirect: `/${defaultLocale}`,
  },
  {
    path: '/:locale',
    children: [
      {
        path: '',
        name: 'home',
        component: () => import('@/views/Home.vue'),
      },
      {
        path: 'about',
        name: 'about',
        component: () => import('@/views/About.vue'),
      },
      {
        path: 'contact',
        name: 'contact',
        component: () => import('@/views/Contact.vue'),
      },
    ],
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

// Navigation guard for locale validation
router.beforeEach((to, from, next) => {
  const locale = to.params.locale as string;
  
  if (!locales.includes(locale)) {
    // Redirect to default locale
    const newPath = to.fullPath.replace(/^\/[^/]+/, `/${defaultLocale}`);
    return next(newPath);
  }
  
  // Set i18n locale
  const i18n = useI18n();
  i18n.locale.value = locale;
  
  // Update HTML lang attribute
  document.documentElement.lang = locale;
  
  next();
});

export default router;
```

### Language Switcher Component

```vue
<!-- components/LanguageSwitcher.vue -->
<template>
  <nav class="lang-switcher" aria-label="Language selection">
    <button
      v-for="lang in languages"
      :key="lang.code"
      @click="switchLanguage(lang.code)"
      :class="['lang-switcher__btn', { active: currentLocale === lang.code }]"
      :aria-current="currentLocale === lang.code ? 'true' : undefined"
    >
      <span aria-hidden="true">{{ lang.flag }}</span>
      <span class="lang-switcher__label">{{ lang.name }}</span>
    </button>
  </nav>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';

const languages = [
  { code: 'en', name: 'English', flag: '🇬🇧' },
  { code: 'fr', name: 'Français', flag: '🇫🇷' },
  { code: 'de', name: 'Deutsch', flag: '🇩🇪' },
];

const route = useRoute();
const router = useRouter();
const { locale } = useI18n();

const currentLocale = computed(() => route.params.locale as string);

function switchLanguage(newLocale: string) {
  // Replace locale in current path
  const newPath = route.fullPath.replace(
    `/${currentLocale.value}`,
    `/${newLocale}`
  );
  
  // Save preference
  localStorage.setItem('preferred-locale', newLocale);
  
  // Navigate without full reload
  router.push(newPath);
}
</script>
```

---

## TOKEN-AWARE STYLES

```css
/* Language switcher using design tokens */
.lang-switcher {
  display: flex;
  gap: var(--space-2);
  align-items: center;
}

.lang-switcher__btn,
.lang-switcher__link {
  display: inline-flex;
  align-items: center;
  gap: var(--space-2);
  padding: var(--space-2) var(--space-3);
  background: transparent;
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
  color: var(--text-2);
  font-size: var(--text-sm);
  font-family: var(--font-body);
  cursor: pointer;
  transition: 
    background var(--transition-fast),
    border-color var(--transition-fast),
    color var(--transition-fast);
  text-decoration: none;
}

.lang-switcher__btn:hover,
.lang-switcher__link:hover {
  background: var(--surface-up);
  border-color: var(--accent);
  color: var(--text);
}

.lang-switcher__btn.active,
.lang-switcher__link.active {
  background: var(--accent);
  border-color: var(--accent);
  color: white;
}

.lang-switcher__btn:focus-visible,
.lang-switcher__link:focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 2px;
}

/* Compact mode (icon only) */
.lang-switcher--compact .lang-switcher__label {
  position: absolute;
  width: 1px;
  height: 1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
}

/* Dropdown variant */
.lang-dropdown {
  position: relative;
}

.lang-dropdown__menu {
  position: absolute;
  top: 100%;
  right: 0;
  margin-top: var(--space-2);
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
  box-shadow: var(--shadow-lg);
  min-width: 160px;
  z-index: var(--z-dropdown);
  opacity: 0;
  visibility: hidden;
  transform: translateY(calc(-1 * var(--space-2)));
  transition: 
    opacity var(--transition-fast),
    transform var(--transition-fast),
    visibility var(--transition-fast);
}

.lang-dropdown[open] .lang-dropdown__menu {
  opacity: 1;
  visibility: visible;
  transform: translateY(0);
}

@media (prefers-reduced-motion: reduce) {
  .lang-dropdown__menu {
    transition: none;
  }
}
```

---

## SEO CONSIDERATIONS

```html
<!-- Add hreflang tags in <head> -->
<link rel="alternate" hreflang="en" href="https://example.com/en/about" />
<link rel="alternate" hreflang="fr" href="https://example.com/fr/about" />
<link rel="alternate" hreflang="de" href="https://example.com/de/about" />
<link rel="alternate" hreflang="x-default" href="https://example.com/en/about" />
```

```tsx
// Next.js metadata
export function generateMetadata({ params }: { params: { lang: string } }) {
  const alternates = {
    languages: {
      en: '/en' + pathname,
      fr: '/fr' + pathname,
      de: '/de' + pathname,
    },
  };
  
  return { alternates };
}
```

---

## CHECKLIST

- [ ] URL has language prefix (`/en/`, `/fr/`)
- [ ] Root path redirects to default locale
- [ ] Language switcher updates URL without full reload
- [ ] User preference saved (cookie/localStorage)
- [ ] `<html lang="">` attribute set correctly
- [ ] hreflang tags in `<head>`
- [ ] Switcher is accessible (keyboard, screen reader)
- [ ] Styles use design tokens
- [ ] `prefers-reduced-motion` respected
