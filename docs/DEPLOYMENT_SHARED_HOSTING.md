# PRINTMASTER – Panduan Deployment Shared Hosting (cPanel)

Panduan ini disusun khusus untuk memasang aplikasi **PRINTMASTER** pada lingkungan **Shared Hosting (cPanel / DirectAdmin / Plesk)** tanpa memerlukan akses root VPS, tanpa terminal SSH, dan tanpa dependensi Node.js / Docker.

---

## 1. Persiapan Sebelum Unggah

1. Pastikan paket shared hosting Anda menyediakan:
   - **PHP 8.1 / 8.2 / 8.3** (dapat diubah via menu *Select PHP Version* di cPanel).
   - **MySQL 5.7+ / MariaDB 10.4+**.
   - **Sertifikat SSL Aktif** (AutoSSL / Let's Encrypt gratis dari cPanel), karena fitur **PWA (Progressive Web App)** dan Service Worker memerlukan protokol HTTPS.

2. Kompresi seluruh folder proyek PRINTMASTER menjadi berkas `printmaster.zip`.

---

## 2. Pilihan Struktur Penempatan di Shared Hosting

### Metode A: Struktur Langsung (Paling Praktis untuk Pemula)
Seluruh isi proyek diletakkan langsung di dalam folder domain / subdomain Anda (misalnya `public_html/` atau `public_html/cetak/`).

Aplikasi telah dilengkapi dengan berkas [`.htaccess`](file:///home/antigravity/.htaccess) di tingkat root yang secara otomatis mengalihkan lalu lintas pengunjung ke subfolder `public/` sekaligus mengunci folder sensitif (`app/`, `database/`, `routes/`, `resources/`) dari akses langsung browser.

```apache
# Isi .htaccess Root (Sudah Disediakan Otomatis):
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^$ public/ [L]
    RewriteRule (.*) public/$1 [L]
</IfModule>
```

---

### Metode B: Struktur Terpisah / Split (Direkomendasikan untuk Keamanan Maksimal)
Untuk keamanan tingkat enterprise, letakkan kode logika di luar root publik:

1. Buat folder baru di tingkat home akun cPanel Anda:
   `/home/username/printmaster_app/`
2. Pindahkan folder berikut ke dalam folder tersebut:
   - `app/`
   - `database/`
   - `resources/`
   - `routes/`
3. Pindahkan **isi** dari folder `public/` langsung ke dalam folder web publik:
   `/home/username/public_html/` (atau folder subdomain Anda).
4. Sesuaikan path pada berkas `public_html/index.php`:
   ```php
   // Ubah:
   $baseDir = dirname(__DIR__) . '/app/';
   // Menjadi:
   $baseDir = '/home/username/printmaster_app/app/';
   ```

---

## 3. Langkah Demi Langkah Instalasi Melalui cPanel

### Langkah 1: Buat Database MySQL & Pengguna
1. Masuk ke dashboard cPanel Anda.
2. Buka menu **MySQL® Database Wizard**.
3. Masukkan nama database (misal: `cpaneluser_printmaster`).
4. Buat pengguna database (misal: `cpaneluser_pmuser`) dengan kata sandi yang kuat.
5. Centang **ALL PRIVILEGES** (Semua Hak Akses), lalu klik *Next Step*.

### Langkah 2: Impor Skema & Data Awal via phpMyAdmin
1. Kembali ke cPanel, buka menu **phpMyAdmin**.
2. Pilih database yang baru saja dibuat di bilah sisi kiri.
3. Klik tab **Import** di bagian atas:
   - Unggah berkas [`database/printmaster.sql`](file:///home/antigravity/database/printmaster.sql), lalu klik **Go**.
   - Setelah sukses, klik tab **Import** kembali dan unggah [`database/seeds/initial_seeds.sql`](file:///home/antigravity/database/seeds/initial_seeds.sql), lalu klik **Go**.
4. Pastikan 62 tabel berhasil tercipta dan terisi data awal.

### Langkah 3: Unggah dan Ekstrak Berkas
1. Buka menu **File Manager** di cPanel.
2. Masuk ke direktori tujuan (misal: `public_html`).
3. Klik tombol **Upload**, pilih berkas `printmaster.zip`, dan tunggu hingga proses unggah selesai (indikator bar hijau 100%).
4. Klik kanan berkas zip yang telah diunggah, lalu pilih **Extract**.

### Langkah 4: Sesuaikan Konfigurasi Koneksi Database
1. Buka folder `app/Config/`.
2. Klik kanan berkas [`Database.php`](file:///home/antigravity/app/Config/Database.php) dan pilih **Edit**.
3. Masukkan kredensial database cPanel Anda:
   ```php
   return [
       'host'     => 'localhost',     // Di shared hosting biasanya localhost
       'port'     => 3306,
       'database' => 'cpaneluser_printmaster',
       'username' => 'cpaneluser_pmuser',
       'password' => 'PasswordDatabaseKuatAnda123!',
       'charset'  => 'utf8mb4',
       'options'  => [
           \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
           \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
           \PDO::ATTR_EMULATE_PREPARES   => false,
       ]
   ];
   ```
4. Simpan perubahan (*Save Changes*).

### Langkah 5: Atur Izin Berkas (File Permissions)
1. Di cPanel File Manager, periksa folder `public/uploads`.
2. Pastikan permission folder bernilai **0755** (atau **0775** jika server web menggunakan konfigurasi suPHP).
3. Berkas PHP lainnya standar bernilai **0644**.

### Langkah 6: Aktifkan HTTPS & Uji PWA
1. Buka domain Anda di browser: `https://namadomainanda.com/`.
2. Pastikan gembok hijau HTTPS aktif.
3. Buka melalui browser Google Chrome di ponsel Android: Anda akan melihat tombol prompt **"Tambahkan ke Layar Utama" / "Install PRINTMASTER"** untuk menginstal aplikasi sebagai PWA.

---

## 4. Pengaturan Cron Job cPanel (Opsional)

Jika Anda ingin antrean notifikasi WhatsApp otomatis dikirim berkala di latar belakang, buat Cron Job di menu **Cron Jobs** cPanel:

```bash
* * * * * php -q /home/username/public_html/cron.php >/dev/null 2>&1
```
*(Interval dapat diatur setiap 1 menit atau setiap 5 menit).*
