# UI/UX Accessibility, Forms & Fluid Typography

> Fluid clamp() typography, WCAG contrast rules, skip links, ARIA landmarks, floating label forms, drag-and-drop upload.

## STEP 19 — Fluid Typography & Spacing

**Rule**: Replace fixed breakpoints with fluid scales. `clamp()` creates smooth, proportional scaling between any two sizes with no jumps.

### Fluid Type Scale

```css
:root {
  /* Fluid text — scales smoothly from mobile to desktop */
  /* clamp(MIN, PREFERRED, MAX) */
  --text-xs:   clamp(0.75rem,  0.7rem  + 0.25vw, 0.875rem);
  --text-sm:   clamp(0.875rem, 0.8rem  + 0.375vw, 1rem);
  --text-base: clamp(1rem,     0.9rem  + 0.5vw,   1.125rem);
  --text-lg:   clamp(1.125rem, 1rem    + 0.625vw, 1.25rem);
  --text-xl:   clamp(1.25rem,  1.1rem  + 0.75vw,  1.5rem);
  --text-2xl:  clamp(1.5rem,   1.2rem  + 1.5vw,   2rem);
  --text-3xl:  clamp(1.875rem, 1.4rem  + 2.375vw, 2.5rem);
  --text-4xl:  clamp(2.25rem,  1.5rem  + 3.75vw,  3.5rem);
  --text-5xl:  clamp(3rem,     1.8rem  + 6vw,     5rem);
  --text-hero: clamp(3rem,     2rem    + 8vw,     8rem);

  /* Fluid spacing — scales with viewport */
  --fluid-sm:  clamp(var(--space-4),  2vw,  var(--space-8));
  --fluid-md:  clamp(var(--space-8),  4vw,  var(--space-16));
  --fluid-lg:  clamp(var(--space-16), 8vw,  var(--space-30));
  --fluid-xl:  clamp(var(--space-20), 10vw, var(--space-40));
}
```

### Fluid Usage Rules per Language

| Language | Hero text | Body text | Section spacing |
|----------|-----------|-----------|----------------|
| **Luxury** | `--text-hero` (very large) | `--text-base` (restrained) | `--fluid-xl` |
| **Premium** | `--text-4xl / 5xl` | `--text-base / lg` | `--fluid-lg` |
| **Minimalist** | `--text-3xl / 4xl` | `--text-sm` | `--fluid-xl` (whitespace) |
| **Expressive** | `--text-hero` | `--text-lg` | `--fluid-md` |
| **Technical** | `--text-2xl` | `--text-sm` (dense) | `--fluid-sm` |

---

## STEP 20 — Accessibility First (WCAG AA+)

**Rule**: Accessibility is not a feature. It is the foundation. Inaccessible = broken.

### Contrast Requirements

| Text type | Min contrast | Target |
|-----------|-------------|--------|
| Body text (≥18px) | 4.5:1 | 7:1 (AAA) |
| Large text (≥24px or bold ≥18.67px) | 3:1 | 4.5:1 |
| UI components (borders, icons) | 3:1 | — |
| Placeholder text | 4.5:1 | — |

**Quick check**: `color-contrast()` in CSS (Safari) or use [WebAIM Contrast Checker](https://webaim.org/resources/contrastchecker/).

### Focus Visible — Aesthetic & Accessible

```css
/* Universal — works with any design language */
:focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 3px;
  border-radius: var(--radius-sm);
}

/* Luxury — thin, high-contrast line */
[data-lang="luxury"] :focus-visible {
  outline: 1px solid var(--text);
  outline-offset: 6px;
}

/* Technical — terminal-style focus */
[data-lang="technical"] :focus-visible {
  outline: 1px solid var(--accent);
  outline-offset: 1px;
  border-radius: 2px;
  box-shadow: 0 0 0 3px color-mix(in srgb, var(--accent) 30%, transparent);
}

/* Never do: */
/* *:focus { outline: none; } ← kills keyboard navigation */
```

### ARIA Landmarks — Required in Every Layout

```html
<body>
  <a href="#main-content" class="skip-link">Skip to main content</a>

  <header role="banner">
    <nav role="navigation" aria-label="Main navigation">
      <!-- nav items -->
    </nav>
  </header>

  <main id="main-content" role="main">
    <section aria-labelledby="hero-heading">
      <h1 id="hero-heading">Headline</h1>
    </section>
    <!-- All sections need aria-labelledby pointing to their heading -->
  </main>

  <aside role="complementary" aria-label="Related content"><!-- sidebar --></aside>

  <footer role="contentinfo"><!-- footer --></footer>
</body>
```

```css
/* Skip link — hidden until focused */
.skip-link {
  position: absolute; top: -100%; left: var(--space-4);
  background: var(--accent); color: white;
  padding: var(--space-2) var(--space-4); border-radius: var(--radius-md);
  font-weight: 600; z-index: 9999;
  transition: top 0.2s;
}
.skip-link:focus { top: var(--space-4); }
```

### Heading Hierarchy — Strict Rule

```
<h1> — One per page. Page title or hero headline.
<h2> — Section titles (Features, About, CTA, etc.)
<h3> — Sub-sections within <h2> sections (card titles, etc.)
<h4> — Rarely used. Only for deep nesting (FAQ items, etc.)
Never skip levels. Never use heading for styling — use CSS.
```

### Interactive Element Requirements

```css
/* Minimum touch target (already in Step 8) */
/* Additional: disabled states must be visually clear */
.btn:disabled, button:disabled {
  opacity: 0.4;
  cursor: not-allowed;
  pointer-events: none;
}

/* Icon-only buttons need labels */
/* <button aria-label="Close menu"><i data-lucide="x"></i></button> */

/* Images: meaningful ones need alt, decorative ones get alt="" */
/* <img src="..." alt=""> ← decorative, screen reader skips it */
/* <img src="..." alt="Product photo of leather wallet"> ← meaningful */
```

---

## STEP 21 — 3D & Immersive Elements (Sparingly)
## STEP 22 — Advanced Form & Data Input Patterns

**Rule**: Forms are often the most broken part of AI-generated UIs. Polished forms = trust. Every input has a label, a helper, and an error state.

### Modern Input Component

```html
<!-- Floating label input -->
<div class="field" id="field-email">
  <input class="field__input" type="email" id="email" name="email"
    placeholder=" " autocomplete="email" required>
  <label class="field__label" for="email">Email address</label>
  <span class="field__helper">We'll never share your email.</span>
  <span class="field__error" role="alert" aria-live="polite"></span>
</div>
```

```css
.field {
  position: relative;
  display: flex; flex-direction: column; gap: var(--space-1);
}

.field__input {
  width: 100%; padding: 20px 16px 8px;
  border: 1.5px solid var(--border);
  border-radius: var(--radius-md);
  background: var(--surface);
  font-size: var(--text-base);
  transition: border-color var(--transition-micro), box-shadow var(--transition-micro);
}
.field__input:focus {
  border-color: var(--accent);
  box-shadow: 0 0 0 3px color-mix(in srgb, var(--accent) 20%, transparent);
  outline: none;
}

.field__label {
  position: absolute; top: 14px; left: 16px;
  color: var(--muted); font-size: var(--text-sm);
  transition: transform var(--transition-tactile), font-size var(--transition-tactile), color var(--transition-micro);
  pointer-events: none; transform-origin: left top;
}
/* Float label up when input has value or is focused */
.field__input:not(:placeholder-shown) ~ .field__label,
.field__input:focus ~ .field__label {
  transform: translateY(-9px) scale(0.78);
  color: var(--accent);
}

.field__helper { font-size: var(--text-xs); color: var(--muted); padding-left: 4px; }
.field__error  { font-size: var(--text-xs); color: #CF222E; padding-left: 4px; display: none; }

/* Error state — uses :has() for parent styling */
.field:has(.field__input:invalid:not(:placeholder-shown)) .field__input {
  border-color: #CF222E;
}
.field:has(.field__input:invalid:not(:placeholder-shown)) .field__error {
  display: block;
}
.field:has(.field__input:invalid:not(:placeholder-shown)) .field__label {
  color: #CF222E;
}
```

### Drag & Drop File Upload

```html
<div class="dropzone" id="dropzone" tabindex="0" role="button"
  aria-label="Upload file. Click or drag and drop.">
  <i data-lucide="upload-cloud" style="width:48px;height:48px;color:var(--muted)"></i>
  <p><strong>Drop files here</strong> or click to browse</p>
  <p style="font-size:var(--text-xs);color:var(--muted)">PNG, JPG, PDF up to 10MB</p>
  <input type="file" id="file-input" accept="image/*,.pdf" style="display:none">
</div>
```

```css
.dropzone {
  border: 2px dashed var(--border); border-radius: var(--radius-lg);
  padding: var(--space-12) var(--space-8); text-align: center;
  display: flex; flex-direction: column; align-items: center; gap: var(--space-3);
  cursor: pointer; color: var(--text-2);
  transition: border-color var(--transition-base), background var(--transition-base);
}
.dropzone:hover, .dropzone.drag-over {
  border-color: var(--accent);
  background: color-mix(in srgb, var(--accent) 5%, transparent);
}
```

```javascript
const dropzone  = document.getElementById('dropzone');
const fileInput = document.getElementById('file-input');

dropzone.addEventListener('click', () => fileInput.click());
dropzone.addEventListener('dragover', e => { e.preventDefault(); dropzone.classList.add('drag-over'); });
dropzone.addEventListener('dragleave', () => dropzone.classList.remove('drag-over'));
dropzone.addEventListener('drop', e => {
  e.preventDefault();
  dropzone.classList.remove('drag-over');
  handleFiles(e.dataTransfer.files);
});
fileInput.addEventListener('change', e => handleFiles(e.target.files));

function handleFiles(files) {
  [...files].forEach(file => {
    // validate, preview, upload
    swal.toast(`${file.name} uploaded`, 'success');
  });
}
```

---

## STEP 23 — WCAG 3.0 AWARENESS (Preparing for 2026+)

WCAG 3.0 is the next-generation accessibility standard, currently in development.
It is NOT yet a legal requirement in most regions, but structure your code NOW to be ready.

### Key Shift: Pass/Fail → Bronze/Silver/Gold Scoring

| Level | Requirement | Who needs it |
|-------|-------------|--------------|
| **Bronze** | Technical compliance — replaces WCAG 2.1 AA | All public websites |
| **Silver** | UX quality — beyond technical specs | Government, enterprise |
| **Gold** | Tested with real disabled users, exceptional UX | High-compliance sectors |

### What Changes from WCAG 2.x

**Color contrast**: WCAG 3.0 uses **APCA** (Advanced Perceptual Contrast Algorithm) instead of the current 4.5:1 ratio. APCA is more nuanced — it accounts for font weight, size, and polarity (light on dark vs dark on light).

```javascript
// Check APCA contrast today: https://www.myndex.com/APCA/
// APCA Lc value targets:
// Body text (16px, normal):     Lc 75+ (preferred), Lc 60+ (minimum)
// Large text (24px, bold):      Lc 45+
// UI components (icons, borders): Lc 30+
// Decorative (non-essential):   Lc 15+ or exempt
```

**Functional needs over rules**: WCAG 3.0 tests whether users with visual, auditory, motor, and cognitive disabilities can **accomplish tasks** — not just whether elements have the right attributes.

**Cognitive accessibility**: WCAG 3.0 formally includes COGA (Cognitive Accessibility) guidelines:
- Clear, simple language (reading level ≤ 8th grade for consumer apps)
- Consistent navigation and naming
- Visible, understandable error messages
- Minimize time limits
- Reduce distractions (auto-playing media, flashing content)

### Prepare NOW (Zero Extra Cost)

```html
<!-- 1. Use semantic HTML — WCAG 3.0 rewards it -->
<button> not <div onclick>
<nav>    not <div class="nav">
<main>   not <div id="content">
<article> not <div class="post">

<!-- 2. Meaningful error messages (Cognitive a11y) -->
<!-- BAD: -->
<span class="error">Invalid input</span>
<!-- GOOD: -->
<span class="field__error" role="alert">
  Email must be a valid address, like name@example.com
</span>

<!-- 3. Status messages (WCAG 4.1.3 — already in 2.1, critical for 3.0) -->
<div role="status" aria-live="polite">3 items added to cart</div>
<div role="alert"  aria-live="assertive">Error: Session expired</div>
```

```css
/* 4. Respect prefers-reduced-motion (already in uiux-motion.md — verify it's present) */
/* 5. Respect prefers-contrast */
@media (prefers-contrast: more) {
  :root {
    --border: rgba(0,0,0,0.5);  /* stronger borders */
  }
  .btn { border-width: 2px; }
  :focus-visible { outline-width: 3px; }
}

/* 6. Respect prefers-reduced-transparency */
@media (prefers-reduced-transparency: reduce) {
  .nav-premium, .glass-card {
    backdrop-filter: none;
    background: var(--surface); /* solid fallback */
  }
}
```

### WCAG 3.0 Readiness Checklist

```
□ Semantic HTML5 throughout (no div-soup)
□ All ARIA landmarks present (banner/main/nav/contentinfo)
□ Error messages are descriptive (not just "Invalid")
□ Status updates use role="status" or role="alert"
□ No keyboard trap (except modals — which have intentional trap)
□ Focus visible in all components
□ prefers-reduced-motion respected
□ prefers-contrast supported (stronger borders/outlines)
□ Reading level: consumer-facing copy is simple and clear
□ No auto-playing audio/video with audio
□ Session timeouts warn users before expiry
□ Form fields clearly labeled (not just placeholder)
□ Color is never the ONLY way to convey information
```

---

## ACCESSIBILITY FOR AI-GENERATED CONTENT

**Rule**: Content generated by AI is dynamic, unpredictable, and often invisible to screen readers unless specifically handled. AI-native apps must ensure this content is inclusive.

### 1. Auto-generating Alt Text
Always provide `alt` text for AI-generated images. If the AI description is pending, use a generic fallback.

```javascript
// Pattern for AI image generation
async function generateAIImage(prompt) {
  const imgEl = document.querySelector('#generated-img');
  imgEl.alt = "Generating image based on: " + prompt; // Immediate context
  imgEl.setAttribute('aria-busy', 'true');
  
  const result = await aiService.generate(prompt);
  
  imgEl.src = result.url;
  imgEl.alt = result.description || "AI-generated image of " + prompt;
  imgEl.removeAttribute('aria-busy');
}
```

### 2. Streaming LLM Responses
Streaming text (token by token) is a "live region" nightmare. If every token triggers an update, it will overwhelm screen readers.

**Solution**: Use `aria-live="polite"` on the container and update it at reasonable intervals, or use a hidden status region to announce completion.

```html
<div class="chat-response">
  <!-- Visible streaming container -->
  <div id="streaming-text" aria-hidden="true"></div>
  
  <!-- Hidden accessible region -->
  <div id="accessible-status" role="status" aria-live="polite" class="sr-only">
    AI is thinking...
  </div>
</div>
```

### 3. Announcing AI Lifecycle
Users must know when the AI is working and when it has finished.

```javascript
function updateChat(token, isFirst, isLast) {
  const visible = document.getElementById('streaming-text');
  const status = document.getElementById('accessible-status');
  
  visible.textContent += token;
  
  if (isFirst) {
    status.textContent = "AI started generating response.";
  }
  
  if (isLast) {
    // Final announcement of the full content
    status.textContent = "AI finished. Response: " + visible.textContent;
  }
}
```

### 4. AI Content Streaming Example

```javascript
// Example: Accessible Streaming Helper
class AIAccessibleStreamer {
  constructor(displayEl, statusEl) {
    this.displayEl = displayEl;
    this.statusEl = statusEl;
    this.fullText = "";
  }

  onStart() {
    this.statusEl.textContent = "AI is generating a response...";
    this.displayEl.setAttribute('aria-busy', 'true');
  }

  onToken(token) {
    this.fullText += token;
    this.displayEl.textContent = this.fullText;
    // We don't update statusEl on every token to avoid chatter
  }

  onComplete() {
    this.displayEl.removeAttribute('aria-busy');
    // Announce the full result for screen readers
    this.statusEl.textContent = "AI Response complete: " + this.fullText;
  }
}


---

## :focus-visible POLYFILL (Safari < 15.4)

**Rule**: `:focus-visible` is essential for accessible focus states. Safari versions before 15.4 don't support it. Always include a polyfill for full browser coverage.

### Browser Support (2026)

```
Chrome 86+    ✅ Native support
Firefox 85+   ✅ Native support
Safari 15.4+  ✅ Native support
Safari < 15.4 ❌ Needs polyfill
```

### Polyfill CDN Script

```html
<!-- Add to <head> — loads polyfill only if needed -->
<script>
  // Feature detect :focus-visible support
  try {
    document.querySelector(':focus-visible');
  } catch (e) {
    // Load polyfill for browsers that don't support it
    var script = document.createElement('script');
    script.src = 'https://cdn.jsdelivr.net/npm/focus-visible@5.2.0/dist/focus-visible.min.js';
    document.head.appendChild(script);
  }
</script>
```

### Fallback CSS

The polyfill adds `.focus-visible` class instead of the pseudo-class. Write CSS that handles both:

```css
/* Modern browsers — use native :focus-visible */
:focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 3px;
  border-radius: var(--radius-sm);
}

/* Polyfill fallback — .focus-visible class */
.js-focus-visible :focus:not(.focus-visible) {
  outline: none;
}

.js-focus-visible .focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 3px;
  border-radius: var(--radius-sm);
}

/* Combined selector for maximum compatibility */
:focus-visible,
.focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 3px;
}
```

### Complete Focus System with Polyfill

```css
/* Reset default focus for mouse users */
:focus:not(:focus-visible) {
  outline: none;
}

/* Keyboard focus ring — visible and accessible */
:focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 3px;
  border-radius: var(--radius-sm);
}

/* Polyfill support */
.js-focus-visible :focus:not(.focus-visible) {
  outline: none;
}

.js-focus-visible .focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 3px;
  border-radius: var(--radius-sm);
}

/* High contrast mode support */
@media (prefers-contrast: more) {
  :focus-visible,
  .focus-visible {
    outline-width: 3px;
    outline-offset: 4px;
  }
}

/* Per-design-language focus styles */
[data-lang="luxury"] :focus-visible,
[data-lang="luxury"] .focus-visible {
  outline: 1px solid var(--text);
  outline-offset: 6px;
  border-radius: 0;
}

[data-lang="technical"] :focus-visible,
[data-lang="technical"] .focus-visible {
  outline: 1px solid var(--accent);
  outline-offset: 1px;
  border-radius: 2px;
  box-shadow: 0 0 0 3px color-mix(in srgb, var(--accent) 30%, transparent);
}

[data-lang="warm"] :focus-visible,
[data-lang="warm"] .focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 4px;
  border-radius: 999px;
}
```

### JavaScript Detection

```javascript
// Detect if polyfill is active
const isPolyfillActive = document.body.classList.contains('js-focus-visible');

// Programmatically apply focus-visible class when using focus()
function focusWithVisible(element) {
  element.focus();
  
  // If polyfill is active, add the class manually
  if (isPolyfillActive || !CSS.supports('selector(:focus-visible)')) {
    element.classList.add('focus-visible');
    
    element.addEventListener('blur', () => {
      element.classList.remove('focus-visible');
    }, { once: true });
  }
}

// Usage
const button = document.querySelector('#my-button');
focusWithVisible(button);
```

### NPM Package Alternative

```bash
npm install focus-visible
```

```javascript
// Import at app entry point
import 'focus-visible';
```

### React Integration

```jsx
// _app.jsx or layout.jsx
import { useEffect } from 'react';

export default function App({ Component, pageProps }) {
  useEffect(() => {
    // Dynamic import of polyfill only if needed
    if (!CSS.supports('selector(:focus-visible)')) {
      import('focus-visible');
    }
  }, []);

  return <Component {...pageProps} />;
}
```