<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\data_anggota;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AnggotaExport;

class DtaAnggotaController extends Controller
{
    public function index(Request $request)
    {
        $query = data_anggota::query();

        // Handle search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('no_ktp', 'like', '%' . $search . '%')
                  ->orWhere('departement', 'like', '%' . $search . '%');
            });
        }

        $dataAnggota = $query->orderBy('nama')->paginate(10);
        return view('master-data.data_anggota', compact('dataAnggota'));
    }

    public function show($id)
    {
        $anggota = data_anggota::findOrFail($id);
        return view('master-data.show_data_anggota', compact('anggota'));
    }

    public function create()
    {
        return view('layouts.form.add_data_anggota');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'jk' => 'required|in:L,P',
            'tmp_lahir' => 'required|string|max:255',
            'tgl_lahir' => 'required|date',
            'status' => 'required|string|max:255',
            'agama' => 'required|string|max:255',
            'departement' => 'required|string|max:255',
            'pekerjaan' => 'required|string|max:255',
            'alamat' => 'required|string',
            'kota' => 'required|string|max:255',
            'notelp' => 'required|string|max:20',
            'file_pic' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'no_ktp' => 'required|string|max:20|unique:tbl_anggota',
            'bank' => 'required|string|max:255',
            'nama_pemilik_rekening' => 'required|string|max:255',
            'no_rekening' => 'required|string|max:255',
            'simpanan_wajib' => 'required|string',
            'simpanan_sukarela' => 'required|string',
            'simpanan_khusus_2' => 'required|string'
        ]);

        // Clean and convert simpanan values
        $validated['simpanan_wajib'] = (int) str_replace([',', '.'], '', $request->simpanan_wajib);
        $validated['simpanan_sukarela'] = (int) str_replace([',', '.'], '', $request->simpanan_sukarela);
        $validated['simpanan_khusus_2'] = (int) str_replace([',', '.'], '', $request->simpanan_khusus_2);

        // Set nilai yang tidak diinput
        $validated['identitas'] = $validated['nama'];
        $validated['tgl_daftar'] = date('Y-m-d');
        $validated['jabatan_id'] = 2;
        $validated['aktif'] = 1;
        $validated['pass_word'] = bcrypt($validated['no_ktp']);
        $validated['id_tagihan'] = null;
        $validated['jns_trans'] = null;
        $validated['id_cabang'] = null;

        if($request->hasFile('file_pic')) {
            $file = $request->file('file_pic');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/anggota', $filename);
            $validated['file_pic'] = $filename;
        }

        data_anggota::create($validated);

        return redirect()->route('master-data.data_anggota')
            ->with('success', 'Data anggota berhasil ditambahkan');
    }

    public function edit($id)
    {
        $anggota = data_anggota::findOrFail($id);
        return view('layouts.form.edit_data_anggota', compact('anggota'));
    }

    public function update(Request $request, $id)
    {
        $anggota = data_anggota::findOrFail($id);
        
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'jk' => 'required|in:L,P',
            'tmp_lahir' => 'required|string|max:255',
            'tgl_lahir' => 'required|date',
            'status' => 'required|string|max:255',
            'agama' => 'required|string|max:255',
            'departement' => 'required|string|max:255',
            'pekerjaan' => 'required|string|max:255',
            'alamat' => 'required|string',
            'kota' => 'required|string|max:255',
            'notelp' => 'required|string|max:20',
            'file_pic' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'no_ktp' => 'required|string|max:20|unique:tbl_anggota,no_ktp,'.$id,
            'bank' => 'required|string|max:255',
            'nama_pemilik_rekening' => 'required|string|max:255',
            'no_rekening' => 'required|string|max:255',
            'simpanan_wajib' => 'required|string',
            'simpanan_sukarela' => 'required|string',
            'simpanan_khusus_2' => 'required|string'
        ]);

        // Clean and convert simpanan values - remove thousand separators and convert to integer
        $validated['simpanan_wajib'] = (int) str_replace([',', '.'], '', $request->simpanan_wajib);
        $validated['simpanan_sukarela'] = (int) str_replace([',', '.'], '', $request->simpanan_sukarela);
        $validated['simpanan_khusus_2'] = (int) str_replace([',', '.'], '', $request->simpanan_khusus_2);

        // Set nilai yang tidak diinput
        $validated['identitas'] = $validated['nama'];

        if($request->hasFile('file_pic')) {
            // Hapus file lama jika ada
            if($anggota->file_pic) {
                Storage::delete('public/anggota/' . $anggota->file_pic);
            }
            
            $file = $request->file('file_pic');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/anggota', $filename);
            $validated['file_pic'] = $filename;
        }

        $anggota->update($validated);

        return redirect()->route('master-data.data_anggota')
            ->with('success', 'Data anggota berhasil diperbarui');
    }

    public function destroy($id)
    {
        $anggota = data_anggota::findOrFail($id);
        
        // Hapus file foto jika ada
        if($anggota->file_pic) {
            Storage::delete('public/anggota/' . $anggota->file_pic);
        }
        
        $anggota->delete();

        return redirect()->route('master-data.data_anggota')
            ->with('success', 'Data anggota berhasil dihapus');
    }

    public function export() 
    {
        $fileName = 'data_anggota_' . date('Y-m-d') . '.xlsx';
        
        return Excel::download(new AnggotaExport, $fileName);
    }
}