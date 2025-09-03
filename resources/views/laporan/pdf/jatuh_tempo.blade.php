<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Jatuh Tempo Pembayaran Kredit</title>
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
            border-bottom: 2px solid #3B82F6;
            padding-bottom: 20px;
        }
        
        .header h1 {
            font-size: 18px;
            font-weight: bold;
            color: #3B82F6;
            margin: 0 0 10px 0;
        }
        
        .header p {
            font-size: 14px;
            color: #666;
            margin: 0;
        }
        
        .info-section {
            margin-bottom: 20px;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .info-label {
            font-weight: bold;
            color: #555;
        }
        
        .info-value {
            color: #333;
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
            background-color: #3B82F6;
            color: white;
            font-weight: bold;
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .total-row {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        
        .overdue {
            color: #dc3545;
            font-weight: bold;
        }
        
        .current {
            color: #28a745;
            font-weight: bold;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
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
        <h1>LAPORAN JATUH TEMPO PEMBAYARAN KREDIT</h1>
        <p>Periode: {{ $periodeText }}</p>
        <p>Tanggal Cetak: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <!-- Info Section -->
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Total Data:</span>
            <span class="info-value">{{ $dataPinjaman->count() }} pinjaman</span>
        </div>
        <div class="info-row">
            <span class="info-label">Total Tagihan:</span>
            <span class="info-value">Rp {{ number_format($totalTagihan) }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Total Dibayar:</span>
            <span class="info-value">Rp {{ number_format($totalDibayar) }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Total Sisa Tagihan:</span>
            <span class="info-value">Rp {{ number_format($totalSisa) }}</span>
        </div>
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
                <th style="width: 12%;">Dibayar</th>
                <th style="width: 12%;">Sisa Tagihan</th>
            </tr>
        </thead>
        <tbody>
                         @forelse($dataPinjaman as $index => $pinjaman)
             @php
                 // Calculate tagihan (angsuran pokok + bunga + biaya admin)
                 $angsuranPokok = $pinjaman->jumlah / $pinjaman->lama_angsuran;
                 $angsuranBunga = $pinjaman->bunga_rp / $pinjaman->lama_angsuran;
                 $totalTagihan = $angsuranPokok + $angsuranBunga + $pinjaman->biaya_adm;
                 
                 $totalBayar = \App\Models\TblPinjamanD::where('pinjam_id', $pinjaman->id)->sum('jumlah_bayar');
                 $sisaTagihan = $totalTagihan - $totalBayar;
                 $kodePinjam = 'TPJ' . str_pad($pinjaman->id, 5, '0', STR_PAD_LEFT);
                 $isOverdue = \Carbon\Carbon::parse($pinjaman->tempo)->isPast();
             @endphp
             <tr>
                 <td class="text-center">{{ $index + 1 }}</td>
                 <td>{{ $kodePinjam }}</td>
                 <td>{{ $pinjaman->no_ktp }} - {{ $pinjaman->nama_anggota }}</td>
                 <td class="text-center">{{ \Carbon\Carbon::parse($pinjaman->tgl_pinjam)->format('d/m/Y') }}</td>
                 <td class="text-center {{ $isOverdue ? 'overdue' : 'current' }}">
                     {{ \Carbon\Carbon::parse($pinjaman->tempo)->format('d/m/Y') }}
                 </td>
                 <td class="text-center">{{ $pinjaman->lama_angsuran }} bulan</td>
                 <td class="text-right">Rp {{ number_format($totalTagihan) }}</td>
                 <td class="text-right">Rp {{ number_format($totalBayar) }}</td>
                 <td class="text-right {{ $sisaTagihan > 0 ? 'overdue' : 'current' }}">
                     Rp {{ number_format($sisaTagihan) }}
                 </td>
             </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center" style="padding: 20px;">
                    Tidak ada data jatuh tempo untuk periode yang dipilih
                </td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="6" class="text-right"><strong>Jumlah Total:</strong></td>
                <td class="text-right"><strong>Rp {{ number_format($totalTagihan) }}</strong></td>
                <td class="text-right"><strong>Rp {{ number_format($totalDibayar) }}</strong></td>
                <td class="text-right"><strong>Rp {{ number_format($totalSisa) }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <!-- Legend -->
    <div style="margin-top: 20px; padding: 15px; background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px;">
        <h4 style="margin: 0 0 10px 0; color: #856404; font-size: 14px;">Keterangan:</h4>
        <div style="font-size: 11px; color: #856404;">
            <p style="margin: 2px 0;"><strong>Kode Pinjam:</strong> Format TPJ + 5 digit angka</p>
            <p style="margin: 2px 0;"><strong>Jumlah Tagihan:</strong> Tagihan pokok + denda</p>
            <p style="margin: 2px 0;"><strong>Sisa Tagihan:</strong> Jumlah tagihan - jumlah dibayar</p>
            <p style="margin: 2px 0;"><strong>Warna Merah:</strong> Pinjaman yang sudah jatuh tempo atau ada sisa tagihan</p>
            <p style="margin: 2px 0;"><strong>Warna Hijau:</strong> Pinjaman yang belum jatuh tempo atau sudah lunas</p>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Laporan ini dibuat secara otomatis pada {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
        <p>Koperasi Karyawan - Sistem Informasi Manajemen</p>
    </div>
</body>
</html>
