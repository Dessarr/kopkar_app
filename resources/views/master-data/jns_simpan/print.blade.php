<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Jenis Simpanan - Print</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #fff;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #14AE5C;
            padding-bottom: 20px;
        }
        
        .header h1 {
            color: #14AE5C;
            margin: 0;
            font-size: 24px;
        }
        
        .header p {
            color: #666;
            margin: 5px 0 0 0;
            font-size: 14px;
        }
        
        .print-info {
            text-align: right;
            margin-bottom: 20px;
            font-size: 12px;
            color: #666;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 12px;
        }
        
        th {
            background-color: #14AE5C;
            color: white;
            font-weight: bold;
        }
        
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .summary {
            margin-top: 30px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        
        .summary h3 {
            margin: 0 0 10px 0;
            color: #14AE5C;
            font-size: 16px;
        }
        
        .summary-item {
            display: inline-block;
            margin-right: 20px;
            margin-bottom: 5px;
        }
        
        .summary-label {
            font-weight: bold;
            color: #333;
        }
        
        .summary-value {
            color: #666;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 10px;
            }
            
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Data Jenis Simpanan</h1>
        <p>Sistem Informasi Koperasi</p>
    </div>
    
    <div class="print-info">
        Dicetak pada: {{ date('d/m/Y H:i:s') }}
    </div>
    
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 10%;">ID</th>
                <th style="width: 30%;">Jenis Simpanan</th>
                <th style="width: 20%;">Jumlah Minimum</th>
                <th style="width: 15%;">Status Tampil</th>
                <th style="width: 10%;">Urutan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dataSimpan as $index => $simpan)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $simpan->id }}</td>
                <td>{{ $simpan->jns_simpan }}</td>
                <td>Rp {{ number_format($simpan->jumlah, 0, ',', '.') }}</td>
                <td>{{ $simpan->tampil == 'Y' ? 'Tampil' : 'Tidak Tampil' }}</td>
                <td>{{ $simpan->urut }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; padding: 20px;">
                    Tidak ada data jenis simpanan
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    
    <div class="summary">
        <h3>Ringkasan Data</h3>
        <div class="summary-item">
            <span class="summary-label">Total Jenis:</span>
            <span class="summary-value">{{ $dataSimpan->count() }}</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">Tampil:</span>
            <span class="summary-value">{{ $dataSimpan->where('tampil', 'Y')->count() }}</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">Tidak Tampil:</span>
            <span class="summary-value">{{ $dataSimpan->where('tampil', 'T')->count() }}</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">Total Nilai:</span>
            <span class="summary-value">Rp {{ number_format($dataSimpan->sum('jumlah'), 0, ',', '.') }}</span>
        </div>
    </div>
    
    <script>
        // Auto print when page loads
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
