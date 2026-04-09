---
description: Library of token-aware micro-interactions including ripple, skeleton loaders, toasts, pull-to-refresh, and drag-to-reorder. All respect prefers-reduced-motion.
---

# UI/UX Micro-Interactions Library

> Token-aware micro-interactions that respect prefers-reduced-motion.
> Enhance UX with subtle, meaningful animations.

---

## OVERVIEW

Micro-interactions provide feedback and delight. This skill covers:
1. Ripple effects
2. Skeleton loaders (shimmer, pulse, wave)
3. Toast notifications
4. Pull-to-refresh
5. Drag-to-reorder
6. Button feedback
7. Form interactions

**Critical Rule**: All animations MUST respect `prefers-reduced-motion`.

---

## REDUCED MOTION FOUNDATION

```css
/* Base transition that respects user preference */
:root {
  --dur-micro: 150ms;
  --dur-base: 250ms;
  --dur-slow: 400ms;
  --ease-out: cubic-bezier(0.16, 1, 0.3, 1);
  --ease-spring: cubic-bezier(0.34, 1.56, 0.64, 1);
}

/* Disable animations for users who prefer reduced motion */
@media (prefers-reduced-motion: reduce) {
  *,
  *::before,
  *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}
```

---

## 1. RIPPLE EFFECT

### CSS + JavaScript

```css
.ripple-container {
  position: relative;
  overflow: hidden;
}

.ripple {
  position: absolute;
  border-radius: 50%;
  background: var(--accent);
  opacity: 0.3;
  transform: scale(0);
  pointer-events: none;
}

.ripple.animate {
  animation: ripple-animation var(--dur-slow) var(--ease-out) forwards;
}

@keyframes ripple-animation {
  to {
    transform: scale(4);
    opacity: 0;
  }
}

@media (prefers-reduced-motion: reduce) {
  .ripple.animate {
    animation: none;
    opacity: 0;
  }
}
```

```typescript
export function createRipple(event: MouseEvent, container: HTMLElement) {
  // Check for reduced motion preference
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    return;
  }
  
  const rect = container.getBoundingClientRect();
  const ripple = document.createElement('span');
  
  const diameter = Math.max(rect.width, rect.height);
  const radius = diameter / 2;
  
  ripple.style.width = ripple.style.height = `${diameter}px`;
  ripple.style.left = `${event.clientX - rect.left - radius}px`;
  ripple.style.top = `${event.clientY - rect.top - radius}px`;
  ripple.className = 'ripple';
  
  // Remove existing ripples
  const existingRipple = container.querySelector('.ripple');
  if (existingRipple) {
    existingRipple.remove();
  }
  
  container.appendChild(ripple);
  
  // Trigger animation
  requestAnimationFrame(() => {
    ripple.classList.add('animate');
  });
  
  // Clean up
  ripple.addEventListener('animationend', () => {
    ripple.remove();
  });
}

// Usage
document.querySelectorAll('.btn').forEach(btn => {
  btn.classList.add('ripple-container');
  btn.addEventListener('click', (e) => createRipple(e, btn));
});
```

### React Component

```tsx
import { useCallback, useRef } from 'react';

interface RippleProps {
  color?: string;
  duration?: number;
}

export function useRipple({ color = 'var(--accent)', duration = 400 }: RippleProps = {}) {
  const containerRef = useRef<HTMLElement>(null);
  
  const createRipple = useCallback((event: React.MouseEvent) => {
    const container = containerRef.current;
    if (!container) return;
    
    // Check for reduced motion
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      return;
    }
    
    const rect = container.getBoundingClientRect();
    const diameter = Math.max(rect.width, rect.height);
    const radius = diameter / 2;
    
    const ripple = document.createElement('span');
    ripple.style.cssText = `
      position: absolute;
      width: ${diameter}px;
      height: ${diameter}px;
      left: ${event.clientX - rect.left - radius}px;
      top: ${event.clientY - rect.top - radius}px;
      background: ${color};
      border-radius: 50%;
      opacity: 0.3;
      transform: scale(0);
      pointer-events: none;
      animation: ripple ${duration}ms ease-out forwards;
    `;
    
    container.appendChild(ripple);
    
    setTimeout(() => ripple.remove(), duration);
  }, [color, duration]);
  
  return { containerRef, createRipple };
}
```

---

## 2. SKELETON LOADERS

### Shimmer (Default)

```css
.skeleton {
  background: var(--surface);
  border-radius: var(--radius-sm);
  position: relative;
  overflow: hidden;
}

.skeleton::after {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(
    90deg,
    transparent,
    var(--skeleton-shine, rgba(255,255,255,0.3)),
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

/* Dark mode */
[data-theme="dark"] .skeleton {
  --skeleton-shine: rgba(255,255,255,0.1);
}

@media (prefers-reduced-motion: reduce) {
  .skeleton::after {
    animation: none;
    transform: none;
    background: var(--skeleton-shine);
  }
}
```

### Pulse

```css
.skeleton--pulse {
  animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

@media (prefers-reduced-motion: reduce) {
  .skeleton--pulse {
    animation: none;
  }
}
```

### Wave

```css
.skeleton--wave::after {
  background: linear-gradient(
    90deg,
    transparent 0%,
    var(--skeleton-shine) 20%,
    var(--skeleton-shine) 40%,
    transparent 60%
  );
  animation: wave 2s infinite;
}

@keyframes wave {
  0% { transform: translateX(-150%); }
  50%, 100% { transform: translateX(150%); }
}
```

### Skeleton Component

```tsx
interface SkeletonProps {
  variant?: 'shimmer' | 'pulse' | 'wave';
  width?: string | number;
  height?: string | number;
  borderRadius?: string;
  className?: string;
}

export function Skeleton({
  variant = 'shimmer',
  width = '100%',
  height = 20,
  borderRadius = 'var(--radius-sm)',
  className = ''
}: SkeletonProps) {
  return (
    <div
      className={`skeleton skeleton--${variant} ${className}`}
      style={{
        width: typeof width === 'number' ? `${width}px` : width,
        height: typeof height === 'number' ? `${height}px` : height,
        borderRadius
      }}
      aria-hidden="true"
    />
  );
}

// Usage examples
<Skeleton height={200} borderRadius="var(--radius-lg)" /> {/* Image */}
<Skeleton height={24} width="60%" />                      {/* Title */}
<Skeleton height={16} width="80%" />                      {/* Text line */}
<Skeleton height={16} width="40%" />                      {/* Short line */}
```

### Content Skeleton Pattern

```tsx
function ArticleCardSkeleton() {
  return (
    <div className="card">
      <Skeleton height={200} borderRadius="var(--radius-lg) var(--radius-lg) 0 0" />
      <div className="card__content">
        <Skeleton height={24} width="75%" className="mb-2" />
        <Skeleton height={16} width="100%" className="mb-1" />
        <Skeleton height={16} width="90%" className="mb-1" />
        <Skeleton height={16} width="60%" />
        <div className="card__footer mt-4">
          <Skeleton height={32} width={32} borderRadius="50%" />
          <Skeleton height={14} width="120px" />
        </div>
      </div>
    </div>
  );
}
```

---

## 3. TOAST NOTIFICATIONS

### CSS

```css
.toast-container {
  position: fixed;
  bottom: var(--space-6);
  right: var(--space-6);
  z-index: 1000;
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
}

.toast {
  display: flex;
  align-items: flex-start;
  gap: var(--space-3);
  padding: var(--space-4);
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-lg);
  min-width: 300px;
  max-width: 420px;
  transform: translateX(100%);
  opacity: 0;
  animation: toast-in var(--dur-base) var(--ease-out) forwards;
}

.toast.exiting {
  animation: toast-out var(--dur-micro) ease-in forwards;
}

@keyframes toast-in {
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

@keyframes toast-out {
  to {
    transform: translateX(100%);
    opacity: 0;
  }
}

.toast--success { border-left: 4px solid var(--color-success); }
.toast--error { border-left: 4px solid var(--color-error); }
.toast--warning { border-left: 4px solid var(--color-warning); }
.toast--info { border-left: 4px solid var(--accent); }

.toast__icon {
  flex-shrink: 0;
  width: var(--space-5);
  height: var(--space-5);
}

.toast__content {
  flex: 1;
  min-width: 0;
}

.toast__title {
  font-weight: 600;
  font-size: var(--text-sm);
  color: var(--text);
  margin-bottom: var(--space-1);
}

.toast__message {
  font-size: var(--text-sm);
  color: var(--muted);
}

.toast__close {
  flex-shrink: 0;
  padding: var(--space-1);
  background: none;
  border: none;
  color: var(--muted);
  cursor: pointer;
  border-radius: var(--radius-sm);
  transition: background var(--dur-micro);
}

.toast__close:hover {
  background: var(--bg-hover);
}

/* Progress bar for auto-dismiss */
.toast__progress {
  position: absolute;
  bottom: 0;
  left: 0;
  height: 3px;
  background: var(--accent);
  border-radius: 0 0 var(--radius-lg) var(--radius-lg);
  animation: progress linear forwards;
}

@keyframes progress {
  from { width: 100%; }
  to { width: 0%; }
}

@media (prefers-reduced-motion: reduce) {
  .toast {
    transform: none;
    animation: fade-in var(--dur-micro) forwards;
  }
  
  .toast.exiting {
    animation: fade-out var(--dur-micro) forwards;
  }
  
  @keyframes fade-in {
    from { opacity: 0; }
    to { opacity: 1; }
  }
  
  @keyframes fade-out {
    from { opacity: 1; }
    to { opacity: 0; }
  }
}
```

### Toast Manager

```typescript
type ToastType = 'success' | 'error' | 'warning' | 'info';

interface ToastOptions {
  type?: ToastType;
  title?: string;
  message: string;
  duration?: number;
  dismissible?: boolean;
}

class ToastManager {
  private container: HTMLElement;
  
  constructor() {
    this.container = document.createElement('div');
    this.container.className = 'toast-container';
    this.container.setAttribute('role', 'region');
    this.container.setAttribute('aria-label', 'Notifications');
    document.body.appendChild(this.container);
  }
  
  show(options: ToastOptions) {
    const {
      type = 'info',
      title,
      message,
      duration = 5000,
      dismissible = true
    } = options;
    
    const toast = document.createElement('div');
    toast.className = `toast toast--${type}`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'polite');
    
    const icons = {
      success: 'check-circle',
      error: 'x-circle',
      warning: 'alert-triangle',
      info: 'info'
    };
    
    toast.innerHTML = `
      <i data-lucide="${icons[type]}" class="toast__icon"></i>
      <div class="toast__content">
        ${title ? `<div class="toast__title">${title}</div>` : ''}
        <div class="toast__message">${message}</div>
      </div>
      ${dismissible ? `
        <button class="toast__close" aria-label="Dismiss">
          <i data-lucide="x"></i>
        </button>
      ` : ''}
      ${duration > 0 ? `
        <div class="toast__progress" style="animation-duration: ${duration}ms"></div>
      ` : ''}
    `;
    
    this.container.appendChild(toast);
    
    // Initialize Lucide icons
    if (window.lucide) {
      window.lucide.createIcons({ icons: {}, nameAttr: 'data-lucide' });
    }
    
    // Dismiss handler
    const dismiss = () => {
      toast.classList.add('exiting');
      toast.addEventListener('animationend', () => toast.remove());
    };
    
    if (dismissible) {
      toast.querySelector('.toast__close')?.addEventListener('click', dismiss);
    }
    
    if (duration > 0) {
      setTimeout(dismiss, duration);
    }
    
    return { dismiss };
  }
  
  success(message: string, title?: string) {
    return this.show({ type: 'success', message, title });
  }
  
  error(message: string, title?: string) {
    return this.show({ type: 'error', message, title });
  }
  
  warning(message: string, title?: string) {
    return this.show({ type: 'warning', message, title });
  }
  
  info(message: string, title?: string) {
    return this.show({ type: 'info', message, title });
  }
}

export const toast = new ToastManager();

// Usage
toast.success('Profile updated successfully');
toast.error('Failed to save changes', 'Error');
```

---

## 4. PULL-TO-REFRESH

```tsx
import { useState, useRef, useCallback } from 'react';

interface PullToRefreshProps {
  onRefresh: () => Promise<void>;
  children: React.ReactNode;
  threshold?: number;
}

export function PullToRefresh({ 
  onRefresh, 
  children, 
  threshold = 80 
}: PullToRefreshProps) {
  const [pullDistance, setPullDistance] = useState(0);
  const [isRefreshing, setIsRefreshing] = useState(false);
  const containerRef = useRef<HTMLDivElement>(null);
  const startY = useRef(0);
  
  const handleTouchStart = useCallback((e: React.TouchEvent) => {
    if (containerRef.current?.scrollTop === 0) {
      startY.current = e.touches[0].clientY;
    }
  }, []);
  
  const handleTouchMove = useCallback((e: React.TouchEvent) => {
    if (isRefreshing || startY.current === 0) return;
    
    const currentY = e.touches[0].clientY;
    const distance = Math.max(0, currentY - startY.current);
    
    // Apply resistance
    const pullAmount = Math.min(distance * 0.5, threshold * 1.5);
    setPullDistance(pullAmount);
  }, [isRefreshing, threshold]);
  
  const handleTouchEnd = useCallback(async () => {
    if (pullDistance >= threshold && !isRefreshing) {
      setIsRefreshing(true);
      setPullDistance(threshold);
      
      try {
        await onRefresh();
      } finally {
        setIsRefreshing(false);
        setPullDistance(0);
      }
    } else {
      setPullDistance(0);
    }
    
    startY.current = 0;
  }, [pullDistance, threshold, isRefreshing, onRefresh]);
  
  const rotation = Math.min((pullDistance / threshold) * 360, 360);
  const opacity = Math.min(pullDistance / threshold, 1);
  
  return (
    <div
      ref={containerRef}
      className="pull-to-refresh"
      onTouchStart={handleTouchStart}
      onTouchMove={handleTouchMove}
      onTouchEnd={handleTouchEnd}
      style={{
        transform: `translateY(${pullDistance}px)`,
        transition: pullDistance === 0 ? 'transform 0.2s' : 'none'
      }}
    >
      <div 
        className="pull-to-refresh__indicator"
        style={{
          opacity,
          transform: `translateY(-100%) rotate(${rotation}deg)`
        }}
        aria-hidden={!isRefreshing}
      >
        <i data-lucide={isRefreshing ? 'loader-2' : 'arrow-down'} 
           className={isRefreshing ? 'spin' : ''} />
      </div>
      {children}
    </div>
  );
}
```

---

## 5. DRAG-TO-REORDER

```tsx
import { useState, useCallback } from 'react';

interface DragItem {
  id: string;
  content: React.ReactNode;
}

interface DragListProps {
  items: DragItem[];
  onReorder: (items: DragItem[]) => void;
}

export function DragList({ items, onReorder }: DragListProps) {
  const [draggedId, setDraggedId] = useState<string | null>(null);
  const [dragOverId, setDragOverId] = useState<string | null>(null);
  
  const handleDragStart = useCallback((id: string) => {
    setDraggedId(id);
  }, []);
  
  const handleDragOver = useCallback((e: React.DragEvent, id: string) => {
    e.preventDefault();
    if (id !== draggedId) {
      setDragOverId(id);
    }
  }, [draggedId]);
  
  const handleDragEnd = useCallback(() => {
    if (draggedId && dragOverId && draggedId !== dragOverId) {
      const newItems = [...items];
      const draggedIndex = newItems.findIndex(i => i.id === draggedId);
      const dragOverIndex = newItems.findIndex(i => i.id === dragOverId);
      
      const [removed] = newItems.splice(draggedIndex, 1);
      newItems.splice(dragOverIndex, 0, removed);
      
      onReorder(newItems);
    }
    
    setDraggedId(null);
    setDragOverId(null);
  }, [draggedId, dragOverId, items, onReorder]);
  
  return (
    <ul className="drag-list" role="listbox" aria-label="Reorderable list">
      {items.map(item => (
        <li
          key={item.id}
          draggable
          onDragStart={() => handleDragStart(item.id)}
          onDragOver={(e) => handleDragOver(e, item.id)}
          onDragEnd={handleDragEnd}
          className={`
            drag-list__item
            ${draggedId === item.id ? 'dragging' : ''}
            ${dragOverId === item.id ? 'drag-over' : ''}
          `}
          role="option"
          aria-selected={draggedId === item.id}
        >
          <i data-lucide="grip-vertical" className="drag-handle" aria-hidden="true" />
          {item.content}
        </li>
      ))}
    </ul>
  );
}
```

```css
.drag-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.drag-list__item {
  display: flex;
  align-items: center;
  gap: var(--space-3);
  padding: var(--space-3) var(--space-4);
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
  margin-bottom: var(--space-2);
  cursor: grab;
  transition: transform var(--dur-micro), box-shadow var(--dur-micro);
}

.drag-list__item:active {
  cursor: grabbing;
}

.drag-list__item.dragging {
  opacity: 0.5;
  transform: scale(1.02);
  box-shadow: var(--shadow-lg);
}

.drag-list__item.drag-over {
  border-color: var(--accent);
  background: var(--accent-soft);
}

.drag-handle {
  color: var(--muted);
  flex-shrink: 0;
}

@media (prefers-reduced-motion: reduce) {
  .drag-list__item {
    transition: none;
  }
  
  .drag-list__item.dragging {
    transform: none;
  }
}
```

---

## 6. BUTTON FEEDBACK

### Click Feedback

```css
.btn {
  transition: transform var(--dur-micro), box-shadow var(--dur-micro);
}

.btn:active {
  transform: scale(0.98);
}

@media (prefers-reduced-motion: reduce) {
  .btn:active {
    transform: none;
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.15);
  }
}
```

### Loading State

```css
.btn--loading {
  position: relative;
  color: transparent;
  pointer-events: none;
}

.btn--loading::after {
  content: '';
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: inherit;
  border-radius: inherit;
}

.btn--loading::before {
  content: '';
  position: absolute;
  width: 18px;
  height: 18px;
  border: 2px solid currentColor;
  border-right-color: transparent;
  border-radius: 50%;
  animation: spin 0.75s linear infinite;
  color: var(--text-on-accent);
  z-index: 1;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

@media (prefers-reduced-motion: reduce) {
  .btn--loading::before {
    animation: none;
    border-color: currentColor;
    border-style: dotted;
  }
}
```

---

## CHECKLIST

Before shipping micro-interactions:

- [ ] `prefers-reduced-motion` respected
- [ ] Uses design tokens for timing/easing
- [ ] No `transition: all`
- [ ] Only animates transform/opacity (GPU)
- [ ] Feedback is immediate (<100ms)
- [ ] Animations don't block interaction
- [ ] Works on touch devices
- [ ] ARIA attributes for state changes
- [ ] Tested on low-end devices
