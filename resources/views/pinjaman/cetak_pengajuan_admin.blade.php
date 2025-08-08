<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Cetak Pengajuan - Admin</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; }
        .wrap { width: 800px; margin: 0 auto; }
        .row { display: flex; justify-content: space-between; margin-bottom: 8px; }
        .label { color: #555; width: 200px; }
        .value { font-weight: 600; }
        .title { text-align: center; font-size: 18px; font-weight: bold; margin-bottom: 16px; }
        .hr { border-top: 1px solid #ddd; margin: 12px 0; }
    </style>
</head>
<body onload="window.print()">
    <div class="wrap">
        <div class="title">Data Pengajuan Pinjaman</div>
        <div class="row"><div class="label">ID Ajuan</div><div class="value">{{ $pengajuan->ajuan_id }}</div></div>
        <div class="row"><div class="label">Anggota ID</div><div class="value">{{ $pengajuan->anggota_id }}</div></div>
        <div class="row"><div class="label">Tanggal Pengajuan</div><div class="value">{{ \Carbon\Carbon::parse($pengajuan->tgl_input)->format('d/m/Y H:i') }}</div></div>
        <div class="row"><div class="label">Jenis</div><div class="value">{{ $pengajuan->jenis == '1' ? 'Biasa' : $pengajuan->jenis }}</div></div>
        <div class="row"><div class="label">Jumlah</div><div class="value">Rp {{ number_format($pengajuan->nominal,0,',','.') }}</div></div>
        <div class="row"><div class="label">Lama Angsuran</div><div class="value">{{ $pengajuan->lama_ags }} bulan</div></div>
        <div class="row"><div class="label">Status</div><div class="value">{{ [0=>'Pending',1=>'Disetujui',2=>'Ditolak',3=>'Terlaksana',4=>'Batal'][$pengajuan->status] ?? $pengajuan->status }}</div></div>
        <div class="row"><div class="label">Keterangan</div><div class="value">{{ $pengajuan->keterangan }}</div></div>
        <div class="row"><div class="label">Alasan</div><div class="value">{{ $pengajuan->alasan }}</div></div>
        <div class="hr"></div>
        <div>Dicetak: {{ now()->format('d/m/Y H:i') }}</div>
    </div>
</body>
</html>


