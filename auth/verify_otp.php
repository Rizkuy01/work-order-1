<?php
session_start();
include '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit;
}

$npk = $_POST['npk'];
$otp = $_POST['otp_full'];

// Default OTP untuk testing
$default_otp = '123456';

// ambil otp terbaru
$q = mysqli_query($conn, "
    SELECT * FROM otp 
    WHERE npk='$npk' 
    ORDER BY id DESC LIMIT 1
");

$data = mysqli_fetch_assoc($q);

// Validasi OTP - bisa menggunakan OTP dari DB atau default 123456
$is_valid = false;
$is_expired = false;

if ($otp == $default_otp) {
    // OTP default selalu valid
    $is_valid = true;
} elseif ($data) {
    // Validasi dengan OTP dari DB
    if ($otp == $data['kode_otp']) {
        // Cek apakah OTP sudah expired
        if (strtotime($data['expired_at']) >= time()) {
            $is_valid = true;
        } else {
            $is_expired = true;
        }
    }
}

// Jika OTP tidak valid, tampilkan pesan error yang sesuai
if (!$is_valid) {
    if ($is_expired) {
        echo "<script>alert('OTP sudah kadaluarsa!'); window.location='login.php';</script>";
    } else {
        echo "<script>alert('OTP SALAH!'); history.back();</script>";
    }
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

// ====== CLEANUP OTP ======
// 1. Hapus OTP yang baru saja digunakan (berhasil login)
if ($data) {
    mysqli_query($conn, "DELETE FROM otp WHERE id = " . $data['id']);
}

// 2. Hapus semua OTP lama yang sudah expired untuk NPK ini
mysqli_query($conn, "DELETE FROM otp WHERE npk='$npk' AND expired_at < NOW()");

// 3. Hapus OTP duplikat (lebih dari 1 untuk NPK yang sama)
$cleanup = "
    DELETE FROM otp 
    WHERE npk='$npk' 
    AND id NOT IN (
        SELECT id FROM (
            SELECT id FROM otp 
            WHERE npk='$npk' 
            ORDER BY created_at DESC 
            LIMIT 1
        ) AS latest
    )
";
mysqli_query($conn, $cleanup);

header("Location: ../work_order/dashboard.php");
exit;
