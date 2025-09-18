@extends('layouts.app')

@section('title', 'Laporan Neraca Saldo')
@section('sub-title', 'Laporan')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <!-- Header Panel -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg p-4 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas fa-balance-scale text-2xl mr-3"></i>
                <div>
                    <h2 class="text-xl font-bold">Laporan Neraca Saldo</h2>
                    <p class="text-blue-100 text-sm">Laporan keseimbangan debet dan kredit semua akun</p>
                </div>
            </div>
            <button onclick="toggleFilter()" class="bg-white bg-opacity-20 hover:bg-opacity-30 rounded-lg p-2 transition-all duration-200">
                <i class="fas fa-chevron-down text-lg" id="filterIcon"></i>
            </button>
        </div>
    </div>

    <!-- Filter Section -->
    <div id="filterSection" class="mb-6 bg-gray-50 rounded-lg p-4 border border-gray-200">
        <form method="GET" action="{{ route('laporan.neraca_saldo') }}" class="space-y-4">
            <!-- Filter Controls -->
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
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-clock mr-1"></i>Preset Periode
                </label>
                <div class="flex flex-wrap gap-2">
                    <button type="button" onclick="setPreset('today')" class="px-3 py-1 bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200 transition-colors duration-200 text-sm">
                        Hari ini
                    </button>
                    <button type="button" onclick="setPreset('yesterday')" class="px-3 py-1 bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200 transition-colors duration-200 text-sm">
                        Kemarin
                    </button>
                    <button type="button" onclick="setPreset('week')" class="px-3 py-1 bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200 transition-colors duration-200 text-sm">
                        7 Hari yang lalu
                    </button>
                    <button type="button" onclick="setPreset('month')" class="px-3 py-1 bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200 transition-colors duration-200 text-sm">
                        Bulan ini
                    </button>
                    <button type="button" onclick="setPreset('lastmonth')" class="px-3 py-1 bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200 transition-colors duration-200 text-sm">
                        Bulan kemarin
                    </button>
                    <button type="button" onclick="setPreset('year')" class="px-3 py-1 bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200 transition-colors duration-200 text-sm">
                        Tahun ini
                    </button>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap gap-2 pt-4 border-t border-gray-200">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-200 flex items-center">
                    <i class="fas fa-search mr-2"></i>Filter
                </button>
                <a href="{{ route('laporan.neraca_saldo') }}" class="px-6 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors duration-200 flex items-center">
                    <i class="fas fa-refresh mr-2"></i>Hapus Filter
                </a>
            </div>
        </form>
    </div>

    <!-- Export Buttons -->
    <div class="mb-6 flex flex-wrap gap-2">
        <a href="{{ route('laporan.neraca_saldo.export.pdf', ['tgl_dari' => $tgl_dari, 'tgl_samp' => $tgl_samp]) }}" 
           class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors duration-200 flex items-center">
            <i class="fas fa-file-pdf mr-2"></i>Cetak Laporan (PDF)
        </a>
    </div>


    <!-- Main Content -->
    <div class="mb-6">
        <h3 class="text-2xl font-bold text-gray-800 mb-2 text-center">
            Laporan Neraca Saldo
        </h3>
        <p class="text-center text-gray-600 mb-6">
            <strong>Periode:</strong> {{ \Carbon\Carbon::parse($tgl_dari)->format('d F Y') }} - {{ \Carbon\Carbon::parse($tgl_samp)->format('d F Y') }}
        </p>
        
        @if(count($data['rows']) > 0)
        <div class="overflow-x-auto bg-white rounded-lg shadow-lg">
            <table class="min-w-full border border-gray-200">
                <thead class="bg-gradient-to-r from-blue-600 to-blue-700 text-white">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Kode Akun</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Nama Akun</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider">Debet</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider">Kredit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($data['rows'] as $row)
                    <tr class="{{ isset($row['is_header']) && $row['is_header'] ? 'bg-gray-100 font-bold border-b-2 border-gray-300' : 'hover:bg-blue-50 transition-colors duration-200' }}">
                        <td class="px-4 py-3 text-sm {{ isset($row['is_header']) && $row['is_header'] ? 'text-gray-800' : 'text-gray-900' }}">
                            @if(isset($row['is_header']) && $row['is_header'])
                                <i class="fas fa-folder mr-2 text-blue-600"></i>
                            @else
                                <i class="fas fa-file-invoice mr-2 text-gray-500"></i>
                            @endif
                            {{ $row['no'] }}
                        </td>
                        <td class="px-4 py-3 text-sm {{ isset($row['is_header']) && $row['is_header'] ? 'text-gray-800 font-bold' : 'text-gray-900' }}">
                            {{ $row['nama'] }}
                        </td>
                        <td class="px-4 py-3 text-sm text-right {{ $row['debet'] > 0 ? 'text-green-600 font-semibold' : 'text-gray-400' }}">
                            {{ $row['debet'] > 0 ? 'Rp ' . number_format($row['debet'], 0, ',', '.') : '-' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-right {{ $row['kredit'] > 0 ? 'text-red-600 font-semibold' : 'text-gray-400' }}">
                            {{ $row['kredit'] > 0 ? 'Rp ' . number_format($row['kredit'], 0, ',', '.') : '-' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gradient-to-r from-gray-100 to-gray-200">
                    <tr>
                        <td class="px-4 py-3 text-sm font-bold text-gray-900" colspan="2">
                            <i class="fas fa-calculator mr-2 text-gray-600"></i>
                            <span class="font-bold text-gray-800">JUMLAH</span>
                        </td>
                        <td class="px-4 py-3 text-sm font-bold text-right text-green-600">
                            Rp {{ number_format($data['totalDebet'], 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-sm font-bold text-right text-red-600">
                            Rp {{ number_format($data['totalKredit'], 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @else
        <div class="text-center py-12 bg-gray-50 rounded-lg">
            <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada data neraca saldo</h3>
            <p class="text-gray-500">Tidak ada transaksi untuk periode <strong>{{ \Carbon\Carbon::parse($tgl_dari)->format('d F Y') }} - {{ \Carbon\Carbon::parse($tgl_samp)->format('d F Y') }}</strong></p>
        </div>
        @endif
    </div>

    <!-- Balance Check Info -->
    @if(count($data['rows']) > 0)
    <div class="mt-6 p-4 {{ $data['is_balanced'] ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }} rounded-lg">
        <div class="flex items-center">
            <i class="fas {{ $data['is_balanced'] ? 'fa-check-circle text-green-500' : 'fa-exclamation-triangle text-red-500' }} text-xl mr-3"></i>
            <div>
                <h4 class="font-bold {{ $data['is_balanced'] ? 'text-green-800' : 'text-red-800' }}">
                    {{ $data['is_balanced'] ? 'Neraca Saldo Seimbang' : 'Neraca Saldo Tidak Seimbang' }}
                </h4>
                <p class="text-sm {{ $data['is_balanced'] ? 'text-green-600' : 'text-red-600' }}">
                    @if($data['is_balanced'])
                        Total Debet (Rp {{ number_format($data['totalDebet']) }}) = Total Kredit (Rp {{ number_format($data['totalKredit']) }})
                    @else
                        Selisih: Rp {{ number_format(abs($data['totalDebet'] - $data['totalKredit'])) }}
                    @endif
                </p>
            </div>
        </div>
    </div>
    @endif

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

    // Preset functions
    window.setPreset = function(preset) {
        const today = new Date();
        const tglDariInput = document.getElementById('tgl_dari');
        const tglSampInput = document.getElementById('tgl_samp');
        
        switch(preset) {
            case 'today':
                tglDariInput.value = today.toISOString().split('T')[0];
                tglSampInput.value = today.toISOString().split('T')[0];
                break;
            case 'yesterday':
                const yesterday = new Date(today);
                yesterday.setDate(yesterday.getDate() - 1);
                tglDariInput.value = yesterday.toISOString().split('T')[0];
                tglSampInput.value = yesterday.toISOString().split('T')[0];
                break;
            case 'week':
                const weekAgo = new Date(today);
                weekAgo.setDate(weekAgo.getDate() - 7);
                tglDariInput.value = weekAgo.toISOString().split('T')[0];
                tglSampInput.value = today.toISOString().split('T')[0];
                break;
            case 'month':
                const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
                tglDariInput.value = firstDay.toISOString().split('T')[0];
                tglSampInput.value = today.toISOString().split('T')[0];
                break;
            case 'lastmonth':
                const lastMonth = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                const lastDayOfLastMonth = new Date(today.getFullYear(), today.getMonth(), 0);
                tglDariInput.value = lastMonth.toISOString().split('T')[0];
                tglSampInput.value = lastDayOfLastMonth.toISOString().split('T')[0];
                break;
            case 'year':
                const firstDayOfYear = new Date(today.getFullYear(), 0, 1);
                tglDariInput.value = firstDayOfYear.toISOString().split('T')[0];
                tglSampInput.value = today.toISOString().split('T')[0];
                break;
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
        if (!row.classList.contains('bg-gray-100')) {
            row.addEventListener('mouseenter', function() {
                this.style.transform = 'scale(1.01)';
                this.style.transition = 'transform 0.2s ease';
            });
            
            row.addEventListener('mouseleave', function() {
                this.style.transform = 'scale(1)';
            });
        }
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