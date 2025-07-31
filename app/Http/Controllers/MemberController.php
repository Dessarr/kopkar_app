<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;  
use App\Models\Member;

class MemberController extends Controller
{
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

        // Debug log
        Log::info('Login attempt for member: ' . $credentials['no_ktp']);

        // Cari member berdasarkan no_ktp
        $member = Member::where('no_ktp', $credentials['no_ktp'])->first();

        if ($member) {
            Log::info('Member found with ID: ' . $member->id);
            
            // Debug password check
            $passwordMatch = Hash::check($credentials['pass_word'], $member->pass_word);
            Log::info('Password match: ' . ($passwordMatch ? 'Yes' : 'No'));

            if ($passwordMatch) {
                Auth::guard('member')->login($member);
                $request->session()->regenerate();
                Log::info('Login successful for member: ' . $member->nama);
                return redirect()->intended('member/dashboard');
            }
        } else {
            Log::info('No member found with name: ' . $credentials['no_ktp']);
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
        $jenisPinjaman = \App\Models\JnsPinjaman::where('aktif', 'Y')->get();
        return view('member.pengajuan_pinjaman', compact('member', 'jenisPinjaman'));
    }

    public function storePengajuanPinjaman(Request $request)
    {
        $request->validate([
            'jenis_pinjaman' => 'required',
            'jumlah_pinjaman' => 'required|numeric|min:1000',
            'keterangan' => 'nullable|string|max:500'
        ]);

        try {
            $member = auth()->guard('member')->user();
            
            // Create loan application
            $pengajuan = new \App\Models\data_pengajuan();
            $pengajuan->no_ktp = $member->no_ktp;
            $pengajuan->jenis_pinjaman = $request->jenis_pinjaman;
            $pengajuan->jumlah_pinjaman = $request->jumlah_pinjaman;
            $pengajuan->keterangan = $request->keterangan;
            $pengajuan->status = 'Pending';
            $pengajuan->tgl_pengajuan = now();
            $pengajuan->save();

            return redirect()->route('member.pengajuan.pinjaman')->with('success', 'Pengajuan pinjaman berhasil dikirim');
        } catch (\Exception $e) {
            Log::error('Error in storePengajuanPinjaman: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
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
            $member = auth()->guard('member')->user();
            $member->update($request->only(['nama', 'alamat', 'notelp', 'email']));

            return redirect()->route('member.profile')->with('success', 'Profil berhasil diperbarui');
        } catch (\Exception $e) {
            Log::error('Error in updateProfile: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
} 