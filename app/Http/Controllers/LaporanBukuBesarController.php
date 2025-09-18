<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NamaKasTbl;
use App\Models\transaksi_kas;
use App\Models\jns_akun;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanBukuBesarController extends Controller
{
    public function index(Request $request)
    {
        // Get filter parameters with default values
        $periode = $request->input('periode', date('Y-m'));
        
        // Get all active kas accounts
        $kasList = NamaKasTbl::where('aktif', 'Y')->get();
        
        $processedData = [];
        $totalSaldoKeseluruhan = 0;
        
        // Parse periode
        $tglArr = explode('-', $periode);
        $thn = $tglArr[0];
        $bln = $tglArr[1];
        
        // Process data for each kas account
        foreach ($kasList as $kas) {
            $kasData = $this->processKasData($kas, $thn, $bln);
            if (!empty($kasData['transaksi'])) {
                $processedData[] = $kasData;
                $totalSaldoKeseluruhan += $kasData['saldo_akhir'];
            }
        }
        
        return view('laporan.buku_besar', compact(
            'kasList',
            'processedData',
            'periode',
            'totalSaldoKeseluruhan'
        ));
    }

    /**
     * Process data for a specific kas account
     */
    private function processKasData($kas, $tahun, $bulan)
    {
        $kasId = $kas->id;
        
        // Calculate saldo awal using v_transaksi
        $saldoAwal = $this->calculateSaldoAwal($kasId, $tahun, $bulan);
        
        // Get transactions for the selected period using v_transaksi
        $transaksi = $this->getTransaksiFromView($kasId, $tahun, $bulan);
        
        // Process transactions with running balance
        $processedTransaksi = $this->prosesTransaksiFromView($transaksi, $kasId, $saldoAwal);
        
        // Calculate totals
        $totalDebet = collect($processedTransaksi)->sum('debet');
        $totalKredit = collect($processedTransaksi)->sum('kredit');
        $saldoAkhir = $saldoAwal + ($totalDebet - $totalKredit);
        
        return [
            'kas' => $kas,
            'transaksi' => $processedTransaksi,
            'saldo_awal' => $saldoAwal,
            'total_debet' => $totalDebet,
            'total_kredit' => $totalKredit,
            'saldo_akhir' => $saldoAkhir
        ];
    }

    /**
     * Calculate saldo awal using v_transaksi view
     */
    private function calculateSaldoAwal($kasId, $tahun, $bulan)
    {
        $saldoAwal = DB::table('v_transaksi')
            ->selectRaw('
                SUM(CASE WHEN untuk_kas = ? THEN debet ELSE 0 END) - 
                SUM(CASE WHEN dari_kas = ? THEN kredit ELSE 0 END) as saldo
            ', [$kasId, $kasId])
            ->where(function($query) use ($tahun, $bulan) {
                $query->whereYear('tgl', '<', $tahun)
                      ->orWhere(function($q) use ($tahun, $bulan) {
                          $q->whereYear('tgl', $tahun)
                            ->whereMonth('tgl', '<', $bulan);
                      });
            })
            ->where(function($q) use ($kasId) {
                $q->where('dari_kas', $kasId)
                  ->orWhere('untuk_kas', $kasId);
            })
            ->value('saldo');

        return $saldoAwal ?? 0;
    }

    /**
     * Get transactions from v_transaksi view for specific kas and period
     */
    private function getTransaksiFromView($kasId, $tahun, $bulan)
    {
        return DB::table('v_transaksi as v')
            ->leftJoin('jns_akun as ja', 'v.transaksi', '=', 'ja.id')
            ->leftJoin('nama_kas_tbl as nk_dari', 'v.dari_kas', '=', 'nk_dari.id')
            ->leftJoin('nama_kas_tbl as nk_untuk', 'v.untuk_kas', '=', 'nk_untuk.id')
            ->select(
                'v.tbl',
                'v.id',
                'v.tgl',
                'v.nama',
                'v.debet',
                'v.kredit',
                'v.dari_kas',
                'v.untuk_kas',
                'v.transaksi',
                'v.ket',
                'v.user',
                'ja.jns_trans as jenis_transaksi',
                'nk_dari.nama as dari_kas_nama',
                'nk_untuk.nama as untuk_kas_nama'
            )
            ->where(function($q) use ($kasId) {
                $q->where('v.dari_kas', $kasId)
                  ->orWhere('v.untuk_kas', $kasId);
            })
            ->whereYear('v.tgl', $tahun)
            ->whereMonth('v.tgl', $bulan)
            ->orderBy('v.tgl', 'asc')
            ->orderBy('v.id', 'asc')
            ->get();
    }

    /**
     * Process transactions from v_transaksi with running balance calculation
     */
    private function prosesTransaksiFromView($transaksi, $kasId, $saldoAwal)
    {
        $result = [];
        $runningBalance = $saldoAwal;
        $no = 1;
        
        foreach ($transaksi as $row) {
            $debet = 0;
            $kredit = 0;
            
            // Determine debet/kredit based on transaction direction
            if ($row->untuk_kas == $kasId) {
                // Money coming into this kas account
                $debet = $row->debet;
            }
            if ($row->dari_kas == $kasId) {
                // Money going out of this kas account
                $kredit = $row->kredit;
            }
            
            // Update running balance
            $runningBalance += ($debet - $kredit);
            
            $result[] = [
                'no' => $no++,
                'tanggal' => $row->tgl,
                'jenis_transaksi' => $row->jenis_transaksi ?? 'N/A',
                'keterangan' => $row->ket,
                'nama' => $row->nama,
                'debet' => $debet,
                'kredit' => $kredit,
                'saldo' => $runningBalance,
                'tbl' => $row->tbl,
                'dari_kas_nama' => $row->dari_kas_nama,
                'untuk_kas_nama' => $row->untuk_kas_nama
            ];
        }
        
        return $result;
    }

    public function exportPdf(Request $request)
    {
        // Get filter parameters
        $periode = $request->input('periode', date('Y-m'));
        
        // Get all active kas accounts
        $kasList = NamaKasTbl::where('aktif', 'Y')->get();
        
        $processedData = [];
        $totalSaldoKeseluruhan = 0;
        
        // Parse periode
        $tglArr = explode('-', $periode);
        $thn = $tglArr[0];
        $bln = $tglArr[1];
        
        // Process data for each kas account
        foreach ($kasList as $kas) {
            $kasData = $this->processKasData($kas, $thn, $bln);
            if (!empty($kasData['transaksi'])) {
                $processedData[] = $kasData;
                $totalSaldoKeseluruhan += $kasData['saldo_akhir'];
            }
        }
        
        // Format periode text
        $periodeText = Carbon::createFromFormat('Y-m', $periode)->format('F Y');
        
        $pdf = Pdf::loadView('laporan.pdf.buku_besar', compact(
            'processedData',
            'periode',
            'periodeText',
            'totalSaldoKeseluruhan'
        ));

        return $pdf->download('laporan_buku_besar_' . $periode . '.pdf');
    }

} 