@extends('layouts.app')

@section('title', 'Transaksi Kas')
@section('sub-title', 'Pengeluaran Kas Tunai')

@section('content')
<div class="px-1 justify-center flex flex-col">
    <!-- Header Section -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Data Transaksi Pengeluaran Kas</h1>
            <p class="text-gray-600 mt-1">Kelola semua transaksi pengeluaran kas dengan sistem filter lengkap</p>
        </div>
        <div class="flex space-x-3">
            <button onclick="openModal('addModal')" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                <i class="fas fa-plus"></i>
                <span>Tambah Pengeluaran</span>
            </button>
            <div class="bg-red-100 p-2 rounded-lg border-2 border-red-400 space-x-2 flex justify-around cursor-pointer" onclick="exportData()">
                <p class="text-sm">Export</p> 
                <img src="{{ asset('img/icons-bootstrap/export/cloud-download.svg') }}" class="h-auto w-[20px]">
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-100 text-red-600">
                    <i class="fas fa-money-bill-wave text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Pengeluaran</p>
                    <p class="text-2xl font-semibold text-gray-900">Rp{{ number_format($totalPengeluaran, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-list text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Transaksi</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($totalRecords) }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <i class="fas fa-calendar text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Periode Aktif</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ request('periode_bulan', date('Y-m')) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Filter Pengeluaran Kas</h3>
            <button type="button" onclick="resetAllFilters()" class="text-red-600 hover:text-red-800 text-sm">
                <i class="fas fa-times mr-1"></i>Reset Semua Filter
            </button>
        </div>

        <form method="GET" action="{{ route('admin.transaksi.pengeluaran') }}" id="filterForm">
            <!-- Row 1: Search dan Kas -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                <!-- Search Input -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pencarian</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Keterangan, nama kas, user..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>

                <!-- Kas Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kas Asal</label>
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
                    <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        <i class="fas fa-filter mr-2"></i>Terapkan Filter
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 border-b bg-gray-50">
            <h2 class="text-lg font-semibold text-gray-800">Data Pengeluaran Kas</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="table-auto w-full border border-gray-300 text-center">
                <thead class="bg-gray-100">
                    <tr class="text-sm">
                        <th class="py-3 border px-4">No</th>
                        <th class="py-3 border px-4">Kode Transaksi</th>
                        <th class="py-3 border px-4">Tanggal Transaksi</th>
                        <th class="py-3 border px-4">Keterangan</th>
                        <th class="py-3 border px-4">Dari Kas</th>
                        <th class="py-3 border px-4">Jumlah</th>
                        <th class="py-3 border px-4">User</th>
                        <th class="py-3 border px-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dataKas as $kas)
                    <tr class="text-sm align-middle hover:bg-gray-50">
                        <td class="py-3 border px-4">
                            {{ ($dataKas->currentPage() - 1) * $dataKas->perPage() + $loop->iteration }}
                        </td>
                        <td class="py-3 border px-4">
                            <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs font-medium">
                                {{ 'TKK' . str_pad($kas->id, 5, '0', STR_PAD_LEFT) }}
                            </span>
                        </td>
                        <td class="py-3 border px-4">{{ \Carbon\Carbon::parse($kas->tgl)->format('d/m/Y') }}</td>
                        <td class="py-3 border px-4 text-left">{{ $kas->keterangan }}</td>
                        <td class="py-3 border px-4">{{ $kas->kasAsal->nama ?? '-' }}</td>
                        <td class="py-3 border px-4 font-semibold text-red-600">
                            Rp{{ number_format($kas->kredit, 0, ',', '.') }}
                        </td>
                        <td class="py-3 border px-4">{{ $kas->user }}</td>
                        <td class="py-3 border px-4">
                            <div class="flex space-x-2 justify-center">
                                <button onclick="viewDetail({{ $kas->id }})" class="text-blue-600 hover:text-blue-800">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button onclick="editData({{ $kas->id }})" class="text-yellow-600 hover:text-yellow-800">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deleteData({{ $kas->id }})" class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-2"></i>
                            <p>Tidak ada data pengeluaran kas</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-5 w-full relative px-2 py-2">
        <div class="mx-auto w-fit">
            <div class="bg-white px-4 py-1 flex flex-row rounded-full justify-center items-center space-x-2 border border-gray-300 shadow-sm">
                @for ($i = 1; $i <= $dataKas->lastPage(); $i++)
                    @if ($i == 1 || $i == $dataKas->lastPage() || ($i >= $dataKas->currentPage() - 1 && $i <= $dataKas->currentPage() + 1))
                        <a href="{{ $dataKas->url($i) }}">
                            <div class="rounded-md px-2 py-0.5 text-sm border border-gray-300 {{ $dataKas->currentPage() == $i ? 'bg-gray-100 font-bold' : '' }}">
                                {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                            </div>
                        </a>
                        @elseif ($i == 2 || $i == $dataKas->lastPage() - 1)
                        <div class="rounded-md px-2 py-0.5 text-sm">...</div>
                        @endif
                        @endfor
            </div>
        </div>

        <div class="absolute right-4 top-1/2 -translate-y-1/2 whitespace-nowrap text-sm text-gray-400">
            Displaying {{ $dataKas->firstItem() }} to {{ $dataKas->lastItem() }} of {{ $dataKas->total() }} items
        </div>
    </div>
</div>

<!-- Add Modal -->
<div id="addModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
            <div class="flex items-center justify-between p-4 border-b">
                <h3 class="text-lg font-semibold">Tambah Pengeluaran Kas</h3>
                <button onclick="closeModal('addModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="addForm" class="p-4">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Transaksi</label>
                        <input type="datetime-local" name="tgl_catat" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah</label>
                        <input type="number" name="jumlah" min="0" step="1000" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                        <textarea name="keterangan" required rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Akun</label>
                        <select name="akun" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            <option value="">Pilih Jenis Akun</option>
                            @foreach($jenisAkun as $akun)
                                <option value="{{ $akun->id }}">{{ $akun->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kas Asal</label>
                        <select name="dari_kas_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            <option value="">Pilih Kas Asal</option>
                            @foreach($listKas as $kas)
                                <option value="{{ $kas->id }}">{{ $kas->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeModal('addModal')" class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Batal
                    </button>
                    <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Fungsi untuk menghitung jumlah filter aktif
function updateFilterCount() {
    const form = document.getElementById("filterForm");
    const formData = new FormData(form);
    let activeFilters = 0;

    const filters = [
        "search", "kas_filter", "user_filter", "date_from", "date_to",
        "periode_bulan", "nominal_min", "nominal_max"
    ];

    filters.forEach((filter) => {
        const value = formData.get(filter);
        if (value && value !== "" && value !== "0") {
            activeFilters++;
        }
    });

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

    if (dateFrom && dateTo && dateFrom > dateTo) {
        alert("Tanggal awal tidak boleh lebih besar dari tanggal akhir");
        return false;
    }

    if (nominalMin && nominalMax && parseInt(nominalMin) > parseInt(nominalMax)) {
        alert("Nominal minimum tidak boleh lebih besar dari nominal maksimum");
        return false;
    }

    return true;
}

// Fungsi untuk reset semua filter
function resetAllFilters() {
    if (confirm('Apakah Anda yakin ingin mereset semua filter?')) {
        window.location.href = "{{ route('admin.transaksi.pengeluaran') }}";
    }
}

// Fungsi untuk bersihkan filter
function clearFilters() {
    const form = document.getElementById("filterForm");
    form.reset();
    updateFilterCount();
}

// Fungsi untuk export data
function exportData() {
    const form = document.getElementById("filterForm");
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    
    window.open("{{ route('admin.transaksi.pengeluaran.export') }}?" + params.toString(), '_blank');
}

// Modal functions
function openModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

// CRUD functions
function viewDetail(id) {
    // Implementasi view detail
    alert('View detail untuk ID: ' + id);
}

function editData(id) {
    // Implementasi edit data
    alert('Edit data untuk ID: ' + id);
}

function deleteData(id) {
    if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
        // Implementasi delete data
        alert('Delete data untuk ID: ' + id);
    }
}

// Form submission
document.getElementById('addForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch("{{ route('admin.transaksi.pengeluaran.store') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(Object.fromEntries(formData))
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Data berhasil disimpan');
            closeModal('addModal');
            location.reload();
        } else {
            alert('Gagal menyimpan data: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menyimpan data');
    });
});

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    updateFilterCount();
    
    document.getElementById("filterForm").addEventListener('submit', function(e) {
        if (!validateFilterForm()) {
            e.preventDefault();
        }
    });

    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            document.getElementById("filterForm").submit();
        }
        if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
            e.preventDefault();
            resetAllFilters();
        }
        if (e.key === 'Escape') {
            clearFilters();
        }
    });
});
</script>
@endsection