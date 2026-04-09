# UI/UX Tokens & Design System

> Brand token system, 3-tier token architecture (Figma-aligned), CSS variables,
> gradient rules, font pairings, shadow/elevation tokens, animation tokens.

---

## TOKEN ARCHITECTURE — 3 Tiers (Figma-aligned, W3C standard)

Based on Figma Variables best practice: Primitive → Semantic → Component.
**Only semantic tokens go in components. Never use primitives directly in UI.**

```
Tier 1 — PRIMITIVE (raw values, never applied directly)
  --primitive-blue-500: #3B82F6
  --primitive-space-4:  16px

Tier 2 — SEMANTIC (how/where to use — these are your CSS vars)
  --accent:   var(--primitive-blue-500)   → "the primary action color"
  --space-4:  var(--primitive-space-4)    → "standard spacing unit"

Tier 3 — COMPONENT (optional, large teams / complex systems)
  --btn-bg:         var(--accent)
  --btn-bg-hover:   color-mix(in srgb, var(--accent) 85%, black)
```

**For most projects: Tier 1 + Tier 2 is enough.**
Add Tier 3 only when the same semantic token is used differently in many components.

---

## STEP 1 — Brand Token System (CONSISTENCY ACROSS ALL PAGES)

**This is the #1 consistency rule.** Define ALL tokens once in `:root` / a global CSS file / theme provider. Import it on every page. Never redefine brand colors inline.

### Token Architecture

```css
/* ================================================
   BRAND TOKEN FILE — tokens.css (import everywhere)
   One file. All pages. Never override.
   ================================================ */

:root {
  /* FONTS — set once, use everywhere */
  --font-display: 'YOUR_DISPLAY_FONT', serif;
  --font-body:    'YOUR_BODY_FONT', sans-serif;
  --font-mono:    'YOUR_MONO_FONT', monospace;

  /* TYPE SCALE — never use raw px in components */
  --text-xs:   0.75rem;
  --text-sm:   0.875rem;
  --text-base: 1rem;
  --text-lg:   1.125rem;
  --text-xl:   1.25rem;
  --text-2xl:  1.5rem;
  --text-3xl:  1.875rem;
  --text-4xl:  2.25rem;
  --text-5xl:  3rem;
  --text-6xl:  3.75rem;
  --text-hero: clamp(3rem, 8vw, 8rem);

  /* SPACING — 8px grid, never use arbitrary values */
  --space-1: 4px;   --space-2: 8px;   --space-3: 12px;
  --space-4: 16px;  --space-6: 24px;  --space-8: 32px;
  --space-12: 48px; --space-16: 64px; --space-20: 80px;
  --space-24: 96px; --space-30: 120px;--space-40: 160px;

  /* RADIUS — set per design language, never change per-page */
  --radius-sm: Xpx;  /* set from table below */
  --radius-md: Xpx;
  --radius-lg: Xpx;
  --radius-full: 9999px;

  /* EASING */
  --ease-spring:  cubic-bezier(0.34, 1.56, 0.64, 1);
  --ease-out:     cubic-bezier(0.16, 1, 0.3, 1);
  --ease-sharp:   cubic-bezier(0.4, 0, 0.2, 1);
  --ease-luxury:  cubic-bezier(0.25, 0.1, 0, 1);

  /* LAYOUT */
  --container-max:    1280px; /* set per language */
  --container-gutter: 24px;   /* set per language, responsive */
  --section-space-sm: 48px;   /* mobile */
  --section-space-md: 64px;   /* tablet */
  --section-space-lg: 96px;   /* desktop */

  /* TRANSITIONS */
  --transition-fast:   150ms var(--ease-sharp);
  --transition-base:   250ms var(--ease-out);
  --transition-slow:   400ms var(--ease-luxury);
  --transition-spring: 350ms var(--ease-spring);

  /* MICRO-INTERACTION TOKENS */
  --transition-micro:    100ms ease;
  --transition-tactile:  250ms cubic-bezier(0.2, 0.9, 0.4, 1.1);
  --transition-reveal:   600ms var(--ease-out);

  /* ANIMATION DURATION TOKENS */
  --duration-instant:  100ms;
  --duration-fast:     200ms;
  --duration-base:     350ms;
  --duration-slow:     600ms;
  --duration-lazy:     900ms;

  /* ELEVATION (shadow) TOKENS — values set per language below */
  --shadow-sm:  0 1px 3px rgba(0,0,0,0.08), 0 1px 2px rgba(0,0,0,0.04);
  --shadow-md:  0 4px 16px rgba(0,0,0,0.10), 0 2px 4px rgba(0,0,0,0.06);
  --shadow-lg:  0 12px 40px rgba(0,0,0,0.14), 0 4px 8px rgba(0,0,0,0.06);
  --shadow-xl:  0 24px 64px rgba(0,0,0,0.18), 0 8px 16px rgba(0,0,0,0.08);
  --shadow-inner: inset 0 2px 8px rgba(0,0,0,0.08);
  --shadow-none:  none;

  /* THIRD-PARTY BRAND COLORS (v10.1) — use for social share, integrations */
  --brand-twitter:          #1DA1F2;
  --brand-twitter-hover:    #1a8cd8;
  --brand-linkedin:         #0A66C2;
  --brand-linkedin-hover:   #095196;
  --brand-facebook:         #1877F2;
  --brand-facebook-hover:   #166fe5;
  --brand-whatsapp:         #25D366;
  --brand-whatsapp-hover:   #22c35e;
  --brand-youtube:          #FF0000;
  --brand-youtube-hover:    #cc0000;
  --brand-instagram:        #E4405F;
  --brand-instagram-hover:  #d62e4c;
  --brand-github:           #181717;
  --brand-github-hover:     #333333;
  --brand-discord:          #5865F2;
  --brand-discord-hover:    #4752c4;
}

/* THEME COLORS — both modes, always together */
[data-theme="light"] {
  --bg:          #XXXXXX;  /* fill from table below */
  --surface:     #XXXXXX;
  --surface-up:  #XXXXXX;
  --text:        #XXXXXX;
  --text-2:      #XXXXXX;
  --muted:       #XXXXXX;
  --border:      rgba(X,X,X,0.XX);
  --accent:      #XXXXXX;
  --accent-h:    #XXXXXX;  /* hover state */
  --overlay:     rgba(X,X,X,0.XX);
  --gradient:    /* set from gradient table below */;
}
[data-theme="dark"] { /* same tokens, dark values */ }
```

### Token Values by Design Language

| Token | Luxury Light | Luxury Dark | Premium Light | Premium Dark | Minimal Light | Minimal Dark |
|-------|-------------|-------------|---------------|--------------|---------------|--------------|
| `--bg` | `#FAF9F7` | `#0E0D0B` | `#FFFFFF` | `#0A0B0E` | `#FFFFFF` | `#111111` |
| `--surface` | `#F2F0EC` | `#1A1814` | `#F7F8FA` | `#13151A` | `#F5F5F5` | `#1A1A1A` |
| `--surface-up` | `#EAEAE0` | `#242018` | `#EDEEF2` | `#1C1F28` | `#EBEBEB` | `#222222` |
| `--text` | `#1A1814` | `#F5F0E8` | `#0F1117` | `#EDF0F5` | `#111111` | `#F0F0F0` |
| `--text-2` | `#6B6558` | `#A09880` | `#4A5060` | `#8892A0` | `#666666` | `#888888` |
| `--muted` | `#9C9890` | `#6B6860` | `#6C737A` | `#4A5060` | `#AAAAAA` | `#555555` |
| `--accent` | `#C4A882` | `#D4B896` | `#0066FF` | `#4D8EFF` | `#111111` | `#F0F0F0` |
| `--radius-sm` | `0px` | `0px` | `6px` | `6px` | `2px` | `2px` |
| `--radius-md` | `0px` | `0px` | `12px` | `12px` | `4px` | `4px` |
| `--radius-lg` | `0px` | `0px` | `20px` | `20px` | `8px` | `8px` |

| Token | Expressive Light | Expressive Dark | Warm Light | Warm Dark | Technical Light | Technical Dark |
|-------|-----------------|-----------------|------------|-----------|----------------|----------------|
| `--bg` | `#F5F5F5` | `#0A0A0A` | `#FDFAF6` | `#1A1410` | `#F6F8FA` | `#0D1117` |
| `--surface` | `#EBEBEB` | `#141414` | `#F5EFE8` | `#231C17` | `#FFFFFF` | `#161B22` |
| `--text` | `#111111` | `#F5F5F5` | `#2D2520` | `#F0E8DF` | `#1C2128` | `#C9D1D9` |
| `--accent` | `#FF3B00` | `#FF5C1F` | `#E8603C` | `#F07050` | `#0969DA` | `#58A6FF` |
| `--radius-sm` | `0px or 999px` | same | `12px` | `12px` | `4px` | `4px` |
| `--radius-md` | `0px or 999px` | same | `20px` | `20px` | `6px` | `6px` |
| `--radius-lg` | `0px or 999px` | same | `28px` | `28px` | `8px` | `8px` |

### Elevation (Shadow) Tokens per Language

| Language | `--shadow-sm` | `--shadow-md` | `--shadow-lg` | Notes |
|----------|--------------|--------------|--------------|-------|
| **Luxury** | `0 1px 4px rgba(0,0,0,0.04)` | `0 4px 20px rgba(0,0,0,0.06)` | `0 12px 48px rgba(0,0,0,0.08)` | Ultra-subtle, barely visible |
| **Premium** | default | default | default | Standard system above |
| **Minimalist** | `none` | `none` | `0 2px 8px rgba(0,0,0,0.06)` | Borders over shadows |
| **Expressive** | `0 2px 8px rgba(255,59,0,0.12)` | `0 6px 24px rgba(255,59,0,0.18)` | `0 16px 48px rgba(255,59,0,0.24)` | Colored accent shadows |
| **Warm** | `0 2px 12px rgba(232,96,60,0.08)` | `0 6px 24px rgba(232,96,60,0.12)` | `0 16px 40px rgba(232,96,60,0.16)` | Warm-tinted shadows |
| **Technical** | `none` | `0 1px 3px rgba(0,0,0,0.4)` (dark) | `0 4px 16px rgba(0,0,0,0.5)` | No shadows in light, strong in dark |

### Animation Tokens per Language

| Language | Duration multiplier | Easing | Micro-interaction feel |
|----------|--------------------|---------|-----------------------|
| **Luxury** | 1.5–2× (slow) | `--ease-luxury` | Deliberate, cinematic |
| **Premium** | 1× (standard) | `--ease-out` | Polished, instant feedback |
| **Minimalist** | 1× | `--ease-sharp` | Crisp, no bounce |
| **Expressive** | 0.7× (fast) | `--ease-spring` | Bouncy, energetic |
| **Warm** | 1× | `--ease-spring` | Springy, gentle |
| **Technical** | 0.5× (instant) | `--ease-sharp` | Zero decoration |

---

## STEP 2 — Gradient System (Intentional, Not Random)

**Rule**: Gradients are a brand decision, not decoration. Luxury and Minimalist rarely use gradient. Technical uses only for status/data. Expressive uses aggressively. Premium uses subtly.

### Gradient Tokens per Language

```css
/* LUXURY — no gradient. Use opacity and shadow instead. */
/* If forced: single-direction tint, never rainbow */
--gradient: linear-gradient(180deg, var(--bg) 0%, var(--surface) 100%);
--gradient-hero: linear-gradient(to bottom, transparent 60%, rgba(14,13,11,0.8) 100%);

/* PREMIUM MODERN — subtle, cool, directional */
--gradient: linear-gradient(135deg, #0066FF08 0%, #00C89608 100%);
--gradient-hero: linear-gradient(135deg, #0066FF 0%, #0052CC 100%);
--gradient-mesh: radial-gradient(ellipse 80% 80% at 20% 50%, #0066FF18 0%, transparent 60%),
                 radial-gradient(ellipse 60% 60% at 80% 20%, #00C89612 0%, transparent 60%);
--gradient-card: linear-gradient(135deg, var(--surface) 0%, var(--surface-up) 100%);

/* MINIMALIST — never gradient. If used: monochrome only */
--gradient: none;
--gradient-hero: linear-gradient(180deg, var(--bg) 0%, #F0F0F0 100%);

/* EXPRESSIVE — vivid, bold, unexpected angles */
--gradient: linear-gradient(135deg, #FF3B00 0%, #FF9500 50%, #FFCC00 100%);
--gradient-hero: linear-gradient(160deg, #0A0A0A 0%, #1A0A00 60%, #2D0A00 100%);
--gradient-text: linear-gradient(135deg, #FF3B00, #FF9500); /* for gradient text */
/* Usage: background: var(--gradient-text); -webkit-background-clip: text; color: transparent; */

/* EDITORIAL — none. Typography IS the design. */
--gradient: none;
--gradient-hero: none; /* full-bleed color blocks or solid image overlay */

/* WARM & HUMAN — soft, organic, warm-toned */
--gradient: linear-gradient(135deg, #FFF5EE 0%, #FFE8D6 100%);
--gradient-hero: linear-gradient(160deg, #FDFAF6 0%, #F5EFE8 100%);
--gradient-card: linear-gradient(135deg, #FFF8F0 0%, var(--surface) 100%);

/* TECHNICAL — only for status/data visualization */
--gradient: none; /* no decorative gradients */
--gradient-success: linear-gradient(90deg, #1A7F37, #3FB950);
--gradient-danger:  linear-gradient(90deg, #CF222E, #F85149);
--gradient-bar:     linear-gradient(90deg, var(--accent) 0%, color-mix(in srgb, var(--accent) 60%, transparent));
```

### Gradient Usage Rules

```css
/* Gradient TEXT — Expressive only */
.gradient-text {
  background: var(--gradient-text);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

/* Gradient BORDER — Premium, Expressive */
.gradient-border {
  border: 1px solid transparent;
  background:
    linear-gradient(var(--surface), var(--surface)) padding-box,
    var(--gradient-hero) border-box;
}

/* Gradient OVERLAY on image backgrounds */
.img-overlay {
  position: relative;
}
.img-overlay::after {
  content: '';
  position: absolute;
  inset: 0;
  background: var(--gradient-hero);
  pointer-events: none;
}

/* Gradient MESH background (Premium) */
.mesh-bg {
  background: var(--bg);
  background-image: var(--gradient-mesh);
}
```

---


---

## STEP 3 — DTCG / W3C TOKEN FORMAT (Style Dictionary Compatible)

The **Design Token Community Group (DTCG)** format is the W3C standard for token interoperability.
Use this when exporting from Figma or syncing tokens via Style Dictionary or Theo.

### DTCG JSON Structure

```json
{
  "$schema": "https://design-tokens.org/schema.json",
  "color": {
    "bg":      { "$value": "#FFFFFF",  "$type": "color",     "$description": "Page background" },
    "surface": { "$value": "#F7F8FA",  "$type": "color",     "$description": "Card/panel surfaces" },
    "accent":  { "$value": "#0066FF",  "$type": "color",     "$description": "Primary action color" },
    "text":    { "$value": "#0F1117",  "$type": "color",     "$description": "Primary text" },
    "text-2":  { "$value": "#4A5060",  "$type": "color",     "$description": "Secondary text" },
    "border":  { "$value": "rgba(15,17,23,0.08)", "$type": "color", "$description": "Borders/dividers" }
  },
  "spacing": {
    "space-1":  { "$value": "4px",   "$type": "dimension" },
    "space-2":  { "$value": "8px",   "$type": "dimension" },
    "space-3":  { "$value": "12px",  "$type": "dimension" },
    "space-4":  { "$value": "16px",  "$type": "dimension" },
    "space-6":  { "$value": "24px",  "$type": "dimension" },
    "space-8":  { "$value": "32px",  "$type": "dimension" },
    "space-12": { "$value": "48px",  "$type": "dimension" },
    "space-16": { "$value": "64px",  "$type": "dimension" },
    "space-20": { "$value": "80px",  "$type": "dimension" },
    "space-24": { "$value": "96px",  "$type": "dimension" }
  },
  "borderRadius": {
    "radius-sm":   { "$value": "6px",    "$type": "dimension" },
    "radius-md":   { "$value": "12px",   "$type": "dimension" },
    "radius-lg":   { "$value": "20px",   "$type": "dimension" },
    "radius-full": { "$value": "9999px", "$type": "dimension" }
  },
  "fontFamily": {
    "display": { "$value": "'Instrument Serif', serif", "$type": "fontFamily" },
    "body":    { "$value": "'Plus Jakarta Sans', sans-serif", "$type": "fontFamily" },
    "mono":    { "$value": "'JetBrains Mono', monospace", "$type": "fontFamily" }
  },
  "shadow": {
    "shadow-md": {
      "$value": "0 4px 16px rgba(0,0,0,0.10), 0 2px 4px rgba(0,0,0,0.06)",
      "$type": "shadow"
    }
  }
}
```

### 3-Tier Mapping (Primitive → Semantic → Component)

```json
{
  "_primitives": {
    "blue-600":    { "$value": "#0066FF", "$type": "color" },
    "neutral-900": { "$value": "#0F1117", "$type": "color" }
  },
  "semantic": {
    "accent":  { "$value": "{_primitives.blue-600}",    "$type": "color" },
    "text":    { "$value": "{_primitives.neutral-900}", "$type": "color" }
  },
  "component": {
    "button-bg":        { "$value": "{semantic.accent}",  "$type": "color" },
    "button-text":      { "$value": "#FFFFFF",            "$type": "color" },
    "button-bg-hover":  { "$value": "#0052CC",            "$type": "color" }
  }
}
```

### Style Dictionary Config (DTCG → CSS)

```javascript
// style-dictionary.config.mjs
export default {
  source: ['tokens.dtcg.json'],
  platforms: {
    css: {
      transformGroup: 'css',
      files: [{
        destination: 'src/styles/tokens.generated.css',
        format: 'css/variables',
        options: { selector: ':root', outputReferences: true },
      }],
    },
    ts: {
      transformGroup: 'js',
      files: [{
        destination: 'src/styles/tokens.ts',
        format: 'javascript/es6',
      }],
    },
  },
};
// Outputs: :root { --color-accent: #0066FF; --spacing-space-4: 16px; ... }
```

---

## STEP 4 — RESPONSIVE BREAKPOINT TOKENS

**Rule**: Never hardcode breakpoint values. Use these semantic tokens in all media queries for consistency across the design system.

### Breakpoint Token System

```css
:root {
  /* RESPONSIVE BREAKPOINTS — use these in @media queries */
  --breakpoint-sm: 640px;   /* Small screens / large phones */
  --breakpoint-md: 768px;   /* Tablets */
  --breakpoint-lg: 1024px;  /* Laptops / small desktops */
  --breakpoint-xl: 1280px;  /* Large desktops */
  --breakpoint-2xl: 1536px; /* Extra large screens */
}
```

### Usage in Media Queries

```css
/* Use token values in @media — never hardcode breakpoints */
@media (min-width: 640px)  { /* --breakpoint-sm */ }
@media (min-width: 768px)  { /* --breakpoint-md */ }
@media (min-width: 1024px) { /* --breakpoint-lg */ }
@media (min-width: 1280px) { /* --breakpoint-xl */ }

/* Note: CSS custom properties cannot be used directly in @media queries.
   Instead, use the same values defined above. The tokens serve as the 
   single source of truth — update :root tokens and @media values together. */
```

### Breakpoint Tokens per Design Language

| Language | Mobile-first breakpoint | Desktop target | Max container |
|----------|------------------------|----------------|---------------|
| **Luxury** | 768px | 1280px | 1440px |
| **Premium** | 640px | 1024px | 1280px |
| **Minimalist** | 768px | 1280px | 1200px |
| **Expressive** | 640px | 1024px | 1440px |
| **Technical** | 640px | 1024px | 1600px (wide) |
| **Warm** | 640px | 1024px | 1200px |

### JavaScript Access

```javascript
// Access breakpoint values in JS for responsive logic
const breakpoints = {
  sm:  parseInt(getComputedStyle(document.documentElement).getPropertyValue('--breakpoint-sm')),
  md:  parseInt(getComputedStyle(document.documentElement).getPropertyValue('--breakpoint-md')),
  lg:  parseInt(getComputedStyle(document.documentElement).getPropertyValue('--breakpoint-lg')),
  xl:  parseInt(getComputedStyle(document.documentElement).getPropertyValue('--breakpoint-xl')),
};

// Usage
const isMobile = window.innerWidth < breakpoints.md;
```

> **Important**: Use these breakpoint tokens in @media (min-width: VALUE) — never hardcode pixel values like `768px` directly. This ensures consistency when breakpoints are updated.

