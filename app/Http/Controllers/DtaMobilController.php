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

        // Get all data for summary cards (without pagination)
        $allDataMobil = tbl_mobil::query();
        
        // Apply same filters for summary
        if ($request->filled('search')) {
            $allDataMobil->search($request->search);
        }
        if ($request->filled('jenis')) {
            $allDataMobil->byJenis($request->jenis);
        }
        if ($request->filled('merek')) {
            $allDataMobil->byMerek($request->merek);
        }
        if ($request->filled('pabrikan')) {
            $allDataMobil->byPabrikan($request->pabrikan);
        }
        if ($request->filled('warna')) {
            $allDataMobil->byWarna($request->warna);
        }
        if ($request->filled('tahun')) {
            $allDataMobil->byTahun($request->tahun);
        }
        if ($request->filled('status_aktif')) {
            $allDataMobil->byStatusAktif($request->status_aktif);
        }
        if ($request->filled('status_stnk')) {
            $allDataMobil->byStatusStnk($request->status_stnk);
        }
        
        $allDataMobil = $allDataMobil->get();

        return view('master-data.data_mobil', compact('dataMobil', 'allDataMobil', 'jenis', 'merek', 'pabrikan', 'warna', 'tahun'));
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
            'merek' => 'nullable|string|max:100',
            'pabrikan' => 'nullable|string|max:100',
            'warna' => 'nullable|string|max:50',
            'tahun' => 'nullable|integer|min:1900|max:' . date('Y'),
            'no_polisi' => 'nullable|string|max:15|unique:tbl_mobil,no_polisi',
            'no_rangka' => 'nullable|string|max:50|unique:tbl_mobil,no_rangka',
            'no_mesin' => 'nullable|string|max:50|unique:tbl_mobil,no_mesin',
            'no_bpkb' => 'nullable|string|max:50|unique:tbl_mobil,no_bpkb',
            'tgl_berlaku_stnk' => 'nullable|date',
            'file_pic' => 'nullable|string|max:100',
            'aktif' => 'required|in:Y,N',
        ], [
            'nama.required' => 'Nama mobil wajib diisi.',
            'nama.max' => 'Nama mobil maksimal 255 karakter.',
            'tahun.min' => 'Tahun tidak boleh kurang dari 1900.',
            'tahun.max' => 'Tahun tidak boleh lebih dari tahun sekarang.',
            'no_polisi.unique' => 'Nomor polisi sudah digunakan.',
            'no_rangka.unique' => 'Nomor rangka sudah digunakan.',
            'no_mesin.unique' => 'Nomor mesin sudah digunakan.',
            'no_bpkb.unique' => 'Nomor BPKB sudah digunakan.',
            'aktif.required' => 'Status aktif wajib dipilih.',
            'aktif.in' => 'Status aktif harus Aktif atau Nonaktif.',
        ]);

        try {
            tbl_mobil::create($request->all());

            return redirect()->route('master-data.data_mobil.index')
                ->with('success', 'Data mobil berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
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
            'merek' => 'nullable|string|max:100',
            'pabrikan' => 'nullable|string|max:100',
            'warna' => 'nullable|string|max:50',
            'tahun' => 'nullable|integer|min:1900|max:' . date('Y'),
            'no_polisi' => 'nullable|string|max:15|unique:tbl_mobil,no_polisi,' . $id,
            'no_rangka' => 'nullable|string|max:50|unique:tbl_mobil,no_rangka,' . $id,
            'no_mesin' => 'nullable|string|max:50|unique:tbl_mobil,no_mesin,' . $id,
            'no_bpkb' => 'nullable|string|max:50|unique:tbl_mobil,no_bpkb,' . $id,
            'tgl_berlaku_stnk' => 'nullable|date',
            'file_pic' => 'nullable|string|max:100',
            'aktif' => 'required|in:Y,N',
        ], [
            'nama.required' => 'Nama mobil wajib diisi.',
            'nama.max' => 'Nama mobil maksimal 255 karakter.',
            'tahun.min' => 'Tahun tidak boleh kurang dari 1900.',
            'tahun.max' => 'Tahun tidak boleh lebih dari tahun sekarang.',
            'no_polisi.unique' => 'Nomor polisi sudah digunakan.',
            'no_rangka.unique' => 'Nomor rangka sudah digunakan.',
            'no_mesin.unique' => 'Nomor mesin sudah digunakan.',
            'no_bpkb.unique' => 'Nomor BPKB sudah digunakan.',
            'aktif.required' => 'Status aktif wajib dipilih.',
            'aktif.in' => 'Status aktif harus Aktif atau Nonaktif.',
        ]);

        try {
            $mobil->update($request->all());

            return redirect()->route('master-data.data_mobil.index')
                ->with('success', 'Data mobil berhasil diupdate.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat mengupdate data: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $mobil = tbl_mobil::findOrFail($id);
            $mobil->delete();

            return redirect()->route('master-data.data_mobil.index')
                ->with('success', 'Data mobil berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage());
        }
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

        return redirect()->route('master-data.data_mobil.index')
            ->with('success', 'Data mobil berhasil diimport.');
    }

    public function downloadTemplate()
    {
        return Excel::download(new TblMobilExport(), 'template_data_mobil.xlsx');
    }

    public function print()
    {
        $dataMobil = tbl_mobil::with(['kas', 'cabang'])->orderBy('nama')->get();
        
        return view('master-data.data_mobil.print', compact('dataMobil'));
    }
}