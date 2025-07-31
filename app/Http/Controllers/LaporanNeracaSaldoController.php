<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NamaKasTbl;
use App\Models\jns_akun;
use App\Models\transaksi_kas;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LaporanNeracaSaldoController extends Controller
{
    public function index(Request $request)
    {
        $tgl_dari = $request->input('tgl_dari', date('Y').'-01-01');
        $tgl_samp = $request->input('tgl_samp', date('Y').'-12-31');
        $kasList = NamaKasTbl::where('aktif', 'Y')->orderBy('id')->get();
        $akunList = jns_akun::where('aktif', 'Y')->orderByRaw("LPAD(kd_aktiva, 1, 0) ASC, LPAD(kd_aktiva, 5, 1) ASC")->get();
        $data = $this->getNeracaData($kasList, $akunList, $tgl_dari, $tgl_samp);
        return view('laporan.neraca_saldo', [
            'kasList' => $kasList,
            'akunList' => $akunList,
            'tgl_dari' => $tgl_dari,
            'tgl_samp' => $tgl_samp,
            'data' => $data
        ]);
    }

    private function getNeracaData($kasList, $akunList, $tgl_dari, $tgl_samp)
    {
        $result = [];
        $no = 1;
        $totalDebet = 0;
        $totalKredit = 0;
        // Kas
        foreach ($kasList as $kas) {
            $debet = transaksi_kas::where('untuk_kas_id', $kas->id)
                ->whereDate('tgl_catat', '>=', $tgl_dari)
                ->whereDate('tgl_catat', '<=', $tgl_samp)
                ->sum('jumlah');
            $kredit = transaksi_kas::where('dari_kas_id', $kas->id)
                ->whereDate('tgl_catat', '>=', $tgl_dari)
                ->whereDate('tgl_catat', '<=', $tgl_samp)
                ->sum('jumlah');
            $saldo = $debet - $kredit;
            $result[] = [
                'no' => 'A'.$no,
                'nama' => $kas->nama,
                'debet' => $saldo,
                'kredit' => 0
            ];
            $totalDebet += $saldo;
            $no++;
        }
        // Akun
        foreach ($akunList as $akun) {
            $akunDebet = transaksi_kas::where('akun', $akun->id)
                ->whereDate('tgl_catat', '>=', $tgl_dari)
                ->whereDate('tgl_catat', '<=', $tgl_samp)
                ->sum('jumlah');
            $akunKredit = 0; // Placeholder, sesuaikan jika ada field kredit khusus
            if ($akun->akun == 'Aktiva') {
                $result[] = [
                    'no' => $akun->kd_aktiva,
                    'nama' => $akun->jns_trans,
                    'debet' => abs($akunDebet),
                    'kredit' => 0
                ];
                $totalDebet += abs($akunDebet);
            } elseif ($akun->akun == 'Pasiva') {
                $result[] = [
                    'no' => $akun->kd_aktiva,
                    'nama' => $akun->jns_trans,
                    'debet' => 0,
                    'kredit' => abs($akunDebet)
                ];
                $totalKredit += abs($akunDebet);
            }
        }
        return [
            'rows' => $result,
            'totalDebet' => $totalDebet,
            'totalKredit' => $totalKredit
        ];
    }

    public function exportPdf(Request $request)
    {
        $tgl_dari = $request->input('tgl_dari', date('Y').'-01-01');
        $tgl_samp = $request->input('tgl_samp', date('Y').'-12-31');
        $kasList = NamaKasTbl::where('aktif', 'Y')->orderBy('id')->get();
        $akunList = jns_akun::where('aktif', 'Y')->orderByRaw("LPAD(kd_aktiva, 1, 0) ASC, LPAD(kd_aktiva, 5, 1) ASC")->get();
        $data = $this->getNeracaData($kasList, $akunList, $tgl_dari, $tgl_samp);
        $pdf = Pdf::loadView('laporan.neraca_saldo_pdf', [
            'tgl_dari' => $tgl_dari,
            'tgl_samp' => $tgl_samp,
            'data' => $data
        ]);
        return $pdf->download('laporan_neraca_saldo_'.$tgl_dari.'_'.$tgl_samp.'.pdf');
    }

    public function exportExcel(Request $request)
    {
        $tgl_dari = $request->input('tgl_dari', date('Y').'-01-01');
        $tgl_samp = $request->input('tgl_samp', date('Y').'-12-31');
        $kasList = NamaKasTbl::where('aktif', 'Y')->orderBy('id')->get();
        $akunList = jns_akun::where('aktif', 'Y')->orderByRaw("LPAD(kd_aktiva, 1, 0) ASC, LPAD(kd_aktiva, 5, 1) ASC")->get();
        $data = $this->getNeracaData($kasList, $akunList, $tgl_dari, $tgl_samp);
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'LAPORAN NERACA SALDO Periode '.$tgl_dari.' s/d '.$tgl_samp);
        $sheet->mergeCells('A1:D1');
        $sheet->setCellValue('A2', 'No');
        $sheet->setCellValue('B2', 'Nama Akun');
        $sheet->setCellValue('C2', 'Debet');
        $sheet->setCellValue('D2', 'Kredit');
        $rowNum = 3;
        foreach ($data['rows'] as $row) {
            $sheet->setCellValue('A'.$rowNum, $row['no']);
            $sheet->setCellValue('B'.$rowNum, $row['nama']);
            $sheet->setCellValue('C'.$rowNum, $row['debet']);
            $sheet->setCellValue('D'.$rowNum, $row['kredit']);
            $rowNum++;
        }
        $sheet->setCellValue('A'.$rowNum, 'JUMLAH');
        $sheet->mergeCells('A'.$rowNum.':B'.$rowNum);
        $sheet->setCellValue('C'.$rowNum, $data['totalDebet']);
        $sheet->setCellValue('D'.$rowNum, $data['totalKredit']);
        $writer = new Xlsx($spreadsheet);
        $filename = 'laporan_neraca_saldo_'.$tgl_dari.'_'.$tgl_samp.'.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }
} 