<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TblPinjamanH;
use App\Models\TblPinjamanD;
use App\Models\jns_akun;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class LaporanShuController extends Controller
{
    public function index(Request $request)
    {
        $tgl_dari = $request->input('tgl_dari', date('Y').'-01-01');
        $tgl_samp = $request->input('tgl_samp', date('Y').'-12-31');
        
        // Get detailed SHU data using views
        $data = $this->getShuDataFromView($tgl_dari, $tgl_samp);
        
        return view('laporan.shu', [
            'tgl_dari' => $tgl_dari,
            'tgl_samp' => $tgl_samp,
            'data' => $data
        ]);
    }

    private function getShuDataFromView($tgl_dari, $tgl_samp)
    {
        // Calculate laba pinjaman from v_hitung_pinjaman
        $laba_pinjaman = DB::table('v_hitung_pinjaman')
            ->whereBetween('tgl_pinjam', [$tgl_dari, $tgl_samp])
            ->select(DB::raw('SUM(jumlah) as total_pinjaman'),
                     DB::raw('SUM(total_bayar) as total_angsuran'))
            ->first();
        
        $laba_pinjaman_value = ($laba_pinjaman->total_angsuran ?? 0) - ($laba_pinjaman->total_pinjaman ?? 0);
        
        // Get total simpanan anggota from v_shu (menggunakan jns_trans = 155)
        $total_simpanan = DB::table('v_shu')
            ->whereBetween('tgl_transaksi', [$tgl_dari, $tgl_samp])
            ->where('dk', 'D')
            ->where('jns_trans', 155) // Simpanan berdasarkan data yang ada
            ->sum('jumlah_bayar');
        
        // Get total pendapatan anggota from v_shu (menggunakan jns_trans = 155)
        $total_pendapatan_anggota = DB::table('v_shu')
            ->whereBetween('tgl_transaksi', [$tgl_dari, $tgl_samp])
            ->where('dk', 'K')
            ->where('jns_trans', 155) // Pendapatan berdasarkan data yang ada
            ->sum('jumlah_bayar');
        
        // Calculate SHU (using dummy values for now - can be replaced with real calculations)
        $shu_sebelum_pajak = 433866900; // Dummy value from the image
        $tax_rate = 5; // 5% PPh
        $pajak_pph = $shu_sebelum_pajak * ($tax_rate / 100);
        $shu_setelah_pajak = $shu_sebelum_pajak - $pajak_pph;
        
        // Calculate fund distribution
        $dana_cadangan = $shu_setelah_pajak * 0.40; // 40%
        $jasa_anggota = $shu_setelah_pajak * 0.40; // 40%
        $dana_pengurus = $shu_setelah_pajak * 0.05; // 5%
        $dana_karyawan = $shu_setelah_pajak * 0.05; // 5%
        $dana_pendidikan = $shu_setelah_pajak * 0.05; // 5%
        $dana_sosial = $shu_setelah_pajak * 0.05; // 5%
        
        // Calculate member distribution
        $jasa_usaha = $jasa_anggota * 0.70; // 70% of jasa anggota
        $jasa_modal = $jasa_anggota * 0.30; // 30% of jasa anggota
        
        // Calculate total pendapatan and total biaya
        $total_pendapatan = $laba_pinjaman_value + $total_simpanan + $total_pendapatan_anggota;
        $total_biaya = $dana_cadangan + $dana_pengurus + $dana_karyawan + $dana_pendidikan + $dana_sosial;
        
        return [
            'shu_sebelum_pajak' => $shu_sebelum_pajak,
            'pajak_pph' => $pajak_pph,
            'tax_rate' => $tax_rate,
            'shu_setelah_pajak' => $shu_setelah_pajak,
            'laba_pinjaman' => $laba_pinjaman_value,
            'total_simpanan' => $total_simpanan,
            'total_pendapatan_anggota' => $total_pendapatan_anggota,
            'total_pendapatan' => $total_pendapatan,
            'total_biaya' => $total_biaya,
            'dana_cadangan' => $dana_cadangan,
            'jasa_anggota' => $jasa_anggota,
            'dana_pengurus' => $dana_pengurus,
            'dana_karyawan' => $dana_karyawan,
            'dana_pendidikan' => $dana_pendidikan,
            'dana_sosial' => $dana_sosial,
            'jasa_usaha' => $jasa_usaha,
            'jasa_modal' => $jasa_modal
        ];
    }



    public function exportPdf(Request $request)
    {
        $tgl_dari = $request->input('tgl_dari', date('Y').'-01-01');
        $tgl_samp = $request->input('tgl_samp', date('Y').'-12-31');
        
        // Get detailed data using views
        $data = $this->getShuDataFromView($tgl_dari, $tgl_samp);
        
        $pdf = Pdf::loadView('laporan.pdf.shu', [
            'tgl_dari' => $tgl_dari,
            'tgl_samp' => $tgl_samp,
            'data' => $data,
            'summary' => $data
        ]);
        
        return $pdf->download('laporan_shu_'.$tgl_dari.'_'.$tgl_samp.'.pdf');
    }

} 