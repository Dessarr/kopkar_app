<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Pengeluaran Kas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
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
        .periode {
            margin-bottom: 15px;
            font-weight: bold;
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
            text-align: center;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .total-row {
            font-weight: bold;
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 30px;
            text-align: right;
        }
        .signature {
            margin-top: 50px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN PENGELUARAN KAS</h1>
        <p>KOPERASI KARYAWAN</p>
        <p>Periode: {{ $periode }}</p>
    </div>

    <div class="periode">
        Periode: {{ \Carbon\Carbon::parse($periode . '-01')->format('F Y') }}
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Tanggal</th>
                <th width="25%">Keterangan</th>
                <th width="20%">Kas Asal</th>
                <th width="15%">Jumlah</th>
                <th width="10%">User</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dataKas as $index => $kas)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($kas->tgl)->format('d/m/Y') }}</td>
                <td>{{ $kas->keterangan }}</td>
                <td>{{ $kas->kasAsal->nama ?? '-' }}</td>
                <td class="text-right">Rp{{ number_format($kas->kredit, 0, ',', '.') }}</td>
                <td class="text-center">{{ $kas->user }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Tidak ada data pengeluaran kas</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="4" class="text-right"><strong>TOTAL PENGELUARAN:</strong></td>
                <td class="text-right"><strong>Rp{{ number_format($totalPengeluaran, 0, ',', '.') }}</strong></td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}</p>
        <p>Total Transaksi: {{ $dataKas->count() }} transaksi</p>
    </div>

    <div class="signature">
        <table style="width: 100%; border: none;">
            <tr style="border: none;">
                <td style="border: none; width: 50%; text-align: center;">
                    <p>Dibuat oleh,</p>
                    <br><br><br>
                    <p>_________________</p>
                    <p>Admin</p>
                </td>
                <td style="border: none; width: 50%; text-align: center;">
                    <p>Disetujui oleh,</p>
                    <br><br><br>
                    <p>_________________</p>
                    <p>Manager</p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
