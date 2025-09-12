<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\data_anggota;
use App\Models\tbl_anggota;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AnggotaExport;
use App\Exports\TblAnggotaExport;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class DtaAnggotaController extends Controller
{
    public function index(Request $request)
    {
        $query = tbl_anggota::query();

        // Handle search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'aktif') {
                $query->aktif();
            } elseif ($request->status === 'tidak_aktif') {
                $query->tidakAktif();
            }
        } else {
            // Default to active members
            $query->aktif();
        }

        // Filter by gender
        if ($request->filled('jenis_kelamin')) {
            $query->byJenisKelamin($request->jenis_kelamin);
        }

        // Filter by department
        if ($request->filled('departement')) {
            $query->byDepartemen($request->departement);
        }

        // Filter by city
        if ($request->filled('kota')) {
            $query->byKota($request->kota);
        }

        // Filter by age range
        if ($request->filled('umur_min') || $request->filled('umur_max')) {
            $query->where(function($q) use ($request) {
                if ($request->filled('umur_min')) {
                    $minDate = now()->subYears($request->umur_min)->format('Y-m-d');
                    $q->where('tgl_lahir', '<=', $minDate);
                }
                if ($request->filled('umur_max')) {
                    $maxDate = now()->subYears($request->umur_max)->format('Y-m-d');
                    $q->where('tgl_lahir', '>=', $maxDate);
                }
            });
        }

        // Filter by registration date range
        if ($request->filled('tgl_daftar_dari') || $request->filled('tgl_daftar_sampai')) {
            $query->where(function($q) use ($request) {
                if ($request->filled('tgl_daftar_dari')) {
                    $q->where('tgl_daftar', '>=', $request->tgl_daftar_dari);
                }
                if ($request->filled('tgl_daftar_sampai')) {
                    $q->where('tgl_daftar', '<=', $request->tgl_daftar_sampai);
                }
            });
        }

        // Sort by
        $sortBy = $request->get('sort_by', 'nama');
        $sortOrder = $request->get('sort_order', 'asc');
        
        if (in_array($sortBy, ['nama', 'no_ktp', 'tgl_daftar', 'departement', 'kota'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('nama', 'asc');
        }

        $dataAnggota = $query->paginate(15)->withQueryString();

        // Get filter options for dropdowns
        $departements = tbl_anggota::select('departement')
            ->whereNotNull('departement')
            ->where('departement', '!=', '')
            ->distinct()
            ->orderBy('departement')
            ->pluck('departement');

        $kota = tbl_anggota::select('kota')
            ->whereNotNull('kota')
            ->where('kota', '!=', '')
            ->distinct()
            ->orderBy('kota')
            ->pluck('kota');

        // Get statistics for summary cards
        $totalAnggota = tbl_anggota::count();
        $anggotaAktif = tbl_anggota::where('aktif', 'Y')->count();
        $anggotaTidakAktif = tbl_anggota::where('aktif', 'N')->count();
        $lakiLaki = tbl_anggota::where('jk', 'L')->count();
        $perempuan = tbl_anggota::where('jk', 'P')->count();

        return view('master-data.data_anggota', compact('dataAnggota', 'departements', 'kota', 'totalAnggota', 'anggotaAktif', 'anggotaTidakAktif', 'lakiLaki', 'perempuan'));
    }

    public function show($id)
    {
        $anggota = data_anggota::findOrFail($id);
        return view('master-data.show_data_anggota', compact('anggota'));
    }

    public function create()
    {
        // Hitung ID Koperasi otomatis berikutnya
        $currentYear = date('Y');
        $currentMonth = date('m');
        $yearMonth = $currentYear . $currentMonth;
        $lastAnggota = data_anggota::where('no_ktp', 'like', $yearMonth . '%')
            ->orderBy('no_ktp', 'desc')
            ->first();
        if ($lastAnggota) {
            $lastNumber = (int) substr($lastAnggota->no_ktp, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        $no_ktp_auto = $yearMonth . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
        return view('layouts.form.add_data_anggota', compact('no_ktp_auto'));
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


    public function nonaktif(Request $request)
    {
        $query = tbl_anggota::query();

        // Handle search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by status - only inactive
        $query->tidakAktif();

        // Filter by gender
        if ($request->filled('jenis_kelamin')) {
            $query->byJenisKelamin($request->jenis_kelamin);
        }

        // Filter by department
        if ($request->filled('departement')) {
            $query->byDepartemen($request->departement);
        }

        // Filter by city
        if ($request->filled('kota')) {
            $query->byKota($request->kota);
        }

        // Sort by
        $sortBy = $request->get('sort_by', 'nama');
        $sortOrder = $request->get('sort_order', 'asc');
        
        if (in_array($sortBy, ['nama', 'no_ktp', 'tgl_daftar', 'departement', 'kota'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('nama', 'asc');
        }

        $dataAnggotaNonAktif = $query->paginate(15)->withQueryString();

        // Get filter options for dropdowns
        $departements = tbl_anggota::select('departement')
            ->whereNotNull('departement')
            ->where('departement', '!=', '')
            ->distinct()
            ->orderBy('departement')
            ->pluck('departement');

        $kota = tbl_anggota::select('kota')
            ->whereNotNull('kota')
            ->where('kota', '!=', '')
            ->distinct()
            ->orderBy('kota')
            ->pluck('kota');

        // Get statistics for summary cards
        $totalTidakAktif = $dataAnggotaNonAktif->total();
        $lakiLakiTidakAktif = $dataAnggotaNonAktif->where('jk', 'L')->count();
        $perempuanTidakAktif = $dataAnggotaNonAktif->where('jk', 'P')->count();

        $tab = 'nonaktif';
        return view('master-data.data_anggota_nonaktif', compact('dataAnggotaNonAktif', 'departements', 'kota', 'tab', 'totalTidakAktif', 'lakiLakiTidakAktif', 'perempuanTidakAktif'));
    }

    public function export(Request $request)
    {
        $query = tbl_anggota::query();

        // Apply same filters as index
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('status')) {
            if ($request->status === 'aktif') {
                $query->aktif();
            } elseif ($request->status === 'tidak_aktif') {
                $query->tidakAktif();
            }
        } else {
            $query->aktif();
        }

        if ($request->filled('jenis_kelamin')) {
            $query->byJenisKelamin($request->jenis_kelamin);
        }

        if ($request->filled('departement')) {
            $query->byDepartemen($request->departement);
        }

        if ($request->filled('kota')) {
            $query->byKota($request->kota);
        }

        $data = $query->orderBy('nama')->get();
        $fileName = 'data_anggota_' . date('Y-m-d_H-i-s') . '.xlsx';

        return Excel::download(new TblAnggotaExport($data), $fileName);
    }

    public function print(Request $request)
    {
        $query = tbl_anggota::query();

        // Apply same filters as index
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('status')) {
            if ($request->status === 'aktif') {
                $query->aktif();
            } elseif ($request->status === 'tidak_aktif') {
                $query->tidakAktif();
            }
        } else {
            $query->aktif();
        }

        if ($request->filled('jenis_kelamin')) {
            $query->byJenisKelamin($request->jenis_kelamin);
        }

        if ($request->filled('departement')) {
            $query->byDepartemen($request->departement);
        }

        if ($request->filled('kota')) {
            $query->byKota($request->kota);
        }

        $dataAnggota = $query->orderBy('nama')->get();

        $pdf = PDF::loadView('master-data.data_anggota.print', compact('dataAnggota'));
        $pdf->setPaper('A4', 'landscape');
        
        return $pdf->stream('data_anggota_' . date('Y-m-d') . '.pdf');
    }
}