<?php
include '../../includes/session_check.php';
include '../../includes/role_check.php';
only(['Foreman', 'Super Administrator']);

include '../../includes/layout.php';
include '../../config/database.php';

// Ambil ID WO
$id = $_GET['id'] ?? 0;
$result = mysqli_query($conn, "SELECT * FROM work_order WHERE id_work_order = $id");
$data = mysqli_fetch_assoc($result);

if (!$data) {
  echo "<div class='alert alert-danger m-4'>Data Work Order tidak ditemukan.</div>";
  include '../../includes/footer.php';
  exit;
}

// Simpan jadwal
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $plan_date = mysqli_real_escape_string($conn, $_POST['plan_date']);
  $plan_time = mysqli_real_escape_string($conn, $_POST['plan_time']);
  $pic       = mysqli_real_escape_string($conn, $_POST['pic']);
  $note      = mysqli_real_escape_string($conn, $_POST['note']);
  $id_user   = $_SESSION['id_user'];

  $insert = "INSERT INTO wo_schedule (id_work_order, plan_date, plan_time, pic, note, scheduled_by)
             VALUES ($id, '$plan_date', '$plan_time', '$pic', '$note', $id_user)";

  if (mysqli_query($conn, $insert)) {
    mysqli_query($conn, "UPDATE work_order SET status = 'WAITING APPROVAL' WHERE id_work_order = $id");
    echo "<script>alert('Jadwal berhasil disimpan!');window.location='schedule.php';</script>";
  } else {
    echo "<div class='alert alert-danger'>Gagal menyimpan jadwal: " . mysqli_error($conn) . "</div>";
  }
}
?>

<div class="container-fluid px-4">
  <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
    <h4 class="fw-semibold text-primary"><i class="fa-solid fa-calendar-plus me-2"></i> Input Jadwal Work Order</h4>
    <a href="schedule.php" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-arrow-left me-1"></i> Kembali</a>
  </div>

  <div class="card shadow border-0">
    <div class="card-header bg-primary text-white fw-semibold">
      Detail Work Order
    </div>
    <div class="card-body p-4">
      <form method="POST" class="needs-validation" novalidate>
        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label">Nama Mesin</label>
            <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($data['nama_mesin']) ?>" readonly>
          </div>
          <div class="col-md-6">
            <label class="form-label">Judul WO</label>
            <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($data['judul_wo']) ?>" readonly>
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label">Tanggal Rencana</label>
            <input type="date" name="plan_date" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Jam Rencana</label>
            <input type="time" name="plan_time" class="form-control" required>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">PIC (Person in Charge)</label>
          <input type="text" name="pic" class="form-control" placeholder="Nama petugas / teknisi" required>
        </div>

        <div class="mb-4">
          <label class="form-label">Catatan</label>
          <textarea name="note" class="form-control" rows="3" placeholder="Catatan tambahan (opsional)"></textarea>
        </div>

        <div class="text-end">
          <button type="submit" class="btn btn-success px-4 fw-semibold">
            <i class="fa-solid fa-save me-1"></i> Simpan Jadwal
          </button>
          <a href="schedule.php" class="btn btn-secondary px-4 fw-semibold">
            <i class="fa-solid fa-xmark me-1"></i> Batal
          </a>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
  .card {
    border-radius: 12px;
    overflow: hidden;
  }
  .card-header {
    font-size: 1rem;
    padding: 0.75rem 1.25rem;
  }
  label {
    font-weight: 600;
  }
  input[readonly] {
    color: #555;
    font-weight: 500;
  }
  .form-control {
    border-radius: 8px;
  }
  .btn {
    border-radius: 8px;
  }
</style>

<?php include '../../includes/footer.php'; ?>
