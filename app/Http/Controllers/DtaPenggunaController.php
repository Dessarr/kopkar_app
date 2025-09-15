<?php
namespace App\Http\Controllers;

use App\Models\TblUser;
use App\Models\Cabang;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TblUserExport;
use App\Imports\TblUserImport;

class DtaPenggunaController extends Controller
{
    public function index(Request $request)
    {
        $query = TblUser::with('cabang');

        // Search functionality
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by status aktif
        if ($request->filled('status')) {
            $query->where('aktif', $request->status);
        }

        // Filter by level
        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        // Filter by cabang
        if ($request->filled('cabang')) {
            $query->where('id_cabang', $request->cabang);
        }

        $dataPengguna = $query->ordered()->paginate(10);
        
        // Get all data for summary cards (without pagination)
        $allDataPengguna = TblUser::query();
        // Apply same filters for summary
        if ($request->filled('search')) { $allDataPengguna->search($request->search); }
        if ($request->filled('status')) { $allDataPengguna->where('aktif', $request->status); }
        if ($request->filled('level')) { $allDataPengguna->where('level', $request->level); }
        if ($request->filled('cabang')) { $allDataPengguna->where('id_cabang', $request->cabang); }
        $allDataPengguna = $allDataPengguna->get();
        
        // Get unique values for filters
        $levels = TblUser::distinct()->pluck('level')->filter()->sort()->values();
        $cabangs = Cabang::orderBy('nama')->get();

        return view('master-data.data_pengguna', compact('dataPengguna', 'allDataPengguna', 'levels', 'cabangs'));
    }

    public function create()
    {
        $cabangs = Cabang::orderBy('nama')->get();
        return view('master-data.data_pengguna.create', compact('cabangs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'u_name' => 'required|string|max:255|unique:tbl_user,u_name',
            'pass_word' => 'required|string|min:6',
            'id_cabang' => 'required|string|exists:cabang,id_cabang',
            'aktif' => 'required|in:Y,N',
            'level' => 'required|string|in:admin,pinjaman,simpanan,kas,laporan,supervisor,manager',
        ]);

        $data = $request->all();
        $data['pass_word'] = bcrypt($request->pass_word);

        TblUser::create($data);

        return redirect()->route('master-data.data_pengguna.index')
            ->with('success', 'Data pengguna berhasil ditambahkan.');
    }

    public function show($id)
    {
        $pengguna = TblUser::with('cabang')->findOrFail($id);
        return view('master-data.data_pengguna.show', compact('pengguna'));
    }

    public function edit($id)
    {
        $pengguna = TblUser::findOrFail($id);
        $cabangs = Cabang::orderBy('nama')->get();
        return view('master-data.data_pengguna.edit', compact('pengguna', 'cabangs'));
    }

    public function update(Request $request, $id)
    {
        $pengguna = TblUser::findOrFail($id);

        $request->validate([
            'u_name' => 'required|string|max:255|unique:tbl_user,u_name,' . $id,
            'pass_word' => 'nullable|string|min:6',
            'id_cabang' => 'required|string|exists:cabang,id_cabang',
            'aktif' => 'required|in:Y,N',
            'level' => 'required|string|in:admin,pinjaman,simpanan,kas,laporan,supervisor,manager',
        ]);

        $data = $request->all();
        
        // Only update password if provided
        if ($request->filled('pass_word')) {
            $data['pass_word'] = bcrypt($request->pass_word);
        } else {
            unset($data['pass_word']);
        }

        $pengguna->update($data);

        return redirect()->route('master-data.data_pengguna.index')
            ->with('success', 'Data pengguna berhasil diupdate.');
    }

    public function destroy($id)
    {
        $pengguna = TblUser::findOrFail($id);
        $pengguna->delete();

        return redirect()->route('master-data.data_pengguna.index')
            ->with('success', 'Data pengguna berhasil dihapus.');
    }

    public function export(Request $request)
    {
        return Excel::download(
            new TblUserExport($request->search, $request->status, $request->level, $request->cabang),
            'data_pengguna_' . date('Y-m-d_H-i-s') . '.xlsx'
        );
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        Excel::import(new TblUserImport, $request->file('file'));

        return redirect()->route('master-data.data_pengguna.index')
            ->with('success', 'Data pengguna berhasil diimport.');
    }

    public function downloadTemplate()
    {
        return Excel::download(new TblUserExport(), 'template_data_pengguna.xlsx');
    }

    public function print()
    {
        $dataPengguna = TblUser::with('cabang')->orderBy('u_name')->get();
        return view('master-data.data_pengguna.print', compact('dataPengguna'));
    }
}