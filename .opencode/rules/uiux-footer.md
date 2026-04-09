# UI/UX Footer System 2026

> Full footer system per design language. Every footer is a complete component:
> multi-column layout, back-to-top, language selector (droptop), copyright,
> legal links, dark/light toggle placement, newsletter signup, mobile stacking.
> `role="contentinfo"` on every footer — no exceptions.

---

## FOOTER ANATOMY (all languages)

```
┌─────────────────────────────────────────────────────────┐
│  UPPER FOOTER                                           │
│  [Logo + Tagline] [Nav col 1] [Nav col 2] [Social/CTA] │
├─────────────────────────────────────────────────────────┤
│  LOWER FOOTER                                           │
│  [© Year Brand] [Legal links] [Language] [Theme toggle] │
└─────────────────────────────────────────────────────────┘
```

Mobile stacking order (top → bottom):
1. Logo + tagline
2. Newsletter / CTA (if present)
3. Navigation columns (each becomes accordion or flat list)
4. Social links
5. Divider
6. Copyright + legal links
7. Language selector + theme toggle

---

## UNIVERSAL FOOTER CSS (base — extend per language)

```css
/* ── FOOTER BASE ──────────────────────────────────────── */
.site-footer {
  background: var(--footer-bg, var(--surface));
  color: var(--footer-text, var(--text-2));
  border-top: 1px solid var(--border);
  font-size: var(--text-sm);
}

.footer-upper {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-12);
  padding: var(--space-12) 0;
}
@media (min-width: 768px) {
  .footer-upper {
    grid-template-columns: 2fr 1fr 1fr 1fr;
    gap: var(--space-8);
    padding: var(--space-16) 0;
  }
}

.footer-lower {
  border-top: 1px solid var(--border);
  padding: var(--space-6) 0;
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
  align-items: flex-start;
}
@media (min-width: 768px) {
  .footer-lower {
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
  }
}

/* Brand column */
.footer-brand { display: flex; flex-direction: column; gap: var(--space-4); }
.footer-logo  { font-family: var(--font-display); font-size: var(--text-xl);
  font-weight: 700; color: var(--text); }
.footer-tagline { font-size: var(--text-sm); color: var(--muted); max-width: 280px;
  line-height: 1.6; }

/* Nav columns */
.footer-nav { display: flex; flex-direction: column; gap: var(--space-3); }
.footer-nav-title {
  font-size: var(--text-xs); font-weight: 700; letter-spacing: 0.08em;
  text-transform: uppercase; color: var(--text); margin-bottom: var(--space-1);
}
.footer-nav-link {
  color: var(--footer-text, var(--text-2));
  transition: color var(--transition-micro);
  line-height: 1.4;
}
.footer-nav-link:hover { color: var(--text); }

/* Social icons row */
.footer-social { display: flex; gap: var(--space-3); flex-wrap: wrap; }
.footer-social-link {
  width: 40px; height: 40px; display: grid; place-items: center;
  border: 1px solid var(--border); border-radius: var(--radius-md);
  color: var(--text-2); transition: color var(--transition-micro),
    border-color var(--transition-micro), background var(--transition-micro);
}
.footer-social-link:hover { color: var(--text); background: var(--surface-up);
  border-color: var(--text-2); }
.footer-social-link svg { width: 16px; height: 16px; }

/* Copyright */
.footer-copy { font-size: var(--text-xs); color: var(--muted); }

/* Legal links */
.footer-legal { display: flex; gap: var(--space-4); flex-wrap: wrap;
  font-size: var(--text-xs); }
.footer-legal a { color: var(--muted); transition: color var(--transition-micro); }
.footer-legal a:hover { color: var(--text); }

/* Lower right: language + theme toggle */
.footer-lower-right {
  display: flex; align-items: center; gap: var(--space-4); flex-shrink: 0;
}

/* Newsletter form */
.footer-newsletter { display: flex; flex-direction: column; gap: var(--space-3); }
.footer-newsletter-form {
  display: flex; gap: var(--space-2);
  max-width: 340px;
}
.footer-newsletter-input {
  flex: 1; padding: 10px 16px;
  background: var(--bg); border: 1px solid var(--border);
  border-radius: var(--radius-md); font-size: var(--text-sm);
  color: var(--text); font-family: var(--font-body);
  transition: border-color var(--transition-micro);
}
.footer-newsletter-input:focus { border-color: var(--accent); outline: none; }
.footer-newsletter-input::placeholder { color: var(--muted); }
.footer-newsletter-btn {
  flex-shrink: 0; padding: 10px 20px;
  background: var(--accent); color: white;
  border: none; border-radius: var(--radius-md);
  font-size: var(--text-sm); font-weight: 600; cursor: pointer;
  transition: background var(--transition-micro);
  white-space: nowrap;
}
.footer-newsletter-btn:hover {
  background: color-mix(in srgb, var(--accent) 85%, black);
}
```

---

## LANGUAGE SELECTOR (droptop — opens UPWARD)

```html
<!-- Place in .footer-lower-right -->
<div class="custom-select droptop footer-lang-select" id="lang-select"
  role="combobox" aria-expanded="false" aria-haspopup="listbox"
  aria-label="Select language">
  <button class="custom-select__trigger" aria-controls="lang-list">
    <i data-lucide="globe" style="width:14px;height:14px"></i>
    <span class="custom-select__value">English</span>
    <i data-lucide="chevron-up" class="custom-select__icon" style="width:14px;height:14px"></i>
  </button>
  <ul class="custom-select__list" id="lang-list" role="listbox">
    <li class="custom-select__option" role="option" data-value="en" aria-selected="true">🇺🇸 English</li>
    <li class="custom-select__option" role="option" data-value="es">🇪🇸 Español</li>
    <li class="custom-select__option" role="option" data-value="fr">🇫🇷 Français</li>
    <li class="custom-select__option" role="option" data-value="de">🇩🇪 Deutsch</li>
    <li class="custom-select__option" role="option" data-value="ja">🇯🇵 日本語</li>
    <li class="custom-select__option" role="option" data-value="zh">🇨🇳 中文</li>
  </ul>
</div>
```

```css
/* Compact style for footer */
.footer-lang-select .custom-select__trigger {
  padding: 7px 12px; font-size: var(--text-xs);
  gap: var(--space-2); border-radius: var(--radius-sm);
}
.footer-lang-select .custom-select__list {
  min-width: 160px; font-size: var(--text-xs);
}
```

---

## BACK-TO-TOP BUTTON (universal)

```html
<!-- Place just before </body> -->
<button id="back-to-top" class="back-to-top" aria-label="Scroll back to top">
  <i data-lucide="arrow-up"></i>
</button>
```

```css
.back-to-top {
  position: fixed; bottom: var(--space-6); right: var(--space-6);
  width: 44px; height: 44px;
  background: var(--accent); color: white;
  border: none; border-radius: var(--radius-md);
  display: grid; place-items: center; cursor: pointer;
  z-index: 500;
  opacity: 0; pointer-events: none; transform: translateY(12px);
  transition:
    opacity var(--transition-base),
    transform var(--transition-spring),
    background var(--transition-micro);
  box-shadow: var(--shadow-md);
}
.back-to-top.visible { opacity: 1; pointer-events: all; transform: translateY(0); }
.back-to-top:hover { background: color-mix(in srgb, var(--accent) 85%, black);
  transform: translateY(-3px); }
.back-to-top svg { width: 18px; height: 18px; }

/* Language-specific tweaks */
[data-lang="luxury"]  .back-to-top { border-radius: 0; }
[data-lang="warm"]    .back-to-top { border-radius: var(--radius-full); }
[data-lang="technical"] .back-to-top { box-shadow: none; }

/* Mobile: don't overlap tab bar */
@media (max-width: 1023px) {
  .back-to-top { bottom: calc(var(--space-6) + 60px); }
}
```

```javascript
const btt = document.getElementById('back-to-top');
window.addEventListener('scroll', () => {
  btt.classList.toggle('visible', window.scrollY > 400);
}, { passive: true });
btt.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
```

---

## FOOTER VARIANT 1 — LUXURY

```html
<footer class="site-footer footer-luxury" role="contentinfo">
  <div class="container">
    <div class="footer-upper">

      <!-- Brand -->
      <div class="footer-brand">
        <a href="/" class="footer-logo" aria-label="Brand home">MAISON</a>
        <p class="footer-tagline">Crafted without compromise. Worn without occasion.</p>
        <div class="footer-social">
          <a href="#" class="footer-social-link" aria-label="Instagram">
            <i data-lucide="instagram"></i></a>
          <a href="#" class="footer-social-link" aria-label="Pinterest">
            <i data-lucide="image"></i></a>
        </div>
      </div>

      <!-- Collections -->
      <nav class="footer-nav" aria-label="Collections">
        <span class="footer-nav-title">Collections</span>
        <a href="#" class="footer-nav-link">Autumn Winter 2026</a>
        <a href="#" class="footer-nav-link">Resort Collection</a>
        <a href="#" class="footer-nav-link">Archive</a>
        <a href="#" class="footer-nav-link">Bespoke</a>
      </nav>

      <!-- Services -->
      <nav class="footer-nav" aria-label="Services">
        <span class="footer-nav-title">Services</span>
        <a href="#" class="footer-nav-link">Personal Styling</a>
        <a href="#" class="footer-nav-link">Alterations</a>
        <a href="#" class="footer-nav-link">Monogramming</a>
        <a href="#" class="footer-nav-link">Gift Packaging</a>
      </nav>

      <!-- Contact -->
      <nav class="footer-nav" aria-label="Contact">
        <span class="footer-nav-title">Contact</span>
        <a href="tel:+1800000000" class="footer-nav-link">+1 800 000 0000</a>
        <a href="mailto:atelier@maison.com" class="footer-nav-link">atelier@maison.com</a>
        <p class="footer-nav-link" style="cursor:default">Mon–Sat 10am–6pm</p>
      </nav>
    </div>

    <div class="footer-lower">
      <p class="footer-copy">© <span id="year-luxury"></span> Maison. All rights reserved.</p>
      <nav class="footer-legal" aria-label="Legal">
        <a href="#">Privacy Policy</a>
        <a href="#">Terms</a>
        <a href="#">Cookie Preferences</a>
        <a href="#">Accessibility</a>
      </nav>
      <div class="footer-lower-right">
        <!-- Language droptop -->
        <!-- See language selector HTML above -->
        <!-- Theme toggle: text variant for luxury -->
        <button class="theme-toggle-luxury" onclick="toggleTheme()" id="theme-toggle" aria-label="Toggle theme">
          <span data-theme-label></span>
        </button>
      </div>
    </div>
  </div>
</footer>
```

```css
/* LUXURY footer — no borders, generous whitespace, warm off-white */
.footer-luxury {
  background: var(--surface);
  border-top: none; /* no top border — luxury uses space as divider */
  padding-top: var(--space-20);
}
.footer-luxury .footer-logo {
  font-size: var(--text-sm); font-weight: 400;
  letter-spacing: 0.3em; text-transform: uppercase;
}
.footer-luxury .footer-nav-title {
  font-size: var(--text-xs); letter-spacing: 0.15em; font-weight: 400;
  color: var(--muted); margin-bottom: var(--space-3);
}
.footer-luxury .footer-nav-link {
  font-size: var(--text-sm); letter-spacing: 0.02em; line-height: 2;
}
.footer-luxury .footer-social-link {
  border-radius: 0; /* sharp corners for luxury */
}
.footer-luxury .footer-lower {
  border-top: 1px solid var(--border);
  padding: var(--space-8) 0;
}
.footer-luxury .footer-copy { font-size: var(--text-xs); letter-spacing: 0.05em; }
```

---

## FOOTER VARIANT 2 — PREMIUM MODERN

```html
<footer class="site-footer footer-premium" role="contentinfo">
  <div class="container">
    <div class="footer-upper">

      <!-- Brand + newsletter -->
      <div class="footer-brand">
        <a href="/" class="footer-logo">Streamline</a>
        <p class="footer-tagline">The productivity platform built for modern teams.</p>
        <div class="footer-newsletter">
          <p class="footer-nav-title">Stay in the loop</p>
          <form class="footer-newsletter-form" onsubmit="handleNewsletter(event)">
            <input class="footer-newsletter-input" type="email"
              placeholder="your@email.com" autocomplete="email" required
              aria-label="Email for newsletter">
            <button type="submit" class="footer-newsletter-btn">Subscribe</button>
          </form>
        </div>
      </div>

      <nav class="footer-nav" aria-label="Product">
        <span class="footer-nav-title">Product</span>
        <a href="#" class="footer-nav-link">Features</a>
        <a href="#" class="footer-nav-link">Pricing</a>
        <a href="#" class="footer-nav-link">Changelog</a>
        <a href="#" class="footer-nav-link">Roadmap</a>
        <a href="#" class="footer-nav-link">Integrations</a>
      </nav>

      <nav class="footer-nav" aria-label="Company">
        <span class="footer-nav-title">Company</span>
        <a href="#" class="footer-nav-link">About</a>
        <a href="#" class="footer-nav-link">Blog</a>
        <a href="#" class="footer-nav-link">Careers <span class="badge badge--info" style="font-size:10px;padding:2px 6px">3 open</span></a>
        <a href="#" class="footer-nav-link">Press</a>
      </nav>

      <nav class="footer-nav" aria-label="Support">
        <span class="footer-nav-title">Support</span>
        <a href="#" class="footer-nav-link">Documentation</a>
        <a href="#" class="footer-nav-link">API Reference</a>
        <a href="#" class="footer-nav-link">Status</a>
        <a href="#" class="footer-nav-link">Community</a>
      </nav>
    </div>

    <div class="footer-lower">
      <p class="footer-copy">© <span id="year-premium"></span> Streamline Inc.</p>
      <nav class="footer-legal" aria-label="Legal">
        <a href="#">Privacy</a>
        <a href="#">Terms</a>
        <a href="#">Security</a>
        <a href="#">Cookies</a>
      </nav>
      <div class="footer-lower-right">
        <!-- Language selector -->
        <!-- Theme toggle: icon button for premium -->
        <button class="theme-toggle-icon" onclick="toggleTheme()" id="theme-toggle"
          aria-label="Toggle theme">
          <i data-lucide="sun" class="icon-light"></i>
          <i data-lucide="moon" class="icon-dark"></i>
        </button>
      </div>
    </div>
  </div>
</footer>
```

```css
.footer-premium { background: var(--surface); }
/* Social icons in premium: rounded */
.footer-premium .footer-social-link { border-radius: var(--radius-md); }
/* Inline badge in nav */
.footer-premium .footer-nav-link { display: flex; align-items: center; gap: var(--space-2); }
/* Show sun/moon based on theme */
[data-theme="light"] .theme-toggle-icon .icon-dark { display: none; }
[data-theme="dark"]  .theme-toggle-icon .icon-light { display: none; }
```

---

## FOOTER VARIANT 3 — MINIMALIST

```html
<footer class="site-footer footer-minimal" role="contentinfo">
  <div class="container">
    <div class="footer-minimal-inner">
      <!-- Single row on desktop, stacked on mobile -->
      <a href="/" class="footer-logo footer-minimal-logo" aria-label="Brand home">Studio</a>

      <nav class="footer-minimal-nav" aria-label="Footer navigation">
        <a href="#work" class="footer-nav-link">Work</a>
        <a href="#about" class="footer-nav-link">About</a>
        <a href="#services" class="footer-nav-link">Services</a>
        <a href="#contact" class="footer-nav-link">Contact</a>
      </nav>

      <div class="footer-minimal-right">
        <p class="footer-copy">© <span id="year-minimal"></span></p>
        <button class="theme-toggle-luxury" onclick="toggleTheme()" aria-label="Toggle theme">
          ◐
        </button>
      </div>
    </div>
  </div>
</footer>
```

```css
.footer-minimal {
  border-top: 1px solid var(--border);
  padding: var(--space-8) 0;
}
.footer-minimal-inner {
  display: flex; flex-direction: column; gap: var(--space-6);
}
@media (min-width: 768px) {
  .footer-minimal-inner {
    flex-direction: row; align-items: center;
    justify-content: space-between;
  }
}
.footer-minimal-logo { font-size: var(--text-sm); letter-spacing: 0.2em;
  text-transform: uppercase; font-weight: 400; }
.footer-minimal-nav { display: flex; gap: var(--space-6); flex-wrap: wrap; }
.footer-minimal-nav .footer-nav-link {
  font-size: var(--text-xs); letter-spacing: 0.06em; text-transform: uppercase;
}
.footer-minimal-right { display: flex; align-items: center; gap: var(--space-4); }
/* NO newsletter, NO social icons — restraint IS the brand */
```

---

## FOOTER VARIANT 4 — EXPRESSIVE / BOLD

```html
<footer class="site-footer footer-expressive" role="contentinfo">
  <div class="footer-expressive-top">
    <!-- Full-width CTA band -->
    <div class="container footer-expressive-cta">
      <p class="footer-expressive-headline">Let's make something <em>loud</em>.</p>
      <a href="/contact" class="btn btn-expressive btn--xl btn--primary">Start a project</a>
    </div>
  </div>

  <div class="container">
    <div class="footer-upper">
      <div class="footer-brand">
        <a href="/" class="footer-logo">NEON/PULSE</a>
        <p class="footer-tagline">We build experiences that refuse to be ignored.</p>
        <div class="footer-social">
          <a href="#" class="footer-social-link" aria-label="Instagram"><i data-lucide="instagram"></i></a>
          <a href="#" class="footer-social-link" aria-label="TikTok"><i data-lucide="music"></i></a>
          <a href="#" class="footer-social-link" aria-label="X/Twitter"><i data-lucide="twitter"></i></a>
          <a href="#" class="footer-social-link" aria-label="YouTube"><i data-lucide="youtube"></i></a>
        </div>
      </div>

      <nav class="footer-nav" aria-label="Work">
        <span class="footer-nav-title">Work</span>
        <a href="#" class="footer-nav-link">Case Studies</a>
        <a href="#" class="footer-nav-link">Motion</a>
        <a href="#" class="footer-nav-link">Brand</a>
        <a href="#" class="footer-nav-link">Interactive</a>
      </nav>

      <nav class="footer-nav" aria-label="Info">
        <span class="footer-nav-title">Info</span>
        <a href="#" class="footer-nav-link">About</a>
        <a href="#" class="footer-nav-link">Manifesto</a>
        <a href="#" class="footer-nav-link">Awards</a>
        <a href="#" class="footer-nav-link">Contact</a>
      </nav>
    </div>

    <div class="footer-lower">
      <p class="footer-copy">© <span id="year-expr"></span> NEON/PULSE STUDIO</p>
      <nav class="footer-legal" aria-label="Legal">
        <a href="#">Privacy</a><a href="#">Terms</a>
      </nav>
      <div class="footer-lower-right">
        <!-- Language selector -->
        <button class="theme-toggle-icon" onclick="toggleTheme()" aria-label="Toggle theme">
          <i data-lucide="zap"></i>
        </button>
      </div>
    </div>
  </div>
</footer>
```

```css
.footer-expressive {
  background: var(--surface);
  border-top: 3px solid var(--accent);
}
.footer-expressive-top {
  background: var(--bg);
  padding: var(--space-20) 0;
  border-bottom: 1px solid var(--border);
}
.footer-expressive-cta {
  display: flex; flex-direction: column; gap: var(--space-8);
}
@media (min-width: 768px) {
  .footer-expressive-cta {
    flex-direction: row; justify-content: space-between; align-items: center;
  }
}
.footer-expressive-headline {
  font-family: var(--font-display);
  font-size: clamp(2rem, 5vw, 4rem);
  font-weight: 800; letter-spacing: -0.03em;
  color: var(--text); line-height: 1;
}
.footer-expressive-headline em {
  font-style: normal;
  background: var(--gradient-text, var(--accent));
  -webkit-background-clip: text; -webkit-text-fill-color: transparent;
  background-clip: text;
}
.footer-expressive .footer-logo {
  font-size: var(--text-base); letter-spacing: 0.15em;
  font-weight: 800;
}
.footer-expressive .footer-social-link {
  border-radius: 0; /* sharp squares for expressive */
}
```

---

## FOOTER VARIANT 5 — WARM & HUMAN

```html
<footer class="site-footer footer-warm" role="contentinfo">
  <div class="container">
    <div class="footer-upper">
      <div class="footer-brand">
        <a href="/" class="footer-logo">Kindly</a>
        <p class="footer-tagline">Healthcare made simple, human, and kind.</p>
        <!-- App download badges -->
        <div class="footer-app-badges">
          <a href="#" class="footer-badge-link" aria-label="Download on the App Store">
            <img src="https://via.placeholder.com/120x40/FDFAF6/2D2520?text=App+Store"
              width="120" height="40" alt="App Store" loading="lazy">
          </a>
          <a href="#" class="footer-badge-link" aria-label="Get it on Google Play">
            <img src="https://via.placeholder.com/120x40/FDFAF6/2D2520?text=Google+Play"
              width="120" height="40" alt="Google Play" loading="lazy">
          </a>
        </div>
      </div>

      <nav class="footer-nav" aria-label="Services">
        <span class="footer-nav-title">Services</span>
        <a href="#" class="footer-nav-link">Mental Health</a>
        <a href="#" class="footer-nav-link">Primary Care</a>
        <a href="#" class="footer-nav-link">Nutrition</a>
        <a href="#" class="footer-nav-link">Coaching</a>
      </nav>

      <nav class="footer-nav" aria-label="Support">
        <span class="footer-nav-title">Support</span>
        <a href="#" class="footer-nav-link">Help Center</a>
        <a href="#" class="footer-nav-link">Crisis Line</a>
        <a href="#" class="footer-nav-link">Providers</a>
        <a href="#" class="footer-nav-link">Partnerships</a>
      </nav>

      <div class="footer-nav">
        <span class="footer-nav-title">Join our community</span>
        <div class="footer-newsletter">
          <form class="footer-newsletter-form" onsubmit="handleNewsletter(event)">
            <input class="footer-newsletter-input" type="email"
              placeholder="your@email.com" aria-label="Email" required>
            <button type="submit" class="footer-newsletter-btn">Join</button>
          </form>
          <p style="font-size:var(--text-xs);color:var(--muted)">
            No spam. Unsubscribe anytime.
          </p>
        </div>
        <div class="footer-social" style="margin-top:var(--space-4)">
          <a href="#" class="footer-social-link" aria-label="Instagram"><i data-lucide="instagram"></i></a>
          <a href="#" class="footer-social-link" aria-label="Facebook"><i data-lucide="facebook"></i></a>
          <a href="#" class="footer-social-link" aria-label="TikTok"><i data-lucide="music"></i></a>
        </div>
      </div>
    </div>

    <div class="footer-lower">
      <p class="footer-copy">© <span id="year-warm"></span> Kindly Health, Inc.</p>
      <nav class="footer-legal" aria-label="Legal">
        <a href="#">Privacy</a>
        <a href="#">Terms</a>
        <a href="#">HIPAA Notice</a>
        <a href="#">Accessibility</a>
        <a href="#">Cookie Preferences</a>
      </nav>
      <div class="footer-lower-right">
        <!-- Language + pill theme toggle for warm -->
        <button class="theme-toggle-pill" onclick="toggleTheme()" id="theme-toggle"
          role="switch" aria-checked="false" aria-label="Toggle dark mode"></button>
      </div>
    </div>
  </div>
</footer>
```

```css
.footer-warm {
  background: var(--surface);
  border-top: none;
  padding-top: var(--space-4);
}
.footer-warm::before {
  content: '';
  display: block;
  height: 4px;
  background: linear-gradient(90deg, var(--accent), color-mix(in srgb, var(--accent) 50%, #FFB347));
  border-radius: var(--radius-full);
  margin-bottom: var(--space-12);
}
.footer-warm .footer-logo { font-size: var(--text-2xl); font-weight: 800; }
.footer-warm .footer-social-link { border-radius: var(--radius-full); }
.footer-warm .footer-newsletter-input { border-radius: var(--radius-full); }
.footer-warm .footer-newsletter-btn  { border-radius: var(--radius-full); }
.footer-app-badges { display: flex; gap: var(--space-3); flex-wrap: wrap; }
.footer-badge-link img { border-radius: var(--radius-sm); }
```

---

## FOOTER VARIANT 6 — TECHNICAL

```html
<footer class="site-footer footer-technical" role="contentinfo">
  <div class="container">
    <div class="footer-upper footer-upper--technical">

      <div class="footer-brand">
        <a href="/" class="footer-logo footer-logo--technical" aria-label="Home">
          <i data-lucide="terminal" style="width:16px;height:16px"></i>
          devframe
        </a>
        <p class="footer-tagline">Open-source infrastructure for modern engineering teams.</p>
        <div class="footer-tech-stats">
          <span class="footer-stat">
            <i data-lucide="star" style="width:12px;height:12px"></i>
            12.4k stars
          </span>
          <span class="footer-stat">
            <i data-lucide="git-fork" style="width:12px;height:12px"></i>
            1.2k forks
          </span>
          <span class="footer-stat footer-stat--online">
            <span class="status-dot"></span>
            All systems operational
          </span>
        </div>
      </div>

      <nav class="footer-nav" aria-label="Product">
        <span class="footer-nav-title">Product</span>
        <a href="#" class="footer-nav-link">Documentation</a>
        <a href="#" class="footer-nav-link">API Reference</a>
        <a href="#" class="footer-nav-link">Changelog</a>
        <a href="#" class="footer-nav-link">SDK</a>
        <a href="#" class="footer-nav-link">CLI</a>
      </nav>

      <nav class="footer-nav" aria-label="Community">
        <span class="footer-nav-title">Community</span>
        <a href="#" class="footer-nav-link">GitHub</a>
        <a href="#" class="footer-nav-link">Discord</a>
        <a href="#" class="footer-nav-link">Stack Overflow</a>
        <a href="#" class="footer-nav-link">Blog</a>
        <a href="#" class="footer-nav-link">Contribute</a>
      </nav>

      <nav class="footer-nav" aria-label="Legal">
        <span class="footer-nav-title">Legal</span>
        <a href="#" class="footer-nav-link">Privacy Policy</a>
        <a href="#" class="footer-nav-link">Terms of Service</a>
        <a href="#" class="footer-nav-link">License (MIT)</a>
        <a href="#" class="footer-nav-link">Security</a>
      </nav>
    </div>

    <div class="footer-lower">
      <p class="footer-copy footer-copy--technical">
        <code>© <span id="year-tech"></span> devframe</code>
        · Built with ❤️ in San Francisco
      </p>
      <div class="footer-lower-right">
        <!-- Language selector (compact) -->
        <!-- Theme toggle: icon -->
        <button class="theme-toggle-icon theme-toggle-icon--sm" onclick="toggleTheme()"
          aria-label="Toggle theme">
          <i data-lucide="monitor"></i>
        </button>
      </div>
    </div>
  </div>
</footer>
```

```css
.footer-technical {
  background: var(--surface);
  border-top: 1px solid var(--border);
  font-size: var(--text-xs);
  font-family: var(--font-body);
}
.footer-upper--technical {
  padding: var(--space-8) 0;
}
@media (min-width: 768px) {
  .footer-upper--technical {
    grid-template-columns: 1.5fr 1fr 1fr 1fr;
  }
}
.footer-logo--technical {
  display: flex; align-items: center; gap: var(--space-2);
  font-family: var(--font-mono); font-size: var(--text-sm); font-weight: 700;
  letter-spacing: -0.02em;
}
.footer-technical .footer-nav-title {
  font-family: var(--font-mono); font-size: 10px; letter-spacing: 0.08em;
}
.footer-technical .footer-nav-link { font-size: var(--text-xs); line-height: 2; }

/* Tech stats row */
.footer-tech-stats { display: flex; flex-wrap: wrap; gap: var(--space-3);
  margin-top: var(--space-4); }
.footer-stat { display: flex; align-items: center; gap: 4px;
  font-size: 11px; color: var(--muted); font-family: var(--font-mono); }
.footer-stat--online .status-dot {
  width: 6px; height: 6px; border-radius: 50%; background: var(--color-success);
  animation: pulse 2s ease-in-out infinite;
}
.footer-copy--technical { font-family: var(--font-mono); font-size: 11px; }
.theme-toggle-icon--sm { width: 32px; height: 32px; border-radius: 4px; }
```

---

## UNIVERSAL FOOTER JS

```javascript
// ── YEAR AUTO-UPDATE ───────────────────────────────────
document.querySelectorAll('[id^="year-"]').forEach(el => {
  el.textContent = new Date().getFullYear();
});

// ── NEWSLETTER SUBMIT ─────────────────────────────────
function handleNewsletter(e) {
  e.preventDefault();
  const input = e.target.querySelector('input[type="email"]');
  const email = input.value;
  // Replace with real API call
  swal.toast(`You're subscribed! Welcome aboard.`, 'success');
  e.target.reset();
}

// ── THEME TOGGLE aria-checked sync ──────────────────
function syncThemeToggleAria() {
  const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
  document.querySelectorAll('#theme-toggle[role="switch"]').forEach(btn => {
    btn.setAttribute('aria-checked', isDark ? 'true' : 'false');
  });
}
// Call after initTheme() and after toggleTheme()
const _toggleTheme = toggleTheme;
window.toggleTheme = function() { _toggleTheme(); syncThemeToggleAria(); };
syncThemeToggleAria();

// ── LANGUAGE CHANGE ────────────────────────────────────
document.querySelectorAll('.footer-lang-select').forEach(sel => {
  sel.addEventListener('change', () => {
    const lang = sel.querySelector('.custom-select__value')?.textContent;
    // Implement i18n routing or query param: ?lang=es
    swal.toast(`Language changed to ${lang}`, 'info');
  });
});
```

---

## FOOTER QUICK REFERENCE

| Variant | Logo style | Newsletter | Social | Theme toggle | Unique element |
|---------|-----------|------------|--------|--------------|----------------|
| **Luxury** | Uppercase tracking | No | 2 icons, sharp | Text "LIGHT/DARK" | Generous padding, no top border |
| **Premium** | Bold, modern | Yes, inline | Icon row | Icon button | Job openings badge |
| **Minimalist** | Tracking, light | No | None | ◐ symbol | Single-row, ultra-sparse |
| **Expressive** | Heavy weight | No | 4 icons, sharp | Icon (⚡) | Full-width CTA band above |
| **Warm** | Rounded, heavy | Yes, pill | 3 icons, circle | Pill toggle | App store badges, gradient top bar |
| **Technical** | Mono, icon | No | GitHub/Discord | Monitor icon | Repo stats, status dot, monospace |
