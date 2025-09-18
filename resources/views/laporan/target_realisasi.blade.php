@extends('layouts.app')

@section('title', 'Laporan Target & Realisasi')
@section('sub-title', 'Laporan')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <!-- Header Panel -->
    <div class="bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-lg p-4 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas fa-chart-line text-2xl mr-3"></i>
                <div>
                    <h2 class="text-xl font-bold">Laporan Target & Realisasi</h2>
                    <p class="text-purple-100 text-sm">Analisis perbandingan target vs realisasi pinjaman anggota</p>
                </div>
            </div>
            <button onclick="toggleFilter()" class="bg-white bg-opacity-20 hover:bg-opacity-30 rounded-lg p-2 transition-all duration-200">
                <i class="fas fa-chevron-down text-lg" id="filterIcon"></i>
            </button>
        </div>
    </div>

    <!-- Filter Section -->
    <div id="filterSection" class="mb-6 bg-gray-50 rounded-lg p-4 border border-gray-200">
        <form method="GET" action="{{ route('laporan.target_realisasi') }}" class="space-y-4">
            <!-- Filter Controls -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="tgl_dari" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar-alt mr-1"></i>Tanggal Dari
                    </label>
                    <input type="date" id="tgl_dari" name="tgl_dari" value="{{ $tgl_dari }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>
                <div>
                    <label for="tgl_samp" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar-alt mr-1"></i>Tanggal Sampai
                    </label>
                    <input type="date" id="tgl_samp" name="tgl_samp" value="{{ $tgl_samp }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>
                <div class="flex items-end">
                    <div class="flex gap-2">
                        <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition-colors duration-200 flex items-center">
                            <i class="fas fa-search mr-2"></i>Filter
                        </button>
                        <a href="{{ route('laporan.target_realisasi') }}" class="px-6 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors duration-200 flex items-center">
                            <i class="fas fa-refresh mr-2"></i>Reset
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Quick Filter Presets -->
            <div class="flex flex-wrap gap-2">
                <button type="button" onclick="setPreset('today')" class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-sm hover:bg-purple-200 transition-colors">
                    <i class="fas fa-calendar mr-1"></i>Hari Ini
                </button>
                <button type="button" onclick="setPreset('yesterday')" class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm hover:bg-gray-200 transition-colors">
                    <i class="fas fa-calendar mr-1"></i>Kemarin
                </button>
                <button type="button" onclick="setPreset('week')" class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm hover:bg-blue-200 transition-colors">
                    <i class="fas fa-calendar mr-1"></i>7 Hari Lalu
                </button>
                <button type="button" onclick="setPreset('month')" class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm hover:bg-green-200 transition-colors">
                    <i class="fas fa-calendar mr-1"></i>30 Hari Lalu
                </button>
                <button type="button" onclick="setPreset('this_month')" class="px-3 py-1 bg-orange-100 text-orange-700 rounded-full text-sm hover:bg-orange-200 transition-colors">
                    <i class="fas fa-calendar mr-1"></i>Bulan Ini
                </button>
                <button type="button" onclick="setPreset('last_month')" class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm hover:bg-red-200 transition-colors">
                    <i class="fas fa-calendar mr-1"></i>Bulan Kemarin
                </button>
                <button type="button" onclick="setPreset('year')" class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-sm hover:bg-indigo-200 transition-colors">
                    <i class="fas fa-calendar mr-1"></i>Tahun Ini
                </button>
                <button type="button" onclick="setPreset('last_year')" class="px-3 py-1 bg-pink-100 text-pink-700 rounded-full text-sm hover:bg-pink-200 transition-colors">
                    <i class="fas fa-calendar mr-1"></i>Tahun Kemarin
                </button>
            </div>
        </form>
    </div>

    <!-- Export Buttons -->
    <div class="mb-6 flex flex-wrap gap-2">
        <a href="{{ route('laporan.target_realisasi.export.pdf', ['tgl_dari' => $tgl_dari, 'tgl_samp' => $tgl_samp]) }}" 
           class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors duration-200 flex items-center">
            <i class="fas fa-file-pdf mr-2"></i>Cetak Laporan (PDF)
        </a>
    </div>


    <!-- Main Content -->
    <div class="mb-6">
        <h3 class="text-2xl font-bold text-gray-800 mb-2 text-center">
            Laporan Target & Realisasi Pinjaman Anggota
        </h3>
        <p class="text-center text-gray-600 mb-6">
            <strong>Periode:</strong> {{ \Carbon\Carbon::parse($tgl_dari)->format('d F Y') }} s/d {{ \Carbon\Carbon::parse($tgl_samp)->format('d F Y') }}
        </p>
        
        @if(count($data) > 0)
        <!-- Main Report Table -->
        <div class="overflow-x-auto bg-white rounded-lg shadow-lg">
            <table class="min-w-full border border-gray-200">
                <thead class="bg-gradient-to-r from-purple-600 to-purple-700 text-white">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">No</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Tanggal Pinjam</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Nama</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Pokok Pinjaman</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Saldo Pinjaman</th>
                        <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider">JW</th>
                        <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider">%</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider">Pokok (Target)</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider">Bunga (Target)</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider">Admin (Target)</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider">Jumlah (Target)</th>
                        <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider">Angsuran Ke</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider">Pokok (Realisasi)</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider">Bunga (Realisasi)</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider">Denda</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider">Jumlah (Realisasi)</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider">Sisa Tagihan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($data as $row)
                    <tr class="hover:bg-purple-50 transition-colors duration-200 {{ $row['lunas'] == 'Lunas' ? 'bg-green-50' : '' }}">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $row['no'] }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $row['tgl_pinjam']->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $row['nama'] }}</td>
                        <td class="px-4 py-3 text-sm text-right font-semibold text-gray-900">
                            Rp {{ number_format($row['jumlah'], 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-sm text-right {{ $row['sisa_pokok'] < 0 ? 'text-red-600' : 'text-gray-600' }}">
                            Rp {{ number_format($row['sisa_pokok'], 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-sm text-center text-gray-600">
                            {{ $row['lama_angsuran'] }}
                        </td>
                        <td class="px-4 py-3 text-sm text-center text-gray-600">
                            {{ $row['bunga'] }}%
                        </td>
                        <td class="px-4 py-3 text-sm text-right text-gray-600">
                            Rp {{ number_format($row['pokok_angsuran'], 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-sm text-right text-gray-600">
                            Rp {{ number_format($row['pokok_bunga'], 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-sm text-right text-gray-600">
                            Rp {{ number_format($row['biaya_adm'], 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-sm text-right font-semibold text-gray-700">
                            Rp {{ number_format($row['target_angsuran_bulanan'], 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-sm text-center">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $row['bln_sudah_angsur'] > 0 ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $row['bln_sudah_angsur'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-right font-semibold text-green-600">
                            Rp {{ number_format($row['total_bayar'], 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-sm text-right text-orange-600">
                            Rp {{ number_format($row['bunga_ags'], 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-sm text-right text-red-600">
                            Rp {{ number_format($row['denda_rp'], 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-sm text-right font-bold text-green-700">
                            Rp {{ number_format($row['realisasi_pembayaran'], 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-sm text-right font-bold {{ $row['sisa_tagihan'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                            Rp {{ number_format($row['sisa_tagihan'], 0, ',', '.') }}
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
                        <td class="px-4 py-3 text-sm text-right font-bold text-gray-900">
                            Rp {{ number_format($summary['total_nilai_pinjaman'], 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-sm text-right font-bold text-gray-600" colspan="3">
                            <!-- Empty cells -->
                        </td>
                        <td class="px-4 py-3 text-sm text-right font-bold text-gray-700" colspan="4">
                            Rp {{ number_format($summary['total_target_angsuran'], 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-sm text-center font-bold text-gray-600">
                            {{ $summary['total_pinjaman'] }}
                        </td>
                        <td class="px-4 py-3 text-sm text-right font-bold text-green-700" colspan="4">
                            Rp {{ number_format($summary['total_realisasi'], 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-sm text-right font-bold text-red-600">
                            Rp {{ number_format($summary['total_sisa_tagihan'], 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @else
        <div class="text-center py-12 bg-gray-50 rounded-lg">
            <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada data pinjaman</h3>
            <p class="text-gray-500">Tidak ada pinjaman untuk periode <strong>{{ \Carbon\Carbon::parse($tgl_dari)->format('d F Y') }} - {{ \Carbon\Carbon::parse($tgl_samp)->format('d F Y') }}</strong></p>
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
            case 'today':
                startDate = today;
                endDate = today;
                break;
            case 'yesterday':
                startDate = new Date(today.getTime() - 24 * 60 * 60 * 1000);
                endDate = new Date(today.getTime() - 24 * 60 * 60 * 1000);
                break;
            case 'week':
                startDate = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000);
                endDate = today;
                break;
            case 'month':
                startDate = new Date(today.getTime() - 30 * 24 * 60 * 60 * 1000);
                endDate = today;
                break;
            case 'this_month':
                startDate = new Date(today.getFullYear(), today.getMonth(), 1);
                endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                break;
            case 'last_month':
                startDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                endDate = new Date(today.getFullYear(), today.getMonth(), 0);
                break;
            case 'year':
                startDate = new Date(today.getFullYear(), 0, 1);
                endDate = new Date(today.getFullYear(), 11, 31);
                break;
            case 'last_year':
                startDate = new Date(today.getFullYear() - 1, 0, 1);
                endDate = new Date(today.getFullYear() - 1, 11, 31);
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