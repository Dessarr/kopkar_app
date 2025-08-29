<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\data_mobil;
use App\Models\transaksi_kas;
use App\Models\NamaKasTbl;
use App\Models\jns_akun;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AngkutanController extends Controller
{
    public function pemasukan(Request $request)
    {
        // Get filter parameters
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $search = $request->input('search');
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
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $search = $request->input('search');
        $kasFilter = $request->input('kas_filter');

        // Base query for pengeluaran (jns_trans 55-69 for various operational costs)
        $query = transaksi_kas::with(['dariKas', 'untukKas'])
            ->whereIn('jns_trans', ['55', '56', '57', '58', '59', '60', '61', '62', '63', '64', '65', '66', '67', '68', '69'])
            ->where('dk', 'K');

        // Apply filters
        $query = $this->applyFilters($query, $startDate, $endDate, $search, $kasFilter);

        // Get paginated results
        $transaksi = $query->orderBy('tgl_catat', 'desc')->paginate(10);

        // Get statistics
        $totalPengeluaran = $query->sum('jumlah');
        $totalTransaksi = $query->count();

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
                $q->where('keterangan', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%");
            });
        }

        if ($kasFilter) {
            $query->where('dari_kas_id', $kasFilter);
        }

        return $query;
    }

    public function storePemasukan(Request $request)
    {
        $request->validate([
            'tgl_catat' => 'required|date',
            'keterangan' => 'required|string',
            'jumlah' => 'required|numeric|min:0',
            'untuk_kas_id' => 'required|exists:nama_kas_tbl,id',
            'dari_kas_id' => 'required|exists:nama_kas_tbl,id'
        ]);

        $transaksi = new transaksi_kas();
        $transaksi->tgl_catat = $request->tgl_catat;
        $transaksi->keterangan = $request->keterangan;
        $transaksi->jumlah = $request->jumlah;
        $transaksi->jns_trans = '46'; // Pendapatan Jasa Sewa Bus
        $transaksi->akun = '46'; // Pendapatan Jasa Sewa Bus
        $transaksi->dari_kas_id = $request->dari_kas_id;
        $transaksi->untuk_kas_id = $request->untuk_kas_id;
        $transaksi->dk = 'D'; // Debit untuk pemasukan
        $transaksi->update_data = now();
        $transaksi->user_name = Auth::user()->name;
        $transaksi->id_cabang = 1;
        $transaksi->save();

        return redirect()->back()->with('success', 'Pemasukan angkutan berhasil disimpan');
    }

    public function storePengeluaran(Request $request)
    {
        $request->validate([
            'tgl_catat' => 'required|date',
            'keterangan' => 'required|string',
            'jumlah' => 'required|numeric|min:0',
            'dari_kas_id' => 'required|exists:nama_kas_tbl,id',
            'untuk_kas_id' => 'required|exists:nama_kas_tbl,id',
            'jns_trans' => 'required|in:55,56,57,58,59,60,61,62,63,64,65,66,67,68,69'
        ]);

        $transaksi = new transaksi_kas();
        $transaksi->tgl_catat = $request->tgl_catat;
        $transaksi->keterangan = $request->keterangan;
        $transaksi->jumlah = $request->jumlah;
        $transaksi->jns_trans = $request->jns_trans;
        $transaksi->akun = $request->jns_trans;
        $transaksi->dari_kas_id = $request->dari_kas_id;
        $transaksi->untuk_kas_id = $request->untuk_kas_id;
        $transaksi->dk = 'K'; // Kredit untuk pengeluaran
        $transaksi->update_data = now();
        $transaksi->user_name = Auth::user()->name;
        $transaksi->id_cabang = 1;
        $transaksi->save();

        return redirect()->back()->with('success', 'Pengeluaran angkutan berhasil disimpan');
    }

    public function exportPdfPemasukan(Request $request)
    {
        // Get filter parameters
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $search = $request->input('search');
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
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $search = $request->input('search');
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
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $search = $request->input('search');
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
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $search = $request->input('search');
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