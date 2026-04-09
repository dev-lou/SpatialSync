# UI/UX Components, Alerts, Nav & Grid

> Dark mode setup, global CSS reset, CDN stack, SweetAlert2 themes, spacing, responsive navigation, grid layouts.

## STEP 3 — Dark/Light Mode System

### Anti-FOWT (Flash of Wrong Theme) — ALWAYS inline in `<head>`

```html
<script>
(function(){
  var t=localStorage.getItem('theme')||
    (window.matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light');
  document.documentElement.setAttribute('data-theme',t);
})();
</script>
```

### Toggle JS (universal, all stacks)

```javascript
const applyTheme = t => {
  document.documentElement.setAttribute('data-theme', t);
  localStorage.setItem('theme', t);
};
const toggleTheme = () => {
  applyTheme(document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
};
// Init
applyTheme(localStorage.getItem('theme') || (matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'));
// System change listener
matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
  if (!localStorage.getItem('theme')) applyTheme(e.matches ? 'dark' : 'light');
});
```

### React / Next.js Theme Hook

```jsx
// hooks/useTheme.js — import in _app.js / layout.jsx
import { useEffect, useState } from 'react';
export function useTheme() {
  const [theme, setTheme] = useState('light');
  useEffect(() => {
    const saved = localStorage.getItem('theme') ||
      (matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    setTheme(saved);
    document.documentElement.setAttribute('data-theme', saved);
  }, []);
  const toggle = () => {
    const next = theme === 'dark' ? 'light' : 'dark';
    setTheme(next);
    document.documentElement.setAttribute('data-theme', next);
    localStorage.setItem('theme', next);
  };
  return { theme, toggle };
}
```

### Toggle Button Styles by Language

```css
/* Luxury — minimal text toggle */
.theme-toggle-luxury {
  font-size: 10px; letter-spacing: 0.12em; text-transform: uppercase;
  border: 1px solid var(--border); padding: 8px 16px; background: none;
  cursor: pointer; color: var(--text); transition: var(--transition-slow);
}

/* Premium / Technical — icon button */
.theme-toggle-icon {
  width: 40px; height: 40px; border-radius: var(--radius-md);
  border: 1px solid var(--border); background: var(--surface);
  display: grid; place-items: center; cursor: pointer;
  transition: var(--transition-base); color: var(--text);
}
.theme-toggle-icon:hover { background: var(--surface-up); }

/* Warm — pill toggle with sliding circle */
.theme-toggle-pill {
  width: 52px; height: 28px; border-radius: var(--radius-full);
  background: var(--surface-up); border: 1px solid var(--border);
  position: relative; cursor: pointer; transition: var(--transition-spring);
}
.theme-toggle-pill::after {
  content: ''; position: absolute; top: 3px; left: 3px;
  width: 20px; height: 20px; border-radius: 50%;
  background: var(--accent); transition: var(--transition-spring);
}
[data-theme="dark"] .theme-toggle-pill::after { transform: translateX(24px); }
```

---

## STEP 4 — Global CSS Reset

```css
/* ===== PASTE IN EVERY PROJECT — base.css / globals.css ===== */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

html {
  font-size: 16px; scroll-behavior: smooth;
  -webkit-text-size-adjust: 100%;
  text-rendering: optimizeLegibility;
  -webkit-font-smoothing: antialiased;
}

body {
  font-family: var(--font-body);
  background: var(--bg);
  color: var(--text);
  line-height: 1.6;
  overflow-x: hidden;
  transition: background 0.3s, color 0.3s;
}

:focus-visible { outline: 2px solid var(--accent); outline-offset: 3px; }

img, video, iframe, svg { max-width: 100%; display: block; height: auto; }

button { cursor: pointer; border: none; background: none; font: inherit; color: inherit; }
input, textarea, select { font: inherit; color: inherit; border: none; outline: none; background: transparent; }
a { color: inherit; text-decoration: none; }
nav ul, nav ol { list-style: none; }

.container {
  width: 100%; max-width: var(--container-max);
  margin-inline: auto; padding-inline: var(--container-gutter, 24px);
}
@media (min-width: 768px)  { :root { --container-gutter: 40px; } }
@media (min-width: 1280px) { :root { --container-gutter: 48px; } }

.section { padding-block: var(--section-space-sm); }
@media (min-width: 768px)  { .section { padding-block: var(--section-space-md); } }
@media (min-width: 1024px) { .section { padding-block: var(--section-space-lg); } }

.sr-only { position: absolute; width: 1px; height: 1px; overflow: hidden;
  clip: rect(0,0,0,0); white-space: nowrap; }
```

---

## STEP 5 — CDN Stack

### Universal (every project)

```html
<!-- In <head>, after anti-FOWT script -->
<link rel="preconnect" href="https://fonts.bunny.net">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css">
<!-- Before </body> -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
<script>AOS.init({duration:700,once:true,offset:80});lucide.createIcons();</script>
```

### Language-Specific CDNs

| Language | Additional CDNs |
|----------|----------------|
| **Luxury** | GSAP 3.12 + ScrollTrigger, Swiper 11 |
| **Premium Modern** | GSAP 3.12 + ScrollTrigger, Chart.js 4, Tippy.js 6 |
| **Minimalist** | Nothing extra — restraint is the brand |
| **Expressive** | GSAP 3.12 + TextPlugin, tsParticles 2, Lottie-web 5 |
| **Editorial** | Prism.js (Tomorrow theme), no others |
| **Warm & Human** | Lottie-web 5, Swiper 11 |
| **Technical** | Prism.js, Chart.js 4, Sortable.js, Flatpickr |

```html
<!-- GSAP + ScrollTrigger -->
<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/ScrollTrigger.min.js"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<!-- Swiper -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<!-- Lottie -->
<script src="https://cdn.jsdelivr.net/npm/lottie-web@5.12.2/build/player/lottie.min.js"></script>
<!-- Sortable -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<!-- Flatpickr -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
```

### Font CDN by Language (Bunny Fonts — GDPR safe)

| Language | Font URL |
|----------|----------|
| Luxury | `fonts.bunny.net/css?family=cormorant-garamond:300,400,500,600\|dm-sans:300,400,500` |
| Premium Modern | `fonts.bunny.net/css?family=plus-jakarta-sans:300,400,500,600,700\|instrument-serif:400` |
| Minimalist | `fonts.bunny.net/css?family=space-grotesk:300,400,500,700` |
| Expressive | `fonts.bunny.net/css?family=syne:700,800\|nunito:400,500,600` |
| Editorial | `fonts.bunny.net/css?family=playfair-display:700,900\|source-serif-4:300,400,600` |
| Warm & Human | `fonts.bunny.net/css?family=nunito:400,600,700,800\|nunito-sans:300,400,500,600` |
| Technical | `fonts.bunny.net/css?family=jetbrains-mono:400,500,700\|ibm-plex-sans:300,400,500` |

---

## Placeholder Images by Design Language (2026)

| Language | Hero placeholder | Feature / card placeholder |
|----------|-----------------|---------------------------|
| Luxury | `https://source.unsplash.com/1600x900/?luxury,fashion,marble` | `https://source.unsplash.com/800x600/?jewelry,elegance` |
| Premium | `https://source.unsplash.com/1600x900/?workspace,technology,abstract` | `https://source.unsplash.com/800x600/?dashboard,productivity` |
| Minimalist | `https://source.unsplash.com/1600x900/?white,minimal,architecture` | `https://source.unsplash.com/800x600/?plain,texture` |
| Expressive | `https://source.unsplash.com/1600x900/?neon,concert,graffiti` | `https://source.unsplash.com/800x600/?colorful,urban` |
| Editorial | `https://source.unsplash.com/1600x900/?magazine,print,editorial` | `https://source.unsplash.com/800x600/?books,reading` |
| Warm | `https://source.unsplash.com/1600x900/?people,community,smiling` | `https://source.unsplash.com/800x600/?nature,plants` |
| Technical | `https://source.unsplash.com/1600x900/?code,terminal,server` | `https://source.unsplash.com/800x600/?data,circuit` |

> **Note**: These are placeholder URLs – replace with actual project assets in production.

---

## Icon Fallback for Accessibility

If the Lucide CDN fails or an icon doesn't load, provide a text fallback using `aria-label` or a hidden span. Example:

```html
<button aria-label="Menu">
  <i data-lucide="menu"></i>
  <span class="sr-only">Menu</span>
</button>
```

This ensures screen readers always announce the action even if the icon is missing.

---

## STEP 6 — Modern Alert System

### SweetAlert2 Theme CSS per Language

```css
/* LUXURY — sharp, minimal, uppercase */
.swal-luxury .swal2-popup {
  background:var(--surface); border:1px solid var(--border); border-radius:0;
  font-family:var(--font-body); padding:48px; box-shadow:none;
}
.swal-luxury .swal2-title { font-family:var(--font-display); font-size:1.3rem;
  font-weight:300; letter-spacing:0.1em; text-transform:uppercase; color:var(--text); }
.swal-luxury .swal2-confirm { background:transparent!important; border:1px solid var(--text)!important;
  color:var(--text)!important; border-radius:0!important; font-size:10px!important;
  letter-spacing:0.15em!important; text-transform:uppercase!important; padding:14px 40px!important; }

/* PREMIUM — card-like, accent CTA */
.swal-premium .swal2-popup {
  background:var(--surface); border:1px solid var(--border); border-radius:16px;
  font-family:var(--font-body); padding:32px;
  box-shadow:0 24px 64px rgba(0,0,0,0.15);
}
.swal-premium .swal2-title { font-size:1.2rem; font-weight:600;
  letter-spacing:-0.02em; color:var(--text); }
.swal-premium .swal2-confirm { background:var(--accent)!important;
  border-radius:8px!important; font-weight:600!important; padding:12px 24px!important; }

/* TECHNICAL — compact, left-aligned, monospace */
.swal-technical .swal2-popup {
  background:var(--surface); border:1px solid var(--border); border-radius:4px;
  font-family:var(--font-body); padding:20px 24px; text-align:left;
  box-shadow:0 8px 32px rgba(0,0,0,0.5);
}
.swal-technical .swal2-title { font-size:0.9rem; font-weight:600;
  text-align:left; color:var(--text); }
.swal-technical .swal2-confirm { background:var(--accent)!important;
  border-radius:4px!important; font-size:13px!important; padding:8px 16px!important; }

/* WARM — pill buttons, rounded, friendly */
.swal-warm .swal2-popup {
  background:var(--surface); border-radius:24px; font-family:var(--font-body);
  padding:32px; box-shadow:0 16px 48px rgba(0,0,0,0.08);
}
.swal-warm .swal2-title { font-size:1.25rem; font-weight:700; color:var(--text); }
.swal-warm .swal2-confirm { background:var(--accent)!important;
  border-radius:999px!important; font-weight:700!important; padding:14px 32px!important; }
```

### Alert Functions (paste in every project)

```javascript
// Set this to match your design language
const THEME = 'swal-premium'; // swal-luxury | swal-premium | swal-technical | swal-warm

const swal = {
  success: (title, text='') => Swal.fire({
    icon:'success', title, text, customClass:{popup:THEME},
    confirmButtonText:'Got it', timer:3000, timerProgressBar:true,
  }),
  error: (title, text='') => Swal.fire({
    icon:'error', title, text, customClass:{popup:THEME},
  }),
  confirm: async (title, text='', ok='Confirm', cancel='Cancel') => {
    const res = await Swal.fire({
      title, text, icon:'question', showCancelButton:true,
      confirmButtonText:ok, cancelButtonText:cancel, customClass:{popup:THEME}
    });
    return res.isConfirmed;
  },
  toast: (title, icon='info') => {
    const Toast = Swal.mixin({
      toast:true, position:'top-end', showConfirmButton:false,
      timer:3000, timerProgressBar:true, customClass:{popup:THEME}
    });
    Toast.fire({icon, title});
  }
};
```

---

## STEP 7 — Print Styles

**Rule**: Every page must have print styles. Users print invoices, receipts, articles, and documentation. Without print CSS, your page will look broken on paper.

### Print CSS Reset

```css
@media print {
  /* Hide non-essential UI elements */
  .no-print,
  .nav,
  .navbar,
  .footer,
  .sidebar,
  .cookie-banner,
  .chat-widget,
  .back-to-top,
  button:not(.print-btn),
  [role="navigation"],
  [role="complementary"] {
    display: none !important;
  }

  /* Reset backgrounds and colors for ink saving */
  body {
    background: white !important;
    color: black !important;
    font-size: 12pt;
    line-height: 1.5;
  }

  /* Ensure text is readable */
  * {
    color: black !important;
    background: transparent !important;
    box-shadow: none !important;
    text-shadow: none !important;
  }

  /* Show URLs after links */
  a[href]::after {
    content: " (" attr(href) ")";
    font-size: 0.9em;
    color: #666 !important;
  }

  /* Don't show URL for internal/anchor links */
  a[href^="#"]::after,
  a[href^="javascript"]::after {
    content: "";
  }

  /* Page break control */
  .page-break {
    page-break-before: always;
  }

  .no-break {
    page-break-inside: avoid;
  }

  /* Keep headings with their content */
  h1, h2, h3, h4, h5, h6 {
    page-break-after: avoid;
  }

  /* Keep images with captions */
  img, figure {
    page-break-inside: avoid;
  }

  /* Ensure images print correctly */
  img {
    max-width: 100% !important;
    filter: none !important;
  }

  /* Tables */
  table {
    border-collapse: collapse;
  }

  th, td {
    border: 1px solid #ccc !important;
    padding: 8px;
  }

  thead {
    display: table-header-group; /* Repeat headers on each page */
  }

  tr {
    page-break-inside: avoid;
  }
}
```

### Print Button Component

```html
<!-- Token-styled print button -->
<button class="btn btn--outline btn--sm print-btn" onclick="window.print()" aria-label="Print this page">
  <i data-lucide="printer"></i>
  <span>Print</span>
</button>
```

```css
/* Print button - visible on screen, triggers print dialog */
.print-btn {
  display: inline-flex;
  align-items: center;
  gap: var(--space-2);
  padding: var(--space-2) var(--space-4);
  background: var(--surface);
  color: var(--text);
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
  font-size: var(--text-sm);
  font-weight: 500;
  cursor: pointer;
  transition: background var(--transition-micro), border-color var(--transition-micro);
}

.print-btn:hover {
  background: var(--surface-up);
  border-color: var(--accent);
}

.print-btn:focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 2px;
}

/* Hide print button in print output */
@media print {
  .print-btn {
    display: none !important;
  }
}
```

### Print-Specific Classes

```css
/* Use these classes to control print visibility */

/* Elements that should ONLY appear in print */
.print-only {
  display: none !important;
}

@media print {
  .print-only {
    display: block !important;
  }
}

/* Elements that should NEVER appear in print */
.screen-only,
.no-print {
  /* Visible on screen */
}

@media print {
  .screen-only,
  .no-print {
    display: none !important;
  }
}
```

### Print Header/Footer (Page Margins)

```css
@page {
  size: A4;
  margin: 2cm;
}

/* First page can have different margins */
@page :first {
  margin-top: 3cm;
}

/* Add page numbers (browser support varies) */
@page {
  @bottom-center {
    content: "Page " counter(page) " of " counter(pages);
    font-size: 10pt;
    color: #666;
  }
}
```
