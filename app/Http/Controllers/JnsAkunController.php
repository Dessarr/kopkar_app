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
        try {
            // Build query with filters
            $query = jns_akun::query();

            // Apply search filter
            if ($request->filled('search')) {
                $query->search($request->search);
            }

            // Apply status filter
            if ($request->filled('status')) {
                if ($request->status === 'Y' || $request->status === 'N') {
                    $query->where('aktif', $request->status);
                } elseif ($request->status === '1') {
                    $query->where('aktif', 'Y');
                } elseif ($request->status === '0') {
                    $query->where('aktif', 'N');
                }
            }

            // Apply account type filter
            if ($request->filled('akun_type')) {
                $query->byAkunType($request->akun_type);
            }

            // Get paginated data
            $dataAkun = $query->ordered()->paginate(10);
            
            // Get all data for summary cards (apply same filters)
            $allDataAkun = jns_akun::query();
            
            if ($request->filled('search')) {
                $allDataAkun->search($request->search);
            }
            
            if ($request->filled('status')) {
                if ($request->status === 'Y' || $request->status === 'N') {
                    $allDataAkun->where('aktif', $request->status);
                } elseif ($request->status === '1') {
                    $allDataAkun->where('aktif', 'Y');
                } elseif ($request->status === '0') {
                    $allDataAkun->where('aktif', 'N');
                }
            }
            
            if ($request->filled('akun_type')) {
                $allDataAkun->byAkunType($request->akun_type);
            }
            
            $allDataAkun = $allDataAkun->get();
            
            // Get unique account types for filter dropdown
            $accountTypes = jns_akun::select('akun')
                ->whereNotNull('akun')
                ->distinct()
                ->orderBy('akun')
                ->pluck('akun');

            return view('master-data.jns_akun', compact('dataAkun', 'allDataAkun', 'accountTypes'));
            
        } catch (\Exception $e) {
            // Log error for debugging
            \Log::error('Error in JnsAkunController@index: ' . $e->getMessage());
            
            // Return empty results with error message
            $dataAkun = collect()->paginate(10);
            $allDataAkun = collect();
            $accountTypes = collect();
            
            return view('master-data.jns_akun', compact('dataAkun', 'allDataAkun', 'accountTypes'))
                ->with('error', 'Terjadi kesalahan saat memuat data. Silakan coba lagi.');
        }
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
        
        // Normalize status value for export
        $status = $request->status;
        if ($status === '1') {
            $status = 'Y';
        } elseif ($status === '0') {
            $status = 'N';
        }
        
        return Excel::download(new JnsAkunExport(
            $request->search,
            $status,
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