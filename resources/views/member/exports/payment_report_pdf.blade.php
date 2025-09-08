<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pembayaran Pinjaman</title>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            font-size: 12px;
            color: #1f2937;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #10b981;
            padding-bottom: 20px;
        }
        
        .header h1 {
            font-size: 24px;
            font-weight: bold;
            color: #10b981;
            margin: 0 0 10px 0;
        }
        
        .header p {
            font-size: 14px;
            color: #6b7280;
            margin: 0;
        }
        
        .member-info {
            background-color: #f9fafb;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .member-info h3 {
            font-size: 16px;
            font-weight: bold;
            color: #1f2937;
            margin: 0 0 10px 0;
        }
        
        .member-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        
        .member-details div {
            font-size: 12px;
        }
        
        .member-details strong {
            color: #374151;
        }
        
        .period-info {
            background-color: #ecfdf5;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #10b981;
        }
        
        .period-info h3 {
            font-size: 16px;
            font-weight: bold;
            color: #10b981;
            margin: 0 0 10px 0;
        }
        
        .statistics {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background-color: #f9fafb;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            border: 1px solid #e5e7eb;
        }
        
        .stat-card h4 {
            font-size: 12px;
            color: #6b7280;
            margin: 0 0 5px 0;
            font-weight: normal;
        }
        
        .stat-card .value {
            font-size: 16px;
            font-weight: bold;
            color: #1f2937;
        }
        
        .table-container {
            margin-bottom: 30px;
        }
        
        .table-container h3 {
            font-size: 16px;
            font-weight: bold;
            color: #1f2937;
            margin: 0 0 15px 0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        
        th {
            background-color: #10b981;
            color: white;
            padding: 8px 6px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #059669;
        }
        
        td {
            padding: 6px;
            border: 1px solid #d1d5db;
            vertical-align: top;
        }
        
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .status-tepat {
            background-color: #dcfce7;
            color: #166534;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
        }
        
        .status-terlambat {
            background-color: #fef2f2;
            color: #dc2626;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
        }
        
        .jenis-biasa {
            background-color: #dbeafe;
            color: #1e40af;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
        }
        
        .jenis-barang {
            background-color: #e9d5ff;
            color: #7c3aed;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #6b7280;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Pembayaran Pinjaman</h1>
        <p>Koperasi Karyawan - Sistem Informasi Koperasi</p>
    </div>

    <div class="member-info">
        <h3>Informasi Anggota</h3>
        <div class="member-details">
            <div><strong>Nama:</strong> {{ $member->nama }}</div>
            <div><strong>No. KTP:</strong> {{ $member->no_ktp }}</div>
            <div><strong>No. Anggota:</strong> {{ $member->no_anggota ?? '-' }}</div>
            <div><strong>Alamat:</strong> {{ $member->alamat ?? '-' }}</div>
        </div>
    </div>

    <div class="period-info">
        <h3>Periode Laporan</h3>
        <div><strong>Dari:</strong> {{ \Carbon\Carbon::parse($tgl_dari)->format('d F Y') }}</div>
        <div><strong>Sampai:</strong> {{ \Carbon\Carbon::parse($tgl_samp)->format('d F Y') }}</div>
    </div>

    <div class="statistics">
        <div class="stat-card">
            <h4>Total Pembayaran</h4>
            <div class="value">{{ $statistics['total_pembayaran'] }}</div>
        </div>
        <div class="stat-card">
            <h4>Total Pokok Dibayar</h4>
            <div class="value">Rp {{ number_format($statistics['total_pokok_dibayar'], 0, ',', '.') }}</div>
        </div>
        <div class="stat-card">
            <h4>Total Bunga Dibayar</h4>
            <div class="value">Rp {{ number_format($statistics['total_bunga_dibayar'], 0, ',', '.') }}</div>
        </div>
        <div class="stat-card">
            <h4>Total Denda</h4>
            <div class="value">Rp {{ number_format($statistics['total_denda_dibayar'], 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="table-container">
        <h3>Detail Pembayaran Pinjaman</h3>
        
        @if($paymentData->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th style="width: 12%;">Tanggal</th>
                        <th style="width: 15%;">Jenis</th>
                        <th style="width: 8%;">Angsuran Ke</th>
                        <th style="width: 10%;">Pokok</th>
                        <th style="width: 10%;">Jasa</th>
                        <th style="width: 10%;">Denda</th>
                        <th style="width: 12%;">Total Bayar</th>
                        <th style="width: 10%;">Status</th>
                        <th style="width: 13%;">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($paymentData as $payment)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($payment->tgl_bayar)->format('d/m/Y') }}</td>
                        <td>
                            @if($payment->jns_pinjaman == '1')
                                <span class="jenis-biasa">Pinjaman Biasa</span>
                            @else
                                <span class="jenis-barang">Pinjaman Barang</span>
                            @endif
                        </td>
                        <td class="text-center">{{ $payment->angsuran_ke }}</td>
                        <td class="text-right">Rp {{ number_format($payment->jumlah_bayar, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format($payment->bunga, 0, ',', '.') }}</td>
                        <td class="text-right">
                            @if($payment->denda_rp > 0)
                                Rp {{ number_format($payment->denda_rp, 0, ',', '.') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-right">Rp {{ number_format($payment->total_bayar, 0, ',', '.') }}</td>
                        <td class="text-center">
                            @if($payment->status_pembayaran == 'Tepat Waktu')
                                <span class="status-tepat">Tepat Waktu</span>
                            @else
                                <span class="status-terlambat">Terlambat</span>
                            @endif
                        </td>
                        <td>{{ $payment->ket_bayar ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="no-data">
                Tidak ada data pembayaran untuk periode yang dipilih.
            </div>
        @endif
    </div>

    <div class="footer">
        <p>Laporan ini dibuat secara otomatis pada {{ now()->format('d F Y H:i:s') }}</p>
        <p>Koperasi Karyawan - Sistem Informasi Koperasi</p>
    </div>
</body>
</html>
