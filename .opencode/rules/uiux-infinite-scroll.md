---
description: Infinite scroll pattern with Intersection Observer. Loading skeleton, end of content message, manual load more fallback. Respects prefers-reduced-motion.
---

# UI/UX Infinite Scroll

> Load more content as user scrolls with Intersection Observer.
> Accessible, performant, with graceful fallbacks.

---

## OVERVIEW

This skill covers:
1. Intersection Observer implementation
2. Loading skeleton during fetch
3. "End of content" message
4. Manual "Load more" button fallback
5. Error handling and retry
6. Accessibility considerations

---

## HTML STRUCTURE

```html
<div class="feed" id="feed" role="feed" aria-busy="false">
  <!-- Content items -->
  <article class="feed__item">...</article>
  <article class="feed__item">...</article>
  <article class="feed__item">...</article>
  
  <!-- Sentinel element for intersection observer -->
  <div class="feed__sentinel" id="feed-sentinel" aria-hidden="true"></div>
  
  <!-- Loading state -->
  <div class="feed__loading" id="feed-loading" hidden>
    <div class="skeleton skeleton--card"></div>
    <div class="skeleton skeleton--card"></div>
    <div class="skeleton skeleton--card"></div>
  </div>
  
  <!-- End of content -->
  <div class="feed__end" id="feed-end" hidden>
    <p>You've reached the end!</p>
  </div>
  
  <!-- Error state -->
  <div class="feed__error" id="feed-error" hidden>
    <p>Failed to load more content.</p>
    <button class="btn btn--secondary" id="retry-btn">Try Again</button>
  </div>
  
  <!-- Manual load more (fallback) -->
  <button class="btn btn--secondary feed__load-more" id="load-more-btn" hidden>
    Load More
  </button>
</div>

<!-- Screen reader announcements -->
<div id="feed-status" role="status" aria-live="polite" class="sr-only"></div>
```

---

## CSS STYLES

```css
.feed {
  display: flex;
  flex-direction: column;
  gap: var(--space-6);
}

.feed__item {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  padding: var(--space-6);
}

.feed__sentinel {
  height: 1px;
  width: 100%;
}

.feed__loading {
  display: flex;
  flex-direction: column;
  gap: var(--space-6);
}

.feed__end {
  text-align: center;
  padding: var(--space-8) var(--space-4);
  color: var(--muted);
}

.feed__end p {
  font-size: var(--text-sm);
}

.feed__error {
  text-align: center;
  padding: var(--space-6);
  background: var(--color-error-soft);
  border-radius: var(--radius-lg);
}

.feed__error p {
  color: var(--color-error);
  margin-bottom: var(--space-4);
}

.feed__load-more {
  align-self: center;
  margin-top: var(--space-4);
}

/* Skeleton loading */
.skeleton--card {
  height: 200px;
  background: var(--surface);
  border-radius: var(--radius-lg);
  position: relative;
  overflow: hidden;
}

.skeleton--card::after {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(
    90deg,
    transparent,
    rgba(255, 255, 255, 0.3),
    transparent
  );
  transform: translateX(-100%);
  animation: shimmer 1.5s infinite;
}

@keyframes shimmer {
  100% {
    transform: translateX(100%);
  }
}

@media (prefers-reduced-motion: reduce) {
  .skeleton--card::after {
    animation: none;
    transform: none;
    background: rgba(255, 255, 255, 0.1);
  }
}
```

---

## JAVASCRIPT IMPLEMENTATION

```typescript
interface InfiniteScrollOptions {
  container: HTMLElement;
  sentinel: HTMLElement;
  loadingEl: HTMLElement;
  endEl: HTMLElement;
  errorEl: HTMLElement;
  loadMoreBtn?: HTMLElement;
  fetchData: (page: number) => Promise<{ items: any[]; hasMore: boolean }>;
  renderItem: (item: any) => HTMLElement;
  threshold?: number;
  rootMargin?: string;
}

function initInfiniteScroll(options: InfiniteScrollOptions): void {
  const {
    container,
    sentinel,
    loadingEl,
    endEl,
    errorEl,
    loadMoreBtn,
    fetchData,
    renderItem,
    threshold = 0.1,
    rootMargin = '100px'
  } = options;
  
  let page = 1;
  let isLoading = false;
  let hasMore = true;
  
  const statusEl = document.getElementById('feed-status');
  
  // Check for reduced motion preference
  const prefersReducedMotion = window.matchMedia(
    '(prefers-reduced-motion: reduce)'
  ).matches;
  
  // Announce to screen readers
  function announce(message: string) {
    if (statusEl) {
      statusEl.textContent = message;
      setTimeout(() => {
        statusEl.textContent = '';
      }, 1000);
    }
  }
  
  // Show/hide states
  function showLoading() {
    loadingEl.hidden = false;
    endEl.hidden = true;
    errorEl.hidden = true;
    container.setAttribute('aria-busy', 'true');
  }
  
  function hideLoading() {
    loadingEl.hidden = true;
    container.setAttribute('aria-busy', 'false');
  }
  
  function showEnd() {
    endEl.hidden = false;
    announce('End of content reached');
  }
  
  function showError() {
    errorEl.hidden = false;
    announce('Failed to load more content');
  }
  
  function hideError() {
    errorEl.hidden = true;
  }
  
  // Load more content
  async function loadMore() {
    if (isLoading || !hasMore) return;
    
    isLoading = true;
    showLoading();
    hideError();
    
    try {
      const result = await fetchData(page);
      
      // Render new items
      const fragment = document.createDocumentFragment();
      result.items.forEach(item => {
        const element = renderItem(item);
        fragment.appendChild(element);
      });
      
      // Insert before sentinel
      sentinel.before(fragment);
      
      // Update state
      page++;
      hasMore = result.hasMore;
      
      // Announce to screen readers
      announce(`${result.items.length} new items loaded`);
      
      if (!hasMore) {
        showEnd();
        observer.disconnect();
      }
      
    } catch (error) {
      console.error('Failed to load more:', error);
      showError();
    } finally {
      isLoading = false;
      hideLoading();
    }
  }
  
  // Intersection Observer
  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting && !isLoading && hasMore) {
          loadMore();
        }
      });
    },
    {
      root: null, // viewport
      rootMargin,
      threshold
    }
  );
  
  // Start observing
  observer.observe(sentinel);
  
  // Retry button
  const retryBtn = errorEl.querySelector('#retry-btn');
  retryBtn?.addEventListener('click', () => {
    loadMore();
  });
  
  // Manual load more (fallback for users who prefer it)
  if (loadMoreBtn) {
    // Show button if user prefers reduced motion
    if (prefersReducedMotion) {
      loadMoreBtn.hidden = false;
      observer.disconnect(); // Disable auto-loading
    }
    
    loadMoreBtn.addEventListener('click', () => {
      loadMore();
    });
  }
  
  // Cleanup function
  return {
    destroy: () => {
      observer.disconnect();
    },
    reset: () => {
      page = 1;
      hasMore = true;
      hideLoading();
      endEl.hidden = true;
      hideError();
      observer.observe(sentinel);
    }
  };
}

// Usage
const feed = document.getElementById('feed');
const sentinel = document.getElementById('feed-sentinel');
const loading = document.getElementById('feed-loading');
const end = document.getElementById('feed-end');
const error = document.getElementById('feed-error');
const loadMoreBtn = document.getElementById('load-more-btn');

if (feed && sentinel && loading && end && error) {
  initInfiniteScroll({
    container: feed,
    sentinel,
    loadingEl: loading,
    endEl: end,
    errorEl: error,
    loadMoreBtn,
    
    fetchData: async (page) => {
      const response = await fetch(`/api/posts?page=${page}`);
      const data = await response.json();
      return {
        items: data.posts,
        hasMore: data.hasMore
      };
    },
    
    renderItem: (post) => {
      const article = document.createElement('article');
      article.className = 'feed__item';
      article.innerHTML = `
        <h2>${post.title}</h2>
        <p>${post.excerpt}</p>
      `;
      return article;
    }
  });
}
```

---

## REACT COMPONENT

```tsx
import { useState, useEffect, useRef, useCallback } from 'react';

interface UseInfiniteScrollOptions<T> {
  fetchData: (page: number) => Promise<{ items: T[]; hasMore: boolean }>;
  initialPage?: number;
  threshold?: number;
  rootMargin?: string;
}

function useInfiniteScroll<T>({
  fetchData,
  initialPage = 1,
  threshold = 0.1,
  rootMargin = '100px'
}: UseInfiniteScrollOptions<T>) {
  const [items, setItems] = useState<T[]>([]);
  const [page, setPage] = useState(initialPage);
  const [isLoading, setIsLoading] = useState(false);
  const [hasMore, setHasMore] = useState(true);
  const [error, setError] = useState<Error | null>(null);
  
  const sentinelRef = useRef<HTMLDivElement>(null);
  const statusRef = useRef<HTMLDivElement>(null);
  
  const prefersReducedMotion = typeof window !== 'undefined' 
    ? window.matchMedia('(prefers-reduced-motion: reduce)').matches 
    : false;
  
  const announce = useCallback((message: string) => {
    if (statusRef.current) {
      statusRef.current.textContent = message;
      setTimeout(() => {
        if (statusRef.current) statusRef.current.textContent = '';
      }, 1000);
    }
  }, []);
  
  const loadMore = useCallback(async () => {
    if (isLoading || !hasMore) return;
    
    setIsLoading(true);
    setError(null);
    
    try {
      const result = await fetchData(page);
      setItems(prev => [...prev, ...result.items]);
      setPage(prev => prev + 1);
      setHasMore(result.hasMore);
      announce(`${result.items.length} new items loaded`);
    } catch (err) {
      setError(err as Error);
      announce('Failed to load more content');
    } finally {
      setIsLoading(false);
    }
  }, [fetchData, page, isLoading, hasMore, announce]);
  
  // Intersection Observer
  useEffect(() => {
    if (prefersReducedMotion) return; // Use manual load for reduced motion
    
    const sentinel = sentinelRef.current;
    if (!sentinel) return;
    
    const observer = new IntersectionObserver(
      ([entry]) => {
        if (entry.isIntersecting && !isLoading && hasMore) {
          loadMore();
        }
      },
      { threshold, rootMargin }
    );
    
    observer.observe(sentinel);
    
    return () => observer.disconnect();
  }, [loadMore, isLoading, hasMore, threshold, rootMargin, prefersReducedMotion]);
  
  const reset = useCallback(() => {
    setItems([]);
    setPage(initialPage);
    setHasMore(true);
    setError(null);
  }, [initialPage]);
  
  return {
    items,
    isLoading,
    hasMore,
    error,
    sentinelRef,
    statusRef,
    loadMore,
    reset,
    prefersReducedMotion
  };
}

// Component
interface FeedProps<T> {
  fetchData: (page: number) => Promise<{ items: T[]; hasMore: boolean }>;
  renderItem: (item: T, index: number) => React.ReactNode;
  keyExtractor: (item: T) => string;
  emptyMessage?: string;
}

export function InfiniteFeed<T>({
  fetchData,
  renderItem,
  keyExtractor,
  emptyMessage = 'No items to display'
}: FeedProps<T>) {
  const {
    items,
    isLoading,
    hasMore,
    error,
    sentinelRef,
    statusRef,
    loadMore,
    prefersReducedMotion
  } = useInfiniteScroll({ fetchData });
  
  return (
    <div className="feed" role="feed" aria-busy={isLoading}>
      {items.length === 0 && !isLoading && (
        <div className="feed__empty">
          <p>{emptyMessage}</p>
        </div>
      )}
      
      {items.map((item, index) => (
        <article key={keyExtractor(item)} className="feed__item">
          {renderItem(item, index)}
        </article>
      ))}
      
      {/* Sentinel */}
      <div ref={sentinelRef} className="feed__sentinel" aria-hidden="true" />
      
      {/* Loading */}
      {isLoading && (
        <div className="feed__loading">
          <div className="skeleton skeleton--card" />
          <div className="skeleton skeleton--card" />
          <div className="skeleton skeleton--card" />
        </div>
      )}
      
      {/* End of content */}
      {!hasMore && items.length > 0 && (
        <div className="feed__end">
          <p>You've reached the end!</p>
        </div>
      )}
      
      {/* Error */}
      {error && (
        <div className="feed__error">
          <p>Failed to load more content.</p>
          <button className="btn btn--secondary" onClick={loadMore}>
            Try Again
          </button>
        </div>
      )}
      
      {/* Manual load more for reduced motion */}
      {prefersReducedMotion && hasMore && !isLoading && (
        <button className="btn btn--secondary feed__load-more" onClick={loadMore}>
          Load More
        </button>
      )}
      
      {/* Screen reader status */}
      <div ref={statusRef} role="status" aria-live="polite" className="sr-only" />
    </div>
  );
}
```

### Usage

```tsx
interface Post {
  id: string;
  title: string;
  excerpt: string;
}

function PostsFeed() {
  const fetchPosts = async (page: number) => {
    const response = await fetch(`/api/posts?page=${page}`);
    const data = await response.json();
    return {
      items: data.posts as Post[],
      hasMore: data.hasMore
    };
  };
  
  return (
    <InfiniteFeed
      fetchData={fetchPosts}
      keyExtractor={(post) => post.id}
      renderItem={(post) => (
        <>
          <h2>{post.title}</h2>
          <p>{post.excerpt}</p>
        </>
      )}
    />
  );
}
```

---

## ACCESSIBILITY

### ARIA Attributes

```html
<!-- Feed container -->
<div role="feed" aria-busy="false">
  
  <!-- Individual items can use article -->
  <article>...</article>
  
</div>

<!-- Live region for announcements -->
<div role="status" aria-live="polite" class="sr-only">
  <!-- "10 new items loaded" -->
</div>
```

### Reduced Motion Support

```javascript
const prefersReducedMotion = window.matchMedia(
  '(prefers-reduced-motion: reduce)'
).matches;

if (prefersReducedMotion) {
  // Show manual "Load More" button
  // Disable auto-loading
  loadMoreBtn.hidden = false;
  observer.disconnect();
}
```

### Keyboard Navigation

```css
/* Ensure items are focusable */
.feed__item:focus-within {
  outline: 2px solid var(--accent);
  outline-offset: 2px;
}
```

---

## PERFORMANCE TIPS

1. **Virtual scrolling** for very long lists
2. **Debounce** the intersection callback
3. **Remove old items** if list gets too long
4. **Use `will-change: transform`** sparingly
5. **Lazy load images** within items

```javascript
// Remove old items to prevent memory issues
const MAX_ITEMS = 100;

if (container.children.length > MAX_ITEMS) {
  const removeCount = container.children.length - MAX_ITEMS;
  for (let i = 0; i < removeCount; i++) {
    container.firstElementChild?.remove();
  }
}
```

---

## CHECKLIST

- [ ] Intersection Observer for scroll detection
- [ ] Loading skeleton during fetch
- [ ] End of content message
- [ ] Error state with retry button
- [ ] Manual "Load More" fallback
- [ ] Respects `prefers-reduced-motion`
- [ ] `role="feed"` on container
- [ ] `aria-busy` during loading
- [ ] Screen reader announcements
- [ ] Handles network errors gracefully
- [ ] Debounced fetch (prevent rapid calls)
- [ ] Memory management for long lists
