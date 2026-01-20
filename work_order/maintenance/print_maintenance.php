<?php
include '../../includes/session_check.php';
include '../../includes/role_check.php';
only(['Maintenance', 'Super Administrator']);
include '../../config/database.php';
include '../../config/status_helper.php';

// Helper aman
function safe($value) {
  return htmlspecialchars($value ?? '-', ENT_QUOTES, 'UTF-8');
}

// --- Filter ---
$search = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? '';

// 🔐 Filter by Role
$role = $_SESSION['role'] ?? '';
$nama_user = $_SESSION['nama'] ?? '';

$where = "WHERE (wo.status IN ('OPENED', 'OPEN', 'ON PROGRESS'))";

// ✅ Jika Maintenance, filter hanya WO yang ditugaskan
if ($role === 'Maintenance') {
  $nama_user_escaped = mysqli_real_escape_string($conn, $nama_user);
  $where .= " AND (wo.pic = '$nama_user_escaped' OR wo.pic2 = '$nama_user_escaped' OR wo.pic3 = '$nama_user_escaped')";
}

if ($search !== '') {
  $search_escaped = mysqli_real_escape_string($conn, $search);
  $where .= " AND (wo.judul_wo LIKE '%$search_escaped%' OR wo.nama_mesin LIKE '%$search_escaped%')";
}

if ($statusFilter !== '') {
  $statusFilter_escaped = mysqli_real_escape_string($conn, normalizeStatus($statusFilter));
  if ($statusFilter_escaped === 'OPENED') {
    $where .= " AND (wo.status = 'OPENED' OR wo.status = 'OPEN')";
  } else {
    $where .= " AND wo.status = '$statusFilter_escaped'";
  }
}

// --- Query untuk semua WO dengan sorting khusus ---
// Prioritas: Hari ini → Overdue → Rejected → Lainnya
$today = date('Y-m-d');
$query = "
  SELECT wo.*, ws.plan_date, ws.plan_time, ws.pic,
  CASE 
    WHEN ws.plan_date = '$today' THEN 0
    WHEN ws.plan_date IS NOT NULL AND DATE(ws.plan_date) < '$today' AND wo.status IN ('OPENED', 'OPEN', 'ON PROGRESS') THEN 1
    WHEN wo.status = 'REJECTED' THEN 2
    ELSE 3
  END AS sort_priority
  FROM work_order wo
  LEFT JOIN wo_schedule ws ON wo.id_work_order = ws.id_work_order
  $where
  ORDER BY sort_priority ASC, ws.plan_date ASC, ws.plan_time ASC
";
$result = mysqli_query($conn, $query);

// Group by priority dan date
$woByPriority = [];
while ($row = mysqli_fetch_assoc($result)) {
  $priority = $row['sort_priority'];
  $date = $row['plan_date'] ?? 'Tidak Dijadwalkan';
  
  if (!isset($woByPriority[$priority])) {
    $woByPriority[$priority] = [];
  }
  if (!isset($woByPriority[$priority][$date])) {
    $woByPriority[$priority][$date] = [];
  }
  $woByPriority[$priority][$date][] = $row;
}

// Format tanggal Indonesia
function formatTanggalIndo($date) {
  if ($date === 'Tidak Dijadwalkan') return $date;
  $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
  $timestamp = strtotime($date);
  if ($timestamp === false) return $date;
  return date('d', $timestamp) . ' ' . $months[intval(date('m', $timestamp)) - 1] . ' ' . date('Y', $timestamp);
}

$today = date('Y-m-d');
$currentUser = $_SESSION['nama'] ?? 'User';

// Set timezone to WIB (UTC+7)
date_default_timezone_set('Asia/Jakarta');
$printDate = date('d M Y H:i \W\I\B');
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Laporan Jadwal Work Order</title>
  <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="../../assets/css/bootstrap-icons.css">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      color: #333;
      background: #f5f5f5;
    }

    @media print {
      body {
        background: white;
        margin: 0;
        padding: 5px;
      }
      .no-print {
        display: none !important;
      }
      .page-break {
        page-break-after: always;
      }
      @page {
        size: A4 landscape;
        margin: 8mm;
      }
    }

    .print-container {
      background: white;
      padding: 15px;
      margin: 0 auto;
    }

    /* Header */
    .print-header {
      background: linear-gradient(135deg, #1a1a1a, #2d2d2d);
      color: white;
      padding: 15px 20px;
      text-align: center;
      border-bottom: 3px solid #ff4b2b;
      margin-bottom: 15px;
      border-radius: 6px;
    }

    .print-header h1 {
      font-size: 22px;
      margin-bottom: 3px;
      font-weight: bold;
    }

    .print-header p {
      font-size: 11px;
      opacity: 0.9;
      margin: 1px 0;
    }

    .print-info {
      display: flex;
      justify-content: space-between;
      gap: 30px;
      margin-top: 8px;
      font-size: 11px;
      padding-top: 8px;
      border-top: 1px solid rgba(255, 255, 255, 0.3);
    }

    .print-info div {
      flex: 1;
    }

    .print-info strong {
      color: #ff4b2b;
    }

    /* Table */
    .table-print {
      width: 100%;
      border-collapse: collapse;
      font-size: 11px;
      margin-bottom: 15px;
    }

    .table-print thead {
      background: linear-gradient(90deg, #ff4b2b, #ff416c);
      color: white;
    }

    .table-print thead th {
      padding: 8px;
      text-align: left;
      font-weight: bold;
      border: 1px solid #ddd;
    }

    .table-print tbody td {
      padding: 8px;
      border: 1px solid #ddd;
      vertical-align: middle;
    }

    .table-print tbody tr:nth-child(odd) {
      background: #f9f9f9;
    }

    .table-print tbody tr:hover {
      background: #f0f0f0;
    }

    .status-badge {
      display: inline-block;
      padding: 3px 8px;
      border-radius: 3px;
      font-size: 10px;
      font-weight: bold;
      color: white;
      text-align: center;
      min-width: 60px;
    }

    .status-opened {
      background: #636e72;
    }

    .status-progress {
      background: #d35400;
    }

    .badge-overdue {
      background: #dc3545 !important;
      padding: 2px 6px;
      font-size: 9px;
      margin-left: 3px;
    }

    .date-row {
      background: #fff3cd;
      font-weight: bold;
    }

    .date-row td {
      padding: 10px 8px !important;
    }

    /* No print button */
    .btn-print {
      position: fixed;
      top: 20px;
      right: 20px;
      z-index: 1000;
    }

    @media print {
      .btn-print {
        display: none;
      }
    }

    /* Footer */
    .print-footer {
      border-top: 2px solid #ddd;
      padding: 10px;
      text-align: center;
      color: #666;
      font-size: 10px;
      margin-top: 15px;
    }

    .empty-state {
      text-align: center;
      padding: 30px;
      color: #999;
    }

    /* Column widths */
    .col-no { width: 4%; }
    .col-date { width: 8%; }
    .col-time { width: 6%; }
    .col-title { width: 18%; }
    .col-line { width: 10%; }
    .col-mesin { width: 15%; }
    .col-tipe { width: 10%; }
    .col-pic { width: 12%; }
    .col-status { width: 10%; }
  </style>
</head>
<body>
  <button class="btn btn-primary no-print btn-print" onclick="window.print()">
    <i class="bi bi-printer"></i> Print / Simpan PDF
  </button>

  <div class="print-container">
    <!-- Header -->
    <div class="print-header">
      <h1>📋 LAPORAN JADWAL WORK ORDER</h1>
      <div class="print-info">
        <div>User: <strong><?= safe($currentUser) ?></strong></div>
        <div>Tanggal Cetak: <strong><?= $printDate ?></strong></div>
        <div>Total WO: <strong><?php $totalWO = 0; foreach($woByPriority as $p => $d) { foreach($d as $dates => $items) { $totalWO += count($items); }} echo $totalWO; ?> Item</strong></div>
      </div>
    </div>

    <!-- Content -->
    <?php if (count($woByPriority) > 0): ?>
      <table class="table-print">
        <thead>
          <tr>
            <th class="col-no">No</th>
            <th class="col-date">Tanggal</th>
            <th class="col-time">Jam</th>
            <th class="col-title">Judul WO</th>
            <th class="col-line">Line</th>
            <th class="col-mesin">Mesin</th>
            <th class="col-tipe">Tipe Perbaikan</th>
            <th class="col-pic">PIC</th>
            <th class="col-status">Status</th>
          </tr>
        </thead>
        <tbody>
          <?php 
            $counter = 1;
            $sectionHeaders = [
              0 => ['icon' => '📅', 'title' => 'Jadwal Hari Ini', 'color' => '#17a2b8'],
              1 => ['icon' => '⏰', 'title' => 'Jadwal Terlewat (OVERDUE)', 'color' => '#dc3545'],
              2 => ['icon' => '❌', 'title' => 'Ditolak (REJECTED)', 'color' => '#ff6b6b'],
              3 => ['icon' => '📋', 'title' => 'Jadwal Lainnya', 'color' => '#6c757d']
            ];
            
            // Loop by priority
            foreach ($woByPriority as $priority => $dateGroups): 
              $header = $sectionHeaders[$priority] ?? $sectionHeaders[3];
          ?>
            <!-- Priority Section Header -->
            <tr style="background: linear-gradient(90deg, <?= $header['color'] ?>, rgba(0,0,0,0.1)); color: white; font-weight: bold; border-left: 4px solid <?= $header['color'] ?>;">
              <td colspan="9" style="padding: 12px; color: white;">
                <?= $header['icon'] ?> <?= $header['title'] ?>
              </td>
            </tr>

            <?php 
              // Loop by date within priority
              foreach ($dateGroups as $date => $woList): 
            ?>
              <!-- Date separator within priority -->
              <tr class="date-row">
                <td colspan="9" style="background: linear-gradient(90deg, #fff3cd, #ffe082); border-left: 4px solid #ffc107;">
                  📅 <?= formatTanggalIndo($date) ?> — <?= count($woList) ?> Pekerjaan
                </td>
              </tr>

              <?php foreach ($woList as $wo): ?>
                <?php
                  $status = $wo['status'];
                  $statusClass = match($status) {
                    'OPENED', 'OPEN' => 'status-opened',
                    'ON PROGRESS' => 'status-progress',
                    default => 'status-opened',
                  };
                ?>
                <tr>
                  <td class="col-no"><?= $counter ?></td>
                  <td class="col-date"><?= safe($wo['plan_date'] ?? '-') ?></td>
                  <td class="col-time" style="font-weight: bold; color: #ff4b2b;"><?= safe($wo['plan_time'] ?? '-') ?></td>
                  <td class="col-title"><strong><?= safe($wo['judul_wo']) ?></strong></td>
                  <td class="col-line"><?= safe($wo['line'] ?? '-') ?></td>
                  <td class="col-mesin"><?= safe($wo['nama_mesin'] ?? '-') ?></td>
                  <td class="col-tipe"><?= safe($wo['tipe'] ?? '-') ?></td>
                  <td class="col-pic"><?= safe($wo['pic'] ?? '-') ?></td>
                  <td class="col-status">
                    <span class="status-badge <?= $statusClass ?>">
                      <?= strtoupper(safe($wo['status'])) ?>
                    </span>
                  </td>
                </tr>
                <?php $counter++; ?>
              <?php endforeach; ?>

            <?php endforeach; ?>
          <?php endforeach; ?>
        </tbody>
      </table>

    <?php else: ?>
      <div class="empty-state">
        <i class="bi bi-inbox" style="font-size: 48px;"></i>
        <h3>Tidak ada data ditemukan</h3>
        <p>Belum ada jadwal perbaikan yang sesuai dengan filter.</p>
      </div>
    <?php endif; ?>

    <!-- Footer -->
    <div class="print-footer">
      <p>Dokumen ini dicetak dari Sistem Work Order Management | Informasi ini bersifat rahasia dan hanya untuk pihak yang berwenang</p>
      <p style="margin-top: 3px; font-size: 9px;">© PT. Kayaba Indonesia - Maintenance Departement</p>
    </div>
  </div>
</body>
</html>
