@extends('layouts.app')

@section('title', 'Data Pinjaman')
@section('sub-title', 'Data Pinjaman Aktif')

@section('content')
<div class="px-1 justify-center flex flex-col">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Data Pinjaman</h1>
        <div class="flex space-x-2">
            <a href="{{ route('pinjaman.data_pengajuan') }}"
                class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                <i class="fas fa-file-alt mr-2"></i>Data Pengajuan
            </a>
            <a href="{{ route('pinjaman.lunas') }}"
                class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                <i class="fas fa-check-circle mr-2"></i>Pinjaman Lunas
            </a>
        </div>
    </div>

    <!-- Info Box tentang Kebijakan Penghapusan -->
    <!-- <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-blue-600 mt-1"></i>
            </div>
            <div class="ml-3">so javascript when i wrote my self
                <h3 class="text-sm font-medium text-blue-800">📋 Kebijakan Penghapusan Data</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <p>• Data pinjaman yang sudah memiliki pembayaran angsuran <strong>tidak dapat dihapus</strong> untuk menjaga integritas audit dan laporan keuangan</p>
                    <p>• Jika ingin menghapus data, pastikan tidak ada pembayaran angsuran yang terkait</p>
                    <p>• Data yang dihapus akan menghapus semua relasi terkait (tempo pinjaman, transaksi tagihan, dll)</p>
                </div>
            </div><!
        </div>
    </div> -->

    <!-- Filter Section -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <form method="GET" action="{{ route('pinjaman.data_pinjaman') }}" id="filterForm">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4 items-end">
                <!-- Filter Tanggal -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                    <select name="date_filter" id="dateFilter"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                        <option value="">Semua Tanggal</option>
                        <option value="hari_ini" {{ request('date_filter') == 'hari_ini' ? 'selected' : '' }}>Hari Ini
                        </option>
                        <option value="kemarin" {{ request('date_filter') == 'kemarin' ? 'selected' : '' }}>Kemarin
                        </option>
                        <option value="minggu_ini" {{ request('date_filter') == 'minggu_ini' ? 'selected' : '' }}>Minggu
                            Ini</option>
                        <option value="bulan_ini" {{ request('date_filter') == 'bulan_ini' ? 'selected' : '' }}>Bulan
                            Ini</option>
                        <option value="tahun_ini" {{ request('date_filter') == 'tahun_ini' ? 'selected' : '' }}>Tahun
                            Ini</option>
                        <option value="custom" {{ request('date_filter') == 'custom' ? 'selected' : '' }}>Rentang Kustom
                        </option>
                    </select>
                </div>

                <!-- Date Range (hidden by default) -->
                <div id="dateRange" class="hidden lg:col-span-2">
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Dari</label>
                            <input type="date" name="date_from" value="{{ request('date_from') }}"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sampai</label>
                            <input type="date" name="date_to" value="{{ request('date_to') }}"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                        </div>
                    </div>
                </div>

                <!-- Filter Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status Pinjaman</label>
                    <select name="status_pinjaman" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                        <option value="">Semua Status</option>
                        <option value="Belum Lunas" {{ request('status_pinjaman') == 'Belum Lunas' ? 'selected' : '' }}>
                            Belum Lunas</option>
                        <option value="Sudah Lunas" {{ request('status_pinjaman') == 'Sudah Lunas' ? 'selected' : '' }}>
                            Sudah Lunas</option>
                    </select>
                </div>

                <!-- Search Kode Transaksi -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cari: Kode Transaksi</label>
                    <input type="text" name="kode_transaksi" value="{{ request('kode_transaksi') }}"
                        placeholder="Kode Transaksi" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                </div>

                <!-- Search Nama Anggota -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Anggota</label>
                    <input type="text" name="nama_anggota" value="{{ request('nama_anggota') }}"
                        placeholder="Nama atau No KTP"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                </div>

                <!-- Action Buttons -->
                <div class="flex space-x-2">
                    <button type="submit"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md text-sm transition-colors duration-200">
                        <i class="fas fa-search mr-1"></i>Cari
                    </button>
                    <a href="{{ route('pinjaman.data_pinjaman') }}"
                        class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm transition-colors duration-200">
                        <i class="fas fa-times mr-1"></i>Hapus Filter
                    </a>
                    <button type="button" onclick="exportPdf()"
                        class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md text-sm transition-colors duration-200">
                        <i class="fas fa-print mr-1"></i>Cetak Laporan
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Action Buttons -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <div class="flex justify-between items-center">
            <div class="flex space-x-2">
                <button type="button" onclick="openModal('create')"
                    class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md text-sm transition-colors duration-200">
                    <i class="fas fa-plus mr-1"></i>Tambah
                </button>
                <button type="button" onclick="editSelected()"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md text-sm transition-colors duration-200">
                    <i class="fas fa-edit mr-1"></i>Edit
                </button>
                <button type="button" onclick="showDeleteConfirm()"
                    class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md text-sm transition-colors duration-200">
                    <i class="fas fa-trash mr-1"></i>Hapus
                </button>
                <button type="button" onclick="uploadData()"
                    class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-md text-sm transition-colors duration-200">
                    <i class="fas fa-upload mr-1"></i>Upload
                </button>
            </div>
        </div>
    </div>

    <!-- Tabel Transaksi -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 border-b flex items-center justify-between">
            <h2 class="text-lg font-semibold">Data Pinjaman Aktif</h2>
            @if (session('success'))
            <div class="text-green-700 bg-green-100 border border-green-300 rounded px-3 py-1 text-sm">
                {{ session('success') }}</div>
            @endif
            @if (session('error'))
            <div class="text-red-700 bg-red-100 border border-red-300 rounded px-3 py-1 text-sm">
                {{ session('error') }}</div>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="w-full table-fixed border border-gray-200 text-[12px]">
                <thead class="bg-gray-50 text-[12px] uppercase text-gray-600">
                    <tr class="w-full">
                        <th class="py-2 px-3 border text-center w-[36px]">No</th>
                        <th class="py-2 px-3 border text-center w-[40px]">
                            <input type="checkbox" id="selectAll" class="rounded border-gray-300">
                        </th>
                        <th class="py-2 px-3 border text-left whitespace-nowrap w-[110px]">Kode</th>
                        <th class="py-2 px-3 border text-left w-[110px]">Tanggal Pinjam</th>
                        <th class="py-2 px-3 border text-left whitespace-nowrap w-[200px]">Nama Anggota</th>
                        <th class="py-2 px-3 border text-left w-[200px]">Hitungan</th>
                        <th class="py-2 px-3 border text-left w-[200px]">Total Tagihan</th>
                        <th class="py-2 px-3 border text-center w-[80px]">Lunas</th>
                        <th class="py-2 px-3 border text-center w-[80px]">User</th>
                        <th class="py-2 px-3 border text-center w-[120px]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($dataPinjaman as $pinjaman)
                    <tr class="hover:bg-gray-50">
                        <td class="py-1 px-2 border text-center align-top">
                            {{ ($dataPinjaman->currentPage() - 1) * $dataPinjaman->perPage() + $loop->iteration }}
                        </td>
                        <td class="py-1 px-2 border text-center align-top">
                            <input type="checkbox" class="row-checkbox rounded border-gray-300"
                                value="{{ $pinjaman->id }}">
                        </td>
                        <td class="py-1 px-2 border font-medium text-gray-800 align-top">
                            <div class="truncate" title="{{ $pinjaman->id }}">{{ $pinjaman->id }}</div>
                        </td>
                        <td class="py-1 px-2 border align-top">
                            @php $tgl = \Carbon\Carbon::parse($pinjaman->tgl_pinjam); @endphp
                            <div class="leading-tight">
                                <div class="truncate">{{ $tgl->format('d M Y') }}</div>
                                <div class="text-[10px] text-gray-500">{{ $tgl->format('H:i') }}</div>
                            </div>
                        </td>
                        <td class="py-1 px-2 border align-top">
                            @php
                            $namaAnggota = optional($pinjaman->anggota)->nama;
                            $noKtp = optional($pinjaman->anggota)->no_ktp;
                            @endphp
                            <table class="w-full text-[10px]">
                                <tr>
                                    <td class="font-semibold text-gray-700">ID:</td>
                                    <td class="text-gray-800">{{ 'AG' . sprintf('%04d', $pinjaman->anggota_id) }}</td>
                                </tr>
                                <tr>
                                    <td class="font-semibold text-gray-700">Nama:</td>
                                    <td class="text-gray-800">{{ $namaAnggota ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="font-semibold text-gray-700">NIK:</td>
                                    <td class="text-gray-800">{{ $noKtp ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="font-semibold text-gray-700">Jenis:</td>
                                    <td class="text-gray-800">Pinjaman:
                                        {{ $pinjaman->jenis_pinjaman == '1' ? 'Biasa' : 'Barang' }}</td>
                                </tr>
                            </table>
                        </td>
                        <td class="py-1 px-2 border align-top">
                            <table class="w-full text-[10px]">
                                <tr>
                                    <td class="font-semibold text-gray-700">Pinjaman:</td>
                                    <td class="text-gray-800">Pinjaman Uang</td>
                                </tr>
                                <tr>
                                    <td class="font-semibold text-gray-700">Jumlah:</td>
                                    <td class="text-gray-800">{{ number_format($pinjaman->jumlah, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td class="font-semibold text-gray-700">Lama Angsuran:</td>
                                    <td class="text-gray-800">{{ $pinjaman->lama_angsuran }} Bulan</td>
                                </tr>
                                <tr>
                                    <td class="font-semibold text-gray-700">Pokok Angsuran:</td>
                                    <td class="text-gray-800">
                                        {{ number_format($pinjaman->jumlah_angsuran, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td class="font-semibold text-gray-700">Bunga Pinjaman:</td>
                                    <td class="text-gray-800">{{ number_format($pinjaman->bunga ?? 0, 0, ',', '.') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="font-semibold text-gray-700">Biaya Admin:</td>
                                    <td class="text-gray-800">
                                        {{ number_format($pinjaman->biaya_adm ?? 0, 0, ',', '.') }}</td>
                                </tr>
                            </table>
                        </td>
                        <td class="py-1 px-2 border align-top">
                            @php
                            $sudahDibayar = $pinjaman->detail_angsuran->sum('jumlah_bayar') ?? 0;
                            $sisaAngsuran = $pinjaman->lama_angsuran - ($pinjaman->detail_angsuran->count() ?? 0);
                            $sisaTagihan = $pinjaman->jumlah - $sudahDibayar;
                            $totalDenda = $pinjaman->detail_angsuran->sum('denda_rp') ?? 0;
                            $totalTagihan = $pinjaman->jumlah + $totalDenda;
                            @endphp
                            <table class="w-full text-[10px]">
                                <tr>
                                    <td class="font-semibold text-gray-700">Jumlah Angsuran:</td>
                                    <td class="text-gray-800">
                                        {{ number_format($pinjaman->jumlah_angsuran, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td class="font-semibold text-gray-700">Jumlah Denda:</td>
                                    <td class="text-gray-800">{{ number_format($totalDenda, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td class="font-semibold text-gray-700">Sudah Dibayar:</td>
                                    <td class="text-gray-800">{{ number_format($sudahDibayar, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td class="font-semibold text-gray-700">Sisa Angsuran:</td>
                                    <td class="text-gray-800">{{ $sisaAngsuran }}</td>
                                </tr>
                                <tr>
                                    <td class="font-semibold text-gray-700">Sisa Tagihan:</td>
                                    <td class="text-gray-800">{{ number_format($sisaTagihan, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td class="font-semibold text-gray-700">Total Tagihan:</td>
                                    <td class="text-gray-800">{{ number_format($totalTagihan, 0, ',', '.') }}</td>
                                </tr>
                            </table>
                        </td>
                        <td class="py-1 px-2 border text-center align-top">
                            @php
                            $statusLunas = $pinjaman->lunas ?? 'Belum';
                            $statusClass = $statusLunas == 'Lunas' ? 'bg-green-100 text-green-700 border-green-300' :
                            'bg-yellow-100 text-yellow-700 border-yellow-300';
                            @endphp
                            <span class="px-2 py-1 text-[10px] rounded border {{ $statusClass }}">
                                {{ $statusLunas }}
                            </span>
                        </td>
                        <td class="py-1 px-2 border text-center align-top">
                            {{ $pinjaman->user_name ?? 'admin' }}
                        </td>
                        <td class="py-1 px-2 border align-top">
                            <div class="grid grid-cols-2 gap-1">
                                <a class="w-full px-1.5 py-0.5 text-[10px] rounded border bg-blue-50 text-blue-700 border-blue-300 hover:bg-blue-100"
                                    href="{{ route('pinjaman.data_pinjaman.show', $pinjaman->id) }}" title="Detail">
                                    <i class="fas fa-search mr-1"></i>Detail
                                </a>
                                <a class="w-full px-1.5 py-0.5 text-[10px] rounded border bg-green-50 text-green-700 border-green-300 hover:bg-green-100"
                                    href="{{ route('pinjaman.nota', $pinjaman->id) }}" title="Nota" target="_blank">
                                    <i class="fas fa-print mr-1"></i>Nota
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-5 w-full relative px-2 py-2">
            <div class="mt-6">{{ $dataPinjaman->links('vendor.pagination.simple-tailwind') }}</div>

            <div class="absolute right-4 top-1/2 -translate-y-1/2 whitespace-nowrap text-sm text-gray-400">
                Menampilkan {{ $dataPinjaman->firstItem() }} - {{ $dataPinjaman->lastItem() }} dari
                {{ $dataPinjaman->total() }} data
            </div>
        </div>
    </div>
</div>

</div>

<!-- Modal Form Pinjaman -->
<div id="pinjamanModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 id="modalTitle" class="text-lg font-semibold text-gray-900">Form Tambah Pinjaman</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <form id="pinjamanForm" method="POST" action="{{ route('pinjaman.data_pinjaman.store') }}">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Left Column -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Pinjam *</label>
                        <input type="datetime-local" name="tgl_pinjam" id="tgl_pinjam" required
                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Anggota *</label>
                        <select name="anggota_id" id="anggota_id" required
                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                            <option value="">-- Pilih Anggota --</option>
                            @foreach(\App\Models\data_anggota::all() as $anggota)
                            <option value="{{ $anggota->id }}">{{ $anggota->nama }} - {{ $anggota->no_ktp }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Pinjaman *</label>
                        <input type="number" name="jumlah" id="jumlah" required min="1000" step="1000"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Lama Angsuran (Bulan) *</label>
                        <input type="number" name="lama_angsuran" id="lama_angsuran" required min="1" max="60"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bunga (%) *</label>
                        <input type="number" name="bunga" id="bunga" required min="0" max="100" step="0.1"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Pinjaman *</label>
                        <select name="jenis_pinjaman" id="jenis_pinjaman" required
                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                            <option value="">-- Pilih Jenis --</option>
                            <option value="1">Biasa</option>
                            <option value="2">Barang</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ambil Dari Kas *</label>
                        <select name="kas_id" id="kas_id" required
                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                            <option value="">-- Pilih Kas --</option>
                            @foreach(\App\Models\DataKas::all() as $kas)
                            <option value="{{ $kas->id }}">{{ $kas->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                        <textarea name="keterangan" id="keterangan" rows="3"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm"></textarea>
                    </div>

                    <!-- Photo placeholder -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Photo</label>
                        <div
                            class="w-full h-32 border-2 border-dashed border-gray-300 rounded-md flex items-center justify-center">
                            <span class="text-gray-500 text-sm">Photo akan ditampilkan di sini</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" onclick="closeModal()"
                    class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md text-sm transition-colors duration-200">
                    <i class="fas fa-times mr-1"></i>Batal
                </button>
                <button type="submit"
                    class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md text-sm transition-colors duration-200">
                    <i class="fas fa-check mr-1"></i>Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Warning Modal -->
<div id="warningModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="flex items-center mb-4">
            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100">
                <i class="fas fa-exclamation-triangle text-blue-600"></i>
            </div>
        </div>
        <div class="text-center">
            <h3 class="text-lg font-medium text-gray-900 mb-2">Peringatan!</h3>
            <p id="warningMessage" class="text-sm text-gray-500">Maaf, Data harus dipilih terlebih dahulu</p>
            <div class="mt-4">
                <button onclick="closeWarningModal()"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md text-sm transition-colors duration-200">
                    OK
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteConfirmModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="flex items-center mb-4">
            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <i class="fas fa-trash text-red-600"></i>
            </div>
        </div>
        <div class="text-center">
            <h3 class="text-lg font-medium text-gray-900 mb-2">Konfirmasi Hapus</h3>
            <p id="deleteConfirmMessage" class="text-sm text-gray-500 mb-4">Yakin ingin menghapus data yang dipilih?</p>
            <div class="flex justify-center space-x-3">
                <button onclick="closeDeleteConfirmModal()"
                    class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm transition-colors duration-200">
                    Batal
                </button>
                <button onclick="confirmDelete()"
                    class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md text-sm transition-colors duration-200">
                    <i class="fas fa-trash mr-1"></i>Hapus
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let selectedRows = [];

// Date filter functionality
document.getElementById('dateFilter').addEventListener('change', function() {
    const dateRange = document.getElementById('dateRange');
    if (this.value === 'custom') {
        dateRange.classList.remove('hidden');
    } else {
        dateRange.classList.add('hidden');
        // Auto-fill date range based on selection
        const today = new Date();
        let fromDate, toDate;

        switch (this.value) {
            case 'hari_ini':
                fromDate = toDate = today.toISOString().split('T')[0];
                break;
            case 'kemarin':
                const yesterday = new Date(today);
                yesterday.setDate(yesterday.getDate() - 1);
                fromDate = toDate = yesterday.toISOString().split('T')[0];
                break;
            case 'minggu_ini':
                const startOfWeek = new Date(today);
                startOfWeek.setDate(today.getDate() - today.getDay());
                fromDate = startOfWeek.toISOString().split('T')[0];
                toDate = today.toISOString().split('T')[0];
                break;
            case 'bulan_ini':
                fromDate = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
                toDate = today.toISOString().split('T')[0];
                break;
            case 'tahun_ini':
                fromDate = new Date(today.getFullYear(), 0, 1).toISOString().split('T')[0];
                toDate = today.toISOString().split('T')[0];
                break;
        }

        if (fromDate && toDate) {
            document.querySelector('input[name="date_from"]').value = fromDate;
            document.querySelector('input[name="date_to"]').value = toDate;
        }
    }
});

// Select all functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.row-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
        if (this.checked) {
            selectedRows.push(checkbox.value);
        } else {
            selectedRows = [];
        }
    });
});

// Individual row selection
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('row-checkbox')) {
        if (e.target.checked) {
            selectedRows.push(e.target.value);
        } else {
            selectedRows = selectedRows.filter(id => id !== e.target.value);
        }

        // Update select all checkbox
        const allCheckboxes = document.querySelectorAll('.row-checkbox');
        const checkedCheckboxes = document.querySelectorAll('.row-checkbox:checked');
        document.getElementById('selectAll').checked = allCheckboxes.length === checkedCheckboxes.length;
    }
});

// Modal functions
function openModal(type, id = null) {
    const modal = document.getElementById('pinjamanModal');
    const form = document.getElementById('pinjamanForm');
    const title = document.getElementById('modalTitle');
    const methodInput = document.getElementById('formMethod');

    if (type === 'create') {
        title.textContent = 'Form Tambah Pinjaman';
        form.action = '{{ route("pinjaman.data_pinjaman.store") }}';
        methodInput.value = 'POST';
        form.reset();
        // Set default date to now
        document.getElementById('tgl_pinjam').value = new Date().toISOString().slice(0, 16);
    } else if (type === 'edit' && id) {
        title.textContent = 'Edit Data Pinjaman';
        form.action = `/pinjaman/data_pinjaman/${id}`;
        methodInput.value = 'PUT';
        // Load data for editing (you'll need to implement this)
        loadPinjamanData(id);
    }

    modal.classList.remove('hidden');
}

function closeModal() {
    document.getElementById('pinjamanModal').classList.add('hidden');
}

function closeWarningModal() {
    document.getElementById('warningModal').classList.add('hidden');
}

function closeDeleteConfirmModal() {
    document.getElementById('deleteConfirmModal').classList.add('hidden');
}

function showDeleteConfirm() {
    if (selectedRows.length === 0) {
        showWarning('Maaf, Data harus dipilih terlebih dahulu');
        return;
    }

    const message =
        `Yakin ingin menghapus ${selectedRows.length} data yang dipilih? Data yang sudah dihapus tidak dapat dikembalikan.`;
    document.getElementById('deleteConfirmMessage').textContent = message;
    document.getElementById('deleteConfirmModal').classList.remove('hidden');
}

function confirmDelete() {
    // Hide modal first
    closeDeleteConfirmModal();

    // Show loading state
    const deleteBtn = event.target;
    const originalText = deleteBtn.innerHTML;
    deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Menghapus...';
    deleteBtn.disabled = true;

    // Debug logging
    console.log('Selected rows:', selectedRows);
    console.log('Selected rows type:', typeof selectedRows);
    console.log('Selected rows length:', selectedRows.length);

    // Create form data for bulk delete request
    const formData = new FormData();
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('ids', JSON.stringify(selectedRows));

    console.log('FormData ids:', formData.get('ids'));

    // Send bulk delete request
    fetch('{{ route("pinjaman.data_pinjaman.bulk_destroy") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);

            // Reset button state
            deleteBtn.innerHTML = originalText;
            deleteBtn.disabled = false;

            if (data.success) {
                // Show success message
                showWarning(data.message);

                // Remove successfully deleted rows from table
                if (data.success_count > 0) {
                    selectedRows.forEach(id => {
                        const row = document.querySelector(`input[value="${id}"]`);
                        if (row) {
                            const tableRow = row.closest('tr');
                            if (tableRow) {
                                tableRow.remove();
                            }
                        }
                    });
                }

                // Clear selection
                selectedRows = [];
                document.getElementById('selectAll').checked = false;
                document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = false);

                // Show detailed errors if any
                if (data.error_count > 0 && data.errors && data.errors.length > 0) {
                    console.log('Errors during deletion:', data.errors);
                }
            } else {
                showWarning(data.message || 'Gagal menghapus data yang dipilih.');
            }
        })
        .catch(error => {
            console.error('Error:', error);

            // Reset button state
            deleteBtn.innerHTML = originalText;
            deleteBtn.disabled = false;

            // Show more detailed error message
            let errorMessage = 'Terjadi kesalahan saat menghapus data.';
            if (error.message) {
                errorMessage += ' Detail: ' + error.message;
            }
            showWarning(errorMessage);
        });
}

function showWarning(message) {
    document.getElementById('warningMessage').textContent = message;
    document.getElementById('warningModal').classList.remove('hidden');
}

// Action functions
function editSelected() {
    if (selectedRows.length === 0) {
        showWarning('Maaf, Data harus dipilih terlebih dahulu');
        return;
    }

    if (selectedRows.length > 1) {
        showWarning('Maaf, hanya bisa memilih satu data untuk diedit');
        return;
    }

    openModal('edit', selectedRows[0]);
}



function uploadData() {
    // Implement upload functionality
    alert('Fitur upload akan diimplementasikan');
}

function exportPdf() {
    const form = document.getElementById('filterForm');
    const exportInput = document.createElement('input');
    exportInput.type = 'hidden';
    exportInput.name = 'export';
    exportInput.value = 'pdf';
    form.appendChild(exportInput);
    form.submit();
    form.removeChild(exportInput);
}

function loadPinjamanData(id) {
    // Load data for editing via AJAX
    fetch(`/pinjaman/data_pinjaman/${id}/data`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert('Error: ' + data.error);
                return;
            }

            // Fill form fields with data
            document.getElementById('anggota_id').value = data.anggota_id;
            document.getElementById('tgl_pinjam').value = data.tgl_pinjam.replace(' ', 'T');
            document.getElementById('jumlah').value = data.jumlah;
            document.getElementById('lama_angsuran').value = data.lama_angsuran;
            document.getElementById('bunga').value = data.bunga;
            document.getElementById('jenis_pinjaman').value = data.jenis_pinjaman;
            document.getElementById('kas_id').value = data.kas_id;
            document.getElementById('keterangan').value = data.keterangan || '';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal memuat data pinjaman');
        });
}

// Auto-calculate installment amount
document.addEventListener('input', function(e) {
    if (e.target.name === 'jumlah' || e.target.name === 'lama_angsuran') {
        const jumlah = document.getElementById('jumlah').value;
        const lamaAngsuran = document.getElementById('lama_angsuran').value;

        if (jumlah && lamaAngsuran) {
            const angsuranPerBulan = jumlah / lamaAngsuran;
            // You could display this in a readonly field if needed
            console.log('Angsuran per bulan:', angsuranPerBulan);
        }
    }
});
</script>

<style>
.scroll-tbody {
    display: block;
    max-height: 400px;
    overflow-x: auto;
    width: 100%;
}

.scroll-tbody tr {
    display: table;
    width: 100%;
    table-layout: fixed;
}

thead,
.scroll-tbody tr {
    width: 100%;
    table-layout: fixed;
}
</style>
@endsection