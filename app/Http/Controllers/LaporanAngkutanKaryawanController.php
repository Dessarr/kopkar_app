<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\View_AngkutanKaryawan;
use App\Models\View_BiayaOperasional;
use App\Models\View_AdminUmum;
use App\Models\TblTransAngkutan;
use App\Models\data_mobil;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanAngkutanKaryawanController extends Controller
{
    public function index(Request $request)
    {
        $tgl_dari = $request->input('tgl_dari', date('Y') . '-01-01');
        $tgl_samp = $request->input('tgl_samp', date('Y') . '-12-31');

        // Get data bus (pendapatan)
        $dataPendapatan = $this->getDataBus($tgl_dari, $tgl_samp);
        $jmlBus = $this->getJmlBus($tgl_dari, $tgl_samp);
        $jmlBusTahun = $this->getJmlBusTahun($tgl_dari, $tgl_samp);
        $jmlBusTahunPajak = $this->getJmlBusTahunPajak($tgl_dari, $tgl_samp);

        // Get data operasional
        $dataBiayaOperasional = $this->getDataOperasional($tgl_dari, $tgl_samp);
        $jmlOperasional = $this->getJmlOperasional($tgl_dari, $tgl_samp);
        $jmlOperasionalTahun = $this->getJmlOperasionalTahun($tgl_dari, $tgl_samp);

        // Get data admin
        $dataBiayaAdmin = $this->getDataAdmin($tgl_dari, $tgl_samp);
        $jmlAdmin = $this->getJmlAdmin($tgl_dari, $tgl_samp);
        $jmlAdminTahun = $this->getJmlAdminTahun($tgl_dari, $tgl_samp);

        // Calculate Laba Usaha (Laba Bersih)
        $labaUsaha = $this->calculateLabaUsaha($jmlBus, $jmlOperasional, $jmlAdmin);
        
        // Calculate SHU Distribution
        $shuDistribution = $this->calculateShuDistribution($labaUsaha);

        // Create summary array for the view
        $summary = [
            'total_pendapatan' => $jmlBus->jml_total ?? 0,
            'total_biaya_operasional' => $jmlOperasional->jml_total ?? 0,
            'total_biaya_admin' => $jmlAdmin->jml_total ?? 0,
            'total_biaya' => ($jmlOperasional->jml_total ?? 0) + ($jmlAdmin->jml_total ?? 0),
            'laba_usaha' => $labaUsaha->laba_usaha,
            'pajak' => ($jmlBus->jml_total ?? 0) * 0.02,
            'pendapatan_setelah_pajak' => $labaUsaha->pendapatan_setelah_pajak
        ];

        return view('laporan.angkutan_karyawan', compact(
            'dataPendapatan',
            'dataBiayaOperasional', 
            'dataBiayaAdmin',
            'summary',
            'shuDistribution',
            'tgl_dari',
            'tgl_samp'
        ));
    }

    public function exportPdf(Request $request)
    {
        $tgl_dari = $request->input('tgl_dari', date('Y') . '-01-01');
        $tgl_samp = $request->input('tgl_samp', date('Y') . '-12-31');

        // Get data for PDF
        $dataPendapatan = $this->getDataBus($tgl_dari, $tgl_samp);
        $dataBiayaOperasional = $this->getDataOperasional($tgl_dari, $tgl_samp);
        $dataBiayaAdmin = $this->getDataAdmin($tgl_dari, $tgl_samp);
        
        // Get summary data
        $jmlBus = $this->getJmlBus($tgl_dari, $tgl_samp);
        $jmlOperasional = $this->getJmlOperasional($tgl_dari, $tgl_samp);
        $jmlAdmin = $this->getJmlAdmin($tgl_dari, $tgl_samp);
        $labaUsaha = $this->calculateLabaUsaha($jmlBus, $jmlOperasional, $jmlAdmin);
        $shuDistribution = $this->calculateShuDistribution($labaUsaha);

        // Create summary array
        $summary = [
            'total_pendapatan' => $jmlBus->jml_total ?? 0,
            'total_biaya_operasional' => $jmlOperasional->jml_total ?? 0,
            'total_biaya_admin' => $jmlAdmin->jml_total ?? 0,
            'total_biaya' => ($jmlOperasional->jml_total ?? 0) + ($jmlAdmin->jml_total ?? 0),
            'laba_usaha' => $labaUsaha->laba_usaha,
            'pajak' => $labaUsaha->pajak_2_persen,
            'pendapatan_setelah_pajak' => $labaUsaha->pendapatan_setelah_pajak
        ];

        $pdf = PDF::loadView('laporan.pdf.angkutan_karyawan', compact(
            'dataPendapatan',
            'dataBiayaOperasional', 
            'dataBiayaAdmin',
            'summary',
            'shuDistribution',
            'tgl_dari',
            'tgl_samp'
        ));

        return $pdf->download('laporan_angkutan_karyawan_' . date('Ymd') . '.pdf');
    }


    // Private methods for data retrieval
    private function getDataBus($tgl_dari, $tgl_samp)
    {
        return DB::table('v_angkutan_karyawan')
            ->select('no_polisi', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'TOTAL')
            ->whereBetween(DB::raw('DATE(tgl_catat)'), [$tgl_dari, $tgl_samp])
            ->groupBy('no_polisi', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'TOTAL')
            ->get();
    }

    private function getJmlBus($tgl_dari, $tgl_samp)
    {
        return DB::table('v_angkutan_karyawan')
            ->select(DB::raw('SUM(TOTAL) as jml_total'))
            ->whereBetween(DB::raw('DATE(tgl_catat)'), [$tgl_dari, $tgl_samp])
            ->first();
    }

    private function getJmlBusTahun($tgl_dari, $tgl_samp)
    {
        return DB::table('v_angkutan_karyawan')
            ->select(
                DB::raw('SUM(Jan) as jml_total_jan'),
                DB::raw('SUM(Feb) as jml_total_feb'),
                DB::raw('SUM(Mar) as jml_total_mar'),
                DB::raw('SUM(Apr) as jml_total_apr'),
                DB::raw('SUM(May) as jml_total_may'),
                DB::raw('SUM(Jun) as jml_total_jun'),
                DB::raw('SUM(Jul) as jml_total_jul'),
                DB::raw('SUM(Aug) as jml_total_aug'),
                DB::raw('SUM(Sep) as jml_total_sep'),
                DB::raw('SUM(Oct) as jml_total_oct'),
                DB::raw('SUM(Nov) as jml_total_nov'),
                DB::raw('SUM(`Dec`) as jml_total_dec')
            )
            ->whereBetween(DB::raw('DATE(tgl_catat)'), [$tgl_dari, $tgl_samp])
            ->first();
    }

    private function getJmlBusTahunPajak($tgl_dari, $tgl_samp)
    {
        return DB::table('v_angkutan_karyawan')
            ->select(
                DB::raw('(SUM(Jan)*2)/100 as jml_total_jan_pajak'),
                DB::raw('(SUM(Feb)*2)/100 as jml_total_feb_pajak'),
                DB::raw('(SUM(Mar)*2)/100 as jml_total_mar_pajak'),
                DB::raw('(SUM(Apr)*2)/100 as jml_total_apr_pajak'),
                DB::raw('(SUM(May)*2)/100 as jml_total_may_pajak'),
                DB::raw('(SUM(Jun)*2)/100 as jml_total_jun_pajak'),
                DB::raw('(SUM(Jul)*2)/100 as jml_total_jul_pajak'),
                DB::raw('(SUM(Aug)*2)/100 as jml_total_aug_pajak'),
                DB::raw('(SUM(Sep)*2)/100 as jml_total_sep_pajak'),
                DB::raw('(SUM(Oct)*2)/100 as jml_total_oct_pajak'),
                DB::raw('(SUM(Nov)*2)/100 as jml_total_nov_pajak'),
                DB::raw('(SUM(`Dec`)*2)/100 as jml_total_dec_pajak')
            )
            ->whereBetween(DB::raw('DATE(tgl_catat)'), [$tgl_dari, $tgl_samp])
            ->first();
    }

    private function getDataOperasional($tgl_dari, $tgl_samp)
    {
        return View_BiayaOperasional::select('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'TOTAL')
            ->whereBetween(DB::raw('DATE(tgl_catat)'), [$tgl_dari, $tgl_samp])
            ->groupBy('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'TOTAL')
            ->get();
    }

    private function getJmlOperasional($tgl_dari, $tgl_samp)
    {
        return View_BiayaOperasional::select(DB::raw('SUM(TOTAL) as jml_total'))
            ->whereBetween(DB::raw('DATE(tgl_catat)'), [$tgl_dari, $tgl_samp])
            ->first();
    }

    private function getJmlOperasionalTahun($tgl_dari, $tgl_samp)
    {
        return View_BiayaOperasional::select(
                DB::raw('SUM(Jan) as jml_total_jan'),
                DB::raw('SUM(Feb) as jml_total_feb'),
                DB::raw('SUM(Mar) as jml_total_mar'),
                DB::raw('SUM(Apr) as jml_total_apr'),
                DB::raw('SUM(May) as jml_total_may'),
                DB::raw('SUM(Jun) as jml_total_jun'),
                DB::raw('SUM(Jul) as jml_total_jul'),
                DB::raw('SUM(Aug) as jml_total_aug'),
                DB::raw('SUM(Sep) as jml_total_sep'),
                DB::raw('SUM(Oct) as jml_total_oct'),
                DB::raw('SUM(Nov) as jml_total_nov'),
                DB::raw('SUM(`Dec`) as jml_total_dec')
            )
            ->whereBetween(DB::raw('DATE(tgl_catat)'), [$tgl_dari, $tgl_samp])
            ->first();
    }

    private function getDataAdmin($tgl_dari, $tgl_samp)
    {
        return View_AdminUmum::select('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'TOTAL')
            ->whereBetween(DB::raw('DATE(tgl_catat)'), [$tgl_dari, $tgl_samp])
            ->groupBy('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'TOTAL')
            ->get();
    }

    private function getJmlAdmin($tgl_dari, $tgl_samp)
    {
        return View_AdminUmum::select(DB::raw('SUM(TOTAL) as jml_total'))
            ->whereBetween(DB::raw('DATE(tgl_catat)'), [$tgl_dari, $tgl_samp])
            ->first();
    }

    private function getJmlAdminTahun($tgl_dari, $tgl_samp)
    {
        return View_AdminUmum::select(
                DB::raw('SUM(Jan) as jml_total_jan'),
                DB::raw('SUM(Feb) as jml_total_feb'),
                DB::raw('SUM(Mar) as jml_total_mar'),
                DB::raw('SUM(Apr) as jml_total_apr'),
                DB::raw('SUM(May) as jml_total_may'),
                DB::raw('SUM(Jun) as jml_total_jun'),
                DB::raw('SUM(Jul) as jml_total_jul'),
                DB::raw('SUM(Aug) as jml_total_aug'),
                DB::raw('SUM(Sep) as jml_total_sep'),
                DB::raw('SUM(Oct) as jml_total_oct'),
                DB::raw('SUM(Nov) as jml_total_nov'),
                DB::raw('SUM(`Dec`) as jml_total_dec')
            )
            ->whereBetween(DB::raw('DATE(tgl_catat)'), [$tgl_dari, $tgl_samp])
            ->first();
    }

    /**
     * Calculate Laba Usaha (Net Profit)
     * Formula: Laba Usaha = Pendapatan Setelah Pajak - (Biaya Operasional + Biaya Administrasi)
     */
    private function calculateLabaUsaha($jmlBus, $jmlOperasional, $jmlAdmin)
    {
        $pendapatanSetelahPajak = ($jmlBus->jml_total ?? 0) - (($jmlBus->jml_total ?? 0) * 0.02);
        $totalBiaya = ($jmlOperasional->jml_total ?? 0) + ($jmlAdmin->jml_total ?? 0);
        $labaUsaha = $pendapatanSetelahPajak - $totalBiaya;

        return (object) [
            'pendapatan_kotor' => $jmlBus->jml_total ?? 0,
            'pajak_2_persen' => ($jmlBus->jml_total ?? 0) * 0.02,
            'pendapatan_setelah_pajak' => $pendapatanSetelahPajak,
            'biaya_operasional' => $jmlOperasional->jml_total ?? 0,
            'biaya_administrasi' => $jmlAdmin->jml_total ?? 0,
            'total_biaya' => $totalBiaya,
            'laba_usaha' => $labaUsaha
        ];
    }

    /**
     * Calculate SHU Distribution according to cooperative principles
     * Formula: SHU dibagi ke 7 kategori dana dengan persentase tetap
     */
    private function calculateShuDistribution($labaUsaha)
    {
        $laba = $labaUsaha->laba_usaha;
        
        // Jika laba negatif, tidak ada SHU yang dibagikan
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
                'laba_usaha' => $laba
            ];
        }

        return (object) [
            'dana_anggota' => $laba * 0.50,           // 50%
            'dana_cadangan' => $laba * 0.20,          // 20%
            'dana_pegawai' => $laba * 0.10,           // 10%
            'dana_pembangunan_daerah_kerja' => $laba * 0.05,  // 5%
            'dana_sosial' => $laba * 0.05,            // 5%
            'dana_kesejahteraan_pegawai' => $laba * 0.05,     // 5%
            'dana_pendidikan' => $laba * 0.05,        // 5%
            'total_shu' => $laba,
            'laba_usaha' => $laba
        ];
    }
} 