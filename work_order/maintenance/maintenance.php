<?php
include '../../includes/session_check.php';
include '../../includes/role_check.php';
only(['Maintenance', 'Super Administrator']);
include '../../config/database.php';
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

$where = "WHERE (wo.status IN ('OPENED', 'ON PROGRESS'))";
if ($search !== '') {
  $search = mysqli_real_escape_string($conn, $search);
  $where .= " AND (wo.judul_wo LIKE '%$search%' OR wo.nama_mesin LIKE '%$search%')";
}
if ($statusFilter !== '') {
  $statusFilter = mysqli_real_escape_string($conn, $statusFilter);
  $where .= " AND wo.status = '$statusFilter'";
}

// --- Hitung total data ---
$totalData = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM work_order wo $where"))['total'];
$totalPages = ceil($totalData / $limit);

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

  <!-- 🔴 HEADER + FILTER + TABLE DALAM SATU CARD -->
  <div class="card shadow border-0">
    <div class="card-header text-white fw-semibold d-flex align-items-center"
         style="background: linear-gradient(90deg, #ff4b2b, #ff416c); font-size: 1.1rem;">
      <i class="fa-solid fa-list-check me-2"></i> Daftar My Work Order
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
        <div class="col-md-2 d-grid">
          <button class="btn btn-danger-gradient fw-semibold text-white" type="submit">
            <i class="fa-solid fa-filter me-1"></i> Filter
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
                  <td><?= safe($row['plan_date'] ?: '-') ?></td>
                  <td><?= safe($row['plan_time'] ?: '-') ?></td>
                  <td>
                    <span class="badge px-3 py-2" style="<?= $badgeStyle ?> border-radius:8px; box-shadow:0 2px 4px rgba(0,0,0,0.15);">
                      <?= strtoupper(safe($row['status'])) ?>
                    </span>
                  </td>
                  <td>
                    <button type="button" class="btn btn-danger btn-sm text-white fw-semibold shadow-sm"
                            onclick="showDetail(<?= $row['id_work_order'] ?>)">
                      <i class="fa-solid fa-eye me-1"></i> Detail
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
</style>

<?php include '../../includes/footer.php'; ?>
