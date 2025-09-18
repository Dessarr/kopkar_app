<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\data_anggota;
use App\Models\TransaksiSimpanan;
use App\Models\jns_simpan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LaporanKasSimpananController extends Controller
{
    public function index(Request $request)
    {
        // Get filter parameters with default values
        $periode = $request->input('periode', date('Y-m'));
        
        // Get specific savings types as per specification
        $jenisSimpanan = jns_simpan::whereIn('id', [31, 32, 40, 41, 51, 52])
            ->orderBy('urut')
            ->get();
        
        // Get detailed savings data using v_rekap_simpanan
        $data = $this->getRekapSimpananFromView($jenisSimpanan, $periode);
        
        // Calculate summary statistics
        $summary = $this->calculateSummaryFromView($data, $jenisSimpanan);
        
        return view('laporan.kas_simpanan', compact(
            'jenisSimpanan',
            'periode',
            'data',
            'summary'
        ));
    }

    /**
     * Get detailed savings data using v_rekap_simpanan view
     * This implements the accounting principle of subsidiary ledger for savings
     */
    private function getRekapSimpananFromView($jenisSimpanan, $periode)
    {
        $result = [];
        $tglArr = explode('-', $periode);
        $thn = $tglArr[0];
        $bln = $tglArr[1];
        
        // Group data by jenis simpanan
        foreach ($jenisSimpanan as $jenis) {
            $jenisData = [
                'jenis_id' => $jenis->id,
                'jenis_nama' => $jenis->jns_simpan,
                'transaksi' => []
            ];
            
            // Get transactions for this savings type from v_rekap_simpanan
            $transactions = DB::table('v_rekap_simpanan')
                ->where('jenis_id', $jenis->id)
                ->whereYear('tgl_transaksi', $thn)
                ->whereMonth('tgl_transaksi', $bln)
                ->orderBy('tgl_transaksi', 'desc')
                ->orderBy('nama')
                ->get();
            
            $no = 1;
            foreach ($transactions as $transaction) {
                $jenisData['transaksi'][] = [
                    'no' => $no++,
                    'tanggal' => $transaction->tgl_transaksi,
                    'nama' => $transaction->nama,
                    'debet' => $transaction->Debet,
                    'kredit' => $transaction->Kredit
                ];
            }
            
            $result[] = $jenisData;
        }
        
        return $result;
    }

    /**
     * Calculate summary statistics from v_rekap_simpanan data
     */
    private function calculateSummaryFromView($data, $jenisSimpanan)
    {
        $summary = [
            'total_debet' => 0,
            'total_kredit' => 0,
            'per_jenis' => []
        ];
        
        foreach ($data as $jenisData) {
            $totalDebet = 0;
            $totalKredit = 0;
            
            foreach ($jenisData['transaksi'] as $transaksi) {
                $totalDebet += $transaksi['debet'];
                $totalKredit += $transaksi['kredit'];
            }
            
            $summary['per_jenis'][$jenisData['jenis_id']] = [
                'nama' => $jenisData['jenis_nama'],
                'debet' => $totalDebet,
                'kredit' => $totalKredit,
                'saldo' => $totalDebet - $totalKredit,
                'jumlah_transaksi' => count($jenisData['transaksi'])
            ];
            
            $summary['total_debet'] += $totalDebet;
            $summary['total_kredit'] += $totalKredit;
        }
        
        $summary['total_saldo'] = $summary['total_debet'] - $summary['total_kredit'];
        
        return $summary;
    }

    /**
     * Get detailed savings data with proper accounting classification (OLD METHOD - KEEP FOR REFERENCE)
     * This implements the accounting principle of subsidiary ledger for savings
     */
    private function getRekapSimpanan($anggotaList, $jenisSimpanan, $periode)
    {
        $result = [];
        $tglArr = explode('-', $periode);
        $thn = $tglArr[0];
        $bln = $tglArr[1];
        $no = 1;
        
        foreach ($anggotaList as $anggota) {
            $row = [
                'no' => $no++,
                'id' => 'AG' . str_pad($anggota->id, 4, '0', STR_PAD_LEFT),
                'nama' => $anggota->nama,
                'no_ktp' => $anggota->no_ktp,
                'jabatan' => $anggota->jabatan_id == 1 ? 'Pengurus' : 'Anggota',
                'departemen' => $anggota->departement ?? '-',
                'total_simpanan' => 0,
                'total_penarikan' => 0,
                'saldo_bersih' => 0
            ];
            
            $totalDebet = 0;
            $totalKredit = 0;
            
            foreach ($jenisSimpanan as $jenis) {
                // Get detailed transaction data for this member and savings type
                $transaksiData = $this->getTransaksiDetail($anggota->no_ktp, $jenis->id, $thn, $bln);
                
                $row[$jenis->id] = [
                    'jenis_id' => $jenis->id,
                    'jenis_nama' => $jenis->jns_simpan,
                    'debet' => $transaksiData['debet'],
                    'kredit' => $transaksiData['kredit'],
                    'saldo' => $transaksiData['saldo'],
                    'transaksi_count' => $transaksiData['count'],
                    'last_transaction' => $transaksiData['last_transaction']
                ];
                
                $totalDebet += $transaksiData['debet'];
                $totalKredit += $transaksiData['kredit'];
            }
            
            $row['total_simpanan'] = $totalDebet;
            $row['total_penarikan'] = $totalKredit;
            $row['saldo_bersih'] = $totalDebet - $totalKredit;
            
            $result[] = $row;
        }
        
        return $result;
    }

    /**
     * Get detailed transaction data for specific member and savings type
     * This implements the accounting principle for savings transactions
     */
    private function getTransaksiDetail($noKtp, $jenisId, $tahun, $bulan)
    {
        // Get all transactions for this member and savings type in the period
        $transactions = TransaksiSimpanan::where('no_ktp', $noKtp)
            ->where('jenis_id', $jenisId)
            ->whereYear('tgl_transaksi', $tahun)
            ->whereMonth('tgl_transaksi', $bulan)
            ->orderBy('tgl_transaksi', 'desc')
            ->get();
        
        $debet = 0;
        $kredit = 0;
        $lastTransaction = null;
        
        foreach ($transactions as $transaction) {
            if ($transaction->dk == 'D') {
                $debet += $transaction->jumlah;
            } else {
                $kredit += $transaction->jumlah;
            }
            
            if (!$lastTransaction) {
                $lastTransaction = $transaction->tgl_transaksi->format('d/m/Y');
            }
        }
        
        return [
            'debet' => $debet,
            'kredit' => $kredit,
            'saldo' => $debet - $kredit,
            'count' => $transactions->count(),
            'last_transaction' => $lastTransaction
        ];
    }

    /**
     * Calculate summary statistics for the report
     * This provides overview of total savings activity
     */
    private function calculateSummary($data, $jenisSimpanan)
    {
        $summary = [
            'total_anggota' => count($data),
            'total_simpanan' => 0,
            'total_penarikan' => 0,
            'saldo_bersih' => 0,
            'per_jenis' => []
        ];
        
        // Calculate totals
        foreach ($data as $row) {
            $summary['total_simpanan'] += $row['total_simpanan'];
            $summary['total_penarikan'] += $row['total_penarikan'];
            $summary['saldo_bersih'] += $row['saldo_bersih'];
        }
        
        // Calculate per savings type
        foreach ($jenisSimpanan as $jenis) {
            $totalDebet = 0;
            $totalKredit = 0;
            $anggotaAktif = 0;
            
            foreach ($data as $row) {
                if (isset($row[$jenis->id])) {
                    $totalDebet += $row[$jenis->id]['debet'];
                    $totalKredit += $row[$jenis->id]['kredit'];
                    if ($row[$jenis->id]['transaksi_count'] > 0) {
                        $anggotaAktif++;
                    }
                }
            }
            
            $summary['per_jenis'][$jenis->id] = [
                'nama' => $jenis->jns_simpan,
                'debet' => $totalDebet,
                'kredit' => $totalKredit,
                'saldo' => $totalDebet - $totalKredit,
                'anggota_aktif' => $anggotaAktif
            ];
        }
        
        return $summary;
    }

    public function exportPdf(Request $request)
    {
        // Get filter parameters
        $periode = $request->input('periode', date('Y-m'));
        
        // Get data
        $jenisSimpanan = jns_simpan::whereIn('id', [31, 32, 40, 41, 51, 52])
            ->orderBy('urut')
            ->get();
        $data = $this->getRekapSimpananFromView($jenisSimpanan, $periode);
        $summary = $this->calculateSummaryFromView($data, $jenisSimpanan);
        
        // Format period text
        $periodeText = Carbon::createFromFormat('Y-m', $periode)->format('F Y');
        
        $pdf = Pdf::loadView('laporan.pdf.kas_simpanan', compact(
            'periode',
            'periodeText',
            'jenisSimpanan',
            'data',
            'summary'
        ));

        return $pdf->download('laporan_kas_simpanan_' . $periode . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        // Get filter parameters
        $periode = $request->input('periode', date('Y-m'));
        
        // Get data
        $jenisSimpanan = jns_simpan::whereIn('id', [31, 32, 40, 41, 51, 52])
            ->orderBy('urut')
            ->get();
        $data = $this->getRekapSimpananFromView($jenisSimpanan, $periode);
        $summary = $this->calculateSummaryFromView($data, $jenisSimpanan);
        
        // Format period text
        $periodeText = Carbon::createFromFormat('Y-m', $periode)->format('F Y');
        
        // Create Excel file
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set title
        $sheet->setCellValue('A1', 'LAPORAN KAS SIMPANAN');
        $sheet->mergeCells('A1:' . chr(67 + count($jenisSimpanan) * 3) . '1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
        
        // Set period info
        $sheet->setCellValue('A2', 'Periode: ' . $periodeText);
        $sheet->mergeCells('A2:' . chr(67 + count($jenisSimpanan) * 3) . '2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
        
        // Set headers
        $sheet->setCellValue('A4', 'No');
        $sheet->setCellValue('B4', 'ID Anggota');
        $sheet->setCellValue('C4', 'Nama Anggota');
        $sheet->setCellValue('D4', 'Jabatan');
        $sheet->setCellValue('E4', 'Departemen');
        
        $col = 6;
        foreach ($jenisSimpanan as $jenis) {
            $sheet->setCellValue(chr(64 + $col) . '4', $jenis->jns_simpan . ' (Setoran)');
            $sheet->setCellValue(chr(64 + $col + 1) . '4', $jenis->jns_simpan . ' (Penarikan)');
            $sheet->setCellValue(chr(64 + $col + 2) . '4', $jenis->jns_simpan . ' (Saldo)');
            $col += 3;
        }
        
        $sheet->setCellValue(chr(64 + $col) . '4', 'Total Setoran');
        $sheet->setCellValue(chr(64 + $col + 1) . '4', 'Total Penarikan');
        $sheet->setCellValue(chr(64 + $col + 2) . '4', 'Saldo Bersih');
        
        // Style headers
        $headerRange = 'A4:' . chr(64 + $col + 2) . '4';
        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E5E7EB');
        
        // Fill data
        $rowNum = 5;
        foreach ($data as $row) {
            $sheet->setCellValue('A' . $rowNum, $row['no']);
            $sheet->setCellValue('B' . $rowNum, $row['id']);
            $sheet->setCellValue('C' . $rowNum, $row['nama']);
            $sheet->setCellValue('D' . $rowNum, $row['jabatan']);
            $sheet->setCellValue('E' . $rowNum, $row['departemen']);
            
            $col = 6;
            foreach ($jenisSimpanan as $jenis) {
                if (isset($row[$jenis->id])) {
                    $sheet->setCellValue(chr(64 + $col) . $rowNum, $row[$jenis->id]['debet']);
                    $sheet->setCellValue(chr(64 + $col + 1) . $rowNum, $row[$jenis->id]['kredit']);
                    $sheet->setCellValue(chr(64 + $col + 2) . $rowNum, $row[$jenis->id]['saldo']);
                } else {
                    $sheet->setCellValue(chr(64 + $col) . $rowNum, 0);
                    $sheet->setCellValue(chr(64 + $col + 1) . $rowNum, 0);
                    $sheet->setCellValue(chr(64 + $col + 2) . $rowNum, 0);
                }
                $col += 3;
            }
            
            $sheet->setCellValue(chr(64 + $col) . $rowNum, $row['total_simpanan']);
            $sheet->setCellValue(chr(64 + $col + 1) . $rowNum, $row['total_penarikan']);
            $sheet->setCellValue(chr(64 + $col + 2) . $rowNum, $row['saldo_bersih']);
            
            $rowNum++;
        }
        
        // Add totals row
        $totalRow = $rowNum + 1;
        $sheet->setCellValue('A' . $totalRow, 'TOTAL');
        $sheet->mergeCells('A' . $totalRow . ':E' . $totalRow);
        
        $col = 6;
        foreach ($jenisSimpanan as $jenis) {
            $sheet->setCellValue(chr(64 + $col) . $totalRow, $summary['per_jenis'][$jenis->id]['debet']);
            $sheet->setCellValue(chr(64 + $col + 1) . $totalRow, $summary['per_jenis'][$jenis->id]['kredit']);
            $sheet->setCellValue(chr(64 + $col + 2) . $totalRow, $summary['per_jenis'][$jenis->id]['saldo']);
            $col += 3;
        }
        
        $sheet->setCellValue(chr(64 + $col) . $totalRow, $summary['total_simpanan']);
        $sheet->setCellValue(chr(64 + $col + 1) . $totalRow, $summary['total_penarikan']);
        $sheet->setCellValue(chr(64 + $col + 2) . $totalRow, $summary['saldo_bersih']);
        
        // Style totals
        $totalRange = 'A' . $totalRow . ':' . chr(64 + $col + 2) . $totalRow;
        $sheet->getStyle($totalRange)->getFont()->setBold(true);
        $sheet->getStyle($totalRange)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('D1D5DB');
        
        // Auto size columns
        foreach (range('A', chr(64 + $col + 2)) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Format currency columns
        $currencyRange = 'F5:' . chr(64 + $col + 2) . $totalRow;
        $sheet->getStyle($currencyRange)->getNumberFormat()->setFormatCode('#,##0');
        
        $writer = new Xlsx($spreadsheet);
        $filename = 'laporan_kas_simpanan_' . $periode . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }
}