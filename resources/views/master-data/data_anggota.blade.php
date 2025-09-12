@extends('layouts.app')

@section('title', 'Data Anggota')
@section('sub-title', 'Data Anggota Koperasi')

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
        <h1 class="text-2xl font-bold">Data Anggota Koperasi</h1>
    </div>

    <!-- Tab Navigasi -->
    <div class="mb-4 flex gap-2">
        <a href="{{ route('master-data.data_anggota') }}">
            <button id="tab-aktif" type="button"
                class="tab-btn rounded-t-lg font-semibold px-6 py-2 border-b-2 transition-all duration-200 {{ (isset($tab) ? $tab == 'aktif' : true) ? 'active' : '' }}">Anggota
                Aktif</button>
        </a>
        <a href="{{ route('master-data.data_anggota.nonaktif') }}">
            <button id="tab-nonaktif" type="button"
                class="tab-btn rounded-t-lg font-semibold px-6 py-2 border-b-2 transition-all duration-200 {{ (isset($tab) && $tab == 'nonaktif') ? 'active' : '' }}">Anggota
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
        background: #10b981;
        color: #fff;
        border-bottom: 2px solid #10b981;
        border-top: 2px solid #10b981;
        border-left: 2px solid #10b981;
        border-right: 2px solid #10b981;
        outline: none;
        z-index: 10;
    }

    .tab-btn:hover:not(.active) {
        background: #d1fae5;
        color: #047857;
        border-bottom: 2px solid #10b981;
    }
    </style>
    <script>
    // Tidak perlu JS showTab, karena sudah pakai route berbeda
    </script>

    <!-- Tabel Data Anggota Aktif -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4">
            {{ session('success') }}
        </div>
        @endif
        <!-- ... existing search/export ... -->
        <div class="p-4 border-b">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex items-center gap-2">
                    <a href="{{ route('master-data.data_anggota.create') }}"
                        class="inline-flex items-center gap-2 bg-green-100 hover:bg-green-200 text-green-800 text-sm font-medium px-4 py-2 rounded-lg transition">
                        <i class="fa-solid fa-plus fa-xs"></i>
                        Tambah Data Anggota
                    </a>
                </div>
                <div class="flex flex-col md:flex-row md:items-center gap-2 md:ml-auto">
                    <form action="{{ route('master-data.data_anggota') }}" method="GET" class="mb-2 md:mb-0">
                        <div class="flex items-center bg-gray-100 p-2 rounded-lg border-2 border-gray-300">
                            <i class="fa-solid fa-magnifying-glass mr-2 text-gray-400"></i>
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="Cari nama/ID anggota"
                                class="text-sm text-gray-500 bg-transparent border-none focus:outline-none w-40 md:w-56">
                        </div>
                    </form>
                    <a href="{{ route('master-data.data_anggota.export') }}"
                        class="flex items-center gap-2 bg-green-100 p-2 rounded-lg border-2 border-green-400 hover:bg-green-200 transition">
                        <img src="{{ asset('img/icons-bootstrap/export/cloud-download.svg') }}" class="h-5 w-5"
                            alt="Export Excel">
                        <span class="text-sm">Export Excel</span>
                    </a>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 text-gray-600 text-sm">
                        <th class="px-4 py-3 border-b text-center w-12">#</th>
                        <th class="px-4 py-3 border-b text-center w-20">Foto</th>
                        <th class="px-4 py-3 border-b text-center">ID Koperasi</th>
                        <th class="px-4 py-3 border-b text-center">Nama Lengkap</th>
                        <th class="px-4 py-3 border-b text-center w-24">Jenis Kelamin</th>
                        <th class="px-4 py-3 border-b text-center">Alamat</th>
                        <th class="px-4 py-3 border-b text-center">Kota</th>
                        <th class="px-4 py-3 border-b text-center">Department</th>
                        <th class="px-4 py-3 border-b text-center">Tgl Daftar</th>
                        <th class="px-4 py-3 border-b text-center w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($dataAnggota as $anggota)
                    <tr class="expandable-row">
                        <td class="px-4 py-3 text-center text-sm">
                            {{ ($dataAnggota->currentPage() - 1) * $dataAnggota->perPage() + $loop->iteration }}
                        </td>
                        <td class="px-4 py-3 text-center">
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
                        <td class="px-4 py-3 text-center text-sm">{{ $anggota->no_ktp }}</td>
                        <td class="px-4 py-3">
                            <div class="text-sm">
                                <p class="font-medium text-gray-900">{{ $anggota->nama }}</p>
                                @if($anggota->username)
                                <p class="text-xs text-gray-500">{{ $anggota->username }}</p>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center text-sm">
                            {{ $anggota->jk == 'L' ? 'Laki-laki' : 'Perempuan' }}
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <div class="expandable-content">
                                {{ $anggota->alamat }}
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center text-sm">{{ $anggota->kota }}</td>
                        <td class="px-4 py-3 text-center text-sm">{{ $anggota->departement }}</td>
                        <td class="px-4 py-3 text-center text-sm">
                            @if($anggota->tgl_daftar && $anggota->tgl_daftar != '0000-00-00')
                            {{ date('d/m/Y', strtotime($anggota->tgl_daftar)) }}
                            @else
                            <span class="text-gray-400 italic text-xs">Tidak ada Data</span>
                            @endif
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
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <!-- Pagination Aktif di luar table -->
    <div class="mt-5 flex items-center justify-between px-4">
        <div class="flex justify-center flex-1">
            <div class="bg-white px-4 py-2 flex items-center gap-2 rounded-lg border shadow-sm">
                @for ($i = 1; $i <= $dataAnggota->lastPage(); $i++)
                    @if ($i == 1 || $i == $dataAnggota->lastPage() || ($i >= $dataAnggota->currentPage() - 1 && $i <= $dataAnggota->currentPage() + 1))
                        <a href="{{ $dataAnggota->url($i) }}"
                            class="px-3 py-1 text-sm rounded-md {{ $dataAnggota->currentPage() == $i ? 'bg-gray-100 font-medium' : 'hover:bg-gray-50' }}">
                            {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                        </a>
                    @elseif ($i == 2 || $i == $dataAnggota->lastPage() - 1)
                        <span class="px-2 text-gray-400">...</span>
                    @endif
                @endfor
            </div>
        </div>
        <div class="text-sm text-gray-500">
            Showing {{ $dataAnggota->firstItem() }} to {{ $dataAnggota->lastItem() }} of {{ $dataAnggota->total() }} entries
        </div>
    </div>
</div>

<script>
// Default tab
// showTab('aktif'); // This line is removed as per the edit hint
</script>
@endsection