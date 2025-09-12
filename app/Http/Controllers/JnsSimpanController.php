<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\jns_simpan;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\JnsSimpanExport;
use App\Imports\JnsSimpanImport;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class JnsSimpanController extends Controller
{
    public function index(Request $request)
    {
        $query = jns_simpan::query();

        // Handle search
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', '%' . $search . '%')
                  ->orWhere('jns_simpan', 'like', '%' . $search . '%')
                  ->orWhere('jumlah', 'like', '%' . $search . '%');
            });
        }

        // Handle filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('tampil', $request->status);
        }

        // Handle filter by type
        if ($request->has('type') && $request->type !== '') {
            $query->where('jns_simpan', 'like', '%' . $request->type . '%');
        }

        // Handle sorting
        $sortBy = $request->get('sort_by', 'urut');
        $sortOrder = $request->get('sort_order', 'asc');
        
        if (in_array($sortBy, ['id', 'jns_simpan', 'jumlah', 'tampil', 'urut'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('urut', 'asc');
        }

        $dataSimpan = $query->paginate(10);
        
        // Get unique types for filter
        $types = jns_simpan::select('jns_simpan')->distinct()->pluck('jns_simpan');

        // Calculate statistics
        $totalJenisSimpanan = jns_simpan::count();
        $jenisSimpananAktif = jns_simpan::where('tampil', 'Y')->count();
        $jenisSimpananTidakAktif = jns_simpan::where('tampil', 'T')->count();
        $totalJumlahMinimum = jns_simpan::sum('jumlah');
        $rataRataJumlah = $totalJenisSimpanan > 0 ? $totalJumlahMinimum / $totalJenisSimpanan : 0;

        return view('master-data.jns_simpanan', compact(
            'dataSimpan', 
            'types', 
            'totalJenisSimpanan',
            'jenisSimpananAktif',
            'jenisSimpananTidakAktif',
            'totalJumlahMinimum',
            'rataRataJumlah'
        ));
    }

    public function create()
    {
        return view('master-data.jns_simpanan.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'jns_simpan' => 'required|string|max:30',
            'jumlah' => 'required|numeric|min:0',
            'tampil' => 'required|in:Y,T',
            'urut' => 'required|integer|min:1|max:99'
        ]);

        jns_simpan::create($validated);

        return redirect()->route('master-data.jns_simpan')
            ->with('success', 'Data jenis simpanan berhasil ditambahkan');
    }

    public function show($id)
    {
        $simpan = jns_simpan::findOrFail($id);
        return view('master-data.jns_simpanan.show', compact('simpan'));
    }

    public function edit($id)
    {
        $simpan = jns_simpan::findOrFail($id);
        return view('master-data.jns_simpanan.edit', compact('simpan'));
    }

    public function update(Request $request, $id)
    {
        $simpan = jns_simpan::findOrFail($id);
        
        $validated = $request->validate([
            'jns_simpan' => 'required|string|max:30',
            'jumlah' => 'required|numeric|min:0',
            'tampil' => 'required|in:Y,T',
            'urut' => 'required|integer|min:1|max:99'
        ]);

        $simpan->update($validated);

        return redirect()->route('master-data.jns_simpan')
            ->with('success', 'Data jenis simpanan berhasil diperbarui');
    }

    public function destroy($id)
    {
        $simpan = jns_simpan::findOrFail($id);
        $simpan->delete();

        return redirect()->route('master-data.jns_simpan')
            ->with('success', 'Data jenis simpanan berhasil dihapus');
    }

    public function export(Request $request)
    {
        $filters = $request->only(['search', 'status', 'type']);
        $fileName = 'jenis_simpanan_' . date('Y-m-d') . '.xlsx';
        return Excel::download(new JnsSimpanExport($filters), $fileName);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new JnsSimpanImport, $request->file('file'));
            return redirect()->back()->with('success', 'Data berhasil diimport');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error importing data: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        $fileName = 'template_jenis_simpanan.xlsx';
        return Excel::download(new JnsSimpanExport, $fileName);
    }

    public function print(Request $request)
    {
        $query = jns_simpan::query();

        // Apply same filters as index method
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', '%' . $search . '%')
                  ->orWhere('jns_simpan', 'like', '%' . $search . '%')
                  ->orWhere('jumlah', 'like', '%' . $search . '%');
            });
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('tampil', $request->status);
        }

        if ($request->has('type') && $request->type !== '') {
            $query->where('jns_simpan', 'like', '%' . $request->type . '%');
        }

        $sortBy = $request->get('sort_by', 'urut');
        $sortOrder = $request->get('sort_order', 'asc');
        
        if (in_array($sortBy, ['id', 'jns_simpan', 'jumlah', 'tampil', 'urut'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('urut', 'asc');
        }

        $dataSimpan = $query->get();

        $pdf = PDF::loadView('master-data.jns_simpanan.print', compact('dataSimpan'));
        $pdf->setPaper('A4', 'landscape');
        
        return $pdf->download('jenis_simpanan_' . date('Y-m-d_H-i-s') . '.pdf');
    }
}