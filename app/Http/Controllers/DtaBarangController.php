<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\tbl_barang;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TblBarangExport;
use App\Imports\TblBarangImport;

class DtaBarangController extends Controller
{
    public function index(Request $request)
    {
        $query = tbl_barang::query();

        // Search functionality
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->byType($request->type);
        }

        // Filter by merk
        if ($request->filled('merk')) {
            $query->byMerk($request->merk);
        }

        // Filter by cabang
        if ($request->filled('cabang')) {
            $query->byCabang($request->cabang);
        }

        // Filter by status stok
        if ($request->filled('status_stok')) {
            $query->byStatusStok($request->status_stok);
        }

        // Filter by harga range
        if ($request->filled('harga_min') && $request->filled('harga_max')) {
            $query->byHargaRange($request->harga_min, $request->harga_max);
        }

        $dataBarang = $query->ordered()->paginate(10);
        
        // Get unique values for filters
        $types = tbl_barang::distinct()->pluck('type')->filter()->sort()->values();
        $merks = tbl_barang::distinct()->pluck('merk')->filter()->sort()->values();
        $cabangs = tbl_barang::distinct()->pluck('id_cabang')->filter()->sort()->values();

        return view('master-data.data_barang', compact('dataBarang', 'types', 'merks', 'cabangs'));
    }

    public function create()
    {
        return view('master-data.data_barang.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nm_barang' => 'required|string|max:255',
            'type' => 'required|string|max:50',
            'merk' => 'required|string|max:50',
            'harga' => 'required|numeric|min:0',
            'jml_brg' => 'required|integer|min:0',
            'ket' => 'required|string|max:255',
            'id_cabang' => 'nullable|string|max:8',
        ]);

        tbl_barang::create($request->all());

        return redirect()->route('master-data.data_barang')
            ->with('success', 'Data barang berhasil ditambahkan.');
    }

    public function show($id)
    {
        $barang = tbl_barang::findOrFail($id);
        return view('master-data.data_barang.show', compact('barang'));
    }

    public function edit($id)
    {
        $barang = tbl_barang::findOrFail($id);
        return view('master-data.data_barang.edit', compact('barang'));
    }

    public function update(Request $request, $id)
    {
        $barang = tbl_barang::findOrFail($id);

        $request->validate([
            'nm_barang' => 'required|string|max:255',
            'type' => 'required|string|max:50',
            'merk' => 'required|string|max:50',
            'harga' => 'required|numeric|min:0',
            'jml_brg' => 'required|integer|min:0',
            'ket' => 'required|string|max:255',
            'id_cabang' => 'nullable|string|max:8',
        ]);

        $barang->update($request->all());

        return redirect()->route('master-data.data_barang')
            ->with('success', 'Data barang berhasil diupdate.');
    }

    public function destroy($id)
    {
        $barang = tbl_barang::findOrFail($id);
        $barang->delete();

        return redirect()->route('master-data.data_barang')
            ->with('success', 'Data barang berhasil dihapus.');
    }

    public function export(Request $request)
    {
        return Excel::download(
            new TblBarangExport($request->search, $request->type, $request->merk, $request->cabang, $request->status_stok),
            'data_barang_' . date('Y-m-d_H-i-s') . '.xlsx'
        );
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        Excel::import(new TblBarangImport, $request->file('file'));

        return redirect()->route('master-data.data_barang')
            ->with('success', 'Data barang berhasil diimport.');
    }

    public function downloadTemplate()
    {
        return Excel::download(new TblBarangExport(), 'template_data_barang.xlsx');
    }
}