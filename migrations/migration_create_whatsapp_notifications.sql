-- Migration: Create Notifications Table
-- Description: Table untuk menyimpan notifikasi yang akan dikirim ke maintenance

CREATE TABLE IF NOT EXISTS notifikasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    no_wa VARCHAR(20) NOT NULL COMMENT 'Nomor WhatsApp tujuan',
    message LONGTEXT NOT NULL COMMENT 'Pesan notifikasi',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu notifikasi dibuat',
    INDEX idx_created_at (created_at),
    INDEX idx_no_wa (no_wa)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel penyimpanan notifikasi untuk maintenance';
