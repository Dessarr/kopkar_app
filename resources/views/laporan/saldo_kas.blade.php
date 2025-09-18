@extends('layouts.app')

@section('title', 'Laporan Saldo Kas')
@section('sub-title', 'Laporan')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <!-- Collapsible Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-3">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <i class="fas fa-cash-register text-blue-600 text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Laporan Saldo Kas</h1>
                    <p class="text-gray-600">Monitoring dan analisis saldo kas per periode</p>
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
                <form method="GET" action="{{ route('laporan.saldo_kas') }}" class="flex flex-wrap gap-4 items-end">
                    <div class="flex-1 min-w-[200px]">
                        <label for="periode" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar-alt mr-2"></i>Periode
                        </label>
                        <input type="month" id="periode" name="periode" value="{{ $periode }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="px-4 py-2 bg-[#14AE5C] text-white rounded-md hover:bg-[#11994F] transition-colors duration-200">
                            <i class="fas fa-search mr-2"></i>Filter
                        </button>
                        <a href="{{ route('laporan.saldo_kas') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors duration-200">
                            <i class="fas fa-refresh mr-2"></i>Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- Export Buttons -->
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('laporan.saldo_kas.export.pdf', ['periode' => $periode]) }}" 
                   class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors duration-200">
                    <i class="fas fa-file-pdf mr-2"></i>Export PDF
                </a>
                <a href="{{ route('laporan.saldo_kas.export.excel', ['periode' => $periode]) }}" 
                   class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors duration-200">
                    <i class="fas fa-file-excel mr-2"></i>Export Excel
                </a>
                <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-200">
                    <i class="fas fa-print mr-2"></i>Print
                </button>
            </div>
        </div>
    </div>


    <!-- Main Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Detail Saldo Kas</h3>
            <p class="text-sm text-gray-600">Periode: {{ \Carbon\Carbon::parse($periode . '-01')->format('F Y') }}</p>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Kas</th>
                        <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Saldo</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr class="bg-gray-100 font-bold">
                        <td class="px-6 py-5 text-center"></td>
                        <td class="px-6 py-5 text-left font-bold">SALDO PERIODE SEBELUMNYA</td>
                        <td class="px-6 py-5 text-right font-bold text-gray-700">{{ number_format($saldo_sblm, 0, ',', '.') }}</td>
                    </tr>
                    @foreach($data as $row)
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-900">{{ $row['no'] }}</td>
                        <td class="px-6 py-5 whitespace-nowrap text-sm font-medium text-gray-900">{{ $row['nama'] }}</td>
                        <td class="px-6 py-5 whitespace-nowrap text-sm text-right font-medium {{ $row['saldo'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ number_format($row['saldo'], 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                    <tr class="bg-gray-100 font-bold">
                        <td class="px-6 py-5 text-center"></td>
                        <td class="px-6 py-5 text-left font-bold">Jumlah</td>
                        <td class="px-6 py-5 text-right font-bold text-gray-700">{{ number_format($total, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="bg-green-100 font-bold">
                        <td class="px-6 py-5 text-center"></td>
                        <td class="px-6 py-5 text-left font-bold">Saldo</td>
                        <td class="px-6 py-5 text-right font-bold text-green-700">{{ number_format($total + $saldo_sblm, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

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
</script>
@endsection 