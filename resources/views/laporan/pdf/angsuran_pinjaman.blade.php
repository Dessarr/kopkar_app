<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Angsuran Pinjaman - {{ \Carbon\Carbon::parse($tgl_dari)->format('d M Y') }} - {{ \Carbon\Carbon::parse($tgl_samp)->format('d M Y') }}</title>
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
            border-left: 4px solid #38a169;
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
            color: #38a169;
        }
        
        .status-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
        }
        
        .status-card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
        }
        
        .status-card h3 {
            font-size: 12px;
            font-weight: bold;
            margin: 0 0 8px 0;
            color: #4a5568;
        }
        
        .status-card .value {
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
            padding: 8px 4px;
            text-align: left;
            border: 1px solid #e2e8f0;
            font-size: 9px;
        }
        
        td {
            padding: 6px 4px;
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
        
        .status-lunas {
            background-color: #c6f6d5;
            color: #22543d;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        
        .status-tepat-waktu {
            background-color: #bee3f8;
            color: #2a4365;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        
        .status-terlambat {
            background-color: #faf089;
            color: #744210;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        
        .status-belum-bayar {
            background-color: #fed7d7;
            color: #742a2a;
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
            border-left: 3px solid #38a169;
        }
        
        .recent-info {
            flex: 1;
        }
        
        .recent-name {
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
            color: #38a169;
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
        <h1>LAPORAN ANGSURAN PINJAMAN</h1>
        <h2>Koperasi Karyawan</h2>
        <p>Periode: {{ \Carbon\Carbon::parse($tgl_dari)->format('d M Y') }} - {{ \Carbon\Carbon::parse($tgl_samp)->format('d M Y') }}</p>
        <p>Dicetak pada: {{ \Carbon\Carbon::now()->format('d M Y H:i:s') }}</p>
    </div>

    <!-- Info Section -->
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Periode Laporan:</span>
            <span class="info-value">{{ \Carbon\Carbon::parse($tgl_dari)->format('d M Y') }} - {{ \Carbon\Carbon::parse($tgl_samp)->format('d M Y') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Total Data:</span>
            <span class="info-value">{{ count($data) }} angsuran</span>
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
                <h3>Total Angsuran</h3>
                <div class="value">{{ number_format($summary['total_angsuran']) }}</div>
            </div>
            <div class="summary-card">
                <h3>Total Pokok</h3>
                <div class="value">Rp {{ number_format($summary['total_pokok'], 0, ',', '.') }}</div>
            </div>
            <div class="summary-card">
                <h3>Total Bunga</h3>
                <div class="value">Rp {{ number_format($summary['total_bunga'], 0, ',', '.') }}</div>
            </div>
            <div class="summary-card">
                <h3>Total Denda</h3>
                <div class="value">Rp {{ number_format($summary['total_denda'], 0, ',', '.') }}</div>
            </div>
        </div>
        
        <div class="status-grid">
            <div class="status-card">
                <h3>Lunas</h3>
                <div class="value">{{ $summary['angsuran_lunas'] }}</div>
            </div>
            <div class="status-card">
                <h3>Tepat Waktu</h3>
                <div class="value">{{ $summary['angsuran_tepat_waktu'] }}</div>
            </div>
            <div class="status-card">
                <h3>Terlambat</h3>
                <div class="value">{{ $summary['angsuran_terlambat'] }}</div>
            </div>
            <div class="status-card">
                <h3>Belum Bayar</h3>
                <div class="value">{{ $summary['angsuran_belum_bayar'] }}</div>
            </div>
        </div>
    </div>
    @endif

    <!-- Main Table -->
    <div class="table-section">
        <h3>Data Angsuran Pinjaman</h3>
        
        <table>
            <thead>
                <tr>
                    <th style="width: 3%;">No</th>
                    <th style="width: 8%;">Tanggal Pinjam</th>
                    <th style="width: 12%;">Nama</th>
                    <th style="width: 8%;">ID</th>
                    <th style="width: 10%;">Pinjaman Awal</th>
                    <th style="width: 4%;">JW</th>
                    <th style="width: 4%;">%</th>
                    <th style="width: 10%;">Saldo Pinjaman</th>
                    <th style="width: 8%;">Pokok</th>
                    <th style="width: 8%;">Bunga</th>
                    <th style="width: 8%;">Denda</th>
                    <th style="width: 8%;">Jumlah</th>
                    <th style="width: 10%;">Saldo Akhir</th>
                    <th style="width: 6%;">Angsuran Ke</th>
                    <th style="width: 8%;">Tgl. Bayar</th>
                    <th style="width: 8%;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $row)
                <tr>
                    <td class="text-center">{{ $row['no'] }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($row['tgl_pinjam'])->format('d/m/Y') }}</td>
                    <td>{{ $row['nama'] }}</td>
                    <td class="text-center">{{ $row['id'] }}</td>
                    <td class="text-right">Rp {{ number_format($row['jumlah'], 0, ',', '.') }}</td>
                    <td class="text-center">{{ $row['lama_angsuran'] }}</td>
                    <td class="text-center">{{ $row['jumlah_bunga'] }}%</td>
                    <td class="text-right">Rp {{ number_format($row['saldo_pinjaman'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($row['pokok'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($row['bunga'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($row['denda'], 0, ',', '.') }}</td>
                    <td class="text-right text-bold">Rp {{ number_format($row['jumlah_angsuran'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($row['saldo_akhir'], 0, ',', '.') }}</td>
                    <td class="text-center">{{ $row['angsuran_ke'] }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($row['tgl_bayar'])->format('d/m/Y') }}</td>
                    <td class="text-center">
                        <span class="
                            @if($row['status'] == 'Lunas') status-lunas
                            @elseif($row['status'] == 'Tepat Waktu') status-tepat-waktu
                            @elseif($row['status'] == 'Terlambat') status-terlambat
                            @else status-belum-bayar
                            @endif">
                            {{ $row['status'] }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="16" class="text-center" style="padding: 20px; color: #718096;">
                        Tidak ada data angsuran untuk periode yang dipilih
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if(count($data) > 0)
            <tfoot>
                <tr class="total-row">
                    <td colspan="8" class="text-center text-bold">TOTAL</td>
                    <td class="text-right text-bold">Rp {{ number_format($summary['total_pokok'], 0, ',', '.') }}</td>
                    <td class="text-right text-bold">Rp {{ number_format($summary['total_bunga'], 0, ',', '.') }}</td>
                    <td class="text-right text-bold">Rp {{ number_format($summary['total_denda'], 0, ',', '.') }}</td>
                    <td class="text-right text-bold">Rp {{ number_format($summary['total_jumlah_angsuran'], 0, ',', '.') }}</td>
                    <td colspan="4"></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

    <!-- Recent Installments Section -->
    @if(isset($recentInstallments) && $recentInstallments->count() > 0)
    <div class="recent-section">
        <h3>Aktivitas Angsuran Terbaru</h3>
        
        @foreach($recentInstallments as $installment)
        <div class="recent-item">
            <div class="recent-info">
                <div class="recent-name">{{ $installment['anggota'] }}</div>
                <div class="recent-details">{{ $installment['id'] }} • {{ $installment['tgl_bayar'] }}</div>
            </div>
            <div class="recent-amount">
                <div class="recent-amount-value">Rp {{ number_format($installment['jumlah_angsuran'], 0, ',', '.') }}</div>
                <div class="recent-amount-details">Angsuran ke-{{ $installment['angsuran_ke'] }}</div>
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
