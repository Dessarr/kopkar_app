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

    <!-- Summary Cards -->
    @if(isset($summary))
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm">Total Saldo Akhir</p>
                    <p class="text-2xl font-bold">Rp {{ number_format($summary['total_saldo_akhir'], 0, ',', '.') }}</p>
                </div>
                <i class="fas fa-wallet text-3xl text-blue-200"></i>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm">Total Debet</p>
                    <p class="text-2xl font-bold">Rp {{ number_format($summary['total_debet'], 0, ',', '.') }}</p>
                </div>
                <i class="fas fa-arrow-down text-3xl text-green-200"></i>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-red-500 to-red-600 rounded-lg p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-red-100 text-sm">Total Kredit</p>
                    <p class="text-2xl font-bold">Rp {{ number_format($summary['total_kredit'], 0, ',', '.') }}</p>
                </div>
                <i class="fas fa-arrow-up text-3xl text-red-200"></i>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm">Net Cash Flow</p>
                    <p class="text-2xl font-bold {{ $summary['net_cash_flow'] >= 0 ? 'text-green-200' : 'text-red-200' }}">
                        Rp {{ number_format($summary['net_cash_flow'], 0, ',', '.') }}
                    </p>
                </div>
                <i class="fas fa-exchange-alt text-3xl text-purple-200"></i>
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
                    <p class="text-gray-600 text-sm">Rasio Likuiditas</p>
                    <p class="text-xl font-bold text-blue-600">{{ number_format($performance['liquidity_ratio'], 1) }}%</p>
                </div>
                <i class="fas fa-percentage text-2xl text-blue-500"></i>
            </div>
        </div>
        
        <div class="bg-white border border-gray-200 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Efisiensi Kas</p>
                    <p class="text-xl font-bold text-green-600">{{ number_format($performance['cash_efficiency'], 1) }}%</p>
                </div>
                <i class="fas fa-chart-line text-2xl text-green-500"></i>
            </div>
        </div>
        
        <div class="bg-white border border-gray-200 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Tingkat Pertumbuhan</p>
                    <p class="text-xl font-bold {{ $performance['growth_rate'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $performance['growth_rate'] >= 0 ? '+' : '' }}{{ number_format($performance['growth_rate'], 1) }}%
                    </p>
                </div>
                <i class="fas fa-trending-up text-2xl {{ $performance['growth_rate'] >= 0 ? 'text-green-500' : 'text-red-500' }}"></i>
            </div>
        </div>
        
        <div class="bg-white border border-gray-200 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Konsentrasi Kas</p>
                    <p class="text-xl font-bold text-purple-600">{{ number_format($performance['cash_concentration'], 1) }}%</p>
                </div>
                <i class="fas fa-bullseye text-2xl text-purple-500"></i>
            </div>
        </div>
    </div>
    @endif

    <!-- Cash Status Overview -->
    @if(isset($summary))
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-800 text-sm font-medium">Akun Surplus</p>
                    <p class="text-2xl font-bold text-green-600">{{ $summary['surplus_count'] }}</p>
                </div>
                <i class="fas fa-arrow-up text-2xl text-green-500"></i>
            </div>
        </div>
        
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-red-800 text-sm font-medium">Akun Defisit</p>
                    <p class="text-2xl font-bold text-red-600">{{ $summary['deficit_count'] }}</p>
                </div>
                <i class="fas fa-arrow-down text-2xl text-red-500"></i>
            </div>
        </div>
        
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-800 text-sm font-medium">Saldo Tertinggi</p>
                    <p class="text-lg font-bold text-blue-600">Rp {{ number_format($summary['highest_balance'], 0, ',', '.') }}</p>
                </div>
                <i class="fas fa-trophy text-2xl text-blue-500"></i>
            </div>
        </div>
    </div>
    @endif

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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Kas</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Debet</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Kredit</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Saldo</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr class="bg-gray-100 font-bold">
                        <td class="px-6 py-4 text-center" colspan="2">SALDO PERIODE SEBELUMNYA</td>
                        <td class="px-6 py-4 text-right">-</td>
                        <td class="px-6 py-4 text-right">-</td>
                        <td class="px-6 py-4 text-right font-bold text-gray-700">{{ number_format($saldo_sblm, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-center">-</td>
                    </tr>
                    @foreach($data as $row)
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $row['no'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $row['nama'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-green-600">
                            {{ number_format($row['debet'], 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-red-600">
                            {{ number_format($row['kredit'], 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium {{ $row['saldo'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ number_format($row['saldo'], 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $row['status_badge'] }}">
                                {{ $row['status'] }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                    <tr class="bg-gray-100 font-bold">
                        <td class="px-6 py-4 text-center" colspan="2">JUMLAH</td>
                        <td class="px-6 py-4 text-right text-green-600">{{ number_format(array_sum(array_column($data, 'debet')), 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-right text-red-600">{{ number_format(array_sum(array_column($data, 'kredit')), 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-right font-bold text-gray-700">{{ number_format($total, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-center">-</td>
                    </tr>
                    <tr class="bg-green-100 font-bold">
                        <td class="px-6 py-4 text-center" colspan="2">TOTAL SALDO</td>
                        <td class="px-6 py-4 text-right">-</td>
                        <td class="px-6 py-4 text-right">-</td>
                        <td class="px-6 py-4 text-right font-bold text-green-700">{{ number_format($total + $saldo_sblm, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-center">-</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Transactions -->
    @if(isset($recentTransactions) && $recentTransactions->count() > 0)
    <div class="mt-8 bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Transaksi Kas Terbaru</h3>
            <p class="text-sm text-gray-600">10 transaksi terbaru dalam periode ini</p>
        </div>
        
        <div class="divide-y divide-gray-200">
            @foreach($recentTransactions as $transaction)
            <div class="px-6 py-4 hover:bg-gray-50 transition-colors duration-150">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-{{ $transaction['tipe'] == 'Masuk' ? 'green' : 'red' }}-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-{{ $transaction['tipe'] == 'Masuk' ? 'arrow-down' : 'arrow-up' }} text-{{ $transaction['tipe'] == 'Masuk' ? 'green' : 'red' }}-600 text-sm"></i>
                            </div>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $transaction['keterangan'] }}</p>
                            <p class="text-sm text-gray-500">{{ $transaction['tanggal'] }} • {{ $transaction['dari_kas'] }} → {{ $transaction['untuk_kas'] }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium {{ $transaction['tipe'] == 'Masuk' ? 'text-green-600' : 'text-red-600' }}">
                            {{ $transaction['tipe'] == 'Masuk' ? '+' : '-' }}Rp {{ number_format($transaction['jumlah'], 0, ',', '.') }}
                        </p>
                        <p class="text-xs text-gray-500">{{ $transaction['jenis_akun'] }}</p>
                    </div>
                </div>
            </div>
            @endforeach
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
</script>
@endsection 