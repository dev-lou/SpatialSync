-- =============================================
-- Remove stored facial-biometric templates
-- =============================================
-- Face login was removed from the application (2026-09-14). The code path was
-- 1:N identification against every enrolled user at a 0.45 distance threshold,
-- and it stored a face template per user in users.biometric_data.
--
-- Deleting the UI is not enough: the stored templates are the part that carries
-- legal exposure (facial templates are special-category data under GDPR Art. 9,
-- and Illinois BIPA 740 ILCS 14/15(b) requires written release + a retention
-- schedule before collection). Clearing the column removes the data itself.
--
-- Run this in the Supabase SQL Editor against the live project.
-- =============================================

UPDATE users
SET biometric_data = NULL
WHERE biometric_data IS NOT NULL;

-- Once you have confirmed no code or session references it any more, drop the
-- column entirely so nothing can repopulate it:
--
-- ALTER TABLE users DROP COLUMN IF EXISTS biometric_data;

SELECT 'Biometric templates cleared.' AS message;
