<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TblPinjamanH;
use App\Models\TblPinjamanD;
use App\Models\data_anggota;
use App\Models\DataKas;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DataAngsuranController extends Controller
{
    public function index()
    {
        // Ambil data pinjaman yang sudah aktif dan belum lunas
        $dataPinjaman = TblPinjamanH::with('anggota')
            ->where('status', '1') // Aktif
            ->where('lunas', 'Belum') // Belum lunas
            ->orderByDesc('tgl_pinjam')
            ->paginate(10);

        return view('pinjaman.data_angsuran', compact('dataPinjaman'));
    }

    public function show(string $id)
    {
        $pinjaman = TblPinjamanH::with(['anggota', 'detail_angsuran'])->findOrFail($id);
        
        // Hitung sisa angsuran
        $totalBayar = $pinjaman->detail_angsuran->sum('jumlah_bayar');
        $sisaAngsuran = $pinjaman->jumlah - $totalBayar;
        $angsuranKe = $pinjaman->detail_angsuran->count() + 1;
        
        // Hitung jatuh tempo
        $tglTempo = Carbon::parse($pinjaman->tgl_pinjam)->addMonths($angsuranKe);
        $denda = 0;
        if ($tglTempo < now()) {
            $selisihHari = now()->diffInDays($tglTempo);
            $denda = $selisihHari * 1000; // Denda per hari
        }

        // Hitung total tagihan yang benar (termasuk bunga dan biaya admin)
        $totalTagihan = $this->hitungTotalTagihan($pinjaman);
        $sisaTagihan = $totalTagihan - $totalBayar;

        return view('pinjaman.detail_angsuran', compact('pinjaman', 'sisaAngsuran', 'angsuranKe', 'tglTempo', 'denda', 'totalTagihan', 'sisaTagihan'));
    }

    public function store(Request $request, string $pinjamanId)
    {
        $request->validate([
            'tgl_bayar' => 'required|date',
            'jumlah_bayar' => 'required|numeric|min:0',
            'bunga' => 'required|numeric|min:0',
            'biaya_adm' => 'required|numeric|min:0',
            'denda_rp' => 'nullable|numeric|min:0',
            'keterangan' => 'nullable|string|max:500',
            'kas_id' => 'required|exists:data_kas,id'
        ]);

        try {
            DB::beginTransaction();

            $pinjaman = TblPinjamanH::findOrFail($pinjamanId);
            
            // Cek apakah sudah lunas
            if ($pinjaman->lunas === 'Lunas') {
                throw new \Exception('Pinjaman sudah lunas');
            }

            // Hitung angsuran ke berapa
            $angsuranKe = $pinjaman->detail_angsuran()->count() + 1;
            
            // Cek apakah melebihi lama angsuran
            if ($angsuranKe > $pinjaman->lama_angsuran) {
                throw new \Exception('Angsuran melebihi jadwal yang ditentukan');
            }

            // Validasi: Mencegah pembayaran langsung lunas pada angsuran pertama
            $totalBayar = $pinjaman->detail_angsuran->sum('jumlah_bayar');
            $totalTagihan = $this->hitungTotalTagihan($pinjaman);
            $sisaTagihan = $totalTagihan - $totalBayar;
            
            // Validasi 1: Jika ini angsuran pertama dan jumlah bayar melebihi sisa tagihan
            if ($angsuranKe === 1 && $request->jumlah_bayar >= $sisaTagihan) {
                throw new \Exception('Pembayaran angsuran pertama tidak boleh langsung lunas. Gunakan menu Pelunasan untuk membayar langsung lunas.');
            }
            
            // Validasi 2: Pastikan pembayaran tidak melebihi sisa tagihan untuk semua angsuran
            if ($request->jumlah_bayar > $sisaTagihan) {
                throw new \Exception('Jumlah pembayaran melebihi sisa tagihan. Sisa tagihan: Rp ' . number_format($sisaTagihan, 0, ',', '.'));
            }
            
            // Validasi 3: Pastikan pembayaran minimal sesuai dengan angsuran per bulan
            $minPembayaran = $pinjaman->jumlah_angsuran;
            if ($request->jumlah_bayar < $minPembayaran) {
                throw new \Exception('Jumlah pembayaran minimal: Rp ' . number_format($minPembayaran, 0, ',', '.'));
            }

            // Tentukan ket_bayar berdasarkan jumlah pembayaran
            $ketBayar = 'Angsuran';
            if ($request->jumlah_bayar >= $sisaTagihan) {
                $ketBayar = 'Pelunasan';
            }

            // Buat record angsuran
            $angsuran = new TblPinjamanD();
            $angsuran->tgl_bayar = $request->tgl_bayar;
            $angsuran->pinjam_id = $pinjamanId;
            $angsuran->angsuran_ke = $angsuranKe;
            $angsuran->jumlah_bayar = $request->jumlah_bayar;
            $angsuran->bunga = $request->bunga;
            $angsuran->denda_rp = $request->denda_rp ?? 0;
            $angsuran->biaya_adm = $request->biaya_adm;
            $angsuran->terlambat = $request->denda_rp > 0 ? 1 : 0;
            $angsuran->ket_bayar = $ketBayar;
            $angsuran->dk = 'D';
            $angsuran->kas_id = $request->kas_id;
            $angsuran->jns_trans = '48'; // Jenis transaksi angsuran
            $angsuran->keterangan = $request->keterangan;
            $angsuran->user_name = 'admin';
            $angsuran->id_cabang = 1; // Default cabang ID
            $angsuran->save();

            // Update status lunas jika sudah lunas
            $this->updateStatusLunas($pinjamanId);

            DB::commit();

            Log::info('Angsuran berhasil disimpan', [
                'pinjaman_id' => $pinjamanId,
                'angsuran_ke' => $angsuranKe,
                'jumlah_bayar' => $request->jumlah_bayar,
                'ket_bayar' => $ketBayar
            ]);

            return back()->with('success', 'Angsuran berhasil disimpan');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Gagal simpan angsuran', [
                'pinjaman_id' => $pinjamanId,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Gagal simpan angsuran: ' . $e->getMessage());
        }
    }

    public function edit(string $id)
    {
        $angsuran = TblPinjamanD::with(['pinjaman.anggota'])->findOrFail($id);
        $kasList = DataKas::all();
        
        return view('pinjaman.edit_angsuran', compact('angsuran', 'kasList'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'tgl_bayar' => 'required|date',
            'jumlah_bayar' => 'required|numeric|min:0',
            'bunga' => 'required|numeric|min:0',
            'biaya_adm' => 'required|numeric|min:0',
            'denda_rp' => 'nullable|numeric|min:0',
            'keterangan' => 'nullable|string|max:500',
            'kas_id' => 'required|exists:data_kas,id'
        ]);

        try {
            $angsuran = TblPinjamanD::findOrFail($id);
            
            $angsuran->tgl_bayar = $request->tgl_bayar;
            $angsuran->jumlah_bayar = $request->jumlah_bayar;
            $angsuran->bunga = $request->bunga;
            $angsuran->denda_rp = $request->denda_rp ?? 0;
            $angsuran->biaya_adm = $request->biaya_adm;
            $angsuran->terlambat = $request->denda_rp > 0 ? 1 : 0;
            $angsuran->kas_id = $request->kas_id;
            $angsuran->keterangan = $request->keterangan;
            $angsuran->update_data = now();
            $angsuran->save();

            // Update status lunas
            $this->updateStatusLunas($angsuran->pinjam_id);

            Log::info('Angsuran berhasil diupdate', ['angsuran_id' => $id]);

            return back()->with('success', 'Angsuran berhasil diupdate');

        } catch (\Exception $e) {
            Log::error('Gagal update angsuran', [
                'angsuran_id' => $id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Gagal update angsuran: ' . $e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        try {
            $angsuran = TblPinjamanD::findOrFail($id);
            $pinjamanId = $angsuran->pinjam_id;
            
            $angsuran->delete();

            // Update status lunas
            $this->updateStatusLunas($pinjamanId);

            Log::info('Angsuran berhasil dihapus', ['angsuran_id' => $id]);

            return back()->with('success', 'Angsuran berhasil dihapus');

        } catch (\Exception $e) {
            Log::error('Gagal hapus angsuran', [
                'angsuran_id' => $id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Gagal hapus angsuran: ' . $e->getMessage());
        }
    }

    /**
     * Hitung total tagihan yang benar (termasuk bunga dan biaya admin)
     * Sesuai dengan logika project lama: ags_per_bulan × lama_angsuran + biaya admin
     */
    private function hitungTotalTagihan($pinjaman)
    {
        // Total tagihan = angsuran per bulan × lama angsuran + biaya admin
        // Ini sesuai dengan logika project lama yang menggunakan ags_per_bulan
        $totalTagihan = ($pinjaman->jumlah_angsuran * $pinjaman->lama_angsuran) + $pinjaman->biaya_adm;
        
        return $totalTagihan;
    }

    /**
     * Update status lunas dengan logika yang benar
     * Hanya mengubah kolom 'lunas', tidak mengubah kolom 'status' yang sudah bernilai 1
     * Terintegrasi dengan sistem billing untuk update tagihan
     */
    private function updateStatusLunas($pinjamanId)
    {
        $pinjaman = TblPinjamanH::find($pinjamanId);
        if (!$pinjaman) return;

        $totalBayar = $pinjaman->detail_angsuran->sum('jumlah_bayar');
        $totalTagihan = $this->hitungTotalTagihan($pinjaman);

        // Cek apakah sudah lunas
        if ($totalBayar >= $totalTagihan) {
            $pinjaman->lunas = 'Lunas';
            // Tidak mengubah kolom status, tetap 1 sesuai dengan struktur database
            $pinjaman->save();

            // Update billing tagihan - hapus tagihan yang sudah lunas
            $this->updateBillingTagihan($pinjamanId, 'lunas');

            Log::info('Status pinjaman diubah menjadi lunas', [
                'pinjaman_id' => $pinjamanId,
                'total_bayar' => $totalBayar,
                'total_tagihan' => $totalTagihan
            ]);
        } else {
            // Jika belum lunas, pastikan status tetap 'Belum'
            if ($pinjaman->lunas !== 'Belum') {
                $pinjaman->lunas = 'Belum';
                // Tidak mengubah kolom status, tetap 1 sesuai dengan struktur database
                $pinjaman->save();

                // Update billing tagihan - restore tagihan yang belum lunas
                $this->updateBillingTagihan($pinjamanId, 'belum_lunas');

                Log::info('Status pinjaman dikembalikan menjadi belum lunas', [
                    'pinjaman_id' => $pinjamanId,
                    'total_bayar' => $totalBayar,
                    'total_tagihan' => $totalTagihan
                ]);
            }
        }
    }

    /**
     * Update billing tagihan berdasarkan status lunas pinjaman
     */
    private function updateBillingTagihan($pinjamanId, $status)
    {
        try {
            if ($status === 'lunas') {
                // Hapus tagihan pinjaman yang sudah lunas dari tbl_trans_tagihan
                DB::table('tbl_trans_tagihan')
                    ->where('jenis_id', 999) // ID untuk jenis Pinjaman
                    ->where('keterangan', 'like', '%' . $pinjamanId . '%')
                    ->delete();
                
                Log::info('Tagihan pinjaman dihapus karena sudah lunas', [
                    'pinjaman_id' => $pinjamanId
                ]);
            } else {
                // Jika belum lunas, generate ulang tagihan yang belum dibayar
                $this->regenerateBillingTagihan($pinjamanId);
            }
        } catch (\Exception $e) {
            Log::error('Gagal update billing tagihan', [
                'pinjaman_id' => $pinjamanId,
                'status' => $status,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Regenerate billing tagihan untuk pinjaman yang belum lunas
     */
    private function regenerateBillingTagihan($pinjamanId)
    {
        try {
            // Ambil data pinjaman
            $pinjaman = TblPinjamanH::find($pinjamanId);
            if (!$pinjaman) return;

            // Ambil jadwal angsuran yang belum dibayar
            $jadwalBelumBayar = DB::table('tempo_pinjaman as t')
                ->leftJoin('tbl_pinjaman_d as d', function($join) {
                    $join->on('t.pinjam_id', '=', 'd.pinjam_id')
                         ->on('t.no_urut', '=', 'd.angsuran_ke');
                })
                ->where('t.pinjam_id', $pinjamanId)
                ->whereNull('d.tgl_bayar')
                ->get();

            foreach ($jadwalBelumBayar as $jadwal) {
                // Hitung angsuran per bulan
                $angsuranPokok = $pinjaman->jumlah / $pinjaman->lama_angsuran;
                $angsuranBunga = $pinjaman->bunga_rp / $pinjaman->lama_angsuran;
                $totalAngsuran = $angsuranPokok + $angsuranBunga;

                // Insert atau update tagihan
                DB::table('tbl_trans_tagihan')->updateOrInsert(
                    [
                        'jenis_id' => 999, // ID untuk jenis Pinjaman
                        'keterangan' => 'Tagihan Angsuran Pinjaman ke-' . $jadwal->no_urut . ' - Jatuh Tempo: ' . $jadwal->tempo
                    ],
                    [
                        'tgl_transaksi' => $jadwal->tempo,
                        'no_ktp' => $jadwal->no_ktp,
                        'anggota_id' => null,
                        'jumlah' => $totalAngsuran,
                        'akun' => 'Tagihan',
                        'dk' => 'K',
                        'kas_id' => 1,
                        'jns_trans' => 48,
                        'user_name' => 'admin',
                        'updated_at' => now()
                    ]
                );
            }

            Log::info('Billing tagihan berhasil di-regenerate', [
                'pinjaman_id' => $pinjamanId,
                'jumlah_tagihan' => count($jadwalBelumBayar)
            ]);

        } catch (\Exception $e) {
            Log::error('Gagal regenerate billing tagihan', [
                'pinjaman_id' => $pinjamanId,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function cetak(string $id)
    {
        $angsuran = TblPinjamanD::with(['pinjaman.anggota'])->findOrFail($id);
        
        // Generate PDF atau view cetak
        return view('pinjaman.cetak_angsuran', compact('angsuran'));
    }
}