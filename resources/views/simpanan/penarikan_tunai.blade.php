@extends('layouts.app')

@section('title', 'Penarikan Tunai')
@section('sub-title', 'Penarikan Tunai')

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
                <div class="p-3 rounded-full bg-red-100 text-red-600">
                    <i class="fas fa-money-bill-wave text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Penarikan</p>
                    <p class="text-2xl font-semibold text-gray-900">
                        Rp{{ number_format($transaksiPenarikan->sum('jumlah'), 0, ',', '.') }}
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
                    <p class="text-2xl font-semibold text-gray-900">{{ $transaksiPenarikan->total() }}</p>
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

    <!-- Filter Section -->
    <div class="bg-white rounded-lg shadow-md p-3 mb-4">
        <div class="flex items-center justify-between mb-2">
            <h3 class="text-base font-semibold text-gray-800">Filter Penarikan Tunai</h3>
        </div>

        <form method="GET" action="{{ route('simpanan.penarikan') }}" id="filterForm">
            <div class="flex flex-wrap items-center justify-between gap-2 py-2 px-2 bg-gray-50 rounded-lg">
                <div class="flex items-center space-x-3">
                    <!-- Tanggal -->
                    <div class="flex items-center space-x-2">
                        <label class="text-sm font-medium text-gray-700">Tanggal:</label>
                        <button type="button" id="daterange-btn"
                            class="px-3 py-1.5 border border-gray-300 rounded-lg bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500 text-sm">
                            <i class="fas fa-calendar mr-1"></i>
                            <span id="daterange-text">Pilih Tanggal</span>
                            <i class="fas fa-chevron-down ml-1"></i>
                        </button>
                        <input type="hidden" name="start_date" id="tgl_dari" value="{{ $startDate ?? '' }}">
                        <input type="hidden" name="end_date" id="tgl_sampai" value="{{ $endDate ?? '' }}">
                    </div>

                    <!-- Search -->
                    <div class="flex items-center space-x-2">
                        <label class="text-sm font-medium text-gray-700">Cari:</label>
                        <input type="text" name="search" id="search" value="{{ $search ?? '' }}"
                            placeholder="TRK00001, AG0001, atau Nama"
                            class="px-2 py-1.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 text-sm w-40"
                            onkeypress="if(event.key==='Enter'){doSearch();}">
                    </div>

                    <!-- Button Filter -->
                    <button type="button" onclick="doSearch()" id="searchBtn"
                        class="px-3 py-1.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm">
                        <i class="fas fa-search mr-1"></i>Cari
                    </button>
                </div>

                <div class="flex items-center space-x-2">
                    <!-- Button Cetak Laporan -->
                    <button type="button" onclick="cetakLaporan()"
                        class="px-3 py-1.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm">
                        <i class="fas fa-print mr-1"></i>Cetak Laporan
                    </button>

                    <!-- Button Hapus Filter -->
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
            <h2 class="text-lg font-semibold text-gray-800">Data Transaksi Penarikan Tunai</h2>
            <div class="flex space-x-3">
                <button onclick="openModal('addModal')"
                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
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
                        <th class="py-3 border px-4">Jenis Penarikan</th>
                        <th class="py-3 border px-4">Jumlah</th>
                        <th class="py-3 border px-4">User</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transaksiPenarikan as $index => $t)
                    <tr class="text-sm align-middle hover:bg-gray-50 cursor-pointer row-selectable"
                        data-id="{{ $t->id }}" onclick="selectRow(this)"
                        data-kode="TRK{{ str_pad($t->id, 5, '0', STR_PAD_LEFT) }}"
                        data-tanggal="{{ $t->tgl_transaksi }}" data-keterangan="{{ $t->keterangan ?? '' }}"
                        data-no-ktp="{{ $t->no_ktp }}" data-nama-anggota="{{ $t->anggota ? $t->anggota->nama : 'N/A' }}"
                        data-id-anggota="{{ $t->anggota ? $t->anggota->id : 0 }}" data-jumlah="{{ $t->jumlah }}"
                        data-jenis-id="{{ $t->jenis_id }}" data-user="{{ $t->user_name }}"
                        data-nama-penyetor="{{ $t->nama_penyetor }}" data-no-identitas="{{ $t->no_identitas }}"
                        data-alamat="{{ $t->alamat }}" data-kas-id="{{ $t->kas_id }}">
                        <td class="py-3 border px-4">
                            {{ ($transaksiPenarikan->currentPage() - 1) * $transaksiPenarikan->perPage() + $loop->iteration }}
                        </td>
                        <td class="py-3 border px-4">
                            <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs font-medium">
                                TRK{{ str_pad($t->id, 5, '0', STR_PAD_LEFT) }}
                            </span>
                        </td>
                        <td class="py-3 border px-4">
                            {{ $t->tgl_transaksi ? \Carbon\Carbon::parse($t->tgl_transaksi)->format('d F Y - H:i') : '-' }}
                        </td>
                        <td class="py-3 border px-4">
                            AG{{ str_pad($t->anggota ? $t->anggota->id : 0, 4, '0', STR_PAD_LEFT) }}
                        </td>
                        <td class="py-3 border px-4 text-left">
                            <div>
                                <div class="font-medium">{{ $t->anggota ? $t->anggota->nama : 'N/A' }}</div>
                                <div class="text-xs text-gray-500">{{ $t->no_ktp ?? 'N/A' }}</div>
                            </div>
                        </td>
                        <td class="py-3 border px-4">
                            <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded-full text-xs">
                                @php
                                $jenis = \App\Models\jns_simpan::find($t->jenis_id);
                                @endphp
                                {{ $jenis ? $jenis->jns_simpan : 'N/A' }}
                            </span>
                        </td>
                        <td class="py-3 border px-4 font-semibold text-red-600">
                            {{ number_format($t->jumlah ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="py-3 border px-4">{{ $t->user_name ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-2"></i>
                            <p>Tidak ada data penarikan</p>
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
                @for ($i = 1; $i <= $transaksiPenarikan->lastPage(); $i++)
                    @if ($i == 1 || $i == $transaksiPenarikan->lastPage() || ($i >= $transaksiPenarikan->currentPage() -
                    1 && $i <= $transaksiPenarikan->currentPage() + 1))
                        <a href="{{ $transaksiPenarikan->appends(request()->query())->url($i) }}">
                            <div
                                class="rounded-md px-2 py-0.5 text-sm border border-gray-300 {{ $transaksiPenarikan->currentPage() == $i ? 'bg-gray-100 font-bold' : '' }}">
                                {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                            </div>
                        </a>
                        @elseif ($i == 2 || $i == $transaksiPenarikan->lastPage() - 1)
                        <div class="rounded-md px-2 py-0.5 text-sm">...</div>
                        @endif
                        @endfor
            </div>
        </div>

        <div class="absolute right-4 top-1/2 -translate-y-1/2 whitespace-nowrap text-sm text-gray-400">
            Displaying {{ $transaksiPenarikan->firstItem() }} to {{ $transaksiPenarikan->lastItem() }} of
            {{ $transaksiPenarikan->total() }} items
        </div>
    </div>
</div>

<!-- Add Modal -->
<div id="addModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between p-4 border-b">
                <h3 class="text-lg font-semibold">Tambah Data Penarikan</h3>
                <button onclick="closeModal('addModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="addForm" class="p-4">
                <!-- Tanggal Transaksi -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Transaksi</label>
                    <input type="date" name="tgl_transaksi" id="tgl_transaksi" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                        value="{{ date('Y-m-d') }}">
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Identitas Anggota -->
                    <div class="space-y-4">
                        <h4 class="text-md font-semibold text-gray-800 border-b pb-2">Identitas Anggota</h4>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Anggota</label>
                            <div class="relative">
                                <input type="text" name="nama_anggota" id="nama_anggota" required
                                    placeholder="Pilih anggota dari dropdown"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                                    readonly onclick="toggleAnggotaDropdown()">
                                <input type="hidden" name="no_ktp" id="no_ktp">
                                <input type="hidden" name="anggota_id" id="anggota_id">
                                <div class="absolute right-3 top-1/2 transform -translate-y-1/2">
                                    <i class="fas fa-chevron-down text-gray-400"></i>
                                </div>
                            </div>

                            <!-- Anggota Dropdown -->
                            <div id="anggotaDropdown"
                                class="mt-2 border border-gray-300 rounded-lg bg-white shadow-lg max-h-48 overflow-y-auto hidden">
                                <div class="p-2">
                                    <input type="text" id="searchAnggota" placeholder="Cari anggota..."
                                        class="w-full px-2 py-1 border border-gray-300 rounded text-sm"
                                        onkeyup="filterAnggota(this.value)">
                                </div>
                                <div id="anggotaList">
                                    @foreach($dataAnggota as $ang)
                                    <div class="p-2 hover:bg-gray-100 cursor-pointer anggota-option border-b border-gray-100"
                                        data-id="{{ $ang->id }}" data-no-ktp="{{ $ang->no_ktp }}"
                                        data-nama="{{ $ang->nama }}" onclick="selectAnggota(this)">
                                        <div class="flex items-center space-x-3">
                                            <div
                                                class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                                                <i class="fas fa-user text-gray-500"></i>
                                            </div>
                                            <div class="flex-1">
                                                <div class="font-medium text-sm">{{ $ang->nama }}</div>
                                                <div class="text-xs text-gray-500">
                                                    ID: AG{{ str_pad($ang->id, 4, '0', STR_PAD_LEFT) }} | KTP:
                                                    {{ $ang->no_ktp }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Penarikan -->
                    <div class="space-y-4">
                        <h4 class="text-md font-semibold text-gray-800 border-b pb-2">Data Penarikan</h4>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Simpanan</label>
                            <select name="jenis_id" id="jenis_id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                                <option value="">-- Pilih Jenis Simpanan --</option>
                                @foreach($jenisSimpanan as $jenis)
                                <option value="{{ $jenis->id }}">{{ $jenis->jns_simpan }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah Penarikan</label>
                            <input type="text" name="jumlah" id="jumlah" required placeholder="Masukkan jumlah"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                                oninput="formatNumberSimple(this)" pattern="[0-9,.]*" inputmode="numeric">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                            <input type="text" name="keterangan" id="keterangan" placeholder="Keterangan (opsional)"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Kas</label>
                            <select name="kas_id" id="kas_id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                                <option value="">-- Pilih Kas --</option>
                                @if(isset($dataKas) && $dataKas->count() > 0)
                                @foreach($dataKas as $kas)
                                <option value="{{ $kas->id }}">{{ $kas->nama }}</option>
                                @endforeach
                                @else
                                <option value="">Tidak ada data kas</option>
                                @endif
                            </select>
                            <!-- Debug info -->
                            <div class="text-xs text-gray-500 mt-1">
                                Debug: {{ isset($dataKas) ? $dataKas->count() : 'No dataKas variable' }} kas records
                                found
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Kuasa</label>
                            <input type="text" name="nama_penyetor" id="nama_penyetor" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                                placeholder="Nama penarik">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">No Identitas</label>
                            <input type="text" name="no_identitas" id="no_identitas" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                                placeholder="No identitas">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Alamat</label>
                            <input type="text" name="alamat" id="alamat" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                                placeholder="Alamat">
                        </div>


                        <!-- Hidden fields -->
                        <input type="hidden" name="akun" value="Penarikan">
                        <input type="hidden" name="dk" value="K">
                    </div>
                </div>

                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeModal('addModal')"
                        class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 flex items-center space-x-2">
                        <i class="fas fa-times"></i>
                        <span>Batal</span>
                    </button>
                    <button type="submit"
                        class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 flex items-center space-x-2">
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
        <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between p-4 border-b">
                <h3 class="text-lg font-semibold">Edit Data Penarikan</h3>
                <button onclick="closeModal('editModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="editForm" class="p-4">
                <!-- Tanggal Transaksi -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Transaksi</label>
                    <input type="date" name="tgl_transaksi" id="edit_tgl_transaksi" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Identitas Anggota -->
                    <div class="space-y-4">
                        <h4 class="text-md font-semibold text-gray-800 border-b pb-2">Identitas Anggota</h4>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Anggota</label>
                            <input type="text" name="nama_anggota" id="edit_nama_anggota" required
                                placeholder="Nama anggota"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 bg-gray-100"
                                readonly>
                            <input type="hidden" name="no_ktp" id="edit_no_ktp">
                            <input type="hidden" name="anggota_id" id="edit_anggota_id">
                            <input type="hidden" name="penarikan_id" id="edit_penarikan_id">
                            <p class="text-xs text-gray-500 mt-1">Anggota tidak dapat diubah saat edit</p>
                        </div>
                    </div>

                    <!-- Data Penarikan -->
                    <div class="space-y-4">
                        <h4 class="text-md font-semibold text-gray-800 border-b pb-2">Data Penarikan</h4>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Simpanan</label>
                            <select name="jenis_id" id="edit_jenis_id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                                <option value="">-- Pilih Jenis Simpanan --</option>
                                @foreach($jenisSimpanan as $jenis)
                                <option value="{{ $jenis->id }}">{{ $jenis->jns_simpan }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah Penarikan</label>
                            <input type="text" name="jumlah" id="edit_jumlah" required placeholder="Masukkan jumlah"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                                oninput="formatNumberSimple(this)" pattern="[0-9,.]*" inputmode="numeric">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                            <input type="text" name="keterangan" id="edit_keterangan"
                                placeholder="Keterangan (opsional)"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Kas</label>
                            <select name="kas_id" id="edit_kas_id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                                <option value="">-- Pilih Kas --</option>
                                @if(isset($dataKas) && $dataKas->count() > 0)
                                @foreach($dataKas as $kas)
                                <option value="{{ $kas->id }}">{{ $kas->nama }}</option>
                                @endforeach
                                @else
                                <option value="">Tidak ada data kas</option>
                                @endif
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Penarik</label>
                            <input type="text" name="nama_penyetor" id="edit_nama_penyetor" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                                placeholder="Nama penarik">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">No Identitas</label>
                            <input type="text" name="no_identitas" id="edit_no_identitas" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                                placeholder="No identitas">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Alamat</label>
                            <input type="text" name="alamat" id="edit_alamat" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                                placeholder="Alamat">
                        </div>


                        <!-- Hidden fields -->
                        <input type="hidden" name="akun" value="Penarikan">
                        <input type="hidden" name="dk" value="K">
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

<!-- Include required libraries -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<script>
// Global variables
let selectedRowData = null;

// Modal functions
function openModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
    if (modalId === 'addModal') {
        document.getElementById('addForm').reset();
        document.getElementById('tgl_transaksi').value = '{{ date("Y-m-d") }}';
    }
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
    selectedRowData = null;
}

// Table row selection
function selectRow(row) {
    document.querySelectorAll('tr').forEach(r => r.classList.remove('bg-blue-50'));
    row.classList.add('bg-blue-50');

    selectedRowData = {
        id: row.dataset.id,
        kode: row.dataset.kode,
        tgl_transaksi: row.dataset.tanggal,
        nama_anggota: row.dataset.namaAnggota,
        id_anggota: row.dataset.idAnggota,
        no_ktp: row.dataset.noKtp,
        jenis_id: row.dataset.jenisId,
        jumlah: row.dataset.jumlah,
        keterangan: row.dataset.keterangan,
        user: row.dataset.user,
        nama_penyetor: row.dataset.namaPenyetor,
        no_identitas: row.dataset.noIdentitas,
        alamat: row.dataset.alamat,
        kas_id: row.dataset.kasId
    };
}

// Edit data function
function editData() {
    if (!selectedRowData) {
        alert('Pilih data yang akan diedit terlebih dahulu!');
        return;
    }

    // Populate edit form
    if (selectedRowData.tgl_transaksi) {
        const date = new Date(selectedRowData.tgl_transaksi);
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const dateString = `${year}-${month}-${day}`;
        document.getElementById('edit_tgl_transaksi').value = dateString;
    }

    document.getElementById('edit_nama_anggota').value = selectedRowData.nama_anggota;
    document.getElementById('edit_no_ktp').value = selectedRowData.no_ktp;
    document.getElementById('edit_anggota_id').value = selectedRowData.id_anggota;
    document.getElementById('edit_penarikan_id').value = selectedRowData.id;
    document.getElementById('edit_jenis_id').value = selectedRowData.jenis_id;

    const amount = parseFloat(selectedRowData.jumlah);
    const formattedAmount = amount.toLocaleString('id-ID');
    document.getElementById('edit_jumlah').value = formattedAmount;

    document.getElementById('edit_keterangan').value = selectedRowData.keterangan || '';
    document.getElementById('edit_kas_id').value = selectedRowData.kas_id;
    document.getElementById('edit_nama_penyetor').value = selectedRowData.nama_penyetor;
    document.getElementById('edit_no_identitas').value = selectedRowData.no_identitas;
    document.getElementById('edit_alamat').value = selectedRowData.alamat;

    openModal('editModal');
}

// Delete data function
function deleteData() {
    if (!selectedRowData) {
        alert('Pilih data yang akan dihapus terlebih dahulu!');
        return;
    }

    if (confirm(`Apakah Anda yakin ingin menghapus data kode transaksi: ${selectedRowData.kode}?`)) {
        const deleteUrl = `{{ url('simpanan/penarikan') }}/${selectedRowData.id}/delete`;

        fetch(deleteUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Data berhasil dihapus');
                    location.reload();
                } else {
                    alert('Gagal menghapus data: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menghapus data');
            });
    }
}

// Number formatting functions
function formatNumberSimple(input) {
    let value = input.value.replace(/[^0-9.]/g, '');
    
    // Remove multiple decimal points
    const parts = value.split('.');
    if (parts.length > 2) {
        value = parts[0] + '.' + parts.slice(1).join('');
    }
    
    if (value) {
        const number = parseFloat(value);
        if (!isNaN(number) && number > 0) {
            input.value = number.toString();
        } else {
            input.value = '';
        }
    } else {
        input.value = '';
    }
}

function getRawNumber(input) {
    return input.value.replace(/[^0-9.]/g, '');
}

// Form submission handlers
document.addEventListener('DOMContentLoaded', function() {
    // Add form submission
    const addForm = document.getElementById('addForm');
    if (addForm) {
        addForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const data = Object.fromEntries(formData);

            const jumlahInput = document.getElementById('jumlah');
            data.jumlah = getRawNumber(jumlahInput);

            // Validation
            if (!data.tgl_transaksi) {
                alert('Tanggal Transaksi harus diisi');
                return;
            }
            if (!data.no_ktp) {
                alert('No KTP harus diisi');
                return;
            }
            if (!data.jenis_id) {
                alert('Jenis Simpanan harus dipilih');
                return;
            }
            if (!data.jumlah || parseFloat(data.jumlah) <= 0) {
                alert('Jumlah harus lebih dari 0');
                return;
            }

            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Menyimpan...';

            try {
                const response = await fetch("{{ route('simpanan.store.penarikan') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                            .getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    alert('Data berhasil disimpan');
                    closeModal('addModal');
                    location.reload();
                } else {
                    alert('Gagal menyimpan data: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan koneksi, silahkan ulangi.');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
    }

    // Edit form submission
    const editForm = document.getElementById('editForm');
    if (editForm) {
        editForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            if (!selectedRowData) {
                alert('Tidak ada data yang dipilih untuk diedit');
                return;
            }

            const formData = new FormData(this);
            const data = Object.fromEntries(formData);

            const jumlahInput = document.getElementById('edit_jumlah');
            data.jumlah = getRawNumber(jumlahInput);

            // Validation
            if (!data.tgl_transaksi) {
                alert('Tanggal Transaksi harus diisi');
                return;
            }
            if (!data.no_ktp) {
                alert('No KTP harus diisi');
                return;
            }
            if (!data.anggota_id) {
                alert('ID Anggota harus diisi');
                return;
            }
            if (!data.jenis_id) {
                alert('Jenis Simpanan harus dipilih');
                return;
            }
            if (!data.jumlah || parseFloat(data.jumlah) <= 0) {
                alert('Jumlah harus lebih dari 0');
                return;
            }

            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Mengupdate...';

            try {
                const updateUrl = `{{ url('simpanan/penarikan') }}/${selectedRowData.id}`;
                const response = await fetch(updateUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                            .getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    alert('Data berhasil diupdate');
                    closeModal('editModal');
                    location.reload();
                } else {
                    alert('Gagal mengupdate data: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat mengupdate data');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
    }
});

// Anggota dropdown functions
function toggleAnggotaDropdown() {
    const dropdown = document.getElementById('anggotaDropdown');
    dropdown.classList.toggle('hidden');
}

function selectAnggota(element) {
    const id = element.dataset.id;
    const noKtp = element.dataset.noKtp;
    const nama = element.dataset.nama;

    document.getElementById('nama_anggota').value = nama;
    document.getElementById('no_ktp').value = noKtp;
    document.getElementById('anggota_id').value = id;

    document.getElementById('anggotaDropdown').classList.add('hidden');
}

function filterAnggota(searchTerm) {
    const options = document.querySelectorAll('.anggota-option');
    const term = searchTerm.toLowerCase();

    options.forEach(option => {
        const nama = option.dataset.nama.toLowerCase();
        if (nama.includes(term)) {
            option.style.display = 'block';
        } else {
            option.style.display = 'none';
        }
    });
}

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    const dropdown = document.getElementById('anggotaDropdown');
    const input = document.getElementById('nama_anggota');

    if (!dropdown.contains(e.target) && !input.contains(e.target)) {
        dropdown.classList.add('hidden');
    }
});

// Filter functions
function doSearch() {
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);

    // Build query parameters
    const params = new URLSearchParams();

    // Search term - clean it like SHU page but also handle AG prefix
    const search = formData.get('search');
    if (search && search.trim() !== '') {
        let cleanSearch = search.replace(/TRK|AG/gi, '').replace(/^0+/, '');
        params.append('search', cleanSearch);
    }

    // Date Range
    const startDate = formData.get('start_date');
    const endDate = formData.get('end_date');
    if (startDate && endDate) {
        params.append('start_date', startDate);
        params.append('end_date', endDate);
    }

    // Redirect with parameters
    window.location.href = "{{ route('simpanan.penarikan') }}?" + params.toString();
}

function clearFilters() {
    // Reset date range picker
    $('#daterange-text').text('Pilih Tanggal');
    $('#tgl_dari').val('');
    $('#tgl_sampai').val('');

    // Reset search
    $('#search').val('');

    // Reload page
    window.location.href = "{{ route('simpanan.penarikan') }}";
}

function cetakLaporan() {
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);

    const params = new URLSearchParams();

    const search = formData.get('search');
    if (search && search.trim() !== '') {
        let cleanSearch = search.replace(/TRK|AG/gi, '').replace(/^0+/, '');
        params.append('search', cleanSearch);
    }

    const startDate = formData.get('start_date');
    const endDate = formData.get('end_date');
    if (startDate && endDate) {
        params.append('start_date', startDate);
        params.append('end_date', endDate);
    }

    window.open("{{ route('simpanan.penarikan.export') }}?" + params.toString(), '_blank');
}

// Date range picker initialization
$(document).ready(function() {
    // Initialize Date Range Picker
    initializeDateRangePicker();
});

// Initialize Date Range Picker with Preset Ranges
function initializeDateRangePicker() {
    $('#daterange-btn').daterangepicker({
        ranges: {
            'Hari ini': [moment(), moment()],
            'Kemarin': [moment().subtract('days', 1), moment().subtract('days', 1)],
            '7 Hari yang lalu': [moment().subtract('days', 6), moment()],
            '30 Hari yang lalu': [moment().subtract('days', 29), moment()],
            'Bulan ini': [moment().startOf('month'), moment().endOf('month')],
            'Bulan kemarin': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1)
                .endOf('month')
            ],
            'Tahun ini': [moment().startOf('year'), moment().endOf('year')],
            'Tahun kemarin': [moment().subtract('year', 1).startOf('year'), moment().subtract('year', 1).endOf(
                'year')]
        },
        showDropdowns: true,
        format: 'YYYY-MM-DD',
        startDate: moment().startOf('year'),
        endDate: moment().endOf('year'),
        autoApply: true,
        locale: {
            format: 'DD/MM/YYYY',
            separator: ' - ',
            applyLabel: 'Terapkan',
            cancelLabel: 'Batal',
            fromLabel: 'Dari',
            toLabel: 'Sampai',
            customRangeLabel: 'Pilih Manual',
            weekLabel: 'W',
            daysOfWeek: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
            monthNames: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ],
            firstDay: 1
        }
    }, function(start, end, label) {
        // Update hidden inputs
        $('#tgl_dari').val(start.format('YYYY-MM-DD'));
        $('#tgl_sampai').val(end.format('YYYY-MM-DD'));

        // Update display text
        $('#daterange-text').text(start.format('DD/MM/YYYY') + ' - ' + end.format('DD/MM/YYYY'));
    });

    // Set initial display text if values exist
    const startDate = document.getElementById('tgl_dari').value;
    const endDate = document.getElementById('tgl_sampai').value;

    if (startDate && endDate) {
        const start = moment(startDate);
        const end = moment(endDate);
        document.getElementById('daterange-text').textContent = start.format('DD/MM/YYYY') + ' - ' + end.format(
            'DD/MM/YYYY');
    }
}
</script>