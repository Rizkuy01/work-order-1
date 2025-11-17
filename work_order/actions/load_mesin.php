<?php
include '../../config/database.php';

if (!isset($_POST['line']) || $_POST['line'] == '') {
    echo '<option value="">-- Pilih Line Dulu --</option>';
    exit;
}

$line = mysqli_real_escape_string($conn, $_POST['line']);

$query = mysqli_query($conn, "
    SELECT machine 
    FROM mesin 
    WHERE linename = '$line'
    ORDER BY machine ASC
");

echo '<option value="">-- Pilih Mesin --</option>';

while ($row = mysqli_fetch_assoc($query)) {
    echo '<option value="' . $row['machine'] . '">' . $row['machine'] . '</option>';
}
?>
