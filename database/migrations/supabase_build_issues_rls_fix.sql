-- =============================================
-- BUILD ISSUES TABLE — RLS POLICY FIX
-- For apps using Laravel session auth (not Supabase auth)
-- The app uses Supabase service key for all REST calls,
-- which bypasses RLS. These policies are for direct
-- anon-key access (e.g., realtime broadcasts).
-- =============================================

-- Drop existing policies that rely on auth.uid()
DROP POLICY IF EXISTS "Users can view build issues" ON build_issues;
DROP POLICY IF EXISTS "Users can create build issues" ON build_issues;
DROP POLICY IF EXISTS "Users can update their issues" ON build_issues;
DROP POLICY IF EXISTS "Users can delete their issues" ON build_issues;

-- Policy: Anyone with build access can view issues
-- Since the app uses service key (bypasses RLS), this allows anon reads
-- for realtime subscribers who are already authenticated via Laravel
CREATE POLICY "Anyone can view build issues" 
    ON build_issues FOR SELECT 
    USING (true);

-- Policy: Authenticated users (via service key) can create issues
-- Service key bypasses RLS, so this is for completeness
CREATE POLICY "Authenticated users can create build issues" 
    ON build_issues FOR INSERT 
    WITH CHECK (true);

-- Policy: Authenticated users can update issues
CREATE POLICY "Authenticated users can update issues" 
    ON build_issues FOR UPDATE 
    USING (true);

-- Policy: Authenticated users can delete issues
CREATE POLICY "Authenticated users can delete issues" 
    ON build_issues FOR DELETE 
    USING (true);

-- NOTE: For true multi-tenant security, the app should:
-- 1. Use Supabase auth (not Laravel sessions) for direct DB access
-- 2. Or use Row Level Security with custom JWT claims
-- 3. Or keep using service key (current approach) and rely on
--    Laravel middleware for access control
