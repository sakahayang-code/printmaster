# PRINTMASTER – Konfigurasi Perusahaan & Manajemen Multi-Cabang

Dokumen ini menjelaskan tata kelola arsitektur **Multi-Cabang**, isolasi data operasional, prosedur alih cabang (branch switching), serta kolaborasi antar-cabang pada sistem **PRINTMASTER ERP**.

---

## 1. Arsitektur Multi-Cabang

PRINTMASTER dirancang dengan arsitektur multi-tenant berbasis cabang dalam satu basis data terpadu:

1. **Satu Perusahaan Induk (`companies`)**: Menyimpan identitas legal entitas bisnis percetakan.
2. **Banyak Cabang (`branches`)**: Setiap cabang memiliki entitas operasional mandiri:
   - Pengguna & Karyawan Cabang
   - Laci Kasir (Cash Register) & Rekap Shift Kasir
   - Gudang Bahan Baku & Saldo Stok Fisik
   - Mesin Percetakan & Antrean SPK Cetak
   - Riwayat Penjualan, Biaya, dan Pengiriman Lokal

```
                  ┌───────────────────────────────┐
                  │    PERUSAHAAN INDUK (PT)      │
                  │   Super Admin & Owner Access  │
                  └───────────────┬───────────────┘
                                  │
         ┌────────────────────────┴────────────────────────┐
         │                                                 │
         ▼                                                 ▼
┌─────────────────────────────────┐       ┌─────────────────────────────────┐
│     CABANG UTAMA (JAKARTA)      │       │        CABANG SURABAYA          │
│ • Kasir & Shift Tunai JKT       │       │ • Kasir & Shift Tunai SBY       │
│ • Gudang Bahan Baku JKT         │       │ • Gudang Bahan Baku SBY         │
│ • Mesin Cetak Offset & Digital  │       │ • Mesin Cetak Outdoor & DTF     │
│ • Antrean SPK Produksi JKT      │       │ • Antrean SPK Produksi SBY      │
└─────────────────────────────────┘       └─────────────────────────────────┘
                 ▲                                         ▲
                 └─────────── Transfer Stok Bahan ─────────┘
                 └─────────── Transfer Job SPK ────────────┘
```

---

## 2. Mekanisme Alih Cabang Aktif (Active Branch Switching)

Pengguna tingkat pimpinan (**Super Administrator** dan **Owner / Direktur**) memiliki akses global ke seluruh cabang.

1. **Penggantian Konteks Instan**:
   - Di bilah navigasi atas (top navbar) terdapat dropdown *Pilih Cabang Aktif*.
   - Saat pimpinan memilih cabang (misal dari *Cabang Utama Jakarta* pindah ke *Cabang Surabaya*), sistem mengirim request `POST /switch-branch`.
   - Konteks sesi `active_branch_id` diperbarui secara aman, dan seluruh data dashboard, pesanan, gudang, serta kasir seketika menampilkan data cabang terpilih.

2. **Isolasi Cabang untuk Staf Lokal**:
   - Pengguna dengan peran staf lokal (seperti `kacab.sby`, `kasir.sby`, `operator1`) **terkunci permanen** hanya pada cabang tempat mereka ditugaskan (`user_branches`).
   - Sistem menolak akses jika staf cabang Surabaya mencoba membuka atau mengubah transaksi milik cabang Jakarta (`403 Forbidden: Akses Ditolak di luar cabang kewenangan`).

---

## 3. Alur Kerja Kolaborasi Antar-Cabang

### A. Transfer Stok Bahan Baku Antar-Cabang (`/inventory/transfers`)
Ketika Cabang Surabaya mengalami kekurangan stok kertas Art Paper 260gr sedangkan Cabang Jakarta memiliki persediaan melimpah:

1. Staf Cabang Surabaya mengajukan dokumen **Transfer Stok** dari menu *Gudang > Transfer Stok*.
2. Cabang Jakarta melakukan verifikasi fisik dan menyetujui pengiriman (*Status: In Transit*).
   - Saldo stok bahan di Cabang Jakarta langsung dipotong melalui kartu stok otomatis.
3. Cabang Surabaya menerima barang fisik dan menekan tombol **Terima Transfer** (*Status: Completed*).
   - Saldo stok bahan di Cabang Surabaya bertambah secara otomatis.
   - Jurnal mutasi persediaan tercatat seimbang.

### B. Pemindahan Pekerjaan Produksi Antar-Cabang (Sub-Job Dispatching)
Jika Cabang Jakarta menerima pesanan cetak spanduk kain atau merchandise yang mesinnya hanya tersedia di Cabang Surabaya:

1. Kasir Cabang Jakarta membuat pesanan atas nama pelanggan di Jakarta.
2. Manajer Produksi mengalokasikan SPK cetak (*Production Job*) ke mesin di Cabang Surabaya.
3. Lantai produksi Surabaya memproses pencetakan dan verifikasi QC.
4. Setelah selesai, barang dikirimkan kembali ke Jakarta atau langsung dikirimkan ke alamat pelanggan menggunakan modul *Pengiriman & Ekspedisi*.

---

## 4. Panduan Menambah Cabang Baru

1. Masuk sebagai **Super Administrator**.
2. Buka menu **Pengaturan > Cabang** (`/branches`).
3. Klik tombol **+ Tambah Cabang Baru**:
   - **Kode Cabang**: Contoh `BDG01` (Maksimal 10 karakter, harus unik).
   - **Nama Cabang**: Contoh `Cabang Bandung - Dago`.
   - **Alamat**: Alamat lengkap workshop cabang.
   - **Nomor Telepon & WhatsApp**: Nomor kontak operasional cabang.
   - **Status Kantor Pusat**: Centang jika cabang ini merupakan kantor pusat.
4. Klik **Simpan Cabang**.
5. Secara otomatis sistem akan menginisialisasi:
   - Akun buku kasir default (`cash_registers`) untuk cabang baru.
   - Gudang penyimpanan bahan utama (`warehouses`) untuk cabang baru.
