<?php

namespace App\Http\Controllers;

use App\Models\billing;
use App\Models\data_anggota;
use App\Models\jns_simpan;
use App\Models\jns_akun;
use App\Models\TblTransToserda;
use App\Models\TblTransSp;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\PDF;
use Carbon\Carbon;

class BillingController extends Controller
{
    // Konstanta untuk jenis ID
    const JENIS_ID_PINJAMAN = 999;
    const JENIS_ID_SIMPANAN_WAJIB = 41;
    const JENIS_ID_SIMPANAN_SUKARELA = 32;
    const JENIS_ID_SIMPANAN_KHUSUS_2 = 52;
    const JENIS_ID_TOSERDA = 155;
    
    // Define bulanList as class property
    private $bulanList = [
        '01' => 'Januari',
        '02' => 'Februari',
        '03' => 'Maret',
        '04' => 'April',
        '05' => 'Mei',
        '06' => 'Juni',
        '07' => 'Juli',
        '08' => 'Agustus',
        '09' => 'September',
        '10' => 'Oktober',
        '11' => 'November',
        '12' => 'Desember'
    ];
    
    public function index(Request $request)
    {
        try {
            $bulan = $request->input('bulan', date('m'));
            $tahun = $request->input('tahun', date('Y'));
            
            // Debug input values
            Log::info('Filtering billing with:', [
                'bulan' => $bulan,
                'tahun' => $tahun,
                'bulan_tahun' => $this->bulanList[$bulan] . ' ' . $tahun
            ]);
            
            // Tidak ada auto-generate, data hanya muncul jika sudah ada
            $bulan_tahun_string = $this->bulanList[$bulan] . ' ' . $tahun;
            
            // Base query for billing data with month/year filter
            $query = billing::with('anggota')
                ->where('jns_trans', 'simpanan')
                ->where('bulan_tahun', $bulan_tahun_string)
                ->orderBy('created_at', 'desc'); // Tampilkan data terbaru dulu

            // Pencarian berdasarkan nama, ID anggota, atau kode transaksi
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('nama', 'like', '%' . $search . '%')
                      ->orWhere('id_anggota', 'like', '%' . $search . '%')
                      ->orWhere('kode_transaksi', 'like', '%' . $search . '%')
                      ->orWhereHas('anggota', function($subQ) use ($search) {
                          $subQ->where('no_ktp', 'like', '%' . $search . '%');
                      });
                });
            }
            
            // Pastikan urutan tetap berdasarkan created_at DESC
            $query->orderBy('created_at', 'desc');
            
            // Ambil data billing dengan pagination
            $dataBilling = $query->paginate(10);
            
            // Data untuk dropdown tahun (5 tahun ke belakang sampai 2 tahun ke depan)
            $tahunList = range(date('Y') - 5, date('Y') + 2);
            
            // Ambil data anggota untuk dropdown
            $anggota = data_anggota::where('aktif', 'Y')->get();
            
            return view('billing.billing', [
                'dataBilling' => $dataBilling,
                'bulan' => $bulan,
                'tahun' => $tahun,
                'tahunList' => $tahunList,
                'bulanList' => $this->bulanList,
                'anggota' => $anggota
            ]);
            
        } catch (\Exception $e) {
            //Log the error
            Log::error('Error in billing index: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            // Return empty paginator instead of empty collection
            // Ambil data anggota untuk dropdown
            $anggota = data_anggota::where('aktif', 'Y')->get();
            
            return view('billing.billing', [
                'error' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'dataBilling' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10),
                'bulan' => $bulan ?? date('m'),
                'tahun' => $tahun ?? date('Y'),
                'tahunList' => range(date('Y') - 5, date('Y') + 2),
                'bulanList' => $this->bulanList,
                'anggota' => $anggota
            ]);
        }
    }
    
    /**
     * Generate full billing untuk bulan dan tahun tertentu
     * Implementasi dari pseudocode yang diberikan
     */
    private function generateFullBilling($bulan_input, $tahun_input)
    {
        // Get current month and year
        $bulan_sekarang = date('m');
        $tahun_sekarang = date('Y');
        
        // Mengizinkan load semua bulan (sesuai kebutuhan revisi)
        
        // Format bulan tahun string
        $bulan_tahun_string = $this->bulanList[$bulan_input] . ' ' . $tahun_input;
        
        try {
            // 1. Hapus data billing simpanan untuk bulan dan tahun tertentu saja (bukan semua)
            $deletedCount = billing::where('jns_trans', 'simpanan')
                ->where('bulan_tahun', $bulan_tahun_string)
                ->delete();
            
            // Log untuk debugging
            Log::info("Deleted $deletedCount billing records for $bulan_tahun_string before generating new data");
            
            // Mulai transaction untuk generate data baru
            DB::beginTransaction();
             
            // 2. Generate billing simpanan (wajib, sukarela, khusus_2) + simpanan pokok jika bulan daftar
            // Get active members
            $anggotaAktif = data_anggota::where('aktif', 'Y')->get();
            
            // Debug: Log jumlah anggota aktif
            Log::info("Found " . $anggotaAktif->count() . " active members for billing generation");
            
            // Get simpanan settings from jns_simpan table
            $simpanan_pokok = jns_simpan::where('jns_simpan', 'Simpanan Pokok')->value('jumlah') ?? 100000;
            $simpanan_wajib = jns_simpan::where('jns_simpan', 'Simpanan Wajib')->value('jumlah') ?? 50000;
            
            // Get simpanan account ID
            $akunSimpanan = jns_akun::where('akun', 'like', '%Simpanan%')->first();
            $id_akun_simpanan = $akunSimpanan ? $akunSimpanan->id : 151; // Default to 151 if not found
            
            // Prepare data for bulk insert - SIMPANAN
            $billingData = [];
            
            foreach ($anggotaAktif as $anggota) {
                // Each member's monthly billing should be their individual savings amounts
                $simpanan_wajib = $anggota->simpanan_wajib ?? 0;
                $simpanan_sukarela = $anggota->simpanan_sukarela ?? 0;
                $simpanan_khusus_2 = $anggota->simpanan_khusus_2 ?? 0;
                $simpanan_khusus_1 = 0; // Default value, bisa diambil dari tbl_anggota jika ada
                $tab_perumahan = 0; // Default value, bisa diambil dari tbl_anggota jika ada
                
                // Debug: Log setiap anggota yang diproses
                Log::info("Processing member: {$anggota->nama} (ID: {$anggota->id}) - Simpanan: Wajib={$simpanan_wajib}, Sukarela={$simpanan_sukarela}, Khusus2={$simpanan_khusus_2}");
                
                // Add simpanan pokok if this is the member's registration month
                $tambah_simpanan_pokok = 0;
                if ($anggota->tgl_daftar && $anggota->tgl_daftar != '0000-00-00') {
                    try {
                        $tgl_daftar = Carbon::parse($anggota->tgl_daftar);
                        $tambah_simpanan_pokok = ($bulan_input == $tgl_daftar->format('m') && $tahun_input == $tgl_daftar->format('Y'))
                            ? ($simpanan_pokok ?? 100000) : 0;
                    } catch (\Exception $e) {
                        // Jika error parsing tanggal, tidak tambah simpanan pokok
                        $tambah_simpanan_pokok = 0;
                    }
                }
                
                $total_simpanan = $simpanan_wajib + $simpanan_sukarela + $simpanan_khusus_2 + $simpanan_khusus_1 + $tab_perumahan + $tambah_simpanan_pokok;
                
                // Generate billing code dengan format baru
                $billing_code = billing::generateBillingCode($bulan_input, $tahun_input, $anggota->id);
                
                // Prepare data for bulk insert
                $billingData[] = [
                    'kode_transaksi' => $billing_code,
                    'bulan_tahun' => $bulan_tahun_string,
                    'id_anggota' => $anggota->id,
                    'nama' => $anggota->nama,
                    'simpanan_wajib' => $simpanan_wajib,
                    'simpanan_khusus_1' => $simpanan_khusus_1,
                    'simpanan_sukarela' => $simpanan_sukarela,
                    'simpanan_khusus_2' => $simpanan_khusus_2,
                    'tab_perumahan' => $tab_perumahan,
                    'simpanan_pokok' => $tambah_simpanan_pokok,
                    'total_tagihan' => $total_simpanan,
                    'jns_trans' => 'simpanan',
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
            
            // Perform bulk insert in chunks to improve performance - SIMPANAN
            if (!empty($billingData)) {
                Log::info("Inserting " . count($billingData) . " billing records");
                foreach (array_chunk($billingData, 100) as $chunk) {
                    billing::insert($chunk);
                }
            } else {
                Log::warning("No billing data to insert - billingData array is empty");
            }
            
            // Tidak generate TOSERDA di sini (dipindah ke modul Toserda -> Billing Utama via process-all)
            
            // Generate billing pinjaman untuk bulan ini
            $this->generateBillingPinjaman($bulan_input, $tahun_input);
            
            // Proses billing pinjaman ke tbl_trans_sp_bayar_temp
            $this->processBillingPinjamanToMain($bulan_input, $tahun_input);
            
            DB::commit();
            
            return [
                'status' => 'success',
                'message' => 'Billing simpanan berhasil di-generate untuk ' . count($billingData) . ' anggota.'
            ];
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error generating billing: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return [
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat generate billing: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generate billing pinjaman untuk bulan tertentu
     */
    private function generateBillingPinjaman($bulan, $tahun)
    {
        try {
            // Hapus billing pinjaman bulan sebelumnya untuk menghindari duplikasi
            DB::table('tbl_trans_tagihan')
                ->where('jenis_id', self::JENIS_ID_PINJAMAN)
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
                // Hitung angsuran per bulan dengan pembulatan yang benar
                $angsuranPokok = floor($jadwal->jumlah / $jadwal->lama_angsuran);
                $sisaPembulatan = $jadwal->jumlah - ($angsuranPokok * $jadwal->lama_angsuran);
                
                // Hitung angsuran bunga
                $angsuranBunga = floor($jadwal->bunga_rp / $jadwal->lama_angsuran);
                $sisaBunga = $jadwal->bunga_rp - ($angsuranBunga * $jadwal->lama_angsuran);
                
                // Tentukan apakah ini angsuran terakhir untuk menambahkan sisa pembulatan
                $bulanTempo = date('m', strtotime($jadwal->tempo));
                $tahunTempo = date('Y', strtotime($jadwal->tempo));
                $bulanPinjam = date('m', strtotime($jadwal->tgl_pinjam ?? $jadwal->tempo));
                $tahunPinjam = date('Y', strtotime($jadwal->tgl_pinjam ?? $jadwal->tempo));
                
                // Hitung bulan ke berapa dari total angsuran
                $bulanKe = (($tahunTempo - $tahunPinjam) * 12) + ($bulanTempo - $bulanPinjam) + 1;
                
                // Jika ini angsuran terakhir, tambahkan sisa pembulatan
                if ($bulanKe == $jadwal->lama_angsuran) {
                    $angsuranPokok += $sisaPembulatan;
                    $angsuranBunga += $sisaBunga;
                }
                
                $totalAngsuran = $angsuranPokok + $angsuranBunga;
                
                // Generate tagihan untuk semua jadwal, terlepas dari status lunas
                $billingData[] = [
                    'tgl_transaksi' => $jadwal->tempo,
                    'no_ktp' => $jadwal->no_ktp,
                    'anggota_id' => null, // Akan diisi nanti
                    'jenis_id' => self::JENIS_ID_PINJAMAN,
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
            
            Log::info('Billing pinjaman berhasil di-generate', [
                'bulan' => $bulan,
                'tahun' => $tahun,
                'jumlah_tagihan' => count($billingData)
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error generating billing pinjaman: ' . $e->getMessage());
            throw $e;
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
                ->where('jenis_id', self::JENIS_ID_PINJAMAN)
                ->whereMonth('tgl_transaksi', $bulan)
                ->whereYear('tgl_transaksi', $tahun)
                ->groupBy('no_ktp')
                ->get();
            
            foreach ($tagihanPinjaman as $tagihan) {
                // Upsert ke tbl_trans_sp_bayar_temp
                DB::table('tbl_trans_sp_bayar_temp')->updateOrInsert(
                    [
                        'tgl_transaksi' => Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->toDateString(),
                        'no_ktp' => $tagihan->no_ktp,
                    ],
                    [
                        'anggota_id' => null,
                        'jumlah' => DB::raw('COALESCE(jumlah,0) + ' . ($tagihan->total ?? 0)),
                        'keterangan' => 'Billing Pinjaman ' . $bulan . '-' . $tahun,
                        'tagihan_simpanan_wajib' => DB::raw('COALESCE(tagihan_simpanan_wajib,0)'),
                        'tagihan_simpanan_sukarela' => DB::raw('COALESCE(tagihan_simpanan_sukarela,0)'),
                        'tagihan_simpanan_khusus_2' => DB::raw('COALESCE(tagihan_simpanan_khusus_2,0)'),
                        'tagihan_pinjaman' => DB::raw('COALESCE(tagihan_pinjaman,0) + ' . ($tagihan->total ?? 0)),
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
                ->where('jenis_id', self::JENIS_ID_PINJAMAN)
                ->whereMonth('tgl_transaksi', $bulan)
                ->whereYear('tgl_transaksi', $tahun)
                ->delete();
            
        } catch (\Exception $e) {
            Log::error('Error processBillingPinjamanToMain: ' . $e->getMessage());
        }
    }

    // Proses semua billing simpanan ke Billing Utama (tbl_trans_sp_bayar_temp)
    public function processAllToMain(Request $request)
    {
        $bulan = $request->input('bulan', date('m'));
        $tahun = $request->input('tahun', date('Y'));
        try {
            DB::beginTransaction();
            $billings = billing::where('jns_trans', 'simpanan')
                ->where('bulan_tahun', 'like', '%' . $this->bulanList[$bulan] . ' ' . $tahun . '%')
                ->get();

            foreach ($billings as $b) {
                // Get anggota data
                $anggota = DB::table('tbl_anggota')
                    ->where('id', $b->id_anggota)
                    ->first();
                
                $anggotaId = $anggota ? $anggota->id : null;
                $noKtp = $anggota ? $anggota->no_ktp : null;
                
                // Cek apakah data sudah ada di tbl_trans_sp_bayar_temp
                $existingData = DB::table('tbl_trans_sp_bayar_temp')
                    ->where('tgl_transaksi', Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->toDateString())
                    ->where('no_ktp', $noKtp)
                    ->first();

                if ($existingData) {
                    // TAMBAH field simpanan ke data yang sudah ada, jangan timpa
                    DB::table('tbl_trans_sp_bayar_temp')
                        ->where('tgl_transaksi', Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->toDateString())
                        ->where('no_ktp', $noKtp)
                        ->update([
                            'anggota_id' => $anggotaId,
                            'keterangan' => 'Billing Simpanan ' . ($b->bulan_tahun ?? ($bulan.'-'.$tahun)),
                            'tagihan_simpanan_wajib' => DB::raw('COALESCE(tagihan_simpanan_wajib, 0) + ' . ($b->simpanan_wajib ?? 0)),
                            'tagihan_simpanan_sukarela' => DB::raw('COALESCE(tagihan_simpanan_sukarela, 0) + ' . ($b->simpanan_sukarela ?? 0)),
                            'tagihan_simpanan_khusus_2' => DB::raw('COALESCE(tagihan_simpanan_khusus_2, 0) + ' . ($b->simpanan_khusus_2 ?? 0)),
                            'tagihan_simpanan_pokok' => DB::raw('COALESCE(tagihan_simpanan_pokok, 0) + ' . ($b->simpanan_pokok ?? 0)),
                            'total_tagihan_simpanan' => DB::raw('COALESCE(total_tagihan_simpanan, 0) + ' . ($b->total_tagihan ?? 0)),
                            // Jangan update field pinjaman yang sudah ada
                        ]);
                } else {
                    // Insert data baru jika belum ada
                    DB::table('tbl_trans_sp_bayar_temp')->insert([
                        'tgl_transaksi' => Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->toDateString(),
                        'no_ktp' => $noKtp,
                        'anggota_id' => $anggotaId,
                        'jumlah' => 0,
                        'keterangan' => 'Billing Simpanan ' . ($b->bulan_tahun ?? ($bulan.'-'.$tahun)),
                        'tagihan_simpanan_wajib' => $b->simpanan_wajib ?? 0,
                        'tagihan_simpanan_sukarela' => $b->simpanan_sukarela ?? 0,
                        'tagihan_simpanan_khusus_2' => $b->simpanan_khusus_2 ?? 0,
                        'tagihan_simpanan_pokok' => $b->simpanan_pokok ?? 0,
                        'tagihan_pinjaman' => 0,
                        'tagihan_pinjaman_jasa' => 0,
                        'tagihan_toserda' => 0,
                        'total_tagihan_simpanan' => ($b->total_tagihan ?? 0),
                        'selisih' => 0,
                        'saldo_simpanan_sukarela' => 0,
                        'saldo_akhir_simpanan_sukarela' => 0,
                    ]);
                }
            }

            // Hapus billing simpanan bulan/tahun ini setelah dipindahkan
            billing::where('jns_trans', 'simpanan')
                ->where('bulan_tahun', 'like', '%' . $this->bulanList[$bulan] . ' ' . $tahun . '%')
                ->delete();

            DB::commit();
            return redirect()->route('billing.utama')->with('success', 'Berhasil memproses semua billing simpanan ke Billing Utama.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processAllToMain: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    /**
     * Process selected billing to main billing table
     */
    public function processSelectedToMain(Request $request)
    {
        $billingId = $request->input('billing_id');
        
        if (!$billingId) {
            return redirect()->back()->with('error', 'ID billing tidak ditemukan');
        }
        
        try {
            DB::beginTransaction();
            
            // Get the specific billing record
            $billing = billing::where('id', $billingId)
                ->where('jns_trans', 'simpanan')
                ->first();
                
            if (!$billing) {
                return redirect()->back()->with('error', 'Data billing tidak ditemukan');
            }
            
            // Get anggota data
            $anggota = DB::table('tbl_anggota')
                ->where('id', $billing->id_anggota)
                ->first();
            
            $anggotaId = $anggota ? $anggota->id : null;
            $noKtp = $anggota ? $anggota->no_ktp : null;
            
            // Extract bulan and tahun from billing
            $bulanTahun = $billing->bulan_tahun;
            $bulan = null;
            $tahun = null;
            
            // Parse bulan_tahun format (e.g., "September 2025")
            foreach ($this->bulanList as $key => $value) {
                if (strpos($bulanTahun, $value) !== false) {
                    $bulan = $key;
                    $tahun = preg_replace('/[^0-9]/', '', $bulanTahun);
                    break;
                }
            }
            
            if (!$bulan || !$tahun) {
                return redirect()->back()->with('error', 'Format bulan/tahun tidak valid');
            }
            
            // Cek apakah data sudah ada di tbl_trans_sp_bayar_temp
            $existingData = DB::table('tbl_trans_sp_bayar_temp')
                ->where('tgl_transaksi', Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->toDateString())
                ->where('no_ktp', $noKtp)
                ->first();

            if ($existingData) {
                // TAMBAH field simpanan ke data yang sudah ada, jangan timpa
                DB::table('tbl_trans_sp_bayar_temp')
                    ->where('tgl_transaksi', Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->toDateString())
                    ->where('no_ktp', $noKtp)
                    ->update([
                        'anggota_id' => $anggotaId,
                        'keterangan' => 'Billing Simpanan ' . $bulanTahun,
                        'tagihan_simpanan_wajib' => DB::raw('COALESCE(tagihan_simpanan_wajib, 0) + ' . ($billing->simpanan_wajib ?? 0)),
                        'tagihan_simpanan_sukarela' => DB::raw('COALESCE(tagihan_simpanan_sukarela, 0) + ' . ($billing->simpanan_sukarela ?? 0)),
                        'tagihan_simpanan_khusus_2' => DB::raw('COALESCE(tagihan_simpanan_khusus_2, 0) + ' . ($billing->simpanan_khusus_2 ?? 0)),
                        'tagihan_simpanan_pokok' => DB::raw('COALESCE(tagihan_simpanan_pokok, 0) + ' . ($billing->simpanan_pokok ?? 0)),
                        'total_tagihan_simpanan' => DB::raw('COALESCE(total_tagihan_simpanan, 0) + ' . ($billing->total_tagihan ?? 0)),
                        // Jangan update field pinjaman yang sudah ada
                    ]);
            } else {
                // Insert data baru jika belum ada
                DB::table('tbl_trans_sp_bayar_temp')->insert([
                    'tgl_transaksi' => Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->toDateString(),
                    'no_ktp' => $noKtp,
                    'anggota_id' => $anggotaId,
                    'jumlah' => 0,
                    'keterangan' => 'Billing Simpanan ' . $bulanTahun,
                    'tagihan_simpanan_wajib' => $billing->simpanan_wajib ?? 0,
                    'tagihan_simpanan_sukarela' => $billing->simpanan_sukarela ?? 0,
                    'tagihan_simpanan_khusus_2' => $billing->simpanan_khusus_2 ?? 0,
                    'tagihan_simpanan_pokok' => $billing->simpanan_pokok ?? 0,
                    'tagihan_pinjaman' => 0,
                    'tagihan_pinjaman_jasa' => 0,
                    'tagihan_toserda' => 0,
                    'total_tagihan_simpanan' => ($billing->total_tagihan ?? 0),
                    'selisih' => 0,
                    'saldo_simpanan_sukarela' => 0,
                    'saldo_akhir_simpanan_sukarela' => 0,
                ]);
            }
            
            // Hapus billing simpanan yang dipilih setelah dipindahkan
            billing::where('id', $billingId)->delete();
            
            DB::commit();
            return redirect()->route('billing.utama')->with('success', 'Berhasil memproses billing simpanan "' . $anggota->nama . '" ke Billing Utama.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processSelectedToMain: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    /**
     * Cancel a processed payment and move it back to billing table
     */
    public function cancelPayment($billing_process_id)
    {
        try {
            // Find the processed billing first before starting transaction
            $processedBilling = \App\Models\BillingProcess::find($billing_process_id);
            
            if (!$processedBilling) {
                return redirect()->back()->with('error', 'Data pembayaran tidak ditemukan');
            }
            
            // Check if the billing already exists in billing table before starting transaction
            $billingExists = billing::where('billing_code', $processedBilling->billing_code)
                ->orWhere(function($query) use ($processedBilling) {
                    $query->where('no_ktp', $processedBilling->no_ktp)
                          ->where('bulan', $processedBilling->bulan)
                          ->where('tahun', $processedBilling->tahun);
                })
                ->exists();
            
            if ($billingExists) {
                return redirect()->back()->with('error', 'Billing sudah ada di daftar tagihan aktif');
            }
            
            // Now start the transaction
            DB::beginTransaction();
            
            // Create a new billing record
            $billing = new billing();
            $billing->fill($processedBilling->toArray());
            $billing->status = 'N';
            $billing->save();
            
            // Delete the processed billing
            $processedBilling->delete();
            
            DB::commit();
            
            return redirect()->back()->with('success', 'Pembayaran berhasil dibatalkan dan dikembalikan ke daftar tagihan');
        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            Log::error('Error in cancelPayment: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    public function exportExcel(Request $request)
    {
        // Buat spreadsheet baru
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set judul kolom
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'ID Billing');
        $sheet->setCellValue('C1', 'No KTP');
        $sheet->setCellValue('D1', 'Nama');
        $sheet->setCellValue('E1', 'Bulan');
        $sheet->setCellValue('F1', 'Tahun');
        $sheet->setCellValue('G1', 'Jenis Transaksi');
        $sheet->setCellValue('H1', 'Total Tagihan');
        $sheet->setCellValue('I1', 'Status');
        
        // Format header dengan styling
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '14AE5C'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
        
        $sheet->getStyle('A1:I1')->applyFromArray($headerStyle);
        
        // Set lebar kolom
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(25);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(20);
        $sheet->getColumnDimension('H')->setWidth(15);
        $sheet->getColumnDimension('I')->setWidth(15);
        
        // Query data billing
        $query = billing::query();
        
        // Pencarian berdasarkan nama atau no KTP
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('no_ktp', 'like', '%' . $search . '%');
            });
        }
        
        // Filter berdasarkan bulan dan tahun jika ada
        if ($request->has('bulan') && $request->has('tahun')) {
            $query->where(function($q) use ($request) {
                $q->where(function($q2) use ($request) {
                    $q2->where('bulan', $request->bulan)
                       ->where('tahun', $request->tahun);
                })
                ->orWhere('bulan_tahun', 'like', '%' . $this->bulanList[$request->bulan] . ' ' . $request->tahun . '%');
            });
        }
        
        $dataBilling = $query->get();
        
        // Isi data
        $row = 2;
        $totalBilling = 0;
        foreach ($dataBilling as $index => $item) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $item->billing_code);
            $sheet->setCellValue('C' . $row, $item->no_ktp);
            $sheet->setCellValue('D' . $row, $item->nama);
            $sheet->setCellValue('E' . $row, $item->bulan);
            $sheet->setCellValue('F' . $row, $item->tahun);
            $sheet->setCellValue('G' . $row, $item->jns_trans ?? 'Billing');
            $sheet->setCellValue('H' . $row, $item->total_tagihan ?? $item->total_billing ?? 0);
            $sheet->setCellValue('I' . $row, ($item->status == 'Y') ? 'Lunas' : 'Belum Lunas');
            
            // Format angka untuk kolom nominal
            $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode('#,##0');
            
            $totalBilling += $item->total_tagihan ?? $item->total_billing ?? 0;
            $row++;
        }
        
        // Tambahkan total keseluruhan
        $sheet->setCellValue('A' . $row, '');
        $sheet->setCellValue('B' . $row, '');
        $sheet->setCellValue('C' . $row, '');
        $sheet->setCellValue('D' . $row, '');
        $sheet->setCellValue('E' . $row, '');
        $sheet->setCellValue('F' . $row, '');
        $sheet->setCellValue('G' . $row, 'TOTAL');
        $sheet->setCellValue('H' . $row, $totalBilling);
        $sheet->setCellValue('I' . $row, '');
        
        // Format total
        $sheet->getStyle('G' . $row . ':H' . $row)->getFont()->setBold(true);
        $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode('#,##0');
        
        // Set style untuk semua data
        $dataStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
        
        $sheet->getStyle('A2:I' . $row)->applyFromArray($dataStyle);
        
        // Buat file Excel
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        // Set header untuk download
        $filename = 'billing_anggota_' . date('Ymd') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        // Tulis ke output
        $writer->save('php://output');
        exit;
    }
    
    public function exportPdf(Request $request)
    {
        // Query data billing
        $query = billing::query();
        
        // Pencarian berdasarkan nama atau no KTP
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('no_ktp', 'like', '%' . $search . '%');
            });
        }
        
        // Filter berdasarkan bulan dan tahun jika ada
        if ($request->has('bulan') && $request->has('tahun')) {
            $query->where(function($q) use ($request) {
                $q->where(function($q2) use ($request) {
                    $q2->where('bulan', $request->bulan)
                       ->where('tahun', $request->tahun);
                })
                ->orWhere('bulan_tahun', 'like', '%' . $this->bulanList[$request->bulan] . ' ' . $request->tahun . '%');
            });
        }
        
        $dataBilling = $query->get();
        
        // Load view untuk PDF
        $pdf = PDF::loadView('billing.pdf', compact('dataBilling'));
        
        // Download PDF
        return $pdf->download('billing_anggota_' . date('Ymd') . '.pdf');
    }
    
    /**
     * Display processed billing records.
     */
    public function processed(Request $request)
    {
        try {
            $bulan = $request->input('bulan', date('m'));
            $tahun = $request->input('tahun', date('Y'));
            
            // Base query
            $query = \App\Models\BillingProcess::query();

            // Pencarian berdasarkan nama atau no KTP
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('nama', 'like', '%' . $search . '%')
                      ->orWhere('no_ktp', 'like', '%' . $search . '%');
                });
            }
            
            // Filter berdasarkan bulan dan tahun
            if ($bulan && $tahun) {
                $query->where(function($q) use ($bulan, $tahun) {
                    $q->where(function($q2) use ($bulan, $tahun) {
                        $q2->where('bulan', $bulan)
                           ->where('tahun', $tahun);
                    })
                    ->orWhere('bulan_tahun', 'like', '%' . $this->bulanList[$bulan] . ' ' . $tahun . '%');
                });
            }
            
            // Ambil data billing dengan pagination
            $dataBillingProcess = $query->paginate(10);
            
            // Data untuk dropdown tahun (5 tahun ke belakang sampai 2 tahun ke depan)
            $tahunList = range(date('Y') - 5, date('Y') + 2);
            
            return view('billing.processed', [
                'dataBillingProcess' => $dataBillingProcess,
                'bulan' => $bulan,
                'tahun' => $tahun,
                'tahunList' => $tahunList,
                'bulanList' => $this->bulanList
            ]);
            
        } catch (\Exception $e) {
            // Log the error
            Log::error('Error in processed billings: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            // Return empty paginator instead of empty collection
            return view('billing.processed', [
                'error' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'dataBillingProcess' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10),
                'bulan' => $bulan ?? date('m'),
                'tahun' => $tahun ?? date('Y'),
                'tahunList' => range(date('Y') - 5, date('Y') + 2),
                'bulanList' => $this->bulanList
            ]);
        }
    }
    
    /**
     * Export processed billings to Excel
     */
    public function exportProcessedExcel(Request $request)
    {
        // Buat spreadsheet baru
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set judul kolom
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'ID Billing');
        $sheet->setCellValue('C1', 'No KTP');
        $sheet->setCellValue('D1', 'Nama');
        $sheet->setCellValue('E1', 'Bulan');
        $sheet->setCellValue('F1', 'Tahun');
        $sheet->setCellValue('G1', 'Jenis Transaksi');
        $sheet->setCellValue('H1', 'Total Tagihan');
        $sheet->setCellValue('I1', 'Tanggal Bayar');
        
        // Format header dengan styling
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '14AE5C'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
        
        $sheet->getStyle('A1:I1')->applyFromArray($headerStyle);
        
        // Set lebar kolom
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(25);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(20);
        $sheet->getColumnDimension('H')->setWidth(15);
        $sheet->getColumnDimension('I')->setWidth(15);
        
        // Query data billing
        $query = \App\Models\BillingProcess::query();
        
        // Pencarian berdasarkan nama atau no KTP
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('no_ktp', 'like', '%' . $search . '%');
            });
        }
        
        // Filter berdasarkan bulan dan tahun jika ada
        if ($request->has('bulan') && $request->has('tahun')) {
            $query->where(function($q) use ($request) {
                $q->where(function($q2) use ($request) {
                    $q2->where('bulan', $request->bulan)
                       ->where('tahun', $request->tahun);
                })
                ->orWhere('bulan_tahun', 'like', '%' . $this->bulanList[$request->bulan] . ' ' . $request->tahun . '%');
            });
        }
        
        $dataBillingProcess = $query->get();
        
        // Isi data
        $row = 2;
        $totalBilling = 0;
        foreach ($dataBillingProcess as $index => $item) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $item->billing_code);
            $sheet->setCellValue('C' . $row, $item->no_ktp);
            $sheet->setCellValue('D' . $row, $item->nama);
            $sheet->setCellValue('E' . $row, $item->bulan);
            $sheet->setCellValue('F' . $row, $item->tahun);
            $sheet->setCellValue('G' . $row, $item->jns_trans ?? 'Billing');
            $sheet->setCellValue('H' . $row, $item->total_tagihan ?? $item->total_billing ?? 0);
            $sheet->setCellValue('I' . $row, $item->tgl_bayar ? $item->tgl_bayar->format('d/m/Y') : '-');
            
            // Format angka untuk kolom nominal
            $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode('#,##0');
            
            $totalBilling += $item->total_tagihan ?? $item->total_billing ?? 0;
            $row++;
        }
        
        // Tambahkan total di baris terakhir
        $sheet->setCellValue('G' . $row, 'TOTAL');
        $sheet->setCellValue('H' . $row, $totalBilling);
        $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('G' . $row . ':H' . $row)->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F2F2F2'],
            ],
        ]);
        
        // Buat writer
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'billing_lunas_' . date('Ymd') . '.xlsx';
        
        // Simpan ke file sementara
        $temp_file = tempnam(sys_get_temp_dir(), $filename);
        $writer->save($temp_file);
        
        // Return response download
        return response()->download($temp_file, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
    
    /**
     * Export processed billings to PDF
     */
    public function exportProcessedPdf(Request $request)
    {
        // Query data billing
        $query = \App\Models\BillingProcess::query();
        
        // Pencarian berdasarkan nama atau no KTP
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('no_ktp', 'like', '%' . $search . '%');
            });
        }
        
        // Filter berdasarkan bulan dan tahun jika ada
        if ($request->has('bulan') && $request->has('tahun')) {
            $query->where(function($q) use ($request) {
                $q->where(function($q2) use ($request) {
                    $q2->where('bulan', $request->bulan)
                       ->where('tahun', $request->tahun);
                })
                ->orWhere('bulan_tahun', 'like', '%' . $this->bulanList[$request->bulan] . ' ' . $request->tahun . '%');
            });
        }
        
        $dataBillingProcess = $query->get();
        
        // Load view untuk PDF
        $pdf = PDF::loadView('billing.pdf_processed', compact('dataBillingProcess'));
        
        // Download PDF
        return $pdf->download('billing_lunas_' . date('Ymd') . '.pdf');
    }

    /**
     * Store a newly created billing record.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'id_anggota' => 'required|integer|min:1',
                'nama' => 'required|string|max:255',
                'simpanan_wajib' => 'required|numeric|min:0',
                'simpanan_khusus_1' => 'nullable|numeric|min:0',
                'simpanan_sukarela' => 'required|numeric|min:0',
                'simpanan_khusus_2' => 'required|numeric|min:0',
                'tab_perumahan' => 'nullable|numeric|min:0',
                'simpanan_pokok' => 'required|numeric|min:0'
            ]);

            // Calculate total tagihan
            $totalTagihan = $request->simpanan_wajib + ($request->simpanan_khusus_1 ?? 0) + 
                           $request->simpanan_sukarela + $request->simpanan_khusus_2 + 
                           ($request->tab_perumahan ?? 0) + $request->simpanan_pokok;

            // Generate billing code
            $bulan = date('m');
            $tahun = date('Y');
            $billingCode = billing::generateBillingCode($bulan, $tahun, $request->id_anggota);

            // Create billing record
            $billing = billing::create([
                'id_anggota' => $request->id_anggota,
                'nama' => $request->nama,
                'simpanan_wajib' => $request->simpanan_wajib,
                'simpanan_khusus_1' => $request->simpanan_khusus_1 ?? 0,
                'simpanan_sukarela' => $request->simpanan_sukarela,
                'simpanan_khusus_2' => $request->simpanan_khusus_2,
                'tab_perumahan' => $request->tab_perumahan ?? 0,
                'simpanan_pokok' => $request->simpanan_pokok,
                'total_tagihan' => $totalTagihan,
                'jns_trans' => 'simpanan',
                'bulan_tahun' => $this->bulanList[$bulan] . ' ' . $tahun,
                'kode_transaksi' => $billingCode
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data billing berhasil disimpan',
                'data' => $billing
            ]);

        } catch (\Exception $e) {
            Log::error('Error storing billing: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified billing record.
     */
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'id_anggota' => 'required|integer|min:1',
                'nama' => 'required|string|max:255',
                'simpanan_wajib' => 'required|numeric|min:0',
                'simpanan_khusus_1' => 'nullable|numeric|min:0',
                'simpanan_sukarela' => 'required|numeric|min:0',
                'simpanan_khusus_2' => 'required|numeric|min:0',
                'tab_perumahan' => 'nullable|numeric|min:0',
                'simpanan_pokok' => 'required|numeric|min:0'
            ]);

            $billing = billing::findOrFail($id);

            // Calculate total tagihan
            $totalTagihan = $request->simpanan_wajib + ($request->simpanan_khusus_1 ?? 0) + 
                           $request->simpanan_sukarela + $request->simpanan_khusus_2 + 
                           ($request->tab_perumahan ?? 0) + $request->simpanan_pokok;

            // Update billing record
            $billing->update([
                'id_anggota' => $request->id_anggota,
                'nama' => $request->nama,
                'simpanan_wajib' => $request->simpanan_wajib,
                'simpanan_khusus_1' => $request->simpanan_khusus_1 ?? 0,
                'simpanan_sukarela' => $request->simpanan_sukarela,
                'simpanan_khusus_2' => $request->simpanan_khusus_2,
                'tab_perumahan' => $request->tab_perumahan ?? 0,
                'simpanan_pokok' => $request->simpanan_pokok,
                'total_tagihan' => $totalTagihan
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data billing berhasil diupdate',
                'data' => $billing
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating billing: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified billing record.
     */
    public function destroy($id)
    {
        try {
            $billing = billing::findOrFail($id);
            $billing->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data billing berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting billing: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate ulang data billing untuk periode yang dipilih
     * Hapus semua data periode lain terlebih dahulu
     */
    public function generateUlang(Request $request)
    {
        try {
            $bulan = $request->input('bulan', date('m'));
            $tahun = $request->input('tahun', date('Y'));
            
            // Validasi input
            if (!$bulan || !$tahun) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bulan dan tahun harus diisi'
                ], 400);
            }

            $bulan_tahun_string = $this->bulanList[$bulan] . ' ' . $tahun;
            
            // Log: Cek data sebelum hapus
            $totalBefore = \App\Models\billing::where('jns_trans', 'simpanan')->count();
            Log::info('Total billing records before generate: ' . $totalBefore);
            
            // 1. HAPUS SEMUA DATA BILLING (semua periode)
            $deletedCount = \App\Models\billing::where('jns_trans', 'simpanan')->delete();
            Log::info('Deleted all billing records: ' . $deletedCount);
            
            // 2. GENERATE DATA UNTUK PERIODE YANG DIPILIH
            $result = $this->generateFullBilling($bulan, $tahun);
            
            if ($result['status'] === 'error') {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal generate data: ' . $result['message']
                ], 500);
            }
            
            // Log: Cek data setelah generate
            $totalAfter = \App\Models\billing::where('jns_trans', 'simpanan')->count();
            Log::info('Total billing records after generate: ' . $totalAfter);

            return response()->json([
                'success' => true,
                'message' => 'Berhasil menghapus ' . $deletedCount . ' data lama dan generate ulang data untuk periode ' . $this->bulanList[$bulan] . ' ' . $tahun . ' (' . $totalAfter . ' data baru)'
            ]);

        } catch (\Exception $e) {
            Log::error('Error generating billing: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal generate data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hapus semua data billing
     */
    public function clearTable(Request $request)
    {
        try {
            // Debug: Log request
            Log::info('Clear table request received');
            
            // Cek apakah ada data billing
            $totalBilling = \App\Models\billing::where('jns_trans', 'simpanan')->count();
            Log::info('Total billing records before delete: ' . $totalBilling);
            
            if ($totalBilling == 0) {
                return redirect()->back()->with('success', 'Tidak ada data billing untuk dihapus');
            }

            // Hapus semua data billing
            $deletedCount = \App\Models\billing::where('jns_trans', 'simpanan')->delete();
            Log::info('Deleted billing records: ' . $deletedCount);

            return redirect()->back()->with('success', 'Berhasil menghapus ' . $deletedCount . ' data billing');

        } catch (\Exception $e) {
            Log::error('Error clearing billing table: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return redirect()->back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }
}