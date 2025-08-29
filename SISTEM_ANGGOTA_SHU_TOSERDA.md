# SISTEM ANGGOTA SHU DAN TOSERDA

## 📋 **OVERVIEW**

Sistem ini mengelola transaksi SHU (Sisa Hasil Usaha) dan TOSERDA (Toko Serba Ada) untuk anggota koperasi. Kedua modul ini terintegrasi dalam menu "Anggota" di sidebar dan menggunakan layout yang konsisten dengan modul lainnya.

## 🗄️ **STRUKTUR DATABASE**

### **Tabel Utama**

#### **1. Tabel SHU: `tbl_shu`**
```sql
- id (Primary Key, Auto Increment)
- tgl_transaksi (DateTime) - Tanggal transaksi SHU
- no_ktp (String) - Nomor KTP anggota
- jumlah_bayar (Decimal) - Jumlah SHU yang dibayarkan
- dk (String) - Debit/Kredit (selalu 'K' untuk SHU)
- kas_id (Integer) - ID kas (selalu 1)
- update_data (DateTime) - Tanggal update
- user_name (String) - Nama user (selalu 'admin')
- keterangan (Text, Nullable) - Keterangan tambahan
```

#### **2. Tabel TOSERDA: `tbl_trans_sp`**
```sql
- id (Primary Key, Auto Increment)
- tgl_transaksi (DateTime) - Tanggal transaksi
- no_ktp (String) - Nomor KTP anggota
- anggota_id (Integer) - ID anggota
- jenis_id (Integer) - ID jenis transaksi (154=Lain-lain, 155=Toserda)
- jumlah (Decimal) - Jumlah transaksi
- keterangan (Text, Nullable) - Keterangan transaksi
- dk (String) - Debit/Kredit (selalu 'D' untuk TOSERDA)
- kas_id (Integer) - ID kas (selalu 1)
- update_data (DateTime) - Tanggal update
- user_name (String) - Nama user (selalu 'admin')
```

#### **3. Tabel Pendukung**
- **`tbl_anggota`** - Data anggota koperasi
- **`nama_kas_tbl`** - Master data kas/bank
- **`jns_akun`** - Master jenis transaksi TOSERDA
- **`v_shu`** - View untuk data SHU (join dengan tbl_anggota)
- **`v_toserda`** - View untuk data TOSERDA (join dengan tbl_anggota)

## 🎯 **FITUR UTAMA**

### **A. MODUL SHU (Sisa Hasil Usaha)**

#### **1. Halaman Utama SHU** (`/anggota/shu`)
- **Statistics Cards:**
  - Total SHU (jumlah total pembayaran SHU)
  - Total Transaksi (jumlah transaksi SHU)
  - Periode (rentang tanggal filter)

- **Filter System:**
  - Filter berdasarkan tanggal (start_date, end_date)
  - Pencarian berdasarkan nama atau No KTP anggota
  - Filter button untuk menerapkan filter

- **Action Buttons:**
  - **Tambah SHU** - Modal form untuk menambah data SHU
  - **Export PDF** - Export laporan SHU ke PDF
  - **Export Excel** - Export laporan SHU ke Excel
  - **Import Excel** - Import data SHU dari file Excel

- **Data Table:**
  - Kode Transaksi (TRD + 5 digit ID)
  - Tanggal Transaksi (format Indonesia)
  - ID Anggota (AG + 4 digit ID)
  - Nama Anggota
  - No KTP
  - Jumlah SHU (format number dengan separator)
  - User
  - Aksi (Cetak, Edit, Hapus)

#### **2. Modal Form Tambah SHU**
- Tanggal Transaksi (datetime-local, default current time)
- Anggota (dropdown dari tbl_anggota)
- Jumlah SHU (number, step 0.01, min 0)
- Keterangan (textarea, optional)

#### **3. Fitur CRUD**
- **Create:** `POST /anggota/shu/store`
- **Read:** `GET /anggota/shu` (dengan filter)
- **Update:** `GET /anggota/shu/edit/{id}` dan `PUT /anggota/shu/update/{id}`
- **Delete:** `DELETE /anggota/shu/delete/{id}`

#### **4. Export & Import**
- **PDF Export:** `GET /anggota/shu/export/pdf`
- **Excel Export:** `GET /anggota/shu/export/excel`
- **Excel Import:** `POST /anggota/shu/import`

#### **5. Cetak Bukti**
- **Cetak Bukti SHU:** `GET /anggota/shu/cetak/{id}`

### **B. MODUL TOSERDA (Toko Serba Ada)**

#### **1. Halaman Utama TOSERDA** (`/anggota/toserda`)
- **Statistics Cards:**
  - Total TOSERDA (jumlah total transaksi TOSERDA)
  - Total Transaksi (jumlah transaksi TOSERDA)
  - Periode (rentang tanggal filter)

- **Filter System:**
  - Filter berdasarkan tanggal (start_date, end_date)
  - Pencarian berdasarkan nama atau No KTP anggota
  - Filter button untuk menerapkan filter

- **Action Buttons:**
  - **Tambah TOSERDA** - Modal form untuk menambah data TOSERDA
  - **Export PDF** - Export laporan TOSERDA ke PDF
  - **Export Excel** - Export laporan TOSERDA ke Excel
  - **Import Excel** - Import data TOSERDA dari file Excel

- **Data Table:**
  - Kode Transaksi (TRD + 5 digit ID)
  - Tanggal Transaksi (format Indonesia)
  - ID Anggota (AG + 4 digit ID)
  - Nama Anggota
  - No KTP
  - Jenis Transaksi (badge: Lain-lain/Toserda/Lainnya)
  - Jumlah (format number dengan separator)
  - User
  - Aksi (Cetak, Edit, Hapus)

#### **2. Modal Form Tambah TOSERDA**
- Tanggal Transaksi (datetime-local, default current time)
- Anggota (dropdown dari tbl_anggota)
- Jenis Transaksi (dropdown dari jns_akun: 154=Lain-lain, 155=Toserda)
- Jumlah (number, step 0.01, min 0)
- Keterangan (textarea, optional)

#### **3. Fitur CRUD**
- **Create:** `POST /anggota/toserda/store`
- **Read:** `GET /anggota/toserda` (dengan filter)
- **Update:** `GET /anggota/toserda/edit/{id}` dan `PUT /anggota/toserda/update/{id}`
- **Delete:** `DELETE /anggota/toserda/delete/{id}`

#### **4. Export & Import**
- **PDF Export:** `GET /anggota/toserda/export/pdf`
- **Excel Export:** `GET /anggota/toserda/export/excel`
- **Excel Import:** `POST /anggota/toserda/import`

#### **5. Cetak Bukti**
- **Cetak Bukti TOSERDA:** `GET /anggota/toserda/cetak/{id}`

## 🔧 **IMPLEMENTASI TEKNIS**

### **Controller Methods**

#### **AnggotaController.php**

```php
// SHU Methods
public function shu(Request $request) // Halaman utama SHU
public function storeShu(Request $request) // Simpan data SHU
public function editShu($id) // Edit form SHU
public function updateShu(Request $request, $id) // Update data SHU
public function deleteShu($id) // Hapus data SHU
public function exportPdfShu(Request $request) // Export PDF SHU
public function exportExcelShu(Request $request) // Export Excel SHU
public function importShu(Request $request) // Import Excel SHU
public function cetakShu($id) // Cetak bukti SHU

// TOSERDA Methods
public function toserda(Request $request) // Halaman utama TOSERDA
public function storeToserda(Request $request) // Simpan data TOSERDA
public function editToserda($id) // Edit form TOSERDA
public function updateToserda(Request $request, $id) // Update data TOSERDA
public function deleteToserda($id) // Hapus data TOSERDA
public function exportPdfToserda(Request $request) // Export PDF TOSERDA
public function exportExcelToserda(Request $request) // Export Excel TOSERDA
public function importToserda(Request $request) // Import Excel TOSERDA
public function cetakToserda($id) // Cetak bukti TOSERDA
```

### **Routes**

```php
// SHU Routes
Route::get('/anggota/shu', [AnggotaController::class, 'shu'])->name('anggota.shu');
Route::post('/anggota/shu', [AnggotaController::class, 'storeShu'])->name('anggota.shu.store');
Route::get('/anggota/shu/edit/{id}', [AnggotaController::class, 'editShu'])->name('anggota.shu.edit');
Route::put('/anggota/shu/update/{id}', [AnggotaController::class, 'updateShu'])->name('anggota.shu.update');
Route::delete('/anggota/shu/delete/{id}', [AnggotaController::class, 'deleteShu'])->name('anggota.shu.delete');
Route::get('/anggota/shu/export/pdf', [AnggotaController::class, 'exportPdfShu'])->name('anggota.shu.export.pdf');
Route::get('/anggota/shu/export/excel', [AnggotaController::class, 'exportExcelShu'])->name('anggota.shu.export.excel');
Route::post('/anggota/shu/import', [AnggotaController::class, 'importShu'])->name('anggota.shu.import');
Route::get('/anggota/shu/cetak/{id}', [AnggotaController::class, 'cetakShu'])->name('anggota.shu.cetak');

// TOSERDA Routes
Route::get('/anggota/toserda', [AnggotaController::class, 'toserda'])->name('anggota.toserda');
Route::post('/anggota/toserda', [AnggotaController::class, 'storeToserda'])->name('anggota.toserda.store');
Route::get('/anggota/toserda/edit/{id}', [AnggotaController::class, 'editToserda'])->name('anggota.toserda.edit');
Route::put('/anggota/toserda/update/{id}', [AnggotaController::class, 'updateToserda'])->name('anggota.toserda.update');
Route::delete('/anggota/toserda/delete/{id}', [AnggotaController::class, 'deleteToserda'])->name('anggota.toserda.delete');
Route::get('/anggota/toserda/export/pdf', [AnggotaController::class, 'exportPdfToserda'])->name('anggota.toserda.export.pdf');
Route::get('/anggota/toserda/export/excel', [AnggotaController::class, 'exportExcelToserda'])->name('anggota.toserda.export.excel');
Route::post('/anggota/toserda/import', [AnggotaController::class, 'importToserda'])->name('anggota.toserda.import');
Route::get('/anggota/toserda/cetak/{id}', [AnggotaController::class, 'cetakToserda'])->name('anggota.toserda.cetak');
```

### **Views**

#### **Main Views**
- `resources/views/anggota/shu.blade.php` - Halaman utama SHU
- `resources/views/anggota/toserda.blade.php` - Halaman utama TOSERDA

#### **PDF Templates**
- `resources/views/anggota/pdf/shu.blade.php` - Template PDF laporan SHU
- `resources/views/anggota/pdf/toserda.blade.php` - Template PDF laporan TOSERDA
- `resources/views/anggota/pdf/cetak_shu.blade.php` - Template PDF bukti SHU
- `resources/views/anggota/pdf/cetak_toserda.blade.php` - Template PDF bukti TOSERDA

### **Models**

#### **TblShu.php**
```php
protected $fillable = [
    'tgl_transaksi', 'no_ktp', 'jumlah_bayar', 'dk', 'kas_id', 
    'update_data', 'user_name', 'keterangan'
];

protected $casts = [
    'tgl_transaksi' => 'datetime',
    'update_data' => 'datetime',
    'jumlah_bayar' => 'decimal:2'
];

public function anggota()
{
    return $this->belongsTo(data_anggota::class, 'no_ktp', 'no_ktp');
}
```

#### **TblTransSp.php**
```php
protected $fillable = [
    'tgl_transaksi', 'no_ktp', 'anggota_id', 'jenis_id', 'jumlah',
    'keterangan', 'dk', 'kas_id', 'update_data', 'user_name'
];

protected $casts = [
    'tgl_transaksi' => 'datetime',
    'update_data' => 'datetime',
    'jumlah' => 'decimal:2'
];

public function anggota()
{
    return $this->belongsTo(data_anggota::class, 'no_ktp', 'no_ktp');
}

public function jenisTransaksi()
{
    return $this->belongsTo(jns_akun::class, 'jenis_id', 'id');
}
```

## 🎨 **UI/UX FEATURES**

### **Design System**
- **Color Scheme:** Konsisten dengan tema hijau (#14AE5C)
- **Typography:** Arial, sans-serif untuk PDF; Tailwind CSS untuk web
- **Layout:** Responsive grid system dengan Tailwind CSS
- **Components:** Cards, tables, modals, buttons, forms

### **Interactive Elements**
- **Modal Forms:** Untuk tambah/edit data
- **Filter System:** Date range dan search functionality
- **Action Buttons:** Export, import, cetak, edit, delete
- **Pagination:** Untuk data yang banyak
- **Hover Effects:** Pada table rows dan buttons

### **JavaScript Functions**
```javascript
// Modal Control
function openModal() // Buka modal tambah/edit
function closeModal() // Tutup modal
function openImportModal() // Buka modal import
function closeImportModal() // Tutup modal import

// CRUD Operations
function editShu(id) // Redirect ke halaman edit SHU
function deleteShu(id) // Konfirmasi dan hapus SHU
function editToserda(id) // Redirect ke halaman edit TOSERDA
function deleteToserda(id) // Konfirmasi dan hapus TOSERDA

// Utility
// Set default datetime to current time
// Close modal when clicking outside
```

## 📊 **EXPORT FORMATS**

### **PDF Export**
- **Laporan SHU:** Tabel dengan data SHU, total, dan periode
- **Laporan TOSERDA:** Tabel dengan data TOSERDA, total, dan periode
- **Bukti SHU:** Format bukti pembayaran dengan signature section
- **Bukti TOSERDA:** Format bukti transaksi dengan signature section

### **Excel Export**
- **Format SHU:** Kolom A (Tanggal), B (No KTP), C (Jumlah)
- **Format TOSERDA:** Kolom A (Tanggal), B (No KTP), C (Jenis ID), D (Jumlah), E (Keterangan)

## 🔒 **SECURITY & VALIDATION**

### **Middleware**
- `auth:admin` - Semua route admin dilindungi
- `auth:member` - Route member untuk bayar toserda

### **Validation Rules**
```php
// SHU Validation
'tgl_transaksi' => 'required|date',
'no_ktp' => 'required|exists:tbl_anggota,no_ktp',
'jumlah_bayar' => 'required|numeric|min:0',

// TOSERDA Validation
'tgl_transaksi' => 'required|date',
'no_ktp' => 'required|exists:tbl_anggota,no_ktp',
'jenis_id' => 'required|in:154,155',
'jumlah' => 'required|numeric|min:0',
```

## 🚀 **PERFORMANCE OPTIMIZATION**

### **Database Optimization**
- **Indexing:** Pada kolom yang sering digunakan untuk filter
- **Eager Loading:** Relasi anggota dan jenis transaksi
- **Pagination:** Untuk data yang banyak
- **Query Optimization:** Menggunakan where clauses yang efisien

### **Caching Strategy**
- **View Caching:** Template PDF dan HTML
- **Query Caching:** Untuk data yang jarang berubah
- **Session Caching:** Filter parameters

## 📝 **TODO & ENHANCEMENTS**

### **Immediate TODOs**
- [ ] Implementasi halaman edit SHU dan TOSERDA
- [ ] Implementasi fungsi delete dengan soft delete
- [ ] Validasi file import Excel
- [ ] Error handling untuk import/export
- [ ] Unit testing untuk controller methods

### **Future Enhancements**
- [ ] Dashboard analytics untuk SHU dan TOSERDA
- [ ] Notifikasi real-time untuk transaksi baru
- [ ] Multi-language support
- [ ] Advanced reporting dengan charts
- [ ] Mobile responsive optimization
- [ ] API endpoints untuk mobile app

### **Technical Debt**
- [ ] Refactor duplicate code in controller
- [ ] Implement repository pattern
- [ ] Add comprehensive logging
- [ ] Performance monitoring
- [ ] Security audit

## 🔧 **DEPENDENCIES**

### **Laravel Packages**
- `barryvdh/laravel-dompdf` - PDF generation
- `maatwebsite/excel` - Excel import/export
- `carbon/carbon` - Date/time manipulation

### **Frontend Libraries**
- `Tailwind CSS` - Styling framework
- `Alpine.js` - JavaScript framework (dalam sidebar)
- `Vanilla JavaScript` - Modal control dan form handling

## 📚 **REFERENCES**

- **Laravel Documentation:** https://laravel.com/docs
- **Tailwind CSS:** https://tailwindcss.com/docs
- **DomPDF:** https://github.com/barryvdh/laravel-dompdf
- **Laravel Excel:** https://docs.laravel-excel.com/

---

**Last Updated:** {{ now()->format('d F Y H:i:s') }}
**Version:** 1.0.0
**Author:** AI Assistant
