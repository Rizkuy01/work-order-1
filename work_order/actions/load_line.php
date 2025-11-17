<?php
include '../../config/database.php';

if (!isset($_POST['section']) || $_POST['section'] == '') {
    echo '<option value="">-- Pilih Section Dulu --</option>';
    exit;
}

$section = mysqli_real_escape_string($conn, $_POST['section']);

$query = mysqli_query($conn, "
    SELECT DISTINCT linename 
    FROM mesin 
    WHERE prod = '$section'
    ORDER BY linename ASC
");

echo '<option value="">-- Pilih Line --</option>';

while ($row = mysqli_fetch_assoc($query)) {
    echo '<option value="' . $row['linename'] . '">' . $row['linename'] . '</option>';
}
?>
