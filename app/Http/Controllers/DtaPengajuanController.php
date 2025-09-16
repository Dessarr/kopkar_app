<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\data_pengajuan;
use App\Models\TblPinjamanH;
use App\Models\suku_bunga;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DtaPengajuanController extends Controller
{
    public function index(Request $request)
    {
        $query = data_pengajuan::with('anggota');

        // Filter by date range
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('tgl_input', [$request->date_from, $request->date_to]);
        } elseif ($request->filled('date_from')) {
            $query->whereDate('tgl_input', '>=', $request->date_from);
        } elseif ($request->filled('date_to')) {
            $query->whereDate('tgl_input', '<=', $request->date_to);
        }

        // Filter by jenis
        if ($request->filled('jenis')) {
            $query->where('jenis', $request->jenis);
        }

        // Filter by status
        if ($request->filled('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter by anggota (search by name or ID)
        if ($request->filled('anggota')) {
            $searchTerm = $request->anggota;
            $query->whereHas('anggota', function($q) use ($searchTerm) {
                $q->where('nama', 'like', "%{$searchTerm}%")
                  ->orWhere('id', 'like', "%{$searchTerm}%")
                  ->orWhere('no_ktp', 'like', "%{$searchTerm}%");
            });
        }

        // Handle export
        if ($request->has('export') && $request->export === 'pdf') {
            return $this->exportPdf($query->get());
        }

        $dataPengajuan = $query->orderByDesc('tgl_input')->paginate(10);
        
        // Calculate remaining loan information for each application
        foreach ($dataPengajuan as $pengajuan) {
            if ($pengajuan->status == 0) {
                // Menunggu konfirmasi - belum ada pinjaman aktif
                $pengajuan->sisa_pinjaman = 0;
                $pengajuan->sisa_angsuran = 0;
                $pengajuan->sisa_tagihan = 0;
            } elseif ($pengajuan->status == 4) {
                // Batal - cek apakah ada pinjaman aktif untuk anggota ini
                $pinjamanAktif = \App\Models\TblPinjamanH::where('anggota_id', $pengajuan->anggota_id)
                    ->where('lunas', 'Belum')
                    ->first();
                
                if ($pinjamanAktif) {
                    // Ada pinjaman aktif (sempat disetujui)
                    $pengajuan->sisa_pinjaman = 1;
                    
                    // Hitung sisa angsuran
                    $sudahBayar = \App\Models\TblPinjamanD::where('pinjam_id', $pinjamanAktif->id)
                        ->where('ket_bayar', 'Angsuran')
                        ->count();
                    $pengajuan->sisa_angsuran = $pinjamanAktif->lama_angsuran - $sudahBayar;
                    
                    // Hitung sisa tagihan
                    $totalTagihan = ($pinjamanAktif->jumlah_angsuran * $pinjamanAktif->lama_angsuran) + $pinjamanAktif->biaya_adm;
                    $totalBayar = \App\Models\TblPinjamanD::where('pinjam_id', $pinjamanAktif->id)->sum('jumlah_bayar');
                    $totalDenda = \App\Models\TblPinjamanD::where('pinjam_id', $pinjamanAktif->id)->sum('denda_rp');
                    $pengajuan->sisa_tagihan = $totalTagihan - $totalBayar + $totalDenda;
                } else {
                    // Tidak ada pinjaman aktif
                    $pengajuan->sisa_pinjaman = 0;
                    $pengajuan->sisa_angsuran = 0;
                    $pengajuan->sisa_tagihan = 0;
                }
            } else {
                // Status lain (Disetujui, Terlaksana, dll) - hitung sisa pinjaman
                $remaining = $this->calculateRemainingLoan($pengajuan->id);
                $pengajuan->sisa_pinjaman = $remaining['sisa_pinjaman'];
                $pengajuan->sisa_angsuran = $remaining['sisa_angsuran'];
                $pengajuan->sisa_tagihan = $remaining['sisa_tagihan'];
            }
        }
        
        // Append query parameters to pagination links
        $dataPengajuan->appends($request->query());
        
        return view('pinjaman.data_pengajuan', compact('dataPengajuan'));
    }

    public function approve(string $id, Request $request)
    {
        try {
            DB::beginTransaction();

            $pengajuan = data_pengajuan::findOrFail($id);
            
            // Validasi: hanya bisa dari status pending
            if ($pengajuan->status != 0) {
                return back()->with('error', 'Hanya pengajuan yang masih pending yang dapat disetujui');
            }

            // Update status pengajuan menjadi disetujui
            $pengajuan->status = 1; // Disetujui
            $pengajuan->alasan = $request->input('alasan') ?: 'Disetujui tanpa alasan khusus';
            $pengajuan->tgl_cair = $request->input('tgl_cair', now());
            $pengajuan->tgl_update = now();
            $pengajuan->save();

            // INSERT KE tbl_pinjaman_h (sesuai alur yang benar)
            $pinjaman = new TblPinjamanH();
            
            // Generate ID transaksi unik (format: YYMM + 4 digit counter)
            $counter = 1;
            do {
                $id_trans = date('ym') . str_pad($counter, 4, '0', STR_PAD_LEFT);
                $existing = TblPinjamanH::find($id_trans);
                $counter++;
            } while ($existing && $counter < 10000); // Prevent infinite loop
            
            // Validasi panjang ID (maksimal 8 digit)
            if (strlen($id_trans) > 8) {
                throw new \Exception('ID transaksi terlalu panjang: ' . $id_trans);
            }
            
            Log::info('ID transaksi di-generate', [
                'id_trans' => $id_trans,
                'length' => strlen($id_trans),
                'counter' => $counter - 1
            ]);
            
            // Ambil data suku bunga berdasarkan jenis pinjaman
            $jenisBunga = $pengajuan->jenis == '1' ? 'bunga_biasa' : 'bunga_barang';
            $sukuBunga = suku_bunga::where('opsi_key', $jenisBunga)->first();
            
            if (!$sukuBunga) {
                Log::warning('Suku bunga tidak ditemukan', [
                    'opsi_key' => $jenisBunga,
                    'pengajuan_id' => $id,
                    'jenis_pinjaman' => $pengajuan->jenis
                ]);
            }
            
            $bungaPersen = $sukuBunga ? (float)$sukuBunga->opsi_val : 12; // Default 12%
            
            // Jika suku bunga tidak ditemukan, buat default values
            if (!$sukuBunga) {
                $this->createDefaultSukuBunga($jenisBunga);
            }
            
            // Hitung bunga dalam rupiah
            $bungaRp = ($bungaPersen * $pengajuan->nominal) / 100;
            
            // Hitung jumlah angsuran per bulan
            $jumlahAngsuran = $pengajuan->nominal / $pengajuan->lama_ags;
            
            // Biaya admin diatur menjadi 0
            $biayaAdmin = 0;

            $pinjaman->id = $id_trans;
            $pinjaman->no_ktp = $pengajuan->anggota->no_ktp ?? '';
            // PERBAIKAN: Ambil waktu dari pengajuan, bukan dari tgl_cair
            $tglPinjam = $pengajuan->tgl_input; // Gunakan waktu pengajuan asli
            
            // Jika tgl_input hanya DATE, tambah waktu default
            if (strlen($tglPinjam) <= 10) {
                $tglPinjam .= ' 09:00:00'; // Tambah waktu default 09:00
            }
            
            // Log untuk debugging waktu
            Log::info('Waktu pinjaman di-set', [
                'pengajuan_tgl_input' => $pengajuan->tgl_input,
                'tgl_pinjam_final' => $tglPinjam,
                'panjang_input' => strlen($pengajuan->tgl_input),
                'panjang_final' => strlen($tglPinjam)
            ]);
            
            $pinjaman->tgl_pinjam = $tglPinjam;
            $pinjaman->anggota_id = $pengajuan->anggota_id;
            $pinjaman->barang_id = 4; // Default barang (uang)
            $pinjaman->lama_angsuran = $pengajuan->lama_ags;
            $pinjaman->setAttribute('jumlah_angsuran', $jumlahAngsuran);
            $pinjaman->setAttribute('jumlah', $pengajuan->nominal);
            $pinjaman->setAttribute('bunga', $bungaPersen);
            $pinjaman->setAttribute('bunga_rp', $bungaRp);
            $pinjaman->biaya_adm = $biayaAdmin;
            $pinjaman->lunas = 'Belum';
            $pinjaman->dk = 'K';
            $pinjaman->kas_id = 2; // Default kas sumber
            $pinjaman->jns_trans = 7; // Jenis transaksi pinjaman
            $pinjaman->status = '1'; // Status pinjaman aktif
            $pinjaman->jenis_pinjaman = $pengajuan->jenis;
            $pinjaman->keterangan = $pengajuan->keterangan ?? '';
            $pinjaman->user_name = 'admin';
            $pinjaman->id_cabang = 1; // Default cabang ID
            $pinjaman->save();

            // TIDAK generate tempo_pinjaman di sini
            // Tempo_pinjaman akan di-generate saat status menjadi Terlaksana

            DB::commit();

            Log::info('Pengajuan disetujui dan data pinjaman dibuat', [
                'pengajuan_id' => $id,
                'pinjaman_id' => $id_trans,
                'anggota_id' => $pengajuan->anggota_id,
                'tgl_pengajuan' => $pengajuan->tgl_input,
                'tgl_pinjaman' => $tglPinjam,
                'nominal' => $pengajuan->nominal,
                'lama_angsuran' => $pengajuan->lama_ags
            ]);

            return back()->with('success', 'Pengajuan disetujui dan data pinjaman berhasil dibuat');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Gagal menyetujui pengajuan', [
                'pengajuan_id' => $id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Gagal menyetujui pengajuan: ' . $e->getMessage());
        }
    }

    public function reject(string $id, Request $request)
    {
        $request->validate(['alasan' => ['required','string','max:500']]);
        $pengajuan = data_pengajuan::findOrFail($id);
        $pengajuan->status = 2; // Ditolak
        $pengajuan->alasan = $request->alasan;
        $pengajuan->tgl_update = now();
        $pengajuan->save();
        Log::info('Admin menolak pengajuan', ['id'=>$id, 'ajuan_id'=>$pengajuan->ajuan_id, 'alasan'=>$request->alasan]);
        return back()->with('success', 'Pengajuan ditolak');
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
                        'tgl_transaksi' => \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->toDateString(),
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

    public function cancel(string $id)
    {
        $pengajuan = data_pengajuan::findOrFail($id);
        $pengajuan->status = 4; // Batal
        $pengajuan->alasan = 'Dibatalkan oleh admin';
        $pengajuan->tgl_update = now();
        $pengajuan->save();
        Log::info('Admin membatalkan pengajuan', ['id'=>$id, 'ajuan_id'=>$pengajuan->ajuan_id]);
        return back()->with('success', 'Pengajuan dibatalkan');
    }

    public function destroy(string $id)
    {
        try {
            $pengajuan = data_pengajuan::findOrFail($id);
            $ajuanId = $pengajuan->ajuan_id; // Simpan ajuan_id sebelum delete
            
            // Hard delete - hapus permanen dari database
            $pengajuan->delete();
            
            // Clear cache jika ada
            if (class_exists('\Illuminate\Support\Facades\Cache')) {
                \Illuminate\Support\Facades\Cache::flush();
            }
            
            Log::warning('Admin menghapus pengajuan', [
                'id' => $id, 
                'ajuan_id' => $ajuanId,
                'deleted_at' => now()
            ]);
            
            return back()->with('success', 'Pengajuan berhasil dihapus permanen');
            
        } catch (\Exception $e) {
            Log::error('Gagal menghapus pengajuan', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Gagal menghapus pengajuan: ' . $e->getMessage());
        }
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
                
                Log::info('Status pengajuan berhasil diubah menjadi terlaksana, jadwal angsuran dibuat, dan billing data di-generate', [
                    'pengajuan_id' => $id,
                    'pinjaman_id' => $pinjaman->id,
                    'anggota_id' => $pengajuan->anggota_id
                ]);
            } else {
                Log::warning('Data pinjaman tidak ditemukan saat terlaksana', [
                    'pengajuan_id' => $id,
                    'anggota_id' => $pengajuan->anggota_id
                ]);
            }

            DB::commit();

            Log::info('Admin mengubah status pengajuan menjadi terlaksana', [
                'id' => $id, 
                'ajuan_id' => $pengajuan->ajuan_id
            ]);

            return back()->with('success', 'Status pengajuan berhasil diubah menjadi terlaksana');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Gagal ubah status menjadi terlaksana', [
                'id' => $id, 
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Gagal ubah status: ' . $e->getMessage());
        }
    }

    /**
     * Buat default suku bunga jika tidak ada
     */
    private function createDefaultSukuBunga($jenisBunga)
    {
        try {
            $defaultValues = [
                'bunga_biasa' => '12',
                'bunga_barang' => '15',
                'biaya_adm' => '1'
            ];

            if (isset($defaultValues[$jenisBunga])) {
                suku_bunga::create([
                    'opsi_key' => $jenisBunga,
                    'opsi_val' => $defaultValues[$jenisBunga],
                    'id_cabang' => '1'
                ]);

                Log::info('Default suku bunga dibuat', [
                    'opsi_key' => $jenisBunga,
                    'opsi_val' => $defaultValues[$jenisBunga]
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Gagal membuat default suku bunga', [
                'jenis_bunga' => $jenisBunga,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function cetak(string $id)
    {
        $pengajuan = data_pengajuan::findOrFail($id);
        return view('pinjaman.cetak_pengajuan_admin', compact('pengajuan'));
    }

    /**
     * Export data pengajuan to PDF
     */
    private function exportPdf($dataPengajuan)
    {
        // Generate PDF using DomPDF or similar library
        // For now, we'll return a simple view
        return view('pinjaman.export_pengajuan', compact('dataPengajuan'));
    }

    /**
     * Calculate remaining loan information
     */
    private function calculateRemainingLoan($pengajuanId)
    {
        $sisaPinjaman = 0;
        $sisaAngsuran = 0;
        $sisaTagihan = 0;

        // Get pengajuan data to get anggota_id
        $pengajuan = data_pengajuan::find($pengajuanId);
        if (!$pengajuan) {
            return [
                'sisa_pinjaman' => 0,
                'sisa_angsuran' => 0,
                'sisa_tagihan' => 0
            ];
        }

        // Get active loan for this anggota (not pengajuan_id)
        $pinjamanAktif = \App\Models\TblPinjamanH::where('anggota_id', $pengajuan->anggota_id)
            ->where('lunas', 'Belum')
            ->first();
        
        if ($pinjamanAktif) {
            // Still active loan
            $sisaPinjaman = 1; // 1 active loan
            
            // Calculate remaining installments
            $sudahBayar = \App\Models\TblPinjamanD::where('pinjam_id', $pinjamanAktif->id)
                ->where('ket_bayar', 'Angsuran')
                ->count();
            $sisaAngsuran = $pinjamanAktif->lama_angsuran - $sudahBayar;
            
            // Calculate remaining bill
            $totalTagihan = ($pinjamanAktif->jumlah_angsuran * $pinjamanAktif->lama_angsuran) + $pinjamanAktif->biaya_adm;
            $totalBayar = \App\Models\TblPinjamanD::where('pinjam_id', $pinjamanAktif->id)->sum('jumlah_bayar');
            $totalDenda = \App\Models\TblPinjamanD::where('pinjam_id', $pinjamanAktif->id)->sum('denda_rp');
            $sisaTagihan = $totalTagihan - $totalBayar + $totalDenda;
        }

        return [
            'sisa_pinjaman' => $sisaPinjaman,
            'sisa_angsuran' => $sisaAngsuran,
            'sisa_tagihan' => $sisaTagihan
        ];
    }

    /**
     * Update field inline
     */
    public function updateField(Request $request, string $id)
    {
        try {
            $pengajuan = data_pengajuan::findOrFail($id);
            
            // Validasi: hanya bisa edit jika status masih menunggu konfirmasi
            if ($pengajuan->status != 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya pengajuan yang masih menunggu konfirmasi yang dapat diedit'
                ]);
            }
            
            $field = $request->input('field');
            $value = $request->input('value');
            
            // Validasi field yang diizinkan
            $allowedFields = ['nominal', 'lama_ags', 'keterangan'];
            if (!in_array($field, $allowedFields)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Field tidak diizinkan untuk diedit'
                ]);
            }
            
            // Validasi nilai
            if ($field === 'nominal') {
                if (!is_numeric($value) || $value <= 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Nominal harus berupa angka positif'
                    ]);
                }
            } elseif ($field === 'lama_ags') {
                if (!is_numeric($value) || $value <= 0 || $value > 60) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Lama angsuran harus antara 1-60 bulan'
                    ]);
                }
            } elseif ($field === 'keterangan') {
                if (strlen($value) > 500) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Keterangan maksimal 500 karakter'
                    ]);
                }
            }
            
            // Update field
            $pengajuan->$field = $value;
            $pengajuan->tgl_update = now();
            $pengajuan->save();
            
            Log::info('Admin mengupdate field pengajuan', [
                'id' => $id,
                'field' => $field,
                'old_value' => $pengajuan->getOriginal($field),
                'new_value' => $value
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diperbarui',
                'data' => [
                    'field' => $field,
                    'value' => $value
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Gagal update field pengajuan', [
                'id' => $id,
                'field' => $request->input('field'),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }
}