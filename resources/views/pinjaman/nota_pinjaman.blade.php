<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Pinjaman - {{ $dataNota['pinjaman']->id }}</title>
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
            margin-bottom: 30px;
        }
        .koperasi-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .koperasi-address {
            font-size: 12px;
            margin-bottom: 5px;
        }
        .document-title {
            font-size: 16px;
            font-weight: bold;
            margin: 20px 0;
            text-align: center;
            text-decoration: underline;
        }
        .info-section {
            margin-bottom: 20px;
        }
        .info-row {
            display: flex;
            margin-bottom: 8px;
        }
        .info-label {
            width: 150px;
            font-weight: bold;
        }
        .info-value {
            flex: 1;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
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
            margin-top: 30px;
            border-top: 1px solid #000;
            padding-top: 20px;
        }
        .footer {
            margin-top: 50px;
            text-align: right;
        }
        .signature-line {
            border-top: 1px solid #000;
            width: 200px;
            margin-top: 50px;
            display: inline-block;
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
        <div class="koperasi-name">KOPERASI KARYAWAN</div>
        <div class="koperasi-address">Jl. Contoh No. 123, Jakarta</div>
        <div class="koperasi-address">Telp: (021) 1234567 | Email: info@kopkar.com</div>
    </div>

    <div class="document-title">NOTA PINJAMAN</div>

    <div class="info-section">
        <div class="info-row">
            <div class="info-label">No. Pinjaman:</div>
            <div class="info-value">{{ $dataNota['pinjaman']->id }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Tanggal Pinjam:</div>
            <div class="info-value">{{ \Carbon\Carbon::parse($dataNota['pinjaman']->tgl_pinjam)->format('d/m/Y H:i') }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">ID Anggota:</div>
            <div class="info-value">{{ 'AG' . sprintf('%04d', $dataNota['pinjaman']->anggota_id) }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Nama Anggota:</div>
            <div class="info-value">{{ $dataNota['pinjaman']->anggota->nama ?? 'N/A' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">NIK:</div>
            <div class="info-value">{{ $dataNota['pinjaman']->anggota->no_ktp ?? 'N/A' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Alamat:</div>
            <div class="info-value">{{ $dataNota['pinjaman']->anggota->alamat ?? 'N/A' }}</div>
        </div>
    </div>

    <div class="info-section">
        <div class="info-row">
            <div class="info-label">Jenis Pinjaman:</div>
            <div class="info-value">{{ $dataNota['pinjaman']->jenis_pinjaman == '1' ? 'Biasa' : 'Barang' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Jumlah Pinjaman:</div>
            <div class="info-value">Rp {{ number_format($dataNota['pinjaman']->jumlah, 0, ',', '.') }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Lama Angsuran:</div>
            <div class="info-value">{{ $dataNota['pinjaman']->lama_angsuran }} Bulan</div>
        </div>
        <div class="info-row">
            <div class="info-label">Angsuran per Bulan:</div>
            <div class="info-value">Rp {{ number_format($dataNota['pinjaman']->jumlah_angsuran, 0, ',', '.') }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Bunga:</div>
            <div class="info-value">{{ $dataNota['pinjaman']->bunga ?? 0 }}%</div>
        </div>
        <div class="info-row">
            <div class="info-label">Biaya Admin:</div>
            <div class="info-value">Rp {{ number_format($dataNota['pinjaman']->biaya_adm ?? 0, 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="total-section">
        <div class="info-row">
            <div class="info-label">Total Pinjaman:</div>
            <div class="info-value">Rp {{ number_format($dataNota['pinjaman']->jumlah, 0, ',', '.') }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Total Denda:</div>
            <div class="info-value">Rp {{ number_format($dataNota['total_denda'], 0, ',', '.') }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Total Tagihan:</div>
            <div class="info-value">Rp {{ number_format($dataNota['total_tagihan'], 0, ',', '.') }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Sudah Dibayar:</div>
            <div class="info-value">Rp {{ number_format($dataNota['sudah_dibayar'], 0, ',', '.') }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Sisa Tagihan:</div>
            <div class="info-value">Rp {{ number_format($dataNota['sisa_tagihan'], 0, ',', '.') }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Sisa Angsuran:</div>
            <div class="info-value">{{ $dataNota['sisa_angsuran'] }} Bulan</div>
        </div>
    </div>

    <div class="footer">
        <div>Jakarta, {{ \Carbon\Carbon::now()->format('d/m/Y') }}</div>
        <div>Admin Koperasi</div>
        <div class="signature-line"></div>
    </div>

    <div class="no-print" style="margin-top: 30px; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">
            Cetak Nota
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 5px; cursor: pointer; margin-left: 10px;">
            Tutup
        </button>
    </div>
</body>
</html>
