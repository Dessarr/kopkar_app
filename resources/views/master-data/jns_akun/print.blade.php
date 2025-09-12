<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Jenis Akun</title>
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
            font-size: 11px;
            color: #666;
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
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
            text-align: center;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .status-aktif {
            background-color: #d4edda;
            color: #155724;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
        }
        .status-tidak-aktif {
            background-color: #f8d7da;
            color: #721c24;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
        }
        .status-ya {
            background-color: #d4edda;
            color: #155724;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
        }
        .status-tidak {
            background-color: #f8d7da;
            color: #721c24;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Jenis Akun</h1>
        <p>Koperasi Indonesia</p>
    </div>

    <div class="info">
        <p><strong>Tanggal Cetak:</strong> {{ date('d F Y H:i:s') }}</p>
        <p><strong>Total Data:</strong> {{ $dataAkun->count() }} jenis akun</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 5%;">#</th>
                <th class="text-center" style="width: 10%;">Kode Aktiva</th>
                <th style="width: 25%;">Jenis Transaksi</th>
                <th class="text-center" style="width: 15%;">Akun</th>
                <th class="text-center" style="width: 15%;">Laba Rugi</th>
                <th class="text-center" style="width: 10%;">Pemasukan</th>
                <th class="text-center" style="width: 10%;">Pengeluaran</th>
                <th class="text-center" style="width: 10%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dataAkun as $index => $akun)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ $akun->kd_aktiva }}</td>
                <td>{{ $akun->jns_trans }}</td>
                <td class="text-center">{{ $akun->akun }}</td>
                <td class="text-center">{{ $akun->laba_rugi ?? '-' }}</td>
                <td class="text-center">
                    <span class="{{ $akun->pemasukan ? 'status-ya' : 'status-tidak' }}">
                        {{ $akun->pemasukan ? 'Ya' : 'Tidak' }}
                    </span>
                </td>
                <td class="text-center">
                    <span class="{{ $akun->pengeluaran ? 'status-ya' : 'status-tidak' }}">
                        {{ $akun->pengeluaran ? 'Ya' : 'Tidak' }}
                    </span>
                </td>
                <td class="text-center">
                    <span class="{{ $akun->aktif ? 'status-aktif' : 'status-tidak-aktif' }}">
                        {{ $akun->aktif ? 'Aktif' : 'Tidak Aktif' }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">Tidak ada data jenis akun</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak pada {{ date('d F Y H:i:s') }} | Halaman 1</p>
    </div>
</body>
</html>
