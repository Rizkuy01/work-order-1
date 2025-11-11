<?php
function only($roles = []) {
    if (!isset($_SESSION['role'])) {
        header("Location: ../auth/login.php");
        exit;
    }

    // Super Admin bisa akses semua halaman
    if ($_SESSION['role'] === 'Super Administrator' || $_SESSION['role'] === 'Super Admin') {
        return;
    }

    if (!in_array($_SESSION['role'], $roles)) {
        echo "<div class='alert alert-danger m-3'>⚠️ Akses ditolak. Anda tidak memiliki izin untuk membuka halaman ini.</div>";
        include '../includes/footer.php';
        exit;
    }
}
?>
