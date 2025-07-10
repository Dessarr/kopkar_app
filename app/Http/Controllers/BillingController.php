<?php
namespace App\Http\Controllers;

use App\Models\billing;
use App\Models\data_anggota;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class BillingController extends Controller
{
    public function index(Request $request)
    {
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
        // (tidak diimplementasikan karena tabel billing tidak memiliki field tanggal)
        // Jika ingin menambahkan fitur ini, perlu menambahkan field period_month dan period_year di tabel

        // Ambil data dengan paginate (10 per halaman)
        $dataBilling = $query->paginate(10);

        return view('billing.billing', compact('dataBilling'));
    }
    
    public function processPayment($id)
    {
        // Proses pembayaran billing
        $billing = billing::findOrFail($id);
        
        // Update status menjadi 'Lunas'
        $billing->status_bayar = 'Lunas';
        $billing->save();
        
        return redirect()->back()->with('success', 'Pembayaran berhasil diproses');
    }
    
    public function exportExcel(Request $request)
    {
        // Buat spreadsheet baru
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set judul kolom
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'No KTP');
        $sheet->setCellValue('C1', 'Nama');
        $sheet->setCellValue('D1', 'Simpanan Wajib');
        $sheet->setCellValue('E1', 'Simpanan Sukarela');
        $sheet->setCellValue('F1', 'Toserda (Khusus 2)');
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
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->getColumnDimension('D')->setWidth(15);
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
        
        $dataBilling = $query->get();
        
        // Default jns_akun untuk Toserda
        $defaultJnsAkun = \App\Models\jns_akun::find(155);
        $defaultJnsTrans = $defaultJnsAkun && $defaultJnsAkun->jns_trans ? $defaultJnsAkun->jns_trans : 'Toserda';
        
        // Isi data
        $row = 2;
        $totalBilling = 0;
        foreach ($dataBilling as $index => $item) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $item->no_ktp);
            $sheet->setCellValue('C' . $row, $item->nama);
            $sheet->setCellValue('D' . $row, $item->simpanan_wajib ?? 0);
            $sheet->setCellValue('E' . $row, $item->simpanan_sukarela ?? 0);
            $sheet->setCellValue('F' . $row, $item->simpanan_khusus_2 ?? 0);
            $sheet->setCellValue('G' . $row, $defaultJnsTrans); // Jenis transaksi default
            $sheet->setCellValue('H' . $row, $item->total_billing);
            $sheet->setCellValue('I' . $row, $item->status_bayar ?? 'Belum Lunas');
            
            // Format angka untuk kolom nominal
            $sheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode('#,##0');
            
            $totalBilling += $item->total_billing;
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
        
        $dataBilling = $query->get();
        
        // Load view untuk PDF
        $pdf = PDF::loadView('billing.pdf', compact('dataBilling'));
        
        // Download PDF
        return $pdf->download('billing_anggota_' . date('Ymd') . '.pdf');
    }
}