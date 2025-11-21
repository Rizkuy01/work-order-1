<?php
include '../../includes/session_check.php';
include '../../config/database.php';
include '../../config/upload_config.php';
include '../../includes/layout.php';

$id = $_GET['id'] ?? 0;

// Ambil data WO
$q = mysqli_query($conn, "SELECT * FROM work_order WHERE id_work_order=$id");
$data = mysqli_fetch_assoc($q);

if (!$data) {
    echo "<div class='alert alert-danger m-4'>Data tidak ditemukan.</div>";
    include '../../includes/footer.php';
    exit;
}

function safe($v) {
    return htmlspecialchars($v ?? '-', ENT_QUOTES, 'UTF-8');
}

$status = $data['status'];

$badgeStyle = match($status) {
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

<div class="container-fluid px-4 py-3">

    <div class="card shadow border-0" style="border-radius:12px;">

        <!-- HEADER -->
        <div class="card-header text-white fw-semibold"
            style="background: linear-gradient(90deg,#ff4b2b,#ff416c); font-size:1.15rem;">
            <i class="bi bi-info-circle me-2"></i> Detail Work Order
        </div>

        <div class="card-body p-4">

            <!-- ⭐ Highlight -->
            <div class="text-center mb-4">
                <h3 class="fw-bold" style="color:#ff416c"><?= strtoupper(safe($data['nama_mesin'])) ?></h3>

                <span class="badge px-4 py-2 fw-semibold"
                    style="font-size:1rem; <?= $badgeStyle ?> border-radius:8px;">
                    <?= strtoupper($status) ?>
                </span>
            </div>

            <!-- GRID 3 KOLOM -->
            <div class="row g-3">

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
                        <div class="detail-item">
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
                        <div class="detail-item">
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
                        // "Reject Note" => $data['reject_note'],
                    ];

                    foreach ($right as $label => $value):
                    ?>
                        <div class="detail-item">
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
                    <h6 class="fw-bold text-danger">Foto Before</h6>
                    <img src="<?= UPLOADS_BEFORE_URL ?><?= safe($data['fotobefore']) ?>"
                        class="img-fluid rounded shadow zoom-img"
                        onclick="zoomImage(this.src)">
                </div>

                <div class="col-md-6">
                    <h6 class="fw-bold text-success">Foto After</h6>
                    <img src="<?= UPLOADS_AFTER_URL ?><?= safe($data['fotoafter']) ?>"
                        class="img-fluid rounded shadow zoom-img"
                        onclick="zoomImage(this.src)">
                </div>
            </div>

            <div class="text-end mt-4">
                <a href="../index.php" class="btn btn-secondary px-4">
                    <i class="fa-solid fa-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </div>
    </div>
</div>

<!-- FULLSCREEN IMAGE MODAL -->
<div id="imgModal" class="img-modal" onclick="closeModal()">
    <img id="modalImg" class="img-modal-content">
</div>

<style>
.detail-item {
    display:flex;
    justify-content:space-between;
    border-bottom:1px dashed #ddd;
    padding:6px 0;
}
.detail-item strong {
    width:50%;
    color:#2c3e50;
}
.detail-item span {
    width:50%;
    text-align:right;
    color:#34495e;
}

/* Foto */
.zoom-img {
    max-height: 250px;
    object-fit: cover;
    cursor: zoom-in;
    transition: 0.3s;
}
.zoom-img:hover { transform: scale(1.03); }

/* Modal Fullscreen */
.img-modal {
    display:none;
    position:fixed;
    z-index:9999;
    left:0; top:0;
    width:100%; height:100%;
    background:rgba(0,0,0,0.8);
    text-align:center;
    padding-top:40px;
}
.img-modal-content {
    max-width:90%;
    max-height:90%;
    border-radius:8px;
}
</style>

<script>
function zoomImage(src) {
    document.getElementById('modalImg').src = src;
    document.getElementById('imgModal').style.display = 'block';
}
function closeModal() {
    document.getElementById('imgModal').style.display = 'none';
}
</script>

<?php include '../../includes/footer.php'; ?>
