<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\tbl_mobil;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TblMobilExport;
use App\Imports\TblMobilImport;

class DtaMobilController extends Controller
{
    public function index(Request $request)
    {
        $query = tbl_mobil::query();

        // Search functionality
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by jenis
        if ($request->filled('jenis')) {
            $query->byJenis($request->jenis);
        }

        // Filter by merek
        if ($request->filled('merek')) {
            $query->byMerek($request->merek);
        }

        // Filter by pabrikan
        if ($request->filled('pabrikan')) {
            $query->byPabrikan($request->pabrikan);
        }

        // Filter by warna
        if ($request->filled('warna')) {
            $query->byWarna($request->warna);
        }

        // Filter by tahun
        if ($request->filled('tahun')) {
            $query->byTahun($request->tahun);
        }

        // Filter by status aktif
        if ($request->filled('status_aktif')) {
            $query->byStatusAktif($request->status_aktif);
        }

        // Filter by status STNK
        if ($request->filled('status_stnk')) {
            $query->byStatusStnk($request->status_stnk);
        }

        $dataMobil = $query->ordered()->paginate(10);
        
        // Get unique values for filters
        $jenis = tbl_mobil::distinct()->pluck('jenis')->filter()->sort()->values();
        $merek = tbl_mobil::distinct()->pluck('merek')->filter()->sort()->values();
        $pabrikan = tbl_mobil::distinct()->pluck('pabrikan')->filter()->sort()->values();
        $warna = tbl_mobil::distinct()->pluck('warna')->filter()->sort()->values();
        $tahun = tbl_mobil::distinct()->pluck('tahun')->filter()->sort()->values();

        return view('master-data.data_mobil', compact('dataMobil', 'jenis', 'merek', 'pabrikan', 'warna', 'tahun'));
    }

    public function create()
    {
        return view('master-data.data_mobil.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'jenis' => 'nullable|string|max:100',
            'merek' => 'nullable|string|max:225',
            'pabrikan' => 'nullable|string|max:100',
            'warna' => 'nullable|string|max:50',
            'tahun' => 'nullable|integer|min:1900|max:' . date('Y'),
            'no_polisi' => 'nullable|string|max:15',
            'no_rangka' => 'nullable|string|max:50',
            'no_mesin' => 'nullable|string|max:50',
            'no_bpkb' => 'nullable|string|max:50',
            'tgl_berlaku_stnk' => 'nullable|date',
            'file_pic' => 'nullable|string|max:100',
            'aktif' => 'required|in:Y,N',
        ]);

        tbl_mobil::create($request->all());

        return redirect()->route('master-data.data_mobil')
            ->with('success', 'Data mobil berhasil ditambahkan.');
    }

    public function show($id)
    {
        $mobil = tbl_mobil::findOrFail($id);
        return view('master-data.data_mobil.show', compact('mobil'));
    }

    public function edit($id)
    {
        $mobil = tbl_mobil::findOrFail($id);
        return view('master-data.data_mobil.edit', compact('mobil'));
    }

    public function update(Request $request, $id)
    {
        $mobil = tbl_mobil::findOrFail($id);

        $request->validate([
            'nama' => 'required|string|max:255',
            'jenis' => 'nullable|string|max:100',
            'merek' => 'nullable|string|max:225',
            'pabrikan' => 'nullable|string|max:100',
            'warna' => 'nullable|string|max:50',
            'tahun' => 'nullable|integer|min:1900|max:' . date('Y'),
            'no_polisi' => 'nullable|string|max:15',
            'no_rangka' => 'nullable|string|max:50',
            'no_mesin' => 'nullable|string|max:50',
            'no_bpkb' => 'nullable|string|max:50',
            'tgl_berlaku_stnk' => 'nullable|date',
            'file_pic' => 'nullable|string|max:100',
            'aktif' => 'required|in:Y,N',
        ]);

        $mobil->update($request->all());

        return redirect()->route('master-data.data_mobil')
            ->with('success', 'Data mobil berhasil diupdate.');
    }

    public function destroy($id)
    {
        $mobil = tbl_mobil::findOrFail($id);
        $mobil->delete();

        return redirect()->route('master-data.data_mobil')
            ->with('success', 'Data mobil berhasil dihapus.');
    }

    public function export(Request $request)
    {
        return Excel::download(
            new TblMobilExport($request->search, $request->jenis, $request->merek, $request->pabrikan, $request->warna, $request->tahun, $request->status_aktif, $request->status_stnk),
            'data_mobil_' . date('Y-m-d_H-i-s') . '.xlsx'
        );
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        Excel::import(new TblMobilImport, $request->file('file'));

        return redirect()->route('master-data.data_mobil')
            ->with('success', 'Data mobil berhasil diimport.');
    }

    public function downloadTemplate()
    {
        return Excel::download(new TblMobilExport(), 'template_data_mobil.xlsx');
    }
}