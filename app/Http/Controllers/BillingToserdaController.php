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
        // Tombol proses per-item dihapus dalam revisi
        return redirect()->back()->with('error', 'Aksi ini tidak tersedia. Gunakan Proses All ke Billing Utama.');
    }

    // Halaman processed dihapus pada revisi

    // Pembatalan tidak diperlukan lagi

    // Proses semua data Toserda bulan/tahun terpilih ke Billing Utama (tbl_trans_sp_bayar_temp)
    public function processAllToMain(Request $request)
    {
        $bulan = $request->input('bulan', date('m'));
        $tahun = $request->input('tahun', date('Y'));
        try {
            DB::beginTransaction();

            // Ambil total tagihan per anggota (sudah berstatus_billing=Y & belum dibayar)
            $rows = DB::table('tbl_trans_toserda')
                ->select('no_ktp', DB::raw('SUM(jumlah) as total'))
                ->where('status_billing', 'Y')
                ->whereNull('tgl_bayar')
                ->whereMonth('tgl_transaksi', $bulan)
                ->whereYear('tgl_transaksi', $tahun)
                ->groupBy('no_ktp')
                ->get();

            foreach ($rows as $r) {
                // Get anggota_id from tbl_anggota
                $anggota = DB::table('tbl_anggota')
                    ->where('no_ktp', $r->no_ktp)
                    ->first();
                
                $anggotaId = $anggota ? $anggota->id : null;
                
                DB::table('tbl_trans_sp_bayar_temp')->updateOrInsert(
                    [
                        'tgl_transaksi' => \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->toDateString(),
                        'no_ktp' => $r->no_ktp,
                    ],
                    [
                        'anggota_id' => $anggotaId,
                        'jumlah' => DB::raw('COALESCE(jumlah,0)'),
                        'keterangan' => 'Billing Toserda ' . $bulan . '-' . $tahun,
                        'tagihan_simpanan_wajib' => DB::raw('COALESCE(tagihan_simpanan_wajib,0)'),
                        'tagihan_simpanan_sukarela' => DB::raw('COALESCE(tagihan_simpanan_sukarela,0)'),
                        'tagihan_simpanan_khusus_2' => DB::raw('COALESCE(tagihan_simpanan_khusus_2,0)'),
                        'tagihan_pinjaman' => DB::raw('COALESCE(tagihan_pinjaman,0)'),
                        'tagihan_pinjaman_jasa' => DB::raw('COALESCE(tagihan_pinjaman_jasa,0)'),
                        'tagihan_toserda' => $r->total ?? 0,
                        'total_tagihan_simpanan' => DB::raw('COALESCE(total_tagihan_simpanan,0)'),
                        'selisih' => DB::raw('COALESCE(selisih,0)'),
                        'saldo_simpanan_sukarela' => DB::raw('COALESCE(saldo_simpanan_sukarela,0)'),
                        'saldo_akhir_simpanan_sukarela' => DB::raw('COALESCE(saldo_akhir_simpanan_sukarela,0)'),
                    ]
                );
            }

            DB::commit();
            return redirect()->route('billing.utama')->with('success', 'Berhasil memproses Billing Toserda ke Billing Utama.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}