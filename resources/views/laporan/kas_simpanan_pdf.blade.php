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
    <h2 class="text-center">LAPORAN KAS SIMPANAN</h2>
    <p><b>Periode:</b> {{ $periode }}</p>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>ID</th>
                <th>Nama</th>
                @foreach($jenisSimpanan as $jenis)
                    <th class="text-right">{{ $jenis->jns_simpan }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
            <tr>
                <td>{{ $row['no'] }}</td>
                <td>{{ $row['id'] }}</td>
                <td>{{ $row['nama'] }}</td>
                @foreach($jenisSimpanan as $jenis)
                    <td class="text-right">{{ number_format($row[$jenis->id]) }}</td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html> 