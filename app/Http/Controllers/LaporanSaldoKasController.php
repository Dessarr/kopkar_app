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
        
        // Get detailed cash balance data using v_transaksi
        $data = $this->getSaldoKasFromView($periode);
        
        return view('laporan.saldo_kas', [
            'periode' => $periode,
            'data' => $data['rows'],
            'saldo_sblm' => $data['saldo_sblm'],
            'total' => $data['total']
        ]);
    }

    private function getSaldoKasFromView($periode)
    {
        $tglArr = explode('-', $periode);
        $thn = $tglArr[0];
        $bln = $tglArr[1];
        
        // Get specific cash accounts only - exclude Bank Mandiri
        $kasList = DB::table('nama_kas_tbl')
            ->where('aktif', 'Y')
            ->whereIn('nama', ['Kas Tunai', 'Kas Besar', 'Bank BCA', 'Bank BNI'])
            ->select(DB::raw('MIN(id) as id'), 'nama')
            ->groupBy('nama')
            ->orderBy('nama')
            ->get();
        
        // Calculate previous period balance using v_transaksi
        $saldo_sblm = DB::table('v_transaksi')
            ->where(function($q) use ($thn, $bln) {
                $q->whereYear('tgl', '<', $thn)
                  ->orWhere(function($q2) use ($thn, $bln) {
                      $q2->whereYear('tgl', $thn)
                         ->whereMonth('tgl', '<', $bln);
                  });
            })
            ->select(DB::raw('SUM(debet) as jum_debet'),
                     DB::raw('SUM(kredit) as jum_kredit'))
            ->first();
        $saldo_sblm_val = ($saldo_sblm->jum_debet ?? 0) - ($saldo_sblm->jum_kredit ?? 0);
        
        $rows = [];
        $total_saldo = 0;
        $no = 1;
        
        foreach ($kasList as $kas) {
            // Calculate debet (cash in) for current period using v_transaksi
            $debet = DB::table('v_transaksi')
                ->where('untuk_kas', $kas->id)
                ->whereYear('tgl', $thn)
                ->whereMonth('tgl', $bln)
                ->sum('debet');
                
            // Calculate credit (cash out) for current period using v_transaksi
            $kredit = DB::table('v_transaksi')
                ->where('dari_kas', $kas->id)
                ->whereYear('tgl', $thn)
                ->whereMonth('tgl', $bln)
                ->sum('kredit');
                
            // Calculate net cash flow for this account
            $saldo = $debet - $kredit;
            
            $rows[] = [
                'no' => $no++,
                'id' => $kas->id,
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
        
        // Get detailed data using v_transaksi
        $data = $this->getSaldoKasFromView($periode);
        
        $pdf = Pdf::loadView('laporan.pdf.saldo_kas', [
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
        
        // Get detailed data using v_transaksi
        $data = $this->getSaldoKasFromView($periode);
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set title
        $sheet->setCellValue('A1', 'LAPORAN SALDO KAS');
        $sheet->setCellValue('A2', 'Koperasi Karyawan');
        $sheet->setCellValue('A3', 'Periode: ' . Carbon::parse($periode . '-01')->format('M Y'));
        $sheet->setCellValue('A4', 'Dicetak pada: ' . Carbon::now()->format('d M Y H:i:s'));
        $sheet->mergeCells('A1:F1');
        $sheet->mergeCells('A2:F2');
        $sheet->mergeCells('A3:F3');
        $sheet->mergeCells('A4:F4');
        
        // Style title
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A3')->getFont()->setSize(12);
        $sheet->getStyle('A4')->getFont()->setSize(10);
        
        // Summary section
        $rowNum = 6;
        $sheet->setCellValue('A'.$rowNum, 'RINGKASAN LAPORAN');
        $sheet->mergeCells('A'.$rowNum.':B'.$rowNum);
        $sheet->getStyle('A'.$rowNum)->getFont()->setBold(true);
        
        $total_debet = array_sum(array_column($data['rows'], 'debet'));
        $total_kredit = array_sum(array_column($data['rows'], 'kredit'));
        $total_saldo_akhir = $data['total'] + $data['saldo_sblm'];
        
        $rowNum++;
        $sheet->setCellValue('A'.$rowNum, 'Total Saldo Akhir:');
        $sheet->setCellValue('B'.$rowNum, 'Rp ' . number_format($total_saldo_akhir, 0, ',', '.'));
        $sheet->setCellValue('A'.($rowNum+1), 'Total Debet:');
        $sheet->setCellValue('B'.($rowNum+1), 'Rp ' . number_format($total_debet, 0, ',', '.'));
        $sheet->setCellValue('A'.($rowNum+2), 'Total Kredit:');
        $sheet->setCellValue('B'.($rowNum+2), 'Rp ' . number_format($total_kredit, 0, ',', '.'));
        $sheet->setCellValue('A'.($rowNum+3), 'Saldo Periode Sebelumnya:');
        $sheet->setCellValue('B'.($rowNum+3), 'Rp ' . number_format($data['saldo_sblm'], 0, ',', '.'));
        
        // Set headers
        $headers = ['No', 'Nama Kas', 'Saldo'];
        $col = 1;
        $startRow = $rowNum + 5;
        foreach ($headers as $header) {
            $sheet->setCellValueByColumnAndRow($col, $startRow, $header);
            $col++;
        }
        
        // Style headers
        $sheet->getStyle('A'.$startRow.':C'.$startRow)->getFont()->setBold(true);
        $sheet->getStyle('A'.$startRow.':C'.$startRow)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE0E0E0');
        
        // Add data
        $rowNum = $startRow + 1;
        foreach ($data['rows'] as $row) {
            $sheet->setCellValue('A'.$rowNum, $row['no']);
            $sheet->setCellValue('B'.$rowNum, $row['nama']);
            $sheet->setCellValue('C'.$rowNum, $row['saldo']);
            $rowNum++;
        }
        
        // Add summary rows
        if (count($data['rows']) > 0) {
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
            
            // Style summary rows
            $sheet->getStyle('A'.($rowNum-2).':C'.$rowNum)->getFont()->setBold(true);
            $sheet->getStyle('A'.($rowNum-2).':C'.$rowNum)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFFFE0B2');
        }
        
        
        // Auto-size columns
        foreach (range('A', 'C') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        $writer = new Xlsx($spreadsheet);
        $filename = 'laporan_saldo_kas_'.$periode.'.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }
} 