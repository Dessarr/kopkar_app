<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Kas - {{ date('d/m/Y') }}</title>
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
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #333;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0 0 0;
            color: #666;
            font-size: 14px;
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
            text-align: center;
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
        .fitur-aktif {
            background-color: #d1ecf1;
            color: #0c5460;
            padding: 1px 4px;
            border-radius: 2px;
            font-size: 9px;
            margin: 1px;
            display: inline-block;
        }
        .kategori-komprehensif {
            background-color: #e2e3f1;
            color: #4a4a8a;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
        }
        .kategori-menengah {
            background-color: #cce5ff;
            color: #004080;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
        }
        .kategori-dasar {
            background-color: #fff3cd;
            color: #856404;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
        }
        .kategori-minimal {
            background-color: #f8f9fa;
            color: #6c757d;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Data Kas Koperasi</h1>
        <p>Tanggal Cetak: {{ date('d/m/Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 20%;">Nama Kas</th>
                <th style="width: 10%;">Status</th>
                <th style="width: 35%;">Fitur</th>
                <th style="width: 15%;">Kategori</th>
                <th style="width: 15%;">Total Fitur</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dataKas as $kas)
            <tr>
                <td style="text-align: center;">{{ $loop->iteration }}</td>
                <td>{{ $kas->nama }}</td>
                <td style="text-align: center;">
                    @if($kas->aktif === 'Y')
                        <span class="status-aktif">Aktif</span>
                    @else
                        <span class="status-tidak-aktif">Tidak Aktif</span>
                    @endif
                </td>
                <td>
                    @if($kas->tmpl_simpan === 'Y')
                        <span class="fitur-aktif">Simpanan</span>
                    @endif
                    @if($kas->tmpl_penarikan === 'Y')
                        <span class="fitur-aktif">Penarikan</span>
                    @endif
                    @if($kas->tmpl_pinjaman === 'Y')
                        <span class="fitur-aktif">Pinjaman</span>
                    @endif
                    @if($kas->tmpl_bayar === 'Y')
                        <span class="fitur-aktif">Bayar</span>
                    @endif
                    @if($kas->tmpl_pemasukan === 'Y')
                        <span class="fitur-aktif">Pemasukan</span>
                    @endif
                    @if($kas->tmpl_pengeluaran === 'Y')
                        <span class="fitur-aktif">Pengeluaran</span>
                    @endif
                    @if($kas->tmpl_transfer === 'Y')
                        <span class="fitur-aktif">Transfer</span>
                    @endif
                </td>
                <td style="text-align: center;">
                    @php
                        $kategori = $kas->kategori_kas;
                    @endphp
                    @if($kategori === 'Komprehensif')
                        <span class="kategori-komprehensif">Komprehensif</span>
                    @elseif($kategori === 'Menengah')
                        <span class="kategori-menengah">Menengah</span>
                    @elseif($kategori === 'Dasar')
                        <span class="kategori-dasar">Dasar</span>
                    @else
                        <span class="kategori-minimal">Minimal</span>
                    @endif
                </td>
                <td style="text-align: center;">{{ $kas->total_fitur_aktif }}/7</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; padding: 20px; color: #666;">
                    Tidak ada data
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak pada: {{ date('d/m/Y H:i:s') }} | Total Data: {{ $dataKas->count() }}</p>
    </div>

    <script>
        // Auto print when page loads
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
