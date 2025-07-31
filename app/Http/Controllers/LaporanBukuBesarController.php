<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NamaKasTbl;
use App\Models\transaksi_kas;
use App\Models\jns_akun;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LaporanBukuBesarController extends Controller
{
    public function index(Request $request)
    {
        $periode = $request->input('periode', date('Y-m'));
        $kasId = $request->input('kas_id', null);
        $kasList = NamaKasTbl::where('aktif', 'Y')->get();
        $data = [];
        $selectedKas = null;
        if ($kasId) {
            $selectedKas = $kasList->where('id', $kasId)->first();
            $tglArr = explode('-', $periode);
            $thn = $tglArr[0];
            $bln = $tglArr[1];
            $transaksi = transaksi_kas::where(function($q) use ($kasId) {
                    $q->where('dari_kas_id', $kasId)->orWhere('untuk_kas_id', $kasId);
                })
                ->whereYear('tgl_catat', $thn)
                ->whereMonth('tgl_catat', $bln)
                ->orderBy('tgl_catat', 'asc')
                ->get();
            $data = $this->prosesTransaksi($transaksi, $kasId);
        }
        return view('laporan.buku_besar', [
            'kasList' => $kasList,
            'selectedKas' => $selectedKas,
            'periode' => $periode,
            'data' => $data
        ]);
    }

    private function prosesTransaksi($transaksi, $kasId)
    {
        $result = [];
        $saldo = 0;
        $no = 1;
        foreach ($transaksi as $row) {
            $debet = 0;
            $kredit = 0;
            if ($row->untuk_kas_id == $kasId) {
                $debet = $row->jumlah;
            }
            if ($row->dari_kas_id == $kasId) {
                $kredit = $row->jumlah;
            }
            $saldo += $debet - $kredit;
            $result[] = [
                'no' => $no++,
                'tanggal' => $row->tgl_catat,
                'jenis_transaksi' => $row->akun,
                'keterangan' => $row->keterangan,
                'debet' => $debet,
                'kredit' => $kredit,
                'saldo' => $saldo
            ];
        }
        return $result;
    }

    public function exportPdf(Request $request)
    {
        $periode = $request->input('periode', date('Y-m'));
        $kasId = $request->input('kas_id');
        $kas = NamaKasTbl::find($kasId);
        $tglArr = explode('-', $periode);
        $thn = $tglArr[0];
        $bln = $tglArr[1];
        $transaksi = transaksi_kas::where(function($q) use ($kasId) {
                $q->where('dari_kas_id', $kasId)->orWhere('untuk_kas_id', $kasId);
            })
            ->whereYear('tgl_catat', $thn)
            ->whereMonth('tgl_catat', $bln)
            ->orderBy('tgl_catat', 'asc')
            ->get();
        $data = $this->prosesTransaksi($transaksi, $kasId);
        $pdf = Pdf::loadView('laporan.buku_besar_pdf', [
            'kas' => $kas,
            'periode' => $periode,
            'data' => $data
        ]);
        return $pdf->download('laporan_buku_besar_'.$periode.'.pdf');
    }

    public function exportExcel(Request $request)
    {
        $periode = $request->input('periode', date('Y-m'));
        $kasId = $request->input('kas_id');
        $kas = NamaKasTbl::find($kasId);
        $tglArr = explode('-', $periode);
        $thn = $tglArr[0];
        $bln = $tglArr[1];
        $transaksi = transaksi_kas::where(function($q) use ($kasId) {
                $q->where('dari_kas_id', $kasId)->orWhere('untuk_kas_id', $kasId);
            })
            ->whereYear('tgl_catat', $thn)
            ->whereMonth('tgl_catat', $bln)
            ->orderBy('tgl_catat', 'asc')
            ->get();
        $data = $this->prosesTransaksi($transaksi, $kasId);
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'LAPORAN BUKU BESAR - ' . $kas->nama . ' Periode ' . $periode);
        $sheet->mergeCells('A1:G1');
        $sheet->setCellValue('A2', 'No');
        $sheet->setCellValue('B2', 'Tanggal');
        $sheet->setCellValue('C2', 'Jenis Transaksi');
        $sheet->setCellValue('D2', 'Keterangan');
        $sheet->setCellValue('E2', 'Debet');
        $sheet->setCellValue('F2', 'Kredit');
        $sheet->setCellValue('G2', 'Saldo');
        $rowNum = 3;
        foreach ($data as $row) {
            $sheet->setCellValue('A'.$rowNum, $row['no']);
            $sheet->setCellValue('B'.$rowNum, $row['tanggal']);
            $sheet->setCellValue('C'.$rowNum, $row['jenis_transaksi']);
            $sheet->setCellValue('D'.$rowNum, $row['keterangan']);
            $sheet->setCellValue('E'.$rowNum, $row['debet']);
            $sheet->setCellValue('F'.$rowNum, $row['kredit']);
            $sheet->setCellValue('G'.$rowNum, $row['saldo']);
            $rowNum++;
        }
        $writer = new Xlsx($spreadsheet);
        $filename = 'laporan_buku_besar_'.$periode.'.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }
} 