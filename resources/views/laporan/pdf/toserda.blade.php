<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Rugi Laba Toserda</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        
        .header h1 {
            font-size: 18px;
            font-weight: bold;
            margin: 0 0 10px 0;
            text-transform: uppercase;
        }
        
        .header h2 {
            font-size: 16px;
            font-weight: bold;
            margin: 0 0 5px 0;
        }
        
        .header p {
            font-size: 12px;
            margin: 0;
        }
        
        .period {
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
        }
        
        .section {
            margin-bottom: 25px;
        }
        
        .section-title {
            font-size: 14px;
            font-weight: bold;
            background-color: #f5f5f5;
            padding: 8px;
            border: 1px solid #ddd;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        th, td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            text-align: left;
        }
        
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
        }
        
        td {
            font-size: 11px;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .font-bold {
            font-weight: bold;
        }
        
        .bg-gray {
            background-color: #f8f9fa;
        }
        
        .summary-box {
            border: 2px solid #333;
            padding: 15px;
            margin: 15px 0;
            background-color: #f9f9f9;
        }
        
        .summary-title {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 10px;
        }
        
        .summary-amount {
            font-size: 16px;
            font-weight: bold;
            text-align: right;
        }
        
        .positive {
            color: #28a745;
        }
        
        .negative {
            color: #dc3545;
        }
        
        .neutral {
            color: #333;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>Laporan Rugi Laba</h1>
        <h2>Toserda (Toko Serba Ada)</h2>
        <p>Koperasi Pegawai Republik Indonesia</p>
    </div>
    
    <!-- Period -->
    <div class="period">
        Periode: {{ \Carbon\Carbon::parse($tgl_dari)->format('d F Y') }} - {{ \Carbon\Carbon::parse($tgl_samp)->format('d F Y') }}
    </div>
    
    <!-- Pendapatan Usaha -->
    <div class="section">
        <div class="section-title">Pendapatan Usaha</div>
        <table>
            <thead>
                <tr>
                    <th>Jenis Transaksi</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dataPenjualan as $penjualan)
                <tr>
                    <td>{{ $penjualan->jenisAkun->nama_akun ?? 'Pendapatan' }}</td>
                    <td class="text-right">{{ number_format($penjualan->TOTAL ?? 0, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="2" class="text-center">Tidak ada data pendapatan</td>
                </tr>
                @endforelse
                <tr class="bg-gray font-bold">
                    <td>Total Pendapatan Usaha</td>
                    <td class="text-right">{{ number_format($labaKotor->pendapatan_usaha, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <!-- Harga Pokok Penjualan -->
    <div class="section">
        <div class="section-title">Harga Pokok Penjualan</div>
        <table>
            <thead>
                <tr>
                    <th>Komponen</th>
                    <th class="text-right">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dataPembelian as $pembelian)
                <tr>
                    <td>{{ $pembelian->jenisAkun->nama_akun ?? 'Pembelian' }}</td>
                    <td class="text-right">{{ number_format($pembelian->TOTAL ?? 0, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td>Pembelian Bersih</td>
                    <td class="text-right">0</td>
                </tr>
                @endforelse
                <tr>
                    <td>Persediaan Awal</td>
                    <td class="text-right">{{ number_format($labaKotor->persediaan_awal, 0, ',', '.') }}</td>
                </tr>
                <tr class="bg-gray">
                    <td class="font-bold">Barang Tersedia untuk Dijual</td>
                    <td class="text-right font-bold">{{ number_format($labaKotor->barang_tersedia, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Persediaan Akhir</td>
                    <td class="text-right">{{ number_format($labaKotor->persediaan_akhir, 0, ',', '.') }}</td>
                </tr>
                <tr class="bg-gray font-bold">
                    <td>Harga Pokok Penjualan</td>
                    <td class="text-right">{{ number_format($labaKotor->hpp, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <!-- Laba Kotor -->
    <div class="summary-box">
        <div class="summary-title">Laba Kotor</div>
        <div class="summary-amount {{ $labaKotor->laba_kotor >= 0 ? 'positive' : 'negative' }}">
            {{ number_format($labaKotor->laba_kotor, 0, ',', '.') }}
        </div>
    </div>
    
    <!-- Biaya-Biaya Usaha -->
    <div class="section">
        <div class="section-title">Biaya-Biaya Usaha</div>
        <table>
            <thead>
                <tr>
                    <th>Jenis Biaya</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dataBiayaUsaha as $biaya)
                <tr>
                    <td>{{ $biaya->jenisAkun->nama_akun ?? 'Biaya' }}</td>
                    <td class="text-right">{{ number_format($biaya->TOTAL ?? 0, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="2" class="text-center">Tidak ada data biaya usaha</td>
                </tr>
                @endforelse
                <tr class="bg-gray font-bold">
                    <td>Total Biaya Usaha</td>
                    <td class="text-right">{{ number_format($labaUsaha->total_biaya_usaha, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <!-- Laba Usaha -->
    <div class="summary-box">
        <div class="summary-title">Laba Usaha</div>
        <div class="summary-amount {{ $labaUsaha->laba_usaha >= 0 ? 'positive' : 'negative' }}">
            {{ number_format($labaUsaha->laba_usaha, 0, ',', '.') }}
        </div>
    </div>
    
    <!-- Pajak Penghasilan -->
    <div class="summary-box">
        <div class="summary-title">Pajak Penghasilan (12.5%)</div>
        <div class="summary-amount neutral">
            {{ number_format($pajakPenghasilan->pajak_penghasilan, 0, ',', '.') }}
        </div>
    </div>
    
    <!-- Laba Usaha Setelah Pajak -->
    <div class="summary-box">
        <div class="summary-title">Laba Usaha Setelah Pajak</div>
        <div class="summary-amount {{ $labaUsahaSetelahPajak->laba_usaha_setelah_pajak >= 0 ? 'positive' : 'negative' }}">
            {{ number_format($labaUsahaSetelahPajak->laba_usaha_setelah_pajak, 0, ',', '.') }}
        </div>
    </div>
    
    <!-- SHU Yang Dibagikan -->
    <div class="section">
        <div class="section-title">SHU Yang Dibagikan</div>
        <table>
            <thead>
                <tr>
                    <th>Jenis Dana</th>
                    <th class="text-center">Persentase</th>
                    <th class="text-right">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Dana Anggota</td>
                    <td class="text-center">50%</td>
                    <td class="text-right">{{ number_format($shuDistribution->dana_anggota, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Dana Cadangan</td>
                    <td class="text-center">20%</td>
                    <td class="text-right">{{ number_format($shuDistribution->dana_cadangan, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Dana Pegawai</td>
                    <td class="text-center">10%</td>
                    <td class="text-right">{{ number_format($shuDistribution->dana_pegawai, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Dana Pembangunan Daerah Kerja</td>
                    <td class="text-center">5%</td>
                    <td class="text-right">{{ number_format($shuDistribution->dana_pembangunan_daerah_kerja, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Dana Sosial</td>
                    <td class="text-center">5%</td>
                    <td class="text-right">{{ number_format($shuDistribution->dana_sosial, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Dana Kesejahteraan Pegawai</td>
                    <td class="text-center">5%</td>
                    <td class="text-right">{{ number_format($shuDistribution->dana_kesejahteraan_pegawai, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Dana Pendidikan</td>
                    <td class="text-center">5%</td>
                    <td class="text-right">{{ number_format($shuDistribution->dana_pendidikan, 0, ',', '.') }}</td>
                </tr>
                <tr class="bg-gray font-bold">
                    <td>Total SHU</td>
                    <td class="text-center">100%</td>
                    <td class="text-right">{{ number_format($shuDistribution->total_shu, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <!-- Footer -->
    <div class="footer">
        <p>Laporan ini dibuat secara otomatis pada {{ date('d F Y H:i:s') }}</p>
        <p>Koperasi Pegawai Republik Indonesia - Sistem Informasi Manajemen Koperasi</p>
    </div>
</body>
</html>
