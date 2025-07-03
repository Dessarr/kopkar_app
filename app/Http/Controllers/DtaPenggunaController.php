<?php
namespace App\Http\Controllers;

use App\Models\data_pengguna;
use Illuminate\Http\Request;

class DtaPenggunaController extends Controller
{
    public function index()
    {
        $dataPengguna = data_pengguna::paginate(10);
        return view('master-data.data_pengguna', compact('dataPengguna'));
    }
}