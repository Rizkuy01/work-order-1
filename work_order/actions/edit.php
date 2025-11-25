<?php
include '../../includes/session_check.php';
include '../../includes/role_check.php';
only(['Maintenance', 'Super Administrator']);
include '../../config/database.php';
include '../../includes/layout.php';

// ================= GET DATA WORK ORDER ==================
$id = $_GET['id'] ?? 0;
$q = mysqli_query($conn, "SELECT * FROM work_order WHERE id_work_order = $id");
$data = mysqli_fetch_assoc($q);

if (!$data) {
    echo "<div class='alert alert-danger text-center mt-4'>Data tidak ditemukan.</div>";
    include '../../includes/footer.php';
    exit;
}

// ================= GET SECTION (FROM MESIN) ==================
$q_section = mysqli_query($conn, "SELECT DISTINCT prod FROM mesin ORDER BY prod ASC");

// ================= UPDATE WORK ORDER ==================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $initiator  = mysqli_real_escape_string($conn, $_POST['initiator']);
    $dept       = mysqli_real_escape_string($conn, $_POST['section']);
    $tipe       = mysqli_real_escape_string($conn, $_POST['tipe']);
    $line       = mysqli_real_escape_string($conn, $_POST['line']);
    $nama_mesin = mysqli_real_escape_string($conn, $_POST['nama_mesin']);
    $judul_wo   = mysqli_real_escape_string($conn, $_POST['judul_wo']);
    $detail_wo  = mysqli_real_escape_string($conn, $_POST['detail_wo']);
    $tgl_temuan = mysqli_real_escape_string($conn, $_POST['tgl_temuan']);

    $update = "
        UPDATE work_order SET 
        initiator = '$initiator',
        dept = '$dept',
        tipe = '$tipe',
        line = '$line',
        nama_mesin = '$nama_mesin',
        judul_wo = '$judul_wo',
        detail_wo = '$detail_wo',
        tgl_temuan = '$tgl_temuan'
        WHERE id_work_order = $id
    ";

    if (mysqli_query($conn, $update)) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script><script>Swal.fire({icon: 'success', title: 'Berhasil!', text: 'Data berhasil diperbarui'}).then(() => { window.location='../index.php'; });</script>";
        exit;
    } else {
        echo "<div class='alert alert-danger mt-3 text-center'>
            Gagal memperbarui data: " . mysqli_error($conn) . "
        </div>";
    }
}
?>

<div class="container-fluid py-4">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="card shadow border-0">
        <div class="card-header bg-danger-gradient text-white fw-semibold">
          <i class="fa-solid fa-pen-to-square me-2"></i> Edit Work Order
        </div>

        <div class="card-body p-4">
          <form method="POST">

            <!-- CREATOR + NPK -->
            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label text-danger fw-semibold">Creator</label>
                <input type="text" class="form-control bg-body-secondary"
                  value="<?= $_SESSION['nama'] ?>" readonly>
              </div>

              <div class="col-md-6">
                <label class="form-label text-danger fw-semibold">NPK</label>
                <input type="text" class="form-control bg-body-secondary"
                  value="<?= $_SESSION['npk'] ?>" readonly>
              </div>
            </div>

            <!-- INITIATOR -->
            <div class="mb-3">
              <label class="form-label text-danger fw-semibold">Initiator</label>
              <input type="text" name="initiator" class="form-control"
                value="<?= htmlspecialchars($data['initiator']) ?>" required>
            </div>

            <!-- SECTION -->
            <div class="mb-3">
              <label class="form-label text-danger fw-semibold">Section</label>
              <select name="section" id="section" class="form-select" required>
                <option value="">-- Pilih Section --</option>
                <?php while ($s = mysqli_fetch_assoc($q_section)) : ?>
                  <option value="<?= $s['prod'] ?>"
                    <?= ($s['prod'] == $data['dept']) ? 'selected' : '' ?>>
                    <?= $s['prod'] ?>
                  </option>
                <?php endwhile; ?>
              </select>
            </div>

            <!-- TIPE & LINE -->
            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label text-danger fw-semibold">Tipe Perbaikan</label>
                <select name="tipe" class="form-select" required>
                  <option value="Repair" <?= $data['tipe']=='Repair'?'selected':'' ?>>Repair</option>
                  <option value="Improve" <?= $data['tipe']=='Improve'?'selected':'' ?>>Improve</option>
                  <option value="Predictive" <?= $data['tipe']=='Predictive'?'selected':'' ?>>Predictive</option>
                  <option value="Preventive" <?= $data['tipe']=='Preventive'?'selected':'' ?>>Preventive</option>
                  <option value="DCM" <?= $data['tipe']=='DCM'?'selected':'' ?>>DCM</option>
                  <option value="Blue Tag" <?= $data['tipe']=='Blue Tag'?'selected':'' ?>>Blue Tag</option>
                  <option value="Red Tag" <?= $data['tipe']=='Red Tag'?'selected':'' ?>>Red Tag</option>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label text-danger fw-semibold">Line</label>
                <select name="line" id="line" class="form-select" required>
                  <option value="<?= $data['line'] ?>"><?= $data['line'] ?></option>
                </select>
              </div>
            </div>

            <!-- MESIN -->
            <div class="mb-3">
              <label class="form-label text-danger fw-semibold">No & Nama Mesin</label>
              <select name="nama_mesin" id="mesin" class="form-select" required>
                <option value="<?= $data['nama_mesin'] ?>">
                  <?= $data['nama_mesin'] ?>
                </option>
              </select>
            </div>

            <!-- TGL TEMUAN -->
            <div class="mb-3">
              <label class="form-label text-danger fw-semibold">Tanggal Temuan</label>
              <input type="date" name="tgl_temuan" class="form-control"
                value="<?= $data['tgl_temuan'] ?>" required>
            </div>

            <!-- JUDUL & DETAIL -->
            <div class="mb-3">
              <label class="form-label text-danger fw-semibold">Judul WO</label>
              <input type="text" name="judul_wo" class="form-control"
                value="<?= $data['judul_wo'] ?>" required>
            </div>

            <div class="mb-3">
              <label class="form-label text-danger fw-semibold">Detail WO</label>
              <textarea name="detail_wo" class="form-control" rows="4"><?= $data['detail_wo'] ?></textarea>
            </div>

            <div class="text-end mt-4">
              <button type="submit" class="btn btn-danger text-white px-4 fw-semibold">
                <i class="fa-solid fa-save me-1"></i> Simpan
              </button>
            </div>

          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- AJAX LINE & MESIN -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$("#section").change(function(){
    $.post("load_line.php", { section: $(this).val() }, function(data){
        $("#line").html(data);
        $("#mesin").html('<option value="">-- Pilih Line Terlebih Dahulu --</option>');
    });
});

$("#line").change(function(){
    $.post("load_mesin.php", { line: $(this).val() }, function(data){
        $("#mesin").html(data);
    });
});
</script>

<style>
.bg-danger-gradient { background: linear-gradient(135deg,#ff4b2b,#c0392b); }
.card { border-radius: 12px; }
input, textarea, select { border-radius: 8px; }
</style>

<?php include '../../includes/footer.php'; ?>
