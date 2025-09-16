# 📋 BAGIAN 1: PROSES PENGAJUAN PINJAMAN (APPLICATION FLOW)

## 🎯 **OVERVIEW PROSES PENGAJUAN**

Proses pengajuan pinjaman adalah tahap awal dalam sistem pinjaman terintegrasi. Sistem ini memungkinkan anggota untuk mengajukan pinjaman dengan berbagai jenis dan persyaratan yang telah ditentukan.

---

## 🖥️ **1.1 FORM DAN INTERFACE PENGAJUAN**

### **File View**: `resources/views/pinjaman/data_pengajuan.blade.php`

### **Field yang Tersedia**:
- `anggota_id` - ID anggota (required, exists:data_anggota,id)
- `tgl_pinjam` - Tanggal pinjam (required, date)
- `jumlah` - Jumlah pinjaman (required, numeric, min:1000)
- `lama_angsuran` - Durasi angsuran (required, integer, min:1, max:60)
- `bunga` - Persentase bunga (required, numeric, min:0, max:100)
- `jenis_pinjaman` - Jenis pinjaman (required, in:1,3)
- `kas_id` - Sumber dana (required, exists:data_kas,id)
- `keterangan` - Keterangan tambahan

### **Validation Rules**:
```php
$request->validate([
    'anggota_id' => 'required|exists:data_anggota,id',
    'tgl_pinjam' => 'required|date',
    'jumlah' => 'required|numeric|min:1000',
    'lama_angsuran' => 'required|integer|min:1|max:60',
    'bunga' => 'required|numeric|min:0|max:100',
    'jenis_pinjaman' => 'required|in:1,3',
    'kas_id' => 'required|exists:data_kas,id'
]);
```

---

## 🎮 **1.2 CONTROLLER DAN ROUTE PENGAJUAN**

### **Controller**: `app/Http/Controllers/DataPengajuanController.php`

### **Method yang Tersedia**:

#### **1. Method `index()` - Tampilan List Pengajuan**
```php
public function index()
{
    $pengajuan = DataPengajuan::with(['anggota', 'kas', 'createdBy', 'approvedBy'])
        ->orderBy('created_at', 'desc')
        ->paginate(15);
    
    return view('pinjaman.data_pengajuan.index', compact('pengajuan'));
}
```

#### **2. Method `create()` - Form Pengajuan Baru**
```php
public function create()
{
    $anggota = DataAnggota::where('aktif', 'Y')->orderBy('nama')->get();
    $kas = DataKas::where('aktif', 'Y')->orderBy('nama')->get();
    
    return view('pinjaman.data_pengajuan.create', compact('anggota', 'kas'));
}
```

#### **3. Method `store()` - Simpan Pengajuan**
```php
public function store(Request $request)
{
    try {
        // 1. Validasi input
        $validated = $request->validate([...]);
        
        // 2. Validasi bisnis tambahan
        $this->validateBusinessRules($validated);
        
        // 3. Buat pengajuan
        $pengajuan = new DataPengajuan();
        $pengajuan->fill($validated);
        $pengajuan->status = 'Pending';
        $pengajuan->created_by = auth()->id();
        $pengajuan->save();
        
        return redirect()->route('pinjaman.data_pengajuan.index')
            ->with('success', 'Pengajuan pinjaman berhasil dibuat');
            
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Terjadi kesalahan');
    }
}
```

#### **4. Method `approve()` - Persetujuan Admin**
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

### **Routes**:
```php
Route::resource('pinjaman/data_pengajuan', DataPengajuanController::class);
Route::post('/pinjaman/data_pengajuan/{id}/approve', [DataPengajuanController::class, 'approve']);
Route::post('/pinjaman/data_pengajuan/{id}/reject', [DataPengajuanController::class, 'reject']);
```

---

## 🧮 **1.3 SIMULASI DAN KALKULASI**

### **Formula Perhitungan**:
- **Angsuran Pokok** = Jumlah Pinjaman / Lama Angsuran
- **Bunga Bulanan** = (Angsuran Pokok × Bunga %) / 12
- **Total Angsuran** = Angsuran Pokok + Bunga Bulanan

### **JavaScript Function untuk Real-time Calculation**:
```javascript
class PinjamanSimulator {
    updateSimulasi() {
        const jumlah = parseFloat(document.getElementById('jumlah').value) || 0;
        const lamaAngsuran = parseInt(document.getElementById('lama_angsuran').value) || 0;
        const bunga = parseFloat(document.getElementById('bunga').value) || 0;
        
        if (jumlah > 0 && lamaAngsuran > 0) {
            const angsuranPokok = jumlah / lamaAngsuran;
            const bungaBulanan = (angsuranPokok * bunga / 100) / 12;
            const totalAngsuran = angsuranPokok + bungaBulanan;
            
            this.tampilkanSimulasi({
                angsuranPokok: angsuranPokok,
                bungaBulanan: bungaBulanan,
                totalAngsuran: totalAngsuran
            });
        }
    }
}
```

---

## 🔐 **1.4 VALIDASI DAN BUSINESS LOGIC**

### **Validasi Server-side**:
```php
private function validateBusinessRules($data)
{
    // 1. Cek pinjaman aktif
    $pinjamanAktif = TblPinjamanH::where('anggota_id', $data['anggota_id'])
        ->where('status', '1')
        ->where('lunas', 'Belum')
        ->count();
    
    if ($pinjamanAktif > 0) {
        throw ValidationException::withMessages([
            'anggota_id' => 'Anggota masih memiliki pinjaman aktif'
        ]);
    }
    
    // 2. Cek limit pinjaman
    $anggota = DataAnggota::find($data['anggota_id']);
    $limitPinjaman = ($anggota->simpanan_pokok + $anggota->simpanan_wajib) * 3;
    
    if ($data['jumlah'] > $limitPinjaman) {
        throw ValidationException::withMessages([
            'jumlah' => 'Jumlah pinjaman melebihi limit'
        ]);
    }
    
    // 3. Cek saldo kas
    $kas = DataKas::find($data['kas_id']);
    if ($kas->saldo < $data['jumlah']) {
        throw ValidationException::withMessages([
            'jumlah' => 'Saldo kas tidak mencukupi'
        ]);
    }
}
```

---

## 📊 **1.5 MONITORING DAN REPORTING**

### **Dashboard Statistics**:
```php
public function getDashboardStats()
{
    return [
        'total_pengajuan' => DataPengajuan::count(),
        'pengajuan_pending' => DataPengajuan::where('status', 'Pending')->count(),
        'pengajuan_approved' => DataPengajuan::where('status', 'Approved')->count(),
        'pengajuan_rejected' => DataPengajuan::where('status', 'Rejected')->count(),
        'total_nilai_pengajuan' => DataPengajuan::where('status', 'Pending')->sum('jumlah')
    ];
}
```

---

## 🚀 **KESIMPULAN BAGIAN 1**

Bagian 1 ini telah mencakup secara lengkap:

✅ **Form dan Interface** - Form lengkap dengan validasi
✅ **Controller dan Routes** - Semua method dengan error handling
✅ **Simulasi dan Kalkulasi** - JavaScript real-time calculation
✅ **Validasi Bisnis** - Server-side validation yang robust
✅ **Monitoring** - Dashboard stats dan reporting

**Next Step**: Lanjut ke Bagian 2 untuk proses Approval dan Aktivasi Pinjaman.

# 📊 BAGIAN 2: PROSES APPROVAL DAN AKTIVASI PINJAMAN

## 🎯 **OVERVIEW PROSES APPROVAL**

Proses approval adalah tahap kritis dimana admin/manager menyetujui atau menolak pengajuan pinjaman. Setelah approval, sistem akan otomatis mengaktifkan pinjaman dan generate jadwal angsuran bulanan.

---

## ✅ **2.1 APPROVAL WORKFLOW**

### **Method Approve Lengkap**:
```php
public function approve($id)
{
    try {
        DB::beginTransaction();
        
        // 1. Ambil data pengajuan
        $pengajuan = DataPengajuan::with(['anggota', 'kas'])->findOrFail($id);
        
        // 2. Validasi status
        if ($pengajuan->status !== 'Pending') {
            throw new \Exception('Pengajuan tidak dapat diapprove');
        }
        
        // 3. Validasi bisnis
        $this->validateApproval($pengajuan);
        
        // 4. Buat record pinjaman di tbl_pinjaman_h
        $pinjaman = $this->createPinjamanHeader($pengajuan);
        
        // 5. Generate jadwal angsuran
        $this->generateTempoPinjaman($pinjaman);
        
        // 6. Update status pengajuan
        $pengajuan->status = 'Approved';
        $pengajuan->approved_by = auth()->id();
        $pengajuan->save();
        
        // 7. Kurangi saldo kas
        $this->updateSaldoKas($pengajuan);
        
        DB::commit();
        
        return redirect()->back()->with('success', 'Pengajuan berhasil disetujui');
        
    } catch (\Exception $e) {
        DB::rollback();
        return redirect()->back()->with('error', 'Gagal menyetujui pengajuan');
    }
}
```

### **Validasi Approval**:
```php
private function validateApproval($pengajuan)
{
    // 1. Cek status pengajuan
    if ($pengajuan->status !== 'Pending') {
        throw new \Exception('Pengajuan tidak dapat diapprove');
    }
    
    // 2. Cek kelengkapan dokumen
    if (!$this->cekKelengkapanDokumen($pengajuan)) {
        throw new \Exception('Dokumen belum lengkap');
    }
    
    // 3. Cek saldo kas
    $kas = DataKas::find($pengajuan->kas_id);
    if ($kas->saldo < $pengajuan->jumlah) {
        throw new \Exception('Saldo kas tidak mencukupi');
    }
}
```

---

## ⚙️ **2.2 AKTIVASI PINJAMAN**

### **Generate Tempo Pinjaman**:
```php
private function generateTempoPinjaman($pinjaman)
{
    $jumlahAngsuran = $pinjaman->jumlah_angsuran;
    $tglPinjam = Carbon::parse($pinjaman->tgl_pinjam);
    $bunga = $pinjaman->bunga;
    
    for ($i = 1; $i <= $pinjaman->lama_angsuran; $i++) {
        // Hitung tanggal jatuh tempo
        $tglTempo = $tglPinjam->copy()->addMonths($i);
        
        // Hitung komponen angsuran
        $pokok = $jumlahAngsuran;
        $bungaBulanan = ($pokok * $bunga / 100) / 12;
        $totalAngsuran = $pokok + $bungaBulanan;
        
        DB::table('tempo_pinjaman')->insert([
            'pinjam_id' => $pinjaman->id,
            'no_ktp' => $pinjaman->anggota->no_ktp,
            'tgl_pinjam' => $pinjaman->tgl_pinjam,
            'tempo' => $tglTempo->toDateString(),
            'no_urut' => $i,
            'pokok' => $pokok,
            'bunga' => $bungaBulanan,
            'total_angsuran' => $totalAngsuran,
            'status_bayar' => 'Belum'
        ]);
    }
}
```

### **Create Pinjaman Header**:
```php
private function createPinjamanHeader($pengajuan)
{
    $pinjaman = new TblPinjamanH();
    $pinjaman->anggota_id = $pengajuan->anggota_id;
    $pinjaman->no_pinjaman = $this->generateNomorPinjaman();
    $pinjaman->tgl_pinjam = $pengajuan->tgl_pinjam;
    $pinjaman->jumlah = $pengajuan->jumlah;
    $pinjaman->lama_angsuran = $pengajuan->lama_angsuran;
    $pinjaman->jumlah_angsuran = $pengajuan->jumlah / $pengajuan->lama_angsuran;
    $pinjaman->bunga = $pengajuan->bunga;
    $pinjaman->jenis_pinjaman = $pengajuan->jenis_pinjaman;
    $pinjaman->kas_id = $pengajuan->kas_id;
    $pinjaman->status = '1'; // Aktif
    $pinjaman->lunas = 'Belum';
    $pinjaman->total_bayar = 0;
    $pinjaman->sisa_pokok = $pengajuan->jumlah;
    $pinjaman->save();
    
    return $pinjaman;
}
```

---

## 🔐 **2.3 APPROVAL LEVELS & PERMISSIONS**

### **Approval Level Berdasarkan Jumlah**:
```php
// File: config/pinjaman.php

return [
    'approval_levels' => [
        'kecil' => [
            'max_amount' => 10000000, // 10 juta
            'roles' => ['admin', 'supervisor']
        ],
        'menengah' => [
            'max_amount' => 50000000, // 50 juta
            'roles' => ['manager', 'admin']
        ],
        'besar' => [
            'max_amount' => 1000000000, // 1 milyar
            'roles' => ['director', 'manager']
        ]
    ]
];
```

---

## 📧 **2.4 NOTIFIKASI DAN LOGGING**

### **Send Approval Notification**:
```php
private function sendApprovalNotification($pengajuan)
{
    try {
        // Email notification
        if ($pengajuan->anggota->email) {
            Mail::to($pengajuan->anggota->email)->send(new PinjamanApprovedMail($pengajuan));
        }
        
        // SMS notification
        if ($pengajuan->anggota->no_hp) {
            $this->sendSMSNotification($pengajuan);
        }
        
    } catch (\Exception $e) {
        Log::error('Gagal kirim notifikasi: ' . $e->getMessage());
    }
}
```

---

## 🚫 **2.5 REJECTION PROCESS**

### **Method Reject**:
```php
public function reject(Request $request, $id)
{
    $request->validate([
        'alasan_penolakan' => 'required|string|max:500'
    ]);
    
    try {
        $pengajuan = DataPengajuan::findOrFail($id);
        
        if ($pengajuan->status !== 'Pending') {
            return redirect()->back()->with('error', 'Pengajuan tidak dapat ditolak');
        }
        
        $pengajuan->status = 'Rejected';
        $pengajuan->keterangan = $request->alasan_penolakan;
        $pengajuan->rejected_by = auth()->id();
        $pengajuan->save();
        
        return redirect()->back()->with('success', 'Pengajuan berhasil ditolak');
        
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Gagal menolak pengajuan');
    }
}
```

---

## 🚀 **KESIMPULAN BAGIAN 2**

Bagian 2 ini telah mencakup secara lengkap:

✅ **Approval Workflow** - Proses approval yang robust
✅ **Aktivasi Pinjaman** - Generate header dan jadwal angsuran
✅ **Approval Levels** - Permission berdasarkan jumlah pinjaman
✅ **Notifikasi** - Email, SMS, dan in-app notification
✅ **Rejection Process** - Proses penolakan yang terstruktur

**Next Step**: Lanjut ke Bagian 3 untuk Database Operations & Structure.

