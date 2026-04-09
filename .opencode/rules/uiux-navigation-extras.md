---
description: Advanced navigation patterns including breadcrumbs, pagination, tabs, accordions, collapsible sidebars, bottom sheets, and command palettes.
---

# UI/UX Navigation Extras 2026

> Breadcrumbs, pagination, tabs, accordions, collapsible sidebars, 
> bottom sheets, and command palettes (cmdk).

---

## 1. BREADCRUMBS (A11y-First)

```html
<nav aria-label="Breadcrumb" class="breadcrumb">
  <ol class="breadcrumb__list">
    <li class="breadcrumb__item"><a href="/">Home</a></li>
    <li class="breadcrumb__item"><a href="/products">Products</a></li>
    <li class="breadcrumb__item" aria-current="page">New Arrivals</li>
  </ol>
</nav>
```

```css
.breadcrumb__list {
  display: flex;
  list-style: none;
  gap: var(--space-2);
  font-size: var(--text-sm);
  color: var(--muted);
}

.breadcrumb__item:not(:last-child)::after {
  content: '/';
  margin-left: var(--space-2);
  opacity: 0.5;
}

.breadcrumb__item a:hover { color: var(--accent); }
.breadcrumb__item[aria-current="page"] { color: var(--text); font-weight: 600; }
```

---

## 2. PAGINATION (Numbered + Selectors)

```html
<nav aria-label="Pagination" class="pagination">
  <button class="btn btn--icon" aria-label="Previous page"><i data-lucide="chevron-left"></i></button>
  <div class="pagination__pages">
    <button class="btn btn--page active" aria-current="page">1</button>
    <button class="btn btn--page">2</button>
    <span class="pagination__ellipsis">...</span>
    <button class="btn btn--page">12</button>
  </div>
  <button class="btn btn--icon" aria-label="Next page"><i data-lucide="chevron-right"></i></button>
  <select class="pagination__size" aria-label="Items per page">
    <option>10</option><option>25</option><option>50</option>
  </select>
</nav>
```

```css
.pagination {
  display: flex;
  align-items: center;
  gap: var(--space-4);
  padding-block: var(--space-4);
}

.btn--page {
  width: 36px; height: 36px;
  border-radius: var(--radius-sm);
  transition: var(--transition-base);
}

.btn--page.active { background: var(--accent); color: white; }
```

---

## 3. TABS (Animated Indicator)

```html
<div class="tabs" id="main-tabs">
  <div role="tablist" class="tabs__list">
    <button role="tab" aria-selected="true" aria-controls="panel-1" id="tab-1">Overview</button>
    <button role="tab" aria-selected="false" aria-controls="panel-2" id="tab-2">Settings</button>
    <div class="tabs__indicator"></div>
  </div>
  <div role="tabpanel" id="panel-1" aria-labelledby="tab-1">...</div>
  <div role="tabpanel" id="panel-2" aria-labelledby="tab-2" hidden>...</div>
</div>
```

```css
.tabs__list {
  position: relative;
  display: flex;
  border-bottom: 1px solid var(--border);
}

.tabs__indicator {
  position: absolute;
  bottom: -1px; left: 0;
  height: 2px;
  background: var(--accent);
  transition: transform var(--dur-base) var(--ease-spring), width var(--dur-base) var(--ease-spring);
}
```

---

## 4. ACCORDION (Collapsible Sections)

```html
<div class="accordion">
  <div class="accordion__item">
    <button class="accordion__trigger" aria-expanded="false" aria-controls="acc-1">
      What is OpenCode?
      <i data-lucide="chevron-down"></i>
    </button>
    <div class="accordion__content" id="acc-1" hidden>
      <div class="accordion__inner">...</div>
    </div>
  </div>
</div>
```

```css
.accordion__content {
  display: grid;
  grid-template-rows: 0fr;
  transition: grid-template-rows var(--dur-base) var(--ease-out);
}

.accordion__content:not([hidden]) { grid-template-rows: 1fr; }

.accordion__inner { overflow: hidden; padding-block: var(--space-4); }
```

---

## 5. COLLAPSIBLE SIDEBAR (Nested Items)

```css
.sidebar {
  width: 280px;
  height: 100vh;
  background: var(--surface);
  border-right: 1px solid var(--border);
  transition: width var(--dur-base) var(--ease-out);
}

.sidebar--collapsed { width: 80px; }

.sidebar__nav-item {
  display: flex;
  align-items: center;
  padding: var(--space-3) var(--space-4);
  gap: var(--space-3);
  border-radius: var(--radius-md);
}

.sidebar__nav-item:hover { background: var(--surface-up); }
```

---

## 6. BOTTOM SHEET / DRAWER (Vaul/Radix)

```css
.drawer-overlay {
  position: fixed; inset: 0;
  background: var(--overlay);
  backdrop-filter: blur(4px);
}

.drawer-content {
  position: fixed; bottom: 0; left: 0; right: 0;
  background: var(--surface);
  border-top-left-radius: var(--radius-lg);
  border-top-right-radius: var(--radius-lg);
  padding: var(--space-6);
  transform: translateY(100%);
  transition: transform var(--dur-base) var(--ease-spring);
}

.drawer-content.open { transform: translateY(0); }
```

---

## 7. COMMAND PALETTE (cmdk)

```html
<div class="cmdk-dialog" role="dialog" aria-modal="true">
  <div class="cmdk-input-wrap">
    <i data-lucide="search"></i>
    <input type="text" placeholder="Type a command or search..." class="cmdk-input">
  </div>
  <div class="cmdk-list">
    <div class="cmdk-group" role="group" aria-label="Suggestions">
      <div class="cmdk-item" role="option">Search Projects...</div>
      <div class="cmdk-item" role="option">Create New File...</div>
    </div>
  </div>
</div>
```

```css
.cmdk-dialog {
  position: fixed; top: 20%; left: 50%;
  transform: translateX(-50%);
  width: 100%; max-width: 640px;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-xl);
  z-index: 1000;
}
```
