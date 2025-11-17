<?php
include '../../includes/session_check.php';
include '../../includes/role_check.php';
only(['Maintenance', 'Super Administrator']);

include '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id     = $_POST['id'] ?? 0;
    $action = $_POST['action'] ?? '';
    $nama   = mysqli_real_escape_string($conn, $_SESSION['nama']); 
    $jam_now = date('H:i:s'); 

    if (!$id || !$action) {
        die("Invalid request");
    }

    if ($action === 'progress') {
        $query = "
            UPDATE work_order 
            SET status = 'ON PROGRESS',
                person_accept = '$nama'
            WHERE id_work_order = $id
        ";

        $message = "Work Order telah dimulai oleh $nama";
    }

    elseif ($action === 'finish') {
        $query = "
            UPDATE work_order 
            SET status = 'WAITING CHECKED',
                person_finish = '$nama',
                jam_finish = '$jam_now'
            WHERE id_work_order = $id
        ";

        $message = "Work Order telah diselesaikan oleh $nama";
    }

    else {
        die("Aksi tidak valid");
    }

    if (mysqli_query($conn, $query)) {
        echo "<script>alert('$message');window.location='maintenance.php';</script>";
    } else {
        echo "<div class='alert alert-danger'>Gagal update: " . mysqli_error($conn) . "</div>";
    }
}
?>
