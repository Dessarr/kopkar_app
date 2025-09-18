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

class LaporanTargetRealisasiController extends Controller
{
    public function index(Request $request)
    {
        // Get filter parameters with default values
        $tgl_dari = $request->input('tgl_dari', date('Y') . '-01-01');
        $tgl_samp = $request->input('tgl_samp', date('Y') . '-12-31');
        
        // Get detailed loan data with proper accounting logic
        $data = $this->getDetailPinjaman($tgl_dari, $tgl_samp);
        
        // Calculate summary statistics
        $summary = $this->calculateSummary($data);
        
        // Get performance metrics
        $performance = $this->calculatePerformanceMetrics($data);
        
        // Get recent loan activities
        $recentLoans = $this->getRecentLoans($tgl_dari, $tgl_samp);
        
        return view('laporan.target_realisasi', compact(
            'tgl_dari',
            'tgl_samp',
            'data',
            'summary',
            'performance',
            'recentLoans'
        ));
    }

    /**
     * Get detailed loan data using v_hitung_pinjaman view
     * This implements the accounting principle for target vs realization analysis
     */
    private function getDetailPinjaman($tgl_dari, $tgl_samp)
    {
        // Get loan data from v_hitung_pinjaman view with member data
        $loans = DB::table('v_hitung_pinjaman as v')
            ->leftJoin('tbl_anggota as a', 'v.no_ktp', '=', 'a.no_ktp')
            ->leftJoin('tbl_pinjaman_h as p', 'v.id', '=', 'p.id')
            ->whereDate('v.tgl_pinjam', '>=', $tgl_dari)
            ->whereDate('v.tgl_pinjam', '<=', $tgl_samp)
            ->select(
                'v.id',
                'v.no_ktp',
                'v.jenis_pinjaman',
                'v.jumlah',
                'v.lunas',
                'v.status',
                'v.tgl_pinjam',
                'v.total_bayar',
                'v.sisa_pokok',
                'v.tagihan',
                'p.lama_angsuran',
                'p.jumlah_angsuran',
                'p.bunga',
                'p.bunga_rp',
                'p.biaya_adm',
                'a.nama',
                'a.id as anggota_id',
                'a.jabatan_id',
                'a.departement'
            )
            ->orderBy('v.tgl_pinjam', 'asc')
            ->get();
        
        $result = [];
        $no = 1;
        
        foreach ($loans as $loan) {
            // Get installment data for calculations
            $angsuran = TblPinjamanD::where('pinjam_id', $loan->id)->get();
            $bln_sudah_angsur = $angsuran->count();
            
            // Calculate actual payments
            $bunga_ags = $angsuran->sum('bunga');
            $denda_rp = $angsuran->sum('denda_rp');
            
            // Calculate target vs realization
            $pokok_angsuran = $loan->jumlah_angsuran ?? 0;
            $pokok_bunga = $loan->bunga_rp ?? 0;
            $biaya_adm = $loan->biaya_adm ?? 0;
            
            $target_angsuran_bulanan = $pokok_angsuran + $pokok_bunga + $biaya_adm;
            $realisasi_pembayaran = $loan->total_bayar + $bunga_ags + $denda_rp;
            
            // Calculate total target for entire loan period
            $total_target = $target_angsuran_bulanan * $loan->lama_angsuran;
            
            // Calculate remaining installments
            $sisa_angsuran = $loan->lama_angsuran - $bln_sudah_angsur;
            
            // Calculate completion percentage
            $persentase_realisasi = $total_target > 0 ? ($realisasi_pembayaran / $total_target) * 100 : 0;
            
            // Calculate remaining balance
            $sisa_tagihan = $loan->tagihan - $loan->total_bayar;
            
            // Determine loan status
            $status = $this->determineLoanStatus($loan, $bln_sudah_angsur, $sisa_tagihan);
            
            $result[] = [
                'no' => $no++,
                'tgl_pinjam' => Carbon::parse($loan->tgl_pinjam),
                'nama' => $loan->nama ? 'AG' . str_pad($loan->anggota_id, 8, '0', STR_PAD_LEFT) . '/' . $loan->nama : 'N/A',
                'id' => 'AG' . str_pad($loan->anggota_id ?? 0, 4, '0', STR_PAD_LEFT),
                'id_anggota' => $loan->anggota_id ?? 0,
                'no_ktp' => $loan->no_ktp,
                'jabatan' => ($loan->jabatan_id == 1) ? 'Pengurus' : 'Anggota',
                'departemen' => $loan->departement ?? '-',
                
                // Target (Rencana) Data
                'jumlah' => $loan->jumlah ?? 0,
                'sisa_pokok' => $loan->sisa_pokok ?? 0,
                'lama_angsuran' => $loan->lama_angsuran ?? 0,
                'bunga' => $loan->bunga ?? 0,
                'pokok_angsuran' => $pokok_angsuran,
                'pokok_bunga' => $pokok_bunga,
                'biaya_adm' => $biaya_adm,
                'target_angsuran_bulanan' => $target_angsuran_bulanan,
                'total_target' => $total_target,
                
                // Realisasi (Aktual) Data
                'angsuran_ke' => $bln_sudah_angsur,
                'bln_sudah_angsur' => $bln_sudah_angsur,
                'sisa_angsuran' => $sisa_angsuran,
                'pokok_bayar' => $loan->total_bayar,
                'bunga_bayar' => $bunga_ags,
                'total_bayar' => $loan->total_bayar,
                'bunga_ags' => $bunga_ags,
                'denda_rp' => $denda_rp,
                'realisasi_pembayaran' => $realisasi_pembayaran,
                'sisa_tagihan' => $sisa_tagihan,
                
                // Performance Metrics
                'persentase_realisasi' => $persentase_realisasi,
                'status' => $status,
                'status_badge' => $this->getStatusBadge($status),
                'gap_target_realisasi' => $target_angsuran_bulanan - ($realisasi_pembayaran / max($bln_sudah_angsur, 1)),
                
                // Additional Info
                'lunas' => $loan->lunas ?? 'Belum',
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
        
        return $result;
    }

    /**
     * Determine loan status based on payment progress
     */
    private function determineLoanStatus($loan, $bln_sudah_angsur, $sisa_tagihan)
    {
        $lunas = $loan->lunas ?? 'Belum';
        $lama_angsuran = $loan->lama_angsuran ?? 0;
        
        if ($lunas == 'Lunas') {
            return 'Lunas';
        } elseif ($sisa_tagihan <= 0) {
            return 'Lunas';
        } elseif ($bln_sudah_angsur >= $lama_angsuran) {
            return 'Jatuh Tempo';
        } elseif ($bln_sudah_angsur > 0) {
            return 'Berjalan';
        } else {
            return 'Belum Mulai';
        }
    }

    /**
     * Get status badge class for UI
     */
    private function getStatusBadge($status)
    {
        if (empty($status)) {
            return 'secondary';
        }
        
        switch ($status) {
            case 'Lunas':
                return 'success';
            case 'Berjalan':
                return 'info';
            case 'Jatuh Tempo':
                return 'warning';
            case 'Belum Mulai':
                return 'secondary';
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
                'total_pinjaman' => 0,
                'total_nilai_pinjaman' => 0,
                'total_target_angsuran' => 0,
                'total_realisasi' => 0,
                'total_sisa_tagihan' => 0,
                'pinjaman_lunas' => 0,
                'pinjaman_berjalan' => 0,
                'pinjaman_jatuh_tempo' => 0,
                'pinjaman_belum_mulai' => 0,
                'persentase_realisasi_keseluruhan' => 0,
                'persentase_pelunasan' => 0,
                'completion_rate' => 0,
                'overdue_rate' => 0,
                'average_loan_amount' => 0,
                'total_gap' => 0
            ];
        }
        
        $summary = [
            'total_pinjaman' => count($data),
            'total_nilai_pinjaman' => 0,
            'total_target_angsuran' => 0,
            'total_realisasi' => 0,
            'total_sisa_tagihan' => 0,
            'pinjaman_lunas' => 0,
            'pinjaman_berjalan' => 0,
            'pinjaman_jatuh_tempo' => 0,
            'pinjaman_belum_mulai' => 0
        ];
        
        foreach ($data as $row) {
            $summary['total_nilai_pinjaman'] += $row['jumlah'];
            $summary['total_target_angsuran'] += $row['total_target'];
            $summary['total_realisasi'] += $row['realisasi_pembayaran'];
            $summary['total_sisa_tagihan'] += $row['sisa_tagihan'];
            
            switch ($row['status']) {
                case 'Lunas':
                    $summary['pinjaman_lunas']++;
                    break;
                case 'Berjalan':
                    $summary['pinjaman_berjalan']++;
                    break;
                case 'Jatuh Tempo':
                    $summary['pinjaman_jatuh_tempo']++;
                    break;
                case 'Belum Mulai':
                    $summary['pinjaman_belum_mulai']++;
                    break;
            }
        }
        
        // Calculate overall performance
        $summary['persentase_realisasi_keseluruhan'] = $summary['total_target_angsuran'] > 0 
            ? ($summary['total_realisasi'] / $summary['total_target_angsuran']) * 100 
            : 0;
        
        $summary['persentase_pelunasan'] = $summary['total_pinjaman'] > 0 
            ? ($summary['pinjaman_lunas'] / $summary['total_pinjaman']) * 100 
            : 0;
        
        // Calculate additional metrics
        $summary['completion_rate'] = $summary['total_pinjaman'] > 0 
            ? (($summary['pinjaman_lunas'] + $summary['pinjaman_berjalan']) / $summary['total_pinjaman']) * 100 
            : 0;
        
        $summary['overdue_rate'] = $summary['total_pinjaman'] > 0 
            ? ($summary['pinjaman_jatuh_tempo'] / $summary['total_pinjaman']) * 100 
            : 0;
        
        $summary['average_loan_amount'] = $summary['total_pinjaman'] > 0 
            ? $summary['total_nilai_pinjaman'] / $summary['total_pinjaman'] 
            : 0;
        
        $summary['total_gap'] = $summary['total_target_angsuran'] - $summary['total_realisasi'];
        
        return $summary;
    }

    /**
     * Calculate performance metrics for analysis
     */
    private function calculatePerformanceMetrics($data)
    {
        if (empty($data) || !is_array($data)) {
            return [
                'rata_rata_target' => 0,
                'rata_rata_realisasi' => 0,
                'rata_rata_gap' => 0,
                'pinjaman_tertinggi' => 0,
                'pinjaman_terendah' => 0,
                'persentase_tertinggi' => 0,
                'persentase_terendah' => 100
            ];
        }
        
        $targets = array_column($data, 'target_angsuran_bulanan');
        $realizations = array_column($data, 'realisasi_pembayaran');
        $percentages = array_column($data, 'persentase_realisasi');
        $gaps = array_column($data, 'gap_target_realisasi');
        $amounts = array_column($data, 'jumlah');
        
        return [
            'rata_rata_target' => array_sum($targets) / count($targets),
            'rata_rata_realisasi' => array_sum($realizations) / count($realizations),
            'rata_rata_gap' => array_sum($gaps) / count($gaps),
            'pinjaman_tertinggi' => max($amounts),
            'pinjaman_terendah' => min($amounts),
            'persentase_tertinggi' => max($percentages),
            'persentase_terendah' => min($percentages)
        ];
    }

    /**
     * Get recent loan activities for monitoring
     */
    private function getRecentLoans($tgl_dari, $tgl_samp)
    {
        $loans = DB::table('v_hitung_pinjaman as v')
            ->leftJoin('tbl_anggota as a', 'v.no_ktp', '=', 'a.no_ktp')
            ->leftJoin('tbl_pinjaman_h as p', 'v.id', '=', 'p.id')
            ->whereDate('v.tgl_pinjam', '>=', $tgl_dari)
            ->whereDate('v.tgl_pinjam', '<=', $tgl_samp)
            ->select(
                'v.id',
                'v.jumlah',
                'v.tgl_pinjam',
                'v.total_bayar',
                'v.tagihan',
                'p.lama_angsuran',
                'a.nama'
            )
            ->orderBy('v.tgl_pinjam', 'desc')
            ->limit(10)
            ->get();
        
        return $loans->map(function ($loan) {
            $angsuran = TblPinjamanD::where('pinjam_id', $loan->id)->get();
            $sisa_tagihan = $loan->tagihan - $loan->total_bayar;
            
            // Determine status
            $status = $this->determineLoanStatus($loan, $angsuran->count(), $sisa_tagihan);
            
            return [
                'id' => 'PNJ' . str_pad($loan->id, 5, '0', STR_PAD_LEFT),
                'anggota' => $loan->nama ? 'AG' . str_pad($loan->id, 8, '0', STR_PAD_LEFT) . '/' . $loan->nama : 'N/A',
                'jumlah' => $loan->jumlah,
                'tgl_pinjam' => Carbon::parse($loan->tgl_pinjam)->format('d/m/Y'),
                'status' => $status,
                'sisa_tagihan' => $sisa_tagihan,
                'persentase' => $loan->jumlah > 0 ? (($loan->total_bayar / $loan->jumlah) * 100) : 0
            ];
        });
    }

    /**
     * Get loan data for PDF export
     */
    public function getLoanDataForPdf($tgl_dari, $tgl_samp)
    {
        $data = $this->getDetailPinjaman($tgl_dari, $tgl_samp);
        $summary = $this->calculateSummary($data);
        $performance = $this->calculatePerformanceMetrics($data);
        $recentLoans = $this->getRecentLoans($tgl_dari, $tgl_samp);
        
        return compact('data', 'summary', 'performance', 'recentLoans');
    }

    /**
     * Get loan data for Excel export
     */
    public function getLoanDataForExcel($tgl_dari, $tgl_samp)
    {
        $data = $this->getDetailPinjaman($tgl_dari, $tgl_samp);
        $summary = $this->calculateSummary($data);
        $performance = $this->calculatePerformanceMetrics($data);
        $recentLoans = $this->getRecentLoans($tgl_dari, $tgl_samp);
        
        return compact('data', 'summary', 'performance', 'recentLoans');
    }

    /**
     * Get loan data for web view
     */
    public function getLoanDataForWeb($tgl_dari, $tgl_samp)
    {
        $data = $this->getDetailPinjaman($tgl_dari, $tgl_samp);
        $summary = $this->calculateSummary($data);
        $performance = $this->calculatePerformanceMetrics($data);
        $recentLoans = $this->getRecentLoans($tgl_dari, $tgl_samp);
        
        return compact('data', 'summary', 'performance', 'recentLoans');
    }

    /**
     * Get loan data for API
     */
    public function getLoanDataForApi($tgl_dari, $tgl_samp)
    {
        $data = $this->getDetailPinjaman($tgl_dari, $tgl_samp);
        $summary = $this->calculateSummary($data);
        $performance = $this->calculatePerformanceMetrics($data);
        $recentLoans = $this->getRecentLoans($tgl_dari, $tgl_samp);
        
        return [
            'success' => true,
            'data' => $data,
            'summary' => $summary,
            'performance' => $performance,
            'recent_loans' => $recentLoans,
            'period' => [
                'from' => $tgl_dari,
                'to' => $tgl_samp
            ]
        ];
    }

    public function exportPdf(Request $request)
    {
        // Get filter parameters
        $tgl_dari = $request->input('tgl_dari', date('Y') . '-01-01');
        $tgl_samp = $request->input('tgl_samp', date('Y') . '-12-31');
        
        // Get data
        $data = $this->getDetailPinjaman($tgl_dari, $tgl_samp);
        $summary = $this->calculateSummary($data);
        $performance = $this->calculatePerformanceMetrics($data);
        $recentLoans = $this->getRecentLoans($tgl_dari, $tgl_samp);
        
        // Format dates
        $tgl_dari_formatted = Carbon::parse($tgl_dari)->format('d F Y');
        $tgl_samp_formatted = Carbon::parse($tgl_samp)->format('d F Y');
        
        $pdf = Pdf::loadView('laporan.pdf.target_realisasi', compact(
            'tgl_dari',
            'tgl_samp',
            'tgl_dari_formatted',
            'tgl_samp_formatted',
            'data',
            'summary',
            'performance',
            'recentLoans'
        ));

        return $pdf->download('laporan_target_realisasi_' . $tgl_dari . '_' . $tgl_samp . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        // Get filter parameters
        $tgl_dari = $request->input('tgl_dari', date('Y') . '-01-01');
        $tgl_samp = $request->input('tgl_samp', date('Y') . '-12-31');
        
        // Get data
        $data = $this->getDetailPinjaman($tgl_dari, $tgl_samp);
        $summary = $this->calculateSummary($data);
        $performance = $this->calculatePerformanceMetrics($data);
        $recentLoans = $this->getRecentLoans($tgl_dari, $tgl_samp);
        
        // Format dates
        $tgl_dari_formatted = Carbon::parse($tgl_dari)->format('d F Y');
        $tgl_samp_formatted = Carbon::parse($tgl_samp)->format('d F Y');
        
        // Create Excel file
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set title
        $sheet->setCellValue('A1', 'LAPORAN TARGET & REALISASI PINJAMAN ANGGOTA');
        $sheet->mergeCells('A1:U1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
        
        // Set period info
        $sheet->setCellValue('A2', 'Periode: ' . $tgl_dari_formatted . ' s/d ' . $tgl_samp_formatted);
        $sheet->mergeCells('A2:U2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
        
        // Set headers
        $headers = [
            'No', 'Tanggal Pinjam', 'Nama', 'ID', 'Jabatan', 'Departemen',
            'Pinjaman', 'Saldo Pinjaman', 'JW', '%', 'Pokok', 'Bunga', 'Admin',
            'Target Bulanan', 'Angsuran Ke', 'Sisa Angsuran', 'Pokok Bayar', 
            'Bunga Bayar', 'Denda', 'Total Bayar', 'Sisa Tagihan', 'Status', '% Realisasi'
        ];
        
        $col = 1;
        foreach ($headers as $header) {
            $sheet->setCellValueByColumnAndRow($col, 4, $header);
            $col++;
        }
        
        // Style headers
        $headerRange = 'A4:' . chr(64 + count($headers)) . '4';
        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E5E7EB');
        
        // Fill data
        $rowNum = 5;
        foreach ($data as $row) {
            $sheet->setCellValue('A' . $rowNum, $row['no']);
            $sheet->setCellValue('B' . $rowNum, $row['tgl_pinjam']->format('d/m/Y'));
            $sheet->setCellValue('C' . $rowNum, $row['nama']);
            $sheet->setCellValue('D' . $rowNum, $row['id']);
            $sheet->setCellValue('E' . $rowNum, $row['jabatan']);
            $sheet->setCellValue('F' . $rowNum, $row['departemen']);
            $sheet->setCellValue('G' . $rowNum, $row['jumlah']);
            $sheet->setCellValue('H' . $rowNum, $row['sisa_pokok']);
            $sheet->setCellValue('I' . $rowNum, $row['lama_angsuran']);
            $sheet->setCellValue('J' . $rowNum, $row['bunga']);
            $sheet->setCellValue('K' . $rowNum, $row['pokok_angsuran']);
            $sheet->setCellValue('L' . $rowNum, $row['pokok_bunga']);
            $sheet->setCellValue('M' . $rowNum, $row['biaya_adm']);
            $sheet->setCellValue('N' . $rowNum, $row['target_angsuran_bulanan']);
            $sheet->setCellValue('O' . $rowNum, $row['bln_sudah_angsur']);
            $sheet->setCellValue('P' . $rowNum, $row['sisa_angsuran']);
            $sheet->setCellValue('Q' . $rowNum, $row['total_bayar']);
            $sheet->setCellValue('R' . $rowNum, $row['bunga_ags']);
            $sheet->setCellValue('S' . $rowNum, $row['denda_rp']);
            $sheet->setCellValue('T' . $rowNum, $row['realisasi_pembayaran']);
            $sheet->setCellValue('U' . $rowNum, $row['sisa_tagihan']);
            $sheet->setCellValue('V' . $rowNum, $row['status']);
            $sheet->setCellValue('W' . $rowNum, number_format($row['persentase_realisasi'], 2) . '%');
            
            $rowNum++;
        }
        
        // Add totals row
        $totalRow = $rowNum + 1;
        $sheet->setCellValue('A' . $totalRow, 'TOTAL');
        $sheet->mergeCells('A' . $totalRow . ':F' . $totalRow);
        $sheet->setCellValue('G' . $totalRow, $summary['total_nilai_pinjaman']);
        $sheet->setCellValue('N' . $totalRow, $summary['total_target_angsuran']);
        $sheet->setCellValue('T' . $totalRow, $summary['total_realisasi']);
        $sheet->setCellValue('U' . $totalRow, $summary['total_sisa_tagihan']);
        
        // Style totals
        $totalRange = 'A' . $totalRow . ':W' . $totalRow;
        $sheet->getStyle($totalRange)->getFont()->setBold(true);
        $sheet->getStyle($totalRange)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('D1D5DB');
        
        // Add summary section
        $summaryRow = $totalRow + 3;
        $sheet->setCellValue('A' . $summaryRow, 'RINGKASAN LAPORAN');
        $sheet->mergeCells('A' . $summaryRow . ':W' . $summaryRow);
        $sheet->getStyle('A' . $summaryRow)->getFont()->setBold(true)->setSize(14);
        
        $summaryData = [
            ['Total Pinjaman', $summary['total_pinjaman']],
            ['Total Nilai Pinjaman', 'Rp ' . number_format($summary['total_nilai_pinjaman'])],
            ['Total Target Angsuran', 'Rp ' . number_format($summary['total_target_angsuran'])],
            ['Total Realisasi', 'Rp ' . number_format($summary['total_realisasi'])],
            ['Total Sisa Tagihan', 'Rp ' . number_format($summary['total_sisa_tagihan'])],
            ['Pinjaman Lunas', $summary['pinjaman_lunas']],
            ['Pinjaman Berjalan', $summary['pinjaman_berjalan']],
            ['Pinjaman Jatuh Tempo', $summary['pinjaman_jatuh_tempo']],
            ['Persentase Realisasi', number_format($summary['persentase_realisasi_keseluruhan'], 2) . '%'],
            ['Persentase Pelunasan', number_format($summary['persentase_pelunasan'], 2) . '%']
        ];
        
        $summaryRow++;
        foreach ($summaryData as $index => $summaryItem) {
            $sheet->setCellValue('A' . $summaryRow, $summaryItem[0]);
            $sheet->setCellValue('B' . $summaryRow, $summaryItem[1]);
            $summaryRow++;
        }
        
        // Auto size columns
        foreach (range('A', 'W') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Format currency columns
        $currencyColumns = ['G', 'H', 'K', 'L', 'M', 'N', 'Q', 'R', 'S', 'T', 'U'];
        foreach ($currencyColumns as $col) {
            $sheet->getStyle($col . '5:' . $col . $totalRow)->getNumberFormat()->setFormatCode('#,##0');
        }
        
        $writer = new Xlsx($spreadsheet);
        $filename = 'laporan_target_realisasi_' . $tgl_dari . '_' . $tgl_samp . '.xlsx';
        
        return response()->streamDownload(function() use ($writer) {
        $writer->save('php://output');
        }, $filename);
    }
} 