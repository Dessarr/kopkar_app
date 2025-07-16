<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NamaKasTbl;

class DtaKasController extends Controller
{
    // Menampilkan halaman daftar jenis akun
    public function index()
    {
        // Ambil 10 data dari tabel jns_akun
        $dataKas = NamaKasTbl::paginate(10);

        // Kirim ke view bernama jns_akun.index
        return view('master-data.data_kas', compact('dataKas'));
    }
}