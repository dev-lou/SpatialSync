# UI/UX 2026 — Project Rules (v11)

## Quick Start: OpenCode UI/UX 2026 Core Principles

This document outlines the essential non-negotiable laws and the proactive reading rule for OpenCode UI/UX 2026 design system. Adherence to these principles ensures consistency, performance, and accessibility across all projects.

**Non-Negotiable Laws (36)**: These are fundamental design and development mandates covering aspects like design language classification, token usage, accessibility (a11y), performance, animation best practices, and form handling. Key directives include mobile-first design, strict adherence to an 8px spacing grid, and comprehensive state management for interactive components (now 10 states including "Busy"). Always prioritize these laws; they are enforced without exception.

**Critical Rule (v10):** Any component that would normally use a native browser UI (date picker, time picker, color picker, select dropdown, alerts) must be replaced with a custom, token-styled component. No native browser UI allowed – all colors, spacing, radii, and fonts must come from design tokens.

**New in v11:** Advanced sensory UI (spatial 3D, voice, sound design), context-aware interfaces (temporal, ambient), delight patterns (easter eggs, loading storytelling, microcopy), ethics (anti-dark patterns, trust signals), and emerging tech (WebXR, biometric auth).

**Proactive Reading Rule**: Before planning or implementing any UI/UX task, identify and load relevant rule files from `.opencode/rules/` using the `read_file` tool. Base all responses and implementations on the rules read, as they override generic knowledge. This ensures context-aware and compliant execution.

By internalizing these core principles, AI agents can efficiently and effectively contribute to the OpenCode UI/UX 2026 design system, maintaining high standards of quality and consistency.

---

## How OpenCode uses this file
OpenCode reads this AGENTS.md at the start of every session. The detailed rule files in `.opencode/rules/` are **not** auto‑loaded. Instead, the AI must read them on demand using the `read_file` tool, following the Implementation Directive below.

---

## NON-NEGOTIABLE LAWS (always enforced, no exceptions)

```
 1. Classify design language FIRST — before any code.
 2. Brand tokens in :root once — never override per-page or inline.
 3. Never window.alert/confirm/prompt — SweetAlert2 or custom toast only.
 4. No grey placeholder boxes — Picsum/Unsplash URLs, context-matched.
 5. No localhost in any user-visible text or URL.
 6. Dark + light mode on every project — [data-theme] on <html>.
 7. Mobile-first always — design at 390px, expand outward.
 8. transition: all NEVER — specify only changed properties.
 9. Every <img>: width, height, loading, alt — no exceptions.
10. Every async state has a skeleton — never blank.
11. prefers-reduced-motion respected in all animations.
12. Only animate transform and opacity (GPU S-Tier). Never top/left/width/height.
13. Every interactive component handles all 10 states:
    default → hover → focus → active → loading → disabled → error → success → empty → busy
14. Never native <select>, native date input — always custom (uiux-interactive.md).
15. Image grids mix shapes — never all-square.
16. Min contrast: 4.5:1 body, 3:1 large text. Skip links + ARIA landmarks on every page.
17. Every page has a footer with role="contentinfo", copyright, legal links, back-to-top.
18. All spacing on the 8px scale — 4,8,12,16,24,32,48,64,80,96,120,160px only.
19. Every empty data state shows an empty-state component — never blank.
20. Images: <picture> + AVIF source + WebP fallback for hero/feature images.
21. Hero images: loading="eager" fetchpriority="high". Others: loading="lazy".
22. Scroll-driven animations: wrap in @supports (animation-timeline: view()) + JS IO fallback.
23. Custom cursors: only Luxury + Expressive, never on touch devices.
24. Forms: always use autocomplete attributes. Never window.prompt.
25. Run reasoning.md workflow BEFORE writing code for any non-trivial task.
26. Run threat-modeling.md BEFORE any feature involving auth, payments, or user data.
27. Run uiux-audit-automation.md after every new page — zero Critical/Serious violations.
28. All E2E tests use accessible queries (getByRole, getByLabel) — never querySelector.
29. Touch-specific states (long-press, haptic feedback) for mobile.
30. AI-generated content must be announced via aria-live regions.
31. Emoji usage: Never as primary icon. Only for flags, ratings, or Expressive/Warm languages. Luxury/Minimalist/Technical: no emoji.
32. Never hardcode API keys, passwords, or secrets. Use environment variables only. Add .env to .gitignore. Scan for secrets before every commit.
33. Morphing / storytelling scroll effects: Only for Expressive design language or when user explicitly requests "wow" factor. Never for funeral, legal, medical, government, or any somber context.
34. Animation defaults: If design language or context is unclear, use instant reveal (no animation). Never default to generic fade/slide for serious or professional sites.
35. Modern dashboard layout: For admin, analytics, or data interfaces, default to asymmetric bento grids, generous whitespace, floating cards, and large typography. Never output centred, boxy, or compact layouts unless requested.
36. Template revision rule: When the user provides a reference to a popular template or asks to "use something like X", the AI must analyse the template, identify its generic elements, and transform them using the visual wow principles (asymmetric grids, generous whitespace, floating cards, large typography, glassmorphism, etc.). The final output must not be recognisable as the original template.
```

---

## PROACTIVE READING RULE (for planning and any UI/UX task)

Whenever the user asks for:
- A plan, architecture, or technical design
- Any front‑end implementation (components, pages, forms, animations, etc.)
- A review or analysis of existing UI/UX code
- A security review, audit, or threat model
- Documentation, tests, or stories for any component

You MUST:
1. Identify which of the **92 rule files** (see table below) are relevant to the request.
2. Use the `read_file` tool to load those files **before** generating a response.
3. Base your answer on the rules you just read.

This applies even if the user does not use trigger words like "proceed". The only exception is if the user explicitly asks a non‑UI question (e.g., "explain a Python function").

---

## IMPLEMENTATION DIRECTIVE (when user says "proceed")

When the user says any of the following trigger words:
- "proceed"
- "implement"
- "build"
- "execute the plan"
- "start coding"

You MUST follow this process:

1. Identify which of the **79 rule files** (see table below) are relevant to the task at hand.
2. Use the `read_file` tool to load each relevant file from `.opencode/rules/`.
3. After reading the files, follow their rules strictly – they override any generic knowledge.
4. Only then begin writing code or implementing the requested feature.

Do not skip this step. Do not assume you already know the rules.

---

## DESIGN LANGUAGE FOR THIS PROJECT

**[ FILL IN BEFORE STARTING: Luxury / Premium Modern / Minimalist / Expressive / Editorial / Warm & Human / Technical ]**

| # | Language       | Best For                          | Key feel               |
|---|----------------|-----------------------------------|------------------------|
| 1 | Luxury         | Fashion, hotels, jewelry, art     | Slow, spacious, sharp  |
| 2 | Premium Modern | SaaS, fintech, B2B, productivity  | Polished, confident    |
| 3 | Minimalist     | Portfolios, studios, agencies     | Silent, precise        |
| 4 | Expressive     | Gaming, music, events, youth      | Loud, energetic        |
| 5 | Editorial      | News, blogs, long-form media      | Typographic, readable  |
| 6 | Warm & Human   | Health, education, NGO, consumer  | Approachable, safe     |
| 7 | Technical      | Dashboards, dev tools, admin, CLI | Dense, zero decoration |

---

## RULE FILES

| File | Contents | Load when |
|------|----------|-----------|
| `uiux-tokens.md` | 3-tier tokens, DTCG, colors, fonts, gradients, shadows | Always first |
| `uiux-components.md` | Dark mode, CSS reset, CDN stack, alerts, spacing, nav, Radix/shadcn | Every project |
| `uiux-interactive.md` | Buttons, selects, toggles, pickers, mega-menu, image shapes | Building UI |
| `uiux-motion.md` | Per-language animation, GSAP, scroll-driven, Safari fallback, skeletons | Any animation |
| `uiux-states.md` | All 10 states incl. empty state, busy state, toasts, badges, alerts | All components |
| `uiux-a11y.md` | Fluid type, WCAG 2.1 AA, WCAG 3.0, ARIA, forms | Every page |
| `uiux-footer.md` | 6 footer variants, back-to-top, language selector | Every page |
| `uiux-performance.md` | CWV, font loading, resource hints, image formats, Lighthouse CI | Before ship |
| `uiux-forms.md` | Multi-step, autocomplete, password meter, phone picker | Any form |
| `uiux-custom-cursor.md` | Dot+ring, blob, magnetic buttons | Luxury / Expressive only |
| `uiux-architecture.md` | FSD, headless components, Signals, Zustand, TanStack Query | React/Vue apps |
| `uiux-css-architecture.md` | CSS Layers, Tailwind v4, CSS Modules, container queries | All projects |
| `uiux-ai-workflow.md` | AI prompts, token scan, Figma DTCG sync | AI-assisted work |
| `uiux-testing.md` | Testing Trophy, Storybook, axe-core, Chromatic, Playwright | Pre-ship |
| `reasoning.md` | Plan → constrain → diverge → evaluate → propose → self-review workflow | Any non-trivial task; always before complex implementation |
| `uiux-audit-automation.md` | Chrome DevTools MCP axe-core scan, WCAG violation report, fix suggestions | After every new page; a11y audit requests |
| `performance-auditor.md` | Lighthouse via MCP/CLI, CWV capture, per-audit fix library, CI integration | Before ship; performance review requests |
| `ai-red-teaming.md` | Prompt injection, jailbreaks, data leakage, resource exhaustion, agentic action tests | Any LLM-powered feature |
| `threat-modeling.md` | STRIDE model, trust boundaries, threat table, mitigation code patterns | Auth, payments, file upload, admin features |
| `api-documentation.md` | README, API reference, Mermaid architecture diagrams, JSDoc/TSDoc generation | /doc command; "document this" requests |
| `ai-test-generation.md` | Playwright E2E, integration tests, API tests, CI config from user stories | "generate tests"; "write E2E"; pre-ship |
| `component-storybook.md` | Storybook meta, all-variants stories, interaction tests, Chromatic visual regression | "generate stories"; new component |
| `uiux-laravel.md` | Laravel + Blade / Vite integration, CSRF, assets, session flashing, design tokens | Laravel projects |
| `uiux-flutter.md` | Flutter integration, ThemeData mapping, dark/light mode, navigation, alerts, gestures | Flutter projects |
| `uiux-mcp-setup.md` | MCP server configuration for Chrome DevTools and Lighthouse CLI | MCP setup, audit automation |
| `uiux-layouts.md` | Floating cards, hero offsets, diagonal splits, masonry, widget grids | Layout planning |
| `uiux-navigation-extras.md` | Breadcrumbs, pagination, tabs, accordions, sidebars, command palette | Navigation UI |
| `uiux-content-components.md` | Tooltips, popovers, carousels, video players, avatars, code blocks | Content-heavy UI |
| `uiux-data-table.md` | TanStack Table, filters, bulk actions, activity logs, kanban | Data management |
| `uiux-forms-extras.md` | Search bars, searchable dropdowns, range sliders, color pickers | Complex forms |
| `uiux-admin-dashboard.md` | Widget grids, RBAC UI, KPI cards, notification center, impersonation | Admin features |
| `uiux-i18n.md` | Language switcher, RTL support, Intl formatting, pluralization | Multi-language |
| `uiux-seo.md` | JSON-LD, meta tags, sitemap.xml, robots.txt, canonicals | SEO optimization |
| `uiux-pwa.md` | Service workers, manifest.json, app icons, offline fallback | PWA features |
| `uiux-visual-effects.md` | Parallax, mouse glow, typing animation, confetti, Lottie | Visual polish |
| `uiux-realtime.md` | WebSockets, SSE, Live dashboards, Collaborative editing | Real-time features |
| `uiux-graphql.md` | Apollo Client, URQL, Code generation, Caching | GraphQL APIs |
| `uiux-edge.md` | Edge Functions, Middleware, A/B testing, Geolocation | Edge computing |
| `uiux-ai-frontend.md` | AI search, autocomplete, chat interface, content generation | AI-powered features |
| `uiux-library-research.md` | Meta-skill for researching and evaluating modern libraries | Library selection |
| `uiux-migration.md` | Convert CSS/JSX to design tokens with regex patterns | Token migration |
| `uiux-token-validator.js` | CLI script to scan for hardcoded values and native UI | Pre-commit validation |
| `uiux-user-flow.md` | Generate Mermaid user flow diagrams from natural language | UX planning |
| `uiux-print-export.md` | Print and PDF export with jsPDF integration | Print features |
| `uiux-design-critique.md` | Heuristic evaluation (Nielsen's 10 + design language) | Design review |
| `uiux-responsive-tester.md` | Responsive testing matrix (390px, 768px, 1280px) | QA testing |
| `uiux-component-analytics.md` | Component usage analysis and optimization | Codebase analysis |
| `uiux-a11y-statement.md` | WCAG compliance statement template | Legal/compliance |
| `uiux-color-blindness.md` | CVD simulation via SVG filters | Accessibility |
| `uiux-micro-interactions.md` | Token-aware ripple, skeleton, toast, drag-to-reorder | Micro UX polish |
| `uiux-unique-ideas.md` | Creativity skill with 20+ wow effect categories | Visual innovation |
| `uiux-fuzzy-search.md` | Typo-tolerant search with Fuse.js | Search features |
| `uiux-native-apis.md` | Popover API, dialog, :user-valid, @starting-style | Modern browser APIs |
| `uiux-profile-settings.md` | Complete profile settings page pattern | User settings UI |
| `uiux-database-migrations.md` | Safe schema change rules (Laravel, Django, SQL) | Backend/DB |
| `uiux-site-configuration.md` | .env patterns, APP_NAME, dynamic title, named routes | Configuration |
| `uiux-error-pages.md` | Custom 404, 403, 419, 429, 500, 503 pages | Error handling |
| `uiux-legal-placeholders.md` | Privacy Policy, Terms of Service templates | Legal pages |
| `uiux-consistent-navigation.md` | Shared navbar/footer, active link highlighting | Navigation |
| `uiux-routes-naming.md` | Kebab-case URLs, RESTful conventions, named routes | Routing |
| `uiux-number-counter.md` | Animated KPI counter with scroll trigger | Data visualization |
| `uiux-otp-input.md` | 6-digit OTP input with auto-focus, paste support | Auth/verification |
| `uiux-infinite-scroll.md` | Intersection Observer loading with fallback | Content loading |
| `uiux-social-share.md` | Web Share API with fallback popups | Social sharing |
| `uiux-copy-to-clipboard.md` | Clipboard API with visual feedback | Utility features |
| `uiux-library-verification.md` | Verify npm packages before recommending | Library selection |
| `uiux-scroll-restoration.md` | Save/restore scroll position across navigation | SPA/MPA navigation |
| `uiux-analytics-setup.md` | Sentry, PostHog, GA4 with consent management | Analytics integration |
| `uiux-a11y-ci.md` | GitHub Action for axe-core a11y testing | CI/CD |
| `uiux-i18n-routing.md` | URL-based language routing (Next.js, Laravel, Vue) | Multi-language |
| `uiux-seo-meta.md` | Dynamic meta tags, Open Graph, Twitter Cards | SEO |
| `uiux-performance-ci.md` | Lighthouse CI with budget assertions | Performance CI |
| `uiux-component-docs.md` | Auto-generate component documentation | Documentation |
| `uiux-security-headers.md` | CSP, HSTS, X-Frame-Options configuration | Security |
| `uiux-offline-pwa.md` | Advanced service worker caching strategies | PWA |
| `uiux-monorepo.md` | Share design system across apps (Turborepo) | Architecture |
| `uiux-token-checker.md` | Pre-commit hook for token validation | Quality assurance |
| `uiux-spatial-ui.md` | WebGPU, Three.js, 3D product viewers, depth layers | Immersive 3D experiences |
| `uiux-voice-ui.md` | Web Speech API, voice commands, audio feedback | Voice interfaces |
| `uiux-sound-design.md` | UI sound cues, scroll-linked audio, data sonification | Audio feedback |
| `uiux-ambient-awareness.md` | Battery-aware UI, light sensor, network-adaptive | Context-aware optimization |
| `uiux-temporal-ui.md` | Time-of-day themes, deadline urgency, greetings | Time-aware interfaces |
| `uiux-progressive-disclosure.md` | Info architecture, expert mode, complexity hiding | Information hierarchy |
| `uiux-easter-eggs.md` | Konami codes, hidden features, achievements | Delight & discovery |
| `uiux-loading-storytelling.md` | Progress narratives, skeleton theatre, mini-games | Entertaining waits |
| `uiux-microcopy.md` | Error messages, empty states, CTAs, tooltips | Words that work |
| `uiux-anti-dark-patterns.md` | Ethical UI, no manipulation, transparent design | Trust & ethics |
| `uiux-trust-signals.md` | Security badges, verification, privacy indicators | Building confidence |
| `uiux-webxr.md` | AR try-on, VR experiences, immersive product views | AR/VR web |
| `uiux-biometric-auth.md` | WebAuthn/passkeys, fingerprint/face prompts | Passwordless auth |
| `uiux-changelog.md` | Version history, breaking changes, new features | Always check |
| `uiux-contributing.md` | Contribution guide, rule naming, token usage | Extending the system |
