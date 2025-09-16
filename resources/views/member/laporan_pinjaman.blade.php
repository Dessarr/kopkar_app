@extends('layouts.member')

@section('title', 'Laporan Pinjaman')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Page Header -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-lg p-6 mb-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold mb-2">Laporan Pinjaman</h1>
                <p class="text-blue-100">Riwayat dan status pinjaman Anda</p>
            </div>
            <div class="text-right">
                <div class="text-sm text-blue-100">Periode</div>
                <div class="text-lg font-semibold">{{ \Carbon\Carbon::parse($tgl_dari)->format('d M Y') }} - {{ \Carbon\Carbon::parse($tgl_samp)->format('d M Y') }}</div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Pinjaman -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Pinjaman</p>
                    <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($statistics['total_pinjaman'], 0, ',', '.') }}</p>
                </div>
                <div class="bg-blue-100 p-3 rounded-full">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Dibayar -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Dibayar</p>
                    <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($statistics['total_dibayar'], 0, ',', '.') }}</p>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Sisa Tagihan -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-orange-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Sisa Tagihan</p>
                    <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($statistics['sisa_tagihan'], 0, ',', '.') }}</p>
                </div>
                <div class="bg-orange-100 p-3 rounded-full">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Progress -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Progress Pembayaran</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($statistics['payment_progress'], 1) }}%</p>
                </div>
                <div class="bg-purple-100 p-3 rounded-full">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h3 class="text-lg font-semibold mb-4">Filter Laporan</h3>
        <form method="GET" action="{{ route('member.laporan.pinjaman') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Dari</label>
                <input type="date" name="tgl_dari" value="{{ $tgl_dari }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Sampai</label>
                <input type="date" name="tgl_samp" value="{{ $tgl_samp }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="all" {{ $status_filter == 'all' ? 'selected' : '' }}>Semua Status</option>
                    <option value="lunas" {{ $status_filter == 'lunas' ? 'selected' : '' }}>Lunas</option>
                    <option value="belum_lunas" {{ $status_filter == 'belum_lunas' ? 'selected' : '' }}>Belum Lunas</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Pinjaman</label>
                <select name="jenis" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="all" {{ $jenis_filter == 'all' ? 'selected' : '' }}>Semua Jenis</option>
                    <option value="1" {{ $jenis_filter == '1' ? 'selected' : '' }}>Pinjaman Biasa</option>
                    <option value="3" {{ $jenis_filter == '3' ? 'selected' : '' }}>Pinjaman Barang</option>
                </select>
            </div>
            <div class="md:col-span-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md font-medium">
                    <i class="fas fa-search mr-2"></i>Filter Data
                </button>
                <a href="{{ route('member.laporan.pinjaman') }}" class="ml-2 bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-md font-medium">
                    <i class="fas fa-refresh mr-2"></i>Reset
                </a>
                <a href="{{ route('member.laporan.pinjaman.export.pdf', request()->query()) }}" class="ml-2 bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-md font-medium">
                    <i class="fas fa-file-pdf mr-2"></i>Export PDF
                </a>
                <a href="{{ route('member.laporan.pinjaman.export.excel', request()->query()) }}" class="ml-2 bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-md font-medium">
                    <i class="fas fa-file-excel mr-2"></i>Export Excel
                </a>
            </div>
        </form>
    </div>

    <!-- Loan Summary -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Active Loans -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4 text-gray-800">Pinjaman Aktif</h3>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-600">Jumlah Pinjaman:</span>
                    <span class="font-semibold">{{ $loanSummary['active_loans_count'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Total Nominal:</span>
                    <span class="font-semibold">Rp {{ number_format($loanSummary['total_active_amount'], 0, ',', '.') }}</span>
                </div>
                @if($loanSummary['next_payment'])
                <div class="flex justify-between">
                    <span class="text-gray-600">Pembayaran Selanjutnya:</span>
                    <span class="font-semibold text-blue-600">Rp {{ number_format($loanSummary['next_payment']['amount'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Jatuh Tempo:</span>
                    <span class="font-semibold text-red-600">{{ \Carbon\Carbon::parse($loanSummary['next_payment']['due_date'])->format('d M Y') }}</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Loan History -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4 text-gray-800">Riwayat Pinjaman</h3>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-600">Total Pinjaman:</span>
                    <span class="font-semibold">{{ $loanSummary['total_loans_count'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Selesai:</span>
                    <span class="font-semibold text-green-600">{{ $loanSummary['completed_loans_count'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Aktif:</span>
                    <span class="font-semibold text-blue-600">{{ $loanSummary['active_loans_count'] }}</span>
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4 text-gray-800">Aktivitas Terbaru</h3>
            <div class="space-y-2">
                @if($recentLoans['pembayaran']->count() > 0)
                    @foreach($recentLoans['pembayaran']->take(3) as $pembayaran)
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-600">Pembayaran</span>
                        <span class="font-semibold text-green-600">Rp {{ number_format($pembayaran->jumlah_bayar, 0, ',', '.') }}</span>
                    </div>
                    @endforeach
                @else
                    <p class="text-gray-500 text-sm">Belum ada pembayaran</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Loan Data Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Data Pinjaman</h3>
        </div>
        
        @if(count($loanData) > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lama</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Angsuran/Bulan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progress</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($loanData as $loan)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ \Carbon\Carbon::parse($loan['tgl_pinjam'])->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @if($loan['jns_pinjaman'] == '1')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    Biasa
                                </span>
                            @elseif($loan['jns_pinjaman'] == '2')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Barang
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ $loan['jns_pinjaman'] }}
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            Rp {{ number_format($loan['jumlah'], 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $loan['lama_angsuran'] }} bulan
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            Rp {{ number_format($loan['angsuran_per_bulan'], 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-full bg-gray-200 rounded-full h-2 mr-2">
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $loan['progress'] }}%"></div>
                                </div>
                                <span class="text-sm text-gray-600">{{ $loan['progress'] }}%</span>
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                {{ $loan['angsuran_count'] }}/{{ $loan['total_angsuran'] }} angsuran
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $statusColors = [
                                    'Lunas' => 'bg-green-100 text-green-800',
                                    'Hampir Lunas' => 'bg-yellow-100 text-yellow-800',
                                    'Sedang Berjalan' => 'bg-blue-100 text-blue-800',
                                    'Baru Dimulai' => 'bg-purple-100 text-purple-800',
                                    'Belum Bayar' => 'bg-red-100 text-red-800'
                                ];
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$loan['status']] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ $loan['status'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button onclick="showLoanDetail({{ $loan['id'] }})" class="text-blue-600 hover:text-blue-900 mr-3">
                                <i class="fas fa-eye"></i> Detail
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada data pinjaman</h3>
            <p class="mt-1 text-sm text-gray-500">Belum ada pinjaman dalam periode yang dipilih.</p>
        </div>
        @endif
    </div>
</div>

<!-- Loan Detail Modal -->
<div id="loanDetailModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Detail Pinjaman</h3>
                <button onclick="closeLoanDetail()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div id="loanDetailContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function showLoanDetail(loanId) {
    // Show modal
    document.getElementById('loanDetailModal').classList.remove('hidden');
    
    // Load loan detail content
    fetch(`/member/pinjaman/${loanId}/detail`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('loanDetailContent').innerHTML = data.html;
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('loanDetailContent').innerHTML = '<p class="text-red-500">Error loading loan details</p>';
        });
}

function closeLoanDetail() {
    document.getElementById('loanDetailModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('loanDetailModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeLoanDetail();
    }
});
</script>
@endpush
