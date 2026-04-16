-- =============================================
-- MIGRATION DATA: Copy this and run in Supabase
-- =============================================
-- This file contains sample user data
-- Replace with your actual data or run the Laravel command

-- Sample users (replace with your actual data)
-- INSERT INTO users (id, name, email, password, is_admin, created_at, updated_at) VALUES
-- ('user-uuid-here', 'Your Name', 'your@email.com', 'hashed_password', false, NOW(), NOW());

-- Sample teams
-- INSERT INTO teams (id, name, created_at, updated_at) VALUES
-- ('team-uuid-here', 'Your Team Name', NOW(), NOW());

-- Sample builds
-- INSERT INTO builds (id, team_id, name, description, created_by, current_floor, roof_visible, created_at, updated_at) VALUES
-- ('build-uuid-here', 'team-uuid', 'My Build', 'Description', 'user-uuid', 1, true, NOW(), NOW());

-- Sample build parts (from existing database)
-- Run: php artisan migrate:to-supabase
-- Then copy the output from: database/migrations/supabase_migration_data.sql

-- =============================================
-- TO MIGRATE YOUR EXISTING DATA:
-- =============================================
-- 1. Make sure SQLite database exists at: database/database.sqlite
-- 2. Run: php artisan migrate:to-supabase
-- 3. Copy output from: database/migrations/supabase_migration_data.sql
-- 4. Paste into Supabase SQL Editor and run

-- =============================================
-- SAMPLE DATA FOR TESTING
-- =============================================

-- Sample user for testing (password: password123)
INSERT INTO users (id, name, email, password, is_admin, created_at, updated_at) VALUES 
('00000000-0000-0000-0000-000000000001', 'Test User', 'test@example.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5NU7yYL3J0NHy', false, NOW(), NOW());

-- Sample team
INSERT INTO teams (id, name, created_at, updated_at) VALUES 
('00000000-0000-0000-0000-000000000002', 'Test Team', NOW(), NOW());

SELECT 'Migration template ready - run php artisan migrate:to-supabase for your data' as message;