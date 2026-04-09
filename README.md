# ConstructHub

**Build Houses & Buildings Together in Real-Time**

A collaborative 3D house construction platform inspired by Bloxburg and Sims Build Mode. Create houses, multi-story buildings, and architectural designs with friends in real-time.

## Features

### 🏠 House Building
- **16+ Building Parts**: Walls, floors, roofs, doors, windows, stairs
- **Multi-Story Support**: Build up to 10 floors
- **Auto-Height System**: Parts automatically position correctly
- **Grid Snapping**: Precise placement with 1-meter grid
- **Auto-Roof Positioning**: Roofs automatically position above walls

### 🎮 Intuitive Controls
- **Orbit Camera**: Rotate around your house like Bloxburg
- **Click-to-Place**: Simple click to add parts
- **Drag-to-Move**: Move parts by dragging
- **Real-time Preview**: See where parts will be placed
- **Minimap**: Bird's eye view navigation

### 🔄 Undo/Redo System
- **50-step History**: Undo and redo all actions
- **Keyboard Shortcuts**: Ctrl+Z, Ctrl+Y
- **Action Tracking**: Add, delete, move tracked

### 💾 Save & Export
- **Auto-Save**: Automatic save every 30 seconds
- **Manual Save**: Save anytime with Ctrl+S
- **Export PNG**: High-quality screenshots
- **Export JPEG**: Compressed images
- **Export JSON**: Full build data backup
- **Share Links**: Copy build URL to clipboard

### ⌨️ Keyboard Shortcuts
- `Ctrl + S` - Save build
- `Ctrl + D` - Duplicate selected part
- `Ctrl + Z` - Undo
- `Ctrl + Y` - Redo
- `Delete` - Delete selected part
- `Escape` - Deselect / Exit placement
- `G` - Toggle grid
- `Ctrl + ?` - Show shortcuts

### 🎨 3D Visualization
- **Three.js Rendering**: Professional 3D graphics
- **Real-time Shadows**: Dynamic lighting and shadows
- **OrbitControls**: Smooth camera rotation
- **Floor Indicators**: Visual floor level markers

## Tech Stack

- **Backend**: Laravel 11
- **Frontend**: Alpine.js, Vanilla JS
- **3D Engine**: Three.js with OrbitControls
- **Database**: SQLite (dev) / PostgreSQL (prod)
- **Real-time**: Laravel Echo (preparation ready)
- **Styling**: Custom CSS with design tokens

## Requirements

- PHP 8.2+
- Node.js 18+
- Composer
- SQLite (included) or PostgreSQL/MySQL

## Installation

### 1. Clone the Repository

```bash
git clone https://github.com/yourusername/constructhub.git
cd constructhub
```

### 2. Install Dependencies

```bash
composer install
npm install
```

### 3. Environment Setup

```bash
cp .env.example .env
```

Edit `.env`:

```env
APP_NAME="ConstructHub"
APP_ENV=local
APP_KEY=
APP_URL=http://localhost:8000

# Use SQLite for development
DB_CONNECTION=sqlite
# Or use MySQL/PostgreSQL
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=constructhub
# DB_USERNAME=root
# DB_PASSWORD=
```

### 4. Generate Application Key

```bash
php artisan key:generate
```

### 5. Run Migrations & Seed

```bash
php artisan migrate:fresh --seed
```

This creates demo users:
- **Admin**: admin@constructhub.com / password
- **User**: demo@example.com / password

### 6. Start Development Server

```bash
php artisan serve
```

Visit: http://localhost:8000

## Getting Started

1. Login with `demo@example.com` / `password`
2. Go to Dashboard
3. Click "New Build"
4. Enter a name (e.g., "My First House")
5. Start building!

## Building Parts

### Walls (6 types)
- Exterior Wall Large (4m × 3m)
- Exterior Wall Medium (2m × 3m)
- Interior Wall Large (4m × 2.8m)
- Interior Wall Small (2m × 2.8m)
- Glass Wall (2m × 2m)
- Foundation (4m × 0.5m)

### Floors (3 types)
- Tile Floor (1m × 1m)
- Hardwood Floor (1m × 1m)
- Carpet Floor (1m × 1m)

### Roofs (2 types)
- Flat Roof (4m × 4m)
- Peaked Roof (4m × 4m)

### Doors & Windows
- Single Door (0.9m × 2.1m)
- Double Door (1.8m × 2.1m)
- Single Window (1m × 1m)
- Double Window (2m × 1m)

### Special
- Stairs (1m × 2m)

## Build a House

### Step 1: Foundation
1. Select "Foundation" from Walls
2. Place on grid

### Step 2: Exterior Walls
1. Select "Floor 1" tab
2. Choose "Exterior Wall Large"
3. Click to place walls around foundation
4. Create rooms by placing interior walls

### Step 3: Floors
1. Select "Tile Floor" from Floors
2. Cover the floor area

### Step 4: Doors & Windows
1. Place doors in walls
2. Place windows above walls

### Step 5: Add Second Floor
1. Click "+" to add Floor 2
2. Place walls, floors, doors, windows

### Step 6: Add Roof
1. Select "Flat Roof"
2. Click above walls to auto-position
3. Toggle "Hide Roof" to see inside

### Step 7: Save & Export
1. Click "Save" (or Ctrl+S)
2. Click "PNG" to export image
3. Click "JSON" to backup build
4. Click "Share" to copy link

```bash
php artisan serve
npm run dev
```

## Supabase Setup

### 1. Create a Supabase Project

1. Go to [supabase.com](https://supabase.com) and create a new project
2. Copy the connection details from Settings > Database

### 2. Enable Realtime

1. Go to Database > Replication
2. Enable replication for the tables you'll use

### 3. Get API Keys

1. Go to Settings > API
2. Copy the `anon` public key and `service_role` secret key

## Database Schema

The application uses the following tables:

- `users` - User accounts
- `teams` - Team/workspace grouping
- `team_user` - Team membership
- `blueprints` - Blueprint projects with JSON canvas data
- `blueprint_members` - Blueprint access control
- `chat_messages` - In-room chat messages

## Deployment to Render

### 1. Create Render Account

Sign up at [render.com](https://render.com)

### 2. Create a Web Service

1. New > Web Service
2. Connect your GitHub repository
3. Configure:
   - **Root Directory**: (leave empty)
   - **Build Command**: `composer install && npm install && npm run build`
   - **Start Command**: `php artisan serve`

### 3. Add Environment Variables

Add all variables from your `.env` file in Render's dashboard.

### 4. Configure Supabase

Make sure your Supabase project allows connections from Render's IPs, or enable SSL connections.

## Architecture

```
┌─────────────────────┐     ┌─────────────────────┐
│  Browser A          │     │  Browser B          │
│  ┌───────────────┐  │     │  ┌───────────────┐  │
│  │ fabric.js     │  │     │  │ fabric.js     │  │
│  │ Canvas        │  │     │  │ Canvas        │  │
│  └───────┬───────┘  │     │  └───────┬───────┘  │
│          │          │     │          │          │
│  ┌───────▼───────┐  │     │  ┌───────▼───────┐  │
│  │ Supabase JS   │  │     │  │ Supabase JS   │  │
│  │ Client        │  │     │  │ Client        │  │
│  └───────┬───────┘  │     │  └───────┬───────┘  │
└──────────┼──────────┘     └──────────┼──────────┘
           │                           │
           │  Broadcast Events         │
           └──────────┬────────────────┘
                      │
           ┌──────────▼────────────────┐
           │  Supabase Realtime        │
           │  Channel: blueprint:{id}   │
           └──────────┬────────────────┘
                      │
           ┌──────────▼────────────────┐
           │  Laravel Backend (AJAX)   │
           │  POST /api/canvas/save    │
           └──────────┬────────────────┘
                      │
           ┌──────────▼────────────────┐
           │  Supabase PostgreSQL      │
           │  blueprints.canvas_json   │
           └───────────────────────────┘
```

## License

MIT License - See LICENSE file for details.

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

## Support

For issues and feature requests, please use GitHub Issues.
