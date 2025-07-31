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
    <h2 class="text-center">LAPORAN SHU</h2>
    <p><b>Periode:</b> {{ $tgl_dari }} s/d {{ $tgl_samp }}</p>
    <table>
        <thead>
            <tr>
                <th>Keterangan</th>
                <th class="text-right">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="font-bold">Total Pinjaman</td>
                <td class="text-right">{{ number_format($data['jml_pinjaman']) }}</td>
            </tr>
            <tr>
                <td class="font-bold">Total Tagihan</td>
                <td class="text-right">{{ number_format($data['jml_tagihan']) }}</td>
            </tr>
            <tr>
                <td class="font-bold">Total Angsuran</td>
                <td class="text-right">{{ number_format($data['jml_angsuran']) }}</td>
            </tr>
            <tr>
                <td class="font-bold">Total Denda</td>
                <td class="text-right">{{ number_format($data['jml_denda']) }}</td>
            </tr>
            <tr class="font-bold">
                <td>Pendapatan</td>
                <td></td>
            </tr>
            @foreach($data['pendapatan_rows'] as $row)
            <tr>
                <td>{{ $row['nama'] }}</td>
                <td class="text-right">{{ number_format($row['jumlah']) }}</td>
            </tr>
            @endforeach
            <tr class="font-bold">
                <td>Total Pendapatan</td>
                <td class="text-right">{{ number_format($data['total_pendapatan']) }}</td>
            </tr>
            <tr class="font-bold">
                <td>Biaya</td>
                <td></td>
            </tr>
            @foreach($data['biaya_rows'] as $row)
            <tr>
                <td>{{ $row['nama'] }}</td>
                <td class="text-right">{{ number_format($row['jumlah']) }}</td>
            </tr>
            @endforeach
            <tr class="font-bold">
                <td>Total Biaya</td>
                <td class="text-right">{{ number_format($data['total_biaya']) }}</td>
            </tr>
            <tr class="font-bold" style="background: #d4f5d4;">
                <td>SHU</td>
                <td class="text-right">{{ number_format($data['shu']) }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html> 