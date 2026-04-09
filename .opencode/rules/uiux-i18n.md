---
description: Advanced i18n patterns including language switchers, RTL support, date/time formatting, number/currency formatting, and pluralization rules.
---

# UI/UX Internationalization (i18n) 2026

> Language switchers, RTL support, date/time formatting, 
> number/currency formatting, and pluralization rules.

---

## 1. LANGUAGE SWITCHER (Flag/Text & `dir` Attribute)

```html
<div class="language-switcher">
  <button class="language-switcher__trigger" aria-haspopup="listbox" aria-expanded="false">
    <span class="language-switcher__flag">🇺🇸</span>
    <span class="language-switcher__text">English</span>
    <i data-lucide="chevron-down"></i>
  </button>
  <ul role="listbox" class="language-switcher__menu" hidden>
    <li role="option" class="language-switcher__item" data-lang="en" data-dir="ltr">English</li>
    <li role="option" class="language-switcher__item" data-lang="ar" data-dir="rtl">العربية</li>
  </ul>
</div>
```

```javascript
// Language Switcher Logic
const switcher = document.querySelector('.language-switcher');
const trigger = switcher.querySelector('.language-switcher__trigger');
const menu = switcher.querySelector('.language-switcher__menu');

trigger.addEventListener('click', () => {
  const expanded = trigger.getAttribute('aria-expanded') === 'true';
  trigger.setAttribute('aria-expanded', !expanded);
  menu.hidden = expanded;
});

menu.addEventListener('click', (e) => {
  const item = e.target.closest('.language-switcher__item');
  if (!item) return;

  const lang = item.dataset.lang;
  const dir = item.dataset.dir;

  document.documentElement.lang = lang;
  document.documentElement.dir = dir;
  localStorage.setItem('lang', lang);
  localStorage.setItem('dir', dir);

  // Update UI
  trigger.querySelector('.language-switcher__text').textContent = item.textContent;
  trigger.setAttribute('aria-expanded', 'false');
  menu.hidden = true;

  // Reload or update translations
  updateTranslations(lang);
});
```

---

## 2. RTL SUPPORT (CSS Logical Properties)

**Rule**: Use logical properties (`margin-inline`, `padding-inline`, `inset-inline`) instead of physical properties (`margin-left`, `padding-right`, `left`).

```css
/* BAD — physical properties */
.card { margin-left: 20px; padding-right: 10px; border-left: 1px solid var(--border); }

/* GOOD — logical properties (auto-flips for RTL) */
.card {
  margin-inline-start: 20px;
  padding-inline-end: 10px;
  border-inline-start: 1px solid var(--border);
}

/* Flipped Icons for RTL */
[dir="rtl"] .icon-flip { transform: scaleX(-1); }
```

---

## 3. DATE/TIME FORMATTING (Intl.DateTimeFormat)

```javascript
// Format date based on current locale
const formatDate = (date, locale = document.documentElement.lang) => {
  return new Intl.DateTimeFormat(locale, {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  }).format(date);
};

// Usage: formatDate(new Date()) -> "April 6, 2026" (en) or "6 أبريل 2026" (ar)
```

---

## 4. NUMBER & CURRENCY FORMATTING

```javascript
// Format currency based on current locale
const formatCurrency = (amount, currency = 'USD', locale = document.documentElement.lang) => {
  return new Intl.NumberFormat(locale, {
    style: 'currency',
    currency: currency,
  }).format(amount);
};

// Usage: formatCurrency(1250.50) -> "$1,250.50" (en) or "١٬٢٥٠٫٥٠ $" (ar)
```

---

## 5. PLURALIZATION RULES

```javascript
// Pluralization using Intl.PluralRules
const getPlural = (count, locale = document.documentElement.lang) => {
  const pr = new Intl.PluralRules(locale);
  const rule = pr.select(count);

  const messages = {
    en: { one: 'item', other: 'items' },
    ar: { zero: 'لا توجد عناصر', one: 'عنصر واحد', two: 'عنصران', few: 'عناصر', many: 'عنصرًا', other: 'عنصر' }
  };

  return messages[locale][rule] || messages[locale].other;
};

// Usage: `${count} ${getPlural(count)}`
```

---

## 6. DYNAMIC TRANSLATION LOADING

```javascript
// Example: Dynamic Translation Loading
async function updateTranslations(lang) {
  const response = await fetch(`/locales/${lang}.json`);
  const translations = await response.json();

  document.querySelectorAll('[data-i18n]').forEach(el => {
    const key = el.dataset.i18n;
    el.textContent = translations[key] || key;
  });
}
```
