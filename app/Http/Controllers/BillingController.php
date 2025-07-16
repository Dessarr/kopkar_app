<?php

namespace App\Http\Controllers;

use App\Models\billing;
use App\Models\data_anggota;
use App\Models\jns_simpan;
use App\Models\jns_akun;
use App\Models\TblTransToserda;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\PDF;

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
            \Log::info('Filtering billing with:', [
                'bulan' => $bulan,
                'tahun' => $tahun,
                'bulan_tahun' => $this->bulanList[$bulan] . ' ' . $tahun
            ]);
            
            // Base query
            $query = billing::query();

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
            
            // Debug the generated SQL query
            \Log::info('Generated SQL:', [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings()
            ]);
            
            // Cek apakah billing untuk bulan dan tahun ini sudah ada
            $billingExists = $query->count() > 0;
            
            // Debug billing existence
            \Log::info('Billing exists check:', [
                'exists' => $billingExists
            ]);
            
            // Jika belum ada, generate billing baru
            if (!$billingExists) {
                $result = $this->generateFullBilling($bulan, $tahun);
                
                // Debug generation result
                \Log::info('Generation result:', $result);
                
                if ($result['status'] === 'error') {
                    // Return empty paginator instead of empty collection
                    return view('billing.billing', [
                        'error' => $result['message'],
                        'dataBilling' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10),
                        'bulan' => $bulan,
                        'tahun' => $tahun,
                        'tahunList' => range(date('Y') - 5, date('Y') + 2),
                        'bulanList' => $this->bulanList
                    ]);
                }
                
                // Refresh query setelah generate
                $query = billing::query();
                if ($bulan && $tahun) {
                    $query->where(function($q) use ($bulan, $tahun) {
                        $q->where(function($q2) use ($bulan, $tahun) {
                            $q2->where('bulan', $bulan)
                               ->where('tahun', $tahun);
                        })
                        ->orWhere('bulan_tahun', 'like', '%' . $this->bulanList[$bulan] . ' ' . $tahun . '%');
                    });
                }
            }
            
            // Ambil data billing dengan pagination
            $dataBilling = $query->paginate(10);
            
            // Debug final result count
            \Log::info('Final result count:', [
                'total' => $dataBilling->total()
            ]);
            
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
            // Log the error
            \Log::error('Error in billing index: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
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
        
        // Check if trying to process past billing
        if ($tahun_input < $tahun_sekarang || ($tahun_input == $tahun_sekarang && $bulan_input < $bulan_sekarang)) {
            return [
                'status' => 'error',
                'message' => 'Tidak bisa memproses tagihan masa lalu'
            ];
        }
        
        // Format bulan tahun string
        $bulan_tahun_string = $this->bulanList[$bulan_input] . ' ' . $tahun_input;
        
        try {
            DB::beginTransaction();
            
            // ============================
            // 1. GENERATE BILLING SIMPANAN
            // ============================
            
            // Check if billing simpanan already exists
            $billingSimpananExists = billing::where('bulan_tahun', $bulan_tahun_string)
                ->whereRaw("billing_code LIKE '%SMPN'")
                ->exists();
                
            if (!$billingSimpananExists) {
                // Get active members
                $anggotaAktif = data_anggota::where('aktif', 'Y')
                    ->select([
                        'no_ktp',
                        'nama',
                        'tgl_daftar',
                        'simpanan_wajib',
                        'simpanan_sukarela',
                        'simpanan_khusus_2'
                    ])
                    ->get();
                
                // Debug: Print member data
                foreach ($anggotaAktif as $anggota) {
                    \Log::info("Member data for {$anggota->nama}:", [
                        'simpanan_wajib' => $anggota->simpanan_wajib,
                        'simpanan_sukarela' => $anggota->simpanan_sukarela,
                        'simpanan_khusus_2' => $anggota->simpanan_khusus_2
                    ]);
                }
                
                // Get simpanan pokok amount
                $jnsSimpanPokok = jns_simpan::where('jns_simpan', 'Pokok')->first();
                $simpanan_pokok = $jnsSimpanPokok ? $jnsSimpanPokok->jumlah : 100000; // Default to 100k if not found
                
                // Get simpanan account ID
                $akunSimpanan = jns_akun::where('akun', 'like', '%Simpanan%')->first();
                $id_akun_simpanan = $akunSimpanan ? $akunSimpanan->id : 1; // Default to 1 if not found
                
                foreach ($anggotaAktif as $anggota) {
                    // Each member's monthly billing should be their individual savings amounts
                    $total_simpanan = $anggota->simpanan_wajib + 
                                    $anggota->simpanan_sukarela + 
                                    $anggota->simpanan_khusus_2;
                    
                    // Add simpanan pokok if this is the member's registration month
                    $tgl_daftar = Carbon::parse($anggota->tgl_daftar);
                    if ($bulan_input == $tgl_daftar->format('m') && $tahun_input == $tgl_daftar->format('Y')) {
                        $total_simpanan += $simpanan_pokok;
                    }
                    
                    // Create billing record
                    $biliing_code = "BILL-" . $tahun_input . $bulan_input . "-" . $anggota->no_ktp . "-SMPN";
                    
                    billing::create([
                        'biliing_code' => $biliing_code,
                        'bulan_tahun' => $bulan_tahun_string,
                        'id_anggota' => $anggota->no_ktp,
                        'no_ktp' => $anggota->no_ktp,
                        'nama' => $anggota->nama,
                        'bulan' => $bulan_input,
                        'tahun' => $tahun_input,
                        'simpanan_wajib' => $anggota->simpanan_wajib,
                        'simpanan_sukarela' => $anggota->simpanan_sukarela,
                        'simpanan_khusus_2' => $anggota->simpanan_khusus_2,
                        'simpanan_pokok' => ($bulan_input == $tgl_daftar->format('m') && $tahun_input == $tgl_daftar->format('Y')) ? $simpanan_pokok : 0,
                        'total_billing' => $total_simpanan,
                        'total_tagihan' => $total_simpanan,
                        'id_akun' => $id_akun_simpanan,
                        'status' => 'N',
                        'status_bayar' => 'Belum Lunas',
                        'jns_trans' => 'Simpanan'
                    ]);
                }
            }
            
            // ========================
            // 2. GENERATE BILLING TOSERDA
            // ========================
            
            // Check if billing toserda already exists
            $billingToserdaExists = billing::where('bulan_tahun', $bulan_tahun_string)
                ->whereRaw("billing_code LIKE '%TSD'")
                ->exists();
                
            if (!$billingToserdaExists) {
                // Get toserda account ID
                $akunToserda = jns_akun::where('akun', 'like', '%Toserda%')->first();
                $id_akun_toserda = $akunToserda ? $akunToserda->id : 155; // Default to 155 if not found
                
                // Get toserda transactions for the month
                $transaksiToserda = DB::table('tbl_trans_toserda')
                    ->select('no_ktp', DB::raw('SUM(jumlah) as total_belanja'))
                    ->whereMonth('tgl_transaksi', $bulan_input)
                    ->whereYear('tgl_transaksi', $tahun_input)
                    ->where('dk', 'D') // Only debit transactions (sales)
                    ->whereNotNull('no_ktp')
                    ->groupBy('no_ktp')
                    ->get();
                
                foreach ($transaksiToserda as $transaksi) {
                    // Get member data
                    $anggota = data_anggota::where('no_ktp', $transaksi->no_ktp)->first();
                    if (!$anggota) continue;
                    
                    // Create billing record
                    $biliing_code = "BILL-" . $tahun_input . $bulan_input . "-" . $transaksi->no_ktp . "-TSD";
                    
                    billing::create([
                        'biliing_code' => $biliing_code,
                        'bulan_tahun' => $bulan_tahun_string,
                        'id_anggota' => $transaksi->no_ktp,
                        'no_ktp' => $transaksi->no_ktp,
                        'nama' => $anggota->nama,
                        'bulan' => $bulan_input,
                        'tahun' => $tahun_input,
                        'simpanan_wajib' => 0,
                        'simpanan_sukarela' => 0,
                        'simpanan_khusus_2' => $transaksi->total_belanja,
                        'simpanan_pokok' => 0,
                        'total_billing' => $transaksi->total_belanja,
                        'total_tagihan' => $transaksi->total_belanja,
                        'id_akun' => $id_akun_toserda,
                        'status' => 'N',
                        'status_bayar' => 'Belum Lunas',
                        'jns_trans' => 'Toserda'
                    ]);
                }
            }
            
            DB::commit();
            
            return [
                'status' => 'success',
                'message' => 'Billing berhasil digenerate'
            ];
            
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error generating billing: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return [
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat generate billing: ' . $e->getMessage()
            ];
        }
    }
    
    public function processPayment($biliing_code)
    {
        // Proses pembayaran billing
        try {
            // Try to find by biliing_code first
            $billing = billing::where('biliing_code', $biliing_code)->first();
            
            // If not found, try to find by id
            if (!$billing) {
                $billing = billing::find($biliing_code);
            }
            
            if (!$billing) {
                return redirect()->back()->with('error', 'Data billing tidak ditemukan');
            }
            
            // Update status menjadi 'Lunas'
            $billing->status_bayar = 'Lunas';
            $billing->status = 'Y';
            $billing->save();
            
            return redirect()->back()->with('success', 'Pembayaran berhasil diproses');
        } catch (\Exception $e) {
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
            $sheet->setCellValue('B' . $row, $item->biliing_code);
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
     * Public method to generate billing from routes or other controllers
     */
    public function generateBillingForPeriod($bulan, $tahun)
    {
        $result = $this->generateFullBilling($bulan, $tahun);
        
        if ($result['status'] === 'error') {
            return redirect()->route('billing.index')->with('error', $result['message']);
        }
        
        return redirect()->route('billing.index', ['bulan' => $bulan, 'tahun' => $tahun])
                         ->with('success', 'Billing berhasil dibuat untuk periode ' . $this->bulanList[$bulan] . ' ' . $tahun);
    }
}