@extends('layouts.app')

@section('title', 'Jenis Simpanan')
@section('sub-title', 'Master Data Jenis Simpanan')

@section('content')
<div class="px-1 justify-center flex flex-col">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg p-6 mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold">Jenis Simpanan</h1>
                <p class="text-green-100">Master Data Jenis Simpanan</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('master-data.jns_simpan.export') }}" 
                   class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white px-4 py-2 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-file-excel mr-2"></i>Export Excel
                </a>
                <a href="{{ route('master-data.jns_simpan.print') }}" 
                   class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white px-4 py-2 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-print mr-2"></i>Cetak
                </a>
                <a href="{{ route('master-data.jns_simpan.template') }}" 
                   class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white px-4 py-2 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-download mr-2"></i>Template
                </a>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-list-alt text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Jenis Simpanan</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalJenisSimpanan }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-eye text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Aktif</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $jenisSimpananAktif }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-100 text-red-600">
                    <i class="fas fa-eye-slash text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Tidak Aktif</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $jenisSimpananTidakAktif }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <i class="fas fa-money-bill-wave text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Jumlah Minimum</p>
                    <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($totalJumlahMinimum, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Collapsible Filter -->
    <div class="bg-white rounded-lg shadow mb-4">
        <button onclick="toggleCollapsible()" class="w-full flex items-center justify-between p-4 text-left hover:bg-gray-50 transition-colors">
            <div class="flex items-center">
                <i class="fas fa-filter text-green-600 mr-3"></i>
                <span class="font-semibold text-gray-700">Filter & Pencarian</span>
            </div>
            <i id="collapsible-icon" class="fas fa-chevron-down text-gray-500 transition-transform"></i>
        </button>
        
        <!-- Collapsible Content -->
        <div id="collapsible-content" class="space-y-4 p-4 border-t" style="display: none;">
            <form method="GET" action="{{ route('master-data.jns_simpan') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-search mr-2"></i>Pencarian
                    </label>
                    <input type="text" id="search" name="search" value="{{ request('search') }}" 
                           placeholder="Cari jenis simpanan..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                </div>
                
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-tag mr-2"></i>Tipe Simpanan
                    </label>
                    <select id="type" name="type" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value="">Semua Tipe</option>
                        @foreach($types as $type)
                            <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-eye mr-2"></i>Status Tampil
                    </label>
                    <select id="status" name="status" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value="">Semua Status</option>
                        <option value="Y" {{ request('status') == 'Y' ? 'selected' : '' }}>Tampil</option>
                        <option value="T" {{ request('status') == 'T' ? 'selected' : '' }}>Tidak Tampil</option>
                    </select>
                </div>
                
                <div class="flex items-end space-x-2">
                    <button type="submit" class="flex-1 px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors duration-200">
                        <i class="fas fa-search mr-2"></i>Filter
                    </button>
                    <button type="button" onclick="clearFilters()" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors duration-200">
                        <i class="fas fa-refresh mr-2"></i>Reset
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4">
            {{ session('success') }}
        </div>
        @endif

        <div class="p-4 border-b">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex items-center gap-2">
                    <a href="{{ route('master-data.jns_simpan.create') }}"
                        class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                        <i class="fa-solid fa-plus fa-xs"></i>
                        Tambah Jenis Simpanan
                    </a>
                </div>
                <div class="flex items-center gap-2 text-sm text-gray-600">
                    <i class="fas fa-info-circle mr-1"></i>
                    Menampilkan {{ $dataSimpan->firstItem() ?? 0 }} - {{ $dataSimpan->lastItem() ?? 0 }} dari {{ $dataSimpan->total() }} data
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 text-gray-600 text-sm">
                        <th class="px-4 py-3 border-b text-center w-12">#</th>
                        <th class="px-4 py-3 border-b text-center">ID</th>
                        <th class="px-4 py-3 border-b text-left">Jenis Simpanan</th>
                        <th class="px-4 py-3 border-b text-right">Jumlah Minimum</th>
                        <th class="px-4 py-3 border-b text-center">Status Tampil</th>
                        <th class="px-4 py-3 border-b text-center">Urutan</th>
                        <th class="px-4 py-3 border-b text-center w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($dataSimpan as $simpan)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 text-center text-sm">
                            {{ ($dataSimpan->currentPage() - 1) * $dataSimpan->perPage() + $loop->iteration }}
                        </td>
                        <td class="px-4 py-3 text-center text-sm font-mono">{{ $simpan->id }}</td>
                        <td class="px-4 py-3 text-sm font-medium">{{ $simpan->jns_simpan }}</td>
                        <td class="px-4 py-3 text-right text-sm font-mono">
                            Rp {{ number_format($simpan->jumlah, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-center text-sm">
                            <span class="px-2 py-1 text-xs rounded-full {{ $simpan->tampil == 'Y' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $simpan->tampil == 'Y' ? 'Tampil' : 'Tidak Tampil' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-sm">
                            <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                {{ $simpan->urut }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <a href="{{ route('master-data.jns_simpan.show', $simpan->id) }}"
                                    class="text-blue-600 hover:text-blue-900 p-1 rounded" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('master-data.jns_simpan.edit', $simpan->id) }}"
                                    class="text-green-600 hover:text-green-900 p-1 rounded" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('master-data.jns_simpan.destroy', $simpan->id) }}" method="POST"
                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 p-1 rounded" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">
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
    @if($dataSimpan->hasPages())
    <div class="mt-6 flex items-center justify-between">
        <div class="flex justify-center flex-1">
            <div class="bg-white px-4 py-2 flex items-center gap-2 rounded-lg border shadow-sm">
                @for ($i = 1; $i <= $dataSimpan->lastPage(); $i++)
                    @if ($i == 1 || $i == $dataSimpan->lastPage() || ($i >= $dataSimpan->currentPage() - 1 && $i <= $dataSimpan->currentPage() + 1))
                        <a href="{{ $dataSimpan->url($i) }}"
                            class="px-3 py-1 text-sm rounded-md {{ $dataSimpan->currentPage() == $i ? 'bg-green-100 text-green-800 font-medium' : 'hover:bg-gray-50' }}">
                            {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                        </a>
                    @elseif ($i == 2 || $i == $dataSimpan->lastPage() - 1)
                        <span class="px-2 text-gray-400">...</span>
                    @endif
                @endfor
            </div>
        </div>
    </div>
    @endif
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

function clearFilters() {
    window.location.href = '{{ route("master-data.jns_simpan") }}';
}
</script>
@endsection