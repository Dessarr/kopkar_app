@extends('layouts.admin')

@section('title', 'Billing Tagihan Simpanan')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-2">Billing Tagihan Simpanan</h1>
        <p class="text-gray-600">Generate dan kelola tagihan simpanan untuk anggota koperasi</p>
    </div>

    <!-- Generate Billing Section -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Generate Tagihan</h2>
        
        <form id="generateForm" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="jenis_id" class="block text-sm font-medium text-gray-700 mb-1">Jenis Simpanan</label>
                    <select id="jenis_id" name="jenis_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Pilih Jenis Simpanan</option>
                        @foreach($jenisSimpanan as $jenis)
                        <option value="{{ $jenis['id'] }}">{{ $jenis['nama'] }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="bulan" class="block text-sm font-medium text-gray-700 mb-1">Bulan</label>
                    <select id="bulan" name="bulan" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Pilih Bulan</option>
                        <option value="1">Januari</option>
                        <option value="2">Februari</option>
                        <option value="3">Maret</option>
                        <option value="4">April</option>
                        <option value="5">Mei</option>
                        <option value="6">Juni</option>
                        <option value="7">Juli</option>
                        <option value="8">Agustus</option>
                        <option value="9">September</option>
                        <option value="10">Oktober</option>
                        <option value="11">November</option>
                        <option value="12">Desember</option>
                    </select>
                </div>
                
                <div>
                    <label for="tahun" class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
                    <select id="tahun" name="tahun" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Pilih Tahun</option>
                        @for($i = date('Y'); $i >= 2020; $i--)
                        <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md transition-colors duration-200">
                        <i class="fas fa-plus mr-2"></i>Generate Tagihan
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- View Billing Section -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Lihat Tagihan</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
            <div>
                <label for="filter_jenis_id" class="block text-sm font-medium text-gray-700 mb-1">Jenis Simpanan</label>
                <select id="filter_jenis_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Jenis</option>
                    @foreach($jenisSimpanan as $jenis)
                    <option value="{{ $jenis['id'] }}">{{ $jenis['nama'] }}</option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label for="filter_bulan" class="block text-sm font-medium text-gray-700 mb-1">Bulan</label>
                <select id="filter_bulan" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Bulan</option>
                    <option value="1">Januari</option>
                    <option value="2">Februari</option>
                    <option value="3">Maret</option>
                    <option value="4">April</option>
                    <option value="5">Mei</option>
                    <option value="6">Juni</option>
                    <option value="7">Juli</option>
                    <option value="8">Agustus</option>
                    <option value="9">September</option>
                    <option value="10">Oktober</option>
                    <option value="11">November</option>
                    <option value="12">Desember</option>
                </select>
            </div>
            
            <div>
                <label for="filter_tahun" class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
                <select id="filter_tahun" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Tahun</option>
                    @for($i = date('Y'); $i >= 2020; $i--)
                    <option value="{{ $i }}">{{ $i }}</option>
                    @endfor
                </select>
            </div>
            
            <div class="flex items-end">
                <button onclick="loadBillingData()" class="w-full bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md transition-colors duration-200">
                    <i class="fas fa-search mr-2"></i>Cari
                </button>
            </div>
        </div>
        
        <!-- Billing Data Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIK</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody id="billingTableBody" class="bg-white divide-y divide-gray-200">
                    <!-- Data will be loaded here -->
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div id="pagination" class="mt-4 flex justify-between items-center">
            <!-- Pagination will be loaded here -->
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div id="loadingModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded-lg shadow-lg">
        <div class="flex items-center">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mr-3"></div>
            <span class="text-gray-700">Memproses...</span>
        </div>
    </div>
</div>

<script>
// Generate Billing
document.getElementById('generateForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    if (!data.jenis_id || !data.bulan || !data.tahun) {
        alert('Mohon lengkapi semua field');
        return;
    }
    
    showLoading();
    
    fetch('{{ route("billing.tagihan.generate") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        hideLoading();
        
        if (result.success) {
            alert(result.message);
            loadBillingData(); // Reload table
        } else {
            alert('Error: ' + result.message);
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        alert('Terjadi kesalahan saat generate tagihan');
    });
});

// Load Billing Data
function loadBillingData(page = 1) {
    const jenisId = document.getElementById('filter_jenis_id').value;
    const bulan = document.getElementById('filter_bulan').value;
    const tahun = document.getElementById('filter_tahun').value;
    
    showLoading();
    
    const params = new URLSearchParams({
        page: page,
        jenis_id: jenisId,
        bulan: bulan,
        tahun: tahun
    });
    
    fetch(`{{ route("billing.tagihan.view") }}?${params}`)
    .then(response => response.json())
    .then(result => {
        hideLoading();
        
        if (result.success) {
            renderBillingTable(result.data);
            renderPagination(result.data);
        } else {
            alert('Error: ' + result.message);
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        alert('Terjadi kesalahan saat memuat data');
    });
}

// Render Billing Table
function renderBillingTable(data) {
    const tbody = document.getElementById('billingTableBody');
    tbody.innerHTML = '';
    
    if (data.data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="px-4 py-3 text-center text-gray-500">Tidak ada data</td></tr>';
        return;
    }
    
    data.data.forEach(item => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td class="px-4 py-3 text-sm text-gray-900">${formatDate(item.tgl_transaksi)}</td>
            <td class="px-4 py-3 text-sm text-gray-900">${item.no_ktp}</td>
            <td class="px-4 py-3 text-sm text-gray-900">${item.anggota ? item.anggota.nama_anggota : 'N/A'}</td>
            <td class="px-4 py-3 text-sm text-gray-900">${getJenisNama(item.jenis_id)}</td>
            <td class="px-4 py-3 text-sm text-gray-900">Rp ${formatNumber(item.jumlah)}</td>
            <td class="px-4 py-3 text-sm text-gray-900">${item.keterangan}</td>
            <td class="px-4 py-3 text-sm text-gray-900">
                <button onclick="deleteBilling(${item.id})" class="text-red-600 hover:text-red-900">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Render Pagination
function renderPagination(data) {
    const pagination = document.getElementById('pagination');
    
    if (!data.prev_page_url && !data.next_page_url) {
        pagination.innerHTML = '';
        return;
    }
    
    let html = '<div class="flex space-x-2">';
    
    if (data.prev_page_url) {
        html += `<button onclick="loadBillingData(${data.current_page - 1})" class="px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded">Previous</button>`;
    }
    
    html += `<span class="px-3 py-1">Page ${data.current_page} of ${data.last_page}</span>`;
    
    if (data.next_page_url) {
        html += `<button onclick="loadBillingData(${data.current_page + 1})" class="px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded">Next</button>`;
    }
    
    html += '</div>';
    pagination.innerHTML = html;
}

// Delete Billing
function deleteBilling(id) {
    if (!confirm('Yakin ingin menghapus tagihan ini?')) {
        return;
    }
    
    showLoading();
    
    fetch(`{{ route("billing.tagihan.delete") }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ id: id })
    })
    .then(response => response.json())
    .then(result => {
        hideLoading();
        
        if (result.success) {
            alert(result.message);
            loadBillingData(); // Reload table
        } else {
            alert('Error: ' + result.message);
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menghapus tagihan');
    });
}

// Utility Functions
function showLoading() {
    document.getElementById('loadingModal').classList.remove('hidden');
}

function hideLoading() {
    document.getElementById('loadingModal').classList.add('hidden');
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID');
}

function formatNumber(number) {
    return new Intl.NumberFormat('id-ID').format(number);
}

function getJenisNama(jenisId) {
    const jenisMap = {
        41: 'Simpanan Wajib',
        32: 'Simpanan Sukarela',
        51: 'Simpanan Khusus 1',
        52: 'Simpanan Khusus 2',
        40: 'Simpanan Pokok',
        156: 'Tabungan Perumahan'
    };
    
    return jenisMap[jenisId] || 'Simpanan';
}

// Load initial data
document.addEventListener('DOMContentLoaded', function() {
    loadBillingData();
});
</script>
@endsection
