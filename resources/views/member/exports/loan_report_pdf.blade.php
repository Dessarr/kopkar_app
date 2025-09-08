<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pinjaman - {{ $member->nama }}</title>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #1f2937;
            padding-bottom: 20px;
        }
        .header h1 {
            font-size: 24px;
            font-weight: bold;
            color: #1f2937;
            margin: 0;
        }
        .header h2 {
            font-size: 18px;
            color: #6b7280;
            margin: 5px 0;
        }
        .member-info {
            background-color: #f9fafb;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .member-info h3 {
            font-size: 16px;
            font-weight: bold;
            color: #1f2937;
            margin: 0 0 10px 0;
        }
        .member-info table {
            width: 100%;
        }
        .member-info td {
            padding: 3px 0;
        }
        .member-info .label {
            font-weight: bold;
            width: 150px;
        }
        .statistics {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        .stat-card {
            background-color: #f3f4f6;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            flex: 1;
            margin: 5px;
            min-width: 150px;
        }
        .stat-card h4 {
            font-size: 14px;
            font-weight: bold;
            color: #1f2937;
            margin: 0 0 5px 0;
        }
        .stat-card .value {
            font-size: 16px;
            font-weight: bold;
            color: #059669;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table th {
            background-color: #1f2937;
            color: white;
            padding: 10px 8px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #374151;
        }
        .table td {
            padding: 8px;
            border: 1px solid #d1d5db;
            text-align: center;
        }
        .table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .table tr:hover {
            background-color: #f3f4f6;
        }
        .text-right {
            text-align: right;
        }
        .text-left {
            text-align: left;
        }
        .status-lunas {
            background-color: #dcfce7;
            color: #166534;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 10px;
        }
        .status-belum-lunas {
            background-color: #fef3c7;
            color: #92400e;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 10px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #6b7280;
            border-top: 1px solid #d1d5db;
            padding-top: 10px;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>LAPORAN PINJAMAN</h1>
        <h2>Koperasi Indonesia</h2>
        <p>Periode: {{ \Carbon\Carbon::parse($tgl_dari)->format('d M Y') }} - {{ \Carbon\Carbon::parse($tgl_samp)->format('d M Y') }}</p>
    </div>

    <!-- Member Information -->
    <div class="member-info">
        <h3>Informasi Anggota</h3>
        <table>
            <tr>
                <td class="label">Nama:</td>
                <td>{{ $member->nama }}</td>
                <td class="label">No. KTP:</td>
                <td>{{ $member->no_ktp }}</td>
            </tr>
            <tr>
                <td class="label">Alamat:</td>
                <td>{{ $member->alamat }}</td>
                <td class="label">Telepon:</td>
                <td>{{ $member->notelp ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <!-- Statistics -->
    <div class="statistics">
        <div class="stat-card">
            <h4>Total Pinjaman</h4>
            <div class="value">Rp {{ number_format($statistics['total_pinjaman'], 0, ',', '.') }}</div>
        </div>
        <div class="stat-card">
            <h4>Total Dibayar</h4>
            <div class="value">Rp {{ number_format($statistics['total_dibayar'], 0, ',', '.') }}</div>
        </div>
        <div class="stat-card">
            <h4>Sisa Tagihan</h4>
            <div class="value">Rp {{ number_format($statistics['sisa_tagihan'], 0, ',', '.') }}</div>
        </div>
        <div class="stat-card">
            <h4>Progress</h4>
            <div class="value">{{ number_format($statistics['payment_progress'], 1) }}%</div>
        </div>
    </div>

    <!-- Loan Data Table -->
    <table class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Jenis</th>
                <th>Jumlah</th>
                <th>Lama</th>
                <th>Angsuran/Bulan</th>
                <th>Total Tagihan</th>
                <th>Dibayar</th>
                <th>Sisa</th>
                <th>Progress</th>
                <th>Status</th>
                <th>Tempo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($loanData as $index => $loan)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($loan['tgl_pinjam'])->format('d/m/Y') }}</td>
                <td>{{ $loan['jns_pinjaman'] == '1' ? 'Biasa' : ($loan['jns_pinjaman'] == '2' ? 'Barang' : $loan['jns_pinjaman']) }}</td>
                <td class="text-right">Rp {{ number_format($loan['jumlah'], 0, ',', '.') }}</td>
                <td>{{ $loan['lama_angsuran'] }} bln</td>
                <td class="text-right">Rp {{ number_format($loan['angsuran_per_bulan'], 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($loan['total_tagihan'], 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($loan['jml_bayar'], 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($loan['sisa_tagihan'], 0, ',', '.') }}</td>
                <td>{{ $loan['progress'] }}%</td>
                <td>
                    @if($loan['status'] == 'Lunas')
                        <span class="status-lunas">Lunas</span>
                    @else
                        <span class="status-belum-lunas">{{ $loan['status'] }}</span>
                    @endif
                </td>
                <td>{{ \Carbon\Carbon::parse($loan['tempo'])->format('d/m/Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Summary -->
    <div class="statistics">
        <div class="stat-card">
            <h4>Total Pinjaman</h4>
            <div class="value">{{ count($loanData) }}</div>
        </div>
        <div class="stat-card">
            <h4>Lunas</h4>
            <div class="value">{{ $statistics['lunas_count'] }}</div>
        </div>
        <div class="stat-card">
            <h4>Belum Lunas</h4>
            <div class="value">{{ $statistics['belum_lunas_count'] }}</div>
        </div>
        <div class="stat-card">
            <h4>Rata-rata</h4>
            <div class="value">Rp {{ number_format($statistics['avg_pinjaman'], 0, ',', '.') }}</div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Laporan ini dibuat secara otomatis pada {{ now()->format('d M Y H:i:s') }}</p>
        <p>Koperasi Indonesia - Sistem Informasi Koperasi</p>
    </div>
</body>
</html>
