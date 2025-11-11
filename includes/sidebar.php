<?php
$current_page = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['role'] ?? '';
?>

<div class="brand text-center mt-2 mb-3">
  <img src="<?= $basePath ?>assets/img/logo-kyb.png" style="height: 40px; width: auto;" alt="KYB Logo">
</div>

<ul class="nav flex-column mt-2">
  <!-- Dashboard -->
  <li>
    <a href="<?= $basePath ?>work_order/dashboard.php" class="<?= $current_page=='dashboard.php'?'active':'' ?>">
      <i class="bi bi-speedometer2"></i> Dashboard
    </a>
  </li>

  <!-- Data Work Order -->
  <li>
    <a href="<?= $basePath ?>work_order/index.php" class="<?= $current_page=='index.php'?'active':'' ?>">
      <i class="bi bi-list"></i> Data Work Order
    </a>
  </li>

  <!-- Maintenance -->
  <?php if (in_array($role, ['Maintenance', 'Super Administrator'])): ?>
    <li><a href="<?= $basePath ?>work_order/actions/add.php" class="<?= $current_page=='add.php'?'active':'' ?>"><i class="bi bi-plus-circle"></i> Input WO</a></li>
    <li><a href="<?= $basePath ?>work_order/maintenance/maintenance.php" class="<?= $current_page=='maintenance.php'?'active':'' ?>"><i class="bi bi-tools"></i> My WO</a></li>
  <?php endif; ?>

  <!-- Foreman -->
  <?php if (in_array($role, ['Foreman', 'Super Administrator'])): ?>
    <li><a href="<?= $basePath ?>work_order/scheduling/schedule.php" class="<?= $current_page=='schedule.php'?'active':'' ?>"><i class="bi bi-calendar-event"></i> Scheduling</a></li>
  <?php endif; ?>

  <!-- Supervisor -->
  <?php if (in_array($role, ['Supervisor', 'Super Administrator'])): ?>
    <li><a href="<?= $basePath ?>work_order/approval/approval.php" class="<?= $current_page=='approval.php'?'active':'' ?>"><i class="bi bi-check2-circle"></i> Approval</a></li>
  <?php endif; ?>

  <!-- Monitoring -->
  <!-- <?php if (in_array($role, ['Ka. Dept', 'Super Administrator'])): ?>
    <li><a href="<?= $basePath ?>work_order/monitoring.php" class="<?= $current_page=='monitoring.php'?'active':'' ?>"><i class="bi bi-graph-up"></i> Monitoring</a></li>
  <?php endif; ?> -->

  <hr>

  <!-- Logout -->
  <li>
    <a href="<?= $basePath ?>auth/logout.php" class="text-danger">
      <i class="bi bi-box-arrow-right"></i> Logout
    </a>
  </li>
</ul>

<style>
  /* === Warna umum ikon sidebar === */
.sidebar i.bi {
  color: #333 !important; /* Warna default (gelap) */
  transition: color 0.25s ease-in-out;
}

/* Saat hover: merah */
.sidebar a:hover i.bi {
  color: #c0392b !important; /* Merah KYB */
}

/* Saat menu aktif: merah juga */
.sidebar a.active i.bi {
  color: #c0392b !important; /* Merah KYB */
}

/* Warna teks aktif dan hover */
.sidebar a.active,
.sidebar a:hover {
  color: #c0392b !important;
}

/* (Tambahan agar border kiri aktif juga merah) */
.sidebar a.active {
  background-color: rgba(192, 57, 43, 0.1);
  border-left: 4px solid #c0392b;
  font-weight: 600;
}

</style>