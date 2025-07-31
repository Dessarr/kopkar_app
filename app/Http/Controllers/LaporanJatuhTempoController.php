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

class LaporanJatuhTempoController extends Controller
{
    public function index(Request $request)
    {
        $tgl_dari = $request->input('tgl_dari', date('Y-m-01'));
        $tgl_samp = $request->input('tgl_samp', date('Y-m-t'));
        $status = $request->input('status', 'semua');
        $search = $request->input('search');
        $perPage = $request->input('per_page', 15);

        $query = TblPinjamanH::with(['anggota', 'detail'])
            ->whereRaw('DATE_ADD(tgl_pinjam, INTERVAL lama_angsuran MONTH) BETWEEN ? AND ?', [$tgl_dari, $tgl_samp]);

        // Filter berdasarkan status
        if ($status === 'lunas') {
            $query->where('status', 'L');
        } elseif ($status === 'belum_lunas') {
            $query->where('status', 'BL');
        }

        // Search berdasarkan nama anggota atau no pinjaman
        if ($search) {
            $query->whereHas('anggota', function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('no_ktp', 'like', '%' . $search . '%');
            })->orWhere('no_pinjaman', 'like', '%' . $search . '%');
        }

        $dataPinjaman = $query->orderByRaw('DATE_ADD(tgl_pinjam, INTERVAL lama_angsuran MONTH) ASC')->paginate($perPage);

        // Hitung total
        $totalPinjaman = $query->sum('jumlah');
        $totalAngsuran = $query->sum('jumlah_angsuran');
        $totalSisa = $totalPinjaman - $totalAngsuran;

        // Statistik berdasarkan status
        $totalLunas = TblPinjamanH::whereRaw('DATE_ADD(tgl_pinjam, INTERVAL lama_angsuran MONTH) BETWEEN ? AND ?', [$tgl_dari, $tgl_samp])
            ->where('status', 'L')
            ->count();
        $totalBelumLunas = TblPinjamanH::whereRaw('DATE_ADD(tgl_pinjam, INTERVAL lama_angsuran MONTH) BETWEEN ? AND ?', [$tgl_dari, $tgl_samp])
            ->where('status', 'BL')
            ->count();

        return view('laporan.jatuh_tempo', compact(
            'dataPinjaman',
            'tgl_dari',
            'tgl_samp',
            'status',
            'search',
            'perPage',
            'totalPinjaman',
            'totalAngsuran',
            'totalSisa',
            'totalLunas',
            'totalBelumLunas'
        ));
    }

    public function exportPdf(Request $request)
    {
        $tgl_dari = $request->input('tgl_dari', date('Y-m-01'));
        $tgl_samp = $request->input('tgl_samp', date('Y-m-t'));
        $status = $request->input('status', 'semua');
        $search = $request->input('search');

        $query = TblPinjamanH::with(['anggota', 'detail'])
            ->whereRaw('DATE_ADD(tgl_pinjam, INTERVAL lama_angsuran MONTH) BETWEEN ? AND ?', [$tgl_dari, $tgl_samp]);

        if ($status === 'lunas') {
            $query->where('status', 'L');
        } elseif ($status === 'belum_lunas') {
            $query->where('status', 'BL');
        }

        if ($search) {
            $query->whereHas('anggota', function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('no_ktp', 'like', '%' . $search . '%');
            })->orWhere('no_pinjaman', 'like', '%' . $search . '%');
        }

        $dataPinjaman = $query->orderByRaw('DATE_ADD(tgl_pinjam, INTERVAL lama_angsuran MONTH) ASC')->get();

        $tgl_periode_txt = Carbon::parse($tgl_dari)->format('d/m/Y') . ' - ' . Carbon::parse($tgl_samp)->format('d/m/Y');

        $pdf = PDF::loadView('laporan.pdf.jatuh_tempo', compact('dataPinjaman', 'tgl_periode_txt', 'status'));

        return $pdf->download('laporan_jatuh_tempo_' . date('Ymd') . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        $tgl_dari = $request->input('tgl_dari', date('Y-m-01'));
        $tgl_samp = $request->input('tgl_samp', date('Y-m-t'));
        $status = $request->input('status', 'semua');
        $search = $request->input('search');

        $query = TblPinjamanH::with(['anggota', 'detail'])
            ->whereRaw('DATE_ADD(tgl_pinjam, INTERVAL lama_angsuran MONTH) BETWEEN ? AND ?', [$tgl_dari, $tgl_samp]);

        if ($status === 'lunas') {
            $query->where('status', 'L');
        } elseif ($status === 'belum_lunas') {
            $query->where('status', 'BL');
        }

        if ($search) {
            $query->whereHas('anggota', function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('no_ktp', 'like', '%' . $search . '%');
            })->orWhere('no_pinjaman', 'like', '%' . $search . '%');
        }

        $dataPinjaman = $query->orderByRaw('DATE_ADD(tgl_pinjam, INTERVAL lama_angsuran MONTH) ASC')->get();

        $tgl_periode_txt = Carbon::parse($tgl_dari)->format('d/m/Y') . ' - ' . Carbon::parse($tgl_samp)->format('d/m/Y');

        // Create Excel file
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set title
        $sheet->setCellValue('A1', 'LAPORAN JATUH TEMPO PINJAMAN');
        $sheet->setCellValue('A2', 'Periode: ' . $tgl_periode_txt);
        $sheet->setCellValue('A3', 'Status: ' . ucfirst(str_replace('_', ' ', $status)));
        $sheet->setCellValue('A4', 'Tanggal: ' . Carbon::now()->format('d/m/Y H:i:s'));
        $sheet->mergeCells('A1:I1');
        $sheet->mergeCells('A2:I2');
        $sheet->mergeCells('A3:I3');
        $sheet->mergeCells('A4:I4');

        // Style title
        $sheet->getStyle('A1:A4')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1:A4')->getAlignment()->setHorizontal('center');

        // Header table
        $headers = ['No', 'No Pinjaman', 'Nama Anggota', 'No KTP', 'Tanggal Pinjam', 'Jatuh Tempo', 'Jumlah Pinjaman', 'Jumlah Angsuran', 'Sisa', 'Status'];
        $col = 'A';
        $row = 6;
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $col++;
        }

        // Style header
        $sheet->getStyle('A6:J6')->getFont()->setBold(true);
        $sheet->getStyle('A6:J6')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('CCCCCC');

        // Data pinjaman
        $row = 7;
        $no = 1;
        $totalPinjaman = 0;
        $totalAngsuran = 0;

        foreach ($dataPinjaman as $pinjaman) {
            $jatuhTempo = Carbon::parse($pinjaman->tgl_pinjam)->addMonths($pinjaman->lama_angsuran);
            
            $sheet->setCellValue('A' . $row, $no);
            $sheet->setCellValue('B' . $row, $pinjaman->no_pinjaman);
            $sheet->setCellValue('C' . $row, $pinjaman->anggota->nama ?? '-');
            $sheet->setCellValue('D' . $row, $pinjaman->anggota->no_ktp ?? '-');
            $sheet->setCellValue('E' . $row, Carbon::parse($pinjaman->tgl_pinjam)->format('d/m/Y'));
            $sheet->setCellValue('F' . $row, $jatuhTempo->format('d/m/Y'));
            $sheet->setCellValue('G' . $row, $pinjaman->jumlah);
            $sheet->setCellValue('H' . $row, $pinjaman->jumlah_angsuran);
            $sheet->setCellValue('I' . $row, $pinjaman->jumlah - $pinjaman->jumlah_angsuran);
            $sheet->setCellValue('J' . $row, $pinjaman->status === 'L' ? 'Lunas' : 'Belum Lunas');

            $totalPinjaman += $pinjaman->jumlah;
            $totalAngsuran += $pinjaman->jumlah_angsuran;

            $row++;
            $no++;
        }

        // Total row
        $sheet->setCellValue('A' . $row, '');
        $sheet->setCellValue('B' . $row, '');
        $sheet->setCellValue('C' . $row, '');
        $sheet->setCellValue('D' . $row, '');
        $sheet->setCellValue('E' . $row, '');
        $sheet->setCellValue('F' . $row, 'TOTAL');
        $sheet->setCellValue('G' . $row, $totalPinjaman);
        $sheet->setCellValue('H' . $row, $totalAngsuran);
        $sheet->setCellValue('I' . $row, $totalPinjaman - $totalAngsuran);
        $sheet->setCellValue('J' . $row, '');

        // Style total row
        $sheet->getStyle('A' . $row . ':J' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':J' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('E6E6E6');

        // Auto size columns
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Set number format for currency columns
        $sheet->getStyle('G7:I' . $row)->getNumberFormat()->setFormatCode('#,##0');

        // Create file
        $writer = new Xlsx($spreadsheet);
        $filename = 'laporan_jatuh_tempo_' . date('Ymd') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
} 