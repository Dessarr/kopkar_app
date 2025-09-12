<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Data Pengguna</title>
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
        .badge-purple {
            background-color: #e2d9f3;
            color: #6f42c1;
        }
        .badge-blue {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        .badge-green {
            background-color: #d4edda;
            color: #155724;
        }
        .badge-yellow {
            background-color: #fff3cd;
            color: #856404;
        }
        .badge-indigo {
            background-color: #d6d8f5;
            color: #4c51bf;
        }
        .badge-orange {
            background-color: #ffeaa7;
            color: #d63031;
        }
        .badge-red {
            background-color: #f8d7da;
            color: #721c24;
        }
        .badge-gray {
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
        <h1>Laporan Data Pengguna</h1>
        <p>Koperasi Indonesia</p>
        <p>Tanggal Cetak: {{ date('d F Y H:i:s') }}</p>
    </div>

    <div class="info">
        <p><strong>Total Data:</strong> {{ $dataPengguna->count() }} pengguna</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>ID</th>
                <th>Username</th>
                <th>Level</th>
                <th>Cabang</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dataPengguna as $index => $pengguna)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $pengguna->id }}</td>
                <td>{{ $pengguna->u_name }}</td>
                <td>
                    <span class="badge {{ $pengguna->level_badge }}">
                        {{ $pengguna->level_text }}
                    </span>
                </td>
                <td>{{ $pengguna->cabang ? $pengguna->cabang->nama : '-' }}</td>
                <td>
                    <span class="badge {{ $pengguna->status_aktif_badge }}">
                        {{ $pengguna->status_aktif_text }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; padding: 20px;">
                    Tidak ada data pengguna
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
