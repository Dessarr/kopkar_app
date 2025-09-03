<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Kredit Macet</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
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
            font-size: 18px;
            font-weight: bold;
            margin: 0 0 10px 0;
            color: #333;
        }
        
        .header h2 {
            font-size: 16px;
            font-weight: bold;
            margin: 0 0 5px 0;
            color: #666;
        }
        
        .header p {
            font-size: 12px;
            margin: 0;
            color: #666;
        }
        
        .info {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
        }
        
        .info p {
            margin: 0;
            font-size: 11px;
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
            vertical-align: top;
        }
        
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            font-size: 11px;
            text-align: center;
        }
        
        td {
            font-size: 10px;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-red {
            color: #dc3545;
            font-weight: bold;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        
        .footer th {
            background-color: #e9ecef;
            font-weight: bold;
        }
        
        .footer td {
            font-weight: bold;
        }
        
        .footer .text-red {
            color: #dc3545;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 15px;
            }
            
            .header {
                margin-bottom: 20px;
            }
            
            table {
                margin-bottom: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>KOPERASI KARYAWAN</h1>
        <h2>Laporan Kredit Macet</h2>
        <p>Periode: {{ $periodeText }}</p>
        <p>Tanggal Cetak: {{ \Carbon\Carbon::now()->format('d F Y H:i:s') }}</p>
    </div>

    <!-- Summary Info -->
    <div class="info">
        <p><strong>Keterangan:</strong> Laporan ini menampilkan data pinjaman yang telah melewati jatuh tempo pembayaran (kredit macet) berdasarkan periode yang dipilih.</p>
        <p><strong>Total Data:</strong> {{ count($dataPinjaman) }} kredit macet | <strong>Total Tagihan:</strong> Rp {{ number_format($totalTagihan) }} | <strong>Total Sisa:</strong> Rp {{ number_format($totalSisa) }}</p>
    </div>

    <!-- Data Table -->
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No.</th>
                <th style="width: 12%;">Kode Pinjam</th>
                <th style="width: 25%;">Nama Anggota</th>
                <th style="width: 12%;">Tanggal Pinjam</th>
                <th style="width: 12%;">Tanggal Tempo</th>
                <th style="width: 10%;">Lama Pinjam</th>
                <th style="width: 12%;">Jumlah Tagihan</th>
                <th style="width: 10%;">Dibayar</th>
                <th style="width: 12%;">Sisa Tagihan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dataPinjaman as $index => $pinjaman)
            @php
                // Calculate total tagihan (tagihan + denda)
                $totalTagihan = $pinjaman->tagihan + $pinjaman->denda_rp;
                
                // Get total payment from tbl_pinjaman_d
                $totalBayar = \App\Models\TblPinjamanD::where('pinjam_id', $pinjaman->id)
                    ->sum('jumlah_bayar');
                
                $sisaTagihan = $totalTagihan - $totalBayar;
                
                // Format kode pinjam as TPJ + 5 digits
                $kodePinjam = 'TPJ' . str_pad($pinjaman->id, 5, '0', STR_PAD_LEFT);
                
                // Calculate days overdue
                $daysOverdue = \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($pinjaman->tempo), false);
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $kodePinjam }}</td>
                <td>{{ $pinjaman->no_ktp }} - {{ $pinjaman->nama_anggota }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($pinjaman->tgl_pinjam)->format('d/m/Y') }}</td>
                <td class="text-center text-red">
                    {{ \Carbon\Carbon::parse($pinjaman->tempo)->format('d/m/Y') }}
                    @if($daysOverdue > 0)
                    <br><small>({{ $daysOverdue }} hari terlambat)</small>
                    @endif
                </td>
                <td class="text-center">{{ $pinjaman->lama_angsuran }} bln</td>
                <td class="text-right">Rp {{ number_format($totalTagihan) }}</td>
                <td class="text-right">Rp {{ number_format($totalBayar) }}</td>
                <td class="text-right text-red">Rp {{ number_format($sisaTagihan) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center" style="padding: 20px;">
                    Tidak ada data kredit macet untuk periode yang dipilih
                </td>
            </tr>
            @endforelse
        </tbody>
        <tfoot class="footer">
            <tr>
                <td colspan="6" class="text-right"><strong>Jumlah Total:</strong></td>
                <td class="text-right"><strong>Rp {{ number_format($totalTagihan) }}</strong></td>
                <td class="text-right"><strong>Rp {{ number_format($totalDibayar) }}</strong></td>
                <td class="text-right text-red"><strong>Rp {{ number_format($totalSisa) }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <!-- Summary -->
    <div style="margin-top: 30px; padding: 15px; background-color: #f8f9fa; border-left: 4px solid #dc3545;">
        <h3 style="margin: 0 0 10px 0; font-size: 14px; color: #dc3545;">Ringkasan Laporan Kredit Macet</h3>
        <p style="margin: 0; font-size: 11px;"><strong>Total Jumlah Tagihan:</strong> Rp {{ number_format($totalTagihan) }}</p>
        <p style="margin: 0; font-size: 11px;"><strong>Total Dibayar:</strong> Rp {{ number_format($totalDibayar) }}</p>
        <p style="margin: 0; font-size: 11px; color: #dc3545;"><strong>Total Sisa Tagihan:</strong> Rp {{ number_format($totalSisa) }}</p>
        <p style="margin: 5px 0 0 0; font-size: 10px; color: #666;">
            <em>Laporan ini menunjukkan pinjaman yang telah melewati jatuh tempo pembayaran dan memerlukan tindakan penanganan khusus.</em>
        </p>
    </div>

    <!-- Footer -->
    <div style="margin-top: 40px; text-align: center; font-size: 10px; color: #666;">
        <p>Laporan ini dibuat secara otomatis oleh sistem pada {{ \Carbon\Carbon::now()->format('d F Y H:i:s') }}</p>
        <p>Koperasi Karyawan - Laporan Kredit Macet</p>
    </div>
</body>
</html>
