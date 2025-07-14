<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TransaksiSimpanan;
use App\Models\data_anggota;
use App\Models\jns_simpan;
use App\Models\DataKas;
use App\Models\TblTransSpTemp;
use App\Models\TblTransTagihan;
use App\Imports\SetoranImport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class SimpananController extends Controller
{
    public function setoranTunai()
    {
            $dataAnggota = data_anggota::where('aktif', 'Y')->paginate(10);
            $jenisSimpanan = jns_simpan::all();
            $dataKas = DataKas::all();
            $transaksiSetoran = TransaksiSimpanan::where('akun', 'setoran')
                ->orderBy('update_data', 'desc')
                ->paginate(10);
            
            return view('simpanan.setoran_tunai', compact('dataAnggota', 'jenisSimpanan', 'dataKas', 'transaksiSetoran'));
        
    }

    public function penarikanTunai()
    {

            $dataAnggota = data_anggota::where('aktif', 'Y')->paginate(10);
            $jenisSimpanan = jns_simpan::all();
            $dataKas = DataKas::all();
            $transaksiPenarikan = TransaksiSimpanan::where('akun', 'penarikan')
                ->orderBy('update_data', 'desc')
                ->paginate(10);
            
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
            'kas_id' => 'required|exists:data_kas,id',
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
                'keterangan' => $request->keterangan ?? 'Setoran Tunai',
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

            DB::commit();
            return redirect()->back()->with('success', 'Setoran tunai berhasil disimpan');
        
    }

    public function storePenarikan(Request $request)
    {
        try {
            DB::beginTransaction();

        $request->validate([
            'tgl_transaksi' => 'required|date',
            'no_ktp' => 'required|string',
            'anggota_id' => 'required|exists:tbl_anggota,id',
            'jenis_id' => 'required|exists:jns_simpan,id',
            'jumlah' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
            'akun' => 'required|string',
            'dk' => 'required|string',
            'kas_id' => 'required|exists:data_kas,id',
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
                'keterangan' => $request->keterangan ?? 'Penarikan Tunai',
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

            DB::commit();
            return redirect()->back()->with('success', 'Penarikan tunai berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function setoranUpload()
    {
        try {
            $dataAnggota = data_anggota::where('aktif', 'Y')->paginate(10);
            $jenisSimpanan = jns_simpan::all();
            $dataKas = DataKas::all();
            $transaksiUpload = TblTransSpTemp::orderBy('created_at', 'desc')->paginate(10);
            
            return view('simpanan.setoran_upload', compact('dataAnggota', 'jenisSimpanan', 'dataKas', 'transaksiUpload'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function uploadSetoran(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:2048',
            'jenis_id' => 'required|exists:jns_simpan,id',
            'kas_id' => 'required|exists:nama_kas_tbl,id',
            'tgl_transaksi' => 'required|date'
        ]);

        try {
            DB::beginTransaction();

            $file = $request->file('file');
            $rows = Excel::toArray(new SetoranImport, $file)[0];

            // Remove header row
            array_shift($rows);

            foreach ($rows as $row) {
                if (!isset($row[0]) || !isset($row[1])) {
                    continue; // Skip invalid rows
                }

                $anggota = data_anggota::where('no_ktp', $row[0])->where('aktif', 'Y')->first();
                
                if ($anggota) {
                    TblTransSpTemp::create([
                        'tgl_transaksi' => $request->tgl_transaksi,
                        'no_ktp' => $row[0],
                        'anggota_id' => $anggota->id,
                        'jenis_id' => $request->jenis_id,
                        'jumlah' => $row[1],
                        'keterangan' => $row[2] ?? 'Setoran Upload',
                        'akun' => 'setoran',
                        'dk' => 'D',
                        'kas_id' => $request->kas_id,
                        'user_name' => Auth::user()->name ?? 'admin',
                        'id_cabang' => $anggota->id_cabang
                    ]);
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'File berhasil diupload');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function prosesSetoran(Request $request)
    {
        try {
            DB::beginTransaction();

            $tempSetorans = TblTransSpTemp::all();

            if ($tempSetorans->isEmpty()) {
                return redirect()->back()->with('error', 'Tidak ada data setoran untuk diproses');
            }

            foreach ($tempSetorans as $setoran) {
                TransaksiSimpanan::create([
                    'tgl_transaksi' => $setoran->tgl_transaksi,
                    'no_ktp' => $setoran->no_ktp,
                    'anggota_id' => $setoran->anggota_id,
                    'jenis_id' => $setoran->jenis_id,
                    'jumlah' => $setoran->jumlah,
                    'keterangan' => $setoran->keterangan,
                    'akun' => $setoran->akun,
                    'dk' => $setoran->dk,
                    'kas_id' => $setoran->kas_id,
                    'update_data' => now(),
                    'user_name' => Auth::user()->name ?? 'admin',
                    'id_cabang' => $setoran->id_cabang
                ]);
            }

            // Clear temporary data
            TblTransSpTemp::truncate();

            DB::commit();
            return redirect()->back()->with('success', 'Setoran berhasil diproses');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function tagihan()
    {
        try {
            $dataAnggota = data_anggota::where('aktif', 'Y')->paginate(10);
            $jenisSimpanan = jns_simpan::all();
            $tagihan = TblTransTagihan::with(['anggota', 'jenisSimpanan'])
                ->orderBy('tgl_transaksi', 'desc')
                ->paginate(10);
            
            return view('simpanan.tagihan', compact('dataAnggota', 'jenisSimpanan', 'tagihan'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function storeTagihan(Request $request)
    {
        try {
            $request->validate([
                'tgl_transaksi' => 'required|date',
                'anggota_id' => 'required|exists:tbl_anggota,id',
                'jenis_id' => 'required|exists:jns_simpan,id',
                'jumlah' => 'required|numeric|min:0',
                'keterangan' => 'nullable|string',
                'jatuh_tempo' => 'required|date|after:tgl_transaksi'
            ]);

            DB::beginTransaction();

            $anggota = data_anggota::findOrFail($request->anggota_id);

            TblTransTagihan::create([
                'tgl_transaksi' => $request->tgl_transaksi,
                'anggota_id' => $request->anggota_id,
                'jenis_id' => $request->jenis_id,
                'jumlah' => $request->jumlah,
                'keterangan' => $request->keterangan ?? 'Tagihan Simpanan',
                'jatuh_tempo' => $request->jatuh_tempo,
                'status' => 'belum_bayar',
                'user_name' => Auth::user()->name ?? 'admin',
                'id_cabang' => $anggota->id_cabang
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Tagihan berhasil dibuat');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function getAnggotaByKtp($noKtp)
    {
        try {
            $anggota = data_anggota::where('no_ktp', $noKtp)
                ->where('aktif', 'Y')
                ->first();
            
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
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    public function pengajuanPenarikan()
    {
        // Contoh: ambil data pengajuan penarikan dari model terkait (misal: data_pengajuan_penarikan)
        $dataPengajuan = \App\Models\data_pengajuan_penarikan::paginate(10);
        return view('simpanan.pengajuan_penarikan', compact('dataPengajuan'));
    }
}