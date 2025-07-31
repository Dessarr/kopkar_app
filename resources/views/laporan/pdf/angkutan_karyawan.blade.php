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
        <div class="title">LAPORAN BUS ANGKUTAN KARYAWAN</div>
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
                <td>{{ $operasional->nama_akun }}</td>
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
                <td>{{ $admin->nama_akun }}</td>
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
</body>
</html> 