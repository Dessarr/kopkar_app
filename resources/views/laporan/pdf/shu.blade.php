<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan SHU - {{ $tgl_dari }} s/d {{ $tgl_samp }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
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
            font-size: 18px;
            font-weight: bold;
            margin: 0 0 10px 0;
            color: #2d3748;
        }
        
        .header h2 {
            font-size: 14px;
            font-weight: normal;
            margin: 0 0 5px 0;
            color: #4a5568;
        }
        
        .header p {
            font-size: 10px;
            margin: 0;
            color: #718096;
        }
        
        .summary-section {
            margin-bottom: 20px;
        }
        
        .summary-grid {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        
        .summary-item {
            display: table-cell;
            width: 25%;
            padding: 10px;
            text-align: center;
            border: 1px solid #e2e8f0;
            background-color: #f7fafc;
        }
        
        .summary-item.income {
            background-color: #f0fff4;
            border-color: #9ae6b4;
        }
        
        .summary-item.expense {
            background-color: #fff5f5;
            border-color: #feb2b2;
        }
        
        .summary-item.shu-before {
            background-color: #ebf8ff;
            border-color: #90cdf4;
        }
        
        .summary-item.shu-after {
            background-color: #faf5ff;
            border-color: #d6bcfa;
        }
        
        .summary-label {
            font-size: 8px;
            color: #4a5568;
            margin-bottom: 5px;
        }
        
        .summary-value {
            font-size: 12px;
            font-weight: bold;
            color: #2d3748;
        }
        
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #2d3748;
            margin: 20px 0 10px 0;
            padding: 8px 12px;
            background-color: #edf2f7;
            border-left: 4px solid #4299e1;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .table th {
            background-color: #f7fafc;
            border: 1px solid #e2e8f0;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            font-size: 9px;
            color: #4a5568;
        }
        
        .table td {
            border: 1px solid #e2e8f0;
            padding: 6px 8px;
            font-size: 9px;
            color: #2d3748;
        }
        
        .table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .font-bold {
            font-weight: bold;
        }
        
        .income-header {
            background-color: #f0fff4 !important;
            color: #22543d !important;
        }
        
        .expense-header {
            background-color: #fff5f5 !important;
            color: #742a2a !important;
        }
        
        .calculation-section {
            background-color: #f7fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
        }
        
        .calculation-grid {
            display: table;
            width: 100%;
        }
        
        .calculation-item {
            display: table-cell;
            width: 25%;
            padding: 8px;
            text-align: center;
            border-right: 1px solid #e2e8f0;
        }
        
        .calculation-item:last-child {
            border-right: none;
        }
        
        .calculation-label {
            font-size: 8px;
            color: #4a5568;
            margin-bottom: 3px;
        }
        
        .calculation-value {
            font-size: 10px;
            font-weight: bold;
            color: #2d3748;
        }
        
        .shu-final {
            background-color: #faf5ff;
            border: 2px solid #d6bcfa;
            border-radius: 4px;
            padding: 15px;
            text-align: center;
            margin: 15px 0;
        }
        
        .shu-final-label {
            font-size: 10px;
            color: #553c9a;
            margin-bottom: 5px;
        }
        
        .shu-final-value {
            font-size: 16px;
            font-weight: bold;
            color: #553c9a;
        }
        
        .distribution-section {
            margin: 20px 0;
        }
        
        .distribution-grid {
            display: table;
            width: 100%;
        }
        
        .distribution-item {
            display: table-cell;
            width: 33.33%;
            padding: 10px;
            border: 1px solid #e2e8f0;
            background-color: #f7fafc;
            vertical-align: top;
        }
        
        .distribution-label {
            font-size: 9px;
            font-weight: bold;
            color: #2d3748;
            margin-bottom: 5px;
        }
        
        .distribution-percentage {
            font-size: 8px;
            color: #4a5568;
            margin-bottom: 5px;
        }
        
        .distribution-amount {
            font-size: 11px;
            font-weight: bold;
            color: #2d3748;
            margin-bottom: 8px;
        }
        
        .sub-items {
            font-size: 8px;
            color: #4a5568;
        }
        
        .sub-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 8px;
            color: #718096;
        }
        
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>LAPORAN SISA HASIL USAHA (SHU)</h1>
        <h2>KOPERASI SIMPAN PINJAM</h2>
        <p>Periode: {{ \Carbon\Carbon::parse($tgl_dari)->format('d F Y') }} s/d {{ \Carbon\Carbon::parse($tgl_samp)->format('d F Y') }}</p>
        <p>Dicetak pada: {{ \Carbon\Carbon::now()->format('d F Y H:i:s') }}</p>
    </div>

    <!-- Summary Section -->
    @if(isset($summary))
    <div class="summary-section">
        <div class="summary-grid">
            <div class="summary-item income">
                <div class="summary-label">Total Pendapatan</div>
                <div class="summary-value">Rp {{ number_format($summary['total_pendapatan'], 0, ',', '.') }}</div>
            </div>
            <div class="summary-item expense">
                <div class="summary-label">Total Biaya</div>
                <div class="summary-value">Rp {{ number_format($summary['total_biaya'], 0, ',', '.') }}</div>
            </div>
            <div class="summary-item shu-before">
                <div class="summary-label">SHU Sebelum Pajak</div>
                <div class="summary-value">Rp {{ number_format($summary['shu_sebelum_pajak'], 0, ',', '.') }}</div>
            </div>
            <div class="summary-item shu-after">
                <div class="summary-label">SHU Setelah Pajak</div>
                <div class="summary-value">Rp {{ number_format($summary['shu_setelah_pajak'], 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    @endif

    <!-- Income Section -->
    <div class="section-title">PENDAPATAN</div>
    <table class="table">
        <thead>
            <tr class="income-header">
                <th style="width: 15%;">Kode Akun</th>
                <th style="width: 60%;">Nama Akun</th>
                <th style="width: 25%;" class="text-right">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @if(isset($data['pendapatan_rows']) && count($data['pendapatan_rows']) > 0)
                @foreach($data['pendapatan_rows'] as $row)
                    <tr>
                        <td>{{ $row['kode_akun'] ?? '-' }}</td>
                        <td>{{ $row['nama_akun'] ?? $row['nama'] }}</td>
                        <td class="text-right font-bold">Rp {{ number_format($row['jumlah'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="3" class="text-center">Tidak ada data pendapatan</td>
                </tr>
            @endif
        </tbody>
    </table>

    <!-- Expense Section -->
    <div class="section-title">BIAYA</div>
    <table class="table">
        <thead>
            <tr class="expense-header">
                <th style="width: 15%;">Kode Akun</th>
                <th style="width: 60%;">Nama Akun</th>
                <th style="width: 25%;" class="text-right">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @if(isset($data['biaya_rows']) && count($data['biaya_rows']) > 0)
                @foreach($data['biaya_rows'] as $row)
                    <tr>
                        <td>{{ $row['kode_akun'] ?? '-' }}</td>
                        <td>{{ $row['nama_akun'] ?? $row['nama'] }}</td>
                        <td class="text-right font-bold">Rp {{ number_format($row['jumlah'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="3" class="text-center">Tidak ada data biaya</td>
                </tr>
            @endif
        </tbody>
    </table>

    <!-- SHU Calculation Section -->
    <div class="calculation-section">
        <div class="section-title" style="margin-top: 0;">PERHITUNGAN SHU</div>
        <div class="calculation-grid">
            <div class="calculation-item">
                <div class="calculation-label">Total Pendapatan</div>
                <div class="calculation-value">Rp {{ number_format($data['total_pendapatan'], 0, ',', '.') }}</div>
            </div>
            <div class="calculation-item">
                <div class="calculation-label">Total Biaya</div>
                <div class="calculation-value">Rp {{ number_format($data['total_biaya'], 0, ',', '.') }}</div>
            </div>
            <div class="calculation-item">
                <div class="calculation-label">SHU Sebelum Pajak</div>
                <div class="calculation-value">Rp {{ number_format($data['shu_sebelum_pajak'] ?? $data['shu'], 0, ',', '.') }}</div>
            </div>
            <div class="calculation-item">
                <div class="calculation-label">Pajak PPH</div>
                <div class="calculation-value">Rp {{ number_format($data['pajak_pph'] ?? 0, 0, ',', '.') }}</div>
            </div>
        </div>
        
        <div class="shu-final">
            <div class="shu-final-label">SHU SETELAH PAJAK</div>
            <div class="shu-final-value">Rp {{ number_format($data['shu_setelah_pajak'] ?? $data['shu'], 0, ',', '.') }}</div>
        </div>
    </div>

    <!-- SHU Distribution Section -->
    @if(isset($distribution))
    <div class="distribution-section">
        <div class="section-title">DISTRIBUSI SHU</div>
        <div class="distribution-grid">
            @foreach($distribution as $item)
                <div class="distribution-item">
                    <div class="distribution-label">{{ $item['label'] }}</div>
                    <div class="distribution-percentage">{{ $item['percentage'] }}%</div>
                    <div class="distribution-amount">Rp {{ number_format($item['amount'], 0, ',', '.') }}</div>
                    @if(isset($item['sub_items']))
                        <div class="sub-items">
                            @foreach($item['sub_items'] as $subItem)
                                <div class="sub-item">
                                    <span>{{ $subItem['label'] }}</span>
                                    <span>Rp {{ number_format($subItem['amount'], 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Performance Metrics -->
    @if(isset($performance))
    <div class="section-title">METRIK KINERJA</div>
    <table class="table">
        <thead>
            <tr>
                <th style="width: 40%;">Indikator</th>
                <th style="width: 30%;" class="text-center">Nilai</th>
                <th style="width: 30%;" class="text-center">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Profit Margin</td>
                <td class="text-center font-bold">{{ number_format($summary['profit_margin'], 1) }}%</td>
                <td class="text-center">Efisiensi pendapatan</td>
            </tr>
            <tr>
                <td>Expense Ratio</td>
                <td class="text-center font-bold">{{ number_format($summary['expense_ratio'], 1) }}%</td>
                <td class="text-center">Rasio biaya operasional</td>
            </tr>
            <tr>
                <td>Tax Burden</td>
                <td class="text-center font-bold">{{ number_format($summary['tax_burden'], 1) }}%</td>
                <td class="text-center">Beban pajak</td>
            </tr>
            <tr>
                <td>SHU Efficiency</td>
                <td class="text-center font-bold">{{ number_format($performance['shu_efficiency'], 1) }}%</td>
                <td class="text-center">Efisiensi distribusi SHU</td>
            </tr>
        </tbody>
    </table>
    @endif

    <!-- Recent Activities -->
    @if(isset($recent_activities) && count($recent_activities) > 0)
    <div class="section-title">AKTIVITAS TERBARU</div>
    <table class="table">
        <thead>
            <tr>
                <th style="width: 50%;">Deskripsi</th>
                <th style="width: 20%;" class="text-center">Tanggal</th>
                <th style="width: 20%;" class="text-right">Jumlah</th>
                <th style="width: 10%;" class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($recent_activities as $activity)
                <tr>
                    <td>{{ $activity['description'] }}</td>
                    <td class="text-center">{{ $activity['date'] }}</td>
                    <td class="text-right font-bold">Rp {{ number_format($activity['amount'], 0, ',', '.') }}</td>
                    <td class="text-center">{{ $activity['status'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>Laporan ini dibuat secara otomatis oleh sistem Koperasi Simpan Pinjam</p>
        <p>Untuk pertanyaan lebih lanjut, silakan hubungi administrator</p>
    </div>
</body>
</html>
