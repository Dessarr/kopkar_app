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
    <h2 class="text-center">LAPORAN ANGSURAN PINJAMAN</h2>
    <p><b>Periode:</b> {{ $tgl_dari }} s/d {{ $tgl_samp }}</p>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal Pinjam</th>
                <th>Nama</th>
                <th>ID</th>
                <th class="text-right">Pinjaman Awal</th>
                <th>JW</th>
                <th>%</th>
                <th class="text-right">Saldo Pinjaman</th>
                <th class="text-right">Pokok</th>
                <th class="text-right">Bunga</th>
                <th class="text-right">Denda</th>
                <th class="text-right">Jumlah</th>
                <th class="text-right">Saldo Akhir</th>
                <th>Angsuran Ke</th>
                <th>Tgl. Bayar</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
            <tr>
                <td>{{ $row['no'] }}</td>
                <td>{{ \Carbon\Carbon::parse($row['tgl_pinjam'])->format('d/m/Y') }}</td>
                <td>{{ $row['nama'] }}</td>
                <td>{{ $row['id'] }}</td>
                <td class="text-right">{{ number_format($row['jumlah']) }}</td>
                <td>{{ $row['lama_angsuran'] }}</td>
                <td>{{ $row['jumlah_bunga'] }}</td>
                <td class="text-right">{{ number_format($row['saldo_pinjaman']) }}</td>
                <td class="text-right">{{ number_format($row['pokok']) }}</td>
                <td class="text-right">{{ number_format($row['bunga']) }}</td>
                <td class="text-right">{{ number_format($row['denda']) }}</td>
                <td class="text-right">{{ number_format($row['jumlah_angsuran']) }}</td>
                <td class="text-right">{{ number_format($row['saldo_akhir']) }}</td>
                <td>{{ $row['angsuran_ke'] }}</td>
                <td>{{ \Carbon\Carbon::parse($row['tgl_bayar'])->format('d/m/Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html> 