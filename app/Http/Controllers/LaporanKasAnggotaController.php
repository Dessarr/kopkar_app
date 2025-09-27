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
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LaporanKasAnggotaController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('per_page', 20); // Increased for better display

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

        // Get data untuk setiap anggota (menggunakan logika yang sama dengan dashboard member)
        $anggotaData = [];
        foreach ($dataAnggota as $anggota) {
            $anggotaData[$anggota->no_ktp] = [
                'anggota' => $anggota,
                'identitas' => $this->getIdentitasAnggota($anggota),
                'saldo_simpanan' => $this->hitungSaldoSimpanan($anggota->no_ktp),
                'tagihan_kredit' => $this->hitungTagihanKredit($anggota->no_ktp),
                'keterangan' => $this->hitungKeteranganPinjaman($anggota->no_ktp)
            ];
        }

        // Statistik - hanya total anggota dan total saldo
        $totalAnggota = data_anggota::where('aktif', 'Y')->count();
        
        // Hitung total saldo dari semua anggota
        $totalSaldo = 0;
        foreach ($anggotaData as $data) {
            $saldo = $data['saldo_simpanan'];
            $totalSaldo += ($saldo->simpanan_wajib ?? 0) + 
                          ($saldo->simpanan_sukarela ?? 0) + 
                          ($saldo->simpanan_khusus_2 ?? 0) + 
                          ($saldo->simpanan_pokok ?? 0) + 
                          ($saldo->simpanan_khusus_1 ?? 0) + 
                          ($saldo->tab_perumahan ?? 0);
        }

        return view('laporan.kas_anggota', compact(
            'dataAnggota',
            'anggotaData',
            'search',
            'perPage',
            'totalAnggota',
            'totalSaldo'
        ));
    }

    public function exportPdf(Request $request)
    {
        $search = $request->input('search');

        $query = data_anggota::where('aktif', 'Y');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('no_ktp', 'like', '%' . $search . '%')
                  ->orWhere('id', 'like', '%' . $search . '%');
            });
        }

        $dataAnggota = $query->orderBy('nama', 'asc')->get();

        // Hitung data menggunakan method yang sama dengan web view
        $anggotaData = [];
        foreach ($dataAnggota as $anggota) {
            $anggotaData[$anggota->no_ktp] = [
                'anggota' => $anggota,
                'identitas' => $this->getIdentitasAnggota($anggota),
                'saldo_simpanan' => $this->hitungSaldoSimpanan($anggota->no_ktp),
                'tagihan_kredit' => $this->hitungTagihanKredit($anggota->no_ktp),
                'keterangan' => $this->hitungKeteranganPinjaman($anggota->no_ktp)
            ];
        }

        // Statistik
        $totalAnggota = $dataAnggota->count();
        
        // Hitung total saldo dari semua anggota
        $totalSaldo = 0;
        foreach ($anggotaData as $data) {
            $saldo = $data['saldo_simpanan'];
            $totalSaldo += ($saldo->simpanan_wajib ?? 0) + 
                          ($saldo->simpanan_sukarela ?? 0) + 
                          ($saldo->simpanan_khusus_2 ?? 0) + 
                          ($saldo->simpanan_pokok ?? 0) + 
                          ($saldo->simpanan_khusus_1 ?? 0) + 
                          ($saldo->tab_perumahan ?? 0);
        }

        $pdf = PDF::loadView('laporan.pdf.kas_anggota', compact(
            'dataAnggota',
            'anggotaData',
            'search',
            'totalAnggota',
            'totalSaldo'
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
        $search = $request->input('search');

        $query = data_anggota::where('aktif', 'Y');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('no_ktp', 'like', '%' . $search . '%')
                  ->orWhere('id', 'like', '%' . $search . '%');
            });
        }

        $dataAnggota = $query->orderBy('nama', 'asc')->get();

        // Hitung data menggunakan method yang sama dengan web view
        $anggotaData = [];
        foreach ($dataAnggota as $anggota) {
            $anggotaData[$anggota->no_ktp] = [
                'anggota' => $anggota,
                'identitas' => $this->getIdentitasAnggota($anggota),
                'saldo_simpanan' => $this->hitungSaldoSimpanan($anggota->no_ktp),
                'tagihan_kredit' => $this->hitungTagihanKredit($anggota->no_ktp),
                'keterangan' => $this->hitungKeteranganPinjaman($anggota->no_ktp)
            ];
        }

        // Create Excel file with comprehensive data
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set title
        $sheet->setCellValue('A1', 'LAPORAN DATA KAS PER ANGGOTA');
        $sheet->setCellValue('A2', 'Periode: ' . Carbon::now()->format('F Y'));
        $sheet->setCellValue('A3', 'Tanggal: ' . Carbon::now()->format('d/m/Y H:i:s'));
        if ($search) {
            $sheet->setCellValue('A4', 'Hasil pencarian untuk: "' . $search . '"');
        }

        // Merge cells for title
        $lastCol = 'V';
        $sheet->mergeCells('A1:' . $lastCol . '1');
        $sheet->mergeCells('A2:' . $lastCol . '2');
        $sheet->mergeCells('A3:' . $lastCol . '3');
        if ($search) {
            $sheet->mergeCells('A4:' . $lastCol . '4');
        }

        // Style title
        $titleRow = $search ? 4 : 3;
        $sheet->getStyle('A1:A' . $titleRow)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1:A' . $titleRow)->getAlignment()->setHorizontal('center');

        // Header table
        $row = $titleRow + 2;
        $headers = ['No', 'ID Anggota', 'Nama', 'No KTP', 'Jenis Kelamin', 'Alamat', 'Telp', 'Simpanan Wajib', 'Simpanan Sukarela', 'Simpanan Khusus II', 'Simpanan Pokok', 'Simpanan Khusus I', 'Tab. Perumahan', 'Total Simpanan', 'Pinjaman Biasa', 'Sisa Pinjaman Biasa', 'Pinjaman Barang', 'Sisa Pinjaman Barang', 'Jumlah Pinjaman', 'Pinjaman Lunas', 'Status Pembayaran', 'Tanggal Tempo'];
        
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $col++;
        }

        // Style header
        $sheet->getStyle('A' . $row . ':' . $lastCol . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':' . $lastCol . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('CCCCCC');

        // Data anggota
        $row = $titleRow + 3;
        $no = 1;
        foreach ($dataAnggota as $anggota) {
            // Get calculated data
            $data = $anggotaData[$anggota->no_ktp] ?? [];
            $identitas = $data['identitas'] ?? [];
            $saldoSimpanan = $data['saldo_simpanan'] ?? (object)[];
            $tagihanKredit = $data['tagihan_kredit'] ?? (object)[];
            $keterangan = $data['keterangan'] ?? (object)[];

            // Calculate totals
            $totalSimpanan = ($saldoSimpanan->simpanan_wajib ?? 0) + 
                           ($saldoSimpanan->simpanan_sukarela ?? 0) + 
                           ($saldoSimpanan->simpanan_khusus_2 ?? 0) + 
                           ($saldoSimpanan->simpanan_pokok ?? 0) + 
                           ($saldoSimpanan->simpanan_khusus_1 ?? 0) + 
                           ($saldoSimpanan->tab_perumahan ?? 0);

            $sheet->setCellValue('A' . $row, $no);
            $sheet->setCellValue('B' . $row, $identitas['id_anggota'] ?? '-');
            $sheet->setCellValue('C' . $row, $identitas['nama'] ?? '-');
            $sheet->setCellValue('D' . $row, $anggota->no_ktp);
            $sheet->setCellValue('E' . $row, $identitas['jenis_kelamin'] ?? '-');
            $sheet->setCellValue('F' . $row, $identitas['alamat'] ?? '-');
            $sheet->setCellValue('G' . $row, $identitas['telp'] ?? '-');
            $sheet->setCellValue('H' . $row, $saldoSimpanan->simpanan_wajib ?? 0);
            $sheet->setCellValue('I' . $row, $saldoSimpanan->simpanan_sukarela ?? 0);
            $sheet->setCellValue('J' . $row, $saldoSimpanan->simpanan_khusus_2 ?? 0);
            $sheet->setCellValue('K' . $row, $saldoSimpanan->simpanan_pokok ?? 0);
            $sheet->setCellValue('L' . $row, $saldoSimpanan->simpanan_khusus_1 ?? 0);
            $sheet->setCellValue('M' . $row, $saldoSimpanan->tab_perumahan ?? 0);
            $sheet->setCellValue('N' . $row, $totalSimpanan);
            $sheet->setCellValue('O' . $row, $tagihanKredit->pinjaman_biasa ?? 0);
            $sheet->setCellValue('P' . $row, $tagihanKredit->sisa_pinjaman_biasa ?? 0);
            $sheet->setCellValue('Q' . $row, $tagihanKredit->pinjaman_barang ?? 0);
            $sheet->setCellValue('R' . $row, $tagihanKredit->sisa_pinjaman_barang ?? 0);
            $sheet->setCellValue('S' . $row, $keterangan->jumlah_pinjaman ?? 0);
            $sheet->setCellValue('T' . $row, $keterangan->pinjaman_lunas ?? 0);
            $sheet->setCellValue('U' . $row, $keterangan->status_pembayaran ?? 'Lancar');
            $sheet->setCellValue('V' . $row, $keterangan->tanggal_tempo ?? '-');

            $row++;
            $no++;
        }

        // Auto size columns
        foreach (range('A', 'V') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Set number format for currency columns
        $sheet->getStyle('H' . ($titleRow + 3) . ':V' . ($row - 1))->getNumberFormat()->setFormatCode('#,##0');

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
        $search = $request->input('search');

        $query = data_anggota::where('aktif', 'Y');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('no_ktp', 'like', '%' . $search . '%')
                  ->orWhere('id', 'like', '%' . $search . '%');
            });
        }

        $dataAnggota = $query->orderBy('nama', 'asc')->get();

        // Hitung data menggunakan method yang sama dengan web view
        $anggotaData = [];
        foreach ($dataAnggota as $anggota) {
            $anggotaData[$anggota->no_ktp] = [
                'anggota' => $anggota,
                'identitas' => $this->getIdentitasAnggota($anggota),
                'saldo_simpanan' => $this->hitungSaldoSimpanan($anggota->no_ktp),
                'tagihan_kredit' => $this->hitungTagihanKredit($anggota->no_ktp),
                'keterangan' => $this->hitungKeteranganPinjaman($anggota->no_ktp)
            ];
        }

        // Create Excel file focused on billing
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set title
        $sheet->setCellValue('A1', 'LAPORAN TAGIHAN ANGGOTA');
        $sheet->setCellValue('A2', 'Periode: ' . Carbon::now()->format('F Y'));
        $sheet->setCellValue('A3', 'Tanggal: ' . Carbon::now()->format('d/m/Y H:i:s'));
        if ($search) {
            $sheet->setCellValue('A4', 'Hasil pencarian untuk: "' . $search . '"');
        }

        // Merge cells for title
        $lastCol = 'J';
        $sheet->mergeCells('A1:' . $lastCol . '1');
        $sheet->mergeCells('A2:' . $lastCol . '2');
        $sheet->mergeCells('A3:' . $lastCol . '3');
        if ($search) {
            $sheet->mergeCells('A4:' . $lastCol . '4');
        }

        // Style title
        $titleRow = $search ? 4 : 3;
        $sheet->getStyle('A1:A' . $titleRow)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1:A' . $titleRow)->getAlignment()->setHorizontal('center');

        // Header table
        $row = $titleRow + 2;
        $headers = ['No', 'ID Anggota', 'Nama', 'No KTP', 'Tagihan Simpanan', 'Tagihan Kredit', 'Total Tagihan', 'Bayar', 'Sisa', 'Status'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $col++;
        }

        // Style header
        $sheet->getStyle('A' . $row . ':' . $lastCol . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':' . $lastCol . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('CCCCCC');

        // Data anggota
        $row = $titleRow + 3;
        $no = 1;
        foreach ($dataAnggota as $anggota) {
            // Get calculated data
            $data = $anggotaData[$anggota->no_ktp] ?? [];
            $identitas = $data['identitas'] ?? [];
            $saldoSimpanan = $data['saldo_simpanan'] ?? (object)[];
            $tagihanKredit = $data['tagihan_kredit'] ?? (object)[];
            
            // Calculate totals
            $totalSimpanan = ($saldoSimpanan->simpanan_wajib ?? 0) + 
                           ($saldoSimpanan->simpanan_sukarela ?? 0) + 
                           ($saldoSimpanan->simpanan_khusus_2 ?? 0) + 
                           ($saldoSimpanan->simpanan_pokok ?? 0) + 
                           ($saldoSimpanan->simpanan_khusus_1 ?? 0) + 
                           ($saldoSimpanan->tab_perumahan ?? 0);
            
            $totalKredit = ($tagihanKredit->pinjaman_biasa ?? 0) + ($tagihanKredit->pinjaman_barang ?? 0);
            $totalTagihan = $totalSimpanan + $totalKredit;
            $bayar = 0; // Tidak ada data pembayaran dalam struktur ini
            $sisa = $totalTagihan - $bayar;
            
            $sheet->setCellValue('A' . $row, $no);
            $sheet->setCellValue('B' . $row, $identitas['id_anggota'] ?? '-');
            $sheet->setCellValue('C' . $row, $identitas['nama'] ?? '-');
            $sheet->setCellValue('D' . $row, $anggota->no_ktp);
            $sheet->setCellValue('E' . $row, $totalSimpanan);
            $sheet->setCellValue('F' . $row, $totalKredit);
            $sheet->setCellValue('G' . $row, $totalTagihan);
            $sheet->setCellValue('H' . $row, $bayar);
            $sheet->setCellValue('I' . $row, $sisa);
            $sheet->setCellValue('J' . $row, $sisa > 0 ? 'Belum Lunas' : 'Lunas');

            $row++;
            $no++;
        }

        // Auto size columns
        foreach (range('A', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Set number format for currency columns
        $sheet->getStyle('E' . ($titleRow + 3) . ':' . $lastCol . ($row - 1))->getNumberFormat()->setFormatCode('#,##0');

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
        $search = $request->input('search');

        $query = data_anggota::where('aktif', 'Y');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('no_ktp', 'like', '%' . $search . '%')
                  ->orWhere('id', 'like', '%' . $search . '%');
            });
        }

        $dataAnggota = $query->orderBy('nama', 'asc')->get();

        // Hitung data menggunakan method yang sama dengan web view
        $anggotaData = [];
        foreach ($dataAnggota as $anggota) {
            $anggotaData[$anggota->no_ktp] = [
                'anggota' => $anggota,
                'identitas' => $this->getIdentitasAnggota($anggota),
                'saldo_simpanan' => $this->hitungSaldoSimpanan($anggota->no_ktp),
                'tagihan_kredit' => $this->hitungTagihanKredit($anggota->no_ktp),
                'keterangan' => $this->hitungKeteranganPinjaman($anggota->no_ktp)
            ];
        }

        // Create Excel file focused on savings
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set title
        $sheet->setCellValue('A1', 'LAPORAN SIMPANAN ANGGOTA');
        $sheet->setCellValue('A2', 'Periode: ' . Carbon::now()->format('F Y'));
        $sheet->setCellValue('A3', 'Tanggal: ' . Carbon::now()->format('d/m/Y H:i:s'));
        if ($search) {
            $sheet->setCellValue('A4', 'Hasil pencarian untuk: "' . $search . '"');
        }

        // Merge cells for title
        $lastCol = 'N';
        $sheet->mergeCells('A1:' . $lastCol . '1');
        $sheet->mergeCells('A2:' . $lastCol . '2');
        $sheet->mergeCells('A3:' . $lastCol . '3');
        if ($search) {
            $sheet->mergeCells('A4:' . $lastCol . '4');
        }

        // Style title
        $titleRow = $search ? 4 : 3;
        $sheet->getStyle('A1:A' . $titleRow)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1:A' . $titleRow)->getAlignment()->setHorizontal('center');

        // Header table
        $row = $titleRow + 2;
        $headers = ['No', 'ID Anggota', 'Nama', 'No KTP', 'Simpanan Wajib', 'Simpanan Sukarela', 'Simpanan Khusus II', 'Simpanan Pokok', 'Simpanan Khusus I', 'Tab. Perumahan', 'Jumlah Simpanan'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $col++;
        }

        // Style header
        $sheet->getStyle('A' . $row . ':' . $lastCol . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':' . $lastCol . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('CCCCCC');

        // Data anggota
        $row = $titleRow + 3;
        $no = 1;
        foreach ($dataAnggota as $anggota) {
            // Get calculated data
            $data = $anggotaData[$anggota->no_ktp] ?? [];
            $identitas = $data['identitas'] ?? [];
            $saldoSimpanan = $data['saldo_simpanan'] ?? (object)[];
            
            // Calculate totals
            $totalSimpanan = ($saldoSimpanan->simpanan_wajib ?? 0) + 
                           ($saldoSimpanan->simpanan_sukarela ?? 0) + 
                           ($saldoSimpanan->simpanan_khusus_2 ?? 0) + 
                           ($saldoSimpanan->simpanan_pokok ?? 0) + 
                           ($saldoSimpanan->simpanan_khusus_1 ?? 0) + 
                           ($saldoSimpanan->tab_perumahan ?? 0);

            $sheet->setCellValue('A' . $row, $no);
            $sheet->setCellValue('B' . $row, $identitas['id_anggota'] ?? '-');
            $sheet->setCellValue('C' . $row, $identitas['nama'] ?? '-');
            $sheet->setCellValue('D' . $row, $anggota->no_ktp);
            $sheet->setCellValue('E' . $row, $saldoSimpanan->simpanan_wajib ?? 0);
            $sheet->setCellValue('F' . $row, $saldoSimpanan->simpanan_sukarela ?? 0);
            $sheet->setCellValue('G' . $row, $saldoSimpanan->simpanan_khusus_2 ?? 0);
            $sheet->setCellValue('H' . $row, $saldoSimpanan->simpanan_pokok ?? 0);
            $sheet->setCellValue('I' . $row, $saldoSimpanan->simpanan_khusus_1 ?? 0);
            $sheet->setCellValue('J' . $row, $saldoSimpanan->tab_perumahan ?? 0);
            $sheet->setCellValue('K' . $row, $totalSimpanan);

            $row++;
            $no++;
        }

        // Auto size columns
        foreach (range('A', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Set number format for currency columns
        $sheet->getStyle('E' . ($titleRow + 3) . ':' . $lastCol . ($row - 1))->getNumberFormat()->setFormatCode('#,##0');

        // Create file
        $writer = new Xlsx($spreadsheet);
        $filename = 'laporan_simpanan_anggota_' . date('Ymd') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    /**
     * Get identitas anggota data
     */
    private function getIdentitasAnggota($anggota)
    {
        return [
            'id_anggota' => $anggota->no_ktp,
            'nama' => $anggota->nama,
            'jenis_kelamin' => $anggota->jk == 'L' ? 'Laki-laki' : 'Perempuan',
            'alamat' => $anggota->alamat,
            'telp' => $anggota->notelp
        ];
    }

    /**
     * Hitung saldo simpanan (konsisten dengan dashboard member)
     */
    private function hitungSaldoSimpanan($noKtp)
    {
        return DB::table('tbl_trans_sp')
            ->selectRaw('
                SUM(CASE WHEN jenis_id = 41 AND dk = "D" THEN jumlah ELSE 0 END) - 
                SUM(CASE WHEN jenis_id = 41 AND dk = "K" THEN jumlah ELSE 0 END) as simpanan_wajib,
                
                SUM(CASE WHEN jenis_id = 32 AND dk = "D" THEN jumlah ELSE 0 END) - 
                SUM(CASE WHEN jenis_id = 32 AND dk = "K" THEN jumlah ELSE 0 END) as simpanan_sukarela,
                
                SUM(CASE WHEN jenis_id = 52 AND dk = "D" THEN jumlah ELSE 0 END) - 
                SUM(CASE WHEN jenis_id = 52 AND dk = "K" THEN jumlah ELSE 0 END) as simpanan_khusus_2,
                
                SUM(CASE WHEN jenis_id = 40 AND dk = "D" THEN jumlah ELSE 0 END) - 
                SUM(CASE WHEN jenis_id = 40 AND dk = "K" THEN jumlah ELSE 0 END) as simpanan_pokok,
                
                SUM(CASE WHEN jenis_id = 51 AND dk = "D" THEN jumlah ELSE 0 END) - 
                SUM(CASE WHEN jenis_id = 51 AND dk = "K" THEN jumlah ELSE 0 END) as simpanan_khusus_1,
                
                SUM(CASE WHEN jenis_id = 156 AND dk = "D" THEN jumlah ELSE 0 END) - 
                SUM(CASE WHEN jenis_id = 156 AND dk = "K" THEN jumlah ELSE 0 END) as tab_perumahan
            ')
            ->where('no_ktp', $noKtp)
            ->first();
    }

    /**
     * Hitung tagihan kredit menggunakan view seperti dashboard member
     */
    private function hitungTagihanKredit($noKtp)
    {
        return DB::table('v_hitung_pinjaman')
            ->selectRaw('
                SUM(CASE WHEN jenis_pinjaman = 1 THEN jumlah ELSE 0 END) as pinjaman_biasa,
                SUM(CASE WHEN jenis_pinjaman = 1 AND lunas = "Belum" THEN sisa_pokok ELSE 0 END) as sisa_pinjaman_biasa,
                SUM(CASE WHEN jenis_pinjaman = 3 THEN jumlah ELSE 0 END) as pinjaman_barang,
                SUM(CASE WHEN jenis_pinjaman = 3 AND lunas = "Belum" THEN sisa_pokok ELSE 0 END) as sisa_pinjaman_barang
            ')
            ->where('no_ktp', $noKtp)
            ->where('status', '1')
            ->first();
    }

    /**
     * Hitung keterangan pinjaman (konsisten dengan dashboard member)
     */
    private function hitungKeteranganPinjaman($noKtp)
    {
        $data = DB::table('tbl_pinjaman_h as p')
            ->selectRaw('
                COUNT(*) as jumlah_pinjaman,
                SUM(CASE WHEN p.lunas = "Lunas" THEN 1 ELSE 0 END) as pinjaman_lunas
            ')
            ->where('p.no_ktp', $noKtp)
            ->where('p.status', '1')
            ->first();
        
        // Logika sederhana seperti project lama
        $statusPembayaran = 'Lancar';
        $tanggalTempo = '-';
        
        if ($data->jumlah_pinjaman > 0) {
            $pinjamanAktif = DB::table('tbl_pinjaman_h')
                ->where('no_ktp', $noKtp)
                ->where('status', '1')
                ->where('lunas', 'Belum')
                ->first();
            
            if ($pinjamanAktif) {
                $bulanTempo = date('m', strtotime($pinjamanAktif->tgl_pinjam));
                $bulanSekarang = date('m');
                
                if ($bulanSekarang > $bulanTempo) {
                    $statusPembayaran = 'Macet';
                }
                
                $tanggalTempo = date('d/m/Y', strtotime($pinjamanAktif->tgl_pinjam));
            }
        }
        
        $data->status_pembayaran = $statusPembayaran;
        $data->tanggal_tempo = $tanggalTempo;
        return $data;
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

    private function getKeteranganPinjaman($no_ktp)
    {
        try {
            // Jumlah Pinjaman
            $jumlah_pinjaman = TblPinjamanH::where('no_ktp', $no_ktp)->count();
            
            // Pinjaman Lunas
            $pinjaman_lunas = TblPinjamanH::where('no_ktp', $no_ktp)
                ->where('lunas', 'Lunas')
                ->count();
                
            // Status Pembayaran (default Lancar)
            $status_pembayaran = 'Lancar';
            
            // Tanggal Tempo (default -)
            $tanggal_tempo = '-';
            
            return [
                'jumlah_pinjaman' => $jumlah_pinjaman,
                'pinjaman_lunas' => $pinjaman_lunas,
                'status_pembayaran' => $status_pembayaran,
                'tanggal_tempo' => $tanggal_tempo
            ];
        } catch (\Exception $e) {
            Log::error('Error getting keterangan pinjaman:', ['error' => $e->getMessage()]);
            return [
                'jumlah_pinjaman' => 0,
                'pinjaman_lunas' => 0,
                'status_pembayaran' => 'Lancar',
                'tanggal_tempo' => '-'
            ];
        }
    }

} 