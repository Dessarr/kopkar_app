<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Angkutan Karyawan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .subtitle {
            font-size: 14px;
            margin-bottom: 10px;
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
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .total-row {
            background-color: #f9f9f9;
            font-weight: bold;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            margin: 20px 0 10px 0;
            color: #333;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">LAPORAN LABA RUGI BUS ANGKUTAN KARYAWAN</div>
        <div class="subtitle">Periode: {{ $tgl_periode_txt }}</div>
        <div class="subtitle">Tanggal Cetak: {{ date('d/m/Y H:i:s') }}</div>
    </div>

    <!-- Penghasilan Jasa Sewa Bus -->
    <div class="section-title">PENGHASILAN JASA SEWA BUS</div>
    <table>
        <thead>
            <tr>
                <th>No Polisi</th>
                <th class="text-right">Jan</th>
                <th class="text-right">Feb</th>
                <th class="text-right">Mar</th>
                <th class="text-right">Apr</th>
                <th class="text-right">May</th>
                <th class="text-right">Jun</th>
                <th class="text-right">Jul</th>
                <th class="text-right">Aug</th>
                <th class="text-right">Sep</th>
                <th class="text-right">Oct</th>
                <th class="text-right">Nov</th>
                <th class="text-right">Dec</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dataBus as $bus)
            <tr>
                <td>{{ $bus->no_polisi }}</td>
                <td class="text-right">{{ number_format($bus->Jan ?? 0) }}</td>
                <td class="text-right">{{ number_format($bus->Feb ?? 0) }}</td>
                <td class="text-right">{{ number_format($bus->Mar ?? 0) }}</td>
                <td class="text-right">{{ number_format($bus->Apr ?? 0) }}</td>
                <td class="text-right">{{ number_format($bus->May ?? 0) }}</td>
                <td class="text-right">{{ number_format($bus->Jun ?? 0) }}</td>
                <td class="text-right">{{ number_format($bus->Jul ?? 0) }}</td>
                <td class="text-right">{{ number_format($bus->Aug ?? 0) }}</td>
                <td class="text-right">{{ number_format($bus->Sep ?? 0) }}</td>
                <td class="text-right">{{ number_format($bus->Oct ?? 0) }}</td>
                <td class="text-right">{{ number_format($bus->Nov ?? 0) }}</td>
                <td class="text-right">{{ number_format($bus->Dec ?? 0) }}</td>
                <td class="text-right">{{ number_format($bus->TOTAL ?? 0) }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td>JUMLAH</td>
                <td class="text-right">{{ number_format($jmlBusTahun->jml_total_jan ?? 0) }}</td>
                <td class="text-right">{{ number_format($jmlBusTahun->jml_total_feb ?? 0) }}</td>
                <td class="text-right">{{ number_format($jmlBusTahun->jml_total_mar ?? 0) }}</td>
                <td class="text-right">{{ number_format($jmlBusTahun->jml_total_apr ?? 0) }}</td>
                <td class="text-right">{{ number_format($jmlBusTahun->jml_total_may ?? 0) }}</td>
                <td class="text-right">{{ number_format($jmlBusTahun->jml_total_jun ?? 0) }}</td>
                <td class="text-right">{{ number_format($jmlBusTahun->jml_total_jul ?? 0) }}</td>
                <td class="text-right">{{ number_format($jmlBusTahun->jml_total_aug ?? 0) }}</td>
                <td class="text-right">{{ number_format($jmlBusTahun->jml_total_sep ?? 0) }}</td>
                <td class="text-right">{{ number_format($jmlBusTahun->jml_total_oct ?? 0) }}</td>
                <td class="text-right">{{ number_format($jmlBusTahun->jml_total_nov ?? 0) }}</td>
                <td class="text-right">{{ number_format($jmlBusTahun->jml_total_dec ?? 0) }}</td>
                <td class="text-right">{{ number_format($jmlBus->jml_total ?? 0) }}</td>
            </tr>
            <tr>
                <td>Pajak (2%)</td>
                <td class="text-right">{{ number_format($jmlBusTahunPajak->jml_total_jan_pajak ?? 0) }}</td>
                <td class="text-right">{{ number_format($jmlBusTahunPajak->jml_total_feb_pajak ?? 0) }}</td>
                <td class="text-right">{{ number_format($jmlBusTahunPajak->jml_total_mar_pajak ?? 0) }}</td>
                <td class="text-right">{{ number_format($jmlBusTahunPajak->jml_total_apr_pajak ?? 0) }}</td>
                <td class="text-right">{{ number_format($jmlBusTahunPajak->jml_total_may_pajak ?? 0) }}</td>
                <td class="text-right">{{ number_format($jmlBusTahunPajak->jml_total_jun_pajak ?? 0) }}</td>
                <td class="text-right">{{ number_format($jmlBusTahunPajak->jml_total_jul_pajak ?? 0) }}</td>
                <td class="text-right">{{ number_format($jmlBusTahunPajak->jml_total_aug_pajak ?? 0) }}</td>
                <td class="text-right">{{ number_format($jmlBusTahunPajak->jml_total_sep_pajak ?? 0) }}</td>
                <td class="text-right">{{ number_format($jmlBusTahunPajak->jml_total_oct_pajak ?? 0) }}</td>
                <td class="text-right">{{ number_format($jmlBusTahunPajak->jml_total_nov_pajak ?? 0) }}</td>
                <td class="text-right">{{ number_format($jmlBusTahunPajak->jml_total_dec_pajak ?? 0) }}</td>
                <td class="text-right">{{ number_format(($jmlBus->jml_total ?? 0) * 0.02) }}</td>
            </tr>
            <tr class="total-row">
                <td>Setelah Pajak</td>
                <td class="text-right">{{ number_format(($jmlBusTahun->jml_total_jan ?? 0) - ($jmlBusTahunPajak->jml_total_jan_pajak ?? 0)) }}</td>
                <td class="text-right">{{ number_format(($jmlBusTahun->jml_total_feb ?? 0) - ($jmlBusTahunPajak->jml_total_feb_pajak ?? 0)) }}</td>
                <td class="text-right">{{ number_format(($jmlBusTahun->jml_total_mar ?? 0) - ($jmlBusTahunPajak->jml_total_mar_pajak ?? 0)) }}</td>
                <td class="text-right">{{ number_format(($jmlBusTahun->jml_total_apr ?? 0) - ($jmlBusTahunPajak->jml_total_apr_pajak ?? 0)) }}</td>
                <td class="text-right">{{ number_format(($jmlBusTahun->jml_total_may ?? 0) - ($jmlBusTahunPajak->jml_total_may_pajak ?? 0)) }}</td>
                <td class="text-right">{{ number_format(($jmlBusTahun->jml_total_jun ?? 0) - ($jmlBusTahunPajak->jml_total_jun_pajak ?? 0)) }}</td>
                <td class="text-right">{{ number_format(($jmlBusTahun->jml_total_jul ?? 0) - ($jmlBusTahunPajak->jml_total_jul_pajak ?? 0)) }}</td>
                <td class="text-right">{{ number_format(($jmlBusTahun->jml_total_aug ?? 0) - ($jmlBusTahunPajak->jml_total_aug_pajak ?? 0)) }}</td>
                <td class="text-right">{{ number_format(($jmlBusTahun->jml_total_sep ?? 0) - ($jmlBusTahunPajak->jml_total_sep_pajak ?? 0)) }}</td>
                <td class="text-right">{{ number_format(($jmlBusTahun->jml_total_oct ?? 0) - ($jmlBusTahunPajak->jml_total_oct_pajak ?? 0)) }}</td>
                <td class="text-right">{{ number_format(($jmlBusTahun->jml_total_nov ?? 0) - ($jmlBusTahunPajak->jml_total_nov_pajak ?? 0)) }}</td>
                <td class="text-right">{{ number_format(($jmlBusTahun->jml_total_dec ?? 0) - ($jmlBusTahunPajak->jml_total_dec_pajak ?? 0)) }}</td>
                <td class="text-right">{{ number_format(($jmlBus->jml_total ?? 0) - (($jmlBus->jml_total ?? 0) * 0.02)) }}</td>
            </tr>
        </tbody>
    </table>

    @if($dataOperasional->count() > 0)
    <div class="page-break"></div>
    
    <!-- Biaya Operasional -->
    <div class="section-title">BIAYA OPERASIONAL</div>
    <table>
        <thead>
            <tr>
                <th>Biaya</th>
                <th class="text-right">Jan</th>
                <th class="text-right">Feb</th>
                <th class="text-right">Mar</th>
                <th class="text-right">Apr</th>
                <th class="text-right">May</th>
                <th class="text-right">Jun</th>
                <th class="text-right">Jul</th>
                <th class="text-right">Aug</th>
                <th class="text-right">Sep</th>
                <th class="text-right">Oct</th>
                <th class="text-right">Nov</th>
                <th class="text-right">Dec</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dataOperasional as $operasional)
            <tr>
                <td>-</td>
                <td class="text-right">{{ number_format($operasional->Jan ?? 0) }}</td>
                <td class="text-right">{{ number_format($operasional->Feb ?? 0) }}</td>
                <td class="text-right">{{ number_format($operasional->Mar ?? 0) }}</td>
                <td class="text-right">{{ number_format($operasional->Apr ?? 0) }}</td>
                <td class="text-right">{{ number_format($operasional->May ?? 0) }}</td>
                <td class="text-right">{{ number_format($operasional->Jun ?? 0) }}</td>
                <td class="text-right">{{ number_format($operasional->Jul ?? 0) }}</td>
                <td class="text-right">{{ number_format($operasional->Aug ?? 0) }}</td>
                <td class="text-right">{{ number_format($operasional->Sep ?? 0) }}</td>
                <td class="text-right">{{ number_format($operasional->Oct ?? 0) }}</td>
                <td class="text-right">{{ number_format($operasional->Nov ?? 0) }}</td>
                <td class="text-right">{{ number_format($operasional->Dec ?? 0) }}</td>
                <td class="text-right">{{ number_format($operasional->TOTAL ?? 0) }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td>JUMLAH</td>
                <td class="text-right">{{ number_format($jmlOperasionalTahun->jml_total_jan ?? 0) }}</td>
                <td class="text-right">{{ number_format($jmlOperasionalTahun->jml_total_feb ?? 0) }}</td>
                <td class="text-right">{{ number_format($jmlOperasionalTahun->jml_total_mar ?? 0) }}</td>
                <td class="text-right">{{ number_format($jmlOperasionalTahun->jml_total_apr ?? 0) }}</td>
                <td class="text-right">{{ number_format($jmlOperasionalTahun->jml_total_may ?? 0) }}</td>
                <td class="text-right">{{ number_format($jmlOperasionalTahun->jml_total_jun ?? 0) }}</td>
                <td class="text-right">{{ number_format($jmlOperasionalTahun->jml_total_jul ?? 0) }}</td>
                <td class="text-right">{{ number_format($jmlOperasionalTahun->jml_total_aug ?? 0) }}</td>
                <td class="text-right">{{ number_format($jmlOperasionalTahun->jml_total_sep ?? 0) }}</td>
                <td class="text-right">{{ number_format($jmlOperasionalTahun->jml_total_oct ?? 0) }}</td>
                <td class="text-right">{{ number_format($jmlOperasionalTahun->jml_total_nov ?? 0) }}</td>
                <td class="text-right">{{ number_format($jmlOperasionalTahun->jml_total_dec ?? 0) }}</td>
                <td class="text-right">{{ number_format($jmlOperasional->jml_total ?? 0) }}</td>
            </tr>
        </tbody>
    </table>
    @endif

    @if($dataAdmin->count() > 0)
    <div class="page-break"></div>
    
    <!-- Biaya Admin -->
    <div class="section-title">BIAYA ADMIN</div>
    <table>
        <thead>
            <tr>
                <th>Biaya</th>
                <th class="text-right">Jan</th>
                <th class="text-right">Feb</th>
                <th class="text-right">Mar</th>
                <th class="text-right">Apr</th>
                <th class="text-right">May</th>
                <th class="text-right">Jun</th>
                <th class="text-right">Jul</th>
                <th class="text-right">Aug</th>
                <th class="text-right">Sep</th>
                <th class="text-right">Oct</th>
                <th class="text-right">Nov</th>
                <th class="text-right">Dec</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dataAdmin as $admin)
            <tr>
                <td>-</td>
                <td class="text-right">{{ number_format($admin->Jan ?? 0) }}</td>
                <td class="text-right">{{ number_format($admin->Feb ?? 0) }}</td>
                <td class="text-right">{{ number_format($admin->Mar ?? 0) }}</td>
                <td class="text-right">{{ number_format($admin->Apr ?? 0) }}</td>
                <td class="text-right">{{ number_format($admin->May ?? 0) }}</td>
                <td class="text-right">{{ number_format($admin->Jun ?? 0) }}</td>
                <td class="text-right">{{ number_format($admin->Jul ?? 0) }}</td>
                <td class="text-right">{{ number_format($admin->Aug ?? 0) }}</td>
                <td class="text-right">{{ number_format($admin->Sep ?? 0) }}</td>
                <td class="text-right">{{ number_format($admin->Oct ?? 0) }}</td>
                <td class="text-right">{{ number_format($admin->Nov ?? 0) }}</td>
                <td class="text-right">{{ number_format($admin->Dec ?? 0) }}</td>
                <td class="text-right">{{ number_format($admin->TOTAL ?? 0) }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td>JUMLAH</td>
                <td class="text-right">{{ number_format($jmlAdminTahun->jml_total_jan ?? 0) }}</td>
                <td class="text-right">{{ number_format($jmlAdminTahun->jml_total_feb ?? 0) }}</td>
                <td class="text-right">{{ number_format($jmlAdminTahun->jml_total_mar ?? 0) }}</td>
                <td class="text-right">{{ number_format($jmlAdminTahun->jml_total_apr ?? 0) }}</td>
                <td class="text-right">{{ number_format($jmlAdminTahun->jml_total_may ?? 0) }}</td>
                <td class="text-right">{{ number_format($jmlAdminTahun->jml_total_jun ?? 0) }}</td>
                <td class="text-right">{{ number_format($jmlAdminTahun->jml_total_jul ?? 0) }}</td>
                <td class="text-right">{{ number_format($jmlAdminTahun->jml_total_aug ?? 0) }}</td>
                <td class="text-right">{{ number_format($jmlAdminTahun->jml_total_sep ?? 0) }}</td>
                <td class="text-right">{{ number_format($jmlAdminTahun->jml_total_oct ?? 0) }}</td>
                <td class="text-right">{{ number_format($jmlAdminTahun->jml_total_nov ?? 0) }}</td>
                <td class="text-right">{{ number_format($jmlAdminTahun->jml_total_dec ?? 0) }}</td>
                <td class="text-right">{{ number_format($jmlAdmin->jml_total ?? 0) }}</td>
            </tr>
        </tbody>
    </table>
    @endif

    <div class="page-break"></div>
    
    <!-- Perhitungan Laba Usaha -->
    <div class="section-title">PERHITUNGAN LABA USAHA</div>
    <table>
        <tbody>
            <tr>
                <td class="text-right" style="width: 70%;">Pendapatan Kotor</td>
                <td class="text-right">{{ number_format($labaUsaha->pendapatan_kotor) }}</td>
            </tr>
            <tr>
                <td class="text-right">Pajak (2%)</td>
                <td class="text-right">- {{ number_format($labaUsaha->pajak_2_persen) }}</td>
            </tr>
            <tr class="total-row">
                <td class="text-right"><strong>Pendapatan Setelah Pajak</strong></td>
                <td class="text-right"><strong>{{ number_format($labaUsaha->pendapatan_setelah_pajak) }}</strong></td>
            </tr>
            <tr>
                <td class="text-right">Biaya Operasional</td>
                <td class="text-right">- {{ number_format($labaUsaha->biaya_operasional) }}</td>
            </tr>
            <tr>
                <td class="text-right">Biaya Administrasi</td>
                <td class="text-right">- {{ number_format($labaUsaha->biaya_administrasi) }}</td>
            </tr>
            <tr class="total-row">
                <td class="text-right"><strong>Total Biaya</strong></td>
                <td class="text-right"><strong>- {{ number_format($labaUsaha->total_biaya) }}</strong></td>
            </tr>
            <tr style="background-color: #e6f7e6; font-weight: bold; font-size: 14px;">
                <td class="text-right"><strong>LABA USAHA</strong></td>
                <td class="text-right"><strong>{{ $labaUsaha->laba_usaha >= 0 ? '+' : '' }}{{ number_format($labaUsaha->laba_usaha) }}</strong></td>
            </tr>
        </tbody>
    </table>

    @if($labaUsaha->laba_usaha > 0)
    <div class="page-break"></div>
    
    <!-- Distribusi SHU -->
    <div class="section-title">DISTRIBUSI SHU (SISA HASIL USAHA)</div>
    <table>
        <thead>
            <tr>
                <th style="width: 70%;">Jenis Dana</th>
                <th class="text-right">Persentase</th>
                <th class="text-right">Jumlah (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Dana Anggota</td>
                <td class="text-right">50%</td>
                <td class="text-right">{{ number_format($shuDistribution->dana_anggota) }}</td>
            </tr>
            <tr>
                <td>Dana Cadangan</td>
                <td class="text-right">20%</td>
                <td class="text-right">{{ number_format($shuDistribution->dana_cadangan) }}</td>
            </tr>
            <tr>
                <td>Dana Pegawai</td>
                <td class="text-right">10%</td>
                <td class="text-right">{{ number_format($shuDistribution->dana_pegawai) }}</td>
            </tr>
            <tr>
                <td>Dana Pembangunan Daerah Kerja</td>
                <td class="text-right">5%</td>
                <td class="text-right">{{ number_format($shuDistribution->dana_pembangunan_daerah_kerja) }}</td>
            </tr>
            <tr>
                <td>Dana Sosial</td>
                <td class="text-right">5%</td>
                <td class="text-right">{{ number_format($shuDistribution->dana_sosial) }}</td>
            </tr>
            <tr>
                <td>Dana Kesejahteraan Pegawai</td>
                <td class="text-right">5%</td>
                <td class="text-right">{{ number_format($shuDistribution->dana_kesejahteraan_pegawai) }}</td>
            </tr>
            <tr>
                <td>Dana Pendidikan</td>
                <td class="text-right">5%</td>
                <td class="text-right">{{ number_format($shuDistribution->dana_pendidikan) }}</td>
            </tr>
            <tr class="total-row" style="background-color: #e6f7e6; font-weight: bold; font-size: 14px;">
                <td><strong>TOTAL SHU DIBAGIKAN</strong></td>
                <td class="text-right"><strong>100%</strong></td>
                <td class="text-right"><strong>{{ number_format($shuDistribution->total_shu) }}</strong></td>
            </tr>
        </tbody>
    </table>
    @else
    <div class="page-break"></div>
    
    <!-- Tidak Ada SHU -->
    <div class="section-title">DISTRIBUSI SHU (SISA HASIL USAHA)</div>
    <div style="background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 20px; text-align: center; margin: 20px 0;">
        <h4 style="color: #856404; margin: 0 0 10px 0;">TIDAK ADA SHU YANG DIBAGIKAN</h4>
        <p style="color: #856404; margin: 0;">Karena laba usaha negatif atau nol, tidak ada SHU (Sisa Hasil Usaha) yang dapat dibagikan pada periode ini.</p>
    </div>
    @endif
</body>
</html> 