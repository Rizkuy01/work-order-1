<?php
include '../../includes/session_check.php';
include '../../includes/role_check.php';
only(['Maintenance', 'Super Administrator']);
include '../../config/database.php';
include '../../config/upload_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id       = $_POST['id'] ?? 0;
    $action   = $_POST['action'] ?? '';
    $nama     = mysqli_real_escape_string($conn, $_SESSION['nama']); 
    $jam_now  = date('H:i:s');

    if (!$id || !$action) {
        die("Invalid request");
    }

    // ================== ACTION MULAI (progress) ==================
    if ($action === 'progress') {

        $query = "
            UPDATE work_order 
            SET status = 'ON PROGRESS',
                person_accept = '$nama'
            WHERE id_work_order = $id
        ";

        $message = "Work Order telah dimulai oleh <b>$nama</b>";

    // ================== ACTION SELESAI (finish) ==================
    } elseif ($action === 'finish') {

        // ---- upload fotoafter (opsional) ----
        $fotoName = null;

        if (!empty($_FILES['fotoafter']['name'])) {
            ensureUploadDirs();
            $ext = pathinfo($_FILES['fotoafter']['name'], PATHINFO_EXTENSION);
            $fotoName = "AFTER_" . time() . "_" . rand(1000,9999) . "." . $ext;

            move_uploaded_file(
                $_FILES['fotoafter']['tmp_name'],
                UPLOADS_AFTER_DIR . $fotoName
            );
        }

        $query = "
            UPDATE work_order SET
                status       = 'WAITING CHECKED',
                person_finish = '$nama',
                jam_finish    = '$jam_now',
                fotoafter     = " . ($fotoName ? "'$fotoName'" : "fotoafter") . "
            WHERE id_work_order = $id
        ";

        $message = "Work Order berhasil diselesaikan oleh <b>$nama</b>";

    } else {
        die("Invalid action");
    }

    $success = mysqli_query($conn, $query);
}
?>

<!DOCTYPE html>
<html>
<head>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<?php if (!empty($success)) : ?>
<script>
Swal.fire({
    icon: 'success',
    title: 'Berhasil!',
    html: '<?= $message ?>',
    confirmButtonColor: '#28a745'
}).then(() => {
    window.location = 'maintenance.php';
});
</script>

<?php else: ?>
<script>
Swal.fire({
    icon: 'error',
    title: 'Gagal!',
    html: 'Terjadi kesalahan saat update Work Order.',
    confirmButtonColor: '#d33'
}).then(() => {
    window.location = 'maintenance.php';
});
</script>
<?php endif; ?>

</body>
</html>
