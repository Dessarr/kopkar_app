@extends('layouts.app')

@section('title', 'Laporan Transaksi Kas')
@section('sub-title', 'Laporan')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <!-- Filter Section -->
    <div class="mb-6">
        <form method="GET" action="{{ route('laporan.transaksi.kas') }}" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label for="tgl_dari" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Dari</label>
                <input type="date" id="tgl_dari" name="tgl_dari" value="{{ $tgl_dari }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent">
            </div>
            <div class="flex-1 min-w-[200px]">
                <label for="tgl_samp" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Sampai</label>
                <input type="date" id="tgl_samp" name="tgl_samp" value="{{ $tgl_samp }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent">
            </div>
            <div class="flex-1 min-w-[200px]">
                <label for="jenis_transaksi" class="block text-sm font-medium text-gray-700 mb-2">Jenis Transaksi</label>
                <select id="jenis_transaksi" name="jenis_transaksi" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent">
                    <option value="semua" {{ $jenis_transaksi === 'semua' ? 'selected' : '' }}>Semua</option>
                    <option value="pemasukan" {{ $jenis_transaksi === 'pemasukan' ? 'selected' : '' }}>Pemasukan</option>
                    <option value="pengeluaran" {{ $jenis_transaksi === 'pengeluaran' ? 'selected' : '' }}>Pengeluaran</option>
                </select>
            </div>
            <div class="flex-1 min-w-[200px]">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Cari</label>
                <input type="text" id="search" name="search" value="{{ $search }}" 
                       placeholder="Keterangan atau nama kas"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent">
            </div>
            <div class="flex-1 min-w-[150px]">
                <label for="per_page" class="block text-sm font-medium text-gray-700 mb-2">Per Halaman</label>
                <select id="per_page" name="per_page" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent">
                    <option value="15" {{ $perPage == 15 ? 'selected' : '' }}>15</option>
                    <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-[#14AE5C] text-white rounded-md hover:bg-[#11994F] transition-colors duration-200">
                    <i class="fas fa-search mr-2"></i>Filter
                </button>
                <a href="{{ route('laporan.transaksi.kas') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors duration-200">
                    <i class="fas fa-refresh mr-2"></i>Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Export Buttons -->
    <div class="mb-6 flex flex-wrap gap-2">
        <a href="{{ route('laporan.transaksi.kas.export.pdf') }}?tgl_dari={{ $tgl_dari }}&tgl_samp={{ $tgl_samp }}&jenis_transaksi={{ $jenis_transaksi }}&search={{ $search }}" 
           class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors duration-200">
            <i class="fas fa-file-pdf mr-2"></i>Export PDF
        </a>
        <a href="{{ route('laporan.transaksi.kas.export.excel') }}?tgl_dari={{ $tgl_dari }}&tgl_samp={{ $tgl_samp }}&jenis_transaksi={{ $jenis_transaksi }}&search={{ $search }}" 
           class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors duration-200">
            <i class="fas fa-file-excel mr-2"></i>Export Excel
        </a>
        <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-200">
            <i class="fas fa-print mr-2"></i>Print
        </button>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-white bg-opacity-20">
                    <i class="fas fa-arrow-up text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm opacity-90">Total Pemasukan</p>
                    <p class="text-2xl font-bold">Rp {{ number_format($totalPemasukan) }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-red-500 to-red-600 text-white rounded-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-white bg-opacity-20">
                    <i class="fas fa-arrow-down text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm opacity-90">Total Pengeluaran</p>
                    <p class="text-2xl font-bold">Rp {{ number_format($totalPengeluaran) }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-white bg-opacity-20">
                    <i class="fas fa-wallet text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm opacity-90">Saldo</p>
                    <p class="text-2xl font-bold {{ $saldo >= 0 ? 'text-green-200' : 'text-red-200' }}">
                        Rp {{ number_format($saldo) }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Section -->
    @if($chartData->count() > 0)
    <div class="mb-8">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-chart-line mr-2 text-[#14AE5C]"></i>
            Grafik Transaksi Bulanan
        </h3>
        <div class="bg-white p-4 rounded-lg border">
            <canvas id="transaksiChart" width="400" height="200"></canvas>
        </div>
    </div>
    @endif

    <!-- Data Table -->
    <div class="mb-4">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-list mr-2 text-[#14AE5C]"></i>
            Data Transaksi Kas
        </h3>
        
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200 rounded-lg overflow-hidden">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kas</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($dataTransaksi as $index => $transaksi)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $dataTransaksi->firstItem() + $index }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ \Carbon\Carbon::parse($transaksi->tgl)->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $transaksi->keterangan }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $transaksi->nama_kas }}</td>
                        <td class="px-4 py-3 text-sm text-center">
                            @if($transaksi->transaksi === '48')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-arrow-up mr-1"></i>Pemasukan
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <i class="fas fa-arrow-down mr-1"></i>Pengeluaran
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right">
                            @if($transaksi->transaksi === '48')
                                Rp {{ number_format($transaksi->kredit) }}
                            @else
                                Rp {{ number_format($transaksi->debet) }}
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-2"></i>
                            <p>Tidak ada data transaksi untuk kriteria yang dipilih</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($dataTransaksi->hasPages())
    <div class="mt-6">
        {{ $dataTransaksi->appends(request()->query())->links() }}
    </div>
    @endif

    <!-- Summary Footer -->
    <div class="mt-6 p-4 bg-gray-50 rounded-lg">
        <div class="flex flex-wrap justify-between items-center text-sm text-gray-600">
            <div>
                <span class="font-medium">Total Data:</span> {{ number_format($dataTransaksi->total()) }} transaksi
            </div>
            <div>
                <span class="font-medium">Halaman:</span> {{ $dataTransaksi->currentPage() }} dari {{ $dataTransaksi->lastPage() }}
            </div>
            <div>
                <span class="font-medium">Menampilkan:</span> {{ $dataTransaksi->firstItem() ?? 0 }} - {{ $dataTransaksi->lastItem() ?? 0 }} dari {{ $dataTransaksi->total() }}
            </div>
        </div>
    </div>
</div>

<!-- Chart Script -->
@if($chartData->count() > 0)
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('transaksiChart').getContext('2d');
    
    const bulanLabels = [
        'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
        'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
    ];
    
    const pemasukanData = Array(12).fill(0);
    const pengeluaranData = Array(12).fill(0);
    
    @foreach($chartData as $data)
        pemasukanData[{{ $data->bulan - 1 }}] = {{ $data->pemasukan }};
        pengeluaranData[{{ $data->bulan - 1 }}] = {{ $data->pengeluaran }};
    @endforeach
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: bulanLabels,
            datasets: [{
                label: 'Pemasukan',
                data: pemasukanData,
                borderColor: 'rgb(34, 197, 94)',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                tension: 0.1
            }, {
                label: 'Pengeluaran',
                data: pengeluaranData,
                borderColor: 'rgb(239, 68, 68)',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Grafik Transaksi Kas Bulanan'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                        }
                    }
                }
            }
        }
    });
});
</script>
@endif

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