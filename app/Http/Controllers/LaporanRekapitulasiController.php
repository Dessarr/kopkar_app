<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TblPinjamanH;
use App\Models\TblPinjamanD;
use App\Models\TempoPinjaman;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\DB;

class LaporanRekapitulasiController extends Controller
{
    public function index(Request $request)
    {
        $periode = $request->input('periode', date('Y-m'));
        $data = $this->getRekapBulanan($periode);
        return view('laporan.rekapitulasi', [
            'periode' => $periode,
            'data' => $data
        ]);
    }

    private function getRekapBulanan($periode)
    {
        $tglArr = explode('-', $periode);
        $thn = $tglArr[0];
        $bln = $tglArr[1];
        $days = cal_days_in_month(CAL_GREGORIAN, $bln, $thn);
        $result = [];
        for ($i = 1; $i <= $days; $i++) {
            $tgl = sprintf('%s-%02d-%02d', $thn, $bln, $i);
            // Tagihan hari ini
            $jml_tagihan = TempoPinjaman::whereDate('tempo', $tgl)->count();
            // Pinjam_id yang jatuh tempo hari ini
            $pinjam_ids = TempoPinjaman::whereDate('tempo', $tgl)->pluck('pinjam_id')->toArray();
            // Target pokok & bunga
            $target_pokok = TblPinjamanH::whereIn('id', $pinjam_ids)->sum(DB::raw('jumlah / lama_angsuran'));
            $target_bunga = TblPinjamanH::whereIn('id', $pinjam_ids)->sum(DB::raw('(jumlah * bunga) / 100'));
            // Tagihan masuk (angsuran yang dibayar hari ini)
            $tagihan_masuk = TblPinjamanD::whereDate('tgl_bayar', $tgl)->count();
            $realisasi_pokok = TblPinjamanD::whereDate('tgl_bayar', $tgl)->sum('jumlah_bayar');
            $realisasi_bunga = TblPinjamanD::whereDate('tgl_bayar', $tgl)->sum('bunga');
            // Tagihan bermasalah
            $tagihan_bermasalah = $jml_tagihan - $tagihan_masuk;
            // Tidak bayar pokok & bunga
            $tidak_bayar_pokok = $target_pokok - $realisasi_pokok;
            $tidak_bayar_bunga = $target_bunga - $realisasi_bunga;
            $result[] = [
                'no' => $i,
                'tanggal' => $tgl,
                'jml_tagihan' => $jml_tagihan,
                'target_pokok' => $target_pokok,
                'target_bunga' => $target_bunga,
                'tagihan_masuk' => $tagihan_masuk,
                'realisasi_pokok' => $realisasi_pokok,
                'realisasi_bunga' => $realisasi_bunga,
                'tagihan_bermasalah' => $tagihan_bermasalah,
                'tidak_bayar_pokok' => abs($tidak_bayar_pokok),
                'tidak_bayar_bunga' => abs($tidak_bayar_bunga)
            ];
        }
        return $result;
    }

    public function exportPdf(Request $request)
    {
        $periode = $request->input('periode', date('Y-m'));
        $data = $this->getRekapBulanan($periode);
        $pdf = Pdf::loadView('laporan.rekapitulasi_pdf', [
            'periode' => $periode,
            'data' => $data
        ]);
        return $pdf->download('laporan_rekapitulasi_'.$periode.'.pdf');
    }

    public function exportExcel(Request $request)
    {
        $periode = $request->input('periode', date('Y-m'));
        $data = $this->getRekapBulanan($periode);
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'LAPORAN REKAPITULASI Periode '.$periode);
        $sheet->mergeCells('A1:K1');
        $headers = ['No','Tanggal','Tagihan Hari Ini','Target Pokok','Target Bunga','Tagihan Masuk','Realisasi Pokok','Realisasi Bunga','Tagihan Bermasalah','Tidak Bayar Pokok','Tidak Bayar Bunga'];
        $col = 1;
        foreach ($headers as $header) {
            $sheet->setCellValueByColumnAndRow($col, 2, $header);
            $col++;
        }
        $rowNum = 3;
        foreach ($data as $row) {
            $sheet->setCellValue('A'.$rowNum, $row['no']);
            $sheet->setCellValue('B'.$rowNum, $row['tanggal']);
            $sheet->setCellValue('C'.$rowNum, $row['jml_tagihan']);
            $sheet->setCellValue('D'.$rowNum, $row['target_pokok']);
            $sheet->setCellValue('E'.$rowNum, $row['target_bunga']);
            $sheet->setCellValue('F'.$rowNum, $row['tagihan_masuk']);
            $sheet->setCellValue('G'.$rowNum, $row['realisasi_pokok']);
            $sheet->setCellValue('H'.$rowNum, $row['realisasi_bunga']);
            $sheet->setCellValue('I'.$rowNum, $row['tagihan_bermasalah']);
            $sheet->setCellValue('J'.$rowNum, $row['tidak_bayar_pokok']);
            $sheet->setCellValue('K'.$rowNum, $row['tidak_bayar_bunga']);
            $rowNum++;
        }
        $writer = new Xlsx($spreadsheet);
        $filename = 'laporan_rekapitulasi_'.$periode.'.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }
} 