<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
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

        // Summary data for cards
        $summary = [
            'total_pendapatan' => $labaKotor->pendapatan_usaha,
            'total_hpp' => $labaKotor->hpp,
            'laba_kotor' => $labaKotor->laba_kotor,
            'total_biaya_usaha' => $labaUsaha->total_biaya_usaha,
            'laba_usaha' => $labaUsaha->laba_usaha,
            'pajak_penghasilan' => $pajakPenghasilan->pajak_penghasilan,
            'laba_usaha_setelah_pajak' => $labaUsahaSetelahPajak->laba_usaha_setelah_pajak,
            'total_shu' => $shuDistribution->total_shu
        ];

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
            'summary',
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

        // Summary data for cards
        $summary = [
            'total_pendapatan' => $labaKotor->pendapatan_usaha,
            'total_hpp' => $labaKotor->hpp,
            'laba_kotor' => $labaKotor->laba_kotor,
            'total_biaya_usaha' => $labaUsaha->total_biaya_usaha,
            'laba_usaha' => $labaUsaha->laba_usaha,
            'pajak_penghasilan' => $pajakPenghasilan->pajak_penghasilan,
            'laba_usaha_setelah_pajak' => $labaUsahaSetelahPajak->laba_usaha_setelah_pajak,
            'total_shu' => $shuDistribution->total_shu
        ];

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
            'summary',
            'tgl_dari',
            'tgl_samp',
            'tahun'
        ))->setPaper('A3', 'landscape');

        return $pdf->download('laporan_rugi_laba_toserda_' . date('Ymd') . '.pdf');
    }


    // Private methods for data retrieval and calculations
    private function getDataPenjualan($tgl_dari, $tgl_samp)
    {
        // Aggregasi data penjualan per jenis transaksi untuk menghindari banyak row
        return View_Penjualan::select('jns_trans', DB::raw('SUM(TOTAL) as TOTAL'))
            ->with('jenisAkun')
            ->whereBetween('tgl_catat', [$tgl_dari, $tgl_samp])
            ->groupBy('jns_trans')
            ->get();
    }

    private function getDataPembelian($tgl_dari, $tgl_samp)
    {
        // Aggregasi data pembelian per jenis transaksi untuk menghindari banyak row
        return View_Pembelian::select('jns_trans', DB::raw('SUM(TOTAL) as TOTAL'))
            ->with('jenisAkun')
            ->whereBetween('tgl_catat', [$tgl_dari, $tgl_samp])
            ->whereIn('kode_trans', [117, 118, 119, 120]) // Kode akun pembelian barang dagangan
            ->groupBy('jns_trans')
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
        // Aggregasi data biaya usaha per jenis transaksi untuk menghindari banyak row
        return View_BiayaUsaha::select('jns_trans', DB::raw('SUM(TOTAL) as TOTAL'))
            ->with('jenisAkun')
            ->whereBetween('tgl_catat', [$tgl_dari, $tgl_samp])
            ->groupBy('jns_trans')
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