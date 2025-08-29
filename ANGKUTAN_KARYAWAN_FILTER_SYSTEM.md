# SISTEM FILTER ANGKUTAN KARYAWAN

## Ringkasan

Sistem filter Angkutan Karyawan telah diperbarui untuk menggunakan tabel `tbl_trans_kas` dengan sistem filter yang konsisten dengan modul Transaksi Kas dan Toserda. Sistem ini mendukung fitur "tambah pemasukan" dan "tambah pengeluaran" dengan layout yang modern dan responsif.

## Struktur Database

### Tabel Utama: `tbl_trans_kas`

Sistem menggunakan tabel `tbl_trans_kas` dengan field berikut:

-   `id` - Primary key
-   `tgl_catat` - Tanggal transaksi
-   `keterangan` - Uraian transaksi
-   `jumlah` - Jumlah transaksi
-   `jns_trans` - Jenis transaksi (ID)
-   `akun` - Akun transaksi
-   `dari_kas_id` - ID kas asal
-   `untuk_kas_id` - ID kas tujuan
-   `dk` - Debit/Kredit (D/K)
-   `user_name` - Nama user
-   `update_data` - Timestamp update
-   `id_cabang` - ID cabang

### Mapping Jenis Transaksi

#### Pemasukan Angkutan

-   **jns_trans**: `46`
-   **Akun**: Pendapatan Jasa Sewa Bus
-   **dk**: `D` (Debit)

#### Pengeluaran Angkutan

-   **jns_trans**: `55` - `69`
-   **Akun**: Berbagai jenis beban operasional
-   **dk**: `K` (Kredit)

**Detail Jenis Pengeluaran:**

-   `55` - Beban Bahan Bakar
-   `56` - Beban Servis
-   `57` - Beban Parkir
-   `58` - Beban Tol
-   `59` - Beban Gaji Supir
-   `60` - Beban Gaji Kernet
-   `61` - Beban Asuransi
-   `62` - Beban Pajak
-   `63` - Beban Administrasi
-   `64` - Beban Lain-lain
-   `65` - Beban Perbaikan
-   `66` - Beban P3K
-   `67` - Beban Cuci
-   `68` - Beban Ban
-   `69` - Beban Oli

## Fitur Utama

### 1. Halaman Pemasukan Angkutan

-   **URL**: `/angkutan/pemasukan`
-   **Controller**: `AngkutanController@pemasukan`
-   **View**: `resources/views/angkutan/pemasukan.blade.php`

**Fitur:**

-   Filter berdasarkan tanggal (start_date, end_date)
-   Pencarian berdasarkan kode transaksi atau uraian
-   Filter berdasarkan kas
-   Statistik total pemasukan dan jumlah transaksi
-   Tabel data dengan pagination
-   Modal form untuk tambah pemasukan
-   Export PDF dan Excel

### 2. Halaman Pengeluaran Angkutan

-   **URL**: `/angkutan/pengeluaran`
-   **Controller**: `AngkutanController@pengeluaran`
-   **View**: `resources/views/angkutan/pengeluaran.blade.php`

**Fitur:**

-   Filter berdasarkan tanggal (start_date, end_date)
-   Pencarian berdasarkan kode transaksi atau uraian
-   Filter berdasarkan kas
-   Statistik total pengeluaran dan jumlah transaksi
-   Tabel data dengan pagination
-   Modal form untuk tambah pengeluaran dengan dropdown jenis pengeluaran
-   Export PDF dan Excel

## Struktur Tabel

### Header Tabel

1. **Kode Transaksi** - Format: `TKD{id dengan padding 6 digit}`
2. **Tanggal Transaksi** - Format: `d F Y - H:i`
3. **Uraian** - Keterangan transaksi
4. **Dari Kas/Untuk Kas** - Nama kas (sesuai jenis transaksi)
5. **Akun** - Nama akun yang dipetakan dari jns_trans
6. **Jumlah** - Format currency Indonesia
7. **User** - Nama user yang melakukan transaksi
8. **Aksi** - Tombol Edit dan Hapus

## Controller Methods

### AngkutanController

#### 1. pemasukan(Request $request)

-   Menampilkan halaman pemasukan dengan filter
-   Query: `jns_trans = '46' AND dk = 'D'`
-   Menghitung statistik total pemasukan dan jumlah transaksi

#### 2. pengeluaran(Request $request)

-   Menampilkan halaman pengeluaran dengan filter
-   Query: `jns_trans IN ('55','56','57','58','59','60','61','62','63','64','65','66','67','68','69') AND dk = 'K'`
-   Menghitung statistik total pengeluaran dan jumlah transaksi

#### 3. applyFilters($query, $startDate, $endDate, $search, $kasFilter)

-   Method helper untuk menerapkan filter pada query
-   Filter tanggal, pencarian, dan kas

#### 4. storePemasukan(Request $request)

-   Menyimpan data pemasukan baru
-   Validasi input
-   Set `jns_trans = '46'` dan `dk = 'D'`

#### 5. storePengeluaran(Request $request)

-   Menyimpan data pengeluaran baru
-   Validasi input termasuk jns_trans
-   Set `dk = 'K'`

#### 6. Export Methods

-   `exportPdfPemasukan()` - Export PDF pemasukan
-   `exportPdfPengeluaran()` - Export PDF pengeluaran
-   `exportExcelPemasukan()` - Export Excel pemasukan
-   `exportExcelPengeluaran()` - Export Excel pengeluaran

## Routes

```php
// Angkutan Routes
Route::prefix('angkutan')->group(function () {
    Route::get('/pemasukan', [AngkutanController::class, 'pemasukan'])->name('angkutan.pemasukan');
    Route::get('/pengeluaran', [AngkutanController::class, 'pengeluaran'])->name('angkutan.pengeluaran');
    Route::post('/pemasukan', [AngkutanController::class, 'storePemasukan'])->name('angkutan.store.pemasukan');
    Route::post('/pengeluaran', [AngkutanController::class, 'storePengeluaran'])->name('angkutan.store.pengeluaran');
    Route::get('/export/pdf/pemasukan', [AngkutanController::class, 'exportPdfPemasukan'])->name('angkutan.export.pdf.pemasukan');
    Route::get('/export/pdf/pengeluaran', [AngkutanController::class, 'exportPdfPengeluaran'])->name('angkutan.export.pdf.pengeluaran');
    Route::get('/export/excel/pemasukan', [AngkutanController::class, 'exportExcelPemasukan'])->name('angkutan.export.excel.pemasukan');
    Route::get('/export/excel/pengeluaran', [AngkutanController::class, 'exportExcelPengeluaran'])->name('angkutan.export.excel.pengeluaran');
});
```

## View Components

### 1. Statistics Cards

-   Total Pemasukan/Pengeluaran dengan format currency
-   Total Transaksi
-   Periode filter yang aktif

### 2. Filter Form

-   Tanggal mulai dan akhir
-   Pencarian (kode transaksi, uraian)
-   Filter kas
-   Tombol Filter dan Reset

### 3. Action Buttons

-   Tambah Pemasukan/Pengeluaran (modal)
-   Export PDF
-   Export Excel

### 4. Data Table

-   Responsive table dengan hover effects
-   Pagination
-   Empty state handling
-   Action buttons (Edit/Hapus)

### 5. Modal Form

-   Form untuk tambah transaksi
-   Validasi client-side
-   Auto-set datetime to current time
-   Dropdown untuk jenis pengeluaran (pengeluaran only)

## PDF Templates

### 1. pemasukan.blade.php

-   Header dengan judul dan periode
-   Tabel data dengan styling
-   Total pemasukan di footer
-   Timestamp cetak

### 2. pengeluaran.blade.php

-   Header dengan judul dan periode
-   Tabel data dengan styling
-   Mapping akun untuk jenis pengeluaran
-   Total pengeluaran di footer
-   Timestamp cetak

## JavaScript Functions

### Modal Management

-   `openModal()` - Membuka modal form
-   `closeModal()` - Menutup modal form
-   Event listener untuk close modal saat klik outside

### Form Handling

-   Auto-set datetime to current time
-   Form reset saat modal dibuka
-   Dynamic form action URL

### Action Functions

-   `editTransaksi(id)` - Placeholder untuk edit (perlu implementasi)
-   `deleteTransaksi(id)` - Placeholder untuk delete (perlu implementasi)

## Styling

### Color Scheme

-   Primary: `#14AE5C` (green)
-   Success: Green variants
-   Danger: Red variants
-   Neutral: Gray variants

### Components

-   Tailwind CSS classes
-   Responsive design
-   Modern card-based layout
-   Consistent spacing and typography

## Dependencies

### Models

-   `transaksi_kas` - Model utama untuk transaksi
-   `NamaKasTbl` - Model untuk data kas
-   `data_mobil` - Model untuk data mobil (tersedia tapi tidak digunakan dalam view baru)

### Packages

-   DomPDF untuk export PDF
-   PhpSpreadsheet untuk export Excel
-   Carbon untuk date handling

## Catatan Implementasi

1. **Sistem menggunakan tabel `tbl_trans_kas`** untuk konsistensi dengan modul lain
2. **Mapping jns_trans** mengikuti analisis sistem lama yang diberikan
3. **Layout konsisten** dengan modul Transaksi Kas dan Toserda
4. **Filter system** sama dengan modul lainnya
5. **Export functionality** tersedia untuk PDF dan Excel
6. **Modal forms** untuk UX yang lebih baik
7. **Responsive design** untuk berbagai ukuran layar

## TODO

1. Implementasi fungsi Edit transaksi
2. Implementasi fungsi Delete transaksi
3. Validasi tambahan jika diperlukan
4. Testing pada berbagai browser
5. Optimasi query untuk performa yang lebih baik
