# UI/UX Testing 2026

> Testing Trophy, component testing with Storybook, accessibility testing (axe-core),
> visual regression, and interaction testing for UI/UX systems.

---

## THE TESTING TROPHY (Kent C. Dodds)

```
         /\
        /E2E\         ← Few, high-value, slow (Playwright)
       /──────\
      /Integr. \      ← Most tests live here (React Testing Library)
     /──────────\
    /   Unit     \    ← Small, fast, pure functions/hooks only
   /──────────────\
  / Static (types) \  ← TypeScript + ESLint — free tests
 /──────────────────\
```

**Ratio target:** Static > Unit > Integration > E2E
**Most value:** Integration tests — test behavior, not implementation.

---

## STEP 1 — STATIC ANALYSIS (Free Tests)

```json
// package.json — run before any other test
"scripts": {
  "lint":    "eslint src --ext .js,.jsx,.ts,.tsx",
  "type-check": "tsc --noEmit",
  "check":   "npm run lint && npm run type-check"
}
```

```javascript
// .eslintrc — recommended rules for UI
{
  "extends": [
    "eslint:recommended",
    "plugin:react-hooks/recommended",
    "plugin:jsx-a11y/recommended",  // catches a11y issues at lint time
    "plugin:@typescript-eslint/recommended"
  ]
}
```

**jsx-a11y catches:** missing alt text, bad button usage, unlabeled inputs, missing ARIA.
Zero runtime cost. Fix at write time.

---

## STEP 2 — UNIT TESTS (Vitest / Jest)

Only for **pure functions** — formatters, validators, utils. Not for components.

```javascript
// lib/format.test.js
import { describe, it, expect } from 'vitest';
import { formatPrice, formatDate, truncate } from './format';

describe('formatPrice', () => {
  it('formats USD correctly',      () => expect(formatPrice(9.99, 'USD')).toBe('$9.99'));
  it('handles zero',               () => expect(formatPrice(0, 'USD')).toBe('$0.00'));
  it('handles large numbers',      () => expect(formatPrice(1234.5)).toBe('$1,234.50'));
});

describe('truncate', () => {
  it('truncates long strings',     () => expect(truncate('Hello World', 5)).toBe('Hello…'));
  it('leaves short strings alone', () => expect(truncate('Hi', 10)).toBe('Hi'));
});
```

---

## STEP 3 — COMPONENT TESTING (Storybook + React Testing Library)

### Storybook Setup (2026: Storybook 8)

```bash
npx storybook@latest init
# Supports: React, Vue, Angular, Svelte, Next.js, SvelteKit
```

### Writing Stories (Component Documentation + Test)

```jsx
// components/ui/Button/Button.stories.jsx
import { Button } from './Button';

export default {
  title: 'UI/Button',
  component: Button,
  parameters: {
    layout: 'centered',
    backgrounds: {
      default: 'light',
      values: [
        { name: 'light', value: 'var(--bg)' },
        { name: 'dark', value: '#0A0B0E' },
      ],
    },
  },
  argTypes: {
    variant: { control: 'select', options: ['primary', 'secondary', 'ghost', 'danger'] },
    size:    { control: 'select', options: ['sm', 'md', 'lg', 'xl'] },
  },
};

// Stories = visual test cases
export const Primary    = { args: { variant: 'primary', children: 'Get Started' } };
export const Ghost      = { args: { variant: 'ghost',   children: 'Learn More' } };
export const Loading    = { args: { variant: 'primary', loading: true, children: 'Save' } };
export const Disabled   = { args: { variant: 'primary', disabled: true, children: 'Unavailable' } };
export const AllSizes   = {
  render: () => (
    <div style={{ display: 'flex', gap: 16, alignItems: 'center' }}>
      <Button size="sm">Small</Button>
      <Button size="md">Medium</Button>
      <Button size="lg">Large</Button>
      <Button size="xl">XL</Button>
    </div>
  ),
};
```

### Interaction Testing (Storybook + play())

```jsx
import { userEvent, within, expect } from '@storybook/test';

export const ClickFeedback = {
  args: { children: 'Submit' },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement);
    const button = canvas.getByRole('button', { name: /submit/i });

    // Simulate click
    await userEvent.click(button);

    // Assert feedback
    await expect(button).toHaveClass('btn--loading');
  },
};
```

### React Testing Library (Behavior Testing)

```jsx
// components/ui/Button/Button.test.jsx
import { render, screen, fireEvent } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { Button } from './Button';

describe('Button', () => {
  it('renders with correct text', () => {
    render(<Button>Click me</Button>);
    expect(screen.getByRole('button', { name: /click me/i })).toBeInTheDocument();
  });

  it('calls onClick when clicked', async () => {
    const handleClick = vi.fn();
    render(<Button onClick={handleClick}>Click</Button>);
    await userEvent.click(screen.getByRole('button'));
    expect(handleClick).toHaveBeenCalledOnce();
  });

  it('is disabled when disabled prop is true', () => {
    render(<Button disabled>Can\'t click</Button>);
    expect(screen.getByRole('button')).toBeDisabled();
  });

  it('shows loading spinner when loading', () => {
    render(<Button loading>Save</Button>);
    expect(screen.getByRole('button')).toHaveClass('btn--loading');
    // Text hidden but button still accessible
    expect(screen.getByRole('button')).toBeInTheDocument();
  });
});
```

**RTL Rule:** Query by role/label, never by test-id.
```javascript
✅ screen.getByRole('button', { name: /submit/i })
✅ screen.getByLabelText('Email address')
✅ screen.getByText('Success message')
❌ screen.getByTestId('submit-btn')   // brittle, couples to impl
❌ container.querySelector('.btn')    // implementation detail
```

---

## STEP 4 — ACCESSIBILITY TESTING (axe-core)

### Automated a11y in Storybook

```bash
npm install --save-dev @storybook/addon-a11y
```

```javascript
// .storybook/main.js
export default {
  addons: ['@storybook/addon-a11y'],
};
```

Now every story has an "Accessibility" tab with violations, passes, and incomplete checks.

### Automated a11y in Tests (vitest-axe)

```bash
npm install --save-dev vitest-axe @axe-core/react
```

```jsx
import { render } from '@testing-library/react';
import { axe, toHaveNoViolations } from 'vitest-axe';
expect.extend(toHaveNoViolations);

describe('Modal accessibility', () => {
  it('has no axe violations', async () => {
    const { container } = render(<Modal title="Confirm" open>Content</Modal>);
    const results = await axe(container);
    expect(results).toHaveNoViolations();
  });
});
```

### Manual a11y Checklist

```
□ Tab order: logical flow, no focus traps (except modals)
□ Screen reader: all content readable by VoiceOver/NVDA
□ Keyboard: Enter/Space activate buttons; Escape closes dialogs
□ Color contrast: 4.5:1 body, 3:1 large text (use WebAIM Checker)
□ Focus visible: `:focus-visible` outline never removed
□ Images: meaningful = alt text, decorative = alt=""
□ Forms: every input has a visible label (not just placeholder)
□ Errors: announced via role="alert" or aria-live="polite"
□ Skip link: first focusable element on every page
□ ARIA landmarks: banner, main, nav, complementary, contentinfo
□ Headings: h1 once, no skipped levels (h1 → h3 is wrong)
□ Motion: all animations respect prefers-reduced-motion
□ Zoom: 400% zoom usable (WCAG 1.4.10 Reflow)
```

---

## STEP 5 — VISUAL REGRESSION (Chromatic / Percy)

Catches unintended visual changes — the "I didn't mean to change that button" problem.

### Chromatic (by Storybook team — recommended)

```bash
npm install --save-dev chromatic
```

```json
// package.json
"scripts": {
  "chromatic": "chromatic --project-token=YOUR_TOKEN"
}
```

```yaml
# .github/workflows/chromatic.yml
name: Chromatic
on: [push]
jobs:
  chromatic:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
        with: { fetch-depth: 0 }
      - run: npm ci
      - run: npm run chromatic
        env: { CHROMATIC_PROJECT_TOKEN: ${{ secrets.CHROMATIC_TOKEN }} }
```

Chromatic compares every story against a baseline. PR shows diffs. You accept or reject changes.

---

## STEP 6 — E2E TESTING (Playwright)

Only for critical user journeys. Not for every component.

```bash
npm init playwright@latest
```

### What to E2E test (pick max 5–10 flows)

```
✅ User can sign up and log in
✅ User can complete a purchase (if e-commerce)
✅ Critical form submission works end-to-end
✅ Navigation works on mobile (hamburger menu)
✅ Dark mode persists across page reload
❌ Every button click (use RTL for this)
❌ Visual appearance (use Chromatic for this)
```

```javascript
// tests/auth.spec.js
import { test, expect } from '@playwright/test';

test('user can sign in', async ({ page }) => {
  await page.goto('/login');
  await page.getByLabel('Email').fill('test@example.com');
  await page.getByLabel('Password').fill('password123');
  await page.getByRole('button', { name: /sign in/i }).click();
  await expect(page).toHaveURL('/dashboard');
  await expect(page.getByText('Welcome back')).toBeVisible();
});

test('dark mode persists on reload', async ({ page }) => {
  await page.goto('/');
  await page.getByRole('button', { name: /toggle.*theme/i }).click();
  await expect(page.locator('html')).toHaveAttribute('data-theme', 'dark');
  await page.reload();
  await expect(page.locator('html')).toHaveAttribute('data-theme', 'dark');
});
```

---

## WCAG 3.0 AWARENESS (Coming 2026)

WCAG 3.0 moves from **pass/fail binary** to **Bronze/Silver/Gold scoring**.

| Level | What it means |
|-------|--------------|
| **Bronze** | Core accessibility — replaces WCAG 2.1 AA as minimum |
| **Silver** | Better UX for disabled users — goes beyond technical checklist |
| **Gold** | Exceptional inclusion — tested with real disabled users |

**Key shifts from WCAG 2.x:**
- **Outcomes over rules:** "Can this user complete the task?" not "Does this pass criterion 1.4.3?"
- **Functional categories:** Vision, Hearing, Mobility, Cognition — not just technical specs
- **User testing required** for Silver/Gold — automated tools are insufficient
- **Visual contrast** gets a new APCA (Advanced Perceptual Contrast Algorithm) metric

**Action now:** Aim for WCAG 2.1 AA compliance (still the law in most regions).
Structure your code so semantic HTML and ARIA are correct — WCAG 3.0 Bronze will follow naturally.

---

## TESTING QUICK REFERENCE

| Layer | Tool | What it catches | Speed |
|-------|------|----------------|-------|
| Static | TypeScript + ESLint + jsx-a11y | Type errors, a11y lint | Instant |
| Unit | Vitest | Pure function logic | <1s |
| Component | RTL + Storybook | Behavior, render, events | Fast |
| A11y | axe-core + Storybook a11y tab | ARIA, contrast, focus | Medium |
| Visual | Chromatic | Unintended visual changes | Medium |
| E2E | Playwright | Critical user journeys | Slow |

**Run order (CI):** Static → Unit → Component → A11y → Visual → E2E
