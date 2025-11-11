<?php
include '../../includes/session_check.php';
include '../../includes/role_check.php';
only(['Supervisor', 'Super Administrator']);

include '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? 0;
    $action = $_POST['action'] ?? '';

    if (!$id || !$action) {
        die("Invalid request");
    }

    if ($action === 'approve') {
        $status = 'OPENED';
    } elseif ($action === 'reject') {
        $status = 'REJECTED';
    } else {
        die("Invalid action");
    }

    $query = "UPDATE work_order SET status = '$status' WHERE id_work_order = $id";

    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Work Order berhasil diupdate menjadi $status');window.location='approval.php';</script>";
    } else {
        echo "<div class='alert alert-danger'>Gagal update: " . mysqli_error($conn) . "</div>";
    }
}
?>
