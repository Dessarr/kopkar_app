<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Rekapitulasi Tagihan - {{ \Carbon\Carbon::parse($periode . '-01')->format('F Y') }}</title>
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
            border-left: 4px solid #805ad5;
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
            color: #805ad5;
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
        
        .status-sempurna {
            background-color: #c6f6d5;
            color: #22543d;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        
        .status-sangat-baik {
            background-color: #bee3f8;
            color: #2a4365;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        
        .status-baik {
            background-color: #c3ddfd;
            color: #2a4365;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        
        .status-cukup {
            background-color: #faf089;
            color: #744210;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        
        .status-perlu-perhatian {
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
            border-left: 3px solid #805ad5;
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
            color: #805ad5;
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
        <h1>LAPORAN REKAPITULASI TAGIHAN</h1>
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
            <span class="info-label">Total Data:</span>
            <span class="info-value">{{ count($data) }} hari</span>
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
                <h3>Total Tagihan</h3>
                <div class="value">{{ number_format($summary['total_tagihan']) }}</div>
            </div>
            <div class="summary-card">
                <h3>Target Pokok</h3>
                <div class="value">Rp {{ number_format($summary['total_target_pokok'], 0, ',', '.') }}</div>
            </div>
            <div class="summary-card">
                <h3>Realisasi Pokok</h3>
                <div class="value">Rp {{ number_format($summary['total_realisasi_pokok'], 0, ',', '.') }}</div>
            </div>
            <div class="summary-card">
                <h3>Rata-rata Koleksi</h3>
                <div class="value">{{ number_format($summary['rata_rata_koleksi'], 1) }}%</div>
            </div>
        </div>
        
        <div class="performance-grid">
            <div class="performance-card">
                <h3>Hari Sempurna</h3>
                <div class="value">{{ $performance['hari_sempurna'] }}</div>
            </div>
            <div class="performance-card">
                <h3>Hari Bermasalah</h3>
                <div class="value">{{ $performance['hari_bermasalah'] }}</div>
            </div>
            <div class="performance-card">
                <h3>Tingkat Koleksi</h3>
                <div class="value">{{ number_format($performance['tingkat_koleksi_keseluruhan'], 1) }}%</div>
            </div>
            <div class="performance-card">
                <h3>Trend Koleksi</h3>
                <div class="value {{ $performance['trend_koleksi'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    {{ $performance['trend_koleksi'] >= 0 ? '+' : '' }}{{ number_format($performance['trend_koleksi'], 1) }}%
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Main Table -->
    <div class="table-section">
        <h3>Data Rekapitulasi Harian</h3>
        
        <table>
            <thead>
                <tr>
                    <th style="width: 3%;">No</th>
                    <th style="width: 8%;">Tanggal</th>
                    <th style="width: 8%;">Tagihan Hari Ini</th>
                    <th style="width: 10%;">Target Pokok</th>
                    <th style="width: 10%;">Target Bunga</th>
                    <th style="width: 8%;">Tagihan Masuk</th>
                    <th style="width: 10%;">Realisasi Pokok</th>
                    <th style="width: 10%;">Realisasi Bunga</th>
                    <th style="width: 8%;">Tagihan Bermasalah</th>
                    <th style="width: 10%;">Tidak Bayar Pokok</th>
                    <th style="width: 10%;">Tidak Bayar Bunga</th>
                    <th style="width: 6%;">% Koleksi</th>
                    <th style="width: 8%;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $row)
                <tr>
                    <td class="text-center">{{ $row['no'] }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($row['tanggal'])->format('d/m/Y') }}</td>
                    <td class="text-center">{{ $row['jml_tagihan'] }}</td>
                    <td class="text-right">Rp {{ number_format($row['target_pokok'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($row['target_bunga'], 0, ',', '.') }}</td>
                    <td class="text-center">{{ $row['tagihan_masuk'] }}</td>
                    <td class="text-right">Rp {{ number_format($row['realisasi_pokok'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($row['realisasi_bunga'], 0, ',', '.') }}</td>
                    <td class="text-center">{{ $row['tagihan_bermasalah'] }}</td>
                    <td class="text-right">Rp {{ number_format($row['tidak_bayar_pokok'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($row['tidak_bayar_bunga'], 0, ',', '.') }}</td>
                    <td class="text-center">{{ number_format($row['persentase_koleksi'], 1) }}%</td>
                    <td class="text-center">
                        <span class="
                            @if($row['status'] == 'Sempurna') status-sempurna
                            @elseif($row['status'] == 'Sangat Baik') status-sangat-baik
                            @elseif($row['status'] == 'Baik') status-baik
                            @elseif($row['status'] == 'Cukup') status-cukup
                            @else status-perlu-perhatian
                            @endif">
                            {{ $row['status'] }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="13" class="text-center" style="padding: 20px; color: #718096;">
                        Tidak ada data rekapitulasi untuk periode yang dipilih
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if(count($data) > 0)
            <tfoot>
                <tr class="total-row">
                    <td colspan="2" class="text-center text-bold">TOTAL</td>
                    <td class="text-center text-bold">{{ $summary['total_tagihan'] }}</td>
                    <td class="text-right text-bold">Rp {{ number_format($summary['total_target_pokok'], 0, ',', '.') }}</td>
                    <td class="text-right text-bold">Rp {{ number_format($summary['total_target_bunga'], 0, ',', '.') }}</td>
                    <td class="text-center text-bold">{{ array_sum(array_column($data, 'tagihan_masuk')) }}</td>
                    <td class="text-right text-bold">Rp {{ number_format($summary['total_realisasi_pokok'], 0, ',', '.') }}</td>
                    <td class="text-right text-bold">Rp {{ number_format($summary['total_realisasi_bunga'], 0, ',', '.') }}</td>
                    <td class="text-center text-bold">{{ $summary['total_tagihan_bermasalah'] }}</td>
                    <td class="text-right text-bold">Rp {{ number_format($summary['total_tidak_bayar_pokok'], 0, ',', '.') }}</td>
                    <td class="text-right text-bold">Rp {{ number_format($summary['total_tidak_bayar_bunga'], 0, ',', '.') }}</td>
                    <td class="text-center text-bold">{{ number_format($summary['rata_rata_koleksi'], 1) }}%</td>
                    <td class="text-center text-bold">BULANAN</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

    <!-- Recent Activities Section -->
    @if(isset($recentActivities) && $recentActivities->count() > 0)
    <div class="recent-section">
        <h3>Aktivitas Pembayaran Terbaru</h3>
        
        @foreach($recentActivities as $activity)
        <div class="recent-item">
            <div class="recent-info">
                <div class="recent-name">{{ $activity['anggota'] }}</div>
                <div class="recent-details">{{ $activity['id'] }} • {{ $activity['tgl_bayar'] }}</div>
            </div>
            <div class="recent-amount">
                <div class="recent-amount-value">Rp {{ number_format($activity['jumlah_bayar'], 0, ',', '.') }}</div>
                <div class="recent-amount-details">Angsuran ke-{{ $activity['angsuran_ke'] }}</div>
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
