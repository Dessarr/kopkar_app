@extends('layouts.app')

@section('title', 'Billing Simpanan')
@section('sub-title', 'Manajemen Tagihan Simpanan Anggota')

@section('content')
<div class="px-1 justify-center flex flex-col">

    <!-- Alert Messages -->
    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
        <span class="block sm:inline">{{ session('error') }}</span>
    </div>
    @endif


    <!-- Filter Section -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-4">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-base font-semibold text-gray-800">Filter Billing Simpanan</h3>
        </div>

        <form method="GET" action="{{ route('billing.index') }}" id="filterForm">
            <!-- Filter Bar - Layout Rapi -->
            <div class="flex flex-wrap items-center justify-between gap-3 py-3 px-3 bg-gray-50 rounded-lg">
                <!-- Left Side: Periode dan Actions -->
                <div class="flex items-center gap-3">
                    <!-- Periode -->
                    <div class="flex items-center space-x-2">
                        <label class="text-sm font-medium text-gray-700">Periode:</label>
                        <select name="bulan" id="bulanSelect"
                            class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                            @foreach($bulanList as $key => $value)
                            <option value="{{ $key }}" {{ $bulan == $key ? 'selected' : '' }}>{{ $value }}</option>
                            @endforeach
                        </select>
                        <select name="tahun" id="tahunSelect"
                            class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                            @foreach($tahunList as $t)
                            <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Generate Ulang -->
                    <button type="button" onclick="generateUlang()"
                        class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors text-sm font-medium">
                        <i class="fas fa-sync-alt mr-1"></i>Generate Ulang
                    </button>

                    <!-- Clear Table -->
                    <button type="button" onclick="clearTable()"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm font-medium">
                        <i class="fas fa-trash mr-1"></i>Clear Table
                    </button>
                </div>

                <!-- Right Side: Search dan Process All -->
                <div class="flex items-center gap-3">
                    <!-- Search -->
                    <div class="flex items-center space-x-2">
                        <label class="text-sm font-medium text-gray-700">Cari:</label>
                        <input type="text" name="search" id="search" value="{{ request('search') }}"
                            placeholder="[Nama/ID/Kode Transaksi]"
                            class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm w-48"
                            onkeypress="if(event.key==='Enter'){doSearch();}">
                        <button type="button" onclick="doSearch()" id="searchBtn"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                            <i class="fas fa-search mr-1"></i>Cari
                        </button>
                    </div>

                    <!-- Process All -->
                    <button type="button" onclick="processAllToMain()"
                        class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors text-sm font-medium">
                        <i class="fas fa-arrow-right mr-1"></i>Process All
                    </button>

                    <!-- Hapus Filter -->
                    <button type="button" onclick="clearFilters()"
                        class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors text-sm font-medium">
                        <i class="fas fa-times mr-1"></i>Hapus Filter
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="flex justify-between items-center mb-2 p-4">
            <h2 class="text-lg font-semibold text-gray-800">Data Billing Simpanan</h2>

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
                <!-- Process Selected -->
                <button type="button" onclick="processSelectedToMain()" id="processSelectedBtn"
                    class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors text-sm font-medium disabled:bg-gray-400 disabled:cursor-not-allowed"
                    disabled>
                    <i class="fas fa-arrow-right mr-1"></i>Process Selected
                </button>
                <!-- Cetak Laporan -->
                <button type="button" onclick="cetakLaporan()"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                    <i class="fas fa-print mr-1"></i>Cetak Laporan
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
                        <th class="py-3 border px-4">Nama</th>
                        <th class="py-3 border px-4">Kas Simpanan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dataBilling as $billing)
                    <tr class="text-sm align-middle hover:bg-gray-50 cursor-pointer row-selectable"
                        data-id="{{ $billing->id }}" data-id-anggota="{{ $billing->id_anggota }}"
                        data-nama="{{ $billing->nama }}" data-no-ktp="{{ $billing->anggota->no_ktp ?? '' }}"
                        data-foto="{{ $billing->anggota->file_pic ?? '' }}"
                        data-simpanan-wajib="{{ $billing->simpanan_wajib }}"
                        data-simpanan-khusus-1="{{ $billing->simpanan_khusus_1 }}"
                        data-simpanan-sukarela="{{ $billing->simpanan_sukarela }}"
                        data-simpanan-khusus-2="{{ $billing->simpanan_khusus_2 }}"
                        data-tab-perumahan="{{ $billing->tab_perumahan }}"
                        data-simpanan-pokok="{{ $billing->simpanan_pokok }}"
                        data-total-tagihan="{{ $billing->total_tagihan }}"
                        data-kode-transaksi="{{ $billing->kode_transaksi }}"
                        data-bulan-tahun="{{ $billing->bulan_tahun }}"
                        data-tgl-transaksi="{{ $billing->created_at ? $billing->created_at->format('Y-m-d\TH:i') : '' }}">
                        <td class="py-3 border px-4">
                            {{ ($dataBilling->currentPage() - 1) * $dataBilling->perPage() + $loop->iteration }}
                        </td>
                        <td class="py-3 border px-4">
                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-medium">
                                {{ $billing->kode_transaksi }}
                            </span>
                        </td>
                        <td class="py-3 border px-4">{{ $billing->bulan_tahun }}</td>
                        <td class="py-3 border px-4 text-left">
                            <!-- Subtable untuk Nama -->
                            <table class="w-full" style="border: none;">
                                <tr>
                                    <td class="text-left py-1" style="border: none;">{{ $billing->nama }}</td>
                                </tr>
                                <tr>
                                    <td class="text-left py-1 text-xs text-gray-500" style="border: none;">
                                        {{ $billing->anggota->no_ktp ?? '-' }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td class="py-3 border px-4">
                            <!-- Subtable untuk Kas Simpanan -->
                            <table class="w-full text-xs" style="border: none;">
                                <tr>
                                    <td class="text-left py-1" style="border: none;">Simpanan Wajib</td>
                                    <td class="text-right py-1 font-semibold text-green-600" style="border: none;">
                                        Rp{{ number_format($billing->simpanan_wajib, 0, ',', '.') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-left py-1" style="border: none;">Simpanan Pokok</td>
                                    <td class="text-right py-1 font-semibold text-green-600" style="border: none;">
                                        Rp{{ number_format($billing->simpanan_pokok, 0, ',', '.') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-left py-1" style="border: none;">Simpanan Sukarela</td>
                                    <td class="text-right py-1 font-semibold text-green-600" style="border: none;">
                                        Rp{{ number_format($billing->simpanan_sukarela, 0, ',', '.') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-left py-1" style="border: none;">Simpanan Khusus I</td>
                                    <td class="text-right py-1 font-semibold text-green-600" style="border: none;">
                                        Rp{{ number_format($billing->simpanan_khusus_1, 0, ',', '.') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-left py-1" style="border: none;">Simpanan Khusus II</td>
                                    <td class="text-right py-1 font-semibold text-green-600" style="border: none;">
                                        Rp{{ number_format($billing->simpanan_khusus_2, 0, ',', '.') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-left py-1" style="border: none;">Tab Perumahan</td>
                                    <td class="text-right py-1 font-semibold text-green-600" style="border: none;">
                                        Rp{{ number_format($billing->tab_perumahan, 0, ',', '.') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-left py-1 font-bold" style="border: none;">Total Tagihan</td>
                                    <td class="text-right py-1 font-bold text-blue-600" style="border: none;">
                                        Rp{{ number_format($billing->total_tagihan, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-2"></i>
                            <p>Tidak ada data billing simpanan</p>
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
                @for ($i = 1; $i <= $dataBilling->lastPage(); $i++)
                    @if ($i == 1 || $i == $dataBilling->lastPage() || ($i >= $dataBilling->currentPage() - 1 && $i <=
                        $dataBilling->currentPage() + 1))
                        <a href="{{ $dataBilling->url($i) }}">
                            <div
                                class="rounded-md px-2 py-0.5 text-sm border border-gray-300 {{ $dataBilling->currentPage() == $i ? 'bg-gray-100 font-bold' : '' }}">
                                {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                            </div>
                        </a>
                        @elseif ($i == 2 || $i == $dataBilling->lastPage() - 1)
                        <div class="rounded-md px-2 py-0.5 text-sm">...</div>
                        @endif
                        @endfor
            </div>
        </div>

        <div class="absolute right-4 top-1/2 -translate-y-1/2 whitespace-nowrap text-sm text-gray-400">
            Displaying {{ $dataBilling->firstItem() }} to {{ $dataBilling->lastItem() }} of {{ $dataBilling->total() }}
            items
        </div>
    </div>
</div>

<!-- Add Modal -->
<div id="addModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 overflow-y-scroll">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl">
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <h3 class="text-xl font-semibold text-gray-800">Tambah Billing Simpanan</h3>
                <button onclick="closeModal('addModal')" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form id="addForm" class="p-6">
                <!-- Header Info -->
                <div class="mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                    <div class="flex items-center">
                        <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                        <p class="text-sm text-blue-700">
                            <strong>Petunjuk:</strong> Pilih anggota terlebih dahulu untuk mengaktifkan input simpanan.
                            Nilai simpanan akan otomatis terisi dari data anggota dan dapat diubah sesuai kebutuhan.
                        </p>
                    </div>
                </div>

                <!-- Informasi Anggota Section -->
                <div class="mb-8">
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h4 class="text-lg font-medium text-gray-800 mb-6 flex items-center">
                            <i class="fas fa-user mr-2 text-blue-500"></i>
                            Informasi Anggota
                        </h4>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Transaksi</label>
                                <input type="datetime-local" name="tgl_transaksi" id="tgl_transaksi" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Anggota <span
                                        class="text-red-500">*</span></label>
                                <input type="text" name="nama_anggota" id="nama_anggota" required
                                    placeholder="Klik untuk memilih anggota..."
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors cursor-pointer"
                                    readonly>
                                <!-- Hidden inputs -->
                                <input type="hidden" name="id_anggota" id="id_anggota">
                                <input type="hidden" name="no_ktp" id="no_ktp">

                                <!-- Dropdown anggota -->
                                <div id="anggotaDropdown"
                                    class="mt-2 border border-gray-300 rounded-lg bg-white shadow-lg max-h-60 overflow-y-auto hidden z-10">
                                    @foreach($anggota as $ang)
                                    <div class="p-3 hover:bg-blue-50 cursor-pointer anggota-option border-b border-gray-100 last:border-b-0"
                                        data-id="{{ $ang->id }}" data-no-ktp="{{ $ang->no_ktp }}"
                                        data-nama="{{ $ang->nama }}"
                                        data-simpanan-wajib="{{ $ang->simpanan_wajib ?? 0 }}"
                                        data-simpanan-sukarela="{{ $ang->simpanan_sukarela ?? 0 }}"
                                        data-simpanan-khusus-2="{{ $ang->simpanan_khusus_2 ?? 0 }}">
                                        <div class="flex items-center space-x-3">
                                            <div
                                                class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                                <i class="fas fa-user text-blue-600"></i>
                                            </div>
                                            <div class="flex-1">
                                                <div class="font-medium text-gray-900">{{ $ang->nama }}</div>
                                                <div class="text-sm text-gray-500">
                                                    ID: AG{{ str_pad($ang->id, 4, '0', STR_PAD_LEFT) }} |
                                                    KTP: {{ $ang->no_ktp }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detail Simpanan Section -->
                <div class="mb-8">
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h4 class="text-lg font-medium text-gray-800 mb-6 flex items-center">
                            <i class="fas fa-calculator mr-2 text-green-500"></i>
                            Detail Simpanan
                        </h4>

                        <!-- Grid 2x3 untuk input simpanan -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Simpanan Wajib</label>
                                <input type="text" name="simpanan_wajib" id="simpanan_wajib" value="Rp 0" disabled
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Simpanan Pokok</label>
                                <input type="text" name="simpanan_pokok" id="simpanan_pokok" value="Rp 0" disabled
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Simpanan Sukarela</label>
                                <input type="text" name="simpanan_sukarela" id="simpanan_sukarela" value="Rp 0" disabled
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Simpanan Khusus I</label>
                                <input type="text" name="simpanan_khusus_1" id="simpanan_khusus_1" value="Rp 0" disabled
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Simpanan Khusus II</label>
                                <input type="text" name="simpanan_khusus_2" id="simpanan_khusus_2" value="Rp 0" disabled
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tab Perumahan</label>
                                <input type="text" name="tab_perumahan" id="tab_perumahan" value="Rp 0" disabled
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            </div>
                        </div>

                        <!-- Total Tagihan (Full Width) -->
                        <div class="border-t pt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Total Tagihan</label>
                            <input type="text" name="total_tagihan" id="total_tagihan" value="Rp 0" readonly
                                class="w-full px-4 py-3 border border-blue-300 rounded-lg bg-blue-50 font-bold text-blue-700 text-xl text-center">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 mt-8 pt-6 border-t border-gray-200">
                    <button type="button" onclick="closeModal('addModal')"
                        class="px-6 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        <i class="fas fa-times mr-2"></i>Batal
                    </button>
                    <button type="submit"
                        class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        <i class="fas fa-save mr-2"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 overflow-y-scroll">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl">
            <div class="flex items-center justify-between p-4 border-b">
                <h3 class="text-lg font-semibold">Edit Billing Simpanan</h3>
                <button onclick="closeModal('editModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="editForm" class="p-6">
                <!-- Informasi Anggota Section -->
                <div class="mb-8">
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h4 class="text-lg font-medium text-gray-800 mb-6 flex items-center">
                            <i class="fas fa-user mr-2 text-blue-500"></i>
                            Informasi Anggota
                        </h4>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Transaksi</label>
                                <input type="datetime-local" name="tgl_transaksi" id="edit_tgl_transaksi" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Anggota</label>
                                <input type="text" name="nama_anggota" id="edit_nama_anggota" required
                                    placeholder="Nama anggota"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                    readonly>
                                <input type="hidden" name="id_anggota" id="edit_id_anggota">
                                <input type="hidden" name="no_ktp" id="edit_no_ktp">
                                <input type="hidden" name="billing_id" id="edit_billing_id">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detail Simpanan Section -->
                <div class="mb-8">
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h4 class="text-lg font-medium text-gray-800 mb-6 flex items-center">
                            <i class="fas fa-calculator mr-2 text-green-500"></i>
                            Detail Simpanan
                        </h4>

                        <!-- Grid 2x3 untuk input simpanan -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Simpanan Wajib</label>
                                <input type="text" name="simpanan_wajib" id="edit_simpanan_wajib" value="Rp 0"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Simpanan Pokok</label>
                                <input type="text" name="simpanan_pokok" id="edit_simpanan_pokok" value="Rp 0"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Simpanan Sukarela</label>
                                <input type="text" name="simpanan_sukarela" id="edit_simpanan_sukarela" value="Rp 0"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Simpanan Khusus I</label>
                                <input type="text" name="simpanan_khusus_1" id="edit_simpanan_khusus_1" value="Rp 0"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Simpanan Khusus II</label>
                                <input type="text" name="simpanan_khusus_2" id="edit_simpanan_khusus_2" value="Rp 0"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tab Perumahan</label>
                                <input type="text" name="tab_perumahan" id="edit_tab_perumahan" value="Rp 0"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            </div>
                        </div>

                        <!-- Total Tagihan (Full Width) -->
                        <div class="border-t pt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Total Tagihan</label>
                            <input type="text" name="total_tagihan" id="edit_total_tagihan" value="Rp 0" readonly
                                class="w-full px-4 py-3 border border-blue-300 rounded-lg bg-blue-50 font-bold text-blue-700 text-xl text-center">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 mt-8 pt-6 border-t border-gray-200">
                    <button type="button" onclick="closeModal('editModal')"
                        class="px-6 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        <i class="fas fa-times mr-2"></i>Batal
                    </button>
                    <button type="submit"
                        class="px-6 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors">
                        <i class="fas fa-edit mr-2"></i>Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Global variable untuk menyimpan data row yang dipilih
let selectedRowData = null;

// Simple Filter System
$(document).ready(function() {
    // Add click event listener for row selection
    $(document).on('click', '.row-selectable', function() {
        selectRow(this, $(this).data('id'));
    });
});

// Main Search Function
function doSearch() {
    const form = document.getElementById("filterForm");
    const formData = new FormData(form);

    // Build query parameters
    const params = new URLSearchParams();

    // Search
    const search = formData.get('search');
    if (search && search.trim() !== '') {
        params.append('search', search);
    }

    // Bulan dan Tahun
    const bulan = formData.get('bulan');
    const tahun = formData.get('tahun');
    if (bulan) params.append('bulan', bulan);
    if (tahun) params.append('tahun', tahun);

    // Redirect with parameters
    window.location.href = "{{ route('billing.index') }}?" + params.toString();
}

// Clear all filters
function clearFilters() {
    // Reset search
    $('#search').val('');

    // Reload page
    window.location.href = "{{ route('billing.index') }}";
}

// Generate ulang data billing untuk periode yang dipilih
function generateUlang() {
    const bulan = document.getElementById('bulanSelect').value;
    const tahun = document.getElementById('tahunSelect').value;

    if (!bulan || !tahun) {
        alert('Pilih periode terlebih dahulu!');
        return;
    }

    if (confirm('Apakah Anda yakin ingin menggenerate ulang data billing untuk periode ' +
            document.getElementById('bulanSelect').selectedOptions[0].text + ' ' + tahun + '?')) {

        // Show loading
        const btn = event.target;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Generating...';
        btn.disabled = true;

        // Send AJAX request
        fetch('{{ route("billing.generate-ulang") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    bulan: bulan,
                    tahun: tahun
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    // Reload page to show new data
                    window.location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat generate data');
            })
            .finally(() => {
                // Restore button
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
    }
}

// Clear table - hapus semua data billing
function clearTable() {
    if (confirm(
            'PERINGATAN: Apakah Anda yakin ingin menghapus SEMUA data billing? Tindakan ini tidak dapat dibatalkan!')) {

        // Show loading
        const btn = event.target;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Deleting...';
        btn.disabled = true;

        // Create form for POST request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("billing.clear-table") }}';

        // Add CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);

        // Submit form
        document.body.appendChild(form);
        form.submit();
    }
}

// Cetak Laporan PDF
function cetakLaporan() {
    const form = document.getElementById("filterForm");
    const formData = new FormData(form);

    const params = new URLSearchParams();

    const search = formData.get('search');
    if (search && search.trim() !== '') {
        params.append('search', search);
    }

    const bulan = formData.get('bulan');
    const tahun = formData.get('tahun');
    if (bulan) params.append('bulan', bulan);
    if (tahun) params.append('tahun', tahun);

    window.open("{{ route('billing.export.pdf') }}?" + params.toString(), '_blank');
}

// Process All to Main
function processAllToMain() {
    const bulan = document.getElementById('bulanSelect').value;
    const tahun = document.getElementById('tahunSelect').value;

    if (!bulan || !tahun) {
        alert('Pilih periode terlebih dahulu!');
        return;
    }

    if (confirm('Apakah Anda yakin ingin memproses semua data billing simpanan ke Billing Utama untuk periode ' +
            document.getElementById('bulanSelect').selectedOptions[0].text + ' ' + tahun + '?')) {

        // Show loading
        const btn = event.target;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Processing...';
        btn.disabled = true;

        // Create form for POST request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("billing.simpanan.process_all") }}';

        // Add CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);

        // Add bulan and tahun
        const bulanField = document.createElement('input');
        bulanField.type = 'hidden';
        bulanField.name = 'bulan';
        bulanField.value = bulan;
        form.appendChild(bulanField);

        const tahunField = document.createElement('input');
        tahunField.type = 'hidden';
        tahunField.name = 'tahun';
        tahunField.value = tahun;
        form.appendChild(tahunField);

        // Submit form
        document.body.appendChild(form);
        form.submit();
    }
}

// Process Selected to Main
function processSelectedToMain() {
    if (!selectedRowData || !selectedRowData.id) {
        alert('Pilih data terlebih dahulu!');
        return;
    }

    if (confirm('Apakah Anda yakin ingin memproses data billing simpanan "' + selectedRowData.nama +
            '" ke Billing Utama?')) {

        // Show loading
        const btn = document.getElementById('processSelectedBtn');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Processing...';
        btn.disabled = true;

        // Create form for POST request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("billing.simpanan.process_selected") }}';

        // Add CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);

        // Add billing ID
        const billingIdField = document.createElement('input');
        billingIdField.type = 'hidden';
        billingIdField.name = 'billing_id';
        billingIdField.value = selectedRowData.id;
        form.appendChild(billingIdField);

        // Submit form
        document.body.appendChild(form);
        form.submit();
    }
}

// Modal functions
function openModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');

    // Set default values untuk form add
    if (modalId === 'addModal') {
        // Reset form
        document.getElementById('addForm').reset();

        // Disable simpanan inputs initially
        disableSimpananInputs();

        // Set default values with currency formatting
        $('#simpanan_wajib').val('0');
        $('#simpanan_pokok').val('0');
        $('#simpanan_sukarela').val('0');
        $('#simpanan_khusus_1').val('0');
        $('#simpanan_khusus_2').val('0');
        $('#tab_perumahan').val('0');
        $('#total_tagihan').val('0');

        // Format all inputs after a short delay to ensure they're rendered
        setTimeout(function() {
            $('#simpanan_wajib, #simpanan_pokok, #simpanan_sukarela, #simpanan_khusus_1, #simpanan_khusus_2, #tab_perumahan, #total_tagihan')
                .each(function() {
                    const value = $(this).val();
                    if (value && value !== '0' && !value.includes('Rp')) {
                        $(this).val(formatCurrency(value));
                    }
                });
        }, 100);

        // Set default tanggal transaksi
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');

        const datetimeString = `${year}-${month}-${day}T${hours}:${minutes}`;
        document.getElementById('tgl_transaksi').value = datetimeString;
    }

    // Disable Process Selected button when opening modal
    const processSelectedBtn = document.getElementById('processSelectedBtn');
    if (processSelectedBtn) {
        processSelectedBtn.disabled = true;
        processSelectedBtn.classList.add('disabled:bg-gray-400', 'disabled:cursor-not-allowed');
    }
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

// Function untuk select row (click to edit)
function selectRow(row, id) {
    // Remove highlight dari semua row
    document.querySelectorAll('tbody tr').forEach(r => {
        r.classList.remove('bg-yellow-100', 'border-yellow-300');
        r.classList.add('hover:bg-gray-50');
    });

    // Add highlight ke row yang dipilih
    row.classList.remove('hover:bg-gray-50');
    row.classList.add('bg-yellow-100', 'border-yellow-300');

    // Simpan data row yang dipilih
    selectedRowData = {
        id: row.dataset.id,
        id_anggota: row.dataset.idAnggota,
        nama: row.dataset.nama,
        no_ktp: row.dataset.noKtp,
        foto: row.dataset.foto,
        simpanan_wajib: row.dataset.simpananWajib,
        simpanan_khusus_1: row.getAttribute('data-simpanan-khusus-1'),
        simpanan_sukarela: row.dataset.simpananSukarela,
        simpanan_khusus_2: row.getAttribute('data-simpanan-khusus-2'),
        tab_perumahan: row.dataset.tabPerumahan,
        simpanan_pokok: row.dataset.simpananPokok,
        total_tagihan: row.dataset.totalTagihan,
        kode_transaksi: row.dataset.kodeTransaksi,
        bulan_tahun: row.dataset.bulanTahun,
        tgl_transaksi: row.dataset.tglTransaksi
    };

    // Debug: Log individual data attributes
    console.log('=== DEBUGGING ROW SELECTION ===');
    console.log('Row element:', row);
    console.log('Row dataset:', row.dataset);
    console.log('Raw dataset values:', {
        'simpanan-wajib': row.dataset.simpananWajib,
        'simpanan-khusus-1': row.dataset.simpananKhusus1,
        'simpanan-sukarela': row.dataset.simpananSukarela,
        'simpanan-khusus-2': row.dataset.simpananKhusus2,
        'tab-perumahan': row.dataset.tabPerumahan,
        'simpanan-pokok': row.dataset.simpananPokok
    });
    console.log('Direct attribute access:', {
        'simpanan-wajib': row.getAttribute('data-simpanan-wajib'),
        'simpanan-khusus-1': row.getAttribute('data-simpanan-khusus-1'),
        'simpanan-sukarela': row.getAttribute('data-simpanan-sukarela'),
        'simpanan-khusus-2': row.getAttribute('data-simpanan-khusus-2'),
        'tab-perumahan': row.getAttribute('data-tab-perumahan'),
        'simpanan-pokok': row.getAttribute('data-simpanan-pokok')
    });

    // Debug: Log data yang dipilih
    console.log('Selected Row Data:', selectedRowData);

    // Enable Process Selected button
    const processSelectedBtn = document.getElementById('processSelectedBtn');
    if (processSelectedBtn) {
        processSelectedBtn.disabled = false;
        processSelectedBtn.classList.remove('disabled:bg-gray-400', 'disabled:cursor-not-allowed');
    }
}

// CRUD functions
function editData() {
    if (!selectedRowData) {
        alert('Pilih data yang akan diedit terlebih dahulu!');
        return;
    }

    // Buka modal edit dengan data terisi
    openModal('editModal');

    // Populate form dengan data yang dipilih
    document.getElementById('edit_billing_id').value = selectedRowData.id;
    document.getElementById('edit_id_anggota').value = selectedRowData.id_anggota;
    document.getElementById('edit_nama_anggota').value = selectedRowData.nama;
    document.getElementById('edit_no_ktp').value = selectedRowData.no_ktp || '';

    // Format tanggal untuk input datetime-local
    if (selectedRowData.tgl_transaksi) {
        document.getElementById('edit_tgl_transaksi').value = selectedRowData.tgl_transaksi;
    } else {
        // Fallback ke tanggal sekarang jika tidak ada data
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const datetimeString = `${year}-${month}-${day}T${hours}:${minutes}`;
        document.getElementById('edit_tgl_transaksi').value = datetimeString;
    }

    // Debug: Log simpanan values before formatting
    console.log('=== DEBUGGING EDIT DATA ===');
    console.log('selectedRowData object:', selectedRowData);
    console.log('Simpanan values before formatting:', {
        simpanan_wajib: selectedRowData.simpanan_wajib,
        simpanan_khusus_1: selectedRowData.simpanan_khusus_1,
        simpanan_sukarela: selectedRowData.simpanan_sukarela,
        simpanan_khusus_2: selectedRowData.simpanan_khusus_2,
        tab_perumahan: selectedRowData.tab_perumahan,
        simpanan_pokok: selectedRowData.simpanan_pokok
    });
    console.log('Type of simpanan_khusus_2:', typeof selectedRowData.simpanan_khusus_2);
    console.log('Value of simpanan_khusus_2:', selectedRowData.simpanan_khusus_2);

    // Populate simpanan values with currency formatting
    document.getElementById('edit_simpanan_wajib').value = formatCurrency(selectedRowData.simpanan_wajib || 0);
    document.getElementById('edit_simpanan_khusus_1').value = formatCurrency(selectedRowData.simpanan_khusus_1 || 0);
    document.getElementById('edit_simpanan_sukarela').value = formatCurrency(selectedRowData.simpanan_sukarela || 0);
    document.getElementById('edit_simpanan_khusus_2').value = formatCurrency(selectedRowData.simpanan_khusus_2 || 0);
    document.getElementById('edit_tab_perumahan').value = formatCurrency(selectedRowData.tab_perumahan || 0);
    document.getElementById('edit_simpanan_pokok').value = formatCurrency(selectedRowData.simpanan_pokok || 0);

    // Debug: Log form values after population
    console.log('=== FORM VALUES AFTER POPULATION ===');
    console.log('edit_simpanan_wajib value:', document.getElementById('edit_simpanan_wajib').value);
    console.log('edit_simpanan_khusus_1 value:', document.getElementById('edit_simpanan_khusus_1').value);
    console.log('edit_simpanan_sukarela value:', document.getElementById('edit_simpanan_sukarela').value);
    console.log('edit_simpanan_khusus_2 value:', document.getElementById('edit_simpanan_khusus_2').value);
    console.log('edit_tab_perumahan value:', document.getElementById('edit_tab_perumahan').value);
    console.log('edit_simpanan_pokok value:', document.getElementById('edit_simpanan_pokok').value);

    // Calculate and set total
    calculateEditTotalTagihan();
}

function deleteData() {
    if (!selectedRowData) {
        alert('Pilih data yang akan dihapus terlebih dahulu!');
        return;
    }

    if (confirm(`Apakah Anda yakin ingin menghapus billing untuk anggota: ${selectedRowData.nama}?`)) {
        // Kirim request delete
        const deleteUrl = `{{ url('billing') }}/${selectedRowData.id}`;
        fetch(deleteUrl, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
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

// Form submission dengan validasi lengkap
document.getElementById('addForm').addEventListener('submit', function(e) {
    e.preventDefault();

    // Validasi form
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);

    // Validasi required fields
    if (!data.id_anggota || data.id_anggota <= 0) {
        alert('Anggota harus dipilih');
        return;
    }

    if (!data.nama_anggota) {
        alert('Nama anggota harus dipilih');
        return;
    }

    if (!data.tgl_transaksi) {
        alert('Tanggal transaksi harus diisi');
        return;
    }

    // Hitung total tagihan menggunakan fungsi yang sudah ada
    calculateTotalTagihan();
    const totalTagihan = parseCurrency($('#total_tagihan').val());

    // Prepare data for submission
    const submitData = {
        id_anggota: data.id_anggota,
        nama: data.nama_anggota,
        no_ktp: data.no_ktp,
        tgl_transaksi: data.tgl_transaksi,
        simpanan_wajib: parseCurrency(data.simpanan_wajib || 0),
        simpanan_khusus_1: parseCurrency(data.simpanan_khusus_1 || 0),
        simpanan_sukarela: parseCurrency(data.simpanan_sukarela || 0),
        simpanan_khusus_2: parseCurrency(data.simpanan_khusus_2 || 0),
        tab_perumahan: parseCurrency(data.tab_perumahan || 0),
        simpanan_pokok: parseCurrency(data.simpanan_pokok || 0),
        total_tagihan: totalTagihan
    };

    // Submit data
    fetch("{{ route('billing.store') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(submitData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Data berhasil disimpan');
                closeModal('addModal');
                location.reload();
            } else {
                alert('Gagal menyimpan data: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menyimpan data');
        });
});

// Edit form submission
document.getElementById('editForm').addEventListener('submit', function(e) {
    e.preventDefault();

    if (!selectedRowData) {
        alert('Tidak ada data yang dipilih untuk diedit');
        return;
    }

    const formData = new FormData(this);
    const data = Object.fromEntries(formData);

    // Validasi required fields
    if (!data.id_anggota || data.id_anggota <= 0) {
        alert('Data anggota tidak valid');
        return;
    }

    if (!data.nama_anggota) {
        alert('Nama anggota harus diisi');
        return;
    }

    if (!data.tgl_transaksi) {
        alert('Tanggal transaksi harus diisi');
        return;
    }

    // Hitung total tagihan menggunakan fungsi yang sudah ada
    calculateEditTotalTagihan();
    const totalTagihan = parseCurrency($('#edit_total_tagihan').val());

    // Prepare data for submission
    const submitData = {
        id_anggota: data.id_anggota,
        nama: data.nama_anggota,
        no_ktp: data.no_ktp,
        tgl_transaksi: data.tgl_transaksi,
        simpanan_wajib: parseCurrency(data.simpanan_wajib || 0),
        simpanan_khusus_1: parseCurrency(data.simpanan_khusus_1 || 0),
        simpanan_sukarela: parseCurrency(data.simpanan_sukarela || 0),
        simpanan_khusus_2: parseCurrency(data.simpanan_khusus_2 || 0),
        tab_perumahan: parseCurrency(data.tab_perumahan || 0),
        simpanan_pokok: parseCurrency(data.simpanan_pokok || 0),
        total_tagihan: totalTagihan
    };

    const updateUrl = `{{ url('billing') }}/${selectedRowData.id}`;
    fetch(updateUrl, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(submitData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Data berhasil diupdate');
                closeModal('editModal');
                location.reload();
            } else {
                alert('Gagal mengupdate data: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat mengupdate data');
        });
});

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl+Enter atau Cmd+Enter: Trigger search
    if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
        e.preventDefault();
        doSearch();
    }

    // Escape: Clear filters
    if (e.key === 'Escape') {
        e.preventDefault();
        clearFilters();
    }
});

// Auto-focus pada search jika URL parameter ada
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('search')) {
        document.getElementById('search').focus();
    }
});

// Handle Anggota Dropdown untuk Billing
function handleAnggotaDropdownBilling() {
    // Show dropdown when clicking nama anggota input
    $('#nama_anggota').on('click', function() {
        $('#anggotaDropdown').toggleClass('hidden');
    });

    // Hide dropdown when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#nama_anggota, #anggotaDropdown').length) {
            $('#anggotaDropdown').addClass('hidden');
        }
    });

    // Handle anggota selection
    $('.anggota-option').on('click', function() {
        const id = $(this).data('id');
        const noKtp = $(this).data('no-ktp');
        const nama = $(this).data('nama');
        const simpananWajib = $(this).data('simpanan-wajib') || 0;
        const simpananSukarela = $(this).data('simpanan-sukarela') || 0;
        const simpananKhusus2 = $(this).data('simpanan-khusus-2') || 0;

        // Set values
        $('#nama_anggota').val(nama);
        $('#id_anggota').val(id);
        $('#no_ktp').val(noKtp);

        // Enable all simpanan inputs
        enableSimpananInputs();

        // Auto-fill simpanan values with currency formatting
        $('#simpanan_wajib').val(formatCurrency(simpananWajib));
        $('#simpanan_sukarela').val(formatCurrency(simpananSukarela));
        $('#simpanan_khusus_2').val(formatCurrency(simpananKhusus2));

        // Set default values for other fields with currency formatting
        $('#simpanan_pokok').val('0');
        $('#simpanan_khusus_1').val('0');
        $('#tab_perumahan').val('0');

        // Calculate total
        calculateTotalTagihan();

        // Format all inputs after selection
        setTimeout(function() {
            $('#simpanan_wajib, #simpanan_pokok, #simpanan_sukarela, #simpanan_khusus_1, #simpanan_khusus_2, #tab_perumahan, #total_tagihan')
                .each(function() {
                    const value = $(this).val();
                    if (value && value !== '0' && !value.includes('Rp')) {
                        $(this).val(formatCurrency(value));
                    }
                });
        }, 100);

        // Hide dropdown
        $('#anggotaDropdown').addClass('hidden');
    });
}

// Enable Simpanan Inputs
function enableSimpananInputs() {
    const inputs = ['#simpanan_wajib', '#simpanan_pokok', '#simpanan_sukarela', '#simpanan_khusus_1',
        '#simpanan_khusus_2', '#tab_perumahan'
    ];

    inputs.forEach(selector => {
        $(selector).prop('disabled', false)
            .removeClass('bg-gray-100 text-gray-500')
            .addClass('bg-white text-gray-900');
    });
}

// Disable Simpanan Inputs
function disableSimpananInputs() {
    const inputs = ['#simpanan_wajib', '#simpanan_pokok', '#simpanan_sukarela', '#simpanan_khusus_1',
        '#simpanan_khusus_2', '#tab_perumahan'
    ];

    inputs.forEach(selector => {
        $(selector).prop('disabled', true)
            .removeClass('bg-white text-gray-900')
            .addClass('bg-gray-100 text-gray-500');
    });
}

// Format number to currency (Rp 1.000.000)
function formatCurrency(value) {
    if (!value && value !== 0) return 'Rp 0';
    return 'Rp ' + parseInt(value).toLocaleString('id-ID');
}

// Parse currency to number
function parseCurrency(value) {
    if (!value) return 0;
    return parseInt(value.toString().replace(/[^\d]/g, '')) || 0;
}

// Calculate Total Tagihan
function calculateTotalTagihan() {
    const simpananWajib = parseCurrency($('#simpanan_wajib').val());
    const simpananPokok = parseCurrency($('#simpanan_pokok').val());
    const simpananSukarela = parseCurrency($('#simpanan_sukarela').val());
    const simpananKhusus1 = parseCurrency($('#simpanan_khusus_1').val());
    const simpananKhusus2 = parseCurrency($('#simpanan_khusus_2').val());
    const tabPerumahan = parseCurrency($('#tab_perumahan').val());

    const total = simpananWajib + simpananPokok + simpananSukarela + simpananKhusus1 + simpananKhusus2 + tabPerumahan;

    $('#total_tagihan').val(formatCurrency(total));
}

// Calculate Total Tagihan for Edit Form
function calculateEditTotalTagihan() {
    const simpananWajib = parseCurrency($('#edit_simpanan_wajib').val());
    const simpananPokok = parseCurrency($('#edit_simpanan_pokok').val());
    const simpananSukarela = parseCurrency($('#edit_simpanan_sukarela').val());
    const simpananKhusus1 = parseCurrency($('#edit_simpanan_khusus_1').val());
    const simpananKhusus2 = parseCurrency($('#edit_simpanan_khusus_2').val());
    const tabPerumahan = parseCurrency($('#edit_tab_perumahan').val());

    const total = simpananWajib + simpananPokok + simpananSukarela + simpananKhusus1 + simpananKhusus2 + tabPerumahan;

    $('#edit_total_tagihan').val(formatCurrency(total));
}

// Format input on focus out
function formatInputOnBlur(inputId) {
    $(inputId).on('blur', function() {
        const value = $(this).val();
        if (value && value !== '0' && !value.includes('Rp')) {
            $(this).val(formatCurrency(value));
        }
    });
}

// Format input on focus (remove formatting for editing)
function formatInputOnFocus(inputId) {
    $(inputId).on('focus', function() {
        const value = $(this).val();
        if (value && value.includes('Rp')) {
            $(this).val(parseCurrency(value));
        }
    });
}

// Initialize billing-specific functions
$(document).ready(function() {
    // Initialize anggota dropdown
    handleAnggotaDropdownBilling();

    // Add event listeners for simpanan inputs to auto-calculate total
    $('#simpanan_wajib, #simpanan_pokok, #simpanan_sukarela, #simpanan_khusus_1, #simpanan_khusus_2, #tab_perumahan')
        .on('input', function() {
            calculateTotalTagihan();
        });

    // Add event listeners for edit form simpanan inputs to auto-calculate total
    $('#edit_simpanan_wajib, #edit_simpanan_pokok, #edit_simpanan_sukarela, #edit_simpanan_khusus_1, #edit_simpanan_khusus_2, #edit_tab_perumahan')
        .on('input', function() {
            calculateEditTotalTagihan();
        });

    // Add currency formatting for all simpanan inputs using event delegation
    $(document).on('focus',
        '#simpanan_wajib, #simpanan_pokok, #simpanan_sukarela, #simpanan_khusus_1, #simpanan_khusus_2, #tab_perumahan, #edit_simpanan_wajib, #edit_simpanan_pokok, #edit_simpanan_sukarela, #edit_simpanan_khusus_1, #edit_simpanan_khusus_2, #edit_tab_perumahan',
        function() {
            const value = $(this).val();
            if (value && value.includes('Rp')) {
                $(this).val(parseCurrency(value));
            }
        });

    $(document).on('blur',
        '#simpanan_wajib, #simpanan_pokok, #simpanan_sukarela, #simpanan_khusus_1, #simpanan_khusus_2, #tab_perumahan, #edit_simpanan_wajib, #edit_simpanan_pokok, #edit_simpanan_sukarela, #edit_simpanan_khusus_1, #edit_simpanan_khusus_2, #edit_tab_perumahan',
        function() {
            const value = $(this).val();
            if (value && value !== '0' && !value.includes('Rp')) {
                $(this).val(formatCurrency(value));
            }
        });

    // Add input mask for currency inputs (only allow numbers)
    $(document).on('keypress',
        '#simpanan_wajib, #simpanan_pokok, #simpanan_sukarela, #simpanan_khusus_1, #simpanan_khusus_2, #tab_perumahan, #edit_simpanan_wajib, #edit_simpanan_pokok, #edit_simpanan_sukarela, #edit_simpanan_khusus_1, #edit_simpanan_khusus_2, #edit_tab_perumahan',
        function(e) {
            // Allow only numbers, backspace, delete, tab, escape, enter
            if ([46, 8, 9, 27, 13, 110].indexOf(e.keyCode) !== -1 ||
                // Allow Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
                (e.keyCode === 65 && e.ctrlKey === true) ||
                (e.keyCode === 67 && e.ctrlKey === true) ||
                (e.keyCode === 86 && e.ctrlKey === true) ||
                (e.keyCode === 88 && e.ctrlKey === true) ||
                // Allow home, end, left, right
                (e.keyCode >= 35 && e.keyCode <= 40)) {
                return;
            }
            // Ensure that it is a number and stop the keypress
            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();
            }
        });

    // Initialize simpanan inputs as disabled on page load
    disableSimpananInputs();
});
</script>
@endsection