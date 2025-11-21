# Setup untuk Mode Offline - Panduan Lengkap

## Situasi Saat Ini

Anda sudah download bootstrap-icons tapi struktur folder belum benar. Berikut panduan lengkapnya:

## ❌ Masalah Saat Ini

- Folder `assets/css/icons/` berisi FULL REPOSITORY (terlalu besar & tidak perlu)
- Folder `assets/fonts/bootstrap-icons/` masih kosong

## ✅ File yang Benar-Benar Diperlukan

Hanya **2 file** yang dibutuhkan untuk offline icons:

1. **bootstrap-icons.css** (CSS file)

   - Size: ~180 KB
   - Lokasi: harus di `assets/css/`

2. **bootstrap-icons.woff2** (Font file)
   - Size: ~100 KB
   - Lokasi: harus di `assets/fonts/bootstrap-icons/`

## 📥 Cara Setup Benar

### Step 1: Download File yang Tepat

Go to: https://github.com/twbs/icons/releases/tag/v1.11.1

Download file: **bootstrap-icons-1.11.1.zip** (bukan full repository)

### Step 2: Extract & Copy File

Setelah extract, akan ada struktur seperti ini:

```
bootstrap-icons-1.11.1/
├── bootstrap-icons.css ← COPY KE assets/css/
├── bootstrap-icons.json
├── CHANGELOG.md
├── docs/
├── fonts/
│   ├── bootstrap-icons.woff2 ← COPY KE assets/fonts/bootstrap-icons/
│   └── bootstrap-icons.woff
└── README.md
```

**Copy:**

- `bootstrap-icons.css` → `c:\laragon\www\work-order\assets\css\`
- `bootstrap-icons.woff2` → `c:\laragon\www\work-order\assets\fonts\bootstrap-icons\`

### Step 3: Update HTML (layout.php)

Di file `includes/layout.php`, ganti baris ini:

```php
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
```

Dengan:

```php
<link rel="stylesheet" href="<?= $basePath ?>assets/css/bootstrap-icons.css">
```

### Step 4: Update CSS (bootstrap-icons.css)

Buka file `assets/css/bootstrap-icons.css` yang sudah di-copy.

Cari baris `@font-face` (sekitar line 1-10), akan terlihat:

```css
@font-face {
  font-family: "bootstrap-icons";
  src: url("../fonts/bootstrap-icons.woff2?v=1.11.1") format("woff2"), url("../fonts/bootstrap-icons.woff?v=1.11.1")
      format("woff");
}
```

**Pastikan path sudah benar:**

- Dari `assets/css/bootstrap-icons.css`
- Ke `assets/fonts/bootstrap-icons/bootstrap-icons.woff2`
- Path relatif: `../fonts/bootstrap-icons/bootstrap-icons.woff2` ✅ (sudah benar)

## File Structure Target

```
assets/
├── css/
│   ├── bootstrap.min.css ✅ (sudah ada, jangan dihapus)
│   ├── bootstrap-icons.css ← BARU (copy dari download)
│   └── icons/ ❌ (hapus folder ini, tidak diperlukan)
├── fonts/
│   ├── arial.TTF
│   └── bootstrap-icons/
│       ├── bootstrap-icons.woff2 ← BARU (copy dari download)
│       └── bootstrap-icons.woff (opsional)
└── js/
    └── bootstrap.bundle.min.js ✅ (sudah ada, jangan dihapus)
```

## 🧹 Cleanup (Opsional tapi Recommended)

Folder `assets/css/icons/` bisa dihapus karena tidak diperlukan:

```bash
# Di terminal/PowerShell
cd c:\laragon\www\work-order\assets\css
rmdir /s icons
```

## 🧪 Testing

Setelah setup:

1. **Test dengan Internet:**

   ```
   http://localhost/work-order/work_order/dashboard.php
   ```

   Icons seharusnya muncul normal

2. **Test Offline:**
   - Buka DevTools (F12)
   - Tab Network → Dropdown = "Offline"
   - Refresh halaman
   - Icons seharusnya masih muncul ✅

## 📊 Status Setelah Setup

- ✅ Bootstrap CSS/JS: Local (100% Offline)
- ✅ Bootstrap Icons: Local (100% Offline)
- ⚠️ Chart.js: CDN (Offline perlu browser cache)

## Bonus: Chart.js Offline (Opsional)

Jika ingin chart juga offline:

1. Download: https://github.com/chartjs/Chart.js/releases/download/v4.4.1/chart.umd.min.js
2. Simpan ke: `assets/js/chart.min.js`
3. Update di `work_order/dashboard.php`:
   ```php
   <script src="<?= $basePath ?>assets/js/chart.min.js"></script>
   ```

---

**Questions?** Cek struktur folder Anda sesuai dengan "File Structure Target" di atas.
