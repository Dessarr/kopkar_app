@extends('layouts.app')

@section('title', 'Jenis Angsuran')
@section('sub-title', 'Master Data Jenis Angsuran')

@section('content')
<style>
.expandable-row {
    transition: all 0.3s ease-in-out;
}

.expandable-row:hover {
    padding-top: 1rem;
    padding-bottom: 1rem;
    background-color: rgb(249 250 251);
}

.expandable-content {
    transition: all 0.3s ease-in-out;
    max-height: 1.5rem;
    overflow: hidden;
}

.expandable-row:hover .expandable-content {
    max-height: 100px;
}
</style>

<div class="px-1 justify-center flex flex-col">
    <!-- Header Section -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Jenis Angsuran</h1>
            <p class="text-sm text-gray-600 mt-1">Kelola data jenis angsuran koperasi</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('master-data.jenis_angsuran.create') }}" 
               class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
                <i class="fas fa-plus"></i>
                <span>Tambah Data</span>
            </a>
            <a href="{{ route('master-data.jenis_angsuran.export', request()->query()) }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
                <i class="fas fa-download"></i>
                <span>Export Excel</span>
            </a>
            <a href="{{ route('master-data.jenis_angsuran.print') }}" 
               class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
                <i class="fas fa-print"></i>
                <span>Cetak</span>
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-medium">Total Jenis Angsuran</p>
                    <p class="text-2xl font-bold">{{ $totalAngsuran ?? 0 }}</p>
                </div>
                <div class="bg-green-400 bg-opacity-30 rounded-full p-3">
                    <i class="fas fa-calendar-alt text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium">Aktif</p>
                    <p class="text-2xl font-bold">{{ $totalAktif ?? 0 }}</p>
                </div>
                <div class="bg-blue-400 bg-opacity-30 rounded-full p-3">
                    <i class="fas fa-check-circle text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-100 text-sm font-medium">Tidak Aktif</p>
                    <p class="text-2xl font-bold">{{ $totalTidakAktif ?? 0 }}</p>
                </div>
                <div class="bg-orange-400 bg-opacity-30 rounded-full p-3">
                    <i class="fas fa-times-circle text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm font-medium">Jangka Pendek</p>
                    <p class="text-2xl font-bold">{{ $totalPendek ?? 0 }}</p>
                </div>
                <div class="bg-purple-400 bg-opacity-30 rounded-full p-3">
                    <i class="fas fa-clock text-xl"></i>
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
            <i id="collapsible-icon" class="fas fa-chevron-down text-gray-500 transition-transform {{ request()->hasAny(['search', 'status_aktif', 'kategori', 'min_bulan', 'max_bulan']) ? 'rotate-180' : '' }}"></i>
        </button>
        
        <!-- Collapsible Content -->
        <div id="collapsible-content" class="space-y-4 p-4 border-t {{ request()->hasAny(['search', 'status_aktif', 'kategori', 'min_bulan', 'max_bulan']) ? '' : 'hidden' }}">
            <!-- Filter Section -->
            <div class="bg-gray-50 rounded-lg p-4">
                <form method="GET" action="{{ route('master-data.jenis_angsuran.index') }}" class="flex flex-wrap gap-4 items-end">
                    <div class="flex-1 min-w-[200px]">
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-search mr-2"></i>Pencarian
                        </label>
                        <input type="text" id="search" name="search" value="{{ request('search') }}" 
                               placeholder="Cari jumlah bulan..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent">
                    </div>
                    <div class="flex-1 min-w-[120px]">
                        <label for="status_aktif" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-toggle-on mr-2"></i>Status Aktif
                        </label>
                        <select id="status_aktif" name="status_aktif" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent">
                            <option value="">Semua Status</option>
                            <option value="aktif" {{ request('status_aktif') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="nonaktif" {{ request('status_aktif') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>
                    <div class="flex-1 min-w-[120px]">
                        <label for="kategori" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-tags mr-2"></i>Kategori
                        </label>
                        <select id="kategori" name="kategori" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent">
                            <option value="">Semua Kategori</option>
                            <option value="pendek" {{ request('kategori') == 'pendek' ? 'selected' : '' }}>Jangka Pendek (≤6 bulan)</option>
                            <option value="menengah" {{ request('kategori') == 'menengah' ? 'selected' : '' }}>Jangka Menengah (7-24 bulan)</option>
                            <option value="panjang" {{ request('kategori') == 'panjang' ? 'selected' : '' }}>Jangka Panjang (>24 bulan)</option>
                        </select>
                    </div>
                    <div class="flex-1 min-w-[120px]">
                        <label for="min_bulan" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar mr-2"></i>Min Bulan
                        </label>
                        <input type="number" id="min_bulan" name="min_bulan" value="{{ request('min_bulan') }}" 
                               placeholder="Min"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent">
                    </div>
                    <div class="flex-1 min-w-[120px]">
                        <label for="max_bulan" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar mr-2"></i>Max Bulan
                        </label>
                        <input type="number" id="max_bulan" name="max_bulan" value="{{ request('max_bulan') }}" 
                               placeholder="Max"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="px-4 py-2 bg-[#14AE5C] text-white rounded-md hover:bg-[#11994F] transition-colors duration-200">
                            <i class="fas fa-search mr-2"></i>Filter
                        </button>
                        <a href="{{ route('master-data.jenis_angsuran.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors duration-200">
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

        <!-- Table Header with Actions -->
        <div class="px-6 py-4 border-b bg-gray-50">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900">Daftar Jenis Angsuran</h3>
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-500">
                        Total: {{ $jnsAngsuran->total() }} data
                    </span>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 text-gray-600 text-sm">
                        <th class="px-4 py-3 border-b text-center w-12">#</th>
                        <th class="px-4 py-3 border-b text-center">ID</th>
                        <th class="px-4 py-3 border-b text-center">Jumlah Bulan</th>
                        <th class="px-4 py-3 border-b text-center">Kategori</th>
                        <th class="px-4 py-3 border-b text-center">Status Aktif</th>
                        <th class="px-4 py-3 border-b text-center w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($jnsAngsuran as $angsuran)
                    <tr class="expandable-row">
                        <td class="px-4 py-3 text-center text-sm">
                            {{ ($jnsAngsuran->currentPage() - 1) * $jnsAngsuran->perPage() + $loop->iteration }}
                        </td>
                        <td class="px-4 py-3 text-center text-sm font-mono">{{ $angsuran->id }}</td>
                        <td class="px-4 py-3 text-center text-sm font-mono">
                            <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                {{ $angsuran->ket_formatted }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-sm">
                            <span class="px-2 py-1 text-xs rounded-full bg-{{ $angsuran->kategori_angsuran_badge }}-100 text-{{ $angsuran->kategori_angsuran_badge }}-800">
                                {{ $angsuran->kategori_angsuran }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-sm">
                            <span class="px-2 py-1 text-xs rounded-full bg-{{ $angsuran->status_aktif_badge }}-100 text-{{ $angsuran->status_aktif_badge }}-800">
                                {{ $angsuran->status_aktif_text }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <a href="{{ route('master-data.jenis_angsuran.show', $angsuran->id) }}"
                                    class="text-blue-600 hover:text-blue-900" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('master-data.jenis_angsuran.edit', $angsuran->id) }}"
                                    class="text-green-600 hover:text-green-900" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('master-data.jenis_angsuran.destroy', $angsuran->id) }}" method="POST"
                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                            <i class="fas fa-calendar-alt text-4xl mb-2"></i>
                            <p>Tidak ada data jenis angsuran</p>
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
                @for ($i = 1; $i <= $jnsAngsuran->lastPage(); $i++)
                    @if ($i == 1 || $i == $jnsAngsuran->lastPage() || ($i >= $jnsAngsuran->currentPage() - 1 && $i <= $jnsAngsuran->currentPage() + 1))
                        <a href="{{ $jnsAngsuran->url($i) }}"
                            class="px-3 py-1 text-sm rounded-md {{ $jnsAngsuran->currentPage() == $i ? 'bg-gray-100 font-medium' : 'hover:bg-gray-50' }}">
                            {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                        </a>
                    @elseif ($i == 2 || $i == $jnsAngsuran->lastPage() - 1)
                        <span class="px-2 text-gray-400">...</span>
                    @endif
                @endfor
            </div>
        </div>
        <div class="text-sm text-gray-500">
            Showing {{ $jnsAngsuran->firstItem() }} to {{ $jnsAngsuran->lastItem() }} of {{ $jnsAngsuran->total() }} entries
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