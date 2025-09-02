<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan Toserda</title>
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
            font-size: 18px;
            font-weight: bold;
        }
        .header p {
            margin: 5px 0;
            font-size: 12px;
        }
        .info-section {
            margin-bottom: 20px;
        }
        .info-row {
            display: flex;
            margin-bottom: 5px;
        }
        .info-label {
            width: 120px;
            font-weight: bold;
        }
        .info-value {
            flex: 1;
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
            font-size: 10px;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
            text-align: center;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .text-left {
            text-align: left;
        }
        .total-row {
            font-weight: bold;
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 10px;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN PENJUALAN TOSERDA</h1>
        <p>KOPERASI KARYAWAN</p>
        <p>Periode: {{ request('tgl_dari') ? \Carbon\Carbon::parse(request('tgl_dari'))->format('d/m/Y') : 'Semua' }} - {{ request('tgl_sampai') ? \Carbon\Carbon::parse(request('tgl_sampai'))->format('d/m/Y') : 'Semua' }}</p>
        <p>Tanggal Cetak: {{ date('d/m/Y H:i') }}</p>
    </div>

    <div class="info-section">
        <div class="info-row">
            <div class="info-label">Total Transaksi:</div>
            <div class="info-value">{{ $dataKas->count() }} transaksi</div>
        </div>
        <div class="info-row">
            <div class="info-label">Total Penjualan:</div>
            <div class="info-value">Rp{{ number_format($totalPenjualan, 0, ',', '.') }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 15%">Kode Transaksi</th>
                <th style="width: 15%">Tanggal</th>
                <th style="width: 25%">Keterangan</th>
                <th style="width: 15%">Untuk Kas</th>
                <th style="width: 15%">Akun</th>
                <th style="width: 15%">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dataKas as $index => $tr)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td class="text-center">TKD{{ str_pad($tr->id, 5, '0', STR_PAD_LEFT) }}</td>
                <td class="text-center">{{ $tr->tgl_catat ? \Carbon\Carbon::parse($tr->tgl_catat)->format('d/m/Y H:i') : '-' }}</td>
                <td class="text-left">{{ $tr->keterangan ?? '-' }}</td>
                <td class="text-left">{{ optional($tr->untukKas)->nama ?? '-' }}</td>
                <td class="text-left">
                    @php
                    $akunNames = [
                    '112' => 'Penjualan',
                    '113' => 'Penjualan Tempo',
                    '114' => 'Retur Penjualan',
                    '115' => 'Pendapatan Service',
                    '116' => 'Potongan Penjualan'
                    ];
                    echo $akunNames[$tr->jns_trans] ?? $tr->jns_trans;
                    @endphp
                </td>
                <td class="text-right">Rp{{ number_format($tr->jumlah ?? 0, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">Tidak ada data penjualan toserda</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="6" class="text-right"><strong>TOTAL PENJUALAN:</strong></td>
                <td class="text-right"><strong>Rp{{ number_format($totalPenjualan, 0, ',', '.') }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Dicetak pada: {{ date('d/m/Y H:i:s') }}</p>
        <p>Oleh: {{ auth()->user()->name ?? 'System' }}</p>
    </div>
</body>
</html>
