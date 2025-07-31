<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TblPinjamanH;
use App\Models\TblPinjamanD;
use App\Models\data_anggota;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\DB;

class LaporanTargetRealisasiController extends Controller
{
    public function index(Request $request)
    {
        $tgl_dari = $request->input('tgl_dari', date('Y').'-01-01');
        $tgl_samp = $request->input('tgl_samp', date('Y').'-12-31');
        $data = $this->getDetailPinjaman($tgl_dari, $tgl_samp);
        return view('laporan.target_realisasi', [
            'tgl_dari' => $tgl_dari,
            'tgl_samp' => $tgl_samp,
            'data' => $data
        ]);
    }

    private function getDetailPinjaman($tgl_dari, $tgl_samp)
    {
        $pinjaman = TblPinjamanH::with('anggota')
            ->whereDate('tgl_pinjam', '>=', $tgl_dari)
            ->whereDate('tgl_pinjam', '<=', $tgl_samp)
            ->orderBy('tgl_pinjam', 'asc')
            ->get();
        $result = [];
        $no = 1;
        foreach ($pinjaman as $row) {
            $angsuran = TblPinjamanD::where('pinjam_id', $row->id)->get();
            $bln_sudah_angsur = $angsuran->count();
            $total_bayar = $angsuran->sum('jumlah_bayar');
            $bunga_ags = $angsuran->sum('bunga');
            $denda_rp = $angsuran->sum('denda_rp');
            $result[] = [
                'no' => $no++,
                'tgl_pinjam' => $row->tgl_pinjam,
                'nama' => $row->anggota->nama ?? '',
                'id' => $row->anggota->id ?? '',
                'jumlah' => $row->jumlah,
                'sisa_pokok' => $row->sisa_pokok,
                'lama_angsuran' => $row->lama_angsuran,
                'bunga' => $row->bunga,
                'pokok_angsuran' => $row->pokok_angsuran,
                'pokok_bunga' => $row->pokok_bunga,
                'biaya_adm' => $row->biaya_adm,
                'bln_sudah_angsur' => $bln_sudah_angsur,
                'total_bayar' => $total_bayar,
                'bunga_ags' => $bunga_ags,
                'denda_rp' => $denda_rp,
                'sisa_tagihan' => $row->jumlah - $total_bayar
            ];
        }
        return $result;
    }

    public function exportPdf(Request $request)
    {
        $tgl_dari = $request->input('tgl_dari', date('Y').'-01-01');
        $tgl_samp = $request->input('tgl_samp', date('Y').'-12-31');
        $data = $this->getDetailPinjaman($tgl_dari, $tgl_samp);
        $pdf = Pdf::loadView('laporan.target_realisasi_pdf', [
            'tgl_dari' => $tgl_dari,
            'tgl_samp' => $tgl_samp,
            'data' => $data
        ]);
        return $pdf->download('laporan_target_realisasi_'.$tgl_dari.'_'.$tgl_samp.'.pdf');
    }

    public function exportExcel(Request $request)
    {
        $tgl_dari = $request->input('tgl_dari', date('Y').'-01-01');
        $tgl_samp = $request->input('tgl_samp', date('Y').'-12-31');
        $data = $this->getDetailPinjaman($tgl_dari, $tgl_samp);
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'LAPORAN TARGET & REALISASI Periode '.$tgl_dari.' s/d '.$tgl_samp);
        $sheet->mergeCells('A1:R1');
        $headers = ['No','Tanggal Pinjam','Nama','ID','Pinjaman','Saldo Pinjaman','JW','%','Pokok','Bunga','Admin','Angsuran Ke','Pokok','Bunga','Denda','Jumlah','Sisa Tagihan'];
        $col = 1;
        foreach ($headers as $header) {
            $sheet->setCellValueByColumnAndRow($col, 2, $header);
            $col++;
        }
        $rowNum = 3;
        foreach ($data as $row) {
            $sheet->setCellValue('A'.$rowNum, $row['no']);
            $sheet->setCellValue('B'.$rowNum, $row['tgl_pinjam']);
            $sheet->setCellValue('C'.$rowNum, $row['nama']);
            $sheet->setCellValue('D'.$rowNum, $row['id']);
            $sheet->setCellValue('E'.$rowNum, $row['jumlah']);
            $sheet->setCellValue('F'.$rowNum, $row['sisa_pokok']);
            $sheet->setCellValue('G'.$rowNum, $row['lama_angsuran']);
            $sheet->setCellValue('H'.$rowNum, $row['bunga']);
            $sheet->setCellValue('I'.$rowNum, $row['pokok_angsuran']);
            $sheet->setCellValue('J'.$rowNum, $row['pokok_bunga']);
            $sheet->setCellValue('K'.$rowNum, $row['biaya_adm']);
            $sheet->setCellValue('L'.$rowNum, $row['bln_sudah_angsur']);
            $sheet->setCellValue('M'.$rowNum, $row['total_bayar']);
            $sheet->setCellValue('N'.$rowNum, $row['bunga_ags']);
            $sheet->setCellValue('O'.$rowNum, $row['denda_rp']);
            $sheet->setCellValue('P'.$rowNum, $row['total_bayar'] + $row['bunga_ags'] + $row['denda_rp']);
            $sheet->setCellValue('Q'.$rowNum, $row['sisa_tagihan']);
            $rowNum++;
        }
        $writer = new Xlsx($spreadsheet);
        $filename = 'laporan_target_realisasi_'.$tgl_dari.'_'.$tgl_samp.'.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }
} 