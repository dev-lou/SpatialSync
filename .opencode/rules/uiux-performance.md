# UI/UX Performance 2026

> Core Web Vitals thresholds, font loading, resource hints, image format decisions,
> Lighthouse CI, bundle budgets. Performance is UX — slow = broken.

---

## CORE WEB VITALS — 2026 Thresholds

Google's ranking signals. Measure all three on real devices, not just desktop.

| Metric | Good | Needs Work | Poor | What it measures |
|--------|------|-----------|------|-----------------|
| **LCP** (Largest Contentful Paint) | ≤ 2.5s | 2.5–4.0s | > 4.0s | When main content loads |
| **CLS** (Cumulative Layout Shift) | ≤ 0.1 | 0.1–0.25 | > 0.25 | Visual stability (no jumps) |
| **INP** (Interaction to Next Paint) | ≤ 200ms | 200–500ms | > 500ms | Responsiveness to interaction |

> **INP replaced FID in March 2024.** If your docs still say FID, they're outdated.

### What Causes Each Metric to Fail

**LCP fails when:**
- Hero image has no `fetchpriority="high"` or `loading="eager"`
- Render-blocking CSS/JS delays the paint
- Server response time (TTFB) > 800ms
- Hero is a CSS `background-image` (browser can't preload it)
- Web font blocks text rendering (no `font-display: swap`)

**CLS fails when:**
- Images have no `width`/`height` attributes (browser doesn't reserve space)
- Web fonts swap and cause layout reflow
- Ads, embeds, or iframes load without reserved dimensions
- Dynamic content injected above existing content

**INP fails when:**
- Long JavaScript tasks (> 50ms) block the main thread
- Event handlers do heavy synchronous work
- Too many DOM nodes (> 1500 is a warning)
- React re-renders entire subtrees on every interaction

---

## FONT LOADING STRATEGY

### The Performance Stack (apply in this order)

```html
<!-- 1. Preconnect — tells browser to open connection ASAP -->
<link rel="preconnect" href="https://fonts.bunny.net">
<link rel="preconnect" href="https://fonts.bunny.net" crossorigin>

<!-- 2. Preload the most critical font file directly -->
<!-- Find the actual .woff2 URL from the font CSS, then preload it -->
<link rel="preload"
  href="https://fonts.bunny.net/plus-jakarta-sans/files/plus-jakarta-sans-latin-500-normal.woff2"
  as="font" type="font/woff2" crossorigin>

<!-- 3. Load font CSS non-render-blocking (media trick) -->
<link rel="stylesheet"
  href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700&display=swap"
  media="print" onload="this.media='all'">
<noscript>
  <link rel="stylesheet"
    href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700&display=swap">
</noscript>
```

### font-display Values — Choose Correctly

```css
@font-face {
  font-family: 'Plus Jakarta Sans';
  font-display: swap;     /* ← use this for body text */
  /* swap: show fallback immediately, swap to custom when loaded → CLS risk if metrics differ */
  /* optional: only use if cached — best for non-critical fonts */
  /* block: reserve space, show nothing for 100ms → best for icon fonts */
  /* fallback: 100ms block, 3s swap window → good for display fonts */
}
```

| Use case | `font-display` | Why |
|----------|---------------|-----|
| Body / paragraph text | `swap` | Users can read immediately |
| Display / hero headline | `optional` | If not cached, use fallback — avoids CLS |
| Icon font (e.g., custom SVG font) | `block` | Blank > wrong icon |
| Monospace / code | `swap` | Functional, needs to be readable |

### Font Fallback — Reduce CLS

```css
/* Match system fallback metrics to your web font to minimize layout shift */
/* Tool: https://screenspan.net/fallback */

body {
  font-family: 'Plus Jakarta Sans', 'Arial Narrow', Arial, sans-serif;
  /* Size-adjust reduces CLS when font swaps */
}

/* Advanced: CSS @font-face size-adjust (2026 support: Chrome 92+, FF 92+, Safari 17+) */
@font-face {
  font-family: 'Plus Jakarta Sans Fallback';
  src: local('Arial');
  ascent-override:  90%;
  descent-override: 22%;
  line-gap-override: 0%;
  size-adjust: 103%;
}
body {
  font-family: 'Plus Jakarta Sans', 'Plus Jakarta Sans Fallback', sans-serif;
}
```

---

## RESOURCE HINTS

```html
<!-- Preconnect: for any third-party origin you'll load from -->
<!-- Use for: fonts, CDNs, analytics, API endpoints you call immediately -->
<link rel="preconnect" href="https://cdn.jsdelivr.net">
<link rel="preconnect" href="https://api.yourapp.com">

<!-- dns-prefetch: lighter than preconnect, for origins loaded later -->
<!-- Use for: resources loaded on interaction (e.g., map tiles, chat widget) -->
<link rel="dns-prefetch" href="https://maps.googleapis.com">
<link rel="dns-prefetch" href="https://widget.intercom.io">

<!-- Preload: for critical resources on the current page -->
<!-- Only preload what's needed for FIRST paint — don't preload everything -->
<link rel="preload" href="/hero.webp" as="image" fetchpriority="high">
<link rel="preload" href="/fonts/brand.woff2" as="font" type="font/woff2" crossorigin>
<!-- Preload critical CSS (if not inlined) -->
<link rel="preload" href="/styles/tokens.css" as="style">

<!-- Prefetch: for resources needed on the NEXT page (after likely navigation) -->
<!-- Use for: next page's JS chunk, next page's hero image -->
<link rel="prefetch" href="/js/dashboard.chunk.js">

<!-- Modulepreload: preload JS modules (better than preload for ES modules) -->
<link rel="modulepreload" href="/js/main.js">
```

### Decision Guide

```
User will definitely see this on current page?
├─ Critical (above fold): preload
└─ Non-critical (below fold): nothing (lazy load)

User will likely navigate to another page?
└─ prefetch the next page's key resources

Third-party origin needed for current page?
├─ Connection needed immediately: preconnect
└─ Connection needed on interaction: dns-prefetch

Caution: too many preloads = bandwidth competition
Rule: preload max 3–5 resources per page
```

---

## IMAGE FORMAT DECISION TREE

```
What is the image?
├─ Photograph / complex image
│   ├─ Need transparency?
│   │   └─ AVIF (best) → WebP fallback → PNG last resort
│   └─ No transparency
│       └─ AVIF (best compression) → WebP fallback → JPEG last resort
├─ Illustration / flat graphic with few colors
│   └─ SVG (if vector) → WebP → PNG
├─ Icon / logo / UI element
│   └─ SVG always — scales perfectly, tiny file, CSS-styleable
├─ Animated
│   └─ WebP (animated) → AVIF → GIF (never for photos)
└─ Screenshot / UI mockup
    └─ WebP (lossless) → PNG
```

### HTML Implementation (all formats with fallback)

```html
<!-- <picture> for format negotiation — browser picks first supported -->
<picture>
  <source srcset="hero.avif" type="image/avif">
  <source srcset="hero.webp" type="image/webp">
  <img src="hero.jpg"
    width="1920" height="1080"
    alt="Hero image description"
    loading="eager"
    fetchpriority="high"
    decoding="async">
</picture>

<!-- Responsive images with srcset -->
<picture>
  <source
    type="image/avif"
    srcset="
      feature-400.avif  400w,
      feature-800.avif  800w,
      feature-1200.avif 1200w"
    sizes="(max-width: 768px) 100vw, 50vw">
  <source
    type="image/webp"
    srcset="
      feature-400.webp  400w,
      feature-800.webp  800w,
      feature-1200.webp 1200w"
    sizes="(max-width: 768px) 100vw, 50vw">
  <img src="feature-800.jpg"
    width="800" height="500"
    alt="Feature visual"
    loading="lazy"
    decoding="async">
</picture>
```

### CLS Prevention — Always Reserve Space

```html
<!-- ALWAYS include width + height — browser computes aspect ratio before load -->
<img src="..." width="800" height="500" alt="...">
<!-- Browser calculates: 500/800 = 62.5% aspect ratio, reserves that space -->
<!-- Without this: layout jumps when image loads = CLS fail -->

<!-- CSS: maintain aspect ratio responsively -->
img {
  max-width: 100%;
  height: auto;    /* this is why width+height in HTML still works responsively */
}

<!-- Explicit aspect-ratio for dynamic images -->
.product-img {
  aspect-ratio: 4/3;
  width: 100%;
  object-fit: cover;
}
```

### Image Budget

| Image type | Target size | Format |
|-----------|------------|--------|
| Hero (above fold) | < 200KB | AVIF/WebP |
| Feature/section | < 100KB | WebP |
| Card thumbnail | < 40KB | WebP |
| Avatar (100×100) | < 10KB | WebP |
| Open Graph (og:image) | < 100KB | JPEG/WebP |
| Favicon | < 5KB | SVG or ICO |

### Convert to WebP/AVIF (build scripts)

```bash
# Install sharp CLI
npm install --save-dev sharp-cli

# Convert all JPGs to WebP
npx sharp-cli -i "public/images/**/*.{jpg,jpeg,png}" -o public/images/ -f webp

# Convert to AVIF
npx sharp-cli -i "public/images/**/*.{jpg,jpeg}" -o public/images/ -f avif --quality 60
```

---

## CRITICAL RENDERING PATH (HTML `<head>` Order)

```html
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- 1. Anti-FOWT — inline, FIRST (from uiux-components.md) -->
  <script>(function(){var t=localStorage.getItem('theme')||(matchMedia('(prefers-color-scheme:dark)').matches?'dark':'light');document.documentElement.setAttribute('data-theme',t);})();</script>

  <!-- 2. Preconnect (before font CSS) -->
  <link rel="preconnect" href="https://fonts.bunny.net">
  <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
  <link rel="preconnect" href="https://cdn.jsdelivr.net">

  <!-- 3. Preload critical font file -->
  <link rel="preload" href="FONT_WOFF2_URL" as="font" type="font/woff2" crossorigin>

  <!-- 4. Preload critical above-fold image -->
  <link rel="preload" href="/hero.webp" as="image" fetchpriority="high">

  <!-- 5. Critical CSS — inline for < 14KB, link for larger -->
  <style>/* tokens.css + above-fold styles inlined here */</style>
  <!-- OR: <link rel="stylesheet" href="/styles/critical.css"> -->

  <!-- 6. Non-critical CSS — deferred -->
  <link rel="stylesheet" href="/styles/main.css" media="print" onload="this.media='all'">

  <!-- 7. Font CSS — deferred -->
  <link rel="stylesheet" href="BUNNY_FONT_URL&display=swap" media="print" onload="this.media='all'">

  <!-- 8. Meta, OG, title -->
  <title>Page Title</title>
  <meta name="description" content="...">
  <meta property="og:image" content="/og-image.jpg">
</head>
<body>
  <!-- Content -->

  <!-- 9. Scripts — before </body> or type="module" -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
  <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js" defer></script>
  <script src="/js/main.js" defer></script>
</body>
```

---

## JAVASCRIPT PERFORMANCE

### Long Task Prevention

```javascript
// Long tasks (>50ms) block INP. Break them up with scheduler.

// BAD: blocks main thread
function processLargeList(items) {
  return items.map(item => heavyComputation(item)); // blocks if items.length > 1000
}

// GOOD: yield to browser between chunks
async function processLargeListAsync(items) {
  const results = [];
  for (let i = 0; i < items.length; i++) {
    results.push(heavyComputation(items[i]));
    // Yield every 50 items — lets browser handle clicks/input
    if (i % 50 === 0) await scheduler.yield?.() ?? new Promise(r => setTimeout(r, 0));
  }
  return results;
}

// Defer non-critical work
requestIdleCallback(() => {
  initAnalytics();
  loadChatWidget();
  prefetchNextPage();
}, { timeout: 2000 });
```

### Event Handler Optimization

```javascript
// NEVER attach heavy logic directly to scroll/resize/input
// Use debounce/throttle

function debounce(fn, delay) {
  let t; return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), delay); };
}
function throttle(fn, limit) {
  let last = 0;
  return (...args) => { const now = Date.now(); if (now - last >= limit) { last = now; fn(...args); } };
}

// Scroll: throttle (not debounce — we want regular calls)
window.addEventListener('scroll', throttle(handleScroll, 100), { passive: true });

// Resize: debounce (only care about final state)
window.addEventListener('resize', debounce(handleResize, 200));

// Search input: debounce (wait until user stops typing)
searchInput.addEventListener('input', debounce(fetchResults, 350));
```

---

## LIGHTHOUSE CI

### Setup

```bash
npm install --save-dev @lhci/cli
```

```yaml
# .lighthouserc.yml
ci:
  collect:
    url:
      - http://localhost:3000
      - http://localhost:3000/about
      - http://localhost:3000/pricing
    startServerCommand: 'npm run build && npm run start'
    numberOfRuns: 3

  assert:
    assertions:
      # Core Web Vitals thresholds
      'categories:performance':     ['error', { minScore: 0.85 }]
      'categories:accessibility':   ['error', { minScore: 0.95 }]
      'categories:best-practices':  ['error', { minScore: 0.90 }]
      'categories:seo':             ['error', { minScore: 0.90 }]

      # Specific metric gates
      'first-contentful-paint':     ['warn',  { maxNumericValue: 2000 }]
      'largest-contentful-paint':   ['error', { maxNumericValue: 2500 }]
      'cumulative-layout-shift':    ['error', { maxNumericValue: 0.1 }]
      'total-blocking-time':        ['warn',  { maxNumericValue: 300 }]

      # Audit gates
      'uses-webp-images':           ['warn',  { minScore: 1 }]
      'uses-optimized-images':      ['warn',  { minScore: 1 }]
      'render-blocking-resources':  ['warn',  { maxLength: 0 }]
      'unused-css-rules':           ['warn',  { maxLength: 3 }]
      'image-explicit-dimensions':  ['error', { minScore: 1 }]

  upload:
    target: temporary-public-storage  # or lhci server
```

```yaml
# .github/workflows/lighthouse.yml
name: Lighthouse CI
on: [push, pull_request]
jobs:
  lighthouse:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-node@v4
      - run: npm ci && npm run build
      - run: npx lhci autorun
        env: { LHCI_GITHUB_APP_TOKEN: ${{ secrets.LHCI_GITHUB_APP_TOKEN }} }
```

---

## BUNDLE BUDGET ENFORCEMENT

```javascript
// next.config.js (Next.js)
const { BundleAnalyzerPlugin } = require('webpack-bundle-analyzer');

module.exports = {
  experimental: { bundlePagesRouterDependencies: true },
  webpack(config, { isServer }) {
    if (process.env.ANALYZE === 'true') {
      config.plugins.push(new BundleAnalyzerPlugin({ analyzerMode: 'static' }));
    }
    return config;
  },
};
// ANALYZE=true npm run build → opens visual bundle map
```

```json
// package.json — size-limit (enforces budget in CI)
"size-limit": [
  { "path": "dist/js/main.*.js",  "limit": "200 KB" },
  { "path": "dist/css/main.*.css","limit": "30 KB" }
],
"scripts": {
  "size": "size-limit"
}
```

| Asset | Budget | Action if exceeded |
|-------|--------|--------------------|
| Initial JS (gzip) | 200KB | Code split, dynamic import |
| Per-route JS | 50KB | Move to lazy import |
| CSS (gzip) | 30KB | PurgeCSS, remove unused |
| Fonts total | 100KB | Subset fonts, fewer weights |
| Hero image | 200KB | AVIF, reduce dimensions |
| Total page weight | 1MB | Audit largest assets |

---

## LCP ELEMENT DETECTION RULE

**Rule**: The AI MUST identify the Largest Contentful Paint (LCP) element on every page and optimize it. LCP is the main ranking signal for perceived load speed.

### LCP Identification Process

When building or reviewing any page, follow this checklist:

```
1. IDENTIFY the LCP element (usually one of these):
   - Hero image
   - Hero heading (<h1>)
   - Background image in hero section
   - Video poster frame
   - Above-the-fold product image

2. APPLY these optimizations to the LCP element:
   - fetchpriority="high" (images only)
   - loading="eager" (NOT lazy!)
   - Preload link in <head>
   - No CSS background-image (use <img>)
   - Serve from same origin (avoid CORS delay)
```

### LCP Element Patterns by Page Type

| Page Type | Likely LCP Element | Optimization |
|-----------|-------------------|--------------|
| Homepage | Hero image or H1 | Preload image, inline critical CSS |
| Product | Product image | Preload, fetchpriority="high" |
| Blog/Article | Featured image or H1 | Preload image, font preload |
| Landing page | Hero image/video poster | Preload, eager load |
| Dashboard | First chart or KPI card | Preload data, skeleton |

### LCP Image Optimization

```html
<!-- STEP 1: Preload in <head> BEFORE stylesheets -->
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  
  <!-- Preload LCP image first — MUST be early in <head> -->
  <link 
    rel="preload" 
    href="/images/hero.webp" 
    as="image" 
    type="image/webp"
    fetchpriority="high"
  >
  
  <!-- For responsive images, preload with imagesrcset -->
  <link
    rel="preload"
    as="image"
    fetchpriority="high"
    imagesrcset="
      /images/hero-400.webp 400w,
      /images/hero-800.webp 800w,
      /images/hero-1200.webp 1200w"
    imagesizes="100vw"
  >
  
  <!-- Then fonts, then styles -->
  <link rel="preconnect" href="https://fonts.bunny.net">
  <link rel="stylesheet" href="/styles/main.css">
</head>

<!-- STEP 2: In body, use proper attributes -->
<body>
  <section class="hero">
    <picture>
      <source srcset="/images/hero.avif" type="image/avif">
      <source srcset="/images/hero.webp" type="image/webp">
      <img 
        src="/images/hero.jpg"
        alt="Hero image description"
        width="1920"
        height="1080"
        loading="eager"           <!-- NEVER lazy for LCP -->
        fetchpriority="high"      <!-- Prioritize this image -->
        decoding="async"
      >
    </picture>
    <h1>Headline</h1>
  </section>
</body>
```

### LCP Heading Optimization

If the LCP is a text element (heading), optimize the font:

```html
<head>
  <!-- Preload the EXACT font file used in the heading -->
  <link 
    rel="preload" 
    href="/fonts/display-bold.woff2" 
    as="font" 
    type="font/woff2" 
    crossorigin
  >
  
  <!-- Inline critical CSS for the heading -->
  <style>
    .hero h1 {
      font-family: 'Display Font', Georgia, serif;
      font-size: clamp(2.5rem, 6vw, 5rem);
      font-weight: 700;
      /* Inline just enough CSS to render the heading */
    }
  </style>
</head>
```

### LCP Background Image (Avoid if Possible)

**Warning**: CSS `background-image` cannot be preloaded and is discovered late. Convert to `<img>` when possible.

If you must use background-image:

```html
<head>
  <!-- Preload background image -->
  <link rel="preload" href="/images/hero-bg.webp" as="image">
</head>

<body>
  <!-- Add a hidden img for browser to discover early -->
  <img 
    src="/images/hero-bg.webp" 
    alt="" 
    aria-hidden="true"
    style="position: absolute; width: 1px; height: 1px; opacity: 0; pointer-events: none;"
    fetchpriority="high"
    loading="eager"
  >
  
  <section class="hero" style="background-image: url(/images/hero-bg.webp)">
    <!-- content -->
  </section>
</body>
```

### LCP Detection Script (Development Only)

```javascript
// Add to page during development to identify LCP element
if (process.env.NODE_ENV === 'development') {
  new PerformanceObserver((entryList) => {
    const entries = entryList.getEntries();
    const lastEntry = entries[entries.length - 1];
    
    console.log('🎯 LCP Element:', lastEntry.element);
    console.log('⏱️ LCP Time:', lastEntry.startTime.toFixed(0) + 'ms');
    console.log('📐 LCP Size:', lastEntry.size);
    
    // Highlight the LCP element
    if (lastEntry.element) {
      lastEntry.element.style.outline = '3px dashed red';
      lastEntry.element.setAttribute('data-lcp', 'true');
    }
  }).observe({ type: 'largest-contentful-paint', buffered: true });
}
```

### AI Self-Check for LCP

Before outputting any page, the AI must verify:

```
□ Identified the LCP element (image or heading)
□ LCP image has fetchpriority="high" and loading="eager"
□ LCP image has preload link in <head>
□ Preload link appears BEFORE stylesheets
□ LCP is NOT lazy-loaded
□ LCP is NOT a CSS background-image (or has workaround)
□ LCP image has width/height to prevent CLS
□ If LCP is text: font is preloaded, critical CSS is inlined
```
