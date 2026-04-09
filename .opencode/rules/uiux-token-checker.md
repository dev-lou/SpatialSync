---
description: Pre-commit hook or CI step that scans code for undefined CSS variables and hardcoded values that should be tokens.
---

# Token Checker Skill

> Validate design token usage in generated code.

---

## WHAT IT CHECKS

| Violation | Example | Fix |
|-----------|---------|-----|
| Undefined variable | `var(--color-unknown)` | Use defined token or add to tokens |
| Hardcoded color | `color: #3b82f6` | Use `var(--accent)` |
| Hardcoded spacing | `margin: 16px` | Use `var(--space-4)` |
| Hardcoded radius | `border-radius: 8px` | Use `var(--radius-md)` |
| Hardcoded font | `font-size: 14px` | Use `var(--text-sm)` |

---

## CLI SCRIPT

### `scripts/check-tokens.js`

```javascript
#!/usr/bin/env node

/**
 * Token Checker - Validates design token usage
 * 
 * Usage:
 *   node scripts/check-tokens.js [path]
 *   node scripts/check-tokens.js src/
 *   node scripts/check-tokens.js --fix
 */

const fs = require('fs');
const path = require('path');
const glob = require('glob');

// Configuration
const CONFIG = {
  // Files to check
  patterns: ['**/*.css', '**/*.scss', '**/*.tsx', '**/*.jsx', '**/*.vue'],
  ignore: ['**/node_modules/**', '**/dist/**', '**/build/**', '**/*.min.css'],
  
  // Token file path (to extract defined tokens)
  tokenFile: './src/tokens.css',
  
  // Patterns for violations
  violations: {
    hardcodedColor: {
      // Matches hex colors like #fff, #ffffff, #rrggbbaa
      pattern: /#([0-9A-Fa-f]{3,8})\b/g,
      message: 'Hardcoded color found. Use a token like var(--accent) instead.',
      severity: 'error',
      // Exceptions (e.g., in token definitions)
      allowIn: ['tokens.css', 'tokens.scss', 'variables.css'],
    },
    hardcodedPx: {
      // Matches px values (but not 0px)
      pattern: /(?<![a-z-])([1-9]\d*|0\.\d+)px\b/gi,
      message: 'Hardcoded pixel value. Use a spacing/size token.',
      severity: 'warning',
      allowIn: ['tokens.css'],
      // Exceptions for common non-token values
      allowValues: ['1px', '2px'], // Often used for borders
    },
    hardcodedRem: {
      pattern: /(?<![a-z-])\d+(\.\d+)?rem\b/gi,
      message: 'Consider using a token for rem values.',
      severity: 'warning',
      allowIn: ['tokens.css'],
    },
  },
};

// Extract defined CSS variables from token file
function extractDefinedTokens(tokenFile) {
  if (!fs.existsSync(tokenFile)) {
    console.warn(`⚠️ Token file not found: ${tokenFile}`);
    return new Set();
  }
  
  const content = fs.readFileSync(tokenFile, 'utf8');
  const tokens = new Set();
  
  // Match CSS variable definitions
  const varPattern = /--([\w-]+)\s*:/g;
  let match;
  
  while ((match = varPattern.exec(content)) !== null) {
    tokens.add(`--${match[1]}`);
  }
  
  console.log(`📦 Found ${tokens.size} defined tokens`);
  return tokens;
}

// Check for undefined CSS variables
function checkUndefinedVariables(content, definedTokens) {
  const violations = [];
  const varUsagePattern = /var\(\s*--([\w-]+)/g;
  let match;
  
  while ((match = varUsagePattern.exec(content)) !== null) {
    const varName = `--${match[1]}`;
    if (!definedTokens.has(varName)) {
      violations.push({
        type: 'undefined-variable',
        value: varName,
        index: match.index,
        message: `Undefined CSS variable: var(${varName})`,
        severity: 'error',
      });
    }
  }
  
  return violations;
}

// Check for hardcoded values
function checkHardcodedValues(content, filename) {
  const violations = [];
  
  for (const [type, config] of Object.entries(CONFIG.violations)) {
    // Skip if file is in allowIn list
    if (config.allowIn?.some(allowed => filename.includes(allowed))) {
      continue;
    }
    
    let match;
    while ((match = config.pattern.exec(content)) !== null) {
      const value = match[0];
      
      // Skip allowed values
      if (config.allowValues?.includes(value)) {
        continue;
      }
      
      violations.push({
        type,
        value,
        index: match.index,
        message: config.message,
        severity: config.severity,
      });
    }
  }
  
  return violations;
}

// Get line number from index
function getLineNumber(content, index) {
  const lines = content.substring(0, index).split('\n');
  return lines.length;
}

// Format violation for output
function formatViolation(filename, content, violation) {
  const line = getLineNumber(content, violation.index);
  const icon = violation.severity === 'error' ? '❌' : '⚠️';
  
  return `${icon} ${filename}:${line} - ${violation.message}\n   Found: ${violation.value}`;
}

// Main check function
function checkFile(filepath, definedTokens) {
  const content = fs.readFileSync(filepath, 'utf8');
  const violations = [];
  
  // Check for undefined variables
  violations.push(...checkUndefinedVariables(content, definedTokens));
  
  // Check for hardcoded values
  violations.push(...checkHardcodedValues(content, filepath));
  
  return violations;
}

// Main execution
function main() {
  const args = process.argv.slice(2);
  const targetPath = args.find(arg => !arg.startsWith('-')) || '.';
  
  console.log('🔍 Token Checker\n');
  
  // Extract defined tokens
  const definedTokens = extractDefinedTokens(CONFIG.tokenFile);
  
  // Find files to check
  const files = [];
  for (const pattern of CONFIG.patterns) {
    const matches = glob.sync(path.join(targetPath, pattern), {
      ignore: CONFIG.ignore,
    });
    files.push(...matches);
  }
  
  console.log(`📁 Checking ${files.length} files...\n`);
  
  let totalViolations = 0;
  let errorCount = 0;
  let warningCount = 0;
  
  for (const file of files) {
    const violations = checkFile(file, definedTokens);
    
    if (violations.length > 0) {
      const content = fs.readFileSync(file, 'utf8');
      
      for (const violation of violations) {
        console.log(formatViolation(file, content, violation));
        
        if (violation.severity === 'error') {
          errorCount++;
        } else {
          warningCount++;
        }
      }
      
      totalViolations += violations.length;
    }
  }
  
  // Summary
  console.log('\n' + '─'.repeat(50));
  console.log(`\n📊 Summary:`);
  console.log(`   Files checked: ${files.length}`);
  console.log(`   Errors: ${errorCount}`);
  console.log(`   Warnings: ${warningCount}`);
  
  if (errorCount > 0) {
    console.log('\n❌ Token check failed!\n');
    process.exit(1);
  } else if (warningCount > 0) {
    console.log('\n⚠️ Token check passed with warnings.\n');
    process.exit(0);
  } else {
    console.log('\n✅ All tokens valid!\n');
    process.exit(0);
  }
}

main();
```

### `package.json` Scripts

```json
{
  "scripts": {
    "check:tokens": "node scripts/check-tokens.js src/",
    "check:tokens:all": "node scripts/check-tokens.js ."
  }
}
```

---

## PRE-COMMIT HOOK

### Using Husky

```bash
npm install -D husky lint-staged
npx husky init
```

### `.husky/pre-commit`

```bash
#!/bin/sh
. "$(dirname "$0")/_/husky.sh"

npx lint-staged
```

### `package.json`

```json
{
  "lint-staged": {
    "*.{css,scss,tsx,jsx,vue}": [
      "node scripts/check-tokens.js"
    ]
  }
}
```

---

## GITHUB ACTION

```yaml
# .github/workflows/token-check.yml
name: Token Validation

on:
  pull_request:
    paths:
      - '**.css'
      - '**.scss'
      - '**.tsx'
      - '**.jsx'
      - '**.vue'

jobs:
  check-tokens:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      
      - name: Setup Node
        uses: actions/setup-node@v4
        with:
          node-version: '20'

      - name: Install dependencies
        run: npm ci

      - name: Check tokens
        run: npm run check:tokens
```

---

## ESLINT PLUGIN

### `eslint-plugin-design-tokens/rules/no-hardcoded-colors.js`

```javascript
module.exports = {
  meta: {
    type: 'suggestion',
    docs: {
      description: 'Disallow hardcoded color values',
      category: 'Best Practices',
    },
    fixable: null,
    schema: [],
    messages: {
      hardcodedColor: 'Hardcoded color "{{value}}". Use a design token instead.',
    },
  },

  create(context) {
    const hexPattern = /#([0-9A-Fa-f]{3,8})\b/;
    const rgbPattern = /rgba?\([^)]+\)/;

    return {
      Literal(node) {
        if (typeof node.value === 'string') {
          if (hexPattern.test(node.value) || rgbPattern.test(node.value)) {
            context.report({
              node,
              messageId: 'hardcodedColor',
              data: { value: node.value },
            });
          }
        }
      },

      TemplateElement(node) {
        const value = node.value.raw;
        if (hexPattern.test(value) || rgbPattern.test(value)) {
          context.report({
            node,
            messageId: 'hardcodedColor',
            data: { value },
          });
        }
      },
    };
  },
};
```

### ESLint Config

```javascript
// .eslintrc.js
module.exports = {
  plugins: ['design-tokens'],
  rules: {
    'design-tokens/no-hardcoded-colors': 'error',
    'design-tokens/no-hardcoded-spacing': 'warn',
  },
};
```

---

## STYLELINT PLUGIN

### `stylelint.config.js`

```javascript
module.exports = {
  plugins: ['stylelint-declaration-strict-value'],
  rules: {
    'scale-unlimited/declaration-strict-value': [
      [
        '/color/',
        'fill',
        'stroke',
        'background',
        'border-color',
        'box-shadow',
      ],
      {
        ignoreValues: ['inherit', 'transparent', 'currentColor'],
        disableFix: true,
        message: 'Use a CSS variable for {{ property }}',
      },
    ],
    
    // Custom rule for spacing
    'declaration-property-value-allowed-list': {
      '/^margin/': ['/var\\(--space/', '0', 'auto'],
      '/^padding/': ['/var\\(--space/', '0'],
      '/^gap/': ['/var\\(--space/', '0'],
      'border-radius': ['/var\\(--radius/', '0', '50%'],
    },
  },
};
```

---

## SUGGESTED FIXES OUTPUT

```javascript
// Enhanced check-tokens.js with suggestions
const SUGGESTIONS = {
  // Color suggestions
  '#3b82f6': 'var(--accent)',
  '#ef4444': 'var(--error)',
  '#22c55e': 'var(--success)',
  '#ffffff': 'var(--bg)',
  '#000000': 'var(--text)',
  
  // Spacing suggestions
  '4px': 'var(--space-1)',
  '8px': 'var(--space-2)',
  '12px': 'var(--space-3)',
  '16px': 'var(--space-4)',
  '24px': 'var(--space-6)',
  '32px': 'var(--space-8)',
  
  // Radius suggestions
  '4px': 'var(--radius-sm)',
  '8px': 'var(--radius-md)',
  '12px': 'var(--radius-lg)',
  
  // Font size suggestions
  '12px': 'var(--text-xs)',
  '14px': 'var(--text-sm)',
  '16px': 'var(--text-base)',
  '18px': 'var(--text-lg)',
};

function getSuggestion(value) {
  return SUGGESTIONS[value.toLowerCase()] || null;
}

// In formatViolation:
const suggestion = getSuggestion(violation.value);
if (suggestion) {
  output += `\n   💡 Suggestion: ${suggestion}`;
}
```

---

## CHECKLIST

- [ ] CLI script created
- [ ] Pre-commit hook configured
- [ ] CI workflow added
- [ ] ESLint rules for JS/TSX
- [ ] Stylelint rules for CSS
- [ ] Token file path configured
- [ ] Suggestions for common values
- [ ] Exit code 1 on errors
