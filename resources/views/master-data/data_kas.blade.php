@extends('layouts.app')

@section('title', 'Data Kas')
@section('sub-title', 'Master Data Kas')

@section('content')
<div class="px-1 justify-center flex flex-col">
    <!-- Header Section -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Data Kas</h1>
            <p class="text-sm text-gray-600 mt-1">Kelola konfigurasi kas dan fitur yang tersedia</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('master-data.data_kas.create') }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
                <i class="fas fa-plus"></i>
                <span>Tambah Data</span>
            </a>
            <a href="{{ route('master-data.data_kas.export', request()->query()) }}" 
               class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
                <i class="fas fa-download"></i>
                <span>Export Excel</span>
            </a>
            <button onclick="document.getElementById('importModal').classList.remove('hidden')" 
                    class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
                <i class="fas fa-upload"></i>
                <span>Import Excel</span>
            </button>
            <a href="{{ route('master-data.data_kas.print') }}" 
               target="_blank"
               class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
                <i class="fas fa-print"></i>
                <span>Cetak</span>
            </a>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-4 border-b">
            <button onclick="toggleFilter()" class="flex items-center justify-between w-full text-left">
                <h2 class="text-lg font-semibold text-gray-900">Filter & Pencarian</h2>
                <i id="filterIcon" class="fas fa-chevron-down transform transition-transform"></i>
            </button>
        </div>
        <div id="filterContent" class="hidden p-4 border-t">
            <form method="GET" action="{{ route('master-data.data_kas') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pencarian</label>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Cari nama kas..." 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Status Aktif -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status Aktif</label>
                    <select name="status_aktif" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Status</option>
                        <option value="aktif" {{ request('status_aktif') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="tidak_aktif" {{ request('status_aktif') == 'tidak_aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                </div>

                <!-- Kategori -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kategori</label>
                    <select name="kategori" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Kategori</option>
                        <option value="komprehensif" {{ request('kategori') == 'komprehensif' ? 'selected' : '' }}>Komprehensif</option>
                        <option value="menengah" {{ request('kategori') == 'menengah' ? 'selected' : '' }}>Menengah</option>
                        <option value="dasar" {{ request('kategori') == 'dasar' ? 'selected' : '' }}>Dasar</option>
                        <option value="minimal" {{ request('kategori') == 'minimal' ? 'selected' : '' }}>Minimal</option>
                    </select>
                </div>

                <!-- Fitur -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Fitur</label>
                    <select name="fitur" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Fitur</option>
                        <option value="tmpl_simpan" {{ request('fitur') == 'tmpl_simpan' ? 'selected' : '' }}>Simpanan</option>
                        <option value="tmpl_penarikan" {{ request('fitur') == 'tmpl_penarikan' ? 'selected' : '' }}>Penarikan</option>
                        <option value="tmpl_pinjaman" {{ request('fitur') == 'tmpl_pinjaman' ? 'selected' : '' }}>Pinjaman</option>
                        <option value="tmpl_bayar" {{ request('fitur') == 'tmpl_bayar' ? 'selected' : '' }}>Bayar</option>
                        <option value="tmpl_pemasukan" {{ request('fitur') == 'tmpl_pemasukan' ? 'selected' : '' }}>Pemasukan</option>
                        <option value="tmpl_pengeluaran" {{ request('fitur') == 'tmpl_pengeluaran' ? 'selected' : '' }}>Pengeluaran</option>
                        <option value="tmpl_transfer" {{ request('fitur') == 'tmpl_transfer' ? 'selected' : '' }}>Transfer</option>
                    </select>
                </div>

                <div class="md:col-span-2 lg:col-span-4 flex justify-end space-x-2">
                    <button type="button" onclick="clearFilters()" 
                            class="px-4 py-2 text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                        <i class="fas fa-times mr-2"></i>Reset
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white hover:bg-blue-700 rounded-lg transition-colors">
                        <i class="fas fa-filter mr-2"></i>Terapkan Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <i class="fas fa-database text-blue-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Data Kas</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $dataKas->total() }}</p>
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
                    <p class="text-2xl font-bold text-gray-900">{{ $dataKas->where('aktif', 'Y')->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-purple-100 rounded-lg">
                    <i class="fas fa-star text-purple-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Komprehensif</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $dataKas->where('aktif', 'Y')->filter(function($item) { return $item->total_fitur_aktif >= 6; })->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-yellow-100 rounded-lg">
                    <i class="fas fa-cog text-yellow-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Rata-rata Fitur</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($dataKas->where('aktif', 'Y')->avg('total_fitur_aktif'), 1) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Kas</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fitur</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Fitur</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($dataKas as $kas)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ ($dataKas->currentPage() - 1) * $dataKas->perPage() + $loop->iteration }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $kas->nama }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {!! $kas->status_aktif_badge !!}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-wrap gap-1">
                                @if($kas->tmpl_simpan === 'Y')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Simpanan</span>
                                @endif
                                @if($kas->tmpl_penarikan === 'Y')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Penarikan</span>
                                @endif
                                @if($kas->tmpl_pinjaman === 'Y')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">Pinjaman</span>
                                @endif
                                @if($kas->tmpl_bayar === 'Y')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Bayar</span>
                                @endif
                                @if($kas->tmpl_pemasukan === 'Y')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">Pemasukan</span>
                                @endif
                                @if($kas->tmpl_pengeluaran === 'Y')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">Pengeluaran</span>
                                @endif
                                @if($kas->tmpl_transfer === 'Y')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Transfer</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {!! $kas->kategori_kas_badge !!}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <div class="flex items-center">
                                <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ ($kas->total_fitur_aktif / 7) * 100 }}%"></div>
                                </div>
                                <span class="text-sm font-medium">{{ $kas->total_fitur_aktif }}/7</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                            <div class="flex justify-center space-x-2">
                                <a href="{{ route('master-data.data_kas.show', $kas->id) }}" 
                                   class="text-blue-600 hover:text-blue-900" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('master-data.data_kas.edit', $kas->id) }}" 
                                   class="text-yellow-600 hover:text-yellow-900" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('master-data.data_kas.destroy', $kas->id) }}" 
                                      method="POST" class="inline" 
                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
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
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-4"></i>
                            <p class="text-lg font-medium">Tidak ada data</p>
                            <p class="text-sm">Mulai dengan menambahkan data kas baru</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($dataKas->hasPages())
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            <div class="flex items-center justify-between">
                <div class="flex-1 flex justify-between sm:hidden">
                    @if($dataKas->onFirstPage())
                        <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-500 bg-white cursor-not-allowed">
                            Previous
                        </span>
                    @else
                        <a href="{{ $dataKas->previousPageUrl() }}" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Previous
                        </a>
                    @endif

                    @if($dataKas->hasMorePages())
                        <a href="{{ $dataKas->nextPageUrl() }}" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Next
                        </a>
                    @else
                        <span class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-500 bg-white cursor-not-allowed">
                            Next
                        </span>
                    @endif
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Menampilkan
                            <span class="font-medium">{{ $dataKas->firstItem() }}</span>
                            sampai
                            <span class="font-medium">{{ $dataKas->lastItem() }}</span>
                            dari
                            <span class="font-medium">{{ $dataKas->total() }}</span>
                            hasil
                        </p>
                    </div>
                    <div>
                        {{ $dataKas->links() }}
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Import Modal -->
<div id="importModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Import Data Kas</h3>
                <button onclick="document.getElementById('importModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="{{ route('master-data.data_kas.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pilih File Excel</label>
                    <input type="file" name="file" accept=".xlsx,.xls,.csv" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')" 
                            class="px-4 py-2 text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white hover:bg-blue-700 rounded-lg">
                        Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleFilter() {
    const content = document.getElementById('filterContent');
    const icon = document.getElementById('filterIcon');
    
    if (content.classList.contains('hidden')) {
        content.classList.remove('hidden');
        icon.classList.add('rotate-180');
    } else {
        content.classList.add('hidden');
        icon.classList.remove('rotate-180');
    }
}

// Auto-expand filter if there are active filters
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const hasFilters = urlParams.has('search') || urlParams.has('status_aktif') || 
                      urlParams.has('kategori') || urlParams.has('fitur');
    
    if (hasFilters) {
        toggleFilter();
    }
    
    // Add loading state to filter form
    const filterForm = document.querySelector('form[method="GET"]');
    if (filterForm) {
        filterForm.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Memproses...';
                submitBtn.disabled = true;
            }
        });
    }
});

// Clear all filters
function clearFilters() {
    const form = document.querySelector('form[method="GET"]');
    if (form) {
        form.reset();
        form.submit();
    }
}
</script>
@endsection