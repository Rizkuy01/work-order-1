<?php
// ====== Koneksi Database Work Order ======
$conn = mysqli_connect('localhost', 'root', '', 'work_order');
if (!$conn) {
    die("Koneksi work_order gagal: " . mysqli_connect_error());
}

// ====== Koneksi Database Breakdown ======
$conn_breakdown = mysqli_connect('localhost', 'root', '', 'breakdown');
if (!$conn_breakdown) {
    die("Koneksi breakdown gagal: " . mysqli_connect_error());
}

// ====== Koneksi Database Lembur ======
$conn_lembur = mysqli_connect('localhost', 'root', '', 'lembur1');
if (!$conn_lembur) {
    die("Koneksi lembur gagal: " . mysqli_connect_error());
}

// ====== Koneksi Database ISD ======
$conn_isd = mysqli_connect('localhost', 'root', '', 'isd');
if (!$conn_isd) {
    die("Koneksi isd gagal: " . mysqli_connect_error());
}
?>
