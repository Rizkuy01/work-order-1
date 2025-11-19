<?php
include '../../config/database.php'; 

// Validasi input
if (!isset($_POST['section']) || $_POST['section'] == '') {
    echo '<option value="">-- Pilih Section Dulu --</option>';
    exit;
}

$section = mysqli_real_escape_string($conn_breakdown, $_POST['section']);

// QUERY AMBIL LINE DARI DB breakdown
$query = mysqli_query($conn_breakdown, "
    SELECT DISTINCT linename 
    FROM mesin 
    WHERE prod = '$section'
    ORDER BY linename ASC
");

// Default option
echo '<option value="">-- Pilih Line --</option>';

// Generate dropdown
while ($row = mysqli_fetch_assoc($query)) {
    echo '<option value="' . $row['linename'] . '">' . $row['linename'] . '</option>';
}
?>
