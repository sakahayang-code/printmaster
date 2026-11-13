# PRINTMASTER – Panduan Instalasi Lokal (Development)

Panduan ini memandu Anda dalam melakukan instalasi dan menjalankan aplikasi **PRINTMASTER** di lingkungan komputer lokal pengembang (Windows XAMPP / Laragon, macOS Homebrew, atau Linux).

---

## 1. Kebutuhan Sistem Minimum

- **PHP**: Versi 8.1, 8.2, atau 8.3.
- **Database**: MariaDB 10.4+ atau MySQL 5.7 / 8.0+.
- **Ekstensi PHP Wajib**:
  - `pdo_mysql`
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

### Langkah 2: Buat Database & Impor Skema
1. Buka antarmuka phpMyAdmin (`http://localhost/phpmyadmin`) atau terminal MySQL.
2. Buat database baru bernama `printmaster` dengan collation `utf8mb4_unicode_ci`:
   ```sql
   CREATE DATABASE printmaster CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
3. Impor berkas skema dan data awal secara berurutan:
   - **Skema 62 Tabel**: [`database/printmaster.sql`](file:///home/antigravity/database/printmaster.sql)
   - **Data Seed Awal**: [`database/seeds/initial_seeds.sql`](file:///home/antigravity/database/seeds/initial_seeds.sql)

   *Atau jalankan melalui perintah terminal:*
   ```bash
   mysql -u root -p printmaster < database/printmaster.sql
   mysql -u root -p printmaster < database/seeds/initial_seeds.sql
   ```

### Langkah 3: Konfigurasi Database
Buka berkas konfigurasi database di [`app/Config/Database.php`](file:///home/antigravity/app/Config/Database.php):

```php
return [
    'host'     => '127.0.0.1',
    'port'     => 3306,
    'database' => 'printmaster',
    'username' => 'root',        // Sesuaikan username database lokal Anda
    'password' => '',            // Sesuaikan password database lokal Anda
    'charset'  => 'utf8mb4',
    'options'  => [
        \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        \PDO::ATTR_EMULATE_PREPARES   => false,
    ]
];
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
