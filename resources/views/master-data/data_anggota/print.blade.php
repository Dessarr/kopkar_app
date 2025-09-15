<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Data Anggota - {{ date('d/m/Y H:i') }}</title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none !important; }
            .print-break { page-break-before: always; }
        }
        
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
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
            color: #14AE5C;
        }
        
        .header p {
            margin: 5px 0 0 0;
            font-size: 14px;
            color: #666;
        }
        
        .info {
            margin-bottom: 20px;
            font-size: 12px;
            color: #666;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 12px;
        }
        
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        
        .status-aktif {
            color: #059669;
            font-weight: bold;
        }
        
        .status-tidak-aktif {
            color: #dc2626;
            font-weight: bold;
        }
        
        .jenis-kelamin-laki {
            color: #2563eb;
            font-weight: bold;
        }
        
        .jenis-kelamin-perempuan {
            color: #dc2626;
            font-weight: bold;
        }
        
        .summary {
            margin-top: 30px;
            padding: 15px;
            background-color: #f9fafb;
            border-radius: 5px;
        }
        
        .summary h3 {
            margin: 0 0 10px 0;
            color: #14AE5C;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .summary-item {
            text-align: center;
        }
        
        .summary-item .number {
            font-size: 24px;
            font-weight: bold;
            color: #14AE5C;
        }
        
        .summary-item .label {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>KOPERASI INDONESIA</h1>
        <p>Laporan Data Anggota Aktif</p>
        <p>Tanggal Cetak: {{ date('d/m/Y H:i') }}</p>
    </div>

    <div class="info">
        <p><strong>Total Data:</strong> {{ $dataAnggota->count() }} anggota</p>
        <p><strong>Laki-laki:</strong> {{ $dataAnggota->where('jk', 'L')->count() }} anggota</p>
        <p><strong>Perempuan:</strong> {{ $dataAnggota->where('jk', 'P')->count() }} anggota</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>ID Koperasi</th>
                <th>Nama Lengkap</th>
                <th>Jenis Kelamin</th>
                <th>Alamat</th>
                <th>Kota</th>
                <th>Department</th>
                <th>Tgl Daftar</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dataAnggota as $index => $anggota)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $anggota->no_ktp }}</td>
                <td>{{ $anggota->nama }}</td>
                <td>
                    <span class="{{ $anggota->jk == 'L' ? 'jenis-kelamin-laki' : 'jenis-kelamin-perempuan' }}">
                        {{ $anggota->jk == 'L' ? 'Laki-laki' : 'Perempuan' }}
                    </span>
                </td>
                <td>{{ $anggota->alamat }}</td>
                <td>{{ $anggota->kota }}</td>
                <td>{{ $anggota->departement }}</td>
                <td>
                    @if($anggota->tgl_daftar && $anggota->tgl_daftar != '0000-00-00')
                    {{ date('d/m/Y', strtotime($anggota->tgl_daftar)) }}
                    @else
                    <span class="text-gray-400 italic text-xs">Tidak ada Data</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <h3>Ringkasan Data Anggota</h3>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="number">{{ $dataAnggota->count() }}</div>
                <div class="label">Total Anggota</div>
            </div>
            <div class="summary-item">
                <div class="number">{{ $dataAnggota->where('jk', 'L')->count() }}</div>
                <div class="label">Laki-laki</div>
            </div>
            <div class="summary-item">
                <div class="number">{{ $dataAnggota->where('jk', 'P')->count() }}</div>
                <div class="label">Perempuan</div>
            </div>
            <div class="summary-item">
                <div class="number">{{ $dataAnggota->groupBy('departement')->count() }}</div>
                <div class="label">Department</div>
            </div>
            <div class="summary-item">
                <div class="number">{{ $dataAnggota->groupBy('kota')->count() }}</div>
                <div class="label">Kota</div>
            </div>
        </div>
    </div>

    <div class="no-print" style="margin-top: 30px; text-align: center;">
        <button onclick="window.print()" style="background-color: #14AE5C; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin-right: 10px;">
            <i class="fas fa-print"></i> Cetak
        </button>
        <button onclick="window.close()" style="background-color: #6b7280; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
            <i class="fas fa-times"></i> Tutup
        </button>
    </div>

    <script>
        // Auto print saat halaman dibuka
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        }
    </script>
</body>
</html>
