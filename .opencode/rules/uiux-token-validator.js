#!/usr/bin/env node
/**
 * uiux-token-validator.js
 * 
 * CLI script that scans a codebase for design system violations:
 * - Hardcoded px, hex colors, radii
 * - Native browser UI elements that must be replaced
 * - transition: all (forbidden)
 * - !important (warning)
 * 
 * Usage:
 *   node uiux-token-validator.js ./src
 *   node uiux-token-validator.js ./src --fix (outputs suggested fixes)
 *   node uiux-token-validator.js ./src --json (JSON output)
 * 
 * Exit codes:
 *   0 = No violations
 *   1 = Violations found
 */

const fs = require('fs');
const path = require('path');

// Configuration
const CONFIG = {
  // Valid spacing values (8px grid)
  validSpacing: [0, 4, 8, 12, 16, 24, 32, 48, 64, 80, 96, 120, 160],
  
  // Valid breakpoints
  validBreakpoints: [640, 768, 1024, 1280, 1536],
  
  // File extensions to scan
  cssExtensions: ['.css', '.scss', '.less', '.sass'],
  htmlExtensions: ['.html', '.htm', '.jsx', '.tsx', '.vue', '.blade.php'],
  jsExtensions: ['.js', '.jsx', '.ts', '.tsx'],
  
  // Directories to skip
  skipDirs: ['node_modules', '.git', 'dist', 'build', 'vendor', '.next', '.nuxt'],
};

// Token mapping for suggestions
const TOKEN_MAP = {
  spacing: {
    4: 'var(--space-1)',
    8: 'var(--space-2)',
    12: 'var(--space-3)',
    16: 'var(--space-4)',
    24: 'var(--space-6)',
    32: 'var(--space-8)',
    48: 'var(--space-12)',
    64: 'var(--space-16)',
    80: 'var(--space-20)',
    96: 'var(--space-24)',
    120: 'var(--space-30)',
    160: 'var(--space-40)',
  },
  radius: {
    0: '0',
    2: 'var(--radius-sm)',
    4: 'var(--radius-sm)',
    6: 'var(--radius-sm)',
    8: 'var(--radius-md)',
    12: 'var(--radius-md)',
    16: 'var(--radius-lg)',
    20: 'var(--radius-lg)',
    9999: 'var(--radius-full)',
  },
  fontSize: {
    12: 'var(--text-xs)',
    14: 'var(--text-sm)',
    16: 'var(--text-base)',
    18: 'var(--text-lg)',
    20: 'var(--text-xl)',
    24: 'var(--text-2xl)',
    30: 'var(--text-3xl)',
    36: 'var(--text-4xl)',
    48: 'var(--text-5xl)',
  },
};

// Violation storage
const violations = [];

// Parse CLI arguments
const args = process.argv.slice(2);
const targetDir = args.find(a => !a.startsWith('--')) || './src';
const outputJson = args.includes('--json');
const showFixes = args.includes('--fix');

/**
 * Walk directory recursively
 */
function walkDir(dir, callback) {
  if (!fs.existsSync(dir)) {
    console.error(`Error: Directory "${dir}" does not exist`);
    process.exit(1);
  }
  
  const files = fs.readdirSync(dir);
  
  for (const file of files) {
    const filePath = path.join(dir, file);
    const stat = fs.statSync(filePath);
    
    if (stat.isDirectory()) {
      if (!CONFIG.skipDirs.includes(file)) {
        walkDir(filePath, callback);
      }
    } else {
      callback(filePath);
    }
  }
}

/**
 * Add a violation
 */
function addViolation(file, line, type, value, suggestion, severity = 'warning') {
  violations.push({ file, line, type, value, suggestion, severity });
}

/**
 * Get line number from index
 */
function getLineNumber(content, index) {
  return content.substring(0, index).split('\n').length;
}

/**
 * Scan CSS content
 */
function scanCSS(content, filePath) {
  const lines = content.split('\n');
  
  // Hardcoded spacing (not on 8px grid)
  const spacingRegex = /(?:margin|padding|gap|top|right|bottom|left)(?:-(?:top|right|bottom|left))?\s*:\s*(\d+)px/gi;
  let match;
  while ((match = spacingRegex.exec(content)) !== null) {
    const px = parseInt(match[1]);
    if (!CONFIG.validSpacing.includes(px)) {
      const suggestion = TOKEN_MAP.spacing[px] || `Use var(--space-*) token (closest: ${findClosest(px, CONFIG.validSpacing)}px)`;
      addViolation(filePath, getLineNumber(content, match.index), 'spacing', match[0], suggestion);
    }
  }
  
  // Hardcoded border-radius
  const radiusRegex = /border-radius\s*:\s*(\d+)px/gi;
  while ((match = radiusRegex.exec(content)) !== null) {
    const px = parseInt(match[1]);
    const suggestion = TOKEN_MAP.radius[px] || 'Use var(--radius-*) token';
    addViolation(filePath, getLineNumber(content, match.index), 'radius', match[0], suggestion);
  }
  
  // Hardcoded colors (hex)
  const hexRegex = /#(?:[0-9A-Fa-f]{3}){1,2}\b/g;
  while ((match = hexRegex.exec(content)) !== null) {
    // Skip if inside CSS variable declaration
    const beforeMatch = content.substring(Math.max(0, match.index - 50), match.index);
    if (beforeMatch.includes('--')) continue;
    
    addViolation(
      filePath, 
      getLineNumber(content, match.index), 
      'color', 
      match[0], 
      'Use semantic token: var(--accent), var(--text), var(--bg), etc.'
    );
  }
  
  // transition: all (forbidden)
  const transitionAllRegex = /transition\s*:\s*all/gi;
  while ((match = transitionAllRegex.exec(content)) !== null) {
    addViolation(
      filePath,
      getLineNumber(content, match.index),
      'transition',
      'transition: all',
      'Specify properties explicitly: transition: background var(--transition-base), color var(--transition-base)',
      'error'
    );
  }
  
  // !important (warning)
  const importantRegex = /!important/gi;
  while ((match = importantRegex.exec(content)) !== null) {
    addViolation(
      filePath,
      getLineNumber(content, match.index),
      'important',
      '!important',
      'Avoid !important. Use CSS Layers or increase specificity',
      'warning'
    );
  }
}

/**
 * Scan HTML/JSX content
 */
function scanHTML(content, filePath) {
  let match;
  
  // Native date input
  const dateRegex = /<input[^>]*type\s*=\s*["']date["'][^>]*>/gi;
  while ((match = dateRegex.exec(content)) !== null) {
    addViolation(
      filePath,
      getLineNumber(content, match.index),
      'native-ui',
      '<input type="date">',
      'Replace with Flatpickr date picker (see uiux-interactive.md)',
      'critical'
    );
  }
  
  // Native time input
  const timeRegex = /<input[^>]*type\s*=\s*["']time["'][^>]*>/gi;
  while ((match = timeRegex.exec(content)) !== null) {
    addViolation(
      filePath,
      getLineNumber(content, match.index),
      'native-ui',
      '<input type="time">',
      'Replace with Flatpickr time picker (see uiux-interactive.md)',
      'critical'
    );
  }
  
  // Native datetime-local input
  const datetimeRegex = /<input[^>]*type\s*=\s*["']datetime-local["'][^>]*>/gi;
  while ((match = datetimeRegex.exec(content)) !== null) {
    addViolation(
      filePath,
      getLineNumber(content, match.index),
      'native-ui',
      '<input type="datetime-local">',
      'Replace with Flatpickr datetime picker (see uiux-interactive.md)',
      'critical'
    );
  }
  
  // Native color input
  const colorRegex = /<input[^>]*type\s*=\s*["']color["'][^>]*>/gi;
  while ((match = colorRegex.exec(content)) !== null) {
    addViolation(
      filePath,
      getLineNumber(content, match.index),
      'native-ui',
      '<input type="color">',
      'Replace with custom color picker (see uiux-forms-extras.md)',
      'critical'
    );
  }
  
  // Native select (not custom-select)
  const selectRegex = /<select(?![^>]*class\s*=\s*["'][^"']*custom)[^>]*>/gi;
  while ((match = selectRegex.exec(content)) !== null) {
    addViolation(
      filePath,
      getLineNumber(content, match.index),
      'native-ui',
      '<select>',
      'Replace with custom select dropdown (see uiux-interactive.md)',
      'critical'
    );
  }
}

/**
 * Scan JavaScript content
 */
function scanJS(content, filePath) {
  let match;
  
  // window.alert()
  const alertRegex = /(?:window\.)?alert\s*\(/g;
  while ((match = alertRegex.exec(content)) !== null) {
    addViolation(
      filePath,
      getLineNumber(content, match.index),
      'native-ui',
      'alert()',
      'Replace with swal.fire() or custom toast (see uiux-components.md)',
      'critical'
    );
  }
  
  // window.confirm()
  const confirmRegex = /(?:window\.)?confirm\s*\(/g;
  while ((match = confirmRegex.exec(content)) !== null) {
    addViolation(
      filePath,
      getLineNumber(content, match.index),
      'native-ui',
      'confirm()',
      'Replace with swal.confirm() (see uiux-components.md)',
      'critical'
    );
  }
  
  // window.prompt()
  const promptRegex = /(?:window\.)?prompt\s*\(/g;
  while ((match = promptRegex.exec(content)) !== null) {
    addViolation(
      filePath,
      getLineNumber(content, match.index),
      'native-ui',
      'prompt()',
      'Replace with custom modal input (see uiux-components.md)',
      'critical'
    );
  }
}

/**
 * Find closest valid value
 */
function findClosest(value, validValues) {
  return validValues.reduce((prev, curr) => 
    Math.abs(curr - value) < Math.abs(prev - value) ? curr : prev
  );
}

/**
 * Scan a file
 */
function scanFile(filePath) {
  const ext = path.extname(filePath).toLowerCase();
  
  try {
    const content = fs.readFileSync(filePath, 'utf8');
    
    if (CONFIG.cssExtensions.includes(ext)) {
      scanCSS(content, filePath);
    }
    
    if (CONFIG.htmlExtensions.includes(ext)) {
      scanHTML(content, filePath);
      // Also scan inline styles in HTML
      const styleMatches = content.match(/<style[^>]*>([\s\S]*?)<\/style>/gi);
      if (styleMatches) {
        styleMatches.forEach(style => scanCSS(style, filePath));
      }
    }
    
    if (CONFIG.jsExtensions.includes(ext)) {
      scanJS(content, filePath);
    }
  } catch (err) {
    console.error(`Error reading ${filePath}: ${err.message}`);
  }
}

/**
 * Output results
 */
function outputResults() {
  if (outputJson) {
    console.log(JSON.stringify({
      summary: {
        total: violations.length,
        critical: violations.filter(v => v.severity === 'critical').length,
        errors: violations.filter(v => v.severity === 'error').length,
        warnings: violations.filter(v => v.severity === 'warning').length,
      },
      violations,
    }, null, 2));
    return;
  }
  
  console.log('\n╔════════════════════════════════════════════════════════════╗');
  console.log('║         UIUX TOKEN VALIDATOR — OpenCode 2026 v10          ║');
  console.log('╚════════════════════════════════════════════════════════════╝\n');
  
  const critical = violations.filter(v => v.severity === 'critical');
  const errors = violations.filter(v => v.severity === 'error');
  const warnings = violations.filter(v => v.severity === 'warning');
  
  console.log(`📊 Summary:`);
  console.log(`   Total violations: ${violations.length}`);
  console.log(`   🔴 Critical: ${critical.length}`);
  console.log(`   🟠 Errors: ${errors.length}`);
  console.log(`   🟡 Warnings: ${warnings.length}`);
  console.log('');
  
  if (violations.length === 0) {
    console.log('✅ No violations found! Your codebase follows the design system.');
    return;
  }
  
  // Group by file
  const byFile = violations.reduce((acc, v) => {
    (acc[v.file] = acc[v.file] || []).push(v);
    return acc;
  }, {});
  
  for (const [file, fileViolations] of Object.entries(byFile)) {
    console.log(`\n📁 ${file}`);
    
    for (const v of fileViolations) {
      const icon = v.severity === 'critical' ? '🔴' : v.severity === 'error' ? '🟠' : '🟡';
      console.log(`   ${icon} Line ${v.line}: ${v.type}`);
      console.log(`      Found: ${v.value}`);
      if (showFixes) {
        console.log(`      Fix: ${v.suggestion}`);
      }
    }
  }
  
  console.log('\n────────────────────────────────────────────────────────────');
  
  if (critical.length > 0) {
    console.log('\n⚠️  Critical violations found! These MUST be fixed before shipping.');
    console.log('   Native UI elements must be replaced with custom token-styled components.');
  }
}

// Run validation
console.log(`Scanning: ${path.resolve(targetDir)}\n`);
walkDir(targetDir, scanFile);
outputResults();

// Exit with code 1 if violations found
const hasCritical = violations.some(v => v.severity === 'critical');
const hasErrors = violations.some(v => v.severity === 'error');
process.exit(hasCritical || hasErrors ? 1 : 0);
