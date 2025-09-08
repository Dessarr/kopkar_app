<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\TblPinjamanH;
use App\Models\TblPinjamanD;

class BillingPinjamanController extends Controller
{
    /**
     * Tampilkan halaman billing pinjaman
     */
    public function index(Request $request)
    {
        $bulan = $request->get('bulan', date('m'));
        $tahun = $request->get('tahun', date('Y'));
        $search = $request->get('search');

        $query = DB::table('tbl_trans_tagihan as t')
            ->join('tbl_anggota as a', 't.no_ktp', '=', 'a.no_ktp')
            ->select('t.*', 'a.nama')
            ->where('t.jenis_id', 999) // ID untuk jenis Pinjaman
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

        return view('billing.pinjaman', compact('dataBilling', 'tahunList', 'bulanList', 'bulan', 'tahun'));
    }

    /**
     * Generate billing pinjaman untuk bulan tertentu
     */
    public function generateMonthlyBilling($bulan, $tahun)
    {
        try {
            DB::beginTransaction();
            
            // Hapus billing pinjaman bulan sebelumnya untuk menghindari duplikasi
            DB::table('tbl_trans_tagihan')
                ->where('jenis_id', 999) // ID untuk jenis Pinjaman
                ->whereMonth('tgl_transaksi', $bulan)
                ->whereYear('tgl_transaksi', $tahun)
                ->delete();
            
            // Ambil semua jadwal angsuran untuk bulan tertentu
            // TIDAK ADA FILTER lunas = 'Belum' agar semua jadwal tetap di-generate
            $jadwalAngsuran = DB::table('tempo_pinjaman as t')
                ->join('tbl_pinjaman_h as h', 't.pinjam_id', '=', 'h.id')
                ->select(
                    't.pinjam_id',
                    't.no_ktp',
                    't.tempo',
                    'h.jumlah',
                    'h.lama_angsuran',
                    'h.bunga_rp',
                    'h.biaya_adm',
                    'h.lunas'
                )
                ->whereMonth('t.tempo', $bulan)
                ->whereYear('t.tempo', $tahun)
                ->get();
            
            $billingData = [];
            
            foreach ($jadwalAngsuran as $jadwal) {
                // Hitung angsuran per bulan
                $angsuranPokok = $jadwal->jumlah / $jadwal->lama_angsuran;
                $angsuranBunga = $jadwal->bunga_rp / $jadwal->lama_angsuran;
                $totalAngsuran = $angsuranPokok + $angsuranBunga;
                
                // Generate tagihan untuk semua jadwal, terlepas dari status lunas
                $billingData[] = [
                    'tgl_transaksi' => $jadwal->tempo,
                    'no_ktp' => $jadwal->no_ktp,
                    'anggota_id' => null, // Akan diisi nanti
                    'jenis_id' => 999, // ID untuk jenis Pinjaman
                    'jumlah' => $totalAngsuran,
                    'keterangan' => 'Tagihan Angsuran Pinjaman - Jatuh Tempo: ' . $jadwal->tempo,
                    'akun' => 'Tagihan',
                    'dk' => 'K',
                    'kas_id' => 1,
                    'user_name' => 'admin'
                ];
            }
            
            // Insert billing data
            if (!empty($billingData)) {
                foreach (array_chunk($billingData, 100) as $chunk) {
                    DB::table('tbl_trans_tagihan')->insert($chunk);
                }
            }
            
            DB::commit();
            
            Log::info('Billing pinjaman berhasil di-generate', [
                'bulan' => $bulan,
                'tahun' => $tahun,
                'jumlah_tagihan' => count($billingData)
            ]);
            
            return [
                'status' => 'success',
                'message' => 'Billing pinjaman berhasil di-generate untuk ' . count($billingData) . ' tagihan.'
            ];
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error generating billing pinjaman: ' . $e->getMessage());
            
            return [
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat generate billing pinjaman: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Proses semua billing pinjaman ke Billing Utama
     */
    public function processAllToMain(Request $request)
    {
        $bulan = $request->input('bulan', date('m'));
        $tahun = $request->input('tahun', date('Y'));
        
        try {
            DB::beginTransaction();
            
            // Ambil semua tagihan pinjaman untuk bulan tertentu
            $tagihanPinjaman = DB::table('tbl_trans_tagihan')
                ->select('no_ktp', DB::raw('SUM(jumlah) as total'))
                ->where('jenis_id', 999) // ID untuk jenis Pinjaman
                ->whereMonth('tgl_transaksi', $bulan)
                ->whereYear('tgl_transaksi', $tahun)
                ->groupBy('no_ktp')
                ->get();
            
            foreach ($tagihanPinjaman as $tagihan) {
                // Get anggota_id from tbl_anggota
                $anggota = DB::table('tbl_anggota')
                    ->where('no_ktp', $tagihan->no_ktp)
                    ->first();
                
                $anggotaId = $anggota ? $anggota->id : null;
                
                // Upsert ke tbl_trans_sp_bayar_temp
                DB::table('tbl_trans_sp_bayar_temp')->updateOrInsert(
                    [
                        'tgl_transaksi' => Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->toDateString(),
                        'no_ktp' => $tagihan->no_ktp,
                    ],
                    [
                        'anggota_id' => $anggotaId,
                        'jumlah' => DB::raw('COALESCE(jumlah,0)'),
                        'keterangan' => 'Billing Pinjaman ' . $bulan . '-' . $tahun,
                        'tagihan_simpanan_wajib' => DB::raw('COALESCE(tagihan_simpanan_wajib,0)'),
                        'tagihan_simpanan_sukarela' => DB::raw('COALESCE(tagihan_simpanan_sukarela,0)'),
                        'tagihan_simpanan_khusus_2' => DB::raw('COALESCE(tagihan_simpanan_khusus_2,0)'),
                        'tagihan_pinjaman' => $tagihan->total ?? 0,
                        'tagihan_pinjaman_jasa' => DB::raw('COALESCE(tagihan_pinjaman_jasa,0)'),
                        'tagihan_toserda' => DB::raw('COALESCE(tagihan_toserda,0)'),
                        'total_tagihan_simpanan' => DB::raw('COALESCE(total_tagihan_simpanan,0)'),
                        'selisih' => DB::raw('COALESCE(selisih,0)'),
                        'saldo_simpanan_sukarela' => DB::raw('COALESCE(saldo_simpanan_sukarela,0)'),
                        'saldo_akhir_simpanan_sukarela' => DB::raw('COALESCE(saldo_akhir_simpanan_sukarela,0)'),
                    ]
                );
            }
            
            // Hapus billing pinjaman bulan/tahun ini setelah dipindahkan
            DB::table('tbl_trans_tagihan')
                ->where('jenis_id', 999) // ID untuk jenis Pinjaman
                ->whereMonth('tgl_transaksi', $bulan)
                ->whereYear('tgl_transaksi', $tahun)
                ->delete();
            
            DB::commit();
            
            return redirect()->route('billing.utama')->with('success', 'Berhasil memproses semua billing pinjaman ke Billing Utama.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processAllToMain pinjaman: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}