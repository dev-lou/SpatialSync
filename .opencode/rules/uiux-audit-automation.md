---
description: >
  Automated accessibility audit skill. Load when the user asks for an a11y audit,
  WCAG report, axe-core scan, or accessibility review of any page or component.
  Uses Chrome DevTools MCP to navigate, inject axe-core, and produce a structured
  WCAG violation report with prioritised fix suggestions. Load after uiux-a11y.md.
---

# UI/UX Audit Automation 2026

> Navigate → inject axe-core → scan → report → fix.
> Produces a structured WCAG 2.1 AA + 3.0 violation report for any live URL or
> local dev server page.

---

## PREREQUISITES

```bash
# Required MCP: Chrome DevTools (or Puppeteer MCP)
# Confirm it is active before running any step:
# - Tool: browser_navigate, browser_evaluate, browser_screenshot

# axe-core CDN (injected at runtime — no install needed):
AXE_CDN = "https://cdnjs.cloudflare.com/ajax/libs/axe-core/4.9.1/axe.min.js"
```

---

## STEP 1 — NAVIGATE TO TARGET

```javascript
// Via Chrome DevTools MCP tool: browser_navigate
await browser_navigate({ url: TARGET_URL });
// Wait for network idle — important for SPAs
await browser_evaluate({ script: `
  await new Promise(resolve => {
    if (document.readyState === 'complete') return resolve();
    window.addEventListener('load', resolve);
  });
  await new Promise(r => setTimeout(r, 500)); // extra tick for hydration
` });
```

**For local dev servers**, use `http://localhost:PORT/PATH`.
**For SPAs**, navigate to the specific route — not just `/`.

---

## STEP 2 — INJECT AXE-CORE

```javascript
await browser_evaluate({ script: `
  // Inject axe-core from CDN
  const script = document.createElement('script');
  script.src = '${AXE_CDN}';
  document.head.appendChild(script);
  await new Promise((res, rej) => {
    script.onload = res;
    script.onerror = () => rej(new Error('axe-core failed to load'));
  });
  console.log('axe-core loaded:', typeof axe !== 'undefined');
` });
```

---

## STEP 3 — RUN THE SCAN

```javascript
const results = await browser_evaluate({ script: `
  // Run full axe scan with WCAG 2.1 AA + 2.2 tags
  const results = await axe.run(document, {
    runOnly: {
      type: 'tag',
      values: ['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa', 'best-practice']
    },
    resultTypes: ['violations', 'incomplete'],
  });

  // Summarise for output
  return {
    url:        location.href,
    timestamp:  new Date().toISOString(),
    violations: results.violations.map(v => ({
      id:          v.id,
      impact:      v.impact,          // critical | serious | moderate | minor
      description: v.description,
      help:        v.help,
      helpUrl:     v.helpUrl,
      wcag:        v.tags.filter(t => t.startsWith('wcag')),
      nodes:       v.nodes.map(n => ({
        html:    n.html.slice(0, 200),
        summary: n.failureSummary,
        target:  n.target,
      })),
    })),
    incomplete: results.incomplete.length,
    passes:     results.passes.length,
  };
` });
```

---

## STEP 4 — GENERATE THE REPORT

Produce the report in this exact structure. Never summarise without the full node list.

```markdown
# Accessibility Audit Report

**URL:** {url}
**Date:** {timestamp}
**Tool:** axe-core 4.9.1 + WCAG 2.1 AA / 2.2 AA

---

## Summary

| Severity   | Count |
|------------|-------|
| 🔴 Critical | {n}   |
| 🟠 Serious  | {n}   |
| 🟡 Moderate | {n}   |
| 🔵 Minor    | {n}   |
| ⚠️ Incomplete (needs manual check) | {n} |
| ✅ Passing rules | {n} |

**Overall status:** FAIL / CONDITIONAL PASS / PASS

---

## Violations (ordered by impact)

### 🔴 [impact] — [violation.id]: [violation.help]

**WCAG criteria:** {wcag tags}
**Reference:** {helpUrl}

**Affected elements ({count} found):**
```html
<!-- Element 1 -->
{node.html}
Target: {node.target}
Failure: {node.summary}
```

**Fix:**
{concrete fix instruction — see Fix Library below}

**Code example:**
```html
<!-- Before -->
{bad example}

<!-- After -->
{fixed example}
```

---

## Incomplete / Needs Manual Review

{list items needing human judgement — colour contrast with images, audio descriptions, etc.}

---

## Quick-Win Fixes (highest impact, lowest effort)

1. …
2. …
3. …

---

## Recommended Next Steps

1. Fix all Critical and Serious violations before launch.
2. Run Lighthouse accessibility score — target ≥ 95.
3. Manual keyboard navigation test (Tab → Enter → Escape flows).
4. Screen reader test: NVDA/Firefox (Windows), VoiceOver/Safari (macOS).
```

---

## STEP 5 — TAKE SCREENSHOT FOR EVIDENCE

```javascript
await browser_screenshot({ path: './audit-screenshots/before-fixes.png' });
```

Re-run after fixes with `path: './audit-screenshots/after-fixes.png'` to confirm.

---

## FIX LIBRARY — Common Violations

### `image-alt` (Critical)
```html
<!-- ❌ Missing alt -->
<img src="hero.jpg">

<!-- ✅ Meaningful image -->
<img src="hero.jpg" alt="Architect reviewing blueprints at a desk" width="1200" height="800" loading="eager">

<!-- ✅ Decorative image -->
<img src="divider.svg" alt="" role="presentation" width="100" height="4" loading="lazy">
```

### `color-contrast` (Serious)
```css
/* ❌ Contrast ratio < 4.5:1 */
.text { color: #aaa; background: #fff; }   /* ratio: 2.32:1 */

/* ✅ Compliant */
.text { color: var(--text-2); }
/* --text-2 must be defined with contrast ≥ 4.5:1 against --bg in tokens.css */
```

### `label` — Input missing label (Critical)
```html
<!-- ❌ -->
<input type="email" placeholder="Email">

<!-- ✅ -->
<label for="email">Email address</label>
<input type="email" id="email" name="email"
  autocomplete="email" aria-describedby="email-help">
<span id="email-help" class="field__helper">We'll never share your email.</span>
```

### `button-name` (Critical)
```html
<!-- ❌ Icon-only with no accessible name -->
<button><svg>…</svg></button>

<!-- ✅ -->
<button aria-label="Close menu">
  <svg aria-hidden="true" focusable="false">…</svg>
</button>
```

### `landmark-one-main` (Moderate)
```html
<!-- ✅ Required landmarks on every page -->
<header role="banner">…</header>
<nav role="navigation" aria-label="Main">…</nav>
<main id="main-content" role="main">…</main>
<footer role="contentinfo">…</footer>
```

### `skip-link` (Serious — best practice)
```html
<!-- First element inside <body> -->
<a href="#main-content" class="skip-link">Skip to main content</a>
```
```css
.skip-link {
  position: absolute; top: -100%; left: var(--space-4);
  background: var(--accent); color: white;
  padding: var(--space-2) var(--space-4);
  border-radius: var(--radius-md); font-weight: 600; z-index: 9999;
  transition: top 0.2s;
}
.skip-link:focus { top: var(--space-4); }
```

### `aria-required-children` (Critical)
```html
<!-- ❌ ul with aria-role="list" but li has wrong role -->
<ul role="listbox"><li>Item</li></ul>

<!-- ✅ -->
<ul role="listbox">
  <li role="option" aria-selected="false">Item</li>
</ul>
```

---

## COMPONENT-LEVEL AUDIT (Storybook / Isolated)

For component-level audits (not full pages), use axe in test files:

```javascript
// vitest / jest — component audit
import { render } from '@testing-library/react';
import { axe, toHaveNoViolations } from 'jest-axe';
import { Button } from './Button';

expect.extend(toHaveNoViolations);

test('Button has no a11y violations in all states', async () => {
  const states = [
    <Button>Default</Button>,
    <Button disabled>Disabled</Button>,
    <Button aria-busy="true">Loading</Button>,
    <Button variant="icon" aria-label="Close"><Icon /></Button>,
  ];
  for (const element of states) {
    const { container } = render(element);
    const results = await axe(container);
    expect(results).toHaveNoViolations();
  }
});
```

---

## AUDIT SCHEDULE

| When | What | Target |
|------|------|--------|
| During development | Per-component axe in unit tests | 0 violations |
| Before PR merge | Full-page axe scan on local server | 0 Critical/Serious |
| Before release | Lighthouse a11y score | ≥ 95 |
| Monthly | Manual screen reader walkthrough | All key flows |
| After major feature | Re-run full page scan | 0 violations |
