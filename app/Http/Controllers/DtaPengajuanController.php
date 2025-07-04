<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\data_pengajuan;

class DtaPengajuanController extends Controller
{
    public function index()
    {
        $dataPengajuan = data_pengajuan::paginate(10);
        return view('pinjaman.data_pengajuan', compact('dataPengajuan'));
    }
}