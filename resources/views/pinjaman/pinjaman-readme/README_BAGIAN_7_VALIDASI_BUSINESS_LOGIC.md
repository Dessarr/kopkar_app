# 🔧 BAGIAN 7: VALIDASI & BUSINESS LOGIC

## 🎯 **OVERVIEW VALIDASI & BUSINESS LOGIC**

Bagian ini menjelaskan semua aturan validasi dan business logic yang diterapkan dalam sistem pinjaman dan billing. Validasi ini memastikan integritas data dan kepatuhan terhadap aturan bisnis yang telah ditetapkan.

---

## ✅ **7.1 VALIDASI PENGAJUAN**

### **Validation Rules Lengkap**:
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
        
        'tgl_pinjam' => [
            'required',
            'date',
            'after_or_equal:today',
            function ($attribute, $value, $fail) {
                // Cek apakah tanggal jatuh pada hari kerja
                $tglPinjam = Carbon::parse($value);
                if ($tglPinjam->isWeekend()) {
                    $fail('Tanggal pinjam tidak boleh jatuh pada hari libur.');
                }
                
                // Cek apakah tanggal tidak terlalu jauh ke depan
                if ($tglPinjam->diffInDays(now()) > 30) {
                    $fail('Tanggal pinjam tidak boleh lebih dari 30 hari ke depan.');
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
        ],
        
        'bunga' => [
            'required',
            'numeric',
            'min:0',
            'max:24', // Maksimal 24% per tahun
            function ($attribute, $value, $fail) {
                // Bunga harus sesuai dengan kebijakan perusahaan
                $jenisPinjaman = request('jenis_pinjaman');
                
                if ($jenisPinjaman == '1') { // Pinjaman Biasa
                    if ($value < 12 || $value > 18) {
                        $fail('Bunga pinjaman biasa harus antara 12% - 18% per tahun.');
                    }
                } elseif ($jenisPinjaman == '2') { // Pinjaman Barang
                    if ($value < 15 || $value > 24) {
                        $fail('Bunga pinjaman barang harus antara 15% - 24% per tahun.');
                    }
                }
            }
        ],
        
        'jenis_pinjaman' => [
            'required',
            'in:1,2'
        ],
        
        'kas_id' => [
            'required',
            'exists:data_kas,id',
            function ($attribute, $value, $fail) {
                $jumlah = request('jumlah');
                if ($jumlah) {
                    $kas = DataKas::find($value);
                    if ($kas && $kas->saldo < $jumlah) {
                        $fail('Saldo kas tidak mencukupi untuk jumlah pinjaman ini.');
                    }
                }
            }
        ],
        
        'keterangan' => [
            'nullable',
            'string',
            'max:500'
        ]
    ];
}
```

### **Custom Validation Messages**:
```php
/**
 * Custom validation messages
 */
public function messages()
{
    return [
        'anggota_id.required' => 'Anggota harus dipilih.',
        'anggota_id.exists' => 'Anggota yang dipilih tidak ditemukan.',
        'tgl_pinjam.required' => 'Tanggal pinjam harus diisi.',
        'tgl_pinjam.date' => 'Format tanggal pinjam tidak valid.',
        'tgl_pinjam.after_or_equal' => 'Tanggal pinjam tidak boleh di masa lalu.',
        'jumlah.required' => 'Jumlah pinjaman harus diisi.',
        'jumlah.numeric' => 'Jumlah pinjaman harus berupa angka.',
        'jumlah.min' => 'Jumlah pinjaman minimal Rp 1.000.',
        'jumlah.max' => 'Jumlah pinjaman maksimal Rp 1.000.000.000.',
        'lama_angsuran.required' => 'Lama angsuran harus diisi.',
        'lama_angsuran.integer' => 'Lama angsuran harus berupa angka bulat.',
        'lama_angsuran.min' => 'Lama angsuran minimal 1 bulan.',
        'lama_angsuran.max' => 'Lama angsuran maksimal 60 bulan.',
        'bunga.required' => 'Bunga harus diisi.',
        'bunga.numeric' => 'Bunga harus berupa angka.',
        'bunga.min' => 'Bunga minimal 0%.',
        'bunga.max' => 'Bunga maksimal 24%.',
        'jenis_pinjaman.required' => 'Jenis pinjaman harus dipilih.',
        'jenis_pinjaman.in' => 'Jenis pinjaman tidak valid.',
        'kas_id.required' => 'Sumber dana harus dipilih.',
        'kas_id.exists' => 'Sumber dana yang dipilih tidak ditemukan.',
        'keterangan.max' => 'Keterangan maksimal 500 karakter.'
    ];
}
```

---

## 🔐 **7.2 VALIDASI APPROVAL**

### **Business Rules untuk Approval**:
```php
/**
 * Validasi bisnis sebelum approval
 */
private function validateApproval($pengajuan)
{
    $errors = [];
    
    // 1. Cek status pengajuan
    if ($pengajuan->status !== 'Pending') {
        $errors[] = 'Pengajuan tidak dapat diapprove karena status bukan Pending.';
    }
    
    // 2. Cek kelengkapan dokumen
    if (!$this->cekKelengkapanDokumen($pengajuan)) {
        $errors[] = 'Dokumen belum lengkap untuk approval.';
    }
    
    // 3. Cek limit pinjaman
    $limitErrors = $this->validatePinjamanLimit($pengajuan);
    $errors = array_merge($errors, $limitErrors);
    
    // 4. Cek saldo kas
    if (!$this->validateSaldoKas($pengajuan)) {
        $errors[] = 'Saldo kas tidak mencukupi untuk approval.';
    }
    
    // 5. Cek approval level
    if (!$this->validateApprovalLevel($pengajuan)) {
        $errors[] = 'Tidak memiliki hak untuk approve pinjaman ini.';
    }
    
    // 6. Cek jadwal approval
    if (!$this->validateApprovalSchedule($pengajuan)) {
        $errors[] = 'Pengajuan tidak dapat diapprove pada waktu ini.';
    }
    
    if (!empty($errors)) {
        throw new \Exception(implode(' ', $errors));
    }
}

/**
 * Cek kelengkapan dokumen
 */
private function cekKelengkapanDokumen($pengajuan)
{
    $dokumenWajib = [
        'ktp' => true,
        'kk' => true,
        'slip_gaji' => $pengajuan->jenis_pinjaman == '1',
        'surat_kerja' => $pengajuan->jenis_pinjaman == '1',
        'jaminan' => $pengajuan->jenis_pinjaman == '2',
        'npwp' => $pengajuan->jumlah > 100000000 // NPWP wajib untuk pinjaman > 100 juta
    ];
    
    foreach ($dokumenWajib as $dokumen => $wajib) {
        if ($wajib && !$this->dokumenAda($pengajuan->id, $dokumen)) {
            return false;
        }
    }
    
    return true;
}

/**
 * Validasi limit pinjaman
 */
private function validatePinjamanLimit($pengajuan)
{
    $errors = [];
    $anggota = DataAnggota::find($pengajuan->anggota_id);
    
    if (!$anggota) {
        $errors[] = 'Data anggota tidak ditemukan.';
        return $errors;
    }
    
    // 1. Limit berdasarkan simpanan
    $totalSimpanan = $anggota->simpanan_pokok + $anggota->simpanan_wajib;
    $limitSimpanan = $totalSimpanan * 3;
    
    if ($pengajuan->jumlah > $limitSimpanan) {
        $errors[] = "Jumlah pinjaman melebihi limit berdasarkan simpanan (Rp " . number_format($limitSimpanan, 0, ',', '.') . ").";
    }
    
    // 2. Limit berdasarkan masa keanggotaan
    $masaKeanggotaan = Carbon::parse($anggota->tgl_bergabung)->diffInMonths(now());
    
    if ($masaKeanggotaan < 6) {
        $errors[] = 'Anggota harus bergabung minimal 6 bulan untuk dapat mengajukan pinjaman.';
    }
    
    if ($masaKeanggotaan < 12 && $pengajuan->jumlah > 10000000) {
        $errors[] = 'Anggota dengan masa keanggotaan kurang dari 1 tahun maksimal pinjam Rp 10 juta.';
    }
    
    // 3. Limit berdasarkan riwayat pinjaman
    $riwayatPinjaman = TblPinjamanH::where('anggota_id', $pengajuan->anggota_id)
        ->where('status', '1')
        ->where('lunas', 'Sudah')
        ->count();
    
    if ($riwayatPinjaman == 0 && $pengajuan->jumlah > 5000000) {
        $errors[] = 'Anggota baru maksimal pinjam Rp 5 juta untuk pinjaman pertama.';
    }
    
    return $errors;
}

/**
 * Validasi saldo kas
 */
private function validateSaldoKas($pengajuan)
{
    $kas = DataKas::find($pengajuan->kas_id);
    
    if (!$kas) {
        return false;
    }
    
    // Saldo kas harus mencukupi
    if ($kas->saldo < $pengajuan->jumlah) {
        return false;
    }
    
    // Saldo kas tidak boleh kurang dari 10% setelah pinjaman
    $saldoSetelahPinjaman = $kas->saldo - $pengajuan->jumlah;
    $saldoMinimum = $kas->saldo * 0.1;
    
    if ($saldoSetelahPinjaman < $saldoMinimum) {
        return false;
    }
    
    return true;
}

/**
 * Validasi approval level
 */
private function validateApprovalLevel($pengajuan)
{
    $user = auth()->user();
    $jumlah = $pengajuan->jumlah;
    
    // Approval level berdasarkan jumlah pinjaman
    if ($jumlah <= 10000000) { // <= 10 juta
        return $user->can('approve-pinjaman-kecil');
    } elseif ($jumlah <= 50000000) { // <= 50 juta
        return $user->can('approve-pinjaman-menengah');
    } else { // > 50 juta
        return $user->can('approve-pinjaman-besar');
    }
}

/**
 * Validasi jadwal approval
 */
private function validateApprovalSchedule($pengajuan)
{
    $now = now();
    
    // Approval hanya bisa dilakukan pada jam kerja (Senin-Jumat, 08:00-17:00)
    if ($now->isWeekend()) {
        return false;
    }
    
    $hour = $now->hour;
    if ($hour < 8 || $hour >= 17) {
        return false;
    }
    
    // Approval tidak bisa dilakukan pada hari libur nasional
    if ($this->isHariLibur($now)) {
        return false;
    }
    
    return true;
}
```

---

## 🚫 **7.3 VALIDASI PENOLAKAN**

### **Business Rules untuk Rejection**:
```php
/**
 * Validasi sebelum reject pengajuan
 */
private function validateRejection($pengajuan, $alasan)
{
    $errors = [];
    
    // 1. Cek status pengajuan
    if ($pengajuan->status !== 'Pending') {
        $errors[] = 'Pengajuan tidak dapat ditolak karena status bukan Pending.';
    }
    
    // 2. Validasi alasan penolakan
    if (empty($alasan) || strlen($alasan) < 10) {
        $errors[] = 'Alasan penolakan harus diisi minimal 10 karakter.';
    }
    
    // 3. Cek apakah pengajuan sudah pernah diapprove sebelumnya
    if ($pengajuan->approved_at) {
        $errors[] = 'Pengajuan yang sudah diapprove tidak dapat ditolak.';
    }
    
    // 4. Cek permission untuk reject
    if (!$this->canRejectPengajuan($pengajuan)) {
        $errors[] = 'Tidak memiliki hak untuk menolak pengajuan ini.';
    }
    
    if (!empty($errors)) {
        throw new \Exception(implode(' ', $errors));
    }
}

/**
 * Cek permission untuk reject
 */
private function canRejectPengajuan($pengajuan)
{
    $user = auth()->user();
    
    // User yang membuat pengajuan tidak bisa reject
    if ($user->id == $pengajuan->created_by) {
        return false;
    }
    
    // Hanya admin dan manager yang bisa reject
    return $user->hasRole(['admin', 'manager']);
}
```

---

## 🔄 **7.4 VALIDASI BILLING**

### **Business Rules untuk Billing**:
```php
/**
 * Validasi data billing sebelum insert/update
 */
private function validateBillingData($data)
{
    $errors = [];
    
    // 1. Validasi total tagihan
    $calculatedTotal = 
        ($data['tagihan_simpanan_wajib'] ?? 0) +
        ($data['tagihan_simpanan_sukarela'] ?? 0) +
        ($data['tagihan_simpanan_khusus_2'] ?? 0) +
        ($data['tagihan_pinjaman'] ?? 0) +
        ($data['tagihan_pinjaman_jasa'] ?? 0) +
        ($data['tagihan_toserda'] ?? 0);
    
    if (abs($calculatedTotal - ($data['total_tagihan'] ?? 0)) > 0.01) {
        $errors[] = "Total tagihan tidak sesuai dengan perhitungan (Expected: {$calculatedTotal}, Actual: {$data['total_tagihan']})";
    }
    
    // 2. Validasi tanggal transaksi
    if (!empty($data['tgl_transaksi'])) {
        $tglTransaksi = Carbon::parse($data['tgl_transaksi']);
        if (!$tglTransaksi->isEndOfMonth()) {
            $errors[] = "Tanggal transaksi harus akhir bulan (Current: {$tglTransaksi->format('Y-m-d')})";
        }
    }
    
    // 3. Validasi jumlah tagihan tidak boleh negatif
    $fields = ['tagihan_simpanan_wajib', 'tagihan_simpanan_sukarela', 'tagihan_simpanan_khusus_2', 'tagihan_pinjaman', 'tagihan_pinjaman_jasa', 'tagihan_toserda'];
    
    foreach ($fields as $field) {
        if (isset($data[$field]) && $data[$field] < 0) {
            $errors[] = "Field {$field} tidak boleh negatif";
        }
    }
    
    // 4. Validasi no_ktp harus valid
    if (!empty($data['no_ktp'])) {
        if (!preg_match('/^\d{16}$/', $data['no_ktp'])) {
            $errors[] = "Format nomor KTP tidak valid (harus 16 digit)";
        }
        
        // Cek apakah no_ktp ada di tabel anggota
        $anggota = DataAnggota::where('no_ktp', $data['no_ktp'])->first();
        if (!$anggota) {
            $errors[] = "Nomor KTP tidak ditemukan di data anggota";
        }
    }
    
    return $errors;
}
```

---

## 🚨 **7.5 ERROR HANDLING**

### **Comprehensive Error Handling**:
```php
/**
 * Handle error scenarios dengan logging
 */
private function handleError($error, $context = [])
{
    // Log error dengan context
    Log::error('Error in billing system', [
        'error' => $error->getMessage(),
        'file' => $error->getFile(),
        'line' => $error->getLine(),
        'trace' => $error->getTraceAsString(),
        'context' => $context,
        'user_id' => auth()->id(),
        'ip_address' => request()->ip(),
        'user_agent' => request()->userAgent()
    ]);
    
    // Return user-friendly error message
    if ($error instanceof ValidationException) {
        return response()->json([
            'success' => false,
            'message' => 'Validasi gagal',
            'errors' => $error->errors()
        ], 422);
    }
    
    if ($error instanceof \Exception) {
        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan sistem',
            'error' => config('app.debug') ? $error->getMessage() : 'Internal server error'
        ], 500);
    }
    
    return response()->json([
        'success' => false,
        'message' => 'Terjadi kesalahan yang tidak diketahui'
    ], 500);
}

/**
 * Try-catch wrapper untuk operasi database
 */
private function executeWithTransaction($callback, $errorMessage = 'Operasi gagal')
{
    try {
        DB::beginTransaction();
        
        $result = $callback();
        
        DB::commit();
        
        return $result;
        
    } catch (\Exception $e) {
        DB::rollback();
        
        $this->handleError($e, [
            'operation' => $errorMessage,
            'timestamp' => now()
        ]);
        
        throw $e;
    }
}
```

---

## 📊 **7.6 BUSINESS RULES ENGINE**

### **Rules Engine Configuration**:
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
    
    /**
     * Validate specific rule
     */
    private function validateRule($ruleName, $rule, $data)
    {
        switch ($ruleName) {
            case 'limit_pinjaman':
                return $this->validateLimitPinjaman($rule, $data);
                
            case 'min_masa_keanggotaan':
                return $this->validateMasaKeanggotaan($rule, $data);
                
            case 'max_bunga':
                return $this->validateMaxBunga($rule, $data);
                
            case 'min_angsuran':
                return $this->validateMinAngsuran($rule, $data);
                
            case 'max_angsuran':
                return $this->validateMaxAngsuran($rule, $data);
                
            default:
                return ['valid' => true, 'message' => ''];
        }
    }
}
```

---

## 🚀 **KESIMPULAN BAGIAN 7**

Bagian 7 ini telah mencakup secara lengkap:

✅ **Validasi Pengajuan** - Rules lengkap dengan custom validation
✅ **Validasi Approval** - Business rules untuk approval process
✅ **Validasi Penolakan** - Rules untuk rejection
✅ **Validasi Billing** - Data integrity checks
✅ **Error Handling** - Comprehensive error management
✅ **Business Rules Engine** - Configurable rules system

**Next Step**: Lanjut ke Bagian 8 untuk Monitoring & Reporting.

