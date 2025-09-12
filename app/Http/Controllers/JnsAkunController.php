<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\jns_akun;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\JnsAkunExport;
use App\Imports\JnsAkunImport;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class JnsAkunController extends Controller
{
    public function index(Request $request)
    {
        $query = jns_akun::query();

        // Handle search
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('kd_aktiva', 'like', '%' . $search . '%')
                  ->orWhere('jns_trans', 'like', '%' . $search . '%')
                  ->orWhere('akun', 'like', '%' . $search . '%')
                  ->orWhere('laba_rugi', 'like', '%' . $search . '%');
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

        // Handle filter by pemasukan
        if ($request->has('pemasukan') && $request->pemasukan !== '') {
            $query->where('pemasukan', $request->pemasukan);
        }

        // Handle filter by pengeluaran
        if ($request->has('pengeluaran') && $request->pengeluaran !== '') {
            $query->where('pengeluaran', $request->pengeluaran);
        }

        // Handle sorting
        $sortBy = $request->get('sort_by', 'kd_aktiva');
        $sortOrder = $request->get('sort_order', 'asc');
        
        if (in_array($sortBy, ['kd_aktiva', 'jns_trans', 'akun', 'laba_rugi', 'aktif'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('kd_aktiva', 'asc');
        }

        $dataAkun = $query->paginate(10);
        
        // Get unique account types for filter
        $accountTypes = jns_akun::select('akun')->distinct()->pluck('akun');

        // Calculate statistics
        $totalJenisAkun = jns_akun::count();
        $akunAktif = jns_akun::where('aktif', 1)->count();
        $akunTidakAktif = jns_akun::where('aktif', 0)->count();
        $akunPemasukan = jns_akun::where('pemasukan', 1)->count();
        $akunPengeluaran = jns_akun::where('pengeluaran', 1)->count();
        $akunLabaRugi = jns_akun::whereNotNull('laba_rugi')->count();

        return view('master-data.jns_akun', compact(
            'dataAkun', 
            'accountTypes',
            'totalJenisAkun',
            'akunAktif',
            'akunTidakAktif',
            'akunPemasukan',
            'akunPengeluaran',
            'akunLabaRugi'
        ));
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
        $filters = $request->only(['search', 'status', 'jns_trans', 'pemasukan', 'pengeluaran', 'sort_by', 'sort_order']);
        $fileName = 'jenis_akun_' . date('Y-m-d') . '.xlsx';
        return Excel::download(new JnsAkunExport($filters), $fileName);
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

    public function print(Request $request)
    {
        $query = jns_akun::query();

        // Apply same filters as index method
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('kd_aktiva', 'like', '%' . $search . '%')
                  ->orWhere('jns_trans', 'like', '%' . $search . '%')
                  ->orWhere('akun', 'like', '%' . $search . '%')
                  ->orWhere('laba_rugi', 'like', '%' . $search . '%');
            });
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('aktif', $request->status);
        }

        if ($request->has('akun_type') && $request->akun_type !== '') {
            $query->where('akun', $request->akun_type);
        }

        if ($request->has('pemasukan') && $request->pemasukan !== '') {
            $query->where('pemasukan', $request->pemasukan);
        }

        if ($request->has('pengeluaran') && $request->pengeluaran !== '') {
            $query->where('pengeluaran', $request->pengeluaran);
        }

        $sortBy = $request->get('sort_by', 'kd_aktiva');
        $sortOrder = $request->get('sort_order', 'asc');
        
        if (in_array($sortBy, ['kd_aktiva', 'jns_trans', 'akun', 'laba_rugi', 'aktif'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('kd_aktiva', 'asc');
        }

        $dataAkun = $query->get();

        $pdf = PDF::loadView('master-data.jns_akun.print', compact('dataAkun'));
        $pdf->setPaper('A4', 'landscape');
        
        return $pdf->download('jenis_akun_' . date('Y-m-d_H-i-s') . '.pdf');
    }
}