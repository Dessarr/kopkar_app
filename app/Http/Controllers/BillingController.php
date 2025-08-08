<?php

namespace App\Http\Controllers;

use App\Models\billing;
use App\Models\data_anggota;
use App\Models\jns_simpan;
use App\Models\jns_akun;
use App\Models\TblTransToserda;
use App\Models\TblTransSp;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\PDF;
use Carbon\Carbon;

class BillingController extends Controller
{
    // Define bulanList as class property
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
    
    public function index(Request $request)
    {
        try {
            $bulan = $request->input('bulan', date('m'));
            $tahun = $request->input('tahun', date('Y'));
            
            // Debug input values
            Log::info('Filtering billing with:', [
                'bulan' => $bulan,
                'tahun' => $tahun,
                'bulan_tahun' => $this->bulanList[$bulan] . ' ' . $tahun
            ]);
            
            // Always generate billing for the selected month and year (Simpanan)
            $result = $this->generateFullBilling($bulan, $tahun);
            
            if ($result['status'] === 'error') {
                return view('billing.billing', [
                    'error' => $result['message'],
                    'dataBilling' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10),
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                    'tahunList' => range(date('Y') - 5, date('Y') + 2),
                    'bulanList' => $this->bulanList
                ]);
            }
            
            // Base query for the newly generated billing data
            $query = billing::query();

            // Pencarian berdasarkan nama atau no KTP
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('nama', 'like', '%' . $search . '%')
                      ->orWhere('no_ktp', 'like', '%' . $search . '%');
                });
            }
            
            // Ambil data billing dengan pagination
            $dataBilling = $query->paginate(10);
            
            // Data untuk dropdown tahun (5 tahun ke belakang sampai 2 tahun ke depan)
            $tahunList = range(date('Y') - 5, date('Y') + 2);
            
            return view('billing.billing', [
                'dataBilling' => $dataBilling,
                'bulan' => $bulan,
                'tahun' => $tahun,
                'tahunList' => $tahunList,
                'bulanList' => $this->bulanList
            ]);
            
        } catch (\Exception $e) {
            //Log the error
            Log::error('Error in billing index: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            // Return empty paginator instead of empty collection
            return view('billing.billing', [
                'error' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'dataBilling' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10),
                'bulan' => $bulan ?? date('m'),
                'tahun' => $tahun ?? date('Y'),
                'tahunList' => range(date('Y') - 5, date('Y') + 2),
                'bulanList' => $this->bulanList
            ]);
        }
    }
    
    /**
     * Generate full billing untuk bulan dan tahun tertentu
     * Implementasi dari pseudocode yang diberikan
     */
    private function generateFullBilling($bulan_input, $tahun_input)
    {
        // Get current month and year
        $bulan_sekarang = date('m');
        $tahun_sekarang = date('Y');
        
        // Mengizinkan load semua bulan (sesuai kebutuhan revisi)
        
        // Format bulan tahun string
        $bulan_tahun_string = $this->bulanList[$bulan_input] . ' ' . $tahun_input;
        
        try {
            // 1. Hapus SEMUA data billing simpanan terlebih dahulu untuk menghindari penumpukan
            $deletedCount = billing::where('jns_trans', 'Simpanan')->delete();
            
            // Log untuk debugging
            Log::info("Deleted $deletedCount billing records (all simpanan) before generating for month $bulan_input/$tahun_input");
            
            // Mulai transaction untuk generate data baru
            DB::beginTransaction();
             
            // 2. Generate billing simpanan (wajib, sukarela, khusus_2) + simpanan pokok jika bulan daftar
            // Get active members
            $anggotaAktif = data_anggota::where('aktif', 'Y')->get();
            
            // Get simpanan settings from jns_simpan table
            $simpanan_pokok = jns_simpan::where('jns_simpan', 'Simpanan Pokok')->value('jumlah') ?? 100000;
            $simpanan_wajib = jns_simpan::where('jns_simpan', 'Simpanan Wajib')->value('jumlah') ?? 50000;
            
            // Get simpanan account ID
            $akunSimpanan = jns_akun::where('akun', 'like', '%Simpanan%')->first();
            $id_akun_simpanan = $akunSimpanan ? $akunSimpanan->id : 151; // Default to 151 if not found
            
            // Prepare data for bulk insert - SIMPANAN
            $billingData = [];
            
            foreach ($anggotaAktif as $anggota) {
                // Each member's monthly billing should be their individual savings amounts
                $simpanan_wajib = $anggota->simpanan_wajib ?? 0;
                $simpanan_sukarela = $anggota->simpanan_sukarela ?? 0;
                $simpanan_khusus_2 = $anggota->simpanan_khusus_2 ?? 0;
                $total_simpanan = $simpanan_wajib + $simpanan_sukarela + $simpanan_khusus_2;
                
                // Add simpanan pokok if this is the member's registration month
                $tambah_simpanan_pokok = 0;
                if ($anggota->tgl_daftar && $anggota->tgl_daftar != '0000-00-00') {
                    try {
                        $tgl_daftar = Carbon::parse($anggota->tgl_daftar);
                        $tambah_simpanan_pokok = ($bulan_input == $tgl_daftar->format('m') && $tahun_input == $tgl_daftar->format('Y'))
                            ? ($simpanan_pokok ?? 100000) : 0;
                    } catch (\Exception $e) {
                        // Jika error parsing tanggal, tidak tambah simpanan pokok
                        $tambah_simpanan_pokok = 0;
                    }
                }
                
                $total_simpanan += $tambah_simpanan_pokok;
                
                // Generate billing code
                $billing_code = "BILL-" . $tahun_input . $bulan_input . "-" . $anggota->no_ktp . "-SMPN";
                
                // Prepare data for bulk insert
                $billingData[] = [
                    'billing_code' => $billing_code,
                    'bulan_tahun' => $bulan_tahun_string,
                    'id_anggota' => $anggota->no_ktp,
                    'no_ktp' => $anggota->no_ktp,
                    'nama' => $anggota->nama,
                    'bulan' => $bulan_input,
                    'tahun' => $tahun_input,
                    'simpanan_wajib' => $simpanan_wajib,
                    'simpanan_sukarela' => $simpanan_sukarela,
                    'simpanan_khusus_2' => $simpanan_khusus_2,
                    'simpanan_pokok' => $tambah_simpanan_pokok,
                    'total_billing' => $total_simpanan,
                    'total_tagihan' => $total_simpanan,
                    'id_akun' => $id_akun_simpanan,
                    'status' => 'N',
                    'status_bayar' => 'Belum Lunas',
                    'jns_trans' => 'Simpanan',
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
            
            // Perform bulk insert in chunks to improve performance - SIMPANAN
            if (!empty($billingData)) {
                foreach (array_chunk($billingData, 100) as $chunk) {
                    billing::insert($chunk);
                }
            }
            
            // Tidak generate TOSERDA di sini (dipindah ke modul Toserda -> Billing Utama via process-all)
            
            DB::commit();
            
            return [
                'status' => 'success',
                'message' => 'Billing simpanan berhasil di-generate untuk ' . count($billingData) . ' anggota.'
            ];
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error generating billing: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return [
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat generate billing: ' . $e->getMessage()
            ];
        }
    }

    // Proses semua billing simpanan ke Billing Utama (tbl_trans_sp_bayar_temp)
    public function processAllToMain(Request $request)
    {
        $bulan = $request->input('bulan', date('m'));
        $tahun = $request->input('tahun', date('Y'));
        try {
            DB::beginTransaction();
            $billings = billing::where('jns_trans', 'Simpanan')
                ->where('bulan', $bulan)
                ->where('tahun', $tahun)
                ->get();

            foreach ($billings as $b) {
                // Upsert ke tbl_trans_sp_bayar_temp
                DB::table('tbl_trans_sp_bayar_temp')->updateOrInsert(
                    [
                        'tgl_transaksi' => Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->toDateString(),
                        'no_ktp' => $b->no_ktp,
                    ],
                    [
                        'anggota_id' => null,
                        'jumlah' => DB::raw('COALESCE(jumlah,0)'),
                        'keterangan' => 'Billing Simpanan ' . ($b->bulan_tahun ?? ($bulan.'-'.$tahun)),
                        'tagihan_simpanan_wajib' => $b->simpanan_wajib ?? 0,
                        'tagihan_simpanan_sukarela' => $b->simpanan_sukarela ?? 0,
                        'tagihan_simpanan_khusus_2' => $b->simpanan_khusus_2 ?? 0,
                        'tagihan_pinjaman' => DB::raw('COALESCE(tagihan_pinjaman,0)') ,
                        'tagihan_pinjaman_jasa' => DB::raw('COALESCE(tagihan_pinjaman_jasa,0)') ,
                        'tagihan_toserda' => DB::raw('COALESCE(tagihan_toserda,0)') ,
                        'total_tagihan_simpanan' => ($b->total_tagihan ?? $b->total_billing ?? 0),
                        'selisih' => DB::raw('COALESCE(selisih,0)'),
                        'saldo_simpanan_sukarela' => DB::raw('COALESCE(saldo_simpanan_sukarela,0)'),
                        'saldo_akhir_simpanan_sukarela' => DB::raw('COALESCE(saldo_akhir_simpanan_sukarela,0)'),
                    ]
                );
            }

            // Hapus billing simpanan bulan/tahun ini setelah dipindahkan
            billing::where('jns_trans', 'Simpanan')
                ->where('bulan', $bulan)
                ->where('tahun', $tahun)
                ->delete();

            DB::commit();
            return redirect()->route('billing.utama')->with('success', 'Berhasil memproses semua billing simpanan ke Billing Utama.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processAllToMain: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    public function processPayment($billing_code)
    {
        // Proses pembayaran billing
        try {
            // Find billing record first to avoid starting a transaction if not found
            // Try to find by billing_code first
            $billing = billing::where('billing_code', $billing_code)->first();
            
            // If not found, try to find by id
            if (!$billing) {
                $billing = billing::find($billing_code);
            }
            
            if (!$billing) {
                return redirect()->back()->with('error', 'Data billing tidak ditemukan');
            }
            
            // Now begin the transaction
            DB::beginTransaction();
            
            // Jika billing untuk simpanan, generate record di tbl_trans_sp
            if ($billing->jns_trans === 'Simpanan') {
                $this->generateSimpananRecords($billing);
            }
            
            // Create record in billing_process table
            $billingProcess = new \App\Models\BillingProcess();
            $billingProcess->fill($billing->toArray());
            $billingProcess->status_bayar = 'Lunas';
            $billingProcess->status = 'Y';
            $billingProcess->tgl_bayar = now();
            $billingProcess->save();
            
            // Delete from billing table
            $billing->delete();
            
            DB::commit();
            
            return redirect()->back()->with('success', 'Pembayaran berhasil diproses');
            
        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            Log::error('Error in processPayment: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    /**
     * Generate records di tbl_trans_sp berdasarkan billing simpanan
     */
    private function generateSimpananRecords($billing)
    {
        try {
            // Get anggota data
            $anggota = data_anggota::where('no_ktp', $billing->no_ktp)->first();
            if (!$anggota) {
                throw new \Exception('Data anggota tidak ditemukan');
            }
            
            // Get jenis simpanan dari jns_simpan
            $jenisSimpanan = jns_simpan::all();
            
            // Generate record untuk setiap jenis simpanan yang ada
            $records = [];
            
            // Simpanan Wajib
            if ($billing->simpanan_wajib > 0) {
                $jenisWajib = $jenisSimpanan->where('jns_simpan', 'Simpanan Wajib')->first();
                if ($jenisWajib) {
                    $records[] = [
                        'tgl_transaksi' => now(),
                        'no_ktp' => $billing->no_ktp,
                        'anggota_id' => $anggota->id,
                        'jenis_id' => $jenisWajib->id,
                        'jumlah' => $billing->simpanan_wajib,
                        'keterangan' => 'Setoran Simpanan Wajib - ' . $billing->bulan_tahun,
                        'akun' => 'Setoran',
                        'dk' => 'D',
                        'kas_id' => 1, // Default kas
                        'update_data' => now(),
                        'user_name' => 'admin',
                        'nama_penyetor' => $anggota->nama,
                        'no_identitas' => $anggota->no_ktp,
                        'alamat' => $anggota->alamat,
                        'id_cabang' => $anggota->id_cabang ?? 1
                    ];
                }
            }
            
            // Simpanan Sukarela
            if ($billing->simpanan_sukarela > 0) {
                $jenisSukarela = $jenisSimpanan->where('jns_simpan', 'Simpanan Sukarela')->first();
                if ($jenisSukarela) {
                    $records[] = [
                        'tgl_transaksi' => now(),
                        'no_ktp' => $billing->no_ktp,
                        'anggota_id' => $anggota->id,
                        'jenis_id' => $jenisSukarela->id,
                        'jumlah' => $billing->simpanan_sukarela,
                        'keterangan' => 'Setoran Simpanan Sukarela - ' . $billing->bulan_tahun,
                        'akun' => 'Setoran',
                        'dk' => 'D',
                        'kas_id' => 1, // Default kas
                        'update_data' => now(),
                        'user_name' => 'admin',
                        'nama_penyetor' => $anggota->nama,
                        'no_identitas' => $anggota->no_ktp,
                        'alamat' => $anggota->alamat,
                        'id_cabang' => $anggota->id_cabang ?? 1
                    ];
                }
            }
            
            // Simpanan Khusus 2
            if ($billing->simpanan_khusus_2 > 0) {
                $jenisKhusus = $jenisSimpanan->where('jns_simpan', 'Simpanan Khusus 2')->first();
                if ($jenisKhusus) {
                    $records[] = [
                        'tgl_transaksi' => now(),
                        'no_ktp' => $billing->no_ktp,
                        'anggota_id' => $anggota->id,
                        'jenis_id' => $jenisKhusus->id,
                        'jumlah' => $billing->simpanan_khusus_2,
                        'keterangan' => 'Setoran Simpanan Khusus 2 - ' . $billing->bulan_tahun,
                        'akun' => 'Setoran',
                        'dk' => 'D',
                        'kas_id' => 1, // Default kas
                        'update_data' => now(),
                        'user_name' => 'admin',
                        'nama_penyetor' => $anggota->nama,
                        'no_identitas' => $anggota->no_ktp,
                        'alamat' => $anggota->alamat,
                        'id_cabang' => $anggota->id_cabang ?? 1
                    ];
                }
            }
            
            // Simpanan Pokok
            if ($billing->simpanan_pokok > 0) {
                $jenisPokok = $jenisSimpanan->where('jns_simpan', 'Simpanan Pokok')->first();
                if ($jenisPokok) {
                    $records[] = [
                        'tgl_transaksi' => now(),
                        'no_ktp' => $billing->no_ktp,
                        'anggota_id' => $anggota->id,
                        'jenis_id' => $jenisPokok->id,
                        'jumlah' => $billing->simpanan_pokok,
                        'keterangan' => 'Setoran Simpanan Pokok - ' . $billing->bulan_tahun,
                        'akun' => 'Setoran',
                        'dk' => 'D',
                        'kas_id' => 1, // Default kas
                        'update_data' => now(),
                        'user_name' => 'admin',
                        'nama_penyetor' => $anggota->nama,
                        'no_identitas' => $anggota->no_ktp,
                        'alamat' => $anggota->alamat,
                        'id_cabang' => $anggota->id_cabang ?? 1
                    ];
                }
            }
            
            // Insert records ke tbl_trans_sp
            if (!empty($records)) {
                foreach ($records as $record) {
                    TblTransSp::create($record);
                }
            }
            
            Log::info('Generated ' . count($records) . ' simpanan records for billing: ' . $billing->billing_code);
            
        } catch (\Exception $e) {
            Log::error('Error generating simpanan records: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Cancel a processed payment and move it back to billing table
     */
    public function cancelPayment($billing_process_id)
    {
        try {
            // Find the processed billing first before starting transaction
            $processedBilling = \App\Models\BillingProcess::find($billing_process_id);
            
            if (!$processedBilling) {
                return redirect()->back()->with('error', 'Data pembayaran tidak ditemukan');
            }
            
            // Check if the billing already exists in billing table before starting transaction
            $billingExists = billing::where('billing_code', $processedBilling->billing_code)
                ->orWhere(function($query) use ($processedBilling) {
                    $query->where('no_ktp', $processedBilling->no_ktp)
                          ->where('bulan', $processedBilling->bulan)
                          ->where('tahun', $processedBilling->tahun);
                })
                ->exists();
            
            if ($billingExists) {
                return redirect()->back()->with('error', 'Billing sudah ada di daftar tagihan aktif');
            }
            
            // Now start the transaction
            DB::beginTransaction();
            
            // Create a new billing record
            $billing = new billing();
            $billing->fill($processedBilling->toArray());
            $billing->status_bayar = 'Belum Lunas';
            $billing->status = 'N';
            $billing->save();
            
            // Delete the processed billing
            $processedBilling->delete();
            
            DB::commit();
            
            return redirect()->back()->with('success', 'Pembayaran berhasil dibatalkan dan dikembalikan ke daftar tagihan');
        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            Log::error('Error in cancelPayment: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    public function exportExcel(Request $request)
    {
        // Buat spreadsheet baru
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set judul kolom
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'ID Billing');
        $sheet->setCellValue('C1', 'No KTP');
        $sheet->setCellValue('D1', 'Nama');
        $sheet->setCellValue('E1', 'Bulan');
        $sheet->setCellValue('F1', 'Tahun');
        $sheet->setCellValue('G1', 'Jenis Transaksi');
        $sheet->setCellValue('H1', 'Total Tagihan');
        $sheet->setCellValue('I1', 'Status');
        
        // Format header dengan styling
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '14AE5C'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
        
        $sheet->getStyle('A1:I1')->applyFromArray($headerStyle);
        
        // Set lebar kolom
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(25);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(20);
        $sheet->getColumnDimension('H')->setWidth(15);
        $sheet->getColumnDimension('I')->setWidth(15);
        
        // Query data billing
        $query = billing::query();
        
        // Pencarian berdasarkan nama atau no KTP
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('no_ktp', 'like', '%' . $search . '%');
            });
        }
        
        // Filter berdasarkan bulan dan tahun jika ada
        if ($request->has('bulan') && $request->has('tahun')) {
            $query->where(function($q) use ($request) {
                $q->where(function($q2) use ($request) {
                    $q2->where('bulan', $request->bulan)
                       ->where('tahun', $request->tahun);
                })
                ->orWhere('bulan_tahun', 'like', '%' . $this->bulanList[$request->bulan] . ' ' . $request->tahun . '%');
            });
        }
        
        $dataBilling = $query->get();
        
        // Isi data
        $row = 2;
        $totalBilling = 0;
        foreach ($dataBilling as $index => $item) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $item->billing_code);
            $sheet->setCellValue('C' . $row, $item->no_ktp);
            $sheet->setCellValue('D' . $row, $item->nama);
            $sheet->setCellValue('E' . $row, $item->bulan);
            $sheet->setCellValue('F' . $row, $item->tahun);
            $sheet->setCellValue('G' . $row, $item->jns_trans ?? 'Billing');
            $sheet->setCellValue('H' . $row, $item->total_tagihan ?? $item->total_billing ?? 0);
            $sheet->setCellValue('I' . $row, ($item->status_bayar == 'Lunas' || $item->status == 'Y') ? 'Lunas' : 'Belum Lunas');
            
            // Format angka untuk kolom nominal
            $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode('#,##0');
            
            $totalBilling += $item->total_tagihan ?? $item->total_billing ?? 0;
            $row++;
        }
        
        // Tambahkan total keseluruhan
        $sheet->setCellValue('A' . $row, '');
        $sheet->setCellValue('B' . $row, '');
        $sheet->setCellValue('C' . $row, '');
        $sheet->setCellValue('D' . $row, '');
        $sheet->setCellValue('E' . $row, '');
        $sheet->setCellValue('F' . $row, '');
        $sheet->setCellValue('G' . $row, 'TOTAL');
        $sheet->setCellValue('H' . $row, $totalBilling);
        $sheet->setCellValue('I' . $row, '');
        
        // Format total
        $sheet->getStyle('G' . $row . ':H' . $row)->getFont()->setBold(true);
        $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode('#,##0');
        
        // Set style untuk semua data
        $dataStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
        
        $sheet->getStyle('A2:I' . $row)->applyFromArray($dataStyle);
        
        // Buat file Excel
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        // Set header untuk download
        $filename = 'billing_anggota_' . date('Ymd') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        // Tulis ke output
        $writer->save('php://output');
        exit;
    }
    
    public function exportPdf(Request $request)
    {
        // Query data billing
        $query = billing::query();
        
        // Pencarian berdasarkan nama atau no KTP
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('no_ktp', 'like', '%' . $search . '%');
            });
        }
        
        // Filter berdasarkan bulan dan tahun jika ada
        if ($request->has('bulan') && $request->has('tahun')) {
            $query->where(function($q) use ($request) {
                $q->where(function($q2) use ($request) {
                    $q2->where('bulan', $request->bulan)
                       ->where('tahun', $request->tahun);
                })
                ->orWhere('bulan_tahun', 'like', '%' . $this->bulanList[$request->bulan] . ' ' . $request->tahun . '%');
            });
        }
        
        $dataBilling = $query->get();
        
        // Load view untuk PDF
        $pdf = PDF::loadView('billing.pdf', compact('dataBilling'));
        
        // Download PDF
        return $pdf->download('billing_anggota_' . date('Ymd') . '.pdf');
    }
    
    /**
     * Display processed billing records.
     */
    public function processed(Request $request)
    {
        try {
            $bulan = $request->input('bulan', date('m'));
            $tahun = $request->input('tahun', date('Y'));
            
            // Base query
            $query = \App\Models\BillingProcess::query();

            // Pencarian berdasarkan nama atau no KTP
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('nama', 'like', '%' . $search . '%')
                      ->orWhere('no_ktp', 'like', '%' . $search . '%');
                });
            }
            
            // Filter berdasarkan bulan dan tahun
            if ($bulan && $tahun) {
                $query->where(function($q) use ($bulan, $tahun) {
                    $q->where(function($q2) use ($bulan, $tahun) {
                        $q2->where('bulan', $bulan)
                           ->where('tahun', $tahun);
                    })
                    ->orWhere('bulan_tahun', 'like', '%' . $this->bulanList[$bulan] . ' ' . $tahun . '%');
                });
            }
            
            // Ambil data billing dengan pagination
            $dataBillingProcess = $query->paginate(10);
            
            // Data untuk dropdown tahun (5 tahun ke belakang sampai 2 tahun ke depan)
            $tahunList = range(date('Y') - 5, date('Y') + 2);
            
            return view('billing.processed', [
                'dataBillingProcess' => $dataBillingProcess,
                'bulan' => $bulan,
                'tahun' => $tahun,
                'tahunList' => $tahunList,
                'bulanList' => $this->bulanList
            ]);
            
        } catch (\Exception $e) {
            // Log the error
            Log::error('Error in processed billings: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            // Return empty paginator instead of empty collection
            return view('billing.processed', [
                'error' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'dataBillingProcess' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10),
                'bulan' => $bulan ?? date('m'),
                'tahun' => $tahun ?? date('Y'),
                'tahunList' => range(date('Y') - 5, date('Y') + 2),
                'bulanList' => $this->bulanList
            ]);
        }
    }
    
    /**
     * Export processed billings to Excel
     */
    public function exportProcessedExcel(Request $request)
    {
        // Buat spreadsheet baru
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set judul kolom
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'ID Billing');
        $sheet->setCellValue('C1', 'No KTP');
        $sheet->setCellValue('D1', 'Nama');
        $sheet->setCellValue('E1', 'Bulan');
        $sheet->setCellValue('F1', 'Tahun');
        $sheet->setCellValue('G1', 'Jenis Transaksi');
        $sheet->setCellValue('H1', 'Total Tagihan');
        $sheet->setCellValue('I1', 'Tanggal Bayar');
        
        // Format header dengan styling
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '14AE5C'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
        
        $sheet->getStyle('A1:I1')->applyFromArray($headerStyle);
        
        // Set lebar kolom
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(25);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(20);
        $sheet->getColumnDimension('H')->setWidth(15);
        $sheet->getColumnDimension('I')->setWidth(15);
        
        // Query data billing
        $query = \App\Models\BillingProcess::query();
        
        // Pencarian berdasarkan nama atau no KTP
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('no_ktp', 'like', '%' . $search . '%');
            });
        }
        
        // Filter berdasarkan bulan dan tahun jika ada
        if ($request->has('bulan') && $request->has('tahun')) {
            $query->where(function($q) use ($request) {
                $q->where(function($q2) use ($request) {
                    $q2->where('bulan', $request->bulan)
                       ->where('tahun', $request->tahun);
                })
                ->orWhere('bulan_tahun', 'like', '%' . $this->bulanList[$request->bulan] . ' ' . $request->tahun . '%');
            });
        }
        
        $dataBillingProcess = $query->get();
        
        // Isi data
        $row = 2;
        $totalBilling = 0;
        foreach ($dataBillingProcess as $index => $item) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $item->billing_code);
            $sheet->setCellValue('C' . $row, $item->no_ktp);
            $sheet->setCellValue('D' . $row, $item->nama);
            $sheet->setCellValue('E' . $row, $item->bulan);
            $sheet->setCellValue('F' . $row, $item->tahun);
            $sheet->setCellValue('G' . $row, $item->jns_trans ?? 'Billing');
            $sheet->setCellValue('H' . $row, $item->total_tagihan ?? $item->total_billing ?? 0);
            $sheet->setCellValue('I' . $row, $item->tgl_bayar ? $item->tgl_bayar->format('d/m/Y') : '-');
            
            // Format angka untuk kolom nominal
            $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode('#,##0');
            
            $totalBilling += $item->total_tagihan ?? $item->total_billing ?? 0;
            $row++;
        }
        
        // Tambahkan total di baris terakhir
        $sheet->setCellValue('G' . $row, 'TOTAL');
        $sheet->setCellValue('H' . $row, $totalBilling);
        $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('G' . $row . ':H' . $row)->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F2F2F2'],
            ],
        ]);
        
        // Buat writer
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'billing_lunas_' . date('Ymd') . '.xlsx';
        
        // Simpan ke file sementara
        $temp_file = tempnam(sys_get_temp_dir(), $filename);
        $writer->save($temp_file);
        
        // Return response download
        return response()->download($temp_file, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
    
    /**
     * Export processed billings to PDF
     */
    public function exportProcessedPdf(Request $request)
    {
        // Query data billing
        $query = \App\Models\BillingProcess::query();
        
        // Pencarian berdasarkan nama atau no KTP
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('no_ktp', 'like', '%' . $search . '%');
            });
        }
        
        // Filter berdasarkan bulan dan tahun jika ada
        if ($request->has('bulan') && $request->has('tahun')) {
            $query->where(function($q) use ($request) {
                $q->where(function($q2) use ($request) {
                    $q2->where('bulan', $request->bulan)
                       ->where('tahun', $request->tahun);
                })
                ->orWhere('bulan_tahun', 'like', '%' . $this->bulanList[$request->bulan] . ' ' . $request->tahun . '%');
            });
        }
        
        $dataBillingProcess = $query->get();
        
        // Load view untuk PDF
        $pdf = PDF::loadView('billing.pdf_processed', compact('dataBillingProcess'));
        
        // Download PDF
        return $pdf->download('billing_lunas_' . date('Ymd') . '.pdf');
    }
}