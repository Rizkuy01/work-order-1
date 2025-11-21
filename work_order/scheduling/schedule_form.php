<?php
include '../../includes/session_check.php';
include '../../includes/role_check.php';
only(['Foreman', 'Super Administrator']);

include '../../includes/layout.php';
include '../../config/database.php';

// ======== AMBIL DATA WO BY ID ========
$id = $_GET['id'] ?? 0;
$result = mysqli_query($conn, "SELECT * FROM work_order WHERE id_work_order = $id");
$data = mysqli_fetch_assoc($result);

if (!$data) {
  echo "<div class='alert alert-danger m-4'>Data Work Order tidak ditemukan.</div>";
  include '../../includes/footer.php';
  exit;
}

// ======== AMBIL TEKNISI DARI DB LEMBUR1 ========
$teknisiQuery = "
  SELECT full_name 
  FROM ct_users 
  WHERE dept IN ('PE2W','PE4W')
    AND golongan IN (1, 2)
  ORDER BY full_name ASC
";
$teknisi = mysqli_query($conn_lembur, $teknisiQuery);

// ========= SIMPAN JADWAL =========
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $plan_date = mysqli_real_escape_string($conn, $_POST['plan_date']);
  $plan_time = mysqli_real_escape_string($conn, $_POST['plan_time']);
  $pic       = mysqli_real_escape_string($conn, $_POST['pic']);
  $pic2      = mysqli_real_escape_string($conn, $_POST['pic2']);
  $pic3      = mysqli_real_escape_string($conn, $_POST['pic3']);
  $note      = mysqli_real_escape_string($conn, $_POST['note']);
  $id_user   = $_SESSION['npk'];

  // INSERT KE TABEL wo_schedule
  $insert = "
    INSERT INTO wo_schedule 
    (id_work_order, plan_date, plan_time, pic, note, scheduled_by)
    VALUES 
    ($id, '$plan_date', '$plan_time', '$pic', '$note', '$id_user')
  ";

  // UPDATE KE TABEL work_order
  $update = "
      UPDATE work_order SET
        tgl_plan = '$plan_date',
        jam_plan = '$plan_time',
        pic = '$pic',
        pic2 = '$pic2',
        pic3 = '$pic3',
        note = '$note',
        person_scheduled = '$id_user',
        status = 'WAITING APPROVAL'
      WHERE id_work_order = $id
  ";

  if (mysqli_query($conn, $insert) && mysqli_query($conn, $update)) {

      echo "
      <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
      <script>
          Swal.fire({
              icon: 'success',
              title: 'Berhasil!',
              html: 'Work Order telah dijadwalkan! Tunggu persetujuan dari Supervisor.',
              confirmButtonColor: '#28a745',
              confirmButtonText: 'OK'
          }).then(() => {
              window.location = 'schedule.php';
          });
      </script>";
      exit;

  } else {
      $error = mysqli_error($conn);
      echo "
      <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
      <script>
          Swal.fire({
              icon: 'error',
              title: 'Gagal!',
              html: 'Terjadi kesalahan:<br><b>$error</b>',
              confirmButtonColor: '#d33'
          }).then(() => {
              window.location = 'schedule.php';
          });
      </script>";
      exit;
  }
}
?>

<div class="container-fluid px-4 py-3">
  <div class="card shadow border-0">
    <div class="card-header text-white fw-semibold d-flex align-items-center justify-content-between"
         style="background: linear-gradient(90deg, #ff4b2b, #ff416c); font-size: 1.1rem;">
      <div><i class="fa-solid fa-calendar-plus me-2"></i> Input Jadwal Work Order</div>
      <a href="schedule.php" class="btn btn-light btn-sm fw-semibold shadow-sm">
        <i class="fa-solid fa-arrow-left me-1"></i> Kembali
      </a>
    </div>

    <div class="card-body bg-white p-4">
      <form method="POST">

        <div class="row mb-4">
          <div class="col-md-6">
            <label class="form-label">Nama Mesin</label>
            <input type="text" class="form-control bg-light fw-semibold" 
                   value="<?= htmlspecialchars($data['nama_mesin']) ?>" readonly>
          </div>
          <div class="col-md-6">
            <label class="form-label">Judul WO</label>
            <input type="text" class="form-control bg-light fw-semibold" 
                   value="<?= htmlspecialchars($data['judul_wo']) ?>" readonly>
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

        <!-- PIC 1-2-3 DROPDOWN -->
        <div class="row mb-3">

          <!-- PIC 1 -->
          <div class="col-md-4">
            <label class="form-label">PIC 1</label>
            <select name="pic" id="pic1" class="form-select" required>
              <option value="">-- Pilih PIC 1 --</option>
              <?php
                $list = mysqli_query($conn_lembur, $teknisiQuery);
                while ($t = mysqli_fetch_assoc($list)):
              ?>
                <option value="<?= $t['full_name'] ?>"><?= $t['full_name'] ?></option>
              <?php endwhile; ?>
            </select>
          </div>

          <!-- PIC 2 -->
          <div class="col-md-4">
            <label class="form-label">PIC 2</label>
            <select name="pic2" id="pic2" class="form-select">
              <option value="">-- Pilih PIC 2 --</option>
              <?php
                $list = mysqli_query($conn_lembur, $teknisiQuery);
                while ($t = mysqli_fetch_assoc($list)):
              ?>
                <option value="<?= $t['full_name'] ?>"><?= $t['full_name'] ?></option>
              <?php endwhile; ?>
            </select>
          </div>

          <!-- PIC 3 -->
          <div class="col-md-4">
            <label class="form-label">PIC 3</label>
            <select name="pic3" id="pic3" class="form-select">
              <option value="">-- Pilih PIC 3 --</option>
              <?php
                $list = mysqli_query($conn_lembur, $teknisiQuery);
                while ($t = mysqli_fetch_assoc($list)):
              ?>
                <option value="<?= $t['full_name'] ?>"><?= $t['full_name'] ?></option>
              <?php endwhile; ?>
            </select>
          </div>

        </div>

        <div class="mb-4">
          <label class="form-label">Catatan</label>
          <textarea name="note" class="form-control" rows="3"></textarea>
        </div>

        <div class="text-end">
          <button type="submit" class="btn btn-success-gradient px-4 fw-semibold me-2">
            <i class="fa-solid fa-save me-1"></i> Jadwalkan
          </button>
          <a href="schedule.php" class="btn btn-secondary px-4 fw-semibold">
            <i class="fa-solid fa-xmark me-1"></i> Batal
          </a>
        </div>

      </form>
    </div>
  </div>
</div>

<!-- HILANGKAN NAMA DI DROPDOWN BERIKUTNYA -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function refreshPIC() {
    let p1 = $("#pic1").val();
    let p2 = $("#pic2").val();

    $("#pic2 option, #pic3 option").show();

    if (p1) {
        $("#pic2 option[value='"+p1+"']").hide();
        $("#pic3 option[value='"+p1+"']").hide();
    }
    if (p2) {
        $("#pic3 option[value='"+p2+"']").hide();
    }
}

$("#pic1, #pic2").change(function(){
    refreshPIC();
});

refreshPIC();
</script>

<style>
.card { border-radius: 12px; }
label { font-weight: 600; }
.form-control, .form-select { border-radius: 8px; }
.btn-success-gradient {
  background: linear-gradient(90deg, #23d23a, #53fc97);
  border: none;
  color:white;
}
.btn-success-gradient:hover {
  background: linear-gradient(90deg, #1bb730, #4cd67f);
}
</style>

<?php include '../../includes/footer.php'; ?>
