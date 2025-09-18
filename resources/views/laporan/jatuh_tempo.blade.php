@extends('layouts.app')

@section('title', 'Laporan Jatuh Tempo Pembayaran Kredit')
@section('sub-title', 'Laporan')

@section('content')
<div class="bg-white rounded-lg shadow-md">
    <!-- Blue Header Panel -->
    <div class="bg-[#3B82F6] text-white p-4 rounded-t-lg">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold">
                <i class="fas fa-calendar-times mr-2"></i>
                Jatuh Tempo Pembayaran Kredit
            </h2>
            <button type="button" onclick="toggleCollapse()" class="bg-white bg-opacity-20 hover:bg-opacity-30 px-3 py-1 rounded-md transition-colors duration-200">
                <i class="fas fa-chevron-up mr-2"></i>Collapse
            </button>
            </div>
            </div>

    <!-- Control Panel -->
    <div id="control-panel" class="p-6 border-b border-gray-200">
        <form method="GET" action="{{ route('laporan.jatuh.tempo') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Filter Periode -->
                <div>
                    <label for="periode" class="block text-sm font-medium text-gray-700 mb-2">Periode</label>
                    <input type="month" id="periode" name="periode" value="{{ $periode }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#3B82F6] focus:border-transparent">
            </div>

                <!-- Search -->
                <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Cari</label>
                <input type="text" id="search" name="search" value="{{ $search }}" 
                           placeholder="Nama anggota atau kode pinjam"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#3B82F6] focus:border-transparent">
            </div>

                <!-- Action Buttons -->
                <div class="flex items-end space-x-2">
                    <button type="button" onclick="submitFilter()" class="px-4 py-2 bg-[#14AE5C] text-white rounded-md hover:bg-[#11994F] transition-colors duration-200">
                        <i class="fas fa-search mr-2"></i>Cari
                </button>
                <a href="{{ route('laporan.jatuh.tempo') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors duration-200">
                        <i class="fas fa-refresh mr-2"></i>Hapus Filter
                    </a>
                </div>
            </div>

            <!-- Export Button -->
            <div class="flex justify-end">
                <a href="{{ route('laporan.jatuh.tempo.export.pdf') }}?periode={{ $periode }}&search={{ $search }}" 
                   class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors duration-200">
                    <i class="fas fa-file-pdf mr-2"></i>Cetak Laporan
                </a>
            </div>
        </form>
    </div>

    <!-- Main Content -->
    <div class="p-6">
        <!-- Report Title -->
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Laporan Jatuh Tempo Pembayaran Kredit</h1>
            <p class="text-gray-600 mt-2">Periode: {{ \Carbon\Carbon::parse($periode . '-01')->format('F Y') }}</p>
    </div>

    <!-- Data Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200 rounded-lg overflow-hidden">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No.</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode Pinjam</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Anggota</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Pinjam</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Tempo</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lama Pinjam</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Tagihan</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Dibayar</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Sisa Tagihan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($processedData as $index => $pinjaman)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $dataPinjaman->firstItem() + $index }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $pinjaman->kode_pinjam }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            {{ $pinjaman->no_ktp }} - {{ $pinjaman->nama_anggota }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ \Carbon\Carbon::parse($pinjaman->tgl_pinjam)->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            <span class="font-medium {{ \Carbon\Carbon::parse($pinjaman->tempo)->isPast() ? 'text-red-600' : 'text-green-600' }}">
                                {{ \Carbon\Carbon::parse($pinjaman->tempo)->format('d/m/Y') }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $pinjaman->lama_angsuran }} bulan</td>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right">Rp {{ number_format($pinjaman->tagihan, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right">Rp {{ number_format($pinjaman->total_bayar, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm font-bold text-gray-900 text-right {{ $pinjaman->sisa_tagihan > 0 ? 'text-red-600' : 'text-green-600' }}">
                            Rp {{ number_format($pinjaman->sisa_tagihan, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-2"></i>
                            <p>Tidak ada data jatuh tempo untuk periode yang dipilih</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-100">
                    <tr>
                        <td colspan="6" class="px-4 py-3 text-sm font-bold text-gray-900 text-right">Jumlah Total:</td>
                        <td class="px-4 py-3 text-sm font-bold text-gray-900 text-right">Rp {{ number_format($totalTagihan, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm font-bold text-gray-900 text-right">Rp {{ number_format($totalDibayar, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm font-bold text-gray-900 text-right">Rp {{ number_format($totalSisa, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
    </div>

    <!-- Pagination -->
    @if($dataPinjaman->hasPages())
    <div class="mt-6">
        {{ $dataPinjaman->appends(request()->query())->links() }}
    </div>
    @endif
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
    let url = '{{ route("laporan.jatuh.tempo") }}';
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
    .sidebar, .bg-blue-600, button, a {
        display: none !important;
    }
    
    .bg-white {
        box-shadow: none !important;
    }
    
    table {
        page-break-inside: avoid;
    }
    
    .mb-6 {
        margin-bottom: 2rem !important;
    }
}
</style>
@endsection 