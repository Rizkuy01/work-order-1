<?php
include '../../includes/session_check.php';
include '../../config/database.php';
include '../../includes/role_check.php';

// Hanya Maintenance yang bisa delete
only(['Maintenance']);

$id = $_GET['id'] ?? 0;
if ($id) {
  mysqli_query($conn, "DELETE FROM work_order WHERE id_work_order=$id");
}
header("Location: ../index.php");
exit;
?>
