# UI/UX Interactive Components 2026

> Covers: image shape variants, mega-menu dropdowns (Tesla-style), navbar variants,
> custom selects/dropdowns/droptops, date & time pickers, toggles, and button systems —
> all per design language. No generic browser defaults anywhere.

---

## STEP 27 — Image Shape Variants (Anti-flat, Unique Frames)

**Rule**: Never use only square images. Mix shapes per design language for visual rhythm.
Each shape uses `clip-path` or `border-radius` — no extra wrapper divs needed.

### Shape Library

```css
/* ── BASIC ─────────────────────────────────────────── */
.img-square   { border-radius: 0; }
.img-rounded  { border-radius: var(--radius-lg); }
.img-pill     { border-radius: 9999px; }
.img-circle   { border-radius: 50%; aspect-ratio: 1; object-fit: cover; }

/* ── GEOMETRIC ──────────────────────────────────────── */
.img-hexagon {
  clip-path: polygon(50% 0%, 100% 25%, 100% 75%, 50% 100%, 0% 75%, 0% 25%);
  aspect-ratio: 1; object-fit: cover;
}
.img-diamond {
  clip-path: polygon(50% 0%, 100% 50%, 50% 100%, 0% 50%);
  aspect-ratio: 1; object-fit: cover;
}
.img-triangle {
  clip-path: polygon(50% 0%, 100% 100%, 0% 100%);
  aspect-ratio: 1; object-fit: cover;
}
.img-pentagon {
  clip-path: polygon(50% 0%, 100% 38%, 82% 100%, 18% 100%, 0% 38%);
  aspect-ratio: 1; object-fit: cover;
}
.img-star {
  clip-path: polygon(50% 0%, 61% 35%, 98% 35%, 68% 57%, 79% 91%,
                     50% 70%, 21% 91%, 32% 57%, 2% 35%, 39% 35%);
  aspect-ratio: 1; object-fit: cover;
}
.img-arch {
  border-radius: 999px 999px 0 0;
  aspect-ratio: 3/4; object-fit: cover;
}
.img-arch-bottom {
  border-radius: 0 0 999px 999px;
  aspect-ratio: 3/4; object-fit: cover;
}
.img-squircle {
  border-radius: 30%;
  aspect-ratio: 1; object-fit: cover;
}

/* ── ORGANIC / BLOB ─────────────────────────────────── */
/* Each blob is unique — use different ones for variety */
.img-blob-1 {
  clip-path: polygon(
    30% 0%, 70% 0%, 100% 30%, 100% 70%,
    70% 100%, 30% 100%, 0% 70%, 0% 30%
  );
  border-radius: 60% 40% 30% 70% / 60% 30% 70% 40%;
  aspect-ratio: 1; object-fit: cover;
}
.img-blob-2 {
  border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%;
  aspect-ratio: 1; object-fit: cover;
}
.img-blob-3 {
  border-radius: 70% 30% 30% 70% / 70% 70% 30% 30%;
  aspect-ratio: 1; object-fit: cover;
}

/* Animate blob on hover for expressive/warm */
.img-blob-animated {
  border-radius: 60% 40% 30% 70% / 60% 30% 70% 40%;
  transition: border-radius 1.2s var(--ease-luxury);
  aspect-ratio: 1; object-fit: cover;
}
.img-blob-animated:hover {
  border-radius: 30% 70% 70% 30% / 70% 30% 30% 70%;
}

/* ── EDITORIAL / POLAROID ────────────────────────────── */
.img-polaroid {
  padding: 12px 12px 40px;
  background: white;
  box-shadow: var(--shadow-md);
  border-radius: 2px;
  transform: rotate(-1.5deg);
  transition: transform var(--transition-base);
  display: inline-block;
}
.img-polaroid:hover { transform: rotate(0deg) scale(1.02); }
.img-polaroid img  { width: 100%; display: block; }

/* ── FRAME BORDER VARIANTS ──────────────────────────── */
.img-framed {
  padding: 6px;
  border: 2px solid var(--accent);
  border-radius: var(--radius-md);
}
.img-framed-offset {
  position: relative;
}
.img-framed-offset::after {
  content: '';
  position: absolute;
  inset: -8px;
  border: 2px solid var(--accent);
  border-radius: var(--radius-md);
  pointer-events: none;
  transition: inset var(--transition-base);
}
.img-framed-offset:hover::after { inset: -12px; }
```

### Shape Usage Per Design Language

| Shape | Luxury | Premium | Minimalist | Expressive | Warm | Technical |
|-------|--------|---------|------------|------------|------|-----------|
| Square | ✅ hero | ✅ product | ✅ primary | — | — | ✅ screenshot |
| Arch | ✅ portrait | — | ✅ accent | — | ✅ people | — |
| Circle | — | ✅ avatar | ✅ avatar | — | ✅ team | ✅ avatar |
| Blob | — | — | — | ✅ hero | ✅ feature | — |
| Diamond | ✅ accent | — | — | ✅ grid | — | — |
| Hexagon | — | ✅ tech | — | ✅ grid | — | ✅ icon |
| Polaroid | — | — | — | ✅ gallery | ✅ memory | — |
| Squircle | — | ✅ app icon | — | — | ✅ feature | — |
| Framed-offset | ✅ feature | ✅ feature | — | — | — | — |

### Mixed-Shape Grid (Anti-slop pattern)

```html
<!-- Use varied shapes in the same grid for visual rhythm -->
<div class="shape-grid">
  <img class="img-arch"     src="..." alt="..." width="400" height="533" loading="lazy">
  <img class="img-blob-2"   src="..." alt="..." width="400" height="400" loading="lazy">
  <img class="img-squircle" src="..." alt="..." width="400" height="400" loading="lazy">
  <img class="img-diamond"  src="..." alt="..." width="400" height="400" loading="lazy">
</div>
```

```css
.shape-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: var(--space-6);
  align-items: center;
}
@media (min-width: 768px) {
  .shape-grid { grid-template-columns: repeat(4, 1fr); }
}
```

---

## STEP 28 — Navbar Variants & Mega-Menu Dropdown

**Rule**: Navbar style is set by design language. Never reuse the same nav across systems.

### Navbar Variants Per Language

```css
/* LUXURY — ultra-thin, no background, fades in on scroll */
.nav-luxury {
  position: fixed; top: 0; left: 0; right: 0; z-index: 100;
  padding: var(--space-6) var(--container-gutter);
  display: flex; justify-content: space-between; align-items: center;
  background: transparent;
  transition: background var(--transition-slow), padding var(--transition-slow);
}
.nav-luxury.scrolled {
  background: color-mix(in srgb, var(--bg) 90%, transparent);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  padding: var(--space-4) var(--container-gutter);
}

/* PREMIUM — glass card with border */
.nav-premium {
  position: sticky; top: 12px; z-index: 100;
  margin: 12px auto; max-width: calc(var(--container-max) - 48px);
  padding: var(--space-3) var(--space-6);
  display: flex; justify-content: space-between; align-items: center;
  background: color-mix(in srgb, var(--surface) 85%, transparent);
  backdrop-filter: blur(24px);
  border: 1px solid var(--border);
  border-radius: var(--radius-full); /* pill shape */
  box-shadow: var(--shadow-md);
}

/* MINIMALIST — full-width, single bottom border only */
.nav-minimal {
  position: sticky; top: 0; z-index: 100;
  width: 100%;
  padding: var(--space-4) var(--container-gutter);
  display: flex; justify-content: space-between; align-items: center;
  background: var(--bg);
  border-bottom: 1px solid var(--border);
}

/* EXPRESSIVE — full color, asymmetric, bold */
.nav-expressive {
  position: sticky; top: 0; z-index: 100;
  padding: var(--space-4) var(--container-gutter);
  display: flex; justify-content: space-between; align-items: center;
  background: var(--surface);
  border-bottom: 3px solid var(--accent);
}

/* TECHNICAL — dense, sidebar-ready, compact */
.nav-technical {
  position: sticky; top: 0; z-index: 100;
  padding: var(--space-2) var(--space-6);
  display: flex; justify-content: space-between; align-items: center;
  background: var(--surface);
  border-bottom: 1px solid var(--border);
  font-size: var(--text-sm);
  min-height: 48px;
}

/* WARM — rounded, soft shadow, friendly */
.nav-warm {
  position: sticky; top: 0; z-index: 100;
  padding: var(--space-3) var(--container-gutter);
  display: flex; justify-content: space-between; align-items: center;
  background: var(--surface);
  border-radius: 0 0 var(--radius-lg) var(--radius-lg);
  box-shadow: 0 4px 24px rgba(0,0,0,0.06);
}
```

### Mega-Menu Dropdown (Tesla-style)

```html
<nav class="nav-premium" role="navigation" aria-label="Main navigation">
  <a href="/" class="nav-logo">BRAND</a>

  <ul class="nav-links" role="list">
    <li class="nav-item has-mega">
      <button class="nav-link" aria-expanded="false" aria-haspopup="true">
        Products <i data-lucide="chevron-down" class="nav-chevron"></i>
      </button>
      <!-- MEGA MENU -->
      <div class="mega-menu" role="region" aria-label="Products menu">
        <div class="container mega-menu__inner">
          <!-- Column 1: Visual cards (like Tesla Energy) -->
          <div class="mega-menu__products">
            <a href="/product-1" class="mega-card">
              <img src="https://picsum.photos/seed/prod1/280/200"
                width="280" height="200" alt="Product 1" loading="lazy"
                class="mega-card__img img-rounded">
              <span class="mega-card__name">Product One</span>
              <span class="mega-card__links">
                <span>Learn</span> <span>Order</span>
              </span>
            </a>
            <a href="/product-2" class="mega-card">
              <img src="https://picsum.photos/seed/prod2/280/200"
                width="280" height="200" alt="Product 2" loading="lazy"
                class="mega-card__img img-rounded">
              <span class="mega-card__name">Product Two</span>
              <span class="mega-card__links">
                <span>Learn</span>
              </span>
            </a>
          </div>
          <!-- Column 2: Link list (like Tesla's right panel) -->
          <div class="mega-menu__links" role="list">
            <a href="#" class="mega-link">Schedule a Consultation</a>
            <a href="#" class="mega-link">Why Choose Us</a>
            <a href="#" class="mega-link">Incentives</a>
            <a href="#" class="mega-link">Support</a>
            <a href="#" class="mega-link">Commercial</a>
          </div>
        </div>
      </div>
    </li>
    <li class="nav-item">
      <a href="/about" class="nav-link">About</a>
    </li>
  </ul>

  <div class="nav-actions">
    <button class="btn btn--ghost btn--sm">Sign In</button>
    <button class="btn btn--primary btn--sm">Get Started</button>
  </div>
</nav>
```

```css
/* Nav link base */
.nav-links { display: flex; gap: var(--space-2); list-style: none; align-items: center; }
.nav-link {
  padding: var(--space-2) var(--space-3);
  border-radius: var(--radius-md);
  font-size: var(--text-sm); font-weight: 500; color: var(--text-2);
  display: flex; align-items: center; gap: 4px;
  transition: color var(--transition-micro), background var(--transition-micro);
  cursor: pointer; background: none; border: none;
}
.nav-link:hover { color: var(--text); background: var(--surface-up); }

/* Chevron rotates when open */
.nav-chevron { width: 14px; height: 14px; transition: transform var(--transition-tactile); }
.nav-item.open .nav-chevron { transform: rotate(180deg); }

/* ── MEGA MENU PANEL ──────────────────────────────────── */
.mega-menu {
  position: fixed; left: 0; right: 0;
  top: 60px; /* adjust to nav height */
  background: var(--surface);
  border-top: 1px solid var(--border);
  border-bottom: 1px solid var(--border);
  box-shadow: var(--shadow-lg);
  z-index: 99;
  /* Hidden state */
  opacity: 0;
  pointer-events: none;
  transform: translateY(-8px);
  transition:
    opacity var(--transition-base),
    transform var(--transition-base);
}
.nav-item.open .mega-menu {
  opacity: 1;
  pointer-events: all;
  transform: translateY(0);
}

.mega-menu__inner {
  display: grid;
  grid-template-columns: 1fr auto;
  gap: var(--space-12);
  padding-block: var(--space-8);
  align-items: start;
}

/* Product card grid inside mega menu */
.mega-menu__products {
  display: flex; gap: var(--space-6); flex-wrap: wrap;
}
.mega-card {
  display: flex; flex-direction: column; align-items: center;
  gap: var(--space-3); text-align: center;
  text-decoration: none; color: var(--text);
  max-width: 180px;
  transition: opacity var(--transition-micro);
}
.mega-card:hover { opacity: 0.8; }
.mega-card__img  { width: 100%; height: 140px; object-fit: contain; }
.mega-card__name { font-weight: 600; font-size: var(--text-sm); }
.mega-card__links {
  display: flex; gap: var(--space-4); font-size: var(--text-xs);
  color: var(--text-2);
}
.mega-card__links span {
  text-decoration: underline; cursor: pointer;
}

/* Right-side link list */
.mega-menu__links {
  display: flex; flex-direction: column; gap: var(--space-3);
  border-left: 1px solid var(--border);
  padding-left: var(--space-8);
  min-width: 200px;
}
.mega-link {
  font-size: var(--text-sm); color: var(--text-2);
  text-decoration: none; padding: var(--space-1) 0;
  transition: color var(--transition-micro);
}
.mega-link:hover { color: var(--text); }

/* ── SIMPLE DROPDOWN (not mega) ───────────────────────── */
.dropdown-menu {
  position: absolute; top: calc(100% + 8px); left: 0;
  min-width: 200px;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
  box-shadow: var(--shadow-lg);
  padding: var(--space-2);
  z-index: 200;
  opacity: 0; pointer-events: none;
  transform: translateY(-6px) scale(0.98);
  transform-origin: top left;
  transition:
    opacity var(--transition-base),
    transform var(--transition-tactile);
}
.nav-item.open .dropdown-menu {
  opacity: 1; pointer-events: all; transform: translateY(0) scale(1);
}
.dropdown-item {
  display: block; padding: var(--space-2) var(--space-3);
  border-radius: calc(var(--radius-md) - 2px);
  font-size: var(--text-sm); color: var(--text-2);
  text-decoration: none; transition: background var(--transition-micro), color var(--transition-micro);
}
.dropdown-item:hover { background: var(--surface-up); color: var(--text); }
```

```javascript
// Universal mega-menu / dropdown JS — works for both
document.querySelectorAll('.nav-item.has-mega, .nav-item.has-dropdown').forEach(item => {
  const trigger = item.querySelector('.nav-link');
  const close = () => {
    item.classList.remove('open');
    trigger.setAttribute('aria-expanded', 'false');
  };

  trigger.addEventListener('click', e => {
    e.stopPropagation();
    const isOpen = item.classList.contains('open');
    // Close all others
    document.querySelectorAll('.nav-item').forEach(i => {
      i.classList.remove('open');
      i.querySelector('.nav-link')?.setAttribute('aria-expanded', 'false');
    });
    if (!isOpen) {
      item.classList.add('open');
      trigger.setAttribute('aria-expanded', 'true');
    }
  });

  // Close on Escape
  item.addEventListener('keydown', e => { if (e.key === 'Escape') close(); });
});
// Close when clicking outside
document.addEventListener('click', () => {
  document.querySelectorAll('.nav-item.open').forEach(i => {
    i.classList.remove('open');
    i.querySelector('.nav-link')?.setAttribute('aria-expanded', 'false');
  });
});
```

---

## STEP 29 — Custom Select / Dropdown / Droptop

**Rule**: Never use the native `<select>` element in UI-facing interfaces. Always replace with a custom component styled to the design language.

### Custom Select (dropdown down)

```html
<div class="custom-select" id="my-select" role="combobox"
  aria-expanded="false" aria-haspopup="listbox" aria-labelledby="my-select-label">
  <label id="my-select-label" class="custom-select__label">Category</label>
  <button class="custom-select__trigger" aria-controls="my-select-list">
    <span class="custom-select__value">Select an option</span>
    <i data-lucide="chevron-down" class="custom-select__icon"></i>
  </button>
  <ul class="custom-select__list" id="my-select-list" role="listbox">
    <li class="custom-select__option" role="option" data-value="design" aria-selected="false">Design</li>
    <li class="custom-select__option" role="option" data-value="dev" aria-selected="false">Development</li>
    <li class="custom-select__option" role="option" data-value="strategy" aria-selected="false">Strategy</li>
    <li class="custom-select__option is-disabled" role="option" data-value="other" aria-disabled="true">Other (unavailable)</li>
  </ul>
</div>
<!-- Hidden real input for forms -->
<input type="hidden" name="category" id="my-select-input">
```

```css
.custom-select {
  position: relative;
  width: 100%;
}
.custom-select__label {
  display: block; margin-bottom: var(--space-1);
  font-size: var(--text-xs); font-weight: 600;
  text-transform: uppercase; letter-spacing: 0.06em;
  color: var(--text-2);
}
.custom-select__trigger {
  width: 100%;
  display: flex; justify-content: space-between; align-items: center;
  gap: var(--space-3);
  padding: 12px 16px;
  background: var(--surface); color: var(--text);
  border: 1.5px solid var(--border);
  border-radius: var(--radius-md);
  font-size: var(--text-base); font-family: var(--font-body);
  cursor: pointer; text-align: left;
  transition:
    border-color var(--transition-micro),
    box-shadow var(--transition-micro);
}
.custom-select__trigger:hover { border-color: var(--text-2); }
.custom-select[aria-expanded="true"] .custom-select__trigger {
  border-color: var(--accent);
  box-shadow: 0 0 0 3px color-mix(in srgb, var(--accent) 20%, transparent);
}
.custom-select__icon {
  width: 16px; height: 16px; flex-shrink: 0;
  transition: transform var(--transition-tactile);
}
.custom-select[aria-expanded="true"] .custom-select__icon {
  transform: rotate(180deg);
}

/* Dropdown list */
.custom-select__list {
  position: absolute; top: calc(100% + 4px); left: 0; right: 0; z-index: 300;
  background: var(--surface);
  border: 1.5px solid var(--border);
  border-radius: var(--radius-md);
  box-shadow: var(--shadow-lg);
  overflow: hidden; overflow-y: auto;
  max-height: 280px;
  list-style: none;
  padding: var(--space-1);
  /* Hidden state */
  opacity: 0; pointer-events: none;
  transform: translateY(-6px) scale(0.98);
  transform-origin: top center;
  transition:
    opacity var(--transition-base),
    transform var(--transition-tactile);
}
.custom-select[aria-expanded="true"] .custom-select__list {
  opacity: 1; pointer-events: all; transform: translateY(0) scale(1);
}
.custom-select__option {
  padding: 10px 14px;
  border-radius: calc(var(--radius-md) - 4px);
  cursor: pointer; font-size: var(--text-sm);
  transition: background var(--transition-micro), color var(--transition-micro);
}
.custom-select__option:hover       { background: var(--surface-up); }
.custom-select__option[aria-selected="true"] {
  background: color-mix(in srgb, var(--accent) 12%, transparent);
  color: var(--accent); font-weight: 600;
}
.custom-select__option.is-disabled {
  opacity: 0.4; cursor: not-allowed; pointer-events: none;
}
```

### Custom Droptop (opens UPWARD — for footers, bottom bars)

```css
/* Same as dropdown but opens up instead of down */
.custom-select.droptop .custom-select__list {
  top: auto;
  bottom: calc(100% + 4px);
  transform-origin: bottom center;
  transform: translateY(6px) scale(0.98);
}
.custom-select.droptop[aria-expanded="true"] .custom-select__list {
  transform: translateY(0) scale(1);
}
```

```javascript
// Universal custom select — handles both dropdown and droptop
document.querySelectorAll('.custom-select').forEach(select => {
  const trigger = select.querySelector('.custom-select__trigger');
  const options = select.querySelectorAll('.custom-select__option:not(.is-disabled)');
  const valueEl = select.querySelector('.custom-select__value');
  const hiddenInput = document.getElementById(select.id + '-input');

  const toggle = open => {
    select.setAttribute('aria-expanded', open ? 'true' : 'false');
  };

  trigger.addEventListener('click', e => {
    e.stopPropagation();
    const isOpen = select.getAttribute('aria-expanded') === 'true';
    document.querySelectorAll('.custom-select').forEach(s =>
      s.setAttribute('aria-expanded', 'false')
    );
    toggle(!isOpen);
  });

  options.forEach(opt => {
    opt.addEventListener('click', () => {
      options.forEach(o => o.setAttribute('aria-selected', 'false'));
      opt.setAttribute('aria-selected', 'true');
      valueEl.textContent = opt.textContent;
      if (hiddenInput) hiddenInput.value = opt.dataset.value;
      toggle(false);
      trigger.dispatchEvent(new Event('change', { bubbles: true }));
    });
  });

  // Keyboard navigation
  trigger.addEventListener('keydown', e => {
    if (['Enter', ' ', 'ArrowDown'].includes(e.key)) {
      e.preventDefault(); toggle(true);
      options[0]?.focus();
    }
  });
  select.addEventListener('keydown', e => {
    if (e.key === 'Escape') { toggle(false); trigger.focus(); }
  });
});

document.addEventListener('click', () => {
  document.querySelectorAll('.custom-select').forEach(s =>
    s.setAttribute('aria-expanded', 'false')
  );
});
```

### Select Style per Language

```css
/* LUXURY — no radius, thin border, uppercase label */
.select-luxury .custom-select__trigger {
  border-radius: 0;
  border-width: 1px;
  letter-spacing: 0.05em;
  font-size: var(--text-sm);
}
.select-luxury .custom-select__list  { border-radius: 0; border-width: 1px; }
.select-luxury .custom-select__option { border-radius: 0; }

/* WARM — big radius, soft colors */
.select-warm .custom-select__trigger {
  border-radius: var(--radius-lg);
  background: var(--surface-up);
  border-color: transparent;
}
.select-warm .custom-select__list { border-radius: var(--radius-lg); }

/* TECHNICAL — compact, monospace hint */
.select-technical .custom-select__trigger {
  font-size: var(--text-sm);
  padding: 8px 12px;
  border-radius: var(--radius-sm);
}
.select-technical .custom-select__list { border-radius: var(--radius-sm); }
.select-technical .custom-select__option { font-size: var(--text-xs); padding: 6px 10px; }
```

---

## ICONS IN CUSTOM SELECT (Enhanced Dropdowns)

**Rule**: Custom selects should include icons in both the trigger and options. Use Lucide icons with token-based sizing. NEVER show the native browser select.

### HTML Structure with Icons

```html
<!-- Custom select with icons — completely replaces native select -->
<div class="custom-select custom-select--with-icons" id="status-select" role="combobox"
  aria-expanded="false" aria-haspopup="listbox" aria-labelledby="status-label">
  
  <label id="status-label" class="custom-select__label">Status</label>
  
  <button class="custom-select__trigger" aria-controls="status-list">
    <span class="custom-select__icon-left">
      <i data-lucide="circle-dot"></i>
    </span>
    <span class="custom-select__value">Select status</span>
    <i data-lucide="chevron-down" class="custom-select__chevron"></i>
  </button>
  
  <ul class="custom-select__list" id="status-list" role="listbox">
    <li class="custom-select__option" role="option" data-value="active" data-icon="check-circle" aria-selected="false">
      <i data-lucide="check-circle" class="custom-select__option-icon"></i>
      <span>Active</span>
    </li>
    <li class="custom-select__option" role="option" data-value="pending" data-icon="clock" aria-selected="false">
      <i data-lucide="clock" class="custom-select__option-icon"></i>
      <span>Pending</span>
    </li>
    <li class="custom-select__option" role="option" data-value="inactive" data-icon="x-circle" aria-selected="false">
      <i data-lucide="x-circle" class="custom-select__option-icon"></i>
      <span>Inactive</span>
    </li>
    <li class="custom-select__option" role="option" data-value="archived" data-icon="archive" aria-selected="false">
      <i data-lucide="archive" class="custom-select__option-icon"></i>
      <span>Archived</span>
    </li>
  </ul>
  
  <!-- Hidden native input for form submission -->
  <input type="hidden" name="status" id="status-value">
</div>

<!-- NEVER use native select for styled forms -->
<!-- ❌ <select> — Cannot be fully styled, shows native UI -->
<!-- ✅ Custom select — Full token control -->
```

### CSS for Icons in Select

```css
/* Icon sizing uses space tokens */
.custom-select--with-icons .custom-select__icon-left {
  display: flex;
  align-items: center;
  justify-content: center;
  width: var(--space-5);  /* 20px */
  height: var(--space-5);
  color: var(--muted);
  flex-shrink: 0;
}

.custom-select--with-icons .custom-select__icon-left svg {
  width: var(--space-4);  /* 16px */
  height: var(--space-4);
}

/* Trigger layout with icon */
.custom-select--with-icons .custom-select__trigger {
  display: flex;
  align-items: center;
  gap: var(--space-3);
  padding: var(--space-3) var(--space-4);
  padding-right: var(--space-10); /* Room for chevron */
  position: relative;
}

/* Chevron positioning */
.custom-select__chevron {
  position: absolute;
  right: var(--space-4);
  top: 50%;
  transform: translateY(-50%);
  width: var(--space-4);
  height: var(--space-4);
  color: var(--muted);
  transition: transform var(--transition-micro);
  pointer-events: none;
}

.custom-select[aria-expanded="true"] .custom-select__chevron {
  transform: translateY(-50%) rotate(180deg);
}

/* Option layout with icon */
.custom-select--with-icons .custom-select__option {
  display: flex;
  align-items: center;
  gap: var(--space-3);
  padding: var(--space-3) var(--space-4);
}

.custom-select__option-icon {
  width: var(--space-4);  /* 16px */
  height: var(--space-4);
  color: var(--muted);
  flex-shrink: 0;
}

/* Selected option icon color */
.custom-select__option[aria-selected="true"] .custom-select__option-icon {
  color: var(--accent);
}

/* Hover state icon */
.custom-select__option:hover .custom-select__option-icon {
  color: var(--text);
}

/* Update trigger icon when value selected */
.custom-select--with-icons.has-value .custom-select__icon-left {
  color: var(--accent);
}
```

### JavaScript for Icon Sync

```javascript
// Enhanced custom select with icon support
document.querySelectorAll('.custom-select--with-icons').forEach(select => {
  const trigger = select.querySelector('.custom-select__trigger');
  const options = select.querySelectorAll('.custom-select__option:not(.is-disabled)');
  const valueEl = select.querySelector('.custom-select__value');
  const iconEl = select.querySelector('.custom-select__icon-left');
  const hiddenInput = select.querySelector('input[type="hidden"]');

  // Toggle dropdown
  trigger.addEventListener('click', () => {
    const isOpen = select.getAttribute('aria-expanded') === 'true';
    closeAllSelects();
    if (!isOpen) {
      select.setAttribute('aria-expanded', 'true');
    }
  });

  // Select option
  options.forEach(option => {
    option.addEventListener('click', () => {
      // Update visual state
      options.forEach(o => o.setAttribute('aria-selected', 'false'));
      option.setAttribute('aria-selected', 'true');
      
      // Update trigger text
      valueEl.textContent = option.querySelector('span').textContent;
      
      // Update trigger icon to match selected option
      const iconName = option.dataset.icon;
      if (iconEl && iconName) {
        iconEl.innerHTML = `<i data-lucide="${iconName}"></i>`;
        lucide.createIcons({ icons: { [iconName]: lucide.icons[iconName] }, nodes: [iconEl] });
      }
      
      // Update hidden input
      if (hiddenInput) {
        hiddenInput.value = option.dataset.value;
      }
      
      // Add selected class
      select.classList.add('has-value');
      
      // Close dropdown
      select.setAttribute('aria-expanded', 'false');
      
      // Dispatch change event
      select.dispatchEvent(new CustomEvent('change', { 
        detail: { value: option.dataset.value, label: option.textContent.trim() }
      }));
    });
  });
});

function closeAllSelects() {
  document.querySelectorAll('.custom-select').forEach(s => 
    s.setAttribute('aria-expanded', 'false')
  );
}

// Close on outside click
document.addEventListener('click', e => {
  if (!e.target.closest('.custom-select')) {
    closeAllSelects();
  }
});
```

### Icon Size Reference

```css
/* Standard icon sizes using space tokens */
.icon-xs { width: var(--space-3); height: var(--space-3); }  /* 12px - badges */
.icon-sm { width: var(--space-4); height: var(--space-4); }  /* 16px - inline, options */
.icon-md { width: var(--space-5); height: var(--space-5); }  /* 20px - buttons, triggers */
.icon-lg { width: var(--space-6); height: var(--space-6); }  /* 24px - headers */
.icon-xl { width: var(--space-8); height: var(--space-8); }  /* 32px - features */
```

### Category Select with Icons (Practical Example)

```html
<div class="custom-select custom-select--with-icons" id="category-select">
  <label class="custom-select__label">Category</label>
  <button class="custom-select__trigger">
    <span class="custom-select__icon-left"><i data-lucide="folder"></i></span>
    <span class="custom-select__value">Choose category</span>
    <i data-lucide="chevron-down" class="custom-select__chevron"></i>
  </button>
  <ul class="custom-select__list">
    <li class="custom-select__option" data-value="design" data-icon="palette">
      <i data-lucide="palette" class="custom-select__option-icon"></i>
      <span>Design</span>
    </li>
    <li class="custom-select__option" data-value="development" data-icon="code">
      <i data-lucide="code" class="custom-select__option-icon"></i>
      <span>Development</span>
    </li>
    <li class="custom-select__option" data-value="marketing" data-icon="megaphone">
      <i data-lucide="megaphone" class="custom-select__option-icon"></i>
      <span>Marketing</span>
    </li>
    <li class="custom-select__option" data-value="finance" data-icon="wallet">
      <i data-lucide="wallet" class="custom-select__option-icon"></i>
      <span>Finance</span>
    </li>
  </ul>
  <input type="hidden" name="category">
</div>
```

---

## STEP 30 — Custom Date & Time Picker

**Rule**: Flatpickr is the CDN-based picker (already in Step 5). Style it to match the design language. Never show the browser native date picker.

### Flatpickr CDN (already in Step 5 — reminder)

```html
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
```

### HTML Structure (same for all, CSS varies)

```html
<!-- Date picker -->
<div class="datepicker-wrap field">
  <label class="field__label-static" for="date-input">Select Date</label>
  <div class="datepicker-input-wrap">
    <i data-lucide="calendar" class="datepicker-icon"></i>
    <input type="text" id="date-input" class="datepicker-input field__input-flat"
      placeholder="DD / MM / YYYY" readonly>
  </div>
</div>

<!-- Date + Time picker -->
<div class="datepicker-wrap field">
  <label class="field__label-static" for="datetime-input">Select Date & Time</label>
  <div class="datepicker-input-wrap">
    <i data-lucide="clock" class="datepicker-icon"></i>
    <input type="text" id="datetime-input" class="datepicker-input field__input-flat"
      placeholder="DD / MM / YYYY  HH : MM" readonly>
  </div>
</div>

<!-- Time-only picker -->
<div class="datepicker-wrap field">
  <label class="field__label-static" for="time-input">Select Time</label>
  <div class="datepicker-input-wrap">
    <i data-lucide="clock-3" class="datepicker-icon"></i>
    <input type="text" id="time-input" class="datepicker-input field__input-flat"
      placeholder="HH : MM" readonly>
  </div>
</div>
```

```css
/* Input wrapper with icon */
.datepicker-wrap { display: flex; flex-direction: column; gap: var(--space-1); }
.field__label-static {
  font-size: var(--text-xs); font-weight: 600;
  letter-spacing: 0.06em; text-transform: uppercase; color: var(--text-2);
}
.datepicker-input-wrap {
  position: relative; display: flex; align-items: center;
}
.datepicker-icon {
  position: absolute; left: 14px; width: 16px; height: 16px;
  color: var(--muted); pointer-events: none; z-index: 1;
}
.field__input-flat {
  width: 100%; padding: 12px 16px 12px 42px;
  background: var(--surface); color: var(--text);
  border: 1.5px solid var(--border);
  border-radius: var(--radius-md);
  font-size: var(--text-base); font-family: var(--font-body);
  cursor: pointer; caret-color: transparent;
  transition: border-color var(--transition-micro), box-shadow var(--transition-micro);
}
.field__input-flat:focus {
  border-color: var(--accent);
  box-shadow: 0 0 0 3px color-mix(in srgb, var(--accent) 20%, transparent);
  outline: none;
}
```

```css
/* ── FLATPICKR THEME — adapts to [data-theme] ─────────── */
.flatpickr-calendar {
  background: var(--surface) !important;
  border: 1.5px solid var(--border) !important;
  border-radius: var(--radius-lg) !important;
  box-shadow: var(--shadow-xl) !important;
  font-family: var(--font-body) !important;
  color: var(--text) !important;
}
.flatpickr-months { border-radius: var(--radius-lg) var(--radius-lg) 0 0 !important; }
.flatpickr-month  { background: transparent !important; color: var(--text) !important; }
.flatpickr-current-month .flatpickr-monthDropdown-months {
  background: var(--surface) !important; color: var(--text) !important;
}
.flatpickr-current-month input.cur-year { color: var(--text) !important; }
.flatpickr-weekdays { background: transparent !important; }
span.flatpickr-weekday { color: var(--text-2) !important; background: transparent !important; font-size: var(--text-xs) !important; }
.flatpickr-day {
  color: var(--text) !important; border-radius: var(--radius-sm) !important;
  transition: background var(--transition-micro) !important;
}
.flatpickr-day:hover { background: var(--surface-up) !important; }
.flatpickr-day.selected, .flatpickr-day.selected:hover {
  background: var(--accent) !important;
  border-color: var(--accent) !important;
  color: white !important;
}
.flatpickr-day.today { border-color: var(--accent) !important; }
.flatpickr-day.inRange {
  background: color-mix(in srgb, var(--accent) 15%, transparent) !important;
  box-shadow: none !important;
}
.numInputWrapper span.arrowUp::after  { border-bottom-color: var(--text) !important; }
.numInputWrapper span.arrowDown::after { border-top-color: var(--text) !important; }
.flatpickr-time input { color: var(--text) !important; background: transparent !important; }
.flatpickr-time .flatpickr-time-separator { color: var(--text-2) !important; }
.flatpickr-prev-month svg, .flatpickr-next-month svg { fill: var(--text) !important; }

/* Language-specific radius tweaks */
/* Luxury: sharp corners */
.datepicker-luxury .flatpickr-calendar { border-radius: 0 !important; }
.datepicker-luxury .flatpickr-day      { border-radius: 0 !important; }
/* Warm: extra rounded */
.datepicker-warm .flatpickr-calendar   { border-radius: var(--radius-xl, 24px) !important; }
.datepicker-warm .flatpickr-day        { border-radius: 50% !important; }
```

```javascript
// Date picker init — swap mode/config per use case
flatpickr('#date-input', {
  dateFormat: 'd / m / Y',
  disableMobile: true,         // always use custom UI
  animate: true,
});

// Date + Time
flatpickr('#datetime-input', {
  enableTime: true,
  dateFormat: 'd / m / Y  H : i',
  time_24hr: true,
  disableMobile: true,
});

// Time only
flatpickr('#time-input', {
  noCalendar: true,
  enableTime: true,
  dateFormat: 'H : i',
  time_24hr: true,
  disableMobile: true,
});

// Date range picker
flatpickr('#range-input', {
  mode: 'range',
  dateFormat: 'd / m / Y',
  disableMobile: true,
});
```

---

## STEP 31 — Custom Toggle / Switch

**Rule**: Toggles have 3 variants per language: pill (default), square, and icon toggle.
All must be keyboard accessible and have `aria-checked`.

```html
<!-- Pill toggle (all languages) -->
<label class="toggle-wrap" aria-label="Enable notifications">
  <input type="checkbox" class="toggle-input" role="switch" aria-checked="false">
  <span class="toggle-pill">
    <span class="toggle-thumb"></span>
  </span>
  <span class="toggle-label">Enable notifications</span>
</label>

<!-- Toggle with icons -->
<label class="toggle-wrap" aria-label="Dark mode">
  <input type="checkbox" class="toggle-input" role="switch">
  <span class="toggle-pill toggle-pill--icon">
    <i data-lucide="moon"  class="toggle-icon toggle-icon--off"></i>
    <span class="toggle-thumb"></span>
    <i data-lucide="sun"   class="toggle-icon toggle-icon--on"></i>
  </span>
</label>

<!-- Square toggle (Luxury / Minimalist) -->
<label class="toggle-wrap" aria-label="Subscribe">
  <input type="checkbox" class="toggle-input" role="switch">
  <span class="toggle-square">
    <span class="toggle-thumb"></span>
  </span>
  <span class="toggle-label">Subscribe to updates</span>
</label>
```

```css
/* Shared base — hide native checkbox */
.toggle-input {
  position: absolute; opacity: 0; width: 0; height: 0; pointer-events: none;
}
.toggle-wrap {
  display: inline-flex; align-items: center; gap: var(--space-3);
  cursor: pointer; user-select: none;
}
.toggle-label { font-size: var(--text-sm); color: var(--text-2); }
.toggle-wrap:hover .toggle-label { color: var(--text); }

/* ── PILL TOGGLE ──────────────────────────────────────── */
.toggle-pill {
  position: relative; display: flex; align-items: center;
  width: 52px; height: 28px;
  background: var(--surface-up);
  border: 1.5px solid var(--border);
  border-radius: 9999px;
  transition:
    background var(--transition-tactile),
    border-color var(--transition-tactile);
  flex-shrink: 0;
}
.toggle-pill .toggle-thumb {
  position: absolute; left: 3px;
  width: 20px; height: 20px; border-radius: 50%;
  background: var(--muted);
  box-shadow: var(--shadow-sm);
  transition: transform var(--transition-tactile), background var(--transition-tactile);
}
/* Checked state */
.toggle-input:checked + .toggle-pill {
  background: var(--accent);
  border-color: var(--accent);
}
.toggle-input:checked + .toggle-pill .toggle-thumb {
  transform: translateX(24px);
  background: white;
}
/* Focus */
.toggle-input:focus-visible + .toggle-pill {
  outline: 2px solid var(--accent);
  outline-offset: 3px;
}

/* ── ICON TOGGLE ─────────────────────────────────────── */
.toggle-pill--icon {
  width: 60px; gap: 4px; padding: 0 6px;
  justify-content: space-between;
}
.toggle-icon { width: 12px; height: 12px; color: var(--muted); flex-shrink: 0; z-index: 1; }
.toggle-icon--on  { color: white; opacity: 0; transition: opacity var(--transition-micro); }
.toggle-icon--off { transition: opacity var(--transition-micro); }
.toggle-input:checked ~ .toggle-pill--icon .toggle-icon--on  { opacity: 1; }
.toggle-input:checked ~ .toggle-pill--icon .toggle-icon--off { opacity: 0; }
.toggle-pill--icon .toggle-thumb { left: 6px; }
.toggle-input:checked + .toggle-pill--icon .toggle-thumb { transform: translateX(28px); }

/* ── SQUARE TOGGLE (Luxury / Minimal) ────────────────── */
.toggle-square {
  position: relative;
  width: 48px; height: 24px;
  background: var(--surface-up);
  border: 1px solid var(--border);
  border-radius: 0;
  flex-shrink: 0;
  transition: background var(--transition-base), border-color var(--transition-base);
}
.toggle-square .toggle-thumb {
  position: absolute; top: 3px; left: 3px;
  width: 16px; height: 16px; border-radius: 0;
  background: var(--muted);
  transition: transform var(--transition-spring), background var(--transition-base);
}
.toggle-input:checked + .toggle-square {
  background: var(--text);
  border-color: var(--text);
}
.toggle-input:checked + .toggle-square .toggle-thumb {
  transform: translateX(24px);
  background: var(--bg);
}

/* Size variants */
.toggle-pill.toggle--sm { width: 40px; height: 22px; }
.toggle-pill.toggle--sm .toggle-thumb { width: 16px; height: 16px; }
.toggle-input:checked + .toggle-pill.toggle--sm .toggle-thumb { transform: translateX(18px); }

.toggle-pill.toggle--lg { width: 64px; height: 34px; }
.toggle-pill.toggle--lg .toggle-thumb { width: 26px; height: 26px; }
.toggle-input:checked + .toggle-pill.toggle--lg .toggle-thumb { transform: translateX(30px); }
```

---

## STEP 32 — Button System (Full Variant Set)

**Rule**: Every project needs the full button set defined once. Never create one-off button styles.

```html
<!-- Primary -->    <button class="btn btn--primary">Get Started</button>
<!-- Secondary -->  <button class="btn btn--secondary">Learn More</button>
<!-- Ghost -->      <button class="btn btn--ghost">Cancel</button>
<!-- Danger -->     <button class="btn btn--danger">Delete</button>
<!-- Link-style --> <button class="btn btn--link">View Details →</button>
<!-- Icon only -->  <button class="btn btn--icon" aria-label="Settings">
                      <i data-lucide="settings"></i></button>
<!-- Icon + text --> <button class="btn btn--primary">
                       <i data-lucide="plus"></i> Add Item</button>
<!-- Loading -->    <button class="btn btn--primary btn--loading">Save</button>
<!-- Disabled -->   <button class="btn btn--primary" disabled>Unavailable</button>

<!-- Sizes -->
<button class="btn btn--primary btn--xs">Tiny</button>
<button class="btn btn--primary btn--sm">Small</button>
<button class="btn btn--primary">Base</button>
<button class="btn btn--primary btn--lg">Large</button>
<button class="btn btn--primary btn--xl">XL</button>
<button class="btn btn--primary btn--full">Full Width</button>
```

```css
/* ── BASE BUTTON ────────────────────────────────────────── */
.btn {
  display: inline-flex; align-items: center; justify-content: center;
  gap: var(--space-2);
  font-family: var(--font-body); font-weight: 600;
  white-space: nowrap; cursor: pointer;
  border: 1.5px solid transparent;
  border-radius: var(--radius-md);
  text-decoration: none;
  transition:
    background-color var(--transition-micro),
    border-color var(--transition-micro),
    color var(--transition-micro),
    transform var(--transition-tactile),
    box-shadow var(--transition-base);
  /* Default size */
  padding: 10px 20px;
  font-size: var(--text-sm);
  min-height: 40px;
}
.btn svg { width: 16px; height: 16px; flex-shrink: 0; }
.btn:hover   { transform: translateY(-1px); box-shadow: var(--shadow-md); }
.btn:active  { transform: translateY(0) scale(0.98); box-shadow: none; }
.btn:disabled { opacity: 0.4; cursor: not-allowed; pointer-events: none;
                transform: none; box-shadow: none; }

/* ── VARIANTS ────────────────────────────────────────────── */
.btn--primary {
  background: var(--accent); color: white; border-color: var(--accent);
}
.btn--primary:hover {
  background: color-mix(in srgb, var(--accent) 85%, black);
  border-color: color-mix(in srgb, var(--accent) 85%, black);
}

.btn--secondary {
  background: var(--surface-up); color: var(--text); border-color: var(--border);
}
.btn--secondary:hover { background: var(--border); }

.btn--ghost {
  background: transparent; color: var(--text); border-color: var(--border);
}
.btn--ghost:hover { background: var(--surface-up); }

.btn--danger {
  background: #CF222E; color: white; border-color: #CF222E;
}
.btn--danger:hover { background: #a21c26; border-color: #a21c26; }

.btn--link {
  background: transparent; color: var(--accent); border-color: transparent;
  text-decoration: underline; text-underline-offset: 3px;
  padding-inline: var(--space-2);
}
.btn--link:hover { box-shadow: none; transform: none; opacity: 0.8; }

/* Icon-only button */
.btn--icon {
  width: 40px; height: 40px; padding: 0;
  border-radius: var(--radius-md);
  background: var(--surface); border-color: var(--border); color: var(--text-2);
}
.btn--icon svg { width: 18px; height: 18px; }
.btn--icon:hover { color: var(--text); background: var(--surface-up); }

/* ── SIZES ───────────────────────────────────────────────── */
.btn--xs  { padding: 5px 10px;  font-size: var(--text-xs); min-height: 28px; }
.btn--xs svg { width: 12px; height: 12px; }
.btn--sm  { padding: 8px 14px;  font-size: var(--text-xs); min-height: 32px; }
.btn--lg  { padding: 14px 28px; font-size: var(--text-base); min-height: 48px; }
.btn--lg svg { width: 20px; height: 20px; }
.btn--xl  { padding: 18px 40px; font-size: var(--text-lg); min-height: 56px; }
.btn--xl svg { width: 22px; height: 22px; }
.btn--full { width: 100%; }

/* ── LOADING STATE ───────────────────────────────────────── */
.btn--loading {
  position: relative; color: transparent !important; pointer-events: none;
}
.btn--loading::after {
  content: '';
  position: absolute; inset: 0; margin: auto;
  width: 16px; height: 16px;
  border: 2px solid currentColor;
  border-top-color: transparent;
  border-radius: 50%;
  animation: btn-spin 0.65s linear infinite;
  color: white;
}
@keyframes btn-spin { to { transform: rotate(360deg); } }

/* ── LANGUAGE VARIANTS ───────────────────────────────────── */
/* Luxury — sharp, no radius, thin border, uppercase */
.btn-luxury { border-radius: 0; text-transform: uppercase;
  letter-spacing: 0.12em; font-size: 10px; font-weight: 500; }
.btn-luxury.btn--primary {
  background: transparent; color: var(--text); border-color: var(--text); }
.btn-luxury.btn--primary:hover {
  background: var(--text); color: var(--bg); box-shadow: none; }

/* Warm — pill shape, no uppercase */
.btn-warm { border-radius: 9999px; }

/* Technical — compact, flat, no hover lift */
.btn-technical { border-radius: var(--radius-sm); font-size: var(--text-xs);
  padding: 6px 12px; min-height: 28px; }
.btn-technical:hover { transform: none; }

/* Expressive — gradient or vivid, bold */
.btn-expressive.btn--primary {
  background: var(--gradient-hero, var(--accent));
  border-color: transparent;
}
```

---

## QUICK REFERENCE — Interactive Components per Language

| Component | Luxury | Premium | Minimalist | Expressive | Warm | Technical |
|-----------|--------|---------|------------|------------|------|-----------|
| **Button** | Sharp, uppercase, ghost-first | Rounded, accent-fill | Barely styled, text-based | Gradient or vivid fill | Pill, friendly | Compact, flat |
| **Select** | No radius, thin border | Pill trigger | Thin underline only | Bold accent border | Soft bg, large radius | Compact, monospace |
| **Toggle** | Square, no animation | Pill, subtle | Minimal, no shadow | Pill, fast spring | Pill, warm shadow | Square, instant |
| **Datepicker** | Sharp calendar | Rounded, soft | Clean, minimal | Vivid selected | Round days | Compact, dense |
| **Navbar** | Transparent→glass | Pill glass floating | Full-width border | Accent border bottom | Soft shadow | Dense, borderline |
| **Mega-menu** | Full-width, image+links | Grid cards | Simple list | Bold cards | Photo cards | Table-style list |
| **Image shapes** | Square, arch, diamond | Squircle, circle | Arch, square | Blob, hexagon | Blob, circle | Square only |
