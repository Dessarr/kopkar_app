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
    <h2 class="text-center">LAPORAN PENGELUARAN PINJAMAN</h2>
    <p><b>Periode:</b> {{ $tgl_dari }} s/d {{ $tgl_samp }}</p>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal Pinjam</th>
                <th>Nama</th>
                <th>ID</th>
                <th class="text-right">Pokok Pinjaman</th>
                <th>Lama Pinjaman</th>
                <th>Status Lunas</th>
                <th class="text-right">Pokok Angsuran</th>
                <th class="text-right">Bunga</th>
                <th class="text-right">Jumlah Angsuran</th>
                <th class="text-right">Tagihan</th>
                <th class="text-right">Total Bunga</th>
                <th class="text-right">Total Denda</th>
                <th class="text-right">Total Biaya Adm</th>
                <th class="text-right">Dibayar</th>
                <th class="text-right">Sisa Tagihan</th>
                <th>Alamat</th>
                <th>No. Telp</th>
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
                <td>{{ $row['lunas'] }}</td>
                <td class="text-right">{{ number_format($row['pokok_angsuran']) }}</td>
                <td class="text-right">{{ number_format($row['pokok_bunga']) }}</td>
                <td class="text-right">{{ number_format($row['ags_per_bulan']) }}</td>
                <td class="text-right">{{ number_format($row['tagihan']) }}</td>
                <td class="text-right">{{ number_format($row['jml_bunga']) }}</td>
                <td class="text-right">{{ number_format($row['jml_denda']) }}</td>
                <td class="text-right">{{ number_format($row['jml_adm']) }}</td>
                <td class="text-right">{{ number_format($row['jml_bayar']) }}</td>
                <td class="text-right">{{ number_format($row['sisa_tagihan']) }}</td>
                <td>{{ $row['alamat'] }}</td>
                <td>{{ $row['notelp'] }}</td>
            </tr>
            @endforeach
            <tr class="font-bold">
                <td colspan="4" class="text-center">TOTAL</td>
                <td class="text-right">{{ number_format($total['total_pinjaman']) }}</td>
                <td colspan="5"></td>
                <td class="text-right">{{ number_format($total['total_tagihan']) }}</td>
                <td colspan="3"></td>
                <td class="text-right">{{ number_format($total['total_dibayar']) }}</td>
                <td class="text-right">{{ number_format($total['total_sisa_tagihan']) }}</td>
                <td colspan="2"></td>
            </tr>
        </tbody>
    </table>
</body>
</html> 