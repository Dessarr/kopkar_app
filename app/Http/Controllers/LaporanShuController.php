<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TblPinjamanH;
use App\Models\TblPinjamanD;
use App\Models\jns_akun;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\DB;

class LaporanShuController extends Controller
{
    public function index(Request $request)
    {
        $tgl_dari = $request->input('tgl_dari', date('Y').'-01-01');
        $tgl_samp = $request->input('tgl_samp', date('Y').'-12-31');
        
        // Get detailed SHU data
        $data = $this->getShuData($tgl_dari, $tgl_samp);
        $summary = $this->calculateSummary($data);
        $performance = $this->calculatePerformanceMetrics($data);
        $distribution = $this->calculateShuDistribution($data);
        $recentActivities = $this->getRecentActivities($tgl_dari, $tgl_samp);
        
        return view('laporan.shu', [
            'tgl_dari' => $tgl_dari,
            'tgl_samp' => $tgl_samp,
            'data' => $data,
            'summary' => $summary,
            'performance' => $performance,
            'distribution' => $distribution,
            'recentActivities' => $recentActivities
        ]);
    }

    private function getShuData($tgl_dari, $tgl_samp)
    {
        // Total pinjaman, angsuran, denda
        $jml_pinjaman = TblPinjamanH::whereBetween('tgl_pinjam', [$tgl_dari, $tgl_samp])->sum('jumlah');
        $jml_angsuran = TblPinjamanD::whereBetween('tgl_bayar', [$tgl_dari, $tgl_samp])->sum('jumlah_bayar');
        $jml_denda = TblPinjamanD::whereBetween('tgl_bayar', [$tgl_dari, $tgl_samp])->sum('denda_rp');
        
        // Pendapatan - gunakan data dummy untuk testing
        $total_pendapatan = 0;
        $pendapatan_rows = [];
        
        // Data dummy pendapatan untuk testing
        $dummy_pendapatan = [
            ['id' => 1, 'nama' => 'Bunga Pinjaman', 'kode_akun' => 'P001', 'jumlah' => 5000000],
            ['id' => 2, 'nama' => 'Denda Keterlambatan', 'kode_akun' => 'P002', 'jumlah' => 1000000],
            ['id' => 3, 'nama' => 'Biaya Administrasi', 'kode_akun' => 'P003', 'jumlah' => 2000000],
        ];
        
        foreach ($dummy_pendapatan as $item) {
            $pendapatan_rows[] = [
                'id' => $item['id'],
                'nama' => $item['nama'],
                'kode_akun' => $item['kode_akun'],
                'nama_akun' => $item['nama'],
                'jumlah' => $item['jumlah'],
                'persentase' => 0 // Will be calculated later
            ];
            $total_pendapatan += $item['jumlah'];
        }
        
        // Calculate percentage for each income
        foreach ($pendapatan_rows as &$row) {
            $row['persentase'] = $total_pendapatan > 0 ? round(($row['jumlah'] / $total_pendapatan) * 100, 2) : 0;
        }
        
        // Biaya - gunakan data dummy untuk testing
        $total_biaya = 0;
        $biaya_rows = [];
        
        // Data dummy biaya untuk testing
        $dummy_biaya = [
            ['id' => 1, 'nama' => 'Gaji Karyawan', 'kode_akun' => 'B001', 'jumlah' => 3000000],
            ['id' => 2, 'nama' => 'Biaya Operasional', 'kode_akun' => 'B002', 'jumlah' => 1500000],
            ['id' => 3, 'nama' => 'Biaya Administrasi', 'kode_akun' => 'B003', 'jumlah' => 500000],
        ];
        
        foreach ($dummy_biaya as $item) {
            $biaya_rows[] = [
                'id' => $item['id'],
                'nama' => $item['nama'],
                'kode_akun' => $item['kode_akun'],
                'nama_akun' => $item['nama'],
                'jumlah' => $item['jumlah'],
                'persentase' => 0 // Will be calculated later
            ];
            $total_biaya += $item['jumlah'];
        }
        
        // Calculate percentage for each expense
        foreach ($biaya_rows as &$row) {
            $row['persentase'] = $total_biaya > 0 ? round(($row['jumlah'] / $total_biaya) * 100, 2) : 0;
        }
        
        // Calculate SHU
        $shu_sebelum_pajak = $total_pendapatan - $total_biaya;
        
        // Get tax rate from settings (pjk_pph = 5%)
        $tax_rate = DB::table('suku_bunga')->where('opsi_key', 'pjk_pph')->value('opsi_val') ?? 5;
        $pajak_pph = $shu_sebelum_pajak * ($tax_rate / 100);
        $shu_setelah_pajak = $shu_sebelum_pajak - $pajak_pph;
        
        return [
            'jml_pinjaman' => $jml_pinjaman,
            'jml_angsuran' => $jml_angsuran,
            'jml_denda' => $jml_denda,
            'pendapatan_rows' => $pendapatan_rows,
            'total_pendapatan' => $total_pendapatan,
            'biaya_rows' => $biaya_rows,
            'total_biaya' => $total_biaya,
            'shu_sebelum_pajak' => $shu_sebelum_pajak,
            'pajak_pph' => $pajak_pph,
            'tax_rate' => $tax_rate,
            'shu_setelah_pajak' => $shu_setelah_pajak,
            'shu' => $shu_setelah_pajak // For backward compatibility
        ];
    }

    /**
     * Calculate summary statistics
     */
    private function calculateSummary($data)
    {
        $total_pendapatan = $data['total_pendapatan'];
        $total_biaya = $data['total_biaya'];
        $shu_sebelum_pajak = $data['shu_sebelum_pajak'];
        $shu_setelah_pajak = $data['shu_setelah_pajak'];
        
        // Calculate profit margin
        $profit_margin = $total_pendapatan > 0 ? round(($shu_sebelum_pajak / $total_pendapatan) * 100, 2) : 0;
        
        // Calculate expense ratio
        $expense_ratio = $total_pendapatan > 0 ? round(($total_biaya / $total_pendapatan) * 100, 2) : 0;
        
        // Calculate tax burden
        $tax_burden = $shu_sebelum_pajak > 0 ? round(($data['pajak_pph'] / $shu_sebelum_pajak) * 100, 2) : 0;
        
        return [
            'total_pendapatan' => $total_pendapatan,
            'total_biaya' => $total_biaya,
            'shu_sebelum_pajak' => $shu_sebelum_pajak,
            'shu_setelah_pajak' => $shu_setelah_pajak,
            'pajak_pph' => $data['pajak_pph'],
            'tax_rate' => $data['tax_rate'],
            'profit_margin' => $profit_margin,
            'expense_ratio' => $expense_ratio,
            'tax_burden' => $tax_burden
        ];
    }

    /**
     * Calculate performance metrics
     */
    private function calculatePerformanceMetrics($data)
    {
        $total_pendapatan = $data['total_pendapatan'];
        $total_biaya = $data['total_biaya'];
        $shu_setelah_pajak = $data['shu_setelah_pajak'];
        
        // Calculate growth indicators
        $revenue_growth = $this->calculateGrowthRate('pendapatan', $data);
        $expense_growth = $this->calculateGrowthRate('biaya', $data);
        $shu_growth = $this->calculateGrowthRate('shu', $data);
        
        // Calculate efficiency ratios
        $operating_efficiency = $total_pendapatan > 0 ? round(($total_biaya / $total_pendapatan) * 100, 2) : 0;
        $shu_efficiency = $total_pendapatan > 0 ? round(($shu_setelah_pajak / $total_pendapatan) * 100, 2) : 0;
        
        return [
            'revenue_growth' => $revenue_growth,
            'expense_growth' => $expense_growth,
            'shu_growth' => $shu_growth,
            'operating_efficiency' => $operating_efficiency,
            'shu_efficiency' => $shu_efficiency
        ];
    }

    /**
     * Calculate SHU distribution according to cooperative principles
     */
    private function calculateShuDistribution($data)
    {
        $shu_setelah_pajak = $data['shu_setelah_pajak'];
        
        // Standard SHU distribution percentages (can be configured)
        $dana_cadangan = $shu_setelah_pajak * 0.40; // 40%
        $jasa_anggota = $shu_setelah_pajak * 0.40; // 40%
        $dana_pengurus = $shu_setelah_pajak * 0.05; // 5%
        $dana_karyawan = $shu_setelah_pajak * 0.05; // 5%
        $dana_pendidikan = $shu_setelah_pajak * 0.05; // 5%
        $dana_sosial = $shu_setelah_pajak * 0.05; // 5%
        
        // Further distribution of jasa_anggota
        $jasa_usaha = $jasa_anggota * 0.70; // 70% of jasa_anggota
        $jasa_modal = $jasa_anggota * 0.30; // 30% of jasa_anggota
        
        return [
            [
                'label' => 'Dana Cadangan',
                'percentage' => 40,
                'amount' => $dana_cadangan
            ],
            [
                'label' => 'Jasa Anggota',
                'percentage' => 40,
                'amount' => $jasa_anggota,
                'sub_items' => [
                    [
                        'label' => 'Jasa Usaha (70%)',
                        'amount' => $jasa_usaha
                    ],
                    [
                        'label' => 'Jasa Modal (30%)',
                        'amount' => $jasa_modal
                    ]
                ]
            ],
            [
                'label' => 'Dana Pengurus',
                'percentage' => 5,
                'amount' => $dana_pengurus
            ],
            [
                'label' => 'Dana Karyawan',
                'percentage' => 5,
                'amount' => $dana_karyawan
            ],
            [
                'label' => 'Dana Pendidikan',
                'percentage' => 5,
                'amount' => $dana_pendidikan
            ],
            [
                'label' => 'Dana Sosial',
                'percentage' => 5,
                'amount' => $dana_sosial
            ]
        ];
    }

    /**
     * Calculate growth rate compared to previous period
     */
    private function calculateGrowthRate($type, $currentData)
    {
        // This is a simplified calculation
        // In a real application, you would compare with previous period data
        return 0; // Placeholder for now
    }

    /**
     * Get recent financial activities
     */
    private function getRecentActivities($tgl_dari, $tgl_samp)
    {
        $activities = collect();
        
        try {
            // Get recent loan activities
            $recent_loans = TblPinjamanH::with('anggota')
                ->whereBetween('tgl_pinjam', [$tgl_dari, $tgl_samp])
                ->orderBy('tgl_pinjam', 'desc')
                ->limit(5)
                ->get()
                ->map(function($loan) {
                    return [
                        'type' => 'Pinjaman',
                        'description' => 'Pinjaman ' . ($loan->anggota->nama ?? 'N/A'),
                        'amount' => $loan->jumlah,
                        'date' => Carbon::parse($loan->tgl_pinjam)->format('d/m/Y'),
                        'status' => 'Aktif',
                        'icon' => 'hand-holding-usd',
                        'status_class' => 'bg-green-100 text-green-800'
                    ];
                });
            
            $activities = $activities->merge($recent_loans);
        } catch (\Exception $e) {
            // Log error but continue
            \Log::error('Error getting recent loans: ' . $e->getMessage());
        }
        
        try {
            // Get recent payment activities
            $recent_payments = TblPinjamanD::with('pinjaman.anggota')
                ->whereBetween('tgl_bayar', [$tgl_dari, $tgl_samp])
                ->orderBy('tgl_bayar', 'desc')
                ->limit(5)
                ->get()
                ->map(function($payment) {
                    return [
                        'type' => 'Angsuran',
                        'description' => 'Angsuran ' . ($payment->pinjaman->anggota->nama ?? 'N/A'),
                        'amount' => $payment->jumlah_bayar,
                        'date' => Carbon::parse($payment->tgl_bayar)->format('d/m/Y'),
                        'status' => 'Lunas',
                        'icon' => 'money-bill-wave',
                        'status_class' => 'bg-blue-100 text-blue-800'
                    ];
                });
            
            $activities = $activities->merge($recent_payments);
        } catch (\Exception $e) {
            // Log error but continue
            \Log::error('Error getting recent payments: ' . $e->getMessage());
        }
        
        return $activities->sortByDesc('date')->take(10);
    }

    public function exportPdf(Request $request)
    {
        $tgl_dari = $request->input('tgl_dari', date('Y').'-01-01');
        $tgl_samp = $request->input('tgl_samp', date('Y').'-12-31');
        
        // Get detailed data
        $data = $this->getShuData($tgl_dari, $tgl_samp);
        $summary = $this->calculateSummary($data);
        $performance = $this->calculatePerformanceMetrics($data);
        $distribution = $this->calculateShuDistribution($data);
        $recentActivities = $this->getRecentActivities($tgl_dari, $tgl_samp);
        
        $pdf = Pdf::loadView('laporan.pdf.shu', [
            'tgl_dari' => $tgl_dari,
            'tgl_samp' => $tgl_samp,
            'data' => $data,
            'summary' => $summary,
            'performance' => $performance,
            'distribution' => $distribution,
            'recentActivities' => $recentActivities
        ]);
        
        return $pdf->download('laporan_shu_'.$tgl_dari.'_'.$tgl_samp.'.pdf');
    }

    public function exportExcel(Request $request)
    {
        $tgl_dari = $request->input('tgl_dari', date('Y').'-01-01');
        $tgl_samp = $request->input('tgl_samp', date('Y').'-12-31');
        
        // Get detailed data
        $data = $this->getShuData($tgl_dari, $tgl_samp);
        $summary = $this->calculateSummary($data);
        $performance = $this->calculatePerformanceMetrics($data);
        $distribution = $this->calculateShuDistribution($data);
        $recentActivities = $this->getRecentActivities($tgl_dari, $tgl_samp);
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set title
        $sheet->setCellValue('A1', 'LAPORAN SISA HASIL USAHA (SHU)');
        $sheet->setCellValue('A2', 'Koperasi Karyawan');
        $sheet->setCellValue('A3', 'Periode: ' . Carbon::parse($tgl_dari)->format('d M Y') . ' - ' . Carbon::parse($tgl_samp)->format('d M Y'));
        $sheet->setCellValue('A4', 'Dicetak pada: ' . Carbon::now()->format('d M Y H:i:s'));
        $sheet->mergeCells('A1:D1');
        $sheet->mergeCells('A2:D2');
        $sheet->mergeCells('A3:D3');
        $sheet->mergeCells('A4:D4');
        
        // Style title
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A3')->getFont()->setSize(12);
        $sheet->getStyle('A4')->getFont()->setSize(10);
        
        // Summary section
        $rowNum = 6;
        $sheet->setCellValue('A'.$rowNum, 'RINGKASAN SHU');
        $sheet->mergeCells('A'.$rowNum.':B'.$rowNum);
        $sheet->getStyle('A'.$rowNum)->getFont()->setBold(true);
        
        $rowNum++;
        $sheet->setCellValue('A'.$rowNum, 'Total Pendapatan:');
        $sheet->setCellValue('B'.$rowNum, 'Rp ' . number_format($summary['total_pendapatan'], 0, ',', '.'));
        $sheet->setCellValue('A'.($rowNum+1), 'Total Biaya:');
        $sheet->setCellValue('B'.($rowNum+1), 'Rp ' . number_format($summary['total_biaya'], 0, ',', '.'));
        $sheet->setCellValue('A'.($rowNum+2), 'SHU Sebelum Pajak:');
        $sheet->setCellValue('B'.($rowNum+2), 'Rp ' . number_format($summary['shu_sebelum_pajak'], 0, ',', '.'));
        $sheet->setCellValue('A'.($rowNum+3), 'Pajak PPh (' . $summary['tax_rate'] . '%):');
        $sheet->setCellValue('B'.($rowNum+3), 'Rp ' . number_format($summary['pajak_pph'], 0, ',', '.'));
        $sheet->setCellValue('A'.($rowNum+4), 'SHU Setelah Pajak:');
        $sheet->setCellValue('B'.($rowNum+4), 'Rp ' . number_format($summary['shu_setelah_pajak'], 0, ',', '.'));
        
        // Performance metrics
        $sheet->setCellValue('D'.$rowNum, 'METRIK KINERJA');
        $sheet->mergeCells('D'.$rowNum.':E'.$rowNum);
        $sheet->getStyle('D'.$rowNum)->getFont()->setBold(true);
        
        $sheet->setCellValue('D'.($rowNum+1), 'Profit Margin:');
        $sheet->setCellValue('E'.($rowNum+1), number_format($summary['profit_margin'], 2) . '%');
        $sheet->setCellValue('D'.($rowNum+2), 'Expense Ratio:');
        $sheet->setCellValue('E'.($rowNum+2), number_format($summary['expense_ratio'], 2) . '%');
        $sheet->setCellValue('D'.($rowNum+3), 'Tax Burden:');
        $sheet->setCellValue('E'.($rowNum+3), number_format($summary['tax_burden'], 2) . '%');
        $sheet->setCellValue('D'.($rowNum+4), 'SHU Efficiency:');
        $sheet->setCellValue('E'.($rowNum+4), number_format($performance['shu_efficiency'], 2) . '%');
        
        // Main data section
        $startRow = $rowNum + 7;
        $sheet->setCellValue('A'.$startRow, 'DETAIL PENDAPATAN DAN BIAYA');
        $sheet->mergeCells('A'.$startRow.':C'.$startRow);
        $sheet->getStyle('A'.$startRow)->getFont()->setBold(true);
        
        $startRow++;
        $sheet->setCellValue('A'.$startRow, 'Keterangan');
        $sheet->setCellValue('B'.$startRow, 'Jumlah');
        $sheet->setCellValue('C'.$startRow, 'Persentase');
        
        // Style headers
        $sheet->getStyle('A'.$startRow.':C'.$startRow)->getFont()->setBold(true);
        $sheet->getStyle('A'.$startRow.':C'.$startRow)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE0E0E0');
        
        $rowNum = $startRow + 1;
        
        // Add income data
        $sheet->setCellValue('A'.$rowNum, 'PENDAPATAN');
        $sheet->getStyle('A'.$rowNum)->getFont()->setBold(true);
        $rowNum++;
        
        foreach ($data['pendapatan_rows'] as $row) {
            $sheet->setCellValue('A'.$rowNum, $row['nama']);
            $sheet->setCellValue('B'.$rowNum, 'Rp ' . number_format($row['jumlah'], 0, ',', '.'));
            $sheet->setCellValue('C'.$rowNum, number_format($row['persentase'], 2) . '%');
            $rowNum++;
        }
        
        $sheet->setCellValue('A'.$rowNum, 'TOTAL PENDAPATAN');
        $sheet->setCellValue('B'.$rowNum, 'Rp ' . number_format($data['total_pendapatan'], 0, ',', '.'));
        $sheet->setCellValue('C'.$rowNum, '100.00%');
        $sheet->getStyle('A'.$rowNum.':C'.$rowNum)->getFont()->setBold(true);
        $rowNum++;
        
        // Add expense data
        $rowNum++;
        $sheet->setCellValue('A'.$rowNum, 'BIAYA');
        $sheet->getStyle('A'.$rowNum)->getFont()->setBold(true);
        $rowNum++;
        
        foreach ($data['biaya_rows'] as $row) {
            $sheet->setCellValue('A'.$rowNum, $row['nama']);
            $sheet->setCellValue('B'.$rowNum, 'Rp ' . number_format($row['jumlah'], 0, ',', '.'));
            $sheet->setCellValue('C'.$rowNum, number_format($row['persentase'], 2) . '%');
            $rowNum++;
        }
        
        $sheet->setCellValue('A'.$rowNum, 'TOTAL BIAYA');
        $sheet->setCellValue('B'.$rowNum, 'Rp ' . number_format($data['total_biaya'], 0, ',', '.'));
        $sheet->setCellValue('C'.$rowNum, '100.00%');
        $sheet->getStyle('A'.$rowNum.':C'.$rowNum)->getFont()->setBold(true);
        $rowNum++;
        
        // SHU calculation
        $rowNum += 2;
        $sheet->setCellValue('A'.$rowNum, 'PERHITUNGAN SHU');
        $sheet->mergeCells('A'.$rowNum.':C'.$rowNum);
        $sheet->getStyle('A'.$rowNum)->getFont()->setBold(true);
        
        $rowNum++;
        $sheet->setCellValue('A'.$rowNum, 'SHU Sebelum Pajak:');
        $sheet->setCellValue('B'.$rowNum, 'Rp ' . number_format($data['shu_sebelum_pajak'], 0, ',', '.'));
        $rowNum++;
        $sheet->setCellValue('A'.$rowNum, 'Pajak PPh (' . $data['tax_rate'] . '%):');
        $sheet->setCellValue('B'.$rowNum, 'Rp ' . number_format($data['pajak_pph'], 0, ',', '.'));
        $rowNum++;
        $sheet->setCellValue('A'.$rowNum, 'SHU Setelah Pajak:');
        $sheet->setCellValue('B'.$rowNum, 'Rp ' . number_format($data['shu_setelah_pajak'], 0, ',', '.'));
        $sheet->getStyle('A'.$rowNum.':B'.$rowNum)->getFont()->setBold(true);
        
        // SHU Distribution
        $rowNum += 2;
        $sheet->setCellValue('A'.$rowNum, 'PEMBAGIAN SHU');
        $sheet->mergeCells('A'.$rowNum.':C'.$rowNum);
        $sheet->getStyle('A'.$rowNum)->getFont()->setBold(true);
        
        $rowNum++;
        foreach ($distribution as $item) {
            $sheet->setCellValue('A'.$rowNum, $item['label'] . ' (' . $item['percentage'] . '%):');
            $sheet->setCellValue('B'.$rowNum, 'Rp ' . number_format($item['amount'], 0, ',', '.'));
            $rowNum++;
            
            if (isset($item['sub_items'])) {
                foreach ($item['sub_items'] as $subItem) {
                    $sheet->setCellValue('A'.$rowNum, '  - ' . $subItem['label']);
                    $sheet->setCellValue('B'.$rowNum, 'Rp ' . number_format($subItem['amount'], 0, ',', '.'));
                    $rowNum++;
                }
            }
        }
        
        // Auto-size columns
        foreach (range('A', 'E') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        $writer = new Xlsx($spreadsheet);
        $filename = 'laporan_shu_'.$tgl_dari.'_'.$tgl_samp.'.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }
} 