<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bukti SHU</title>
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
            padding-bottom: 15px;
        }
        .title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .subtitle {
            font-size: 16px;
            margin-bottom: 5px;
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
        .amount-section {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
            border: 2px solid #333;
            border-radius: 10px;
        }
        .amount-label {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .amount-value {
            font-size: 24px;
            font-weight: bold;
            color: #14AE5C;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
        }
        .signature-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            text-align: center;
            width: 200px;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 50px;
            padding-top: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">BUKTI PEMBAYARAN SHU</div>
        <div class="subtitle">Koperasi Karyawan</div>
        <div>Nomor: TRD{{ str_pad($shu->id, 5, '0', STR_PAD_LEFT) }}</div>
    </div>

    <div class="info-section">
        <div class="info-row">
            <div class="info-label">Tanggal:</div>
            <div class="info-value">{{ $shu->tgl_transaksi->format('d F Y') }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">ID Anggota:</div>
            <div class="info-value">AG{{ str_pad($shu->anggota->id ?? 0, 4, '0', STR_PAD_LEFT) }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Nama Anggota:</div>
            <div class="info-value">{{ $shu->anggota->nama ?? 'N/A' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">No KTP:</div>
            <div class="info-value">{{ $shu->no_ktp }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Alamat:</div>
            <div class="info-value">{{ $shu->anggota->alamat ?? 'N/A' }}</div>
        </div>
    </div>

    <div class="amount-section">
        <div class="amount-label">JUMLAH SHU YANG DITERIMA</div>
        <div class="amount-value">Rp {{ number_format($shu->jumlah_bayar, 0, ',', '.') }}</div>
    </div>

    <div class="info-section">
        <div class="info-row">
            <div class="info-label">Keterangan:</div>
            <div class="info-value">{{ $shu->keterangan ?? 'Pembayaran SHU' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Dibuat oleh:</div>
            <div class="info-value">{{ $shu->user_name }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Tanggal Cetak:</div>
            <div class="info-value">{{ now()->format('d F Y H:i:s') }}</div>
        </div>
    </div>

    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line">Anggota</div>
        </div>
        <div class="signature-box">
            <div class="signature-line">Bendahara</div>
        </div>
        <div class="signature-box">
            <div class="signature-line">Ketua</div>
        </div>
    </div>

    <div class="footer">
        <p><strong>Terima kasih telah menjadi anggota koperasi kami</strong></p>
        <p>Dokumen ini adalah bukti sah pembayaran SHU</p>
    </div>
</body>
</html>
