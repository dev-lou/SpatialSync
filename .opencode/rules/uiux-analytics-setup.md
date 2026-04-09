---
description: Step-by-step integration for Sentry (error tracking), PostHog (product analytics), and Google Analytics 4. Includes cookie consent integration.
---

# Analytics Setup Skill

> Configure error tracking, product analytics, and web analytics with proper consent management.

---

## CRITICAL RULES

1. **Never load analytics scripts before user consent** (GDPR/CCPA).
2. **Store API keys in `.env`** — never hardcode.
3. **Use design tokens** for any error UI (toasts, banners).
4. **Respect `prefers-reduced-motion`** in any animated feedback.

---

## ENVIRONMENT VARIABLES

```env
# .env (never commit this file)

# Sentry
SENTRY_DSN=https://xxx@xxx.ingest.sentry.io/xxx
SENTRY_ENVIRONMENT=production
SENTRY_RELEASE=1.0.0

# PostHog
POSTHOG_KEY=phc_xxxxxxxxxxxxxxxxxx
POSTHOG_HOST=https://app.posthog.com

# Google Analytics 4
GA_MEASUREMENT_ID=G-XXXXXXXXXX
```

---

## 1. SENTRY (Error Tracking)

### Installation

```bash
npm install @sentry/browser
# Or for frameworks:
npm install @sentry/react @sentry/nextjs @sentry/vue
```

### Vanilla JS Setup

```javascript
// sentry.js
import * as Sentry from '@sentry/browser';

export function initSentry() {
  // Only init if user consented and DSN exists
  const hasConsent = localStorage.getItem('analytics-consent') === 'true';
  const dsn = import.meta.env.VITE_SENTRY_DSN; // or process.env
  
  if (!hasConsent || !dsn) {
    console.log('Sentry: Skipped (no consent or DSN)');
    return;
  }

  Sentry.init({
    dsn,
    environment: import.meta.env.VITE_SENTRY_ENVIRONMENT || 'development',
    release: import.meta.env.VITE_SENTRY_RELEASE,
    
    // Performance monitoring (optional)
    integrations: [
      Sentry.browserTracingIntegration(),
    ],
    tracesSampleRate: 0.1, // 10% of transactions
    
    // Filter out noisy errors
    ignoreErrors: [
      'ResizeObserver loop limit exceeded',
      'Non-Error promise rejection',
      /Loading chunk \d+ failed/,
    ],
    
    // Sanitize sensitive data
    beforeSend(event) {
      // Remove PII from breadcrumbs
      if (event.breadcrumbs) {
        event.breadcrumbs = event.breadcrumbs.map(crumb => {
          if (crumb.data?.url) {
            crumb.data.url = sanitizeUrl(crumb.data.url);
          }
          return crumb;
        });
      }
      return event;
    }
  });

  // Set user context (anonymized)
  Sentry.setUser({
    id: getAnonymousId(), // Hash of user ID, not actual ID
  });
}

function sanitizeUrl(url) {
  try {
    const parsed = new URL(url);
    // Remove sensitive query params
    ['token', 'key', 'password', 'secret'].forEach(param => {
      parsed.searchParams.delete(param);
    });
    return parsed.toString();
  } catch {
    return '[invalid-url]';
  }
}
```

### React Setup

```tsx
// sentry.ts
import * as Sentry from '@sentry/react';

Sentry.init({
  dsn: process.env.NEXT_PUBLIC_SENTRY_DSN,
  integrations: [
    Sentry.browserTracingIntegration(),
    Sentry.replayIntegration({
      maskAllText: true,
      blockAllMedia: true,
    }),
  ],
  tracesSampleRate: 0.1,
  replaysSessionSampleRate: 0.1,
  replaysOnErrorSampleRate: 1.0,
});

// Error boundary component
export const SentryErrorBoundary = Sentry.ErrorBoundary;

// Usage
<SentryErrorBoundary fallback={<ErrorFallback />}>
  <App />
</SentryErrorBoundary>
```

### Token-Aware Error Toast

```typescript
// Show user-friendly error notification
import { showToast } from './toast'; // Your token-aware toast

Sentry.addGlobalEventProcessor((event) => {
  // Show toast for unhandled errors
  if (event.level === 'error') {
    showToast({
      type: 'error',
      title: 'Something went wrong',
      message: 'Our team has been notified. Please try again.',
      duration: 5000,
    });
  }
  return event;
});
```

```css
/* Token-aware error toast */
.toast--error {
  background: var(--surface);
  border: 1px solid var(--error);
  border-left: 4px solid var(--error);
  border-radius: var(--radius-md);
  padding: var(--space-4);
  color: var(--text);
  box-shadow: var(--shadow-lg);
}

.toast__icon--error {
  color: var(--error);
  width: var(--space-5);
  height: var(--space-5);
}
```

---

## 2. POSTHOG (Product Analytics)

### Installation

```bash
npm install posthog-js
```

### Setup

```javascript
// posthog.js
import posthog from 'posthog-js';

export function initPostHog() {
  const hasConsent = localStorage.getItem('analytics-consent') === 'true';
  const apiKey = import.meta.env.VITE_POSTHOG_KEY;
  
  if (!hasConsent || !apiKey) {
    console.log('PostHog: Skipped (no consent or key)');
    return;
  }

  posthog.init(apiKey, {
    api_host: import.meta.env.VITE_POSTHOG_HOST || 'https://app.posthog.com',
    
    // Privacy settings
    persistence: 'localStorage',
    autocapture: true,
    capture_pageview: true,
    capture_pageleave: true,
    
    // Disable session recording by default (enable with consent)
    disable_session_recording: true,
    
    // Mask sensitive elements
    mask_all_text: false,
    mask_all_element_attributes: false,
    
    // Custom properties
    loaded: (posthog) => {
      // Set super properties
      posthog.register({
        app_version: import.meta.env.VITE_APP_VERSION,
        theme: document.documentElement.dataset.theme,
      });
    }
  });
}

// Track custom events
export function trackEvent(eventName, properties = {}) {
  if (typeof posthog !== 'undefined' && posthog.__loaded) {
    posthog.capture(eventName, {
      ...properties,
      timestamp: new Date().toISOString(),
    });
  }
}

// Track page views (for SPAs)
export function trackPageView(path) {
  if (typeof posthog !== 'undefined' && posthog.__loaded) {
    posthog.capture('$pageview', {
      $current_url: window.location.href,
      path,
    });
  }
}
```

### React Integration

```tsx
// PostHogProvider.tsx
import { PostHogProvider as PHProvider } from 'posthog-js/react';
import posthog from 'posthog-js';
import { useEffect } from 'react';
import { useLocation } from 'react-router-dom';

export function PostHogProvider({ children }: { children: React.ReactNode }) {
  return (
    <PHProvider client={posthog}>
      <PostHogPageView />
      {children}
    </PHProvider>
  );
}

function PostHogPageView() {
  const location = useLocation();
  
  useEffect(() => {
    if (posthog.__loaded) {
      posthog.capture('$pageview', {
        $current_url: window.location.href,
      });
    }
  }, [location.pathname]);
  
  return null;
}
```

### Feature Flags

```typescript
// Use PostHog feature flags
import { useFeatureFlag } from 'posthog-js/react';

function MyComponent() {
  const showNewFeature = useFeatureFlag('new-checkout-flow');
  
  if (showNewFeature) {
    return <NewCheckout />;
  }
  
  return <LegacyCheckout />;
}
```

---

## 3. GOOGLE ANALYTICS 4

### Setup with Consent Mode

```html
<!-- In <head>, before any other scripts -->
<script>
  // Google tag (gtag.js) with consent mode
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  
  // Default to denied until consent given
  gtag('consent', 'default', {
    'ad_storage': 'denied',
    'ad_user_data': 'denied',
    'ad_personalization': 'denied',
    'analytics_storage': 'denied',
    'wait_for_update': 500, // Wait for consent tool
  });
  
  gtag('js', new Date());
  gtag('config', '{{ GA_MEASUREMENT_ID }}', {
    send_page_view: false, // We'll send manually after consent
  });
</script>
<script async src="https://www.googletagmanager.com/gtag/js?id={{ GA_MEASUREMENT_ID }}"></script>
```

### JavaScript Module

```javascript
// ga4.js
export function initGA4() {
  const measurementId = import.meta.env.VITE_GA_MEASUREMENT_ID;
  
  if (!measurementId) {
    console.log('GA4: No measurement ID');
    return;
  }

  // Load gtag script dynamically
  const script = document.createElement('script');
  script.src = `https://www.googletagmanager.com/gtag/js?id=${measurementId}`;
  script.async = true;
  document.head.appendChild(script);

  window.dataLayer = window.dataLayer || [];
  window.gtag = function() { dataLayer.push(arguments); };
  
  gtag('js', new Date());
  gtag('config', measurementId, {
    send_page_view: false,
  });
}

// Update consent when user accepts
export function updateGAConsent(granted) {
  if (typeof gtag !== 'undefined') {
    gtag('consent', 'update', {
      'analytics_storage': granted ? 'granted' : 'denied',
    });
    
    if (granted) {
      // Send initial pageview after consent
      gtag('event', 'page_view');
    }
  }
}

// Track custom events
export function trackGA4Event(eventName, params = {}) {
  if (typeof gtag !== 'undefined') {
    gtag('event', eventName, params);
  }
}

// Track page views (for SPAs)
export function trackGA4PageView(path, title) {
  if (typeof gtag !== 'undefined') {
    gtag('event', 'page_view', {
      page_path: path,
      page_title: title,
    });
  }
}
```

---

## COOKIE CONSENT INTEGRATION

### Consent Manager

```javascript
// consent-manager.js
import { initSentry } from './sentry';
import { initPostHog } from './posthog';
import { initGA4, updateGAConsent } from './ga4';

const CONSENT_KEY = 'analytics-consent';
const CONSENT_VERSION = '1.0'; // Bump to re-ask consent

export function hasValidConsent() {
  const consent = localStorage.getItem(CONSENT_KEY);
  if (!consent) return null;
  
  try {
    const parsed = JSON.parse(consent);
    if (parsed.version !== CONSENT_VERSION) return null;
    return parsed.granted;
  } catch {
    return null;
  }
}

export function setConsent(granted) {
  localStorage.setItem(CONSENT_KEY, JSON.stringify({
    granted,
    version: CONSENT_VERSION,
    timestamp: new Date().toISOString(),
  }));
  
  if (granted) {
    initAllAnalytics();
  } else {
    disableAllAnalytics();
  }
}

export function initAllAnalytics() {
  initSentry();
  initPostHog();
  initGA4();
  updateGAConsent(true);
}

export function disableAllAnalytics() {
  // Clear PostHog
  if (typeof posthog !== 'undefined') {
    posthog.opt_out_capturing();
  }
  
  // Update GA consent
  updateGAConsent(false);
  
  // Clear cookies
  document.cookie.split(';').forEach(cookie => {
    const name = cookie.split('=')[0].trim();
    if (name.startsWith('_ga') || name.startsWith('ph_')) {
      document.cookie = `${name}=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/`;
    }
  });
}

// Check consent on page load
export function checkConsent() {
  const consent = hasValidConsent();
  
  if (consent === true) {
    initAllAnalytics();
  } else if (consent === null) {
    // Show consent banner
    showConsentBanner();
  }
  // If consent === false, do nothing (user declined)
}
```

### Token-Aware Consent Banner

```html
<div class="consent-banner" id="consent-banner" role="dialog" aria-labelledby="consent-title">
  <div class="consent-banner__content">
    <h2 id="consent-title" class="consent-banner__title">We value your privacy</h2>
    <p class="consent-banner__text">
      We use cookies to improve your experience and analyze site usage.
    </p>
    <div class="consent-banner__actions">
      <button class="btn btn--outline" onclick="handleConsent(false)">
        Decline
      </button>
      <button class="btn btn--primary" onclick="handleConsent(true)">
        Accept
      </button>
    </div>
    <a href="/privacy" class="consent-banner__link">Privacy Policy</a>
  </div>
</div>
```

```css
.consent-banner {
  position: fixed;
  bottom: var(--space-4);
  left: var(--space-4);
  right: var(--space-4);
  max-width: 480px;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  padding: var(--space-6);
  box-shadow: var(--shadow-xl);
  z-index: var(--z-modal);
  animation: slideUp var(--duration-base) var(--ease-out);
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(var(--space-4));
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@media (prefers-reduced-motion: reduce) {
  .consent-banner {
    animation: none;
  }
}

.consent-banner__title {
  font-family: var(--font-display);
  font-size: var(--text-lg);
  font-weight: 600;
  color: var(--text);
  margin-bottom: var(--space-2);
}

.consent-banner__text {
  font-size: var(--text-sm);
  color: var(--text-2);
  margin-bottom: var(--space-4);
}

.consent-banner__actions {
  display: flex;
  gap: var(--space-3);
}

.consent-banner__link {
  display: block;
  margin-top: var(--space-3);
  font-size: var(--text-xs);
  color: var(--muted);
}
```

---

## LARAVEL INTEGRATION

### Config

```php
// config/analytics.php
return [
    'sentry_dsn' => env('SENTRY_DSN'),
    'posthog_key' => env('POSTHOG_KEY'),
    'ga_measurement_id' => env('GA_MEASUREMENT_ID'),
];
```

### Blade Partial

```blade
{{-- resources/views/partials/analytics.blade.php --}}
@if(config('analytics.ga_measurement_id'))
<script>
  window.analyticsConfig = {
    sentry: @json(config('analytics.sentry_dsn')),
    posthog: @json(config('analytics.posthog_key')),
    ga: @json(config('analytics.ga_measurement_id')),
  };
</script>
<script src="{{ mix('js/analytics.js') }}" defer></script>
@endif
```

---

## CHECKLIST

- [ ] API keys stored in `.env`
- [ ] Scripts load only after consent
- [ ] Consent banner uses design tokens
- [ ] Error toasts use token-aware styling
- [ ] Sensitive data sanitized before sending
- [ ] PII not tracked (emails, passwords, etc.)
- [ ] SPA page views tracked correctly
- [ ] `prefers-reduced-motion` respected in UI
- [ ] Privacy policy link in consent banner
- [ ] Consent version tracked for re-consent
