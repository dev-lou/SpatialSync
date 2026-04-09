# UI/UX CSS Architecture 2026

> Tailwind v4, CSS Layers, utility-first vs component-first, CSS Modules,
> and best CSS organization patterns for every stack.

---

## DECISION TREE — CSS Strategy

```
Stack choice?
├─ Vanilla HTML / static site
│   └─ CSS Layers + Custom Properties (this file, Step 1)
├─ React / Next.js / Vue / Nuxt
│   ├─ Need maximum speed + utility classes?
│   │   └─ Tailwind v4 (see Step 2)
│   ├─ Need scoped styles, co-located?
│   │   └─ CSS Modules (see Step 3)
│   ├─ Need type-safe styles?
│   │   └─ vanilla-extract (see Step 4)
│   └─ Building a component library?
│       └─ CSS Modules + design tokens
├─ Expo / React Native
│   └─ StyleSheet API + Unistyles (see Step 5)
└─ Everything above
    └─ Design tokens from uiux-tokens.md — always the foundation
```

---

## STEP 1 — CSS LAYERS (Native, Every Project)

`@layer` prevents specificity wars. Load order = cascade order. No `!important` needed.

```css
/* styles/tokens.css — import first, always */
@layer reset, tokens, base, components, utilities, overrides;

@layer reset {
  /* normalize + box-sizing (from uiux-components.md) */
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
}

@layer tokens {
  /* ALL :root CSS variables from uiux-tokens.md */
  :root { --accent: #0066FF; /* etc */ }
  [data-theme="dark"] { --bg: #0A0B0E; /* etc */ }
}

@layer base {
  /* html, body, img, a defaults */
  body { font-family: var(--font-body); background: var(--bg); color: var(--text); }
}

@layer components {
  /* .btn, .card, .field, .nav, .footer — reusable components */
  .btn { display: inline-flex; /* from uiux-interactive.md */ }
}

@layer utilities {
  /* Single-purpose helpers */
  .sr-only { position: absolute; width: 1px; /* etc */ }
  .container { max-width: var(--container-max); /* etc */ }
}

@layer overrides {
  /* Per-page / per-component overrides — highest layer, cleanest specificity */
  /* Use sparingly — prefer component tokens over overrides */
}
```

**Layer priority:** `overrides > utilities > components > base > tokens > reset`
No specificity battles. `.btn` in components never fights `.footer .btn` — just add to overrides.

---

## STEP 2 — TAILWIND V4 (2026)

Tailwind v4 switches from `tailwind.config.js` to **CSS-first config**.
No config file. Configure directly in CSS.

### Installation

```bash
npm install tailwindcss@next @tailwindcss/vite
# or
npm install tailwindcss@next @tailwindcss/postcss
```

### CSS-First Configuration (v4)

```css
/* styles/main.css */
@import "tailwindcss";

/* ── THEME CUSTOMIZATION (replaces tailwind.config.js) ── */
@theme {
  /* Map your design tokens to Tailwind's theme */
  --color-accent:       #0066FF;
  --color-accent-dark:  #4D8EFF;
  --color-bg:           #FFFFFF;
  --color-surface:      #F7F8FA;
  --color-text:         #0F1117;

  /* Fonts */
  --font-display: 'Instrument Serif', serif;
  --font-body:    'Plus Jakarta Sans', sans-serif;
  --font-mono:    'JetBrains Mono', monospace;

  /* Custom spacing (adds to existing scale) */
  --spacing-18: 4.5rem;
  --spacing-22: 5.5rem;

  /* Custom border-radius (per language) */
  --radius-brand-sm: 6px;
  --radius-brand-md: 12px;
  --radius-brand-lg: 20px;

  /* Custom shadows */
  --shadow-brand: 0 24px 64px rgba(0,0,0,0.15);

  /* Custom breakpoints */
  --breakpoint-xs: 480px;
}

/* ── DARK MODE (uses [data-theme="dark"] instead of .dark) ── */
@variant dark (&:where([data-theme="dark"], [data-theme="dark"] *));
```

### v4 Utility Usage

```html
<!-- v4: same utility classes, plus new features -->
<button class="
  bg-accent text-white
  px-6 py-3 rounded-brand-md
  font-semibold text-sm
  transition-all duration-200
  hover:bg-accent/85 hover:-translate-y-px
  focus-visible:ring-2 focus-visible:ring-accent
  dark:bg-accent-dark
">
  Get Started
</button>

<!-- v4: arbitrary values still work -->
<div class="grid-cols-[2fr_1fr_1fr]">
<!-- v4: CSS variables in utilities -->
<div class="bg-[var(--surface)] border-[var(--border)]">
```

### v4 CSS Variables in Utilities

```html
<!-- v4 maps @theme tokens to utilities automatically -->
<!-- --color-accent → bg-accent, text-accent, border-accent, ring-accent -->
<!-- --font-display → font-display -->
<!-- --spacing-18   → p-18, m-18, gap-18 -->
```

### When NOT to Use Tailwind

```
❌ Style-heavy design system with complex token logic → use CSS custom properties
❌ Animations with GSAP/timeline logic → CSS/JS directly
❌ Existing codebase using CSS Modules → don't mix without layer separation
❌ When AI-generated HTML will be reviewed by non-devs → Tailwind classes are noise
```

---

## STEP 3 — CSS MODULES (Scoped Styles)

Zero global leakage. Perfect for component libraries and design systems.

```jsx
// components/ui/Button/Button.module.css
.btn {
  display: inline-flex;
  align-items: center;
  padding: var(--space-3) var(--space-6);
  background: var(--accent);       /* uses global tokens — always */
  color: white;
  border-radius: var(--radius-md); /* uses global tokens */
  font-weight: 600;
  transition: background var(--transition-micro);
}
.btn:hover { background: var(--accent-h); }
.btn--ghost { background: transparent; border: 1px solid var(--border); color: var(--text); }
.btn--sm { padding: var(--space-2) var(--space-4); font-size: var(--text-xs); }
```

```jsx
// components/ui/Button/Button.jsx
import styles from './Button.module.css';
import clsx from 'clsx'; // npm install clsx

export function Button({ variant = 'primary', size = 'md', className, children, ...props }) {
  return (
    <button
      className={clsx(
        styles.btn,
        variant === 'ghost' && styles['btn--ghost'],
        size === 'sm' && styles['btn--sm'],
        className // allow external overrides
      )}
      {...props}
    >
      {children}
    </button>
  );
}
```

```
Compiles to: .Button_btn__xK8pQ  — unique hash, zero conflicts
Design tokens (--accent, --radius-md) still come from global :root
```

---

## STEP 4 — VANILLA-EXTRACT (Type-Safe CSS)

Zero-runtime, type-safe, works with Next.js / Vite. Best for typed design systems.

```typescript
// styles/tokens.css.ts
import { createGlobalTheme } from '@vanilla-extract/css';

export const vars = createGlobalTheme(':root', {
  color: { bg: '#FFFFFF', accent: '#0066FF', text: '#0F1117' },
  space: { sm: '8px', md: '16px', lg: '32px' },
  font:  { body: "'Plus Jakarta Sans', sans-serif" },
  radius:{ md: '12px' },
});

// [data-theme="dark"]
createGlobalThemeContract(/* dark values */);
```

```typescript
// components/Button.css.ts
import { style, styleVariants } from '@vanilla-extract/css';
import { vars } from '../styles/tokens.css';

export const base = style({
  display: 'inline-flex', padding: `${vars.space.sm} ${vars.space.lg}`,
  background: vars.color.accent, borderRadius: vars.radius.md,
  color: 'white', fontWeight: 600,
  ':hover': { opacity: 0.9 },
});
export const variants = styleVariants({
  primary: [base, { background: vars.color.accent }],
  ghost:   [base, { background: 'transparent', border: `1px solid ${vars.color.text}` }],
});
```

---

## STEP 5 — REACT NATIVE / EXPO (StyleSheet API)

```javascript
// styles/tokens.js — mirrors CSS tokens for native
const tokens = {
  light: {
    bg:      '#FFFFFF', surface:  '#F7F8FA', text:     '#0F1117',
    text2:   '#4A5060', border:   'rgba(15,17,23,0.08)', accent:   '#0066FF',
  },
  dark: {
    bg:      '#0A0B0E', surface:  '#13151A', text:     '#EDF0F5',
    text2:   '#8892A0', border:   'rgba(237,240,245,0.07)', accent: '#4D8EFF',
  },
  space:  { 1:4, 2:8, 3:12, 4:16, 6:24, 8:32, 12:48 },
  radius: { sm:6, md:12, lg:20, full:999 },
  font:   { display:'InstrumentSerif', body:'PlusJakartaSans', mono:'JetBrainsMono' },
};
export default tokens;

// Usage in component:
import { useColorScheme } from 'react-native';
import t from '../styles/tokens';

const scheme = useColorScheme();
const c = t[scheme ?? 'light'];
const styles = StyleSheet.create({
  container: { backgroundColor: c.bg, padding: t.space[6] },
  text: { color: c.text, fontFamily: t.font.body },
});
```

**React Native alert replacement:** Never `Alert.alert()` for UX-facing feedback.
Use `react-native-toast-message` or `burnt` (Expo) for toast notifications.

---

## CSS ANTI-PATTERNS 2026

```css
/* ❌ NEVER: style specificity wars */
.header .nav .btn.primary { color: blue; }  /* deep nesting = fragile */

/* ✅ INSTEAD: use @layer overrides */
@layer overrides { .nav-btn { color: var(--accent); } }

/* ❌ NEVER: override design tokens inline */
<div style="color: #FF0000; font-size: 22px">  /* breaks consistency */

/* ✅ INSTEAD: use semantic tokens */
<div class="text-error" style="font-size: var(--text-xl)">

/* ❌ NEVER: !important in components */
.card { background: var(--surface) !important; }  /* signals architectural failure */

/* ✅ INSTEAD: fix specificity with @layer */
@layer components { .card { background: var(--surface); } }

/* ❌ NEVER: magic numbers */
.hero { padding-top: 73px; }

/* ✅ INSTEAD: spacing tokens */
.hero { padding-top: var(--space-20); }
```

---

## QUICK REFERENCE

| Stack | CSS Approach | Config |
|-------|-------------|--------|
| HTML/CSS/JS | CSS Layers + Custom Props | tokens.css |
| React/Next.js (utility) | Tailwind v4 | @theme in CSS |
| React/Next.js (scoped) | CSS Modules + tokens | module.css files |
| Component library | vanilla-extract | .css.ts files |
| Vue/Nuxt | Tailwind v4 or scoped `<style>` | same @theme |
| React Native | StyleSheet + tokens.js | tokens.js |
| All of the above | Design tokens are always the foundation | tokens.css / tokens.js |

---

## STEP 6 — CSS CONTAINER QUERIES (Component-Level Responsiveness)

Container queries let components respond to **their own container's size**, not the viewport.
This is the correct model for reusable components — a card in a sidebar and a card in a full-width grid should look different without media queries.

### Why Container Queries Over Media Queries

```
Media query: responds to VIEWPORT width
└─ Problem: same component in a narrow sidebar + wide grid = same styles

Container query: responds to PARENT container width
└─ Solution: component adapts to wherever it is placed
```

### Setup

```css
/* 1. Define a containment context on the PARENT */
.card-grid     { container-type: inline-size; container-name: card-grid; }
.sidebar       { container-type: inline-size; container-name: sidebar; }
.modal-content { container-type: inline-size; }

/* 2. Query the container from inside the component */
.card {
  /* Default: compact (narrow container) */
  display: flex;
  flex-direction: column;
  padding: var(--space-4);
}

/* When the card's container is ≥ 480px wide — switch to horizontal layout */
@container (min-width: 480px) {
  .card {
    flex-direction: row;
    padding: var(--space-6);
  }
  .card__image {
    width: 200px;
    flex-shrink: 0;
  }
}

/* Named container query */
@container card-grid (min-width: 800px) {
  .card__title { font-size: var(--text-2xl); }
}
```

### Practical Patterns

```css
/* Responsive product card — adapts anywhere */
.product-card-wrapper { container-type: inline-size; }

.product-card {
  padding: var(--space-4);
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
}

@container (min-width: 400px) {
  .product-card {
    flex-direction: row;
    align-items: center;
  }
  .product-card__image { width: 140px; aspect-ratio: 1; object-fit: cover; }
  .product-card__actions { margin-left: auto; }
}

@container (min-width: 600px) {
  .product-card { padding: var(--space-6); gap: var(--space-6); }
  .product-card__image { width: 200px; }
  .product-card__desc { display: block; } /* was hidden at sm */
}
```

```css
/* Nav that changes orientation based on container */
.nav-wrapper { container-type: inline-size; }
.nav-links   { display: flex; flex-direction: column; gap: var(--space-2); }

@container (min-width: 600px) {
  .nav-links { flex-direction: row; }
}
```

### Container Units (cqw, cqh, cqi, cqb)

```css
/* cqw = container query width unit (like vw but for container) */
.hero-title {
  font-size: clamp(1.5rem, 5cqw, 4rem);
  /* scales font relative to container width, not viewport */
}
```

### Browser Support (2026)

```
Chrome 105+  ✅ Full support
Firefox 110+ ✅ Full support
Safari 16+   ✅ Full support
```
Container queries have excellent support in 2026 — safe to use without fallback for modern audiences.
For legacy support, wrap in `@supports`:
```css
@supports (container-type: inline-size) {
  .card-grid { container-type: inline-size; }
}
/* Fallback: component uses viewport-based media queries naturally */
```

---

## STEP 7 — CSS SUBGRID (Aligned Nested Grids)

**Rule**: Subgrid allows nested elements to align to the parent grid. This eliminates manual column/row alignment and creates perfectly aligned card layouts.

### What Subgrid Solves

```
WITHOUT subgrid:
┌─────────────┐ ┌─────────────┐ ┌─────────────┐
│ Card Title  │ │ Long Title  │ │ Title       │
│             │ │ That Wraps  │ │             │
│ Short text  │ │             │ │ Medium text │
│             │ │ Description │ │ here today  │
│ [Button]    │ │ [Button]    │ │ [Button]    │
└─────────────┘ └─────────────┘ └─────────────┘
  ↑ Buttons misaligned! ↑

WITH subgrid:
┌─────────────┐ ┌─────────────┐ ┌─────────────┐
│ Card Title  │ │ Long Title  │ │ Title       │
│             │ │ That Wraps  │ │             │
├─────────────┤ ├─────────────┤ ├─────────────┤
│ Short text  │ │ Description │ │ Medium text │
│             │ │             │ │ here today  │
├─────────────┤ ├─────────────┤ ├─────────────┤
│ [Button]    │ │ [Button]    │ │ [Button]    │
└─────────────┘ └─────────────┘ └─────────────┘
  ↑ Perfectly aligned! ↑
```

### Basic Subgrid Pattern

```css
/* Parent grid — defines the row template */
.card-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: var(--space-6);
}

/* Child card — inherits parent grid for rows */
.card {
  display: grid;
  grid-template-rows: subgrid;  /* ← Key: inherit parent's row tracks */
  grid-row: span 3;             /* ← Span 3 rows (title, content, footer) */
}

.card__title   { /* Row 1 */ }
.card__content { /* Row 2 */ }
.card__footer  { /* Row 3 */ }
```

### Full Card Grid Example

```html
<div class="product-grid">
  <article class="product-card">
    <img src="..." alt="..." class="product-card__image">
    <h3 class="product-card__title">Product Name</h3>
    <p class="product-card__description">Description text that may vary in length...</p>
    <div class="product-card__footer">
      <span class="product-card__price">$99</span>
      <button class="btn btn--primary btn--sm">Add to Cart</button>
    </div>
  </article>
  <!-- More cards... -->
</div>
```

```css
/* Parent grid — 4 implicit row tracks */
.product-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  grid-template-rows: auto auto auto auto; /* image, title, desc, footer */
  gap: var(--space-6);
}

/* Card uses subgrid for rows */
.product-card {
  display: grid;
  grid-template-rows: subgrid;
  grid-row: span 4;  /* Match parent's 4 rows */
  
  background: var(--surface);
  border-radius: var(--radius-lg);
  border: 1px solid var(--border);
  overflow: hidden;
}

/* Each child aligns to its row */
.product-card__image {
  width: 100%;
  aspect-ratio: 4/3;
  object-fit: cover;
}

.product-card__title {
  padding: var(--space-4) var(--space-4) 0;
  font-size: var(--text-lg);
  font-weight: 600;
}

.product-card__description {
  padding: var(--space-2) var(--space-4);
  color: var(--text-2);
  font-size: var(--text-sm);
}

.product-card__footer {
  padding: var(--space-4);
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-top: 1px solid var(--border);
  margin-top: auto;  /* Push to bottom of cell */
}
```

### Column Subgrid

```css
/* Subgrid can also work for columns */
.grid-parent {
  display: grid;
  grid-template-columns: repeat(6, 1fr);
  gap: var(--space-4);
}

/* Child spans 4 columns but uses parent's tracks */
.grid-child {
  display: grid;
  grid-template-columns: subgrid;
  grid-column: span 4;  /* Uses parent's 4 column tracks */
}

/* Grandchildren align perfectly to parent grid */
.grid-child > * {
  /* Each child gets one column from parent */
}
```

### Two-Dimensional Subgrid

```css
/* Subgrid for both rows AND columns */
.dashboard-grid {
  display: grid;
  grid-template-columns: repeat(12, 1fr);
  grid-template-rows: repeat(4, auto);
  gap: var(--space-4);
}

.dashboard-widget {
  display: grid;
  grid-template-columns: subgrid;
  grid-template-rows: subgrid;
  grid-column: span 6;
  grid-row: span 2;
}
```

### Browser Support (2026)

```
Chrome 117+ ✅ Full support
Firefox 71+ ✅ Full support  
Safari 16+  ✅ Full support
```

Subgrid has excellent support in 2026. For legacy browsers, use `@supports`:

```css
/* Progressive enhancement */
.card-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: var(--space-6);
}

.card {
  display: flex;
  flex-direction: column;
  /* Fallback: flexbox with min-height */
}

.card__footer {
  margin-top: auto;
}

/* Enhanced: use subgrid when supported */
@supports (grid-template-rows: subgrid) {
  .card-grid {
    grid-template-rows: auto auto auto;
  }
  
  .card {
    display: grid;
    grid-template-rows: subgrid;
    grid-row: span 3;
  }
  
  .card__footer {
    margin-top: 0;
  }
}
```

### Common Subgrid Patterns

```css
/* PATTERN 1: Pricing table with aligned rows */
.pricing-table {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  grid-template-rows: auto auto 1fr auto; /* header, price, features, CTA */
}

.pricing-card {
  display: grid;
  grid-template-rows: subgrid;
  grid-row: span 4;
}

/* PATTERN 2: Form with aligned labels */
.form-grid {
  display: grid;
  grid-template-columns: max-content 1fr;
  gap: var(--space-4);
}

.form-row {
  display: grid;
  grid-template-columns: subgrid;
  grid-column: span 2;
}

/* PATTERN 3: Sidebar + main aligned sections */
.page-layout {
  display: grid;
  grid-template-columns: 280px 1fr;
  grid-template-rows: auto 1fr auto;
}

.sidebar {
  display: grid;
  grid-template-rows: subgrid;
  grid-row: span 3;
}
```