<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Neraca Saldo</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 20px;
        }
        
        .header h1 {
            font-size: 24px;
            font-weight: bold;
            color: #1e40af;
            margin: 0 0 10px 0;
        }
        
        .header h2 {
            font-size: 18px;
            font-weight: bold;
            color: #374151;
            margin: 0 0 5px 0;
        }
        
        .header p {
            font-size: 14px;
            color: #6b7280;
            margin: 0;
        }
        
        .info-section {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
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
            margin-bottom: 25px;
            gap: 15px;
        }
        
        .summary-card {
            flex: 1;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        
        .summary-card.green {
            background: linear-gradient(135deg, #10b981, #059669);
        }
        
        .summary-card.red {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }
        
        .summary-card.purple {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
        }
        
        .summary-card h3 {
            font-size: 12px;
            margin: 0 0 8px 0;
            opacity: 0.9;
        }
        
        .summary-card .amount {
            font-size: 16px;
            font-weight: bold;
            margin: 0;
        }
        
        .table-container {
            margin-top: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        th {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        th.text-right {
            text-align: right;
        }
        
        td {
            padding: 10px 8px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 11px;
        }
        
        td.text-right {
            text-align: right;
        }
        
        .header-row {
            background-color: #f3f4f6;
            font-weight: bold;
            border: 2px solid #374151;
        }
        
        .header-row td {
            border-bottom: 2px solid #374151;
            padding: 12px 8px;
            font-size: 12px;
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
            padding: 12px 8px;
        }
        
        .balance-check {
            margin-top: 20px;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        
        .balance-check.balanced {
            background-color: #f0fdf4;
            border: 2px solid #22c55e;
            color: #166534;
        }
        
        .balance-check.unbalanced {
            background-color: #fef2f2;
            border: 2px solid #ef4444;
            color: #991b1b;
        }
        
        .balance-check h3 {
            margin: 0 0 8px 0;
            font-size: 16px;
        }
        
        .balance-check p {
            margin: 0;
            font-size: 14px;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 10px;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #6b7280;
        }
        
        .no-data i {
            font-size: 48px;
            color: #d1d5db;
            margin-bottom: 15px;
        }
        
        .no-data h3 {
            font-size: 18px;
            margin: 0 0 10px 0;
            color: #374151;
        }
        
        .no-data p {
            margin: 0;
            font-size: 14px;
        }
        
        .icon {
            margin-right: 8px;
        }
        
        .icon.folder {
            color: #2563eb;
        }
        
        .icon.wallet {
            color: #059669;
        }
        
        .icon.file {
            color: #6b7280;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>LAPORAN NERACA SALDO</h1>
        <h2>Trial Balance Report</h2>
        <p>Periode: {{ $periodeText }}</p>
    </div>

    <!-- Info Section -->
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Periode:</span>
            <span class="info-value">{{ $periodeText }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Tanggal Cetak:</span>
            <span class="info-value">{{ \Carbon\Carbon::now()->format('d F Y H:i') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Status Keseimbangan:</span>
            <span class="info-value {{ $data['is_balanced'] ? 'positive' : 'negative' }}">
                {{ $data['is_balanced'] ? 'SEIMBANG' : 'TIDAK SEIMBANG' }}
            </span>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="summary-card green">
            <h3>Total Debet</h3>
            <p class="amount">Rp {{ number_format($data['totalDebet']) }}</p>
        </div>
        <div class="summary-card red">
            <h3>Total Kredit</h3>
            <p class="amount">Rp {{ number_format($data['totalKredit']) }}</p>
        </div>
        <div class="summary-card purple">
            <h3>Selisih</h3>
            <p class="amount {{ $data['is_balanced'] ? 'positive' : 'negative' }}">
                Rp {{ number_format(abs($data['totalDebet'] - $data['totalKredit'])) }}
            </p>
        </div>
    </div>

    <!-- Table Section -->
    @if(count($data['rows']) > 0)
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Kode Akun</th>
                    <th>Nama Akun</th>
                    <th class="text-right">Debet</th>
                    <th class="text-right">Kredit</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['rows'] as $row)
                <tr class="{{ isset($row['is_header']) && $row['is_header'] ? 'header-row' : '' }}">
                    <td>
                        @if(isset($row['is_header']) && $row['is_header'])
                            <span class="icon folder">📁</span>
                        @elseif(isset($row['is_kas']) && $row['is_kas'])
                            <span class="icon wallet">💰</span>
                        @else
                            <span class="icon file">📄</span>
                        @endif
                        {{ $row['no'] }}
                    </td>
                    <td>{{ $row['nama'] }}</td>
                    <td class="text-right {{ $row['debet'] > 0 ? 'positive' : '' }}">
                        {{ $row['debet'] > 0 ? 'Rp ' . number_format($row['debet']) : '-' }}
                    </td>
                    <td class="text-right {{ $row['kredit'] > 0 ? 'negative' : '' }}">
                        {{ $row['kredit'] > 0 ? 'Rp ' . number_format($row['kredit']) : '-' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="2">
                        <strong>JUMLAH</strong>
                    </td>
                    <td class="text-right positive">
                        <strong>Rp {{ number_format($data['totalDebet']) }}</strong>
                    </td>
                    <td class="text-right negative">
                        <strong>Rp {{ number_format($data['totalKredit']) }}</strong>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Balance Check -->
    <div class="balance-check {{ $data['is_balanced'] ? 'balanced' : 'unbalanced' }}">
        <h3>
            {{ $data['is_balanced'] ? '✅ Neraca Saldo Seimbang' : '❌ Neraca Saldo Tidak Seimbang' }}
        </h3>
        <p>
            @if($data['is_balanced'])
                Total Debet (Rp {{ number_format($data['totalDebet']) }}) = Total Kredit (Rp {{ number_format($data['totalKredit']) }})
            @else
                Selisih: Rp {{ number_format(abs($data['totalDebet'] - $data['totalKredit'])) }}
            @endif
        </p>
    </div>
    @else
    <div class="no-data">
        <i class="fas fa-inbox"></i>
        <h3>Tidak ada data neraca saldo</h3>
        <p>Tidak ada transaksi untuk periode <strong>{{ $periodeText }}</strong></p>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>Laporan ini dibuat secara otomatis oleh sistem Koperasi pada {{ \Carbon\Carbon::now()->format('d F Y H:i') }}</p>
        <p>Halaman 1 dari 1</p>
    </div>
</body>
</html>
