@extends('layouts.app')

@section('title', 'Pemasukan Angkutan')
@section('sub-title', 'Data Pemasukan Angkutan Karyawan')

@section('content')
<div class="px-1 justify-center flex flex-col">

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-money-bill-wave text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Pemasukan</p>
                    <p class="text-2xl font-semibold text-gray-900">
                        Rp{{ number_format($totalPemasukan, 0, ',', '.') }}
                    </p>
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
                    <p class="text-2xl font-semibold text-gray-900">{{ $transaksi->total() }}</p>
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
                    <p class="text-2xl font-semibold text-gray-900">{{ date('M Y') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Simple Filter Section -->
    <div class="bg-white rounded-lg shadow-md p-3 mb-4">
        <div class="flex items-center justify-between mb-2">
            <h3 class="text-base font-semibold text-gray-800">Filter Pemasukan Angkutan</h3>
        </div>

        <form method="GET" action="{{ route('angkutan.pemasukan') }}" id="filterForm">
            <!-- Simple Filter Bar -->
            <div class="flex flex-wrap items-center justify-between gap-2 py-2 px-2 bg-gray-50 rounded-lg">
                <!-- Left Side: Filter Controls -->
                <div class="flex items-center space-x-3">
                    <!-- 1. Tanggal -->
                    <div class="flex items-center space-x-2">
                        <label class="text-sm font-medium text-gray-700">Tanggal:</label>
                        <button type="button" id="daterange-btn"
                            class="px-3 py-1.5 border border-gray-300 rounded-lg bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-500 text-sm">
                            <i class="fas fa-calendar mr-1"></i>
                            <span id="daterange-text">Pilih Tanggal</span>
                            <i class="fas fa-chevron-down ml-1"></i>
                        </button>
                        <!-- Hidden inputs untuk form submission -->
                        <input type="hidden" name="tgl_dari" id="tgl_dari" value="{{ request('tgl_dari') }}">
                        <input type="hidden" name="tgl_sampai" id="tgl_sampai" value="{{ request('tgl_sampai') }}">
                    </div>

                    <!-- 2. Search Kode Transaksi -->
                    <div class="flex items-center space-x-2">
                        <label class="text-sm font-medium text-gray-700">Cari:</label>
                        <input type="text" name="kode_transaksi" id="kode_transaksi"
                            value="{{ request('kode_transaksi') }}" placeholder="[PA00001]"
                            class="px-2 py-1.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 text-sm w-36"
                            onkeypress="if(event.key==='Enter'){doSearch();}">
                    </div>

                    <!-- 3. Filter Kas -->
                    <div class="flex items-center space-x-2">
                        <label class="text-sm font-medium text-gray-700">Kas:</label>
                        <select name="kas_filter" id="kas_filter"
                            class="px-2 py-1.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 text-sm w-32">
                            <option value="">Semua Kas</option>
                            @foreach($kas as $k)
                            <option value="{{ $k->id }}" {{ request('kas_filter') == $k->id ? 'selected' : '' }}>
                                {{ $k->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 4. Button Filter -->
                    <button type="button" onclick="doSearch()" id="searchBtn"
                        class="px-3 py-1.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm">
                        <i class="fas fa-search mr-1"></i>Cari
                    </button>
                </div>

                <!-- Right Side: Action Buttons -->
                <div class="flex items-center space-x-2">
                    <!-- 5. Button Cetak Laporan -->
                    <button type="button" onclick="cetakLaporan()"
                        class="px-3 py-1.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm">
                        <i class="fas fa-print mr-1"></i>Cetak Laporan
                    </button>



                    <!-- 7. Button Hapus Filter -->
                    <button type="button" onclick="clearFilters()"
                        class="px-3 py-1.5 bg-red-600 text-white rounded-lg hover:bg-gray-700 transition-colors text-sm">
                        <i class="fas fa-times mr-1"></i>Hapus Filter
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="flex justify-between items-center mb-2 p-4">
            <h2 class="text-lg font-semibold text-gray-800">Data Pemasukan Angkutan</h2>
            <div class="flex space-x-3">
                <button onclick="openModal('addModal')"
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                    <i class="fas fa-plus"></i>
                    <span>Tambah</span>
                </button>
                <button onclick="editData()"
                    class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                    <i class="fas fa-edit"></i>
                    <span>Edit</span>
                </button>
                <button onclick="deleteData()"
                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                    <i class="fas fa-trash"></i>
                    <span>Hapus</span>
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="table-auto w-full border border-gray-300 text-center">
                <thead class="bg-gray-100">
                    <tr class="text-sm">
                        <th class="py-3 border px-4">No</th>
                        <th class="py-3 border px-4">No. Polisi</th>
                        <th class="py-3 border px-4">Kode Transaksi</th>
                        <th class="py-3 border px-4">Tanggal Transaksi</th>
                        <th class="py-3 border px-4">Uraian</th>
                        <th class="py-3 border px-4">Untuk Kas</th>
                        <th class="py-3 border px-4">Dari Akun</th>
                        <th class="py-3 border px-4">Jumlah</th>
                        <th class="py-3 border px-4">User</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transaksi as $index => $tr)
                    <tr class="text-sm align-middle hover:bg-gray-50 cursor-pointer row-selectable"
                        data-id="{{ $tr->id }}" data-kode="PA{{ str_pad($tr->id, 5, '0', STR_PAD_LEFT) }}"
                        data-tanggal="{{ $tr->tgl_catat }}" data-keterangan="{{ $tr->keterangan }}"
                        data-untuk-kas-id="{{ $tr->untuk_kas_id }}"
                        data-untuk-kas-nama="{{ optional($tr->untukKas)->nama ?? '-' }}"
                        data-dari-akun-id="{{ $tr->dari_akun_id ?? '46' }}" data-jumlah="{{ $tr->jumlah }}"
                        data-user="{{ $tr->user_name }}" data-no-polisi="{{ $tr->no_polisi ?? '-' }}">
                        <td class="py-3 border px-4">
                            {{ ($transaksi->currentPage() - 1) * $transaksi->perPage() + $loop->iteration }}
                        </td>
                        <td class="py-3 border px-4 font-medium">
                            {{ $tr->no_polisi ?? '-' }}
                        </td>
                        <td class="py-3 border px-4">
                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-medium">
                                PA{{ str_pad($tr->id, 5, '0', STR_PAD_LEFT) }}
                            </span>
                        </td>
                        <td class="py-3 border px-4">
                            {{ $tr->tgl_catat ? \Carbon\Carbon::parse($tr->tgl_catat)->format('d F Y - H:i') : '-' }}
                        </td>
                        <td class="py-3 border px-4 text-left">{{ $tr->keterangan ?? '-' }}</td>
                        <td class="py-3 border px-4">{{ optional($tr->untukKas)->nama ?? '-' }}</td>
                        <td class="py-3 border px-4">Pendapatan Jasa Sewa Bus</td>
                        <td class="py-3 border px-4 font-semibold text-green-600">
                            {{ number_format($tr->jumlah ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="py-3 border px-4">{{ $tr->user_name ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-2"></i>
                            <p>Tidak ada data pemasukan angkutan</p>
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
            <div
                class="bg-white px-4 py-1 flex flex-row rounded-full justify-center items-center space-x-2 border border-gray-300 shadow-sm">
                @for ($i = 1; $i <= $transaksi->lastPage(); $i++)
                    @if ($i == 1 || $i == $transaksi->lastPage() || ($i >= $transaksi->currentPage() - 1 && $i <=
                        $transaksi->currentPage() + 1))
                        <a href="{{ $transaksi->url($i) }}">
                            <div
                                class="rounded-md px-2 py-0.5 text-sm border border-gray-300 {{ $transaksi->currentPage() == $i ? 'bg-gray-100 font-bold' : '' }}">
                                {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                            </div>
                        </a>
                        @elseif ($i == 2 || $i == $transaksi->lastPage() - 1)
                        <div class="rounded-md px-2 py-0.5 text-sm">...</div>
                        @endif
                        @endfor
            </div>
        </div>

        <div class="absolute right-4 top-1/2 -translate-y-1/2 whitespace-nowrap text-sm text-gray-400">
            Displaying {{ $transaksi->firstItem() }} to {{ $transaksi->lastItem() }} of {{ $transaksi->total() }} items
        </div>
    </div>
</div>

<!-- Add Modal -->
<div id="addModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
            <div class="flex items-center justify-between p-4 border-b">
                <h3 class="text-lg font-semibold">Tambah Pemasukan Angkutan</h3>
                <button onclick="closeModal('addModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="addForm" class="p-4">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Polisi</label>
                        <input type="text" name="no_polisi" placeholder="B 1234 ABC"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Transaksi</label>
                        <input type="datetime-local" name="tgl_catat" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah</label>
                        <input type="text" name="jumlah" id="jumlah" required placeholder="Masukkan jumlah"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                            oninput="formatNumber(this)" onblur="validateNumber(this)" pattern="[0-9,.]*"
                            inputmode="numeric">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                        <textarea name="keterangan" rows="3" placeholder="Masukkan keterangan transaksi"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Dari Akun</label>
                        <select name="dari_akun_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="">-- Pilih Akun --</option>
                            <option value="46" selected>Pendapatan Jasa Sewa Bus</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Untuk Kas</label>
                        <select name="untuk_kas_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="">-- Pilih Kas --</option>
                            @foreach($kas as $item)
                            <option value="{{ $item->id }}">{{ $item->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeModal('addModal')"
                        class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 flex items-center space-x-2">
                        <i class="fas fa-times"></i>
                        <span>Batal</span>
                    </button>
                    <button type="submit"
                        class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center space-x-2">
                        <i class="fas fa-check"></i>
                        <span>Simpan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
            <div class="flex items-center justify-between p-4 border-b">
                <h3 class="text-lg font-semibold">Edit Pemasukan Angkutan</h3>
                <button onclick="closeModal('editModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="editForm" class="p-4">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Polisi</label>
                        <input type="text" name="no_polisi" id="edit_no_polisi" placeholder="B 1234 ABC"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Transaksi</label>
                        <input type="datetime-local" name="tgl_catat" id="edit_tgl_catat" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah</label>
                        <input type="text" name="jumlah" id="edit_jumlah" required placeholder="Masukkan jumlah"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                            oninput="formatNumber(this)" onblur="validateNumber(this)" pattern="[0-9,.]*"
                            inputmode="numeric">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                        <textarea name="keterangan" id="edit_keterangan" rows="3"
                            placeholder="Masukkan keterangan transaksi"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Dari Akun</label>
                        <select name="dari_akun_id" id="edit_dari_akun_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="">-- Pilih Akun --</option>
                            <option value="46">Pendapatan Jasa Sewa Bus</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Untuk Kas</label>
                        <select name="untuk_kas_id" id="edit_untuk_kas_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="">-- Pilih Kas --</option>
                            @foreach($kas as $item)
                            <option value="{{ $item->id }}">{{ $item->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeModal('editModal')"
                        class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 flex items-center space-x-2">
                        <i class="fas fa-times"></i>
                        <span>Batal</span>
                    </button>
                    <button type="submit"
                        class="px-6 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 flex items-center space-x-2">
                        <i class="fas fa-edit"></i>
                        <span>Update</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Include required libraries -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<script>
// Simple Filter System
$(document).ready(function() {
    // Initialize Date Range Picker
    initializeDateRangePicker();

    // Add click event listener for row selection
    $(document).on('click', '.row-selectable', function() {
        selectRow(this, $(this).data('id'));
    });
});

// Initialize Date Range Picker with Preset Ranges
function initializeDateRangePicker() {
    $('#daterange-btn').daterangepicker({
        ranges: {
            'Hari ini': [moment(), moment()],
            'Kemarin': [moment().subtract('days', 1), moment().subtract('days', 1)],
            '7 Hari yang lalu': [moment().subtract('days', 6), moment()],
            '30 Hari yang lalu': [moment().subtract('days', 29), moment()],
            'Bulan ini': [moment().startOf('month'), moment().endOf('month')],
            'Bulan kemarin': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1)
                .endOf('month')
            ],
            'Tahun ini': [moment().startOf('year'), moment().endOf('year')],
            'Tahun kemarin': [moment().subtract('year', 1).startOf('year'), moment().subtract('year', 1).endOf(
                'year')]
        },
        showDropdowns: true,
        format: 'YYYY-MM-DD',
        startDate: moment().startOf('year'),
        endDate: moment().endOf('year'),
        autoApply: true,
        locale: {
            format: 'DD/MM/YYYY',
            separator: ' - ',
            applyLabel: 'Terapkan',
            cancelLabel: 'Batal',
            fromLabel: 'Dari',
            toLabel: 'Sampai',
            customRangeLabel: 'Pilih Manual',
            weekLabel: 'W',
            daysOfWeek: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
            monthNames: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ],
            firstDay: 1
        }
    }, function(start, end, label) {
        // Update hidden inputs
        $('#tgl_dari').val(start.format('YYYY-MM-DD'));
        $('#tgl_sampai').val(end.format('YYYY-MM-DD'));

        // Update display text
        $('#daterange-text').text(start.format('DD/MM/YYYY') + ' - ' + end.format('DD/MM/YYYY'));
    });

    // Set initial values if they exist in URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('tgl_dari') && urlParams.has('tgl_sampai')) {
        const tglDari = urlParams.get('tgl_dari');
        const tglSampai = urlParams.get('tgl_sampai');
        $('#tgl_dari').val(tglDari);
        $('#tgl_sampai').val(tglSampai);
        $('#daterange-text').text(moment(tglDari).format('DD/MM/YYYY') + ' - ' + moment(tglSampai).format(
            'DD/MM/YYYY'));
    }
}

// Main Search Function
function doSearch() {
    const form = document.getElementById("filterForm");
    const formData = new FormData(form);

    // Build query parameters
    const params = new URLSearchParams();

    // Kode Transaksi
    const kodeTransaksi = formData.get('kode_transaksi');
    if (kodeTransaksi && kodeTransaksi.trim() !== '') {
        let cleanCode = kodeTransaksi.replace(/PA/gi, '').replace(/^0+/, '');
        params.append('kode_transaksi', cleanCode);
    }

    // Date Range
    const tglDari = formData.get('tgl_dari');
    const tglSampai = formData.get('tgl_sampai');
    if (tglDari && tglSampai) {
        params.append('tgl_dari', tglDari);
        params.append('tgl_sampai', tglSampai);
    }

    // Kas Filter
    const kasFilter = formData.get('kas_filter');
    if (kasFilter && kasFilter !== '') {
        params.append('kas_filter', kasFilter);
    }

    // Redirect with parameters
    window.location.href = "{{ route('angkutan.pemasukan') }}?" + params.toString();
}

// Clear all filters
function clearFilters() {
    // Reset date range picker
    $('#daterange-text').text('Pilih Tanggal');
    $('#tgl_dari').val('');
    $('#tgl_sampai').val('');

    // Reset kode transaksi
    $('#kode_transaksi').val('');

    // Reset kas filter
    $('#kas_filter').val('');

    // Reload page
    window.location.href = "{{ route('angkutan.pemasukan') }}";
}

// Reset all filters
function resetAllFilters() {
    if (confirm('Apakah Anda yakin ingin mereset semua filter?')) {
        window.location.href = "{{ route('angkutan.pemasukan') }}";
    }
}

// Cetak Laporan PDF
function cetakLaporan() {
    const form = document.getElementById("filterForm");
    const formData = new FormData(form);

    const params = new URLSearchParams();

    const kodeTransaksi = formData.get('kode_transaksi');
    if (kodeTransaksi && kodeTransaksi.trim() !== '') {
        let cleanCode = kodeTransaksi.replace(/PA/gi, '').replace(/^0+/, '');
        params.append('kode_transaksi', cleanCode);
    }

    const tglDari = formData.get('tgl_dari');
    const tglSampai = formData.get('tgl_sampai');
    if (tglDari && tglSampai) {
        params.append('tgl_dari', tglDari);
        params.append('tgl_sampai', tglSampai);
    }

    const kasFilter = formData.get('kas_filter');
    if (kasFilter && kasFilter !== '') {
        params.append('kas_filter', kasFilter);
    }

    // Redirect ke route export PDF
    window.open("{{ route('angkutan.export.pdf.pemasukan') }}?" + params.toString(), '_blank');
}



// Modal functions
function openModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');

    // Set default values untuk form add
    if (modalId === 'addModal') {
        // Set tanggal sekarang sebagai default
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');

        const datetimeString = `${year}-${month}-${day}T${hours}:${minutes}`;
        document.querySelector('#addModal input[name="tgl_catat"]').value = datetimeString;

        // Reset form
        document.getElementById('addForm').reset();
        document.querySelector('#addModal input[name="tgl_catat"]').value = datetimeString;
    }
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

// Global variable untuk menyimpan data row yang dipilih
let selectedRowData = null;

// Function untuk select row (click to edit)
function selectRow(row, id) {
    // Remove highlight dari semua row
    document.querySelectorAll('tbody tr').forEach(r => {
        r.classList.remove('bg-yellow-100', 'border-yellow-300');
        r.classList.add('hover:bg-gray-50');
    });

    // Add highlight ke row yang dipilih
    row.classList.remove('hover:bg-gray-50');
    row.classList.add('bg-yellow-100', 'border-yellow-300');

    // Simpan data row yang dipilih
    selectedRowData = {
        id: row.dataset.id,
        kode: row.dataset.kode,
        tanggal: row.dataset.tanggal,
        keterangan: row.dataset.keterangan,
        untuk_kas_id: row.dataset.untukKasId,
        untuk_kas_nama: row.dataset.untukKasNama,
        dari_akun_id: row.dataset.dariAkunId,
        jumlah: row.dataset.jumlah,
        user: row.dataset.user,
        no_polisi: row.dataset.noPolisi
    };
}

// CRUD functions
function editData() {
    if (!selectedRowData) {
        alert('Pilih data yang akan diedit terlebih dahulu!');
        return;
    }

    // Buka modal edit dengan data terisi
    openModal('editModal');

    // Populate form dengan data yang dipilih
    // Format tanggal untuk input datetime-local
    const tanggal = new Date(selectedRowData.tanggal);
    const formattedDate = tanggal.toISOString().slice(0, 16);

    document.getElementById('edit_tgl_catat').value = formattedDate;
    document.getElementById('edit_jumlah').value = parseInt(selectedRowData.jumlah).toLocaleString('id-ID');
    document.getElementById('edit_keterangan').value = selectedRowData.keterangan;
    document.getElementById('edit_dari_akun_id').value = selectedRowData.dari_akun_id || '46';
    document.getElementById('edit_untuk_kas_id').value = selectedRowData.untuk_kas_id;
    document.getElementById('edit_no_polisi').value = selectedRowData.no_polisi;
}

function deleteData() {
    if (!selectedRowData) {
        alert('Pilih data yang akan dihapus terlebih dahulu!');
        return;
    }

    if (confirm(`Apakah Anda yakin ingin menghapus data kode transaksi: ${selectedRowData.kode}?`)) {
        // Kirim request delete
        const deleteUrl = `{{ url('toserda/angkutan/pemasukan') }}/${selectedRowData.id}`;
        fetch(deleteUrl, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert('Data berhasil dihapus');
                    location.reload();
                } else {
                    alert('Gagal menghapus data: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menghapus data: ' + error.message);
            });
    }
}

// Enhanced form validation function
function validateForm(formData) {
    const errors = [];

    // Validate tanggal
    if (!formData.tgl_catat) {
        errors.push('Tanggal Transaksi harus diisi');
    }

    // Validate jumlah
    const jumlah = parseInt(formData.jumlah);
    if (!formData.jumlah || isNaN(jumlah) || jumlah <= 0) {
        errors.push('Jumlah harus berupa angka yang valid dan lebih dari 0');
    }

    // Validate no polisi
    if (!formData.no_polisi || formData.no_polisi.trim() === '') {
        errors.push('Nomor Polisi harus diisi');
    }

    // Validate dari akun
    if (!formData.dari_akun_id || formData.dari_akun_id === '') {
        errors.push('Dari Akun harus dipilih');
    }

    // Validate untuk kas
    if (!formData.untuk_kas_id || formData.untuk_kas_id === '') {
        errors.push('Untuk Kas harus dipilih');
    }

    return errors;
}

// Enhanced form submission dengan validasi lengkap dan async/await
document.getElementById('addForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    // Validasi form
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);

    // Convert formatted number to raw number
    const jumlahInput = document.getElementById('jumlah');
    data.jumlah = getRawNumber(jumlahInput);

    // Enhanced validation
    const validationErrors = validateForm(data);
    if (validationErrors.length > 0) {
        alert('Error:\n' + validationErrors.join('\n'));
        return;
    }

    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Menyimpan...';

    try {
        const response = await fetch("{{ route('angkutan.store.pemasukan') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                    'content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify(data)
        });

        // Enhanced response handling
        if (!response.ok) {
            let errorMessage = 'Network response was not ok';
            try {
                const errorData = await response.json();
                errorMessage = errorData.message || errorData.error || errorMessage;
            } catch (parseError) {
                console.error('Error parsing response:', parseError);
                errorMessage = `HTTP ${response.status}: ${response.statusText}`;
            }
            throw new Error(errorMessage);
        }

        const result = await response.json();

        if (result.success) {
            alert('Data berhasil disimpan!');
            closeModal('addModal');
            location.reload();
        } else {
            alert('Gagal menyimpan data: ' + (result.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menyimpan data: ' + error.message);
    } finally {
        // Reset button state
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});

// Edit form submission
document.getElementById('editForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    if (!selectedRowData) {
        alert('Tidak ada data yang dipilih untuk diedit');
        return;
    }

    const formData = new FormData(this);

    // Convert formatted number to raw number
    const jumlahInput = document.getElementById('edit_jumlah');
    const rawJumlah = getRawNumber(jumlahInput);
    formData.set('jumlah', rawJumlah);

    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Mengupdate...';

    try {
        const updateUrl = `{{ url('toserda/angkutan/pemasukan') }}/${selectedRowData.id}`;
        const response = await fetch(updateUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                    'content'),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(Object.fromEntries(formData))
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || 'Network response was not ok');
        }

        const result = await response.json();

        if (result.success) {
            alert('Data berhasil diupdate!');
            closeModal('editModal');
            location.reload();
        } else {
            alert('Gagal mengupdate data: ' + (result.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat mengupdate data: ' + error.message);
    } finally {
        // Reset button state
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl+Enter atau Cmd+Enter: Trigger search
    if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
        e.preventDefault();
        doSearch();
    }

    // Escape: Clear filters
    if (e.key === 'Escape') {
        e.preventDefault();
        clearFilters();
    }
});

// Enhanced number formatting functions
function formatNumber(input) {
    // Remove non-numeric characters except decimal point
    let value = input.value.replace(/[^0-9.]/g, '');

    // Remove multiple decimal points
    const parts = value.split('.');
    if (parts.length > 2) {
        value = parts[0] + '.' + parts.slice(1).join('');
    }

    // Format with thousand separators for display
    if (value && !isNaN(parseFloat(value))) {
        const number = parseFloat(value);
        if (number > 0) {
            input.value = number.toLocaleString('id-ID');
        } else {
            input.value = '';
        }
    } else if (value === '') {
        input.value = '';
    }
}

function validateNumber(input) {
    const rawValue = input.value.replace(/[^0-9.]/g, '');
    const number = parseFloat(rawValue);

    if (rawValue && (isNaN(number) || number <= 0)) {
        alert('Jumlah harus berupa angka yang valid dan lebih dari 0');
        input.focus();
        input.value = '';
        return false;
    }
    return true;
}

function getRawNumber(input) {
    // Return clean number without formatting
    const rawValue = input.value.replace(/[^0-9.]/g, '');
    return rawValue || '0';
}

// Auto-focus pada kode transaksi jika URL parameter ada
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('kode_transaksi')) {
        document.getElementById('kode_transaksi').focus();
    }
});
</script>
@endsection