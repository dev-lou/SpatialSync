---
description: Changelog for OpenCode UI/UX 2026 Design System, tracking major versions from v1 to v11.
---

# OpenCode UI/UX 2026 Design System Changelog

This document tracks significant changes, additions, and fixes across major versions of the OpenCode UI/UX 2026 Design System.

## v11 - 2026-04-06

### Added — 13 New Unique Skills (Non-AI-Slop 2026)

| Category | File | Purpose |
|----------|------|---------|
| **Sensory & Spatial** | `uiux-spatial-ui.md` | WebGPU, Three.js, 3D product viewers, depth-layered interfaces |
| | `uiux-voice-ui.md` | Web Speech API, voice commands, TTS feedback |
| | `uiux-sound-design.md` | UI audio, scroll-linked sounds, data sonification |
| **Context-Aware** | `uiux-ambient-awareness.md` | Battery-aware UI, light sensor themes, network-adaptive loading |
| | `uiux-temporal-ui.md` | Time-of-day themes, deadline urgency, personalized greetings |
| | `uiux-progressive-disclosure.md` | Information architecture, expert mode, complexity hiding |
| **Delight & Polish** | `uiux-easter-eggs.md` | Konami codes, hidden features, achievement systems |
| | `uiux-loading-storytelling.md` | Progress narratives, skeleton theatre, mini-games |
| | `uiux-microcopy.md` | Error messages that don't suck, empty states, CTAs |
| **Ethics & Trust** | `uiux-anti-dark-patterns.md` | Ethical UI rules, no manipulation, transparent pricing |
| | `uiux-trust-signals.md` | Security badges, verification UI, privacy indicators |
| **Emerging Tech** | `uiux-webxr.md` | AR try-on, VR showrooms, immersive product views |
| | `uiux-biometric-auth.md` | WebAuthn/passkeys, fingerprint/face prompts, passwordless |

### Notes

- 13 new files (2 planned skills were already covered: kinetic typography in `uiux-motion.md`/`uiux-visual-effects.md`, haptic feedback in `uiux-states.md`)
- All new skills enforce design tokens, `prefers-reduced-motion`, and custom components
- Total rule files: 92

---

## v10.2.1 - 2026-04-06

### Fixed

- **`uiux-states.md`:** Corrected state numbering conflict. Empty state is now State 9, Busy state is now State 10. Updated header to reflect "10 states".
- **`uiux-ai-frontend.md`:** Replaced `swal.fire()` calls with `swal.toast()` helper for consistency with `uiux-components.md`.
- **`uiux-forms.md`:** Added "CRITICAL RULE: NO NATIVE BROWSER UI" section at the top for explicit enforcement.

### Added

- **`uiux-monorepo.md`:** Added Yarn workspaces and pnpm workspaces configuration examples. Added note about framework adaptability (Vue, Svelte, etc.).

### Notes

State order is now: default → hover → focus → active → loading → disabled → error → success → empty → busy. This matches Law 13 in AGENTS.md.

---

## v10.2 - 2026-04-06

### Added — 12 New Skill Files

| File | Purpose |
|------|---------|
| `uiux-library-verification.md` | Verify npm packages (downloads, recency, license) before recommending |
| `uiux-scroll-restoration.md` | Save/restore scroll position (React Router, vanilla JS, Laravel) |
| `uiux-analytics-setup.md` | Sentry, PostHog, GA4 integration with cookie consent |
| `uiux-a11y-ci.md` | GitHub Action for axe-core accessibility testing on PRs |
| `uiux-i18n-routing.md` | URL-based language routing (Next.js, Laravel, Vue Router) |
| `uiux-seo-meta.md` | Dynamic meta tags, Open Graph, Twitter Cards per route |
| `uiux-performance-ci.md` | Lighthouse CI with budget assertions and PR comments |
| `uiux-component-docs.md` | Auto-generate component documentation (react-docgen, vuese) |
| `uiux-security-headers.md` | CSP, HSTS, X-Frame-Options for Next.js, Laravel, static sites |
| `uiux-offline-pwa.md` | Advanced service worker caching (Workbox strategies) |
| `uiux-monorepo.md` | Share design system across apps (npm workspaces, Turborepo) |
| `uiux-token-checker.md` | Pre-commit hook to validate design token usage |

### Changed

- **AGENTS.md:** Updated to v10.2, rule file count increased from 67 to 79.

### Notes

All new skills enforce design tokens, respect `prefers-reduced-motion`, and prohibit native browser UI. Total rule files: 79.

---

## v10.1 - 2026-04-06

### Changed

- **`uiux-tokens.md`:** Added third-party brand color tokens (`--brand-twitter`, `--brand-linkedin`, `--brand-facebook`, `--brand-whatsapp`, `--brand-youtube`, `--brand-instagram`, `--brand-github`, `--brand-discord`) with hover variants for full tokenization.
- **`uiux-social-share.md`:** Replaced hardcoded hex colors with brand tokens (`var(--brand-twitter)`, etc.) for complete token compliance.

### Notes

This is a minor patch for completeness — all platform brand colors are now tokenized, eliminating the last acceptable hardcoded hex values outside of token definitions.

---

## v10 - 2026-05-15

### Added

#### Part A — Enhancements to Existing Files

- **`uiux-tokens.md`:** Responsive breakpoint tokens (`--breakpoint-sm/md/lg/xl`) with media query usage note.
- **`uiux-components.md`:** Print styles section (`@media print`, `.no-print`, `.page-break`) and print button pattern.
- **`uiux-forms.md`:**
  - Address autocomplete with Google Places / Mapbox integration.
  - Placeholder styling with focus transitions.
  - Input shake animation (GPU-only transform) for success/failure.
  - Password visibility toggle with eye icon and ARIA attributes.
- **`uiux-admin-dashboard.md`:** Dark mode support for Recharts with `useChartTheme` hook.
- **`uiux-realtime.md`:** Offline queue with IndexedDB, offline banner, and browser fallback.
- **`uiux-ai-frontend.md`:** Rate-limiting UX (429 handling) with countdown timer, disabled send button, toast.
- **`uiux-performance.md`:** LCP element detection rule (fetchpriority="high", preload link guidance).
- **`uiux-a11y.md`:** `:focus-visible` polyfill for Safari < 15.4 with CDN script and fallback CSS.
- **`uiux-states.md`:** 10th state "Busy" with `aria-busy="true"`, spinner overlay, opacity style.
- **`uiux-css-architecture.md`:** CSS subgrid support section with parent/child examples.
- **`uiux-interactive.md`:** Icons inside dropdown triggers and options using Lucide icons.

#### Part B — 25 New Skill Files

| File | Purpose |
|------|---------|
| `uiux-migration.md` | Convert existing CSS/JSX to design tokens with regex patterns and mapping table |
| `uiux-token-validator.js` | CLI script that scans for hardcoded px, hex, radii, and native UI violations |
| `uiux-user-flow.md` | Generate Mermaid user flow diagrams from natural language |
| `uiux-print-export.md` | Deep dive on print and PDF export with jsPDF integration |
| `uiux-design-critique.md` | Heuristic evaluation using Nielsen's 10 + design language consistency |
| `uiux-responsive-tester.md` | Responsive testing matrix (390px, 768px, 1280px viewports) |
| `uiux-component-analytics.md` | Analyse codebase for component usage and complexity |
| `uiux-a11y-statement.md` | Generate WCAG compliance statement template |
| `uiux-color-blindness.md` | Simulate protanopia/deuteranopia/tritanopia via SVG filters |
| `uiux-micro-interactions.md` | Library of token-aware micro-interactions (ripple, skeleton, toast, drag) |
| `uiux-unique-ideas.md` | Creativity skill with 20+ categories for fresh wow effects |
| `uiux-fuzzy-search.md` | Typo-tolerant search using Fuse.js with highlight matches |
| `uiux-native-apis.md` | 2026 browser APIs (Popover API, `<dialog>`, `:user-valid`, `@starting-style`) |
| `uiux-profile-settings.md` | Complete profile settings page pattern with avatar upload |
| `uiux-database-migrations.md` | Safe schema change rules for Laravel, Django, raw SQL |
| `uiux-site-configuration.md` | .env patterns (APP_NAME, APP_LOGO_URL), dynamic title, named routes |
| `uiux-error-pages.md` | Custom 404, 403, 419, 429, 500, 503 pages with design tokens |
| `uiux-legal-placeholders.md` | Privacy Policy, Terms of Service, Cookie Policy templates |
| `uiux-consistent-navigation.md` | Shared navbar/footer rules with active link highlighting |
| `uiux-routes-naming.md` | Professional route naming (kebab-case, RESTful, named routes) |
| `uiux-number-counter.md` | Animated number counter for KPIs with scroll-triggered animation |
| `uiux-otp-input.md` | 6-digit OTP/verification input with auto-focus and paste support |
| `uiux-infinite-scroll.md` | Intersection Observer loading with skeleton and fallback |
| `uiux-social-share.md` | Web Share API with fallback popups for Twitter, LinkedIn, etc. |
| `uiux-copy-to-clipboard.md` | Clipboard API with visual feedback and accessibility |

### Changed

- Updated `AGENTS.md` to reflect version v10 and updated rule count from 39 to 64.
- States increased from 9 to 10 (added "Busy" state).
- Added note about native browser UI replacement rule in multiple files.

### Technical Notes

- **Critical Rule Enforced:** All native browser UI components (date/time/color pickers, `<select>`, alerts) must be replaced with custom token-styled components.
- **Flatpickr:** Standard library for date/time pickers with token-based CSS overrides.
- **All new components:** Respect `prefers-reduced-motion` media query.

---

## v9 - 2026-04-06

### Added
- **5 New Skill Files:**
  - `uiux-realtime.md`: WebSockets, SSE, live dashboards, and collaborative editing.
  - `uiux-graphql.md`: Apollo Client, URQL, code generation, and caching strategies.
  - `uiux-edge.md`: Edge Functions, Middleware, A/B testing, and geolocation.
  - `uiux-ai-frontend.md`: AI search, autocomplete, chat interfaces, and content generation.
  - `uiux-library-research.md`: Meta-skill for researching and evaluating modern libraries.
- **New Laws (31-36):**
  - Law 31: Emoji usage restrictions.
  - Law 32: Environment variables and secret scanning.
  - Law 33: Morphing/storytelling scroll effect restrictions.
  - Law 34: Animation defaults (instant reveal).
  - Law 35: Modern dashboard layout principles.
  - Law 36: Template revision rule for transforming generic designs.
- **New Sections in `uiux-components.md`:**
  - Placeholder Images by Design Language (2026).
  - Icon Fallback for Accessibility.
- **New Sections in `uiux-admin-dashboard.md`:**
  - Modern Dashboard Visual Principles (2026).
  - Charts & Analytics Library (2026).
  - Standard Admin Features – Decision Guide.
  - Visual Wow Checklist.
- **New Sections in `uiux-visual-effects.md`:**
  - Morphing & Storytelling Scroll (Wow Factor) with decision rules and default animation styles.

### Changed
- Updated `AGENTS.md` to reflect version v9 and updated rule count to 39.
- Refined KPI card and chart examples in `uiux-admin-dashboard.md`.

## v8 - 2026-04-06

### Added
- **10 New Layout/Component Files:**
  - `uiux-layouts.md`: Comprehensive layout planning rules.
  - `uiux-navigation-extras.md`: Advanced navigation patterns (breadcrumbs, pagination, tabs, etc.).
  - `uiux-content-components.md`: Rich content components (tooltips, popovers, carousels, etc.).
  - `uiux-data-table.md`: Data table guidelines (TanStack Table, filters, bulk actions).
  - `uiux-forms-extras.md`: Complex form elements (search bars, range sliders, color pickers).
  - `uiux-admin-dashboard.md`: Admin-specific UI patterns (widget grids, RBAC UI).
  - `uiux-i18n.md`: Internationalization and localization guidelines.
  - `uiux-seo.md`: Search Engine Optimization best practices for UI/UX.
  - `uiux-pwa.md`: Progressive Web App features and implementation.
  - `uiux-visual-effects.md`: Visual polish and effects (parallax, mouse glow).
- **8 New Meta-Skills/Process Files:**
  - `reasoning.md`: Workflow for complex task planning.
  - `uiux-audit-automation.md`: Automated accessibility audit processes.
  - `performance-auditor.md`: Lighthouse CLI integration and performance auditing.
  - `ai-red-teaming.md`: AI security and red-teaming guidelines.
  - `threat-modeling.md`: STRIDE model for threat identification.
  - `api-documentation.md`: API documentation generation and standards.
  - `ai-test-generation.md`: AI-assisted test generation for E2E and integration tests.
  - `component-storybook.md`: Storybook integration and component documentation.
- **New Laws:** Laws 25-30 added to `AGENTS.md` for enhanced process guidance.
- **Quick Start Summary:** A concise executive summary inserted into `AGENTS.md` for quick AI agent onboarding.

### Changed
- Updated `AGENTS.md` to reflect version v8 and verified rule count.

### Fixed
- Minor documentation inconsistencies across existing rule files.

## v7 - 2026-04-06

### Added
- Initial set of advanced process guidance and specialized UI patterns.

## v6 - 2025-10-15

### Added
- Introduction of `AGENTS.md` as the central guide for AI agents.
- Initial set of 24 Non-Negotiable Laws for core UI/UX principles.
- Proactive Reading Rule and Implementation Directive established.
- Core UI/UX rule files: `uiux-tokens.md`, `uiux-components.md`, `uiux-interactive.md`, `uiux-motion.md`, `uiux-states.md`, `uiux-a11y.md`, `uiux-footer.md`, `uiux-performance.md`, `uiux-forms.md`, `uiux-custom-cursor.md`, `uiux-architecture.md`, `uiux-css-architecture.md`, `uiux-ai-workflow.md`, `uiux-testing.md`.

### Changed
- Refined design token structure to a 3-tier system (Primitive → Semantic → Component).
- Standardized CSS variable naming conventions.

### Fixed
- Addressed initial feedback on clarity and examples in early rule files.

## v5 - 2025-03-20

### Added
- Comprehensive dark mode support across all components.
- Introduction of a dedicated accessibility (a11y) guideline.
- Initial set of performance optimization rules.

### Changed
- Updated design token values for improved visual consistency.
- Enhanced component states to cover all 9 required states.

### Fixed
- Resolved issues with responsive layouts on smaller screens.

## v4 - 2024-08-10

### Added
- Implementation of an 8px spacing grid system.
- Guidelines for custom cursors and advanced interactive elements.
- Initial documentation for CSS architecture principles.

### Changed
- Refined animation tokens and easing functions.
- Improved form component structures and validation patterns.

### Fixed
- Corrected visual glitches in several UI components.

## v3 - 2024-01-25

### Added
- Introduction of a comprehensive component library.
- Guidelines for motion and animation design.
- Initial set of design tokens for colors and typography.

### Changed
- Standardized component naming conventions.
- Enhanced documentation for basic UI elements.

### Fixed
- Minor styling inconsistencies across the system.

## v2 - 2023-06-15

### Added
- Basic UI kit with foundational elements (buttons, inputs, cards).
- Initial responsive design principles.

### Changed
- Updated color palette to align with brand guidelines.

### Fixed
- Resolved cross-browser compatibility issues for core components.

## v1 - 2023-01-01

### Added
- Initial release of the OpenCode UI/UX Design System.
- Core design principles and philosophy.
- Basic styling for typography and color.

### Changed
- N/A

### Fixed
- N/A
