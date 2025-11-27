<?php
include '../../includes/session_check_flexible.php';
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

            <!-- LAYOUT -->
            <div class="row g-4">
                
                <!-- KOLOM KIRI: FOTO -->
                <div class="col-md-4">
                    <div class="mb-3">
                        <h6 class="fw-bold text-danger mb-2" style="font-size: 12px;">FOTO AFTER</h6>
                        <img src="<?= UPLOADS_AFTER_URL ?><?= safe($data['fotoafter']) ?>"
                            class="img-fluid rounded shadow zoom-img"
                            onclick="zoomImage(this.src)"
                            style="width: 100%; height: 180px; object-fit: cover;">
                    </div>

                    <div>
                        <h6 class="fw-bold text-info mb-2" style="font-size: 12px;">FOTO BEFORE</h6>
                        <img src="<?= UPLOADS_BEFORE_URL ?><?= safe($data['fotobefore']) ?>"
                            class="img-fluid rounded shadow zoom-img"
                            onclick="zoomImage(this.src)"
                            style="width: 100%; height: 180px; object-fit: cover;">
                    </div>
                </div>

                <!-- DATA -->
                <div class="col-md-8">
                    <!-- JUDUL DAN STATUS -->
                    <div class="mb-3 pb-2 border-bottom">
                        <h4 class="fw-bold" style="color:#ff416c; margin-bottom: 6px;"><?= strtoupper(safe($data['nama_mesin'])) ?></h4>
                        <span class="badge px-3 py-1 fw-semibold"
                            style="font-size:0.9rem; <?= $badgeStyle ?> border-radius:6px;">
                            <?= strtoupper($status) ?>
                        </span>
                    </div>

                    <!-- REJECT NOTE -->
                    <?php if ($status === 'REJECTED' && !empty($data['reject_note'])): ?>
                        <div class="alert alert-danger mb-3" style="border-radius: 8px; border-left: 4px solid #c0392b;">
                            <h6 class="fw-bold mb-2" style="color: #c0392b;">
                                <i class="bi bi-exclamation-circle me-2"></i> Alasan Penolakan
                            </h6>
                            <p class="mb-0"><?= safe($data['reject_note']) ?></p>
                        </div>
                    <?php endif; ?>

                    <!-- DATA -->
                    <div class="row g-2">
                        <!-- KOLOM 1 -->
                        <div class="col-md-6 border-end">
                            <?php
                            $left = [
                                "Judul WO" => $data['judul_wo'],
                                "Detail WO" => $data['detail_wo'],
                                "Creator" => $data['creator'],
                                "Initiator" => $data['initiator'],
                                "Dept" => $data['dept'],
                                "Line" => $data['line'],
                            ];

                            foreach ($left as $label => $value):
                            ?>
                                <div class="detail-item">
                                    <strong><?= $label ?></strong>
                                    <span><?= safe($value) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- KOLOM 2 -->
                        <div class="col-md-6">
                            <?php
                            $right = [
                                "Tipe Perbaikan" => $data['tipe'],
                                "Tanggal Temuan" => $data['tgl_temuan'],
                                "Plan Date" => $data['tgl_plan'],
                                "Plan Time" => $data['jam_plan'],
                                "PIC" => $data['pic'],
                                "PIC 2" => $data['pic2'],
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
                    <div class="row g-2 mt-2">
                        <div class="col-md-6 border-end">
                            <?php
                            $approval = [
                                "Person Scheduled" => $data['person_scheduled'],
                                "Person Approved" => $data['person_approved'],
                            ];

                            foreach ($approval as $label => $value):
                            ?>
                                <div class="detail-item">
                                    <strong><?= $label ?></strong>
                                    <span><?= safe($value) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="col-md-6">
                            <?php
                            $finish = [
                                "Person Accept" => $data['person_accept'],
                                "Person Finish" => $data['person_finish'],
                            ];

                            foreach ($finish as $label => $value):
                            ?>
                                <div class="detail-item">
                                    <strong><?= $label ?></strong>
                                    <span><?= safe($value) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

            </div>

            <div class="text-end mt-4">
                <a href="../index.php" class="btn btn-secondary px-4">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
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
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    border-bottom: 1px solid #f0f0f0;
    padding: 5px 0;
    font-size: 13px;
}

.detail-item strong {
    color: #2c3e50;
    font-weight: 600;
    flex: 0 0 40%;
}

.detail-item span {
    color: #555;
    text-align: right;
    flex: 1;
    word-break: break-word;
    font-size: 12px;
}

/* Garis pembatas vertikal di tengah */
.col-md-6.border-end {
    border-right: 2px solid #ddd !important;
    padding-right: 12px;
}

.col-md-6 {
    padding-left: 12px;
}

/* Foto */
.zoom-img {
    cursor: zoom-in;
    transition: 0.3s;
    border: 2px solid #f0f0f0;
}

.zoom-img:hover {
    transform: scale(1.02);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

/* Modal Fullscreen */
.img-modal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.9);
    text-align: center;
    padding-top: 40px;
}

.img-modal-content {
    max-width: 90%;
    max-height: 90%;
    border-radius: 8px;
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
