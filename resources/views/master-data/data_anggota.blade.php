@extends('layouts.app')

@section('title', 'Data Anggota')
@section('sub-title', 'Master Data Anggota')

@section('content')
<div class="px-1 justify-center flex flex-col">
    <!-- Header Section -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Data Anggota</h1>
            <p class="text-sm text-gray-600 mt-1">Kelola data anggota koperasi</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('master-data.data_anggota.create') }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
                <i class="fas fa-plus"></i>
                <span>Tambah Data</span>
            </a>
            <a href="{{ route('master-data.data_anggota.export', request()->query()) }}" 
               class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
                <i class="fas fa-download"></i>
                <span>Export Excel</span>
            </a>
            <a href="{{ route('master-data.data_anggota.print') }}" 
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
                <i id="filterIcon" class="fas fa-chevron-down transform transition-transform {{ request()->hasAny(['search', 'jenis_kelamin', 'departement', 'kota']) ? 'rotate-180' : '' }}"></i>
            </button>
        </div>
        <div id="filterContent" class="{{ request()->hasAny(['search', 'jenis_kelamin', 'departement', 'kota']) ? '' : 'hidden' }} p-4 border-t">
            <form method="GET" action="{{ route('master-data.data_anggota.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pencarian</label>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Cari nama/ID anggota..." 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>


                <!-- Jenis Kelamin -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Jenis Kelamin</option>
                        <option value="L" {{ request('jenis_kelamin') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ request('jenis_kelamin') == 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>

                <!-- Departement -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Departement</label>
                    <select name="departement" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Departement</option>
                        @foreach($departements ?? [] as $dept)
                        <option value="{{ $dept }}" {{ request('departement') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Kota -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kota</label>
                    <select name="kota" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Kota</option>
                        @foreach($kotas ?? [] as $kota)
                        <option value="{{ $kota }}" {{ request('kota') == $kota ? 'selected' : '' }}>{{ $kota }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2 lg:col-span-5 flex justify-end space-x-2">
                    <a href="{{ route('master-data.data_anggota.index') }}" 
                       class="px-4 py-2 text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                        Reset
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white hover:bg-blue-700 rounded-lg transition-colors flex items-center space-x-2"
                            id="filterButton">
                        <i class="fas fa-search"></i>
                        <span>Terapkan Filter</span>
                        <i class="fas fa-spinner fa-spin hidden" id="filterSpinner"></i>
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
                    <i class="fas fa-users text-blue-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Anggota</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalAnggota ?? 0 }}</p>
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
                    <p class="text-2xl font-bold text-gray-900">{{ $totalAktif ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <i class="fas fa-male text-blue-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Laki-laki</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalLakiLaki ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-pink-100 rounded-lg">
                    <i class="fas fa-female text-pink-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Perempuan</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalPerempuan ?? 0 }}</p>
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
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Foto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Koperasi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Lengkap</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Kelamin</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Alamat</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kota</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tgl Daftar</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($dataAnggota as $anggota)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ ($dataAnggota->currentPage() - 1) * $dataAnggota->perPage() + $loop->iteration }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($anggota->file_pic && Storage::disk('public')->exists('anggota/' . $anggota->file_pic))
                            <img src="{{ asset('storage/anggota/' . $anggota->file_pic) }}"
                                alt="Foto {{ $anggota->nama }}" class="w-12 h-12 rounded-full mx-auto object-cover">
                            @else
                            <div class="w-12 h-12 rounded-full bg-gray-100 mx-auto flex items-center justify-center">
                                <i class="fas fa-user text-gray-400 text-xl"></i>
                            </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $anggota->no_ktp }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm">
                                <p class="font-medium text-gray-900">{{ $anggota->nama }}</p>
                                @if($anggota->username)
                                <p class="text-xs text-gray-500">{{ $anggota->username }}</p>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $anggota->jk == 'L' ? 'bg-blue-100 text-blue-800' : 'bg-pink-100 text-pink-800' }}">
                                <i class="fas {{ $anggota->jk == 'L' ? 'fa-male' : 'fa-female' }} mr-1"></i>
                                {{ $anggota->jk == 'L' ? 'Laki-laki' : 'Perempuan' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <div class="max-w-xs truncate" title="{{ $anggota->alamat }}">
                                {{ $anggota->alamat ?: '-' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $anggota->kota ?: '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $anggota->departement ?: '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @if($anggota->tgl_daftar && $anggota->tgl_daftar != '0000-00-00')
                            {{ date('d/m/Y', strtotime($anggota->tgl_daftar)) }}
                            @else
                            <span class="text-gray-400 italic text-xs">Tidak ada Data</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                            <div class="flex justify-center space-x-2">
                                <a href="{{ route('master-data.data_anggota.show', $anggota->id) }}" 
                                   class="text-blue-600 hover:text-blue-900" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('master-data.data_anggota.edit', $anggota->id) }}" 
                                   class="text-yellow-600 hover:text-yellow-900" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('master-data.data_anggota.destroy', $anggota->id) }}" 
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
                        <td colspan="10" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-4"></i>
                            <p class="text-lg font-medium">Tidak ada data</p>
                            <p class="text-sm">Mulai dengan menambahkan data anggota baru</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
        <!-- Pagination -->
        @if($dataAnggota->hasPages())
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            <div class="flex items-center justify-between">
                <div class="flex-1 flex justify-between sm:hidden">
                    @if($dataAnggota->onFirstPage())
                        <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-500 bg-white cursor-not-allowed">
                            Previous
                        </span>
                    @else
                        <a href="{{ $dataAnggota->previousPageUrl() }}" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Previous
                        </a>
                    @endif

                    @if($dataAnggota->hasMorePages())
                        <a href="{{ $dataAnggota->nextPageUrl() }}" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
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
                            <span class="font-medium">{{ $dataAnggota->firstItem() }}</span>
                            sampai
                            <span class="font-medium">{{ $dataAnggota->lastItem() }}</span>
                            dari
                            <span class="font-medium">{{ $dataAnggota->total() }}</span>
                            hasil
                        </p>
                    </div>
                    <div>
                        {{ $dataAnggota->links() }}
                    </div>
                </div>
            </div>
        </div>
        @endif
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

// Loading state untuk filter
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.querySelector('form[method="GET"]');
    const filterButton = document.getElementById('filterButton');
    const filterSpinner = document.getElementById('filterSpinner');
    
    if (filterForm && filterButton) {
        filterForm.addEventListener('submit', function() {
            filterButton.disabled = true;
            filterSpinner.classList.remove('hidden');
            filterButton.querySelector('span').textContent = 'Memproses...';
        });
    }
});
</script>
@endsection