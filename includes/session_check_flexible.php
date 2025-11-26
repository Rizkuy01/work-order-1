<?php
session_start();

if (!isset($_SESSION['npk']) || !isset($_SESSION['role'])) {
    // Jika akses dashboard atau index tanpa login, izinkan dengan role "Guest"
    $current_page = basename($_SERVER['PHP_SELF']);
    
    if (!in_array($current_page, ['dashboard.php', 'index.php'])) {
        header("Location: ../auth/login.php");
        exit;
    }
    
    // Set session untuk guest
    $_SESSION['npk'] = 'GUEST';
    $_SESSION['nama'] = 'Guest User';
    $_SESSION['role'] = 'Guest';
    $_SESSION['is_guest'] = true;
}
?>
