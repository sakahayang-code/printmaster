# PRINTMASTER – Panduan Perlindungan Data & Pembaruan Aplikasi yang Aman

Dokumen ini menjelaskan strategi menyeluruh dan arsitektur pengamanan yang diterapkan pada **PRINTMASTER ERP** untuk menjamin **data transaksi, data pelanggan, catatan keuangan, stok gudang, dan berkas desain tidak akan pernah hilang**, meskipun aplikasi diubah, dimodifikasi kodenya, diperbarui versinya, atau dipindahkan antar-server.

---

## 1. Lima Lapisan Proteksi Data di PRINTMASTER

Untuk menjawab prinsip **"Datanya jangan sampai hilang walaupun aplikasi diubah-ubah"**, sistem mengimplementasikan lima lapisan perlindungan independen:

```
┌─────────────────────────────────────────────────────────────┐
│ 1. SKEMA BASIS DATA NON-DESTRUKTIF                          │
│    (CREATE TABLE IF NOT EXISTS & Eliminasi DROP TABLE)      │
├─────────────────────────────────────────────────────────────┤
│ 2. DATA AWAL AMAN (INSERT IGNORE INTO)                      │
│    (Menjamin data existing tidak terhapus atau terduplikasi)│
├─────────────────────────────────────────────────────────────┤
│ 3. ISOLASI KREDENSIAL & LINGKUNGAN (.env / local config)    │
│    (Pembaruan kode/git pull tidak menimpa setting koneksi)  │
├─────────────────────────────────────────────────────────────┤
│ 4. PROTEKSI FOLDER UPLOAD PELANGGAN (public/uploads/)       │
│    (File desain, bukti bayar, & nota kebal dari git pull)   │
├─────────────────────────────────────────────────────────────┤
│ 5. SISTEM SNAPSHOT CADANGAN & MIGRASI BERTINGKAT           │
│    (Backup 1-klik UI, Cron harian otomatis, & Migrator)     │
└─────────────────────────────────────────────────────────────┘
```

---

## 2. Rincian Teknis Lapisan Pengamanan

### Lapisan 1: Skema Database Non-Destruktif
- Berkas skema [`database/printmaster.sql`](file:///home/antigravity/database/printmaster.sql) telah dibersihkan dari seluruh perintah destruktif seperti `DROP TABLE IF EXISTS`.
- Seluruh 62 tabel didefinisikan menggunakan:
  ```sql
  CREATE TABLE IF NOT EXISTS nama_tabel (...);
  ```
- **Dampaknya**: Jika administrator atau pengembang secara tidak sengaja mengimpor ulang berkas `printmaster.sql` ke database produksi yang sudah berjalan, **MariaDB/MySQL tidak akan pernah menghapus tabel yang ada**, dan seluruh riwayat transaksi tetap utuh 100%.

### Lapisan 2: Seeding Data Menggunakan `INSERT IGNORE`
- Berkas data awal [`database/seeds/initial_seeds.sql`](file:///home/antigravity/database/seeds/initial_seeds.sql) menggunakan sintaks:
  ```sql
  INSERT IGNORE INTO nama_tabel (...) VALUES (...);
  ```
- **Dampaknya**: Jika seed dijalankan kembali, sistem hanya memasukkan data master default yang belum ada. Data pelanggan yang sudah bertransaksi, saldo kasir, atau harga produk yang telah diedit tidak akan tertimpa kembali ke nilai awal.

### Lapisan 3: Isolasi Kredensial Database (`.env` dan `database.local.php`)
- Konfigurasi koneksi di [`app/Config/Database.php`](file:///home/antigravity/app/Config/Database.php) membaca secara hierarkis:
  1. Berkas `.env` di root direktori.
  2. Berkas `config/database.local.php` (prioritas tertinggi).
- Kedua berkas ini telah didaftarkan pada [`.gitignore`](file:///home/antigravity/.gitignore).
- **Dampaknya**: Ketika Anda melakukan `git pull` dari GitHub atau mengekstrak ZIP pembaruan kode di File Manager cPanel, kredensial database server Anda **tidak akan pernah tertimpa** oleh konfigurasi default git.

### Lapisan 4: Proteksi Direktori Unggahan Pelanggan (`public/uploads/`)
- Seluruh berkas unggahan pelanggan (desain spanduk, kartu nama, bukti transfer perbankan, thumbnail proofing) disimpan di folder:
  `public/uploads/`
- Folder ini dikecualikan dari pelacakan git (`.gitignore` mengabaikan seluruh isi folder selain `.gitkeep`).
- **Dampaknya**: Pembaruan kode atau perombakan modul tidak akan menghapus berkas-berkas kerja pelanggan yang tersimpan.

### Lapisan 5: Mesin Migrasi Skema Bertahap (`App\Core\Migrator`)
- Jika di masa depan Anda menambahkan fitur baru yang membutuhkan kolom baru pada tabel yang sudah ada (misalnya menambah kolom `diskon_tambahan` pada tabel `orders`), jangan mengubah skema dasar.
- Cukup buat berkas migrasi di folder `database/migrations/` (misal: `002_add_diskon_to_orders.sql`):
  ```sql
  ALTER TABLE orders ADD COLUMN IF NOT EXISTS diskon_tambahan DECIMAL(15,2) DEFAULT 0.00;
  ```
- Jalankan via tombol **Jalankan Migrasi** di menu `/backup` atau melalui terminal CLI:
  ```bash
  php -r "require 'app/Core/Migrator.php'; \App\Core\Migrator::run();"
  ```
- Mesin migrasi akan mencatat pembaruan pada tabel `migrations` tanpa menghapus atau mengosongkan data transaksi lama.

---

## 3. Fitur Backup & Restore di Antarmuka Web (UI)

Sistem telah dilengkapi modul **Cadangan & Pemulihan Basis Data** khusus pimpinan di menu:
**Laporan & Pengaturan > Cadangan & Pemulihan** (`http://localhost:8080/backup`):

1. **Dashboard Kesehatan Database**:
   - Menampilkan nama database, total tabel (62 tabel), estimasi jumlah baris data, dan total pemakaian disk fisik.
2. **Buat Cadangan Instan (1-Klik)**:
   - Menekan tombol **"Buat Cadangan Baru Sekarang"** akan menghasilkan berkas snapshot SQL lengkap di `storage/backups/printmaster_backup_YYYYMMDD_HHMMSS.sql`.
   - Format SQL menggunakan mode aman (*Non-destructive SQL* dengan `CREATE TABLE IF NOT EXISTS` dan `INSERT IGNORE`).
3. **Unduh Dump SQL Langsung**:
   - Mengalirkan berkas cadangan langsung ke browser untuk disimpan di komputer lokal atau flashdisk.
4. **Riwayat Snapshot Server**:
   - Menampilkan daftar seluruh berkas cadangan di server beserta ukuran dan waktu pembuatan.
   - Tersedia tombol **Unduh**, **Pulihkan (Restore)**, dan **Hapus**.
5. **Pemulihan Cepat (Restore Snapshot & File Upload)**:
   - Mengembalikan data ke kondisi titik waktu tertentu secara aman dengan konfirmasi modal.
   - Mendukung unggah berkas `.sql` eksternal jika ingin memindahkan data dari server lain.

---

## 4. Cadangan Harian Otomatis via cPanel Cron Job

Agar pencadangan berjalan secara otomatis setiap hari tanpa perlu membuka browser, Anda dapat memanfaatkan skrip pekerja [`cli/backup.php`](file:///home/antigravity/cli/backup.php).

### Cara Memasang di cPanel:
1. Masuk ke cPanel, buka menu **Cron Jobs** (Tugas Cron).
2. Tambahkan tugas baru:
   - **Minute**: `0`
   - **Hour**: `2` (Pukul 02:00 pagi setiap hari)
   - **Day / Month / Weekday**: `*` (Setiap hari)
   - **Command**:
     ```bash
     php -q /home/username_cpanel/public_html/cli/backup.php >/dev/null 2>&1
     ```
3. Klik **Add New Cron Job**.
4. **Fitur Retensi Otomatis**: Skrip ini secara otomatis menghapus berkas cadangan yang berusia lebih dari 30 hari, sehingga kapasitas hosting Anda tidak akan pernah penuh.

---

## 5. Panduan Praktis Memperbarui Aplikasi (Safe Update Workflow)

Ikuti 3 langkah sederhana ini setiap kali ingin mengubah atau memperbarui kode aplikasi:

```
[ Langkah 1: Buat Backup ]
Buka menu /backup di web, klik "Buat Cadangan Baru Sekarang".
Atau unduh berkas .sql ke komputer Anda.
       │
       ▼
[ Langkah 2: Perbarui Kode Aplikasi ]
Jalankan `git pull origin main` di terminal
Atau unggah berkas zip kode baru dan ekstrak di File Manager cPanel.
(Konfigurasi database, data transaksi, dan file uploads TIDAK AKAN HILANG).
       │
       ▼
[ Langkah 3: Eksekusi Migrasi (Jika Ada) ]
Buka menu /backup, jika muncul peringatan migrasi tertunda,
klik tombol "Jalankan Migrasi Tertunda".
       │
       ▼
[ SELESAI ]
Aplikasi telah menggunakan versi baru dengan seluruh data historis tetap utuh 100%.
```

---

## 6. Prosedur Pemulihan Darurat (Disaster Recovery)

Jika terjadi kendala ekstrim (misalnya hard disk server hosting rusak atau database terhapus manual dari phpMyAdmin):

1. Masuk ke cPanel phpMyAdmin, buat database baru (misal: `printmaster`).
2. Ambil berkas cadangan terakhir dari folder `storage/backups/` atau dari komputer lokal Anda.
3. Impor berkas `.sql` cadangan tersebut melalui menu **Import** di phpMyAdmin atau melalui fitur **Pulihkan dari Berkas Eksternal** di menu `/backup`.
4. Seluruh struktur 62 tabel, data transaksi penjualan, data pelanggan, saldo buku kasir, dan jurnal akuntansi akan seketika kembali ke kondisi normal.
