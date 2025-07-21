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

        // Tampilkan anggota aktif
        $dataAnggota = $query->where('aktif', 'Y')->orderBy('nama')->paginate(10);

        // Tampilkan anggota tidak aktif jika diminta
        $dataAnggotaNonAktif = data_anggota::where('aktif', 'N')->orderBy('nama')->paginate(10, ['*'], 'nonaktif');

        return view('master-data.data_anggota', compact('dataAnggota', 'dataAnggotaNonAktif'));
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

        // Generate ID Koperasi otomatis
        $currentYear = date('Y');
        $currentMonth = date('m');
        $yearMonth = $currentYear . $currentMonth;
        
        // Cari nomor urut terakhir untuk bulan ini
        $lastAnggota = data_anggota::where('no_ktp', 'like', $yearMonth . '%')
            ->orderBy('no_ktp', 'desc')
            ->first();
        
        if ($lastAnggota) {
            $lastNumber = (int) substr($lastAnggota->no_ktp, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        $validated['no_ktp'] = $yearMonth . str_pad($newNumber, 4, '0', STR_PAD_LEFT);

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
            $extension = $file->getClientOriginalExtension();
            $filename = $validated['no_ktp'] . ' - photo.' . $extension;
            
            // Pastikan direktori ada
            Storage::disk('public')->makeDirectory('anggota');
            
            // Simpan file
            Storage::disk('public')->putFileAs('anggota', $file, $filename);
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
            'bank' => 'required|string|max:255',
            'nama_pemilik_rekening' => 'required|string|max:255',
            'no_rekening' => 'required|string|max:255',
            'simpanan_wajib' => 'required|string',
            'simpanan_sukarela' => 'required|string',
            'simpanan_khusus_2' => 'required|string',
            'aktif' => 'required|in:1,0',
        ]);

        // Konversi nilai aktif menjadi 'Y' atau 'N'
        $validated['aktif'] = $request->aktif == '1' ? 'Y' : 'N';

        // Clean and convert simpanan values - remove thousand separators and convert to integer
        $validated['simpanan_wajib'] = (int) str_replace([',', '.'], '', $request->simpanan_wajib);
        $validated['simpanan_sukarela'] = (int) str_replace([',', '.'], '', $request->simpanan_sukarela);
        $validated['simpanan_khusus_2'] = (int) str_replace([',', '.'], '', $request->simpanan_khusus_2);

        // Set nilai yang tidak diinput
        $validated['identitas'] = $validated['nama'];

        if($request->hasFile('file_pic')) {
            // Hapus file lama jika ada
            if($anggota->file_pic && Storage::disk('public')->exists('anggota/' . $anggota->file_pic)) {
                Storage::disk('public')->delete('anggota/' . $anggota->file_pic);
            }
            
            $file = $request->file('file_pic');
            $extension = $file->getClientOriginalExtension();
            $filename = $anggota->no_ktp . ' - photo.' . $extension;
            
            // Pastikan direktori ada
            Storage::disk('public')->makeDirectory('anggota');
            
            // Simpan file
            Storage::disk('public')->putFileAs('anggota', $file, $filename);
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
        if($anggota->file_pic && Storage::disk('public')->exists('anggota/' . $anggota->file_pic)) {
            Storage::disk('public')->delete('anggota/' . $anggota->file_pic);
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

    public function nonaktif(Request $request)
    {
        $query = data_anggota::query();
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('no_ktp', 'like', '%' . $search . '%')
                  ->orWhere('departement', 'like', '%' . $search . '%');
            });
        }
        $dataAnggotaNonAktif = $query->where('aktif', 'N')->orderBy('nama')->paginate(10, ['*'], 'nonaktif');
        $tab = 'nonaktif';
        return view('master-data.data_anggota_nonaktif', compact('dataAnggotaNonAktif', 'tab'));
    }
}