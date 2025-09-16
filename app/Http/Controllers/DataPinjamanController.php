<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TblPinjamanH;
use App\Models\data_pengajuan;
use App\Models\data_anggota;
use App\Models\suku_bunga;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
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
            DB::beginTransaction();
            
            $pengajuan = data_pengajuan::findOrFail($id);
            
            // Validasi: hanya bisa dari status disetujui
            if ($pengajuan->status != 1) {
                return back()->with('error', 'Hanya pengajuan yang sudah disetujui yang dapat diubah menjadi terlaksana');
            }

            // Update status pengajuan menjadi terlaksana
            $pengajuan->status = 3; // Terlaksana
            $pengajuan->tgl_update = now();
            $pengajuan->save();

            // Cari data pinjaman yang sudah dibuat saat approve
            $pinjaman = TblPinjamanH::where('anggota_id', $pengajuan->anggota_id)
                ->where('status', '1')
                ->where('lunas', 'Belum')
                ->orderBy('id', 'desc') // Gunakan id sebagai pengganti created_at
                ->first();

            if ($pinjaman) {
                // Generate jadwal angsuran di tempo_pinjaman
                $this->generateTempoPinjaman($pinjaman, $pengajuan);
                
                // Generate billing data untuk bulan ini
                $this->generateBillingDataForPinjaman($pinjaman, $pengajuan);
                
                Log::info('Status pengajuan diubah menjadi terlaksana, jadwal angsuran dibuat, dan billing data di-generate', [
                    'pengajuan_id' => $id,
                    'pinjaman_id' => $pinjaman->id,
                    'ajuan_id' => $pengajuan->ajuan_id
                ]);
            } else {
                Log::warning('Data pinjaman tidak ditemukan saat terlaksana', [
                    'pengajuan_id' => $id,
                    'anggota_id' => $pengajuan->anggota_id
                ]);
            }

            DB::commit();
            return back()->with('success', 'Status pengajuan berhasil diubah menjadi terlaksana dan jadwal angsuran dibuat');

        } catch (\Exception $e) {
            DB::rollback();
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

    /**
     * Generate billing data untuk pinjaman yang baru terlaksana
     */
    private function generateBillingDataForPinjaman($pinjaman, $pengajuan)
    {
        try {
            // Ambil data jadwal angsuran untuk bulan-bulan mendatang
            $tempoData = DB::table('tempo_pinjaman')
                ->where('pinjam_id', $pinjaman->id)
                ->get();
            
            foreach ($tempoData as $tempo) {
                $bulan = date('m', strtotime($tempo->tempo));
                $tahun = date('Y', strtotime($tempo->tempo));
                
                // Hitung angsuran per bulan
                $angsuranPokok = $pinjaman->jumlah / $pinjaman->lama_angsuran;
                $angsuranBunga = $pinjaman->bunga_rp / $pinjaman->lama_angsuran;
                $totalAngsuran = $angsuranPokok + $angsuranBunga;
                
                // 1. Insert ke tbl_trans_tagihan
                DB::table('tbl_trans_tagihan')->updateOrInsert(
                    [
                        'tgl_transaksi' => $tempo->tempo,
                        'no_ktp' => $pinjaman->no_ktp,
                        'jenis_id' => 999 // ID untuk jenis Pinjaman
                    ],
                    [
                        'anggota_id' => $pinjaman->anggota_id,
                        'jumlah' => $totalAngsuran,
                        'keterangan' => 'Tagihan Pinjaman ' . $bulan . '-' . $tahun,
                        'akun' => '7', // Akun pinjaman
                        'dk' => 'K', // Kredit
                        'kas_id' => 1, // Kas utama
                        'update_data' => now(),
                        'user_name' => Auth::user()->name ?? 'system'
                    ]
                );
                
                // 2. Insert ke tbl_trans_sp_bayar_temp dengan logika SUM
                DB::table('tbl_trans_sp_bayar_temp')->updateOrInsert(
                    [
                        'tgl_transaksi' => Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->toDateString(),
                        'no_ktp' => $pinjaman->no_ktp
                    ],
                    [
                        'anggota_id' => $pinjaman->anggota_id,
                        'jumlah' => DB::raw('COALESCE(jumlah,0) + ' . $totalAngsuran),
                        'keterangan' => 'Billing Pinjaman ' . $bulan . '-' . $tahun,
                        'tagihan_simpanan_wajib' => DB::raw('COALESCE(tagihan_simpanan_wajib,0)'),
                        'tagihan_simpanan_sukarela' => DB::raw('COALESCE(tagihan_simpanan_sukarela,0)'),
                        'tagihan_simpanan_khusus_2' => DB::raw('COALESCE(tagihan_simpanan_khusus_2,0)'),
                        'tagihan_simpanan_pokok' => DB::raw('COALESCE(tagihan_simpanan_pokok,0)'),
                        'tagihan_pinjaman' => DB::raw('COALESCE(tagihan_pinjaman,0) + ' . $totalAngsuran),
                        'tagihan_pinjaman_jasa' => DB::raw('COALESCE(tagihan_pinjaman_jasa,0)'),
                        'tagihan_toserda' => DB::raw('COALESCE(tagihan_toserda,0)'),
                        'total_tagihan_simpanan' => DB::raw('COALESCE(total_tagihan_simpanan,0)'),
                        'selisih' => DB::raw('COALESCE(selisih,0)'),
                        'saldo_simpanan_sukarela' => DB::raw('COALESCE(saldo_simpanan_sukarela,0)'),
                        'saldo_akhir_simpanan_sukarela' => DB::raw('COALESCE(saldo_akhir_simpanan_sukarela,0)')
                    ]
                );
            }
            
            Log::info('Billing data berhasil di-generate untuk pinjaman', [
                'pinjaman_id' => $pinjaman->id,
                'no_ktp' => $pinjaman->no_ktp,
                'jumlah_tempo' => count($tempoData)
            ]);
            
        } catch (\Exception $e) {
            Log::error('Gagal generate billing data untuk pinjaman', [
                'pinjaman_id' => $pinjaman->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Generate jadwal angsuran di tempo_pinjaman
     */
    private function generateTempoPinjaman($pinjaman, $pengajuan)
    {
        try {
            $tglPinjam = Carbon::parse($pinjaman->tgl_pinjam);
            $lamaAngsuran = $pengajuan->lama_ags;
            
            for ($i = 1; $i <= $lamaAngsuran; $i++) {
                // Hitung tanggal jatuh tempo (mengikuti hari pada tgl_pinjam)
                $tglTempo = $tglPinjam->copy()->addMonths($i);
                
                // Jika bulan tidak memiliki tanggal tersebut, clamp ke akhir bulan
                $hariPinjam = $tglPinjam->day;
                if ($tglTempo->daysInMonth < $hariPinjam) {
                    $tglTempo = $tglTempo->endOfMonth();
                } else {
                    $tglTempo->day($hariPinjam);
                }
                
                // Insert ke tempo_pinjaman
                DB::table('tempo_pinjaman')->insert([
                    'pinjam_id' => $pinjaman->id,
                    'no_ktp' => $pinjaman->no_ktp,
                    'tgl_pinjam' => $tglPinjam->toDateString(),
                    'tempo' => $tglTempo->toDateString()
                ]);
            }
            
            Log::info('Jadwal angsuran berhasil di-generate', [
                'pinjaman_id' => $pinjaman->id,
                'jumlah_angsuran' => $lamaAngsuran,
                'tgl_pinjam' => $tglPinjam->toDateString()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Gagal generate jadwal angsuran', [
                'pinjaman_id' => $pinjaman->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
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
            '3' => 'Barang'
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
            'jenis_pinjaman' => 'required|in:1,3',
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
            $pinjaman->setAttribute('jumlah_angsuran', $jumlahAngsuran);
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
            '3' => 'Barang'
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
            'jenis_pinjaman' => 'required|in:1,3',
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
            $pinjaman->setAttribute('jumlah_angsuran', $jumlahAngsuran);
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