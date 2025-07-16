<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\data_barang;
use App\Models\data_anggota;
use App\Models\jns_akun;
use App\Models\billing;
use App\Models\TblTransToserda;
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
            $barang = data_barang::where('stok', '>', 0)->get();
            $anggota = data_anggota::where('aktif', 'Y')->get();
            $kas = NamaKasTbl::all();
            
            // Query for transaction history
            $query = TblTransToserda::with([
                'anggota' => function($q) { 
                    $q->select('id', 'no_ktp', 'nama'); 
                },
                'barang' => function($q) { 
                    $q->select('id', 'nm_barang', 'harga_jual', 'harga', 'stok'); 
                },
                'kas' => function($q) { 
                    $q->select('id', 'nama'); 
                },
                'billing'
            ])
            ->where('dk', 'D') // Only debit transactions (sales)
            ->orderBy('tgl_transaksi', 'desc');
            
            // Filter by date range
            if ($request->has('tanggal_awal') && $request->tanggal_awal) {
                $query->whereDate('tgl_transaksi', '>=', $request->tanggal_awal);
            }
            
            if ($request->has('tanggal_akhir') && $request->tanggal_akhir) {
                $query->whereDate('tgl_transaksi', '<=', $request->tanggal_akhir);
            }
            
            // Filter by search term (member name or KTP)
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->whereHas('anggota', function($q) use ($search) {
                        $q->where('nama', 'like', '%' . $search . '%')
                          ->orWhere('no_ktp', 'like', '%' . $search . '%');
                    })
                    ->orWhere('no_ktp', 'like', '%' . $search . '%');
                });
            }
            
            $transaksi = $query->paginate(10);
            
            return view('toserda.penjualan', compact('barang', 'anggota', 'kas', 'transaksi'));
        } catch (\Exception $e) {
            \Log::error('Error in penjualan: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function pembelian(Request $request)
    {
        try {
            $barang = data_barang::all();
            $kas = NamaKasTbl::all();
            
            // Query for transaction history
            $query = TblTransToserda::with([
                'barang' => function($q) { 
                    $q->select('id', 'nm_barang', 'harga_beli', 'harga', 'stok'); 
                },
                'kas' => function($q) { 
                    $q->select('id', 'nama'); 
                }
            ])
            ->where('dk', 'K') // Only kredit transactions (purchases)
            ->orderBy('tgl_transaksi', 'desc');
            
            // Filter by date range
            if ($request->has('tanggal_awal') && $request->tanggal_awal) {
                $query->whereDate('tgl_transaksi', '>=', $request->tanggal_awal);
            }
            
            if ($request->has('tanggal_akhir') && $request->tanggal_akhir) {
                $query->whereDate('tgl_transaksi', '<=', $request->tanggal_akhir);
            }
            
            // Filter by barang
            if ($request->has('barang_id') && $request->barang_id) {
                $query->where('jenis_id', $request->barang_id);
            }
            
            $transaksi = $query->paginate(10);
            
            return view('toserda.pembelian', compact('barang', 'kas', 'transaksi'));
        } catch (\Exception $e) {
            \Log::error('Error in pembelian: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function biayaUsaha()
    {
        try {
            $kas = NamaKasTbl::all();
            return view('toserda.biaya_usaha', compact('kas'));
        } catch (\Exception $e) {
            \Log::error('Error in biayaUsaha: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    public function lainLain(Request $request)
    {
        $bulan = $request->bulan;
        $tahun = $request->tahun ?? date('Y');
        $search = $request->search;
        $billingStatus = $request->billing_status;
        
        $query = TblTransToserda::with(['anggota', 'kas']);
            
        if ($bulan) {
            $query->whereMonth('tgl_transaksi', $bulan);
            }
            
        if ($tahun) {
            $query->whereYear('tgl_transaksi', $tahun);
            }
            
        if ($search) {
            $query->whereHas('anggota', function($q) use ($search) {
                $q->where('nama', 'like', "%$search%")
                  ->orWhere('no_ktp', 'like', "%$search%");
                });
            }

        if ($billingStatus === 'billed') {
                    $query->whereHas('billing');
        } elseif ($billingStatus === 'unbilled') {
                    $query->whereDoesntHave('billing');
                }
            
        $transaksi = $query->orderBy('tgl_transaksi', 'desc')->paginate(15);
            
        // Get kas data from both models and merge them
        $namaKasTbl = NamaKasTbl::where('aktif', 'Y')->get();
        $dataKas = DataKas::where('aktif', 'Y')->get();
        
        // Use the nama_kas_tbl data as primary
        $kas = $namaKasTbl;
        
        $bulanList = [
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
        
        return view('toserda.lain_lain', compact('transaksi', 'bulanList', 'kas'));
    }

    public function storePenjualan(Request $request)
    {
        try {
            DB::beginTransaction();
            
            $request->validate([
                'barang_id' => 'required|exists:data_barang,id',
                'anggota_id' => 'required|exists:data_anggota,id',
                'jumlah' => 'required|numeric|min:1',
                'kas_id' => 'required|exists:data_kas,id',
                'keterangan' => 'nullable|string'
            ]);

            // Get anggota data
            $anggota = data_anggota::findOrFail($request->anggota_id);
            $barang = data_barang::findOrFail($request->barang_id);
            
            // Check stock - handle different field names
            $stok = $barang->stok ?? $barang->jml_brg ?? 0;
            
            if ($stok < $request->jumlah) {
                return redirect()->back()->with('error', 'Stok tidak mencukupi');
            }
            
            $transaksi = new TblTransToserda();
            $transaksi->tgl_transaksi = now();
            $transaksi->anggota_id = $request->anggota_id;
            $transaksi->jenis_id = $request->barang_id;
            $transaksi->jumlah = $request->jumlah;
            $transaksi->keterangan = $request->keterangan ?? 'Penjualan Toserda';
            $transaksi->dk = 'D';
            $transaksi->kas_id = $request->kas_id;
            $transaksi->user_name = Auth::user()->name;
            $transaksi->save();

            // Update stok barang - handle different field names
            if (isset($barang->stok)) {
            $barang->stok -= $request->jumlah;
            } else if (isset($barang->jml_brg)) {
                $barang->jml_brg -= $request->jumlah;
            }
            $barang->save();

            DB::commit();
            return redirect()->back()->with('success', 'Transaksi penjualan berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error in storePenjualan: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function storePembelian(Request $request)
    {
        try {
            DB::beginTransaction();
            
            $request->validate([
                'barang_id' => 'required|exists:data_barang,id',
                'jumlah' => 'required|numeric|min:1',
                'kas_id' => 'required|exists:data_kas,id',
                'keterangan' => 'nullable|string'
            ]);

            $transaksi = new TblTransToserda();
            $transaksi->tgl_transaksi = now();
            $transaksi->jenis_id = $request->barang_id;
            $transaksi->jumlah = $request->jumlah;
            $transaksi->keterangan = $request->keterangan ?? 'Pembelian Toserda';
            $transaksi->dk = 'K';
            $transaksi->kas_id = $request->kas_id;
            $transaksi->user_name = Auth::user()->name;
            $transaksi->save();

            // Update stok barang - handle different field names
            $barang = data_barang::findOrFail($request->barang_id);
            if (isset($barang->stok)) {
            $barang->stok += $request->jumlah;
            } else if (isset($barang->jml_brg)) {
                $barang->jml_brg += $request->jumlah;
            } else {
                // If neither field exists, create stok field
                $barang->stok = $request->jumlah;
            }
            $barang->save();

            DB::commit();
            return redirect()->back()->with('success', 'Transaksi pembelian berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error in storePembelian: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function storeBiayaUsaha(Request $request)
    {
        try {
            DB::beginTransaction();
            
            $request->validate([
                'keterangan' => 'required|string',
                'jumlah' => 'required|numeric|min:0',
                'kas_id' => 'required|exists:data_kas,id'
            ]);

            $transaksi = new TblTransToserda();
            $transaksi->tgl_transaksi = now();
            $transaksi->jumlah = $request->jumlah;
            $transaksi->keterangan = $request->keterangan;
            $transaksi->dk = 'K';
            $transaksi->kas_id = $request->kas_id;
            $transaksi->user_name = Auth::user()->name;
            $transaksi->save();

            DB::commit();
            return redirect()->back()->with('success', 'Biaya usaha berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error in storeBiayaUsaha: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    /**
     * Process monthly billing for Toserda transactions
     */
    public function processMonthlyBilling(Request $request)
    {
        try {
            $bulan = $request->bulan;
            $tahun = $request->tahun;
            
            // Get all transactions for the specified month and year that are not yet billed
            $transactions = TblTransToserda::whereMonth('tgl_transaksi', $bulan)
                ->whereYear('tgl_transaksi', $tahun)
                ->whereDoesntHave('billing', function($query) {
                    $query->where('jns_transaksi', 'toserda');
                })
                ->get();
            
            if ($transactions->isEmpty()) {
                return redirect()->back()->with('error', 'Tidak ada transaksi yang perlu diproses untuk periode ini.');
            }
            
            // Group transactions by no_ktp
            $transactionsByMember = $transactions->groupBy('no_ktp');
            $billingCount = 0;
            
            DB::beginTransaction();
            
            foreach ($transactionsByMember as $noKtp => $memberTransactions) {
                $anggota = data_anggota::where('no_ktp', $noKtp)->first();
                
                if (!$anggota) {
                    continue; // Skip if member not found
                }
                
                // Process each transaction
                foreach ($memberTransactions as $transaction) {
                    // Create billing entry
                    $billing = new billing();
                    $billing->no_ktp = $noKtp;
                    $billing->nama = $anggota->nama;
                    $billing->id_transaksi = $transaction->id;
                    $billing->jns_transaksi = 'toserda';
                    $billing->jumlah = $transaction->jumlah;
                    $billing->keterangan = $transaction->keterangan ?? 'Transaksi Toserda';
                    $billing->bulan = $bulan;
                    $billing->tahun = $tahun;
                    $billing->bulan_tahun = $bulan . '/' . $tahun;
                    $billing->status_bayar = 'belum';
                    $billing->billing_code = billing::generateBillingCode($bulan, $tahun, $noKtp, 'toserda');
                    $billing->save();
                    
                    $billingCount++;
            }
            }
            
            DB::commit();
            
            return redirect()->back()->with('success', "Berhasil memproses $billingCount transaksi billing untuk periode $bulan/$tahun.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Store uploaded Toserda transactions from Excel
     */
    public function storeUploadToserda(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
            'bulan' => 'required',
            'tahun' => 'required',
            'kas_id' => 'required',
        ]);

        try {
            $file = $request->file('file');
            $bulan = $request->bulan;
            $tahun = $request->tahun;
            $kas_id = $request->kas_id;
            
            // Check if kas_id exists in any of the possible tables
            $kasExistsInNamaKasTbl = DB::table('nama_kas_tbl')->where('id', $kas_id)->exists();
            $kasExistsInDataKas = DB::table('data_kas')->where('id', $kas_id)->exists();
            
            if (!$kasExistsInNamaKasTbl && !$kasExistsInDataKas) {
                return redirect()->back()->with('error', 'ID Kas tidak valid.');
            }
            
            // Import data from Excel with more detailed error handling
            try {
                // Create the import instance
                $import = new ToserdaImport($bulan, $tahun, $kas_id);
                
                // Import the file
                Excel::import($import, $file);
                
                $count = $import->getRowCount();
                $failures = $import->getFailures();
                
                if (!empty($failures)) {
                    $errorMessages = [];
                    foreach ($failures as $failure) {
                        $row = $failure->row();
                        $errors = $failure->errors();
                        $errorMessages[] = "Baris {$row}: " . implode(', ', $errors);
                    }
                    
                    // Log all failures
                    \Log::warning('Import validation failures:', $errorMessages);
                    
                    // Only show the first few errors to avoid overwhelming the user
                    $displayErrors = array_slice($errorMessages, 0, 5);
                    if (count($errorMessages) > 5) {
                        $displayErrors[] = "... dan " . (count($errorMessages) - 5) . " kesalahan lainnya";
                    }
                    
                    return redirect()->back()->with('error', 'Terjadi kesalahan validasi: ' . implode('<br>', $displayErrors));
                }
                
                return redirect()->back()->with('success', "Berhasil mengupload $count data transaksi Toserda.");
            } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
                $failures = $e->failures();
                $errorMessage = 'Terjadi kesalahan validasi: ';
                
                foreach ($failures as $failure) {
                    $errorMessage .= 'Baris ' . $failure->row() . ': ' . implode(', ', $failure->errors()) . '; ';
                }
                
                return redirect()->back()->with('error', $errorMessage);
            } catch (\Exception $e) {
                \Log::error('Error in Excel import: ' . $e->getMessage());
                \Log::error($e->getTraceAsString());
                return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            \Log::error('Error in storeUploadToserda: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Download Excel template for Toserda transactions
     */
    public function downloadTemplate()
    {
        $filePath = storage_path('app/templates/template_toserda.xlsx');
        
        if (!file_exists($filePath)) {
            // Create template if it doesn't exist
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set headers
            $sheet->setCellValue('A1', 'tanggal');
            $sheet->setCellValue('B1', 'no_ktp');
            $sheet->setCellValue('C1', 'nama');
            $sheet->setCellValue('D1', 'jumlah');
            $sheet->setCellValue('E1', 'keterangan');
            $sheet->setCellValue('F1', 'dk');
            $sheet->setCellValue('G1', 'jns_trans');
            
            // Add example data
            $sheet->setCellValue('A2', date('Y-m-d'));
            $sheet->setCellValue('B2', '1234567890123456');
            $sheet->setCellValue('C2', 'Nama Anggota');
            $sheet->setCellValue('D2', '100000');
            $sheet->setCellValue('E2', 'Belanja Toserda');
            $sheet->setCellValue('F2', 'D');
            $sheet->setCellValue('G2', '155');
            
            // Create directory if it doesn't exist
            if (!file_exists(storage_path('app/templates'))) {
                mkdir(storage_path('app/templates'), 0755, true);
            }
            
            // Save file
            $writer = new Xlsx($spreadsheet);
            $writer->save($filePath);
        }
        
        return response()->download($filePath, 'template_toserda.xlsx');
    }
    
    private function createInstructionsSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Petunjuk');
        
        $sheet->setCellValue('A1', 'PETUNJUK PENGISIAN TEMPLATE TOSERDA');
        $sheet->setCellValue('A3', 'Kolom');
        $sheet->setCellValue('B3', 'Keterangan');
        
        $instructions = [
            ['tanggal', 'Format: YYYY-MM-DD (contoh: 2023-05-15)'],
            ['no_ktp', 'Nomor KTP anggota (format teks)'],
            ['nama', 'Nama anggota'],
            ['jumlah', 'Nominal transaksi (angka)'],
            ['keterangan', 'Keterangan transaksi'],
            ['dk', 'D = Debit (penjualan), K = Kredit (pembelian)'],
            ['jns_trans', 'Jenis transaksi sesuai master data']
        ];
        
        $row = 4;
        foreach ($instructions as $instruction) {
            $sheet->setCellValue('A' . $row, $instruction[0]);
            $sheet->setCellValue('B' . $row, $instruction[1]);
            $row++;
        }
        
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(50);
    }
    
    private function createJenisAkunSheet($spreadsheet)
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Jenis Akun');
        
        $sheet->setCellValue('A1', 'DAFTAR JENIS AKUN');
        $sheet->setCellValue('A3', 'ID');
        $sheet->setCellValue('B3', 'Kode');
        $sheet->setCellValue('C3', 'Jenis Transaksi');
        $sheet->setCellValue('D3', 'Akun');
        
        $jnsAkunData = jns_akun::all();
        $row = 4;
        foreach ($jnsAkunData as $akun) {
            $sheet->setCellValue('A' . $row, $akun->id);
            $sheet->setCellValue('B' . $row, $akun->kd_aktiva);
            $sheet->setCellValue('C' . $row, $akun->jns_trans);
            $sheet->setCellValue('D' . $row, $akun->akun);
            $row++;
        }
        
        $sheet->getColumnDimension('A')->setWidth(10);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->getColumnDimension('D')->setWidth(40);
    }
} 