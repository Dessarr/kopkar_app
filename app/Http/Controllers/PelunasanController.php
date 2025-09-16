<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TblPinjamanH;
use App\Models\TblPinjamanD;
use App\Models\DataKas;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PelunasanController extends Controller
{
    public function index()
    {
        // Ambil data pinjaman yang sudah aktif dan belum lunas
        $dataPinjaman = TblPinjamanH::with('anggota')
            ->where('status', '1') // Aktif
            ->where('lunas', 'Belum') // Belum lunas
            ->orderByDesc('tgl_pinjam')
            ->paginate(10);

        return view('pinjaman.pelunasan', compact('dataPinjaman'));
    }

    public function show(string $id)
    {
        $pinjaman = TblPinjamanH::with(['anggota', 'detail_angsuran'])->findOrFail($id);
        
        // Hitung total yang sudah dibayar
        $totalBayar = $pinjaman->detail_angsuran->sum('jumlah_bayar');
        
        // Hitung total tagihan yang benar (termasuk bunga dan biaya admin)
        $totalTagihan = $this->hitungTotalTagihan($pinjaman);
        $sisaTagihan = $totalTagihan - $totalBayar;
        
        // Hitung denda yang sudah ada
        $totalDenda = $pinjaman->detail_angsuran->sum('denda_rp');
        
        // Hitung jatuh tempo untuk denda
        $tglTempo = Carbon::parse($pinjaman->tgl_pinjam)->addMonths($pinjaman->lama_angsuran);
        $dendaKeterlambatan = 0;
        if ($tglTempo < now()) {
            $selisihHari = now()->diffInDays($tglTempo);
            $dendaKeterlambatan = $selisihHari * 1000; // Denda per hari
        }

        return view('pinjaman.detail_pelunasan', compact(
            'pinjaman', 
            'totalTagihan', 
            'sisaTagihan', 
            'totalDenda', 
            'dendaKeterlambatan'
        ));
    }

    public function store(Request $request, string $pinjamanId)
    {
        $request->validate([
            'tgl_bayar' => 'required|date',
            'jumlah_bayar' => 'required|numeric|min:0',
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

            // Hitung total tagihan
            $totalTagihan = $this->hitungTotalTagihan($pinjaman);
            $totalBayar = $pinjaman->detail_angsuran->sum('jumlah_bayar');
            $sisaTagihan = $totalTagihan - $totalBayar;
            
            // Validasi jumlah pembayaran
            if ($request->jumlah_bayar < $sisaTagihan) {
                throw new \Exception('Jumlah pembayaran tidak mencukupi untuk melunasi pinjaman. Sisa tagihan: Rp ' . number_format($sisaTagihan, 0, ',', '.'));
            }

            // Hitung angsuran ke berapa
            $angsuranKe = $pinjaman->detail_angsuran()->count() + 1;

            // Buat record pelunasan
            $pelunasan = new TblPinjamanD();
            $pelunasan->tgl_bayar = $request->tgl_bayar;
            $pelunasan->pinjam_id = $pinjamanId;
            $pelunasan->angsuran_ke = $angsuranKe;
            $pelunasan->setAttribute('jumlah_bayar', $request->jumlah_bayar);
            $pelunasan->setAttribute('bunga', 0); // Tidak ada bunga untuk pelunasan
            $pelunasan->setAttribute('denda_rp', 0); // Denda sudah dihitung sebelumnya
            $pelunasan->setAttribute('biaya_adm', 0); // Tidak ada biaya admin untuk pelunasan
            $pelunasan->terlambat = 0;
            $pelunasan->ket_bayar = 'Pelunasan'; // Beda dengan Angsuran
            $pelunasan->dk = 'D';
            $pelunasan->kas_id = $request->kas_id;
            $pelunasan->jns_trans = '49'; // Jenis transaksi pelunasan
            $pelunasan->keterangan = $request->keterangan;
            $pelunasan->user_name = 'admin';
            $pelunasan->id_cabang = 1; // Default cabang ID
            $pelunasan->save();

            // Update status lunas
            $pinjaman->lunas = 'Lunas';
            // Tidak mengubah kolom status, tetap 1 sesuai dengan struktur database
            $pinjaman->save();

            // Update billing tagihan - hapus semua tagihan pinjaman yang sudah lunas
            $this->updateBillingTagihanLunas($pinjamanId);

            DB::commit();

            Log::info('Pelunasan berhasil disimpan', [
                'pinjaman_id' => $pinjamanId,
                'angsuran_ke' => $angsuranKe,
                'jumlah_bayar' => $request->jumlah_bayar,
                'ket_bayar' => 'Pelunasan'
            ]);

            return back()->with('success', 'Pinjaman berhasil dilunasi');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Gagal simpan pelunasan', [
                'pinjaman_id' => $pinjamanId,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Gagal simpan pelunasan: ' . $e->getMessage());
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
     * Update billing tagihan ketika pinjaman lunas
     */
    private function updateBillingTagihanLunas($pinjamanId)
    {
        try {
            // Ambil data pinjaman untuk mendapatkan no_ktp
            $pinjaman = TblPinjamanH::find($pinjamanId);
            if (!$pinjaman) return;

            // Update tagihan pinjaman yang sudah lunas di tbl_trans_tagihan
            // Jangan hapus, tapi update status atau tambahkan flag lunas
            $updatedCount = DB::table('tbl_trans_tagihan')
                ->where('jenis_id', 999) // ID untuk jenis Pinjaman
                ->where('no_ktp', $pinjaman->no_ktp)
                ->where('keterangan', 'like', '%' . $pinjamanId . '%')
                ->update([
                    'status_lunas' => 'Y', // Tambahkan flag status lunas
                    'updated_at' => now()
                ]);
            
            // Update juga di tbl_trans_sp_bayar_temp untuk konsistensi
            DB::table('tbl_trans_sp_bayar_temp')
                ->where('no_ktp', $pinjaman->no_ktp)
                ->where('tagihan_pinjaman', '>', 0)
                ->update([
                    'status_lunas' => 'Y',
                    'updated_at' => now()
                ]);
            
            Log::info('Tagihan pinjaman diupdate status lunas', [
                'pinjaman_id' => $pinjamanId,
                'no_ktp' => $pinjaman->no_ktp,
                'updated_count' => $updatedCount
            ]);
            
        } catch (\Exception $e) {
            Log::error('Gagal update billing tagihan lunas', [
                'pinjaman_id' => $pinjamanId,
                'error' => $e->getMessage()
            ]);
        }
    }
}