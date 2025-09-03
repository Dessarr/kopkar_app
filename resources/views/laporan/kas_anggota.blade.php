@extends('layouts.app')

@section('title', 'Laporan Kas Anggota')
@section('sub-title', 'Laporan')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <!-- Header Panel -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg p-6 mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold">Cetak Data Kas Anggota</h1>
                <p class="text-blue-100 mt-1">Laporan komprehensif data kas per anggota</p>
            </div>
            <div class="flex gap-2">
                <button onclick="toggleCollapse()" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-400 transition-colors duration-200">
                    <i class="fas fa-chevron-up mr-2"></i>Collapse
                </button>
            </div>
        </div>
    </div>

    <!-- Collapsible Control Panel -->
    <div id="control-panel" class="mb-6">
        <div class="bg-gray-50 p-4 rounded-lg">
            <div class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label for="periode" class="block text-sm font-medium text-gray-700 mb-2">Periode</label>
                    <input type="month" id="periode" name="periode" value="{{ $periode }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent">
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Cari</label>
                    <input type="text" id="search" name="search" value="{{ $search }}" 
                           placeholder="Nama, No KTP, atau ID Anggota"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent">
                </div>
                <div class="flex gap-2">
                    <button type="button" onclick="submitFilter()" class="px-4 py-2 bg-[#14AE5C] text-white rounded-md hover:bg-[#11994F] transition-colors duration-200">
                        <i class="fas fa-search mr-2"></i>Cari
                    </button>
                    <a href="{{ route('laporan.kas.anggota') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors duration-200">
                        <i class="fas fa-refresh mr-2"></i>Hapus Filter
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Buttons -->
    <div class="mb-6 flex flex-wrap gap-2">
        <a href="{{ route('laporan.kas.anggota.export.detail') }}?periode={{ $periode }}&search={{ $search }}" 
           class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors duration-200">
            <i class="fas fa-file-excel mr-2"></i>Download Detail
        </a>
        <a href="{{ route('laporan.kas.anggota.export.tagihan') }}?periode={{ $periode }}&search={{ $search }}" 
           class="px-4 py-2 bg-orange-600 text-white rounded-md hover:bg-orange-700 transition-colors duration-200">
            <i class="fas fa-file-excel mr-2"></i>Download Tagihan
        </a>
        <a href="{{ route('laporan.kas.anggota.export.simpanan') }}?periode={{ $periode }}&search={{ $search }}" 
           class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-200">
            <i class="fas fa-file-excel mr-2"></i>Download Simpanan
        </a>
    </div>

    <!-- Hidden Form for Filter -->
    <form id="filter-form" method="GET" action="{{ route('laporan.kas.anggota') }}" style="display: none;">
        <input type="hidden" name="periode" value="{{ $periode }}">
        <input type="hidden" name="search" value="{{ $search }}">
    </form>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
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
        
        <div class="bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-white bg-opacity-20">
                    <i class="fas fa-arrow-up text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm opacity-90">Total Setoran</p>
                    <p class="text-2xl font-bold">Rp {{ number_format($totalSimpanan) }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-red-500 to-red-600 text-white rounded-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-white bg-opacity-20">
                    <i class="fas fa-arrow-down text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm opacity-90">Total Penarikan</p>
                    <p class="text-2xl font-bold">Rp {{ number_format($totalPenarikan) }}</p>
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
                    <p class="text-2xl font-bold {{ $totalSaldo >= 0 ? 'text-green-200' : 'text-red-200' }}">
                        Rp {{ number_format($totalSaldo) }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Title -->
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Laporan Data Kas Per Anggota</h1>
        <p class="text-lg text-gray-600 mt-2">Periode {{ \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->format('F Y') }}</p>
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
            <table class="min-w-full bg-white border border-gray-200 rounded-lg overflow-hidden">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Photo</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Identitas</th>
                        
                        @foreach($jenisSimpanan as $jenis)
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" colspan="3">
                            {{ $jenis->jns_simpan }}
                        </th>
                        @endforeach
                        
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" colspan="3">Total Simpanan</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" colspan="3">Tagihan Kredit</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" colspan="3">Tagihan Simpanan</th>
                    </tr>
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID | Nama | L/P | Jabatan | Dept | Alamat | Telp</th>
                        
                        @foreach($jenisSimpanan as $jenis)
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Setor</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Tarik</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Saldo</th>
                        @endforeach
                        
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Setor</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Tarik</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Saldo</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Pinjaman</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Bayar</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Sisa</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Tagihan</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Bayar</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Sisa</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($dataAnggota as $index => $anggota)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $dataAnggota->firstItem() + $index }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-center">
                            @if($anggota->file_pic)
                                <img src="{{ asset('storage/' . $anggota->file_pic) }}" alt="Photo" class="w-8 h-8 rounded-full mx-auto">
                            @else
                                <div class="w-8 h-8 bg-gray-300 rounded-full mx-auto flex items-center justify-center">
                                    <i class="fas fa-user text-gray-600 text-xs"></i>
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            <div class="space-y-1">
                                <div><strong>ID:</strong> AG{{ str_pad($anggota->id, 4, '0', STR_PAD_LEFT) }}</div>
                                <div><strong>Nama:</strong> {{ $anggota->nama }}</div>
                                <div><strong>L/P:</strong> {{ $anggota->jk == 'L' ? 'Laki-laki' : 'Perempuan' }}</div>
                                <div><strong>Jabatan:</strong> {{ $anggota->jabatan_id ? 'Pengurus' : 'Anggota' }}</div>
                                <div><strong>Dept:</strong> {{ $anggota->departement ?? '-' }}</div>
                                <div><strong>Alamat:</strong> {{ $anggota->alamat ?? '-' }}</div>
                                <div><strong>Telp:</strong> {{ $anggota->notelp ?? '-' }}</div>
                            </div>
                        </td>
                        
                        @php
                            $kas = $kasData[$anggota->no_ktp] ?? [];
                        @endphp
                        
                        @foreach($jenisSimpanan as $jenis)
                        @php
                            $setor = $kas['setoran'][$jenis->id] ?? 0;
                            $tarik = $kas['penarikan'][$jenis->id] ?? 0;
                            $saldo = $setor - $tarik;
                        @endphp
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($setor) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($tarik) }}</td>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right {{ $saldo >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ number_format($saldo) }}
                        </td>
                        @endforeach
                        
                        <!-- Total Simpanan -->
                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right">{{ number_format($kas['total_setor'] ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right">{{ number_format($kas['total_tarik'] ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm font-bold text-gray-900 text-right {{ ($kas['total_saldo'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ number_format($kas['total_saldo'] ?? 0) }}
                        </td>
                        
                        <!-- Tagihan Kredit -->
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($kas['tagihan_kredit'] ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($kas['bayar_kredit'] ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right {{ ($kas['sisa_kredit'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ number_format($kas['sisa_kredit'] ?? 0) }}
                        </td>
                        
                        <!-- Tagihan Simpanan -->
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($kas['tagihan_simpanan'] ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($kas['bayar'] ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right {{ ($kas['sisa'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ number_format($kas['sisa'] ?? 0) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ 3 + (count($jenisSimpanan) * 3) + 9 }}" class="px-4 py-8 text-center text-gray-500">
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
                <a href="{{ $dataAnggota->appends(request()->query())->url(1) }}" class="px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
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
                <a href="{{ $dataAnggota->appends(request()->query())->previousPageUrl() }}" class="px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                    <i class="fas fa-chevron-left"></i>
                </a>
            @endif

            <!-- Page Numbers (show 5 pages around current page) -->
            @php
                $start = max(1, $dataAnggota->currentPage() - 2);
                $end = min($dataAnggota->lastPage(), $dataAnggota->currentPage() + 2);
            @endphp

            @for ($page = $start; $page <= $end; $page++)
                @if ($page == $dataAnggota->currentPage())
                    <span class="px-3 py-2 text-sm text-white bg-blue-600 rounded-md font-medium">{{ $page }}</span>
                @else
                    <a href="{{ $dataAnggota->appends(request()->query())->url($page) }}" class="px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                        {{ $page }}
                    </a>
                @endif
            @endfor

            <!-- Next Page Link -->
            @if ($dataAnggota->hasMorePages())
                <a href="{{ $dataAnggota->appends(request()->query())->nextPageUrl() }}" class="px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
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
                <a href="{{ $dataAnggota->appends(request()->query())->url($dataAnggota->lastPage()) }}" class="px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
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
                <span class="font-medium">Halaman:</span> {{ $dataAnggota->currentPage() }} dari {{ $dataAnggota->lastPage() }}
            </div>
            <div>
                <span class="font-medium">Menampilkan:</span> {{ $dataAnggota->firstItem() ?? 0 }} - {{ $dataAnggota->lastItem() ?? 0 }} dari {{ $dataAnggota->total() }}
            </div>
        </div>
    </div>

    <!-- Legend -->
    <div class="mt-6 p-4 bg-blue-50 rounded-lg">
        <h4 class="text-sm font-semibold text-blue-800 mb-2">Keterangan:</h4>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-blue-700">
            <div>
                <p><strong>Setor:</strong> Total setoran anggota per jenis simpanan</p>
                <p><strong>Tarik:</strong> Total penarikan anggota per jenis simpanan</p>
                <p><strong>Saldo:</strong> Selisih antara setoran dan penarikan</p>
            </div>
            <div>
                <p><strong>Tagihan Kredit:</strong> Total tagihan pinjaman yang harus dibayar</p>
                <p><strong>Bayar Kredit:</strong> Total pembayaran pinjaman yang sudah dilakukan</p>
                <p><strong>Sisa Kredit:</strong> Selisih antara tagihan dan pembayaran pinjaman</p>
            </div>
            <div>
                <p><strong>Tagihan Simpanan:</strong> Total tagihan simpanan bulanan</p>
                <p><strong>Bayar Simpanan:</strong> Total pembayaran simpanan yang sudah dilakukan</p>
                <p><strong>Sisa Simpanan:</strong> Selisih antara tagihan dan pembayaran simpanan</p>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
function toggleCollapse() {
    const panel = document.getElementById('control-panel');
    const button = document.querySelector('button[onclick="toggleCollapse()"]');
    const icon = button.querySelector('i');
    
    if (panel.style.display === 'none') {
        panel.style.display = 'block';
        icon.className = 'fas fa-chevron-up mr-2';
        button.innerHTML = '<i class="fas fa-chevron-up mr-2"></i>Collapse';
    } else {
        panel.style.display = 'none';
        icon.className = 'fas fa-chevron-down mr-2';
        button.innerHTML = '<i class="fas fa-chevron-down mr-2"></i>Expand';
    }
}

function submitFilter() {
    // Get current values from the input fields
    const periode = document.getElementById('periode').value;
    const search = document.getElementById('search').value;
    
    // Build the URL with parameters
    let url = '{{ route("laporan.kas.anggota") }}';
    const params = new URLSearchParams();
    
    if (periode) {
        params.append('periode', periode);
    }
    if (search) {
        params.append('search', search);
    }
    
    if (params.toString()) {
        url += '?' + params.toString();
    }
    
    // Redirect to the filtered URL
    window.location.href = url;
}

// Update form values when inputs change (for backup)
document.getElementById('periode').addEventListener('change', function() {
    document.querySelector('input[name="periode"]').value = this.value;
});

document.getElementById('search').addEventListener('input', function() {
    document.querySelector('input[name="search"]').value = this.value;
});

// Allow Enter key to submit filter
document.getElementById('search').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        submitFilter();
    }
});
</script>

<!-- Print Styles -->
<style>
@media print {
    .sidebar, .bg-[#14AE5C], button, a, #control-panel {
        display: none !important;
    }
    
    .bg-white {
        box-shadow: none !important;
    }
    
    table {
        page-break-inside: avoid;
    }
    
    .mb-8 {
        margin-bottom: 2rem !important;
    }
}
</style>
@endsection 