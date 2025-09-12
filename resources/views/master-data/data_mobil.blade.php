@extends('layouts.app')

@section('title', 'Data Mobil')
@section('sub-title', 'Master Data Mobil')

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
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Data Mobil</h1>
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
                <form method="GET" action="{{ route('master-data.data_mobil') }}" class="flex flex-wrap gap-4 items-end">
                    <div class="flex-1 min-w-[200px]">
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-search mr-2"></i>Pencarian
                        </label>
                        <input type="text" id="search" name="search" value="{{ request('search') }}" 
                               placeholder="Cari nama, jenis, merek, no polisi..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent">
                    </div>
                    <div class="flex-1 min-w-[120px]">
                        <label for="jenis" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-car mr-2"></i>Jenis
                        </label>
                        <select id="jenis" name="jenis" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent">
                            <option value="">Semua Jenis</option>
                            @foreach($jenis as $j)
                                <option value="{{ $j }}" {{ request('jenis') == $j ? 'selected' : '' }}>{{ $j }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1 min-w-[120px]">
                        <label for="merek" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-industry mr-2"></i>Merek
                        </label>
                        <select id="merek" name="merek" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent">
                            <option value="">Semua Merek</option>
                            @foreach($merek as $m)
                                <option value="{{ $m }}" {{ request('merek') == $m ? 'selected' : '' }}>{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1 min-w-[120px]">
                        <label for="pabrikan" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-building mr-2"></i>Pabrikan
                        </label>
                        <select id="pabrikan" name="pabrikan" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent">
                            <option value="">Semua Pabrikan</option>
                            @foreach($pabrikan as $p)
                                <option value="{{ $p }}" {{ request('pabrikan') == $p ? 'selected' : '' }}>{{ $p }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1 min-w-[120px]">
                        <label for="warna" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-palette mr-2"></i>Warna
                        </label>
                        <select id="warna" name="warna" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent">
                            <option value="">Semua Warna</option>
                            @foreach($warna as $w)
                                <option value="{{ $w }}" {{ request('warna') == $w ? 'selected' : '' }}>{{ $w }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1 min-w-[120px]">
                        <label for="tahun" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar mr-2"></i>Tahun
                        </label>
                        <select id="tahun" name="tahun" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent">
                            <option value="">Semua Tahun</option>
                            @foreach($tahun as $t)
                                <option value="{{ $t }}" {{ request('tahun') == $t ? 'selected' : '' }}>{{ $t }}</option>
                            @endforeach
                        </select>
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
                        <label for="status_stnk" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-id-card mr-2"></i>Status STNK
                        </label>
                        <select id="status_stnk" name="status_stnk" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent">
                            <option value="">Semua Status</option>
                            <option value="kadaluarsa" {{ request('status_stnk') == 'kadaluarsa' ? 'selected' : '' }}>Kadaluarsa</option>
                            <option value="akan_kadaluarsa" {{ request('status_stnk') == 'akan_kadaluarsa' ? 'selected' : '' }}>Akan Kadaluarsa</option>
                            <option value="masih_berlaku" {{ request('status_stnk') == 'masih_berlaku' ? 'selected' : '' }}>Masih Berlaku</option>
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="px-4 py-2 bg-[#14AE5C] text-white rounded-md hover:bg-[#11994F] transition-colors duration-200">
                            <i class="fas fa-search mr-2"></i>Filter
                        </button>
                        <a href="{{ route('master-data.data_mobil') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors duration-200">
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

        <div class="p-4 border-b">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex items-center gap-2">
                    <a href="{{ route('master-data.data_mobil.create') }}"
                        class="inline-flex items-center gap-2 bg-green-100 hover:bg-green-200 text-green-800 text-sm font-medium px-4 py-2 rounded-lg transition">
                        <i class="fa-solid fa-plus fa-xs"></i>
                        Tambah Mobil
                    </a>
                </div>
                <div class="flex flex-col md:flex-row md:items-center gap-2 md:ml-auto">
                    <a href="{{ route('master-data.data_mobil.export') }}"
                        class="flex items-center gap-2 bg-green-100 p-2 rounded-lg border-2 border-green-400 hover:bg-green-200 transition">
                        <img src="{{ asset('img/icons-bootstrap/export/cloud-download.svg') }}" class="h-5 w-5" alt="Export Excel">
                        <span class="text-sm">Export Excel</span>
                    </a>
                    <a href="{{ route('master-data.data_mobil.template') }}"
                        class="flex items-center gap-2 bg-blue-100 p-2 rounded-lg border-2 border-blue-400 hover:bg-blue-200 transition">
                        <i class="fas fa-download text-blue-600"></i>
                        <span class="text-sm">Template</span>
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
                        <th class="px-4 py-3 border-b text-center">Nama Mobil</th>
                        <th class="px-4 py-3 border-b text-center">Jenis</th>
                        <th class="px-4 py-3 border-b text-center">Merek</th>
                        <th class="px-4 py-3 border-b text-center">Tahun</th>
                        <th class="px-4 py-3 border-b text-center">No Polisi</th>
                        <th class="px-4 py-3 border-b text-center">Status Aktif</th>
                        <th class="px-4 py-3 border-b text-center">Status STNK</th>
                        <th class="px-4 py-3 border-b text-center w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($dataMobil as $mobil)
                    <tr class="expandable-row">
                        <td class="px-4 py-3 text-center text-sm">
                            {{ ($dataMobil->currentPage() - 1) * $dataMobil->perPage() + $loop->iteration }}
                        </td>
                        <td class="px-4 py-3 text-center text-sm font-mono">{{ $mobil->id }}</td>
                        <td class="px-4 py-3 text-sm">
                            <div class="expandable-content">
                                {{ $mobil->nama }}
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center text-sm">
                            <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                {{ $mobil->jenis ?? '-' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-sm">
                            <span class="px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-800">
                                {{ $mobil->merek ?? '-' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-sm font-mono">
                            {{ $mobil->tahun_formatted }}
                        </td>
                        <td class="px-4 py-3 text-center text-sm font-mono">
                            {{ $mobil->no_polisi ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-center text-sm">
                            <span class="px-2 py-1 text-xs rounded-full bg-{{ $mobil->status_aktif_badge }}-100 text-{{ $mobil->status_aktif_badge }}-800">
                                {{ $mobil->status_aktif_text }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-sm">
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
                        <td colspan="10" class="px-4 py-8 text-center text-gray-500">
                            <i class="fas fa-car text-4xl mb-2"></i>
                            <p>Tidak ada data mobil</p>
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
                @for ($i = 1; $i <= $dataMobil->lastPage(); $i++)
                    @if ($i == 1 || $i == $dataMobil->lastPage() || ($i >= $dataMobil->currentPage() - 1 && $i <= $dataMobil->currentPage() + 1))
                        <a href="{{ $dataMobil->url($i) }}"
                            class="px-3 py-1 text-sm rounded-md {{ $dataMobil->currentPage() == $i ? 'bg-gray-100 font-medium' : 'hover:bg-gray-50' }}">
                            {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                        </a>
                    @elseif ($i == 2 || $i == $dataMobil->lastPage() - 1)
                        <span class="px-2 text-gray-400">...</span>
                    @endif
                @endfor
            </div>
        </div>
        <div class="text-sm text-gray-500">
            Showing {{ $dataMobil->firstItem() }} to {{ $dataMobil->lastItem() }} of {{ $dataMobil->total() }} entries
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