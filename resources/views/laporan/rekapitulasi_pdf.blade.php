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
    <h2 class="text-center">LAPORAN REKAPITULASI</h2>
    <p><b>Periode:</b> {{ $periode }}</p>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Tagihan Hari Ini</th>
                <th class="text-right">Target Pokok</th>
                <th class="text-right">Target Bunga</th>
                <th>Tagihan Masuk</th>
                <th class="text-right">Realisasi Pokok</th>
                <th class="text-right">Realisasi Bunga</th>
                <th>Tagihan Bermasalah</th>
                <th class="text-right">Tidak Bayar Pokok</th>
                <th class="text-right">Tidak Bayar Bunga</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
            <tr>
                <td>{{ $row['no'] }}</td>
                <td>{{ \Carbon\Carbon::parse($row['tanggal'])->format('d/m/Y') }}</td>
                <td>{{ $row['jml_tagihan'] }}</td>
                <td class="text-right">{{ number_format($row['target_pokok']) }}</td>
                <td class="text-right">{{ number_format($row['target_bunga']) }}</td>
                <td>{{ $row['tagihan_masuk'] }}</td>
                <td class="text-right">{{ number_format($row['realisasi_pokok']) }}</td>
                <td class="text-right">{{ number_format($row['realisasi_bunga']) }}</td>
                <td>{{ $row['tagihan_bermasalah'] }}</td>
                <td class="text-right">{{ number_format($row['tidak_bayar_pokok']) }}</td>
                <td class="text-right">{{ number_format($row['tidak_bayar_bunga']) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html> 