@extends('layouts.app')

@section('title', 'Laporan Kas Pinjaman')
@section('sub-title', 'Laporan')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <!-- Header Panel -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg p-4 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas fa-money-bill-wave text-2xl mr-3"></i>
                <div>
                    <h2 class="text-xl font-bold">Laporan Kas Pinjaman</h2>
                    <p class="text-blue-100 text-sm">Laporan detail pinjaman dan manajemen kredit anggota</p>
                </div>
            </div>
            <button onclick="toggleFilter()" class="bg-white bg-opacity-20 hover:bg-opacity-30 rounded-lg p-2 transition-all duration-200">
                <i class="fas fa-chevron-down text-lg" id="filterIcon"></i>
            </button>
        </div>
    </div>

    <!-- Filter Section -->
    <div id="filterSection" class="mb-6 bg-gray-50 rounded-lg p-4 border border-gray-200">
        <form method="GET" action="{{ route('laporan.kas_pinjaman') }}" class="space-y-4">
            <!-- Filter Controls -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                <div class="flex items-end">
                    <div class="flex gap-2">
                        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-200 flex items-center">
                            <i class="fas fa-search mr-2"></i>Filter
                        </button>
                        <a href="{{ route('laporan.kas_pinjaman') }}" class="px-6 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors duration-200 flex items-center">
                            <i class="fas fa-refresh mr-2"></i>Reset
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Quick Filter Presets -->
            <div class="flex flex-wrap gap-2">
                <button type="button" onclick="setPreset('year')" class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm hover:bg-blue-200 transition-colors">
                    <i class="fas fa-calendar mr-1"></i>Tahun Ini
                </button>
                <button type="button" onclick="setPreset('last_year')" class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm hover:bg-gray-200 transition-colors">
                    <i class="fas fa-calendar mr-1"></i>Tahun Kemarin
                </button>
                <button type="button" onclick="setPreset('quarter')" class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm hover:bg-green-200 transition-colors">
                    <i class="fas fa-calendar mr-1"></i>Kuartal Ini
                </button>
                <button type="button" onclick="setPreset('month')" class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-sm hover:bg-purple-200 transition-colors">
                    <i class="fas fa-calendar mr-1"></i>Bulan Ini
                </button>
            </div>
        </form>
    </div>

    <!-- Export Buttons -->
    <div class="mb-6 flex flex-wrap gap-2">
        <a href="{{ route('laporan.kas_pinjaman.export.pdf', ['tgl_dari' => $tgl_dari, 'tgl_samp' => $tgl_samp]) }}" 
           class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors duration-200 flex items-center">
            <i class="fas fa-file-pdf mr-2"></i>Cetak Laporan (PDF)
        </a>
        <a href="{{ route('laporan.kas_pinjaman.export.excel', ['tgl_dari' => $tgl_dari, 'tgl_samp' => $tgl_samp]) }}" 
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
            Laporan Kas Pinjaman
        </h3>
        <p class="text-center text-gray-600 mb-6">
            <strong>Periode:</strong> {{ \Carbon\Carbon::parse($tgl_dari)->format('d F Y') }} s/d {{ \Carbon\Carbon::parse($tgl_samp)->format('d F Y') }}
        </p>
        
        @if(count($data) > 0)
        <!-- Main Report Table -->
        <div class="overflow-x-auto bg-white rounded-lg shadow-lg">
            <table class="min-w-full border border-gray-200">
                <thead class="bg-gradient-to-r from-blue-600 to-blue-700 text-white">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">No</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Tanggal Pinjam</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Nama</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider">Pokok Pinjaman</th>
                        <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider">Lama Angsuran (Bulan)</th>
                        <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider">Bunga (%)</th>
                        <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider">Biaya Adm</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider">Pokok Angsuran (Bulan)</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider">Bunga Pinjaman (Bulan)</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider">Angsuran (Bulan)</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider">Jumlah Bayar</th>
                        <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider">Sisa Angsuran (Bulan)</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider">Sisa Tagihan</th>
                        <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($data as $loan)
                    <tr class="hover:bg-blue-50 transition-colors duration-200 {{ $loan['status'] == 'Lunas' ? 'bg-green-50' : '' }}">
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $loan['no'] }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ \Carbon\Carbon::parse($loan['tgl_pinjam'])->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $loan['nama'] }}</td>
                        <td class="px-4 py-3 text-sm text-right font-semibold text-gray-900">
                            {{ number_format($loan['pokok_pinjaman'], 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-sm text-center text-gray-900">{{ $loan['lama_angsuran'] }}</td>
                        <td class="px-4 py-3 text-sm text-center text-gray-900">{{ $loan['bunga'] }}</td>
                        <td class="px-4 py-3 text-sm text-center text-gray-900">{{ $loan['biaya_adm'] }}</td>
                        <td class="px-4 py-3 text-sm text-right text-gray-900">
                            {{ number_format($loan['pokok_angsuran'], 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-sm text-right text-gray-900">
                            {{ number_format($loan['bunga_pinjaman'], 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-sm text-right text-gray-900">
                            {{ number_format($loan['angsuran'], 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-sm text-right font-semibold text-gray-900">
                            {{ number_format($loan['jumlah_bayar'], 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-sm text-center {{ $loan['sisa_angsuran'] < 0 ? 'text-red-600 font-semibold' : 'text-gray-900' }}">
                            {{ $loan['sisa_angsuran'] }}
                        </td>
                        <td class="px-4 py-3 text-sm text-right {{ $loan['sisa_tagihan'] < 0 ? 'text-red-600 font-semibold' : 'text-gray-900' }}">
                            {{ number_format($loan['sisa_tagihan'], 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-sm text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $loan['status'] == 'Lunas' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ $loan['status'] }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gradient-to-r from-gray-100 to-gray-200">
                    <tr>
                        <td class="px-4 py-3 text-sm font-bold text-gray-900" colspan="3">
                            <i class="fas fa-calculator mr-2 text-gray-600"></i>
                            <span class="font-bold text-gray-800">TOTAL</span>
                        </td>
                        <td class="px-4 py-3 text-sm font-bold text-right text-gray-900">
                            {{ number_format($summary['total_pinjaman'], 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-sm font-bold text-center text-gray-900" colspan="6">
                            <!-- Empty cells for alignment -->
                        </td>
                        <td class="px-4 py-3 text-sm font-bold text-right text-gray-900">
                            {{ number_format($summary['total_bayar'], 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-sm font-bold text-center text-gray-900">
                            <!-- Empty cell for alignment -->
                        </td>
                        <td class="px-4 py-3 text-sm font-bold text-right text-gray-900">
                            {{ number_format($summary['total_sisa'], 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-sm font-bold text-center text-gray-900">
                            <!-- Empty cell for alignment -->
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @else
        <div class="text-center py-12 bg-gray-50 rounded-lg">
            <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada data pinjaman</h3>
            <p class="text-gray-500">Tidak ada transaksi pinjaman untuk periode <strong>{{ \Carbon\Carbon::parse($tgl_dari)->format('d F Y') }} - {{ \Carbon\Carbon::parse($tgl_samp)->format('d F Y') }}</strong></p>
        </div>
        @endif
    </div>

</div>

<!-- JavaScript for Filter Toggle and Presets -->
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

    // Date preset functions
    window.setPreset = function(preset) {
        const today = new Date();
        const tglDariInput = document.getElementById('tgl_dari');
        const tglSampInput = document.getElementById('tgl_samp');
        
        let startDate, endDate;
        
        switch(preset) {
            case 'year':
                startDate = new Date(today.getFullYear(), 0, 1);
                endDate = new Date(today.getFullYear(), 11, 31);
                break;
            case 'last_year':
                startDate = new Date(today.getFullYear() - 1, 0, 1);
                endDate = new Date(today.getFullYear() - 1, 11, 31);
                break;
            case 'quarter':
                const quarter = Math.floor(today.getMonth() / 3);
                startDate = new Date(today.getFullYear(), quarter * 3, 1);
                endDate = new Date(today.getFullYear(), quarter * 3 + 3, 0);
                break;
            case 'month':
                startDate = new Date(today.getFullYear(), today.getMonth(), 1);
                endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                break;
        }
        
        tglDariInput.value = startDate.toISOString().split('T')[0];
        tglSampInput.value = endDate.toISOString().split('T')[0];
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