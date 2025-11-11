<?php
include '../../includes/session_check.php';
include '../../includes/role_check.php';
only(['Maintenance', 'Super Administrator']);

include '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = $_POST['id'] ?? 0;
  $action = $_POST['action'] ?? '';

  if ($action === 'progress') {
    $status = 'ON PROGRESS';
  } elseif ($action === 'finish') {
    $status = 'WAITING CHECKED';
  } else {
    die("Aksi tidak valid");
  }

  $query = "UPDATE work_order SET status = '$status' WHERE id_work_order = $id";
  if (mysqli_query($conn, $query)) {
    echo "<script>alert('Status Work Order berhasil diubah menjadi $status');window.location='maintenance.php';</script>";
  } else {
    echo "<div class='alert alert-danger'>Gagal update: " . mysqli_error($conn) . "</div>";
  }
}
?>
