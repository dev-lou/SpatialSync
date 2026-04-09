---
description: Creativity skill for generating fresh, context-specific wow effects. Includes 20+ idea categories, decision tree, mobile gestures, and memory prompt. All ideas use design tokens and are accessible.
---

# UI/UX Unique Ideas Generator

> Generate fresh, context-specific wow effects that never repeat.
> Every effect uses design tokens and respects accessibility.

---

## MEMORY PROMPT

**Before generating effects, ask:**

> "What effects did you use in the last session/project?"

This ensures variety across projects. Track previously used effects to avoid repetition.

---

## DECISION TREE

```
START
  │
  ├─ What is the design language?
  │   ├─ Luxury → Slow, elegant, minimal animations
  │   ├─ Expressive → Bold, energetic, playful animations
  │   ├─ Minimalist → Subtle, precise, barely-there effects
  │   ├─ Technical → Functional, data-driven, no decorative
  │   └─ Other → Balance between subtle and engaging
  │
  ├─ What is the context?
  │   ├─ Serious (legal, medical, gov) → Avoid playful effects
  │   ├─ Creative (portfolio, agency) → More freedom for wow
  │   ├─ E-commerce → Focus on product, subtle UI effects
  │   └─ SaaS → Professional polish, micro-interactions
  │
  ├─ What is the primary device?
  │   ├─ Mobile → Touch gestures, haptic feedback
  │   ├─ Desktop → Hover effects, cursor interactions
  │   └─ Both → Progressive enhancement
  │
  └─ What element needs enhancement?
      ├─ Hero section → Entry animations, scroll effects
      ├─ Navigation → Hover states, transitions
      ├─ Cards/Grid → Hover reveals, stagger animations
      ├─ CTAs → Attention-grabbing, subtle motion
      └─ Data viz → Dynamic updates, transitions
```

---

## IDEA CATEGORIES (20+)

### 1. SCROLL EFFECTS

#### Parallax Depth Layers
```css
.parallax-container {
  perspective: 1000px;
  perspective-origin: center;
}

.parallax-layer--back {
  transform: translateZ(-200px) scale(1.2);
}

.parallax-layer--mid {
  transform: translateZ(-100px) scale(1.1);
}

.parallax-layer--front {
  transform: translateZ(0);
}
```

#### Scroll-Triggered Reveals
```css
.reveal {
  opacity: 0;
  transform: translateY(var(--space-8));
  transition: opacity var(--dur-slow), transform var(--dur-slow);
}

.reveal.visible {
  opacity: 1;
  transform: translateY(0);
}

@media (prefers-reduced-motion: reduce) {
  .reveal {
    opacity: 1;
    transform: none;
    transition: none;
  }
}
```

#### Horizontal Scroll Sections
```css
.horizontal-scroll {
  display: flex;
  overflow-x: auto;
  scroll-snap-type: x mandatory;
  scrollbar-width: none;
}

.horizontal-scroll > * {
  flex: 0 0 100vw;
  scroll-snap-align: start;
}
```

---

### 2. HOVER EFFECTS

#### Magnetic Buttons (Luxury/Expressive only)
```javascript
function magneticButton(button, strength = 0.3) {
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  
  button.addEventListener('mousemove', (e) => {
    const rect = button.getBoundingClientRect();
    const x = e.clientX - rect.left - rect.width / 2;
    const y = e.clientY - rect.top - rect.height / 2;
    
    button.style.transform = `translate(${x * strength}px, ${y * strength}px)`;
  });
  
  button.addEventListener('mouseleave', () => {
    button.style.transform = '';
  });
}
```

#### Image Reveal on Hover
```css
.image-reveal {
  position: relative;
  overflow: hidden;
}

.image-reveal img {
  transform: scale(1.1);
  transition: transform var(--dur-slow) var(--ease-out);
}

.image-reveal:hover img {
  transform: scale(1);
}

.image-reveal::after {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(to top, var(--bg) 0%, transparent 50%);
  opacity: 1;
  transition: opacity var(--dur-base);
}

.image-reveal:hover::after {
  opacity: 0;
}
```

#### Text Scramble Effect
```javascript
class TextScramble {
  constructor(element) {
    this.element = element;
    this.chars = '!<>-_\\/[]{}—=+*^?#________';
    this.queue = [];
    this.frame = 0;
    this.resolve = null;
  }
  
  setText(newText) {
    const oldText = this.element.innerText;
    const length = Math.max(oldText.length, newText.length);
    
    return new Promise((resolve) => {
      this.resolve = resolve;
      this.queue = [];
      
      for (let i = 0; i < length; i++) {
        const from = oldText[i] || '';
        const to = newText[i] || '';
        const start = Math.floor(Math.random() * 40);
        const end = start + Math.floor(Math.random() * 40);
        this.queue.push({ from, to, start, end });
      }
      
      cancelAnimationFrame(this.frameRequest);
      this.frame = 0;
      this.update();
    });
  }
  
  update() {
    let output = '';
    let complete = 0;
    
    for (let i = 0; i < this.queue.length; i++) {
      let { from, to, start, end, char } = this.queue[i];
      
      if (this.frame >= end) {
        complete++;
        output += to;
      } else if (this.frame >= start) {
        if (!char || Math.random() < 0.28) {
          char = this.chars[Math.floor(Math.random() * this.chars.length)];
          this.queue[i].char = char;
        }
        output += char;
      } else {
        output += from;
      }
    }
    
    this.element.innerText = output;
    
    if (complete === this.queue.length) {
      this.resolve();
    } else {
      this.frameRequest = requestAnimationFrame(() => this.update());
      this.frame++;
    }
  }
}
```

---

### 3. CURSOR EFFECTS (Desktop only)

#### Custom Cursor Trail
```javascript
function createCursorTrail() {
  if (window.matchMedia('(pointer: coarse)').matches) return;
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  
  const trail = [];
  const numDots = 10;
  
  for (let i = 0; i < numDots; i++) {
    const dot = document.createElement('div');
    dot.className = 'cursor-trail-dot';
    dot.style.cssText = `
      position: fixed;
      width: ${8 - i * 0.6}px;
      height: ${8 - i * 0.6}px;
      background: var(--accent);
      border-radius: 50%;
      pointer-events: none;
      z-index: 9999;
      opacity: ${1 - i * 0.1};
      transition: transform 0.1s ease-out;
    `;
    document.body.appendChild(dot);
    trail.push({ element: dot, x: 0, y: 0 });
  }
  
  document.addEventListener('mousemove', (e) => {
    trail[0].x = e.clientX;
    trail[0].y = e.clientY;
  });
  
  function animate() {
    for (let i = 1; i < trail.length; i++) {
      trail[i].x += (trail[i - 1].x - trail[i].x) * 0.3;
      trail[i].y += (trail[i - 1].y - trail[i].y) * 0.3;
    }
    
    trail.forEach((dot) => {
      dot.element.style.transform = `translate(${dot.x}px, ${dot.y}px)`;
    });
    
    requestAnimationFrame(animate);
  }
  
  animate();
}
```

#### Glow Follow Effect
```css
.glow-card {
  position: relative;
  overflow: hidden;
}

.glow-card::before {
  content: '';
  position: absolute;
  width: 200px;
  height: 200px;
  background: radial-gradient(circle, var(--accent) 0%, transparent 70%);
  opacity: 0;
  transition: opacity var(--dur-base);
  pointer-events: none;
  transform: translate(-50%, -50%);
}

.glow-card:hover::before {
  opacity: 0.15;
}
```

```javascript
document.querySelectorAll('.glow-card').forEach(card => {
  card.addEventListener('mousemove', (e) => {
    const rect = card.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;
    card.style.setProperty('--mouse-x', `${x}px`);
    card.style.setProperty('--mouse-y', `${y}px`);
  });
});
```

---

### 4. LOADING & TRANSITIONS

#### Page Transition Curtain
```css
.page-transition {
  position: fixed;
  inset: 0;
  background: var(--accent);
  transform: translateY(100%);
  z-index: 9999;
}

.page-transition.entering {
  animation: curtain-in var(--dur-slow) var(--ease-out) forwards;
}

.page-transition.exiting {
  animation: curtain-out var(--dur-slow) var(--ease-out) forwards;
}

@keyframes curtain-in {
  to { transform: translateY(0); }
}

@keyframes curtain-out {
  from { transform: translateY(0); }
  to { transform: translateY(-100%); }
}
```

#### Morphing Loader
```css
.morph-loader {
  width: 48px;
  height: 48px;
  background: var(--accent);
  animation: morph 2s ease-in-out infinite;
}

@keyframes morph {
  0%, 100% {
    border-radius: 50% 50% 50% 50% / 50% 50% 50% 50%;
    transform: rotate(0deg);
  }
  25% {
    border-radius: 50% 50% 20% 50% / 50% 20% 50% 50%;
    transform: rotate(90deg);
  }
  50% {
    border-radius: 20% 50% 50% 50% / 50% 50% 50% 20%;
    transform: rotate(180deg);
  }
  75% {
    border-radius: 50% 20% 50% 50% / 20% 50% 50% 50%;
    transform: rotate(270deg);
  }
}
```

---

### 5. MOBILE GESTURES

#### Swipe Actions
```typescript
function useSwipe(onSwipe: (direction: 'left' | 'right') => void, threshold = 50) {
  const touchStart = useRef<number | null>(null);
  
  const handleTouchStart = (e: React.TouchEvent) => {
    touchStart.current = e.touches[0].clientX;
  };
  
  const handleTouchEnd = (e: React.TouchEvent) => {
    if (touchStart.current === null) return;
    
    const touchEnd = e.changedTouches[0].clientX;
    const diff = touchStart.current - touchEnd;
    
    if (Math.abs(diff) > threshold) {
      onSwipe(diff > 0 ? 'left' : 'right');
    }
    
    touchStart.current = null;
  };
  
  return { handleTouchStart, handleTouchEnd };
}
```

#### Long Press Menu
```typescript
function useLongPress(callback: () => void, duration = 500) {
  const timer = useRef<NodeJS.Timeout | null>(null);
  
  const start = () => {
    timer.current = setTimeout(callback, duration);
    // Optionally add haptic feedback
    if ('vibrate' in navigator) {
      navigator.vibrate(50);
    }
  };
  
  const cancel = () => {
    if (timer.current) {
      clearTimeout(timer.current);
      timer.current = null;
    }
  };
  
  return {
    onTouchStart: start,
    onTouchEnd: cancel,
    onTouchMove: cancel,
    onMouseDown: start,
    onMouseUp: cancel,
    onMouseLeave: cancel,
  };
}
```

---

### 6. TEXT EFFECTS

#### Gradient Text Animation
```css
.gradient-text {
  background: linear-gradient(
    90deg,
    var(--accent),
    var(--accent-secondary),
    var(--accent)
  );
  background-size: 200% 100%;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  animation: gradient-shift 3s ease infinite;
}

@keyframes gradient-shift {
  0%, 100% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
}

@media (prefers-reduced-motion: reduce) {
  .gradient-text {
    animation: none;
    background-position: 0% 50%;
  }
}
```

#### Split Text Reveal
```javascript
function splitTextReveal(element, delay = 50) {
  const text = element.textContent;
  element.textContent = '';
  element.setAttribute('aria-label', text);
  
  [...text].forEach((char, i) => {
    const span = document.createElement('span');
    span.textContent = char === ' ' ? '\u00A0' : char;
    span.style.cssText = `
      display: inline-block;
      opacity: 0;
      transform: translateY(20px);
      animation: char-reveal var(--dur-base) var(--ease-out) forwards;
      animation-delay: ${i * delay}ms;
    `;
    span.setAttribute('aria-hidden', 'true');
    element.appendChild(span);
  });
}
```

---

### 7. CARD EFFECTS

#### 3D Tilt on Hover
```javascript
function tiltCard(card, maxTilt = 10) {
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  
  card.addEventListener('mousemove', (e) => {
    const rect = card.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;
    
    const centerX = rect.width / 2;
    const centerY = rect.height / 2;
    
    const tiltX = ((y - centerY) / centerY) * maxTilt;
    const tiltY = ((centerX - x) / centerX) * maxTilt;
    
    card.style.transform = `perspective(1000px) rotateX(${tiltX}deg) rotateY(${tiltY}deg)`;
  });
  
  card.addEventListener('mouseleave', () => {
    card.style.transform = '';
  });
}
```

#### Staggered Grid Animation
```css
.stagger-grid > * {
  opacity: 0;
  transform: translateY(var(--space-4));
  animation: stagger-in var(--dur-base) var(--ease-out) forwards;
}

.stagger-grid > *:nth-child(1) { animation-delay: 0ms; }
.stagger-grid > *:nth-child(2) { animation-delay: 50ms; }
.stagger-grid > *:nth-child(3) { animation-delay: 100ms; }
.stagger-grid > *:nth-child(4) { animation-delay: 150ms; }
.stagger-grid > *:nth-child(5) { animation-delay: 200ms; }
.stagger-grid > *:nth-child(6) { animation-delay: 250ms; }

@keyframes stagger-in {
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
```

---

### 8. BACKGROUND EFFECTS

#### Animated Mesh Gradient
```css
.mesh-gradient {
  background: linear-gradient(
    45deg,
    var(--accent) 0%,
    var(--accent-secondary) 50%,
    var(--accent) 100%
  );
  background-size: 400% 400%;
  animation: mesh-move 15s ease infinite;
}

@keyframes mesh-move {
  0%, 100% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
}
```

#### Floating Particles
```javascript
function createParticles(container, count = 30) {
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  
  for (let i = 0; i < count; i++) {
    const particle = document.createElement('div');
    particle.className = 'particle';
    particle.style.cssText = `
      position: absolute;
      width: ${Math.random() * 4 + 2}px;
      height: ${Math.random() * 4 + 2}px;
      background: var(--accent);
      border-radius: 50%;
      opacity: ${Math.random() * 0.5 + 0.2};
      left: ${Math.random() * 100}%;
      top: ${Math.random() * 100}%;
      animation: float ${Math.random() * 10 + 10}s ease-in-out infinite;
      animation-delay: ${Math.random() * 5}s;
    `;
    container.appendChild(particle);
  }
}
```

---

### MORE CATEGORIES

9. **Image Effects**: Ken Burns, reveal masks, color shifts
10. **Form Interactions**: Focus animations, validation feedback
11. **Navigation**: Slide menus, morph buttons, breadcrumb animations
12. **Data Visualization**: Chart transitions, number counters
13. **Notification Effects**: Bounce, shake, pulse
14. **Progress Indicators**: Step animations, circular progress
15. **Audio Visualization**: React to sound (with permission)
16. **Scroll-linked Timelines**: CSS scroll-driven animations
17. **View Transitions**: Native page transitions API
18. **Morphing Shapes**: SVG path animations
19. **Typography Animations**: Variable font animations
20. **AR/3D Elements**: WebGL, Three.js integrations

---

## USAGE PROMPT FOR AI

When generating unique effects:

```
1. First, check: "What effects were used in the previous session?"
2. Select effect category based on:
   - Design language
   - Context (serious vs creative)
   - Primary device
   - Element type
3. Generate effect with:
   - Full CSS/JS code
   - Design token usage
   - prefers-reduced-motion handling
   - Accessibility considerations
4. Track: Add to "used effects" list for this project
```

---

## ACCESSIBILITY CHECKLIST

Every effect must:

- [ ] Respect `prefers-reduced-motion`
- [ ] Not rely on animation for information
- [ ] Work without JavaScript (progressive enhancement)
- [ ] Not cause seizures (no rapid flashing)
- [ ] Use design tokens
- [ ] Have instant feedback (<100ms user feedback)
- [ ] Not block user interaction
