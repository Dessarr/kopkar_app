<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\data_anggota;
use App\Models\jns_simpan;
use App\Models\TransaksiSimpanan;
use App\Models\TblTransSp;
use App\Models\TblTransTagihan;
use App\Models\TblTransToserda;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LaporanKasAnggotaController extends Controller
{
    public function index(Request $request)
    {
        $periode = $request->input('periode', date('Y-m'));
        $search = $request->input('search');
        $perPage = $request->input('per_page', 15);

        // Parse periode
        $tgl_arr = explode('-', $periode);
        $tahun = $tgl_arr[0];
        $bulan = $tgl_arr[1];

        $query = data_anggota::where('aktif', 'Y');

        // Search berdasarkan nama atau no KTP
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('no_ktp', 'like', '%' . $search . '%')
                  ->orWhere('no_anggota', 'like', '%' . $search . '%');
            });
        }

        $dataAnggota = $query->orderBy('nama', 'asc')->paginate($perPage);

        // Get jenis simpanan yang ditampilkan
        $jenisSimpanan = jns_simpan::where('tampil', 'Y')
            ->whereIn('id', [41, 32, 52, 40, 51, 31])
            ->orderBy('urut', 'asc')
            ->get();

        // Get data kas untuk setiap anggota
        $kasData = [];
        foreach ($dataAnggota as $anggota) {
            $kasData[$anggota->no_ktp] = $this->getKasData($anggota->no_ktp, $tahun, $bulan);
        }

        // Statistik
        $totalAnggota = data_anggota::where('aktif', 'Y')->count();
        $totalSimpanan = $this->getTotalSimpanan($tahun, $bulan);
        $totalPenarikan = $this->getTotalPenarikan($tahun, $bulan);
        $totalSaldo = $totalSimpanan - $totalPenarikan;

        return view('laporan.kas_anggota', compact(
            'dataAnggota',
            'jenisSimpanan',
            'kasData',
            'periode',
            'tahun',
            'bulan',
            'search',
            'perPage',
            'totalAnggota',
            'totalSimpanan',
            'totalPenarikan',
            'totalSaldo'
        ));
    }

    public function exportPdf(Request $request)
    {
        $periode = $request->input('periode', date('Y-m'));
        $search = $request->input('search');

        $tgl_arr = explode('-', $periode);
        $tahun = $tgl_arr[0];
        $bulan = $tgl_arr[1];

        $query = data_anggota::where('aktif', 'Y');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('no_ktp', 'like', '%' . $search . '%')
                  ->orWhere('no_anggota', 'like', '%' . $search . '%');
            });
        }

        $dataAnggota = $query->orderBy('nama', 'asc')->get();
        $jenisSimpanan = jns_simpan::where('tampil', 'Y')
            ->whereIn('id', [41, 32, 52, 40, 51, 31])
            ->orderBy('urut', 'asc')
            ->get();

        $kasData = [];
        foreach ($dataAnggota as $anggota) {
            $kasData[$anggota->no_ktp] = $this->getKasData($anggota->no_ktp, $tahun, $bulan);
        }

        $pdf = PDF::loadView('laporan.pdf.kas_anggota', compact(
            'dataAnggota',
            'jenisSimpanan',
            'kasData',
            'periode',
            'tahun',
            'bulan'
        ));

        return $pdf->download('laporan_kas_anggota_' . date('Ymd') . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        $periode = $request->input('periode', date('Y-m'));
        $search = $request->input('search');

        $tgl_arr = explode('-', $periode);
        $tahun = $tgl_arr[0];
        $bulan = $tgl_arr[1];

        $query = data_anggota::where('aktif', 'Y');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('no_ktp', 'like', '%' . $search . '%')
                  ->orWhere('no_anggota', 'like', '%' . $search . '%');
            });
        }

        $dataAnggota = $query->orderBy('nama', 'asc')->get();
        $jenisSimpanan = jns_simpan::where('tampil', 'Y')
            ->whereIn('id', [41, 32, 52, 40, 51, 31])
            ->orderBy('urut', 'asc')
            ->get();

        $kasData = [];
        foreach ($dataAnggota as $anggota) {
            $kasData[$anggota->no_ktp] = $this->getKasData($anggota->no_ktp, $tahun, $bulan);
        }

        // Create Excel file
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set title
        $sheet->setCellValue('A1', 'LAPORAN KAS ANGGOTA KOPERASI');
        $sheet->setCellValue('A2', 'Periode: ' . Carbon::createFromDate($tahun, $bulan, 1)->format('F Y'));
        $sheet->setCellValue('A3', 'Tanggal: ' . Carbon::now()->format('d/m/Y H:i:s'));

        // Merge cells for title
        $lastCol = chr(65 + count($jenisSimpanan) * 3 + 3); // A + jumlah jenis simpanan * 3 + 3 kolom tambahan
        $sheet->mergeCells('A1:' . $lastCol . '1');
        $sheet->mergeCells('A2:' . $lastCol . '2');
        $sheet->mergeCells('A3:' . $lastCol . '3');

        // Style title
        $sheet->getStyle('A1:A3')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1:A3')->getAlignment()->setHorizontal('center');

        // Header table
        $row = 5;
        $col = 'A';
        
        // Basic headers
        $headers = ['No', 'No Anggota', 'Nama', 'No KTP'];
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $col++;
        }

        // Jenis simpanan headers
        foreach ($jenisSimpanan as $jenis) {
            $sheet->setCellValue($col . $row, $jenis->jns_simpan . ' Setor');
            $col++;
            $sheet->setCellValue($col . $row, $jenis->jns_simpan . ' Tarik');
            $col++;
            $sheet->setCellValue($col . $row, $jenis->jns_simpan . ' Saldo');
            $col++;
        }

        // Additional headers
        $additionalHeaders = ['Total Setor', 'Total Tarik', 'Total Saldo', 'Tagihan', 'Bayar', 'Sisa'];
        foreach ($additionalHeaders as $header) {
            $sheet->setCellValue($col . $row, $header);
            $col++;
        }

        // Style header
        $sheet->getStyle('A5:' . $lastCol . '5')->getFont()->setBold(true);
        $sheet->getStyle('A5:' . $lastCol . '5')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('CCCCCC');

        // Data anggota
        $row = 6;
        $no = 1;
        foreach ($dataAnggota as $anggota) {
            $col = 'A';
            
            // Basic data
            $sheet->setCellValue($col . $row, $no);
            $col++;
            $sheet->setCellValue($col . $row, $anggota->no_anggota);
            $col++;
            $sheet->setCellValue($col . $row, $anggota->nama);
            $col++;
            $sheet->setCellValue($col . $row, $anggota->no_ktp);
            $col++;

            $kas = $kasData[$anggota->no_ktp] ?? [];

            // Jenis simpanan data
            foreach ($jenisSimpanan as $jenis) {
                $setor = $kas['setoran'][$jenis->id] ?? 0;
                $tarik = $kas['penarikan'][$jenis->id] ?? 0;
                $saldo = $setor - $tarik;

                $sheet->setCellValue($col . $row, $setor);
                $col++;
                $sheet->setCellValue($col . $row, $tarik);
                $col++;
                $sheet->setCellValue($col . $row, $saldo);
                $col++;
            }

            // Additional data
            $totalSetor = $kas['total_setor'] ?? 0;
            $totalTarik = $kas['total_tarik'] ?? 0;
            $totalSaldo = $kas['total_saldo'] ?? 0;
            $tagihan = $kas['tagihan'] ?? 0;
            $bayar = $kas['bayar'] ?? 0;
            $sisa = $kas['sisa'] ?? 0;

            $sheet->setCellValue($col . $row, $totalSetor);
            $col++;
            $sheet->setCellValue($col . $row, $totalTarik);
            $col++;
            $sheet->setCellValue($col . $row, $totalSaldo);
            $col++;
            $sheet->setCellValue($col . $row, $tagihan);
            $col++;
            $sheet->setCellValue($col . $row, $bayar);
            $col++;
            $sheet->setCellValue($col . $row, $sisa);

            $row++;
            $no++;
        }

        // Auto size columns
        foreach (range('A', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Set number format for currency columns
        $sheet->getStyle('E6:' . $lastCol . ($row - 1))->getNumberFormat()->setFormatCode('#,##0');

        // Create file
        $writer = new Xlsx($spreadsheet);
        $filename = 'laporan_kas_anggota_' . date('Ymd') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    private function getKasData($noKtp, $tahun, $bulan)
    {
        $data = [
            'setoran' => [],
            'penarikan' => [],
            'total_setor' => 0,
            'total_tarik' => 0,
            'total_saldo' => 0,
            'tagihan' => 0,
            'bayar' => 0,
            'sisa' => 0
        ];

        // Get jenis simpanan
        $jenisSimpanan = jns_simpan::where('tampil', 'Y')
            ->whereIn('id', [41, 32, 52, 40, 51, 31])
            ->orderBy('urut', 'asc')
            ->get();

        foreach ($jenisSimpanan as $jenis) {
            // Get setoran
            $setoran = TransaksiSimpanan::where('no_ktp', $noKtp)
                ->where('jenis_id', $jenis->id)
                ->where('akun', 'setoran')
                ->whereYear('tgl_transaksi', $tahun)
                ->whereMonth('tgl_transaksi', $bulan)
                ->sum('jumlah');

            // Get penarikan
            $penarikan = TransaksiSimpanan::where('no_ktp', $noKtp)
                ->where('jenis_id', $jenis->id)
                ->where('akun', 'penarikan')
                ->whereYear('tgl_transaksi', $tahun)
                ->whereMonth('tgl_transaksi', $bulan)
                ->sum('jumlah');

            $data['setoran'][$jenis->id] = $setoran;
            $data['penarikan'][$jenis->id] = $penarikan;
            $data['total_setor'] += $setoran;
            $data['total_tarik'] += $penarikan;
        }

        $data['total_saldo'] = $data['total_setor'] - $data['total_tarik'];

        // Get tagihan dan bayar
        $tagihan = TblTransTagihan::where('no_ktp', $noKtp)
            ->whereYear('tgl_transaksi', $tahun)
            ->whereMonth('tgl_transaksi', $bulan)
            ->sum('jumlah');

        $bayar = TblTransSp::where('no_ktp', $noKtp)
            ->whereYear('tgl_transaksi', $tahun)
            ->whereMonth('tgl_transaksi', $bulan)
            ->sum('jumlah');

        $data['tagihan'] = $tagihan;
        $data['bayar'] = $bayar;
        $data['sisa'] = $tagihan - $bayar;

        return $data;
    }

    private function getTotalSimpanan($tahun, $bulan)
    {
        return TransaksiSimpanan::where('akun', 'setoran')
            ->whereYear('tgl_transaksi', $tahun)
            ->whereMonth('tgl_transaksi', $bulan)
            ->sum('jumlah');
    }

    private function getTotalPenarikan($tahun, $bulan)
    {
        return TransaksiSimpanan::where('akun', 'penarikan')
            ->whereYear('tgl_transaksi', $tahun)
            ->whereMonth('tgl_transaksi', $bulan)
            ->sum('jumlah');
    }
} 