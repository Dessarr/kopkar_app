<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DataKas;
use App\Exports\DataKasExport;
use App\Imports\DataKasImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;

class DtaKasController extends Controller
{
    public function index(Request $request)
    {
        $query = DataKas::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('id', 'like', '%' . $search . '%');
            });
        }

        // Filter by status
        if ($request->filled('status_aktif')) {
            $query->byStatusAktif($request->status_aktif);
        }

        // Filter by kategori
        if ($request->filled('kategori')) {
            $query->byKategori($request->kategori);
        }

        // Filter by fitur
        if ($request->filled('fitur')) {
            $query->byFitur($request->fitur);
        }

        $dataKas = $query->ordered()->paginate(10)->withQueryString();

        return view('master-data.data_kas', compact('dataKas'));
    }

    public function create()
    {
        return view('master-data.data_kas.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'aktif' => 'required|in:Y,T',
            'tmpl_simpan' => 'required|in:Y,T',
            'tmpl_penarikan' => 'required|in:Y,T',
            'tmpl_pinjaman' => 'required|in:Y,T',
            'tmpl_bayar' => 'required|in:Y,T',
            'tmpl_pemasukan' => 'required|in:Y,T',
            'tmpl_pengeluaran' => 'required|in:Y,T',
            'tmpl_transfer' => 'required|in:Y,T',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DataKas::create($request->all());

        return redirect()->route('master-data.data_kas.index')
            ->with('success', 'Data Kas berhasil ditambahkan!');
    }

    public function show($id)
    {
        $dataKas = DataKas::findOrFail($id);
        return view('master-data.data_kas.show', compact('dataKas'));
    }

    public function edit($id)
    {
        $dataKas = DataKas::findOrFail($id);
        return view('master-data.data_kas.edit', compact('dataKas'));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'aktif' => 'required|in:Y,T',
            'tmpl_simpan' => 'required|in:Y,T',
            'tmpl_penarikan' => 'required|in:Y,T',
            'tmpl_pinjaman' => 'required|in:Y,T',
            'tmpl_bayar' => 'required|in:Y,T',
            'tmpl_pemasukan' => 'required|in:Y,T',
            'tmpl_pengeluaran' => 'required|in:Y,T',
            'tmpl_transfer' => 'required|in:Y,T',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $dataKas = DataKas::findOrFail($id);
        $dataKas->update($request->all());

        return redirect()->route('master-data.data_kas.index')
            ->with('success', 'Data Kas berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $dataKas = DataKas::findOrFail($id);
        $dataKas->delete();

        return redirect()->route('master-data.data_kas.index')
            ->with('success', 'Data Kas berhasil dihapus!');
    }

    public function export(Request $request)
    {
        $query = DataKas::query();

        // Apply same filters as index
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('id', 'like', '%' . $search . '%');
            });
        }
        if ($request->filled('status_aktif')) {
            $query->byStatusAktif($request->status_aktif);
        }
        if ($request->filled('kategori')) {
            $query->byKategori($request->kategori);
        }
        if ($request->filled('fitur')) {
            $query->byFitur($request->fitur);
        }

        $data = $query->ordered()->get();

        return Excel::download(new DataKasExport($data), 'data_kas_' . date('Y-m-d_H-i-s') . '.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new DataKasImport, $request->file('file'));
            return redirect()->route('master-data.data_kas.index')
                ->with('success', 'Data Kas berhasil diimport!');
        } catch (\Exception $e) {
            return redirect()->route('master-data.data_kas.index')
                ->with('error', 'Error importing data: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        $data = collect([
            (object)[
                'nama' => 'Kas Utama',
                'aktif' => 'Y',
                'tmpl_simpan' => 'Y',
                'tmpl_penarikan' => 'Y',
                'tmpl_pinjaman' => 'Y',
                'tmpl_bayar' => 'Y',
                'tmpl_pemasukan' => 'Y',
                'tmpl_pengeluaran' => 'Y',
                'tmpl_transfer' => 'Y',
            ]
        ]);

        return Excel::download(new DataKasExport($data), 'template_data_kas.xlsx');
    }

    public function print()
    {
        $dataKas = DataKas::ordered()->get();
        return view('master-data.data_kas.print', compact('dataKas'));
    }
}