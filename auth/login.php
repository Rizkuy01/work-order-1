<?php
session_start();
include '../config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $npk = mysqli_real_escape_string($conn, $_POST['npk']);
    $password = $_POST['password'];
    $captcha = $_POST['captcha'];

    if ($captcha !== ($_SESSION['captcha_text'] ?? '')) {
        $error = "Captcha salah!";
    } else {
        $query = "SELECT u.*, r.nama_role, r.judul_role 
                  FROM user u 
                  LEFT JOIN role r ON u.id_role = r.id_role
                  WHERE u.npk = '$npk' AND (u.status = 1 OR u.status IS NULL) AND (u.verified = 1 OR u.verified IS NULL)
                  LIMIT 1";
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            if (password_verify($password, $user['password'])) {
                $_SESSION['id_user']  = $user['id_user'];
                $_SESSION['nama']     = $user['nama'];
                $_SESSION['npk']      = $user['npk'];
                $_SESSION['role']     = $user['judul_role'];
                $_SESSION['id_role']  = $user['id_role'];
                $_SESSION['section']  = $user['section'] ?? '-';

                unset($_SESSION['captcha_text']); 
                header("Location: ../work_order/dashboard.php");
                exit;
            } else {
                $error = "Password salah!";
            }
        } else {
            $error = "User tidak ditemukan atau belum aktif.";
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
