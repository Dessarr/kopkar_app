<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Biaya Usaha Toserda</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
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
            margin: 0;
            color: #333;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .summary {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .summary table {
            width: 100%;
            border-collapse: collapse;
        }
        .summary td {
            padding: 5px;
            border: none;
        }
        .summary .label {
            font-weight: bold;
            width: 150px;
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
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .text-bold {
            font-weight: bold;
        }
        .total-row {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN BIAYA USAHA TOSERDA</h1>
        <p>Koperasi Karyawan</p>
        <p>Periode: {{ request('tgl_dari') ? \Carbon\Carbon::parse(request('tgl_dari'))->format('d/m/Y') : '-' }} - {{ request('tgl_sampai') ? \Carbon\Carbon::parse(request('tgl_sampai'))->format('d/m/Y') : '-' }}</p>
        <p>Tanggal Cetak: {{ date('d/m/Y H:i') }}</p>
    </div>

    <div class="summary">
        <table>
            <tr>
                <td class="label">Total Transaksi:</td>
                <td>{{ $dataKas->count() }} transaksi</td>
            </tr>
            <tr>
                <td class="label">Total Biaya:</td>
                <td class="text-bold">Rp{{ number_format($totalBiayaUsaha, 0, ',', '.') }}</td>
            </tr>
            @if(request('kode_transaksi'))
            <tr>
                <td class="label">Filter Kode:</td>
                <td>TKD{{ str_pad(request('kode_transaksi'), 5, '0', STR_PAD_LEFT) }}</td>
            </tr>
            @endif
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 5%">No</th>
                <th class="text-center" style="width: 15%">Kode Transaksi</th>
                <th class="text-center" style="width: 15%">Tanggal</th>
                <th style="width: 25%">Keterangan</th>
                <th style="width: 15%">Dari Kas</th>
                <th style="width: 15%">Jenis Transaksi</th>
                <th class="text-right" style="width: 10%">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dataKas as $index => $tr)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">
                    <strong>TKD{{ str_pad($tr->id, 5, '0', STR_PAD_LEFT) }}</strong>
                </td>
                <td class="text-center">
                    {{ $tr->tgl_catat ? \Carbon\Carbon::parse($tr->tgl_catat)->format('d/m/Y H:i') : '-' }}
                </td>
                <td>{{ $tr->keterangan ?? '-' }}</td>
                <td>{{ optional($tr->dariKas)->nama ?? '-' }}</td>
                <td>
                    @php
                    $akunNames = [
                        '122' => 'Beban Gaji Karyawan',
                        '123' => 'Biaya Operasional',
                        '124' => 'Bahan Habis Pakai',
                        '125' => 'Insentive Karyawan'
                    ];
                    echo $akunNames[$tr->jns_trans] ?? $tr->jns_trans;
                    @endphp
                </td>
                <td class="text-right text-bold">
                    Rp{{ number_format($tr->jumlah ?? 0, 0, ',', '.') }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center" style="padding: 20px;">
                    Tidak ada data biaya usaha untuk periode yang dipilih
                </td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="6" class="text-right text-bold">TOTAL BIAYA USAHA:</td>
                <td class="text-right text-bold">Rp{{ number_format($totalBiayaUsaha, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Dicetak pada: {{ date('d/m/Y H:i:s') }}</p>
        <p>Oleh: {{ Auth::user()->name ?? 'System' }}</p>
    </div>
</body>
</html>
