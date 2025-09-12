@extends('layouts.app')

@section('title', 'Data Pengguna')
@section('sub-title', 'Master Data Anggota/Pengguna')

@section('content')
<div class="px-1 justify-center flex flex-col">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-green-600 to-green-700 text-white p-6 rounded-lg mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold">Data Pengguna</h1>
                <p class="text-green-100">Master Data Anggota/Pengguna</p>
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
            <form method="GET" action="{{ route('master-data.data_pengguna') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-search mr-2"></i>Pencarian
                    </label>
                    <input type="text" id="search" name="search" value="{{ request('search') }}" 
                           placeholder="Cari username, level..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                </div>
                
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user-check mr-2"></i>Status Aktif
                    </label>
                    <select id="status" name="status" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value="">Semua Status</option>
                        <option value="Y" {{ request('status') == 'Y' ? 'selected' : '' }}>Aktif</option>
                        <option value="N" {{ request('status') == 'N' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                </div>
                
                <div>
                    <label for="level" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user-tag mr-2"></i>Level
                    </label>
                    <select id="level" name="level" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value="">Semua Level</option>
                        @foreach($levels as $level)
                            <option value="{{ $level }}" {{ request('level') == $level ? 'selected' : '' }}>{{ ucfirst($level) }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="cabang" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-building mr-2"></i>Cabang
                    </label>
                    <select id="cabang" name="cabang" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value="">Semua Cabang</option>
                        @foreach($cabangs as $cabang)
                            <option value="{{ $cabang->id_cabang }}" {{ request('cabang') == $cabang->id_cabang ? 'selected' : '' }}>{{ $cabang->nama }}</option>
                        @endforeach
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
                    <i class="fas fa-users text-blue-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Pengguna</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalPengguna }}</p>
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
                    <p class="text-2xl font-bold text-gray-900">{{ $penggunaAktif }}</p>
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
                    <p class="text-2xl font-bold text-gray-900">{{ $penggunaTidakAktif }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-purple-100 rounded-lg">
                    <i class="fas fa-crown text-purple-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Administrator</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $adminCount }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <i class="fas fa-user-tie text-blue-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Staff</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $staffCount }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-orange-100 rounded-lg">
                    <i class="fas fa-user-shield text-orange-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Supervisor</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $supervisorCount }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Bar -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-4 border-b">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex items-center gap-2">
                    <a href="{{ route('master-data.data_pengguna.create') }}"
                        class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                        <i class="fas fa-plus"></i>
                        Tambah Pengguna
                    </a>
                </div>
                <div class="flex flex-col md:flex-row md:items-center gap-2">
                    <a href="{{ route('master-data.data_pengguna.export') }}"
                        class="flex items-center gap-2 bg-green-100 p-2 rounded-lg border-2 border-green-400 hover:bg-green-200 transition">
                        <img src="{{ asset('img/icons-bootstrap/export/cloud-download.svg') }}" class="h-5 w-5" alt="Export Excel">
                        <span class="text-sm">Export Excel</span>
                    </a>
                    <a href="{{ route('master-data.data_pengguna.print') }}"
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Level</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cabang</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($dataPengguna as $pengguna)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ ($dataPengguna->currentPage() - 1) * $dataPengguna->perPage() + $loop->iteration }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900">
                            {{ $pengguna->id }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <div class="flex items-center">
                                <div class="h-8 w-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-user text-green-600 text-sm"></i>
                                </div>
                                <span class="font-medium">{{ $pengguna->u_name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <span class="px-2 py-1 text-xs rounded-full {{ $pengguna->level_badge }}">
                                {{ $pengguna->level_text }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $pengguna->cabang ? $pengguna->cabang->nama : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <span class="px-2 py-1 text-xs rounded-full {{ $pengguna->status_aktif_badge }}">
                                {{ $pengguna->status_aktif_text }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <a href="{{ route('master-data.data_pengguna.show', $pengguna->id) }}"
                                    class="text-blue-600 hover:text-blue-900" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('master-data.data_pengguna.edit', $pengguna->id) }}"
                                    class="text-green-600 hover:text-green-900" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('master-data.data_pengguna.destroy', $pengguna->id) }}" method="POST"
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
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-users text-4xl mb-2"></i>
                            <p>Tidak ada data pengguna</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $dataPengguna->links() }}
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
    window.location.href = '{{ route("master-data.data_pengguna") }}';
}
</script>
@endsection