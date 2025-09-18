<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TblPinjamanH;
use App\Models\TblPinjamanD;
use App\Models\data_anggota;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class LaporanKasPinjamanController extends Controller
{
    public function index(Request $request)
    {
        // Get filter parameters with default values
        $tgl_dari = $request->input('tgl_dari', date('Y') . '-01-01');
        $tgl_samp = $request->input('tgl_samp', date('Y') . '-12-31');
        
        // Get loan data using v_hitung_pinjaman
        $data = $this->getRekapPinjamanFromView($tgl_dari, $tgl_samp);
        
        // Calculate summary statistics
        $summary = $this->calculateSummaryFromView($data);
        
        return view('laporan.kas_pinjaman', compact(
            'tgl_dari',
            'tgl_samp',
            'data',
            'summary'
        ));
    }

    /**
     * Get comprehensive loan data using tbl_pinjaman_h and v_hitung_pinjaman
     * This implements the accounting principle for loan management and credit risk
     */
    private function getRekapPinjamanFromView($tgl_dari, $tgl_samp)
    {
        // Get loan data from tbl_pinjaman_h with payment data from v_hitung_pinjaman
        $loans = DB::table('tbl_pinjaman_h as p')
            ->leftJoin('v_hitung_pinjaman as v', 'p.id', '=', 'v.id')
            ->whereDate('p.tgl_pinjam', '>=', $tgl_dari)
            ->whereDate('p.tgl_pinjam', '<=', $tgl_samp)
            ->select(
                'p.id',
                'p.no_ktp',
                'p.tgl_pinjam',
                'p.lama_angsuran',
                'p.jumlah_angsuran',
                'p.jumlah',
                'p.bunga',
                'p.bunga_rp',
                'p.biaya_adm',
                'p.lunas',
                'p.status',
                'p.jenis_pinjaman',
                'v.total_bayar',
                'v.sisa_pokok',
                'v.tagihan'
            )
            ->orderBy('p.tgl_pinjam', 'asc')
            ->get();
        
        $result = [];
        $no = 1;
        
        foreach ($loans as $loan) {
            // Get member data
            $anggota = data_anggota::where('no_ktp', $loan->no_ktp)->first();
            
            // Calculate remaining installments
            $sisa_angsuran = $loan->lama_angsuran - $this->calculatePaidInstallments($loan->id);
            
            // Calculate remaining balance
            $sisa_tagihan = $loan->tagihan - $loan->total_bayar;
            
            // Generate loan code
            $kode_pinjam = 'TPJ' . str_pad($loan->id, 5, '0', STR_PAD_LEFT);
            
            $result[] = [
                'no' => $no++,
                'id' => $loan->id,
                'kode_pinjam' => $kode_pinjam,
                'tgl_pinjam' => $loan->tgl_pinjam,
                'nama' => $anggota ? 'AG' . str_pad($anggota->id, 8, '0', STR_PAD_LEFT) . '/' . $anggota->nama : 'N/A',
                'pokok_pinjaman' => $loan->jumlah,
                'lama_angsuran' => $loan->lama_angsuran ?? 0,
                'bunga' => $loan->bunga ?? 0,
                'biaya_adm' => $loan->biaya_adm ?? 0,
                'pokok_angsuran' => $loan->jumlah_angsuran ?? 0,
                'bunga_pinjaman' => $loan->bunga_rp ?? 0,
                'angsuran' => ($loan->jumlah_angsuran ?? 0) + ($loan->bunga_rp ?? 0),
                'jumlah_bayar' => $loan->total_bayar ?? 0,
                'sisa_angsuran' => $sisa_angsuran,
                'sisa_tagihan' => $sisa_tagihan,
                'status' => $loan->lunas
            ];
        }
        
        return $result;
    }
    
    /**
     * Calculate paid installments for a loan
     */
    private function calculatePaidInstallments($pinjamId)
    {
        return DB::table('tbl_pinjaman_d')
            ->where('pinjam_id', $pinjamId)
            ->count();
    }
    
    /**
     * Calculate summary statistics from v_hitung_pinjaman data
     */
    private function calculateSummaryFromView($data)
    {
        $total_pinjaman = 0;
        $total_bayar = 0;
        $total_sisa = 0;
        $peminjam_aktif = count($data);
        $peminjam_lunas = 0;
        $peminjam_belum = 0;
        
        foreach ($data as $loan) {
            $total_pinjaman += $loan['pokok_pinjaman'];
            $total_bayar += $loan['jumlah_bayar'];
            $total_sisa += $loan['sisa_tagihan'];
            
            if ($loan['status'] == 'Lunas') {
                $peminjam_lunas++;
            } else {
                $peminjam_belum++;
            }
        }
        
        $completion_rate = $peminjam_aktif > 0 ? ($peminjam_lunas / $peminjam_aktif) * 100 : 0;
        
        return [
            'total_pinjaman' => $total_pinjaman,
            'total_bayar' => $total_bayar,
            'total_sisa' => $total_sisa,
            'peminjam_aktif' => $peminjam_aktif,
            'peminjam_lunas' => $peminjam_lunas,
            'peminjam_belum' => $peminjam_belum,
            'completion_rate' => $completion_rate
        ];
    }

    /**
     * Get comprehensive loan data with proper accounting principles (OLD METHOD - KEEP FOR REFERENCE)
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
        $data = $this->getRekapPinjamanFromView($tgl_dari, $tgl_samp);
        $summary = $this->calculateSummaryFromView($data);
        
        // Format dates
        $tgl_dari_formatted = Carbon::parse($tgl_dari)->format('d F Y');
        $tgl_samp_formatted = Carbon::parse($tgl_samp)->format('d F Y');
        
        // Create statistics array for PDF view
        $statistics = [
            'peminjam_aktif' => $summary['peminjam_aktif'],
            'peminjam_lunas' => $summary['peminjam_lunas'],
            'peminjam_belum' => $summary['peminjam_belum'],
            'completion_rate' => $summary['completion_rate'],
            'overdue_rate' => $summary['peminjam_aktif'] > 0 ? ($summary['peminjam_belum'] / $summary['peminjam_aktif']) * 100 : 0
        ];
        
        // Pass data as recentLoans for PDF view compatibility
        $recentLoans = $data;
        
        $pdf = Pdf::loadView('laporan.pdf.kas_pinjaman', compact(
            'tgl_dari',
            'tgl_samp',
            'tgl_dari_formatted',
            'tgl_samp_formatted',
            'data',
            'summary',
            'statistics',
            'recentLoans'
        ));

        return $pdf->download('laporan_kas_pinjaman_' . $tgl_dari . '_' . $tgl_samp . '.pdf');
    }

}