---
description: Analyse codebase for component usage patterns. Reports most/least used components, complexity scores, and optimization suggestions.
---

# UI/UX Component Analytics Skill

> Analyse your codebase to understand component usage patterns.
> Identify optimization opportunities and unused code.

---

## OVERVIEW

This skill scans your codebase to:
1. Count component imports and usages
2. Identify most/least used components
3. Calculate complexity scores
4. Detect unused components
5. Suggest optimizations

---

## USAGE

### Command Line

```bash
# Run the analyzer
node uiux-component-analytics.js ./src

# Output to JSON
node uiux-component-analytics.js ./src --json > report.json

# Include complexity analysis
node uiux-component-analytics.js ./src --complexity
```

### AI Prompt

```
Analyze the component usage in this codebase. Tell me:
1. Which components are used most frequently?
2. Are there any unused components I can remove?
3. Which components are overly complex?
4. What optimization opportunities exist?
```

---

## ANALYZER SCRIPT

```javascript
#!/usr/bin/env node

/**
 * Component Analytics Analyzer
 * Scans codebase for React/Vue/Svelte component usage patterns
 */

const fs = require('fs');
const path = require('path');

const CONFIG = {
  extensions: ['.jsx', '.tsx', '.vue', '.svelte', '.js', '.ts'],
  skipDirs: ['node_modules', '.git', 'dist', 'build', '.next'],
  componentPatterns: {
    // React/JSX components (PascalCase)
    react: /<([A-Z][a-zA-Z0-9]+)(?:\s|\/|>)/g,
    // Import statements
    imports: /import\s+(?:{[^}]+}|\w+)\s+from\s+['"]([^'"]+)['"]/g,
    // React.lazy imports
    lazy: /React\.lazy\s*\(\s*\(\)\s*=>\s*import\s*\(['"]([^'"]+)['"]\)\s*\)/g,
  }
};

// Component registry
const components = new Map();
const imports = new Map();
const fileComplexity = new Map();

function walkDir(dir, callback) {
  if (!fs.existsSync(dir)) return;
  
  const files = fs.readdirSync(dir);
  
  for (const file of files) {
    const filePath = path.join(dir, file);
    const stat = fs.statSync(filePath);
    
    if (stat.isDirectory()) {
      if (!CONFIG.skipDirs.includes(file)) {
        walkDir(filePath, callback);
      }
    } else {
      const ext = path.extname(file);
      if (CONFIG.extensions.includes(ext)) {
        callback(filePath);
      }
    }
  }
}

function analyzeFile(filePath) {
  const content = fs.readFileSync(filePath, 'utf8');
  
  // Track JSX component usage
  let match;
  while ((match = CONFIG.componentPatterns.react.exec(content)) !== null) {
    const componentName = match[1];
    
    // Skip HTML elements
    if (componentName === componentName.toLowerCase()) continue;
    
    if (!components.has(componentName)) {
      components.set(componentName, {
        name: componentName,
        usages: [],
        importedFrom: null,
        isExported: false
      });
    }
    
    components.get(componentName).usages.push({
      file: filePath,
      line: getLineNumber(content, match.index)
    });
  }
  
  // Track imports
  while ((match = CONFIG.componentPatterns.imports.exec(content)) !== null) {
    const importPath = match[1];
    const importStatement = match[0];
    
    // Extract component names from import
    const namedImports = importStatement.match(/{\s*([^}]+)\s*}/);
    if (namedImports) {
      namedImports[1].split(',').forEach(name => {
        const cleanName = name.trim().split(' as ')[0].trim();
        if (cleanName && /^[A-Z]/.test(cleanName)) {
          if (components.has(cleanName)) {
            components.get(cleanName).importedFrom = importPath;
          }
        }
      });
    }
    
    // Track import counts
    if (!imports.has(importPath)) {
      imports.set(importPath, { count: 0, files: [] });
    }
    imports.get(importPath).count++;
    imports.get(importPath).files.push(filePath);
  }
  
  // Calculate complexity
  const complexity = calculateComplexity(content, filePath);
  fileComplexity.set(filePath, complexity);
}

function getLineNumber(content, index) {
  return content.substring(0, index).split('\n').length;
}

function calculateComplexity(content, filePath) {
  let score = 0;
  const factors = [];
  
  // Lines of code
  const lines = content.split('\n').length;
  if (lines > 300) {
    score += 2;
    factors.push(`Large file (${lines} lines)`);
  } else if (lines > 150) {
    score += 1;
    factors.push(`Medium file (${lines} lines)`);
  }
  
  // Number of hooks (React)
  const hooks = (content.match(/use[A-Z]\w+/g) || []).length;
  if (hooks > 10) {
    score += 3;
    factors.push(`Many hooks (${hooks})`);
  } else if (hooks > 5) {
    score += 1;
    factors.push(`Several hooks (${hooks})`);
  }
  
  // Number of state variables
  const stateCount = (content.match(/useState/g) || []).length;
  if (stateCount > 7) {
    score += 2;
    factors.push(`Many state variables (${stateCount})`);
  }
  
  // Nested ternaries
  const nestedTernaries = (content.match(/\?.*\?.*:/g) || []).length;
  if (nestedTernaries > 0) {
    score += nestedTernaries;
    factors.push(`Nested ternaries (${nestedTernaries})`);
  }
  
  // Inline styles (anti-pattern)
  const inlineStyles = (content.match(/style=\{\{/g) || []).length;
  if (inlineStyles > 5) {
    score += 1;
    factors.push(`Many inline styles (${inlineStyles})`);
  }
  
  // Props spreading (potential issue)
  const propsSpreading = (content.match(/\{\.\.\.props\}/g) || []).length;
  if (propsSpreading > 0) {
    factors.push(`Props spreading (${propsSpreading})`);
  }
  
  return {
    score,
    factors,
    lines
  };
}

function generateReport(targetDir, outputJson = false) {
  // Sort by usage count
  const sortedComponents = Array.from(components.entries())
    .map(([name, data]) => ({
      name,
      usageCount: data.usages.length,
      importedFrom: data.importedFrom,
      usedIn: [...new Set(data.usages.map(u => u.file))]
    }))
    .sort((a, b) => b.usageCount - a.usageCount);
  
  // High complexity files
  const complexFiles = Array.from(fileComplexity.entries())
    .map(([file, data]) => ({
      file: path.relative(targetDir, file),
      ...data
    }))
    .filter(f => f.score >= 3)
    .sort((a, b) => b.score - a.score);
  
  // Unused components (0 usages found)
  const potentiallyUnused = sortedComponents.filter(c => c.usageCount === 1);
  
  // Most imported packages
  const topImports = Array.from(imports.entries())
    .map(([pkg, data]) => ({ package: pkg, ...data }))
    .sort((a, b) => b.count - a.count)
    .slice(0, 20);
  
  const report = {
    summary: {
      totalComponents: components.size,
      totalFiles: fileComplexity.size,
      highComplexityFiles: complexFiles.length,
      potentiallyUnusedComponents: potentiallyUnused.length
    },
    mostUsedComponents: sortedComponents.slice(0, 15),
    leastUsedComponents: sortedComponents.slice(-10).reverse(),
    potentiallyUnused,
    highComplexityFiles: complexFiles.slice(0, 10),
    topImports: topImports.slice(0, 10),
    optimizationSuggestions: generateSuggestions(sortedComponents, complexFiles)
  };
  
  if (outputJson) {
    console.log(JSON.stringify(report, null, 2));
  } else {
    printReport(report);
  }
  
  return report;
}

function generateSuggestions(components, complexFiles) {
  const suggestions = [];
  
  // Split large components
  complexFiles.forEach(f => {
    if (f.lines > 300) {
      suggestions.push({
        type: 'split-component',
        file: f.file,
        reason: `File has ${f.lines} lines. Consider splitting into smaller components.`,
        priority: 'medium'
      });
    }
  });
  
  // Consolidate rarely used components
  const rareComponents = components.filter(c => c.usageCount === 1 && c.usedIn.length === 1);
  if (rareComponents.length > 5) {
    suggestions.push({
      type: 'consolidate',
      reason: `${rareComponents.length} components are only used once. Consider inlining or consolidating.`,
      components: rareComponents.map(c => c.name).slice(0, 10),
      priority: 'low'
    });
  }
  
  // Highly used components should be optimized
  const highlyUsed = components.filter(c => c.usageCount > 50);
  highlyUsed.forEach(c => {
    suggestions.push({
      type: 'optimize-frequent',
      component: c.name,
      reason: `Used ${c.usageCount} times. Ensure it's memoized (React.memo) and optimized.`,
      priority: 'high'
    });
  });
  
  return suggestions;
}

function printReport(report) {
  console.log('\n╔════════════════════════════════════════════════════════════╗');
  console.log('║           COMPONENT ANALYTICS REPORT                       ║');
  console.log('╚════════════════════════════════════════════════════════════╝\n');
  
  console.log('📊 Summary');
  console.log('─'.repeat(50));
  console.log(`   Total components found: ${report.summary.totalComponents}`);
  console.log(`   Files analyzed: ${report.summary.totalFiles}`);
  console.log(`   High complexity files: ${report.summary.highComplexityFiles}`);
  console.log(`   Potentially unused: ${report.summary.potentiallyUnusedComponents}`);
  
  console.log('\n🔥 Most Used Components');
  console.log('─'.repeat(50));
  report.mostUsedComponents.slice(0, 10).forEach((c, i) => {
    console.log(`   ${i + 1}. ${c.name} (${c.usageCount} usages)`);
  });
  
  console.log('\n❄️  Least Used Components');
  console.log('─'.repeat(50));
  report.leastUsedComponents.slice(0, 5).forEach((c, i) => {
    console.log(`   ${i + 1}. ${c.name} (${c.usageCount} usages)`);
  });
  
  if (report.highComplexityFiles.length > 0) {
    console.log('\n⚠️  High Complexity Files');
    console.log('─'.repeat(50));
    report.highComplexityFiles.slice(0, 5).forEach(f => {
      console.log(`   ${f.file}`);
      console.log(`      Score: ${f.score}, Lines: ${f.lines}`);
      console.log(`      Factors: ${f.factors.join(', ')}`);
    });
  }
  
  if (report.optimizationSuggestions.length > 0) {
    console.log('\n💡 Optimization Suggestions');
    console.log('─'.repeat(50));
    report.optimizationSuggestions.forEach((s, i) => {
      const icon = s.priority === 'high' ? '🔴' : s.priority === 'medium' ? '🟡' : '🟢';
      console.log(`   ${icon} ${s.reason}`);
    });
  }
  
  console.log('\n');
}

// Main execution
const args = process.argv.slice(2);
const targetDir = args.find(a => !a.startsWith('--')) || './src';
const outputJson = args.includes('--json');

console.log(`Analyzing: ${path.resolve(targetDir)}\n`);
walkDir(targetDir, analyzeFile);
generateReport(targetDir, outputJson);
```

---

## OUTPUT EXAMPLE

```
╔════════════════════════════════════════════════════════════╗
║           COMPONENT ANALYTICS REPORT                       ║
╚════════════════════════════════════════════════════════════╝

📊 Summary
──────────────────────────────────────────────────
   Total components found: 156
   Files analyzed: 89
   High complexity files: 7
   Potentially unused: 12

🔥 Most Used Components
──────────────────────────────────────────────────
   1. Button (234 usages)
   2. Icon (189 usages)
   3. Card (78 usages)
   4. Modal (45 usages)
   5. Input (42 usages)

❄️  Least Used Components
──────────────────────────────────────────────────
   1. DeprecatedTooltip (1 usage)
   2. LegacyHeader (1 usage)
   3. OldDatePicker (2 usages)

⚠️  High Complexity Files
──────────────────────────────────────────────────
   src/components/Dashboard.tsx
      Score: 7, Lines: 485
      Factors: Large file, Many hooks (12), Many state variables (8)

💡 Optimization Suggestions
──────────────────────────────────────────────────
   🔴 Button used 234 times. Ensure it's memoized (React.memo) and optimized.
   🟡 Dashboard.tsx has 485 lines. Consider splitting into smaller components.
   🟢 12 components are only used once. Consider inlining or consolidating.
```

---

## INTEGRATION WITH CI

### GitHub Actions

```yaml
name: Component Analytics

on:
  pull_request:
    paths:
      - 'src/**/*.tsx'
      - 'src/**/*.jsx'

jobs:
  analyze:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      
      - name: Setup Node
        uses: actions/setup-node@v4
        with:
          node-version: '20'
      
      - name: Run Component Analytics
        run: |
          node .opencode/rules/uiux-component-analytics.js ./src --json > analytics.json
      
      - name: Check for high complexity
        run: |
          HIGH_COMPLEXITY=$(jq '.summary.highComplexityFiles' analytics.json)
          if [ "$HIGH_COMPLEXITY" -gt 10 ]; then
            echo "⚠️ Warning: $HIGH_COMPLEXITY high complexity files found"
            exit 1
          fi
      
      - name: Upload Report
        uses: actions/upload-artifact@v4
        with:
          name: component-analytics
          path: analytics.json
```

---

## METRICS TO TRACK

| Metric | Good | Warning | Action |
|--------|------|---------|--------|
| Component size | <150 lines | 150-300 | Split if >300 |
| State variables | <5 | 5-8 | Consider reducer >8 |
| Hook count | <7 | 7-10 | Extract custom hook >10 |
| Usage count | 3-50 | 1-2 or >100 | Review rarely/highly used |
| Inline styles | 0 | 1-5 | Extract to CSS >5 |

---

## RECOMMENDATIONS BY FINDING

### High Usage Components (>50 uses)

```tsx
// Ensure memoization
export const Button = React.memo(function Button({ 
  children, 
  onClick 
}: ButtonProps) {
  // ...
});

// Use stable callbacks
const handleClick = useCallback(() => {
  // ...
}, [dependencies]);
```

### Large Files (>300 lines)

Split into:
1. Container (logic) and Presentational (UI) components
2. Extract custom hooks for reusable logic
3. Create sub-components for sections

### Unused Components

1. Verify no dynamic imports
2. Check if exported for external use
3. Consider removal or deprecation

### High Complexity

1. Extract state to custom hooks
2. Use reducer for complex state
3. Break into smaller components
