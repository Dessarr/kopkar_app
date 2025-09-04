@extends('layouts.app')

@section('title', 'SHU')
@section('sub-title', 'Sisa Hasil Usaha')

@section('content')
<div class="px-1 justify-center flex flex-col">
    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
        <span class="block sm:inline">{{ session('error') }}</span>
    </div>
    @endif

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-money-bill-wave text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total SHU</p>
                    <p class="text-2xl font-semibold text-gray-900">
                        Rp{{ number_format($shuData->sum('jumlah_bayar'), 0, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-list text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Transaksi</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $shuData->total() }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <i class="fas fa-calendar text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Periode Aktif</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ date('M Y') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Simple Filter Section -->
    <div class="bg-white rounded-lg shadow-md p-3 mb-4">
        <div class="flex items-center justify-between mb-2">
            <h3 class="text-base font-semibold text-gray-800">Filter SHU Anggota</h3>
        </div>

        <form method="GET" action="{{ route('anggota.shu') }}" id="filterForm">
            <!-- Simple Filter Bar -->
            <div class="flex flex-wrap items-center justify-between gap-2 py-2 px-2 bg-gray-50 rounded-lg">
                <!-- Left Side: Filter Controls -->
                <div class="flex items-center space-x-3">
                    <!-- 1. Tanggal -->
                    <div class="flex items-center space-x-2">
                        <label class="text-sm font-medium text-gray-700">Tanggal:</label>
                        <button type="button" id="daterange-btn"
                            class="px-3 py-1.5 border border-gray-300 rounded-lg bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-500 text-sm">
                            <i class="fas fa-calendar mr-1"></i>
                            <span id="daterange-text">Pilih Tanggal</span>
                            <i class="fas fa-chevron-down ml-1"></i>
                        </button>
                        <!-- Hidden inputs untuk form submission -->
                        <input type="hidden" name="start_date" id="tgl_dari" value="{{ $startDate }}">
                        <input type="hidden" name="end_date" id="tgl_sampai" value="{{ $endDate }}">
                    </div>

                    <!-- 2. Search Kode Transaksi -->
                    <div class="flex items-center space-x-2">
                        <label class="text-sm font-medium text-gray-700">Cari:</label>
                        <input type="text" name="search" id="search" value="{{ $search }}"
                            placeholder="[TRD00001] atau Nama"
                            class="px-2 py-1.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 text-sm w-36"
                            onkeypress="if(event.key==='Enter'){doSearch();}">
                    </div>

                    <!-- 3. Button Filter -->
                    <button type="button" onclick="doSearch()" id="searchBtn"
                        class="px-3 py-1.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm">
                        <i class="fas fa-search mr-1"></i>Cari
                    </button>
                </div>

                <!-- Right Side: Action Buttons -->
                <div class="flex items-center space-x-2">
                    <!-- 4. Button Cetak Laporan -->
                    <button type="button" onclick="cetakLaporan()"
                        class="px-3 py-1.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm">
                        <i class="fas fa-print mr-1"></i>Cetak Laporan
                    </button>

                    <!-- 5. Button Hapus Filter -->
                    <button type="button" onclick="clearFilters()"
                        class="px-3 py-1.5 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors text-sm">
                        <i class="fas fa-times mr-1"></i>Hapus Filter
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="flex justify-between items-center mb-2 p-4">
            <h2 class="text-lg font-semibold text-gray-800">Data SHU Anggota</h2>
            <div class="flex space-x-3">
                <button onclick="openModal('addModal')"
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                    <i class="fas fa-plus"></i>
                    <span>Tambah</span>
                </button>
                <button onclick="editData()"
                    class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                    <i class="fas fa-edit"></i>
                    <span>Edit</span>
                </button>
                <button onclick="deleteData()"
                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                    <i class="fas fa-trash"></i>
                    <span>Hapus</span>
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="table-auto w-full border border-gray-300 text-center">
                <thead class="bg-gray-100">
                    <tr class="text-sm">
                        <th class="py-3 border px-4">No</th>
                        <th class="py-3 border px-4">Kode Transaksi</th>
                        <th class="py-3 border px-4">Tanggal Transaksi</th>
                        <th class="py-3 border px-4">ID Anggota</th>
                        <th class="py-3 border px-4">Nama Anggota</th>
                        <th class="py-3 border px-4">Jumlah</th>
                        <th class="py-3 border px-4">User</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shuData as $index => $shu)
                    <tr class="text-sm align-middle hover:bg-gray-50 cursor-pointer row-selectable"
                        data-id="{{ $shu->id }}" data-kode="TRD{{ str_pad($shu->id, 5, '0', STR_PAD_LEFT) }}"
                        data-tanggal="{{ $shu->tgl_transaksi }}" data-keterangan="{{ $shu->keterangan ?? '' }}"
                        data-no-ktp="{{ $shu->no_ktp }}" data-nama-anggota="{{ $shu->anggota->nama ?? 'N/A' }}"
                        data-id-anggota="{{ $shu->anggota->id ?? 0 }}" data-jumlah="{{ $shu->jumlah_bayar }}"
                        data-user="{{ $shu->user_name }}">
                        <td class="py-3 border px-4">
                            {{ ($shuData->currentPage() - 1) * $shuData->perPage() + $loop->iteration }}
                        </td>
                        <td class="py-3 border px-4">
                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-medium">
                                TRD{{ str_pad($shu->id, 5, '0', STR_PAD_LEFT) }}
                            </span>
                        </td>
                        <td class="py-3 border px-4">
                            {{ $shu->tgl_transaksi ? \Carbon\Carbon::parse($shu->tgl_transaksi)->format('d F Y - H:i') : '-' }}
                        </td>
                        <td class="py-3 border px-4">
                            AG{{ str_pad($shu->anggota->id ?? 0, 4, '0', STR_PAD_LEFT) }}
                        </td>
                        <td class="py-3 border px-4 text-left">{{ $shu->anggota->nama ?? 'N/A' }}</td>
                        <td class="py-3 border px-4 font-semibold text-green-600">
                            {{ number_format($shu->jumlah_bayar ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="py-3 border px-4">{{ $shu->user_name ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-2"></i>
                            <p>Tidak ada data SHU</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

    <!-- Pagination -->
    <div class="mt-5 w-full relative px-2 py-2">
        <div class="mx-auto w-fit">
            <div
                class="bg-white px-4 py-1 flex flex-row rounded-full justify-center items-center space-x-2 border border-gray-300 shadow-sm">
                @for ($i = 1; $i <= $shuData->lastPage(); $i++)
                    @if ($i == 1 || $i == $shuData->lastPage() || ($i >= $shuData->currentPage() - 1 && $i <= $shuData->
                        currentPage() + 1))
                        <a href="{{ $shuData->url($i) }}">
                            <div
                                class="rounded-md px-2 py-0.5 text-sm border border-gray-300 {{ $shuData->currentPage() == $i ? 'bg-gray-100 font-bold' : '' }}">
                                {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                            </div>
                        </a>
                        @elseif ($i == 2 || $i == $shuData->lastPage() - 1)
                        <div class="rounded-md px-2 py-0.5 text-sm">...</div>
                        @endif
                        @endfor
            </div>
        </div>

        <div class="absolute right-4 top-1/2 -translate-y-1/2 whitespace-nowrap text-sm text-gray-400">
            Displaying {{ $shuData->firstItem() }} to {{ $shuData->lastItem() }} of {{ $shuData->total() }} items
        </div>
    </div>
</div>
</div>

<!-- Add Modal -->
<div id="addModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
            <div class="flex items-center justify-between p-4 border-b">
                <h3 class="text-lg font-semibold">Tambah Data</h3>
                <button onclick="closeModal('addModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="addForm" class="p-4">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Transaksi</label>
                        <input type="datetime-local" name="tgl_transaksi" id="tgl_transaksi" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Anggota</label>
                        <input type="text" name="nama_anggota" id="nama_anggota" required
                            placeholder="Pilih anggota dari dropdown"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                            readonly>
                        <!-- Hidden input untuk no_ktp -->
                        <input type="hidden" name="no_ktp" id="no_ktp">
                        <!-- Dropdown anggota -->
                        <div id="anggotaDropdown"
                            class="mt-2 border border-gray-300 rounded-lg bg-white shadow-lg max-h-48 overflow-y-auto hidden">
                            @foreach($anggota as $ang)
                            <div class="p-2 hover:bg-gray-100 cursor-pointer anggota-option" data-id="{{ $ang->id }}"
                                data-no-ktp="{{ $ang->no_ktp }}" data-nama="{{ $ang->nama }}"
                                data-foto="{{ $ang->file_pic ?? '' }}">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                                        <i class="fas fa-user text-gray-500"></i>
                                    </div>
                                    <div>
                                        <div class="font-medium text-sm">{{ $ang->nama }}</div>
                                        <div class="text-xs text-gray-500">ID:
                                            AG{{ str_pad($ang->id, 4, '0', STR_PAD_LEFT) }} | KTP: {{ $ang->no_ktp }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah SHU</label>
                        <input type="text" name="jumlah_bayar" id="jumlah_bayar" required placeholder="Masukkan jumlah"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                            oninput="formatNumberSimple(this)" pattern="[0-9,.]*" inputmode="numeric">
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeModal('addModal')"
                        class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 flex items-center space-x-2">
                        <i class="fas fa-times"></i>
                        <span>Batal</span>
                    </button>
                    <button type="submit"
                        class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center space-x-2">
                        <i class="fas fa-check"></i>
                        <span>Simpan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
            <div class="flex items-center justify-between p-4 border-b">
                <h3 class="text-lg font-semibold">Edit Data</h3>
                <button onclick="closeModal('editModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="editForm" class="p-4">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Transaksi</label>
                        <input type="datetime-local" name="tgl_transaksi" id="edit_tgl_transaksi" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Anggota</label>
                        <input type="text" name="nama_anggota" id="edit_nama_anggota" required
                            placeholder="Nama anggota"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                            readonly>
                        <input type="hidden" name="no_ktp" id="edit_no_ktp">
                        <input type="hidden" name="shu_id" id="edit_shu_id">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah SHU</label>
                        <input type="text" name="jumlah_bayar" id="edit_jumlah_bayar" required
                            placeholder="Masukkan jumlah"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                            oninput="formatNumberSimple(this)" pattern="[0-9,.]*" inputmode="numeric">
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeModal('editModal')"
                        class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 flex items-center space-x-2">
                        <i class="fas fa-times"></i>
                        <span>Batal</span>
                    </button>
                    <button type="submit"
                        class="px-6 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 flex items-center space-x-2">
                        <i class="fas fa-edit"></i>
                        <span>Update</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@include('anggota.shu-scripts')