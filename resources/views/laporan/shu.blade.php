@extends('layouts.app')

@section('title', 'Laporan SHU')
@section('sub-title', 'Laporan')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <!-- Collapsible Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-3">
                <div class="p-2 bg-green-100 rounded-lg">
                    <i class="fas fa-chart-pie text-green-600 text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Laporan Sisa Hasil Usaha (SHU)</h1>
                    <p class="text-gray-600">Analisis dan distribusi keuntungan koperasi</p>
                </div>
            </div>
            <button onclick="toggleCollapse()" class="p-2 text-gray-500 hover:text-gray-700 transition-colors duration-200">
                <i class="fas fa-chevron-down text-xl" id="collapse-icon"></i>
            </button>
        </div>
        
        <!-- Collapsible Content -->
        <div id="collapsible-content" class="space-y-4">
            <!-- Filter Section -->
            <div class="bg-gray-50 rounded-lg p-4">
                <form method="GET" action="{{ route('laporan.shu') }}" class="flex flex-wrap gap-4 items-end">
                    <div class="flex-1 min-w-[200px]">
                        <label for="tgl_dari" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar-alt mr-2"></i>Tanggal Dari
                        </label>
                        <input type="date" id="tgl_dari" name="tgl_dari" value="{{ $tgl_dari }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent">
                    </div>
                    <div class="flex-1 min-w-[200px]">
                        <label for="tgl_samp" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar-alt mr-2"></i>Tanggal Sampai
                        </label>
                        <input type="date" id="tgl_samp" name="tgl_samp" value="{{ $tgl_samp }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="px-4 py-2 bg-[#14AE5C] text-white rounded-md hover:bg-[#11994F] transition-colors duration-200">
                            <i class="fas fa-search mr-2"></i>Filter
                        </button>
                        <a href="{{ route('laporan.shu') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors duration-200">
                            <i class="fas fa-refresh mr-2"></i>Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- Export Buttons -->
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('laporan.shu.export.pdf', ['tgl_dari' => $tgl_dari, 'tgl_samp' => $tgl_samp]) }}" 
                   class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors duration-200">
                    <i class="fas fa-file-pdf mr-2"></i>Export PDF
                </a>
                <a href="{{ route('laporan.shu.export.excel', ['tgl_dari' => $tgl_dari, 'tgl_samp' => $tgl_samp]) }}" 
                   class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors duration-200">
                    <i class="fas fa-file-excel mr-2"></i>Export Excel
                </a>
                <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-200">
                    <i class="fas fa-print mr-2"></i>Print
                </button>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    @if(isset($summary))
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm">Total Pendapatan</p>
                    <p class="text-2xl font-bold">Rp {{ number_format($summary['total_pendapatan'], 0, ',', '.') }}</p>
                </div>
                <i class="fas fa-arrow-up text-3xl text-green-200"></i>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-red-500 to-red-600 rounded-lg p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-red-100 text-sm">Total Biaya</p>
                    <p class="text-2xl font-bold">Rp {{ number_format($summary['total_biaya'], 0, ',', '.') }}</p>
                </div>
                <i class="fas fa-arrow-down text-3xl text-red-200"></i>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm">SHU Sebelum Pajak</p>
                    <p class="text-2xl font-bold">Rp {{ number_format($summary['shu_sebelum_pajak'], 0, ',', '.') }}</p>
                </div>
                <i class="fas fa-calculator text-3xl text-blue-200"></i>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm">SHU Setelah Pajak</p>
                    <p class="text-2xl font-bold">Rp {{ number_format($summary['shu_setelah_pajak'], 0, ',', '.') }}</p>
                </div>
                <i class="fas fa-coins text-3xl text-purple-200"></i>
            </div>
        </div>
    </div>
    @endif

    <!-- Performance Metrics -->
    @if(isset($performance))
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white border border-gray-200 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Profit Margin</p>
                    <p class="text-xl font-bold text-green-600">{{ number_format($summary['profit_margin'], 1) }}%</p>
                </div>
                <i class="fas fa-percentage text-2xl text-green-500"></i>
            </div>
        </div>
        
        <div class="bg-white border border-gray-200 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Expense Ratio</p>
                    <p class="text-xl font-bold text-red-600">{{ number_format($summary['expense_ratio'], 1) }}%</p>
                </div>
                <i class="fas fa-chart-line text-2xl text-red-500"></i>
            </div>
        </div>
        
        <div class="bg-white border border-gray-200 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Tax Burden</p>
                    <p class="text-xl font-bold text-blue-600">{{ number_format($summary['tax_burden'], 1) }}%</p>
                </div>
                <i class="fas fa-receipt text-2xl text-blue-500"></i>
            </div>
        </div>
        
        <div class="bg-white border border-gray-200 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">SHU Efficiency</p>
                    <p class="text-xl font-bold text-purple-600">{{ number_format($performance['shu_efficiency'], 1) }}%</p>
                </div>
                <i class="fas fa-trophy text-2xl text-purple-500"></i>
            </div>
        </div>
    </div>
    @endif

    <!-- Income and Expense Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Income Section -->
        <div class="bg-white border border-gray-200 rounded-lg">
            <div class="bg-green-50 px-4 py-3 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-green-800 flex items-center">
                    <i class="fas fa-arrow-up mr-2"></i>Pendapatan
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Kode</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nama Akun</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @if(isset($data['pendapatan_rows']) && count($data['pendapatan_rows']) > 0)
                            @foreach($data['pendapatan_rows'] as $row)
                                <tr class="hover:bg-green-50 transition-colors duration-200">
                                    <td class="px-3 py-2 text-sm text-gray-900">{{ $row['kode_akun'] ?? '-' }}</td>
                                    <td class="px-3 py-2 text-sm text-gray-900">{{ $row['nama_akun'] ?? $row['nama'] }}</td>
                                    <td class="px-3 py-2 text-sm text-gray-900 text-right font-medium">Rp {{ number_format($row['jumlah'], 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="3" class="px-3 py-4 text-center text-gray-500">Tidak ada data pendapatan</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Expense Section -->
        <div class="bg-white border border-gray-200 rounded-lg">
            <div class="bg-red-50 px-4 py-3 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-red-800 flex items-center">
                    <i class="fas fa-arrow-down mr-2"></i>Biaya
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Kode</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nama Akun</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @if(isset($data['biaya_rows']) && count($data['biaya_rows']) > 0)
                            @foreach($data['biaya_rows'] as $row)
                                <tr class="hover:bg-red-50 transition-colors duration-200">
                                    <td class="px-3 py-2 text-sm text-gray-900">{{ $row['kode_akun'] ?? '-' }}</td>
                                    <td class="px-3 py-2 text-sm text-gray-900">{{ $row['nama_akun'] ?? $row['nama'] }}</td>
                                    <td class="px-3 py-2 text-sm text-gray-900 text-right font-medium">Rp {{ number_format($row['jumlah'], 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="3" class="px-3 py-4 text-center text-gray-500">Tidak ada data biaya</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- SHU Calculation Section -->
    <div class="bg-white border border-gray-200 rounded-lg mb-6">
        <div class="bg-blue-50 px-4 py-3 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-blue-800 flex items-center">
                <i class="fas fa-calculator mr-2"></i>Perhitungan SHU
            </h3>
        </div>
        <div class="p-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600 mb-1">Total Pendapatan</p>
                    <p class="text-xl font-bold text-green-600">Rp {{ number_format($data['total_pendapatan'], 0, ',', '.') }}</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600 mb-1">Total Biaya</p>
                    <p class="text-xl font-bold text-red-600">Rp {{ number_format($data['total_biaya'], 0, ',', '.') }}</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600 mb-1">SHU Sebelum Pajak</p>
                    <p class="text-xl font-bold text-blue-600">Rp {{ number_format($data['shu_sebelum_pajak'] ?? $data['shu'], 0, ',', '.') }}</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600 mb-1">Pajak PPH</p>
                    <p class="text-xl font-bold text-yellow-600">Rp {{ number_format($data['pajak_pph'] ?? 0, 0, ',', '.') }}</p>
                </div>
            </div>
            <div class="mt-4 p-4 bg-purple-50 rounded-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-purple-600 mb-1">SHU Setelah Pajak</p>
                        <p class="text-2xl font-bold text-purple-800">Rp {{ number_format($data['shu_setelah_pajak'] ?? $data['shu'], 0, ',', '.') }}</p>
                    </div>
                    <i class="fas fa-coins text-3xl text-purple-400"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- SHU Distribution Section -->
    @if(isset($distribution))
    <div class="bg-white border border-gray-200 rounded-lg mb-6">
        <div class="bg-purple-50 px-4 py-3 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-purple-800 flex items-center">
                <i class="fas fa-share-alt mr-2"></i>Distribusi SHU
            </h3>
        </div>
        <div class="p-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($distribution as $key => $item)
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-sm font-medium text-gray-700">{{ $item['label'] }}</p>
                            <span class="text-xs text-gray-500">{{ $item['percentage'] }}%</span>
                        </div>
                        <p class="text-lg font-bold text-gray-900">Rp {{ number_format($item['amount'], 0, ',', '.') }}</p>
                        @if(isset($item['sub_items']))
                            <div class="mt-2 space-y-1">
                                @foreach($item['sub_items'] as $subKey => $subItem)
                                    <div class="flex justify-between text-xs text-gray-600">
                                        <span>{{ $subItem['label'] }}</span>
                                        <span>Rp {{ number_format($subItem['amount'], 0, ',', '.') }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Recent Activities Section -->
    @if(isset($recent_activities) && count($recent_activities) > 0)
    <div class="bg-white border border-gray-200 rounded-lg mb-6">
        <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                <i class="fas fa-history mr-2"></i>Aktivitas Terbaru
            </h3>
        </div>
        <div class="p-4">
            <div class="space-y-3">
                @foreach($recent_activities as $activity)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="p-2 bg-blue-100 rounded-full">
                                <i class="fas fa-{{ $activity['icon'] }} text-blue-600"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $activity['description'] }}</p>
                                <p class="text-xs text-gray-500">{{ $activity['date'] }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-900">Rp {{ number_format($activity['amount'], 0, ',', '.') }}</p>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $activity['status_class'] }}">
                                {{ $activity['status'] }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>

<script>
function toggleCollapse() {
    const content = document.getElementById('collapsible-content');
    const icon = document.getElementById('collapse-icon');
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.classList.remove('fa-chevron-down');
        icon.classList.add('fa-chevron-up');
    } else {
        content.style.display = 'none';
        icon.classList.remove('fa-chevron-up');
        icon.classList.add('fa-chevron-down');
    }
}

// Initialize collapsible state
document.addEventListener('DOMContentLoaded', function() {
    const content = document.getElementById('collapsible-content');
    const icon = document.getElementById('collapse-icon');
    
    // Start with content visible
    content.style.display = 'block';
    icon.classList.remove('fa-chevron-down');
    icon.classList.add('fa-chevron-up');
});
</script>
@endsection 