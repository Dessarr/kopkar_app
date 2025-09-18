<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TblPinjamanH;
use App\Models\TblPinjamanD;
use App\Models\TempoPinjaman;
use App\Models\data_anggota;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\DB;

class LaporanRekapitulasiController extends Controller
{
    /**
     * Display the recapitulation report page
     * This implements the accounting principle for target vs realization analysis
     */
    public function index(Request $request)
    {
        // Get filter parameters with default values
        $periode = $request->input('periode', date('Y-m'));
        
        // Get detailed recapitulation data using v_rekap
        $data = $this->getRekapDataFromView($periode);
        
        return view('laporan.rekapitulasi', [
            'periode' => $periode,
            'data' => $data
        ]);
    }

    /**
     * Get detailed recapitulation data using v_rekap view
     * This implements the accounting principle for target vs realization analysis
     */
    private function getRekapDataFromView($periode)
    {
        $tglArr = explode('-', $periode);
        $thn = $tglArr[0];
        $bln = $tglArr[1];
        
        // Get data from v_rekap for the specified period
        $data = DB::table('v_rekap')
            ->whereYear('tgl_bayar', $thn)
            ->whereMonth('tgl_bayar', $bln)
            ->orderBy('tgl_bayar')
            ->get();
        
        $result = [];
        $no = 1;
        
        foreach ($data as $row) {
            // Calculate collection rate
            $persentase_koleksi = $row->tagihan_hari_ini > 0 ? (($row->tagihan_masuk / $row->tagihan_hari_ini) * 100) : 0;
            
            // Determine status
            $status = $this->determineDayStatus($persentase_koleksi, $row->tagihan_bermasalah);
            
            $result[] = [
                'no' => $no++,
                'tanggal' => $row->tgl_bayar,
                'jml_tagihan' => $row->tagihan_hari_ini,
                'target_pokok' => $row->target_pokok,
                'target_bunga' => $row->target_bunga,
                'tagihan_masuk' => $row->tagihan_masuk,
                'realisasi_pokok' => $row->realisasi_pokok,
                'realisasi_bunga' => $row->realisasi_bunga,
                'tagihan_bermasalah' => $row->tagihan_bermasalah,
                'tidak_bayar_pokok' => $row->tidak_bayar_pokok,
                'tidak_bayar_bunga' => $row->tidak_bayar_bunga,
                'persentase_koleksi' => round($persentase_koleksi, 2),
                'status' => $status,
                'status_badge' => $this->getStatusBadge($status)
            ];
        }
        
        return $result;
    }

    /**
     * Determine day status based on collection rate and problem loans
     */
    private function determineDayStatus($persentase_koleksi, $tagihan_bermasalah)
    {
        if ($tagihan_bermasalah == 0) {
            return 'Sempurna';
        } elseif ($persentase_koleksi >= 90) {
            return 'Sangat Baik';
        } elseif ($persentase_koleksi >= 75) {
            return 'Baik';
        } elseif ($persentase_koleksi >= 50) {
            return 'Cukup';
        } else {
            return 'Perlu Perhatian';
        }
    }

    /**
     * Get status badge class for UI
     */
    private function getStatusBadge($status)
    {
        switch ($status) {
            case 'Sempurna':
                return 'success';
            case 'Sangat Baik':
                return 'info';
            case 'Baik':
                return 'primary';
            case 'Cukup':
                return 'warning';
            case 'Perlu Perhatian':
                return 'danger';
            default:
                return 'secondary';
        }
    }



    public function exportPdf(Request $request)
    {
        $periode = $request->input('periode', date('Y-m'));
        
        // Get detailed data using v_rekap
        $data = $this->getRekapDataFromView($periode);
        
        $pdf = Pdf::loadView('laporan.pdf.rekapitulasi', [
            'periode' => $periode,
            'data' => $data
        ])->setPaper('a4', 'landscape');
        
        return $pdf->download('laporan_rekapitulasi_'.$periode.'.pdf');
    }

    public function exportExcel(Request $request)
    {
        $periode = $request->input('periode', date('Y-m'));
        
        // Get detailed data using v_rekap
        $data = $this->getRekapDataFromView($periode);
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set title
        $sheet->setCellValue('A1', 'LAPORAN REKAPITULASI TAGIHAN');
        $sheet->setCellValue('A2', 'Koperasi Karyawan');
        $sheet->setCellValue('A3', 'Periode: ' . Carbon::parse($periode . '-01')->format('M Y'));
        $sheet->setCellValue('A4', 'Dicetak pada: ' . Carbon::now()->format('d M Y H:i:s'));
        $sheet->mergeCells('A1:M1');
        $sheet->mergeCells('A2:M2');
        $sheet->mergeCells('A3:M3');
        $sheet->mergeCells('A4:M4');
        
        // Style title
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A3')->getFont()->setSize(12);
        $sheet->getStyle('A4')->getFont()->setSize(10);
        
        // Calculate summary from data
        $total_tagihan = array_sum(array_column($data, 'jml_tagihan'));
        $total_target_pokok = array_sum(array_column($data, 'target_pokok'));
        $total_target_bunga = array_sum(array_column($data, 'target_bunga'));
        $total_realisasi_pokok = array_sum(array_column($data, 'realisasi_pokok'));
        $total_realisasi_bunga = array_sum(array_column($data, 'realisasi_bunga'));
        $total_tagihan_bermasalah = array_sum(array_column($data, 'tagihan_bermasalah'));
        $rata_rata_koleksi = !empty($data) ? array_sum(array_column($data, 'persentase_koleksi')) / count($data) : 0;
        
        // Summary section
        $rowNum = 6;
        $sheet->setCellValue('A'.$rowNum, 'RINGKASAN LAPORAN');
        $sheet->mergeCells('A'.$rowNum.':B'.$rowNum);
        $sheet->getStyle('A'.$rowNum)->getFont()->setBold(true);
        
        $rowNum++;
        $sheet->setCellValue('A'.$rowNum, 'Total Tagihan:');
        $sheet->setCellValue('B'.$rowNum, $total_tagihan);
        $sheet->setCellValue('A'.($rowNum+1), 'Total Target Pokok:');
        $sheet->setCellValue('B'.($rowNum+1), 'Rp ' . number_format($total_target_pokok, 0, ',', '.'));
        $sheet->setCellValue('A'.($rowNum+2), 'Total Target Bunga:');
        $sheet->setCellValue('B'.($rowNum+2), 'Rp ' . number_format($total_target_bunga, 0, ',', '.'));
        $sheet->setCellValue('A'.($rowNum+3), 'Total Realisasi Pokok:');
        $sheet->setCellValue('B'.($rowNum+3), 'Rp ' . number_format($total_realisasi_pokok, 0, ',', '.'));
        $sheet->setCellValue('A'.($rowNum+4), 'Total Realisasi Bunga:');
        $sheet->setCellValue('B'.($rowNum+4), 'Rp ' . number_format($total_realisasi_bunga, 0, ',', '.'));
        $sheet->setCellValue('A'.($rowNum+5), 'Rata-rata Koleksi:');
        $sheet->setCellValue('B'.($rowNum+5), number_format($rata_rata_koleksi, 2) . '%');
        
        // Set headers
        $headers = [
            'No', 'Tanggal', 'Tagihan Hari Ini', 'Target Pokok', 'Target Bunga', 
            'Tagihan Masuk', 'Realisasi Pokok', 'Realisasi Bunga', 'Tagihan Bermasalah', 
            'Tidak Bayar Pokok', 'Tidak Bayar Bunga', 'Persentase Koleksi', 'Status'
        ];
        
        $col = 1;
        $startRow = $rowNum + 7;
        foreach ($headers as $header) {
            $sheet->setCellValueByColumnAndRow($col, $startRow, $header);
            $col++;
        }
        
        // Style headers
        $sheet->getStyle('A'.$startRow.':M'.$startRow)->getFont()->setBold(true);
        $sheet->getStyle('A'.$startRow.':M'.$startRow)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE0E0E0');
        
        // Add data
        $rowNum = $startRow + 1;
        foreach ($data as $row) {
            $sheet->setCellValue('A'.$rowNum, $row['no']);
            $sheet->setCellValue('B'.$rowNum, Carbon::parse($row['tanggal'])->format('d/m/Y'));
            $sheet->setCellValue('C'.$rowNum, $row['jml_tagihan']);
            $sheet->setCellValue('D'.$rowNum, $row['target_pokok']);
            $sheet->setCellValue('E'.$rowNum, $row['target_bunga']);
            $sheet->setCellValue('F'.$rowNum, $row['tagihan_masuk']);
            $sheet->setCellValue('G'.$rowNum, $row['realisasi_pokok']);
            $sheet->setCellValue('H'.$rowNum, $row['realisasi_bunga']);
            $sheet->setCellValue('I'.$rowNum, $row['tagihan_bermasalah']);
            $sheet->setCellValue('J'.$rowNum, $row['tidak_bayar_pokok']);
            $sheet->setCellValue('K'.$rowNum, $row['tidak_bayar_bunga']);
            $sheet->setCellValue('L'.$rowNum, $row['persentase_koleksi'] . '%');
            $sheet->setCellValue('M'.$rowNum, $row['status']);
            $rowNum++;
        }
        
        // Add summary row
        if (count($data) > 0) {
            $sheet->setCellValue('A'.$rowNum, 'TOTAL');
            $sheet->mergeCells('A'.$rowNum.':B'.$rowNum);
            $sheet->setCellValue('C'.$rowNum, $total_tagihan);
            $sheet->setCellValue('D'.$rowNum, $total_target_pokok);
            $sheet->setCellValue('E'.$rowNum, $total_target_bunga);
            $sheet->setCellValue('F'.$rowNum, array_sum(array_column($data, 'tagihan_masuk')));
            $sheet->setCellValue('G'.$rowNum, $total_realisasi_pokok);
            $sheet->setCellValue('H'.$rowNum, $total_realisasi_bunga);
            $sheet->setCellValue('I'.$rowNum, $total_tagihan_bermasalah);
            $sheet->setCellValue('J'.$rowNum, array_sum(array_column($data, 'tidak_bayar_pokok')));
            $sheet->setCellValue('K'.$rowNum, array_sum(array_column($data, 'tidak_bayar_bunga')));
            $sheet->setCellValue('L'.$rowNum, number_format($rata_rata_koleksi, 2) . '%');
            $sheet->setCellValue('M'.$rowNum, 'BULANAN');
            
            // Style summary row
            $sheet->getStyle('A'.$rowNum.':M'.$rowNum)->getFont()->setBold(true);
            $sheet->getStyle('A'.$rowNum.':M'.$rowNum)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFFFE0B2');
        }
        
        
        // Auto-size columns
        foreach (range('A', 'M') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
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