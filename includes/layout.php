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
  <!-- Bootstrap Icons - Local (Offline Support) -->
  <link rel="stylesheet" href="<?= $basePath ?>assets/css/bootstrap-icons.css">
  <style>
    /* Fallback icons jika local file juga tidak available */
    .bi:not([class*="bi-"]) {
      content: "◆";
    }
  </style>

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
      left: 240px;
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

    /* === PROFILE DROPDOWN === */
    .profile-dropdown {
      position: absolute;
      top: 100%;
      right: 0;
      background-color: white;
      border: 1px solid #ddd;
      border-radius: 6px;
      min-width: 180px;
      margin-top: 5px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      z-index: 3000;
    }

    .profile-dropdown .dropdown-item {
      display: flex;
      align-items: center;
      padding: 12px 16px;
      color: #333;
      text-decoration: none;
      transition: 0.2s;
      border: none;
      background: none;
      width: 100%;
      text-align: left;
      font-size: 14px;
    }

    .profile-dropdown .dropdown-item:hover {
      background-color: #f5f5f5;
      color: #d62828;
    }

    .profile-dropdown .dropdown-item i {
      margin-right: 10px;
      font-size: 16px;
    }

    /* === SIDEBAR === */
    .sidebar {
      width: 240px;
      background-color: var(--sidebar-bg);
      position: fixed;
      top: 0;
      bottom: 0;
      left: 0;
      border-right: 1px solid #e0e0e0;
      box-shadow: var(--shadow);
      overflow-y: auto;
      transition: 0.3s;
      z-index: 1500;
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
      margin-top: 60px;
      padding: 20px;
    }

    /* === RESPONSIVE === */
    @media (max-width: 992px) {
      .topbar {
        left: 0;
      }
      
      .sidebar {
        top: 0;
        left: -240px;
      }
      
      .sidebar.active {
        left: 0;
      }
      
      .main {
        margin-left: 0;
        margin-top: 80px;
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
  <div class="user-info position-relative">
    <div class="d-flex align-items-center" style="cursor: pointer;" onclick="toggleProfileDropdown(event)">
      <i class="bi bi-person-circle"></i>
      <span><?= htmlspecialchars($nama) ?> (<?= $role ?>)</span>
    </div>
    
    <!-- Profile Dropdown -->
    <div id="profileDropdown" class="profile-dropdown" style="display: none;">
      <a href="#" onclick="confirmLogout(event)" class="dropdown-item">
        <i class="bi bi-box-arrow-right"></i> Logout
      </a>
    </div>
  </div>
</div>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">
  <?php include $basePath . 'includes/sidebar.php'; ?>
</div>

<!-- MAIN CONTENT -->
<div class="main">

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= $basePath ?>assets/js/bootstrap.bundle.min.js"></script>

<script>
// Toggle Profile Dropdown
function toggleProfileDropdown(event) {
    event.preventDefault();
    const dropdown = document.getElementById('profileDropdown');
    if (dropdown.style.display === 'none') {
        dropdown.style.display = 'block';
    } else {
        dropdown.style.display = 'none';
    }
}

// Close dropdown ketika klik di luar
document.addEventListener('click', function(event) {
    const userInfo = document.querySelector('.user-info');
    const dropdown = document.getElementById('profileDropdown');
    
    if (!userInfo.contains(event.target) && dropdown.style.display === 'block') {
        dropdown.style.display = 'none';
    }
});

// Confirm Logout dengan SweetAlert
function confirmLogout(event) {
    event.preventDefault();
    
    Swal.fire({
        title: 'Yakin Logout?',
        text: 'Anda akan keluar dari sistem.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Logout',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#d62828',
        cancelButtonColor: '#6c757d'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '<?= $basePath ?>auth/logout.php';
        }
    });
}
</script>
