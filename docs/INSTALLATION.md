# PRINTMASTER – Panduan Instalasi Lokal (Development)

Panduan ini memandu Anda dalam melakukan instalasi dan menjalankan aplikasi **PRINTMASTER** di lingkungan komputer lokal pengembang (Windows XAMPP / Laragon, macOS Homebrew, atau Linux).

---

## 1. Kebutuhan Sistem Minimum

- **PHP**: Versi 8.1, 8.2, atau 8.3.
- **Database**:
  - **SQLite 3** (Bawaan / Rekomendasi): Tersedia langsung di PHP (`pdo_sqlite`), tanpa perlu server terpisah.
  - **MariaDB 10.4+ / MySQL 8.0+** (Opsional): Jika ingin menggunakan engine MySQL terpusat.
- **Ekstensi PHP Wajib**:
  - `pdo_sqlite` (atau `pdo_mysql` jika menggunakan MySQL)
  - `mbstring`
  - `json`
  - `curl`
  - `session`
  - `fileinfo`
  - `gd` (untuk pengolahan thumbnail desain)
- **Web Server**: Apache 2.4+ (dengan modul `mod_rewrite` aktif) atau Nginx, atau built-in PHP CLI Server.

---

## 2. Langkah-Langkah Instalasi

### Langkah 1: Unduh / Tempatkan Source Code
Salin seluruh berkas proyek PRINTMASTER ke dalam folder root web server Anda:
- **XAMPP (Windows)**: `C:\xampp\htdocs\printmaster`
- **Laragon (Windows)**: `C:\laragon\www\printmaster`
- **Linux / macOS**: `/var/www/html/printmaster` atau folder kerja Anda.

### Langkah 2: Opsi Database

#### Opsi A: SQLite (Default - Paling Cepat & Zero Setup)
Anda **TIDAK PERLU** membuat database apapun!
Sistem secara otomatis akan mendeteksi dan menginisialisasi skema 62 tabel beserta data awal (*seeds*) di `database/printmaster.sqlite` pada saat Anda pertama kali mengakses aplikasi di browser.
> [!NOTE]
> File `database/printmaster.sqlite` diabaikan oleh Git, sehingga data transaksi Anda akan tetap 100% aman dan tidak akan hilang meskipun kode program diperbarui atau ditarik (*pull*) dari Git.

#### Opsi B: MariaDB / MySQL (Opsional)
Jika Anda ingin menggunakan MariaDB/MySQL:
1. Buat database baru bernama `printmaster` di phpMyAdmin.
2. Buat file `.env` di direktori utama:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=printmaster
   DB_USERNAME=root
   DB_PASSWORD=
   ```
3. Impor skema dan data awal:
   ```bash
   mysql -u root -p printmaster < database/printmaster.sql
   mysql -u root -p printmaster < database/seeds/initial_seeds.sql
   ```

### Langkah 4: Atur Izin Folder (Permissions)
Pastikan folder upload berkas memiliki hak akses tulis:
```bash
chmod -R 775 public/uploads
```

### Langkah 5: Jalankan Aplikasi

#### Cara A: Menggunakan PHP Built-in Server (Paling Cepat)
Jalankan perintah berikut di dalam direktori utama proyek:
```bash
php -S 127.0.0.1:8080 -t public public/index.php
```
Buka browser Anda dan akses:
```
http://127.0.0.1:8080/
```

#### Cara B: Menggunakan Apache (VirtualHost / XAMPP)
Jika menggunakan Apache VirtualHost, arahkan `DocumentRoot` langsung ke subdirektori `public/`:
```apache
<VirtualHost *:80>
    ServerName printmaster.test
    DocumentRoot "C:/xampp/htdocs/printmaster/public"
    <Directory "C:/xampp/htdocs/printmaster/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

---

## 3. Akun Pengujian Default

Setelah instalasi selesai, Anda dapat login menggunakan kredensial berikut:

| Peran (Role) | Username | Password | Keterangan |
| :--- | :--- | :--- | :--- |
| **Super Administrator** | `admin` | `password123` | Akses penuh seluruh modul & seluruh cabang |
| **Owner / Direktur** | `owner` | `password123` | Monitoring laporan konsolidasi & audit trail |
| **Kepala Cabang Jakarta** | `kacab.jkt` | `password123` | Manajemen cabang Jakarta |
| **Kasir Jakarta** | `kasir.jkt` | `password123` | Point of Sales (POS) & penerimaan pembayaran |
| **Desainer Grafis** | `desainer1` | `password123` | Antrean desain & proofing |
| **Operator Produksi** | `operator1` | `password123` | Update status SPK cetak |
| **Petugas QC** | `qc1` | `password123` | Verifikasi hasil cetak & tiket cetak ulang |

*(Daftar lengkap 16 peran akun uji dapat dilihat di [`docs/TEST_ACCOUNTS.md`](file:///home/antigravity/docs/TEST_ACCOUNTS.md)).*
