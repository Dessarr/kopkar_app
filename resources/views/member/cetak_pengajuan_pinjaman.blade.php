<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Cetak Pengajuan Pinjaman</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; }
        .wrap { width: 720px; margin: 0 auto; }
        .row { display: flex; justify-content: space-between; margin-bottom: 8px; }
        .label { color: #555; width: 180px; }
        .value { font-weight: 600; }
        .title { text-align: center; font-size: 18px; font-weight: bold; margin-bottom: 16px; }
        .muted { color: #666; }
        .hr { border-top: 1px solid #ddd; margin: 12px 0; }
    </style>
</head>
<body onload="window.print()">
    <div class="wrap">
        <div class="title">Dokumen Pengajuan Pinjaman</div>
        <div class="row"><div class="label">ID Ajuan</div><div class="value">{{ $pengajuan->ajuan_id }}</div></div>
        <div class="row"><div class="label">Anggota</div><div class="value">{{ $member->nama }} (ID: {{ $member->id }})</div></div>
        <div class="row"><div class="label">Tanggal Pengajuan</div><div class="value">{{ \Carbon\Carbon::parse($pengajuan->tgl_input)->format('d/m/Y H:i') }}</div></div>
        <div class="row"><div class="label">Jenis</div><div class="value">
            @php
            $jenisMap = [
                '1' => 'Biasa',
                '3' => 'Barang'
            ];
            $jenisText = $jenisMap[$pengajuan->jenis] ?? $pengajuan->jenis;
            @endphp
            {{ $jenisText }}
        </div></div>
        <div class="row"><div class="label">Jumlah</div><div class="value">Rp {{ number_format($pengajuan->nominal,0,',','.') }}</div></div>
        <div class="row"><div class="label">Lama Angsuran</div><div class="value">{{ $pengajuan->lama_ags }} bulan</div></div>
        <div class="row"><div class="label">Status</div><div class="value">{{ [0=>'Pending',1=>'Disetujui',2=>'Ditolak',3=>'Terlaksana',4=>'Batal'][$pengajuan->status] ?? $pengajuan->status }}</div></div>
        <div class="hr"></div>
        <div class="muted">Dicetak oleh {{ $member->nama }} pada {{ now()->format('d/m/Y H:i') }}</div>
    </div>
</body>
</html>


