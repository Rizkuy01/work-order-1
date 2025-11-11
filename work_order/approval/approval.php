<?php
include '../../includes/session_check.php';
include '../../includes/role_check.php';
only(['Supervisor', 'Super Administrator']);
include '../../includes/layout.php';
include '../../config/database.php';

// Helper aman
function safe($value) {
  return htmlspecialchars($value ?? '-', ENT_QUOTES, 'UTF-8');
}

// Pagination
$limit = 10;
$page = $_GET['page'] ?? 1;
$offset = ($page - 1) * $limit;

// Query utama
$query = "
  SELECT wo.*, ws.plan_date, ws.plan_time, ws.pic, ws.note, u.nama AS foreman_name
  FROM work_order wo
  JOIN wo_schedule ws ON wo.id_work_order = ws.id_work_order
  JOIN user u ON ws.scheduled_by = u.id_user
  WHERE wo.status = 'WAITING APPROVAL'
  ORDER BY ws.plan_date ASC
  LIMIT $limit OFFSET $offset
";
$result = mysqli_query($conn, $query);

// Hitung total data untuk pagination
$totalData = mysqli_fetch_assoc(mysqli_query($conn, "
  SELECT COUNT(*) AS total
  FROM work_order wo
  JOIN wo_schedule ws ON wo.id_work_order = ws.id_work_order
  WHERE wo.status = 'WAITING APPROVAL'
"))['total'];
$totalPages = ceil($totalData / $limit);
?>

<div class="container-fluid px-4">
  <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
    <h4 class="fw-semibold text-primary"><i class="fa-solid fa-clipboard-check me-2"></i> Approval Jadwal Work Order</h4>
  </div>

  <div class="card shadow border-0">
    <div class="card-body table-responsive">
      <table class="table table-hover align-middle text-center">
        <thead class="table-primary text-dark">
          <tr>
            <th>Judul</th>
            <th>Mesin</th>
            <th>Tanggal Rencana</th>
            <th>Jam Rencana</th>
            <th>PIC</th>
            <th>Foreman</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
              <?php
                $status = $row['status'];
                $badgeStyle = match($status) {
                  'WAITING APPROVAL' => 'background: linear-gradient(135deg, #e39eff, #8e44ad); color:white;',
                  'WAITING SCHEDULE' => 'background: linear-gradient(135deg, #f1c40f, #f39c12); color:white;',
                  'ON PROGRESS' => 'background: linear-gradient(135deg, #fb963d, #d35400); color:white;',
                  'FINISHED' => 'background: linear-gradient(135deg, #5ce894, #23d23a); color:white;',
                  'REJECTED' => 'background: linear-gradient(135deg, #ff7363, #c0392b); color:white;',
                  default => 'background: #dcdcdc; color:#333;',
                };
              ?>
              <tr>
                <td class="fw-semibold text-start"><?= safe($row['judul_wo']) ?></td>
                <td><?= safe($row['nama_mesin']) ?></td>
                <td><?= safe($row['plan_date']) ?></td>
                <td><?= safe($row['plan_time']) ?></td>
                <td><?= safe($row['pic']) ?></td>
                <td><?= safe($row['foreman_name']) ?></td>
                <td>
                  <form method="POST" action="approval_action.php" class="d-inline">
                    <input type="hidden" name="id" value="<?= $row['id_work_order'] ?>">
                    <button type="submit" name="action" value="approve"
                      class="btn btn-sm fw-semibold text-white"
                      style="background: linear-gradient(135deg, #23d23a, #1a9b2b); border:none;">
                      <i class="fa-solid fa-check me-1"></i> Approve
                    </button>
                  </form>
                  <form method="POST" action="approval_action.php" class="d-inline">
                    <input type="hidden" name="id" value="<?= $row['id_work_order'] ?>">
                    <button type="submit" name="action" value="reject"
                      class="btn btn-sm fw-semibold text-white"
                      style="background: linear-gradient(135deg, #ff7363, #c0392b); border:none;">
                      <i class="fa-solid fa-xmark me-1"></i> Reject
                    </button>
                  </form>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="7" class="text-muted py-4">
                <i class="fa-solid fa-circle-info me-2 text-secondary"></i>
                Tidak ada Work Order yang menunggu approval saat ini.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Pagination -->
  <?php if ($totalPages > 1): ?>
    <nav class="mt-3">
      <ul class="pagination justify-content-center">
        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
          <a class="page-link" href="?page=<?= $page - 1 ?>">← Prev</a>
        </li>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
          </li>
        <?php endfor; ?>
        <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
          <a class="page-link" href="?page=<?= $page + 1 ?>">Next →</a>
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
