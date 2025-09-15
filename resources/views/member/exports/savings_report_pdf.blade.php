<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Simpanan - {{ $member->nama }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #2d3748;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .info-section {
            margin-bottom: 20px;
        }
        .info-section h3 {
            margin: 0 0 10px 0;
            font-size: 16px;
            color: #2d3748;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .info-item {
            margin-bottom: 8px;
        }
        .info-label {
            font-weight: bold;
            color: #4a5568;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .table th {
            background-color: #f7fafc;
            font-weight: bold;
            color: #2d3748;
        }
        .table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .status-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        .status-wajib {
            background-color: #bee3f8;
            color: #1a365d;
        }
        .status-sukarela {
            background-color: #c6f6d5;
            color: #22543d;
        }
        .status-khusus {
            background-color: #e9d8fd;
            color: #553c9a;
        }
        .summary {
            margin-top: 30px;
            padding: 15px;
            background-color: #f7fafc;
            border-radius: 5px;
        }
        .summary h3 {
            margin: 0 0 15px 0;
            color: #2d3748;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
        }
        .summary-item {
            text-align: center;
        }
        .summary-value {
            font-size: 18px;
            font-weight: bold;
            color: #2d3748;
        }
        .summary-label {
            font-size: 11px;
            color: #666;
            margin-top: 5px;
        }
        .breakdown {
            margin-top: 20px;
        }
        .breakdown h3 {
            margin: 0 0 15px 0;
            color: #2d3748;
        }
        .breakdown-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }
        .breakdown-item {
            padding: 10px;
            background-color: #f9f9f9;
            border-radius: 5px;
            text-align: center;
        }
        .breakdown-name {
            font-weight: bold;
            color: #2d3748;
            margin-bottom: 5px;
        }
        .breakdown-count {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }
        .breakdown-amount {
            font-size: 14px;
            font-weight: bold;
            color: #2d3748;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Simpanan - Toserda</h1>
        <p>{{ $member->nama }} ({{ $member->no_ktp }})</p>
        <p>Periode: {{ \Carbon\Carbon::parse($tgl_dari)->format('d M Y') }} - {{ \Carbon\Carbon::parse($tgl_samp)->format('d M Y') }}</p>
        <p>Tanggal Cetak: {{ date('d M Y H:i') }}</p>
    </div>

    <div class="info-section">
        <h3>Informasi Anggota</h3>
        <div class="info-grid">
            <div>
                <div class="info-item">
                    <span class="info-label">Nama:</span> {{ $member->nama }}
                </div>
                <div class="info-item">
                    <span class="info-label">No. KTP:</span> {{ $member->no_ktp }}
                </div>
                <div class="info-item">
                    <span class="info-label">Alamat:</span> {{ $member->alamat }}
                </div>
            </div>
            <div>
                <div class="info-item">
                    <span class="info-label">Departemen:</span> {{ $member->departemen }}
                </div>
                <div class="info-item">
                    <span class="info-label">Jabatan:</span> {{ $member->jabatan }}
                </div>
                <div class="info-item">
                    <span class="info-label">Status:</span> {{ $member->aktif == 'Y' ? 'Aktif' : 'Tidak Aktif' }}
                </div>
            </div>
        </div>
    </div>

    @if($savingsData->count() > 0)
        <table class="table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Jenis Simpanan</th>
                    <th>Jumlah</th>
                    <th>Status</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($savingsData as $saving)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($saving->tgl_transaksi)->format('d M Y') }}</td>
                    <td>{{ $saving->jenis_simpanan_text }}</td>
                    <td>Rp {{ number_format($saving->jumlah, 0, ',', '.') }}</td>
                    <td>
                        <span class="status-badge 
                            @if($saving->jenis_id == 41) status-wajib
                            @elseif($saving->jenis_id == 32) status-sukarela
                            @else status-khusus @endif">
                            {{ $saving->jenis_simpanan_text }}
                        </span>
                    </td>
                    <td>{{ $saving->keterangan ?: 'Setoran simpanan' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary">
            <h3>Ringkasan Simpanan</h3>
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="summary-value">{{ number_format($statistics['total_transaksi']) }}</div>
                    <div class="summary-label">Total Transaksi</div>
                </div>
                <div class="summary-item">
                    <div class="summary-value">Rp {{ number_format($statistics['total_setoran'], 0, ',', '.') }}</div>
                    <div class="summary-label">Total Setoran</div>
                </div>
                <div class="summary-item">
                    <div class="summary-value">Rp {{ number_format($statistics['rata_rata_setoran'], 0, ',', '.') }}</div>
                    <div class="summary-label">Rata-rata Setoran</div>
                </div>
                <div class="summary-item">
                    <div class="summary-value">Rp {{ number_format($statistics['setoran_terbesar'], 0, ',', '.') }}</div>
                    <div class="summary-label">Setoran Terbesar</div>
                </div>
            </div>
        </div>

        @if($statistics['breakdown']->count() > 0)
        <div class="breakdown">
            <h3>Breakdown per Jenis Simpanan</h3>
            <div class="breakdown-grid">
                @foreach($statistics['breakdown'] as $breakdown)
                <div class="breakdown-item">
                    <div class="breakdown-name">{{ $breakdown->jns_simpan ?: 'Toserda' }}</div>
                    <div class="breakdown-count">{{ $breakdown->jumlah_transaksi }} transaksi</div>
                    <div class="breakdown-amount">Rp {{ number_format($breakdown->total_jumlah, 0, ',', '.') }}</div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    @else
        <div style="text-align: center; padding: 40px; color: #666;">
            <h3>Tidak ada data simpanan</h3>
            <p>Belum ada riwayat setoran simpanan untuk periode yang dipilih.</p>
        </div>
    @endif

    <div class="footer">
        <p>Dicetak pada {{ date('d M Y H:i:s') }} | Sistem Informasi Koperasi</p>
    </div>
</body>
</html>