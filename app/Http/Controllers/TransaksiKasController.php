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
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class TransaksiKasController extends Controller
{
    /**
     * Menampilkan halaman pemasukan kas dengan sistem filter lengkap
     */
    public function pemasukan(Request $request)
    {
        $query = View_Transaksi::where('transaksi', '48')
            ->with('kasTujuan');

        // Filter berdasarkan request
        $query = $this->applyFilters($query, $request);

        $dataKas = $query->orderBy('tgl', 'desc')->paginate(15);

        // Data untuk filter dropdowns
        $listKas = NamaKasTbl::where('aktif', 'Y')
            ->where('tmpl_pemasukan', 'Y')
            ->get();
        $jenisAkun = jns_akun::where('pemasukan', 'Y')->get();
        $users = View_Transaksi::select('user')->distinct()->whereNotNull('user')->pluck('user');

        // Statistik
        $totalPemasukan = $query->sum('debet');
        $totalRecords = $query->count();

        return view('transaksi_kas.pemasukan', compact(
            'dataKas', 
            'listKas', 
            'users', 
            'jenisAkun',
            'totalPemasukan',
            'totalRecords'
        ));
    }

    /**
     * Menampilkan halaman pengeluaran kas dengan sistem filter lengkap
     */
    public function pengeluaran(Request $request)
    {
        $query = View_Transaksi::where('transaksi', '7')
            ->with('kasAsal');

        // Filter berdasarkan request
        $query = $this->applyFilters($query, $request);

        $dataKas = $query->orderBy('tgl', 'desc')->paginate(15);

        // Data untuk filter dropdowns
        $listKas = NamaKasTbl::where('aktif', 'Y')
            ->where('tmpl_pengeluaran', 'Y')
            ->get();
        $jenisAkun = jns_akun::where('pengeluaran', 'Y')->get();
        $users = View_Transaksi::select('user')->distinct()->whereNotNull('user')->pluck('user');

        // Statistik
        $totalPengeluaran = $query->sum('kredit');
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
        $query = transaksi_kas::with(['dariKas', 'untukKas']);

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
            $transaksi->akun = $request->akun;
            $transaksi->untuk_kas_id = $request->untuk_kas_id;
            $transaksi->jns_trans = 'Pemasukan Kas';
            $transaksi->dk = 'D'; // Debit untuk pemasukan
            $transaksi->update_data = now();
            $transaksi->user_name = Auth::user()->name ?? 'admin';
            $transaksi->id_cabang = 1; // Sesuaikan dengan sistem cabang yang ada
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
            $transaksi->akun = $request->akun;
            $transaksi->dari_kas_id = $request->dari_kas_id;
            $transaksi->jns_trans = 'Pengeluaran Kas';
            $transaksi->dk = 'K'; // Kredit untuk pengeluaran
            $transaksi->update_data = now();
            $transaksi->user_name = Auth::user()->name ?? 'admin';
            $transaksi->id_cabang = 1; // Sesuaikan dengan sistem cabang yang ada
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
            $transaksi->dari_kas_id = $request->dari_kas_id;
            $transaksi->untuk_kas_id = $request->untuk_kas_id;
            $transaksi->jns_trans = 'Transfer Kas';
            $transaksi->dk = 'T'; // Transfer
            $transaksi->update_data = now();
            $transaksi->user_name = Auth::user()->name ?? 'admin';
            $transaksi->id_cabang = 1; // Sesuaikan dengan sistem cabang yang ada
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
     * Filter untuk pemasukan dan pengeluaran kas
     */
    private function applyFilters($query, $request)
    {
        // 1. Filter Pencarian
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('keterangan', 'like', "%{$search}%")
                  ->orWhere('nama_kas', 'like', "%{$search}%")
                  ->orWhere('user', 'like', "%{$search}%");
            });
        }

        // 2. Filter Tanggal
        if ($request->filled('date_from')) {
            $query->whereDate('tgl', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('tgl', '<=', $request->date_to);
        }

        // 3. Filter Periode Bulan (21-20)
        if ($request->filled('periode_bulan')) {
            $periode = $request->periode_bulan;
            $tglDari = date('Y-m-21', strtotime($periode . '-01 -1 month'));
            $tglSampai = $periode . '-20';
            $query->whereDate('tgl', '>=', $tglDari)
                  ->whereDate('tgl', '<=', $tglSampai);
        }

        // 4. Filter Nominal Range - disesuaikan berdasarkan jenis transaksi
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

        // 5. Filter Kas (Multiple Selection) - disesuaikan berdasarkan jenis transaksi
        if ($request->filled('kas_filter')) {
            $kasArray = is_array($request->kas_filter) ? $request->kas_filter : [$request->kas_filter];
            if ($query->getModel() instanceof View_Transaksi) {
                $transaksiType = $query->getQuery()->wheres[0]['value'] ?? null;
                if ($transaksiType === '48') { // Pemasukan
                    $query->whereIn('untuk_kas', $kasArray);
                } else { // Pengeluaran
                    $query->whereIn('dari_kas', $kasArray);
                }
            }
        }

        // 6. Filter User (Multiple Selection)
        if ($request->filled('user_filter')) {
            $userArray = is_array($request->user_filter) ? $request->user_filter : [$request->user_filter];
            $query->whereIn('user', $userArray);
        }

        return $query;
    }

    /**
     * Filter untuk transfer kas
     */
    private function applyTransferFilters($query, $request)
    {
        // 1. Filter Pencarian
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('keterangan', 'like', "%{$search}%")
                  ->orWhere('user_name', 'like', "%{$search}%");
            });
        }

        // 2. Filter Tanggal
        if ($request->filled('date_from')) {
            $query->whereDate('tgl_catat', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('tgl_catat', '<=', $request->date_to);
        }

        // 3. Filter Periode Bulan (21-20)
        if ($request->filled('periode_bulan')) {
            $periode = $request->periode_bulan;
            $tglDari = date('Y-m-21', strtotime($periode . '-01 -1 month'));
            $tglSampai = $periode . '-20';
            $query->whereDate('tgl_catat', '>=', $tglDari)
                  ->whereDate('tgl_catat', '<=', $tglSampai);
        }

        // 4. Filter Nominal Range
        if ($request->filled('nominal_min')) {
            $query->where('jumlah', '>=', $request->nominal_min);
        }
        if ($request->filled('nominal_max')) {
            $query->where('jumlah', '<=', $request->nominal_max);
        }

        // 5. Filter Kas Asal (Multiple Selection)
        if ($request->filled('kas_asal_filter')) {
            $kasArray = is_array($request->kas_asal_filter) ? $request->kas_asal_filter : [$request->kas_asal_filter];
            $query->whereIn('dari_kas_id', $kasArray);
        }

        // 6. Filter Kas Tujuan (Multiple Selection)
        if ($request->filled('kas_tujuan_filter')) {
            $kasArray = is_array($request->kas_tujuan_filter) ? $request->kas_tujuan_filter : [$request->kas_tujuan_filter];
            $query->whereIn('untuk_kas_id', $kasArray);
        }

        // 7. Filter User (Multiple Selection)
        if ($request->filled('user_filter')) {
            $userArray = is_array($request->user_filter) ? $request->user_filter : [$request->user_filter];
            $query->whereIn('user_name', $userArray);
        }

        return $query;
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
        $query = View_Transaksi::where('transaksi', '7')
            ->with('kasAsal');

        $query = $this->applyFilters($query, $request);
        $dataKas = $query->orderBy('tgl', 'desc')->get();

        $periode = $request->filled('periode_bulan') ? $request->periode_bulan : date('Y-m');
        $totalPengeluaran = $dataKas->sum('kredit');

        $pdf = PDF::loadView('transaksi_kas.pdf.pengeluaran', compact('dataKas', 'periode', 'totalPengeluaran'));
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('laporan_pengeluaran_kas_' . date('Ymd') . '.pdf');
    }

    /**
     * Export data transfer kas ke PDF
     */
    public function exportTransferPdf(Request $request)
    {
        $query = transaksi_kas::with(['dariKas', 'untukKas']);

        $query = $this->applyTransferFilters($query, $request);
        $dataKas = $query->orderBy('tgl_catat', 'desc')->get();

        $periode = $request->filled('periode_bulan') ? $request->periode_bulan : date('Y-m');
        $totalTransfer = $dataKas->sum('jumlah');

        $pdf = PDF::loadView('transaksi_kas.pdf.transfer', compact('dataKas', 'periode', 'totalTransfer'));
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('laporan_transfer_kas_' . date('Ymd') . '.pdf');
    }

    /**
     * Export data ke Excel
     */
    public function exportPemasukan(Request $request)
    {
        $query = View_Transaksi::where('transaksi', '48')
            ->with('kasTujuan');

        $query = $this->applyFilters($query, $request);
        $dataKas = $query->orderBy('tgl', 'desc')->get();

        return $this->exportToExcel($dataKas, 'Pemasukan Kas', $request);
    }

    public function exportPengeluaran(Request $request)
    {
        $query = View_Transaksi::where('transaksi', '7')
            ->with('kasAsal');

        $query = $this->applyFilters($query, $request);
        $dataKas = $query->orderBy('tgl', 'desc')->get();

        return $this->exportToExcel($dataKas, 'Pengeluaran Kas', $request);
    }

    public function exportTransfer(Request $request)
    {
        $query = transaksi_kas::with(['dariKas', 'untukKas']);

        $query = $this->applyTransferFilters($query, $request);
        $dataKas = $query->orderBy('tgl_catat', 'desc')->get();

        return $this->exportToExcel($dataKas, 'Transfer Kas', $request);
    }

    /**
     * Helper method untuk export ke Excel
     */
    private function exportToExcel($data, $type, $request)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header
        $sheet->setCellValue('A1', 'Laporan ' . $type);
        $sheet->setCellValue('A2', 'Periode: ' . ($request->filled('periode_bulan') ? $request->periode_bulan : date('Y-m')));

        // Set column headers
        $headers = ['No', 'Tanggal', 'Keterangan', 'Jumlah', 'Kas', 'User'];
        foreach ($headers as $key => $header) {
            $sheet->setCellValue(chr(65 + $key) . '4', $header);
        }

        // Fill data
        $row = 5;
        foreach ($data as $index => $item) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $item->tgl ?? $item->tgl_catat);
            $sheet->setCellValue('C' . $row, $item->keterangan);
            $sheet->setCellValue('D' . $row, $item->debet ?? $item->kredit ?? $item->jumlah);
            $sheet->setCellValue('E' . $row, $item->kasTujuan->nama ?? $item->kasAsal->nama ?? $item->dariKas->nama);
            $sheet->setCellValue('F' . $row, $item->user ?? $item->user_name);
            $row++;
        }

        // Auto size columns
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'laporan_' . strtolower(str_replace(' ', '_', $type)) . '_' . date('Ymd') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
}
}