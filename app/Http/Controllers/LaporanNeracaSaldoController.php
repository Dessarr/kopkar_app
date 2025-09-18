<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NamaKasTbl;
use App\Models\jns_akun;
use App\Models\transaksi_kas;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LaporanNeracaSaldoController extends Controller
{
    public function index(Request $request)
    {
        // Get filter parameters with default values
        $tgl_dari = $request->input('tgl_dari', date('Y').'-01-01');
        $tgl_samp = $request->input('tgl_samp', date('Y').'-12-31');
        
        // Get all active kas accounts
        $kasList = NamaKasTbl::where('aktif', 'Y')->orderBy('id')->get();
        
        // Get all active accounts
        $akunList = jns_akun::where('aktif', 'Y')
            ->orderByRaw("LPAD(kd_aktiva, 1, 0) ASC, LPAD(kd_aktiva, 5, 1) ASC")
            ->get();
        
        // Get neraca data using v_transaksi
        $data = $this->getNeracaDataFromView($kasList, $akunList, $tgl_dari, $tgl_samp);
        
        return view('laporan.neraca_saldo', compact(
            'kasList',
            'akunList',
            'tgl_dari',
            'tgl_samp',
            'data'
        ));
    }

    /**
     * Get neraca data using v_transaksi view
     * This implements the accounting principle of trial balance
     */
    private function getNeracaDataFromView($kasList, $akunList, $tgl_dari, $tgl_samp)
    {
        $result = [];
        $no = 1;
        $totalDebet = 0;
        $totalKredit = 0;
        
        // Group accounts by category based on kd_aktiva
        $groupedAccounts = $this->groupAccountsByCategory($akunList);
        
        // Process each category
        foreach ($groupedAccounts as $category => $accounts) {
            // Skip empty categories
            if (empty($accounts)) {
                continue;
            }
            
            // Add category header
            $result[] = [
                'no' => '',
                'nama' => $category,
                'debet' => 0,
                'kredit' => 0,
                'is_header' => true,
                'is_kas' => false
            ];
            
            // Remove duplicate accounts by kd_aktiva
            $uniqueAccounts = [];
            $seenKdAktiva = [];
            
            foreach ($accounts as $akun) {
                if (!in_array($akun->kd_aktiva, $seenKdAktiva)) {
                    $uniqueAccounts[] = $akun;
                    $seenKdAktiva[] = $akun->kd_aktiva;
                }
            }
            
            // Process unique accounts in this category
            foreach ($uniqueAccounts as $akun) {
                $akunData = $this->calculateAkunSaldoFromView($akun, $tgl_dari, $tgl_samp);
                
                $result[] = [
                    'no' => $akun->kd_aktiva,
                    'nama' => $akun->jns_trans,
                    'debet' => $akunData['debet'],
                    'kredit' => $akunData['kredit'],
                    'is_header' => false,
                    'is_kas' => false,
                    'saldo' => $akunData['saldo']
                ];
                
                $totalDebet += $akunData['debet'];
                $totalKredit += $akunData['kredit'];
            }
        }
        
        return [
            'rows' => $result,
            'totalDebet' => $totalDebet,
            'totalKredit' => $totalKredit,
            'is_balanced' => $totalDebet == $totalKredit
        ];
    }

    /**
     * Group accounts by category based on kd_aktiva
     */
    private function groupAccountsByCategory($akunList)
    {
        $grouped = [];
        
        foreach ($akunList as $akun) {
            $firstChar = substr($akun->kd_aktiva, 0, 1);
            
            // Initialize array if not exists
            if (!isset($grouped[$firstChar])) {
                $grouped[$firstChar] = [];
            }
            
            $grouped[$firstChar][] = $akun;
        }
        
        // Convert to proper category names
        $categoryMap = [
            'A' => 'A. AKTIVA LANCAR',
            'B' => 'B. AKTIVA LAINNYA', 
            'C' => 'C. AKTIVA TETAP BERWUJUD',
            'D' => 'D. AKTIVA TETAP TIDAK BERWUJUD',
            'E' => 'E. AKTIVA LAIN-LAIN',
            'F' => 'F. UTANG',
            'G' => 'G. UTANG JANGKA PENDEK',
            'H' => 'H. UTANG JANGKA PANJANG',
            'I' => 'I. MODAL',
            'J' => 'J. PENDAPATAN',
            'K' => 'K. BEBAN',
        ];
        
        $result = [];
        foreach ($grouped as $key => $accounts) {
            $categoryName = $categoryMap[$key] ?? 'L. LAIN-LAIN';
            $result[$categoryName] = $accounts;
        }
        
        return $result;
    }

    /**
     * Calculate account saldo using v_transaksi view with aggregation to prevent duplicates
     */
    private function calculateAkunSaldoFromView($akun, $tgl_dari, $tgl_samp)
    {
        // Get aggregated transactions for this account from v_transaksi to prevent duplicates
        $result = DB::table('v_transaksi')
            ->where('transaksi', $akun->id)
            ->whereDate('tgl', '>=', $tgl_dari)
            ->whereDate('tgl', '<=', $tgl_samp)
            ->selectRaw('SUM(debet) as total_debet, SUM(kredit) as total_kredit')
            ->first();
        
        $totalDebet = $result->total_debet ?? 0;
        $totalKredit = $result->total_kredit ?? 0;
        
        // In v_transaksi, debet and kredit are already in final format
        // So we can use them directly without additional calculation
        return [
            'debet' => $totalDebet,
            'kredit' => $totalKredit,
            'saldo' => $totalDebet - $totalKredit
        ];
    }

    /**
     * Get neraca data with proper accounting classification (OLD METHOD - KEEP FOR REFERENCE)
     * This implements the accounting principle of trial balance
     */
    private function getNeracaData($kasList, $akunList, $tgl_dari, $tgl_samp)
    {
        $result = [];
        $no = 1;
        $totalDebet = 0;
        $totalKredit = 0;
        
        // Add header for Aktiva Lancar
        $result[] = [
            'no' => '',
            'nama' => 'A. AKTIVA LANCAR',
            'debet' => 0,
            'kredit' => 0,
            'is_header' => true,
            'is_kas' => false
        ];
        
        // Process Kas accounts (Aktiva Lancar)
        foreach ($kasList as $kas) {
            $kasData = $this->calculateKasSaldo($kas->id, $tgl_dari, $tgl_samp);
            
            $result[] = [
                'no' => 'A' . str_pad($no, 2, '0', STR_PAD_LEFT),
                'nama' => $kas->nama,
                'debet' => $kasData['debet'],
                'kredit' => $kasData['kredit'],
                'is_header' => false,
                'is_kas' => true,
                'saldo' => $kasData['saldo']
            ];
            
            $totalDebet += $kasData['debet'];
            $totalKredit += $kasData['kredit'];
            $no++;
        }
        
        // Process other accounts based on classification
        $aktivaAccounts = $akunList->where('akun', 'Aktiva')->where('kd_aktiva', '!=', '1');
        $pasivaAccounts = $akunList->where('akun', 'Pasiva');
        
        // Add Aktiva accounts
        if ($aktivaAccounts->count() > 0) {
            $result[] = [
                'no' => '',
                'nama' => 'B. AKTIVA LAINNYA',
                'debet' => 0,
                'kredit' => 0,
                'is_header' => true,
                'is_kas' => false
            ];
            
            foreach ($aktivaAccounts as $akun) {
                $akunData = $this->calculateAkunSaldo($akun->id, $tgl_dari, $tgl_samp, 'Aktiva');
                
                $result[] = [
                    'no' => $akun->kd_aktiva,
                    'nama' => $akun->jns_trans,
                    'debet' => $akunData['debet'],
                    'kredit' => $akunData['kredit'],
                    'is_header' => false,
                    'is_kas' => false,
                    'saldo' => $akunData['saldo']
                ];
                
                $totalDebet += $akunData['debet'];
                $totalKredit += $akunData['kredit'];
            }
        }
        
        // Add Pasiva accounts
        if ($pasivaAccounts->count() > 0) {
            $result[] = [
                'no' => '',
                'nama' => 'C. PASIVA',
                'debet' => 0,
                'kredit' => 0,
                'is_header' => true,
                'is_kas' => false
            ];
            
            foreach ($pasivaAccounts as $akun) {
                $akunData = $this->calculateAkunSaldo($akun->id, $tgl_dari, $tgl_samp, 'Pasiva');
                
                $result[] = [
                    'no' => $akun->kd_aktiva,
                    'nama' => $akun->jns_trans,
                    'debet' => $akunData['debet'],
                    'kredit' => $akunData['kredit'],
                    'is_header' => false,
                    'is_kas' => false,
                    'saldo' => $akunData['saldo']
                ];
                
                $totalDebet += $akunData['debet'];
                $totalKredit += $akunData['kredit'];
            }
        }
        
        return [
            'rows' => $result,
            'totalDebet' => $totalDebet,
            'totalKredit' => $totalKredit,
            'is_balanced' => $totalDebet == $totalKredit
        ];
    }

    /**
     * Calculate kas saldo based on transaction flow
     * This implements the accounting principle for cash accounts
     */
    private function calculateKasSaldo($kasId, $tgl_dari, $tgl_samp)
    {
        // Calculate debet (money coming into kas)
        $debet = DB::table('tbl_trans_kas')
            ->where('untuk_kas_id', $kasId)
            ->whereDate('tgl_catat', '>=', $tgl_dari)
            ->whereDate('tgl_catat', '<=', $tgl_samp)
            ->whereIn('dk', ['D', 'K'])
            ->sum('jumlah');
        
        // Calculate kredit (money going out of kas)
        $kredit = DB::table('tbl_trans_kas')
            ->where('dari_kas_id', $kasId)
            ->whereDate('tgl_catat', '>=', $tgl_dari)
            ->whereDate('tgl_catat', '<=', $tgl_samp)
            ->whereIn('dk', ['D', 'K'])
            ->sum('jumlah');
        
        $saldo = $debet - $kredit;
        
        // For kas accounts, if saldo is positive, it's debet; if negative, it's kredit
        if ($saldo >= 0) {
            return [
                'debet' => $saldo,
                'kredit' => 0,
                'saldo' => $saldo
            ];
        } else {
            return [
                'debet' => 0,
                'kredit' => abs($saldo),
                'saldo' => $saldo
            ];
        }
    }

    /**
     * Calculate account saldo based on transaction type
     * This implements the accounting principle for different account types
     */
    private function calculateAkunSaldo($akunId, $tgl_dari, $tgl_samp, $accountType)
    {
        // Get all transactions for this account
        $transactions = DB::table('tbl_trans_kas')
            ->where('akun', $akunId)
            ->whereDate('tgl_catat', '>=', $tgl_dari)
            ->whereDate('tgl_catat', '<=', $tgl_samp)
            ->whereIn('dk', ['D', 'K'])
            ->get();
        
        $totalDebet = 0;
        $totalKredit = 0;
        
        foreach ($transactions as $transaction) {
            if ($transaction->dk == 'D') {
                $totalDebet += $transaction->jumlah;
            } else {
                $totalKredit += $transaction->jumlah;
            }
        }
        
        $saldo = $totalDebet - $totalKredit;
        
        // For Aktiva accounts: positive saldo = debet, negative saldo = kredit
        // For Pasiva accounts: positive saldo = kredit, negative saldo = debet
        if ($accountType == 'Aktiva') {
            if ($saldo >= 0) {
                return [
                    'debet' => $saldo,
                    'kredit' => 0,
                    'saldo' => $saldo
                ];
            } else {
                return [
                    'debet' => 0,
                    'kredit' => abs($saldo),
                    'saldo' => $saldo
                ];
            }
        } else { // Pasiva
            if ($saldo >= 0) {
                return [
                    'debet' => 0,
                    'kredit' => $saldo,
                    'saldo' => $saldo
                ];
            } else {
                return [
                    'debet' => abs($saldo),
                    'kredit' => 0,
                    'saldo' => $saldo
                ];
            }
        }
    }

    public function exportPdf(Request $request)
    {
        // Get filter parameters
        $tgl_dari = $request->input('tgl_dari', date('Y').'-01-01');
        $tgl_samp = $request->input('tgl_samp', date('Y').'-12-31');
        
        // Get data
        $kasList = NamaKasTbl::where('aktif', 'Y')->orderBy('id')->get();
        $akunList = jns_akun::where('aktif', 'Y')
            ->orderByRaw("LPAD(kd_aktiva, 1, 0) ASC, LPAD(kd_aktiva, 5, 1) ASC")
            ->get();
        $data = $this->getNeracaDataFromView($kasList, $akunList, $tgl_dari, $tgl_samp);
        
        // Format dates
        $periodeText = Carbon::parse($tgl_dari)->format('d F Y') . ' - ' . Carbon::parse($tgl_samp)->format('d F Y');
        
        $pdf = Pdf::loadView('laporan.pdf.neraca_saldo', compact(
            'tgl_dari',
            'tgl_samp',
            'periodeText',
            'data'
        ));

        return $pdf->download('laporan_neraca_saldo_' . $tgl_dari . '_' . $tgl_samp . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        // Get filter parameters
        $tgl_dari = $request->input('tgl_dari', date('Y').'-01-01');
        $tgl_samp = $request->input('tgl_samp', date('Y').'-12-31');
        
        // Get data
        $kasList = NamaKasTbl::where('aktif', 'Y')->orderBy('id')->get();
        $akunList = jns_akun::where('aktif', 'Y')
            ->orderByRaw("LPAD(kd_aktiva, 1, 0) ASC, LPAD(kd_aktiva, 5, 1) ASC")
            ->get();
        $data = $this->getNeracaDataFromView($kasList, $akunList, $tgl_dari, $tgl_samp);
        
        // Format dates
        $periodeText = Carbon::parse($tgl_dari)->format('d F Y') . ' - ' . Carbon::parse($tgl_samp)->format('d F Y');
        
        // Create Excel file
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set title
        $sheet->setCellValue('A1', 'LAPORAN NERACA SALDO');
        $sheet->mergeCells('A1:D1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
        
        // Set period info
        $sheet->setCellValue('A2', 'Periode: ' . $periodeText);
        $sheet->mergeCells('A2:D2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
        
        // Set headers
        $sheet->setCellValue('A4', 'Kode Akun');
        $sheet->setCellValue('B4', 'Nama Akun');
        $sheet->setCellValue('C4', 'Debet');
        $sheet->setCellValue('D4', 'Kredit');
        
        // Style headers
        $headerRange = 'A4:D4';
        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E5E7EB');
        
        // Fill data
        $rowNum = 5;
        foreach ($data['rows'] as $row) {
            $sheet->setCellValue('A' . $rowNum, $row['no']);
            $sheet->setCellValue('B' . $rowNum, $row['nama']);
            $sheet->setCellValue('C' . $rowNum, $row['debet']);
            $sheet->setCellValue('D' . $rowNum, $row['kredit']);
            
            // Style header rows
            if (isset($row['is_header']) && $row['is_header']) {
                $sheet->getStyle('A' . $rowNum . ':D' . $rowNum)->getFont()->setBold(true);
                $sheet->getStyle('A' . $rowNum . ':D' . $rowNum)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('F3F4F6');
            }
            
            $rowNum++;
        }
        
        // Add totals
        $totalRow = $rowNum + 1;
        $sheet->setCellValue('A' . $totalRow, 'JUMLAH');
        $sheet->mergeCells('A' . $totalRow . ':B' . $totalRow);
        $sheet->setCellValue('C' . $totalRow, $data['totalDebet']);
        $sheet->setCellValue('D' . $totalRow, $data['totalKredit']);
        
        // Style totals
        $totalRange = 'A' . $totalRow . ':D' . $totalRow;
        $sheet->getStyle($totalRange)->getFont()->setBold(true);
        $sheet->getStyle($totalRange)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('D1D5DB');
        
        // Add balance check
        $balanceRow = $totalRow + 2;
        $sheet->setCellValue('A' . $balanceRow, 'Status Keseimbangan:');
        $sheet->setCellValue('B' . $balanceRow, $data['is_balanced'] ? 'SEIMBANG' : 'TIDAK SEIMBANG');
        $sheet->getStyle('B' . $balanceRow)->getFont()->setBold(true);
        $sheet->getStyle('B' . $balanceRow)->getFont()->getColor()
            ->setRGB($data['is_balanced'] ? '059669' : 'DC2626');
        
        // Auto size columns
        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Format currency columns
        $sheet->getStyle('C5:D' . $totalRow)->getNumberFormat()->setFormatCode('#,##0');
        
        $writer = new Xlsx($spreadsheet);
        $filename = 'laporan_neraca_saldo_' . $tgl_dari . '_' . $tgl_samp . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }
}