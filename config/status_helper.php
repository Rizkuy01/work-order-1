<?php
/**
 * Status Helper - Normalisasi status Work Order
 * 
 * Menangani variasi status 'OPEN' dan 'OPENED' agar dianggap sama
 * Fungsi ini memastikan konsistensi data status di seluruh sistem
 */

/**
 * Normalisasi status ke format standar 'OPENED'
 * 
 * @param string $status Status yang akan dinormalisasi
 * @return string Status yang sudah dinormalisasi
 */
function normalizeStatus($status) {
    $status = trim(strtoupper($status));
    
    // Ubah 'OPEN' menjadi 'OPENED'
    if ($status === 'OPEN') {
        return 'OPENED';
    }
    
    return $status;
}

/**
 * Ambil data dengan normalisasi status dari query
 * Menggunakan IN clause untuk kedua variasi status
 * 
 * @param object $conn Database connection
 * @param string $query Query SQL dengan placeholder :status
 * @param string $status Status yang akan dicari (akan di-normalize)
 * @param string $fetchType 'assoc' atau 'array' atau 'all'
 * @return mixed Hasil query
 */
function getWithStatusNormalization($conn, $query, $status, $fetchType = 'assoc') {
    $normalized = normalizeStatus($status);
    
    // Ganti :status dengan IN clause untuk kedua variasi
    $modifiedQuery = str_replace(':status', "('OPEN', 'OPENED')", $query);
    
    $result = mysqli_query($conn, $modifiedQuery);
    
    if (!$result) {
        return null;
    }
    
    if ($fetchType === 'all') {
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        return $data;
    } else if ($fetchType === 'array') {
        return mysqli_fetch_array($result);
    } else {
        return mysqli_fetch_assoc($result);
    }
}

/**
 * Build WHERE clause untuk query dengan status normalisasi
 * Mengembalikan clause yang menangani kedua variasi 'OPEN' dan 'OPENED'
 * 
 * @param string $status Status yang akan dicari
 * @param string $column Nama kolom (default: 'status')
 * @return string WHERE clause
 */
function getStatusWhereClause($status, $column = 'status') {
    $normalized = normalizeStatus($status);
    return "({$column} = '{$normalized}' OR {$column} = 'OPEN')";
}

/**
 * Build WHERE clause untuk multiple status dengan normalisasi
 * 
 * @param array $statuses Array of statuses
 * @param string $column Nama kolom (default: 'status')
 * @return string WHERE clause
 */
function getMultipleStatusWhereClause($statuses, $column = 'status') {
    $normalized = [];
    foreach ($statuses as $status) {
        $normalized[] = normalizeStatus($status);
    }
    
    // Jika 'OPENED' dalam list, tambahkan juga 'OPEN'
    if (in_array('OPENED', $normalized) && !in_array('OPEN', $normalized)) {
        $normalized[] = 'OPEN';
    }
    
    $statusList = "'" . implode("','", $normalized) . "'";
    return "{$column} IN ({$statusList})";
}

/**
 * Count data dengan normalisasi status
 * 
 * @param object $conn Database connection
 * @param string $table Table name
 * @param string $status Status yang akan dicari
 * @param string $where Additional WHERE clause (optional)
 * @return int Jumlah data
 */
function countWithStatus($conn, $table, $status, $where = '') {
    $status = normalizeStatus($status);
    $whereClause = getStatusWhereClause($status);
    
    if ($where) {
        $whereClause = "{$whereClause} AND ({$where})";
    }
    
    $query = "SELECT COUNT(*) AS total FROM {$table} WHERE {$whereClause}";
    $result = mysqli_fetch_assoc(mysqli_query($conn, $query));
    
    return $result['total'] ?? 0;
}
?>
