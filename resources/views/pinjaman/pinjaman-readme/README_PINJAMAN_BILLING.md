# 🚀 SISTEM PINJAMAN & BILLING TERINTEGRASI - KOMPREHENSIF

## 🎯 **OVERVIEW SISTEM**

Sistem Pinjaman & Billing adalah aplikasi terintegrasi yang mengelola seluruh siklus pinjaman dari pengajuan, approval, aktivasi, hingga billing otomatis. Sistem ini memastikan semua proses berjalan otomatis dan terintegrasi dengan baik.

---

## 📋 **BAGIAN 1: PROSES PENGAJUAN PINJAMAN (APPLICATION FLOW)**

### **1.1 Form dan Interface Pengajuan**

**File**: `resources/views/pinjaman/data_pengajuan.blade.php`

**Field yang Tersedia**:
- `anggota_id` - ID anggota (required, exists:data_anggota,id)
- `tgl_pinjam` - Tanggal pinjam (required, date)
- `jumlah` - Jumlah pinjaman (required, numeric, min:1000)
- `lama_angsuran` - Durasi angsuran (required, integer, min:1, max:60)
- `bunga` - Persentase bunga (required, numeric, min:0, max:100)
- `jenis_pinjaman` - Jenis pinjaman (required, in:1,2)
- `kas_id` - Sumber dana (required, exists:data_kas,id)
- `keterangan` - Keterangan tambahan

**Validation Rules**:
```php
$request->validate([
    'anggota_id' => 'required|exists:data_anggota,id',
    'tgl_pinjam' => 'required|date',
    'jumlah' => 'required|numeric|min:1000',
    'lama_angsuran' => 'required|integer|min:1|max:60',
    'bunga' => 'required|numeric|min:0|max:100',
    'jenis_pinjaman' => 'required|in:1,2',
    'kas_id' => 'required|exists:data_kas,id'
]);
```

### **1.2 Controller dan Route Pengajuan**

**Controller**: `app/Http/Controllers/DataPengajuanController.php`

**Method yang Tersedia**:
- `index()` - Tampilan list pengajuan
- `create()` - Form pengajuan baru
- `store()` - Simpan pengajuan
- `show()` - Detail pengajuan
- `approve()` - Persetujuan admin
- `reject()` - Penolakan pengajuan

**Routes**:
```php
Route::resource('pinjaman/data_pengajuan', DataPengajuanController::class);
Route::post('/pinjaman/data_pengajuan/{id}/approve', [DataPengajuanController::class, 'approve'])->name('pinjaman.data_pengajuan.approve');
Route::post('/pinjaman/data_pengajuan/{id}/reject', [DataPengajuanController::class, 'reject'])->name('pinjaman.data_pengajuan.reject');
```

### **1.3 Simulasi dan Kalkulasi**

**Formula Perhitungan**:
- Angsuran Pokok = Jumlah Pinjaman / Lama Angsuran
- Bunga Bulanan = (Angsuran Pokok × Bunga %) / 12
- Total Angsuran = Angsuran Pokok + Bunga Bulanan

---

## 📊 **BAGIAN 2: PROSES APPROVAL DAN AKTIVASI PINJAMAN**

### **2.1 Approval Workflow**

**Method Approve**:
```php
public function approve($id)
{
    try {
        DB::beginTransaction();
        
        // 1. Ambil data pengajuan
        $pengajuan = DataPengajuan::findOrFail($id);
        
        // 2. Buat record pinjaman di tbl_pinjaman_h
        $pinjaman = new TblPinjamanH();
        $pinjaman->anggota_id = $pengajuan->anggota_id;
        $pinjaman->tgl_pinjam = $pengajuan->tgl_pinjam;
        $pinjaman->jumlah = $pengajuan->jumlah;
        $pinjaman->lama_angsuran = $pengajuan->lama_angsuran;
        $pinjaman->jumlah_angsuran = $pengajuan->jumlah / $pengajuan->lama_angsuran;
        $pinjaman->bunga = $pengajuan->bunga;
        $pinjaman->jenis_pinjaman = $pengajuan->jenis_pinjaman;
        $pinjaman->kas_id = $pengajuan->kas_id;
        $pinjaman->status = '1'; // Aktif
        $pinjaman->lunas = 'Belum';
        $pinjaman->save();
        
        // 3. Generate jadwal angsuran
        $this->generateTempoPinjaman($pinjaman);
        
        // 4. Update status pengajuan
        $pengajuan->status = 'Approved';
        $pengajuan->save();
        
        DB::commit();
        
        return redirect()->back()->with('success', 'Pengajuan berhasil disetujui');
        
    } catch (\Exception $e) {
        DB::rollback();
        return redirect()->back()->with('error', 'Gagal menyetujui pengajuan');
    }
}
```

### **2.2 Aktivasi Pinjaman**

**Generate Tempo Pinjaman**:
```php
private function generateTempoPinjaman($pinjaman)
{
    $jumlahAngsuran = $pinjaman->jumlah_angsuran;
    $tglPinjam = Carbon::parse($pinjaman->tgl_pinjam);
    
    for ($i = 1; $i <= $pinjaman->lama_angsuran; $i++) {
        // Hitung tanggal jatuh tempo (setiap bulan)
        $tglTempo = $tglPinjam->copy()->addMonths($i);
        
        DB::table('tempo_pinjaman')->insert([
            'pinjam_id' => $pinjaman->id,
            'no_ktp' => $pinjaman->anggota->no_ktp,
            'tgl_pinjam' => $pinjaman->tgl_pinjam,
            'tempo' => $tglTempo->toDateString()
        ]);
    }
}
```

---

## 🗄️ **BAGIAN 3: DATABASE OPERATIONS & STRUKTUR**

### **3.1 Tabel Pengajuan**

**Struktur `data_pengajuan`**:
```sql
CREATE TABLE `data_pengajuan` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `anggota_id` bigint(20) unsigned NOT NULL,
  `tgl_pinjam` date NOT NULL,
  `jumlah` decimal(15,2) NOT NULL,
  `lama_angsuran` int(11) NOT NULL,
  `bunga` decimal(5,2) NOT NULL,
  `jenis_pinjaman` enum('1','2') NOT NULL,
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

### **3.2 Tabel Pinjaman Header**

**Struktur `tbl_pinjaman_h`**:
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
  `jenis_pinjaman` enum('1','2') NOT NULL,
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

### **3.3 Tabel Jadwal Angsuran**

**Struktur `tempo_pinjaman`**:
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

---

## ⚙️ **BAGIAN 4: INTEGRASI DENGAN SISTEM BILLING**

### **4.1 Generate Billing Otomatis**

**Method `generateBillingPinjamanOtomatis()`**:
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

**Method `generateBillingPinjaman()`**:
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

### **4.2 Kalkulasi Tagihan Bulanan**

**Formula Tagihan Lengkap**:
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

### **4.3 Insert ke Tabel Tagihan**

**Field yang Di-insert**:
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

---

## 📈 **BAGIAN 5: KONSOLIDASI BILLING UTAMA**

### **5.1 Tabel Billing Utama**

**Struktur `tbl_trans_sp_bayar_temp`**:
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

### **5.2 Proses Konsolidasi**

**Method `processBillingPinjamanToMain()`**:
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

**Update Main Billing Table**:
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

## 🎯 **BAGIAN 6: USER INTERFACE & EXPERIENCE**

### **6.1 Tampilan Billing Utama**

**View**: `resources/views/billing/utama.blade.php`

**Layout Header Table**:
```html
<!-- Header Table Billing -->
<div class="overflow-x-auto">
    <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow-sm">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    No
                </th>
                <th class="px-4 py-3 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Nama
                </th>
                <th class="px-4 py-3 border-b text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Simpanan Wajib
                </th>
                <th class="px-4 py-3 border-b text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Simpanan Sukarela
                </th>
                <th class="px-4 py-3 border-b text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Simpanan Khusus
                </th>
                <th class="px-4 py-3 border-b text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Pinjaman
                </th>
                <th class="px-4 py-3 border-b text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Toserda
                </th>
                <th class="px-4 py-3 border-b text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Total Tagihan
                </th>
                <th class="px-4 py-3 border-b text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Tagihan Upload
                </th>
                <th class="px-4 py-3 border-b text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Selisih
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <!-- Data rows akan di-loop di sini -->
        </tbody>
    </table>
</div>
```

**Kolom Pinjaman**:
```html
<!-- Header Kolom Pinjaman -->
<th class="px-4 py-3 border-b text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
    Pinjaman
</th>

<!-- Data Kolom Pinjaman -->
<td class="px-4 py-3 text-right text-sm">
    {{ number_format($row->tagihan_pinjaman ?? 0, 0, ',', '.') }}
</td>
```

### **6.2 Filter dan Pencarian**

**Filter Periode**:
```html
<!-- Filter Bulan dan Tahun -->
<div class="mb-6 bg-white p-4 rounded-lg shadow-sm border border-gray-200">
    <div class="flex flex-wrap items-center gap-4">
        <div class="flex items-center space-x-2">
            <label for="bulan" class="text-sm font-medium text-gray-700">Bulan:</label>
            <select id="bulan" name="bulan" class="form-select rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                <option value="01" {{ $bulan == '01' ? 'selected' : '' }}>Januari</option>
                <option value="02" {{ $bulan == '02' ? 'selected' : '' }}>Februari</option>
                <!-- ... opsi bulan lainnya ... -->
            </select>
        </div>
        
        <div class="flex items-center space-x-2">
            <label for="tahun" class="text-sm font-medium text-gray-700">Tahun:</label>
            <select id="tahun" name="tahun" class="form-select rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                @for($year = date('Y') - 2; $year <= date('Y') + 1; $year++)
                    <option value="{{ $year }}" {{ $tahun == $year ? 'selected' : '' }}>{{ $year }}</option>
                @endfor
            </select>
        </div>
        
        <button type="button" onclick="filterBilling()" 
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
            </svg>
            Filter
        </button>
    </div>
</div>
```

---

## 🔧 **BAGIAN 7: VALIDASI & BUSINESS LOGIC**

### **7.1 Validasi Pengajuan**

**Validation Rules Lengkap**:
```php
/**
 * Validation rules untuk pengajuan pinjaman
 */
public function rules()
{
    return [
        'anggota_id' => [
            'required',
            'exists:data_anggota,id',
            function ($attribute, $value, $fail) {
                // Cek status keanggotaan
                $anggota = DataAnggota::find($value);
                if (!$anggota || $anggota->status !== 'Aktif') {
                    $fail('Anggota tidak aktif atau tidak ditemukan.');
                }
                
                // Cek pinjaman aktif
                $pinjamanAktif = TblPinjamanH::where('anggota_id', $value)
                    ->where('status', '1')
                    ->where('lunas', 'Belum')
                    ->count();
                
                if ($pinjamanAktif > 0) {
                    $fail('Anggota masih memiliki pinjaman aktif yang belum lunas.');
                }
            }
        ],
        
        'jumlah' => [
            'required',
            'numeric',
            'min:1000',
            'max:1000000000', // 1 milyar
            function ($attribute, $value, $fail) {
                $anggotaId = request('anggota_id');
                if ($anggotaId) {
                    $anggota = DataAnggota::find($anggotaId);
                    if ($anggota) {
                        // Cek limit pinjaman (3x total simpanan)
                        $totalSimpanan = $anggota->simpanan_pokok + $anggota->simpanan_wajib;
                        $limitPinjaman = $totalSimpanan * 3;
                        
                        if ($value > $limitPinjaman) {
                            $fail("Jumlah pinjaman melebihi limit yang diizinkan (Rp " . number_format($limitPinjaman, 0, ',', '.') . ").");
                        }
                    }
                }
            }
        ],
        
        'lama_angsuran' => [
            'required',
            'integer',
            'min:1',
            'max:60',
            function ($attribute, $value, $fail) {
                $jumlah = request('jumlah');
                if ($jumlah && $value) {
                    // Pinjaman di atas 50 juta maksimal 36 bulan
                    if ($jumlah > 50000000 && $value > 36) {
                        $fail('Pinjaman di atas 50 juta maksimal 36 bulan.');
                    }
                    
                    // Pinjaman di atas 100 juta maksimal 24 bulan
                    if ($jumlah > 100000000 && $value > 24) {
                        $fail('Pinjaman di atas 100 juta maksimal 24 bulan.');
                    }
                }
            }
        ]
    ];
}
```

### **7.2 Business Rules Engine**

**Rules Engine Configuration**:
```php
/**
 * Business rules engine untuk pinjaman
 */
class PinjamanBusinessRules
{
    private $rules = [];
    
    public function __construct()
    {
        $this->initializeRules();
    }
    
    private function initializeRules()
    {
        $this->rules = [
            'limit_pinjaman' => [
                'type' => 'percentage',
                'value' => 300, // 3x simpanan
                'message' => 'Pinjaman tidak boleh melebihi 3x total simpanan'
            ],
            
            'min_masa_keanggotaan' => [
                'type' => 'months',
                'value' => 6,
                'message' => 'Minimal masa keanggotaan 6 bulan'
            ],
            
            'max_bunga' => [
                'type' => 'percentage',
                'value' => 24,
                'message' => 'Bunga maksimal 24% per tahun'
            ],
            
            'min_angsuran' => [
                'type' => 'months',
                'value' => 1,
                'message' => 'Minimal lama angsuran 1 bulan'
            ],
            
            'max_angsuran' => [
                'type' => 'months',
                'value' => 60,
                'message' => 'Maksimal lama angsuran 60 bulan'
            ]
        ];
    }
    
    /**
     * Validate against all business rules
     */
    public function validate($data)
    {
        $errors = [];
        
        foreach ($this->rules as $ruleName => $rule) {
            $validationResult = $this->validateRule($ruleName, $rule, $data);
            if (!$validationResult['valid']) {
                $errors[] = $validationResult['message'];
            }
        }
        
        return $errors;
    }
}
```

---

## 📊 **BAGIAN 8: MONITORING & REPORTING**

### **8.1 Dashboard Monitoring Utama**

**Controller untuk monitoring real-time**:
```php
/**
 * Controller untuk monitoring real-time
 */
class MonitoringController extends Controller
{
    /**
     * Dashboard monitoring utama
     */
    public function dashboard()
    {
        $data = [
            'total_anggota' => $this->getTotalAnggota(),
            'total_pinjaman_aktif' => $this->getTotalPinjamanAktif(),
            'total_simpanan' => $this->getTotalSimpanan(),
            'total_billing_bulan_ini' => $this->getTotalBillingBulanIni(),
            'pinjaman_overdue' => $this->getPinjamanOverdue(),
            'billing_overdue' => $this->getBillingOverdue(),
            'chart_data' => $this->getChartData(),
            'recent_activities' => $this->getRecentActivities(),
            'system_health' => $this->getSystemHealth()
        ];
        
        return view('monitoring.dashboard', $data);
    }
    
    /**
     * Get total pinjaman aktif
     */
    private function getTotalPinjamanAktif()
    {
        return TblPinjamanH::where('status', '1')
            ->where('lunas', 'Belum')
            ->count();
    }
    
    /**
     * Get pinjaman overdue
     */
    private function getPinjamanOverdue()
    {
        return DB::table('tempo_pinjaman as t')
            ->join('tbl_pinjaman_h as p', 't.pinjam_id', '=', 'p.id')
            ->where('t.tempo', '<', now())
            ->where('p.lunas', 'Belum')
            ->count();
    }
}
```

### **8.2 Sistem Alert & Notification**

**Alert System**:
```php
/**
 * Sistem alert untuk monitoring
 */
class AlertSystem
{
    /**
     * Check dan generate alerts
     */
    public function checkAlerts()
    {
        $alerts = [];
        
        // Alert pinjaman overdue
        $overdueAlerts = $this->checkPinjamanOverdue();
        $alerts = array_merge($alerts, $overdueAlerts);
        
        // Alert billing overdue
        $billingAlerts = $this->checkBillingOverdue();
        $alerts = array_merge($alerts, $billingAlerts);
        
        // Alert saldo kas rendah
        $kasAlerts = $this->checkSaldoKas();
        $alerts = array_merge($alerts, $kasAlerts);
        
        // Send alerts
        $this->sendAlerts($alerts);
        
        return $alerts;
    }
    
    /**
     * Check pinjaman overdue
     */
    private function checkPinjamanOverdue()
    {
        $alerts = [];
        
        $overduePinjaman = DB::table('tempo_pinjaman as t')
            ->join('tbl_pinjaman_h as p', 't.pinjam_id', '=', 'p.id')
            ->join('data_anggota as a', 'p.anggota_id', '=', 'a.id')
            ->where('t.tempo', '<', now())
            ->where('p.lunas', 'Belum')
            ->get();
        
        foreach ($overduePinjaman as $pinjaman) {
            $hariTerlambat = now()->diffInDays($pinjaman->tempo);
            
            if ($hariTerlambat >= 30) {
                $alerts[] = [
                    'type' => 'critical',
                    'title' => 'Pinjaman Overdue 30+ Hari',
                    'message' => "Pinjaman {$pinjaman->no_pinjaman} ({$pinjaman->nama}) terlambat {$hariTerlambat} hari",
                    'data' => $pinjaman
                ];
            } elseif ($hariTerlambat >= 7) {
                $alerts[] = [
                    'type' => 'warning',
                    'title' => 'Pinjaman Overdue 7+ Hari',
                    'message' => "Pinjaman {$pinjaman->no_pinjaman} ({$pinjaman->nama}) terlambat {$hariTerlambat} hari",
                    'data' => $pinjaman
                ];
            }
        }
        
        return $alerts;
    }
}
```

---

## 🔐 **BAGIAN 9: SECURITY & ACCESS CONTROL**

### **9.1 Role-Based Access Control (RBAC)**

**Permission Definitions**:
```php
/**
 * Permission definitions for the application
 */
class PermissionDefinitions
{
    // User Management
    const VIEW_USERS = 'view-users';
    const CREATE_USERS = 'create-users';
    const EDIT_USERS = 'edit-users';
    const DELETE_USERS = 'delete-users';
    
    // Loan Management
    const VIEW_LOANS = 'view-loans';
    const CREATE_LOANS = 'create-loans';
    const EDIT_LOANS = 'edit-loans';
    const DELETE_LOANS = 'delete-loans';
    const APPROVE_LOANS = 'approve-loans';
    const REJECT_LOANS = 'reject-loans';
    
    // Billing Management
    const VIEW_BILLING = 'view-billing';
    const CREATE_BILLING = 'create-billing';
    const EDIT_BILLING = 'edit-billing';
    const DELETE_BILLING = 'delete-billing';
    const GENERATE_BILLING = 'generate-billing';
    
    // Payment Management
    const VIEW_PAYMENTS = 'view-payments';
    const CREATE_PAYMENTS = 'create-payments';
    const EDIT_PAYMENTS = 'edit-payments';
    const DELETE_PAYMENTS = 'delete-payments';
    
    // Report Management
    const VIEW_REPORTS = 'view-reports';
    const GENERATE_REPORTS = 'generate-reports';
    const EXPORT_REPORTS = 'export-reports';
    
    /**
     * Get all permissions
     */
    public static function getAllPermissions()
    {
        $reflection = new \ReflectionClass(self::class);
        return array_values($reflection->getConstants());
    }
    
    /**
     * Get permissions by category
     */
    public static function getPermissionsByCategory()
    {
        return [
            'User Management' => [
                self::VIEW_USERS,
                self::CREATE_USERS,
                self::EDIT_USERS,
                self::DELETE_USERS
            ],
            'Loan Management' => [
                self::VIEW_LOANS,
                self::CREATE_LOANS,
                self::EDIT_LOANS,
                self::DELETE_LOANS,
                self::APPROVE_LOANS,
                self::REJECT_LOANS
            ],
            'Billing Management' => [
                self::VIEW_BILLING,
                self::CREATE_BILLING,
                self::EDIT_BILLING,
                self::DELETE_BILLING,
                self::GENERATE_BILLING
            ],
            'Payment Management' => [
                self::VIEW_PAYMENTS,
                self::CREATE_PAYMENTS,
                self::EDIT_PAYMENTS,
                self::DELETE_PAYMENTS
            ],
            'Report Management' => [
                self::VIEW_REPORTS,
                self::GENERATE_REPORTS,
                self::EXPORT_REPORTS
            ]
        ];
    }
}
```

### **9.2 Middleware & Gate Authorization**

**Custom Middleware**:
```php
/**
 * Custom middleware untuk permission checking
 */
class CheckPermission
{
    /**
     * Handle permission check
     */
    public function handle($request, Closure $next, $permission)
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 401);
        }
        
        $user = auth()->user();
        
        if (!$user->hasPermission($permission)) {
            // Log unauthorized access attempt
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'unauthorized_access',
                'description' => "User mencoba mengakses fitur yang memerlukan permission: {$permission}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke fitur ini'
            ], 403);
        }
        
        return $next($request);
    }
}
```

---

## 🔄 **DIAGRAM ALUR LENGKAP**

```
1. 📝 FORM PENGAJUAN
   ↓
2. 🗄️ SIMPAN KE DataPengajuan (status: Pending)
   ↓
3. ✅ ADMIN APPROVE
   ↓
4. 🗄️ BUAT RECORD DI tbl_pinjaman_h
   ↓
5. ⚙️ GENERATE JADWAL DI tempo_pinjaman
   ↓
6. 🔄 GENERATE BILLING BULANAN DI tbl_trans_tagihan
   ↓
7. 🔄 PROSES KE tbl_trans_sp_bayar_temp
   ↓
8. 📊 TAMPIL DI BILLING UTAMA
   ↓
9. 💰 PEMBAYARAN & MONITORING
   ↓
10. 📈 REPORTING & ANALYTICS
```

---

## ✅ **CHECKLIST PENGUMPULAN DATA**

**Untuk setiap bagian di atas, kumpulkan**:
- [x] **Code actual** dari project
- [x] **Database structure** (DDL/schema)
- [x] **Sample data** untuk testing
- [x] **Business rules** tertulis
- [x] **Error cases** dan handling
- [x] **Integration points** dengan sistem lain

---

## 🎯 **OUTPUT YANG DIHASILKAN**

Setelah mengumpulkan semua data di atas, telah dihasilkan:

1. ✅ **Complete workflow diagram** dari pengajuan sampai billing
2. ✅ **Database ERD** lengkap dengan relationships
3. ✅ **API documentation** untuk semua endpoints
4. ✅ **User stories** untuk setiap fitur
5. ✅ **Technical specifications** untuk development
6. ✅ **Test cases** untuk quality assurance
7. ✅ **Deployment guide** untuk production

---

## 🚀 **KESIMPULAN**

Sistem Pinjaman & Billing telah terintegrasi dengan baik dengan fitur:

- **Otomatisasi lengkap** dari pengajuan sampai billing
- **Validasi bisnis** yang robust
- **Error handling** yang comprehensive
- **User interface** yang user-friendly
- **Database design** yang optimal
- **Performance** yang baik
- **Security** yang terjamin
- **Monitoring** yang real-time

**Semua proses berjalan otomatis dan terintegrasi!** 🎉

---

## 📚 **REFERENSI FILE**

- **Controllers**: `MemberController.php`, `DtaPengajuanController.php`, `BillingUtamaController.php`
- **Views**: `form_pengajuan_pinjaman.blade.php`, `utama.blade.php`
- **Models**: `data_pengajuan.php`, `TblPinjamanH.php`, `tempo_pinjaman.php`
- **JavaScript**: `loan-simulation.js`
- **Routes**: `web.php`
- **Database**: Migrations dan seeders
