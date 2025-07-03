<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\data_mobil;

class DtaMobilController extends Controller
{
    public function index()
    {
        $dataMobil = data_mobil::paginate(10);
        return view('master-data.data_mobil', compact('dataMobil'));
    }
}