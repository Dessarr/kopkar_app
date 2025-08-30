<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\data_barang;
use App\Models\data_anggota;
use App\Models\jns_akun;
use App\Models\billing;
use App\Models\transaksi_kas;
use App\Models\NamaKasTbl;
use App\Models\DataKas;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use App\Imports\ToserdaImport;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Auth;

class ToserdaController extends Controller
{
    private $bulanList = [
        '01' => 'Januari',
        '02' => 'Februari',
        '03' => 'Maret',
        '04' => 'April',
        '05' => 'Mei',
        '06' => 'Juni',
        '07' => 'Juli',
        '08' => 'Agustus',
        '09' => 'September',
        '10' => 'Oktober',
        '11' => 'November',
        '12' => 'Desember'
    ];

    public function penjualan(Request $request)
    {
        try {
            $kas = NamaKasTbl::all();
            
            // Query for penjualan transactions (jns_trans: 112,113,114,115,116)
            $query = transaksi_kas::with(['untukKas', 'dariKas'])
                ->where('akun', 'Pemasukan')
                ->whereIn('jns_trans', ['112', '113', '114', '115', '116'])
                ->orderBy('tgl_catat', 'desc');
            
            // 1. Filter Search (Multi-field)
            if ($request->filled('search')) {
                $search = trim($request->search);
                $query->where(function($q) use ($search) {
                    $q->where('keterangan', 'like', "%{$search}%")
                      ->orWhere('user_name', 'like', "%{$search}%");
                });
            }

            // 2. Filter Kas (Multiple Selection)
            if ($request->filled('kas_filter')) {
                $kasArray = is_array($request->kas_filter) ? $request->kas_filter : [$request->kas_filter];
                $query->whereIn('untuk_kas_id', $kasArray);
            }

            // 3. Filter User (Multiple Selection)
            if ($request->filled('user_filter')) {
                $userArray = is_array($request->user_filter) ? $request->user_filter : [$request->user_filter];
                $query->whereIn('user_name', $userArray);
            }

            // 4. Filter Date Range
            if ($request->filled('date_from')) {
                $query->whereDate('tgl_catat', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('tgl_catat', '<=', $request->date_to);
            }

            // 5. Filter Periode Bulan (21-20)
            if ($request->filled('periode_bulan')) {
                $periode = $request->periode_bulan;
                $tglDari = date('Y-m-21', strtotime($periode . '-01 -1 month'));
                $tglSampai = $periode . '-20';
                $query->whereDate('tgl_catat', '>=', $tglDari)
                      ->whereDate('tgl_catat', '<=', $tglSampai);
            }

            // 6. Filter Nominal Range
            if ($request->filled('nominal_min')) {
                $query->where('jumlah', '>=', $request->nominal_min);
            }
            if ($request->filled('nominal_max')) {
                $query->where('jumlah', '<=', $request->nominal_max);
            }
            
            $transaksi = $query->paginate(15);
            
            // Get unique users for filter dropdown
            $users = transaksi_kas::where('akun', 'Pemasukan')
                ->whereIn('jns_trans', ['112', '113', '114', '115', '116'])
                ->whereNotNull('user_name')
                ->distinct()
                ->pluck('user_name')
                ->filter()
                ->values();
            
            return view('toserda.penjualan', compact('kas', 'transaksi', 'users'));
        } catch (\Exception $e) {
            \Log::error('Error in penjualan: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function pembelian(Request $request)
    {
        try {
            $kas = NamaKasTbl::all();
            $barang = data_barang::all(); // Add this line for pembelian view
            
            // Query for pembelian transactions (jns_trans: 9,117,118,119,120,121)
            $query = transaksi_kas::with(['dariKas', 'untukKas'])
                ->whereIn('jns_trans', ['9', '117', '118', '119', '120', '121'])
                ->orderBy('tgl_catat', 'desc');
            
            // 1. Filter Search (Multi-field)
            if ($request->filled('search')) {
                $search = trim($request->search);
                $query->where(function($q) use ($search) {
                    $q->where('keterangan', 'like', "%{$search}%")
                      ->orWhere('user_name', 'like', "%{$search}%");
                });
            }

            // 2. Filter Kas (Multiple Selection)
            if ($request->filled('kas_filter')) {
                $kasArray = is_array($request->kas_filter) ? $request->kas_filter : [$request->kas_filter];
                $query->whereIn('dari_kas_id', $kasArray);
            }

            // 3. Filter User (Multiple Selection)
            if ($request->filled('user_filter')) {
                $userArray = is_array($request->user_filter) ? $request->user_filter : [$request->user_filter];
                $query->whereIn('user_name', $userArray);
            }

            // 4. Filter Date Range
            if ($request->filled('date_from')) {
                $query->whereDate('tgl_catat', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('tgl_catat', '<=', $request->date_to);
            }

            // 5. Filter Periode Bulan (21-20)
            if ($request->filled('periode_bulan')) {
                $periode = $request->periode_bulan;
                $tglDari = date('Y-m-21', strtotime($periode . '-01 -1 month'));
                $tglSampai = $periode . '-20';
                $query->whereDate('tgl_catat', '>=', $tglDari)
                      ->whereDate('tgl_catat', '<=', $tglSampai);
            }

            // 6. Filter Nominal Range
            if ($request->filled('nominal_min')) {
                $query->where('jumlah', '>=', $request->nominal_min);
            }
            if ($request->filled('nominal_max')) {
                $query->where('jumlah', '<=', $request->nominal_max);
            }
            
            $transaksi = $query->paginate(15);
            
            // Get unique users for filter dropdown
            $users = transaksi_kas::whereIn('jns_trans', ['9', '117', '118', '119', '120', '121'])
                ->whereNotNull('user_name')
                ->distinct()
                ->pluck('user_name')
                ->filter()
                ->values();
            
            return view('toserda.pembelian', compact('kas', 'barang', 'transaksi', 'users'));
        } catch (\Exception $e) {
            \Log::error('Error in pembelian: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function biayaUsaha(Request $request)
    {
        try {
            $kas = NamaKasTbl::all();
            
            // Query for biaya usaha transactions (jns_trans: 122,123,124,152)
            $query = transaksi_kas::with(['dariKas', 'untukKas'])
                ->whereIn('jns_trans', ['122', '123', '124', '152'])
                ->orderBy('tgl_catat', 'desc');
            
            // 1. Filter Search (Multi-field)
            if ($request->filled('search')) {
                $search = trim($request->search);
                $query->where(function($q) use ($search) {
                    $q->where('keterangan', 'like', "%{$search}%")
                      ->orWhere('user_name', 'like', "%{$search}%");
                });
            }

            // 2. Filter Kas (Multiple Selection)
            if ($request->filled('kas_filter')) {
                $kasArray = is_array($request->kas_filter) ? $request->kas_filter : [$request->kas_filter];
                $query->whereIn('dari_kas_id', $kasArray);
            }

            // 3. Filter User (Multiple Selection)
            if ($request->filled('user_filter')) {
                $userArray = is_array($request->user_filter) ? $request->user_filter : [$request->user_filter];
                $query->whereIn('user_name', $userArray);
            }

            // 4. Filter Date Range
            if ($request->filled('date_from')) {
                $query->whereDate('tgl_catat', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('tgl_catat', '<=', $request->date_to);
            }

            // 5. Filter Periode Bulan (21-20)
            if ($request->filled('periode_bulan')) {
                $periode = $request->periode_bulan;
                $tglDari = date('Y-m-21', strtotime($periode . '-01 -1 month'));
                $tglSampai = $periode . '-20';
                $query->whereDate('tgl_catat', '>=', $tglDari)
                      ->whereDate('tgl_catat', '<=', $tglSampai);
            }

            // 6. Filter Nominal Range
            if ($request->filled('nominal_min')) {
                $query->where('jumlah', '>=', $request->nominal_min);
            }
            if ($request->filled('nominal_max')) {
                $query->where('jumlah', '<=', $request->nominal_max);
            }
            
            $transaksi = $query->paginate(15);
            
            // Get unique users for filter dropdown
            $users = transaksi_kas::whereIn('jns_trans', ['122', '123', '124', '152'])
                ->whereNotNull('user_name')
                ->distinct()
                ->pluck('user_name')
                ->filter()
                ->values();
            
            return view('toserda.biaya_usaha', compact('kas', 'transaksi', 'users'));
        } catch (\Exception $e) {
            \Log::error('Error in biaya usaha: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    public function lainLain(Request $request)
    {
        // Inisialisasi $bulanList di awal untuk memastikan selalu terdefinisi
        $bulanList = $this->bulanList;
        
        try {
            $bulan = $request->get('bulan', date('m'));
            $tahun = $request->get('tahun', date('Y'));
            $search = $request->get('search');
            $billingStatus = $request->get('billing_status');

            // Simple query first - just get basic data
            $query = DB::table('tbl_trans_toserda as t')
                ->select('t.*')
                ->orderBy('t.tgl_transaksi', 'desc');

            // Filter by month and year
            if ($bulan && $tahun) {
                $query->whereYear('t.tgl_transaksi', $tahun)
                      ->whereMonth('t.tgl_transaksi', $bulan);
            }

            // Filter by search
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('t.no_ktp', 'like', "%{$search}%");
                });
            }

            // Filter by billing status
            if ($billingStatus) {
                if ($billingStatus === 'billed') {
                    $query->where('t.status_billing', 'Y');
                } elseif ($billingStatus === 'unbilled') {
                    $query->where('t.status_billing', '!=', 'Y');
                }
            }

            $transaksi = $query->paginate(15);

            // Debug: Log the query and data
            \Log::info('Toserda Lain-lain Query: ' . $query->toSql());
            \Log::info('Toserda Lain-lain Data Count: ' . $transaksi->count());

            // Test: Check if data exists
            if ($transaksi->count() == 0) {
                \Log::warning('No data found in tbl_trans_toserda');
            }

            return view('toserda.lain_lain', compact('transaksi', 'bulanList'));
        } catch (\Exception $e) {
            \Log::error('Error in lain lain: ' . $e->getMessage());
            \Log::error('Error Stack: ' . $e->getTraceAsString());
            
            // Return simple error view instead of redirect
            $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);
            return view('toserda.lain_lain', [
                'transaksi' => $emptyPaginator,
                'bulanList' => $bulanList,
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }



    // Export methods
    public function exportPenjualan(Request $request)
    {
        try {
            $query = transaksi_kas::with(['untukKas', 'dariKas'])
                ->where('akun', 'Pemasukan')
                ->whereIn('jns_trans', ['112', '113', '114', '115', '116'])
                ->orderBy('tgl_catat', 'desc');

            // Apply same filters as index method
            if ($request->filled('search')) {
                $search = trim($request->search);
                $query->where(function($q) use ($search) {
                    $q->where('keterangan', 'like', "%{$search}%")
                      ->orWhere('user_name', 'like', "%{$search}%");
                });
            }

            if ($request->filled('kas_filter')) {
                $kasArray = is_array($request->kas_filter) ? $request->kas_filter : [$request->kas_filter];
                $query->whereIn('untuk_kas_id', $kasArray);
            }

            if ($request->filled('user_filter')) {
                $userArray = is_array($request->user_filter) ? $request->user_filter : [$request->user_filter];
                $query->whereIn('user_name', $userArray);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('tgl_catat', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('tgl_catat', '<=', $request->date_to);
            }

            if ($request->filled('periode_bulan')) {
                $periode = $request->periode_bulan;
                $tglDari = date('Y-m-21', strtotime($periode . '-01 -1 month'));
                $tglSampai = $periode . '-20';
                $query->whereDate('tgl_catat', '>=', $tglDari)
                      ->whereDate('tgl_catat', '<=', $tglSampai);
            }

            if ($request->filled('nominal_min')) {
                $query->where('jumlah', '>=', $request->nominal_min);
            }
            if ($request->filled('nominal_max')) {
                $query->where('jumlah', '<=', $request->nominal_max);
            }

            $data = $query->get();

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set headers
            $sheet->setCellValue('A1', 'Tanggal');
            $sheet->setCellValue('B1', 'Keterangan');
            $sheet->setCellValue('C1', 'Jumlah');
            $sheet->setCellValue('D1', 'Kas');
            $sheet->setCellValue('E1', 'User');
            $sheet->setCellValue('F1', 'Jenis Transaksi');

            $row = 2;
            foreach ($data as $item) {
                $sheet->setCellValue('A' . $row, $item->tgl_catat ? $item->tgl_catat->format('d/m/Y') : '-');
                $sheet->setCellValue('B' . $row, $item->keterangan ?? '-');
                $sheet->setCellValue('C' . $row, number_format($item->jumlah ?? 0, 0, ',', '.'));
                $sheet->setCellValue('D' . $row, optional($item->untukKas)->nama ?? 'N/A');
                $sheet->setCellValue('E' . $row, $item->user_name ?? '-');
                $sheet->setCellValue('F' . $row, $item->jns_trans ?? '-');
                $row++;
            }

            $writer = new Xlsx($spreadsheet);
            $filename = 'penjualan_toserda_' . date('Y-m-d_H-i-s') . '.xlsx';
            
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            $writer->save('php://output');
            exit;
        } catch (\Exception $e) {
            \Log::error('Error in export penjualan: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat export: ' . $e->getMessage());
        }
    }

    public function exportPembelian(Request $request)
    {
        try {
            $query = transaksi_kas::with(['dariKas', 'untukKas'])
                ->whereIn('jns_trans', ['9', '117', '118', '119', '120', '121'])
                ->orderBy('tgl_catat', 'desc');

            // Apply same filters as index method
            if ($request->filled('search')) {
                $search = trim($request->search);
                $query->where(function($q) use ($search) {
                    $q->where('keterangan', 'like', "%{$search}%")
                      ->orWhere('user_name', 'like', "%{$search}%");
                });
            }

            if ($request->filled('kas_filter')) {
                $kasArray = is_array($request->kas_filter) ? $request->kas_filter : [$request->kas_filter];
                $query->whereIn('dari_kas_id', $kasArray);
            }

            if ($request->filled('user_filter')) {
                $userArray = is_array($request->user_filter) ? $request->user_filter : [$request->user_filter];
                $query->whereIn('user_name', $userArray);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('tgl_catat', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('tgl_catat', '<=', $request->date_to);
            }

            if ($request->filled('periode_bulan')) {
                $periode = $request->periode_bulan;
                $tglDari = date('Y-m-21', strtotime($periode . '-01 -1 month'));
                $tglSampai = $periode . '-20';
                $query->whereDate('tgl_catat', '>=', $tglDari)
                      ->whereDate('tgl_catat', '<=', $tglSampai);
            }

            if ($request->filled('nominal_min')) {
                $query->where('jumlah', '>=', $request->nominal_min);
            }
            if ($request->filled('nominal_max')) {
                $query->where('jumlah', '<=', $request->nominal_max);
            }

            $data = $query->get();

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set headers
            $sheet->setCellValue('A1', 'Tanggal');
            $sheet->setCellValue('B1', 'Keterangan');
            $sheet->setCellValue('C1', 'Jumlah');
            $sheet->setCellValue('D1', 'Kas');
            $sheet->setCellValue('E1', 'User');
            $sheet->setCellValue('F1', 'Jenis Transaksi');

            $row = 2;
            foreach ($data as $item) {
                $sheet->setCellValue('A' . $row, $item->tgl_catat ? $item->tgl_catat->format('d/m/Y') : '-');
                $sheet->setCellValue('B' . $row, $item->keterangan ?? '-');
                $sheet->setCellValue('C' . $row, number_format($item->jumlah ?? 0, 0, ',', '.'));
                $sheet->setCellValue('D' . $row, optional($item->dariKas)->nama ?? 'N/A');
                $sheet->setCellValue('E' . $row, $item->user_name ?? '-');
                $sheet->setCellValue('F' . $row, $item->jns_trans ?? '-');
                $row++;
            }

            $writer = new Xlsx($spreadsheet);
            $filename = 'pembelian_toserda_' . date('Y-m-d_H-i-s') . '.xlsx';
            
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            $writer->save('php://output');
            exit;
        } catch (\Exception $e) {
            \Log::error('Error in export pembelian: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat export: ' . $e->getMessage());
        }
    }

    public function exportBiayaUsaha(Request $request)
    {
        try {
            $query = transaksi_kas::with(['dariKas', 'untukKas'])
                ->whereIn('jns_trans', ['122', '123', '124', '152'])
                ->orderBy('tgl_catat', 'desc');

            // Apply same filters as index method
            if ($request->filled('search')) {
                $search = trim($request->search);
                $query->where(function($q) use ($search) {
                    $q->where('keterangan', 'like', "%{$search}%")
                      ->orWhere('user_name', 'like', "%{$search}%");
                });
            }

            if ($request->filled('kas_filter')) {
                $kasArray = is_array($request->kas_filter) ? $request->kas_filter : [$request->kas_filter];
                $query->whereIn('dari_kas_id', $kasArray);
            }

            if ($request->filled('user_filter')) {
                $userArray = is_array($request->user_filter) ? $request->user_filter : [$request->user_filter];
                $query->whereIn('user_name', $userArray);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('tgl_catat', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('tgl_catat', '<=', $request->date_to);
            }

            if ($request->filled('periode_bulan')) {
                $periode = $request->periode_bulan;
                $tglDari = date('Y-m-21', strtotime($periode . '-01 -1 month'));
                $tglSampai = $periode . '-20';
                $query->whereDate('tgl_catat', '>=', $tglDari)
                      ->whereDate('tgl_catat', '<=', $tglSampai);
            }

            if ($request->filled('nominal_min')) {
                $query->where('jumlah', '>=', $request->nominal_min);
            }
            if ($request->filled('nominal_max')) {
                $query->where('jumlah', '<=', $request->nominal_max);
            }

            $data = $query->get();

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set headers
            $sheet->setCellValue('A1', 'Tanggal');
            $sheet->setCellValue('B1', 'Keterangan');
            $sheet->setCellValue('C1', 'Jumlah');
            $sheet->setCellValue('D1', 'Kas');
            $sheet->setCellValue('E1', 'User');
            $sheet->setCellValue('F1', 'Jenis Transaksi');

            $row = 2;
            foreach ($data as $item) {
                $sheet->setCellValue('A' . $row, $item->tgl_catat ? $item->tgl_catat->format('d/m/Y') : '-');
                $sheet->setCellValue('B' . $row, $item->keterangan ?? '-');
                $sheet->setCellValue('C' . $row, number_format($item->jumlah ?? 0, 0, ',', '.'));
                $sheet->setCellValue('D' . $row, optional($item->dariKas)->nama ?? 'N/A');
                $sheet->setCellValue('E' . $row, $item->user_name ?? '-');
                $sheet->setCellValue('F' . $row, $item->jns_trans ?? '-');
                $row++;
            }

            $writer = new Xlsx($spreadsheet);
            $filename = 'biaya_usaha_toserda_' . date('Y-m-d_H-i-s') . '.xlsx';
            
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            $writer->save('php://output');
            exit;
        } catch (\Exception $e) {
            \Log::error('Error in export biaya usaha: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat export: ' . $e->getMessage());
        }
    }

    // Store methods
    public function storePenjualan(Request $request)
    {
        try {
            $request->validate([
                'keterangan' => 'required|string',
                'jumlah' => 'required|numeric|min:0',
                'untuk_kas_id' => 'required|exists:nama_kas_tbl,id',
                'jns_trans' => 'required|in:112,113,114,115,116'
            ]);

            transaksi_kas::create([
                'tgl_catat' => now(),
                'jumlah' => $request->jumlah,
                'keterangan' => $request->keterangan,
                'akun' => 'Pemasukan',
                'untuk_kas_id' => $request->untuk_kas_id,
                'jns_trans' => $request->jns_trans,
                'dk' => 'D',
                'user_name' => Auth::user()->name ?? 'admin',
                'update_data' => now()
            ]);

            return response()->json(['success' => true, 'message' => 'Data penjualan berhasil disimpan']);
        } catch (\Exception $e) {
            \Log::error('Error in store penjualan: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    public function storePembelian(Request $request)
    {
        try {
            $request->validate([
                'keterangan' => 'required|string',
                'jumlah' => 'required|numeric|min:0',
                'dari_kas_id' => 'required|exists:nama_kas_tbl,id',
                'jns_trans' => 'required|in:9,117,118,119,120,121'
            ]);

            transaksi_kas::create([
                'tgl_catat' => now(),
                'jumlah' => $request->jumlah,
                'keterangan' => $request->keterangan,
                'akun' => 'Pengeluaran',
                'dari_kas_id' => $request->dari_kas_id,
                'jns_trans' => $request->jns_trans,
                'dk' => 'K',
                'user_name' => Auth::user()->name ?? 'admin',
                'update_data' => now()
            ]);

            return response()->json(['success' => true, 'message' => 'Data pembelian berhasil disimpan']);
        } catch (\Exception $e) {
            \Log::error('Error in store pembelian: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    public function storeBiayaUsaha(Request $request)
    {
        try {
            $request->validate([
                'keterangan' => 'required|string',
                'jumlah' => 'required|numeric|min:0',
                'dari_kas_id' => 'required|exists:nama_kas_tbl,id',
                'jns_trans' => 'required|in:122,123,124,152'
            ]);

            transaksi_kas::create([
                'tgl_catat' => now(),
                'jumlah' => $request->jumlah,
                'keterangan' => $request->keterangan,
                'akun' => 'Pengeluaran',
                'dari_kas_id' => $request->dari_kas_id,
                'jns_trans' => $request->jns_trans,
                'dk' => 'K',
                'user_name' => Auth::user()->name ?? 'admin',
                'update_data' => now()
            ]);

            return response()->json(['success' => true, 'message' => 'Data biaya usaha berhasil disimpan']);
        } catch (\Exception $e) {
            \Log::error('Error in store biaya usaha: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    public function storeUploadToserda(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls'
            ]);

            Excel::import(new ToserdaImport, $request->file('file'));

            return redirect()->back()->with('success', 'Data berhasil diupload');
        } catch (\Exception $e) {
            \Log::error('Error in upload toserda: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat upload: ' . $e->getMessage());
        }
    }
    
    public function processMonthlyBilling(Request $request)
    {
        try {
            $bulan = $request->bulan;
            $tahun = $request->tahun;

            // Process billing logic here
            // This would typically involve calculating totals and creating billing records

            return redirect()->back()->with('success', 'Billing bulanan berhasil diproses');
        } catch (\Exception $e) {
            \Log::error('Error in process billing: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memproses billing: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set headers
            $sheet->setCellValue('A1', 'Tanggal');
            $sheet->setCellValue('B1', 'Keterangan');
            $sheet->setCellValue('C1', 'Jumlah');
            $sheet->setCellValue('D1', 'Akun');
            $sheet->setCellValue('E1', 'Dari Kas ID');
            $sheet->setCellValue('F1', 'Untuk Kas ID');
            $sheet->setCellValue('G1', 'Jenis Transaksi');
            $sheet->setCellValue('H1', 'DK');

            $filename = 'template_toserda_' . date('Y-m-d_H-i-s') . '.xlsx';
            
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;
        } catch (\Exception $e) {
            \Log::error('Error in download template: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat download template: ' . $e->getMessage());
        }
    }
}