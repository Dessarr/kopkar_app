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
            color: #333;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        
        .header h1 {
            font-size: 24px;
            font-weight: bold;
            margin: 0 0 10px 0;
            color: #1e40af;
        }
        
        .header h2 {
            font-size: 18px;
            font-weight: normal;
            margin: 0;
            color: #666;
        }
        
        .period {
            text-align: center;
            margin-bottom: 30px;
            font-size: 14px;
            color: #666;
        }
        
        .section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        
        .section-title {
            background-color: #f3f4f6;
            padding: 10px 15px;
            font-weight: bold;
            font-size: 14px;
            border: 1px solid #d1d5db;
            margin-bottom: 0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        th, td {
            padding: 8px 12px;
            text-align: left;
            border: 1px solid #d1d5db;
        }
        
        th {
            background-color: #f9fafb;
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
        
        .total-row {
            background-color: #f3f4f6;
            font-weight: bold;
        }
        
        .highlight-box {
            background-color: #f0f9ff;
            border: 2px solid #0ea5e9;
            padding: 15px;
            margin: 15px 0;
            text-align: center;
        }
        
        .highlight-box h3 {
            margin: 0 0 10px 0;
            font-size: 16px;
            color: #0369a1;
        }
        
        .highlight-box .amount {
            font-size: 20px;
            font-weight: bold;
            color: #0369a1;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .summary-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 15px;
            text-align: center;
        }
        
        .summary-card h4 {
            margin: 0 0 8px 0;
            font-size: 12px;
            color: #64748b;
            text-transform: uppercase;
        }
        
        .summary-card .amount {
            font-size: 16px;
            font-weight: bold;
            color: #1e293b;
        }
        
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #d1d5db;
            padding-top: 10px;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 15px;
            }
            
            .page-break {
                page-break-before: always;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>LAPORAN RUGI LABA TOSERDA</h1>
        <h2>Koperasi Serba Usaha</h2>
    </div>
    
    <!-- Period -->
    <div class="period">
        <strong>Periode: {{ \Carbon\Carbon::parse($tgl_dari)->format('d F Y') }} - {{ \Carbon\Carbon::parse($tgl_samp)->format('d F Y') }}</strong>
    </div>
    
    <!-- Summary Cards -->
    <div class="summary-grid">
        <div class="summary-card">
            <h4>Total Pendapatan</h4>
            <div class="amount">Rp {{ number_format($summary['total_pendapatan'], 0, ',', '.') }}</div>
        </div>
        <div class="summary-card">
            <h4>Laba Kotor</h4>
            <div class="amount">Rp {{ number_format($summary['laba_kotor'], 0, ',', '.') }}</div>
        </div>
        <div class="summary-card">
            <h4>Laba Usaha</h4>
            <div class="amount">Rp {{ number_format($summary['laba_usaha'], 0, ',', '.') }}</div>
        </div>
        <div class="summary-card">
            <h4>Total SHU</h4>
            <div class="amount">Rp {{ number_format($summary['total_shu'], 0, ',', '.') }}</div>
        </div>
    </div>
    
    <!-- Pendapatan Usaha -->
    <div class="section">
        <h3 class="section-title">PENDAPATAN USAHA</h3>
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
                    <td class="text-right">Rp {{ number_format($penjualan->TOTAL ?? 0, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="2" class="text-center">Tidak ada data pendapatan</td>
                </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td>Total Pendapatan Usaha</td>
                    <td class="text-right">Rp {{ number_format($labaKotor->pendapatan_usaha, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
    
    <!-- Harga Pokok Penjualan -->
    <div class="section">
        <h3 class="section-title">HARGA POKOK PENJUALAN</h3>
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
                    <td class="text-right">Rp {{ number_format($pembelian->TOTAL ?? 0, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td>Pembelian Bersih</td>
                    <td class="text-right">Rp 0</td>
                </tr>
                @endforelse
                <tr>
                    <td>Persediaan Awal</td>
                    <td class="text-right">Rp {{ number_format($labaKotor->persediaan_awal, 0, ',', '.') }}</td>
                </tr>
                <tr class="total-row">
                    <td>Barang Tersedia untuk Dijual</td>
                    <td class="text-right">Rp {{ number_format($labaKotor->barang_tersedia, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Persediaan Akhir</td>
                    <td class="text-right">Rp {{ number_format($labaKotor->persediaan_akhir, 0, ',', '.') }}</td>
                </tr>
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td>Harga Pokok Penjualan</td>
                    <td class="text-right">Rp {{ number_format($labaKotor->hpp, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
    
    <!-- Laba Kotor -->
    <div class="highlight-box">
        <h3>LABA KOTOR</h3>
        <div class="amount">Rp {{ number_format($labaKotor->laba_kotor, 0, ',', '.') }}</div>
    </div>
    
    <!-- Biaya-Biaya Usaha -->
    <div class="section">
        <h3 class="section-title">BIAYA-BIAYA USAHA</h3>
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
                    <td class="text-right">Rp {{ number_format($biaya->TOTAL ?? 0, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="2" class="text-center">Tidak ada data biaya usaha</td>
                </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td>Total Biaya Usaha</td>
                    <td class="text-right">Rp {{ number_format($labaUsaha->total_biaya_usaha, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
    
    <!-- Laba Usaha -->
    <div class="highlight-box">
        <h3>LABA USAHA</h3>
        <div class="amount">Rp {{ number_format($labaUsaha->laba_usaha, 0, ',', '.') }}</div>
    </div>
    
    <!-- Pajak Penghasilan -->
    <div class="highlight-box">
        <h3>Pajak Penghasilan (12.5%)</h3>
        <div class="amount">Rp {{ number_format($pajakPenghasilan->pajak_penghasilan, 0, ',', '.') }}</div>
    </div>
    
    <!-- Laba Usaha Setelah Pajak -->
    <div class="highlight-box">
        <h3>LABA USAHA SETELAH PAJAK</h3>
        <div class="amount">Rp {{ number_format($labaUsahaSetelahPajak->laba_usaha_setelah_pajak, 0, ',', '.') }}</div>
    </div>
    
    <!-- SHU Yang Dibagikan -->
    <div class="section">
        <h3 class="section-title">SHU YANG DIBAGIKAN</h3>
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
                    <td class="text-right">Rp {{ number_format($shuDistribution->dana_anggota, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Dana Cadangan</td>
                    <td class="text-center">20%</td>
                    <td class="text-right">Rp {{ number_format($shuDistribution->dana_cadangan, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Dana Pegawai</td>
                    <td class="text-center">10%</td>
                    <td class="text-right">Rp {{ number_format($shuDistribution->dana_pegawai, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Dana Pembangunan Daerah Kerja</td>
                    <td class="text-center">5%</td>
                    <td class="text-right">Rp {{ number_format($shuDistribution->dana_pembangunan_daerah_kerja, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Dana Sosial</td>
                    <td class="text-center">5%</td>
                    <td class="text-right">Rp {{ number_format($shuDistribution->dana_sosial, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Dana Kesejahteraan Pegawai</td>
                    <td class="text-center">5%</td>
                    <td class="text-right">Rp {{ number_format($shuDistribution->dana_kesejahteraan_pegawai, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Dana Pendidikan</td>
                    <td class="text-center">5%</td>
                    <td class="text-right">Rp {{ number_format($shuDistribution->dana_pendidikan, 0, ',', '.') }}</td>
                </tr>
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td>Total SHU</td>
                    <td class="text-center">100%</td>
                    <td class="text-right">Rp {{ number_format($shuDistribution->total_shu, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
    
    <!-- Footer -->
    <div class="footer">
        <p>Laporan ini dibuat secara otomatis pada {{ date('d F Y H:i:s') }}</p>
        <p>Koperasi Serba Usaha - Sistem Informasi Manajemen Koperasi</p>
    </div>
</body>
</html>