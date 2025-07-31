@extends('layouts.app')

@section('title', 'Laporan Target & Realisasi')
@section('sub-title', 'Laporan')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <!-- Filter Section -->
    <div class="mb-6">
        <form method="GET" action="{{ route('laporan.target_realisasi') }}" class="flex flex-wrap gap-4 items-end">
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
                <a href="{{ route('laporan.target_realisasi') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors duration-200">
                    <i class="fas fa-refresh mr-2"></i>Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Export Buttons -->
    <div class="mb-6 flex flex-wrap gap-2">
        <a href="{{ route('laporan.target_realisasi.export.pdf', ['tgl_dari' => $tgl_dari, 'tgl_samp' => $tgl_samp]) }}" 
           class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors duration-200">
            <i class="fas fa-file-pdf mr-2"></i>Export PDF
        </a>
        <a href="{{ route('laporan.target_realisasi.export.excel', ['tgl_dari' => $tgl_dari, 'tgl_samp' => $tgl_samp]) }}" 
           class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors duration-200">
            <i class="fas fa-file-excel mr-2"></i>Export Excel
        </a>
        <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-200">
            <i class="fas fa-print mr-2"></i>Print
        </button>
    </div>

    <!-- Table Section -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-xs">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-2 py-2">No</th>
                    <th class="px-2 py-2">Tanggal Pinjam</th>
                    <th class="px-2 py-2">Nama</th>
                    <th class="px-2 py-2">ID</th>
                    <th class="px-2 py-2 text-right">Pinjaman</th>
                    <th class="px-2 py-2 text-right">Saldo Pinjaman</th>
                    <th class="px-2 py-2">JW</th>
                    <th class="px-2 py-2">%</th>
                    <th class="px-2 py-2 text-right">Pokok</th>
                    <th class="px-2 py-2 text-right">Bunga</th>
                    <th class="px-2 py-2 text-right">Admin</th>
                    <th class="px-2 py-2">Angsuran Ke</th>
                    <th class="px-2 py-2 text-right">Pokok</th>
                    <th class="px-2 py-2 text-right">Bunga</th>
                    <th class="px-2 py-2 text-right">Denda</th>
                    <th class="px-2 py-2 text-right">Jumlah</th>
                    <th class="px-2 py-2 text-right">Sisa Tagihan</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($data as $row)
                <tr>
                    <td class="px-2 py-2">{{ $row['no'] }}</td>
                    <td class="px-2 py-2">{{ \Carbon\Carbon::parse($row['tgl_pinjam'])->format('d/m/Y') }}</td>
                    <td class="px-2 py-2">{{ $row['nama'] }}</td>
                    <td class="px-2 py-2">{{ $row['id'] }}</td>
                    <td class="px-2 py-2 text-right">{{ number_format($row['jumlah']) }}</td>
                    <td class="px-2 py-2 text-right">{{ number_format($row['sisa_pokok']) }}</td>
                    <td class="px-2 py-2">{{ $row['lama_angsuran'] }}</td>
                    <td class="px-2 py-2">{{ $row['bunga'] }}</td>
                    <td class="px-2 py-2 text-right">{{ number_format($row['pokok_angsuran']) }}</td>
                    <td class="px-2 py-2 text-right">{{ number_format($row['pokok_bunga']) }}</td>
                    <td class="px-2 py-2 text-right">{{ number_format($row['biaya_adm']) }}</td>
                    <td class="px-2 py-2">{{ $row['bln_sudah_angsur'] }}</td>
                    <td class="px-2 py-2 text-right">{{ number_format($row['total_bayar']) }}</td>
                    <td class="px-2 py-2 text-right">{{ number_format($row['bunga_ags']) }}</td>
                    <td class="px-2 py-2 text-right">{{ number_format($row['denda_rp']) }}</td>
                    <td class="px-2 py-2 text-right">{{ number_format($row['total_bayar'] + $row['bunga_ags'] + $row['denda_rp']) }}</td>
                    <td class="px-2 py-2 text-right">{{ number_format($row['sisa_tagihan']) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection 