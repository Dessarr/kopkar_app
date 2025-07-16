@extends('layouts.app')

@section('title', 'Toserda Lain-lain')
@section('sub-title', 'Upload & Laporan Toserda')

@section('content')
<div class="container">
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

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Upload File Excel -->
        <div class="md:col-span-1 bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-lg font-semibold mb-4">Upload Data Toserda</h2>
            <form action="{{ route('toserda.upload.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label for="file" class="block text-sm font-medium text-gray-700">File Excel</label>
                    <input type="file" name="file" id="file" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#14AE5C] focus:ring focus:ring-[#14AE5C] focus:ring-opacity-50">
                    <p class="text-xs text-gray-500 mt-1">Format: .xlsx, .xls</p>
                </div>

                <div>
                    <label for="bulan" class="block text-sm font-medium text-gray-700">Bulan</label>
                    <select name="bulan" id="bulan" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#14AE5C] focus:ring focus:ring-[#14AE5C] focus:ring-opacity-50">
                        @foreach($bulanList as $key => $bulan)
                            <option value="{{ $key }}" {{ date('m') == $key ? 'selected' : '' }}>{{ $bulan }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="tahun" class="block text-sm font-medium text-gray-700">Tahun</label>
                    <input type="number" name="tahun" id="tahun" value="{{ date('Y') }}" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#14AE5C] focus:ring focus:ring-[#14AE5C] focus:ring-opacity-50">
                </div>

                <div>
                    <label for="kas_id" class="block text-sm font-medium text-gray-700">Kas</label>
                    <select name="kas_id" id="kas_id" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#14AE5C] focus:ring focus:ring-[#14AE5C] focus:ring-opacity-50">
                        <option value="">Pilih Kas</option>
                        @foreach($kas as $item)
                            <option value="{{ $item->id }}">{{ $item->nama_kas ?? $item->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="pt-2">
                    <button type="submit"
                        class="w-full px-4 py-2 bg-[#14AE5C] text-white rounded-md hover:bg-[#14AE5C]/80 focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:ring-opacity-50">
                        Upload Data
                    </button>
                </div>
            </form>
        </div>

        <!-- Proses Billing Bulanan -->
        <div class="md:col-span-1 bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-lg font-semibold mb-4">Proses Billing Bulanan</h2>
            <form action="{{ route('toserda.billing.process') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="billing_bulan" class="block text-sm font-medium text-gray-700">Bulan</label>
                    <select name="bulan" id="billing_bulan" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#14AE5C] focus:ring focus:ring-[#14AE5C] focus:ring-opacity-50">
                        @foreach($bulanList as $key => $bulan)
                            <option value="{{ $key }}" {{ date('m') == $key ? 'selected' : '' }}>{{ $bulan }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="billing_tahun" class="block text-sm font-medium text-gray-700">Tahun</label>
                    <input type="number" name="tahun" id="billing_tahun" value="{{ date('Y') }}" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#14AE5C] focus:ring focus:ring-[#14AE5C] focus:ring-opacity-50">
                </div>

                <div class="pt-2">
                    <button type="submit"
                        class="w-full px-4 py-2 bg-amber-500 text-white rounded-md hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-opacity-50">
                        Proses Billing
                    </button>
                </div>
                <p class="text-xs text-gray-500 mt-2 text-center">
                    Proses ini akan menghitung total belanja per anggota dan memperbarui data billing
                </p>
            </form>
        </div>

        <!-- Contoh Template Excel -->
        <div class="md:col-span-1 bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-lg font-semibold mb-4">Panduan Upload</h2>
            <div class="space-y-4">
                <p class="text-sm text-gray-700">Format kolom file Excel yang dibutuhkan:</p>
                <ul class="list-disc list-inside text-sm text-gray-600 space-y-1">
                    <li><strong>tanggal</strong> - Format tanggal</li>
                    <li><strong>no_ktp</strong> - Nomor KTP anggota</li>
                    <li><strong>nama</strong> - Nama anggota (opsional jika ada no_ktp)</li>
                    <li><strong>jumlah</strong> - Nominal transaksi</li>
                    <li><strong>keterangan</strong> - Keterangan transaksi</li>
                    <li><strong>dk</strong> - D untuk Debit, K untuk Kredit</li>
                    <li><strong>jns_trans</strong> - Jenis transaksi (sesuai tabel jns_akun)</li>
                </ul>
                <div class="pt-4">
                    <a href="{{ route('toserda.template.download') }}" 
                        class="inline-block px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                        Download Template Excel
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Transaksi -->
    <div class="mt-8 bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-lg font-semibold mb-4">Data Transaksi Toserda</h2>
        
        <!-- Filter -->
        <div class="mb-6 p-4 border rounded-lg bg-gray-50">
            <form action="{{ route('toserda.lain-lain') }}" method="GET" class="flex flex-wrap items-end gap-4">
                <div class="w-full sm:w-auto">
                    <label for="filter_bulan" class="block text-sm font-medium text-gray-700">Bulan</label>
                    <select name="bulan" id="filter_bulan"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#14AE5C] focus:ring focus:ring-[#14AE5C] focus:ring-opacity-50">
                        <option value="">Semua</option>
                        @foreach($bulanList as $key => $bulan)
                            <option value="{{ $key }}" {{ request('bulan') == $key ? 'selected' : '' }}>{{ $bulan }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="w-full sm:w-auto">
                    <label for="filter_tahun" class="block text-sm font-medium text-gray-700">Tahun</label>
                    <input type="number" name="tahun" id="filter_tahun" value="{{ request('tahun', date('Y')) }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#14AE5C] focus:ring focus:ring-[#14AE5C] focus:ring-opacity-50">
                </div>

                <div class="w-full sm:w-auto">
                    <label for="search" class="block text-sm font-medium text-gray-700">Cari Anggota</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Nama atau No KTP"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#14AE5C] focus:ring focus:ring-[#14AE5C] focus:ring-opacity-50">
                </div>

                <div class="w-full sm:w-auto">
                    <label for="billing_status" class="block text-sm font-medium text-gray-700">Status Billing</label>
                    <select name="billing_status" id="billing_status"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#14AE5C] focus:ring focus:ring-[#14AE5C] focus:ring-opacity-50">
                        <option value="">Semua</option>
                        <option value="billed" {{ request('billing_status') == 'billed' ? 'selected' : '' }}>Sudah Billing</option>
                        <option value="unbilled" {{ request('billing_status') == 'unbilled' ? 'selected' : '' }}>Belum Billing</option>
                    </select>
                </div>

                <div class="flex-shrink-0">
                    <button type="submit"
                        class="px-4 py-2 bg-[#14AE5C] text-white rounded-md hover:bg-[#14AE5C]/80 focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:ring-opacity-50">
                        Filter
                    </button>
                    <a href="{{ route('toserda.lain-lain') }}"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50 ml-2">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Tabel Data -->
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200">
                <thead>
                    <tr>
                        <th class="px-4 py-2 border-b text-left">Tanggal</th>
                        <th class="px-4 py-2 border-b text-left">No KTP</th>
                        <th class="px-4 py-2 border-b text-left">Nama Anggota</th>
                        <th class="px-4 py-2 border-b text-left">Jumlah</th>
                        <th class="px-4 py-2 border-b text-left">Keterangan</th>
                        <th class="px-4 py-2 border-b text-left">Debit/Kredit</th>
                        <th class="px-4 py-2 border-b text-left">Kas</th>
                        <th class="px-4 py-2 border-b text-left">Status Billing</th>
                        <th class="px-4 py-2 border-b text-left">Status Pembayaran</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transaksi as $tr)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 border-b">{{ $tr->tgl_transaksi->format('d/m/Y') }}</td>
                        <td class="px-4 py-2 border-b">{{ $tr->no_ktp }}</td>
                        <td class="px-4 py-2 border-b">{{ $tr->anggota->nama ?? 'N/A' }}</td>
                        <td class="px-4 py-2 border-b">{{ number_format($tr->jumlah, 0, ',', '.') }}</td>
                        <td class="px-4 py-2 border-b">{{ $tr->keterangan }}</td>
                        <td class="px-4 py-2 border-b">{{ $tr->dk == 'D' ? 'Debit' : 'Kredit' }}</td>
                        <td class="px-4 py-2 border-b">{{ $tr->kas->nama_kas ?? $tr->kas->nama ?? 'N/A' }}</td>
                        <td class="px-4 py-2 border-b">
                            @php
                                $billing = \App\Models\billing::where('id_transaksi', $tr->id)
                                    ->where('jns_transaksi', 'toserda')
                                    ->first();
                            @endphp
                            
                            @if($billing)
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Sudah Billing</span>
                            @else
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">Belum Billing</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 border-b">
                            @if($billing && $billing->status_bayar == 'sudah')
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Sudah Bayar</span>
                            @elseif($billing)
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">Belum Bayar</span>
                            @else
                                <span class="px-2 py-1 bg-gray-100 text-gray-500 rounded-full text-xs">Belum Ditagih</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-4 text-center text-gray-500">Belum ada data transaksi</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $transaksi->links() }}
        </div>
    </div>
</div>
@endsection 