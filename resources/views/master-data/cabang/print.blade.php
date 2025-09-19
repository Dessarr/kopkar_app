<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Cabang - {{ now()->format('d F Y') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }
        .header h2 {
            margin: 5px 0 0 0;
            font-size: 16px;
            color: #666;
            font-weight: normal;
        }
        .info {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
        }
        .info div {
            font-size: 11px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
            text-align: center;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>DATA CABANG KOPERASI</h1>
        <h2>Laporan Data Cabang</h2>
    </div>

    <div class="info">
        <div>
            <strong>Tanggal Cetak:</strong> {{ now()->format('d F Y, H:i:s') }}
        </div>
        <div>
            <strong>Total Data:</strong> {{ $cabang->count() }} cabang
        </div>
    </div>

    @if($cabang->count() > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 10%;">No</th>
                    <th style="width: 15%;">ID Cabang</th>
                    <th style="width: 25%;">Nama Cabang</th>
                    <th style="width: 35%;">Alamat</th>
                    <th style="width: 15%;">No. Telepon</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cabang as $index => $item)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td style="text-align: center; font-weight: bold;">{{ $item->id_cabang }}</td>
                    <td>{{ $item->nama }}</td>
                    <td>{{ $item->alamat }}</td>
                    <td style="text-align: center;">{{ $item->no_telp }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="no-data">
            <p>Tidak ada data cabang yang ditemukan.</p>
        </div>
    @endif

    <div class="footer">
        <p>Dicetak pada {{ now()->format('d F Y, H:i:s') }} | Halaman 1 dari 1</p>
        <p>Sistem Informasi Koperasi - Data Cabang</p>
    </div>

    <script>
        // Auto print when page loads
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
