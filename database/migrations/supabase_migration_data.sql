-- =============================================
-- FIXED MIGRATION DATA: Copy this and run in Supabase
-- =============================================

-- Users - Convert to UUID format
INSERT INTO users (id, name, email, password, is_admin, created_at, updated_at) VALUES 
('11111111-1111-1111-1111-111111111111', 'Admin User', 'admin@blueprintfow.com', '$2y$12$9EVQBWWddzYZK3nnLsEzae/EW8BdAv4g/5XG3726Y3i80UT0utWzu', true, '2026-04-08 11:14:19', '2026-04-08 11:14:19'),
('22222222-2222-2222-2222-222222222222', 'Demo Editor', 'demo@example.com', '$2y$12$9EVQBWWddzYZK3nnLsEzae/EW8BdAv4g/5XG3726Y3i80UT0utWzu', false, '2026-04-08 11:14:19', '2026-04-08 11:14:19'),
('33333333-3333-3333-3333-333333333333', 'd', 'a@gmail.com', '$2y$12$utACBLmfHDHOGyhZU80L1u3hDRFbChBaGXt29WgnJCcqubtv3oZai', false, '2026-04-09 13:14:47', '2026-04-09 13:14:47');

-- Teams - Convert to UUID format
INSERT INTO teams (id, name, created_at, updated_at) VALUES 
('aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa', 'ConstructHub Team', '2026-04-08 11:14:19', '2026-04-08 11:14:19'),
('bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb', 'ds Team', '2026-04-09 13:14:47', '2026-04-09 13:14:47');

-- Builds - Convert to UUID format, update foreign keys
INSERT INTO builds (id, team_id, name, description, canvas_json, created_by, current_floor, roof_visible, created_at, updated_at) VALUES 
('11111111-1111-1111-1111-000000000001', 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa', 'Modern House', 'Contemporary 2-story home with open floor plan', '{"version":"1.0","objects":[]}', '22222222-2222-2222-2222-222222222222', 1, true, '2026-04-08 11:14:19', '2026-04-08 11:14:19'),
('11111111-1111-1111-1111-000000000002', 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa', 'Beach Villa', 'Luxury beachfront property with multiple floors', '{"version":"1.0","objects":[]}', '22222222-2222-2222-2222-222222222222', 1, true, '2026-04-08 11:14:19', '2026-04-08 11:14:19'),
('11111111-1111-1111-1111-000000000003', 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa', 'Cozy Cottage', 'Rustic 1-story home with garden', '{"version":"1.0","objects":[]}', '22222222-2222-2222-2222-222222222222', 1, true, '2026-04-08 11:14:19', '2026-04-08 11:14:19'),
('11111111-1111-1111-1111-000000000004', 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa', 'sd', 'ds', '{"version":"1.0","parts":[]}', '22222222-2222-2222-2222-222222222222', 1, true, '2026-04-09 03:45:40', '2026-04-09 03:45:40'),
('11111111-1111-1111-1111-000000000005', 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb', 'yea', 'd', '{"version":"1.0","parts":[]}', '33333333-3333-3333-3333-333333333333', 1, true, '2026-04-09 13:15:30', '2026-04-09 13:15:30');

-- Build Parts - Convert to UUID format, update build_id references
INSERT INTO build_parts (id, build_id, type, variant, position_x, position_y, position_z, width, height, depth, rotation_y, color, color_front, color_back, material, floor_number, z_index, created_at, updated_at) VALUES 
('aaaaaaa1-aaaa-aaaa-aaaa-aaaaaaaaaaaa', '11111111-1111-1111-1111-000000000001', 'wall', 'standard', 11, 1.5, 12, 4, 3, 0.2, 0, '#6B7280', NULL, NULL, 'default', 1, 0, '2026-04-08 13:25:46', '2026-04-08 13:25:46'),
('aaaaaaa2-aaaa-aaaa-aaaa-aaaaaaaaaaaa', '11111111-1111-1111-1111-000000000001', 'wall', 'standard', 15, 1.5, 12, 4, 3, 0.2, 0, '#6B7280', NULL, NULL, 'default', 1, 0, '2026-04-08 13:26:01', '2026-04-08 13:26:01'),
('aaaaaaa3-aaaa-aaaa-aaaa-aaaaaaaaaaaa', '11111111-1111-1111-1111-000000000001', 'door', 'single', 15, 1.2, 12.11, 1, 2.4, 0.2, 0, '#78350F', NULL, NULL, 'default', 1, 0, '2026-04-08 15:02:36', '2026-04-08 15:02:36'),
('aaaaaaa4-aaaa-aaaa-aaaa-aaaaaaaaaaaa', '11111111-1111-1111-1111-000000000004', 'wall', 'standard', 15, 1.5, 17.5, 1, 3, 0.2, 90, '#6B7280', '#6B7280', '#6B7280', 'default', 1, 0, '2026-04-09 04:24:06', '2026-04-09 04:24:06');

SELECT 'Migration complete!' as message;