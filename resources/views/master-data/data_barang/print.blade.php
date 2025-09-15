<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Barang - Print</title>
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
            display: flex;
            justify-content: space-between;
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
        }
        
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            text-align: center;
        }
        
        .status-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        
        .status-green {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-yellow {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-red {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .footer {
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
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
        <h1>Data Barang</h1>
        <p>Master Data Barang - Koperasi Karyawan</p>
    </div>
    
    <div class="print-info">
        <div>
            <strong>Tanggal Cetak:</strong> {{ date('d F Y, H:i') }}
        </div>
        <div>
            <strong>Total Data:</strong> {{ $dataBarang->count() }} barang
        </div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 10%;">ID</th>
                <th style="width: 25%;">Nama Barang</th>
                <th style="width: 12%;">Type</th>
                <th style="width: 12%;">Merk</th>
                <th style="width: 15%;">Harga</th>
                <th style="width: 8%;">Stok</th>
                <th style="width: 13%;">Status Stok</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dataBarang as $index => $barang)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $barang->id }}</td>
                <td>{{ $barang->nm_barang }}</td>
                <td>{{ $barang->type }}</td>
                <td>{{ $barang->merk }}</td>
                <td>{{ $barang->harga_formatted }}</td>
                <td>{{ number_format($barang->jml_brg, 0, ',', '.') }}</td>
                <td>
                    <span class="status-badge status-{{ $barang->status_stok_badge }}">
                        {{ $barang->status_stok }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align: center; padding: 20px;">
                    Tidak ada data barang
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    
    <div class="footer">
        <p>Dicetak pada {{ date('d F Y, H:i') }} | Halaman 1 dari 1</p>
    </div>
    
    <script>
        // Auto print when page loads
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
