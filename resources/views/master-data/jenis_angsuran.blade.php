@extends('layouts.app')

@section('title', 'Jenis Angsuran')
@section('sub-title', 'Master Data Jenis Angsuran')

@section('content')
<div class="px-1 justify-center flex flex-col">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-green-600 to-green-700 text-white p-6 rounded-lg mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold">Jenis Angsuran</h1>
                <p class="text-green-100">Master Data Jenis Angsuran</p>
            </div>
            <div class="text-right">
                <p class="text-sm">{{ date('d F Y H.i.s') }}</p>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-4 border-b">
            <button onclick="toggleFilter()" class="flex items-center justify-between w-full text-left">
                <div class="flex items-center">
                    <i class="fas fa-filter text-green-600 mr-3"></i>
                    <span class="font-semibold text-gray-700">Filter & Pencarian</span>
                </div>
                <i id="filterIcon" class="fas fa-chevron-down text-gray-500 transition-transform"></i>
            </button>
        </div>
        
        <div id="filterContent" class="p-4 hidden">
            <form method="GET" action="{{ route('master-data.jenis_angsuran') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-search mr-2"></i>Pencarian
                    </label>
                    <input type="text" id="search" name="search" value="{{ request('search') }}" 
                           placeholder="Cari jumlah bulan..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                </div>
                
                <div>
                    <label for="status_aktif" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-toggle-on mr-2"></i>Status Aktif
                    </label>
                    <select id="status_aktif" name="status_aktif" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value="">Semua Status</option>
                        <option value="aktif" {{ request('status_aktif') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="nonaktif" {{ request('status_aktif') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
                
                <div>
                    <label for="kategori" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-tags mr-2"></i>Kategori
                    </label>
                    <select id="kategori" name="kategori" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value="">Semua Kategori</option>
                        <option value="pendek" {{ request('kategori') == 'pendek' ? 'selected' : '' }}>Jangka Pendek (≤6 bulan)</option>
                        <option value="menengah" {{ request('kategori') == 'menengah' ? 'selected' : '' }}>Jangka Menengah (7-24 bulan)</option>
                        <option value="panjang" {{ request('kategori') == 'panjang' ? 'selected' : '' }}>Jangka Panjang (>24 bulan)</option>
                    </select>
                </div>
                
                <div>
                    <label for="min_bulan" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar mr-2"></i>Min Bulan
                    </label>
                    <input type="number" id="min_bulan" name="min_bulan" value="{{ request('min_bulan') }}" 
                           placeholder="Min" min="1" max="120"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                </div>
                
                <div>
                    <label for="max_bulan" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar mr-2"></i>Max Bulan
                    </label>
                    <input type="number" id="max_bulan" name="max_bulan" value="{{ request('max_bulan') }}" 
                           placeholder="Max" min="1" max="120"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                </div>
                
                <div class="flex items-end gap-2">
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors duration-200">
                        <i class="fas fa-search mr-2"></i>Filter
                    </button>
                    <button type="button" onclick="clearFilters()" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors duration-200">
                        <i class="fas fa-refresh mr-2"></i>Reset
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <i class="fas fa-list text-blue-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalAngsuran }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg">
                    <i class="fas fa-check-circle text-green-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Aktif</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $angsuranAktif }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-red-100 rounded-lg">
                    <i class="fas fa-times-circle text-red-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Tidak Aktif</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $angsuranTidakAktif }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg">
                    <i class="fas fa-clock text-green-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Jangka Pendek</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $jangkaPendek }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-yellow-100 rounded-lg">
                    <i class="fas fa-hourglass-half text-yellow-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Jangka Menengah</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $jangkaMenengah }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <i class="fas fa-calendar-alt text-blue-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Jangka Panjang</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $jangkaPanjang }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Bar -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-4 border-b">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex items-center gap-2">
                    <a href="{{ route('master-data.jenis_angsuran.create') }}"
                        class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                        <i class="fas fa-plus"></i>
                        Tambah Jenis Angsuran
                    </a>
                </div>
                <div class="flex flex-col md:flex-row md:items-center gap-2">
                    <a href="{{ route('master-data.jenis_angsuran.export') }}"
                        class="flex items-center gap-2 bg-green-100 p-2 rounded-lg border-2 border-green-400 hover:bg-green-200 transition">
                        <img src="{{ asset('img/icons-bootstrap/export/cloud-download.svg') }}" class="h-5 w-5" alt="Export Excel">
                        <span class="text-sm">Export Excel</span>
                    </a>
                    <a href="{{ route('master-data.jenis_angsuran.print') }}"
                        class="flex items-center gap-2 bg-blue-100 p-2 rounded-lg border-2 border-blue-400 hover:bg-blue-200 transition">
                        <i class="fas fa-print text-blue-600"></i>
                        <span class="text-sm">Cetak</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4">
            {{ session('success') }}
        </div>
        @endif

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Bulan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status Aktif</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($jnsAngsuran as $angsuran)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ ($jnsAngsuran->currentPage() - 1) * $jnsAngsuran->perPage() + $loop->iteration }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900">
                            {{ $angsuran->id }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                {{ $angsuran->ket_formatted }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <span class="px-2 py-1 text-xs rounded-full bg-{{ $angsuran->kategori_angsuran_badge }}-100 text-{{ $angsuran->kategori_angsuran_badge }}-800">
                                {{ $angsuran->kategori_angsuran }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
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
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-calendar-alt text-4xl mb-2"></i>
                            <p>Tidak ada data jenis angsuran</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $jnsAngsuran->links() }}
        </div>
    </div>
</div>

<script>
function toggleFilter() {
    const filterContent = document.getElementById('filterContent');
    const filterIcon = document.getElementById('filterIcon');
    
    if (filterContent.classList.contains('hidden')) {
        filterContent.classList.remove('hidden');
        filterIcon.classList.remove('fa-chevron-down');
        filterIcon.classList.add('fa-chevron-up');
    } else {
        filterContent.classList.add('hidden');
        filterIcon.classList.remove('fa-chevron-up');
        filterIcon.classList.add('fa-chevron-down');
    }
}

function clearFilters() {
    // Redirect to the base URL without any query parameters
    window.location.href = '{{ route("master-data.jenis_angsuran") }}';
}
</script>
@endsection