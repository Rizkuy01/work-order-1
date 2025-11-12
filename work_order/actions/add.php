<?php
include '../../includes/session_check.php';
include '../../includes/role_check.php';
only(['Maintenance']);
include '../../config/database.php';
include '../../includes/layout.php';
echo '<link rel="stylesheet" href="../../assets/css/bootstrap.min.css">';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $creator    = mysqli_real_escape_string($conn, $_SESSION['nama']);
  $npk        = mysqli_real_escape_string($conn, $_SESSION['nik']);
  $initiator  = $creator;
  $section    = mysqli_real_escape_string($conn, $_SESSION['dept']);
  $tipe       = mysqli_real_escape_string($conn, $_POST['tipe']);
  $line       = mysqli_real_escape_string($conn, $_POST['line']);
  $nama_mesin = mysqli_real_escape_string($conn, $_POST['nama_mesin']);
  $judul_wo   = mysqli_real_escape_string($conn, $_POST['judul_wo']);
  $detail_wo  = mysqli_real_escape_string($conn, $_POST['detail_wo']);
  $tgl_temuan = mysqli_real_escape_string($conn, $_POST['tgl_temuan']);
  $id_user    = $_SESSION['id_user'];
  $tgl_input  = date('Y-m-d');
  $status     = 'WAITING SCHEDULE';

  $insert = "
    INSERT INTO work_order 
    (creator, npk, initiator, section, tipe, line, nama_mesin, judul_wo, detail_wo, tgl_temuan, fotobefore, status, tgl_input, id_user_input)
    VALUES 
    ('$creator', '$npk', '$initiator', '$section', '$tipe', '$line', '$nama_mesin', '$judul_wo', '$detail_wo', '$tgl_temuan', '$status', '$tgl_input', '$id_user')
  ";

  if (mysqli_query($conn, $insert)) {
    echo "<script>alert('✅ Work Order berhasil ditambahkan');window.location='../layout.php?page=my_wo';</script>";
  } else {
    echo "<div class='alert alert-danger mt-3 text-center'>Gagal menambahkan data: " . mysqli_error($conn) . "</div>";
  }
}
?>

<div class="container-fluid py-4">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="card border-0 shadow-lg">
        <div class="card-header bg-danger-gradient text-white fw-semibold">
          <i class="fa-solid fa-plus-circle me-2"></i> Tambah Data Work Order
        </div>
        <div class="card-body p-4">
          <form method="POST" enctype="multipart/form-data">
            
            <!-- Creator, NPK, Initiator, Section -->
            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label text-danger fw-semibold">Creator</label>
                <input type="text" class="form-control bg-body-secondary" value="<?= $_SESSION['nama'] ?>" readonly>
              </div>
              <div class="col-md-6">
                <label class="form-label text-danger fw-semibold">NPK</label>
                <input type="text" class="form-control bg-body-secondary" value="<?= $_SESSION['npk'] ?>" readonly>
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label text-danger fw-semibold">Initiator</label>
                <input type="text" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label text-danger fw-semibold">Section</label>
                <input type="text" class="form-control bg-body-secondary" value="<?= $_SESSION['dept'] ?>" readonly>
              </div>
            </div>

            <!-- Tipe, Line -->
            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label text-danger fw-semibold">Tipe Perbaikan</label>
                <select name="tipe" class="form-select" required>
                  <option value="">-- Pilih --</option>
                  <option value="Repair">Repair</option>
                  <option value="Improve">Improve</option>
                  <option value="Predictive">Predictive</option>
                  <option value="Preventive">Preventive</option>
                  <option value="DCM">DCM</option>
                  <option value="Blue Tag">Blue Tag</option>
                  <option value="Red Tag">Red Tag</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label text-danger fw-semibold">Line</label>
                <input type="text" name="line" class="form-control" required>
              </div>
            </div>

            <!-- Tanggal Temuan -->
            <div class="mb-3">
              <label class="form-label text-danger fw-semibold">Tanggal Temuan</label>
              <input type="date" name="tgl_temuan" class="form-control" required>
            </div>

            <!-- Mesin, Judul, Detail -->
            <div class="mb-3">
              <label class="form-label text-danger fw-semibold">No & Nama Mesin</label>
              <select name="nama_mesin" class="form-select" required>
                <option value="">-- Pilih Mesin --</option>
                <option value="OC307 - DAMPING FORCE TESTER">OC307 - DAMPING FORCE TESTER</option>
                <option value="OTC030 - GRAVITY DIE CASTING 33">OTC030 - GRAVITY DIE CASTING 33</option>
                <option value="OC205 - OIL SEAL PRESS">OC205 - OIL SEAL PRESS</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label text-danger fw-semibold">Judul WO</label>
              <input type="text" name="judul_wo" class="form-control" required>
            </div>

            <div class="mb-3">
              <label class="form-label text-danger fw-semibold">Detail WO (Opsional)</label>
              <textarea name="detail_wo" class="form-control" rows="4"></textarea>
            </div>

            <div class="text-end mt-4">
              <button type="submit" class="btn btn-danger px-4 text-white fw-semibold">
                <i class="fa-solid fa-save me-1"></i> Submit
              </button>
            </div>

          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
  .bg-danger-gradient { background: linear-gradient(135deg, #ff4b2b, #c0392b); }
  .card { border-radius: 12px; }
  input.form-control, textarea.form-control, select.form-select {
    border-radius: 8px;
  }
  input:focus, textarea:focus, select:focus {
    border-color: #dc3545;
    box-shadow: 0 0 5px rgba(220,53,69,0.3);
  }
</style>

<?php include '../../includes/footer.php'; ?>
