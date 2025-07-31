<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NamaKasTbl;
use App\Models\transaksi_kas;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\DB;

class LaporanSaldoKasController extends Controller
{
    public function index(Request $request)
    {
        $periode = $request->input('periode', date('Y-m'));
        $data = $this->getSaldoKas($periode);
        return view('laporan.saldo_kas', [
            'periode' => $periode,
            'data' => $data['rows'],
            'saldo_sblm' => $data['saldo_sblm'],
            'total' => $data['total']
        ]);
    }

    private function getSaldoKas($periode)
    {
        $tglArr = explode('-', $periode);
        $thn = $tglArr[0];
        $bln = $tglArr[1];
        $kasList = NamaKasTbl::where('aktif', 'Y')->orderBy('id')->get();
        // Saldo sebelum periode
        $saldo_sblm = transaksi_kas::whereYear('tgl_catat', '<', $thn)
            ->orWhere(function($q) use ($thn, $bln) {
                $q->whereYear('tgl_catat', $thn)
                  ->whereMonth('tgl_catat', '<', $bln);
            })
            ->select(DB::raw('SUM(CASE WHEN untuk_kas_id IS NOT NULL THEN jumlah ELSE 0 END) as jum_debet'),
                     DB::raw('SUM(CASE WHEN dari_kas_id IS NOT NULL THEN jumlah ELSE 0 END) as jum_kredit'))
            ->first();
        $saldo_sblm_val = ($saldo_sblm->jum_debet ?? 0) - ($saldo_sblm->jum_kredit ?? 0);
        $rows = [];
        $total_saldo = 0;
        $no = 1;
        foreach ($kasList as $kas) {
            $debet = transaksi_kas::where('untuk_kas_id', $kas->id)
                ->whereYear('tgl_catat', $thn)
                ->whereMonth('tgl_catat', $bln)
                ->sum('jumlah');
            $kredit = transaksi_kas::where('dari_kas_id', $kas->id)
                ->whereYear('tgl_catat', $thn)
                ->whereMonth('tgl_catat', $bln)
                ->sum('jumlah');
            $saldo = $debet - $kredit;
            $rows[] = [
                'no' => $no++,
                'nama' => $kas->nama,
                'saldo' => $saldo
            ];
            $total_saldo += $saldo;
        }
        return [
            'rows' => $rows,
            'saldo_sblm' => $saldo_sblm_val,
            'total' => $total_saldo
        ];
    }

    public function exportPdf(Request $request)
    {
        $periode = $request->input('periode', date('Y-m'));
        $data = $this->getSaldoKas($periode);
        $pdf = Pdf::loadView('laporan.saldo_kas_pdf', [
            'periode' => $periode,
            'data' => $data['rows'],
            'saldo_sblm' => $data['saldo_sblm'],
            'total' => $data['total']
        ]);
        return $pdf->download('laporan_saldo_kas_'.$periode.'.pdf');
    }

    public function exportExcel(Request $request)
    {
        $periode = $request->input('periode', date('Y-m'));
        $data = $this->getSaldoKas($periode);
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'LAPORAN SALDO KAS Periode '.$periode);
        $sheet->mergeCells('A1:C1');
        $sheet->setCellValue('A2', 'No');
        $sheet->setCellValue('B2', 'Nama Kas');
        $sheet->setCellValue('C2', 'Saldo');
        $rowNum = 3;
        foreach ($data['rows'] as $row) {
            $sheet->setCellValue('A'.$rowNum, $row['no']);
            $sheet->setCellValue('B'.$rowNum, $row['nama']);
            $sheet->setCellValue('C'.$rowNum, $row['saldo']);
            $rowNum++;
        }
        $sheet->setCellValue('A'.$rowNum, 'SALDO PERIODE SEBELUMNYA');
        $sheet->mergeCells('A'.$rowNum.':B'.$rowNum);
        $sheet->setCellValue('C'.$rowNum, $data['saldo_sblm']);
        $rowNum++;
        $sheet->setCellValue('A'.$rowNum, 'JUMLAH');
        $sheet->mergeCells('A'.$rowNum.':B'.$rowNum);
        $sheet->setCellValue('C'.$rowNum, $data['total']);
        $rowNum++;
        $sheet->setCellValue('A'.$rowNum, 'TOTAL SALDO');
        $sheet->mergeCells('A'.$rowNum.':B'.$rowNum);
        $sheet->setCellValue('C'.$rowNum, $data['total'] + $data['saldo_sblm']);
        $writer = new Xlsx($spreadsheet);
        $filename = 'laporan_saldo_kas_'.$periode.'.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }
} 