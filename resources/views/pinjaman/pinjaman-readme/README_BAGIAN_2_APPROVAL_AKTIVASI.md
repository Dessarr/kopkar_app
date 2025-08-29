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
