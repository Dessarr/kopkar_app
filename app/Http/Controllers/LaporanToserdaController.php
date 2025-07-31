<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LaporanToserdaController extends Controller
{
    public function index(Request $request)
    {
        return view('laporan.toserda', [
            'message' => 'Laporan Toserda belum tersedia.'
        ]);
    }

    public function exportPdf(Request $request)
    {
        $pdf = Pdf::loadView('laporan.toserda_pdf', [
            'message' => 'Laporan Toserda belum tersedia.'
        ]);
        return $pdf->download('laporan_toserda.pdf');
    }

    public function exportExcel(Request $request)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Laporan Toserda belum tersedia.');
        $writer = new Xlsx($spreadsheet);
        $filename = 'laporan_toserda.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }
} 