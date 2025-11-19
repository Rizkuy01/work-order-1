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
?>
