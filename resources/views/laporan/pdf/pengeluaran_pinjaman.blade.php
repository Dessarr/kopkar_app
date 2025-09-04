<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pengeluaran Pinjaman</title>
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
            font-weight: normal;
            color: #64748b;
            margin: 0;
        }
        
        .info-section {
            background-color: #f8fafc;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #2563eb;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        
        .info-label {
            font-weight: bold;
            color: #475569;
        }
        
        .info-value {
            color: #1e293b;
        }
        
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .summary-card {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        
        .summary-card h3 {
            font-size: 14px;
            margin: 0 0 8px 0;
            opacity: 0.9;
        }
        
        .summary-card .value {
            font-size: 18px;
            font-weight: bold;
            margin: 0;
        }
        
        .table-container {
            margin-bottom: 25px;
        }
        
        .table-title {
            font-size: 16px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        th {
            background-color: #f1f5f9;
            color: #475569;
            font-weight: bold;
            padding: 10px 8px;
            text-align: left;
            border: 1px solid #e2e8f0;
            font-size: 11px;
        }
        
        td {
            padding: 8px;
            border: 1px solid #e2e8f0;
            font-size: 11px;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .status-lunas {
            background-color: #dcfce7;
            color: #166534;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }
        
        .status-berjalan {
            background-color: #dbeafe;
            color: #1e40af;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }
        
        .status-jatuh-tempo {
            background-color: #fef3c7;
            color: #92400e;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }
        
        .status-belum-mulai {
            background-color: #f3f4f6;
            color: #374151;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }
        
        .total-row {
            background-color: #f8fafc;
            font-weight: bold;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #e2e8f0;
            text-align: center;
            color: #64748b;
            font-size: 10px;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .recent-loans {
            background-color: #f8fafc;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .recent-loans h3 {
            color: #1e40af;
            margin-bottom: 15px;
            font-size: 14px;
        }
        
        .loan-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .loan-item:last-child {
            border-bottom: none;
        }
        
        .loan-info {
            flex: 1;
        }
        
        .loan-name {
            font-weight: bold;
            color: #1e293b;
        }
        
        .loan-details {
            font-size: 10px;
            color: #64748b;
        }
        
        .loan-amount {
            text-align: right;
            font-weight: bold;
            color: #1e293b;
        }
        
        .loan-percentage {
            font-size: 10px;
            color: #64748b;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>LAPORAN PENGELUARAN PINJAMAN</h1>
        <h2>Koperasi Karyawan</h2>
    </div>

    <!-- Info Section -->
    <div class="info-section">
        <div class="info-grid">
            <div>
                <div class="info-item">
                    <span class="info-label">Periode Laporan:</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($tgl_dari)->format('d M Y') }} - {{ \Carbon\Carbon::parse($tgl_samp)->format('d M Y') }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Tanggal Cetak:</span>
                    <span class="info-value">{{ \Carbon\Carbon::now()->format('d M Y H:i') }}</span>
                </div>
            </div>
            <div>
                <div class="info-item">
                    <span class="info-label">Total Pinjaman:</span>
                    <span class="info-value">{{ count($data) }} pinjaman</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Nilai Total:</span>
                    <span class="info-value">Rp {{ number_format($total['total_pinjaman'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    @if(isset($summary))
    <div class="summary-cards">
        <div class="summary-card">
            <h3>Total Pinjaman</h3>
            <p class="value">{{ number_format($summary['total_pinjaman']) }}</p>
        </div>
        <div class="summary-card">
            <h3>Nilai Pinjaman</h3>
            <p class="value">Rp {{ number_format($summary['total_nilai_pinjaman'], 0, ',', '.') }}</p>
        </div>
        <div class="summary-card">
            <h3>Total Tagihan</h3>
            <p class="value">Rp {{ number_format($summary['total_tagihan'], 0, ',', '.') }}</p>
        </div>
        <div class="summary-card">
            <h3>Sisa Tagihan</h3>
            <p class="value">Rp {{ number_format($summary['total_sisa_tagihan'], 0, ',', '.') }}</p>
        </div>
    </div>
    @endif

    <!-- Main Data Table -->
    <div class="table-container">
        <div class="table-title">Data Pinjaman Anggota</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 10%;">Tanggal</th>
                    <th style="width: 15%;">Nama</th>
                    <th style="width: 8%;">ID</th>
                    <th style="width: 12%;" class="text-right">Pinjaman</th>
                    <th style="width: 15%;">Jaminan</th>
                    <th style="width: 12%;" class="text-right">Tagihan</th>
                    <th style="width: 12%;" class="text-right">Dibayar</th>
                    <th style="width: 12%;" class="text-right">Sisa</th>
                    <th style="width: 8%;" class="text-center">Status</th>
                    <th style="width: 15%;">Alamat</th>
                    <th style="width: 10%;">Telp</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $row)
                <tr>
                    <td>{{ $row['no'] }}</td>
                    <td>{{ \Carbon\Carbon::parse($row['tgl_pinjam'])->format('d/m/Y') }}</td>
                    <td>{{ $row['nama'] }}</td>
                    <td>{{ $row['id'] }}</td>
                    <td class="text-right">Rp {{ number_format($row['jumlah'], 0, ',', '.') }}</td>
                    <td>{{ $row['jaminan'] }}</td>
                    <td class="text-right">Rp {{ number_format($row['tagihan'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($row['jml_bayar'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($row['sisa_tagihan'], 0, ',', '.') }}</td>
                    <td class="text-center">
                        <span class="
                            @if($row['status'] == 'Lunas') status-lunas
                            @elseif($row['status'] == 'Berjalan') status-berjalan
                            @elseif($row['status'] == 'Jatuh Tempo') status-jatuh-tempo
                            @else status-belum-mulai
                            @endif">
                            {{ $row['status'] }}
                        </span>
                    </td>
                    <td>{{ $row['alamat'] }}</td>
                    <td>{{ $row['notelp'] }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="12" class="text-center" style="padding: 20px; color: #64748b;">
                        Tidak ada data pinjaman untuk periode ini
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if(count($data) > 0)
            <tfoot>
                <tr class="total-row">
                    <td colspan="4" class="text-center">TOTAL</td>
                    <td class="text-right">Rp {{ number_format($total['total_pinjaman'], 0, ',', '.') }}</td>
                    <td></td>
                    <td class="text-right">Rp {{ number_format($total['total_tagihan'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($total['total_dibayar'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($total['total_sisa_tagihan'], 0, ',', '.') }}</td>
                    <td></td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

    <!-- Status Overview -->
    @if(isset($summary))
    <div class="table-container">
        <div class="table-title">Ringkasan Status Pinjaman</div>
        <table>
            <thead>
                <tr>
                    <th>Status</th>
                    <th class="text-right">Jumlah</th>
                    <th class="text-right">Persentase</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Lunas</td>
                    <td class="text-right">{{ $summary['pinjaman_lunas'] }}</td>
                    <td class="text-right">{{ $summary['total_pinjaman'] > 0 ? number_format(($summary['pinjaman_lunas'] / $summary['total_pinjaman']) * 100, 1) : 0 }}%</td>
                </tr>
                <tr>
                    <td>Berjalan</td>
                    <td class="text-right">{{ $summary['pinjaman_berjalan'] }}</td>
                    <td class="text-right">{{ $summary['total_pinjaman'] > 0 ? number_format(($summary['pinjaman_berjalan'] / $summary['total_pinjaman']) * 100, 1) : 0 }}%</td>
                </tr>
                <tr>
                    <td>Jatuh Tempo</td>
                    <td class="text-right">{{ $summary['pinjaman_jatuh_tempo'] }}</td>
                    <td class="text-right">{{ $summary['total_pinjaman'] > 0 ? number_format(($summary['pinjaman_jatuh_tempo'] / $summary['total_pinjaman']) * 100, 1) : 0 }}%</td>
                </tr>
                <tr>
                    <td>Belum Mulai</td>
                    <td class="text-right">{{ $summary['pinjaman_belum_mulai'] }}</td>
                    <td class="text-right">{{ $summary['total_pinjaman'] > 0 ? number_format(($summary['pinjaman_belum_mulai'] / $summary['total_pinjaman']) * 100, 1) : 0 }}%</td>
                </tr>
            </tbody>
        </table>
    </div>
    @endif

    <!-- Recent Loans -->
    @if(isset($recentLoans) && $recentLoans->count() > 0)
    <div class="recent-loans">
        <h3>Aktivitas Pinjaman Terbaru</h3>
        @foreach($recentLoans as $loan)
        <div class="loan-item">
            <div class="loan-info">
                <div class="loan-name">{{ $loan['anggota'] }}</div>
                <div class="loan-details">{{ $loan['id'] }} • {{ $loan['tgl_pinjam'] }}</div>
            </div>
            <div class="loan-amount">
                <div>Rp {{ number_format($loan['jumlah'], 0, ',', '.') }}</div>
                <div class="loan-percentage">{{ number_format($loan['persentase'], 1) }}% dibayar</div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>Laporan ini dibuat secara otomatis pada {{ \Carbon\Carbon::now()->format('d M Y H:i') }}</p>
        <p>Koperasi Karyawan - Sistem Informasi Manajemen</p>
    </div>
</body>
</html>
