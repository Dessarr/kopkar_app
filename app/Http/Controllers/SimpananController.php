<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TransaksiSimpanan;
use App\Models\data_anggota;
use App\Models\jns_simpan;
use App\Models\NamaKasTbl;
use App\Models\TblTransSpTemp;
use App\Models\TblTransTagihan;
use App\Imports\SetoranImport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class SimpananController extends Controller
{
    public function setoranTunai(Request $request)
    {
        $dataAnggota = data_anggota::where('aktif', 'Y')->get();
        $jenisSimpanan = jns_simpan::all();
        $dataKas = NamaKasTbl::where('aktif', 'Y')->where('tmpl_simpan', 'Y')->get();
        
        // Get filter parameters
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $search = $request->get('search');
        
        // Build query
        $query = TransaksiSimpanan::with(['anggota', 'jenisSimpanan'])
            ->where('akun', 'Setoran')
            ->orderBy('tgl_transaksi', 'desc')
            ->orderBy('id', 'desc');
        
        // Apply date filter
        if ($startDate && $endDate) {
            $query->whereBetween('tgl_transaksi', [$startDate, $endDate]);
        }
        
        // Apply search filter
        if ($search) {
            $search = trim($search);
            if (preg_match('/^TRD\d+$/i', $search)) {
                $id = (int) str_replace(['TRD', 'trd'], '', $search);
                $query->where('id', $id);
            } elseif (is_numeric($search)) {
                $query->where('id', $search);
            } elseif (preg_match('/^AG\d+$/i', $search)) {
                $memberId = (int) str_replace(['AG', 'ag'], '', $search);
                $query->whereHas('anggota', function($q) use ($memberId) {
                    $q->where('id', $memberId);
                });
            } else {
                $query->where(function($q) use ($search) {
                    // Search in anggota relationship (for data with valid anggota_id)
                    $q->whereHas('anggota', function($subQ) use ($search) {
                        $subQ->where('nama', 'like', "%{$search}%")
                             ->orWhere('no_ktp', 'like', "%{$search}%");
                    })
                    // Also search directly in tbl_trans_sp.no_ktp (for data with anggota_id = NULL)
                    ->orWhere('no_ktp', 'like', "%{$search}%");
                });
            }
        }
        
        $transaksiSetoran = $query->paginate(10);
        
        return view('simpanan.setoran_tunai', compact(
            'dataAnggota', 
            'jenisSimpanan', 
            'dataKas', 
            'transaksiSetoran',
            'startDate',
            'endDate',
            'search'
        ));
    }

    public function penarikanTunai()
    {

            $dataAnggota = data_anggota::where('aktif', 'Y')->paginate(10);
            $jenisSimpanan = jns_simpan::all();
            $dataKas = DataKas::all();
            $transaksiPenarikan = TransaksiSimpanan::where('akun', 'penarikan')
                ->orderBy('update_data', 'desc')
                ->paginate(10);
            
            return view('simpanan.penarikan_tunai', compact('dataAnggota', 'jenisSimpanan', 'dataKas', 'transaksiPenarikan'));
        
    }

    public function storeSetoran(Request $request)
    {
        // Debug: Log the received data
        \Log::info('StoreSetoran Request Data:', $request->all());
        
        $request->validate([
            'tgl_transaksi' => 'required|date',
            'no_ktp' => 'required|string',
            'anggota_id' => 'required|exists:tbl_anggota,id',
            'jenis_id' => 'required|exists:jns_simpan,id',
            'jumlah' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
            'akun' => 'required|string',
            'dk' => 'required|string',
            'kas_id' => 'required|exists:nama_kas_tbl,id',
            'nama_penyetor' => 'required|string',
            'no_identitas' => 'required|string',
            'alamat' => 'required|string',
            'id_cabang' => 'required|string'
        ]);

        try {
            DB::beginTransaction();
            
            $anggota = data_anggota::where('no_ktp', $request->no_ktp)->first();
            
            if (!$anggota) {
                throw new \Exception('Anggota dengan No KTP ' . $request->no_ktp . ' tidak ditemukan');
            }
            
            $transaksi = TransaksiSimpanan::create([
                'tgl_transaksi' => $request->tgl_transaksi,
                'no_ktp' => $request->no_ktp,
                'anggota_id' => $anggota->id, // Pastikan anggota_id diisi dengan benar
                'jenis_id' => $request->jenis_id,
                'jumlah' => $request->jumlah,
                'keterangan' => $request->keterangan ?? 'Setoran Tunai',
                'akun' => $request->akun,
                'dk' => $request->dk,
                'kas_id' => $request->kas_id,
                'update_data' => now(),
                'user_name' => Auth::user()->u_name ?? 'admin',
                'nama_penyetor' => $request->nama_penyetor,
                'no_identitas' => $request->no_identitas,
                'alamat' => $request->alamat,
                'id_cabang' => $request->id_cabang
            ]);

            DB::commit();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data setoran tunai berhasil disimpan'
                ]);
            }
            
            return redirect()->back()->with('success', 'Setoran tunai berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollback();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ]);
            }
            
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function storePenarikan(Request $request)
    {
        try {
            DB::beginTransaction();

        $request->validate([
            'tgl_transaksi' => 'required|date',
            'no_ktp' => 'required|string',
            'anggota_id' => 'required|exists:tbl_anggota,id',
            'jenis_id' => 'required|exists:jns_simpan,id',
            'jumlah' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
            'akun' => 'required|string',
            'dk' => 'required|string',
            'kas_id' => 'required|exists:data_kas,id',
            'nama_penyetor' => 'required|string',
            'no_identitas' => 'required|string',
            'alamat' => 'required|string',
            'id_cabang' => 'required|string'
        ]);

            $transaksi = TransaksiSimpanan::create([
                'tgl_transaksi' => $request->tgl_transaksi,
                'no_ktp' => $request->no_ktp,
                'anggota_id' => $request->anggota_id,
                'jenis_id' => $request->jenis_id,
                'jumlah' => $request->jumlah,
                'keterangan' => $request->keterangan ?? 'Penarikan Tunai',
                'akun' => $request->akun,
                'dk' => $request->dk,
                'kas_id' => $request->kas_id,
                'update_data' => now(),
                'user_name' => Auth::user()->name ?? 'admin',
                'nama_penyetor' => $request->nama_penyetor,
                'no_identitas' => $request->no_identitas,
                'alamat' => $request->alamat,
                'id_cabang' => $request->id_cabang
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Penarikan tunai berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function setoranUpload()
    {
        try {
            $dataAnggota = data_anggota::where('aktif', 'Y')->paginate(10);
            $jenisSimpanan = jns_simpan::all();
            $dataKas = DataKas::all();
            $transaksiUpload = TblTransSpTemp::orderBy('created_at', 'desc')->paginate(10);
            
            return view('simpanan.setoran_upload', compact('dataAnggota', 'jenisSimpanan', 'dataKas', 'transaksiUpload'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Get anggota photo for API
     */
    public function getAnggotaPhoto($id)
    {
        try {
            $anggota = data_anggota::find($id);
            
            if (!$anggota) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anggota tidak ditemukan'
                ], 404);
            }

            $photoUrl = null;
            if ($anggota->file_pic && file_exists(public_path('uploads/anggota/' . $anggota->file_pic))) {
                $photoUrl = asset('uploads/anggota/' . $anggota->file_pic);
            }

            return response()->json([
                'status' => 'success',
                'photo_url' => $photoUrl,
                'anggota' => [
                    'id' => $anggota->id,
                    'nama' => $anggota->nama,
                    'no_ktp' => $anggota->no_ktp
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function uploadSetoran(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:2048',
            'jenis_id' => 'required|exists:jns_simpan,id',
            'kas_id' => 'required|exists:nama_kas_tbl,id',
            'tgl_transaksi' => 'required|date'
        ]);

        try {
            DB::beginTransaction();

            $file = $request->file('file');
            $rows = Excel::toArray(new SetoranImport, $file)[0];

            // Remove header row
            array_shift($rows);

            foreach ($rows as $row) {
                if (!isset($row[0]) || !isset($row[1])) {
                    continue; // Skip invalid rows
                }

                $anggota = data_anggota::where('no_ktp', $row[0])->where('aktif', 'Y')->first();
                
                if ($anggota) {
                    TblTransSpTemp::create([
                        'tgl_transaksi' => $request->tgl_transaksi,
                        'no_ktp' => $row[0],
                        'anggota_id' => $anggota->id,
                        'jenis_id' => $request->jenis_id,
                        'jumlah' => $row[1],
                        'keterangan' => $row[2] ?? 'Setoran Upload',
                        'akun' => 'setoran',
                        'dk' => 'D',
                        'kas_id' => $request->kas_id,
                        'user_name' => Auth::user()->name ?? 'admin',
                        'id_cabang' => $anggota->id_cabang
                    ]);
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'File berhasil diupload');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function prosesSetoran(Request $request)
    {
        try {
            DB::beginTransaction();

            $tempSetorans = TblTransSpTemp::all();

            if ($tempSetorans->isEmpty()) {
                return redirect()->back()->with('error', 'Tidak ada data setoran untuk diproses');
            }

            foreach ($tempSetorans as $setoran) {
                TransaksiSimpanan::create([
                    'tgl_transaksi' => $setoran->tgl_transaksi,
                    'no_ktp' => $setoran->no_ktp,
                    'anggota_id' => $setoran->anggota_id,
                    'jenis_id' => $setoran->jenis_id,
                    'jumlah' => $setoran->jumlah,
                    'keterangan' => $setoran->keterangan,
                    'akun' => $setoran->akun,
                    'dk' => $setoran->dk,
                    'kas_id' => $setoran->kas_id,
                    'update_data' => now(),
                    'user_name' => Auth::user()->name ?? 'admin',
                    'id_cabang' => $setoran->id_cabang
                ]);
            }

            // Clear temporary data
            TblTransSpTemp::truncate();

            DB::commit();
            return redirect()->back()->with('success', 'Setoran berhasil diproses');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function tagihan()
    {
        try {
            $dataAnggota = data_anggota::where('aktif', 'Y')->paginate(10);
            $jenisSimpanan = jns_simpan::all();
            $tagihan = TblTransTagihan::with(['anggota', 'jenisSimpanan'])
                ->orderBy('tgl_transaksi', 'desc')
                ->paginate(10);
            
            return view('simpanan.tagihan', compact('dataAnggota', 'jenisSimpanan', 'tagihan'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function storeTagihan(Request $request)
    {
        try {
            $request->validate([
                'tgl_transaksi' => 'required|date',
                'anggota_id' => 'required|exists:tbl_anggota,id',
                'jenis_id' => 'required|exists:jns_simpan,id',
                'jumlah' => 'required|numeric|min:0',
                'keterangan' => 'nullable|string',
                'jatuh_tempo' => 'required|date|after:tgl_transaksi'
            ]);

            DB::beginTransaction();

            $anggota = data_anggota::findOrFail($request->anggota_id);

            TblTransTagihan::create([
                'tgl_transaksi' => $request->tgl_transaksi,
                'anggota_id' => $request->anggota_id,
                'jenis_id' => $request->jenis_id,
                'jumlah' => $request->jumlah,
                'keterangan' => $request->keterangan ?? 'Tagihan Simpanan',
                'jatuh_tempo' => $request->jatuh_tempo,
                'status' => 'belum_bayar',
                'user_name' => Auth::user()->name ?? 'admin',
                'id_cabang' => $anggota->id_cabang
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Tagihan berhasil dibuat');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function updateSetoran(Request $request, $id)
    {
        $request->validate([
            'tgl_transaksi' => 'required|date',
            'jenis_id' => 'required|exists:jns_simpan,id',
            'jumlah' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
            'akun' => 'required|string',
            'dk' => 'required|string',
            'kas_id' => 'required|exists:nama_kas_tbl,id',
            'nama_penyetor' => 'required|string',
            'no_identitas' => 'required|string',
            'alamat' => 'required|string',
            'id_cabang' => 'required|string'
        ]);

        try {
            $transaksi = TransaksiSimpanan::findOrFail($id);
            $transaksi->tgl_transaksi = $request->tgl_transaksi;
            $transaksi->jenis_id = $request->jenis_id;
            $transaksi->jumlah = $request->jumlah;
            $transaksi->keterangan = $request->keterangan;
            $transaksi->akun = $request->akun;
            $transaksi->dk = $request->dk;
            $transaksi->kas_id = $request->kas_id;
            $transaksi->nama_penyetor = $request->nama_penyetor;
            $transaksi->no_identitas = $request->no_identitas;
            $transaksi->alamat = $request->alamat;
            $transaksi->id_cabang = $request->id_cabang;
            $transaksi->update_data = now();
            $transaksi->user_name = Auth::user()->u_name ?? 'admin';
            $transaksi->save();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data setoran tunai berhasil diupdate'
                ]);
            }
            
            return redirect()->back()->with('success', 'Setoran tunai berhasil diupdate');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ]);
            }
            
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function deleteSetoran(Request $request, $id)
    {
        try {
            $transaksi = TransaksiSimpanan::findOrFail($id);
            $transaksi->delete();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data setoran tunai berhasil dihapus'
                ]);
            }
            
            return redirect()->back()->with('success', 'Setoran tunai berhasil dihapus');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ]);
            }
            
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function importSetoran(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:2048'
        ]);

        try {
            DB::beginTransaction();
            
            $file = $request->file('file');
            $rows = Excel::toArray(new SetoranImport, $file)[0];
            
            // Remove header row
            array_shift($rows);
            
            $successCount = 0;
            $errorCount = 0;
            
            foreach ($rows as $row) {
                try {
                    if (!isset($row[0]) || !isset($row[1]) || !isset($row[2]) || !isset($row[3])) {
                        $errorCount++;
                        continue;
                    }
                    
                    $tgl_transaksi = \Carbon\Carbon::parse($row[0])->format('Y-m-d H:i:s');
                    $no_ktp = $row[1];
                    $jenis_id = (int) $row[2];
                    $jumlah = (float) $row[3];
                    $keterangan = $row[4] ?? 'Setoran Import';
                    
                    $anggota = data_anggota::where('no_ktp', $no_ktp)->where('aktif', 'Y')->first();
                    
                    if (!$anggota) {
                        $errorCount++;
                        continue;
                    }
                    
                    TransaksiSimpanan::create([
                        'tgl_transaksi' => $tgl_transaksi,
                        'no_ktp' => $no_ktp,
                        'anggota_id' => $anggota->id,
                        'jenis_id' => $jenis_id,
                        'jumlah' => $jumlah,
                        'keterangan' => $keterangan,
                        'akun' => 'Setoran',
                        'dk' => 'D',
                        'kas_id' => 1,
                        'update_data' => now(),
                        'user_name' => 'admin',
                        'nama_penyetor' => $anggota->nama,
                        'no_identitas' => $anggota->no_ktp,
                        'alamat' => $anggota->alamat ?? '',
                        'id_cabang' => $anggota->id_cabang ?? ''
                    ]);
                    
                    $successCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                }
            }
            
            DB::commit();
            
            return redirect()->back()->with('success', 
                "Import selesai. Berhasil: {$successCount}, Gagal: {$errorCount}");
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function exportSetoran(Request $request)
    {
        // Implementation for export functionality
        return response()->json(['message' => 'Export functionality not implemented yet']);
    }

    public function cetakNota($id)
    {
        // Implementation for print nota functionality
        return response()->json(['message' => 'Print nota functionality not implemented yet']);
    }

    public function getAnggotaByKtp($noKtp)
    {
        try {
            $anggota = data_anggota::where('no_ktp', $noKtp)
                ->where('aktif', 'Y')
                ->first();
            
            if ($anggota) {
                return response()->json([
                    'success' => true,
                    'data' => $anggota
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Anggota tidak ditemukan'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    public function pengajuanPenarikan()
    {
        // Contoh: ambil data pengajuan penarikan dari model terkait (misal: data_pengajuan_penarikan)
        $dataPengajuan = \App\Models\data_pengajuan_penarikan::paginate(10);
        
        // Get jenis simpanan for filter dropdown - always get this
        try {
            $jenisSimpanan = \App\Models\jns_simpan::where('tampil', 'Y')->get();
        } catch (\Exception $e) {
            // If there's an error, provide empty collection
            $jenisSimpanan = collect([]);
        }
        
        return view('simpanan.pengajuan_penarikan', compact('dataPengajuan', 'jenisSimpanan'));
    }
}