# 🎯 BAGIAN 6: USER INTERFACE & EXPERIENCE

## 🎯 **OVERVIEW USER INTERFACE**

Bagian ini menjelaskan tampilan dan pengalaman pengguna pada sistem billing utama. Interface dirancang dengan modern UI menggunakan Tailwind CSS dan JavaScript untuk interaksi yang responsif dan user-friendly.

---

## 🖥️ **6.1 TAMPILAN BILLING UTAMA**

### **View**: `resources/views/billing/utama.blade.php`

### **Layout Header Table**:
```html
<!-- Header Table Billing -->
<div class="overflow-x-auto">
    <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow-sm">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    No
                </th>
                <th class="px-4 py-3 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Nama
                </th>
                <th class="px-4 py-3 border-b text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Simpanan Wajib
                </th>
                <th class="px-4 py-3 border-b text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Simpanan Sukarela
                </th>
                <th class="px-4 py-3 border-b text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Simpanan Khusus
                </th>
                <th class="px-4 py-3 border-b text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Pinjaman
                </th>
                <th class="px-4 py-3 border-b text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Toserda
                </th>
                <th class="px-4 py-3 border-b text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Total Tagihan
                </th>
                <th class="px-4 py-3 border-b text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Tagihan Upload
                </th>
                <th class="px-4 py-3 border-b text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Selisih
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <!-- Data rows akan di-loop di sini -->
        </tbody>
    </table>
</div>
```

### **Kolom Pinjaman**:
```html
<!-- Header Kolom Pinjaman -->
<th class="px-4 py-3 border-b text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
    Pinjaman
</th>

<!-- Data Kolom Pinjaman -->
<td class="px-4 py-3 text-right text-sm">
    {{ number_format($row->tagihan_pinjaman ?? 0, 0, ',', '.') }}
</td>
```

### **Data Row Template**:
```html
@foreach($data as $index => $row)
<tr class="hover:bg-gray-50 transition-colors duration-200">
    <td class="px-4 py-3 text-sm text-gray-900">
        {{ $index + 1 }}
    </td>
    <td class="px-4 py-3 text-sm text-gray-900">
        <div class="flex items-center">
            <div class="flex-shrink-0 h-8 w-8">
                <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                    <span class="text-sm font-medium text-blue-600">
                        {{ strtoupper(substr($row->nama ?? 'A', 0, 1)) }}
                    </span>
                </div>
            </div>
            <div class="ml-3">
                <div class="text-sm font-medium text-gray-900">{{ $row->nama ?? 'N/A' }}</div>
                <div class="text-sm text-gray-500">{{ $row->no_ktp ?? 'N/A' }}</div>
            </div>
        </div>
    </td>
    <td class="px-4 py-3 text-right text-sm">
        {{ number_format($row->tagihan_simpanan_wajib ?? 0, 0, ',', '.') }}
    </td>
    <td class="px-4 py-3 text-right text-sm">
        {{ number_format($row->tagihan_simpanan_sukarela ?? 0, 0, ',', '.') }}
    </td>
    <td class="px-4 py-3 text-right text-sm">
        {{ number_format($row->tagihan_simpanan_khusus_2 ?? 0, 0, ',', '.') }}
    </td>
    <td class="px-4 py-3 text-right text-sm">
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
            {{ number_format($row->tagihan_pinjaman ?? 0, 0, ',', '.') }}
        </span>
    </td>
    <td class="px-4 py-3 text-right text-sm">
        {{ number_format($row->tagihan_toserda ?? 0, 0, ',', '.') }}
    </td>
    <td class="px-4 py-3 text-right text-sm font-medium">
        {{ number_format($row->total_tagihan ?? 0, 0, ',', '.') }}
    </td>
    <td class="px-4 py-3 text-right text-sm">
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
            {{ number_format($row->tagihan_upload ?? 0, 0, ',', '.') }}
        </span>
    </td>
    <td class="px-4 py-3 text-right text-sm">
        @php
            $selisih = $row->selisih_calculated ?? 0;
            $bgColor = $selisih > 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800';
        @endphp
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $bgColor }}">
            {{ number_format($selisih, 0, ',', '.') }}
        </span>
    </td>
</tr>
@endforeach
```

---

## 🔍 **6.2 FILTER DAN PENCARIAN**

### **Filter Periode**:
```html
<!-- Filter Bulan dan Tahun -->
<div class="mb-6 bg-white p-4 rounded-lg shadow-sm border border-gray-200">
    <div class="flex flex-wrap items-center gap-4">
        <div class="flex items-center space-x-2">
            <label for="bulan" class="text-sm font-medium text-gray-700">Bulan:</label>
            <select id="bulan" name="bulan" class="form-select rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                <option value="01" {{ $bulan == '01' ? 'selected' : '' }}>Januari</option>
                <option value="02" {{ $bulan == '02' ? 'selected' : '' }}>Februari</option>
                <option value="03" {{ $bulan == '03' ? 'selected' : '' }}>Maret</option>
                <option value="04" {{ $bulan == '04' ? 'selected' : '' }}>April</option>
                <option value="05" {{ $bulan == '05' ? 'selected' : '' }}>Mei</option>
                <option value="06" {{ $bulan == '06' ? 'selected' : '' }}>Juni</option>
                <option value="07" {{ $bulan == '07' ? 'selected' : '' }}>Juli</option>
                <option value="08" {{ $bulan == '08' ? 'selected' : '' }}>Agustus</option>
                <option value="09" {{ $bulan == '09' ? 'selected' : '' }}>September</option>
                <option value="10" {{ $bulan == '10' ? 'selected' : '' }}>Oktober</option>
                <option value="11" {{ $bulan == '11' ? 'selected' : '' }}>November</option>
                <option value="12" {{ $bulan == '12' ? 'selected' : '' }}>Desember</option>
            </select>
        </div>
        
        <div class="flex items-center space-x-2">
            <label for="tahun" class="text-sm font-medium text-gray-700">Tahun:</label>
            <select id="tahun" name="tahun" class="form-select rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                @for($year = date('Y') - 2; $year <= date('Y') + 1; $year++)
                    <option value="{{ $year }}" {{ $tahun == $year ? 'selected' : '' }}>{{ $year }}</option>
                @endfor
            </select>
        </div>
        
        <button type="button" onclick="filterBilling()" 
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
            </svg>
            Filter
        </button>
        
        <button type="button" onclick="exportToExcel()" 
                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Export Excel
        </button>
    </div>
</div>
```

### **Search Bar**:
```html
<!-- Search Bar -->
<div class="mb-4">
    <div class="relative">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </div>
        <input type="text" id="searchInput" 
               class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm" 
               placeholder="Cari berdasarkan nama atau nomor KTP...">
    </div>
</div>
```

---

## 📊 **6.3 PERIODE SUMMARY TABLE**

### **Period Summary Section**:
```html
<!-- Period Summary Table -->
<div id="period-summary" class="mb-6">
    <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Ringkasan Periode</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-blue-50 p-4 rounded-lg">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-blue-800">Total Anggota</p>
                        <p id="total-anggota" class="text-lg font-semibold text-blue-900">0</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-green-50 p-4 rounded-lg">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">Simpanan Pokok*</p>
                        <p id="simpanan-pokok" class="text-lg font-semibold text-green-900">Rp 0</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-yellow-50 p-4 rounded-lg">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-yellow-800">Simpanan Wajib</p>
                        <p id="simpanan-wajib" class="text-lg font-semibold text-yellow-900">Rp 0</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-purple-50 p-4 rounded-lg">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-purple-800">Simpanan Sukarela</p>
                        <p id="simpanan-sukarela" class="text-lg font-semibold text-purple-900">Rp 0</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-4 text-xs text-gray-500">
            * Simpanan Pokok = Total simpanan pokok semua anggota aktif
        </div>
    </div>
</div>
```

---

## 📤 **6.4 EXCEL UPLOAD MODAL**

### **Upload Modal**:
```html
<!-- Excel Upload Modal -->
<div id="uploadModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Upload File Excel</h3>
                <button onclick="closeUploadModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form id="uploadForm" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pilih File Excel</label>
                    <input type="file" id="excelFile" name="excel_file" accept=".xlsx,.xls" 
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Bulan</label>
                    <select name="bulan" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="01">Januari</option>
                        <option value="02">Februari</option>
                        <option value="03">Maret</option>
                        <option value="04">April</option>
                        <option value="05">Mei</option>
                        <option value="06">Juni</option>
                        <option value="07">Juli</option>
                        <option value="08">Agustus</option>
                        <option value="09">September</option>
                        <option value="10">Oktober</option>
                        <option value="11">November</option>
                        <option value="12">Desember</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tahun</label>
                    <select name="tahun" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @for($year = date('Y') - 2; $year <= date('Y') + 1; $year++)
                            <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
                        @endfor
                    </select>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeUploadModal()" 
                            class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
```

---

## 🎮 **6.5 JAVASCRIPT INTERACTIONS**

### **Filter Billing Function**:
```javascript
/**
 * Filter billing berdasarkan bulan dan tahun
 */
function filterBilling() {
    const bulan = document.getElementById('bulan').value;
    const tahun = document.getElementById('tahun').value;
    
    // Show loading
    showLoading();
    
    // Redirect dengan parameter
    window.location.href = `/billing/utama?bulan=${bulan}&tahun=${tahun}`;
}

/**
 * Show loading indicator
 */
function showLoading() {
    const loadingDiv = document.createElement('div');
    loadingDiv.id = 'loading';
    loadingDiv.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50';
    loadingDiv.innerHTML = `
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <div class="flex items-center">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <span class="ml-3 text-gray-700">Loading...</span>
            </div>
        </div>
    `;
    document.body.appendChild(loadingDiv);
}
```

### **Search Function**:
```javascript
/**
 * Search functionality
 */
document.getElementById('searchInput').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const tableRows = document.querySelectorAll('tbody tr');
    
    tableRows.forEach(row => {
        const nama = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
        const noKtp = row.querySelector('td:nth-child(2) .text-gray-500').textContent.toLowerCase();
        
        if (nama.includes(searchTerm) || noKtp.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
```

### **Excel Upload Function**:
```javascript
/**
 * Handle Excel upload
 */
document.getElementById('uploadForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const uploadButton = this.querySelector('button[type="submit"]');
    const originalText = uploadButton.textContent;
    
    // Show loading state
    uploadButton.disabled = true;
    uploadButton.textContent = 'Uploading...';
    
    fetch('/billing/upload-excel', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('File berhasil diupload!', 'success');
            closeUploadModal();
            // Refresh halaman untuk update data
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showNotification(data.message || 'Gagal upload file', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Terjadi kesalahan saat upload', 'error');
    })
    .finally(() => {
        uploadButton.disabled = false;
        uploadButton.textContent = originalText;
    });
});
```

### **Period Summary Update**:
```javascript
/**
 * Update period summary table
 */
function updatePeriodSummary() {
    const bulan = document.getElementById('bulan').value;
    const tahun = document.getElementById('tahun').value;
    
    fetch(`/billing/period-summary?bulan=${bulan}&tahun=${tahun}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('total-anggota').textContent = data.data.totalAnggota;
                document.getElementById('simpanan-pokok').textContent = formatCurrency(data.data.simpananPokok);
                document.getElementById('simpanan-wajib').textContent = formatCurrency(data.data.simpananWajib);
                document.getElementById('simpanan-sukarela').textContent = formatCurrency(data.data.simpananSukarela);
            }
        })
        .catch(error => {
            console.error('Error updating period summary:', error);
        });
}

/**
 * Format currency
 */
function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(amount);
}
```

---

## 📱 **6.6 RESPONSIVE DESIGN**

### **Mobile Responsive**:
```html
<!-- Responsive table wrapper -->
<div class="overflow-x-auto -mx-4 sm:-mx-6 lg:-mx-8">
    <div class="inline-block min-w-full align-middle">
        <div class="overflow-hidden shadow-sm ring-1 ring-black ring-opacity-5">
            <table class="min-w-full divide-y divide-gray-300">
                <!-- Table content -->
            </table>
        </div>
    </div>
</div>

<!-- Mobile card view -->
<div class="block lg:hidden">
    @foreach($data as $index => $row)
    <div class="bg-white shadow rounded-lg p-4 mb-4">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center">
                <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                    <span class="text-sm font-medium text-blue-600">
                        {{ strtoupper(substr($row->nama ?? 'A', 0, 1)) }}
                    </span>
                </div>
                <div class="ml-3">
                    <div class="text-sm font-medium text-gray-900">{{ $row->nama ?? 'N/A' }}</div>
                    <div class="text-sm text-gray-500">{{ $row->no_ktp ?? 'N/A' }}</div>
                </div>
            </div>
        </div>
        
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <span class="text-gray-500">Pinjaman:</span>
                <div class="font-medium">{{ number_format($row->tagihan_pinjaman ?? 0, 0, ',', '.') }}</div>
            </div>
            <div>
                <span class="text-gray-500">Total Tagihan:</span>
                <div class="font-medium">{{ number_format($row->total_tagihan ?? 0, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    @endforeach
</div>
```

---

## 🚀 **KESIMPULAN BAGIAN 6**

Bagian 6 ini telah mencakup secara lengkap:

✅ **Tampilan Billing Utama** - Layout table yang modern dan responsive
✅ **Filter dan Pencarian** - Filter periode dan search functionality
✅ **Period Summary Table** - Dashboard ringkasan periode
✅ **Excel Upload Modal** - Interface untuk upload file Excel
✅ **JavaScript Interactions** - Function untuk interaksi user
✅ **Responsive Design** - Mobile-friendly interface

**Next Step**: Lanjut ke Bagian 7 untuk Validasi & Business Logic.

