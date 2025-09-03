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
        
        .legend {
            margin-top: 20px;
            padding: 15px;
            background-color: #e3f2fd;
            border-radius: 5px;
            font-size: 10px;
        }
        
        .legend h4 {
            margin: 0 0 10px 0;
            font-size: 12px;
            font-weight: bold;
        }
        
        .legend-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
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
        <h2>Periode {{ \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->format('F Y') }}</h2>
        <p>Tanggal Cetak: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <!-- Information Section -->
    <div class="info-section">
        <div class="info-grid">
            <div class="info-card">
                <h3>Total Anggota</h3>
                <div class="value">{{ number_format($dataAnggota->count()) }}</div>
            </div>
            <div class="info-card">
                <h3>Total Setoran</h3>
                <div class="value">Rp {{ number_format($totalSimpanan) }}</div>
            </div>
            <div class="info-card">
                <h3>Total Penarikan</h3>
                <div class="value">Rp {{ number_format($totalPenarikan) }}</div>
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
                <th rowspan="2">No</th>
                <th rowspan="2">Photo</th>
                <th rowspan="2">Identitas</th>
                
                @foreach($jenisSimpanan as $jenis)
                <th colspan="3">{{ $jenis->jns_simpan }}</th>
                @endforeach
                
                <th colspan="3">Total Simpanan</th>
                <th colspan="3">Tagihan Kredit</th>
                <th colspan="3">Tagihan Simpanan</th>
            </tr>
            <tr>
                @foreach($jenisSimpanan as $jenis)
                <th>Setor</th>
                <th>Tarik</th>
                <th>Saldo</th>
                @endforeach
                
                <th>Setor</th>
                <th>Tarik</th>
                <th>Saldo</th>
                <th>Pinjaman</th>
                <th>Bayar</th>
                <th>Sisa</th>
                <th>Tagihan</th>
                <th>Bayar</th>
                <th>Sisa</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dataAnggota as $index => $anggota)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">
                    <div class="photo-placeholder">
                        <span>👤</span>
                    </div>
                </td>
                <td>
                    <div class="member-info">
                        <div><strong>ID:</strong> AG{{ str_pad($anggota->id, 4, '0', STR_PAD_LEFT) }}</div>
                        <div><strong>Nama:</strong> {{ $anggota->nama }}</div>
                        <div><strong>L/P:</strong> {{ $anggota->jk == 'L' ? 'L' : 'P' }}</div>
                        <div><strong>Jabatan:</strong> {{ $anggota->jabatan_id ? 'Pengurus' : 'Anggota' }}</div>
                        <div><strong>Dept:</strong> {{ $anggota->departement ?? '-' }}</div>
                        <div><strong>Alamat:</strong> {{ Str::limit($anggota->alamat ?? '-', 20) }}</div>
                        <div><strong>Telp:</strong> {{ $anggota->notelp ?? '-' }}</div>
                    </div>
                </td>
                
                @php
                    $kas = $kasData[$anggota->no_ktp] ?? [];
                @endphp
                
                @foreach($jenisSimpanan as $jenis)
                @php
                    $setor = $kas['setoran'][$jenis->id] ?? 0;
                    $tarik = $kas['penarikan'][$jenis->id] ?? 0;
                    $saldo = $setor - $tarik;
                @endphp
                <td class="text-right">{{ number_format($setor) }}</td>
                <td class="text-right">{{ number_format($tarik) }}</td>
                <td class="text-right font-bold {{ $saldo >= 0 ? 'text-green' : 'text-red' }}">
                    {{ number_format($saldo) }}
                </td>
                @endforeach
                
                <!-- Total Simpanan -->
                <td class="text-right font-bold">{{ number_format($kas['total_setor'] ?? 0) }}</td>
                <td class="text-right font-bold">{{ number_format($kas['total_tarik'] ?? 0) }}</td>
                <td class="text-right font-bold {{ ($kas['total_saldo'] ?? 0) >= 0 ? 'text-green' : 'text-red' }}">
                    {{ number_format($kas['total_saldo'] ?? 0) }}
                </td>
                
                <!-- Tagihan Kredit -->
                <td class="text-right">{{ number_format($kas['tagihan_kredit'] ?? 0) }}</td>
                <td class="text-right">{{ number_format($kas['bayar_kredit'] ?? 0) }}</td>
                <td class="text-right font-bold {{ ($kas['sisa_kredit'] ?? 0) >= 0 ? 'text-green' : 'text-red' }}">
                    {{ number_format($kas['sisa_kredit'] ?? 0) }}
                </td>
                
                <!-- Tagihan Simpanan -->
                <td class="text-right">{{ number_format($kas['tagihan_simpanan'] ?? 0) }}</td>
                <td class="text-right">{{ number_format($kas['bayar'] ?? 0) }}</td>
                <td class="text-right font-bold {{ ($kas['sisa'] ?? 0) >= 0 ? 'text-green' : 'text-red' }}">
                    {{ number_format($kas['sisa'] ?? 0) }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Legend -->
    <div class="legend">
        <h4>Keterangan:</h4>
        <div class="legend-grid">
            <div>
                <p><strong>Setor:</strong> Total setoran anggota per jenis simpanan</p>
                <p><strong>Tarik:</strong> Total penarikan anggota per jenis simpanan</p>
                <p><strong>Saldo:</strong> Selisih antara setoran dan penarikan</p>
            </div>
            <div>
                <p><strong>Tagihan Kredit:</strong> Total tagihan pinjaman yang harus dibayar</p>
                <p><strong>Bayar Kredit:</strong> Total pembayaran pinjaman yang sudah dilakukan</p>
                <p><strong>Sisa Kredit:</strong> Selisih antara tagihan dan pembayaran pinjaman</p>
            </div>
            <div>
                <p><strong>Tagihan Simpanan:</strong> Total tagihan simpanan bulanan</p>
                <p><strong>Bayar Simpanan:</strong> Total pembayaran simpanan yang sudah dilakukan</p>
                <p><strong>Sisa Simpanan:</strong> Selisih antara tagihan dan pembayaran simpanan</p>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Laporan ini dibuat secara otomatis oleh sistem Koperasi pada {{ \Carbon\Carbon::now()->format('d F Y H:i:s') }}</p>
        <p>Halaman 1 dari 1 | Total Data: {{ $dataAnggota->count() }} anggota</p>
    </div>
</body>
</html>
