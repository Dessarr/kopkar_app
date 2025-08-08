<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BillingUtamaController extends Controller
{
    public function index(Request $request)
    {
        $bulan = $request->input('bulan', date('m'));
        $tahun = $request->input('tahun', date('Y'));
        $search = $request->input('search');

        $query = DB::table('tbl_trans_sp_bayar_temp as t')
            ->join('tbl_anggota as a', 't.no_ktp', '=', 'a.no_ktp')
            ->select('t.*', 'a.nama');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('a.nama', 'like', '%'.$search.'%')
                  ->orWhere('t.no_ktp', 'like', '%'.$search.'%');
            });
        }

        if ($bulan && $tahun) {
            $query->whereMonth('t.tgl_transaksi', $bulan)
                  ->whereYear('t.tgl_transaksi', $tahun);
        }

        $data = $query->paginate(10);

        $bulanList = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        ];
        $tahunList = range(date('Y') - 5, date('Y') + 2);

        return view('billing.utama', compact('data', 'bulan', 'tahun', 'bulanList', 'tahunList'));
    }
}