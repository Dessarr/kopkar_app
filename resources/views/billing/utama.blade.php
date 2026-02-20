@extends('layouts.app')

@section('title', 'Billing Utama')
@section('sub-title', 'Rekap Tagihan Utama')

<meta name="csrf-token" content="{{ csrf_token() }}">

@section('content')
<div class="container mx-auto px-4">
    @if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
        <strong class="font-bold">Error!</strong>
        <span class="block sm:inline">{!! session('error') !!}</span>
    </div>
    @endif
    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
        <strong class="font-bold">Success!</strong>
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
    @endif

    <div class="bg-white rounded-lg shadow-md mb-6">
        <div class="border-b border-gray-200 px-6 py-4">
            <h5 class="font-semibold text-lg">Billing Utama</h5>
        </div>
        <div class="p-6">
            <div class="mb-6">
                <form action="{{ route('billing.utama') }}" method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <div class="md:col-span-3">
                        <label for="bulan" class="block text-sm font-medium text-gray-700 mb-1">Bulan</label>
                        <select name="bulan" id="bulan"
                            class="w-full rounded-lg border-2 border-gray-300 bg-gray-100 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50 text-sm py-2 px-3">
                            @foreach($bulanList as $key => $namaBulan)
                            <option value="{{ $key }}" {{ (isset($bulan) && $bulan == $key) ? 'selected' : '' }}>
                                {{ $namaBulan }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-3">
                        <label for="tahun" class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
                        <select name="tahun" id="tahun"
                            class="w-full rounded-lg border-2 border-gray-300 bg-gray-100 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50 text-sm py-2 px-3">
                            @foreach($tahunList ?? [] as $tahunOption)
                            <option value="{{ $tahunOption }}"
                                {{ (isset($tahun) && $tahun == $tahunOption) ? 'selected' : '' }}>{{ $tahunOption }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-4">
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Cari Anggota</label>
                        <div class="flex items-center bg-gray-100 p-2 rounded-lg border-2 border-gray-300">
                            <i class="fa-solid fa-magnifying-glass mr-2 text-gray-400"></i>
                            <input type="text"
                                class="text-sm text-gray-500 bg-transparent border-none focus:outline-none w-full"
                                id="search" name="search" placeholder="Nama atau No ID Koperasi"
                                value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="md:col-span-2 flex items-end">
                        <div class="flex space-x-2">
                            <button type="submit"
                                class="bg-blue-100 hover:bg-blue-200 text-blue-800 text-sm font-medium px-4 py-2 rounded-lg border-2 border-blue-300 transition">Filter</button>
                            <a href="{{ route('billing.utama') }}"
                                class="bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm font-medium px-4 py-2 rounded-lg border-2 border-gray-300 transition">Reset</a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Button Upload Excel -->
            <div class="mb-6 flex justify-start gap-x-2 items-center">
                <!-- Debug Button -->
                <!-- <button type="button" onclick="debugPeriodData()"
                    class="inline-flex items-center gap-2 bg-yellow-50 border border-yellow-400 text-yellow-900 font-medium px-4 py-2 rounded-lg transition hover:bg-yellow-100 hover:border-yellow-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                    <span class="text-sm">Debug Data</span>
                </button> -->

                <!-- Upload Excel Button -->
                <button type="button" onclick="openUploadModal()"
                    class="inline-flex items-center gap-2 bg-green-50 border border-green-400 text-green-900 font-medium px-5 py-2 rounded-lg transition hover:bg-green-100 hover:border-green-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                    </svg>
                    <span class="text-sm">Add File Excel</span>
                </button>



                <!-- Proceed Button -->
                <button type="button" onclick="proceedBilling(this)"
                    class="inline-flex items-center gap-2 bg-purple-50 border border-purple-400 text-purple-900 font-medium px-5 py-2 rounded-lg transition hover:bg-purple-100 hover:border-purple-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                    <span class="text-sm">Proceed</span>
                </button>
            </div>
            <!-- Table Periode Summary -->
            <div class="mb-6" id="period-summary">
                @include('billing.partials.period-table')
            </div>
            </form>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-between items-center mb-4 p-4">
            <h2 class="text-lg font-semibold text-gray-800">Data Billing Utama</h2>
            <div class="flex space-x-3">
                <button onclick="editData()"
                    class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                    <i class="fas fa-edit"></i>
                    <span>Edit Nominal</span>
                </button>
                <button onclick="deleteData()"
                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                    <i class="fas fa-trash"></i>
                    <span>Hapus</span>
                </button>
                <button onclick="deleteAllData()"
                    class="bg-red-800 hover:bg-red-900 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                    <i class="fas fa-trash-alt"></i>
                    <span>Hapus Semua</span>
                </button>
            </div>
        </div>

        <div id="selisihValidationAlert"
            class="hidden mx-4 mb-4 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-red-800">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="font-semibold">Proceed diblokir: masih ada selisih yang belum 0</p>
                    <p id="selisihValidationText" class="text-sm mt-1"></p>
                </div>
                <button type="button" onclick="hideSelisihValidationAlert()" class="text-red-700 hover:text-red-900 text-lg leading-none">&times;</button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 text-gray-600 text-sm">
                        <th class="px-4 py-3 border-b text-center w-12">No.</th>
                        <th class="px-4 py-3 border-b text-center">No KTP</th>
                        <th class="px-4 py-3 border-b text-center">Nama</th>
                        <th class="px-4 py-3 border-b text-center">Tgl Transaksi</th>
                        <th class="px-4 py-3 border-b text-center">Toserda</th>
                        <th class="px-4 py-3 border-b text-center">Simpanan Wajib</th>
                        <th class="px-4 py-3 border-b text-center">Sukarela</th>
                        <th class="px-4 py-3 border-b text-center">Khusus 2</th>
                        <th class="px-4 py-3 border-b text-center">Pokok</th>
                        <th class="px-4 py-3 border-b text-center">Pinjaman</th>
                        <th class="px-4 py-3 border-b text-center">Total Tagihan</th>
                        <th class="px-4 py-3 border-b text-center">Tagihan Upload</th>
                        <th class="px-4 py-3 border-b text-center">Selisih</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($data as $index => $row)
                    <tr class="text-sm align-middle hover:bg-gray-50 cursor-pointer row-selectable js-billing-row {{ $index % 2 == 0 ? 'bg-white' : 'bg-gray-50' }} {{ ((float)($row->selisih_calculated ?? 0) != 0.0) ? 'billing-row-mismatch' : '' }}"
                        data-id="{{ $row->id ?? '' }}" data-no-ktp="{{ $row->no_ktp }}" data-nama="{{ $row->nama }}"
                        data-tgl-transaksi="{{ $row->tgl_transaksi }}"
                        data-tagihan-toserda="{{ $row->tagihan_toserda ?? 0 }}"
                        data-tagihan-simpanan-wajib="{{ $row->tagihan_simpanan_wajib ?? 0 }}"
                        data-tagihan-simpanan-sukarela="{{ $row->tagihan_simpanan_sukarela ?? 0 }}"
                        data-tagihan-simpanan-khusus-2="{{ $row->tagihan_simpanan_khusus_2 ?? 0 }}"
                        data-tagihan-simpanan-pokok="{{ $row->tagihan_simpanan_pokok ?? 0 }}"
                        data-tagihan-pinjaman="{{ $row->tagihan_pinjaman ?? 0 }}"
                        data-selisih="{{ $row->selisih_calculated ?? 0 }}"
                        data-total-tagihan="{{ $row->total_tagihan ?? 0 }}"
                        data-keterangan="{{ $row->keterangan ?? '' }}">
                        <td class="px-4 py-3 text-center text-sm">{{ $index + 1 }}</td>
                        <td class="px-4 py-3 text-center text-sm">{{ $row->no_ktp }}</td>
                        <td class="px-4 py-3 text-sm">{{ $row->nama }}</td>
                        <td class="px-4 py-3 text-center text-sm">
                            {{ \Carbon\Carbon::parse($row->tgl_transaksi)->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-right text-sm">
                            {{ number_format($row->tagihan_toserda ?? 0, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-sm">
                            {{ number_format($row->tagihan_simpanan_wajib ?? 0, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-sm">
                            {{ number_format($row->tagihan_simpanan_sukarela ?? 0, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-sm">
                            {{ number_format($row->tagihan_simpanan_khusus_2 ?? 0, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-sm">
                            {{ number_format($row->tagihan_simpanan_pokok ?? 0, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-sm">
                            {{ number_format($row->tagihan_pinjaman ?? 0, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-sm font-semibold">
                            {{ number_format($row->total_tagihan ?? 0, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-sm">
                            {{ number_format($row->tagihan_upload ?? 0, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-sm">
                            {{ number_format($row->selisih_calculated ?? 0, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="13" class="px-4 py-3 text-center text-sm text-gray-500">Belum ada data Billing
                            Utama</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            @if(is_object($data) && method_exists($data, 'hasPages') && $data->hasPages())
            {{ $data->withQueryString()->links('vendor.pagination.simple') }}
            @endif
        </div>
    </div>
</div>
</div>

<!-- Modal Daftar Selisih -->
<div id="selisihModal" class="fixed inset-0 bg-black bg-opacity-40 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl">
            <div class="flex items-center justify-between p-4 border-b">
                <h3 class="text-lg font-semibold text-gray-900">Daftar Anggota dengan Selisih</h3>
                <button type="button" onclick="closeSelisihModal()" class="text-gray-500 hover:text-gray-700 text-xl leading-none">&times;</button>
            </div>
            <div class="p-4">
                <p class="text-sm text-gray-600 mb-3">Perbaiki data anggota berikut hingga kolom selisih menjadi 0 sebelum melakukan proceed.</p>
                <div class="max-h-80 overflow-y-auto border rounded-lg">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left">No KTP</th>
                                <th class="px-3 py-2 text-left">Nama</th>
                                <th class="px-3 py-2 text-right">Selisih</th>
                            </tr>
                        </thead>
                        <tbody id="selisihModalBody"></tbody>
                    </table>
                </div>
            </div>
            <div class="flex justify-end p-4 border-t">
                <button type="button" onclick="closeSelisihModal()" class="px-4 py-2 bg-gray-100 border rounded-lg hover:bg-gray-200">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Upload Excel -->
<div id="uploadModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
            <div class="flex items-center justify-between p-6 border-b">
                <h3 class="text-lg font-semibold text-gray-900">Upload File Excel</h3>
                <button onclick="closeUploadModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>
            <div class="p-6">
                <form action="#" method="POST" enctype="multipart/form-data" id="uploadForm">
                    @csrf
                    <div class="mb-4">
                        <label for="excel_file" class="block text-sm font-medium text-gray-700 mb-2">Pilih File
                            Excel</label>
                        <input type="file" id="excel_file" name="excel_file" accept=".xlsx,.xls"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                        <p class="text-xs text-gray-500 mt-1">Format: .xlsx atau .xls</p>
                        <p class="text-xs text-gray-500 mt-1">Kolom yang dibutuhkan: tgl_transaksi, no_ktp, jumlah</p>
                        <p class="text-xs text-gray-500 mt-1">Format tanggal: YYYY-MM-DD (contoh: 2025-07-18)</p>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeUploadModal()"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700">
                            Upload
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl">
            <div class="flex items-center justify-between p-4 border-b">
                <h3 class="text-lg font-semibold">Edit Nominal Tagihan</h3>
                <button onclick="closeModal('editModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="editForm" class="p-4">
                <!-- Info Anggota (Read-only) -->
                <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Informasi Anggota</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="font-medium">Nama:</span>
                            <span id="edit_info_nama" class="ml-2"></span>
                        </div>
                        <div>
                            <span class="font-medium">No KTP:</span>
                            <span id="edit_info_no_ktp" class="ml-2"></span>
                        </div>
                        <div>
                            <span class="font-medium">Tanggal Transaksi:</span>
                            <span id="edit_info_tgl_transaksi" class="ml-2"></span>
                        </div>
                    </div>
                </div>

                <!-- Form Edit Nominal -->
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Toserda</label>
                            <input type="number" name="tagihan_toserda" id="edit_tagihan_toserda" min="0" step="1"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Simpanan Wajib</label>
                            <input type="number" name="tagihan_simpanan_wajib" id="edit_tagihan_simpanan_wajib" min="0"
                                step="1"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Simpanan Sukarela</label>
                            <input type="number" name="tagihan_simpanan_sukarela" id="edit_tagihan_simpanan_sukarela"
                                min="0" step="1"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Simpanan Khusus 2</label>
                            <input type="number" name="tagihan_simpanan_khusus_2" id="edit_tagihan_simpanan_khusus_2"
                                min="0" step="1"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Simpanan Pokok</label>
                            <input type="number" name="tagihan_simpanan_pokok" id="edit_tagihan_simpanan_pokok" min="0"
                                step="1"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Pinjaman</label>
                            <input type="number" name="tagihan_pinjaman" id="edit_tagihan_pinjaman" min="0" step="1"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                        </div>
                    </div>

                    <!-- Total Tagihan (Read-only) -->
                    <div class="mt-4 p-3 bg-blue-50 rounded-lg">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-700">Total Tagihan:</span>
                            <span id="edit_total_tagihan" class="text-lg font-semibold text-blue-600">0</span>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeModal('editModal')"
                        class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Batal
                    </button>
                    <button type="submit" class="px-6 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
                        Update Nominal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.billing-row-mismatch {
    background-color: #fef2f2 !important;
    border-left: 4px solid #ef4444;
}
</style>

<script>
// Global variable untuk menyimpan data row yang dipilih
let selectedRowData = null;
let currentMismatchMembers = [];

function hideSelisihValidationAlert() {
    const alertBox = document.getElementById('selisihValidationAlert');
    if (alertBox) alertBox.classList.add('hidden');
}

function clearMismatchHighlights() {
    document.querySelectorAll('.js-billing-row').forEach(row => {
        row.classList.remove('billing-row-mismatch');
    });
}

function applyMismatchHighlights(members) {
    clearMismatchHighlights();
    const mismatchSet = new Set((members || []).map(item => String(item.no_ktp)));
    document.querySelectorAll('.js-billing-row').forEach(row => {
        if (mismatchSet.has(String(row.dataset.noKtp))) {
            row.classList.add('billing-row-mismatch');
        }
    });
}

function openSelisihModal(members) {
    const body = document.getElementById('selisihModalBody');
    if (!body) return;
    body.innerHTML = '';
    (members || []).forEach(item => {
        const tr = document.createElement('tr');
        const selisih = Number(item.selisih || 0);
        tr.innerHTML = `
            <td class="px-3 py-2 border-t">${item.no_ktp || '-'}</td>
            <td class="px-3 py-2 border-t">${item.nama || '-'}</td>
            <td class="px-3 py-2 border-t text-right font-semibold ${selisih < 0 ? 'text-red-600' : 'text-orange-600'}">${new Intl.NumberFormat('id-ID').format(selisih)}</td>
        `;
        body.appendChild(tr);
    });
    document.getElementById('selisihModal').classList.remove('hidden');
}

function closeSelisihModal() {
    const modal = document.getElementById('selisihModal');
    if (modal) modal.classList.add('hidden');
}

function showSelisihValidationAlert(members) {
    const alertBox = document.getElementById('selisihValidationAlert');
    const text = document.getElementById('selisihValidationText');
    if (!alertBox || !text) return;
    const count = members?.length || 0;
    text.textContent = `Ditemukan ${count} anggota dengan selisih tidak sama dengan 0. Klik Proceed setelah data diperbaiki.`;
    alertBox.classList.remove('hidden');
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Add click event listener for row selection
    $(document).on('click', '.row-selectable', function() {
        selectRow(this, $(this).data('id'));
    });
});

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
        no_ktp: row.dataset.noKtp,
        nama: row.dataset.nama,
        tgl_transaksi: row.dataset.tglTransaksi,
        tagihan_toserda: row.dataset.tagihanToserda,
        tagihan_simpanan_wajib: row.dataset.tagihanSimpananWajib,
        tagihan_simpanan_sukarela: row.dataset.tagihanSimpananSukarela,
        tagihan_simpanan_khusus_2: row.dataset.tagihanSimpananKhusus2,
        tagihan_simpanan_pokok: row.dataset.tagihanSimpananPokok,
        tagihan_pinjaman: row.dataset.tagihanPinjaman,
        total_tagihan: row.dataset.totalTagihan,
        keterangan: row.dataset.keterangan
    };
}

// Modal functions
function openModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');

    // Set default values untuk form add
    if (modalId === 'addModal') {
        // Set tanggal sekarang sebagai default
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const dateString = `${year}-${month}-${day}`;
        document.querySelector('#addModal input[name="tgl_transaksi"]').value = dateString;

        // Reset form
        document.getElementById('addForm').reset();
        document.querySelector('#addModal input[name="tgl_transaksi"]').value = dateString;
    }
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

// CRUD functions
function editData() {
    if (!selectedRowData) {
        alert('Pilih data yang akan diedit terlebih dahulu!');
        return;
    }

    // Buka modal edit dengan data terisi
    openModal('editModal');

    // Populate info anggota (read-only)
    document.getElementById('edit_info_nama').textContent = selectedRowData.nama;
    document.getElementById('edit_info_no_ktp').textContent = selectedRowData.no_ktp;
    document.getElementById('edit_info_tgl_transaksi').textContent = new Date(selectedRowData.tgl_transaksi)
        .toLocaleDateString('id-ID');

    // Populate form dengan data yang dipilih (hanya nominal tagihan)
    document.getElementById('edit_tagihan_simpanan_wajib').value = selectedRowData.tagihan_simpanan_wajib;
    document.getElementById('edit_tagihan_simpanan_sukarela').value = selectedRowData.tagihan_simpanan_sukarela;
    document.getElementById('edit_tagihan_simpanan_khusus_2').value = selectedRowData.tagihan_simpanan_khusus_2;
    document.getElementById('edit_tagihan_simpanan_pokok').value = selectedRowData.tagihan_simpanan_pokok;
    document.getElementById('edit_tagihan_pinjaman').value = selectedRowData.tagihan_pinjaman;
    document.getElementById('edit_tagihan_toserda').value = selectedRowData.tagihan_toserda;

    // Hitung total tagihan awal
    calculateTotalTagihan();

    // Add event listeners untuk auto-calculate total
    const inputs = document.querySelectorAll('#editModal input[type="number"]');
    inputs.forEach(input => {
        input.addEventListener('input', calculateTotalTagihan);
    });
}

// Function untuk menghitung total tagihan
function calculateTotalTagihan() {
    const toserda = parseFloat(document.getElementById('edit_tagihan_toserda').value) || 0;
    const simpananWajib = parseFloat(document.getElementById('edit_tagihan_simpanan_wajib').value) || 0;
    const simpananSukarela = parseFloat(document.getElementById('edit_tagihan_simpanan_sukarela').value) || 0;
    const simpananKhusus2 = parseFloat(document.getElementById('edit_tagihan_simpanan_khusus_2').value) || 0;
    const simpananPokok = parseFloat(document.getElementById('edit_tagihan_simpanan_pokok').value) || 0;
    const pinjaman = parseFloat(document.getElementById('edit_tagihan_pinjaman').value) || 0;

    const total = toserda + simpananWajib + simpananSukarela + simpananKhusus2 + simpananPokok + pinjaman;

    document.getElementById('edit_total_tagihan').textContent = new Intl.NumberFormat('id-ID').format(total);
}

function deleteData() {
    if (!selectedRowData) {
        alert('Pilih data yang akan dihapus terlebih dahulu!');
        return;
    }

    if (confirm(`Apakah Anda yakin ingin menghapus data billing untuk ${selectedRowData.nama}?`)) {
        // Kirim request delete
        const deleteUrl = `{{ url('billing-utama') }}/${selectedRowData.id}`;
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

function deleteAllData() {
    const bulan = document.getElementById('bulan')?.value;
    const tahun = document.getElementById('tahun')?.value;

    if (!bulan || !tahun) {
        alert('Bulan dan tahun harus dipilih terlebih dahulu.');
        return;
    }

    const rowCount = document.querySelectorAll('.js-billing-row').length;
    if (rowCount === 0) {
        alert('Tidak ada data Billing Utama untuk dihapus pada periode ini.');
        return;
    }

    if (!confirm(`Apakah Anda yakin ingin menghapus SEMUA data Billing Utama periode ${bulan}-${tahun}?\n\nTindakan ini tidak dapat dibatalkan.`)) {
        return;
    }

    fetch(`{{ route('billing.utama.destroy_all') }}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ bulan, tahun })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`${data.message}\n\nData billing utama terhapus: ${data.deleted_billing_utama}\nData upload terhapus: ${data.deleted_upload}`);
            window.location.reload();
        } else {
            alert('Gagal menghapus semua data: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Delete all error:', error);
        alert('Terjadi kesalahan saat menghapus semua data.');
    });
}

// Form submission untuk edit data
document.getElementById('editForm').addEventListener('submit', function(e) {
    e.preventDefault();

    if (!selectedRowData) {
        alert('Tidak ada data yang dipilih untuk diedit');
        return;
    }

    // Validasi minimal - setidaknya ada satu nominal yang diisi
    const toserda = parseFloat(document.getElementById('edit_tagihan_toserda').value) || 0;
    const simpananWajib = parseFloat(document.getElementById('edit_tagihan_simpanan_wajib').value) || 0;
    const simpananSukarela = parseFloat(document.getElementById('edit_tagihan_simpanan_sukarela').value) || 0;
    const simpananKhusus2 = parseFloat(document.getElementById('edit_tagihan_simpanan_khusus_2').value) || 0;
    const simpananPokok = parseFloat(document.getElementById('edit_tagihan_simpanan_pokok').value) || 0;
    const pinjaman = parseFloat(document.getElementById('edit_tagihan_pinjaman').value) || 0;

    const total = toserda + simpananWajib + simpananSukarela + simpananKhusus2 + simpananPokok + pinjaman;

    if (total === 0) {
        alert('Minimal harus ada satu nominal tagihan yang diisi');
        return;
    }

    const formData = new FormData(this);
    const updateUrl = `{{ url('billing-utama') }}/${selectedRowData.id}`;

    fetch(updateUrl, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(Object.fromEntries(formData))
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Nominal tagihan berhasil diupdate');
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

function openUploadModal() {
    document.getElementById('uploadModal').classList.remove('hidden');
    excelLogger.logInfo('MODAL', 'Upload modal opened');
}

function closeUploadModal() {
    document.getElementById('uploadModal').classList.add('hidden');
    excelLogger.logInfo('MODAL', 'Upload modal closed');
}

// Close modal when clicking outside
document.getElementById('uploadModal').addEventListener('click', function(e) {
    if (e.target === this) {
        excelLogger.logInfo('MODAL', 'Modal closed by clicking outside');
        closeUploadModal();
    }
});

// Excel Upload Logger Class
class ExcelUploadLogger {
    constructor() {
        this.uploadStartTime = null;
        this.totalRows = 0;
        this.validRows = 0;
        this.invalidRows = 0;
        this.errors = [];
        this.warnings = [];
        this.fileName = '';
        this.fileSize = 0;
    }

    // Log saat mulai upload
    logUploadStart(fileName, fileSize) {
        this.uploadStartTime = new Date();
        this.fileName = fileName;
        this.fileSize = fileSize;

        console.log('🚀 === EXCEL UPLOAD STARTED ===');
        console.log(`📁 File: ${fileName}`);
        console.log(`📊 Size: ${(fileSize / 1024).toFixed(2)} KB`);
        console.log(`⏰ Time: ${this.uploadStartTime.toLocaleString()}`);
        console.log(
            `📅 Period: ${document.getElementById('bulan').value}-${document.getElementById('tahun').value}`);
        console.log('================================');
    }

    // Log validasi file
    logFileValidation(file) {
        console.log('🔍 === FILE VALIDATION ===');

        // Validasi extension
        const allowedExtensions = ['.xlsx', '.xls'];
        const fileExtension = file.name.substring(file.name.lastIndexOf('.')).toLowerCase();

        if (allowedExtensions.includes(fileExtension)) {
            console.log(`✅ File Extension: ${fileExtension} (Valid)`);
        } else {
            console.error(`❌ File Extension: ${fileExtension} (Invalid)`);
            console.error(`   Allowed: ${allowedExtensions.join(', ')}`);
        }

        // Validasi ukuran file
        const maxSize = 10 * 1024 * 1024; // 10MB
        if (file.size <= maxSize) {
            console.log(`✅ File Size: ${(file.size / 1024).toFixed(2)} KB (Valid)`);
        } else {
            console.warn(`⚠️  File Size: ${(file.size / 1024).toFixed(2)} KB (Large file)`);
        }

        console.log('================================');
    }

    // Log proses upload
    logUploadProgress(current, total, message = '') {
        const percentage = ((current / total) * 100).toFixed(1);
        console.log(`📤 Upload Progress: ${current}/${total} (${percentage}%) ${message}`);
    }

    // Log hasil upload
    logUploadResult(result) {
        const uploadEndTime = new Date();
        const duration = uploadEndTime - this.uploadStartTime;

        console.log('✅ === UPLOAD COMPLETED ===');
        console.log(`⏱️  Duration: ${duration}ms`);
        console.log(`📊 Total Processed: ${result.total || 0}`);
        console.log(`✅ Success: ${result.success || 0}`);
        console.log(`❌ Errors: ${result.errors || 0}`);
        console.log(`⚠️  Warnings: ${result.warnings || 0}`);

        if (result.message) {
            console.log(`📝 Message: ${result.message}`);
        }

        if (result.details) {
            console.log('📋 Details:');
            Object.entries(result.details).forEach(([key, value]) => {
                console.log(`   ${key}: ${value}`);
            });
        }

        console.log('============================');
    }

    // Log error spesifik
    logError(type, message, data = null) {
        this.errors.push({
            type,
            message,
            data,
            timestamp: new Date()
        });
        console.error(`❌ ERROR [${type}]: ${message}`);
        if (data) {
            console.error('   Data:', data);
        }
    }

    // Log warning
    logWarning(type, message, data = null) {
        this.warnings.push({
            type,
            message,
            data,
            timestamp: new Date()
        });
        console.warn(`⚠️  WARNING [${type}]: ${message}`);
        if (data) {
            console.warn('   Data:', data);
        }
    }

    // Log info
    logInfo(type, message, data = null) {
        console.info(`ℹ️  INFO [${type}]: ${message}`);
        if (data) {
            console.info('   Data:', data);
        }
    }

    // Generate summary report
    generateSummary() {
        console.log('📊 === UPLOAD SUMMARY ===');
        console.log(`📁 File: ${this.fileName}`);
        console.log(`⏰ Start Time: ${this.uploadStartTime?.toLocaleString()}`);
        console.log(`📊 File Size: ${(this.fileSize / 1024).toFixed(2)} KB`);
        console.log(
            `📅 Period: ${document.getElementById('bulan').value}-${document.getElementById('tahun').value}`);
        console.log(`✅ Valid Rows: ${this.validRows}`);
        console.log(`❌ Invalid Rows: ${this.invalidRows}`);
        console.log(`⚠️  Warnings: ${this.warnings.length}`);
        console.log(`❌ Errors: ${this.errors.length}`);

        if (this.errors.length > 0) {
            console.log('❌ Error Details:');
            this.errors.forEach((error, index) => {
                console.log(`   ${index + 1}. [${error.type}] ${error.message}`);
            });
        }

        if (this.warnings.length > 0) {
            console.log('⚠️  Warning Details:');
            this.warnings.forEach((warning, index) => {
                console.log(`   ${index + 1}. [${warning.type}] ${warning.message}`);
            });
        }

        console.log('==========================');
    }
}

// Inisialisasi logger
const excelLogger = new ExcelUploadLogger();

// Event listener untuk file selection
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('excel_file');

    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                excelLogger.logInfo('FILE_SELECTED', `File selected: ${file.name}`);
                excelLogger.logFileValidation(file);
            }
        });
    }
});

// Handle form submission dengan logging
document.getElementById('uploadForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const fileInput = document.getElementById('excel_file');
    const bulan = document.getElementById('bulan').value;
    const tahun = document.getElementById('tahun').value;

    if (!fileInput.files[0]) {
        excelLogger.logError('FILE_SELECTION', 'No file selected');
        alert('Silakan pilih file Excel terlebih dahulu');
        return;
    }

    const file = fileInput.files[0];

    // Log file validation
    excelLogger.logFileValidation(file);

    // Log upload start
    excelLogger.logUploadStart(file.name, file.size);

    // Add bulan and tahun to form data
    formData.append('bulan', bulan);
    formData.append('tahun', tahun);

    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Uploading...';
    submitBtn.disabled = true;

    // Log progress
    excelLogger.logUploadProgress(1, 3, 'Starting upload...');

    // Submit via AJAX
    fetch('/billing-upload/excel', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            excelLogger.logUploadProgress(2, 3, 'Processing response...');
            return response.json();
        })
        .then(data => {
            excelLogger.logUploadProgress(3, 3, 'Finalizing...');

            if (data.status === 'success') {
                // Log success
                excelLogger.logUploadResult({
                    total: data.total || 0,
                    success: data.success || 0,
                    errors: data.errors || 0,
                    warnings: data.warnings || 0,
                    message: data.message,
                    details: data.details || {}
                });

                excelLogger.generateSummary();

                alert(data.message);
                closeUploadModal();
                // Refresh the page to show updated data
                location.reload();
            } else {
                // Log error
                excelLogger.logError('UPLOAD_FAILED', data.message, data);
                excelLogger.generateSummary();
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            // Log error
            excelLogger.logError('NETWORK_ERROR', 'Network or server error', error);
            excelLogger.generateSummary();
            console.error('Upload error:', error);
            alert('Terjadi kesalahan saat upload: ' + error.message);
        })
        .finally(() => {
            // Reset button state
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        });
});

// Update period summary when month/year changes
document.addEventListener('DOMContentLoaded', function() {
    const bulanSelect = document.getElementById('bulan');
    const tahunSelect = document.getElementById('tahun');

    function updatePeriodSummary() {
        const bulan = bulanSelect.value;
        const tahun = tahunSelect.value;

        console.log('📅 === PERIOD SUMMARY UPDATE ===');
        console.log(`🔄 Updating period: ${bulan}-${tahun}`);
        console.log(`⏰ Time: ${new Date().toLocaleString()}`);

        // Show loading state
        const periodSummary = document.getElementById('period-summary');
        if (periodSummary) {
            periodSummary.style.opacity = '0.6';
        }

        // Fetch new period data
        fetch(`/billing-periode/summary/${bulan}/${tahun}`)
            .then(response => {
                console.log('📥 Period summary response received');
                return response.json();
            })
            .then(data => {
                console.log('📊 Period summary data:', data);

                if (data.status === 'success') {
                    console.log('✅ Period summary updated successfully');
                    // Update period display
                    updatePeriodSummaryDisplay(data.data);
                } else {
                    console.warn('⚠️  Period summary update failed:', data.message);
                }
            })
            .catch(error => {
                console.error('❌ Error updating period summary:', error);
            })
            .finally(() => {
                console.log('🏁 Period summary update completed');
                // Remove loading state
                if (periodSummary) {
                    periodSummary.style.opacity = '1';
                }
            });
    }

    function updatePeriodSummaryDisplay(data) {
        // Update menggunakan ID unik, bukan querySelectorAll yang bisa mengubah header
        const periodValue = document.getElementById('period-value');
        const totalAnggotaValue = document.getElementById('total-anggota-value');
        const simpananSukarelaValue = document.getElementById('simpanan-sukarela-value');
        const simpananPokokValue = document.getElementById('simpanan-pokok-value');
        const simpananWajibValue = document.getElementById('simpanan-wajib-value');

        if (periodValue) periodValue.textContent = data.periode;
        if (totalAnggotaValue) totalAnggotaValue.textContent = new Intl.NumberFormat('id-ID').format(data
            .total_anggota);
        if (simpananSukarelaValue) simpananSukarelaValue.textContent = new Intl.NumberFormat('id-ID').format(
            data.simpanan_sukarela);
        if (simpananPokokValue) simpananPokokValue.textContent = new Intl.NumberFormat('id-ID').format(data
            .simpanan_pokok);
        if (simpananWajibValue) simpananWajibValue.textContent = new Intl.NumberFormat('id-ID').format(data
            .simpanan_wajib);
    }

    // Add event listeners
    bulanSelect.addEventListener('change', function() {
        console.log('📅 Bulan changed to:', bulanSelect.value);
        updatePeriodSummary();
    });

    tahunSelect.addEventListener('change', function() {
        console.log('📅 Tahun changed to:', tahunSelect.value);
        updatePeriodSummary();
    });
});

// Debug function to check what data exists
function debugPeriodData() {
    const bulan = document.getElementById('bulan').value;
    const tahun = document.getElementById('tahun').value;

    console.log('🔍 === DEBUG PERIOD DATA ===');
    console.log(`📅 Period: ${bulan}-${tahun}`);
    console.log(`⏰ Time: ${new Date().toLocaleString()}`);
    console.log('============================');

    fetch(`/billing-periode/debug/${bulan}/${tahun}`)
        .then(response => {
            console.log('📥 Debug response received');
            return response.json();
        })
        .then(data => {
            console.log('📊 Debug data:', data);

            if (data.status === 'success') {
                console.log('✅ Debug successful');
                console.log('🔍 Debug info:', data.debug_info);
                alert(
                    `Debug Info:\n\nPeriode: ${data.debug_info.periode}\nTotal Records Billing: ${data.debug_info.billing_table_total_records}\nTotal Records Anggota: ${data.debug_info.anggota_table_total_records}\nData for Period: ${data.debug_info.billing_data_for_period}\nAnggota for Period: ${data.debug_info.anggota_data_for_period}`
                );
            } else {
                console.error('❌ Debug failed:', data.message);
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('❌ Debug error:', error);
            alert('Error debugging data: ' + error.message);
        })
        .finally(() => {
            console.log('🏁 Debug process completed');
        });
}



// Proceed function to process billing data
function proceedBilling(buttonEl) {
    const bulan = document.getElementById('bulan').value;
    const tahun = document.getElementById('tahun').value;
    hideSelisihValidationAlert();
    closeSelisihModal();
    currentMismatchMembers = [];

    console.log('🚀 === PROCEED BILLING STARTED ===');
    console.log(`📅 Period: ${bulan}-${tahun}`);
    console.log(`⏰ Time: ${new Date().toLocaleString()}`);
    console.log('================================');

    if (!confirm('Apakah Anda yakin ingin memproses data billing untuk periode ' + bulan + '-' + tahun +
            '?\n\nTindakan ini akan:\n1. Memproses semua pembayaran ke database utama\n2. Mengupdate status pembayaran\n3. Menghapus data temporary\n\nData yang sudah diproses tidak dapat dibatalkan.'
        )) {
        console.log('❌ Proceed billing cancelled by user');
        return;
    }

    // Show loading state
    const proceedButton = buttonEl;
    const originalText = proceedButton.innerHTML;
    proceedButton.innerHTML = `
        <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span class="text-sm">Processing...</span>
    `;
    proceedButton.disabled = true;

    console.log('📤 Proceeding billing for:', bulan, tahun);

    fetch('/billing/proceed', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                bulan: bulan,
                tahun: tahun
            })
        })
        .then(response => {
            console.log('📥 Response received from server');
            return response.json();
        })
        .then(data => {
            console.log('📊 Proceed result:', data);

            if (data.status === 'success') {
                console.log('✅ Proceed billing successful!');
                alert('Berhasil! Data billing berhasil diproses.\n\n');
                // Reload page to show updated data
                window.location.reload();
            } else {
                console.error('❌ Proceed billing failed:', data.message);
                if (data.code === 'VALIDATION_SELISIH' && Array.isArray(data.mismatch_members)) {
                    currentMismatchMembers = data.mismatch_members;
                    applyMismatchHighlights(currentMismatchMembers);
                    showSelisihValidationAlert(currentMismatchMembers);
                    openSelisihModal(currentMismatchMembers);
                    alert((data.message || 'Proceed diblokir karena selisih belum 0') + `\nJumlah anggota bermasalah: ${currentMismatchMembers.length}`);
                } else {
                    alert('Error: ' + data.message);
                }
            }
        })
        .catch(error => {
            console.error('❌ Proceed error:', error);
            alert('Error memproses data: ' + error.message);
        })
        .finally(() => {
            console.log('🏁 Proceed billing process completed');
            // Restore button state
            proceedButton.innerHTML = originalText;
            proceedButton.disabled = false;
        });
}
</script>
@endsection