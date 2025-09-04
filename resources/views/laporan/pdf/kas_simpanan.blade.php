<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Kas Simpanan</title>
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
            border-bottom: 3px solid #10b981;
            padding-bottom: 15px;
        }
        
        .header h1 {
            font-size: 20px;
            font-weight: bold;
            color: #059669;
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
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
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
        
        .summary-card.red {
            background: linear-gradient(135deg, #ef4444, #dc2626);
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
        
        .table-container {
            margin-top: 15px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        th {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 8px 4px;
            text-align: left;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        
        th.text-center {
            text-align: center;
        }
        
        td {
            padding: 6px 4px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 9px;
        }
        
        td.text-center {
            text-align: center;
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
            padding: 8px 4px;
        }
        
        .member-id {
            font-family: monospace;
            color: #2563eb;
            font-weight: bold;
        }
        
        .role-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 12px;
            font-size: 8px;
            font-weight: bold;
        }
        
        .role-pengurus {
            background-color: #e9d5ff;
            color: #7c3aed;
        }
        
        .role-anggota {
            background-color: #f3f4f6;
            color: #374151;
        }
        
        .savings-detail {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        
        .savings-amount {
            font-size: 8px;
        }
        
        .savings-label {
            font-size: 7px;
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
        
        .icon {
            margin-right: 4px;
        }
        
        .icon.piggy {
            color: #10b981;
        }
        
        .icon.users {
            color: #3b82f6;
        }
        
        .icon.calculator {
            color: #6b7280;
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
        
        .compact table {
            font-size: 8px;
        }
        
        .compact th {
            padding: 6px 3px;
            font-size: 8px;
        }
        
        .compact td {
            padding: 4px 3px;
            font-size: 8px;
        }
    </style>
</head>
<body class="compact">
    <!-- Header -->
    <div class="header">
        <h1>LAPORAN KAS SIMPANAN</h1>
        <h2>Member Savings Cash Report</h2>
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
            <span class="info-label">Total Anggota:</span>
            <span class="info-value">{{ $summary['total_anggota'] }} anggota</span>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="summary-card">
            <h3>Total Anggota</h3>
            <p class="amount">{{ $summary['total_anggota'] }}</p>
        </div>
        <div class="summary-card green">
            <h3>Total Setoran</h3>
            <p class="amount">Rp {{ number_format($summary['total_simpanan']) }}</p>
        </div>
        <div class="summary-card red">
            <h3>Total Penarikan</h3>
            <p class="amount">Rp {{ number_format($summary['total_penarikan']) }}</p>
        </div>
        <div class="summary-card purple">
            <h3>Saldo Bersih</h3>
            <p class="amount {{ $summary['saldo_bersih'] >= 0 ? 'positive' : 'negative' }}">
                Rp {{ number_format($summary['saldo_bersih']) }}
            </p>
        </div>
    </div>

    <!-- Table Section -->
    @if(count($data) > 0)
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>ID Anggota</th>
                    <th>Nama Anggota</th>
                    <th>Jabatan</th>
                    <th>Departemen</th>
                    @foreach($jenisSimpanan as $jenis)
                    <th class="text-center">
                        <div style="display: flex; flex-direction: column; align-items: center;">
                            <span style="font-weight: bold; margin-bottom: 2px;">{{ $jenis->jns_simpan }}</span>
                            <div style="display: flex; gap: 4px; font-size: 7px;">
                                <span style="background: #bbf7d0; color: #166534; padding: 1px 3px; border-radius: 2px;">Setoran</span>
                                <span style="background: #fecaca; color: #991b1b; padding: 1px 3px; border-radius: 2px;">Penarikan</span>
                            </div>
                        </div>
                    </th>
                    @endforeach
                    <th class="text-center">
                        <div style="display: flex; flex-direction: column; align-items: center;">
                            <span style="font-weight: bold; margin-bottom: 2px;">Total</span>
                            <div style="display: flex; gap: 4px; font-size: 7px;">
                                <span style="background: #bbf7d0; color: #166534; padding: 1px 3px; border-radius: 2px;">Setoran</span>
                                <span style="background: #fecaca; color: #991b1b; padding: 1px 3px; border-radius: 2px;">Penarikan</span>
                            </div>
                        </div>
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $row)
                <tr>
                    <td>{{ $row['no'] }}</td>
                    <td class="member-id">{{ $row['id'] }}</td>
                    <td style="font-weight: bold;">{{ $row['nama'] }}</td>
                    <td>
                        <span class="role-badge {{ $row['jabatan'] == 'Pengurus' ? 'role-pengurus' : 'role-anggota' }}">
                            {{ $row['jabatan'] }}
                        </span>
                    </td>
                    <td>{{ $row['departemen'] }}</td>
                    @foreach($jenisSimpanan as $jenis)
                    <td class="text-center">
                        @if(isset($row[$jenis->id]))
                        <div class="savings-detail">
                            <div class="savings-amount positive">
                                Rp {{ number_format($row[$jenis->id]['debet']) }}
                            </div>
                            <div class="savings-amount negative">
                                Rp {{ number_format($row[$jenis->id]['kredit']) }}
                            </div>
                            <div class="savings-label">
                                Saldo: Rp {{ number_format($row[$jenis->id]['saldo']) }}
                            </div>
                            @if($row[$jenis->id]['transaksi_count'] > 0)
                            <div class="savings-label" style="color: #2563eb;">
                                {{ $row[$jenis->id]['transaksi_count'] }} trans
                            </div>
                            @endif
                        </div>
                        @else
                        <div style="color: #9ca3af;">-</div>
                        @endif
                    </td>
                    @endforeach
                    <td class="text-center">
                        <div class="savings-detail">
                            <div class="savings-amount positive">
                                Rp {{ number_format($row['total_simpanan']) }}
                            </div>
                            <div class="savings-amount negative">
                                Rp {{ number_format($row['total_penarikan']) }}
                            </div>
                            <div class="savings-label" style="font-weight: bold; {{ $row['saldo_bersih'] >= 0 ? 'color: #059669;' : 'color: #dc2626;' }}">
                                Saldo: Rp {{ number_format($row['saldo_bersih']) }}
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="5" style="font-weight: bold;">
                        <span class="icon calculator">🧮</span>
                        <strong>TOTAL</strong>
                    </td>
                    @foreach($jenisSimpanan as $jenis)
                    <td class="text-center">
                        <div class="savings-detail">
                            <div class="savings-amount positive">
                                Rp {{ number_format($summary['per_jenis'][$jenis->id]['debet']) }}
                            </div>
                            <div class="savings-amount negative">
                                Rp {{ number_format($summary['per_jenis'][$jenis->id]['kredit']) }}
                            </div>
                            <div class="savings-label" style="font-weight: bold; {{ $summary['per_jenis'][$jenis->id]['saldo'] >= 0 ? 'color: #059669;' : 'color: #dc2626;' }}">
                                Rp {{ number_format($summary['per_jenis'][$jenis->id]['saldo']) }}
                            </div>
                        </div>
                    </td>
                    @endforeach
                    <td class="text-center">
                        <div class="savings-detail">
                            <div class="savings-amount positive">
                                Rp {{ number_format($summary['total_simpanan']) }}
                            </div>
                            <div class="savings-amount negative">
                                Rp {{ number_format($summary['total_penarikan']) }}
                            </div>
                            <div class="savings-label" style="font-weight: bold; {{ $summary['saldo_bersih'] >= 0 ? 'color: #059669;' : 'color: #dc2626;' }}">
                                Rp {{ number_format($summary['saldo_bersih']) }}
                            </div>
                        </div>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
    @else
    <div class="no-data">
        <i class="fas fa-inbox"></i>
        <h3>Tidak ada data simpanan</h3>
        <p>Tidak ada transaksi simpanan untuk periode <strong>{{ $periodeText }}</strong></p>
    </div>
    @endif

    <!-- Summary Footer -->
    @if(count($data) > 0)
    <div style="margin-top: 20px; padding: 15px; background: linear-gradient(135deg, #f9fafb, #f3f4f6); border-radius: 6px; border: 1px solid #e5e7eb;">
        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 9px; color: #6b7280;">
            <div style="display: flex; align-items: center;">
                <span class="icon users">👥</span>
                <span style="font-weight: bold;">Total Anggota:</span> {{ $summary['total_anggota'] }} anggota
            </div>
            <div style="display: flex; align-items: center;">
                <span class="icon calculator">📅</span>
                <span style="font-weight: bold;">Periode:</span> {{ $periodeText }}
            </div>
            <div style="display: flex; align-items: center;">
                <span class="icon piggy">🐷</span>
                <span style="font-weight: bold;">Jenis Simpanan:</span> {{ count($jenisSimpanan) }} jenis
            </div>
        </div>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>Laporan ini dibuat secara otomatis oleh sistem Koperasi pada {{ \Carbon\Carbon::now()->format('d F Y H:i') }}</p>
        <p>Halaman 1 dari 1</p>
    </div>
</body>
</html>
