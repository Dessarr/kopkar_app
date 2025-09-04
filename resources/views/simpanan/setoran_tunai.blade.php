@extends('layouts.app')

@section('title', 'Setoran Tunai')
@section('sub-title', 'Setoran Tunai')

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
                    <p class="text-sm font-medium text-gray-600">Total Setoran</p>
                    <p class="text-2xl font-semibold text-gray-900">
                        Rp{{ number_format($transaksiSetoran->sum('jumlah'), 0, ',', '.') }}
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
                    <p class="text-2xl font-semibold text-gray-900">{{ $transaksiSetoran->total() }}</p>
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
            <h3 class="text-base font-semibold text-gray-800">Filter Setoran Tunai</h3>
        </div>

        <form method="GET" action="{{ route('simpanan.setoran.index') }}" id="filterForm">
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
                        <input type="hidden" name="start_date" id="tgl_dari" value="{{ $startDate ?? '' }}">
                        <input type="hidden" name="end_date" id="tgl_sampai" value="{{ $endDate ?? '' }}">
                    </div>

                    <!-- 2. Search -->
                    <div class="flex items-center space-x-2">
                        <label class="text-sm font-medium text-gray-700">Cari:</label>
                        <input type="text" name="search" id="search" value="{{ $search ?? '' }}"
                            placeholder="TRD00001, AG0001, atau Nama"
                            class="px-2 py-1.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 text-sm w-40"
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
            <h2 class="text-lg font-semibold text-gray-800">Data Transaksi Setoran Tunai</h2>
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
                <button onclick="openModal('importModal')"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                    <i class="fas fa-upload"></i>
                    <span>Upload</span>
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
                        <th class="py-3 border px-4">Jenis Simpanan</th>
                        <th class="py-3 border px-4">Jumlah</th>
                        <th class="py-3 border px-4">User</th>
                        <th class="py-3 border px-4">Cetak Nota</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transaksiSetoran as $index => $transaksi)
                    <tr class="text-sm align-middle hover:bg-gray-50 cursor-pointer row-selectable"
                        data-id="{{ $transaksi->id }}"
                        data-kode="TRD{{ str_pad($transaksi->id, 5, '0', STR_PAD_LEFT) }}"
                        data-tanggal="{{ $transaksi->tgl_transaksi }}"
                        data-keterangan="{{ $transaksi->keterangan ?? '' }}" data-no-ktp="{{ $transaksi->no_ktp }}"
                        data-nama-anggota="{{ $transaksi->anggota ? $transaksi->anggota->nama : 'N/A' }}"
                        data-id-anggota="{{ $transaksi->anggota ? $transaksi->anggota->id : 0 }}"
                        data-jumlah="{{ $transaksi->jumlah }}" data-jenis-id="{{ $transaksi->jenis_id }}"
                        data-user="{{ $transaksi->user_name }}" data-akun="{{ $transaksi->akun }}"
                        data-dk="{{ $transaksi->dk }}" data-kas-id="{{ $transaksi->kas_id }}"
                        data-nama-penyetor="{{ $transaksi->nama_penyetor }}"
                        data-no-identitas="{{ $transaksi->no_identitas }}" data-alamat="{{ $transaksi->alamat }}"
                        data-id-cabang="{{ $transaksi->id_cabang }}">
                        <td class="py-3 border px-4">
                            {{ ($transaksiSetoran->currentPage() - 1) * $transaksiSetoran->perPage() + $loop->iteration }}
                        </td>
                        <td class="py-3 border px-4">
                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-medium">
                                TRD{{ str_pad($transaksi->id, 5, '0', STR_PAD_LEFT) }}
                            </span>
                        </td>
                        <td class="py-3 border px-4">
                            {{ $transaksi->tgl_transaksi ? \Carbon\Carbon::parse($transaksi->tgl_transaksi)->format('d F Y - H:i') : '-' }}
                        </td>
                        <td class="py-3 border px-4">
                            AG{{ str_pad($transaksi->anggota ? $transaksi->anggota->id : 0, 4, '0', STR_PAD_LEFT) }}
                        </td>
                        <td class="py-3 border px-4 text-left">
                            <div>
                                <div class="font-medium">{{ $transaksi->anggota ? $transaksi->anggota->nama : 'N/A' }}
                                </div>
                                <div class="text-xs text-gray-500">{{ $transaksi->no_ktp ?? 'N/A' }}</div>
                            </div>
                        </td>
                        <td class="py-3 border px-4">
                            <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded-full text-xs">
                                {{ $transaksi->jenis_simpanan }}
                            </span>
                        </td>
                        <td class="py-3 border px-4 font-semibold text-green-600">
                            {{ number_format($transaksi->jumlah ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="py-3 border px-4">{{ $transaksi->user_name ?? '-' }}</td>
                        <td class="py-3 border px-4">
                            <button onclick="cetakNota('{{ $transaksi->id }}')"
                                class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs hover:bg-blue-200 transition-colors">
                                <i class="fas fa-print mr-1"></i>Nota
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-2"></i>
                            <p>Tidak ada data setoran tunai</p>
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
                @for ($i = 1; $i <= $transaksiSetoran->lastPage(); $i++)
                    @if ($i == 1 || $i == $transaksiSetoran->lastPage() || ($i >= $transaksiSetoran->currentPage() - 1
                    && $i
                    <= $transaksiSetoran->
                        currentPage() + 1))
                        <a href="{{ $transaksiSetoran->url($i) }}">
                            <div
                                class="rounded-md px-2 py-0.5 text-sm border border-gray-300 {{ $transaksiSetoran->currentPage() == $i ? 'bg-gray-100 font-bold' : '' }}">
                                {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                            </div>
                        </a>
                        @elseif ($i == 2 || $i == $transaksiSetoran->lastPage() - 1)
                        <div class="rounded-md px-2 py-0.5 text-sm">...</div>
                        @endif
                        @endfor
            </div>
        </div>

        <div class="absolute right-4 top-1/2 -translate-y-1/2 whitespace-nowrap text-sm text-gray-400">
            Displaying {{ $transaksiSetoran->firstItem() }} to {{ $transaksiSetoran->lastItem() }} of
            {{ $transaksiSetoran->total() }}
            items
        </div>
    </div>
</div>

<!-- Add Modal -->
<div id="addModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between p-4 border-b">
                <h3 class="text-lg font-semibold">Tambah Data</h3>
                <button onclick="closeModal('addModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="addForm" class="p-4">
                <!-- Tanggal Transaksi -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Transaksi</label>
                    <div class="flex items-center space-x-2">
                        <input type="text" name="tgl_transaksi_txt" id="tgl_transaksi_txt" readonly
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50"
                            value="{{ date('d F Y - H:i') }}">
                        <input type="hidden" name="tgl_transaksi" id="tgl_transaksi" value="{{ date('Y-m-d H:i:s') }}">
                        <button type="button" onclick="openDatePicker()"
                            class="px-3 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                            <i class="fas fa-calendar"></i>
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Identitas Penyetor -->
                    <div class="space-y-4">
                        <h4 class="text-md font-semibold text-gray-800 border-b pb-2">Identitas Penyetor</h4>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Penyetor</label>
                            <input type="text" name="nama_penyetor" id="nama_penyetor" required
                                placeholder="Nama lengkap penyetor"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Identitas</label>
                            <input type="text" name="no_identitas" id="no_identitas" required
                                placeholder="No KTP/SIM penyetor"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Alamat</label>
                            <textarea name="alamat" id="alamat" rows="3" required placeholder="Alamat lengkap penyetor"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 resize-none"></textarea>
                        </div>
                    </div>

                    <!-- Identitas Penerima -->
                    <div class="space-y-4">
                        <h4 class="text-md font-semibold text-gray-800 border-b pb-2">Identitas Penerima</h4>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Anggota</label>
                            <div class="relative">
                                <input type="text" name="nama_anggota" id="nama_anggota" required
                                    placeholder="Pilih anggota dari dropdown"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
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
                                                @if($ang->file_pic)
                                                <img src="{{ asset('uploads/anggota/' . $ang->file_pic) }}"
                                                    class="w-10 h-10 rounded-full object-cover" alt="Photo">
                                                @else
                                                <i class="fas fa-user text-gray-500"></i>
                                                @endif
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

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Simpanan</label>
                            <select name="jenis_id" id="jenis_id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                                onchange="autoFillJumlah()">
                                <option value="">-- Pilih Simpanan --</option>
                                @foreach($jenisSimpanan as $jenis)
                                <option value="{{ $jenis->id }}" data-jumlah="{{ $jenis->jumlah ?? 0 }}">
                                    {{ $jenis->jns_simpan }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah Simpanan</label>
                            <input type="text" name="jumlah" id="jumlah" required placeholder="Masukkan jumlah"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                                oninput="formatNumberSimple(this)" pattern="[0-9,.]*" inputmode="numeric">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                            <input type="text" name="keterangan" id="keterangan" placeholder="Keterangan (opsional)"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Simpan Ke Kas</label>
                            <select name="kas_id" id="kas_id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                                <option value="">-- Pilih Kas --</option>
                                @foreach($dataKas as $kas)
                                <option value="{{ $kas->id }}">{{ $kas->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Photo Section -->
                <div class="mt-6">
                    <h4 class="text-md font-semibold text-gray-800 border-b pb-2 mb-4">Photo</h4>
                    <div class="flex justify-center">
                        <div id="anggotaPhoto"
                            class="w-32 h-40 bg-gray-200 rounded-lg flex items-center justify-center border-2 border-dashed border-gray-300">
                            <div class="text-center text-gray-500">
                                <i class="fas fa-user text-4xl mb-2"></i>
                                <p class="text-sm">Pilih anggota untuk melihat foto</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hidden Fields -->
                <input type="hidden" name="akun" value="Setoran">
                <input type="hidden" name="dk" value="D">
                <input type="hidden" name="id_cabang" value="CB0001">

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
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between p-4 border-b">
                <h3 class="text-lg font-semibold">Edit Data Setoran Tunai</h3>
                <button onclick="closeModal('editModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="editForm" class="p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                        <input type="hidden" name="anggota_id" id="edit_anggota_id">
                        <input type="hidden" name="setoran_id" id="edit_setoran_id">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Simpanan</label>
                        <select name="jenis_id" id="edit_jenis_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="">Pilih Jenis Simpanan</option>
                            @foreach($jenisSimpanan as $jenis)
                            <option value="{{ $jenis->id }}">{{ $jenis->nama_simpanan }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah</label>
                        <input type="text" name="jumlah" id="edit_jumlah" required placeholder="Masukkan jumlah"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                            oninput="formatNumberSimple(this)" pattern="[0-9,.]*" inputmode="numeric">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                        <input type="text" name="keterangan" id="edit_keterangan" placeholder="Keterangan (opsional)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Akun</label>
                        <select name="akun" id="edit_akun" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="">Pilih Akun</option>
                            <option value="Setoran">Setoran</option>
                            <option value="Penarikan">Penarikan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">D/K</label>
                        <select name="dk" id="edit_dk" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="">Pilih D/K</option>
                            <option value="D">Debit (D)</option>
                            <option value="K">Kredit (K)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kas</label>
                        <select name="kas_id" id="edit_kas_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="">Pilih Kas</option>
                            @foreach($dataKas as $kas)
                            <option value="{{ $kas->id }}">{{ $kas->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Penyetor</label>
                        <input type="text" name="nama_penyetor" id="edit_nama_penyetor" required
                            placeholder="Nama penyetor"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">No Identitas</label>
                        <input type="text" name="no_identitas" id="edit_no_identitas" required
                            placeholder="No identitas"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Alamat</label>
                        <input type="text" name="alamat" id="edit_alamat" required placeholder="Alamat"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">ID Cabang</label>
                        <input type="text" name="id_cabang" id="edit_id_cabang" required placeholder="ID Cabang"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
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

<!-- Modal Import Excel -->
<div id="importModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Import Data Setoran Tunai</h3>
            <form method="POST" action="{{ route('simpanan.setoran.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label for="file" class="block text-sm font-medium text-gray-700 mb-1">File Excel</label>
                    <input type="file" id="file" name="file" accept=".xls,.xlsx" required
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#14AE5C] focus:ring focus:ring-[#14AE5C] focus:ring-opacity-50">
                    <p class="text-xs text-gray-500 mt-1">Format: Kolom A (Tanggal), B (No KTP), C (Jenis ID), D
                        (Jumlah), E (Keterangan)</p>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeImportModal()"
                        class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
                        Batal
                    </button>
                    <button type="submit" class="bg-[#14AE5C] text-white px-4 py-2 rounded-md hover:bg-[#14AE5C]/80">
                        Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@include('simpanan.setoran_tunai-scripts')