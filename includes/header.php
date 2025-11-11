<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// === KONFIGURASI DASAR ===
// ganti "/work-order" jika folder project kamu di htdocs beda
$baseURL = '/work-order';

// === Path CSS Bootstrap Offline ===
$cssPath = $baseURL . '/assets/css/bootstrap.min.css';
$jsPath  = $baseURL . '/assets/js/bootstrap.bundle.min.js';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Work Order System</title>
  <link rel="stylesheet" href="<?= $cssPath ?>">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="<?= $baseURL ?>/work_order/index.php">Work Order</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link" href="<?= $baseURL ?>/work_order/index.php">Home</a>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="<?= $baseURL ?>/work_order/dashboard.php">Dashboard</a>
        </li>

        <?php if ($_SESSION['role'] == 'Maintenance') : ?>
          <li class="nav-item"><a class="nav-link" href="<?= $baseURL ?>/work_order/actions/add.php">Input WO</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= $baseURL ?>/work_order/maintenance/maintenance.php">My WO</a></li>
        <?php endif; ?>

        <?php if ($_SESSION['role'] == 'Foreman') : ?>
          <li class="nav-item"><a class="nav-link" href="<?= $baseURL ?>/work_order/scheduling/schedule.php">Scheduling</a></li>
        <?php endif; ?>

        <?php if ($_SESSION['role'] == 'Supervisor') : ?>
          <li class="nav-item"><a class="nav-link" href="<?= $baseURL ?>/work_order/approval/approval.php">Approval</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= $baseURL ?>/work_order/check/check.php">Check WO</a></li>
        <?php endif; ?>

        <?php if ($_SESSION['role'] == 'Ka. Dept' || $_SESSION['role'] == 'Super Administrator') : ?>
          <li class="nav-item"><a class="nav-link" href="<?= $baseURL ?>/work_order/monitoring.php">Monitoring</a></li>
        <?php endif; ?>
      </ul>

      <div class="d-flex text-white">
        <span class="me-3">👤 <?= $_SESSION['nama'] ?> (<?= $_SESSION['role'] ?>)</span>
        <a href="<?= $baseURL ?>/auth/logout.php" class="btn btn-outline-light btn-sm">Logout</a>
      </div>
    </div>
  </div>
</nav>

<div class="container mt-4">
