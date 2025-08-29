<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Data Pinjaman</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }
        .header p {
            margin: 5px 0;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
            font-size: 10px;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .summary {
            margin-top: 20px;
            border-top: 2px solid #333;
            padding-top: 10px;
        }
        .summary table {
            width: auto;
            margin-left: auto;
        }
        .summary th {
            background-color: #f5f5f5;
            padding: 8px 12px;
        }
        .summary td {
            padding: 8px 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN DATA PINJAMAN</h1>
        <p>Koperasi Simpan Pinjam</p>
        <p>Periode: {{ request('date_from') ? \Carbon\Carbon::parse(request('date_from'))->format('d/m/Y') : 'Semua' }} - {{ request('date_to') ? \Carbon\Carbon::parse(request('date_to'))->format('d/m/Y') : 'Semua' }}</p>
        <p>Tanggal Cetak: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 30px;">No</th>
                <th style="width: 80px;">Kode</th>
                <th style="width: 120px;">Nama Anggota</th>
                <th style="width: 80px;">Tanggal</th>
                <th style="width: 60px;">Jenis</th>
                <th class="text-right" style="width: 100px;">Jumlah</th>
                <th class="text-center" style="width: 40px;">Bln</th>
                <th class="text-center" style="width: 50px;">Bunga %</th>
                <th class="text-right" style="width: 80px;">Angsuran/Bln</th>
                <th class="text-center" style="width: 80px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $index => $pinjaman)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $pinjaman->id }}</td>
                <td>{{ optional($pinjaman->anggota)->nama ?? '-' }}</td>
                <td>{{ \Carbon\Carbon::parse($pinjaman->tgl_pinjam)->format('d/m/Y') }}</td>
                <td>{{ $pinjaman->jenis_pinjaman == '1' ? 'Biasa' : 'Barang' }}</td>
                <td class="text-right">{{ number_format($pinjaman->jumlah, 0, ',', '.') }}</td>
                <td class="text-center">{{ $pinjaman->lama_angsuran }}</td>
                <td class="text-center">{{ $pinjaman->bunga }}%</td>
                <td class="text-right">{{ number_format($pinjaman->jumlah_angsuran, 0, ',', '.') }}</td>
                <td class="text-center">{{ $pinjaman->lunas == 'Lunas' ? 'Lunas' : 'Belum Lunas' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <table>
            <tr>
                <th>Total Pinjaman</th>
                <td class="text-right">{{ number_format($data->sum('jumlah'), 0, ',', '.') }}</td>
            </tr>
            <tr>
                <th>Jumlah Data</th>
                <td class="text-center">{{ $data->count() }}</td>
            </tr>
            <tr>
                <th>Status Lunas</th>
                <td class="text-center">{{ $data->where('lunas', 'Lunas')->count() }}</td>
            </tr>
            <tr>
                <th>Status Belum Lunas</th>
                <td class="text-center">{{ $data->where('lunas', 'Belum')->count() }}</td>
            </tr>
        </table>
    </div>
</body>
</html>
