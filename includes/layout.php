<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$role = $_SESSION['role'] ?? '';
$nama = $_SESSION['nama'] ?? '';

// 🧭 Deteksi path otomatis
$basePath = '../';
if (strpos($_SERVER['PHP_SELF'], '/work_order/actions/') !== false ||
    strpos($_SERVER['PHP_SELF'], '/work_order/maintenance/') !== false ||
    strpos($_SERVER['PHP_SELF'], '/work_order/final_check/') !== false ||
    strpos($_SERVER['PHP_SELF'], '/work_order/approval/') !== false ||
    strpos($_SERVER['PHP_SELF'], '/work_order/check/') !== false ||
    strpos($_SERVER['PHP_SELF'], '/work_order/scheduling/') !== false) {
  $basePath = '../../';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Work Order System - KYB</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="<?= $basePath ?>assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

  <style>
    :root {
      --primary-color: #0a2351;
      --kyb-red: #d62828;
      --sidebar-bg: #ffffff;
      --hover-bg: #f1f5ff;
      --text-color: #333;
      --shadow: 0 4px 8px rgba(0,0,0,0.05);
    }

    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f6f8fb;
      margin: 0;
    }

    /* === TOPBAR === */
    .topbar {
      height: 60px;
      background-color: var(--primary-color);
      color: white;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 25px;
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      z-index: 2000;
      box-shadow: var(--shadow);
    }

    .topbar .logo img {
      height: 40px;
    }

    .user-info {
      font-weight: 500;
      display: flex;
      align-items: center;
    }

    .user-info i {
      font-size: 20px;
      margin-right: 8px;
    }

    /* === SIDEBAR === */
    .sidebar {
      width: 240px;
      background-color: var(--sidebar-bg);
      position: fixed;
      top: 60px;
      bottom: 0;
      left: 0;
      border-right: 1px solid #e0e0e0;
      box-shadow: var(--shadow);
      overflow-y: auto;
      transition: 0.3s;
      z-index: 1000;
    }

    .brand {
      text-align: center;
      padding: 20px 0;
      border-bottom: 1px solid #e9ecef;
    }

    .brand img {
      height: 60px;
      object-fit: contain;
    }

    .sidebar a {
      display: flex;
      align-items: center;
      color: var(--text-color);
      padding: 10px 20px;
      text-decoration: none;
      font-weight: 500;
      border-left: 4px solid transparent;
      transition: 0.2s;
    }

    .sidebar a i {
      font-size: 18px;
      margin-right: 10px;
      color: #007bff;
    }

    .sidebar a:hover,
    .sidebar a.active {
      background-color: var(--hover-bg);
      border-left: 4px solid #007bff;
      color: #007bff;
    }

    /* === MAIN === */
    .main {
      margin-left: 240px;
      margin-top: 80px;
      padding: 20px;
    }

    /* === RESPONSIVE === */
    @media (max-width: 992px) {
      .sidebar {
        left: -240px;
      }
      .sidebar.active {
        left: 0;
      }
      .main {
        margin-left: 0;
      }
      .toggle-btn {
        display: inline-block;
        cursor: pointer;
        margin-right: 15px;
      }
    }

    .toggle-btn {
      display: none;
      font-size: 22px;
    }

    ::-webkit-scrollbar { width: 6px; }
    ::-webkit-scrollbar-thumb { background: #ccc; border-radius: 4px; }
  </style>
</head>
<body>

<!-- TOPBAR -->
<div class="topbar">
  <div class="d-flex align-items-center">
    <span class="toggle-btn text-white me-3" onclick="toggleSidebar()"><i class="bi bi-list"></i></span>
    <div class="logo d-flex align-items-center">
      <span class="ms-2 fw-semibold">Work Order System</span>
    </div>
  </div>
  <div class="user-info">
    <i class="bi bi-person-circle"></i><?= htmlspecialchars($nama) ?> (<?= $role ?>)
  </div>
</div>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">
  <?php include $basePath . 'includes/sidebar.php'; ?>
</div>

<!-- MAIN CONTENT -->
<div class="main">
