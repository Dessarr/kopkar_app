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
    <h2 class="text-center">LAPORAN NERACA SALDO</h2>
    <p><b>Periode:</b> {{ $tgl_dari }} s/d {{ $tgl_samp }}</p>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Akun</th>
                <th class="text-right">Debet</th>
                <th class="text-right">Kredit</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['rows'] as $row)
            <tr>
                <td>{{ $row['no'] }}</td>
                <td>{{ $row['nama'] }}</td>
                <td class="text-right">{{ number_format($row['debet']) }}</td>
                <td class="text-right">{{ number_format($row['kredit']) }}</td>
            </tr>
            @endforeach
            <tr class="font-bold">
                <td colspan="2" class="text-center">JUMLAH</td>
                <td class="text-right">{{ number_format($data['totalDebet']) }}</td>
                <td class="text-right">{{ number_format($data['totalKredit']) }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html> 