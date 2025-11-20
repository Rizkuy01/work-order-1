<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['pending_npk'])) {
    header("Location: login.php");
    exit;
}

$npk  = $_SESSION['pending_npk'];
$nama = $_SESSION['pending_nama'];
$role = $_SESSION['pending_role'];

// ambil no hp dari DB isd
$q = mysqli_query($conn_isd, "SELECT * FROM hp WHERE npk='$npk' LIMIT 1");
$d = mysqli_fetch_assoc($q);

if (!$d) {
    die("Nomor HP tidak ditemukan untuk NPK: $npk");
}

$nohp = $d['no_hp'];

// generate otp
$otp_code = rand(100000, 999999);
$expired = date('Y-m-d H:i:s', time() + 300);

// Simpan OTP ke DB work_order
mysqli_query($conn, "
    INSERT INTO otp (npk, no_hp, kode_otp, expired_at)
    VALUES ('$npk', '$nohp', '$otp_code', '$expired')
");

?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Verifikasi OTP - Work Order System</title>
<link rel="stylesheet" href="../assets/css/bootstrap.min.css">

<style>
    body {
      background: url('../assets/img/bg-kyb.png') no-repeat center center fixed;
      background-size: cover;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .otp-container {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      backdrop-filter: blur(3px);
    }
    .otp-card {
      background: rgba(255, 255, 255, 0.95);
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.2);
      width: 420px;
      padding: 30px 25px;
      text-align: center;
    }
    .otp-card img { width: 120px; margin-bottom: 10px; }

    .otp-input {
        width: 48px;
        height: 48px;
        text-align: center;
        font-size: 22px;
        border-radius: 8px;
        border: 2px solid #bbb;
        margin: 3px;
    }
    .otp-input:focus {
        border-color: #d50000;
        box-shadow: 0 0 6px rgba(213,0,0,0.4);
        outline: none;
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
</style>
</head>

<body>

<div class="otp-container">
  <div class="otp-card">

    <img src="../assets/img/logo-kyb.png">

    <h5 class="fw-bold mb-2">VERIFIKASI OTP</h5>
    <p class="text-muted">Kode OTP telah dikirim ke WhatsApp</p>

    <form method="POST" action="verify_otp.php" id="otpForm">

        <input type="hidden" name="npk" value="<?= $npk ?>">

        <div class="d-flex justify-content-center mb-3">
            <input type="text" maxlength="1" class="otp-input" name="otp[]" required>
            <input type="text" maxlength="1" class="otp-input" name="otp[]" required>
            <input type="text" maxlength="1" class="otp-input" name="otp[]" required>
            <input type="text" maxlength="1" class="otp-input" name="otp[]" required>
            <input type="text" maxlength="1" class="otp-input" name="otp[]" required>
            <input type="text" maxlength="1" class="otp-input" name="otp[]" required>
        </div>

        <div class="d-flex gap-2 mt-3">
            <button type="submit" class="btn btn-primary flex-fill">
                VERIFIKASI
            </button>

            <button type="submit" formaction="proses_otp.php" name="resend" class="btn btn-secondary flex-fill">
                KIRIM ULANG
            </button>
        </div>

    <!-- =============== HAPUS SAAT PRODUCTION =============== -->
    <div class="alert alert-info text-center mt-3">
        <b>OTP (Testing): <?= $otp_code ?></b>
    </div>
    <!-- ===================================================== -->

  </div>
</div>

<script>
// AUTO-MOVE cursor ke kotak berikutnya
const inputs = document.querySelectorAll(".otp-input");
inputs.forEach((input, index) => {
    input.addEventListener("input", () => {
        if (input.value.length === 1 && index < inputs.length - 1) {
            inputs[index + 1].focus();
        }
    });

    input.addEventListener("keydown", (e) => {
        if (e.key === "Backspace" && index > 0 && input.value === "") {
            inputs[index - 1].focus();
        }
    });
});

// Saat submit, gabungkan 6 kotak jadi 1 kode OTP
document.getElementById("otpForm").addEventListener("submit", function(){
    let code = "";
    inputs.forEach(i => code += i.value);
    
    let hidden = document.createElement("input");
    hidden.type = "hidden";
    hidden.name = "otp_full";
    hidden.value = code;

    this.appendChild(hidden);
});
</script>

</body>
</html>
