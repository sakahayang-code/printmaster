# PRINTMASTER – Daftar Akun Pengujian & Skenario Uji

Dokumen ini memuat daftar 16 akun pengujian yang telah diinisialisasi pada basis data, mencakup seluruh variasi jabatan dan cabang beserta skenario pengujian yang direkomendasikan.

> **Catatan Keamanan**: Seluruh akun pengujian menggunakan kata sandi seragam: `password123`.

---

## 1. Tabel Kredensial Akun Pengujian

| No | Username | Password | Nama Lengkap | Peran (Role) | Cabang Penugasan |
| :-: | :--- | :--- | :--- | :--- | :--- |
| 1 | `admin` | `password123` | Super Administrator | **Super Admin** | Kantor Pusat (Akses Semua Cabang) |
| 2 | `owner` | `password123` | Ir. Hendra Wijaya | **Owner / Direktur** | Kantor Pusat (Akses Monitoring Global) |
| 3 | `kacab.jkt` | `password123` | Bambang Sudiro | **Kepala Cabang** | Cabang Utama (Pusat) - Jakarta |
| 4 | `kacab.sby` | `password123` | Dewi Lestari | **Kepala Cabang** | Cabang Surabaya |
| 5 | `kasir.jkt` | `password123` | Rina Marlina | **Kasir / POS** | Cabang Utama (Pusat) - Jakarta |
| 6 | `kasir.sby` | `password123` | Andi Saputra | **Kasir / POS** | Cabang Surabaya |
| 7 | `desainer1` | `password123` | Dimas Anggoro | **Desainer Grafis**| Cabang Utama (Pusat) - Jakarta |
| 8 | `prodman` | `password123` | Eko Wahyudi | **Manajer Produksi**| Cabang Utama (Pusat) - Jakarta |
| 9 | `operator1` | `password123` | Joko Susilo | **Operator Cetak**| Cabang Utama (Pusat) - Jakarta |
| 10 | `qc1` | `password123` | Fajar Nugraha | **Petugas QC** | Cabang Utama (Pusat) - Jakarta |
| 11 | `gudang1` | `password123` | Agus Santoso | **Staf Gudang** | Cabang Utama (Pusat) - Jakarta |
| 12 | `purchasing1`| `password123` | Siska Amelia | **Staf Purchasing**| Cabang Utama (Pusat) - Jakarta |
| 13 | `akuntan1` | `password123` | Wahyu Hidayat | **Akuntan / Finance**| Cabang Utama (Pusat) - Jakarta |
| 14 | `hrd1` | `password123` | Maya Puspita | **Admin HRD / SDM** | Cabang Utama (Pusat) - Jakarta |
| 15 | `kurir1` | `password123` | Rudi Hermawan | **Kurir Ekspedisi**| Cabang Utama (Pusat) - Jakarta |
| 16 | `budi.santoso`| `password123`| Budi Santoso | **Pelanggan** | Cabang Utama (Pusat) - Jakarta |

---

## 2. Skenario Pengujian Alur Bisnis Utama

### Skenario 1: Isolasi Data & Alih Cabang (Super Admin vs Kasir Surabaya)
1. Login sebagai `kasir.sby` (Cabang Surabaya):
   - Perhatikan bahwa daftar pesanan di `/orders` hanya menampilkan pesanan milik Cabang Surabaya.
   - Menu alih cabang tidak mengizinkan akses ke data Jakarta.
2. Logout, lalu login sebagai `admin` (Super Admin):
   - Buka dropdown cabang di bilah atas, pilih *Cabang Surabaya*. Seluruh data seketika berpindah ke konteks Surabaya.
   - Beralih kembali ke *Cabang Utama Jakarta*, konteks data seketika berpindah ke Jakarta.

### Skenario 2: Transaksi POS Cepat, Struk & Jurnal Otomatis
1. Login sebagai `kasir.jkt`.
2. Buka menu **POS Kasir** (`/pos`).
3. Pilih produk (misal: *Banner Outdoor Flexi Korea 440gr*), tentukan ukuran $3 \times 1$ meter, kuantitas 2 pcs.
4. Pilih metode pembayaran *Tunai* (Rp 180.000). Klik **Bayar & Cetak Struk**.
5. Verifikasi bahwa:
   - Struk thermal kasir siap cetak muncul dengan rincian akurat.
   - Stok bahan *Flexi Korea 440gr* otomatis berkurang di kartu stok gudang.
   - Jurnal penerimaan kas otomatis terbit di modul akuntansi.

### Skenario 3: Alur Siklus Produksi Lengkap (Order ➔ Desain ➔ SPK ➔ QC ➔ Ambil)
1. **Pemesanan**: Buat pesanan baru di `/orders/create` dengan item cetak brosur.
2. **Desain**: Login sebagai `desainer1`, buka `/design`, setujui proofing berkas desain.
3. **Produksi**: Login sebagai `operator1`, buka papan Kanban di `/production`, ubah status SPK menjadi *Dalam Proses*, lalu klik *Selesai*.
4. **Quality Control**: Login sebagai `qc1`, buka `/qc`, klik tombol *Lolos QC*.
5. **Pengiriman**: Buka `/delivery`, cetak Surat Jalan dan serahkan pesanan kepada kurir atau pelanggan.

### Skenario 4: Portal Pelanggan Publik & Tracking
1. Buka halaman publik tanpa login: `http://localhost:8080/customer-portal`.
2. Masukkan nomor pesanan (misal: `ORD-202611-0001`) pada bilah pelacakan di `/customer-portal/track`.
3. Verifikasi bahwa timeline status pesanan (Desain, Cetak, QC, Siap Ambil) tampil transparan secara real-time.
4. Buka `/customer-portal/order`, buat pesanan mandiri secara online dan periksa bahwa pesanan langsung tersimpan ke basis data dan diarahkan ke halaman pelacakan.
