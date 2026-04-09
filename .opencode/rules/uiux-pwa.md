---
description: Advanced PWA patterns including service workers, manifest.json, app icon generation, and offline fallback strategies.
---

# UI/UX Progressive Web Apps (PWA) 2026

> Service workers, manifest.json, app icon generation, 
> and offline fallback strategies for modern web apps.

---

## 1. MANIFEST.JSON (Icons, Theme, Display)

```json
{
  "name": "OpenCode Premium UI/UX",
  "short_name": "OpenCode",
  "description": "The definitive UI/UX design system for 2026.",
  "start_url": "/",
  "display": "standalone",
  "background_color": "#FFFFFF",
  "theme_color": "#0066FF",
  "icons": [
    {
      "src": "/icons/icon-192x192.png",
      "sizes": "192x192",
      "type": "image/png",
      "purpose": "any maskable"
    },
    {
      "src": "/icons/icon-512x512.png",
      "sizes": "512x512",
      "type": "image/png"
    }
  ]
}
```

---

## 2. SERVICE WORKER (Basic Offline Fallback)

```javascript
// sw.js — Service Worker
const CACHE_NAME = 'opencode-v1';
const ASSETS = [
  '/',
  '/index.html',
  '/styles/tokens.css',
  '/styles/globals.css',
  '/scripts/main.js',
  '/offline.html'
];

self.addEventListener('install', (e) => {
  e.waitUntil(
    caches.open(CACHE_NAME).then(cache => cache.addAll(ASSETS))
  );
});

self.addEventListener('fetch', (e) => {
  e.respondWith(
    caches.match(e.request).then(response => {
      return response || fetch(e.request).catch(() => caches.match('/offline.html'));
    })
  );
});
```

---

## 3. SERVICE WORKER REGISTRATION

```javascript
// Register Service Worker in main.js
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/sw.js')
      .then(reg => console.log('SW Registered', reg))
      .catch(err => console.log('SW Registration Failed', err));
  });
}
```

---

## 4. APP ICON GENERATION (Touch Icons, Favicon)

```html
<!-- Apple Touch Icon -->
<link rel="apple-touch-icon" sizes="180x180" href="/icons/apple-touch-icon.png">

<!-- Favicons -->
<link rel="icon" type="image/png" sizes="32x32" href="/icons/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/icons/favicon-16x16.png">

<!-- Safari Pinned Tab -->
<link rel="mask-icon" href="/icons/safari-pinned-tab.svg" color="#0066FF">
```

---

## 5. OFFLINE FALLBACK STRATEGY

**Rule**: Always provide a meaningful offline page instead of the browser's default "No Internet" screen.

```html
<!-- offline.html -->
<div class="layout-focus">
  <div class="card">
    <i data-lucide="wifi-off" style="width:48px;height:48px;color:var(--muted)"></i>
    <h1>You're Offline</h1>
    <p>Please check your internet connection and try again.</p>
    <button class="btn btn--accent" onclick="window.location.reload()">Retry</button>
  </div>
</div>
```

---

## 6. PWA INSTALL PROMPT (Custom UI)

```javascript
// Custom Install Prompt
let deferredPrompt;
const installBtn = document.getElementById('install-btn');

window.addEventListener('beforeinstallprompt', (e) => {
  e.preventDefault();
  deferredPrompt = e;
  installBtn.hidden = false;
});

installBtn.addEventListener('click', async () => {
  if (deferredPrompt) {
    deferredPrompt.prompt();
    const { outcome } = await deferredPrompt.userChoice;
    if (outcome === 'accepted') console.log('User accepted install');
    deferredPrompt = null;
    installBtn.hidden = true;
  }
});
```
