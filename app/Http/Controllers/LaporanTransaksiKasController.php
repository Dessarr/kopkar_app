<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\View_Transaksi;
use App\Models\transaksi_kas;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LaporanTransaksiKasController extends Controller
{
    public function index(Request $request)
    {
        $tgl_dari = $request->input('tgl_dari', date('Y') . '-01-01');
        $tgl_samp = $request->input('tgl_samp', date('Y') . '-12-31');
        $jenis_transaksi = $request->input('jenis_transaksi', 'semua');
        $search = $request->input('search');
        $perPage = $request->input('per_page', 15);

        $query = View_Transaksi::query();

        // Filter berdasarkan tanggal
        $query->whereBetween(DB::raw('DATE(tgl)'), [$tgl_dari, $tgl_samp]);

        // Filter berdasarkan jenis transaksi
        if ($jenis_transaksi === 'pemasukan') {
            $query->where('transaksi', '48'); // Pemasukan
        } elseif ($jenis_transaksi === 'pengeluaran') {
            $query->where('transaksi', '7'); // Pengeluaran
        }

        // Search berdasarkan keterangan atau nama kas
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('keterangan', 'like', '%' . $search . '%')
                  ->orWhere('nama_kas', 'like', '%' . $search . '%');
            });
        }

        $dataTransaksi = $query->orderBy('tgl', 'desc')->paginate($perPage);

        // Statistik
        $totalPemasukan = View_Transaksi::where('transaksi', '48')
            ->whereBetween(DB::raw('DATE(tgl)'), [$tgl_dari, $tgl_samp])
            ->sum('kredit');

        $totalPengeluaran = View_Transaksi::where('transaksi', '7')
            ->whereBetween(DB::raw('DATE(tgl)'), [$tgl_dari, $tgl_samp])
            ->sum('debet');

        $saldo = $totalPemasukan - $totalPengeluaran;

        // Data untuk chart (jika diperlukan)
        $chartData = $this->getChartData($tgl_dari, $tgl_samp);

        return view('laporan.transaksi_kas', compact(
            'dataTransaksi',
            'tgl_dari',
            'tgl_samp',
            'jenis_transaksi',
            'search',
            'perPage',
            'totalPemasukan',
            'totalPengeluaran',
            'saldo',
            'chartData'
        ));
    }

    public function exportPdf(Request $request)
    {
        $tgl_dari = $request->input('tgl_dari', date('Y') . '-01-01');
        $tgl_samp = $request->input('tgl_samp', date('Y') . '-12-31');
        $jenis_transaksi = $request->input('jenis_transaksi', 'semua');
        $search = $request->input('search');

        $query = View_Transaksi::query();

        // Filter berdasarkan tanggal
        $query->whereBetween(DB::raw('DATE(tgl)'), [$tgl_dari, $tgl_samp]);

        // Filter berdasarkan jenis transaksi
        if ($jenis_transaksi === 'pemasukan') {
            $query->where('transaksi', '48');
        } elseif ($jenis_transaksi === 'pengeluaran') {
            $query->where('transaksi', '7');
        }

        // Search berdasarkan keterangan atau nama kas
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('keterangan', 'like', '%' . $search . '%')
                  ->orWhere('nama_kas', 'like', '%' . $search . '%');
            });
        }

        $dataTransaksi = $query->orderBy('tgl', 'desc')->get();

        $tgl_periode_txt = Carbon::parse($tgl_dari)->format('d/m/Y') . ' - ' . Carbon::parse($tgl_samp)->format('d/m/Y');

        $pdf = PDF::loadView('laporan.pdf.transaksi_kas', compact('dataTransaksi', 'tgl_periode_txt', 'jenis_transaksi'));

        return $pdf->download('laporan_transaksi_kas_' . date('Ymd') . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        $tgl_dari = $request->input('tgl_dari', date('Y') . '-01-01');
        $tgl_samp = $request->input('tgl_samp', date('Y') . '-12-31');
        $jenis_transaksi = $request->input('jenis_transaksi', 'semua');
        $search = $request->input('search');

        $query = View_Transaksi::query();

        // Filter berdasarkan tanggal
        $query->whereBetween(DB::raw('DATE(tgl)'), [$tgl_dari, $tgl_samp]);

        // Filter berdasarkan jenis transaksi
        if ($jenis_transaksi === 'pemasukan') {
            $query->where('transaksi', '48');
        } elseif ($jenis_transaksi === 'pengeluaran') {
            $query->where('transaksi', '7');
        }

        // Search berdasarkan keterangan atau nama kas
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('keterangan', 'like', '%' . $search . '%')
                  ->orWhere('nama_kas', 'like', '%' . $search . '%');
            });
        }

        $dataTransaksi = $query->orderBy('tgl', 'desc')->get();

        $tgl_periode_txt = Carbon::parse($tgl_dari)->format('d/m/Y') . ' - ' . Carbon::parse($tgl_samp)->format('d/m/Y');

        // Create Excel file
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set title
        $sheet->setCellValue('A1', 'LAPORAN TRANSAKSI KAS');
        $sheet->setCellValue('A2', 'Periode: ' . $tgl_periode_txt);
        $sheet->setCellValue('A3', 'Jenis: ' . ucfirst($jenis_transaksi));
        $sheet->mergeCells('A1:F1');
        $sheet->mergeCells('A2:F2');
        $sheet->mergeCells('A3:F3');

        // Style title
        $sheet->getStyle('A1:A3')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1:A3')->getAlignment()->setHorizontal('center');

        // Header table
        $headers = ['No', 'Tanggal', 'Keterangan', 'Kas', 'Jenis', 'Jumlah'];
        $col = 'A';
        $row = 5;
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $col++;
        }

        // Style header
        $sheet->getStyle('A5:F5')->getFont()->setBold(true);
        $sheet->getStyle('A5:F5')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('CCCCCC');

        // Data transaksi
        $row = 6;
        $no = 1;
        $totalPemasukan = 0;
        $totalPengeluaran = 0;

        foreach ($dataTransaksi as $transaksi) {
            $sheet->setCellValue('A' . $row, $no);
            $sheet->setCellValue('B' . $row, Carbon::parse($transaksi->tgl)->format('d/m/Y'));
            $sheet->setCellValue('C' . $row, $transaksi->keterangan);
            $sheet->setCellValue('D' . $row, $transaksi->nama_kas);
            $sheet->setCellValue('E' . $row, $transaksi->transaksi === '48' ? 'Pemasukan' : 'Pengeluaran');
            $sheet->setCellValue('F' . $row, $transaksi->transaksi === '48' ? $transaksi->kredit : $transaksi->debet);

            if ($transaksi->transaksi === '48') {
                $totalPemasukan += $transaksi->kredit;
            } else {
                $totalPengeluaran += $transaksi->debet;
            }

            $row++;
            $no++;
        }

        // Total row
        $sheet->setCellValue('A' . $row, '');
        $sheet->setCellValue('B' . $row, '');
        $sheet->setCellValue('C' . $row, '');
        $sheet->setCellValue('D' . $row, '');
        $sheet->setCellValue('E' . $row, 'TOTAL PEMASUKAN');
        $sheet->setCellValue('F' . $row, $totalPemasukan);
        $row++;

        $sheet->setCellValue('A' . $row, '');
        $sheet->setCellValue('B' . $row, '');
        $sheet->setCellValue('C' . $row, '');
        $sheet->setCellValue('D' . $row, '');
        $sheet->setCellValue('E' . $row, 'TOTAL PENGELUARAN');
        $sheet->setCellValue('F' . $row, $totalPengeluaran);
        $row++;

        $sheet->setCellValue('A' . $row, '');
        $sheet->setCellValue('B' . $row, '');
        $sheet->setCellValue('C' . $row, '');
        $sheet->setCellValue('D' . $row, '');
        $sheet->setCellValue('E' . $row, 'SALDO');
        $sheet->setCellValue('F' . $row, $totalPemasukan - $totalPengeluaran);

        // Style total rows
        $sheet->getStyle('A' . ($row-2) . ':F' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . ($row-2) . ':F' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('E6E6E6');

        // Auto size columns
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Set number format for currency columns
        $sheet->getStyle('F6:F' . $row)->getNumberFormat()->setFormatCode('#,##0');

        // Create file
        $writer = new Xlsx($spreadsheet);
        $filename = 'laporan_transaksi_kas_' . date('Ymd') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    private function getChartData($tgl_dari, $tgl_samp)
    {
        // Data untuk chart bulanan
        $monthlyData = View_Transaksi::select(
            DB::raw('MONTH(tgl) as bulan'),
            DB::raw('SUM(CASE WHEN transaksi = "48" THEN kredit ELSE 0 END) as pemasukan'),
            DB::raw('SUM(CASE WHEN transaksi = "7" THEN debet ELSE 0 END) as pengeluaran')
        )
        ->whereBetween(DB::raw('DATE(tgl)'), [$tgl_dari, $tgl_samp])
        ->groupBy(DB::raw('MONTH(tgl)'))
        ->orderBy('bulan')
        ->get();

        return $monthlyData;
    }
} 