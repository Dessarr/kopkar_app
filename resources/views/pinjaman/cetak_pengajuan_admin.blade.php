<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Pengajuan - {{ $pengajuan->ajuan_id }}</title>
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

    .table th,
    .table td {
        border: 1px solid #000;
        padding: 8px;
        text-align: left;
    }

    .table th {
        background-color: #f0f0f0;
        font-weight: bold;
    }

    .status-section {
        margin-top: 20px;
        padding: 15px;
        border: 2px solid #000;
        border-radius: 5px;
    }

    .status-approved {
        background-color: #d4edda;
        border-color: #28a745;
    }

    .status-rejected {
        background-color: #f8d7da;
        border-color: #dc3545;
    }

    .status-pending {
        background-color: #fff3cd;
        border-color: #ffc107;
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

    .no-print {
        display: none;
    }

    @media print {
        body {
            margin: 0;
            padding: 15px;
        }
    }
    </style>
</head>

<body>
    <div class="header">
        <h1>KOPERASI INDONESIA</h1>
        <p>Jl. Contoh No. 123, Jakarta</p>
        <p>Telp: (021) 123-4567 | Email: info@kopkar.co.id</p>
        <h2>FORMULIR PENGAJUAN PINJAMAN</h2>
    </div>

    <div class="content">
        <div class="section">
            <h3>Informasi Pengajuan</h3>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">No. Ajuan:</span>
                    <span class="info-value">{{ $pengajuan->no_ajuan }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">ID Ajuan:</span>
                    <span class="info-value">{{ $pengajuan->ajuan_id }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Tanggal Pengajuan:</span>
                    <span
                        class="info-value">{{ \Carbon\Carbon::parse($pengajuan->tgl_input)->format('d/m/Y H:i') }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Status:</span>
                    <span class="info-value">
                        @php
                        $statusMap = [
                        0 => 'Menunggu Konfirmasi',
                        1 => 'Disetujui',
                        2 => 'Ditolak',
                        3 => 'Terlaksana',
                        4 => 'Batal'
                        ];
                        @endphp
                        {{ $statusMap[$pengajuan->status] ?? $pengajuan->status }}
                    </span>
                </div>
            </div>
        </div>

        <div class="section">
            <h3>Data Anggota</h3>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">ID Anggota:</span>
                    <span class="info-value">{{ $pengajuan->anggota_id }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Nama Anggota:</span>
                    <span class="info-value">{{ $pengajuan->anggota->nama ?? '-' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">No KTP:</span>
                    <span class="info-value">{{ $pengajuan->anggota->no_ktp ?? '-' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Alamat:</span>
                    <span class="info-value">{{ $pengajuan->anggota->alamat ?? '-' }}</span>
                </div>
            </div>
        </div>

        <div class="section">
            <h3>Detail Pinjaman</h3>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Jenis Pinjaman:</span>
                    <span class="info-value">{{ $pengajuan->jenis == '1' ? 'Biasa' : 'Barang' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Jumlah Pinjaman:</span>
                    <span class="info-value">Rp {{ number_format($pengajuan->nominal, 0, ',', '.') }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Lama Angsuran:</span>
                    <span class="info-value">{{ $pengajuan->lama_ags }} Bulan</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Keterangan:</span>
                    <span class="info-value">{{ $pengajuan->keterangan ?: '-' }}</span>
                </div>
            </div>
        </div>

        @if($pengajuan->status == 1 || $pengajuan->status == 3)
        <div class="section">
            <h3>Informasi Persetujuan</h3>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Tanggal Cair:</span>
                    <span
                        class="info-value">{{ $pengajuan->tgl_cair ? \Carbon\Carbon::parse($pengajuan->tgl_cair)->format('d/m/Y') : '-' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Tanggal Update:</span>
                    <span
                        class="info-value">{{ $pengajuan->tgl_update ? \Carbon\Carbon::parse($pengajuan->tgl_update)->format('d/m/Y H:i') : '-' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Alasan:</span>
                    <span class="info-value">{{ $pengajuan->alasan ?: '-' }}</span>
                </div>
            </div>
        </div>
        @endif

        @if($pengajuan->status == 2)
        <div class="section">
            <h3>Informasi Penolakan</h3>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Tanggal Update:</span>
                    <span
                        class="info-value">{{ $pengajuan->tgl_update ? \Carbon\Carbon::parse($pengajuan->tgl_update)->format('d/m/Y H:i') : '-' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Alasan Penolakan:</span>
                    <span class="info-value">{{ $pengajuan->alasan ?: '-' }}</span>
                </div>
            </div>
        </div>
        @endif

        <div class="status-section 
            @if($pengajuan->status == 1) status-approved
            @elseif($pengajuan->status == 2) status-rejected
            @else status-pending
            @endif">
            <h3>Status Pengajuan</h3>
            <p><strong>Status Saat Ini:</strong>
                @if($pengajuan->status == 0)
                <span style="color: #856404;">Menunggu Konfirmasi dari Admin</span>
                @elseif($pengajuan->status == 1)
                <span style="color: #155724;">DISETUJUI - Siap untuk diproses</span>
                @elseif($pengajuan->status == 2)
                <span style="color: #721c24;">DITOLAK - {{ $pengajuan->alasan }}</span>
                @elseif($pengajuan->status == 3)
                <span style="color: #155724;">TERLAKSANA - Pinjaman sudah dicairkan</span>
                @elseif($pengajuan->status == 4)
                <span style="color: #6c757d;">DIBATALKAN</span>
                @endif
            </p>
        </div>
    </div>

    <div class="footer">
        <p>Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}</p>
        <p>Dokumen ini dicetak otomatis dari sistem Koperasi Indonesia</p>
    </div>

    <div class="signature">
        <div class="signature-item">
            <div class="signature-line">Pemohon</div>
        </div>
        <div class="signature-item">
            <div class="signature-line">Admin</div>
        </div>
    </div>

    <div class="no-print" style="margin-top: 30px; text-align: center;">
        <button onclick="window.print()"
            style="padding: 10px 20px; font-size: 16px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">
            Cetak
        </button>
        <button onclick="window.close()"
            style="padding: 10px 20px; font-size: 16px; background-color: #6c757d; color: white; border: none; border-radius: 5px; cursor: pointer; margin-left: 10px;">
            Tutup
        </button>
    </div>
</body>

</html>