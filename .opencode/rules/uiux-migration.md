---
description: Skill to migrate existing CSS/JSX to design tokens. Includes regex patterns, mapping tables, native UI element detection, and diff output.
---

# UI/UX Migration Skill

> Convert legacy CSS to design tokens. Scan for hardcoded values and native UI elements
> that must be replaced with custom token-styled components.

---

## MIGRATION OVERVIEW

This skill helps convert existing codebases to the OpenCode UI/UX 2026 design system by:
1. Scanning for hardcoded values (px, hex colors, raw radii)
2. Detecting native browser UI elements that need replacement
3. Providing token mappings and automated fix suggestions
4. Generating diff output showing before/after

---

## TOKEN MAPPING TABLE

### Spacing (px → token)

| Hardcoded | Token | Usage |
|-----------|-------|-------|
| `4px` | `var(--space-1)` | Micro spacing |
| `8px` | `var(--space-2)` | Tight spacing |
| `12px` | `var(--space-3)` | Compact spacing |
| `16px` | `var(--space-4)` | Standard spacing |
| `24px` | `var(--space-6)` | Medium spacing |
| `32px` | `var(--space-8)` | Large spacing |
| `48px` | `var(--space-12)` | Section padding |
| `64px` | `var(--space-16)` | Large section |
| `80px` | `var(--space-20)` | Hero spacing |
| `96px` | `var(--space-24)` | Mega spacing |
| `120px` | `var(--space-30)` | XL section |
| `160px` | `var(--space-40)` | XXL section |

### Font Sizes (px/rem → token)

| Hardcoded | Token |
|-----------|-------|
| `12px` / `0.75rem` | `var(--text-xs)` |
| `14px` / `0.875rem` | `var(--text-sm)` |
| `16px` / `1rem` | `var(--text-base)` |
| `18px` / `1.125rem` | `var(--text-lg)` |
| `20px` / `1.25rem` | `var(--text-xl)` |
| `24px` / `1.5rem` | `var(--text-2xl)` |
| `30px` / `1.875rem` | `var(--text-3xl)` |
| `36px` / `2.25rem` | `var(--text-4xl)` |
| `48px` / `3rem` | `var(--text-5xl)` |

### Border Radius (px → token)

| Hardcoded | Token | Notes |
|-----------|-------|-------|
| `0px` | `0` | Sharp corners (Luxury) |
| `2px` | `var(--radius-sm)` | Minimal (Technical) |
| `4px` | `var(--radius-sm)` | Subtle |
| `6px` | `var(--radius-sm)` | Default Premium |
| `8px` | `var(--radius-md)` | Minimalist |
| `12px` | `var(--radius-md)` | Premium |
| `16px` | `var(--radius-lg)` | Soft |
| `20px` | `var(--radius-lg)` | Premium large |
| `9999px` | `var(--radius-full)` | Pill shape |

### Common Colors (hex → token)

| Pattern | Token |
|---------|-------|
| `#FFFFFF`, `white` | `var(--bg)` (light) |
| `#000000`, `black` | `var(--text)` (dark) |
| `#F7F8FA`, `#F5F5F5` | `var(--surface)` |
| `#0066FF`, `#3B82F6` | `var(--accent)` |
| `#6B7280`, `#666666` | `var(--muted)` |
| `#CF222E`, `#EF4444` | `var(--color-error)` |
| `#1A7F37`, `#22C55E` | `var(--color-success)` |

---

## REGEX PATTERNS FOR SCANNING

```javascript
// Spacing violations (hardcoded px not on 8px grid)
const spacingRegex = /(?:margin|padding|gap|top|right|bottom|left|width|height):\s*(\d+)px/gi;

// Font size violations
const fontSizeRegex = /font-size:\s*(\d+(?:\.\d+)?)(px|rem)/gi;

// Border radius violations
const radiusRegex = /border-radius:\s*(\d+)px/gi;

// Color violations (hex codes)
const hexColorRegex = /#([0-9A-Fa-f]{3}){1,2}\b/g;

// RGB/RGBA violations
const rgbRegex = /rgba?\s*\(\s*\d+\s*,\s*\d+\s*,\s*\d+/gi;

// Shadow violations
const shadowRegex = /box-shadow:\s*[^;]+(?:px|rgba)/gi;

// Transition "all" violations
const transitionAllRegex = /transition:\s*all/gi;

// !important violations
const importantRegex = /!important/gi;
```

---

## NATIVE UI ELEMENT DETECTION

**Rule**: These native elements MUST be replaced with custom token-styled components.

### Elements to Replace

| Native Element | Replacement | Rule File |
|----------------|-------------|-----------|
| `<input type="date">` | Flatpickr date picker | uiux-interactive.md |
| `<input type="time">` | Flatpickr time picker | uiux-interactive.md |
| `<input type="datetime-local">` | Flatpickr datetime | uiux-interactive.md |
| `<input type="color">` | Custom color picker | uiux-forms-extras.md |
| `<select>` | Custom select dropdown | uiux-interactive.md |
| `window.alert()` | SweetAlert2 alert | uiux-components.md |
| `window.confirm()` | SweetAlert2 confirm | uiux-components.md |
| `window.prompt()` | Custom modal input | uiux-components.md |

### Detection Regex

```javascript
// Native inputs that need custom replacements
const nativeDateRegex = /<input[^>]*type\s*=\s*["']date["'][^>]*>/gi;
const nativeTimeRegex = /<input[^>]*type\s*=\s*["']time["'][^>]*>/gi;
const nativeDatetimeRegex = /<input[^>]*type\s*=\s*["']datetime-local["'][^>]*>/gi;
const nativeColorRegex = /<input[^>]*type\s*=\s*["']color["'][^>]*>/gi;
const nativeSelectRegex = /<select[^>]*>[\s\S]*?<\/select>/gi;

// JavaScript alert/confirm/prompt
const alertRegex = /window\.alert\s*\(|alert\s*\(/g;
const confirmRegex = /window\.confirm\s*\(|confirm\s*\(/g;
const promptRegex = /window\.prompt\s*\(|prompt\s*\(/g;
```

---

## MIGRATION SCRIPT

```javascript
// migration-scan.js
// Run: node migration-scan.js ./src

const fs = require('fs');
const path = require('path');

const violations = [];

function scanFile(filePath) {
  const content = fs.readFileSync(filePath, 'utf8');
  const ext = path.extname(filePath);
  
  // CSS violations
  if (['.css', '.scss', '.less'].includes(ext)) {
    scanCSS(content, filePath);
  }
  
  // HTML/JSX violations
  if (['.html', '.jsx', '.tsx', '.vue'].includes(ext)) {
    scanHTML(content, filePath);
  }
  
  // JavaScript violations
  if (['.js', '.jsx', '.ts', '.tsx'].includes(ext)) {
    scanJS(content, filePath);
  }
}

function scanCSS(content, filePath) {
  // Check for hardcoded px values
  const spacingMatches = content.matchAll(/(?:margin|padding|gap):\s*(\d+)px/gi);
  for (const match of spacingMatches) {
    const px = parseInt(match[1]);
    if (![4, 8, 12, 16, 24, 32, 48, 64, 80, 96, 120, 160].includes(px)) {
      violations.push({
        file: filePath,
        type: 'spacing',
        value: match[0],
        suggestion: `Use var(--space-*) token`,
        severity: 'warning'
      });
    }
  }
  
  // Check for hardcoded colors
  const colorMatches = content.matchAll(/#[0-9A-Fa-f]{3,6}\b/g);
  for (const match of colorMatches) {
    violations.push({
      file: filePath,
      type: 'color',
      value: match[0],
      suggestion: 'Use semantic color token (var(--accent), var(--text), etc.)',
      severity: 'warning'
    });
  }
  
  // Check for transition: all
  if (/transition:\s*all/i.test(content)) {
    violations.push({
      file: filePath,
      type: 'transition',
      value: 'transition: all',
      suggestion: 'Specify properties explicitly (e.g., transition: background, color)',
      severity: 'error'
    });
  }
}

function scanHTML(content, filePath) {
  // Native date inputs
  if (/<input[^>]*type\s*=\s*["']date["']/i.test(content)) {
    violations.push({
      file: filePath,
      type: 'native-ui',
      value: '<input type="date">',
      suggestion: 'Replace with Flatpickr date picker (uiux-interactive.md)',
      severity: 'critical'
    });
  }
  
  // Native select
  if (/<select[^>]*>/i.test(content)) {
    violations.push({
      file: filePath,
      type: 'native-ui',
      value: '<select>',
      suggestion: 'Replace with custom select (uiux-interactive.md)',
      severity: 'critical'
    });
  }
  
  // Native color picker
  if (/<input[^>]*type\s*=\s*["']color["']/i.test(content)) {
    violations.push({
      file: filePath,
      type: 'native-ui',
      value: '<input type="color">',
      suggestion: 'Replace with custom color picker (uiux-forms-extras.md)',
      severity: 'critical'
    });
  }
}

function scanJS(content, filePath) {
  // window.alert
  if (/(?:window\.)?alert\s*\(/g.test(content)) {
    violations.push({
      file: filePath,
      type: 'native-ui',
      value: 'alert()',
      suggestion: 'Replace with swal.fire() or custom toast',
      severity: 'critical'
    });
  }
  
  // window.confirm
  if (/(?:window\.)?confirm\s*\(/g.test(content)) {
    violations.push({
      file: filePath,
      type: 'native-ui',
      value: 'confirm()',
      suggestion: 'Replace with swal.confirm()',
      severity: 'critical'
    });
  }
}

// Run scan
const targetDir = process.argv[2] || './src';
walkDir(targetDir, scanFile);

// Output report
console.log('\n📊 Migration Scan Report\n');
console.log(`Total violations: ${violations.length}`);
console.log(`Critical: ${violations.filter(v => v.severity === 'critical').length}`);
console.log(`Warnings: ${violations.filter(v => v.severity === 'warning').length}`);

violations.forEach(v => {
  const icon = v.severity === 'critical' ? '🔴' : '🟡';
  console.log(`\n${icon} ${v.file}`);
  console.log(`   Type: ${v.type}`);
  console.log(`   Found: ${v.value}`);
  console.log(`   Fix: ${v.suggestion}`);
});
```

---

## DIFF OUTPUT FORMAT

When generating fixes, output in unified diff format:

```diff
--- src/components/Button.css
+++ src/components/Button.css
@@ -5,7 +5,7 @@
 .btn {
-  padding: 12px 24px;
-  border-radius: 8px;
-  background: #0066FF;
+  padding: var(--space-3) var(--space-6);
+  border-radius: var(--radius-md);
+  background: var(--accent);
   transition: all 0.2s;
 }
```

---

## AUTO-FIX SUGGESTIONS

For each violation, the AI should suggest the exact fix:

### Spacing Fix
```css
/* Before */
padding: 16px 24px;
margin-bottom: 32px;

/* After */
padding: var(--space-4) var(--space-6);
margin-bottom: var(--space-8);
```

### Color Fix
```css
/* Before */
color: #0066FF;
background: rgba(0, 0, 0, 0.1);

/* After */
color: var(--accent);
background: var(--overlay);
```

### Native UI Fix
```html
<!-- Before -->
<input type="date" name="birthday">

<!-- After -->
<div class="datepicker-wrap field">
  <label class="field__label-static">Birthday</label>
  <div class="datepicker-input-wrap">
    <i data-lucide="calendar"></i>
    <input type="text" id="birthday" class="datepicker-input" readonly>
  </div>
</div>
<script>flatpickr('#birthday', { dateFormat: 'Y-m-d' });</script>
```
