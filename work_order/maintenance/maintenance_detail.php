<?php
include '../../config/database.php';

$id = $_GET['id'] ?? 0;
$query = "
  SELECT wo.*, ws.plan_date, ws.plan_time, ws.pic AS pic_schedule
  FROM work_order wo
  LEFT JOIN wo_schedule ws ON wo.id_work_order = ws.id_work_order
  WHERE wo.id_work_order = $id
";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);

if (!$row) {
  echo "<div class='text-center text-danger py-4'>Data tidak ditemukan.</div>";
  exit;
}

function safe($v) { return htmlspecialchars($v ?? '-', ENT_QUOTES, 'UTF-8'); }

$status = $row['status'] ?? $row['status_show'] ?? '-';
$badgeStyle = match($status) {
  'WAITING SCHEDULE' => 'background: linear-gradient(135deg, #f1c40f, #f39c12); color:white;',
  'WAITING APPROVAL' => 'background: linear-gradient(135deg, #e39eff, #8e44ad); color:white;',
  'OPENED'           => 'background: linear-gradient(135deg, #b5c1c2, #636e72); color:white;',
  'ON PROGRESS'      => 'background: linear-gradient(135deg, #fb963d, #d35400); color:white;',
  'WAITING CHECKED'  => 'background: linear-gradient(135deg, #59ccfe, #086bff); color:white;',
  'FINISHED'         => 'background: linear-gradient(135deg, #5ce894, #23d23a); color:white;',
  'REJECTED'         => 'background: linear-gradient(135deg, #ff7363, #c0392b); color:white;',
  default            => 'background: #dcdcdc; color:#333;',
};
?>

<div class="modal-header border-0 pb-0">
  <div class="w-100 text-center">
    <h5 class="fw-bold text-primary mb-1"><?= strtoupper(safe($row['judul_wo'])) ?></h5>
    <span class="badge px-3 py-2 fw-semibold" style="<?= $badgeStyle ?> border-radius:8px;"><?= strtoupper($status) ?></span>
  </div>
  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body pt-3">
  <div class="row g-3">
    <div class="col-md-6">
      <div class="info-item"><strong>ID Work Order</strong><span><?= safe($row['id_work_order']) ?></span></div>
      <div class="info-item"><strong>Nama Mesin</strong><span><?= safe($row['nama_mesin']) ?></span></div>
      <div class="info-item"><strong>Deskripsi</strong><span><?= safe($row['detail_wo']) ?></span></div>
      <div class="info-item"><strong>Tipe</strong><span><?= safe($row['tipe']) ?></span></div>
      <div class="info-item"><strong>Line</strong><span><?= safe($row['line']) ?></span></div>
      <div class="info-item"><strong>Section</strong><span><?= safe($row['section']) ?></span></div>
      <div class="info-item"><strong>PIC</strong><span><?= safe($row['pic']) ?></span></div>
      <div class="info-item"><strong>PIC 2</strong><span><?= safe($row['pic2']) ?></span></div>
      <div class="info-item"><strong>PIC 3</strong><span><?= safe($row['pic3']) ?></span></div>
    </div>

    <div class="col-md-6">
      <div class="info-item"><strong>Plan Perbaikan</strong><span><?= safe($row['plan_perbaikan']) ?></span></div>
      <div class="info-item"><strong>Tanggal Plan</strong><span><?= safe($row['plan_date']) ?></span></div>
      <div class="info-item"><strong>Jam Plan</strong><span><?= safe($row['plan_time']) ?></span></div>
      <div class="info-item"><strong>Jam Selesai</strong><span><?= safe($row['jam_finish']) ?></span></div>
      <div class="info-item"><strong>Person Scheduled</strong><span><?= safe($row['person_scheduled']) ?></span></div>
      <div class="info-item"><strong>Person Accept</strong><span><?= safe($row['person_accept']) ?></span></div>
      <div class="info-item"><strong>Person Finish</strong><span><?= safe($row['person_finish']) ?></span></div>
      <div class="info-item"><strong>Note</strong><span><?= safe($row['note']) ?></span></div>
      <div class="info-item"><strong>Reject Note</strong><span><?= safe($row['reject_note']) ?></span></div>
      <div class="info-item"><strong>Dibuat Oleh</strong><span><?= safe($row['creator']) ?></span></div>
      <div class="info-item"><strong>Initiator</strong><span><?= safe($row['initiator']) ?></span></div>
    </div>
  </div>

  <hr class="my-3">

  <?php if (in_array($row['status'], ['OPENED', 'ON PROGRESS'])): ?>
  <div class="text-end">
    <form method="POST" action="maintenance_action.php" class="d-inline">
      <input type="hidden" name="id" value="<?= $row['id_work_order'] ?>">
      <?php if ($row['status'] == 'OPENED'): ?>
        <button type="button" class="btn btn-warning text-white fw-semibold" onclick="startWO(<?= $row['id_work_order'] ?>)">
            <i class="fa-solid fa-play me-1"></i> Mulai
        </button>
      <?php elseif ($row['status'] == 'ON PROGRESS'): ?>
        <button type="button" class="btn btn-success text-white fw-semibold" onclick="finishWO(<?= $row['id_work_order'] ?>)">
            <i class="fa-solid fa-check me-1"></i> Selesai
        </button>
      <?php endif; ?>
    </form>
  </div>
  <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
  function startWO(id) {
      Swal.fire({
          title: 'Mulai Work Order?',
          text: 'Setelah dimulai, WO akan berpindah ke status ON PROGRESS',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Ya, mulai!',
          cancelButtonText: 'Batal'
      }).then((result) => {
          if (result.isConfirmed) {
              const form = document.createElement('form');
              form.method = 'POST';
              form.action = 'maintenance_action.php';

              form.innerHTML = `
                  <input type="hidden" name="id" value="${id}">
                  <input type="hidden" name="action" value="progress">
              `;
              
              document.body.appendChild(form);
              form.submit();
          }
      });
  }

  function finishWO(idWO) {
    Swal.fire({
        title: 'Selesaikan Work Order?',
        html: `
            <p class="mb-2">Upload Foto After sebelum menyelesaikan WO:</p>
            <input type="file" id="fotoAfter" accept="image/*" class="form-control">
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Selesaikan WO',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#d33',
        preConfirm: () => {

            const fileInput = document.getElementById('fotoAfter');

            if (!fileInput.files[0]) {
                Swal.showValidationMessage('Foto after wajib di-upload!');
                return false;
            }

            return fileInput.files[0]; 
        }
    }).then((result) => {
        if (result.isConfirmed) {

            let file = result.value;
            let formData = new FormData();

            formData.append('id', idWO);
            formData.append('action', 'finish');
            formData.append('fotoafter', file);

            fetch('maintenance_action.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(out => {
                document.write(out); 
            });
        }
    });
}
</script>


<style>
  .info-item {
    display: flex;
    justify-content: space-between;
    border-bottom: 1px dashed #e0e0e0;
    padding: 5px 0;
  }
  .info-item strong {
    color: #2c3e50;
    font-weight: 600;
    width: 45%;
  }
  .info-item span {
    color: #34495e;
    width: 50%;
    text-align: right;
  }
  .modal-body {
    font-size: 0.93rem;
  }
  @media (max-width: 768px) {
    .info-item { flex-direction: column; align-items: flex-start; }
    .info-item span { text-align: left; width: 100%; }
  }
</style>
