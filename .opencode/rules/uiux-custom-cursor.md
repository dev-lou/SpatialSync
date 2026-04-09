# UI/UX Custom Cursor 2026

> Custom cursors, magnetic buttons, and cursor-based interactions for Luxury and
> Expressive design languages. Polish that makes a site feel hand-crafted.
> Never use on Technical or Warm systems — cursor tricks frustrate data-heavy UIs.

---

## WHEN TO USE CUSTOM CURSORS

| Language | Custom cursor? | Type |
|----------|--------------|------|
| **Luxury** | ✅ Yes | Minimal dot + border ring |
| **Expressive** | ✅ Yes | Vivid blob, color-shift, large |
| **Editorial** | Optional | Subtle crosshair or line cursor |
| **Minimalist** | Optional | Tiny dot only, very subtle |
| **Premium Modern** | ❌ No | Functional — don't distract |
| **Warm & Human** | ❌ No | Standard cursor = approachable |
| **Technical** | ❌ No | Standard cursor = productivity |

**Rules:**
- Always preserve the default cursor for interactive elements (links, buttons) — override back to `pointer`
- Never on mobile/touch — `@media (pointer: fine)` guards everything
- Never when `prefers-reduced-motion: reduce` — hide custom cursor, show default
- Custom cursor hides the native cursor with `cursor: none` on `<body>`

---

## VARIANT 1 — LUXURY (Dot + Ring)

Tiny filled dot that moves exactly. Larger ring that follows with lag.
Minimal, elegant, barely noticeable — that's the point.

```html
<!-- Place directly after <body> opens -->
<div id="cursor-dot"  class="cursor-dot"  aria-hidden="true"></div>
<div id="cursor-ring" class="cursor-ring" aria-hidden="true"></div>
```

```css
@media (pointer: fine) {
  /* Hide native cursor everywhere */
  * { cursor: none !important; }

  /* Restore pointer cursor on interactive elements
     (still cursor: none — but cursor-ring handles the visual) */
  a, button, [role="button"], label, select,
  input, textarea, .clickable { cursor: none !important; }

  .cursor-dot {
    position: fixed; top: 0; left: 0; z-index: 99999;
    width: 6px; height: 6px; border-radius: 50%;
    background: var(--text);
    pointer-events: none;
    transform: translate(-50%, -50%);
    transition: transform 0.1s, background 0.2s, width 0.2s, height 0.2s;
    will-change: transform;
  }

  .cursor-ring {
    position: fixed; top: 0; left: 0; z-index: 99998;
    width: 36px; height: 36px; border-radius: 50%;
    border: 1px solid var(--text);
    pointer-events: none;
    transform: translate(-50%, -50%);
    opacity: 0.5;
    transition: transform 0.08s, width 0.3s var(--ease-luxury),
                height 0.3s var(--ease-luxury), opacity 0.3s, border-color 0.3s;
    will-change: transform;
  }

  /* Hover states — on interactive elements */
  body.cursor-hover .cursor-dot {
    width: 0; height: 0; background: transparent;
  }
  body.cursor-hover .cursor-ring {
    width: 56px; height: 56px; opacity: 0.25;
    background: color-mix(in srgb, var(--text) 5%, transparent);
  }

  /* Click state */
  body.cursor-click .cursor-dot  { transform: translate(-50%,-50%) scale(0.5); }
  body.cursor-click .cursor-ring { transform: translate(-50%,-50%) scale(0.8); }

  /* Inverted on dark backgrounds (if hero image is dark) */
  body.cursor-inverted .cursor-dot  { background: white; }
  body.cursor-inverted .cursor-ring { border-color: white; }
}

/* Disable on touch + reduced motion */
@media (pointer: coarse), (prefers-reduced-motion: reduce) {
  .cursor-dot, .cursor-ring { display: none !important; }
  * { cursor: auto !important; }
}
```

```javascript
// Only run on fine-pointer devices
if (window.matchMedia('(pointer: fine)').matches &&
    !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {

  const dot  = document.getElementById('cursor-dot');
  const ring = document.getElementById('cursor-ring');
  if (!dot || !ring) return; // guard

  let mouseX = 0, mouseY = 0;
  let ringX  = 0, ringY  = 0;

  document.addEventListener('mousemove', e => {
    mouseX = e.clientX;
    mouseY = e.clientY;
    // Dot follows exactly
    dot.style.transform  = `translate(calc(${mouseX}px - 50%), calc(${mouseY}px - 50%))`;
  });

  // Ring follows with lag (lerp)
  const lerp = (a, b, t) => a + (b - a) * t;
  function animateRing() {
    ringX = lerp(ringX, mouseX, 0.12); // 0.12 = lag amount (lower = more lag)
    ringY = lerp(ringY, mouseY, 0.12);
    ring.style.transform = `translate(calc(${ringX}px - 50%), calc(${ringY}px - 50%))`;
    requestAnimationFrame(animateRing);
  }
  animateRing();

  // Hover detection
  const hoverTargets = 'a, button, [role="button"], label, input, select, textarea, .clickable';
  document.querySelectorAll(hoverTargets).forEach(el => {
    el.addEventListener('mouseenter', () => document.body.classList.add('cursor-hover'));
    el.addEventListener('mouseleave', () => document.body.classList.remove('cursor-hover'));
  });

  // Click feedback
  document.addEventListener('mousedown', () => document.body.classList.add('cursor-click'));
  document.addEventListener('mouseup',   () => document.body.classList.remove('cursor-click'));

  // Hide when leaving window
  document.addEventListener('mouseleave', () => {
    dot.style.opacity = '0'; ring.style.opacity = '0';
  });
  document.addEventListener('mouseenter', () => {
    dot.style.opacity = '1';
    ring.style.opacity = document.body.classList.contains('cursor-hover') ? '0.25' : '0.5';
  });
}
```

---

## VARIANT 2 — EXPRESSIVE (Blob Cursor)

Large, vivid, color-shifting blob. Energetic and loud — matches the language.

```html
<div id="cursor-blob" class="cursor-blob" aria-hidden="true"></div>
```

```css
@media (pointer: fine) {
  * { cursor: none !important; }

  .cursor-blob {
    position: fixed; top: 0; left: 0; z-index: 99999;
    width: 20px; height: 20px;
    border-radius: 50%;
    background: var(--accent);
    pointer-events: none;
    transform: translate(-50%, -50%);
    mix-blend-mode: difference; /* inverts colors it passes over — dramatic */
    transition:
      width 0.3s var(--ease-spring),
      height 0.3s var(--ease-spring),
      border-radius 0.4s var(--ease-spring);
    will-change: transform;
  }

  /* Hover — blob expands into a large fill */
  body.cursor-hover .cursor-blob {
    width: 80px; height: 80px;
    border-radius: 40%;
    mix-blend-mode: difference;
  }

  /* Click — squish */
  body.cursor-click .cursor-blob {
    width: 60px; height: 60px;
    border-radius: 60%;
    transition-duration: 0.08s;
  }

  /* Text mode — thin vertical line cursor */
  body.cursor-text .cursor-blob {
    width: 2px; height: 24px;
    border-radius: 1px;
    background: var(--text);
    mix-blend-mode: normal;
  }
}
@media (pointer: coarse), (prefers-reduced-motion: reduce) {
  .cursor-blob { display: none !important; }
  * { cursor: auto !important; }
}
```

```javascript
if (window.matchMedia('(pointer: fine)').matches &&
    !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {

  const blob = document.getElementById('cursor-blob');
  if (!blob) return;

  let x = 0, y = 0;
  document.addEventListener('mousemove', e => {
    x = e.clientX; y = e.clientY;
    blob.style.transform = `translate(calc(${x}px - 50%), calc(${y}px - 50%))`;
  });

  document.querySelectorAll('a, button, [role="button"]').forEach(el => {
    el.addEventListener('mouseenter', () => document.body.classList.add('cursor-hover'));
    el.addEventListener('mouseleave', () => document.body.classList.remove('cursor-hover'));
  });

  // Text cursor on text elements
  document.querySelectorAll('p, h1, h2, h3, h4, li, span, blockquote').forEach(el => {
    el.addEventListener('mouseenter', () => document.body.classList.add('cursor-text'));
    el.addEventListener('mouseleave', () => document.body.classList.remove('cursor-text'));
  });

  document.addEventListener('mousedown', () => document.body.classList.add('cursor-click'));
  document.addEventListener('mouseup',   () => document.body.classList.remove('cursor-click'));
}
```

---

## MAGNETIC BUTTONS

Buttons that subtly attract the cursor. Luxury and Expressive only.

```html
<button class="btn btn--primary magnetic-btn" data-magnetic>
  Get started
</button>
```

```javascript
// Magnetic effect — button follows cursor slightly when cursor is nearby
document.querySelectorAll('[data-magnetic]').forEach(btn => {
  const strength = parseFloat(btn.dataset.magneticStrength) || 0.35;
  const radius   = parseFloat(btn.dataset.magneticRadius) || 100; // px from center

  btn.addEventListener('mousemove', e => {
    const rect = btn.getBoundingClientRect();
    const cx   = rect.left + rect.width  / 2;
    const cy   = rect.top  + rect.height / 2;
    const dx   = e.clientX - cx;
    const dy   = e.clientY - cy;
    const dist = Math.sqrt(dx * dx + dy * dy);

    if (dist < radius) {
      const pull = (1 - dist / radius) * strength;
      btn.style.transform = `translate(${dx * pull}px, ${dy * pull}px)`;
    }
  });

  btn.addEventListener('mouseleave', () => {
    btn.style.transform = '';
    btn.style.transition = `transform 0.5s var(--ease-spring)`;
    // Reset transition after spring settles
    setTimeout(() => btn.style.transition = '', 500);
  });
});
```

```css
/* Magnetic button needs hardware acceleration base */
[data-magnetic] {
  will-change: transform;
  transition: transform 0.15s var(--ease-out);
  display: inline-block; /* required for transform */
}
[data-magnetic]:hover { transition: transform 0.08s linear; }
```

---

## CURSOR — TEXT LABELS ON HOVER

Cursor changes to show a text label when hovering certain elements.
Classic technique on portfolio/agency sites.

```html
<!-- Trigger on any element -->
<div class="project-card" data-cursor-label="View →">
  <img ...>
</div>

<a href="/work" data-cursor-label="See work">
  Our Work
</a>
```

```html
<!-- Add this cursor element -->
<div id="cursor-label" class="cursor-label" aria-hidden="true">
  <span id="cursor-label-text"></span>
</div>
```

```css
@media (pointer: fine) {
  .cursor-label {
    position: fixed; top: 0; left: 0; z-index: 99999;
    pointer-events: none;
    transform: translate(-50%, -50%);
    opacity: 0; scale: 0.8;
    transition: opacity 0.2s var(--ease-out), scale 0.2s var(--ease-spring);
    will-change: transform;
  }
  .cursor-label.visible { opacity: 1; scale: 1; }

  #cursor-label-text {
    display: block;
    background: var(--text);
    color: var(--bg);
    padding: 8px 16px;
    border-radius: var(--radius-full);
    font-size: var(--text-xs);
    font-weight: 600;
    letter-spacing: 0.04em;
    white-space: nowrap;
  }
  /* Luxury: no radius */
  [data-lang="luxury"] #cursor-label-text { border-radius: 0; }
}
@media (pointer: coarse), (prefers-reduced-motion: reduce) {
  .cursor-label { display: none !important; }
}
```

```javascript
if (window.matchMedia('(pointer: fine)').matches) {
  const label = document.getElementById('cursor-label');
  const labelText = document.getElementById('cursor-label-text');
  if (!label || !labelText) return;

  document.addEventListener('mousemove', e => {
    label.style.transform = `translate(calc(${e.clientX}px - 50%), calc(${e.clientY}px - 70px))`;
  });

  document.querySelectorAll('[data-cursor-label]').forEach(el => {
    el.addEventListener('mouseenter', () => {
      labelText.textContent = el.dataset.cursorLabel;
      label.classList.add('visible');
    });
    el.addEventListener('mouseleave', () => {
      label.classList.remove('visible');
    });
  });
}
```

---

## SCROLL VELOCITY CURSOR (Expressive only)

Cursor tilts and stretches based on scroll speed — dramatic, magazine-feel.

```css
@media (pointer: fine) {
  #cursor-blob {
    /* Add transform-origin center for stretch effect */
    transform-origin: center center;
  }
}
```

```javascript
let lastScrollY = window.scrollY;
let scrollVel   = 0;

window.addEventListener('scroll', () => {
  scrollVel   = window.scrollY - lastScrollY;
  lastScrollY = window.scrollY;

  const blob = document.getElementById('cursor-blob');
  if (!blob) return;

  const clampedVel = Math.max(-20, Math.min(20, scrollVel));
  const scaleY = 1 + Math.abs(clampedVel) * 0.04;
  const scaleX = 1 / scaleY; // maintain area
  blob.style.transform =
    `translate(calc(var(--cx, 0px) - 50%), calc(var(--cy, 0px) - 50%)) scaleY(${scaleY}) scaleX(${scaleX})`;
}, { passive: true });
```

---

## QUICK REFERENCE

| Feature | Luxury | Expressive | Minimalist | Others |
|---------|--------|------------|------------|--------|
| Dot + ring | ✅ | — | Optional | ❌ |
| Blob | — | ✅ | — | ❌ |
| Magnetic buttons | ✅ | ✅ | — | ❌ |
| Text label on hover | ✅ | ✅ | Optional | ❌ |
| Scroll velocity | — | ✅ | — | ❌ |
| mix-blend-mode | — | ✅ | — | ❌ |
| On mobile | ❌ | ❌ | ❌ | ❌ |
| With reduced-motion | ❌ | ❌ | ❌ | ❌ |
