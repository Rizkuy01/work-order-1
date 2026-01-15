<?php
include '../includes/session_check_flexible.php';
include '../config/database.php';
include '../config/status_helper.php';

$role = $_SESSION['role'];
$nama = $_SESSION['nama'];

$response = [];
$notif = [];
$query = "";

// =============================
// 1️⃣ Supervisor (approval + final checking)
// =============================
if ($role === "Supervisor") {
    $query = "
        SELECT * FROM work_order
        WHERE status='WAITING APPROVAL'
        OR status='WAITING CHECKED'
        ORDER BY tgl_input DESC
        LIMIT 10
    ";
}

// =============================
// 2️⃣ Foreman (scheduling)
// =============================
elseif ($role === "Foreman") {
    $query = "
        SELECT * FROM work_order
        WHERE status='WAITING SCHEDULE'
        ORDER BY tgl_input DESC
        LIMIT 10
    ";
}

// =============================
// 3️⃣ Maintenance (WO yang di-assign kepadanya)
// =============================
elseif ($role === "Maintenance") {
    $query = "
        SELECT * FROM work_order
        WHERE (status='OPENED' OR status='OPEN'
            OR status='SCHEDULED'
            OR status='IN PROGRESS'
            OR status='FINISHED'
            OR status='REJECTED')
        AND (pic = '$nama' OR pic2 = '$nama' OR pic3 = '$nama')
        ORDER BY tgl_input DESC
        LIMIT 10
    ";
}

// =============================
// 4️⃣ Operator - tidak punya notif
// =============================
elseif ($role === "Operator") {
    $query = ""; // no notif
}

// =============================
// 5️⃣ Super Administrator - tampilkan semua notif
// =============================
elseif ($role === "Super Administrator") {
    $query = "
        SELECT * FROM work_order
        ORDER BY tgl_input DESC
        LIMIT 10
    ";
}

// =============================
// Eksekusi Query
// =============================
if ($query != "") {
    $res = mysqli_query($conn, $query);
    while ($row = mysqli_fetch_assoc($res)) {
        $notif[] = $row;
    }
}

echo json_encode([
    "count" => count($notif),
    "data" => $notif
]);
?>
