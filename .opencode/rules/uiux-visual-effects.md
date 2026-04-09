---
description: Advanced visual effects including parallax scrolling, mouse-following glow, typing animations, confetti celebrations, and Lottie animation usage.
---

# UI/UX Visual Effects 2026

> Parallax scrolling, mouse-following glow, typing animations, 
> confetti celebrations, and Lottie animation usage.

---

## 1. PARALLAX SCROLLING (Advanced GSAP)

**Rule**: Use GSAP + ScrollTrigger for smooth, high-performance parallax. Never use `background-attachment: fixed`.

```javascript
// GSAP Parallax Example
gsap.registerPlugin(ScrollTrigger);

gsap.to('.parallax-bg', {
  scrollTrigger: {
    trigger: '.parallax-section',
    start: 'top bottom',
    end: 'bottom top',
    scrub: true,
  },
  yPercent: 20,
  ease: 'none',
});

gsap.to('.parallax-content', {
  scrollTrigger: {
    trigger: '.parallax-section',
    start: 'top bottom',
    end: 'bottom top',
    scrub: 0.5,
  },
  yPercent: -10,
  ease: 'none',
});
```

---

## 2. MOUSE-FOLLOWING GLOW / SPOTLIGHT

```html
<div class="glow-card" id="glow-card">
  <div class="glow-card__content">...</div>
  <div class="glow-card__spotlight"></div>
</div>
```

```css
.glow-card {
  position: relative;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  overflow: hidden;
}

.glow-card__spotlight {
  position: absolute; inset: 0;
  background: radial-gradient(circle 200px at var(--x, 50%) var(--y, 50%), 
              color-mix(in srgb, var(--accent) 15%, transparent), transparent);
  pointer-events: none;
  opacity: 0;
  transition: opacity var(--dur-base) var(--ease-out);
}

.glow-card:hover .glow-card__spotlight { opacity: 1; }
```

```javascript
// Spotlight Logic
const card = document.getElementById('glow-card');
card.addEventListener('mousemove', (e) => {
  const rect = card.getBoundingClientRect();
  const x = ((e.clientX - rect.left) / rect.width) * 100;
  const y = ((e.clientY - rect.top) / rect.height) * 100;
  card.style.setProperty('--x', `${x}%`);
  card.style.setProperty('--y', `${y}%`);
});
```

---

## 3. TYPING ANIMATION (Typewriter Effect)

```html
<h1 class="typing-text" id="typing-text"></h1>
```

```javascript
// Typewriter Logic
const text = "Designing the future of UI/UX.";
const el = document.getElementById('typing-text');
let i = 0;

function type() {
  if (i < text.length) {
    el.textContent += text.charAt(i);
    i++;
    setTimeout(type, 50);
  }
}
type();
```

---

## 4. CONFETTI / CELEBRATION (Success States)

```javascript
// Confetti using canvas-confetti
import confetti from 'canvas-confetti';

function celebrate() {
  confetti({
    particleCount: 100,
    spread: 70,
    origin: { y: 0.6 },
    colors: ['#0066FF', '#00C896', '#FF3B00'],
  });
}

// Usage: celebrate() on success
```

---

## 5. LOTTIE ANIMATION USAGE GUIDE

**Rule**: Use Lottie for complex vector animations. Load via CDN for small projects.

```html
<!-- Lottie Container -->
<div id="lottie-success" style="width:200px;height:200px"></div>

<!-- Lottie Script -->
<script src="https://cdn.jsdelivr.net/npm/lottie-web@5.12.2/build/player/lottie.min.js"></script>
<script>
  const anim = lottie.loadAnimation({
    container: document.getElementById('lottie-success'),
    renderer: 'svg',
    loop: false,
    autoplay: true,
    path: 'https://assets.example.com/success.json'
  });
</script>
```

---

## 6. PREFERS-REDUCED-MOTION (GPU-Only)

**Rule**: Always respect `prefers-reduced-motion` and only animate `transform` and `opacity`.

```css
@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
    scroll-behavior: auto !important;
  }
}

/* GPU-Only Animation */
.gpu-animate {
  will-change: transform, opacity;
  transform: translateZ(0); /* Force GPU layer */
}
```

---

## Morphing & Storytelling Scroll (Wow Factor)

### When to use morphing / storytelling scroll effects

These effects are NOT for every project. Apply them ONLY when:

- The design language is **Expressive** (loud, energetic, memorable)
- The user explicitly asks for a “wow”, “storytelling”, or “immersive” experience
- The project is NOT somber, serious, or professional (e.g., funeral, legal, medical, government)

DO NOT use for:
- Luxury (breaks elegance)
- Minimalist (breaks silence)
- Editorial (distracts from reading)
- Technical (adds unnecessary noise)
- Warm & Human (unless gentle fade only – no morphing)
- Any funeral, memorial, or bereavement service

### Default animation rules by design language

| Design language / context | Animation style |
|---------------------------|----------------|
| Luxury | Ultra‑slow, elegant fade (1–2s) |
| Premium Modern | Subtle fade‑up (0.4s, small offset) |
| Minimalist | **No animation** – instant reveal |
| Expressive | Full storytelling (morphing, 3D tilt, scroll‑driven) – only when appropriate |
| Editorial | **No scroll animations** – static |
| Warm & Human | Gentle fade (0.5s, no offset) |
| Technical | **No animations** – instant |
| Somber (funeral, legal, medical, gov) | **No animations** – instant, respectful |

**If in doubt** (design language not specified or unclear): use **instant reveal (no animation)**. Never default to generic fade/slide for serious or professional sites.

### 1. Morphing between sections (View Transitions API)

```javascript
// Using next-view-transitions or native API
async function navigateWithMorph(url) {
  if (!document.startViewTransition) {
    window.location.href = url;
    return;
  }

  document.startViewTransition(async () => {
    // Perform navigation or DOM swap
    const content = await fetch(url).then(r => r.text());
    document.querySelector('main').innerHTML = content;
  });
}
```

```css
/* CSS for morphing elements */
.morph-target {
  view-transition-name: hero-image;
}

::view-transition-old(hero-image),
::view-transition-new(hero-image) {
  animation-duration: var(--dur-slow);
  animation-timing-function: var(--ease-luxury);
}
```

### 2. Scroll‑driven storytelling (GSAP ScrollTrigger)

```javascript
gsap.timeline({
  scrollTrigger: {
    trigger: ".story-section",
    start: "top top",
    end: "+=3000",
    pin: true,
    scrub: 1,
  }
})
.to(".story-card", { opacity: 1, y: 0, stagger: 0.5 })
.to(".story-bg", { scale: 1.2, duration: 1 }, "-=1");
```

### 3. Morphing shapes (clip-path transition)

```css
.morph-shape {
  clip-path: polygon(0% 0%, 100% 0%, 100% 100%, 0% 100%);
  transition: clip-path var(--dur-slow) var(--ease-spring);
}

.morph-shape:hover {
  clip-path: polygon(50% 0%, 100% 50%, 50% 100%, 0% 50%);
}
```

### 4. Reveal on scroll with 3D tilt

```css
.reveal-3d {
  opacity: 0;
  transform: perspective(1000px) rotateX(15deg) translateY(var(--space-8));
  transition: all var(--dur-slow) var(--ease-out);
}

.reveal-3d.visible {
  opacity: 1;
  transform: perspective(1000px) rotateX(0deg) translateY(0);
}
```

### 5. Smooth page transitions for Next.js

```jsx
// components/PageTransition.jsx
import { motion } from 'framer-motion';

export default function PageTransition({ children }) {
  return (
    <motion.main
      initial={{ opacity: 0, y: 10 }}
      animate={{ opacity: 1, y: 0 }}
      exit={{ opacity: 0, y: -10 }}
      transition={{ duration: 0.4, ease: "easeOut" }}
    >
      {children}
    </motion.main>
  );
}
```
