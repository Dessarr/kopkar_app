-- =====================================================
-- KOPERASI MANAGEMENT SYSTEM DATABASE SCHEMA
-- Author: Database Designer
-- Version: 1.0
-- Created: 2025
-- =====================================================

/*
PANDUAN DOKUMENTASI DATABASE KOPERASI

1. STRUKTUR MODUL:
   a. Manajemen Pengguna & Anggota
      - akun: Tabel utama untuk autentikasi pengguna
      - akun_admin: Informasi khusus admin
      - akun_member: Informasi khusus anggota
      - anggota: Data lengkap anggota koperasi
   
   b. Manajemen Keuangan
      - simpanan: Pengelolaan simpanan anggota
      - pinjaman: Pengelolaan pinjaman anggota
      - gaji: Integrasi dengan sistem penggajian
      - kas: Manajemen kas dan transaksi keuangan
   
   c. Unit Usaha
      - toserda: Manajemen toko serba ada
      - angkutan: Manajemen angkutan karyawan
   
   d. Sistem Pendukung
      - notifikasi: Sistem notifikasi otomatis
      - laporan: Sistem pelaporan
      - audit: Sistem audit trail

2. KONVENSI PENAMAAN:
   - Tabel: Menggunakan kata benda, lowercase dengan underscore
   - Kolom: Menggunakan snake_case
   - Foreign Key: [nama_tabel]_id
   - Timestamp: created_at, updated_at
   - Status: Menggunakan enum

3. TIPE DATA UMUM:
   - ID: VARCHAR(36) dengan UUID()
   - Nama/Deskripsi: VARCHAR(255)
   - Jumlah/Nominal: DECIMAL(15,2)
   - Status: ENUM
   - Timestamp: TIMESTAMP

4. RELASI ANTAR TABEL:
   - One-to-One: akun -> akun_admin
   - One-to-Many: anggota -> simpanan
   - Many-to-Many: (menggunakan tabel perantara)

5. KEAMANAN:
   - Password di-hash menggunakan bcrypt
   - Audit trail untuk semua operasi kritis
   - Validasi input melalui constraints
   - Pembatasan akses database user

6. PERFORMANCE:
   - Indeks pada kolom yang sering di-query
   - Generated columns untuk perhitungan
   - Views untuk reporting
   - Stored procedures untuk operasi kompleks

7. MAINTENANCE:
   - Backup harian otomatis
   - Pembersihan log berkala
   - Update statistik indeks
   - Pengecekan integritas data

8. CATATAN PENTING:
   - Selalu gunakan transaksi untuk operasi multi-tabel
   - Validasi data sebelum insert/update
   - Gunakan stored procedure untuk operasi kompleks
   - Monitor penggunaan indeks
*/

DROP DATABASE IF EXISTS koperasi_management;
CREATE DATABASE koperasi_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE koperasi_management;

-- =====================================================
-- ENABLE FOREIGN KEY CHECKS AND SET SQL MODE
-- =====================================================
SET foreign_key_checks = 1;
SET sql_mode = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- =====================================================
-- CREATE MASTER TABLES FIRST
-- =====================================================

-- Master table for pekerjaan
CREATE TABLE pekerjaan (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    nama VARCHAR(100) NOT NULL,    -- nama pekerjaan
    keterangan VARCHAR(255)        -- informasi tambahan
) ENGINE=InnoDB COMMENT='Master pekerjaan anggota';

-- Master table for jabatan
CREATE TABLE jabatan (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    nama VARCHAR(100) NOT NULL,    -- nama jabatan
    keterangan VARCHAR(255)        -- informasi tambahan
) ENGINE=InnoDB COMMENT='Master jabatan anggota';

-- Create indexes for master tables
CREATE INDEX idx_pekerjaan_nama ON pekerjaan(nama);
CREATE INDEX idx_jabatan_nama ON jabatan(nama);

-- =====================================================
-- USER MANAGEMENT TABLES
-- =====================================================

-- Main account table for authentication
CREATE TABLE akun (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role ENUM('admin', 'member') NOT NULL DEFAULT 'member',
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    email_verified BOOLEAN NOT NULL DEFAULT FALSE,
    failed_login_attempts INT NOT NULL DEFAULT 0,
    locked_until TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    created_by VARCHAR(36) NULL,
    
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_is_active (is_active),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB COMMENT='User authentication and authorization table';

-- Admin-specific information
CREATE TABLE akun_admin (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    akun_id VARCHAR(36) NOT NULL UNIQUE,
    employee_id VARCHAR(20) UNIQUE,
    department VARCHAR(50),
    position VARCHAR(50),
    permissions JSON,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    
    FOREIGN KEY (akun_id) REFERENCES akun(id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_employee_id (employee_id),
    INDEX idx_department (department),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB COMMENT='Admin-specific account information';

-- HRD table for external HR system integration
CREATE TABLE hrd (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    department VARCHAR(50),
    employee_id VARCHAR(20) UNIQUE,
    phone VARCHAR(20),
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_employee_id (employee_id),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB COMMENT='HR department users for payroll integration';

-- Main member information
CREATE TABLE anggota (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    member_code VARCHAR(20) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    alamat TEXT,
    no_ktp VARCHAR(20) UNIQUE,
    tempat_lahir VARCHAR(50),
    tanggal_lahir DATE,
    jenis_kelamin ENUM('L', 'P'),
    phone VARCHAR(20),
    email VARCHAR(100),
    pekerjaan VARCHAR(50),
    tanggal_gabung DATE NOT NULL DEFAULT (CURRENT_DATE),
    tanggal_keluar DATE NULL,
    status ENUM('aktif', 'non_aktif', 'suspend', 'keluar') NOT NULL DEFAULT 'aktif',
    alasan_keluar TEXT NULL,
    gaji_pokok DECIMAL(15,2) DEFAULT 0.00,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by VARCHAR(36),
    updated_by VARCHAR(36),
    pekerjaan_id VARCHAR(36),
    jabatan_id VARCHAR(36),
    
    INDEX idx_member_code (member_code),
    INDEX idx_nama (nama),
    INDEX idx_no_ktp (no_ktp),
    INDEX idx_status (status),
    INDEX idx_tanggal_gabung (tanggal_gabung),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (created_by) REFERENCES akun(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES akun(id) ON DELETE SET NULL,
    FOREIGN KEY (pekerjaan_id) REFERENCES pekerjaan(id) ON DELETE SET NULL,
    FOREIGN KEY (jabatan_id) REFERENCES jabatan(id) ON DELETE SET NULL
) ENGINE=InnoDB COMMENT='Cooperative members information';

-- Member account linking
CREATE TABLE akun_member (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    akun_id VARCHAR(36) NOT NULL UNIQUE,
    anggota_id VARCHAR(36) NOT NULL UNIQUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    
    FOREIGN KEY (akun_id) REFERENCES akun(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (anggota_id) REFERENCES anggota(id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB COMMENT='Link between accounts and members';

-- =====================================================
-- FINANCIAL MANAGEMENT TABLES
-- =====================================================

/*
Tabel simpanan:
- Mengelola semua jenis simpanan anggota
- Tracking saldo dan transaksi
- Perhitungan bunga otomatis
- Validasi minimal setoran

Tabel pinjaman:
- Manajemen pengajuan dan persetujuan pinjaman
- Perhitungan angsuran otomatis
- Tracking pembayaran dan tunggakan
- Validasi limit pinjaman
*/

-- Payroll/Salary information
CREATE TABLE gaji (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    anggota_id VARCHAR(36) NOT NULL,
    periode_bulan INT NOT NULL CHECK (periode_bulan BETWEEN 1 AND 12),
    periode_tahun YEAR NOT NULL,
    gaji_pokok DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    tunjangan_total DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    overtime_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    bonus DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    gross_salary DECIMAL(15,2) GENERATED ALWAYS AS (gaji_pokok + tunjangan_total + overtime_amount + bonus) STORED,
    potongan_pajak DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    potongan_bpjs DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    potongan_lainnya DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    potongan_koperasi DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    total_potongan DECIMAL(15,2) GENERATED ALWAYS AS (potongan_pajak + potongan_bpjs + potongan_lainnya + potongan_koperasi) STORED,
    net_salary DECIMAL(15,2) GENERATED ALWAYS AS (gross_salary - total_potongan) STORED,
    tanggal_bayar DATE,
    status_bayar ENUM('pending', 'processed', 'paid', 'cancelled') NOT NULL DEFAULT 'pending',
    keterangan TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by VARCHAR(36),
    processed_by VARCHAR(36),
    
    UNIQUE KEY unique_salary_period (anggota_id, periode_bulan, periode_tahun),
    INDEX idx_periode (periode_tahun, periode_bulan),
    INDEX idx_anggota_periode (anggota_id, periode_tahun, periode_bulan),
    INDEX idx_status_bayar (status_bayar),
    INDEX idx_tanggal_bayar (tanggal_bayar),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (anggota_id) REFERENCES anggota(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (created_by) REFERENCES akun(id) ON DELETE SET NULL,
    FOREIGN KEY (processed_by) REFERENCES akun(id) ON DELETE SET NULL
) ENGINE=InnoDB COMMENT='Member salary and payroll information';

-- Salary deduction details
CREATE TABLE potongan_gaji_detail (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    gaji_id VARCHAR(36) NOT NULL,
    jenis_potongan ENUM('pajak', 'bpjs_kesehatan', 'bpjs_tk', 'pinjaman_koperasi', 'simpanan_wajib', 'lainnya') NOT NULL,
    nominal DECIMAL(15,2) NOT NULL,
    keterangan VARCHAR(255),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_gaji_id (gaji_id),
    INDEX idx_jenis_potongan (jenis_potongan),
    FOREIGN KEY (gaji_id) REFERENCES gaji(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB COMMENT='Detailed salary deductions';

-- HRD data validation for payroll
CREATE TABLE data_potongan (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    hrd_id VARCHAR(36) NOT NULL,
    anggota_id VARCHAR(36) NOT NULL,
    periode_bulan INT NOT NULL CHECK (periode_bulan BETWEEN 1 AND 12),
    periode_tahun YEAR NOT NULL,
    jenis_potongan VARCHAR(50) NOT NULL,
    nominal DECIMAL(15,2) NOT NULL,
    keterangan TEXT,
    file_bukti VARCHAR(255),
    tanggal_input DATE NOT NULL DEFAULT (CURRENT_DATE),
    status_validasi ENUM('pending', 'valid', 'invalid', 'need_revision') NOT NULL DEFAULT 'pending',
    catatan_validasi TEXT,
    admin_validasi_id VARCHAR(36),
    tanggal_validasi TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_hrd_id (hrd_id),
    INDEX idx_anggota_id (anggota_id),
    INDEX idx_periode (periode_tahun, periode_bulan),
    INDEX idx_status_validasi (status_validasi),
    INDEX idx_tanggal_input (tanggal_input),
    FOREIGN KEY (hrd_id) REFERENCES hrd(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (anggota_id) REFERENCES anggota(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (admin_validasi_id) REFERENCES akun_admin(id) ON DELETE SET NULL
) ENGINE=InnoDB COMMENT='HRD payroll deduction data for validation';

-- =====================================================
-- SAVINGS MANAGEMENT
-- =====================================================

-- Savings types configuration
CREATE TABLE jenis_simpanan (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    kode VARCHAR(10) NOT NULL UNIQUE,
    nama VARCHAR(50) NOT NULL,
    deskripsi TEXT,
    minimal_setoran DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    maksimal_setoran DECIMAL(15,2) NULL,
    bunga_persen DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    is_wajib BOOLEAN NOT NULL DEFAULT FALSE,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_kode (kode),
    INDEX idx_is_wajib (is_wajib),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB COMMENT='Types of savings configuration';

-- Member savings
CREATE TABLE simpanan (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    anggota_id VARCHAR(36) NOT NULL,
    jenis_simpanan_id VARCHAR(36) NOT NULL,
    nomor_rekening VARCHAR(20) NOT NULL UNIQUE,
    saldo_awal DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    saldo_akhir DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    tanggal_buka DATE NOT NULL DEFAULT (CURRENT_DATE),
    tanggal_tutup DATE NULL,
    status ENUM('aktif', 'tutup', 'suspend') NOT NULL DEFAULT 'aktif',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by VARCHAR(36),
    
    INDEX idx_anggota_id (anggota_id),
    INDEX idx_jenis_simpanan_id (jenis_simpanan_id),
    INDEX idx_nomor_rekening (nomor_rekening),
    INDEX idx_status (status),
    INDEX idx_tanggal_buka (tanggal_buka),
    FOREIGN KEY (anggota_id) REFERENCES anggota(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (jenis_simpanan_id) REFERENCES jenis_simpanan(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (created_by) REFERENCES akun(id) ON DELETE SET NULL
) ENGINE=InnoDB COMMENT='Member savings accounts';

-- Savings transactions
CREATE TABLE transaksi_simpanan (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    simpanan_id VARCHAR(36) NOT NULL,
    jenis_transaksi ENUM('setor', 'tarik', 'bunga', 'koreksi', 'transfer_masuk', 'transfer_keluar') NOT NULL,
    nominal DECIMAL(15,2) NOT NULL,
    saldo_sebelum DECIMAL(15,2) NOT NULL,
    saldo_sesudah DECIMAL(15,2) NOT NULL,
    tanggal_transaksi TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    keterangan TEXT,
    referensi VARCHAR(50),
    bukti_transaksi VARCHAR(255),
    processed_by VARCHAR(36),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_simpanan_id (simpanan_id),
    INDEX idx_jenis_transaksi (jenis_transaksi),
    INDEX idx_tanggal_transaksi (tanggal_transaksi),
    INDEX idx_referensi (referensi),
    FOREIGN KEY (simpanan_id) REFERENCES simpanan(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (processed_by) REFERENCES akun(id) ON DELETE SET NULL
) ENGINE=InnoDB COMMENT='Savings account transactions';

-- =====================================================
-- LOAN MANAGEMENT
-- =====================================================

-- Loan types configuration
CREATE TABLE jenis_pinjaman (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    kode VARCHAR(10) NOT NULL UNIQUE,
    nama VARCHAR(50) NOT NULL,
    deskripsi TEXT,
    bunga_persen DECIMAL(5,2) NOT NULL,
    minimal_pinjaman DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    maksimal_pinjaman DECIMAL(15,2) NULL,
    jangka_waktu_max INT NOT NULL COMMENT 'in months',
    require_guarantor BOOLEAN NOT NULL DEFAULT FALSE,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_kode (kode),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB COMMENT='Loan types configuration';

-- Loan applications and management
CREATE TABLE pinjaman (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    nomor_pinjaman VARCHAR(20) NOT NULL UNIQUE,
    anggota_id VARCHAR(36) NOT NULL,
    jenis_pinjaman_id VARCHAR(36) NOT NULL,
    nominal_pengajuan DECIMAL(15,2) NOT NULL,
    nominal_disetujui DECIMAL(15,2) NULL,
    jangka_waktu INT NOT NULL COMMENT 'in months',
    bunga_persen DECIMAL(5,2) NOT NULL,
    total_bunga DECIMAL(15,2) GENERATED ALWAYS AS (ROUND((nominal_disetujui * bunga_persen * jangka_waktu) / 100, 2)) STORED,
    total_pinjaman DECIMAL(15,2) GENERATED ALWAYS AS (nominal_disetujui + total_bunga) STORED,
    angsuran_pokok DECIMAL(15,2) GENERATED ALWAYS AS (ROUND(nominal_disetujui / jangka_waktu, 2)) STORED,
    angsuran_bunga DECIMAL(15,2) GENERATED ALWAYS AS (ROUND(total_bunga / jangka_waktu, 2)) STORED,
    angsuran_per_bulan DECIMAL(15,2) GENERATED ALWAYS AS (angsuran_pokok + angsuran_bunga) STORED,
    tanggal_pengajuan DATE NOT NULL DEFAULT (CURRENT_DATE),
    tanggal_persetujuan DATE NULL,
    tanggal_pencairan DATE NULL,
    tanggal_jatuh_tempo DATE NULL,
    status ENUM('draft', 'submitted', 'under_review', 'approved', 'rejected', 'disbursed', 'active', 'completed', 'overdue', 'written_off') NOT NULL DEFAULT 'draft',
    alasan_penolakan TEXT NULL,
    tujuan_pinjaman TEXT,
    jaminan TEXT,
    catatan TEXT,
    admin_review_id VARCHAR(36),
    admin_approval_id VARCHAR(36),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by VARCHAR(36),
    
    INDEX idx_nomor_pinjaman (nomor_pinjaman),
    INDEX idx_anggota_id (anggota_id),
    INDEX idx_jenis_pinjaman_id (jenis_pinjaman_id),
    INDEX idx_status (status),
    INDEX idx_tanggal_pengajuan (tanggal_pengajuan),
    INDEX idx_tanggal_jatuh_tempo (tanggal_jatuh_tempo),
    FOREIGN KEY (anggota_id) REFERENCES anggota(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (jenis_pinjaman_id) REFERENCES jenis_pinjaman(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (admin_review_id) REFERENCES akun_admin(id) ON DELETE SET NULL,
    FOREIGN KEY (admin_approval_id) REFERENCES akun_admin(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES akun(id) ON DELETE SET NULL
) ENGINE=InnoDB COMMENT='Loan applications and management';

-- Loan installment schedule
CREATE TABLE jadwal_angsuran (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    pinjaman_id VARCHAR(36) NOT NULL,
    angsuran_ke INT NOT NULL,
    tanggal_jatuh_tempo DATE NOT NULL,
    nominal_pokok DECIMAL(15,2) NOT NULL,
    nominal_bunga DECIMAL(15,2) NOT NULL,
    total_angsuran DECIMAL(15,2) GENERATED ALWAYS AS (nominal_pokok + nominal_bunga) STORED,
    saldo_pinjaman DECIMAL(15,2) NOT NULL,
    status ENUM('belum_bayar', 'sebagian', 'lunas', 'overdue') NOT NULL DEFAULT 'belum_bayar',
    tanggal_bayar DATE NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_installment (pinjaman_id, angsuran_ke),
    INDEX idx_pinjaman_id (pinjaman_id),
    INDEX idx_tanggal_jatuh_tempo (tanggal_jatuh_tempo),
    INDEX idx_status (status),
    FOREIGN KEY (pinjaman_id) REFERENCES pinjaman(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB COMMENT='Loan installment schedule';

-- Loan payments/installments
CREATE TABLE pembayaran_angsuran (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    pinjaman_id VARCHAR(36) NOT NULL,
    jadwal_angsuran_id VARCHAR(36) NULL,
    nominal_dibayar DECIMAL(15,2) NOT NULL,
    nominal_pokok DECIMAL(15,2) NOT NULL,
    nominal_bunga DECIMAL(15,2) NOT NULL,
    denda DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    total_pembayaran DECIMAL(15,2) GENERATED ALWAYS AS (nominal_dibayar + denda) STORED,
    tanggal_bayar DATE NOT NULL DEFAULT (CURRENT_DATE),
    metode_bayar ENUM('tunai', 'transfer', 'potong_gaji', 'potong_simpanan') NOT NULL,
    keterangan TEXT,
    bukti_bayar VARCHAR(255),
    processed_by VARCHAR(36),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_pinjaman_id (pinjaman_id),
    INDEX idx_jadwal_angsuran_id (jadwal_angsuran_id),
    INDEX idx_tanggal_bayar (tanggal_bayar),
    INDEX idx_metode_bayar (metode_bayar),
    FOREIGN KEY (pinjaman_id) REFERENCES pinjaman(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (jadwal_angsuran_id) REFERENCES jadwal_angsuran(id) ON DELETE SET NULL,
    FOREIGN KEY (processed_by) REFERENCES akun(id) ON DELETE SET NULL
) ENGINE=InnoDB COMMENT='Loan payment transactions';

-- Salary deduction for loans
CREATE TABLE potongan_gaji_pinjaman (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    pinjaman_id VARCHAR(36) NOT NULL,
    gaji_id VARCHAR(36) NOT NULL,
    nominal DECIMAL(15,2) NOT NULL,
    tanggal_potong DATE NOT NULL,
    status ENUM('scheduled', 'processed', 'failed') NOT NULL DEFAULT 'scheduled',
    keterangan TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    processed_at TIMESTAMP NULL,
    processed_by VARCHAR(36),
    
    INDEX idx_pinjaman_id (pinjaman_id),
    INDEX idx_gaji_id (gaji_id),
    INDEX idx_tanggal_potong (tanggal_potong),
    INDEX idx_status (status),
    FOREIGN KEY (pinjaman_id) REFERENCES pinjaman(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (gaji_id) REFERENCES gaji(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (processed_by) REFERENCES akun(id) ON DELETE SET NULL
) ENGINE=InnoDB COMMENT='Salary deductions for loan payments';

-- =====================================================
-- REPORTING SYSTEM
-- =====================================================

-- Report templates/types
CREATE TABLE jenis_laporan (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    kode VARCHAR(20) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    kategori ENUM('keuangan', 'anggota', 'simpanan', 'pinjaman', 'operational') NOT NULL,
    template_query TEXT,
    parameter_config JSON,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_kode (kode),
    INDEX idx_kategori (kategori),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB COMMENT='Report types and templates';

-- Generated reports
CREATE TABLE laporan (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    jenis_laporan_id VARCHAR(36) NOT NULL,
    judul VARCHAR(200) NOT NULL,
    periode_dari DATE,
    periode_sampai DATE,
    parameter_input JSON,
    file_path VARCHAR(500),
    file_size INT,
    format_file ENUM('pdf', 'excel', 'csv', 'json') NOT NULL DEFAULT 'pdf',
    status ENUM('generating', 'completed', 'failed', 'archived') NOT NULL DEFAULT 'generating',
    error_message TEXT NULL,
    total_records INT,
    generated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    generated_by VARCHAR(36) NOT NULL,
    downloaded_count INT NOT NULL DEFAULT 0,
    last_downloaded_at TIMESTAMP NULL,
    
    INDEX idx_jenis_laporan_id (jenis_laporan_id),
    INDEX idx_periode (periode_dari, periode_sampai),
    INDEX idx_status (status),
    INDEX idx_generated_at (generated_at),
    INDEX idx_generated_by (generated_by),
    FOREIGN KEY (jenis_laporan_id) REFERENCES jenis_laporan(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (generated_by) REFERENCES akun(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB COMMENT='Generated reports log';

-- =====================================================
-- AUDIT AND LOGGING SYSTEM
-- =====================================================

-- System audit log
CREATE TABLE audit_log (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    table_name VARCHAR(50) NOT NULL,
    record_id VARCHAR(36) NOT NULL,
    action ENUM('INSERT', 'UPDATE', 'DELETE') NOT NULL,
    old_values JSON NULL,
    new_values JSON NULL,
    changed_fields JSON NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    performed_by VARCHAR(36),
    performed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_table_name (table_name),
    INDEX idx_record_id (record_id),
    INDEX idx_action (action),
    INDEX idx_performed_at (performed_at),
    INDEX idx_performed_by (performed_by),
    FOREIGN KEY (performed_by) REFERENCES akun(id) ON DELETE SET NULL
) ENGINE=InnoDB COMMENT='System audit trail';

-- User activity log
CREATE TABLE activity_log (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    user_id VARCHAR(36) NOT NULL,
    activity_type VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    module VARCHAR(50),
    ip_address VARCHAR(45),
    user_agent TEXT,
    session_id VARCHAR(100),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_user_id (user_id),
    INDEX idx_activity_type (activity_type),
    INDEX idx_module (module),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (user_id) REFERENCES akun(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB COMMENT='User activity tracking';

-- =====================================================
-- SYSTEM CONFIGURATION
-- =====================================================

-- Application settings
CREATE TABLE system_config (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    config_key VARCHAR(100) NOT NULL UNIQUE,
    config_value TEXT NOT NULL,
    data_type ENUM('string', 'integer', 'decimal', 'boolean', 'json') NOT NULL DEFAULT 'string',
    category VARCHAR(50) NOT NULL,
    description TEXT,
    is_public BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by VARCHAR(36),
    
    INDEX idx_config_key (config_key),
    INDEX idx_category (category),
    INDEX idx_is_public (is_public),
    FOREIGN KEY (updated_by) REFERENCES akun(id) ON DELETE SET NULL
) ENGINE=InnoDB COMMENT='System configuration settings';

-- =====================================================
-- INSERT INITIAL DATA
-- =====================================================

-- Insert default loan types
INSERT INTO jenis_pinjaman (kode, nama, deskripsi, bunga_persen, minimal_pinjaman, maksimal_pinjaman, jangka_waktu_max, require_guarantor) VALUES
('KON', 'Pinjaman Konsumtif', 'Pinjaman untuk kebutuhan konsumsi sehari-hari', 1.5, 100000, 10000000, 24, FALSE),
('PRO', 'Pinjaman Produktif', 'Pinjaman untuk usaha atau investasi produktif', 1.2, 500000, 50000000, 36, TRUE),
('DAR', 'Pinjaman Darurat', 'Pinjaman untuk kebutuhan mendesak', 2.0, 50000, 5000000, 12, FALSE),
('PEN', 'Pinjaman Pendidikan', 'Pinjaman untuk biaya pendidikan', 1.0, 100000, 20000000, 48, TRUE);

-- Insert default savings types
INSERT INTO jenis_simpanan (kode, nama, deskripsi, minimal_setoran, bunga_persen, is_wajib) VALUES
('POK', 'Simpanan Pokok', 'Simpanan pokok anggota yang dibayar sekali saat masuk', 100000, 0.0, TRUE),
('WAJ', 'Simpanan Wajib', 'Simpanan wajib bulanan anggota', 25000, 3.0, TRUE),
('SUK', 'Simpanan Sukarela', 'Simpanan sukarela anggota', 10000, 4.0, FALSE),
('BER', 'Simpanan Berjangka', 'Simpanan berjangka dengan bunga tinggi', 1000000, 6.0, FALSE);

-- Insert default report types
INSERT INTO jenis_laporan (kode, nama, deskripsi, kategori) VALUES
('LAP001', 'Laporan Posisi Keuangan', 'Laporan posisi keuangan koperasi secara keseluruhan', 'keuangan'),
('LAP002', 'Laporan Simpanan Anggota', 'Laporan detail simpanan per anggota', 'simpanan'),
('LAP003', 'Laporan Pinjaman Aktif', 'Laporan pinjaman yang masih aktif', 'pinjaman'),
('LAP004', 'Laporan Anggota Baru', 'Laporan anggota yang bergabung dalam periode tertentu', 'anggota'),
('LAP005', 'Laporan Tunggakan', 'Laporan anggota dengan tunggakan pembayaran', 'pinjaman'),
('LAP006', 'Laporan Mutasi Simpanan', 'Laporan mutasi simpanan dalam periode tertentu', 'simpanan');

-- Insert system configuration
INSERT INTO system_config (config_key, config_value, data_type, category, description, is_public) VALUES
('app.name', 'Koperasi Management System', 'string', 'general', 'Application name', TRUE),
('app.version', '1.0.0', 'string', 'general', 'Application version', TRUE),
('interest.calculation_method', 'monthly', 'string', 'finance', 'Interest calculation method', FALSE),
('loan.max_amount_multiplier', '10', 'integer', 'finance', 'Maximum loan amount multiplier based on savings', FALSE),
('savings.minimum_balance', '10000', 'decimal', 'finance', 'Minimum balance for savings account', FALSE),
('system.backup_retention_days', '90', 'integer', 'system', 'Backup retention period in days', FALSE),
('notification.email_enabled', 'true', 'boolean', 'notification', 'Enable email notifications', FALSE),
('notification.sms_enabled', 'false', 'boolean', 'notification', 'Enable SMS notifications', FALSE);

-- Insert default admin user (password: admin123 - should be changed in production)
INSERT INTO akun (username, password_hash, email, role, email_verified) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@koperasi.com', 'admin', TRUE);

SET @admin_akun_id = (SELECT id FROM akun WHERE username = 'admin');

INSERT INTO akun_admin (akun_id, employee_id, department, position, permissions) VALUES
(@admin_akun_id, 'ADM001', 'IT', 'System Administrator', '{"all": true}');

-- =====================================================
-- CREATE VIEWS FOR REPORTING
-- =====================================================

-- View for member financial summary
CREATE VIEW v_member_financial_summary AS
SELECT 
    a.id,
    a.member_code,
    a.nama,
    a.status,
    COALESCE(SUM(CASE WHEN ts.jenis_transaksi IN ('setor', 'bunga') THEN ts.nominal ELSE -ts.nominal END), 0) as total_simpanan,
    COALESCE(SUM(CASE WHEN p.status = 'active' THEN p.nominal_disetujui - COALESCE(paid.total_paid, 0) ELSE 0 END), 0) as total_pinjaman_aktif,
    COUNT(DISTINCT p.id) as jumlah_pinjaman,
    COUNT(DISTINCT s.id) as jumlah_simpanan
FROM anggota a
LEFT JOIN simpanan s ON a.id = s.anggota_id AND s.status = 'aktif'
LEFT JOIN transaksi_simpanan ts ON s.id = ts.simpanan_id
LEFT JOIN pinjaman p ON a.id = p.anggota_id
LEFT JOIN (
    SELECT 
        pinjaman_id,
        SUM(nominal_dibayar) as total_paid
    FROM pembayaran_angsuran
    GROUP BY pinjaman_id
) paid ON p.id = paid.pinjaman_id
WHERE a.status = 'aktif'
GROUP BY a.id, a.member_code, a.nama, a.status;

-- View for loan portfolio summary
CREATE VIEW v_loan_portfolio AS
SELECT 
    jp.nama as jenis_pinjaman,
    COUNT(p.id) as jumlah_pinjaman,
    SUM(p.nominal_disetujui) as total_nominal,
    SUM(CASE WHEN p.status = 'active' THEN p.nominal_disetujui ELSE 0 END) as outstanding_amount,
    SUM(CASE WHEN p.status = 'overdue' THEN p.nominal_disetujui ELSE 0 END) as overdue_amount,
    AVG(p.bunga_persen) as avg_interest_rate
FROM pinjaman p
JOIN jenis_pinjaman jp ON p.jenis_pinjaman_id = jp.id
WHERE p.status IN ('active', 'completed', 'overdue')
GROUP BY jp.id, jp.nama;

-- View for savings portfolio summary
CREATE VIEW v_savings_portfolio AS
SELECT 
    js.nama as jenis_simpanan,
    COUNT(s.id) as jumlah_rekening,
    SUM(s.saldo_akhir) as total_saldo,
    AVG(s.saldo_akhir) as rata_rata_saldo,
    js.bunga_persen
FROM simpanan s
JOIN jenis_simpanan js ON s.jenis_simpanan_id = js.id
WHERE s.status = 'aktif'
GROUP BY js.id, js.nama, js.bunga_persen;

-- View for monthly financial report
CREATE VIEW v_monthly_financial AS
SELECT 
    YEAR(created_at) as tahun,
    MONTH(created_at) as bulan,
    'simpanan' as kategori,
    SUM(CASE WHEN jenis_transaksi = 'setor' THEN nominal ELSE 0 END) as masuk,
    SUM(CASE WHEN jenis_transaksi = 'tarik' THEN nominal ELSE 0 END) as keluar,
    SUM(CASE WHEN jenis_transaksi = 'setor' THEN nominal ELSE -nominal END) as net_amount
FROM transaksi_simpanan
GROUP BY YEAR(created_at), MONTH(created_at)

UNION ALL

SELECT 
    YEAR(tanggal_bayar) as tahun,
    MONTH(tanggal_bayar) as bulan,
    'pinjaman' as kategori,
    SUM(nominal_dibayar) as masuk,
    0 as keluar,
    SUM(nominal_dibayar) as net_amount
FROM pembayaran_angsuran
WHERE tanggal_bayar IS NOT NULL
GROUP BY YEAR(tanggal_bayar), MONTH(tanggal_bayar);

-- =====================================================
-- CREATE STORED PROCEDURES
-- =====================================================

DELIMITER //

-- Procedure to calculate loan installments
CREATE PROCEDURE sp_generate_loan_schedule(
    IN loan_id VARCHAR(36)
)
BEGIN
    DECLARE loan_amount DECIMAL(15,2);
    DECLARE interest_rate DECIMAL(5,2);
    DECLARE loan_term INT;
    DECLARE monthly_principal DECIMAL(15,2);
    DECLARE monthly_interest DECIMAL(15,2);
    DECLARE remaining_balance DECIMAL(15,2);
    DECLARE installment_number INT DEFAULT 1;
    DECLARE due_date DATE;
    
    -- Get loan details
    SELECT 
        nominal_disetujui, 
        bunga_persen, 
        jangka_waktu,
        DATE_ADD(tanggal_pencairan, INTERVAL 1 MONTH)
    INTO loan_amount, interest_rate, loan_term, due_date
    FROM pinjaman 
    WHERE id = loan_id;
    
    SET monthly_principal = loan_amount / loan_term;
    SET monthly_interest = (loan_amount * interest_rate * loan_term / 100) / loan_term;
    SET remaining_balance = loan_amount;
    
    -- Clear existing schedule
    DELETE FROM jadwal_angsuran WHERE pinjaman_id = loan_id;
    
    -- Generate installment schedule
    WHILE installment_number <= loan_term DO
        INSERT INTO jadwal_angsuran (
            pinjaman_id,
            angsuran_ke,
            tanggal_jatuh_tempo,
            nominal_pokok,
            nominal_bunga,
            saldo_pinjaman
        ) VALUES (
            loan_id,
            installment_number,
            due_date,
            monthly_principal,
            monthly_interest,
            remaining_balance - monthly_principal
        );
        
        SET remaining_balance = remaining_balance - monthly_principal;
        SET due_date = DATE_ADD(due_date, INTERVAL 1 MONTH);
        SET installment_number = installment_number + 1;
    END WHILE;
    
END //

-- Procedure to process loan payment
CREATE PROCEDURE sp_process_loan_payment(
    IN loan_id VARCHAR(36),
    IN payment_amount DECIMAL(15,2),
    IN payment_date DATE,
    IN payment_method VARCHAR(20),
    IN processed_by_user VARCHAR(36),
    IN notes TEXT
)
BEGIN
    DECLARE current_installment_id VARCHAR(36);
    DECLARE installment_amount DECIMAL(15,2);
    DECLARE principal_amount DECIMAL(15,2);
    DECLARE interest_amount DECIMAL(15,2);
    DECLARE penalty_amount DECIMAL(15,2) DEFAULT 0;
    DECLARE remaining_payment DECIMAL(15,2);
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    -- Get next unpaid installment
    SELECT id, total_angsuran, nominal_pokok, nominal_bunga
    INTO current_installment_id, installment_amount, principal_amount, interest_amount
    FROM jadwal_angsuran 
    WHERE pinjaman_id = loan_id 
      AND status IN ('belum_bayar', 'sebagian')
    ORDER BY angsuran_ke 
    LIMIT 1;
    
    -- Calculate penalty for overdue payments
    IF payment_date > (SELECT tanggal_jatuh_tempo FROM jadwal_angsuran WHERE id = current_installment_id) THEN
        SET penalty_amount = installment_amount * 0.01; -- 1% penalty
    END IF;
    
    SET remaining_payment = payment_amount;
    
    -- Process payment
    WHILE remaining_payment > 0 AND current_installment_id IS NOT NULL DO
        IF remaining_payment >= (installment_amount + penalty_amount) THEN
            -- Full payment
            INSERT INTO pembayaran_angsuran (
                pinjaman_id, jadwal_angsuran_id, nominal_dibayar, 
                nominal_pokok, nominal_bunga, denda, tanggal_bayar, 
                metode_bayar, keterangan, processed_by
            ) VALUES (
                loan_id, current_installment_id, installment_amount,
                principal_amount, interest_amount, penalty_amount, 
                payment_date, payment_method, notes, processed_by_user
            );
            
            UPDATE jadwal_angsuran 
            SET status = 'lunas', tanggal_bayar = payment_date 
            WHERE id = current_installment_id;
            
            SET remaining_payment = remaining_payment - installment_amount - penalty_amount;
            
        ELSE
            -- Partial payment
            INSERT INTO pembayaran_angsuran (
                pinjaman_id, jadwal_angsuran_id, nominal_dibayar, 
                nominal_pokok, nominal_bunga, denda, tanggal_bayar, 
                metode_bayar, keterangan, processed_by
            ) VALUES (
                loan_id, current_installment_id, remaining_payment,
                (remaining_payment * principal_amount / installment_amount), 
                (remaining_payment * interest_amount / installment_amount), 
                0, payment_date, payment_method, notes, processed_by_user
            );
            
            UPDATE jadwal_angsuran 
            SET status = 'sebagian' 
            WHERE id = current_installment_id;
            
            SET remaining_payment = 0;
        END IF;
        
        -- Get next installment
        SELECT id, total_angsuran, nominal_pokok, nominal_bunga
        INTO current_installment_id, installment_amount, principal_amount, interest_amount
        FROM jadwal_angsuran 
        WHERE pinjaman_id = loan_id 
          AND status IN ('belum_bayar', 'sebagian')
          AND id != current_installment_id
        ORDER BY angsuran_ke 
        LIMIT 1;
        
        SET penalty_amount = 0; -- Only first overdue installment gets penalty
    END WHILE;
    
    -- Update loan status if fully paid
    IF NOT EXISTS (
        SELECT 1 FROM jadwal_angsuran 
        WHERE pinjaman_id = loan_id AND status != 'lunas'
    ) THEN
        UPDATE pinjaman SET status = 'completed' WHERE id = loan_id;
    END IF;
    
    COMMIT;
    
END //

-- Procedure to update savings balance
CREATE PROCEDURE sp_update_savings_balance(
    IN savings_id VARCHAR(36),
    IN transaction_type VARCHAR(20),
    IN amount DECIMAL(15,2),
    IN description TEXT,
    IN processed_by_user VARCHAR(36)
)
BEGIN
    DECLARE current_balance DECIMAL(15,2);
    DECLARE new_balance DECIMAL(15,2);
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    -- Get current balance
    SELECT saldo_akhir INTO current_balance 
    FROM simpanan 
    WHERE id = savings_id FOR UPDATE;
    
    -- Calculate new balance
    IF transaction_type IN ('setor', 'bunga', 'transfer_masuk') THEN
        SET new_balance = current_balance + amount;
    ELSE
        SET new_balance = current_balance - amount;
        IF new_balance < 0 THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Insufficient balance';
        END IF;
    END IF;
    
    -- Insert transaction record
    INSERT INTO transaksi_simpanan (
        simpanan_id, jenis_transaksi, nominal, 
        saldo_sebelum, saldo_sesudah, keterangan, processed_by
    ) VALUES (
        savings_id, transaction_type, amount,
        current_balance, new_balance, description, processed_by_user
    );
    
    -- Update savings balance
    UPDATE simpanan 
    SET saldo_akhir = new_balance, updated_at = CURRENT_TIMESTAMP 
    WHERE id = savings_id;
    
    COMMIT;
    
END //

DELIMITER ;

-- =====================================================
-- CREATE TRIGGERS FOR AUDIT LOGGING
-- =====================================================

DELIMITER //

-- Trigger for anggota table
CREATE TRIGGER tr_anggota_audit_insert 
AFTER INSERT ON anggota FOR EACH ROW
BEGIN
    INSERT INTO audit_log (table_name, record_id, action, new_values, performed_by)
    VALUES ('anggota', NEW.id, 'INSERT', 
        JSON_OBJECT(
            'member_code', NEW.member_code,
            'nama', NEW.nama,
            'status', NEW.status
        ), 
        NEW.created_by
    );
END //

CREATE TRIGGER tr_anggota_audit_update 
AFTER UPDATE ON anggota FOR EACH ROW
BEGIN
    INSERT INTO audit_log (table_name, record_id, action, old_values, new_values, performed_by)
    VALUES ('anggota', NEW.id, 'UPDATE',
        JSON_OBJECT(
            'member_code', OLD.member_code,
            'nama', OLD.nama,
            'status', OLD.status
        ),
        JSON_OBJECT(
            'member_code', NEW.member_code,
            'nama', NEW.nama,
            'status', NEW.status
        ),
        NEW.updated_by
    );
END //

-- Trigger for pinjaman table
CREATE TRIGGER tr_pinjaman_audit_insert 
AFTER INSERT ON pinjaman FOR EACH ROW
BEGIN
    INSERT INTO audit_log (table_name, record_id, action, new_values, performed_by)
    VALUES ('pinjaman', NEW.id, 'INSERT',
        JSON_OBJECT(
            'nomor_pinjaman', NEW.nomor_pinjaman,
            'anggota_id', NEW.anggota_id,
            'nominal_pengajuan', NEW.nominal_pengajuan,
            'status', NEW.status
        ),
        NEW.created_by
    );
END //

CREATE TRIGGER tr_pinjaman_audit_update 
AFTER UPDATE ON pinjaman FOR EACH ROW
BEGIN
    INSERT INTO audit_log (table_name, record_id, action, old_values, new_values)
    VALUES ('pinjaman', NEW.id, 'UPDATE',
        JSON_OBJECT(
            'status', OLD.status,
            'nominal_disetujui', OLD.nominal_disetujui
        ),
        JSON_OBJECT(
            'status', NEW.status,
            'nominal_disetujui', NEW.nominal_disetujui
        )
    );
END //

DELIMITER ;

-- =====================================================
-- CREATE INDEXES FOR PERFORMANCE OPTIMIZATION
-- =====================================================

-- Additional indexes for better query performance
CREATE INDEX idx_transaksi_simpanan_periode ON transaksi_simpanan(tanggal_transaksi, jenis_transaksi);
CREATE INDEX idx_pembayaran_angsuran_periode ON pembayaran_angsuran(tanggal_bayar, pinjaman_id);
CREATE INDEX idx_audit_log_composite ON audit_log(table_name, record_id, performed_at);
CREATE INDEX idx_activity_log_composite ON activity_log(user_id, created_at, activity_type);

-- =====================================================
-- SECURITY SETTINGS
-- =====================================================

-- Create application user with limited privileges
CREATE USER IF NOT EXISTS 'koperasi_app'@'localhost' IDENTIFIED BY 'K0p3r@s1_S3cur3_P@ssw0rd!';
GRANT SELECT, INSERT, UPDATE ON koperasi_management.* TO 'koperasi_app'@'localhost';
GRANT DELETE ON koperasi_management.audit_log TO 'koperasi_app'@'localhost';
GRANT DELETE ON koperasi_management.activity_log TO 'koperasi_app'@'localhost';
GRANT EXECUTE ON koperasi_management.* TO 'koperasi_app'@'localhost';

-- Create read-only user for reporting
CREATE USER IF NOT EXISTS 'koperasi_report'@'localhost' IDENTIFIED BY 'R3p0rt_R3@d0nly!';
GRANT SELECT ON koperasi_management.* TO 'koperasi_report'@'localhost';

FLUSH PRIVILEGES;

-- =====================================================
-- DATABASE DOCUMENTATION
-- =====================================================

/*
KOPERASI MANAGEMENT SYSTEM DATABASE DOCUMENTATION

1. MAIN ENTITIES:
   - akun: User authentication and authorization
   - anggota: Cooperative members
   - simpanan: Savings accounts and transactions
   - pinjaman: Loans and installments
   - gaji: Payroll management
   - laporan: Reporting system

2. KEY FEATURES:
   - Role-based access control (admin/member)
   - Comprehensive audit logging
   - Automated loan schedule generation
   - Savings transaction tracking
   - Payroll integration with deductions
   - Flexible reporting system
   - Data integrity with foreign key constraints

3. SECURITY MEASURES:
   - Password hashing for user accounts
   - Audit trail for all critical operations
   - Activity logging for user actions
   - Separate database users with limited privileges
   - Input validation through constraints

4. PERFORMANCE OPTIMIZATIONS:
   - Strategic indexing on frequently queried columns
   - Composite indexes for complex queries
   - Generated columns for calculated values
   - Optimized views for reporting

5. STORED PROCEDURES:
   - sp_generate_loan_schedule: Creates loan installment schedule
   - sp_process_loan_payment: Handles loan payments with business logic
   - sp_update_savings_balance: Manages savings transactions

6. BACKUP RECOMMENDATIONS:
   - Daily automated backups
   - Transaction log backups every 15 minutes
   - Monthly full backup archival
   - Test restore procedures regularly

7. MAINTENANCE TASKS:
   - Regular index maintenance and statistics updates
   - Audit log cleanup (retain based on system_config)
   - Activity log archival
   - Database integrity checks

For support and maintenance, contact the database administrator.
*/

-- =====================================================
-- TOSERDA MODULE
-- =====================================================

/*
DESKRIPSI MODUL:
Modul ini menangani manajemen toko serba ada (Toserda) koperasi.
Fitur utama:
- Manajemen inventori barang
- Pencatatan transaksi penjualan/pembelian
- Tracking stok dan harga
- Perhitungan laba/rugi

KOLOM PENTING:
- nama: Nama barang
- harga: Harga jual barang
- jumlah: Stok tersedia
- is_deleted: Soft delete untuk barang
*/

CREATE TABLE barang (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    nama VARCHAR(255) NOT NULL,    -- nama barang
    tipe VARCHAR(50),             -- kategori barang
    merk VARCHAR(50),             -- merek barang
    harga DECIMAL(15,2) NOT NULL, -- harga jual
    jumlah INT NOT NULL DEFAULT 0, -- stok tersedia
    keterangan VARCHAR(255),      -- informasi tambahan
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_deleted BOOLEAN DEFAULT FALSE -- soft delete
) ENGINE=InnoDB COMMENT='Data barang untuk Toserda';

/*
DESKRIPSI TABEL TRANSAKSI_TOSERDA:
Tabel ini mencatat semua transaksi yang terjadi di Toserda.
Fitur:
- Pencatatan penjualan dan pembelian
- Tracking harga jual
- Perhitungan total otomatis
- History transaksi

KOLOM PENTING:
- jenis_transaksi: Tipe transaksi (penjualan/pembelian)
- jumlah: Kuantitas barang
- harga_satuan: Harga per unit
- total: Total transaksi (generated)
*/

CREATE TABLE transaksi_toserda (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    barang_id VARCHAR(36) NOT NULL, -- referensi ke barang
    jenis_transaksi ENUM('penjualan', 'pembelian', 'biaya_usaha', 'retur', 'adjustment', 'lain_lain') NOT NULL,
    jumlah INT NOT NULL,           -- kuantitas
    harga_satuan DECIMAL(15,2) NOT NULL, -- harga per unit
    total DECIMAL(15,2) GENERATED ALWAYS AS (jumlah * harga_satuan) STORED, -- total transaksi
    tanggal_transaksi TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    keterangan VARCHAR(255),
    created_by VARCHAR(36),        -- user yang membuat transaksi
    FOREIGN KEY (barang_id) REFERENCES barang(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES akun(id) ON DELETE SET NULL
) ENGINE=InnoDB COMMENT='Transaksi Toserda (Penjualan, Pembelian, Biaya Usaha, Lain-lain)';

/*
INDEKS DAN OPTIMASI:
- Indeks untuk pencarian barang
- Indeks untuk laporan transaksi
- Optimasi query stok
*/

-- Indeks untuk optimasi
CREATE INDEX idx_barang_nama ON barang(nama);
CREATE INDEX idx_barang_tipe ON barang(tipe);
CREATE INDEX idx_transaksi_tanggal ON transaksi_toserda(tanggal_transaksi);
CREATE INDEX idx_transaksi_jenis ON transaksi_toserda(jenis_transaksi);

/*
CONTOH PENGGUNAAN:

1. Menambah barang baru:
INSERT INTO barang (nama, tipe, merk, harga, jumlah)
VALUES ('Mie Instan', 'Makanan', 'Indomie', 3500, 100);

2. Mencatat transaksi penjualan:
INSERT INTO transaksi_toserda (barang_id, jenis_transaksi, jumlah, harga_satuan)
VALUES ('barang_id', 'penjualan', 5, 3500);

3. Update stok barang:
UPDATE barang 
SET jumlah = jumlah - 5 
WHERE id = 'barang_id';
*/

/*
MAINTENANCE:
- Pembersihan data barang yang tidak aktif
- Archive transaksi lama
- Monitoring stok minimum
- Backup regular
*/

-- Prosedur untuk update stok
DELIMITER //
CREATE PROCEDURE sp_update_stok_barang(
    IN p_barang_id VARCHAR(36),
    IN p_jumlah INT,
    IN p_tipe ENUM('tambah', 'kurang')
)
BEGIN
    IF p_tipe = 'tambah' THEN
        UPDATE barang SET jumlah = jumlah + p_jumlah WHERE id = p_barang_id;
    ELSE
        UPDATE barang SET jumlah = jumlah - p_jumlah WHERE id = p_barang_id;
    END IF;
END //
DELIMITER ;

/*
KEAMANAN:
- Validasi stok sebelum transaksi
- Audit trail untuk perubahan harga
- Pembatasan akses user
- Enkripsi data sensitif
*/

-- Trigger untuk audit perubahan harga
DELIMITER //
CREATE TRIGGER tr_barang_audit_harga
AFTER UPDATE ON barang
FOR EACH ROW
BEGIN
    IF OLD.harga != NEW.harga THEN
        INSERT INTO audit_log (table_name, record_id, action, old_values, new_values)
        VALUES ('barang', NEW.id, 'UPDATE',
            JSON_OBJECT('harga', OLD.harga),
            JSON_OBJECT('harga', NEW.harga)
        );
    END IF;
END //
DELIMITER ;

-- =====================================================
-- ANGKUTAN KARYAWAN MODULE
-- =====================================================

/*
DESKRIPSI MODUL:
Modul ini menangani manajemen angkutan karyawan koperasi.
Fitur utama:
- Manajemen data kendaraan
- Pencatatan pemasukan/pengeluaran
- Tracking penggunaan kendaraan
- Perhitungan biaya operasional

KOLOM PENTING:
- no_polisi: Nomor polisi kendaraan
- tgl_berlaku_stnk: Tanggal berlaku STNK
- aktif: Status kendaraan
- is_deleted: Soft delete untuk kendaraan
*/

CREATE TABLE mobil (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    nama VARCHAR(255) NOT NULL,    -- nama/tipe kendaraan
    jenis VARCHAR(100),           -- jenis kendaraan
    merk VARCHAR(100),            -- merek kendaraan
    tahun INT,                    -- tahun pembuatan
    no_polisi VARCHAR(20),        -- nomor polisi
    warna VARCHAR(50),            -- warna kendaraan
    no_rangka VARCHAR(50),        -- nomor rangka
    no_mesin VARCHAR(50),         -- nomor mesin
    no_bpkb VARCHAR(50),          -- nomor BPKB
    tgl_berlaku_stnk DATE,        -- tanggal berlaku STNK
    keterangan VARCHAR(255),      -- informasi tambahan
    aktif BOOLEAN NOT NULL DEFAULT TRUE, -- status kendaraan
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_deleted BOOLEAN DEFAULT FALSE -- soft delete
) ENGINE=InnoDB COMMENT='Data kendaraan untuk angkutan karyawan';

/*
DESKRIPSI TABEL TRANSAKSI_ANGKUTAN:
Tabel ini mencatat semua transaksi keuangan terkait angkutan.
Fitur:
- Pencatatan pemasukan dan pengeluaran
- Tracking biaya operasional
- History transaksi per kendaraan
- Perhitungan laba/rugi

KOLOM PENTING:
- jenis_transaksi: Tipe transaksi (pemasukan/pengeluaran)
- jumlah: Nominal transaksi
- tanggal_transaksi: Waktu transaksi
*/

CREATE TABLE transaksi_angkutan (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    mobil_id VARCHAR(36) NOT NULL, -- referensi ke kendaraan
    jenis_transaksi ENUM('pemasukan', 'pengeluaran') NOT NULL,
    jumlah DECIMAL(15,2) NOT NULL, -- nominal transaksi
    tanggal_transaksi TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    keterangan VARCHAR(255),      -- keterangan transaksi
    created_by VARCHAR(36),       -- user yang membuat transaksi
    FOREIGN KEY (mobil_id) REFERENCES mobil(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES akun(id) ON DELETE SET NULL
) ENGINE=InnoDB COMMENT='Transaksi pemasukan/pengeluaran angkutan karyawan';

/*
INDEKS DAN OPTIMASI:
- Indeks untuk pencarian kendaraan
- Indeks untuk laporan transaksi
- Optimasi query laporan
*/

-- Indeks untuk optimasi
CREATE INDEX idx_mobil_no_polisi ON mobil(no_polisi);
CREATE INDEX idx_mobil_aktif ON mobil(aktif);
CREATE INDEX idx_transaksi_angkutan_tanggal ON transaksi_angkutan(tanggal_transaksi);
CREATE INDEX idx_transaksi_angkutan_jenis ON transaksi_angkutan(jenis_transaksi);

/*
CONTOH PENGGUNAAN:

1. Menambah kendaraan baru:
INSERT INTO mobil (nama, no_polisi, tgl_berlaku_stnk)
VALUES ('Toyota Avanza', 'B 1234 ABC', '2024-12-31');

2. Mencatat transaksi pengeluaran:
INSERT INTO transaksi_angkutan (mobil_id, jenis_transaksi, jumlah, keterangan)
VALUES ('mobil_id', 'pengeluaran', 500000, 'Biaya BBM');

3. Update status kendaraan:
UPDATE mobil 
SET aktif = FALSE 
WHERE id = 'mobil_id';
*/

/*
MAINTENANCE:
- Pembersihan data kendaraan tidak aktif
- Archive transaksi lama
- Monitoring STNK
- Backup regular
*/

-- Prosedur untuk laporan transaksi
DELIMITER //
CREATE PROCEDURE sp_laporan_transaksi_angkutan(
    IN p_mobil_id VARCHAR(36),
    IN p_tanggal_mulai DATE,
    IN p_tanggal_selesai DATE
)
BEGIN
    SELECT 
        m.no_polisi,
        t.jenis_transaksi,
        SUM(t.jumlah) as total,
        COUNT(*) as jumlah_transaksi
    FROM transaksi_angkutan t
    JOIN mobil m ON t.mobil_id = m.id
    WHERE t.mobil_id = p_mobil_id
    AND t.tanggal_transaksi BETWEEN p_tanggal_mulai AND p_tanggal_selesai
    GROUP BY m.no_polisi, t.jenis_transaksi;
END //
DELIMITER ;

/*
KEAMANAN:
- Validasi data kendaraan
- Audit trail untuk perubahan status
- Pembatasan akses user
- Enkripsi data sensitif
*/

-- Trigger untuk audit perubahan status
DELIMITER //
CREATE TRIGGER tr_mobil_audit_status
AFTER UPDATE ON mobil
FOR EACH ROW
BEGIN
    IF OLD.aktif != NEW.aktif THEN
        INSERT INTO audit_log (table_name, record_id, action, old_values, new_values)
        VALUES ('mobil', NEW.id, 'UPDATE',
            JSON_OBJECT('aktif', OLD.aktif),
            JSON_OBJECT('aktif', NEW.aktif)
        );
    END IF;
END //
DELIMITER ;

-- =====================================================
-- MODUL KAS
-- =====================================================

/*
DESKRIPSI MODUL:
Modul ini menangani manajemen kas dan transaksi keuangan koperasi.
Fitur utama:
- Manajemen multiple kas/akun kas
- Pencatatan pemasukan dan pengeluaran
- Transfer antar kas
- Laporan keuangan per kas

KOLOM PENTING:
- nama: Nama kas/akun kas
- tipe: Jenis kas (operasional/unit_usaha/cabang)
- is_active: Status aktif kas
*/

CREATE TABLE kas (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    nama VARCHAR(255) NOT NULL,    -- nama kas/akun kas
    tipe ENUM('operasional', 'unit_usaha', 'cabang', 'lainnya') NOT NULL DEFAULT 'operasional', -- jenis kas
    keterangan VARCHAR(255),       -- informasi tambahan
    is_active BOOLEAN NOT NULL DEFAULT TRUE, -- status aktif
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB COMMENT='Master data kas/akun kas/cabang';

/*
DESKRIPSI TABEL TRANSAKSI_KAS:
Tabel ini mencatat semua transaksi keuangan kas.
Fitur:
- Pencatatan pemasukan dan pengeluaran
- Transfer antar kas
- History transaksi per kas
- Validasi saldo

KOLOM PENTING:
- jenis_transaksi: Tipe transaksi (pemasukan/pengeluaran/transfer)
- jumlah: Nominal transaksi
- dari_kas_id: Kas sumber (untuk transfer)
- untuk_kas_id: Kas tujuan (untuk transfer)
*/

CREATE TABLE transaksi_kas (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    kas_id VARCHAR(36) NOT NULL,   
    jenis_transaksi ENUM('pemasukan', 'pengeluaran', 'transfer') NOT NULL,
    jumlah DECIMAL(15,2) NOT NULL, 
    tanggal_transaksi TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    keterangan VARCHAR(255),       
    dari_kas_id VARCHAR(36),       
    untuk_kas_id VARCHAR(36),      
    created_by VARCHAR(36),        
    FOREIGN KEY (kas_id) REFERENCES kas(id) ON DELETE CASCADE,
    FOREIGN KEY (dari_kas_id) REFERENCES kas(id) ON DELETE SET NULL,
    FOREIGN KEY (untuk_kas_id) REFERENCES kas(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES akun(id) ON DELETE SET NULL,
    CONSTRAINT chk_jumlah_positif_kas CHECK (jumlah > 0)
) ENGINE=InnoDB COMMENT='Transaksi kas (pemasukan, pengeluaran, transfer antar kas)';

/*
INDEKS DAN OPTIMASI:
- Indeks untuk pencarian kas
- Indeks untuk laporan transaksi
- Optimasi query saldo
*/

-- Indeks untuk optimasi
CREATE INDEX idx_kas_tipe ON kas(tipe);
CREATE INDEX idx_kas_active ON kas(is_active);
CREATE INDEX idx_transaksi_kas_tanggal ON transaksi_kas(tanggal_transaksi);
CREATE INDEX idx_transaksi_kas_jenis ON transaksi_kas(jenis_transaksi);

/*
CONTOH PENGGUNAAN:

1. Membuat kas baru:
INSERT INTO kas (nama, tipe, keterangan)
VALUES ('Kas Operasional', 'operasional', 'Kas untuk operasional harian');

2. Mencatat transaksi pemasukan:
INSERT INTO transaksi_kas (kas_id, jenis_transaksi, jumlah, keterangan)
VALUES ('kas_id', 'pemasukan', 1000000, 'Setoran dari anggota');

3. Transfer antar kas:
INSERT INTO transaksi_kas (kas_id, jenis_transaksi, jumlah, dari_kas_id, untuk_kas_id, keterangan)
VALUES ('kas_id', 'transfer', 500000, 'kas_sumber_id', 'kas_tujuan_id', 'Transfer ke kas cabang');
*/

/*
MAINTENANCE:
- Pembersihan data kas tidak aktif
- Archive transaksi lama
- Monitoring saldo kas
- Backup regular
*/

-- Prosedur untuk laporan saldo kas
DELIMITER //
CREATE PROCEDURE sp_laporan_saldo_kas(
    IN p_kas_id VARCHAR(36),
    IN p_tanggal_mulai DATE,
    IN p_tanggal_selesai DATE
)
BEGIN
    SELECT 
        k.nama as nama_kas,
        SUM(CASE WHEN t.jenis_transaksi = 'pemasukan' THEN t.jumlah ELSE 0 END) as total_masuk,
        SUM(CASE WHEN t.jenis_transaksi = 'pengeluaran' THEN t.jumlah ELSE 0 END) as total_keluar,
        SUM(CASE 
            WHEN t.jenis_transaksi = 'pemasukan' THEN t.jumlah 
            WHEN t.jenis_transaksi = 'pengeluaran' THEN -t.jumlah 
            ELSE 0 
        END) as saldo
    FROM kas k
    LEFT JOIN transaksi_kas t ON k.id = t.kas_id
    WHERE k.id = p_kas_id
    AND t.tanggal_transaksi BETWEEN p_tanggal_mulai AND p_tanggal_selesai
    GROUP BY k.id, k.nama;
END //
DELIMITER ;

/*
KEAMANAN:
- Validasi saldo sebelum transaksi
- Audit trail untuk semua transaksi
- Pembatasan akses user
- Enkripsi data sensitif
*/

-- Trigger untuk audit transaksi kas
DELIMITER //
CREATE TRIGGER tr_transaksi_kas_audit
AFTER INSERT ON transaksi_kas
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (table_name, record_id, action, new_values)
    VALUES ('transaksi_kas', NEW.id, 'INSERT', 
        JSON_OBJECT(
            'kas_id', NEW.kas_id,
            'jenis_transaksi', NEW.jenis_transaksi,
            'jumlah', NEW.jumlah
        )
    );
END //
DELIMITER ;



-- =====================================================

CREATE TABLE tagihan_simpanan (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    anggota_id VARCHAR(36) NOT NULL,
    jenis_simpanan_id VARCHAR(36) NOT NULL,
    periode_bulan INT NOT NULL CHECK (periode_bulan BETWEEN 1 AND 12),
    periode_tahun YEAR NOT NULL,
    jumlah DECIMAL(15,2) NOT NULL,
    status ENUM('belum_bayar', 'sudah_bayar', 'jatuh_tempo', 'dibatalkan') NOT NULL DEFAULT 'belum_bayar',
    tanggal_jatuh_tempo DATE NOT NULL,
    tanggal_bayar DATE,
    keterangan VARCHAR(255),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (anggota_id) REFERENCES anggota(id) ON DELETE CASCADE,
    FOREIGN KEY (jenis_simpanan_id) REFERENCES jenis_simpanan(id) ON DELETE RESTRICT,
    is_deleted BOOLEAN DEFAULT FALSE,
    deleted_at TIMESTAMP NULL
) ENGINE=InnoDB COMMENT='Tagihan simpanan anggota per periode';

CREATE INDEX idx_tagihan_jatuh_tempo ON tagihan_simpanan(tanggal_jatuh_tempo);

CREATE TABLE shu (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    anggota_id VARCHAR(36) NOT NULL,
    tahun YEAR NOT NULL,
    jumlah DECIMAL(15,2) NOT NULL,
    status ENUM('pending', 'dibagikan', 'ditahan') NOT NULL DEFAULT 'pending',
    tanggal_bagi DATE,
    keterangan VARCHAR(255),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (anggota_id) REFERENCES anggota(id) ON DELETE CASCADE,
    is_deleted BOOLEAN DEFAULT FALSE,
    deleted_at TIMESTAMP NULL,
    CONSTRAINT chk_jumlah_positif_shu CHECK (jumlah > 0)
) ENGINE=InnoDB COMMENT='Pembagian Sisa Hasil Usaha (SHU) ke anggota';

CREATE INDEX idx_shu_tahun_status ON shu(tahun, status);

CREATE TABLE inventaris (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    nama VARCHAR(255) NOT NULL,
    kategori VARCHAR(100),
    jumlah INT NOT NULL DEFAULT 1,
    satuan VARCHAR(50),
    lokasi VARCHAR(100),
    kondisi ENUM('baik', 'rusak', 'hilang', 'lainnya') NOT NULL DEFAULT 'baik',
    keterangan VARCHAR(255),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_deleted BOOLEAN DEFAULT FALSE,
    deleted_at TIMESTAMP NULL
) ENGINE=InnoDB COMMENT='Inventaris/aset tetap koperasi';

CREATE INDEX idx_inventaris_kategori ON inventaris(kategori, kondisi);

-- =====================================================
-- MODUL PENGAJUAN
-- =====================================================

/*
DESKRIPSI MODUL:
Modul ini menangani pengajuan berbagai jenis permohonan anggota.
Fitur utama:
- Pengajuan pinjaman
- Penarikan simpanan
- Tracking status pengajuan
- History pengajuan
- Notifikasi otomatis

KOLOM PENTING:
- jenis_pengajuan: Tipe pengajuan (pinjaman/penarikan_simpanan)
- nominal: Jumlah yang diajukan
- status: Status pengajuan (draft/diajukan/disetujui/ditolak)
- tanggal_pengajuan: Waktu pengajuan
- tanggal_approval: Waktu persetujuan
*/

CREATE TABLE pengajuan (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    anggota_id VARCHAR(36) NOT NULL,
    jenis_pengajuan ENUM('pinjaman', 'penarikan_simpanan', 'lainnya') NOT NULL,
    nominal DECIMAL(15,2) NOT NULL,
    status ENUM('draft', 'diajukan', 'disetujui', 'ditolak', 'dicairkan', 'dibatalkan') NOT NULL DEFAULT 'draft',
    tanggal_pengajuan DATE NOT NULL DEFAULT (CURRENT_DATE),
    tanggal_approval DATE,
    tanggal_cair DATE,
    alasan VARCHAR(255),
    keterangan TEXT,
    approved_by VARCHAR(36),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_deleted BOOLEAN DEFAULT FALSE,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (anggota_id) REFERENCES anggota(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES akun(id) ON DELETE SET NULL,
    CONSTRAINT chk_nominal_positif_pengajuan CHECK (nominal > 0)
) ENGINE=InnoDB COMMENT='Pengajuan pinjaman/penarikan simpanan anggota';

/*
INDEKS DAN OPTIMASI:
- Indeks untuk pencarian cepat
- Indeks untuk filtering status
- Indeks untuk laporan
*/

-- Indeks untuk optimasi
CREATE INDEX idx_pengajuan_status ON pengajuan(status);
CREATE INDEX idx_pengajuan_tanggal ON pengajuan(tanggal_pengajuan);
CREATE INDEX idx_pengajuan_anggota ON pengajuan(anggota_id);
CREATE INDEX idx_pengajuan_jenis ON pengajuan(jenis_pengajuan);

/*
CONTOH PENGGUNAAN:

1. Membuat pengajuan baru:
INSERT INTO pengajuan (anggota_id, jenis_pengajuan, nominal, keterangan)
VALUES ('anggota_id', 'pinjaman', 1000000, 'Pinjaman untuk modal usaha');

2. Update status pengajuan:
UPDATE pengajuan 
SET status = 'disetujui', 
    tanggal_approval = CURRENT_DATE,
    approved_by = 'admin_id'
WHERE id = 'pengajuan_id';

3. Mencari pengajuan berdasarkan status:
SELECT * FROM pengajuan 
WHERE status = 'diajukan' 
AND tanggal_pengajuan >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY);
*/

/*
MAINTENANCE:
- Pembersihan data pengajuan lama
- Archive pengajuan yang sudah selesai
- Monitoring pengajuan yang pending
- Backup regular
*/

-- Prosedur untuk laporan pengajuan
DELIMITER //
CREATE PROCEDURE sp_laporan_pengajuan(
    IN p_tanggal_mulai DATE,
    IN p_tanggal_selesai DATE,
    IN p_status VARCHAR(20)
)
BEGIN
    SELECT 
        p.id,
        a.nama as nama_anggota,
        p.jenis_pengajuan,
        p.nominal,
        p.status,
        p.tanggal_pengajuan,
        p.tanggal_approval,
        ak.username as approved_by
    FROM pengajuan p
    JOIN anggota a ON p.anggota_id = a.id
    LEFT JOIN akun ak ON p.approved_by = ak.id
    WHERE p.tanggal_pengajuan BETWEEN p_tanggal_mulai AND p_tanggal_selesai
    AND (p_status IS NULL OR p.status = p_status)
    AND p.is_deleted = FALSE
    ORDER BY p.tanggal_pengajuan DESC;
END //
DELIMITER ;

/*
KEAMANAN:
- Validasi data pengajuan
- Audit trail untuk perubahan status
- Pembatasan akses user
- Enkripsi data sensitif
*/

-- Trigger untuk audit perubahan status
DELIMITER //
CREATE TRIGGER tr_pengajuan_audit_status
AFTER UPDATE ON pengajuan
FOR EACH ROW
BEGIN
    IF OLD.status != NEW.status THEN
        INSERT INTO audit_log (table_name, record_id, action, old_values, new_values)
        VALUES ('pengajuan', NEW.id, 'UPDATE',
            JSON_OBJECT('status', OLD.status),
            JSON_OBJECT('status', NEW.status)
        );
    END IF;
END //
DELIMITER ;

-- Trigger untuk notifikasi otomatis
DELIMITER //
CREATE TRIGGER tr_pengajuan_notifikasi
AFTER INSERT ON pengajuan
FOR EACH ROW
BEGIN
    -- Notifikasi untuk admin
    INSERT INTO notifikasi (
        user_id,
        judul,
        pesan,
        tipe,
        ref_id,
        ref_tipe
    )
    SELECT 
        id,
        'Pengajuan Baru',
        CONCAT('Ada pengajuan baru dari ', 
               (SELECT nama FROM anggota WHERE id = NEW.anggota_id),
               ' sebesar Rp ', NEW.nominal),
        'pengajuan',
        NEW.id,
        'pengajuan'
    FROM akun
    WHERE role = 'admin';
END //
DELIMITER ;

-- =====================================================
-- MODUL NOTIFIKASI OTOMATIS & EMAIL LOG
-- =====================================================

/*
DESKRIPSI MODUL:
Modul ini menangani sistem notifikasi otomatis dan log email untuk koperasi.
Fitur utama:
- Notifikasi in-app untuk user
- Pengiriman email otomatis
- Tracking status pengiriman
- History notifikasi

KOLOM PENTING:
- user_id: ID penerima notifikasi
- tipe: Jenis notifikasi (tagihan/pinjaman/umum)
- status: Status notifikasi (belum_dibaca/dibaca/diarsipkan)
- ref_id: Referensi ke data terkait (opsional)
- tanggal_kirim: Waktu pengiriman
*/

CREATE TABLE notifikasi (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    user_id VARCHAR(36) NOT NULL, -- relasi ke akun penerima notifikasi
    judul VARCHAR(255) NOT NULL,  -- judul notifikasi
    pesan TEXT NOT NULL,          -- isi pesan notifikasi
    tipe ENUM('tagihan', 'pinjaman', 'umum', 'lainnya') NOT NULL DEFAULT 'umum', -- jenis notifikasi
    status ENUM('belum_dibaca', 'dibaca', 'diarsipkan') NOT NULL DEFAULT 'belum_dibaca', -- status notifikasi
    tanggal_kirim TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, -- waktu pengiriman
    tanggal_baca TIMESTAMP NULL,  -- waktu dibaca oleh user
    ref_id VARCHAR(36),           -- ID referensi (tagihan/pinjaman)
    ref_tipe ENUM('tagihan', 'pinjaman', 'shu', 'lainnya'), -- tipe referensi
    sisa_nilai DECIMAL(15,2),     -- nilai sisa untuk tagihan/pinjaman
    tanggal_jatuh_tempo DATE,     -- tanggal jatuh tempo untuk notifikasi
    FOREIGN KEY (user_id) REFERENCES akun(id) ON DELETE CASCADE
) ENGINE=InnoDB COMMENT='Log notifikasi otomatis untuk user (tagihan, pinjaman, dsb)';

/*
DESKRIPSI TABEL EMAIL_LOG:
Tabel ini menyimpan log pengiriman email notifikasi.
Fitur:
- Tracking status pengiriman email
- Menyimpan error message jika gagal
- History pengiriman email
- Relasi dengan user

KOLOM PENTING:
- status: Status pengiriman (pending/sent/failed)
- error_message: Pesan error jika gagal
- tanggal_kirim: Waktu pengiriman email
*/

CREATE TABLE email_log (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    user_id VARCHAR(36),          -- relasi ke user penerima
    email VARCHAR(100),           -- alamat email penerima
    subject VARCHAR(255),         -- subjek email
    body TEXT,                    -- isi email
    status ENUM('pending', 'sent', 'failed') NOT NULL DEFAULT 'pending', -- status pengiriman
    tanggal_kirim TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, -- waktu pengiriman
    error_message TEXT,           -- pesan error jika gagal
    FOREIGN KEY (user_id) REFERENCES akun(id) ON DELETE SET NULL
) ENGINE=InnoDB COMMENT='Log pengiriman email notifikasi';

/*
INDEKS DAN OPTIMASI:
- Indeks pada kolom yang sering di-query
- Indeks komposit untuk filtering
- Optimasi untuk query notifikasi
*/

-- Indeks untuk optimasi query notifikasi
CREATE INDEX idx_notifikasi_user_status ON notifikasi(user_id, status);
CREATE INDEX idx_notifikasi_tanggal ON notifikasi(tanggal_kirim);
CREATE INDEX idx_email_log_status ON email_log(status, tanggal_kirim);

/*
CONTOH PENGGUNAAN:

1. Membuat notifikasi baru:
INSERT INTO notifikasi (user_id, judul, pesan, tipe, ref_id, ref_tipe)
VALUES ('user_id', 'Tagihan Baru', 'Anda memiliki tagihan baru', 'tagihan', 'tagihan_id', 'tagihan');

2. Update status notifikasi:
UPDATE notifikasi 
SET status = 'dibaca', tanggal_baca = CURRENT_TIMESTAMP 
WHERE id = 'notifikasi_id';

3. Log pengiriman email:
INSERT INTO email_log (user_id, email, subject, body, status)
VALUES ('user_id', 'user@email.com', 'Notifikasi Tagihan', 'Isi email', 'pending');
*/

/*
MAINTENANCE:
- Pembersihan log email yang sudah lama
- Archive notifikasi yang sudah dibaca
- Monitoring failed emails
- Backup regular
*/

-- Prosedur untuk membersihkan log email lama
DELIMITER //
CREATE PROCEDURE sp_cleanup_email_logs(IN days_to_keep INT)
BEGIN
    DELETE FROM email_log 
    WHERE tanggal_kirim < DATE_SUB(CURRENT_DATE, INTERVAL days_to_keep DAY)
    AND status IN ('sent', 'failed');
END //
DELIMITER ;

/*
KEAMANAN:
- Validasi input email
- Enkripsi data sensitif
- Rate limiting pengiriman
- Audit trail
*/

-- Trigger untuk audit log
DELIMITER //
CREATE TRIGGER tr_notifikasi_audit
AFTER INSERT ON notifikasi
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (table_name, record_id, action, new_values)
    VALUES ('notifikasi', NEW.id, 'INSERT', 
        JSON_OBJECT(
            'user_id', NEW.user_id,
            'tipe', NEW.tipe,
            'status', NEW.status
        )
    );
END //
DELIMITER ;

-- =====================================================

ALTER TABLE transaksi_kas ADD INDEX idx_tanggal_transaksi (tanggal_transaksi);
ALTER TABLE notifikasi ADD INDEX idx_status_tanggal (status, tanggal_kirim);

-- =====================================================

-- Trigger untuk notifikasi otomatis sudah didefinisikan sebelumnya
-- Menghapus definisi duplikat di sini untuk menghindari error

-- =====================================================
