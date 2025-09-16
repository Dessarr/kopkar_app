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
use Illuminate\Support\Facades\Log;
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
            // Jika start_date dan end_date sama, gunakan whereDate
            if ($startDate === $endDate) {
                $query->whereDate('tgl_transaksi', $startDate);
            } else {
                // Jika berbeda, gunakan whereBetween dengan waktu penuh
                $query->whereBetween('tgl_transaksi', [
                    $startDate . ' 00:00:00', 
                    $endDate . ' 23:59:59'
                ]);
            }
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

    public function penarikanTunai(Request $request)
    {
        try {
            // Get filter parameters
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');
            $search = $request->get('search');
            
            // Build query for penarikan (withdrawal transactions)
            $query = TransaksiSimpanan::with(['anggota', 'jenisSimpanan'])
                ->where('akun', 'Penarikan')
                ->orderBy('tgl_transaksi', 'desc')
                ->orderBy('id', 'desc');
            
            // Apply date filter
            if ($startDate && $endDate) {
                if ($startDate === $endDate) {
                    $query->whereDate('tgl_transaksi', $startDate);
                } else {
                    $query->whereBetween('tgl_transaksi', [
                        $startDate . ' 00:00:00', 
                        $endDate . ' 23:59:59'
                    ]);
                }
            }
            
            // Apply search filter - similar to tagihan logic
            if ($search) {
                // Check if search is numeric (ID) or text (name/ktp)
                if (is_numeric($search)) {
                    // For numeric search, check both id and anggota_id
                    $query->where(function($q) use ($search) {
                        $q->where('id', $search)
                          ->orWhere('anggota_id', $search);
                    });
                } else {
                    // Case-insensitive search for text
                    $query->whereHas('anggota', function($q) use ($search) {
                        $q->whereRaw('LOWER(nama) LIKE ?', ['%' . strtolower($search) . '%'])
                          ->orWhereRaw('LOWER(no_ktp) LIKE ?', ['%' . strtolower($search) . '%']);
                    });
                }
            }
            
            $transaksiPenarikan = $query->paginate(10);
            
            // Get data for dropdowns
            $dataAnggota = data_anggota::where('aktif', 'Y')->get();
            $jenisSimpanan = jns_simpan::where('tampil', 'Y')->get();
            $dataKas = NamaKasTbl::where('aktif', 'Y')->where('tmpl_penarikan', 'Y')->get();
            
            
            return view('simpanan.penarikan_tunai', compact(
                'transaksiPenarikan', 'dataAnggota', 'jenisSimpanan', 'dataKas',
                'startDate', 'endDate', 'search'
            ));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function storeSetoran(Request $request)
    {
        // Debug: Log the received data
        Log::info('StoreSetoran Request Data:', $request->all());
        
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
        // Clean jumlah field before validation (remove commas and dots)
        $cleanJumlah = str_replace([',', '.'], '', $request->jumlah);
        $request->merge(['jumlah' => $cleanJumlah]);

        $request->validate([
            'tgl_transaksi' => 'required|date',
            'no_ktp' => 'required|exists:tbl_anggota,no_ktp',
            'anggota_id' => 'required|exists:tbl_anggota,id',
            'jenis_id' => 'required|exists:jns_simpan,id',
            'jumlah' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
            'akun' => 'required|string',
            'dk' => 'required|string',
            'kas_id' => 'required|exists:nama_kas_tbl,id',
            'nama_penyetor' => 'required|string',
            'no_identitas' => 'required|string',
            'alamat' => 'required|string'
        ], [
            'jumlah.numeric' => 'Jumlah harus berupa angka',
            'jumlah.min' => 'Jumlah harus lebih dari 0',
            'jenis_id.exists' => 'Jenis simpanan tidak valid',
            'no_ktp.exists' => 'No KTP tidak ditemukan',
            'anggota_id.exists' => 'Anggota tidak ditemukan',
            'kas_id.exists' => 'Kas tidak ditemukan'
        ]);

        try {
            DB::beginTransaction();

            $anggota = data_anggota::findOrFail($request->anggota_id);

            $transaksi = TransaksiSimpanan::create([
                'tgl_transaksi' => $request->tgl_transaksi,
                'no_ktp' => $request->no_ktp,
                'anggota_id' => $request->anggota_id,
                'jenis_id' => $request->jenis_id,
                'jumlah' => $cleanJumlah,
                'keterangan' => $request->keterangan ?? 'Penarikan Tunai',
                'akun' => 'Penarikan',
                'dk' => 'K', // Kredit untuk penarikan
                'kas_id' => $request->kas_id,
                'update_data' => now(),
                'user_name' => Auth::user()->u_name ?? 'admin',
                'nama_penyetor' => $request->nama_penyetor,
                'no_identitas' => $request->no_identitas,
                'alamat' => $request->alamat,
            ]);

            DB::commit();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Penarikan tunai berhasil disimpan'
                ]);
            }
            
            return redirect()->back()->with('success', 'Penarikan tunai berhasil disimpan');
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

    public function updatePenarikan(Request $request, $id)
    {
        // Clean jumlah field before validation (remove commas and dots)
        $cleanJumlah = str_replace([',', '.'], '', $request->jumlah);
        $request->merge(['jumlah' => $cleanJumlah]);

        $request->validate([
            'tgl_transaksi' => 'required|date',
            'no_ktp' => 'required|exists:tbl_anggota,no_ktp',
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
        ], [
            'jumlah.numeric' => 'Format jumlah tidak valid. Gunakan angka saja.',
            'jumlah.min' => 'Jumlah harus lebih dari 0.',
            'jenis_id.exists' => 'Jenis simpanan tidak valid.',
            'no_ktp.exists' => 'Anggota tidak ditemukan.',
            'anggota_id.exists' => 'ID anggota tidak valid.',
            'kas_id.exists' => 'Kas tidak ditemukan.'
        ]);

        try {
            $transaksi = TransaksiSimpanan::findOrFail($id);
            $transaksi->tgl_transaksi = $request->tgl_transaksi;
            $transaksi->no_ktp = $request->no_ktp;
            $transaksi->anggota_id = $request->anggota_id;
            $transaksi->jenis_id = $request->jenis_id;
            $transaksi->jumlah = $cleanJumlah;
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
                    'message' => 'Data penarikan tunai berhasil diupdate'
                ]);
            }
            
            return redirect()->back()->with('success', 'Penarikan tunai berhasil diupdate');
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

    public function deletePenarikan(Request $request, $id)
    {
        try {
            $transaksi = TransaksiSimpanan::findOrFail($id);
            $transaksi->delete();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data penarikan tunai berhasil dihapus'
                ]);
            }
            
            return redirect()->back()->with('success', 'Penarikan tunai berhasil dihapus');
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

    public function exportPenarikan(Request $request)
    {
        try {
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');
            $search = $request->get('search');

            $query = TransaksiSimpanan::with(['anggota', 'jenisSimpanan'])
                ->where('akun', 'Penarikan');

            if ($startDate && $endDate) {
                if ($startDate === $endDate) {
                    $query->whereDate('tgl_transaksi', $startDate);
                } else {
                    $query->whereBetween('tgl_transaksi', [
                        $startDate . ' 00:00:00',
                        $endDate . ' 23:59:59'
                    ]);
                }
            }

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->whereHas('anggota', function($subQ) use ($search) {
                        $subQ->where('nama', 'like', "%{$search}%")
                             ->orWhere('no_ktp', 'like', "%{$search}%");
                    })->orWhere('id', 'like', "%{$search}%");
                });
            }

            $penarikan = $query->orderBy('tgl_transaksi', 'desc')->get();

            $filename = 'penarikan_tunai_' . date('Y-m-d_H-i-s') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($penarikan) {
                $file = fopen('php://output', 'w');
                
                // Header CSV
                fputcsv($file, [
                    'No',
                    'Kode Transaksi',
                    'Tanggal Transaksi',
                    'ID Anggota',
                    'Nama Anggota',
                    'No KTP',
                    'Jenis Penarikan',
                    'Jumlah',
                    'Keterangan',
                    'User'
                ]);

                $no = 1;
                foreach ($penarikan as $p) {
                    fputcsv($file, [
                        $no++,
                        'TRK' . str_pad($p->id, 5, '0', STR_PAD_LEFT),
                        $p->tgl_transaksi ? \Carbon\Carbon::parse($p->tgl_transaksi)->format('d/m/Y H:i') : '-',
                        'AG' . str_pad($p->anggota ? $p->anggota->id : 0, 4, '0', STR_PAD_LEFT),
                        $p->anggota ? $p->anggota->nama : 'N/A',
                        $p->no_ktp ?? 'N/A',
                        $p->jenisSimpanan ? $p->jenisSimpanan->jns_simpan : 'N/A',
                        number_format($p->jumlah ?? 0, 0, ',', '.'),
                        $p->keterangan ?? '',
                        $p->user_name ?? '-'
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat export: ' . $e->getMessage());
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



    public function tagihan(Request $request)
    {
        try {
            // Get filter parameters
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');
            $search = $request->get('search');
            
            // Build query
            $query = TblTransTagihan::with(['anggota', 'jenisSimpanan'])
                ->whereIn('jenis_id', [8, 31, 32, 40, 41, 51, 52]); // Filter jenis simpanan yang ditagih
            
            // Apply date filter
            if ($startDate && $endDate) {
                if ($startDate === $endDate) {
                    $query->whereDate('tgl_transaksi', $startDate);
                } else {
                    $query->whereBetween('tgl_transaksi', [
                        $startDate . ' 00:00:00',
                        $endDate . ' 23:59:59'
                    ]);
                }
            }

            // Apply search filter - fixed logic for tagihan
            if ($search) {
                // Check if search is numeric (ID) or text (name/ktp)
                if (is_numeric($search)) {
                    // For numeric search, check both id and anggota_id
                    $query->where(function($q) use ($search) {
                        $q->where('id', $search)
                          ->orWhere('anggota_id', $search);
                    });
                } else {
                    // Case-insensitive search for text
                    $query->whereHas('anggota', function($q) use ($search) {
                        $q->whereRaw('LOWER(nama) LIKE ?', ['%' . strtolower($search) . '%'])
                          ->orWhereRaw('LOWER(no_ktp) LIKE ?', ['%' . strtolower($search) . '%']);
                    });
                }
            }
            
            $tagihan = $query->orderBy('tgl_transaksi', 'desc')->paginate(10);
            
            // Get data for dropdowns
            $dataAnggota = data_anggota::where('aktif', 'Y')->get();
            $jenisSimpanan = jns_simpan::whereIn('id', [8, 31, 32, 40, 41, 51, 52])->get();
            
            return view('simpanan.tagihan', compact(
                'tagihan', 'dataAnggota', 'jenisSimpanan', 
                'startDate', 'endDate', 'search'
            ));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function storeTagihan(Request $request)
    {
        // Clean jumlah field before validation (remove commas and dots)
        $cleanJumlah = str_replace([',', '.'], '', $request->jumlah);
        $request->merge(['jumlah' => $cleanJumlah]);

            $request->validate([
                'tgl_transaksi' => 'required|date',
            'no_ktp' => 'required|exists:tbl_anggota,no_ktp',
                'anggota_id' => 'required|exists:tbl_anggota,id',
                'jenis_id' => 'required|exists:jns_simpan,id',
                'jumlah' => 'required|numeric|min:0',
                'keterangan' => 'nullable|string'
        ], [
            'jumlah.numeric' => 'Jumlah harus berupa angka',
            'jumlah.min' => 'Jumlah harus lebih dari 0',
            'jenis_id.exists' => 'Jenis simpanan tidak valid',
            'no_ktp.exists' => 'No KTP tidak ditemukan',
            'anggota_id.exists' => 'Anggota tidak ditemukan'
        ]);

        try {
            DB::beginTransaction();

            $anggota = data_anggota::findOrFail($request->anggota_id);

            TblTransTagihan::create([
                'tgl_transaksi' => $request->tgl_transaksi,
                'no_ktp' => $request->no_ktp,
                'anggota_id' => $request->anggota_id,
                'jenis_id' => $request->jenis_id,
                'jumlah' => $cleanJumlah,
                'keterangan' => $request->keterangan ?? 'Tagihan Simpanan',
                'akun' => 'Tagihan',
                'dk' => 'D',
                'user_name' => Auth::user()->name ?? 'admin',
                'id_cabang' => $anggota->id_cabang ?? 'CB0001'
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Tagihan berhasil dibuat'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    public function updateTagihan(Request $request, $id)
    {
        // Clean jumlah field before validation (remove commas and dots)
        $cleanJumlah = str_replace([',', '.'], '', $request->jumlah);
        $request->merge(['jumlah' => $cleanJumlah]);

        $request->validate([
            'tgl_transaksi' => 'required|date',
            'no_ktp' => 'required|exists:tbl_anggota,no_ktp',
            'anggota_id' => 'required|exists:tbl_anggota,id',
            'jenis_id' => 'required|exists:jns_simpan,id',
            'jumlah' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string'
        ], [
            'jumlah.numeric' => 'Jumlah harus berupa angka',
            'jumlah.min' => 'Jumlah harus lebih dari 0',
            'jenis_id.exists' => 'Jenis simpanan tidak valid',
            'no_ktp.exists' => 'No KTP tidak ditemukan',
            'anggota_id.exists' => 'Anggota tidak ditemukan'
        ]);

        try {
            DB::beginTransaction();

            $tagihan = TblTransTagihan::findOrFail($id);
            
            $tagihan->update([
                'tgl_transaksi' => $request->tgl_transaksi,
                'no_ktp' => $request->no_ktp,
                'anggota_id' => $request->anggota_id,
                'jenis_id' => $request->jenis_id,
                'jumlah' => $cleanJumlah,
                'keterangan' => $request->keterangan,
                'update_data' => now(),
                'user_name' => Auth::user()->name ?? 'admin'
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Tagihan berhasil diupdate'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    public function deleteTagihan(Request $request, $id)
    {
        try {
            $tagihan = TblTransTagihan::findOrFail($id);
            $tagihan->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tagihan berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    public function exportTagihan(Request $request)
    {
        try {
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');
            $search = $request->get('search');

            $query = TblTransTagihan::with(['anggota', 'jenisSimpanan'])
                ->whereIn('jenis_id', [8, 31, 32, 40, 41, 51, 52]);

            if ($startDate && $endDate) {
                if ($startDate === $endDate) {
                    $query->whereDate('tgl_transaksi', $startDate);
                } else {
                    $query->whereBetween('tgl_transaksi', [
                        $startDate . ' 00:00:00',
                        $endDate . ' 23:59:59'
                    ]);
                }
            }

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->whereHas('anggota', function($subQ) use ($search) {
                        $subQ->where('nama', 'like', "%{$search}%")
                             ->orWhere('no_ktp', 'like', "%{$search}%");
                    })->orWhere('id', 'like', "%{$search}%");
                });
            }

            $tagihan = $query->orderBy('tgl_transaksi', 'desc')->get();

            $filename = 'tagihan_' . date('Y-m-d_H-i-s') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($tagihan) {
                $file = fopen('php://output', 'w');
                
                // Header CSV
                fputcsv($file, [
                    'No',
                    'Kode Transaksi',
                    'Tanggal Transaksi',
                    'ID Anggota',
                    'Nama Anggota',
                    'No KTP',
                    'Jenis Tagihan',
                    'Jumlah',
                    'Keterangan',
                    'User'
                ]);

                $no = 1;
                foreach ($tagihan as $t) {
                    fputcsv($file, [
                        $no++,
                        'TRD' . str_pad($t->id, 5, '0', STR_PAD_LEFT),
                        $t->tgl_transaksi ? \Carbon\Carbon::parse($t->tgl_transaksi)->format('d/m/Y') : '-',
                        'AG' . str_pad($t->anggota ? $t->anggota->id : 0, 4, '0', STR_PAD_LEFT),
                        $t->anggota ? $t->anggota->nama : 'N/A',
                        $t->no_ktp ?? 'N/A',
                        $t->jenisSimpanan ? $t->jenisSimpanan->jns_simpan : 'N/A',
                        number_format($t->jumlah ?? 0, 0, ',', '.'),
                        $t->keterangan ?? '',
                        $t->user_name ?? '-'
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat export: ' . $e->getMessage());
        }
    }

    public function updateSetoran(Request $request, $id)
    {
        // Clean jumlah field before validation (remove commas and dots)
        $cleanJumlah = str_replace([',', '.'], '', $request->jumlah);
        $request->merge(['jumlah' => $cleanJumlah]);

        $request->validate([
            'tgl_transaksi' => 'required|date',
            'no_ktp' => 'required|exists:tbl_anggota,no_ktp',
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
        ], [
            'jumlah.numeric' => 'Format jumlah tidak valid. Gunakan angka saja.',
            'jumlah.min' => 'Jumlah harus lebih dari 0.',
            'jenis_id.exists' => 'Jenis simpanan tidak valid.',
            'no_ktp.exists' => 'Anggota tidak ditemukan.',
            'anggota_id.exists' => 'ID anggota tidak valid.',
            'kas_id.exists' => 'Kas tidak ditemukan.'
        ]);

        try {
            $transaksi = TransaksiSimpanan::findOrFail($id);
            $transaksi->tgl_transaksi = $request->tgl_transaksi;
            $transaksi->no_ktp = $request->no_ktp;
            $transaksi->anggota_id = $request->anggota_id;
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