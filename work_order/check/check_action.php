<?php
include '../../includes/session_check.php';
include '../../includes/role_check.php';
only(['Supervisor', 'Super Administrator']);

include '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = $_POST['id'] ?? 0;
  $action = $_POST['action'] ?? '';

  if (!$id || !$action) die("Invalid request");

  if ($action === 'finish') {
    $status = 'FINISHED';
  } elseif ($action === 'reject') {
    $status = 'REJECTED';
  } else {
    die("Invalid action");
  }

  $query = "UPDATE work_order SET status = '$status' WHERE id_work_order = $id";
  if (mysqli_query($conn, $query)) {
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script><script>Swal.fire({icon: 'success', title: 'Berhasil!', text: 'Work Order telah diubah menjadi $status'}).then(() => { window.location='check.php'; });</script>";
  } else {
    echo "<div class='alert alert-danger'>Gagal update: " . mysqli_error($conn) . "</div>";
  }
}
?>
