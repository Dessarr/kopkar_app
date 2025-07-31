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
        $status = $request->input('status', 'aktif');
        $search = $request->input('search');
        $perPage = $request->input('per_page', 15);

        $query = data_anggota::query();

        // Filter berdasarkan status
        if ($status === 'aktif') {
            $query->where('aktif', 'Y');
        } elseif ($status === 'nonaktif') {
            $query->where('aktif', 'N');
        }

        // Search berdasarkan nama atau no KTP
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('no_ktp', 'like', '%' . $search . '%')
                  ->orWhere('no_anggota', 'like', '%' . $search . '%');
            });
        }

        $dataAnggota = $query->orderBy('nama', 'asc')->paginate($perPage);

        // Statistik
        $totalAktif = data_anggota::where('aktif', 'Y')->count();
        $totalNonaktif = data_anggota::where('aktif', 'N')->count();
        $totalAnggota = data_anggota::count();

        return view('laporan.data_anggota', compact(
            'dataAnggota',
            'status',
            'search',
            'perPage',
            'totalAktif',
            'totalNonaktif',
            'totalAnggota'
        ));
    }

    public function exportPdf(Request $request)
    {
        $status = $request->input('status', 'aktif');
        $search = $request->input('search');

        $query = data_anggota::query();

        // Filter berdasarkan status
        if ($status === 'aktif') {
            $query->where('aktif', 'Y');
        } elseif ($status === 'nonaktif') {
            $query->where('aktif', 'N');
        }

        // Search berdasarkan nama atau no KTP
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('no_ktp', 'like', '%' . $search . '%')
                  ->orWhere('no_anggota', 'like', '%' . $search . '%');
            });
        }

        $dataAnggota = $query->orderBy('nama', 'asc')->get();

        $pdf = PDF::loadView('laporan.pdf.data_anggota', compact('dataAnggota', 'status'));

        return $pdf->download('laporan_data_anggota_' . date('Ymd') . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        $status = $request->input('status', 'aktif');
        $search = $request->input('search');

        $query = data_anggota::query();

        // Filter berdasarkan status
        if ($status === 'aktif') {
            $query->where('aktif', 'Y');
        } elseif ($status === 'nonaktif') {
            $query->where('aktif', 'N');
        }

        // Search berdasarkan nama atau no KTP
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('no_ktp', 'like', '%' . $search . '%')
                  ->orWhere('no_anggota', 'like', '%' . $search . '%');
            });
        }

        $dataAnggota = $query->orderBy('nama', 'asc')->get();

        // Create Excel file
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set title
        $sheet->setCellValue('A1', 'LAPORAN DATA ANGGOTA KOPERASI');
        $sheet->setCellValue('A2', 'Status: ' . ucfirst($status));
        $sheet->setCellValue('A3', 'Tanggal: ' . Carbon::now()->format('d/m/Y H:i:s'));
        $sheet->mergeCells('A1:H1');
        $sheet->mergeCells('A2:H2');
        $sheet->mergeCells('A3:H3');

        // Style title
        $sheet->getStyle('A1:A3')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1:A3')->getAlignment()->setHorizontal('center');

        // Header table
        $headers = ['No', 'No Anggota', 'Nama', 'No KTP', 'Tempat Lahir', 'Tanggal Lahir', 'Alamat', 'Status'];
        $col = 'A';
        $row = 5;
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $col++;
        }

        // Style header
        $sheet->getStyle('A5:H5')->getFont()->setBold(true);
        $sheet->getStyle('A5:H5')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('CCCCCC');

        // Data anggota
        $row = 6;
        $no = 1;
        foreach ($dataAnggota as $anggota) {
            $sheet->setCellValue('A' . $row, $no);
            $sheet->setCellValue('B' . $row, $anggota->no_anggota);
            $sheet->setCellValue('C' . $row, $anggota->nama);
            $sheet->setCellValue('D' . $row, $anggota->no_ktp);
            $sheet->setCellValue('E' . $row, $anggota->tempat_lahir);
            $sheet->setCellValue('F' . $row, $anggota->tgl_lahir ? Carbon::parse($anggota->tgl_lahir)->format('d/m/Y') : '');
            $sheet->setCellValue('G' . $row, $anggota->alamat);
            $sheet->setCellValue('H' . $row, $anggota->aktif === 'Y' ? 'Aktif' : 'Nonaktif');
            $row++;
            $no++;
        }

        // Auto size columns
        foreach (range('A', 'H') as $col) {
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