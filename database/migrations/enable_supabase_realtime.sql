-- =============================================
-- ENABLE SUPABASE REALTIME FOR TABLES
-- Run this in Supabase SQL Editor:
-- https://mpxdhzazdzkygercrniz.supabase.co/project/default/sql
-- =============================================

-- Enable realtime for build_messages (chat)
ALTER PUBLICATION supabase_realtime ADD TABLE build_messages;

-- Enable realtime for build_parts (3D collaboration sync)
ALTER PUBLICATION supabase_realtime ADD TABLE build_parts;

-- Verify realtime is enabled
SELECT schemaname, tablename 
FROM pg_publication_tables 
WHERE pubname = 'supabase_realtime';

-- If the above returns build_messages and build_parts, realtime is enabled.
-- If you get an error "publication does not exist", run this instead:
-- SELECT pg_create_logical_replication_slot('supabase_realtime', 'pgoutput');
-- CREATE PUBLICATION supabase_realtime FOR ALL TABLES;
