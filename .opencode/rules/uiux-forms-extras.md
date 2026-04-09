---
description: Advanced form patterns including search bars, searchable dropdowns, range sliders, color pickers, and autocomplete/typeahead with API integration.
---

# UI/UX Forms Extras 2026

> Search bars, searchable dropdowns, range sliders, color pickers, 
> and autocomplete/typeahead with API integration.

---

## 1. SEARCH BAR (Debounced Input & Clear Button)

```html
<div class="search-bar">
  <i data-lucide="search" class="search-bar__icon"></i>
  <input type="text" placeholder="Search..." class="search-bar__input" id="search-input">
  <button class="search-bar__clear" id="search-clear" hidden><i data-lucide="x"></i></button>
  <div class="search-bar__results" id="search-results" hidden>
    <ul class="search-bar__list">
      <li class="search-bar__item">Result 1</li>
      <li class="search-bar__item">Result 2</li>
    </ul>
  </div>
</div>
```

```css
.search-bar {
  position: relative;
  display: flex;
  align-items: center;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-full);
  padding: var(--space-2) var(--space-4);
  transition: border-color var(--transition-micro), box-shadow var(--transition-micro);
}

.search-bar:focus-within {
  border-color: var(--accent);
  box-shadow: 0 0 0 3px color-mix(in srgb, var(--accent) 20%, transparent);
}

.search-bar__input {
  width: 100%;
  padding: var(--space-2) var(--space-4);
  font-size: var(--text-base);
  border: none;
  outline: none;
  background: transparent;
}

.search-bar__clear {
  color: var(--muted);
  cursor: pointer;
  padding: var(--space-1);
  border-radius: 50%;
  transition: background var(--transition-micro);
}

.search-bar__clear:hover { background: var(--surface-up); }
```

---

## 2. SEARCHABLE DROPDOWN (Real-Time Filtering)

```html
<div class="dropdown dropdown--searchable">
  <button class="dropdown__trigger" aria-haspopup="listbox" aria-expanded="false">
    Select an option...
    <i data-lucide="chevron-down"></i>
  </button>
  <div class="dropdown__menu" hidden>
    <div class="dropdown__search">
      <input type="text" placeholder="Filter options..." class="dropdown__search-input">
    </div>
    <ul role="listbox" class="dropdown__list">
      <li role="option" class="dropdown__item">Option 1</li>
      <li role="option" class="dropdown__item">Option 2</li>
    </ul>
  </div>
</div>
```

```css
.dropdown--searchable .dropdown__menu {
  width: 100%;
  max-height: 300px;
  overflow-y: auto;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
  box-shadow: var(--shadow-lg);
  z-index: 100;
}

.dropdown__search {
  padding: var(--space-2);
  border-bottom: 1px solid var(--border);
  position: sticky;
  top: 0;
  background: var(--surface);
}

.dropdown__search-input {
  width: 100%;
  padding: var(--space-2);
  font-size: var(--text-sm);
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  background: var(--surface-up);
}
```

---

## 3. RANGE SLIDER (Dual Thumb & Numeric Labels)

```html
<div class="range-slider">
  <div class="range-slider__track">
    <div class="range-slider__progress"></div>
  </div>
  <input type="range" min="0" max="100" value="20" class="range-slider__input range-slider__input--min">
  <input type="range" min="0" max="100" value="80" class="range-slider__input range-slider__input--max">
  <div class="range-slider__labels">
    <span class="range-slider__label">Min: $20</span>
    <span class="range-slider__label">Max: $80</span>
  </div>
</div>
```

```css
.range-slider {
  position: relative;
  width: 100%;
  height: 40px;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.range-slider__track {
  position: absolute;
  width: 100%;
  height: 6px;
  background: var(--surface-up);
  border-radius: var(--radius-full);
}

.range-slider__progress {
  position: absolute;
  height: 100%;
  background: var(--accent);
  border-radius: var(--radius-full);
}

.range-slider__input {
  position: absolute;
  width: 100%;
  height: 6px;
  background: none;
  pointer-events: none;
  -webkit-appearance: none;
}

.range-slider__input::-webkit-slider-thumb {
  pointer-events: auto;
  width: 20px; height: 20px;
  border-radius: 50%;
  background: var(--accent);
  border: 2px solid var(--bg);
  box-shadow: var(--shadow-sm);
  cursor: pointer;
  -webkit-appearance: none;
}
```

---

## 4. COLOR PICKER (Theming/Admin)

```html
<div class="color-picker">
  <label for="theme-color" class="color-picker__label">Theme Color</label>
  <div class="color-picker__wrap">
    <input type="color" id="theme-color" class="color-picker__input">
    <span class="color-picker__value">#0066FF</span>
  </div>
</div>
```

```css
.color-picker__input {
  width: 40px; height: 40px;
  border: none;
  border-radius: var(--radius-sm);
  cursor: pointer;
  background: none;
}

.color-picker__input::-webkit-color-swatch {
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
}
```

---

## 5. AUTOCOMPLETE / TYPEAHEAD (API Integration)

```javascript
// Example: Debounced Autocomplete
let timeout;
const searchInput = document.getElementById('search-input');

searchInput.addEventListener('input', (e) => {
  clearTimeout(timeout);
  const query = e.target.value;
  if (query.length < 2) return;

  timeout = setTimeout(async () => {
    const response = await fetch(`/api/search?q=${query}`);
    const results = await response.json();
    renderResults(results);
  }, 300);
});

function renderResults(results) {
  const resultsEl = document.getElementById('search-results');
  resultsEl.innerHTML = results.map(r => `<li class="search-bar__item">${r.title}</li>`).join('');
  resultsEl.hidden = false;
}
```
