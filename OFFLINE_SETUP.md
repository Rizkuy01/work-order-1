# Setup untuk Mode Offline

## Masalah

Ketika mengakses aplikasi tanpa koneksi internet, beberapa elemen tidak muncul:

- ❌ Icon dari Bootstrap Icons (bi bi-\*)
- ❌ Chart dari Chart.js di dashboard

## Solusi

### 1. Bootstrap Icons (Icons)

**Opsi A: Menggunakan Local Fallback (Recommended)**
Sistem sudah dikonfigurasi untuk fallback otomatis. Jika CDN tidak tersedia, akan menampilkan icon placeholder.

**Opsi B: Download Bootstrap Icons Lokal**
Jika ingin menggunakan icon fully offline:

1. Download dari: https://github.com/twbs/icons/releases
2. Extract folder `bootstrap-icons` ke `assets/fonts/`
3. Update di `includes/layout.php`:

   ```php
   <!-- Ganti baris ini -->
   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

   <!-- Dengan ini -->
   <link rel="stylesheet" href="<?= $basePath ?>assets/css/bootstrap-icons.css">
   ```

### 2. Chart.js (Dashboard)

**Opsi A: Menggunakan Cache (Browser Cache)**

- Pertama kali akses dengan internet, browser akan cache Chart.js
- Akses berikutnya bisa offline (selama cache tidak dihapus)

**Opsi B: Download Chart.js Lokal**

1. Download dari: https://www.chartjs.org/
2. Simpan ke `assets/js/chart.min.js`
3. Update di `work_order/dashboard.php`:

   ```php
   <!-- Ganti baris ini -->
   <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

   <!-- Dengan ini -->
   <script src="../assets/js/chart.min.js"></script>
   ```

### 3. Bootstrap CSS & JS

Sudah tersedia lokal di:

- `assets/css/bootstrap.min.css`
- `assets/js/bootstrap.bundle.min.js`

Jadi bootstrap tidak perlu khawatir offline.

## Versi Terbaru

- Bootstrap: 5.3.x (Local)
- Bootstrap Icons: 1.11.1 (CDN + Fallback)
- Chart.js: Latest (CDN + Browser Cache)

## Testing Offline

1. Buka DevTools (F12)
2. Klik Network tab
3. Pilih "Offline" dari dropdown
4. Refresh halaman
5. Perhatikan elemen mana yang hilang

---

**Status Saat Ini:**

- ✅ Bootstrap CSS/JS: Local (Full Offline)
- ⚠️ Bootstrap Icons: CDN + Fallback (Partial Offline - akan terlihat placeholder)
- ⚠️ Chart.js: CDN + Browser Cache (Partial Offline - tergantung cache)
