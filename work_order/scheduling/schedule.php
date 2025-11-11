<?php
include '../../includes/session_check.php';
include '../../includes/role_check.php';
only(['Foreman', 'Super Administrator']);
include '../../includes/layout.php';
include '../../config/database.php';

// Helper aman
function safe($value) {
  return htmlspecialchars($value ?? '-', ENT_QUOTES, 'UTF-8');
}

// Pagination setup
$limit = 10;
$page = $_GET['page'] ?? 1;
$offset = ($page - 1) * $limit;

// Search filter
$search = $_GET['search'] ?? '';
$where = "WHERE status = 'WAITING SCHEDULE'";
if ($search !== '') {
  $search = mysqli_real_escape_string($conn, $search);
  $where .= " AND (nama_mesin LIKE '%$search%' OR judul_wo LIKE '%$search%')";
}

// Hitung total data
$totalData = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM work_order $where"))['total'];
$totalPages = ceil($totalData / $limit);

// Query utama
$query = "SELECT * FROM work_order $where ORDER BY tgl_input ASC LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);
?>

<div class="container-fluid px-4">
  <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
    <h4 class="fw-semibold text-primary"><i class="fa-solid fa-calendar-days me-2"></i> Scheduling Work Order</h4>
  </div>

  <!-- FILTER -->
  <form method="GET" class="row g-3 mb-3">
    <div class="col-md-4">
      <input type="text" name="search" class="form-control" placeholder="Cari mesin atau judul WO..." value="<?= safe($search) ?>">
    </div>
    <div class="col-md-2">
      <button class="btn btn-outline-primary w-100" type="submit"><i class="fa-solid fa-search me-1"></i> Cari</button>
    </div>
  </form>

  <!-- TABLE -->
  <div class="card shadow border-0">
    <div class="card-body table-responsive">
      <table class="table table-hover align-middle text-center">
        <thead class="table-primary text-dark">
          <tr>
            <th>Nama Mesin</th>
            <th>Judul WO</th>
            <th>Tanggal Input</th>
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
              <?php
                $status = $row['status'];
                $badgeStyle = match($status) {
                  'WAITING SCHEDULE' => 'background: linear-gradient(135deg, #f1c40f, #f39c12); color:white;',
                  'WAITING APPROVAL' => 'background: linear-gradient(135deg, #e39eff, #8e44ad); color:white;',
                  'ON PROGRESS' => 'background: linear-gradient(135deg, #fb963d, #d35400); color:white;',
                  'FINISHED' => 'background: linear-gradient(135deg, #5ce894, #23d23a); color:white;',
                  'REJECTED' => 'background: linear-gradient(135deg, #ff7363, #c0392b); color:white;',
                  default => 'background: #dcdcdc; color:#333;',
                };
              ?>
              <tr>
                <td class="fw-semibold text-start"><?= safe($row['nama_mesin']) ?></td>
                <td><?= safe($row['judul_wo']) ?></td>
                <td><?= safe($row['tgl_input']) ?></td>
                <td>
                  <span class="badge px-3 py-2" style="<?= $badgeStyle ?> border-radius:8px; box-shadow:0 2px 4px rgba(0,0,0,0.15);">
                    <?= strtoupper(safe($row['status'])) ?>
                  </span>
                </td>
                <td>
                  <a href="schedule_form.php?id=<?= $row['id_work_order'] ?>" 
                     class="btn btn-primary btn-sm fw-semibold shadow-sm" 
                     style="background: linear-gradient(135deg, #007bff, #0056b3); border:none;">
                    <i class="fa-solid fa-calendar-plus me-1"></i> Buat Jadwal
                  </a>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="5" class="text-muted py-4">
                <i class="fa-solid fa-circle-info me-2 text-secondary"></i>
                Tidak ada Work Order yang menunggu penjadwalan saat ini.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- PAGINATION -->
  <?php if ($totalPages > 1): ?>
    <nav class="mt-3">
      <ul class="pagination justify-content-center">
        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
          <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>">← Prev</a>
        </li>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
          </li>
        <?php endfor; ?>

        <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
          <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>">Next →</a>
        </li>
      </ul>
    </nav>
  <?php endif; ?>
</div>

<style>
  .card { border-radius: 12px; }
  table thead th { vertical-align: middle; font-weight: 600; }
  table tbody tr:hover { background-color: #f9f9f9; }
  .btn-sm { border-radius: 6px; padding: 6px 12px; }
  .pagination .page-link { border-radius: 6px; margin: 0 2px; }
</style>

<?php include '../../includes/footer.php'; ?>
