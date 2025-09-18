@extends('layouts.app')

@section('title', 'Laporan Rekapitulasi Tagihan')
@section('sub-title', 'Laporan')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <!-- Header Section -->
    <div class="mb-6">
        <div class="bg-gradient-to-r from-purple-600 to-purple-700 text-white p-4 rounded-lg shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Laporan Rekapitulasi Tagihan</h1>
                    <p class="text-purple-100 mt-1">Analisis target vs realisasi pembayaran pinjaman harian</p>
                </div>
                <div class="text-right">
                    <div class="text-sm text-purple-100">Periode Laporan</div>
                    <div class="text-lg font-semibold">
                        {{ \Carbon\Carbon::parse($periode . '-01')->format('F Y') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="mb-6">
        <div class="bg-gray-50 rounded-lg p-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-filter mr-2 text-purple-600"></i>Filter Laporan
                </h3>
                <button onclick="toggleFilter()" class="text-purple-600 hover:text-purple-800 transition-colors">
                    <i class="fas fa-chevron-down" id="filter-icon"></i>
                </button>
            </div>
            
            <div id="filter-content" class="hidden">
                <form method="GET" action="{{ route('laporan.rekapitulasi') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label for="periode" class="block text-sm font-medium text-gray-700 mb-2">Periode</label>
                            <input type="month" id="periode" name="periode" value="{{ $periode }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Preset Periode</label>
                            <select onchange="setPreset(this.value)" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <option value="">Pilih Preset</option>
                                <option value="this_month">Bulan ini</option>
                                <option value="last_month">Bulan kemarin</option>
                                <option value="this_year">Tahun ini</option>
                                <option value="last_year">Tahun kemarin</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="w-full px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition-colors duration-200">
                                <i class="fas fa-search mr-2"></i>Filter
                            </button>
                        </div>
                        <div class="flex items-end">
                            <a href="{{ route('laporan.rekapitulasi') }}" class="w-full px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors duration-200 text-center">
                                <i class="fas fa-refresh mr-2"></i>Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="mb-6 flex flex-wrap gap-3">
        <a href="{{ route('laporan.rekapitulasi.export.pdf', ['periode' => $periode]) }}" 
           class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors duration-200 shadow-sm">
            <i class="fas fa-file-pdf mr-2"></i>Export PDF
        </a>
    </div>


    <!-- Main Report Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-table mr-2 text-purple-600"></i>Data Rekapitulasi Harian
            </h3>
            <p class="text-sm text-gray-600 mt-1">Detail target vs realisasi pembayaran periode {{ \Carbon\Carbon::parse($periode . '-01')->format('F Y') }}</p>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Tagihan Hari Ini</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Target Pokok</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Target Bunga</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Tagihan Masuk</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Realisasi Pokok</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Realisasi Bunga</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Tagihan Bermasalah</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Tidak Bayar Pokok</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Tidak Bayar Bunga</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">% Koleksi</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($data as $row)
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $row['no'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ \Carbon\Carbon::parse($row['tanggal'])->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">{{ $row['jml_tagihan'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">Rp {{ number_format($row['target_pokok'], 0, ',', '.') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">Rp {{ number_format($row['target_bunga'], 0, ',', '.') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">{{ $row['tagihan_masuk'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">Rp {{ number_format($row['realisasi_pokok'], 0, ',', '.') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">Rp {{ number_format($row['realisasi_bunga'], 0, ',', '.') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">{{ $row['tagihan_bermasalah'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">Rp {{ number_format($row['tidak_bayar_pokok'], 0, ',', '.') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">Rp {{ number_format($row['tidak_bayar_bunga'], 0, ',', '.') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">{{ number_format($row['persentase_koleksi'], 1) }}%</td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($row['status'] == 'Sempurna') bg-green-100 text-green-800
                                @elseif($row['status'] == 'Sangat Baik') bg-blue-100 text-blue-800
                                @elseif($row['status'] == 'Baik') bg-indigo-100 text-indigo-800
                                @elseif($row['status'] == 'Cukup') bg-yellow-100 text-yellow-800
                                @else bg-red-100 text-red-800
                                @endif">
                                {{ $row['status'] }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="13" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-4"></i>
                            <div class="text-lg font-medium">Tidak ada data rekapitulasi</div>
                            <div class="text-sm">Tidak ada data untuk periode yang dipilih</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if(count($data) > 0)
                <tfoot class="bg-gray-50">
                    <tr class="font-semibold">
                        <td class="px-6 py-4 text-center" colspan="2">TOTAL</td>
                        <td class="px-6 py-4 text-center">{{ array_sum(array_column($data, 'jml_tagihan')) }}</td>
                        <td class="px-6 py-4 text-right">Rp {{ number_format(array_sum(array_column($data, 'target_pokok')), 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-right">Rp {{ number_format(array_sum(array_column($data, 'target_bunga')), 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-center">{{ array_sum(array_column($data, 'tagihan_masuk')) }}</td>
                        <td class="px-6 py-4 text-right">Rp {{ number_format(array_sum(array_column($data, 'realisasi_pokok')), 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-right">Rp {{ number_format(array_sum(array_column($data, 'realisasi_bunga')), 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-center">{{ array_sum(array_column($data, 'tagihan_bermasalah')) }}</td>
                        <td class="px-6 py-4 text-right">Rp {{ number_format(array_sum(array_column($data, 'tidak_bayar_pokok')), 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-right">Rp {{ number_format(array_sum(array_column($data, 'tidak_bayar_bunga')), 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-center">{{ number_format(!empty($data) ? array_sum(array_column($data, 'persentase_koleksi')) / count($data) : 0, 1) }}%</td>
                        <td class="px-6 py-4 text-center">BULANAN</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

</div>

<script>
function toggleFilter() {
    const content = document.getElementById('filter-content');
    const icon = document.getElementById('filter-icon');
    
    if (content.classList.contains('hidden')) {
        content.classList.remove('hidden');
        icon.classList.remove('fa-chevron-down');
        icon.classList.add('fa-chevron-up');
    } else {
        content.classList.add('hidden');
        icon.classList.remove('fa-chevron-up');
        icon.classList.add('fa-chevron-down');
    }
}

function setPreset(value) {
    const today = new Date();
    const periode = document.getElementById('periode');
    
    switch(value) {
        case 'this_month':
            periode.value = today.toISOString().slice(0, 7);
            break;
        case 'last_month':
            const lastMonth = new Date(today.getFullYear(), today.getMonth() - 1, 1);
            periode.value = lastMonth.toISOString().slice(0, 7);
            break;
        case 'this_year':
            periode.value = today.getFullYear() + '-01';
            break;
        case 'last_year':
            periode.value = (today.getFullYear() - 1) + '-01';
            break;
    }
}

// Smooth scrolling for better UX
document.addEventListener('DOMContentLoaded', function() {
    // Add smooth scrolling to all anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Add hover effects to table rows
    const tableRows = document.querySelectorAll('tbody tr');
    tableRows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-1px)';
            this.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';
        });
        
        row.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = 'none';
        });
    });
});
</script>
@endsection