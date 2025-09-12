<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\jns_angsuran;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\JnsAngsuranExport;
use App\Imports\JnsAngsuranImport;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

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
        if ($request->filled('min_bulan') || $request->filled('max_bulan')) {
            $min = $request->min_bulan ?? 1;
            $max = $request->max_bulan ?? 120;
            $query->byRangeBulan($min, $max);
        }

        // Sort by
        $sortBy = $request->get('sort_by', 'ket');
        $sortOrder = $request->get('sort_order', 'asc');
        
        if (in_array($sortBy, ['ket', 'aktif'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('ket', 'asc');
        }

        $jnsAngsuran = $query->paginate(15)->withQueryString();

        // Get statistics for summary cards
        $totalAngsuran = jns_angsuran::count();
        $angsuranAktif = jns_angsuran::where('aktif', 'Y')->count();
        $angsuranTidakAktif = jns_angsuran::where('aktif', 'T')->count();
        $jangkaPendek = jns_angsuran::where('ket', '<=', 6)->count();
        $jangkaMenengah = jns_angsuran::where('ket', '>', 6)->where('ket', '<=', 24)->count();
        $jangkaPanjang = jns_angsuran::where('ket', '>', 24)->count();

        return view('master-data.jenis_angsuran', compact('jnsAngsuran', 'totalAngsuran', 'angsuranAktif', 'angsuranTidakAktif', 'jangkaPendek', 'jangkaMenengah', 'jangkaPanjang'));
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

        return redirect()->route('master-data.jenis_angsuran')
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

        return redirect()->route('master-data.jenis_angsuran')
            ->with('success', 'Jenis angsuran berhasil diupdate.');
    }

    public function destroy($id)
    {
        $angsuran = jns_angsuran::findOrFail($id);
        $angsuran->delete();

        return redirect()->route('master-data.jenis_angsuran')
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

        return redirect()->route('master-data.jenis_angsuran')
            ->with('success', 'Jenis angsuran berhasil diimport.');
    }

    public function downloadTemplate()
    {
        return Excel::download(new JnsAngsuranExport(), 'template_jenis_angsuran.xlsx');
    }

    public function print(Request $request)
    {
        $query = jns_angsuran::query();

        // Apply same filters as index
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('status_aktif')) {
            $query->byStatusAktif($request->status_aktif);
        }

        if ($request->filled('kategori')) {
            $query->byKategori($request->kategori);
        }

        if ($request->filled('min_bulan') || $request->filled('max_bulan')) {
            $min = $request->min_bulan ?? 1;
            $max = $request->max_bulan ?? 120;
            $query->byRangeBulan($min, $max);
        }

        $jnsAngsuran = $query->ordered()->get();

        $pdf = PDF::loadView('master-data.jenis_angsuran.print', compact('jnsAngsuran'));
        $pdf->setPaper('A4', 'landscape');
        
        return $pdf->download('jenis_angsuran_' . date('Y-m-d_H-i-s') . '.pdf');
    }
}