<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Toserda Lain-lain</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
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
            margin: 0;
            color: #333;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .summary {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .summary table {
            width: 100%;
            border-collapse: collapse;
        }
        .summary td {
            padding: 5px;
            border: none;
        }
        .summary .label {
            font-weight: bold;
            width: 150px;
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
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .text-bold {
            font-weight: bold;
        }
        .total-row {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN TOSERDA LAIN-LAIN</h1>
        <p>Koperasi Karyawan</p>
        @php
            $bulanNama = request('bulan') ? [
                '01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni',
                '07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'
            ][request('bulan')] ?? request('bulan') : 'Semua';
        @endphp
        <p>Periode: {{ $bulanNama }} {{ request('tahun', date('Y')) }}</p>
        <p>Tanggal Cetak: {{ date('d/m/Y H:i') }}</p>
    </div>

    <div class="summary">
        <table>
            <tr>
                <td class="label">Total Transaksi:</td>
                <td>{{ count($data) }} transaksi</td>
            </tr>
            <tr>
                <td class="label">Total Nilai:</td>
                <td class="text-bold">Rp{{ number_format($totalNilai ?? 0, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 5%">No</th>
                <th class="text-center" style="width: 12%">Tanggal</th>
                <th style="width: 15%">No KTP</th>
                <th style="width: 25%">Nama Anggota</th>
                <th class="text-right" style="width: 15%">Jumlah</th>
                <th class="text-center" style="width: 15%">Status Billing</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $tr)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">
                    @php
                        $tgl = $tr->tgl_transaksi ?? null;
                        if (is_object($tgl)) {
                            $tgl = $tgl->format('d/m/Y');
                        } elseif ($tgl) {
                            $tgl = \Carbon\Carbon::parse($tgl)->format('d/m/Y');
                        } else {
                            $tgl = '-';
                        }
                    @endphp
                    {{ $tgl }}
                </td>
                <td>{{ $tr->no_ktp ?? '-' }}</td>
                <td>{{ $tr->nama_anggota ?? '-' }}</td>
                <td class="text-right text-bold">
                    Rp{{ number_format($tr->jumlah ?? 0, 0, ',', '.') }}
                </td>
                <td class="text-center">
                    {{ (isset($tr->status_billing) && $tr->status_billing === 'Y') ? 'Sudah Billing' : 'Belum Billing' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center" style="padding: 20px;">
                    Tidak ada data transaksi untuk periode yang dipilih
                </td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="4" class="text-right text-bold">TOTAL NILAI:</td>
                <td class="text-right text-bold">Rp{{ number_format($totalNilai ?? 0, 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Dicetak pada: {{ date('d/m/Y H:i:s') }}</p>
        <p>Oleh: {{ auth()->user()->name ?? 'System' }}</p>
    </div>
</body>
</html>
