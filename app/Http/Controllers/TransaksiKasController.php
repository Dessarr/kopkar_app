<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\transaksi_kas;
use App\Models\View_Transaksi;

class TransaksiKasController extends Controller
{
    public function pemasukan()
    {
        $dataKas = View_Transaksi::where('transaksi', '48')
        ->with('kasTujuan')
        ->paginate(10);

        return view('transaksi_kas.pemasukan', compact('dataKas'));
    }
    public function pengeluaran()
    {
        $dataKas = View_Transaksi::where('transaksi', '7')
        ->with('kasAsal')
        ->paginate(10);

        return view('transaksi_kas.pengeluaran', compact('dataKas'));
    }

   public function transfer()
{
    $dataKas = transaksi_kas::with(['dariKas', 'untukKas'])->paginate(10);
    return view('transaksi_kas.transfer', compact('dataKas'));
}
}