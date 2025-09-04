<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Saldo Kas - {{ \Carbon\Carbon::parse($periode . '-01')->format('F Y') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
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
            font-size: 24px;
            font-weight: bold;
            margin: 0 0 10px 0;
            color: #2d3748;
        }
        
        .header h2 {
            font-size: 18px;
            font-weight: normal;
            margin: 0 0 5px 0;
            color: #4a5568;
        }
        
        .header p {
            font-size: 14px;
            margin: 0;
            color: #718096;
        }
        
        .info-section {
            margin-bottom: 20px;
            background-color: #f7fafc;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #4299e1;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .info-label {
            font-weight: bold;
            color: #2d3748;
        }
        
        .info-value {
            color: #4a5568;
        }
        
        .summary-section {
            margin-bottom: 25px;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .summary-card {
            background-color: #f7fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
        }
        
        .summary-card h3 {
            font-size: 14px;
            font-weight: bold;
            margin: 0 0 8px 0;
            color: #2d3748;
        }
        
        .summary-card .value {
            font-size: 18px;
            font-weight: bold;
            color: #4299e1;
        }
        
        .performance-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
        }
        
        .performance-card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
        }
        
        .performance-card h3 {
            font-size: 12px;
            font-weight: bold;
            margin: 0 0 8px 0;
            color: #4a5568;
        }
        
        .performance-card .value {
            font-size: 16px;
            font-weight: bold;
            color: #2d3748;
        }
        
        .table-section {
            margin-bottom: 25px;
        }
        
        .table-section h3 {
            font-size: 16px;
            font-weight: bold;
            margin: 0 0 15px 0;
            color: #2d3748;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 5px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10px;
        }
        
        th {
            background-color: #f7fafc;
            color: #2d3748;
            font-weight: bold;
            padding: 8px 6px;
            text-align: left;
            border: 1px solid #e2e8f0;
            font-size: 9px;
        }
        
        td {
            padding: 6px 6px;
            border: 1px solid #e2e8f0;
            vertical-align: top;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-bold {
            font-weight: bold;
        }
        
        .status-surplus {
            background-color: #c6f6d5;
            color: #22543d;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        
        .status-defisit {
            background-color: #fed7d7;
            color: #742a2a;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        
        .status-seimbang {
            background-color: #bee3f8;
            color: #2a4365;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        
        .status-tidak-ada-aktivitas {
            background-color: #e2e8f0;
            color: #4a5568;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        
        .total-row {
            background-color: #f7fafc;
            font-weight: bold;
        }
        
        .total-row td {
            border-top: 2px solid #2d3748;
            border-bottom: 2px solid #2d3748;
        }
        
        .final-total-row {
            background-color: #c6f6d5;
            font-weight: bold;
        }
        
        .final-total-row td {
            border-top: 2px solid #38a169;
            border-bottom: 2px solid #38a169;
        }
        
        .recent-section {
            margin-top: 30px;
            page-break-inside: avoid;
        }
        
        .recent-section h3 {
            font-size: 16px;
            font-weight: bold;
            margin: 0 0 15px 0;
            color: #2d3748;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 5px;
        }
        
        .recent-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 12px;
            margin-bottom: 5px;
            background-color: #f7fafc;
            border-radius: 5px;
            border-left: 3px solid #4299e1;
        }
        
        .recent-info {
            flex: 1;
        }
        
        .recent-description {
            font-weight: bold;
            color: #2d3748;
            font-size: 11px;
        }
        
        .recent-details {
            color: #4a5568;
            font-size: 9px;
        }
        
        .recent-amount {
            text-align: right;
        }
        
        .recent-amount-value {
            font-weight: bold;
            color: #4299e1;
            font-size: 11px;
        }
        
        .recent-amount-details {
            color: #4a5568;
            font-size: 9px;
        }
        
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #718096;
            border-top: 1px solid #e2e8f0;
            padding-top: 15px;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 15px;
            }
            
            .page-break {
                page-break-before: always;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>LAPORAN SALDO KAS</h1>
        <h2>Koperasi Karyawan</h2>
        <p>Periode: {{ \Carbon\Carbon::parse($periode . '-01')->format('F Y') }}</p>
        <p>Dicetak pada: {{ \Carbon\Carbon::now()->format('d M Y H:i:s') }}</p>
    </div>

    <!-- Info Section -->
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Periode Laporan:</span>
            <span class="info-value">{{ \Carbon\Carbon::parse($periode . '-01')->format('F Y') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Total Akun Kas:</span>
            <span class="info-value">{{ count($data) }} akun</span>
        </div>
        <div class="info-row">
            <span class="info-label">Tanggal Cetak:</span>
            <span class="info-value">{{ \Carbon\Carbon::now()->format('d M Y H:i:s') }}</span>
        </div>
    </div>

    <!-- Summary Section -->
    @if(isset($summary))
    <div class="summary-section">
        <h3>Ringkasan Laporan</h3>
        
        <div class="summary-grid">
            <div class="summary-card">
                <h3>Total Saldo Akhir</h3>
                <div class="value">Rp {{ number_format($summary['total_saldo_akhir'], 0, ',', '.') }}</div>
            </div>
            <div class="summary-card">
                <h3>Total Debet</h3>
                <div class="value">Rp {{ number_format($summary['total_debet'], 0, ',', '.') }}</div>
            </div>
            <div class="summary-card">
                <h3>Total Kredit</h3>
                <div class="value">Rp {{ number_format($summary['total_kredit'], 0, ',', '.') }}</div>
            </div>
            <div class="summary-card">
                <h3>Net Cash Flow</h3>
                <div class="value {{ $summary['net_cash_flow'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    Rp {{ number_format($summary['net_cash_flow'], 0, ',', '.') }}
                </div>
            </div>
        </div>
        
        <div class="performance-grid">
            <div class="performance-card">
                <h3>Rasio Likuiditas</h3>
                <div class="value">{{ number_format($performance['liquidity_ratio'], 1) }}%</div>
            </div>
            <div class="performance-card">
                <h3>Efisiensi Kas</h3>
                <div class="value">{{ number_format($performance['cash_efficiency'], 1) }}%</div>
            </div>
            <div class="performance-card">
                <h3>Tingkat Pertumbuhan</h3>
                <div class="value {{ $performance['growth_rate'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    {{ $performance['growth_rate'] >= 0 ? '+' : '' }}{{ number_format($performance['growth_rate'], 1) }}%
                </div>
            </div>
            <div class="performance-card">
                <h3>Konsentrasi Kas</h3>
                <div class="value">{{ number_format($performance['cash_concentration'], 1) }}%</div>
            </div>
        </div>
    </div>
    @endif

    <!-- Main Table -->
    <div class="table-section">
        <h3>Detail Saldo Kas</h3>
        
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 25%;">Nama Kas</th>
                    <th style="width: 20%;">Debet</th>
                    <th style="width: 20%;">Kredit</th>
                    <th style="width: 20%;">Saldo</th>
                    <th style="width: 10%;">Status</th>
                </tr>
            </thead>
            <tbody>
                <tr class="total-row">
                    <td class="text-center" colspan="2">SALDO PERIODE SEBELUMNYA</td>
                    <td class="text-center">-</td>
                    <td class="text-center">-</td>
                    <td class="text-right text-bold">{{ number_format($saldo_sblm, 0, ',', '.') }}</td>
                    <td class="text-center">-</td>
                </tr>
                @forelse($data as $row)
                <tr>
                    <td class="text-center">{{ $row['no'] }}</td>
                    <td>{{ $row['nama'] }}</td>
                    <td class="text-right">{{ number_format($row['debet'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($row['kredit'], 0, ',', '.') }}</td>
                    <td class="text-right {{ $row['saldo'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ number_format($row['saldo'], 0, ',', '.') }}
                    </td>
                    <td class="text-center">
                        <span class="
                            @if($row['status'] == 'Surplus') status-surplus
                            @elseif($row['status'] == 'Defisit') status-defisit
                            @elseif($row['status'] == 'Seimbang') status-seimbang
                            @else status-tidak-ada-aktivitas
                            @endif">
                            {{ $row['status'] }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 20px; color: #718096;">
                        Tidak ada data saldo kas untuk periode yang dipilih
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if(count($data) > 0)
            <tfoot>
                <tr class="total-row">
                    <td class="text-center text-bold" colspan="2">JUMLAH</td>
                    <td class="text-right text-bold">{{ number_format(array_sum(array_column($data, 'debet')), 0, ',', '.') }}</td>
                    <td class="text-right text-bold">{{ number_format(array_sum(array_column($data, 'kredit')), 0, ',', '.') }}</td>
                    <td class="text-right text-bold">{{ number_format($total, 0, ',', '.') }}</td>
                    <td class="text-center">-</td>
                </tr>
                <tr class="final-total-row">
                    <td class="text-center text-bold" colspan="2">TOTAL SALDO</td>
                    <td class="text-center">-</td>
                    <td class="text-center">-</td>
                    <td class="text-right text-bold">{{ number_format($total + $saldo_sblm, 0, ',', '.') }}</td>
                    <td class="text-center">-</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

    <!-- Recent Transactions Section -->
    @if(isset($recentTransactions) && $recentTransactions->count() > 0)
    <div class="recent-section">
        <h3>Transaksi Kas Terbaru</h3>
        
        @foreach($recentTransactions as $transaction)
        <div class="recent-item">
            <div class="recent-info">
                <div class="recent-description">{{ $transaction['keterangan'] }}</div>
                <div class="recent-details">{{ $transaction['tanggal'] }} • {{ $transaction['dari_kas'] }} → {{ $transaction['untuk_kas'] }}</div>
            </div>
            <div class="recent-amount">
                <div class="recent-amount-value {{ $transaction['tipe'] == 'Masuk' ? 'text-green-600' : 'text-red-600' }}">
                    {{ $transaction['tipe'] == 'Masuk' ? '+' : '-' }}Rp {{ number_format($transaction['jumlah'], 0, ',', '.') }}
                </div>
                <div class="recent-amount-details">{{ $transaction['jenis_akun'] }}</div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>Laporan ini dibuat secara otomatis oleh sistem Koperasi Karyawan</p>
        <p>Halaman 1 dari 1 • Dicetak pada {{ \Carbon\Carbon::now()->format('d M Y H:i:s') }}</p>
    </div>
</body>
</html>
