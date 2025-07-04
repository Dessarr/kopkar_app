<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\data_pengajuan_penarikan;

class DtaPengajuanPenarikanController extends Controller
{
    public function index()
    {
        $dataPengajuan = data_pengajuan_penarikan::orderBy('tgl_input', 'desc')->paginate(10);
        return view('simpanan.pengajuan_penarikan', compact('dataPengajuan'));
    }
}
