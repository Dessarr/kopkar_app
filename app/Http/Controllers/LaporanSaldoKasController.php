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
        
        // Get detailed cash balance data
        $data = $this->getSaldoKas($periode);
        $summary = $this->calculateSummary($data);
        $performance = $this->calculatePerformanceMetrics($data);
        $recentTransactions = $this->getRecentTransactions($periode);
        
        return view('laporan.saldo_kas', [
            'periode' => $periode,
            'data' => $data['rows'],
            'saldo_sblm' => $data['saldo_sblm'],
            'total' => $data['total'],
            'summary' => $summary,
            'performance' => $performance,
            'recentTransactions' => $recentTransactions
        ]);
    }

    private function getSaldoKas($periode)
    {
        $tglArr = explode('-', $periode);
        $thn = $tglArr[0];
        $bln = $tglArr[1];
        
        // Get active cash accounts
        $kasList = NamaKasTbl::where('aktif', 'Y')->orderBy('id')->get();
        
        // Calculate previous period balance
        $saldo_sblm = transaksi_kas::whereYear('tgl_catat', '<', $thn)
            ->orWhere(function($q) use ($thn, $bln) {
                $q->whereYear('tgl_catat', $thn)
                  ->whereMonth('tgl_catat', '<', $bln);
            })
            ->select(DB::raw('SUM(CASE WHEN untuk_kas_id IS NOT NULL THEN jumlah ELSE 0 END) as jum_debet'),
                     DB::raw('SUM(CASE WHEN dari_kas_id IS NOT NULL THEN jumlah ELSE 0 END) as jum_kredit'))
            ->first();
        $saldo_sblm_val = ($saldo_sblm->jum_debet ?? 0) - ($saldo_sblm->jum_kredit ?? 0);
        
        $rows = [];
        $total_saldo = 0;
        $no = 1;
        
        foreach ($kasList as $kas) {
            // Calculate debit (cash in) for current period
            $debet = transaksi_kas::where('untuk_kas_id', $kas->id)
                ->whereYear('tgl_catat', $thn)
                ->whereMonth('tgl_catat', $bln)
                ->sum('jumlah');
                
            // Calculate credit (cash out) for current period
            $kredit = transaksi_kas::where('dari_kas_id', $kas->id)
                ->whereYear('tgl_catat', $thn)
                ->whereMonth('tgl_catat', $bln)
                ->sum('jumlah');
                
            // Calculate net cash flow for this account
            $saldo = $debet - $kredit;
            
            // Determine cash status
            $status = $this->determineCashStatus($saldo, $debet, $kredit);
            
            $rows[] = [
                'no' => $no++,
                'id' => $kas->id,
                'nama' => $kas->nama,
                'saldo' => $saldo,
                'debet' => $debet,
                'kredit' => $kredit,
                'status' => $status,
                'status_badge' => $this->getCashStatusBadge($status)
            ];
            $total_saldo += $saldo;
        }
        
        return [
            'rows' => $rows,
            'saldo_sblm' => $saldo_sblm_val,
            'total' => $total_saldo
        ];
    }

    /**
     * Determine cash status based on balance and flow
     */
    private function determineCashStatus($saldo, $debet, $kredit)
    {
        if ($saldo > 0) {
            return 'Surplus';
        } elseif ($saldo < 0) {
            return 'Defisit';
        } elseif ($debet > 0 && $kredit > 0) {
            return 'Seimbang';
        } else {
            return 'Tidak Ada Aktivitas';
        }
    }

    /**
     * Get CSS classes for cash status badge
     */
    private function getCashStatusBadge($status)
    {
        switch ($status) {
            case 'Surplus':
                return 'bg-green-100 text-green-800';
            case 'Defisit':
                return 'bg-red-100 text-red-800';
            case 'Seimbang':
                return 'bg-blue-100 text-blue-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    }

    /**
     * Calculate summary statistics
     */
    private function calculateSummary($data)
    {
        $rows = $data['rows'];
        $total_saldo = $data['total'];
        $saldo_sblm = $data['saldo_sblm'];
        
        $total_debet = array_sum(array_column($rows, 'debet'));
        $total_kredit = array_sum(array_column($rows, 'kredit'));
        $net_cash_flow = $total_debet - $total_kredit;
        
        $surplus_count = count(array_filter($rows, function($row) {
            return $row['status'] === 'Surplus';
        }));
        
        $deficit_count = count(array_filter($rows, function($row) {
            return $row['status'] === 'Defisit';
        }));
        
        $highest_balance = !empty($rows) ? max(array_column($rows, 'saldo')) : 0;
        $lowest_balance = !empty($rows) ? min(array_column($rows, 'saldo')) : 0;
        
        return [
            'total_saldo' => $total_saldo,
            'saldo_sblm' => $saldo_sblm,
            'total_saldo_akhir' => $total_saldo + $saldo_sblm,
            'total_debet' => $total_debet,
            'total_kredit' => $total_kredit,
            'net_cash_flow' => $net_cash_flow,
            'surplus_count' => $surplus_count,
            'deficit_count' => $deficit_count,
            'highest_balance' => $highest_balance,
            'lowest_balance' => $lowest_balance
        ];
    }

    /**
     * Calculate performance metrics
     */
    private function calculatePerformanceMetrics($data)
    {
        $rows = $data['rows'];
        $total_saldo = $data['total'];
        $saldo_sblm = $data['saldo_sblm'];
        
        if (empty($rows)) {
            return [
                'liquidity_ratio' => 0,
                'cash_efficiency' => 0,
                'growth_rate' => 0,
                'cash_concentration' => 0
            ];
        }
        
        $total_debet = array_sum(array_column($rows, 'debet'));
        $total_kredit = array_sum(array_column($rows, 'kredit'));
        
        // Liquidity ratio (simplified)
        $liquidity_ratio = $total_kredit > 0 ? ($total_debet / $total_kredit) * 100 : 0;
        
        // Cash efficiency (net flow vs total flow)
        $total_flow = $total_debet + $total_kredit;
        $cash_efficiency = $total_flow > 0 ? (abs($total_saldo) / $total_flow) * 100 : 0;
        
        // Growth rate compared to previous period
        $growth_rate = $saldo_sblm != 0 ? (($total_saldo - $saldo_sblm) / abs($saldo_sblm)) * 100 : 0;
        
        // Cash concentration (how much is in the largest account)
        $max_balance = max(array_column($rows, 'saldo'));
        $cash_concentration = $total_saldo != 0 ? ($max_balance / $total_saldo) * 100 : 0;
        
        return [
            'liquidity_ratio' => round($liquidity_ratio, 2),
            'cash_efficiency' => round($cash_efficiency, 2),
            'growth_rate' => round($growth_rate, 2),
            'cash_concentration' => round($cash_concentration, 2)
        ];
    }

    /**
     * Get recent cash transactions
     */
    private function getRecentTransactions($periode)
    {
        $tglArr = explode('-', $periode);
        $thn = $tglArr[0];
        $bln = $tglArr[1];
        
        return transaksi_kas::with(['dariKas', 'untukKas', 'jenisAkun'])
            ->whereYear('tgl_catat', $thn)
            ->whereMonth('tgl_catat', $bln)
            ->orderBy('tgl_catat', 'desc')
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get()
            ->map(function($transaction) {
                return [
                    'id' => $transaction->id,
                    'tanggal' => Carbon::parse($transaction->tgl_catat)->format('d/m/Y'),
                    'keterangan' => $transaction->keterangan ?? 'Transaksi Kas',
                    'jumlah' => $transaction->jumlah,
                    'dari_kas' => $transaction->dariKas->nama ?? 'N/A',
                    'untuk_kas' => $transaction->untukKas->nama ?? 'N/A',
                    'jenis_akun' => $transaction->jenisAkun->nama ?? 'N/A',
                    'tipe' => $transaction->untuk_kas_id ? 'Masuk' : 'Keluar'
                ];
            });
    }

    public function exportPdf(Request $request)
    {
        $periode = $request->input('periode', date('Y-m'));
        
        // Get detailed data
        $data = $this->getSaldoKas($periode);
        $summary = $this->calculateSummary($data);
        $performance = $this->calculatePerformanceMetrics($data);
        $recentTransactions = $this->getRecentTransactions($periode);
        
        $pdf = Pdf::loadView('laporan.pdf.saldo_kas', [
            'periode' => $periode,
            'data' => $data['rows'],
            'saldo_sblm' => $data['saldo_sblm'],
            'total' => $data['total'],
            'summary' => $summary,
            'performance' => $performance,
            'recentTransactions' => $recentTransactions
        ]);
        
        return $pdf->download('laporan_saldo_kas_'.$periode.'.pdf');
    }

    public function exportExcel(Request $request)
    {
        $periode = $request->input('periode', date('Y-m'));
        
        // Get detailed data
        $data = $this->getSaldoKas($periode);
        $summary = $this->calculateSummary($data);
        $performance = $this->calculatePerformanceMetrics($data);
        $recentTransactions = $this->getRecentTransactions($periode);
        
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
        
        $rowNum++;
        $sheet->setCellValue('A'.$rowNum, 'Total Saldo Akhir:');
        $sheet->setCellValue('B'.$rowNum, 'Rp ' . number_format($summary['total_saldo_akhir'], 0, ',', '.'));
        $sheet->setCellValue('A'.($rowNum+1), 'Total Debet:');
        $sheet->setCellValue('B'.($rowNum+1), 'Rp ' . number_format($summary['total_debet'], 0, ',', '.'));
        $sheet->setCellValue('A'.($rowNum+2), 'Total Kredit:');
        $sheet->setCellValue('B'.($rowNum+2), 'Rp ' . number_format($summary['total_kredit'], 0, ',', '.'));
        $sheet->setCellValue('A'.($rowNum+3), 'Net Cash Flow:');
        $sheet->setCellValue('B'.($rowNum+3), 'Rp ' . number_format($summary['net_cash_flow'], 0, ',', '.'));
        
        // Performance metrics
        $sheet->setCellValue('D'.$rowNum, 'METRIK KINERJA');
        $sheet->mergeCells('D'.$rowNum.':E'.$rowNum);
        $sheet->getStyle('D'.$rowNum)->getFont()->setBold(true);
        
        $sheet->setCellValue('D'.($rowNum+1), 'Rasio Likuiditas:');
        $sheet->setCellValue('E'.($rowNum+1), number_format($performance['liquidity_ratio'], 2) . '%');
        $sheet->setCellValue('D'.($rowNum+2), 'Efisiensi Kas:');
        $sheet->setCellValue('E'.($rowNum+2), number_format($performance['cash_efficiency'], 2) . '%');
        $sheet->setCellValue('D'.($rowNum+3), 'Tingkat Pertumbuhan:');
        $sheet->setCellValue('E'.($rowNum+3), number_format($performance['growth_rate'], 2) . '%');
        
        // Set headers
        $headers = ['No', 'Nama Kas', 'Debet', 'Kredit', 'Saldo', 'Status'];
        $col = 1;
        $startRow = $rowNum + 5;
        foreach ($headers as $header) {
            $sheet->setCellValueByColumnAndRow($col, $startRow, $header);
            $col++;
        }
        
        // Style headers
        $sheet->getStyle('A'.$startRow.':F'.$startRow)->getFont()->setBold(true);
        $sheet->getStyle('A'.$startRow.':F'.$startRow)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE0E0E0');
        
        // Add data
        $rowNum = $startRow + 1;
        foreach ($data['rows'] as $row) {
            $sheet->setCellValue('A'.$rowNum, $row['no']);
            $sheet->setCellValue('B'.$rowNum, $row['nama']);
            $sheet->setCellValue('C'.$rowNum, $row['debet']);
            $sheet->setCellValue('D'.$rowNum, $row['kredit']);
            $sheet->setCellValue('E'.$rowNum, $row['saldo']);
            $sheet->setCellValue('F'.$rowNum, $row['status']);
            $rowNum++;
        }
        
        // Add summary rows
        if (count($data['rows']) > 0) {
        $sheet->setCellValue('A'.$rowNum, 'SALDO PERIODE SEBELUMNYA');
        $sheet->mergeCells('A'.$rowNum.':B'.$rowNum);
            $sheet->setCellValue('E'.$rowNum, $data['saldo_sblm']);
        $rowNum++;
        $sheet->setCellValue('A'.$rowNum, 'JUMLAH');
        $sheet->mergeCells('A'.$rowNum.':B'.$rowNum);
            $sheet->setCellValue('E'.$rowNum, $data['total']);
        $rowNum++;
        $sheet->setCellValue('A'.$rowNum, 'TOTAL SALDO');
        $sheet->mergeCells('A'.$rowNum.':B'.$rowNum);
            $sheet->setCellValue('E'.$rowNum, $data['total'] + $data['saldo_sblm']);
            
            // Style summary rows
            $sheet->getStyle('A'.($rowNum-2).':F'.$rowNum)->getFont()->setBold(true);
            $sheet->getStyle('A'.($rowNum-2).':F'.$rowNum)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFFFE0B2');
        }
        
        // Recent transactions section
        if ($recentTransactions->count() > 0) {
            $recentStartRow = $rowNum + 2;
            $sheet->setCellValue('A'.$recentStartRow, 'TRANSAKSI KAS TERBARU');
            $sheet->mergeCells('A'.$recentStartRow.':F'.$recentStartRow);
            $sheet->getStyle('A'.$recentStartRow)->getFont()->setBold(true);
            
            $recentStartRow++;
            $sheet->setCellValue('A'.$recentStartRow, 'Tanggal');
            $sheet->setCellValue('B'.$recentStartRow, 'Keterangan');
            $sheet->setCellValue('C'.$recentStartRow, 'Dari Kas');
            $sheet->setCellValue('D'.$recentStartRow, 'Untuk Kas');
            $sheet->setCellValue('E'.$recentStartRow, 'Jumlah');
            $sheet->setCellValue('F'.$recentStartRow, 'Tipe');
            
            // Style recent headers
            $sheet->getStyle('A'.$recentStartRow.':F'.$recentStartRow)->getFont()->setBold(true);
            $sheet->getStyle('A'.$recentStartRow.':F'.$recentStartRow)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFF0F8FF');
            
            $recentRow = $recentStartRow + 1;
            foreach ($recentTransactions as $transaction) {
                $sheet->setCellValue('A'.$recentRow, $transaction['tanggal']);
                $sheet->setCellValue('B'.$recentRow, $transaction['keterangan']);
                $sheet->setCellValue('C'.$recentRow, $transaction['dari_kas']);
                $sheet->setCellValue('D'.$recentRow, $transaction['untuk_kas']);
                $sheet->setCellValue('E'.$recentRow, 'Rp ' . number_format((float)$transaction['jumlah'], 0, ',', '.'));
                $sheet->setCellValue('F'.$recentRow, $transaction['tipe']);
                $recentRow++;
            }
        }
        
        // Auto-size columns
        foreach (range('A', 'F') as $column) {
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