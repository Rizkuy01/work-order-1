# Panduan Setup Folder Upload Terpisah

## 📁 Struktur Folder Baru

Folder upload telah dipindahkan dari dalam project ke lokasi terpisah untuk mengoptimalkan ukuran project.

**Sebelumnya:**

```
c:\laragon\www\work-order\
├── uploads/
│   ├── before/
│   └── after/
├── work_order/
├── auth/
└── ...
```

**Sekarang:**

```
c:\laragon\www\
├── work-order/          (Hanya kode project)
│   ├── config/
│   ├── work_order/
│   ├── auth/
│   └── ...
│
└── work-order-files/    (Folder untuk upload - BUAT INI!)
    ├── before/          (Foto kondisi awal)
    └── after/           (Foto setelah perbaikan)
```

## ⚙️ Langkah Setup

### 1. Buat Folder Baru

Buat folder `work-order-files` di dalam `c:\laragon\www\`:

```bash
# Buat di Windows
mkdir c:\laragon\www\work-order-files
mkdir c:\laragon\www\work-order-files\before
mkdir c:\laragon\www\work-order-files\after
```

Atau gunakan Windows Explorer:

1. Buka `c:\laragon\www\`
2. Buat folder baru bernama `work-order-files`
3. Di dalamnya, buat 2 folder: `before` dan `after`

### 2. Set Permission (Optional tapi direkomendasikan)

Pastikan folder punya permission write:

```bash
# Lewat Terminal (Run as Administrator)
icacls c:\laragon\www\work-order-files /grant:r Everyone:F
```

## 🔄 Perubahan yang Dilakukan

Semua file telah diupdate untuk menggunakan:

### 1. **Config File Baru** (`config/upload_config.php`)

File ini mendefinisikan path untuk upload:

- `UPLOADS_BASE_PATH` - Path absolut folder uploads
- `UPLOADS_BEFORE_DIR` - Path untuk foto before
- `UPLOADS_AFTER_DIR` - Path untuk foto after
- `UPLOADS_BEFORE_URL` - URL untuk menampilkan foto before
- `UPLOADS_AFTER_URL` - URL untuk menampilkan foto after

### 2. **File yang Diupdate**

#### Upload File (Simpan)

- `work_order/actions/add.php` - Upload foto before
- `work_order/maintenance/maintenance_action.php` - Upload foto after

#### Display File (Tampilkan)

- `work_order/actions/detail.php` - Tampilkan foto di detail WO
- `work_order/final_check/final_check_detail.php` - Tampilkan foto di final check

## ✅ Verifikasi

Setelah setup, coba:

1. **Upload foto** di menu "Tambah Work Order"
2. **Lihat detail** - foto harus muncul dengan benar
3. **Cek folder** - foto akan tersimpan di `c:\laragon\www\work-order-files\before/` atau `after/`

## 📊 Keuntungan

✅ **Project lebih ringan** - Kode project tidak berisi file upload besar  
✅ **Upload terisolir** - Folder upload terpisah dari kode  
✅ **Lebih aman** - Mudah di-backup atau dipindahkan  
✅ **Skalabel** - Folder bisa grow besar tanpa memperbesar project

## 🐛 Troubleshooting

### Foto tidak tampil?

- Pastikan folder `work-order-files/before/` dan `after/` sudah dibuat
- Cek permission folder
- Cek path di browser developer tools (F12)

### Error saat upload?

- Pastikan folder punya permission write
- Cek disk space
- Cek ukuran file

### Path masih error?

- Pastikan file `config/upload_config.php` ada
- Pastikan `$_SERVER['DOCUMENT_ROOT']` benar (biasanya `c:\laragon\www\`)

---

**Dibuat**: November 21, 2025  
**Status**: Setup Selesai
