@extends('layouts.app')

@section('title', 'Laporan Kas Pinjaman')
@section('sub-title', 'Laporan')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <!-- Filter Section -->
    <div class="mb-6">
        <form method="GET" action="{{ route('laporan.kas_pinjaman') }}" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label for="tgl_dari" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Dari</label>
                <input type="date" id="tgl_dari" name="tgl_dari" value="{{ $tgl_dari }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent">
            </div>
            <div class="flex-1 min-w-[200px]">
                <label for="tgl_samp" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Sampai</label>
                <input type="date" id="tgl_samp" name="tgl_samp" value="{{ $tgl_samp }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-[#14AE5C] text-white rounded-md hover:bg-[#11994F] transition-colors duration-200">
                    <i class="fas fa-search mr-2"></i>Filter
                </button>
                <a href="{{ route('laporan.kas_pinjaman') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors duration-200">
                    <i class="fas fa-refresh mr-2"></i>Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Export Buttons -->
    <div class="mb-6 flex flex-wrap gap-2">
        <a href="{{ route('laporan.kas_pinjaman.export.pdf', ['tgl_dari' => $tgl_dari, 'tgl_samp' => $tgl_samp]) }}" 
           class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors duration-200">
            <i class="fas fa-file-pdf mr-2"></i>Export PDF
        </a>
        <a href="{{ route('laporan.kas_pinjaman.export.excel', ['tgl_dari' => $tgl_dari, 'tgl_samp' => $tgl_samp]) }}" 
           class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors duration-200">
            <i class="fas fa-file-excel mr-2"></i>Export Excel
        </a>
        <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-200">
            <i class="fas fa-print mr-2"></i>Print
        </button>
    </div>

    <!-- Table Section -->
    <div class="overflow-x-auto mb-8">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <tr>
                    <td class="px-4 py-2">1</td>
                    <td class="px-4 py-2">Pokok Pinjaman</td>
                    <td class="px-4 py-2 text-right">{{ number_format($data['jml_pinjaman']) }}</td>
                </tr>
                <tr>
                    <td class="px-4 py-2">2</td>
                    <td class="px-4 py-2">Tagihan Pinjaman</td>
                    <td class="px-4 py-2 text-right">{{ number_format($data['jml_pinjaman']) }}</td>
                </tr>
                <tr>
                    <td class="px-4 py-2">3</td>
                    <td class="px-4 py-2">Tagihan Denda</td>
                    <td class="px-4 py-2 text-right">{{ number_format($data['jml_denda']) }}</td>
                </tr>
                <tr class="bg-gray-100 font-bold">
                    <td class="px-4 py-2"></td>
                    <td class="px-4 py-2">Jumlah Tagihan + Denda</td>
                    <td class="px-4 py-2 text-right">{{ number_format($data['tot_tagihan']) }}</td>
                </tr>
                <tr>
                    <td class="px-4 py-2">4</td>
                    <td class="px-4 py-2">Tagihan Sudah Dibayar</td>
                    <td class="px-4 py-2 text-right">{{ number_format($data['jml_angsuran']) }}</td>
                </tr>
                <tr class="bg-green-100 font-bold">
                    <td class="px-4 py-2">5</td>
                    <td class="px-4 py-2">Sisa Tagihan</td>
                    <td class="px-4 py-2 text-right">{{ number_format($data['sisa_tagihan']) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-white bg-opacity-20">
                    <i class="fas fa-users text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm opacity-90">Peminjam Aktif</p>
                    <p class="text-2xl font-bold">{{ number_format($data['peminjam_aktif']) }}</p>
                </div>
            </div>
        </div>
        <div class="bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-white bg-opacity-20">
                    <i class="fas fa-check-circle text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm opacity-90">Peminjam Lunas</p>
                    <p class="text-2xl font-bold">{{ number_format($data['peminjam_lunas']) }}</p>
                </div>
            </div>
        </div>
        <div class="bg-gradient-to-r from-red-500 to-red-600 text-white rounded-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-white bg-opacity-20">
                    <i class="fas fa-times-circle text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm opacity-90">Peminjam Belum Lunas</p>
                    <p class="text-2xl font-bold">{{ number_format($data['peminjam_belum']) }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 