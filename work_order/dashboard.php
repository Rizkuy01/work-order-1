<?php
include '../includes/session_check.php';
include '../config/database.php';

$role = $_SESSION['role'];
$nama = $_SESSION['nama'];

// Statistik WO
$totalWO         = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM work_order"))['total'] ?? 0;
$woWaiting       = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM work_order WHERE status='WAITING SCHEDULE'"))['total'] ?? 0;
$woApproval      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM work_order WHERE status='WAITING APPROVAL'"))['total'] ?? 0;
$woOpened        = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM work_order WHERE status='OPENED'"))['total'] ?? 0;
$woProgress      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM work_order WHERE status='ON PROGRESS'"))['total'] ?? 0;
$woChecked       = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM work_order WHERE status='WAITING CHECKED'"))['total'] ?? 0;
$woFinish        = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM work_order WHERE status='FINISHED'"))['total'] ?? 0;
$woReject        = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM work_order WHERE status='REJECTED'"))['total'] ?? 0;

// Data untuk Bar Chart - Total WO per Tahun
$yearData = [];
$result = mysqli_query($conn, "
  SELECT YEAR(tgl_input) AS tahun, COUNT(*) AS total
  FROM work_order
  GROUP BY YEAR(tgl_input)
  ORDER BY tahun ASC
");
while ($row = mysqli_fetch_assoc($result)) {
  $yearData[] = $row;
}
$years = array_column($yearData, 'tahun');
$totals = array_column($yearData, 'total');


?>

<?php include '../includes/layout.php'; ?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard - Work Order</title>
  <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <style>
    body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; }

    .card-stat {
      border: none;
      border-radius: 12px;
      color: white;
      padding: 22px 20px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.15);
      position: relative;
      overflow: hidden;
      transition: all 0.3s ease;
    }
    .card-stat:hover { transform: translateY(-4px); box-shadow: 0 8px 16px rgba(0,0,0,0.2); }

    /* Gradient warna */
    .grad-total { background: linear-gradient(135deg, #567890ff, #023556ff); }
    .grad-yellow { background: linear-gradient(135deg, #f1c40f, #f39c12); }
    .grad-purple { background: linear-gradient(135deg, #e39effff, #8e44ad); }
    .grad-gray { background: linear-gradient(135deg, #b5c1c2ff, #636e72); }
    .grad-orange { background: linear-gradient(135deg, #fb963dff, #d35400); }
    .grad-green { background: linear-gradient(135deg, #5ce894ff, #23d23aff); }
    .grad-blue { background: linear-gradient(135deg, #59ccfeff, #086bffff); }
    .grad-red { background: linear-gradient(135deg, #ff7363ff, #c0392b); }

    .card-stat i {
      position: absolute;
      top: 10px;
      right: 15px;
      font-size: 28px;
      opacity: 0.25;
    }

    .chart-card {
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.08);
      padding: 20px;
      height: 360px; /* tinggi tetap agar sejajar */
    }

    .chart-wrapper {
      display: flex;
      align-items: center;
      justify-content: center;
      height: 260px; /* buat donut chart lebih proporsional */
    }
  </style>
</head>
<body>

<div class="container-fluid mt-4">
  <h4 class="fw-semibold mb-3">👋 Selamat datang, <?= htmlspecialchars($nama) ?>!</h4>
  <p class="text-muted mb-4">Berikut ringkasan aktivitas Work Order Anda di sistem.</p>

  <!-- STAT CARDS -->
  <div class="row g-3 mb-4">
    <div class="col-md-3 col-lg-3"><div class="card-stat grad-total text-center"><i class="bi bi-collection"></i><h6>Total Work Order</h6><h2><?= $totalWO ?></h2></div></div>
    <div class="col-md-3 col-lg-3"><div class="card-stat grad-yellow text-center"><i class="bi bi-hourglass-split"></i><h6>Waiting Schedule</h6><h2><?= $woWaiting ?></h2></div></div>
    <div class="col-md-3 col-lg-3"><div class="card-stat grad-purple text-center"><i class="bi bi-check2-square"></i><h6>Waiting Approval</h6><h2><?= $woApproval ?></h2></div></div>
    <div class="col-md-3 col-lg-3"><div class="card-stat grad-gray text-center"><i class="bi bi-box-seam"></i><h6>Opened</h6><h2><?= $woOpened ?></h2></div></div>
    <div class="col-md-3 col-lg-3"><div class="card-stat grad-orange text-center"><i class="bi bi-tools"></i><h6>On Progress</h6><h2><?= $woProgress ?></h2></div></div>
    <div class="col-md-3 col-lg-3"><div class="card-stat grad-blue text-center"><i class="bi bi-clipboard2-check"></i><h6>Waiting Checked</h6><h2><?= $woChecked ?></h2></div></div>
    <div class="col-md-3 col-lg-3"><div class="card-stat grad-green text-center"><i class="bi bi-check-circle"></i><h6>Finished</h6><h2><?= $woFinish ?></h2></div></div>
    <div class="col-md-3 col-lg-3"><div class="card-stat grad-red text-center"><i class="bi bi-x-circle"></i><h6>Rejected</h6><h2><?= $woReject ?></h2></div></div>
  </div>

  <!-- CHARTS -->
  <div class="row g-3">
    <div class="col-md-6">
      <div class="chart-card">
        <h6 class="text-primary fw-semibold mb-3"><i class="bi bi-bar-chart-line me-2"></i>Summary Work Order</h6>
        <canvas id="barChart" height="220"></canvas>
      </div>
    </div>
    <div class="col-md-6">
      <div class="chart-card">
        <h6 class="text-primary fw-semibold mb-3"><i class="bi bi-pie-chart me-2"></i>Distribusi Work Order</h6>
        <div class="chart-wrapper">
          <canvas id="pieChart" style="max-width:320px; max-height:320px;"></canvas>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// 📊 Ambil data WO per tahun dari PHP
const yearLabels = <?= json_encode($years) ?>; // tahun, misal [2022, 2023, 2024, 2025]
const yearTotals = <?= json_encode($totals) ?>; // total WO per tahun, misal [10, 24, 33, 15]

// 🎨 Warna gradasi untuk bar chart
function createGradient(ctx, area) {
  const gradient = ctx.createLinearGradient(0, area.bottom, 0, area.top);
  gradient.addColorStop(0, '#ff4b2b'); // bawah
  gradient.addColorStop(1, '#ff416c'); // atas
  return gradient;
}

// 🎬 Animasi fade-in sederhana untuk chart container
document.querySelectorAll('.chart-card').forEach(el => {
  el.style.opacity = '0';
  el.style.transition = 'opacity 1s ease';
});

window.addEventListener('load', () => {
  setTimeout(() => {
    document.querySelectorAll('.chart-card').forEach(el => el.style.opacity = '1');
  }, 250);
});

// 📊 Bar Chart - Total Work Order per Tahun
new Chart(document.getElementById('barChart'), {
  type: 'bar',
  data: {
    labels: yearLabels,
    datasets: [{
      label: 'Total Work Order',
      data: yearTotals,
      backgroundColor: (context) => {
        const chart = context.chart;
        const {ctx, chartArea} = chart;
        if (!chartArea) return null;
        return createGradient(ctx, chartArea);
      },
      borderRadius: 6
    }]
  },
  options: {
    layout: { padding: { top: 10, right: 10, bottom: 10, left: 10 } },
    plugins: {
      legend: { display: false },
      tooltip: {
        backgroundColor: '#2c3e50',
        titleFont: { weight: 'bold' },
        cornerRadius: 8
      }
    },
    scales: {
      y: {
        beginAtZero: true,
        grid: { color: '#ececec' },
        ticks: { font: { size: 12 } }
      },
      x: {
        grid: { display: false },
        ticks: {
          font: { size: 12 },
          maxRotation: 40,
          minRotation: 20
        }
      }
    },
    animation: {
      duration: 1200,
      easing: 'easeOutQuart'
    }
  }
});


// 🍩 Donut Chart (Tetap Sama)
const labels = [
  'Waiting Schedule', 
  'Waiting Approval', 
  'Opened', 
  'On Progress', 
  'Waiting Checked', 
  'Finished', 
  'Rejected'
];

const dataWO = [
  <?= $woWaiting ?>,
  <?= $woApproval ?>,
  <?= $woOpened ?>,
  <?= $woProgress ?>,
  <?= $woChecked ?>,
  <?= $woFinish ?>,
  <?= $woReject ?>
];

const colors = [
  '#f1c40f',
  '#9b59b6',
  '#7f8c8d',
  '#e67e22',
  '#086bff',
  '#27ae60',
  '#e74c3c'
];

new Chart(document.getElementById('pieChart'), {
  type: 'doughnut',
  data: {
    labels: labels,
    datasets: [{
      data: dataWO,
      backgroundColor: colors,
      hoverOffset: 10,
      borderWidth: 1
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    cutout: '68%',
    layout: { padding: { top: 20, bottom: 20 } },
    plugins: {
      legend: {
        position: 'bottom',
        align: 'center',
        labels: {
          boxWidth: 14,
          font: { size: 12 },
          padding: 10,
          color: '#2c3e50'
        }
      },
      tooltip: {
        backgroundColor: '#2c3e50',
        titleFont: { weight: 'bold' },
        cornerRadius: 8
      }
    },
    animation: {
      animateScale: true,
      animateRotate: true,
      duration: 1300,
      easing: 'easeOutCubic'
    }
  }
});
</script>


</body>
</html>
