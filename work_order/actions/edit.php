<?php
include '../../includes/session_check.php';
include '../../includes/role_check.php';
only(['Maintenance', 'Super Administrator']);
include '../../config/database.php';
include '../../includes/layout.php';

$id = $_GET['id'] ?? 0;
$result = mysqli_query($conn, "SELECT * FROM work_order WHERE id_work_order=$id");
$data = mysqli_fetch_assoc($result);

if (!$data) {
  echo "<div class='alert alert-danger text-center mt-4'>Data tidak ditemukan.</div>";
  include '../../includes/footer.php';
  exit;
}

// Update data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $judul = mysqli_real_escape_string($conn, $_POST['judul_wo']);
  $status = mysqli_real_escape_string($conn, $_POST['status']);

  $update = "UPDATE work_order SET judul_wo='$judul', status='$status' WHERE id_work_order=$id";
  if (mysqli_query($conn, $update)) {
    echo "<script>
      alert('✅ Work Order berhasil diperbarui');
      window.location='../index.php';
    </script>";
    exit;
  } else {
    echo "<div class='alert alert-danger mt-3 text-center'>
      Gagal memperbarui data: " . mysqli_error($conn) . "
    </div>";
  }
}
?>

<div class="container-fluid px-4">
  <div class="row justify-content-center mt-4">
    <div class="col-lg-8">
      <div class="card shadow border-0">
        <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between">
          <h5 class="mb-0"><i class="fa-solid fa-pen-to-square me-2"></i> Edit Work Order</h5>
          <a href="../index.php" class="btn btn-light btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali
          </a>
        </div>

        <div class="card-body p-4">
          <form method="POST" id="formEditWO">

            <div class="mb-3">
              <label class="form-label fw-semibold"><i class="fa-solid fa-hashtag me-1"></i> Kode Mesin</label>
              <input type="text" class="form-control" value="<?= htmlspecialchars($data['nama_mesin']) ?>" readonly>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold"><i class="fa-solid fa-pen me-1"></i> Judul Work Order</label>
              <input type="text" name="judul_wo" class="form-control" value="<?= htmlspecialchars($data['judul_wo']) ?>" required>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold"><i class="fa-solid fa-layer-group me-1"></i> Status Work Order</label>
              <select name="status" class="form-select" required>
                <option value="">Pilih Status</option>
                <option value="WAITING SCHEDULE" <?= $data['status'] == 'WAITING SCHEDULE' ? 'selected' : '' ?>>Waiting Schedule</option>
                <option value="WAITING APPROVAL" <?= $data['status'] == 'WAITING APPROVAL' ? 'selected' : '' ?>>Waiting Approval</option>
                <option value="OPENED" <?= $data['status'] == 'OPENED' ? 'selected' : '' ?>>Opened</option>
                <option value="ON PROGRESS" <?= $data['status'] == 'ON PROGRESS' ? 'selected' : '' ?>>On Progress</option>
                <option value="WAITING CHECKED" <?= $data['status'] == 'WAITING CHECKED' ? 'selected' : '' ?>>Waiting Checked</option>
                <option value="FINISHED" <?= $data['status'] == 'FINISHED' ? 'selected' : '' ?>>Finished</option>
                <option value="REJECTED" <?= $data['status'] == 'REJECTED' ? 'selected' : '' ?>>Rejected</option>
              </select>
            </div>

            <div class="text-end mt-4">
              <button type="submit" class="btn btn-success px-4">
                <i class="fa-solid fa-save me-1"></i> Simpan Perubahan
              </button>
              <a href="../index.php" class="btn btn-secondary px-4">
                <i class="fa-solid fa-xmark me-1"></i> Batal
              </a>
            </div>

          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
  .card {
    border-radius: 12px;
  }

  .card-header {
    border-top-left-radius: 12px;
    border-top-right-radius: 12px;
  }

  input, select {
    border-radius: 8px !important;
  }

  input:focus, select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 4px rgba(13, 110, 253, 0.4);
  }

  .btn-success {
    background: linear-gradient(90deg, #1db954, #17a64a);
    border: none;
  }

  .btn-success:hover {
    background: linear-gradient(90deg, #17a64a, #138f3e);
  }
</style>

<?php include '../../includes/footer.php'; ?>
