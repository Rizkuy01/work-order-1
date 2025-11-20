<?php
session_start();
if (!isset($_SESSION['npk']) || !isset($_SESSION['role'])) {
    header("Location: ../auth/login.php");
    exit;
}
?>
