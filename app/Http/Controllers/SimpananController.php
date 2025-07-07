<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TransaksiSimpanan;
use App\Models\data_anggota;
use App\Models\jns_simpan;
use App\Models\DataKas;
use Illuminate\Support\Facades\Auth;

class SimpananController extends Controller
{
    public function setoranTunai()
    {
        $dataAnggota = data_anggota::paginate(10);
        $jenisSimpanan = jns_simpan::all();
        $dataKas = DataKas::all();
        $transaksiSetoran = TransaksiSimpanan::where('akun', 'setoran')->orderBy('update_data', 'desc')->paginate(10);
        
        return view('simpanan.setoran_tunai', compact('dataAnggota', 'jenisSimpanan', 'dataKas', 'transaksiSetoran'));
    }

    public function penarikanTunai()
    {
        $dataAnggota = data_anggota::paginate(10);
        $jenisSimpanan = jns_simpan::all();
        $dataKas = DataKas::all();
        $transaksiPenarikan = TransaksiSimpanan::where('akun', 'penarikan')->orderBy('update_data', 'desc')->paginate(10);
        
        return view('simpanan.penarikan_tunai', compact('dataAnggota', 'jenisSimpanan', 'dataKas', 'transaksiPenarikan'));
    }

    public function storeSetoran(Request $request)
    {
        $request->validate([
            'tgl_transaksi' => 'required|date',
            'no_ktp' => 'required|string',
            'anggota_id' => 'required|exists:tbl_anggota,id',
            'jenis_id' => 'required|exists:jns_simpan,id',
            'jumlah' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
            'akun' => 'required|string',
            'dk' => 'required|string',
            'kas_id' => 'required|exists:DataKas,id',
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
            'keterangan' => $request->keterangan,
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

        return redirect()->back()->with('success', 'Setoran tunai berhasil disimpan');
    }

    public function storePenarikan(Request $request)
    {
        $request->validate([
            'tgl_transaksi' => 'required|date',
            'no_ktp' => 'required|string',
            'anggota_id' => 'required|exists:tbl_anggota,id',
            'jenis_id' => 'required|exists:jns_simpan,id',
            'jumlah' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
            'akun' => 'required|string',
            'dk' => 'required|string',
            'kas_id' => 'required|exists:DataKas,id',
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
            'keterangan' => $request->keterangan,
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

        return redirect()->back()->with('success', 'Penarikan tunai berhasil disimpan');
    }

    public function getAnggotaByKtp($noKtp)
    {
        $anggota = data_anggota::where('no_ktp', $noKtp)->first();
        
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
    }
}