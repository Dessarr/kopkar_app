@extends('layouts.app')

@section('title', 'Jenis Simpanan')
@section('sub-title', 'Master Data Jenis Simpanan')

@section('content')
<div class="px-1 justify-center flex flex-col">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Data Jenis Simpanan</h1>
        <p class="text-gray-600">Kelola jenis-jenis simpanan dan konfigurasinya</p>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <!-- Total Jenis Simpanan -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-piggy-bank text-blue-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Jenis</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $allDataSimpan->count() }}</p>
                </div>
            </div>
        </div>

        <!-- Tampil -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-eye text-green-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Tampil</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $allDataSimpan->where('tampil', 'Y')->count() }}</p>
                </div>
            </div>
        </div>

        <!-- Tidak Tampil -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-eye-slash text-red-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Tidak Tampil</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $allDataSimpan->where('tampil', 'T')->count() }}</p>
                </div>
            </div>
        </div>

        <!-- Total Nilai -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-calculator text-purple-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Nilai</p>
                    <p class="text-2xl font-semibold text-gray-900">Rp {{ number_format($allDataSimpan->sum('jumlah'), 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Collapsible Header -->
    <div class="bg-white rounded-lg shadow mb-4">
        <button onclick="toggleCollapsible()" class="w-full flex items-center justify-between p-4 text-left hover:bg-gray-50 transition-colors">
            <div class="flex items-center">
                <i class="fas fa-filter text-[#14AE5C] mr-3"></i>
                <span class="font-semibold text-gray-700">Filter & Pencarian</span>
            </div>
            <i id="collapsible-icon" class="fas fa-chevron-down text-gray-500 transition-transform"></i>
        </button>
        
        <!-- Collapsible Content -->
        <div id="collapsible-content" class="space-y-4 p-4 border-t" style="display: none;">
            <!-- Filter Section -->
            <div class="bg-gray-50 rounded-lg p-4">
                <form method="GET" action="{{ route('master-data.jns_simpan.index') }}" class="flex flex-wrap gap-4 items-end">
                    <div class="flex-1 min-w-[200px]">
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-search mr-2"></i>Pencarian
                        </label>
                        <input type="text" id="search" name="search" value="{{ request('search') }}" 
                               placeholder="Cari jenis simpanan..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent">
                    </div>
                    <div class="flex-1 min-w-[150px]">
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-tag mr-2"></i>Tipe Simpanan
                        </label>
                        <select id="type" name="type" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent">
                            <option value="">Semua Tipe</option>
                            @foreach($types as $type)
                                <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1 min-w-[150px]">
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-eye mr-2"></i>Status Tampil
                        </label>
                        <select id="status" name="status" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent">
                            <option value="">Semua Status</option>
                            <option value="Y" {{ request('status') == 'Y' ? 'selected' : '' }}>Tampil</option>
                            <option value="T" {{ request('status') == 'T' ? 'selected' : '' }}>Tidak Tampil</option>
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="px-4 py-2 bg-[#14AE5C] text-white rounded-md hover:bg-[#11994F] transition-colors duration-200">
                            <i class="fas fa-search mr-2"></i>Filter
                        </button>
                        <a href="{{ route('master-data.jns_simpan.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors duration-200">
                            <i class="fas fa-refresh mr-2"></i>Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Tabel Data -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4">
            {{ session('success') }}
        </div>
        @endif

        <!-- Action Buttons -->
        <div class="p-4 border-b">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex items-center gap-2">
                    <a href="{{ route('master-data.jns_simpan.create') }}"
                        class="inline-flex items-center gap-2 bg-blue-100 hover:bg-blue-200 text-blue-800 text-sm font-medium px-4 py-2 rounded-lg transition">
                        <i class="fas fa-plus"></i>
                        Tambah Data
                    </a>
                </div>
                <div class="flex flex-col md:flex-row md:items-center gap-2 md:ml-auto">
                    <a href="{{ route('master-data.jns_simpan.export') }}"
                        class="inline-flex items-center gap-2 bg-green-100 hover:bg-green-200 text-green-800 text-sm font-medium px-4 py-2 rounded-lg transition">
                        <i class="fas fa-file-excel"></i>
                        Export Excel
                    </a>
                    <a href="{{ route('master-data.jns_simpan.template') }}"
                        class="inline-flex items-center gap-2 bg-purple-100 hover:bg-purple-200 text-purple-800 text-sm font-medium px-4 py-2 rounded-lg transition">
                        <i class="fas fa-download"></i>
                        Template
                    </a>
                    <a href="{{ route('master-data.jns_simpan.print') }}"
                        class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm font-medium px-4 py-2 rounded-lg transition">
                        <i class="fas fa-print"></i>
                        Cetak
                    </a>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 text-gray-600 text-sm">
                        <th class="px-4 py-3 border-b text-center w-12">#</th>
                        <th class="px-4 py-3 border-b text-center">ID</th>
                        <th class="px-4 py-3 border-b text-center">Jenis Simpanan</th>
                        <th class="px-4 py-3 border-b text-center">Jumlah Minimum</th>
                        <th class="px-4 py-3 border-b text-center">Status Tampil</th>
                        <th class="px-4 py-3 border-b text-center">Urutan</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($dataSimpan as $simpan)
                    <tr class="hover:bg-gray-50 cursor-pointer transition-colors duration-200" 
                        onclick="window.location.href='{{ route('master-data.jns_simpan.show', $simpan->id) }}'">
                        <td class="px-4 py-3 text-center text-sm">
                            {{ ($dataSimpan->currentPage() - 1) * $dataSimpan->perPage() + $loop->iteration }}
                        </td>
                        <td class="px-4 py-3 text-center text-sm font-mono">{{ $simpan->id }}</td>
                        <td class="px-4 py-3 text-sm font-medium">
                                {{ $simpan->jns_simpan }}
                        </td>
                        <td class="px-4 py-3 text-center text-sm font-mono">
                            {{ $simpan->jumlah_formatted }}
                        </td>
                        <td class="px-4 py-3 text-center text-sm">
                            <span class="px-2 py-1 text-xs rounded-full {{ $simpan->tampil == 'Y' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $simpan->status_text }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-sm">
                            <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                {{ $simpan->urut }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-2"></i>
                            <p>Tidak ada data jenis simpanan</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-5 flex items-center justify-between px-4">
        <div class="flex justify-center flex-1">
            <div class="bg-white px-4 py-2 flex items-center gap-2 rounded-lg border shadow-sm">
                @for ($i = 1; $i <= $dataSimpan->lastPage(); $i++)
                    @if ($i == 1 || $i == $dataSimpan->lastPage() || ($i >= $dataSimpan->currentPage() - 1 && $i <= $dataSimpan->currentPage() + 1))
                        <a href="{{ $dataSimpan->url($i) }}"
                            class="px-3 py-1 text-sm rounded-md {{ $dataSimpan->currentPage() == $i ? 'bg-gray-100 font-medium' : 'hover:bg-gray-50' }}">
                            {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                        </a>
                    @elseif ($i == 2 || $i == $dataSimpan->lastPage() - 1)
                        <span class="px-2 text-gray-400">...</span>
                    @endif
                @endfor
            </div>
        </div>
        <div class="text-sm text-gray-500">
            Showing {{ $dataSimpan->firstItem() }} to {{ $dataSimpan->lastItem() }} of {{ $dataSimpan->total() }} entries
        </div>
    </div>
</div>

<script>
function toggleCollapsible() {
    const content = document.getElementById('collapsible-content');
    const icon = document.getElementById('collapsible-icon');
    
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