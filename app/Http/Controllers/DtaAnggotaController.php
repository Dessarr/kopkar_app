<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\data_anggota;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
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

        // Handle filter jenis kelamin
        if ($request->has('jenis_kelamin') && $request->jenis_kelamin != '') {
            $query->where('jk', $request->jenis_kelamin);
        }

        // Handle filter departement
        if ($request->has('departement') && $request->departement != '') {
            $query->where('departement', $request->departement);
        }

        // Handle filter kota
        if ($request->has('kota') && $request->kota != '') {
            $query->where('kota', $request->kota);
        }

        // Tampilkan anggota aktif
        $dataAnggota = $query->where('aktif', 'Y')->orderBy('nama')->paginate(10);

        // Tampilkan anggota tidak aktif jika diminta
        $dataAnggotaNonAktif = data_anggota::where('aktif', 'N')->orderBy('nama')->paginate(10, ['*'], 'nonaktif');

        // Hitung statistik untuk card informatif
        $totalAnggota = data_anggota::where('aktif', 'Y')->count();
        $totalAktif = data_anggota::where('aktif', 'Y')->count();
        $totalLakiLaki = data_anggota::where('aktif', 'Y')->where('jk', 'L')->count();
        $totalPerempuan = data_anggota::where('aktif', 'Y')->where('jk', 'P')->count();

        // Ambil data untuk filter dropdown
        $departements = data_anggota::where('aktif', 'Y')
            ->whereNotNull('departement')
            ->where('departement', '!=', '')
            ->distinct()
            ->pluck('departement')
            ->sort()
            ->values();

        $kotas = data_anggota::where('aktif', 'Y')
            ->whereNotNull('kota')
            ->where('kota', '!=', '')
            ->distinct()
            ->pluck('kota')
            ->sort()
            ->values();

        return view('master-data.data_anggota', compact(
            'dataAnggota', 
            'dataAnggotaNonAktif', 
            'totalAnggota', 
            'totalAktif', 
            'totalLakiLaki', 
            'totalPerempuan',
            'departements',
            'kotas'
        ));
    }

    public function show($id)
    {
        $anggota = data_anggota::findOrFail($id);
        return view('master-data.data_anggota.show', compact('anggota'));
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
        $id_anggota_auto = $yearMonth . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
        return view('master-data.data_anggota.create', compact('id_anggota_auto'));
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
        $validated['aktif'] = 'Y';
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

        return redirect()->route('master-data.data_anggota.index')
            ->with('success', 'Data anggota berhasil diperbarui');
    }

    public function destroy($id)
    {
        try {
        $anggota = data_anggota::findOrFail($id);
        
        // Hapus file foto jika ada
        if($anggota->file_pic && Storage::disk('public')->exists('anggota/' . $anggota->file_pic)) {
            Storage::disk('public')->delete('anggota/' . $anggota->file_pic);
        }
        
            // Hapus semua data terkait dengan anggota_id
            $this->deleteRelatedData($id);
            
            // Hapus data anggota
        $anggota->delete();

        return redirect()->route('master-data.data_anggota.index')
                ->with('success', 'Data anggota dan semua data terkait berhasil dihapus');
                
        } catch (\Exception $e) {
            return redirect()->route('master-data.data_anggota.index')
                ->with('error', 'Gagal menghapus data anggota: ' . $e->getMessage());
        }
    }
    
    /**
     * Hapus semua data terkait dengan anggota_id
     */
    private function deleteRelatedData($anggotaId)
    {
        // Ambil no_ktp dari anggota yang akan dihapus
        $anggota = data_anggota::find($anggotaId);
        $noKtp = $anggota ? $anggota->no_ktp : null;
        
        if (!$noKtp) {
            return; // Jika tidak ada no_ktp, tidak ada yang perlu dihapus
        }
        
        try {
            // Hapus data pinjaman berdasarkan anggota_id dan no_ktp
            DB::table('tbl_pinjaman_h')->where('anggota_id', $anggotaId)->delete();
            DB::table('tbl_pinjaman_h')->where('no_ktp', $noKtp)->delete();
            
            // Hapus data pinjaman detail berdasarkan pinjam_id
            DB::table('tbl_pinjaman_d')->whereIn('pinjam_id', function($query) use ($anggotaId) {
                $query->select('id')->from('tbl_pinjaman_h')->where('anggota_id', $anggotaId);
            })->delete();
            
            DB::table('tbl_pinjaman_d')->whereIn('pinjam_id', function($query) use ($noKtp) {
                $query->select('id')->from('tbl_pinjaman_h')->where('no_ktp', $noKtp);
            })->delete();
            
            // Hapus data pengajuan berdasarkan anggota_id (jika ada kolom anggota_id)
            if (Schema::hasColumn('tbl_pengajuan', 'anggota_id')) {
                DB::table('tbl_pengajuan')->where('anggota_id', $anggotaId)->delete();
            }
            if (Schema::hasColumn('tbl_pengajuan_penarikan', 'anggota_id')) {
                DB::table('tbl_pengajuan_penarikan')->where('anggota_id', $anggotaId)->delete();
            }
            
            // Hapus data pengajuan berdasarkan no_ktp (jika ada kolom no_ktp)
            if (Schema::hasColumn('tbl_pengajuan', 'no_ktp')) {
                DB::table('tbl_pengajuan')->where('no_ktp', $noKtp)->delete();
            }
            if (Schema::hasColumn('tbl_pengajuan_penarikan', 'no_ktp')) {
                DB::table('tbl_pengajuan_penarikan')->where('no_ktp', $noKtp)->delete();
            }
            
            // Hapus data transaksi simpanan berdasarkan anggota_id
            if (Schema::hasColumn('tbl_trans_sp', 'anggota_id')) {
                DB::table('tbl_trans_sp')->where('anggota_id', $anggotaId)->delete();
            }
            if (Schema::hasColumn('tbl_trans_sp_bayar_temp', 'anggota_id')) {
                DB::table('tbl_trans_sp_bayar_temp')->where('anggota_id', $anggotaId)->delete();
            }
            if (Schema::hasColumn('tbl_trans_sp_temp', 'anggota_id')) {
                DB::table('tbl_trans_sp_temp')->where('anggota_id', $anggotaId)->delete();
            }
            if (Schema::hasColumn('tbl_trans_sps', 'anggota_id')) {
                DB::table('tbl_trans_sps')->where('anggota_id', $anggotaId)->delete();
            }
            
            // Hapus data transaksi simpanan berdasarkan no_ktp
            if (Schema::hasColumn('tbl_trans_sp', 'no_ktp')) {
                DB::table('tbl_trans_sp')->where('no_ktp', $noKtp)->delete();
            }
            if (Schema::hasColumn('tbl_trans_sp_bayar_temp', 'no_ktp')) {
                DB::table('tbl_trans_sp_bayar_temp')->where('no_ktp', $noKtp)->delete();
            }
            if (Schema::hasColumn('tbl_trans_sp_temp', 'no_ktp')) {
                DB::table('tbl_trans_sp_temp')->where('no_ktp', $noKtp)->delete();
            }
            if (Schema::hasColumn('tbl_trans_sps', 'no_ktp')) {
                DB::table('tbl_trans_sps')->where('no_ktp', $noKtp)->delete();
            }
            
            // Hapus data tagihan berdasarkan anggota_id
            if (Schema::hasColumn('tbl_trans_tagihan', 'anggota_id')) {
                DB::table('tbl_trans_tagihan')->where('anggota_id', $anggotaId)->delete();
            }
            if (Schema::hasColumn('tbl_trans_tagihan_khusus2_temp', 'anggota_id')) {
                DB::table('tbl_trans_tagihan_khusus2_temp')->where('anggota_id', $anggotaId)->delete();
            }
            if (Schema::hasColumn('tbl_trans_tagihan_sukarela_temp', 'anggota_id')) {
                DB::table('tbl_trans_tagihan_sukarela_temp')->where('anggota_id', $anggotaId)->delete();
            }
            if (Schema::hasColumn('tbl_trans_tagihan_wajib_temp', 'anggota_id')) {
                DB::table('tbl_trans_tagihan_wajib_temp')->where('anggota_id', $anggotaId)->delete();
            }
            if (Schema::hasColumn('tbl_trans_tagihans', 'anggota_id')) {
                DB::table('tbl_trans_tagihans')->where('anggota_id', $anggotaId)->delete();
            }
            
            // Hapus data tagihan berdasarkan no_ktp
            if (Schema::hasColumn('tbl_trans_tagihan', 'no_ktp')) {
                DB::table('tbl_trans_tagihan')->where('no_ktp', $noKtp)->delete();
            }
            if (Schema::hasColumn('tbl_trans_tagihan_khusus2_temp', 'no_ktp')) {
                DB::table('tbl_trans_tagihan_khusus2_temp')->where('no_ktp', $noKtp)->delete();
            }
            if (Schema::hasColumn('tbl_trans_tagihan_sukarela_temp', 'no_ktp')) {
                DB::table('tbl_trans_tagihan_sukarela_temp')->where('no_ktp', $noKtp)->delete();
            }
            if (Schema::hasColumn('tbl_trans_tagihan_wajib_temp', 'no_ktp')) {
                DB::table('tbl_trans_tagihan_wajib_temp')->where('no_ktp', $noKtp)->delete();
            }
            if (Schema::hasColumn('tbl_trans_tagihans', 'no_ktp')) {
                DB::table('tbl_trans_tagihans')->where('no_ktp', $noKtp)->delete();
            }
            
            // Hapus data toserda berdasarkan anggota_id
            if (Schema::hasColumn('tbl_trans_toserda', 'anggota_id')) {
                DB::table('tbl_trans_toserda')->where('anggota_id', $anggotaId)->delete();
            }
            
            // Hapus data toserda berdasarkan no_ktp
            if (Schema::hasColumn('tbl_trans_toserda', 'no_ktp')) {
                DB::table('tbl_trans_toserda')->where('no_ktp', $noKtp)->delete();
            }
            
            // Hapus data SHU berdasarkan no_ktp
            if (Schema::hasColumn('tbl_shu', 'no_ktp')) {
                DB::table('tbl_shu')->where('no_ktp', $noKtp)->delete();
            }
            
            // Hapus data billing berdasarkan anggota_id
            if (Schema::hasColumn('billing', 'id_anggota')) {
                DB::table('billing')->where('id_anggota', $anggotaId)->delete();
            }
            
            // Hapus data tempo pinjaman berdasarkan anggota_id
            if (Schema::hasColumn('tempo_pinjaman', 'anggota_id')) {
                DB::table('tempo_pinjaman')->where('anggota_id', $anggotaId)->delete();
            }
            
            // Hapus data tempo pinjaman berdasarkan no_ktp
            if (Schema::hasColumn('tempo_pinjaman', 'no_ktp')) {
                DB::table('tempo_pinjaman')->where('no_ktp', $noKtp)->delete();
            }
            
            // Bersihkan data angsuran yang tidak valid sebelum menghapus data pinjaman
            $this->cleanInvalidAngsuranData($noKtp);
            
        } catch (\Exception $e) {
            // Log error tapi tetap lanjutkan proses
            Log::error('Error deleting related data for anggota ' . $anggotaId . ': ' . $e->getMessage());
        }
    }

    /**
     * Bersihkan data angsuran yang tidak valid untuk mencegah bug Pot Gaji
     * Data angsuran dianggap tidak valid jika:
     * - Total pembayaran angsuran > total pinjaman
     * - Jumlah pembayaran per angsuran > total pinjaman
     */
    private function cleanInvalidAngsuranData($noKtp)
    {
        try {
            // Ambil semua pinjaman untuk no_ktp ini
            $pinjamanList = DB::table('tbl_pinjaman_h')
                ->where('no_ktp', $noKtp)
                ->get();

            foreach ($pinjamanList as $pinjaman) {
                $totalPinjaman = $pinjaman->jumlah;
                $pinjamId = $pinjaman->id;

                // Hitung total yang sudah dibayar
                $totalBayar = DB::table('tbl_pinjaman_d')
                    ->where('pinjam_id', $pinjamId)
                    ->sum('jumlah_bayar');

                // Cek apakah ada data angsuran yang tidak valid
                if ($totalBayar > $totalPinjaman) {
                    Log::warning("Invalid angsuran data found for pinjaman ID: $pinjamId, Total Pinjaman: $totalPinjaman, Total Bayar: $totalBayar");
                    
                    // Hapus semua data angsuran yang tidak valid
                    $deletedCount = DB::table('tbl_pinjaman_d')
                        ->where('pinjam_id', $pinjamId)
                        ->delete();
                    
                    Log::info("Cleaned $deletedCount invalid angsuran records for pinjaman ID: $pinjamId");
                }

                // Cek juga data angsuran individual yang tidak masuk akal
                $invalidAngsuran = DB::table('tbl_pinjaman_d')
                    ->where('pinjam_id', $pinjamId)
                    ->where('jumlah_bayar', '>', $totalPinjaman)
                    ->get();

                if ($invalidAngsuran->count() > 0) {
                    Log::warning("Found individual invalid angsuran records for pinjaman ID: $pinjamId");
                    
                    $deletedCount = DB::table('tbl_pinjaman_d')
                        ->where('pinjam_id', $pinjamId)
                        ->where('jumlah_bayar', '>', $totalPinjaman)
                        ->delete();
                    
                    Log::info("Cleaned $deletedCount individual invalid angsuran records for pinjaman ID: $pinjamId");
                }
            }
        } catch (\Exception $e) {
            Log::error('Error cleaning invalid angsuran data for no_ktp ' . $noKtp . ': ' . $e->getMessage());
        }
    }

    /**
     * Method untuk membersihkan data angsuran yang tidak valid di seluruh sistem
     * Bisa dipanggil secara manual untuk cleanup data yang sudah ada
     */
    public function cleanupInvalidAngsuranData()
    {
        try {
            $totalCleaned = 0;
            
            // Ambil semua pinjaman yang memiliki data angsuran
            $pinjamanWithAngsuran = DB::table('tbl_pinjaman_h as ph')
                ->join('tbl_pinjaman_d as pd', 'ph.id', '=', 'pd.pinjam_id')
                ->select('ph.id', 'ph.no_ktp', 'ph.jumlah')
                ->groupBy('ph.id', 'ph.no_ktp', 'ph.jumlah')
                ->get();

            foreach ($pinjamanWithAngsuran as $pinjaman) {
                $totalPinjaman = $pinjaman->jumlah;
                $pinjamId = $pinjaman->id;
                $noKtp = $pinjaman->no_ktp;

                // Hitung total yang sudah dibayar
                $totalBayar = DB::table('tbl_pinjaman_d')
                    ->where('pinjam_id', $pinjamId)
                    ->sum('jumlah_bayar');

                // Cek apakah ada data angsuran yang tidak valid
                if ($totalBayar > $totalPinjaman) {
                    Log::warning("Invalid angsuran data found for pinjaman ID: $pinjamId, No KTP: $noKtp, Total Pinjaman: $totalPinjaman, Total Bayar: $totalBayar");
                    
                    // Hapus semua data angsuran yang tidak valid
                    $deletedCount = DB::table('tbl_pinjaman_d')
                        ->where('pinjam_id', $pinjamId)
                        ->delete();
                    
                    $totalCleaned += $deletedCount;
                    Log::info("Cleaned $deletedCount invalid angsuran records for pinjaman ID: $pinjamId");
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Cleanup completed. Total invalid records cleaned: $totalCleaned",
                'total_cleaned' => $totalCleaned
            ]);

        } catch (\Exception $e) {
            Log::error('Error during cleanup of invalid angsuran data: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error during cleanup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Method untuk validasi data angsuran sebelum menyimpan
     * Bisa dipanggil dari controller lain untuk validasi
     */
    public static function validateAngsuranData($pinjamId, $jumlahBayar)
    {
        try {
            // Ambil data pinjaman
            $pinjaman = DB::table('tbl_pinjaman_h')->where('id', $pinjamId)->first();
            
            if (!$pinjaman) {
                return [
                    'valid' => false,
                    'message' => 'Pinjaman tidak ditemukan'
                ];
            }

            $totalPinjaman = $pinjaman->jumlah;
            
            // Cek apakah jumlah bayar tidak melebihi total pinjaman
            if ($jumlahBayar > $totalPinjaman) {
                return [
                    'valid' => false,
                    'message' => "Jumlah pembayaran (Rp " . number_format($jumlahBayar, 0, ',', '.') . ") melebihi total pinjaman (Rp " . number_format($totalPinjaman, 0, ',', '.') . ")"
                ];
            }

            // Hitung total yang sudah dibayar
            $totalBayar = DB::table('tbl_pinjaman_d')
                ->where('pinjam_id', $pinjamId)
                ->sum('jumlah_bayar');

            // Cek apakah total bayar + jumlah bayar baru tidak melebihi total pinjaman
            if (($totalBayar + $jumlahBayar) > $totalPinjaman) {
                return [
                    'valid' => false,
                    'message' => "Total pembayaran akan melebihi total pinjaman. Sisa pinjaman: Rp " . number_format($totalPinjaman - $totalBayar, 0, ',', '.')
                ];
            }

            return [
                'valid' => true,
                'message' => 'Data angsuran valid'
            ];

        } catch (\Exception $e) {
            Log::error('Error validating angsuran data: ' . $e->getMessage());
            
            return [
                'valid' => false,
                'message' => 'Error validasi: ' . $e->getMessage()
            ];
        }
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