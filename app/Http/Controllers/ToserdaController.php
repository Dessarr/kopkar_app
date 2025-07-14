<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TblTransToserda;
use App\Models\data_barang;
use App\Models\data_anggota;
use App\Models\DataKas;
use App\Models\View_Toserda;
use App\Models\View_LapToserda;
use App\Models\billing;
use App\Models\jns_akun;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

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

    public function penjualan()
    {
        try {
            $barang = data_barang::where('stok', '>', 0)->get();
            $anggota = data_anggota::where('aktif', 'Y')->get();
            $kas = DataKas::all();
            return view('toserda.penjualan', compact('barang', 'anggota', 'kas'));
        } catch (\Exception $e) {
            \Log::error('Error in penjualan: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function pembelian()
    {
        try {
            $barang = data_barang::all();
            $kas = DataKas::all();
            return view('toserda.pembelian', compact('barang', 'kas'));
        } catch (\Exception $e) {
            \Log::error('Error in pembelian: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function biayaUsaha()
    {
        try {
            $kas = DataKas::all();
            return view('toserda.biaya_usaha', compact('kas'));
        } catch (\Exception $e) {
            \Log::error('Error in biayaUsaha: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    public function lainLain(Request $request)
    {
        try {
            // Base query with eager loading of relationships
            $query = View_Toserda::with(['anggota', 'kas', 'billing']);
            
            // Filter berdasarkan bulan
            if ($request->has('bulan') && $request->bulan !== '') {
                $query->whereMonth('tgl_transaksi', $request->bulan);
            }
            
            // Filter berdasarkan tahun
            if ($request->has('tahun') && $request->tahun !== '') {
                $query->whereYear('tgl_transaksi', $request->tahun);
            }
            
            // Filter berdasarkan pencarian
            if ($request->has('search') && $request->search !== '') {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->whereHas('anggota', function($q) use ($search) {
                        $q->where('nama', 'like', '%' . $search . '%');
                    })
                    ->orWhere('no_ktp', 'like', '%' . $search . '%');
                });
            }

            // Filter berdasarkan status billing
            if ($request->has('billing_status') && $request->billing_status !== '') {
                if ($request->billing_status === 'billed') {
                    $query->whereHas('billing');
                } else if ($request->billing_status === 'unbilled') {
                    $query->whereDoesntHave('billing');
                }
            }
            
            $transaksi = $query->orderBy('tgl_transaksi', 'desc')->paginate(10);
            
            // Data untuk dropdown
            $kas = DataKas::all();
            $tahunList = range(date('Y') - 5, date('Y') + 2);
            $bulanList = $this->bulanList;
            
            return view('toserda.lain_lain', compact('transaksi', 'kas', 'tahunList', 'bulanList'));
        } catch (\Exception $e) {
            \Log::error('Error in lainLain: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function storePenjualan(Request $request)
    {
        try {
            DB::beginTransaction();
            
            $request->validate([
                'barang_id' => 'required|exists:data_barang,id',
                'anggota_id' => 'required|exists:data_anggota,id',
                'jumlah' => 'required|numeric|min:1',
                'kas_id' => 'required|exists:nama_kas_tbl,id',
                'keterangan' => 'nullable|string'
            ]);

            // Get anggota data
            $anggota = data_anggota::findOrFail($request->anggota_id);
            $barang = data_barang::findOrFail($request->barang_id);
            
            // Check stock
            if ($barang->stok < $request->jumlah) {
                return redirect()->back()->with('error', 'Stok tidak mencukupi');
            }
            
            $transaksi = new TblTransToserda();
            $transaksi->tgl_transaksi = now();
            $transaksi->no_ktp = $anggota->no_ktp;
            $transaksi->anggota_id = $request->anggota_id;
            $transaksi->jenis_id = $request->barang_id;
            $transaksi->jumlah = $request->jumlah;
            $transaksi->keterangan = $request->keterangan ?? 'Penjualan Toserda';
            $transaksi->dk = 'D';
            $transaksi->kas_id = $request->kas_id;
            $transaksi->user_name = Auth::user()->name;
            $transaksi->save();

            // Update stok barang
            $barang->stok -= $request->jumlah;
            $barang->save();

            DB::commit();
            return redirect()->back()->with('success', 'Transaksi penjualan berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error in storePenjualan: ' . $e->getMessage());
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
                'kas_id' => 'required|exists:nama_kas_tbl,id',
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

            // Update stok barang
            $barang = data_barang::findOrFail($request->barang_id);
            $barang->stok += $request->jumlah;
            $barang->save();

            DB::commit();
            return redirect()->back()->with('success', 'Transaksi pembelian berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error in storePembelian: ' . $e->getMessage());
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
                'kas_id' => 'required|exists:nama_kas_tbl,id'
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
    
    public function storeUploadToserda(Request $request)
    {
        try {
            DB::beginTransaction();
            
            $request->validate([
                'file' => 'required|mimes:xlsx,xls|max:2048',
                'bulan' => 'required|string',
                'tahun' => 'required|numeric',
                'kas_id' => 'required|exists:nama_kas_tbl,id',
            ]);
            
            // Get Toserda account ID
            $jnsAkun = jns_akun::where('akun', 'like', '%Toserda%')->first();
            if (!$jnsAkun) {
                throw new \Exception('Jenis akun Toserda tidak ditemukan');
            }
            
            // Import data
            $file = $request->file('file');
            $import = new \App\Imports\ToserdaImport($request->kas_id);
            Excel::import($import, $file);
            
            // Generate billing for the month
            $billingController = new BillingController();
            $result = $billingController->generateBillingForPeriod($request->bulan, $request->tahun);
            
            DB::commit();
            return redirect()->back()->with('success', 'Data berhasil diupload dan billing telah digenerate');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            DB::rollback();
            $failures = $e->failures();
            $errorMessages = [];
            
            foreach ($failures as $failure) {
                $errorMessages[] = "Baris ke-" . $failure->row() . ": " . implode(', ', $failure->errors());
            }
            
            return redirect()->back()->with('error', 'Error validasi: ' . implode('<br>', $errorMessages));
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error upload Toserda: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
    
    public function processMonthlyBilling(Request $request)
    {
        try {
            $request->validate([
                'bulan' => 'required|string',
                'tahun' => 'required|numeric',
            ]);
            
            $billingController = new BillingController();
            return $billingController->generateBillingForPeriod($request->bulan, $request->tahun);
        } catch (\Exception $e) {
            \Log::error('Error in processMonthlyBilling: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set judul kolom
            $sheet->setCellValue('A1', 'tanggal');
            $sheet->setCellValue('B1', 'no_ktp');
            $sheet->setCellValue('C1', 'nama');
            $sheet->setCellValue('D1', 'jumlah');
            $sheet->setCellValue('E1', 'keterangan');
            $sheet->setCellValue('F1', 'dk');
            $sheet->setCellValue('G1', 'jns_trans');
            
            // Format header
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
            
            $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);
            
            // Set column widths
            $sheet->getColumnDimension('A')->setWidth(15);
            $sheet->getColumnDimension('B')->setWidth(20);
            $sheet->getColumnDimension('C')->setWidth(25);
            $sheet->getColumnDimension('D')->setWidth(15);
            $sheet->getColumnDimension('E')->setWidth(30);
            $sheet->getColumnDimension('F')->setWidth(10);
            $sheet->getColumnDimension('G')->setWidth(15);
            
            // Get jns_akun list
            $jnsAkunList = jns_akun::pluck('jns_trans')->toArray();
            $jnsAkunString = implode(',', $jnsAkunList);
            
            // Default Toserda account
            $defaultJnsAkun = jns_akun::where('akun', 'like', '%Toserda%')->first();
            $defaultJnsTrans = $defaultJnsAkun ? $defaultJnsAkun->jns_trans : 'Toserda';
            
            // Add example data
            $sheet->setCellValue('A2', date('Y-m-d'));
            $sheet->setCellValue('B2', '3201234567890001');
            $sheet->setCellValue('C2', 'Contoh Nama');
            $sheet->setCellValue('D2', '50000');
            $sheet->setCellValue('E2', 'Pembelian Sembako');
            $sheet->setCellValue('F2', 'D');
            $sheet->setCellValue('G2', $defaultJnsTrans);
            
            // Format date column
            $sheet->getStyle('A2:A1000')->getNumberFormat()->setFormatCode('yyyy-mm-dd');
            
            // Format no_ktp column as text
            $sheet->getStyle('B2:B1000')->getNumberFormat()->setFormatCode('@');
            
            // Add data validation for dk column
            $validation = $sheet->getCell('F2')->getDataValidation();
            $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
            $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
            $validation->setAllowBlank(false);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setFormula1('"D,K"');
            $sheet->setDataValidation('F2:F1000', $validation);
            
            // Add data validation for jns_trans column
            if (!empty($jnsAkunString)) {
                $jnsTransValidation = $sheet->getCell('G2')->getDataValidation();
                $jnsTransValidation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
                $jnsTransValidation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
                $jnsTransValidation->setAllowBlank(true);
                $jnsTransValidation->setShowInputMessage(true);
                $jnsTransValidation->setShowErrorMessage(true);
                $jnsTransValidation->setShowDropDown(true);
                $jnsTransValidation->setFormula1('"' . $jnsAkunString . '"');
                $sheet->setDataValidation('G2:G1000', $jnsTransValidation);
            }
            
            // Create instructions sheet
            $this->createInstructionsSheet($spreadsheet);
            
            // Create jenis akun sheet
            $this->createJenisAkunSheet($spreadsheet);
            
            // Set active sheet to first sheet
            $spreadsheet->setActiveSheetIndex(0);
            
            // Generate Excel file
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $filename = 'template_toserda_' . date('Ymd') . '.xlsx';
            
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            $writer->save('php://output');
            exit;
        } catch (\Exception $e) {
            \Log::error('Error in downloadTemplate: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error generating template: ' . $e->getMessage());
        }
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