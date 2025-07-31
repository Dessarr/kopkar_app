<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BillingToserdaController extends Controller
{
    public function index(Request $request)
    {
        // Update records with null tgl_trans
        DB::table('tbl_trans_toserda')
            ->whereNull('tgl_trans')
            ->update(['tgl_trans' => now()]);
            
        $bulan = $request->get('bulan', date('m'));
        $tahun = $request->get('tahun', date('Y'));
        $search = $request->get('search');

        $query = DB::table('tbl_trans_toserda as t')
            ->join('tbl_anggota as a', 't.no_ktp', '=', 'a.no_ktp')
            ->select('t.*', 'a.nama')
            ->where('t.status_billing', 'Y')
            ->whereNull('t.tgl_bayar')
            ->whereMonth('t.tgl_transaksi', $bulan)
            ->whereYear('t.tgl_transaksi', $tahun);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('a.nama', 'like', '%'.$search.'%')
                  ->orWhere('t.no_ktp', 'like', '%'.$search.'%');
            });
        }

        $dataBilling = $query->paginate(10);
        $tahunList = range(date('Y') - 2, date('Y'));
        $bulanList = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        ];

        return view('billing.toserda', compact('dataBilling', 'tahunList', 'bulanList', 'bulan', 'tahun'));
    }

    public function proses($id)
    {
        try {
            DB::beginTransaction();

            $billing = DB::table('tbl_trans_toserda as t')
                ->join('tbl_anggota as a', 't.no_ktp', '=', 'a.no_ktp')
                ->select('t.*', 'a.nama')
                ->where('t.id', $id)
                ->first();

            if (!$billing) {
                throw new \Exception('Data billing tidak ditemukan');
            }

            // Update status billing
            DB::table('tbl_trans_toserda')
                ->where('id', $id)
                ->update([
                    'status_billing' => 'Y',
                    'tgl_bayar' => now()
                ]);

            DB::commit();
            return redirect()->back()->with('success', 'Billing berhasil diproses');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function processed(Request $request)
    {
        // Update records with null tgl_trans
        DB::table('tbl_trans_toserda')
            ->whereNull('tgl_trans')
            ->update(['tgl_trans' => now()]);
            
        $bulan = $request->get('bulan', date('m'));
        $tahun = $request->get('tahun', date('Y'));
        $search = $request->get('search');

        $query = DB::table('tbl_trans_toserda as t')
            ->join('tbl_anggota as a', 't.no_ktp', '=', 'a.no_ktp')
            ->select('t.*', 'a.nama')
            ->where('t.status_billing', 'Y')
            ->whereNotNull('t.tgl_bayar')
            ->whereMonth('t.tgl_transaksi', $bulan)
            ->whereYear('t.tgl_transaksi', $tahun);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('a.nama', 'like', '%'.$search.'%')
                  ->orWhere('t.no_ktp', 'like', '%'.$search.'%');
            });
        }

        $dataBillingProcessed = $query->paginate(10);
        $tahunList = range(date('Y') - 2, date('Y'));
        $bulanList = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        ];

        return view('billing.toserda_processed', compact('dataBillingProcessed', 'tahunList', 'bulanList', 'bulan', 'tahun'));
    }

    public function cancel($id)
    {
        try {
            DB::beginTransaction();

            $billing = DB::table('tbl_trans_toserda')
                ->where('id', $id)
                ->first();

            if (!$billing) {
                throw new \Exception('Data billing tidak ditemukan');
            }

            // Update status billing kembali ke belum dibayar
            DB::table('tbl_trans_toserda')
                ->where('id', $id)
                ->update([
                    'tgl_bayar' => null
                ]);

            DB::commit();
            return redirect()->back()->with('success', 'Pembayaran billing berhasil dibatalkan');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}