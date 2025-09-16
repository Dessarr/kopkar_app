# 🗄️ BAGIAN 3: DATABASE OPERATIONS & STRUKTUR

## 🎯 **OVERVIEW DATABASE STRUCTURE**

Bagian ini menjelaskan struktur database lengkap yang digunakan dalam sistem pinjaman dan billing.

---

## 📋 **3.1 TABEL PENGAJUAN**

### **Struktur `data_pengajuan`**:
```sql
CREATE TABLE `data_pengajuan` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `anggota_id` bigint(20) unsigned NOT NULL,
  `tgl_pinjam` date NOT NULL,
  `jumlah` decimal(15,2) NOT NULL,
  `lama_angsuran` int(11) NOT NULL,
  `bunga` decimal(5,2) NOT NULL,
  `jenis_pinjaman` enum('1','3') NOT NULL COMMENT '1=Biasa, 3=Barang',
  `kas_id` bigint(20) unsigned NOT NULL,
  `keterangan` text,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `approved_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `data_pengajuan_anggota_id_foreign` (`anggota_id`),
  KEY `data_pengajuan_kas_id_foreign` (`kas_id`)
);
```

### **Field Descriptions**:
- `id` - Primary key auto increment
- `anggota_id` - Foreign key ke data_anggota
- `tgl_pinjam` - Tanggal pengajuan pinjaman
- `jumlah` - Jumlah pinjaman
- `lama_angsuran` - Durasi angsuran dalam bulan
- `bunga` - Persentase bunga per tahun
- `jenis_pinjaman` - 1=Biasa, 3=Barang
- `kas_id` - Foreign key ke data_kas
- `status` - Status pengajuan (Pending/Approved/Rejected)

---

## 🏦 **3.2 TABEL PINJAMAN HEADER**

### **Struktur `tbl_pinjaman_h`**:
```sql
CREATE TABLE `tbl_pinjaman_h` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `anggota_id` bigint(20) unsigned NOT NULL,
  `no_pinjaman` varchar(20) NOT NULL COMMENT 'Format: PINJ-YYYY-MM-XXXX',
  `tgl_pinjam` date NOT NULL,
  `jumlah` decimal(15,2) NOT NULL,
  `lama_angsuran` int(11) NOT NULL,
  `jumlah_angsuran` decimal(15,2) NOT NULL COMMENT 'Angsuran per bulan',
  `bunga` decimal(5,2) NOT NULL,
  `jenis_pinjaman` enum('1','3') NOT NULL,
  `kas_id` bigint(20) unsigned NOT NULL,
  `status` enum('0','1') DEFAULT '1' COMMENT '0=Nonaktif, 1=Aktif',
  `lunas` enum('Belum','Sudah') DEFAULT 'Belum',
  `total_bayar` decimal(15,2) DEFAULT '0.00',
  `sisa_pokok` decimal(15,2) DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tbl_pinjaman_h_no_pinjaman_unique` (`no_pinjaman`),
  KEY `tbl_pinjaman_h_anggota_id_foreign` (`anggota_id`)
);
```

### **Field Descriptions**:
- `id` - Primary key auto increment
- `anggota_id` - Foreign key ke data_anggota
- `no_pinjaman` - Nomor pinjaman unik
- `tgl_pinjam` - Tanggal pinjaman
- `jumlah` - Jumlah pinjaman
- `lama_angsuran` - Durasi angsuran
- `jumlah_angsuran` - Angsuran per bulan
- `bunga` - Persentase bunga
- `status` - Status pinjaman (0=Nonaktif, 1=Aktif)
- `lunas` - Status pelunasan (Belum/Sudah)

---

## 📅 **3.3 TABEL JADWAL ANGSURAN**

### **Struktur `tempo_pinjaman`**:
```sql
CREATE TABLE `tempo_pinjaman` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pinjam_id` bigint(20) unsigned NOT NULL,
  `no_ktp` varchar(16) NOT NULL,
  `tgl_pinjam` date NOT NULL,
  `tempo` date NOT NULL COMMENT 'Tanggal jatuh tempo',
  `no_urut` int(11) NOT NULL COMMENT 'Urutan angsuran ke-n',
  `pokok` decimal(15,2) NOT NULL COMMENT 'Angsuran pokok',
  `bunga` decimal(15,2) NOT NULL COMMENT 'Bunga bulanan',
  `total_angsuran` decimal(15,2) NOT NULL COMMENT 'Total angsuran',
  `status_bayar` enum('Belum','Sudah','Terlambat') DEFAULT 'Belum',
  `tgl_bayar` date NULL DEFAULT NULL,
  `jumlah_bayar` decimal(15,2) NULL DEFAULT NULL,
  `denda` decimal(15,2) DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tempo_pinjaman_pinjam_id_foreign` (`pinjam_id`),
  KEY `tempo_pinjaman_no_ktp_index` (`no_ktp`)
);
```

### **Field Descriptions**:
- `id` - Primary key auto increment
- `pinjam_id` - Foreign key ke tbl_pinjaman_h
- `no_ktp` - Nomor KTP anggota
- `tempo` - Tanggal jatuh tempo
- `no_urut` - Urutan angsuran
- `pokok` - Angsuran pokok
- `bunga` - Bunga bulanan
- `total_angsuran` - Total angsuran (pokok + bunga)
- `status_bayar` - Status pembayaran

---

## 💰 **3.4 TABEL TRANSAKSI TAGIHAN**

### **Struktur `tbl_trans_tagihan`**:
```sql
CREATE TABLE `tbl_trans_tagihan` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tgl_transaksi` date NOT NULL,
  `no_ktp` varchar(16) NOT NULL,
  `jenis_id` int(11) NOT NULL COMMENT '999=Pinjaman, 1=Simpanan Wajib',
  `jumlah` decimal(15,2) NOT NULL,
  `keterangan` text,
  `status_bayar` enum('Belum','Sudah','Terlambat') DEFAULT 'Belum',
  `tgl_bayar` date NULL DEFAULT NULL,
  `jumlah_bayar` decimal(15,2) NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tbl_trans_tagihan_no_ktp_index` (`no_ktp`),
  KEY `tbl_trans_tagihan_jenis_id_index` (`jenis_id`)
);
```

### **Field Descriptions**:
- `id` - Primary key auto increment
- `tgl_transaksi` - Tanggal transaksi
- `no_ktp` - Nomor KTP anggota
- `jenis_id` - Jenis tagihan (999=Pinjaman)
- `jumlah` - Jumlah tagihan
- `status_bayar` - Status pembayaran

---

## 🏠 **3.5 TABEL BILLING UTAMA**

### **Struktur `tbl_trans_sp_bayar_temp`**:
```sql
CREATE TABLE `tbl_trans_sp_bayar_temp` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tgl_transaksi` date NOT NULL,
  `no_ktp` varchar(16) NOT NULL,
  `tagihan_simpanan_wajib` decimal(15,2) DEFAULT '0.00',
  `tagihan_simpanan_sukarela` decimal(15,2) DEFAULT '0.00',
  `tagihan_simpanan_khusus_2` decimal(15,2) DEFAULT '0.00',
  `tagihan_pinjaman` decimal(15,2) DEFAULT '0.00',
  `tagihan_pinjaman_jasa` decimal(15,2) DEFAULT '0.00',
  `tagihan_toserda` decimal(15,2) DEFAULT '0.00',
  `total_tagihan` decimal(15,2) DEFAULT '0.00',
  `selisih` decimal(15,2) DEFAULT '0.00',
  `saldo_simpanan_sukarela` decimal(15,2) DEFAULT '0.00',
  `saldo_akhir_simpanan_sukarela` decimal(15,2) DEFAULT '0.00',
  `keterangan` text,
  `anggota_id` bigint(20) unsigned DEFAULT NULL,
  `jumlah` decimal(15,2) DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tbl_trans_sp_bayar_temp_tgl_ktp_unique` (`tgl_transaksi`, `no_ktp`)
);
```

### **Field Descriptions**:
- `id` - Primary key auto increment
- `tgl_transaksi` - Tanggal transaksi
- `no_ktp` - Nomor KTP anggota
- `tagihan_pinjaman` - Tagihan pinjaman
- `total_tagihan` - Total semua tagihan
- `selisih` - Selisih dengan pembayaran

---

## 🔗 **3.6 RELASI ANTAR TABEL**

### **Entity Relationship Diagram (ERD)**:
```
data_pengajuan (1) -----> (1) tbl_pinjaman_h
     |                           |
     |                           |
     v                           v
data_anggota <------------ tempo_pinjaman
     |                           |
     |                           |
     v                           v
data_kas <---------------- tbl_trans_tagihan
     |                           |
     |                           |
     v                           v
billing_upload_temp <----> tbl_trans_sp_bayar_temp
```

### **Foreign Key Relationships**:
```sql
-- data_pengajuan -> data_anggota
ALTER TABLE `data_pengajuan` 
ADD CONSTRAINT `data_pengajuan_anggota_id_foreign` 
FOREIGN KEY (`anggota_id`) REFERENCES `data_anggota` (`id`) ON DELETE CASCADE;

-- tbl_pinjaman_h -> data_anggota
ALTER TABLE `tbl_pinjaman_h` 
ADD CONSTRAINT `tbl_pinjaman_h_anggota_id_foreign` 
FOREIGN KEY (`anggota_id`) REFERENCES `data_anggota` (`id`) ON DELETE CASCADE;

-- tempo_pinjaman -> tbl_pinjaman_h
ALTER TABLE `tempo_pinjaman` 
ADD CONSTRAINT `tempo_pinjaman_pinjam_id_foreign` 
FOREIGN KEY (`pinjam_id`) REFERENCES `tbl_pinjaman_h` (`id`) ON DELETE CASCADE;
```

---

## 📊 **3.7 INDEXES DAN PERFORMANCE**

### **Index Strategy**:
```sql
-- Single Column Indexes
CREATE INDEX idx_data_pengajuan_status ON data_pengajuan(status);
CREATE INDEX idx_tbl_pinjaman_h_status ON tbl_pinjaman_h(status);
CREATE INDEX idx_tempo_pinjaman_tempo ON tempo_pinjaman(tempo);

-- Composite Indexes
CREATE INDEX idx_tbl_trans_tagihan_jenis_ktp ON tbl_trans_tagihan(jenis_id, no_ktp);
CREATE INDEX idx_tbl_trans_sp_bayar_temp_tgl_ktp ON tbl_trans_sp_bayar_temp(tgl_transaksi, no_ktp);
```

---

## 🚀 **KESIMPULAN BAGIAN 3**

Bagian 3 ini telah mencakup secara lengkap:

✅ **Database Structure** - Semua tabel dengan DDL lengkap
✅ **Field Descriptions** - Penjelasan detail setiap field
✅ **Indexes & Performance** - Strategy untuk optimasi query
✅ **Relationships** - Foreign key dan cascade rules

**Next Step**: Lanjut ke Bagian 4 untuk Integrasi dengan Sistem Billing.
