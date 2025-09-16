<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TblTransToserda;
use App\Models\TblShu;
use App\Models\TblTransSp;
use App\Models\billing;
use App\Models\data_anggota;
use App\Models\data_barang;
use App\Models\NamaKasTbl;
use App\Models\jns_akun;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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
                ->where('jns_trans', 'toserda')
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
            if ($billing->id_anggota !== $member->id) {
                return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk membayar tagihan ini.');
            }
            
            // Update billing status to paid - kolom status sudah tidak ada, mungkin perlu dibuat tabel terpisah untuk tracking pembayaran
            // $billing->status = 'Y';
            // $billing->tgl_bayar = now();
            // $billing->save();
            
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
                ->where('jns_trans', 'toserda')
                ->exists();
                
            $tr->billing = billing::where('id_transaksi', $tr->id)
                ->where('jns_trans', 'toserda')
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
            // Check if search is numeric (ID) or text (name/ktp)
            if (is_numeric($search)) {
                $query->where('id', $search);
            } else {
                $query->whereHas('anggota', function($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                      ->orWhere('no_ktp', 'like', "%{$search}%");
                });
            }
        }
        
        if ($startDate) {
            $query->whereDate('tgl_transaksi', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->whereDate('tgl_transaksi', '<=', $endDate);
        }
        
        $shuData = $query->orderBy('tgl_transaksi', 'desc')->paginate(10);
        $anggota = data_anggota::where('aktif', 'Y')->get();
        $kas = NamaKasTbl::where('aktif', 'Y')->get();
        
        return view('anggota.shu', compact('shuData', 'anggota', 'kas', 'search', 'startDate', 'endDate'));
    }
    
    /**
     * Store new SHU transaction - Format response seperti project CodeIgniter lama
     */
    public function storeShu(Request $request)
    {
        try {
            Log::info('Store SHU Request:', $request->all());
            
            // Validasi seperti project lama - pastikan jumlah > 0
            $validated = $request->validate([
                'tgl_transaksi' => 'required|date',
                'no_ktp' => 'required|exists:tbl_anggota,no_ktp',
                'jumlah_bayar' => 'required|numeric|min:1' // Pastikan > 0 seperti project lama
            ], [
                'tgl_transaksi.required' => 'Tanggal Transaksi harus diisi',
                'tgl_transaksi.date' => 'Format tanggal tidak valid',
                'no_ktp.required' => 'Anggota harus dipilih',
                'no_ktp.exists' => 'Anggota yang dipilih tidak ditemukan',
                'jumlah_bayar.required' => 'Jumlah SHU harus diisi',
                'jumlah_bayar.numeric' => 'Jumlah SHU harus berupa angka',
                'jumlah_bayar.min' => 'Jumlah SHU tidak boleh kurang dari 1'
            ]);
            
            Log::info('Validation passed:', $validated);

            if (!Auth::check()) {
                Log::error('User not authenticated');
                return response()->json([
                    'ok' => false, 
                    'msg' => '<div class="text-red"><i class="fa fa-ban"></i> User tidak terautentikasi</div>'
                ]);
            }
            
            Log::info('User authenticated:', ['user' => Auth::user()->u_name ?? 'unknown']);

            DB::beginTransaction();
            
            // Format data seperti project lama
            $shu = new TblShu();
            $shu->tgl_transaksi = $validated['tgl_transaksi'];
            $shu->no_ktp = $validated['no_ktp'];
            $shu->jumlah_bayar = $validated['jumlah_bayar'];
            $shu->jns_trans = '46'; // SHU transaction type
            $shu->dk = 'K'; // Kredit seperti project lama
            $shu->kas_id = 1; // Default kas
            $shu->update_data = now();
            $shu->user_name = Auth::user()->u_name ?? 'admin';
            
            if ($shu->save()) {
                DB::commit();
                Log::info('Data SHU berhasil disimpan dengan ID:', ['id' => $shu->id]);
                
                // Response format seperti project CodeIgniter lama
                return response()->json([
                    'ok' => true, 
                    'msg' => '<div class="text-green"><i class="fa fa-check"></i> Data berhasil disimpan</div>'
                ]);
            } else {
                throw new \Exception('Gagal menyimpan data ke database');
            }
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            $errorMessage = '';
            foreach ($e->errors() as $field => $errors) {
                $errorMessage .= implode(', ', $errors) . ' ';
            }
            
            return response()->json([
                'ok' => false, 
                'msg' => '<div class="text-red"><i class="fa fa-ban"></i> ' . trim($errorMessage) . '</div>'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error storing SHU:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            
            return response()->json([
                'ok' => false, 
                'msg' => '<div class="text-red"><i class="fa fa-ban"></i> Gagal menyimpan data, pastikan nilai lebih dari <strong>0 (NOL)</strong>.</div>'
            ]);
        }
    }
    
    /**
     * Get anggota data with foto - seperti project CodeIgniter lama
     */
    public function getAnggotaData(Request $request)
    {
        try {
            $anggota = data_anggota::where('no_ktp', $request->no_ktp)->first();
            
            if ($anggota) {
                // Handle foto - path yang benar
                $foto_path = '';
                if (empty($anggota->file_pic)) {
                    $foto_path = ''; // No default photo, use placeholder
                } else {
                    // Path yang benar: storage/anggota/filename
                    $path = 'anggota/' . $anggota->file_pic;
                    if (\Storage::exists($path)) {
                        $foto_path = asset('storage/' . $path);
                    } else {
                        $foto_path = ''; // Use placeholder if file not found
                    }
                }
                
                return response()->json([
                    'ok' => true,
                    'data' => [
                        'id' => $anggota->id,
                        'nama' => $anggota->nama,
                        'no_ktp' => $anggota->no_ktp,
                        'foto' => $foto_path
                    ]
                ]);
            }
            
            return response()->json([
                'ok' => false, 
                'msg' => 'Anggota tidak ditemukan'
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting anggota data:', ['error' => $e->getMessage()]);
            return response()->json([
                'ok' => false, 
                'msg' => 'Terjadi kesalahan sistem'
            ]);
        }
    }
    
    /**
     * Update SHU transaction
     */
    public function updateShu(Request $request, $id)
    {
        try {
            Log::info('Update SHU Request:', $request->all());
            Log::info('Update ID:', ['id' => $id]);
            Log::info('User authenticated:', ['user' => Auth::user()]);
            
            $validated = $request->validate([
                'tgl_transaksi' => 'required|date',
                'no_ktp' => 'required|exists:tbl_anggota,no_ktp',
                'jumlah_bayar' => 'required|numeric|min:0.01'
            ], [
                'tgl_transaksi.required' => 'Tanggal Transaksi harus diisi',
                'tgl_transaksi.date' => 'Format tanggal tidak valid',
                'no_ktp.required' => 'Anggota harus dipilih',
                'no_ktp.exists' => 'Anggota yang dipilih tidak ditemukan',
                'jumlah_bayar.required' => 'Jumlah SHU harus diisi',
                'jumlah_bayar.numeric' => 'Jumlah SHU harus berupa angka',
                'jumlah_bayar.min' => 'Jumlah SHU tidak boleh kurang dari 0.01'
            ]);

            if (!Auth::check()) {
                return response()->json(['success' => false, 'message' => 'User tidak terautentikasi'], 401);
            }

            $shu = TblShu::findOrFail($id);
            $shu->tgl_transaksi = $validated['tgl_transaksi'];
            $shu->no_ktp = $validated['no_ktp'];
            $shu->jumlah_bayar = $validated['jumlah_bayar'];
            $shu->update_data = now();
            $shu->user_name = Auth::user()->u_name ?? 'System';
            
            if (!$shu->save()) {
                throw new \Exception('Gagal mengupdate data ke database');
            }
            
            Log::info('Data SHU berhasil diupdate dengan ID:', ['id' => $shu->id]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data SHU berhasil diupdate',
                    'data' => ['id' => $shu->id, 'kode_transaksi' => 'TRD' . str_pad($shu->id, 5, '0', STR_PAD_LEFT)]
                ]);
            } else {
                return redirect()->route('anggota.shu')->with('success', 'Data SHU berhasil diperbarui.');
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Validasi gagal', 'errors' => $e->errors()], 422);
            } else {
                return redirect()->back()->withErrors($e->errors())->withInput();
            }
        } catch (\Exception $e) {
            Log::error('Error updating SHU:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
            } else {
                return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
            }
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

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
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
        
        $query = TblTransToserda::with('anggota', 'kas', 'barang');
        
        // Apply filters
        if ($search) {
            $search = trim($search);
            
            // Check if search is a transaction code (TRD format)
            if (preg_match('/^TRD\d+$/i', $search)) {
                // Extract ID from TRD code
                $id = (int) str_replace(['TRD', 'trd'], '', $search);
                $query->where('id', $id);
            }
            // Check if search is just a number (ID)
            elseif (is_numeric($search)) {
                $query->where('id', $search);
            }
            // Check if search is member ID (AG format)
            elseif (preg_match('/^AG\d+$/i', $search)) {
                $memberId = (int) str_replace(['AG', 'ag'], '', $search);
                $query->whereHas('anggota', function($q) use ($memberId) {
                    $q->where('id', $memberId);
                });
            }
            // Otherwise search by name or KTP
            else {
                $query->whereHas('anggota', function($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                      ->orWhere('no_ktp', 'like', "%{$search}%");
                });
            }
        }
        
        if ($startDate) {
            $query->whereDate('tgl_transaksi', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->whereDate('tgl_transaksi', '<=', $endDate);
        }
        
        $toserdaData = $query->whereHas('anggota')->orderBy('tgl_transaksi', 'desc')->paginate(15);
        $anggota = data_anggota::where('aktif', 'Y')->get();
        $kas = NamaKasTbl::where('aktif', 'Y')->get();
        // Jenis transaksi hardcoded: 154=Lain-lain, 155=Toserda
        
        return view('anggota.toserda', compact('toserdaData', 'anggota', 'kas', 'search', 'startDate', 'endDate'));
    }
    
    /**
     * Store new TOSERDA transaction
     */
    public function storeToserda(Request $request)
    {
        // Clean jumlah field - remove commas and dots (thousand separators) and convert to numeric
        $cleanJumlah = str_replace([',', '.'], '', $request->jumlah);
        $request->merge(['jumlah' => $cleanJumlah]);
        
        $request->validate([
            'tgl_transaksi' => 'required|date',
            'no_ktp' => 'required|exists:tbl_anggota,no_ktp',
            'jenis_id' => 'required|in:154,155',
            'jumlah' => 'required|numeric|min:0'
        ], [
            'jumlah.numeric' => 'Format jumlah tidak valid. Gunakan angka saja.',
            'jumlah.min' => 'Jumlah harus lebih dari 0.',
            'jenis_id.in' => 'Jenis transaksi harus dipilih.',
            'no_ktp.exists' => 'Anggota tidak ditemukan.',
        ]);
        
        try {
            DB::beginTransaction();
            
            $anggota = data_anggota::where('no_ktp', $request->no_ktp)->first();
            
            // Validasi jumlah harus lebih dari 0
            if($request->jumlah <= 0) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Gagal menyimpan data, pastikan nilai lebih dari 0 (NOL).'
                    ]);
                }
                return redirect()->back()->with('error', 'Gagal menyimpan data, pastikan nilai lebih dari 0 (NOL).');
            }
            
            $toserda = new TblTransToserda();
            $toserda->tgl_transaksi = $request->tgl_transaksi;
            $toserda->no_ktp = $request->no_ktp;
            $toserda->anggota_id = $anggota->id;
            $toserda->jenis_id = $request->jenis_id;
            $toserda->jumlah = $request->jumlah;
            $toserda->keterangan = '';
            $toserda->dk = 'D'; // Debit
            $toserda->kas_id = 1; // Default kas utama
            $toserda->user_name = Auth::user()->u_name ?? 'admin';
            $toserda->save();
            
            DB::commit();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data TOSERDA berhasil ditambahkan',
                    'data' => ['id' => $toserda->id, 'kode_transaksi' => 'TRD' . str_pad($toserda->id, 5, '0', STR_PAD_LEFT)]
                ]);
            }
            
            return redirect()->route('anggota.toserda')->with('success', 'Data TOSERDA berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollback();
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ]);
            }
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    /**
     * Edit TOSERDA transaction
     */
    public function editToserda($id)
    {
        $toserda = TblTransToserda::findOrFail($id);
        $anggota = data_anggota::where('aktif', 'Y')->get();
        $kas = NamaKasTbl::where('aktif', 'Y')->get();
        // Jenis transaksi hardcoded: 154=Lain-lain, 155=Toserda
        
        return view('anggota.toserda', compact('toserda', 'anggota', 'kas'));
    }
    
    /**
     * Update TOSERDA transaction
     */
    public function updateToserda(Request $request, $id)
    {
        // Clean jumlah field - remove commas and dots (thousand separators) and convert to numeric
        $cleanJumlah = str_replace([',', '.'], '', $request->jumlah);
        $request->merge(['jumlah' => $cleanJumlah]);
        
        $request->validate([
            'tgl_transaksi' => 'required|date',
            'no_ktp' => 'required|exists:tbl_anggota,no_ktp',
            'jenis_id' => 'required|in:154,155',
            'jumlah' => 'required|numeric|min:0'
        ], [
            'jumlah.numeric' => 'Format jumlah tidak valid. Gunakan angka saja.',
            'jumlah.min' => 'Jumlah harus lebih dari 0.',
            'jenis_id.in' => 'Jenis transaksi harus dipilih.',
            'no_ktp.exists' => 'Anggota tidak ditemukan.',
        ]);
        
        try {
            $toserda = TblTransToserda::findOrFail($id);
            $toserda->tgl_transaksi = $request->tgl_transaksi;
            $toserda->no_ktp = $request->no_ktp;
            $toserda->jenis_id = $request->jenis_id;
            $toserda->jumlah = $request->jumlah;
            $toserda->user_name = Auth::user()->u_name ?? 'admin';
            $toserda->save();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data TOSERDA berhasil diupdate',
                    'data' => ['id' => $toserda->id, 'kode_transaksi' => 'TRD' . str_pad($toserda->id, 5, '0', STR_PAD_LEFT)]
                ]);
            }
            
            return redirect()->route('anggota.toserda')->with('success', 'Data TOSERDA berhasil diperbarui.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ]);
            }
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    /**
     * Delete TOSERDA transaction
     */
    public function deleteToserda(Request $request, $id)
    {
        try {
            $toserda = TblTransToserda::findOrFail($id);
            $toserda->delete();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data TOSERDA berhasil dihapus'
                ]);
            }
            
            return redirect()->route('anggota.toserda')->with('success', 'Data TOSERDA berhasil dihapus.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ]);
            }
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
                    $toserda->akun = 'Setoran'; // TOSERDA adalah setoran
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