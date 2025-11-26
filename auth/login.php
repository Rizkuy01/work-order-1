<?php
session_start();
include '../config/database.php'; // ada: $conn_lembur, $conn_isd, $conn(work_order)

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $npk      = mysqli_real_escape_string($conn_lembur, $_POST['npk']);
    $password = $_POST['password'];
    $captcha  = $_POST['captcha'];

    // ============================
    // CEK CAPTCHA
    // ============================
    if ($captcha !== ($_SESSION['captcha_text'] ?? '')) {
        $error = "Captcha salah!";
    } else {

        // ============================
        // GET USER DARI ct_users (DB lembur1)
        // ============================
        $q = "
            SELECT *
            FROM ct_users
            WHERE npk = '$npk'
            LIMIT 1
        ";
        $result = mysqli_query($conn_lembur, $q);

        if ($result && mysqli_num_rows($result) === 1) {

            $user = mysqli_fetch_assoc($result);

            // ============================
            // VERIFIKASI PASSWORD (kolom = pwd)
            // ============================
            if (password_verify($password, $user['pwd'])) {

                // ============================
                // MAPPING ROLE
                // ============================
                $role = "Operator";

                if ($user['golongan'] == 1 || $user['golongan'] == 2) {
                    $role = "Maintenance";
                }
                elseif ($user['golongan'] == 3) {
                    $role = "Foreman";
                }
                elseif ($user['golongan'] == 4 && $user['acting'] == 2) {
                    $role = "Supervisor";
                }
                elseif ($user['golongan'] == 4 && $user['acting'] == 1) {
                    $role = "Super Administrator";
                }

                // ============================
                // SAVE SESSION SEMENTARA (OTP BELUM VERIFIED)
                // ============================
                $_SESSION['pending_npk']     = $user['npk'];
                $_SESSION['pending_nama']    = $user['full_name'];
                $_SESSION['pending_role']    = $role;
                $_SESSION['pending_dept']    = $user['dept'];
                $_SESSION['pending_section'] = $user['sect'];

                unset($_SESSION['captcha_text']);

                // ============================
                // LANJUTKAN KE OTP
                // ============================
                header("Location: proses_otp.php");
                exit;

            } else {
                $error = "Password salah!";
            }

        } else {
            $error = "NPK tidak ditemukan!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Login - Work Order System</title>
  <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
  <style>
    body {
      background: url('../assets/img/bg-kyb.png') no-repeat center center fixed;
      background-size: cover;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .login-container {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      backdrop-filter: blur(3px);
    }
    .login-card {
      background: rgba(255, 255, 255, 0.95);
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.2);
      width: 400px;
      padding: 30px 25px;
      text-align: center;
    }
    .login-card img {
      width: 120px;
      margin-bottom: 10px;
    }
    .btn-primary {
      background-color: #d50000;
      border-color: #d50000;
      font-weight: 600;
    }
    .btn-primary:hover {
      background-color: #b20000;
      border-color: #b20000;
    }
    .captcha-box img {
      height: 40px;
      cursor: pointer;
    }
  </style>
</head>
<body>
<div class="login-container">
  <div class="login-card">
    <img src="../assets/img/logo-kyb.png" alt="KYB Logo">
    <h5 class="fw-bold mb-2">LOGIN</h5>
    <p class="text-muted">WORK ORDER SYSTEM</p>

    <?php if ($error): ?>
      <div class="alert alert-danger py-2" id="error-alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" id="loginForm" autocomplete="off">
      <div class="mb-3 text-start">
        <label>NPK</label>
        <input type="text" name="npk" class="form-control" required autofocus>
      </div>

      <div class="mb-3 text-start">
        <label>Password</label>
        <input type="password" name="password" class="form-control" required>
      </div>

      <div class="mb-3 text-start">
        <label>Captcha</label>
        <div class="d-flex align-items-center captcha-box">
          <img src="captcha.php" alt="Captcha" id="captchaImage" class="border rounded me-2" title="Klik untuk refresh captcha">
          <input type="text" name="captcha" class="form-control" placeholder="Masukkan kode" required>
        </div>
      </div>

      <button type="submit" class="btn btn-primary w-100 mt-3">LOGIN</button>
    </form>

    <hr class="my-3">
    <div class="d-grid gap-2">
      <a href="../work_order/dashboard.php" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-bar-chart"></i> Lihat Dashboard
      </a>
    </div>
  </div>
</div>

<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script>
  const captchaImage = document.getElementById("captchaImage");
  const errorAlert = document.getElementById("error-alert");

  // Klik gambar untuk refresh captcha manual
  captchaImage.addEventListener("click", () => {
    captchaImage.src = "captcha.php?" + Math.random();
  });

  // Jika ada error (misal password salah), refresh captcha otomatis
  if (errorAlert) {
    captchaImage.src = "captcha.php?" + Math.random();
  }
</script>
</body>
</html>
