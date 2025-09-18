<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Laba Rugi Bus Angkutan Karyawan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .header h1 {
            font-size: 18px;
            margin: 0;
            color: #333;
        }
        
        .header h2 {
            font-size: 14px;
            margin: 5px 0;
            color: #666;
        }
        
        .period {
            font-size: 12px;
            color: #666;
            margin-bottom: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin: 20px 0 10px 0;
            color: #333;
        }
        
        .summary {
            background-color: #f9f9f9;
            padding: 15px;
            border: 1px solid #ddd;
            margin: 20px 0;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
        }
        
        .summary-total {
            font-weight: bold;
            border-top: 1px solid #333;
            padding-top: 5px;
            margin-top: 10px;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        
        .no-data {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN LABA RUGI BUS ANGKUTAN KARYAWAN</h1>
        <h2>KOPERASI INDONESIA</h2>
        <div class="period">
            Periode: {{ \Carbon\Carbon::parse($tgl_dari)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($tgl_samp)->format('d/m/Y') }}
        </div>
    </div>

    <!-- Financial Summary -->
    <div class="summary">
        <div class="section-title">RINGKASAN KEUANGAN</div>
        <div class="summary-row">
            <span>Pendapatan Kotor:</span>
            <span class="text-right">Rp {{ number_format($summary['total_pendapatan'], 0, ',', '.') }}</span>
        </div>
        <div class="summary-row">
            <span>Pajak (2%):</span>
            <span class="text-right">- Rp {{ number_format($summary['pajak'], 0, ',', '.') }}</span>
        </div>
        <div class="summary-row summary-total">
            <span>Pendapatan Setelah Pajak:</span>
            <span class="text-right">Rp {{ number_format($summary['pendapatan_setelah_pajak'], 0, ',', '.') }}</span>
        </div>
        <div class="summary-row">
            <span>Biaya Operasional:</span>
            <span class="text-right">- Rp {{ number_format($summary['total_biaya_operasional'], 0, ',', '.') }}</span>
        </div>
        <div class="summary-row">
            <span>Biaya Administrasi:</span>
            <span class="text-right">- Rp {{ number_format($summary['total_biaya_admin'], 0, ',', '.') }}</span>
        </div>
        <div class="summary-row">
            <span>Total Biaya:</span>
            <span class="text-right">- Rp {{ number_format($summary['total_biaya'], 0, ',', '.') }}</span>
        </div>
        <div class="summary-row summary-total">
            <span>Laba Usaha:</span>
            <span class="text-right">Rp {{ number_format($summary['laba_usaha'], 0, ',', '.') }}</span>
        </div>
    </div>

    <!-- Pendapatan Table -->
    <div class="section-title">PENGHASILAN JASA SEWA BUS</div>
    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>Keterangan</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dataPendapatan as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $item->no_polisi }}</td>
                <td class="text-right">Rp {{ number_format($item->TOTAL, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="3" class="no-data">Tidak ada data pendapatan</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #f5f5f5; font-weight: bold;">
                <td colspan="2">TOTAL PENDAPATAN</td>
                <td class="text-right">Rp {{ number_format($summary['total_pendapatan'], 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <!-- Biaya Operasional Table -->
    <div class="section-title">BIAYA OPERASIONAL</div>
    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>Keterangan</th>
                <th class="text-right">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dataBiayaOperasional as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $item->jns_trans }}</td>
                <td class="text-right">Rp {{ number_format($item->TOTAL, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="3" class="no-data">Tidak ada data biaya operasional</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #f5f5f5; font-weight: bold;">
                <td colspan="2">TOTAL BIAYA OPERASIONAL</td>
                <td class="text-right">Rp {{ number_format($summary['total_biaya_operasional'], 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
    
    <!-- Biaya Admin Table -->
    <div class="section-title">BIAYA ADMINISTRASI DAN UMUM</div>
    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>Keterangan</th>
                <th class="text-right">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dataBiayaAdmin as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $item->jns_trans }}</td>
                <td class="text-right">Rp {{ number_format($item->TOTAL, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="3" class="no-data">Tidak ada data biaya administrasi</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #f5f5f5; font-weight: bold;">
                <td colspan="2">TOTAL BIAYA ADMIN</td>
                <td class="text-right">Rp {{ number_format($summary['total_biaya_admin'], 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <!-- SHU Distribution -->
    @if($summary['laba_usaha'] > 0)
    <div class="section-title">DISTRIBUSI SHU (SISA HASIL USAHA)</div>
    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>Keterangan</th>
                <th class="text-center">Persentase</th>
                <th class="text-right">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td>Dana Anggota</td>
                <td class="text-center">50%</td>
                <td class="text-right">Rp {{ number_format($shuDistribution['dana_anggota'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-center">2</td>
                <td>Dana Cadangan</td>
                <td class="text-center">20%</td>
                <td class="text-right">Rp {{ number_format($shuDistribution['dana_cadangan'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-center">3</td>
                <td>Dana Pegawai</td>
                <td class="text-center">10%</td>
                <td class="text-right">Rp {{ number_format($shuDistribution['dana_pegawai'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-center">4</td>
                <td>Dana Pembangunan Daerah Kerja</td>
                <td class="text-center">5%</td>
                <td class="text-right">Rp {{ number_format($shuDistribution['dana_pembangunan_daerah_kerja'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-center">5</td>
                <td>Dana Sosial</td>
                <td class="text-center">5%</td>
                <td class="text-right">Rp {{ number_format($shuDistribution['dana_sosial'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-center">6</td>
                <td>Dana Kesejahteraan Pegawai</td>
                <td class="text-center">5%</td>
                <td class="text-right">Rp {{ number_format($shuDistribution['dana_kesejahteraan_pegawai'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-center">7</td>
                <td>Dana Pendidikan</td>
                <td class="text-center">5%</td>
                <td class="text-right">Rp {{ number_format($shuDistribution['dana_pendidikan'], 0, ',', '.') }}</td>
            </tr>
        </tbody>
        <tfoot>
            <tr style="background-color: #f5f5f5; font-weight: bold;">
                <td colspan="3">TOTAL SHU</td>
                <td class="text-right">Rp {{ number_format($shuDistribution['total_shu'], 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
    @else
    <div class="section-title">DISTRIBUSI SHU (SISA HASIL USAHA)</div>
    <div style="background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 20px 0; border-radius: 5px;">
        <strong>Tidak Ada SHU yang Dibagikan</strong><br>
        Karena laba usaha negatif atau nol, tidak ada SHU (Sisa Hasil Usaha) yang dapat dibagikan pada periode ini.
    </div>
    @endif

    <div class="footer">
        <p>Laporan ini dibuat pada: {{ date('d F Y H:i:s') }}</p>
        <p>KOPERASI INDONESIA</p>
    </div>
</body>
</html> 