<div align="center">

  <!-- Banner — uses the og-meta.png that actually exists in the repo -->
  <img src="https://raw.githubusercontent.com/dev-lou/SpatialSync/main/public/images/og-meta.png"
       alt="SpatialSync — Collaborative 3D Architectural Platform"
       width="800" />

  <h1>SpatialSync</h1>
  <p><strong>Build a house by dragging things. Get a price. Get a plan. Argue about the window.</strong></p>
  <p><em>Drag-and-drop 3D building in the browser — with a planning estimate, a real floor-plan PDF, and client comments pinned onto the walls.</em></p>

  <p>
    <a href="https://spatialsync.onrender.com" target="_blank">🌐 Live Demo</a> &nbsp;·&nbsp;
    <a href="DEMO.md">🎬 Demo runbook</a> &nbsp;·&nbsp;
    <a href="#-quick-start">⚡ Quick Start</a> &nbsp;·&nbsp;
    <a href="#-features">✨ Features</a> &nbsp;·&nbsp;
    <a href="#%EF%B8%8F-tech-stack">🛠️ Tech Stack</a> &nbsp;·&nbsp;
    <a href="#-team">👥 Team</a>
  </p>

  <br/>

  <!-- Badges -->
  <img src="https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 11">
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.2">
  <img src="https://img.shields.io/badge/Three.js-0.183-000000?style=for-the-badge&logo=three.js&logoColor=white" alt="Three.js">
  <img src="https://img.shields.io/badge/Supabase-Realtime-3ECF8E?style=for-the-badge&logo=supabase&logoColor=white" alt="Supabase">
  <img src="https://img.shields.io/badge/Alpine.js-3.x-8BC0D0?style=for-the-badge&logo=alpinedotjs&logoColor=white" alt="Alpine.js">
  <img src="https://img.shields.io/badge/Vite-5.x-646CFF?style=for-the-badge&logo=vite&logoColor=white" alt="Vite 5">
  <img src="https://img.shields.io/badge/License-MIT-22C55E?style=for-the-badge" alt="MIT License">

  <br/>

  <!-- CI/CD DevOps Badges 2026 -->
  <img src="https://img.shields.io/github/actions/workflow/status/dev-lou/SpatialSync/ci.yml?style=for-the-badge&logo=github&label=CI" alt="CI">
  <img src="https://img.shields.io/github/actions/workflow/status/dev-lou/SpatialSync/pr-checks.yml?style=for-the-badge&logo=github&label=PR%20Checks" alt="PR Checks">
  <img src="https://img.shields.io/github/actions/workflow/status/dev-lou/SpatialSync/security.yml?style=for-the-badge&logo=github&label=Security" alt="Security">
  <img src="https://img.shields.io/github/actions/workflow/status/dev-lou/SpatialSync/supply-chain.yml?style=for-the-badge&logo=github&label=Supply%20Chain" alt="Supply Chain">
  <img src="https://img.shields.io/github/actions/workflow/status/dev-lou/SpatialSync/release.yml?style=for-the-badge&logo=github&label=Release" alt="Release">
  <br/>
  <img src="https://img.shields.io/badge/Dependabot-active-025E8C?style=for-the-badge&logo=dependabot" alt="Dependabot">
  <img src="https://img.shields.io/badge/Commits-Conventional-0a7b5c?style=for-the-badge&logo=conventionalcommits" alt="Conventional Commits">
  <img src="https://img.shields.io/badge/Release-Semantic-e10079?style=for-the-badge&logo=semanticrelease" alt="Semantic Release">
  <img src="https://img.shields.io/badge/Linter-ESLint-4B32C3?style=for-the-badge&logo=eslint" alt="ESLint">
  <img src="https://img.shields.io/badge/Code%20Style-Prettier-1a2b34?style=for-the-badge&logo=prettier" alt="Prettier">
  <img src="https://img.shields.io/badge/Code%20Style-Pint-FF2D20?style=for-the-badge&logo=laravel" alt="Laravel Pint">
  <img src="https://img.shields.io/badge/PHPStan-level%20max-brightgreen?style=for-the-badge&logo=php" alt="PHPStan">
  <img src="https://img.shields.io/badge/Pre--commit-Husky-fedcba?style=for-the-badge&logo=husky" alt="Husky">
  <img src="https://img.shields.io/badge/CodeQL-Passing-005C9A?style=for-the-badge&logo=github" alt="CodeQL">
  <img src="https://img.shields.io/badge/SBOM-generated-22C55E?style=for-the-badge" alt="SBOM">

</div>

---

## 📋 Table of Contents

- [Overview](#-overview)
- [Suggested Repo Names](#-suggested-repo-names)
- [Features](#-features)
  - [3D Build Editor](#-3d-build-editor)
  - [Real-Time Collaboration](#-real-time-collaboration)
  - [Scenery & Environment](#-scenery--environment)
  - [Issue Tracker](#-issue-tracker)
  - [Client Review Links](#-client-review-links)
  - [Admin Panel](#-admin-panel)
  - [Pricing & Checkout](#-pricing--checkout)
  - [UI/UX 2026 Standards](#-uiux-2026-standards)
- [Tech Stack](#%EF%B8%8F-tech-stack)
- [Architecture](#-architecture)
- [Database Schema](#-database-schema)
- [Quick Start](#-quick-start)
  - [Prerequisites](#prerequisites)
  - [1 — Clone & Install](#1--clone--install)
  - [2 — Supabase Setup](#2--supabase-setup)
  - [3 — Environment Configuration](#3--environment-configuration)
  - [4 — Database Migration](#4--database-migration)
  - [5 — Build Assets & Run](#5--build-assets--run)
  - [6 — Create Admin Account](#6--create-admin-account)
- [Testing & Verification](#-testing--verification)
- [Keyboard Shortcuts](#-keyboard-shortcuts)
- [Deployment](#-deployment)
- [API Reference](#-api-reference)
- [Team](#-team)
- [License](#-license)

---

## 🌐 Overview

**Drop walls, doors and windows into a 3D scene in your browser and a house appears** — then turn it into a planning estimate, a floor-plan PDF, and a review link your client opens without creating an account.

It is built for the people the professional tools skip: homeowners renovating, small builders and remodelers, students. Ten floors, snap-to-grid placement, live multi-user editing, role-based permissions, comment pins on real parts — all on a PHP + Supabase stack.

No desktop software. No plugins. No training. Just open a browser.

> **Project status — September 2026.** Working product, **no paying customers yet**. Checkout is a sandbox with no payment provider connected, and the database's row-level security is not locked down. [`DEMO.md`](DEMO.md) lists exactly what is real and what is not; pitch material is in [`docs/pitch/`](docs/pitch/).

---

## ✨ Features

### 🏗️ 3D Build Editor

The heart of SpatialSync — a full WebGL 3D editor running in the browser, powered by Three.js.

| Feature | Details |
|---|---|
| **11 Smart Building Parts** | Six part types — walls, five floor finishes, roofs, doors, windows, stairs — with auto-height and auto-snap |
| **Multi-Floor Support** | Up to 10 independent floor levels with quick floor navigator (↑/↓ arrows) |
| **Custom Polygon Drawing** | Freeform geometry creation for irregular floor plans |
| **Texture Mapping** | Apply material textures (brick, marble, wood, glass) to any surface |
| **Wall Colour Picker** | Per-wall custom colour with real-time preview |
| **Roof Toggle** | Show/hide roof for interior inspection |
| **Undo / Redo** | Full history stack — `Ctrl+Z` / `Ctrl+Y` / `Ctrl+Shift+Z` |
| **Auto-Save** | Manual save + periodic auto-sync to Supabase |
| **Export PNG** | High-quality screenshot of the current 3D viewport |
| **Export JSON** | Full geometry data backup for re-import |
| **Export Blueprint PDF** | Real 2D floor-plan drawing from the 3D model — A4/A3, 1:50–1:200, all floors or one |
| **Orbit Controls** | Click-drag to rotate, scroll to zoom, right-drag to pan |
| **Keyboard Navigation** | Full shortcut system (see [Keyboard Shortcuts](#-keyboard-shortcuts)) |
| **Planning Estimate** | Indicative cost total that updates as parts are placed — rates in `config/spatialsync.php` |

> **Export formats:** PNG and blueprint PDF come from the browser; geometry export is **JSON only**. There is no IFC, DWG or Revit import/export yet.

---

### ⚡ Real-Time Collaboration

Multiple users can edit the same build simultaneously with zero conflicts.

| Feature | Details |
|---|---|
| **Live Sync** | Sub-second state synchronisation via Supabase Realtime WebSocket channels |
| **Connection Indicator** | Live status badge: `Live` / `Syncing...` / `Reconnecting...` / `Offline` |
| **Reconnection Logic** | Automatic exponential backoff (5–6 s) with up to 5 retry attempts |
| **In-App Chat** | Persistent workspace chat panel — messages stored in the database |
| **Invite by Email** | Search registered users by name/email and add them directly |
| **Share Link** | Generate a one-click invite URL for external collaborators |
| **Role-Based Editing** | Editors can modify; Viewers get a read-only badge and cannot change anything |
| **Timestamped Mutations** | Conflict-free state resolution using server-side timestamps |

---

### 🌅 Scenery & Environment

Change the world your building lives in — all rendered in real-time 3D.

| Scenery | Description |
|---|---|
| 🏘️ **Modern Neighborhood** | Sidewalks, roads & city trees |
| 🌲 **Nature & Mountains** | Forest backdrop with rolling hills |
| 🏙️ **Urban City** | Dense city blocks & grid streets |
| 🏜️ **Desert Oasis** | Sand dunes & arid landscape |

**Day / Night Toggle** — Switch between full sunlight and an atmospheric night scene with a single click (or press `B`). Ambient lighting, shadows, and sky colour all update live.

---

### 🐛 Issue Tracker

Built directly into the collaboration sidebar — no external tool required.

- Create issues with title, description, priority, and status
- Status workflow: `Open` → `In Progress` → `Resolved`
- Live badge count on the Issues tab
- Linked to the specific build

---

### 🔗 Client Review Links

Send a link; your client opens the model and comments on it — **no account, no email, no password**.

- **View + comment only** — a guest can look around, pin comments onto parts and post in chat; they can never move or delete geometry
- **Named attribution** — the visitor gives a display name, which appears on the pin and in chat
- **Expiring and revocable** — links are created with a 30-day expiry and can be revoked from the editor sidebar immediately
- **Re-validated server-side** — the token is the only credential a guest holds, so it is checked on every request, and the share routes are rate limited

Implementation: `BuildController@guestShow`, `App\Http\Middleware\ResolveCollaborator`, `app/Support/GuestReview.php`.

> **Note:** facial-biometric login was removed on 14 September 2026. It matched a visitor against every enrolled template at a 0.45 threshold — identification, not verification — and stored a face template per user. Run `database/migrations/2026_09_14_remove_biometric_data.sql` to clear any stored templates.

---

### 🛡️ Admin Panel

A dedicated admin interface for platform management.

| Page | Features |
|---|---|
| **Dashboard** | Platform-wide stats, user activity, recent builds |
| **Users** | View all registered users, promote to admin, deactivate accounts |
| **Builds** | View, manage, or delete any build on the platform |
| **Presets** | Create and manage reusable building-part presets |

**Role Levels:**
- `Admin` — Full platform access, user management, all builds
- `Editor / Architect` — Create and edit their own builds, invite members
- `Viewer` — Read-only access to shared builds

---

### 💳 Pricing & Checkout

- **Planning Estimate** — an indicative running total as parts are added or removed; the rates are illustrative defaults, not market data
- **Checkout Flow** — a **sandbox** plan-upgrade screen: no payment provider is connected and no card is charged
- **Contact Sales** — Enterprise inquiry form

---

### 💎 UI/UX 2026 Standards

SpatialSync is built on the **OpenCode UI/UX 2026** design system.

- **Design Language:** Premium Modern — polished, confident, professional
- **Design Tokens:** 3-tier token system (global → semantic → component) in CSS custom properties
- **Dark & Light Mode** — `[data-theme]` toggle on `<html>`, persisted across sessions
- **10-State Components** — Every interactive element handles: `default`, `hover`, `focus`, `active`, `loading`, `disabled`, `error`, `success`, `empty`, `busy`
- **Mobile-First** — Designed at 390 px, expands outward
- **8px Grid** — All spacing on the 8 px scale
- **Glassmorphism** — Auth pages, modals, and panels use `backdrop-filter: blur()`
- **Micro-Animations** — Stagger reveals, hover lifts, connection pulse animations
- **SweetAlert2** — All alerts and confirmations use a custom-themed SweetAlert2 mixin — no native `alert()`
- **Lucide Icons** — Consistent icon set throughout
- **Fluid Typography** — `clamp()` based scaling for headings
- **Skeleton Loading** — Every async state has a shimmer skeleton, never a blank screen

---

## 🛠️ Tech Stack

### Backend
| Technology | Version | Purpose |
|---|---|---|
| **Laravel** | 11.x | Core MVC framework, routing, middleware, auth |
| **PHP** | 8.2+ | Server runtime |
| **Laravel Sanctum** | 4.x | API token authentication |
| **Laravel Jetstream** | 5.x | Team management scaffolding |
| **Livewire** | 3.x | Server-rendered reactive components |
| **Laravel Pint** | 1.x | Code style enforcement |

### Database & Real-Time
| Technology | Purpose |
|---|---|
| **Supabase** | Managed PostgreSQL + Realtime WebSocket engine |
| **PostgreSQL** | Primary relational database (via Supabase) |
| **Supabase Realtime** | WebSocket channels for live state sync |
| **Supabase Storage** | File and asset storage |
| **Supabase PHP SDK** | `saeedvir/supabase` — server-side DB queries |

### Frontend
| Technology | Version | Purpose |
|---|---|---|
| **Three.js** | 0.183 | WebGL 3D rendering engine |
| **Fabric.js** | 5.x | Canvas 2D overlay for blueprint/floor-plan tools |
| **Alpine.js** | 3.x | Lightweight reactivity and component state |
| **@supabase/supabase-js** | 2.x | Client-side Realtime subscription |
| **Tailwind CSS** | 3.4 | Utility CSS framework |
| **Vite** | 5.x | Asset bundler and dev server |
| **Lucide Icons** | Latest | Icon library |
| **SweetAlert2** | 11.x | Premium modal dialogs |

### Infrastructure & DevOps
| Technology | Purpose |
|---|---|
| **Render** | Production cloud hosting |
| **Docker** | Containerised deployment (`Dockerfile` included) |
| **GitHub Actions** | CI/CD pipeline & release automation |
| **Dependabot** | Automated dependency security updates |
| **Husky + lint-staged** | Pre-commit hooks for code quality gates |
| **Commitlint** | Conventional Commits enforcement |
| **Semantic Release** | Automated versioning, changelog & GitHub releases |
| <sup>⚠️ v25+ requires Node.js ≥ 22.14</sup> | <sup>Update `NODE_VERSION` in `release.yml` when upgrading</sup> |
| **PHPStan (max level)** | Static analysis for PHP |
| **Laravel Pint** | Opinionated PHP code style |
| **ESLint + Prettier** | JavaScript linting & formatting |
| **CodeQL** | GitHub Advanced Security SAST scanning |
| **CycloneDX SBOM** | Software Bill of Materials generation |

---

## 🏛️ Architecture

```mermaid
graph TD
    USER["👤 Browser Client"] -->|HTTPS| FE["Frontend Layer\nAlpine.js + Three.js"]
    FE -->|WebGL Canvas| R3D["3D Rendering Engine\nThree.js + Fabric.js"]
    FE -->|REST API| BE["Laravel 11 Backend"]
    FE <-->|WebSocket Realtime| SR["Supabase Realtime\nChannel: build:{id}"]
    CLIENT["👤 Client (no account)\n/share/{token}"] -->|View + comment| FE
    BE -->|Sanctum + RBAC| DB[("PostgreSQL\nvia Supabase")]
    SR --> DB
    BE -->|Middleware| ADMIN["Admin Panel\n/admin/*"]
    BE -->|BotSeoMiddleware| BOT["Social Bot Fast-Path\nOG Meta HTML"]

    style USER fill:#3B82F6,color:#fff,stroke:none
    style R3D fill:#8B5CF6,color:#fff,stroke:none
    style DB fill:#10B981,color:#fff,stroke:none
    style SR fill:#3ECF8E,color:#fff,stroke:none
    style CLIENT fill:#F59E0B,color:#fff,stroke:none
```

### Key Architectural Decisions

| Decision | Rationale |
|---|---|
| **Supabase Realtime over Laravel Echo** | Zero infrastructure overhead — no Redis or separate WebSocket server needed |
| **Debounced display status (3 s)** | Prevents UI jitter from transient network drops without alarming the user |
| **8-second grace period on connect** | Avoids false "disconnected" alerts during initial page load |
| **BotSeoMiddleware (prepend)** | Social scrapers (Facebook/Twitter) get instant static OG HTML — bypasses full SPA boot |
| **Client review links** | A 64-character share token, re-validated server-side on every request, with a 30-day expiry and immediate revocation from the editor |
| **One request instance** | `public/index.php` creates the request as `AuthenticatedRequest` and binds it in the container, so middleware type hints and controller input read the same object instead of an empty rebuild |

---

## 🗃️ Database Schema

```
users                  — Auth accounts, admin flag, profile data
├── teams              — Workspace teams (Jetstream)
├── personal_access_tokens — Sanctum API tokens

builds                 — 3D build projects (name, owner, visibility)
├── build_parts        — Individual 3D parts (type, position, rotation, texture, colour, shape_points)
├── build_members      — Collaborator list with role (admin/editor/viewer)
├── build_messages     — In-app chat messages per build
├── build_shares       — Client review links (share token, expiry, access level)
├── build_issues       — Bug/issue tracker entries per build
└── part_presets       — Reusable part templates (admin-managed)

notifications          — Platform-wide notification records
```

> **Supabase Realtime** is enabled on `build_parts`, `build_messages`, and `build_issues` tables. See `database/migrations/enable_supabase_realtime.sql`.

---

## ⚡ Quick Start

### Prerequisites

Make sure you have the following installed:

| Tool | Version | Check |
|---|---|---|
| PHP | ≥ 8.2 | `php -v` |
| Composer | ≥ 2.x | `composer -V` |
| Node.js | ≥ 18.x | `node -v` |
| npm | ≥ 9.x | `npm -v` |
| Git | Any | `git -v` |

You also need a free **[Supabase](https://supabase.com)** account and project.

---

### 1 — Clone & Install

```bash
# Clone the repository
git clone https://github.com/dev-lou/SpatialSync.git
cd SpatialSync

# Install PHP dependencies
composer install

# Install Node dependencies
npm install
```

---

### 2 — Supabase Setup

1. Go to [supabase.com](https://supabase.com) → **New Project**
2. Note down your **Project URL**, **anon key**, and **service_role key** from:
   `Settings → API`
3. Note down your **Database Host** from:
   `Settings → Database → Connection String → URI` (use the **pooler** host on port `6543`)
4. Run the full schema in the **SQL Editor**:

```sql
-- Copy and paste the entire contents of:
database/migrations/supabase_schema.sql
```

5. Enable Realtime on the required tables:

```sql
-- Copy and paste:
database/migrations/enable_supabase_realtime.sql
```

6. *(Optional)* Seed with example data:

```sql
-- Copy and paste:
database/migrations/supabase_migration_data.sql
```

---

### 3 — Environment Configuration

```bash
# Copy the example environment file
cp .env.example .env

# Generate a unique application key
php artisan key:generate
```

Now open `.env` and fill in your values:

```dotenv
APP_NAME="SpatialSync"
APP_ENV=local
APP_KEY=            # auto-filled by php artisan key:generate
APP_DEBUG=true
APP_URL=http://localhost:8000

# ── Supabase ─────────────────────────────────────────
SUPABASE_URL=https://xxxxxxxxxxxx.supabase.co
SUPABASE_SERVICE_KEY=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
SUPABASE_ANON_KEY=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...

# ── Database (Supabase PostgreSQL pooler) ────────────
DB_CONNECTION=pgsql
DB_HOST=db.xxxxxxxxxxxx.supabase.co
DB_PORT=6543
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=your-supabase-password

# ── Session & Cache ──────────────────────────────────
SESSION_DRIVER=file
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
```

> ⚠️ **Never commit your `.env` file.** It is already listed in `.gitignore`.

---

### 4 — Database Migration

Run Laravel's built-in migrations (creates the local relational tables Sanctum needs):

```bash
php artisan migrate
```

> The main application data (builds, parts, messages) lives in **Supabase PostgreSQL** and was set up in Step 2. The Laravel migrations handle `users`, `teams`, `personal_access_tokens`, and `notifications`.

---

### 5 — Build Assets & Run

Open **two terminal windows** side-by-side:

**Terminal 1 — Vite Dev Server (hot reload):**
```bash
npm run dev
```

**Terminal 2 — Laravel Server:**
```bash
php artisan serve
```

Your app is now running at **[http://localhost:8000](http://localhost:8000)** 🎉

> **Production build** (when deploying):
> ```bash
> npm run build
> php artisan config:cache
> php artisan route:cache
> php artisan view:cache
> ```

---

### 6 — Create Admin Account

1. Register a new account at `/register`
2. In your Supabase dashboard → **Table Editor** → `users`
3. Find your user row and set `is_admin = true`
4. Refresh the app — the **Admin** link will appear in your navigation

Alternatively via Laravel Tinker:
```bash
php artisan tinker
```
```php
\App\Models\User::where('email', 'your@email.com')->update(['is_admin' => true]);
```

---

## ✅ Testing & Verification

Everything this README claims can be checked with four commands:

```bash
vendor/bin/phpstan analyse        # static analysis at level max — no errors
vendor/bin/phpunit --no-coverage  # 37 tests: unit + feature suites
npx eslint resources/js/ --max-warnings=0
npm run build                     # Vite production build
```

`tests/Feature/GuestReviewLinkTest.php` drives the **client review link** through
real routing, middleware, session and Blade views, faking only the Supabase REST
calls — so it runs with no credentials. It covers the no-signup join screen,
comment attribution, expired and revoked tokens, a token presented against the
wrong build, and every write a client is refused. If someone asks whether the
no-signup link really works, that file is the answer you can run in front of them.

`php artisan test` is not available in this Laravel version; CI calls PHPUnit
directly (`.github/workflows/ci.yml`).

---

## ⌨️ Keyboard Shortcuts

These shortcuts are active while inside the **3D Build Editor**:

| Key | Action |
|---|---|
| `W` `A` `S` `D` | Move the selected part |
| `R` | Rotate selected part 45° |
| `G` | Delete selected part |
| `T` | Open Transform mode |
| `SPACE` | Reset camera to default view |
| `Q` | Cancel current action |
| `B` | Toggle Day / Night mode |
| `Ctrl + Z` | Undo last action |
| `Ctrl + Y` / `Ctrl + Shift + Z` | Redo |

---

## 🚀 Deployment

SpatialSync includes a complete production configuration for **[Render](https://render.com)**.

### Using render.yaml (recommended)

```bash
# The render.yaml at the project root configures the service automatically.
# Just connect your GitHub repo to Render and it will detect it.
```

### Required Environment Variables on Render

Set all variables from your `.env` in **Render → Environment**. Key production overrides:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-custom-domain.com
SESSION_DRIVER=file
CACHE_DRIVER=file
```

### Docker

A `Dockerfile` is included for custom container deployments:

```bash
docker build -t spatialsync .
docker run -p 8000:8000 --env-file .env spatialsync
```

---

## 📡 API Reference

All API routes are prefixed with `/api` and protected by **Laravel Sanctum**.

### Build Parts
| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/builds/{id}/parts` | Fetch all parts for a build |
| `POST` | `/api/builds/{id}/parts` | Create a new part |
| `PUT` | `/api/parts/{id}` | Update part position/rotation/texture |
| `DELETE` | `/api/parts/{id}` | Delete a part |

### Collaboration
| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/builds/{id}/messages` | Get chat history |
| `POST` | `/api/builds/{id}/messages` | Send a chat message |
| `GET` | `/api/builds/{id}/members` | List collaborators |
| `POST` | `/api/builds/{id}/members` | Invite a collaborator |
| `PUT` | `/api/permissions/{id}` | Update member role |

### Issues
| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/builds/{id}/issues` | List build issues |
| `POST` | `/api/builds/{id}/issues` | Create an issue |
| `PUT` | `/api/issues/{id}` | Update issue status/priority |
| `DELETE` | `/api/issues/{id}` | Delete an issue |

### Build Sharing
| Method | Endpoint | Description |
|---|---|---|
| `POST` | `/api/builds/{id}/share` | Generate shareable invite link |
| `GET` | `/join/{token}` | Join via invite token |

---

## 📄 License

This project is licensed under the **MIT License**.  
See the [LICENSE](LICENSE) file for full details.

```
MIT License — Copyright (c) 2026 SpatialSync Team
```

---

<div align="center">

**Built with ❤️ by the SpatialSync Team · 2026**

[🌐 Live Demo](https://spatialsync.onrender.com) &nbsp;·&nbsp; [🐛 Report a Bug](https://github.com/dev-lou/SpatialSync/issues) &nbsp;·&nbsp; [💡 Request a Feature](https://github.com/dev-lou/SpatialSync/issues)

</div>
