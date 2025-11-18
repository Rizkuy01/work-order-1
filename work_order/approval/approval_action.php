<?php
include '../../includes/session_check.php';
include '../../includes/role_check.php';
only(['Supervisor', 'Super Administrator']);
include '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id        = $_POST['id'];
    $action    = $_POST['action'];
    $nama_user = mysqli_real_escape_string($conn, $_SESSION['nama']);

    if ($action === 'approve') {
        $query = "UPDATE work_order SET status='OPENED', person_approved='$nama_user' WHERE id_work_order=$id";
        $msg   = "Work Order berhasil <b>APPROVED</b>";
    } else {
        $query = "UPDATE work_order SET status='REJECTED' WHERE id_work_order=$id";
        $msg   = "Work Order telah <b>REJECTED</b>";
    }

    mysqli_query($conn, $query);
}
?>

<!DOCTYPE html>
<html>
<head>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<script>
Swal.fire({
    icon: 'success',
    title: 'Berhasil!',
    html: '<?= $msg ?>',
    confirmButtonColor: '#28a745',
    confirmButtonText: 'OK'
}).then(() => {
    window.location = 'approval.php';
});
</script>

</body>
</html>
