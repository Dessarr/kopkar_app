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
        
        // Get detailed recapitulation data with proper accounting logic
        $data = $this->getRekapBulanan($periode);
        
        // Calculate summary statistics
        $summary = $this->calculateSummary($data);
        
        // Get performance metrics
        $performance = $this->calculatePerformanceMetrics($data);
        
        // Get recent activities
        $recentActivities = $this->getRecentActivities($periode);
        
        return view('laporan.rekapitulasi', [
            'periode' => $periode,
            'data' => $data,
            'summary' => $summary,
            'performance' => $performance,
            'recentActivities' => $recentActivities
        ]);
    }

    /**
     * Get detailed monthly recapitulation data with proper accounting principles
     * This implements the accounting principle for target vs realization analysis
     */
    private function getRekapBulanan($periode)
    {
        $tglArr = explode('-', $periode);
        $thn = $tglArr[0];
        $bln = $tglArr[1];
        $days = cal_days_in_month(CAL_GREGORIAN, $bln, $thn);
        $result = [];
        
        for ($i = 1; $i <= $days; $i++) {
            $tgl = sprintf('%s-%02d-%02d', $thn, $bln, $i);
            
            // Get loans due on this date
            $tempoPinjaman = TempoPinjaman::whereDate('tempo', $tgl)
                ->whereHas('pinjaman', function($q) {
                    $q->whereNotNull('anggota_id');
                })
                ->get();
            
            $jml_tagihan = $tempoPinjaman->count();
            $pinjam_ids = $tempoPinjaman->pluck('pinjam_id')->toArray();
            
            // Calculate target amounts using proper accounting principles
            $target_pokok = 0;
            $target_bunga = 0;
            
            if (!empty($pinjam_ids)) {
                $pinjamanData = TblPinjamanH::whereIn('id', $pinjam_ids)
                    ->whereNotNull('anggota_id')
                    ->get();
                
                foreach ($pinjamanData as $pinjaman) {
                    // Target pokok = jumlah pinjaman / lama angsuran
                    $target_pokok += ($pinjaman->jumlah ?? 0) / max($pinjaman->lama_angsuran ?? 1, 1);
                    
                    // Target bunga = (jumlah pinjaman * bunga) / 100
                    $target_bunga += (($pinjaman->jumlah ?? 0) * ($pinjaman->bunga ?? 0)) / 100;
                }
            }
            
            // Get actual payments made on this date
            $tagihan_masuk = TblPinjamanD::whereDate('tgl_bayar', $tgl)
                ->whereHas('pinjaman', function($q) {
                    $q->whereNotNull('anggota_id');
                })
                ->count();
            
            $realisasi_pokok = TblPinjamanD::whereDate('tgl_bayar', $tgl)
                ->whereHas('pinjaman', function($q) {
                    $q->whereNotNull('anggota_id');
                })
                ->sum('jumlah_bayar');
            
            $realisasi_bunga = TblPinjamanD::whereDate('tgl_bayar', $tgl)
                ->whereHas('pinjaman', function($q) {
                    $q->whereNotNull('anggota_id');
                })
                ->sum('bunga');
            
            // Calculate variances and problem loans
            $tagihan_bermasalah = max(0, $jml_tagihan - $tagihan_masuk);
            $tidak_bayar_pokok = max(0, $target_pokok - $realisasi_pokok);
            $tidak_bayar_bunga = max(0, $target_bunga - $realisasi_bunga);
            
            // Calculate collection rate
            $persentase_koleksi = $jml_tagihan > 0 ? (($tagihan_masuk / $jml_tagihan) * 100) : 0;
            
            // Determine status
            $status = $this->determineDayStatus($persentase_koleksi, $tagihan_bermasalah);
            
            $result[] = [
                'no' => $i,
                'tanggal' => $tgl,
                'jml_tagihan' => $jml_tagihan,
                'target_pokok' => round($target_pokok, 2),
                'target_bunga' => round($target_bunga, 2),
                'tagihan_masuk' => $tagihan_masuk,
                'realisasi_pokok' => round($realisasi_pokok, 2),
                'realisasi_bunga' => round($realisasi_bunga, 2),
                'tagihan_bermasalah' => $tagihan_bermasalah,
                'tidak_bayar_pokok' => round($tidak_bayar_pokok, 2),
                'tidak_bayar_bunga' => round($tidak_bayar_bunga, 2),
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

    /**
     * Calculate summary statistics for the report
     */
    private function calculateSummary($data)
    {
        if (empty($data)) {
            return [
                'total_tagihan' => 0,
                'total_target_pokok' => 0,
                'total_target_bunga' => 0,
                'total_realisasi_pokok' => 0,
                'total_realisasi_bunga' => 0,
                'total_tagihan_bermasalah' => 0,
                'total_tidak_bayar_pokok' => 0,
                'total_tidak_bayar_bunga' => 0,
                'rata_rata_koleksi' => 0,
                'hari_terbaik' => 0,
                'hari_terburuk' => 0
            ];
        }

        $summary = [
            'total_tagihan' => 0,
            'total_target_pokok' => 0,
            'total_target_bunga' => 0,
            'total_realisasi_pokok' => 0,
            'total_realisasi_bunga' => 0,
            'total_tagihan_bermasalah' => 0,
            'total_tidak_bayar_pokok' => 0,
            'total_tidak_bayar_bunga' => 0
        ];

        $koleksi_rates = [];
        $terbaik = 0;
        $terburuk = 100;

        foreach ($data as $row) {
            $summary['total_tagihan'] += $row['jml_tagihan'];
            $summary['total_target_pokok'] += $row['target_pokok'];
            $summary['total_target_bunga'] += $row['target_bunga'];
            $summary['total_realisasi_pokok'] += $row['realisasi_pokok'];
            $summary['total_realisasi_bunga'] += $row['realisasi_bunga'];
            $summary['total_tagihan_bermasalah'] += $row['tagihan_bermasalah'];
            $summary['total_tidak_bayar_pokok'] += $row['tidak_bayar_pokok'];
            $summary['total_tidak_bayar_bunga'] += $row['tidak_bayar_bunga'];

            $koleksi_rates[] = $row['persentase_koleksi'];
            $terbaik = max($terbaik, $row['persentase_koleksi']);
            $terburuk = min($terburuk, $row['persentase_koleksi']);
        }

        $summary['rata_rata_koleksi'] = !empty($koleksi_rates) ? array_sum($koleksi_rates) / count($koleksi_rates) : 0;
        $summary['hari_terbaik'] = $terbaik;
        $summary['hari_terburuk'] = $terburuk;

        return $summary;
    }

    /**
     * Calculate performance metrics for analysis
     */
    private function calculatePerformanceMetrics($data)
    {
        if (empty($data)) {
            return [
                'tingkat_koleksi_keseluruhan' => 0,
                'variance_pokok' => 0,
                'variance_bunga' => 0,
                'hari_sempurna' => 0,
                'hari_bermasalah' => 0,
                'trend_koleksi' => 0
            ];
        }

        $total_tagihan = array_sum(array_column($data, 'jml_tagihan'));
        $total_tagihan_masuk = array_sum(array_column($data, 'tagihan_masuk'));
        $total_target_pokok = array_sum(array_column($data, 'target_pokok'));
        $total_realisasi_pokok = array_sum(array_column($data, 'realisasi_pokok'));
        $total_target_bunga = array_sum(array_column($data, 'target_bunga'));
        $total_realisasi_bunga = array_sum(array_column($data, 'realisasi_bunga'));

        $hari_sempurna = 0;
        $hari_bermasalah = 0;

        foreach ($data as $row) {
            if ($row['status'] == 'Sempurna') {
                $hari_sempurna++;
            }
            if ($row['status'] == 'Perlu Perhatian') {
                $hari_bermasalah++;
            }
        }

        // Calculate trend (comparing first half vs second half of month)
        $midpoint = count($data) / 2;
        $first_half = array_slice($data, 0, $midpoint);
        $second_half = array_slice($data, $midpoint);

        $first_half_koleksi = !empty($first_half) ? array_sum(array_column($first_half, 'persentase_koleksi')) / count($first_half) : 0;
        $second_half_koleksi = !empty($second_half) ? array_sum(array_column($second_half, 'persentase_koleksi')) / count($second_half) : 0;

        return [
            'tingkat_koleksi_keseluruhan' => $total_tagihan > 0 ? (($total_tagihan_masuk / $total_tagihan) * 100) : 0,
            'variance_pokok' => $total_target_pokok - $total_realisasi_pokok,
            'variance_bunga' => $total_target_bunga - $total_realisasi_bunga,
            'hari_sempurna' => $hari_sempurna,
            'hari_bermasalah' => $hari_bermasalah,
            'trend_koleksi' => $first_half_koleksi > 0 ? (($second_half_koleksi - $first_half_koleksi) / $first_half_koleksi) * 100 : 0
        ];
    }

    /**
     * Get recent activities for monitoring
     */
    private function getRecentActivities($periode)
    {
        $tglArr = explode('-', $periode);
        $thn = $tglArr[0];
        $bln = $tglArr[1];
        
        // Get recent payments
        $recentPayments = TblPinjamanD::with(['pinjaman.anggota'])
            ->whereYear('tgl_bayar', $thn)
            ->whereMonth('tgl_bayar', $bln)
            ->whereHas('pinjaman', function($q) {
                $q->whereNotNull('anggota_id');
            })
            ->orderBy('tgl_bayar', 'desc')
            ->limit(10)
            ->get()
            ->filter(function($payment) {
                return $payment->pinjaman && $payment->pinjaman->anggota;
            })
            ->map(function ($payment) {
                $pinjaman = $payment->pinjaman;
                $anggota = $pinjaman->anggota;
                
                return [
                    'id' => 'PAY' . str_pad($payment->id, 5, '0', STR_PAD_LEFT),
                    'anggota' => $anggota ? $anggota->nama : 'N/A',
                    'pinjaman_id' => 'PNJ' . str_pad($pinjaman->id, 5, '0', STR_PAD_LEFT),
                    'jumlah_bayar' => $payment->jumlah_bayar ?? 0,
                    'bunga' => $payment->bunga ?? 0,
                    'tgl_bayar' => $payment->tgl_bayar ? Carbon::parse($payment->tgl_bayar)->format('d/m/Y') : '-',
                    'angsuran_ke' => $payment->angsuran_ke ?? 0
                ];
            });

        return $recentPayments;
    }

    public function exportPdf(Request $request)
    {
        $periode = $request->input('periode', date('Y-m'));
        
        // Get detailed data
        $data = $this->getRekapBulanan($periode);
        $summary = $this->calculateSummary($data);
        $performance = $this->calculatePerformanceMetrics($data);
        $recentActivities = $this->getRecentActivities($periode);
        
        $pdf = Pdf::loadView('laporan.pdf.rekapitulasi', [
            'periode' => $periode,
            'data' => $data,
            'summary' => $summary,
            'performance' => $performance,
            'recentActivities' => $recentActivities
        ])->setPaper('a4', 'landscape');
        
        return $pdf->download('laporan_rekapitulasi_'.$periode.'.pdf');
    }

    public function exportExcel(Request $request)
    {
        $periode = $request->input('periode', date('Y-m'));
        
        // Get detailed data
        $data = $this->getRekapBulanan($periode);
        $summary = $this->calculateSummary($data);
        $performance = $this->calculatePerformanceMetrics($data);
        $recentActivities = $this->getRecentActivities($periode);
        
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
        
        // Summary section
        $rowNum = 6;
        $sheet->setCellValue('A'.$rowNum, 'RINGKASAN LAPORAN');
        $sheet->mergeCells('A'.$rowNum.':B'.$rowNum);
        $sheet->getStyle('A'.$rowNum)->getFont()->setBold(true);
        
        $rowNum++;
        $sheet->setCellValue('A'.$rowNum, 'Total Tagihan:');
        $sheet->setCellValue('B'.$rowNum, $summary['total_tagihan']);
        $sheet->setCellValue('A'.($rowNum+1), 'Total Target Pokok:');
        $sheet->setCellValue('B'.($rowNum+1), 'Rp ' . number_format($summary['total_target_pokok'], 0, ',', '.'));
        $sheet->setCellValue('A'.($rowNum+2), 'Total Target Bunga:');
        $sheet->setCellValue('B'.($rowNum+2), 'Rp ' . number_format($summary['total_target_bunga'], 0, ',', '.'));
        $sheet->setCellValue('A'.($rowNum+3), 'Total Realisasi Pokok:');
        $sheet->setCellValue('B'.($rowNum+3), 'Rp ' . number_format($summary['total_realisasi_pokok'], 0, ',', '.'));
        $sheet->setCellValue('A'.($rowNum+4), 'Total Realisasi Bunga:');
        $sheet->setCellValue('B'.($rowNum+4), 'Rp ' . number_format($summary['total_realisasi_bunga'], 0, ',', '.'));
        $sheet->setCellValue('A'.($rowNum+5), 'Rata-rata Koleksi:');
        $sheet->setCellValue('B'.($rowNum+5), number_format($summary['rata_rata_koleksi'], 2) . '%');
        
        // Performance metrics
        $sheet->setCellValue('D'.$rowNum, 'METRIK KINERJA');
        $sheet->mergeCells('D'.$rowNum.':E'.$rowNum);
        $sheet->getStyle('D'.$rowNum)->getFont()->setBold(true);
        
        $sheet->setCellValue('D'.($rowNum+1), 'Tingkat Koleksi Keseluruhan:');
        $sheet->setCellValue('E'.($rowNum+1), number_format($performance['tingkat_koleksi_keseluruhan'], 2) . '%');
        $sheet->setCellValue('D'.($rowNum+2), 'Hari Sempurna:');
        $sheet->setCellValue('E'.($rowNum+2), $performance['hari_sempurna']);
        $sheet->setCellValue('D'.($rowNum+3), 'Hari Bermasalah:');
        $sheet->setCellValue('E'.($rowNum+3), $performance['hari_bermasalah']);
        $sheet->setCellValue('D'.($rowNum+4), 'Variance Pokok:');
        $sheet->setCellValue('E'.($rowNum+4), 'Rp ' . number_format($performance['variance_pokok'], 0, ',', '.'));
        $sheet->setCellValue('D'.($rowNum+5), 'Variance Bunga:');
        $sheet->setCellValue('E'.($rowNum+5), 'Rp ' . number_format($performance['variance_bunga'], 0, ',', '.'));
        
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
            $sheet->setCellValue('C'.$rowNum, $summary['total_tagihan']);
            $sheet->setCellValue('D'.$rowNum, $summary['total_target_pokok']);
            $sheet->setCellValue('E'.$rowNum, $summary['total_target_bunga']);
            $sheet->setCellValue('F'.$rowNum, array_sum(array_column($data, 'tagihan_masuk')));
            $sheet->setCellValue('G'.$rowNum, $summary['total_realisasi_pokok']);
            $sheet->setCellValue('H'.$rowNum, $summary['total_realisasi_bunga']);
            $sheet->setCellValue('I'.$rowNum, $summary['total_tagihan_bermasalah']);
            $sheet->setCellValue('J'.$rowNum, $summary['total_tidak_bayar_pokok']);
            $sheet->setCellValue('K'.$rowNum, $summary['total_tidak_bayar_bunga']);
            $sheet->setCellValue('L'.$rowNum, number_format($summary['rata_rata_koleksi'], 2) . '%');
            $sheet->setCellValue('M'.$rowNum, 'BULANAN');
            
            // Style summary row
            $sheet->getStyle('A'.$rowNum.':M'.$rowNum)->getFont()->setBold(true);
            $sheet->getStyle('A'.$rowNum.':M'.$rowNum)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFFFE0B2');
        }
        
        // Recent activities section
        if ($recentActivities->count() > 0) {
            $recentStartRow = $rowNum + 2;
            $sheet->setCellValue('A'.$recentStartRow, 'AKTIVITAS PEMBAYARAN TERBARU');
            $sheet->mergeCells('A'.$recentStartRow.':E'.$recentStartRow);
            $sheet->getStyle('A'.$recentStartRow)->getFont()->setBold(true);
            
            $recentStartRow++;
            $sheet->setCellValue('A'.$recentStartRow, 'Nama');
            $sheet->setCellValue('B'.$recentStartRow, 'ID');
            $sheet->setCellValue('C'.$recentStartRow, 'Tanggal Bayar');
            $sheet->setCellValue('D'.$recentStartRow, 'Jumlah Bayar');
            $sheet->setCellValue('E'.$recentStartRow, 'Angsuran Ke');
            
            // Style recent headers
            $sheet->getStyle('A'.$recentStartRow.':E'.$recentStartRow)->getFont()->setBold(true);
            $sheet->getStyle('A'.$recentStartRow.':E'.$recentStartRow)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFF0F8FF');
            
            $recentRow = $recentStartRow + 1;
            foreach ($recentActivities as $activity) {
                $sheet->setCellValue('A'.$recentRow, $activity['anggota']);
                $sheet->setCellValue('B'.$recentRow, $activity['id']);
                $sheet->setCellValue('C'.$recentRow, $activity['tgl_bayar']);
                $sheet->setCellValue('D'.$recentRow, 'Rp ' . number_format($activity['jumlah_bayar'], 0, ',', '.'));
                $sheet->setCellValue('E'.$recentRow, $activity['angsuran_ke']);
                $recentRow++;
            }
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