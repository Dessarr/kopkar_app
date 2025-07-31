<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TblPinjamanH;
use App\Models\TblPinjamanD;
use App\Models\jns_akun;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\DB;

class LaporanShuController extends Controller
{
    public function index(Request $request)
    {
        $tgl_dari = $request->input('tgl_dari', date('Y').'-01-01');
        $tgl_samp = $request->input('tgl_samp', date('Y').'-12-31');
        $data = $this->getShuData($tgl_dari, $tgl_samp);
        return view('laporan.shu', [
            'tgl_dari' => $tgl_dari,
            'tgl_samp' => $tgl_samp,
            'data' => $data
        ]);
    }

    private function getShuData($tgl_dari, $tgl_samp)
    {
        // Total pinjaman, angsuran, denda
        $jml_pinjaman = TblPinjamanH::whereBetween('tgl_pinjam', [$tgl_dari, $tgl_samp])->sum('jumlah');
        $jml_angsuran = TblPinjamanD::whereBetween('tgl_bayar', [$tgl_dari, $tgl_samp])->sum('jumlah_bayar');
        $jml_denda = TblPinjamanD::whereBetween('tgl_bayar', [$tgl_dari, $tgl_samp])->sum('denda_rp');
        
        // Pendapatan
        $pendapatan = jns_akun::where('akun', 'Pendapatan')->where('aktif', 'Y')->get();
        $total_pendapatan = 0;
        $pendapatan_rows = [];
        foreach ($pendapatan as $akun) {
            $jumlah = DB::table('v_transaksi')
                ->where('transaksi', $akun->id)
                ->whereBetween('tgl', [$tgl_dari, $tgl_samp])
                ->sum('debet');
            $pendapatan_rows[] = [
                'nama' => $akun->jns_trans,
                'jumlah' => $jumlah
            ];
            $total_pendapatan += $jumlah;
        }
        
        // Biaya
        $biaya = jns_akun::where('akun', 'Biaya')->where('aktif', 'Y')->get();
        $total_biaya = 0;
        $biaya_rows = [];
        foreach ($biaya as $akun) {
            $jumlah = DB::table('v_transaksi')
                ->where('transaksi', $akun->id)
                ->whereBetween('tgl', [$tgl_dari, $tgl_samp])
                ->sum('kredit');
            $biaya_rows[] = [
                'nama' => $akun->jns_trans,
                'jumlah' => $jumlah
            ];
            $total_biaya += $jumlah;
        }
        
        $shu = $total_pendapatan - $total_biaya;
        
        return [
            'jml_pinjaman' => $jml_pinjaman,
            'jml_angsuran' => $jml_angsuran,
            'jml_denda' => $jml_denda,
            'pendapatan_rows' => $pendapatan_rows,
            'total_pendapatan' => $total_pendapatan,
            'biaya_rows' => $biaya_rows,
            'total_biaya' => $total_biaya,
            'shu' => $shu
        ];
    }

    public function exportPdf(Request $request)
    {
        $tgl_dari = $request->input('tgl_dari', date('Y').'-01-01');
        $tgl_samp = $request->input('tgl_samp', date('Y').'-12-31');
        $data = $this->getShuData($tgl_dari, $tgl_samp);
        $pdf = Pdf::loadView('laporan.shu_pdf', [
            'tgl_dari' => $tgl_dari,
            'tgl_samp' => $tgl_samp,
            'data' => $data
        ]);
        return $pdf->download('laporan_shu_'.$tgl_dari.'_'.$tgl_samp.'.pdf');
    }

    public function exportExcel(Request $request)
    {
        $tgl_dari = $request->input('tgl_dari', date('Y').'-01-01');
        $tgl_samp = $request->input('tgl_samp', date('Y').'-12-31');
        $data = $this->getShuData($tgl_dari, $tgl_samp);
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'LAPORAN SHU Periode '.$tgl_dari.' s/d '.$tgl_samp);
        $sheet->mergeCells('A1:D1');
        $sheet->setCellValue('A2', 'Keterangan');
        $sheet->setCellValue('B2', 'Jumlah');
        $rowNum = 3;
        $sheet->setCellValue('A'.$rowNum, 'Total Pinjaman');
        $sheet->setCellValue('B'.$rowNum, $data['jml_pinjaman']);
        $rowNum++;
        $sheet->setCellValue('A'.$rowNum, 'Total Angsuran');
        $sheet->setCellValue('B'.$rowNum, $data['jml_angsuran']);
        $rowNum++;
        $sheet->setCellValue('A'.$rowNum, 'Total Denda');
        $sheet->setCellValue('B'.$rowNum, $data['jml_denda']);
        $rowNum++;
        $sheet->setCellValue('A'.$rowNum, 'Pendapatan');
        $rowNum++;
        foreach ($data['pendapatan_rows'] as $row) {
            $sheet->setCellValue('A'.$rowNum, $row['nama']);
            $sheet->setCellValue('B'.$rowNum, $row['jumlah']);
            $rowNum++;
        }
        $sheet->setCellValue('A'.$rowNum, 'Total Pendapatan');
        $sheet->setCellValue('B'.$rowNum, $data['total_pendapatan']);
        $rowNum++;
        $sheet->setCellValue('A'.$rowNum, 'Biaya');
        $rowNum++;
        foreach ($data['biaya_rows'] as $row) {
            $sheet->setCellValue('A'.$rowNum, $row['nama']);
            $sheet->setCellValue('B'.$rowNum, $row['jumlah']);
            $rowNum++;
        }
        $sheet->setCellValue('A'.$rowNum, 'Total Biaya');
        $sheet->setCellValue('B'.$rowNum, $data['total_biaya']);
        $rowNum++;
        $sheet->setCellValue('A'.$rowNum, 'SHU');
        $sheet->setCellValue('B'.$rowNum, $data['shu']);
        $writer = new Xlsx($spreadsheet);
        $filename = 'laporan_shu_'.$tgl_dari.'_'.$tgl_samp.'.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }
} 