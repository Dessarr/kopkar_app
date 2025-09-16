# IMPLEMENTASI RINGKASAN TAGIHAN DASHBOARD MEMBER

## **OVERVIEW**
Implementasi ini menerapkan logika data ringkasan tagihan berdasarkan analisis project CI lama ke dalam Laravel project. Data yang ditampilkan meliputi 5 baris utama: Jumlah, Tag Bulan Lalu, Pot Gaji, Pot Simpanan, dan Tag Harus Dibayar.

## **PERUBAHAN YANG DILAKUKAN**

### **1. Controller: `app/Http/Controllers/MemberController.php`**

#### **Method Baru yang Ditambahkan:**

**A. `getJmlSimpans($noKtp)` - Data "Jumlah" (Row 1)**
```php
private function getJmlSimpans($noKtp)
{
    $id = [40, 32, 41, 52]; // Pokok, Sukarela, Wajib, Khusus2
    
    return DB::table('tbl_trans_tagihan')
        ->selectRaw('SUM(jumlah) as jml_total')
        ->whereIn('jenis_id', $id)
        ->where('no_ktp', $noKtp)
        ->whereYear('tgl_transaksi', date('Y'))
        ->whereMonth('tgl_transaksi', date('m'))
        ->first();
}
```
- **Asal Tabel**: `tbl_trans_tagihan`
- **Kolom**: `jumlah`
- **Filter**: `jenis_id IN(40,32,41,52)` + `no_ktp` + `YEAR/MONTH = current`

**B. `getTagihanBulanLaluNew($noKtp)` - Data "Tag Bulan Lalu" (Row 2)**
```php
private function getTagihanBulanLaluNew($noKtp)
{
    // Hitung bulan lalu
    $bulanLalu = date('m') - 1;
    $tahunLalu = date('Y');
    
    if ($bulanLalu == 0) {
        $bulanLalu = 12;
        $tahunLalu = date('Y') - 1;
    }
    
    // Tagihan bulan lalu (jenis_id = 8)
    $bulanTak = DB::table('tbl_trans_tagihan')
        ->selectRaw('SUM(jumlah) as jml_total')
        ->where('no_ktp', $noKtp)
        ->where('jenis_id', 8)
        ->whereYear('tgl_transaksi', $tahunLalu)
        ->whereMonth('tgl_transaksi', $bulanLalu)
        ->first();
        
    // Pembayaran bulan lalu
    $bulanLaluBayar = DB::table('tbl_trans_sp')
        ->selectRaw('SUM(jumlah) as jml_total')
        ->where('no_ktp', $noKtp)
        ->where('jenis_id', 8)
        ->whereYear('tgl_transaksi', $tahunLalu)
        ->whereMonth('tgl_transaksi', $bulanLalu)
        ->first();
    
    return ($bulanTak->jml_total ?? 0) - ($bulanLaluBayar->jml_total ?? 0);
}
```
- **Asal Tabel**: `tbl_trans_tagihan` - `tbl_trans_sp`
- **Kolom**: `jumlah`
- **Filter**: `jenis_id = 8` + `no_ktp` + `YEAR/MONTH = bulan_lalu`

**C. `getBayarSimpanan($noKtp)` - Data "Pot Gaji" (Row 3)**
```php
private function getBayarSimpanan($noKtp)
{
    // Simpanan (jenis_id NOT IN(155,8,125) AND dk='D')
    $sim = DB::table('tbl_trans_sp')
        ->selectRaw('SUM(jumlah) as jml_total')
        ->where('no_ktp', $noKtp)
        ->whereNotIn('jenis_id', [155, 8, 125])
        ->where('dk', 'D')
        ->whereYear('tgl_transaksi', date('Y'))
        ->whereMonth('tgl_transaksi', date('m'))
        ->first();
        
    // Pinjaman - perlu join dengan tbl_pinjaman_h untuk mendapatkan no_ktp
    $pin = DB::table('tbl_pinjaman_d as pd')
        ->join('tbl_pinjaman_h as ph', 'pd.pinjam_id', '=', 'ph.id')
        ->selectRaw('SUM(pd.jumlah_bayar) as jml_total')
        ->where('ph.no_ktp', $noKtp)
        ->whereYear('pd.tgl_bayar', date('Y'))
        ->whereMonth('pd.tgl_bayar', date('m'))
        ->first();
        
    // Toserda - menggunakan tabel yang benar
    $tos = DB::table('tbl_trans_toserda')
        ->selectRaw('SUM(jumlah) as jml_total')
        ->where('no_ktp', $noKtp)
        ->where('dk', 'D')
        ->whereYear('tgl_transaksi', date('Y'))
        ->whereMonth('tgl_transaksi', date('m'))
        ->first();
    
    return ($sim->jml_total ?? 0) + ($pin->jml_total ?? 0) + ($tos->jml_total ?? 0);
}
```
- **Asal Tabel**: `tbl_trans_sp` + `tbl_pinjaman_d` + `tbl_trans_toserda`
- **Kolom**: `jumlah` + `jumlah_bayar`
- **Filter**: `jenis_id NOT IN(155,8,125)` + `dk='D'` + `YEAR/MONTH = current`

**D. `getBayarSimpananPot($noKtp)` - Data "Pot Simpanan" (Row 4)**
```php
private function getBayarSimpananPot($noKtp)
{
    return DB::table('tbl_trans_sp')
        ->selectRaw('SUM(jumlah) as jml_total')
        ->where('no_ktp', $noKtp)
        ->whereNotIn('jenis_id', [8, 125])
        ->where('jumlah', '<', 0) // Nilai negatif
        ->whereYear('tgl_transaksi', date('Y'))
        ->whereMonth('tgl_transaksi', date('m'))
        ->first();
}
```
- **Asal Tabel**: `tbl_trans_sp`
- **Kolom**: `jumlah`
- **Filter**: `jenis_id NOT IN(8,125)` + `jumlah < 0` + `YEAR/MONTH = current`

#### **Update Method `memberDashboard()`:**
```php
// 9. Data ringkasan tagihan berdasarkan logika project CI lama
$jmlSimpans = $this->getJmlSimpans($anggota->no_ktp);
$tagihanBulanLaluNew = $this->getTagihanBulanLaluNew($anggota->no_ktp);
$potGaji = $this->getBayarSimpanan($anggota->no_ktp);
$potSimpanan = $this->getBayarSimpananPot($anggota->no_ktp);

// 10. Hitung tag harus dibayar
$tagHarusDibayar = ($jmlSimpans->jml_total ?? 0) - $potGaji;
```

### **2. View: `resources/views/member/dashboard.blade.php`**

#### **Update Bagian Ringkasan Tagihan:**
```php
<div class="space-y-1">
    <div class="flex justify-between items-center">
        <span>Jumlah:</span>
        <span class="font-bold">{{ number_format($jmlSimpans->jml_total ?? 0, 0, ',', '.') }}</span>
    </div>
    <div class="flex justify-between items-center">
        <span>Tag Bulan Lalu:</span>
        <span class="font-bold">{{ number_format($tagihanBulanLaluNew, 0, ',', '.') }}</span>
    </div>
    <div class="flex justify-between items-center">
        <span class="font-bold">Pot Gaji:</span>
        <span class="font-bold">{{ number_format($potGaji, 0, ',', '.') }}</span>
    </div>
    <div class="flex justify-between items-center">
        <span class="font-bold">Pot Simpanan:</span>
        <span class="font-bold">{{ number_format($potSimpanan->jml_total ?? 0, 0, ',', '.') }}</span>
    </div>
    <div class="flex justify-between items-center">
        <span>Tag Harus Dibayar:</span>
        <span class="font-bold">{{ number_format($tagHarusDibayar, 0, ',', '.') }}</span>
    </div>
</div>
```

## **STRUKTUR DATA LENGKAP PER ROW**

| **No** | **Label** | **Nominal** | **Sumber Data** | **Tabel Asal** | **Kolom Data** | **Filter** |
|--------|-----------|-------------|-----------------|----------------|----------------|------------|
| **1** | **Jumlah** | `$jmlSimpans->jml_total` | `getJmlSimpans()` | `tbl_trans_tagihan` | `SUM(jumlah)` | `jenis_id IN(40,32,41,52)` + `YEAR/MONTH = current` |
| **2** | **Tag Bulan Lalu** | `$tagihanBulanLaluNew` | `getTagihanBulanLaluNew()` | `tbl_trans_tagihan` - `tbl_trans_sp` | `SUM(jumlah)` | `jenis_id=8` + `YEAR/MONTH = bulan_lalu` |
| **3** | **Pot Gaji** | `$potGaji` | `getBayarSimpanan()` | `tbl_trans_sp` + `tbl_pinjaman_d` + `tbl_trans_toserda` | `SUM(jumlah)` + `SUM(jumlah_bayar)` | `jenis_id NOT IN(155,8,125)` + `dk='D'` + `YEAR/MONTH = current` |
| **4** | **Pot Simpanan** | `$potSimpanan->jml_total` | `getBayarSimpananPot()` | `tbl_trans_sp` | `SUM(jumlah)` | `jenis_id NOT IN(8,125)` + `jumlah < 0` + `YEAR/MONTH = current` |
| **5** | **Tag Harus Dibayar** | `$tagHarusDibayar` | `(Jumlah) - (Pot Gaji)` | Hasil perhitungan | `(Row 1) - (Row 3)` | - |

## **CARA MENGUBAH NOMINAL**

### **A. Mengubah "Jumlah" (Row 1):**
```sql
-- Input tagihan simpanan baru
INSERT INTO tbl_trans_tagihan (tgl_transaksi, no_ktp, jenis_id, jumlah, keterangan, akun, dk, kas_id, user_name)
VALUES ('2025-01-15', '1234567890123456', 40, 50000, 'Simpanan Pokok Januari', 'Tagihan', 'D', '1', 'admin');
```

### **B. Mengubah "Tag Bulan Lalu" (Row 2):**
```sql
-- Input tagihan bulan lalu
INSERT INTO tbl_trans_tagihan (tgl_transaksi, no_ktp, jenis_id, jumlah, keterangan, akun, dk, kas_id, user_name)
VALUES ('2025-01-15', '1234567890123456', 8, 25000, 'Tagihan Bulan Lalu', 'Tagihan', 'D', '1', 'admin');
```

### **C. Mengubah "Pot Gaji" (Row 3):**
```sql
-- Input pembayaran simpanan
INSERT INTO tbl_trans_sp (tgl_transaksi, no_ktp, jenis_id, jumlah, dk, user_name)
VALUES ('2025-01-15', '1234567890123456', 40, 50000, 'D', 'admin');
```

### **D. Mengubah "Pot Simpanan" (Row 4):**
```sql
-- Input potongan simpanan (nilai negatif)
INSERT INTO tbl_trans_sp (tgl_transaksi, no_ktp, jenis_id, jumlah, dk, user_name)
VALUES ('2025-01-15', '1234567890123456', 40, -10000, 'K', 'admin');
```

## **KEUNTUNGAN IMPLEMENTASI INI**

1. **Konsistensi Data**: Menggunakan logika yang sama dengan project CI lama
2. **Akurasi Perhitungan**: Data dihitung berdasarkan filter yang tepat
3. **Maintainability**: Kode terstruktur dan mudah dipahami
4. **Performance**: Query yang efisien dengan filter yang tepat
5. **Scalability**: Mudah untuk menambah jenis data baru

## **CATATAN PENTING**

- **Periode Data**: Semua data menggunakan periode bulan dan tahun saat ini
- **Filter Utama**: `no_ktp` untuk memfilter data per anggota
- **Jenis ID**: Menggunakan ID yang sama dengan project CI lama
- **Debit/Kredit**: Menggunakan `dk` untuk membedakan pemasukan dan pengeluaran
- **Nilai Negatif**: Pot Simpanan menggunakan nilai negatif untuk menunjukkan potongan

## **TESTING**

Untuk menguji implementasi ini:
1. Login sebagai member
2. Akses dashboard member
3. Periksa data di panel "Tagihan Simpanan"
4. Pastikan data "Jumlah", "Tag Bulan Lalu", "Pot Gaji", "Pot Simpanan", dan "Tag Harus Dibayar" tampil dengan benar
5. Input data baru melalui modul yang sesuai dan lihat perubahan di dashboard
