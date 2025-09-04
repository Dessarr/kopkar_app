<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NamaKasTbl;
use App\Exports\NamaKasTblExport;
use App\Imports\NamaKasTblImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;

class DtaKasController extends Controller
{
    public function index(Request $request)
    {
        $query = NamaKasTbl::query();

        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
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

        NamaKasTbl::create($request->all());

        return redirect()->route('master-data.data_kas')
            ->with('success', 'Data Kas berhasil ditambahkan!');
    }

    public function show($id)
    {
        $dataKas = NamaKasTbl::findOrFail($id);
        return view('master-data.data_kas.show', compact('dataKas'));
    }

    public function edit($id)
    {
        $dataKas = NamaKasTbl::findOrFail($id);
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

        $dataKas = NamaKasTbl::findOrFail($id);
        $dataKas->update($request->all());

        return redirect()->route('master-data.data_kas')
            ->with('success', 'Data Kas berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $dataKas = NamaKasTbl::findOrFail($id);
        $dataKas->delete();

        return redirect()->route('master-data.data_kas')
            ->with('success', 'Data Kas berhasil dihapus!');
    }

    public function export(Request $request)
    {
        $query = NamaKasTbl::query();

        // Apply same filters as index
        if ($request->filled('search')) {
            $query->search($request->search);
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

        return Excel::download(new NamaKasTblExport($data), 'data_kas_' . date('Y-m-d_H-i-s') . '.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new NamaKasTblImport, $request->file('file'));
            return redirect()->route('master-data.data_kas')
                ->with('success', 'Data Kas berhasil diimport!');
        } catch (\Exception $e) {
            return redirect()->route('master-data.data_kas')
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

        return Excel::download(new NamaKasTblExport($data), 'template_data_kas.xlsx');
    }
}