<?php
$current_page = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['role'] ?? '';
?>

<div class="brand text-center mt-2 mb-3">
  <img src="<?= $basePath ?>assets/img/logo-kyb.png" style="height: 40px; width: auto;" alt="KYB Logo">
</div>

<ul class="nav flex-column mt-2">
  <!-- Dashboard & Data Work Order (Common for All) -->
  <li class="menu-group">
    <span class="group-label">General</span>
    <a href="<?= $basePath ?>work_order/dashboard.php" class="<?= $current_page=='dashboard.php'?'active':'' ?>">
      <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="<?= $basePath ?>work_order/index.php" class="<?= $current_page=='index.php'?'active':'' ?>">
      <i class="bi bi-list"></i> Data Work Order
    </a>
  </li>

  <!-- Maintenance Role -->
  <?php if (in_array($role, ['Maintenance', 'Super Administrator'])): ?>
  <li class="menu-group">
    <span class="group-label">Maintenance</span>
    <a href="<?= $basePath ?>work_order/actions/add.php" class="<?= $current_page=='add.php'?'active':'' ?>"><i class="bi bi-plus-circle"></i> Input WO</a>
    <a href="<?= $basePath ?>work_order/maintenance/maintenance.php" class="<?= $current_page=='maintenance.php'?'active':'' ?>"><i class="bi bi-tools"></i> My WO</a>
  </li>
  <?php endif; ?>

  <!-- Foreman Role -->
  <?php if (in_array($role, ['Foreman', 'Super Administrator'])): ?>
  <li class="menu-group">
    <span class="group-label">Foreman</span>
    <a href="<?= $basePath ?>work_order/scheduling/schedule.php" class="<?= $current_page=='schedule.php'?'active':'' ?>"><i class="bi bi-calendar-event"></i> Scheduling</a>
  </li>
  <?php endif; ?>

  <!-- Supervisor Role -->
  <?php if (in_array($role, ['Supervisor', 'Super Administrator'])): ?>
  <li class="menu-group">
    <span class="group-label">Supervisor</span>
    <a href="<?= $basePath ?>work_order/approval/approval.php" class="<?= $current_page=='approval.php'?'active':'' ?>"><i class="bi bi-check2-circle"></i> Approval</a>
    <a href="<?= $basePath ?>work_order/final_check/final_check.php" class="<?= $current_page=='final_check.php'?'active':'' ?>"><i class="bi bi-list-check"></i> Final Checking</a>
  </li>
  <?php endif; ?>

  <!-- Monitoring -->
  <!-- <?php if (in_array($role, ['Kepala Departemen', 'Super Administrator'])): ?>
  <li class="menu-group">
    <span class="group-label">Monitoring</span>
    <a href="<?= $basePath ?>work_order/monitoring.php" class="<?= $current_page=='monitoring.php'?'active':'' ?>"><i class="bi bi-graph-up"></i> Monitoring</a>
  </li>
  <?php endif; ?> -->
</ul>


<style>
/* ===== MENU GROUP STYLING ===== */
.menu-group {
  list-style: none;
  padding: 0;
  margin: 15px 0 0 0;
  border-top: 1px solid #e0e0e0;
  padding-top: 8px;
}

.menu-group:first-of-type {
  border-top: none;
  margin-top: 0;
  padding-top: 0;
}

.group-label {
  display: block;
  font-size: 11px;
  font-weight: 700;
  color: #999;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  padding: 0 15px 8px 15px;
  margin: 0;
}

.menu-group a {
  display: flex;
  align-items: center;
  color: #333;
  padding: 10px 15px;
  text-decoration: none;
  font-weight: 500;
  border-left: 4px solid transparent;
  transition: all 0.25s ease-in-out;
  font-size: 14px;
}

.sidebar i.bi {
  color: #333 !important; 
  transition: color 0.25s ease-in-out;
  margin-right: 8px;
  font-size: 16px;
}

.menu-group a:hover i.bi {
  color: #c0392b !important;
}

.menu-group a.active i.bi {
  color: #c0392b !important;
}

.menu-group a:hover,
.menu-group a.active {
  color: #c0392b !important;
  background-color: rgba(192, 57, 43, 0.08);
}

.menu-group a.active {
  background-color: rgba(192, 57, 43, 0.12);
  border-left: 4px solid #c0392b;
  font-weight: 600;
}

</style>