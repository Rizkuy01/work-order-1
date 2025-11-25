<?php
include '../../config/database.php';

if (!isset($_POST['line']) || $_POST['line'] == '') {
    echo '<option value="">-- Pilih Line Dulu --</option>';
    exit;
}

$line = mysqli_real_escape_string($conn_breakdown, $_POST['line']);

$query = mysqli_query($conn_breakdown, "
    SELECT machine 
    FROM mesin 
    WHERE linename = '$line'
    ORDER BY machine ASC
");

if (!$query) {
    echo '<option value="">-- Error: ' . mysqli_error($conn_breakdown) . ' --</option>';
    exit;
}

// Default option
echo '<option value="">-- Pilih Mesin --</option>';

while ($row = mysqli_fetch_assoc($query)) {
    echo '<option value="' . $row['machine'] . '">' . $row['machine'] . "</option>";
}

?>
