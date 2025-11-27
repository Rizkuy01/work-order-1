<?php
include '../../includes/session_check.php';
include '../../includes/role_check.php';
only(['Supervisor', 'Super Administrator']);
include '../../includes/layout.php';
include '../../config/database.php';

// Helper
function safe($v){ return htmlspecialchars($v ?? '-', ENT_QUOTES, 'UTF-8'); }

// Pagination
$limit = 10;
$page = $_GET['page'] ?? 1;
$offset = ($page - 1) * $limit;

// Ambil data WO yang menunggu approval
$query = "
  SELECT wo.*, ws.plan_date, ws.plan_time, ws.pic, ws.note, ws.scheduled_by
  FROM work_order wo
  JOIN wo_schedule ws ON wo.id_work_order = ws.id_work_order
  WHERE wo.status = 'WAITING APPROVAL'
  ORDER BY ws.plan_date ASC
  LIMIT $limit OFFSET $offset
";
$result = mysqli_query($conn, $query);

// Hitung total data
$totalData = mysqli_fetch_assoc(mysqli_query($conn,"
  SELECT COUNT(*) AS total
  FROM work_order wo
  JOIN wo_schedule ws ON wo.id_work_order = ws.id_work_order
  WHERE wo.status='WAITING APPROVAL'
"))['total'];

$totalPages = ceil($totalData / $limit);
?>

<div class="container-fluid px-4 py-3">
  <div class="card shadow border-0">
    <div class="card-header text-white fw-semibold"
         style="background: linear-gradient(90deg, #ff4b2b, #ff416c);">
      <i class="bi bi-clipboard-check me-2"></i> Approval Jadwal Work Order
    </div>

    <div class="card-body bg-white">
      <div class="table-responsive">
        <table class="table table-hover align-middle text-center">
          <thead class="table-light">
            <tr>
              <th>Judul</th>
              <th>Mesin</th>
              <th>Tanggal</th>
              <th>Jam</th>
              <th>PIC</th>
              <th>Foreman</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>

<?php if (mysqli_num_rows($result) > 0): ?>
  <?php while ($row = mysqli_fetch_assoc($result)):

      // Ambil nama foreman dari DB lembur1
      $foreman = mysqli_fetch_assoc(mysqli_query(
        $conn_lembur,
        "SELECT full_name FROM ct_users WHERE npk='{$row['scheduled_by']}' LIMIT 1"
      ));

      $foreman_name = $foreman['full_name'] ?? '-';

  ?>
    <tr>
      <td class="fw-semibold text-start"><?= safe($row['judul_wo']) ?></td>
      <td><?= safe($row['nama_mesin']) ?></td>
      <td><?= safe($row['plan_date']) ?></td>
      <td><?= safe($row['plan_time']) ?></td>
      <td><?= safe($row['pic']) ?></td>
      <td><?= safe($foreman_name) ?></td>

      <td>
        <div class="d-flex justify-content-center gap-2">

          <!-- Approve -->
          <form method="POST" action="approval_action.php">
            <input type="hidden" name="id" value="<?= $row['id_work_order'] ?>">
            <button type="submit" name="action" value="approve"
                    class="action-btn bg-success-gradient text-white">
              <i class="bi bi-check-lg"></i>
            </button>
            <div class="action-label text-success">Approve</div>
          </form>

          <!-- Reject -->
          <form method="POST" action="approval_action.php">
            <input type="hidden" name="id" value="<?= $row['id_work_order'] ?>">
            <button type="submit" name="action" value="reject"
                    class="action-btn bg-danger-gradient text-white">
              <i class="bi bi-x-lg"></i>
            </button>
            <div class="action-label text-danger">Reject</div>
          </form>

        </div>
      </td>
    </tr>
  <?php endwhile; ?>

<?php else: ?>
  <tr>
    <td colspan="7" class="text-muted py-4">
      Tidak ada Work Order yang menunggu approval.
    </td>
  </tr>
<?php endif; ?>

          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <?php if ($totalPages > 1): ?>
        <nav class="mt-3">
          <ul class="pagination justify-content-center">
            <li class="page-item <?= ($page <= 1 ? 'disabled' : '') ?>">
              <a class="page-link" href="?page=<?= $page-1 ?>">← Prev</a>
            </li>

            <?php for ($i=1; $i <= $totalPages; $i++): ?>
              <li class="page-item <?= ($i==$page?'active':'') ?>">
                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
              </li>
            <?php endfor; ?>

            <li class="page-item <?= ($page >= $totalPages ? 'disabled':'') ?>">
              <a class="page-link" href="?page=<?= $page+1 ?>">Next →</a>
            </li>
          </ul>
        </nav>
      <?php endif; ?>

    </div>
  </div>
</div>


<style>
  .card {
    border-radius: 12px;
    overflow: hidden;
  }

  table thead th {
    vertical-align: middle;
    font-weight: 600;
  }

  table tbody tr:hover {
    background-color: #fff7f7;
  }

  /* 🔘 Tombol kecil dengan icon */
  .action-btn {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    border: none;
    font-size: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: auto;
    transition: all 0.2s ease-in-out;
    cursor: pointer;
  }
  .action-btn:hover {
    transform: scale(1.1);
    opacity: 0.9;
  }

  .action-label {
    font-size: 11px;
    font-weight: 600;
    margin-top: 3px;
  }

  /* 🟢 Gradasi Approve */
  .bg-success-gradient {
    background: linear-gradient(90deg, #23d23a, #5ce894);
  }

  /* 🔴 Gradasi Reject */
  .bg-danger-gradient {
    background: linear-gradient(90deg, #ff4b2b, #ff416c);
  }

  /* Pagination Merah */
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
