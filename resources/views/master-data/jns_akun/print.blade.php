<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Jenis Akun - Print</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            background: white;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #14AE5C;
        }

        .header h1 {
            font-size: 24px;
            font-weight: bold;
            color: #14AE5C;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 14px;
            color: #666;
        }

        .print-info {
            text-align: right;
            margin-bottom: 20px;
            font-size: 10px;
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
            background-color: #14AE5C;
            color: white;
            font-weight: bold;
            text-align: center;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        .status-aktif {
            background-color: #d4edda;
            color: #155724;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
        }

        .status-tidak-aktif {
            background-color: #f8d7da;
            color: #721c24;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
        }

        .badge-ya {
            background-color: #d4edda;
            color: #155724;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
        }

        .badge-tidak {
            background-color: #f8d7da;
            color: #721c24;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
        }

        .badge-akun {
            background-color: #cce5ff;
            color: #004085;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
        }

        .summary {
            margin-top: 30px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }

        .summary h3 {
            color: #14AE5C;
            margin-bottom: 15px;
            font-size: 16px;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding: 5px 0;
            border-bottom: 1px solid #dee2e6;
        }

        .summary-label {
            font-weight: bold;
            color: #495057;
        }

        .summary-value {
            color: #14AE5C;
            font-weight: bold;
        }

        @media print {
            body {
                margin: 0;
                padding: 20px;
            }
            
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Data Jenis Akun</h1>
        <p>Sistem Informasi Koperasi</p>
    </div>

    <div class="print-info">
        Dicetak pada: {{ date('d/m/Y H:i:s') }}
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 12%;">Kode Aktiva</th>
                <th style="width: 25%;">Jenis Transaksi</th>
                <th style="width: 12%;">Akun</th>
                <th style="width: 12%;">Laba Rugi</th>
                <th style="width: 10%;">Pemasukan</th>
                <th style="width: 10%;">Pengeluaran</th>
                <th style="width: 10%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dataAkun as $index => $akun)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td style="text-align: center; font-family: monospace;">{{ $akun->kd_aktiva }}</td>
                <td>{{ $akun->jns_trans }}</td>
                <td style="text-align: center;">
                    <span class="badge-akun">{{ $akun->akun }}</span>
                </td>
                <td style="text-align: center;">{{ $akun->laba_rugi ?? '-' }}</td>
                <td style="text-align: center;">
                    <span class="{{ $akun->pemasukan === 'Y' ? 'badge-ya' : 'badge-tidak' }}">
                        {{ $akun->pemasukan === 'Y' ? 'Ya' : 'Tidak' }}
                    </span>
                </td>
                <td style="text-align: center;">
                    <span class="{{ $akun->pengeluaran === 'Y' ? 'badge-ya' : 'badge-tidak' }}">
                        {{ $akun->pengeluaran === 'Y' ? 'Ya' : 'Tidak' }}
                    </span>
                </td>
                <td style="text-align: center;">
                    <span class="{{ $akun->aktif === 'Y' ? 'status-aktif' : 'status-tidak-aktif' }}">
                        {{ $akun->aktif === 'Y' ? 'Aktif' : 'Tidak Aktif' }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align: center; padding: 20px;">
                    Tidak ada data jenis akun
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary">
        <h3>Ringkasan Data</h3>
        <div class="summary-item">
            <span class="summary-label">Total Jenis Akun:</span>
            <span class="summary-value">{{ $dataAkun->count() }}</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">Akun Aktif:</span>
            <span class="summary-value">{{ $dataAkun->where('aktif', 'Y')->count() }}</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">Akun Tidak Aktif:</span>
            <span class="summary-value">{{ $dataAkun->where('aktif', 'N')->count() }}</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">Total Tipe Akun:</span>
            <span class="summary-value">{{ $dataAkun->pluck('akun')->unique()->count() }}</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">Akun Pemasukan:</span>
            <span class="summary-value">{{ $dataAkun->where('pemasukan', 'Y')->count() }}</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">Akun Pengeluaran:</span>
            <span class="summary-value">{{ $dataAkun->where('pengeluaran', 'Y')->count() }}</span>
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
