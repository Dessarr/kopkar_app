<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TblPinjamanH;
use App\Models\TblPinjamanD;
use App\Models\data_anggota;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LaporanKreditMacetController extends Controller
{
    public function index(Request $request)
    {
        $hari_macet = $request->input('hari_macet', 90); // Default 90 hari
        $search = $request->input('search');
        $perPage = $request->input('per_page', 15);

        // Hitung tanggal batas untuk kredit macet
        $tgl_batas = Carbon::now()->subDays($hari_macet)->format('Y-m-d');

        $query = TblPinjamanH::with(['anggota', 'detail'])
            ->where('status', 'BL') // Belum lunas
            ->whereRaw('DATE_ADD(tgl_pinjam, INTERVAL lama_angsuran MONTH) <= ?', [$tgl_batas]);

        // Search berdasarkan nama anggota atau no pinjaman
        if ($search) {
            $query->whereHas('anggota', function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('no_ktp', 'like', '%' . $search . '%');
            })->orWhere('no_pinjaman', 'like', '%' . $search . '%');
        }

        $dataKreditMacet = $query->orderByRaw('DATE_ADD(tgl_pinjam, INTERVAL lama_angsuran MONTH) ASC')->paginate($perPage);

        // Hitung total
        $totalPinjaman = $query->sum('jumlah');
        $totalAngsuran = $query->sum('jumlah_angsuran');
        $totalSisa = $totalPinjaman - $totalAngsuran;

        // Statistik berdasarkan lama macet
        $macet30 = TblPinjamanH::where('status', 'BL')
            ->whereRaw('DATE_ADD(tgl_pinjam, INTERVAL lama_angsuran MONTH) <= ?', [Carbon::now()->subDays(30)->format('Y-m-d')])
            ->count();
        $macet60 = TblPinjamanH::where('status', 'BL')
            ->whereRaw('DATE_ADD(tgl_pinjam, INTERVAL lama_angsuran MONTH) <= ?', [Carbon::now()->subDays(60)->format('Y-m-d')])
            ->count();
        $macet90 = TblPinjamanH::where('status', 'BL')
            ->whereRaw('DATE_ADD(tgl_pinjam, INTERVAL lama_angsuran MONTH) <= ?', [Carbon::now()->subDays(90)->format('Y-m-d')])
            ->count();

        return view('laporan.kredit_macet', compact(
            'dataKreditMacet',
            'hari_macet',
            'tgl_batas',
            'search',
            'perPage',
            'totalPinjaman',
            'totalAngsuran',
            'totalSisa',
            'macet30',
            'macet60',
            'macet90'
        ));
    }

    public function exportPdf(Request $request)
    {
        $hari_macet = $request->input('hari_macet', 90);
        $search = $request->input('search');

        $tgl_batas = Carbon::now()->subDays($hari_macet)->format('Y-m-d');

        $query = TblPinjamanH::with(['anggota', 'detail'])
            ->where('status', 'BL')
            ->whereRaw('DATE_ADD(tgl_pinjam, INTERVAL lama_angsuran MONTH) <= ?', [$tgl_batas]);

        if ($search) {
            $query->whereHas('anggota', function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('no_ktp', 'like', '%' . $search . '%');
            })->orWhere('no_pinjaman', 'like', '%' . $search . '%');
        }

        $dataKreditMacet = $query->orderByRaw('DATE_ADD(tgl_pinjam, INTERVAL lama_angsuran MONTH) ASC')->get();

        $pdf = PDF::loadView('laporan.pdf.kredit_macet', compact('dataKreditMacet', 'hari_macet', 'tgl_batas'));

        return $pdf->download('laporan_kredit_macet_' . date('Ymd') . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        $hari_macet = $request->input('hari_macet', 90);
        $search = $request->input('search');

        $tgl_batas = Carbon::now()->subDays($hari_macet)->format('Y-m-d');

        $query = TblPinjamanH::with(['anggota', 'detail'])
            ->where('status', 'BL')
            ->whereRaw('DATE_ADD(tgl_pinjam, INTERVAL lama_angsuran MONTH) <= ?', [$tgl_batas]);

        if ($search) {
            $query->whereHas('anggota', function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('no_ktp', 'like', '%' . $search . '%');
            })->orWhere('no_pinjaman', 'like', '%' . $search . '%');
        }

        $dataKreditMacet = $query->orderByRaw('DATE_ADD(tgl_pinjam, INTERVAL lama_angsuran MONTH) ASC')->get();

        // Create Excel file
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set title
        $sheet->setCellValue('A1', 'LAPORAN KREDIT MACET');
        $sheet->setCellValue('A2', 'Kriteria: ' . $hari_macet . ' hari atau lebih');
        $sheet->setCellValue('A3', 'Tanggal Batas: ' . Carbon::parse($tgl_batas)->format('d/m/Y'));
        $sheet->setCellValue('A4', 'Tanggal: ' . Carbon::now()->format('d/m/Y H:i:s'));
        $sheet->mergeCells('A1:J1');
        $sheet->mergeCells('A2:J2');
        $sheet->mergeCells('A3:J3');
        $sheet->mergeCells('A4:J4');

        // Style title
        $sheet->getStyle('A1:A4')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1:A4')->getAlignment()->setHorizontal('center');

        // Header table
        $headers = ['No', 'No Pinjaman', 'Nama Anggota', 'No KTP', 'Tanggal Pinjam', 'Jatuh Tempo', 'Lama Macet (Hari)', 'Jumlah Pinjaman', 'Jumlah Angsuran', 'Sisa'];
        $col = 'A';
        $row = 6;
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $col++;
        }

        // Style header
        $sheet->getStyle('A6:J6')->getFont()->setBold(true);
        $sheet->getStyle('A6:J6')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('CCCCCC');

        // Data kredit macet
        $row = 7;
        $no = 1;
        $totalPinjaman = 0;
        $totalAngsuran = 0;

        foreach ($dataKreditMacet as $kredit) {
            $jatuhTempo = Carbon::parse($kredit->tgl_pinjam)->addMonths($kredit->lama_angsuran);
            $lamaMacet = $jatuhTempo->diffInDays(Carbon::now());
            
            $sheet->setCellValue('A' . $row, $no);
            $sheet->setCellValue('B' . $row, $kredit->no_pinjaman);
            $sheet->setCellValue('C' . $row, $kredit->anggota->nama ?? '-');
            $sheet->setCellValue('D' . $row, $kredit->anggota->no_ktp ?? '-');
            $sheet->setCellValue('E' . $row, Carbon::parse($kredit->tgl_pinjam)->format('d/m/Y'));
            $sheet->setCellValue('F' . $row, $jatuhTempo->format('d/m/Y'));
            $sheet->setCellValue('G' . $row, $lamaMacet);
            $sheet->setCellValue('H' . $row, $kredit->jumlah);
            $sheet->setCellValue('I' . $row, $kredit->jumlah_angsuran);
            $sheet->setCellValue('J' . $row, $kredit->jumlah - $kredit->jumlah_angsuran);

            $totalPinjaman += $kredit->jumlah;
            $totalAngsuran += $kredit->jumlah_angsuran;

            $row++;
            $no++;
        }

        // Total row
        $sheet->setCellValue('A' . $row, '');
        $sheet->setCellValue('B' . $row, '');
        $sheet->setCellValue('C' . $row, '');
        $sheet->setCellValue('D' . $row, '');
        $sheet->setCellValue('E' . $row, '');
        $sheet->setCellValue('F' . $row, '');
        $sheet->setCellValue('G' . $row, 'TOTAL');
        $sheet->setCellValue('H' . $row, $totalPinjaman);
        $sheet->setCellValue('I' . $row, $totalAngsuran);
        $sheet->setCellValue('J' . $row, $totalPinjaman - $totalAngsuran);

        // Style total row
        $sheet->getStyle('A' . $row . ':J' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':J' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('E6E6E6');

        // Auto size columns
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Set number format for currency columns
        $sheet->getStyle('H7:J' . $row)->getNumberFormat()->setFormatCode('#,##0');

        // Create file
        $writer = new Xlsx($spreadsheet);
        $filename = 'laporan_kredit_macet_' . date('Ymd') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
} 