@extends('layouts.app')

@section('title', 'Data Barang')
@section('sub-title', 'Master Data Barang')

@section('content')

<div class="px-1 justify-center flex flex-col">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold">Data Barang</h1>
            <p class="text-gray-600 mt-1">Kelola data barang dan inventori</p>
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
                <form method="GET" action="{{ route('master-data.data_barang.index') }}" class="flex flex-wrap gap-4 items-end">
                    <div class="flex-1 min-w-[200px]">
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-search mr-2"></i>Pencarian
                        </label>
                        <input type="text" id="search" name="search" value="{{ request('search') }}" 
                               placeholder="Cari nama barang, type, merk..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent">
                    </div>
                    <div class="flex-1 min-w-[120px]">
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-tag mr-2"></i>Type
                        </label>
                        <select id="type" name="type" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent">
                            <option value="">Semua Type</option>
                            @foreach($types as $type)
                                <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1 min-w-[120px]">
                        <label for="merk" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-industry mr-2"></i>Merk
                        </label>
                        <select id="merk" name="merk" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent">
                            <option value="">Semua Merk</option>
                            @foreach($merks as $merk)
                                <option value="{{ $merk }}" {{ request('merk') == $merk ? 'selected' : '' }}>{{ $merk }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1 min-w-[120px]">
                        <label for="status_stok" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-boxes mr-2"></i>Status Stok
                        </label>
                        <select id="status_stok" name="status_stok" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent">
                            <option value="">Semua Status</option>
                            <option value="habis" {{ request('status_stok') == 'habis' ? 'selected' : '' }}>Habis</option>
                            <option value="kritis" {{ request('status_stok') == 'kritis' ? 'selected' : '' }}>Kritis (≤5)</option>
                            <option value="sedikit" {{ request('status_stok') == 'sedikit' ? 'selected' : '' }}>Sedikit (6-20)</option>
                            <option value="cukup" {{ request('status_stok') == 'cukup' ? 'selected' : '' }}>Cukup (>20)</option>
                        </select>
                    </div>
                    <div class="flex-1 min-w-[120px]">
                        <label for="cabang" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-building mr-2"></i>Cabang
                        </label>
                        <select id="cabang" name="cabang" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent">
                            <option value="">Semua Cabang</option>
                            @foreach($cabangs as $cabang)
                                <option value="{{ $cabang }}" {{ request('cabang') == $cabang ? 'selected' : '' }}>{{ $cabang }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="px-4 py-2 bg-[#14AE5C] text-white rounded-md hover:bg-[#11994F] transition-colors duration-200">
                            <i class="fas fa-search mr-2"></i>Filter
                        </button>
                        <a href="{{ route('master-data.data_barang.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors duration-200">
                            <i class="fas fa-refresh mr-2"></i>Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                    <i class="fas fa-box text-xl"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Data Barang</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $allDataBarang->count() }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                    <i class="fas fa-check-circle text-xl"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Stok Cukup</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $allDataBarang->where('status_stok', 'cukup')->count() }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 mr-4">
                    <i class="fas fa-exclamation-triangle text-xl"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Stok Kritis</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $allDataBarang->where('status_stok', 'kritis')->count() }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600 mr-4">
                    <i class="fas fa-money-bill-wave text-xl"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Nilai</p>
                    <p class="text-2xl font-bold text-gray-900">{{ 'Rp ' . number_format($allDataBarang->sum(function($item) { return $item->harga * $item->jml_brg; }), 0, ',', '.') }}</p>
                </div>
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

        <div class="p-4 border-b">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex items-center gap-2">
                    <a href="{{ route('master-data.data_barang.create') }}"
                        class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                        <i class="fa-solid fa-plus fa-xs"></i>
                        Tambah Data
                    </a>
                </div>
                <div class="flex flex-col md:flex-row md:items-center gap-2 md:ml-auto">
                    <a href="{{ route('master-data.data_barang.export') }}"
                        class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                        <i class="fas fa-file-excel"></i>
                        Export Excel
                    </a>
                    <a href="{{ route('master-data.data_barang.template') }}"
                        class="inline-flex items-center gap-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                        <i class="fas fa-download"></i>
                        Import Excel
                    </a>
                    <a href="{{ route('master-data.data_barang.print') }}"
                        class="inline-flex items-center gap-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
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
                        <th class="px-4 py-3 border-b text-center">Nama Barang</th>
                        <th class="px-4 py-3 border-b text-center">Type</th>
                        <th class="px-4 py-3 border-b text-center">Merk</th>
                        <th class="px-4 py-3 border-b text-center">Harga</th>
                        <th class="px-4 py-3 border-b text-center">Stok</th>
                        <th class="px-4 py-3 border-b text-center">Status Stok</th>
                        <th class="px-4 py-3 border-b text-center">Cabang</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($dataBarang as $barang)
                    <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location.href='{{ route('master-data.data_barang.show', $barang->id) }}'">
                        <td class="px-4 py-3 text-center text-sm">
                            {{ ($dataBarang->currentPage() - 1) * $dataBarang->perPage() + $loop->iteration }}
                        </td>
                        <td class="px-4 py-3 text-center text-sm font-mono">{{ $barang->id }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                            {{ $barang->nm_barang }}
                        </td>
                        <td class="px-4 py-3 text-center text-sm">
                            <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                {{ $barang->type }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-sm">
                            <span class="px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-800">
                                {{ $barang->merk }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-sm font-mono">
                            {{ $barang->harga_formatted }}
                        </td>
                        <td class="px-4 py-3 text-center text-sm font-mono">
                            {{ $barang->jml_brg }}
                        </td>
                        <td class="px-4 py-3 text-center text-sm">
                            <span class="px-2 py-1 text-xs rounded-full bg-{{ $barang->status_stok_badge }}-100 text-{{ $barang->status_stok_badge }}-800">
                                {{ $barang->status_stok }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-sm">
                            <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">
                                {{ $barang->id_cabang ?? '-' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-2"></i>
                            <p>Tidak ada data barang</p>
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
                @for ($i = 1; $i <= $dataBarang->lastPage(); $i++)
                    @if ($i == 1 || $i == $dataBarang->lastPage() || ($i >= $dataBarang->currentPage() - 1 && $i <= $dataBarang->currentPage() + 1))
                        <a href="{{ $dataBarang->url($i) }}"
                            class="px-3 py-1 text-sm rounded-md {{ $dataBarang->currentPage() == $i ? 'bg-gray-100 font-medium' : 'hover:bg-gray-50' }}">
                            {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                        </a>
                    @elseif ($i == 2 || $i == $dataBarang->lastPage() - 1)
                        <span class="px-2 text-gray-400">...</span>
                    @endif
                @endfor
            </div>
        </div>
        <div class="text-sm text-gray-500">
            Showing {{ $dataBarang->firstItem() }} to {{ $dataBarang->lastItem() }} of {{ $dataBarang->total() }} entries
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