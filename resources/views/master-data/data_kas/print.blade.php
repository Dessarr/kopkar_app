<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Data Kas - {{ date('d/m/Y H:i') }}</title>
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
        
        .kategori-komprehensif {
            color: #7c3aed;
            font-weight: bold;
        }
        
        .kategori-menengah {
            color: #2563eb;
            font-weight: bold;
        }
        
        .kategori-dasar {
            color: #d97706;
            font-weight: bold;
        }
        
        .kategori-minimal {
            color: #6b7280;
            font-weight: bold;
        }
        
        .fitur-aktif {
            color: #059669;
        }
        
        .fitur-tidak-aktif {
            color: #dc2626;
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
        <p>Laporan Data Kas</p>
        <p>Tanggal Cetak: {{ date('d/m/Y H:i') }}</p>
    </div>

    <div class="info">
        <p><strong>Total Data:</strong> {{ $dataKas->count() }} kas</p>
        <p><strong>Status Aktif:</strong> {{ $dataKas->where('aktif', 'Y')->count() }} kas</p>
        <p><strong>Status Tidak Aktif:</strong> {{ $dataKas->where('aktif', 'T')->count() }} kas</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Kas</th>
                <th>Status</th>
                <th>Fitur</th>
                <th>Kategori</th>
                <th>Total Fitur</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dataKas as $index => $kas)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $kas->nama }}</td>
                <td>
                    <span class="{{ $kas->aktif === 'Y' ? 'status-aktif' : 'status-tidak-aktif' }}">
                        {{ $kas->status_aktif_text }}
                    </span>
                </td>
                <td>
                    <div style="font-size: 10px;">
                        @if($kas->tmpl_simpan === 'Y')
                            <div class="fitur-aktif">✓ Simpanan</div>
                        @else
                            <div class="fitur-tidak-aktif">✗ Simpanan</div>
                        @endif
                        @if($kas->tmpl_penarikan === 'Y')
                            <div class="fitur-aktif">✓ Penarikan</div>
                        @else
                            <div class="fitur-tidak-aktif">✗ Penarikan</div>
                        @endif
                        @if($kas->tmpl_pinjaman === 'Y')
                            <div class="fitur-aktif">✓ Pinjaman</div>
                        @else
                            <div class="fitur-tidak-aktif">✗ Pinjaman</div>
                        @endif
                        @if($kas->tmpl_bayar === 'Y')
                            <div class="fitur-aktif">✓ Bayar</div>
                        @else
                            <div class="fitur-tidak-aktif">✗ Bayar</div>
                        @endif
                        @if($kas->tmpl_pemasukan === 'Y')
                            <div class="fitur-aktif">✓ Pemasukan</div>
                        @else
                            <div class="fitur-tidak-aktif">✗ Pemasukan</div>
                        @endif
                        @if($kas->tmpl_pengeluaran === 'Y')
                            <div class="fitur-aktif">✓ Pengeluaran</div>
                        @else
                            <div class="fitur-tidak-aktif">✗ Pengeluaran</div>
                        @endif
                        @if($kas->tmpl_transfer === 'Y')
                            <div class="fitur-aktif">✓ Transfer</div>
                        @else
                            <div class="fitur-tidak-aktif">✗ Transfer</div>
                        @endif
                    </div>
                </td>
                <td>
                    <span class="kategori-{{ strtolower($kas->kategori_kas) }}">
                        {{ $kas->kategori_kas }}
                    </span>
                </td>
                <td>
                    <strong>{{ $kas->total_fitur_aktif }}/7</strong>
                    <div style="width: 100%; background-color: #e5e7eb; height: 4px; margin-top: 2px;">
                        <div style="width: {{ ($kas->total_fitur_aktif / 7) * 100 }}%; background-color: #14AE5C; height: 4px;"></div>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <h3>Ringkasan Data Kas</h3>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="number">{{ $dataKas->count() }}</div>
                <div class="label">Total Data Kas</div>
            </div>
            <div class="summary-item">
                <div class="number">{{ $dataKas->where('aktif', 'Y')->count() }}</div>
                <div class="label">Status Aktif</div>
            </div>
            <div class="summary-item">
                <div class="number">{{ $dataKas->where('aktif', 'T')->count() }}</div>
                <div class="label">Status Tidak Aktif</div>
            </div>
            <div class="summary-item">
                <div class="number">{{ $dataKas->where('aktif', 'Y')->filter(function($item) { return $item->total_fitur_aktif >= 6; })->count() }}</div>
                <div class="label">Kategori Komprehensif</div>
            </div>
            <div class="summary-item">
                <div class="number">{{ number_format($dataKas->where('aktif', 'Y')->avg('total_fitur_aktif'), 1) }}</div>
                <div class="label">Rata-rata Fitur</div>
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
