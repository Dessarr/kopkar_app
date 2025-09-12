<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Anggota - {{ date('d/m/Y') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #fff;
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
            font-size: 14px;
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
            font-size: 12px;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .no-print {
            display: none;
        }
        @media print {
            body { margin: 0; }
            .no-print { display: none !important; }
        }
        .status-aktif {
            color: #28a745;
            font-weight: bold;
        }
        .status-tidak-aktif {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Data Anggota Koperasi</h1>
        <p>Dicetak pada: {{ date('d F Y, H:i:s') }}</p>
        <p>Total Data: {{ $dataAnggota->count() }} anggota</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 12%">ID Koperasi</th>
                <th style="width: 20%">Nama Lengkap</th>
                <th style="width: 8%">JK</th>
                <th style="width: 10%">Tempat Lahir</th>
                <th style="width: 10%">Tanggal Lahir</th>
                <th style="width: 5%">Umur</th>
                <th style="width: 8%">Status</th>
                <th style="width: 10%">Departemen</th>
                <th style="width: 12%">Kota</th>
                <th style="width: 10%">No. Telepon</th>
                <th style="width: 10%">Tanggal Daftar</th>
                <th style="width: 8%">Status Aktif</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dataAnggota as $index => $anggota)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $anggota->no_ktp }}</td>
                <td>{{ $anggota->nama }}</td>
                <td>{{ $anggota->jenis_kelamin_text }}</td>
                <td>{{ $anggota->tmp_lahir }}</td>
                <td>{{ $anggota->tgl_lahir ? $anggota->tgl_lahir->format('d/m/Y') : '-' }}</td>
                <td>{{ $anggota->umur ?? '-' }}</td>
                <td>{{ $anggota->status }}</td>
                <td>{{ $anggota->departement }}</td>
                <td>{{ $anggota->kota }}</td>
                <td>{{ $anggota->notelp }}</td>
                <td>{{ $anggota->tgl_daftar ? $anggota->tgl_daftar->format('d/m/Y') : '-' }}</td>
                <td class="{{ $anggota->aktif == 'Y' ? 'status-aktif' : 'status-tidak-aktif' }}">
                    {{ $anggota->status_aktif_text }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($dataAnggota->count() == 0)
    <div style="text-align: center; margin-top: 50px; color: #666;">
        <p>Tidak ada data anggota yang ditemukan</p>
    </div>
    @endif
</body>
</html>
