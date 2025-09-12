<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Data Mobil</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #333;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0 0 0;
            color: #666;
        }
        .info {
            margin-bottom: 20px;
        }
        .info p {
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }
        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }
        .badge-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
        }
        .badge-secondary {
            background-color: #e2e3e5;
            color: #383d41;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Data Mobil</h1>
        <p>Koperasi Indonesia</p>
        <p>Tanggal Cetak: {{ date('d F Y H:i:s') }}</p>
    </div>

    <div class="info">
        <p><strong>Total Data:</strong> {{ $dataMobil->count() }} mobil</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>ID</th>
                <th>Nama Mobil</th>
                <th>Jenis</th>
                <th>Merek</th>
                <th>Tahun</th>
                <th>No Polisi</th>
                <th>Status Aktif</th>
                <th>Status STNK</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dataMobil as $index => $mobil)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $mobil->id }}</td>
                <td>{{ $mobil->nama }}</td>
                <td>{{ $mobil->jenis ?? '-' }}</td>
                <td>{{ $mobil->merek ?? '-' }}</td>
                <td>{{ $mobil->tahun_formatted }}</td>
                <td>{{ $mobil->no_polisi ?? '-' }}</td>
                <td>
                    <span class="badge badge-{{ $mobil->status_aktif_badge }}">
                        {{ $mobil->status_aktif_text }}
                    </span>
                </td>
                <td>
                    <span class="badge badge-{{ $mobil->status_stnk_badge }}">
                        {{ $mobil->status_stnk }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align: center; padding: 20px;">
                    Tidak ada data mobil
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak pada {{ date('d F Y H:i:s') }} | Halaman 1</p>
    </div>
</body>
</html>
