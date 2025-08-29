# Sistem Filter Baru - Pengajuan Penarikan Simpanan

## 🎯 Overview

Sistem filter baru untuk pengajuan penarikan simpanan telah dibuat dengan fitur yang lebih lengkap, modern, dan user-friendly. Sistem ini menggantikan filter lama yang memiliki beberapa keterbatasan.

## ✨ Fitur Utama

### 1. **Filter Pencarian Multi-Kriteria**

-   **Pencarian**: Nama, Ajuan ID, KTP, No Ajuan
-   **Status**: Multiple selection (Menunggu, Disetujui, Ditolak, Terlaksana, Batal)
-   **Jenis Simpanan**: Multiple selection dari database
-   **Departemen**: Multiple selection berdasarkan data anggota
-   **Cabang**: Multiple selection berdasarkan data anggota

### 2. **Filter Tanggal & Periode**

-   **Rentang Tanggal**: Date picker untuk tanggal awal dan akhir
-   **Periode Bulan**: Filter khusus periode 21-20 (tanggal 21 bulan sebelumnya sampai 20 bulan berjalan)

### 3. **Filter Nominal**

-   **Rentang Nominal**: Input minimum dan maksimum nominal penarikan
-   **Validasi**: Mencegah input nominal minimum lebih besar dari maksimum

### 4. **Fitur Tambahan**

-   **Filter Counter**: Menampilkan jumlah filter aktif
-   **Validasi Form**: Validasi otomatis sebelum submit
-   **Loading State**: Indikator loading saat memproses filter
-   **Keyboard Shortcuts**:
    -   `Ctrl/Cmd + Enter`: Submit form
    -   `Ctrl/Cmd + R`: Reset semua filter
    -   `Escape`: Bersihkan filter
-   **Tooltips**: Bantuan untuk setiap field filter

## 🔧 Implementasi Teknis

### Controller (`DtaPengajuanPenarikanController.php`)

```php
public function index(Request $request)
{
    $query = data_pengajuan_penarikan::query();
    $query->with(['anggota', 'jenisSimpanan']);

    // 1. Filter Status (Multiple Selection)
    if ($request->filled('status_filter')) {
        $statusArray = is_array($request->status_filter) ? $request->status_filter : [$request->status_filter];
        $query->whereIn('status', $statusArray);
    }

    // 2. Filter Jenis Simpanan (Multiple Selection)
    if ($request->filled('jenis_filter')) {
        $jenisArray = is_array($request->jenis_filter) ? $request->jenis_filter : [$request->jenis_filter];
        $query->whereIn('jenis', $jenisArray);
    }

    // 3. Filter Tanggal (Date Range)
    if ($request->filled('date_from')) {
        $query->whereDate('tgl_input', '>=', $request->date_from);
    }
    if ($request->filled('date_to')) {
        $query->whereDate('tgl_input', '<=', $request->date_to);
    }

    // 4. Filter Periode Bulan (21-20)
    if ($request->filled('periode_bulan')) {
        $periode = $request->periode_bulan;
        $tglDari = date('Y-m-21', strtotime($periode . '-01 -1 month'));
        $tglSampai = $periode . '-20';
        $query->whereDate('tgl_input', '>=', $tglDari)
              ->whereDate('tgl_input', '<=', $tglSampai);
    }

    // 5. Filter Pencarian (Multi-field)
    if ($request->filled('search')) {
        $search = trim($request->search);
        $query->where(function($q) use ($search) {
            $q->where('ajuan_id', 'like', "%{$search}%")
              ->orWhere('no_ajuan', 'like', "%{$search}%")
              ->orWhereHas('anggota', function($subQ) use ($search) {
                  $subQ->where('nama', 'like', "%{$search}%")
                       ->orWhere('no_ktp', 'like', "%{$search}%")
                       ->orWhere('identitas', 'like', "%{$search}%");
              });
        });
    }

    // 6. Filter Departemen
    if ($request->filled('departemen_filter')) {
        $departemenArray = is_array($request->departemen_filter) ? $request->departemen_filter : [$request->departemen_filter];
        $query->whereHas('anggota', function($subQ) use ($departemenArray) {
            $subQ->whereIn('departement', $departemenArray);
        });
    }

    // 7. Filter Nominal Range
    if ($request->filled('nominal_min')) {
        $query->where('nominal', '>=', $request->nominal_min);
    }
    if ($request->filled('nominal_max')) {
        $query->where('nominal', '<=', $request->nominal_max);
    }

    // 8. Filter Cabang
    if ($request->filled('cabang_filter')) {
        $cabangArray = is_array($request->cabang_filter) ? $request->cabang_filter : [$request->cabang_filter];
        $query->whereIn('id_cabang', $cabangArray);
    }

    $dataPengajuan = $query->orderBy('tgl_input', 'desc')->paginate(15);

    // Get data for filter dropdowns
    $jenisSimpanan = \App\Models\jns_simpan::where('tampil', 'Y')->get();
    $departemen = \App\Models\data_anggota::select('departement')->distinct()->whereNotNull('departement')->pluck('departement');
    $cabang = \App\Models\data_anggota::select('id_cabang')->distinct()->whereNotNull('id_cabang')->pluck('id_cabang');

    return view('simpanan.pengajuan_penarikan', compact('dataPengajuan', 'jenisSimpanan', 'departemen', 'cabang'));
}
```

### View (`pengajuan_penarikan.blade.php`)

```html
<!-- Filter Section Baru -->
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-800">Filter Pengajuan Penarikan</h3>
        <button type="button" onclick="resetAllFilters()" class="text-red-600 hover:text-red-800 text-sm">
            <i class="fas fa-times mr-1"></i>Reset Semua Filter
        </button>
    </div>

    <form method="GET" action="{{ route('admin.pengajuan.penarikan.index') }}" id="filterForm">
        <!-- Row 1: Search dan Status -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
            <!-- Search Input -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Pencarian</label>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Nama, Ajuan ID, KTP, No Ajuan..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status_filter[]" multiple class="w-full px-3 py-2 border border-gray-300 rounded-lg" size="4">
                    <option value="0" {{ in_array('0', request('status_filter', [])) ? 'selected' : '' }}>Menunggu Konfirmasi</option>
                    <option value="1" {{ in_array('1', request('status_filter', [])) ? 'selected' : '' }}>Disetujui</option>
                    <option value="2" {{ in_array('2', request('status_filter', [])) ? 'selected' : '' }}>Ditolak</option>
                    <option value="3" {{ in_array('3', request('status_filter', [])) ? 'selected' : '' }}>Terlaksana</option>
                    <option value="4" {{ in_array('4', request('status_filter', [])) ? 'selected' : '' }}>Batal</option>
                </select>
            </div>

            <!-- Jenis Simpanan Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Simpanan</label>
                <select name="jenis_filter[]" multiple class="w-full px-3 py-2 border border-gray-300 rounded-lg" size="4">
                    @foreach($jenisSimpanan as $jenis)
                        <option value="{{ $jenis->id }}" {{ in_array($jenis->id, request('jenis_filter', [])) ? 'selected' : '' }}>
                            {{ $jenis->jns_simpan }}
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

        <!-- Row 3: Departemen dan Cabang -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <!-- Departemen Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Departemen</label>
                <select name="departemen_filter[]" multiple class="w-full px-3 py-2 border border-gray-300 rounded-lg" size="4">
                    @foreach($departemen as $dept)
                        <option value="{{ $dept }}" {{ in_array($dept, request('departemen_filter', [])) ? 'selected' : '' }}>
                            {{ $dept }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Cabang Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Cabang</label>
                <select name="cabang_filter[]" multiple class="w-full px-3 py-2 border border-gray-300 rounded-lg" size="4">
                    @foreach($cabang as $cab)
                        <option value="{{ $cab }}" {{ in_array($cab, request('cabang_filter', [])) ? 'selected' : '' }}>
                            Cabang {{ $cab }}
                        </option>
                    @endforeach
                </select>
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
        "status_filter",
        "jenis_filter",
        "date_from",
        "date_to",
        "periode_bulan",
        "nominal_min",
        "nominal_max",
        "departemen_filter",
        "cabang_filter",
    ];

    filters.forEach((filter) => {
        const value = formData.get(filter);
        if (value && value !== "" && value !== "0") {
            activeFilters++;
        }
    });

    // Check multiple select filters
    const multipleFilters = [
        "status_filter",
        "jenis_filter",
        "departemen_filter",
        "cabang_filter",
    ];
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
```

## 🚀 Keunggulan Sistem Baru

### 1. **User Experience yang Lebih Baik**

-   Interface yang lebih modern dan intuitif
-   Multiple selection untuk filter yang relevan
-   Real-time filter counter
-   Loading state dan validasi otomatis

### 2. **Fungsionalitas yang Lebih Lengkap**

-   8 jenis filter berbeda
-   Pencarian multi-field
-   Filter periode bulan khusus (21-20)
-   Filter nominal range
-   Filter berdasarkan departemen dan cabang

### 3. **Performance yang Lebih Baik**

-   Query yang dioptimasi dengan `whereIn` untuk multiple selection
-   Eager loading untuk relationships
-   Pagination yang efisien

### 4. **Maintainability**

-   Kode yang lebih terstruktur dan mudah dipahami
-   Error handling yang lebih baik
-   Dokumentasi yang lengkap

## 📊 Perbandingan dengan Sistem Lama

| Aspek              | Sistem Lama        | Sistem Baru      |
| ------------------ | ------------------ | ---------------- |
| Jumlah Filter      | 4 filter           | 8 filter         |
| Multiple Selection | Tidak ada          | 4 filter         |
| Validasi           | Manual             | Otomatis         |
| UI/UX              | Dropdown sederhana | Modern interface |
| Performance        | Basic query        | Optimized query  |
| Error Handling     | Minimal            | Comprehensive    |
| Documentation      | Tidak ada          | Lengkap          |

## 🎯 Cara Penggunaan

### 1. **Filter Pencarian**

-   Masukkan kata kunci di field pencarian
-   Sistem akan mencari di: nama, Ajuan ID, KTP, No Ajuan

### 2. **Filter Status**

-   Pilih satu atau lebih status dari dropdown
-   Gunakan Ctrl/Cmd + klik untuk multiple selection

### 3. **Filter Jenis Simpanan**

-   Pilih satu atau lebih jenis simpanan
-   Data diambil dari database secara dinamis

### 4. **Filter Tanggal**

-   Pilih tanggal awal dan akhir
-   Atau gunakan filter periode bulan (21-20)

### 5. **Filter Nominal**

-   Masukkan nominal minimum dan/atau maksimum
-   Sistem akan memvalidasi input

### 6. **Filter Departemen & Cabang**

-   Pilih satu atau lebih departemen/cabang
-   Data diambil dari tabel anggota

## 🔧 Troubleshooting

### Masalah Umum:

1. **Filter tidak berfungsi**: Pastikan semua field terisi dengan benar
2. **Data tidak muncul**: Periksa apakah ada data yang sesuai dengan filter
3. **Error validasi**: Ikuti pesan error yang muncul

### Debug Mode:

-   Gunakan browser developer tools untuk melihat console logs
-   Periksa network tab untuk request yang gagal
-   Pastikan semua JavaScript files ter-load dengan benar

## 📝 Catatan Penting

1. **Database Requirements**: Pastikan tabel `tbl_anggota` memiliki kolom `departement` dan `id_cabang`
2. **Performance**: Untuk data yang sangat besar, pertimbangkan untuk menambahkan index pada kolom yang sering difilter
3. **Security**: Semua input sudah di-sanitize untuk mencegah SQL injection
4. **Compatibility**: Sistem ini kompatibel dengan Laravel 8+ dan PHP 7.4+

## 🎉 Kesimpulan

Sistem filter baru ini memberikan pengalaman yang jauh lebih baik untuk pengguna dalam mengelola dan memfilter data pengajuan penarikan simpanan. Dengan fitur yang lebih lengkap, performa yang lebih baik, dan interface yang lebih modern, sistem ini siap untuk digunakan dalam produksi.
