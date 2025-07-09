<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TblTransToserda;
use App\Models\data_barang;
use App\Models\data_anggota;
use App\Models\DataKas;
use Illuminate\Support\Facades\Auth;

class ToserdaController extends Controller
{
    public function penjualan()
    {
        $barang = data_barang::all();
        $anggota = data_anggota::all();
        $kas = DataKas::all();
        return view('toserda.penjualan', compact('barang', 'anggota', 'kas'));
    }

    public function pembelian()
    {
        $barang = data_barang::all();
        $kas = DataKas::all();
        return view('toserda.pembelian', compact('barang', 'kas'));
    }

    public function biayaUsaha()
    {
        $kas = DataKas::all();
        return view('toserda.biaya_usaha', compact('kas'));
    }

    public function storePenjualan(Request $request)
    {
        $request->validate([
            'barang_id' => 'required|exists:data_barang,id',
            'anggota_id' => 'required|exists:data_anggota,id',
            'jumlah' => 'required|numeric|min:1',
            'kas_id' => 'required|exists:nama_kas_tbl,id',
            'keterangan' => 'nullable|string'
        ]);

        // Get anggota data
        $anggota = data_anggota::find($request->anggota_id);
        
        $transaksi = new TblTransToserda();
        $transaksi->tgl_transaksi = now();
        $transaksi->no_ktp = $anggota->no_ktp;
        $transaksi->anggota_id = $request->anggota_id;
        $transaksi->jenis_id = $request->barang_id; // using barang_id as jenis_id
        $transaksi->jumlah = $request->jumlah;
        $transaksi->keterangan = $request->keterangan ?? 'Penjualan Toserda';
        $transaksi->dk = 'D'; // Debit untuk penjualan
        $transaksi->kas_id = $request->kas_id;
        $transaksi->user_name = Auth::user()->name;
        $transaksi->save();

        // Update stok barang
        $barang = data_barang::find($request->barang_id);
        $barang->stok -= $request->jumlah;
        $barang->save();

        return redirect()->back()->with('success', 'Transaksi penjualan berhasil disimpan');
    }

    public function storePembelian(Request $request)
    {
        $request->validate([
            'barang_id' => 'required|exists:data_barang,id',
            'jumlah' => 'required|numeric|min:1',
            'kas_id' => 'required|exists:nama_kas_tbl,id',
            'keterangan' => 'nullable|string'
        ]);

        $transaksi = new TblTransToserda();
        $transaksi->tgl_transaksi = now();
        $transaksi->jenis_id = $request->barang_id;
        $transaksi->jumlah = $request->jumlah;
        $transaksi->keterangan = $request->keterangan ?? 'Pembelian Toserda';
        $transaksi->dk = 'K'; // Kredit untuk pembelian
        $transaksi->kas_id = $request->kas_id;
        $transaksi->user_name = Auth::user()->name;
        $transaksi->save();

        // Update stok barang
        $barang = data_barang::find($request->barang_id);
        $barang->stok += $request->jumlah;
        $barang->save();

        return redirect()->back()->with('success', 'Transaksi pembelian berhasil disimpan');
    }

    public function storeBiayaUsaha(Request $request)
    {
        $request->validate([
            'keterangan' => 'required|string',
            'jumlah' => 'required|numeric|min:0',
            'kas_id' => 'required|exists:nama_kas_tbl,id'
        ]);

        $transaksi = new TblTransToserda();
        $transaksi->tgl_transaksi = now();
        $transaksi->jumlah = $request->jumlah;
        $transaksi->keterangan = $request->keterangan;
        $transaksi->dk = 'K'; // Kredit untuk biaya
        $transaksi->kas_id = $request->kas_id;
        $transaksi->user_name = Auth::user()->name;
        $transaksi->save();

        return redirect()->back()->with('success', 'Biaya usaha berhasil disimpan');
    }
} 