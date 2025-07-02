<?php

namespace App\Http\Controllers;

use App\Models\Kas;
use App\Models\TransaksiKas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class KasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function pemasukanView()
    {

        return view('kas.index');
    }
    public function pengeluaranView()
    {
        return view('kas.pengeluaran');
    }
    public function transferView()
    {
        return view('kas.transfer');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $listKas = Kas::where('is_active', true)->get();
        return view('kas.create', compact('listKas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
       
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function report(Request $request)
    {
       
    }
}