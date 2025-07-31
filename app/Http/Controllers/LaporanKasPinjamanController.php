<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TblPinjamanH;
use App\Models\TblPinjamanD;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\DB;

class LaporanKasPinjamanController extends Controller
{
    public function index(Request $request)
    {
        $tgl_dari = $request->input('tgl_dari', date('Y').'-01-01');
        $tgl_samp = $request->input('tgl_samp', date('Y').'-12-31');
        $data = $this->getRekapPinjaman($tgl_dari, $tgl_samp);
        return view('laporan.kas_pinjaman', [
            'tgl_dari' => $tgl_dari,
            'tgl_samp' => $tgl_samp,
            'data' => $data
        ]);
    }

    private function getRekapPinjaman($tgl_dari, $tgl_samp)
    {
        // Pokok Pinjaman
        $jml_pinjaman = TblPinjamanH::whereDate('tgl_pinjam', '>=', $tgl_dari)
            ->whereDate('tgl_pinjam', '<=', $tgl_samp)
            ->sum('jumlah');
        
        // Angsuran
        $jml_angsuran = TblPinjamanD::join('tbl_pinjaman_h', 'tbl_pinjaman_h.id', '=', 'tbl_pinjaman_d.pinjam_id')
            ->whereDate('tbl_pinjaman_h.tgl_pinjam', '>=', $tgl_dari)
            ->whereDate('tbl_pinjaman_h.tgl_pinjam', '<=', $tgl_samp)
            ->sum('tbl_pinjaman_d.jumlah_bayar');
        
        // Denda
        $jml_denda = TblPinjamanD::join('tbl_pinjaman_h', 'tbl_pinjaman_h.id', '=', 'tbl_pinjaman_d.pinjam_id')
            ->whereDate('tbl_pinjaman_h.tgl_pinjam', '>=', $tgl_dari)
            ->whereDate('tbl_pinjaman_h.tgl_pinjam', '<=', $tgl_samp)
            ->sum('tbl_pinjaman_d.denda_rp');
        
        // Jumlah Tagihan + Denda (menggunakan jumlah pinjaman sebagai tagihan)
        $tot_tagihan = $jml_pinjaman + $jml_denda;
        
        // Sisa Tagihan
        $sisa_tagihan = $tot_tagihan - $jml_angsuran;
        
        // Jumlah peminjam aktif
        $peminjam_aktif = TblPinjamanH::whereDate('tgl_pinjam', '>=', $tgl_dari)
            ->whereDate('tgl_pinjam', '<=', $tgl_samp)
            ->count();
        
        // Jumlah peminjam lunas
        $peminjam_lunas = TblPinjamanH::where('lunas', 'Lunas')
            ->whereDate('tgl_pinjam', '>=', $tgl_dari)
            ->whereDate('tgl_pinjam', '<=', $tgl_samp)
            ->count();
        
        // Jumlah peminjam belum lunas
        $peminjam_belum = TblPinjamanH::where('lunas', 'Belum')
            ->whereDate('tgl_pinjam', '>=', $tgl_dari)
            ->whereDate('tgl_pinjam', '<=', $tgl_samp)
            ->count();
        
        return [
            'jml_pinjaman' => $jml_pinjaman,
            'jml_angsuran' => $jml_angsuran,
            'jml_denda' => $jml_denda,
            'tot_tagihan' => $tot_tagihan,
            'sisa_tagihan' => $sisa_tagihan,
            'peminjam_aktif' => $peminjam_aktif,
            'peminjam_lunas' => $peminjam_lunas,
            'peminjam_belum' => $peminjam_belum
        ];
    }

    public function exportPdf(Request $request)
    {
        $tgl_dari = $request->input('tgl_dari', date('Y').'-01-01');
        $tgl_samp = $request->input('tgl_samp', date('Y').'-12-31');
        $data = $this->getRekapPinjaman($tgl_dari, $tgl_samp);
        $pdf = Pdf::loadView('laporan.kas_pinjaman_pdf', [
            'tgl_dari' => $tgl_dari,
            'tgl_samp' => $tgl_samp,
            'data' => $data
        ]);
        return $pdf->download('laporan_kas_pinjaman_'.$tgl_dari.'_'.$tgl_samp.'.pdf');
    }

    public function exportExcel(Request $request)
    {
        $tgl_dari = $request->input('tgl_dari', date('Y').'-01-01');
        $tgl_samp = $request->input('tgl_samp', date('Y').'-12-31');
        $data = $this->getRekapPinjaman($tgl_dari, $tgl_samp);
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'LAPORAN KAS PINJAMAN Periode '.$tgl_dari.' s/d '.$tgl_samp);
        $sheet->mergeCells('A1:C1');
        $sheet->setCellValue('A2', 'No');
        $sheet->setCellValue('B2', 'Keterangan');
        $sheet->setCellValue('C2', 'Jumlah');
        $rowNum = 3;
        $rows = [
            ['Pokok Pinjaman', $data['jml_pinjaman']],
            ['Tagihan Denda', $data['jml_denda']],
            ['Jumlah Tagihan + Denda', $data['tot_tagihan']],
            ['Tagihan Sudah Dibayar', $data['jml_angsuran']],
            ['Sisa Tagihan', $data['sisa_tagihan']]
        ];
        $no = 1;
        foreach ($rows as $row) {
            $sheet->setCellValue('A'.$rowNum, $no++);
            $sheet->setCellValue('B'.$rowNum, $row[0]);
            $sheet->setCellValue('C'.$rowNum, $row[1]);
            $rowNum++;
        }
        $writer = new Xlsx($spreadsheet);
        $filename = 'laporan_kas_pinjaman_'.$tgl_dari.'_'.$tgl_samp.'.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }
} 