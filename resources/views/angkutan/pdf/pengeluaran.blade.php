<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Pengeluaran Angkutan</title>
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
        <div class="title">LAPORAN PENGELUARAN ANGKUTAN KARYAWAN</div>
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
                <th>Dari Kas</th>
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
                <td>{{ optional($tr->dariKas)->nama }}</td>
                <td>
                    @php
                        $akunMap = [
                            '55' => 'Beban Bahan Bakar',
                            '56' => 'Beban Servis',
                            '57' => 'Beban Parkir',
                            '58' => 'Beban Tol',
                            '59' => 'Beban Gaji Supir',
                            '60' => 'Beban Gaji Kernet',
                            '61' => 'Beban Asuransi',
                            '62' => 'Beban Pajak',
                            '63' => 'Beban Administrasi',
                            '64' => 'Beban Lain-lain',
                            '65' => 'Beban Perbaikan',
                            '66' => 'Beban P3K',
                            '67' => 'Beban Cuci',
                            '68' => 'Beban Ban',
                            '69' => 'Beban Oli'
                        ];
                        echo $akunMap[$tr->jns_trans] ?? 'Akun Lain';
                    @endphp
                </td>
                <td class="text-right">Rp {{ number_format($tr->jumlah, 0, ',', '.') }}</td>
                <td>{{ $tr->user_name }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">Tidak ada data pengeluaran angkutan</td>
            </tr>
            @endforelse
        </tbody>
        @if($transaksi->count() > 0)
        <tfoot>
            <tr class="total-row">
                <td colspan="6" class="text-right"><strong>Total Pengeluaran:</strong></td>
                <td class="text-right"><strong>Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</strong></td>
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
