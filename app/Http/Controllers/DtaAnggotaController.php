<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\data_anggota;

class DtaAnggotaController extends Controller
{
    public function index()
    {
        $dataAnggota = data_anggota::paginate(10);
        return view('master-data.data_anggota', compact('dataAnggota'));
    }
}