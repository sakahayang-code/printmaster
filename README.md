# PRINTMASTER – ERP Jasa Percetakan Multi-Cabang

Aplikasi Enterprise Resource Planning (ERP) profesional, terintegrasi, dan modern yang dirancang khusus untuk mengelola operasional bisnis jasa percetakan dengan banyak cabang (multi-cabang). 

Dibangun dengan arsitektur **PHP Native Modular (Service-Oriented)** yang bersih, responsif di desktop dan mobile Android, mendukung **Progressive Web App (PWA)**, dan 100% siap dipasang pada **Shared Hosting (cPanel)** tanpa memerlukan dependensi Node.js, VPS, atau runtime background daemon.

---

## 🚀 Fitur Utama & 21 Modul Lengkap

Aplikasi ini mencakup seluruh 21 modul sesuai spesifikasi wajib **Technical Blueprint**:

1. **Dashboard Manajemen**: Visualisasi metrik omset, antrean produksi, piutang, dan performa per cabang.
2. **Manajemen Perusahaan & Multi-Cabang**: Pengelolaan legalitas perusahaan, cabang operasional, dan alih cabang aktif secara instan.
3. **Pengguna, Jabatan & RBAC**: Kontrol akses granular untuk 16 peran (Super Admin, Owner, Kepala Cabang, Kasir, Desainer, Operator, QC, Gudang, Akuntan, HRD, dll).
4. **Pelanggan & CRM**: Klasifikasi tier pelanggan (Regular, Member, Reseller, Corporate), riwayat transaksi, dan manajemen limit piutang.
5. **Katalog Produk & Finishing**: Produk cetak (Outdoor, Offset, Digital, Merchandise) dengan penentuan harga berbasis satuan, luas ($m^2$), atau panjang.
6. **Kalkulator Harga & Estimasi Biaya**: Perhitungan harga otomatis berdasarkan bahan, tinta, mesin, overhead, dan margin keuntungan, serta pembuatan surat penawaran harga resmi (Quotation).
7. **Pemesanan, Penjualan & Kasir POS**: Point of Sales (POS) cepat dengan dukungan struk thermal kasir, cetak invoice penjualan, kuitansi, dan multi-metode pembayaran (Tunai, Transfer, QRIS).
8. **Pengelolaan File Desain & Proofing**: Unggah berkas resolusi tinggi, antrean desainer, verifikasi spesifikasi (CMYK, 300 DPI), dan approval pelanggan.
9. **Manajemen Produksi & Antrean SPK**: Papan Kanban alur kerja produksi real-time dengan penerbitan Surat Perintah Kerja (SPK) otomatis.
10. **Quality Control & Cetak Ulang**: Pemeriksaan hasil cetak sebelum penyerahan barang dan penerbitan tiket cetak ulang (reprint) otomatis jika terjadi cacat produksi.
11. **Manajemen Mesin & Pemeliharaan**: Pemantauan kapasitas mesin cetak, meter counter, log servis berkala, dan jadwal pemeliharaan teknisi.
12. **Persediaan Bahan Baku & Gudang**: Pemantauan stok bahan baku per cabang, peringatan stok minimum (safety stock), kartu stok otomatis, dan transfer bahan antar-cabang.
13. **Pembelian & Pemasok (Purchasing)**: Pembuatan Purchase Order (PO) ke vendor pemasok bahan baku dan verifikasi penerimaan barang fisik di gudang.
14. **Keuangan, Kas, Bank & Akuntansi**: Manajemen shift kasir harian, pencatatan biaya kas keluar operasional, buku jurnal umum berpasangan otomatis (*double-entry balancing*), serta laporan Laba Rugi dan Neraca.
15. **SDM, Presensi & Penggajian (HR & Payroll)**: Pencatatan biodata karyawan, rekap absensi harian, dan pencetakan slip gaji bulanan.
16. **Pengiriman & Pengambilan Pesanan**: Manajemen ekspedisi pengiriman, pencetakan Surat Jalan resmi, dan verifikasi penyerahan pesanan di kasir toko.
17. **Portal Pelanggan Publik**: Halaman publik untuk pelacakan status pesanan secara transparan (*order tracking*) dan pemesanan cetak mandiri secara online tanpa harus login.
18. **Promosi & Pemasaran**: Pembuatan dan manajemen kupon/voucher diskon promosi dengan pembatasan kuota dan periode masa berlaku.
19. **Laporan Manajemen & Analitik**: Laporan penjualan analitik per kategori produk, rekapitulasi performa per cabang, dan ekspor data penjualan ke format CSV.
20. **Notifikasi & Integrasi WhatsApp**: Simulasi WhatsApp Gateway dan manajemen templat notifikasi otomatis (pesanan masuk, SPK selesai, pelunasan).
21. **PWA & Operasional Offline**: Dukungan Service Worker, caching aset statis, dan Web App Manifest agar dapat diinstal sebagai aplikasi native di smartphone Android.

---

## 🛠️ Tumpukan Teknologi (Tech Stack)

- **Backend**: PHP Native 8.2+ (Modular MVC, Service Layer, OOP, Tanpa Framework Berat)
- **Database**: MariaDB 10.5+ / MySQL 8.0+ (InnoDB, Transaksi ACID, 62 Tabel Berelasi)
- **Frontend**: Bootstrap 5.3, Bootstrap Icons, JavaScript (Vanilla ES6), HTML5 Canvas
- **PWA**: Web App Manifest, Service Worker Caching, Offline Fallback
- **Kompatibilitas Server**: 100% Shared Hosting Ready (cPanel, DirectAdmin, Apache mod_rewrite, Nginx)

---

## 📚 Indeks Dokumentasi Lengkap

Dokumentasi teknis dan operasional lengkap tersedia di direktori `docs/`:

1. [**Struktur Folder & Arsitektur**](file:///home/antigravity/docs/STRUCTURE.md): Penjelasan alur MVC, Service Layer, dan konvensi kode.
2. [**Skema Database & ERD**](file:///home/antigravity/docs/DATABASE.md): Kamus data 62 tabel, relasi foreign key, dan diagram ERD.
3. [**Panduan Instalasi Lokal**](file:///home/antigravity/docs/INSTALLATION.md): Langkah instalasi pada XAMPP, Laragon, atau built-in PHP server.
4. [**Panduan Deployment Shared Hosting**](file:///home/antigravity/docs/DEPLOYMENT_SHARED_HOSTING.md): Langkah unggah ke cPanel shared hosting tanpa SSH.
5. [**Konfigurasi Perusahaan & Cabang**](file:///home/antigravity/docs/COMPANY_BRANCH_CONFIG.md): Manajemen multi-cabang, alih cabang aktif, dan transfer stok.
6. [**Panduan Peran Pengguna (Roles)**](file:///home/antigravity/docs/USER_ROLES_GUIDE.md): Matriks hak akses RBAC dan alur kerja harian masing-masing jabatan.
7. [**Daftar Akun Pengujian**](file:///home/antigravity/docs/TEST_ACCOUNTS.md): Tabel 16 akun pengujian default dan skenario uji verifikasi.
8. [**Matriks Verifikasi Modul**](file:///home/antigravity/docs/FEATURE_CHECKLIST.md): Daftar periksa kelengkapan 21 modul sesuai spesifikasi blueprint.
9. [**Panduan Perlindungan Data & Pembaruan Aman**](file:///home/antigravity/docs/DATA_PROTECTION_AND_SAFE_UPDATES.md): Strategi 5 lapis proteksi data, skema non-destruktif, snapshot 1-klik, dan prosedur update tanpa kehilangan data.

---

## ⚡ Memulai Cepat (Quick Start)

### 1. Menjalankan Server Lokal
Pastikan MariaDB/MySQL telah aktif, lalu jalankan perintah berikut di root folder:
```bash
php -S 127.0.0.1:8080 -t public public/index.php
```

### 2. Mengakses Aplikasi
Buka peramban (browser) dan tuju ke:
```
http://127.0.0.1:8080/login
```

### 3. Kredensial Pengujian Default
- **Super Administrator**: `admin` / `password123`
- **Owner / Direktur**: `owner` / `password123`
- **Kasir Jakarta**: `kasir.jkt` / `password123`
- **Kasir Surabaya**: `kasir.sby` / `password123`
- **Portal Pelanggan Publik**: Akses langsung tanpa login di `http://127.0.0.1:8080/customer-portal`

---

## 🧪 Hasil Pengujian Otomatis

Aplikasi telah divalidasi menggunakan test suite komprehensif di [`tests/verify_all.php`](file:///home/antigravity/tests/verify_all.php):

```
====================================================
   PRINTMASTER ERP - AUTOMATED VERIFICATION SUITE   
====================================================
[PASS] 1. GET /login - HTTP 200
[PASS] 1b. CSRF Token extraction
[PASS] 2. POST /login (Super Admin) - Super Admin logged in
[PASS] 21 Module HTTP 200 Verification (POS, Orders, Pricing, Quotes, Customers, Products, Design, Production, QC, Machines, Inventory, Purchasing, Cash Shifts, Payments, Expenses, Journals, Financial Reports, HR, Attendance, Payroll, Delivery, Promotions, WhatsApp, Branches, Users, Audit Logs, Database Backup & Restore)
[PASS] Public Customer Portal Tracking & Online Order Store
[PASS] Multi-Branch Switching Context
[PASS] CSV Sales Export Generation
[PASS] Full Database SQL Dump Download
====================================================
SUMMARY: 43 PASSED, 0 FAILED (100% SUCCESS RATE)
====================================================
```
