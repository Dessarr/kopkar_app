@extends('layouts.app')

@section('title', 'Laporan Transaksi Kas')
@section('sub-title', 'Laporan')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <!-- Header Panel -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg p-4 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas fa-cash-register text-2xl mr-3"></i>
                <div>
                    <h2 class="text-xl font-bold">Cetak Laporan Transaksi Kas</h2>
                    <p class="text-blue-100 text-sm">Laporan lengkap transaksi kas dengan saldo berjalan</p>
                </div>
            </div>
            <button onclick="toggleFilter()" class="bg-white bg-opacity-20 hover:bg-opacity-30 rounded-lg p-2 transition-all duration-200">
                <i class="fas fa-chevron-down text-lg" id="filterIcon"></i>
            </button>
        </div>
    </div>

    <!-- Filter Section -->
    <div id="filterSection" class="mb-6 bg-gray-50 rounded-lg p-4 border border-gray-200">
        <form method="GET" action="{{ route('laporan.transaksi.kas') }}" class="space-y-4">
            <!-- Date Range Picker with Preset Options -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="tgl_dari" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar-alt mr-1"></i>Tanggal Dari
                    </label>
                <input type="date" id="tgl_dari" name="tgl_dari" value="{{ $tgl_dari }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
                <div>
                    <label for="tgl_samp" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar-alt mr-1"></i>Tanggal Sampai
                    </label>
                <input type="date" id="tgl_samp" name="tgl_samp" value="{{ $tgl_samp }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            </div>

            <!-- Preset Options -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-clock mr-1"></i>Pilihan Cepat
                </label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                    <button type="button" onclick="setDateRange('today')" 
                            class="px-3 py-2 text-sm bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200 transition-colors duration-200">
                        <i class="fas fa-calendar-day mr-1"></i>Hari ini
                    </button>
                    <button type="button" onclick="setDateRange('yesterday')" 
                            class="px-3 py-2 text-sm bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200 transition-colors duration-200">
                        <i class="fas fa-calendar-minus mr-1"></i>Kemarin
                    </button>
                    <button type="button" onclick="setDateRange('7days')" 
                            class="px-3 py-2 text-sm bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200 transition-colors duration-200">
                        <i class="fas fa-calendar-week mr-1"></i>7 Hari yang lalu
                    </button>
                    <button type="button" onclick="setDateRange('30days')" 
                            class="px-3 py-2 text-sm bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200 transition-colors duration-200">
                        <i class="fas fa-calendar mr-1"></i>30 Hari yang lalu
                    </button>
                    <button type="button" onclick="setDateRange('thisMonth')" 
                            class="px-3 py-2 text-sm bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200 transition-colors duration-200">
                        <i class="fas fa-calendar-alt mr-1"></i>Bulan ini
                    </button>
                    <button type="button" onclick="setDateRange('lastMonth')" 
                            class="px-3 py-2 text-sm bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200 transition-colors duration-200">
                        <i class="fas fa-calendar-alt mr-1"></i>Bulan kemarin
                    </button>
                    <button type="button" onclick="setDateRange('thisYear')" 
                            class="px-3 py-2 text-sm bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200 transition-colors duration-200">
                        <i class="fas fa-calendar mr-1"></i>Tahun ini
                    </button>
                    <button type="button" onclick="setDateRange('lastYear')" 
                            class="px-3 py-2 text-sm bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200 transition-colors duration-200">
                        <i class="fas fa-calendar mr-1"></i>Tahun kemarin
                    </button>
            </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap gap-2 pt-4 border-t border-gray-200">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-200 flex items-center">
                    <i class="fas fa-search mr-2"></i>Cari
                </button>
                <a href="{{ route('laporan.transaksi.kas') }}" class="px-6 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors duration-200 flex items-center">
                    <i class="fas fa-refresh mr-2"></i>Hapus Filter
                </a>
            </div>
        </form>
    </div>

    <!-- Export Buttons -->
    <div class="mb-6 flex flex-wrap gap-2">
        <a href="{{ route('laporan.transaksi.kas.export.pdf') }}?tgl_dari={{ $tgl_dari }}&tgl_samp={{ $tgl_samp }}" 
           class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors duration-200 flex items-center">
            <i class="fas fa-file-pdf mr-2"></i>Cetak Laporan
        </a>
        <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-200 flex items-center">
            <i class="fas fa-print mr-2"></i>Print
        </button>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg p-6 transform hover:scale-105 transition-all duration-200 shadow-lg">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-white bg-opacity-20">
                    <i class="fas fa-arrow-up text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm opacity-90">Total Debet</p>
                    <p class="text-2xl font-bold">Rp {{ number_format($totalDebet) }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-red-500 to-red-600 text-white rounded-lg p-6 transform hover:scale-105 transition-all duration-200 shadow-lg">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-white bg-opacity-20">
                    <i class="fas fa-arrow-down text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm opacity-90">Total Kredit</p>
                    <p class="text-2xl font-bold">Rp {{ number_format($totalKredit) }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg p-6 transform hover:scale-105 transition-all duration-200 shadow-lg">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-white bg-opacity-20">
                    <i class="fas fa-wallet text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm opacity-90">Saldo Sebelumnya</p>
                    <p class="text-2xl font-bold {{ $saldoSebelumnya >= 0 ? 'text-green-200' : 'text-red-200' }}">
                        Rp {{ number_format($saldoSebelumnya) }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-lg p-6 transform hover:scale-105 transition-all duration-200 shadow-lg">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-white bg-opacity-20">
                    <i class="fas fa-calculator text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm opacity-90">Saldo Akhir</p>
                    <p class="text-2xl font-bold {{ $saldoAkhir >= 0 ? 'text-green-200' : 'text-red-200' }}">
                        Rp {{ number_format($saldoAkhir) }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="mb-6">
        <h3 class="text-2xl font-bold text-gray-800 mb-2 text-center">
            Laporan Transaksi Kas
        </h3>
        <p class="text-center text-gray-600 mb-6">
            Periode: {{ $periodeText }}
        </p>
        
        <div class="overflow-x-auto bg-white rounded-lg shadow-lg">
            <table class="min-w-full border border-gray-200">
                <thead class="bg-gradient-to-r from-blue-600 to-blue-700 text-white">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">No.</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Kode Transaksi</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Tanggal Transaksi</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Akun Transaksi</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Dari Kas</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Untuk Kas</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider">Debet</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider">Kredit</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider">Saldo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <!-- Saldo Sebelumnya Row -->
                    <tr class="bg-yellow-50 border-b-2 border-yellow-200">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900" colspan="8">
                            <i class="fas fa-wallet mr-2 text-yellow-600"></i>
                            <span class="font-bold text-yellow-800">SALDO SEBELUMNYA</span>
                        </td>
                        <td class="px-4 py-3 text-sm font-bold text-right {{ $saldoSebelumnya >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            Rp {{ number_format($saldoSebelumnya) }}
                        </td>
                    </tr>
                    
                    @forelse($dataTransaksi as $index => $transaksi)
                    <tr class="hover:bg-blue-50 transition-colors duration-200">
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $dataTransaksi->firstItem() + $index }}</td>
                        <td class="px-4 py-3 text-sm font-mono text-blue-600">
                            @php
                                $prefixes = [
                                    '48' => 'TPJ', // Pemasukan
                                    '7' => 'TBY',  // Pengeluaran
                                    'transfer' => 'TRD',
                                    'kas_keluar' => 'TRK',
                                    'kas_fisik' => 'TRF',
                                    'kas_deposit' => 'TKD',
                                    'kas_kredit' => 'TKK'
                                ];
                                $prefix = $prefixes[$transaksi->transaksi] ?? 'TRX';
                                $kode = $prefix . str_pad($transaksi->id, 5, '0', STR_PAD_LEFT);
                            @endphp
                            {{ $kode }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            {{ \Carbon\Carbon::parse($transaksi->tgl)->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            {{ $transaksi->akun_transaksi ?? 'N/A' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            {{ $transaksi->dari_kas_nama ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            {{ $transaksi->untuk_kas_nama ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-right {{ $transaksi->debet > 0 ? 'text-green-600 font-semibold' : 'text-gray-400' }}">
                            {{ $transaksi->debet > 0 ? 'Rp ' . number_format($transaksi->debet) : '-' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-right {{ $transaksi->kredit > 0 ? 'text-red-600 font-semibold' : 'text-gray-400' }}">
                            {{ $transaksi->kredit > 0 ? 'Rp ' . number_format($transaksi->kredit) : '-' }}
                        </td>
                        <td class="px-4 py-3 text-sm font-bold text-right {{ $transaksi->saldo >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            Rp {{ number_format($transaksi->saldo) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-2"></i>
                            <p>Tidak ada data transaksi untuk periode yang dipilih</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($dataTransaksi->hasPages())
    <div class="mt-6 flex justify-center">
        <div class="bg-white rounded-lg shadow-md p-4">
        {{ $dataTransaksi->appends(request()->query())->links() }}
        </div>
    </div>
    @endif

    <!-- Summary Footer -->
    <div class="mt-6 p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg border border-gray-200">
        <div class="flex flex-wrap justify-between items-center text-sm text-gray-600">
            <div class="flex items-center">
                <i class="fas fa-info-circle mr-2 text-blue-500"></i>
                <span class="font-medium">Total Data:</span> {{ number_format($dataTransaksi->total()) }} transaksi
            </div>
            <div class="flex items-center">
                <i class="fas fa-file-alt mr-2 text-green-500"></i>
                <span class="font-medium">Halaman:</span> {{ $dataTransaksi->currentPage() }} dari {{ $dataTransaksi->lastPage() }}
            </div>
            <div class="flex items-center">
                <i class="fas fa-list mr-2 text-purple-500"></i>
                <span class="font-medium">Menampilkan:</span> {{ $dataTransaksi->firstItem() ?? 0 }} - {{ $dataTransaksi->lastItem() ?? 0 }} dari {{ $dataTransaksi->total() }}
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for Filter and Preset Options -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize filter section as collapsed
    const filterSection = document.getElementById('filterSection');
    const filterIcon = document.getElementById('filterIcon');
    
    // Toggle filter section
    window.toggleFilter = function() {
        if (filterSection.style.display === 'none') {
            filterSection.style.display = 'block';
            filterIcon.className = 'fas fa-chevron-up text-lg';
        } else {
            filterSection.style.display = 'none';
            filterIcon.className = 'fas fa-chevron-down text-lg';
        }
    };

    // Set date range based on preset options
    window.setDateRange = function(preset) {
        const today = new Date();
        const tglDari = document.getElementById('tgl_dari');
        const tglSamp = document.getElementById('tgl_samp');
        
        let dari, sampai;
        
        switch(preset) {
            case 'today':
                dari = sampai = today;
                break;
            case 'yesterday':
                dari = sampai = new Date(today);
                dari.setDate(today.getDate() - 1);
                break;
            case '7days':
                dari = new Date(today);
                dari.setDate(today.getDate() - 7);
                sampai = today;
                break;
            case '30days':
                dari = new Date(today);
                dari.setDate(today.getDate() - 30);
                sampai = today;
                break;
            case 'thisMonth':
                dari = new Date(today.getFullYear(), today.getMonth(), 1);
                sampai = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                break;
            case 'lastMonth':
                dari = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                sampai = new Date(today.getFullYear(), today.getMonth(), 0);
                break;
            case 'thisYear':
                dari = new Date(today.getFullYear(), 0, 1);
                sampai = new Date(today.getFullYear(), 11, 31);
                break;
            case 'lastYear':
                dari = new Date(today.getFullYear() - 1, 0, 1);
                sampai = new Date(today.getFullYear() - 1, 11, 31);
                break;
        }
        
        tglDari.value = formatDate(dari);
        tglSamp.value = formatDate(sampai);
    };

    // Format date to YYYY-MM-DD
    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    // Add smooth scrolling to table when filter is applied
    const form = document.querySelector('form');
    form.addEventListener('submit', function() {
        setTimeout(() => {
            document.querySelector('.overflow-x-auto').scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }, 100);
    });

    // Add hover effects to table rows
    const tableRows = document.querySelectorAll('tbody tr');
    tableRows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.01)';
            this.style.transition = 'transform 0.2s ease';
        });
        
        row.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });
});
</script>

<!-- Print Styles -->
<style>
@media print {
    .sidebar, .bg-[#14AE5C], button, a {
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