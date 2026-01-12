<?php
include '../../includes/session_check.php';
include '../../includes/role_check.php';
only(['Supervisor', 'Super Administrator']);
include '../../config/database.php';

header('Content-Type: application/json');

$id     = $_POST['id'] ?? 0;
$action = $_POST['action'] ?? '';
$note   = mysqli_real_escape_string($conn, $_POST['reject_note'] ?? '');
$user   = mysqli_real_escape_string($conn, $_SESSION['nama']);

if (!$id || !$action) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

if ($action === 'approve') {

    $query = "
        UPDATE work_order 
        SET status = 'FINISHED',
            person_finish = '$user'
        WHERE id_work_order = $id
    ";

    $msg = "Work Order telah <b>DITERIMA</b> dan status menjadi <b>FINISHED</b>.";

} elseif ($action === 'reject') {

    $query = "
        UPDATE work_order 
        SET status = 'OPENED',
            reject_note = '$note',
            reject_date = NOW()
        WHERE id_work_order = $id
    ";

    $msg = "Work Order telah <b>DITOLAK</b> dan status dikembalikan ke <b>OPENED</b> untuk dikerjakan ulang.<br>Alasan: $note";

} else {

    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    exit;
}

if (mysqli_query($conn, $query)) {
    echo json_encode(['status' => 'success', 'message' => $msg]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . mysqli_error($conn)]);
}
?>
