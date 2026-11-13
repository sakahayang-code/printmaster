# PRINTMASTER – Arsitektur Folder & Struktur File

Dokumen ini menjelaskan struktur direktori, arsitektur MVC Modular, dan konvensi penamaan yang diterapkan pada aplikasi **PRINTMASTER ERP Jasa Percetakan Multi-Cabang**.

---

## 1. Ikhtisar Arsitektur

PRINTMASTER dibangun menggunakan pola arsitektur **Modular MVC (Model-View-Controller) dengan Service Layer** dalam PHP Native modern (PHP 8.2+) yang dirancang khusus untuk kompatibilitas 100% dengan lingkungan **Shared Hosting (cPanel)** tanpa memerlukan node.js, build tools, atau daemon runtime VPS.

```
[ HTTP Request ]
       │
       ▼
[ public/index.php ] ────────► [ Front Controller / Router ]
       │                                  │
       ▼                                  ▼
[ Middleware Stack ] ◄─────── [ Auth & CSRF Protection ]
       │
       ▼
[ Controller ] ──────────────► [ Request Validation & Permissions ]
       │
       ├─────────────────────► [ Services Layer (Business Logic) ]
       │                                  │
       │                                  ▼
       │                       [ Database (PDO Transaction & ACID) ]
       │
       ▼
[ View Engine ] ─────────────► [ Bootstrap 5.3 + PWA Mobile Layout ]
       │
       ▼
[ HTTP Response (HTML / JSON / CSV / PDF) ]
```

---

## 2. Struktur Direktori Proyek

```
printmaster/
├── app/                              # Kode Utama Aplikasi (PSR-4 Namespace: App\)
│   ├── Config/                       # Konfigurasi Global
│   │   ├── App.php                   # Nama aplikasi, zona waktu, locale Rupiah
│   │   └── Database.php              # Kredensial MySQL/MariaDB & opsi PDO
│   ├── Core/                         # Engine & Framework Inti
│   │   ├── Auth.php                  # Autentikasi, isolasi cabang, permission check
│   │   ├── Controller.php            # Base Controller (render, json, validate)
│   │   ├── CSRF.php                  # Token CSRF anti-tampering
│   │   ├── Database.php              # Wrapper PDO, transaksi bersarang & CRUD
│   │   ├── Model.php                 # Base Model
│   │   ├── Request.php               # HTTP Request wrapper (GET/POST/JSON/Files)
│   │   ├── Response.php              # HTTP Response (JSON/Redirect/Download)
│   │   ├── Router.php                # RESTful route matcher & middleware dispatcher
│   │   ├── Session.php               # Session security & flash messages
│   │   └── View.php                  # Templating engine & layout compositor
│   ├── Helpers/                      # Fungsi Bantuan
│   │   └── FormatHelper.php          # Format Rupiah, tanggal lokal, badge status
│   ├── Middleware/                   # Lapisan Filter HTTP
│   │   ├── AuthMiddleware.php        # Memverifikasi user telah login
│   │   └── CSRFMiddleware.php        # Memvalidasi token CSRF pada POST/PUT/DELETE
│   ├── Modules/                      # 21 Modul Operasional ERP
│   │   ├── Accounting/               # Buku Jurnal, Kasir Shifts, Biaya, Laba Rugi
│   │   ├── Auth/                     # Login, Logout, Ganti Cabang Aktif
│   │   ├── Company/                  # Profil Perusahaan, Cabang, Users, Audit Logs
│   │   ├── CustomerPortal/           # Tracking Publik & Order Online
│   │   ├── Customers/                # Pelanggan, Tiers, Piutang, Diskon
│   │   ├── Dashboard/                # KPI Statistik, Grafik Omset, Antrean Mesin
│   │   ├── Delivery/                 # Surat Jalan, Ekspedisi, Penyerahan Kasir
│   │   ├── Design/                   # Upload Desain, Antrean Desainer, Approval
│   │   ├── HR/                       # Biodata Karyawan, Absensi, Penggajian
│   │   ├── Inventory/                # Stok Bahan Baku, Mutasi, Transfer Antar-Cabang
│   │   ├── Machines/                 # Mesin Cetak, Log Meter, Jadwal Perawatan
│   │   ├── Marketing/                # Voucher Diskon, Kupon Promosi
│   │   ├── Orders/                   # SPK/Work Order, Invoice, Kuitansi, Detail
│   │   ├── POS/                      # Kasir Cepat, Keranjang POS, Multi-Bayar
│   │   ├── Pricing/                  # Kalkulator Estimasi Biaya & Margin Cetak
│   │   ├── Products/                 # Katalog Produk, Kategori, Opsi Finishing
│   │   ├── Production/               # Papan Kanban Produksi, Update Status SPK
│   │   ├── Purchasing/               # Purchase Order (PO), Penerimaan Bahan
│   │   ├── QC/                       # Pengecekan Kualitas & Tiket Cetak Ulang
│   │   ├── Reports/                  # Laporan Penjualan, Rekap Cabang, Ekspor CSV
│   │   └── WhatsApp/                 # Simulasi Notifikasi & Template WhatsApp Gateway
│   └── Services/                     # Lapisan Logika Bisnis Kompleks (Service Layer)
│       ├── AccountingService.php     # Otomatisasi Jurnal Double-Entry (Debit=Kredit)
│       ├── InventoryService.php      # Mutasi Stok Otomatis & Pemotongan Bahan SPK
│       ├── OrderService.php          # Orkestrator Order: Item, SPK, Finansial, WA
│       ├── PricingService.php        # Formula Perhitungan Luas/Meter/Satuan
│       └── WhatsAppService.php       # Dispatcher Template & Antrean Log Notifikasi
│
├── database/                         # Skema & Data Awal Database
│   ├── printmaster.sql               # Skema Lengkap 62 Tabel MariaDB/MySQL (InnoDB)
│   └── seeds/
│       └── initial_seeds.sql         # Data Awal (Akun Perusahaan, Cabang, 16 Akun User)
│
├── docs/                             # Dokumentasi Komprehensif
│   ├── STRUCTURE.md                  # Struktur Folder dan File ini
│   ├── DATABASE.md                   # Kamus Data 62 Tabel & Relasi
│   ├── INSTALLATION.md               # Panduan Instalasi Lokal
│   ├── DEPLOYMENT_SHARED_HOSTING.md  # Panduan Pasang di cPanel Shared Hosting
│   ├── COMPANY_BRANCH_CONFIG.md      # Konfigurasi Multi-Cabang & Hak Akses
│   ├── USER_ROLES_GUIDE.md           # Panduan Operasional 16 Role Pengguna
│   ├── TEST_ACCOUNTS.md              # Daftar Akun Pengujian & Skenario
│   └── FEATURE_CHECKLIST.md          # Matriks Verifikasi 21 Modul Sesuai Blueprint
│
├── public/                           # Web Root Publik (Docroot Shared Hosting)
│   ├── assets/                       # Aset Statis
│   │   ├── css/                      # Bootstrap 5.3 & Custom ERP Styling
│   │   ├── js/                       # Interaksi POS, Kalkulator, & PWA
│   │   └── img/                      # Icon PWA (192x192, 512x512) & Logo
│   ├── uploads/                      # Direktori Upload Berkas Desain & Bukti Bayar
│   ├── .htaccess                     # Rewrite URL Apache ke public/index.php
│   ├── index.php                     # Front Controller & Autoloader
│   ├── manifest.json                 # PWA Web App Manifest
│   ├── offline.html                  # Halaman Fallback Offline PWA
│   └── service-worker.js             # Service Worker Cache & Offline Handling
│
├── resources/                        # Tampilan Template (Views)
│   └── views/
│       ├── layouts/                  # Layout Induk
│       │   ├── main.php              # Layout Utama ERP (Sidebar, Navbar, Switcher)
│       │   ├── auth.php              # Layout Halaman Login
│       │   └── customer.php          # Layout Portal Pelanggan Publik
│       ├── accounting/               # Template Kasir Shift, Jurnal, Biaya, Laba Rugi
│       ├── auth/                     # Template Formulir Login
│       ├── company/                  # Template Cabang, Pengguna, Audit Trail
│       ├── customer_portal/          # Template Tracking Pesanan & Order Online
│       ├── customers/                # Template Master CRM Pelanggan
│       ├── dashboard/                # Template Dashboard KPI & Statistik
│       ├── delivery/                 # Template Surat Jalan & Logistik
│       ├── design/                   # Template Antrean Desain Grafis
│       ├── errors/                   # Template Halaman Error (404, 403, 500)
│       ├── hr/                       # Template Karyawan, Presensi, Payroll
│       ├── inventory/                # Template Gudang, Kartu Stok, Transfer
│       ├── machines/                 # Template Mesin Cetak & Perawatan
│       ├── marketing/                # Template Voucher Diskon
│       ├── orders/                   # Template Pesanan, Cetak Invoice, SPK, Kuitansi
│       ├── pos/                      # Template Point of Sales Kasir Cepat
│       ├── pricing/                  # Template Kalkulator Estimasi & Penawaran
│       ├── products/                 # Template Master Produk & Finishing
│       ├── production/               # Template Papan Antrean Produksi (Kanban)
│       ├── purchasing/               # Template PO Pembelian & Pemasok
│       ├── qc/                       # Template Quality Control & Cetak Ulang
│       ├── reports/                  # Template Laporan Manajemen & Grafik
│       └── whatsapp/                 # Template Simulasi WhatsApp Gateway
│
├── routes/
│   └── web.php                       # Registrasi Seluruh Route URL & Middleware
│
├── tests/
│   └── verify_all.php                # Test Suite Otomatis (42 Pengujian 100% Pass)
│
├── .htaccess                         # Fallback Redirect Root ke public/
└── README.md                         # Petunjuk Cepat Proyek
```

---

## 3. Konvensi Penulisan Kode

1. **Namespace PSR-4**:
   - `App\Config` ➔ `app/Config/`
   - `App\Core` ➔ `app/Core/`
   - `App\Modules\<ModuleName>` ➔ `app/Modules/<ModuleName>/`
   - `App\Services` ➔ `app/Services/`
   - `App\Helpers` ➔ `app/Helpers/`

2. **Format Data & Finansial**:
   - Semua mata uang diformat melalui `FormatHelper::rupiah($amount)` menghasilkan format standar Indonesia: `Rp 150.000`.
   - Tanggal diformat melalui `FormatHelper::tanggal($datetime)` dan `FormatHelper::datetime($datetime)` menghasilkan format waktu Indonesia (`12 November 2026 14:30 WIB`).

3. **Integritas Transaksi (ACID)**:
   - Modul yang melakukan mutasi multi-tabel (seperti pembuatan Order, pelunasan POS, penerimaan PO, dan jurnal akuntansi) wajib dibungkus dalam `Database::beginTransaction()`, `Database::commit()`, dan `Database::rollBack()`.
