<?php
include '../../includes/session_check.php';
include '../../includes/role_check.php';
only(['Supervisor', 'Super Administrator']);
include '../../config/database.php';
include '../../config/notification_helper.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id        = $_POST['id'];
    $action    = $_POST['action'];
    $nama_user = mysqli_real_escape_string($conn, $_SESSION['nama']);

    if ($action === 'approve') {
        // Standardisasi status ke 'OPENED' ketika approve
        $query = "UPDATE work_order SET status='OPENED', person_approved='$nama_user' WHERE id_work_order=$id";
        $msg   = "Work Order berhasil <b>APPROVED</b>";
        
        // ===== INSERT NOTIFIKASI UNTUK MAINTENANCE =====
        // Ambil data WO dan schedule
        $wo_query = "
            SELECT wo.*, ws.plan_date, ws.plan_time
            FROM work_order wo
            LEFT JOIN wo_schedule ws ON wo.id_work_order = ws.id_work_order
            WHERE wo.id_work_order = $id
        ";
        $wo_result = mysqli_query($conn, $wo_query);
        $wo_data = mysqli_fetch_assoc($wo_result);
        
        if ($wo_data) {
            // Kumpulkan nama PIC dari work_order
            $pic_names = [
                $wo_data['pic'] ?? null,
                $wo_data['pic2'] ?? null,
                $wo_data['pic3'] ?? null
            ];
            
            // Insert notifikasi untuk semua PIC
            insertNotifikasiForPICs($conn, $conn_lembur, $pic_names, $wo_data);
        }
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
