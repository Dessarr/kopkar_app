@extends('layouts.app')

@section('title', 'Laporan SHU')
@section('sub-title', 'Laporan')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <!-- Collapsible Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-3">
                <div class="p-2 bg-green-100 rounded-lg">
                    <i class="fas fa-chart-pie text-green-600 text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Laporan Sisa Hasil Usaha (SHU)</h1>
                    <p class="text-gray-600">Analisis dan distribusi keuntungan koperasi</p>
                </div>
            </div>
            <button onclick="toggleCollapse()"
                class="p-2 text-gray-500 hover:text-gray-700 transition-colors duration-200">
                <i class="fas fa-chevron-down text-xl" id="collapse-icon"></i>
            </button>
        </div>

        <!-- Collapsible Content -->
        <div id="collapsible-content" class="space-y-4">
            <!-- Filter Section -->
            <div class="bg-gray-50 rounded-lg p-4">
                <form method="GET" action="{{ route('laporan.shu') }}" class="flex flex-wrap gap-4 items-end">
                    <div class="flex-1 min-w-[200px]">
                        <label for="tgl_dari" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar-alt mr-2"></i>Tanggal Dari
                        </label>
                        <input type="date" id="tgl_dari" name="tgl_dari" value="{{ $tgl_dari }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent">
                    </div>
                    <div class="flex-1 min-w-[200px]">
                        <label for="tgl_samp" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar-alt mr-2"></i>Tanggal Sampai
                        </label>
                        <input type="date" id="tgl_samp" name="tgl_samp" value="{{ $tgl_samp }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit"
                            class="px-4 py-2 bg-[#14AE5C] text-white rounded-md hover:bg-[#11994F] transition-colors duration-200">
                            <i class="fas fa-search mr-2"></i>Filter
                        </button>
                        <a href="{{ route('laporan.shu') }}"
                            class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors duration-200">
                            <i class="fas fa-refresh mr-2"></i>Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- Export Buttons -->
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('laporan.shu.export.pdf', ['tgl_dari' => $tgl_dari, 'tgl_samp' => $tgl_samp]) }}"
                    class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors duration-200">
                    <i class="fas fa-file-pdf mr-2"></i>Export PDF
                </a>
                <a href="{{ route('laporan.shu.export.excel', ['tgl_dari' => $tgl_dari, 'tgl_samp' => $tgl_samp]) }}"
                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors duration-200">
                    <i class="fas fa-file-excel mr-2"></i>Export Excel
                </a>
                <button onclick="window.print()"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-200">
                    <i class="fas fa-print mr-2"></i>Print
                </button>
            </div>
        </div>
    </div>


    <!-- Ringkasan SHU Section -->
    <div class="bg-white border border-gray-200 rounded-lg mb-6">
        <div class="bg-blue-50 px-4 py-3 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-blue-800 flex items-center">
                <i class="fas fa-calculator mr-2"></i>Ringkasan SHU
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Keterangan</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Jumlah</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">SHU Sebelum Pajak</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-gray-900">Rp
                            {{ number_format($data['shu_sebelum_pajak'], 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Pajak PPh
                            ({{ $data['tax_rate'] }}%)</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-gray-900">Rp
                            {{ number_format($data['pajak_pph'], 0, ',', '.') }}</td>
                    </tr>
                    <tr class="bg-gray-100">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">SHU Setelah Pajak</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-gray-900">Rp
                            {{ number_format($data['shu_setelah_pajak'], 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pembagian SHU untuk Dana-Dana Section -->
    <div class="bg-white border border-gray-200 rounded-lg mb-6">
        <div class="bg-green-50 px-4 py-3 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-green-800 flex items-center">
                <i class="fas fa-share-alt mr-2"></i>PEMBAGIAN SHU UNTUK DANA-DANA
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dana
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Persentase</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Jumlah</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Dana Cadangan</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900">40%</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-900">Rp
                            {{ number_format($data['dana_cadangan'], 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Jasa Anggota</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900">40%</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-900">Rp
                            {{ number_format($data['jasa_anggota'], 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Dana Pengurus</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900">5%</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-900">Rp
                            {{ number_format($data['dana_pengurus'], 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Dana Karyawan</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900">5%</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-900">Rp
                            {{ number_format($data['dana_karyawan'], 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Dana Pendidikan</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900">5%</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-900">Rp
                            {{ number_format($data['dana_pendidikan'], 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Dana Sosial</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900">5%</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-900">Rp
                            {{ number_format($data['dana_sosial'], 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pembagian SHU Anggota Section -->
    <div class="bg-white border border-gray-200 rounded-lg mb-6">
        <div class="bg-purple-50 px-4 py-3 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-purple-800 flex items-center">
                <i class="fas fa-users mr-2"></i>PEMBAGIAN SHU ANGGOTA
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Keterangan</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Persentase</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Jumlah</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Jasa Usaha</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900">70%</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-900">Rp
                            {{ number_format($data['jasa_usaha'], 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Jasa Modal</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900">30%</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-900">Rp
                            {{ number_format($data['jasa_modal'], 0, ',', '.') }}</td>
                    </tr>
                    <tr class="bg-gray-100">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">Total Pendapatan Anggota
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900">-</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-gray-900">Rp
                            {{ number_format($data['total_pendapatan_anggota'], 0, ',', '.') }}</td>
                    </tr>
                    <tr class="bg-gray-100">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">Total Simpanan Anggota
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900">-</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-gray-900">Rp
                            {{ number_format($data['total_simpanan'], 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function toggleCollapse() {
    const content = document.getElementById('collapsible-content');
    const icon = document.getElementById('collapse-icon');

    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.classList.remove('fa-chevron-down');
        icon.classList.add('fa-chevron-up');
    } else {
        content.style.display = 'none';
        icon.classList.remove('fa-chevron-up');
        icon.classList.add('fa-chevron-down');
    }
}

// Initialize collapsible state
document.addEventListener('DOMContentLoaded', function() {
    const content = document.getElementById('collapsible-content');
    const icon = document.getElementById('collapse-icon');

    // Start with content visible
    content.style.display = 'block';
    icon.classList.remove('fa-chevron-down');
    icon.classList.add('fa-chevron-up');
});
</script>
@endsection