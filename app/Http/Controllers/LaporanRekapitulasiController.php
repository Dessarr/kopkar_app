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

} 