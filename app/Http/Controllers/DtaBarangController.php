<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\data_barang;

class DtaBarangController extends Controller
{
    public function index()
    {
        $dataBarang = data_barang::paginate(10);
        return view('master-data.data_barang', compact('dataBarang'));
    }
}