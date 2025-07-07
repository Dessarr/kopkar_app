<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\transaksi_kas;

class TransaksiKasController extends Controller
{
    public function pemasukan()
    {
        $dataKas = transaksi_kas::where('akun', 'pemasukan')->paginate(10);

        return view('transaksi_kas.pemasukan', compact('dataKas'));
    }
    public function pengeluaran()
    {
        $dataKas = transaksi_kas::where('akun', 'pengeluaran')->paginate(10);

        return view('transaksi_kas.pengeluaran', compact('dataKas'));
    }

   public function transfer()
{
    $dataKas = transaksi_kas::with(['dariKas', 'untukKas'])->paginate(10);
    return view('transaksi_kas.transfer', compact('dataKas'));
}
}