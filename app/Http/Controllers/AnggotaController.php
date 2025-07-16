<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TblTransToserda;
use App\Models\billing;
use App\Models\data_anggota;
use Illuminate\Support\Facades\Auth;

class AnggotaController extends Controller
{
    /**
     * Display Toserda payment page for members
     */
    public function bayarToserda()
    {
        $member = Auth::guard('member')->user();
        $anggota = data_anggota::where('no_ktp', $member->no_ktp)->first();
        
        // Get all Toserda transactions for this member
        $transaksi = TblTransToserda::where('no_ktp', $member->no_ktp)
            ->orderBy('tgl_transaksi', 'desc')
            ->get();
            
        // Group transactions by month and year
        $transactionsByPeriod = $transaksi->groupBy(function($item) {
            return $item->tgl_transaksi->format('Y-m');
        });
        
        // Get billing status for each transaction
        foreach ($transaksi as $tr) {
            $tr->is_billed = billing::where('id_transaksi', $tr->id)
                ->where('jns_transaksi', 'toserda')
                ->exists();
        }
        
        return view('anggota.bayar_toserda_lain', [
            'anggota' => $anggota,
            'transaksi' => $transaksi,
            'transactionsByPeriod' => $transactionsByPeriod
        ]);
    }
    
    /**
     * Process payment for a specific billing
     */
    public function processPayment(Request $request, $billing_code)
    {
        try {
            $billing = billing::findOrFail($billing_code);
            $member = Auth::guard('member')->user();
            
            // Verify that this billing belongs to the logged-in member
            if ($billing->no_ktp !== $member->no_ktp) {
                return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk membayar tagihan ini.');
            }
            
            // Update billing status to paid
            $billing->status_bayar = 'sudah';
            $billing->tgl_bayar = now();
            $billing->save();
            
            return redirect()->route('anggota.bayar.toserda')->with('success', 'Pembayaran berhasil diproses.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    /**
     * Get transactions by period (month/year)
     */
    public function getTransaksiByPeriod(Request $request)
    {
        $member = Auth::guard('member')->user();
        $period = $request->period; // Format: YYYY-MM
        
        if (!$period) {
            return response()->json(['error' => 'Period is required'], 400);
        }
        
        list($year, $month) = explode('-', $period);
        
        $transaksi = TblTransToserda::where('no_ktp', $member->no_ktp)
            ->whereYear('tgl_transaksi', $year)
            ->whereMonth('tgl_transaksi', $month)
            ->orderBy('tgl_transaksi', 'desc')
            ->get();
            
        // Get billing status for each transaction
        foreach ($transaksi as $tr) {
            $tr->is_billed = billing::where('id_transaksi', $tr->id)
                ->where('jns_transaksi', 'toserda')
                ->exists();
                
            $tr->billing = billing::where('id_transaksi', $tr->id)
                ->where('jns_transaksi', 'toserda')
                ->first();
        }
        
        return response()->json([
            'transaksi' => $transaksi,
            'period' => $period
        ]);
    }
} 