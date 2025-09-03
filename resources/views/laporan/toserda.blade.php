@extends('layouts.app')

@section('title', 'Laporan Rugi Laba Toserda')
@section('sub-title', 'Laporan')

@section('content')
<div class="space-y-6">
    <!-- Header Panel -->
    <div class="bg-blue-600 text-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold mb-2">Cetak Laporan Rugi Laba</h2>
        <p class="text-blue-100">Laporan rugi laba toserda dengan perhitungan HPP, laba kotor, biaya usaha, dan distribusi SHU</p>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <form method="GET" action="{{ route('laporan.toserda') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="tahun" class="block text-sm font-medium text-gray-700 mb-2">Tahun</label>
                    <select name="tahun" id="tahun" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @for($i = date('Y'); $i >= 2020; $i--)
                            <option value="{{ $i }}" {{ $tahun == $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label for="tgl_dari" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Dari</label>
                    <input type="date" name="tgl_dari" id="tgl_dari" value="{{ $tgl_dari }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="tgl_samp" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Sampai</label>
                    <input type="date" name="tgl_samp" id="tgl_samp" value="{{ $tgl_samp }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            
            <div class="flex gap-3">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <i class="fas fa-search mr-2"></i>Filter Data
                </button>
                <a href="{{ route('laporan.toserda') }}" class="px-6 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    <i class="fas fa-times mr-2"></i>Hapus Filter
                </a>
            </div>
        </form>
    </div>

    <!-- Export Buttons -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex gap-3">
            <a href="{{ route('laporan.toserda.export.pdf', request()->query()) }}" class="px-6 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                <i class="fas fa-file-pdf mr-2"></i>Cetak Laporan
            </a>
            <a href="{{ route('laporan.toserda.export.excel', request()->query()) }}" class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                <i class="fas fa-file-excel mr-2"></i>Export Excel
            </a>
        </div>
    </div>

    <!-- Laporan Content -->
    <div class="space-y-6">
        <!-- Pendapatan Usaha -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">PENDAPATAN USAHA</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Transaksi</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($dataPenjualan as $penjualan)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $penjualan->jenisAkun->nama_akun ?? 'Pendapatan' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ number_format($penjualan->TOTAL ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2" class="px-6 py-4 text-center text-gray-500">Tidak ada data pendapatan</td>
                        </tr>
                        @endforelse
                        <tr class="bg-gray-50 font-bold">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Total Pendapatan Usaha</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ number_format($labaKotor->pendapatan_usaha, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Harga Pokok Penjualan -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">HARGA POKOK PENJUALAN</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Komponen</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($dataPembelian as $pembelian)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $pembelian->jenisAkun->nama_akun ?? 'Pembelian' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ number_format($pembelian->TOTAL ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Pembelian Bersih</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">0</td>
                        </tr>
                        @endforelse
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Persediaan Awal</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ number_format($labaKotor->persediaan_awal, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-semibold">Barang Tersedia untuk Dijual</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right font-semibold">{{ number_format($labaKotor->barang_tersedia, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Persediaan Akhir</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ number_format($labaKotor->persediaan_akhir, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="bg-gray-50 font-bold">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Harga Pokok Penjualan</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ number_format($labaKotor->hpp, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Laba Kotor -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-800">LABA KOTOR</h3>
                <span class="text-2xl font-bold text-green-600">{{ number_format($labaKotor->laba_kotor, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Biaya-Biaya Usaha -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">BIAYA-BIAYA USAHA</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Biaya</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($dataBiayaUsaha as $biaya)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $biaya->jenisAkun->nama_akun ?? 'Biaya' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ number_format($biaya->TOTAL ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2" class="px-6 py-4 text-center text-gray-500">Tidak ada data biaya usaha</td>
                        </tr>
                        @endforelse
                        <tr class="bg-gray-50 font-bold">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Total Biaya Usaha</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ number_format($labaUsaha->total_biaya_usaha, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Laba Usaha -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-800">LABA USAHA</h3>
                <span class="text-2xl font-bold text-blue-600">{{ number_format($labaUsaha->laba_usaha, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Pajak Penghasilan -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-800">Pajak Penghasilan (12.5%)</h3>
                <span class="text-2xl font-bold text-orange-600">{{ number_format($pajakPenghasilan->pajak_penghasilan, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Laba Usaha Setelah Pajak -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-800">LABA USAHA SETELAH PAJAK</h3>
                <span class="text-2xl font-bold text-purple-600">{{ number_format($labaUsahaSetelahPajak->laba_usaha_setelah_pajak, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- SHU Yang Dibagikan -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">SHU YANG DIBAGIKAN</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Dana</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Persentase</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Dana Anggota</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">50%</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ number_format($shuDistribution->dana_anggota, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Dana Cadangan</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">20%</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ number_format($shuDistribution->dana_cadangan, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Dana Pegawai</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">10%</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ number_format($shuDistribution->dana_pegawai, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Dana Pembangunan Daerah Kerja</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">5%</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ number_format($shuDistribution->dana_pembangunan_daerah_kerja, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Dana Sosial</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">5%</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ number_format($shuDistribution->dana_sosial, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Dana Kesejahteraan Pegawai</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">5%</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ number_format($shuDistribution->dana_kesejahteraan_pegawai, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Dana Pendidikan</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">5%</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ number_format($shuDistribution->dana_pendidikan, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="bg-gray-50 font-bold">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Total SHU</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">100%</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ number_format($shuDistribution->total_shu, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

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
