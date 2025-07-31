@extends('layouts.app')

@section('title', 'Laporan Kas Anggota')
@section('sub-title', 'Laporan')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <!-- Filter Section -->
    <div class="mb-6">
        <form method="GET" action="{{ route('laporan.kas.anggota') }}" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label for="periode" class="block text-sm font-medium text-gray-700 mb-2">Periode</label>
                <input type="month" id="periode" name="periode" value="{{ $periode }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent">
            </div>
            <div class="flex-1 min-w-[200px]">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Cari</label>
                <input type="text" id="search" name="search" value="{{ $search }}" 
                       placeholder="Nama, No KTP, atau No Anggota"
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
                <a href="{{ route('laporan.kas.anggota') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors duration-200">
                    <i class="fas fa-refresh mr-2"></i>Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Export Buttons -->
    <div class="mb-6 flex flex-wrap gap-2">
        <a href="{{ route('laporan.kas.anggota.export.pdf') }}?periode={{ $periode }}&search={{ $search }}" 
           class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors duration-200">
            <i class="fas fa-file-pdf mr-2"></i>Export PDF
        </a>
        <a href="{{ route('laporan.kas.anggota.export.excel') }}?periode={{ $periode }}&search={{ $search }}" 
           class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors duration-200">
            <i class="fas fa-file-excel mr-2"></i>Export Excel
        </a>
        <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-200">
            <i class="fas fa-print mr-2"></i>Print
        </button>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-white bg-opacity-20">
                    <i class="fas fa-users text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm opacity-90">Total Anggota</p>
                    <p class="text-2xl font-bold">{{ number_format($totalAnggota) }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-white bg-opacity-20">
                    <i class="fas fa-arrow-up text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm opacity-90">Total Setoran</p>
                    <p class="text-2xl font-bold">Rp {{ number_format($totalSimpanan) }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-red-500 to-red-600 text-white rounded-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-white bg-opacity-20">
                    <i class="fas fa-arrow-down text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm opacity-90">Total Penarikan</p>
                    <p class="text-2xl font-bold">Rp {{ number_format($totalPenarikan) }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-white bg-opacity-20">
                    <i class="fas fa-wallet text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm opacity-90">Total Saldo</p>
                    <p class="text-2xl font-bold {{ $totalSaldo >= 0 ? 'text-green-200' : 'text-red-200' }}">
                        Rp {{ number_format($totalSaldo) }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="mb-4">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-list mr-2 text-[#14AE5C]"></i>
            Data Kas Anggota - Periode {{ \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->format('F Y') }}
        </h3>
        
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200 rounded-lg overflow-hidden">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No Anggota</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No KTP</th>
                        
                        @foreach($jenisSimpanan as $jenis)
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" colspan="3">
                            {{ $jenis->jns_simpan }}
                        </th>
                        @endforeach
                        
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" colspan="3">Total</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" colspan="3">Tagihan</th>
                    </tr>
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
                        
                        @foreach($jenisSimpanan as $jenis)
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Setor</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Tarik</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Saldo</th>
                        @endforeach
                        
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Setor</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Tarik</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Saldo</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Tagihan</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Bayar</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Sisa</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($dataAnggota as $index => $anggota)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $dataAnggota->firstItem() + $index }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $anggota->no_anggota }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $anggota->nama }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $anggota->no_ktp }}</td>
                        
                        @php
                            $kas = $kasData[$anggota->no_ktp] ?? [];
                        @endphp
                        
                        @foreach($jenisSimpanan as $jenis)
                        @php
                            $setor = $kas['setoran'][$jenis->id] ?? 0;
                            $tarik = $kas['penarikan'][$jenis->id] ?? 0;
                            $saldo = $setor - $tarik;
                        @endphp
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($setor) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($tarik) }}</td>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right {{ $saldo >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ number_format($saldo) }}
                        </td>
                        @endforeach
                        
                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right">{{ number_format($kas['total_setor'] ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right">{{ number_format($kas['total_tarik'] ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm font-bold text-gray-900 text-right {{ ($kas['total_saldo'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ number_format($kas['total_saldo'] ?? 0) }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($kas['tagihan'] ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($kas['bayar'] ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right {{ ($kas['sisa'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ number_format($kas['sisa'] ?? 0) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ 4 + (count($jenisSimpanan) * 3) + 6 }}" class="px-4 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-2"></i>
                            <p>Tidak ada data kas anggota untuk periode yang dipilih</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($dataAnggota->hasPages())
    <div class="mt-6">
        {{ $dataAnggota->appends(request()->query())->links() }}
    </div>
    @endif

    <!-- Summary Footer -->
    <div class="mt-6 p-4 bg-gray-50 rounded-lg">
        <div class="flex flex-wrap justify-between items-center text-sm text-gray-600">
            <div>
                <span class="font-medium">Total Data:</span> {{ number_format($dataAnggota->total()) }} anggota
            </div>
            <div>
                <span class="font-medium">Halaman:</span> {{ $dataAnggota->currentPage() }} dari {{ $dataAnggota->lastPage() }}
            </div>
            <div>
                <span class="font-medium">Menampilkan:</span> {{ $dataAnggota->firstItem() ?? 0 }} - {{ $dataAnggota->lastItem() ?? 0 }} dari {{ $dataAnggota->total() }}
            </div>
        </div>
    </div>

    <!-- Legend -->
    <div class="mt-6 p-4 bg-blue-50 rounded-lg">
        <h4 class="text-sm font-semibold text-blue-800 mb-2">Keterangan:</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-blue-700">
            <div>
                <p><strong>Setor:</strong> Total setoran anggota per jenis simpanan</p>
                <p><strong>Tarik:</strong> Total penarikan anggota per jenis simpanan</p>
                <p><strong>Saldo:</strong> Selisih antara setoran dan penarikan</p>
            </div>
            <div>
                <p><strong>Tagihan:</strong> Total tagihan yang harus dibayar</p>
                <p><strong>Bayar:</strong> Total pembayaran yang sudah dilakukan</p>
                <p><strong>Sisa:</strong> Selisih antara tagihan dan pembayaran</p>
            </div>
        </div>
    </div>
</div>

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