<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\data_anggota;
use App\Models\TransaksiSimpanan;
use App\Models\jns_simpan;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LaporanKasSimpananController extends Controller
{
    public function index(Request $request)
    {
        $periode = $request->input('periode', date('Y-m'));
        $anggotaList = data_anggota::where('aktif', 'Y')->orderBy('nama')->get();
        $jenisSimpanan = jns_simpan::whereIn('id', [41, 32, 52, 40, 51, 31])->orderBy('urut')->get();
        $data = $this->getRekapSimpanan($anggotaList, $jenisSimpanan, $periode);
        return view('laporan.kas_simpanan', [
            'anggotaList' => $anggotaList,
            'jenisSimpanan' => $jenisSimpanan,
            'periode' => $periode,
            'data' => $data
        ]);
    }

    private function getRekapSimpanan($anggotaList, $jenisSimpanan, $periode)
    {
        $result = [];
        $tglArr = explode('-', $periode);
        $thn = $tglArr[0];
        $bln = $tglArr[1];
        $no = 1;
        foreach ($anggotaList as $anggota) {
            $row = [
                'no' => $no++,
                'id' => $anggota->id,
                'nama' => $anggota->nama
            ];
            foreach ($jenisSimpanan as $jenis) {
                $jumlah = TransaksiSimpanan::where('no_ktp', $anggota->no_ktp)
                    ->where('jenis_id', $jenis->id)
                    ->whereYear('tgl_transaksi', $thn)
                    ->whereMonth('tgl_transaksi', $bln)
                    ->sum('jumlah');
                $row[$jenis->id] = $jumlah;
            }
            $result[] = $row;
        }
        return $result;
    }

    public function exportPdf(Request $request)
    {
        $periode = $request->input('periode', date('Y-m'));
        $anggotaList = data_anggota::where('aktif', 'Y')->orderBy('nama')->get();
        $jenisSimpanan = jns_simpan::whereIn('id', [41, 32, 52, 40, 51, 31])->orderBy('urut')->get();
        $data = $this->getRekapSimpanan($anggotaList, $jenisSimpanan, $periode);
        $pdf = Pdf::loadView('laporan.kas_simpanan_pdf', [
            'periode' => $periode,
            'jenisSimpanan' => $jenisSimpanan,
            'data' => $data
        ]);
        return $pdf->download('laporan_kas_simpanan_'.$periode.'.pdf');
    }

    public function exportExcel(Request $request)
    {
        $periode = $request->input('periode', date('Y-m'));
        $anggotaList = data_anggota::where('aktif', 'Y')->orderBy('nama')->get();
        $jenisSimpanan = jns_simpan::whereIn('id', [41, 32, 52, 40, 51, 31])->orderBy('urut')->get();
        $data = $this->getRekapSimpanan($anggotaList, $jenisSimpanan, $periode);
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'LAPORAN KAS SIMPANAN Periode '.$periode);
        $sheet->mergeCells('A1:'.chr(67+count($jenisSimpanan)-1).'1');
        $sheet->setCellValue('A2', 'No');
        $sheet->setCellValue('B2', 'ID');
        $sheet->setCellValue('C2', 'Nama');
        $col = 4;
        foreach ($jenisSimpanan as $jenis) {
            $sheet->setCellValue(chr(64+$col).'2', $jenis->jns_simpan);
            $col++;
        }
        $rowNum = 3;
        foreach ($data as $row) {
            $sheet->setCellValue('A'.$rowNum, $row['no']);
            $sheet->setCellValue('B'.$rowNum, $row['id']);
            $sheet->setCellValue('C'.$rowNum, $row['nama']);
            $col = 4;
            foreach ($jenisSimpanan as $jenis) {
                $sheet->setCellValue(chr(64+$col).$rowNum, $row[$jenis->id]);
                $col++;
            }
            $rowNum++;
        }
        $writer = new Xlsx($spreadsheet);
        $filename = 'laporan_kas_simpanan_'.$periode.'.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }
} 