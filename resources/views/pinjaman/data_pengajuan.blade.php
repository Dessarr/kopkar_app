    @extends('layouts.app')

    @section('title', 'Data Pengajuan')
    @section('sub-title', 'Riwayat Pengajuan')

    @section('content')
    <div class="px-1 justify-center flex flex-col">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Data Pengajuan</h1>
            <div class="flex space-x-2">
                <a href="{{ route('pinjaman.data_pinjaman') }}"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                    <i class="fas fa-money-bill mr-2"></i>Data Pinjaman
                </a>
                <a href="{{ route('pinjaman.lunas') }}"
                    class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                    <i class="fas fa-check-circle mr-2"></i>Pinjaman Lunas
                </a>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4">Filter Data Pengajuan</h2>

            <form action="{{ route('pinjaman.data_pengajuan') }}" method="GET" id="filterForm">
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
                                        data-value="0">Menunggu Konfirmasi</button>
                                    <button type="button"
                                        class="w-full text-left px-2 py-1 text-sm hover:bg-gray-100 rounded"
                                        data-value="1">Disetujui</button>
                                    <button type="button"
                                        class="w-full text-left px-2 py-1 text-sm hover:bg-gray-100 rounded"
                                        data-value="2">Ditolak</button>
                                    <button type="button"
                                        class="w-full text-left px-2 py-1 text-sm hover:bg-gray-100 rounded"
                                        data-value="3">Sudah Terlaksana</button>
                                    <button type="button"
                                        class="w-full text-left px-2 py-1 text-sm hover:bg-gray-100 rounded"
                                        data-value="4">Batal</button>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="status" id="statusHidden" value="{{ request('status') }}">
                    </div>

                    <!-- Pencarian Anggota -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pencarian Anggota</label>
                        <input type="text" name="anggota" id="anggotaSearch" placeholder="Anggota"
                            value="{{ request('anggota') }}"
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
                        <button type="button" onclick="exportData()"
                            class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                            <i class="fas fa-print mr-2"></i>CETAK
                        </button>
                        <button type="button" onclick="resetFilter()"
                            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                            <i class="fas fa-refresh mr-2"></i>Reset
                        </button>
                    </div>


                </div>
            </form>
        </div>

        <!-- Tabel Transaksi -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-4 border-b flex items-center justify-between">
                <h2 class="text-lg font-semibold">Riwayat Transaksi</h2>
                @if (session('success'))
                <div class="text-green-700 bg-green-100 border border-green-300 rounded px-3 py-1 text-sm">
                    {{ session('success') }}</div>
                @endif
                @if (session('error'))
                <div class="text-red-700 bg-red-100 border border-red-300 rounded px-3 py-1 text-sm">
                    {{ session('error') }}</div>
                @endif
            </div>
            <div class="overflow-x-auto">
                <table class="w-full table-fixed border border-gray-200 text-[12px]">
                    <thead class="bg-gray-50 text-[12px] uppercase text-gray-600">
                        <tr class="w-full">
                            <th class="border text-center w-[90px]">ID Ajuan</th>
                            <th class="py-2 px-3 border text-left whitespace-nowrap w-[160px]">Anggota</th>
                            <th class="border text-center w-[90px]">Tanggal Pengajuan</th>
                            <th class="border text-center w-[50px]">Jenis</th>
                            <th class="py-2 px-3 border text-center w-[110px]">Jumlah</th>
                            <th class="py-2 px-3 border text-center whitespace-nowrap w-[46px]">Bln</th>
                            <th class="py-2 px-3 border text-left w-[120px]">Keterangan</th>
                            <th class="py-2 px-3 border text-center w-[120px]">Status</th>
                            <th class="py-2 px-3 border text-center w-[200px]">Sisa Pinjaman</th>
                            <th class="py-2 px-3 border text-center w-[160px]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($dataPengajuan as $Pengajuan)
                        <tr class="hover:bg-gray-50">
                            <td class="py-1 px-2 border font-medium text-gray-800 text-center align-middle">
                                <div class="truncate" title="{{ $Pengajuan->ajuan_id }}">{{ $Pengajuan->ajuan_id }}
                                </div>
                            </td>
                            <td class="py-1 px-2 border align-top">
                                @php
                                $namaAnggota = optional($Pengajuan->anggota)->nama;
                                @endphp
                                <div class="leading-tight">
                                    <div class="truncate hover:whitespace-normal" title="{{ $namaAnggota ?? '' }}">
                                        {{ $namaAnggota ?? '-' }}
                                    </div>
                                    <div class="text-[10px] text-gray-500">({{ $Pengajuan->anggota_id }})</div>
                                </div>
                            </td>
                            <td class="py-1 px-2 border align-top">
                                @php $tgl = \Carbon\Carbon::parse($Pengajuan->tgl_input); @endphp
                                <div class="leading-tight">
                                    <div class="truncate">{{ $tgl->format('d M') }}</div>
                                    <div class="text-[10px] text-gray-500">{{ $tgl->format('Y') }}</div>
                                </div>
                            </td>
                            <td class="py-1 px-2 border align-top">
                                @php
                                $jenisMap = [
                                '1' => 'Biasa',
                                '3' => 'Barang'
                                ];
                                $jenisText = $jenisMap[$Pengajuan->jenis] ?? $Pengajuan->jenis;
                                @endphp
                                {{ $jenisText }}
                            </td>
                            <td class="py-1 px-2 border text-right whitespace-nowrap align-top"
                                title="Rp {{ number_format($Pengajuan->nominal, 0, ',', '.') }}">
                                @if($Pengajuan->status == 0)
                                <div class="truncate max-w-[120px] cursor-pointer hover:bg-yellow-50 border-b border-dashed border-yellow-300 edit-inline"
                                    data-field="nominal" data-id="{{ $Pengajuan->id }}"
                                    data-value="{{ $Pengajuan->nominal }}" id="nominal_{{ $Pengajuan->id }}"
                                    title="Klik untuk edit">
                                    Rp {{ number_format($Pengajuan->nominal, 0, ',', '.') }}
                                </div>
                                @else
                                <div class="truncate max-w-[120px]"
                                    title="Rp {{ number_format($Pengajuan->nominal, 0, ',', '.') }}">
                                    Rp {{ number_format($Pengajuan->nominal, 0, ',', '.') }}
                                </div>
                                @endif
                            </td>
                            <td class="py-1 px-2 border text-center align-top">
                                @if($Pengajuan->status == 0)
                                <div class="cursor-pointer hover:bg-yellow-50 border-b border-dashed border-yellow-300 edit-inline"
                                    data-field="lama_ags" data-id="{{ $Pengajuan->id }}"
                                    data-value="{{ $Pengajuan->lama_ags }}" id="lama_ags_{{ $Pengajuan->id }}"
                                    title="Klik untuk edit">
                                    {{ $Pengajuan->lama_ags }}
                                </div>
                                @else
                                {{ $Pengajuan->lama_ags }}
                                @endif
                            </td>
                            <td class="py-1 px-2 border align-top">
                                @if($Pengajuan->status == 0)
                                <div class="whitespace-normal break-words max-w-[180px] md:max-w-[220px] cursor-pointer hover:bg-yellow-50 border-b border-dashed border-yellow-300 edit-inline"
                                    data-field="keterangan" data-id="{{ $Pengajuan->id }}"
                                    data-value="{{ $Pengajuan->keterangan }}" id="keterangan_{{ $Pengajuan->id }}"
                                    title="Klik untuk edit">
                                    {{ $Pengajuan->keterangan }}
                                </div>
                                @else
                                <div class="whitespace-normal break-words max-w-[180px] md:max-w-[220px]">
                                    {{ $Pengajuan->keterangan }}
                                </div>
                                @endif
                            </td>
                            <td class="py-1 px-2 border text-center align-top">
                                @php
                                $statusMap=[0=>['Menunggu Konfirmasi','bg-yellow-100 text-yellow-700
                                border-yellow-300'],
                                1=>['Disetujui','bg-green-100 text-green-700 border-green-300'],
                                2=>['Ditolak','bg-red-100 text-red-700 border-red-300'],
                                3=>['Terlaksana','bg-indigo-100 text-indigo-700 border-indigo-300'],
                                4=>['Batal','bg-gray-100 text-gray-700 border-gray-300']];
                                [$label,$cls] = $statusMap[$Pengajuan->status] ?? [$Pengajuan->status,'bg-gray-100
                                text-gray-700 border-gray-300'];
                                @endphp
                                <span
                                    class="px-1 py-0.5 text-[10px] rounded border truncate max-w-[110px] inline-block text-center {{ $cls }}"
                                    title="{{ $label }}">{{ $label }}</span>
                            </td>
                            <td class="py-1 px-2 border text-center align-top">
                                <div class="text-[10px] space-y-1">
                                    <div class="text-gray-600">Sisa Jml Pinjaman: <span
                                            class="font-medium">{{ $Pengajuan->sisa_pinjaman }}</span></div>
                                    <div class="text-gray-600">Sisa Jml Angsuran: <span
                                            class="font-medium">{{ $Pengajuan->sisa_angsuran }}</span></div>
                                    <div class="text-gray-600">Sisa Tagihan: <span class="font-medium">Rp
                                            {{ number_format($Pengajuan->sisa_tagihan, 0, ',', '.') }}</span></div>
                                </div>
                            </td>
                            <td class="py-1 px-2 border align-top">
                                <div class="grid grid-cols-3 gap-1">
                                    @if((int)$Pengajuan->status === 0)
                                    <button onclick="openApproveModal('{{ $Pengajuan->id }}')"
                                        class="w-full px-1.5 py-0.5 text-[10px] rounded border bg-green-50 text-green-700 border-green-300 hover:bg-green-100">Setujui</button>
                                    <form action="{{ route('pinjaman.data_pengajuan.reject', $Pengajuan->id) }}"
                                        method="POST">
                                        @csrf
                                        <input type="hidden" name="alasan" value="Ditolak oleh admin" />
                                        <button
                                            class="w-full px-1.5 py-0.5 text-[10px] rounded border bg-amber-50 text-amber-700 border-amber-300 hover:bg-amber-100">Tolak</button>
                                    </form>
                                    <form action="{{ route('pinjaman.data_pengajuan.cancel', $Pengajuan->id) }}"
                                        method="POST" onsubmit="return confirm('Batalkan pengajuan ini?')">
                                        @csrf
                                        <button
                                            class="w-full px-1.5 py-0.5 text-[10px] rounded border bg-red-50 text-red-700 border-red-300 hover:bg-red-100">Batal</button>
                                    </form>
                                    @endif

                                    @if((int)$Pengajuan->status === 1)
                                    <form action="{{ route('pinjaman.data_pengajuan.terlaksana', $Pengajuan->id) }}"
                                        method="POST" onsubmit="return confirm('Ubah status menjadi terlaksana?')">
                                        @csrf
                                        <button
                                            class="w-full px-1.5 py-0.5 text-[10px] rounded border bg-indigo-50 text-indigo-700 border-indigo-300 hover:bg-indigo-100">Terlaksana</button>
                                    </form>
                                    @endif
                                    <a class="w-full px-1.5 py-0.5 text-[10px] rounded border bg-purple-50 text-purple-700 border-purple-300 hover:bg-purple-100"
                                        target="_blank"
                                        href="{{ route('pinjaman.data_pengajuan.cetak', $Pengajuan->id) }}">Cetak</a>
                                    <form action="{{ route('pinjaman.data_pengajuan.destroy', $Pengajuan->id) }}"
                                        method="POST" onsubmit="return confirm('Hapus pengajuan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            class="w-full px-1.5 py-0.5 text-[10px] rounded border bg-gray-50 text-gray-700 border-gray-300 hover:bg-gray-100">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Pagination -->
        @if($dataPengajuan->hasPages())
        <div class="mt-6">
            {{ $dataPengajuan->links('vendor.pagination.simple') }}
        </div>
        @endif

    </div>

    <!-- Modal Approval -->
    <div id="approveModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Setujui Pengajuan Pinjaman</h3>
                <form id="approveForm" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="tgl_cair" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Cair</label>
                        <input type="date" id="tgl_cair" name="tgl_cair" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent"
                            value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="mb-4">
                        <label for="alasan" class="block text-sm font-medium text-gray-700 mb-2">Alasan
                            (Opsional)</label>
                        <textarea id="alasan" name="alasan" rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent"
                            placeholder="Alasan approval..."></textarea>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeApproveModal()"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors duration-200">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-[#14AE5C] text-white rounded-md hover:bg-[#0f8a4a] transition-colors duration-200">
                            Setujui
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    </div>

    <style>
.scroll-tbody {
    display: block;
    max-height: 400px;
    /* atur tinggi sesuai kebutuhan */
    overflow-x: auto;
    width: 100%;
}

.scroll-tbody tr {
    display: table;
    width: 100%;
    table-layout: fixed;
}

thead,
.scroll-tbody tr {
    width: 100%;
    table-layout: fixed;
}
    </style>

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
        const jenisMap = {
            '1': 'Biasa',
            '2': 'Barang',
            '3': 'Bank BSM'
        };
        const jenisText = jenisMap[jenis] || 'Semua Jenis';
        document.getElementById('jenisText').textContent = jenisText;
        document.getElementById('jenisHidden').value = jenis;
    }

    // Set status filter
    const status = urlParams.get('status');
    if (status !== null && status !== '') {
        const statusMap = {
            '0': 'Menunggu Konfirmasi',
            '1': 'Disetujui',
            '2': 'Ditolak',
            '3': 'Sudah Terlaksana',
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

function resetFilter() {
    // Reset all filter values
    document.getElementById('tanggalText').textContent = 'Semua Tanggal';
    document.getElementById('jenisText').textContent = 'Semua Jenis';
    document.getElementById('statusText').textContent = 'Semua Status';
    document.getElementById('anggotaSearch').value = '';
    document.getElementById('dateFrom').value = '';
    document.getElementById('dateTo').value = '';
    document.getElementById('jenisHidden').value = '';
    document.getElementById('statusHidden').value = '';

    // Redirect to base URL
    window.location.href = '{{ route("pinjaman.data_pengajuan") }}';
}

function exportData() {
    // Get current filter parameters
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);

    // Redirect to export route with current filters
    window.open(`{{ route('pinjaman.data_pengajuan') }}?${params.toString()}&export=pdf`, '_blank');
}

function openApproveModal(pengajuanId) {
    const modal = document.getElementById('approveModal');
    const form = document.getElementById('approveForm');

    // Set action URL
    form.action = `/pinjaman/data_pengajuan/${pengajuanId}/approve`;

    // Show modal
    modal.classList.remove('hidden');
}

function closeApproveModal() {
    const modal = document.getElementById('approveModal');
    modal.classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('approveModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeApproveModal();
    }
});

// Edit Inline Functionality
document.addEventListener('DOMContentLoaded', function() {
    // Add click event listeners for edit inline
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('edit-inline')) {
            e.preventDefault();
            const field = e.target.dataset.field;
            const id = e.target.dataset.id;
            const value = e.target.dataset.value;

            editInline(field, id, value);
        }
    });
});

function editInline(field, id, currentValue) {
    const element = document.getElementById(field + '_' + id);

    // Create input field
    let input;
    if (field === 'nominal') {
        input = document.createElement('input');
        input.type = 'number';
        input.value = currentValue;
        input.className =
            'w-full px-2 py-1 text-sm border border-blue-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500';
        input.placeholder = 'Masukkan nominal';
    } else if (field === 'lama_ags') {
        input = document.createElement('input');
        input.type = 'number';
        input.value = currentValue;
        input.className =
            'w-full px-2 py-1 text-sm border border-blue-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500';
        input.placeholder = 'Masukkan lama angsuran';
    } else if (field === 'keterangan') {
        input = document.createElement('textarea');
        input.value = currentValue;
        input.className =
            'w-full px-2 py-1 text-sm border border-blue-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500';
        input.placeholder = 'Masukkan keterangan';
        input.rows = 2;
    }

    // Replace content with input
    element.innerHTML = '';
    element.appendChild(input);
    input.focus();

    // Handle save on blur or enter
    function saveEdit() {
        const newValue = input.value.trim();

        if (newValue !== currentValue) {
            // Show loading
            element.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Menyimpan...</div>';

            // Send AJAX request
            fetch(`/pinjaman/data_pengajuan/${id}/update-field`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        field: field,
                        value: newValue
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update display
                        if (field === 'nominal') {
                            element.innerHTML = `Rp ${new Intl.NumberFormat('id-ID').format(newValue)}`;
                        } else if (field === 'lama_ags') {
                            element.innerHTML = newValue;
                        } else {
                            element.innerHTML = newValue;
                        }

                        // Update data attributes
                        element.dataset.value = newValue;

                        // Show success message
                        showNotification('Data berhasil diperbarui', 'success');
                    } else {
                        // Restore original value
                        restoreOriginalValue();
                        showNotification('Gagal memperbarui data: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    restoreOriginalValue();
                    showNotification('Terjadi kesalahan saat memperbarui data', 'error');
                });
        } else {
            // No change, restore original display
            restoreOriginalValue();
        }
    }

    function restoreOriginalValue() {
        if (field === 'nominal') {
            element.innerHTML = `Rp ${new Intl.NumberFormat('id-ID').format(currentValue)}`;
        } else if (field === 'lama_ags') {
            element.innerHTML = currentValue;
        } else {
            element.innerHTML = currentValue;
        }
    }

    // Event listeners
    input.addEventListener('blur', saveEdit);
    input.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            saveEdit();
        }
    });
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            e.preventDefault();
            restoreOriginalValue();
        }
    });
}

function showNotification(message, type) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 px-4 py-2 rounded-lg text-white ${
        type === 'success' ? 'bg-green-500' : 'bg-red-500'
    }`;
    notification.textContent = message;

    // Add to page
    document.body.appendChild(notification);

    // Remove after 3 seconds
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Edit Inline Functionality
document.addEventListener('DOMContentLoaded', function() {
    // Add click event listeners for edit inline
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('edit-inline')) {
            e.preventDefault();
            const field = e.target.dataset.field;
            const id = e.target.dataset.id;
            const value = e.target.dataset.value;

            editInline(field, id, value);
        }
    });
});

function editInline(field, id, currentValue) {
    const element = document.getElementById(field + '_' + id);

    // Create input field
    let input;
    if (field === 'nominal') {
        input = document.createElement('input');
        input.type = 'number';
        input.value = currentValue;
        input.className =
            'w-full px-2 py-1 text-sm border border-blue-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500';
        input.placeholder = 'Masukkan nominal';
    } else if (field === 'lama_ags') {
        input = document.createElement('input');
        input.type = 'number';
        input.value = currentValue;
        input.className =
            'w-full px-2 py-1 text-sm border border-blue-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500';
        input.placeholder = 'Masukkan lama angsuran';
    } else if (field === 'keterangan') {
        input = document.createElement('textarea');
        input.value = currentValue;
        input.className =
            'w-full px-2 py-1 text-sm border border-blue-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500';
        input.placeholder = 'Masukkan keterangan';
        input.rows = 2;
    }

    // Replace content with input
    element.innerHTML = '';
    element.appendChild(input);
    input.focus();

    // Handle save on blur or enter
    function saveEdit() {
        const newValue = input.value.trim();

        if (newValue !== currentValue) {
            // Show loading
            element.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Menyimpan...</div>';

            // Send AJAX request
            fetch(`/pinjaman/data_pengajuan/${id}/update-field`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        field: field,
                        value: newValue
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update display
                        if (field === 'nominal') {
                            element.innerHTML = `Rp ${new Intl.NumberFormat('id-ID').format(newValue)}`;
                        } else if (field === 'lama_ags') {
                            element.innerHTML = newValue;
                        } else {
                            element.innerHTML = newValue;
                        }

                        // Update data attributes
                        element.dataset.value = newValue;

                        // Show success message
                        showNotification('Data berhasil diperbarui', 'success');
                    } else {
                        // Restore original value
                        restoreOriginalValue();
                        showNotification('Gagal memperbarui data: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    restoreOriginalValue();
                    showNotification('Terjadi kesalahan saat memperbarui data', 'error');
                });
        } else {
            // No change, restore original display
            restoreOriginalValue();
        }
    }

    function restoreOriginalValue() {
        if (field === 'nominal') {
            element.innerHTML = `Rp ${new Intl.NumberFormat('id-ID').format(currentValue)}`;
        } else if (field === 'lama_ags') {
            element.innerHTML = currentValue;
        } else {
            element.innerHTML = currentValue;
        }
    }

    // Event listeners
    input.addEventListener('blur', saveEdit);
    input.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            saveEdit();
        }
    });
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            e.preventDefault();
            restoreOriginalValue();
        }
    });
}

function showNotification(message, type) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 px-4 py-2 rounded-lg text-white ${
        type === 'success' ? 'bg-green-500' : 'bg-red-500'
    }`;
    notification.textContent = message;

    // Add to page
    document.body.appendChild(notification);

    // Remove after 3 seconds
    setTimeout(() => {
        notification.remove();
    }, 3000);
}
    </script>
    @endsection