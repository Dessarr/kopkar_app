<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\data_mobil;
use App\Models\transaksi_kas;
use App\Models\NamaKasTbl;
use App\Models\jns_akun;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AngkutanController extends Controller
{
    public function pemasukan(Request $request)
    {
        // Get filter parameters
        $startDate = $request->input('tgl_dari');
        $endDate = $request->input('tgl_sampai');
        $search = $request->input('kode_transaksi');
        $kasFilter = $request->input('kas_filter');

        // Base query for pemasukan (jns_trans = 46 for Pendapatan Jasa Sewa Bus)
        $query = transaksi_kas::with(['dariKas', 'untukKas'])
            ->where('jns_trans', '46')
            ->where('dk', 'D');

        // Apply filters
        $query = $this->applyFilters($query, $startDate, $endDate, $search, $kasFilter);

        // Get paginated results
        $transaksi = $query->orderBy('tgl_catat', 'desc')->paginate(10);

        // Get statistics
        $totalPemasukan = $query->sum('jumlah');
        $totalTransaksi = $query->count();

        // Get data for dropdowns
        $kas = NamaKasTbl::where('aktif', 'Y')->get();
        $mobil = data_mobil::where('aktif', 'Y')->get();

        return view('angkutan.pemasukan', compact(
            'transaksi', 
            'totalPemasukan', 
            'totalTransaksi', 
            'kas', 
            'mobil',
            'startDate',
            'endDate',
            'search',
            'kasFilter'
        ));
    }

    public function pengeluaran(Request $request)
    {
        // Get filter parameters
        $startDate = $request->input('tgl_dari');
        $endDate = $request->input('tgl_sampai');
        $search = $request->input('kode_transaksi');
        $kasFilter = $request->input('kas_filter');

        // Base query for pengeluaran (akun = 'Pengeluaran')
        $query = transaksi_kas::with(['dariKas', 'untukKas'])
            ->where('akun', 'Pengeluaran')
            ->where('dk', 'D');

        // Apply filters
        $query = $this->applyFilters($query, $startDate, $endDate, $search, $kasFilter);

        // Get statistics before pagination
        $totalPengeluaran = $query->sum('jumlah');
        $totalTransaksi = $query->count();

        // Get paginated results
        $transaksi = $query->orderBy('tgl_catat', 'desc')->paginate(10);

        // Get data for dropdowns
        $kas = NamaKasTbl::where('aktif', 'Y')->get();
        $mobil = data_mobil::where('aktif', 'Y')->get();

        return view('angkutan.pengeluaran', compact(
            'transaksi', 
            'totalPengeluaran', 
            'totalTransaksi', 
            'kas', 
            'mobil',
            'startDate',
            'endDate',
            'search',
            'kasFilter'
        ));
    }

    private function applyFilters($query, $startDate, $endDate, $search, $kasFilter)
    {
        if ($startDate) {
            $query->whereDate('tgl_catat', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('tgl_catat', '<=', $endDate);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                // Clean search input - remove PK prefix and leading zeros
                $cleanSearch = preg_replace('/^PK/i', '', $search);
                $cleanSearch = ltrim($cleanSearch, '0');
                
                $q->where('keterangan', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$cleanSearch}%");
            });
        }

        if ($kasFilter) {
            // For pengeluaran (D), filter by dari_kas_id
            $query->where('dari_kas_id', $kasFilter);
        }

        return $query;
    }

    public function storePemasukan(Request $request)
    {
        try {
            // Log request data untuk debug
            Log::info('Store Pemasukan Request:', $request->all());
            Log::info('User authenticated:', ['user' => Auth::user()]);
            Log::info('Auth check:', ['check' => Auth::check()]);
            
            // Enhanced validation
            $validated = $request->validate([
                'tgl_catat' => 'required|date',
                'keterangan' => 'nullable|string|max:255',
                'jumlah' => 'required|numeric|min:0.01',
                'untuk_kas_id' => 'required|integer|exists:nama_kas_tbl,id',
                'dari_akun_id' => 'required|string',
                'no_polisi' => 'required|string|max:20|min:3'
            ], [
                'tgl_catat.required' => 'Tanggal Transaksi harus diisi',
                'tgl_catat.date' => 'Format tanggal tidak valid',
                'jumlah.required' => 'Jumlah harus diisi',
                'jumlah.numeric' => 'Jumlah harus berupa angka',
                'jumlah.min' => 'Jumlah tidak boleh kurang dari 0.01',
                'untuk_kas_id.required' => 'Untuk Kas harus dipilih',
                'untuk_kas_id.integer' => 'Untuk Kas tidak valid',
                'untuk_kas_id.exists' => 'Kas yang dipilih tidak ditemukan',
                'dari_akun_id.required' => 'Dari Akun harus dipilih',
                'no_polisi.required' => 'Nomor Polisi harus diisi',
                'no_polisi.min' => 'Nomor Polisi minimal 3 karakter',
                'no_polisi.max' => 'Nomor Polisi maksimal 20 karakter'
            ]);

            // Check if user is authenticated
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak terautentikasi'
                ], 401);
            }

            // Create transaction
            $transaksi = new transaksi_kas();
            $transaksi->tgl_catat = $validated['tgl_catat'];
            $transaksi->keterangan = $validated['keterangan'] ?? '';
            $transaksi->jumlah = $validated['jumlah'];
            $transaksi->jns_trans = $validated['dari_akun_id']; // Pendapatan Jasa Sewa Bus
            $transaksi->akun = 'Pemasukan'; // ENUM value: 'Pemasukan','Pengeluaran','Transfer'
            $transaksi->untuk_kas_id = $validated['untuk_kas_id'];
            $transaksi->dk = 'D'; // Debit untuk pemasukan
            $transaksi->no_polisi = $validated['no_polisi'];
            $transaksi->update_data = now();
            $transaksi->user_name = Auth::user()->u_name ?? 'System';
            $transaksi->id_cabang = 1;
            
            // Save with error handling
            if (!$transaksi->save()) {
                throw new \Exception('Gagal menyimpan data ke database');
            }
            
            // Log success
            Log::info('Data berhasil disimpan dengan ID:', ['id' => $transaksi->id]);

            // Check if request expects JSON (AJAX) or regular form submission
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pemasukan angkutan berhasil disimpan',
                    'data' => [
                        'id' => $transaksi->id,
                        'kode_transaksi' => 'PA' . str_pad($transaksi->id, 5, '0', STR_PAD_LEFT)
                    ]
                ]);
            } else {
                return redirect()->back()->with('success', 'Pemasukan angkutan berhasil disimpan');
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $e->errors()
                ], 422);
            } else {
                return redirect()->back()->withErrors($e->errors())->withInput();
            }
        } catch (\Exception $e) {
            Log::error('Error storing pemasukan:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ], 500);
            } else {
                return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
            }
        }
    }

    public function updatePemasukan(Request $request, $id)
    {
        try {
            // Log request data untuk debug
            Log::info('Update Pemasukan Request:', $request->all());
            Log::info('Update ID:', ['id' => $id]);
            Log::info('User authenticated:', ['user' => Auth::user()]);
            $request->validate([
                'tgl_catat' => 'required|date',
                'keterangan' => 'nullable|string|max:255',
                'jumlah' => 'required|numeric|min:1',
                'untuk_kas_id' => 'required|integer',
                'dari_akun_id' => 'required|string',
                'no_polisi' => 'required|string|max:20'
            ], [
                'tgl_catat.required' => 'Tanggal Transaksi harus diisi',
                'tgl_catat.date' => 'Format tanggal tidak valid',
                'jumlah.required' => 'Jumlah harus diisi',
                'jumlah.numeric' => 'Jumlah harus berupa angka',
                'jumlah.min' => 'Jumlah tidak boleh kurang dari 1',
                'untuk_kas_id.required' => 'Untuk Kas harus dipilih',
                'untuk_kas_id.integer' => 'Untuk Kas tidak valid',
                'dari_akun_id.required' => 'Dari Akun harus dipilih',
                'no_polisi.required' => 'Nomor Polisi harus diisi',
                'no_polisi.max' => 'Nomor Polisi maksimal 20 karakter'
            ]);

            $transaksi = transaksi_kas::findOrFail($id);
            $transaksi->tgl_catat = $request->tgl_catat;
            $transaksi->keterangan = $request->keterangan;
            $transaksi->jumlah = $request->jumlah;
            $transaksi->jns_trans = $request->dari_akun_id;
            $transaksi->akun = 'Pemasukan'; // ENUM value: 'Pemasukan','Pengeluaran','Transfer'
            $transaksi->untuk_kas_id = $request->untuk_kas_id;
            $transaksi->no_polisi = $request->no_polisi;
            $transaksi->update_data = now();
            // Get user name with fallback (Admin model uses 'u_name' field)
            $userName = Auth::check() && Auth::user() ? Auth::user()->u_name : 'System';
            $transaksi->user_name = $userName;
            $transaksi->save();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diupdate'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deletePemasukan($id)
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
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function storePengeluaran(Request $request)
    {
        try {
            // Log request data untuk debug
            Log::info('Store Pengeluaran Request:', $request->all());
            Log::info('User authenticated:', ['user' => Auth::user()]);
            Log::info('Auth check:', ['check' => Auth::check()]);
            
            // Enhanced validation
            $validated = $request->validate([
                'tgl_catat' => 'required|date',
                'keterangan' => 'required|string|max:255',
                'jumlah' => 'required|numeric|min:0.01',
                'dari_kas_id' => 'required|integer|exists:nama_kas_tbl,id',
                'untuk_akun_id' => 'required|integer|exists:jns_akun,id'
            ], [
                'tgl_catat.required' => 'Tanggal Transaksi harus diisi',
                'tgl_catat.date' => 'Format tanggal tidak valid',
                'keterangan.required' => 'Keterangan harus diisi',
                'keterangan.max' => 'Keterangan maksimal 255 karakter',
                'jumlah.required' => 'Jumlah harus diisi',
                'jumlah.numeric' => 'Jumlah harus berupa angka',
                'jumlah.min' => 'Jumlah tidak boleh kurang dari 0.01',
                'dari_kas_id.required' => 'Dari Kas harus dipilih',
                'dari_kas_id.exists' => 'Kas yang dipilih tidak ditemukan',
                'untuk_akun_id.required' => 'Untuk Akun harus dipilih',
                'untuk_akun_id.exists' => 'Akun yang dipilih tidak ditemukan'
            ]);

            // Check if user is authenticated
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak terautentikasi'
                ], 401);
            }

            // Create transaction
            $transaksi = new transaksi_kas();
            $transaksi->tgl_catat = $validated['tgl_catat'];
            $transaksi->keterangan = $validated['keterangan'];
            $transaksi->jumlah = $validated['jumlah'];
            $transaksi->jns_trans = $validated['untuk_akun_id']; // ID dari jns_akun
            $transaksi->akun = 'Pengeluaran'; // ENUM value: 'Pemasukan','Pengeluaran','Transfer'
            $transaksi->dari_kas_id = $validated['dari_kas_id'];
            $transaksi->untuk_kas_id = null; // Pengeluaran tidak ada untuk_kas_id
            $transaksi->dk = 'D'; // Debit untuk pengeluaran
            $transaksi->no_polisi = '';
            $transaksi->update_data = now();
            $transaksi->user_name = Auth::user()->u_name ?? 'System';
            $transaksi->id_cabang = 1;
            
            // Save with error handling
            if (!$transaksi->save()) {
                throw new \Exception('Gagal menyimpan data ke database');
            }
            
            // Log success
            Log::info('Data pengeluaran berhasil disimpan dengan ID:', ['id' => $transaksi->id]);

            // Check if request expects JSON (AJAX) or regular form submission
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pengeluaran angkutan berhasil disimpan',
                    'data' => [
                        'id' => $transaksi->id,
                        'kode_transaksi' => 'PK' . str_pad($transaksi->id, 5, '0', STR_PAD_LEFT)
                    ]
                ]);
            } else {
                return redirect()->back()->with('success', 'Pengeluaran angkutan berhasil disimpan');
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $e->errors()
                ], 422);
            } else {
                return redirect()->back()->withErrors($e->errors())->withInput();
            }
        } catch (\Exception $e) {
            Log::error('Error storing pengeluaran:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ], 500);
            } else {
                return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
            }
        }
    }

    public function updatePengeluaran(Request $request, $id)
    {
        try {
            // Log request data untuk debug
            Log::info('Update Pengeluaran Request:', $request->all());
            Log::info('Update ID:', ['id' => $id]);
            Log::info('User authenticated:', ['user' => Auth::user()]);
            
            // Enhanced validation
            $validated = $request->validate([
                'tgl_catat' => 'required|date',
                'keterangan' => 'required|string|max:255',
                'jumlah' => 'required|numeric|min:0.01',
                'dari_kas_id' => 'required|integer|exists:nama_kas_tbl,id',
                'untuk_akun_id' => 'required|integer|exists:jns_akun,id'
            ], [
                'tgl_catat.required' => 'Tanggal Transaksi harus diisi',
                'tgl_catat.date' => 'Format tanggal tidak valid',
                'keterangan.required' => 'Keterangan harus diisi',
                'keterangan.max' => 'Keterangan maksimal 255 karakter',
                'jumlah.required' => 'Jumlah harus diisi',
                'jumlah.numeric' => 'Jumlah harus berupa angka',
                'jumlah.min' => 'Jumlah tidak boleh kurang dari 0.01',
                'dari_kas_id.required' => 'Dari Kas harus dipilih',
                'dari_kas_id.exists' => 'Kas yang dipilih tidak ditemukan',
                'untuk_akun_id.required' => 'Untuk Akun harus dipilih',
                'untuk_akun_id.exists' => 'Akun yang dipilih tidak ditemukan'
            ]);

            // Check if user is authenticated
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak terautentikasi'
                ], 401);
            }

            // Find and update transaction
            $transaksi = transaksi_kas::findOrFail($id);
            $transaksi->tgl_catat = $validated['tgl_catat'];
            $transaksi->keterangan = $validated['keterangan'];
            $transaksi->jumlah = $validated['jumlah'];
            $transaksi->jns_trans = $validated['untuk_akun_id']; // ID dari jns_akun
            $transaksi->akun = 'Pengeluaran'; // ENUM value: 'Pemasukan','Pengeluaran','Transfer'
            $transaksi->dari_kas_id = $validated['dari_kas_id'];
            $transaksi->untuk_kas_id = null; // Pengeluaran tidak ada untuk_kas_id
            $transaksi->dk = 'D'; // Debit untuk pengeluaran
            $transaksi->no_polisi = '';
            $transaksi->update_data = now();
            $transaksi->user_name = Auth::user()->u_name ?? 'System';
            $transaksi->id_cabang = 1;
            
            // Save with error handling
            if (!$transaksi->save()) {
                throw new \Exception('Gagal mengupdate data ke database');
            }
            
            // Log success
            Log::info('Data pengeluaran berhasil diupdate dengan ID:', ['id' => $transaksi->id]);

            // Check if request expects JSON (AJAX) or regular form submission
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pengeluaran angkutan berhasil diupdate',
                    'data' => [
                        'id' => $transaksi->id,
                        'kode_transaksi' => 'PK' . str_pad($transaksi->id, 5, '0', STR_PAD_LEFT)
                    ]
                ]);
            } else {
                return redirect()->back()->with('success', 'Pengeluaran angkutan berhasil diupdate');
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $e->errors()
                ], 422);
            } else {
                return redirect()->back()->withErrors($e->errors())->withInput();
            }
        } catch (\Exception $e) {
            Log::error('Error updating pengeluaran:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ], 500);
            } else {
                return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
            }
        }
    }

    public function deletePengeluaran($id)
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
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function exportPdfPemasukan(Request $request)
    {
        // Get filter parameters
        $startDate = $request->input('tgl_dari');
        $endDate = $request->input('tgl_sampai');
        $search = $request->input('kode_transaksi');
        $kasFilter = $request->input('kas_filter');

        // Base query
        $query = transaksi_kas::with(['dariKas', 'untukKas'])
            ->where('jns_trans', '46')
            ->where('dk', 'D');

        // Apply filters
        $query = $this->applyFilters($query, $startDate, $endDate, $search, $kasFilter);

        $transaksi = $query->orderBy('tgl_catat', 'desc')->get();
        $totalPemasukan = $transaksi->sum('jumlah');

        $pdf = \PDF::loadView('angkutan.pdf.pemasukan', compact('transaksi', 'totalPemasukan', 'startDate', 'endDate'));
        return $pdf->download('laporan_pemasukan_angkutan_' . date('Ymd') . '.pdf');
    }

    public function exportPdfPengeluaran(Request $request)
    {
        // Get filter parameters
        $startDate = $request->input('tgl_dari');
        $endDate = $request->input('tgl_sampai');
        $search = $request->input('kode_transaksi');
        $kasFilter = $request->input('kas_filter');

        // Base query
        $query = transaksi_kas::with(['dariKas', 'untukKas'])
            ->whereIn('jns_trans', ['55', '56', '57', '58', '59', '60', '61', '62', '63', '64', '65', '66', '67', '68', '69'])
            ->where('dk', 'K');

        // Apply filters
        $query = $this->applyFilters($query, $startDate, $endDate, $search, $kasFilter);

        $transaksi = $query->orderBy('tgl_catat', 'desc')->get();
        $totalPengeluaran = $transaksi->sum('jumlah');

        $pdf = \PDF::loadView('angkutan.pdf.pengeluaran', compact('transaksi', 'totalPengeluaran', 'startDate', 'endDate'));
        return $pdf->download('laporan_pengeluaran_angkutan_' . date('Ymd') . '.pdf');
    }

    public function exportExcelPemasukan(Request $request)
    {
        // Get filter parameters
        $startDate = $request->input('tgl_dari');
        $endDate = $request->input('tgl_sampai');
        $search = $request->input('kode_transaksi');
        $kasFilter = $request->input('kas_filter');

        // Base query
        $query = transaksi_kas::with(['dariKas', 'untukKas'])
            ->where('jns_trans', '46')
            ->where('dk', 'D');

        // Apply filters
        $query = $this->applyFilters($query, $startDate, $endDate, $search, $kasFilter);

        $transaksi = $query->orderBy('tgl_catat', 'desc')->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set title
        $sheet->setCellValue('A1', 'LAPORAN PEMASUKAN ANGKUTAN KARYAWAN');
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        // Set headers
        $sheet->setCellValue('A3', 'Kode Transaksi');
        $sheet->setCellValue('B3', 'Tanggal Transaksi');
        $sheet->setCellValue('C3', 'Uraian');
        $sheet->setCellValue('D3', 'Untuk Kas');
        $sheet->setCellValue('E3', 'Akun');
        $sheet->setCellValue('F3', 'Jumlah');
        $sheet->setCellValue('G3', 'User');

        // Style headers
        $sheet->getStyle('A3:G3')->getFont()->setBold(true);
        $sheet->getStyle('A3:G3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('E2E8F0');

        // Fill data
        $row = 4;
        foreach ($transaksi as $tr) {
            $sheet->setCellValue('A' . $row, 'TKD' . str_pad($tr->id, 6, '0', STR_PAD_LEFT));
            $sheet->setCellValue('B' . $row, $tr->tgl_catat->format('d F Y - H:i'));
            $sheet->setCellValue('C' . $row, $tr->keterangan);
            $sheet->setCellValue('D' . $row, optional($tr->untukKas)->nama);
            $sheet->setCellValue('E' . $row, 'Pendapatan Jasa Sewa Bus');
            $sheet->setCellValue('F' . $row, $tr->jumlah);
            $sheet->setCellValue('G' . $row, $tr->user_name);
            $row++;
        }

        // Auto size columns
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Create writer and download
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'laporan_pemasukan_angkutan_' . date('Ymd') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }

    public function exportExcelPengeluaran(Request $request)
    {
        // Get filter parameters
        $startDate = $request->input('tgl_dari');
        $endDate = $request->input('tgl_sampai');
        $search = $request->input('kode_transaksi');
        $kasFilter = $request->input('kas_filter');

        // Base query
        $query = transaksi_kas::with(['dariKas', 'untukKas'])
            ->whereIn('jns_trans', ['55', '56', '57', '58', '59', '60', '61', '62', '63', '64', '65', '66', '67', '68', '69'])
            ->where('dk', 'K');

        // Apply filters
        $query = $this->applyFilters($query, $startDate, $endDate, $search, $kasFilter);

        $transaksi = $query->orderBy('tgl_catat', 'desc')->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set title
        $sheet->setCellValue('A1', 'LAPORAN PENGELUARAN ANGKUTAN KARYAWAN');
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        // Set headers
        $sheet->setCellValue('A3', 'Kode Transaksi');
        $sheet->setCellValue('B3', 'Tanggal Transaksi');
        $sheet->setCellValue('C3', 'Uraian');
        $sheet->setCellValue('D3', 'Dari Kas');
        $sheet->setCellValue('E3', 'Akun');
        $sheet->setCellValue('F3', 'Jumlah');
        $sheet->setCellValue('G3', 'User');

        // Style headers
        $sheet->getStyle('A3:G3')->getFont()->setBold(true);
        $sheet->getStyle('A3:G3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('E2E8F0');

        // Fill data
        $row = 4;
        foreach ($transaksi as $tr) {
            $sheet->setCellValue('A' . $row, 'TKD' . str_pad($tr->id, 6, '0', STR_PAD_LEFT));
            $sheet->setCellValue('B' . $row, $tr->tgl_catat->format('d F Y - H:i'));
            $sheet->setCellValue('C' . $row, $tr->keterangan);
            $sheet->setCellValue('D' . $row, optional($tr->dariKas)->nama);
            $sheet->setCellValue('E' . $row, $this->getAkunName($tr->jns_trans));
            $sheet->setCellValue('F' . $row, $tr->jumlah);
            $sheet->setCellValue('G' . $row, $tr->user_name);
            $row++;
        }

        // Auto size columns
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Create writer and download
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'laporan_pengeluaran_angkutan_' . date('Ymd') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }

    private function getAkunName($jnsTrans)
    {
        $akunMap = [
            '55' => 'Beban Bahan Bakar',
            '56' => 'Beban Servis',
            '57' => 'Beban Parkir',
            '58' => 'Beban Tol',
            '59' => 'Beban Gaji Supir',
            '60' => 'Beban Gaji Kernet',
            '61' => 'Beban Asuransi',
            '62' => 'Beban Pajak',
            '63' => 'Beban Administrasi',
            '64' => 'Beban Lain-lain',
            '65' => 'Beban Perbaikan',
            '66' => 'Beban P3K',
            '67' => 'Beban Cuci',
            '68' => 'Beban Ban',
            '69' => 'Beban Oli'
        ];

        return $akunMap[$jnsTrans] ?? 'Akun Lain';
    }
} 