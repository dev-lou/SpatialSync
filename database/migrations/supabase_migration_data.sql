-- =============================================
-- Demo seed data
-- =============================================
-- This file used to insert three users together with their bcrypt password
-- hashes. Those hashes were committed to a public repository, which made the
-- demo accounts on the live instance effectively public credentials -- anyone
-- reading the repo could try to log in as the admin.
--
-- The user rows are gone. Create accounts through the app's own register flow,
-- then promote the one you want:
--
--   UPDATE users SET is_admin = TRUE WHERE email = 'you@example.com';
--
-- If the live instance still has the old accounts, change their passwords
-- there (or delete them) before sharing the demo link.
-- =============================================

-- Teams
INSERT INTO teams (id, name, created_at, updated_at) VALUES
('aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa', 'Demo Team', '2026-04-08 11:14:19', '2026-04-08 11:14:19');

-- Builds (created_by is left NULL; assign them to a real user after you register)
INSERT INTO builds (id, team_id, name, description, canvas_json, created_by, current_floor, roof_visible, created_at, updated_at) VALUES
('11111111-1111-1111-1111-000000000001', 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa', 'Modern House', 'Contemporary 2-story home with open floor plan', '{"version":"1.0","parts":[]}', NULL, 1, true, '2026-04-08 11:14:19', '2026-04-08 11:14:19'),
('11111111-1111-1111-1111-000000000002', 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa', 'Beach Villa', 'Luxury beachfront property with multiple floors', '{"version":"1.0","parts":[]}', NULL, 1, true, '2026-04-08 11:14:19', '2026-04-08 11:14:19'),
('11111111-1111-1111-1111-000000000003', 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa', 'Cozy Cottage', 'Rustic 1-story home with garden', '{"version":"1.0","parts":[]}', NULL, 1, true, '2026-04-08 11:14:19', '2026-04-08 11:14:19');

-- Build parts: a small demo footprint (two walls and a door)
INSERT INTO build_parts (id, build_id, type, variant, position_x, position_y, position_z, width, height, depth, rotation_y, color, material, floor_number, z_index, created_at, updated_at) VALUES
('aaaaaaa1-aaaa-aaaa-aaaa-aaaaaaaaaaaa', '11111111-1111-1111-1111-000000000001', 'wall', 'standard', 11, 1.5, 12, 4, 3, 0.2, 0, '#6B7280', 'default', 1, 0, '2026-04-08 13:25:46', '2026-04-08 13:25:46'),
('aaaaaaa2-aaaa-aaaa-aaaa-aaaaaaaaaaaa', '11111111-1111-1111-1111-000000000001', 'wall', 'standard', 15, 1.5, 12, 4, 3, 0.2, 0, '#6B7280', 'default', 1, 0, '2026-04-08 13:26:01', '2026-04-08 13:26:01'),
('aaaaaaa3-aaaa-aaaa-aaaa-aaaaaaaaaaaa', '11111111-1111-1111-1111-000000000001', 'door', 'single', 15, 1.2, 12.11, 1, 2.4, 0.2, 0, '#78350F', 'default', 1, 0, '2026-04-08 15:02:36', '2026-04-08 15:02:36');

SELECT 'Demo data complete!' as message;
