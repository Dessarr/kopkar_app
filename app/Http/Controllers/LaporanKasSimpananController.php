<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\data_anggota;
use App\Models\TransaksiSimpanan;
use App\Models\jns_simpan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

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
        
        // Get unique members count
        $uniqueMembers = [];
        
        foreach ($data as $jenisData) {
            $totalDebet = 0;
            $totalKredit = 0;
            
            foreach ($jenisData['transaksi'] as $transaksi) {
                $totalDebet += $transaksi['debet'];
                $totalKredit += $transaksi['kredit'];
                
                // Collect unique members by name (since anggota_id is not available)
                if (!in_array($transaksi['nama'], $uniqueMembers)) {
                    $uniqueMembers[] = $transaksi['nama'];
                }
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
        
        // Add missing fields for PDF view
        $summary['total_saldo'] = $summary['total_debet'] - $summary['total_kredit'];
        $summary['total_anggota'] = count($uniqueMembers);
        $summary['total_simpanan'] = $summary['total_debet']; // Simpanan = debet
        $summary['total_penarikan'] = $summary['total_kredit']; // Penarikan = kredit
        $summary['saldo_bersih'] = $summary['total_saldo'];
        
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

}