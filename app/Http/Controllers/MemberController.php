<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Member;
use App\Models\JnsPinjaman;
use Carbon\Carbon;
use App\Http\Requests\StorePengajuanPinjamanRequest;
use App\Models\data_pengajuan;
use App\Services\ActivityLogService;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Hash as HashFacade;
use App\Exports\MemberPaymentReportExport;


class MemberController extends Controller
{
    
    // Menampilkan form pengajuan pinjaman baru
    public function tambahPengajuanPinjaman()
    {
        $jenisPinjaman = JnsPinjaman::all(); // Assuming you want to get all types of loans
        return view('member.form_pengajuan_pinjaman', compact('jenisPinjaman'));
    }

   

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'no_ktp' => 'required',
            'pass_word' => 'required'
        ]);

        $member = Member::where('no_ktp', $credentials['no_ktp'])->first();

        if ($member) {
            // Fix: Verify the provided password against the stored hash
            if (password_verify($credentials['pass_word'], $member->pass_word)) {
                Auth::guard('member')->login($member);
                $request->session()->regenerate();
                return redirect()->route('member.dashboard');
            }
            // Log failed login attempts
            Log::warning('Failed login attempt for member: ' . $credentials['no_ktp']);
        } else {
            Log::info('No member found with no_ktp: ' . $credentials['no_ktp']);
        }

        return back()->withErrors([
            'login' => 'The provided credentials do not match our records.',
        ])->withInput($request->except('pass_word'));
    }

    public function memberDashboard(Request $request)
    {
        $anggota = $this->getAuthenticatedMember();
        
        $periode = $request->get('periode', date('Y-m'));
        $tahun = substr($periode, 0, 4);
        $bulan = substr($periode, 5, 2);
        
        // 1. Hitung Saldo Simpanan (konsisten dengan project lama)
        $saldoSimpanan = $this->hitungSaldoSimpanan($anggota->no_ktp);
        
        // 2. Hitung Tagihan Kredit
        $tagihanKredit = $this->hitungTagihanKredit($anggota->no_ktp);
        
        // 3. Hitung Keterangan Pinjaman
        $keteranganPinjaman = $this->hitungKeteranganPinjaman($anggota->no_ktp);
        
        // 4. Ambil data tagihan simpanan menggunakan method project lama dengan periode yang dipilih
        $jmlTagihanSimpanan = $this->getJmlTagihanSimpanan($anggota->no_ktp, $tahun, $bulan);
        
        // 5. Ambil data pinjaman berdasarkan periode yang dipilih
        $biasa = $this->getJmlBiasa($anggota->no_ktp, $tahun, $bulan);
        $bank = $this->getJmlBank($anggota->no_ktp, $tahun, $bulan);
        $barang = $this->getJmlBarang($anggota->no_ktp, $tahun, $bulan);
        
        // Debug: Log data pinjaman (akan diisi setelah perhitungan)
        
        // 6. Ambil data toserda dan lain-lain
        $toserda = $this->getJmlToserda($anggota->no_ktp);
        $lainLain = $this->getJmlLainLain($anggota->no_ktp);
        
        // 7. Data simpanan list untuk dashboard
        $simpananList = $this->formatSimpananList($jmlTagihanSimpanan);
        
        // 8. Pengajuan pinjaman terbaru
        $pengajuanPinjaman = \App\Models\data_pengajuan::where('anggota_id', $anggota->id)
            ->orderBy('tgl_input', 'desc')
            ->first();
        
        // 9. Data ringkasan tagihan berdasarkan logika project CI lama dengan periode yang dipilih
        $jmlSimpans = $this->getJmlSimpans($anggota->no_ktp, $tahun, $bulan);
        $tagihanBulanLaluNew = $this->getTagihanBulanLaluNew($anggota->no_ktp, $tahun, $bulan);
        $potGaji = $this->getBayarSimpanan($anggota->no_ktp, $tahun, $bulan);
        $potSimpanan = $this->getBayarSimpananPot($anggota->no_ktp, $tahun, $bulan);
        
        // 10. Hitung tag harus dibayar berdasarkan logika studi kasus
        // Tag harus dibayar = angsuran bulan ini + tag bulan lalu
        $tagHarusDibayar = $this->hitungTagHarusDibayar($anggota->no_ktp, $tahun, $bulan, $tagihanBulanLaluNew);
        
        // 11. Tagihan bulan lalu (legacy - untuk kompatibilitas)
        $tagihanBulanLalu = $this->getTagihanBulanLalu($anggota->no_ktp, $tahun, $bulan);
        
        // 10. Pengajuan penarikan simpanan terbaru
        $pengajuanPenarikan = \App\Models\data_pengajuan_penarikan::where('anggota_id', $anggota->id)
            ->orderBy('tgl_input', 'desc')
            ->first();
        
        // 11. Format data untuk view
        $tagihanData = [
            'simpanan_pokok' => $this->getSimpananByJenis($jmlTagihanSimpanan, 40),
            'simpanan_wajib' => $this->getSimpananByJenis($jmlTagihanSimpanan, 41),
            'simpanan_sukarela' => $this->getSimpananByJenis($jmlTagihanSimpanan, 32),
            'simpanan_khusus_1' => $this->getSimpananByJenis($jmlTagihanSimpanan, 51),
            'simpanan_khusus_2' => $this->getSimpananByJenis($jmlTagihanSimpanan, 52),
            'tab_perumahan' => $this->getSimpananByJenis($jmlTagihanSimpanan, 156),
            'pinjaman_biasa' => $biasa,
            'pinjaman_barang' => $barang,
            'pinjaman_bank' => $bank,
            'toserda' => $toserda,
            'lain_lain' => $lainLain
        ];
        
        return view('member.dashboard', compact(
            'anggota', 
            'saldoSimpanan', 
            'tagihanKredit', 
            'keteranganPinjaman',
            'simpananList',
            'pengajuanPinjaman',
            'tagihanBulanLalu',
            'pengajuanPenarikan',
            'tagihanData',
            'periode',
            'jmlSimpans',
            'tagihanBulanLaluNew',
            'potGaji',
            'potSimpanan',
            'tagHarusDibayar'
        ));
    }

    /**
     * Hitung saldo simpanan (konsisten dengan project lama)
     */
    private function hitungSaldoSimpanan($noKtp)
    {
        return \Illuminate\Support\Facades\DB::table('tbl_trans_sp')
            ->selectRaw('
                SUM(CASE WHEN jenis_id = 41 AND dk = "D" THEN jumlah ELSE 0 END) - 
                SUM(CASE WHEN jenis_id = 41 AND dk = "K" THEN jumlah ELSE 0 END) as simpanan_wajib,
                
                SUM(CASE WHEN jenis_id = 32 AND dk = "D" THEN jumlah ELSE 0 END) - 
                SUM(CASE WHEN jenis_id = 32 AND dk = "K" THEN jumlah ELSE 0 END) as simpanan_sukarela,
                
                SUM(CASE WHEN jenis_id = 52 AND dk = "D" THEN jumlah ELSE 0 END) - 
                SUM(CASE WHEN jenis_id = 52 AND dk = "K" THEN jumlah ELSE 0 END) as simpanan_khusus_2,
                
                SUM(CASE WHEN jenis_id = 40 AND dk = "D" THEN jumlah ELSE 0 END) - 
                SUM(CASE WHEN jenis_id = 40 AND dk = "K" THEN jumlah ELSE 0 END) as simpanan_pokok,
                
                SUM(CASE WHEN jenis_id = 51 AND dk = "D" THEN jumlah ELSE 0 END) - 
                SUM(CASE WHEN jenis_id = 51 AND dk = "K" THEN jumlah ELSE 0 END) as simpanan_khusus_1,
                
                SUM(CASE WHEN jenis_id = 156 AND dk = "D" THEN jumlah ELSE 0 END) - 
                SUM(CASE WHEN jenis_id = 156 AND dk = "K" THEN jumlah ELSE 0 END) as tab_perumahan
            ')
            ->where('no_ktp', $noKtp)
            ->first();
    }

    /**
     * Hitung tagihan kredit berdasarkan tbl_pinjaman_h (head) yang disetujui
     */
    private function hitungTagihanKredit($noKtp)
    {
        // Menggunakan view v_tagihan_kredit_detil untuk logika yang akurat
        $kreditData = \Illuminate\Support\Facades\DB::table('v_tagihan_kredit_detil')
            ->where('no_ktp', $noKtp)
            ->get();

        // Hitung total per jenis pinjaman
        $pinjamanBiasa = $kreditData->where('jenis_pinjaman', '1')->sum('total_pinjaman');
        $sisaPinjamanBiasa = $kreditData->where('jenis_pinjaman', '1')->sum('sisa_pinjaman');
        
        $pinjamanBarang = $kreditData->where('jenis_pinjaman', '3')->sum('total_pinjaman');
        $sisaPinjamanBarang = $kreditData->where('jenis_pinjaman', '3')->sum('sisa_pinjaman');
        
        $pinjamanBank = $kreditData->where('jenis_pinjaman', '2')->sum('total_pinjaman');
        $sisaPinjamanBank = $kreditData->where('jenis_pinjaman', '2')->sum('sisa_pinjaman');
        
        // Hitung tagihan tak terbayar (angsuran yang sudah jatuh tempo tapi belum dibayar)
        $tagihanTakTerbayar = $kreditData->sum('tagihan_tak_terbayar');

        return (object) [
            'pinjaman_biasa' => $pinjamanBiasa,
            'sisa_pinjaman_biasa' => max(0, $sisaPinjamanBiasa),
            'pinjaman_barang' => $pinjamanBarang,
            'sisa_pinjaman_barang' => max(0, $sisaPinjamanBarang),
            'pinjaman_bank' => $pinjamanBank,
            'sisa_pinjaman_bank' => max(0, $sisaPinjamanBank),
            'tagihan_takterbayar' => $tagihanTakTerbayar
        ];
    }

    /**
     * Get jumlah tagihan simpanan seperti project CI lama
     * Menggabungkan data yang sama per jenis simpanan dan menampilkan placeholder 0 jika tidak ada data
     */
    private function getJmlTagihanSimpanan($noKtp, $tahun = null, $bulan = null)
    {
        $tahun = $tahun ?? date('Y');
        $bulan = $bulan ?? date('m');
        $id = [40, 32, 41, 52]; // Pokok, Sukarela, Wajib, Khusus2
        
        // Cek dulu di tbl_trans_tagihan dengan GROUP BY untuk menggabungkan data yang sama
        $tagihanData = \Illuminate\Support\Facades\DB::table('tbl_trans_tagihan as a')
            ->join('jns_simpan as b', 'a.jenis_id', '=', 'b.id')
            ->select('a.jenis_id', 'b.jns_simpan', DB::raw('SUM(a.jumlah) as jumlah'))
            ->whereYear('a.tgl_transaksi', $tahun)
            ->whereMonth('a.tgl_transaksi', $bulan)
            ->whereIn('a.jenis_id', $id)
            ->where('a.no_ktp', $noKtp)
            ->groupBy('a.jenis_id', 'b.jns_simpan')
            ->get();
        
        // Jika tidak ada data di tbl_trans_tagihan, ambil dari tbl_trans_sp dengan GROUP BY
        if ($tagihanData->isEmpty()) {
            $tagihanData = \Illuminate\Support\Facades\DB::table('tbl_trans_sp as a')
                ->join('jns_simpan as b', 'a.jenis_id', '=', 'b.id')
                ->select('a.jenis_id', 'b.jns_simpan', DB::raw('SUM(a.jumlah) as jumlah'))
                ->whereYear('a.tgl_transaksi', $tahun)
                ->whereMonth('a.tgl_transaksi', $bulan)
                ->whereIn('a.jenis_id', $id)
                ->where('a.no_ktp', $noKtp)
                ->where('a.dk', 'D') // Debit untuk setoran
                ->groupBy('a.jenis_id', 'b.jns_simpan')
                ->get();
        }
        
        // Jika masih tidak ada data, buat placeholder dengan nilai 0 untuk semua jenis simpanan
        if ($tagihanData->isEmpty()) {
            $jenisSimpanan = \Illuminate\Support\Facades\DB::table('jns_simpan')
                ->whereIn('id', $id)
                ->get();
            
            $tagihanData = $jenisSimpanan->map(function($jenis) {
                return (object) [
                    'jenis_id' => $jenis->id,
                    'jns_simpan' => $jenis->jns_simpan,
                    'jumlah' => 0
                ];
            });
        }
        
        return $tagihanData;
    }

    /**
     * Get jumlah simpanan untuk data "Jumlah" (Row 1)
     * Berdasarkan logika project CI lama: get_jml_simpans()
     * Menggabungkan data yang sama dan menampilkan 0 jika tidak ada data
     */
    private function getJmlSimpans($noKtp, $tahun = null, $bulan = null)
    {
        $tahun = $tahun ?? date('Y');
        $bulan = $bulan ?? date('m');
        $id = [40, 32, 41, 52]; // Pokok, Sukarela, Wajib, Khusus2
        
        // Cek dulu di tbl_trans_tagihan
        $tagihanTotal = \Illuminate\Support\Facades\DB::table('tbl_trans_tagihan')
            ->selectRaw('SUM(jumlah) as jml_total')
            ->whereIn('jenis_id', $id)
            ->where('no_ktp', $noKtp)
            ->whereYear('tgl_transaksi', $tahun)
            ->whereMonth('tgl_transaksi', $bulan)
            ->first();
        
        // Jika tidak ada data di tbl_trans_tagihan, ambil dari tbl_trans_sp
        if (!$tagihanTotal || $tagihanTotal->jml_total == 0) {
            $tagihanTotal = \Illuminate\Support\Facades\DB::table('tbl_trans_sp')
                ->selectRaw('SUM(jumlah) as jml_total')
                ->whereIn('jenis_id', $id)
                ->where('no_ktp', $noKtp)
                ->where('dk', 'D') // Debit untuk setoran
                ->whereYear('tgl_transaksi', $tahun)
                ->whereMonth('tgl_transaksi', $bulan)
                ->first();
        }
        
        // Jika masih tidak ada data, return 0
        if (!$tagihanTotal || $tagihanTotal->jml_total == null) {
            $tagihanTotal = (object) ['jml_total' => 0];
        }
        
        return $tagihanTotal;
    }

    /**
     * Get tagihan bulan lalu untuk data "Tag Bulan Lalu" berdasarkan tempo_pinjaman
     * Logika: Angsuran yang belum dibayar dari tempo_pinjaman bulan-bulan sebelumnya
     */
    private function getTagihanBulanLaluNew($noKtp, $tahun = null, $bulan = null)
    {
        $tahun = $tahun ?? date('Y');
        $bulan = $bulan ?? date('m');
        
        // Ambil data dari view yang sudah dihitung dengan benar
        $tagihanBulanLalu = \Illuminate\Support\Facades\DB::table('v_hitung_pinjaman_detil')
            ->where('no_ktp', $noKtp)
            ->where('lunas', 'Belum') // Hanya yang belum lunas
            ->sum('tag_bulan_lalu');
        
        return $tagihanBulanLalu ?? 0;
    }

    /**
     * Get bayar simpanan untuk data "Pot Gaji" (Row 3)
     * Logika: Jumlah simpanan + angsuran yang sudah dibayar di bulan tersebut
     */
    private function getBayarSimpanan($noKtp, $tahun = null, $bulan = null)
    {
        $tahun = $tahun ?? date('Y');
        $bulan = $bulan ?? date('m');
        
        // 1. Simpanan (jenis_id NOT IN(155,8,125) AND dk='D')
        $simpanan = \Illuminate\Support\Facades\DB::table('tbl_trans_sp')
            ->selectRaw('SUM(jumlah) as jml_total')
            ->where('no_ktp', $noKtp)
            ->whereNotIn('jenis_id', [155, 8, 125])
            ->where('dk', 'D')
            ->whereYear('tgl_transaksi', $tahun)
            ->whereMonth('tgl_transaksi', $bulan)
            ->first();
            
        // 2. Angsuran pinjaman yang sudah dibayar di bulan tersebut
        $angsuranDibayar = \Illuminate\Support\Facades\DB::table('tbl_pinjaman_d as pd')
            ->join('tbl_pinjaman_h as ph', 'pd.pinjam_id', '=', 'ph.id')
            ->selectRaw('SUM(pd.jumlah_bayar) as jml_total')
            ->where('ph.no_ktp', $noKtp)
            ->whereYear('pd.tgl_bayar', $tahun)
            ->whereMonth('pd.tgl_bayar', $bulan)
            ->first();
            
        // 3. Toserda
        $toserda = \Illuminate\Support\Facades\DB::table('tbl_trans_toserda')
            ->selectRaw('SUM(jumlah) as jml_total')
            ->where('no_ktp', $noKtp)
            ->where('dk', 'D')
            ->whereYear('tgl_transaksi', $tahun)
            ->whereMonth('tgl_transaksi', $bulan)
            ->first();
        
        return ($simpanan->jml_total ?? 0) + ($angsuranDibayar->jml_total ?? 0) + ($toserda->jml_total ?? 0);
    }

    /**
     * Hitung tag harus dibayar berdasarkan logika studi kasus
     * Logika: Angsuran bulan ini + tag bulan lalu
     */
    private function hitungTagHarusDibayar($noKtp, $tahun, $bulan, $tagihanBulanLalu)
    {
        // Hitung angsuran yang harus dibayar di bulan ini
        $angsuranBulanIni = 0;
        
        // Ambil semua pinjaman yang aktif (termasuk yang sudah lewat tenor)
        $pinjamanAktif = \Illuminate\Support\Facades\DB::table('tbl_pinjaman_h')
            ->where('no_ktp', $noKtp)
            ->where('status', '1') // Status disetujui
            ->where('lunas', 'Belum') // Belum lunas
            ->get();
        
        foreach ($pinjamanAktif as $pinjaman) {
            // Hitung angsuran per bulan dengan pembulatan yang benar
            $angsuranPerBulan = floor($pinjaman->jumlah / $pinjaman->lama_angsuran);
            $sisaPembulatan = $pinjaman->jumlah - ($angsuranPerBulan * $pinjaman->lama_angsuran);
            
            // Cek apakah ada tempo untuk bulan ini
            $bulanSekarang = sprintf('%02d', $bulan);
            $bulanDepan = sprintf('%02d', $bulan + 1);
            $tahunDepan = $tahun;
            
            // Handle kasus Desember (bulan 12) -> Januari tahun depan
            if ($bulan == 12) {
                $bulanDepan = '01';
                $tahunDepan = $tahun + 1;
            }
            
            $tempoBulanIni = \Illuminate\Support\Facades\DB::table('tempo_pinjaman')
                ->where('no_ktp', $noKtp)
                ->where('pinjam_id', $pinjaman->id)
                ->where('tempo', '>=', $tahun . '-' . $bulanSekarang . '-01')
                ->where('tempo', '<', $tahunDepan . '-' . $bulanDepan . '-01')
                ->first();
            
            // Jika ada tempo untuk bulan ini, tambahkan angsuran
            if ($tempoBulanIni) {
                // Hitung bulan ke berapa dari ajuan
                $bulanAjuan = date('m', strtotime($pinjaman->tgl_pinjam));
                $tahunAjuan = date('Y', strtotime($pinjaman->tgl_pinjam));
                $bulanKe = (($tahun - $tahunAjuan) * 12) + ($bulan - $bulanAjuan) + 1;
                
                // Tentukan angsuran untuk bulan ini (sisa pembulatan masuk ke bulan terakhir)
                $angsuranBulanIniValue = $angsuranPerBulan;
                if ($bulanKe == $pinjaman->lama_angsuran) {
                    $angsuranBulanIniValue += $sisaPembulatan; // Sisa pembulatan masuk ke angsuran terakhir
                }
                
                $angsuranBulanIni += $angsuranBulanIniValue;
            }
        }
        
        // Ambil data dari view yang sudah dihitung dengan benar
        $tagHarusDibayar = \Illuminate\Support\Facades\DB::table('v_hitung_pinjaman_detil')
            ->where('no_ktp', $noKtp)
            ->where('lunas', 'Belum') // Hanya yang belum lunas
            ->sum('tag_harus_dibayar');
        
        return $tagHarusDibayar ?? 0;
    }

    /**
     * Cek apakah pinjaman sudah lewat tenor berdasarkan periode yang dipilih
     */
    private function isPinjamanLewatTenor($pinjaman, $tahun, $bulan)
    {
        $bulanAjuan = date('m', strtotime($pinjaman->tgl_pinjam));
        $tahunAjuan = date('Y', strtotime($pinjaman->tgl_pinjam));
        
        // Hitung bulan ke berapa dari ajuan (1-based)
        $bulanKe = (($tahun - $tahunAjuan) * 12) + ($bulan - $bulanAjuan) + 1;
        
        // Debug: Log untuk melihat perhitungan
        // \Log::info("Pinjaman ID: {$pinjaman->id}, Bulan Ajuan: {$bulanAjuan}/{$tahunAjuan}, Periode: {$bulan}/{$tahun}, Bulan Ke: {$bulanKe}, Lama Angsuran: {$pinjaman->lama_angsuran}");
        
        // Jika bulan ke > lama angsuran, berarti sudah lewat tenor
        return $bulanKe > $pinjaman->lama_angsuran;
    }

    /**
     * Helper method untuk mendapatkan anggota yang sedang login
     */
    private function getAuthenticatedMember()
    {
        $member = auth()->guard('member')->user();
        if (!$member) {
            abort(404);
        }
        return $member;
    }

    /**
     * Method untuk test hitungTagihanKredit (public)
     */
    public function testHitungTagihanKredit($noKtp)
    {
        return $this->hitungTagihanKredit($noKtp);
    }

    /**
     * Debug method untuk melihat data pinjaman
     */
    public function debugPinjaman($noKtp, $tahun, $bulan)
    {
        $result = [];
        
        // Debug Pinjaman Barang (yang ada di studi kasus)
        $pinjamanBarang = \Illuminate\Support\Facades\DB::table('tbl_pinjaman_h')
            ->where('no_ktp', $noKtp)
            ->where('jenis_pinjaman', '3') // Pinjaman Barang
            ->where('status', '1') // Status disetujui
            ->first();
        
        if ($pinjamanBarang) {
            $bulanAjuan = date('m', strtotime($pinjamanBarang->tgl_pinjam));
            $tahunAjuan = date('Y', strtotime($pinjamanBarang->tgl_pinjam));
            $bulanKe = (($tahun - $tahunAjuan) * 12) + ($bulan - $bulanAjuan) + 1;
            
            // Hitung angsuran per bulan dengan pembulatan yang benar
            $angsuranPerBulan = floor($pinjamanBarang->jumlah / $pinjamanBarang->lama_angsuran);
            $sisaPembulatan = $pinjamanBarang->jumlah - ($angsuranPerBulan * $pinjamanBarang->lama_angsuran);
            
            // Hitung tracker berdasarkan logika baru
            if ($bulanKe <= 1) {
                $tracker = 0;
            } else if ($bulanKe > $pinjamanBarang->lama_angsuran) {
                $tracker = $pinjamanBarang->lama_angsuran;
            } else {
                $tracker = $bulanKe - 1;
            }
            
            // Hitung Tag Bulan Lalu
            $tagBulanLalu = $this->getTagihanBulanLaluNew($noKtp, $tahun, $bulan);
            
            // Hitung Tag Harus Dibayar
            $tagHarusDibayar = $this->hitungTagHarusDibayar($noKtp, $tahun, $bulan, $tagBulanLalu);
            
            // Debug: Cek data tempo_pinjaman
            $tempoData = \Illuminate\Support\Facades\DB::table('tempo_pinjaman')
                ->where('no_ktp', $noKtp)
                ->where('pinjam_id', $pinjamanBarang->id)
                ->orderBy('tempo', 'asc')
                ->get();
            
            $result['pinjaman_barang'] = [
                'pinjaman_id' => $pinjamanBarang->id,
                'tgl_pinjam' => $pinjamanBarang->tgl_pinjam,
                'bulan_ajuan' => "{$bulanAjuan}/{$tahunAjuan}",
                'periode' => "{$bulan}/{$tahun}",
                'bulan_ke' => $bulanKe,
                'lama_angsuran' => $pinjamanBarang->lama_angsuran,
                'tracker' => $tracker,
                'lunas' => $pinjamanBarang->lunas,
                'jumlah' => $pinjamanBarang->jumlah,
                'angsuran_per_bulan' => $angsuranPerBulan,
                'sisa_pembulatan' => $sisaPembulatan,
                'tag_bulan_lalu' => $tagBulanLalu,
                'tag_harus_dibayar' => $tagHarusDibayar,
                'tempo_data' => $tempoData
            ];
        } else {
            $result['pinjaman_barang'] = "Tidak ada pinjaman barang";
        }
        
        return $result;
    }


    /**
     * Get bayar simpanan pot untuk data "Pot Simpanan" (Row 4)
     * Berdasarkan logika project CI lama: get_bayar_simpanan_pot()
     */
    private function getBayarSimpananPot($noKtp, $tahun = null, $bulan = null)
    {
        $tahun = $tahun ?? date('Y');
        $bulan = $bulan ?? date('m');
        
        return \Illuminate\Support\Facades\DB::table('tbl_trans_sp')
            ->selectRaw('SUM(jumlah) as jml_total')
            ->where('no_ktp', $noKtp)
            ->whereNotIn('jenis_id', [8, 125])
            ->where('jumlah', '<', 0) // Nilai negatif
            ->whereYear('tgl_transaksi', $tahun)
            ->whereMonth('tgl_transaksi', $bulan)
            ->first();
    }

    /**
     * Get jumlah pinjaman biasa berdasarkan periode dan logika studi kasus
     */
    private function getJmlBiasa($noKtp, $tahun = null, $bulan = null)
    {
        $tahun = $tahun ?? date('Y');
        $bulan = $bulan ?? date('m');
        
        // Ambil data dari view yang sudah dihitung dengan benar
        $pinjaman = \Illuminate\Support\Facades\DB::table('v_hitung_pinjaman_detil')
            ->where('no_ktp', $noKtp)
            ->where('jenis_pinjaman', '1') // Pinjaman Biasa
            ->where('lunas', 'Belum') // Hanya yang belum lunas
            ->first();
        
        if (!$pinjaman) {
            return (object) [
                'jumlah' => 0,
                'jasa' => 0,
                'lama_angsuran' => 0,
                'angsuran' => 0
            ];
        }
        
        $bulanAjuan = date('m', strtotime($pinjaman->tgl_pinjam));
        $tahunAjuan = date('Y', strtotime($pinjaman->tgl_pinjam));
        
        // Jika bukan bulan ajuan atau setelahnya, return 0
        if ($tahun < $tahunAjuan || ($tahun == $tahunAjuan && $bulan < $bulanAjuan)) {
            return (object) [
                'jumlah' => 0,
                'jasa' => 0,
                'lama_angsuran' => 0,
                'angsuran' => 0
            ];
        }
        
        // Tracker dari view sudah dihitung dengan benar berdasarkan pembayaran aktual
        $tracker = $pinjaman->sudah_bayar;
        
        return (object) [
            'jumlah' => $pinjaman->total_pinjaman,
            'jasa' => $pinjaman->bunga_rp ?? 0,
            'lama_angsuran' => $pinjaman->lama_angsuran,
            'angsuran' => $tracker
        ];
    }

    /**
     * Get jumlah pinjaman barang berdasarkan periode dan logika studi kasus
     */
    private function getJmlBarang($noKtp, $tahun = null, $bulan = null)
    {
        $tahun = $tahun ?? date('Y');
        $bulan = $bulan ?? date('m');
        
        // Ambil data dari view yang sudah dihitung dengan benar
        $pinjaman = \Illuminate\Support\Facades\DB::table('v_hitung_pinjaman_detil')
            ->where('no_ktp', $noKtp)
            ->where('jenis_pinjaman', '3') // Pinjaman Barang
            ->where('lunas', 'Belum') // Hanya yang belum lunas
            ->first();
        
        if (!$pinjaman) {
            return (object) [
                'jumlah' => 0,
                'jasa' => 0,
                'lama_angsuran' => 0,
                'angsuran' => 0
            ];
        }
        
        $bulanAjuan = date('m', strtotime($pinjaman->tgl_pinjam));
        $tahunAjuan = date('Y', strtotime($pinjaman->tgl_pinjam));
        
        // Jika bukan bulan ajuan atau setelahnya, return 0
        if ($tahun < $tahunAjuan || ($tahun == $tahunAjuan && $bulan < $bulanAjuan)) {
            return (object) [
                'jumlah' => 0,
                'jasa' => 0,
                'lama_angsuran' => 0,
                'angsuran' => 0
            ];
        }
        
        // Tracker dari view sudah dihitung dengan benar berdasarkan pembayaran aktual
        $tracker = $pinjaman->sudah_bayar;
        
        return (object) [
            'jumlah' => $pinjaman->total_pinjaman,
            'jasa' => $pinjaman->bunga_rp ?? 0,
            'lama_angsuran' => $pinjaman->lama_angsuran,
            'angsuran' => $tracker
        ];
    }

    /**
     * Get jumlah pinjaman bank berdasarkan periode dan logika studi kasus
     */
    private function getJmlBank($noKtp, $tahun = null, $bulan = null)
    {
        $tahun = $tahun ?? date('Y');
        $bulan = $bulan ?? date('m');
        
        // Ambil data dari view yang sudah dihitung dengan benar
        $pinjaman = \Illuminate\Support\Facades\DB::table('v_hitung_pinjaman_detil')
            ->where('no_ktp', $noKtp)
            ->where('jenis_pinjaman', '2') // Pinjaman Bank BSM
            ->where('lunas', 'Belum') // Hanya yang belum lunas
            ->first();
        
        if (!$pinjaman) {
            return (object) [
                'jumlah' => 0,
                'jasa' => 0,
                'lama_angsuran' => 0,
                'angsuran' => 0
            ];
        }
        
        $bulanAjuan = date('m', strtotime($pinjaman->tgl_pinjam));
        $tahunAjuan = date('Y', strtotime($pinjaman->tgl_pinjam));
        
        // Jika bukan bulan ajuan atau setelahnya, return 0
        if ($tahun < $tahunAjuan || ($tahun == $tahunAjuan && $bulan < $bulanAjuan)) {
            return (object) [
                'jumlah' => 0,
                'jasa' => 0,
                'lama_angsuran' => 0,
                'angsuran' => 0
            ];
        }
        
        // Tracker dari view sudah dihitung dengan benar berdasarkan pembayaran aktual
        $tracker = $pinjaman->sudah_bayar;
        
        return (object) [
            'jumlah' => $pinjaman->total_pinjaman,
            'jasa' => $pinjaman->bunga_rp ?? 0,
            'lama_angsuran' => $pinjaman->lama_angsuran,
            'angsuran' => $tracker
        ];
    }

    /**
     * Get jumlah toserda seperti project CI lama
     */
    private function getJmlToserda($noKtp)
    {
        return \Illuminate\Support\Facades\DB::table('v_lap_toserda')
            ->select('jumlah_bayar')
            ->whereYear('tgl_transaksi', date('Y'))
            ->whereMonth('tgl_transaksi', date('m'))
            ->where('no_ktp', $noKtp)
            ->first();
    }

    /**
     * Get jumlah lain-lain seperti project CI lama
     */
    private function getJmlLainLain($noKtp)
    {
        return \Illuminate\Support\Facades\DB::table('v_lap_lain_lain')
            ->select('jumlah_bayar')
            ->whereYear('tgl_transaksi', date('Y'))
            ->whereMonth('tgl_transaksi', date('m'))
            ->where('no_ktp', $noKtp)
            ->first();
    }

    /**
     * Format simpanan list untuk dashboard
     */
    private function formatSimpananList($jmlTagihanSimpanan)
    {
        $simpananList = [];
        
        foreach ($jmlTagihanSimpanan as $simpanan) {
            $simpananList[] = [
                'nama' => $simpanan->jns_simpan,
                'jumlah' => $simpanan->jumlah
            ];
        }
        
        return $simpananList;
    }

    /**
     * Get simpanan by jenis ID
     */
    private function getSimpananByJenis($jmlTagihanSimpanan, $jenisId)
    {
        foreach ($jmlTagihanSimpanan as $simpanan) {
            if ($simpanan->jenis_id == $jenisId) {
                return $simpanan->jumlah;
            }
        }
        return 0;
    }

    /**
     * Ambil data tagihan simpanan untuk periode tertentu (LEGACY - tidak digunakan lagi)
     */
    private function getTagihanSimpanan($noKtp, $tahun, $bulan)
    {
        // Method ini dipertahankan untuk kompatibilitas dengan kode lama
        // Tapi tidak digunakan lagi di dashboard member
        return [];
    }
    
    /**
     * Ambil data pinjaman untuk jenis tertentu (LEGACY - tidak digunakan lagi)
     */
    private function getPinjamanData($noKtp, $jenisPinjaman, $tahun, $bulan)
    {
        // Method ini dipertahankan untuk kompatibilitas dengan kode lama
        // Tapi tidak digunakan lagi di dashboard member
        return [
            'jumlah' => 0,
            'jasa' => 0,
            'sudah_bayar' => 0,
            'total_angsuran' => 0
        ];
    }
    
    /**
     * Hitung summary tagihan (LEGACY - tidak digunakan lagi)
     */
    private function calculateSummary($data, $noKtp, $tahun, $bulan)
    {
        // Method ini dipertahankan untuk kompatibilitas dengan kode lama
        // Tapi tidak digunakan lagi di dashboard member
        return [
            'jumlah' => 0,
            'tag_bulan_lalu' => 0,
            'pot_gaji' => 0,
            'pot_simpanan' => 0,
            'tag_harus_dibayar' => 0
        ];
    }
    
    /**
     * Hitung tagihan bulan lalu
     */
    private function getTagihanBulanLalu($noKtp, $tahun, $bulan)
    {
        // Hitung bulan lalu
        $bulanLalu = $bulan - 1;
        $tahunLalu = $tahun;
        
        if ($bulanLalu == 0) {
            $bulanLalu = 12;
            $tahunLalu = $tahun - 1;
        }
        
        return \Illuminate\Support\Facades\DB::table('tbl_trans_tagihan')
            ->where('no_ktp', $noKtp)
            ->whereYear('tgl_transaksi', $tahunLalu)
            ->whereMonth('tgl_transaksi', $bulanLalu)
            ->sum('jumlah');
    }
    
    /**
     * Hitung keterangan pinjaman berdasarkan tbl_pinjaman_h yang disetujui dan terlaksana
     */
    private function hitungKeteranganPinjaman($noKtp)
    {
        // Hitung dari tabel tbl_pinjaman_h langsung untuk akurasi
        $allPinjaman = \App\Models\TblPinjamanH::where('no_ktp', $noKtp)
            ->where('status', '1') // Status terlaksana/disetujui
            ->get();

        // Hitung jumlah pinjaman dan pinjaman lunas
        $jumlahPinjaman = $allPinjaman->where('lunas', 'Belum')->count();
        $pinjamanLunas = $allPinjaman->where('lunas', 'Lunas')->count();
        
        // Ambil data pinjaman aktif untuk status pembayaran
        $kreditData = \Illuminate\Support\Facades\DB::table('v_tagihan_kredit_detil')
            ->where('no_ktp', $noKtp)
            ->get();
        
        // Hitung status pembayaran berdasarkan keterlambatan
        $statusPembayaran = 'Lancar';
        $tanggalTempo = '-';
        
        if ($jumlahPinjaman > 0) {
            // Cek apakah ada pinjaman yang macet (ada tagihan tak terbayar)
            $adaTagihanTakTerbayar = $kreditData->where('tagihan_tak_terbayar', '>', 0)->count() > 0;
            
            if ($adaTagihanTakTerbayar) {
                $statusPembayaran = 'Macet';
            }
            
            // Ambil tanggal tempo terdekat dari data yang ada
            $tempoTerdekat = $kreditData->where('tempo_bulan_depan', '>=', date('Y-m-d'))
                ->sortBy('tempo_bulan_depan')
                ->first();
            
            if ($tempoTerdekat) {
                $tanggalTempo = date('d/m/Y', strtotime($tempoTerdekat->tempo_bulan_depan));
            } else {
                // Jika tidak ada tempo yang akan datang, ambil tempo terakhir
                $tempoTerakhir = $kreditData->sortByDesc('tempo_bulan_depan')->first();
                if ($tempoTerakhir) {
                    $tanggalTempo = date('d/m/Y', strtotime($tempoTerakhir->tempo_bulan_depan));
                }
            }
        }
        
        return (object) [
            'jumlah_pinjaman' => $jumlahPinjaman,
            'pinjaman_lunas' => $pinjamanLunas,
            'status_pembayaran' => $statusPembayaran,
            'tanggal_tempo' => $tanggalTempo
        ];
    }
    


    /**
     * Get detailed simpanan data for debugging
     */
    public function getDetailSimpanan()
    {
        $anggota = auth()->guard('member')->user();
        
        // Get all jenis simpanan from master data
        $jenisSimpanan = \App\Models\jns_simpan::where('tampil', 'Y')->orderBy('urut', 'asc')->get();
        
        // Get setoran data
        $setoranData = \App\Models\TblTransSp::where('no_ktp', $anggota->no_ktp)
            ->where('akun', 'Setoran')
            ->where('dk', 'D')
            ->select('jenis_id', \Illuminate\Support\Facades\DB::raw('SUM(jumlah) as total'))
            ->groupBy('jenis_id')
            ->pluck('total', 'jenis_id')
            ->toArray();
        
        // Get penarikan data
        $penarikanData = \App\Models\TblTransSp::where('no_ktp', $anggota->no_ktp)
            ->where('akun', 'Penarikan')
            ->where('dk', 'K')
            ->select('jenis_id', \Illuminate\Support\Facades\DB::raw('SUM(jumlah) as total'))
            ->groupBy('jenis_id')
            ->pluck('total', 'jenis_id')
            ->toArray();
        
        // Get all transactions for debugging
        $allTransactions = \App\Models\TblTransSp::where('no_ktp', $anggota->no_ktp)
            ->orderBy('tgl_transaksi', 'desc')
            ->get();
        
        $detailData = [];
        foreach ($jenisSimpanan as $jenis) {
            $setoran = $setoranData[$jenis->id] ?? 0;
            $penarikan = $penarikanData[$jenis->id] ?? 0;
            $saldo = $setoran - $penarikan;
            
            $detailData[] = [
                'jenis_id' => $jenis->id,
                'nama' => $jenis->jns_simpan,
                'setoran' => $setoran,
                'penarikan' => $penarikan,
                'saldo' => $saldo
            ];
        }
        
        return response()->json([
            'anggota' => [
                'id' => $anggota->id,
                'no_ktp' => $anggota->no_ktp,
                'nama' => $anggota->nama
            ],
            'detail_simpanan' => $detailData,
            'all_transactions' => $allTransactions->take(10), // Last 10 transactions
            'total_setoran' => array_sum($setoranData),
            'total_penarikan' => array_sum($penarikanData),
            'total_saldo' => array_sum($setoranData) - array_sum($penarikanData)
        ]);
    }
    
    /**
     * Get color for simpanan type
     */
    private function getSimpananColor($jenisSimpanan)
    {
        return match($jenisSimpanan) {
            'Simpanan Wajib' => 'blue',
            'Simpanan Sukarela' => 'red', 
            'Simpanan Khusus 2' => 'yellow',
            'Simpanan Pokok' => 'purple',
            default => 'gray'
        };
    }

    /**
     * Get status text for pengajuan
     */
    private function getStatusText($status)
    {
        return match($status) {
            0 => 'Menunggu Konfirmasi',
            1 => 'Disetujui',
            2 => 'Ditolak',
            3 => 'Sudah Terlaksana',
            4 => 'Batal',
            default => 'Unknown'
        };
    }

    /**
     * Get status badge color for pengajuan
     */
    private function getStatusBadge($status)
    {
        return match($status) {
            0 => 'bg-yellow-100 text-yellow-800 border-yellow-300',
            1 => 'bg-green-100 text-green-800 border-green-300',
            2 => 'bg-red-100 text-red-800 border-red-300',
            3 => 'bg-blue-100 text-blue-800 border-blue-300',
            4 => 'bg-gray-100 text-gray-800 border-gray-300',
            default => 'bg-gray-100 text-gray-800 border-gray-300'
        };
    }

    /**
     * Get status icon for pengajuan
     */
    private function getStatusIcon($status)
    {
        return match($status) {
            0 => '<i class="fas fa-clock text-yellow-600"></i>',
            1 => '<i class="fas fa-check-circle text-green-600"></i>',
            2 => '<i class="fas fa-times-circle text-red-600"></i>',
            3 => '<i class="fas fa-rocket text-blue-600"></i>',
            4 => '<i class="fas fa-trash text-gray-600"></i>',
            default => '<i class="fas fa-question-circle text-gray-600"></i>'
        };
    }

    public function logout(Request $request)
    {
        Auth::guard('member')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/');
    }

    public function toserdaPayment(Request $request)
    {
        try {
            $member = auth()->guard('member')->user();
            
            // Get Toserda billing for the member
            $query = \App\Models\billing::where('id_anggota', $member->id)
                ->where('jns_trans', 'toserda')
                ->orderBy('bulan_tahun', 'desc');
                
            // Filter by status if requested - kolom status sudah tidak ada
            // if ($request->has('status') && in_array($request->status, ['Lunas', 'Belum Lunas'])) {
            //     if ($request->status === 'Lunas') {
            //         $query->where('status', 'Y');
            //     } else {
            //         $query->where('status', 'N');
            //     }
            // }
            
            $billings = $query->paginate(10);
            
            // Get transaction history for the member
            $transactions = \App\Models\TblTransToserda::where('no_ktp', $member->no_ktp)
                ->where('dk', 'D')
                ->orderBy('tgl_transaksi', 'desc')
                ->paginate(10);
            
            return view('member.toserda_payment', compact('billings', 'transactions', 'member'));
        } catch (\Exception $e) {
            Log::error('Error in toserdaPayment: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function processToserda(Request $request, $biliing_code)
    {
        try {
            $member = auth()->guard('member')->user();
            
            // Find the billing record
            $billing = \App\Models\billing::where('billing_code', $biliing_code)
                ->where('id_anggota', $member->id)
                ->where('jns_trans', 'toserda')
                ->first();
            
            if (!$billing) {
                return redirect()->back()->with('error', 'Tagihan tidak ditemukan');
            }
            
            // Kolom status sudah tidak ada, mungkin perlu logika lain untuk cek status pembayaran
            // if ($billing->status === 'Y') {
            //     return redirect()->back()->with('error', 'Tagihan ini sudah lunas');
            // }
            
            // Process payment (in a real application, this would integrate with a payment gateway)
            // For now, we'll just mark it as paid - kolom status sudah tidak ada
            // $billing->status = 'Y';
            $billing->updated_at = now();
            $billing->save();
            
            // Record the payment in transaction history
            // This would be expanded in a real application
            
            return redirect()->route('member.toserda.payment')->with('success', 'Pembayaran berhasil diproses');
        } catch (\Exception $e) {
            Log::error('Error in processToserda: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // Additional methods for navbar functionality
    
    public function beranda()
    {
        $member = auth()->guard('member')->user();
        return view('member.beranda', compact('member'));
    }

    public function pengajuanPinjaman(Request $request)
    {
        $member = auth()->guard('member')->user();
        
        $query = data_pengajuan::where('anggota_id', $member->id);
        
        // Filter tanggal
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('tgl_input', [
                $request->date_from . ' 00:00:00',
                $request->date_to . ' 23:59:59'
            ]);
        }
        
        // Filter jenis
        if ($request->filled('jenis')) {
            $query->where('jenis', $request->jenis);
        }
        
        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter pencarian
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('keterangan', 'like', "%{$search}%")
                  ->orWhere('alasan', 'like', "%{$search}%")
                  ->orWhere('ajuan_id', 'like', "%{$search}%");
            });
        }
        
        $dataPengajuan = $query->orderByDesc('tgl_input')->paginate(10)->appends($request->query());
        
        return view('member.pengajuan_pinjaman', compact('member','dataPengajuan'));
    }

    public function createPengajuanPinjaman()
    {
        $member = auth()->guard('member')->user();
        return view('member.form_pengajuan_pinjaman', compact('member'));
    }

    public function storePengajuanPinjaman(StorePengajuanPinjamanRequest $request)
    {
        try {
            $member = auth()->guard('member')->user();

            // Ambil no_ajuan terakhir bulan berjalan
            $last = data_pengajuan::whereYear('tgl_input', now()->year)
                ->whereMonth('tgl_input', now()->month)
                ->max('no_ajuan');
            $nextNoAjuan = $last ? ((int)$last + 1) : 1; // default 1

            // Bentuk ajuan_id: B.YY.MM.XXX
            $yy = now()->format('y');
            $mm = now()->format('m');
            $seq = str_pad((string)$nextNoAjuan, 3, '0', STR_PAD_LEFT);
            $ajuanId = "B.$yy.$mm.$seq";

            $nominalRaw = (string) $request->input('nominal');
            $nominal = (int) str_replace([',', '.'], '', $nominalRaw);
            if (!$nominal) {
                $nominal = (int) $request->input('nominal');
            }

            $pengajuan = new data_pengajuan();
            $pengajuan->no_ajuan = $nextNoAjuan;
            $pengajuan->ajuan_id = $ajuanId;
            $pengajuan->anggota_id = $member->id;
            $pengajuan->tgl_input = now();
            $pengajuan->jenis = $request->input('jenis_pinjaman', '1'); // Ambil dari form, default Biasa
            $pengajuan->nominal = $nominal;
            $pengajuan->lama_ags = (int)$request->input('lama_angsuran');
            $pengajuan->keterangan = $request->input('keterangan');
            $pengajuan->status = 0; // Pending
            $pengajuan->alasan = '';
            // Hindari error SQL_MODE NO_ZERO_DATE, gunakan NULL jika ada
            $pengajuan->tgl_cair = date('Y-m-d', strtotime('1970-01-01'));
            $pengajuan->tgl_update = now();
            $pengajuan->id_cabang = 1; // Default cabang ID
            $pengajuan->save();

            Log::info('Pengajuan pinjaman berhasil disimpan', [
                'member_id' => $member->id,
                'ajuan_id' => $pengajuan->ajuan_id,
                'no_ajuan' => $pengajuan->no_ajuan,
                'nominal' => $pengajuan->nominal,
            ]);

            return redirect()->route('member.pengajuan.pinjaman')
                ->with('success', 'Pengajuan pinjaman berhasil dikirim');
        } catch (\Throwable $e) {
            Log::error('Gagal menyimpan pengajuan pinjaman', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload' => $request->all(),
                'member_id' => optional(auth()->guard('member')->user())->id,
            ]);

            return redirect()->back()->withInput()
                ->with('error', 'Pengajuan gagal disimpan: '.$e->getMessage());
        }
    }

    public function showPengajuan(string $id)
    {
        $member = auth()->guard('member')->user();
        $pengajuan = data_pengajuan::where('anggota_id', $member->id)->findOrFail($id);
        return view('member.detail_pengajuan_pinjaman', compact('member','pengajuan'));
    }

    public function cancelPengajuan(string $id)
    {
        try {
            $member = auth()->guard('member')->user();
            $pengajuan = data_pengajuan::where('anggota_id', $member->id)->findOrFail($id);

            if ((int)$pengajuan->status !== 0) {
                return redirect()->back()->with('error', 'Pengajuan tidak dapat dibatalkan.');
            }

            $pengajuan->status = 4; // Batal
            $pengajuan->tgl_update = now();
            $pengajuan->save();

            Log::info('Pengajuan dibatalkan oleh member', [
                'member_id' => $member->id,
                'ajuan_id' => $pengajuan->ajuan_id,
            ]);

            return redirect()->route('member.pengajuan.pinjaman')->with('success', 'Pengajuan berhasil dibatalkan');
        } catch (\Throwable $e) {
            Log::error('Gagal membatalkan pengajuan', [ 'error' => $e->getMessage(), 'id' => $id ]);
            return redirect()->back()->with('error', 'Gagal membatalkan pengajuan: '.$e->getMessage());
        }
    }

    public function cetakPengajuan(string $id)
    {
        $member = auth()->guard('member')->user();
        $pengajuan = data_pengajuan::where('anggota_id', $member->id)->findOrFail($id);
        return view('member.cetak_pengajuan_pinjaman', compact('member','pengajuan'));
    }

    public function pengajuanPenarikan()
    {
        $member = auth()->guard('member')->user();
        $jenisSimpanan = \App\Models\jns_simpan::where('tampil', 'Y')->get();
        
        // Get pengajuan data for this member
        $dataPengajuan = \App\Models\data_pengajuan_penarikan::where('anggota_id', $member->id)
            ->orderByDesc('tgl_input')
            ->paginate(10);
            
        return view('member.pengajuan_penarikan', compact('member', 'jenisSimpanan', 'dataPengajuan'));
    }

    public function formPengajuanPenarikan()
    {
        $member = auth()->guard('member')->user();
        $jenisSimpanan = \App\Models\jns_simpan::where('tampil', 'Y')->get();
        
        return view('member.form_pengajuan_penarikan', compact('member', 'jenisSimpanan'));
    }

    public function storePengajuanPenarikan(Request $request)
    {
        try {
        $request->validate([
                'jenis_simpanan' => 'required|exists:jns_simpan,id',
                'nominal' => 'required|string|min:1',
                'keterangan' => 'required|string|max:500'
            ]);
            
            // Log activity start
            ActivityLogService::logPending(
                'create',
                'pengajuan_penarikan',
                'Memulai pengajuan penarikan simpanan',
                null,
                $request->all(),
                null,
                'data_pengajuan_penarikan'
            );
            
            $member = auth()->guard('member')->user();
            
            // Generate no_ajuan
            $last = \App\Models\data_pengajuan_penarikan::whereYear('tgl_input', now()->year)
                ->whereMonth('tgl_input', now()->month)
                ->max('no_ajuan');
            $nextNoAjuan = $last ? ((int)$last + 1) : 1;

            // Generate ajuan_id
            $yy = now()->format('y');
            $mm = now()->format('m');
            $seq = str_pad((string)$nextNoAjuan, 3, '0', STR_PAD_LEFT);
            $ajuanId = "S.B.$yy.$mm.$seq";

            // Create withdrawal application
            $pengajuan = new \App\Models\data_pengajuan_penarikan();
            $pengajuan->no_ajuan = $nextNoAjuan;
            $pengajuan->ajuan_id = $ajuanId;
            $pengajuan->anggota_id = $member->id;
            $pengajuan->tgl_input = now();
            $pengajuan->jenis = $request->jenis_simpanan;
            $pengajuan->nominal = $request->nominal;
            $pengajuan->keterangan = $request->keterangan;
            $pengajuan->status = 0; // Pending
            $pengajuan->alasan = '';
            $pengajuan->tgl_cair = date('Y-m-d', strtotime('1970-01-01'));
            $pengajuan->tgl_update = now();
            $pengajuan->id_cabang = 1; // Default cabang ID
            $pengajuan->save();

            // Log successful creation
            ActivityLogService::logSuccess(
                'create',
                'pengajuan_penarikan',
                "Berhasil membuat pengajuan penarikan simpanan - Ajuan ID: {$ajuanId}, Nominal: Rp " . number_format($request->nominal, 0, ',', '.'),
                null,
                $pengajuan->toArray(),
                $pengajuan->id,
                'data_pengajuan_penarikan'
            );

            return redirect()->route('member.pengajuan.penarikan')
                ->with('success', 'Pengajuan penarikan simpanan berhasil dikirim');
        } catch (\Throwable $e) {
            // Log error
            ActivityLogService::logFailed(
                'create',
                'pengajuan_penarikan',
                'Gagal menyimpan pengajuan penarikan simpanan - Error sistem',
                $e->getMessage(),
                null,
                $request->all(),
                null,
                'data_pengajuan_penarikan'
            );

            return redirect()->back()->withInput()
                ->with('error', 'Pengajuan gagal disimpan: '.$e->getMessage());
        }
    }

    public function cancelPengajuanPenarikan(string $id)
    {
        try {
            $member = auth()->guard('member')->user();
            $pengajuan = \App\Models\data_pengajuan_penarikan::where('anggota_id', $member->id)->findOrFail($id);

            if ((int)$pengajuan->status !== 0) {
                ActivityLogService::logFailed(
                    'cancel',
                    'pengajuan_penarikan',
                    'Gagal membatalkan pengajuan penarikan - Status tidak valid',
                    'Pengajuan tidak dapat dibatalkan karena status bukan pending',
                    $pengajuan->toArray(),
                    null,
                    $pengajuan->id,
                    'data_pengajuan_penarikan'
                );
                
                return redirect()->back()->with('error', 'Pengajuan tidak dapat dibatalkan.');
            }

            // Store old values for logging
            $oldValues = $pengajuan->toArray();

            $pengajuan->status = 4; // Batal
            $pengajuan->tgl_update = now();
            $pengajuan->save();

            // Log successful cancellation
            ActivityLogService::logSuccess(
                'cancel',
                'pengajuan_penarikan',
                "Berhasil membatalkan pengajuan penarikan - Ajuan ID: {$pengajuan->ajuan_id}",
                $oldValues,
                $pengajuan->toArray(),
                $pengajuan->id,
                'data_pengajuan_penarikan'
            );

            return redirect()->route('member.pengajuan.penarikan')->with('success', 'Pengajuan berhasil dibatalkan');
        } catch (\Throwable $e) {
            // Log error
            ActivityLogService::logFailed(
                'cancel',
                'pengajuan_penarikan',
                'Gagal membatalkan pengajuan penarikan - Error sistem',
                $e->getMessage(),
                null,
                null,
                $id,
                'data_pengajuan_penarikan'
            );
            
            return redirect()->back()->with('error', 'Gagal membatalkan pengajuan: '.$e->getMessage());
        }
    }

    public function getSaldoSimpanan(Request $request)
    {
        try {
            $member = auth()->guard('member')->user();
            $jenisId = $request->jenis_id;

            $saldoSimpanan = \App\Models\TblTransSp::where('no_ktp', $member->no_ktp)
                ->where('jenis_id', $jenisId)
                ->where('akun', 'Setoran')
                ->where('dk', 'D')
                ->sum('jumlah');
            
            $totalPenarikan = \App\Models\TblTransSp::where('no_ktp', $member->no_ktp)
                ->where('jenis_id', $jenisId)
                ->where('akun', 'Penarikan')
                ->where('dk', 'K')
                ->sum('jumlah');
            
            $saldoTersedia = $saldoSimpanan - $totalPenarikan;

            return response()->json([
                'success' => true,
                'saldo_tersedia' => $saldoTersedia,
                'saldo_formatted' => number_format($saldoTersedia, 0, ',', '.')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
}

    public function showPengajuanPenarikan(string $id)
    {
        try {
            $member = auth()->guard('member')->user();
            $pengajuan = \App\Models\data_pengajuan_penarikan::with(['jenisSimpanan'])
                ->where('anggota_id', $member->id)
                ->findOrFail($id);
            
            return view('member.detail_pengajuan_penarikan', compact('pengajuan', 'member'));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pengajuan tidak ditemukan'
            ], 404);
        }
    }


    public function laporanPinjaman(Request $request)
    {
        $member = auth()->guard('member')->user();
        
        // Get filter parameters
        $tgl_dari = $request->input('tgl_dari', date('Y') . '-01-01');
        $tgl_samp = $request->input('tgl_samp', date('Y') . '-12-31');
        $status_filter = $request->input('status', 'all');
        $jenis_filter = $request->input('jenis', 'all');
        
        // Get comprehensive loan data with proper accounting logic
        $loanData = $this->getMemberLoanData($member->no_ktp, $tgl_dari, $tgl_samp, $status_filter, $jenis_filter);
        
        // Calculate loan statistics
        $statistics = $this->calculateLoanStatistics($member->no_ktp, $tgl_dari, $tgl_samp);
        
        // Get recent loan activities
        $recentLoans = $this->getRecentLoanActivities($member->no_ktp);
        
        // Get loan summary
        $loanSummary = $this->getLoanSummary($member->no_ktp);
        
        return view('member.laporan_pinjaman', compact(
            'member',
            'loanData',
            'statistics',
            'recentLoans',
            'loanSummary',
            'tgl_dari',
            'tgl_samp',
            'status_filter',
            'jenis_filter'
        ));
    }

    /**
     * Get loan detail for modal
     */
    public function getLoanDetail($id)
    {
        $member = auth()->guard('member')->user();
        
        $loan = \App\Models\TblPinjamanH::where('id', $id)
            ->where('no_ktp', $member->no_ktp)
            ->first();
        
        if (!$loan) {
            return response()->json(['error' => 'Pinjaman tidak ditemukan'], 404);
        }
        
        // Get installment data
        $angsuran = \App\Models\TblPinjamanD::where('pinjam_id', $loan->id)
            ->orderBy('tgl_bayar', 'asc')
            ->get();
        
        $jml_bayar = $angsuran->sum('jumlah_bayar');
        $jml_denda = $angsuran->sum('denda_rp');
        $jml_adm = $angsuran->sum('biaya_adm');
        $jml_bunga = $angsuran->sum('bunga');
        
        $total_tagihan_loan = $loan->jumlah + ($loan->bunga_rp ?? 0) + ($loan->biaya_adm ?? 0);
        $sisa_tagihan = $total_tagihan_loan - $jml_bayar;
        
        $html = view('member.partials.loan_detail', compact('loan', 'angsuran', 'jml_bayar', 'jml_denda', 'jml_adm', 'jml_bunga', 'total_tagihan_loan', 'sisa_tagihan'))->render();
        
        return response()->json(['html' => $html]);
    }
    
    /**
     * Export loan report to PDF
     */
    public function exportLoanReportPdf(Request $request)
    {
        $member = auth()->guard('member')->user();
        
        $tgl_dari = $request->input('tgl_dari', date('Y') . '-01-01');
        $tgl_samp = $request->input('tgl_samp', date('Y') . '-12-31');
        $status_filter = $request->input('status', 'all');
        $jenis_filter = $request->input('jenis', 'all');
        
        $loanData = $this->getMemberLoanData($member->no_ktp, $tgl_dari, $tgl_samp, $status_filter, $jenis_filter);
        $statistics = $this->calculateLoanStatistics($member->no_ktp, $tgl_dari, $tgl_samp);
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('member.exports.loan_report_pdf', [
            'member' => $member,
            'loanData' => $loanData,
            'statistics' => $statistics,
            'tgl_dari' => $tgl_dari,
            'tgl_samp' => $tgl_samp
        ]);
        
        $pdf->setPaper('A4', 'landscape');
        
        return $pdf->download('laporan_pinjaman_' . $member->no_ktp . '_' . date('Y-m-d') . '.pdf');
    }
    
    /**
     * Export loan report to Excel
     */
    public function exportLoanReportExcel(Request $request)
    {
        $member = auth()->guard('member')->user();
        
        $tgl_dari = $request->input('tgl_dari', date('Y') . '-01-01');
        $tgl_samp = $request->input('tgl_samp', date('Y') . '-12-31');
        $status_filter = $request->input('status', 'all');
        $jenis_filter = $request->input('jenis', 'all');
        
        $loanData = $this->getMemberLoanData($member->no_ktp, $tgl_dari, $tgl_samp, $status_filter, $jenis_filter);
        $statistics = $this->calculateLoanStatistics($member->no_ktp, $tgl_dari, $tgl_samp);
        
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\MemberLoanReportExport($loanData, $statistics, $member, $tgl_dari, $tgl_samp),
            'laporan_pinjaman_' . $member->no_ktp . '_' . date('Y-m-d') . '.xlsx'
        );
    }

    public function laporanTransaksi()
    {
        $member = auth()->guard('member')->user();
        $transaksiData = \App\Models\TblTransToserda::where('no_ktp', $member->no_ktp)
            ->orderBy('tgl_transaksi', 'desc')
            ->paginate(15);
        
        return view('member.laporan_transaksi', compact('member', 'transaksiData'));
    }

    public function laporanPembayaran(Request $request)
    {
        $member = auth()->guard('member')->user();
        
        // Get filter parameters
        $tgl_dari = $request->input('tgl_dari', date('Y') . '-01-01');
        $tgl_samp = $request->input('tgl_samp', date('Y') . '-12-31');
        $jenis_filter = $request->input('jenis', 'all');
        
        // Get payment data with proper accounting logic
        $paymentData = $this->getMemberPaymentData($member->no_ktp, $tgl_dari, $tgl_samp, $jenis_filter);
        
        // Calculate payment statistics
        $statistics = $this->calculatePaymentStatistics($member->no_ktp, $tgl_dari, $tgl_samp);
        
        // Get recent payment activities
        $recentPayments = $this->getRecentPaymentActivities($member->no_ktp);
        
        return view('member.laporan_pembayaran', compact(
            'member',
            'paymentData',
            'statistics',
            'recentPayments',
            'tgl_dari',
            'tgl_samp',
            'jenis_filter'
        ));
    }

    private function getMemberPaymentData($noKtp, $tgl_dari, $tgl_samp, $jenis_filter)
    {
        $query = \App\Models\TblPinjamanD::select([
                'tbl_pinjaman_d.*',
                'tbl_pinjaman_h.jenis_pinjaman',
                'tbl_pinjaman_h.tgl_pinjam',
                'tbl_pinjaman_h.jumlah as total_pinjaman',
                'tbl_pinjaman_h.lama_angsuran',
                'tempo_pinjaman.tempo as tgl_tempo'
            ])
            ->join('tbl_pinjaman_h', 'tbl_pinjaman_h.id', '=', 'tbl_pinjaman_d.pinjam_id')
            ->join('tbl_anggota', 'tbl_anggota.id', '=', 'tbl_pinjaman_h.anggota_id')
            ->leftJoin('tempo_pinjaman', function($join) {
                $join->on('tempo_pinjaman.pinjam_id', '=', 'tbl_pinjaman_d.pinjam_id')
                     ->on('tempo_pinjaman.no_urut', '=', 'tbl_pinjaman_d.angsuran_ke');
            })
            ->where('tbl_anggota.no_ktp', $noKtp)
            ->whereNotNull('tbl_pinjaman_d.tgl_bayar')
            ->whereBetween('tbl_pinjaman_d.tgl_bayar', [$tgl_dari, $tgl_samp]);

        // Apply jenis filter
        if ($jenis_filter !== 'all') {
            $query->where('tbl_pinjaman_h.jenis_pinjaman', $jenis_filter);
        }

        $payments = $query->orderBy('tbl_pinjaman_d.tgl_bayar', 'desc')
            ->paginate(15);

        // Add calculated fields
        $payments->getCollection()->transform(function ($payment) {
            $payment->total_bayar = $payment->jumlah_bayar + $payment->bunga + $payment->denda_rp;
            $payment->jenis_pinjaman_text = $payment->jenis_pinjaman == '1' ? 'Pinjaman Biasa' : 'Pinjaman Barang';
            $payment->status_pembayaran = $this->determinePaymentStatus($payment);
            return $payment;
        });

        return $payments;
    }

    private function calculatePaymentStatistics($noKtp, $tgl_dari, $tgl_samp)
    {
        $query = \App\Models\TblPinjamanD::select([
                DB::raw('COUNT(*) as total_pembayaran'),
                DB::raw('SUM(tbl_pinjaman_d.jumlah_bayar) as total_pokok_dibayar'),
                DB::raw('SUM(tbl_pinjaman_d.bunga) as total_bunga_dibayar'),
                DB::raw('SUM(tbl_pinjaman_d.denda_rp) as total_denda_dibayar'),
                DB::raw('SUM(tbl_pinjaman_d.jumlah_bayar + tbl_pinjaman_d.bunga + tbl_pinjaman_d.denda_rp) as total_semua_pembayaran')
            ])
            ->join('tbl_pinjaman_h', 'tbl_pinjaman_h.id', '=', 'tbl_pinjaman_d.pinjam_id')
            ->join('tbl_anggota', 'tbl_anggota.id', '=', 'tbl_pinjaman_h.anggota_id')
            ->where('tbl_anggota.no_ktp', $noKtp)
            ->whereNotNull('tbl_pinjaman_d.tgl_bayar')
            ->whereBetween('tbl_pinjaman_d.tgl_bayar', [$tgl_dari, $tgl_samp]);

        $stats = $query->first();

        // Get payment frequency
        $paymentFrequency = \App\Models\TblPinjamanD::select([
                DB::raw('COUNT(*) as frekuensi'),
                DB::raw('AVG(tbl_pinjaman_d.jumlah_bayar + tbl_pinjaman_d.bunga + tbl_pinjaman_d.denda_rp) as rata_rata_pembayaran')
            ])
            ->join('tbl_pinjaman_h', 'tbl_pinjaman_h.id', '=', 'tbl_pinjaman_d.pinjam_id')
            ->join('tbl_anggota', 'tbl_anggota.id', '=', 'tbl_pinjaman_h.anggota_id')
            ->where('tbl_anggota.no_ktp', $noKtp)
            ->whereNotNull('tbl_pinjaman_d.tgl_bayar')
            ->whereBetween('tbl_pinjaman_d.tgl_bayar', [$tgl_dari, $tgl_samp])
            ->first();

        return [
            'total_pembayaran' => $stats->total_pembayaran ?? 0,
            'total_pokok_dibayar' => $stats->total_pokok_dibayar ?? 0,
            'total_bunga_dibayar' => $stats->total_bunga_dibayar ?? 0,
            'total_denda_dibayar' => $stats->total_denda_dibayar ?? 0,
            'total_semua_pembayaran' => $stats->total_semua_pembayaran ?? 0,
            'frekuensi_pembayaran' => $paymentFrequency->frekuensi ?? 0,
            'rata_rata_pembayaran' => $paymentFrequency->rata_rata_pembayaran ?? 0
        ];
    }

    private function getRecentPaymentActivities($noKtp)
    {
        return \App\Models\TblPinjamanD::select([
                'tbl_pinjaman_d.*',
                'tbl_pinjaman_h.jenis_pinjaman',
                'tbl_pinjaman_h.tgl_pinjam',
                'tempo_pinjaman.tempo as tgl_tempo'
            ])
            ->join('tbl_pinjaman_h', 'tbl_pinjaman_h.id', '=', 'tbl_pinjaman_d.pinjam_id')
            ->join('tbl_anggota', 'tbl_anggota.id', '=', 'tbl_pinjaman_h.anggota_id')
            ->leftJoin('tempo_pinjaman', function($join) {
                $join->on('tempo_pinjaman.pinjam_id', '=', 'tbl_pinjaman_d.pinjam_id')
                     ->on('tempo_pinjaman.no_urut', '=', 'tbl_pinjaman_d.angsuran_ke');
            })
            ->where('tbl_anggota.no_ktp', $noKtp)
            ->whereNotNull('tbl_pinjaman_d.tgl_bayar')
            ->orderBy('tbl_pinjaman_d.tgl_bayar', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($payment) {
                $payment->total_bayar = $payment->jumlah_bayar + $payment->bunga + $payment->denda_rp;
                $payment->jenis_pinjaman_text = $payment->jenis_pinjaman == '1' ? 'Pinjaman Biasa' : 'Pinjaman Barang';
                $payment->status_pembayaran = $this->determinePaymentStatus($payment);
                return $payment;
            });
    }

    private function determinePaymentStatus($payment)
    {
        // Jika ada denda, pasti terlambat
        if ($payment->denda_rp > 0) {
            return 'Terlambat';
        }
        
        // Jika tidak ada tgl_tempo, anggap tepat waktu
        if (empty($payment->tgl_tempo)) {
            return 'Tepat Waktu';
        }
        
        // Bandingkan tanggal bayar dengan tanggal tempo
        $tglBayar = \Carbon\Carbon::parse($payment->tgl_bayar);
        $tglTempo = \Carbon\Carbon::parse($payment->tgl_tempo);
        
        if ($tglBayar->lte($tglTempo)) {
            return 'Tepat Waktu';
        } else {
            return 'Terlambat';
        }
    }

    public function exportPaymentReportPdf(Request $request)
    {
        $member = auth()->guard('member')->user();
        
        // Get filter parameters
        $tgl_dari = $request->input('tgl_dari', date('Y') . '-01-01');
        $tgl_samp = $request->input('tgl_samp', date('Y') . '-12-31');
        $jenis_filter = $request->input('jenis', 'all');
        
        // Get payment data
        $paymentData = $this->getMemberPaymentData($member->no_ktp, $tgl_dari, $tgl_samp, $jenis_filter);
        $statistics = $this->calculatePaymentStatistics($member->no_ktp, $tgl_dari, $tgl_samp);
        
        $pdf = PDF::loadView('member.exports.payment_report_pdf', compact(
            'member', 'paymentData', 'statistics', 'tgl_dari', 'tgl_samp'
        ))->setPaper('a4', 'landscape');
        
        return $pdf->download('laporan_pembayaran_pinjaman_' . $member->no_ktp . '_' . date('Y-m-d') . '.pdf');
    }

    public function exportPaymentReportExcel(Request $request)
    {
        $member = auth()->guard('member')->user();
        
        // Get filter parameters
        $tgl_dari = $request->input('tgl_dari', date('Y') . '-01-01');
        $tgl_samp = $request->input('tgl_samp', date('Y') . '-12-31');
        $jenis_filter = $request->input('jenis', 'all');
        
        // Get payment data
        $paymentData = $this->getMemberPaymentData($member->no_ktp, $tgl_dari, $tgl_samp, $jenis_filter);
        
        return Excel::download(new MemberPaymentReportExport($paymentData->getCollection()), 
            'laporan_pembayaran_pinjaman_' . $member->no_ktp . '_' . date('Y-m-d') . '.xlsx');
    }


    public function ubahPic()
    {
        $member = auth()->guard('member')->user();
        
        // Refresh member data to get latest file_pic
        $member = Member::find($member->id);
        
        return view('member.ubah_pic', compact('member'));
    }

    public function updatePic(Request $request)
    {
        // Validasi sederhana dulu
        if (!$request->hasFile('photo')) {
            return redirect()->route('member.ubah.pic')->with('error', 'Tidak ada file yang dipilih.');
        }

        $file = $request->file('photo');
        
        // Validasi file
        if (!$file->isValid()) {
            return redirect()->route('member.ubah.pic')->with('error', 'File tidak valid.');
        }

        // Validasi tipe file
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
        if (!in_array($file->getMimeType(), $allowedTypes)) {
            return redirect()->route('member.ubah.pic')->with('error', 'Format file tidak didukung. Gunakan JPG, PNG, atau GIF.');
        }

        // Validasi ukuran file (1MB = 1024KB)
        if ($file->getSize() > 1024 * 1024) {
            return redirect()->route('member.ubah.pic')->with('error', 'Ukuran file terlalu besar. Maksimal 1MB.');
        }

        try {
            $member = auth()->guard('member')->user();
            
            // Hapus foto lama jika ada
            if ($member->file_pic && Storage::disk('public')->exists('anggota/' . $member->file_pic)) {
                Storage::disk('public')->delete('anggota/' . $member->file_pic);
            }

            // Upload foto baru
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            
            // Simpan file
            $path = $file->storeAs('anggota', $filename, 'public');
            
            // Resize gambar
            $this->resizeImage(storage_path('app/public/' . $path), 250, 250);
            
            // Update database
            Member::where('id', $member->id)->update(['file_pic' => $filename]);
            
            // Refresh member data
            $member = Member::find($member->id);
            
            return redirect()->route('member.ubah.pic')->with('success', 'Foto profil berhasil diperbarui!');
            
        } catch (\Exception $e) {
            Log::error('Error updating profile picture: ' . $e->getMessage());
            return redirect()->route('member.ubah.pic')->with('error', 'Gagal memperbarui foto: ' . $e->getMessage());
        }
    }

    private function resizeImage($path, $width, $height)
    {
        // Simple image resize using GD library
        $imageInfo = getimagesize($path);
        if (!$imageInfo) {
            return false;
        }
        
        $sourceWidth = $imageInfo[0];
        $sourceHeight = $imageInfo[1];
        $mimeType = $imageInfo['mime'];
        
        // Create source image based on type
        switch ($mimeType) {
            case 'image/jpeg':
                $sourceImage = imagecreatefromjpeg($path);
                break;
            case 'image/png':
                $sourceImage = imagecreatefrompng($path);
                break;
            case 'image/gif':
                $sourceImage = imagecreatefromgif($path);
                break;
            default:
                return false;
        }
        
        // Calculate new dimensions maintaining aspect ratio
        $ratio = min($width / $sourceWidth, $height / $sourceHeight);
        $newWidth = intval($sourceWidth * $ratio);
        $newHeight = intval($sourceHeight * $ratio);
        
        // Create new image
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        
        // Preserve transparency for PNG and GIF
        if ($mimeType == 'image/png' || $mimeType == 'image/gif') {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
            imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
        }
        
        // Resize image
        imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $sourceWidth, $sourceHeight);
        
        // Save resized image
        switch ($mimeType) {
            case 'image/jpeg':
                imagejpeg($newImage, $path, 90);
                break;
            case 'image/png':
                imagepng($newImage, $path);
                break;
            case 'image/gif':
                imagegif($newImage, $path);
                break;
        }
        
        // Clean up memory
        imagedestroy($sourceImage);
        imagedestroy($newImage);
        
        return true;
    }

    public function ubahPassword()
    {
        $member = auth()->guard('member')->user();
        
        return view('member.ubah_password', compact('member'));
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'password_lama' => 'required|string',
            'password_baru' => 'required|string|min:6|confirmed',
            'password_baru_confirmation' => 'required|string|min:6'
        ], [
            'password_lama.required' => 'Password lama harus diisi.',
            'password_baru.required' => 'Password baru harus diisi.',
            'password_baru.min' => 'Password baru minimal 6 karakter.',
            'password_baru.confirmed' => 'Konfirmasi password tidak sama.',
            'password_baru_confirmation.required' => 'Konfirmasi password harus diisi.',
            'password_baru_confirmation.min' => 'Konfirmasi password minimal 6 karakter.'
        ]);

        try {
            $member = auth()->guard('member')->user();
            
            // Verifikasi password lama
            if (!password_verify($request->password_lama, $member->pass_word)) {
                return redirect()->route('member.ubah.password')->with('error', 'Password lama tidak benar.');
            }

            // Pastikan password baru tidak sama dengan password lama
            if (password_verify($request->password_baru, $member->pass_word)) {
                return redirect()->route('member.ubah.password')->with('error', 'Password baru harus berbeda dengan password lama.');
            }

            // Update password baru (langsung ke database untuk menghindari mutator)
            DB::table('tbl_anggota')
                ->where('id', $member->id)
                ->update(['pass_word' => password_hash($request->password_baru, PASSWORD_DEFAULT)]);

            return redirect()->route('member.ubah.password')->with('success', 'Password berhasil diubah.');
            
        } catch (\Exception $e) {
            Log::error('Error updating password: ' . $e->getMessage());
            return redirect()->route('member.ubah.password')->with('error', 'Terjadi kesalahan saat mengubah password.');
        }
    }

    public function editProfile()
    {
        $member = auth()->guard('member')->user();
        $anggota = $this->getAuthenticatedMember();
        
        return view('member.edit_profile', compact('anggota', 'member'));
    }

    public function updateProfile(Request $request)
    {
        $member = auth()->guard('member')->user();
        
        // Validasi data yang boleh diedit
        $request->validate([
            'tmp_lahir' => 'nullable|string|max:100',
            'tgl_lahir' => 'nullable|date',
            'status' => 'nullable|string|max:50',
            'agama' => 'nullable|string|max:50',
            'alamat' => 'nullable|string|max:255',
            'kota' => 'nullable|string|max:100',
            'notelp' => 'nullable|string|max:20',
            'departement' => 'nullable|string|max:100',
            'jabatan_id' => 'nullable|string|max:50',
            'bank' => 'nullable|string|max:50',
            'no_rekening' => 'nullable|string|max:50',
            'nama_pemilik_rekening' => 'nullable|string|max:100',
        ], [
            'tmp_lahir.max' => 'Tempat lahir maksimal 100 karakter.',
            'tgl_lahir.date' => 'Tanggal lahir harus format tanggal yang valid.',
            'status.max' => 'Status maksimal 50 karakter.',
            'agama.max' => 'Agama maksimal 50 karakter.',
            'alamat.max' => 'Alamat maksimal 255 karakter.',
            'kota.max' => 'Kota maksimal 100 karakter.',
            'notelp.max' => 'No. Telepon maksimal 20 karakter.',
            'departement.max' => 'Departemen maksimal 100 karakter.',
            'jabatan_id.max' => 'Jabatan maksimal 50 karakter.',
            'bank.max' => 'Bank maksimal 50 karakter.',
            'no_rekening.max' => 'No. Rekening maksimal 50 karakter.',
            'nama_pemilik_rekening.max' => 'Nama Pemilik Rekening maksimal 100 karakter.',
        ]);

        try {
            // Update hanya field yang diperbolehkan (tidak termasuk nama, no_ktp, jk, dan data simpanan)
            $updateData = [
                'tmp_lahir' => $request->tmp_lahir,
                'tgl_lahir' => $request->tgl_lahir,
                'status' => $request->status,
                'agama' => $request->agama,
                'alamat' => $request->alamat,
                'kota' => $request->kota,
                'notelp' => $request->notelp,
                'departement' => $request->departement,
                'jabatan_id' => $request->jabatan_id,
                'bank' => $request->bank,
                'no_rekening' => $request->no_rekening,
                'nama_pemilik_rekening' => $request->nama_pemilik_rekening,
            ];

            // Hapus nilai null dari array
            $updateData = array_filter($updateData, function($value) {
                return $value !== null && $value !== '';
            });

            // Update data anggota
            Member::where('id', $member->id)->update($updateData);

            // Log activity
            Log::info('Anggota memperbarui profil', [
                'member_id' => $member->id,
                'no_ktp' => $member->no_ktp,
            ]);

            return redirect()->route('member.dashboard')->with('success', 'Data profil berhasil diperbarui.');
            
        } catch (\Exception $e) {
            Log::error('Error updating profile: ' . $e->getMessage());
            return redirect()->route('member.edit.profile')->with('error', 'Terjadi kesalahan saat memperbarui profil: ' . $e->getMessage())->withInput();
        }
    }


    /**
     * Get comprehensive member loan data with proper accounting logic
     * This implements the accounting principle for loan management and credit risk
     */
    private function getMemberLoanData($noKtp, $tgl_dari, $tgl_samp, $status_filter, $jenis_filter)
    {
        $query = \App\Models\TblPinjamanH::with(['anggota', 'detailAngsuran'])
            ->where('no_ktp', $noKtp)
            ->whereDate('tgl_pinjam', '>=', $tgl_dari)
            ->whereDate('tgl_pinjam', '<=', $tgl_samp);
        
        // Apply status filter
        if ($status_filter !== 'all') {
            if ($status_filter === 'lunas') {
                $query->where('lunas', 'Lunas');
            } elseif ($status_filter === 'belum_lunas') {
                $query->where('lunas', 'Belum');
            }
        }
        
        // Apply jenis filter
        if ($jenis_filter !== 'all') {
            $query->where('jns_pinjaman', $jenis_filter);
        }
        
        $pinjaman = $query->orderBy('tgl_pinjam', 'desc')->get();
        
        $result = [];
        foreach ($pinjaman as $row) {
            // Get installment data
            $angsuran = \App\Models\TblPinjamanD::where('pinjam_id', $row->id)->get();
            $jml_bayar = $angsuran->sum('jumlah_bayar');
            $jml_denda = $angsuran->sum('denda_rp');
            $jml_adm = $angsuran->sum('biaya_adm');
            $jml_bunga = $angsuran->sum('bunga');
            
            // Calculate total tagihan (principal + interest + admin fee)
            $total_tagihan_loan = $row->jumlah + ($row->bunga_rp ?? 0) + ($row->biaya_adm ?? 0);
            $sisa_tagihan = $total_tagihan_loan - $jml_bayar;
            
            // Calculate loan status
            $status = $this->determineLoanStatus($row, $angsuran->count(), $sisa_tagihan);
            
            // Calculate progress percentage
            $progress = $total_tagihan_loan > 0 ? ($jml_bayar / $total_tagihan_loan) * 100 : 0;
            
            $result[] = [
                'id' => $row->id,
                'tgl_pinjam' => $row->tgl_pinjam,
                'lama_angsuran' => $row->lama_angsuran,
                'jumlah' => $row->jumlah,
                'bunga_rp' => $row->bunga_rp ?? 0,
                'biaya_adm' => $row->biaya_adm ?? 0,
                'angsuran_per_bulan' => $row->angsuran_per_bulan ?? 0,
                'total_tagihan' => $total_tagihan_loan,
                'tempo' => $row->tempo,
                'lunas' => $row->lunas,
                'keterangan' => $row->keterangan,
                'jns_pinjaman' => $row->jns_pinjaman,
                'jml_bayar' => $jml_bayar,
                'jml_denda' => $jml_denda,
                'jml_adm' => $jml_adm,
                'jml_bunga' => $jml_bunga,
                'sisa_tagihan' => $sisa_tagihan,
                'status' => $status,
                'progress' => round($progress, 2),
                'angsuran_count' => $angsuran->count(),
                'total_angsuran' => $row->lama_angsuran,
                'angsuran_data' => $angsuran
            ];
        }
        
        return $result;
    }
    
    /**
     * Calculate comprehensive loan statistics
     */
    private function calculateLoanStatistics($noKtp, $tgl_dari, $tgl_samp)
    {
        $pinjaman = \App\Models\TblPinjamanH::where('no_ktp', $noKtp)
            ->whereDate('tgl_pinjam', '>=', $tgl_dari)
            ->whereDate('tgl_pinjam', '<=', $tgl_samp)
            ->get();
        
        $total_pinjaman = $pinjaman->sum('jumlah');
        $total_bunga = $pinjaman->sum('bunga_rp');
        $total_adm = $pinjaman->sum('biaya_adm');
        $total_tagihan = $total_pinjaman + $total_bunga + $total_adm;
        
        // Calculate paid amounts
        $total_dibayar = 0;
        $total_denda = 0;
        foreach ($pinjaman as $row) {
            $angsuran = \App\Models\TblPinjamanD::where('pinjam_id', $row->id)->get();
            $total_dibayar += $angsuran->sum('jumlah_bayar');
            $total_denda += $angsuran->sum('denda_rp');
        }
        
        $sisa_tagihan = $total_tagihan - $total_dibayar;
        
        // Count by status
        $lunas_count = $pinjaman->where('lunas', 'Lunas')->count();
        $belum_lunas_count = $pinjaman->where('lunas', 'Belum')->count();
        
        // Calculate average loan amount
        $avg_pinjaman = $pinjaman->count() > 0 ? $total_pinjaman / $pinjaman->count() : 0;
        
        return [
            'total_pinjaman' => $total_pinjaman,
            'total_bunga' => $total_bunga,
            'total_adm' => $total_adm,
            'total_tagihan' => $total_tagihan,
            'total_dibayar' => $total_dibayar,
            'total_denda' => $total_denda,
            'sisa_tagihan' => $sisa_tagihan,
            'lunas_count' => $lunas_count,
            'belum_lunas_count' => $belum_lunas_count,
            'total_count' => $pinjaman->count(),
            'avg_pinjaman' => $avg_pinjaman,
            'payment_progress' => $total_tagihan > 0 ? ($total_dibayar / $total_tagihan) * 100 : 0
        ];
    }
    
    /**
     * Get recent loan activities
     */
    private function getRecentLoanActivities($noKtp)
    {
        // Get recent loan applications
        $pengajuan = \App\Models\data_pengajuan::where('anggota_id', function($query) use ($noKtp) {
            $query->select('id')->from('tbl_anggota')->where('no_ktp', $noKtp);
        })->orderBy('tgl_input', 'desc')->limit(5)->get();
        
        // Get recent loan disbursements
        $pinjaman = \App\Models\TblPinjamanH::where('no_ktp', $noKtp)
            ->orderBy('tgl_pinjam', 'desc')->limit(5)->get();
        
        // Get recent payments
        $pembayaran = \App\Models\TblPinjamanD::whereHas('pinjaman', function($query) use ($noKtp) {
            $query->where('no_ktp', $noKtp);
        })->orderBy('tgl_bayar', 'desc')->limit(5)->get();
        
        return [
            'pengajuan' => $pengajuan,
            'pinjaman' => $pinjaman,
            'pembayaran' => $pembayaran
        ];
    }
    
    /**
     * Get loan summary for dashboard
     */
    private function getLoanSummary($noKtp)
    {
        $pinjaman = \App\Models\TblPinjamanH::where('no_ktp', $noKtp)->get();
        
        // Perbaiki logika: gunakan 'Belum' dan 'Lunas' sesuai dengan database
        $active_loans = $pinjaman->where('lunas', 'Belum');
        $total_active_amount = $active_loans->sum('jumlah');
        $total_active_installments = $active_loans->sum('lama_angsuran');
        
        // Calculate next payment due
        $next_payment = null;
        if ($active_loans->count() > 0) {
            $next_loan = $active_loans->sortBy('tempo')->first();
            $next_payment = [
                'amount' => $next_loan->angsuran_per_bulan ?? 0,
                'due_date' => $next_loan->tempo,
                'loan_id' => $next_loan->id
            ];
        }
        
        return [
            'active_loans_count' => $active_loans->count(),
            'total_active_amount' => $total_active_amount,
            'total_active_installments' => $total_active_installments,
            'next_payment' => $next_payment,
            'total_loans_count' => $pinjaman->count(),
            'completed_loans_count' => $pinjaman->where('lunas', 'Lunas')->count()
        ];
    }
    
    /**
     * Determine loan status based on payment progress
     */
    private function determineLoanStatus($loan, $angsuran_count, $sisa_tagihan)
    {
        if ($loan->lunas === 'Lunas') {
            return 'Lunas';
        }
        
        if ($sisa_tagihan <= 0) {
            return 'Lunas';
        }
        
        $progress = $angsuran_count / $loan->lama_angsuran;
        
        if ($progress >= 1) {
            return 'Lunas';
        } elseif ($progress >= 0.8) {
            return 'Hampir Lunas';
        } elseif ($progress >= 0.5) {
            return 'Sedang Berjalan';
        } elseif ($progress > 0) {
            return 'Baru Dimulai';
        } else {
            return 'Belum Bayar';
        }
    }

    public function hitungSimulasi(Request $request)
    {
        try {
            $request->validate([
                'nominal' => 'required|numeric|min:1000',
                'lama_angsuran' => 'required|numeric|min:1|max:60',
                'jenis_pinjaman' => 'required'
            ]);

            $nominal = (float) $request->nominal;
            $lama_angsuran = (int) $request->lama_angsuran;
            
            // Set bunga dan biaya admin ke 0 sesuai permintaan
            $bunga = 0;
            $biaya_admin = 0;

            // Hitung angsuran pokok (nominal dibagi jumlah bulan)
            $angsuran_pokok = $nominal / $lama_angsuran;
            
            // Biaya bunga dan admin tetap 0
            $biaya_bunga = 0;
            
            // Jumlah tagihan per bulan
            $jumlah_tagihan = $angsuran_pokok + $biaya_bunga + $biaya_admin;

            $data = [];

            for ($i = 1; $i <= $lama_angsuran; $i++) {
                // Hitung tanggal tempo (28 hari setiap bulan)
                $tanggal_tempo = Carbon::now()->addMonths($i)->format('d F Y');

                $data[] = [
                    'angsuran_ke' => $i,
                    'tanggal_tempo' => $tanggal_tempo,
                    'angsuran_pokok' => number_format($angsuran_pokok, 0, ',', '.'),
                    'biaya_bunga' => number_format($biaya_bunga, 0, ',', '.'),
                    'biaya_admin' => number_format($biaya_admin, 0, ',', '.'),
                    'jumlah_tagihan' => number_format($jumlah_tagihan, 0, ',', '.'),
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $data,
                'summary' => [
                    'total_pinjaman' => number_format($nominal, 0, ',', '.'),
                    'total_angsuran' => number_format($nominal, 0, ',', '.'),
                    'jumlah_bulan' => $lama_angsuran
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan dalam perhitungan simulasi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Method untuk membersihkan data anggota dari semua tabel terkait
     * Digunakan untuk menghapus data duplikat atau data lama
     */
    public function cleanupMemberData($member_id, $no_ktp = null)
    {
        try {
            DB::beginTransaction();
            
            $total_deleted = 0;
            $deleted_details = [];

            // 1. Hapus data dari tbl_pengajuan (by anggota_id)
            $deleted_pengajuan = DB::table('tbl_pengajuan')
                ->where('anggota_id', $member_id)
                ->delete();
            $total_deleted += $deleted_pengajuan;
            $deleted_details['tbl_pengajuan'] = $deleted_pengajuan;

            // 2. Hapus data dari tbl_trans_sp (by anggota_id)
            $deleted_trans_sp_id = DB::table('tbl_trans_sp')
                ->where('anggota_id', $member_id)
                ->delete();
            $total_deleted += $deleted_trans_sp_id;
            $deleted_details['tbl_trans_sp_by_id'] = $deleted_trans_sp_id;

            // 3. Hapus data dari tbl_trans_sp (by no_ktp jika ada)
            if ($no_ktp) {
                $deleted_trans_sp_ktp = DB::table('tbl_trans_sp')
                    ->where('no_ktp', $no_ktp)
                    ->delete();
                $total_deleted += $deleted_trans_sp_ktp;
                $deleted_details['tbl_trans_sp_by_ktp'] = $deleted_trans_sp_ktp;
            }

            // 4. Hapus data dari billing
            $deleted_billing = DB::table('billing')
                ->where('id_anggota', $member_id)
                ->delete();
            $total_deleted += $deleted_billing;
            $deleted_details['billing'] = $deleted_billing;

            // 5. Hapus data dari tbl_anggota (data lama) - hapus SEMUA data dengan no_ktp yang sama
            if ($no_ktp) {
                $deleted_tbl_anggota = DB::table('tbl_anggota')
                    ->where('no_ktp', $no_ktp)
                    ->delete();
                $total_deleted += $deleted_tbl_anggota;
                $deleted_details['tbl_anggota'] = $deleted_tbl_anggota;
            }

            // 6. Hapus data dari data_anggota (data baru) - hanya jika ada
            try {
                $deleted_data_anggota = DB::table('data_anggota')
                    ->where('id', $member_id)
                    ->delete();
                $total_deleted += $deleted_data_anggota;
                $deleted_details['data_anggota'] = $deleted_data_anggota;
            } catch (\Exception $e) {
                // Tabel data_anggota mungkin tidak ada di MySQL
                $deleted_details['data_anggota'] = 0;
            }

            // 7. Hapus data duplikat dari tbl_pengajuan dengan no_ktp yang sama
            if ($no_ktp) {
                // Cari anggota_id lain yang menggunakan no_ktp yang sama
                $duplicate_anggota_ids = DB::table('tbl_anggota')
                    ->where('no_ktp', $no_ktp)
                    ->pluck('id')
                    ->toArray();
                
                if (!empty($duplicate_anggota_ids)) {
                    $deleted_duplicate_pengajuan = DB::table('tbl_pengajuan')
                        ->whereIn('anggota_id', $duplicate_anggota_ids)
                        ->delete();
                    $total_deleted += $deleted_duplicate_pengajuan;
                    $deleted_details['duplicate_pengajuan'] = $deleted_duplicate_pengajuan;
                }
            }

            DB::commit();

            return [
                'success' => true,
                'message' => "Berhasil menghapus $total_deleted records",
                'total_deleted' => $total_deleted,
                'details' => $deleted_details
            ];

        } catch (\Exception $e) {
            DB::rollback();
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'total_deleted' => 0,
                'details' => []
            ];
        }
    }

    /**
     * Method untuk membersihkan data anggota berdasarkan ID
     * Route: /member/cleanup/{id}
     */
    public function cleanupMemberById($id)
    {
        // Ambil data anggota terlebih dahulu
        $member = DB::table('data_anggota')->where('id', $id)->first();
        
        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => 'Data anggota tidak ditemukan'
            ], 404);
        }

        $result = $this->cleanupMemberData($id, $member->no_ktp);
        
        return response()->json($result);
    }

    /**
     * Method untuk cascade delete data anggota dari tbl_anggota
     * Menghapus semua data terkait di seluruh tabel dan view
     * TIDAK menghapus data sistem seperti tbl_user
     */
    public function cascadeDeleteAnggota($anggota_id, $no_ktp = null)
    {
        try {
            DB::beginTransaction();
            
            $total_deleted = 0;
            $deleted_details = [];
            
            // Dapatkan no_ktp jika tidak disediakan
            if (!$no_ktp) {
                $anggota = DB::table('tbl_anggota')->where('id', $anggota_id)->first();
                if (!$anggota) {
                    throw new \Exception('Data anggota tidak ditemukan');
                }
                $no_ktp = $anggota->no_ktp;
            }

            echo "=== CASCADE DELETE UNTUK ANGGOTA ID: $anggota_id, NO_KTP: $no_ktp ===\n";

            // Helper function untuk menghapus data dengan pengecekan kolom
            $deleteFromTable = function($table, $conditions, $description) use (&$total_deleted, &$deleted_details) {
                try {
                    $query = DB::table($table);
                    $deleted = 0;
                    
                    foreach ($conditions as $column => $value) {
                        // Cek apakah kolom ada di tabel
                        $columns = DB::select("DESCRIBE $table");
                        $column_names = array_column($columns, 'Field');
                        
                        if (in_array($column, $column_names)) {
                            $query->where($column, $value);
                        }
                    }
                    
                    $deleted = $query->delete();
                    $total_deleted += $deleted;
                    $deleted_details[$table] = $deleted;
                    echo "✓ $description: $deleted records\n";
                    
                } catch (\Exception $e) {
                    $deleted_details[$table] = 0;
                    echo "- $description: Error - " . $e->getMessage() . "\n";
                }
            };

            // Daftar tabel yang AMAN untuk dihapus (hanya data anggota, bukan data sistem)
            $safe_tables = [
                'tbl_pengajuan' => ['anggota_id' => $anggota_id],
                'tbl_pengajuan_penarikan' => ['anggota_id' => $anggota_id],
                'tbl_pinjaman_h' => ['no_ktp' => $no_ktp],
                'tbl_pinjaman_d' => ['anggota_id' => $anggota_id],
                'tempo_pinjaman' => ['no_ktp' => $no_ktp],
                'tbl_trans_sp' => ['anggota_id' => $anggota_id, 'no_ktp' => $no_ktp],
                'tbl_trans_sps' => ['anggota_id' => $anggota_id, 'no_ktp' => $no_ktp],
                'tbl_trans_tagihan' => ['anggota_id' => $anggota_id, 'no_ktp' => $no_ktp],
                'tbl_trans_tagihans' => ['anggota_id' => $anggota_id, 'no_ktp' => $no_ktp],
                'tbl_trans_toserda' => ['anggota_id' => $anggota_id, 'no_ktp' => $no_ktp],
                'billing' => ['id_anggota' => $anggota_id],
                'tbl_billing_toserda' => ['anggota_id' => $anggota_id, 'no_ktp' => $no_ktp],
                'tbl_billing_processed_toserda' => ['anggota_id' => $anggota_id, 'no_ktp' => $no_ktp],
                'tbl_shu' => ['anggota_id' => $anggota_id, 'no_ktp' => $no_ktp],
                'transaksi_simpanan' => ['anggota_id' => $anggota_id, 'no_ktp' => $no_ktp],
                'transaksi_kas' => ['anggota_id' => $anggota_id, 'no_ktp' => $no_ktp],
                'tbl_trans_angkutan' => ['anggota_id' => $anggota_id, 'no_ktp' => $no_ktp],
                'tbl_trans_kas' => ['anggota_id' => $anggota_id, 'no_ktp' => $no_ktp],
                'activity_logs' => ['anggota_id' => $anggota_id],
                'tbl_anggota' => ['id' => $anggota_id],
                'data_anggota' => ['id' => $anggota_id]
            ];

            echo "=== MENGHAPUS DATA ANGGOTA DARI TABEL YANG AMAN ===\n";

            // Loop melalui semua tabel yang aman untuk dihapus
            foreach ($safe_tables as $table => $conditions) {
                $deleteFromTable($table, $conditions, $table);
            }

            DB::commit();

            echo "\n=== TOTAL DATA YANG DIHAPUS: $total_deleted records ===\n";
            echo "=== CASCADE DELETE SELESAI ===\n";

            return [
                'success' => true,
                'message' => "Berhasil menghapus $total_deleted records dari semua tabel terkait",
                'total_deleted' => $total_deleted,
                'details' => $deleted_details
            ];

        } catch (\Exception $e) {
            DB::rollback();
            echo "Error: " . $e->getMessage() . "\n";
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'total_deleted' => 0,
                'details' => []
            ];
        }
    }

    /**
     * Method untuk cascade delete berdasarkan ID anggota
     * Route: /member/cascade-delete/{id}
     */
    public function cascadeDeleteById($id)
    {
        $result = $this->cascadeDeleteAnggota($id);
        return response()->json($result);
    }
}