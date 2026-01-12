-- =====================================================================
-- SQL Migration: Normalize Status from 'OPEN' to 'OPENED'
-- =====================================================================
-- Jalankan script ini jika ingin clean database dengan standardisasi status
-- atau jika sudah ada data dengan status 'OPEN' yang ingin dikonversi
-- =====================================================================

-- 1. Backup data sebelum migration (Optional)
-- CREATE TABLE work_order_backup AS SELECT * FROM work_order;

-- 2. Convert semua 'OPEN' menjadi 'OPENED'
UPDATE work_order SET status = 'OPENED' WHERE status = 'OPEN';

-- 3. Verify hasil konversi - harus hanya ada 'OPENED', bukan 'OPEN'
-- SELECT DISTINCT status FROM work_order ORDER BY status;

-- 4. Jika diperlukan, cek jumlah status per tipe sebelum & sesudah
-- SELECT 
--     status, 
--     COUNT(*) as total 
-- FROM work_order 
-- GROUP BY status 
-- ORDER BY status;

-- =====================================================================
-- CATATAN:
-- - Script ini aman dijalankan berkali-kali (idempotent)
-- - Jika tidak ada status 'OPEN', tidak ada yang berubah
-- - Sistem aplikasi sudah handle kedua variasi, jadi migration ini optional
-- =====================================================================
