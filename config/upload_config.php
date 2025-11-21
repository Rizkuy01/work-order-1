<?php
/**
 * Konfigurasi Path Upload
 * File ini mendefinisikan path untuk upload foto Before & After
 * yang disimpan di folder terpisah: c:\laragon\www\work-order-files\
 */

// Path absolut ke folder uploads (di luar project)
define('UPLOADS_BASE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/work-order-files/');

// Path untuk foto before
define('UPLOADS_BEFORE_DIR', UPLOADS_BASE_PATH . 'before/');

// Path untuk foto after
define('UPLOADS_AFTER_DIR', UPLOADS_BASE_PATH . 'after/');

// URL path untuk menampilkan di browser
define('UPLOADS_BEFORE_URL', '/work-order-files/before/');
define('UPLOADS_AFTER_URL', '/work-order-files/after/');

// Fungsi untuk memastikan direktori ada
function ensureUploadDirs() {
    if (!file_exists(UPLOADS_BEFORE_DIR)) {
        mkdir(UPLOADS_BEFORE_DIR, 0777, true);
    }
    if (!file_exists(UPLOADS_AFTER_DIR)) {
        mkdir(UPLOADS_AFTER_DIR, 0777, true);
    }
}

?>
