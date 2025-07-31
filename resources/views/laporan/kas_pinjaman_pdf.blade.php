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
    <h2 class="text-center">LAPORAN KAS PINJAMAN</h2>
    <p><b>Periode:</b> {{ $tgl_dari }} s/d {{ $tgl_samp }}</p>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Keterangan</th>
                <th class="text-right">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>Pokok Pinjaman</td>
                <td class="text-right">{{ number_format($data['jml_pinjaman']) }}</td>
            </tr>
            <tr>
                <td>2</td>
                <td>Tagihan Pinjaman</td>
                <td class="text-right">{{ number_format($data['jml_tagihan']) }}</td>
            </tr>
            <tr>
                <td>3</td>
                <td>Tagihan Denda</td>
                <td class="text-right">{{ number_format($data['jml_denda']) }}</td>
            </tr>
            <tr class="font-bold">
                <td></td>
                <td>Jumlah Tagihan + Denda</td>
                <td class="text-right">{{ number_format($data['tot_tagihan']) }}</td>
            </tr>
            <tr>
                <td>4</td>
                <td>Tagihan Sudah Dibayar</td>
                <td class="text-right">{{ number_format($data['jml_angsuran']) }}</td>
            </tr>
            <tr class="font-bold">
                <td>5</td>
                <td>Sisa Tagihan</td>
                <td class="text-right">{{ number_format($data['sisa_tagihan']) }}</td>
            </tr>
        </tbody>
    </table>
    <br>
    <table style="width: 60%; margin-top: 20px;">
        <tr>
            <td><b>Peminjam Aktif</b></td>
            <td class="text-right">{{ number_format($data['peminjam_aktif']) }}</td>
        </tr>
        <tr>
            <td><b>Peminjam Lunas</b></td>
            <td class="text-right">{{ number_format($data['peminjam_lunas']) }}</td>
        </tr>
        <tr>
            <td><b>Peminjam Belum Lunas</b></td>
            <td class="text-right">{{ number_format($data['peminjam_belum']) }}</td>
        </tr>
    </table>
</body>
</html> 