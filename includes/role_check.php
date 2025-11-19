<?php
function only($roles = []) {

    // SESSION WAJIB ADA
    if (!isset($_SESSION['role'])) {
        header("Location: ../auth/login.php");
        exit;
    }

    // Super Admin bebas akses
    if ($_SESSION['role'] === 'Super Administrator' || $_SESSION['role'] === 'Super Admin') {
        return;
    }

    // Jika role user tidak sesuai
    if (!in_array($_SESSION['role'], $roles)) {

        // ======== AUTO DETECT BASE URL UNTUK REDIRECT ========
        // Cara kerja:
        // - Ambil URL sekarang (misal: /work_order/actions/add.php)
        // - Hitung berapa level folder ke atas
        // - Redirect otomatis ke index.php yang benar

        $currentPath = $_SERVER['REQUEST_URI']; 
        $depth = substr_count($currentPath, "/"); 

        // Buat path "../" sebanyak depth folder
        $backPath = str_repeat("../", max(1, $depth - 2));

        // Hasil akhir: ../../index.php atau ../../../index.php (otomatis)
        $redirectTo = $backPath . "index.php";

        // ======================================================

        echo "
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Akses Ditolak!',
                html: 'Anda tidak memiliki izin untuk membuka halaman ini.',
                confirmButtonColor: '#d33',
                confirmButtonText: 'Kembali'
            }).then(() => {
                window.location.href = '$redirectTo';
            });
        </script>
        ";

        exit;
    }
}
?>
