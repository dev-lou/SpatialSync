---
description: Advanced content components including tooltips, popovers, carousels, video players, lightboxes, avatars, ratings, progress bars, steppers, and code blocks.
---

# UI/UX Content Components 2026

> Tooltips, popovers, carousels, video players, lightboxes, 
> avatars, ratings, progress bars, steppers, and code blocks.

---

## 1. TOOLTIP (Delay & Positioning)

```html
<button class="btn btn--icon" data-tooltip="Search (⌘K)">
  <i data-lucide="search"></i>
</button>
```

```css
.tooltip {
  position: absolute;
  background: var(--text);
  color: var(--bg);
  padding: var(--space-1) var(--space-2);
  border-radius: var(--radius-sm);
  font-size: var(--text-xs);
  pointer-events: none;
  opacity: 0;
  transition: opacity var(--dur-micro) var(--ease-out);
  z-index: 1000;
}

.tooltip.visible { opacity: 1; }
```

---

## 2. POPOVER (Rich Content & Arrow)

```html
<div class="popover" role="dialog" aria-modal="true">
  <div class="popover__content">
    <h3>Notifications</h3>
    <p>You have 3 new messages.</p>
  </div>
  <div class="popover__arrow"></div>
</div>
```

```css
.popover {
  position: absolute;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
  box-shadow: var(--shadow-lg);
  padding: var(--space-4);
  z-index: 1000;
}

.popover__arrow {
  position: absolute;
  width: 8px; height: 8px;
  background: var(--surface);
  border-left: 1px solid var(--border);
  border-top: 1px solid var(--border);
  transform: rotate(45deg);
  top: -5px; left: 50%;
  margin-left: -4px;
}
```

---

## 3. CAROUSEL / SLIDER (Swiper Usage)

```html
<div class="swiper">
  <div class="swiper-wrapper">
    <div class="swiper-slide">Slide 1</div>
    <div class="swiper-slide">Slide 2</div>
  </div>
  <div class="swiper-pagination"></div>
  <div class="swiper-button-prev"></div>
  <div class="swiper-button-next"></div>
</div>
```

```css
.swiper-slide {
  background: var(--surface);
  border-radius: var(--radius-lg);
  padding: var(--space-8);
  display: flex;
  align-items: center;
  justify-content: center;
}

.swiper-button-next, .swiper-button-prev {
  color: var(--accent);
}
```

---

## 4. VIDEO PLAYER WRAPPER (Custom Controls)

```html
<div class="video-player">
  <video src="video.mp4" class="video-player__media"></video>
  <div class="video-player__controls">
    <button class="btn btn--icon play-btn"><i data-lucide="play"></i></button>
    <div class="video-player__progress">
      <div class="video-player__progress-bar"></div>
    </div>
    <button class="btn btn--icon volume-btn"><i data-lucide="volume-2"></i></button>
  </div>
</div>
```

```css
.video-player {
  position: relative;
  border-radius: var(--radius-lg);
  overflow: hidden;
  background: black;
}

.video-player__controls {
  position: absolute;
  bottom: 0; left: 0; right: 0;
  padding: var(--space-4);
  background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
  display: flex;
  align-items: center;
  gap: var(--space-4);
  opacity: 0;
  transition: opacity var(--dur-base) var(--ease-out);
}

.video-player:hover .video-player__controls { opacity: 1; }
```

---

## 5. AVATAR / INITIALS (Fallback & Indicator)

```html
<div class="avatar avatar--online">
  <img src="user.jpg" alt="User Avatar" class="avatar__img" onerror="this.style.display='none'">
  <span class="avatar__fallback">JD</span>
  <span class="avatar__indicator"></span>
</div>
```

```css
.avatar {
  position: relative;
  width: 40px; height: 40px;
  border-radius: 50%;
  background: var(--surface-up);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  color: var(--text-2);
}

.avatar__img {
  position: absolute; inset: 0;
  width: 100%; height: 100%;
  border-radius: 50%;
  object-fit: cover;
}

.avatar__indicator {
  position: absolute; bottom: 0; right: 0;
  width: 10px; height: 10px;
  border-radius: 50%;
  background: var(--color-success);
  border: 2px solid var(--bg);
}
```

---

## 6. RATING STARS (Interactive)

```html
<div class="rating" role="radiogroup" aria-label="Rating">
  <button role="radio" aria-checked="true" aria-label="1 star"><i data-lucide="star" class="fill-accent"></i></button>
  <button role="radio" aria-checked="false" aria-label="2 stars"><i data-lucide="star"></i></button>
  <!-- ... -->
</div>
```

```css
.rating {
  display: flex;
  gap: var(--space-1);
  color: var(--muted);
}

.rating button:hover, .rating button.active {
  color: var(--accent);
}
```

---

## 7. PROGRESS BAR (Linear & Circular)

```css
.progress-linear {
  width: 100%; height: 8px;
  background: var(--surface-up);
  border-radius: var(--radius-full);
  overflow: hidden;
}

.progress-linear__bar {
  height: 100%;
  background: var(--accent);
  transition: width var(--dur-slow) var(--ease-out);
}
```

---

## 8. CODE BLOCK (Syntax Highlighting & Copy)

```html
<div class="code-block">
  <header class="code-block__header">
    <span>index.js</span>
    <button class="btn btn--copy" data-copy="console.log('Hello World');">
      <i data-lucide="copy"></i>
    </button>
  </header>
  <pre><code class="language-javascript">console.log('Hello World');</code></pre>
</div>
```

```css
.code-block {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
  overflow: hidden;
}

.code-block__header {
  padding: var(--space-2) var(--space-4);
  background: var(--surface-up);
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: var(--text-xs);
  color: var(--muted);
}
```
