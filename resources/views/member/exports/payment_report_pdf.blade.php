<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pembayaran Pinjaman - {{ $member->nama }}</title>
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
        .status-tepat {
            background-color: #c6f6d5;
            color: #22543d;
        }
        .status-terlambat {
            background-color: #fed7d7;
            color: #742a2a;
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
        <h1>Laporan Pembayaran Pinjaman</h1>
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
        
        @if($paymentData->count() > 0)
        <table class="table">
                <thead>
                    <tr>
                    <th>Tanggal</th>
                    <th>Jenis</th>
                    <th>Angsuran Ke</th>
                    <th>Pokok</th>
                    <th>Jasa</th>
                    <th>Denda</th>
                    <th>Jumlah Bayar</th>
                    <th>Status</th>
                    <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($paymentData as $payment)
                    <tr>
                    <td>{{ \Carbon\Carbon::parse($payment->tgl_bayar)->format('d M Y') }}</td>
                    <td>{{ $payment->jenis_pinjaman_text }}</td>
                    <td>{{ $payment->angsuran_ke }}</td>
                    <td>Rp {{ number_format($payment->jumlah_bayar, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($payment->bunga, 0, ',', '.') }}</td>
                    <td>
                            @if($payment->denda_rp > 0)
                                Rp {{ number_format($payment->denda_rp, 0, ',', '.') }}
                            @else
                                -
                            @endif
                        </td>
                    <td>Rp {{ number_format($payment->total_bayar, 0, ',', '.') }}</td>
                    <td>
                        <span class="status-badge {{ $payment->status_pembayaran == 'Tepat Waktu' ? 'status-tepat' : 'status-terlambat' }}">
                            {{ $payment->status_pembayaran }}
                        </span>
                        </td>
                        <td>{{ $payment->ket_bayar ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

        <div class="summary">
            <h3>Ringkasan Pembayaran</h3>
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="summary-value">{{ $statistics['total_pembayaran'] }}</div>
                    <div class="summary-label">Total Pembayaran</div>
                </div>
                <div class="summary-item">
                    <div class="summary-value">Rp {{ number_format($statistics['total_pokok_dibayar'], 0, ',', '.') }}</div>
                    <div class="summary-label">Total Pokok Dibayar</div>
                </div>
                <div class="summary-item">
                    <div class="summary-value">Rp {{ number_format($statistics['total_bunga_dibayar'], 0, ',', '.') }}</div>
                    <div class="summary-label">Total Bunga Dibayar</div>
                </div>
                <div class="summary-item">
                    <div class="summary-value">Rp {{ number_format($statistics['total_denda_dibayar'], 0, ',', '.') }}</div>
                    <div class="summary-label">Total Denda</div>
                </div>
            </div>
        </div>
        @else
        <div style="text-align: center; padding: 40px; color: #666;">
            <h3>Tidak ada data pembayaran</h3>
            <p>Belum ada riwayat pembayaran pinjaman untuk periode yang dipilih.</p>
            </div>
        @endif

    <div class="footer">
        <p>Dicetak pada {{ date('d M Y H:i:s') }} | Sistem Informasi Koperasi</p>
    </div>
</body>
</html>
