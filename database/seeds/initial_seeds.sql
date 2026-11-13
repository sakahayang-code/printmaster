-- PRINTMASTER INITIAL SEEDS
-- Perusahaan, Multi-Cabang, Role, Permission, User, Katalog, Mesin, Bahan, Akuntansi, Pelanggan, Order

-- 1. PERUSAHAAN (COMPANY)
INSERT IGNORE INTO companies (id, code, name, legal_name, tax_number, email, phone, address, currency, timezone, is_active)
VALUES (1, 'CMP01', 'PrintMaster Grafika Indonesia', 'PT Master Grafika Indonesia', '01.234.567.8-012.000', 'info@printmaster.id', '021-5558899', 'Jl. Percetakan Negara No. 45, Jakarta Pusat', 'IDR', 'Asia/Jakarta', 1);

INSERT IGNORE INTO company_settings (company_id, setting_key, setting_value) VALUES
(1, 'company_name', 'PrintMaster Grafika Indonesia'),
(1, 'invoice_footer', 'Terima kasih atas kepercayaan Anda mencetak bersama PrintMaster.'),
(1, 'tax_percent', '11.00'),
(1, 'wa_gateway_provider', 'fonnte'),
(1, 'wa_api_token', 'SIMULATED_TOKEN_12345'),
(1, 'wa_sender_number', '081299887766'),
(1, 'point_exchange_rate', '1000'), -- Tiap 1000 rupiah dapat 1 poin
(1, 'backup_retention_days', '30');

-- 2. CABANG (BRANCHES)
INSERT IGNORE INTO branches (id, company_id, code, name, address, phone, email, is_headquarters, operating_hours, monthly_target, is_active)
VALUES
(1, 1, 'JKT01', 'Cabang Utama (Pusat) - Jakarta', 'Jl. Percetakan Negara No. 45, Jakarta Pusat', '021-5558899', 'jakarta@printmaster.id', 1, '08:00 - 22:00', 250000000.00, 1),
(2, 1, 'SBY01', 'Cabang Surabaya', 'Jl. Ngagel Jaya Selatan No. 12, Gubeng, Surabaya', '031-7788990', 'surabaya@printmaster.id', 0, '08:00 - 21:00', 150000000.00, 1),
(3, 1, 'BDG01', 'Cabang Bandung', 'Jl. Buah Batu No. 88, Lengkong, Bandung', '022-4455667', 'bandung@printmaster.id', 0, '08:00 - 20:00', 100000000.00, 1);

-- 3. GUDANG (WAREHOUSES) PER CABANG
INSERT IGNORE INTO warehouses (id, company_id, branch_id, code, name, description, is_active) VALUES
(1, 1, 1, 'GDG-JKT-01', 'Gudang Bahan Utama Jakarta', 'Gudang pusat bahan lembaran, roll banner & tinta', 1),
(2, 1, 2, 'GDG-SBY-01', 'Gudang Bahan Surabaya', 'Gudang cabang Surabaya', 1),
(3, 1, 3, 'GDG-BDG-01', 'Gudang Bahan Bandung', 'Gudang cabang Bandung', 1);

-- 4. KASIR (CASH REGISTERS) PER CABANG
INSERT IGNORE INTO cash_registers (id, company_id, branch_id, code, name, is_active) VALUES
(1, 1, 1, 'POS-JKT-01', 'Kasir Utama Jakarta 01', 1),
(2, 1, 1, 'POS-JKT-02', 'Kasir Utama Jakarta 02', 1),
(3, 1, 2, 'POS-SBY-01', 'Kasir Surabaya 01', 1),
(4, 1, 3, 'POS-BDG-01', 'Kasir Bandung 01', 1);

-- 5. ROLES (HAK AKSES STANDAR)
INSERT IGNORE INTO roles (id, name, display_name, description, is_system) VALUES
(1, 'superadmin', 'Super Admin', 'Akses penuh ke seluruh sistem, cabang, dan konfigurasi', 1),
(2, 'owner', 'Owner / Direktur', 'Akses eksekutif laporan konsolidasi seluruh cabang, keuangan, margin', 1),
(3, 'branch_manager', 'Kepala Cabang', 'Pengelola operasional cabang bersangkutan (order, staf, kas, mesin)', 1),
(4, 'cashier', 'Kasir / Front Office', 'Kasir POS, input order langsung, terima DP/pelunasan, shift kasir', 1),
(5, 'designer', 'Desainer Grafis', 'Pemeriksaan file desain, revisi proof, konfirmasi ACC desain pelanggan', 1),
(6, 'production_manager', 'Manajer Produksi', 'Plotting antrean job produksi, assign mesin & operator, kontrol alur', 1),
(7, 'production_operator', 'Operator Produksi', 'Pelaksana cetak/finishing, update progres job, input pemakaian bahan', 1),
(8, 'qc_officer', 'Petugas QC', 'Inspeksi kualitas cetak/finishing, approval lulus/reject, pic reprint', 1),
(9, 'warehouse', 'Staf Gudang', 'Penerimaan barang PO, pengeluaran bahan produksi, opname, transfer stok', 1),
(10, 'purchasing', 'Staf Purchasing', 'Kelola supplier, purchase order (PO), approval pembelian', 1),
(11, 'accountant', 'Akuntan / Finance', 'Chart of accounts, posting jurnal, buku besar, neraca, laba rugi, hutang/piutang', 1),
(12, 'hrd', 'HRD / Admin SDM', 'Karyawan, shift kerja, absensi, cuti, penggajian (payroll)', 1),
(13, 'courier', 'Kurir / Driver', 'Daftar pengiriman, input nomor resi, foto bukti serah terima pesanan', 1),
(14, 'customer', 'Pelanggan', 'Portal pelanggan untuk cek katalog, order online, unggah desain, tracking', 1);

-- 6. PERMISSIONS
INSERT IGNORE INTO permissions (id, code, module, description) VALUES
(1, 'dashboard.view', 'dashboard', 'Melihat dashboard'),
(2, 'dashboard.consolidated', 'dashboard', 'Melihat dashboard konsolidasi semua cabang'),
(3, 'company.manage', 'company', 'Mengelola profil perusahaan & cabang'),
(4, 'users.manage', 'users', 'Mengelola user, role, dan hak akses'),
(5, 'customers.view', 'customers', 'Melihat data pelanggan'),
(6, 'customers.create', 'customers', 'Menambah pelanggan baru'),
(7, 'customers.edit', 'customers', 'Mengubah data pelanggan'),
(8, 'customers.delete', 'customers', 'Menghapus data pelanggan'),
(9, 'catalog.view', 'catalog', 'Melihat katalog produk & variasi'),
(10, 'catalog.manage', 'catalog', 'Mengelola katalog produk, harga & finishing'),
(11, 'pricing.calculate', 'pricing', 'Menggunakan kalkulator harga & penawaran'),
(12, 'orders.view', 'orders', 'Melihat daftar pesanan'),
(13, 'orders.create', 'orders', 'Membuat pesanan / POS kasir'),
(14, 'orders.edit', 'orders', 'Mengubah detail pesanan'),
(15, 'orders.cancel', 'orders', 'Membatalkan pesanan'),
(16, 'orders.discount', 'orders', 'Memberikan diskon khusus'),
(17, 'design.view', 'design', 'Melihat file desain & antrean'),
(18, 'design.upload', 'design', 'Mengunggah file & revisi proof'),
(19, 'design.approve', 'design', 'Menyetujui / mengunci desain produksi'),
(20, 'production.view', 'production', 'Melihat antrean & papan produksi'),
(21, 'production.manage', 'production', 'Menugaskan operator, mesin & jadwal'),
(22, 'production.execute', 'production', 'Update progres tahapan & bahan aktual'),
(23, 'production.transfer_branch', 'production', 'Memindahkan pekerjaan ke cabang lain'),
(24, 'qc.inspect', 'qc', 'Melakukan inspeksi QC dan checklist'),
(25, 'qc.reprint', 'qc', 'Menyetujui job cetak ulang / rework'),
(26, 'machines.view', 'machines', 'Melihat daftar mesin & status'),
(27, 'machines.manage', 'machines', 'Kelola mesin & riwayat pemeliharaan'),
(28, 'inventory.view', 'inventory', 'Melihat stok & mutasi gudang'),
(29, 'inventory.adjust', 'inventory', 'Melakukan opname & penyesuaian stok'),
(30, 'inventory.transfer', 'inventory', 'Mengirim & menerima transfer stok antar cabang'),
(31, 'purchasing.view', 'purchasing', 'Melihat supplier & purchase order'),
(32, 'purchasing.manage', 'purchasing', 'Membuat & memproses PO supplier'),
(33, 'payments.view', 'payments', 'Melihat pembayaran & riwayat transaksi'),
(34, 'payments.receive', 'payments', 'Menerima pembayaran kasir / transfer'),
(35, 'payments.refund', 'payments', 'Melakukan refund pembayaran'),
(36, 'cash_shift.manage', 'cash_shift', 'Buka dan tutup kas shift'),
(37, 'accounting.view', 'accounting', 'Melihat jurnal, buku besar & neraca'),
(38, 'accounting.manage', 'accounting', 'Kelola akun COA & input jurnal manual'),
(39, 'hr.employees', 'hr', 'Kelola data karyawan & jabatan'),
(40, 'hr.attendance', 'hr', 'Kelola absensi & cuti karyawan'),
(41, 'hr.payroll', 'hr', 'Proses dan cetak slip gaji karyawan'),
(42, 'delivery.view', 'delivery', 'Melihat antrean pengiriman & ekspedisi'),
(43, 'delivery.execute', 'delivery', 'Update status antar & bukti serah terima'),
(44, 'marketing.manage', 'marketing', 'Kelola voucher promo & banner'),
(45, 'reports.view', 'reports', 'Melihat laporan penjualan & operasional'),
(46, 'reports.consolidated', 'reports', 'Melihat laporan konsolidasi semua cabang'),
(47, 'reports.export', 'reports', 'Ekspor laporan ke PDF / Excel'),
(48, 'whatsapp.manage', 'whatsapp', 'Kelola template & simulasi pengiriman WhatsApp'),
(49, 'backup.manage', 'backup', 'Melakukan backup & restore database'),
(50, 'audit.view', 'audit', 'Melihat audit trail aktivitas sistem');

-- 7. ROLE PERMISSIONS ASSIGNMENT
-- Super Admin (Semua permissions 1-50)
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 1, id FROM permissions;

-- Owner (Laporan konsolidasi, dashboard, keuangan, audit)
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 2, id FROM permissions WHERE code IN (
    'dashboard.view', 'dashboard.consolidated', 'customers.view', 'catalog.view', 'orders.view',
    'production.view', 'qc.inspect', 'machines.view', 'inventory.view', 'purchasing.view',
    'payments.view', 'accounting.view', 'hr.employees', 'hr.payroll', 'reports.view',
    'reports.consolidated', 'reports.export', 'audit.view'
);

-- Kepala Cabang (Akses lengkap cabang)
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 3, id FROM permissions WHERE code IN (
    'dashboard.view', 'customers.view', 'customers.create', 'customers.edit', 'catalog.view',
    'pricing.calculate', 'orders.view', 'orders.create', 'orders.edit', 'orders.cancel', 'orders.discount',
    'design.view', 'design.approve', 'production.view', 'production.manage', 'production.transfer_branch',
    'qc.inspect', 'qc.reprint', 'machines.view', 'machines.manage', 'inventory.view', 'inventory.adjust',
    'inventory.transfer', 'purchasing.view', 'purchasing.manage', 'payments.view', 'payments.receive',
    'cash_shift.manage', 'accounting.view', 'hr.employees', 'hr.attendance', 'delivery.view',
    'reports.view', 'reports.export'
);

-- Kasir (POS, Input Order, Bayar, Shift)
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 4, id FROM permissions WHERE code IN (
    'dashboard.view', 'customers.view', 'customers.create', 'catalog.view', 'pricing.calculate',
    'orders.view', 'orders.create', 'orders.edit', 'payments.view', 'payments.receive',
    'cash_shift.manage', 'delivery.view'
);

-- Desainer
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 5, id FROM permissions WHERE code IN (
    'dashboard.view', 'orders.view', 'design.view', 'design.upload', 'design.approve'
);

-- Manajer Produksi
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 6, id FROM permissions WHERE code IN (
    'dashboard.view', 'orders.view', 'design.view', 'production.view', 'production.manage',
    'production.execute', 'production.transfer_branch', 'qc.inspect', 'qc.reprint', 'machines.view',
    'inventory.view', 'reports.view'
);

-- Operator Produksi
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 7, id FROM permissions WHERE code IN (
    'dashboard.view', 'production.view', 'production.execute', 'machines.view'
);

-- QC Officer
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 8, id FROM permissions WHERE code IN (
    'dashboard.view', 'orders.view', 'production.view', 'qc.inspect', 'qc.reprint'
);

-- Staf Gudang
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 9, id FROM permissions WHERE code IN (
    'dashboard.view', 'inventory.view', 'inventory.adjust', 'inventory.transfer', 'purchasing.view'
);

-- Purchasing
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 10, id FROM permissions WHERE code IN (
    'dashboard.view', 'inventory.view', 'purchasing.view', 'purchasing.manage', 'payments.view'
);

-- Akuntan
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 11, id FROM permissions WHERE code IN (
    'dashboard.view', 'payments.view', 'payments.receive', 'payments.refund', 'accounting.view',
    'accounting.manage', 'reports.view', 'reports.export'
);

-- HRD
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 12, id FROM permissions WHERE code IN (
    'dashboard.view', 'hr.employees', 'hr.attendance', 'hr.payroll', 'reports.view'
);

-- Kurir
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 13, id FROM permissions WHERE code IN (
    'dashboard.view', 'delivery.view', 'delivery.execute'
);

-- Pelanggan (Portal Customer)
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 14, id FROM permissions WHERE code IN (
    'catalog.view', 'pricing.calculate'
);

-- 8. USERS (Semua akun passwordnya: password123)
-- Hash bcrypt untuk password123
SET @pwd = 'y0.9vb4G5PTnW';

INSERT IGNORE INTO users (id, company_id, branch_id, role_id, name, username, email, phone, password_hash, is_active) VALUES
(1, 1, 1, 1, 'Super Administrator', 'admin', 'admin@printmaster.id', '0811000001', @pwd, 1),
(2, 1, 1, 2, 'Ir. Hendra Wijaya (Owner)', 'owner', 'owner@printmaster.id', '0811000002', @pwd, 1),
(3, 1, 1, 3, 'Bambang Sudiro (Kepala Cabang Jakarta)', 'kacab.jkt', 'kacab.jkt@printmaster.id', '0811000003', @pwd, 1),
(4, 1, 2, 3, 'Dewi Lestari (Kepala Cabang Surabaya)', 'kacab.sby', 'kacab.sby@printmaster.id', '0811000004', @pwd, 1),
(5, 1, 1, 4, 'Rina Marlina (Kasir Jakarta)', 'kasir.jkt', 'kasir.jkt@printmaster.id', '0811000005', @pwd, 1),
(6, 1, 2, 4, 'Andi Saputra (Kasir Surabaya)', 'kasir.sby', 'kasir.sby@printmaster.id', '0811000006', @pwd, 1),
(7, 1, 1, 5, 'Dimas Anggoro (Desainer Grafis)', 'desainer1', 'desainer@printmaster.id', '0811000007', @pwd, 1),
(8, 1, 1, 6, 'Eko Wahyudi (Manajer Produksi)', 'prodman', 'prodman@printmaster.id', '0811000008', @pwd, 1),
(9, 1, 1, 7, 'Joko Susilo (Operator Cetak)', 'operator1', 'operator@printmaster.id', '0811000009', @pwd, 1),
(10, 1, 1, 8, 'Fajar Nugraha (Petugas QC)', 'qc1', 'qc@printmaster.id', '0811000010', @pwd, 1),
(11, 1, 1, 9, 'Agus Santoso (Staf Gudang)', 'gudang1', 'gudang@printmaster.id', '0811000011', @pwd, 1),
(12, 1, 1, 10, 'Siska Amelia (Staf Purchasing)', 'purchasing1', 'purchasing@printmaster.id', '0811000012', @pwd, 1),
(13, 1, 1, 11, 'Wahyu Hidayat (Finance & Akuntansi)', 'akuntan1', 'akuntan@printmaster.id', '0811000013', @pwd, 1),
(14, 1, 1, 12, 'Maya Puspita (HRD Admin)', 'hrd1', 'hrd@printmaster.id', '0811000014', @pwd, 1),
(15, 1, 1, 13, 'Rudi Hermawan (Kurir Ekspedisi)', 'kurir1', 'kurir@printmaster.id', '0811000015', @pwd, 1),
(16, 1, 1, 14, 'Budi Santoso (Akun Pelanggan)', 'budi.santoso', 'budi@gmail.com', '081234567890', @pwd, 1);

-- User Branch Multi-assignment
INSERT IGNORE INTO user_branches (user_id, branch_id) VALUES
(1, 1), (1, 2), (1, 3), -- Admin can access all 3 branches
(2, 1), (2, 2), (2, 3), -- Owner can access all 3 branches
(3, 1),                 -- Kacab Jkt
(4, 2),                 -- Kacab Sby
(5, 1),                 -- Kasir Jkt
(6, 2),                 -- Kasir Sby
(7, 1), (7, 2),         -- Desainer melayani Jkt & Sby
(8, 1), (8, 2),         -- Prodman supervisi Jkt & Sby
(9, 1),                 -- Operator Jkt
(10, 1),                -- QC Jkt
(11, 1),                -- Gudang Jkt
(12, 1),                -- Purchasing Jkt
(13, 1), (13, 2), (13, 3), -- Akuntan konsolidasi
(14, 1),                -- HRD Jkt
(15, 1),                -- Kurir Jkt
(16, 1);                -- Pelanggan Jkt

-- 9. CHART OF ACCOUNTS (AKUNTANSI PERCETAKAN STANDAR)
INSERT IGNORE INTO accounts (id, company_id, code, name, type, normal_balance, parent_id, is_active, is_system) VALUES
-- Aktiva Lancar (1-1000)
(1, 1, '1111', 'Kas Kecil Cabang Jakarta', 'asset', 'debit', NULL, 1, 1),
(2, 1, '1112', 'Kas Kecil Cabang Surabaya', 'asset', 'debit', NULL, 1, 1),
(3, 1, '1113', 'Kas Kecil Cabang Bandung', 'asset', 'debit', NULL, 1, 1),
(4, 1, '1120', 'Bank BCA Operasional (Pusat)', 'asset', 'debit', NULL, 1, 1),
(5, 1, '1121', 'Bank Mandiri Giro', 'asset', 'debit', NULL, 1, 1),
(6, 1, '1130', 'Piutang Usaha Pelanggan', 'asset', 'debit', NULL, 1, 1),
(7, 1, '1140', 'Persediaan Bahan Baku Flexy & Vinyl', 'asset', 'debit', NULL, 1, 1),
(8, 1, '1141', 'Persediaan Kertas & Lembaran', 'asset', 'debit', NULL, 1, 1),
(9, 1, '1142', 'Persediaan Tinta & Toner', 'asset', 'debit', NULL, 1, 1),
(10, 1, '1143', 'Persediaan Bahan Finishing & Aksesoris', 'asset', 'debit', NULL, 1, 1),
-- Aktiva Tetap (1-2000)
(11, 1, '1210', 'Mesin Cetak & Peralatan Pabrik', 'asset', 'debit', NULL, 1, 1),
(12, 1, '1219', 'Akumulasi Penyusutan Mesin Cetak', 'asset', 'credit', NULL, 1, 1),
(13, 1, '1220', 'Kendaraan Operasional Kurir', 'asset', 'debit', NULL, 1, 1),
-- Kewajiban / Hutang (2-1000)
(14, 1, '2110', 'Hutang Dagang Supplier Bahan', 'liability', 'credit', NULL, 1, 1),
(15, 1, '2120', 'Pendapatan Diterima Dimuka (DP Order)', 'liability', 'credit', NULL, 1, 1),
(16, 1, '2130', 'Hutang Gaji Karyawan', 'liability', 'credit', NULL, 1, 1),
(17, 1, '2140', 'Hutang Pajak PPN', 'liability', 'credit', NULL, 1, 1),
-- Ekuitas / Modal (3-1000)
(18, 1, '3100', 'Modal Disetor Pemilik', 'equity', 'credit', NULL, 1, 1),
(19, 1, '3200', 'Laba Ditahan', 'equity', 'credit', NULL, 1, 1),
-- Pendapatan (4-1000)
(20, 1, '4110', 'Pendapatan Cetak Outdoor & Spanduk', 'revenue', 'credit', NULL, 1, 1),
(21, 1, '4120', 'Pendapatan Cetak Digital Print A3+', 'revenue', 'credit', NULL, 1, 1),
(22, 1, '4130', 'Pendapatan Cetak Offset & Buku', 'revenue', 'credit', NULL, 1, 1),
(23, 1, '4140', 'Pendapatan Cetak Stiker & Label', 'revenue', 'credit', NULL, 1, 1),
(24, 1, '4150', 'Pendapatan Jasa Finishing & Jilid', 'revenue', 'credit', NULL, 1, 1),
(25, 1, '4190', 'Potongan / Diskon Penjualan', 'revenue', 'debit', NULL, 1, 1),
-- Harga Pokok Penjualan (HPP) (5-1000)
(26, 1, '5110', 'HPP Bahan Baku Terpakai', 'expense', 'debit', NULL, 1, 1),
(27, 1, '5120', 'HPP Biaya Tinta & Cairan Cetak', 'expense', 'debit', NULL, 1, 1),
(28, 1, '5130', 'HPP Tenaga Kerja Langsung Produksi', 'expense', 'debit', NULL, 1, 1),
(29, 1, '5140', 'HPP Listrik & Daya Mesin Pabrik', 'expense', 'debit', NULL, 1, 1),
-- Beban Operasional (6-1000)
(30, 1, '6100', 'Beban Gaji Staf & Manajemen', 'expense', 'debit', NULL, 1, 1),
(31, 1, '6200', 'Beban Pemeliharaan & Servis Mesin', 'expense', 'debit', NULL, 1, 1),
(32, 1, '6300', 'Beban Sewa Gedung & Listrik Kantor', 'expense', 'debit', NULL, 1, 1),
(33, 1, '6400', 'Beban Transportasi & Pengiriman Kurir', 'expense', 'debit', NULL, 1, 1),
(34, 1, '6500', 'Beban Pemasaran & Promosi', 'expense', 'debit', NULL, 1, 1),
(35, 1, '6900', 'Beban Operasional Lain-lain', 'expense', 'debit', NULL, 1, 1);

-- 10. PELANGGAN (CUSTOMERS)
INSERT IGNORE INTO customers (id, company_id, branch_id, customer_code, type, name, company_name, email, phone, address, customer_tier, discount_percent, credit_limit, current_receivable, loyalty_points) VALUES
(1, 1, 1, 'CUST-001', 'individual', 'Budi Santoso', NULL, 'budi@gmail.com', '081234567890', 'Jl. Tebet Raya No. 15, Jakarta Selatan', 'regular', 0.00, 1000000.00, 0.00, 50),
(2, 1, 1, 'CUST-002', 'individual', 'Siti Nurhaliza', NULL, 'siti.nur@yahoo.com', '081398765432', 'Jl. Salemba Tengah No. 22, Jakarta Pusat', 'member', 5.00, 3000000.00, 0.00, 320),
(3, 1, 2, 'CUST-003', 'company', 'Percetakan Berkah Studio', 'CV Berkah Studio Kreatif', 'berkah.print@gmail.com', '085611223344', 'Jl. Rungkut Madya No. 44, Surabaya', 'reseller', 12.00, 10000000.00, 1500000.00, 1250),
(4, 1, 1, 'CUST-004', 'company', 'PT Maju Jaya Makmur', 'PT Maju Jaya Makmur Tbk', 'procurement@majujaya.co.id', '082199887766', 'Kawasan Industri Pulogadung Blok B No. 9, Jakarta Timur', 'corporate', 10.00, 25000000.00, 3500000.00, 4800);

-- 11. KATEGORI PRODUK
INSERT IGNORE INTO product_categories (id, company_id, code, name, description, icon, sort_order, is_active) VALUES
(1, 1, 'CAT-BANNER', 'Banner & Spanduk Outdoor', 'Cetak spanduk flexy, baliho, backlite, x-banner', 'bi-easel', 1, 1),
(2, 1, 'CAT-DIGITAL', 'Digital Print A3+', 'Cetak brosur, poster, flyer, sertifikat, menu resto', 'bi-file-earmark-richtext', 2, 1),
(3, 1, 'CAT-CARD', 'Kartu Nama & Undangan', 'Cetak kartu nama box, undangan pernikahan/event', 'bi-credit-card-2-front', 3, 1),
(4, 1, 'CAT-STICKER', 'Stiker & Label Kemasan', 'Cetak stiker vinyl, kromo, transparan, kiss cut / die cut', 'bi-stickies', 4, 1),
(5, 1, 'CAT-BOOK', 'Buku, Majalah & Kalender', 'Cetak buku jilid lem panas, majalah, kalender meja/dinding', 'bi-book', 5, 1),
(6, 1, 'CAT-MERCH', 'Merchandise & Sablon', 'Cetak sablon kaos DTF, mug keramik, payung, totebag', 'bi-cup-hot', 6, 1),
(7, 1, 'CAT-FINISH', 'Jasa Finishing & Penjilidan', 'Laminasi doff/glossy, jilid spiral kawat, jilid hardcover, pond', 'bi-scissors', 7, 1);

-- 12. PRODUK & LAYANAN
INSERT IGNORE INTO products (id, company_id, category_id, code, name, description, pricing_mode, base_price, min_price, min_order_qty, default_margin_percent, waste_percent, setup_cost, overhead_cost, is_active) VALUES
(1, 1, 1, 'PRD-BNR-280', 'Spanduk Flexy Standar 280g', 'Cetak banner luar ruangan bahan flexy 280 gram tajam dan tahan cuaca', 'area_sqm', 25000.00, 20000.00, 1, 35.00, 5.00, 0.00, 2000.00, 1),
(2, 1, 1, 'PRD-BNR-340', 'Spanduk Flexy Korea 340g (Highend)', 'Bahan flexy tebal serat halus tahan lama hingga 1-2 tahun', 'area_sqm', 40000.00, 35000.00, 1, 40.00, 5.00, 0.00, 3000.00, 1),
(3, 1, 1, 'PRD-XBNR-60', 'X-Banner 60x160cm Komplit Stand', 'Paket cetak bahan albatros/luster + standing rangka fiber X', 'pcs', 65000.00, 55000.00, 1, 35.00, 3.00, 0.00, 5000.00, 1),
(4, 1, 1, 'PRD-ROLL-85', 'Roll Up Banner Aluminium 85x200cm', 'Standing roll banner portable bahan aluminium tebal & mewah', 'pcs', 195000.00, 175000.00, 1, 30.00, 3.00, 0.00, 10000.00, 1),
(5, 1, 2, 'PRD-A3-AP150', 'Print A3+ Art Paper 150g (Brosur/Flyer)', 'Cetak laser digital resolusi tinggi full color 1 atau 2 sisi', 'sheet_a3', 4500.00, 3500.00, 1, 40.00, 2.00, 0.00, 500.00, 1),
(6, 1, 2, 'PRD-A3-AC260', 'Print A3+ Art Carton 260g (Poster/Cover)', 'Kertas tebal kaku cocok untuk cover buku, poster, kartu menu', 'sheet_a3', 5500.00, 4500.00, 1, 40.00, 2.00, 0.00, 500.00, 1),
(7, 1, 3, 'PRD-KRT-NAMA', 'Kartu Nama Box (100 pcs) AC 260g', 'Kartu nama standar box mika isi 100 lembar full color', 'pcs', 35000.00, 30000.00, 1, 45.00, 5.00, 5000.00, 2000.00, 1),
(8, 1, 4, 'PRD-STK-VINYL', 'Stiker Vinyl A3+ Kiss Cut (Tahan Air)', 'Stiker plastik putih anti sobek dan anti air sudah dicutting pola', 'sheet_a3', 18000.00, 15000.00, 1, 45.00, 4.00, 0.00, 1500.00, 1),
(9, 1, 4, 'PRD-STK-CROMO', 'Stiker Kromo A3+ Kiss Cut (Ekonomis)', 'Stiker bahan kertas kilap cocok label botol / toples kue', 'sheet_a3', 13000.00, 10000.00, 1, 40.00, 4.00, 0.00, 1000.00, 1),
(10, 1, 5, 'PRD-BK-A5-PERF', 'Cetak Buku A5 Jilid Lem Panas (Perfect Binding)', 'Buku isi bookpaper/HVS cover art carton laminating jilid rapi', 'custom', 35000.00, 25000.00, 10, 35.00, 8.00, 25000.00, 5000.00, 1);

-- 13. OPSI FINISHING
INSERT IGNORE INTO product_finishing (id, company_id, code, name, calculation_type, cost_price, selling_price, min_fee, is_active) VALUES
(1, 1, 'FIN-MATA-AYAM', 'Mata Ayam (Keling Lubang Gantungan)', 'per_pcs', 300.00, 1000.00, 0.00, 1),
(2, 1, 'FIN-LIPAT-PRES', 'Lipat Samping Lem Pres / Selongsong Bambu', 'fixed', 1500.00, 5000.00, 0.00, 1),
(3, 1, 'FIN-LAM-DOFF', 'Laminasi Doff Panas A3+ (1 Sisi)', 'per_side', 800.00, 2500.00, 0.00, 1),
(4, 1, 'FIN-LAM-GLOSS', 'Laminasi Glossy Panas A3+ (1 Sisi)', 'per_side', 800.00, 2500.00, 0.00, 1),
(5, 1, 'FIN-JILID-SPIRAL', 'Jilid Spiral Kawat + Mika Bening', 'per_pcs', 4000.00, 12000.00, 0.00, 1),
(6, 1, 'FIN-CUT-POLA', 'Cutting Kiss Cut Pola Khusus', 'per_pcs', 1000.00, 4000.00, 0.00, 1);

-- 14. BAHAN BAKU (MATERIALS)
INSERT IGNORE INTO materials (id, company_id, code, name, category, unit, min_stock, current_stock, avg_cost, last_purchase_price, is_active) VALUES
(1, 1, 'MAT-FLX-280', 'Flexy Frontlite Standar 280g (Rol 3.2m x 50m)', 'banner_flexy', 'm2', 320.00, 850.00, 8500.00, 8500.00, 1),
(2, 1, 'MAT-FLX-340', 'Flexy Korea Korchin 340g (Rol 3.2m x 50m)', 'banner_flexy', 'm2', 200.00, 480.00, 16000.00, 16000.00, 1),
(3, 1, 'MAT-ALB-180', 'Albatros Matte 180g (Rol 0.914m x 30m)', 'banner_flexy', 'm2', 50.00, 120.00, 18000.00, 18000.00, 1),
(4, 1, 'MAT-PPR-AP150', 'Kertas Art Paper 150g (Ukuran Plano 65x100cm)', 'paper', 'lembar', 500.00, 2400.00, 1100.00, 1100.00, 1),
(5, 1, 'MAT-PPR-AC260', 'Kertas Art Carton 260g (Plano 65x100cm)', 'paper', 'lembar', 500.00, 1850.00, 1750.00, 1750.00, 1),
(6, 1, 'MAT-STK-VINYL', 'Stiker Vinyl Glossy A3+ Merk Camel', 'sticker_vinyl', 'lembar', 300.00, 1200.00, 4500.00, 4500.00, 1),
(7, 1, 'MAT-STK-CROMO', 'Stiker Kromo Lintec A3+', 'sticker_vinyl', 'lembar', 300.00, 1500.00, 2800.00, 2800.00, 1),
(8, 1, 'MAT-INK-SOLVENT', 'Tinta Solvent Konica 512i (Galon 5 Liter Cyan/Mag/Yel/Blk)', 'ink_toner', 'liter', 20.00, 65.00, 140000.00, 140000.00, 1),
(9, 1, 'MAT-TON-XEROX', 'Toner Fuji Xerox Versant Set CMYK', 'ink_toner', 'set', 2.00, 6.00, 2800000.00, 2800000.00, 1),
(10, 1, 'MAT-FIN-EYELET', 'Mata Ayam Besi Nikel 10mm (Kotak 1000 pcs)', 'finishing_supplies', 'pcs', 2000.00, 15000.00, 120.00, 120.00, 1),
(11, 1, 'MAT-FIN-GLUE', 'Lem Panas Hotmelt Perfect Binding (Sak 25kg)', 'finishing_supplies', 'kg', 25.00, 95.00, 42000.00, 42000.00, 1);

-- 15. MESIN CETAK & PRODUKSI
INSERT IGNORE INTO machines (id, company_id, branch_id, code, name, brand_model, machine_type, capacity_per_hour, hourly_cost, meter_counter, status, last_maintenance_date) VALUES
(1, 1, 1, 'MSN-OUT-01', 'Mesin Outdoor Spectra Polaris 3.2m', 'Flora Polaris 512i High Speed', 'outdoor_flexy', 80.00, 65000.00, 14250.00, 'available', '2026-10-15'),
(2, 1, 1, 'MSN-IND-01', 'Mesin Indoor Eco-Solvent 1.6m', 'Epson SureColor SC-S80670 10 Warna', 'indoor_photo', 25.00, 45000.00, 6800.00, 'available', '2026-10-20'),
(3, 1, 1, 'MSN-DIG-01', 'Mesin Digital Press A3+', 'Fuji Xerox Versant 180 Press', 'digital_a3', 1200.00, 85000.00, 185000.00, 'available', '2026-11-01'),
(4, 1, 1, 'MSN-CUT-01', 'Mesin Cutting Plotter 130cm', 'Graphtec CE7000-130', 'cutting_plotter', 60.00, 25000.00, 3400.00, 'available', '2026-10-10'),
(5, 1, 1, 'MSN-LAM-01', 'Mesin Laminating Panas Roll 65cm', 'FM-650 Hot Roll Laminator', 'laminator', 150.00, 20000.00, 4200.00, 'available', '2026-10-25'),
(6, 1, 2, 'MSN-OUT-SBY', 'Mesin Outdoor Allwin Konica 3.2m', 'Allwin C8-512i', 'outdoor_flexy', 75.00, 60000.00, 8900.00, 'available', '2026-10-18'),
(7, 1, 2, 'MSN-DIG-SBY', 'Mesin Digital Konica Minolta A3+', 'AccurioPrint C3070L', 'digital_a3', 1000.00, 75000.00, 92000.00, 'available', '2026-10-28');

-- 16. SUPPLIERS (PEMASOK)
INSERT IGNORE INTO suppliers (id, company_id, code, name, contact_person, phone, email, address, tax_id, payment_terms_days, is_active) VALUES
(1, 1, 'SUP-001', 'PT Aneka Sumber Grafika', 'Iwan Gunawan', '021-6543210', 'sales@anekagrafika.co.id', 'Jl. Daan Mogot KM 12, Jakarta Barat', '01.555.444.3-031.000', 30, 1),
(2, 1, 'SUP-002', 'CV Solusi Visual Mandiri', 'Hendra Kusuma', '021-8899776', 'order@solusivisual.com', 'Jl. Kalibaru Timur No. 19, Senen, Jakarta Pusat', '02.444.333.2-022.000', 14, 1),
(3, 1, 'SUP-003', 'PT Digital Laser Prima', 'Surya Saputra', '031-5566778', 'info@digitallaser.co.id', 'Komp. Ruko Gateway Waru Blok C No. 5, Sidoarjo', '03.777.888.1-041.000', 30, 1);

-- 17. TEMPLATE NOTIFIKASI
INSERT IGNORE INTO notification_templates (id, company_id, event_code, title, message_template, channel, is_active) VALUES
(1, 1, 'order_confirmed', 'Order Berhasil Dikonfirmasi', 'Halo {{customer_name}}, order Anda no *{{order_number}}* telah diterima oleh {{branch_name}}. Total: Rp {{total_amount}}. Silakan pantau status pesanan Anda.', 'whatsapp', 1),
(2, 1, 'proof_ready', 'Proof Desain Siap Direview', 'Halo {{customer_name}}, draft proof desain untuk order *{{order_number}}* telah diunggah oleh desainer kami. Mohon periksa dan beri persetujuan melalui link: {{proof_url}}', 'whatsapp', 1),
(3, 1, 'order_ready', 'Pesanan Siap Diambil', 'Kabar gembira! Pesanan Anda *{{order_number}}* telah selesai diproduksi dan siap diambil di {{branch_name}}. Silakan tunjukkan nota ini kepada kasir kami.', 'whatsapp', 1),
(4, 1, 'payment_received', 'Konfirmasi Pembayaran Diterima', 'Terima kasih, pembayaran sebesar *Rp {{payment_amount}}* untuk order *{{order_number}}* telah kami terima dengan referensi {{payment_ref}}.', 'whatsapp', 1);

-- 18. PROMOSI & VOUCHER
INSERT IGNORE INTO promotions (id, company_id, code, title, discount_type, discount_value, min_purchase, max_discount, quota, used_count, start_date, end_date, is_active) VALUES
(1, 1, 'HEMAT10', 'Promo Pelanggan Baru 10%', 'percentage', 10.00, 100000.00, 50000.00, 100, 12, '2026-01-01', '2026-12-31', 1),
(2, 1, 'CETAKBANNER', 'Potongan Banner Flexy Rp 20.000', 'fixed', 20000.00, 150000.00, 20000.00, 50, 8, '2026-01-01', '2026-12-31', 1);

-- 19. CONTOH TRANSAKSI AWAL REALISTIS
-- Order 1: Selesai & Lunas (Jakarta)
INSERT IGNORE INTO orders (id, company_id, branch_id, order_number, invoice_number, customer_id, order_date, deadline_date, pickup_delivery_type, subtotal, discount_amount, voucher_code, tax_amount, total_amount, paid_amount, remaining_amount, payment_status, status, notes, created_by)
VALUES (1, 1, 1, 'ORD-202611-0001', 'INV-JKT-2611-001', 1, '2026-11-10', '2026-11-11 17:00:00', 'pickup', 150000.00, 0.00, NULL, 0.00, 150000.00, 150000.00, 0.00, 'paid', 'completed', 'Spanduk warung makan 3x2 meter mata ayam 4 sudut', 5);

INSERT IGNORE INTO order_items (id, order_id, product_id, item_name, description, specs_json, width_cm, height_cm, area_sqm, quantity, unit, unit_price, cost_estimate, subtotal, discount, tax, total, finishing_details, price_snapshot_json)
VALUES (1, 1, 1, 'Spanduk Flexy Standar 280g', 'Ukuran 300cm x 200cm, mata ayam 4 sudut', '{"width": 300, "height": 200, "finishing": ["mata_ayam"]}', 300.00, 200.00, 6.0000, 1.00, 'pcs', 25000.00, 60000.00, 150000.00, 0.00, 0.00, 150000.00, 'Mata ayam 4 lubang di tiap sudut', '{"base_rate": 25000, "area": 6.0}');

INSERT IGNORE INTO payments (id, company_id, branch_id, order_id, payment_number, payment_type, payment_method, amount, payment_date, reference_number, verified_by, status, idempotency_key, notes, created_by)
VALUES (1, 1, 1, 1, 'PAY-JKT-2611-0001', 'order_payment', 'cash', 150000.00, '2026-11-10 10:15:00', 'CASH-001', 5, 'verified', 'IDEMP-PAY-1', 'Pembayaran tunai kasir Jakarta lunas di awal', 5);

-- Order 2: Sedang Produksi & DP Sebagian (Jakarta)
INSERT IGNORE INTO orders (id, company_id, branch_id, order_number, invoice_number, customer_id, order_date, deadline_date, pickup_delivery_type, subtotal, discount_amount, voucher_code, tax_amount, total_amount, paid_amount, remaining_amount, payment_status, status, notes, created_by)
VALUES (2, 1, 1, 'ORD-202611-0002', 'INV-JKT-2611-002', 4, '2026-11-12', '2026-11-14 15:00:00', 'internal_courier', 700000.00, 70000.00, 'HEMAT10', 0.00, 630000.00, 300000.00, 330000.00, 'partial', 'in_production', 'Brosur company profile dan x-banner standing', 5);

INSERT IGNORE INTO order_items (id, order_id, product_id, item_name, description, specs_json, width_cm, height_cm, area_sqm, quantity, unit, unit_price, cost_estimate, subtotal, discount, tax, total, finishing_details, price_snapshot_json)
VALUES
(2, 2, 5, 'Print A3+ Art Paper 150g (Brosur)', 'Brosur 100 lembar A3 lipat 3 full color', '{"sheets": 100}', 0.00, 0.00, 0.0000, 100.00, 'lembar', 4500.00, 180000.00, 450000.00, 45000.00, 0.00, 405000.00, 'Lipat 3 rapi', '{"rate": 4500}'),
(3, 2, 3, 'X-Banner 60x160cm Komplit Stand', 'X-Banner 2 unit albatros + laminasi doff', '{"qty": 2}', 60.00, 160.00, 0.9600, 2.00, 'pcs', 125000.00, 90000.00, 250000.00, 25000.00, 0.00, 225000.00, 'Laminasi doff tahan gores', '{"rate": 125000}');

INSERT IGNORE INTO payments (id, company_id, branch_id, order_id, payment_number, payment_type, payment_method, amount, payment_date, reference_number, verified_by, status, idempotency_key, notes, created_by)
VALUES (2, 1, 1, 2, 'PAY-JKT-2611-0002', 'order_payment', 'bank_transfer', 300000.00, '2026-11-12 11:30:00', 'TRF-BCA-987654', 13, 'verified', 'IDEMP-PAY-2', 'DP 50% via transfer BCA', 5);

-- Job Produksi untuk Order 2
INSERT IGNORE INTO production_jobs (id, company_id, source_branch_id, target_branch_id, order_id, order_item_id, job_number, machine_id, operator_id, priority, status, estimated_duration_minutes, scheduled_date, notes)
VALUES
(1, 1, 1, 1, 2, 2, 'JOB-2611-0001', 3, 9, 'normal', 'in_progress', 45, '2026-11-13', 'Cetak brosur art paper 150g 100 lbr'),
(2, 1, 1, 1, 2, 3, 'JOB-2611-0002', 2, 9, 'high', 'queued', 30, '2026-11-13', 'Cetak albatros x-banner 2 unit');

INSERT IGNORE INTO production_steps (id, production_job_id, step_name, sequence, status, operator_id, started_at)
VALUES
(1, 1, 'prepress', 1, 'completed', 7, '2026-11-13 08:30:00'),
(2, 1, 'printing', 2, 'in_progress', 9, '2026-11-13 09:00:00'),
(3, 1, 'finishing', 3, 'pending', 9, NULL),
(4, 1, 'packing', 4, 'pending', 9, NULL);

-- Order 3: Pesanan Surabaya (Surabaya)
INSERT IGNORE INTO orders (id, company_id, branch_id, order_number, invoice_number, customer_id, order_date, deadline_date, pickup_delivery_type, subtotal, discount_amount, voucher_code, tax_amount, total_amount, paid_amount, remaining_amount, payment_status, status, notes, created_by)
VALUES (3, 1, 2, 'ORD-202611-0003', 'INV-SBY-2611-001', 3, '2026-11-11', '2026-11-12 18:00:00', 'pickup', 450000.00, 54000.00, NULL, 0.00, 396000.00, 396000.00, 0.00, 'paid', 'completed', 'Stiker vinyl label kemasan botol reseller Surabaya', 6);

INSERT IGNORE INTO order_items (id, order_id, product_id, item_name, description, specs_json, width_cm, height_cm, area_sqm, quantity, unit, unit_price, cost_estimate, subtotal, discount, tax, total, finishing_details, price_snapshot_json)
VALUES (4, 3, 8, 'Stiker Vinyl A3+ Kiss Cut', 'Label botol kopi literan 25 lembar A3+ cutting pola bulat', '{"sheets": 25}', 0.00, 0.00, 0.0000, 25.00, 'sheet_a3', 18000.00, 112500.00, 450000.00, 54000.00, 0.00, 396000.00, 'Kiss cut pola diameter 5cm', '{"rate": 18000}');

INSERT IGNORE INTO payments (id, company_id, branch_id, order_id, payment_number, payment_type, payment_method, amount, payment_date, reference_number, verified_by, status, idempotency_key, notes, created_by)
VALUES (3, 1, 2, 3, 'PAY-SBY-2611-0001', 'order_payment', 'qris', 396000.00, '2026-11-11 14:20:00', 'QRIS-SBY-099182', 6, 'verified', 'IDEMP-PAY-3', 'Pembayaran QRIS lunas cabang Surabaya', 6);

-- 20. JURNAL OTOMATIS SAMPLE (DOUBLE-ENTRY BALANCED)
-- Jurnal Order 1 Lunas Tunai Rp 150.000
-- Dr. Kas Kecil Jkt (1111) 150.000
-- Cr. Pendapatan Cetak Outdoor (4110) 150.000
INSERT IGNORE INTO journals (id, company_id, branch_id, journal_number, journal_date, reference_type, reference_id, description, total_debit, total_credit, status, created_by)
VALUES (1, 1, 1, 'JRN-202611-0001', '2026-11-10', 'order', 1, 'Penjualan spanduk ORD-202611-0001 lunas tunai', 150000.00, 150000.00, 'posted', 5);

INSERT IGNORE INTO journal_details (journal_id, account_id, debit, credit, memo) VALUES
(1, 1, 150000.00, 0.00, 'Penerimaan kas tunai'),
(1, 20, 0.00, 150000.00, 'Pendapatan cetak outdoor');

-- Jurnal Order 2 DP BCA Rp 300.000 + Piutang Rp 330.000
-- Dr. Bank BCA (1120) 300.000
-- Dr. Piutang Usaha (1130) 330.000
-- Cr. Pendapatan Cetak Digital (4120) 405.000
-- Cr. Pendapatan Cetak Outdoor (4110) 225.000
INSERT IGNORE INTO journals (id, company_id, branch_id, journal_number, journal_date, reference_type, reference_id, description, total_debit, total_credit, status, created_by)
VALUES (2, 1, 1, 'JRN-202611-0002', '2026-11-12', 'order', 2, 'Penjualan brosur & banner ORD-202611-0002 DP transfer BCA', 630000.00, 630000.00, 'posted', 5);

INSERT IGNORE INTO journal_details (journal_id, account_id, debit, credit, memo) VALUES
(2, 4, 300000.00, 0.00, 'Penerimaan transfer Bank BCA'),
(2, 6, 330000.00, 0.00, 'Piutang sisa pembayaran'),
(2, 21, 0.00, 405000.00, 'Pendapatan cetak digital A3+'),
(2, 20, 0.00, 225000.00, 'Pendapatan cetak x-banner');

-- Jurnal Order 3 Surabaya QRIS Rp 396.000
-- Dr. Kas Kecil Sby (1112) 396.000
-- Cr. Pendapatan Stiker & Label (4140) 396.000
INSERT IGNORE INTO journals (id, company_id, branch_id, journal_number, journal_date, reference_type, reference_id, description, total_debit, total_credit, status, created_by)
VALUES (3, 1, 2, 'JRN-202611-0003', '2026-11-11', 'order', 3, 'Penjualan stiker vinyl ORD-202611-0003 QRIS Surabaya', 396000.00, 396000.00, 'posted', 6);

INSERT IGNORE INTO journal_details (journal_id, account_id, debit, credit, memo) VALUES
(3, 2, 396000.00, 0.00, 'Penerimaan QRIS Surabaya'),
(3, 23, 0.00, 396000.00, 'Pendapatan stiker vinyl');

-- 21. KARYAWAN & ABSENSI SAMPLE
INSERT IGNORE INTO employees (id, company_id, branch_id, user_id, employee_code, name, position, department, phone, email, join_date, basic_salary, allowance_fixed, status) VALUES
(1, 1, 1, 3, 'EMP-JKT-001', 'Bambang Sudiro', 'Kepala Cabang', 'Manajemen', '0811000003', 'kacab.jkt@printmaster.id', '2024-01-15', 8500000.00, 1500000.00, 'active'),
(2, 1, 1, 5, 'EMP-JKT-002', 'Rina Marlina', 'Kasir Senior', 'Front Office', '0811000005', 'kasir.jkt@printmaster.id', '2024-03-01', 4800000.00, 500000.00, 'active'),
(3, 1, 1, 7, 'EMP-JKT-003', 'Dimas Anggoro', 'Desainer Grafis', 'Kreatif', '0811000007', 'desainer@printmaster.id', '2024-02-10', 5200000.00, 600000.00, 'active'),
(4, 1, 1, 9, 'EMP-JKT-004', 'Joko Susilo', 'Operator Mesin Outdoor', 'Produksi', '0811000009', 'operator@printmaster.id', '2024-01-20', 4900000.00, 400000.00, 'active'),
(5, 1, 2, 4, 'EMP-SBY-001', 'Dewi Lestari', 'Kepala Cabang', 'Manajemen', '0811000004', 'kacab.sby@printmaster.id', '2024-05-01', 7500000.00, 1200000.00, 'active');

INSERT IGNORE INTO employee_shifts (id, company_id, name, start_time, end_time, late_tolerance_minutes) VALUES
(1, 1, 'Shift Pagi', '08:00:00', '16:00:00', 15),
(2, 1, 'Shift Siang', '13:00:00', '21:00:00', 15);

INSERT IGNORE INTO employee_attendance (id, employee_id, branch_id, date, check_in, check_out, status, notes) VALUES
(1, 1, 1, '2026-11-13', '07:55:00', NULL, 'present', 'Hadir tepat waktu'),
(2, 2, 1, '2026-11-13', '07:58:00', NULL, 'present', 'Hadir tepat waktu'),
(3, 3, 1, '2026-11-13', '08:05:00', NULL, 'present', 'Hadir tepat waktu'),
(4, 4, 1, '2026-11-13', '07:50:00', NULL, 'present', 'Hadir tepat waktu');

-- 22. SHIFT KASIR TERBUKA (HARI INI)
INSERT IGNORE INTO cash_shifts (id, branch_id, cash_register_id, user_id, opening_time, opening_balance, cash_sales, cash_refunds, expected_balance, actual_balance, difference, status)
VALUES (1, 1, 1, 5, '2026-11-13 08:00:00', 300000.00, 0.00, 0.00, 300000.00, 0.00, 0.00, 'open');
