# ⚙️ BAGIAN 4: INTEGRASI DENGAN SISTEM BILLING

## 🎯 **OVERVIEW INTEGRASI BILLING**

Bagian ini menjelaskan bagaimana sistem pinjaman terintegrasi dengan sistem billing utama. Setelah pinjaman diapprove, sistem akan otomatis generate tagihan bulanan dan mengkonsolidasikannya ke dalam billing utama.

---

## 🔄 **4.1 GENERATE BILLING OTOMATIS**

### **Method `generateBillingPinjamanOtomatis()`**:
```php
/**
 * Generate billing pinjaman otomatis untuk bulan tertentu
 */
private function generateBillingPinjamanOtomatis($bulan, $tahun)
{
    try {
        // 1. Generate billing pinjaman untuk bulan tertentu
        $this->generateBillingPinjaman($bulan, $tahun);
        
        // 2. Proses ke tabel utama
        $this->processBillingPinjamanToMain($bulan, $tahun);
        
        Log::info("Billing pinjaman berhasil di-generate untuk {$bulan}-{$tahun}");
        
    } catch (\Exception $e) {
        Log::error("Gagal generate billing pinjaman: " . $e->getMessage());
        throw $e;
    }
}
```

### **Method `generateBillingPinjaman()`**:
```php
/**
 * Generate tagihan bulanan pinjaman
 */
private function generateBillingPinjaman($bulan, $tahun)
{
    // 1. Hapus billing lama untuk bulan ini
    DB::table('tbl_trans_tagihan')
        ->where('jenis_id', 999) // ID untuk pinjaman
        ->whereMonth('tgl_transaksi', $bulan)
        ->whereYear('tgl_transaksi', $tahun)
        ->delete();
    
    // 2. Ambil data pinjaman aktif dengan jadwal angsuran
    $pinjamanAktif = DB::table('tbl_pinjaman_h as p')
        ->join('tempo_pinjaman as t', 'p.id', '=', 't.pinjam_id')
        ->join('data_anggota as a', 'p.anggota_id', '=', 'a.id')
        ->where('p.status', '1') // Aktif
        ->where('p.lunas', 'Belum') // Belum lunas
        ->whereMonth('t.tempo', $bulan)
        ->whereYear('t.tempo', $tahun)
        ->select([
            'a.no_ktp',
            'p.jumlah_angsuran',
            'p.bunga',
            't.tempo',
            't.no_urut',
            't.pokok',
            't.bunga as bunga_bulanan',
            't.total_angsuran'
        ])
        ->get();
    
    // 3. Generate tagihan bulanan
    foreach ($pinjamanAktif as $pinjaman) {
        // Hitung jumlah tagihan
        $tagihanBulanan = $pinjaman->total_angsuran;
        
        // Insert ke tbl_trans_tagihan
        DB::table('tbl_trans_tagihan')->insert([
            'tgl_transaksi' => $pinjaman->tempo,
            'no_ktp' => $pinjaman->no_ktp,
            'jenis_id' => 999, // ID untuk pinjaman
            'jumlah' => $tagihanBulanan,
            'keterangan' => "Tagihan Angsuran Pinjaman ke-{$pinjaman->no_urut} ({$bulan}-{$tahun})",
            'status_bayar' => 'Belum',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
    
    Log::info("Berhasil generate " . count($pinjamanAktif) . " tagihan pinjaman untuk {$bulan}-{$tahun}");
}
```

---

## 🧮 **4.2 KALKULASI TAGIHAN BULANAN**

### **Formula Tagihan Lengkap**:
```php
/**
 * Hitung komponen tagihan pinjaman
 */
private function hitungKomponenTagihan($pinjaman)
{
    $jumlahPinjaman = $pinjaman->jumlah;
    $lamaAngsuran = $pinjaman->lama_angsuran;
    $bunga = $pinjaman->bunga;
    
    // 1. Angsuran Pokok (Flat)
    $angsuranPokok = $jumlahPinjaman / $lamaAngsuran;
    
    // 2. Bunga Bulanan
    $bungaBulanan = ($angsuranPokok * $bunga / 100) / 12;
    
    // 3. Total Angsuran Bulanan
    $totalAngsuran = $angsuranPokok + $bungaBulanan;
    
    // 4. Total Bunga Seluruh Masa
    $totalBunga = $bungaBulanan * $lamaAngsuran;
    
    // 5. Total Pembayaran Seluruh Masa
    $totalPembayaran = $totalAngsuran * $lamaAngsuran;
    
    return [
        'angsuran_pokok' => $angsuranPokok,
        'bunga_bulanan' => $bungaBulanan,
        'total_angsuran' => $totalAngsuran,
        'total_bunga' => $totalBunga,
        'total_pembayaran' => $totalPembayaran
    ];
}
```

### **Handle Bunga Berbeda**:
```php
/**
 * Hitung bunga berdasarkan jenis pinjaman
 */
private function hitungBungaBulanan($pinjaman)
{
    $jenisPinjaman = $pinjaman->jenis_pinjaman;
    $jumlahPinjaman = $pinjaman->jumlah;
    $bunga = $pinjaman->bunga;
    
    switch ($jenisPinjaman) {
        case '1': // Pinjaman Biasa
            // Bunga flat per bulan
            return ($jumlahPinjaman * $bunga / 100) / 12;
            
        case '2': // Pinjaman Barang
            // Bunga flat per bulan dengan minimum
            $bungaBulanan = ($jumlahPinjaman * $bunga / 100) / 12;
            return max($bungaBulanan, 50000); // Minimum Rp 50.000
            
        default:
            return 0;
    }
}
```

---

## 📊 **4.3 INSERT KE TABEL TAGIHAN**

### **Field yang Di-insert**:
```php
/**
 * Data yang di-insert ke tbl_trans_tagihan
 */
$dataTagihan = [
    'tgl_transaksi' => $pinjaman->tempo,
    'no_ktp' => $pinjaman->no_ktp,
    'jenis_id' => 999, // ID untuk pinjaman
    'jumlah' => $totalTagihan,
    'keterangan' => $keterangan,
    'status_bayar' => 'Belum',
    'tgl_bayar' => null,
    'jumlah_bayar' => null,
    'created_at' => now(),
    'updated_at' => now()
];

// Insert dengan batch untuk performa
DB::table('tbl_trans_tagihan')->insert($dataTagihan);
```

### **Handle Duplicate Data**:
```php
/**
 * Handle duplicate data dengan updateOrInsert
 */
private function insertTagihanPinjaman($data)
{
    foreach ($data as $tagihan) {
        DB::table('tbl_trans_tagihan')->updateOrInsert(
            [
                'tgl_transaksi' => $tagihan['tgl_transaksi'],
                'no_ktp' => $tagihan['no_ktp'],
                'jenis_id' => $tagihan['jenis_id']
            ],
            [
                'jumlah' => $tagihan['jumlah'],
                'keterangan' => $tagihan['keterangan'],
                'status_bayar' => $tagihan['status_bayar'],
                'updated_at' => now()
            ]
        );
    }
}
```

---

## 📈 **4.4 KONSOLIDASI BILLING UTAMA**

### **Method `processBillingPinjamanToMain()`**:
```php
/**
 * Proses tagihan pinjaman ke tabel utama
 */
private function processBillingPinjamanToMain($bulan, $tahun)
{
    try {
        // 1. Ambil data tagihan pinjaman
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

## 🔄 **4.5 TRIGGER DAN OTOMATISASI**

### **Auto Generate Billing**:
```php
/**
 * Auto generate billing saat buka halaman
 */
public function index(Request $request)
{
    $bulan = $request->input('bulan', date('m'));
    $tahun = $request->input('tahun', date('Y'));
    
    // Generate billing pinjaman otomatis
    $this->generateBillingPinjamanOtomatis($bulan, $tahun);
    
    // Query data untuk ditampilkan
    $data = $this->getBillingData($bulan, $tahun);
    
    return view('billing.utama', compact('data', 'bulan', 'tahun'));
}
```

### **Scheduled Job (Cron)**:
```php
// File: app/Console/Commands/GenerateBillingPinjaman.php

class GenerateBillingPinjaman extends Command
{
    protected $signature = 'billing:generate-pinjaman {bulan?} {tahun?}';
    protected $description = 'Generate billing pinjaman bulanan';
    
    public function handle()
    {
        $bulan = $this->argument('bulan') ?: date('m');
        $tahun = $this->argument('tahun') ?: date('Y');
        
        $this->info("Generating billing pinjaman untuk {$bulan}-{$tahun}...");
        
        try {
            $controller = new BillingUtamaController();
            $controller->generateBillingPinjamanOtomatis($bulan, $tahun);
            
            $this->info("Billing pinjaman berhasil di-generate!");
            
        } catch (\Exception $e) {
            $this->error("Gagal generate billing: " . $e->getMessage());
        }
    }
}

// Cron job (setiap tanggal 1 bulan)
// 0 0 1 * * cd /path/to/project && php artisan billing:generate-pinjaman
```

---

## 📊 **4.6 MONITORING DAN REPORTING**

### **Billing Statistics**:
```php
/**
 * Get billing pinjaman statistics
 */
public function getBillingPinjamanStats($bulan, $tahun)
{
    return [
        'total_tagihan' => DB::table('tbl_trans_tagihan')
            ->where('jenis_id', 999)
            ->whereMonth('tgl_transaksi', $bulan)
            ->whereYear('tgl_transaksi', $tahun)
            ->sum('jumlah'),
        
        'total_terbayar' => DB::table('tbl_trans_tagihan')
            ->where('jenis_id', 999)
            ->whereMonth('tgl_transaksi', $bulan)
            ->whereYear('tgl_transaksi', $tahun)
            ->where('status_bayar', 'Sudah')
            ->sum('jumlah_bayar'),
        
        'total_belum_bayar' => DB::table('tbl_trans_tagihan')
            ->where('jenis_id', 999)
            ->whereMonth('tgl_transaksi', $bulan)
            ->whereYear('tgl_transaksi', $tahun)
            ->where('status_bayar', 'Belum')
            ->sum('jumlah'),
        
        'total_terlambat' => DB::table('tbl_trans_tagihan')
            ->where('jenis_id', 999)
            ->whereMonth('tgl_transaksi', $bulan)
            ->whereYear('tgl_transaksi', $tahun)
            ->where('status_bayar', 'Terlambat')
            ->sum('jumlah')
    ];
}
```

### **Debug dan Troubleshooting**:
```php
/**
 * Debug data billing pinjaman
 */
public function debugBillingPinjaman($bulan, $tahun)
{
    $debugData = [
        'tagihan_pinjaman' => DB::table('tbl_trans_tagihan')
            ->where('jenis_id', 999)
            ->whereMonth('tgl_transaksi', $bulan)
            ->whereYear('tgl_transaksi', $tahun)
            ->get(),
        
        'main_billing' => DB::table('tbl_trans_sp_bayar_temp')
            ->whereMonth('tgl_transaksi', $bulan)
            ->whereYear('tgl_transaksi', $tahun)
            ->get(),
        
        'pinjaman_aktif' => DB::table('tbl_pinjaman_h')
            ->where('status', '1')
            ->where('lunas', 'Belum')
            ->count()
    ];
    
    return response()->json($debugData);
}
```

---

## 🚀 **KESIMPULAN BAGIAN 4**

Bagian 4 ini telah mencakup secara lengkap:

✅ **Generate Billing Otomatis** - Proses generate tagihan bulanan
✅ **Kalkulasi Tagihan** - Formula perhitungan yang akurat
✅ **Insert ke Tabel Tagihan** - Data flow ke tbl_trans_tagihan
✅ **Konsolidasi Billing** - Proses ke tabel utama
✅ **Trigger dan Otomatisasi** - Auto generate dan cron job
✅ **Monitoring** - Statistics dan debugging tools

**Next Step**: Lanjut ke Bagian 5 untuk Konsolidasi Billing Utama.

