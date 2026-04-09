# UI/UX Motion, Animation & Loading States 2026

> Per-language animation systems, GSAP vs CSS decision rules, spring physics,
> kinetic typography, skeleton UX, loading states, view transitions, performance tiers.

---

## THE GOLDEN RULE — Performance First

**Only ever animate `transform` and `opacity`.** These are GPU-composited — zero layout
reflow, zero paint, zero jank. Everything else causes dropped frames.

```
S-Tier (GPU, 60fps): transform, opacity
C-Tier (use sparingly): filter, clip-path, border-radius
D-Tier (NEVER animate): width, height, top, left, padding, margin, background-color*
*background-color is fine for micro-interactions but not continuous/looping animations
```

---

## DECISION TREE — CSS vs GSAP vs Motion

```
Need animation?
├─ Simple state (hover, focus, show/hide, toggle)?
│   └─ CSS transition — always. Zero overhead.
├─ Entrance on scroll?
│   ├─ Vanilla → AOS (in CDN) or CSS scroll-timeline
│   └─ React → Motion whileInView
├─ Complex sequence / stagger / timeline?
│   └─ GSAP timeline
├─ height:auto expand/collapse (accordion)?
│   └─ GSAP — CSS literally cannot do height:auto
├─ Scroll cinematic (parallax, pin, scrub)?
│   └─ GSAP + ScrollTrigger
├─ React page/route transition?
│   └─ View Transitions API or Motion AnimatePresence
└─ Physics spring (bouncy, drag)?
    └─ CSS spring easing or Motion (React)
```

---

## PER-LANGUAGE ANIMATION SYSTEM

### Token Values by Language

| Token | Luxury | Premium | Minimalist | Expressive | Warm | Technical |
|-------|--------|---------|------------|------------|------|-----------|
| `--dur-micro` | 150ms | 100ms | 80ms | 60ms | 120ms | 60ms |
| `--dur-base` | 500ms | 300ms | 250ms | 180ms | 350ms | 150ms |
| `--dur-slow` | 900ms | 600ms | 500ms | 300ms | 700ms | 250ms |
| Spring/bounce? | Never | Subtle | Never | Strong | Medium | Never |
| Stagger delay | 120ms | 60ms | 80ms | 40ms | 80ms | 20ms |
| Entrance travel | 32px | 20px | 12px | 16px | 20px | 8px |
| Entrance blur | 0px | 0px | 0px | 8px | 0px | 0px |
| Kinetic type? | Slow reveal | Subtle | No | Fast scramble | No | No |

### Language CSS Animation Tokens

```css
/* LUXURY — slow, deliberate, cinematic. Never bounces. */
:root[data-lang="luxury"] {
  --dur-micro:   150ms; --dur-base: 500ms; --dur-slow: 900ms;
  --ease-enter:  cubic-bezier(0.25, 0.1, 0, 1);
  --ease-exit:   cubic-bezier(0.4, 0, 1, 1);
  --ease-spring: cubic-bezier(0.25, 0.1, 0, 1); /* no overshoot */
  --stagger: 120ms; --entrance-y: 32px; --entrance-blur: 0px;
}

/* PREMIUM — polished, confident, instant feedback */
:root[data-lang="premium"] {
  --dur-micro:   100ms; --dur-base: 300ms; --dur-slow: 600ms;
  --ease-enter:  cubic-bezier(0.16, 1, 0.3, 1);  /* expo out */
  --ease-exit:   cubic-bezier(0.4, 0, 1, 1);
  --ease-spring: cubic-bezier(0.34, 1.2, 0.64, 1); /* gentle spring */
  --stagger: 60ms; --entrance-y: 20px; --entrance-blur: 0px;
}

/* MINIMALIST — crisp, sharp, zero decoration */
:root[data-lang="minimal"] {
  --dur-micro:   80ms; --dur-base: 250ms; --dur-slow: 500ms;
  --ease-enter:  cubic-bezier(0.4, 0, 0.2, 1);
  --ease-exit:   cubic-bezier(0.4, 0, 0.6, 1);
  --ease-spring: cubic-bezier(0.4, 0, 0.2, 1); /* no spring */
  --stagger: 80ms; --entrance-y: 12px; --entrance-blur: 0px;
}

/* EXPRESSIVE — fast, bouncy, energetic. Overshoot is intentional. */
:root[data-lang="expressive"] {
  --dur-micro:   60ms; --dur-base: 180ms; --dur-slow: 300ms;
  --ease-enter:  cubic-bezier(0.34, 1.56, 0.64, 1); /* spring overshoot */
  --ease-exit:   cubic-bezier(0.4, 0, 0.6, 1);
  --ease-spring: cubic-bezier(0.34, 1.7, 0.64, 1);  /* aggressive spring */
  --stagger: 40ms; --entrance-y: 16px; --entrance-blur: 8px;
}

/* WARM — springy, gentle, friendly */
:root[data-lang="warm"] {
  --dur-micro:   120ms; --dur-base: 350ms; --dur-slow: 700ms;
  --ease-enter:  cubic-bezier(0.34, 1.4, 0.64, 1); /* gentle spring */
  --ease-exit:   cubic-bezier(0.4, 0, 0.6, 1);
  --ease-spring: cubic-bezier(0.34, 1.4, 0.64, 1);
  --stagger: 80ms; --entrance-y: 20px; --entrance-blur: 0px;
}

/* TECHNICAL — instant, functional, zero decoration */
:root[data-lang="technical"] {
  --dur-micro:   60ms; --dur-base: 150ms; --dur-slow: 250ms;
  --ease-enter:  cubic-bezier(0.4, 0, 0.2, 1);
  --ease-exit:   cubic-bezier(0.4, 0, 0.6, 1);
  --ease-spring: cubic-bezier(0.4, 0, 0.2, 1); /* no spring */
  --stagger: 20ms; --entrance-y: 8px; --entrance-blur: 0px;
}
```

---

## MICRO-INTERACTIONS (CSS — always use for state changes)

```css
/* Button — universal */
.btn {
  transition:
    background-color var(--dur-micro) var(--ease-enter),
    border-color     var(--dur-micro) var(--ease-enter),
    color            var(--dur-micro) var(--ease-enter),
    transform        var(--dur-base)  var(--ease-spring),
    box-shadow       var(--dur-base)  var(--ease-enter);
}
.btn:hover  { transform: translateY(-2px); box-shadow: var(--shadow-md); }
.btn:active { transform: translateY(0) scale(0.97); transition-duration: 50ms; }
/* Luxury variant: translate only, no scale */
.btn-luxury:hover { transform: translateY(-2px); }
/* Technical variant: no lift */
.btn-technical:hover { transform: none; box-shadow: none; }

/* Card hover */
.card {
  transition:
    transform  var(--dur-base) var(--ease-spring),
    box-shadow var(--dur-base) var(--ease-enter);
}
.card:hover { transform: translateY(-6px) scale(1.01); box-shadow: var(--shadow-xl); }
.card-luxury:hover  { transform: translateY(-4px); } /* no scale */
.card-technical:hover { transform: none; background: var(--surface-up); }

/* Icon rotation (chevron, arrow) */
.icon-rotate { transition: transform var(--dur-base) var(--ease-spring); }
.icon-rotate.open { transform: rotate(180deg); }

/* Ripple (Warm / Expressive buttons) */
.btn-ripple { position: relative; overflow: hidden; }
.btn-ripple::after {
  content: ''; position: absolute; border-radius: 50%;
  background: rgba(255,255,255,0.22);
  width: 0; height: 0; top: 50%; left: 50%;
  transform: translate(-50%,-50%);
  transition: width 0.6s ease, height 0.6s ease, opacity 0.6s ease;
  opacity: 0;
}
.btn-ripple:active::after { width: 300px; height: 300px; opacity: 0; }

/* Focus ring animated in */
:focus-visible {
  outline: 2px solid var(--accent); outline-offset: 3px;
  animation: focus-in var(--dur-base) var(--ease-spring);
}
@keyframes focus-in {
  from { outline-offset: 0px; opacity: 0; }
  to   { outline-offset: 3px; opacity: 1; }
}
```

---

## ENTRANCE ANIMATIONS (CSS — scroll-triggered)

```css
/* Base state — pre-animation */
.enter {
  opacity: 0;
  transform: translateY(var(--entrance-y, 20px));
  filter: blur(var(--entrance-blur, 0px));
}
/* Active state — post trigger */
.enter.visible {
  opacity: 1; transform: translateY(0); filter: blur(0);
  transition:
    opacity   var(--dur-slow) var(--ease-enter),
    transform var(--dur-slow) var(--ease-enter),
    filter    var(--dur-slow) var(--ease-enter);
}
/* Stagger via CSS counter */
.stagger-parent .enter:nth-child(1) { transition-delay: calc(var(--stagger,60ms) * 0); }
.stagger-parent .enter:nth-child(2) { transition-delay: calc(var(--stagger,60ms) * 1); }
.stagger-parent .enter:nth-child(3) { transition-delay: calc(var(--stagger,60ms) * 2); }
.stagger-parent .enter:nth-child(4) { transition-delay: calc(var(--stagger,60ms) * 3); }
.stagger-parent .enter:nth-child(5) { transition-delay: calc(var(--stagger,60ms) * 4); }
.stagger-parent .enter:nth-child(6) { transition-delay: calc(var(--stagger,60ms) * 5); }
/* Or via JS: set style="--i:3" and use: transition-delay: calc(var(--stagger) * var(--i,0)) */
```
```javascript
/* IntersectionObserver — trigger .visible once on scroll */
const io = new IntersectionObserver(entries => {
  entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); io.unobserve(e.target); } });
}, { threshold: 0.12, rootMargin: '0px 0px -32px 0px' });
document.querySelectorAll('.enter, [data-enter]').forEach(el => io.observe(el));
```

---

## GSAP PATTERNS (for complex sequences, timelines, accordions)

```javascript
gsap.registerPlugin(ScrollTrigger);

// Hero entrance sequence
const tl = gsap.timeline({ defaults: { ease: 'power3.out' } });
tl.from('.hero-eyebrow', { y: 20, opacity: 0, duration: 0.6 })
  .from('.hero-title',   { y: 40, opacity: 0, duration: 0.9 }, '-=0.3')
  .from('.hero-body',    { y: 20, opacity: 0, duration: 0.6 }, '-=0.5')
  .from('.hero-cta',     { y: 20, opacity: 0, duration: 0.5 }, '-=0.3')
  .from('.hero-image',   { scale: 1.08, opacity: 0, duration: 1.2, ease: 'power2.out' }, '-=0.8');

// Stagger cards on scroll
gsap.from('.card', {
  scrollTrigger: { trigger: '.card-grid', start: 'top 80%', once: true },
  y: 40, opacity: 0, duration: 0.6, stagger: 0.1, ease: 'power3.out',
});

// Accordion — height:auto (CSS cannot do this)
function toggleAccordion(panel, open) {
  gsap.to(panel, {
    height: open ? 'auto' : 0,
    opacity: open ? 1 : 0,
    duration: 0.4,
    ease: open ? 'power3.out' : 'power2.in',
  });
}

// Number counter
gsap.from('[data-counter]', {
  textContent: 0,
  duration: 2,
  ease: 'power2.out',
  snap: { textContent: 1 },
  stagger: 0.15,
  scrollTrigger: { trigger: '[data-counter]', start: 'top 80%', once: true },
});

// GSAP ease guide per language:
// Luxury:     power2.inOut, power3.out         (never elastic/bounce)
// Premium:    power3.out, back.out(1.2)        (subtle overshoot ok)
// Expressive: elastic.out(1,0.4), back.out(2)  (strong spring)
// Warm:       back.out(1.4), power2.inOut      (gentle spring)
// Technical:  power1.inOut, linear            (functional only)
// Minimalist: power2.inOut                     (never elastic)
```

---

## KINETIC TYPOGRAPHY (Luxury + Expressive only)

```javascript
// Split title letters for per-character animation
function splitKinetic(el) {
  el.innerHTML = [...el.textContent].map(c =>
    c === ' '
      ? '<span style="display:inline-block;width:0.3em"></span>'
      : `<span style="display:inline-block;overflow:hidden">
           <span style="display:inline-block">${c}</span></span>`
  ).join('');
  return el.querySelectorAll('span > span');
}
document.querySelectorAll('[data-kinetic]').forEach(el => {
  const chars = splitKinetic(el);
  const lang  = document.documentElement.dataset.lang;
  gsap.from(chars, {
    yPercent: lang === 'expressive' ? 100 : 110,
    opacity:  lang === 'expressive' ? 0 : undefined,
    rotateX:  lang === 'expressive' ? -90 : undefined,
    duration: lang === 'luxury'     ? 0.9 : 0.4,
    stagger:  lang === 'luxury'     ? 0.04 : 0.025,
    ease:     lang === 'luxury'     ? 'power3.out' : 'back.out(2)',
    scrollTrigger: { trigger: el, start: 'top 85%', once: true },
  });
});
```

---

## VIEW TRANSITIONS

```javascript
async function transitionTo(fn) {
  if (!document.startViewTransition) { fn(); return; }
  await document.startViewTransition(fn).finished;
}
// Usage: transitionTo(() => modal.classList.add('open'));
```

```css
/* Language-specific transition animations */
[data-lang="luxury"]  ::view-transition-old(root) { animation: 600ms ease-in  both vt-fade-out; }
[data-lang="luxury"]  ::view-transition-new(root) { animation: 600ms ease-out both vt-fade-in; }
[data-lang="premium"] ::view-transition-old(root) { animation: 300ms ease-in  both vt-slide-out; }
[data-lang="premium"] ::view-transition-new(root) { animation: 300ms ease-out both vt-slide-in; }
[data-lang="expressive"] ::view-transition-old(root) { animation: 200ms ease-in  both vt-exit-l; }
[data-lang="expressive"] ::view-transition-new(root) { animation: 200ms ease-out both vt-enter-r; }
[data-lang="technical"]  ::view-transition-old(root) { animation: 80ms linear both vt-fade-out; }
[data-lang="technical"]  ::view-transition-new(root) { animation: 80ms linear both vt-fade-in; }

@keyframes vt-fade-out  { to   { opacity: 0; } }
@keyframes vt-fade-in   { from { opacity: 0; } }
@keyframes vt-slide-out { to   { opacity: 0; transform: translateY(-10px); } }
@keyframes vt-slide-in  { from { opacity: 0; transform: translateY(10px); } }
@keyframes vt-exit-l    { to   { transform: translateX(-20px); opacity: 0; } }
@keyframes vt-enter-r   { from { transform: translateX(20px); opacity: 0; } }
```

---

## SCROLL-DRIVEN ANIMATIONS (Native CSS)

```css
@supports (animation-timeline: scroll()) {
  /* Fade in on scroll — use only transform+opacity (S-Tier) */
  .scroll-fade {
    animation: scroll-reveal linear both;
    animation-timeline: view();
    animation-range: entry 5% entry 35%;
  }
  @keyframes scroll-reveal {
    from { opacity: 0; transform: translateY(var(--entrance-y, 24px)); }
    to   { opacity: 1; transform: translateY(0); }
  }

  /* Sticky header shrink */
  header {
    animation: header-compact linear both;
    animation-timeline: scroll(root block);
    animation-range: 0px 100px;
  }
  @keyframes header-compact {
    from { padding-block: 20px; box-shadow: none; background: transparent; }
    to   { padding-block: 10px; box-shadow: var(--shadow-md); background: var(--surface); }
  }

  /* Reading progress bar */
  .read-progress {
    position: fixed; top: 0; left: 0; height: 3px;
    background: var(--accent); transform-origin: left;
    animation: read-fill linear both;
    animation-timeline: scroll(root block);
  }
  @keyframes read-fill { from { transform: scaleX(0); } to { transform: scaleX(1); } }
}
```

---

## LOADING & SKELETON

### Skeleton animations per language

```css
/* SHIMMER — Premium, Technical */
@keyframes shimmer {
  0%   { background-position: -200% 0; }
  100% { background-position:  200% 0; }
}
.skeleton {
  background: linear-gradient(
    90deg, var(--surface) 25%, var(--surface-up) 50%, var(--surface) 75%
  );
  background-size: 200% 100%;
  animation: shimmer 1.4s ease-in-out infinite;
  border-radius: var(--radius-sm);
}

/* PULSE — Luxury, Warm, Minimal */
@keyframes pulse { 0%,100% { opacity:1; } 50% { opacity:0.35; } }
.skeleton-pulse {
  background: var(--surface-up);
  animation: pulse 1.8s ease-in-out infinite;
  border-radius: var(--radius-md);
}
[data-lang="luxury"] .skeleton-pulse { animation-duration: 2.8s; }

/* WAVE — Expressive (branded color wash) */
@keyframes wave { 0% { background-position: -400px 0; } 100% { background-position: 400px 0; } }
.skeleton-wave {
  background: linear-gradient(90deg,
    var(--surface) 0%,
    color-mix(in srgb, var(--accent) 15%, var(--surface)) 50%,
    var(--surface) 100%);
  background-size: 800px 100%;
  animation: wave 1.2s ease-in-out infinite;
  border-radius: var(--radius-sm);
}
```

### Use right skeleton per language

| Language | Skeleton type | Duration |
|----------|--------------|---------|
| Luxury | `skeleton-pulse` | 2.8s slow |
| Premium | `skeleton` (shimmer) | 1.4s |
| Minimalist | `skeleton-pulse` | 1.8s |
| Expressive | `skeleton-wave` | 1.2s fast |
| Warm | `skeleton-pulse` | 1.8s |
| Technical | `skeleton` (shimmer) | 1.2s fast |

### Button loading state

```css
.btn--loading {
  position: relative; color: transparent !important; pointer-events: none;
}
.btn--loading::after {
  content: ''; position: absolute; inset: 0; margin: auto;
  width: 18px; height: 18px;
  border: 2px solid rgba(255,255,255,0.3); border-top-color: white;
  border-radius: 50%;
  animation: btn-spin var(--dur-base) linear infinite;
}
@keyframes btn-spin { to { transform: rotate(360deg); } }
```

### Top progress bar

```javascript
const bar = document.getElementById('top-progress');
const progress = {
  start() { bar.style.cssText = 'width:30%;opacity:1;transition:width 0.3s'; },
  done()  { bar.style.width='100%'; setTimeout(()=>{ bar.style.opacity='0'; bar.style.width='0%'; }, 400); },
};
```

---

## REDUCED MOTION — MANDATORY

```css
@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after {
    animation-duration:        0.01ms !important;
    animation-iteration-count: 1      !important;
    transition-duration:       0.01ms !important;
    scroll-behavior:           auto   !important;
  }
  .marquee__track { animation: none !important; }
  .skeleton, .skeleton-wave {
    animation: none !important;
    background: var(--surface-up) !important;
  }
}
```

---

## BROWSER SUPPORT NOTES — Scroll-Driven Animations

### Safari Support (Critical Warning)

```css
/* Scroll-driven animations (animation-timeline: scroll()) */
/* Status as of 2026: */
/* Chrome 115+  ✅ Supported */
/* Firefox 110+ ✅ Supported */
/* Safari 18+   ⚠️  Partial — behind Experimental Features flag in Safari 17 */
/*                   animation-timeline: view() = YES in Safari 18 */
/*                   animation-timeline: scroll() = YES in Safari 18 */
/*                   Named timelines (scroll(root)) = partial */
```

**Rule**: Always wrap scroll-driven animations in `@supports` with a JS IntersectionObserver fallback.

```css
/* ✅ Correct pattern — progressive enhancement */
.scroll-fade {
  /* FALLBACK: static — visible by default (no JS needed) */
  opacity: 1;
  transform: translateY(0);
}

/* Only apply scroll-driven animation if browser supports it */
@supports (animation-timeline: view()) {
  .scroll-fade {
    opacity: 0;
    transform: translateY(var(--entrance-y, 24px));
    animation: scroll-reveal linear both;
    animation-timeline: view();
    animation-range: entry 5% entry 35%;
  }
  @keyframes scroll-reveal {
    to { opacity: 1; transform: translateY(0); }
  }
}

/* Also guard: header compact on scroll */
@supports (animation-timeline: scroll()) {
  header {
    animation: header-compact linear both;
    animation-timeline: scroll(root block);
    animation-range: 0px 100px;
  }
}
/* Fallback: JS scroll listener handles header state (already in uiux-interactive.md navbar) */
```

```javascript
// JS fallback for scroll-driven animations in unsupported browsers
// Detects support and uses IntersectionObserver when scroll-timeline not available
const supportsScrollTimeline = CSS.supports('animation-timeline', 'view()');

if (!supportsScrollTimeline) {
  // Use IntersectionObserver (the existing .enter + .visible system from this file)
  const io = new IntersectionObserver(entries => {
    entries.forEach(e => {
      if (e.isIntersecting) { e.target.classList.add('visible'); io.unobserve(e.target); }
    });
  }, { threshold: 0.12 });
  document.querySelectorAll('.scroll-fade, .enter, [data-enter]').forEach(el => io.observe(el));
}
// When scroll-timeline IS supported, the CSS @supports block handles it,
// and the JS observer simply finds nothing with .enter (since .scroll-fade
// was handled by CSS). Both paths work independently.
```

### View Transitions API — Safari

```
Chrome 111+  ✅ Single-page transitions
Firefox      ✅ FF 126+
Safari 18+   ✅ Basic support
Cross-document view transitions: Chrome 126+ only
```

```javascript
// Guard: always check before calling
async function transitionTo(fn) {
  if (!document.startViewTransition) {
    // Fallback: just run the function, no transition
    fn();
    return;
  }
  await document.startViewTransition(fn).finished;
}
```

### Quick Browser Support Matrix (2026)

| Feature | Chrome | Firefox | Safari | Use @supports? |
|---------|--------|---------|--------|---------------|
| `animation-timeline: scroll()` | 115+ ✅ | 110+ ✅ | 18+ ✅ | Yes — still patchy |
| `animation-timeline: view()` | 115+ ✅ | 110+ ✅ | 18+ ✅ | Yes |
| Named scroll timelines | 115+ ✅ | 110+ ✅ | Partial ⚠️ | Yes |
| `@starting-style` | 117+ ✅ | 129+ ✅ | 17.5+ ✅ | Optional |
| View Transitions API | 111+ ✅ | 126+ ✅ | 18+ ✅ | Yes |
| `prefers-reduced-motion` | All ✅ | All ✅ | All ✅ | No |

