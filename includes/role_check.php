<?php
function only($roles = []) {

    // SESSION WAJIB ADA
    if (!isset($_SESSION['role'])) {
        header("Location: /work-order/auth/login.php");
        exit;
    }

    // Super Admin bebas akses
    if ($_SESSION['role'] === 'Super Administrator' || $_SESSION['role'] === 'Super Admin') {
        return;
    }

    // Jika role user tidak sesuai
    if (!in_array($_SESSION['role'], $roles)) {
        ?>
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Akses Ditolak</title>
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        </head>
        <body>
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Akses Ditolak!',
                    text: 'Anda tidak memiliki izin untuk membuka halaman ini.',
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Kembali'
                }).then((result) => {
                    window.history.back();
                });
            </script>
        </body>
        </html>
        <?php
        exit;
    }
}
?>
