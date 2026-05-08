-- =============================================
-- BUILD ISSUES TABLE (for 3D Issue Pins)
-- Run this in Supabase SQL Editor
-- =============================================

-- Create build_issues table
CREATE TABLE IF NOT EXISTS build_issues (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    build_id UUID REFERENCES builds(id) ON DELETE CASCADE,
    part_id UUID REFERENCES build_parts(id) ON DELETE SET NULL,
    created_by UUID REFERENCES users(id) ON DELETE SET NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status VARCHAR(50) DEFAULT 'open',
    priority VARCHAR(50) DEFAULT 'medium',
    position_x REAL,
    position_y REAL,
    position_z REAL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Create indexes for performance
CREATE INDEX IF NOT EXISTS idx_build_issues_build_id ON build_issues(build_id);
CREATE INDEX IF NOT EXISTS idx_build_issues_part_id ON build_issues(part_id);
CREATE INDEX IF NOT EXISTS idx_build_issues_status ON build_issues(status);

-- Enable RLS
ALTER TABLE build_issues ENABLE ROW LEVEL SECURITY;

-- Create RLS policies
-- Policy: Users can view issues for builds they have access to
CREATE POLICY "Users can view build issues" 
    ON build_issues FOR SELECT 
    USING (
        EXISTS (
            SELECT 1 FROM builds b 
            WHERE b.id = build_issues.build_id 
            AND (
                b.created_by = auth.uid() 
                OR EXISTS (
                    SELECT 1 FROM build_members bm 
                    WHERE bm.build_id = b.id 
                    AND bm.user_id = auth.uid()
                )
            )
        )
    );

-- Policy: Users can create issues for builds they have access to
CREATE POLICY "Users can create build issues" 
    ON build_issues FOR INSERT 
    WITH CHECK (
        EXISTS (
            SELECT 1 FROM builds b 
            WHERE b.id = build_issues.build_id 
            AND (
                b.created_by = auth.uid() 
                OR EXISTS (
                    SELECT 1 FROM build_members bm 
                    WHERE bm.build_id = b.id 
                    AND bm.user_id = auth.uid()
                )
            )
        )
    );

-- Policy: Only issue creator or build admin can update
CREATE POLICY "Users can update their issues" 
    ON build_issues FOR UPDATE 
    USING (
        created_by = auth.uid() 
        OR EXISTS (
            SELECT 1 FROM builds b 
            WHERE b.id = build_issues.build_id 
            AND b.created_by = auth.uid()
        )
        OR EXISTS (
            SELECT 1 FROM build_members bm 
            WHERE bm.build_id = build_issues.build_id 
            AND bm.user_id = auth.uid() 
            AND bm.role = 'admin'
        )
    );

-- Policy: Only issue creator or build admin can delete
CREATE POLICY "Users can delete their issues" 
    ON build_issues FOR DELETE 
    USING (
        created_by = auth.uid() 
        OR EXISTS (
            SELECT 1 FROM builds b 
            WHERE b.id = build_issues.build_id 
            AND b.created_by = auth.uid()
        )
        OR EXISTS (
            SELECT 1 FROM build_members bm 
            WHERE bm.build_id = build_issues.build_id 
            AND bm.user_id = auth.uid() 
            AND bm.role = 'admin'
        )
    );

-- Create updated_at trigger
CREATE OR REPLACE FUNCTION update_build_issues_updated_at()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER update_build_issues_updated_at
    BEFORE UPDATE ON build_issues
    FOR EACH ROW
    EXECUTE FUNCTION update_build_issues_updated_at();
