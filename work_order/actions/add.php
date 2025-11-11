<?php
include '../../includes/session_check.php';
include '../../includes/role_check.php';
only(['Maintenance']);
include '../../config/database.php';
include '../../includes/layout.php';
echo '<link rel="stylesheet" href="../../assets/css/bootstrap.min.css">';


// Proses simpan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $mesin = mysqli_real_escape_string($conn, $_POST['nama_mesin']);
  $judul = mysqli_real_escape_string($conn, $_POST['judul_wo']);
  $deskripsi = mysqli_real_escape_string($conn, $_POST['detail_wo']);
  $tgl_input = date('Y-m-d H:i:s');
  $status = 'WAITING SCHEDULE';
  $id_user = $_SESSION['id_user'];

  $insert = "INSERT INTO work_order (nama_mesin, judul_wo, detail_wo, tgl_input, status, id_user_input)
             VALUES ('$mesin', '$judul', '$deskripsi', '$tgl_input', '$status', '$id_user')";
  
  if (mysqli_query($conn, $insert)) {
    echo "<script>alert('✅ Work Order berhasil ditambahkan');window.location='../layout.php?page=my_wo';</script>";
    exit;
  } else {
    echo "<div class='alert alert-danger mt-3 text-center'>Gagal menambahkan data: " . mysqli_error($conn) . "</div>";
  }
}
?>

<!-- FORM TAMBAH WORK ORDER -->
<div class="container-fluid py-4">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="card border-0 shadow-lg">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
          <h5 class="mb-0"><i class="fa-solid fa-plus-circle me-2"></i>Tambah Work Order</h5>
          <a href="../layout.php?page=my_wo" class="btn btn-light btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali
          </a>
        </div>
        <div class="card-body p-4">
          <form method="POST">
            <div class="mb-3">
              <label for="nama_mesin" class="form-label fw-semibold">
                <i class="fa-solid fa-cog me-1"></i> Nama Mesin
              </label>
              <input type="text" name="nama_mesin" id="nama_mesin" class="form-control form-control-lg" required placeholder="Masukkan nama mesin">
            </div>

            <div class="mb-3">
              <label for="judul_wo" class="form-label fw-semibold">
                <i class="fa-solid fa-pen me-1"></i> Judul Work Order
              </label>
              <input type="text" name="judul_wo" id="judul_wo" class="form-control form-control-lg" required placeholder="Contoh: Perbaikan Hidrolik Mesin 2">
            </div>

            <div class="mb-3">
              <label for="detail_wo" class="form-label fw-semibold">
                <i class="fa-solid fa-align-left me-1"></i> Deskripsi / Detail WO
              </label>
              <textarea name="detail_wo" id="detail_wo" class="form-control" rows="5" placeholder="Jelaskan detail masalah atau kebutuhan perbaikan..."></textarea>
            </div>

            <div class="text-end mt-4">
              <button type="submit" class="btn btn-success px-4">
                <i class="fa-solid fa-save me-1"></i> Simpan
              </button>
              <a href="../layout.php?page=my_wo" class="btn btn-secondary px-4">
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
    border-radius: 14px;
  }

  .card-header {
    border-top-left-radius: 14px;
    border-top-right-radius: 14px;
  }

  input.form-control, textarea.form-control {
    border-radius: 8px !important;
  }

  input:focus, textarea:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 6px rgba(13, 110, 253, 0.4);
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
