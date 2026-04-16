-- =============================================
-- Supabase Database Schema for ConstructHub
-- Run this in Supabase SQL Editor
-- =============================================

-- Enable UUID extension
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- =============================================
-- USERS TABLE
-- =============================================
CREATE TABLE IF NOT EXISTS users (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    avatar_url TEXT,
    is_admin BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Enable RLS (Row Level Security)
ALTER TABLE users ENABLE ROW LEVEL SECURITY;

-- =============================================
-- TEAMS TABLE
-- =============================================
CREATE TABLE IF NOT EXISTS teams (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

ALTER TABLE teams ENABLE ROW LEVEL SECURITY;

-- =============================================
-- BUILDS TABLE
-- =============================================
CREATE TABLE IF NOT EXISTS builds (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    team_id UUID REFERENCES teams(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    canvas_json JSONB,
    created_by UUID REFERENCES users(id) ON DELETE SET NULL,
    current_floor INTEGER DEFAULT 1,
    roof_visible BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

ALTER TABLE builds ENABLE ROW LEVEL SECURITY;

-- =============================================
-- BUILD MEMBERS TABLE (for collaboration)
-- =============================================
CREATE TABLE IF NOT EXISTS build_members (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    build_id UUID REFERENCES builds(id) ON DELETE CASCADE,
    user_id UUID REFERENCES users(id) ON DELETE CASCADE,
    role VARCHAR(50) DEFAULT 'viewer',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    UNIQUE(build_id, user_id)
);

ALTER TABLE build_members ENABLE ROW LEVEL SECURITY;

-- =============================================
-- BUILD PARTS TABLE
-- =============================================
CREATE TABLE IF NOT EXISTS build_parts (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    build_id UUID REFERENCES builds(id) ON DELETE CASCADE,
    type VARCHAR(50) NOT NULL,
    variant VARCHAR(50),
    position_x REAL NOT NULL,
    position_y REAL NOT NULL,
    position_z REAL NOT NULL,
    width REAL DEFAULT 1,
    height REAL DEFAULT 3,
    depth REAL DEFAULT 0.2,
    rotation_y INTEGER DEFAULT 0,
    color VARCHAR(20),
    color_front VARCHAR(20),
    color_back VARCHAR(20),
    material VARCHAR(50) DEFAULT 'default',
    shape_points JSONB,
    floor_number INTEGER DEFAULT 1,
    z_index INTEGER DEFAULT 0,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

ALTER TABLE build_parts ENABLE ROW LEVEL SECURITY;

-- Create index for faster queries
CREATE INDEX IF NOT EXISTS idx_build_parts_build_id ON build_parts(build_id);
CREATE INDEX IF NOT EXISTS idx_build_parts_floor ON build_parts(build_id, floor_number);

-- =============================================
-- PART PRESETS TABLE
-- =============================================
CREATE TABLE IF NOT EXISTS part_presets (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    name VARCHAR(255) NOT NULL,
    type VARCHAR(50) NOT NULL,
    variant VARCHAR(50),
    default_width REAL DEFAULT 1,
    default_height REAL DEFAULT 3,
    default_depth REAL DEFAULT 0.2,
    default_color VARCHAR(20),
    icon VARCHAR(50),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

ALTER TABLE part_presets ENABLE ROW LEVEL SECURITY;

-- =============================================
-- BUILD SHARES TABLE
-- =============================================
CREATE TABLE IF NOT EXISTS build_shares (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    build_id UUID REFERENCES builds(id) ON DELETE CASCADE,
    share_token VARCHAR(255) UNIQUE NOT NULL,
    access_level VARCHAR(50) DEFAULT 'view',
    expires_at TIMESTAMP WITH TIME ZONE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

ALTER TABLE build_shares ENABLE ROW LEVEL SECURITY;

-- =============================================
-- BUILD MESSAGES TABLE
-- =============================================
CREATE TABLE IF NOT EXISTS build_messages (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    build_id UUID REFERENCES builds(id) ON DELETE CASCADE,
    user_id UUID REFERENCES users(id) ON DELETE SET NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

ALTER TABLE build_messages ENABLE ROW LEVEL SECURITY;

-- =============================================
-- RLS POLICIES (Public read for now)
-- =============================================

-- Users: Everyone can read, only owner can update
CREATE POLICY "Enable read access for all users" ON users FOR SELECT USING (true);
CREATE POLICY "Enable insert for all users" ON users FOR INSERT WITH CHECK (true);
CREATE POLICY "Enable update for all users" ON users FOR UPDATE USING (true);
CREATE POLICY "Enable delete for all users" ON users FOR DELETE USING (true);

-- Teams: Same
CREATE POLICY "Enable read access for all users" ON teams FOR SELECT USING (true);
CREATE POLICY "Enable insert for all users" ON teams FOR INSERT WITH CHECK (true);
CREATE POLICY "Enable update for all users" ON teams FOR UPDATE USING (true);
CREATE POLICY "Enable delete for all users" ON teams FOR DELETE USING (true);

-- Builds: Same
CREATE POLICY "Enable read access for all users" ON builds FOR SELECT USING (true);
CREATE POLICY "Enable insert for all users" ON builds FOR INSERT WITH CHECK (true);
CREATE POLICY "Enable update for all users" ON builds FOR UPDATE USING (true);
CREATE POLICY "Enable delete for all users" ON builds FOR DELETE USING (true);

-- Build Parts: Same
CREATE POLICY "Enable read access for all users" ON build_parts FOR SELECT USING (true);
CREATE POLICY "Enable insert for all users" ON build_parts FOR INSERT WITH CHECK (true);
CREATE POLICY "Enable update for all users" ON build_parts FOR UPDATE USING (true);
CREATE POLICY "Enable delete for all users" ON build_parts FOR DELETE USING (true);

-- Part Presets: Same
CREATE POLICY "Enable read access for all users" ON part_presets FOR SELECT USING (true);
CREATE POLICY "Enable insert for all users" ON part_presets FOR INSERT WITH CHECK (true);
CREATE POLICY "Enable update for all users" ON part_presets FOR UPDATE USING (true);
CREATE POLICY "Enable delete for all users" ON part_presets FOR DELETE USING (true);

-- Build Members: Same
CREATE POLICY "Enable read access for all users" ON build_members FOR SELECT USING (true);
CREATE POLICY "Enable insert for all users" ON build_members FOR INSERT WITH CHECK (true);
CREATE POLICY "Enable update for all users" ON build_members FOR UPDATE USING (true);
CREATE POLICY "Enable delete for all users" ON build_members FOR DELETE USING (true);

-- Build Shares: Same
CREATE POLICY "Enable read access for all users" ON build_shares FOR SELECT USING (true);
CREATE POLICY "Enable insert for all users" ON build_shares FOR INSERT WITH CHECK (true);
CREATE POLICY "Enable update for all users" ON build_shares FOR UPDATE USING (true);
CREATE POLICY "Enable delete for all users" ON build_shares FOR DELETE USING (true);

-- Build Messages: Same
CREATE POLICY "Enable read access for all users" ON build_messages FOR SELECT USING (true);
CREATE POLICY "Enable insert for all users" ON build_messages FOR INSERT WITH CHECK (true);
CREATE POLICY "Enable update for all users" ON build_messages FOR UPDATE USING (true);
CREATE POLICY "Enable delete for all users" ON build_messages FOR DELETE USING (true);

-- =============================================
-- INSERT DEFAULT PART PRESETS
-- =============================================
INSERT INTO part_presets (id, name, type, variant, default_width, default_height, default_depth, default_color, icon, is_active) VALUES
    (uuid_generate_v4(), 'Wall', 'wall', 'standard', 1, 3, 0.2, '#6B7280', 'square', true),
    (uuid_generate_v4(), 'Tile Floor', 'floor', 'tile', 1, 0.05, 1, '#FFFFFF', 'layers', true),
    (uuid_generate_v4(), 'Hardwood Floor', 'floor', 'hardwood', 1, 0.05, 1, '#92400E', 'layers', true),
    (uuid_generate_v4(), 'Carpet Floor', 'floor', 'carpet', 1, 0.05, 1, '#9CA3AF', 'layers', true),
    (uuid_generate_v4(), 'Concrete Floor', 'floor', 'concrete', 1, 0.05, 1, '#6B7280', 'layers', true),
    (uuid_generate_v4(), 'Marble Floor', 'floor', 'marble', 1, 0.05, 1, '#F3F4F6', 'layers', true),
    (uuid_generate_v4(), 'Flat Roof', 'roof', 'flat', 4, 0.2, 4, '#374151', 'triangle', true),
    (uuid_generate_v4(), 'Door', 'door', 'single', 1, 2.4, 0.2, '#78350F', 'door-open', true),
    (uuid_generate_v4(), 'Window', 'window', 'single', 0.8, 0.8, 0.2, '#93C5FD', 'layout-grid', true),
    (uuid_generate_v4(), 'Stairs', 'stairs', 'standard', 1, 2, 2, '#F3F4F6', 'trending-up', true)
ON CONFLICT DO NOTHING;

SELECT 'Database schema created successfully!' as message;