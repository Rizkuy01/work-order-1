<?php
session_start();
include '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit;
}

$npk = $_POST['npk'];
$otp = $_POST['otp_full'];

// ambil otp terbaru
$q = mysqli_query($conn, "
    SELECT * FROM otp 
    WHERE npk='$npk' 
    ORDER BY id DESC LIMIT 1
");

$data = mysqli_fetch_assoc($q);

if (!$data) {
    echo "<script>alert('OTP tidak ditemukan.'); window.location='login.php';</script>";
    exit;
}

// Validasi OTP
if ($otp != $data['kode_otp']) {
    echo "<script>alert('OTP SALAH!'); history.back();</script>";
    exit;
}

// Validasi expired
if (strtotime($data['expired_at']) < time()) {
    echo "<script>alert('OTP sudah kadaluarsa!'); window.location='login.php';</script>";
    exit;
}

// ====== JIKA OTP BENAR ======
$_SESSION['npk']  = $_SESSION['pending_npk'];
$_SESSION['nama'] = $_SESSION['pending_nama'];
$_SESSION['role'] = $_SESSION['pending_role'];
$_SESSION['dept'] = $_SESSION['pending_dept'] ?? '-';
$_SESSION['section'] = $_SESSION['pending_section'] ?? '-';

unset($_SESSION['pending_npk']);
unset($_SESSION['pending_nama']);
unset($_SESSION['pending_role']);
unset($_SESSION['pending_dept']);
unset($_SESSION['pending_section']);

header("Location: ../work_order/dashboard.php");
exit;
