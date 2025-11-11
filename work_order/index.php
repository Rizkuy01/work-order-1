<?php
include '../includes/session_check.php';
include '../config/database.php';
include '../includes/layout.php';

// ====== FILTER DAN PENCARIAN ======
$search = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$sort = $_GET['sort'] ?? 'tgl_input';
$order = $_GET['order'] ?? 'DESC';

// ✅ Tambahan: Filter tanggal
$fromDate = $_GET['from_date'] ?? '';
$toDate   = $_GET['to_date'] ?? '';

// ====== Pagination ======
$limit = 10;
$page = isset($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// ====== Query dasar ======
$where = "WHERE 1=1";

// 🔍 Pencarian kata kunci
if (!empty($search)) {
  $safeSearch = mysqli_real_escape_string($conn, $search);
  $where .= " AND (nama_mesin LIKE '%$safeSearch%' OR judul_wo LIKE '%$safeSearch%')";
}

// 🔍 Filter status
if (!empty($statusFilter)) {
  $safeStatus = mysqli_real_escape_string($conn, $statusFilter);
  $where .= " AND status = '$safeStatus'";
}

// ✅ Filter tanggal (antara from_date dan to_date)
if (!empty($fromDate) && !empty($toDate)) {
  $where .= " AND DATE(tgl_input) BETWEEN '$fromDate' AND '$toDate'";
} elseif (!empty($fromDate)) {
  $where .= " AND DATE(tgl_input) >= '$fromDate'";
} elseif (!empty($toDate)) {
  $where .= " AND DATE(tgl_input) <= '$toDate'";
}

// ====== Hitung total data ======
$countQuery = "SELECT COUNT(*) AS total FROM work_order $where";
$countResult = mysqli_query($conn, $countQuery);
$totalRows = mysqli_fetch_assoc($countResult)['total'] ?? 0;
$totalPages = ceil($totalRows / $limit);

// ====== Query utama ======
$query = "SELECT * FROM work_order $where ORDER BY $sort $order LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);
?>

<div class="container-fluid px-4 mt-4">

  <!-- ✅ Tombol di atas card -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <a href="export_excel.php?search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>&from_date=<?= urlencode($fromDate) ?>&to_date=<?= urlencode($toDate) ?>"
        class="btn btn-gradient-excel shadow-sm me-2">
        <i class="bi bi-file-earmark-excel"></i> Export Excel
      </a>

      <a href="actions/add.php" class="btn btn-gradient-add shadow-sm">
        <i class="bi bi-plus-circle"></i> Tambah Work Order
      </a>
    </div>
  </div>

  <div class="card shadow border-0">
    <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
      <h5 class="mb-0"><i class="bi bi-list-task me-2"></i> Daftar Work Order</h5>
    </div>

    <div class="card-body bg-white">
      <!-- 🔎 Filter dan Pencarian -->
      <form method="GET" class="row g-2 mb-3">
        <div class="col-md-3">
          <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" class="form-control" placeholder="Cari nama mesin atau judul WO...">
        </div>

        <div class="col-md-2">
          <input type="date" name="from_date" value="<?= htmlspecialchars($fromDate) ?>" class="form-control" placeholder="Dari tanggal">
        </div>
        <div class="col-md-2">
          <input type="date" name="to_date" value="<?= htmlspecialchars($toDate) ?>" class="form-control" placeholder="Sampai tanggal">
        </div>

        <div class="col-md-3">
          <select name="status" class="form-select">
            <option value="">Semua Status</option>
            <option value="WAITING SCHEDULE" <?= $statusFilter == 'WAITING SCHEDULE' ? 'selected' : '' ?>>Waiting Schedule</option>
            <option value="WAITING APPROVAL" <?= $statusFilter == 'WAITING APPROVAL' ? 'selected' : '' ?>>Waiting Approval</option>
            <option value="OPENED" <?= $statusFilter == 'OPENED' ? 'selected' : '' ?>>Opened</option>
            <option value="ON PROGRESS" <?= $statusFilter == 'ON PROGRESS' ? 'selected' : '' ?>>On Progress</option>
            <option value="WAITING CHECKED" <?= $statusFilter == 'WAITING CHECKED' ? 'selected' : '' ?>>Waiting Checked</option>
            <option value="FINISHED" <?= $statusFilter == 'FINISHED' ? 'selected' : '' ?>>Finished</option>
            <option value="REJECTED" <?= $statusFilter == 'REJECTED' ? 'selected' : '' ?>>Rejected</option>
          </select>
        </div>

        <div class="col-md-2 d-grid">
          <button class="btn btn-danger"><i class="bi bi-funnel me-1"></i> Filter</button>
        </div>
      </form>

      <!-- 🧾 Tabel Data -->
      <div class="table-responsive">
        <table class="table table-hover align-middle text-center">
          <thead class="table-light">
            <tr>
              <th><a href="?sort=nama_mesin&order=<?= $order == 'ASC' ? 'DESC' : 'ASC' ?>">Nama Mesin</a></th>
              <th><a href="?sort=judul_wo&order=<?= $order == 'ASC' ? 'DESC' : 'ASC' ?>">Judul WO</a></th>
              <th><a href="?sort=tgl_input&order=<?= $order == 'ASC' ? 'DESC' : 'ASC' ?>">Tanggal Input</a></th>
              <th>Status</th>
              <th width="120">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (mysqli_num_rows($result) > 0): ?>
              <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                  <td class="fw-semibold"><?= htmlspecialchars($row['nama_mesin']) ?></td>
                  <td><?= htmlspecialchars($row['judul_wo']) ?></td>
                  <td><?= date('d M Y H:i', strtotime($row['tgl_input'])) ?></td>
                  <td>
                    <?php
                      $status = htmlspecialchars($row['status']);
                      $badgeStyle = match($status) {
                        'WAITING SCHEDULE' => 'background: linear-gradient(135deg, #f1c40f, #f39c12); color: white;',
                        'WAITING APPROVAL' => 'background: linear-gradient(135deg, #e39eff, #8e44ad); color: white;',
                        'OPENED'           => 'background: linear-gradient(135deg, #b5c1c2, #636e72); color: white;',
                        'ON PROGRESS'      => 'background: linear-gradient(135deg, #fb963d, #d35400); color: white;',
                        'WAITING CHECKED'  => 'background: linear-gradient(135deg, #59ccfe, #086bff); color: white;',
                        'FINISHED'         => 'background: linear-gradient(135deg, #5ce894, #23d23a); color: white;',
                        'REJECTED'         => 'background: linear-gradient(135deg, #ff7363, #c0392b); color: white;',
                        default            => 'background: #dcdcdc; color: #333;',
                      };
                    ?>
                    <span class="badge px-3 py-2 fw-semibold" style="<?= $badgeStyle ?> box-shadow: 0 2px 4px rgba(0,0,0,0.15); border-radius: 8px;">
                      <?= strtoupper($status) ?>
                    </span>
                  </td>
                  <td>
                    <a href="actions/edit.php?id=<?= $row['id_work_order'] ?>" class="btn btn-sm btn-outline-primary me-1">
                      <i class="bi bi-pencil-square"></i>
                    </a>
                    <a href="actions/delete.php?id=<?= $row['id_work_order'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Yakin hapus data ini?')">
                      <i class="bi bi-trash3"></i>
                    </a>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr><td colspan="5" class="text-muted py-3">Tidak ada data ditemukan untuk filter yang dipilih.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- 📄 Pagination -->
      <?php if ($totalPages > 1): ?>
      <nav class="d-flex justify-content-center mt-4">
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
            <a class="page-link"
              href="?page=<?= max(1, $page - 1) ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>&from_date=<?= urlencode($fromDate) ?>&to_date=<?= urlencode($toDate) ?>&sort=<?= $sort ?>&order=<?= $order ?>">
              &laquo; Prev
            </a>
          </li>

          <?php
            $startPage = max(1, $page - 2);
            $endPage = min($totalPages, $page + 2);
            for ($i = $startPage; $i <= $endPage; $i++):
          ?>
            <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
              <a class="page-link"
                href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>&from_date=<?= urlencode($fromDate) ?>&to_date=<?= urlencode($toDate) ?>&sort=<?= $sort ?>&order=<?= $order ?>">
                <?= $i ?>
              </a>
            </li>
          <?php endfor; ?>

          <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
            <a class="page-link"
              href="?page=<?= min($totalPages, $page + 1) ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>&from_date=<?= urlencode($fromDate) ?>&to_date=<?= urlencode($toDate) ?>&sort=<?= $sort ?>&order=<?= $order ?>">
              Next &raquo;
            </a>
          </li>
        </ul>
      </nav>
      <?php endif; ?>
    </div>
  </div>
</div>



<style>
  .card { border-radius: 12px; }
  .card-header { border-top-left-radius: 12px; border-top-right-radius: 12px; }
  .table th a { color: inherit; text-decoration: none; }
  .table th a:hover { color: #dc3545; }
  .table-hover tbody tr:hover { background-color: #f8f9ff; }
  .badge { transition: transform 0.2s ease, box-shadow 0.2s ease; }
  .badge:hover { transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0,0,0,0.2); }
  .pagination .page-link {
    border-radius: 6px; padding: 6px 12px;
    font-weight: 500; color: #dc3545;
  }
  .pagination .page-item.active .page-link {
    background-color: #dc3545; border-color: #dc3545; color: white;
  }
  .pagination .page-item.disabled .page-link {
    color: #aaa; background-color: #f5f5f5; pointer-events: none;
  }

  /* 🌈 Header Card Gradasi Merah (KYB Style) */
  .card-header {
    background: linear-gradient(135deg, #ff4b2b, #ff416c);
    color: white;
    border: none;
    font-weight: 600;
    letter-spacing: 0.3px;
    box-shadow: 0 3px 6px rgba(255, 65, 108, 0.3);
  }

  /* Hover/efek kecil kalau mau nanti pakai tombol di header */
  .card-header:hover {
    background: linear-gradient(135deg, #ff6b81, #ff1e56);
  }

  /* Ikon di dalam header */
  .card-header i {
    margin-right: 6px;
  }


  /* 🌈 Tombol Gradasi Export Excel */
  .btn-gradient-excel {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    border: none;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 3px 6px rgba(40, 167, 69, 0.3);
  }
  .btn-gradient-excel:hover {
    background: linear-gradient(135deg, #20c997, #198754);
    transform: translateY(-2px);
    box-shadow: 0 5px 10px rgba(25, 135, 84, 0.4);
  }

  /* 🌈 Tombol Gradasi Tambah Work Order */
  .btn-gradient-add {
    background: linear-gradient(135deg, #ff4b2b, #ff416c);
    color: white;
    border: none;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 3px 6px rgba(255, 65, 108, 0.3);
  }
  .btn-gradient-add:hover {
    background: linear-gradient(135deg, #ff6b81, #ff1e56);
    transform: translateY(-2px);
    box-shadow: 0 5px 10px rgba(255, 30, 86, 0.4);
  }

  /* 🪶 Efek tombol biar lebih rapi */
  .btn-gradient-excel i,
  .btn-gradient-add i {
    margin-right: 6px;
  }
</style>

<?php include '../includes/footer.php'; ?>
