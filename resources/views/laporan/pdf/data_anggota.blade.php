<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Data Anggota</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
            color: #333;
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
            margin: 0 0 10px 0;
            color: #333;
        }
        
        .header h2 {
            font-size: 18px;
            font-weight: bold;
            margin: 0 0 5px 0;
            color: #666;
        }
        
        .header p {
            margin: 0;
            color: #666;
        }
        
        .info-section {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .info-label {
            font-weight: bold;
            min-width: 150px;
        }
        
        .info-value {
            flex: 1;
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
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .status-aktif {
            background-color: #d4edda;
            color: #155724;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
        }
        
        .status-nonaktif {
            background-color: #f8d7da;
            color: #721c24;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
        }
        
        .photo-placeholder {
            width: 20px;
            height: 20px;
            background-color: #e9ecef;
            border-radius: 50%;
            display: inline-block;
            text-align: center;
            line-height: 20px;
            font-size: 8px;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 15px;
            }
            
            .page-break {
                page-break-before: always;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>LAPORAN DATA ANGGOTA KOPERASI</h1>
        <h2>Koperasi Karyawan</h2>
        <p>Tanggal Cetak: {{ \Carbon\Carbon::now()->format('d F Y H:i:s') }}</p>
    </div>

    <!-- Information Section -->
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Total Anggota:</span>
            <span class="info-value">{{ $dataAnggota->count() }} orang</span>
        </div>
        <div class="info-row">
            <span class="info-label">Anggota Aktif:</span>
            <span class="info-value">{{ $dataAnggota->where('aktif', 'Y')->count() }} orang</span>
        </div>
        <div class="info-row">
            <span class="info-label">Anggota Tidak Aktif:</span>
            <span class="info-value">{{ $dataAnggota->where('aktif', 'N')->count() }} orang</span>
        </div>
    </div>

    <!-- Data Table -->
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No.</th>
                <th style="width: 15%;">ID Anggota</th>
                <th style="width: 20%;">Nama Anggota</th>
                <th style="width: 5%;">L/P</th>
                <th style="width: 15%;">Jabatan</th>
                <th style="width: 20%;">Alamat</th>
                <th style="width: 8%;">Status</th>
                <th style="width: 8%;">Tgl Registrasi</th>
                <th style="width: 4%;">Photo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dataAnggota as $index => $anggota)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>
                    AG{{ str_pad($anggota->id, 4, '0', STR_PAD_LEFT) }}<br>
                    <small>{{ $anggota->no_ktp }}</small>
                </td>
                <td>
                    <strong>{{ $anggota->nama }}</strong><br>
                    <small>{{ $anggota->tmp_lahir }}/{{ $anggota->tgl_lahir ? \Carbon\Carbon::parse($anggota->tgl_lahir)->format('d-m-Y') : '-' }}</small>
                </td>
                <td class="text-center">{{ $anggota->jk === 'L' ? 'L' : 'P' }}</td>
                <td>
                    {{ $anggota->jabatan_id == 1 ? 'Pengurus' : 'Anggota' }}<br>
                    <small>{{ $anggota->departement }}</small>
                </td>
                <td>
                    {{ $anggota->alamat }}<br>
                    <small>{{ $anggota->notelp }}</small>
                </td>
                <td class="text-center">
                    @if($anggota->aktif === 'Y')
                        <span class="status-aktif">Aktif</span>
                    @else
                        <span class="status-nonaktif">Tidak Aktif</span>
                    @endif
                </td>
                <td class="text-center">
                    {{ $anggota->tgl_daftar ? \Carbon\Carbon::parse($anggota->tgl_daftar)->format('d/m/Y') : '-' }}
                </td>
                <td class="text-center">
                    <div class="photo-placeholder">
                        @if($anggota->file_pic)
                            ✓
                        @else
                            -
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        <p>Laporan ini dibuat secara otomatis pada {{ \Carbon\Carbon::now()->format('d F Y H:i:s') }}</p>
        <p>Koperasi Karyawan - Sistem Informasi Koperasi</p>
    </div>
</body>
</html>
