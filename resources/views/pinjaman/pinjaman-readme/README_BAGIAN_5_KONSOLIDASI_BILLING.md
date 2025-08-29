# 📈 BAGIAN 5: KONSOLIDASI BILLING UTAMA

## 🎯 **OVERVIEW KONSOLIDASI BILLING**

Bagian ini menjelaskan proses konsolidasi semua jenis tagihan (simpanan, pinjaman, toserda) ke dalam tabel utama `tbl_trans_sp_bayar_temp`. Sistem ini memungkinkan admin melihat total tagihan per anggota dalam satu dashboard yang terintegrasi.

---

## 🏠 **5.1 TABEL BILLING UTAMA**

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
  UNIQUE KEY `tbl_trans_sp_bayar_temp_tgl_ktp_unique` (`tgl_transaksi`, `no_ktp`),
  KEY `tbl_trans_sp_bayar_temp_no_ktp_index` (`no_ktp`),
  KEY `tbl_trans_sp_bayar_temp_tgl_transaksi_index` (`tgl_transaksi`)
);
```

### **Field Descriptions**:
- `id` - Primary key auto increment
- `tgl_transaksi` - Tanggal transaksi (akhir bulan)
- `no_ktp` - Nomor KTP anggota
- `tagihan_simpanan_wajib` - Tagihan simpanan wajib bulanan
- `tagihan_simpanan_sukarela` - Tagihan simpanan sukarela
- `tagihan_simpanan_khusus_2` - Tagihan simpanan khusus
- `tagihan_pinjaman` - Tagihan angsuran pinjaman
- `tagihan_pinjaman_jasa` - Tagihan jasa pinjaman
- `tagihan_toserda` - Tagihan toserda
- `total_tagihan` - Total semua tagihan
- `selisih` - Selisih dengan pembayaran

---

## 🔄 **5.2 PROSES KONSOLIDASI**

### **Method `processBillingPinjamanToMain()`**:
```php
/**
 * Proses tagihan pinjaman ke tabel utama
 */
private function processBillingPinjamanToMain($bulan, $tahun)
{
    try {
        // 1. Ambil data tagihan pinjaman dari tbl_trans_tagihan
        $tagihanPinjaman = DB::table('tbl_trans_tagihan')
            ->select('no_ktp', DB::raw('SUM(jumlah) as total_pinjaman'))
            ->where('jenis_id', 999) // ID untuk pinjaman
            ->whereMonth('tgl_transaksi', $bulan)
            ->whereYear('tgl_transaksi', $tahun)
            ->groupBy('no_ktp')
            ->get();
        
        // 2. Update atau insert ke tabel utama
        foreach ($tagihanPinjaman as $tagihan) {
            $this->updateMainBillingTable($tagihan, $bulan, $tahun);
        }
        
        Log::info("Berhasil proses " . count($tagihanPinjaman) . " tagihan pinjaman ke tabel utama");
        
    } catch (\Exception $e) {
        Log::error("Gagal proses tagihan pinjaman ke tabel utama: " . $e->getMessage());
        throw $e;
    }
}
```

### **Update Main Billing Table**:
```php
/**
 * Update tabel utama billing
 */
private function updateMainBillingTable($tagihan, $bulan, $tahun)
{
    $tglTransaksi = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->toDateString();
    
    DB::table('tbl_trans_sp_bayar_temp')->updateOrInsert(
        [
            'tgl_transaksi' => $tglTransaksi,
            'no_ktp' => $tagihan->no_ktp
        ],
        [
            'tagihan_pinjaman' => $tagihan->total_pinjaman,
            'total_tagihan_simpanan' => DB::raw('COALESCE(tagihan_simpanan_wajib, 0) + COALESCE(tagihan_simpanan_sukarela, 0) + COALESCE(tagihan_simpanan_khusus_2, 0)'),
            'total_tagihan' => DB::raw('COALESCE(tagihan_simpanan_wajib, 0) + COALESCE(tagihan_simpanan_sukarela, 0) + COALESCE(tagihan_simpanan_khusus_2, 0) + COALESCE(tagihan_pinjaman, 0) + COALESCE(tagihan_toserda, 0)'),
            'updated_at' => now()
        ]
    );
}
```

---

## 📊 **5.3 QUERY UNTUK TAMPILAN**

### **Query di `BillingUtamaController@index()`**:
```php
/**
 * Query data untuk ditampilkan di billing utama
 */
public function getBillingData($bulan, $tahun)
{
    return DB::table('tbl_trans_sp_bayar_temp as t')
        ->leftJoin('tbl_anggota as a', 't.no_ktp', '=', 'a.no_ktp')
        ->leftJoin('billing_upload_temp as u', function($join) use ($bulan, $tahun) {
            $join->on('t.no_ktp', '=', 'u.no_ktp')
                  ->where('u.bulan', $bulan)
                  ->where('u.tahun', $tahun);
        })
        ->select([
            't.id',
            't.tgl_transaksi',
            't.no_ktp',
            'a.nama',
            't.tagihan_simpanan_wajib',
            't.tagihan_simpanan_sukarela',
            't.tagihan_simpanan_khusus_2',
            't.tagihan_pinjaman',
            't.tagihan_pinjaman_jasa',
            't.tagihan_toserda',
            't.total_tagihan',
            't.selisih',
            't.saldo_simpanan_sukarela',
            't.saldo_akhir_simpanan_sukarela',
            't.keterangan',
            't.anggota_id',
            't.jumlah',
            DB::raw('SUM(u.jumlah) as tagihan_upload'),
            DB::raw('(t.total_tagihan - COALESCE(SUM(u.jumlah), 0)) as selisih_calculated')
        ])
        ->whereMonth('t.tgl_transaksi', $bulan)
        ->whereYear('t.tgl_transaksi', $tahun)
        ->groupBy('t.id', 't.tgl_transaksi', 't.no_ktp', 't.tagihan_simpanan_wajib', 
                  't.tagihan_simpanan_sukarela', 't.tagihan_simpanan_khusus_2', 
                  't.tagihan_pinjaman', 't.tagihan_pinjaman_jasa', 't.tagihan_toserda', 
                  't.total_tagihan', 't.selisih', 't.saldo_simpanan_sukarela', 
                  't.saldo_akhir_simpanan_sukarela', 't.keterangan', 't.anggota_id', 
                  't.jumlah', 'u.bulan', 'u.tahun')
        ->orderBy('a.nama')
        ->get();
}
```

### **Optimized Query dengan Index**:
```php
/**
 * Query yang dioptimasi dengan index
 */
public function getBillingDataOptimized($bulan, $tahun)
{
    // Gunakan index untuk performa lebih baik
    $tglTransaksi = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->toDateString();
    
    return DB::table('tbl_trans_sp_bayar_temp as t')
        ->leftJoin('tbl_anggota as a', 't.no_ktp', '=', 'a.no_ktp')
        ->leftJoin('billing_upload_temp as u', function($join) use ($bulan, $tahun) {
            $join->on('t.no_ktp', '=', 'u.no_ktp')
                  ->where('u.bulan', $bulan)
                  ->where('u.tahun', $tahun);
        })
        ->select([
            't.*',
            'a.nama',
            DB::raw('COALESCE(SUM(u.jumlah), 0) as tagihan_upload'),
            DB::raw('(t.total_tagihan - COALESCE(SUM(u.jumlah), 0)) as selisih_calculated')
        ])
        ->where('t.tgl_transaksi', $tglTransaksi) // Gunakan index tgl_transaksi
        ->groupBy('t.id', 't.no_ktp', 'u.bulan', 'u.tahun')
        ->orderBy('a.nama')
        ->get();
}
```

---

## 🔧 **5.4 HANDLE KONFLIK DATA**

### **Resolve Data Conflicts**:
```php
/**
 * Handle konflik data saat konsolidasi
 */
private function resolveDataConflicts($bulan, $tahun)
{
    try {
        // 1. Cek data duplikat
        $duplicates = DB::table('tbl_trans_sp_bayar_temp')
            ->select('no_ktp', DB::raw('COUNT(*) as count'))
            ->whereMonth('tgl_transaksi', $bulan)
            ->whereYear('tgl_transaksi', $tahun)
            ->groupBy('no_ktp')
            ->having('count', '>', 1)
            ->get();
        
        // 2. Hapus data duplikat
        foreach ($duplicates as $duplicate) {
            DB::table('tbl_trans_sp_bayar_temp')
                ->where('no_ktp', $duplicate->no_ktp)
                ->whereMonth('tgl_transaksi', $bulan)
                ->whereYear('tgl_transaksi', $tahun)
                ->orderBy('id', 'desc')
                ->limit(1)
                ->delete();
        }
        
        // 3. Recalculate total_tagihan
        $this->recalculateTotalTagihan($bulan, $tahun);
        
        Log::info("Berhasil resolve " . count($duplicates) . " konflik data");
        
    } catch (\Exception $e) {
        Log::error("Gagal resolve konflik data: " . $e->getMessage());
        throw $e;
    }
}
```

### **Recalculate Total Tagihan**:
```php
/**
 * Recalculate total tagihan untuk semua record
 */
private function recalculateTotalTagihan($bulan, $tahun)
{
    $tglTransaksi = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->toDateString();
    
    DB::table('tbl_trans_sp_bayar_temp')
        ->where('tgl_transaksi', $tglTransaksi)
        ->update([
            'total_tagihan' => DB::raw('
                COALESCE(tagihan_simpanan_wajib, 0) + 
                COALESCE(tagihan_simpanan_sukarela, 0) + 
                COALESCE(tagihan_simpanan_khusus_2, 0) + 
                COALESCE(tagihan_pinjaman, 0) + 
                COALESCE(tagihan_pinjaman_jasa, 0) + 
                COALESCE(tagihan_toserda, 0)
            '),
            'updated_at' => now()
        ]);
}
```

---

## 📈 **5.5 PERFORMANCE OPTIMIZATION**

### **Batch Processing**:
```php
/**
 * Process data dalam batch untuk performa lebih baik
 */
private function processBillingInBatch($bulan, $tahun, $batchSize = 1000)
{
    $offset = 0;
    
    do {
        // Ambil data dalam batch
        $tagihanBatch = DB::table('tbl_trans_tagihan')
            ->select('no_ktp', DB::raw('SUM(jumlah) as total_pinjaman'))
            ->where('jenis_id', 999)
            ->whereMonth('tgl_transaksi', $bulan)
            ->whereYear('tgl_transaksi', $tahun)
            ->groupBy('no_ktp')
            ->offset($offset)
            ->limit($batchSize)
            ->get();
        
        // Process batch
        foreach ($tagihanBatch as $tagihan) {
            $this->updateMainBillingTable($tagihan, $bulan, $tahun);
        }
        
        $offset += $batchSize;
        
    } while ($tagihanBatch->count() == $batchSize);
}
```

### **Database Indexes**:
```sql
-- Indexes untuk optimasi query
CREATE INDEX idx_tbl_trans_sp_bayar_temp_tgl_ktp ON tbl_trans_sp_bayar_temp(tgl_transaksi, no_ktp);
CREATE INDEX idx_tbl_trans_sp_bayar_temp_no_ktp ON tbl_trans_sp_bayar_temp(no_ktp);
CREATE INDEX idx_billing_upload_temp_bulan_tahun ON billing_upload_temp(bulan, tahun);
CREATE INDEX idx_billing_upload_temp_no_ktp ON billing_upload_temp(no_ktp);

-- Composite index untuk join
CREATE INDEX idx_billing_upload_temp_ktp_bulan_tahun ON billing_upload_temp(no_ktp, bulan, tahun);
```

---

## 📊 **5.6 MONITORING DAN AUDIT**

### **Billing Audit Trail**:
```php
/**
 * Log semua perubahan data billing
 */
private function logBillingChanges($action, $data, $userId)
{
    DB::table('billing_audit_log')->insert([
        'action' => $action,
        'table_name' => 'tbl_trans_sp_bayar_temp',
        'record_id' => $data['id'] ?? null,
        'old_values' => json_encode($data['old'] ?? []),
        'new_values' => json_encode($data['new'] ?? []),
        'user_id' => $userId,
        'ip_address' => request()->ip(),
        'user_agent' => request()->userAgent(),
        'created_at' => now()
    ]);
}
```

### **Data Validation**:
```php
/**
 * Validasi data billing sebelum insert/update
 */
private function validateBillingData($data)
{
    $errors = [];
    
    // Validasi total tagihan
    $calculatedTotal = 
        ($data['tagihan_simpanan_wajib'] ?? 0) +
        ($data['tagihan_simpanan_sukarela'] ?? 0) +
        ($data['tagihan_simpanan_khusus_2'] ?? 0) +
        ($data['tagihan_pinjaman'] ?? 0) +
        ($data['tagihan_pinjaman_jasa'] ?? 0) +
        ($data['tagihan_toserda'] ?? 0);
    
    if (abs($calculatedTotal - ($data['total_tagihan'] ?? 0)) > 0.01) {
        $errors[] = "Total tagihan tidak sesuai dengan perhitungan";
    }
    
    // Validasi tanggal transaksi
    if (!empty($data['tgl_transaksi'])) {
        $tglTransaksi = Carbon::parse($data['tgl_transaksi']);
        if (!$tglTransaksi->isEndOfMonth()) {
            $errors[] = "Tanggal transaksi harus akhir bulan";
        }
    }
    
    return $errors;
}
```

---

## 🔄 **5.7 SYNC DENGAN SISTEM LAIN**

### **Sync ke Sistem Akuntansi**:
```php
/**
 * Sync data billing ke sistem akuntansi
 */
private function syncToAccountingSystem($bulan, $tahun)
{
    try {
        $billingData = DB::table('tbl_trans_sp_bayar_temp')
            ->whereMonth('tgl_transaksi', $bulan)
            ->whereYear('tgl_transaksi', $tahun)
            ->get();
        
        foreach ($billingData as $billing) {
            // Format data untuk sistem akuntansi
            $accountingData = [
                'transaction_date' => $billing->tgl_transaksi,
                'member_id' => $billing->no_ktp,
                'debit_account' => '1300', // Piutang Anggota
                'credit_account' => '4100', // Pendapatan
                'amount' => $billing->total_tagihan,
                'description' => "Tagihan Bulanan {$bulan}-{$tahun}",
                'reference' => "BILLING-{$bulan}-{$tahun}-{$billing->no_ktp}"
            ];
            
            // Kirim ke sistem akuntansi (API call)
            $this->sendToAccountingAPI($accountingData);
        }
        
        Log::info("Berhasil sync " . count($billingData) . " data ke sistem akuntansi");
        
    } catch (\Exception $e) {
        Log::error("Gagal sync ke sistem akuntansi: " . $e->getMessage());
        throw $e;
    }
}
```

---

## 🚀 **KESIMPULAN BAGIAN 5**

Bagian 5 ini telah mencakup secara lengkap:

✅ **Tabel Billing Utama** - Struktur dan field descriptions
✅ **Proses Konsolidasi** - Method untuk update tabel utama
✅ **Query untuk Tampilan** - Optimized query dengan index
✅ **Handle Konflik Data** - Resolve duplicates dan recalculation
✅ **Performance Optimization** - Batch processing dan indexes
✅ **Monitoring dan Audit** - Log changes dan validation
✅ **Sync dengan Sistem Lain** - Integrasi akuntansi

**Next Step**: Lanjut ke Bagian 6 untuk User Interface & Experience.

