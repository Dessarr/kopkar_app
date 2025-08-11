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

        return view('pinjaman.detail_angsuran', compact('pinjaman', 'sisaAngsuran', 'angsuranKe', 'tglTempo', 'denda'));
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
            $angsuran->ket_bayar = 'Angsuran';
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
                'jumlah_bayar' => $request->jumlah_bayar
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

    private function updateStatusLunas($pinjamanId)
    {
        $pinjaman = TblPinjamanH::find($pinjamanId);
        if (!$pinjaman) return;

        $totalBayar = $pinjaman->detail_angsuran()->sum('jumlah_bayar');
        $totalTagihan = $pinjaman->jumlah + $pinjaman->biaya_adm;

        if ($totalBayar >= $totalTagihan) {
            $pinjaman->lunas = 'Lunas';
            $pinjaman->status = '3'; // Terlaksana
            $pinjaman->save();

            Log::info('Status pinjaman diubah menjadi lunas', ['pinjaman_id' => $pinjamanId]);
        }
    }

    public function cetak(string $id)
    {
        $angsuran = TblPinjamanD::with(['pinjaman.anggota'])->findOrFail($id);
        
        // Generate PDF atau view cetak
        return view('pinjaman.cetak_angsuran', compact('angsuran'));
    }
}
