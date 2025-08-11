<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Angsuran - {{ $angsuran->pinjaman->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }
        .header p {
            margin: 5px 0;
        }
        .content {
            margin-bottom: 20px;
        }
        .section {
            margin-bottom: 15px;
        }
        .section h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            font-weight: bold;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .info-item {
            margin-bottom: 8px;
        }
        .info-label {
            font-weight: bold;
            color: #333;
        }
        .info-value {
            color: #000;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .table th, .table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        .table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .total-section {
            margin-top: 20px;
            text-align: right;
        }
        .total-item {
            margin-bottom: 5px;
        }
        .total-label {
            font-weight: bold;
            margin-right: 10px;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
        }
        .signature {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }
        .signature-item {
            text-align: center;
            width: 200px;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 50px;
            padding-top: 5px;
        }
        @media print {
            body {
                margin: 0;
                padding: 15px;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>KOPERASI INDONESIA</h1>
        <p>Jl. Contoh No. 123, Jakarta</p>
        <p>Telp: (021) 123-4567 | Email: info@kopkar.co.id</p>
        <h2>BUKTI PEMBAYARAN ANGSURAN</h2>
    </div>

    <div class="content">
        <div class="section">
            <h3>Informasi Pinjaman</h3>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Kode Pinjam:</span>
                    <span class="info-value">{{ $angsuran->pinjaman->id }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Nama Anggota:</span>
                    <span class="info-value">{{ $angsuran->pinjaman->anggota->nama ?? '-' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">ID Anggota:</span>
                    <span class="info-value">{{ $angsuran->pinjaman->anggota_id }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">No KTP:</span>
                    <span class="info-value">{{ $angsuran->pinjaman->no_ktp ?? '-' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Tanggal Pinjam:</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($angsuran->pinjaman->tgl_pinjam)->format('d/m/Y') }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Lama Angsuran:</span>
                    <span class="info-value">{{ $angsuran->pinjaman->lama_angsuran }} Bulan</span>
                </div>
            </div>
        </div>

        <div class="section">
            <h3>Detail Pembayaran Angsuran</h3>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Angsuran Ke:</span>
                    <span class="info-value">{{ $angsuran->angsuran_ke }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Tanggal Bayar:</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($angsuran->tgl_bayar)->format('d/m/Y') }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Jumlah Bayar:</span>
                    <span class="info-value">Rp {{ number_format($angsuran->jumlah_bayar, 0, ',', '.') }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Bunga:</span>
                    <span class="info-value">Rp {{ number_format($angsuran->bunga, 0, ',', '.') }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Biaya Admin:</span>
                    <span class="info-value">Rp {{ number_format($angsuran->biaya_adm, 0, ',', '.') }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Denda:</span>
                    <span class="info-value">Rp {{ number_format($angsuran->denda_rp, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <div class="section">
            <h3>Rincian Pinjaman</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Keterangan</th>
                        <th>Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Jumlah Pinjaman</td>
                        <td>Rp {{ number_format($angsuran->pinjaman->jumlah, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>Bunga ({{ $angsuran->pinjaman->bunga }}%)</td>
                        <td>Rp {{ number_format($angsuran->pinjaman->bunga_rp, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>Biaya Admin</td>
                        <td>Rp {{ number_format($angsuran->pinjaman->biaya_adm, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>Total Tagihan</td>
                        <td>Rp {{ number_format($angsuran->pinjaman->jumlah + $angsuran->pinjaman->biaya_adm, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="total-section">
            <div class="total-item">
                <span class="total-label">Total Pembayaran:</span>
                <span>Rp {{ number_format($angsuran->jumlah_bayar + $angsuran->bunga + $angsuran->biaya_adm + $angsuran->denda_rp, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}</p>
        <p>User: {{ $angsuran->user_name }}</p>
    </div>

    <div class="signature">
        <div class="signature-item">
            <div class="signature-line">Pembayar</div>
        </div>
        <div class="signature-item">
            <div class="signature-line">Admin</div>
        </div>
    </div>

    <div class="no-print" style="margin-top: 30px; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">
            Cetak
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; font-size: 16px; background-color: #6c757d; color: white; border: none; border-radius: 5px; cursor: pointer; margin-left: 10px;">
            Tutup
        </button>
    </div>
</body>
</html>
