<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\jns_akun;

class JnsAkunController extends Controller
{
    // Menampilkan halaman daftar jenis akun
    public function index()
    {
        // Ambil 10 data dari tabel jns_akun
        $dataAkun = jns_akun::paginate(10);

        // Kirim ke view bernama jns_akun.index
        return view('master-data.jns_akun', compact('dataAkun'));
    }
}