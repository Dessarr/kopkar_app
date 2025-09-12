@extends('layouts.app')

@section('title', 'Jenis Akun')
@section('sub-title', 'Master Data Jenis Akun')

@section('content')
<div class="px-1 justify-center flex flex-col">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg p-6 mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold">Jenis Akun</h1>
                <p class="text-green-100">Master Data Jenis Akun</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('master-data.jns_akun.export') }}" 
                   class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white px-4 py-2 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-file-excel mr-2"></i>Export Excel
                </a>
                <a href="{{ route('master-data.jns_akun.print') }}" 
                   class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white px-4 py-2 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-print mr-2"></i>Cetak
                </a>
                <a href="{{ route('master-data.jns_akun.template') }}" 
                   class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white px-4 py-2 rounded-lg transition-all duration-200 flex items-center">
                    <i class="fas fa-download mr-2"></i>Template
                </a>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-list-alt text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Jenis Akun</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalJenisAkun }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-toggle-on text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Aktif</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $akunAktif }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-100 text-red-600">
                    <i class="fas fa-toggle-off text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Tidak Aktif</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $akunTidakAktif }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <i class="fas fa-arrow-up text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Pemasukan</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $akunPemasukan }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-orange-100 text-orange-600">
                    <i class="fas fa-arrow-down text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Pengeluaran</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $akunPengeluaran }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <i class="fas fa-chart-line text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Laba Rugi</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $akunLabaRugi }}</p>
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
            <form method="GET" action="{{ route('master-data.jns_akun.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-search mr-2"></i>Pencarian
                    </label>
                    <input type="text" id="search" name="search" value="{{ request('search') }}" 
                           placeholder="Cari kode, jenis transaksi, atau akun..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                </div>
                
                <div>
                    <label for="akun_type" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-tag mr-2"></i>Tipe Akun
                    </label>
                    <select id="akun_type" name="akun_type" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value="">Semua Tipe</option>
                        @foreach($accountTypes as $type)
                            <option value="{{ $type }}" {{ request('akun_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-toggle-on mr-2"></i>Status
                    </label>
                    <select id="status" name="status" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value="">Semua Status</option>
                        <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                </div>
                
                <div>
                    <label for="pemasukan" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-arrow-up mr-2"></i>Pemasukan
                    </label>
                    <select id="pemasukan" name="pemasukan" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value="">Semua</option>
                        <option value="1" {{ request('pemasukan') == '1' ? 'selected' : '' }}>Ya</option>
                        <option value="0" {{ request('pemasukan') == '0' ? 'selected' : '' }}>Tidak</option>
                    </select>
                </div>
                
                <div>
                    <label for="pengeluaran" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-arrow-down mr-2"></i>Pengeluaran
                    </label>
                    <select id="pengeluaran" name="pengeluaran" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value="">Semua</option>
                        <option value="1" {{ request('pengeluaran') == '1' ? 'selected' : '' }}>Ya</option>
                        <option value="0" {{ request('pengeluaran') == '0' ? 'selected' : '' }}>Tidak</option>
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
                    <a href="{{ route('master-data.jns_akun.create') }}"
                        class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                        <i class="fa-solid fa-plus fa-xs"></i>
                        Tambah Jenis Akun
                    </a>
                </div>
                <div class="flex items-center gap-2 text-sm text-gray-600">
                    <i class="fas fa-info-circle mr-1"></i>
                    Menampilkan {{ $dataAkun->firstItem() ?? 0 }} - {{ $dataAkun->lastItem() ?? 0 }} dari {{ $dataAkun->total() }} data
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 text-gray-600 text-sm">
                        <th class="px-4 py-3 border-b text-center w-12">#</th>
                        <th class="px-4 py-3 border-b text-center">Kode Aktiva</th>
                        <th class="px-4 py-3 border-b text-left">Jenis Transaksi</th>
                        <th class="px-4 py-3 border-b text-center">Akun</th>
                        <th class="px-4 py-3 border-b text-center">Laba Rugi</th>
                        <th class="px-4 py-3 border-b text-center">Pemasukan</th>
                        <th class="px-4 py-3 border-b text-center">Pengeluaran</th>
                        <th class="px-4 py-3 border-b text-center">Status</th>
                        <th class="px-4 py-3 border-b text-center w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($dataAkun as $akun)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 text-center text-sm">
                            {{ ($dataAkun->currentPage() - 1) * $dataAkun->perPage() + $loop->iteration }}
                        </td>
                        <td class="px-4 py-3 text-center text-sm font-mono">{{ $akun->kd_aktiva }}</td>
                        <td class="px-4 py-3 text-sm font-medium">{{ $akun->jns_trans }}</td>
                        <td class="px-4 py-3 text-center text-sm">
                            <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                {{ $akun->akun }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-sm">{{ $akun->laba_rugi ?? '-' }}</td>
                        <td class="px-4 py-3 text-center text-sm">
                            @if($akun->pemasukan)
                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                                    <i class="fas fa-check mr-1"></i>Ya
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">
                                    <i class="fas fa-times mr-1"></i>Tidak
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center text-sm">
                            @if($akun->pengeluaran)
                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                                    <i class="fas fa-check mr-1"></i>Ya
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">
                                    <i class="fas fa-times mr-1"></i>Tidak
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center text-sm">
                            <span class="px-2 py-1 text-xs rounded-full {{ $akun->aktif ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $akun->aktif ? 'Aktif' : 'Tidak Aktif' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <a href="{{ route('master-data.jns_akun.show', $akun->id) }}"
                                    class="text-blue-600 hover:text-blue-900 p-1 rounded" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('master-data.jns_akun.edit', $akun->id) }}"
                                    class="text-green-600 hover:text-green-900 p-1 rounded" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('master-data.jns_akun.destroy', $akun->id) }}" method="POST"
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
                        <td colspan="9" class="px-4 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-2"></i>
                            <p>Tidak ada data jenis akun</p>
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
                @for ($i = 1; $i <= $dataAkun->lastPage(); $i++)
                    @if ($i == 1 || $i == $dataAkun->lastPage() || ($i >= $dataAkun->currentPage() - 1 && $i <= $dataAkun->currentPage() + 1))
                        <a href="{{ $dataAkun->url($i) }}"
                            class="px-3 py-1 text-sm rounded-md {{ $dataAkun->currentPage() == $i ? 'bg-gray-100 font-medium' : 'hover:bg-gray-50' }}">
                            {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                        </a>
                    @elseif ($i == 2 || $i == $dataAkun->lastPage() - 1)
                        <span class="px-2 text-gray-400">...</span>
                    @endif
                @endfor
            </div>
        </div>
        <div class="text-sm text-gray-500">
            Showing {{ $dataAkun->firstItem() }} to {{ $dataAkun->lastItem() }} of {{ $dataAkun->total() }} entries
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

function clearFilters() {
    // Redirect to base URL without query parameters
    window.location.href = '{{ route("master-data.jns_akun.index") }}';
}
</script>
@endsection