<?php
include '../../includes/session_check.php';
include '../../includes/role_check.php';
only(['Maintenance', 'Super Administrator']);
include '../../config/database.php';
include '../../config/status_helper.php';
include '../../includes/layout.php';

// Helper aman
function safe($value) {
  return htmlspecialchars($value ?? '-', ENT_QUOTES, 'UTF-8');
}

// --- Pagination setup ---
$limit = 10;
$page = $_GET['page'] ?? 1;
$offset = ($page - 1) * $limit;

// --- Filter dan Search ---
$search = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? '';

// 🔐 Filter by Role - Maintenance hanya melihat WO yang ditugaskan padanya
$role = $_SESSION['role'] ?? '';
$nama_user = $_SESSION['nama'] ?? '';

$where = "WHERE (wo.status IN ('OPENED', 'OPEN', 'ON PROGRESS'))";

// ✅ Jika Maintenance, filter hanya WO yang ditugaskan (PIC1, PIC2, atau PIC3)
if ($role === 'Maintenance') {
  $nama_user_escaped = mysqli_real_escape_string($conn, $nama_user);
  $where .= " AND (wo.pic = '$nama_user_escaped' OR wo.pic2 = '$nama_user_escaped' OR wo.pic3 = '$nama_user_escaped')";
}

if ($search !== '') {
  $search = mysqli_real_escape_string($conn, $search);
  $where .= " AND (wo.judul_wo LIKE '%$search%' OR wo.nama_mesin LIKE '%$search%')";
}
if ($statusFilter !== '') {
  $statusFilter = mysqli_real_escape_string($conn, normalizeStatus($statusFilter));
  if ($statusFilter === 'OPENED') {
    $where .= " AND (wo.status = 'OPENED' OR wo.status = 'OPEN')";
  } else {
    $where .= " AND wo.status = '$statusFilter'";
  }
}

// --- Hitung total data ---
$totalData = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM work_order wo $where"))['total'];
$totalPages = ceil($totalData / $limit);

// --- Query untuk WO yang dijadwalkan hari ini ---
$today = date('Y-m-d');
$todayWOQuery = "
  SELECT wo.*, ws.plan_date, ws.plan_time, ws.pic
  FROM work_order wo
  LEFT JOIN wo_schedule ws ON wo.id_work_order = ws.id_work_order
  WHERE ws.plan_date = '$today'
  AND (wo.status IN ('OPENED', 'OPEN', 'ON PROGRESS'))
";

// ✅ Jika Maintenance, filter hanya WO yang ditugaskan
if ($role === 'Maintenance') {
  $nama_user_escaped = mysqli_real_escape_string($conn, $nama_user);
  $todayWOQuery .= " AND (wo.pic = '$nama_user_escaped' OR wo.pic2 = '$nama_user_escaped' OR wo.pic3 = '$nama_user_escaped')";
}

$todayWOQuery .= " ORDER BY ws.plan_time ASC";
$todayWOResult = mysqli_query($conn, $todayWOQuery);

// --- Query utama ---
$query = "
  SELECT wo.*, ws.plan_date, ws.plan_time, ws.pic
  FROM work_order wo
  LEFT JOIN wo_schedule ws ON wo.id_work_order = ws.id_work_order
  $where
  ORDER BY ws.plan_date ASC
  LIMIT $limit OFFSET $offset
";
$result = mysqli_query($conn, $query);
?>

<div class="container-fluid px-4 py-3">

  <!-- � ALERT SECTION - WO Dijadwalkan Hari Ini -->
  <?php if (mysqli_num_rows($todayWOResult) > 0): ?>
    <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" 
         style="background: linear-gradient(135deg, #fff3cd, #ffe082); border-left: 5px solid #ffc107;">
      <div class="d-flex align-items-center mb-3">
        <i class="bi bi-exclamation-triangle-fill me-3" style="font-size: 1.5rem; color: #ff9800;"></i>
        <h5 class="mb-0" style="color: #333;">Jadwal Perbaikan Hari Ini</h5>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      
      <div class="row g-3">
        <?php while ($todayWO = mysqli_fetch_assoc($todayWOResult)): ?>
          <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm" style="border-left: 4px solid #ff9800; border-radius: 8px;">
              <div class="card-body p-3">
                <h6 class="card-title fw-bold text-dark mb-2" style="font-size: 1rem; min-height: 2.5rem;">
                  <?= safe($todayWO['judul_wo']) ?>
                </h6>
                
                <div class="mb-2">
                  <small class="text-muted d-block">
                    <i class="bi bi-diagram-2 me-2" style="color: #ff9800;"></i><strong>Line:</strong>
                  </small>
                  <p class="mb-2 ms-3" style="color: #333;">
                    <?= safe($todayWO['line'] ?? '-') ?>
                  </p>
                </div>

                <div class="mb-2">
                  <small class="text-muted d-block">
                    <i class="bi bi-gear me-2" style="color: #ff9800;"></i><strong>Mesin:</strong>
                  </small>
                  <p class="mb-2 ms-3" style="color: #333;">
                    <?= safe($todayWO['nama_mesin'] ?? '-') ?>
                  </p>
                </div>

                <div class="mb-2">
                  <small class="text-muted d-block">
                    <i class="bi bi-tools me-2" style="color: #ff9800;"></i><strong>Tipe:</strong>
                  </small>
                  <p class="mb-2 ms-3" style="color: #333;">
                    <?= safe($todayWO['jenis_perbaikan'] ?? '-') ?>
                  </p>
                </div>

                <div class="d-flex align-items-center border-top pt-2">
                  <i class="bi bi-clock me-2" style="color: #ff9800; font-size: 1.1rem;"></i>
                  <strong style="color: #ff9800; font-size: 1.1rem;">
                    <?= safe($todayWO['plan_time'] ?? '-') ?>
                  </strong>
                </div>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    </div>
  <?php endif; ?>

  <!-- �🔴 HEADER + FILTER + TABLE DALAM SATU CARD -->
  <div class="card shadow border-0">
    <div class="card-header text-white fw-semibold d-flex align-items-center"
         style="background: linear-gradient(90deg, #ff4b2b, #ff416c); font-size: 1.1rem;">
      <i class="bi bi-list-check me-2"></i> Daftar My Work Order
    </div>

    <div class="card-body bg-white">
      <!-- 🔎 Filter -->
      <form method="GET" class="row g-2 mb-3">
        <div class="col-md-4">
          <input type="text" name="search" class="form-control" placeholder="Cari judul atau mesin..." 
                 value="<?= safe($_GET['search'] ?? '') ?>">
        </div>
        <div class="col-md-3">
          <select name="status" class="form-select">
            <option value="">Semua Status</option>
            <option value="OPENED" <?= ($statusFilter == 'OPENED') ? 'selected' : '' ?>>Opened</option>
            <option value="ON PROGRESS" <?= ($statusFilter == 'ON PROGRESS') ? 'selected' : '' ?>>On Progress</option>
            <option value="WAITING CHECKED" <?= ($statusFilter == 'WAITING CHECKED') ? 'selected' : '' ?>>Waiting Checked</option>
            <option value="FINISHED" <?= ($statusFilter == 'FINISHED') ? 'selected' : '' ?>>Finished</option>
          </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
          <button class="btn btn-danger-gradient fw-semibold text-white flex-grow-1" type="submit">
            <i class="bi bi-funnel-fill me-1"></i> Filter
          </button>
          <button class="btn btn-info fw-semibold text-white" type="button" onclick="printDailySchedule()">
            <i class="bi bi-printer me-1"></i> Print
          </button>
        </div>
      </form>

      <!-- 🧾 Tabel Data -->
      <div class="table-responsive">
        <table class="table table-hover align-middle text-center mb-0">
          <thead class="table-light">
            <tr>
              <th>Judul</th>
              <th>Mesin</th>
              <th>Tanggal</th>
              <th>Jam</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (mysqli_num_rows($result) > 0): ?>
              <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                <?php
                  $status = $row['status'];
                  $hasReject = !empty($row['reject_note']); // Cek apakah ada reject
                  
                  // Hitung deadline H+3 jika ada reject
                  $displayDate = $row['plan_date'];
                  $displayTime = $row['plan_time'];
                  if ($hasReject && !empty($row['reject_date'])) {
                    $rejectDateTime = new DateTime($row['reject_date']);
                    $rejectDateTime->add(new DateInterval('P3D')); // Tambah 3 hari
                    $displayDate = $rejectDateTime->format('Y-m-d');
                    $displayTime = '24:00'; // Atau bisa gunakan jam dari reject_date
                  }
                  
                  // 🚨 Cek apakah pekerjaan sudah terlewat deadline (overdue)
                  $isOverdue = false;
                  $today = new DateTime(date('Y-m-d'));
                  if (!empty($displayDate) && in_array($status, ['OPENED', 'OPEN', 'ON PROGRESS'])) {
                    $planDateTime = new DateTime($displayDate);
                    if ($planDateTime < $today) {
                      $isOverdue = true;
                    }
                  }
                  
                  $badgeStyle = match($status) {
                    'WAITING SCHEDULE' => 'background: linear-gradient(135deg, #f1c40f, #f39c12); color:white;',
                    'WAITING APPROVAL' => 'background: linear-gradient(135deg, #e39eff, #8e44ad); color:white;',
                    'OPENED'           => 'background: linear-gradient(135deg, #b5c1c2, #636e72); color:white;',
                    'ON PROGRESS'      => 'background: linear-gradient(135deg, #fb963d, #d35400); color:white;',
                    'WAITING CHECKED'  => 'background: linear-gradient(135deg, #59ccfe, #086bff); color:white;',
                    'FINISHED'         => 'background: linear-gradient(135deg, #5ce894, #23d23a); color:white;',
                    'REJECTED'         => 'background: linear-gradient(135deg, #ff7363, #c0392b); color:white;',
                    default            => 'background: #dcdcdc; color:#333;',
                  };
                ?>
                <tr>
                  <td class="fw-semibold text-start"><?= safe($row['judul_wo']) ?></td>
                  <td><?= safe($row['nama_mesin']) ?></td>
                  <td><?= safe($displayDate ?: '-') ?></td>
                  <td><?= safe($displayTime ?: '-') ?></td>
                  <td>
                    <div class="position-relative d-inline-block">
                      <span class="badge px-3 py-2 <?php echo $isOverdue ? 'badge-overdue' : ''; ?>" 
                            style="<?= $badgeStyle ?> border-radius:8px; box-shadow:0 2px 4px rgba(0,0,0,0.15);">
                        <?= strtoupper(safe($row['status'])) ?>
                        <?php if ($hasReject): ?>
                          <br><small style="font-size:0.7rem;">(⚠️ REJECTED)</small>
                        <?php endif; ?>
                        <?php if ($isOverdue): ?>
                          <br><small style="font-size:0.7rem; font-weight: bold;">⏰ OVERDUE</small>
                        <?php endif; ?>
                      </span>
                      <?php if ($isOverdue): ?>
                        <span class="position-absolute top-0 start-100 translate-middle p-2 bg-danger border border-light rounded-circle" 
                              style="animation: pulse-overdue 2s infinite;" title="Jadwal sudah terlewat">
                          <span class="visually-hidden">Overdue</span>
                        </span>
                      <?php endif; ?>
                    </div>
                  </td>
                  <td>
                    <button type="button" class="btn btn-danger btn-sm text-white fw-semibold shadow-sm"
                            onclick="showDetail(<?= $row['id_work_order'] ?>)">
                      Detail
                    </button>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr><td colspan="6" class="text-muted py-3">Tidak ada data ditemukan.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- 📄 Pagination -->
      <nav class="mt-3">
        <ul class="pagination justify-content-center">
          <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>">← Prev</a>
          </li>

          <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
              <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>"><?= $i ?></a>
            </li>
          <?php endfor; ?>

          <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>">Next →</a>
          </li>
        </ul>
      </nav>
    </div>
  </div>
</div>

<!-- MODAL DETAIL -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-body" id="detailContent">
        <div class="text-center text-muted py-4">Memuat data...</div>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {
  window.showDetail = function(id) {
    const modal = new bootstrap.Modal(document.getElementById('detailModal'));
    modal.show();
    $('#detailContent').html('<div class="text-center text-muted py-4">🔄 Sedang memuat...</div>');
    $.ajax({
      url: 'maintenance_detail.php',
      method: 'GET',
      data: { id: id },
      success: function(response) {
        $('#detailContent').html(response);
      },
      error: function() {
        $('#detailContent').html('<div class="text-danger text-center py-4">⚠️ Gagal memuat data.</div>');
      }
    });
  };

  window.printDailySchedule = function() {
    // Get current filter values
    const search = new URLSearchParams(window.location.search).get('search') || '';
    const status = new URLSearchParams(window.location.search).get('status') || '';
    
    // Open print window
    const printWindow = window.open('print_maintenance.php?search=' + encodeURIComponent(search) + '&status=' + encodeURIComponent(status), 'PrintWindow', 'width=1000,height=800');
    
    // Print after window loads
    if (printWindow) {
      printWindow.addEventListener('load', function() {
        setTimeout(() => {
          printWindow.print();
        }, 500);
      });
    }
  };
});
</script>

<style>
  .card { border-radius: 12px; }
  table thead th { vertical-align: middle; font-weight: 600; }
  table tbody tr:hover { background-color: #fff7f7; }

  .btn-sm { border-radius: 6px; padding: 5px 10px; cursor:pointer; }
  .btn-danger-gradient {
    background: linear-gradient(90deg, #ff4b2b, #ff416c);
    border: none;
  }
  .btn-danger-gradient:hover {
    background: linear-gradient(90deg, #ff416c, #c0392b);
  }
  .pagination .page-link {
    border-radius: 6px;
    margin: 0 2px;
    color: #c0392b;
    border-color: #f5c6cb;
  }
  .pagination .page-item.active .page-link {
    background: linear-gradient(135deg, #ff4b2b, #ff416c);
    color: white;
    border: none;
  }
  .pagination .page-item.disabled .page-link {
    color: #aaa;
    background-color: #f5f5f5;
  }

  /* Styling untuk Alert Card */
  .alert.alert-warning {
    box-shadow: 0 2px 8px rgba(255, 152, 0, 0.15);
  }

  .alert .card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    background: #fff;
  }

  .alert .card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(255, 152, 0, 0.2) !important;
  }

  .alert .card-body small {
    font-size: 0.8rem;
    letter-spacing: 0.5px;
  }

  .alert .card-body p {
    font-size: 0.95rem;
    margin-bottom: 0;
    font-weight: 500;
  }

  .alert .card-body .border-top {
    border-color: #ffe0b2 !important;
  }

  /* 🚨 Overdue Badge Animation */
  @keyframes pulse-overdue {
    0%, 100% {
      opacity: 1;
      box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
    }
    50% {
      box-shadow: 0 0 0 8px rgba(220, 53, 69, 0);
    }
  }

  .badge-overdue {
    border: 2px solid #dc3545 !important;
    box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.2) !important;
  }

  .badge-overdue small:last-child {
    display: block;
    margin-top: 3px;
    color: #fff;
    font-weight: bold;
    animation: blink 1.5s infinite;
  }

  @keyframes blink {
    0%, 50%, 100% { opacity: 1; }
    25%, 75% { opacity: 0.5; }
  }
</style>

<?php include '../../includes/footer.php'; ?>
