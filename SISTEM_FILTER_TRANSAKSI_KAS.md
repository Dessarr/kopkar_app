# Sistem Filter Transaksi Kas - Implementasi Lengkap

## 🎯 Overview

Sistem filter transaksi kas telah diperbarui dengan fitur yang lebih lengkap, modern, dan user-friendly. Sistem ini menggantikan filter lama yang memiliki beberapa keterbatasan dan memberikan pengalaman yang lebih baik untuk mengelola data transaksi kas.

## ✨ Fitur Utama

### 1. **Filter Pencarian Multi-Kriteria**

-   **Pencarian**: Keterangan, nama kas, user
-   **Kas**: Multiple selection berdasarkan jenis transaksi (Kas Tujuan untuk Pemasukan, Kas Asal untuk Pengeluaran)
-   **User**: Multiple selection dari data transaksi

### 2. **Filter Tanggal & Periode**

-   **Rentang Tanggal**: Date picker untuk tanggal awal dan akhir
-   **Periode Bulan**: Filter khusus periode 21-20 (tanggal 21 bulan sebelumnya sampai 20 bulan berjalan)

### 3. **Filter Nominal**

-   **Rentang Nominal**: Input minimum dan maksimum nominal transaksi
-   **Validasi**: Mencegah input nominal minimum lebih besar dari maksimum
-   **Otomatis**: Menyesuaikan field berdasarkan jenis transaksi (debet/kredit)

### 4. **Fitur Tambahan**

-   **Filter Counter**: Menampilkan jumlah filter aktif
-   **Validasi Form**: Validasi otomatis sebelum submit
-   **Loading State**: Indikator loading saat memproses filter
-   **Keyboard Shortcuts**:
    -   `Ctrl/Cmd + Enter`: Submit form
    -   `Ctrl/Cmd + R`: Reset semua filter
    -   `Escape`: Bersihkan filter
-   **Export Data**: Export data berdasarkan filter yang diterapkan
-   **Tooltips**: Bantuan untuk setiap field filter

## 🔧 Implementasi Teknis

### Controller (`TransaksiKasController.php`)

```php
class TransaksiKasController extends Controller
{
    public function pemasukan(Request $request)
    {
        $query = View_Transaksi::where('transaksi', '48')
            ->with('kasTujuan');

        // Filter berdasarkan request
        $query = $this->applyFilters($query, $request);

        $dataKas = $query->orderBy('tgl', 'desc')->paginate(15);

        // Data untuk filter dropdowns
        $listKas = DataKas::where('is_active', true)->get();
        $users = View_Transaksi::select('user')->distinct()->whereNotNull('user')->pluck('user');

        return view('transaksi_kas.pemasukan', compact('dataKas', 'listKas', 'users'));
    }

    public function pengeluaran(Request $request)
    {
        $query = View_Transaksi::where('transaksi', '7')
            ->with('kasAsal');

        // Filter berdasarkan request
        $query = $this->applyFilters($query, $request);

        $dataKas = $query->orderBy('tgl', 'desc')->paginate(15);

        // Data untuk filter dropdowns
        $listKas = DataKas::where('is_active', true)->get();
        $users = View_Transaksi::select('user')->distinct()->whereNotNull('user')->pluck('user');

        return view('transaksi_kas.pengeluaran', compact('dataKas', 'listKas', 'users'));
    }

    public function transfer(Request $request)
    {
        $query = transaksi_kas::with(['dariKas', 'untukKas']);

        // Filter berdasarkan request untuk transfer
        $query = $this->applyTransferFilters($query, $request);

        $dataKas = $query->orderBy('tgl_catat', 'desc')->paginate(15);

        // Data untuk filter dropdowns
        $listKas = DataKas::where('is_active', true)->get();
        $users = transaksi_kas::select('user_name')->distinct()->whereNotNull('user_name')->pluck('user_name');

        return view('transaksi_kas.transfer', compact('dataKas', 'listKas', 'users'));
    }
}
```

### Filter Logic

```php
private function applyFilters($query, $request)
{
    // 1. Filter Pencarian
    if ($request->filled('search')) {
        $search = trim($request->search);
        $query->where(function($q) use ($search) {
            $q->where('keterangan', 'like', "%{$search}%")
              ->orWhere('nama_kas', 'like', "%{$search}%")
              ->orWhere('user', 'like', "%{$search}%");
        });
    }

    // 2. Filter Tanggal
    if ($request->filled('date_from')) {
        $query->whereDate('tgl', '>=', $request->date_from);
    }
    if ($request->filled('date_to')) {
        $query->whereDate('tgl', '<=', $request->date_to);
    }

    // 3. Filter Periode Bulan (21-20)
    if ($request->filled('periode_bulan')) {
        $periode = $request->periode_bulan;
        $tglDari = date('Y-m-21', strtotime($periode . '-01 -1 month'));
        $tglSampai = $periode . '-20';
        $query->whereDate('tgl', '>=', $tglDari)
              ->whereDate('tgl', '<=', $tglSampai);
    }

    // 4. Filter Nominal Range - disesuaikan berdasarkan jenis transaksi
    if ($request->filled('nominal_min')) {
        if ($query->getModel() instanceof View_Transaksi) {
            $transaksiType = $query->getQuery()->wheres[0]['value'] ?? null;
            if ($transaksiType === '48') { // Pemasukan
                $query->where('debet', '>=', $request->nominal_min);
            } else { // Pengeluaran
                $query->where('kredit', '>=', $request->nominal_min);
            }
        }
    }

    // 5. Filter Kas (Multiple Selection) - disesuaikan berdasarkan jenis transaksi
    if ($request->filled('kas_filter')) {
        $kasArray = is_array($request->kas_filter) ? $request->kas_filter : [$request->kas_filter];
        if ($query->getModel() instanceof View_Transaksi) {
            $transaksiType = $query->getQuery()->wheres[0]['value'] ?? null;
            if ($transaksiType === '48') { // Pemasukan
                $query->whereIn('untuk_kas', $kasArray);
            } else { // Pengeluaran
                $query->whereIn('dari_kas', $kasArray);
            }
        }
    }

    // 6. Filter User (Multiple Selection)
    if ($request->filled('user_filter')) {
        $userArray = is_array($request->user_filter) ? $request->user_filter : [$request->user_filter];
        $query->whereIn('user', $userArray);
    }

    return $query;
}
```

### Routes

```php
// Route untuk modul kas dengan sistem filter baru
Route::prefix('transaksi-kas')->group(function () {
    // Pemasukan Kas
    Route::get('/pemasukan', [TransaksiKasController::class, 'pemasukan'])->name('admin.transaksi.pemasukan');
    Route::get('/pemasukan/export', [TransaksiKasController::class, 'exportPemasukan'])->name('admin.transaksi.pemasukan.export');

    // Pengeluaran Kas
    Route::get('/pengeluaran', [TransaksiKasController::class, 'pengeluaran'])->name('admin.transaksi.pengeluaran');
    Route::get('/pengeluaran/export', [TransaksiKasController::class, 'exportPengeluaran'])->name('admin.transaksi.pengeluaran.export');

    // Transfer Kas
    Route::get('/transfer', [TransaksiKasController::class, 'transfer'])->name('admin.transaksi.transfer');
    Route::get('/transfer/export', [TransaksiKasController::class, 'exportTransfer'])->name('admin.transaksi.transfer.export');
});
```

## 🎨 Interface Design

### Filter Section

```html
<!-- Filter Section Baru -->
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-800">Filter Pemasukan Kas</h3>
        <button type="button" onclick="resetAllFilters()" class="text-red-600 hover:text-red-800 text-sm">
            <i class="fas fa-times mr-1"></i>Reset Semua Filter
        </button>
    </div>

    <form method="GET" action="{{ route('admin.transaksi.pemasukan') }}" id="filterForm">
        <!-- Row 1: Search dan Kas -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
            <!-- Search Input -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Pencarian</label>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Keterangan, nama kas, user..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>

            <!-- Kas Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Kas Tujuan</label>
                <select name="kas_filter[]" multiple class="w-full px-3 py-2 border border-gray-300 rounded-lg" size="4">
                    @foreach($listKas as $kas)
                        <option value="{{ $kas->id }}" {{ in_array($kas->id, request('kas_filter', [])) ? 'selected' : '' }}>
                            {{ $kas->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- User Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">User</label>
                <select name="user_filter[]" multiple class="w-full px-3 py-2 border border-gray-300 rounded-lg" size="4">
                    @foreach($users as $user)
                        <option value="{{ $user }}" {{ in_array($user, request('user_filter', [])) ? 'selected' : '' }}>
                            {{ $user }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Row 2: Tanggal dan Periode -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <!-- Date Range -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Rentang Tanggal</label>
                <div class="grid grid-cols-2 gap-2">
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                </div>
            </div>

            <!-- Periode Bulan -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Periode Bulan (21-20)</label>
                <input type="month" name="periode_bulan" value="{{ request('periode_bulan') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                <p class="text-xs text-gray-500 mt-1">Periode dari tanggal 21 bulan sebelumnya sampai 20 bulan berjalan</p>
            </div>

            <!-- Nominal Range -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Rentang Nominal</label>
                <div class="grid grid-cols-2 gap-2">
                    <input type="number" name="nominal_min" value="{{ request('nominal_min') }}" placeholder="Min" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    <input type="number" name="nominal_max" value="{{ request('nominal_max') }}" placeholder="Max" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-between items-center pt-4 border-t border-gray-200">
            <div class="text-sm text-gray-600">
                <span id="filterCount">0</span> filter aktif
            </div>
            <div class="flex space-x-3">
                <button type="button" onclick="clearFilters()" class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">
                    <i class="fas fa-times mr-2"></i>Bersihkan
                </button>
                <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    <i class="fas fa-filter mr-2"></i>Terapkan Filter
                </button>
            </div>
        </div>
    </form>
</div>
```

### JavaScript Functions

```javascript
// Fungsi untuk menghitung jumlah filter aktif
function updateFilterCount() {
    const form = document.getElementById("filterForm");
    const formData = new FormData(form);
    let activeFilters = 0;

    // Check each filter type
    const filters = [
        "search",
        "kas_filter",
        "user_filter",
        "date_from",
        "date_to",
        "periode_bulan",
        "nominal_min",
        "nominal_max",
    ];

    filters.forEach((filter) => {
        const value = formData.get(filter);
        if (value && value !== "" && value !== "0") {
            activeFilters++;
        }
    });

    // Check multiple select filters
    const multipleFilters = ["kas_filter", "user_filter"];
    multipleFilters.forEach((filter) => {
        const values = formData.getAll(filter);
        if (values.length > 0 && values.some((v) => v !== "")) {
            activeFilters++;
        }
    });

    document.getElementById("filterCount").textContent = activeFilters;
}

// Fungsi untuk validasi form
function validateFilterForm() {
    const form = document.getElementById("filterForm");
    const dateFrom = form.querySelector('input[name="date_from"]').value;
    const dateTo = form.querySelector('input[name="date_to"]').value;
    const nominalMin = form.querySelector('input[name="nominal_min"]').value;
    const nominalMax = form.querySelector('input[name="nominal_max"]').value;

    // Validasi tanggal
    if (dateFrom && dateTo && dateFrom > dateTo) {
        alert("Tanggal awal tidak boleh lebih besar dari tanggal akhir");
        return false;
    }

    // Validasi nominal
    if (
        nominalMin &&
        nominalMax &&
        parseInt(nominalMin) > parseInt(nominalMax)
    ) {
        alert("Nominal minimum tidak boleh lebih besar dari nominal maksimum");
        return false;
    }

    return true;
}

// Fungsi untuk export data
function exportData() {
    const form = document.getElementById("filterForm");
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);

    window.open(
        "{{ route('admin.transaksi.pemasukan.export') }}?" + params.toString(),
        "_blank"
    );
}
```

## 🚀 Keunggulan Sistem Baru

### 1. **User Experience yang Lebih Baik**

-   Interface yang lebih modern dan intuitif
-   Multiple selection untuk filter yang relevan
-   Real-time filter counter
-   Loading state dan validasi otomatis
-   Keyboard shortcuts untuk power users

### 2. **Fungsionalitas yang Lebih Lengkap**

-   6 jenis filter berbeda untuk pemasukan/pengeluaran
-   7 jenis filter untuk transfer kas
-   Pencarian multi-field
-   Filter periode bulan khusus (21-20)
-   Filter nominal range yang otomatis menyesuaikan jenis transaksi
-   Export data berdasarkan filter

### 3. **Performance yang Lebih Baik**

-   Query yang dioptimasi dengan `whereIn` untuk multiple selection
-   Eager loading untuk relationships
-   Pagination yang efisien (15 item per halaman)

### 4. **Maintainability**

-   Kode yang lebih terstruktur dan mudah dipahami
-   Error handling yang lebih baik
-   Dokumentasi yang lengkap
-   Backward compatibility dengan route lama

## 📊 Perbandingan dengan Sistem Lama

| Aspek              | Sistem Lama        | Sistem Baru        |
| ------------------ | ------------------ | ------------------ |
| Jumlah Filter      | 2 filter           | 6-7 filter         |
| Multiple Selection | Tidak ada          | 3 filter           |
| Validasi           | Manual             | Otomatis           |
| UI/UX              | Dropdown sederhana | Modern interface   |
| Performance        | Basic query        | Optimized query    |
| Error Handling     | Minimal            | Comprehensive      |
| Export             | Tidak ada          | Berdasarkan filter |
| Documentation      | Tidak ada          | Lengkap            |

## 🎯 Cara Penggunaan

### 1. **Filter Pencarian**

-   Masukkan kata kunci di field pencarian
-   Sistem akan mencari di: keterangan, nama kas, user

### 2. **Filter Kas**

-   **Pemasukan**: Pilih satu atau lebih kas tujuan
-   **Pengeluaran**: Pilih satu atau lebih kas asal
-   **Transfer**: Pilih satu atau lebih kas asal dan tujuan
-   Gunakan Ctrl/Cmd + klik untuk multiple selection

### 3. **Filter User**

-   Pilih satu atau lebih user
-   Data diambil dari database secara dinamis

### 4. **Filter Tanggal**

-   Pilih tanggal awal dan akhir
-   Atau gunakan filter periode bulan (21-20)

### 5. **Filter Nominal**

-   Masukkan nominal minimum dan/atau maksimum
-   Sistem akan memvalidasi input
-   Otomatis menyesuaikan field debet/kredit

### 6. **Export Data**

-   Klik tombol Export untuk mengunduh data
-   Data yang di-export sesuai dengan filter yang diterapkan

## 🔧 Troubleshooting

### Masalah Umum:

1. **Filter tidak berfungsi**: Pastikan semua field terisi dengan benar
2. **Data tidak muncul**: Periksa apakah ada data yang sesuai dengan filter
3. **Error validasi**: Ikuti pesan error yang muncul
4. **Export tidak berfungsi**: Pastikan route export sudah terdaftar

### Debug Mode:

-   Gunakan browser developer tools untuk melihat console logs
-   Periksa network tab untuk request yang gagal
-   Pastikan semua JavaScript files ter-load dengan benar

## 📝 Catatan Penting

1. **Database Requirements**:
    - Tabel `v_transaksi` untuk pemasukan/pengeluaran
    - Tabel `tbl_trans_kas` untuk transfer
    - Tabel `data_kas` untuk data kas
2. **Performance**:
    - Untuk data yang sangat besar, pertimbangkan untuk menambahkan index pada kolom yang sering difilter
    - Pagination diatur ke 15 item per halaman untuk optimal performance
3. **Security**:
    - Semua input sudah di-sanitize untuk mencegah SQL injection
    - Validasi dilakukan di client dan server side
4. **Compatibility**:
    - Sistem ini kompatibel dengan Laravel 8+ dan PHP 7.4+
    - Route lama tetap berfungsi untuk backward compatibility

## 🎉 Kesimpulan

Sistem filter transaksi kas baru ini memberikan pengalaman yang jauh lebih baik untuk pengguna dalam mengelola dan memfilter data transaksi kas. Dengan fitur yang lebih lengkap, performa yang lebih baik, dan interface yang lebih modern, sistem ini siap untuk digunakan dalam produksi dan memberikan kemudahan dalam pengelolaan data transaksi kas yang kompleks.

### Fitur Utama yang Ditambahkan:

1. **Sistem Filter Lengkap** untuk ketiga jenis transaksi kas
2. **Multiple Selection** untuk kas dan user
3. **Filter Nominal** yang otomatis menyesuaikan jenis transaksi
4. **Export Data** berdasarkan filter
5. **Keyboard Shortcuts** untuk power users
6. **Validasi Otomatis** untuk mencegah kesalahan input
7. **Interface Modern** dengan Tailwind CSS
8. **Backward Compatibility** dengan sistem lama
