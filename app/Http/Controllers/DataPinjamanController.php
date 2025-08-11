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
    public function index()
    {
        // Ambil data pinjaman yang sudah disetujui (status = 1) atau terlaksana (status = 3)
        $dataPinjaman = TblPinjamanH::with('anggota')
            ->whereIn('status', ['1', '3']) // Aktif atau Terlaksana
            ->orderByDesc('tgl_pinjam')
            ->paginate(10);

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

    public function edit(string $id)
    {
        $pinjaman = TblPinjamanH::with('anggota')->findOrFail($id);
        return view('pinjaman.edit_pinjaman', compact('pinjaman'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'lama_angsuran' => 'required|integer|min:1|max:60',
            'bunga' => 'required|numeric|min:0|max:100',
            'biaya_adm' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string|max:500'
        ]);

        try {
            $pinjaman = TblPinjamanH::findOrFail($id);
            
            // Hitung ulang jumlah angsuran
            $jumlahAngsuran = $pinjaman->jumlah / $request->lama_angsuran;
            $bungaRp = ($request->bunga * $pinjaman->jumlah) / 100;

            $pinjaman->lama_angsuran = $request->lama_angsuran;
            $pinjaman->jumlah_angsuran = $jumlahAngsuran;
            $pinjaman->bunga = $request->bunga;
            $pinjaman->bunga_rp = $bungaRp;
            $pinjaman->biaya_adm = $request->biaya_adm;
            $pinjaman->keterangan = $request->keterangan;
            $pinjaman->update_data = now();
            $pinjaman->save();

            Log::info('Pinjaman berhasil diupdate', ['pinjaman_id' => $id]);

            return back()->with('success', 'Data pinjaman berhasil diupdate');

        } catch (\Exception $e) {
            Log::error('Gagal update pinjaman', [
                'pinjaman_id' => $id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Gagal update pinjaman: ' . $e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        try {
            $pinjaman = TblPinjamanH::findOrFail($id);
            
            // Cek apakah sudah ada pembayaran angsuran
            if ($pinjaman->detail_angsuran()->count() > 0) {
                return back()->with('error', 'Pinjaman tidak dapat dihapus karena sudah ada pembayaran angsuran');
            }

            $pinjaman->delete();

            Log::info('Pinjaman berhasil dihapus', ['pinjaman_id' => $id]);

            return back()->with('success', 'Pinjaman berhasil dihapus');

        } catch (\Exception $e) {
            Log::error('Gagal hapus pinjaman', [
                'pinjaman_id' => $id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Gagal hapus pinjaman: ' . $e->getMessage());
        }
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
}