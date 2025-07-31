<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Transaksi Kas</title>
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
        .header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }
        .header p {
            margin: 5px 0;
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
        .footer {
            margin-top: 30px;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN TRANSAKSI KAS</h1>
        <p>Periode: {{ $tgl_periode_txt }}</p>
        <p>Jenis: {{ $jenis_transaksi === 'semua' ? 'Semua Transaksi' : ($jenis_transaksi === 'pemasukan' ? 'Pemasukan' : 'Pengeluaran') }}</p>
        <p>Tanggal Cetak: {{ date('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 15%">Tanggal</th>
                <th style="width: 35%">Keterangan</th>
                <th style="width: 20%">Kas</th>
                <th style="width: 10%">Jenis</th>
                <th style="width: 15%" class="text-right">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dataTransaksi as $index => $transaksi)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($transaksi->tgl)->format('d/m/Y') }}</td>
                <td>{{ $transaksi->keterangan }}</td>
                <td>{{ $transaksi->nama_kas }}</td>
                <td class="text-center">
                    @if($transaksi->transaksi === '48')
                        Pemasukan
                    @else
                        Pengeluaran
                    @endif
                </td>
                <td class="text-right">
                    @if($transaksi->transaksi === '48')
                        Rp {{ number_format($transaksi->kredit) }}
                    @else
                        Rp {{ number_format($transaksi->debet) }}
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Tidak ada data transaksi untuk kriteria yang dipilih</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($dataTransaksi->count() > 0)
    <div class="footer">
        <p><strong>Total Data:</strong> {{ $dataTransaksi->count() }} transaksi</p>
    </div>
    @endif
</body>
</html> 