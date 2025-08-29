<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Pemasukan Angkutan</title>
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
        <div class="title">LAPORAN PEMASUKAN ANGKUTAN KARYAWAN</div>
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
                <th>Uraian</th>
                <th>Untuk Kas</th>
                <th>Akun</th>
                <th class="text-right">Jumlah</th>
                <th>User</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transaksi as $index => $tr)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>TKD{{ str_pad($tr->id, 6, '0', STR_PAD_LEFT) }}</td>
                <td>{{ $tr->tgl_catat->format('d/m/Y H:i') }}</td>
                <td>{{ $tr->keterangan }}</td>
                <td>{{ optional($tr->untukKas)->nama }}</td>
                <td>Pendapatan Jasa Sewa Bus</td>
                <td class="text-right">Rp {{ number_format($tr->jumlah, 0, ',', '.') }}</td>
                <td>{{ $tr->user_name }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">Tidak ada data pemasukan angkutan</td>
            </tr>
            @endforelse
        </tbody>
        @if($transaksi->count() > 0)
        <tfoot>
            <tr class="total-row">
                <td colspan="6" class="text-right"><strong>Total Pemasukan:</strong></td>
                <td class="text-right"><strong>Rp {{ number_format($totalPemasukan, 0, ',', '.') }}</strong></td>
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
