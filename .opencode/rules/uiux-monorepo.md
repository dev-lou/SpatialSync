---
description: Share design system across multiple apps using npm workspaces, Turborepo, or publishable packages. Avoid duplication.
---

# Monorepo Design System Skill

> Share design tokens and components across multiple applications.

---

## ARCHITECTURE OPTIONS

| Approach | Best For | Complexity |
|----------|----------|------------|
| npm workspaces | Small teams, 2-3 apps | Low |
| Turborepo | Medium teams, 5+ apps | Medium |
| Nx | Large enterprise | High |
| Publishable package | External consumers | Medium |

---

## NPM WORKSPACES (BASIC)

### Folder Structure

```
my-monorepo/
├── package.json              # Root package.json
├── packages/
│   ├── tokens/               # Design tokens
│   │   ├── package.json
│   │   ├── src/
│   │   │   ├── tokens.css
│   │   │   ├── tokens.js
│   │   │   └── index.ts
│   │   └── tsconfig.json
│   ├── ui/                   # Shared components
│   │   ├── package.json
│   │   ├── src/
│   │   │   ├── Button/
│   │   │   ├── Input/
│   │   │   └── index.ts
│   │   └── tsconfig.json
│   └── utils/                # Shared utilities
│       ├── package.json
│       └── src/
└── apps/
    ├── web/                  # Main web app
    │   ├── package.json
    │   └── src/
    ├── admin/                # Admin dashboard
    │   ├── package.json
    │   └── src/
    └── docs/                 # Documentation site
        ├── package.json
        └── src/
```

### Root `package.json`

```json
{
  "name": "my-monorepo",
  "private": true,
  "workspaces": [
    "packages/*",
    "apps/*"
  ],
  "scripts": {
    "dev": "npm run dev --workspaces --if-present",
    "build": "npm run build --workspaces --if-present",
    "lint": "npm run lint --workspaces --if-present",
    "test": "npm run test --workspaces --if-present"
  },
  "devDependencies": {
    "typescript": "^5.0.0"
  }
}
```

### Tokens Package

```json
// packages/tokens/package.json
{
  "name": "@myorg/tokens",
  "version": "1.0.0",
  "main": "dist/index.js",
  "module": "dist/index.mjs",
  "types": "dist/index.d.ts",
  "exports": {
    ".": {
      "import": "./dist/index.mjs",
      "require": "./dist/index.js",
      "types": "./dist/index.d.ts"
    },
    "./css": "./dist/tokens.css"
  },
  "files": ["dist"],
  "scripts": {
    "build": "tsup src/index.ts --format cjs,esm --dts && cp src/tokens.css dist/"
  }
}
```

```css
/* packages/tokens/src/tokens.css */
:root {
  /* Colors */
  --color-primary: #3b82f6;
  --color-primary-hover: #2563eb;
  --color-secondary: #64748b;
  
  /* Spacing (8px grid) */
  --space-1: 4px;
  --space-2: 8px;
  --space-3: 12px;
  --space-4: 16px;
  --space-6: 24px;
  --space-8: 32px;
  
  /* Typography */
  --font-sans: system-ui, -apple-system, sans-serif;
  --font-mono: 'JetBrains Mono', monospace;
  
  --text-xs: 0.75rem;
  --text-sm: 0.875rem;
  --text-base: 1rem;
  --text-lg: 1.125rem;
  --text-xl: 1.25rem;
  
  /* Radius */
  --radius-sm: 4px;
  --radius-md: 8px;
  --radius-lg: 12px;
  --radius-full: 9999px;
  
  /* Shadows */
  --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
  --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
  --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
  
  /* Transitions */
  --transition-fast: 150ms ease;
  --transition-base: 250ms ease;
}

[data-theme="dark"] {
  --color-bg: #0f172a;
  --color-surface: #1e293b;
  --color-text: #f1f5f9;
  --color-text-muted: #94a3b8;
}

[data-theme="light"] {
  --color-bg: #ffffff;
  --color-surface: #f8fafc;
  --color-text: #0f172a;
  --color-text-muted: #64748b;
}
```

```typescript
// packages/tokens/src/index.ts
export const tokens = {
  colors: {
    primary: 'var(--color-primary)',
    primaryHover: 'var(--color-primary-hover)',
    secondary: 'var(--color-secondary)',
  },
  spacing: {
    1: 'var(--space-1)',
    2: 'var(--space-2)',
    3: 'var(--space-3)',
    4: 'var(--space-4)',
    6: 'var(--space-6)',
    8: 'var(--space-8)',
  },
  // ... etc
} as const;

export type Tokens = typeof tokens;
```

### UI Package

```json
// packages/ui/package.json
{
  "name": "@myorg/ui",
  "version": "1.0.0",
  "main": "dist/index.js",
  "module": "dist/index.mjs",
  "types": "dist/index.d.ts",
  "peerDependencies": {
    "react": "^18.0.0",
    "react-dom": "^18.0.0"
  },
  "dependencies": {
    "@myorg/tokens": "workspace:*"
  },
  "scripts": {
    "build": "tsup src/index.ts --format cjs,esm --dts --external react"
  }
}
```

```tsx
// packages/ui/src/Button/Button.tsx
import '@myorg/tokens/css';

export interface ButtonProps {
  variant?: 'primary' | 'secondary' | 'outline';
  size?: 'sm' | 'md' | 'lg';
  children: React.ReactNode;
  onClick?: () => void;
  disabled?: boolean;
}

export function Button({
  variant = 'primary',
  size = 'md',
  children,
  onClick,
  disabled,
}: ButtonProps) {
  return (
    <button
      className={`btn btn--${variant} btn--${size}`}
      onClick={onClick}
      disabled={disabled}
    >
      {children}
    </button>
  );
}
```

### App Usage

```json
// apps/web/package.json
{
  "name": "web",
  "dependencies": {
    "@myorg/tokens": "workspace:*",
    "@myorg/ui": "workspace:*"
  }
}
```

```tsx
// apps/web/src/App.tsx
import '@myorg/tokens/css';
import { Button } from '@myorg/ui';

export function App() {
  return (
    <Button variant="primary" onClick={() => alert('Clicked!')}>
      Click me
    </Button>
  );
}
```

---

## TURBOREPO (RECOMMENDED)

### Setup

```bash
npx create-turbo@latest
```

### `turbo.json`

```json
{
  "$schema": "https://turbo.build/schema.json",
  "globalDependencies": ["**/.env.*local"],
  "pipeline": {
    "build": {
      "dependsOn": ["^build"],
      "outputs": ["dist/**", ".next/**", "!.next/cache/**"]
    },
    "lint": {},
    "dev": {
      "cache": false,
      "persistent": true
    },
    "test": {
      "dependsOn": ["build"]
    }
  }
}
```

### Scripts

```json
// Root package.json
{
  "scripts": {
    "dev": "turbo run dev",
    "build": "turbo run build",
    "lint": "turbo run lint",
    "test": "turbo run test",
    "clean": "turbo run clean && rm -rf node_modules"
  }
}
```

### Build Order

Turborepo automatically handles dependency ordering:

```
1. @myorg/tokens builds first (no dependencies)
2. @myorg/ui builds second (depends on tokens)
3. apps/web builds third (depends on ui and tokens)
```

---

## PUBLISHABLE PACKAGE

### For External Consumers

```json
// packages/tokens/package.json
{
  "name": "@myorg/design-tokens",
  "version": "1.0.0",
  "publishConfig": {
    "access": "public"
  },
  "repository": {
    "type": "git",
    "url": "https://github.com/myorg/design-system.git",
    "directory": "packages/tokens"
  },
  "files": ["dist"],
  "exports": {
    ".": {
      "import": "./dist/index.mjs",
      "require": "./dist/index.js"
    },
    "./css": "./dist/tokens.css",
    "./tailwind": "./dist/tailwind.config.js"
  }
}
```

### Changesets for Versioning

```bash
npm install -D @changesets/cli
npx changeset init
```

```json
// .changeset/config.json
{
  "$schema": "https://unpkg.com/@changesets/config@3.0.0/schema.json",
  "changelog": "@changesets/cli/changelog",
  "commit": false,
  "fixed": [],
  "linked": [["@myorg/tokens", "@myorg/ui"]],
  "access": "public",
  "baseBranch": "main",
  "updateInternalDependencies": "patch"
}
```

### Publish Workflow

```yaml
# .github/workflows/release.yml
name: Release

on:
  push:
    branches: [main]

jobs:
  release:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      
      - name: Setup Node
        uses: actions/setup-node@v4
        with:
          node-version: '20'
          registry-url: 'https://registry.npmjs.org'

      - name: Install
        run: npm ci

      - name: Build
        run: npm run build

      - name: Create Release PR or Publish
        uses: changesets/action@v1
        with:
          publish: npm run release
        env:
          GITHUB_TOKEN: ${{ secrets.GITHUB_TOKEN }}
          NPM_TOKEN: ${{ secrets.NPM_TOKEN }}
```

---

## SHARED TSCONFIG

```json
// packages/tsconfig/base.json
{
  "$schema": "https://json.schemastore.org/tsconfig",
  "display": "Default",
  "compilerOptions": {
    "composite": false,
    "declaration": true,
    "declarationMap": true,
    "esModuleInterop": true,
    "forceConsistentCasingInFileNames": true,
    "inlineSources": false,
    "isolatedModules": true,
    "moduleResolution": "node",
    "noUnusedLocals": false,
    "noUnusedParameters": false,
    "preserveWatchOutput": true,
    "skipLibCheck": true,
    "strict": true
  },
  "exclude": ["node_modules"]
}
```

```json
// packages/ui/tsconfig.json
{
  "extends": "@myorg/tsconfig/react-library.json",
  "compilerOptions": {
    "outDir": "dist"
  },
  "include": ["src"],
  "exclude": ["node_modules", "dist"]
}
```

---

## AVOIDING DUPLICATION

### Rules

1. **Tokens in one place** — `packages/tokens`
2. **Components in one place** — `packages/ui`
3. **No local token overrides** — Apps import, never redefine
4. **Shared configs** — ESLint, TypeScript, Prettier

### Import Enforcement

```javascript
// eslint-plugin-myorg/rules/no-duplicate-tokens.js
module.exports = {
  create(context) {
    return {
      VariableDeclaration(node) {
        const filename = context.getFilename();
        if (!filename.includes('packages/tokens')) {
          node.declarations.forEach(decl => {
            if (decl.id.name?.startsWith('--')) {
              context.report({
                node,
                message: 'CSS custom properties must be defined in @myorg/tokens',
              });
            }
          });
        }
      },
    };
  },
};
```

---

## CHECKLIST

- [ ] Tokens package with CSS and JS exports
- [ ] UI package with peer dependencies
- [ ] Workspace linking (`workspace:*`)
- [ ] Turborepo for build orchestration
- [ ] Shared TypeScript config
- [ ] Changesets for versioning
- [ ] No duplicate token definitions
- [ ] CI/CD for publishing

---

## YARN WORKSPACES

For teams using Yarn, configure workspaces in `package.json`:

```json
// package.json (root)
{
  "name": "my-monorepo",
  "private": true,
  "workspaces": [
    "packages/*",
    "apps/*"
  ]
}
```

Then use `yarn install` to link all packages. Reference internal packages with:

```json
// apps/web/package.json
{
  "dependencies": {
    "@myorg/tokens": "*",
    "@myorg/ui": "*"
  }
}
```

---

## PNPM WORKSPACES

For teams using pnpm, create a `pnpm-workspace.yaml` at the root:

```yaml
# pnpm-workspace.yaml
packages:
  - 'packages/*'
  - 'apps/*'
```

Then use `pnpm install` to link all packages. Reference internal packages with:

```json
// apps/web/package.json
{
  "dependencies": {
    "@myorg/tokens": "workspace:*",
    "@myorg/ui": "workspace:*"
  }
}
```

---

## NOTE: FRAMEWORK ADAPTABILITY

The examples in this file focus on React/Next.js, but the monorepo architecture applies equally to Vue, Svelte, Solid, or any other framework. The key principles remain the same:

- Tokens in a single package
- UI components in a shared package
- Apps consume packages via workspace linking

Adapt the component syntax to your framework of choice.
