<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\jns_simpan;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\JnsSimpanExport;
use App\Imports\JnsSimpanImport;

class JnsSimpanController extends Controller
{
    public function index(Request $request)
    {
        $query = jns_simpan::query();

        // Handle search
        if ($request->has('search')) {
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

        $dataSimpan = $query->orderBy('urut')->paginate(10);
        
        // Get all data for summary cards (without pagination)
        $allDataSimpan = jns_simpan::query();
        // Apply same filters for summary
        if ($request->has('search')) {
            $search = $request->search;
            $allDataSimpan->where(function($q) use ($search) {
                $q->where('id', 'like', '%' . $search . '%')
                  ->orWhere('jns_simpan', 'like', '%' . $search . '%')
                  ->orWhere('jumlah', 'like', '%' . $search . '%');
            });
        }
        if ($request->has('status') && $request->status !== '') {
            $allDataSimpan->where('tampil', $request->status);
        }
        if ($request->has('type') && $request->type !== '') {
            $allDataSimpan->where('jns_simpan', 'like', '%' . $request->type . '%');
        }
        $allDataSimpan = $allDataSimpan->get();
        
        // Get unique types for filter
        $types = jns_simpan::select('jns_simpan')->distinct()->pluck('jns_simpan');

        return view('master-data.jns_simpan', compact('dataSimpan', 'allDataSimpan', 'types'));
    }

    public function create()
    {
        return view('master-data.jns_simpan.create');
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

        return redirect()->route('master-data.jns_simpan.index')
            ->with('success', 'Data jenis simpanan berhasil ditambahkan');
    }

    public function show($id)
    {
        $simpan = jns_simpan::findOrFail($id);
        return view('master-data.jns_simpan.show', compact('simpan'));
    }

    public function edit($id)
    {
        $simpan = jns_simpan::findOrFail($id);
        return view('master-data.jns_simpan.edit', compact('simpan'));
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

        return redirect()->route('master-data.jns_simpan.index')
            ->with('success', 'Data jenis simpanan berhasil diperbarui');
    }

    public function destroy($id)
    {
        $simpan = jns_simpan::findOrFail($id);
        $simpan->delete();

        return redirect()->route('master-data.jns_simpan.index')
            ->with('success', 'Data jenis simpanan berhasil dihapus');
    }

    public function export()
    {
        $fileName = 'jenis_simpanan_' . date('Y-m-d') . '.xlsx';
        return Excel::download(new JnsSimpanExport, $fileName);
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

    public function print()
    {
        $dataSimpan = jns_simpan::orderBy('urut')->get();
        return view('master-data.jns_simpan.print', compact('dataSimpan'));
    }
}