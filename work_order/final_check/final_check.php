<?php
include '../../includes/session_check.php';
include '../../includes/role_check.php';
only(['Supervisor', 'Super Administrator']);

include '../../includes/layout.php';
include '../../config/database.php';

/* ===============================
   PAGINATION CONFIG
================================*/
$limit = 10;
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

/* ===============================
   SEARCH QUERY
================================*/
$search = $_GET['search'] ?? "";

$filter = "";
if (!empty($search)) {
    $search_safe = mysqli_real_escape_string($conn, $search);
    $filter = " AND (judul_wo LIKE '%$search_safe%' OR nama_mesin LIKE '%$search_safe%' OR person_finish LIKE '%$search_safe%')";
}

/* ===============================
   GET DATA + COUNT DATA
================================*/
$query = "
    SELECT * FROM work_order 
    WHERE status = 'WAITING CHECKED'
    $filter
    ORDER BY tgl_input DESC
    LIMIT $start, $limit
";
$result = mysqli_query($conn, $query);

$countResult = mysqli_query($conn, "
    SELECT COUNT(*) AS total FROM work_order 
    WHERE status = 'WAITING CHECKED' $filter
");
$total = mysqli_fetch_assoc($countResult)['total'];
$total_pages = ceil($total / $limit);

/* Function trim judul */
function trimTitle($text) {
    return strlen($text) > 28 ? substr($text, 0, 28) . "..." : $text;
}
?>

<div class="container-fluid px-4 py-3">

    <!-- CARD -->
    <div class="card shadow-lg border-0 rounded-4">

        <!-- HEADER -->
        <div class="card-header text-white fw-semibold rounded-top-4"
             style="background: linear-gradient(90deg, #ff4b2b, #ff416c); font-size: 1.2rem;">
            <i class="bi bi-check2-all me-2"></i> Final Checking Work Order
        </div>

        <div class="card-body bg-white px-4 py-4">

            <!-- SEARCH BAR -->
            <form method="GET" class="mb-3">
                <div class="input-group">
                    <input type="text" name="search" class="form-control shadow-sm"
                           placeholder="Cari nama mesin / judul WO / PIC..."
                           value="<?= htmlspecialchars($search) ?>">
                    <button class="btn btn-danger" type="submit">
                        <i class="bi bi-search"></i> Cari
                    </button>
                </div>
            </form>

            <!-- TABLE -->
            <div class="table-responsive">
                <table class="table table-borderless modern-table text-center align-middle">
                    <thead>
                        <tr>
                            <th>Nama Mesin</th>
                            <th>Judul WO</th>
                            <th>PIC</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td class="fw-semibold text-danger"><?= htmlspecialchars($row['nama_mesin']) ?></td>

                                <td><?= htmlspecialchars(trimTitle($row['judul_wo'])) ?></td>

                                <td>
                                    <span class="px-3 py-2">
                                        <?= htmlspecialchars($row['person_finish'] ?? '-') ?>
                                    </span>
                                </td>

                                <td>
                                    <a href="final_check_detail.php?id=<?= $row['id_work_order'] ?>" class="btn btn-info btn-sm text-white btn-detail">
                                        <i class="bi bi-eye me-1"></i>Detail
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>

                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="py-4">
                                <div class="empty-box">
                                    <i class="bi bi-box-open empty-icon"></i>
                                    <p class="empty-text">Tidak ada Work Order menunggu pengecekan.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->
            <div class="d-flex justify-content-center mt-3">
                <nav>
                    <ul class="pagination">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= $search ?>">Sebelumnya</a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>&search=<?= $search ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= $search ?>">Berikutnya</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>

        </div>
    </div>
</div>

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


<style>
/* Table */
.modern-table thead tr {
    background: #f8f9fc;
    border-bottom: 2px solid #eee;
    font-weight: 700;
    color: #444;
}

.modern-table tbody tr:hover {
    background: #fff2f2;
    transform: scale(1.01);
}

/* Button Detail */
.btn-detail {
    background: #0dcaf0;
    color: #fff;
    border: none;
    padding: 6px 14px;
    font-weight: 600;
    border-radius: 8px;
    transition: .2s;
    text-decoration: none !important;
}
.btn-detail:hover {
    background: #0bb9db;
    transform: translateY(-2px);
    box-shadow: 0 3px 8px rgba(0,0,0,0.15);
}

/* Empty State */
.empty-box {
    padding: 30px;
    border-radius: 12px;
    border: 1px dashed #ddd;
    background: #fafbfd;
}
.empty-icon {
    font-size: 40px;
    color: #c0392b;
}
.empty-text {
    font-size: 1.1rem;
    font-weight: 600;
    color: #777;
}
.pagination .page-link {
    border-radius: 6px;
    margin: 0 2px;
    color: #c0392b;
    border-color: #f5c6cb;
  }
  .pagination .page-item.active .page-link {
    background: linear-gradient(135deg, #ff4b2b, #ff416c);
    color: white;
    border: none;
  }
</style>

<?php include '../../includes/footer.php'; ?>
