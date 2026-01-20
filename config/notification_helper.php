<?php
/**
 * Helper untuk mengelola notifikasi
 * File ini menyediakan fungsi untuk insert notifikasi ke database
 */

/**
 * Insert notifikasi ke table notifikasi
 * 
 * @param mysqli $conn - Koneksi database
 * @param string $no_wa - Nomor WhatsApp tujuan
 * @param string $message - Pesan notifikasi
 * @return bool - true jika berhasil, false jika gagal
 */
function insertNotifikasi($conn, $no_wa, $message) {
    // Escape input
    $no_wa = mysqli_real_escape_string($conn, $no_wa);
    $message = mysqli_real_escape_string($conn, $message);
    
    $query = "
        INSERT INTO notifikasi (no_wa, message, created_at)
        VALUES ('$no_wa', '$message', NOW())
    ";
    
    return mysqli_query($conn, $query);
}

/**
 * Insert notifikasi untuk multiple PIC
 * 
 * @param mysqli $conn - Koneksi database
 * @param mysqli $conn_lembur - Koneksi database lembur untuk ambil no_wa
 * @param array $pic_names - Array nama PIC (pic, pic2, pic3)
 * @param array $wo_data - Array data work order (untuk membuat message)
 * @return void
 */
function insertNotifikasiForPICs($conn, $conn_lembur, $pic_names, $wo_data) {
    foreach ($pic_names as $pic_name) {
        if (empty($pic_name)) continue;
        
        // Ambil nomor WA dari database lembur1
        $user_result = mysqli_query(
            $conn_lembur,
            "SELECT no_telp FROM ct_users WHERE full_name = '" . mysqli_real_escape_string($conn_lembur, $pic_name) . "' LIMIT 1"
        );
        
        $user = mysqli_fetch_assoc($user_result);
        $no_wa = $user['no_telp'] ?? null;
        
        if (!empty($no_wa)) {
            // Format pesan notifikasi
            $message = "🛠️ *NOTIFIKASI SISTEM PT. KAYABA INDONESIA* ⚙️\n\n";
            $message .= "Kepada " . $pic_name . " dari departemen " . ($wo_data['dept'] ?? '-') . "\n\n";
            $message .= "*Jadwal Work Order Baru* ⚠️\n\n";
            $message .= "Judul: " . ($wo_data['judul_wo'] ?? '-') . "\n";
            $message .= "Mesin: " . ($wo_data['nama_mesin'] ?? '-') . "\n";
            $message .= "Tanggal: " . ($wo_data['plan_date'] ?? '-') . "\n";
            $message .= "Jam: " . ($wo_data['plan_time'] ?? '-') . "\n";
            $message .= "Line: " . ($wo_data['line'] ?? '-') . "\n";
            $message .= "📄 Tipe: " . ($wo_data['tipe'] ?? '-') . "\n\n";
            $message .= "Mohon untuk dikerjakan dan selesaikan sesuai dengan waktu yang terjadwal, terimakasih.";
            
            // Insert ke notifikasi
            insertNotifikasi($conn, $no_wa, $message);
        }
    }
}

?>
