---
description: >
  AI test generation skill. Load when the user asks to "generate tests", "write
  E2E tests", "create integration tests", "test this user story", or "add Playwright /
  Cypress coverage". Produces integration and end-to-end tests from user stories,
  component files, or page interactions. Goes beyond unit tests — focuses on
  behaviour, not implementation. Load alongside uiux-testing.md.
---

# AI Test Generation 2026

> User story → test scenarios → Playwright / Cypress implementation → CI integration.
> Generates behaviour-focused tests that catch real regressions, not just
> implementation details.

---

## TESTING PHILOSOPHY

```
Write tests that resemble how users interact with your application.
— Kent C. Dodds

Tests should catch real regressions, not break on refactors.
Good test: "user can complete checkout" — survives CSS class rename
Bad test:  "button has class 'btn-primary'" — breaks on any rename
```

**Rule:** Test **outcomes**, not **implementation**.

---

## STEP 1 — EXTRACT TEST SCENARIOS FROM USER STORIES

### Input format — User Story

```
As a [role], I want to [action] so that [outcome].

Acceptance criteria:
- [criterion 1]
- [criterion 2]
- [criterion 3]
```

### Process: Story → Scenarios

For each user story, generate test scenarios covering:

```markdown
## Test Scenarios for: {Story Title}

### Happy Path
1. {Normal, expected flow from start to success}

### Alternate Paths
2. {Alternative but valid path}
3. {Optional step skipped}

### Error Paths
4. {Required field missing}
5. {Invalid input format}
6. {Network/server error during submission}
7. {Session expires mid-flow}

### Edge Cases
8. {Minimum valid input}
9. {Maximum length input}
10. {Special characters in input}

### Accessibility
11. {Complete flow using keyboard only}
12. {Complete flow with screen reader announced states}
```

---

## STEP 2 — PLAYWRIGHT E2E TESTS

### File structure

```
tests/
├── e2e/
│   ├── auth/
│   │   ├── login.spec.ts
│   │   └── registration.spec.ts
│   ├── checkout/
│   │   ├── checkout-happy-path.spec.ts
│   │   └── checkout-errors.spec.ts
│   └── fixtures/
│       ├── users.ts
│       └── products.ts
├── integration/
│   ├── api/
│   │   └── orders.test.ts
│   └── components/
│       └── CheckoutForm.test.tsx
└── playwright.config.ts
```

### Playwright config

```typescript
// playwright.config.ts
import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  testDir: './tests/e2e',
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: process.env.CI ? 1 : undefined,
  reporter: [['html'], ['github']],

  use: {
    baseURL:     process.env.BASE_URL ?? 'http://localhost:3000',
    trace:       'on-first-retry',
    screenshot:  'only-on-failure',
    video:       'retain-on-failure',
  },

  projects: [
    // Setup — create auth state once
    { name: 'setup', testMatch: /.*\.setup\.ts/ },

    // Browsers
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
      dependencies: ['setup'],
    },
    {
      name: 'Mobile Chrome',
      use: { ...devices['Pixel 5'] },
      dependencies: ['setup'],
    },
    {
      name: 'Mobile Safari',
      use: { ...devices['iPhone 14'] },
      dependencies: ['setup'],
    },
  ],

  webServer: {
    command: 'npm run build && npm run start',
    url: 'http://localhost:3000',
    reuseExistingServer: !process.env.CI,
  },
});
```

### Auth fixture (avoid login in every test)

```typescript
// tests/e2e/fixtures/auth.setup.ts
import { test as setup } from '@playwright/test';
import path from 'path';

const authFile = path.join(__dirname, '../.auth/user.json');

setup('authenticate', async ({ page }) => {
  await page.goto('/login');
  await page.getByLabel('Email').fill('test@example.com');
  await page.getByLabel('Password').fill('Password1!');
  await page.getByRole('button', { name: 'Sign in' }).click();
  await page.waitForURL('/dashboard');
  await page.context().storageState({ path: authFile });
});
```

### E2E test — Checkout happy path

```typescript
// tests/e2e/checkout/checkout-happy-path.spec.ts
import { test, expect } from '@playwright/test';

test.use({ storageState: 'tests/e2e/.auth/user.json' });

test.describe('Checkout — Happy Path', () => {

  test.beforeEach(async ({ page }) => {
    // Start with a known cart state via API call (faster than UI)
    await page.request.post('/api/cart/reset');
    await page.request.post('/api/cart/add', {
      data: { productId: 'PROD-001', quantity: 2 },
    });
    await page.goto('/checkout');
  });

  test('user can complete checkout with credit card', async ({ page }) => {
    // Step 1 — Shipping address
    await expect(page.getByRole('heading', { name: 'Shipping' })).toBeVisible();
    await page.getByLabel('First name').fill('Jane');
    await page.getByLabel('Last name').fill('Doe');
    await page.getByLabel('Address').fill('123 Main St');
    await page.getByLabel('City').fill('London');
    await page.getByLabel('Postcode').fill('SW1A 1AA');
    await page.getByRole('button', { name: 'Continue' }).click();

    // Step 2 — Payment
    await expect(page.getByRole('heading', { name: 'Payment' })).toBeVisible();
    // Stripe iframe
    const stripeFrame = page.frameLocator('iframe[name*="stripe"]').first();
    await stripeFrame.getByPlaceholder('Card number').fill('4242 4242 4242 4242');
    await stripeFrame.getByPlaceholder('MM / YY').fill('12 / 26');
    await stripeFrame.getByPlaceholder('CVC').fill('123');
    await page.getByRole('button', { name: /pay/i }).click();

    // Confirm
    await page.waitForURL(/\/order\/\w+/);
    await expect(page.getByRole('heading', { name: /order confirmed/i })).toBeVisible();
    await expect(page.getByText('Jane Doe')).toBeVisible();
    await expect(page.getByText('123 Main St')).toBeVisible();
  });

  test('order confirmation email is sent', async ({ page, request }) => {
    // After successful checkout, check the test email inbox
    await completeCheckout(page);
    const emails = await request.get('http://localhost:8025/api/v2/messages');
    const body   = await emails.json();
    expect(body.total).toBeGreaterThan(0);
    expect(body.items[0].Content.Headers.Subject[0]).toContain('Order confirmed');
  });

});
```

### E2E test — Error paths

```typescript
test.describe('Checkout — Error Paths', () => {

  test('shows inline error for missing required fields', async ({ page }) => {
    await page.goto('/checkout');
    // Click Continue without filling anything
    await page.getByRole('button', { name: 'Continue' }).click();

    // Verify error messages (not just browser validation)
    await expect(page.getByText('First name is required')).toBeVisible();
    await expect(page.getByText('Address is required')).toBeVisible();
    // Verify focus moves to first error field
    await expect(page.getByLabel('First name')).toBeFocused();
  });

  test('handles network failure gracefully', async ({ page }) => {
    await page.route('**/api/orders', route => route.abort('failed'));
    await completeCheckoutForm(page);
    await page.getByRole('button', { name: /pay/i }).click();

    await expect(page.getByRole('alert')).toContainText(
      'Something went wrong. Your card was not charged.'
    );
    // Confirm user can retry
    await expect(page.getByRole('button', { name: /pay/i })).toBeEnabled();
  });

  test('shows loading state during payment processing', async ({ page }) => {
    // Slow down the payment API
    await page.route('**/api/orders', async route => {
      await new Promise(r => setTimeout(r, 2000));
      await route.continue();
    });
    await completeCheckoutForm(page);
    await page.getByRole('button', { name: /pay/i }).click();

    // Button should show loading state immediately
    await expect(page.getByRole('button', { name: /processing/i })).toBeVisible();
    await expect(page.getByRole('button', { name: /pay/i })).toBeDisabled();
  });

});
```

### E2E test — Accessibility

```typescript
test.describe('Checkout — Accessibility', () => {

  test('is keyboard navigable end-to-end', async ({ page }) => {
    await page.goto('/checkout');
    // Tab through all form fields without mouse
    await page.keyboard.press('Tab');
    await expect(page.getByLabel('First name')).toBeFocused();
    await page.keyboard.type('Jane');
    await page.keyboard.press('Tab');
    await expect(page.getByLabel('Last name')).toBeFocused();
    // … continue through all fields
    // Submit with Enter
    await page.keyboard.press('Tab'); // reach button
    await page.keyboard.press('Enter');
    await expect(page.getByRole('heading', { name: 'Payment' })).toBeVisible();
  });

  test('axe: checkout page has no accessibility violations', async ({ page }) => {
    const { checkA11y, injectAxe } = await import('axe-playwright');
    await page.goto('/checkout');
    await injectAxe(page);
    await checkA11y(page, undefined, {
      detailedReport: true,
      detailedReportOptions: { html: true },
    });
  });

});
```

---

## STEP 3 — INTEGRATION TESTS (React Testing Library)

Integration tests for complex components with user interaction:

```typescript
// tests/integration/components/CheckoutForm.test.tsx
import { render, screen, userEvent } from '@testing-library/react';
import { vi, describe, it, expect, beforeEach } from 'vitest';
import { CheckoutForm } from '../../src/components/CheckoutForm';
import { server } from '../mocks/server'; // MSW mock server
import { http, HttpResponse } from 'msw';

describe('CheckoutForm', () => {
  const user = userEvent.setup();

  describe('Form validation', () => {
    it('shows error when submitting empty form', async () => {
      render(<CheckoutForm />);
      await user.click(screen.getByRole('button', { name: /continue/i }));

      expect(screen.getByText('First name is required')).toBeInTheDocument();
      expect(screen.getByText('Address is required')).toBeInTheDocument();
      // Focus should be on first error field
      expect(screen.getByLabelText('First name')).toHaveFocus();
    });

    it('clears error when user corrects the field', async () => {
      render(<CheckoutForm />);
      await user.click(screen.getByRole('button', { name: /continue/i }));
      await user.type(screen.getByLabelText('First name'), 'Jane');

      expect(screen.queryByText('First name is required')).not.toBeInTheDocument();
    });
  });

  describe('Loading states', () => {
    it('disables submit button and shows spinner during submission', async () => {
      server.use(
        http.post('/api/orders', async () => {
          await new Promise(r => setTimeout(r, 100));
          return HttpResponse.json({ orderId: '123' });
        })
      );
      render(<CheckoutForm />);
      await fillValidForm(user);
      const btn = screen.getByRole('button', { name: /pay/i });
      await user.click(btn);

      expect(btn).toBeDisabled();
      expect(btn).toHaveAttribute('aria-busy', 'true');
    });
  });

  describe('Error handling', () => {
    it('shows server error message without crashing', async () => {
      server.use(
        http.post('/api/orders', () =>
          HttpResponse.json({ error: 'Card declined' }, { status: 402 })
        )
      );
      render(<CheckoutForm />);
      await fillValidForm(user);
      await user.click(screen.getByRole('button', { name: /pay/i }));

      expect(screen.getByRole('alert')).toHaveTextContent('Card declined');
    });
  });
});
```

---

## STEP 4 — API INTEGRATION TESTS

```typescript
// tests/integration/api/orders.test.ts
import { describe, it, expect, beforeAll, afterAll } from 'vitest';
import { createTestServer } from '../helpers/server';
import { db } from '../../src/lib/db';

describe('POST /api/orders', () => {
  let app: ReturnType<typeof createTestServer>;
  let authToken: string;

  beforeAll(async () => {
    app = createTestServer();
    authToken = await getAuthToken('user@example.com');
  });

  afterAll(async () => {
    await db.orders.deleteMany({ where: { userId: 'test-user' } });
  });

  it('creates an order and returns 201', async () => {
    const response = await app.inject({
      method: 'POST',
      url: '/api/orders',
      headers: { Authorization: `Bearer ${authToken}` },
      payload: { items: [{ productId: 'PROD-001', quantity: 2 }] },
    });

    expect(response.statusCode).toBe(201);
    const body = response.json();
    expect(body).toMatchObject({
      orderId:    expect.stringMatching(/^ord_/),
      status:     'pending',
      totalPence: expect.any(Number),
    });

    // Verify DB side-effect
    const order = await db.orders.findUnique({ where: { id: body.orderId } });
    expect(order?.status).toBe('pending');
  });

  it('returns 401 without auth token', async () => {
    const response = await app.inject({ method: 'POST', url: '/api/orders' });
    expect(response.statusCode).toBe(401);
  });

  it('returns 400 for empty items array', async () => {
    const response = await app.inject({
      method: 'POST', url: '/api/orders',
      headers: { Authorization: `Bearer ${authToken}` },
      payload: { items: [] },
    });
    expect(response.statusCode).toBe(400);
    expect(response.json().error).toContain('items');
  });

  it('is idempotent with idempotency key', async () => {
    const key = crypto.randomUUID();
    const first  = await makeOrderRequest(authToken, key);
    const second = await makeOrderRequest(authToken, key);
    expect(first.json().orderId).toBe(second.json().orderId);
    // Only one order created in DB
    const count = await db.orders.count({ where: { idempotencyKey: key } });
    expect(count).toBe(1);
  });
});
```

---

## STEP 5 — CI INTEGRATION

```yaml
# .github/workflows/test.yml
name: Tests
on: [push, pull_request]
jobs:
  integration:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-node@v4
        with: { node-version: '20' }
      - run: npm ci
      - run: npm run test:integration   # vitest --run

  e2e:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-node@v4
        with: { node-version: '20' }
      - run: npm ci
      - run: npx playwright install --with-deps chromium
      - run: npm run build
      - run: npm run test:e2e
        env:
          BASE_URL: http://localhost:3000
      - uses: actions/upload-artifact@v4
        if: failure()
        with:
          name: playwright-report
          path: playwright-report/
          retention-days: 7
```

---

## TEST GENERATION CHECKLIST

Before finalising generated tests:

```markdown
- [ ] Every acceptance criterion has at least one test
- [ ] Happy path tested end-to-end
- [ ] All error conditions from the API have a test
- [ ] Loading/skeleton states are asserted
- [ ] Empty states are tested
- [ ] Keyboard navigation test for any form or interactive flow
- [ ] axe-core test for any new page
- [ ] Tests use accessible queries (getByRole, getByLabel) — not getByTestId or querySelector
- [ ] No implementation details (class names, component names) in assertions
- [ ] Database is cleaned up after tests (no test pollution)
- [ ] Tests pass in CI (not just locally)
```
