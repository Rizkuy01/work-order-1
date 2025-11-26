<?php
include '../includes/session_check.php';
include '../config/database.php';

// Ambil filter dari POST
$dept = $_POST['dept'] ?? '';
$line = $_POST['line'] ?? '';
$mesin = $_POST['mesin'] ?? '';

// Build WHERE clause
$where = "WHERE 1=1";

if (!empty($dept)) {
    $safeDept = mysqli_real_escape_string($conn, $dept);
    $where .= " AND dept = '$safeDept'";
}

if (!empty($line)) {
    $safeLine = mysqli_real_escape_string($conn, $line);
    $where .= " AND line = '$safeLine'";
}

if (!empty($mesin)) {
    $safeMesin = mysqli_real_escape_string($conn, $mesin);
    $where .= " AND nama_mesin = '$safeMesin'";
}

// Ambil statistik WO
$totalWO     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM work_order $where"))['total'] ?? 0;
$woWaiting   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM work_order $where AND status='WAITING SCHEDULE'"))['total'] ?? 0;
$woApproval  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM work_order $where AND status='WAITING APPROVAL'"))['total'] ?? 0;
$woOpened    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM work_order $where AND status='OPENED'"))['total'] ?? 0;
$woProgress  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM work_order $where AND status='ON PROGRESS'"))['total'] ?? 0;
$woChecked   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM work_order $where AND status='WAITING CHECKED'"))['total'] ?? 0;
$woFinish    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM work_order $where AND status='FINISHED'"))['total'] ?? 0;
$woReject    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM work_order $where AND status='REJECTED'"))['total'] ?? 0;

// Data untuk Bar Chart - Total WO per Tahun (dengan filter)
$yearData = [];
$query = "
  SELECT YEAR(tgl_input) AS tahun, COUNT(*) AS total
  FROM work_order
  $where
  GROUP BY YEAR(tgl_input)
  ORDER BY tahun ASC
";
$result = mysqli_query($conn, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $yearData[] = $row;
}
$years = array_column($yearData, 'tahun');
$totals = array_column($yearData, 'total');

// Data untuk Bar Chart - Distribusi per Status
$statusData = [
    'WAITING SCHEDULE' => $woWaiting,
    'WAITING APPROVAL' => $woApproval,
    'OPENED' => $woOpened,
    'ON PROGRESS' => $woProgress,
    'WAITING CHECKED' => $woChecked,
    'FINISHED' => $woFinish,
    'REJECTED' => $woReject
];

// Return JSON
header('Content-Type: application/json');
echo json_encode([
    'totalWO' => $totalWO,
    'woWaiting' => $woWaiting,
    'woApproval' => $woApproval,
    'woOpened' => $woOpened,
    'woProgress' => $woProgress,
    'woChecked' => $woChecked,
    'woFinish' => $woFinish,
    'woReject' => $woReject,
    'yearLabels' => $years,
    'yearTotals' => $totals,
    'statusData' => array_values($statusData),
    'statusLabels' => array_keys($statusData)
]);
?>
