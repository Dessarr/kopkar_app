<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Data Pengajuan Pinjaman</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
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
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 10px;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .status-0 { background-color: #fff3cd; }
        .status-1 { background-color: #d1edff; }
        .status-2 { background-color: #f8d7da; }
        .status-3 { background-color: #d4edda; }
        .status-4 { background-color: #e2e3e5; }
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 10px;
        }
        .summary {
            margin-top: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN DATA PENGAJUAN PINJAMAN</h1>
        <p>Koperasi Simpan Pinjam</p>
        <p>Tanggal Cetak: {{ date('d/m/Y H:i:s') }}</p>
        <p>Total Data: {{ $dataPengajuan->count() }} pengajuan</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>ID Ajuan</th>
                <th>Anggota</th>
                <th>Tanggal</th>
                <th>Jenis</th>
                <th>Jumlah</th>
                <th>Bulan</th>
                <th>Status</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dataPengajuan as $index => $pengajuan)
            <tr class="status-{{ $pengajuan->status }}">
                <td>{{ $index + 1 }}</td>
                <td>{{ $pengajuan->ajuan_id }}</td>
                <td>
                    {{ $pengajuan->anggota->nama ?? '-' }}
                    <br><small>({{ $pengajuan->anggota_id }})</small>
                </td>
                <td>{{ \Carbon\Carbon::parse($pengajuan->tgl_input)->format('d/m/Y') }}</td>
                <td>
                    @php
                    $jenisMap = [
                        '1' => 'Biasa',
                        '3' => 'Barang'
                    ];
                    $jenisText = $jenisMap[$pengajuan->jenis] ?? $pengajuan->jenis;
                    @endphp
                    {{ $jenisText }}
                </td>
                <td>Rp {{ number_format($pengajuan->nominal, 0, ',', '.') }}</td>
                <td>{{ $pengajuan->lama_ags }}</td>
                <td>
                    @php
                    $statusMap = [
                        0 => 'Menunggu Konfirmasi',
                        1 => 'Disetujui',
                        2 => 'Ditolak',
                        3 => 'Terlaksana',
                        4 => 'Batal'
                    ];
                    @endphp
                    {{ $statusMap[$pengajuan->status] ?? $pengajuan->status }}
                </td>
                <td>{{ $pengajuan->keterangan }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <h3>Ringkasan Status:</h3>
        <ul>
            @php
            $statusCounts = $dataPengajuan->groupBy('status')->map->count();
            $statusLabels = [
                0 => 'Menunggu Konfirmasi',
                1 => 'Disetujui',
                2 => 'Ditolak',
                3 => 'Terlaksana',
                4 => 'Batal'
            ];
            @endphp
            @foreach($statusCounts as $status => $count)
            <li>{{ $statusLabels[$status] ?? 'Status ' . $status }}: {{ $count }} pengajuan</li>
            @endforeach
        </ul>
        
        <h3>Ringkasan Jenis:</h3>
        <ul>
            @php
            $jenisCounts = $dataPengajuan->groupBy('jenis')->map->count();
            @endphp
            <li>Biasa: {{ $jenisCounts['1'] ?? 0 }} pengajuan</li>
            <li>Barang: {{ $jenisCounts['2'] ?? 0 }} pengajuan</li>
        </ul>
        
        <h3>Total Nominal:</h3>
        <p>Rp {{ number_format($dataPengajuan->sum('nominal'), 0, ',', '.') }}</p>
    </div>

    <div class="footer">
        <p>Dicetak pada: {{ date('d/m/Y H:i:s') }}</p>
        <p>Oleh: Admin Sistem</p>
    </div>
</body>
</html>
