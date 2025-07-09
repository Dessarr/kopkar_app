<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\data_mobil;
use App\Models\TblTransAngkutan;
use App\Models\NamaKasTbl;
use App\Models\jns_akun;
use Illuminate\Support\Facades\Auth;

class AngkutanController extends Controller
{
    public function pemasukan()
    {
        $mobil = data_mobil::where('aktif', 'Y')->get();
        $kas = NamaKasTbl::all();
        $akun = jns_akun::where('aktif', 'Y')->get();
        return view('angkutan.pemasukan', compact('mobil', 'kas', 'akun'));
    }

    public function pengeluaran()
    {
        $mobil = data_mobil::where('aktif', 'Y')->get();
        $kas = NamaKasTbl::all();
        $akun = jns_akun::where('aktif', 'Y')->get();
        return view('angkutan.pengeluaran', compact('mobil', 'kas', 'akun'));
    }

    public function storePemasukan(Request $request)
    {
        $request->validate([
            'id_mobil' => 'required|exists:tbl_mobil,id',
            'tgl_catat' => 'required|date',
            'keterangan' => 'required|string',
            'jumlah' => 'required|numeric|min:0',
            'akun' => 'required|exists:jns_akun,id',
            'dari_kas_id' => 'required|exists:nama_kas_tbl,id',
            'untuk_kas_id' => 'required|exists:nama_kas_tbl,id'
        ]);

        $transaksi = new TblTransAngkutan();
        $transaksi->id_mobil = $request->id_mobil;
        $transaksi->tgl_catat = $request->tgl_catat;
        $transaksi->keterangan = $request->keterangan;
        $transaksi->jumlah = $request->jumlah;
        $transaksi->akun = $request->akun;
        $transaksi->dari_kas_id = $request->dari_kas_id;
        $transaksi->untuk_kas_id = $request->untuk_kas_id;
        $transaksi->jns_trans = 'Pemasukan Angkutan';
        $transaksi->dk = 'D'; // Debit untuk pemasukan
        $transaksi->update_data = now();
        $transaksi->user_name = Auth::user()->name;
        $transaksi->id_cabang = 1; // Sesuaikan dengan sistem cabang yang ada
        $transaksi->save();

        return redirect()->back()->with('success', 'Pemasukan angkutan berhasil disimpan');
    }

    public function storePengeluaran(Request $request)
    {
        $request->validate([
            'id_mobil' => 'required|exists:tbl_mobil,id',
            'tgl_catat' => 'required|date',
            'keterangan' => 'required|string',
            'jumlah' => 'required|numeric|min:0',
            'akun' => 'required|exists:jns_akun,id',
            'dari_kas_id' => 'required|exists:nama_kas_tbl,id',
            'untuk_kas_id' => 'required|exists:nama_kas_tbl,id'
        ]);

        $transaksi = new TblTransAngkutan();
        $transaksi->id_mobil = $request->id_mobil;
        $transaksi->tgl_catat = $request->tgl_catat;
        $transaksi->keterangan = $request->keterangan;
        $transaksi->jumlah = $request->jumlah;
        $transaksi->akun = $request->akun;
        $transaksi->dari_kas_id = $request->dari_kas_id;
        $transaksi->untuk_kas_id = $request->untuk_kas_id;
        $transaksi->jns_trans = 'Pengeluaran Angkutan';
        $transaksi->dk = 'K'; // Kredit untuk pengeluaran
        $transaksi->update_data = now();
        $transaksi->user_name = Auth::user()->name;
        $transaksi->id_cabang = 1; // Sesuaikan dengan sistem cabang yang ada
        $transaksi->save();

        return redirect()->back()->with('success', 'Pengeluaran angkutan berhasil disimpan');
    }

    public function getTransaksi(Request $request)
    {
        $transaksi = TblTransAngkutan::with(['mobil', 'dari_kas', 'untuk_kas', 'jenis_transaksi'])
            ->where('id_mobil', $request->id_mobil)
            ->orderBy('tgl_catat', 'desc')
            ->get();

        return response()->json($transaksi);
    }
} 