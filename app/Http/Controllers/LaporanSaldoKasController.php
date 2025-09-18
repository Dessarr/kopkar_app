<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NamaKasTbl;
use App\Models\transaksi_kas;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class LaporanSaldoKasController extends Controller
{
    public function index(Request $request)
    {
        $periode = $request->input('periode', date('Y-m'));
        
        // Get detailed cash balance data using v_transaksi
        $data = $this->getSaldoKasFromView($periode);
        
        return view('laporan.saldo_kas', [
            'periode' => $periode,
            'data' => $data['rows'],
            'saldo_sblm' => $data['saldo_sblm'],
            'total' => $data['total']
        ]);
    }

    private function getSaldoKasFromView($periode)
    {
        $tglArr = explode('-', $periode);
        $thn = $tglArr[0];
        $bln = $tglArr[1];
        
        // Get specific cash accounts only - exclude Bank Mandiri
        $kasList = DB::table('nama_kas_tbl')
            ->where('aktif', 'Y')
            ->whereIn('nama', ['Kas Tunai', 'Kas Besar', 'Bank BCA', 'Bank BNI'])
            ->select(DB::raw('MIN(id) as id'), 'nama')
            ->groupBy('nama')
            ->orderBy('nama')
            ->get();
        
        // Calculate previous period balance using v_transaksi
        $saldo_sblm = DB::table('v_transaksi')
            ->where(function($q) use ($thn, $bln) {
                $q->whereYear('tgl', '<', $thn)
                  ->orWhere(function($q2) use ($thn, $bln) {
                      $q2->whereYear('tgl', $thn)
                         ->whereMonth('tgl', '<', $bln);
                  });
            })
            ->select(DB::raw('SUM(debet) as jum_debet'),
                     DB::raw('SUM(kredit) as jum_kredit'))
            ->first();
        $saldo_sblm_val = ($saldo_sblm->jum_debet ?? 0) - ($saldo_sblm->jum_kredit ?? 0);
        
        $rows = [];
        $total_saldo = 0;
        $no = 1;
        
        foreach ($kasList as $kas) {
            // Calculate debet (cash in) for current period using v_transaksi
            $debet = DB::table('v_transaksi')
                ->where('untuk_kas', $kas->id)
                ->whereYear('tgl', $thn)
                ->whereMonth('tgl', $bln)
                ->sum('debet');
                
            // Calculate credit (cash out) for current period using v_transaksi
            $kredit = DB::table('v_transaksi')
                ->where('dari_kas', $kas->id)
                ->whereYear('tgl', $thn)
                ->whereMonth('tgl', $bln)
                ->sum('kredit');
                
            // Calculate net cash flow for this account
            $saldo = $debet - $kredit;
            
            $rows[] = [
                'no' => $no++,
                'id' => $kas->id,
                'nama' => $kas->nama,
                'debet' => $debet,
                'kredit' => $kredit,
                'saldo' => $saldo,
                'status' => $saldo >= 0 ? 'Surplus' : 'Defisit'
            ];
            $total_saldo += $saldo;
        }
        
        return [
            'rows' => $rows,
            'saldo_sblm' => $saldo_sblm_val,
            'total' => $total_saldo
        ];
    }




    public function exportPdf(Request $request)
    {
        $periode = $request->input('periode', date('Y-m'));
        
        // Get detailed data using v_transaksi
        $data = $this->getSaldoKasFromView($periode);
        
        $pdf = Pdf::loadView('laporan.pdf.saldo_kas', [
            'periode' => $periode,
            'data' => $data['rows'],
            'saldo_sblm' => $data['saldo_sblm'],
            'total' => $data['total']
        ]);
        
        return $pdf->download('laporan_saldo_kas_'.$periode.'.pdf');
    }

} 