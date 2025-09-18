@extends('layouts.app')

@section('title', 'Laporan Kas Simpanan')
@section('sub-title', 'Laporan')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <!-- Header Panel -->
    <div class="bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg p-4 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas fa-piggy-bank text-2xl mr-3"></i>
                <div>
                    <h2 class="text-xl font-bold">Laporan Kas Simpanan</h2>
                    <p class="text-green-100 text-sm">Laporan detail simpanan anggota per jenis simpanan</p>
                </div>
            </div>
            <button onclick="toggleFilter()" class="bg-white bg-opacity-20 hover:bg-opacity-30 rounded-lg p-2 transition-all duration-200">
                <i class="fas fa-chevron-down text-lg" id="filterIcon"></i>
            </button>
        </div>
    </div>

    <!-- Filter Section -->
    <div id="filterSection" class="mb-6 bg-gray-50 rounded-lg p-4 border border-gray-200">
        <form method="GET" action="{{ route('laporan.kas_simpanan') }}" class="space-y-4">
            <!-- Filter Controls -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="periode" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar-alt mr-1"></i>Periode (Bulan/Tahun)
                    </label>
                    <input type="month" id="periode" name="periode" value="{{ $periode }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                </div>
                <div class="flex items-end">
                    <div class="flex gap-2">
                        <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors duration-200 flex items-center">
                            <i class="fas fa-search mr-2"></i>Filter
                        </button>
                        <a href="{{ route('laporan.kas_simpanan') }}" class="px-6 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors duration-200 flex items-center">
                            <i class="fas fa-refresh mr-2"></i>Reset
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Export Buttons -->
    <div class="mb-6 flex flex-wrap gap-2">
        <a href="{{ route('laporan.kas_simpanan.export.pdf', ['periode' => $periode]) }}" 
           class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors duration-200 flex items-center">
            <i class="fas fa-file-pdf mr-2"></i>Cetak Laporan (PDF)
        </a>
        <a href="{{ route('laporan.kas_simpanan.export.excel', ['periode' => $periode]) }}" 
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
            Laporan Kas Simpanan Anggota
        </h3>
        <p class="text-center text-gray-600 mb-6">
            <strong>Periode:</strong> {{ \Carbon\Carbon::createFromFormat('Y-m', $periode)->format('F Y') }}
        </p>
        
        @if(count($data) > 0)
        <!-- Display data per jenis simpanan -->
        @foreach($data as $jenisData)
        <div class="mb-8">
            <h4 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-piggy-bank mr-2 text-green-600"></i>
                {{ $jenisData['jenis_nama'] }}
            </h4>
            
            @if(count($jenisData['transaksi']) > 0)
            <div class="overflow-x-auto bg-white rounded-lg shadow-lg">
                <table class="min-w-full border border-gray-200">
                    <thead class="bg-gradient-to-r from-green-600 to-green-700 text-white">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">No</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Tanggal</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Nama</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider">Debet</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider">Kredit</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($jenisData['transaksi'] as $transaksi)
                        <tr class="hover:bg-green-50 transition-colors duration-200">
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $transaksi['no'] }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ \Carbon\Carbon::parse($transaksi['tanggal'])->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $transaksi['nama'] }}</td>
                            <td class="px-4 py-3 text-sm text-right {{ $transaksi['debet'] > 0 ? 'text-green-600 font-semibold' : 'text-gray-400' }}">
                                {{ $transaksi['debet'] > 0 ? 'Rp ' . number_format($transaksi['debet'], 0, ',', '.') : '-' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-right {{ $transaksi['kredit'] > 0 ? 'text-red-600 font-semibold' : 'text-gray-400' }}">
                                {{ $transaksi['kredit'] > 0 ? 'Rp ' . number_format($transaksi['kredit'], 0, ',', '.') : '-' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gradient-to-r from-gray-100 to-gray-200">
                        <tr>
                            <td class="px-4 py-3 text-sm font-bold text-gray-900" colspan="3">
                                <i class="fas fa-calculator mr-2 text-gray-600"></i>
                                <span class="font-bold text-gray-800">TOTAL {{ $jenisData['jenis_nama'] }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm font-bold text-right text-green-600">
                                Rp {{ number_format($summary['per_jenis'][$jenisData['jenis_id']]['debet'], 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-sm font-bold text-right text-red-600">
                                Rp {{ number_format($summary['per_jenis'][$jenisData['jenis_id']]['kredit'], 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @else
            <div class="text-center py-8 bg-gray-50 rounded-lg">
                <i class="fas fa-inbox text-4xl text-gray-300 mb-2"></i>
                <p class="text-gray-500">Tidak ada transaksi untuk {{ $jenisData['jenis_nama'] }}</p>
            </div>
            @endif
        </div>
        @endforeach
        
        <!-- Total Keseluruhan -->
        <div class="mt-8 p-4 bg-gradient-to-r from-gray-100 to-gray-200 rounded-lg border border-gray-200">
            <h4 class="text-lg font-bold text-gray-800 mb-4 text-center">
                <i class="fas fa-calculator mr-2 text-gray-600"></i>
                TOTAL KESELURUHAN
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                <div>
                    <p class="text-sm text-gray-600">Total Debet</p>
                    <p class="text-xl font-bold text-green-600">Rp {{ number_format($summary['total_debet'], 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Total Kredit</p>
                    <p class="text-xl font-bold text-red-600">Rp {{ number_format($summary['total_kredit'], 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Saldo Bersih</p>
                    <p class="text-xl font-bold {{ $summary['total_saldo'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        Rp {{ number_format($summary['total_saldo'], 0, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>
        @else
        <div class="text-center py-12 bg-gray-50 rounded-lg">
            <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada data simpanan</h3>
            <p class="text-gray-500">Tidak ada transaksi simpanan untuk periode <strong>{{ \Carbon\Carbon::createFromFormat('Y-m', $periode)->format('F Y') }}</strong></p>
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