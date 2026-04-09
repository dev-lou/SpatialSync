---
description: Auto-generate component documentation from source code using react-docgen, vuese, or vitepress. Outputs docs with props, examples, and token usage.
---

# Component Documentation Skill

> Generate documentation for components automatically from source code.

---

## CORE PRINCIPLE

**Rule**: Every component should have documentation generated from its source code. Documentation must include props, examples, and design token usage.

---

## REACT (REACT-DOCGEN + MARKDOWN)

### Setup

```bash
npm install -D react-docgen-typescript typescript
```

### Script

```typescript
// scripts/generate-docs.ts
import * as fs from 'fs';
import * as path from 'path';
import { parse } from 'react-docgen-typescript';

const COMPONENTS_DIR = './src/components';
const DOCS_DIR = './docs/components';

// Ensure docs directory exists
if (!fs.existsSync(DOCS_DIR)) {
  fs.mkdirSync(DOCS_DIR, { recursive: true });
}

// Parser options
const options = {
  savePropValueAsString: true,
  shouldExtractLiteralValuesFromEnum: true,
  shouldRemoveUndefinedFromOptional: true,
  propFilter: (prop: any) => {
    // Filter out HTML attributes
    if (prop.declarations?.length > 0) {
      return prop.declarations.some(
        (d: any) => !d.fileName.includes('node_modules')
      );
    }
    return true;
  },
};

// Find all component files
function findComponents(dir: string): string[] {
  const files: string[] = [];
  
  fs.readdirSync(dir).forEach(file => {
    const fullPath = path.join(dir, file);
    const stat = fs.statSync(fullPath);
    
    if (stat.isDirectory()) {
      files.push(...findComponents(fullPath));
    } else if (file.match(/\.(tsx|ts)$/) && !file.includes('.test.') && !file.includes('.stories.')) {
      files.push(fullPath);
    }
  });
  
  return files;
}

// Generate markdown for a component
function generateMarkdown(component: any): string {
  let md = `# ${component.displayName}\n\n`;
  
  if (component.description) {
    md += `${component.description}\n\n`;
  }
  
  // Props table
  const props = Object.entries(component.props || {});
  if (props.length > 0) {
    md += `## Props\n\n`;
    md += `| Prop | Type | Default | Required | Description |\n`;
    md += `|------|------|---------|----------|-------------|\n`;
    
    props.forEach(([name, prop]: [string, any]) => {
      const type = prop.type?.name || 'unknown';
      const defaultValue = prop.defaultValue?.value || '-';
      const required = prop.required ? '✅' : '❌';
      const description = prop.description || '-';
      
      md += `| \`${name}\` | \`${type}\` | \`${defaultValue}\` | ${required} | ${description} |\n`;
    });
    
    md += '\n';
  }
  
  // Design tokens section
  md += `## Design Tokens Used\n\n`;
  md += `> This component uses the following design tokens:\n\n`;
  md += `\`\`\`css\n`;
  md += `/* Spacing */\n`;
  md += `var(--space-2), var(--space-4)\n\n`;
  md += `/* Colors */\n`;
  md += `var(--accent), var(--text), var(--border)\n\n`;
  md += `/* Radius */\n`;
  md += `var(--radius-md)\n`;
  md += `\`\`\`\n\n`;
  
  // Usage example
  md += `## Usage\n\n`;
  md += `\`\`\`tsx\n`;
  md += `import { ${component.displayName} } from '@/components/${component.displayName}';\n\n`;
  md += `function Example() {\n`;
  md += `  return (\n`;
  md += `    <${component.displayName}\n`;
  
  // Add example props
  props.slice(0, 3).forEach(([name, prop]: [string, any]) => {
    if (prop.required) {
      const exampleValue = getExampleValue(prop.type?.name);
      md += `      ${name}={${exampleValue}}\n`;
    }
  });
  
  md += `    />\n`;
  md += `  );\n`;
  md += `}\n`;
  md += `\`\`\`\n\n`;
  
  return md;
}

function getExampleValue(type: string): string {
  switch (type) {
    case 'string': return '"example"';
    case 'number': return '42';
    case 'boolean': return 'true';
    case 'function':
    case '() => void': return '() => {}';
    default: return '...';
  }
}

// Main execution
const componentFiles = findComponents(COMPONENTS_DIR);

componentFiles.forEach(file => {
  try {
    const components = parse(file, options);
    
    components.forEach(component => {
      if (component.displayName) {
        const markdown = generateMarkdown(component);
        const outputPath = path.join(DOCS_DIR, `${component.displayName}.md`);
        
        fs.writeFileSync(outputPath, markdown);
        console.log(`✅ Generated: ${outputPath}`);
      }
    });
  } catch (error) {
    console.warn(`⚠️ Skipped: ${file}`);
  }
});

// Generate index
const indexContent = `# Component Documentation\n\n` +
  fs.readdirSync(DOCS_DIR)
    .filter(f => f.endsWith('.md') && f !== 'index.md')
    .map(f => `- [${f.replace('.md', '')}](./${f})`)
    .join('\n');

fs.writeFileSync(path.join(DOCS_DIR, 'index.md'), indexContent);
console.log('\n✅ Generated index.md');
```

### Package.json Script

```json
{
  "scripts": {
    "docs:components": "ts-node scripts/generate-docs.ts",
    "docs:build": "npm run docs:components && vitepress build docs",
    "docs:dev": "vitepress dev docs"
  }
}
```

---

## VUE (VUESE)

### Setup

```bash
npm install -D @vuese/cli
```

### Configuration

```javascript
// .vueserc.js
module.exports = {
  include: ['src/components/**/*.vue'],
  exclude: ['**/node_modules/**', '**/*.stories.vue'],
  outDir: 'docs/components',
  markdownDir: 'docs/components',
  genType: 'markdown',
  title: 'Component Documentation',
};
```

### Running

```bash
npx vuese gen
```

### Manual Vue Documentation

```vue
<!-- src/components/Button.vue -->
<template>
  <!-- @slot Default slot for button content -->
  <button
    :class="['btn', `btn--${variant}`, `btn--${size}`]"
    :disabled="disabled || loading"
    @click="handleClick"
  >
    <span v-if="loading" class="btn__spinner" aria-hidden="true"></span>
    <slot />
  </button>
</template>

<script lang="ts">
/**
 * @displayName Button
 * @description Primary button component with multiple variants and sizes.
 * Uses design tokens for all styling.
 */
export default {
  name: 'Button',
  props: {
    /**
     * Button variant style
     * @values primary, secondary, outline, ghost
     */
    variant: {
      type: String,
      default: 'primary',
      validator: (v: string) => ['primary', 'secondary', 'outline', 'ghost'].includes(v),
    },
    /**
     * Button size
     * @values sm, md, lg
     */
    size: {
      type: String,
      default: 'md',
      validator: (v: string) => ['sm', 'md', 'lg'].includes(v),
    },
    /**
     * Disabled state
     */
    disabled: {
      type: Boolean,
      default: false,
    },
    /**
     * Loading state with spinner
     */
    loading: {
      type: Boolean,
      default: false,
    },
  },
  emits: {
    /**
     * Fired when button is clicked
     * @arg {MouseEvent} event - Native click event
     */
    click: (event: MouseEvent) => true,
  },
  methods: {
    handleClick(event: MouseEvent) {
      if (!this.disabled && !this.loading) {
        this.$emit('click', event);
      }
    },
  },
};
</script>

<style scoped>
/* Design tokens used:
 * --space-2, --space-3, --space-4
 * --accent, --text, --border
 * --radius-md, --radius-lg
 * --transition-fast
 */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: var(--space-2);
  font-family: var(--font-body);
  font-weight: 500;
  border-radius: var(--radius-md);
  transition: all var(--transition-fast);
}

.btn--primary {
  background: var(--accent);
  color: white;
  border: none;
}

.btn--sm { padding: var(--space-2) var(--space-3); font-size: var(--text-sm); }
.btn--md { padding: var(--space-3) var(--space-4); font-size: var(--text-base); }
.btn--lg { padding: var(--space-4) var(--space-6); font-size: var(--text-lg); }
</style>
```

---

## VITEPRESS DOCUMENTATION SITE

### Setup

```bash
npm install -D vitepress
```

### Configuration

```typescript
// docs/.vitepress/config.ts
import { defineConfig } from 'vitepress';

export default defineConfig({
  title: 'Component Library',
  description: 'Documentation for our design system components',
  themeConfig: {
    sidebar: [
      {
        text: 'Getting Started',
        items: [
          { text: 'Introduction', link: '/introduction' },
          { text: 'Installation', link: '/installation' },
          { text: 'Design Tokens', link: '/tokens' },
        ],
      },
      {
        text: 'Components',
        items: [
          { text: 'Button', link: '/components/Button' },
          { text: 'Input', link: '/components/Input' },
          { text: 'Select', link: '/components/Select' },
          { text: 'Modal', link: '/components/Modal' },
        ],
      },
    ],
  },
});
```

### Component Documentation Page

```md
<!-- docs/components/Button.md -->
# Button

Primary button component with multiple variants and sizes.

<script setup>
import Button from '../../src/components/Button.vue';
</script>

## Preview

<div class="demo-container">
  <Button variant="primary">Primary</Button>
  <Button variant="secondary">Secondary</Button>
  <Button variant="outline">Outline</Button>
  <Button variant="ghost">Ghost</Button>
</div>

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `variant` | `'primary' \| 'secondary' \| 'outline' \| 'ghost'` | `'primary'` | Button style variant |
| `size` | `'sm' \| 'md' \| 'lg'` | `'md'` | Button size |
| `disabled` | `boolean` | `false` | Disabled state |
| `loading` | `boolean` | `false` | Loading state with spinner |

## Design Tokens

```css
/* This component uses these design tokens */
--space-2, --space-3, --space-4, --space-6
--accent, --text, --border
--radius-md
--transition-fast
```

## Usage

```vue
<template>
  <Button variant="primary" size="md" @click="handleClick">
    Click me
  </Button>
</template>
```

## Accessibility

- Uses native `<button>` element
- Disabled state communicated via `disabled` attribute
- Loading state shows spinner with `aria-hidden="true"`
- Focus ring visible with `var(--accent)` outline
```

---

## CI INTEGRATION

### Auto-Generate on Build

```yaml
# .github/workflows/docs.yml
name: Generate Docs

on:
  push:
    branches: [main]
    paths:
      - 'src/components/**'

jobs:
  docs:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      
      - name: Setup Node
        uses: actions/setup-node@v4
        with:
          node-version: '20'

      - name: Install
        run: npm ci

      - name: Generate docs
        run: npm run docs:components

      - name: Commit docs
        uses: stefanzweifel/git-auto-commit-action@v5
        with:
          commit_message: 'docs: auto-generate component documentation'
          file_pattern: 'docs/components/*.md'
```

---

## CHECKLIST

- [ ] Script parses component source files
- [ ] Props extracted with types and defaults
- [ ] Description from JSDoc/comments
- [ ] Design tokens documented
- [ ] Usage examples generated
- [ ] Index file lists all components
- [ ] CI regenerates on component changes
- [ ] VitePress/Docusaurus for browsing
