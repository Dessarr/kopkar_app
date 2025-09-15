<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Jenis Angsuran - {{ date('d/m/Y') }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        
        .print-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #14AE5C;
            padding-bottom: 20px;
        }
        
        .header h1 {
            color: #14AE5C;
            margin: 0;
            font-size: 28px;
            font-weight: bold;
        }
        
        .header p {
            color: #666;
            margin: 5px 0 0 0;
            font-size: 14px;
        }
        
        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 25px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 8px;
        }
        
        .info-item {
            text-align: center;
        }
        
        .info-item .label {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .info-item .value {
            font-size: 18px;
            font-weight: bold;
            color: #14AE5C;
        }
        
        .table-container {
            margin-top: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        th {
            background-color: #14AE5C;
            color: white;
            padding: 12px 8px;
            text-align: center;
            font-weight: bold;
            font-size: 12px;
        }
        
        td {
            padding: 10px 8px;
            text-align: center;
            border-bottom: 1px solid #e0e0e0;
            font-size: 11px;
        }
        
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        tr:hover {
            background-color: #e8f5e8;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
        }
        
        .status-aktif {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-tidak-aktif {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .kategori-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
        }
        
        .kategori-pendek {
            background-color: #e3f2fd;
            color: #1565c0;
        }
        
        .kategori-menengah {
            background-color: #fff3e0;
            color: #ef6c00;
        }
        
        .kategori-panjang {
            background-color: #f3e5f5;
            color: #7b1fa2;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            color: #666;
            font-size: 12px;
            border-top: 1px solid #e0e0e0;
            padding-top: 15px;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .no-data i {
            font-size: 48px;
            color: #ddd;
            margin-bottom: 15px;
        }
        
        @media print {
            body {
                background-color: white;
                padding: 0;
            }
            
            .print-container {
                box-shadow: none;
                border-radius: 0;
                padding: 0;
            }
            
            .no-print {
                display: none;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="print-container">
        <!-- Header -->
        <div class="header">
            <h1><i class="fas fa-calendar-alt"></i> Laporan Jenis Angsuran</h1>
            <p>Koperasi Karyawan - {{ date('d F Y') }}</p>
        </div>

        <!-- Info Section -->
        <div class="info-section">
            <div class="info-item">
                <div class="label">Total Data</div>
                <div class="value">{{ $jnsAngsuran->count() }}</div>
            </div>
            <div class="info-item">
                <div class="label">Aktif</div>
                <div class="value">{{ $jnsAngsuran->where('aktif', 'Y')->count() }}</div>
            </div>
            <div class="info-item">
                <div class="label">Tidak Aktif</div>
                <div class="value">{{ $jnsAngsuran->where('aktif', 'T')->count() }}</div>
            </div>
            <div class="info-item">
                <div class="label">Jangka Pendek</div>
                <div class="value">{{ $jnsAngsuran->where('ket', '<=', 6)->count() }}</div>
            </div>
        </div>

        <!-- Table -->
        <div class="table-container">
            @if($jnsAngsuran->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th style="width: 5%;">#</th>
                            <th style="width: 10%;">ID</th>
                            <th style="width: 15%;">Jumlah Bulan</th>
                            <th style="width: 20%;">Kategori</th>
                            <th style="width: 15%;">Status Aktif</th>
                            <th style="width: 20%;">Keterangan</th>
                            <th style="width: 15%;">Tanggal Dibuat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($jnsAngsuran as $index => $angsuran)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $angsuran->id }}</td>
                            <td>
                                <strong>{{ $angsuran->ket }} Bulan</strong>
                            </td>
                            <td>
                                <span class="kategori-badge kategori-{{ $angsuran->kategori_angsuran_badge }}">
                                    {{ $angsuran->kategori_angsuran }}
                                </span>
                            </td>
                            <td>
                                <span class="status-badge status-{{ $angsuran->status_aktif_badge }}">
                                    {{ $angsuran->status_aktif_text }}
                                </span>
                            </td>
                            <td>{{ $angsuran->ket_formatted }}</td>
                            <td>-</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="no-data">
                    <i class="fas fa-calendar-alt"></i>
                    <p>Tidak ada data jenis angsuran</p>
                </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Dicetak pada: {{ date('d F Y H:i:s') }} | Halaman 1</p>
        </div>
    </div>

    <script>
        // Auto print when page loads
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 1000);
        }
    </script>
</body>
</html>
