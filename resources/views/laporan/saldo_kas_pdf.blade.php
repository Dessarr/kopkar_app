<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #333; padding: 4px; text-align: left; }
        th { background: #eee; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
    </style>
</head>
<body>
    <h2 class="text-center">LAPORAN SALDO KAS</h2>
    <p><b>Periode:</b> {{ $periode }}</p>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Kas</th>
                <th class="text-right">Saldo</th>
            </tr>
        </thead>
        <tbody>
            <tr class="font-bold">
                <td colspan="2" class="text-center">SALDO PERIODE SEBELUMNYA</td>
                <td class="text-right">{{ number_format($saldo_sblm) }}</td>
            </tr>
            @foreach($data as $row)
            <tr>
                <td>{{ $row['no'] }}</td>
                <td>{{ $row['nama'] }}</td>
                <td class="text-right">{{ number_format($row['saldo']) }}</td>
            </tr>
            @endforeach
            <tr class="font-bold">
                <td colspan="2" class="text-center">JUMLAH</td>
                <td class="text-right">{{ number_format($total) }}</td>
            </tr>
            <tr class="font-bold">
                <td colspan="2" class="text-center">TOTAL SALDO</td>
                <td class="text-right">{{ number_format($total + $saldo_sblm) }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html> 