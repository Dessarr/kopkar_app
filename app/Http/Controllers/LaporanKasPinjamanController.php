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

class LaporanKasPinjamanController extends Controller
{
    public function index(Request $request)
    {
        // Get filter parameters with default values
        $tgl_dari = $request->input('tgl_dari', date('Y') . '-01-01');
        $tgl_samp = $request->input('tgl_samp', date('Y') . '-12-31');
        
        // Get loan data with proper accounting logic
        $data = $this->getRekapPinjaman($tgl_dari, $tgl_samp);
        
        // Get detailed loan statistics
        $statistics = $this->getLoanStatistics($tgl_dari, $tgl_samp);
        
        // Get recent loan activities
        $recentLoans = $this->getRecentLoans($tgl_dari, $tgl_samp);
        
        // Calculate period summary
        $periodSummary = $this->calculatePeriodSummary($tgl_dari, $tgl_samp);
        
        return view('laporan.kas_pinjaman', compact(
            'tgl_dari',
            'tgl_samp',
            'data',
            'statistics',
            'recentLoans',
            'periodSummary'
        ));
    }

    /**
     * Get comprehensive loan data with proper accounting principles
     * This implements the accounting principle for loan management and credit risk
     */
    private function getRekapPinjaman($tgl_dari, $tgl_samp)
    {
        // Get loans within the specified period
        $loans = TblPinjamanH::whereDate('tgl_pinjam', '>=', $tgl_dari)
            ->whereDate('tgl_pinjam', '<=', $tgl_samp)
            ->get();
        
        // Calculate total loan principal
        $jml_pinjaman = $loans->sum('jumlah');
        
        // Calculate total installments paid
        $jml_angsuran = TblPinjamanD::whereIn('pinjam_id', $loans->pluck('id'))
            ->sum('jumlah_bayar');
        
        // Calculate total penalties
        $jml_denda = TblPinjamanD::whereIn('pinjam_id', $loans->pluck('id'))
            ->sum('denda_rp');
        
        // Calculate total outstanding (principal + penalties)
        $tot_tagihan = $jml_pinjaman + $jml_denda;
        
        // Calculate remaining balance
        $sisa_tagihan = $tot_tagihan - $jml_angsuran;
        
        // Calculate collection rate
        $collection_rate = $tot_tagihan > 0 ? ($jml_angsuran / $tot_tagihan) * 100 : 0;
        
        // Calculate average loan amount
        $avg_loan_amount = $loans->count() > 0 ? $jml_pinjaman / $loans->count() : 0;
        
        return [
            'jml_pinjaman' => $jml_pinjaman,
            'jml_angsuran' => $jml_angsuran,
            'jml_denda' => $jml_denda,
            'tot_tagihan' => $tot_tagihan,
            'sisa_tagihan' => $sisa_tagihan,
            'collection_rate' => $collection_rate,
            'avg_loan_amount' => $avg_loan_amount,
            'total_loans' => $loans->count()
        ];
    }

    /**
     * Get detailed loan statistics for borrower analysis
     * This implements borrower categorization and risk assessment
     */
    private function getLoanStatistics($tgl_dari, $tgl_samp)
    {
        // Get all loans in the period
        $loans = TblPinjamanH::whereDate('tgl_pinjam', '>=', $tgl_dari)
            ->whereDate('tgl_pinjam', '<=', $tgl_samp)
            ->get();
        
        // Count borrowers by status
        $peminjam_aktif = $loans->count();
        $peminjam_lunas = $loans->where('lunas', 'Lunas')->count();
        $peminjam_belum = $loans->where('lunas', 'Belum')->count();
        
        // Calculate completion rate
        $completion_rate = $peminjam_aktif > 0 ? ($peminjam_lunas / $peminjam_aktif) * 100 : 0;
        
        // Get overdue loans (loans with penalties)
        $overdue_loans = TblPinjamanD::whereIn('pinjam_id', $loans->pluck('id'))
            ->where('denda_rp', '>', 0)
            ->count();
        
        // Calculate overdue rate
        $overdue_rate = $peminjam_aktif > 0 ? ($overdue_loans / $peminjam_aktif) * 100 : 0;
        
        return [
            'peminjam_aktif' => $peminjam_aktif,
            'peminjam_lunas' => $peminjam_lunas,
            'peminjam_belum' => $peminjam_belum,
            'completion_rate' => $completion_rate,
            'overdue_loans' => $overdue_loans,
            'overdue_rate' => $overdue_rate
        ];
    }

    /**
     * Get recent loan activities for monitoring
     */
    private function getRecentLoans($tgl_dari, $tgl_samp)
    {
        return TblPinjamanH::with('anggota')
            ->whereDate('tgl_pinjam', '>=', $tgl_dari)
            ->whereDate('tgl_pinjam', '<=', $tgl_samp)
            ->orderBy('tgl_pinjam', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($loan) {
                return [
                    'id' => 'PNJ' . str_pad($loan->id, 5, '0', STR_PAD_LEFT),
                    'anggota' => $loan->anggota->nama ?? 'N/A',
                    'jumlah' => $loan->jumlah,
                    'tgl_pinjam' => $loan->tgl_pinjam->format('d/m/Y'),
                    'status' => $loan->lunas,
                    'status_badge' => $loan->lunas == 'Lunas' ? 'success' : 'warning'
                ];
            });
    }

    /**
     * Calculate period summary for comparison
     */
    private function calculatePeriodSummary($tgl_dari, $tgl_samp)
    {
        $start_date = Carbon::parse($tgl_dari);
        $end_date = Carbon::parse($tgl_samp);
        
        // Previous period for comparison
        $period_days = $start_date->diffInDays($end_date);
        $prev_start = $start_date->copy()->subDays($period_days + 1);
        $prev_end = $start_date->copy()->subDay();
        
        // Current period data
        $current_data = $this->getRekapPinjaman($tgl_dari, $tgl_samp);
        
        // Previous period data
        $previous_data = $this->getRekapPinjaman($prev_start->format('Y-m-d'), $prev_end->format('Y-m-d'));
        
        // Calculate growth rates
        $loan_growth = $previous_data['jml_pinjaman'] > 0 
            ? (($current_data['jml_pinjaman'] - $previous_data['jml_pinjaman']) / $previous_data['jml_pinjaman']) * 100 
            : 0;
        
        $collection_growth = $previous_data['jml_angsuran'] > 0 
            ? (($current_data['jml_angsuran'] - $previous_data['jml_angsuran']) / $previous_data['jml_angsuran']) * 100 
            : 0;
        
        return [
            'current_period' => $current_data,
            'previous_period' => $previous_data,
            'loan_growth' => $loan_growth,
            'collection_growth' => $collection_growth,
            'period_days' => $period_days + 1
        ];
    }

    public function exportPdf(Request $request)
    {
        // Get filter parameters
        $tgl_dari = $request->input('tgl_dari', date('Y') . '-01-01');
        $tgl_samp = $request->input('tgl_samp', date('Y') . '-12-31');
        
        // Get data
        $data = $this->getRekapPinjaman($tgl_dari, $tgl_samp);
        $statistics = $this->getLoanStatistics($tgl_dari, $tgl_samp);
        $periodSummary = $this->calculatePeriodSummary($tgl_dari, $tgl_samp);
        
        // Format dates
        $tgl_dari_formatted = Carbon::parse($tgl_dari)->format('d F Y');
        $tgl_samp_formatted = Carbon::parse($tgl_samp)->format('d F Y');
        
        $pdf = Pdf::loadView('laporan.pdf.kas_pinjaman', compact(
            'tgl_dari',
            'tgl_samp',
            'tgl_dari_formatted',
            'tgl_samp_formatted',
            'data',
            'statistics',
            'periodSummary'
        ));

        return $pdf->download('laporan_kas_pinjaman_' . $tgl_dari . '_' . $tgl_samp . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        // Get filter parameters
        $tgl_dari = $request->input('tgl_dari', date('Y') . '-01-01');
        $tgl_samp = $request->input('tgl_samp', date('Y') . '-12-31');
        
        // Get data
        $data = $this->getRekapPinjaman($tgl_dari, $tgl_samp);
        $statistics = $this->getLoanStatistics($tgl_dari, $tgl_samp);
        $periodSummary = $this->calculatePeriodSummary($tgl_dari, $tgl_samp);
        
        // Format dates
        $tgl_dari_formatted = Carbon::parse($tgl_dari)->format('d F Y');
        $tgl_samp_formatted = Carbon::parse($tgl_samp)->format('d F Y');
        
        // Create Excel file
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set title
        $sheet->setCellValue('A1', 'LAPORAN KAS PINJAMAN');
        $sheet->mergeCells('A1:D1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
        
        // Set period info
        $sheet->setCellValue('A2', 'Periode: ' . $tgl_dari_formatted . ' s/d ' . $tgl_samp_formatted);
        $sheet->mergeCells('A2:D2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
        
        // Set headers
        $sheet->setCellValue('A4', 'No');
        $sheet->setCellValue('B4', 'Keterangan');
        $sheet->setCellValue('C4', 'Jumlah (Rp)');
        $sheet->setCellValue('D4', 'Persentase');
        
        // Style headers
        $headerRange = 'A4:D4';
        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E5E7EB');
        
        // Fill data
        $rows = [
            ['Pokok Pinjaman', $data['jml_pinjaman']],
            ['Tagihan Denda', $data['jml_denda']],
            ['Jumlah Tagihan + Denda', $data['tot_tagihan']],
            ['Tagihan Sudah Dibayar', $data['jml_angsuran']],
            ['Sisa Tagihan', $data['sisa_tagihan']]
        ];
        
        $rowNum = 5;
        $no = 1;
        foreach ($rows as $row) {
            $percentage = $data['tot_tagihan'] > 0 ? ($row[1] / $data['tot_tagihan']) * 100 : 0;
            
            $sheet->setCellValue('A' . $rowNum, $no++);
            $sheet->setCellValue('B' . $rowNum, $row[0]);
            $sheet->setCellValue('C' . $rowNum, $row[1]);
            $sheet->setCellValue('D' . $rowNum, number_format($percentage, 2) . '%');
            $rowNum++;
        }
        
        // Add totals row
        $totalRow = $rowNum + 1;
        $sheet->setCellValue('A' . $totalRow, 'TOTAL');
        $sheet->setCellValue('B' . $totalRow, 'Total Keseluruhan');
        $sheet->setCellValue('C' . $totalRow, $data['tot_tagihan']);
        $sheet->setCellValue('D' . $totalRow, '100.00%');
        
        // Style totals
        $totalRange = 'A' . $totalRow . ':D' . $totalRow;
        $sheet->getStyle($totalRange)->getFont()->setBold(true);
        $sheet->getStyle($totalRange)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('D1D5DB');
        
        // Add statistics section
        $statsRow = $totalRow + 3;
        $sheet->setCellValue('A' . $statsRow, 'STATISTIK PEMINJAM');
        $sheet->mergeCells('A' . $statsRow . ':D' . $statsRow);
        $sheet->getStyle('A' . $statsRow)->getFont()->setBold(true)->setSize(14);
        
        $statsData = [
            ['Peminjam Aktif', $statistics['peminjam_aktif']],
            ['Peminjam Lunas', $statistics['peminjam_lunas']],
            ['Peminjam Belum Lunas', $statistics['peminjam_belum']],
            ['Tingkat Pelunasan', number_format($statistics['completion_rate'], 2) . '%'],
            ['Pinjaman Terlambat', $statistics['overdue_loans']],
            ['Tingkat Keterlambatan', number_format($statistics['overdue_rate'], 2) . '%']
        ];
        
        $statsRow++;
        foreach ($statsData as $stat) {
            $sheet->setCellValue('A' . $statsRow, $stat[0]);
            $sheet->setCellValue('C' . $statsRow, $stat[1]);
            $statsRow++;
        }
        
        // Auto size columns
        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Format currency columns
        $currencyRange = 'C5:C' . $totalRow;
        $sheet->getStyle($currencyRange)->getNumberFormat()->setFormatCode('#,##0');
        
        $writer = new Xlsx($spreadsheet);
        $filename = 'laporan_kas_pinjaman_' . $tgl_dari . '_' . $tgl_samp . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }
}