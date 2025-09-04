@extends('layouts.app')

@section('title', 'Laporan Data Anggota')
@section('sub-title', 'Laporan')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <!-- Header and Controls -->
    <div class="bg-blue-600 text-white p-4 rounded-lg mb-6">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-bold">Cetak Laporan Data Anggota</h2>
            <div class="flex gap-2">
                <a href="{{ route('laporan.data.anggota.export.pdf', request()->query()) }}" 
                   class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors duration-200">
                    <i class="fas fa-file-pdf mr-2"></i>Cetak Laporan
                </a>
                <button onclick="toggleCollapse()" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors duration-200">
                    <i class="fas fa-chevron-up mr-2" id="collapse-icon"></i>Collapse
                </button>
            </div>
        </div>
    </div>

    <!-- Collapsible Control Panel -->
    <div id="control-panel" class="mb-6">
        <div class="bg-gray-50 p-4 rounded-lg">
        <form method="GET" action="{{ route('laporan.data.anggota') }}" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Cari</label>
                    <input type="text" id="search" name="search" value="{{ $search ?? '' }}"
                       placeholder="Nama, No KTP, atau No Anggota"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-[#14AE5C] text-white rounded-md hover:bg-[#11994F] transition-colors duration-200">
                        <i class="fas fa-search mr-2"></i>Cari
                </button>
                <a href="{{ route('laporan.data.anggota') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors duration-200">
                    <i class="fas fa-refresh mr-2"></i>Reset
                </a>
            </div>
        </form>
    </div>
    </div>

    <!-- Main Title -->
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Laporan Data Anggota</h1>
        @if($search)
            <div class="mt-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                    <i class="fas fa-search mr-2"></i>
                    Hasil pencarian untuk: "{{ $search }}"
                    <a href="{{ route('laporan.data.anggota') }}" class="ml-2 text-blue-600 hover:text-blue-800">
                        <i class="fas fa-times"></i>
                    </a>
                </span>
                </div>
        @endif
    </div>

    <!-- Data Table -->
    <div class="mb-4">
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200 rounded-lg overflow-hidden">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No.</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Anggota</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Anggota</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">L/P</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jabatan</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Alamat</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Tgl Registrasi</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Photo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($dataAnggota as $index => $anggota)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $dataAnggota->firstItem() + $index }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                            AG{{ str_pad($anggota->id, 4, '0', STR_PAD_LEFT) }} - {{ $anggota->no_ktp }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            {{ $anggota->nama }}<br>
                            <small class="text-gray-500">{{ $anggota->tmp_lahir }}/{{ $anggota->tgl_lahir ? \Carbon\Carbon::parse($anggota->tgl_lahir)->format('d-m-Y') : '-' }}</small>
                        </td>
                        <td class="px-4 py-3 text-sm text-center text-gray-900">{{ $anggota->jk === 'L' ? 'L' : 'P' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            {{ $anggota->jabatan_id == 1 ? 'Pengurus' : 'Anggota' }}<br>
                            <small class="text-gray-500">{{ $anggota->departement }}</small>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            {{ $anggota->alamat }}<br>
                            <small class="text-gray-500">{{ $anggota->notelp }}</small>
                        </td>
                        <td class="px-4 py-3 text-sm text-center">
                            @if($anggota->aktif === 'Y')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Tidak Aktif
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-center text-gray-900">
                            {{ $anggota->tgl_daftar ? \Carbon\Carbon::parse($anggota->tgl_daftar)->format('d/m/Y') : '-' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-center text-gray-900">
                            @if($anggota->file_pic)
                                <div class="w-8 h-8 bg-gray-200 rounded-full mx-auto flex items-center justify-center">
                                    <i class="fas fa-user text-gray-500 text-xs"></i>
                                </div>
                            @else
                                <div class="w-8 h-8 bg-gray-300 rounded-full mx-auto flex items-center justify-center">
                                    <i class="fas fa-user text-gray-600 text-xs"></i>
                                </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-2"></i>
                            <p>Tidak ada data anggota</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($dataAnggota->hasPages())
    <div class="mt-6 flex justify-center">
        <nav class="flex items-center space-x-1">
            <!-- First Page Link -->
            @if ($dataAnggota->currentPage() > 3)
                <a href="{{ $dataAnggota->appends(request()->query())->url(1) }}" class="px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                    1
                </a>
                @if ($dataAnggota->currentPage() > 4)
                    <span class="px-3 py-2 text-sm text-gray-500">...</span>
                @endif
            @endif

            <!-- Previous Page Link -->
            @if ($dataAnggota->onFirstPage())
                <span class="px-3 py-2 text-sm text-gray-400 bg-gray-100 rounded-md cursor-not-allowed">
                    <i class="fas fa-chevron-left"></i>
                </span>
            @else
                <a href="{{ $dataAnggota->appends(request()->query())->previousPageUrl() }}" class="px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                    <i class="fas fa-chevron-left"></i>
                </a>
            @endif

            <!-- Page Numbers (show 5 pages around current page) -->
            @php
                $start = max(1, $dataAnggota->currentPage() - 2);
                $end = min($dataAnggota->lastPage(), $dataAnggota->currentPage() + 2);
            @endphp

            @for ($page = $start; $page <= $end; $page++)
                @if ($page == $dataAnggota->currentPage())
                    <span class="px-3 py-2 text-sm text-white bg-blue-600 rounded-md font-medium">{{ $page }}</span>
                @else
                    <a href="{{ $dataAnggota->appends(request()->query())->url($page) }}" class="px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                        {{ $page }}
                    </a>
                @endif
            @endfor

            <!-- Next Page Link -->
            @if ($dataAnggota->hasMorePages())
                <a href="{{ $dataAnggota->appends(request()->query())->nextPageUrl() }}" class="px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                    <i class="fas fa-chevron-right"></i>
                </a>
            @else
                <span class="px-3 py-2 text-sm text-gray-400 bg-gray-100 rounded-md cursor-not-allowed">
                    <i class="fas fa-chevron-right"></i>
                </span>
    @endif

            <!-- Last Page Link -->
            @if ($dataAnggota->currentPage() < $dataAnggota->lastPage() - 2)
                @if ($dataAnggota->currentPage() < $dataAnggota->lastPage() - 3)
                    <span class="px-3 py-2 text-sm text-gray-500">...</span>
                @endif
                <a href="{{ $dataAnggota->appends(request()->query())->url($dataAnggota->lastPage()) }}" class="px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                    {{ $dataAnggota->lastPage() }}
                </a>
            @endif
        </nav>
            </div>

    <!-- Pagination Info -->
    <div class="mt-4 text-center text-sm text-gray-600">
        <span class="font-medium">Halaman {{ $dataAnggota->currentPage() }}</span> dari 
        <span class="font-medium">{{ $dataAnggota->lastPage() }}</span> 
        (Total {{ number_format($dataAnggota->total()) }} data)
            </div>
    @endif
</div>

<!-- JavaScript for Collapse Functionality -->
<script>
function toggleCollapse() {
    const panel = document.getElementById('control-panel');
    const icon = document.getElementById('collapse-icon');
    
    if (panel.style.display === 'none') {
        panel.style.display = 'block';
        icon.className = 'fas fa-chevron-up mr-2';
    } else {
        panel.style.display = 'none';
        icon.className = 'fas fa-chevron-down mr-2';
    }
}


</script>

<!-- Print Styles -->
<style>
@media print {
    .sidebar, .bg-blue-600, button, a, #control-panel {
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