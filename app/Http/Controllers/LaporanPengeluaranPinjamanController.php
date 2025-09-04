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

class LaporanPengeluaranPinjamanController extends Controller
{
    /**
     * Display the loan disbursement report page
     * This implements the accounting principle for loan disbursement tracking
     */
    public function index(Request $request)
    {
        // Get filter parameters with default values
        $tgl_dari = $request->input('tgl_dari', date('Y').'-01-01');
        $tgl_samp = $request->input('tgl_samp', date('Y').'-12-31');
        
        // Get detailed loan disbursement data with proper accounting logic
        $data = $this->getDetailPengeluaran($tgl_dari, $tgl_samp);
        
        // Calculate summary statistics
        $summary = $this->calculateSummary($data['rows']);
        
        // Get performance metrics
        $performance = $this->calculatePerformanceMetrics($data['rows']);
        
        // Get recent loan activities
        $recentLoans = $this->getRecentLoans($tgl_dari, $tgl_samp);
        
        return view('laporan.pengeluaran_pinjaman', [
            'tgl_dari' => $tgl_dari,
            'tgl_samp' => $tgl_samp,
            'data' => $data['rows'],
            'total' => $data['total'],
            'summary' => $summary,
            'performance' => $performance,
            'recentLoans' => $recentLoans
        ]);
    }

    /**
     * Get detailed loan disbursement data with proper accounting principles
     * This implements the accounting principle for loan disbursement tracking
     */
    private function getDetailPengeluaran($tgl_dari, $tgl_samp)
    {
        $pinjaman = TblPinjamanH::with('anggota')
            ->whereDate('tgl_pinjam', '>=', $tgl_dari)
            ->whereDate('tgl_pinjam', '<=', $tgl_samp)
            ->orderBy('tgl_pinjam', 'asc')
            ->get();
        
        // Filter out loans without anggota relationship
        $pinjaman = $pinjaman->filter(function($row) {
            return $row->anggota !== null;
        });
        
        $result = [];
        $total_pinjaman = 0;
        $total_tagihan = 0;
        $total_dibayar = 0;
        $total_sisa_tagihan = 0;
        $total_bunga = 0;
        $total_denda = 0;
        $total_adm = 0;
        $no = 1;
        
        foreach ($pinjaman as $row) {
            $anggota = $row->anggota;
            
            // Get installment data
            $angsuran = TblPinjamanD::where('pinjam_id', $row->id)->get();
            $jml_bayar = $angsuran->sum('jumlah_bayar');
            $jml_denda = $angsuran->sum('denda_rp');
            $jml_adm = $angsuran->sum('biaya_adm');
            $jml_bunga = $angsuran->sum('bunga');
            
            // Calculate total tagihan (principal + interest + admin fee)
            $total_tagihan_loan = $row->jumlah + ($row->bunga_rp ?? 0) + ($row->biaya_adm ?? 0);
            $sisa_tagihan = $total_tagihan_loan - $jml_bayar;
            
            // Calculate loan status
            $status = $this->determineLoanStatus($row, $angsuran->count(), $sisa_tagihan);
            
            $result[] = [
                'no' => $no++,
                'tgl_pinjam' => $row->tgl_pinjam,
                'nama' => $anggota ? $anggota->nama : 'N/A',
                'id' => 'AG' . str_pad($anggota ? $anggota->id : 0, 4, '0', STR_PAD_LEFT),
                'id_anggota' => $anggota ? $anggota->id : 0,
                'jumlah' => $row->jumlah ?? 0,
                'lama_angsuran' => $row->lama_angsuran ?? 0,
                'lunas' => $row->lunas ?? 'Belum Lunas',
                'pokok_angsuran' => $row->pokok_angsuran ?? 0,
                'pokok_bunga' => $row->pokok_bunga ?? 0,
                'bunga' => $row->bunga ?? 0,
                'biaya_adm' => $row->biaya_adm ?? 0,
                'ags_per_bulan' => $row->ags_per_bulan ?? 0,
                'tagihan' => $total_tagihan_loan,
                'jml_bunga' => $jml_bunga,
                'jml_denda' => $jml_denda,
                'jml_adm' => $jml_adm,
                'jml_bayar' => $jml_bayar,
                'sisa_tagihan' => $sisa_tagihan,
                'alamat' => $anggota ? $anggota->alamat : '-',
                'notelp' => $anggota ? $anggota->notelp : '-',
                'jaminan' => $row->keterangan ?? '-',
                'status' => $status,
                'status_badge' => $this->getStatusBadge($status),
                'persentase_bayar' => $total_tagihan_loan > 0 ? (($jml_bayar / $total_tagihan_loan) * 100) : 0
            ];
            
            $total_pinjaman += $row->jumlah ?? 0;
            $total_tagihan += $total_tagihan_loan;
            $total_dibayar += $jml_bayar;
            $total_sisa_tagihan += $sisa_tagihan;
            $total_bunga += $jml_bunga;
            $total_denda += $jml_denda;
            $total_adm += $jml_adm;
        }
        
        return [
            'rows' => $result,
            'total' => [
                'total_pinjaman' => $total_pinjaman,
                'total_tagihan' => $total_tagihan,
                'total_dibayar' => $total_dibayar,
                'total_sisa_tagihan' => $total_sisa_tagihan,
                'total_bunga' => $total_bunga,
                'total_denda' => $total_denda,
                'total_adm' => $total_adm
            ]
        ];
    }

    /**
     * Determine loan status based on payment progress
     */
    private function determineLoanStatus($loan, $bln_sudah_angsur, $sisa_tagihan)
    {
        $lunas = $loan->lunas ?? 'Belum Lunas';
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
                'total_tagihan' => 0,
                'total_dibayar' => 0,
                'total_sisa_tagihan' => 0,
                'pinjaman_lunas' => 0,
                'pinjaman_berjalan' => 0,
                'pinjaman_jatuh_tempo' => 0,
                'pinjaman_belum_mulai' => 0,
                'persentase_pelunasan' => 0,
                'rata_rata_pinjaman' => 0,
                'pinjaman_tertinggi' => 0,
                'pinjaman_terendah' => 0
            ];
        }
        
        $summary = [
            'total_pinjaman' => count($data),
            'total_nilai_pinjaman' => 0,
            'total_tagihan' => 0,
            'total_dibayar' => 0,
            'total_sisa_tagihan' => 0,
            'pinjaman_lunas' => 0,
            'pinjaman_berjalan' => 0,
            'pinjaman_jatuh_tempo' => 0,
            'pinjaman_belum_mulai' => 0
        ];
        
        $pinjaman_values = [];
        
        foreach ($data as $row) {
            $summary['total_nilai_pinjaman'] += $row['jumlah'];
            $summary['total_tagihan'] += $row['tagihan'];
            $summary['total_dibayar'] += $row['jml_bayar'];
            $summary['total_sisa_tagihan'] += $row['sisa_tagihan'];
            
            // Count by status
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
            
            $pinjaman_values[] = $row['jumlah'];
        }
        
        // Calculate percentages
        $summary['persentase_pelunasan'] = $summary['total_tagihan'] > 0 
            ? (($summary['total_dibayar'] / $summary['total_tagihan']) * 100) 
            : 0;
        
        // Calculate loan statistics
        $summary['rata_rata_pinjaman'] = $summary['total_pinjaman'] > 0 
            ? ($summary['total_nilai_pinjaman'] / $summary['total_pinjaman']) 
            : 0;
        $summary['pinjaman_tertinggi'] = !empty($pinjaman_values) ? max($pinjaman_values) : 0;
        $summary['pinjaman_terendah'] = !empty($pinjaman_values) ? min($pinjaman_values) : 0;
        
        return $summary;
    }

    /**
     * Calculate performance metrics for analysis
     */
    private function calculatePerformanceMetrics($data)
    {
        if (empty($data) || !is_array($data)) {
            return [
                'rata_rata_pinjaman' => 0,
                'rata_rata_pembayaran' => 0,
                'pinjaman_tertinggi' => 0,
                'pinjaman_terendah' => 0,
                'persentase_tertinggi' => 0,
                'persentase_terendah' => 100
            ];
        }
        
        $pinjaman_values = array_column($data, 'jumlah');
        $pembayaran_values = array_column($data, 'jml_bayar');
        $persentase_values = array_column($data, 'persentase_bayar');
        
        return [
            'rata_rata_pinjaman' => count($pinjaman_values) > 0 ? array_sum($pinjaman_values) / count($pinjaman_values) : 0,
            'rata_rata_pembayaran' => count($pembayaran_values) > 0 ? array_sum($pembayaran_values) / count($pembayaran_values) : 0,
            'pinjaman_tertinggi' => !empty($pinjaman_values) ? max($pinjaman_values) : 0,
            'pinjaman_terendah' => !empty($pinjaman_values) ? min($pinjaman_values) : 0,
            'persentase_tertinggi' => !empty($persentase_values) ? max($persentase_values) : 0,
            'persentase_terendah' => !empty($persentase_values) ? min($persentase_values) : 100
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
            ->filter(function($loan) {
                return $loan->anggota !== null;
            })
            ->map(function ($loan) {
                $angsuran = TblPinjamanD::where('pinjam_id', $loan->id)->get();
                $total_bayar = $angsuran->sum('jumlah_bayar');
                $total_tagihan = $loan->jumlah + ($loan->bunga_rp ?? 0) + ($loan->biaya_adm ?? 0);
                $sisa_tagihan = $total_tagihan - $total_bayar;
                
                // Determine status
                $status = $this->determineLoanStatus($loan, $angsuran->count(), $sisa_tagihan);
                
                return [
                    'id' => 'PNJ' . str_pad($loan->id, 5, '0', STR_PAD_LEFT),
                    'anggota' => $loan->anggota ? $loan->anggota->nama : 'N/A',
                    'jumlah' => $loan->jumlah,
                    'tgl_pinjam' => $loan->tgl_pinjam->format('d/m/Y'),
                    'status' => $status,
                    'sisa_tagihan' => $sisa_tagihan,
                    'persentase' => $total_tagihan > 0 ? (($total_bayar / $total_tagihan) * 100) : 0
                ];
            });
    }

    public function exportPdf(Request $request)
    {
        $tgl_dari = $request->input('tgl_dari', date('Y').'-01-01');
        $tgl_samp = $request->input('tgl_samp', date('Y').'-12-31');
        
        // Get detailed data
        $data = $this->getDetailPengeluaran($tgl_dari, $tgl_samp);
        $summary = $this->calculateSummary($data['rows']);
        $performance = $this->calculatePerformanceMetrics($data['rows']);
        $recentLoans = $this->getRecentLoans($tgl_dari, $tgl_samp);
        
        $pdf = Pdf::loadView('laporan.pdf.pengeluaran_pinjaman', [
            'tgl_dari' => $tgl_dari,
            'tgl_samp' => $tgl_samp,
            'data' => $data['rows'],
            'total' => $data['total'],
            'summary' => $summary,
            'performance' => $performance,
            'recentLoans' => $recentLoans
        ])->setPaper('a4', 'landscape');
        
        return $pdf->download('laporan_pengeluaran_pinjaman_'.$tgl_dari.'_'.$tgl_samp.'.pdf');
    }

    public function exportExcel(Request $request)
    {
        $tgl_dari = $request->input('tgl_dari', date('Y').'-01-01');
        $tgl_samp = $request->input('tgl_samp', date('Y').'-12-31');
        
        // Get detailed data
        $data = $this->getDetailPengeluaran($tgl_dari, $tgl_samp);
        $summary = $this->calculateSummary($data['rows']);
        $performance = $this->calculatePerformanceMetrics($data['rows']);
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set title
        $sheet->setCellValue('A1', 'LAPORAN PENGELUARAN PINJAMAN');
        $sheet->setCellValue('A2', 'Periode: ' . Carbon::parse($tgl_dari)->format('d/m/Y') . ' s/d ' . Carbon::parse($tgl_samp)->format('d/m/Y'));
        $sheet->mergeCells('A1:S1');
        $sheet->mergeCells('A2:S2');
        
        // Set headers
        $headers = [
            'No', 'Tanggal Pinjam', 'Nama', 'ID', 'Pokok Pinjaman', 'Lama Pinjaman', 
            'Status Lunas', 'Pokok Angsuran', 'Bunga', 'Biaya Adm', 'Jumlah Angsuran', 
            'Tagihan', 'Total Bunga', 'Total Denda', 'Total Biaya Adm', 'Dibayar', 
            'Sisa Tagihan', 'Alamat', 'No. Telp', 'Jaminan'
        ];
        
        $col = 1;
        foreach ($headers as $header) {
            $sheet->setCellValueByColumnAndRow($col, 4, $header);
            $col++;
        }
        
        // Style headers
        $sheet->getStyle('A4:S4')->getFont()->setBold(true);
        $sheet->getStyle('A4:S4')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE0E0E0');
        
        // Add data
        $rowNum = 5;
        foreach ($data['rows'] as $row) {
            $sheet->setCellValue('A'.$rowNum, $row['no']);
            $sheet->setCellValue('B'.$rowNum, Carbon::parse($row['tgl_pinjam'])->format('d/m/Y'));
            $sheet->setCellValue('C'.$rowNum, $row['nama']);
            $sheet->setCellValue('D'.$rowNum, $row['id']);
            $sheet->setCellValue('E'.$rowNum, $row['jumlah']);
            $sheet->setCellValue('F'.$rowNum, $row['lama_angsuran']);
            $sheet->setCellValue('G'.$rowNum, $row['lunas']);
            $sheet->setCellValue('H'.$rowNum, $row['pokok_angsuran']);
            $sheet->setCellValue('I'.$rowNum, $row['bunga']);
            $sheet->setCellValue('J'.$rowNum, $row['biaya_adm']);
            $sheet->setCellValue('K'.$rowNum, $row['ags_per_bulan']);
            $sheet->setCellValue('L'.$rowNum, $row['tagihan']);
            $sheet->setCellValue('M'.$rowNum, $row['jml_bunga']);
            $sheet->setCellValue('N'.$rowNum, $row['jml_denda']);
            $sheet->setCellValue('O'.$rowNum, $row['jml_adm']);
            $sheet->setCellValue('P'.$rowNum, $row['jml_bayar']);
            $sheet->setCellValue('Q'.$rowNum, $row['sisa_tagihan']);
            $sheet->setCellValue('R'.$rowNum, $row['alamat']);
            $sheet->setCellValue('S'.$rowNum, $row['notelp']);
            $sheet->setCellValue('T'.$rowNum, $row['jaminan']);
            $rowNum++;
        }
        
        // Add summary row
        $sheet->setCellValue('A'.$rowNum, 'TOTAL');
        $sheet->mergeCells('A'.$rowNum.':D'.$rowNum);
        $sheet->setCellValue('E'.$rowNum, $data['total']['total_pinjaman']);
        $sheet->setCellValue('L'.$rowNum, $data['total']['total_tagihan']);
        $sheet->setCellValue('P'.$rowNum, $data['total']['total_dibayar']);
        $sheet->setCellValue('Q'.$rowNum, $data['total']['total_sisa_tagihan']);
        
        // Style summary row
        $sheet->getStyle('A'.$rowNum.':T'.$rowNum)->getFont()->setBold(true);
        $sheet->getStyle('A'.$rowNum.':T'.$rowNum)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFFE0B2');
        
        // Add summary statistics
        $rowNum += 2;
        $sheet->setCellValue('A'.$rowNum, 'RINGKASAN STATISTIK');
        $sheet->mergeCells('A'.$rowNum.':B'.$rowNum);
        $sheet->getStyle('A'.$rowNum)->getFont()->setBold(true);
        
        $rowNum++;
        $sheet->setCellValue('A'.$rowNum, 'Total Pinjaman:');
        $sheet->setCellValue('B'.$rowNum, $summary['total_pinjaman']);
        $rowNum++;
        $sheet->setCellValue('A'.$rowNum, 'Total Nilai Pinjaman:');
        $sheet->setCellValue('B'.$rowNum, 'Rp ' . number_format($summary['total_nilai_pinjaman'], 0, ',', '.'));
        $rowNum++;
        $sheet->setCellValue('A'.$rowNum, 'Total Tagihan:');
        $sheet->setCellValue('B'.$rowNum, 'Rp ' . number_format($summary['total_tagihan'], 0, ',', '.'));
        $rowNum++;
        $sheet->setCellValue('A'.$rowNum, 'Total Dibayar:');
        $sheet->setCellValue('B'.$rowNum, 'Rp ' . number_format($summary['total_dibayar'], 0, ',', '.'));
        $rowNum++;
        $sheet->setCellValue('A'.$rowNum, 'Total Sisa Tagihan:');
        $sheet->setCellValue('B'.$rowNum, 'Rp ' . number_format($summary['total_sisa_tagihan'], 0, ',', '.'));
        $rowNum++;
        $sheet->setCellValue('A'.$rowNum, 'Persentase Pelunasan:');
        $sheet->setCellValue('B'.$rowNum, number_format($summary['persentase_pelunasan'], 2) . '%');
        
        // Auto-size columns
        foreach (range('A', 'T') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        $writer = new Xlsx($spreadsheet);
        $filename = 'laporan_pengeluaran_pinjaman_'.$tgl_dari.'_'.$tgl_samp.'.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }
} 