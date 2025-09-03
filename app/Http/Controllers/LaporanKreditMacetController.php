<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\View_HitungPinjaman3;
use App\Models\TblPinjamanD;
use App\Models\data_anggota;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanKreditMacetController extends Controller
{
    public function index(Request $request)
    {
        // Get filter parameters
        $periode = $request->input('periode', date('Y-m'));
        $perPage = 10;

        // Parse periode to get year and month
        $tahun = date('Y', strtotime($periode . '-01'));
        $bulan = date('m', strtotime($periode . '-01'));
        $bulanSekarang = date('m');

        // Build query using v_hitung_pinjaman_3 with joins
        $query = DB::table('v_hitung_pinjaman_3 as v')
            ->join('tbl_anggota as a', 'v.no_ktp', '=', 'a.no_ktp')
            ->select(
                'v.id',
                'v.no_ktp',
                'v.tempo',
                'v.tgl_pinjam',
                'v.tagihan',
                'v.denda_rp',
                'v.lama_angsuran',
                'v.lunas',
                'a.nama as nama_anggota'
            )
            ->whereYear('v.tempo', $tahun)
            ->whereMonth('v.tempo', '<', $bulanSekarang) // Loans past due
            ->where('v.lunas', 'Belum');

        // Get paginated data
        $dataPinjaman = $query->orderBy('v.tempo', 'asc')->paginate($perPage);

        // Calculate totals
        $totalTagihan = 0;
        $totalDibayar = 0;
        $totalSisa = 0;

        foreach ($dataPinjaman as $pinjaman) {
            // Calculate total tagihan (tagihan + denda)
            $totalTagihan += $pinjaman->tagihan + $pinjaman->denda_rp;
            
            // Get total payment from tbl_pinjaman_d
            $totalBayar = TblPinjamanD::where('pinjam_id', $pinjaman->id)
                ->sum('jumlah_bayar');
            $totalDibayar += $totalBayar;
            $totalSisa += ($pinjaman->tagihan + $pinjaman->denda_rp) - $totalBayar;
        }

        return view('laporan.kredit_macet', compact(
            'dataPinjaman',
            'periode',
            'totalTagihan',
            'totalDibayar',
            'totalSisa'
        ));
    }

    public function exportPdf(Request $request)
    {
        // Get filter parameters
        $periode = $request->input('periode', date('Y-m'));

        // Parse periode to get year and month
        $tahun = date('Y', strtotime($periode . '-01'));
        $bulan = date('m', strtotime($periode . '-01'));
        $bulanSekarang = date('m');

        // Build query using v_hitung_pinjaman_3 with joins
        $query = DB::table('v_hitung_pinjaman_3 as v')
            ->join('tbl_anggota as a', 'v.no_ktp', '=', 'a.no_ktp')
            ->select(
                'v.id',
                'v.no_ktp',
                'v.tempo',
                'v.tgl_pinjam',
                'v.tagihan',
                'v.denda_rp',
                'v.lama_angsuran',
                'v.lunas',
                'a.nama as nama_anggota'
            )
            ->whereYear('v.tempo', $tahun)
            ->whereMonth('v.tempo', '<', $bulanSekarang) // Loans past due
            ->where('v.lunas', 'Belum');

        // Get all data for PDF
        $dataPinjaman = $query->orderBy('v.tempo', 'asc')->get();

        // Calculate totals
        $totalTagihan = 0;
        $totalDibayar = 0;
        $totalSisa = 0;

        foreach ($dataPinjaman as $pinjaman) {
            // Calculate total tagihan (tagihan + denda)
            $totalTagihan += $pinjaman->tagihan + $pinjaman->denda_rp;
            
            // Get total payment from tbl_pinjaman_d
            $totalBayar = TblPinjamanD::where('pinjam_id', $pinjaman->id)
                ->sum('jumlah_bayar');
            $totalDibayar += $totalBayar;
            $totalSisa += ($pinjaman->tagihan + $pinjaman->denda_rp) - $totalBayar;
        }

        // Format periode text
        $periodeText = Carbon::parse($periode . '-01')->format('F Y');

        $pdf = Pdf::loadView('laporan.pdf.kredit_macet', compact(
            'dataPinjaman',
            'periodeText',
            'totalTagihan',
            'totalDibayar',
            'totalSisa'
        ));

        return $pdf->download('laporan_kredit_macet_' . date('Ymd') . '.pdf');
    }
} 