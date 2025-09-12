@extends('layouts.app')

@section('title', 'Data Mobil')
@section('sub-title', 'Master Data Mobil')

@section('content')
<div class="px-1 justify-center flex flex-col">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-green-600 to-green-700 text-white p-6 rounded-lg mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold">Data Mobil</h1>
                <p class="text-green-100">Master Data Mobil</p>
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
            <form method="GET" action="{{ route('master-data.data_mobil') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-search mr-2"></i>Pencarian
                    </label>
                    <input type="text" id="search" name="search" value="{{ request('search') }}" 
                           placeholder="Cari nama, jenis, merek, no polisi..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                </div>
                
                <div>
                    <label for="jenis" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-car mr-2"></i>Jenis
                    </label>
                    <select id="jenis" name="jenis" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value="">Semua Jenis</option>
                        @foreach($jenis as $j)
                            <option value="{{ $j }}" {{ request('jenis') == $j ? 'selected' : '' }}>{{ $j }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="merek" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-industry mr-2"></i>Merek
                    </label>
                    <select id="merek" name="merek" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value="">Semua Merek</option>
                        @foreach($merek as $m)
                            <option value="{{ $m }}" {{ request('merek') == $m ? 'selected' : '' }}>{{ $m }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="pabrikan" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-building mr-2"></i>Pabrikan
                    </label>
                    <select id="pabrikan" name="pabrikan" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value="">Semua Pabrikan</option>
                        @foreach($pabrikan as $p)
                            <option value="{{ $p }}" {{ request('pabrikan') == $p ? 'selected' : '' }}>{{ $p }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="warna" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-palette mr-2"></i>Warna
                    </label>
                    <select id="warna" name="warna" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value="">Semua Warna</option>
                        @foreach($warna as $w)
                            <option value="{{ $w }}" {{ request('warna') == $w ? 'selected' : '' }}>{{ $w }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="tahun" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar mr-2"></i>Tahun
                    </label>
                    <select id="tahun" name="tahun" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value="">Semua Tahun</option>
                        @foreach($tahun as $t)
                            <option value="{{ $t }}" {{ request('tahun') == $t ? 'selected' : '' }}>{{ $t }}</option>
                        @endforeach
                    </select>
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
                    <label for="status_stnk" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-id-card mr-2"></i>Status STNK
                    </label>
                    <select id="status_stnk" name="status_stnk" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value="">Semua Status</option>
                        <option value="kadaluarsa" {{ request('status_stnk') == 'kadaluarsa' ? 'selected' : '' }}>Kadaluarsa</option>
                        <option value="akan_kadaluarsa" {{ request('status_stnk') == 'akan_kadaluarsa' ? 'selected' : '' }}>Akan Kadaluarsa</option>
                        <option value="masih_berlaku" {{ request('status_stnk') == 'masih_berlaku' ? 'selected' : '' }}>Masih Berlaku</option>
                    </select>
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
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <i class="fas fa-car text-blue-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Mobil</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalMobil }}</p>
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
                    <p class="text-2xl font-bold text-gray-900">{{ $mobilAktif }}</p>
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
                    <p class="text-2xl font-bold text-gray-900">{{ $mobilTidakAktif }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-red-100 rounded-lg">
                    <i class="fas fa-exclamation-triangle text-red-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">STNK Kadaluarsa</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stnkKadaluarsa }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-yellow-100 rounded-lg">
                    <i class="fas fa-clock text-yellow-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Akan Kadaluarsa</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stnkAkanKadaluarsa }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg">
                    <i class="fas fa-check text-green-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">STNK Berlaku</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stnkMasihBerlaku }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Bar -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-4 border-b">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex items-center gap-2">
                    <a href="{{ route('master-data.data_mobil.create') }}"
                        class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                        <i class="fas fa-plus"></i>
                        Tambah Mobil
                    </a>
                </div>
                <div class="flex flex-col md:flex-row md:items-center gap-2">
                    <a href="{{ route('master-data.data_mobil.export') }}"
                        class="flex items-center gap-2 bg-green-100 p-2 rounded-lg border-2 border-green-400 hover:bg-green-200 transition">
                        <img src="{{ asset('img/icons-bootstrap/export/cloud-download.svg') }}" class="h-5 w-5" alt="Export Excel">
                        <span class="text-sm">Export Excel</span>
                    </a>
                    <a href="{{ route('master-data.data_mobil.print') }}"
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Mobil</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Merek</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tahun</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No Polisi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status Aktif</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status STNK</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($dataMobil as $mobil)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ ($dataMobil->currentPage() - 1) * $dataMobil->perPage() + $loop->iteration }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900">
                            {{ $mobil->id }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $mobil->nama }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                {{ $mobil->jenis ?? '-' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <span class="px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-800">
                                {{ $mobil->merek ?? '-' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900">
                            {{ $mobil->tahun_formatted }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900">
                            {{ $mobil->no_polisi ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <span class="px-2 py-1 text-xs rounded-full bg-{{ $mobil->status_aktif_badge }}-100 text-{{ $mobil->status_aktif_badge }}-800">
                                {{ $mobil->status_aktif_text }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <span class="px-2 py-1 text-xs rounded-full bg-{{ $mobil->status_stnk_badge }}-100 text-{{ $mobil->status_stnk_badge }}-800">
                                {{ $mobil->status_stnk }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <a href="{{ route('master-data.data_mobil.show', $mobil->id) }}"
                                    class="text-blue-600 hover:text-blue-900" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('master-data.data_mobil.edit', $mobil->id) }}"
                                    class="text-green-600 hover:text-green-900" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('master-data.data_mobil.destroy', $mobil->id) }}" method="POST"
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
                        <td colspan="10" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-car text-4xl mb-2"></i>
                            <p>Tidak ada data mobil</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $dataMobil->links() }}
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
    window.location.href = '{{ route("master-data.data_mobil") }}';
}
</script>
@endsection