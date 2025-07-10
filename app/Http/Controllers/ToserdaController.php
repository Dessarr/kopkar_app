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

class ToserdaController extends Controller
{
    public function penjualan()
    {
        $barang = data_barang::all();
        $anggota = data_anggota::all();
        $kas = DataKas::all();
        return view('toserda.penjualan', compact('barang', 'anggota', 'kas'));
    }

    public function pembelian()
    {
        $barang = data_barang::all();
        $kas = DataKas::all();
        return view('toserda.pembelian', compact('barang', 'kas'));
    }

    public function biayaUsaha()
    {
        $kas = DataKas::all();
        return view('toserda.biaya_usaha', compact('kas'));
    }
    
    public function lainLain(Request $request)
    {
        // Ambil data dari View_Toserda untuk menampilkan semua transaksi
        $query = View_Toserda::with(['anggota', 'kas']);
        
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
        
        $transaksi = $query->orderBy('tgl_transaksi', 'desc')->paginate(10);
        
        // Ambil data kas untuk dropdown pada form upload
        $kas = DataKas::all();
        
        return view('toserda.lain_lain', compact('transaksi', 'kas'));
    }

    public function storePenjualan(Request $request)
    {
        $request->validate([
            'barang_id' => 'required|exists:data_barang,id',
            'anggota_id' => 'required|exists:data_anggota,id',
            'jumlah' => 'required|numeric|min:1',
            'kas_id' => 'required|exists:nama_kas_tbl,id',
            'keterangan' => 'nullable|string'
        ]);

        // Get anggota data
        $anggota = data_anggota::find($request->anggota_id);
        
        $transaksi = new TblTransToserda();
        $transaksi->tgl_transaksi = now();
        $transaksi->no_ktp = $anggota->no_ktp;
        $transaksi->anggota_id = $request->anggota_id;
        $transaksi->jenis_id = $request->barang_id; // using barang_id as jenis_id
        $transaksi->jumlah = $request->jumlah;
        $transaksi->keterangan = $request->keterangan ?? 'Penjualan Toserda';
        $transaksi->dk = 'D'; // Debit untuk penjualan
        $transaksi->kas_id = $request->kas_id;
        $transaksi->user_name = Auth::user()->name;
        $transaksi->save();

        // Update stok barang
        $barang = data_barang::find($request->barang_id);
        $barang->stok -= $request->jumlah;
        $barang->save();

        return redirect()->back()->with('success', 'Transaksi penjualan berhasil disimpan');
    }

    public function storePembelian(Request $request)
    {
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
        $transaksi->dk = 'K'; // Kredit untuk pembelian
        $transaksi->kas_id = $request->kas_id;
        $transaksi->user_name = Auth::user()->name;
        $transaksi->save();

        // Update stok barang
        $barang = data_barang::find($request->barang_id);
        $barang->stok += $request->jumlah;
        $barang->save();

        return redirect()->back()->with('success', 'Transaksi pembelian berhasil disimpan');
    }

    public function storeBiayaUsaha(Request $request)
    {
        $request->validate([
            'keterangan' => 'required|string',
            'jumlah' => 'required|numeric|min:0',
            'kas_id' => 'required|exists:nama_kas_tbl,id'
        ]);

        $transaksi = new TblTransToserda();
        $transaksi->tgl_transaksi = now();
        $transaksi->jumlah = $request->jumlah;
        $transaksi->keterangan = $request->keterangan;
        $transaksi->dk = 'K'; // Kredit untuk biaya
        $transaksi->kas_id = $request->kas_id;
        $transaksi->user_name = Auth::user()->name;
        $transaksi->save();

        return redirect()->back()->with('success', 'Biaya usaha berhasil disimpan');
    }
    
    public function storeUploadToserda(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:2048',
            'bulan' => 'required|string',
            'tahun' => 'required|numeric',
            'kas_id' => 'required|exists:nama_kas_tbl,id',
        ]);
        
        // Proses upload file Excel
        try {
            // Ambil ID jenis akun untuk Toserda (155)
            $jnsAkun = jns_akun::find(155);
            if (!$jnsAkun) {
                return redirect()->back()->with('error', 'Jenis akun Toserda tidak ditemukan');
            }
            
            // Import data dari Excel
            $file = $request->file('file');
            $import = new \App\Imports\ToserdaImport($request->kas_id);
            
            // Gunakan import dengan penanganan error
            Excel::import($import, $file);
            
            return redirect()->back()->with('success', 'Data berhasil diupload');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errorMessages = [];
            
            foreach ($failures as $failure) {
                $errorMessages[] = "Baris ke-" . $failure->row() . ": " . implode(', ', $failure->errors());
            }
            
            return redirect()->back()->with('error', 'Error validasi: ' . implode('<br>', $errorMessages));
        } catch (\Exception $e) {
            // Log error untuk debugging
            \Illuminate\Support\Facades\Log::error('Error upload Toserda: ' . $e->getMessage());
            \Illuminate\Support\Facades\Log::error($e->getTraceAsString());
            
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
    
    public function processMonthlyBilling(Request $request)
    {
        $request->validate([
            'bulan' => 'required|string',
            'tahun' => 'required|numeric',
        ]);
        
        try {
            // Ambil ID jenis akun untuk Toserda (155)
            $jnsAkun = jns_akun::find(155);
            if (!$jnsAkun) {
                return redirect()->back()->with('error', 'Jenis akun Toserda tidak ditemukan');
            }
            
            // Tentukan periode untuk query
            $period = $request->tahun . '-' . str_pad($request->bulan, 2, '0', STR_PAD_LEFT);
            $startDate = $period . '-01';
            $endDate = date('Y-m-t', strtotime($startDate));
            
            // Ambil semua transaksi per anggota dalam periode yang ditentukan
            $anggotaTransaksi = DB::table('tbl_trans_toserda')
                ->select('anggota_id', 'no_ktp', DB::raw('SUM(jumlah) as total_belanja'), 'jns_trans')
                ->whereNotNull('anggota_id')
                ->whereNotNull('no_ktp')
                ->whereBetween('tgl_transaksi', [$startDate, $endDate])
                ->where('dk', 'D') // Hanya transaksi debit (penjualan)
                ->groupBy('anggota_id', 'no_ktp', 'jns_trans')
                ->get();
            
            $processed = 0;
            
            // Update billing untuk setiap anggota
            foreach ($anggotaTransaksi as $transaksi) {
                $anggota = data_anggota::find($transaksi->anggota_id);
                if ($anggota) {
                    // Update data anggota dengan total belanja pada billing toserda
                    $anggota->simpanan_khusus_2 = $transaksi->total_belanja;
                    $anggota->jns_trans = $transaksi->jns_trans ?? ($jnsAkun ? $jnsAkun->jns_trans : null);
                    $anggota->save();
                    
                    $processed++;
                }
            }
            
            return redirect()->back()->with('success', "Berhasil memproses tagihan bulanan untuk $processed anggota");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        // Buat spreadsheet baru
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
        
        $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);
        
        // Set lebar kolom
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(30);
        $sheet->getColumnDimension('F')->setWidth(10);
        $sheet->getColumnDimension('G')->setWidth(15);
        
        // Ambil data jns_akun untuk dropdown
        $jnsAkunList = jns_akun::pluck('jns_trans')->toArray();
        $jnsAkunString = implode(',', $jnsAkunList);
        
        // Default jns_akun untuk Toserda
        $defaultJnsAkun = jns_akun::find(155);
        $defaultJnsTrans = $defaultJnsAkun && $defaultJnsAkun->jns_trans ? $defaultJnsAkun->jns_trans : 'Toserda';
        
        // Tambahkan contoh data
        $sheet->setCellValue('A2', date('Y-m-d')); // Format YYYY-MM-DD
        $sheet->setCellValue('B2', '3201234567890001'); // Format string
        $sheet->setCellValue('C2', 'Akram');
        $sheet->setCellValue('D2', '50000');
        $sheet->setCellValue('E2', 'Pembelian Sembako');
        $sheet->setCellValue('F2', 'D');
        $sheet->setCellValue('G2', $defaultJnsTrans);
        
        // Contoh data kedua
        $sheet->setCellValue('A3', date('Y-m-d'));
        $sheet->setCellValue('B3', '3201234567890002');
        $sheet->setCellValue('C3', 'Budi');
        $sheet->setCellValue('D3', '75000');
        $sheet->setCellValue('E3', 'Pembelian ATK');
        $sheet->setCellValue('F3', 'D');
        $sheet->setCellValue('G3', $defaultJnsTrans);
        
        // Tambahkan format tanggal untuk kolom tanggal
        $sheet->getStyle('A2:A3')->getNumberFormat()->setFormatCode('yyyy-mm-dd');
        
        // Tambahkan format text untuk kolom no_ktp untuk mencegah format ilmiah
        $sheet->getStyle('B2:B3')->getNumberFormat()->setFormatCode('@');
        
        // Set style untuk contoh data
        $dataStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
        
        $sheet->getStyle('A2:G3')->applyFromArray($dataStyle);
        
        // Tambahkan validasi untuk kolom DK
        $validation = $sheet->getCell('F2')->getDataValidation();
        $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
        $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
        $validation->setAllowBlank(false);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowDropDown(true);
        $validation->setFormula1('"D,K"');
        $validation->setPromptTitle('Pilih D atau K');
        $validation->setPrompt('D untuk Debit (penjualan), K untuk Kredit (pembelian)');
        $validation->setErrorTitle('Input salah');
        $validation->setError('Hanya D atau K yang diperbolehkan');
        
        // Copy validasi ke seluruh kolom
        $sheet->setDataValidation('F4:F1000', $validation);
        
        // Tambahkan validasi untuk kolom jns_trans jika ada data
        if (!empty($jnsAkunString)) {
            $jnsTransValidation = $sheet->getCell('G2')->getDataValidation();
            $jnsTransValidation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
            $jnsTransValidation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
            $jnsTransValidation->setAllowBlank(true);
            $jnsTransValidation->setShowInputMessage(true);
            $jnsTransValidation->setShowErrorMessage(true);
            $jnsTransValidation->setShowDropDown(true);
            $jnsTransValidation->setFormula1('"' . $jnsAkunString . '"');
            $jnsTransValidation->setPromptTitle('Pilih Jenis Transaksi');
            $jnsTransValidation->setPrompt('Pilih jenis transaksi dari daftar');
            $jnsTransValidation->setErrorTitle('Input salah');
            $jnsTransValidation->setError('Hanya jenis transaksi yang terdaftar diperbolehkan');
            
            // Copy validasi ke seluruh kolom
            $sheet->setDataValidation('G4:G1000', $jnsTransValidation);
        }
        
        // Tambahkan sheet petunjuk
        $petunjuk = $spreadsheet->createSheet();
        $petunjuk->setTitle('Petunjuk');
        
        $petunjuk->setCellValue('A1', 'PETUNJUK PENGISIAN TEMPLATE TOSERDA');
        $petunjuk->setCellValue('A3', 'Kolom');
        $petunjuk->setCellValue('B3', 'Keterangan');
        
        $petunjuk->setCellValue('A4', 'tanggal');
        $petunjuk->setCellValue('B4', 'Format tanggal YYYY-MM-DD (contoh: 2023-05-15). Jika tidak diisi, akan menggunakan tanggal hari ini.');
        
        $petunjuk->setCellValue('A5', 'no_ktp');
        $petunjuk->setCellValue('B5', 'Nomor KTP anggota, harus terdaftar dalam sistem. Pastikan format adalah teks, bukan angka.');
        
        $petunjuk->setCellValue('A6', 'nama');
        $petunjuk->setCellValue('B6', 'Nama anggota, diperlukan jika no_ktp tidak diisi.');
        
        $petunjuk->setCellValue('A7', 'jumlah');
        $petunjuk->setCellValue('B7', 'Jumlah nominal transaksi (angka).');
        
        $petunjuk->setCellValue('A8', 'keterangan');
        $petunjuk->setCellValue('B8', 'Keterangan transaksi.');
        
        $petunjuk->setCellValue('A9', 'dk');
        $petunjuk->setCellValue('B9', 'D untuk Debit (penjualan kepada anggota), K untuk Kredit (pembelian stok).');
        
        $petunjuk->setCellValue('A10', 'jns_trans');
        $petunjuk->setCellValue('B10', 'Jenis transaksi sesuai dengan tabel jns_akun. Jika kosong, akan menggunakan default "Toserda".');
        
        $petunjuk->setCellValue('A12', 'CATATAN PENTING:');
        $petunjuk->setCellValue('A13', '1. Pastikan kolom no_ktp diformat sebagai teks untuk mencegah konversi ke notasi ilmiah.');
        $petunjuk->setCellValue('A14', '2. Untuk mengubah format sel menjadi teks: pilih sel > klik kanan > Format Cells > Text.');
        $petunjuk->setCellValue('A15', '3. Atau tambahkan tanda petik satu (\') di awal nomor KTP, contoh: \'3201234567890001');
        $petunjuk->setCellValue('A16', '4. Jika menggunakan Excel, pilih semua sel di kolom no_ktp, lalu ubah format menjadi Text sebelum mengetik.');
        $petunjuk->setCellValue('A17', '5. Kolom jns_trans harus sesuai dengan jenis transaksi yang terdaftar di sistem.');
        
        // Format judul
        $petunjuk->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $petunjuk->getStyle('A3:B3')->getFont()->setBold(true);
        $petunjuk->getStyle('A12')->getFont()->setBold(true);
        
        // Set lebar kolom
        $petunjuk->getColumnDimension('A')->setWidth(15);
        $petunjuk->getColumnDimension('B')->setWidth(60);
        
        // Tambahkan sheet jenis akun
        $jnsAkunSheet = $spreadsheet->createSheet();
        $jnsAkunSheet->setTitle('Jenis Akun');
        
        $jnsAkunSheet->setCellValue('A1', 'DAFTAR JENIS AKUN');
        $jnsAkunSheet->setCellValue('A3', 'ID');
        $jnsAkunSheet->setCellValue('B3', 'Kode Aktiva');
        $jnsAkunSheet->setCellValue('C3', 'Jenis Transaksi');
        $jnsAkunSheet->setCellValue('D3', 'Akun');
        
        // Format header
        $jnsAkunSheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $jnsAkunSheet->getStyle('A3:D3')->getFont()->setBold(true);
        
        // Set lebar kolom
        $jnsAkunSheet->getColumnDimension('A')->setWidth(10);
        $jnsAkunSheet->getColumnDimension('B')->setWidth(15);
        $jnsAkunSheet->getColumnDimension('C')->setWidth(25);
        $jnsAkunSheet->getColumnDimension('D')->setWidth(40);
        
        // Isi data jenis akun
        $jnsAkunData = jns_akun::all();
        $row = 4;
        foreach ($jnsAkunData as $akun) {
            $jnsAkunSheet->setCellValue('A' . $row, $akun->id);
            $jnsAkunSheet->setCellValue('B' . $row, $akun->kd_aktiva);
            $jnsAkunSheet->setCellValue('C' . $row, $akun->jns_trans);
            $jnsAkunSheet->setCellValue('D' . $row, $akun->akun);
            $row++;
        }
        
        // Format tabel
        $jnsAkunSheet->getStyle('A3:D' . ($row-1))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        
        // Kembali ke sheet pertama
        $spreadsheet->setActiveSheetIndex(0);
        
        // Buat file Excel
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        // Set header untuk download
        $filename = 'template_toserda_' . date('Ymd') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        // Tulis ke output
        $writer->save('php://output');
        exit;
    }
} 