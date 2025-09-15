@extends('layouts.app')

@section('title', 'Jenis Akun')
@section('sub-title', 'Master Data Jenis Akun')

@section('content')
<div class="px-1 justify-center flex flex-col">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Data Jenis Akun</h1>
            <p class="text-gray-600 mt-1">Kelola data jenis akun untuk sistem keuangan koperasi</p>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <!-- Total Jenis Akun -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-chart-line text-blue-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Jenis</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $allDataAkun->count() }}</p>
                </div>
            </div>
        </div>

        <!-- Akun Aktif -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Aktif</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $allDataAkun->where('aktif', 'Y')->count() }}</p>
                </div>
            </div>
        </div>

        <!-- Akun Tidak Aktif -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-times-circle text-red-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Tidak Aktif</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $allDataAkun->where('aktif', 'N')->count() }}</p>
                </div>
            </div>
        </div>

        <!-- Total Tipe Akun -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-tags text-purple-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Tipe Akun</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $allDataAkun->pluck('akun')->unique()->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-4 border-b">
            <div class="flex items-center">
                <i class="fas fa-filter text-[#14AE5C] mr-3"></i>
                <span class="font-semibold text-gray-700">Filter & Pencarian</span>
            </div>
        </div>
        
        <div class="p-4">
                <form method="GET" action="{{ route('master-data.jns_akun.index') }}" class="flex flex-wrap gap-4 items-end">
                    <div class="flex-1 min-w-[200px]">
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-search mr-2"></i>Pencarian
                        </label>
                        <input type="text" id="search" name="search" value="{{ request('search') }}" 
                               placeholder="Cari kode, jenis transaksi, atau akun..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent">
                    </div>
                    <div class="flex-1 min-w-[150px]">
                        <label for="akun_type" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-tag mr-2"></i>Tipe Akun
                        </label>
                        <select id="akun_type" name="akun_type" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent">
                            <option value="">Semua Tipe</option>
                            @foreach($accountTypes as $type)
                                <option value="{{ $type }}" {{ request('akun_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1 min-w-[150px]">
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-toggle-on mr-2"></i>Status
                        </label>
                        <select id="status" name="status" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent">
                            <option value="">Semua Status</option>
                            <option value="Y" {{ request('status') == 'Y' ? 'selected' : '' }}>Aktif</option>
                            <option value="N" {{ request('status') == 'N' ? 'selected' : '' }}>Tidak Aktif</option>
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="px-4 py-2 bg-[#14AE5C] text-white rounded-md hover:bg-[#11994F] transition-colors duration-200">
                            <i class="fas fa-search mr-2"></i>Filter
                        </button>
                        <a href="{{ route('master-data.jns_akun.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors duration-200">
                            <i class="fas fa-refresh mr-2"></i>Reset
                        </a>
                    </div>
                </form>
        </div>
    </div>

    <!-- Tabel Data -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4">
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4">
            {{ session('error') }}
        </div>
        @endif

        <div class="p-4 border-b">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex items-center gap-2">
                    <a href="{{ route('master-data.jns_akun.create') }}"
                        class="inline-flex items-center gap-2 bg-[#14AE5C] hover:bg-[#11994F] text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                        <i class="fa-solid fa-plus fa-xs"></i>
                        Tambah Data
                    </a>
                </div>
                <div class="flex flex-col md:flex-row md:items-center gap-2 md:ml-auto">
                    <a href="{{ route('master-data.jns_akun.export', request()->query()) }}"
                        class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                        <i class="fas fa-file-excel"></i>
                        Export Excel
                    </a>
                    <a href="{{ route('master-data.jns_akun.template') }}"
                        class="inline-flex items-center gap-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                        <i class="fas fa-download"></i>
                        Template
                    </a>
                    <a href="{{ route('master-data.jns_akun.print') }}"
                        class="inline-flex items-center gap-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                        <i class="fas fa-print"></i>
                        Cetak
                    </a>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 text-gray-600 text-sm">
                        <th class="px-4 py-3 border-b text-center w-12">#</th>
                        <th class="px-4 py-3 border-b text-center">Kode Aktiva</th>
                        <th class="px-4 py-3 border-b text-center">Jenis Transaksi</th>
                        <th class="px-4 py-3 border-b text-center">Akun</th>
                        <th class="px-4 py-3 border-b text-center">Laba Rugi</th>
                        <th class="px-4 py-3 border-b text-center">Pemasukan</th>
                        <th class="px-4 py-3 border-b text-center">Pengeluaran</th>
                        <th class="px-4 py-3 border-b text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($dataAkun as $akun)
                    <tr class="hover:bg-gray-50 cursor-pointer transition-colors duration-200" 
                        onclick="window.location.href='{{ route('master-data.jns_akun.show', $akun->id) }}'">
                        <td class="px-4 py-3 text-center text-sm">
                            {{ ($dataAkun->currentPage() - 1) * $dataAkun->perPage() + $loop->iteration }}
                        </td>
                        <td class="px-4 py-3 text-center text-sm font-mono">{{ $akun->kd_aktiva }}</td>
                        <td class="px-4 py-3 text-sm">
                                {{ $akun->jns_trans }}
                        </td>
                        <td class="px-4 py-3 text-center text-sm">
                            <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                {{ $akun->akun ?? 'Tidak Diketahui' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-sm">{{ $akun->laba_rugi_text }}</td>
                        <td class="px-4 py-3 text-center text-sm">
                            @if($akun->pemasukan === 'Y')
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
                            @if($akun->pengeluaran === 'Y')
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
                            <span class="px-2 py-1 text-xs rounded-full {{ $akun->aktif === 'Y' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $akun->status_text }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-gray-500">
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
    <div class="mt-6 flex items-center justify-between">
        <div class="flex justify-center flex-1">
            <div class="bg-white px-4 py-2 flex items-center gap-2 rounded-lg border shadow-sm">
                @for ($i = 1; $i <= $dataAkun->lastPage(); $i++)
                    @if ($i == 1 || $i == $dataAkun->lastPage() || ($i >= $dataAkun->currentPage() - 1 && $i <= $dataAkun->currentPage() + 1))
                        <a href="{{ $dataAkun->url($i) }}"
                            class="px-3 py-1 text-sm rounded-md {{ $dataAkun->currentPage() == $i ? 'bg-[#14AE5C] text-white font-medium' : 'hover:bg-gray-50' }}">
                            {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                        </a>
                    @elseif ($i == 2 || $i == $dataAkun->lastPage() - 1)
                        <span class="px-2 text-gray-400">...</span>
                    @endif
                @endfor
            </div>
        </div>
        <div class="text-sm text-gray-500">
            Menampilkan {{ $dataAkun->firstItem() }} sampai {{ $dataAkun->lastItem() }} dari {{ $dataAkun->total() }} data
        </div>
    </div>
</div>

@endsection