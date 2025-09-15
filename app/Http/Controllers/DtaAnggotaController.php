<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\data_anggota;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AnggotaExport;

class DtaAnggotaController extends Controller
{
    public function index(Request $request)
    {
        $query = data_anggota::query();

        // Handle search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('no_ktp', 'like', '%' . $search . '%')
                  ->orWhere('departement', 'like', '%' . $search . '%')
                  ->orWhere('kota', 'like', '%' . $search . '%');
            });
        }

        // Hanya tampilkan anggota aktif
        $query->where('aktif', 'Y');

        // Handle jenis kelamin filter
        if ($request->has('jenis_kelamin') && $request->jenis_kelamin) {
            $query->where('jk', $request->jenis_kelamin);
        }

        // Handle departement filter
        if ($request->has('departement') && $request->departement) {
            $query->where('departement', $request->departement);
        }

        // Handle kota filter
        if ($request->has('kota') && $request->kota) {
            $query->where('kota', $request->kota);
        }

        // Get data with pagination
        $dataAnggota = $query->orderBy('nama')->paginate(10)->withQueryString();

        // Get unique values for filter dropdowns
        $departements = data_anggota::distinct()->pluck('departement')->filter()->sort()->values();
        $kotas = data_anggota::distinct()->pluck('kota')->filter()->sort()->values();


        // Calculate statistics for summary cards
        $totalAnggota = data_anggota::count();
        $totalAktif = data_anggota::where('aktif', 'Y')->count();
        $totalLakiLaki = data_anggota::where('jk', 'L')->count();
        $totalPerempuan = data_anggota::where('jk', 'P')->count();

        // Debug: Log filter parameters
        \Log::info('Filter Parameters:', [
            'search' => $request->search,
            'status_aktif' => $request->status_aktif,
            'jenis_kelamin' => $request->jenis_kelamin,
            'departement' => $request->departement,
            'kota' => $request->kota,
            'total_results' => $dataAnggota->total(),
            'total_anggota' => $totalAnggota,
            'total_aktif' => $totalAktif,
            'total_laki' => $totalLakiLaki,
            'total_perempuan' => $totalPerempuan
        ]);

        return view('master-data.data_anggota', compact(
            'dataAnggota', 
            'departements', 
            'kotas',
            'totalAnggota',
            'totalAktif', 
            'totalLakiLaki',
            'totalPerempuan'
        ));
    }

    public function show($id)
    {
        $anggota = data_anggota::findOrFail($id);
        return view('master-data.data_anggota.show', compact('anggota'));
    }

    public function create()
    {
        // Generate ID Anggota otomatis
        $id_anggota_auto = $this->generateIdAnggota();
        
        return view('master-data.data_anggota.create', compact('id_anggota_auto'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'identitas' => 'required|string|max:255',
            'jk' => 'nullable|in:L,P',
            'tmp_lahir' => 'nullable|string|max:225',
            'tgl_lahir' => 'nullable|date',
            'status' => 'nullable|string|max:30',
            'agama' => 'nullable|string|max:30',
            'departement' => 'nullable|string|max:255',
            'pekerjaan' => 'nullable|string|max:30',
            'alamat' => 'nullable|string',
            'kota' => 'nullable|string|max:255',
            'notelp' => 'nullable|string|max:12',
            'file_pic' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'bank' => 'nullable|string|max:50',
            'nama_pemilik_rekening' => 'nullable|string|max:150',
            'no_rekening' => 'nullable|string|max:50',
            'simpanan_wajib' => 'required|numeric|min:0',
            'simpanan_sukarela' => 'required|numeric|min:0',
            'simpanan_khusus_2' => 'required|numeric|min:0',
            'aktif' => 'nullable|in:Y,N',
            'status_bayar' => 'nullable|in:Belum Lunas,Lunas'
        ]);

        // Generate ID Anggota otomatis
        $validated['no_ktp'] = $this->generateIdAnggota();

        // Handle file upload
        if ($request->hasFile('file_pic')) {
            $file = $request->file('file_pic');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/anggota', $filename);
            $validated['file_pic'] = $filename;
        }

        // Set default values
        $validated['aktif'] = $request->aktif ?? 'Y';
        $validated['tgl_daftar'] = $request->tgl_daftar ?? now()->format('Y-m-d');
        $validated['jabatan_id'] = $request->jabatan_id ?? 1;
        $validated['id_tagihan'] = $request->id_tagihan ?? '';
        $validated['jns_trans'] = $request->jns_trans ?? '';
        $validated['status_bayar'] = $request->status_bayar ?? 'Belum Lunas';
        $validated['id_cabang'] = $request->id_cabang ?? '';
        $validated['username'] = $request->username ?? '';
        $validated['pass_word'] = $request->pass_word ?? '';

        data_anggota::create($validated);

        return redirect()->route('master-data.data_anggota.index')
            ->with('success', 'Data anggota berhasil ditambahkan');
    }

    public function edit($id)
    {
        $anggota = data_anggota::findOrFail($id);
        return view('master-data.data_anggota.edit', compact('anggota'));
    }

    public function update(Request $request, $id)
    {
        $anggota = data_anggota::findOrFail($id);
        
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'identitas' => 'required|string|max:255',
            'jk' => 'nullable|in:L,P',
            'tmp_lahir' => 'nullable|string|max:225',
            'tgl_lahir' => 'nullable|date',
            'status' => 'nullable|string|max:30',
            'agama' => 'nullable|string|max:30',
            'departement' => 'nullable|string|max:255',
            'pekerjaan' => 'nullable|string|max:30',
            'alamat' => 'nullable|string',
            'kota' => 'nullable|string|max:255',
            'notelp' => 'nullable|string|max:12',
            'file_pic' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'bank' => 'nullable|string|max:50',
            'nama_pemilik_rekening' => 'nullable|string|max:150',
            'no_rekening' => 'nullable|string|max:50',
            'simpanan_wajib' => 'required|numeric|min:0',
            'simpanan_sukarela' => 'required|numeric|min:0',
            'simpanan_khusus_2' => 'required|numeric|min:0',
            'aktif' => 'required|in:Y,N',
            'status_bayar' => 'nullable|in:Belum Lunas,Lunas'
        ]);

        // Handle file upload
        if ($request->hasFile('file_pic')) {
            // Hapus file lama jika ada
            if($anggota->file_pic && Storage::disk('public')->exists('anggota/' . $anggota->file_pic)) {
                Storage::disk('public')->delete('anggota/' . $anggota->file_pic);
            }
            
            $file = $request->file('file_pic');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/anggota', $filename);
            $validated['file_pic'] = $filename;
        }

        $anggota->update($validated);

        return redirect()->route('master-data.data_anggota.index')
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

        return redirect()->route('master-data.data_anggota.index')
            ->with('success', 'Data anggota berhasil dihapus');
    }

    public function export() 
    {
        $fileName = 'data_anggota_' . date('Y-m-d') . '.xlsx';
        
        return Excel::download(new AnggotaExport, $fileName);
    }


    public function print()
    {
        $dataAnggota = data_anggota::where('aktif', 'Y')->orderBy('nama')->get();
        return view('master-data.data_anggota.print', compact('dataAnggota'));
    }

    /**
     * Generate ID Anggota otomatis dengan format: YYYYMMNNNN
     * Contoh: 2025090004 (2025 September anggota ke 4)
     */
    private function generateIdAnggota()
    {
        $tahun = date('Y');
        $bulan = date('m');
        
        // Ambil nomor urut terakhir untuk bulan ini
        $lastAnggota = data_anggota::where('no_ktp', 'like', $tahun . $bulan . '%')
            ->orderBy('no_ktp', 'desc')
            ->first();
        
        if ($lastAnggota) {
            // Extract nomor urut dari ID terakhir
            $lastNumber = (int) substr($lastAnggota->no_ktp, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        // Format: YYYYMMNNNN
        return $tahun . $bulan . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}