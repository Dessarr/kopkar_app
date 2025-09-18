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

        // Build query using v_transaksi view with joins
        $query = DB::table('v_transaksi as v')
            ->leftJoin('nama_kas_tbl as nk1', 'v.dari_kas', '=', 'nk1.id')
            ->leftJoin('nama_kas_tbl as nk2', 'v.untuk_kas', '=', 'nk2.id')
            ->leftJoin('jns_akun as ja', 'v.transaksi', '=', 'ja.id')
            ->select(
                'v.id',
                'v.tgl',
                'v.ket as keterangan',
                'v.debet',
                'v.kredit',
                'v.user',
                'v.dari_kas',
                'v.untuk_kas',
                'v.transaksi',
                'v.nama',
                'nk1.nama as dari_kas_nama',
                'nk2.nama as untuk_kas_nama',
                'ja.jns_trans as akun_transaksi'
            )
            ->whereBetween(DB::raw('DATE(v.tgl)'), [$tgl_dari, $tgl_samp])
            ->orderBy('v.tgl', 'asc');

        // Get paginated data
        $dataTransaksi = $query->paginate($perPage);

        // Calculate saldo sebelumnya (before tgl_dari)
        $saldoSebelumnya = $this->calculateSaldoSebelumnya($tgl_dari);

        // Calculate running balance and process data
        $runningBalance = $saldoSebelumnya;
        $processedData = [];
        
        foreach ($dataTransaksi as $transaksi) {
            // Calculate running balance
            $transaksi->saldo = $runningBalance + ($transaksi->debet - $transaksi->kredit);
            $runningBalance = $transaksi->saldo;
            
            // Generate transaction code
            $kodeTransaksi = $this->generateKodeTransaksi($transaksi->transaksi, $transaksi->id);
            
            $processedData[] = (object) [
                'id' => $transaksi->id,
                'tgl' => $transaksi->tgl,
                'keterangan' => $transaksi->keterangan,
                'debet' => $transaksi->debet,
                'kredit' => $transaksi->kredit,
                'user' => $transaksi->user,
                'dari_kas' => $transaksi->dari_kas,
                'untuk_kas' => $transaksi->untuk_kas,
                'transaksi' => $transaksi->transaksi,
                'nama' => $transaksi->nama,
                'dari_kas_nama' => $transaksi->dari_kas_nama,
                'untuk_kas_nama' => $transaksi->untuk_kas_nama,
                'akun_transaksi' => $transaksi->akun_transaksi,
                'saldo' => $transaksi->saldo,
                'kode_transaksi' => $kodeTransaksi
            ];
        }

        // Format periode text
        $periodeText = Carbon::parse($tgl_dari)->format('d/m/Y') . ' - ' . Carbon::parse($tgl_samp)->format('d/m/Y');

        return view('laporan.transaksi_kas', compact(
            'dataTransaksi',
            'processedData',
            'tgl_dari',
            'tgl_samp',
            'periodeText',
            'saldoSebelumnya'
        ));
    }

    public function exportPdf(Request $request)
    {
        // Get filter parameters
        $tgl_dari = $request->input('tgl_dari', date('Y-m-d'));
        $tgl_samp = $request->input('tgl_samp', date('Y-m-d'));

        // Build query using v_transaksi view with joins
        $query = DB::table('v_transaksi as v')
            ->leftJoin('nama_kas_tbl as nk1', 'v.dari_kas', '=', 'nk1.id')
            ->leftJoin('nama_kas_tbl as nk2', 'v.untuk_kas', '=', 'nk2.id')
            ->leftJoin('jns_akun as ja', 'v.transaksi', '=', 'ja.id')
            ->select(
                'v.id',
                'v.tgl',
                'v.ket as keterangan',
                'v.debet',
                'v.kredit',
                'v.user',
                'v.dari_kas',
                'v.untuk_kas',
                'v.transaksi',
                'v.nama',
                'nk1.nama as dari_kas_nama',
                'nk2.nama as untuk_kas_nama',
                'ja.jns_trans as akun_transaksi'
            )
            ->whereBetween(DB::raw('DATE(v.tgl)'), [$tgl_dari, $tgl_samp])
            ->orderBy('v.tgl', 'asc');

        // Get all data for PDF
        $dataTransaksi = $query->get();

        // Calculate saldo sebelumnya (before tgl_dari)
        $saldoSebelumnya = $this->calculateSaldoSebelumnya($tgl_dari);

        // Calculate running balance and process data
        $runningBalance = $saldoSebelumnya;
        $processedData = [];
        
        foreach ($dataTransaksi as $transaksi) {
            // Calculate running balance
            $transaksi->saldo = $runningBalance + ($transaksi->debet - $transaksi->kredit);
            $runningBalance = $transaksi->saldo;
            
            // Generate transaction code
            $kodeTransaksi = $this->generateKodeTransaksi($transaksi->transaksi, $transaksi->id);
            
            $processedData[] = (object) [
                'id' => $transaksi->id,
                'tgl' => $transaksi->tgl,
                'keterangan' => $transaksi->keterangan,
                'debet' => $transaksi->debet,
                'kredit' => $transaksi->kredit,
                'user' => $transaksi->user,
                'dari_kas' => $transaksi->dari_kas,
                'untuk_kas' => $transaksi->untuk_kas,
                'transaksi' => $transaksi->transaksi,
                'nama' => $transaksi->nama,
                'dari_kas_nama' => $transaksi->dari_kas_nama,
                'untuk_kas_nama' => $transaksi->untuk_kas_nama,
                'akun_transaksi' => $transaksi->akun_transaksi,
                'saldo' => $transaksi->saldo,
                'kode_transaksi' => $kodeTransaksi
            ];
        }

        // Format periode text
        $periodeText = Carbon::parse($tgl_dari)->format('d/m/Y') . ' - ' . Carbon::parse($tgl_samp)->format('d/m/Y');

        $pdf = Pdf::loadView('laporan.pdf.transaksi_kas', compact(
            'dataTransaksi',
            'processedData',
            'periodeText',
            'saldoSebelumnya'
        ));

        return $pdf->download('laporan_transaksi_kas_' . date('Ymd') . '.pdf');
    }

    /**
     * Calculate saldo kas before the specified date
     * This implements the accounting principle of running balance
     */
    private function calculateSaldoSebelumnya($tgl_dari)
    {
        $saldoSebelumnya = DB::table('v_transaksi')
            ->selectRaw('SUM(debet) - SUM(kredit) as saldo')
            ->where(DB::raw('DATE(tgl)'), '<', $tgl_dari)
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