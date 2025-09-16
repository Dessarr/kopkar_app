<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BillingPeriodeController extends Controller
{
    /**
     * Get summary data for period table
     */
    public function getPeriodSummary($bulan, $tahun)
    {
        try {
            $periode = $tahun . '-' . str_pad($bulan, 2, '0', STR_PAD_LEFT);
            
            // Debug: Log what we're querying
            Log::info("Querying period summary for: {$periode} (bulan: {$bulan}, tahun: {$tahun})");
            
            // Query Total Anggota - Ambil dari tbl_trans_sp untuk periode tertentu (menghindari duplikasi)
            $totalAnggota = DB::table('tbl_trans_sp')
                ->where('dk', 'D')
                ->whereIn('jenis_id', [32, 40, 41]) // Simpanan Wajib, Pokok, Sukarela
                ->whereMonth('tgl_transaksi', $bulan)
                ->whereYear('tgl_transaksi', $tahun)
                ->distinct('no_ktp')
                ->count('no_ktp');

            // Query Simpanan Pokok - Ambil dari tbl_trans_sp (data resmi)
            $simpananPokok = DB::table('tbl_trans_sp')
                ->where('jenis_id', 40) // Simpanan Pokok
                ->where('dk', 'D')
                ->whereMonth('tgl_transaksi', $bulan)
                ->whereYear('tgl_transaksi', $tahun)
                ->sum('jumlah') ?? 0;

            // Query Simpanan Sukarela - Ambil dari tbl_trans_sp (data resmi)
            $simpananSukarela = DB::table('tbl_trans_sp')
                ->where('jenis_id', 32) // Simpanan Sukarela
                ->where('dk', 'D')
                ->whereMonth('tgl_transaksi', $bulan)
                ->whereYear('tgl_transaksi', $tahun)
                ->sum('jumlah') ?? 0;

            // Query Simpanan Wajib - Ambil dari tbl_trans_sp (data resmi)
            $simpananWajib = DB::table('tbl_trans_sp')
                ->where('jenis_id', 41) // Simpanan Wajib
                ->where('dk', 'D')
                ->whereMonth('tgl_transaksi', $bulan)
                ->whereYear('tgl_transaksi', $tahun)
                ->sum('jumlah') ?? 0;

            // Debug: Log the results
            Log::info("Period summary results:", [
                'periode' => $periode,
                'bulan' => $bulan,
                'tahun' => $tahun,
                'total_anggota' => $totalAnggota,
                'simpanan_pokok' => $simpananPokok,
                'simpanan_sukarela' => $simpananSukarela,
                'simpanan_wajib' => $simpananWajib,
                'query_details' => [
                    'total_anggota_query' => 'tbl_anggota WHERE aktif = Y',
                    'simpanan_pokok_query' => 'tbl_anggota WHERE aktif = Y (simpanan_wajib)',
                    'simpanan_sukarela_query' => "tbl_trans_sp_bayar_temp WHERE MONTH(tgl_transaksi) = {$bulan} AND YEAR(tgl_transaksi) = {$tahun}",
                    'simpanan_wajib_query' => "tbl_trans_sp_bayar_temp WHERE MONTH(tgl_transaksi) = {$bulan} AND YEAR(tgl_transaksi) = {$tahun}"
                ],
                'raw_values' => [
                    'simpanan_sukarela_raw' => DB::table('tbl_trans_sp_bayar_temp')
                        ->whereMonth('tgl_transaksi', $bulan)
                        ->whereYear('tgl_transaksi', $tahun)
                        ->get(['no_ktp', 'tagihan_simpanan_sukarela'])->toArray(),
                    'simpanan_wajib_raw' => DB::table('tbl_trans_sp_bayar_temp')
                        ->whereMonth('tgl_transaksi', $bulan)
                        ->whereYear('tgl_transaksi', $tahun)
                        ->get(['no_ktp', 'tagihan_simpanan_wajib'])->toArray()
                ]
            ]);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'periode' => $periode,
                    'total_anggota' => $totalAnggota,
                    'simpanan_pokok' => $simpananPokok,
                    'simpanan_sukarela' => $simpananSukarela,
                    'simpanan_wajib' => $simpananWajib
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get period summary for AJAX request
     */
    public function transLalu($periode)
    {
        try {
            $periodeArr = explode('-', $periode);
            if (count($periodeArr) !== 2) {
                throw new \Exception('Format periode tidak valid');
            }

            $tahun = $periodeArr[0];
            $bulan = $periodeArr[1];

            return $this->getPeriodSummary($bulan, $tahun);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Debug method to check what data exists in database
     */
    public function debugPeriodData($bulan, $tahun)
    {
        try {
            $periode = $tahun . '-' . str_pad($bulan, 2, '0', STR_PAD_LEFT);
            
            // Check tbl_trans_sp_bayar_temp
            $billingData = DB::table('tbl_trans_sp_bayar_temp')
                ->whereMonth('tgl_transaksi', $bulan)
                ->whereYear('tgl_transaksi', $tahun)
                ->get();

            // Check tbl_anggota
            $anggotaData = DB::table('tbl_anggota')
                ->select('no_ktp', 'nama', 'simpanan_wajib', 'simpanan_sukarela', 'simpanan_khusus_2', 'aktif')
                ->where('aktif', 'Y')
                ->get();

            // Check if tables exist and have data
            $billingCount = DB::table('tbl_trans_sp_bayar_temp')->count();
            $anggotaCount = DB::table('tbl_anggota')->count();

            return response()->json([
                'status' => 'success',
                'debug_info' => [
                    'periode' => $periode,
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                    'billing_table_total_records' => $billingCount,
                    'anggota_table_total_records' => $anggotaCount,
                    'anggota_aktif_count' => DB::table('tbl_anggota')->where('aktif', 'Y')->count(),
                    'billing_data_for_period' => $billingData->count(),
                    'anggota_data_for_period' => $anggotaData->count(),
                    'sample_billing_data' => $billingData->take(3)->toArray(),
                    'sample_anggota_data' => $anggotaData->take(3)->toArray(),
                    'query_explanation' => [
                        'total_anggota' => 'Selalu dari tbl_anggota WHERE aktif = Y (konsisten)',
                        'simpanan_pokok' => 'Selalu dari tbl_anggota WHERE aktif = Y (konsisten)',
                        'simpanan_sukarela' => 'Dari tbl_trans_sp_bayar_temp sesuai periode',
                        'simpanan_wajib' => 'Dari tbl_trans_sp_bayar_temp sesuai periode'
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Debug error: ' . $e->getMessage()
            ], 500);
        }
    }
}
