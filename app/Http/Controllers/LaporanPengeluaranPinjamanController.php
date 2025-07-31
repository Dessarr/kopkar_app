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

class LaporanPengeluaranPinjamanController extends Controller
{
    public function index(Request $request)
    {
        $tgl_dari = $request->input('tgl_dari', date('Y').'-01-01');
        $tgl_samp = $request->input('tgl_samp', date('Y').'-12-31');
        $data = $this->getDetailPengeluaran($tgl_dari, $tgl_samp);
        return view('laporan.pengeluaran_pinjaman', [
            'tgl_dari' => $tgl_dari,
            'tgl_samp' => $tgl_samp,
            'data' => $data['rows'],
            'total' => $data['total']
        ]);
    }

    private function getDetailPengeluaran($tgl_dari, $tgl_samp)
    {
        $pinjaman = TblPinjamanH::with('anggota')
            ->whereDate('tgl_pinjam', '>=', $tgl_dari)
            ->whereDate('tgl_pinjam', '<=', $tgl_samp)
            ->orderBy('tgl_pinjam', 'asc')
            ->get();
        $result = [];
        $total_pinjaman = 0;
        $total_tagihan = 0;
        $total_dibayar = 0;
        $total_sisa_tagihan = 0;
        $no = 1;
        foreach ($pinjaman as $row) {
            $anggota = $row->anggota;
            $angsuran = TblPinjamanD::where('pinjam_id', $row->id)->get();
            $jml_bayar = $angsuran->sum('jumlah_bayar');
            $jml_denda = $angsuran->sum('denda_rp');
            $jml_adm = $angsuran->sum('biaya_adm');
            $jml_bunga = $angsuran->sum('bunga');
            $sisa_tagihan = $row->tagihan - $jml_bayar;
            $result[] = [
                'no' => $no++,
                'tgl_pinjam' => $row->tgl_pinjam,
                'nama' => $anggota->nama ?? '',
                'id' => $anggota->id ?? '',
                'jumlah' => $row->jumlah,
                'lama_angsuran' => $row->lama_angsuran,
                'lunas' => $row->lunas,
                'pokok_angsuran' => $row->pokok_angsuran,
                'pokok_bunga' => $row->pokok_bunga,
                'ags_per_bulan' => $row->ags_per_bulan,
                'tagihan' => $row->tagihan,
                'jml_bunga' => $jml_bunga,
                'jml_denda' => $jml_denda,
                'jml_adm' => $jml_adm,
                'jml_bayar' => $jml_bayar,
                'sisa_tagihan' => $sisa_tagihan,
                'alamat' => $anggota->alamat ?? '',
                'notelp' => $anggota->notelp ?? ''
            ];
            $total_pinjaman += $row->jumlah;
            $total_tagihan += $row->tagihan;
            $total_dibayar += $jml_bayar;
            $total_sisa_tagihan += $sisa_tagihan;
        }
        return [
            'rows' => $result,
            'total' => [
                'total_pinjaman' => $total_pinjaman,
                'total_tagihan' => $total_tagihan,
                'total_dibayar' => $total_dibayar,
                'total_sisa_tagihan' => $total_sisa_tagihan
            ]
        ];
    }

    public function exportPdf(Request $request)
    {
        $tgl_dari = $request->input('tgl_dari', date('Y').'-01-01');
        $tgl_samp = $request->input('tgl_samp', date('Y').'-12-31');
        $data = $this->getDetailPengeluaran($tgl_dari, $tgl_samp);
        $pdf = Pdf::loadView('laporan.pengeluaran_pinjaman_pdf', [
            'tgl_dari' => $tgl_dari,
            'tgl_samp' => $tgl_samp,
            'data' => $data['rows'],
            'total' => $data['total']
        ]);
        return $pdf->download('laporan_pengeluaran_pinjaman_'.$tgl_dari.'_'.$tgl_samp.'.pdf');
    }

    public function exportExcel(Request $request)
    {
        $tgl_dari = $request->input('tgl_dari', date('Y').'-01-01');
        $tgl_samp = $request->input('tgl_samp', date('Y').'-12-31');
        $data = $this->getDetailPengeluaran($tgl_dari, $tgl_samp);
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'LAPORAN PENGELUARAN PINJAMAN Periode '.$tgl_dari.' s/d '.$tgl_samp);
        $sheet->mergeCells('A1:R1');
        $headers = ['No','Tanggal Pinjam','Nama','ID','Pokok Pinjaman','Lama Pinjaman','Status Lunas','Pokok Angsuran','Bunga','Jumlah Angsuran','Tagihan','Total Bunga','Total Denda','Total Biaya Adm','Dibayar','Sisa Tagihan','Alamat','No. Telp'];
        $col = 1;
        foreach ($headers as $header) {
            $sheet->setCellValueByColumnAndRow($col, 2, $header);
            $col++;
        }
        $rowNum = 3;
        foreach ($data['rows'] as $row) {
            $sheet->setCellValue('A'.$rowNum, $row['no']);
            $sheet->setCellValue('B'.$rowNum, $row['tgl_pinjam']);
            $sheet->setCellValue('C'.$rowNum, $row['nama']);
            $sheet->setCellValue('D'.$rowNum, $row['id']);
            $sheet->setCellValue('E'.$rowNum, $row['jumlah']);
            $sheet->setCellValue('F'.$rowNum, $row['lama_angsuran']);
            $sheet->setCellValue('G'.$rowNum, $row['lunas']);
            $sheet->setCellValue('H'.$rowNum, $row['pokok_angsuran']);
            $sheet->setCellValue('I'.$rowNum, $row['pokok_bunga']);
            $sheet->setCellValue('J'.$rowNum, $row['ags_per_bulan']);
            $sheet->setCellValue('K'.$rowNum, $row['tagihan']);
            $sheet->setCellValue('L'.$rowNum, $row['jml_bunga']);
            $sheet->setCellValue('M'.$rowNum, $row['jml_denda']);
            $sheet->setCellValue('N'.$rowNum, $row['jml_adm']);
            $sheet->setCellValue('O'.$rowNum, $row['jml_bayar']);
            $sheet->setCellValue('P'.$rowNum, $row['sisa_tagihan']);
            $sheet->setCellValue('Q'.$rowNum, $row['alamat']);
            $sheet->setCellValue('R'.$rowNum, $row['notelp']);
            $rowNum++;
        }
        $sheet->setCellValue('A'.$rowNum, 'TOTAL');
        $sheet->mergeCells('A'.$rowNum.':D'.$rowNum);
        $sheet->setCellValue('E'.$rowNum, $data['total']['total_pinjaman']);
        $sheet->setCellValue('K'.$rowNum, $data['total']['total_tagihan']);
        $sheet->setCellValue('O'.$rowNum, $data['total']['total_dibayar']);
        $sheet->setCellValue('P'.$rowNum, $data['total']['total_sisa_tagihan']);
        $writer = new Xlsx($spreadsheet);
        $filename = 'laporan_pengeluaran_pinjaman_'.$tgl_dari.'_'.$tgl_samp.'.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }
} 