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
            'nama' => 'required',
            'pass_word' => 'required'
        ]);

        // Debug log
        Log::info('Login attempt for member: ' . $credentials['nama']);

        // Cari member berdasarkan nama
        $member = Member::where('nama', $credentials['nama'])->first();

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
            Log::info('No member found with name: ' . $credentials['nama']);
        }

        return back()->withErrors([
            'login' => 'The provided credentials do not match our records.',
        ])->withInput($request->except('pass_word'));
    }

    public function memberDashboard()
    {
        return view('member.dashboard');
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
            \Log::error('Error in toserdaPayment: ' . $e->getMessage());
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
            \Log::error('Error in processToserda: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
} 