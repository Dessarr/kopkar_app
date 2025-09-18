<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Buku Besar</title>
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
        
        .saldo-awal-row {
            background-color: #fef3c7;
            border: 2px solid #f59e0b;
            font-weight: bold;
        }
        
        .saldo-awal-row td {
            border-bottom: 2px solid #f59e0b;
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
        
        .badge {
            display: inline-block;
            padding: 2px 8px;
            background-color: #dbeafe;
            color: #1e40af;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
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
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>LAPORAN BUKU BESAR</h1>
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
            <span class="info-label">Total Saldo Keseluruhan:</span>
            <span class="info-value">Rp {{ number_format($totalSaldoKeseluruhan) }}</span>
        </div>
    </div>

    <!-- Main Content -->
    @if(count($processedData) > 0)
        @foreach($processedData as $kasData)
            @php
                $kas = $kasData['kas'];
                $transaksi = $kasData['transaksi'];
                $saldoAwal = $kasData['saldo_awal'];
                $totalDebet = $kasData['total_debet'];
                $totalKredit = $kasData['total_kredit'];
                $saldoAkhir = $kasData['saldo_akhir'];
            @endphp
            
            <!-- Kas Header -->
            <div class="kas-header" style="background-color: #2563eb; color: white; padding: 15px; margin: 20px 0 10px 0; border-radius: 5px;">
                <h2 style="margin: 0; font-size: 18px;">{{ $kas->nama }}</h2>
            </div>

            <!-- Summary Cards for this Kas -->
            <div class="summary-cards" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 20px;">
                <div class="summary-card" style="background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 15px; border-radius: 5px; text-align: center;">
                    <h3 style="margin: 0 0 8px 0; font-size: 12px; color: #64748b;">Saldo Awal</h3>
                    <p class="amount {{ $saldoAwal >= 0 ? 'positive' : 'negative' }}" style="margin: 0; font-size: 14px; font-weight: bold; color: {{ $saldoAwal >= 0 ? '#059669' : '#dc2626' }};">
                        Rp {{ number_format($saldoAwal) }}
                    </p>
                </div>
                <div class="summary-card green" style="background-color: #f0fdf4; border: 1px solid #bbf7d0; padding: 15px; border-radius: 5px; text-align: center;">
                    <h3 style="margin: 0 0 8px 0; font-size: 12px; color: #166534;">Total Debet</h3>
                    <p class="amount" style="margin: 0; font-size: 14px; font-weight: bold; color: #059669;">Rp {{ number_format($totalDebet) }}</p>
                </div>
                <div class="summary-card red" style="background-color: #fef2f2; border: 1px solid #fecaca; padding: 15px; border-radius: 5px; text-align: center;">
                    <h3 style="margin: 0 0 8px 0; font-size: 12px; color: #991b1b;">Total Kredit</h3>
                    <p class="amount" style="margin: 0; font-size: 14px; font-weight: bold; color: #dc2626;">Rp {{ number_format($totalKredit) }}</p>
                </div>
                <div class="summary-card purple" style="background-color: #faf5ff; border: 1px solid #e9d5ff; padding: 15px; border-radius: 5px; text-align: center;">
                    <h3 style="margin: 0 0 8px 0; font-size: 12px; color: #6b21a8;">Saldo Akhir</h3>
                    <p class="amount {{ $saldoAkhir >= 0 ? 'positive' : 'negative' }}" style="margin: 0; font-size: 14px; font-weight: bold; color: {{ $saldoAkhir >= 0 ? '#059669' : '#dc2626' }};">
                        Rp {{ number_format($saldoAkhir) }}
                    </p>
                </div>
            </div>

            <!-- Table Section for this Kas -->
            @if(count($transaksi) > 0)
            <div class="table-container" style="margin-bottom: 30px;">
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                    <thead>
                        <tr style="background-color: #f8fafc;">
                            <th style="padding: 8px 12px; text-align: left; border: 1px solid #d1d5db; font-weight: bold; font-size: 11px;">No.</th>
                            <th style="padding: 8px 12px; text-align: left; border: 1px solid #d1d5db; font-weight: bold; font-size: 11px;">Tanggal</th>
                            <th style="padding: 8px 12px; text-align: left; border: 1px solid #d1d5db; font-weight: bold; font-size: 11px;">Jenis Transaksi</th>
                            <th style="padding: 8px 12px; text-align: left; border: 1px solid #d1d5db; font-weight: bold; font-size: 11px;">Keterangan</th>
                            <th style="padding: 8px 12px; text-align: left; border: 1px solid #d1d5db; font-weight: bold; font-size: 11px;">Nama</th>
                            <th style="padding: 8px 12px; text-align: right; border: 1px solid #d1d5db; font-weight: bold; font-size: 11px;">Debet</th>
                            <th style="padding: 8px 12px; text-align: right; border: 1px solid #d1d5db; font-weight: bold; font-size: 11px;">Kredit</th>
                            <th style="padding: 8px 12px; text-align: right; border: 1px solid #d1d5db; font-weight: bold; font-size: 11px;">Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Saldo Awal Row -->
                        <tr style="background-color: #f3f4f6;">
                            <td colspan="7" style="padding: 8px 12px; border: 1px solid #d1d5db; font-weight: bold;">
                                <strong>SALDO AWAL</strong>
                            </td>
                            <td class="text-right {{ $saldoAwal >= 0 ? 'positive' : 'negative' }}" style="padding: 8px 12px; border: 1px solid #d1d5db; text-align: right; font-weight: bold; color: {{ $saldoAwal >= 0 ? '#059669' : '#dc2626' }};">
                                <strong>Rp {{ number_format($saldoAwal) }}</strong>
                            </td>
                        </tr>
                        
                        @foreach($transaksi as $index => $row)
                        <tr>
                            <td style="padding: 8px 12px; border: 1px solid #d1d5db; font-size: 11px;">{{ $row['no'] }}</td>
                            <td style="padding: 8px 12px; border: 1px solid #d1d5db; font-size: 11px;">{{ \Carbon\Carbon::parse($row['tanggal'])->format('d/m/Y') }}</td>
                            <td style="padding: 8px 12px; border: 1px solid #d1d5db; font-size: 11px;">{{ $row['jenis_transaksi'] }}</td>
                            <td style="padding: 8px 12px; border: 1px solid #d1d5db; font-size: 11px;">{{ $row['keterangan'] }}</td>
                            <td style="padding: 8px 12px; border: 1px solid #d1d5db; font-size: 11px;">{{ $row['nama'] }}</td>
                            <td class="text-right" style="padding: 8px 12px; border: 1px solid #d1d5db; text-align: right; font-size: 11px; color: {{ $row['debet'] > 0 ? '#059669' : '#000' }};">
                                {{ $row['debet'] > 0 ? 'Rp ' . number_format($row['debet']) : '-' }}
                            </td>
                            <td class="text-right" style="padding: 8px 12px; border: 1px solid #d1d5db; text-align: right; font-size: 11px; color: {{ $row['kredit'] > 0 ? '#dc2626' : '#000' }};">
                                {{ $row['kredit'] > 0 ? 'Rp ' . number_format($row['kredit']) : '-' }}
                            </td>
                            <td class="text-right {{ $row['saldo'] >= 0 ? 'positive' : 'negative' }}" style="padding: 8px 12px; border: 1px solid #d1d5db; text-align: right; font-size: 11px; font-weight: bold; color: {{ $row['saldo'] >= 0 ? '#059669' : '#dc2626' }};">
                                <strong>Rp {{ number_format($row['saldo']) }}</strong>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background-color: #f3f4f6; font-weight: bold;">
                            <td colspan="5" style="padding: 8px 12px; border: 1px solid #d1d5db;">
                                <strong>TOTAL {{ $kas->nama }}</strong>
                            </td>
                            <td class="text-right positive" style="padding: 8px 12px; border: 1px solid #d1d5db; text-align: right; color: #059669;">
                                <strong>Rp {{ number_format($totalDebet) }}</strong>
                            </td>
                            <td class="text-right negative" style="padding: 8px 12px; border: 1px solid #d1d5db; text-align: right; color: #dc2626;">
                                <strong>Rp {{ number_format($totalKredit) }}</strong>
                            </td>
                            <td class="text-right {{ $saldoAkhir >= 0 ? 'positive' : 'negative' }}" style="padding: 8px 12px; border: 1px solid #d1d5db; text-align: right; color: {{ $saldoAkhir >= 0 ? '#059669' : '#dc2626' }};">
                                <strong>Rp {{ number_format($saldoAkhir) }}</strong>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @else
            <div class="no-data" style="text-align: center; padding: 40px; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 5px; margin-bottom: 20px;">
                <h3 style="margin: 0 0 10px 0; color: #64748b;">Tidak ada data transaksi</h3>
                <p style="margin: 0; color: #64748b;">Tidak ada transaksi untuk kas <strong>{{ $kas->nama }}</strong> pada periode <strong>{{ $periodeText }}</strong></p>
            </div>
            @endif
            
            <!-- Add page break between kas accounts -->
            @if(!$loop->last)
                <div class="page-break" style="page-break-before: always;"></div>
            @endif
        @endforeach
    @else
    <div class="no-data" style="text-align: center; padding: 40px; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 5px;">
        <h3 style="margin: 0 0 10px 0; color: #64748b;">Tidak ada data transaksi</h3>
        <p style="margin: 0; color: #64748b;">Tidak ada data transaksi pada periode <strong>{{ $periodeText }}</strong></p>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>Laporan ini dibuat secara otomatis oleh sistem Koperasi pada {{ \Carbon\Carbon::now()->format('d F Y H:i') }}</p>
        <p>Halaman 1 dari 1</p>
    </div>
</body>
</html>
