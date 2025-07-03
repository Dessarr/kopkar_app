<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\transaksi_kas;

class TransaksiKasController extends Controller
{
    // Menampilkan halaman daftar jenis akun
    public function pemasukan()
    {
        // Ambil 10 data dari tabel jns_akun
        $dataKas = transaksi_kas::paginate(10);

        // Kirim ke view bernama jns_akun.index
        return view('transaksi_kas.pemasukan', compact('dataKas'));
    }
    public function pengeluaran()
    {
        // Ambil 10 data dari tabel jns_akun
        $dataKas = transaksi_kas::paginate(10);

        // Kirim ke view bernama jns_akun.index
        return view('transaksi_kas.pengeluaran', compact('dataKas'));
    }

    public function transfer()
    {
        // Ambil 10 data dari tabel jns_akun
        $dataKas = transaksi_kas::paginate(10);

        // Kirim ke view bernama jns_akun.index
        return view('transaksi_kas.transfer', compact('dataKas'));
    }
}