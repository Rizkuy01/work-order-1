<?php
// Role check FIRST sebelum apapun
include '../../includes/session_check.php';
include '../../includes/role_check.php';

// Periksa role sebelum include layout (hanya Maintenance yang bisa akses)
only(['Maintenance']);

// SETELAH role check lolos, baru include yang lain
include '../../config/database.php';
include '../../config/upload_config.php';
include '../../includes/layout.php';
echo '<link rel="stylesheet" href="../../assets/css/bootstrap.min.css">';

// ========== AMBIL DATA SECTION (PROD) ==========
$q_section = mysqli_query($conn_breakdown, "SELECT DISTINCT prod FROM mesin ORDER BY prod ASC");


// ========== PROSES SIMPAN ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $creator    = mysqli_real_escape_string($conn, $_SESSION['nama']);
    $npk        = mysqli_real_escape_string($conn, $_SESSION['npk']);
    $initiator  = mysqli_real_escape_string($conn, $_POST['initiator']);
    $section    = mysqli_real_escape_string($conn, $_POST['section']);
    $tipe       = mysqli_real_escape_string($conn, $_POST['tipe']);
    $line       = mysqli_real_escape_string($conn, $_POST['line']);
    $nama_mesin = mysqli_real_escape_string($conn, $_POST['nama_mesin']);
    $judul_wo   = mysqli_real_escape_string($conn, $_POST['judul_wo']);
    $detail_wo  = mysqli_real_escape_string($conn, $_POST['detail_wo']);
    $tgl_temuan = mysqli_real_escape_string($conn, $_POST['tgl_temuan']);
    $id_user    = $_SESSION['id_user'];
    $tgl_input  = date('Y-m-d');
    $status     = 'WAITING SCHEDULE';

    // proses upload foto before
    $fotobefore = "";
    if (!empty($_FILES['fotobefore']['name'])) {
        ensureUploadDirs();
        $filename =  "BEFORE_" . time() . "_" . basename($_FILES['fotobefore']['name']);
        $target_path = UPLOADS_BEFORE_DIR . $filename;

        if (move_uploaded_file($_FILES['fotobefore']['tmp_name'], $target_path)) {
            $fotobefore = $filename;
        }
    }

    // insert ke database
    $insert = "
        INSERT INTO work_order 
        (creator, npk, initiator, section, tipe, line, nama_mesin, judul_wo, detail_wo, 
         tgl_temuan, fotobefore, status, tgl_input, id_user_input)
        VALUES 
        ('$creator', '$npk', '$initiator', '$section', '$tipe', '$line', '$nama_mesin', 
         '$judul_wo', '$detail_wo', '$tgl_temuan', '$fotobefore', '$status', '$tgl_input', '$id_user')
    ";

    if (mysqli_query($conn, $insert)) {
        echo "
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Work Order berhasil ditambahkan!',
                text: 'Data sudah masuk ke sistem. Mohon tunggu proses selanjutnya.',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                window.location = '../index.php';
            });
        </script>
        ";
        exit;
    } else {
        echo "
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal Menambahkan!',
                text: '".mysqli_real_escape_string($conn, mysqli_error($conn))."',
            });
        </script>
        ";
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
          
          <!-- WAJIB ADA UNTUK UPLOAD -->
          <form method="POST" enctype="multipart/form-data">

            <!-- CREATOR, NPK -->
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
                <input type="text" name="initiator" class="form-control" placeholder="Nama pengusul" required>
              </div>

              <div class="col-md-6">
                <label class="form-label text-danger fw-semibold">Section</label>
                <select name="section" id="section" class="form-select" required>
                  <option value="">-- Pilih Section --</option>
                  <?php while ($s = mysqli_fetch_assoc($q_section)) : ?>
                    <option value="<?= $s['prod'] ?>"><?= $s['prod'] ?></option>
                  <?php endwhile; ?>
                </select>
              </div>
            </div>

            <!-- TIPE & LINE -->
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
                <select name="line" id="line" class="form-select" required>
                  <option value="">-- Pilih Section Terlebih Dahulu --</option>
                </select>
              </div>
            </div>

            <!-- MESIN -->
            <div class="mb-3">
              <label class="form-label text-danger fw-semibold">No & Nama Mesin</label>
              <select name="nama_mesin" id="mesin" class="form-select" required>
                <option value="">-- Pilih Line Terlebih Dahulu --</option>
              </select>
            </div>

            <!-- FOTO BEFORE -->
            <div class="mb-3">
              <label class="form-label text-danger fw-semibold">Foto Before</label>
              <input type="file" name="fotobefore" class="form-control" accept="image/*">
              <small class="text-muted">Opsional — upload kondisi awal mesin</small>
            </div>

            <!-- TANGGAL TEMUAN -->
            <div class="mb-3">
              <label class="form-label text-danger fw-semibold">Tanggal Temuan</label>
              <input type="date" name="tgl_temuan" class="form-control" required>
            </div>

            <!-- JUDUL & DETAIL -->
            <div class="mb-3">
              <label class="form-label text-danger fw-semibold">Judul WO</label>
              <input type="text" name="judul_wo" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label text-danger fw-semibold">Detail WO (Opsional)</label>
              <textarea name="detail_wo" class="form-control" rows="4"></textarea>
            </div>

            <div class="text-end">
              <button type="submit" class="btn btn-danger px-4 fw-semibold text-white">
                <i class="fa-solid fa-save me-1"></i> Submit
              </button>
            </div>

          </form>

        </div>
      </div>
    </div>
  </div>
</div>

<!-- AJAX UNTUK LINE & MESIN -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function () {

    $("#section").change(function () {
        var section = $(this).val();
        $("#line").html('<option value="">Loading...</option>');

        $.post("load_line.php", { section: section }, function (data) {
            $("#line").html(data);
            $("#mesin").html('<option value="">-- Pilih Line Terlebih Dahulu --</option>');
        });
    });

    $("#line").change(function () {
        var line = $(this).val();
        $("#mesin").html('<option value="">Loading...</option>');

        $.post("load_mesin.php", { line: line }, function (data) {
            $("#mesin").html(data);
        });
    });

});
</script>

<style>
  .bg-danger-gradient { background: linear-gradient(135deg, #ff4b2b, #c0392b); }
  .card { border-radius: 12px; }
  input, textarea, select { border-radius: 8px !important; }
  input:focus, textarea:focus, select:focus {
    border-color: #dc3545;
    box-shadow: 0 0 5px rgba(220,53,69,0.3);
  }
</style>

<?php include '../../includes/footer.php'; ?>
