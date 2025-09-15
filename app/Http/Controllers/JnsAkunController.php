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
        if ($request->has('search') && !empty(trim($request->search))) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('kd_aktiva', 'like', '%' . $search . '%')
                  ->orWhere('jns_trans', 'like', '%' . $search . '%')
                  ->orWhere('akun', 'like', '%' . $search . '%');
            });
        }

        // Handle filter by status
        if ($request->has('status') && $request->status !== '') {
            $statusValue = $request->status == '1' ? 'Y' : 'N';
            $query->where('aktif', $statusValue);
        }

        // Handle filter by account type
        if ($request->has('akun_type') && $request->akun_type !== '' && $request->akun_type !== null) {
            $query->where('akun', $request->akun_type);
        }

        $dataAkun = $query->orderBy('kd_aktiva')->paginate(10);
        
        
        // Get all data for summary cards (without pagination)
        $allDataAkun = jns_akun::query();
        // Apply same filters for summary
        if ($request->has('search') && !empty(trim($request->search))) {
            $search = trim($request->search);
            $allDataAkun->where(function($q) use ($search) {
                $q->where('kd_aktiva', 'like', '%' . $search . '%')
                  ->orWhere('jns_trans', 'like', '%' . $search . '%')
                  ->orWhere('akun', 'like', '%' . $search . '%');
            });
        }
        if ($request->has('status') && $request->status !== '') {
            $statusValue = $request->status == '1' ? 'Y' : 'N';
            $allDataAkun->where('aktif', $statusValue);
        }
        if ($request->has('akun_type') && $request->akun_type !== '' && $request->akun_type !== null) {
            $allDataAkun->where('akun', $request->akun_type);
        }
        $allDataAkun = $allDataAkun->get();
        
        // Get unique account types for filter (exclude NULL values)
        $accountTypes = jns_akun::select('akun')
            ->whereNotNull('akun')
            ->distinct()
            ->pluck('akun');

        return view('master-data.jns_akun', compact('dataAkun', 'allDataAkun', 'accountTypes'));
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

        return redirect()->route('master-data.jns_akun.index')
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

        return redirect()->route('master-data.jns_akun.index')
            ->with('success', 'Data jenis akun berhasil diperbarui');
    }

    public function destroy($id)
    {
        $akun = jns_akun::findOrFail($id);
        $akun->delete();

        return redirect()->route('master-data.jns_akun.index')
            ->with('success', 'Data jenis akun berhasil dihapus');
    }

    public function export(Request $request)
    {
        $fileName = 'jenis_akun_' . date('Y-m-d') . '.xlsx';
        return Excel::download(new JnsAkunExport(
            $request->search,
            $request->status,
            $request->akun_type
        ), $fileName);
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

    public function print()
    {
        $dataAkun = jns_akun::orderBy('kd_aktiva')->get();
        return view('master-data.jns_akun.print', compact('dataAkun'));
    }
}