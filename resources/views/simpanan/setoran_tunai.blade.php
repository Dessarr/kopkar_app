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
                        data-id="{{ $transaksi->id }}" onclick="selectRow(this)"
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
                        <a href="{{ $transaksiSetoran->appends(request()->query())->url($i) }}">
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
                        <input type="datetime-local" name="tgl_transaksi" id="tgl_transaksi" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                            value="{{ date('Y-m-d\TH:i') }}">
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
        <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between p-4 border-b">
                <h3 class="text-lg font-semibold">Edit Data Setoran Tunai</h3>
                <button onclick="closeModal('editModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="editForm" class="p-4">
                <!-- Tanggal Transaksi -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Transaksi</label>
                    <div class="flex items-center space-x-2">
                        <input type="datetime-local" name="tgl_transaksi" id="edit_tgl_transaksi" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Identitas Penyetor -->
                    <div class="space-y-4">
                        <h4 class="text-md font-semibold text-gray-800 border-b pb-2">Identitas Penyetor</h4>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Penyetor</label>
                            <input type="text" name="nama_penyetor" id="edit_nama_penyetor" required
                                placeholder="Nama lengkap penyetor"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Identitas</label>
                            <input type="text" name="no_identitas" id="edit_no_identitas" required
                                placeholder="No KTP/SIM penyetor"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Alamat</label>
                            <textarea name="alamat" id="edit_alamat" rows="3" required
                                placeholder="Alamat lengkap penyetor"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 resize-none"></textarea>
                        </div>
                    </div>

                    <!-- Identitas Penerima -->
                    <div class="space-y-4">
                        <h4 class="text-md font-semibold text-gray-800 border-b pb-2">Identitas Penerima</h4>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Anggota</label>
                            <input type="text" name="nama_anggota" id="edit_nama_anggota" required
                                placeholder="Nama anggota"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 bg-gray-100"
                                readonly>
                            <input type="hidden" name="no_ktp" id="edit_no_ktp">
                            <input type="hidden" name="anggota_id" id="edit_anggota_id">
                            <input type="hidden" name="setoran_id" id="edit_setoran_id">
                            <p class="text-xs text-gray-500 mt-1">Anggota tidak dapat diubah saat edit</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Simpanan</label>
                            <select name="jenis_id" id="edit_jenis_id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                                <option value="">-- Pilih Simpanan --</option>
                                @foreach($jenisSimpanan as $jenis)
                                <option value="{{ $jenis->id }}">{{ $jenis->jns_simpan }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah Simpanan</label>
                            <input type="text" name="jumlah" id="edit_jumlah" required placeholder="Masukkan jumlah"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                                oninput="formatNumberSimple(this)" pattern="[0-9,.]*" inputmode="numeric">
                            <p class="text-xs text-gray-500 mt-1">Gunakan angka saja, separator akan ditambahkan
                                otomatis</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                            <input type="text" name="keterangan" id="edit_keterangan"
                                placeholder="Keterangan (opsional)"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Simpan Ke Kas</label>
                            <select name="kas_id" id="edit_kas_id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                                <option value="">-- Pilih Kas --</option>
                                @foreach($dataKas as $kas)
                                <option value="{{ $kas->id }}">{{ $kas->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Hidden Fields -->
                <input type="hidden" name="akun" value="Setoran">
                <input type="hidden" name="dk" value="D">
                <input type="hidden" name="id_cabang" value="CB0001">

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

<script>
// Global variables
let selectedRowData = null;

// Modal functions
function openModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');

    // Set default values untuk form add
    if (modalId === 'addModal') {
        // Reset form first
        document.getElementById('addForm').reset();

        // Set tanggal sekarang sebagai default
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const datetimeString = `${year}-${month}-${day}T${hours}:${minutes}`;
        document.getElementById('tgl_transaksi').value = datetimeString;

        // Set default values
        document.querySelector('#addModal input[name="akun"]').value = 'Setoran';
        document.querySelector('#addModal input[name="dk"]').value = 'D';
        document.querySelector('#addModal input[name="id_cabang"]').value = '1';
    }
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
    selectedRowData = null;
}

// Table row selection
function selectRow(row) {
    // Remove previous selection
    document.querySelectorAll('tr').forEach(r => r.classList.remove('bg-blue-50'));

    // Add selection to current row
    row.classList.add('bg-blue-50');

    // Get row data from data attributes
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
        akun: row.dataset.akun,
        dk: row.dataset.dk,
        kas_id: row.dataset.kasId,
        nama_penyetor: row.dataset.namaPenyetor,
        no_identitas: row.dataset.noIdentitas,
        alamat: row.dataset.alamat,
        id_cabang: row.dataset.idCabang
    };
}

// Edit data function
function editData() {
    if (!selectedRowData) {
        alert('Pilih data yang akan diedit terlebih dahulu!');
        return;
    }

    // Populate edit form with selected data
    // Convert datetime to datetime-local format
    if (selectedRowData.tgl_transaksi) {
        const date = new Date(selectedRowData.tgl_transaksi);
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        const datetimeString = `${year}-${month}-${day}T${hours}:${minutes}`;
        document.getElementById('edit_tgl_transaksi').value = datetimeString;
    }

    // Identitas Penerima (Anggota - DISABLED saat edit)
    document.getElementById('edit_nama_anggota').value = selectedRowData.nama_anggota;
    document.getElementById('edit_no_ktp').value = selectedRowData.no_ktp;
    document.getElementById('edit_anggota_id').value = selectedRowData.id_anggota;
    document.getElementById('edit_setoran_id').value = selectedRowData.id;
    document.getElementById('edit_jenis_id').value = selectedRowData.jenis_id;

    // Format the amount value for display with thousand separators
    const amount = parseFloat(selectedRowData.jumlah);
    const formattedAmount = amount.toLocaleString('id-ID');
    document.getElementById('edit_jumlah').value = formattedAmount;

    document.getElementById('edit_keterangan').value = selectedRowData.keterangan || '';
    document.getElementById('edit_kas_id').value = selectedRowData.kas_id;

    // Identitas Penyetor
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
        // Kirim request delete
        const deleteUrl = `{{ url('simpanan/setoran') }}/${selectedRowData.id}/delete`;

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
function formatNumber(input) {
    let value = input.value.replace(/[^0-9]/g, '');
    if (value) {
        value = parseInt(value).toLocaleString('id-ID');
    }
    input.value = value;
}

function getRawNumber(input) {
    return input.value.replace(/[^0-9]/g, '');
}

function getRawNumberEdit(input) {
    return input.value.replace(/[^0-9]/g, '');
}

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

function autoFillJumlah() {
    const jenisSelect = document.getElementById('jenis_id');
    const jumlahInput = document.getElementById('jumlah');
    const selectedOption = jenisSelect.options[jenisSelect.selectedIndex];

    if (selectedOption && selectedOption.dataset.jumlah) {
        const jumlah = selectedOption.dataset.jumlah;
        if (jumlah && jumlah > 0) {
            jumlahInput.value = parseInt(jumlah).toLocaleString('id-ID');
        }
    }
}

// Form validation
function validateForm(data) {
    const errors = [];

    if (!data.tgl_transaksi) {
        errors.push('Tanggal Transaksi harus diisi');
    }
    if (!data.no_ktp) {
        errors.push('No KTP harus diisi');
    }
    if (!data.jenis_id) {
        errors.push('Jenis Simpanan harus dipilih');
    }
    if (!data.jumlah || parseFloat(data.jumlah) <= 0) {
        errors.push('Jumlah harus lebih dari 0');
    }
    if (!data.kas_id) {
        errors.push('Kas harus dipilih');
    }
    if (!data.nama_penyetor) {
        errors.push('Nama Penyetor harus diisi');
    }
    if (!data.no_identitas) {
        errors.push('No Identitas harus diisi');
    }
    if (!data.alamat) {
        errors.push('Alamat harus diisi');
    }

    return errors;
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

            // Convert formatted number to raw number
            const jumlahInput = document.getElementById('jumlah');
            data.jumlah = getRawNumber(jumlahInput);

            // Validation
            const validationErrors = validateForm(data);
            if (validationErrors.length > 0) {
                alert('Error:\n' + validationErrors.join('\n'));
                return;
            }

            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Menyimpan...';

            try {
                const response = await fetch("{{ route('simpanan.setoran.store') }}", {
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
                // Reset button state
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

            // Convert formatted number to raw number
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
            if (!data.kas_id) {
                alert('Kas harus dipilih');
                return;
            }
            if (!data.nama_penyetor) {
                alert('Nama Penyetor harus diisi');
                return;
            }
            if (!data.no_identitas) {
                alert('No Identitas harus diisi');
                return;
            }
            if (!data.alamat) {
                alert('Alamat harus diisi');
                return;
            }

            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Mengupdate...';

            try {
                const updateUrl = `{{ url('simpanan/setoran') }}/${selectedRowData.id}`;
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
                // Reset button state
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
    }
});

// Import modal functions
function openImportModal() {
    document.getElementById('importModal').classList.remove('hidden');
}

function closeImportModal() {
    document.getElementById('importModal').classList.add('hidden');
}

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
    if (form) {
        form.submit();
    }
}

function clearFilters() {
    // Clear date inputs
    document.getElementById('tgl_dari').value = '';
    document.getElementById('tgl_sampai').value = '';
    document.getElementById('daterange-text').textContent = 'Pilih Tanggal';

    // Clear search input
    document.getElementById('search').value = '';

    // Submit form to reload with cleared filters
    const form = document.getElementById('filterForm');
    if (form) {
        form.submit();
    }
}

function cetakLaporan() {
    // Get current filter values
    const startDate = document.getElementById('tgl_dari').value;
    const endDate = document.getElementById('tgl_sampai').value;
    const search = document.getElementById('search').value;

    // Build URL with current filters
    let url = "{{ route('simpanan.setoran.export') }}?";
    const params = [];

    if (startDate) params.push(`start_date=${startDate}`);
    if (endDate) params.push(`end_date=${endDate}`);
    if (search) params.push(`search=${encodeURIComponent(search)}`);

    if (params.length > 0) {
        url += params.join('&');
    }

    // Open in new window
    window.open(url, '_blank');
}

// Date range picker initialization
document.addEventListener('DOMContentLoaded', function() {
    // Set moment.js locale to Indonesian
    if (typeof moment !== 'undefined') {
        moment.locale('id');
    }

    // Initialize date range picker if daterangepicker is available
    if (typeof $.fn.daterangepicker !== 'undefined') {
        $('#daterange-btn').daterangepicker({
            locale: {
                format: 'DD/MM/YYYY',
                separator: ' - ',
                applyLabel: 'Terapkan',
                cancelLabel: 'Batal',
                fromLabel: 'Dari',
                toLabel: 'Sampai',
                customRangeLabel: 'Custom',
                weekLabel: 'M',
                daysOfWeek: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
                monthNames: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                ],
                firstDay: 1
            },
            ranges: {
                'Hari Ini': [moment(), moment()],
                'Kemarin': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                '7 Hari Terakhir': [moment().subtract(6, 'days'), moment()],
                '30 Hari Terakhir': [moment().subtract(29, 'days'), moment()],
                'Bulan Ini': [moment().startOf('month'), moment().endOf('month')],
                'Bulan Lalu': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1,
                    'month').endOf('month')]
            },
            startDate: moment().subtract(29, 'days'),
            endDate: moment()
        }, function(start, end, label) {
            // Update hidden inputs
            document.getElementById('tgl_dari').value = start.format('YYYY-MM-DD');
            document.getElementById('tgl_sampai').value = end.format('YYYY-MM-DD');

            // Update display text
            document.getElementById('daterange-text').textContent = start.format('DD/MM/YYYY') + ' - ' +
                end.format('DD/MM/YYYY');
        });

        // Set initial values if they exist
        const startDate = document.getElementById('tgl_dari').value;
        const endDate = document.getElementById('tgl_sampai').value;

        if (startDate && endDate) {
            const start = moment(startDate);
            const end = moment(endDate);
            document.getElementById('daterange-text').textContent = start.format('DD/MM/YYYY') + ' - ' + end
                .format('DD/MM/YYYY');
        }
    } else {
        // Fallback: Simple date inputs if daterangepicker is not available
        console.log('DateRangePicker not available, using fallback');

        // Create simple date inputs
        const dateContainer = document.getElementById('daterange-btn');
        if (dateContainer) {
            dateContainer.innerHTML = `
                <input type="date" id="start_date_input" class="px-2 py-1 border border-gray-300 rounded text-sm" 
                       onchange="updateDateRange()" placeholder="Dari">
                <span class="mx-2">-</span>
                <input type="date" id="end_date_input" class="px-2 py-1 border border-gray-300 rounded text-sm" 
                       onchange="updateDateRange()" placeholder="Sampai">
            `;
        }
    }
});

function updateDateRange() {
    const startInput = document.getElementById('start_date_input');
    const endInput = document.getElementById('end_date_input');

    if (startInput && endInput) {
        document.getElementById('tgl_dari').value = startInput.value;
        document.getElementById('tgl_sampai').value = endInput.value;

        if (startInput.value && endInput.value) {
            document.getElementById('daterange-text').textContent =
                startInput.value + ' - ' + endInput.value;
        }
    }
}

// Function untuk cetak nota
function cetakNota(id) {
    if (!id) {
        alert('ID transaksi tidak valid');
        return;
    }

    const url = `{{ url('simpanan/setoran/nota') }}/${id}`;
    window.open(url, '_blank');
}
</script>