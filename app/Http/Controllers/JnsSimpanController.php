<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\jns_simpan;

class JnsSimpanController extends Controller
{
    // Menampilkan halaman daftar jenis akun
    public function index()
    {
        // Ambil 10 data dari tabel jns_akun
        $dataSimpan = jns_simpan::paginate(10);

        // Kirim ke view bernama jns_akun.index
        return view('master-data.jns_simpanan', compact('dataSimpan'));
    }
}