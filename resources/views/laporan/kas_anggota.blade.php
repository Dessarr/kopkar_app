@extends('layouts.app')

@section('title', 'Laporan Kas Anggota')
@section('sub-title', 'Laporan')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-white bg-opacity-20">
                    <i class="fas fa-users text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm opacity-90">Total Anggota</p>
                    <p class="text-2xl font-bold">{{ number_format($totalAnggota) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-white bg-opacity-20">
                    <i class="fas fa-wallet text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm opacity-90">Total Saldo</p>
                    <p class="text-2xl font-bold text-green-200">
                        Rp {{ number_format($totalSaldo) }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Panel -->
    <div class="mb-6">
        <div class="bg-gray-50 p-4 rounded-lg">
            <div class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Cari</label>
                    <input type="text" id="search" name="search" value="{{ $search }}"
                        placeholder="Nama, No KTP, atau ID Anggota"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent">
                </div>
                <div class="flex gap-2">
                    <button type="button" onclick="submitFilter()"
                        class="px-4 py-2 bg-[#14AE5C] text-white rounded-md hover:bg-[#11994F] transition-colors duration-200">
                        <i class="fas fa-search mr-2"></i>Cari
                    </button>
                    <a href="{{ route('laporan.kas.anggota') }}"
                        class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors duration-200">
                        <i class="fas fa-refresh mr-2"></i>Hapus Filter
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Buttons -->
    <div class="mb-6 flex flex-wrap gap-2">
        <a href="{{ route('laporan.kas.anggota.export.detail') }}?search={{ $search }}"
            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors duration-200">
            <i class="fas fa-file-excel mr-2"></i>Download Detail
        </a>
        <a href="{{ route('laporan.kas.anggota.export.tagihan') }}?search={{ $search }}"
            class="px-4 py-2 bg-orange-600 text-white rounded-md hover:bg-orange-700 transition-colors duration-200">
            <i class="fas fa-file-excel mr-2"></i>Download Tagihan
        </a>
        <a href="{{ route('laporan.kas.anggota.export.simpanan') }}?search={{ $search }}"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-200">
            <i class="fas fa-file-excel mr-2"></i>Download Simpanan
        </a>
    </div>

    <!-- Main Title -->
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Laporan Data Kas Per Anggota</h1>
        @if($search)
        <div class="mt-2">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                <i class="fas fa-search mr-2"></i>
                Hasil pencarian untuk: "{{ $search }}"
                <a href="{{ route('laporan.kas.anggota') }}" class="ml-2 text-blue-600 hover:text-blue-800">
                    <i class="fas fa-times"></i>
                </a>
            </span>
        </div>
        @endif
    </div>

    <!-- Data Table -->
    <div class="mb-4">
        <div class="overflow-x-auto">
            <table class="w-full bg-white border border-gray-200 rounded-lg">
                <thead class="bg-gray-50">
                    <tr>
                        <th
                            class="px-4 py-3 text-center text-sm font-bold text-gray-700 uppercase tracking-wider border-r border-gray-200 w-16">
                            No</th>
                        <th
                            class="px-4 py-3 text-center text-sm font-bold text-gray-700 uppercase tracking-wider border-r border-gray-200 w-20">
                            Photo</th>
                        <th
                            class="px-4 py-3 text-center text-sm font-bold text-gray-700 uppercase tracking-wider border-r border-gray-200 w-64">
                            Identitas</th>
                        <th
                            class="px-4 py-3 text-center text-sm font-bold text-gray-700 uppercase tracking-wider border-r border-gray-200 w-64">
                            Saldo Simpanan</th>
                        <th
                            class="px-4 py-3 text-center text-sm font-bold text-gray-700 uppercase tracking-wider border-r border-gray-200 w-64">
                            Tagihan Kredit</th>
                        <th class="px-4 py-3 text-center text-sm font-bold text-gray-700 uppercase tracking-wider w-64">
                            Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($dataAnggota as $index => $anggota)
                    @php
                    $anggotaInfo = $anggotaData[$anggota->no_ktp] ?? null;
                    $identitas = $anggotaInfo['identitas'] ?? [];
                    $saldoSimpanan = $anggotaInfo['saldo_simpanan'] ?? [];
                    $tagihanKredit = $anggotaInfo['tagihan_kredit'] ?? [];
                    $keterangan = $anggotaInfo['keterangan'] ?? [];
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <!-- No -->
                        <td class="px-4 py-3 text-center text-sm text-gray-900 border-r border-gray-200">
                            {{ ($dataAnggota->currentPage() - 1) * $dataAnggota->perPage() + $loop->iteration }}
                        </td>

                        <!-- Photo -->
                        <td class="px-4 py-3 text-center border-r border-gray-200">
                            @if($anggota->file_pic && \Storage::disk('public')->exists('anggota/' . $anggota->file_pic))
                            <img src="{{ asset('storage/anggota/' . $anggota->file_pic) }}"
                                alt="Foto {{ $anggota->nama }}"
                                class="w-12 h-12 rounded-full mx-auto object-cover border-2 border-gray-300">
                            @else
                            <div
                                class="w-12 h-12 rounded-full bg-gray-100 mx-auto flex items-center justify-center border-2 border-gray-300">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                                </svg>
                            </div>
                            @endif
                        </td>

                        <!-- Identitas -->
                        <td class="px-4 py-3 border-r border-gray-200">
                            <table class="w-full">
                                <tr>
                                    <td class="text-xs text-gray-600 py-1">ID Anggota:</td>
                                    <td class="text-xs font-semibold text-right">{{ $identitas['id_anggota'] ?? '-' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-xs text-gray-600 py-1">Nama:</td>
                                    <td class="text-xs font-semibold text-right">{{ $identitas['nama'] ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-xs text-gray-600 py-1">Jenis Kelamin:</td>
                                    <td class="text-xs font-semibold text-right">
                                        {{ $identitas['jenis_kelamin'] ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-xs text-gray-600 py-1">Alamat:</td>
                                    <td class="text-xs font-semibold text-right">{{ $identitas['alamat'] ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-xs text-gray-600 py-1">Telp:</td>
                                    <td class="text-xs font-semibold text-right">{{ $identitas['telp'] ?? '-' }}</td>
                                </tr>
                            </table>
                        </td>

                        <!-- Saldo Simpanan -->
                        <td class="px-4 py-3 border-r border-gray-200">
                            <table class="w-full">
                                <tr>
                                    <td class="text-xs text-gray-600 py-1">Simpanan Wajib:</td>
                                    <td class="text-xs font-semibold text-right">
                                        {{ number_format($saldoSimpanan->simpanan_wajib ?? 0, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-xs text-gray-600 py-1">Simpanan Sukarela:</td>
                                    <td class="text-xs font-semibold text-right">
                                        {{ number_format($saldoSimpanan->simpanan_sukarela ?? 0, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-xs text-gray-600 py-1">Simpanan Khusus II:</td>
                                    <td class="text-xs font-semibold text-right">
                                        {{ number_format($saldoSimpanan->simpanan_khusus_2 ?? 0, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-xs text-gray-600 py-1">Simpanan Pokok:</td>
                                    <td class="text-xs font-semibold text-right">
                                        {{ number_format($saldoSimpanan->simpanan_pokok ?? 0, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-xs text-gray-600 py-1">Simpanan Khusus I:</td>
                                    <td class="text-xs font-semibold text-right">
                                        {{ number_format($saldoSimpanan->simpanan_khusus_1 ?? 0, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-xs text-gray-600 py-1">Tab. Perumahan:</td>
                                    <td class="text-xs font-semibold text-right">
                                        {{ number_format($saldoSimpanan->tab_perumahan ?? 0, 0, ',', '.') }}</td>
                                </tr>
                                <tr class="border-t border-gray-300">
                                    <td class="text-xs font-bold text-gray-800 py-1">Jumlah:</td>
                                    <td class="text-xs font-bold text-right text-blue-600">
                                        {{ number_format(($saldoSimpanan->simpanan_wajib ?? 0) + ($saldoSimpanan->simpanan_sukarela ?? 0) + ($saldoSimpanan->simpanan_khusus_2 ?? 0) + ($saldoSimpanan->simpanan_pokok ?? 0) + ($saldoSimpanan->simpanan_khusus_1 ?? 0) + ($saldoSimpanan->tab_perumahan ?? 0), 0, ',', '.') }}
                                    </td>
                                </tr>
                            </table>
                        </td>

                        <!-- Tagihan Kredit -->
                        <td class="px-4 py-3 border-r border-gray-200">
                            <table class="w-full">
                                <tr>
                                    <td class="text-xs text-gray-600 py-1">Pinjaman Biasa:</td>
                                    <td class="text-xs font-semibold text-right">
                                        {{ number_format($tagihanKredit->pinjaman_biasa ?? 0, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-xs text-gray-600 py-1">Sisa Pinjaman:</td>
                                    <td class="text-xs font-semibold text-right">
                                        {{ number_format($tagihanKredit->sisa_pinjaman_biasa ?? 0, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-xs text-gray-600 py-1">Pinjaman Barang:</td>
                                    <td class="text-xs font-semibold text-right">
                                        {{ number_format($tagihanKredit->pinjaman_barang ?? 0, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-xs text-gray-600 py-1">Sisa Pinjaman:</td>
                                    <td class="text-xs font-semibold text-right">
                                        {{ number_format($tagihanKredit->sisa_pinjaman_barang ?? 0, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-xs text-gray-600 py-1">Tagihan Takterbayar:</td>
                                    <td class="text-xs font-semibold text-right">{{ number_format(0, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </table>
                        </td>

                        <!-- Keterangan -->
                        <td class="px-4 py-3">
                            <table class="w-full">
                                <tr>
                                    <td class="text-xs text-gray-600 py-1">Jumlah Pinjaman:</td>
                                    <td class="text-xs font-semibold text-right">{{ $keterangan->jumlah_pinjaman ?? 0 }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-xs text-gray-600 py-1">Pinjaman Lunas:</td>
                                    <td class="text-xs font-semibold text-right">{{ $keterangan->pinjaman_lunas ?? 0 }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-xs text-gray-600 py-1">Pembayaran:</td>
                                    <td class="text-xs font-semibold text-right">
                                        <span
                                            class="px-2 py-1 text-xs rounded border {{ ($keterangan->status_pembayaran ?? 'Lancar') == 'Lancar' ? 'text-green-500 border-green-500' : 'text-red-500 border-red-500' }}">
                                            {{ $keterangan->status_pembayaran ?? 'Lancar' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-xs text-gray-600 py-1">Tanggal Tempo:</td>
                                    <td class="text-xs font-semibold text-right">{{ $keterangan->tanggal_tempo ?? '-' }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-2"></i>
                            <p>Tidak ada data kas anggota untuk periode yang dipilih</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($dataAnggota->hasPages())
    <div class="mt-6 flex justify-center">
        <nav class="flex items-center space-x-1">
            <!-- First Page Link -->
            @if ($dataAnggota->currentPage() > 3)
            <a href="{{ $dataAnggota->appends(request()->query())->url(1) }}"
                class="px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                1
            </a>
            @if ($dataAnggota->currentPage() > 4)
            <span class="px-3 py-2 text-sm text-gray-500">...</span>
            @endif
            @endif

            <!-- Previous Page Link -->
            @if ($dataAnggota->onFirstPage())
            <span class="px-3 py-2 text-sm text-gray-400 bg-gray-100 rounded-md cursor-not-allowed">
                <i class="fas fa-chevron-left"></i>
            </span>
            @else
            <a href="{{ $dataAnggota->appends(request()->query())->previousPageUrl() }}"
                class="px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                <i class="fas fa-chevron-left"></i>
            </a>
            @endif

            <!-- Page Numbers (show 5 pages around current page) -->
            @php
            $start = max(1, $dataAnggota->currentPage() - 2);
            $end = min($dataAnggota->lastPage(), $dataAnggota->currentPage() + 2);
            @endphp

            @for ($page = $start; $page <= $end; $page++) @if ($page==$dataAnggota->currentPage())
                <span class="px-3 py-2 text-sm text-white bg-blue-600 rounded-md font-medium">{{ $page }}</span>
                @else
                <a href="{{ $dataAnggota->appends(request()->query())->url($page) }}"
                    class="px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                    {{ $page }}
                </a>
                @endif
                @endfor

                <!-- Next Page Link -->
                @if ($dataAnggota->hasMorePages())
                <a href="{{ $dataAnggota->appends(request()->query())->nextPageUrl() }}"
                    class="px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                    <i class="fas fa-chevron-right"></i>
                </a>
                @else
                <span class="px-3 py-2 text-sm text-gray-400 bg-gray-100 rounded-md cursor-not-allowed">
                    <i class="fas fa-chevron-right"></i>
                </span>
                @endif

                <!-- Last Page Link -->
                @if ($dataAnggota->currentPage() < $dataAnggota->lastPage() - 2)
                    @if ($dataAnggota->currentPage() < $dataAnggota->lastPage() - 3)
                        <span class="px-3 py-2 text-sm text-gray-500">...</span>
                        @endif
                        <a href="{{ $dataAnggota->appends(request()->query())->url($dataAnggota->lastPage()) }}"
                            class="px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                            {{ $dataAnggota->lastPage() }}
                        </a>
                        @endif
        </nav>
    </div>

    <!-- Pagination Info -->
    <div class="mt-4 text-center text-sm text-gray-600">
        <span class="font-medium">Halaman {{ $dataAnggota->currentPage() }}</span> dari
        <span class="font-medium">{{ $dataAnggota->lastPage() }}</span>
        (Total {{ number_format($dataAnggota->total()) }} data)
    </div>
    @endif

    <!-- Summary Footer -->
    <div class="mt-6 p-4 bg-gray-50 rounded-lg">
        <div class="flex flex-wrap justify-between items-center text-sm text-gray-600">
            <div>
                <span class="font-medium">Total Data:</span> {{ number_format($dataAnggota->total()) }} anggota
            </div>
            <div>
                <span class="font-medium">Halaman:</span> {{ $dataAnggota->currentPage() }} dari
                {{ $dataAnggota->lastPage() }}
            </div>
            <div>
                <span class="font-medium">Menampilkan:</span> {{ $dataAnggota->firstItem() ?? 0 }} -
                {{ $dataAnggota->lastItem() ?? 0 }} dari {{ $dataAnggota->total() }}
            </div>
        </div>
    </div>

    <!-- Legend -->
    <div class="mt-6 p-4 bg-blue-50 rounded-lg">
        <h4 class="text-sm font-semibold text-blue-800 mb-4">Keterangan Kolom Laporan:</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 text-sm text-blue-700">
            <div>
                <h5 class="font-semibold text-blue-800 mb-2">Identitas Anggota</h5>
                <p><strong>ID Anggota:</strong> Nomor identitas anggota</p>
                <p><strong>Nama:</strong> Nama lengkap anggota</p>
                <p><strong>Jenis Kelamin:</strong> Laki-laki atau Perempuan</p>
                <p><strong>Alamat:</strong> Alamat tempat tinggal anggota</p>
                <p><strong>Telp:</strong> Nomor telepon anggota</p>
            </div>
            <div>
                <h5 class="font-semibold text-blue-800 mb-2">Saldo Simpanan</h5>
                <p><strong>Simpanan Wajib:</strong> Total saldo simpanan wajib</p>
                <p><strong>Simpanan Sukarela:</strong> Total saldo simpanan sukarela</p>
                <p><strong>Simpanan Khusus I & II:</strong> Total saldo simpanan khusus</p>
                <p><strong>Simpanan Pokok:</strong> Total saldo simpanan pokok</p>
                <p><strong>Tab. Perumahan:</strong> Total saldo tabungan perumahan</p>
                <p><strong>Jumlah:</strong> Total keseluruhan saldo simpanan</p>
            </div>
            <div>
                <h5 class="font-semibold text-blue-800 mb-2">Tagihan Kredit</h5>
                <p><strong>Pinjaman Biasa:</strong> Total pinjaman biasa yang diambil</p>
                <p><strong>Sisa Pinjaman Biasa:</strong> Sisa pinjaman biasa yang belum lunas</p>
                <p><strong>Pinjaman Barang:</strong> Total pinjaman barang yang diambil</p>
                <p><strong>Sisa Pinjaman Barang:</strong> Sisa pinjaman barang yang belum lunas</p>
            </div>
            <div>
                <h5 class="font-semibold text-blue-800 mb-2">Keterangan Pinjaman</h5>
                <p><strong>Jumlah Pinjaman:</strong> Total jumlah pinjaman yang pernah diambil</p>
                <p><strong>Pinjaman Lunas:</strong> Jumlah pinjaman yang sudah lunas</p>
                <p><strong>Pembayaran:</strong> Status pembayaran (Lancar/Macet)</p>
                <p><strong>Tanggal Tempo:</strong> Tanggal jatuh tempo pinjaman</p>
            </div>
            <div>
                <h5 class="font-semibold text-blue-800 mb-2">Keterangan Simpanan</h5>
                <p><strong>Setor:</strong> Total setoran anggota per jenis simpanan</p>
                <p><strong>Tarik:</strong> Total penarikan anggota per jenis simpanan</p>
                <p><strong>Saldo:</strong> Selisih antara setoran dan penarikan</p>
            </div>
            <div>
                <h5 class="font-semibold text-blue-800 mb-2">Keterangan Umum</h5>
                <p><strong>Tagihan Kredit:</strong> Total tagihan pinjaman yang harus dibayar</p>
                <p><strong>Bayar Kredit:</strong> Total pembayaran pinjaman yang sudah dilakukan</p>
                <p><strong>Sisa Kredit:</strong> Selisih antara tagihan dan pembayaran pinjaman</p>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
function submitFilter() {
    // Get current values from the input fields
    const search = document.getElementById('search').value;

    // Build the URL with parameters
    let url = '{{ route("laporan.kas.anggota") }}';
    const params = new URLSearchParams();

    if (search) {
        params.append('search', search);
    }

    if (params.toString()) {
        url += '?' + params.toString();
    }

    // Redirect to the filtered URL
    window.location.href = url;
}

// Allow Enter key to submit filter
document.getElementById('search').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        submitFilter();
    }
});
</script>

<!-- Custom Styles -->
<style>
/* Sub-table styling - no borders */
table table {
    border: none !important;
}

table table tr {
    border: none !important;
}

table table td {
    border: none !important;
    padding: 2px 0;
}

/* Hover effect for main rows */
tbody tr:hover {
    background-color: #f8fafc;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .overflow-x-auto {
        font-size: 0.75rem;
    }
}

/* Print Styles */
@media print {

    .sidebar,
    .bg-\[#14AE5C\],
    button,
    a {
        display: none !important;
    }

    .bg-white {
        box-shadow: none !important;
    }

    .overflow-x-auto {
        overflow: visible !important;
    }

    table {
        page-break-inside: avoid;
        min-width: auto !important;
    }

    .mb-8 {
        margin-bottom: 2rem !important;
    }
}
</style>
@endsection