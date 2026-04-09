---
description: Advanced layout patterns including floating cards, hero offsets, full-bleed bands, diagonal splits, masonry grids, and dashboard widget systems.
---

# UI/UX Layout Patterns 2026

> Advanced responsive layouts, CSS Grid/Flexbox strategies, diagonal sections, 
> masonry grids, and interactive dashboard widget systems.

---

## 1. FLOATING CENTRED CARD (The "Focus" Layout)

Use for login, onboarding, or single-action landing pages.

```html
<div class="layout-focus">
  <div class="card card--floating">
    <!-- Content -->
  </div>
</div>
```

```css
.layout-focus {
  min-height: 100dvh;
  display: grid;
  place-items: center;
  padding: var(--space-6);
  background: var(--bg);
  background-image: var(--gradient-mesh);
}

.card--floating {
  width: 100%;
  max-width: 480px;
  padding: var(--space-8);
  box-shadow: var(--shadow-xl);
  animation: float-in var(--dur-slow) var(--ease-spring);
}

@keyframes float-in {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}
```

---

## 2. HERO WITH OFFSET IMAGE

A dynamic hero section where the image breaks the container or overlaps content.

```html
<section class="hero-offset container">
  <div class="hero-offset__content">
    <h1 class="text-hero">Design the Future</h1>
    <p class="text-lg">Premium UI/UX systems for 2026.</p>
  </div>
  <div class="hero-offset__media">
    <picture>
      <source srcset="hero.avif" type="image/avif">
      <img src="hero.jpg" alt="Hero Image" loading="eager" fetchpriority="high">
    </picture>
  </div>
</section>
```

```css
.hero-offset {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-8);
  align-items: center;
  padding-block: var(--section-space-lg);
}

@media (min-width: 1024px) {
  .hero-offset {
    grid-template-columns: 1.2fr 0.8fr;
  }
  .hero-offset__media {
    transform: translateX(var(--space-12));
    z-index: 2;
  }
}
```

---

## 3. DIAGONAL SPLIT SECTION (Clip-Path)

Use to break the "boxy" feel of standard web layouts.

```css
.section--diagonal {
  position: relative;
  background: var(--surface);
  padding-block: var(--section-space-lg);
  clip-path: polygon(0 5%, 100% 0, 100% 95%, 0 100%);
  margin-block: calc(var(--section-space-lg) * -0.5);
}

/* Ensure content stays upright */
.section--diagonal > .container {
  clip-path: none;
}
```

---

## 4. MASONRY GRID (CSS Grid + Aspect Ratio)

Modern masonry-style gallery using CSS Grid `grid-template-rows: masonry` (where supported) or the `aspect-ratio` fallback.

```css
.grid-masonry {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: var(--space-4);
}

.grid-masonry > .item:nth-child(2n) { aspect-ratio: 3/4; }
.grid-masonry > .item:nth-child(3n) { aspect-ratio: 1/1; }
.grid-masonry > .item:nth-child(4n) { aspect-ratio: 4/5; }
```

---

## 5. DASHBOARD WIDGET GRID (Draggable/Resizable)

Use for admin panels and data-heavy interfaces.

```html
<div class="widget-grid" id="dashboard-grid">
  <div class="widget" data-size="small">
    <header class="widget__header">
      <h3>KPI</h3>
      <button class="widget__drag-handle"><i data-lucide="grip-vertical"></i></button>
    </header>
    <div class="widget__content">...</div>
  </div>
</div>
```

```css
.widget-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
  grid-auto-rows: 240px;
  gap: var(--space-4);
}

.widget {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
  display: flex;
  flex-direction: column;
  transition: var(--transition-base);
}

.widget[data-size="large"] { grid-column: span 2; grid-row: span 2; }
.widget[data-size="wide"]  { grid-column: span 2; }

.widget__drag-handle { cursor: grab; color: var(--muted); }
.widget__drag-handle:active { cursor: grabbing; }
```

---

## 6. TIMELINE (Vertical/Horizontal)

```css
.timeline {
  position: relative;
  padding-left: var(--space-8);
}

.timeline::before {
  content: '';
  position: absolute;
  left: 7px; top: 0; bottom: 0;
  width: 2px;
  background: var(--border);
}

.timeline__item {
  position: relative;
  margin-bottom: var(--space-8);
}

.timeline__dot {
  position: absolute;
  left: -31px; top: 4px;
  width: 12px; height: 12px;
  border-radius: 50%;
  background: var(--accent);
  border: 2px solid var(--bg);
}
```

---

## 7. PRICING TABLE (Sticky Headers)

```css
.pricing-table {
  display: grid;
  grid-template-columns: 200px repeat(3, 1fr);
  width: 100%;
}

.pricing-header {
  position: sticky;
  top: 0;
  background: var(--surface);
  z-index: 10;
  padding: var(--space-4);
  border-bottom: 1px solid var(--border);
}
```

---

## 8. SPLIT SCREEN (50/50 Sticky)

```css
.split-screen {
  display: grid;
  grid-template-columns: 1fr;
}

@media (min-width: 1024px) {
  .split-screen {
    grid-template-columns: 1fr 1fr;
  }
  .split-screen__sticky {
    position: sticky;
    top: 0;
    height: 100vh;
  }
}
```
