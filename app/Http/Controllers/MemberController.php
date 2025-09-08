<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;  
use App\Models\Member;
use App\Models\JnsPinjaman;
use Carbon\Carbon;
use App\Http\Requests\StorePengajuanPinjamanRequest;
use App\Models\data_pengajuan;
use App\Services\ActivityLogService;


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
        $anggota = auth()->guard('member')->user();
        $periode = $request->get('periode', date('Y-m'));
        $tahun = substr($periode, 0, 4);
        $bulan = substr($periode, 5, 2);
        
        // 1. Hitung Saldo Simpanan (konsisten dengan project lama)
        $saldoSimpanan = $this->hitungSaldoSimpanan($anggota->no_ktp);
        

        // 2. Hitung Tagihan Kredit
        $tagihanKredit = $this->hitungTagihanKredit($anggota->no_ktp);
        
        // 3. Hitung Keterangan Pinjaman
        $keteranganPinjaman = $this->hitungKeteranganPinjaman($anggota->no_ktp);
        
        // 4. Ambil data tagihan simpanan untuk periode tertentu
        $tagihanData = $this->getTagihanSimpanan($anggota->no_ktp, $tahun, $bulan);
        
        // 5. Data simpanan list untuk dashboard
        $simpananList = [
            ['nama' => 'Simpanan Pokok', 'jumlah' => $tagihanData['simpanan_pokok']],
            ['nama' => 'Simpanan Wajib', 'jumlah' => $tagihanData['simpanan_wajib']],
            ['nama' => 'Simpanan Sukarela', 'jumlah' => $tagihanData['simpanan_sukarela']],
            ['nama' => 'Simpanan Khusus 1', 'jumlah' => $tagihanData['simpanan_khusus_1']],
            ['nama' => 'Simpanan Khusus 2', 'jumlah' => $tagihanData['simpanan_khusus_2']],
            ['nama' => 'Tabungan Perumahan', 'jumlah' => $tagihanData['tab_perumahan']]
        ];
        
        // 6. Pengajuan pinjaman terbaru
        $pengajuanPinjaman = \App\Models\data_pengajuan::where('anggota_id', $anggota->id)
            ->orderBy('tgl_input', 'desc')
            ->first();
        
        // 7. Tagihan bulan lalu
        $tagihanBulanLalu = $this->getTagihanBulanLalu($anggota->no_ktp, $tahun, $bulan);
        
        // 8. Pengajuan penarikan simpanan terbaru
        $pengajuanPenarikan = \App\Models\data_pengajuan_penarikan::where('anggota_id', $anggota->id)
            ->orderBy('tgl_input', 'desc')
            ->first();
        
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
            'periode'
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
     * Hitung tagihan kredit menggunakan view seperti project CI lama
     */
    private function hitungTagihanKredit($noKtp)
    {
        return \Illuminate\Support\Facades\DB::table('v_hitung_pinjaman')
            ->selectRaw('
                SUM(CASE WHEN jenis_pinjaman = 1 THEN jumlah ELSE 0 END) as pinjaman_biasa,
                SUM(CASE WHEN jenis_pinjaman = 1 AND lunas = "Belum" THEN sisa_pokok ELSE 0 END) as sisa_pinjaman_biasa,
                SUM(CASE WHEN jenis_pinjaman = 2 THEN jumlah ELSE 0 END) as pinjaman_bank,
                SUM(CASE WHEN jenis_pinjaman = 2 AND lunas = "Belum" THEN sisa_pokok ELSE 0 END) as sisa_pinjaman_bank,
                SUM(CASE WHEN jenis_pinjaman = 3 THEN jumlah ELSE 0 END) as pinjaman_barang,
                SUM(CASE WHEN jenis_pinjaman = 3 AND lunas = "Belum" THEN sisa_pokok ELSE 0 END) as sisa_pinjaman_barang
            ')
            ->where('no_ktp', $noKtp)
            ->where('status', '1')
            ->first();
    }

    /**
     * Ambil data tagihan simpanan untuk periode tertentu
     */
    private function getTagihanSimpanan($noKtp, $tahun, $bulan)
    {
        // 1. Simpanan Wajib
        $simpananWajib = \Illuminate\Support\Facades\DB::table('tbl_trans_tagihan')
            ->where('no_ktp', $noKtp)
            ->where('jenis_id', 41)
            ->whereYear('tgl_transaksi', $tahun)
            ->whereMonth('tgl_transaksi', $bulan)
            ->sum('jumlah');
        
        // 2. Simpanan Sukarela
        $simpananSukarela = \Illuminate\Support\Facades\DB::table('tbl_trans_tagihan')
            ->where('no_ktp', $noKtp)
            ->where('jenis_id', 32)
            ->whereYear('tgl_transaksi', $tahun)
            ->whereMonth('tgl_transaksi', $bulan)
            ->sum('jumlah');
        
        // 3. Simpanan Khusus 1
        $simpananKhusus1 = \Illuminate\Support\Facades\DB::table('tbl_trans_tagihan')
            ->where('no_ktp', $noKtp)
            ->where('jenis_id', 51)
            ->whereYear('tgl_transaksi', $tahun)
            ->whereMonth('tgl_transaksi', $bulan)
            ->sum('jumlah');
        
        // 4. Simpanan Khusus 2
        $simpananKhusus2 = \Illuminate\Support\Facades\DB::table('tbl_trans_tagihan')
            ->where('no_ktp', $noKtp)
            ->where('jenis_id', 52)
            ->whereYear('tgl_transaksi', $tahun)
            ->whereMonth('tgl_transaksi', $bulan)
            ->sum('jumlah');
        
        // 5. Simpanan Pokok
        $simpananPokok = \Illuminate\Support\Facades\DB::table('tbl_trans_tagihan')
            ->where('no_ktp', $noKtp)
            ->where('jenis_id', 40)
            ->whereYear('tgl_transaksi', $tahun)
            ->whereMonth('tgl_transaksi', $bulan)
            ->sum('jumlah');
        
        // 6. Tabungan Perumahan
        $tabPerumahan = \Illuminate\Support\Facades\DB::table('tbl_trans_tagihan')
            ->where('no_ktp', $noKtp)
            ->where('jenis_id', 156)
            ->whereYear('tgl_transaksi', $tahun)
            ->whereMonth('tgl_transaksi', $bulan)
            ->sum('jumlah');
        
        // 7. Pinjaman Biasa
        $pinjamanBiasa = $this->getPinjamanData($noKtp, 'Biasa', $tahun, $bulan);
        
        // 8. Pinjaman Barang
        $pinjamanBarang = $this->getPinjamanData($noKtp, 'Barang', $tahun, $bulan);
        
        // 9. Pinjaman Bank BSM
        $pinjamanBank = $this->getPinjamanData($noKtp, 'Bank BSM', $tahun, $bulan);
        
        // 10. Toserda
        $toserda = \Illuminate\Support\Facades\DB::table('tbl_shu')
            ->where('no_ktp', $noKtp)
            ->where('jns_trans', 155)
            ->whereYear('tgl_transaksi', $tahun)
            ->whereMonth('tgl_transaksi', $bulan)
            ->sum('jumlah_bayar');
        
        // 11. Lain-lain
        $lainLain = \Illuminate\Support\Facades\DB::table('tbl_shu')
            ->where('no_ktp', $noKtp)
            ->where('jns_trans', 154)
            ->whereYear('tgl_transaksi', $tahun)
            ->whereMonth('tgl_transaksi', $bulan)
            ->sum('jumlah_bayar');
        
        // 12. Hitung summary
        $summary = $this->calculateSummary([
            'simpanan_wajib' => $simpananWajib,
            'simpanan_sukarela' => $simpananSukarela,
            'simpanan_khusus_1' => $simpananKhusus1,
            'simpanan_khusus_2' => $simpananKhusus2,
            'simpanan_pokok' => $simpananPokok,
            'tab_perumahan' => $tabPerumahan,
            'pinjaman_biasa' => $pinjamanBiasa['jumlah'],
            'jasa_biasa' => $pinjamanBiasa['jasa'],
            'pinjaman_barang' => $pinjamanBarang['jumlah'],
            'jasa_barang' => $pinjamanBarang['jasa'],
            'pinjaman_bank' => $pinjamanBank['jumlah'],
            'jasa_bank' => $pinjamanBank['jasa'],
            'toserda' => $toserda,
            'lain_lain' => $lainLain
        ], $noKtp, $tahun, $bulan);
        
        return [
            'simpanan_wajib' => $simpananWajib,
            'simpanan_sukarela' => $simpananSukarela,
            'simpanan_khusus_1' => $simpananKhusus1,
            'simpanan_khusus_2' => $simpananKhusus2,
            'simpanan_pokok' => $simpananPokok,
            'tab_perumahan' => $tabPerumahan,
            'pinjaman_biasa' => $pinjamanBiasa,
            'pinjaman_barang' => $pinjamanBarang,
            'pinjaman_bank' => $pinjamanBank,
            'toserda' => $toserda,
            'lain_lain' => $lainLain,
            'summary' => $summary
        ];
    }
    
    /**
     * Ambil data pinjaman untuk jenis tertentu
     */
    private function getPinjamanData($noKtp, $jenisPinjaman, $tahun, $bulan)
    {
        $pinjaman = \Illuminate\Support\Facades\DB::table('tbl_pinjaman_h')
            ->where('no_ktp', $noKtp)
            ->where('jenis_pinjaman', $jenisPinjaman)
            ->where('lunas', 'Belum')
            ->first();
        
        if (!$pinjaman) {
            return [
                'jumlah' => 0,
                'jasa' => 0,
                'sudah_bayar' => 0,
                'total_angsuran' => 0
            ];
        }
        
        // Hitung angsuran yang sudah dibayar
        $sudahBayar = \Illuminate\Support\Facades\DB::table('tbl_pinjaman_d')
            ->where('pinjam_id', $pinjaman->id)
            ->where('status_bayar', 'Lunas')
            ->count();
        
        return [
            'jumlah' => $pinjaman->jumlah,
            'jasa' => $pinjaman->bunga_rp,
            'sudah_bayar' => $sudahBayar,
            'total_angsuran' => $pinjaman->lama_angsuran
        ];
    }
    
    /**
     * Hitung summary tagihan
     */
    private function calculateSummary($data, $noKtp, $tahun, $bulan)
    {
        // Hitung jumlah total
        $jumlah = array_sum([
            $data['simpanan_wajib'],
            $data['simpanan_sukarela'],
            $data['simpanan_khusus_1'],
            $data['simpanan_khusus_2'],
            $data['simpanan_pokok'],
            $data['tab_perumahan'],
            $data['pinjaman_biasa'],
            $data['jasa_biasa'],
            $data['pinjaman_barang'],
            $data['jasa_barang'],
            $data['pinjaman_bank'],
            $data['jasa_bank'],
            $data['toserda'],
            $data['lain_lain']
        ]);
        
        // Hitung tagihan bulan lalu
        $bulanLalu = $this->getTagihanBulanLalu($noKtp, $tahun, $bulan);
        
        // Hitung potongan gaji (default 0)
        $potGaji = 0;
        
        // Hitung potongan simpanan (default 0)
        $potSimpanan = 0;
        
        $tagHarusDibayar = $jumlah + $bulanLalu - $potGaji - $potSimpanan;
        
        return [
            'jumlah' => $jumlah,
            'tag_bulan_lalu' => $bulanLalu,
            'pot_gaji' => $potGaji,
            'pot_simpanan' => $potSimpanan,
            'tag_harus_dibayar' => $tagHarusDibayar
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
     * Hitung keterangan pinjaman (konsisten dengan project lama)
     */
    private function hitungKeteranganPinjaman($noKtp)
    {
        $data = \Illuminate\Support\Facades\DB::table('tbl_pinjaman_h as p')
            ->selectRaw('
                COUNT(*) as jumlah_pinjaman,
                SUM(CASE WHEN p.lunas = "Lunas" THEN 1 ELSE 0 END) as pinjaman_lunas
            ')
            ->where('p.no_ktp', $noKtp)
            ->where('p.status', '1')
            ->first();
        
        // Logika sederhana seperti project lama
        $statusPembayaran = 'Lancar';
        $tanggalTempo = '-';
        
        if ($data->jumlah_pinjaman > 0) {
            $pinjamanAktif = \Illuminate\Support\Facades\DB::table('tbl_pinjaman_h')
                ->where('no_ktp', $noKtp)
                ->where('status', '1')
                ->where('lunas', 'Belum')
                ->first();
            
            if ($pinjamanAktif) {
                $bulanTempo = date('m', strtotime($pinjamanAktif->tgl_pinjam));
                $bulanSekarang = date('m');
                
                if ($bulanSekarang > $bulanTempo) {
                    $statusPembayaran = 'Macet';
                }
                
                $tanggalTempo = date('d/m/Y', strtotime($pinjamanAktif->tgl_pinjam));
            }
        }
        
        $data->status_pembayaran = $statusPembayaran;
        $data->tanggal_tempo = $tanggalTempo;
        return $data;
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
            $query = \App\Models\billing::where('no_ktp', $member->no_ktp)
                ->where('jns_trans', 'Toserda')
                ->orderBy('tahun', 'desc')
                ->orderBy('bulan', 'desc');
                
            // Filter by status if requested
            if ($request->has('status') && in_array($request->status, ['Lunas', 'Belum Lunas'])) {
                $query->where('status_bayar', $request->status);
            }
            
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
            $billing = \App\Models\billing::where('biliing_code', $biliing_code)
                ->where('no_ktp', $member->no_ktp)
                ->where('jns_trans', 'Toserda')
                ->first();
            
            if (!$billing) {
                return redirect()->back()->with('error', 'Tagihan tidak ditemukan');
            }
            
            if ($billing->status_bayar === 'Lunas') {
                return redirect()->back()->with('error', 'Tagihan ini sudah lunas');
            }
            
            // Process payment (in a real application, this would integrate with a payment gateway)
            // For now, we'll just mark it as paid
            $billing->status_bayar = 'Lunas';
            $billing->status = 'Y';
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
            $pengajuan->jenis = '1'; // default Biasa
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

    public function laporan()
    {
        $member = auth()->guard('member')->user();
        return view('member.laporan', compact('member'));
    }

    public function laporanSimpanan()
    {
        $member = auth()->guard('member')->user();
        $simpananData = \App\Models\TblTransSp::where('no_ktp', $member->no_ktp)
            ->where('akun', 'Setoran')
            ->where('dk', 'D')
            ->orderBy('tgl_transaksi', 'desc')
            ->paginate(15);
        
        return view('member.laporan_simpanan', compact('member', 'simpananData'));
    }

    public function laporanPinjaman()
    {
        $member = auth()->guard('member')->user();
        $pinjamanData = \App\Models\TblPinjamanH::where('no_ktp', $member->no_ktp)
            ->orderBy('tgl_pinjaman', 'desc')
            ->paginate(15);
        
        return view('member.laporan_pinjaman', compact('member', 'pinjamanData'));
    }

    public function laporanTransaksi()
    {
        $member = auth()->guard('member')->user();
        $transaksiData = \App\Models\TblTransToserda::where('no_ktp', $member->no_ktp)
            ->orderBy('tgl_transaksi', 'desc')
            ->paginate(15);
        
        return view('member.laporan_transaksi', compact('member', 'transaksiData'));
    }

    public function profile()
    {
        $member = auth()->guard('member')->user();
        return view('member.profile', compact('member'));
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'alamat' => 'required|string|max:500',
            'notelp' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255'
        ]);

        try {
            $authMember = auth()->guard('member')->user();
            $member =Member::find($authMember->id);
            $member->nama = $request->input('nama');
            $member->alamat = $request->input('alamat');
            $member->notelp = $request->input('notelp');
            $member->email = $request->input('email');
            $member->save();

            return redirect()->route('member.profile')->with('success', 'Profil berhasil diperbarui');
        } catch (\Exception $e) {
            Log::error('Error in updateProfile: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
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



}