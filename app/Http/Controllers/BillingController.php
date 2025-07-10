<?php

namespace App\Http\Controllers;

use App\Models\Billing;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BillingController extends Controller
{
    public function index(Request $request)
    {
        $periode = $request->input('periode'); // format: 2025-07
        $query = Billing::with('anggota');     // relasi dengan anggota (no_ktp)

        // Jika ada filter bulan
        if ($periode) {
            $carbonPeriode = Carbon::parse($periode . '-01');
            $query->where('bulan_tahun', $carbonPeriode->translatedFormat('F Y'));
        }

        $dataBilling = $query->paginate(10);

        return view('billing.billing', compact('dataBilling', 'periode'));
    }
}