@extends('layouts.app')

@section('title', 'Data Anggota')
@section('sub-title', 'Data Anggota Koperasi')

@section('content')
<div class="px-1 justify-center flex flex-col">
    <!-- Header Section -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Data Anggota</h1>
            <p class="text-sm text-gray-600 mt-1">Kelola data anggota koperasi dengan fitur lengkap</p>
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
            <a href="{{ route('master-data.data_anggota.print', request()->query()) }}" 
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
            <form method="GET" action="{{ route('master-data.data_anggota') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pencarian</label>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Cari nama, ID, departemen..." 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Status</option>
                        <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="tidak_aktif" {{ request('status') == 'tidak_aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                </div>

                <!-- Gender -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Jenis Kelamin</option>
                        <option value="L" {{ request('jenis_kelamin') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ request('jenis_kelamin') == 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>

                <!-- Department -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Departemen</label>
                    <select name="departement" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Departemen</option>
                        @foreach($departements as $dept)
                            <option value="{{ $dept }}" {{ request('departement') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- City -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kota</label>
                    <select name="kota" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Kota</option>
                        @foreach($kota as $city)
                            <option value="{{ $city }}" {{ request('kota') == $city ? 'selected' : '' }}>{{ $city }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Age Range -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Umur Min</label>
                    <input type="number" name="umur_min" value="{{ request('umur_min') }}" 
                           placeholder="Min umur"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Umur Max</label>
                    <input type="number" name="umur_max" value="{{ request('umur_max') }}" 
                           placeholder="Max umur"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Registration Date Range -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tgl Daftar Dari</label>
                    <input type="date" name="tgl_daftar_dari" value="{{ request('tgl_daftar_dari') }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tgl Daftar Sampai</label>
                    <input type="date" name="tgl_daftar_sampai" value="{{ request('tgl_daftar_sampai') }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Sort Options -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Urutkan Berdasarkan</label>
                    <select name="sort_by" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="nama" {{ request('sort_by') == 'nama' ? 'selected' : '' }}>Nama</option>
                        <option value="no_ktp" {{ request('sort_by') == 'no_ktp' ? 'selected' : '' }}>ID Koperasi</option>
                        <option value="tgl_daftar" {{ request('sort_by') == 'tgl_daftar' ? 'selected' : '' }}>Tanggal Daftar</option>
                        <option value="departement" {{ request('sort_by') == 'departement' ? 'selected' : '' }}>Departemen</option>
                        <option value="kota" {{ request('sort_by') == 'kota' ? 'selected' : '' }}>Kota</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Urutan</label>
                    <select name="sort_order" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>A-Z</option>
                        <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>Z-A</option>
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
    <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <i class="fas fa-users text-blue-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Anggota</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalAnggota }}</p>
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
                    <p class="text-2xl font-bold text-gray-900">{{ $anggotaAktif }}</p>
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
                    <p class="text-2xl font-bold text-gray-900">{{ $anggotaTidakAktif }}</p>
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
                    <p class="text-2xl font-bold text-gray-900">{{ $lakiLaki }}</p>
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
                    <p class="text-2xl font-bold text-gray-900">{{ $perempuan }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="mb-4 flex gap-2">
        <a href="{{ route('master-data.data_anggota') }}">
            <button id="tab-aktif" type="button"
                class="tab-btn rounded-t-lg font-semibold px-6 py-2 border-b-2 transition-all duration-200 active">Anggota
                Aktif</button>
        </a>
        <a href="{{ route('master-data.data_anggota.nonaktif') }}">
            <button id="tab-nonaktif" type="button"
                class="tab-btn rounded-t-lg font-semibold px-6 py-2 border-b-2 transition-all duration-200">Anggota
                Tidak Aktif</button>
        </a>
    </div>
    <style>
    .tab-btn {
        background: #f3f4f6;
        color: #374151;
        border: 1px solid #e5e7eb;
        border-bottom: 2px solid transparent;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.03);
        cursor: pointer;
    }

    .tab-btn.active,
    .tab-btn:focus {
        background: #3b82f6;
        color: #fff;
        border-bottom: 2px solid #3b82f6;
        border-top: 2px solid #3b82f6;
        border-left: 2px solid #3b82f6;
        border-right: 2px solid #3b82f6;
        outline: none;
        z-index: 10;
    }

    .tab-btn:hover:not(.active) {
        background: #dbeafe;
        color: #1d4ed8;
        border-bottom: 2px solid #3b82f6;
    }
    </style>

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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Departemen</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kota</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
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
                                <svg class="w-7 h-7 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                                </svg>
                            </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $anggota->no_ktp }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm">
                                <p class="font-medium text-gray-900">{{ $anggota->nama }}</p>
                                @if($anggota->username)
                                <p class="text-xs text-gray-500">{{ $anggota->username }}</p>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $anggota->jenis_kelamin_text == 'Laki-laki' ? 'bg-blue-100 text-blue-800' : 'bg-pink-100 text-pink-800' }}">
                                {{ $anggota->jenis_kelamin_text }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $anggota->departement ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $anggota->kota ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $anggota->status_aktif == 'Y' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $anggota->status_aktif_text }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <a href="{{ route('master-data.data_anggota.show', $anggota->id) }}"
                                    class="text-blue-600 hover:text-blue-900">Detail</a>
                                <a href="{{ route('master-data.data_anggota.edit', $anggota->id) }}"
                                    class="text-green-600 hover:text-green-900">Edit</a>
                                <form action="{{ route('master-data.data_anggota.destroy', $anggota->id) }}"
                                    method="POST"
                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');"
                                    class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-users text-4xl text-gray-300 mb-2"></i>
                                <p>Tidak ada data anggota</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-4 py-3 bg-gray-50 border-t">
            {{ $dataAnggota->links() }}
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
    window.location.href = '{{ route("master-data.data_anggota") }}';
}
</script>
@endsection