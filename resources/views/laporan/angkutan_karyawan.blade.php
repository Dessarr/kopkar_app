@extends('layouts.app')

@section('title', 'Laporan Laba Rugi Bus Angkutan Karyawan')
@section('sub-title', 'Laporan')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <!-- Header and Controls -->
    <div class="bg-blue-600 text-white p-4 rounded-lg mb-6">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-bold">
                <i class="fas fa-bus mr-2"></i>
                Laporan Laba Rugi Bus Angkutan Karyawan
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('laporan.angkutan.karyawan.export.pdf', request()->query()) }}" 
                   class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors duration-200">
                    <i class="fas fa-file-pdf mr-2"></i>Cetak Laporan
                </a>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="mb-6">
        <div class="bg-gray-50 p-4 rounded-lg">
        <form method="GET" action="{{ route('laporan.angkutan.karyawan') }}" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label for="tgl_dari" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Dari</label>
                    <input type="date" id="tgl_dari" name="tgl_dari" 
                           value="{{ $tgl_dari }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent">
            </div>
            <div class="flex-1 min-w-[200px]">
                <label for="tgl_samp" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Sampai</label>
                    <input type="date" id="tgl_samp" name="tgl_samp" 
                           value="{{ $tgl_samp }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-[#14AE5C] text-white rounded-md hover:bg-[#11994F] transition-colors duration-200">
                        <i class="fas fa-filter mr-2"></i>Filter
                </button>
                <a href="{{ route('laporan.angkutan.karyawan') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors duration-200">
                    <i class="fas fa-refresh mr-2"></i>Hapus Filter
                </a>
            </div>
        </form>
    </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Pendapatan -->
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white p-6 rounded-lg shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium">Total Pendapatan</p>
                    <p class="text-2xl font-bold">Rp {{ number_format($summary['total_pendapatan'], 0, ',', '.') }}</p>
                </div>
                <div class="bg-blue-400 bg-opacity-30 p-3 rounded-full">
                    <i class="fas fa-chart-line text-2xl"></i>
                </div>
            </div>
        </div>
        
        <!-- Total Biaya Operasional -->
        <div class="bg-gradient-to-r from-orange-500 to-orange-600 text-white p-6 rounded-lg shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-100 text-sm font-medium">Total Biaya Operasional</p>
                    <p class="text-2xl font-bold">Rp {{ number_format($summary['total_biaya_operasional'], 0, ',', '.') }}</p>
                </div>
                <div class="bg-orange-400 bg-opacity-30 p-3 rounded-full">
                    <i class="fas fa-cogs text-2xl"></i>
                </div>
            </div>
        </div>
        
        <!-- Total Biaya Admin -->
        <div class="bg-gradient-to-r from-purple-500 to-purple-600 text-white p-6 rounded-lg shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm font-medium">Total Biaya Admin</p>
                    <p class="text-2xl font-bold">Rp {{ number_format($summary['total_biaya_admin'], 0, ',', '.') }}</p>
                </div>
                <div class="bg-purple-400 bg-opacity-30 p-3 rounded-full">
                    <i class="fas fa-users text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Laba Usaha -->
        <div class="bg-gradient-to-r {{ $summary['laba_usaha'] >= 0 ? 'from-green-500 to-green-600' : 'from-red-500 to-red-600' }} text-white p-6 rounded-lg shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="{{ $summary['laba_usaha'] >= 0 ? 'text-green-100' : 'text-red-100' }} text-sm font-medium">Laba Usaha</p>
                    <p class="text-2xl font-bold">Rp {{ number_format($summary['laba_usaha'], 0, ',', '.') }}</p>
                </div>
                <div class="bg-opacity-30 p-3 rounded-full {{ $summary['laba_usaha'] >= 0 ? 'bg-green-400' : 'bg-red-400' }}">
                    <i class="fas fa-chart-pie text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Summary -->
    <div class="mb-8">
        <div class="bg-gray-50 p-6 rounded-lg">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Ringkasan Keuangan</h3>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Pendapatan -->
                <div>
                    <h4 class="text-blue-600 font-medium mb-3">Pendapatan</h4>
                    <div class="space-y-2">
                        <div class="flex justify-between items-center py-2 border-b border-gray-200">
                            <span class="text-gray-700">Pendapatan Kotor</span>
                            <span class="font-medium">Rp {{ number_format($summary['total_pendapatan'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-200">
                            <span class="text-gray-700">Pajak (2%)</span>
                            <span class="text-red-600 font-medium">- Rp {{ number_format($summary['pajak'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 bg-blue-50 rounded-md px-3">
                            <span class="font-semibold text-blue-800">Pendapatan Setelah Pajak</span>
                            <span class="font-bold text-blue-800">Rp {{ number_format($summary['pendapatan_setelah_pajak'], 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Biaya -->
                <div>
                    <h4 class="text-red-600 font-medium mb-3">Biaya</h4>
                    <div class="space-y-2">
                        <div class="flex justify-between items-center py-2 border-b border-gray-200">
                            <span class="text-gray-700">Biaya Operasional</span>
                            <span class="text-red-600 font-medium">- Rp {{ number_format($summary['total_biaya_operasional'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-200">
                            <span class="text-gray-700">Biaya Administrasi</span>
                            <span class="text-red-600 font-medium">- Rp {{ number_format($summary['total_biaya_admin'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 bg-red-50 rounded-md px-3">
                            <span class="font-semibold text-red-800">Total Biaya</span>
                            <span class="font-bold text-red-800">- Rp {{ number_format($summary['total_biaya'], 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Laba Usaha -->
            <div class="mt-6 text-center">
                <div class="inline-block bg-gray-100 px-6 py-4 rounded-lg">
                    <h4 class="text-lg font-semibold text-gray-700 mb-2">Laba Usaha</h4>
                    <p class="text-3xl font-bold {{ $summary['laba_usaha'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        Rp {{ number_format($summary['laba_usaha'], 0, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Tables -->
    <!-- Pendapatan Table -->
    <div class="mb-8">
        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
            <div class="bg-blue-50 px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">Penghasilan Jasa Sewa Bus</h3>
            </div>
        <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-16">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                    </tr>
                </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($dataPendapatan as $index => $item)
                    <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->no_polisi ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">Rp {{ number_format($item->TOTAL ?? 0, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                            <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">Tidak ada data pendapatan</td>
                    </tr>
                    @endforelse
                </tbody>
                    <tfoot class="bg-blue-50">
                        <tr>
                            <th colspan="2" class="px-6 py-3 text-left text-sm font-semibold text-gray-900">TOTAL PENDAPATAN</th>
                            <th class="px-6 py-3 text-right text-sm font-semibold text-gray-900">Rp {{ number_format($summary['total_pendapatan'], 0, ',', '.') }}</th>
                    </tr>
                </tfoot>
            </table>
            </div>
        </div>
    </div>

    <!-- Biaya Operasional Table -->
    <div class="mb-8">
        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
            <div class="bg-orange-50 px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">Biaya Operasional</h3>
            </div>
        <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-16">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                    </tr>
                </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($dataBiayaOperasional as $index => $item)
                    <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->jns_trans ?? 'Biaya Operasional' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">Rp {{ number_format($item->TOTAL ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">Tidak ada data biaya operasional</td>
                    </tr>
                        @endforelse
                </tbody>
                    <tfoot class="bg-orange-50">
                        <tr>
                            <th colspan="2" class="px-6 py-3 text-left text-sm font-semibold text-gray-900">TOTAL BIAYA OPERASIONAL</th>
                            <th class="px-6 py-3 text-right text-sm font-semibold text-gray-900">Rp {{ number_format($summary['total_biaya_operasional'], 0, ',', '.') }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    </div>

    <!-- Biaya Admin Table -->
    <div class="mb-8">
        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
            <div class="bg-purple-50 px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">Biaya Administrasi Dan Umum</h3>
            </div>
        <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-16">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                    </tr>
                </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($dataBiayaAdmin as $index => $item)
                    <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->jns_trans ?? 'Biaya Administrasi' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">Rp {{ number_format($item->TOTAL ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">Tidak ada data biaya administrasi</td>
                    </tr>
                        @endforelse
                </tbody>
                    <tfoot class="bg-purple-50">
                        <tr>
                            <th colspan="2" class="px-6 py-3 text-left text-sm font-semibold text-gray-900">TOTAL BIAYA ADMIN</th>
                            <th class="px-6 py-3 text-right text-sm font-semibold text-gray-900">Rp {{ number_format($summary['total_biaya_admin'], 0, ',', '.') }}</th>
                    </tr>
                </tfoot>
            </table>
            </div>
        </div>
    </div>

    <!-- SHU Distribution -->
    @if($summary['laba_usaha'] > 0)
    <div class="mb-8">
        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
            <div class="bg-green-50 px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">Distribusi SHU (Sisa Hasil Usaha)</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-16">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Persentase</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">1</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Dana Anggota</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">50%</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">Rp {{ number_format($shuDistribution['dana_anggota'], 0, ',', '.') }}</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">2</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Dana Cadangan</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">20%</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">Rp {{ number_format($shuDistribution['dana_cadangan'], 0, ',', '.') }}</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">3</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Dana Pegawai</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">10%</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">Rp {{ number_format($shuDistribution['dana_pegawai'], 0, ',', '.') }}</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">4</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Dana Pembangunan Daerah Kerja</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">5%</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">Rp {{ number_format($shuDistribution['dana_pembangunan_daerah_kerja'], 0, ',', '.') }}</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">5</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Dana Sosial</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">5%</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">Rp {{ number_format($shuDistribution['dana_sosial'], 0, ',', '.') }}</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">6</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Dana Kesejahteraan Pegawai</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">5%</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">Rp {{ number_format($shuDistribution['dana_kesejahteraan_pegawai'], 0, ',', '.') }}</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">7</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Dana Pendidikan</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">5%</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">Rp {{ number_format($shuDistribution['dana_pendidikan'], 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                    <tfoot class="bg-green-50">
                        <tr>
                            <th colspan="3" class="px-6 py-3 text-left text-sm font-semibold text-gray-900">TOTAL SHU</th>
                            <th class="px-6 py-3 text-right text-sm font-semibold text-gray-900">Rp {{ number_format($shuDistribution['total_shu'], 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    @else
    <div class="mb-8">
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-yellow-400 text-2xl"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-lg font-medium text-yellow-800">Tidak Ada SHU yang Dibagikan</h3>
                    <p class="mt-1 text-sm text-yellow-700">
                        Karena laba usaha negatif atau nol, tidak ada SHU (Sisa Hasil Usaha) yang dapat dibagikan pada periode ini.
                    </p>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Print Styles -->
<style>
@media print {
    .bg-blue-600, .bg-orange-500, .bg-purple-500, .bg-green-500, .bg-red-500 {
        background-color: #f3f4f6 !important;
        color: #000 !important;
    }
    
    .bg-gradient-to-r {
        background: #f3f4f6 !important;
        color: #000 !important;
    }
    
    .shadow-lg, .shadow-md {
        box-shadow: none !important;
    }
    
    .rounded-lg {
        border-radius: 0 !important;
    }
    
    .border {
        border: 1px solid #000 !important;
    }
    
    .text-white {
        color: #000 !important;
    }
    
    .hover\:bg-gray-50:hover {
        background-color: transparent !important;
    }
}
</style>
@endsection 