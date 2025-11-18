<?php
include '../../includes/session_check.php';
include '../../includes/role_check.php';
only(['Supervisor', 'Super Administrator']);

include '../../config/database.php';
include '../../includes/layout.php';

$id = $_GET['id'] ?? 0;

$q = mysqli_query($conn, "SELECT * FROM work_order WHERE id_work_order=$id");
$data = mysqli_fetch_assoc($q);

if (!$data) {
    echo "<div class='alert alert-danger m-4'>Data tidak ditemukan.</div>";
    include '../../includes/footer.php';
    exit;
}

function safe($v) { return htmlspecialchars($v ?? '-', ENT_QUOTES, 'UTF-8'); }

$status = $data['status'];

$badgeStyle = match ($status) {
    'WAITING SCHEDULE' => 'background:linear-gradient(135deg,#f1c40f,#f39c12);color:white;',
    'WAITING APPROVAL' => 'background:linear-gradient(135deg,#d980fa,#6c3483);color:white;',
    'OPENED' => 'background:linear-gradient(135deg,#95a5a6,#34495e);color:white;',
    'ON PROGRESS' => 'background:linear-gradient(135deg,#ff8c42,#e67e22);color:white;',
    'WAITING CHECKED' => 'background:linear-gradient(135deg,#74b9ff,#0984e3);color:white;',
    'FINISHED' => 'background:linear-gradient(135deg,#2ecc71,#27ae60);color:white;',
    'REJECTED' => 'background:linear-gradient(135deg,#ff6b6b,#c0392b);color:white;',
    default => 'background:#bdc3c7;color:#2c3e50;',
};
?>

<div class="container py-4">

    <div class="card shadow border-0" style="border-radius:12px;">

        <!-- HEADER -->
        <div class="card-header text-white fw-semibold d-flex align-items-center"
            style="background: linear-gradient(90deg, #ff4b2b, #ff416c);
                   font-size:1.15rem; border-radius:12px 12px 0 0;">
            <i class="bi bi-info-circle me-2"></i> Detail Work Order
        </div>

        <div class="card-body p-4">

            <!-- TITLE + STATUS -->
            <div class="text-center mb-4">
                <h2 class="fw-bold" style="color:#ff416c;">
                    <?= strtoupper(safe($data['nama_mesin'])) ?>
                </h2>

                <span class="badge px-4 py-2 fw-semibold"
                    style="font-size:1rem; <?= $badgeStyle ?> border-radius:8px;">
                    <?= strtoupper($status) ?>
                </span>
            </div>

            <!-- GRID DETAIL 3 KOLOM -->
            <div class="row g-4">

                <!-- Kolom 1 -->
                <div class="col-md-4">
                    <?php
                    $left = [
                        "Judul WO" => $data['judul_wo'],
                        "Detail WO" => $data['detail_wo'],
                        "Creator" => $data['creator'],
                        "Initiator" => $data['initiator'],
                        "PIC" => $data['pic'],
                        "Note" => $data['note'],
                    ];
                    foreach ($left as $label => $value):
                    ?>
                    <div class="detail-row">
                        <strong><?= $label ?></strong>
                        <span><?= safe($value) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Kolom 2 -->
                <div class="col-md-4">
                    <?php
                    $mid = [
                        "Section" => $data['section'],
                        "Line" => $data['line'],
                        "Tipe Perbaikan" => $data['tipe'],
                        "Tanggal Temuan" => $data['tgl_temuan'],
                        "Plan Date" => $data['tgl_plan'],
                        "Plan Time" => $data['jam_plan'],
                    ];
                    foreach ($mid as $label => $value):
                    ?>
                    <div class="detail-row">
                        <strong><?= $label ?></strong>
                        <span><?= safe($value) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Kolom 3 -->
                <div class="col-md-4">
                    <?php
                    $right = [
                        "PIC 2" => $data['pic2'],
                        "PIC 3" => $data['pic3'],
                        "Person Scheduled" => $data['person_scheduled'],
                        "Person Approved" => $data['person_approved'],
                        "Person Accept" => $data['person_accept'],
                        "Person Finish" => $data['person_finish'],
                    ];
                    foreach ($right as $label => $value):
                    ?>
                    <div class="detail-row">
                        <strong><?= $label ?></strong>
                        <span><?= safe($value) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>

            </div>

            <hr class="my-4">

            <!-- FOTO -->
            <div class="row text-center">

                <div class="col-md-6">
                    <h5 class="fw-bold text-danger mb-2">Foto Before</h5>
                    <img src="../../uploads/before/<?= safe($data['fotobefore']) ?>"
                        class="img-fluid rounded shadow"
                        style="max-height:350px; object-fit:contain;">
                </div>

                <div class="col-md-6">
                    <h5 class="fw-bold text-success mb-2">Foto After</h5>
                    <img src="../../uploads/after/<?= safe($data['fotoafter']) ?>"
                        class="img-fluid rounded shadow"
                        style="max-height:350px; object-fit:contain;">
                </div>

            </div>

            <hr>

            <!-- BUTTON ACTION -->
            <?php if ($status == 'WAITING CHECKED'): ?>
            <div class="text-center mt-4">

                <button class="btn btn-success px-4 py-2 fw-semibold me-2"
                        onclick="approveWO(<?= $id ?>)">
                    <i class="fa-solid fa-check me-1"></i> FINISHED
                </button>

                <button class="btn btn-danger px-4 py-2 fw-semibold"
                        onclick="rejectWO(<?= $id ?>)">
                    <i class="fa-solid fa-xmark me-1"></i> REJECT
                </button>

            </div>
            <?php endif; ?>

            <div class="text-center mt-4">
                <a href="final_check.php" class="btn btn-secondary px-4">
                    <i class="fa-solid fa-arrow-left me-1"></i> Kembali
                </a>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function approveWO(id){
    Swal.fire({
        icon:'success',
        title:'Terima WO?',
        text:'Work Order akan ditandai FINISHED.',
        showCancelButton:true,
        confirmButtonText:'Ya, Terima',
        confirmButtonColor:'#27ae60'
    }).then((r)=>{
        if(r.isConfirmed){
            window.location='final_check_process.php?action=finish&id='+id;
        }
    });
}
function rejectWO(id){
    Swal.fire({
        icon:'warning',
        title:'Tolak WO?',
        input:'text',
        inputPlaceholder:'Alasan penolakan...',
    showCancelButton:true,
    confirmButtonText:'Tolak',
    confirmButtonColor:'#e74c3c'
    }).then((r)=>{
        if(r.isConfirmed){
            window.location='final_check_process.php?action=reject&id='+id+'&note='+r.value;
        }
    });
}
</script>

<style>
.detail-row {
    padding:6px 0;
    border-bottom:1px dashed #ddd;
    display:flex;
    justify-content:space-between;
}
.detail-row strong { color:#2c3e50; }
.detail-row span { color:#34495e; text-align:right; }
</style>

<?php include '../../includes/footer.php'; ?>
