---
description: >
  Automated Lighthouse performance audit skill. Load when the user asks for a
  performance audit, Lighthouse score, Core Web Vitals check, or "before ship"
  performance review. Uses Chrome DevTools MCP (or Lighthouse CLI) to run a full
  audit, capture CWV metrics, and output a prioritised fix plan. Load alongside
  uiux-performance.md for remediation patterns.
---

# Performance Auditor 2026

> Run → capture → score → fix.
> Automates Lighthouse audits via MCP (headless Chrome), reports CWV,
> and maps every failing audit to a concrete, token-aware fix.

---

## CORE WEB VITALS TARGETS (2026)

| Metric | Good | Needs Improvement | Poor |
|--------|------|------------------|------|
| **LCP** Largest Contentful Paint | ≤ 2.5 s | 2.5–4.0 s | > 4.0 s |
| **INP** Interaction to Next Paint | ≤ 200 ms | 200–500 ms | > 500 ms |
| **CLS** Cumulative Layout Shift | ≤ 0.1 | 0.1–0.25 | > 0.25 |
| **FCP** First Contentful Paint | ≤ 1.8 s | 1.8–3.0 s | > 3.0 s |
| **TTFB** Time to First Byte | ≤ 800 ms | 800–1800 ms | > 1800 ms |
| **TBT** Total Blocking Time | ≤ 200 ms | 200–600 ms | > 600 ms |

**Score targets:**
- Performance: ≥ 90
- Accessibility: ≥ 95
- Best Practices: ≥ 95
- SEO: ≥ 90

---

## STEP 1 — CHOOSE AUDIT METHOD

### Method A — Chrome DevTools MCP (preferred for local dev)

```javascript
// Navigate to target
await browser_navigate({ url: TARGET_URL });

// Wait for page to fully load
await browser_evaluate({ script: `
  await new Promise(resolve => {
    if (document.readyState === 'complete') return resolve();
    window.addEventListener('load', resolve);
  });
  await new Promise(r => setTimeout(r, 1000));
` });

// Collect Web Vitals via PerformanceObserver
const vitals = await browser_evaluate({ script: `
  const nav = performance.getEntriesByType('navigation')[0];
  const paint = performance.getEntriesByType('paint');
  const lcp  = await new Promise(res => {
    new PerformanceObserver(list => {
      const entries = list.getEntries();
      res(entries[entries.length - 1].startTime);
    }).observe({ type: 'largest-contentful-paint', buffered: true });
    setTimeout(() => res(null), 3000);
  });
  return {
    TTFB:  nav?.responseStart - nav?.requestStart,
    FCP:   paint.find(e => e.name === 'first-contentful-paint')?.startTime,
    LCP:   lcp,
    TBT:   nav?.domComplete - nav?.domInteractive,
    domSize: document.querySelectorAll('*').length,
    scripts: performance.getEntriesByType('resource')
             .filter(r => r.initiatorType === 'script')
             .map(r => ({ url: r.name.split('/').pop(), size: r.transferSize, duration: r.duration }))
             .sort((a,b) => b.size - a.size)
             .slice(0, 10),
    images:  performance.getEntriesByType('resource')
             .filter(r => r.initiatorType === 'img')
             .map(r => ({ url: r.name.split('/').pop(), size: r.transferSize }))
             .sort((a,b) => b.size - a.size)
             .slice(0, 10),
    thirdParty: performance.getEntriesByType('resource')
             .filter(r => !r.name.includes(location.hostname))
             .reduce((acc, r) => acc + r.transferSize, 0),
  };
` });
```

### Method B — Lighthouse CLI (for CI / terminal use)

```bash
# Install once
npm install -g lighthouse

# Run audit (mobile — default, mimics real-user conditions)
lighthouse ${TARGET_URL} \
  --output=json \
  --output-path=./lighthouse-report.json \
  --chrome-flags="--headless --no-sandbox" \
  --preset=desktop  # remove for mobile

# Parse key metrics
node -e "
  const r = require('./lighthouse-report.json');
  const c = r.categories;
  const a = r.audits;
  console.log({
    scores: { perf: c.performance.score*100, a11y: c.accessibility.score*100, bp: c['best-practices'].score*100, seo: c.seo.score*100 },
    lcp:    a['largest-contentful-paint'].displayValue,
    inp:    a['interaction-to-next-paint']?.displayValue,
    cls:    a['cumulative-layout-shift'].displayValue,
    fcp:    a['first-contentful-paint'].displayValue,
    tbt:    a['total-blocking-time'].displayValue,
    ttfb:   a['server-response-time'].displayValue,
    opportunities: r.categories.performance.auditRefs
      .filter(ref => ref.group === 'load-opportunities')
      .map(ref => a[ref.id])
      .filter(a => a.score < 0.9)
      .map(a => ({ id: a.id, title: a.title, savings: a.displayValue })),
  });
"
```

---

## STEP 2 — GENERATE THE PERFORMANCE REPORT

```markdown
# Lighthouse Performance Report

**URL:** {url}
**Date:** {timestamp}
**Conditions:** Mobile 4G throttling / Desktop (state which)

---

## Scores

| Category       | Score | Status |
|----------------|-------|--------|
| Performance    | {n}   | ✅ / ⚠️ / ❌ |
| Accessibility  | {n}   | ✅ / ⚠️ / ❌ |
| Best Practices | {n}   | ✅ / ⚠️ / ❌ |
| SEO            | {n}   | ✅ / ⚠️ / ❌ |

---

## Core Web Vitals

| Metric | Value | Target | Status |
|--------|-------|--------|--------|
| LCP    | {v}   | ≤ 2.5s | ✅/⚠️/❌ |
| INP    | {v}   | ≤ 200ms | ✅/⚠️/❌ |
| CLS    | {v}   | ≤ 0.1  | ✅/⚠️/❌ |
| FCP    | {v}   | ≤ 1.8s | ✅/⚠️/❌ |
| TTFB   | {v}   | ≤ 800ms | ✅/⚠️/❌ |
| TBT    | {v}   | ≤ 200ms | ✅/⚠️/❌ |

---

## Top Opportunities (by estimated savings)

{ordered list of failing audits with savings}

---

## Diagnostics

- DOM size: {n} nodes (target < 1500)
- Third-party bytes: {n} KB
- Render-blocking resources: {list}
- Unused JS: {n} KB
- Unused CSS: {n} KB
- Largest scripts: {table}
- Unoptimised images: {table}
```

---

## STEP 3 — FIX LIBRARY (Mapped to Audit IDs)

### `largest-contentful-paint` / LCP

```html
<!-- ❌ LCP image discovered late, no priority hint -->
<img src="/hero.jpg" alt="Hero">

<!-- ✅ -->
<picture>
  <source srcset="/hero.avif" type="image/avif">
  <source srcset="/hero.webp" type="image/webp">
  <img src="/hero.jpg" alt="Hero scene"
    width="1920" height="1080"
    loading="eager"
    fetchpriority="high"
    decoding="sync">
</picture>
```

```html
<!-- Preload LCP image in <head> -->
<link rel="preload" as="image"
  href="/hero.avif"
  imagesrcset="/hero-480.avif 480w, /hero-1280.avif 1280w, /hero-1920.avif 1920w"
  imagesizes="100vw"
  fetchpriority="high">
```

### `cumulative-layout-shift` / CLS

```css
/* ❌ No dimensions → shifts as image loads */
img { max-width: 100%; }

/* ✅ Always reserve space */
img { width: 100%; height: auto; aspect-ratio: attr(width) / attr(height); }

/* ❌ Web font causes FOUT shift */
/* ✅ font-display: optional (prevents layout shift) */
@font-face {
  font-family: 'BrandFont';
  src: url('/fonts/brand.woff2') format('woff2');
  font-display: optional; /* or swap with size-adjust fallback */
}

/* ❌ Ad / embed placeholder collapses then expands */
.ad-slot { min-height: 250px; } /* ✅ reserve minimum height */
```

### `total-blocking-time` / TBT & INP

```html
<!-- ❌ Render-blocking script in <head> -->
<script src="/analytics.js"></script>

<!-- ✅ Defer non-critical scripts -->
<script src="/analytics.js" defer></script>
<script type="module" src="/app.js"></script>   <!-- modules are always deferred -->

<!-- ✅ Lazy-load heavy components (React) -->
```
```javascript
const HeavyChart = React.lazy(() => import('./HeavyChart'));
// Wrap in <Suspense fallback={<SkeletonChart />}>

// ✅ Long tasks — use scheduler
function processLargeList(items) {
  const CHUNK = 50;
  let i = 0;
  function tick() {
    const end = Math.min(i + CHUNK, items.length);
    while (i < end) processItem(items[i++]);
    if (i < items.length) scheduler.postTask(tick, { priority: 'background' });
  }
  scheduler.postTask(tick, { priority: 'background' });
}
```

### `render-blocking-resources`

```html
<!-- ✅ Critical CSS: inline above-the-fold styles -->
<style>
  /* Paste output of critical-css extractor here */
  :root { --bg: #fff; --text: #111; }
  body { font-family: var(--font-body); background: var(--bg); color: var(--text); }
  .hero { min-height: 100svh; display: flex; align-items: center; }
</style>

<!-- Load full stylesheet asynchronously -->
<link rel="stylesheet" href="/styles.css" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="/styles.css"></noscript>
```

### `uses-optimized-images`

```bash
# Convert to AVIF + WebP using sharp (Node.js build step)
const sharp = require('sharp');
await sharp('src.jpg')
  .avif({ quality: 65, effort: 6 })
  .toFile('output.avif');
await sharp('src.jpg')
  .webp({ quality: 75 })
  .toFile('output.webp');
```

### `unused-javascript`

```javascript
// ✅ Tree-shake: named imports only
import { format } from 'date-fns';           // ✅ — only format included
import _ from 'lodash';                      // ❌ — whole lodash included
import { debounce } from 'lodash-es';        // ✅ — ES module, tree-shakeable

// ✅ Dynamic import for route-level code splitting
const AdminPanel = () => import('./AdminPanel'); // loaded only when needed
```

### `server-response-time` / TTFB

```
Recommendations (choose based on stack):
- Add CDN (Cloudflare, Vercel Edge, AWS CloudFront)
- Enable HTTP/2 or HTTP/3
- Use stale-while-revalidate caching headers
- Move to SSG or ISR for mostly-static pages
- Add Redis / memory cache for DB-heavy endpoints
```

---

## STEP 4 — CI INTEGRATION (Lighthouse CI)

```yaml
# .github/workflows/lighthouse.yml
name: Lighthouse CI
on: [pull_request]
jobs:
  lighthouse:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-node@v4
        with: { node-version: '20' }
      - run: npm ci && npm run build
      - run: npm run start &
      - run: npx @lhci/cli@0.14 autorun
        env:
          LHCI_GITHUB_APP_TOKEN: ${{ secrets.LHCI_GITHUB_APP_TOKEN }}
```

```javascript
// lighthouserc.js
module.exports = {
  ci: {
    collect: { url: ['http://localhost:3000/', 'http://localhost:3000/about'] },
    assert: {
      assertions: {
        'categories:performance':    ['warn', { minScore: 0.90 }],
        'categories:accessibility':  ['error', { minScore: 0.95 }],
        'categories:best-practices': ['warn', { minScore: 0.95 }],
        'cumulative-layout-shift':   ['error', { maxNumericValue: 0.1 }],
        'largest-contentful-paint':  ['warn', { maxNumericValue: 2500 }],
        'total-blocking-time':       ['warn', { maxNumericValue: 200 }],
      },
    },
    upload: { target: 'temporary-public-storage' },
  },
};
```

---

## AUDIT SCHEDULE

| Phase | Action | Gate |
|-------|--------|------|
| Development | `npm run build && lighthouse local` | Performance ≥ 80 |
| Pre-PR | Lighthouse CI on PR | Performance ≥ 90, a11y ≥ 95 |
| Pre-launch | Full audit (mobile + desktop) | All CWV green |
| Post-launch | CrUX data via PageSpeed Insights | LCP p75 ≤ 2.5 s |
| Monthly | Trend monitoring via Lighthouse CI dashboard | No regression |
