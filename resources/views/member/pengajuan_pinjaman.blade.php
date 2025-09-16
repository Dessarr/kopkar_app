@extends('layouts.member')

@section('title', 'Data Pengajuan Pinjaman')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow-md p-6">
        @if (session('success'))
            <div class="mb-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">{{ session('error') }}</div>
        @endif
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-800">Data Pengajuan Pinjaman</h1>
            <a href="{{ route('member.tambah.pengajuan.pinjaman') }}"
                class="bg-[#14AE5C] hover:bg-[#14AE5C]/80 text-white px-4 py-2 rounded-lg">
                + Pengajuan Baru
            </a>
        </div>

        <!-- Filter Section -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4">Filter Data Pengajuan</h2>

            <form action="{{ route('member.pengajuan.pinjaman') }}" method="GET" id="filterForm">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                    <!-- Filter Tanggal -->
                    <div class="relative">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Filter Tanggal</label>
                        <div class="relative">
                            <button type="button" id="tanggalBtn"
                                class="w-full flex items-center justify-between px-3 py-2 border border-gray-300 rounded-md bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <span id="tanggalText">Semua Tanggal</span>
                                <i class="fas fa-calendar text-gray-400"></i>
                            </button>
                            <div id="tanggalDropdown"
                                class="hidden absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg">
                                <div class="p-2 space-y-1">
                                    <button type="button"
                                        class="w-full text-left px-2 py-1 text-sm hover:bg-gray-100 rounded"
                                        data-value="today">Hari ini</button>
                                    <button type="button"
                                        class="w-full text-left px-2 py-1 text-sm hover:bg-gray-100 rounded"
                                        data-value="yesterday">Kemarin</button>
                                    <button type="button"
                                        class="w-full text-left px-2 py-1 text-sm hover:bg-gray-100 rounded"
                                        data-value="7days">7 Hari yang lalu</button>
                                    <button type="button"
                                        class="w-full text-left px-2 py-1 text-sm hover:bg-gray-100 rounded"
                                        data-value="30days">30 Hari yang lalu</button>
                                    <button type="button"
                                        class="w-full text-left px-2 py-1 text-sm hover:bg-gray-100 rounded"
                                        data-value="thisMonth">Bulan ini</button>
                                    <button type="button"
                                        class="w-full text-left px-2 py-1 text-sm hover:bg-gray-100 rounded"
                                        data-value="lastMonth">Bulan kemarin</button>
                                    <button type="button"
                                        class="w-full text-left px-2 py-1 text-sm hover:bg-gray-100 rounded"
                                        data-value="thisYear">Tahun ini</button>
                                    <button type="button"
                                        class="w-full text-left px-2 py-1 text-sm hover:bg-gray-100 rounded"
                                        data-value="lastYear">Tahun kemarin</button>
                                    <hr class="my-2">
                                    <div class="p-2">
                                        <div class="mb-2">
                                            <label class="block text-xs text-gray-600 mb-1">FROM:</label>
                                            <input type="date" id="dateFrom" name="date_from"
                                                value="{{ request('date_from') }}"
                                                class="w-full px-2 py-1 text-xs border border-gray-300 rounded">
                                        </div>
                                        <div class="mb-2">
                                            <label class="block text-xs text-gray-600 mb-1">TO:</label>
                                            <input type="date" id="dateTo" name="date_to"
                                                value="{{ request('date_to') }}"
                                                class="w-full px-2 py-1 text-xs border border-gray-300 rounded">
                                        </div>
                                        <div class="flex space-x-1">
                                            <button type="button" id="cancelDate"
                                                class="flex-1 px-2 py-1 text-xs bg-gray-300 text-gray-700 rounded hover:bg-gray-400">Cancel</button>
                                            <button type="button" id="applyDate"
                                                class="flex-1 px-2 py-1 text-xs bg-green-500 text-white rounded hover:bg-green-600">Apply</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Jenis -->
                    <div class="relative">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Filter Jenis</label>
                        <div class="relative">
                            <button type="button" id="jenisBtn"
                                class="w-full flex items-center justify-between px-3 py-2 border border-gray-300 rounded-md bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <span id="jenisText">Semua Jenis</span>
                                <i class="fas fa-chevron-down text-gray-400"></i>
                            </button>
                            <div id="jenisDropdown"
                                class="hidden absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg">
                                <div class="p-2">
                                    <input type="text" id="jenisSearch" placeholder="Cari jenis..."
                                        class="w-full px-2 py-1 text-sm border border-gray-300 rounded mb-2">
                                    <div class="space-y-1 max-h-40 overflow-y-auto">
                                        <button type="button"
                                            class="w-full text-left px-2 py-1 text-sm hover:bg-gray-100 rounded"
                                            data-value="">Semua Jenis</button>
                                        <button type="button"
                                            class="w-full text-left px-2 py-1 text-sm hover:bg-gray-100 rounded"
                                            data-value="1">Biasa</button>
                                        <button type="button"
                                            class="w-full text-left px-2 py-1 text-sm hover:bg-gray-100 rounded"
                                            data-value="3">Barang</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="jenis" id="jenisHidden" value="{{ request('jenis') }}">
                    </div>

                    <!-- Filter Status -->
                    <div class="relative">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Filter Status</label>
                        <div class="relative">
                            <button type="button" id="statusBtn"
                                class="w-full flex items-center justify-between px-3 py-2 border border-gray-300 rounded-md bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <span id="statusText">Semua Status</span>
                                <i class="fas fa-chevron-down text-gray-400"></i>
                            </button>
                            <div id="statusDropdown"
                                class="hidden absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg">
                                <div class="p-2 space-y-1">
                                    <button type="button"
                                        class="w-full text-left px-2 py-1 text-sm hover:bg-gray-100 rounded"
                                        data-value="">Semua Status</button>
                                    <button type="button"
                                        class="w-full text-left px-2 py-1 text-sm hover:bg-gray-100 rounded"
                                        data-value="0">Pending</button>
                                    <button type="button"
                                        class="w-full text-left px-2 py-1 text-sm hover:bg-gray-100 rounded"
                                        data-value="1">Disetujui</button>
                                    <button type="button"
                                        class="w-full text-left px-2 py-1 text-sm hover:bg-gray-100 rounded"
                                        data-value="2">Ditolak</button>
                                    <button type="button"
                                        class="w-full text-left px-2 py-1 text-sm hover:bg-gray-100 rounded"
                                        data-value="3">Terlaksana</button>
                                    <button type="button"
                                        class="w-full text-left px-2 py-1 text-sm hover:bg-gray-100 rounded"
                                        data-value="4">Batal</button>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="status" id="statusHidden" value="{{ request('status') }}">
                    </div>

                    <!-- Pencarian -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pencarian</label>
                        <input type="text" name="search" id="searchInput" placeholder="Cari keterangan..."
                            value="{{ request('search') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-between items-center">
                    <div class="flex space-x-2">
                        <button type="submit"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                            <i class="fas fa-filter mr-2"></i>FILTER
                        </button>
                        <button type="button" onclick="resetFilter()"
                            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                            <i class="fas fa-refresh mr-2"></i>Reset
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border">
                <thead class="bg-white">
                    <tr>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600 border">Tanggal</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600 border">Jenis</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600 border">Jumlah</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600 border">Jml Angsur</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600 border">Keterangan</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600 border">Alasan</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600 border">Tanggal Update</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600 border">Status</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600 border">Opsi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($dataPengajuan as $pengajuan)
                    <tr>
                        <td class="py-3 px-4 text-sm text-gray-700 border">{{ \Carbon\Carbon::parse($pengajuan->tgl_input)->format('d/m/Y H:i') }}</td>
                        <td class="py-3 px-4 text-sm text-gray-700 border">
                            @php
                            $jenisMap = [
                                '1' => 'Biasa',
                                '3' => 'Barang'
                            ];
                            $jenisText = $jenisMap[$pengajuan->jenis] ?? $pengajuan->jenis;
                            @endphp
                            {{ $jenisText }}
                        </td>
                        <td class="py-3 px-4 text-sm text-gray-700 border">Rp {{ number_format($pengajuan->nominal,0,',','.') }}</td>
                        <td class="py-3 px-4 text-sm text-gray-700 border">{{ $pengajuan->lama_ags }} bln</td>
                        <td class="py-3 px-4 text-sm text-gray-700 border">{{ $pengajuan->keterangan }}</td>
                        <td class="py-3 px-4 text-sm text-gray-700 border">{{ $pengajuan->alasan }}</td>
                        <td class="py-3 px-4 text-sm text-gray-700 border">{{ $pengajuan->tgl_update ? \Carbon\Carbon::parse($pengajuan->tgl_update)->format('d/m/Y H:i') : '-' }}</td>
                        <td class="py-3 px-4 text-sm text-gray-700 border">
                            @php $statusMap = [0=>'Pending',1=>'Disetujui',2=>'Ditolak',3=>'Terlaksana',4=>'Batal']; @endphp
                            <span>{{ $statusMap[$pengajuan->status] ?? $pengajuan->status }}</span>
                        </td>
                        <td class="py-3 px-4 text-sm text-gray-700 border">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('member.pengajuan.pinjaman.show', $pengajuan->id) }}" class="px-2 py-1 text-xs bg-blue-50 text-blue-700 border border-blue-200 rounded">Detail</a>
                                @if((int)$pengajuan->status === 0)
                                    <form action="{{ route('member.pengajuan.pinjaman.cancel', $pengajuan->id) }}" method="POST" onsubmit="return confirm('Batalkan pengajuan ini?')">
                                        @csrf
                                        <button type="submit" class="px-2 py-1 text-xs bg-red-50 text-red-700 border border-red-200 rounded">Batal</button>
                                    </form>
                                @endif
                                @if((int)$pengajuan->status === 1)
                                    <a href="{{ route('member.pengajuan.pinjaman.cetak', $pengajuan->id) }}" target="_blank" class="px-2 py-1 text-xs bg-purple-50 text-purple-700 border border-purple-200 rounded">Cetak</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td class="py-3 px-4 text-center text-sm text-gray-500 border" colspan="9">Tidak ada data</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4 flex items-center justify-between">
            <div class="text-sm text-gray-500">{{ $dataPengajuan->firstItem() }} - {{ $dataPengajuan->lastItem() }} dari total {{ $dataPengajuan->total() }} data</div>
            <div class="flex items-center space-x-2">
                <div class="mt-6">{{ $dataPengajuan->links('vendor.pagination.simple-tailwind') }}</div>
            </div>
        </div>
    </div>
</div>

<script>
// Filter functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize current filter values
    initializeFilters();

    // Setup dropdown toggles
    setupDropdowns();

    // Setup date filter functionality
    setupDateFilter();

    // Setup jenis filter functionality
    setupJenisFilter();

    // Setup status filter functionality
    setupStatusFilter();
    
    // Setup search functionality
    setupSearchFilter();
});

function initializeFilters() {
    // Set current filter values from URL parameters
    const urlParams = new URLSearchParams(window.location.search);

    // Set tanggal filter
    const dateFrom = urlParams.get('date_from');
    const dateTo = urlParams.get('date_to');
    if (dateFrom && dateTo) {
        document.getElementById('tanggalText').textContent = `${dateFrom} - ${dateTo}`;
        document.getElementById('dateFrom').value = dateFrom;
        document.getElementById('dateTo').value = dateTo;
    }

    // Set jenis filter
    const jenis = urlParams.get('jenis');
    if (jenis) {
        const jenisText = document.querySelector(`button[data-value="${jenis}"]`);
        if (jenisText) {
            document.getElementById('jenisText').textContent = jenisText.textContent;
            document.getElementById('jenisHidden').value = jenis;
        }
    }

    // Set status filter
    const status = urlParams.get('status');
    if (status !== null && status !== '') {
        const statusMap = {
            '0': 'Pending',
            '1': 'Disetujui',
            '2': 'Ditolak',
            '3': 'Terlaksana',
            '4': 'Batal'
        };
        document.getElementById('statusText').textContent = statusMap[status] || 'Semua Status';
        document.getElementById('statusHidden').value = status;
    }
}

function setupDropdowns() {
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.relative')) {
            closeAllDropdowns();
        }
    });
}

function setupDateFilter() {
    const tanggalBtn = document.getElementById('tanggalBtn');
    const tanggalDropdown = document.getElementById('tanggalDropdown');
    const dateFrom = document.getElementById('dateFrom');
    const dateTo = document.getElementById('dateTo');
    const applyDate = document.getElementById('applyDate');
    const cancelDate = document.getElementById('cancelDate');

    tanggalBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        tanggalDropdown.classList.toggle('hidden');
        closeOtherDropdowns('tanggalDropdown');
    });

    // Predefined date options
    tanggalDropdown.addEventListener('click', function(e) {
        if (e.target.tagName === 'BUTTON' && !e.target.id) {
            const value = e.target.dataset.value;
            const today = new Date();
            let fromDate, toDate;

            switch (value) {
                case 'today':
                    fromDate = toDate = today.toISOString().split('T')[0];
                    break;
                case 'yesterday':
                    const yesterday = new Date(today);
                    yesterday.setDate(yesterday.getDate() - 1);
                    fromDate = toDate = yesterday.toISOString().split('T')[0];
                    break;
                case '7days':
                    const weekAgo = new Date(today);
                    weekAgo.setDate(weekAgo.getDate() - 7);
                    fromDate = weekAgo.toISOString().split('T')[0];
                    toDate = today.toISOString().split('T')[0];
                    break;
                case '30days':
                    const monthAgo = new Date(today);
                    monthAgo.setDate(monthAgo.getDate() - 30);
                    fromDate = monthAgo.toISOString().split('T')[0];
                    toDate = today.toISOString().split('T')[0];
                    break;
                case 'thisMonth':
                    fromDate = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
                    toDate = today.toISOString().split('T')[0];
                    break;
                case 'lastMonth':
                    const lastMonth = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                    fromDate = lastMonth.toISOString().split('T')[0];
                    toDate = new Date(today.getFullYear(), today.getMonth(), 0).toISOString().split('T')[0];
                    break;
                case 'thisYear':
                    fromDate = new Date(today.getFullYear(), 0, 1).toISOString().split('T')[0];
                    toDate = today.toISOString().split('T')[0];
                    break;
                case 'lastYear':
                    fromDate = new Date(today.getFullYear() - 1, 0, 1).toISOString().split('T')[0];
                    toDate = new Date(today.getFullYear() - 1, 11, 31).toISOString().split('T')[0];
                    break;
            }

            if (fromDate && toDate) {
                dateFrom.value = fromDate;
                dateTo.value = toDate;
                document.getElementById('tanggalText').textContent = `${fromDate} - ${toDate}`;
                tanggalDropdown.classList.add('hidden');
            }
        }
    });

    applyDate.addEventListener('click', function() {
        if (dateFrom.value && dateTo.value) {
            document.getElementById('tanggalText').textContent = `${dateFrom.value} - ${dateTo.value}`;
            tanggalDropdown.classList.add('hidden');
            // Auto submit form when date range is applied
            document.getElementById('filterForm').submit();
        }
    });

    cancelDate.addEventListener('click', function() {
        dateFrom.value = '';
        dateTo.value = '';
        document.getElementById('tanggalText').textContent = 'Semua Tanggal';
        tanggalDropdown.classList.add('hidden');
    });
}

function setupJenisFilter() {
    const jenisBtn = document.getElementById('jenisBtn');
    const jenisDropdown = document.getElementById('jenisDropdown');
    const jenisSearch = document.getElementById('jenisSearch');
    const jenisHidden = document.getElementById('jenisHidden');

    jenisBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        jenisDropdown.classList.toggle('hidden');
        closeOtherDropdowns('jenisDropdown');
    });

    jenisDropdown.addEventListener('click', function(e) {
        if (e.target.tagName === 'BUTTON' && !e.target.id) {
            const value = e.target.dataset.value;
            const text = e.target.textContent;

            jenisHidden.value = value;
            document.getElementById('jenisText').textContent = text;
            jenisDropdown.classList.add('hidden');
            // Auto submit form when jenis is selected
            document.getElementById('filterForm').submit();
        }
    });

    // Search functionality
    jenisSearch.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const buttons = jenisDropdown.querySelectorAll('button');

        buttons.forEach(button => {
            const text = button.textContent.toLowerCase();
            if (text.includes(searchTerm)) {
                button.style.display = 'block';
            } else {
                button.style.display = 'none';
            }
        });
    });
}

function setupStatusFilter() {
    const statusBtn = document.getElementById('statusBtn');
    const statusDropdown = document.getElementById('statusDropdown');
    const statusHidden = document.getElementById('statusHidden');

    statusBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        statusDropdown.classList.toggle('hidden');
        closeOtherDropdowns('statusDropdown');
    });

    statusDropdown.addEventListener('click', function(e) {
        if (e.target.tagName === 'BUTTON' && !e.target.id) {
            const value = e.target.dataset.value;
            const text = e.target.textContent;

            statusHidden.value = value;
            document.getElementById('statusText').textContent = text;
            statusDropdown.classList.add('hidden');
            // Auto submit form when status is selected
            document.getElementById('filterForm').submit();
        }
    });
}

function closeAllDropdowns() {
    document.getElementById('tanggalDropdown').classList.add('hidden');
    document.getElementById('jenisDropdown').classList.add('hidden');
    document.getElementById('statusDropdown').classList.add('hidden');
}

function closeOtherDropdowns(currentDropdown) {
    const dropdowns = ['tanggalDropdown', 'jenisDropdown', 'statusDropdown'];
    dropdowns.forEach(dropdown => {
        if (dropdown !== currentDropdown) {
            document.getElementById(dropdown).classList.add('hidden');
        }
    });
}

function setupSearchFilter() {
    const searchInput = document.getElementById('searchInput');
    let searchTimeout;

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            // Auto submit form after 500ms of no typing
            document.getElementById('filterForm').submit();
        }, 500);
    });
}

function resetFilter() {
    // Reset all filter values
    document.getElementById('tanggalText').textContent = 'Semua Tanggal';
    document.getElementById('jenisText').textContent = 'Semua Jenis';
    document.getElementById('statusText').textContent = 'Semua Status';
    document.getElementById('searchInput').value = '';
    document.getElementById('dateFrom').value = '';
    document.getElementById('dateTo').value = '';
    document.getElementById('jenisHidden').value = '';
    document.getElementById('statusHidden').value = '';

    // Redirect to base URL
    window.location.href = '{{ route("member.pengajuan.pinjaman") }}';
}
</script>
@endsection