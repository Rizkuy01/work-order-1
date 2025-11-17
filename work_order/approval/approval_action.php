<?php
include '../../includes/session_check.php';
include '../../includes/role_check.php';
only(['Supervisor', 'Super Administrator']);

include '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id        = $_POST['id'] ?? 0;
    $action    = $_POST['action'] ?? '';
    $nama_user = mysqli_real_escape_string($conn, $_SESSION['nama']);

    if (!$id || !$action) {
        die("Invalid request");
    }

    if ($action === 'approve') {

        $query = "
            UPDATE work_order 
            SET status = 'OPENED',
                person_approved = '$nama_user'
            WHERE id_work_order = $id
        ";

    } elseif ($action === 'reject') {

        $query = "
            UPDATE work_order 
            SET status = 'REJECTED'
            WHERE id_work_order = $id
        ";

    } else {
        die("Invalid action");
    }

    if (mysqli_query($conn, $query)) {
        echo "<script>
            alert('Work Order berhasil diupdate menjadi $action');
            window.location='approval.php';
        </script>";
    } else {
        echo "<div class='alert alert-danger'>Gagal update: " . mysqli_error($conn) . "</div>";
    }
}
?>
