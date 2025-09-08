<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Simpanan - Toserda</title>
    <style>
        body {
            font-family: 'Roboto', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #2563EB;
            padding-bottom: 20px;
        }
        
        .header h1 {
            font-size: 24px;
            font-weight: bold;
            color: #1F2937;
            margin: 0 0 10px 0;
        }
        
        .header h2 {
            font-size: 18px;
            font-weight: 600;
            color: #374151;
            margin: 0 0 5px 0;
        }
        
        .header p {
            font-size: 14px;
            color: #6B7280;
            margin: 0;
        }
        
        .period-info {
            background-color: #F3F4F6;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
        }
        
        .period-info h3 {
            font-size: 16px;
            font-weight: 600;
            color: #1F2937;
            margin: 0 0 10px 0;
        }
        
        .period-info p {
            margin: 5px 0;
            color: #374151;
        }
        
        .member-info {
            background-color: #EBF8FF;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            border-left: 4px solid #2563EB;
        }
        
        .member-info h3 {
            font-size: 16px;
            font-weight: 600;
            color: #1F2937;
            margin: 0 0 10px 0;
        }
        
        .member-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        
        .member-details p {
            margin: 5px 0;
            color: #374151;
        }
        
        .statistics {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .stat-card {
            background-color: #F9FAFB;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            border: 1px solid #E5E7EB;
        }
        
        .stat-card h4 {
            font-size: 12px;
            font-weight: 600;
            color: #6B7280;
            margin: 0 0 8px 0;
            text-transform: uppercase;
        }
        
        .stat-card .value {
            font-size: 18px;
            font-weight: bold;
            color: #1F2937;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        
        .data-table th {
            background-color: #2563EB;
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
        }
        
        .data-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #E5E7EB;
            font-size: 11px;
        }
        
        .data-table tr:nth-child(even) {
            background-color: #F9FAFB;
        }
        
        .data-table tr:hover {
            background-color: #F3F4F6;
        }
        
        .amount {
            text-align: right;
            font-weight: 600;
            color: #059669;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-wajib {
            background-color: #DBEAFE;
            color: #1E40AF;
        }
        
        .status-sukarela {
            background-color: #D1FAE5;
            color: #065F46;
        }
        
        .status-toserda {
            background-color: #F3E8FF;
            color: #7C3AED;
        }
        
        .summary {
            background-color: #FEF3C7;
            padding: 15px;
            border-radius: 8px;
            margin-top: 25px;
            border-left: 4px solid #F59E0B;
        }
        
        .summary h3 {
            font-size: 16px;
            font-weight: 600;
            color: #92400E;
            margin: 0 0 10px 0;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px solid #FDE68A;
        }
        
        .summary-item:last-child {
            border-bottom: none;
            font-weight: bold;
            color: #92400E;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #E5E7EB;
            text-align: center;
            color: #6B7280;
            font-size: 10px;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .font-bold {
            font-weight: bold;
        }
        
        .text-sm {
            font-size: 10px;
        }
        
        .text-xs {
            font-size: 9px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>LAPORAN SIMPANAN - TOSERDA</h1>
        <h2>Koperasi Karyawan</h2>
        <p>Periode: {{ \Carbon\Carbon::parse($tgl_dari)->format('d F Y') }} - {{ \Carbon\Carbon::parse($tgl_samp)->format('d F Y') }}</p>
        <p>Dicetak pada: {{ \Carbon\Carbon::now()->format('d F Y H:i:s') }}</p>
    </div>

    <!-- Period Information -->
    <div class="period-info">
        <h3>Informasi Periode Laporan</h3>
        <p><strong>Tanggal Mulai:</strong> {{ \Carbon\Carbon::parse($tgl_dari)->format('d F Y') }}</p>
        <p><strong>Tanggal Akhir:</strong> {{ \Carbon\Carbon::parse($tgl_samp)->format('d F Y') }}</p>
        <p><strong>Total Hari:</strong> {{ \Carbon\Carbon::parse($tgl_dari)->diffInDays(\Carbon\Carbon::parse($tgl_samp)) + 1 }} hari</p>
    </div>

    <!-- Member Information -->
    <div class="member-info">
        <h3>Informasi Anggota</h3>
        <div class="member-details">
            <div>
                <p><strong>Nama:</strong> {{ $member->nama }}</p>
                <p><strong>No. KTP:</strong> {{ $member->no_ktp }}</p>
            </div>
            <div>
                <p><strong>Departemen:</strong> {{ $member->departemen ?: '-' }}</p>
                <p><strong>Status:</strong> {{ $member->status_aktif == 'Y' ? 'Aktif' : 'Tidak Aktif' }}</p>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="statistics">
        <div class="stat-card">
            <h4>Total Transaksi</h4>
            <div class="value">{{ number_format($statistics['total_transaksi']) }}</div>
        </div>
        <div class="stat-card">
            <h4>Total Setoran</h4>
            <div class="value">Rp {{ number_format($statistics['total_setoran'], 0, ',', '.') }}</div>
        </div>
        <div class="stat-card">
            <h4>Rata-rata Setoran</h4>
            <div class="value">Rp {{ number_format($statistics['rata_rata_setoran'], 0, ',', '.') }}</div>
        </div>
        <div class="stat-card">
            <h4>Setoran Terbesar</h4>
            <div class="value">Rp {{ number_format($statistics['setoran_terbesar'], 0, ',', '.') }}</div>
        </div>
    </div>

    <!-- Data Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 15%;">Tanggal</th>
                <th style="width: 20%;">Jenis Simpanan</th>
                <th style="width: 15%;">Jumlah</th>
                <th style="width: 12%;">Status</th>
                <th style="width: 20%;">Keterangan</th>
                <th style="width: 13%;">User Input</th>
            </tr>
        </thead>
        <tbody>
            @forelse($savingsData as $index => $saving)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($saving->tgl_transaksi)->format('d/m/Y') }}</td>
                <td>{{ $saving->jenis_simpanan_text }}</td>
                <td class="amount">Rp {{ number_format($saving->jumlah, 0, ',', '.') }}</td>
                <td>
                    <span class="status-badge 
                        @if($saving->status_simpanan == 'Wajib') status-wajib
                        @elseif($saving->status_simpanan == 'Sukarela') status-sukarela
                        @else status-toserda @endif">
                        {{ $saving->status_simpanan }}
                    </span>
                </td>
                <td>{{ $saving->keterangan ?: 'Setoran simpanan' }}</td>
                <td>{{ $saving->user_name ?: '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center" style="padding: 30px; color: #6B7280;">
                    <strong>Tidak ada data transaksi untuk periode yang dipilih</strong>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Summary -->
    <div class="summary">
        <h3>Ringkasan Laporan</h3>
        <div class="summary-grid">
            <div>
                <div class="summary-item">
                    <span>Total Transaksi:</span>
                    <span>{{ number_format($statistics['total_transaksi']) }} transaksi</span>
                </div>
                <div class="summary-item">
                    <span>Total Setoran:</span>
                    <span>Rp {{ number_format($statistics['total_setoran'], 0, ',', '.') }}</span>
                </div>
            </div>
            <div>
                <div class="summary-item">
                    <span>Rata-rata per Transaksi:</span>
                    <span>Rp {{ number_format($statistics['rata_rata_setoran'], 0, ',', '.') }}</span>
                </div>
                <div class="summary-item">
                    <span>Setoran Terbesar:</span>
                    <span>Rp {{ number_format($statistics['setoran_terbesar'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Breakdown by Type -->
    @if($statistics['breakdown']->count() > 0)
    <div class="page-break">
        <h3 style="color: #1F2937; margin-bottom: 20px;">Breakdown per Jenis Simpanan</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Jenis Simpanan</th>
                    <th>Jumlah Transaksi</th>
                    <th>Total Jumlah</th>
                    <th>Persentase</th>
                </tr>
            </thead>
            <tbody>
                @foreach($statistics['breakdown'] as $breakdown)
                <tr>
                    <td>{{ $breakdown->jns_simpan ?: 'Toserda' }}</td>
                    <td class="text-center">{{ number_format($breakdown->jumlah_transaksi) }}</td>
                    <td class="amount">Rp {{ number_format($breakdown->total_jumlah, 0, ',', '.') }}</td>
                    <td class="text-center">
                        {{ $statistics['total_setoran'] > 0 ? number_format(($breakdown->total_jumlah / $statistics['total_setoran']) * 100, 1) : 0 }}%
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>Laporan ini dibuat secara otomatis oleh sistem Koperasi Karyawan</p>
        <p>Untuk pertanyaan atau klarifikasi, silakan hubungi bagian administrasi</p>
        <p>© {{ date('Y') }} Koperasi Karyawan - Semua hak dilindungi</p>
    </div>
</body>
</html>
