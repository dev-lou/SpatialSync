---
description: Animated number counter for KPIs and stats. Scroll-triggered with Intersection Observer, easing per design language, formatting options, respects prefers-reduced-motion.
---

# UI/UX Animated Number Counter

> Scroll-triggered animated counters for statistics and KPIs.
> Token-aware, accessible, respects reduced motion.

---

## OVERVIEW

This skill covers:
1. Scroll-triggered counter animation
2. Easing curves per design language
3. Number formatting (commas, decimals, prefix/suffix)
4. Accessibility considerations
5. Multiple counter variants

---

## BASIC IMPLEMENTATION

### HTML Structure

```html
<div class="stats-grid">
  <div class="stat">
    <span 
      class="stat__number" 
      data-count-to="10000"
      data-count-suffix="+"
      data-count-duration="2000"
    >0</span>
    <span class="stat__label">Active Users</span>
  </div>
  
  <div class="stat">
    <span 
      class="stat__number" 
      data-count-to="99.9"
      data-count-suffix="%"
      data-count-decimals="1"
    >0</span>
    <span class="stat__label">Uptime</span>
  </div>
  
  <div class="stat">
    <span 
      class="stat__number" 
      data-count-to="500"
      data-count-prefix="$"
      data-count-suffix="M"
    >0</span>
    <span class="stat__label">Revenue</span>
  </div>
</div>
```

### CSS

```css
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: var(--space-8);
  text-align: center;
}

.stat {
  padding: var(--space-6);
}

.stat__number {
  display: block;
  font-size: var(--text-5xl);
  font-weight: 700;
  color: var(--text);
  line-height: 1;
  margin-bottom: var(--space-2);
  
  /* Tabular numbers for consistent width during animation */
  font-variant-numeric: tabular-nums;
}

.stat__label {
  font-size: var(--text-sm);
  color: var(--muted);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

/* Design language variations */
[data-language="luxury"] .stat__number {
  font-family: var(--font-display);
  font-weight: 300;
}

[data-language="expressive"] .stat__number {
  background: linear-gradient(90deg, var(--accent), var(--accent-secondary));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

/* Reduced motion: no animation, just show final value */
@media (prefers-reduced-motion: reduce) {
  .stat__number[data-counted="true"] {
    transition: none;
  }
}
```

---

## JAVASCRIPT IMPLEMENTATION

### CountUp Function

```typescript
interface CountUpOptions {
  duration?: number;
  decimals?: number;
  prefix?: string;
  suffix?: string;
  separator?: string;
  easing?: 'linear' | 'easeOut' | 'easeInOut' | 'spring';
  onComplete?: () => void;
}

function countUp(
  element: HTMLElement,
  endValue: number,
  options: CountUpOptions = {}
): void {
  const {
    duration = 2000,
    decimals = 0,
    prefix = '',
    suffix = '',
    separator = ',',
    easing = 'easeOut',
    onComplete
  } = options;
  
  // Check for reduced motion preference
  const prefersReducedMotion = window.matchMedia(
    '(prefers-reduced-motion: reduce)'
  ).matches;
  
  if (prefersReducedMotion) {
    // Show final value immediately
    element.textContent = formatNumber(endValue, decimals, prefix, suffix, separator);
    element.setAttribute('data-counted', 'true');
    onComplete?.();
    return;
  }
  
  const startTime = performance.now();
  const startValue = 0;
  
  function formatNumber(
    value: number,
    decimals: number,
    prefix: string,
    suffix: string,
    separator: string
  ): string {
    const fixed = value.toFixed(decimals);
    const [integer, decimal] = fixed.split('.');
    const formatted = integer.replace(/\B(?=(\d{3})+(?!\d))/g, separator);
    return `${prefix}${formatted}${decimal ? '.' + decimal : ''}${suffix}`;
  }
  
  function getEasing(t: number): number {
    switch (easing) {
      case 'linear':
        return t;
      case 'easeOut':
        return 1 - Math.pow(1 - t, 3);
      case 'easeInOut':
        return t < 0.5 
          ? 4 * t * t * t 
          : 1 - Math.pow(-2 * t + 2, 3) / 2;
      case 'spring':
        return 1 - Math.cos(t * Math.PI / 2);
      default:
        return 1 - Math.pow(1 - t, 3);
    }
  }
  
  function animate(currentTime: number) {
    const elapsed = currentTime - startTime;
    const progress = Math.min(elapsed / duration, 1);
    const easedProgress = getEasing(progress);
    const currentValue = startValue + (endValue - startValue) * easedProgress;
    
    element.textContent = formatNumber(
      currentValue,
      decimals,
      prefix,
      suffix,
      separator
    );
    
    if (progress < 1) {
      requestAnimationFrame(animate);
    } else {
      element.setAttribute('data-counted', 'true');
      onComplete?.();
    }
  }
  
  requestAnimationFrame(animate);
}
```

### Intersection Observer Setup

```typescript
function initCounters(): void {
  const counters = document.querySelectorAll('[data-count-to]');
  
  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          const element = entry.target as HTMLElement;
          
          // Only animate once
          if (element.getAttribute('data-counted') === 'true') return;
          
          const endValue = parseFloat(element.dataset.countTo || '0');
          const duration = parseInt(element.dataset.countDuration || '2000', 10);
          const decimals = parseInt(element.dataset.countDecimals || '0', 10);
          const prefix = element.dataset.countPrefix || '';
          const suffix = element.dataset.countSuffix || '';
          const easing = element.dataset.countEasing as CountUpOptions['easing'] || 'easeOut';
          
          countUp(element, endValue, {
            duration,
            decimals,
            prefix,
            suffix,
            easing
          });
          
          // Stop observing after animation starts
          observer.unobserve(element);
        }
      });
    },
    {
      threshold: 0.5,
      rootMargin: '0px 0px -10% 0px'
    }
  );
  
  counters.forEach((counter) => observer.observe(counter));
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', initCounters);
```

---

## REACT COMPONENT

```tsx
import { useEffect, useRef, useState, useCallback } from 'react';

interface CountUpProps {
  end: number;
  duration?: number;
  decimals?: number;
  prefix?: string;
  suffix?: string;
  separator?: string;
  easing?: 'linear' | 'easeOut' | 'easeInOut' | 'spring';
  className?: string;
  onComplete?: () => void;
}

export function CountUp({
  end,
  duration = 2000,
  decimals = 0,
  prefix = '',
  suffix = '',
  separator = ',',
  easing = 'easeOut',
  className = '',
  onComplete
}: CountUpProps) {
  const [count, setCount] = useState(0);
  const [hasAnimated, setHasAnimated] = useState(false);
  const ref = useRef<HTMLSpanElement>(null);
  
  const formatNumber = useCallback((value: number) => {
    const fixed = value.toFixed(decimals);
    const [integer, decimal] = fixed.split('.');
    const formatted = integer.replace(/\B(?=(\d{3})+(?!\d))/g, separator);
    return `${prefix}${formatted}${decimal ? '.' + decimal : ''}${suffix}`;
  }, [decimals, prefix, suffix, separator]);
  
  useEffect(() => {
    if (hasAnimated || !ref.current) return;
    
    const prefersReducedMotion = window.matchMedia(
      '(prefers-reduced-motion: reduce)'
    ).matches;
    
    const observer = new IntersectionObserver(
      ([entry]) => {
        if (entry.isIntersecting && !hasAnimated) {
          setHasAnimated(true);
          
          if (prefersReducedMotion) {
            setCount(end);
            onComplete?.();
            return;
          }
          
          const startTime = performance.now();
          
          function getEasing(t: number): number {
            switch (easing) {
              case 'linear': return t;
              case 'easeOut': return 1 - Math.pow(1 - t, 3);
              case 'easeInOut': return t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2;
              case 'spring': return 1 - Math.cos(t * Math.PI / 2);
              default: return 1 - Math.pow(1 - t, 3);
            }
          }
          
          function animate(currentTime: number) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const easedProgress = getEasing(progress);
            const currentValue = end * easedProgress;
            
            setCount(currentValue);
            
            if (progress < 1) {
              requestAnimationFrame(animate);
            } else {
              onComplete?.();
            }
          }
          
          requestAnimationFrame(animate);
        }
      },
      { threshold: 0.5 }
    );
    
    observer.observe(ref.current);
    
    return () => observer.disconnect();
  }, [end, duration, easing, hasAnimated, onComplete]);
  
  return (
    <span 
      ref={ref} 
      className={`stat__number ${className}`}
      aria-label={formatNumber(end)}
    >
      {formatNumber(count)}
    </span>
  );
}
```

### Usage

```tsx
function StatsSection() {
  return (
    <section className="stats-grid">
      <div className="stat">
        <CountUp 
          end={10000} 
          suffix="+" 
          duration={2000} 
        />
        <span className="stat__label">Active Users</span>
      </div>
      
      <div className="stat">
        <CountUp 
          end={99.9} 
          suffix="%" 
          decimals={1}
          easing="easeInOut"
        />
        <span className="stat__label">Uptime</span>
      </div>
      
      <div className="stat">
        <CountUp 
          end={500} 
          prefix="$" 
          suffix="M"
          easing="spring"
        />
        <span className="stat__label">Revenue</span>
      </div>
    </section>
  );
}
```

---

## EASING BY DESIGN LANGUAGE

| Language | Easing | Duration | Notes |
|----------|--------|----------|-------|
| Luxury | `easeInOut` | 3000ms | Slow, elegant |
| Premium Modern | `easeOut` | 2000ms | Polished, confident |
| Minimalist | `easeOut` | 1500ms | Quick, precise |
| Expressive | `spring` | 1800ms | Energetic, bouncy |
| Editorial | `easeOut` | 2000ms | Standard |
| Warm & Human | `easeInOut` | 2200ms | Gentle |
| Technical | `linear` | 1000ms | Functional, fast |

```javascript
const easingByLanguage = {
  luxury: { easing: 'easeInOut', duration: 3000 },
  'premium-modern': { easing: 'easeOut', duration: 2000 },
  minimalist: { easing: 'easeOut', duration: 1500 },
  expressive: { easing: 'spring', duration: 1800 },
  editorial: { easing: 'easeOut', duration: 2000 },
  'warm-human': { easing: 'easeInOut', duration: 2200 },
  technical: { easing: 'linear', duration: 1000 },
};
```

---

## FORMATTING OPTIONS

### Number Formats

```typescript
// Basic
countUp(el, 1250)                     // "1,250"

// With prefix
countUp(el, 500, { prefix: '$' })     // "$500"

// With suffix
countUp(el, 10000, { suffix: '+' })   // "10,000+"

// With decimals
countUp(el, 99.9, { decimals: 1 })    // "99.9"

// Currency
countUp(el, 1234.56, { 
  prefix: '$', 
  decimals: 2 
})                                     // "$1,234.56"

// Percentage
countUp(el, 85.5, { 
  suffix: '%', 
  decimals: 1 
})                                     // "85.5%"

// Custom separator
countUp(el, 1000000, { 
  separator: ' ' 
})                                     // "1 000 000"

// European format
countUp(el, 1234.56, { 
  separator: '.', 
  decimals: 2 
})                                     // "1.234,56" (needs decimal handling)
```

---

## ACCESSIBILITY

### ARIA Labels

```html
<span 
  class="stat__number"
  data-count-to="10000"
  aria-label="10,000 plus active users"
>
  0
</span>
```

### Screen Reader Announcement

```typescript
function countUp(element, endValue, options) {
  // ... animation code ...
  
  // On complete, announce to screen readers
  const announcement = document.createElement('div');
  announcement.setAttribute('role', 'status');
  announcement.setAttribute('aria-live', 'polite');
  announcement.className = 'sr-only';
  announcement.textContent = `${formatNumber(endValue)} ${element.nextElementSibling?.textContent || ''}`;
  document.body.appendChild(announcement);
  
  setTimeout(() => announcement.remove(), 1000);
}
```

### Reduced Motion Handling

```javascript
const prefersReducedMotion = window.matchMedia(
  '(prefers-reduced-motion: reduce)'
).matches;

if (prefersReducedMotion) {
  // Show final value immediately, no animation
  element.textContent = formatNumber(endValue);
  return;
}
```

---

## VARIANTS

### Counter with Icon

```html
<div class="stat stat--with-icon">
  <i data-lucide="users" class="stat__icon"></i>
  <span class="stat__number" data-count-to="10000">0</span>
  <span class="stat__label">Active Users</span>
</div>
```

### Counter Card

```html
<div class="stat-card">
  <div class="stat-card__header">
    <span class="stat-card__label">Monthly Revenue</span>
    <span class="stat-card__trend stat-card__trend--up">
      <i data-lucide="trending-up"></i>
      12%
    </span>
  </div>
  <span class="stat-card__number" data-count-to="125000" data-count-prefix="$">$0</span>
</div>
```

### Progress Counter

```html
<div class="progress-stat">
  <div class="progress-stat__header">
    <span class="progress-stat__label">Storage Used</span>
    <span class="progress-stat__value">
      <span data-count-to="75" data-count-suffix="%">0%</span>
    </span>
  </div>
  <div class="progress-stat__bar">
    <div class="progress-stat__fill" style="width: 75%"></div>
  </div>
</div>
```

---

## CHECKLIST

- [ ] Uses Intersection Observer for scroll trigger
- [ ] Respects `prefers-reduced-motion`
- [ ] Tabular nums for consistent width
- [ ] Easing matches design language
- [ ] Number formatting (commas, decimals)
- [ ] Prefix/suffix support
- [ ] ARIA label for accessibility
- [ ] Only animates once per element
- [ ] Works without JavaScript (shows final value)
- [ ] Mobile-friendly (adjusts threshold)
