<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NamaKasTbl;
use App\Models\transaksi_kas;
use App\Models\jns_akun;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LaporanBukuBesarController extends Controller
{
    public function index(Request $request)
    {
        // Get filter parameters with default values
        $periode = $request->input('periode', date('Y-m'));
        $kasId = $request->input('kas_id', null);
        
        // Get all active kas accounts
        $kasList = NamaKasTbl::where('aktif', 'Y')->get();
        
        $data = [];
        $selectedKas = null;
        $saldoAwal = 0;
        $totalDebet = 0;
        $totalKredit = 0;
        $saldoAkhir = 0;
        
        if ($kasId) {
            $selectedKas = $kasList->where('id', $kasId)->first();
            
            // Parse periode
            $tglArr = explode('-', $periode);
            $thn = $tglArr[0];
            $bln = $tglArr[1];
            
            // Calculate saldo awal (before the selected period)
            $saldoAwal = $this->calculateSaldoAwal($kasId, $thn, $bln);
            
            // Get transactions for the selected period
            $transaksi = $this->getTransaksiKas($kasId, $thn, $bln);
            
            // Process transactions with running balance
            $data = $this->prosesTransaksi($transaksi, $kasId, $saldoAwal);
            
            // Calculate totals
            $totalDebet = collect($data)->sum('debet');
            $totalKredit = collect($data)->sum('kredit');
            $saldoAkhir = $saldoAwal + ($totalDebet - $totalKredit);
        }
        
        return view('laporan.buku_besar', compact(
            'kasList',
            'selectedKas',
            'periode',
            'data',
            'saldoAwal',
            'totalDebet',
            'totalKredit',
            'saldoAkhir'
        ));
    }

    /**
     * Calculate saldo awal (balance before the selected period)
     * This implements the accounting principle of running balance
     */
    private function calculateSaldoAwal($kasId, $tahun, $bulan)
    {
        $saldoAwal = DB::table('tbl_trans_kas')
            ->selectRaw('
                SUM(CASE WHEN untuk_kas_id = ? THEN jumlah ELSE 0 END) - 
                SUM(CASE WHEN dari_kas_id = ? THEN jumlah ELSE 0 END) as saldo
            ', [$kasId, $kasId])
            ->where(function($query) use ($tahun, $bulan) {
                $query->whereYear('tgl_catat', '<', $tahun)
                      ->orWhere(function($q) use ($tahun, $bulan) {
                          $q->whereYear('tgl_catat', $tahun)
                            ->whereMonth('tgl_catat', '<', $bulan);
                      });
            })
            ->whereIn('dk', ['D', 'K'])
            ->value('saldo');

        return $saldoAwal ?? 0;
    }

    /**
     * Get transactions for specific kas and period
     */
    private function getTransaksiKas($kasId, $tahun, $bulan)
    {
        return DB::table('tbl_trans_kas as t')
            ->leftJoin('jns_akun as ja', 't.jns_trans', '=', 'ja.id')
            ->select(
                't.id',
                't.tgl_catat',
                't.keterangan',
                't.jumlah',
                't.dari_kas_id',
                't.untuk_kas_id',
                't.dk',
                'ja.jns_trans as jenis_transaksi'
            )
            ->where(function($q) use ($kasId) {
                $q->where('t.dari_kas_id', $kasId)
                  ->orWhere('t.untuk_kas_id', $kasId);
            })
            ->whereYear('t.tgl_catat', $tahun)
            ->whereMonth('t.tgl_catat', $bulan)
            ->whereIn('t.dk', ['D', 'K'])
            ->orderBy('t.tgl_catat', 'asc')
            ->orderBy('t.id', 'asc')
            ->get();
    }

    /**
     * Process transactions with running balance calculation
     * This implements the accounting principle of double-entry bookkeeping
     */
    private function prosesTransaksi($transaksi, $kasId, $saldoAwal)
    {
        $result = [];
        $runningBalance = $saldoAwal;
        $no = 1;
        
        foreach ($transaksi as $row) {
            $debet = 0;
            $kredit = 0;
            
            // Determine debet/kredit based on transaction direction
            if ($row->untuk_kas_id == $kasId) {
                // Money coming into this kas account
                $debet = $row->jumlah;
            }
            if ($row->dari_kas_id == $kasId) {
                // Money going out of this kas account
                $kredit = $row->jumlah;
            }
            
            // Update running balance
            $runningBalance += ($debet - $kredit);
            
            $result[] = [
                'no' => $no++,
                'tanggal' => $row->tgl_catat,
                'jenis_transaksi' => $row->jenis_transaksi ?? 'N/A',
                'keterangan' => $row->keterangan,
                'debet' => $debet,
                'kredit' => $kredit,
                'saldo' => $runningBalance
            ];
        }
        
        return $result;
    }

    public function exportPdf(Request $request)
    {
        // Get filter parameters
        $periode = $request->input('periode', date('Y-m'));
        $kasId = $request->input('kas_id');
        
        if (!$kasId) {
            return redirect()->back()->with('error', 'Pilih kas terlebih dahulu');
        }
        
        $kas = NamaKasTbl::find($kasId);
        if (!$kas) {
            return redirect()->back()->with('error', 'Kas tidak ditemukan');
        }
        
        // Parse periode
        $tglArr = explode('-', $periode);
        $thn = $tglArr[0];
        $bln = $tglArr[1];
        
        // Calculate saldo awal
        $saldoAwal = $this->calculateSaldoAwal($kasId, $thn, $bln);
        
        // Get transactions
        $transaksi = $this->getTransaksiKas($kasId, $thn, $bln);
        $data = $this->prosesTransaksi($transaksi, $kasId, $saldoAwal);
        
        // Calculate totals
        $totalDebet = collect($data)->sum('debet');
        $totalKredit = collect($data)->sum('kredit');
        $saldoAkhir = $saldoAwal + ($totalDebet - $totalKredit);
        
        // Format periode text
        $periodeText = Carbon::createFromFormat('Y-m', $periode)->format('F Y');
        
        $pdf = Pdf::loadView('laporan.pdf.buku_besar', compact(
            'kas',
            'periode',
            'periodeText',
            'data',
            'saldoAwal',
            'totalDebet',
            'totalKredit',
            'saldoAkhir'
        ));

        return $pdf->download('laporan_buku_besar_' . $kas->nama . '_' . $periode . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        // Get filter parameters
        $periode = $request->input('periode', date('Y-m'));
        $kasId = $request->input('kas_id');
        
        if (!$kasId) {
            return redirect()->back()->with('error', 'Pilih kas terlebih dahulu');
        }
        
        $kas = NamaKasTbl::find($kasId);
        if (!$kas) {
            return redirect()->back()->with('error', 'Kas tidak ditemukan');
        }
        
        // Parse periode
        $tglArr = explode('-', $periode);
        $thn = $tglArr[0];
        $bln = $tglArr[1];
        
        // Calculate saldo awal
        $saldoAwal = $this->calculateSaldoAwal($kasId, $thn, $bln);
        
        // Get transactions
        $transaksi = $this->getTransaksiKas($kasId, $thn, $bln);
        $data = $this->prosesTransaksi($transaksi, $kasId, $saldoAwal);
        
        // Calculate totals
        $totalDebet = collect($data)->sum('debet');
        $totalKredit = collect($data)->sum('kredit');
        $saldoAkhir = $saldoAwal + ($totalDebet - $totalKredit);
        
        // Format periode text
        $periodeText = Carbon::createFromFormat('Y-m', $periode)->format('F Y');
        
        // Create Excel file
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set title
        $sheet->setCellValue('A1', 'LAPORAN BUKU BESAR');
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
        
        // Set kas and period info
        $sheet->setCellValue('A2', 'Kas: ' . $kas->nama);
        $sheet->setCellValue('A3', 'Periode: ' . $periodeText);
        $sheet->setCellValue('A4', 'Saldo Awal: Rp ' . number_format($saldoAwal));
        
        // Set headers
        $sheet->setCellValue('A6', 'No');
        $sheet->setCellValue('B6', 'Tanggal');
        $sheet->setCellValue('C6', 'Jenis Transaksi');
        $sheet->setCellValue('D6', 'Keterangan');
        $sheet->setCellValue('E6', 'Debet');
        $sheet->setCellValue('F6', 'Kredit');
        $sheet->setCellValue('G6', 'Saldo');
        
        // Style headers
        $headerRange = 'A6:G6';
        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E5E7EB');
        
        // Fill data
        $rowNum = 7;
        foreach ($data as $row) {
            $sheet->setCellValue('A' . $rowNum, $row['no']);
            $sheet->setCellValue('B' . $rowNum, Carbon::parse($row['tanggal'])->format('d/m/Y'));
            $sheet->setCellValue('C' . $rowNum, $row['jenis_transaksi']);
            $sheet->setCellValue('D' . $rowNum, $row['keterangan']);
            $sheet->setCellValue('E' . $rowNum, $row['debet']);
            $sheet->setCellValue('F' . $rowNum, $row['kredit']);
            $sheet->setCellValue('G' . $rowNum, $row['saldo']);
            $rowNum++;
        }
        
        // Add totals
        $totalRow = $rowNum + 1;
        $sheet->setCellValue('A' . $totalRow, 'TOTAL');
        $sheet->setCellValue('E' . $totalRow, $totalDebet);
        $sheet->setCellValue('F' . $totalRow, $totalKredit);
        $sheet->setCellValue('G' . $totalRow, $saldoAkhir);
        
        // Style totals
        $totalRange = 'A' . $totalRow . ':G' . $totalRow;
        $sheet->getStyle($totalRange)->getFont()->setBold(true);
        $sheet->getStyle($totalRange)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('F3F4F6');
        
        // Auto size columns
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Format currency columns
        $sheet->getStyle('E7:G' . $totalRow)->getNumberFormat()->setFormatCode('#,##0');
        
        $writer = new Xlsx($spreadsheet);
        $filename = 'laporan_buku_besar_' . str_replace(' ', '_', $kas->nama) . '_' . $periode . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }
}