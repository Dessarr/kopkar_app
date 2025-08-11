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

    public function memberDashboard()
    {
        $anggota = auth()->guard('member')->user();
        
        // Get all jenis simpanan from master data
        $jenisSimpanan = \App\Models\jns_simpan::where('tampil', 'Y')->orderBy('urut', 'asc')->get();
        
        // Get simpanan data from tbl_trans_sp - grouped by jenis_id
        $simpananData = \App\Models\TblTransSp::where('no_ktp', $anggota->no_ktp)
            ->where('akun', 'Setoran')
            ->where('dk', 'D')
            ->select('jenis_id', \Illuminate\Support\Facades\DB::raw('SUM(jumlah) as total'))
            ->groupBy('jenis_id')
            ->pluck('total', 'jenis_id')
            ->toArray();
        
        // Calculate total simpanan
        $totalSimpanan = array_sum($simpananData);
        
        // Prepare simpanan data for view with specific amounts for each type
        $simpananList = [];
        foreach ($jenisSimpanan as $jenis) {
            $jumlah = $simpananData[$jenis->id] ?? 0;
            $simpananList[] = [
                'nama' => $jenis->jns_simpan,
                'jumlah' => $jumlah,
                'warna' => $this->getSimpananColor($jenis->jns_simpan)
            ];
        }
        
        // Get pinjaman data
        $pinjamanData = \App\Models\TblPinjamanH::where('no_ktp', $anggota->no_ktp)
            ->where('status', 'Aktif')
            ->get();

        $totalPinjaman = $pinjamanData->sum('jumlah_pinjaman');
        $sisaPinjaman = $pinjamanData->sum('sisa_pinjaman');
        $pinjamanLunas = \App\Models\TblPinjamanH::where('no_ktp', $anggota->no_ktp)
            ->where('status', 'Lunas')
            ->count();

        // Get tagihan data
        $tagihanData = \App\Models\billing::where('no_ktp', $anggota->no_ktp)
            ->where('status_bayar', 'Belum Lunas')
            ->get();

        $totalTagihan = $tagihanData->sum('jumlah');
        $tagihanBulanLalu = \App\Models\billing::where('no_ktp', $anggota->no_ktp)
            ->where('status_bayar', 'Belum Lunas')
            ->where('bulan', now()->subMonth()->month)
            ->sum('jumlah');

        // Get transaksi data - remove angkutan query since it doesn't have no_ktp
        $transaksiToserda = \App\Models\TblTransToserda::where('no_ktp', $anggota->no_ktp)
            ->where('dk', 'D')
            ->whereMonth('tgl_transaksi', now()->month)
            ->sum('jumlah');

        // Remove angkutan query since it doesn't have no_ktp column
        $transaksiAngkutan = 0; // Set to 0 since we can't query by no_ktp

        $transaksiLainnya = \App\Models\TblTransToserda::where('no_ktp', $anggota->no_ktp)
            ->where('dk', 'D')
            ->where('jns_trans', '!=', 'Toserda')
            ->whereMonth('tgl_transaksi', now()->month)
            ->sum('jumlah');

        // Prepare data for dashboard cards
        $dashboardData = [
            'anggota' => $anggota,
            'simpananList' => $simpananList,
            'totalSimpanan' => $totalSimpanan,
            'totalPinjaman' => $totalPinjaman,
            'sisaPinjaman' => $sisaPinjaman,
            'pinjamanLunas' => $pinjamanLunas,
            'totalTagihan' => $totalTagihan,
            'tagihanBulanLalu' => $tagihanBulanLalu,
            'transaksiToserda' => $transaksiToserda,
            'transaksiAngkutan' => $transaksiAngkutan,
            'transaksiLainnya' => $transaksiLainnya,
            'pembayaranBulanIni' => $transaksiToserda + $transaksiAngkutan + $transaksiLainnya
        ];
        
        return view('member.dashboard', $dashboardData);
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

    public function pengajuanPinjaman()
    {
        $member = auth()->guard('member')->user();
        $dataPengajuan = data_pengajuan::where('anggota_id', $member->id)
            ->orderByDesc('tgl_input')
            ->paginate(10);
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
        return view('member.pengajuan_penarikan', compact('member', 'jenisSimpanan'));
    }

    public function storePengajuanPenarikan(Request $request)
    {
        $request->validate([
            'jenis_simpanan' => 'required',
            'jumlah_penarikan' => 'required|numeric|min:1000',
            'alasan' => 'required|string|max:500'
        ]);

        try {
            $member = auth()->guard('member')->user();
            
            // Create withdrawal application
            $pengajuan = new \App\Models\data_pengajuan_penarikan();
            $pengajuan->no_ktp = $member->no_ktp;
            $pengajuan->jenis_simpanan = $request->jenis_simpanan;
            $pengajuan->jumlah_penarikan = $request->jumlah_penarikan;
            $pengajuan->alasan = $request->alasan;
            $pengajuan->status = 'Pending';
            $pengajuan->tgl_pengajuan = now();
            $pengajuan->save();

            return redirect()->route('member.pengajuan.penarikan')->with('success', 'Pengajuan penarikan simpanan berhasil dikirim');
        } catch (\Exception $e) {
            Log::error('Error in storePengajuanPenarikan: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
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