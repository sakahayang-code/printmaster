# PRINTMASTER – Panduan Pengguna & Matriks Hak Akses (User Roles)

Dokumen ini menjelaskan wewenang, alur kerja operasional harian, dan matriks hak akses untuk seluruh peran (roles) di dalam sistem **PRINTMASTER ERP**.

---

## 1. Matriks Hak Akses Pengguna (Role-Based Access Control)

| Modul / Menu | Super Admin | Owner | Kepala Cabang | Kasir | Desainer | Produksi & QC | Gudang & PO | Akuntan | HRD |
| :--- | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: |
| **Dashboard Eksekutif** | Full | Global | Cabang | Terbatas | - | - | - | Finansial | SDM |
| **Point of Sales (POS)** | Full | - | Monitor | Full | - | - | - | Monitor | - |
| **Pesanan & SPK** | Full | View | Full | Create/Pay | View | Status | View | View | - |
| **Kalkulator & Penawaran**| Full | View | Full | Create | - | - | - | View | - |
| **File Desain & Proofing**| Full | - | View | Upload | Full | View | - | - | - |
| **Antrean Produksi & SPK**| Full | - | Monitor | - | - | Full | - | - | - |
| **Quality Control (QC)** | Full | - | Monitor | - | - | Full | - | - | - |
| **Mesin & Pemeliharaan** | Full | - | Monitor | - | - | Log/Update| - | - | - |
| **Stok Bahan & Gudang** | Full | - | Monitor | - | - | Pakai Bahan| Full | Audit | - |
| **Pembelian (PO) & Vendor**| Full | View | Approve | - | - | Request | Full | Verify | - |
| **Shift Kasir & Kas Masuk**| Full | View | Verify | Full | - | - | - | Audit | - |
| **Biaya Operasional** | Full | View | Approve | Input | - | - | - | Full | - |
| **Buku Jurnal & Laba Rugi**| Full | Full | Cabang | - | - | - | - | Full | - |
| **Karyawan, Presensi, Gaji**| Full | View | Presensi | - | - | - | - | - | Full |
| **Surat Jalan & Ekspedisi**| Full | - | Monitor | Input | - | - | - | - | - |
| **WhatsApp Gateway** | Full | View | - | Trigger | Trigger | Trigger | - | - | - |
| **Pengaturan Cabang & User**| Full | - | - | - | - | - | - | - | - |

---

## 2. Alur Operasional Harian Peran Kunci

### A. Alur Kasir / Front Office (`kasir.jkt` / `kasir.sby`)
1. **Buka Shift Kasir**: Di awal hari sebelum melayani pelanggan, buka menu *Keuangan > Shift Kasir*, klik **Buka Shift Baru**, dan masukkan nominal uang modal awal laci (misal: `Rp 200.000`).
2. **Melayani Transaksi**:
   - Untuk pesanan cepat siap cetak: Gunakan menu **POS Kasir** (`/pos`), pilih produk, masukkan ukuran, pilih metode bayar (Tunai, Transfer Bank, atau QRIS), cetak struk kuitansi.
   - Untuk pesanan custom dengan desain: Gunakan menu **Pesanan > Buat Pesanan Baru** (`/orders/create`), pilih pelanggan atau tambah baru, masukkan rincian ukuran dan finishing, tentukan DP / Uang Muka.
3. **Tutup Shift Kasir**: Di akhir jam kerja, hitung fisik uang tunai di laci, masukkan jumlah aktual penutupan pada halaman *Shift Kasir*, dan klik **Tutup Shift**.

---

### B. Alur Desainer Grafis (`desainer1`)
1. Buka menu **Desain & Proofing** (`/design`).
2. Periksa antrean pekerjaan dengan status `pending` atau `revision`.
3. Unduh berkas dari pelanggan, periksa resolusi cetak (300 DPI, mode warna CMYK), ukuran bleeding, dan format font.
4. Unggah berkas siap cetak (*Ready to Print PDF/TIFF*).
5. Ubah status menjadi `approved` agar otomatis muncul di antrean lantai produksi.

---

### C. Alur Lantai Produksi & Operator (`operator1`, `prodman`)
1. Buka papan Kanban **Produksi** (`/production`).
2. Ambil SPK cetak dari kolom `pending` dan klik **Mulai Pengerjaan** (berpindah ke kolom `in_progress`).
3. Sistem secara otomatis mencatat waktu mulai pengerjaan dan memotong stok bahan baku yang tertera pada SPK.
4. Setelah pencetakan dan finishing selesai, masukkan jumlah hasil cetak riil dan meter counter mesin, lalu klik **Selesaikan Pekerjaan** (berpindah ke antrean QC).

---

### D. Alur Petugas Quality Control (`qc1`)
1. Buka menu **Quality Control** (`/qc`).
2. Periksa fisik hasil cetak: kesesuaian warna terhadap proofing, ketepatan ukuran potong, kerapihan finishing laminasi/pond.
3. Jika memenuhi standar kualitas: Klik **Lolos QC (Pass)**. Status pesanan berubah menjadi `ready_for_pickup` atau `ready_for_delivery`. Sistem otomatis mengirimkan notifikasi WhatsApp kepada pelanggan bahwa pesanan telah siap.
4. Jika cacat/reject: Klik **Tolak QC (Reject)** dan terbitkan **Tiket Cetak Ulang** beserta keterangan penyebab cacatnya.

---

### E. Alur Staf Gudang & Pembelian (`gudang1`, `purchasing1`)
1. Pantau stok bahan baku di menu **Gudang > Stok Bahan** (`/inventory`). Item yang berada di bawah stok pengaman (*safety stock*) ditandai dengan badge merah.
2. Buat Purchase Order (PO) di menu **Pembelian > Buat PO** (`/purchasing`).
3. Saat supplier mengirimkan bahan ke gudang, staf gudang membuka PO terkait dan menekan tombol **Terima Barang**. Stok bahan di gudang otomatis bertambah dan jurnal utang terposting.

---

### F. Alur Keuangan & Akuntan (`akuntan1`)
1. Buka menu **Buku Jurnal** (`/accounting/journals`) untuk memverifikasi keabsahan posting otomatis dari penjualan kasir, penerimaan PO, dan biaya operasional.
2. Pantau laporan performa di menu **Laporan Finansial** (`/accounting/reports`):
   - **Laporan Laba Rugi (Income Statement)**: Total Pendapatan - HPP - Beban Operasional = Laba Bersih.
   - **Laporan Neraca Sederhana (Balance Sheet)**: Total Aset = Kewajiban + Ekuitas.
