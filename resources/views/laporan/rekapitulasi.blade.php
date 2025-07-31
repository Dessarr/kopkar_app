@extends('layouts.app')

@section('title', 'Laporan Rekapitulasi')
@section('sub-title', 'Laporan')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <!-- Filter Section -->
    <div class="mb-6">
        <form method="GET" action="{{ route('laporan.rekapitulasi') }}" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label for="periode" class="block text-sm font-medium text-gray-700 mb-2">Periode</label>
                <input type="month" id="periode" name="periode" value="{{ $periode }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-[#14AE5C] text-white rounded-md hover:bg-[#11994F] transition-colors duration-200">
                    <i class="fas fa-search mr-2"></i>Filter
                </button>
                <a href="{{ route('laporan.rekapitulasi') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors duration-200">
                    <i class="fas fa-refresh mr-2"></i>Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Export Buttons -->
    <div class="mb-6 flex flex-wrap gap-2">
        <a href="{{ route('laporan.rekapitulasi.export.pdf', ['periode' => $periode]) }}" 
           class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors duration-200">
            <i class="fas fa-file-pdf mr-2"></i>Export PDF
        </a>
        <a href="{{ route('laporan.rekapitulasi.export.excel', ['periode' => $periode]) }}" 
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
                    <th class="px-2 py-2">Tanggal</th>
                    <th class="px-2 py-2">Tagihan Hari Ini</th>
                    <th class="px-2 py-2 text-right">Target Pokok</th>
                    <th class="px-2 py-2 text-right">Target Bunga</th>
                    <th class="px-2 py-2">Tagihan Masuk</th>
                    <th class="px-2 py-2 text-right">Realisasi Pokok</th>
                    <th class="px-2 py-2 text-right">Realisasi Bunga</th>
                    <th class="px-2 py-2">Tagihan Bermasalah</th>
                    <th class="px-2 py-2 text-right">Tidak Bayar Pokok</th>
                    <th class="px-2 py-2 text-right">Tidak Bayar Bunga</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($data as $row)
                <tr>
                    <td class="px-2 py-2">{{ $row['no'] }}</td>
                    <td class="px-2 py-2">{{ \Carbon\Carbon::parse($row['tanggal'])->format('d/m/Y') }}</td>
                    <td class="px-2 py-2">{{ $row['jml_tagihan'] }}</td>
                    <td class="px-2 py-2 text-right">{{ number_format($row['target_pokok']) }}</td>
                    <td class="px-2 py-2 text-right">{{ number_format($row['target_bunga']) }}</td>
                    <td class="px-2 py-2">{{ $row['tagihan_masuk'] }}</td>
                    <td class="px-2 py-2 text-right">{{ number_format($row['realisasi_pokok']) }}</td>
                    <td class="px-2 py-2 text-right">{{ number_format($row['realisasi_bunga']) }}</td>
                    <td class="px-2 py-2">{{ $row['tagihan_bermasalah'] }}</td>
                    <td class="px-2 py-2 text-right">{{ number_format($row['tidak_bayar_pokok']) }}</td>
                    <td class="px-2 py-2 text-right">{{ number_format($row['tidak_bayar_bunga']) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection 