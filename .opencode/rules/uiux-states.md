# UI/UX Component States 2026

> Every UI component has states. This file defines ALL 10 states every component
> must handle — from Figma variables to CSS to JS — per design language.
> Missing states = broken UX. This is what separates AI slop from real products.

---

## THE 10 STATES EVERY COMPONENT MUST HANDLE

```
1. Default    — resting state
2. Hover      — mouse/pointer over
3. Focus      — keyboard/tab navigation
4. Active     — being pressed / in-progress click
5. Loading    — async action pending
6. Disabled   — not interactable
7. Error      — validation failed / system error
8. Success    — action confirmed / validated
9. Empty      — no data yet (tables, feeds, search)
10. Busy      — container is updating (live feed, real-time data)
```

Optional (context-dependent):
```
11. Selected  — chosen from a list
12. Dragging  — being dragged (sortable lists)
13. Skeleton  — pre-data placeholder (see uiux-motion.md)
```

---

## STATE 10 — BUSY (Live Updating Containers)

**Rule**: When a container or section is actively updating (live feeds, real-time dashboards, polling data), use `aria-busy="true"` and provide visual feedback with reduced opacity and a spinner overlay.

### When to Use Busy State

- Live feed is fetching new items
- Dashboard widget is refreshing data
- Chat is receiving streaming response
- Table is filtering/sorting large datasets
- Any container awaiting real-time update

### HTML Structure

```html
<!-- Busy container with aria-busy -->
<div class="live-feed" id="live-feed" aria-busy="true" aria-live="polite">
  <div class="live-feed__spinner">
    <span class="spinner"></span>
    <span class="sr-only">Updating feed...</span>
  </div>
  <ul class="live-feed__items">
    <!-- Feed items -->
  </ul>
</div>
```

### CSS Implementation

```css
/* Busy state — reduced opacity + spinner overlay */
[aria-busy="true"] {
  position: relative;
  pointer-events: none;
}

[aria-busy="true"]::before {
  content: '';
  position: absolute;
  inset: 0;
  background: color-mix(in srgb, var(--bg) 60%, transparent);
  z-index: 10;
  border-radius: inherit;
}

/* Spinner overlay for busy containers */
.live-feed__spinner {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  z-index: 11;
  display: none;
}

[aria-busy="true"] .live-feed__spinner {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: var(--space-2);
}

/* Busy state visual feedback */
[aria-busy="true"] > *:not(.live-feed__spinner) {
  opacity: 0.4;
  filter: blur(1px);
  transition: opacity var(--transition-base), filter var(--transition-base);
}

/* Spinner animation */
.spinner {
  width: 24px;
  height: 24px;
  border: 2px solid var(--border);
  border-top-color: var(--accent);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Respect reduced motion */
@media (prefers-reduced-motion: reduce) {
  .spinner {
    animation: none;
    border-top-color: var(--accent);
    border-right-color: var(--accent);
  }
  
  [aria-busy="true"] > *:not(.live-feed__spinner) {
    filter: none;
  }
}

/* Per-design-language busy styles */
[data-lang="luxury"] [aria-busy="true"]::before {
  background: color-mix(in srgb, var(--bg) 70%, transparent);
}

[data-lang="technical"] [aria-busy="true"]::before {
  background: color-mix(in srgb, var(--bg) 80%, transparent);
}

[data-lang="technical"] .spinner {
  width: 16px;
  height: 16px;
  border-width: 1.5px;
}
```

### JavaScript Implementation

```javascript
// Busy state manager for live containers
class BusyState {
  constructor(containerId) {
    this.container = document.getElementById(containerId);
    this.isBusy = false;
  }

  start(message = 'Updating...') {
    if (this.isBusy) return;
    
    this.isBusy = true;
    this.container.setAttribute('aria-busy', 'true');
    
    // Update screen reader text
    const srText = this.container.querySelector('.sr-only');
    if (srText) {
      srText.textContent = message;
    }
  }

  end() {
    this.isBusy = false;
    this.container.setAttribute('aria-busy', 'false');
  }
}

// Usage with live feed
const feedBusy = new BusyState('live-feed');

async function refreshFeed() {
  feedBusy.start('Loading new posts...');
  
  try {
    const data = await fetch('/api/feed').then(r => r.json());
    renderFeedItems(data);
  } finally {
    feedBusy.end();
  }
}
```

### Live Feed Example

```html
<!-- Complete live feed with busy state -->
<section class="live-feed-container">
  <header class="live-feed__header">
    <h2>Activity Feed</h2>
    <span class="live-indicator" aria-label="Live updates active">
      <span class="live-indicator__dot"></span>
      Live
    </span>
  </header>
  
  <div class="live-feed" id="activity-feed" aria-busy="false" aria-live="polite">
    <div class="live-feed__spinner">
      <span class="spinner"></span>
      <span class="sr-only">Loading activities...</span>
    </div>
    
    <ul class="live-feed__items" role="feed">
      <li class="feed-item" role="article">
        <img src="/avatars/user1.jpg" alt="" class="feed-item__avatar" width="40" height="40">
        <div class="feed-item__content">
          <strong>Jane Doe</strong> commented on your post
          <time datetime="2026-04-06T12:30:00Z">2 min ago</time>
        </div>
      </li>
      <!-- More items -->
    </ul>
  </div>
</section>
```

```css
/* Live indicator */
.live-indicator {
  display: inline-flex;
  align-items: center;
  gap: var(--space-2);
  font-size: var(--text-xs);
  font-weight: 600;
  color: var(--color-success);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.live-indicator__dot {
  width: 8px;
  height: 8px;
  background: var(--color-success);
  border-radius: 50%;
  animation: pulse-live 2s infinite;
}

@keyframes pulse-live {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.5; transform: scale(1.2); }
}

@media (prefers-reduced-motion: reduce) {
  .live-indicator__dot {
    animation: none;
  }
}
```

---

## TOUCH-SPECIFIC STATES (MOBILE)

**Rule**: Mobile interactions are not just "clicks." They involve physical gestures and tactile feedback. Every mobile-first component must consider touch-specific behavior.

### 1. Long-Press Detection
Standard `click` events fire on release. Long-press (hold) triggers secondary actions (context menus, multi-select).

```javascript
// Long-press implementation
let pressTimer;

function startPress(e, callback) {
  // Prevent ghost clicks and context menus
  if (e.type === 'touchstart') e.preventDefault(); 
  
  pressTimer = setTimeout(() => {
    callback();
    triggerHaptic(); // Feedback when long-press is recognized
  }, 600); // 600ms is the standard "hold" threshold
}

function cancelPress() {
  clearTimeout(pressTimer);
}

// Usage
const btn = document.querySelector('.btn-mobile');
btn.addEventListener('touchstart', (e) => startPress(e, () => showContextMenu()));
btn.addEventListener('touchend', cancelPress);
btn.addEventListener('touchmove', cancelPress);
```

### 2. Haptic Feedback (Vibration API)
Use haptics to confirm actions. **Rule**: Use sparingly. Overuse drains battery and annoys users.

```javascript
function triggerHaptic(pattern = 10) {
  if ('vibrate' in navigator) {
    // 10ms = subtle "tick" (Luxury/Premium)
    // [10, 30, 10] = double tap (Success)
    // [50, 50, 50] = heavy (Error/Warning)
    navigator.vibrate(pattern);
  }
}
```

### 3. Touch Feedback & :active
The CSS `:active` pseudo-class provides immediate visual feedback. 

**Warning**: Browsers often have a 300ms delay on touch events to distinguish between a tap and a scroll. 
**Fix**: Use `touch-action: manipulation` to disable double-tap-to-zoom and remove the delay.

```css
.btn-mobile {
  touch-action: manipulation;
  -webkit-tap-highlight-color: transparent; /* Remove default gray box on iOS */
  transition: transform 0.1s var(--ease-out);
}

.btn-mobile:active {
  transform: scale(0.95);
  background: var(--surface-up);
}
```

### 4. Haptic Long-Press Button with Ripple

```html
<button class="btn-haptic" id="haptic-btn">
  Hold for Secret
  <span class="ripple"></span>
</button>
```

```css
.btn-haptic {
  position: relative; overflow: hidden;
  padding: var(--space-4) var(--space-8);
  background: var(--accent); color: white;
  border-radius: var(--radius-md); border: none;
  touch-action: manipulation;
}

.ripple {
  position: absolute; border-radius: 50%;
  background: rgba(255, 255, 255, 0.4);
  transform: scale(0); animation: ripple-effect 0.6s linear;
  pointer-events: none;
}

@keyframes ripple-effect {
  to { transform: scale(4); opacity: 0; }
}
```

```javascript
const hBtn = document.getElementById('haptic-btn');

hBtn.addEventListener('touchstart', (e) => {
  // Create ripple at touch point
  const rect = hBtn.getBoundingClientRect();
  const touch = e.touches[0];
  const x = touch.clientX - rect.left;
  const y = touch.clientY - rect.top;
  
  const ripple = hBtn.querySelector('.ripple') || document.createElement('span');
  ripple.className = 'ripple';
  ripple.style.left = `${x}px`;
  ripple.style.top = `${y}px`;
  hBtn.appendChild(ripple);
  
  // Start long-press timer
  startPress(e, () => {
    hBtn.style.background = 'var(--color-success)';
    showToast({ title: 'Secret Unlocked!', type: 'success' });
  });
});

hBtn.addEventListener('touchend', cancelPress);
```

---

## STATE TOKEN SYSTEM

```css
/* Add to tokens.css alongside brand tokens */
:root {
  /* Status semantic tokens */
  --color-success:        #1A7F37;
  --color-success-bg:     #DCFCE7;
  --color-success-border: #86EFAC;

  --color-error:          #CF222E;
  --color-error-bg:       #FEE2E2;
  --color-error-border:   #FCA5A5;

  --color-warning:        #9A6700;
  --color-warning-bg:     #FEF9C3;
  --color-warning-border: #FDE047;

  --color-info:           #0969DA;
  --color-info-bg:        #DBEAFE;
  --color-info-border:    #93C5FD;

  /* Dark mode overrides */
  [data-theme="dark"] {
    --color-success:        #3FB950;
    --color-success-bg:     #0D2818;
    --color-success-border: #1A7F37;

    --color-error:          #F85149;
    --color-error-bg:       #2D0A0A;
    --color-error-border:   #CF222E;

    --color-warning:        #D29922;
    --color-warning-bg:     #2D2000;
    --color-warning-border: #9A6700;

    --color-info:           #58A6FF;
    --color-info-bg:        #0D1B2D;
    --color-info-border:    #0969DA;
  }
}
```

---

## BUTTON — All 8 States

```css
/* Base */
.btn { /* see uiux-interactive.md for full system */ }

/* 1. Default — defined in interactive file */
/* 2. Hover */
.btn:hover:not(:disabled):not(.btn--loading) {
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}
/* 3. Focus */
.btn:focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 3px;
}
/* 4. Active */
.btn:active:not(:disabled) {
  transform: translateY(0) scale(0.97);
  transition-duration: 50ms;
  box-shadow: none;
}
/* 5. Loading — see uiux-motion.md for .btn--loading */
/* 6. Disabled */
.btn:disabled, .btn[aria-disabled="true"] {
  opacity: 0.38;
  cursor: not-allowed;
  pointer-events: none;
  transform: none;
  box-shadow: none;
}
/* 7. Error state (destructive action confirmed) */
.btn--error {
  background: var(--color-error);
  border-color: var(--color-error);
  color: white;
}
/* 8. Success state (action completed) */
.btn--success {
  background: var(--color-success);
  border-color: var(--color-success);
  color: white;
  pointer-events: none; /* prevent double-click */
}
```

```javascript
// Success flash pattern — shows ✓ then resets
async function submitWithFeedback(btn, action) {
  btn.classList.add('btn--loading');
  btn.setAttribute('aria-busy', 'true');
  try {
    await action();
    btn.classList.remove('btn--loading');
    btn.classList.add('btn--success');
    btn.textContent = '✓ Done';
    setTimeout(() => {
      btn.classList.remove('btn--success');
      btn.textContent = btn.dataset.label || 'Submit';
    }, 2000);
  } catch (err) {
    btn.classList.remove('btn--loading');
    btn.classList.add('btn--error');
    setTimeout(() => btn.classList.remove('btn--error'), 2000);
    swal.error('Something went wrong', err.message);
  } finally {
    btn.removeAttribute('aria-busy');
  }
}
```

---

## FORM INPUT — All 8 States

```html
<div class="field" data-state="default">
  <label class="field__label" for="email">Email address</label>
  <div class="field__wrap">
    <input class="field__input" type="email" id="email"
      placeholder=" " autocomplete="email">
    <span class="field__icon-right"></span>
  </div>
  <span class="field__helper">We'll never share your email.</span>
  <span class="field__error"  role="alert" aria-live="polite"></span>
  <span class="field__success" aria-live="polite"></span>
</div>
```

```css
/* Shared field foundation */
.field { display: flex; flex-direction: column; gap: var(--space-1); }
.field__wrap { position: relative; }
.field__input {
  width: 100%; padding: 12px 16px;
  background: var(--surface); color: var(--text);
  border: 1.5px solid var(--border);
  border-radius: var(--radius-md);
  font-size: var(--text-base); font-family: var(--font-body);
  transition:
    border-color var(--transition-micro),
    box-shadow   var(--transition-micro),
    background   var(--transition-micro);
  outline: none;
}

/* 1. Default — above */
/* 2. Hover */
.field__input:hover:not(:focus):not(:disabled) {
  border-color: var(--text-2);
}
/* 3. Focus */
.field__input:focus {
  border-color: var(--accent);
  box-shadow: 0 0 0 3px color-mix(in srgb, var(--accent) 20%, transparent);
}
/* 4. Active — same as focus for inputs */
/* 5. Loading — show spinner in right icon slot */
.field[data-state="loading"] .field__input { padding-right: 44px; }
.field[data-state="loading"] .field__icon-right::after {
  content: '';
  position: absolute right: 14px; top: 50%; transform: translateY(-50%);
  width: 16px; height: 16px;
  border: 2px solid var(--border); border-top-color: var(--accent);
  border-radius: 50%;
  animation: btn-spin 0.7s linear infinite;
}
/* 6. Disabled */
.field__input:disabled {
  background: var(--surface-up); opacity: 0.5; cursor: not-allowed;
}
.field__input:disabled:hover { border-color: var(--border); }
/* 7. Error */
.field[data-state="error"] .field__input,
.field__input:invalid:not(:placeholder-shown) {
  border-color: var(--color-error);
  box-shadow: 0 0 0 3px color-mix(in srgb, var(--color-error) 15%, transparent);
}
.field[data-state="error"] .field__label { color: var(--color-error); }
.field__error { font-size: var(--text-xs); color: var(--color-error); display: none; padding-left: 2px; }
.field[data-state="error"] .field__error { display: block; }
/* 8. Success */
.field[data-state="success"] .field__input {
  border-color: var(--color-success);
  box-shadow: 0 0 0 3px color-mix(in srgb, var(--color-success) 15%, transparent);
  padding-right: 44px;
}
.field__success { font-size: var(--text-xs); color: var(--color-success); display: none; padding-left: 2px; }
.field[data-state="success"] .field__success { display: block; }

/* Helper text */
.field__helper { font-size: var(--text-xs); color: var(--muted); padding-left: 4px; }
.field[data-state="error"]   .field__helper { display: none; }
.field[data-state="success"] .field__helper { display: none; }
```

```javascript
// Field state manager
function setFieldState(field, state, message = '') {
  const input   = field.querySelector('.field__input');
  const errorEl = field.querySelector('.field__error');
  const successEl = field.querySelector('.field__success');

  field.dataset.state = state;

  if (state === 'error' && errorEl)   errorEl.textContent   = message;
  if (state === 'success' && successEl) successEl.textContent = message || '✓ Looks good';

  // Shake animation on error
  if (state === 'error') {
    input.animate([
      { transform: 'translateX(0)' },
      { transform: 'translateX(-6px)' },
      { transform: 'translateX(6px)' },
      { transform: 'translateX(-4px)' },
      { transform: 'translateX(0)' },
    ], { duration: 300, easing: 'ease-out' });
  }
}
// Usage: setFieldState(field, 'error', 'Email already in use');
// Usage: setFieldState(field, 'success');
// Usage: setFieldState(field, 'loading');
// Usage: setFieldState(field, 'default');
```

---

## CARD — All States

```css
/* 1. Default */
.card {
  background: var(--surface); border: 1px solid var(--border);
  border-radius: var(--radius-lg); overflow: hidden;
  transition: transform var(--dur-base) var(--ease-spring),
              box-shadow var(--dur-base) var(--ease-enter),
              border-color var(--dur-micro) var(--ease-enter);
}
/* 2. Hover */
.card:hover:not(.card--disabled):not(.card--loading) {
  transform: translateY(-6px) scale(1.01);
  box-shadow: var(--shadow-xl);
}
/* 3. Focus (when card is focusable/clickable) */
.card:focus-visible {
  outline: 2px solid var(--accent); outline-offset: 3px;
}
/* 4. Active */
.card:active { transform: translateY(0) scale(0.99); transition-duration: 50ms; }

/* 5. Loading skeleton — replace card body with skeletons (see uiux-motion.md) */

/* 6. Disabled */
.card--disabled {
  opacity: 0.4; pointer-events: none; filter: grayscale(0.3);
}
/* 7. Error state (e.g., payment failed card) */
.card--error {
  border-color: var(--color-error);
  box-shadow: 0 0 0 3px color-mix(in srgb, var(--color-error) 10%, transparent);
}
/* 8. Success state (e.g., order placed) */
.card--success {
  border-color: var(--color-success);
  box-shadow: 0 0 0 3px color-mix(in srgb, var(--color-success) 10%, transparent);
}
/* 10. Selected state (e.g., pricing plan) */
.card--selected {
  border-color: var(--accent);
  box-shadow: 0 0 0 3px color-mix(in srgb, var(--accent) 15%, transparent);
  background: color-mix(in srgb, var(--accent) 4%, var(--surface));
}
```

---

## TOAST / ALERT NOTIFICATIONS — All 4 Types

```css
.toast {
  display: flex; align-items: flex-start; gap: var(--space-3);
  padding: var(--space-4) var(--space-5);
  border-radius: var(--radius-md);
  border-left: 4px solid;
  box-shadow: var(--shadow-lg);
  font-size: var(--text-sm);
  animation: toast-in var(--dur-base) var(--ease-spring) both;
  max-width: 380px;
}
@keyframes toast-in {
  from { opacity: 0; transform: translateX(40px) scale(0.96); }
  to   { opacity: 1; transform: translateX(0)    scale(1); }
}
@keyframes toast-out {
  to { opacity: 0; transform: translateX(40px) scale(0.96); }
}

.toast--success { background: var(--color-success-bg); border-color: var(--color-success); color: var(--color-success); }
.toast--error   { background: var(--color-error-bg);   border-color: var(--color-error);   color: var(--color-error); }
.toast--warning { background: var(--color-warning-bg); border-color: var(--color-warning); color: var(--color-warning); }
.toast--info    { background: var(--color-info-bg);    border-color: var(--color-info);    color: var(--color-info); }

.toast__icon  { flex-shrink: 0; width: 18px; height: 18px; }
.toast__body  { flex: 1; }
.toast__title { font-weight: 600; margin-bottom: 2px; }
.toast__desc  { opacity: 0.8; font-size: var(--text-xs); }
.toast__close { opacity: 0.5; cursor: pointer; flex-shrink: 0; transition: opacity var(--transition-micro); }
.toast__close:hover { opacity: 1; }

/* Toast container */
.toast-container {
  position: fixed; bottom: var(--space-6); right: var(--space-6);
  z-index: 9999; display: flex; flex-direction: column; gap: var(--space-3);
  max-width: 380px; width: calc(100% - var(--space-12));
}
@media (max-width: 640px) {
  .toast-container { bottom: var(--space-4); right: var(--space-4); left: var(--space-4); max-width: none; }
}
```

```javascript
// Toast manager
const toastContainer = (() => {
  const el = document.createElement('div');
  el.className = 'toast-container';
  document.body.appendChild(el);
  return el;
})();

function showToast({ type = 'info', title, desc = '', duration = 4000 }) {
  const icons = { success: 'check-circle', error: 'x-circle', warning: 'alert-triangle', info: 'info' };
  const toast = document.createElement('div');
  toast.className = `toast toast--${type}`;
  toast.setAttribute('role', type === 'error' ? 'alert' : 'status');
  toast.setAttribute('aria-live', type === 'error' ? 'assertive' : 'polite');
  toast.innerHTML = `
    <i data-lucide="${icons[type]}" class="toast__icon"></i>
    <div class="toast__body">
      <div class="toast__title">${title}</div>
      ${desc ? `<div class="toast__desc">${desc}</div>` : ''}
    </div>
    <button class="toast__close btn btn--icon" aria-label="Dismiss" onclick="this.closest('.toast').remove()">
      <i data-lucide="x"></i>
    </button>`;
  toastContainer.appendChild(toast);
  lucide.createIcons();
  if (duration > 0) setTimeout(() => {
    toast.style.animation = 'toast-out 300ms ease-in both';
    setTimeout(() => toast.remove(), 300);
  }, duration);
  return toast;
}
// showToast({ type: 'success', title: 'Saved!', desc: 'Your changes have been saved.' });
// showToast({ type: 'error', title: 'Upload failed', desc: 'File too large. Max 10MB.', duration: 0 });
```

---

## BADGE / CHIP — State Variants

```html
<!-- Status badges -->
<span class="badge badge--success">Active</span>
<span class="badge badge--error">Failed</span>
<span class="badge badge--warning">Pending</span>
<span class="badge badge--info">In Review</span>
<span class="badge badge--neutral">Draft</span>

<!-- Removable chip -->
<span class="chip">
  Design <button class="chip__remove" aria-label="Remove Design">×</button>
</span>
```

```css
.badge {
  display: inline-flex; align-items: center; gap: 4px;
  padding: 3px 10px; border-radius: var(--radius-full);
  font-size: var(--text-xs); font-weight: 600;
  letter-spacing: 0.02em; line-height: 1.4;
  border: 1px solid;
}
.badge--success { background: var(--color-success-bg); color: var(--color-success); border-color: var(--color-success-border); }
.badge--error   { background: var(--color-error-bg);   color: var(--color-error);   border-color: var(--color-error-border); }
.badge--warning { background: var(--color-warning-bg); color: var(--color-warning); border-color: var(--color-warning-border); }
.badge--info    { background: var(--color-info-bg);    color: var(--color-info);    border-color: var(--color-info-border); }
.badge--neutral { background: var(--surface-up); color: var(--text-2); border-color: var(--border); }

/* Dot variant */
.badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }

.chip {
  display: inline-flex; align-items: center; gap: var(--space-2);
  padding: 4px 10px; border-radius: var(--radius-full);
  background: var(--surface-up); border: 1px solid var(--border);
  font-size: var(--text-xs); font-weight: 500;
}
.chip__remove {
  width: 16px; height: 16px; border-radius: 50%;
  background: var(--muted); color: var(--bg);
  display: grid; place-items: center;
  font-size: 10px; cursor: pointer; min-height: unset;
  transition: background var(--transition-micro);
}
.chip__remove:hover { background: var(--color-error); }
```

---

## INLINE ALERT BANNERS

```html
<div class="alert alert--warning" role="alert">
  <i data-lucide="alert-triangle" class="alert__icon"></i>
  <div class="alert__body">
    <strong class="alert__title">Unsaved changes</strong>
    <p class="alert__desc">You have unsaved changes. Save before leaving.</p>
  </div>
  <button class="btn btn--sm btn--ghost alert__action">Save now</button>
</div>
```

```css
.alert {
  display: flex; align-items: flex-start; gap: var(--space-4);
  padding: var(--space-4) var(--space-5);
  border-radius: var(--radius-md);
  border: 1px solid;
  font-size: var(--text-sm);
}
.alert--success { background: var(--color-success-bg); border-color: var(--color-success-border); }
.alert--error   { background: var(--color-error-bg);   border-color: var(--color-error-border); }
.alert--warning { background: var(--color-warning-bg); border-color: var(--color-warning-border); }
.alert--info    { background: var(--color-info-bg);    border-color: var(--color-info-border); }
.alert__icon  { flex-shrink: 0; width: 20px; height: 20px; margin-top: 1px; }
.alert--success .alert__icon { color: var(--color-success); }
.alert--error   .alert__icon { color: var(--color-error); }
.alert--warning .alert__icon { color: var(--color-warning); }
.alert--info    .alert__icon { color: var(--color-info); }
.alert__body  { flex: 1; display: flex; flex-direction: column; gap: 2px; }
.alert__title { font-weight: 600; }
.alert__desc  { opacity: 0.85; line-height: 1.5; }
.alert__action { flex-shrink: 0; align-self: center; }
```

---

## QUICK STATE CHECKLIST

Before shipping any interactive component, verify:
- [ ] Default state styled
- [ ] Hover: visual change (color shift, lift, or border)
- [ ] Focus: visible outline (`:focus-visible`) — not hidden
- [ ] Active: visual press feedback (scale down / color darken)
- [ ] Loading: spinner or skeleton — not blank
- [ ] Disabled: opacity 0.38, `cursor:not-allowed`, `pointer-events:none`
- [ ] Error: red border + error text + `role="alert"`
- [ ] Success: green confirmation + auto-reset after 2–3s
- [ ] All state changes transition, not snap (except Technical/disabled)
- [ ] Reduced motion: all transitions honor `prefers-reduced-motion`

---

## STATE 9 — EMPTY STATE (Core State)

**Rule**: Every component that displays data MUST have an empty state.
Empty state = the UI when there is no content yet (zero items, no results, fresh user).
Missing empty states are the #1 cause of "broken-looking" AI-generated apps.

### Empty State Anatomy

```
┌─────────────────────────────────────────┐
│  [Illustration or Icon]                 │
│                                         │
│  [Primary message — what happened]      │
│  [Secondary — what to do next]          │
│                                         │
│  [Optional CTA button]                  │
└─────────────────────────────────────────┘
```

### Universal Empty State Component

```html
<!-- empty-state.html — reuse across all empty scenarios -->
<div class="empty-state" role="status" aria-label="No content available">
  <!-- Icon or Lottie animation (language-dependent) -->
  <div class="empty-state__visual">
    <i data-lucide="inbox" class="empty-state__icon"></i>
    <!-- OR: <div id="empty-lottie"></div> for Warm/Expressive -->
  </div>
  <h3 class="empty-state__title">No items yet</h3>
  <p class="empty-state__desc">
    When you add items, they'll appear here.
  </p>
  <button class="btn btn--primary empty-state__action">
    <i data-lucide="plus"></i> Add your first item
  </button>
</div>
```

```css
.empty-state {
  display: flex; flex-direction: column; align-items: center;
  justify-content: center; text-align: center;
  padding: var(--space-16) var(--space-8);
  gap: var(--space-4);
  min-height: 320px;
}
.empty-state__visual {
  width: 80px; height: 80px;
  background: var(--surface-up);
  border-radius: var(--radius-lg);
  display: grid; place-items: center;
  color: var(--muted);
}
.empty-state__icon { width: 32px; height: 32px; }
.empty-state__title {
  font-family: var(--font-display);
  font-size: var(--text-xl); font-weight: 600;
  color: var(--text); letter-spacing: -0.02em;
}
.empty-state__desc { font-size: var(--text-sm); color: var(--muted);
  max-width: 320px; line-height: 1.6; }
.empty-state__action { margin-top: var(--space-2); }
```

### Empty State Variants by Context

```javascript
// Context-specific empty states — never use a generic "No data" message

const emptyStates = {
  // Search returned no results
  search: {
    icon: 'search-x',
    title: 'No results found',
    desc: 'Try different keywords or check for typos.',
    action: null,
  },
  // Table/list with no rows yet
  table: {
    icon: 'table-2',
    title: 'No records yet',
    desc: 'Add your first record to get started.',
    action: { label: 'Add record', icon: 'plus' },
  },
  // Inbox/notifications — cleared
  inbox: {
    icon: 'inbox',
    title: "You're all caught up",
    desc: 'No new notifications right now.',
    action: null,
  },
  // Error state that empties the UI
  error: {
    icon: 'wifi-off',
    title: 'Failed to load',
    desc: 'There was a problem fetching your data.',
    action: { label: 'Try again', icon: 'refresh-cw' },
  },
  // First-time user onboarding
  onboarding: {
    icon: 'sparkles',
    title: "Welcome! Let's get started",
    desc: 'Create your first project to begin.',
    action: { label: 'Create project', icon: 'folder-plus' },
  },
  // Cart / basket empty
  cart: {
    icon: 'shopping-bag',
    title: 'Your bag is empty',
    desc: 'Add items to your bag to continue.',
    action: { label: 'Browse products', icon: 'grid-2x2' },
  },
};

function renderEmptyState(context) {
  const s = emptyStates[context];
  return `
    <div class="empty-state" role="status" aria-label="${s.title}">
      <div class="empty-state__visual">
        <i data-lucide="${s.icon}" class="empty-state__icon"></i>
      </div>
      <h3 class="empty-state__title">${s.title}</h3>
      <p class="empty-state__desc">${s.desc}</p>
      ${s.action ? `
        <button class="btn btn--primary empty-state__action">
          <i data-lucide="${s.action.icon}"></i> ${s.action.label}
        </button>` : ''}
    </div>
  `;
}
```

### Empty State per Design Language

| Language | Visual | Animation | CTA style |
|----------|--------|-----------|-----------|
| **Luxury** | Minimal line icon, generous padding | None | Sharp, uppercase |
| **Premium** | Icon in surface card | Fade in on appear | Accent fill button |
| **Minimalist** | Icon only, no card | None | Text link |
| **Expressive** | Bold icon + color bg | Lottie animation | Vivid pill button |
| **Warm** | Lottie illustration | Gentle bounce | Pill, warm accent |
| **Technical** | Icon + monospace text | None | Compact outlined |

### Lottie Empty State (Warm / Expressive)

```html
<div class="empty-state">
  <div id="empty-lottie" style="width:120px;height:120px"></div>
  <h3 class="empty-state__title">Nothing here yet</h3>
  <p class="empty-state__desc">Get started by creating something new.</p>
</div>
<script>
// Free empty state animations: https://lottiefiles.com/search?q=empty+state
lottie.loadAnimation({
  container: document.getElementById('empty-lottie'),
  renderer: 'svg', loop: true, autoplay: true,
  path: 'https://assets5.lottiefiles.com/packages/lf20_hl5n0bwb.json'
});
</script>
