@extends('layouts.app')

@section('title', 'Laporan Rugi Laba Toserda')
@section('sub-title', 'Laporan')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <!-- Header and Controls -->
    <div class="bg-blue-600 text-white p-4 rounded-lg mb-6">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-bold">
                <i class="fas fa-store mr-2"></i>
                Laporan Rugi Laba Toserda
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('laporan.toserda.export.pdf', request()->query()) }}"
                    class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors duration-200">
                    <i class="fas fa-file-pdf mr-2"></i>Cetak Laporan
                </a>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="mb-6">
        <div class="bg-gray-50 p-4 rounded-lg">
            <form method="GET" action="{{ route('laporan.toserda') }}" class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label for="tahun" class="block text-sm font-medium text-gray-700 mb-2">Tahun</label>
                    <select name="tahun" id="tahun"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent">
                        @for($i = date('Y'); $i >= 2020; $i--)
                        <option value="{{ $i }}" {{ $tahun == $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label for="tgl_dari" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Dari</label>
                    <input type="date" id="tgl_dari" name="tgl_dari" value="{{ $tgl_dari }}" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent">
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label for="tgl_samp" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Sampai</label>
                    <input type="date" id="tgl_samp" name="tgl_samp" value="{{ $tgl_samp }}" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent">
                </div>
                <div class="flex gap-2">
                    <button type="submit"
                        class="px-4 py-2 bg-[#14AE5C] text-white rounded-md hover:bg-[#11994F] transition-colors duration-200">
                        <i class="fas fa-filter mr-2"></i>Filter
                    </button>
                    <a href="{{ route('laporan.toserda') }}"
                        class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors duration-200">
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

        <!-- Laba Kotor -->
        <div class="bg-gradient-to-r from-green-500 to-green-600 text-white p-6 rounded-lg shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-medium">Laba Kotor</p>
                    <p class="text-2xl font-bold">Rp {{ number_format($summary['laba_kotor'], 0, ',', '.') }}</p>
                </div>
                <div class="bg-green-400 bg-opacity-30 p-3 rounded-full">
                    <i class="fas fa-chart-pie text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Laba Usaha -->
        <div class="bg-gradient-to-r from-purple-500 to-purple-600 text-white p-6 rounded-lg shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm font-medium">Laba Usaha</p>
                    <p class="text-2xl font-bold">Rp {{ number_format($summary['laba_usaha'], 0, ',', '.') }}</p>
                </div>
                <div class="bg-purple-400 bg-opacity-30 p-3 rounded-full">
                    <i class="fas fa-coins text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Total SHU -->
        <div class="bg-gradient-to-r from-orange-500 to-orange-600 text-white p-6 rounded-lg shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-100 text-sm font-medium">Total SHU</p>
                    <p class="text-2xl font-bold">Rp {{ number_format($summary['total_shu'], 0, ',', '.') }}</p>
                </div>
                <div class="bg-orange-400 bg-opacity-30 p-3 rounded-full">
                    <i class="fas fa-hand-holding-usd text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Laporan Content -->
    <div class="space-y-6">
        <!-- Pendapatan Usaha -->
        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
            <div class="bg-blue-50 px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">PENDAPATAN USAHA</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                No.</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Keterangan</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Total</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($dataPenjualan as $index => $penjualan)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $penjualan->jns_trans ?? 'Pendapatan' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">Rp
                                {{ number_format($penjualan->TOTAL ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">Tidak ada data
                                pendapatan</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-blue-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">-</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Total Pendapatan Usaha
                            </th>
                            <th class="px-6 py-3 text-right text-sm font-semibold text-gray-900">Rp
                                {{ number_format($labaKotor->pendapatan_usaha, 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Harga Pokok Penjualan -->
        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
            <div class="bg-orange-50 px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">HARGA POKOK PENJUALAN</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                No.</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Keterangan</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">-</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Persediaan Awal Brg Dagangan
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">Rp
                                {{ number_format($labaKotor->persediaan_awal, 0, ',', '.') }}</td>
                        </tr>
                        @forelse($dataPembelian as $index => $pembelian)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $pembelian->jns_trans ?? 'Pembelian' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">Rp
                                {{ number_format($pembelian->TOTAL ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">1</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Pembelian</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">Rp 0</td>
                        </tr>
                        @endforelse
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">-</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Pembelian Bersih</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">Rp
                                {{ number_format($labaKotor->pembelian_bersih, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="bg-orange-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-semibold">-</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-semibold">Barang Tersedia
                                Untuk Dijual</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right font-semibold">Rp
                                {{ number_format($labaKotor->barang_tersedia, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">-</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Persediaan Akhir Brng Dagangan
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">Rp
                                {{ number_format($labaKotor->persediaan_akhir, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                    <tfoot class="bg-orange-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">-</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Harga Pokok Penjualan
                            </th>
                            <th class="px-6 py-3 text-right text-sm font-semibold text-gray-900">Rp
                                {{ number_format($labaKotor->hpp, 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Laba Kotor -->
        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
            <div class="bg-green-50 px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-800">LABA KOTOR</h3>
                    <span class="text-3xl font-bold text-green-600">Rp
                        {{ number_format($labaKotor->laba_kotor, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Biaya-Biaya Usaha -->
        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
            <div class="bg-red-50 px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">BIAYA-BIAYA USAHA</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                No.</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Keterangan</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($dataBiayaUsaha as $index => $biaya)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $biaya->jns_trans ?? 'Biaya' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">Rp
                                {{ number_format($biaya->TOTAL ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">Tidak ada data biaya
                                usaha</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-red-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">-</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Total Biaya Usaha</th>
                            <th class="px-6 py-3 text-right text-sm font-semibold text-gray-900">Rp
                                {{ number_format($labaUsaha->total_biaya_usaha, 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Laba Usaha -->
        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
            <div class="bg-purple-50 px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-800">LABA USAHA</h3>
                    <span class="text-3xl font-bold text-purple-600">Rp
                        {{ number_format($labaUsaha->laba_usaha, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Pajak Penghasilan -->
        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
            <div class="bg-yellow-50 px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-800">Pajak Penghasilan (12.5%)</h3>
                    <span class="text-3xl font-bold text-yellow-600">Rp
                        {{ number_format($pajakPenghasilan->pajak_penghasilan, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Laba Usaha Setelah Pajak -->
        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
            <div class="bg-indigo-50 px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-800">LABA USAHA SETELAH PAJAK</h3>
                    <span class="text-3xl font-bold text-indigo-600">Rp
                        {{ number_format($labaUsahaSetelahPajak->laba_usaha_setelah_pajak, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- SHU Yang Dibagikan -->
        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
            <div class="bg-teal-50 px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">SHU YANG DIBAGIKAN</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                No.</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Keterangan</th>
                            <th
                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Persentase</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">1</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Dana Anggota</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">50%</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">Rp
                                {{ number_format($shuDistribution->dana_anggota, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">2</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Dana Cadangan</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">20%</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">Rp
                                {{ number_format($shuDistribution->dana_cadangan, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">3</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Dana Pegawai</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">10%</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">Rp
                                {{ number_format($shuDistribution->dana_pegawai, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">4</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Dana Pembangunan Daerah Kerja
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">5%</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">Rp
                                {{ number_format($shuDistribution->dana_pembangunan_daerah_kerja, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">5</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Dana Sosial</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">5%</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">Rp
                                {{ number_format($shuDistribution->dana_sosial, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">6</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Dana Kesejahteraan Pegawai
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">5%</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">Rp
                                {{ number_format($shuDistribution->dana_kesejahteraan_pegawai, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">7</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Dana Pendidikan</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">5%</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">Rp
                                {{ number_format($shuDistribution->dana_pendidikan, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                    <tfoot class="bg-teal-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">-</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Total SHU</th>
                            <th class="px-6 py-3 text-center text-sm font-semibold text-gray-900">100%</th>
                            <th class="px-6 py-3 text-right text-sm font-semibold text-gray-900">Rp
                                {{ number_format($shuDistribution->total_shu, 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Print Styles -->
<style>
@media print {

    .bg-blue-600,
    .bg-green-500,
    .bg-purple-500,
    .bg-orange-500 {
        background-color: #f3f4f6 !important;
        color: #000 !important;
    }

    .bg-gradient-to-r {
        background: #f3f4f6 !important;
        color: #000 !important;
    }

    .shadow-lg,
    .shadow-md {
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set default date range when year changes
    document.getElementById('tahun').addEventListener('change', function() {
        const year = this.value;
        document.getElementById('tgl_dari').value = year + '-01-01';
        document.getElementById('tgl_samp').value = year + '-12-31';
    });

    // Validate date range
    document.getElementById('tgl_dari').addEventListener('change', function() {
        const tglDari = new Date(this.value);
        const tglSamp = new Date(document.getElementById('tgl_samp').value);

        if (tglDari > tglSamp) {
            alert('Tanggal dari tidak boleh lebih besar dari tanggal sampai');
            this.value = document.getElementById('tgl_samp').value;
        }
    });

    document.getElementById('tgl_samp').addEventListener('change', function() {
        const tglDari = new Date(document.getElementById('tgl_dari').value);
        const tglSamp = new Date(this.value);

        if (tglSamp < tglDari) {
            alert('Tanggal sampai tidak boleh lebih kecil dari tanggal dari');
            this.value = document.getElementById('tgl_dari').value;
        }
    });
});
</script>
@endsection