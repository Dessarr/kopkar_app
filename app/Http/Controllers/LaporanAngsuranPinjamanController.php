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
    /**
     * Display the loan installment report page
     * This implements the accounting principle for loan installment tracking
     */
    public function index(Request $request)
    {
        // Get filter parameters with default values
        $tgl_dari = $request->input('tgl_dari', date('Y').'-01-01');
        $tgl_samp = $request->input('tgl_samp', date('Y').'-12-31');
        
        // Get detailed installment data with proper accounting logic
        $data = $this->getDetailAngsuran($tgl_dari, $tgl_samp);
        
        // Calculate summary statistics
        $summary = $this->calculateSummary($data);
        
        // Get performance metrics
        $performance = $this->calculatePerformanceMetrics($data);
        
        // Get recent installment activities
        $recentInstallments = $this->getRecentInstallments($tgl_dari, $tgl_samp);
        
        return view('laporan.angsuran_pinjaman', [
            'tgl_dari' => $tgl_dari,
            'tgl_samp' => $tgl_samp,
            'data' => $data,
            'summary' => $summary,
            'performance' => $performance,
            'recentInstallments' => $recentInstallments
        ]);
    }

    /**
     * Get detailed installment data with proper accounting principles
     * This implements the accounting principle for loan installment tracking
     */
    private function getDetailAngsuran($tgl_dari, $tgl_samp)
    {
        $angsuran = TblPinjamanD::with(['pinjaman.anggota'])
            ->whereDate('tgl_bayar', '>=', $tgl_dari)
            ->whereDate('tgl_bayar', '<=', $tgl_samp)
            ->whereHas('pinjaman', function($q) {
                $q->whereNotNull('anggota_id');
            })
            ->orderBy('tgl_bayar', 'asc')
            ->get();
        
        // Filter out installments without valid loan or member relationship
        $angsuran = $angsuran->filter(function($row) {
            return $row->pinjaman && $row->pinjaman->anggota;
        });
        
        $result = [];
        $no = 1;
        
        foreach ($angsuran as $row) {
            $pinjaman = $row->pinjaman;
            $anggota = $pinjaman->anggota;
            
            // Calculate loan balance before this installment
            $saldo_pinjaman = $pinjaman->jumlah ?? 0;
            
            // Get all previous installments for this loan
            $previousInstallments = TblPinjamanD::where('pinjam_id', $pinjaman->id)
                ->where('tgl_bayar', '<', $row->tgl_bayar)
                ->sum('jumlah_bayar');
            
            $saldo_pinjaman -= $previousInstallments;
            
            // Calculate installment components
            $pokok = $row->jumlah_bayar ?? 0;
            $bunga = $row->bunga ?? 0;
            $denda = $row->denda_rp ?? 0;
            $biaya_adm = $row->biaya_adm ?? 0;
            $jumlah_angsuran = $pokok + $bunga + $denda + $biaya_adm;
            
            // Calculate ending balance
            $saldo_akhir = $saldo_pinjaman - $pokok;
            
            // Determine installment status
            $status = $this->determineInstallmentStatus($row, $saldo_akhir);
            
            $result[] = [
                'no' => $no++,
                'tgl_pinjam' => $pinjaman->tgl_pinjam ?? '',
                'nama' => $anggota ? $anggota->nama : 'N/A',
                'id' => 'AG' . str_pad($anggota ? $anggota->id : 0, 4, '0', STR_PAD_LEFT),
                'id_anggota' => $anggota ? $anggota->id : 0,
                'jumlah' => $pinjaman->jumlah ?? 0, // Pinjaman awal
                'lama_angsuran' => $pinjaman->lama_angsuran ?? 0, // Jangka waktu
                'jumlah_bunga' => $pinjaman->bunga ?? 0, // Persentase bunga
                'saldo_pinjaman' => $saldo_pinjaman, // Saldo sebelum angsuran
                'pokok' => $pokok,
                'bunga' => $bunga,
                'denda' => $denda,
                'biaya_adm' => $biaya_adm,
                'jumlah_angsuran' => $jumlah_angsuran,
                'saldo_akhir' => $saldo_akhir,
                'angsuran_ke' => $row->angsuran_ke ?? 0,
                'tgl_bayar' => $row->tgl_bayar ?? '',
                'status' => $status,
                'status_badge' => $this->getInstallmentStatusBadge($status),
                'persentase_pelunasan' => $pinjaman->jumlah > 0 ? ((($pinjaman->jumlah - $saldo_akhir) / $pinjaman->jumlah) * 100) : 0
            ];
        }
        
        return $result;
    }

    /**
     * Determine installment status based on payment and balance
     */
    private function determineInstallmentStatus($installment, $saldo_akhir)
    {
        if ($saldo_akhir <= 0) {
            return 'Lunas';
        } elseif ($installment->denda_rp > 0) {
            return 'Terlambat';
        } elseif ($installment->tgl_bayar) {
            return 'Tepat Waktu';
        } else {
            return 'Belum Bayar';
        }
    }

    /**
     * Get installment status badge class for UI
     */
    private function getInstallmentStatusBadge($status)
    {
        if (empty($status)) {
            return 'secondary';
        }
        
        switch ($status) {
            case 'Lunas':
                return 'success';
            case 'Tepat Waktu':
                return 'info';
            case 'Terlambat':
                return 'warning';
            case 'Belum Bayar':
                return 'danger';
            default:
                return 'secondary';
        }
    }

    /**
     * Calculate summary statistics for the report
     */
    private function calculateSummary($data)
    {
        // Handle empty data
        if (empty($data)) {
            return [
                'total_angsuran' => 0,
                'total_pokok' => 0,
                'total_bunga' => 0,
                'total_denda' => 0,
                'total_biaya_adm' => 0,
                'total_jumlah_angsuran' => 0,
                'angsuran_lunas' => 0,
                'angsuran_tepat_waktu' => 0,
                'angsuran_terlambat' => 0,
                'angsuran_belum_bayar' => 0,
                'rata_rata_angsuran' => 0,
                'angsuran_tertinggi' => 0,
                'angsuran_terendah' => 0
            ];
        }
        
        $summary = [
            'total_angsuran' => count($data),
            'total_pokok' => 0,
            'total_bunga' => 0,
            'total_denda' => 0,
            'total_biaya_adm' => 0,
            'total_jumlah_angsuran' => 0,
            'angsuran_lunas' => 0,
            'angsuran_tepat_waktu' => 0,
            'angsuran_terlambat' => 0,
            'angsuran_belum_bayar' => 0
        ];
        
        $angsuran_values = [];
        
        foreach ($data as $row) {
            $summary['total_pokok'] += $row['pokok'];
            $summary['total_bunga'] += $row['bunga'];
            $summary['total_denda'] += $row['denda'];
            $summary['total_biaya_adm'] += $row['biaya_adm'];
            $summary['total_jumlah_angsuran'] += $row['jumlah_angsuran'];
            
            // Count by status
            switch ($row['status']) {
                case 'Lunas':
                    $summary['angsuran_lunas']++;
                    break;
                case 'Tepat Waktu':
                    $summary['angsuran_tepat_waktu']++;
                    break;
                case 'Terlambat':
                    $summary['angsuran_terlambat']++;
                    break;
                case 'Belum Bayar':
                    $summary['angsuran_belum_bayar']++;
                    break;
            }
            
            $angsuran_values[] = $row['jumlah_angsuran'];
        }
        
        // Calculate loan statistics
        $summary['rata_rata_angsuran'] = $summary['total_angsuran'] > 0 
            ? ($summary['total_jumlah_angsuran'] / $summary['total_angsuran']) 
            : 0;
        $summary['angsuran_tertinggi'] = !empty($angsuran_values) ? max($angsuran_values) : 0;
        $summary['angsuran_terendah'] = !empty($angsuran_values) ? min($angsuran_values) : 0;
        
        return $summary;
    }

    /**
     * Calculate performance metrics for analysis
     */
    private function calculatePerformanceMetrics($data)
    {
        if (empty($data) || !is_array($data)) {
            return [
                'rata_rata_pokok' => 0,
                'rata_rata_bunga' => 0,
                'rata_rata_denda' => 0,
                'persentase_tepat_waktu' => 0,
                'persentase_terlambat' => 0,
                'total_pinjaman_terlunasi' => 0
            ];
        }
        
        $total_angsuran = count($data);
        $tepat_waktu = 0;
        $terlambat = 0;
        $total_pokok = 0;
        $total_bunga = 0;
        $total_denda = 0;
        
        foreach ($data as $row) {
            $total_pokok += $row['pokok'];
            $total_bunga += $row['bunga'];
            $total_denda += $row['denda'];
            
            if ($row['status'] == 'Tepat Waktu') {
                $tepat_waktu++;
            } elseif ($row['status'] == 'Terlambat') {
                $terlambat++;
            }
        }
        
        return [
            'rata_rata_pokok' => $total_angsuran > 0 ? ($total_pokok / $total_angsuran) : 0,
            'rata_rata_bunga' => $total_angsuran > 0 ? ($total_bunga / $total_angsuran) : 0,
            'rata_rata_denda' => $total_angsuran > 0 ? ($total_denda / $total_angsuran) : 0,
            'persentase_tepat_waktu' => $total_angsuran > 0 ? (($tepat_waktu / $total_angsuran) * 100) : 0,
            'persentase_terlambat' => $total_angsuran > 0 ? (($terlambat / $total_angsuran) * 100) : 0,
            'total_pinjaman_terlunasi' => $total_pokok
        ];
    }

    /**
     * Get recent installment activities for monitoring
     */
    private function getRecentInstallments($tgl_dari, $tgl_samp)
    {
        return TblPinjamanD::with(['pinjaman.anggota'])
            ->whereDate('tgl_bayar', '>=', $tgl_dari)
            ->whereDate('tgl_bayar', '<=', $tgl_samp)
            ->whereHas('pinjaman', function($q) {
                $q->whereNotNull('anggota_id');
            })
            ->orderBy('tgl_bayar', 'desc')
            ->limit(10)
            ->get()
            ->filter(function($installment) {
                return $installment->pinjaman && $installment->pinjaman->anggota;
            })
            ->map(function ($installment) {
                $pinjaman = $installment->pinjaman;
                $anggota = $pinjaman->anggota;
                
                // Calculate installment components
                $pokok = $installment->jumlah_bayar ?? 0;
                $bunga = $installment->bunga ?? 0;
                $denda = $installment->denda_rp ?? 0;
                $biaya_adm = $installment->biaya_adm ?? 0;
                $jumlah_angsuran = $pokok + $bunga + $denda + $biaya_adm;
                
                // Determine status
                $status = $this->determineInstallmentStatus($installment, 0);
                
                return [
                    'id' => 'INS' . str_pad($installment->id, 5, '0', STR_PAD_LEFT),
                    'anggota' => $anggota ? $anggota->nama : 'N/A',
                    'pinjaman_id' => 'PNJ' . str_pad($pinjaman->id, 5, '0', STR_PAD_LEFT),
                    'jumlah_angsuran' => $jumlah_angsuran,
                    'tgl_bayar' => $installment->tgl_bayar ? Carbon::parse($installment->tgl_bayar)->format('d/m/Y') : '-',
                    'angsuran_ke' => $installment->angsuran_ke ?? 0,
                    'status' => $status,
                    'pokok' => $pokok,
                    'bunga' => $bunga,
                    'denda' => $denda
                ];
            });
    }

    public function exportPdf(Request $request)
    {
        $tgl_dari = $request->input('tgl_dari', date('Y').'-01-01');
        $tgl_samp = $request->input('tgl_samp', date('Y').'-12-31');
        
        // Get detailed data
        $data = $this->getDetailAngsuran($tgl_dari, $tgl_samp);
        $summary = $this->calculateSummary($data);
        $performance = $this->calculatePerformanceMetrics($data);
        $recentInstallments = $this->getRecentInstallments($tgl_dari, $tgl_samp);
        
        $pdf = Pdf::loadView('laporan.pdf.angsuran_pinjaman', [
            'tgl_dari' => $tgl_dari,
            'tgl_samp' => $tgl_samp,
            'data' => $data,
            'summary' => $summary,
            'performance' => $performance,
            'recentInstallments' => $recentInstallments
        ])->setPaper('a4', 'landscape');
        
        return $pdf->download('laporan_angsuran_pinjaman_'.$tgl_dari.'_'.$tgl_samp.'.pdf');
    }

    public function exportExcel(Request $request)
    {
        $tgl_dari = $request->input('tgl_dari', date('Y').'-01-01');
        $tgl_samp = $request->input('tgl_samp', date('Y').'-12-31');
        
        // Get detailed data
        $data = $this->getDetailAngsuran($tgl_dari, $tgl_samp);
        $summary = $this->calculateSummary($data);
        $performance = $this->calculatePerformanceMetrics($data);
        $recentInstallments = $this->getRecentInstallments($tgl_dari, $tgl_samp);
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set title
        $sheet->setCellValue('A1', 'LAPORAN ANGSURAN PINJAMAN');
        $sheet->setCellValue('A2', 'Koperasi Karyawan');
        $sheet->setCellValue('A3', 'Periode: ' . Carbon::parse($tgl_dari)->format('d M Y') . ' - ' . Carbon::parse($tgl_samp)->format('d M Y'));
        $sheet->setCellValue('A4', 'Dicetak pada: ' . Carbon::now()->format('d M Y H:i:s'));
        $sheet->mergeCells('A1:Q1');
        $sheet->mergeCells('A2:Q2');
        $sheet->mergeCells('A3:Q3');
        $sheet->mergeCells('A4:Q4');
        
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
        
        $rowNum++;
        $sheet->setCellValue('A'.$rowNum, 'Total Angsuran:');
        $sheet->setCellValue('B'.$rowNum, $summary['total_angsuran']);
        $sheet->setCellValue('A'.($rowNum+1), 'Total Pokok:');
        $sheet->setCellValue('B'.($rowNum+1), 'Rp ' . number_format($summary['total_pokok'], 0, ',', '.'));
        $sheet->setCellValue('A'.($rowNum+2), 'Total Bunga:');
        $sheet->setCellValue('B'.($rowNum+2), 'Rp ' . number_format($summary['total_bunga'], 0, ',', '.'));
        $sheet->setCellValue('A'.($rowNum+3), 'Total Denda:');
        $sheet->setCellValue('B'.($rowNum+3), 'Rp ' . number_format($summary['total_denda'], 0, ',', '.'));
        $sheet->setCellValue('A'.($rowNum+4), 'Total Jumlah Angsuran:');
        $sheet->setCellValue('B'.($rowNum+4), 'Rp ' . number_format($summary['total_jumlah_angsuran'], 0, ',', '.'));
        
        // Status overview
        $sheet->setCellValue('D'.$rowNum, 'STATUS ANGSURAN');
        $sheet->mergeCells('D'.$rowNum.':E'.$rowNum);
        $sheet->getStyle('D'.$rowNum)->getFont()->setBold(true);
        
        $sheet->setCellValue('D'.($rowNum+1), 'Lunas:');
        $sheet->setCellValue('E'.($rowNum+1), $summary['angsuran_lunas']);
        $sheet->setCellValue('D'.($rowNum+2), 'Tepat Waktu:');
        $sheet->setCellValue('E'.($rowNum+2), $summary['angsuran_tepat_waktu']);
        $sheet->setCellValue('D'.($rowNum+3), 'Terlambat:');
        $sheet->setCellValue('E'.($rowNum+3), $summary['angsuran_terlambat']);
        $sheet->setCellValue('D'.($rowNum+4), 'Belum Bayar:');
        $sheet->setCellValue('E'.($rowNum+4), $summary['angsuran_belum_bayar']);
        
        // Performance metrics
        $sheet->setCellValue('G'.$rowNum, 'METRIK KINERJA');
        $sheet->mergeCells('G'.$rowNum.':H'.$rowNum);
        $sheet->getStyle('G'.$rowNum)->getFont()->setBold(true);
        
        $sheet->setCellValue('G'.($rowNum+1), 'Rata-rata Angsuran:');
        $sheet->setCellValue('H'.($rowNum+1), 'Rp ' . number_format($summary['rata_rata_angsuran'], 0, ',', '.'));
        $sheet->setCellValue('G'.($rowNum+2), 'Tingkat Ketepatan Waktu:');
        $sheet->setCellValue('H'.($rowNum+2), number_format($performance['persentase_tepat_waktu'], 2) . '%');
        $sheet->setCellValue('G'.($rowNum+3), 'Tingkat Terlambat:');
        $sheet->setCellValue('H'.($rowNum+3), number_format($performance['persentase_terlambat'], 2) . '%');
        $sheet->setCellValue('G'.($rowNum+4), 'Total Pinjaman Terlunasi:');
        $sheet->setCellValue('H'.($rowNum+4), 'Rp ' . number_format($performance['total_pinjaman_terlunasi'], 0, ',', '.'));
        
        // Set headers
        $headers = [
            'No', 'Tanggal Pinjam', 'Nama', 'ID', 'Pinjaman Awal', 'JW', '%', 
            'Saldo Pinjaman', 'Pokok', 'Bunga', 'Denda', 'Biaya Adm', 'Jumlah', 
            'Saldo Akhir', 'Angsuran Ke', 'Tgl. Bayar', 'Status'
        ];
        
        $col = 1;
        $startRow = $rowNum + 6;
        foreach ($headers as $header) {
            $sheet->setCellValueByColumnAndRow($col, $startRow, $header);
            $col++;
        }
        
        // Style headers
        $sheet->getStyle('A'.$startRow.':Q'.$startRow)->getFont()->setBold(true);
        $sheet->getStyle('A'.$startRow.':Q'.$startRow)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE0E0E0');
        
        // Add data
        $rowNum = $startRow + 1;
        foreach ($data as $row) {
            $sheet->setCellValue('A'.$rowNum, $row['no']);
            $sheet->setCellValue('B'.$rowNum, Carbon::parse($row['tgl_pinjam'])->format('d/m/Y'));
            $sheet->setCellValue('C'.$rowNum, $row['nama']);
            $sheet->setCellValue('D'.$rowNum, $row['id']);
            $sheet->setCellValue('E'.$rowNum, $row['jumlah']);
            $sheet->setCellValue('F'.$rowNum, $row['lama_angsuran']);
            $sheet->setCellValue('G'.$rowNum, $row['jumlah_bunga']);
            $sheet->setCellValue('H'.$rowNum, $row['saldo_pinjaman']);
            $sheet->setCellValue('I'.$rowNum, $row['pokok']);
            $sheet->setCellValue('J'.$rowNum, $row['bunga']);
            $sheet->setCellValue('K'.$rowNum, $row['denda']);
            $sheet->setCellValue('L'.$rowNum, $row['biaya_adm']);
            $sheet->setCellValue('M'.$rowNum, $row['jumlah_angsuran']);
            $sheet->setCellValue('N'.$rowNum, $row['saldo_akhir']);
            $sheet->setCellValue('O'.$rowNum, $row['angsuran_ke']);
            $sheet->setCellValue('P'.$rowNum, Carbon::parse($row['tgl_bayar'])->format('d/m/Y'));
            $sheet->setCellValue('Q'.$rowNum, $row['status']);
            $rowNum++;
        }
        
        // Add summary row
        if (count($data) > 0) {
            $sheet->setCellValue('A'.$rowNum, 'TOTAL');
            $sheet->mergeCells('A'.$rowNum.':D'.$rowNum);
            $sheet->setCellValue('I'.$rowNum, $summary['total_pokok']);
            $sheet->setCellValue('J'.$rowNum, $summary['total_bunga']);
            $sheet->setCellValue('K'.$rowNum, $summary['total_denda']);
            $sheet->setCellValue('L'.$rowNum, $summary['total_biaya_adm']);
            $sheet->setCellValue('M'.$rowNum, $summary['total_jumlah_angsuran']);
            
            // Style summary row
            $sheet->getStyle('A'.$rowNum.':Q'.$rowNum)->getFont()->setBold(true);
            $sheet->getStyle('A'.$rowNum.':Q'.$rowNum)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFFFE0B2');
        }
        
        // Recent installments section
        if ($recentInstallments->count() > 0) {
            $recentStartRow = $rowNum + 2;
            $sheet->setCellValue('A'.$recentStartRow, 'AKTIVITAS ANGSURAN TERBARU');
            $sheet->mergeCells('A'.$recentStartRow.':E'.$recentStartRow);
            $sheet->getStyle('A'.$recentStartRow)->getFont()->setBold(true);
            
            $recentStartRow++;
            $sheet->setCellValue('A'.$recentStartRow, 'Nama');
            $sheet->setCellValue('B'.$recentStartRow, 'ID');
            $sheet->setCellValue('C'.$recentStartRow, 'Tanggal Bayar');
            $sheet->setCellValue('D'.$recentStartRow, 'Jumlah');
            $sheet->setCellValue('E'.$recentStartRow, 'Angsuran Ke');
            
            // Style recent headers
            $sheet->getStyle('A'.$recentStartRow.':E'.$recentStartRow)->getFont()->setBold(true);
            $sheet->getStyle('A'.$recentStartRow.':E'.$recentStartRow)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFF0F8FF');
            
            $recentRow = $recentStartRow + 1;
            foreach ($recentInstallments as $installment) {
                $sheet->setCellValue('A'.$recentRow, $installment['anggota']);
                $sheet->setCellValue('B'.$recentRow, $installment['id']);
                $sheet->setCellValue('C'.$recentRow, $installment['tgl_bayar']);
                $sheet->setCellValue('D'.$recentRow, 'Rp ' . number_format($installment['jumlah_angsuran'], 0, ',', '.'));
                $sheet->setCellValue('E'.$recentRow, $installment['angsuran_ke']);
                $recentRow++;
            }
        }
        
        // Auto-size columns
        foreach (range('A', 'Q') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
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