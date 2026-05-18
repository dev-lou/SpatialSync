<div align="center">
  <img src="https://raw.githubusercontent.com/dev-lou/SpatialSync/main/public/images/logo.png" alt="SpatialSync Logo" width="200" height="200" style="border-radius: 20px; fallback: 'https://via.placeholder.com/200?text=SpatialSync';">
  
  <h1>SpatialSync</h1>
  <p><strong>Next-Generation Collaborative 3D Architectural Engine</strong></p>
  
  <p>
    <a href="#features">Features</a> •
    <a href="#tech-stack">Tech Stack</a> •
    <a href="#installation">Installation</a> •
    <a href="#architecture">Architecture</a> •
    <a href="#contributing">Contributing</a>
  </p>

  <p>
    <img src="https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel">
    <img src="https://img.shields.io/badge/Three.js-0.183-000000?style=for-the-badge&logo=three.js&logoColor=white" alt="Three.js">
    <img src="https://img.shields.io/badge/Supabase-Realtime-3ECF8E?style=for-the-badge&logo=supabase&logoColor=white" alt="Supabase">
    <img src="https://img.shields.io/badge/Tailwind-CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind CSS">
  </p>
</div>

---

> **SpatialSync** is a highly interactive, real-time 3D construction and spatial planning platform. Built for architects, engineers, and collaborative teams, it brings desktop-grade 3D modeling into the browser with sub-second synchronization, premium UI/UX, and enterprise-grade security.

## 🚀 Features

### 🏢 Advanced 3D Engine
- **Three.js Core:** High-performance rendering engine with custom shaders, dynamic lighting, and real-time shadows.
- **Parametric Building:** 16+ smart building parts (walls, floors, roofs, doors) that auto-height and auto-snap.
- **Custom Polygon Drawing:** Freeform geometry creation and advanced texture mapping.
- **Physics & Collisions:** Accurate bounding box calculations for realistic part placement and intersection handling.

### ⚡ Real-Time Collaboration
- **Supabase Sync:** Sub-second state synchronization across multiple connected clients.
- **Live Cursors & Presence:** See where your team members are looking and what they are selecting.
- **Timestamped Mutations:** Conflict-free state resolution using precise timestamps.
- **In-App Chat:** Persistent, secure workspace communication.

### 🛡️ Enterprise Security & IAM
- **Biometric Authentication:** Passwordless WebAuthn and facial/fingerprint recognition (Biometric HUD).
- **Role-Based Access Control (RBAC):** Granular permissions separating Admins, Architects, and Viewers.
- **Middleware Protected:** Complete API route and component-level security using Laravel Sanctum and custom middleware.

### 💎 UI/UX 2026 Standards
- **Premium Interface:** Glassmorphism, tailored tokens, and smooth micro-animations.
- **10-State Components:** Fully accessible interactive components (default, hover, focus, active, loading, disabled, error, success, empty, busy).
- **Dark/Light Mode:** Seamless theme switching with fluid typography.
- **Dynamic Pricing Engine:** Simulated cost estimations updated in real-time as you build.

## 🏗️ Tech Stack

### Frontend
* **Core:** JavaScript (ES2024), Alpine.js for lightweight reactivity.
* **3D & Graphics:** Three.js, Fabric.js (Canvas 2D overlays).
* **Styling:** Tailwind CSS v3, PostCSS, Custom 2026 UI/UX Tokens.
* **Build Tool:** Vite 5.

### Backend
* **Framework:** Laravel 11.
* **Language:** PHP 8.2+.
* **Real-time & DB:** Supabase (PostgreSQL, Realtime WebSockets, Storage).
* **Authentication:** Laravel Sanctum, Biometric WebAuthn.

## ⚙️ Installation & Setup

### Requirements
* PHP 8.2 or higher
* Node.js 18+ and npm
* Composer
* A Supabase project

### 1. Clone & Install Dependencies
```bash
git clone https://github.com/your-org/spatialsync.git
cd spatialsync

# Install PHP dependencies
composer install

# Install NPM dependencies
npm install
```

### 2. Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```
Update your `.env` with your Supabase credentials and Render/production URLs.

### 3. Database Migration
```bash
php artisan migrate:fresh --seed
```

### 4. Build Assets & Start
```bash
npm run build
php artisan serve
```
Your local environment is now running at `http://localhost:8000`.

## 📐 Architecture Overview

```mermaid
graph TD
    A[Browser Client] -->|WebSockets/HTTPS| B[Next-Gen Frontend]
    B -->|Three.js Canvas| C[3D Rendering Engine]
    B -->|Alpine.js + Tailwind| D[UI Layer]
    B <-->|Realtime Sync| E[Supabase Realtime Channel]
    B -->|REST API| F[Laravel 11 Backend]
    F -->|RBAC / Auth| G[(PostgreSQL DB)]
    E --> G
    
    style A fill:var(--surface),stroke:var(--border)
    style G fill:var(--surface-up),stroke:var(--accent)
```

## 🛡️ License
This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
