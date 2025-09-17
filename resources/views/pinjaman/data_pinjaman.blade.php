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

                <!-- Filter Jenis Pinjaman -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Pinjaman</label>
                    <select name="jenis_pinjaman" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                        <option value="">Semua Jenis</option>
                        <option value="1" {{ request('jenis_pinjaman') == '1' ? 'selected' : '' }}>Biasa</option>
                        <option value="2" {{ request('jenis_pinjaman') == '2' ? 'selected' : '' }}>Bank</option>
                        <option value="3" {{ request('jenis_pinjaman') == '3' ? 'selected' : '' }}>Barang</option>
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
                <button type="button" onclick="editData()"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md text-sm transition-colors duration-200">
                    <i class="fas fa-edit mr-1"></i>Edit
                </button>
                <button type="button" onclick="deleteData()"
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
                    <tr class="hover:bg-gray-50 cursor-pointer row-selectable" data-id="{{ $pinjaman->id }}"
                        data-kode="{{ $pinjaman->id }}" data-tanggal="{{ $pinjaman->tgl_pinjam }}"
                        data-anggota-id="{{ $pinjaman->anggota_id }}"
                        data-anggota-nama="{{ optional($pinjaman->anggota)->nama }}"
                        data-anggota-ktp="{{ optional($pinjaman->anggota)->no_ktp }}"
                        data-jumlah="{{ $pinjaman->jumlah }}" data-lama-angsuran="{{ $pinjaman->lama_angsuran }}"
                        data-jasa="{{ $pinjaman->bunga }}" data-jenis-pinjaman="{{ $pinjaman->jenis_pinjaman }}"
                        data-kas-id="{{ $pinjaman->kas_id }}" data-keterangan="{{ $pinjaman->keterangan }}"
                        data-status="{{ $pinjaman->status }}" data-lunas="{{ $pinjaman->lunas }}"
                        data-user="{{ $pinjaman->user_name }}">
                        <td class="py-1 px-2 border text-center align-top">
                            {{ ($dataPinjaman->currentPage() - 1) * $dataPinjaman->perPage() + $loop->iteration }}
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
                                        @if($pinjaman->jenis_pinjaman == '1')
                                        Biasa
                                        @elseif($pinjaman->jenis_pinjaman == '2')
                                        Bank
                                        @elseif($pinjaman->jenis_pinjaman == '3')
                                        Barang
                                        @else
                                        Tidak Diketahui
                                        @endif
                                    </td>
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
        <!-- Pagination -->
        @if($dataPinjaman->hasPages())
        <div class="mt-6">
            {{ $dataPinjaman->links('vendor.pagination.simple') }}
        </div>
        @endif

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
                    <!-- Tanggal Pinjam -->
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="tgl_pinjam">
                            Tanggal Pinjam *
                        </label>
                        <input type="datetime-local" name="tgl_pinjam" id="tgl_pinjam" required
                            class="form-input w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent">
                    </div>
                    <!-- Nama Anggota -->
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="anggota_id">
                            Nama Anggota *
                        </label>
                        <select
                            class="form-select w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent"
                            id="anggota_id" name="anggota_id" required>
                            <option value="">-PILIH-</option>
                            @foreach(\App\Models\data_anggota::all() as $anggota)
                            <option value="{{ $anggota->id }}">{{ $anggota->nama }} - {{ $anggota->no_ktp }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Jumlah Pinjaman -->
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="jumlah">
                            Jumlah Pinjaman *
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">Rp</span>
                            <input type="text"
                                class="form-input w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent"
                                id="jumlah" name="jumlah" placeholder="Masukkan nominal pinjaman" required
                                onkeyup="formatNumber(this)">
                        </div>
                    </div>

                    <!-- Lama Angsuran -->
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="lama_angsuran">
                            Lama Angsuran (Bulan) *
                        </label>
                        <input type="number" name="lama_angsuran" id="lama_angsuran" required min="1" max="60"
                            class="form-input w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent"
                            placeholder="Masukkan lama angsuran">
                    </div>

                    <!-- Jumlah Angsuran (Auto Calculate) -->
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="jumlah_angsuran">
                            Jumlah Angsuran (Otomatis)
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">Rp</span>
                            <input type="text" name="jumlah_angsuran" id="jumlah_angsuran" readonly
                                class="form-input w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed"
                                value="0" placeholder="Akan dihitung otomatis">
                        </div>
                    </div>

                    <!-- Jasa (Rp) -->
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="jasa">
                            Jasa (Rp) *
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">Rp</span>
                            <input type="number" name="jasa" id="jasa" required min="0" step="1000"
                                class="form-input w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent"
                                placeholder="Masukkan nominal jasa" value="0">
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-4">
                    <!-- Jenis Pinjaman -->
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="jenis_pinjaman">
                            Jenis Pinjaman *
                        </label>
                        <select
                            class="form-select w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent"
                            id="jenis_pinjaman" name="jenis_pinjaman" required>
                            <option value="">-PILIH-</option>
                            <option value="1">Biasa</option>
                            <option value="2">Bank</option>
                            <option value="3">Barang</option>
                        </select>
                    </div>

                    <!-- Ambil Dari Kas -->
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="kas_id">
                            Ambil Dari Kas *
                        </label>
                        <select
                            class="form-select w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent"
                            id="kas_id" name="kas_id" required>
                            <option value="">-PILIH-</option>
                            @foreach(\App\Models\DataKas::all() as $kas)
                            <option value="{{ $kas->id }}">{{ $kas->nama }}</option>
                            @endforeach
                        </select>
                    </div>


                    <!-- Keterangan -->
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="keterangan">
                            Keterangan *
                        </label>
                        <textarea
                            class="form-textarea w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent"
                            id="keterangan" name="keterangan" rows="4" placeholder="Masukkan keterangan"
                            required></textarea>
                        <p class="text-sm text-gray-600 mt-1">*Harus diisi</p>
                    </div>

                    <!-- Photo -->
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="photo">
                            Photo
                        </label>
                        <div
                            class="w-full h-32 border-2 border-dashed border-gray-300 rounded-md flex items-center justify-center">
                            <span class="text-gray-500 text-sm">Photo akan ditampilkan di sini</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end mt-6">
                <button type="button" onclick="closeModal()"
                    class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg mr-3 transition duration-200">
                    <i class="fas fa-times mr-1"></i>Batal
                </button>
                <button type="submit"
                    class="bg-[#14AE5C] text-white px-4 py-2 rounded-lg hover:bg-[#14AE5C]/80 transition duration-200">
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
// Global variable untuk menyimpan data row yang dipilih
let selectedRowData = null;
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

// Add click event listener for row selection
document.addEventListener('click', function(e) {
    if (e.target.closest('.row-selectable')) {
        const row = e.target.closest('.row-selectable');
        selectRow(row, row.dataset.id);
    }
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
        kode: row.dataset.kode,
        tanggal: row.dataset.tanggal,
        anggota_id: row.dataset.anggotaId,
        anggota_nama: row.dataset.anggotaNama,
        anggota_ktp: row.dataset.anggotaKtp,
        jumlah: row.dataset.jumlah,
        lama_angsuran: row.dataset.lamaAngsuran,
        jasa: row.dataset.jasa,
        jenis_pinjaman: row.dataset.jenisPinjaman,
        kas_id: row.dataset.kasId,
        keterangan: row.dataset.keterangan,
        status: row.dataset.status,
        lunas: row.dataset.lunas,
        user: row.dataset.user
    };

    // Update tombol berdasarkan status lunas
    updateButtonStates();
}

// Function untuk update state tombol berdasarkan data yang dipilih
function updateButtonStates() {
    const editBtn = document.querySelector('button[onclick="editData()"]');
    const deleteBtn = document.querySelector('button[onclick="deleteData()"]');

    if (!selectedRowData) {
        // Reset tombol jika tidak ada data terpilih
        if (editBtn) {
            editBtn.disabled = true;
            editBtn.classList.add('opacity-50', 'cursor-not-allowed');
            editBtn.classList.remove('hover:bg-blue-600');
        }
        if (deleteBtn) {
            deleteBtn.disabled = true;
            deleteBtn.classList.add('opacity-50', 'cursor-not-allowed');
            deleteBtn.classList.remove('hover:bg-red-600');
        }
        return;
    }

    // Update tombol Edit
    if (editBtn) {
        if (selectedRowData.lunas === 'Lunas') {
            editBtn.disabled = true;
            editBtn.classList.add('opacity-50', 'cursor-not-allowed');
            editBtn.classList.remove('hover:bg-blue-600');
            editBtn.title = 'Pinjaman yang sudah lunas tidak dapat diedit';
        } else {
            editBtn.disabled = false;
            editBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            editBtn.classList.add('hover:bg-blue-600');
            editBtn.title = 'Edit data pinjaman';
        }
    }

    // Update tombol Delete
    if (deleteBtn) {
        if (selectedRowData.lunas === 'Lunas') {
            deleteBtn.disabled = true;
            deleteBtn.classList.add('opacity-50', 'cursor-not-allowed');
            deleteBtn.classList.remove('hover:bg-red-600');
            deleteBtn.title = 'Pinjaman yang sudah lunas tidak dapat dihapus';
        } else {
            deleteBtn.disabled = false;
            deleteBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            deleteBtn.classList.add('hover:bg-red-600');
            deleteBtn.title = 'Hapus data pinjaman';
        }
    }
}

// Select all functionality
const selectAllElement = document.getElementById('selectAll');
if (selectAllElement) {
    selectAllElement.addEventListener('change', function() {
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
} else {
    console.log('SelectAll element not found - skipping select all functionality');
}

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
        const selectAllElement = document.getElementById('selectAll');
        if (selectAllElement) {
            selectAllElement.checked = allCheckboxes.length === checkedCheckboxes.length;
        }
    }
});

// Modal functions
function openModal(type, id = null) {
    console.log('openModal() called with type:', type, 'id:', id);

    const modal = document.getElementById('pinjamanModal');
    const form = document.getElementById('pinjamanForm');
    const title = document.getElementById('modalTitle');
    const methodInput = document.getElementById('formMethod');

    // Hapus warning yang ada
    const existingWarning = document.querySelector('.bg-yellow-50');
    if (existingWarning) {
        existingWarning.remove();
    }

    if (type === 'create') {
        title.textContent = 'Form Tambah Pinjaman';
        form.action = '{{ route("pinjaman.data_pinjaman.store") }}';
        methodInput.value = 'POST';
        form.dataset.mode = 'create';
        delete form.dataset.pinjamanId;
        form.reset();
        // Set default date to now
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const datetimeString = `${year}-${month}-${day}T${hours}:${minutes}`;
        document.getElementById('tgl_pinjam').value = datetimeString;

        // Reset jumlah angsuran untuk create
        document.getElementById('jumlah_angsuran').value = '0';
    } else if (type === 'edit' && id) {
        console.log('Setting up edit modal for ID:', id);
        title.textContent = 'Edit Data Pinjaman';
        form.action = `/pinjaman/data_pinjaman/${id}`;
        methodInput.value = 'PUT';
        form.dataset.mode = 'edit';
        form.dataset.pinjamanId = id;
        // Note: loadPinjamanData will be called separately
    }

    console.log('Showing modal...');
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

// CRUD functions
function editData() {
    console.log('editData() called', selectedRowData);

    if (!selectedRowData) {
        alert('Pilih data yang akan diedit terlebih dahulu!');
        return;
    }

    // Cek apakah pinjaman sudah lunas
    if (selectedRowData.lunas === 'Sudah') {
        alert('Pinjaman yang sudah lunas tidak dapat diedit!');
        return;
    }

    // Load data pinjaman dari server dan buka modal
    console.log('Loading data for ID:', selectedRowData.id);
    loadPinjamanData(selectedRowData.id);
}

// Load data pinjaman untuk edit
function loadPinjamanData(id) {
    console.log('loadPinjamanData() called with ID:', id);

    fetch(`/pinjaman/data_pinjaman/${id}/data`)
        .then(response => response.json())
        .then(data => {
            console.log('Response from server:', data);

            if (data.success) {
                console.log('Opening modal for edit...');
                // Buka modal edit terlebih dahulu
                openModal('edit', id);

                // Populate form dengan data dari server
                const pinjaman = data.data;
                const form = document.getElementById('pinjamanForm');

                // Format tanggal untuk input datetime-local
                const tanggal = new Date(pinjaman.tgl_pinjam);
                const formattedDate = tanggal.toISOString().slice(0, 16);

                // Tunggu modal terbuka dulu, lalu isi form
                setTimeout(() => {
                    document.getElementById('tgl_pinjam').value = formattedDate;
                    document.getElementById('anggota_id').value = pinjaman.anggota_id;

                    // Format jumlah pinjaman dengan pemisah ribuan
                    const jumlahFormatted = parseInt(pinjaman.jumlah).toLocaleString('id-ID');
                    document.getElementById('jumlah').value = jumlahFormatted;
                    console.log('Set jumlah input to:', jumlahFormatted, 'from:', pinjaman.jumlah);

                    document.getElementById('lama_angsuran').value = pinjaman.lama_angsuran;
                    document.getElementById('jasa').value = pinjaman.jasa || '0';
                    document.getElementById('jenis_pinjaman').value = pinjaman.jenis_pinjaman;
                    document.getElementById('kas_id').value = pinjaman.kas_id;
                    document.getElementById('keterangan').value = pinjaman.keterangan || '';

                    // Simpan data original untuk validasi perubahan kritis
                    form.dataset.original_tgl_pinjam = formattedDate;
                    form.dataset.original_anggota_id = pinjaman.anggota_id;
                    form.dataset.original_jumlah = pinjaman.jumlah;
                    form.dataset.original_lama_angsuran = pinjaman.lama_angsuran;
                    form.dataset.original_jasa = pinjaman.jasa;
                    form.dataset.original_jenis_pinjaman = pinjaman.jenis_pinjaman;
                    form.dataset.original_kas_id = pinjaman.kas_id;
                    form.dataset.original_keterangan = pinjaman.keterangan || '';

                    // Auto calculate jumlah angsuran
                    const jumlahPinjaman = parseInt(pinjaman.jumlah);
                    const lamaAngsuran = parseInt(pinjaman.lama_angsuran);
                    console.log('Load data - Jumlah Pinjaman:', jumlahPinjaman, 'Lama Angsuran:',
                        lamaAngsuran);

                    if (lamaAngsuran && jumlahPinjaman && lamaAngsuran > 0) {
                        const jumlahAngsuran = jumlahPinjaman / lamaAngsuran;
                        const roundedAngsuran = Math.round(jumlahAngsuran);
                        const formattedAngsuran = roundedAngsuran.toLocaleString('id-ID');
                        document.getElementById('jumlah_angsuran').value = formattedAngsuran;
                        console.log('Load data - Calculated angsuran:', formattedAngsuran);
                    } else {
                        document.getElementById('jumlah_angsuran').value = '0';
                        console.log('Load data - Reset angsuran to 0');
                    }

                    // Trigger auto-calculation untuk memastikan event listener aktif
                    setTimeout(() => {
                        const jumlahInput = document.getElementById('jumlah');
                        const lamaInput = document.getElementById('lama_angsuran');

                        // Trigger input event untuk memicu auto-calculation
                        if (jumlahInput && lamaInput) {
                            jumlahInput.dispatchEvent(new Event('input', {
                                bubbles: true
                            }));
                        }
                    }, 200);

                    // Tampilkan warning jika ada pembayaran
                    if (pinjaman.status === 'berjalan') {
                        showEditWarning();
                    }
                }, 100);
            } else {
                alert('Gagal memuat data pinjaman: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat memuat data pinjaman');
        });
}

// Tampilkan warning untuk edit
function showEditWarning() {
    const warningDiv = document.createElement('div');
    warningDiv.className = 'bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4';
    warningDiv.innerHTML = `
        <div class="flex items-start">
            <i class="fas fa-exclamation-triangle text-yellow-600 mt-1 mr-2"></i>
            <div>
                <h4 class="text-sm font-medium text-yellow-800">⚠️ Peringatan Edit Pinjaman</h4>
                <p class="text-sm text-yellow-700 mt-1">
                    Pinjaman ini sudah berjalan. Perubahan pada jumlah, lama angsuran, atau bunga akan mempengaruhi jadwal angsuran dan billing data.
                </p>
            </div>
        </div>
    `;

    // Insert warning sebelum form
    const form = document.getElementById('pinjamanForm');
    form.parentNode.insertBefore(warningDiv, form);
}

function deleteData() {
    if (!selectedRowData) {
        alert('Pilih data yang akan dihapus terlebih dahulu!');
        return;
    }

    // Cek apakah pinjaman sudah lunas
    if (selectedRowData.lunas === 'Lunas') {
        alert(
            '🔒 Pinjaman yang sudah lunas tidak dapat dihapus!\n\nData pinjaman lunas harus dipertahankan untuk keperluan audit dan laporan keuangan.'
        );
        return;
    }

    if (confirm(`Apakah Anda yakin ingin menghapus data pinjaman ID: ${selectedRowData.kode}?`)) {
        // Kirim request delete
        const deleteUrl = `/pinjaman/data_pinjaman/${selectedRowData.id}/delete`;
        fetch(deleteUrl, {
            method: 'POST',
            headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
            }
        })
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                // Cek content type
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
            return response.json();
                } else {
                    // Jika bukan JSON, coba parse sebagai text
                    return response.text().then(text => {
                        console.log('Response text:', text);
                        throw new Error('Server returned non-JSON response: ' + text.substring(0, 100));
                    });
                }
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.success) {
                    let message = 'Data berhasil dihapus';
                    if (data.details) {
                        message += '\n\nDetail penghapusan:';
                        if (data.details.tempo_deleted > 0) message +=
                            `\n- ${data.details.tempo_deleted} jadwal angsuran`;
                        if (data.details.billing_deleted > 0) message +=
                            `\n- ${data.details.billing_deleted} data billing`;
                        if (data.details.trans_kas_deleted > 0) message +=
                            `\n- ${data.details.trans_kas_deleted} transaksi kas`;
                        if (data.details.stok_updated) message += '\n- Stok barang dikembalikan';
                    }
                    alert(message);
                    location.reload();
            } else {
                    alert('Gagal menghapus data: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
                console.error('Error details:', error);
                console.error('Error message:', error.message);
                console.error('Error stack:', error.stack);
                alert('Terjadi kesalahan saat menghapus data: ' + error.message);
            });
    }
}

function uploadData() {
    // Implement upload functionality
    alert('Fitur upload akan diimplementasikan');
}

// Function untuk bulk operations (jika diperlukan)
function editSelected() {
    if (selectedRows.length === 0) {
        alert('Maaf, Data harus dipilih terlebih dahulu');
        return;
    }

    if (selectedRows.length > 1) {
        alert('Maaf, hanya bisa memilih satu data untuk diedit');
        return;
    }

    // Cari data berdasarkan ID yang dipilih
    const selectedId = selectedRows[0];
    const row = document.querySelector(`tr[data-id="${selectedId}"]`);
    if (row) {
        selectRow(row, selectedId);
        editData();
    }
}

function showDeleteConfirm() {
    if (selectedRows.length === 0) {
        alert('Maaf, Data harus dipilih terlebih dahulu');
        return;
    }

    const message =
        `Yakin ingin menghapus ${selectedRows.length} data yang dipilih? Data yang sudah dihapus tidak dapat dikembalikan.`;
    if (confirm(message)) {
        // Implement bulk delete jika diperlukan
        alert('Fitur bulk delete akan diimplementasikan');
    }
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

// Format number dengan pemisah ribuan
function formatNumber(input) {
    // Hapus semua karakter non-digit
    let value = input.value.replace(/\D/g, '');

    // Format dengan pemisah ribuan
    if (value) {
        value = parseInt(value).toLocaleString('id-ID');
    }

    input.value = value;
}

// Function untuk mengkonversi format number ke angka murni
function parseNumber(formattedNumber) {
    if (!formattedNumber) return 0;

    // Hapus semua karakter non-digit kecuali koma untuk desimal
    let cleanNumber = formattedNumber.toString().replace(/[^\d,]/g, '');

    // Ganti koma dengan titik untuk parsing desimal
    cleanNumber = cleanNumber.replace(',', '.');

    // Parse sebagai float untuk handle desimal, lalu convert ke int
    const parsed = parseFloat(cleanNumber) || 0;

    console.log('parseNumber input:', formattedNumber, 'output:', Math.round(parsed));
    return Math.round(parsed);
}

// Form submission untuk pinjaman - Handle CREATE dan UPDATE
document.getElementById('pinjamanForm').addEventListener('submit', function(e) {
    e.preventDefault();

    // Validasi sederhana seperti project lama
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);

    // Konversi jumlah dari format dengan pemisah ribuan ke angka murni
    if (data.jumlah) {
        data.jumlah = parseNumber(data.jumlah);
    }

    // Validasi jumlah > 0 (seperti project lama)
    if (!data.jumlah || data.jumlah <= 0) {
        alert('Jumlah pinjaman harus lebih dari 0');
                return;
            }

    // Tentukan method dan URL berdasarkan mode (create/edit)
    const isEdit = this.dataset.mode === 'edit';
    const method = isEdit ? 'PUT' : 'POST';
    const url = isEdit ? `/pinjaman/data_pinjaman/${this.dataset.pinjamanId}` : this.action;

    // Tampilkan konfirmasi untuk edit dengan perubahan kritis
    if (isEdit) {
        const criticalFields = ['jumlah', 'lama_angsuran', 'jasa', 'tgl_pinjam'];
        const hasCriticalChanges = criticalFields.some(field => {
            const originalValue = this.dataset[`original_${field}`];
            return originalValue && originalValue !== data[field];
        });

        if (hasCriticalChanges) {
            if (!confirm(
                    '⚠️ Anda mengubah data kritis (jumlah, lama angsuran, jasa, atau tanggal).\n\nPerubahan ini akan mempengaruhi jadwal angsuran dan billing data.\n\nApakah Anda yakin ingin melanjutkan?'
                )) {
                return;
            }
        }
    }

    // Submit data dengan fetch API
    fetch(url, {
            method: method,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const message = isEdit ? 'Data berhasil diupdate' : 'Data berhasil disimpan';
                if (data.critical_changes && Object.keys(data.critical_changes).length > 0) {
                    alert(message +
                        '\n\n⚠️ Data tempo dan billing telah di-regenerate karena ada perubahan kritis.'
                    );
                } else {
                    alert(message);
                }
                closeModal();
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

// Auto-calculate installment amount
function calculateInstallmentAmount() {
    console.log('Auto-calculating installment amount...');

    // Ambil nilai dari input field
    const jumlahInput = document.getElementById('jumlah');
    const lamaInput = document.getElementById('lama_angsuran');

    if (!jumlahInput || !lamaInput) {
        console.log('Input fields not found');
        return;
    }

    // Parse nilai dengan benar
    const jumlahPinjaman = parseNumber(jumlahInput.value);
    const lamaAngsuran = parseInt(lamaInput.value);

    console.log('Raw Jumlah Input:', jumlahInput.value);
    console.log('Parsed Jumlah Pinjaman:', jumlahPinjaman);
    console.log('Raw Lama Input:', lamaInput.value);
    console.log('Parsed Lama Angsuran:', lamaAngsuran);

    if (lamaAngsuran && jumlahPinjaman && lamaAngsuran > 0) {
        const jumlahAngsuran = jumlahPinjaman / lamaAngsuran;
        const roundedAngsuran = Math.round(jumlahAngsuran);
        const formattedAngsuran = roundedAngsuran.toLocaleString('id-ID');

        const angsuranField = document.getElementById('jumlah_angsuran');
        if (angsuranField) {
            angsuranField.value = formattedAngsuran;
            console.log('✅ Field updated successfully!');
            console.log('Calculation: ', jumlahPinjaman, ' / ', lamaAngsuran, ' = ', jumlahAngsuran);
            console.log('Rounded: ', roundedAngsuran);
            console.log('Formatted: ', formattedAngsuran);
            console.log('Field value after update:', angsuranField.value);
        } else {
            console.log('❌ Angsuran field not found!');
        }
    } else {
        const angsuranField = document.getElementById('jumlah_angsuran');
        if (angsuranField) {
            angsuranField.value = '0';
            console.log('Reset installment amount to 0 - Invalid input');
        }
    }
}

// Event listener untuk auto-calculation
document.addEventListener('input', function(e) {
    if (e.target.name === 'jumlah' || e.target.name === 'lama_angsuran') {
        console.log('Input event triggered for:', e.target.name);
        calculateInstallmentAmount();
    }
});

// Event listener untuk change event (untuk dropdown dan input number)
document.addEventListener('change', function(e) {
    if (e.target.name === 'jumlah' || e.target.name === 'lama_angsuran') {
        console.log('Change event triggered for:', e.target.name);
        calculateInstallmentAmount();
    }
});

// Event listener khusus untuk field jumlah dan lama_angsuran
document.addEventListener('DOMContentLoaded', function() {
    const jumlahField = document.getElementById('jumlah');
    const lamaField = document.getElementById('lama_angsuran');

    if (jumlahField) {
        jumlahField.addEventListener('input', function() {
            console.log('Jumlah field input event');
            calculateInstallmentAmount();
        });
        jumlahField.addEventListener('change', function() {
            console.log('Jumlah field change event');
            calculateInstallmentAmount();
        });
    }

    if (lamaField) {
        lamaField.addEventListener('input', function() {
            console.log('Lama field input event');
            calculateInstallmentAmount();
        });
        lamaField.addEventListener('change', function() {
            console.log('Lama field change event');
            calculateInstallmentAmount();
        });
    }

    // Initialize button states
    updateButtonStates();
});

// Function untuk test perhitungan manual (untuk debugging)
function testCalculation() {
    console.log('=== TEST CALCULATION ===');
    const jumlahInput = document.getElementById('jumlah');
    const lamaInput = document.getElementById('lama_angsuran');

    if (jumlahInput && lamaInput) {
        console.log('Jumlah Input Value:', jumlahInput.value);
        console.log('Lama Input Value:', lamaInput.value);

        const parsedJumlah = parseNumber(jumlahInput.value);
        const parsedLama = parseInt(lamaInput.value);

        console.log('Parsed Jumlah:', parsedJumlah);
        console.log('Parsed Lama:', parsedLama);

        if (parsedLama && parsedJumlah && parsedLama > 0) {
            const result = parsedJumlah / parsedLama;
            console.log('Calculation Result:', result);
            console.log('Rounded Result:', Math.round(result));
            console.log('Formatted Result:', Math.round(result).toLocaleString('id-ID'));
        }
    }
    console.log('=== END TEST ===');
}

// Function untuk force update jumlah angsuran
function forceUpdateAngsuran() {
    console.log('Force updating jumlah angsuran...');
    calculateInstallmentAmount();
}

// Function untuk manual update dengan nilai tertentu
function manualUpdateAngsuran(jumlah, lama) {
    console.log('Manual update - Jumlah:', jumlah, 'Lama:', lama);

    if (lama && jumlah && lama > 0) {
        const result = jumlah / lama;
        const rounded = Math.round(result);
        const formatted = rounded.toLocaleString('id-ID');

        const angsuranField = document.getElementById('jumlah_angsuran');
        if (angsuranField) {
            angsuranField.value = formatted;
            console.log('Manual update result:', formatted);
        } else {
            console.log('Angsuran field not found!');
        }
    }
}

// Expose functions to global scope for debugging
window.testCalculation = testCalculation;
window.forceUpdateAngsuran = forceUpdateAngsuran;
window.manualUpdateAngsuran = manualUpdateAngsuran;
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