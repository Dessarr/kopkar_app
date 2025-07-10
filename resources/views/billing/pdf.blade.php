<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Billing Anggota</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            padding: 0;
            font-size: 18px;
        }
        .header p {
            margin: 5px 0;
            font-size: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #000;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
            padding: 5px;
        }
        td {
            padding: 5px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .footer {
            margin-top: 20px;
            text-align: right;
        }
        .status-lunas {
            color: green;
            font-weight: bold;
        }
        .status-belum {
            color: #cc8800;
            font-weight: bold;
        }
        .total-row {
            font-weight: bold;
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN BILLING ANGGOTA KOPERASI</h1>
        <p>Tanggal Cetak: {{ date('d-m-Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">No KTP</th>
                <th width="20%">Nama</th>
                <th width="15%">Simpanan Wajib</th>
                <th width="15%">Simpanan Sukarela</th>
                <th width="15%">Toserda</th>
                <th width="15%">Total</th>
                <th width="10%">Status</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $total = 0;
                $no = 1;
            @endphp
            @forelse($dataBilling as $item)
            <tr>
                <td class="text-center">{{ $no++ }}</td>
                <td>{{ $item->no_ktp }}</td>
                <td>{{ $item->nama }}</td>
                <td class="text-right">{{ number_format($item->simpanan_wajib ?? 0, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($item->simpanan_sukarela ?? 0, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($item->simpanan_khusus_2 ?? 0, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($item->total_billing, 0, ',', '.') }}</td>
                <td class="text-center {{ ($item->status_bayar == 'Lunas') ? 'status-lunas' : 'status-belum' }}">
                    {{ $item->status_bayar ?? 'Belum Lunas' }}
                </td>
            </tr>
            @php $total += $item->total_billing; @endphp
            @empty
            <tr>
                <td colspan="8" class="text-center">Tidak ada data</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="6" class="text-right"><strong>Total Keseluruhan</strong></td>
                <td class="text-right"><strong>{{ number_format($total, 0, ',', '.') }}</strong></td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Dicetak oleh: {{ Auth::user()->name }}</p>
    </div>
</body>
</html> 