<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\data_anggota;
use App\Models\jns_simpan;
use App\Models\TransaksiSimpanan;
use App\Models\TblTransSp;
use App\Models\TblTransTagihan;
use App\Models\TblTransToserda;
use App\Models\TblPinjamanH;
use App\Models\TblPinjamanD;
use App\Models\View_SimpananBayarTanggal;
use App\Models\View_SimpananTagihanTanggal;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LaporanKasAnggotaController extends Controller
{
    public function index(Request $request)
    {
        $periode = $request->input('periode', date('Y-m'));
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10); // Fixed to 10 data per page as per specification

        // Parse periode
        $tgl_arr = explode('-', $periode);
        $tahun = $tgl_arr[0];
        $bulan = $tgl_arr[1];

        $query = data_anggota::where('aktif', 'Y');

        // Search berdasarkan nama, no KTP, atau ID anggota
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('no_ktp', 'like', '%' . $search . '%')
                  ->orWhere('id', 'like', '%' . $search . '%');
            });
        }

        $dataAnggota = $query->orderBy('nama', 'asc')->paginate($perPage);

        // Get jenis simpanan yang ditampilkan (Pokok, Wajib, Sukarela, Khusus 1&2, Tabungan Perumahan)
        $jenisSimpanan = jns_simpan::where('tampil', 'Y')
            ->whereIn('id', [41, 32, 52, 40, 51, 31]) // Based on specification
            ->orderBy('urut', 'asc')
            ->get();

        // Get data kas untuk setiap anggota
        $kasData = [];
        foreach ($dataAnggota as $anggota) {
            $kasData[$anggota->no_ktp] = $this->getKasData($anggota->no_ktp, $tahun, $bulan);
        }

        // Statistik
        $totalAnggota = data_anggota::where('aktif', 'Y')->count();
        $totalSimpanan = $this->getTotalSimpanan($tahun, $bulan);
        $totalPenarikan = $this->getTotalPenarikan($tahun, $bulan);
        $totalSaldo = $totalSimpanan - $totalPenarikan;

        return view('laporan.kas_anggota', compact(
            'dataAnggota',
            'jenisSimpanan',
            'kasData',
            'periode',
            'tahun',
            'bulan',
            'search',
            'perPage',
            'totalAnggota',
            'totalSimpanan',
            'totalPenarikan',
            'totalSaldo'
        ));
    }

    public function exportPdf(Request $request)
    {
        $periode = $request->input('periode', date('Y-m'));
        $search = $request->input('search');

        $tgl_arr = explode('-', $periode);
        $tahun = $tgl_arr[0];
        $bulan = $tgl_arr[1];

        $query = data_anggota::where('aktif', 'Y');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('no_ktp', 'like', '%' . $search . '%')
                  ->orWhere('no_anggota', 'like', '%' . $search . '%');
            });
        }

        $dataAnggota = $query->orderBy('nama', 'asc')->get();
        $jenisSimpanan = jns_simpan::where('tampil', 'Y')
            ->whereIn('id', [41, 32, 52, 40, 51, 31])
            ->orderBy('urut', 'asc')
            ->get();

        $kasData = [];
        foreach ($dataAnggota as $anggota) {
            $kasData[$anggota->no_ktp] = $this->getKasData($anggota->no_ktp, $tahun, $bulan);
        }

        $pdf = PDF::loadView('laporan.pdf.kas_anggota', compact(
            'dataAnggota',
            'jenisSimpanan',
            'kasData',
            'periode',
            'tahun',
            'bulan'
        ));

        return $pdf->download('laporan_kas_anggota_' . date('Ymd') . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        $periode = $request->input('periode', date('Y-m'));
        $search = $request->input('search');

        $tgl_arr = explode('-', $periode);
        $tahun = $tgl_arr[0];
        $bulan = $tgl_arr[1];

        $query = data_anggota::where('aktif', 'Y');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('no_ktp', 'like', '%' . $search . '%')
                  ->orWhere('no_anggota', 'like', '%' . $search . '%');
            });
        }

        $dataAnggota = $query->orderBy('nama', 'asc')->get();
        $jenisSimpanan = jns_simpan::where('tampil', 'Y')
            ->whereIn('id', [41, 32, 52, 40, 51, 31])
            ->orderBy('urut', 'asc')
            ->get();

        $kasData = [];
        foreach ($dataAnggota as $anggota) {
            $kasData[$anggota->no_ktp] = $this->getKasData($anggota->no_ktp, $tahun, $bulan);
        }

        // Create Excel file
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set title
        $sheet->setCellValue('A1', 'LAPORAN KAS ANGGOTA KOPERASI');
        $sheet->setCellValue('A2', 'Periode: ' . Carbon::createFromDate($tahun, $bulan, 1)->format('F Y'));
        $sheet->setCellValue('A3', 'Tanggal: ' . Carbon::now()->format('d/m/Y H:i:s'));

        // Merge cells for title
        $lastCol = chr(65 + count($jenisSimpanan) * 3 + 3); // A + jumlah jenis simpanan * 3 + 3 kolom tambahan
        $sheet->mergeCells('A1:' . $lastCol . '1');
        $sheet->mergeCells('A2:' . $lastCol . '2');
        $sheet->mergeCells('A3:' . $lastCol . '3');

        // Style title
        $sheet->getStyle('A1:A3')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1:A3')->getAlignment()->setHorizontal('center');

        // Header table
        $row = 5;
        $col = 'A';
        
        // Basic headers
        $headers = ['No', 'No Anggota', 'Nama', 'No KTP'];
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $col++;
        }

        // Jenis simpanan headers
        foreach ($jenisSimpanan as $jenis) {
            $sheet->setCellValue($col . $row, $jenis->jns_simpan . ' Setor');
            $col++;
            $sheet->setCellValue($col . $row, $jenis->jns_simpan . ' Tarik');
            $col++;
            $sheet->setCellValue($col . $row, $jenis->jns_simpan . ' Saldo');
            $col++;
        }

        // Additional headers
        $additionalHeaders = ['Total Setor', 'Total Tarik', 'Total Saldo', 'Tagihan', 'Bayar', 'Sisa'];
        foreach ($additionalHeaders as $header) {
            $sheet->setCellValue($col . $row, $header);
            $col++;
        }

        // Style header
        $sheet->getStyle('A5:' . $lastCol . '5')->getFont()->setBold(true);
        $sheet->getStyle('A5:' . $lastCol . '5')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('CCCCCC');

        // Data anggota
        $row = 6;
        $no = 1;
        foreach ($dataAnggota as $anggota) {
            $col = 'A';
            
            // Basic data
            $sheet->setCellValue($col . $row, $no);
            $col++;
            $sheet->setCellValue($col . $row, $anggota->no_anggota);
            $col++;
            $sheet->setCellValue($col . $row, $anggota->nama);
            $col++;
            $sheet->setCellValue($col . $row, $anggota->no_ktp);
            $col++;

            $kas = $kasData[$anggota->no_ktp] ?? [];

            // Jenis simpanan data
            foreach ($jenisSimpanan as $jenis) {
                $setor = $kas['setoran'][$jenis->id] ?? 0;
                $tarik = $kas['penarikan'][$jenis->id] ?? 0;
                $saldo = $setor - $tarik;

                $sheet->setCellValue($col . $row, $setor);
                $col++;
                $sheet->setCellValue($col . $row, $tarik);
                $col++;
                $sheet->setCellValue($col . $row, $saldo);
                $col++;
            }

            // Additional data
            $totalSetor = $kas['total_setor'] ?? 0;
            $totalTarik = $kas['total_tarik'] ?? 0;
            $totalSaldo = $kas['total_saldo'] ?? 0;
            $tagihan = $kas['tagihan'] ?? 0;
            $bayar = $kas['bayar'] ?? 0;
            $sisa = $kas['sisa'] ?? 0;

            $sheet->setCellValue($col . $row, $totalSetor);
            $col++;
            $sheet->setCellValue($col . $row, $totalTarik);
            $col++;
            $sheet->setCellValue($col . $row, $totalSaldo);
            $col++;
            $sheet->setCellValue($col . $row, $tagihan);
            $col++;
            $sheet->setCellValue($col . $row, $bayar);
            $col++;
            $sheet->setCellValue($col . $row, $sisa);

            $row++;
            $no++;
        }

        // Auto size columns
        foreach (range('A', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Set number format for currency columns
        $sheet->getStyle('E6:' . $lastCol . ($row - 1))->getNumberFormat()->setFormatCode('#,##0');

        // Create file
        $writer = new Xlsx($spreadsheet);
        $filename = 'laporan_kas_anggota_' . date('Ymd') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    /**
     * Export Excel Detail (Lengkap) - cetak_excel()
     */
    public function exportExcelDetail(Request $request)
    {
        $periode = $request->input('periode', date('Y-m'));
        $search = $request->input('search');

        $tgl_arr = explode('-', $periode);
        $tahun = $tgl_arr[0];
        $bulan = $tgl_arr[1];

        $query = data_anggota::where('aktif', 'Y');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('no_ktp', 'like', '%' . $search . '%')
                  ->orWhere('id', 'like', '%' . $search . '%');
            });
        }

        $dataAnggota = $query->orderBy('nama', 'asc')->get();
        $jenisSimpanan = jns_simpan::where('tampil', 'Y')
            ->whereIn('id', [41, 32, 52, 40, 51, 31])
            ->orderBy('urut', 'asc')
            ->get();

        $kasData = [];
        foreach ($dataAnggota as $anggota) {
            $kasData[$anggota->no_ktp] = $this->getKasData($anggota->no_ktp, $tahun, $bulan);
        }

        // Create Excel file with comprehensive data
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set title
        $sheet->setCellValue('A1', 'LAPORAN DATA KAS PER ANGGOTA');
        $sheet->setCellValue('A2', 'Periode: ' . Carbon::createFromDate($tahun, $bulan, 1)->format('F Y'));
        $sheet->setCellValue('A3', 'Tanggal: ' . Carbon::now()->format('d/m/Y H:i:s'));

        // Merge cells for title
        $lastCol = chr(65 + 4 + (count($jenisSimpanan) * 3) + 6); // A + 4 basic + jenis simpanan * 3 + 6 additional
        $sheet->mergeCells('A1:' . $lastCol . '1');
        $sheet->mergeCells('A2:' . $lastCol . '2');
        $sheet->mergeCells('A3:' . $lastCol . '3');

        // Style title
        $sheet->getStyle('A1:A3')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1:A3')->getAlignment()->setHorizontal('center');

        // Header table
        $row = 5;
        $col = 'A';
        
        // Basic headers
        $headers = ['No', 'ID Anggota', 'Nama', 'No KTP'];
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $col++;
        }

        // Jenis simpanan headers
        foreach ($jenisSimpanan as $jenis) {
            $sheet->setCellValue($col . $row, $jenis->jns_simpan . ' Setor');
            $col++;
            $sheet->setCellValue($col . $row, $jenis->jns_simpan . ' Tarik');
            $col++;
            $sheet->setCellValue($col . $row, $jenis->jns_simpan . ' Saldo');
            $col++;
        }

        // Additional headers
        $additionalHeaders = ['Total Setor', 'Total Tarik', 'Total Saldo', 'Tagihan Kredit', 'Bayar Kredit', 'Sisa Kredit'];
        foreach ($additionalHeaders as $header) {
            $sheet->setCellValue($col . $row, $header);
            $col++;
        }

        // Style header
        $sheet->getStyle('A5:' . $lastCol . '5')->getFont()->setBold(true);
        $sheet->getStyle('A5:' . $lastCol . '5')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('CCCCCC');

        // Data anggota
        $row = 6;
        $no = 1;
        foreach ($dataAnggota as $anggota) {
            $col = 'A';
            
            // Basic data
            $sheet->setCellValue($col . $row, $no);
            $col++;
            $sheet->setCellValue($col . $row, 'AG' . str_pad($anggota->id, 4, '0', STR_PAD_LEFT));
            $col++;
            $sheet->setCellValue($col . $row, $anggota->nama);
            $col++;
            $sheet->setCellValue($col . $row, $anggota->no_ktp);
            $col++;

            $kas = $kasData[$anggota->no_ktp] ?? [];

            // Jenis simpanan data
            foreach ($jenisSimpanan as $jenis) {
                $setor = $kas['setoran'][$jenis->id] ?? 0;
                $tarik = $kas['penarikan'][$jenis->id] ?? 0;
                $saldo = $setor - $tarik;

                $sheet->setCellValue($col . $row, $setor);
                $col++;
                $sheet->setCellValue($col . $row, $tarik);
                $col++;
                $sheet->setCellValue($col . $row, $saldo);
                $col++;
            }

            // Additional data
            $totalSetor = $kas['total_setor'] ?? 0;
            $totalTarik = $kas['total_tarik'] ?? 0;
            $totalSaldo = $kas['total_saldo'] ?? 0;
            $tagihanKredit = $kas['tagihan_kredit'] ?? 0;
            $bayarKredit = $kas['bayar_kredit'] ?? 0;
            $sisaKredit = $kas['sisa_kredit'] ?? 0;

            $sheet->setCellValue($col . $row, $totalSetor);
            $col++;
            $sheet->setCellValue($col . $row, $totalTarik);
            $col++;
            $sheet->setCellValue($col . $row, $totalSaldo);
            $col++;
            $sheet->setCellValue($col . $row, $tagihanKredit);
            $col++;
            $sheet->setCellValue($col . $row, $bayarKredit);
            $col++;
            $sheet->setCellValue($col . $row, $sisaKredit);

            $row++;
            $no++;
        }

        // Auto size columns
        foreach (range('A', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Set number format for currency columns
        $sheet->getStyle('E6:' . $lastCol . ($row - 1))->getNumberFormat()->setFormatCode('#,##0');

        // Create file
        $writer = new Xlsx($spreadsheet);
        $filename = 'laporan_kas_anggota_detail_' . date('Ymd') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    /**
     * Export Excel Tagihan - cetak_tagihan()
     */
    public function exportExcelTagihan(Request $request)
    {
        $periode = $request->input('periode', date('Y-m'));
        $search = $request->input('search');

        $tgl_arr = explode('-', $periode);
        $tahun = $tgl_arr[0];
        $bulan = $tgl_arr[1];

        $query = data_anggota::where('aktif', 'Y');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('no_ktp', 'like', '%' . $search . '%')
                  ->orWhere('id', 'like', '%' . $search . '%');
            });
        }

        $dataAnggota = $query->orderBy('nama', 'asc')->get();

        // Create Excel file focused on billing
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set title
        $sheet->setCellValue('A1', 'LAPORAN TAGIHAN ANGGOTA');
        $sheet->setCellValue('A2', 'Periode: ' . Carbon::createFromDate($tahun, $bulan, 1)->format('F Y'));
        $sheet->setCellValue('A3', 'Tanggal: ' . Carbon::now()->format('d/m/Y H:i:s'));

        // Merge cells for title
        $lastCol = 'J';
        $sheet->mergeCells('A1:' . $lastCol . '1');
        $sheet->mergeCells('A2:' . $lastCol . '2');
        $sheet->mergeCells('A3:' . $lastCol . '3');

        // Style title
        $sheet->getStyle('A1:A3')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1:A3')->getAlignment()->setHorizontal('center');

        // Header table
        $row = 5;
        $headers = ['No', 'ID Anggota', 'Nama', 'No KTP', 'Tagihan Simpanan', 'Tagihan Kredit', 'Total Tagihan', 'Bayar', 'Sisa', 'Status'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $col++;
        }

        // Style header
        $sheet->getStyle('A5:' . $lastCol . '5')->getFont()->setBold(true);
        $sheet->getStyle('A5:' . $lastCol . '5')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('CCCCCC');

        // Data anggota
        $row = 6;
        $no = 1;
        foreach ($dataAnggota as $anggota) {
            $kas = $this->getKasData($anggota->no_ktp, $tahun, $bulan);
            
            $sheet->setCellValue('A' . $row, $no);
            $sheet->setCellValue('B' . $row, 'AG' . str_pad($anggota->id, 4, '0', STR_PAD_LEFT));
            $sheet->setCellValue('C' . $row, $anggota->nama);
            $sheet->setCellValue('D' . $row, $anggota->no_ktp);
            $sheet->setCellValue('E' . $row, $kas['tagihan_simpanan'] ?? 0);
            $sheet->setCellValue('F' . $row, $kas['tagihan_kredit'] ?? 0);
            $sheet->setCellValue('G' . $row, ($kas['tagihan_simpanan'] ?? 0) + ($kas['tagihan_kredit'] ?? 0));
            $sheet->setCellValue('H' . $row, $kas['bayar'] ?? 0);
            $sheet->setCellValue('I' . $row, $kas['sisa'] ?? 0);
            $sheet->setCellValue('J' . $row, ($kas['sisa'] ?? 0) > 0 ? 'Belum Lunas' : 'Lunas');

            $row++;
            $no++;
        }

        // Auto size columns
        foreach (range('A', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Set number format for currency columns
        $sheet->getStyle('E6:' . $lastCol . ($row - 1))->getNumberFormat()->setFormatCode('#,##0');

        // Create file
        $writer = new Xlsx($spreadsheet);
        $filename = 'laporan_tagihan_anggota_' . date('Ymd') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    /**
     * Export Excel Simpanan - cetak_simpanan()
     */
    public function exportExcelSimpanan(Request $request)
    {
        $periode = $request->input('periode', date('Y-m'));
        $search = $request->input('search');

        $tgl_arr = explode('-', $periode);
        $tahun = $tgl_arr[0];
        $bulan = $tgl_arr[1];

        $query = data_anggota::where('aktif', 'Y');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('no_ktp', 'like', '%' . $search . '%')
                  ->orWhere('id', 'like', '%' . $search . '%');
            });
        }

        $dataAnggota = $query->orderBy('nama', 'asc')->get();
        $jenisSimpanan = jns_simpan::where('tampil', 'Y')
            ->whereIn('id', [41, 32, 52, 40, 51, 31])
            ->orderBy('urut', 'asc')
            ->get();

        // Create Excel file focused on savings
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set title
        $sheet->setCellValue('A1', 'LAPORAN SIMPANAN ANGGOTA');
        $sheet->setCellValue('A2', 'Periode: ' . Carbon::createFromDate($tahun, $bulan, 1)->format('F Y'));
        $sheet->setCellValue('A3', 'Tanggal: ' . Carbon::now()->format('d/m/Y H:i:s'));

        // Merge cells for title
        $lastCol = chr(65 + 4 + (count($jenisSimpanan) * 3) + 3); // A + 4 basic + jenis simpanan * 3 + 3 additional
        $sheet->mergeCells('A1:' . $lastCol . '1');
        $sheet->mergeCells('A2:' . $lastCol . '2');
        $sheet->mergeCells('A3:' . $lastCol . '3');

        // Style title
        $sheet->getStyle('A1:A3')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1:A3')->getAlignment()->setHorizontal('center');

        // Header table
        $row = 5;
        $col = 'A';
        
        // Basic headers
        $headers = ['No', 'ID Anggota', 'Nama', 'No KTP'];
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $col++;
        }

        // Jenis simpanan headers
        foreach ($jenisSimpanan as $jenis) {
            $sheet->setCellValue($col . $row, $jenis->jns_simpan . ' Setor');
            $col++;
            $sheet->setCellValue($col . $row, $jenis->jns_simpan . ' Tarik');
            $col++;
            $sheet->setCellValue($col . $row, $jenis->jns_simpan . ' Saldo');
            $col++;
        }

        // Additional headers
        $additionalHeaders = ['Total Setor', 'Total Tarik', 'Total Saldo'];
        foreach ($additionalHeaders as $header) {
            $sheet->setCellValue($col . $row, $header);
            $col++;
        }

        // Style header
        $sheet->getStyle('A5:' . $lastCol . '5')->getFont()->setBold(true);
        $sheet->getStyle('A5:' . $lastCol . '5')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('CCCCCC');

        // Data anggota
        $row = 6;
        $no = 1;
        foreach ($dataAnggota as $anggota) {
            $col = 'A';
            $kas = $this->getKasData($anggota->no_ktp, $tahun, $bulan);
            
            // Basic data
            $sheet->setCellValue($col . $row, $no);
            $col++;
            $sheet->setCellValue($col . $row, 'AG' . str_pad($anggota->id, 4, '0', STR_PAD_LEFT));
            $col++;
            $sheet->setCellValue($col . $row, $anggota->nama);
            $col++;
            $sheet->setCellValue($col . $row, $anggota->no_ktp);
            $col++;

            // Jenis simpanan data
            foreach ($jenisSimpanan as $jenis) {
                $setor = $kas['setoran'][$jenis->id] ?? 0;
                $tarik = $kas['penarikan'][$jenis->id] ?? 0;
                $saldo = $setor - $tarik;

                $sheet->setCellValue($col . $row, $setor);
                $col++;
                $sheet->setCellValue($col . $row, $tarik);
                $col++;
                $sheet->setCellValue($col . $row, $saldo);
                $col++;
            }

            // Additional data
            $totalSetor = $kas['total_setor'] ?? 0;
            $totalTarik = $kas['total_tarik'] ?? 0;
            $totalSaldo = $kas['total_saldo'] ?? 0;

            $sheet->setCellValue($col . $row, $totalSetor);
            $col++;
            $sheet->setCellValue($col . $row, $totalTarik);
            $col++;
            $sheet->setCellValue($col . $row, $totalSaldo);

            $row++;
            $no++;
        }

        // Auto size columns
        foreach (range('A', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Set number format for currency columns
        $sheet->getStyle('E6:' . $lastCol . ($row - 1))->getNumberFormat()->setFormatCode('#,##0');

        // Create file
        $writer = new Xlsx($spreadsheet);
        $filename = 'laporan_simpanan_anggota_' . date('Ymd') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    private function getKasData($noKtp, $tahun, $bulan)
    {
        $data = [
            'setoran' => [],
            'penarikan' => [],
            'total_setor' => 0,
            'total_tarik' => 0,
            'total_saldo' => 0,
            'tagihan' => 0,
            'bayar' => 0,
            'sisa' => 0,
            'tagihan_simpanan' => 0,
            'tagihan_kredit' => 0,
            'bayar_kredit' => 0,
            'sisa_kredit' => 0
        ];

        // Get jenis simpanan
        $jenisSimpanan = jns_simpan::where('tampil', 'Y')
            ->whereIn('id', [41, 32, 52, 40, 51, 31])
            ->orderBy('urut', 'asc')
            ->get();

        foreach ($jenisSimpanan as $jenis) {
            // Get setoran
            $setoran = TransaksiSimpanan::where('no_ktp', $noKtp)
                ->where('jenis_id', $jenis->id)
                ->where('akun', 'setoran')
                ->whereYear('tgl_transaksi', $tahun)
                ->whereMonth('tgl_transaksi', $bulan)
                ->sum('jumlah');

            // Get penarikan
            $penarikan = TransaksiSimpanan::where('no_ktp', $noKtp)
                ->where('jenis_id', $jenis->id)
                ->where('akun', 'penarikan')
                ->whereYear('tgl_transaksi', $tahun)
                ->whereMonth('tgl_transaksi', $bulan)
                ->sum('jumlah');

            $data['setoran'][$jenis->id] = $setoran;
            $data['penarikan'][$jenis->id] = $penarikan;
            $data['total_setor'] += $setoran;
            $data['total_tarik'] += $penarikan;
        }

        $data['total_saldo'] = $data['total_setor'] - $data['total_tarik'];

        // Get tagihan simpanan
        $tagihanSimpanan = TblTransTagihan::where('no_ktp', $noKtp)
            ->whereYear('tgl_transaksi', $tahun)
            ->whereMonth('tgl_transaksi', $bulan)
            ->sum('jumlah');

        // Get tagihan kredit (pinjaman)
        $tagihanKredit = TblPinjamanH::where('no_ktp', $noKtp)
            ->where('lunas', 'N')
            ->whereYear('tgl_pinjam', $tahun)
            ->whereMonth('tgl_pinjam', $bulan)
            ->sum('jumlah_angsuran');

        // Get bayar kredit
        $bayarKredit = TblPinjamanD::whereHas('pinjaman', function($q) use ($noKtp) {
                $q->where('no_ktp', $noKtp);
            })
            ->whereYear('tgl_bayar', $tahun)
            ->whereMonth('tgl_bayar', $bulan)
            ->sum('jumlah_bayar');

        // Get total bayar (simpanan + kredit)
        $bayar = TblTransSp::where('no_ktp', $noKtp)
            ->whereYear('tgl_transaksi', $tahun)
            ->whereMonth('tgl_transaksi', $bulan)
            ->sum('jumlah');

        $data['tagihan_simpanan'] = $tagihanSimpanan;
        $data['tagihan_kredit'] = $tagihanKredit;
        $data['tagihan'] = $tagihanSimpanan + $tagihanKredit;
        $data['bayar_kredit'] = $bayarKredit;
        $data['bayar'] = $bayar + $bayarKredit;
        $data['sisa_kredit'] = $tagihanKredit - $bayarKredit;
        $data['sisa'] = $data['tagihan'] - $data['bayar'];

        return $data;
    }

    private function getTotalSimpanan($tahun, $bulan)
    {
        return TransaksiSimpanan::where('akun', 'setoran')
            ->whereYear('tgl_transaksi', $tahun)
            ->whereMonth('tgl_transaksi', $bulan)
            ->sum('jumlah');
    }

    private function getTotalPenarikan($tahun, $bulan)
    {
        return TransaksiSimpanan::where('akun', 'penarikan')
            ->whereYear('tgl_transaksi', $tahun)
            ->whereMonth('tgl_transaksi', $bulan)
            ->sum('jumlah');
    }
} 