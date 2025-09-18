@extends('layouts.app')

@section('title', 'Laporan Buku Besar')
@section('sub-title', 'Laporan')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <!-- Header Panel -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg p-4 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas fa-book text-2xl mr-3"></i>
                <div>
                    <h2 class="text-xl font-bold">Cetak Laporan Buku Besar</h2>
                    <p class="text-blue-100 text-sm">Laporan lengkap transaksi buku besar dengan saldo berjalan</p>
                </div>
            </div>
            <button onclick="toggleFilter()" class="bg-white bg-opacity-20 hover:bg-opacity-30 rounded-lg p-2 transition-all duration-200">
                <i class="fas fa-chevron-down text-lg" id="filterIcon"></i>
            </button>
        </div>
    </div>

    <!-- Filter Section -->
    <div id="filterSection" class="mb-6 bg-gray-50 rounded-lg p-4 border border-gray-200">
        <form method="GET" action="{{ route('laporan.buku_besar') }}" class="space-y-4">
            <!-- Filter Controls -->
            <div class="grid grid-cols-1 md:grid-cols-1 gap-4">
                <div>
                    <label for="periode" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar-alt mr-1"></i>Periode
                    </label>
                    <input type="month" id="periode" name="periode" value="{{ $periode }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap gap-2 pt-4 border-t border-gray-200">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-200 flex items-center">
                    <i class="fas fa-search mr-2"></i>Filter
                </button>
                <a href="{{ route('laporan.buku_besar') }}" class="px-6 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors duration-200 flex items-center">
                    <i class="fas fa-refresh mr-2"></i>Hapus Filter
                </a>
            </div>
        </form>
    </div>

    <!-- Export Buttons -->
    <div class="mb-6 flex flex-wrap gap-2">
        <a href="{{ route('laporan.buku_besar.export.pdf', ['periode' => $periode]) }}" 
           class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors duration-200 flex items-center">
            <i class="fas fa-file-pdf mr-2"></i>Cetak Laporan (PDF)
        </a>
        <a href="{{ route('laporan.buku_besar.export.excel', ['periode' => $periode]) }}" 
           class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors duration-200 flex items-center">
            <i class="fas fa-file-excel mr-2"></i>Export Excel
        </a>
        <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-200 flex items-center">
            <i class="fas fa-print mr-2"></i>Print
        </button>
    </div>


    <!-- Main Content -->
    <div class="mb-6">
        <h3 class="text-2xl font-bold text-gray-800 mb-2 text-center">
            Laporan Buku Besar
        </h3>
        <p class="text-center text-gray-600 mb-6">
            <strong>Periode:</strong> {{ \Carbon\Carbon::createFromFormat('Y-m', $periode)->format('F Y') }}
        </p>
        
        @if(count($processedData) > 0)
            @foreach($processedData as $kasData)
                <div class="mb-8">
                    <!-- Kas Header -->
                    <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-t-lg p-4">
                        <h4 class="text-xl font-bold">{{ $kasData['kas']->nama }}</h4>
                    </div>
                    
                    <!-- Table for this kas -->
                    <div class="overflow-x-auto bg-white rounded-b-lg shadow-lg">
                        <table class="min-w-full border border-gray-200">
                            <thead class="bg-gradient-to-r from-blue-600 to-blue-700 text-white">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">No.</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Tanggal</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Jenis Transaksi</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Keterangan</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Nama</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider">Debet</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider">Kredit</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider">Saldo</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <!-- Saldo Awal Row -->
                                <tr class="bg-yellow-50 border-b-2 border-yellow-200">
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900" colspan="7">
                                        <i class="fas fa-wallet mr-2 text-yellow-600"></i>
                                        <span class="font-bold text-yellow-800">SALDO AWAL</span>
                                    </td>
                                    <td class="px-4 py-3 text-sm font-bold text-right {{ $kasData['saldo_awal'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        Rp {{ number_format($kasData['saldo_awal'], 0, ',', '.') }}
                                    </td>
                                </tr>
                                
                                @foreach($kasData['transaksi'] as $row)
                                    <tr class="hover:bg-blue-50 transition-colors duration-200">
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $row['no'] }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900">
                                            {{ \Carbon\Carbon::parse($row['tanggal'])->format('d/m/Y') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ $row['jenis_transaksi'] }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900">
                                            {{ $row['keterangan'] }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900">
                                            {{ $row['nama'] }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right {{ $row['debet'] > 0 ? 'text-green-600 font-semibold' : 'text-gray-400' }}">
                                            {{ $row['debet'] > 0 ? 'Rp ' . number_format($row['debet'], 0, ',', '.') : '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right {{ $row['kredit'] > 0 ? 'text-red-600 font-semibold' : 'text-gray-400' }}">
                                            {{ $row['kredit'] > 0 ? 'Rp ' . number_format($row['kredit'], 0, ',', '.') : '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm font-bold text-right {{ $row['saldo'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                            Rp {{ number_format($row['saldo'], 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gradient-to-r from-gray-100 to-gray-200">
                                <tr>
                                    <td class="px-4 py-3 text-sm font-bold text-gray-900" colspan="5">
                                        <i class="fas fa-calculator mr-2 text-gray-600"></i>
                                        <span class="font-bold text-gray-800">TOTAL {{ $kasData['kas']->nama }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-sm font-bold text-right text-green-600">
                                        Rp {{ number_format($kasData['total_debet'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-sm font-bold text-right text-red-600">
                                        Rp {{ number_format($kasData['total_kredit'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-sm font-bold text-right {{ $kasData['saldo_akhir'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        Rp {{ number_format($kasData['saldo_akhir'], 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @endforeach
            
            <!-- Total Saldo Keseluruhan -->
            <div class="mt-6 p-4 bg-gradient-to-r from-green-50 to-green-100 rounded-lg border border-green-200">
                <div class="text-center">
                    <h4 class="text-lg font-bold text-green-800 mb-2">
                        <i class="fas fa-calculator mr-2"></i>
                        TOTAL SALDO KAS BANK
                    </h4>
                    <p class="text-2xl font-bold {{ $totalSaldoKeseluruhan >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        Rp {{ number_format($totalSaldoKeseluruhan, 0, ',', '.') }}
                    </p>
                </div>
            </div>
        @else
            <div class="text-center py-12 bg-gray-50 rounded-lg">
                <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada data transaksi</h3>
                <p class="text-gray-500">Tidak ada transaksi pada periode <strong>{{ \Carbon\Carbon::createFromFormat('Y-m', $periode)->format('F Y') }}</strong></p>
            </div>
        @endif
    </div>

</div>

<!-- JavaScript for Filter Toggle -->
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

    // Add smooth scrolling to table when filter is applied
    const form = document.querySelector('form');
    form.addEventListener('submit', function() {
        setTimeout(() => {
            const table = document.querySelector('.overflow-x-auto');
            if (table) {
                table.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
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
    .sidebar, .bg-green-500, button, a {
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