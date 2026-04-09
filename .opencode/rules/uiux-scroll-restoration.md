---
description: Save and restore scroll position when navigating between pages. Covers React Router, vanilla JS, Laravel/Livewire, and respects prefers-reduced-motion.
---

# Scroll Restoration Skill

> Preserve user scroll position across page navigations for better UX.

---

## CORE PRINCIPLE

**Rule**: When a user navigates away and returns, restore their scroll position. For fresh navigations, scroll to top. Always respect `prefers-reduced-motion`.

---

## REACT ROUTER (v6+)

### Using ScrollRestoration Component

```tsx
// app/root.tsx or App.tsx
import { ScrollRestoration } from 'react-router-dom';

export default function App() {
  return (
    <>
      <Outlet />
      <ScrollRestoration
        getKey={(location, matches) => {
          // Use pathname as key (default behavior)
          return location.pathname;
          
          // Or use location key for history-based restoration
          // return location.key;
        }}
      />
    </>
  );
}
```

### Custom Scroll Restoration Hook

```tsx
// hooks/useScrollRestoration.ts
import { useEffect, useLayoutEffect, useRef } from 'react';
import { useLocation } from 'react-router-dom';

const scrollPositions = new Map<string, number>();

export function useScrollRestoration() {
  const location = useLocation();
  const prevPathRef = useRef<string | null>(null);
  const prefersReducedMotion = useRef(
    typeof window !== 'undefined' &&
    window.matchMedia('(prefers-reduced-motion: reduce)').matches
  );

  // Save scroll position before navigation
  useEffect(() => {
    const handleBeforeUnload = () => {
      scrollPositions.set(location.pathname, window.scrollY);
    };

    // Save on route change
    return () => {
      scrollPositions.set(location.pathname, window.scrollY);
    };
  }, [location.pathname]);

  // Restore scroll position after navigation
  useLayoutEffect(() => {
    const savedPosition = scrollPositions.get(location.pathname);
    
    // Scroll behavior based on motion preference
    const scrollOptions: ScrollToOptions = {
      top: savedPosition ?? 0,
      left: 0,
      behavior: prefersReducedMotion.current ? 'instant' : 'instant' // Always instant for restoration
    };

    // Use requestAnimationFrame to ensure DOM is ready
    requestAnimationFrame(() => {
      window.scrollTo(scrollOptions);
    });

    prevPathRef.current = location.pathname;
  }, [location.pathname]);
}

// Usage in App.tsx
function App() {
  useScrollRestoration();
  return <Outlet />;
}
```

### Next.js App Router

```tsx
// app/template.tsx — runs on every navigation
'use client';

import { useEffect } from 'react';
import { usePathname } from 'next/navigation';

const scrollCache = new Map<string, number>();

export default function Template({ children }: { children: React.ReactNode }) {
  const pathname = usePathname();

  useEffect(() => {
    // Restore on mount
    const saved = scrollCache.get(pathname);
    if (saved !== undefined) {
      window.scrollTo({ top: saved, behavior: 'instant' });
    }

    // Save on unmount
    return () => {
      scrollCache.set(pathname, window.scrollY);
    };
  }, [pathname]);

  return <>{children}</>;
}
```

---

## VANILLA JAVASCRIPT

### Session Storage Approach

```javascript
// scroll-restoration.js
class ScrollRestoration {
  constructor() {
    this.storageKey = 'scroll-positions';
    this.prefersReducedMotion = window.matchMedia(
      '(prefers-reduced-motion: reduce)'
    ).matches;
    
    this.init();
  }

  init() {
    // Restore on page load
    this.restore();
    
    // Save before unload
    window.addEventListener('beforeunload', () => this.save());
    
    // Save before internal navigation (for SPAs)
    document.addEventListener('click', (e) => {
      const link = e.target.closest('a[href]');
      if (link && link.hostname === location.hostname) {
        this.save();
      }
    });
  }

  getPositions() {
    try {
      return JSON.parse(sessionStorage.getItem(this.storageKey) || '{}');
    } catch {
      return {};
    }
  }

  save() {
    const positions = this.getPositions();
    positions[location.pathname] = window.scrollY;
    
    try {
      sessionStorage.setItem(this.storageKey, JSON.stringify(positions));
    } catch (e) {
      // Storage full or unavailable
      console.warn('Could not save scroll position:', e);
    }
  }

  restore() {
    const positions = this.getPositions();
    const savedY = positions[location.pathname];
    
    if (savedY !== undefined && savedY > 0) {
      // Use requestAnimationFrame to ensure DOM is ready
      requestAnimationFrame(() => {
        window.scrollTo({
          top: savedY,
          left: 0,
          behavior: 'instant' // Always instant for restoration
        });
      });
    }
  }

  // Call this when content loads dynamically
  restoreAfterContentLoad() {
    // Wait for images and lazy content
    if (document.readyState === 'complete') {
      this.restore();
    } else {
      window.addEventListener('load', () => this.restore());
    }
  }

  // Clear saved position (e.g., after form submission)
  clear(pathname = location.pathname) {
    const positions = this.getPositions();
    delete positions[pathname];
    sessionStorage.setItem(this.storageKey, JSON.stringify(positions));
  }
}

// Initialize
const scrollRestoration = new ScrollRestoration();

// Export for module systems
if (typeof module !== 'undefined') {
  module.exports = ScrollRestoration;
}
```

### HTML Integration

```html
<script src="/js/scroll-restoration.js"></script>
<script>
  // Automatic initialization
  // Or manual control:
  // scrollRestoration.clear(); // Clear after form submit
</script>
```

---

## LARAVEL / LIVEWIRE

### Livewire Scroll Restoration

```php
{{-- resources/views/layouts/app.blade.php --}}
<script>
  // Livewire-aware scroll restoration
  document.addEventListener('livewire:navigating', () => {
    sessionStorage.setItem(
      'scroll-' + window.location.pathname,
      window.scrollY.toString()
    );
  });

  document.addEventListener('livewire:navigated', () => {
    const saved = sessionStorage.getItem('scroll-' + window.location.pathname);
    if (saved) {
      requestAnimationFrame(() => {
        window.scrollTo({ top: parseInt(saved), behavior: 'instant' });
      });
    }
  });
</script>
```

### Alpine.js Component

```html
{{-- Blade component --}}
<div x-data="scrollRestoration()" x-init="init()">
  {{ $slot }}
</div>

<script>
document.addEventListener('alpine:init', () => {
  Alpine.data('scrollRestoration', () => ({
    storageKey: 'scroll-positions',
    
    init() {
      this.restore();
      
      window.addEventListener('beforeunload', () => this.save());
      
      // For Livewire SPA mode
      document.addEventListener('livewire:navigating', () => this.save());
      document.addEventListener('livewire:navigated', () => {
        this.$nextTick(() => this.restore());
      });
    },
    
    save() {
      const positions = JSON.parse(
        sessionStorage.getItem(this.storageKey) || '{}'
      );
      positions[location.pathname] = window.scrollY;
      sessionStorage.setItem(this.storageKey, JSON.stringify(positions));
    },
    
    restore() {
      const positions = JSON.parse(
        sessionStorage.getItem(this.storageKey) || '{}'
      );
      const saved = positions[location.pathname];
      
      if (saved > 0) {
        window.scrollTo({ top: saved, behavior: 'instant' });
      }
    }
  }));
});
</script>
```

### Laravel Inertia.js

```javascript
// resources/js/app.js
import { router } from '@inertiajs/vue3'; // or react

const scrollPositions = new Map();

router.on('before', (event) => {
  scrollPositions.set(window.location.pathname, window.scrollY);
});

router.on('navigate', (event) => {
  const saved = scrollPositions.get(window.location.pathname);
  
  if (saved !== undefined) {
    requestAnimationFrame(() => {
      window.scrollTo({ top: saved, behavior: 'instant' });
    });
  }
});
```

---

## VUE ROUTER

```typescript
// router/index.ts
import { createRouter, createWebHistory } from 'vue-router';

const scrollPositions = new Map<string, number>();

const router = createRouter({
  history: createWebHistory(),
  routes: [...],
  
  scrollBehavior(to, from, savedPosition) {
    // Browser back/forward — use native saved position
    if (savedPosition) {
      return {
        ...savedPosition,
        behavior: 'instant'
      };
    }
    
    // Manual restoration from our cache
    const cached = scrollPositions.get(to.path);
    if (cached !== undefined) {
      return {
        top: cached,
        left: 0,
        behavior: 'instant'
      };
    }
    
    // New navigation — scroll to top
    return {
      top: 0,
      left: 0,
      behavior: 'instant'
    };
  }
});

// Save position before navigation
router.beforeEach((to, from) => {
  scrollPositions.set(from.path, window.scrollY);
});

export default router;
```

---

## HANDLING DYNAMIC CONTENT

When content loads asynchronously, scroll restoration may fail because the page isn't tall enough yet.

### Wait for Content

```javascript
// Restore after images load
function restoreAfterContent(targetY) {
  return new Promise((resolve) => {
    // Check if we can scroll to target
    const canScroll = () => document.documentElement.scrollHeight >= targetY + window.innerHeight;
    
    if (canScroll()) {
      window.scrollTo({ top: targetY, behavior: 'instant' });
      resolve();
      return;
    }
    
    // Wait for content with timeout
    let attempts = 0;
    const maxAttempts = 50; // 5 seconds max
    
    const interval = setInterval(() => {
      attempts++;
      
      if (canScroll() || attempts >= maxAttempts) {
        clearInterval(interval);
        window.scrollTo({ 
          top: Math.min(targetY, document.documentElement.scrollHeight - window.innerHeight),
          behavior: 'instant' 
        });
        resolve();
      }
    }, 100);
  });
}
```

### Skeleton Placeholder Height

```css
/* Maintain layout height during loading */
.content-skeleton {
  min-height: var(--content-min-height, 100vh);
}

/* Set based on saved scroll position */
[data-restore-height] {
  min-height: attr(data-restore-height);
}
```

---

## TOKEN-AWARE SCROLL INDICATOR

```css
/* Scroll progress indicator */
.scroll-progress {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: var(--space-1);
  background: var(--surface);
  z-index: var(--z-fixed);
}

.scroll-progress__bar {
  height: 100%;
  background: var(--accent);
  width: 0%;
  transition: width 50ms linear;
}

@media (prefers-reduced-motion: reduce) {
  .scroll-progress__bar {
    transition: none;
  }
}
```

```javascript
// Update progress bar
const progressBar = document.querySelector('.scroll-progress__bar');

function updateProgress() {
  const scrollTop = window.scrollY;
  const docHeight = document.documentElement.scrollHeight - window.innerHeight;
  const progress = (scrollTop / docHeight) * 100;
  progressBar.style.width = `${progress}%`;
}

window.addEventListener('scroll', updateProgress, { passive: true });
```

---

## REDUCED MOTION COMPLIANCE

**Rule**: Always use `behavior: 'instant'` for scroll restoration. Animated scrolling after navigation is disorienting.

```javascript
// Even without prefers-reduced-motion, restoration should be instant
const scrollOptions = {
  top: savedY,
  left: 0,
  behavior: 'instant' // Never 'smooth' for restoration
};

// Smooth scrolling is only for user-initiated actions like "scroll to top"
function scrollToTop() {
  const prefersReducedMotion = window.matchMedia(
    '(prefers-reduced-motion: reduce)'
  ).matches;
  
  window.scrollTo({
    top: 0,
    behavior: prefersReducedMotion ? 'instant' : 'smooth'
  });
}
```

---

## CHECKLIST

- [ ] Scroll position saved before navigation
- [ ] Position restored after navigation
- [ ] Uses `sessionStorage` (clears on tab close)
- [ ] Handles dynamic content loading
- [ ] Uses `behavior: 'instant'` for restoration
- [ ] Respects `prefers-reduced-motion` for user-initiated scrolls
- [ ] Works with framework router (React Router, Vue Router, etc.)
- [ ] Clears position after form submissions
