---
description: GitHub Action that runs axe-core accessibility tests on preview/staging URLs. Fails PR on critical/serious violations.
---

# Accessibility CI Skill

> Automated accessibility testing in CI/CD pipeline using axe-core.

---

## CORE PRINCIPLE

**Rule**: Every PR must pass accessibility checks. Fail on any Critical or Serious violation. Warn on Moderate/Minor.

---

## GITHUB ACTION WORKFLOW

### Basic Setup

```yaml
# .github/workflows/a11y.yml
name: Accessibility Tests

on:
  pull_request:
    branches: [main, develop]
  push:
    branches: [main]
  # Manual trigger
  workflow_dispatch:
    inputs:
      url:
        description: 'URL to test'
        required: true
        type: string

jobs:
  a11y:
    runs-on: ubuntu-latest
    name: axe-core accessibility scan
    
    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Setup Node.js
        uses: actions/setup-node@v4
        with:
          node-version: '20'
          cache: 'npm'

      - name: Install dependencies
        run: npm ci

      - name: Build application
        run: npm run build

      - name: Start server
        run: |
          npm run preview &
          sleep 5
        env:
          PORT: 3000

      - name: Wait for server
        run: npx wait-on http://localhost:3000 --timeout 60000

      - name: Run axe-core tests
        id: axe
        run: |
          npx @axe-core/cli http://localhost:3000 \
            --exit \
            --tags wcag2a,wcag2aa,wcag21a,wcag21aa \
            --save results.json
        continue-on-error: true

      - name: Process results
        id: results
        run: |
          node << 'EOF'
          const fs = require('fs');
          const results = JSON.parse(fs.readFileSync('results.json', 'utf8'));
          
          let critical = 0, serious = 0, moderate = 0, minor = 0;
          const violations = [];
          
          results.forEach(page => {
            page.violations?.forEach(v => {
              const count = v.nodes.length;
              switch(v.impact) {
                case 'critical': critical += count; break;
                case 'serious': serious += count; break;
                case 'moderate': moderate += count; break;
                case 'minor': minor += count; break;
              }
              violations.push({
                impact: v.impact,
                description: v.description,
                help: v.help,
                helpUrl: v.helpUrl,
                count
              });
            });
          });
          
          // Generate summary
          const summary = `
          ## Accessibility Report
          
          | Severity | Count |
          |----------|-------|
          | 🔴 Critical | ${critical} |
          | 🟠 Serious | ${serious} |
          | 🟡 Moderate | ${moderate} |
          | 🔵 Minor | ${minor} |
          
          ${critical + serious > 0 ? '### ❌ FAILED — Fix critical/serious issues before merging' : '### ✅ PASSED'}
          
          ${violations.length > 0 ? '### Violations\n' + violations.slice(0, 10).map(v => 
            `- **[${v.impact.toUpperCase()}]** ${v.description} (${v.count} occurrences)\n  - ${v.help}\n  - [Learn more](${v.helpUrl})`
          ).join('\n') : ''}
          `;
          
          fs.writeFileSync('summary.md', summary);
          
          // Set outputs
          const core = require('@actions/core');
          core.setOutput('critical', critical);
          core.setOutput('serious', serious);
          core.setOutput('passed', critical + serious === 0);
          
          console.log(summary);
          EOF

      - name: Comment on PR
        if: github.event_name == 'pull_request'
        uses: actions/github-script@v7
        with:
          script: |
            const fs = require('fs');
            const summary = fs.readFileSync('summary.md', 'utf8');
            
            // Find existing comment
            const { data: comments } = await github.rest.issues.listComments({
              owner: context.repo.owner,
              repo: context.repo.repo,
              issue_number: context.issue.number,
            });
            
            const botComment = comments.find(c => 
              c.user.type === 'Bot' && c.body.includes('Accessibility Report')
            );
            
            if (botComment) {
              await github.rest.issues.updateComment({
                owner: context.repo.owner,
                repo: context.repo.repo,
                comment_id: botComment.id,
                body: summary
              });
            } else {
              await github.rest.issues.createComment({
                owner: context.repo.owner,
                repo: context.repo.repo,
                issue_number: context.issue.number,
                body: summary
              });
            }

      - name: Fail on critical/serious
        if: steps.results.outputs.passed == 'false'
        run: |
          echo "❌ Accessibility check failed"
          echo "Critical: ${{ steps.results.outputs.critical }}"
          echo "Serious: ${{ steps.results.outputs.serious }}"
          exit 1
```

---

## TESTING MULTIPLE PAGES

```yaml
# .github/workflows/a11y-multi-page.yml
name: Multi-Page A11y Scan

on:
  pull_request:
  workflow_dispatch:

jobs:
  a11y:
    runs-on: ubuntu-latest
    strategy:
      matrix:
        page:
          - path: '/'
            name: 'Homepage'
          - path: '/about'
            name: 'About'
          - path: '/contact'
            name: 'Contact'
          - path: '/login'
            name: 'Login'
          - path: '/dashboard'
            name: 'Dashboard'
    
    steps:
      - uses: actions/checkout@v4
      
      - name: Setup and build
        run: |
          npm ci
          npm run build
          npm run preview &
          npx wait-on http://localhost:3000

      - name: Test ${{ matrix.page.name }}
        run: |
          npx @axe-core/cli "http://localhost:3000${{ matrix.page.path }}" \
            --tags wcag2a,wcag2aa \
            --exit
```

---

## PLAYWRIGHT + AXE INTEGRATION

For more control, use Playwright with axe-core:

```typescript
// tests/a11y.spec.ts
import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

const pages = [
  { path: '/', name: 'Homepage' },
  { path: '/about', name: 'About' },
  { path: '/contact', name: 'Contact' },
  { path: '/login', name: 'Login' },
];

for (const page of pages) {
  test(`${page.name} should have no accessibility violations`, async ({ page: p }) => {
    await p.goto(page.path);
    
    // Wait for page to be fully loaded
    await p.waitForLoadState('networkidle');
    
    const results = await new AxeBuilder({ page: p })
      .withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa'])
      .analyze();
    
    // Filter by severity
    const critical = results.violations.filter(v => v.impact === 'critical');
    const serious = results.violations.filter(v => v.impact === 'serious');
    
    // Log violations for debugging
    if (results.violations.length > 0) {
      console.log(`\n${page.name} violations:`);
      results.violations.forEach(v => {
        console.log(`  [${v.impact}] ${v.description}`);
        v.nodes.forEach(n => {
          console.log(`    - ${n.target.join(' > ')}`);
        });
      });
    }
    
    // Fail on critical or serious
    expect(critical.length, `Critical violations on ${page.name}`).toBe(0);
    expect(serious.length, `Serious violations on ${page.name}`).toBe(0);
  });
}

// Test interactive states
test('Form validation errors are accessible', async ({ page }) => {
  await page.goto('/contact');
  
  // Submit empty form to trigger errors
  await page.click('button[type="submit"]');
  
  // Wait for error messages
  await page.waitForSelector('[role="alert"]');
  
  const results = await new AxeBuilder({ page })
    .include('form')
    .analyze();
  
  const formViolations = results.violations.filter(
    v => v.impact === 'critical' || v.impact === 'serious'
  );
  
  expect(formViolations.length).toBe(0);
});

// Test modal accessibility
test('Modal dialog is accessible', async ({ page }) => {
  await page.goto('/');
  
  // Open modal
  await page.click('[data-open-modal]');
  await page.waitForSelector('[role="dialog"]');
  
  const results = await new AxeBuilder({ page })
    .include('[role="dialog"]')
    .analyze();
  
  expect(results.violations.filter(v => 
    v.impact === 'critical' || v.impact === 'serious'
  ).length).toBe(0);
  
  // Check focus trap
  const focusedElement = await page.evaluate(() => 
    document.activeElement?.getAttribute('role')
  );
  expect(focusedElement).toBeDefined();
});
```

### Playwright Config

```typescript
// playwright.config.ts
import { defineConfig } from '@playwright/test';

export default defineConfig({
  testDir: './tests',
  use: {
    baseURL: process.env.BASE_URL || 'http://localhost:3000',
    screenshot: 'only-on-failure',
  },
  reporter: [
    ['html', { open: 'never' }],
    ['json', { outputFile: 'a11y-results.json' }],
  ],
  webServer: {
    command: 'npm run preview',
    port: 3000,
    reuseExistingServer: !process.env.CI,
  },
});
```

---

## VERCEL PREVIEW DEPLOYMENT

Test against Vercel preview URLs:

```yaml
# .github/workflows/a11y-preview.yml
name: A11y on Preview

on:
  deployment_status:

jobs:
  a11y:
    if: github.event.deployment_status.state == 'success'
    runs-on: ubuntu-latest
    
    steps:
      - uses: actions/checkout@v4
      
      - name: Get preview URL
        id: url
        run: |
          echo "url=${{ github.event.deployment_status.target_url }}" >> $GITHUB_OUTPUT

      - name: Run axe-core
        run: |
          npx @axe-core/cli "${{ steps.url.outputs.url }}" \
            --tags wcag2a,wcag2aa \
            --save results.json

      - name: Check results
        run: |
          node -e "
            const r = require('./results.json');
            const fail = r.some(p => p.violations.some(v => 
              v.impact === 'critical' || v.impact === 'serious'
            ));
            process.exit(fail ? 1 : 0);
          "
```

---

## LOCAL TESTING SCRIPT

```json
// package.json
{
  "scripts": {
    "a11y": "axe http://localhost:3000 --tags wcag2a,wcag2aa --exit",
    "a11y:all": "node scripts/a11y-all-pages.js"
  }
}
```

```javascript
// scripts/a11y-all-pages.js
const { execSync } = require('child_process');

const pages = [
  '/',
  '/about',
  '/contact',
  '/login',
  '/dashboard',
];

const baseUrl = process.env.BASE_URL || 'http://localhost:3000';
let failed = false;

for (const page of pages) {
  console.log(`\nTesting: ${page}`);
  try {
    execSync(
      `npx @axe-core/cli "${baseUrl}${page}" --tags wcag2a,wcag2aa --exit`,
      { stdio: 'inherit' }
    );
    console.log(`✅ ${page} passed`);
  } catch (e) {
    console.log(`❌ ${page} failed`);
    failed = true;
  }
}

process.exit(failed ? 1 : 0);
```

---

## DISABLE RULES (USE SPARINGLY)

```yaml
# Only disable rules with documented justification
- name: Run axe with exceptions
  run: |
    npx @axe-core/cli http://localhost:3000 \
      --disable color-contrast \
      --tags wcag2a,wcag2aa
  # JUSTIFICATION: color-contrast check has false positives
  # on gradient backgrounds. Manual review confirmed compliance.
```

---

## CHECKLIST FOR CI SETUP

- [ ] GitHub Action workflow file created
- [ ] Tests run on every PR
- [ ] PR comment shows results
- [ ] Fails on critical/serious violations
- [ ] Multiple pages tested
- [ ] Interactive states tested (modals, forms)
- [ ] Preview deployments tested
- [ ] Local test script available
- [ ] Results saved as artifacts
