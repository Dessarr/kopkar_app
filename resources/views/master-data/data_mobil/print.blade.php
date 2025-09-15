<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Mobil - Print</title>
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
        
        .status-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        
        .status-aktif {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-nonaktif {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .status-kadaluarsa {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .status-akan-kadaluarsa {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-masih-berlaku {
            background-color: #d4edda;
            color: #155724;
        }
        
        .footer {
            margin-top: 30px;
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
        <h1>Data Mobil</h1>
        <p>Master Data Mobil - Koperasi Karyawan</p>
    </div>
    
    <div class="print-info">
        <div>
            <strong>Tanggal Cetak:</strong> {{ date('d F Y, H:i') }}
        </div>
        <div>
            <strong>Total Data:</strong> {{ $dataMobil->count() }} mobil
        </div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 10%;">ID</th>
                <th style="width: 20%;">Nama Mobil</th>
                <th style="width: 12%;">Jenis</th>
                <th style="width: 12%;">Merek</th>
                <th style="width: 8%;">Tahun</th>
                <th style="width: 12%;">No Polisi</th>
                <th style="width: 10%;">Status Aktif</th>
                <th style="width: 11%;">Status STNK</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dataMobil as $index => $mobil)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $mobil->id }}</td>
                <td>{{ $mobil->nama }}</td>
                <td>{{ $mobil->jenis ?? '-' }}</td>
                <td>{{ $mobil->merek ?? '-' }}</td>
                <td>{{ $mobil->tahun_formatted }}</td>
                <td>{{ $mobil->no_polisi ?? '-' }}</td>
                <td>
                    <span class="status-badge status-{{ $mobil->status_aktif_badge }}">
                        {{ $mobil->status_aktif_text }}
                    </span>
                </td>
                <td>
                    <span class="status-badge status-{{ $mobil->status_stnk_badge }}">
                        {{ $mobil->status_stnk }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align: center; padding: 20px;">
                    Tidak ada data mobil
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
