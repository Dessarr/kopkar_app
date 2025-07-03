<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\jns_angsuran;

class JnsAngusuranController extends Controller
{
    public function index()
    {
        $jnsAngsuran = jns_angsuran::paginate(10);
        return view('master-data.jenis_angsuran', compact('jnsAngsuran'));
    }
}