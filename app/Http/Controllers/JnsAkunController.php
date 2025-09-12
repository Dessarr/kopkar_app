<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\jns_akun;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\JnsAkunExport;
use App\Imports\JnsAkunImport;

class JnsAkunController extends Controller
{
    public function index(Request $request)
    {
        $query = jns_akun::query();

        // Handle search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('kd_aktiva', 'like', '%' . $search . '%')
                  ->orWhere('jns_trans', 'like', '%' . $search . '%')
                  ->orWhere('akun', 'like', '%' . $search . '%');
            });
        }

        // Handle filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('aktif', $request->status);
        }

        // Handle filter by account type
        if ($request->has('akun_type') && $request->akun_type !== '') {
            $query->where('akun', $request->akun_type);
        }

        $dataAkun = $query->orderBy('kd_aktiva')->paginate(10);
        
        // Get unique account types for filter
        $accountTypes = jns_akun::select('akun')->distinct()->pluck('akun');

        return view('master-data.jns_akun', compact('dataAkun', 'accountTypes'));
    }

    public function create()
    {
        return view('master-data.jns_akun.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kd_aktiva' => 'required|string|max:10|unique:jns_akun,kd_aktiva',
            'jns_trans' => 'required|string|max:255',
            'akun' => 'required|string|max:50',
            'laba_rugi' => 'nullable|string|max:50',
            'pemasukan' => 'required|boolean',
            'pengeluaran' => 'required|boolean',
            'aktif' => 'required|boolean'
        ]);

        jns_akun::create($validated);

        return redirect()->route('master-data.jns_akun')
            ->with('success', 'Data jenis akun berhasil ditambahkan');
    }

    public function show($id)
    {
        $akun = jns_akun::findOrFail($id);
        return view('master-data.jns_akun.show', compact('akun'));
    }

    public function edit($id)
    {
        $akun = jns_akun::findOrFail($id);
        return view('master-data.jns_akun.edit', compact('akun'));
    }

    public function update(Request $request, $id)
    {
        $akun = jns_akun::findOrFail($id);
        
        $validated = $request->validate([
            'kd_aktiva' => 'required|string|max:10|unique:jns_akun,kd_aktiva,' . $id,
            'jns_trans' => 'required|string|max:255',
            'akun' => 'required|string|max:50',
            'laba_rugi' => 'nullable|string|max:50',
            'pemasukan' => 'required|boolean',
            'pengeluaran' => 'required|boolean',
            'aktif' => 'required|boolean'
        ]);

        $akun->update($validated);

        return redirect()->route('master-data.jns_akun')
            ->with('success', 'Data jenis akun berhasil diperbarui');
    }

    public function destroy($id)
    {
        $akun = jns_akun::findOrFail($id);
        $akun->delete();

        return redirect()->route('master-data.jns_akun')
            ->with('success', 'Data jenis akun berhasil dihapus');
    }

    public function export()
    {
        $fileName = 'jenis_akun_' . date('Y-m-d') . '.xlsx';
        return Excel::download(new JnsAkunExport, $fileName);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new JnsAkunImport, $request->file('file'));
            return redirect()->back()->with('success', 'Data berhasil diimport');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error importing data: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        $fileName = 'template_jenis_akun.xlsx';
        return Excel::download(new JnsAkunExport, $fileName);
    }
}