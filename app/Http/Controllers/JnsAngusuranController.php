<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\jns_angsuran;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\JnsAngsuranExport;
use App\Imports\JnsAngsuranImport;

class JnsAngusuranController extends Controller
{
    public function index(Request $request)
    {
        $query = jns_angsuran::query();

        // Search functionality
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by status aktif
        if ($request->filled('status_aktif')) {
            $query->byStatusAktif($request->status_aktif);
        }

        // Filter by kategori angsuran
        if ($request->filled('kategori')) {
            $query->byKategori($request->kategori);
        }

        // Filter by range bulan
        if ($request->filled('min_bulan') && $request->filled('max_bulan')) {
            $query->byRangeBulan($request->min_bulan, $request->max_bulan);
        }

        $jnsAngsuran = $query->ordered()->paginate(10);

        // Calculate statistics
        $totalAngsuran = jns_angsuran::count();
        $totalAktif = jns_angsuran::where('aktif', 'Y')->count();
        $totalTidakAktif = jns_angsuran::where('aktif', 'T')->count();
        $totalPendek = jns_angsuran::where('ket', '<=', 6)->count();
        $totalMenengah = jns_angsuran::whereBetween('ket', [7, 24])->count();
        $totalPanjang = jns_angsuran::where('ket', '>', 24)->count();

        return view('master-data.jenis_angsuran', compact('jnsAngsuran', 'totalAngsuran', 'totalAktif', 'totalTidakAktif', 'totalPendek', 'totalMenengah', 'totalPanjang'));
    }

    public function create()
    {
        return view('master-data.jenis_angsuran.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'ket' => 'required|integer|min:1|max:120',
            'aktif' => 'required|in:Y,T',
        ]);

        jns_angsuran::create($request->all());

        return redirect()->route('master-data.jenis_angsuran.index')
            ->with('success', 'Jenis angsuran berhasil ditambahkan.');
    }

    public function show($id)
    {
        $angsuran = jns_angsuran::findOrFail($id);
        return view('master-data.jenis_angsuran.show', compact('angsuran'));
    }

    public function edit($id)
    {
        $angsuran = jns_angsuran::findOrFail($id);
        return view('master-data.jenis_angsuran.edit', compact('angsuran'));
    }

    public function update(Request $request, $id)
    {
        $angsuran = jns_angsuran::findOrFail($id);

        $request->validate([
            'ket' => 'required|integer|min:1|max:120',
            'aktif' => 'required|in:Y,T',
        ]);

        $angsuran->update($request->all());

        return redirect()->route('master-data.jenis_angsuran.index')
            ->with('success', 'Jenis angsuran berhasil diupdate.');
    }

    public function destroy($id)
    {
        $angsuran = jns_angsuran::findOrFail($id);
        $angsuran->delete();

        return redirect()->route('master-data.jenis_angsuran.index')
            ->with('success', 'Jenis angsuran berhasil dihapus.');
    }

    public function export(Request $request)
    {
        return Excel::download(
            new JnsAngsuranExport($request->search, $request->status_aktif, $request->kategori),
            'jenis_angsuran_' . date('Y-m-d_H-i-s') . '.xlsx'
        );
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        Excel::import(new JnsAngsuranImport, $request->file('file'));

        return redirect()->route('master-data.jenis_angsuran.index')
            ->with('success', 'Jenis angsuran berhasil diimport.');
    }

    public function downloadTemplate()
    {
        return Excel::download(new JnsAngsuranExport(), 'template_jenis_angsuran.xlsx');
    }

    public function print()
    {
        $jnsAngsuran = jns_angsuran::orderBy('ket')->get();
        return view('master-data.jenis_angsuran.print', compact('jnsAngsuran'));
    }
}