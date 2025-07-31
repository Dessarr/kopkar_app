@extends('layouts.app')

@section('title', 'Laporan Angkutan Karyawan')
@section('sub-title', 'Laporan')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <!-- Filter Section -->
    <div class="mb-6">
        <form method="GET" action="{{ route('laporan.angkutan.karyawan') }}" class="flex flex-wrap gap-4 items-end">
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
                <a href="{{ route('laporan.angkutan.karyawan') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors duration-200">
                    <i class="fas fa-refresh mr-2"></i>Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Export Buttons -->
    <div class="mb-6 flex flex-wrap gap-2">
        <a href="{{ route('laporan.angkutan.karyawan.export.pdf') }}?tgl_dari={{ $tgl_dari }}&tgl_samp={{ $tgl_samp }}" 
           class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors duration-200">
            <i class="fas fa-file-pdf mr-2"></i>Export PDF
        </a>
        <a href="{{ route('laporan.angkutan.karyawan.export.excel') }}?tgl_dari={{ $tgl_dari }}&tgl_samp={{ $tgl_samp }}" 
           class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors duration-200">
            <i class="fas fa-file-excel mr-2"></i>Export Excel
        </a>
        <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-200">
            <i class="fas fa-print mr-2"></i>Print
        </button>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-white bg-opacity-20">
                    <i class="fas fa-bus text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm opacity-90">Total Pendapatan Bus</p>
                    <p class="text-2xl font-bold">Rp {{ number_format($jmlBus->jml_total ?? 0) }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-white bg-opacity-20">
                    <i class="fas fa-tools text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm opacity-90">Total Biaya Operasional</p>
                    <p class="text-2xl font-bold">Rp {{ number_format($jmlOperasional->jml_total ?? 0) }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-white bg-opacity-20">
                    <i class="fas fa-calculator text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm opacity-90">Total Biaya Admin</p>
                    <p class="text-2xl font-bold">Rp {{ number_format($jmlAdmin->jml_total ?? 0) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Penghasilan Jasa Sewa Bus Section -->
    <div class="mb-8">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-bus mr-2 text-[#14AE5C]"></i>
            Penghasilan Jasa Sewa Bus
        </h3>
        
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200 rounded-lg overflow-hidden">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No Polisi</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Jan</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Feb</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Mar</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Apr</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">May</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Jun</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Jul</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aug</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Sep</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Oct</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Nov</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Dec</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($dataBus as $bus)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $bus->no_polisi }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($bus->Jan ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($bus->Feb ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($bus->Mar ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($bus->Apr ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($bus->May ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($bus->Jun ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($bus->Jul ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($bus->Aug ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($bus->Sep ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($bus->Oct ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($bus->Nov ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($bus->Dec ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right">{{ number_format($bus->TOTAL ?? 0) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="14" class="px-4 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-2"></i>
                            <p>Tidak ada data bus untuk periode yang dipilih</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($dataBus->count() > 0)
                <tfoot class="bg-gray-50">
                    <tr class="font-semibold">
                        <td class="px-4 py-3 text-sm text-gray-900">JUMLAH</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($jmlBusTahun->jml_total_jan ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($jmlBusTahun->jml_total_feb ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($jmlBusTahun->jml_total_mar ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($jmlBusTahun->jml_total_apr ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($jmlBusTahun->jml_total_may ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($jmlBusTahun->jml_total_jun ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($jmlBusTahun->jml_total_jul ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($jmlBusTahun->jml_total_aug ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($jmlBusTahun->jml_total_sep ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($jmlBusTahun->jml_total_oct ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($jmlBusTahun->jml_total_nov ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($jmlBusTahun->jml_total_dec ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($jmlBus->jml_total ?? 0) }}</td>
                    </tr>
                    <tr class="bg-yellow-50">
                        <td class="px-4 py-3 text-sm text-gray-900">Pajak (2%)</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($jmlBusTahunPajak->jml_total_jan_pajak ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($jmlBusTahunPajak->jml_total_feb_pajak ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($jmlBusTahunPajak->jml_total_mar_pajak ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($jmlBusTahunPajak->jml_total_apr_pajak ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($jmlBusTahunPajak->jml_total_may_pajak ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($jmlBusTahunPajak->jml_total_jun_pajak ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($jmlBusTahunPajak->jml_total_jul_pajak ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($jmlBusTahunPajak->jml_total_aug_pajak ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($jmlBusTahunPajak->jml_total_sep_pajak ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($jmlBusTahunPajak->jml_total_oct_pajak ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($jmlBusTahunPajak->jml_total_nov_pajak ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($jmlBusTahunPajak->jml_total_dec_pajak ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format(($jmlBus->jml_total ?? 0) * 0.02) }}</td>
                    </tr>
                    <tr class="bg-green-50">
                        <td class="px-4 py-3 text-sm font-semibold text-gray-900">Setelah Pajak</td>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right">{{ number_format(($jmlBusTahun->jml_total_jan ?? 0) - ($jmlBusTahunPajak->jml_total_jan_pajak ?? 0)) }}</td>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right">{{ number_format(($jmlBusTahun->jml_total_feb ?? 0) - ($jmlBusTahunPajak->jml_total_feb_pajak ?? 0)) }}</td>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right">{{ number_format(($jmlBusTahun->jml_total_mar ?? 0) - ($jmlBusTahunPajak->jml_total_mar_pajak ?? 0)) }}</td>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right">{{ number_format(($jmlBusTahun->jml_total_apr ?? 0) - ($jmlBusTahunPajak->jml_total_apr_pajak ?? 0)) }}</td>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right">{{ number_format(($jmlBusTahun->jml_total_may ?? 0) - ($jmlBusTahunPajak->jml_total_may_pajak ?? 0)) }}</td>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right">{{ number_format(($jmlBusTahun->jml_total_jun ?? 0) - ($jmlBusTahunPajak->jml_total_jun_pajak ?? 0)) }}</td>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right">{{ number_format(($jmlBusTahun->jml_total_jul ?? 0) - ($jmlBusTahunPajak->jml_total_jul_pajak ?? 0)) }}</td>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right">{{ number_format(($jmlBusTahun->jml_total_aug ?? 0) - ($jmlBusTahunPajak->jml_total_aug_pajak ?? 0)) }}</td>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right">{{ number_format(($jmlBusTahun->jml_total_sep ?? 0) - ($jmlBusTahunPajak->jml_total_sep_pajak ?? 0)) }}</td>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right">{{ number_format(($jmlBusTahun->jml_total_oct ?? 0) - ($jmlBusTahunPajak->jml_total_oct_pajak ?? 0)) }}</td>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right">{{ number_format(($jmlBusTahun->jml_total_nov ?? 0) - ($jmlBusTahunPajak->jml_total_nov_pajak ?? 0)) }}</td>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right">{{ number_format(($jmlBusTahun->jml_total_dec ?? 0) - ($jmlBusTahunPajak->jml_total_dec_pajak ?? 0)) }}</td>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right">{{ number_format(($jmlBus->jml_total ?? 0) - (($jmlBus->jml_total ?? 0) * 0.02)) }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    <!-- Biaya Operasional Section -->
    @if($dataOperasional->count() > 0)
    <div class="mb-8">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-tools mr-2 text-[#14AE5C]"></i>
            Biaya Operasional
        </h3>
        
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200 rounded-lg overflow-hidden">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Biaya</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Jan</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Feb</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Mar</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Apr</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">May</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Jun</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Jul</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aug</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Sep</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Oct</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Nov</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Dec</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($dataOperasional as $operasional)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">-</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($operasional->Jan ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($operasional->Feb ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($operasional->Mar ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($operasional->Apr ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($operasional->May ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($operasional->Jun ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($operasional->Jul ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($operasional->Aug ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($operasional->Sep ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($operasional->Oct ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($operasional->Nov ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($operasional->Dec ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right">{{ number_format($operasional->TOTAL ?? 0) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr class="font-semibold">
                        <td class="px-4 py-3 text-sm text-gray-900">JUMLAH</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($jmlOperasionalTahun->jml_total_jan ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($jmlOperasionalTahun->jml_total_feb ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($jmlOperasionalTahun->jml_total_mar ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($jmlOperasionalTahun->jml_total_apr ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($jmlOperasionalTahun->jml_total_may ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($jmlOperasionalTahun->jml_total_jun ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($jmlOperasionalTahun->jml_total_jul ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($jmlOperasionalTahun->jml_total_aug ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($jmlOperasionalTahun->jml_total_sep ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($jmlOperasionalTahun->jml_total_oct ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($jmlOperasionalTahun->jml_total_nov ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($jmlOperasionalTahun->jml_total_dec ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($jmlOperasional->jml_total ?? 0) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @endif

    <!-- Biaya Admin Section -->
    @if($dataAdmin->count() > 0)
    <div class="mb-8">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-calculator mr-2 text-[#14AE5C]"></i>
            Biaya Admin
        </h3>
        
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200 rounded-lg overflow-hidden">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Biaya</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Jan</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Feb</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Mar</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Apr</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">May</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Jun</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Jul</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aug</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Sep</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Oct</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Nov</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Dec</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($dataAdmin as $admin)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">-</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($admin->Jan ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($admin->Feb ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($admin->Mar ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($admin->Apr ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($admin->May ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($admin->Jun ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($admin->Jul ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($admin->Aug ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($admin->Sep ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($admin->Oct ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($admin->Nov ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($admin->Dec ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right">{{ number_format($admin->TOTAL ?? 0) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr class="font-semibold">
                        <td class="px-4 py-3 text-sm text-gray-900">JUMLAH</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($jmlAdminTahun->jml_total_jan ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($jmlAdminTahun->jml_total_feb ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($jmlAdminTahun->jml_total_mar ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($jmlAdminTahun->jml_total_apr ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($jmlAdminTahun->jml_total_may ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($jmlAdminTahun->jml_total_jun ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($jmlAdminTahun->jml_total_jul ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($jmlAdminTahun->jml_total_aug ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($jmlAdminTahun->jml_total_sep ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($jmlAdminTahun->jml_total_oct ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($jmlAdminTahun->jml_total_nov ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($jmlAdminTahun->jml_total_dec ?? 0) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($jmlAdmin->jml_total ?? 0) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @endif
</div>

<!-- Print Styles -->
<style>
@media print {
    .sidebar, .bg-[#14AE5C], button, a {
        display: none !important;
    }
    
    .bg-white {
        box-shadow: none !important;
    }
    
    table {
        page-break-inside: avoid;
    }
    
    .mb-8 {
        margin-bottom: 2rem !important;
    }
}
</style>
@endsection 