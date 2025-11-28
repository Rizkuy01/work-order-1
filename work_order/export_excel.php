<?php
require '../includes/session_check.php';
require '../config/database.php';

// Atur header agar browser mengunduh sebagai file Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=WorkOrder_Export_" . date('Ymd_His') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

// Ambil filter dari URL
$search = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$fromDate = $_GET['from_date'] ?? '';
$toDate = $_GET['to_date'] ?? '';

$where = "WHERE 1=1";

// Filter pencarian
if (!empty($search)) {
  $safeSearch = mysqli_real_escape_string($conn, $search);
  $where .= " AND (nama_mesin LIKE '%$safeSearch%' OR judul_wo LIKE '%$safeSearch%')";
}

// Filter status
if (!empty($statusFilter)) {
  $safeStatus = mysqli_real_escape_string($conn, $statusFilter);
  $where .= " AND status = '$safeStatus'";
}

// Filter tanggal
if (!empty($fromDate) && !empty($toDate)) {
  $where .= " AND DATE(tgl_input) BETWEEN '$fromDate' AND '$toDate'";
} elseif (!empty($fromDate)) {
  $where .= " AND DATE(tgl_input) >= '$fromDate'";
} elseif (!empty($toDate)) {
  $where .= " AND DATE(tgl_input) <= '$toDate'";
}

// Ambil data dari database dengan semua kolom detail
$query = "SELECT * FROM work_order $where ORDER BY tgl_input DESC";
$result = mysqli_query($conn, $query);

// Buat tabel HTML agar bisa dibuka Excel
echo "<table border='1' cellspacing='0' cellpadding='5'>";
echo "<tr style='background:#dc3545; color:#fff; text-align:center; font-weight:bold;'>
        <th>ID Work Order</th>
        <th>Nama Mesin</th>
        <th>Judul WO</th>
        <th>Detail WO</th>
        <th>Creator</th>
        <th>Initiator</th>
        <th>Dept</th>
        <th>Line</th>
        <th>Tipe Perbaikan</th>
        <th>Tanggal Temuan</th>
        <th>Plan Date</th>
        <th>Plan Time</th>
        <th>PIC</th>
        <th>PIC 2</th>
        <th>Person Scheduled</th>
        <th>Person Approved</th>
        <th>Person Accept</th>
        <th>Person Finish</th>
        <th>Reject Note</th>
        <th>Tanggal Input</th>
        <th>Status</th>
      </tr>";

if (mysqli_num_rows($result) > 0) {
  while ($data = mysqli_fetch_assoc($result)) {
    echo "<tr>
            <td>{$data['id_work_order']}</td>
            <td>{$data['nama_mesin']}</td>
            <td>{$data['judul_wo']}</td>
            <td>{$data['detail_wo']}</td>
            <td>{$data['creator']}</td>
            <td>{$data['initiator']}</td>
            <td>{$data['dept']}</td>
            <td>{$data['line']}</td>
            <td>{$data['tipe']}</td>
            <td>" . (!empty($data['tgl_temuan']) ? date('d M Y', strtotime($data['tgl_temuan'])) : '') . "</td>
            <td>" . (!empty($data['tgl_plan']) ? date('d M Y', strtotime($data['tgl_plan'])) : '') . "</td>
            <td>{$data['jam_plan']}</td>
            <td>{$data['pic']}</td>
            <td>{$data['pic2']}</td>
            <td>{$data['person_scheduled']}</td>
            <td>{$data['person_approved']}</td>
            <td>{$data['person_accept']}</td>
            <td>{$data['person_finish']}</td>
            <td>{$data['reject_note']}</td>
            <td>" . date('d M Y H:i', strtotime($data['tgl_input'])) . "</td>
            <td>{$data['status']}</td>
          </tr>";
  }
} else {
  echo "<tr><td colspan='21' align='center'>Tidak ada data untuk diekspor</td></tr>";
}

echo "</table>";
exit;
?>
