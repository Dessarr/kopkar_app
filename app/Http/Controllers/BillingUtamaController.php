<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class BillingUtamaController extends Controller
{
    public function index(Request $request)
    {
        $bulan = $request->input('bulan', date('m'));
        $tahun = $request->input('tahun', date('Y'));
        $search = $request->input('search');

        // Generate billing pinjaman otomatis untuk bulan yang dipilih
        $this->generateBillingPinjamanOtomatis($bulan, $tahun);

        $query = DB::table('tbl_trans_sp_bayar_temp as t')
            ->join('tbl_anggota as a', 't.no_ktp', '=', 'a.no_ktp')
            ->leftJoin('billing_upload_temp as u', function($join) use ($bulan, $tahun) {
                $join->on('t.no_ktp', '=', 'u.no_ktp')
                     ->where('u.bulan', $bulan)
                     ->where('u.tahun', $tahun);
            })
            ->select(
                't.no_ktp',
                't.tgl_transaksi',
                't.tagihan_simpanan_wajib',
                't.tagihan_simpanan_sukarela',
                't.tagihan_simpanan_khusus_2',
                't.tagihan_simpanan_pokok',
                't.tagihan_pinjaman',
                't.tagihan_toserda',
                't.jumlah',
                't.keterangan',
                'a.nama',
                DB::raw('(COALESCE(t.tagihan_simpanan_wajib, 0) + COALESCE(t.tagihan_simpanan_sukarela, 0) + COALESCE(t.tagihan_simpanan_khusus_2, 0) + COALESCE(t.tagihan_simpanan_pokok, 0) + COALESCE(t.tagihan_pinjaman, 0) + COALESCE(t.tagihan_toserda, 0)) as total_tagihan'),
                DB::raw('COALESCE(SUM(u.jumlah), 0) as tagihan_upload'),
                DB::raw('(COALESCE(SUM(u.jumlah), 0) - (COALESCE(t.tagihan_simpanan_wajib, 0) + COALESCE(t.tagihan_simpanan_sukarela, 0) + COALESCE(t.tagihan_simpanan_khusus_2, 0) + COALESCE(t.tagihan_simpanan_pokok, 0) + COALESCE(t.tagihan_pinjaman, 0) + COALESCE(t.tagihan_toserda, 0))) as selisih_calculated')
            )
            // HAPUS FILTER status_lunas agar semua data tetap ditampilkan
            ->groupBy('t.no_ktp', 't.tgl_transaksi', 't.tagihan_simpanan_wajib', 't.tagihan_simpanan_sukarela', 't.tagihan_simpanan_khusus_2', 't.tagihan_simpanan_pokok', 't.tagihan_pinjaman', 't.tagihan_toserda', 't.jumlah', 't.keterangan', 'a.nama', 'u.bulan', 'u.tahun');

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

        // Get period summary data
        $periodData = $this->getPeriodSummaryData($bulan, $tahun);

        $bulanList = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        ];
        $tahunList = range(date('Y') - 5, date('Y') + 2);

        return view('billing.utama', compact('data', 'bulan', 'tahun', 'bulanList', 'tahunList', 'periodData'));
    }

    /**
     * Proceed billing data - Process all billing data to main database
     */
    public function proceed(Request $request)
    {
        try {
            $bulan = $request->input('bulan');
            $tahun = $request->input('tahun');

            if (!$bulan || !$tahun) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Bulan dan tahun harus diisi'
                ]);
            }

            DB::beginTransaction();

            // 1. Process simpanan data
            $this->processSimpananData($bulan, $tahun);

            // 2. Process pinjaman data
            $this->processPinjamanData($bulan, $tahun);

            // 3. Process toserda data
            $this->processToserdaData($bulan, $tahun);

                    // 4. Clean up temporary data
            $this->cleanupTemporaryData($bulan, $tahun);

            DB::commit();

            Log::info('Billing proceed completed', [
                'bulan' => $bulan,
                'tahun' => $tahun,
                'user' => Auth::user()->name ?? 'admin'
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Data billing berhasil diproses untuk periode ' . $bulan . '-' . $tahun . '. Total ' . $this->getProcessedCount($bulan, $tahun) . ' transaksi telah diproses.'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error in billing proceed: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat memproses data: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Process simpanan data to tbl_trans_sp
     */
    private function processSimpananData($bulan, $tahun)
    {
        // Process simpanan wajib
        DB::table('tbl_trans_sp')->insertUsing([
            'tgl_transaksi', 'no_ktp', 'anggota_id', 'jenis_id', 'jumlah', 'keterangan', 'akun', 'dk', 'kas_id'
        ], function ($query) use ($bulan, $tahun) {
            $query->select(
                DB::raw('NOW() as tgl_transaksi'), 'a.no_ktp', 'a.anggota_id', 
                DB::raw("'41' as jenis_id"), 'a.tagihan_simpanan_wajib', 
                DB::raw("'Setoran Simpanan Wajib - " . $bulan . "-" . $tahun . "' as keterangan"), 
                DB::raw("'Setoran' as akun"), DB::raw("'D' as dk"), DB::raw("'4' as kas_id")
            )
            ->from('tbl_trans_sp_bayar_temp as a')
            ->whereRaw('YEAR(a.tgl_transaksi) = ? AND MONTH(a.tgl_transaksi) = ?', [$tahun, $bulan])
            ->where('a.tagihan_simpanan_wajib', '<>', 0);
        });

        // Process simpanan pokok
        DB::table('tbl_trans_sp')->insertUsing([
            'tgl_transaksi', 'no_ktp', 'anggota_id', 'jenis_id', 'jumlah', 'keterangan', 'akun', 'dk', 'kas_id'
        ], function ($query) use ($bulan, $tahun) {
            $query->select(
                DB::raw('NOW() as tgl_transaksi'), 'a.no_ktp', 'a.anggota_id', 
                DB::raw("'40' as jenis_id"), 'a.tagihan_simpanan_pokok', 
                DB::raw("'Setoran Simpanan Pokok - " . $bulan . "-" . $tahun . "' as keterangan"), 
                DB::raw("'Setoran' as akun"), DB::raw("'D' as dk"), DB::raw("'4' as kas_id")
            )
            ->from('tbl_trans_sp_bayar_temp as a')
            ->whereRaw('YEAR(a.tgl_transaksi) = ? AND MONTH(a.tgl_transaksi) = ?', [$tahun, $bulan])
            ->where('a.tagihan_simpanan_pokok', '<>', 0);
        });

        // Process simpanan sukarela
        DB::table('tbl_trans_sp')->insertUsing([
            'tgl_transaksi', 'no_ktp', 'anggota_id', 'jenis_id', 'jumlah', 'keterangan', 'akun', 'dk', 'kas_id'
        ], function ($query) use ($bulan, $tahun) {
            $query->select(
                DB::raw('NOW() as tgl_transaksi'), 'a.no_ktp', 'a.anggota_id', 
                DB::raw("'32' as jenis_id"), 'a.tagihan_simpanan_sukarela', 
                DB::raw("'Setoran Simpanan Sukarela - " . $bulan . "-" . $tahun . "' as keterangan"), 
                DB::raw("'Setoran' as akun"), DB::raw("'D' as dk"), DB::raw("'4' as kas_id")
            )
            ->from('tbl_trans_sp_bayar_temp as a')
            ->whereRaw('YEAR(a.tgl_transaksi) = ? AND MONTH(a.tgl_transaksi) = ?', [$tahun, $bulan])
            ->where('a.tagihan_simpanan_sukarela', '<>', 0);
        });

        // Process simpanan khusus 2
        DB::table('tbl_trans_sp')->insertUsing([
            'tgl_transaksi', 'no_ktp', 'anggota_id', 'jenis_id', 'jumlah', 'keterangan', 'akun', 'dk', 'kas_id'
        ], function ($query) use ($bulan, $tahun) {
            $query->select(
                DB::raw('NOW() as tgl_transaksi'), 'a.no_ktp', 'a.anggota_id', 
                DB::raw("'52' as jenis_id"), 'a.tagihan_simpanan_khusus_2', 
                DB::raw("'Setoran Simpanan Khusus 2 - " . $bulan . "-" . $tahun . "' as keterangan"), 
                DB::raw("'Setoran' as akun"), DB::raw("'D' as dk"), DB::raw("'4' as kas_id")
            )
            ->from('tbl_trans_sp_bayar_temp as a')
            ->whereRaw('YEAR(a.tgl_transaksi) = ? AND MONTH(a.tgl_transaksi) = ?', [$tahun, $bulan])
            ->where('a.tagihan_simpanan_khusus_2', '<>', 0);
        });
    }

    /**
     * Process pinjaman data to tbl_pinjaman_d
     */
    private function processPinjamanData($bulan, $tahun)
    {
        // Process pinjaman payments
        DB::table('tbl_pinjaman_d')->insertUsing([
            'tgl_bayar', 'pinjam_id', 'angsuran_ke', 'jumlah_bayar', 'bunga'
        ], function ($query) use ($bulan, $tahun) {
            $query->select(
                'a.tgl_transaksi as tgl_bayar', 'b.id as pinjam_id',
                DB::raw('COUNT(c.angsuran_ke) + 1 as angsuran_ke'),
                'a.tagihan_pinjaman as jumlah_bayar', 'a.tagihan_pinjaman_jasa as bunga'
            )
            ->from('tbl_trans_sp_bayar_temp as a')
            ->join('tbl_pinjaman_h as b', 'a.no_ktp', '=', 'b.no_ktp')
            ->join('tbl_pinjaman_d as c', 'c.pinjam_id', '=', 'b.id')
            ->whereRaw('YEAR(a.tgl_transaksi) = ? AND MONTH(a.tgl_transaksi) = ?', [$tahun, $bulan])
            ->where('a.selisih', '=', 0)
            ->groupBy('a.tgl_transaksi', 'a.no_ktp', 'b.id');
        });
    }

    /**
     * Process toserda data to tbl_trans_sp
     */
    private function processToserdaData($bulan, $tahun)
    {
        // Process toserda payments
        DB::table('tbl_trans_sp')->insertUsing([
            'tgl_transaksi', 'no_ktp', 'anggota_id', 'jenis_id', 'jumlah', 'keterangan', 'akun', 'dk', 'kas_id'
        ], function ($query) use ($bulan, $tahun) {
            $query->select(
                DB::raw('NOW() as tgl_transaksi'), 'a.no_ktp', 'a.anggota_id', 
                DB::raw("'155' as jenis_id"), 'a.tagihan_toserda', 'b.keterangan', 
                DB::raw("'Setoran' as akun"), DB::raw("'D' as dk"), DB::raw("'4' as kas_id")
            )
            ->from('tbl_trans_sp_bayar_temp as a')
            ->join('tbl_trans_tagihan as b', 'a.no_ktp', '=', 'b.no_ktp')
            ->whereRaw('YEAR(a.tgl_transaksi) = ? AND MONTH(b.tgl_transaksi) = ?', [$tahun, $bulan])
            ->where('a.tagihan_toserda', '<>', 0);
        });
    }

    /**
     * Clean up temporary data
     */
    private function cleanupTemporaryData($bulan, $tahun)
    {
        // Delete data from tbl_trans_sp_bayar_temp
        DB::table('tbl_trans_sp_bayar_temp')
            ->whereRaw('YEAR(tgl_transaksi) = ? AND MONTH(tgl_transaksi) = ?', [$tahun, $bulan])
            ->delete();

        // Delete data from billing_upload_temp
        DB::table('billing_upload_temp')
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->delete();
    }

    /**
     * Get processed count for message
     */
    private function getProcessedCount($bulan, $tahun)
    {
        return DB::table('tbl_trans_sp_bayar_temp')
            ->whereRaw('YEAR(tgl_transaksi) = ? AND MONTH(tgl_transaksi) = ?', [$tahun, $bulan])
            ->count();
    }

    /**
     * Generate billing pinjaman otomatis untuk bulan tertentu
     */
    private function generateBillingPinjamanOtomatis($bulan, $tahun)
    {
        try {
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
            
            // Proses billing pinjaman ke tbl_trans_sp_bayar_temp
            if (!empty($billingData)) {
                $this->processBillingPinjamanToMain($bulan, $tahun);
            }
            
        } catch (\Exception $e) {
            Log::error('Error generating billing pinjaman otomatis: ' . $e->getMessage());
        }
    }

    /**
     * Proses billing pinjaman ke tbl_trans_sp_bayar_temp
     */
    private function processBillingPinjamanToMain($bulan, $tahun)
    {
        try {
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
                        'tgl_transaksi' => \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->toDateString(),
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
            
        } catch (\Exception $e) {
            Log::error('Error processBillingPinjamanToMain: ' . $e->getMessage());
        }
    }

    /**
     * Get period summary data for the period table
     */
    private function getPeriodSummaryData($bulan, $tahun)
    {
        try {
            $periode = $tahun . '-' . str_pad($bulan, 2, '0', STR_PAD_LEFT);
            
            // Query Total Anggota - SELALU ambil dari tbl_anggota (konsisten)
            $totalAnggota = DB::table('tbl_anggota')
                ->where('aktif', 'Y')  // Hanya anggota aktif
                ->count('no_ktp');

            // Query Simpanan Pokok - SELALU ambil dari tbl_anggota (konsisten)
            // Note: tbl_anggota tidak memiliki kolom simpanan_pokok, gunakan simpanan_wajib sebagai gantinya
            $simpananPokok = DB::table('tbl_anggota')
                ->where('aktif', 'Y')  // Hanya anggota aktif
                ->sum('simpanan_wajib');

            // Query Simpanan Sukarela (dari billing) - SAMA dengan BillingPeriodeController
            $simpananSukarela = DB::table('tbl_trans_sp_bayar_temp')
                ->whereMonth('tgl_transaksi', $bulan)
                ->whereYear('tgl_transaksi', $tahun)
                ->sum('tagihan_simpanan_sukarela') ?? 0;

            // Query Simpanan Wajib (dari billing) - SAMA dengan BillingPeriodeController
            $simpananWajib = DB::table('tbl_trans_sp_bayar_temp')
                ->whereMonth('tgl_transaksi', $bulan)
                ->whereYear('tgl_transaksi', $tahun)
                ->sum('tagihan_simpanan_wajib') ?? 0;

            // Debug: Log the results untuk konsistensi
            Log::info("BillingUtamaController period summary results:", [
                'periode' => $periode,
                'bulan' => $bulan,
                'tahun' => $tahun,
                'total_anggota' => $totalAnggota,
                'simpanan_pokok' => $simpananPokok,
                'simpanan_sukarela' => $simpananSukarela,
                'simpanan_wajib' => $simpananWajib,
                'controller' => 'BillingUtamaController'
            ]);

            return [
                'periode' => $periode,
                'total_anggota' => $totalAnggota,
                'simpanan_pokok' => $simpananPokok,
                'simpanan_sukarela' => $simpananSukarela,
                'simpanan_wajib' => $simpananWajib
            ];

        } catch (\Exception $e) {
            Log::error('Error getting period summary data: ' . $e->getMessage());
            return [
                'periode' => $periode ?? '',
                'total_anggota' => 0,
                'simpanan_pokok' => 0,
                'simpanan_sukarela' => 0,
                'simpanan_wajib' => 0
            ];
        }
    }
}