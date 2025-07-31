<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #333; padding: 6px; text-align: left; }
        th { background: #eee; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
    </style>
</head>
<body>
    <h2 class="text-center">LAPORAN BUKU BESAR</h2>
    <p><b>Kas:</b> {{ $kas->nama }}<br>
    <b>Periode:</b> {{ $periode }}</p>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Jenis Transaksi</th>
                <th>Keterangan</th>
                <th class="text-right">Debet</th>
                <th class="text-right">Kredit</th>
                <th class="text-right">Saldo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
            <tr>
                <td>{{ $row['no'] }}</td>
                <td>{{ \Carbon\Carbon::parse($row['tanggal'])->format('d/m/Y') }}</td>
                <td>{{ $row['jenis_transaksi'] }}</td>
                <td>{{ $row['keterangan'] }}</td>
                <td class="text-right">{{ number_format($row['debet']) }}</td>
                <td class="text-right">{{ number_format($row['kredit']) }}</td>
                <td class="text-right font-bold">{{ number_format($row['saldo']) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html> 