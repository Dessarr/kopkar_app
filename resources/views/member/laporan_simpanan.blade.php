@extends('layouts.member')

@section('title', 'Laporan Simpanan - Toserda')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="bg-white shadow-sm rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Laporan Simpanan - Toserda</h1>
                        <p class="text-sm text-gray-600 mt-1">Riwayat pembayaran simpanan dan toserda Anda</p>
                    </div>
                    <div class="flex items-center space-x-3">
                        <button onclick="refreshData()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition-colors">
                            <i class="fas fa-sync-alt mr-2"></i>Refresh
                        </button>
                        <div class="relative">
                            <button onclick="toggleView()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md font-medium transition-colors">
                                <i class="fas fa-th-large mr-2"></i>View
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="bg-white shadow-sm rounded-lg mb-6">
            <div class="px-6 py-4">
                <form method="GET" action="{{ route('member.laporan.simpanan') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Dari</label>
                        <input type="date" name="tgl_dari" value="{{ $tgl_dari }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Sampai</label>
                        <input type="date" name="tgl_samp" value="{{ $tgl_samp }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Simpanan</label>
                        <select name="jenis" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="all" {{ $jenis_filter == 'all' ? 'selected' : '' }}>Semua Jenis</option>
                            <option value="1" {{ $jenis_filter == '1' ? 'selected' : '' }}>Simpanan Wajib</option>
                            <option value="2" {{ $jenis_filter == '2' ? 'selected' : '' }}>Simpanan Sukarela</option>
                            <option value="3" {{ $jenis_filter == '3' ? 'selected' : '' }}>Simpanan Khusus</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition-colors">
                            <i class="fas fa-filter mr-2"></i>Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-100 rounded-md flex items-center justify-center">
                                <i class="fas fa-chart-line text-blue-600"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Transaksi</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ number_format($statistics['total_transaksi']) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-100 rounded-md flex items-center justify-center">
                                <i class="fas fa-money-bill-wave text-green-600"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Setoran</p>
                            <p class="text-2xl font-semibold text-gray-900">Rp {{ number_format($statistics['total_setoran'], 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-100 rounded-md flex items-center justify-center">
                                <i class="fas fa-calculator text-yellow-600"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Rata-rata Setoran</p>
                            <p class="text-2xl font-semibold text-gray-900">Rp {{ number_format($statistics['rata_rata_setoran'], 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-purple-100 rounded-md flex items-center justify-center">
                                <i class="fas fa-trophy text-purple-600"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Setoran Terbesar</p>
                            <p class="text-2xl font-semibold text-gray-900">Rp {{ number_format($statistics['setoran_terbesar'], 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Breakdown by Type -->
        @if($statistics['breakdown']->count() > 0)
        <div class="bg-white shadow-sm rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Breakdown per Jenis Simpanan</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($statistics['breakdown'] as $breakdown)
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">{{ $breakdown->jns_simpan ?: 'Toserda' }}</p>
                                <p class="text-xs text-gray-500">{{ $breakdown->jumlah_transaksi }} transaksi</p>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-semibold text-gray-900">Rp {{ number_format($breakdown->total_jumlah, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Data Table -->
        <div class="bg-white shadow-sm rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">Riwayat Setoran Simpanan</h3>
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('member.laporan.simpanan.export.pdf', request()->query()) }}" 
                           class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md font-medium text-sm">
                            <i class="fas fa-file-pdf mr-2"></i>Export PDF
                        </a>
                        <a href="{{ route('member.laporan.simpanan.export.excel', request()->query()) }}" 
                           class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md font-medium text-sm">
                            <i class="fas fa-file-excel mr-2"></i>Export Excel
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <i class="fas fa-calendar mr-2"></i>Tanggal
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <i class="fas fa-tag mr-2"></i>Jenis Simpanan
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <i class="fas fa-money-bill-wave mr-2"></i>Jumlah
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <i class="fas fa-info-circle mr-2"></i>Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <i class="fas fa-comment mr-2"></i>Keterangan
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($savingsData as $saving)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="flex items-center">
                                    <i class="fas fa-calendar-day text-gray-400 mr-2"></i>
                                    {{ \Carbon\Carbon::parse($saving->tgl_transaksi)->format('d M Y') }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($saving->jenis_id == 1) bg-blue-100 text-blue-800
                                        @elseif($saving->jenis_id == 2) bg-green-100 text-green-800
                                        @else bg-purple-100 text-purple-800 @endif">
                                        <i class="fas fa-piggy-bank mr-1"></i>
                                        {{ $saving->jenis_simpanan_text }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="flex items-center">
                                    <i class="fas fa-money-bill-wave text-green-500 mr-2"></i>
                                    <span class="font-semibold text-green-600">Rp {{ number_format($saving->jumlah, 0, ',', '.') }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($saving->status_simpanan == 'Wajib') bg-blue-100 text-blue-800
                                    @elseif($saving->status_simpanan == 'Sukarela') bg-green-100 text-green-800
                                    @else bg-purple-100 text-purple-800 @endif">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    {{ $saving->status_simpanan }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <div class="flex items-center">
                                    <i class="fas fa-comment text-gray-400 mr-2"></i>
                                    {{ $saving->keterangan ?: 'Setoran simpanan' }}
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-inbox text-gray-400 text-4xl mb-4"></i>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada data</h3>
                                    <p class="text-gray-500">Belum ada riwayat setoran simpanan untuk periode yang dipilih.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($savingsData->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Menampilkan {{ $savingsData->firstItem() }} - {{ $savingsData->lastItem() }} 
                        dari {{ $savingsData->total() }} data
                    </div>
                    <div class="flex items-center space-x-2">
                        {{ $savingsData->links() }}
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Recent Activities -->
        @if($recentSavings->count() > 0)
        <div class="bg-white shadow-sm rounded-lg mt-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Aktivitas Terbaru</h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @foreach($recentSavings as $recent)
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-piggy-bank text-blue-600"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $recent->jenis_simpanan_text }}</p>
                                <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($recent->tgl_transaksi)->format('d M Y H:i') }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold text-green-600">Rp {{ number_format($recent->jumlah, 0, ',', '.') }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<script>
function refreshData() {
    window.location.reload();
}

function toggleView() {
    // Toggle between list and grid view
    const table = document.querySelector('table');
    if (table) {
        table.classList.toggle('hidden');
    }
}
</script>
@endsection
