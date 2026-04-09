# UI/UX Architecture 2026

> Feature-Sliced Design, component architecture, state management (Signals vs Context),
> folder structure, and code organization patterns for scalable UI systems.
> Prevents spaghetti code in large applications.

---

## DECISION TREE — Architecture Tier

```
Project size?
├─ Small (1 dev, <10 routes, <3 months)
│   └─ Flat structure: /components, /hooks, /pages — that's enough
├─ Medium (2–5 devs, 10–30 routes)
│   └─ Layer by type: /features, /shared, /pages
└─ Large (5+ devs, 30+ routes, long-lived)
    └─ Feature-Sliced Design (FSD) — see below
```

---

## STATE MANAGEMENT DECISION TREE

Use this tree to choose the right state management tool for your specific use case:

*   **Local Component State** (e.g., form input, toggle, local counter)
    *   **Tool**: `useState` / `useReducer`
    *   **Why**: Simplest, built-in, no overhead.
*   **Shared UI State** (e.g., theme, current user, small group of components)
    *   **Tool**: `useContext`
    *   **Why**: Built-in, avoids prop drilling for shared static or low-frequency data.
*   **Global App State** (e.g., cart, multi-step workflow, complex cross-page data)
    *   **Tool**: `Zustand`
    *   **Why**: Global, simple API, excellent performance, supports persistence (localStorage).
*   **High-Frequency / Fine-Grained State** (e.g., real-time dashboards, complex animations)
    *   **Tool**: `Signals` (Preact Signals in React)
    *   **Why**: Fine-grained reactivity; only components reading the signal re-render. Massive performance gain.
*   **Server State** (e.g., API responses, loading/error states, caching)
    *   **Tool**: `TanStack Query` (React Query)
    *   **Why**: **Mandatory**. Handles caching, revalidation, and loading states automatically.

---

## FEATURE-SLICED DESIGN (FSD) — Large Apps

FSD organizes by **business domain**, not file type. Prevents import cycles.
Rule: each layer can only import from layers **below** it.

```
src/
├─ app/          (L1 — top) config, providers, global styles, router
├─ pages/        (L2) page components — compose features, no business logic
├─ widgets/      (L3) self-contained page sections (Header, Sidebar, PlayerBar)
├─ features/     (L4) user interactions (auth, cart, search, notifications)
├─ entities/     (L5) business objects (User, Product, Order)
└─ shared/       (L6 — bottom) UI kit, utils, API clients, hooks
    ├─ ui/       (Button, Input, Card — no business knowledge)
    ├─ api/      (base fetch client)
    ├─ lib/      (date, format, validate utils)
    └─ config/   (env vars, constants)
```

### Import Rules (FSD)

```
✅ pages     → can import from: widgets, features, entities, shared
✅ widgets   → can import from: features, entities, shared
✅ features  → can import from: entities, shared
✅ entities  → can import from: shared
✅ shared    → can import from: nothing (zero dependencies)

❌ shared    → NEVER imports from features/entities
❌ entities  → NEVER imports from features
❌ features  → NEVER imports from widgets/pages
```

### Slice Structure (each feature/entity/widget has same shape)

```
features/auth/
├─ ui/          LoginForm.jsx, OAuthButtons.jsx
├─ model/       store.js (state), effects.js (side effects)
├─ api/         auth.api.js (fetch calls)
├─ lib/         validators.js, tokens.js
└─ index.js     PUBLIC API — only exports what others need
```

```javascript
// features/auth/index.js — PUBLIC API
// Only export what other slices need. Hide internals.
export { LoginForm }   from './ui/LoginForm';
export { useAuthStore } from './model/store';
export { login, logout } from './api/auth.api';
// DO NOT export: validators, internal hooks, implementation details
```

---

## COMPONENT ARCHITECTURE PATTERNS

### Compound Components (for complex UI)

```jsx
// BAD — prop drilling hell
<Tabs activeTab={tab} onTabChange={setTab} tabs={tabs} content={content} />

// GOOD — compound component (Radix UI / shadcn style)
<Tabs defaultValue="overview">
  <TabsList>
    <TabsTrigger value="overview">Overview</TabsTrigger>
    <TabsTrigger value="analytics">Analytics</TabsTrigger>
  </TabsList>
  <TabsContent value="overview"><OverviewPanel /></TabsContent>
  <TabsContent value="analytics"><AnalyticsPanel /></TabsContent>
</Tabs>
```

### Headless Components (Radix UI / shadcn pattern)

```jsx
// Headless = behavior + accessibility, zero styles
// Style 100% via your design tokens
import * as Dialog from '@radix-ui/react-dialog';

function Modal({ title, children, trigger }) {
  return (
    <Dialog.Root>
      <Dialog.Trigger asChild>{trigger}</Dialog.Trigger>
      <Dialog.Portal>
        <Dialog.Overlay className="modal-overlay" />
        <Dialog.Content className="modal-content"
          aria-describedby="modal-desc">
          <Dialog.Title className="modal-title">{title}</Dialog.Title>
          <div id="modal-desc">{children}</div>
          <Dialog.Close asChild>
            <button className="btn btn--icon modal-close" aria-label="Close">
              <i data-lucide="x"></i>
            </button>
          </Dialog.Close>
        </Dialog.Content>
      </Dialog.Portal>
    </Dialog.Root>
  );
}
```

**Why headless:** Radix/shadcn handle focus trap, Escape key, ARIA, scroll lock.
You handle zero accessibility boilerplate. You control 100% of styles.

### Recommended Headless Libraries (2026)

| Library | Use For | Style Method |
|---------|---------|--------------|
| **Radix UI** | Dialog, Select, Tabs, Accordion, Tooltip, Menu | CSS classes |
| **shadcn/ui** | Pre-built Radix + Tailwind components (copy-paste) | Tailwind |
| **cmdk** | Command palette (⌘K) | CSS/Tailwind |
| **react-aria (Adobe)** | Complex accessible components | CSS |
| **Floating UI** | Popover, tooltip positioning | CSS |
| **Vaul** | Mobile-first drawer/sheet | CSS/Tailwind |

---

## STATE MANAGEMENT — Signals vs Context (2026)

### Decision Tree

```
State type?
├─ Local component state (counter, toggle, form field)
│   └─ useState / useReducer — always
├─ Shared state, few components, same subtree
│   └─ useContext — fine for theme, locale, auth user
├─ Shared state, many components, performance matters
│   └─ Signals (Preact, Solid) or Zustand / Jotai / Valtio
├─ Server state (API data, loading, caching)
│   └─ TanStack Query (React Query) — non-negotiable
└─ Global app state (complex, large team)
    └─ Zustand (simple) or Redux Toolkit (enterprise)
```

### Signals Pattern (Preact Signals in React)

```javascript
// Signals: fine-grained reactivity — only components that READ the signal re-render
// vs Context: ALL consumers re-render when context changes (massive perf issue)

import { signal, computed } from '@preact/signals-react';

// Define signals outside components — they're global reactive atoms
const count = signal(0);
const doubled = computed(() => count.value * 2);

// Component only re-renders when count changes — not when parent renders
function Counter() {
  return (
    <div>
      <p>Count: {count}</p>
      <p>Doubled: {doubled}</p>
      <button onClick={() => count.value++}>+1</button>
    </div>
  );
}
```

### Zustand (recommended for most apps)

```javascript
// stores/useCartStore.js
import { create } from 'zustand';
import { persist } from 'zustand/middleware';

const useCartStore = create(
  persist(
    (set, get) => ({
      items: [],
      addItem: (item) => set(s => ({ items: [...s.items, item] })),
      removeItem: (id) => set(s => ({ items: s.items.filter(i => i.id !== id) })),
      total: () => get().items.reduce((sum, i) => sum + i.price, 0),
      clear: () => set({ items: [] }),
    }),
    { name: 'cart-storage' } // persists to localStorage
  )
);
// Usage: const { items, addItem } = useCartStore();
```

### TanStack Query (server state — mandatory)

```javascript
// Never store API responses in useState. Use TanStack Query.
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';

function ProductList() {
  const { data, isLoading, error } = useQuery({
    queryKey: ['products'],
    queryFn: () => fetch('/api/products').then(r => r.json()),
    staleTime: 5 * 60 * 1000, // 5 min cache
  });

  if (isLoading) return <ProductSkeleton />;
  if (error)    return <ErrorState error={error} />;
  return <div>{data.map(p => <ProductCard key={p.id} {...p} />)}</div>;
}

// Mutation with optimistic update
const qc = useQueryClient();
const mutation = useMutation({
  mutationFn: (newProduct) => fetch('/api/products', { method:'POST', body:JSON.stringify(newProduct) }),
  onSuccess: () => qc.invalidateQueries({ queryKey: ['products'] }),
});
```

---

## FOLDER STRUCTURE BY STACK

### Next.js App Router (recommended 2026)

```
src/
├─ app/
│   ├─ layout.tsx          global layout, providers, fonts
│   ├─ page.tsx            home
│   ├─ (marketing)/        route group — no URL segment
│   │   ├─ about/page.tsx
│   │   └─ pricing/page.tsx
│   ├─ (app)/              protected route group
│   │   ├─ dashboard/page.tsx
│   │   └─ settings/page.tsx
│   └─ api/                route handlers
├─ components/
│   ├─ ui/                 shared primitives (Button, Card, Input)
│   └─ [feature]/          feature-specific components
├─ hooks/                  custom hooks
├─ lib/                    utils, api clients, validators
├─ stores/                 Zustand stores
└─ styles/
    ├─ tokens.css          brand tokens — import in layout.tsx
    └─ globals.css         reset + base
```

### React (Vite / SPA)

```
src/
├─ pages/
├─ features/              FSD features
├─ components/ui/         shared primitives
├─ hooks/
├─ stores/
├─ api/                   fetch clients
├─ lib/                   utils
└─ styles/tokens.css
```

### Vue 3 / Nuxt 3

```
├─ composables/           useTheme, useCart, useAuth
├─ stores/                Pinia stores (= Zustand for Vue)
├─ components/
│   ├─ base/              base components (BaseButton, BaseCard)
│   └─ [feature]/
└─ assets/styles/tokens.css
```

---

## PERFORMANCE ARCHITECTURE

### Code Splitting (Next.js / React)

```javascript
// Lazy-load heavy components
import dynamic from 'next/dynamic';

const Chart = dynamic(() => import('@/components/Chart'), {
  loading: () => <div className="skeleton" style={{height:300}} />,
  ssr: false, // charts are client-only
});

const Modal = dynamic(() => import('@/components/Modal'));
// Modal doesn't load until first render — saves initial bundle
```

### Image Optimization

```jsx
// Next.js
import Image from 'next/image';
<Image src="/hero.jpg" width={1920} height={1080}
  alt="Hero" priority sizes="100vw" />

// Vanilla (already in uiux-components.md)
// Always: width, height, loading, decoding, fetchpriority on above-fold
```

### Bundle Budget

| Asset type | Budget |
|-----------|--------|
| Initial JS (gzip) | < 200KB |
| Per-route JS | < 50KB |
| CSS (gzip) | < 30KB |
| Fonts | < 100KB total |
| Hero image (WebP) | < 200KB |
| LCP target | < 2.5s |

---

## QUICK REFERENCE

| Scenario | Solution |
|----------|---------|
| Large app, team > 3 | Feature-Sliced Design |
| Complex UI components | Radix UI headless |
| Pre-styled components needed fast | shadcn/ui |
| Fine-grained reactivity | Preact Signals |
| Simple global state | Zustand |
| API data caching | TanStack Query |
| Heavy component (charts, maps) | Dynamic import |
| Server + client state | TanStack Query + Zustand |
