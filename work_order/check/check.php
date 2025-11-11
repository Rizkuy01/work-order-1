<?php
include '../../includes/session_check.php';
include '../../includes/role_check.php';
only(['Supervisor', 'Super Administrator']);

include '../../includes/header.php';
include '../../config/database.php';

// Helper aman
function safe($value) {
  return htmlspecialchars($value ?? '-', ENT_QUOTES, 'UTF-8');
}

// Ambil WO yang menunggu pengecekan
$query = "
  SELECT wo.*, ws.plan_date, ws.plan_time, ws.pic, u.nama AS maintenance_name
  FROM work_order wo
  LEFT JOIN wo_schedule ws ON wo.id_work_order = ws.id_work_order
  LEFT JOIN user u ON wo.id_user_input = u.id_user
  WHERE wo.status = 'WAITING CHECKED'
  ORDER BY ws.plan_date ASC
";
$result = mysqli_query($conn, $query);
?>

<h4 class="mb-3">Pengecekan Work Order (Supervisor)</h4>

<div class="card">
  <div class="card-body">
    <table class="table table-striped table-bordered align-middle">
      <thead class="table-dark">
        <tr>
          <!-- <th>Kode WO</th> -->
          <th>Judul</th>
          <th>Mesin</th>
          <th>Tanggal Rencana</th>
          <th>Jam</th>
          <th>PIC</th>
          <th>Maintenance</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (mysqli_num_rows($result) === 0): ?>
          <tr><td colspan="8" class="text-center">Tidak ada Work Order menunggu pengecekan</td></tr>
        <?php else: ?>
          <?php while ($row = mysqli_fetch_assoc($result)) : ?>
            <tr>
              <!-- <td><?= safe($row['kode_wo']) ?></td> -->
              <td><?= safe($row['judul_wo']) ?></td>
              <td><?= safe($row['nama_mesin']) ?></td>
              <td><?= safe($row['plan_date']) ?></td>
              <td><?= safe($row['plan_time']) ?></td>
              <td><?= safe($row['pic']) ?></td>
              <td><?= safe($row['maintenance_name']) ?></td>
              <td>
                <form method="POST" action="check_action.php" class="d-inline">
                  <input type="hidden" name="id" value="<?= $row['id_work_order'] ?>">
                  <button type="submit" name="action" value="finish" class="btn btn-success btn-sm">Approve</button>
                </form>
                <form method="POST" action="check_action.php" class="d-inline">
                  <input type="hidden" name="id" value="<?= $row['id_work_order'] ?>">
                  <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm">Reject</button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include '../../includes/footer.php'; ?>
