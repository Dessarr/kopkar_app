<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TblPinjamanH;
use App\Models\TblPinjamanLog;
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

        // Filter by jenis pinjaman
        if ($request->filled('jenis_pinjaman')) {
            $query->where('jenis_pinjaman', $request->jenis_pinjaman);
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
     * Show edit form
     */
    public function edit(string $id)
    {
        $pinjaman = TblPinjamanH::with(['anggota'])->findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $pinjaman
        ]);
    }

    /**
     * Get pinjaman data for editing
     */
    public function getPinjamanData(string $id)
    {
        try {
            $pinjaman = TblPinjamanH::with(['anggota'])->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $pinjaman->id,
                    'tgl_pinjam' => $pinjaman->tgl_pinjam,
                    'anggota_id' => $pinjaman->anggota_id,
                    'jumlah' => $pinjaman->jumlah,
                    'lama_angsuran' => $pinjaman->lama_angsuran,
                    'bunga' => $pinjaman->bunga,
                    'jasa' => $pinjaman->bunga, // Alias untuk form
                    'jenis_pinjaman' => $pinjaman->jenis_pinjaman,
                    'kas_id' => $pinjaman->kas_id,
                    'keterangan' => $pinjaman->keterangan,
                    'lunas' => $pinjaman->lunas,
                    'status' => $pinjaman->status
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data pinjaman tidak ditemukan'
            ], 404);
        }
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
            
            // Jika ada pengajuan, gunakan lama_ags dari pengajuan
            // Jika tidak ada pengajuan (CRUD tambah), gunakan lama_angsuran dari pinjaman
            $lamaAngsuran = $pengajuan ? $pengajuan->lama_ags : $pinjaman->lama_angsuran;
            
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
                
                // Insert ke tempo_pinjaman dengan field yang benar sesuai struktur database
                DB::table('tempo_pinjaman')->insert([
                    'no_urut' => $i,
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



    /**
     * Update data pinjaman dengan validasi dan audit trail
     */
    public function update(Request $request, string $id)
    {
        // Validasi input
        $request->validate([
            'anggota_id' => 'required|exists:tbl_anggota,id',
            'tgl_pinjam' => 'required|date',
            'jumlah' => 'required|numeric|min:1000',
            'lama_angsuran' => 'required|integer|min:1|max:60',
            'jasa' => 'required|numeric|min:0',
            'jenis_pinjaman' => 'required|in:1,2,3',
            'kas_id' => 'required|exists:data_kas,id',
            'keterangan' => 'required|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            // Ambil data pinjaman lama
            $pinjaman = TblPinjamanH::findOrFail($id);
            $oldData = $pinjaman->toArray();

            // Validasi bisnis
            $this->validateBusinessRules($pinjaman, $request);

            // Cek apakah ada perubahan kritis
            $criticalChanges = $this->checkCriticalChanges($oldData, $request->all());

            // Update data pinjaman
            $pinjaman->tgl_pinjam = $request->tgl_pinjam;
            $pinjaman->anggota_id = $request->anggota_id;
            $pinjaman->jumlah = $request->jumlah;
            $pinjaman->lama_angsuran = $request->lama_angsuran;
            $pinjaman->bunga = $request->jasa;
            $pinjaman->bunga_rp = $request->jasa;
            $pinjaman->jenis_pinjaman = $request->jenis_pinjaman;
            $pinjaman->kas_id = $request->kas_id;
            $pinjaman->keterangan = $request->keterangan;
            $pinjaman->user_name = Auth::user()->name ?? 'admin';
            $pinjaman->update_data = now();

            // Hitung ulang jumlah angsuran
            $jumlahAngsuran = $request->jumlah / $request->lama_angsuran;
            $pinjaman->setAttribute('jumlah_angsuran', $jumlahAngsuran);

            $pinjaman->save();

            // Log perubahan
            $this->logChanges($id, $oldData, $pinjaman->toArray(), $criticalChanges);

            // Jika ada perubahan kritis, regenerate tempo dan billing
            if ($criticalChanges) {
                $this->handleCriticalChanges($pinjaman, $criticalChanges);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data pinjaman berhasil diupdate',
                'data' => $pinjaman,
                'critical_changes' => $criticalChanges
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Gagal update pinjaman', [
                'pinjaman_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

                    return response()->json([
                        'success' => false,
                'message' => 'Gagal update pinjaman: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validasi aturan bisnis untuk edit pinjaman
     */
    private function validateBusinessRules($pinjaman, $request)
    {
        // Cek apakah pinjaman sudah lunas
        if ($pinjaman->lunas === 'Lunas') {
            throw new \Exception('Pinjaman yang sudah lunas tidak dapat diedit');
        }

        // Cek apakah ada pembayaran angsuran
        $hasPayments = DB::table('tbl_pinjaman_d')
            ->where('pinjam_id', $pinjaman->id)
            ->where('jumlah_bayar', '>', 0)
            ->exists();

        if ($hasPayments) {
            // Jika sudah ada pembayaran, batasi perubahan
            if ($request->jumlah < $pinjaman->jumlah) {
                throw new \Exception('Jumlah pinjaman tidak boleh dikurangi jika sudah ada pembayaran angsuran');
            }

            if ($request->lama_angsuran < $pinjaman->lama_angsuran) {
                throw new \Exception('Lama angsuran tidak boleh dikurangi jika sudah ada pembayaran angsuran');
            }

            // Validasi tambahan untuk perubahan kritis
            $criticalChanges = $this->checkCriticalChanges($pinjaman->toArray(), $request->all());
            if ($criticalChanges) {
                // Log warning untuk perubahan kritis pada pinjaman yang sudah berjalan
                Log::warning('Critical changes detected on active loan', [
                    'pinjaman_id' => $pinjaman->id,
                    'changes' => $criticalChanges,
                    'user' => Auth::user()->name ?? 'unknown'
                ]);
            }
        }

        // Validasi tambahan untuk integritas data
        $this->validateDataIntegrity($pinjaman, $request);
    }

    /**
     * Validasi integritas data untuk perubahan pinjaman
     */
    private function validateDataIntegrity($pinjaman, $request)
    {
        // Validasi: cek apakah ada cicilan yang sudah tercatat
        $existingInstallments = DB::table('tbl_pinjaman_d')
            ->where('pinjam_id', $pinjaman->id)
            ->where('jumlah_bayar', '>', 0)
            ->count();

        if ($existingInstallments > 0) {
            // Jika ada cicilan yang sudah tercatat, berikan peringatan
            $this->logChange($pinjaman->id, 'validation_warning', null, 'Pinjaman dengan cicilan existing', 'WARNING', 'Data integrity check');
        }

        // Validasi: cek apakah perubahan akan mempengaruhi data billing
        $existingBilling = DB::table('tbl_trans_sp_bayar_temp')
            ->where('no_ktp', $pinjaman->no_ktp)
            ->where('tagihan_pinjaman', '>', 0)
            ->exists();

        if ($existingBilling) {
            // Jika ada data billing, berikan peringatan
            $this->logChange($pinjaman->id, 'billing_warning', null, 'Pinjaman dengan billing existing', 'WARNING', 'Billing data check');
        }
    }

    /**
     * Cek perubahan kritis yang memerlukan regenerasi data
     */
    private function checkCriticalChanges($oldData, $newData)
    {
        $criticalFields = ['jumlah', 'lama_angsuran', 'bunga', 'tgl_pinjam'];
        $changes = [];

        foreach ($criticalFields as $field) {
            $oldValue = $oldData[$field] ?? null;
            $newValue = $newData[$field] ?? null;

            if ($oldValue != $newValue) {
                $changes[$field] = [
                    'old' => $oldValue,
                    'new' => $newValue
                ];
            }
        }

        return $changes;
    }

    /**
     * Log perubahan data pinjaman (single field)
     */
    private function logChange($pinjamanId, $fieldName, $oldValue, $newValue, $action, $notes = null)
    {
        try {
            TblPinjamanLog::logChange(
                $pinjamanId,
                $fieldName,
                $oldValue,
                $newValue,
                $action,
                Auth::user()->name ?? 'system',
                $notes
            );
            } catch (\Exception $e) {
            Log::error('Gagal log perubahan pinjaman', [
                'pinjaman_id' => $pinjamanId,
                'field' => $fieldName,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Log perubahan data
     */
    private function logChanges($pinjamanId, $oldData, $newData, $criticalChanges)
    {
        $userName = Auth::user()->name ?? 'system';

        // Log perubahan field biasa
        $fieldsToLog = ['tgl_pinjam', 'anggota_id', 'jumlah', 'lama_angsuran', 'bunga', 'jenis_pinjaman', 'kas_id', 'keterangan'];

        foreach ($fieldsToLog as $field) {
            if (isset($oldData[$field]) && isset($newData[$field]) && $oldData[$field] != $newData[$field]) {
                TblPinjamanLog::logChange(
                    $pinjamanId,
                    $field,
                    $oldData[$field],
                    $newData[$field],
                    'UPDATE',
                    $userName,
                    $criticalChanges ? 'Perubahan kritis - memerlukan regenerasi data' : null
                );
            }
        }
    }

    /**
     * Buat revisi pinjaman (fitur terpisah dari edit langsung)
     */
    public function createRevision(Request $request, string $id)
    {
        $request->validate([
            'anggota_id' => 'required|exists:tbl_anggota,id',
            'tgl_pinjam' => 'required|date',
            'jumlah' => 'required|numeric|min:1000',
            'lama_angsuran' => 'required|integer|min:1|max:60',
            'jasa' => 'required|numeric|min:0',
            'jenis_pinjaman' => 'required|in:1,2,3',
            'kas_id' => 'required|exists:data_kas,id',
            'keterangan' => 'required|string|max:500',
            'alasan_revisi' => 'required|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            // Ambil data pinjaman asli
            $originalPinjaman = TblPinjamanH::findOrFail($id);
            
            // Validasi: pinjaman harus belum lunas
            if ($originalPinjaman->lunas === 'Lunas') {
                throw new \Exception('Pinjaman yang sudah lunas tidak dapat direvisi');
            }

            // Cek apakah ada pembayaran
            $hasPayments = DB::table('tbl_pinjaman_d')
                ->where('pinjam_id', $id)
                ->where('jumlah_bayar', '>', 0)
                ->exists();

            if ($hasPayments) {
                throw new \Exception('Pinjaman yang sudah ada pembayaran tidak dapat direvisi. Gunakan fitur restrukturisasi.');
            }

            // Buat data revisi
            $revisionData = $originalPinjaman->toArray();
            $revisionData['id'] = $id . '_REV_' . time();
            $revisionData['tgl_pinjam'] = $request->tgl_pinjam;
            $revisionData['anggota_id'] = $request->anggota_id;
            $revisionData['jumlah'] = $request->jumlah;
            $revisionData['lama_angsuran'] = $request->lama_angsuran;
            $revisionData['bunga'] = $request->jasa;
            $revisionData['bunga_rp'] = $request->jasa;
            $revisionData['jenis_pinjaman'] = $request->jenis_pinjaman;
            $revisionData['kas_id'] = $request->kas_id;
            $revisionData['keterangan'] = $request->keterangan;
            $revisionData['user_name'] = Auth::user()->name ?? 'admin';
            $revisionData['update_data'] = now();
            $revisionData['is_revision'] = true;
            $revisionData['original_id'] = $id;
            $revisionData['alasan_revisi'] = $request->alasan_revisi;

            // Hitung jumlah angsuran
            $jumlahAngsuran = $request->jumlah / $request->lama_angsuran;
            $revisionData['jumlah_angsuran'] = $jumlahAngsuran;

            // Insert revisi
            $revision = TblPinjamanH::create($revisionData);

            // Generate tempo dan billing untuk revisi
            $this->generateTempoPinjaman($revision, null);
            $this->generateBillingDataForPinjaman($revision, null);

            // Log revisi
            $this->logChange($id, 'revision_created', null, $revision->id, 'REVISION', $request->alasan_revisi);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Revisi pinjaman berhasil dibuat',
                'data' => $revision
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Gagal membuat revisi pinjaman', [
                'pinjaman_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat revisi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle perubahan kritis dengan regenerasi data
     */
    private function handleCriticalChanges($pinjaman, $criticalChanges)
    {
        // Hapus tempo lama
        DB::table('tempo_pinjaman')->where('pinjam_id', $pinjaman->id)->delete();

        // Hapus billing lama
        DB::table('tbl_trans_sp_bayar_temp')
            ->where('no_ktp', $pinjaman->no_ktp)
            ->where('tagihan_pinjaman', '>', 0)
            ->delete();

        // Regenerate tempo dan billing
        $this->generateTempoPinjaman($pinjaman, null);
        $this->generateBillingDataForPinjaman($pinjaman, null);

        Log::info('Data tempo dan billing di-regenerate karena perubahan kritis', [
            'pinjaman_id' => $pinjaman->id,
            'changes' => $criticalChanges
        ]);
    }

    public function destroy(string $id)
    {
        try {
            $pinjaman = TblPinjamanH::findOrFail($id);
            
            // Cek apakah sudah ada pembayaran angsuran
            $hasPayments = DB::table('tbl_pinjaman_d')
                ->where('pinjam_id', $id)
                ->where('jumlah_bayar', '>', 0)
                ->exists();

            if ($hasPayments) {
                $message = '📋 Pinjaman tidak dapat dihapus karena sudah ada pembayaran angsuran. Data pembayaran harus dipertahankan untuk keperluan audit dan laporan keuangan.';
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message
                ]);
            }
            
            return back()->with('error', $message);
            }

            // Cek apakah pinjaman sudah lunas
            if ($pinjaman->lunas === 'Lunas') {
                $message = '🔒 Pinjaman yang sudah lunas tidak dapat dihapus. Data pinjaman lunas harus dipertahankan untuk keperluan audit dan laporan keuangan.';
                
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
                // 1. Update stok barang bertambah (jika jenis pinjaman barang)
                if ($pinjaman->jenis_pinjaman == 3 && $pinjaman->barang_id) {
                    DB::table('tbl_barang')
                        ->where('id', $pinjaman->barang_id)
                        ->increment('jml_brg', 1);
                    
                    Log::info('Stok barang dikembalikan saat hapus pinjaman', [
                        'pinjaman_id' => $id,
                        'barang_id' => $pinjaman->barang_id
                    ]);
                }

                // 2. Hapus dari tempo_pinjaman
                $deletedTempo = DB::table('tempo_pinjaman')->where('pinjam_id', $id)->delete();
                
                // 3. Hapus dari tbl_trans_tagihan jika ada
                $deletedTagihan = DB::table('tbl_trans_tagihan')
                    ->where('no_ktp', $pinjaman->no_ktp)
                    ->delete();
                
                // 4. Hapus dari tbl_trans_sp_bayar_temp jika ada
                $deletedBilling = DB::table('tbl_trans_sp_bayar_temp')
                    ->where('no_ktp', $pinjaman->no_ktp)
                    ->where('tagihan_pinjaman', '>', 0)
                    ->delete();

                // 5. Hapus dari tbl_trans_kas terkait
                $deletedTransKas = DB::table('tbl_trans_kas')
                    ->where('keterangan', 'like', '%' . $id . '%')
                    ->delete();
                
                // 6. Hapus pinjaman utama
                $pinjaman->delete();

                // 7. Log audit trail
                $this->logChange($id, 'pinjaman_deleted', $pinjaman->toArray(), null, 'DELETE', 'Pinjaman dihapus beserta data terkait');
                
                DB::commit();
                
                Log::info('Pinjaman dan data terkait berhasil dihapus', [
                    'pinjaman_id' => $id,
                    'tempo_deleted' => $deletedTempo,
                    'tagihan_deleted' => $deletedTagihan,
                    'billing_deleted' => $deletedBilling,
                    'trans_kas_deleted' => $deletedTransKas,
                    'stok_updated' => $pinjaman->jenis_pinjaman == 3 ? true : false
                ]);
        } catch (\Exception $e) {
            DB::rollback();
                throw $e;
            }

            $message = 'Pinjaman berhasil dihapus beserta data terkait (tempo, billing, trans_kas)';
            
            // Selalu return JSON untuk AJAX request
            return response()->json([
                'success' => true,
                'message' => $message,
                'details' => [
                    'tempo_deleted' => $deletedTempo ?? 0,
                    'tagihan_deleted' => $deletedTagihan ?? 0,
                    'billing_deleted' => $deletedBilling ?? 0,
                    'trans_kas_deleted' => $deletedTransKas ?? 0,
                    'stok_updated' => $pinjaman->jenis_pinjaman == 3 ? true : false
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Gagal hapus pinjaman', [
                'pinjaman_id' => $id,
                'error' => $e->getMessage()
            ]);

            $message = 'Gagal hapus pinjaman: ' . $e->getMessage();
            
            // Selalu return JSON untuk error juga
            return response()->json([
                'success' => false,
                'message' => $message,
                'error' => $e->getMessage()
            ], 500);
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
        // Validasi input sesuai project lama
        $request->validate([
            'anggota_id' => 'required|exists:tbl_anggota,id',
            'tgl_pinjam' => 'required|date',
            'jumlah' => 'required|numeric|min:1000',
            'lama_angsuran' => 'required|integer|min:1|max:60',
            'jasa' => 'required|numeric|min:0',
            'jenis_pinjaman' => 'required|in:1,2,3',
            'kas_id' => 'required|exists:data_kas,id',
            'keterangan' => 'required|string|max:500'
        ]);

        // Cek jumlah > 0 (seperti project lama)
        if ($request->jumlah <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Jumlah pinjaman harus lebih dari 0'
            ], 400);
        }

        try {
            DB::beginTransaction();

            // 1. Generate ID transaksi unik (seperti project lama)
            $q = DB::select("SELECT count(id)+2 as mid FROM tbl_pinjaman_h")[0];
            $id_trans = date('Ymd') . $q->mid;

            // 2. Hitung jumlah angsuran per bulan
            $jumlahAngsuran = $request->jumlah / $request->lama_angsuran;
            
            // 3. Ambil data anggota
            $anggota = data_anggota::find($request->anggota_id);
            if (!$anggota) {
                throw new \Exception('Anggota tidak ditemukan');
            }

            // 4. Update stok barang berkurang (seperti project lama)
            // Untuk pinjaman uang (barang_id = 4), tidak perlu update stok
            // Tapi untuk pinjaman barang fisik, update stok berkurang
            if ($request->jenis_pinjaman == 3) { // Pinjaman Barang
                DB::table('tbl_barang')
                    ->where('id', $request->barang_id ?? 4)
                    ->where('type', '<>', 'uang')
                    ->decrement('jml_brg', 1);
            }

            // 5. Insert data pinjaman ke tbl_pinjaman_h (seperti project lama)
            $pinjaman = new TblPinjamanH();
            $pinjaman->id = $id_trans;
            $pinjaman->no_ktp = $anggota->no_ktp ?? '';
            $pinjaman->tgl_pinjam = $request->tgl_pinjam;
            $pinjaman->anggota_id = $request->anggota_id;
            $pinjaman->barang_id = 4; // Default untuk pinjaman uang
            $pinjaman->lama_angsuran = $request->lama_angsuran;
            $pinjaman->setAttribute('jumlah_angsuran', $jumlahAngsuran);
            $pinjaman->jumlah = $request->jumlah;
            $pinjaman->bunga = $request->jasa;
            $pinjaman->bunga_rp = $request->jasa;
            $pinjaman->biaya_adm = 0; // Biaya admin = 0
            $pinjaman->lunas = 'Belum';
            $pinjaman->dk = 'K'; // Kredit
            $pinjaman->kas_id = $request->kas_id;
            $pinjaman->jns_trans = '7'; // Jenis transaksi pinjaman
            $pinjaman->status = '1'; // Aktif
            $pinjaman->jenis_pinjaman = $request->jenis_pinjaman;
            $pinjaman->keterangan = $request->keterangan;
            $pinjaman->user_name = Auth::user()->name ?? 'admin';
            $pinjaman->id_cabang = '001'; // Default cabang
            $pinjaman->update_data = now();
            $pinjaman->save();

            // 7. Insert ke tbl_trans_kas untuk jurnal akuntansi (seperti project lama)
            DB::table('tbl_trans_kas')->insert([
                'tgl_catat' => $request->tgl_pinjam,
                'keterangan' => 'Pinjaman ' . $anggota->nama . ' - ' . $request->keterangan,
                'jumlah' => $request->jumlah,
                'akun' => 'Pengeluaran',
                'dari_kas_id' => null, // NULL untuk pengeluaran
                'untuk_kas_id' => $request->kas_id,
                'jns_trans' => 7, // Jenis transaksi pinjaman
                'dk' => 'K', // Kredit untuk pengeluaran
                'no_polisi' => '', // Kosong untuk pinjaman
                'user_name' => Auth::user()->name ?? 'admin',
                'update_data' => now(),
                'id_cabang' => 1
            ]);

            // 8. Generate tempo_pinjaman menggunakan method yang sudah ada
            $this->generateTempoPinjaman($pinjaman, null);

            // 9. Generate billing data untuk pinjaman (seperti project lama)
            $this->generateBillingDataForPinjaman($pinjaman, null);

            DB::commit();

            // Response JSON seperti project lama
            return response()->json([
                'success' => true,
                'message' => 'Data berhasil disimpan',
                'data' => [
                    'id' => $pinjaman->id,
                    'anggota' => $anggota->nama,
                    'jumlah' => $pinjaman->jumlah,
                    'lama_angsuran' => $pinjaman->lama_angsuran
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage()
            ], 500);
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