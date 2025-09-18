<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Kas Pinjaman</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 10px;
            line-height: 1.3;
            color: #333;
            margin: 0;
            padding: 15px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 3px solid #3b82f6;
            padding-bottom: 15px;
        }
        
        .header h1 {
            font-size: 20px;
            font-weight: bold;
            color: #1d4ed8;
            margin: 0 0 8px 0;
        }
        
        .header h2 {
            font-size: 16px;
            font-weight: bold;
            color: #374151;
            margin: 0 0 5px 0;
        }
        
        .header p {
            font-size: 12px;
            color: #6b7280;
            margin: 0;
        }
        
        .info-section {
            background-color: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 15px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
        }
        
        .info-row:last-child {
            margin-bottom: 0;
        }
        
        .info-label {
            font-weight: bold;
            color: #374151;
        }
        
        .info-value {
            color: #1f2937;
        }
        
        .summary-cards {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            gap: 10px;
        }
        
        .summary-card {
            flex: 1;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            padding: 12px;
            border-radius: 6px;
            text-align: center;
        }
        
        .summary-card.green {
            background: linear-gradient(135deg, #10b981, #059669);
        }
        
        .summary-card.orange {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }
        
        .summary-card.purple {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
        }
        
        .summary-card h3 {
            font-size: 10px;
            margin: 0 0 6px 0;
            opacity: 0.9;
        }
        
        .summary-card .amount {
            font-size: 14px;
            font-weight: bold;
            margin: 0;
        }
        
        .summary-card .subtitle {
            font-size: 8px;
            margin: 4px 0 0 0;
            opacity: 0.8;
        }
        
        .financial-cards {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            gap: 10px;
        }
        
        .financial-card {
            flex: 1;
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: white;
            padding: 12px;
            border-radius: 6px;
            text-align: center;
        }
        
        .financial-card.green {
            background: linear-gradient(135deg, #10b981, #059669);
        }
        
        .financial-card.red {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }
        
        .financial-card h3 {
            font-size: 9px;
            margin: 0 0 6px 0;
            opacity: 0.9;
        }
        
        .financial-card .amount {
            font-size: 12px;
            font-weight: bold;
            margin: 0 0 4px 0;
        }
        
        .financial-card .subtitle {
            font-size: 7px;
            margin: 0;
            opacity: 0.8;
        }
        
        .table-container {
            margin-top: 15px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        th {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            padding: 8px 6px;
            text-align: left;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        
        th.text-center {
            text-align: center;
        }
        
        th.text-right {
            text-align: right;
        }
        
        td {
            padding: 6px 6px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 9px;
        }
        
        td.text-center {
            text-align: center;
        }
        
        td.text-right {
            text-align: right;
        }
        
        .positive {
            color: #059669;
            font-weight: bold;
        }
        
        .negative {
            color: #dc2626;
            font-weight: bold;
        }
        
        .total-row {
            background-color: #f3f4f6;
            font-weight: bold;
            border-top: 2px solid #374151;
        }
        
        .total-row td {
            border-bottom: 2px solid #374151;
            padding: 8px 6px;
        }
        
        .highlight-row {
            background-color: #f0fdf4;
            font-weight: bold;
        }
        
        .highlight-row td {
            border-bottom: 2px solid #10b981;
            padding: 8px 6px;
        }
        
        .icon {
            margin-right: 4px;
        }
        
        .icon.money {
            color: #3b82f6;
        }
        
        .icon.warning {
            color: #f59e0b;
        }
        
        .icon.check {
            color: #10b981;
        }
        
        .icon.calculator {
            color: #6b7280;
        }
        
        .footer {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 8px;
        }
        
        .no-data {
            text-align: center;
            padding: 30px;
            color: #6b7280;
        }
        
        .no-data i {
            font-size: 36px;
            color: #d1d5db;
            margin-bottom: 10px;
        }
        
        .no-data h3 {
            font-size: 14px;
            margin: 0 0 8px 0;
            color: #374151;
        }
        
        .no-data p {
            margin: 0;
            font-size: 10px;
        }
        
        .recent-loans {
            margin-top: 20px;
        }
        
        .recent-loans h4 {
            font-size: 12px;
            font-weight: bold;
            color: #374151;
            margin: 0 0 10px 0;
            padding-bottom: 5px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 12px;
            font-size: 7px;
            font-weight: bold;
        }
        
        .status-success {
            background-color: #dcfce7;
            color: #166534;
        }
        
        .status-warning {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        /* Page break for large tables */
        .page-break {
            page-break-before: always;
        }
        
        /* Compact layout for PDF */
        .compact {
            margin: 0;
            padding: 0;
        }
        
        .compact .header {
            margin-bottom: 15px;
            padding-bottom: 10px;
        }
        
        .compact .summary-cards {
            margin-bottom: 15px;
        }
        
        .compact .financial-cards {
            margin-bottom: 15px;
        }
        
        .compact table {
            font-size: 8px;
        }
        
        .compact th {
            padding: 6px 4px;
            font-size: 8px;
        }
        
        .compact td {
            padding: 4px 4px;
            font-size: 8px;
        }
    </style>
</head>
<body class="compact">
    <!-- Header -->
    <div class="header">
        <h1>LAPORAN KAS PINJAMAN</h1>
        <h2>Loan Cash Report</h2>
        <p>Periode: {{ $tgl_dari_formatted }} s/d {{ $tgl_samp_formatted }}</p>
    </div>

    <!-- Info Section -->
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Periode:</span>
            <span class="info-value">{{ $tgl_dari_formatted }} s/d {{ $tgl_samp_formatted }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Tanggal Cetak:</span>
            <span class="info-value">{{ \Carbon\Carbon::now()->format('d F Y H:i') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Total Peminjam:</span>
            <span class="info-value">{{ $statistics['peminjam_aktif'] }} anggota</span>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="summary-card">
            <h3>Peminjam Aktif</h3>
            <p class="amount">{{ $statistics['peminjam_aktif'] }}</p>
        </div>
        <div class="summary-card green">
            <h3>Peminjam Lunas</h3>
            <p class="amount">{{ $statistics['peminjam_lunas'] }}</p>
            <p class="subtitle">{{ number_format($statistics['completion_rate'], 1) }}% completion</p>
        </div>
        <div class="summary-card orange">
            <h3>Belum Lunas</h3>
            <p class="amount">{{ $statistics['peminjam_belum'] }}</p>
            <p class="subtitle">{{ number_format($statistics['overdue_rate'], 1) }}% overdue</p>
        </div>
        <div class="summary-card purple">
            <h3>Tingkat Pelunasan</h3>
            <p class="amount">{{ number_format($statistics['completion_rate'], 1) }}%</p>
            <p class="subtitle">Collection rate</p>
        </div>
    </div>

    <!-- Financial Summary Cards -->
    <div class="financial-cards">
        <div class="financial-card">
            <h3>Total Pinjaman</h3>
            <p class="amount">Rp {{ number_format($summary['total_pinjaman']) }}</p>
        </div>
        <div class="financial-card green">
            <h3>Sudah Dibayar</h3>
            <p class="amount">Rp {{ number_format($summary['total_bayar']) }}</p>
            <p class="subtitle">{{ number_format($summary['completion_rate'], 1) }}% collected</p>
        </div>
        <div class="financial-card red">
            <h3>Sisa Tagihan</h3>
            <p class="amount">Rp {{ number_format($summary['total_sisa']) }}</p>
            <p class="subtitle">Outstanding</p>
        </div>
    </div>

    <!-- Main Report Table -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Keterangan</th>
                    <th class="text-right">Jumlah (Rp)</th>
                    <th class="text-center">Persentase</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>
                        <span class="icon money">💰</span>
                        Pokok Pinjaman
                    </td>
                    <td class="text-right positive">
                        Rp {{ number_format($summary['total_pinjaman']) }}
                    </td>
                    <td class="text-center">
                        {{ $summary['total_pinjaman'] > 0 ? number_format(($summary['total_pinjaman'] / $summary['total_pinjaman']) * 100, 1) : 0 }}%
                    </td>
                </tr>
                
                <tr>
                    <td>2</td>
                    <td>
                        <span class="icon warning">⚠️</span>
                        Tagihan Denda
                    </td>
                    <td class="text-right negative">
                        Rp 0
                    </td>
                    <td class="text-center">
                        0%
                    </td>
                </tr>
                
                <tr class="total-row">
                    <td></td>
                    <td>
                        <span class="icon calculator">🧮</span>
                        <strong>Jumlah Tagihan + Denda</strong>
                    </td>
                    <td class="text-right">
                        <strong>Rp {{ number_format($summary['total_pinjaman']) }}</strong>
                    </td>
                    <td class="text-center">
                        <strong>100.0%</strong>
                    </td>
                </tr>
                
                <tr>
                    <td>3</td>
                    <td>
                        <span class="icon check">✅</span>
                        Tagihan Sudah Dibayar
                    </td>
                    <td class="text-right positive">
                        Rp {{ number_format($summary['total_bayar']) }}
                    </td>
                    <td class="text-center">
                        {{ $summary['total_pinjaman'] > 0 ? number_format(($summary['total_bayar'] / $summary['total_pinjaman']) * 100, 1) : 0 }}%
                    </td>
                </tr>
                
                <tr class="highlight-row">
                    <td>4</td>
                    <td>
                        <span class="icon warning">⚠️</span>
                        <strong>Sisa Tagihan</strong>
                    </td>
                    <td class="text-right">
                        <strong class="negative">Rp {{ number_format($summary['total_sisa']) }}</strong>
                    </td>
                    <td class="text-center">
                        <strong class="negative">
                            {{ $summary['total_pinjaman'] > 0 ? number_format(($summary['total_sisa'] / $summary['total_pinjaman']) * 100, 1) : 0 }}%
                        </strong>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Recent Loans Section -->
    @if(count($recentLoans) > 0)
    <div class="recent-loans">
        <h4>Pinjaman Terbaru</h4>
        <table>
            <thead>
                <tr>
                    <th>ID Pinjaman</th>
                    <th>Nama Anggota</th>
                    <th class="text-right">Jumlah</th>
                    <th class="text-center">Tanggal</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentLoans as $loan)
                <tr>
                    <td class="font-mono">{{ $loan['id'] }}</td>
                    <td>{{ $loan['anggota'] }}</td>
                    <td class="text-right">Rp {{ number_format($loan['jumlah']) }}</td>
                    <td class="text-center">{{ $loan['tgl_pinjam'] }}</td>
                    <td class="text-center">
                        <span class="status-badge {{ $loan['status_badge'] == 'success' ? 'status-success' : 'status-warning' }}">
                            {{ $loan['status'] }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Summary Footer -->
    <div style="margin-top: 20px; padding: 15px; background: linear-gradient(135deg, #f9fafb, #f3f4f6); border-radius: 6px; border: 1px solid #e5e7eb;">
        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 9px; color: #6b7280;">
            <div style="display: flex; align-items: center;">
                <span class="icon calculator">📅</span>
                <span style="font-weight: bold;">Periode:</span> {{ $tgl_dari_formatted }} - {{ $tgl_samp_formatted }}
            </div>
            <div style="display: flex; align-items: center;">
                <span class="icon money">👥</span>
                <span style="font-weight: bold;">Total Peminjam:</span> {{ $statistics['peminjam_aktif'] }} anggota
            </div>
            <div style="display: flex; align-items: center;">
                <span class="icon check">📊</span>
                <span style="font-weight: bold;">Tingkat Pelunasan:</span> {{ number_format($statistics['completion_rate'], 1) }}%
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Laporan ini dibuat secara otomatis oleh sistem Koperasi pada {{ \Carbon\Carbon::now()->format('d F Y H:i') }}</p>
        <p>Halaman 1 dari 1</p>
    </div>
</body>
</html>
