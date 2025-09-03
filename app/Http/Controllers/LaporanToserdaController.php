<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\View_Penjualan;
use App\Models\View_Pembelian;
use App\Models\View_PersediaanAwal;
use App\Models\View_BiayaUsaha;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LaporanToserdaController extends Controller
{
    public function index(Request $request)
    {
        // Default date range: 1 Jan - 31 Des tahun berjalan
        $tahun = $request->get('tahun', date('Y'));
        $tgl_dari = $request->get('tgl_dari', $tahun . '-01-01');
        $tgl_samp = $request->get('tgl_samp', $tahun . '-12-31');

        // Get data for the report
        $dataPenjualan = $this->getDataPenjualan($tgl_dari, $tgl_samp);
        $dataPembelian = $this->getDataPembelian($tgl_dari, $tgl_samp);
        $dataPersediaanAwal = $this->getDataPersediaanAwal($tgl_dari, $tgl_samp);
        $dataBiayaUsaha = $this->getDataBiayaUsaha($tgl_dari, $tgl_samp);

        // Calculate financial metrics
        $labaKotor = $this->calculateLabaKotor($dataPenjualan, $dataPembelian, $dataPersediaanAwal);
        $labaUsaha = $this->calculateLabaUsaha($labaKotor, $dataBiayaUsaha);
        $pajakPenghasilan = $this->calculatePajakPenghasilan($labaUsaha);
        $labaUsahaSetelahPajak = $this->calculateLabaUsahaSetelahPajak($labaUsaha, $pajakPenghasilan);
        $shuDistribution = $this->calculateShuDistribution($labaUsahaSetelahPajak);

        return view('laporan.toserda', compact(
            'dataPenjualan',
            'dataPembelian', 
            'dataPersediaanAwal',
            'dataBiayaUsaha',
            'labaKotor',
            'labaUsaha',
            'pajakPenghasilan',
            'labaUsahaSetelahPajak',
            'shuDistribution',
            'tgl_dari',
            'tgl_samp',
            'tahun'
        ));
    }

    public function exportPdf(Request $request)
    {
        // Get the same data as index method
        $tahun = $request->get('tahun', date('Y'));
        $tgl_dari = $request->get('tgl_dari', $tahun . '-01-01');
        $tgl_samp = $request->get('tgl_samp', $tahun . '-12-31');

        $dataPenjualan = $this->getDataPenjualan($tgl_dari, $tgl_samp);
        $dataPembelian = $this->getDataPembelian($tgl_dari, $tgl_samp);
        $dataPersediaanAwal = $this->getDataPersediaanAwal($tgl_dari, $tgl_samp);
        $dataBiayaUsaha = $this->getDataBiayaUsaha($tgl_dari, $tgl_samp);

        $labaKotor = $this->calculateLabaKotor($dataPenjualan, $dataPembelian, $dataPersediaanAwal);
        $labaUsaha = $this->calculateLabaUsaha($labaKotor, $dataBiayaUsaha);
        $pajakPenghasilan = $this->calculatePajakPenghasilan($labaUsaha);
        $labaUsahaSetelahPajak = $this->calculateLabaUsahaSetelahPajak($labaUsaha, $pajakPenghasilan);
        $shuDistribution = $this->calculateShuDistribution($labaUsahaSetelahPajak);

        $pdf = Pdf::loadView('laporan.pdf.toserda', compact(
            'dataPenjualan',
            'dataPembelian',
            'dataPersediaanAwal', 
            'dataBiayaUsaha',
            'labaKotor',
            'labaUsaha',
            'pajakPenghasilan',
            'labaUsahaSetelahPajak',
            'shuDistribution',
            'tgl_dari',
            'tgl_samp',
            'tahun'
        ))->setPaper('A3', 'landscape');

        return $pdf->download('laporan_rugi_laba_toserda_' . date('Ymd') . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        $tahun = $request->get('tahun', date('Y'));
        $tgl_dari = $request->get('tgl_dari', $tahun . '-01-01');
        $tgl_samp = $request->get('tgl_samp', $tahun . '-12-31');

        $dataPenjualan = $this->getDataPenjualan($tgl_dari, $tgl_samp);
        $dataPembelian = $this->getDataPembelian($tgl_dari, $tgl_samp);
        $dataPersediaanAwal = $this->getDataPersediaanAwal($tgl_dari, $tgl_samp);
        $dataBiayaUsaha = $this->getDataBiayaUsaha($tgl_dari, $tgl_samp);

        $labaKotor = $this->calculateLabaKotor($dataPenjualan, $dataPembelian, $dataPersediaanAwal);
        $labaUsaha = $this->calculateLabaUsaha($labaKotor, $dataBiayaUsaha);
        $pajakPenghasilan = $this->calculatePajakPenghasilan($labaUsaha);
        $labaUsahaSetelahPajak = $this->calculateLabaUsahaSetelahPajak($labaUsaha, $pajakPenghasilan);
        $shuDistribution = $this->calculateShuDistribution($labaUsahaSetelahPajak);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set title
        $sheet->setCellValue('A1', 'LAPORAN RUGI LABA TOSERDA');
        $sheet->mergeCells('A1:D1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        // Set period
        $sheet->setCellValue('A2', 'Periode: ' . Carbon::parse($tgl_dari)->format('d F Y') . ' - ' . Carbon::parse($tgl_samp)->format('d F Y'));
        $sheet->mergeCells('A2:D2');

        $row = 4;

        // Pendapatan Usaha
        $sheet->setCellValue('A' . $row, 'PENDAPATAN USAHA');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;

        $totalPendapatan = 0;
        foreach ($dataPenjualan as $penjualan) {
            $sheet->setCellValue('A' . $row, $penjualan->jenisAkun->nama_akun ?? 'Pendapatan');
            $sheet->setCellValue('D' . $row, number_format($penjualan->TOTAL ?? 0, 0, ',', '.'));
            $totalPendapatan += $penjualan->TOTAL ?? 0;
            $row++;
        }
        $sheet->setCellValue('A' . $row, 'Total Pendapatan Usaha');
        $sheet->setCellValue('D' . $row, number_format($totalPendapatan, 0, ',', '.'));
        $sheet->getStyle('A' . $row . ':D' . $row)->getFont()->setBold(true);
        $row += 2;

        // HPP
        $sheet->setCellValue('A' . $row, 'HARGA POKOK PENJUALAN');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;

        $totalPembelian = 0;
        foreach ($dataPembelian as $pembelian) {
            $sheet->setCellValue('A' . $row, $pembelian->jenisAkun->nama_akun ?? 'Pembelian');
            $sheet->setCellValue('D' . $row, number_format($pembelian->TOTAL ?? 0, 0, ',', '.'));
            $totalPembelian += $pembelian->TOTAL ?? 0;
            $row++;
        }

        $persediaanAwal = $dataPersediaanAwal->sum('persediaan_awal_jan') ?? 0;
        $persediaanAkhir = $dataPersediaanAwal->sum('persediaan_awal_dec') ?? 0;

        $sheet->setCellValue('A' . $row, 'Persediaan Awal');
        $sheet->setCellValue('D' . $row, number_format($persediaanAwal, 0, ',', '.'));
        $row++;

        $barangTersedia = $totalPembelian + $persediaanAwal;
        $sheet->setCellValue('A' . $row, 'Barang Tersedia untuk Dijual');
        $sheet->setCellValue('D' . $row, number_format($barangTersedia, 0, ',', '.'));
        $row++;

        $sheet->setCellValue('A' . $row, 'Persediaan Akhir');
        $sheet->setCellValue('D' . $row, number_format($persediaanAkhir, 0, ',', '.'));
        $row++;

        $hpp = $barangTersedia - $persediaanAkhir;
        $sheet->setCellValue('A' . $row, 'Harga Pokok Penjualan');
        $sheet->setCellValue('D' . $row, number_format($hpp, 0, ',', '.'));
        $sheet->getStyle('A' . $row . ':D' . $row)->getFont()->setBold(true);
        $row += 2;

        // Laba Kotor
        $labaKotorValue = $totalPendapatan - $hpp;
        $sheet->setCellValue('A' . $row, 'LABA KOTOR');
        $sheet->setCellValue('D' . $row, number_format($labaKotorValue, 0, ',', '.'));
        $sheet->getStyle('A' . $row . ':D' . $row)->getFont()->setBold(true);
        $row += 2;

        // Biaya Usaha
        $sheet->setCellValue('A' . $row, 'BIAYA-BIAYA USAHA');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;

        $totalBiayaUsaha = 0;
        foreach ($dataBiayaUsaha as $biaya) {
            $sheet->setCellValue('A' . $row, $biaya->jenisAkun->nama_akun ?? 'Biaya');
            $sheet->setCellValue('D' . $row, number_format($biaya->TOTAL ?? 0, 0, ',', '.'));
            $totalBiayaUsaha += $biaya->TOTAL ?? 0;
            $row++;
        }
        $sheet->setCellValue('A' . $row, 'Total Biaya Usaha');
        $sheet->setCellValue('D' . $row, number_format($totalBiayaUsaha, 0, ',', '.'));
        $sheet->getStyle('A' . $row . ':D' . $row)->getFont()->setBold(true);
        $row += 2;

        // Laba Usaha
        $labaUsahaValue = $labaKotorValue - $totalBiayaUsaha;
        $sheet->setCellValue('A' . $row, 'LABA USAHA');
        $sheet->setCellValue('D' . $row, number_format($labaUsahaValue, 0, ',', '.'));
        $sheet->getStyle('A' . $row . ':D' . $row)->getFont()->setBold(true);
        $row += 2;

        // Pajak Penghasilan
        $pajakValue = $labaUsahaValue * 0.125;
        $sheet->setCellValue('A' . $row, 'Pajak Penghasilan (12.5%)');
        $sheet->setCellValue('D' . $row, number_format($pajakValue, 0, ',', '.'));
        $row += 2;

        // Laba Usaha Setelah Pajak
        $labaSetelahPajak = $labaUsahaValue - $pajakValue;
        $sheet->setCellValue('A' . $row, 'LABA USAHA SETELAH PAJAK');
        $sheet->setCellValue('D' . $row, number_format($labaSetelahPajak, 0, ',', '.'));
        $sheet->getStyle('A' . $row . ':D' . $row)->getFont()->setBold(true);
        $row += 2;

        // SHU Distribution
        $sheet->setCellValue('A' . $row, 'SHU YANG DIBAGIKAN');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;

        $sheet->setCellValue('A' . $row, 'Dana Anggota (50%)');
        $sheet->setCellValue('D' . $row, number_format($labaSetelahPajak * 0.50, 0, ',', '.'));
        $row++;

        $sheet->setCellValue('A' . $row, 'Dana Cadangan (20%)');
        $sheet->setCellValue('D' . $row, number_format($labaSetelahPajak * 0.20, 0, ',', '.'));
        $row++;

        $sheet->setCellValue('A' . $row, 'Dana Pegawai (10%)');
        $sheet->setCellValue('D' . $row, number_format($labaSetelahPajak * 0.10, 0, ',', '.'));
        $row++;

        $sheet->setCellValue('A' . $row, 'Dana Pembangunan Daerah Kerja (5%)');
        $sheet->setCellValue('D' . $row, number_format($labaSetelahPajak * 0.05, 0, ',', '.'));
        $row++;

        $sheet->setCellValue('A' . $row, 'Dana Sosial (5%)');
        $sheet->setCellValue('D' . $row, number_format($labaSetelahPajak * 0.05, 0, ',', '.'));
        $row++;

        $sheet->setCellValue('A' . $row, 'Dana Kesejahteraan Pegawai (5%)');
        $sheet->setCellValue('D' . $row, number_format($labaSetelahPajak * 0.05, 0, ',', '.'));
        $row++;

        $sheet->setCellValue('A' . $row, 'Dana Pendidikan (5%)');
        $sheet->setCellValue('D' . $row, number_format($labaSetelahPajak * 0.05, 0, ',', '.'));
        $row++;

        $sheet->setCellValue('A' . $row, 'Total SHU');
        $sheet->setCellValue('D' . $row, number_format($labaSetelahPajak, 0, ',', '.'));
        $sheet->getStyle('A' . $row . ':D' . $row)->getFont()->setBold(true);

        // Auto size columns
        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'laporan_rugi_laba_toserda_' . date('Ymd') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    // Private methods for data retrieval and calculations
    private function getDataPenjualan($tgl_dari, $tgl_samp)
    {
        return View_Penjualan::select('jns_trans', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'TOTAL')
            ->with('jenisAkun')
            ->whereBetween('tgl_catat', [$tgl_dari, $tgl_samp])
            ->get();
    }

    private function getDataPembelian($tgl_dari, $tgl_samp)
    {
        return View_Pembelian::select('kode_trans', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'TOTAL')
            ->with('jenisAkun')
            ->whereBetween('tgl_catat', [$tgl_dari, $tgl_samp])
            ->whereIn('kode_trans', [117, 118, 119, 120]) // Kode akun pembelian barang dagangan
            ->get();
    }

    private function getDataPersediaanAwal($tgl_dari, $tgl_samp)
    {
        return View_PersediaanAwal::select('persediaan_awal_jan', 'persediaan_awal_feb', 'persediaan_awal_mar', 'persediaan_awal_apr', 'persediaan_awal_may', 'persediaan_awal_jun', 'persediaan_awal_jul', 'persediaan_awal_aug', 'persediaan_awal_sep', 'persediaan_awal_oct', 'persediaan_awal_nov', 'persediaan_awal_dec')
            ->whereBetween('tgl_catat', [$tgl_dari, $tgl_samp])
            ->get();
    }

    private function getDataBiayaUsaha($tgl_dari, $tgl_samp)
    {
        return View_BiayaUsaha::select('jns_trans', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'TOTAL')
            ->with('jenisAkun')
            ->whereBetween('tgl_catat', [$tgl_dari, $tgl_samp])
            ->get();
    }

    private function calculateLabaKotor($dataPenjualan, $dataPembelian, $dataPersediaanAwal)
    {
        $totalPendapatan = $dataPenjualan->sum('TOTAL');
        $totalPembelian = $dataPembelian->sum('TOTAL');
        $persediaanAwal = $dataPersediaanAwal->sum('persediaan_awal_jan');
        $persediaanAkhir = $dataPersediaanAwal->sum('persediaan_awal_dec');
        
        $barangTersedia = $totalPembelian + $persediaanAwal;
        $hpp = $barangTersedia - $persediaanAkhir;
        $labaKotor = $totalPendapatan - $hpp;

        return (object) [
            'pendapatan_usaha' => $totalPendapatan,
            'pembelian_bersih' => $totalPembelian,
            'persediaan_awal' => $persediaanAwal,
            'persediaan_akhir' => $persediaanAkhir,
            'barang_tersedia' => $barangTersedia,
            'hpp' => $hpp,
            'laba_kotor' => $labaKotor
        ];
    }

    private function calculateLabaUsaha($labaKotor, $dataBiayaUsaha)
    {
        $totalBiayaUsaha = $dataBiayaUsaha->sum('TOTAL');
        $labaUsaha = $labaKotor->laba_kotor - $totalBiayaUsaha;

        return (object) [
            'laba_kotor' => $labaKotor->laba_kotor,
            'total_biaya_usaha' => $totalBiayaUsaha,
            'laba_usaha' => $labaUsaha
        ];
    }

    private function calculatePajakPenghasilan($labaUsaha)
    {
        $pajak = $labaUsaha->laba_usaha * 0.125; // 12.5%

        return (object) [
            'laba_usaha' => $labaUsaha->laba_usaha,
            'tarif_pajak' => 0.125,
            'pajak_penghasilan' => $pajak
        ];
    }

    private function calculateLabaUsahaSetelahPajak($labaUsaha, $pajakPenghasilan)
    {
        $labaSetelahPajak = $labaUsaha->laba_usaha - $pajakPenghasilan->pajak_penghasilan;

        return (object) [
            'laba_usaha' => $labaUsaha->laba_usaha,
            'pajak_penghasilan' => $pajakPenghasilan->pajak_penghasilan,
            'laba_usaha_setelah_pajak' => $labaSetelahPajak
        ];
    }

    private function calculateShuDistribution($labaUsahaSetelahPajak)
    {
        $laba = $labaUsahaSetelahPajak->laba_usaha_setelah_pajak;
        
        if ($laba <= 0) {
            return (object) [
                'dana_anggota' => 0,
                'dana_cadangan' => 0,
                'dana_pegawai' => 0,
                'dana_pembangunan_daerah_kerja' => 0,
                'dana_sosial' => 0,
                'dana_kesejahteraan_pegawai' => 0,
                'dana_pendidikan' => 0,
                'total_shu' => 0,
                'laba_usaha_setelah_pajak' => $laba
            ];
        }

        return (object) [
            'dana_anggota' => $laba * 0.50,
            'dana_cadangan' => $laba * 0.20,
            'dana_pegawai' => $laba * 0.10,
            'dana_pembangunan_daerah_kerja' => $laba * 0.05,
            'dana_sosial' => $laba * 0.05,
            'dana_kesejahteraan_pegawai' => $laba * 0.05,
            'dana_pendidikan' => $laba * 0.05,
            'total_shu' => $laba,
            'laba_usaha_setelah_pajak' => $laba
        ];
    }
} 