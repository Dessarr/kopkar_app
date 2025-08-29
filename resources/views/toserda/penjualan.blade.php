@extends('layouts.app')

@section('title', 'Penjualan Toserda')
@section('sub-title', 'Form Penjualan')

@section('content')
<div class="container">
    <!-- Header Section with Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-green-100 border border-green-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="p-2 bg-green-500 rounded-lg">
                    <i class="fas fa-chart-line text-white"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-green-600">Total Penjualan</p>
                    <p class="text-2xl font-bold text-green-900">
                        Rp {{ number_format($transaksi->sum('jumlah'), 0, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>
        
        <div class="bg-blue-100 border border-blue-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="p-2 bg-blue-500 rounded-lg">
                    <i class="fas fa-shopping-cart text-white"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-blue-600">Total Transaksi</p>
                    <p class="text-2xl font-bold text-blue-900">{{ $transaksi->total() }}</p>
                </div>
        </div>
                </div>

        <div class="bg-purple-100 border border-purple-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="p-2 bg-purple-500 rounded-lg">
                    <i class="fas fa-calendar-alt text-white"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-purple-600">Periode Aktif</p>
                    <p class="text-lg font-bold text-purple-900">{{ date('M Y') }}</p>
                </div>
            </div>
        </div>
                </div>

    <!-- Add Penjualan Button -->
    <div class="mb-6">
        <button onclick="openModal()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
            <i class="fas fa-plus mr-2"></i>Add Penjualan
        </button>
                </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Filter Penjualan Toserda</h3>
            <button type="button" onclick="resetAllFilters()" class="text-red-600 hover:text-red-800 text-sm">
                <i class="fas fa-times mr-1"></i>Reset Semua Filter
            </button>
        </div>
        <form method="GET" action="{{ route('toserda.penjualan') }}" id="filterForm">
            <!-- Row 1: Search dan Kas -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                <!-- Search Input -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pencarian</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Kode Transaksi, Keterangan, User..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                <!-- Kas Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kas Tujuan</label>
                    <select name="kas_filter[]" multiple class="w-full px-3 py-2 border border-gray-300 rounded-lg" size="4">
                        @foreach($kas as $kasItem)
                            <option value="{{ $kasItem->id }}" {{ in_array($kasItem->id, request('kas_filter', [])) ? 'selected' : '' }}>
                                {{ $kasItem->nama }}
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

    <!-- Data Table -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold text-gray-800">Data Penjualan Toserda</h2>
            <button onclick="exportData()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <i class="fas fa-download mr-2"></i>Export Excel
            </button>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-4 py-3 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode Transaksi</th>
                        <th class="px-4 py-3 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Transaksi</th>
                        <th class="px-4 py-3 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Uraian</th>
                        <th class="px-4 py-3 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Untuk Kas</th>
                        <th class="px-4 py-3 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dari Akun</th>
                        <th class="px-4 py-3 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                        <th class="px-4 py-3 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-4 py-3 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($transaksi as $index => $tr)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                            TKD{{ str_pad($tr->id, 6, '0', STR_PAD_LEFT) }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                            {{ $tr->tgl_catat ? $tr->tgl_catat->format('d F Y - H:i') : '-' }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                            {{ $tr->keterangan ?? '-' }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                            {{ optional($tr->untukKas)->nama ?? 'N/A' }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                            @php
                                $akunNames = [
                                    '112' => 'Penjualan',
                                    '113' => 'Penjualan Tempo',
                                    '114' => 'Retur Penjualan',
                                    '115' => 'Pendapatan Service',
                                    '116' => 'Potongan Penjualan'
                                ];
                                echo $akunNames[$tr->jns_trans] ?? $tr->jns_trans;
                            @endphp
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($tr->jumlah ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                            {{ $tr->user_name ?? '-' }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium">
                            <button onclick="viewDetail({{ $tr->id }})" class="text-blue-600 hover:text-blue-900 mr-2">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button onclick="editData({{ $tr->id }})" class="text-green-600 hover:text-green-900 mr-2">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteData({{ $tr->id }})" class="text-red-600 hover:text-red-900">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-4 text-center text-gray-500">Belum ada data transaksi penjualan</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
                {{ $transaksi->links() }}
        </div>
    </div>
</div>

<!-- Modal Form -->
<div id="modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full">
            <div class="flex justify-between items-center p-6 border-b">
                <h3 class="text-lg font-semibold text-gray-900">Add Penjualan Toserda</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="addForm" action="{{ route('toserda.store.penjualan') }}" method="POST" class="p-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="keterangan" class="block text-sm font-medium text-gray-700">Keterangan Penjualan</label>
                        <textarea name="keterangan" id="keterangan" rows="3" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50"
                            placeholder="Masukkan keterangan penjualan..."></textarea>
                    </div>

                    <div>
                        <label for="jumlah" class="block text-sm font-medium text-gray-700">Jumlah Penjualan</label>
                        <input type="number" name="jumlah" id="jumlah" min="0" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50"
                            placeholder="0">
                    </div>

                    <div>
                        <label for="untuk_kas_id" class="block text-sm font-medium text-gray-700">Kas Tujuan</label>
                        <select name="untuk_kas_id" id="untuk_kas_id" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50">
                            <option value="">Pilih Kas</option>
                            @foreach($kas as $item)
                            <option value="{{ $item->id }}">{{ $item->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="jns_trans" class="block text-sm font-medium text-gray-700">Jenis Transaksi</label>
                        <select name="jns_trans" id="jns_trans" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50">
                            <option value="">Pilih Jenis</option>
                            <option value="112">112 - Penjualan</option>
                            <option value="113">113 - Penjualan Tempo</option>
                            <option value="114">114 - Retur Penjualan</option>
                            <option value="115">115 - Pendapatan Service</option>
                            <option value="116">116 - Potongan Penjualan</option>
                        </select>
                    </div>

                    <div>
                        <label for="total_format" class="block text-sm font-medium text-gray-700">Total</label>
                        <input type="text" id="total_format" readonly
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100">
                    </div>
                </div>

                <div class="flex justify-end mt-6 space-x-3">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                        Simpan Penjualan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Filter functions
    function updateFilterCount() {
        const form = document.getElementById("filterForm");
        const formData = new FormData(form);
        let activeFilters = 0;

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

        const multipleFilters = ["kas_filter", "user_filter"];
        multipleFilters.forEach((filter) => {
            const values = formData.getAll(filter);
            if (values.length > 0 && values.some((v) => v !== "")) {
                activeFilters++;
            }
        });

        document.getElementById("filterCount").textContent = activeFilters;
    }

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

    function resetAllFilters() {
        window.location.href = "{{ route('toserda.penjualan') }}";
    }

    function clearFilters() {
        const form = document.getElementById("filterForm");
        form.reset();
        updateFilterCount();
    }

    function exportData() {
        const form = document.getElementById("filterForm");
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);
        window.open("{{ route('toserda.penjualan.export') }}?" + params.toString(), '_blank');
    }

    // Modal functions
    function openModal() {
        document.getElementById('modal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('modal').classList.add('hidden');
        document.getElementById('addForm').reset();
        document.getElementById('total_format').value = '';
    }

    // CRUD functions
    function viewDetail(id) {
        // Implement view detail functionality
        alert('View detail for ID: ' + id);
    }

    function editData(id) {
        // Implement edit functionality
        alert('Edit data for ID: ' + id);
    }

    function deleteData(id) {
        if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
            // Implement delete functionality
            alert('Delete data for ID: ' + id);
        }
    }

    // Form submission
    document.getElementById('addForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeModal();
                location.reload();
            } else {
                alert(data.message || 'Terjadi kesalahan');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan');
        });
    });

    // Event listeners
    document.getElementById('jumlah').addEventListener('input', function() {
        const jumlah = this.value;
        document.getElementById('total_format').value = parseFloat(jumlah).toLocaleString('id-ID', {
            style: 'currency',
            currency: 'IDR'
        });
    });

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        updateFilterCount();
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('filterForm').submit();
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
@endpush
@endsection 