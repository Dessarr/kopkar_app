# Dokumentasi Relasi Database Transaksi Kas

## Struktur Table dan Relasi

### 1. Table `tbl_trans_kas` (Primary Table)
```sql
CREATE TABLE tbl_trans_kas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tgl_catat DATETIME,
    jumlah DECIMAL(15,2),
    keterangan TEXT,
    akun VARCHAR(50), -- 'Pemasukan', 'Pengeluaran', 'Transfer'
    dari_kas_id INT, -- FK ke nama_kas_tbl
    untuk_kas_id INT, -- FK ke nama_kas_tbl (NULL untuk pengeluaran)
    jns_trans INT, -- FK ke jenis_akun_tbl
    dk CHAR(1), -- 'D' = Debit, 'K' = Kredit
    no_polisi VARCHAR(20),
    update_data DATETIME,
    id_cabang INT,
    user_name VARCHAR(100)
);
```

### 2. Table `nama_kas_tbl` (Lookup Table)
```sql
CREATE TABLE nama_kas_tbl (
    id INT PRIMARY KEY,
    nama VARCHAR(100), -- 'Kas Besar', 'Kas Kecil', 'Kas Tunai', dll
    aktif CHAR(1), -- 'Y' = Aktif, 'N' = Tidak Aktif
    tmpl_pemasukan CHAR(1), -- 'Y' = Bisa untuk pemasukan
    tmpl_pengeluaran CHAR(1), -- 'Y' = Bisa untuk pengeluaran
    tmpl_transfer CHAR(1) -- 'Y' = Bisa untuk transfer
);
```

### 3. Table `jenis_akun_tbl` (Lookup Table)
```sql
CREATE TABLE jenis_akun_tbl (
    id INT PRIMARY KEY,
    jns_trans VARCHAR(100), -- 'Penjualan', 'Biaya Operasional', dll
    kd_aktiva VARCHAR(10),
    pemasukan CHAR(1), -- 'Y' = Bisa untuk pemasukan
    pengeluaran CHAR(1) -- 'Y' = Bisa untuk pengeluaran
);
```

## Relasi Eloquent Models

### 1. Model `transaksi_kas`
```php
class transaksi_kas extends Model
{
    protected $table = 'tbl_trans_kas';
    
    // Relasi ke nama_kas_tbl
    public function dariKas()
    {
        return $this->belongsTo(NamaKasTbl::class, 'dari_kas_id', 'id');
    }
    
    public function untukKas()
    {
        return $this->belongsTo(NamaKasTbl::class, 'untuk_kas_id', 'id');
    }
    
    // Relasi ke jenis_akun_tbl
    public function jenisAkun()
    {
        return $this->belongsTo(jns_akun::class, 'jns_trans', 'id');
    }
}
```

### 2. Model `View_Transaksi`
```php
class View_Transaksi extends View_Base
{
    protected $table = 'v_transaksi';
    
    // Relasi ke nama_kas_tbl
    public function kasAsal()
    {
        return $this->belongsTo(NamaKasTbl::class, 'dari_kas', 'id');
    }
    
    public function kasTujuan()
    {
        return $this->belongsTo(NamaKasTbl::class, 'untuk_kas', 'id');
    }
    
    // Relasi ke jenis_akun_tbl
    public function jenisAkun()
    {
        return $this->belongsTo(jns_akun::class, 'jns_trans', 'id');
    }
}
```

## Mapping Data di View

### Kolom Table Pemasukan Kas
| Kolom View | Field Database | Relasi | Keterangan |
|------------|----------------|--------|------------|
| **No** | - | - | Nomor urut |
| **Kode Transaksi** | `id` | - | Format: TKD + sprintf('%05d', id) |
| **Tanggal Transaksi** | `tgl` | - | Format: d/m/Y H:i |
| **Keterangan** | `keterangan` | - | Deskripsi transaksi |
| **Untuk Kas** | `untuk_kas_id` | `kasTujuan->nama` | Nama kas tujuan |
| **Dari Akun** | `jns_trans` | `jenisAkun->jns_trans` | Jenis akun sumber |
| **Jumlah** | `debet` | - | Nominal dengan format Rupiah |
| **User** | `user` | - | Nama user yang input |

### Contoh Data Mapping
```php
// Data yang ditampilkan di view:
$kas->id = 600
$kas->kode = 'TKD00600' // Format: TKD + str_pad(600, 5, '0', STR_PAD_LEFT)
$kas->tgl = '2025-09-30 00:00:00'
$kas->keterangan = 'Penjualan barang'
$kas->kasTujuan->nama = 'Kas Tunai' // dari nama_kas_tbl
$kas->jenisAkun->jns_trans = 'Penjualan' // dari jenis_akun_tbl
$kas->debet = 1000000
$kas->user = 'admin'
```

## Query dengan JOIN

### Query untuk Pemasukan Kas
```sql
SELECT 
    t.id,
    t.tgl_catat as tgl,
    t.keterangan,
    t.debet,
    t.user,
    uk.nama as untuk_kas_nama,
    ja.jns_trans as jenis_akun_nama
FROM v_transaksi t
LEFT JOIN nama_kas_tbl uk ON t.untuk_kas = uk.id
LEFT JOIN jenis_akun_tbl ja ON t.jns_trans = ja.id
WHERE t.transaksi = '48' -- 48 = Pemasukan
ORDER BY t.tgl DESC;
```

## Kode dan ID Mapping

### ID Kas (nama_kas_tbl)
| ID | Nama Kas | Keterangan |
|----|----------|------------|
| 1 | Kas Besar | Kas utama perusahaan |
| 2 | Kas Kecil | Kas untuk operasional harian |
| 3 | Kas Tunai | Kas tunai |
| 4 | Kas Bank | Rekening bank |

### ID Jenis Akun (jenis_akun_tbl)
| ID | Jenis Akun | Keterangan |
|----|-------------|------------|
| 55 | Biaya Operasional | Biaya operasional harian |
| 57 | Biaya Administrasi | Biaya administrasi |
| 58 | Biaya Marketing | Biaya pemasaran |
| 62 | Biaya Transportasi | Biaya transportasi |
| 69 | Biaya Lain-lain | Biaya lainnya |
| 68 | Biaya Maintenance | Biaya perawatan |
| 70 | Penjualan | Pendapatan penjualan |
| 71 | Pendapatan Service | Pendapatan jasa |

## Field `dk` (Debit/Kredit)
- **"D"** = Debit (untuk pemasukan)
- **"K"** = Kredit (untuk pengeluaran)

## Field `akun` (Kategori Transaksi)
- **"Pemasukan"** = Uang masuk ke kas
- **"Pengeluaran"** = Uang keluar dari kas
- **"Transfer"** = Transfer antar kas

## Implementasi di Controller

```php
public function pemasukan(Request $request)
{
    $query = View_Transaksi::where('transaksi', '48')
        ->with(['kasTujuan', 'jenisAkun']); // Eager loading relasi
    
    $dataKas = $query->orderBy('tgl', 'desc')->paginate(15);
    
    return view('transaksi_kas.pemasukan', compact('dataKas'));
}
```

## Implementasi di View

```blade
@foreach($dataKas as $kas)
<tr>
    <td>{{ $kas->id }}</td>
    <td>TKD{{ str_pad($kas->id, 5, '0', STR_PAD_LEFT) }}</td>
    <td>{{ $kas->tgl->format('d/m/Y H:i') }}</td>
    <td>{{ $kas->keterangan }}</td>
    <td>{{ $kas->kasTujuan->nama ?? '-' }}</td>
    <td>{{ $kas->jenisAkun->jns_trans ?? '-' }}</td>
    <td>Rp{{ number_format($kas->debet, 0, ',', '.') }}</td>
    <td>{{ $kas->user }}</td>
</tr>
@endforeach
```

## Summary

Relasi database transaksi kas sudah lengkap dengan:
- ✅ **3 relasi utama**: `dariKas`, `untukKas`, `jenisAkun`
- ✅ **Eager loading** untuk performa optimal
- ✅ **Fallback values** untuk data yang kosong
- ✅ **Format data** yang konsisten
- ✅ **Mapping ID** ke nama yang readable
