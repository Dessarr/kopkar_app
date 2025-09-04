<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Target & Realisasi Pinjaman Anggota</title>
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
            border-bottom: 3px solid #14AE5C;
            padding-bottom: 20px;
        }
        
        .header h1 {
            color: #14AE5C;
            font-size: 24px;
            margin: 0 0 10px 0;
            font-weight: bold;
        }
        
        .header h2 {
            color: #666;
            font-size: 16px;
            margin: 0;
            font-weight: normal;
        }
        
        .info-section {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #14AE5C;
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
            color: #555;
            min-width: 120px;
        }
        
        .info-value {
            color: #333;
        }
        
        .summary-cards {
            display: flex;
            justify-content: space-between;
            margin-bottom: 25px;
            gap: 15px;
        }
        
        .summary-card {
            flex: 1;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
        }
        
        .summary-card h3 {
            margin: 0 0 8px 0;
            font-size: 14px;
            color: #666;
            font-weight: bold;
        }
        
        .summary-card .value {
            font-size: 18px;
            font-weight: bold;
            color: #14AE5C;
        }
        
        .summary-card .subtitle {
            font-size: 10px;
            color: #888;
            margin-top: 4px;
        }
        
        .status-cards {
            display: flex;
            justify-content: space-between;
            margin-bottom: 25px;
            gap: 10px;
        }
        
        .status-card {
            flex: 1;
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 12px;
            text-align: center;
        }
        
        .status-card h4 {
            margin: 0 0 6px 0;
            font-size: 12px;
            color: #666;
            font-weight: bold;
        }
        
        .status-card .count {
            font-size: 16px;
            font-weight: bold;
            color: #14AE5C;
        }
        
        .status-card .percentage {
            font-size: 10px;
            color: #888;
            margin-top: 2px;
        }
        
        .table-container {
            margin-bottom: 20px;
        }
        
        .table-title {
            background-color: #14AE5C;
            color: white;
            padding: 12px;
            font-weight: bold;
            font-size: 14px;
            text-align: center;
            border-radius: 6px 6px 0 0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10px;
        }
        
        th {
            background-color: #f8f9fa;
            color: #555;
            font-weight: bold;
            padding: 8px 4px;
            text-align: center;
            border: 1px solid #dee2e6;
            font-size: 9px;
        }
        
        td {
            padding: 6px 4px;
            border: 1px solid #dee2e6;
            text-align: right;
            font-size: 9px;
        }
        
        td.text-left {
            text-align: left;
        }
        
        td.text-center {
            text-align: center;
        }
        
        .section-header {
            background-color: #e9ecef;
            font-weight: bold;
            color: #495057;
        }
        
        .total-row {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #14AE5C;
        }
        
        .status-lunas {
            background-color: #d4edda;
            color: #155724;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
        }
        
        .status-berjalan {
            background-color: #fff3cd;
            color: #856404;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
        }
        
        .status-jatuh-tempo {
            background-color: #f8d7da;
            color: #721c24;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
        }
        
        .status-belum-mulai {
            background-color: #d1ecf1;
            color: #0c5460;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            color: #666;
            font-size: 10px;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>LAPORAN TARGET & REALISASI</h1>
        <h2>PINJAMAN ANGGOTA</h2>
    </div>

    <!-- Info Section -->
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Periode Laporan:</span>
            <span class="info-value">{{ \Carbon\Carbon::parse($tgl_dari)->format('d F Y') }} - {{ \Carbon\Carbon::parse($tgl_samp)->format('d F Y') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Tanggal Cetak:</span>
            <span class="info-value">{{ \Carbon\Carbon::now()->format('d F Y H:i:s') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Total Pinjaman:</span>
            <span class="info-value">{{ number_format($summary['total_pinjaman']) }} pinjaman</span>
        </div>
        <div class="info-row">
            <span class="info-label">Nilai Total Pinjaman:</span>
            <span class="info-value">Rp {{ number_format($summary['total_nilai_pinjaman']) }}</span>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="summary-card">
            <h3>TARGET ANGSURAN</h3>
            <div class="value">Rp {{ number_format($summary['total_target_angsuran']) }}</div>
            <div class="subtitle">Total yang direncanakan</div>
        </div>
        <div class="summary-card">
            <h3>REALISASI</h3>
            <div class="value">Rp {{ number_format($summary['total_realisasi']) }}</div>
            <div class="subtitle">Total yang dibayar</div>
        </div>
        <div class="summary-card">
            <h3>SISA TAGIHAN</h3>
            <div class="value">Rp {{ number_format($summary['total_sisa_tagihan']) }}</div>
            <div class="subtitle">Belum dibayar</div>
        </div>
        <div class="summary-card">
            <h3>% REALISASI</h3>
            <div class="value">{{ number_format($summary['persentase_realisasi_keseluruhan'], 1) }}%</div>
            <div class="subtitle">Tingkat pencapaian</div>
        </div>
    </div>

    <!-- Status Overview -->
    <div class="status-cards">
        <div class="status-card">
            <h4>PINJAMAN LUNAS</h4>
            <div class="count">{{ number_format($summary['pinjaman_lunas']) }}</div>
            <div class="percentage">{{ number_format($summary['persentase_pelunasan'], 1) }}%</div>
        </div>
        <div class="status-card">
            <h4>BERJALAN</h4>
            <div class="count">{{ number_format($summary['pinjaman_berjalan']) }}</div>
            <div class="percentage">{{ number_format($summary['completion_rate'], 1) }}%</div>
        </div>
        <div class="status-card">
            <h4>JATUH TEMPO</h4>
            <div class="count">{{ number_format($summary['pinjaman_jatuh_tempo']) }}</div>
            <div class="percentage">{{ number_format($summary['overdue_rate'], 1) }}%</div>
        </div>
        <div class="status-card">
            <h4>BELUM MULAI</h4>
            <div class="count">{{ number_format($summary['pinjaman_belum_mulai']) }}</div>
            <div class="percentage">-</div>
        </div>
    </div>

    <!-- Main Report Table -->
    @if(count($data) > 0)
    <div class="table-container">
        <div class="table-title">TABEL DATA PINJAMAN</div>
        <table>
            <thead>
                <tr>
                    <th rowspan="2" style="width: 3%;">No</th>
                    <th rowspan="2" style="width: 8%;">Tanggal Pinjam</th>
                    <th rowspan="2" style="width: 12%;">Nama</th>
                    <th rowspan="2" style="width: 6%;">ID</th>
                    <th rowspan="2" style="width: 8%;">Jabatan</th>
                    <th rowspan="2" style="width: 8%;">Pinjaman</th>
                    <th rowspan="2" style="width: 8%;">Saldo Pinjaman</th>
                    <th rowspan="2" style="width: 4%;">JW</th>
                    <th rowspan="2" style="width: 4%;">%</th>
                    <th colspan="3" style="background-color: #e3f2fd;">TARGET (RENCANA)</th>
                    <th colspan="5" style="background-color: #f3e5f5;">REALISASI (AKTUAL)</th>
                    <th colspan="3" style="background-color: #e8f5e8;">PERFORMANCE</th>
                </tr>
                <tr>
                    <th class="section-header" style="width: 6%;">Pokok</th>
                    <th class="section-header" style="width: 6%;">Bunga</th>
                    <th class="section-header" style="width: 6%;">Total</th>
                    <th class="section-header" style="width: 5%;">Angsuran Ke</th>
                    <th class="section-header" style="width: 6%;">Pokok Bayar</th>
                    <th class="section-header" style="width: 6%;">Bunga Bayar</th>
                    <th class="section-header" style="width: 5%;">Denda</th>
                    <th class="section-header" style="width: 6%;">Total Bayar</th>
                    <th class="section-header" style="width: 6%;">Sisa Tagihan</th>
                    <th class="section-header" style="width: 5%;">Status</th>
                    <th class="section-header" style="width: 5%;">% Realisasi</th>
                    <th class="section-header" style="width: 6%;">Gap</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($item['tgl_pinjam'])->format('d/m/Y') }}</td>
                    <td class="text-left">{{ $item['anggota'] }}</td>
                    <td class="text-center">{{ $item['id_anggota'] }}</td>
                    <td class="text-center">{{ $item['jabatan'] }}</td>
                    <td>Rp {{ number_format($item['jumlah']) }}</td>
                    <td>Rp {{ number_format($item['sisa_tagihan']) }}</td>
                    <td class="text-center">{{ $item['lama_angsuran'] }}</td>
                    <td class="text-center">{{ $item['bunga'] }}%</td>
                    <td>Rp {{ number_format($item['pokok_angsuran']) }}</td>
                    <td>Rp {{ number_format($item['pokok_bunga']) }}</td>
                    <td>Rp {{ number_format($item['target_angsuran']) }}</td>
                    <td class="text-center">{{ $item['angsuran_ke'] }}</td>
                    <td>Rp {{ number_format($item['pokok_bayar']) }}</td>
                    <td>Rp {{ number_format($item['bunga_bayar']) }}</td>
                    <td>Rp {{ number_format($item['denda_rp']) }}</td>
                    <td>Rp {{ number_format($item['realisasi_pembayaran']) }}</td>
                    <td>Rp {{ number_format($item['sisa_tagihan']) }}</td>
                    <td class="text-center">
                        <span class="status-{{ strtolower(str_replace(' ', '-', $item['status'])) }}">
                            {{ $item['status'] }}
                        </span>
                    </td>
                    <td class="text-center">{{ number_format($item['persentase_realisasi'], 1) }}%</td>
                    <td>Rp {{ number_format($item['gap_target_realisasi']) }}</td>
                </tr>
                @endforeach
                
                <!-- Total Row -->
                <tr class="total-row">
                    <td colspan="6" class="text-center"><strong>TOTAL</strong></td>
                    <td><strong>Rp {{ number_format($summary['total_nilai_pinjaman']) }}</strong></td>
                    <td colspan="2"></td>
                    <td><strong>Rp {{ number_format($summary['total_target_angsuran']) }}</strong></td>
                    <td colspan="2"></td>
                    <td><strong>Rp {{ number_format($summary['total_realisasi']) }}</strong></td>
                    <td colspan="2"></td>
                    <td><strong>Rp {{ number_format($summary['total_sisa_tagihan']) }}</strong></td>
                    <td colspan="2"></td>
                    <td><strong>{{ number_format($summary['persentase_realisasi_keseluruhan'], 1) }}%</strong></td>
                    <td><strong>Rp {{ number_format($summary['total_gap']) }}</strong></td>
                </tr>
            </tbody>
        </table>
    </div>
    @else
    <div class="no-data">
        <h3>Tidak ada data pinjaman untuk periode yang dipilih</h3>
        <p>Periode: {{ \Carbon\Carbon::parse($tgl_dari)->format('d F Y') }} - {{ \Carbon\Carbon::parse($tgl_samp)->format('d F Y') }}</p>
    </div>
    @endif

    <!-- Recent Loans Section -->
    @if(count($recentLoans) > 0)
    <div class="page-break"></div>
    <div class="table-container">
        <div class="table-title">PINJAMAN TERBARU</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 15%;">ID Pinjaman</th>
                    <th style="width: 25%;">Nama Anggota</th>
                    <th style="width: 15%;">Jumlah</th>
                    <th style="width: 12%;">Tanggal</th>
                    <th style="width: 10%;">Status</th>
                    <th style="width: 15%;">Sisa Tagihan</th>
                    <th style="width: 8%;">Progress</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentLoans as $loan)
                <tr>
                    <td class="text-center">{{ $loan['id'] }}</td>
                    <td class="text-left">{{ $loan['anggota'] }}</td>
                    <td>Rp {{ number_format($loan['jumlah']) }}</td>
                    <td class="text-center">{{ $loan['tgl_pinjam'] }}</td>
                    <td class="text-center">
                        <span class="status-{{ strtolower(str_replace(' ', '-', $loan['status'])) }}">
                            {{ $loan['status'] }}
                        </span>
                    </td>
                    <td>Rp {{ number_format($loan['sisa_tagihan']) }}</td>
                    <td class="text-center">{{ number_format($loan['persentase'], 1) }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p><strong>KOPERASI KARYAWAN</strong></p>
        <p>Laporan ini dibuat secara otomatis pada {{ \Carbon\Carbon::now()->format('d F Y H:i:s') }}</p>
        <p>Halaman 1 dari 1</p>
    </div>
</body>
</html>
