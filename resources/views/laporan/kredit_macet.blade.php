@extends('layouts.app')

@section('title', 'Laporan Kredit Macet')
@section('sub-title', 'Laporan')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <!-- Filter Section -->
    <div class="mb-6">
        <form method="GET" action="{{ route('laporan.kredit.macet') }}" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label for="hari_macet" class="block text-sm font-medium text-gray-700 mb-2">Kriteria Macet (Hari)</label>
                <select id="hari_macet" name="hari_macet" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent">
                    <option value="30" {{ $hari_macet == 30 ? 'selected' : '' }}>30 Hari</option>
                    <option value="60" {{ $hari_macet == 60 ? 'selected' : '' }}>60 Hari</option>
                    <option value="90" {{ $hari_macet == 90 ? 'selected' : '' }}>90 Hari</option>
                    <option value="120" {{ $hari_macet == 120 ? 'selected' : '' }}>120 Hari</option>
                    <option value="180" {{ $hari_macet == 180 ? 'selected' : '' }}>180 Hari</option>
                </select>
            </div>
            <div class="flex-1 min-w-[200px]">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Cari</label>
                <input type="text" id="search" name="search" value="{{ $search }}" 
                       placeholder="Nama anggota atau no pinjaman"
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
                <a href="{{ route('laporan.kredit.macet') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors duration-200">
                    <i class="fas fa-refresh mr-2"></i>Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Export Buttons -->
    <div class="mb-6 flex flex-wrap gap-2">
        <a href="{{ route('laporan.kredit.macet.export.pdf') }}?hari_macet={{ $hari_macet }}&search={{ $search }}" 
           class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors duration-200">
            <i class="fas fa-file-pdf mr-2"></i>Export PDF
        </a>
        <a href="{{ route('laporan.kredit.macet.export.excel') }}?hari_macet={{ $hari_macet }}&search={{ $search }}" 
           class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors duration-200">
            <i class="fas fa-file-excel mr-2"></i>Export Excel
        </a>
        <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-200">
            <i class="fas fa-print mr-2"></i>Print
        </button>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
        <div class="bg-gradient-to-r from-red-500 to-red-600 text-white rounded-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-white bg-opacity-20">
                    <i class="fas fa-exclamation-triangle text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm opacity-90">Total Pinjaman</p>
                    <p class="text-2xl font-bold">Rp {{ number_format($totalPinjaman) }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-white bg-opacity-20">
                    <i class="fas fa-money-bill-wave text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm opacity-90">Total Angsuran</p>
                    <p class="text-2xl font-bold">Rp {{ number_format($totalAngsuran) }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-white bg-opacity-20">
                    <i class="fas fa-balance-scale text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm opacity-90">Total Sisa</p>
                    <p class="text-2xl font-bold">Rp {{ number_format($totalSisa) }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 text-white rounded-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-white bg-opacity-20">
                    <i class="fas fa-clock text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm opacity-90">30+ Hari</p>
                    <p class="text-2xl font-bold">{{ number_format($macet30) }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-red-500 to-red-600 text-white rounded-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-white bg-opacity-20">
                    <i class="fas fa-exclamation-circle text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm opacity-90">90+ Hari</p>
                    <p class="text-2xl font-bold">{{ number_format($macet90) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="mb-4">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-list mr-2 text-[#14AE5C]"></i>
            Data Kredit Macet ({{ $hari_macet }}+ hari)
        </h3>
        
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200 rounded-lg overflow-hidden">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No Pinjaman</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Anggota</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No KTP</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Pinjam</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jatuh Tempo</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Lama Macet</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Pinjaman</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Angsuran</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Sisa</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($dataKreditMacet as $index => $kredit)
                    @php
                        $lamaMacet = \Carbon\Carbon::parse($kredit->tempo)->diffInDays(\Carbon\Carbon::now());
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $dataKreditMacet->firstItem() + $index }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $kredit->no_pinjaman }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $kredit->anggota->nama ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $kredit->anggota->no_ktp ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ \Carbon\Carbon::parse($kredit->tgl_pinjaman)->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            <span class="font-medium text-red-600">
                                {{ \Carbon\Carbon::parse($kredit->tempo)->format('d/m/Y') }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-center">
                            @if($lamaMacet >= 180)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>{{ $lamaMacet }} hari
                                </span>
                            @elseif($lamaMacet >= 90)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                    <i class="fas fa-exclamation-circle mr-1"></i>{{ $lamaMacet }} hari
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-clock mr-1"></i>{{ $lamaMacet }} hari
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right">Rp {{ number_format($kredit->jumlah_pinjaman) }}</td>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right">Rp {{ number_format($kredit->jumlah_angsuran) }}</td>
                        <td class="px-4 py-3 text-sm font-bold text-red-600 text-right">
                            Rp {{ number_format($kredit->jumlah_pinjaman - $kredit->jumlah_angsuran) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="px-4 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-2"></i>
                            <p>Tidak ada data kredit macet untuk kriteria yang dipilih</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($dataKreditMacet->hasPages())
    <div class="mt-6">
        {{ $dataKreditMacet->appends(request()->query())->links() }}
    </div>
    @endif

    <!-- Summary Footer -->
    <div class="mt-6 p-4 bg-gray-50 rounded-lg">
        <div class="flex flex-wrap justify-between items-center text-sm text-gray-600">
            <div>
                <span class="font-medium">Total Data:</span> {{ number_format($dataKreditMacet->total()) }} kredit macet
            </div>
            <div>
                <span class="font-medium">Halaman:</span> {{ $dataKreditMacet->currentPage() }} dari {{ $dataKreditMacet->lastPage() }}
            </div>
            <div>
                <span class="font-medium">Menampilkan:</span> {{ $dataKreditMacet->firstItem() ?? 0 }} - {{ $dataKreditMacet->lastItem() ?? 0 }} dari {{ $dataKreditMacet->total() }}
            </div>
        </div>
    </div>

    <!-- Legend -->
    <div class="mt-6 p-4 bg-red-50 rounded-lg">
        <h4 class="text-sm font-semibold text-red-800 mb-2">Keterangan Kredit Macet:</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-red-700">
            <div>
                <p><strong>30+ Hari:</strong> Kredit macet ringan (perlu perhatian)</p>
                <p><strong>60+ Hari:</strong> Kredit macet sedang (perlu tindakan)</p>
                <p><strong>90+ Hari:</strong> Kredit macet berat (perlu penanganan khusus)</p>
            </div>
            <div>
                <p><strong>120+ Hari:</strong> Kredit macet sangat berat</p>
                <p><strong>180+ Hari:</strong> Kredit macet kritis</p>
                <p><strong>Lama Macet:</strong> Jumlah hari sejak jatuh tempo</p>
            </div>
        </div>
    </div>

    <!-- Warning Alert -->
    <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
        <div class="flex items-center">
            <i class="fas fa-exclamation-triangle text-yellow-600 mr-3"></i>
            <div>
                <h4 class="text-sm font-semibold text-yellow-800">Peringatan</h4>
                <p class="text-sm text-yellow-700 mt-1">
                    Kredit macet dapat mempengaruhi kesehatan keuangan koperasi. Segera lakukan tindakan penanganan untuk setiap kredit yang masuk kategori macet.
                </p>
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