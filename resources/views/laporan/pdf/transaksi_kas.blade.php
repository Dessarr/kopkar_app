<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Transaksi Kas</title>
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
            color: #2563eb;
            margin: 0 0 5px 0;
        }
        
        .header p {
            font-size: 14px;
            color: #6b7280;
            margin: 0;
        }
        
        .summary-cards {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            gap: 15px;
        }
        
        .summary-card {
            flex: 1;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            color: white;
            font-weight: bold;
        }
        
        .summary-card.debet {
            background: linear-gradient(135deg, #10b981, #059669);
        }
        
        .summary-card.kredit {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }
        
        .summary-card.saldo-sebelumnya {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
        }
        
        .summary-card.saldo-akhir {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
        }
        
        .summary-card h3 {
            font-size: 12px;
            margin: 0 0 8px 0;
            opacity: 0.9;
        }
        
        .summary-card .amount {
            font-size: 16px;
            margin: 0;
        }
        
        .table-container {
            margin-bottom: 30px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
            vertical-align: top;
        }
        
        td.text-right {
            text-align: right;
        }
        
        .saldo-sebelumnya-row {
            background-color: #fef3c7;
            border: 2px solid #f59e0b;
            font-weight: bold;
        }
        
        .saldo-sebelumnya-row td {
            border-bottom: 2px solid #f59e0b;
        }
        
        .kode-transaksi {
            font-family: 'Courier New', monospace;
            color: #2563eb;
            font-weight: bold;
        }
        
        .debet-amount {
            color: #059669;
            font-weight: bold;
        }
        
        .kredit-amount {
            color: #dc2626;
            font-weight: bold;
        }
        
        .saldo-positive {
            color: #059669;
            font-weight: bold;
        }
        
        .saldo-negative {
            color: #dc2626;
            font-weight: bold;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 10px;
        }
        
        .footer-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 11px;
        }
        
        .footer-info div {
            flex: 1;
            text-align: center;
        }
        
        .footer-info i {
            margin-right: 5px;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #6b7280;
            font-style: italic;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 15px;
            }
            
            .summary-cards {
                page-break-inside: avoid;
            }
            
            table {
                page-break-inside: avoid;
            }
            
            .saldo-sebelumnya-row {
                page-break-after: avoid;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>LAPORAN TRANSAKSI KAS</h1>
        <h2>Koperasi Karyawan</h2>
        <p>Periode: {{ $periodeText }}</p>
        <p>Dicetak pada: {{ date('d/m/Y H:i:s') }}</p>
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="summary-card debet">
            <h3>Total Debet</h3>
            <p class="amount">Rp {{ number_format($totalDebet) }}</p>
        </div>
        <div class="summary-card kredit">
            <h3>Total Kredit</h3>
            <p class="amount">Rp {{ number_format($totalKredit) }}</p>
        </div>
        <div class="summary-card saldo-sebelumnya">
            <h3>Saldo Sebelumnya</h3>
            <p class="amount">Rp {{ number_format($saldoSebelumnya) }}</p>
        </div>
        <div class="summary-card saldo-akhir">
            <h3>Saldo Akhir</h3>
            <p class="amount">Rp {{ number_format($saldoAkhir) }}</p>
        </div>
    </div>

    <!-- Data Table -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">No.</th>
                    <th style="width: 12%;">Kode Transaksi</th>
                    <th style="width: 15%;">Tanggal Transaksi</th>
                    <th style="width: 18%;">Akun Transaksi</th>
                    <th style="width: 12%;">Dari Kas</th>
                    <th style="width: 12%;">Untuk Kas</th>
                    <th style="width: 12%;" class="text-right">Debet</th>
                    <th style="width: 12%;" class="text-right">Kredit</th>
                    <th style="width: 12%;" class="text-right">Saldo</th>
                </tr>
            </thead>
            <tbody>
                <!-- Saldo Sebelumnya Row -->
                <tr class="saldo-sebelumnya-row">
                    <td colspan="8">
                        <strong>SALDO SEBELUMNYA</strong>
                    </td>
                    <td class="text-right {{ $saldoSebelumnya >= 0 ? 'saldo-positive' : 'saldo-negative' }}">
                        <strong>Rp {{ number_format($saldoSebelumnya) }}</strong>
                    </td>
                </tr>
                
                @forelse($dataTransaksi as $index => $transaksi)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="kode-transaksi">
                        @php
                            $prefixes = [
                                '48' => 'TPJ', // Pemasukan
                                '7' => 'TBY',  // Pengeluaran
                                'transfer' => 'TRD',
                                'kas_keluar' => 'TRK',
                                'kas_fisik' => 'TRF',
                                'kas_deposit' => 'TKD',
                                'kas_kredit' => 'TKK'
                            ];
                            $prefix = $prefixes[$transaksi->transaksi] ?? 'TRX';
                            $kode = $prefix . str_pad($transaksi->id, 5, '0', STR_PAD_LEFT);
                        @endphp
                        {{ $kode }}
                    </td>
                    <td>{{ \Carbon\Carbon::parse($transaksi->tgl)->format('d/m/Y H:i') }}</td>
                    <td>{{ $transaksi->akun_transaksi ?? 'N/A' }}</td>
                    <td>{{ $transaksi->dari_kas_nama ?? '-' }}</td>
                    <td>{{ $transaksi->untuk_kas_nama ?? '-' }}</td>
                    <td class="text-right {{ $transaksi->debet > 0 ? 'debet-amount' : '' }}">
                        {{ $transaksi->debet > 0 ? 'Rp ' . number_format($transaksi->debet) : '-' }}
                    </td>
                    <td class="text-right {{ $transaksi->kredit > 0 ? 'kredit-amount' : '' }}">
                        {{ $transaksi->kredit > 0 ? 'Rp ' . number_format($transaksi->kredit) : '-' }}
                    </td>
                    <td class="text-right {{ $transaksi->saldo >= 0 ? 'saldo-positive' : 'saldo-negative' }}">
                        Rp {{ number_format($transaksi->saldo) }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="no-data">
                        Tidak ada data transaksi untuk periode yang dipilih
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="footer-info">
            <div>
                <i>📊</i> Total Data: {{ number_format($dataTransaksi->count()) }} transaksi
            </div>
            <div>
                <i>📅</i> Periode: {{ $periodeText }}
            </div>
            <div>
                <i>💰</i> Saldo Akhir: Rp {{ number_format($saldoAkhir) }}
            </div>
        </div>
        <p>
            <strong>Catatan:</strong> Laporan ini menggunakan prinsip pembukuan berpasangan (double-entry bookkeeping) 
            dengan perhitungan saldo berjalan untuk setiap transaksi kas.
        </p>
        <p>
            <em>Dicetak secara otomatis oleh sistem pada {{ date('d/m/Y H:i:s') }}</em>
        </p>
    </div>
</body>
</html>