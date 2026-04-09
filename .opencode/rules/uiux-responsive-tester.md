---
description: Generate responsive testing matrix for viewports, touch targets, overflow detection, and font scaling verification.
---

# UI/UX Responsive Testing Skill

> Systematic responsive design verification across viewports.
> Ensures touch targets, overflow handling, and font scaling.

---

## OVERVIEW

This skill generates comprehensive responsive testing checklists covering:
1. Viewport breakpoint testing
2. Touch target size verification (≥44px)
3. Horizontal overflow detection
4. Font scaling and readability
5. Device-specific considerations

---

## STANDARD VIEWPORTS

### Primary Test Breakpoints

| Name | Width | Device Example | Priority |
|------|-------|----------------|----------|
| Mobile S | 320px | iPhone SE | 🟡 |
| Mobile M | 375px | iPhone 12/13 | 🔴 |
| Mobile L | 390px | iPhone 14/15 | 🔴 |
| Mobile XL | 428px | iPhone 14 Plus | 🟡 |
| Tablet | 768px | iPad Mini | 🔴 |
| Tablet L | 1024px | iPad Pro 11" | 🟡 |
| Desktop | 1280px | Laptop | 🔴 |
| Desktop L | 1440px | Desktop | 🟡 |
| Desktop XL | 1920px | Large monitor | 🟡 |

### Breakpoint Tokens

```css
:root {
  --breakpoint-sm: 640px;
  --breakpoint-md: 768px;
  --breakpoint-lg: 1024px;
  --breakpoint-xl: 1280px;
}

/* Usage */
@media (min-width: 768px) { /* md and up */ }
@media (min-width: 1024px) { /* lg and up */ }
```

---

## TESTING MATRIX TEMPLATE

### Page: [Page Name]

| Viewport | Layout OK | Touch Targets | No Overflow | Font Readable | Notes |
|----------|-----------|---------------|-------------|---------------|-------|
| 390px | ✅ / ❌ | ✅ / ❌ | ✅ / ❌ | ✅ / ❌ | |
| 768px | ✅ / ❌ | ✅ / ❌ | ✅ / ❌ | ✅ / ❌ | |
| 1280px | ✅ / ❌ | ✅ / ❌ | ✅ / ❌ | ✅ / ❌ | |

---

## TOUCH TARGET REQUIREMENTS

### Minimum Sizes

| Element | Minimum Size | WCAG Level |
|---------|--------------|------------|
| Interactive elements | 44×44px | AAA (recommended) |
| Links in text | 24×24px | AA (minimum) |
| Dense UI (technical) | 32×32px | Context-dependent |

### CSS Implementation

```css
/* Base interactive element */
.btn,
.link,
.clickable {
  min-height: 44px;
  min-width: 44px;
  padding: var(--space-3) var(--space-4); /* 12px 16px */
}

/* Touch-friendly spacing for links */
.nav-link {
  padding: var(--space-3);
  margin: calc(var(--space-1) * -1); /* Negative margin to maintain visual spacing */
}

/* Icon buttons */
.btn--icon {
  width: 44px;
  height: 44px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

/* Form inputs */
input,
select,
textarea {
  min-height: 44px;
  padding: var(--space-3);
}
```

### Testing Script

```javascript
// Find elements smaller than 44px touch target
function findSmallTouchTargets() {
  const interactiveElements = document.querySelectorAll(
    'a, button, input, select, textarea, [role="button"], [onclick]'
  );
  
  const violations = [];
  
  interactiveElements.forEach(el => {
    const rect = el.getBoundingClientRect();
    if (rect.width < 44 || rect.height < 44) {
      violations.push({
        element: el,
        width: rect.width,
        height: rect.height,
        selector: getSelector(el)
      });
    }
  });
  
  console.table(violations);
  return violations;
}

function getSelector(el) {
  if (el.id) return `#${el.id}`;
  if (el.className) return `.${el.className.split(' ').join('.')}`;
  return el.tagName.toLowerCase();
}
```

---

## OVERFLOW DETECTION

### CSS Prevention

```css
/* Prevent horizontal overflow */
html, body {
  overflow-x: hidden;
  max-width: 100vw;
}

/* Images should never overflow */
img {
  max-width: 100%;
  height: auto;
}

/* Tables need horizontal scroll wrapper */
.table-responsive {
  width: 100%;
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}

/* Long words should break */
.content {
  overflow-wrap: break-word;
  word-wrap: break-word;
  hyphens: auto;
}

/* URLs and code should scroll or truncate */
.url, code {
  overflow-x: auto;
  white-space: nowrap;
}
```

### Testing Script

```javascript
// Detect horizontal overflow
function detectOverflow() {
  const docWidth = document.documentElement.clientWidth;
  const violations = [];
  
  document.querySelectorAll('*').forEach(el => {
    const rect = el.getBoundingClientRect();
    if (rect.right > docWidth || rect.left < 0) {
      violations.push({
        element: el,
        right: rect.right,
        overflow: rect.right - docWidth,
        selector: getSelector(el)
      });
    }
  });
  
  if (violations.length > 0) {
    console.warn('Horizontal overflow detected:');
    console.table(violations);
  } else {
    console.log('✅ No horizontal overflow');
  }
  
  return violations;
}
```

### DevTools Check

1. Open DevTools → Elements
2. Select `<html>` element
3. Add temporary style: `outline: 1px solid red` to all elements
4. Scroll right to see overflowing elements

```css
/* Debug overflow (temporary) */
* { outline: 1px solid rgba(255,0,0,0.2); }
```

---

## FONT SCALING

### Requirements

| Text Type | Minimum Size | Line Height |
|-----------|--------------|-------------|
| Body text | 16px (1rem) | 1.5 |
| Small text | 14px (0.875rem) | 1.5 |
| Captions | 12px (0.75rem) | 1.4 |
| Headings | Fluid (clamp) | 1.2-1.3 |

### Fluid Typography

```css
:root {
  /* Fluid body text: 16px at 320px, 18px at 1280px */
  --text-base: clamp(1rem, 0.95rem + 0.25vw, 1.125rem);
  
  /* Fluid headings */
  --text-4xl: clamp(2rem, 1.5rem + 2.5vw, 3rem);
  --text-3xl: clamp(1.75rem, 1.25rem + 2vw, 2.25rem);
  --text-2xl: clamp(1.5rem, 1.25rem + 1vw, 1.75rem);
}

body {
  font-size: var(--text-base);
  line-height: 1.5;
}
```

### Zoom Testing

Test at these zoom levels:
- 100% (default)
- 200% (WCAG requirement)
- 400% (WCAG 2.1 AA requirement)

```javascript
// Test readability at different zoom levels
function testZoomLevels() {
  const zoomLevels = [100, 125, 150, 175, 200, 400];
  
  zoomLevels.forEach(zoom => {
    console.log(`\n--- Testing at ${zoom}% ---`);
    document.body.style.zoom = zoom / 100;
    
    // Check for overflow
    const overflow = detectOverflow();
    
    // Check text sizes
    const smallText = document.querySelectorAll('*');
    smallText.forEach(el => {
      const fontSize = parseFloat(getComputedStyle(el).fontSize);
      if (fontSize < 12) {
        console.warn(`Small text at ${zoom}%:`, el, `${fontSize}px`);
      }
    });
  });
  
  // Reset
  document.body.style.zoom = 1;
}
```

---

## DEVICE-SPECIFIC CHECKS

### Mobile (390px)

- [ ] Nav collapses to hamburger menu
- [ ] Cards stack vertically
- [ ] Touch targets ≥44px
- [ ] No horizontal scroll
- [ ] Text readable without zoom
- [ ] Forms are usable (inputs not too small)
- [ ] Modals fit within viewport
- [ ] Bottom sheet patterns work

### Tablet (768px)

- [ ] 2-column layouts work
- [ ] Sidebar behavior correct (hidden/visible)
- [ ] Tables responsive or scrollable
- [ ] Images properly sized
- [ ] Touch and mouse both work

### Desktop (1280px)

- [ ] Max-width containers applied
- [ ] Multi-column layouts render
- [ ] Hover states work
- [ ] Keyboard navigation functional
- [ ] Focus indicators visible

---

## AUTOMATED TESTING

### Playwright Responsive Tests

```typescript
import { test, expect, devices } from '@playwright/test';

const viewports = [
  { name: 'Mobile', width: 390, height: 844 },
  { name: 'Tablet', width: 768, height: 1024 },
  { name: 'Desktop', width: 1280, height: 800 },
];

for (const viewport of viewports) {
  test(`Page renders correctly at ${viewport.name}`, async ({ page }) => {
    await page.setViewportSize({ 
      width: viewport.width, 
      height: viewport.height 
    });
    
    await page.goto('/');
    
    // Check no horizontal overflow
    const bodyWidth = await page.evaluate(() => document.body.scrollWidth);
    expect(bodyWidth).toBeLessThanOrEqual(viewport.width);
    
    // Check touch targets
    const smallButtons = await page.evaluate(() => {
      const buttons = document.querySelectorAll('button, a, [role="button"]');
      return Array.from(buttons).filter(b => {
        const rect = b.getBoundingClientRect();
        return rect.width < 44 || rect.height < 44;
      }).length;
    });
    expect(smallButtons).toBe(0);
    
    // Visual snapshot
    await expect(page).toHaveScreenshot(`homepage-${viewport.name}.png`);
  });
}
```

### CSS Container Query Testing

```css
/* Container query breakpoints */
.card-container {
  container-type: inline-size;
  container-name: card;
}

.card {
  /* Mobile layout */
  display: block;
}

@container card (min-width: 400px) {
  .card {
    display: grid;
    grid-template-columns: 1fr 2fr;
  }
}
```

---

## TESTING CHECKLIST

### Before Release

- [ ] Tested on 390px viewport
- [ ] Tested on 768px viewport
- [ ] Tested on 1280px viewport
- [ ] Touch targets ≥44px verified
- [ ] No horizontal overflow
- [ ] 200% zoom tested
- [ ] 400% zoom tested
- [ ] Landscape orientation checked
- [ ] Real device tested (not just emulator)

### Common Issues to Watch

| Issue | Solution |
|-------|----------|
| Fixed-width elements | Use `max-width: 100%` |
| Oversized images | Add `max-width: 100%; height: auto;` |
| Tiny touch targets | Increase padding/size to 44px |
| Text too small | Use fluid typography |
| Horizontal scroll | Check for `width` values in px |
| Cramped mobile layout | Increase padding at small viewports |

---

## REPORTING TEMPLATE

```markdown
# Responsive Testing Report

**Page**: [URL]
**Date**: [Date]
**Tester**: [Name/AI]

## Summary

| Viewport | Pass/Fail | Issues |
|----------|-----------|--------|
| 390px    | ✅ / ❌   | X      |
| 768px    | ✅ / ❌   | X      |
| 1280px   | ✅ / ❌   | X      |

## Touch Target Violations

| Element | Current Size | Required | Location |
|---------|--------------|----------|----------|
| `.btn-small` | 32×32px | 44×44px | Header nav |

## Overflow Issues

| Element | Overflow Amount | Viewport | Fix |
|---------|-----------------|----------|-----|
| `.code-block` | 45px | 390px | Add `overflow-x: auto` |

## Font Scaling Issues

| Issue | Viewport | Zoom Level |
|-------|----------|------------|
| Text overlaps | 390px | 200% |

## Recommendations

1. [Priority fix]
2. [Priority fix]
3. [Priority fix]
```
