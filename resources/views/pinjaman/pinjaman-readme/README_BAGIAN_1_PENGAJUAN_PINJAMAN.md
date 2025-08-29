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
- `jenis_pinjaman` - Jenis pinjaman (required, in:1,2)
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
    'jenis_pinjaman' => 'required|in:1,2',
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
