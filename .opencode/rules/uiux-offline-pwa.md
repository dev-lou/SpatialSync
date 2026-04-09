---
description: Advanced service worker caching strategies with Workbox. Network-first for APIs, cache-first for assets, stale-while-revalidate for images.
---

# Offline PWA Skill

> Advanced caching strategies for Progressive Web Apps.

---

## CACHING STRATEGIES

| Strategy | Use For | Behavior |
|----------|---------|----------|
| **Network First** | API calls, dynamic content | Try network, fallback to cache |
| **Cache First** | Static assets (JS, CSS) | Use cache, fetch in background |
| **Stale While Revalidate** | Images, fonts | Serve cache immediately, update in background |
| **Network Only** | Auth, payments | Never cache |
| **Cache Only** | App shell | Always from cache |

---

## WORKBOX SETUP

### Installation

```bash
npm install workbox-webpack-plugin workbox-window
# Or for Vite:
npm install vite-plugin-pwa
```

### Vite PWA Plugin

```typescript
// vite.config.ts
import { defineConfig } from 'vite';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig({
  plugins: [
    VitePWA({
      registerType: 'autoUpdate',
      includeAssets: ['favicon.ico', 'robots.txt', 'apple-touch-icon.png'],
      manifest: {
        name: 'My App',
        short_name: 'App',
        description: 'My Progressive Web App',
        theme_color: '#3B82F6',
        background_color: '#ffffff',
        display: 'standalone',
        icons: [
          {
            src: 'pwa-192x192.png',
            sizes: '192x192',
            type: 'image/png',
          },
          {
            src: 'pwa-512x512.png',
            sizes: '512x512',
            type: 'image/png',
          },
          {
            src: 'pwa-512x512.png',
            sizes: '512x512',
            type: 'image/png',
            purpose: 'maskable',
          },
        ],
      },
      workbox: {
        globPatterns: ['**/*.{js,css,html,ico,png,svg,woff2}'],
        runtimeCaching: [
          // API calls - Network First
          {
            urlPattern: /^https:\/\/api\./i,
            handler: 'NetworkFirst',
            options: {
              cacheName: 'api-cache',
              expiration: {
                maxEntries: 100,
                maxAgeSeconds: 60 * 60 * 24, // 24 hours
              },
              networkTimeoutSeconds: 10,
              cacheableResponse: {
                statuses: [0, 200],
              },
            },
          },
          // Images - Stale While Revalidate
          {
            urlPattern: /\.(?:png|gif|jpg|jpeg|svg|webp|avif)$/i,
            handler: 'StaleWhileRevalidate',
            options: {
              cacheName: 'images-cache',
              expiration: {
                maxEntries: 100,
                maxAgeSeconds: 60 * 60 * 24 * 30, // 30 days
              },
            },
          },
          // Fonts - Cache First
          {
            urlPattern: /\.(?:woff|woff2|ttf|otf|eot)$/i,
            handler: 'CacheFirst',
            options: {
              cacheName: 'fonts-cache',
              expiration: {
                maxEntries: 20,
                maxAgeSeconds: 60 * 60 * 24 * 365, // 1 year
              },
            },
          },
          // Google Fonts
          {
            urlPattern: /^https:\/\/fonts\.googleapis\.com/i,
            handler: 'StaleWhileRevalidate',
            options: {
              cacheName: 'google-fonts-stylesheets',
            },
          },
          {
            urlPattern: /^https:\/\/fonts\.gstatic\.com/i,
            handler: 'CacheFirst',
            options: {
              cacheName: 'google-fonts-webfonts',
              expiration: {
                maxEntries: 30,
                maxAgeSeconds: 60 * 60 * 24 * 365,
              },
            },
          },
        ],
      },
    }),
  ],
});
```

---

## CUSTOM SERVICE WORKER

### `sw.js`

```javascript
// sw.js
import { precacheAndRoute, cleanupOutdatedCaches } from 'workbox-precaching';
import { registerRoute, NavigationRoute } from 'workbox-routing';
import { 
  NetworkFirst, 
  CacheFirst, 
  StaleWhileRevalidate 
} from 'workbox-strategies';
import { ExpirationPlugin } from 'workbox-expiration';
import { CacheableResponsePlugin } from 'workbox-cacheable-response';

// Precache static assets
precacheAndRoute(self.__WB_MANIFEST);
cleanupOutdatedCaches();

// API requests - Network First with timeout
registerRoute(
  ({ url }) => url.pathname.startsWith('/api/'),
  new NetworkFirst({
    cacheName: 'api-cache',
    networkTimeoutSeconds: 10,
    plugins: [
      new CacheableResponsePlugin({
        statuses: [0, 200],
      }),
      new ExpirationPlugin({
        maxEntries: 100,
        maxAgeSeconds: 24 * 60 * 60, // 24 hours
      }),
    ],
  })
);

// Static assets - Cache First
registerRoute(
  ({ request }) => 
    request.destination === 'script' ||
    request.destination === 'style',
  new CacheFirst({
    cacheName: 'static-cache',
    plugins: [
      new CacheableResponsePlugin({
        statuses: [0, 200],
      }),
      new ExpirationPlugin({
        maxEntries: 100,
        maxAgeSeconds: 30 * 24 * 60 * 60, // 30 days
      }),
    ],
  })
);

// Images - Stale While Revalidate
registerRoute(
  ({ request }) => request.destination === 'image',
  new StaleWhileRevalidate({
    cacheName: 'images-cache',
    plugins: [
      new CacheableResponsePlugin({
        statuses: [0, 200],
      }),
      new ExpirationPlugin({
        maxEntries: 100,
        maxAgeSeconds: 30 * 24 * 60 * 60, // 30 days
        purgeOnQuotaError: true,
      }),
    ],
  })
);

// Offline fallback for navigation requests
const offlineFallback = new NavigationRoute(
  async ({ request }) => {
    try {
      return await new NetworkFirst({
        cacheName: 'pages-cache',
        plugins: [
          new CacheableResponsePlugin({ statuses: [200] }),
        ],
      }).handle({ request });
    } catch (error) {
      return caches.match('/offline.html');
    }
  }
);

registerRoute(offlineFallback);

// Listen for skip waiting message
self.addEventListener('message', (event) => {
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
});
```

---

## OFFLINE FALLBACK PAGE

### `public/offline.html`

```html
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Offline | My App</title>
  <style>
    :root {
      --bg: #ffffff;
      --surface: #f8fafc;
      --text: #0f172a;
      --text-2: #64748b;
      --accent: #3b82f6;
      --space-4: 16px;
      --space-6: 24px;
      --space-8: 32px;
      --radius-lg: 12px;
      --font-display: system-ui, sans-serif;
      --font-body: system-ui, sans-serif;
    }
    
    [data-theme="dark"] {
      --bg: #0f172a;
      --surface: #1e293b;
      --text: #f1f5f9;
      --text-2: #94a3b8;
    }
    
    * { margin: 0; padding: 0; box-sizing: border-box; }
    
    body {
      font-family: var(--font-body);
      background: var(--bg);
      color: var(--text);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: var(--space-6);
    }
    
    .offline {
      text-align: center;
      max-width: 400px;
    }
    
    .offline__icon {
      width: 80px;
      height: 80px;
      margin-bottom: var(--space-6);
      color: var(--text-2);
    }
    
    .offline__title {
      font-family: var(--font-display);
      font-size: 1.5rem;
      font-weight: 600;
      margin-bottom: var(--space-4);
    }
    
    .offline__text {
      color: var(--text-2);
      margin-bottom: var(--space-6);
      line-height: 1.6;
    }
    
    .offline__btn {
      display: inline-flex;
      align-items: center;
      gap: var(--space-4);
      padding: var(--space-4) var(--space-6);
      background: var(--accent);
      color: white;
      border: none;
      border-radius: var(--radius-lg);
      font-size: 1rem;
      font-weight: 500;
      cursor: pointer;
      transition: opacity 0.2s;
    }
    
    .offline__btn:hover {
      opacity: 0.9;
    }
  </style>
</head>
<body>
  <div class="offline">
    <svg class="offline__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M1 1l22 22M16.72 11.06A10.94 10.94 0 0 1 19 12.55M5 12.55a10.94 10.94 0 0 1 5.17-2.39M10.71 5.05A16 16 0 0 1 22.58 9M1.42 9a15.91 15.91 0 0 1 4.7-2.88M8.53 16.11a6 6 0 0 1 6.95 0M12 20h.01"/>
    </svg>
    
    <h1 class="offline__title">You're offline</h1>
    
    <p class="offline__text">
      It looks like you've lost your internet connection. 
      Please check your connection and try again.
    </p>
    
    <button class="offline__btn" onclick="location.reload()">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M23 4v6h-6M1 20v-6h6M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>
      </svg>
      Try again
    </button>
  </div>
  
  <script>
    // Auto-reload when back online
    window.addEventListener('online', () => {
      location.reload();
    });
    
    // Respect dark mode preference
    if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
      document.documentElement.dataset.theme = 'dark';
    }
  </script>
</body>
</html>
```

---

## SERVICE WORKER REGISTRATION

```typescript
// main.ts
import { registerSW } from 'virtual:pwa-register';

const updateSW = registerSW({
  onNeedRefresh() {
    // Show update prompt
    if (confirm('New content available. Reload?')) {
      updateSW(true);
    }
  },
  onOfflineReady() {
    console.log('App ready to work offline');
    showToast('App ready for offline use');
  },
  onRegistered(registration) {
    // Check for updates periodically
    if (registration) {
      setInterval(() => {
        registration.update();
      }, 60 * 60 * 1000); // Every hour
    }
  },
  onRegisterError(error) {
    console.error('SW registration error:', error);
  },
});
```

### Token-Aware Update Prompt

```typescript
// components/PWAUpdatePrompt.tsx
import { useRegisterSW } from 'virtual:pwa-register/react';

export function PWAUpdatePrompt() {
  const {
    needRefresh: [needRefresh, setNeedRefresh],
    updateServiceWorker,
  } = useRegisterSW();

  if (!needRefresh) return null;

  return (
    <div className="pwa-prompt" role="alert">
      <div className="pwa-prompt__content">
        <p className="pwa-prompt__text">
          A new version is available!
        </p>
        <div className="pwa-prompt__actions">
          <button 
            className="btn btn--ghost"
            onClick={() => setNeedRefresh(false)}
          >
            Later
          </button>
          <button 
            className="btn btn--primary"
            onClick={() => updateServiceWorker(true)}
          >
            Update now
          </button>
        </div>
      </div>
    </div>
  );
}
```

```css
.pwa-prompt {
  position: fixed;
  bottom: var(--space-4);
  left: var(--space-4);
  right: var(--space-4);
  max-width: 400px;
  margin: 0 auto;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  padding: var(--space-4);
  box-shadow: var(--shadow-lg);
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
  .pwa-prompt {
    animation: none;
  }
}

.pwa-prompt__actions {
  display: flex;
  gap: var(--space-3);
  justify-content: flex-end;
  margin-top: var(--space-3);
}
```

---

## BACKGROUND SYNC

```javascript
// sw.js - Queue failed requests for later
import { BackgroundSyncPlugin } from 'workbox-background-sync';
import { registerRoute } from 'workbox-routing';
import { NetworkOnly } from 'workbox-strategies';

const bgSyncPlugin = new BackgroundSyncPlugin('failedRequests', {
  maxRetentionTime: 24 * 60, // Retry for max 24 hours (in minutes)
  onSync: async ({ queue }) => {
    let entry;
    while ((entry = await queue.shiftRequest())) {
      try {
        await fetch(entry.request);
        console.log('Replayed request:', entry.request.url);
      } catch (error) {
        console.error('Failed to replay:', entry.request.url);
        await queue.unshiftRequest(entry);
        throw error;
      }
    }
  },
});

// Queue POST requests when offline
registerRoute(
  ({ request }) => request.method === 'POST' && request.url.includes('/api/'),
  new NetworkOnly({
    plugins: [bgSyncPlugin],
  }),
  'POST'
);
```

---

## CHECKLIST

- [ ] Service worker registered
- [ ] Precaching for static assets
- [ ] Runtime caching strategies configured
- [ ] Offline fallback page created
- [ ] Update prompt shown to users
- [ ] Background sync for failed requests
- [ ] Periodic update checks
- [ ] Styles use design tokens
- [ ] `prefers-reduced-motion` respected
