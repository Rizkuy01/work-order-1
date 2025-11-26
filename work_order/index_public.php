<?php
// No session check - allow public access
include '../config/database.php';

// ====== FILTER DAN PENCARIAN ======
$search = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$sectionFilter = $_GET['dept'] ?? '';
$lineFilter = $_GET['line'] ?? '';
$mesinFilter = $_GET['mesin'] ?? '';
$tipeFilter = $_GET['tipe'] ?? '';
$fromDate = $_GET['from_date'] ?? '';
$toDate = $_GET['to_date'] ?? '';
$sort = $_GET['sort'] ?? 'tgl_input';
$order = $_GET['order'] ?? 'DESC';

// ====== Pagination ======
$limit = 10;
$page = isset($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// ====== Query dasar ======
$where = "WHERE 1=1";

// 🔍 Pencarian kata kunci
if (!empty($search)) {
  $safeSearch = mysqli_real_escape_string($conn, $search);
  $where .= " AND (nama_mesin LIKE '%$safeSearch%' OR judul_wo LIKE '%$safeSearch%')";
}

// 🔍 Filter status
if (!empty($statusFilter)) {
  $safeStatus = mysqli_real_escape_string($conn, $statusFilter);
  $where .= " AND status = '$safeStatus'";
}

// 🔍 Filter section
if (!empty($sectionFilter)) {
  $safeSection = mysqli_real_escape_string($conn, $sectionFilter);
  $where .= " AND dept = '$safeSection'";
}

// 🔍 Filter line
if (!empty($lineFilter)) {
  $safeLine = mysqli_real_escape_string($conn, $lineFilter);
  $where .= " AND line = '$safeLine'";
}

// 🔍 Filter mesin
if (!empty($mesinFilter)) {
  $safeMesin = mysqli_real_escape_string($conn, $mesinFilter);
  $where .= " AND nama_mesin = '$safeMesin'";
}

// 🔍 Filter tipe
if (!empty($tipeFilter)) {
  $safeTipe = mysqli_real_escape_string($conn, $tipeFilter);
  $where .= " AND tipe = '$safeTipe'";
}

// 🔍 Filter tanggal
if (!empty($fromDate)) {
  $safeFromDate = mysqli_real_escape_string($conn, $fromDate);
  $where .= " AND DATE(tgl_input) >= '$safeFromDate'";
}

if (!empty($toDate)) {
  $safeToDate = mysqli_real_escape_string($conn, $toDate);
  $where .= " AND DATE(tgl_input) <= '$safeToDate'";
}

// ====== Hitung total data ======
$countQuery = "SELECT COUNT(*) AS total FROM work_order $where";
$countResult = mysqli_query($conn, $countQuery);
$totalRows = mysqli_fetch_assoc($countResult)['total'] ?? 0;
$totalPages = ceil($totalRows / $limit);

// ====== Query utama ======
$query = "SELECT * FROM work_order $where ORDER BY $sort $order LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);

// Function untuk safe output
function safe($value) {
  return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar Work Order - Work Order System</title>
  <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/css/bootstrap-icons.css">
  <style>
    body {
      background-color: #f4f6f9;
      font-family: 'Segoe UI', sans-serif;
    }

    /* Navbar */
    .navbar-custom {
      background: linear-gradient(135deg, #ff4b2b, #ff416c);
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      padding: 1rem 2rem;
    }

    .navbar-brand {
      font-weight: 700;
      font-size: 1.5rem;
      color: white !important;
    }

    .nav-link {
      color: white !important;
      margin-left: 1rem;
      transition: 0.3s;
    }

    .nav-link:hover {
      opacity: 0.8;
      transform: translateY(-2px);
    }

    .btn-nav {
      background: white;
      color: #ff416c;
      font-weight: 600;
      border: none;
      padding: 0.5rem 1.5rem;
      border-radius: 6px;
      transition: all 0.3s ease;
    }

    .btn-nav:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .card {
      border-radius: 12px;
      border: none;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    .card-header {
      background: linear-gradient(135deg, #ff4b2b, #ff416c);
      color: white;
      border: none;
      font-weight: 600;
      letter-spacing: 0.3px;
      border-top-left-radius: 12px;
      border-top-right-radius: 12px;
    }

    .table-hover tbody tr:hover {
      background-color: #f8f9ff;
    }

    .badge {
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .badge:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    }

    .btn-gradient-view {
      background: linear-gradient(135deg, #007bff, #0056b3);
      color: white;
      border: none;
      font-weight: 600;
      transition: all 0.3s ease;
    }

    .btn-gradient-view:hover {
      background: linear-gradient(135deg, #0056b3, #003d82);
      transform: translateY(-2px);
    }

    .action-btn {
      width: 36px;
      height: 36px;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 0 !important;
      border-radius: 8px;
    }

    .pagination .page-link {
      border-radius: 6px;
      padding: 6px 12px;
      font-weight: 500;
      color: #ff416c;
    }

    .pagination .page-item.active .page-link {
      background-color: #ff416c;
      border-color: #ff416c;
      color: white;
    }

    .pagination .page-item.disabled .page-link {
      color: #aaa;
      background-color: #f5f5f5;
      pointer-events: none;
    }

    /* Footer */
    .footer-custom {
      background: #2c3e50;
      color: white;
      padding: 2rem;
      text-align: center;
      margin-top: 3rem;
    }

    .footer-custom p {
      margin: 0;
      font-size: 0.9rem;
    }
  </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar-custom">
  <div class="d-flex justify-content-between align-items-center">
    <div class="navbar-brand">
      <i class="bi bi-list-check me-2"></i> Work Order List
    </div>
    <div>
      <a href="dashboard_public.php" class="nav-link" style="color: white; text-decoration: none; margin-right: 1rem;">
        <i class="bi bi-bar-chart me-1"></i> Dashboard
      </a>
      <a href="../auth/login.php" class="btn btn-nav">
        <i class="bi bi-box-arrow-in-right me-1"></i> Login
      </a>
    </div>
  </div>
</nav>

<!-- MAIN CONTENT -->
<div class="container-fluid px-4 mt-4 mb-5">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h4 class="fw-bold" style="color: #2c3e50;">📋 Daftar Work Order</h4>
      <p class="text-muted">Total data: <strong><?= $totalRows ?></strong> work order</p>
    </div>
  </div>

  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0"><i class="bi bi-list-task me-2"></i> Daftar Work Order</h5>
    </div>

    <div class="card-body bg-white">
      <!-- 🔎 Filter Button dan Search -->
      <form method="GET" class="row g-2 mb-3">
        <div class="col-md-6">
          <input type="text" name="search" value="<?= safe($search) ?>" class="form-control" placeholder="Cari judul WO atau nama mesin...">
        </div>

        <div class="col-md-6 d-flex gap-2">
          <button type="button" class="btn btn-danger flex-grow-1" data-bs-toggle="modal" data-bs-target="#filterModal">
            <i class="bi bi-funnel me-2"></i> Pilih Filter
          </button>
          
          <!-- Hidden inputs untuk menyimpan filter -->
          <input type="hidden" name="dept" id="hiddenDept" value="<?= safe($sectionFilter) ?>">
          <input type="hidden" name="line" id="hiddenLine" value="<?= safe($lineFilter) ?>">
          <input type="hidden" name="mesin" id="hiddenMesin" value="<?= safe($mesinFilter) ?>">
          <input type="hidden" name="status" id="hiddenStatus" value="<?= safe($statusFilter) ?>">
          <input type="hidden" name="tipe" id="hiddenTipe" value="<?= safe($tipeFilter) ?>">
          <input type="hidden" name="from_date" id="hiddenFromDate" value="<?= safe($fromDate) ?>">
          <input type="hidden" name="to_date" id="hiddenToDate" value="<?= safe($toDate) ?>">
        </div>
      </form>

      <!-- 🧾 Tabel Data -->
      <div class="table-responsive">
        <table class="table table-hover align-middle text-center">
          <thead class="table-light">
            <tr>
              <th><a href="?sort=nama_mesin&order=<?= $order == 'ASC' ? 'DESC' : 'ASC' ?>" class="text-decoration-none text-dark">Nama Mesin</a></th>
              <th><a href="?sort=judul_wo&order=<?= $order == 'ASC' ? 'DESC' : 'ASC' ?>" class="text-decoration-none text-dark">Judul WO</a></th>
              <th><a href="?sort=tgl_input&order=<?= $order == 'ASC' ? 'DESC' : 'ASC' ?>" class="text-decoration-none text-dark">Tanggal Input</a></th>
              <th>Status</th>
              <th width="120">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (mysqli_num_rows($result) > 0): ?>
              <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                  <td class="fw-semibold"><?= safe($row['nama_mesin']) ?></td>
                  <td><?= safe($row['judul_wo']) ?></td>
                  <td><?= date('d M Y', strtotime($row['tgl_input'])) ?></td>
                  <td>
                    <?php
                      $status = safe($row['status']);
                      $badgeStyle = match($status) {
                        'WAITING SCHEDULE' => 'background: linear-gradient(135deg, #f1c40f, #f39c12); color: white;',
                        'WAITING APPROVAL' => 'background: linear-gradient(135deg, #e39eff, #8e44ad); color: white;',
                        'OPENED'           => 'background: linear-gradient(135deg, #b5c1c2, #636e72); color: white;',
                        'ON PROGRESS'      => 'background: linear-gradient(135deg, #fb963d, #d35400); color: white;',
                        'WAITING CHECKED'  => 'background: linear-gradient(135deg, #59ccfe, #086bff); color: white;',
                        'FINISHED'         => 'background: linear-gradient(135deg, #5ce894, #23d23a); color: white;',
                        'REJECTED'         => 'background: linear-gradient(135deg, #ff7363, #c0392b); color: white;',
                        default            => 'background: #dcdcdc; color: #333;',
                      };
                    ?>
                    <span class="badge px-3 py-2 fw-semibold" style="<?= $badgeStyle ?> box-shadow: 0 2px 4px rgba(0,0,0,0.15); border-radius: 8px;">
                      <?= $status ?>
                    </span>
                  </td>
                  <td>
                      <div class="d-flex justify-content-center gap-2">
                          <a href="actions/detail.php?id=<?= $row['id_work_order'] ?>"
                            class="btn btn-sm btn-outline-primary action-btn" title="Detail">
                              <i class="bi bi-eye"></i>
                          </a>
                      </div>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr><td colspan="5" class="text-muted py-3">Tidak ada data ditemukan untuk filter yang dipilih.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- 📄 Pagination -->
      <?php if ($totalPages > 1): ?>
      <nav class="d-flex justify-content-center mt-4">
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
            <a class="page-link"
              href="?page=<?= max(1, $page - 1) ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>&dept=<?= urlencode($sectionFilter) ?>&line=<?= urlencode($lineFilter) ?>&mesin=<?= urlencode($mesinFilter) ?>&sort=<?= $sort ?>&order=<?= $order ?>">
              &laquo; Prev
            </a>
          </li>

          <?php
            $startPage = max(1, $page - 2);
            $endPage = min($totalPages, $page + 2);
            for ($i = $startPage; $i <= $endPage; $i++):
          ?>
            <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
              <a class="page-link"
                href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>&dept=<?= urlencode($sectionFilter) ?>&line=<?= urlencode($lineFilter) ?>&mesin=<?= urlencode($mesinFilter) ?>&sort=<?= $sort ?>&order=<?= $order ?>">
                <?= $i ?>
              </a>
            </li>
          <?php endfor; ?>

          <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
            <a class="page-link"
              href="?page=<?= min($totalPages, $page + 1) ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>&dept=<?= urlencode($sectionFilter) ?>&line=<?= urlencode($lineFilter) ?>&mesin=<?= urlencode($mesinFilter) ?>&sort=<?= $sort ?>&order=<?= $order ?>">
              Next &raquo;
            </a>
          </li>
        </ul>
      </nav>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- MODAL FILTER -->
<div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="filterModalLabel">
          <i class="bi bi-funnel me-2"></i> Pilih Filter
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <form id="filterForm">
          <div class="row g-3">
            <!-- Filter Departement -->
            <div class="col-md-6">
              <label for="modalDept" class="form-label fw-semibold">Departement</label>
              <select id="modalDept" class="form-select">
                <option value="">Semua Departement</option>
                <?php 
                $sections_list = ['PROD1', 'PROD2', 'PROD3', 'PROD4', 'PROD5', 'QA Lab'];
                foreach ($sections_list as $section_name):
                ?>
                  <option value="<?= $section_name ?>" <?= $sectionFilter == $section_name ? 'selected' : '' ?>><?= $section_name ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Filter Line -->
            <div class="col-md-6">
              <label for="modalLine" class="form-label fw-semibold">Line</label>
              <select id="modalLine" class="form-select">
                <option value="">Semua Line</option>
              </select>
            </div>

            <!-- Filter Mesin -->
            <div class="col-md-6">
              <label for="modalMesin" class="form-label fw-semibold">Mesin</label>
              <select id="modalMesin" class="form-select">
                <option value="">Semua Mesin</option>
              </select>
            </div>

            <!-- Filter Status -->
            <div class="col-md-6">
              <label for="modalStatus" class="form-label fw-semibold">Status</label>
              <select id="modalStatus" class="form-select">
                <option value="">Semua Status</option>
                <option value="WAITING SCHEDULE" <?= $statusFilter == 'WAITING SCHEDULE' ? 'selected' : '' ?>>Waiting Schedule</option>
                <option value="WAITING APPROVAL" <?= $statusFilter == 'WAITING APPROVAL' ? 'selected' : '' ?>>Waiting Approval</option>
                <option value="OPENED" <?= $statusFilter == 'OPENED' ? 'selected' : '' ?>>Opened</option>
                <option value="ON PROGRESS" <?= $statusFilter == 'ON PROGRESS' ? 'selected' : '' ?>>On Progress</option>
                <option value="WAITING CHECKED" <?= $statusFilter == 'WAITING CHECKED' ? 'selected' : '' ?>>Waiting Checked</option>
                <option value="FINISHED" <?= $statusFilter == 'FINISHED' ? 'selected' : '' ?>>Finished</option>
                <option value="REJECTED" <?= $statusFilter == 'REJECTED' ? 'selected' : '' ?>>Rejected</option>
              </select>
            </div>

            <!-- Filter Tipe -->
            <div class="col-md-6">
              <label for="modalTipe" class="form-label fw-semibold">Tipe Perbaikan</label>
              <select id="modalTipe" class="form-select">
                <option value="">Semua Tipe</option>
                <option value="Repair" <?= $tipeFilter == 'Repair' ? 'selected' : '' ?>>Repair</option>
                <option value="Improve" <?= $tipeFilter == 'Improve' ? 'selected' : '' ?>>Improve</option>
                <option value="Predictive" <?= $tipeFilter == 'Predictive' ? 'selected' : '' ?>>Predictive</option>
                <option value="Preventive" <?= $tipeFilter == 'Preventive' ? 'selected' : '' ?>>Preventive</option>
                <option value="DCM" <?= $tipeFilter == 'DCM' ? 'selected' : '' ?>>DCM</option>
                <option value="Blue Tag" <?= $tipeFilter == 'Blue Tag' ? 'selected' : '' ?>>Blue Tag</option>
                <option value="Red Tag" <?= $tipeFilter == 'Red Tag' ? 'selected' : '' ?>>Red Tag</option>
              </select>
            </div>

            <!-- Filter Tanggal Mulai -->
            <div class="col-md-6">
              <label for="modalFromDate" class="form-label fw-semibold">Dari Tanggal</label>
              <input type="date" id="modalFromDate" class="form-control" value="<?= safe($fromDate) ?>">
            </div>

            <!-- Filter Tanggal Akhir -->
            <div class="col-md-6">
              <label for="modalToDate" class="form-label fw-semibold">Sampai Tanggal</label>
              <input type="date" id="modalToDate" class="form-control" value="<?= safe($toDate) ?>">
            </div>
          </div>
        </form>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="bi bi-x me-1"></i> Tutup
        </button>
        <button type="button" class="btn btn-danger" id="applyFilter">
          <i class="bi bi-check me-1"></i> Terapkan Filter
        </button>
        <button type="button" class="btn btn-outline-secondary" id="resetFilter">
          <i class="bi bi-arrow-clockwise me-1"></i> Reset
        </button>
      </div>
    </div>
  </div>
</div>

<!-- FOOTER -->
<div class="footer-custom">
  <p>&copy; 2025 Work Order System - KYB Indonesia. All rights reserved.</p>
</div>

<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function () {
    // Load line saat department dipilih di modal
    $("#modalDept").change(function () {
        var section = $(this).val();
        
        if (section == '') {
            $("#modalLine").html('<option value="">Semua Line</option>');
            $("#modalMesin").html('<option value="">Semua Mesin</option>');
            return;
        }

        $.post("filter_line.php", { section: section }, function (data) {
            $("#modalLine").html(data);
            $("#modalMesin").html('<option value="">Semua Mesin</option>');
        });
    });

    // Load mesin saat line dipilih di modal
    $("#modalLine").change(function () {
        var line = $(this).val();
        
        if (line == '') {
            $("#modalMesin").html('<option value="">Semua Mesin</option>');
            return;
        }

        $.post("filter_mesin.php", { line: line }, function (data) {
            $("#modalMesin").html(data);
        });
    });

    // Load line & mesin saat modal dibuka
    $("#filterModal").on("show.bs.modal", function () {
        var section = $("#modalDept").val();
        if (section != '') {
            $.post("filter_line.php", { section: section }, function (data) {
                $("#modalLine").html(data);
                
                var line = '<?= safe($lineFilter) ?>';
                if (line != '') {
                    $("#modalLine").val(line);
                    
                    $.post("filter_mesin.php", { line: line }, function (data2) {
                        $("#modalMesin").html(data2);
                        var mesin = '<?= safe($mesinFilter) ?>';
                        if (mesin != '') {
                            $("#modalMesin").val(mesin);
                        }
                    });
                }
            });
        }
    });

    // Apply Filter Button
    $("#applyFilter").click(function () {
        var dept = $("#modalDept").val();
        var line = $("#modalLine").val();
        var mesin = $("#modalMesin").val();
        var status = $("#modalStatus").val();
        var tipe = $("#modalTipe").val();
        var fromDate = $("#modalFromDate").val();
        var toDate = $("#modalToDate").val();
        
        console.log('Applying filters:', {dept, line, mesin, status, tipe, fromDate, toDate});
        
        $("#hiddenDept").val(dept);
        $("#hiddenLine").val(line);
        $("#hiddenMesin").val(mesin);
        $("#hiddenStatus").val(status);
        $("#hiddenTipe").val(tipe);
        $("#hiddenFromDate").val(fromDate);
        $("#hiddenToDate").val(toDate);
        
        console.log('Hidden values after set:', {
            dept: $("#hiddenDept").val(),
            line: $("#hiddenLine").val(),
            mesin: $("#hiddenMesin").val(),
            status: $("#hiddenStatus").val(),
            tipe: $("#hiddenTipe").val(),
            fromDate: $("#hiddenFromDate").val(),
            toDate: $("#hiddenToDate").val()
        });
        
        // Tutup modal
        var modal = bootstrap.Modal.getInstance(document.getElementById('filterModal'));
        modal.hide();
        
        // Submit form dengan delay kecil
        setTimeout(function() {
            console.log('Submitting form');
            document.querySelector('form[method="GET"]').submit();
        }, 300);
    });

    // Reset Filter Button
    $("#resetFilter").click(function () {
        $("#modalDept").val('');
        $("#modalLine").val('');
        $("#modalMesin").val('');
        $("#modalStatus").val('');
        $("#modalTipe").val('');
        $("#modalFromDate").val('');
        $("#modalToDate").val('');
        
        $("#hiddenDept").val('');
        $("#hiddenLine").val('');
        $("#hiddenMesin").val('');
        $("#hiddenStatus").val('');
        $("#hiddenTipe").val('');
        $("#hiddenFromDate").val('');
        $("#hiddenToDate").val('');
        
        // Tutup modal
        var modal = bootstrap.Modal.getInstance(document.getElementById('filterModal'));
        modal.hide();
        
        // Redirect ke halaman tanpa filter
        setTimeout(function() {
            window.location.href = '?search=';
        }, 300);
    });
});
</script>

</body>
</html>
