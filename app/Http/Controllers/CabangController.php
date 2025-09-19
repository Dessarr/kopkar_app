<?php

namespace App\Http\Controllers;

use App\Models\Cabang;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CabangExport;

class CabangController extends Controller
{
    public function index(Request $request)
    {
        $query = Cabang::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id_cabang', 'like', "%{$search}%")
                  ->orWhere('nama', 'like', "%{$search}%")
                  ->orWhere('alamat', 'like', "%{$search}%")
                  ->orWhere('no_telp', 'like', "%{$search}%");
            });
        }

        $cabang = $query->orderBy('id_cabang')->paginate(10);

        return view('master-data.cabang.index', compact('cabang'));
    }

    public function create()
    {
        // Generate next ID cabang using model method
        $nextId = Cabang::generateNextId();
        
        return view('master-data.cabang.create', compact('nextId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_cabang' => 'required|string|max:8|unique:cabang,id_cabang',
            'nama' => 'required|string|max:200',
            'alamat' => 'required|string|max:500',
            'no_telp' => 'required|string|max:15',
        ]);

        Cabang::create($request->all());

        return redirect()->route('master-data.cabang.index')
            ->with('success', 'Data cabang berhasil ditambahkan.');
    }

    public function show($id)
    {
        $cabang = Cabang::findOrFail($id);
        return view('master-data.cabang.show', compact('cabang'));
    }

    public function edit($id)
    {
        $cabang = Cabang::findOrFail($id);
        return view('master-data.cabang.edit', compact('cabang'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'id_cabang' => 'required|string|max:8|unique:cabang,id_cabang,' . $id . ',id_cabang',
            'nama' => 'required|string|max:200',
            'alamat' => 'required|string|max:500',
            'no_telp' => 'required|string|max:15',
        ]);

        $cabang = Cabang::findOrFail($id);
        $cabang->update($request->all());

        return redirect()->route('master-data.cabang.index')
            ->with('success', 'Data cabang berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $cabang = Cabang::findOrFail($id);
        $cabang->delete();

        return redirect()->route('master-data.cabang.index')
            ->with('success', 'Data cabang berhasil dihapus.');
    }

    public function export(Request $request)
    {
        return Excel::download(new CabangExport(), 'data_cabang.xlsx');
    }


    public function print()
    {
        $cabang = Cabang::orderBy('id_cabang')->get();
        
        return view('master-data.cabang.print', compact('cabang'));
    }

}
