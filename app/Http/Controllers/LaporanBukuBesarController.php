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
        
        // Get all active kas accounts
        $kasList = NamaKasTbl::where('aktif', 'Y')->get();
        
        $processedData = [];
        $totalSaldoKeseluruhan = 0;
        
        // Parse periode
        $tglArr = explode('-', $periode);
        $thn = $tglArr[0];
        $bln = $tglArr[1];
        
        // Process data for each kas account
        foreach ($kasList as $kas) {
            $kasData = $this->processKasData($kas, $thn, $bln);
            if (!empty($kasData['transaksi'])) {
                $processedData[] = $kasData;
                $totalSaldoKeseluruhan += $kasData['saldo_akhir'];
            }
        }
        
        return view('laporan.buku_besar', compact(
            'kasList',
            'processedData',
            'periode',
            'totalSaldoKeseluruhan'
        ));
    }

    /**
     * Process data for a specific kas account
     */
    private function processKasData($kas, $tahun, $bulan)
    {
        $kasId = $kas->id;
        
        // Calculate saldo awal using v_transaksi
        $saldoAwal = $this->calculateSaldoAwal($kasId, $tahun, $bulan);
        
        // Get transactions for the selected period using v_transaksi
        $transaksi = $this->getTransaksiFromView($kasId, $tahun, $bulan);
        
        // Process transactions with running balance
        $processedTransaksi = $this->prosesTransaksiFromView($transaksi, $kasId, $saldoAwal);
        
        // Calculate totals
        $totalDebet = collect($processedTransaksi)->sum('debet');
        $totalKredit = collect($processedTransaksi)->sum('kredit');
        $saldoAkhir = $saldoAwal + ($totalDebet - $totalKredit);
        
        return [
            'kas' => $kas,
            'transaksi' => $processedTransaksi,
            'saldo_awal' => $saldoAwal,
            'total_debet' => $totalDebet,
            'total_kredit' => $totalKredit,
            'saldo_akhir' => $saldoAkhir
        ];
    }

    /**
     * Calculate saldo awal using v_transaksi view
     */
    private function calculateSaldoAwal($kasId, $tahun, $bulan)
    {
        $saldoAwal = DB::table('v_transaksi')
            ->selectRaw('
                SUM(CASE WHEN untuk_kas = ? THEN debet ELSE 0 END) - 
                SUM(CASE WHEN dari_kas = ? THEN kredit ELSE 0 END) as saldo
            ', [$kasId, $kasId])
            ->where(function($query) use ($tahun, $bulan) {
                $query->whereYear('tgl', '<', $tahun)
                      ->orWhere(function($q) use ($tahun, $bulan) {
                          $q->whereYear('tgl', $tahun)
                            ->whereMonth('tgl', '<', $bulan);
                      });
            })
            ->where(function($q) use ($kasId) {
                $q->where('dari_kas', $kasId)
                  ->orWhere('untuk_kas', $kasId);
            })
            ->value('saldo');

        return $saldoAwal ?? 0;
    }

    /**
     * Get transactions from v_transaksi view for specific kas and period
     */
    private function getTransaksiFromView($kasId, $tahun, $bulan)
    {
        return DB::table('v_transaksi as v')
            ->leftJoin('jns_akun as ja', 'v.transaksi', '=', 'ja.id')
            ->leftJoin('nama_kas_tbl as nk_dari', 'v.dari_kas', '=', 'nk_dari.id')
            ->leftJoin('nama_kas_tbl as nk_untuk', 'v.untuk_kas', '=', 'nk_untuk.id')
            ->select(
                'v.tbl',
                'v.id',
                'v.tgl',
                'v.nama',
                'v.debet',
                'v.kredit',
                'v.dari_kas',
                'v.untuk_kas',
                'v.transaksi',
                'v.ket',
                'v.user',
                'ja.jns_trans as jenis_transaksi',
                'nk_dari.nama as dari_kas_nama',
                'nk_untuk.nama as untuk_kas_nama'
            )
            ->where(function($q) use ($kasId) {
                $q->where('v.dari_kas', $kasId)
                  ->orWhere('v.untuk_kas', $kasId);
            })
            ->whereYear('v.tgl', $tahun)
            ->whereMonth('v.tgl', $bulan)
            ->orderBy('v.tgl', 'asc')
            ->orderBy('v.id', 'asc')
            ->get();
    }

    /**
     * Process transactions from v_transaksi with running balance calculation
     */
    private function prosesTransaksiFromView($transaksi, $kasId, $saldoAwal)
    {
        $result = [];
        $runningBalance = $saldoAwal;
        $no = 1;
        
        foreach ($transaksi as $row) {
            $debet = 0;
            $kredit = 0;
            
            // Determine debet/kredit based on transaction direction
            if ($row->untuk_kas == $kasId) {
                // Money coming into this kas account
                $debet = $row->debet;
            }
            if ($row->dari_kas == $kasId) {
                // Money going out of this kas account
                $kredit = $row->kredit;
            }
            
            // Update running balance
            $runningBalance += ($debet - $kredit);
            
            $result[] = [
                'no' => $no++,
                'tanggal' => $row->tgl,
                'jenis_transaksi' => $row->jenis_transaksi ?? 'N/A',
                'keterangan' => $row->ket,
                'nama' => $row->nama,
                'debet' => $debet,
                'kredit' => $kredit,
                'saldo' => $runningBalance,
                'tbl' => $row->tbl,
                'dari_kas_nama' => $row->dari_kas_nama,
                'untuk_kas_nama' => $row->untuk_kas_nama
            ];
        }
        
        return $result;
    }

    public function exportPdf(Request $request)
    {
        // Get filter parameters
        $periode = $request->input('periode', date('Y-m'));
        
        // Get all active kas accounts
        $kasList = NamaKasTbl::where('aktif', 'Y')->get();
        
        $processedData = [];
        $totalSaldoKeseluruhan = 0;
        
        // Parse periode
        $tglArr = explode('-', $periode);
        $thn = $tglArr[0];
        $bln = $tglArr[1];
        
        // Process data for each kas account
        foreach ($kasList as $kas) {
            $kasData = $this->processKasData($kas, $thn, $bln);
            if (!empty($kasData['transaksi'])) {
                $processedData[] = $kasData;
                $totalSaldoKeseluruhan += $kasData['saldo_akhir'];
            }
        }
        
        // Format periode text
        $periodeText = Carbon::createFromFormat('Y-m', $periode)->format('F Y');
        
        $pdf = Pdf::loadView('laporan.pdf.buku_besar', compact(
            'processedData',
            'periode',
            'periodeText',
            'totalSaldoKeseluruhan'
        ));

        return $pdf->download('laporan_buku_besar_' . $periode . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        // Get filter parameters
        $periode = $request->input('periode', date('Y-m'));
        
        // Get all active kas accounts
        $kasList = NamaKasTbl::where('aktif', 'Y')->get();
        
        $processedData = [];
        $totalSaldoKeseluruhan = 0;
        
        // Parse periode
        $tglArr = explode('-', $periode);
        $thn = $tglArr[0];
        $bln = $tglArr[1];
        
        // Process data for each kas account
        foreach ($kasList as $kas) {
            $kasData = $this->processKasData($kas, $thn, $bln);
            if (!empty($kasData['transaksi'])) {
                $processedData[] = $kasData;
                $totalSaldoKeseluruhan += $kasData['saldo_akhir'];
            }
        }
        
        // Format periode text
        $periodeText = Carbon::createFromFormat('Y-m', $periode)->format('F Y');
        
        // Create Excel file
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set title
        $sheet->setCellValue('A1', 'LAPORAN BUKU BESAR');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
        
        // Set period info
        $sheet->setCellValue('A2', 'Periode: ' . $periodeText);
        $sheet->setCellValue('A3', 'Total Saldo Keseluruhan: Rp ' . number_format($totalSaldoKeseluruhan));
        
        $currentRow = 5;
        
        // Process each kas account
        foreach ($processedData as $kasData) {
            $kas = $kasData['kas'];
            $transaksi = $kasData['transaksi'];
            
            // Set kas header
            $sheet->setCellValue('A' . $currentRow, $kas->nama);
            $sheet->mergeCells('A' . $currentRow . ':H' . $currentRow);
            $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A' . $currentRow)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('E5E7EB');
            $currentRow++;
            
            // Set headers
            $sheet->setCellValue('A' . $currentRow, 'No');
            $sheet->setCellValue('B' . $currentRow, 'Tanggal');
            $sheet->setCellValue('C' . $currentRow, 'Jenis Transaksi');
            $sheet->setCellValue('D' . $currentRow, 'Keterangan');
            $sheet->setCellValue('E' . $currentRow, 'Nama');
            $sheet->setCellValue('F' . $currentRow, 'Debet');
            $sheet->setCellValue('G' . $currentRow, 'Kredit');
            $sheet->setCellValue('H' . $currentRow, 'Saldo');
            
            // Style headers
            $headerRange = 'A' . $currentRow . ':H' . $currentRow;
            $sheet->getStyle($headerRange)->getFont()->setBold(true);
            $sheet->getStyle($headerRange)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('D1D5DB');
            $currentRow++;
            
            // Fill data
            foreach ($transaksi as $row) {
                $sheet->setCellValue('A' . $currentRow, $row['no']);
                $sheet->setCellValue('B' . $currentRow, Carbon::parse($row['tanggal'])->format('d/m/Y'));
                $sheet->setCellValue('C' . $currentRow, $row['jenis_transaksi']);
                $sheet->setCellValue('D' . $currentRow, $row['keterangan']);
                $sheet->setCellValue('E' . $currentRow, $row['nama']);
                $sheet->setCellValue('F' . $currentRow, $row['debet']);
                $sheet->setCellValue('G' . $currentRow, $row['kredit']);
                $sheet->setCellValue('H' . $currentRow, $row['saldo']);
                $currentRow++;
            }
            
            // Add totals for this kas
            $sheet->setCellValue('A' . $currentRow, 'TOTAL ' . $kas->nama);
            $sheet->setCellValue('F' . $currentRow, $kasData['total_debet']);
            $sheet->setCellValue('G' . $currentRow, $kasData['total_kredit']);
            $sheet->setCellValue('H' . $currentRow, $kasData['saldo_akhir']);
            
            // Style totals
            $totalRange = 'A' . $currentRow . ':H' . $currentRow;
            $sheet->getStyle($totalRange)->getFont()->setBold(true);
            $sheet->getStyle($totalRange)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('F3F4F6');
            $currentRow += 2; // Add space between kas accounts
        }
        
        // Auto size columns
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Format currency columns
        $sheet->getStyle('F5:H' . $currentRow)->getNumberFormat()->setFormatCode('#,##0');
        
        $writer = new Xlsx($spreadsheet);
        $filename = 'laporan_buku_besar_' . $periode . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }
} 