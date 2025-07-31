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

class LaporanAngsuranPinjamanController extends Controller
{
    public function index(Request $request)
    {
        $tgl_dari = $request->input('tgl_dari', date('Y').'-01-01');
        $tgl_samp = $request->input('tgl_samp', date('Y').'-12-31');
        $data = $this->getDetailAngsuran($tgl_dari, $tgl_samp);
        return view('laporan.angsuran_pinjaman', [
            'tgl_dari' => $tgl_dari,
            'tgl_samp' => $tgl_samp,
            'data' => $data
        ]);
    }

    private function getDetailAngsuran($tgl_dari, $tgl_samp)
    {
        $angsuran = TblPinjamanD::with(['pinjaman.anggota'])
            ->whereHas('pinjaman', function($q) use ($tgl_dari, $tgl_samp) {
                $q->whereDate('tgl_pinjam', '>=', $tgl_dari)
                  ->whereDate('tgl_pinjam', '<=', $tgl_samp);
            })
            ->orderBy('tgl_bayar', 'asc')
            ->get();
        $result = [];
        $no = 1;
        foreach ($angsuran as $row) {
            $pinjaman = $row->pinjaman;
            $anggota = $pinjaman->anggota ?? null;
            $result[] = [
                'no' => $no++,
                'tgl_pinjam' => $pinjaman->tgl_pinjam ?? '',
                'nama' => $anggota->nama ?? '',
                'id' => $anggota->id ?? '',
                'jumlah' => $pinjaman->jumlah ?? 0,
                'lama_angsuran' => $pinjaman->lama_angsuran ?? 0,
                'jumlah_bunga' => $pinjaman->bunga ?? 0,
                'saldo_pinjaman' => ($pinjaman->jumlah ?? 0) - ($pinjaman->pokok_angsuran ?? 0),
                'pokok' => $row->jumlah_bayar,
                'bunga' => $row->bunga,
                'denda' => $row->denda_rp,
                'jumlah_angsuran' => $row->jumlah_bayar + $row->bunga + $row->denda_rp,
                'saldo_akhir' => $row->sisa_pokok,
                'angsuran_ke' => $row->angsuran_ke,
                'tgl_bayar' => $row->tgl_bayar
            ];
        }
        return $result;
    }

    public function exportPdf(Request $request)
    {
        $tgl_dari = $request->input('tgl_dari', date('Y').'-01-01');
        $tgl_samp = $request->input('tgl_samp', date('Y').'-12-31');
        $data = $this->getDetailAngsuran($tgl_dari, $tgl_samp);
        $pdf = Pdf::loadView('laporan.angsuran_pinjaman_pdf', [
            'tgl_dari' => $tgl_dari,
            'tgl_samp' => $tgl_samp,
            'data' => $data
        ]);
        return $pdf->download('laporan_angsuran_pinjaman_'.$tgl_dari.'_'.$tgl_samp.'.pdf');
    }

    public function exportExcel(Request $request)
    {
        $tgl_dari = $request->input('tgl_dari', date('Y').'-01-01');
        $tgl_samp = $request->input('tgl_samp', date('Y').'-12-31');
        $data = $this->getDetailAngsuran($tgl_dari, $tgl_samp);
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'LAPORAN ANGSURAN PINJAMAN Periode '.$tgl_dari.' s/d '.$tgl_samp);
        $sheet->mergeCells('A1:N1');
        $headers = ['No','Tanggal Pinjam','Nama','ID','Pinjaman Awal','JW','%','Saldo Pinjaman','Pokok','Bunga','Denda','Jumlah','Saldo Akhir','Angsuran Ke','Tgl. Bayar'];
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
            $sheet->setCellValue('F'.$rowNum, $row['lama_angsuran']);
            $sheet->setCellValue('G'.$rowNum, $row['jumlah_bunga']);
            $sheet->setCellValue('H'.$rowNum, $row['saldo_pinjaman']);
            $sheet->setCellValue('I'.$rowNum, $row['pokok']);
            $sheet->setCellValue('J'.$rowNum, $row['bunga']);
            $sheet->setCellValue('K'.$rowNum, $row['denda']);
            $sheet->setCellValue('L'.$rowNum, $row['jumlah_angsuran']);
            $sheet->setCellValue('M'.$rowNum, $row['saldo_akhir']);
            $sheet->setCellValue('N'.$rowNum, $row['angsuran_ke']);
            $sheet->setCellValue('O'.$rowNum, $row['tgl_bayar']);
            $rowNum++;
        }
        $writer = new Xlsx($spreadsheet);
        $filename = 'laporan_angsuran_pinjaman_'.$tgl_dari.'_'.$tgl_samp.'.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }
} 