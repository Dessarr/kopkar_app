<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan SHU</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .subtitle {
            font-size: 14px;
            margin-bottom: 5px;
        }
        .period {
            font-size: 12px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .total-row {
            font-weight: bold;
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 30px;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">LAPORAN SISA HASIL USAHA (SHU)</div>
        <div class="subtitle">Koperasi Karyawan</div>
        <div class="period">
            Periode: {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d M Y') : 'Semua' }} - 
            {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d M Y') : 'Semua' }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Transaksi</th>
                <th>Tanggal Transaksi</th>
                <th>ID Anggota</th>
                <th>Nama Anggota</th>
                <th>No KTP</th>
                <th class="text-right">Jumlah SHU</th>
                <th>User</th>
            </tr>
        </thead>
        <tbody>
            @forelse($shuData as $index => $shu)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>TRD{{ str_pad($shu->id, 5, '0', STR_PAD_LEFT) }}</td>
                <td>{{ $shu->tgl_transaksi->format('d/m/Y') }}</td>
                <td>AG{{ str_pad($shu->anggota->id ?? 0, 4, '0', STR_PAD_LEFT) }}</td>
                <td>{{ $shu->anggota->nama ?? 'N/A' }}</td>
                <td>{{ $shu->no_ktp }}</td>
                <td class="text-right">Rp {{ number_format($shu->jumlah_bayar, 0, ',', '.') }}</td>
                <td>{{ $shu->user_name }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">Tidak ada data SHU</td>
            </tr>
            @endforelse
        </tbody>
        @if($shuData->count() > 0)
        <tfoot>
            <tr class="total-row">
                <td colspan="6" class="text-right"><strong>Total SHU:</strong></td>
                <td class="text-right"><strong>Rp {{ number_format($totalShu, 0, ',', '.') }}</strong></td>
                <td></td>
            </tr>
        </tfoot>
        @endif
    </table>

    <div class="footer">
        <p>Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
