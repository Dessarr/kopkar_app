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

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg p-6 transform hover:scale-105 transition-all duration-200 shadow-lg">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-white bg-opacity-20">
                    <i class="fas fa-users text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm opacity-90">Peminjam Aktif</p>
                    <p class="text-2xl font-bold">{{ number_format($statistics['peminjam_aktif']) }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg p-6 transform hover:scale-105 transition-all duration-200 shadow-lg">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-white bg-opacity-20">
                    <i class="fas fa-check-circle text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm opacity-90">Peminjam Lunas</p>
                    <p class="text-2xl font-bold">{{ number_format($statistics['peminjam_lunas']) }}</p>
                    <p class="text-xs opacity-75">{{ number_format($statistics['completion_rate'], 1) }}% completion</p>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg p-6 transform hover:scale-105 transition-all duration-200 shadow-lg">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-white bg-opacity-20">
                    <i class="fas fa-clock text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm opacity-90">Belum Lunas</p>
                    <p class="text-2xl font-bold">{{ number_format($statistics['peminjam_belum']) }}</p>
                    <p class="text-xs opacity-75">{{ number_format($statistics['overdue_rate'], 1) }}% overdue</p>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-lg p-6 transform hover:scale-105 transition-all duration-200 shadow-lg">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-white bg-opacity-20">
                    <i class="fas fa-percentage text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm opacity-90">Tingkat Pelunasan</p>
                    <p class="text-2xl font-bold">{{ number_format($statistics['completion_rate'], 1) }}%</p>
                    <p class="text-xs opacity-75">Collection rate</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="mb-6">
        <h3 class="text-2xl font-bold text-gray-800 mb-2 text-center">
            Laporan Kas Pinjaman
        </h3>
        <p class="text-center text-gray-600 mb-6">
            <strong>Periode:</strong> {{ \Carbon\Carbon::parse($tgl_dari)->format('d F Y') }} s/d {{ \Carbon\Carbon::parse($tgl_samp)->format('d F Y') }}
        </p>
        
        <!-- Financial Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 text-white rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm opacity-90">Total Pinjaman</p>
                        <p class="text-2xl font-bold">Rp {{ number_format($data['jml_pinjaman']) }}</p>
                    </div>
                    <i class="fas fa-hand-holding-usd text-3xl opacity-50"></i>
                </div>
            </div>
            
            <div class="bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm opacity-90">Sudah Dibayar</p>
                        <p class="text-2xl font-bold">Rp {{ number_format($data['jml_angsuran']) }}</p>
                        <p class="text-xs opacity-75">{{ number_format($data['collection_rate'], 1) }}% collected</p>
                    </div>
                    <i class="fas fa-check-circle text-3xl opacity-50"></i>
                </div>
            </div>
            
            <div class="bg-gradient-to-r from-red-500 to-red-600 text-white rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm opacity-90">Sisa Tagihan</p>
                        <p class="text-2xl font-bold">Rp {{ number_format($data['sisa_tagihan']) }}</p>
                        <p class="text-xs opacity-75">Outstanding</p>
                    </div>
                    <i class="fas fa-exclamation-triangle text-3xl opacity-50"></i>
                </div>
            </div>
        </div>

        <!-- Main Report Table -->
        <div class="overflow-x-auto bg-white rounded-lg shadow-lg">
            <table class="min-w-full border border-gray-200">
                <thead class="bg-gradient-to-r from-blue-600 to-blue-700 text-white">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider">No</th>
                        <th class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider">Keterangan</th>
                        <th class="px-6 py-4 text-right text-xs font-medium uppercase tracking-wider">Jumlah (Rp)</th>
                        <th class="px-6 py-4 text-center text-xs font-medium uppercase tracking-wider">Persentase</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">1</td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <div class="flex items-center">
                                <i class="fas fa-hand-holding-usd text-blue-500 mr-2"></i>
                                Pokok Pinjaman
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-right font-semibold text-gray-900">
                            Rp {{ number_format($data['jml_pinjaman']) }}
                        </td>
                        <td class="px-6 py-4 text-sm text-center text-blue-600 font-semibold">
                            {{ $data['tot_tagihan'] > 0 ? number_format(($data['jml_pinjaman'] / $data['tot_tagihan']) * 100, 1) : 0 }}%
                        </td>
                    </tr>
                    
                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">2</td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
                                Tagihan Denda
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-right font-semibold text-red-600">
                            Rp {{ number_format($data['jml_denda']) }}
                        </td>
                        <td class="px-6 py-4 text-sm text-center text-red-600 font-semibold">
                            {{ $data['tot_tagihan'] > 0 ? number_format(($data['jml_denda'] / $data['tot_tagihan']) * 100, 1) : 0 }}%
                        </td>
                    </tr>
                    
                    <tr class="bg-gray-100 hover:bg-gray-200 transition-colors duration-200">
                        <td class="px-6 py-4 text-sm font-bold text-gray-900"></td>
                        <td class="px-6 py-4 text-sm font-bold text-gray-900">
                            <div class="flex items-center">
                                <i class="fas fa-calculator text-gray-600 mr-2"></i>
                                Jumlah Tagihan + Denda
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-right font-bold text-gray-900">
                            Rp {{ number_format($data['tot_tagihan']) }}
                        </td>
                        <td class="px-6 py-4 text-sm text-center text-gray-600 font-bold">100.0%</td>
                    </tr>
                    
                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">3</td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <div class="flex items-center">
                                <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                Tagihan Sudah Dibayar
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-right font-semibold text-green-600">
                            Rp {{ number_format($data['jml_angsuran']) }}
                        </td>
                        <td class="px-6 py-4 text-sm text-center text-green-600 font-semibold">
                            {{ $data['tot_tagihan'] > 0 ? number_format(($data['jml_angsuran'] / $data['tot_tagihan']) * 100, 1) : 0 }}%
                        </td>
                    </tr>
                    
                    <tr class="bg-green-100 hover:bg-green-200 transition-colors duration-200">
                        <td class="px-6 py-4 text-sm font-bold text-gray-900">4</td>
                        <td class="px-6 py-4 text-sm font-bold text-gray-900">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-circle text-green-600 mr-2"></i>
                                Sisa Tagihan
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-right font-bold text-green-700">
                            Rp {{ number_format($data['sisa_tagihan']) }}
                        </td>
                        <td class="px-6 py-4 text-sm text-center text-green-700 font-bold">
                            {{ $data['tot_tagihan'] > 0 ? number_format(($data['sisa_tagihan'] / $data['tot_tagihan']) * 100, 1) : 0 }}%
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Recent Loans Section -->
        @if(count($recentLoans) > 0)
        <div class="mt-8">
            <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-history text-blue-500 mr-2"></i>
                Pinjaman Terbaru
            </h4>
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Pinjaman</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Anggota</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($recentLoans as $loan)
                            <tr class="hover:bg-gray-50 transition-colors duration-200">
                                <td class="px-6 py-4 text-sm font-mono text-blue-600">{{ $loan['id'] }}</td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $loan['anggota'] }}</td>
                                <td class="px-6 py-4 text-sm text-right font-semibold text-gray-900">
                                    Rp {{ number_format($loan['jumlah']) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-center text-gray-500">{{ $loan['tgl_pinjam'] }}</td>
                                <td class="px-6 py-4 text-sm text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $loan['status_badge'] == 'success' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ $loan['status'] }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Summary Footer -->
    <div class="mt-6 p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg border border-gray-200">
        <div class="flex flex-wrap justify-between items-center text-sm text-gray-600">
            <div class="flex items-center">
                <i class="fas fa-calendar mr-2 text-blue-500"></i>
                <span class="font-medium">Periode:</span> {{ \Carbon\Carbon::parse($tgl_dari)->format('d F Y') }} - {{ \Carbon\Carbon::parse($tgl_samp)->format('d F Y') }}
            </div>
            <div class="flex items-center">
                <i class="fas fa-users mr-2 text-green-500"></i>
                <span class="font-medium">Total Peminjam:</span> {{ number_format($statistics['peminjam_aktif']) }} anggota
            </div>
            <div class="flex items-center">
                <i class="fas fa-percentage mr-2 text-purple-500"></i>
                <span class="font-medium">Tingkat Pelunasan:</span> {{ number_format($statistics['completion_rate'], 1) }}%
            </div>
        </div>
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