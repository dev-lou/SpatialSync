---
description: Cover 2026 native browser APIs - Popover API, <dialog> with ::backdrop, :user-valid/:user-invalid pseudo-classes, and @starting-style for entry animations. Always provide fallbacks.
---

# UI/UX Native Browser APIs (2026)

> Modern browser APIs for common UI patterns.
> Use native when possible, with graceful fallbacks.

---

## OVERVIEW

This skill covers emerging browser APIs:
1. **Popover API** - Native popovers without JavaScript
2. **`<dialog>`** - Native modals with ::backdrop
3. **`:user-valid/:user-invalid`** - User-interacted form validation
4. **`@starting-style`** - Entry animations for elements
5. **CSS Anchor Positioning** - Position relative to other elements

---

## 1. POPOVER API

### Basic Usage

```html
<!-- Trigger button -->
<button popovertarget="my-popover">Open Menu</button>

<!-- Popover content -->
<div id="my-popover" popover>
  <p>Popover content here!</p>
</div>
```

### Popover Types

```html
<!-- Auto (default): Closes when clicking outside or pressing Escape -->
<div popover="auto">Light dismiss popover</div>

<!-- Manual: Must be explicitly closed -->
<div popover="manual">Stays open until closed</div>
```

### Token-Aware Styling

```css
/* Base popover styles */
[popover] {
  padding: var(--space-4);
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-lg);
  color: var(--text);
  max-width: 320px;
  
  /* Remove default border */
  border: none;
  
  /* Entry animation */
  opacity: 0;
  transform: translateY(var(--space-2));
  transition: 
    opacity var(--dur-base),
    transform var(--dur-base),
    display var(--dur-base) allow-discrete;
}

[popover]:popover-open {
  opacity: 1;
  transform: translateY(0);
}

/* Starting style for entry animation */
@starting-style {
  [popover]:popover-open {
    opacity: 0;
    transform: translateY(var(--space-2));
  }
}

/* Backdrop for modal-like popovers */
[popover]::backdrop {
  background: var(--overlay);
  opacity: 0;
  transition: opacity var(--dur-base);
}

[popover]:popover-open::backdrop {
  opacity: 1;
}

@starting-style {
  [popover]:popover-open::backdrop {
    opacity: 0;
  }
}
```

### JavaScript Control

```javascript
const popover = document.getElementById('my-popover');

// Show popover
popover.showPopover();

// Hide popover
popover.hidePopover();

// Toggle popover
popover.togglePopover();

// Check if open
if (popover.matches(':popover-open')) {
  console.log('Popover is open');
}

// Listen for toggle events
popover.addEventListener('toggle', (e) => {
  if (e.newState === 'open') {
    console.log('Popover opened');
  } else {
    console.log('Popover closed');
  }
});
```

### Popover with Actions

```html
<button popovertarget="action-menu" class="btn btn--icon">
  <i data-lucide="more-vertical"></i>
</button>

<div id="action-menu" popover class="action-menu">
  <button class="action-menu__item" popovertarget="action-menu" popovertargetaction="hide">
    <i data-lucide="edit"></i>
    Edit
  </button>
  <button class="action-menu__item" popovertarget="action-menu" popovertargetaction="hide">
    <i data-lucide="copy"></i>
    Duplicate
  </button>
  <hr class="action-menu__divider">
  <button class="action-menu__item action-menu__item--danger" popovertarget="action-menu" popovertargetaction="hide">
    <i data-lucide="trash-2"></i>
    Delete
  </button>
</div>
```

### Browser Support & Fallback

```javascript
// Feature detection
if ('popover' in HTMLElement.prototype) {
  // Use native popover
} else {
  // Fallback to custom implementation
  document.querySelectorAll('[popovertarget]').forEach(trigger => {
    const targetId = trigger.getAttribute('popovertarget');
    const target = document.getElementById(targetId);
    
    trigger.addEventListener('click', () => {
      target.classList.toggle('popover--open');
    });
    
    // Close on outside click
    document.addEventListener('click', (e) => {
      if (!target.contains(e.target) && !trigger.contains(e.target)) {
        target.classList.remove('popover--open');
      }
    });
  });
}
```

---

## 2. DIALOG ELEMENT

### Basic Modal

```html
<button onclick="document.getElementById('my-dialog').showModal()">
  Open Modal
</button>

<dialog id="my-dialog">
  <form method="dialog">
    <h2>Modal Title</h2>
    <p>Modal content goes here.</p>
    <div class="dialog__actions">
      <button value="cancel">Cancel</button>
      <button value="confirm" class="btn btn--primary">Confirm</button>
    </div>
  </form>
</dialog>
```

### Token-Aware Dialog Styles

```css
dialog {
  padding: 0;
  border: none;
  border-radius: var(--radius-xl);
  box-shadow: var(--shadow-xl);
  max-width: min(90vw, 500px);
  background: var(--surface);
  color: var(--text);
}

dialog::backdrop {
  background: var(--overlay);
  backdrop-filter: blur(4px);
}

/* Animation */
dialog {
  opacity: 0;
  transform: scale(0.95);
  transition: 
    opacity var(--dur-base) var(--ease-out),
    transform var(--dur-base) var(--ease-out),
    display var(--dur-base) allow-discrete;
}

dialog[open] {
  opacity: 1;
  transform: scale(1);
}

@starting-style {
  dialog[open] {
    opacity: 0;
    transform: scale(0.95);
  }
}

dialog::backdrop {
  opacity: 0;
  transition: opacity var(--dur-base);
}

dialog[open]::backdrop {
  opacity: 1;
}

@starting-style {
  dialog[open]::backdrop {
    opacity: 0;
  }
}

/* Internal structure */
.dialog__header {
  padding: var(--space-6);
  border-bottom: 1px solid var(--border);
}

.dialog__content {
  padding: var(--space-6);
}

.dialog__actions {
  display: flex;
  justify-content: flex-end;
  gap: var(--space-3);
  padding: var(--space-4) var(--space-6);
  border-top: 1px solid var(--border);
  background: var(--bg);
}

/* Responsive */
@media (max-width: 640px) {
  dialog {
    max-width: 100%;
    max-height: 100%;
    width: 100%;
    height: auto;
    margin: 0;
    border-radius: 0;
    position: fixed;
    bottom: 0;
    top: auto;
    border-radius: var(--radius-xl) var(--radius-xl) 0 0;
  }
}
```

### Dialog JavaScript

```javascript
const dialog = document.getElementById('my-dialog');

// Open as modal (blocks background interaction)
dialog.showModal();

// Open as non-modal
dialog.show();

// Close
dialog.close();

// Close with return value
dialog.close('confirmed');

// Handle close
dialog.addEventListener('close', () => {
  console.log('Dialog closed with:', dialog.returnValue);
});

// Handle click outside (for modals)
dialog.addEventListener('click', (e) => {
  if (e.target === dialog) {
    dialog.close();
  }
});

// Handle Escape key (built-in for modals)
// Cancel event fires before close on Escape
dialog.addEventListener('cancel', (e) => {
  // Optionally prevent closing
  // e.preventDefault();
});
```

### Accessible Dialog

```html
<dialog 
  id="confirm-dialog"
  aria-labelledby="dialog-title"
  aria-describedby="dialog-description"
>
  <h2 id="dialog-title">Confirm Deletion</h2>
  <p id="dialog-description">
    Are you sure you want to delete this item? This action cannot be undone.
  </p>
  <form method="dialog">
    <button value="cancel">Cancel</button>
    <button value="delete" class="btn btn--danger">Delete</button>
  </form>
</dialog>
```

---

## 3. :user-valid / :user-invalid

These pseudo-classes only apply AFTER user interaction, preventing form fields from showing errors before the user has typed.

### Basic Usage

```css
/* Only show validation styles after user interaction */
input:user-valid {
  border-color: var(--color-success);
}

input:user-invalid {
  border-color: var(--color-error);
}

/* Show error message only after user interaction */
.field__error {
  display: none;
}

input:user-invalid ~ .field__error {
  display: block;
}
```

### Complete Form Field

```html
<div class="field">
  <label for="email" class="field__label">Email</label>
  <input 
    type="email" 
    id="email" 
    name="email" 
    required
    class="field__input"
    aria-describedby="email-error"
  >
  <p id="email-error" class="field__error">
    Please enter a valid email address
  </p>
</div>
```

```css
.field__input {
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
  padding: var(--space-3);
  transition: border-color var(--dur-micro);
}

.field__input:focus {
  outline: none;
  border-color: var(--accent);
  box-shadow: 0 0 0 3px var(--accent-soft);
}

/* Validation states - only after user interaction */
.field__input:user-valid {
  border-color: var(--color-success);
  background-image: url("data:image/svg+xml,..."); /* Check icon */
  background-repeat: no-repeat;
  background-position: right var(--space-3) center;
  padding-right: var(--space-10);
}

.field__input:user-invalid {
  border-color: var(--color-error);
  background-image: url("data:image/svg+xml,..."); /* X icon */
  background-repeat: no-repeat;
  background-position: right var(--space-3) center;
  padding-right: var(--space-10);
}

/* Error message visibility */
.field__error {
  color: var(--color-error);
  font-size: var(--text-sm);
  margin-top: var(--space-2);
  display: none;
}

.field__input:user-invalid ~ .field__error {
  display: block;
}

/* Focus ring on error state */
.field__input:user-invalid:focus {
  border-color: var(--color-error);
  box-shadow: 0 0 0 3px rgba(207, 34, 46, 0.15);
}
```

### Fallback for Older Browsers

```css
/* Fallback using :invalid with .touched class */
@supports not selector(:user-invalid) {
  .field__input:invalid:not(:focus):not(:placeholder-shown) {
    border-color: var(--color-error);
  }
  
  .field__input:valid:not(:placeholder-shown) {
    border-color: var(--color-success);
  }
}
```

```javascript
// Add .touched class on blur for fallback
if (!CSS.supports('selector(:user-invalid)')) {
  document.querySelectorAll('input, select, textarea').forEach(input => {
    input.addEventListener('blur', () => {
      input.classList.add('touched');
    });
  });
}
```

---

## 4. @starting-style

Define initial styles for elements appearing on the page, enabling entry animations.

### Basic Entry Animation

```css
.card {
  opacity: 1;
  transform: translateY(0);
  transition: opacity var(--dur-base), transform var(--dur-base);
}

@starting-style {
  .card {
    opacity: 0;
    transform: translateY(var(--space-4));
  }
}
```

### Dialog Entry/Exit

```css
dialog {
  opacity: 0;
  transform: translateY(var(--space-4));
  transition: 
    opacity var(--dur-base),
    transform var(--dur-base),
    display var(--dur-base) allow-discrete,
    overlay var(--dur-base) allow-discrete;
}

dialog[open] {
  opacity: 1;
  transform: translateY(0);
}

@starting-style {
  dialog[open] {
    opacity: 0;
    transform: translateY(var(--space-4));
  }
}
```

### Popover with @starting-style

```css
[popover] {
  opacity: 0;
  scale: 0.95;
  transition: 
    opacity var(--dur-base) var(--ease-out),
    scale var(--dur-base) var(--ease-out),
    display var(--dur-base) allow-discrete;
}

[popover]:popover-open {
  opacity: 1;
  scale: 1;
}

@starting-style {
  [popover]:popover-open {
    opacity: 0;
    scale: 0.95;
  }
}
```

### Feature Detection

```css
@supports (selector(:popover-open)) {
  /* Use @starting-style */
}

@supports not (selector(:popover-open)) {
  /* Fallback - no animation or use JS */
}
```

---

## 5. CSS ANCHOR POSITIONING

Position elements relative to other elements without JavaScript.

### Basic Anchor

```html
<button id="anchor-btn" class="btn">
  Options
</button>

<div class="dropdown" anchor="anchor-btn">
  <ul>
    <li>Option 1</li>
    <li>Option 2</li>
  </ul>
</div>
```

```css
#anchor-btn {
  anchor-name: --options-btn;
}

.dropdown {
  position: absolute;
  position-anchor: --options-btn;
  
  /* Position below the anchor */
  top: anchor(bottom);
  left: anchor(left);
  
  /* With offset */
  margin-top: var(--space-2);
}
```

### Position Fallback

```css
.dropdown {
  position: absolute;
  position-anchor: --trigger;
  
  /* Try bottom first, then top if not enough space */
  position-try-options: 
    --bottom-start,
    --top-start,
    --bottom-end,
    --top-end;
}

@position-try --bottom-start {
  top: anchor(bottom);
  left: anchor(left);
}

@position-try --top-start {
  bottom: anchor(top);
  left: anchor(left);
}

@position-try --bottom-end {
  top: anchor(bottom);
  right: anchor(right);
}

@position-try --top-end {
  bottom: anchor(top);
  right: anchor(right);
}
```

---

## BROWSER SUPPORT TABLE

| Feature | Chrome | Firefox | Safari | Edge |
|---------|--------|---------|--------|------|
| Popover API | 114+ | 125+ | 17+ | 114+ |
| `<dialog>` | 37+ | 98+ | 15.4+ | 79+ |
| ::backdrop | 37+ | 98+ | 15.4+ | 79+ |
| :user-valid | 119+ | 88+ | No | 119+ |
| @starting-style | 117+ | No | 17.5+ | 117+ |
| Anchor Position | 125+ | No | No | 125+ |

---

## PROGRESSIVE ENHANCEMENT STRATEGY

```javascript
// Feature detection helper
const supports = {
  popover: 'popover' in HTMLElement.prototype,
  dialog: typeof HTMLDialogElement !== 'undefined',
  startingStyle: CSS.supports('@starting-style {}'),
  userInvalid: CSS.supports('selector(:user-invalid)'),
  anchorPosition: CSS.supports('position-anchor: --test'),
};

// Apply polyfills or fallbacks as needed
if (!supports.popover) {
  // Load popover polyfill or use custom implementation
}

if (!supports.dialog) {
  // Load dialog polyfill: https://github.com/GoogleChrome/dialog-polyfill
}

// Log support for debugging
console.log('Browser API Support:', supports);
```

---

## CHECKLIST

When using native APIs:

- [ ] Feature detection implemented
- [ ] Fallback provided for unsupported browsers
- [ ] Token-aware styling applied
- [ ] Animations respect prefers-reduced-motion
- [ ] Accessibility attributes added
- [ ] Keyboard navigation works
- [ ] Focus management correct
- [ ] Mobile/touch interaction tested
