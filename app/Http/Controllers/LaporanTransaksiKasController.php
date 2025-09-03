<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\View_Transaksi;
use App\Models\transaksi_kas;
use App\Models\NamaKasTbl;
use App\Models\jns_akun;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanTransaksiKasController extends Controller
{
    public function index(Request $request)
    {
        // Get filter parameters with preset options
        $tgl_dari = $request->input('tgl_dari', date('Y-m-d'));
        $tgl_samp = $request->input('tgl_samp', date('Y-m-d'));
        $perPage = 20; // 20 data per page as specified

        // Build query using tbl_trans_kas directly with joins
        $query = DB::table('tbl_trans_kas as t')
            ->leftJoin('nama_kas_tbl as nk1', 't.dari_kas_id', '=', 'nk1.id')
            ->leftJoin('nama_kas_tbl as nk2', 't.untuk_kas_id', '=', 'nk2.id')
            ->leftJoin('jns_akun as ja', 't.jns_trans', '=', 'ja.id')
            ->select(
                't.id',
                't.tgl_catat as tgl',
                't.keterangan',
                DB::raw('CASE WHEN t.dk = "D" THEN t.jumlah ELSE 0 END as debet'),
                DB::raw('CASE WHEN t.dk = "K" THEN t.jumlah ELSE 0 END as kredit'),
                't.user_name as user',
                't.dari_kas_id as dari_kas',
                't.untuk_kas_id as untuk_kas',
                't.jns_trans',
                DB::raw('CASE WHEN t.dk = "D" THEN "48" WHEN t.dk = "K" THEN "7" END as transaksi'),
                'nk1.nama as dari_kas_nama',
                'nk2.nama as untuk_kas_nama',
                'ja.jns_trans as akun_transaksi'
            )
            ->whereBetween(DB::raw('DATE(t.tgl_catat)'), [$tgl_dari, $tgl_samp])
            ->whereIn('t.dk', ['D', 'K'])
            ->orderBy('t.tgl_catat', 'asc');

        // Get paginated data
        $dataTransaksi = $query->paginate($perPage);

        // Calculate saldo sebelumnya (before tgl_dari)
        $saldoSebelumnya = $this->calculateSaldoSebelumnya($tgl_dari);

        // Calculate running balance for each transaction
        $runningBalance = $saldoSebelumnya;
        foreach ($dataTransaksi as $transaksi) {
            $transaksi->saldo = $runningBalance + ($transaksi->debet - $transaksi->kredit);
            $runningBalance = $transaksi->saldo;
        }

        // Calculate totals
        $totalDebet = $dataTransaksi->sum('debet');
        $totalKredit = $dataTransaksi->sum('kredit');
        $saldoAkhir = $saldoSebelumnya + ($totalDebet - $totalKredit);

        // Format periode text
        $periodeText = Carbon::parse($tgl_dari)->format('d/m/Y') . ' - ' . Carbon::parse($tgl_samp)->format('d/m/Y');

        return view('laporan.transaksi_kas', compact(
            'dataTransaksi',
            'tgl_dari',
            'tgl_samp',
            'periodeText',
            'saldoSebelumnya',
            'totalDebet',
            'totalKredit',
            'saldoAkhir'
        ));
    }

    public function exportPdf(Request $request)
    {
        // Get filter parameters
        $tgl_dari = $request->input('tgl_dari', date('Y-m-d'));
        $tgl_samp = $request->input('tgl_samp', date('Y-m-d'));

        // Build query using tbl_trans_kas directly with joins
        $query = DB::table('tbl_trans_kas as t')
            ->leftJoin('nama_kas_tbl as nk1', 't.dari_kas_id', '=', 'nk1.id')
            ->leftJoin('nama_kas_tbl as nk2', 't.untuk_kas_id', '=', 'nk2.id')
            ->leftJoin('jns_akun as ja', 't.jns_trans', '=', 'ja.id')
            ->select(
                't.id',
                't.tgl_catat as tgl',
                't.keterangan',
                DB::raw('CASE WHEN t.dk = "D" THEN t.jumlah ELSE 0 END as debet'),
                DB::raw('CASE WHEN t.dk = "K" THEN t.jumlah ELSE 0 END as kredit'),
                't.user_name as user',
                't.dari_kas_id as dari_kas',
                't.untuk_kas_id as untuk_kas',
                't.jns_trans',
                DB::raw('CASE WHEN t.dk = "D" THEN "48" WHEN t.dk = "K" THEN "7" END as transaksi'),
                'nk1.nama as dari_kas_nama',
                'nk2.nama as untuk_kas_nama',
                'ja.jns_trans as akun_transaksi'
            )
            ->whereBetween(DB::raw('DATE(t.tgl_catat)'), [$tgl_dari, $tgl_samp])
            ->whereIn('t.dk', ['D', 'K'])
            ->orderBy('t.tgl_catat', 'asc');

        // Get all data for PDF
        $dataTransaksi = $query->get();

        // Calculate saldo sebelumnya (before tgl_dari)
        $saldoSebelumnya = $this->calculateSaldoSebelumnya($tgl_dari);

        // Calculate running balance for each transaction
        $runningBalance = $saldoSebelumnya;
        foreach ($dataTransaksi as $transaksi) {
            $transaksi->saldo = $runningBalance + ($transaksi->debet - $transaksi->kredit);
            $runningBalance = $transaksi->saldo;
        }

        // Calculate totals
        $totalDebet = $dataTransaksi->sum('debet');
        $totalKredit = $dataTransaksi->sum('kredit');
        $saldoAkhir = $saldoSebelumnya + ($totalDebet - $totalKredit);

        // Format periode text
        $periodeText = Carbon::parse($tgl_dari)->format('d/m/Y') . ' - ' . Carbon::parse($tgl_samp)->format('d/m/Y');

        $pdf = Pdf::loadView('laporan.pdf.transaksi_kas', compact(
            'dataTransaksi',
            'periodeText',
            'saldoSebelumnya',
            'totalDebet',
            'totalKredit',
            'saldoAkhir'
        ));

        return $pdf->download('laporan_transaksi_kas_' . date('Ymd') . '.pdf');
    }

    /**
     * Calculate saldo kas before the specified date
     * This implements the accounting principle of running balance
     */
    private function calculateSaldoSebelumnya($tgl_dari)
    {
        $saldoSebelumnya = DB::table('tbl_trans_kas')
            ->selectRaw('SUM(CASE WHEN dk = "D" THEN jumlah ELSE 0 END) - SUM(CASE WHEN dk = "K" THEN jumlah ELSE 0 END) as saldo')
            ->where(DB::raw('DATE(tgl_catat)'), '<', $tgl_dari)
            ->whereIn('dk', ['D', 'K'])
            ->value('saldo');

        return $saldoSebelumnya ?? 0;
    }

    /**
     * Generate transaction code based on transaction type and ID
     */
    private function generateKodeTransaksi($transaksi, $id)
    {
        $prefixes = [
            '48' => 'TPJ', // Pemasukan
            '7' => 'TBY',  // Pengeluaran
            'transfer' => 'TRD',
            'kas_keluar' => 'TRK',
            'kas_fisik' => 'TRF',
            'kas_deposit' => 'TKD',
            'kas_kredit' => 'TKK'
        ];

        $prefix = $prefixes[$transaksi] ?? 'TRX';
        return $prefix . str_pad($id, 5, '0', STR_PAD_LEFT);
    }
} 