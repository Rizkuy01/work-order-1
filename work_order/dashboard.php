<?php
include '../includes/session_check_flexible.php';
include '../config/database.php';

$role = $_SESSION['role'];
$nama = $_SESSION['nama'];
$is_guest = $_SESSION['is_guest'] ?? false;

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
  <link rel="stylesheet" href="../assets/css/bootstrap-icons.css">
  <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"> -->
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
      height: 360px;
    }

    .chart-wrapper {
      display: flex;
      align-items: center;
      justify-content: center;
      height: 260px;
    }
  </style>
</head>
<body>

<div class="container-fluid mt-4">
  <h4 class="fw-semibold mb-3">👋 Selamat datang, <?= htmlspecialchars($nama) ?>!</h4>
  <p class="text-muted mb-4">Berikut ringkasan aktivitas Work Order Anda di sistem.</p>

  <!-- FILTER SECTION -->
  <div class="row g-2 mb-4">
    <div class="col-md-3">
      <label class="form-label fw-semibold text-sm">Departement</label>
      <select id="filterDashDept" class="form-select">
        <option value="">Semua Departement</option>
        <?php 
        $sections_list = ['PROD1', 'PROD2', 'PROD3', 'PROD4', 'PROD5', 'QA Lab'];
        foreach ($sections_list as $section_name):
        ?>
          <option value="<?= $section_name ?>"><?= $section_name ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-md-3">
      <label class="form-label fw-semibold text-sm">Line</label>
      <select id="filterDashLine" class="form-select">
        <option value="">Semua Line</option>
      </select>
    </div>

    <div class="col-md-3">
      <label class="form-label fw-semibold text-sm">Mesin</label>
      <select id="filterDashMesin" class="form-select">
        <option value="">Semua Mesin</option>
      </select>
    </div>

    <div class="col-md-3 d-flex align-items-end">
      <button id="resetDashFilter" class="btn btn-outline-secondary w-100">
        <i class="bi bi-arrow-clockwise me-1"></i> Reset Filter
      </button>
    </div>
  </div>

  <!-- STAT CARDS -->
  <div class="row g-3 mb-4">
    <div class="col-md-3 col-lg-3">
      <div class="card-stat grad-total text-center">
        <i class="bi bi-collection"></i>
        <h6>Total Work Order</h6>
        <h2 class="count" id="countTotal" data-target="<?= $totalWO ?>"><?= $totalWO ?></h2>
      </div>
    </div>

    <div class="col-md-3 col-lg-3">
      <div class="card-stat grad-yellow text-center">
        <i class="bi bi-hourglass-split"></i>
        <h6>Waiting Schedule</h6>
        <h2 class="count" id="countWaiting" data-target="<?= $woWaiting ?>"><?= $woWaiting ?></h2>
      </div>
    </div>

    <div class="col-md-3 col-lg-3">
      <div class="card-stat grad-purple text-center">
        <i class="bi bi-check2-square"></i>
        <h6>Waiting Approval</h6>
        <h2 class="count" id="countApproval" data-target="<?= $woApproval ?>"><?= $woApproval ?></h2>
      </div>
    </div>

    <div class="col-md-3 col-lg-3">
      <div class="card-stat grad-gray text-center">
        <i class="bi bi-box-seam"></i>
        <h6>Opened</h6>
        <h2 class="count" id="countOpened" data-target="<?= $woOpened ?>"><?= $woOpened ?></h2>
      </div>
    </div>

    <div class="col-md-3 col-lg-3">
      <div class="card-stat grad-orange text-center">
        <i class="bi bi-tools"></i>
        <h6>On Progress</h6>
        <h2 class="count" id="countProgress" data-target="<?= $woProgress ?>"><?= $woProgress ?></h2>
      </div>
    </div>

    <div class="col-md-3 col-lg-3">
      <div class="card-stat grad-blue text-center">
        <i class="bi bi-clipboard2-check"></i>
        <h6>Waiting Checked</h6>
        <h2 class="count" id="countChecked" data-target="<?= $woChecked ?>"><?= $woChecked ?></h2>
      </div>
    </div>

    <div class="col-md-3 col-lg-3">
      <div class="card-stat grad-green text-center">
        <i class="bi bi-check-circle"></i>
        <h6>Finished</h6>
        <h2 class="count" id="countFinish" data-target="<?= $woFinish ?>"><?= $woFinish ?></h2>
      </div>
    </div>

    <div class="col-md-3 col-lg-3">
      <div class="card-stat grad-red text-center">
        <i class="bi bi-x-circle"></i>
        <h6>Rejected</h6>
        <h2 class="count" id="countReject" data-target="<?= $woReject ?>"><?= $woReject ?></h2>
      </div>
    </div>
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
        <h6 class="text-primary fw-semibold mb-3"><i class="bi bi-bar-chart-line me-2"></i>Distribusi Work Order per Status</h6>
        <canvas id="pieChart" height="220"></canvas>
      </div>
    </div>
  </div>
</div>

<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/chart.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// Fallback jika Chart.js tidak tersedia
if (typeof Chart === 'undefined') {
  console.warn('Chart.js tidak tersedia. Silakan pastikan koneksi internet aktif atau download Chart.js lokal.');
  document.querySelectorAll('.chart-card').forEach(el => {
    el.innerHTML = '<p class="text-danger">Chart tidak dapat dimuat. Periksa koneksi internet Anda.</p>';
  });
}
</script>
<script>
// Global chart instances
let barChartInstance = null;
let pieChartInstance = null;

// 📊 Ambil data WO per tahun dari PHP
let yearLabels = <?= json_encode($years) ?>;
let yearTotals = <?= json_encode($totals) ?>;

// 📊 Data untuk Distribusi per Status
const statusLabels = [
  'Waiting Schedule', 
  'Waiting Approval', 
  'Opened', 
  'On Progress', 
  'Waiting Checked', 
  'Finished', 
  'Rejected'
];

let statusData = [
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

// 🎨 Warna gradasi untuk bar chart
function createGradient(ctx, area) {
  const gradient = ctx.createLinearGradient(0, area.bottom, 0, area.top);
  gradient.addColorStop(0, '#ff4b2b');
  gradient.addColorStop(1, '#ff416c');
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

// Function untuk create atau update bar chart
function createBarChart(labels, data) {
  if (barChartInstance) {
    barChartInstance.destroy();
  }
  
  barChartInstance = new Chart(document.getElementById('barChart'), {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{
        label: 'Total Work Order',
        data: data,
        backgroundColor: (context) => {
          const chart = context.chart;
          const {ctx, chartArea} = chart;
          if (!chartArea) return null;
          return createGradient(ctx, chartArea);
        },
        borderRadius: 6,
        barThickness: 30,
        maxBarThickness: 40
      }]
    },
    options: {
      indexAxis: undefined,
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
          ticks: { font: { size: 12 } },
          suggestedMax: Math.max(...data, 0) * 1.1
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
}

// Function untuk create atau update pie chart
function createPieChart(labels, data) {
  if (pieChartInstance) {
    pieChartInstance.destroy();
  }
  
  pieChartInstance = new Chart(document.getElementById('pieChart'), {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{
        label: 'Jumlah Work Order',
        data: data,
        backgroundColor: colors,
        borderRadius: 6,
        barThickness: 35,
        maxBarThickness: 45
      }]
    },
    options: {
      indexAxis: 'x',
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
          ticks: { font: { size: 12 } },
          suggestedMax: Math.max(...data, 0) * 1.1
        },
        x: {
          grid: { display: false },
          ticks: {
            font: { size: 12 },
            maxRotation: 45,
            minRotation: 0
          }
        }
      },
      responsive: true,
      maintainAspectRatio: true,
      animation: {
        duration: 1200,
        easing: 'easeOutQuart'
      }
    }
  });
}

// Initial chart creation
createBarChart(yearLabels, yearTotals);
createPieChart(statusLabels, statusData);
</script>

<script>
// AJAX Filter Handler
$(document).ready(function() {
  // Load line saat department dipilih
  $("#filterDashDept").change(function() {
    var dept = $(this).val();
    
    if (dept == '') {
      $("#filterDashLine").html('<option value="">Semua Line</option>');
      $("#filterDashMesin").html('<option value="">Semua Mesin</option>');
      return;
    }

    $.post("filter_line.php", { section: dept }, function(data) {
      $("#filterDashLine").html(data);
      $("#filterDashMesin").html('<option value="">Semua Mesin</option>');
    });
  });

  // Load mesin saat line dipilih
  $("#filterDashLine").change(function() {
    var line = $(this).val();
    
    if (line == '') {
      $("#filterDashMesin").html('<option value="">Semua Mesin</option>');
      return;
    }

    $.post("filter_mesin.php", { line: line }, function(data) {
      $("#filterDashMesin").html(data);
    });
  });

  // Update data saat ada filter yang berubah
  function updateDashboard() {
    var dept = $("#filterDashDept").val();
    var line = $("#filterDashLine").val();
    var mesin = $("#filterDashMesin").val();

    $.post("get_dashboard_data.php", {
      dept: dept,
      line: line,
      mesin: mesin
    }, function(response) {
      // Update card stats
      updateCardValue('countTotal', response.totalWO);
      updateCardValue('countWaiting', response.woWaiting);
      updateCardValue('countApproval', response.woApproval);
      updateCardValue('countOpened', response.woOpened);
      updateCardValue('countProgress', response.woProgress);
      updateCardValue('countChecked', response.woChecked);
      updateCardValue('countFinish', response.woFinish);
      updateCardValue('countReject', response.woReject);

      // Update charts
      yearLabels = response.yearLabels;
      yearTotals = response.yearTotals;
      statusData = response.statusData;

      createBarChart(yearLabels, yearTotals);
      createPieChart(statusLabels, statusData);

      // Animate counters
      animateCounters(1000);
    }, 'json');
  }

  // Event listeners untuk filter
  $("#filterDashDept").on("change", function() {
    setTimeout(updateDashboard, 300);
  });

  $("#filterDashLine").on("change", function() {
    setTimeout(updateDashboard, 300);
  });

  $("#filterDashMesin").on("change", function() {
    updateDashboard();
  });

  // Reset filter
  $("#resetDashFilter").click(function() {
    $("#filterDashDept").val('');
    $("#filterDashLine").html('<option value="">Semua Line</option>');
    $("#filterDashMesin").html('<option value="">Semua Mesin</option>');
    updateDashboard();
  });
});

// Update card value dengan animasi
function updateCardValue(elementId, newValue) {
  const element = document.getElementById(elementId);
  if (element) {
    element.setAttribute('data-target', newValue);
    element.textContent = '0';
  }
}

// Animated counter for stat cards
function animateCounters(duration = 1000) {
  const counters = document.querySelectorAll('.count');
  if (!counters.length) return;

  counters.forEach(el => {
    const target = parseInt(el.getAttribute('data-target') || '0', 10);
    // Reset to 0 immediately (helps visual start)
    el.textContent = '0';

    const start = 0;
    const startTime = performance.now();

    function tick(now) {
      const progress = Math.min((now - startTime) / duration, 1);
      const value = Math.floor(progress * (target - start) + start);
      el.textContent = value.toLocaleString();
      if (progress < 1) {
        requestAnimationFrame(tick);
      } else {
        el.textContent = target.toLocaleString();
      }
    }

    requestAnimationFrame(tick);
  });
}

// Run animation on window load (after charts animate)
window.addEventListener('load', function () {
  // small delay so user sees card entry animation first
  setTimeout(() => animateCounters(1400), 200);
});
</script>

</body>
</html>

<?php include '../includes/footer.php'; ?>
