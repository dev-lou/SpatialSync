---
description: Lighthouse CI with budget assertions. GitHub Action that runs on PR, comments scores, and fails if budgets exceeded.
---

# Performance CI Skill

> Automated Lighthouse performance testing in CI/CD with budget enforcement.

---

## BUDGET THRESHOLDS

| Metric | Target | Fail Threshold |
|--------|--------|----------------|
| Performance | ≥ 90 | < 85 |
| Accessibility | ≥ 95 | < 90 |
| Best Practices | ≥ 90 | < 85 |
| SEO | ≥ 90 | < 85 |
| LCP | ≤ 2.5s | > 4.0s |
| CLS | ≤ 0.1 | > 0.25 |
| FID/INP | ≤ 100ms | > 300ms |

---

## LIGHTHOUSE CI CONFIGURATION

### `.lighthouserc.json`

```json
{
  "ci": {
    "collect": {
      "numberOfRuns": 3,
      "url": [
        "http://localhost:3000/",
        "http://localhost:3000/about",
        "http://localhost:3000/contact"
      ],
      "startServerCommand": "npm run preview",
      "startServerReadyPattern": "ready",
      "startServerReadyTimeout": 30000
    },
    "assert": {
      "preset": "lighthouse:recommended",
      "assertions": {
        "categories:performance": ["error", { "minScore": 0.9 }],
        "categories:accessibility": ["error", { "minScore": 0.95 }],
        "categories:best-practices": ["error", { "minScore": 0.9 }],
        "categories:seo": ["error", { "minScore": 0.9 }],
        
        "largest-contentful-paint": ["error", { "maxNumericValue": 2500 }],
        "cumulative-layout-shift": ["error", { "maxNumericValue": 0.1 }],
        "total-blocking-time": ["error", { "maxNumericValue": 300 }],
        "first-contentful-paint": ["warn", { "maxNumericValue": 1800 }],
        "interactive": ["warn", { "maxNumericValue": 3900 }],
        "speed-index": ["warn", { "maxNumericValue": 3400 }],
        
        "uses-responsive-images": "warn",
        "uses-optimized-images": "warn",
        "uses-webp-images": "warn",
        "render-blocking-resources": "warn",
        "unused-css-rules": "warn",
        "unused-javascript": "warn"
      }
    },
    "upload": {
      "target": "temporary-public-storage"
    }
  }
}
```

### Alternative: Per-Environment Config

```json
{
  "ci": {
    "collect": {
      "numberOfRuns": 3,
      "settings": {
        "preset": "desktop",
        "throttling": {
          "rttMs": 40,
          "throughputKbps": 10240,
          "cpuSlowdownMultiplier": 1
        }
      }
    },
    "assert": {
      "assertions": {
        "categories:performance": ["error", { "minScore": 0.9 }],
        "categories:accessibility": ["error", { "minScore": 0.95 }]
      }
    }
  }
}
```

---

## GITHUB ACTIONS WORKFLOW

### Basic Workflow

```yaml
# .github/workflows/lighthouse.yml
name: Lighthouse CI

on:
  pull_request:
    branches: [main, develop]
  push:
    branches: [main]

jobs:
  lighthouse:
    runs-on: ubuntu-latest
    name: Lighthouse Performance Audit
    
    steps:
      - name: Checkout
        uses: actions/checkout@v4

      - name: Setup Node.js
        uses: actions/setup-node@v4
        with:
          node-version: '20'
          cache: 'npm'

      - name: Install dependencies
        run: npm ci

      - name: Build
        run: npm run build

      - name: Run Lighthouse CI
        id: lhci
        uses: treosh/lighthouse-ci-action@v11
        with:
          configPath: './lighthouserc.json'
          uploadArtifacts: true
          temporaryPublicStorage: true

      - name: Format results
        id: format
        run: |
          node << 'EOF'
          const fs = require('fs');
          
          // Read manifest
          const manifest = JSON.parse(
            fs.readFileSync('.lighthouseci/manifest.json', 'utf8')
          );
          
          let comment = '## 🚦 Lighthouse Report\n\n';
          
          manifest.forEach((entry, index) => {
            const summary = entry.summary;
            const url = new URL(entry.url).pathname;
            
            comment += `### ${url}\n\n`;
            comment += '| Category | Score |\n';
            comment += '|----------|-------|\n';
            comment += `| Performance | ${getEmoji(summary.performance)} ${Math.round(summary.performance * 100)} |\n`;
            comment += `| Accessibility | ${getEmoji(summary.accessibility)} ${Math.round(summary.accessibility * 100)} |\n`;
            comment += `| Best Practices | ${getEmoji(summary['best-practices'])} ${Math.round(summary['best-practices'] * 100)} |\n`;
            comment += `| SEO | ${getEmoji(summary.seo)} ${Math.round(summary.seo * 100)} |\n\n`;
            
            comment += `[Full Report](${entry.htmlPath})\n\n`;
          });
          
          function getEmoji(score) {
            if (score >= 0.9) return '🟢';
            if (score >= 0.5) return '🟡';
            return '🔴';
          }
          
          fs.writeFileSync('lhci-comment.md', comment);
          console.log(comment);
          EOF

      - name: Comment on PR
        if: github.event_name == 'pull_request'
        uses: actions/github-script@v7
        with:
          script: |
            const fs = require('fs');
            const comment = fs.readFileSync('lhci-comment.md', 'utf8');
            
            const { data: comments } = await github.rest.issues.listComments({
              owner: context.repo.owner,
              repo: context.repo.repo,
              issue_number: context.issue.number,
            });
            
            const botComment = comments.find(c => 
              c.user.type === 'Bot' && c.body.includes('Lighthouse Report')
            );
            
            if (botComment) {
              await github.rest.issues.updateComment({
                owner: context.repo.owner,
                repo: context.repo.repo,
                comment_id: botComment.id,
                body: comment
              });
            } else {
              await github.rest.issues.createComment({
                owner: context.repo.owner,
                repo: context.repo.repo,
                issue_number: context.issue.number,
                body: comment
              });
            }

      - name: Check budgets
        run: |
          # Fail if any assertion failed
          if [ -f ".lighthouseci/assertion-results.json" ]; then
            failures=$(cat .lighthouseci/assertion-results.json | jq '[.[] | select(.level == "error")] | length')
            if [ "$failures" -gt 0 ]; then
              echo "❌ Lighthouse budget exceeded!"
              cat .lighthouseci/assertion-results.json | jq '.[] | select(.level == "error")'
              exit 1
            fi
          fi
```

### With Preview Deployment (Vercel)

```yaml
# .github/workflows/lighthouse-preview.yml
name: Lighthouse on Preview

on:
  deployment_status:

jobs:
  lighthouse:
    if: github.event.deployment_status.state == 'success'
    runs-on: ubuntu-latest
    
    steps:
      - uses: actions/checkout@v4

      - name: Run Lighthouse
        uses: treosh/lighthouse-ci-action@v11
        with:
          urls: |
            ${{ github.event.deployment_status.target_url }}
            ${{ github.event.deployment_status.target_url }}/about
          budgetPath: './budget.json'
          uploadArtifacts: true

      - name: Comment results
        if: always()
        uses: actions/github-script@v7
        with:
          script: |
            const results = ${{ steps.lighthouse.outputs.manifest }};
            // ... format and post comment
```

---

## BUDGET FILE (Alternative Format)

### `budget.json`

```json
[
  {
    "path": "/*",
    "resourceSizes": [
      { "resourceType": "document", "budget": 50 },
      { "resourceType": "script", "budget": 300 },
      { "resourceType": "stylesheet", "budget": 100 },
      { "resourceType": "image", "budget": 500 },
      { "resourceType": "font", "budget": 100 },
      { "resourceType": "total", "budget": 1000 }
    ],
    "resourceCounts": [
      { "resourceType": "script", "budget": 20 },
      { "resourceType": "stylesheet", "budget": 5 }
    ],
    "timings": [
      { "metric": "largest-contentful-paint", "budget": 2500 },
      { "metric": "cumulative-layout-shift", "budget": 0.1 },
      { "metric": "total-blocking-time", "budget": 300 },
      { "metric": "first-contentful-paint", "budget": 1800 }
    ]
  }
]
```

---

## LOCAL TESTING

### Package.json Scripts

```json
{
  "scripts": {
    "lighthouse": "lhci autorun",
    "lighthouse:collect": "lhci collect --url=http://localhost:3000",
    "lighthouse:assert": "lhci assert",
    "lighthouse:upload": "lhci upload --target=temporary-public-storage"
  }
}
```

### CLI Installation

```bash
npm install -g @lhci/cli

# Run locally
npm run build && npm run preview &
lhci autorun
```

---

## CUSTOM ASSERTIONS

### Extended Configuration

```json
{
  "ci": {
    "assert": {
      "assertions": {
        "categories:performance": ["error", { "minScore": 0.9 }],
        
        "resource-summary:script:size": ["error", { "maxNumericValue": 300000 }],
        "resource-summary:stylesheet:size": ["error", { "maxNumericValue": 100000 }],
        "resource-summary:image:size": ["warn", { "maxNumericValue": 500000 }],
        "resource-summary:font:size": ["warn", { "maxNumericValue": 100000 }],
        "resource-summary:total:size": ["error", { "maxNumericValue": 1000000 }],
        
        "dom-size": ["warn", { "maxNumericValue": 1500 }],
        
        "uses-long-cache-ttl": "warn",
        "uses-text-compression": "error",
        "uses-rel-preconnect": "warn",
        
        "no-unload-listeners": "error",
        "no-document-write": "error",
        
        "image-aspect-ratio": "error",
        "image-size-responsive": "warn"
      }
    }
  }
}
```

---

## INTEGRATION WITH SLACK/DISCORD

```yaml
- name: Notify Slack on failure
  if: failure()
  uses: slackapi/slack-github-action@v1
  with:
    payload: |
      {
        "text": "🔴 Lighthouse CI failed on ${{ github.repository }}",
        "blocks": [
          {
            "type": "section",
            "text": {
              "type": "mrkdwn",
              "text": "*Lighthouse CI Failed*\n\nPR: <${{ github.event.pull_request.html_url }}|#${{ github.event.pull_request.number }}>\nBranch: `${{ github.head_ref }}`"
            }
          }
        ]
      }
  env:
    SLACK_WEBHOOK_URL: ${{ secrets.SLACK_WEBHOOK }}
```

---

## TRACKING OVER TIME

### Upload to Lighthouse CI Server

```json
{
  "ci": {
    "upload": {
      "target": "lhci",
      "serverBaseUrl": "https://your-lhci-server.com",
      "token": "$LHCI_TOKEN"
    }
  }
}
```

### Self-Hosted Server Setup

```bash
# Deploy LHCI server
npm install -g @lhci/server
lhci server --storage.storageMethod=sql --storage.sqlDialect=postgres --storage.sqlConnectionUrl="$DATABASE_URL"
```

---

## CHECKLIST

- [ ] `.lighthouserc.json` configured with budgets
- [ ] GitHub Action runs on PRs
- [ ] PR comment shows scores
- [ ] Fails on budget violation
- [ ] Multiple pages tested
- [ ] Works with preview deployments
- [ ] Local testing script available
- [ ] Historical data tracked (optional)
