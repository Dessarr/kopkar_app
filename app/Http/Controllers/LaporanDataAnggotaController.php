<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\data_anggota;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LaporanDataAnggotaController extends Controller
{
    public function index(Request $request)
    {
        $perPage = 10; // Fixed to 10 data per page as per specification
        $search = $request->input('search');
        
        $query = data_anggota::query();

        // Apply search filter if provided
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('no_ktp', 'like', '%' . $search . '%')
                  ->orWhere('id', 'like', '%' . $search . '%');
            });
        }

        $dataAnggota = $query->orderBy('nama', 'asc')->paginate($perPage);

        // Get total count for statistics
        $totalAnggota = data_anggota::count();
        $totalAktif = data_anggota::where('aktif', 'Y')->count();
        $totalNonaktif = data_anggota::where('aktif', 'N')->count();

        return view('laporan.data_anggota', compact(
            'dataAnggota',
            'totalAnggota',
            'totalAktif',
            'totalNonaktif',
            'search'
        ));
    }

    public function exportPdf(Request $request)
    {
        $search = $request->input('search');
        
        $query = data_anggota::query();

        // Apply search filter if provided
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('no_ktp', 'like', '%' . $search . '%')
                  ->orWhere('id', 'like', '%' . $search . '%');
            });
        }

        $dataAnggota = $query->orderBy('nama', 'asc')->get();

        $pdf = PDF::loadView('laporan.pdf.data_anggota', compact('dataAnggota', 'search'));
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('laporan_data_anggota_' . date('Ymd') . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        $dataAnggota = data_anggota::orderBy('nama', 'asc')->get();

        // Create Excel file
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set title
        $sheet->setCellValue('A1', 'LAPORAN DATA ANGGOTA KOPERASI');
        $sheet->setCellValue('A2', 'Tanggal: ' . Carbon::now()->format('d/m/Y H:i:s'));
        $sheet->mergeCells('A1:J1');
        $sheet->mergeCells('A2:J2');

        // Style title
        $sheet->getStyle('A1:A2')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1:A2')->getAlignment()->setHorizontal('center');

        // Header table
        $headers = ['No', 'ID Anggota', 'Nama Anggota', 'L/P', 'Jabatan', 'Alamat', 'Status', 'Tgl Registrasi', 'Photo'];
        $col = 'A';
        $row = 4;
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $col++;
        }

        // Style header
        $sheet->getStyle('A4:J4')->getFont()->setBold(true);
        $sheet->getStyle('A4:J4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('CCCCCC');

        // Data anggota
        $row = 5;
        $no = 1;
        foreach ($dataAnggota as $anggota) {
            $sheet->setCellValue('A' . $row, $no);
            $sheet->setCellValue('B' . $row, 'AG' . str_pad($anggota->id, 4, '0', STR_PAD_LEFT) . ' - ' . $anggota->no_ktp);
            $sheet->setCellValue('C' . $row, $anggota->nama . ' (' . $anggota->tmp_lahir . '/' . ($anggota->tgl_lahir ? Carbon::parse($anggota->tgl_lahir)->format('d-m-Y') : '-') . ')');
            $sheet->setCellValue('D' . $row, $anggota->jk === 'L' ? 'L' : 'P');
            $sheet->setCellValue('E' . $row, ($anggota->jabatan_id == 1 ? 'Pengurus' : 'Anggota') . ' - ' . $anggota->departement);
            $sheet->setCellValue('F' . $row, $anggota->alamat . ' - ' . $anggota->notelp);
            $sheet->setCellValue('G' . $row, $anggota->aktif === 'Y' ? 'Aktif' : 'Tidak Aktif');
            $sheet->setCellValue('H' . $row, $anggota->tgl_daftar ? Carbon::parse($anggota->tgl_daftar)->format('d/m/Y') : '-');
            $sheet->setCellValue('I' . $row, $anggota->file_pic ? 'Ada' : 'Default');
            $row++;
            $no++;
        }

        // Auto size columns
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Create file
        $writer = new Xlsx($spreadsheet);
        $filename = 'laporan_data_anggota_' . date('Ymd') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
} 