<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\transaksi_kas;
use App\Models\View_Transaksi;
use App\Models\DataKas;
use App\Models\NamaKasTbl;
use App\Models\jns_akun;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;


class TransaksiKasController extends Controller
{
    /**
     * Menampilkan halaman pemasukan kas dengan sistem filter lengkap
     */
    public function pemasukan(Request $request)
    {
        // Ambil semua jenis akun yang bisa untuk pemasukan
        $akunPemasukanIds = jns_akun::where('aktif', 'Y')
            ->where('pemasukan', 'Y')
            ->pluck('id')
            ->toArray();
            
        $query = View_Transaksi::where('tbl', 'D')
            ->whereIn('transaksi', $akunPemasukanIds) // Semua jenis akun untuk pemasukan
            ->with(['kasTujuan', 'jenisAkun']);

        // Filter berdasarkan request
        $query = $this->applyFilters($query, $request);

        $dataKas = $query->orderBy('tgl', 'desc')->paginate(15);

        // Data untuk dropdowns
        $kasOptions = NamaKasTbl::where('aktif', 'Y')
            ->where('tmpl_pemasukan', 'Y')
            ->get();
        $akunOptions = jns_akun::where('aktif', 'Y')
            ->where('pemasukan', 'Y')
            ->get();
        $users = View_Transaksi::select('user')->distinct()->whereNotNull('user')->pluck('user');

        // Statistik
        $totalPemasukan = $query->sum('debet');
        $totalRecords = $query->count();

        return view('transaksi_kas.pemasukan', compact(
            'dataKas', 
            'kasOptions', 
            'users', 
            'akunOptions',
            'totalPemasukan',
            'totalRecords'
        ));
    }

    /**
     * Menampilkan halaman pengeluaran kas dengan sistem filter lengkap
     */
    public function pengeluaran(Request $request)
    {
        // Ambil semua jenis akun yang bisa untuk pengeluaran
        $akunPengeluaranIds = jns_akun::where('aktif', 'Y')
            ->where('pengeluaran', 'Y')
            ->pluck('id')
            ->toArray();
            
        $query = transaksi_kas::where('akun', 'Pengeluaran')
            ->whereIn('jns_trans', $akunPengeluaranIds)
            ->with(['dariKas', 'jenisAkun']);

        // Filter berdasarkan request
        $query = $this->applyPengeluaranFilters($query, $request);

        $dataKas = $query->orderBy('tgl_catat', 'desc')->paginate(15);

        // Data untuk filter dropdowns
        $listKas = NamaKasTbl::where('aktif', 'Y')
            ->where('tmpl_pengeluaran', 'Y')
            ->get();
        $jenisAkun = jns_akun::where('aktif', 'Y')
            ->where('pengeluaran', 'Y')
            ->get();
        $users = transaksi_kas::select('user_name')->distinct()->whereNotNull('user_name')->pluck('user_name');

        // Statistik
        $totalPengeluaran = $query->sum('jumlah');
        $totalRecords = $query->count();

        return view('transaksi_kas.pengeluaran', compact(
            'dataKas', 
            'listKas', 
            'users', 
            'jenisAkun',
            'totalPengeluaran',
            'totalRecords'
        ));
    }

    /**
     * Menampilkan halaman transfer kas dengan sistem filter lengkap
     */
    public function transfer(Request $request)
    {
        $query = transaksi_kas::where('akun', 'Transfer')
            ->with(['dariKas', 'untukKas']);

        // Filter berdasarkan request untuk transfer
        $query = $this->applyTransferFilters($query, $request);

        $dataKas = $query->orderBy('tgl_catat', 'desc')->paginate(15);

        // Data untuk filter dropdowns
        $listKas = NamaKasTbl::where('aktif', 'Y')
            ->where('tmpl_transfer', 'Y')
            ->get();
        $users = transaksi_kas::select('user_name')->distinct()->whereNotNull('user_name')->pluck('user_name');

        // Statistik
        $totalTransfer = $query->sum('jumlah');
        $totalRecords = $query->count();

        return view('transaksi_kas.transfer', compact(
            'dataKas', 
            'listKas', 
            'users',
            'totalTransfer',
            'totalRecords'
        ));
    }

    /**
     * Menyimpan transaksi pemasukan kas baru
     */
    public function storePemasukan(Request $request)
    {
        $request->validate([
            'tgl_catat' => 'required|date',
            'jumlah' => 'required|numeric|min:0',
            'keterangan' => 'required|string|max:255',
            'akun' => 'required|exists:jns_akun,id',
            'untuk_kas_id' => 'required|exists:nama_kas_tbl,id'
        ]);

        try {
            DB::beginTransaction();

            $transaksi = new transaksi_kas();
            $transaksi->tgl_catat = $request->tgl_catat;
            $transaksi->jumlah = $request->jumlah;
            $transaksi->keterangan = $request->keterangan;
            $transaksi->akun = 'Pemasukan'; // Fixed value untuk pemasukan
            $transaksi->untuk_kas_id = $request->untuk_kas_id;
            $transaksi->jns_trans = $request->akun; // ID jenis akun dari dropdown
            $transaksi->dari_kas_id = null; // NULL untuk pemasukan
            $transaksi->no_polisi = ''; // Kosong untuk pemasukan kas
            $transaksi->dk = 'D'; // Debit untuk pemasukan
            $transaksi->update_data = now();
            $transaksi->user_name = auth('admin')->user()->name ?? 'admin';
            $transaksi->id_cabang = '1'; // Sesuaikan dengan sistem cabang yang ada
            $transaksi->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pemasukan kas berhasil disimpan',
                'data' => $transaksi
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan pemasukan kas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menyimpan transaksi pengeluaran kas baru
     */
    public function storePengeluaran(Request $request)
    {
        $request->validate([
            'tgl_catat' => 'required|date',
            'jumlah' => 'required|numeric|min:0',
            'keterangan' => 'required|string|max:255',
            'akun' => 'required|exists:jns_akun,id',
            'dari_kas_id' => 'required|exists:nama_kas_tbl,id'
        ]);

        try {
            DB::beginTransaction();

            $transaksi = new transaksi_kas();
            $transaksi->tgl_catat = $request->tgl_catat;
            $transaksi->jumlah = $request->jumlah;
            $transaksi->keterangan = $request->keterangan;
            $transaksi->akun = 'Pengeluaran'; // Enum value untuk pengeluaran
            $transaksi->dari_kas_id = $request->dari_kas_id;
            $transaksi->jns_trans = $request->akun; // ID dari jns_akun
            $transaksi->no_polisi = ''; // Required field
            $transaksi->dk = 'K'; // Kredit untuk pengeluaran
            $transaksi->update_data = now();
            $transaksi->user_name = auth('admin')->user()->name ?? 'admin';
            $transaksi->id_cabang = '1'; // String value
            $transaksi->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pengeluaran kas berhasil disimpan',
                'data' => $transaksi
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan pengeluaran kas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update transaksi pengeluaran kas
     */
    public function updatePengeluaran(Request $request, $id)
    {
        try {
            $request->validate([
                'tgl_catat' => 'required|date',
                'jumlah' => 'required|numeric|min:1',
                'keterangan' => 'required|string',
                'dari_kas_id' => 'required|exists:nama_kas_tbl,id',
                'akun' => 'required|exists:jns_akun,id'
            ]);

            $transaksi = transaksi_kas::findOrFail($id);
            $transaksi->update([
                'tgl_catat' => $request->tgl_catat,
                'jumlah' => $request->jumlah,
                'keterangan' => $request->keterangan,
                'dari_kas_id' => $request->dari_kas_id,
                'jns_trans' => $request->akun, // ID dari jns_akun
                'akun' => 'Pengeluaran', // Enum value untuk pengeluaran
                'user_name' => auth('admin')->user()->name ?? 'admin'
            ]);

            return response()->json(['success' => true, 'message' => 'Data berhasil diupdate']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal mengupdate data: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete transaksi pengeluaran kas
     */
    public function destroyPengeluaran($id)
    {
        try {
            $transaksi = transaksi_kas::findOrFail($id);
            $transaksi->delete();
            return response()->json(['success' => true, 'message' => 'Data berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menghapus data: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update transaksi transfer kas
     */
    public function updateTransfer(Request $request, $id)
    {
        try {
            $request->validate([
                'tgl_catat' => 'required|date',
                'jumlah' => 'required|numeric|min:1',
                'keterangan' => 'required|string',
                'dari_kas_id' => 'required|exists:nama_kas_tbl,id',
                'untuk_kas_id' => 'required|exists:nama_kas_tbl,id|different:dari_kas_id'
            ]);

            $transaksi = transaksi_kas::findOrFail($id);
            $transaksi->update([
                'tgl_catat' => $request->tgl_catat,
                'jumlah' => $request->jumlah,
                'keterangan' => $request->keterangan,
                'dari_kas_id' => $request->dari_kas_id,
                'untuk_kas_id' => $request->untuk_kas_id,
                'akun' => 'Transfer', // Enum value untuk transfer
                'user_name' => auth('admin')->user()->name ?? 'admin'
            ]);

            return response()->json(['success' => true, 'message' => 'Data berhasil diupdate']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal mengupdate data: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete transaksi transfer kas
     */
    public function destroyTransfer($id)
    {
        try {
            $transaksi = transaksi_kas::findOrFail($id);
            $transaksi->delete();
            return response()->json(['success' => true, 'message' => 'Data berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menghapus data: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Menyimpan transaksi transfer kas baru
     */
    public function storeTransfer(Request $request)
    {
        $request->validate([
            'tgl_catat' => 'required|date',
            'jumlah' => 'required|numeric|min:0',
            'keterangan' => 'required|string|max:255',
            'dari_kas_id' => 'required|exists:nama_kas_tbl,id',
            'untuk_kas_id' => 'required|exists:nama_kas_tbl,id|different:dari_kas_id'
        ]);

        try {
            DB::beginTransaction();

            $transaksi = new transaksi_kas();
            $transaksi->tgl_catat = $request->tgl_catat;
            $transaksi->jumlah = $request->jumlah;
            $transaksi->keterangan = $request->keterangan;
            $transaksi->akun = 'Transfer'; // Enum value untuk transfer
            $transaksi->dari_kas_id = $request->dari_kas_id;
            $transaksi->untuk_kas_id = $request->untuk_kas_id;
            $transaksi->jns_trans = null; // Transfer tidak memerlukan jns_trans
            $transaksi->no_polisi = ''; // Required field
            $transaksi->dk = null; // Transfer tidak memerlukan dk
            $transaksi->update_data = now();
            $transaksi->user_name = auth('admin')->user()->name ?? 'admin';
            $transaksi->id_cabang = '1'; // String value
            $transaksi->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transfer kas berhasil disimpan',
                'data' => $transaksi
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan transfer kas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menampilkan detail transaksi
     */
    public function show($id)
    {
        $transaksi = transaksi_kas::with(['dariKas', 'untukKas'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $transaksi
        ]);
    }

    /**
     * Mengupdate transaksi
     */
    public function update(Request $request, $id)
    {
        $transaksi = transaksi_kas::findOrFail($id);
        
        $request->validate([
            'tgl_catat' => 'required|date',
            'jumlah' => 'required|numeric|min:0',
            'keterangan' => 'required|string|max:255'
        ]);

        try {
            $transaksi->update([
                'tgl_catat' => $request->tgl_catat,
                'jumlah' => $request->jumlah,
                'keterangan' => $request->keterangan,
                'update_data' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil diupdate',
                'data' => $transaksi
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate transaksi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menghapus transaksi
     */
    public function destroy($id)
    {
        try {
            $transaksi = transaksi_kas::findOrFail($id);
            $transaksi->delete();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus transaksi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Apply filters to pengeluaran query (transaksi_kas model)
     */
    private function applyPengeluaranFilters($query, $request)
    {
        // PRIORITAS 1: Kode Transaksi (Jika diisi, abaikan filter tanggal)
        if ($request->filled('kode_transaksi')) {
            $kodeTransaksi = trim($request->kode_transaksi);
            
            // Bersihkan prefix TKK dan leading zeros
            $kodeTransaksi = str_replace(['TKK', 'tkk'], '', $kodeTransaksi);
            $kodeTransaksi = ltrim($kodeTransaksi, '0');
            $kodeTransaksi = (int)$kodeTransaksi;
            
            // Cari berdasarkan ID transaksi
            $query->where('id', 'LIKE', $kodeTransaksi);
            
            return $query;
        }

        // PRIORITAS 2: Filter Tanggal (Hanya jika kode transaksi kosong)
        if ($request->filled('tgl_dari') && $request->filled('tgl_sampai')) {
            $query->whereDate('tgl_catat', '>=', $request->tgl_dari)
                  ->whereDate('tgl_catat', '<=', $request->tgl_sampai);
        }

        return $query;
    }

    /**
     * Filter untuk pemasukan dan pengeluaran kas dengan prioritas yang efisien
     * Mengikuti logika: Kode Transaksi > Date Range > Filter Lainnya
     */
    private function applyFilters($query, $request)
    {
        // PRIORITAS 1: Kode Transaksi (Jika diisi, abaikan filter tanggal)
        if ($request->filled('kode_transaksi')) {
            $kodeTransaksi = trim($request->kode_transaksi);
            
            // Bersihkan prefix TKD dan leading zeros
            $kodeTransaksi = str_replace(['TKD', 'tkd'], '', $kodeTransaksi);
            $kodeTransaksi = ltrim($kodeTransaksi, '0');
            $kodeTransaksi = (int)$kodeTransaksi;
            
            // Cari berdasarkan ID transaksi menggunakan LIKE untuk fleksibilitas
            $query->where('id', 'LIKE', $kodeTransaksi);
            
            // Return langsung, abaikan filter lainnya kecuali user dan kas untuk keamanan
            if ($request->filled('user_filter')) {
                $userArray = is_array($request->user_filter) ? $request->user_filter : [$request->user_filter];
                $query->whereIn('user', $userArray);
            }
            
            return $query;
        }

        // PRIORITAS 2: Filter Tanggal (Hanya jika kode transaksi kosong)
        if ($request->filled('tgl_dari') && $request->filled('tgl_sampai')) {
            $query->whereDate('tgl', '>=', $request->tgl_dari)
                  ->whereDate('tgl', '<=', $request->tgl_sampai);
        } else {
            // Fallback ke date_from/date_to untuk kompatibilitas
        if ($request->filled('date_from')) {
            $query->whereDate('tgl', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('tgl', '<=', $request->date_to);
            }
        }

        // PRIORITAS 3: Filter Periode Bulan (21-20)
        if ($request->filled('periode_bulan')) {
            $periode = $request->periode_bulan;
            $tglDari = date('Y-m-21', strtotime($periode . '-01 -1 month'));
            $tglSampai = $periode . '-20';
            $query->whereDate('tgl', '>=', $tglDari)
                  ->whereDate('tgl', '<=', $tglSampai);
        }

        // PRIORITAS 4: Filter Pencarian Umum
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('keterangan', 'like', "%{$search}%")
                  ->orWhere('nama_kas', 'like', "%{$search}%")
                  ->orWhere('user', 'like', "%{$search}%");
            });
        }

        // PRIORITAS 5: Filter Nominal Range - disesuaikan berdasarkan jenis transaksi
        if ($request->filled('nominal_min')) {
            if ($query->getModel() instanceof View_Transaksi) {
                $transaksiType = $query->getQuery()->wheres[0]['value'] ?? null;
                if ($transaksiType === '48') { // Pemasukan
                    $query->where('debet', '>=', $request->nominal_min);
                } else { // Pengeluaran
                    $query->where('kredit', '>=', $request->nominal_min);
                }
            }
        }
        if ($request->filled('nominal_max')) {
            if ($query->getModel() instanceof View_Transaksi) {
                $transaksiType = $query->getQuery()->wheres[0]['value'] ?? null;
                if ($transaksiType === '48') { // Pemasukan
                    $query->where('debet', '<=', $request->nominal_max);
                } else { // Pengeluaran
                    $query->where('kredit', '<=', $request->nominal_max);
                }
            }
        }

        // PRIORITAS 6: Filter Kas (Single Selection) - disesuaikan berdasarkan jenis transaksi
        if ($request->filled('kas_filter')) {
            if ($query->getModel() instanceof View_Transaksi) {
                $transaksiType = $query->getQuery()->wheres[0]['value'] ?? null;
                if ($transaksiType === '48') { // Pemasukan
                    $query->where('untuk_kas', $request->kas_filter);
                } else { // Pengeluaran
                    $query->where('dari_kas', $request->kas_filter);
                }
            }
        }

        // PRIORITAS 7: Filter User (Single Selection)
        if ($request->filled('user_filter')) {
            $query->where('user', $request->user_filter);
        }

        return $query;
    }

    /**
     * Filter untuk transfer kas dengan prioritas yang efisien
     * Mengikuti logika: Kode Transaksi > Date Range > Filter Lainnya
     */
    private function applyTransferFilters($query, $request)
    {
        // PRIORITAS 1: Kode Transaksi (Jika diisi, abaikan filter tanggal)
        if ($request->filled('kode_transaksi')) {
            $kodeTransaksi = trim($request->kode_transaksi);
            
            // Bersihkan prefix TRF dan leading zeros
            $kodeTransaksi = str_replace(['TRF', 'trf'], '', $kodeTransaksi);
            $kodeTransaksi = ltrim($kodeTransaksi, '0');
            $kodeTransaksi = (int)$kodeTransaksi;
            
            // Cari berdasarkan ID transaksi menggunakan LIKE untuk fleksibilitas
            $query->where('id', 'LIKE', $kodeTransaksi);
            
            // Return langsung, abaikan filter lainnya kecuali user untuk keamanan
            if ($request->filled('user_filter')) {
                $userArray = is_array($request->user_filter) ? $request->user_filter : [$request->user_filter];
                $query->whereIn('user_name', $userArray);
            }
            
            return $query;
        }

        // PRIORITAS 2: Filter Tanggal (Hanya jika kode transaksi kosong)
        if ($request->filled('tgl_dari') && $request->filled('tgl_sampai')) {
            $query->whereDate('tgl_catat', '>=', $request->tgl_dari)
                  ->whereDate('tgl_catat', '<=', $request->tgl_sampai);
        } else {
            // Fallback ke date_from/date_to untuk kompatibilitas
        if ($request->filled('date_from')) {
            $query->whereDate('tgl_catat', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('tgl_catat', '<=', $request->date_to);
            }
        }

        // PRIORITAS 3: Filter Periode Bulan (21-20)
        if ($request->filled('periode_bulan')) {
            $periode = $request->periode_bulan;
            $tglDari = date('Y-m-21', strtotime($periode . '-01 -1 month'));
            $tglSampai = $periode . '-20';
            $query->whereDate('tgl_catat', '>=', $tglDari)
                  ->whereDate('tgl_catat', '<=', $tglSampai);
        }

        // PRIORITAS 4: Filter Pencarian Umum
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('keterangan', 'like', "%{$search}%")
                  ->orWhere('user_name', 'like', "%{$search}%");
            });
        }

        // PRIORITAS 5: Filter Nominal Range
        if ($request->filled('nominal_min')) {
            $query->where('jumlah', '>=', $request->nominal_min);
        }
        if ($request->filled('nominal_max')) {
            $query->where('jumlah', '<=', $request->nominal_max);
        }

        // PRIORITAS 6: Filter Kas Asal (Single Selection)
        if ($request->filled('kas_asal_filter')) {
            $query->where('dari_kas_id', $request->kas_asal_filter);
        }

        // PRIORITAS 7: Filter Kas Tujuan (Single Selection)
        if ($request->filled('kas_tujuan_filter')) {
            $query->where('untuk_kas_id', $request->kas_tujuan_filter);
        }

        // PRIORITAS 8: Filter User (Single Selection)
        if ($request->filled('user_filter')) {
            $query->where('user_name', $request->user_filter);
        }

        return $query;
    }





    /**
     * Update data pemasukan kas
     */
    public function updatePemasukan(Request $request, $id)
    {
        try {
            $request->validate([
                'tgl_catat' => 'required|date',
                'jumlah' => 'required|numeric|min:1',
                'keterangan' => 'required|string',
                'untuk_kas_id' => 'required|exists:nama_kas_tbl,id',
                'akun' => 'required|exists:jns_akun,id'
            ]);

            $transaksi = transaksi_kas::findOrFail($id);
            
            $transaksi->update([
                'tgl_catat' => $request->tgl_catat,
                'jumlah' => $request->jumlah,
                'keterangan' => $request->keterangan,
                'untuk_kas_id' => $request->untuk_kas_id,
                'jns_trans' => $request->akun, // ID dari jns_akun
                'akun' => 'Pemasukan', // Enum value untuk pemasukan
                'user_name' => auth('admin')->user()->name ?? 'admin'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diupdate'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete data pemasukan kas
     */
    public function destroyPemasukan($id)
    {
        try {
            $transaksi = transaksi_kas::findOrFail($id);
            $transaksi->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export data pemasukan kas ke PDF
     */
    public function exportPemasukanPdf(Request $request)
    {
        $query = View_Transaksi::where('transaksi', '48')
            ->with('kasTujuan');

        $query = $this->applyFilters($query, $request);
        $dataKas = $query->orderBy('tgl', 'desc')->get();

        $periode = $request->filled('periode_bulan') ? $request->periode_bulan : date('Y-m');
        $totalPemasukan = $dataKas->sum('debet');

        $pdf = PDF::loadView('transaksi_kas.pdf.pemasukan', compact('dataKas', 'periode', 'totalPemasukan'));
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('laporan_pemasukan_kas_' . date('Ymd') . '.pdf');
    }

    /**
     * Export data pengeluaran kas ke PDF
     */
    public function exportPengeluaranPdf(Request $request)
    {
        $query = transaksi_kas::where('akun', 'Pengeluaran')
            ->whereIn('jns_trans', jns_akun::where('aktif', 'Y')->where('pengeluaran', 'Y')->pluck('id')->toArray())
            ->with(['dariKas', 'jenisAkun']);

        $query = $this->applyPengeluaranFilters($query, $request);
        $dataKas = $query->orderBy('tgl_catat', 'desc')->get();

        $periode = $request->filled('periode_bulan') ? $request->periode_bulan : date('Y-m');
        $totalPengeluaran = $dataKas->sum('jumlah');

        $pdf = PDF::loadView('transaksi_kas.pdf.pengeluaran', compact('dataKas', 'periode', 'totalPengeluaran'));
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('laporan_pengeluaran_kas_' . date('Ymd') . '.pdf');
    }

    /**
     * Export data transfer kas ke PDF
     */
    public function exportTransferPdf(Request $request)
    {
        $query = transaksi_kas::where('akun', 'Transfer')
            ->with(['dariKas', 'untukKas']);

        $query = $this->applyTransferFilters($query, $request);
        $dataKas = $query->orderBy('tgl_catat', 'desc')->get();

        $periode = $request->filled('periode_bulan') ? $request->periode_bulan : date('Y-m');
        $totalTransfer = $dataKas->sum('jumlah');

        $pdf = PDF::loadView('transaksi_kas.pdf.transfer', compact('dataKas', 'periode', 'totalTransfer'));
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('laporan_transfer_kas_' . date('Ymd') . '.pdf');
}
}