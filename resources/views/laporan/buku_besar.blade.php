@extends('layouts.app')

@section('title', 'Laporan Buku Besar')
@section('sub-title', 'Laporan')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <!-- Filter Section -->
    <div class="mb-6">
        <form method="GET" action="{{ route('laporan.buku_besar') }}" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label for="periode" class="block text-sm font-medium text-gray-700 mb-2">Periode</label>
                <input type="month" id="periode" name="periode" value="{{ $periode }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent">
            </div>
            <div class="flex-1 min-w-[200px]">
                <label for="kas_id" class="block text-sm font-medium text-gray-700 mb-2">Kas</label>
                <select id="kas_id" name="kas_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent">
                    <option value="">-- Pilih Kas --</option>
                    @foreach($kasList as $kas)
                        <option value="{{ $kas->id }}" {{ (optional($selectedKas)->id == $kas->id) ? 'selected' : '' }}>{{ $kas->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-[#14AE5C] text-white rounded-md hover:bg-[#11994F] transition-colors duration-200">
                    <i class="fas fa-search mr-2"></i>Filter
                </button>
                <a href="{{ route('laporan.buku_besar') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors duration-200">
                    <i class="fas fa-refresh mr-2"></i>Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Export Buttons -->
    @if($selectedKas)
    <div class="mb-6 flex flex-wrap gap-2">
        <a href="{{ route('laporan.buku_besar.export.pdf', ['periode' => $periode, 'kas_id' => $selectedKas->id]) }}" 
           class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors duration-200">
            <i class="fas fa-file-pdf mr-2"></i>Export PDF
        </a>
        <a href="{{ route('laporan.buku_besar.export.excel', ['periode' => $periode, 'kas_id' => $selectedKas->id]) }}" 
           class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors duration-200">
            <i class="fas fa-file-excel mr-2"></i>Export Excel
        </a>
        <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-200">
            <i class="fas fa-print mr-2"></i>Print
        </button>
    </div>
    @endif

    <!-- Table Section -->
    @if($selectedKas && count($data))
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Jenis Transaksi</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Debet</th>
                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Kredit</th>
                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Saldo</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($data as $row)
                <tr>
                    <td class="px-4 py-2">{{ $row['no'] }}</td>
                    <td class="px-4 py-2">{{ \Carbon\Carbon::parse($row['tanggal'])->format('d/m/Y') }}</td>
                    <td class="px-4 py-2">{{ $row['jenis_transaksi'] }}</td>
                    <td class="px-4 py-2">{{ $row['keterangan'] }}</td>
                    <td class="px-4 py-2 text-right">{{ number_format($row['debet']) }}</td>
                    <td class="px-4 py-2 text-right">{{ number_format($row['kredit']) }}</td>
                    <td class="px-4 py-2 text-right font-bold">{{ number_format($row['saldo']) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @elseif($selectedKas)
        <div class="text-center text-gray-500 py-8">Tidak ada data transaksi untuk kas dan periode ini.</div>
    @endif
</div>
@endsection 