<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Kas Anggota</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
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
            font-size: 24px;
            font-weight: bold;
            margin: 0;
            color: #333;
        }
        
        .header h2 {
            font-size: 18px;
            margin: 10px 0 0 0;
            color: #666;
        }
        
        .info-section {
            margin-bottom: 20px;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .info-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        
        .info-card h3 {
            margin: 0 0 5px 0;
            font-size: 14px;
            font-weight: normal;
        }
        
        .info-card .value {
            font-size: 18px;
            font-weight: bold;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10px;
        }
        
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
            vertical-align: top;
        }
        
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            text-align: center;
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
        
        .text-green {
            color: #28a745;
        }
        
        .text-red {
            color: #dc3545;
        }
        
        .member-info {
            font-size: 9px;
            line-height: 1.2;
        }
        
        .member-info div {
            margin-bottom: 2px;
        }
        
        .photo-placeholder {
            width: 30px;
            height: 30px;
            background-color: #e9ecef;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        
        
        @media print {
            body {
                margin: 0;
                padding: 10px;
            }
            
            .header {
                margin-bottom: 20px;
            }
            
            table {
                font-size: 9px;
            }
            
            th, td {
                padding: 4px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>LAPORAN DATA KAS PER ANGGOTA</h1>
        <h2>Periode {{ \Carbon\Carbon::now()->format('F Y') }}</h2>
        <p>Tanggal Cetak: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
        @if($search)
        <p>Hasil pencarian untuk: "{{ $search }}"</p>
        @endif
    </div>

    <!-- Information Section -->
    <div class="info-section">
        <div class="info-grid">
            <div class="info-card">
                <h3>Total Anggota</h3>
                <div class="value">{{ number_format($totalAnggota) }}</div>
            </div>
            <div class="info-card">
                <h3>Total Saldo</h3>
                <div class="value {{ $totalSaldo >= 0 ? 'text-green' : 'text-red' }}">Rp {{ number_format($totalSaldo) }}</div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Photo</th>
                <th>Identitas</th>
                <th>Saldo Simpanan</th>
                <th>Tagihan Kredit</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dataAnggota as $index => $anggota)
            @php
            $anggotaInfo = $anggotaData[$anggota->no_ktp] ?? null;
            $identitas = $anggotaInfo['identitas'] ?? [];
            $saldoSimpanan = $anggotaInfo['saldo_simpanan'] ?? [];
            $tagihanKredit = $anggotaInfo['tagihan_kredit'] ?? [];
            $keterangan = $anggotaInfo['keterangan'] ?? [];
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">
                    <div class="photo-placeholder">
                        <span>👤</span>
                    </div>
                </td>
                <td>
                    <div class="member-info">
                        <div><strong>ID Anggota:</strong> {{ $identitas['id_anggota'] ?? '-' }}</div>
                        <div><strong>Nama:</strong> {{ $identitas['nama'] ?? '-' }}</div>
                        <div><strong>Jenis Kelamin:</strong> {{ $identitas['jenis_kelamin'] ?? '-' }}</div>
                        <div><strong>Alamat:</strong> {{ Str::limit($identitas['alamat'] ?? '-', 30) }}</div>
                        <div><strong>Telp:</strong> {{ $identitas['telp'] ?? '-' }}</div>
                    </div>
                </td>
                <td>
                    <div class="member-info">
                        <div><strong>Simpanan Wajib:</strong> {{ number_format($saldoSimpanan->simpanan_wajib ?? 0, 0, ',', '.') }}</div>
                        <div><strong>Simpanan Sukarela:</strong> {{ number_format($saldoSimpanan->simpanan_sukarela ?? 0, 0, ',', '.') }}</div>
                        <div><strong>Simpanan Khusus II:</strong> {{ number_format($saldoSimpanan->simpanan_khusus_2 ?? 0, 0, ',', '.') }}</div>
                        <div><strong>Simpanan Pokok:</strong> {{ number_format($saldoSimpanan->simpanan_pokok ?? 0, 0, ',', '.') }}</div>
                        <div><strong>Simpanan Khusus I:</strong> {{ number_format($saldoSimpanan->simpanan_khusus_1 ?? 0, 0, ',', '.') }}</div>
                        <div><strong>Tab. Perumahan:</strong> {{ number_format($saldoSimpanan->tab_perumahan ?? 0, 0, ',', '.') }}</div>
                        <div class="font-bold text-green"><strong>Jumlah:</strong> {{ number_format(($saldoSimpanan->simpanan_wajib ?? 0) + ($saldoSimpanan->simpanan_sukarela ?? 0) + ($saldoSimpanan->simpanan_khusus_2 ?? 0) + ($saldoSimpanan->simpanan_pokok ?? 0) + ($saldoSimpanan->simpanan_khusus_1 ?? 0) + ($saldoSimpanan->tab_perumahan ?? 0), 0, ',', '.') }}</div>
                    </div>
                </td>
                <td>
                    <div class="member-info">
                        <div><strong>Pinjaman Biasa:</strong> {{ number_format($tagihanKredit->pinjaman_biasa ?? 0, 0, ',', '.') }}</div>
                        <div><strong>Sisa Pinjaman:</strong> {{ number_format($tagihanKredit->sisa_pinjaman_biasa ?? 0, 0, ',', '.') }}</div>
                        <div><strong>Pinjaman Barang:</strong> {{ number_format($tagihanKredit->pinjaman_barang ?? 0, 0, ',', '.') }}</div>
                        <div><strong>Sisa Pinjaman:</strong> {{ number_format($tagihanKredit->sisa_pinjaman_barang ?? 0, 0, ',', '.') }}</div>
                        <div><strong>Tagihan Takterbayar:</strong> {{ number_format(0, 0, ',', '.') }}</div>
                    </div>
                </td>
                <td>
                    <div class="member-info">
                        <div><strong>Jumlah Pinjaman:</strong> {{ $keterangan->jumlah_pinjaman ?? 0 }}</div>
                        <div><strong>Pinjaman Lunas:</strong> {{ $keterangan->pinjaman_lunas ?? 0 }}</div>
                        <div><strong>Pembayaran:</strong> {{ $keterangan->status_pembayaran ?? 'Lancar' }}</div>
                        <div><strong>Tanggal Tempo:</strong> {{ $keterangan->tanggal_tempo ?? '-' }}</div>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>


    <!-- Footer -->
    <div class="footer">
        <p>Laporan ini dibuat secara otomatis oleh sistem Koperasi pada {{ \Carbon\Carbon::now()->format('d F Y H:i:s') }}</p>
        <p>Halaman 1 dari 1 | Total Data: {{ $dataAnggota->count() }} anggota</p>
    </div>
</body>
</html>
