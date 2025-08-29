<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TblTransToserda;
use App\Models\TblShu;
use App\Models\TblTransSp;
use App\Models\billing;
use App\Models\data_anggota;
use App\Models\NamaKasTbl;
use App\Models\jns_akun;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Barryvdh\DomPDF\Facade\Pdf;

class AnggotaController extends Controller
{
    /**
     * Display Toserda payment page for members
     */
    public function bayarToserda()
    {
        $member = Auth::guard('member')->user();
        $anggota = data_anggota::where('no_ktp', $member->no_ktp)->first();
        
        // Get all Toserda transactions for this member
        $transaksi = TblTransToserda::where('no_ktp', $member->no_ktp)
            ->orderBy('tgl_transaksi', 'desc')
            ->get();
            
        // Group transactions by month and year
        $transactionsByPeriod = $transaksi->groupBy(function($item) {
            return $item->tgl_transaksi->format('Y-m');
        });
        
        // Get billing status for each transaction
        foreach ($transaksi as $tr) {
            $tr->is_billed = billing::where('id_transaksi', $tr->id)
                ->where('jns_transaksi', 'toserda')
                ->exists();
        }
        
        return view('anggota.bayar_toserda_lain', [
            'anggota' => $anggota,
            'transaksi' => $transaksi,
            'transactionsByPeriod' => $transactionsByPeriod
        ]);
    }
    
    /**
     * Process payment for a specific billing
     */
    public function processPayment(Request $request, $billing_code)
    {
        try {
            $billing = billing::findOrFail($billing_code);
            $member = Auth::guard('member')->user();
            
            // Verify that this billing belongs to the logged-in member
            if ($billing->no_ktp !== $member->no_ktp) {
                return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk membayar tagihan ini.');
            }
            
            // Update billing status to paid
            $billing->status_bayar = 'sudah';
            $billing->tgl_bayar = now();
            $billing->save();
            
            return redirect()->route('anggota.bayar.toserda')->with('success', 'Pembayaran berhasil diproses.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    /**
     * Get transactions by period (month/year)
     */
    public function getTransaksiByPeriod(Request $request)
    {
        $member = Auth::guard('member')->user();
        $period = $request->period; // Format: YYYY-MM
        
        if (!$period) {
            return response()->json(['error' => 'Period is required'], 400);
        }
        
        list($year, $month) = explode('-', $period);
        
        $transaksi = TblTransToserda::where('no_ktp', $member->no_ktp)
            ->whereYear('tgl_transaksi', $year)
            ->whereMonth('tgl_transaksi', $month)
            ->orderBy('tgl_transaksi', 'desc')
            ->get();
            
        // Get billing status for each transaction
        foreach ($transaksi as $tr) {
            $tr->is_billed = billing::where('id_transaksi', $tr->id)
                ->where('jns_transaksi', 'toserda')
                ->exists();
                
            $tr->billing = billing::where('id_transaksi', $tr->id)
                ->where('jns_transaksi', 'toserda')
                ->first();
        }
        
        return response()->json([
            'transaksi' => $transaksi,
            'period' => $period
        ]);
    }

    // ==================== SHU METHODS ====================
    
    /**
     * Display SHU management page
     */
    public function shu(Request $request)
    {
        $search = $request->get('search');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        $query = TblShu::with('anggota', 'kas', 'jenis_transaksi');
        
        // Apply filters
        if ($search) {
            $query->whereHas('anggota', function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('no_ktp', 'like', "%{$search}%");
            });
        }
        
        if ($startDate) {
            $query->whereDate('tgl_transaksi', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->whereDate('tgl_transaksi', '<=', $endDate);
        }
        
        $shuData = $query->orderBy('tgl_transaksi', 'desc')->paginate(15);
        $anggota = data_anggota::where('aktif', 'Y')->get();
        $kas = NamaKasTbl::where('aktif', 'Y')->get();
        
        return view('anggota.shu', compact('shuData', 'anggota', 'kas', 'search', 'startDate', 'endDate'));
    }
    
    /**
     * Store new SHU transaction
     */
    public function storeShu(Request $request)
    {
        $request->validate([
            'tgl_transaksi' => 'required|date',
            'no_ktp' => 'required|exists:tbl_anggota,no_ktp',
            'jumlah_bayar' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string|max:255'
        ]);
        
        try {
            DB::beginTransaction();
            
            $shu = new TblShu();
            $shu->tgl_transaksi = $request->tgl_transaksi;
            $shu->no_ktp = $request->no_ktp;
            $shu->jumlah_bayar = $request->jumlah_bayar;
            $shu->jns_trans = '46'; // SHU transaction type
            $shu->dk = 'K'; // Kredit
            $shu->kas_id = 1; // Default kas
            $shu->update_data = now();
            $shu->user_name = 'admin';
            $shu->save();
            
            DB::commit();
            
            return redirect()->route('anggota.shu')->with('success', 'Data SHU berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    /**
     * Edit SHU transaction
     */
    public function editShu($id)
    {
        $shu = TblShu::findOrFail($id);
        $anggota = data_anggota::where('aktif', 'Y')->get();
        $kas = NamaKasTbl::where('aktif', 'Y')->get();
        
        return view('anggota.shu', compact('shu', 'anggota', 'kas'));
    }
    
    /**
     * Update SHU transaction
     */
    public function updateShu(Request $request, $id)
    {
        $request->validate([
            'tgl_transaksi' => 'required|date',
            'no_ktp' => 'required|exists:tbl_anggota,no_ktp',
            'jumlah_bayar' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string|max:255'
        ]);
        
        try {
            $shu = TblShu::findOrFail($id);
            $shu->tgl_transaksi = $request->tgl_transaksi;
            $shu->no_ktp = $request->no_ktp;
            $shu->jumlah_bayar = $request->jumlah_bayar;
            $shu->update_data = now();
            $shu->save();
            
            return redirect()->route('anggota.shu')->with('success', 'Data SHU berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    /**
     * Delete SHU transaction
     */
    public function deleteShu($id)
    {
        try {
            $shu = TblShu::findOrFail($id);
            $shu->delete();
            
            return redirect()->route('anggota.shu')->with('success', 'Data SHU berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    /**
     * Import SHU data from Excel
     */
    public function importShu(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xls,xlsx|max:2048'
        ]);
        
        try {
            $file = $request->file('file');
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            DB::beginTransaction();
            
            $successCount = 0;
            $errorCount = 0;
            
            foreach ($rows as $index => $row) {
                if ($index === 0) continue; // Skip header
                
                if (empty($row[0]) || empty($row[1]) || empty($row[2])) continue;
                
                try {
                    $tgl_transaksi = Carbon::createFromFormat('d/m/Y', $row[0])->format('Y-m-d');
                    $no_ktp = $row[1];
                    $jumlah_bayar = (float) $row[2];
                    
                    // Check if member exists
                    $anggota = data_anggota::where('no_ktp', $no_ktp)->first();
                    if (!$anggota) {
                        $errorCount++;
                        continue;
                    }
                    
                    $shu = new TblShu();
                    $shu->tgl_transaksi = $tgl_transaksi;
                    $shu->no_ktp = $no_ktp;
                    $shu->jumlah_bayar = $jumlah_bayar;
                    $shu->jns_trans = '46';
                    $shu->dk = 'K';
                    $shu->kas_id = 1;
                    $shu->update_data = now();
                    $shu->user_name = 'admin';
                    $shu->save();
                    
                    $successCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                }
            }
            
            DB::commit();
            
            $message = "Import selesai. Berhasil: {$successCount}, Gagal: {$errorCount}";
            return redirect()->route('anggota.shu')->with('success', $message);
            
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    /**
     * Export SHU to PDF
     */
    public function exportShuPdf(Request $request)
    {
        $search = $request->get('search');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        $query = TblShu::with('anggota', 'kas', 'jenis_transaksi');
        
        if ($search) {
            $query->whereHas('anggota', function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('no_ktp', 'like', "%{$search}%");
            });
        }
        
        if ($startDate) {
            $query->whereDate('tgl_transaksi', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->whereDate('tgl_transaksi', '<=', $endDate);
        }
        
        $shuData = $query->orderBy('tgl_transaksi', 'desc')->get();
        $totalShu = $shuData->sum('jumlah_bayar');
        
        $pdf = PDF::loadView('anggota.pdf.shu', compact('shuData', 'totalShu', 'startDate', 'endDate'));
        return $pdf->download('laporan-shu-' . date('Y-m-d') . '.pdf');
    }
    
    /**
     * Export SHU to Excel
     */
    public function exportShuExcel(Request $request)
    {
        $search = $request->get('search');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        $query = TblShu::with('anggota', 'kas', 'jenis_transaksi');
        
        if ($search) {
            $query->whereHas('anggota', function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('no_ktp', 'like', "%{$search}%");
            });
        }
        
        if ($startDate) {
            $query->whereDate('tgl_transaksi', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->whereDate('tgl_transaksi', '<=', $endDate);
        }
        
        $shuData = $query->orderBy('tgl_transaksi', 'desc')->get();
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set headers
        $sheet->setCellValue('A1', 'Kode Transaksi');
        $sheet->setCellValue('B1', 'Tanggal Transaksi');
        $sheet->setCellValue('C1', 'ID Anggota');
        $sheet->setCellValue('D1', 'Nama Anggota');
        $sheet->setCellValue('E1', 'No KTP');
        $sheet->setCellValue('F1', 'Jumlah SHU');
        $sheet->setCellValue('G1', 'User');
        
        $row = 2;
        foreach ($shuData as $shu) {
            $sheet->setCellValue('A' . $row, 'TRD' . str_pad($shu->id, 5, '0', STR_PAD_LEFT));
            $sheet->setCellValue('B' . $row, $shu->tgl_transaksi->format('d/m/Y'));
            $sheet->setCellValue('C' . $row, 'AG' . str_pad($shu->anggota->id ?? 0, 4, '0', STR_PAD_LEFT));
            $sheet->setCellValue('D' . $row, $shu->anggota->nama ?? '');
            $sheet->setCellValue('E' . $row, $shu->no_ktp);
            $sheet->setCellValue('F' . $row, number_format($shu->jumlah_bayar, 0, ',', '.'));
            $sheet->setCellValue('G' . $row, $shu->user_name);
            $row++;
        }
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'laporan-shu-' . date('Y-m-d') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }
    
    /**
     * Print SHU receipt
     */
    public function cetakShu($id)
    {
        $shu = TblShu::with('anggota', 'kas', 'jenis_transaksi')->findOrFail($id);
        
        $pdf = PDF::loadView('anggota.pdf.cetak_shu', compact('shu'));
        return $pdf->stream('bukti-shu-' . $shu->id . '.pdf');
    }

    // ==================== TOSERDA METHODS ====================
    
    /**
     * Display TOSERDA management page
     */
    public function toserda(Request $request)
    {
        $search = $request->get('search');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        $query = TblTransSp::with('anggota', 'kas', 'jenis_transaksi')
            ->whereIn('jenis_id', [154, 155]); // Lain-lain dan Toserda
        
        // Apply filters
        if ($search) {
            $query->whereHas('anggota', function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('no_ktp', 'like', "%{$search}%");
            });
        }
        
        if ($startDate) {
            $query->whereDate('tgl_transaksi', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->whereDate('tgl_transaksi', '<=', $endDate);
        }
        
        $toserdaData = $query->orderBy('tgl_transaksi', 'desc')->paginate(15);
        $anggota = data_anggota::where('aktif', 'Y')->get();
        $kas = NamaKasTbl::where('aktif', 'Y')->get();
        $jenisTransaksi = jns_akun::whereIn('id', [154, 155])->get();
        
        return view('anggota.toserda', compact('toserdaData', 'anggota', 'kas', 'jenisTransaksi', 'search', 'startDate', 'endDate'));
    }
    
    /**
     * Store new TOSERDA transaction
     */
    public function storeToserda(Request $request)
    {
        $request->validate([
            'tgl_transaksi' => 'required|date',
            'no_ktp' => 'required|exists:tbl_anggota,no_ktp',
            'jenis_id' => 'required|in:154,155',
            'jumlah' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string|max:255'
        ]);
        
        try {
            DB::beginTransaction();
            
            $anggota = data_anggota::where('no_ktp', $request->no_ktp)->first();
            
            $toserda = new TblTransSp();
            $toserda->tgl_transaksi = $request->tgl_transaksi;
            $toserda->no_ktp = $request->no_ktp;
            $toserda->anggota_id = $anggota->id;
            $toserda->jenis_id = $request->jenis_id;
            $toserda->jumlah = $request->jumlah;
            $toserda->keterangan = $request->keterangan;
            $toserda->akun = $request->jenis_id;
            $toserda->dk = 'D'; // Debit
            $toserda->kas_id = 1; // Default kas
            $toserda->update_data = now();
            $toserda->user_name = 'admin';
            $toserda->save();
            
            DB::commit();
            
            return redirect()->route('anggota.toserda')->with('success', 'Data TOSERDA berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    /**
     * Edit TOSERDA transaction
     */
    public function editToserda($id)
    {
        $toserda = TblTransSp::findOrFail($id);
        $anggota = data_anggota::where('aktif', 'Y')->get();
        $kas = NamaKasTbl::where('aktif', 'Y')->get();
        $jenisTransaksi = jns_akun::whereIn('id', [154, 155])->get();
        
        return view('anggota.toserda', compact('toserda', 'anggota', 'kas', 'jenisTransaksi'));
    }
    
    /**
     * Update TOSERDA transaction
     */
    public function updateToserda(Request $request, $id)
    {
        $request->validate([
            'tgl_transaksi' => 'required|date',
            'no_ktp' => 'required|exists:tbl_anggota,no_ktp',
            'jenis_id' => 'required|in:154,155',
            'jumlah' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string|max:255'
        ]);
        
        try {
            $toserda = TblTransSp::findOrFail($id);
            $toserda->tgl_transaksi = $request->tgl_transaksi;
            $toserda->no_ktp = $request->no_ktp;
            $toserda->jenis_id = $request->jenis_id;
            $toserda->jumlah = $request->jumlah;
            $toserda->keterangan = $request->keterangan;
            $toserda->akun = $request->jenis_id;
            $toserda->update_data = now();
            $toserda->save();
            
            return redirect()->route('anggota.toserda')->with('success', 'Data TOSERDA berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    /**
     * Delete TOSERDA transaction
     */
    public function deleteToserda($id)
    {
        try {
            $toserda = TblTransSp::findOrFail($id);
            $toserda->delete();
            
            return redirect()->route('anggota.toserda')->with('success', 'Data TOSERDA berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    /**
     * Import TOSERDA data from Excel
     */
    public function importToserda(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xls,xlsx|max:2048'
        ]);
        
        try {
            $file = $request->file('file');
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            DB::beginTransaction();
            
            $successCount = 0;
            $errorCount = 0;
            
            foreach ($rows as $index => $row) {
                if ($index === 0) continue; // Skip header
                
                if (empty($row[0]) || empty($row[1]) || empty($row[2]) || empty($row[3])) continue;
                
                try {
                    $tgl_transaksi = Carbon::createFromFormat('d/m/Y', $row[0])->format('Y-m-d');
                    $no_ktp = $row[1];
                    $jenis_id = (int) $row[2];
                    $jumlah = (float) $row[3];
                    $keterangan = $row[4] ?? '';
                    
                    // Check if member exists
                    $anggota = data_anggota::where('no_ktp', $no_ktp)->first();
                    if (!$anggota) {
                        $errorCount++;
                        continue;
                    }
                    
                    $toserda = new TblTransSp();
                    $toserda->tgl_transaksi = $tgl_transaksi;
                    $toserda->no_ktp = $no_ktp;
                    $toserda->anggota_id = $anggota->id;
                    $toserda->jenis_id = $jenis_id;
                    $toserda->jumlah = $jumlah;
                    $toserda->keterangan = $keterangan;
                    $toserda->akun = $jenis_id;
                    $toserda->dk = 'D';
                    $toserda->kas_id = 1;
                    $toserda->update_data = now();
                    $toserda->user_name = 'admin';
                    $toserda->save();
                    
                    $successCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                }
            }
            
            DB::commit();
            
            $message = "Import selesai. Berhasil: {$successCount}, Gagal: {$errorCount}";
            return redirect()->route('anggota.toserda')->with('success', $message);
            
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    /**
     * Export TOSERDA to PDF
     */
    public function exportToserdaPdf(Request $request)
    {
        $search = $request->get('search');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        $query = TblTransSp::with('anggota', 'kas', 'jenis_simpanan')
            ->whereIn('jenis_id', [154, 155]);
        
        if ($search) {
            $query->whereHas('anggota', function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('no_ktp', 'like', "%{$search}%");
            });
        }
        
        if ($startDate) {
            $query->whereDate('tgl_transaksi', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->whereDate('tgl_transaksi', '<=', $endDate);
        }
        
        $toserdaData = $query->orderBy('tgl_transaksi', 'desc')->get();
        $totalToserda = $toserdaData->sum('jumlah');
        
        $pdf = PDF::loadView('anggota.pdf.toserda', compact('toserdaData', 'totalToserda', 'startDate', 'endDate'));
        return $pdf->download('laporan-toserda-' . date('Y-m-d') . '.pdf');
    }
    
    /**
     * Export TOSERDA to Excel
     */
    public function exportToserdaExcel(Request $request)
    {
        $search = $request->get('search');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        $query = TblTransSp::with('anggota', 'kas', 'jenis_transaksi')
            ->whereIn('jenis_id', [154, 155]);
        
        if ($search) {
            $query->whereHas('anggota', function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('no_ktp', 'like', "%{$search}%");
            });
        }
        
        if ($startDate) {
            $query->whereDate('tgl_transaksi', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->whereDate('tgl_transaksi', '<=', $endDate);
        }
        
        $toserdaData = $query->orderBy('tgl_transaksi', 'desc')->get();
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set headers
        $sheet->setCellValue('A1', 'Kode Transaksi');
        $sheet->setCellValue('B1', 'Tanggal Transaksi');
        $sheet->setCellValue('C1', 'ID Anggota');
        $sheet->setCellValue('D1', 'Nama Anggota');
        $sheet->setCellValue('E1', 'No KTP');
        $sheet->setCellValue('F1', 'Jenis Transaksi');
        $sheet->setCellValue('G1', 'Jumlah');
        $sheet->setCellValue('H1', 'User');
        
        $row = 2;
        foreach ($toserdaData as $toserda) {
            $jenisTransaksi = $toserda->jenis_id == 154 ? 'Lain-lain' : 'Toserda';
            
            $sheet->setCellValue('A' . $row, 'TRD' . str_pad($toserda->id, 5, '0', STR_PAD_LEFT));
            $sheet->setCellValue('B' . $row, $toserda->tgl_transaksi->format('d/m/Y'));
            $sheet->setCellValue('C' . $row, 'AG' . str_pad($toserda->anggota->id ?? 0, 4, '0', STR_PAD_LEFT));
            $sheet->setCellValue('D' . $row, $toserda->anggota->nama ?? '');
            $sheet->setCellValue('E' . $row, $toserda->no_ktp);
            $sheet->setCellValue('F' . $row, $jenisTransaksi);
            $sheet->setCellValue('G' . $row, number_format($toserda->jumlah, 0, ',', '.'));
            $sheet->setCellValue('H' . $row, $toserda->user_name);
            $row++;
        }
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'laporan-toserda-' . date('Y-m-d') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }
    
    /**
     * Print TOSERDA receipt
     */
    public function cetakToserda($id)
    {
        $toserda = TblTransSp::with('anggota', 'kas', 'jenis_transaksi')->findOrFail($id);
        
        $pdf = PDF::loadView('anggota.pdf.cetak_toserda', compact('toserda'));
        return $pdf->stream('bukti-toserda-' . $toserda->id . '.pdf');
    }
}