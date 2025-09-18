<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TblPinjamanH;
use App\Models\TblPinjamanD;
use App\Models\data_anggota;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
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
        $summary = $this->calculateSummary($data['rows']);
        
        // Get performance metrics
        $performance = $this->calculatePerformanceMetrics($data['rows']);
        
        // Get recent installment activities
        $recentInstallments = $this->getRecentInstallments($tgl_dari, $tgl_samp);
        
        return view('laporan.angsuran_pinjaman', [
            'tgl_dari' => $tgl_dari,
            'tgl_samp' => $tgl_samp,
            'data' => $data['rows'],
            'total' => $data['total'],
            'summary' => $summary,
            'performance' => $performance,
            'recentInstallments' => $recentInstallments
        ]);
    }

    /**
     * Get detailed installment data using v_rekap_det_angsuran view
     * This implements the accounting principle for loan installment tracking
     */
    private function getDetailAngsuran($tgl_dari, $tgl_samp)
    {
        // Get installment data from v_rekap_det_angsuran view
        $angsuran = DB::table('v_rekap_det_angsuran')
            ->whereDate('tgl_bayar', '>=', $tgl_dari)
            ->whereDate('tgl_bayar', '<=', $tgl_samp)
            ->orderBy('tgl_bayar', 'asc')
            ->get();
        
        $result = [];
        $total_pokok = 0;
        $total_bunga = 0;
        $total_denda = 0;
        $total_biaya_adm = 0;
        $total_jumlah_angsuran = 0;
        $no = 1;
        
        foreach ($angsuran as $row) {
            // Calculate installment components
            $pokok = $row->jumlah_bayar ?? 0;
            $bunga = $row->bunga ?? 0;
            $denda = $row->denda_rp ?? 0;
            $biaya_adm = $row->biaya_adm ?? 0;
            $jumlah_angsuran = $pokok + $bunga + $denda + $biaya_adm;
            
            // Calculate saldo pinjaman (jumlah - sisa_pokok)
            $saldo_pinjaman = $row->jumlah - $row->sisa_pokok;
            $saldo_akhir = $row->sisa_pokok;
            
            // Determine installment status
            $status = $this->determineInstallmentStatus($row, $saldo_akhir);
            
            $result[] = [
                'no' => $no++,
                'tgl_pinjam' => Carbon::parse($row->tgl_pinjam),
                'nama' => $row->no_ktp . '<br/>' . $row->nama,
                'id' => $row->id,
                'jumlah' => $row->jumlah ?? 0, // Pinjaman awal
                'lama_angsuran' => $row->lama_angsuran ?? 0, // Jangka waktu
                'jumlah_bunga' => $row->jumlah_bunga ?? 0, // Persentase bunga
                'saldo_pinjaman' => $saldo_pinjaman, // Saldo sebelum angsuran
                'pokok' => $pokok,
                'bunga' => $bunga,
                'denda' => $denda,
                'biaya_adm' => $biaya_adm,
                'jumlah_angsuran' => $jumlah_angsuran,
                'saldo_akhir' => $saldo_akhir,
                'angsuran_ke' => $row->angsuran_ke ?? 0,
                'tgl_bayar' => Carbon::parse($row->tgl_bayar),
                'status' => $status,
                'status_badge' => $this->getInstallmentStatusBadge($status),
                'persentase_pelunasan' => $row->jumlah > 0 ? ((($row->jumlah - $saldo_akhir) / $row->jumlah) * 100) : 0
            ];
            
            $total_pokok += $pokok;
            $total_bunga += $bunga;
            $total_denda += $denda;
            $total_biaya_adm += $biaya_adm;
            $total_jumlah_angsuran += $jumlah_angsuran;
        }
        
        return [
            'rows' => $result,
            'total' => [
                'total_pokok' => $total_pokok,
                'total_bunga' => $total_bunga,
                'total_denda' => $total_denda,
                'total_biaya_adm' => $total_biaya_adm,
                'total_jumlah_angsuran' => $total_jumlah_angsuran
            ]
        ];
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
        } elseif ($installment->terlambat > 0) {
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
        $angsuran = DB::table('v_rekap_det_angsuran')
            ->whereDate('tgl_bayar', '>=', $tgl_dari)
            ->whereDate('tgl_bayar', '<=', $tgl_samp)
            ->orderBy('tgl_bayar', 'desc')
            ->limit(10)
            ->get();
        
        return $angsuran->map(function ($installment) {
            // Calculate installment components
            $pokok = $installment->jumlah_bayar ?? 0;
            $bunga = $installment->bunga ?? 0;
            $denda = $installment->denda_rp ?? 0;
            $biaya_adm = $installment->biaya_adm ?? 0;
            $jumlah_angsuran = $pokok + $bunga + $denda + $biaya_adm;
            
            // Determine status
            $status = $this->determineInstallmentStatus($installment, $installment->sisa_pokok);
            
            return [
                'id' => 'INS' . str_pad($installment->id, 5, '0', STR_PAD_LEFT),
                'anggota' => $installment->no_ktp . '/' . $installment->nama,
                'pinjaman_id' => 'PNJ' . str_pad($installment->pinjam_id, 5, '0', STR_PAD_LEFT),
                'jumlah_angsuran' => $jumlah_angsuran,
                'tgl_bayar' => Carbon::parse($installment->tgl_bayar)->format('d/m/Y'),
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
        $summary = $this->calculateSummary($data['rows']);
        $performance = $this->calculatePerformanceMetrics($data['rows']);
        $recentInstallments = $this->getRecentInstallments($tgl_dari, $tgl_samp);
        
        $pdf = Pdf::loadView('laporan.pdf.angsuran_pinjaman', [
            'tgl_dari' => $tgl_dari,
            'tgl_samp' => $tgl_samp,
            'data' => $data['rows'],
            'summary' => $summary,
            'performance' => $performance,
            'recentInstallments' => $recentInstallments
        ])->setPaper('a4', 'landscape');
        
        return $pdf->download('laporan_angsuran_pinjaman_'.$tgl_dari.'_'.$tgl_samp.'.pdf');
    }

} 