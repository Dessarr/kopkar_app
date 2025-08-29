<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TblPinjamanH;
use App\Models\data_pengajuan;
use App\Models\data_anggota;
use App\Models\suku_bunga;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DataPinjamanController extends Controller
{
    public function index(Request $request)
    {
        $query = TblPinjamanH::with(['anggota', 'detail_angsuran'])
            ->whereIn('status', ['1', '3']); // Aktif atau Terlaksana

        // Filter by date range
        if ($request->filled('date_filter')) {
            $today = now();
            
            switch($request->date_filter) {
                case 'hari_ini':
                    $query->whereDate('tgl_pinjam', $today->toDateString());
                    break;
                case 'kemarin':
                    $query->whereDate('tgl_pinjam', $today->subDay()->toDateString());
                    break;
                case 'minggu_ini':
                    $query->whereBetween('tgl_pinjam', [$today->startOfWeek(), $today->endOfWeek()]);
                    break;
                case 'bulan_ini':
                    $query->whereYear('tgl_pinjam', $today->year)
                          ->whereMonth('tgl_pinjam', $today->month);
                    break;
                case 'tahun_ini':
                    $query->whereYear('tgl_pinjam', $today->year);
                    break;
                case 'custom':
                    if ($request->filled('date_from') && $request->filled('date_to')) {
                        $query->whereBetween('tgl_pinjam', [$request->date_from, $request->date_to]);
                    }
                    break;
            }
        } elseif ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('tgl_pinjam', [$request->date_from, $request->date_to]);
        } elseif ($request->filled('date_from')) {
            $query->whereDate('tgl_pinjam', '>=', $request->date_from);
        } elseif ($request->filled('date_to')) {
            $query->whereDate('tgl_pinjam', '<=', $request->date_to);
        }

        // Filter by status pinjaman
        if ($request->filled('status_pinjaman')) {
            if ($request->status_pinjaman === 'Belum Lunas') {
                $query->where('lunas', 'Belum');
            } elseif ($request->status_pinjaman === 'Sudah Lunas') {
                $query->where('lunas', 'Lunas');
            }
        }

        // Filter by kode transaksi
        if ($request->filled('kode_transaksi')) {
            $query->where('id', 'like', '%' . $request->kode_transaksi . '%');
        }

        // Filter by nama anggota
        if ($request->filled('nama_anggota')) {
            $searchTerm = $request->nama_anggota;
            $query->whereHas('anggota', function($q) use ($searchTerm) {
                $q->where('nama', 'like', "%{$searchTerm}%")
                  ->orWhere('no_ktp', 'like', "%{$searchTerm}%");
            });
        }

        // Handle export
        if ($request->has('export') && $request->export === 'pdf') {
            return $this->exportPdf($query->get());
        }

        $dataPinjaman = $query->orderByDesc('tgl_pinjam')->paginate(10);
        
        // Append query parameters to pagination links
        $dataPinjaman->appends($request->query());

        return view('pinjaman.data_pinjaman', compact('dataPinjaman'));
    }

    public function terlaksana(string $id)
    {
        try {
            // Hanya update status pengajuan menjadi terlaksana
            // Data pinjaman sudah ada di tbl_pinjaman_h sejak status "Disetujui"
            $pengajuan = data_pengajuan::findOrFail($id);
            
            // Validasi: hanya bisa dari status disetujui
            if ($pengajuan->status != 1) {
                return back()->with('error', 'Hanya pengajuan yang sudah disetujui yang dapat diubah menjadi terlaksana');
            }

            $pengajuan->status = 3; // Terlaksana
            $pengajuan->tgl_update = now();
            $pengajuan->save();

            Log::info('Status pengajuan diubah menjadi terlaksana', [
                'pengajuan_id' => $id,
                'ajuan_id' => $pengajuan->ajuan_id
            ]);

            return back()->with('success', 'Status pengajuan berhasil diubah menjadi terlaksana');

        } catch (\Exception $e) {
            Log::error('Gagal ubah status menjadi terlaksana', [
                'pengajuan_id' => $id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Gagal ubah status: ' . $e->getMessage());
        }
    }

    public function show(string $id)
    {
        $pinjaman = TblPinjamanH::with(['anggota', 'detail_angsuran'])->findOrFail($id);
        return view('pinjaman.detail_pinjaman', compact('pinjaman'));
    }



    public function destroy(string $id)
    {
        try {
            $pinjaman = TblPinjamanH::findOrFail($id);
            
            // Cek apakah sudah ada pembayaran angsuran
            if ($pinjaman->detail_angsuran()->count() > 0) {
                $message = '📋 Pinjaman tidak dapat dihapus karena sudah ada pembayaran angsuran. Data pembayaran harus dipertahankan untuk keperluan audit dan laporan keuangan.';
                
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => $message
                    ]);
                }
                
                return back()->with('error', $message);
            }

            // Hapus data terkait terlebih dahulu
            DB::beginTransaction();
            
            try {
                // Hapus dari tempo_pinjaman
                DB::table('tempo_pinjaman')->where('pinjam_id', $id)->delete();
                
                // Hapus dari tbl_trans_tagihan jika ada
                DB::table('tbl_trans_tagihan')->where('no_ktp', $pinjaman->anggota->no_ktp ?? '')->delete();
                
                // Hapus dari tbl_trans_sp_bayar_temp jika ada
                DB::table('tbl_trans_sp_bayar_temp')->where('no_ktp', $pinjaman->anggota->no_ktp ?? '')->delete();
                
                // Hapus pinjaman utama
                $pinjaman->delete();
                
                DB::commit();
                
                Log::info('Pinjaman dan data terkait berhasil dihapus', ['pinjaman_id' => $id]);
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }

            $message = 'Pinjaman berhasil dihapus';
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message
                ]);
            }
            
            return back()->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Gagal hapus pinjaman', [
                'pinjaman_id' => $id,
                'error' => $e->getMessage()
            ]);

            $message = 'Gagal hapus pinjaman: ' . $e->getMessage();
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message
                ]);
            }
            
            return back()->with('error', $message);
        }
    }

    public function create()
    {
        $anggota = data_anggota::all();
        $jenisPinjaman = [
            '1' => 'Biasa',
            '2' => 'Barang'
        ];
        $dataKas = \App\Models\DataKas::all();
        
        return view('pinjaman.form_pinjaman', compact('anggota', 'jenisPinjaman', 'dataKas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'anggota_id' => 'required|exists:data_anggota,id',
            'tgl_pinjam' => 'required|date',
            'jumlah' => 'required|numeric|min:1000',
            'lama_angsuran' => 'required|integer|min:1|max:60',
            'bunga' => 'required|numeric|min:0|max:100',
            'jenis_pinjaman' => 'required|in:1,2',
            'kas_id' => 'required|exists:data_kas,id',
            'keterangan' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            // Hitung jumlah angsuran per bulan
            $jumlahAngsuran = $request->jumlah / $request->lama_angsuran;
            
            // Biaya admin diatur menjadi 0
            $biayaAdm = 0;

            $pinjaman = new TblPinjamanH();
            $pinjaman->anggota_id = $request->anggota_id;
            $pinjaman->tgl_pinjam = $request->tgl_pinjam;
            $pinjaman->jumlah = $request->jumlah;
            $pinjaman->lama_angsuran = $request->lama_angsuran;
            $pinjaman->jumlah_angsuran = $jumlahAngsuran;
            $pinjaman->bunga = $request->bunga;
            $pinjaman->biaya_adm = $biayaAdm;
            $pinjaman->jenis_pinjaman = $request->jenis_pinjaman;
            $pinjaman->kas_id = $request->kas_id;
            $pinjaman->keterangan = $request->keterangan;
            $pinjaman->status = '1'; // Aktif
            $pinjaman->lunas = 'Belum';
            $pinjaman->save();

            DB::commit();

            Log::info('Pinjaman berhasil dibuat', ['pinjaman_id' => $pinjaman->id]);

            return redirect()->route('pinjaman.data_pinjaman')->with('success', 'Pinjaman berhasil dibuat');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Gagal buat pinjaman', [
                'error' => $e->getMessage(),
                'payload' => $request->all()
            ]);

            return back()->withInput()->with('error', 'Gagal buat pinjaman: ' . $e->getMessage());
        }
    }

    public function edit(string $id)
    {
        $pinjaman = TblPinjamanH::with('anggota')->findOrFail($id);
        $anggota = data_anggota::all();
        $jenisPinjaman = [
            '1' => 'Biasa',
            '2' => 'Barang'
        ];
        $dataKas = \App\Models\DataKas::all();
        
        return view('pinjaman.form_pinjaman', compact('pinjaman', 'anggota', 'jenisPinjaman', 'dataKas'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'anggota_id' => 'required|exists:data_anggota,id',
            'tgl_pinjam' => 'required|date',
            'jumlah' => 'required|numeric|min:1000',
            'lama_angsuran' => 'required|integer|min:1|max:60',
            'bunga' => 'required|numeric|min:0|max:100',
            'jenis_pinjaman' => 'required|in:1,2',
            'kas_id' => 'required|exists:data_kas,id',
            'keterangan' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $pinjaman = TblPinjamanH::findOrFail($id);
            
            // Cek apakah sudah ada pembayaran angsuran
            if ($pinjaman->detail_angsuran()->count() > 0) {
                return back()->with('error', 'Pinjaman tidak dapat diedit karena sudah ada pembayaran angsuran');
            }

            // Hitung jumlah angsuran per bulan
            $jumlahAngsuran = $request->jumlah / $request->lama_angsuran;
            
            // Biaya admin diatur menjadi 0
            $biayaAdm = 0;

            $pinjaman->anggota_id = $request->anggota_id;
            $pinjaman->tgl_pinjam = $request->tgl_pinjam;
            $pinjaman->jumlah = $request->jumlah;
            $pinjaman->lama_angsuran = $request->lama_angsuran;
            $pinjaman->jumlah_angsuran = $jumlahAngsuran;
            $pinjaman->bunga = $request->bunga;
            $pinjaman->biaya_adm = $biayaAdm;
            $pinjaman->jenis_pinjaman = $request->jenis_pinjaman;
            $pinjaman->kas_id = $request->kas_id;
            $pinjaman->keterangan = $request->keterangan;
            $pinjaman->save();

            DB::commit();

            Log::info('Pinjaman berhasil diupdate', ['pinjaman_id' => $id]);

            return redirect()->route('pinjaman.data_pinjaman')->with('success', 'Pinjaman berhasil diupdate');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Gagal update pinjaman', [
                'pinjaman_id' => $id,
                'error' => $e->getMessage()
            ]);

            return back()->withInput()->with('error', 'Gagal update pinjaman: ' . $e->getMessage());
        }
    }

    /**
     * Bulk delete multiple pinjaman
     */
    public function bulkDestroy(Request $request)
    {
        try {
            $ids = $request->input('ids', []);
            
            // Decode JSON jika ids dikirim sebagai string
            if (is_string($ids)) {
                $ids = json_decode($ids, true);
            }
            
            // Pastikan ids adalah array
            if (!is_array($ids)) {
                Log::error('Bulk delete: ids bukan array', [
                    'ids' => $ids,
                    'type' => gettype($ids)
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Format data tidak valid'
                ]);
            }
            
            // Debug log
            Log::info('Bulk delete: ids received', [
                'ids' => $ids,
                'count' => count($ids),
                'type' => gettype($ids)
            ]);
            
            if (empty($ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada data yang dipilih untuk dihapus'
                ]);
            }

            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            foreach ($ids as $id) {
                try {
                    $pinjaman = TblPinjamanH::findOrFail($id);
                    
                    // Cek apakah sudah ada pembayaran angsuran
                    $detailCount = $pinjaman->detail_angsuran()->count();
                    if ($detailCount > 0) {
                        $errorCount++;
                        $errors[] = "📋 Pinjaman ID {$id} tidak dapat dihapus karena sudah ada {$detailCount} pembayaran angsuran. Data pembayaran harus dipertahankan untuk keperluan audit dan laporan keuangan.";
                        continue;
                    }

                    // Hapus data terkait terlebih dahulu
                    DB::beginTransaction();
                    
                    try {
                        // Hapus dari tempo_pinjaman
                        DB::table('tempo_pinjaman')->where('pinjam_id', $id)->delete();
                        
                        // Hapus dari tbl_trans_tagihan jika ada
                        DB::table('tbl_trans_tagihan')->where('no_ktp', $pinjaman->anggota->no_ktp ?? '')->delete();
                        
                        // Hapus dari tbl_trans_sp_bayar_temp jika ada
                        DB::table('tbl_trans_sp_bayar_temp')->where('no_ktp', $pinjaman->anggota->no_ktp ?? '')->delete();
                        
                        // Hapus pinjaman utama
                        $pinjaman->delete();
                        
                        DB::commit();
                        $successCount++;
                        
                        Log::info('Pinjaman berhasil dihapus dalam bulk delete', ['pinjaman_id' => $id]);
                        
                    } catch (\Exception $e) {
                        DB::rollback();
                        $errorCount++;
                        $errors[] = "Gagal hapus pinjaman ID {$id}: " . $e->getMessage();
                        Log::error('Gagal hapus pinjaman dalam bulk delete', [
                            'pinjaman_id' => $id,
                            'error' => $e->getMessage()
                        ]);
                    }
                    
                } catch (\Exception $e) {
                    $errorCount++;
                    $errors[] = "Pinjaman ID {$id} tidak ditemukan";
                    Log::error('Pinjaman tidak ditemukan dalam bulk delete', [
                        'pinjaman_id' => $id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            $message = "Berhasil menghapus {$successCount} data";
            if ($errorCount > 0) {
                $message .= ", gagal menghapus {$errorCount} data (data dengan pembayaran angsuran tidak dapat dihapus untuk keperluan audit)";
            }

            return response()->json([
                'success' => $successCount > 0,
                'message' => $message,
                'success_count' => $successCount,
                'error_count' => $errorCount,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            Log::error('Gagal bulk delete pinjaman', [
                'error' => $e->getMessage(),
                'ids' => $request->input('ids', [])
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan bulk delete: ' . $e->getMessage()
            ]);
        }
    }

    public function getPinjamanData($id)
    {
        try {
            $pinjaman = TblPinjamanH::with('anggota')->findOrFail($id);
            return response()->json($pinjaman);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }
    }

    private function exportPdf($data)
    {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pinjaman.export_pinjaman', compact('data'));
        return $pdf->download('laporan_pinjaman_' . date('Y-m-d') . '.pdf');
    }

    public function lunas()
    {
        // Ambil data pinjaman yang sudah lunas (lunas = 'Lunas')
        $dataPinjamanLunas = TblPinjamanH::with('anggota')
            ->where('lunas', 'Lunas')
            ->orderByDesc('tgl_pinjam')
            ->paginate(10);

        return view('pinjaman.data_pinjaman_lunas', compact('dataPinjamanLunas'));
    }

    public function nota(string $id)
    {
        try {
            $pinjaman = TblPinjamanH::with('anggota')->findOrFail($id);
            
            // Hitung data yang diperlukan untuk nota
            $sudahDibayar = $pinjaman->detail_angsuran()->sum('jumlah_bayar') ?? 0;
            $sisaAngsuran = $pinjaman->lama_angsuran - ($pinjaman->detail_angsuran()->count() ?? 0);
            $sisaTagihan = $pinjaman->jumlah - $sudahDibayar;
            $totalDenda = $pinjaman->detail_angsuran()->sum('denda_rp') ?? 0;
            $totalTagihan = $pinjaman->jumlah + $totalDenda;
            
            $dataNota = [
                'pinjaman' => $pinjaman,
                'sudah_dibayar' => $sudahDibayar,
                'sisa_angsuran' => $sisaAngsuran,
                'sisa_tagihan' => $sisaTagihan,
                'total_denda' => $totalDenda,
                'total_tagihan' => $totalTagihan
            ];
            
            return view('pinjaman.nota_pinjaman', compact('dataNota'));
            
        } catch (\Exception $e) {
            Log::error('Gagal generate nota pinjaman', [
                'pinjaman_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Gagal generate nota pinjaman: ' . $e->getMessage());
        }
    }
}