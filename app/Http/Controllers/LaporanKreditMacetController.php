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

        // Build query using v_hitung_pinjaman_3 with joins and aggregation
        $query = DB::table('v_hitung_pinjaman_3 as v')
            ->join('tbl_anggota as a', 'v.no_ktp', '=', 'a.no_ktp')
            ->leftJoin('tbl_pinjaman_d as d', function($join) {
                $join->on('v.id', '=', 'd.pinjam_id');
            })
            ->select(
                'v.id',
                'v.no_ktp',
                'v.tempo',
                'v.tgl_pinjam',
                'v.tagihan',
                'v.denda_rp',
                'v.lama_angsuran',
                'v.lunas',
                'a.nama as nama_anggota',
                DB::raw('COALESCE(SUM(d.jumlah_bayar), 0) as total_bayar')
            )
            ->whereYear('v.tempo', $tahun)
            ->whereMonth('v.tempo', '<', $bulanSekarang) // Loans past due
            ->where('v.lunas', 'Belum')
            ->groupBy('v.id', 'v.no_ktp', 'v.tempo', 'v.tgl_pinjam', 'v.tagihan', 'v.denda_rp', 'v.lama_angsuran', 'v.lunas', 'a.nama');

        // Get paginated data
        $dataPinjaman = $query->orderBy('v.tempo', 'asc')->paginate($perPage);

        // Calculate totals and process data
        $totalTagihan = 0;
        $totalDibayar = 0;
        $totalSisa = 0;

        $processedData = [];
        foreach ($dataPinjaman as $pinjaman) {
            // Calculate total tagihan (tagihan + denda)
            $totalTagihanValue = $pinjaman->tagihan + $pinjaman->denda_rp;
            $totalBayar = $pinjaman->total_bayar;
            $sisaTagihan = $totalTagihanValue - $totalBayar;
            
            $totalTagihan += $totalTagihanValue;
            $totalDibayar += $totalBayar;
            $totalSisa += $sisaTagihan;

            // Format kode pinjam
            $kodePinjam = 'TPJ' . str_pad($pinjaman->id, 5, '0', STR_PAD_LEFT);

            // Calculate days overdue
            $daysOverdue = Carbon::now()->diffInDays(Carbon::parse($pinjaman->tempo), false);

            $processedData[] = (object) [
                'id' => $pinjaman->id,
                'no_ktp' => $pinjaman->no_ktp,
                'tempo' => $pinjaman->tempo,
                'tgl_pinjam' => $pinjaman->tgl_pinjam,
                'tagihan' => $pinjaman->tagihan,
                'denda_rp' => $pinjaman->denda_rp,
                'lama_angsuran' => $pinjaman->lama_angsuran,
                'lunas' => $pinjaman->lunas,
                'nama_anggota' => $pinjaman->nama_anggota,
                'total_bayar' => $totalBayar,
                'total_tagihan' => $totalTagihanValue,
                'sisa_tagihan' => $sisaTagihan,
                'kode_pinjam' => $kodePinjam,
                'days_overdue' => $daysOverdue
            ];
        }

        return view('laporan.kredit_macet', compact(
            'dataPinjaman',
            'processedData',
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

        // Build query using v_hitung_pinjaman_3 with joins and aggregation
        $query = DB::table('v_hitung_pinjaman_3 as v')
            ->join('tbl_anggota as a', 'v.no_ktp', '=', 'a.no_ktp')
            ->leftJoin('tbl_pinjaman_d as d', function($join) {
                $join->on('v.id', '=', 'd.pinjam_id');
            })
            ->select(
                'v.id',
                'v.no_ktp',
                'v.tempo',
                'v.tgl_pinjam',
                'v.tagihan',
                'v.denda_rp',
                'v.lama_angsuran',
                'v.lunas',
                'a.nama as nama_anggota',
                DB::raw('COALESCE(SUM(d.jumlah_bayar), 0) as total_bayar')
            )
            ->whereYear('v.tempo', $tahun)
            ->whereMonth('v.tempo', '<', $bulanSekarang) // Loans past due
            ->where('v.lunas', 'Belum')
            ->groupBy('v.id', 'v.no_ktp', 'v.tempo', 'v.tgl_pinjam', 'v.tagihan', 'v.denda_rp', 'v.lama_angsuran', 'v.lunas', 'a.nama');

        // Get all data for PDF
        $dataPinjaman = $query->orderBy('v.tempo', 'asc')->get();

        // Calculate totals and process data
        $totalTagihan = 0;
        $totalDibayar = 0;
        $totalSisa = 0;

        $processedData = [];
        foreach ($dataPinjaman as $pinjaman) {
            // Calculate total tagihan (tagihan + denda)
            $totalTagihanValue = $pinjaman->tagihan + $pinjaman->denda_rp;
            $totalBayar = $pinjaman->total_bayar;
            $sisaTagihan = $totalTagihanValue - $totalBayar;
            
            $totalTagihan += $totalTagihanValue;
            $totalDibayar += $totalBayar;
            $totalSisa += $sisaTagihan;

            // Format kode pinjam
            $kodePinjam = 'TPJ' . str_pad($pinjaman->id, 5, '0', STR_PAD_LEFT);

            // Calculate days overdue
            $daysOverdue = Carbon::now()->diffInDays(Carbon::parse($pinjaman->tempo), false);

            $processedData[] = (object) [
                'id' => $pinjaman->id,
                'no_ktp' => $pinjaman->no_ktp,
                'tempo' => $pinjaman->tempo,
                'tgl_pinjam' => $pinjaman->tgl_pinjam,
                'tagihan' => $pinjaman->tagihan,
                'denda_rp' => $pinjaman->denda_rp,
                'lama_angsuran' => $pinjaman->lama_angsuran,
                'lunas' => $pinjaman->lunas,
                'nama_anggota' => $pinjaman->nama_anggota,
                'total_bayar' => $totalBayar,
                'total_tagihan' => $totalTagihanValue,
                'sisa_tagihan' => $sisaTagihan,
                'kode_pinjam' => $kodePinjam,
                'days_overdue' => $daysOverdue
            ];
        }

        // Format periode text
        $periodeText = Carbon::parse($periode . '-01')->format('F Y');

        $pdf = Pdf::loadView('laporan.pdf.kredit_macet', compact(
            'dataPinjaman',
            'processedData',
            'periodeText',
            'totalTagihan',
            'totalDibayar',
            'totalSisa'
        ));

        return $pdf->download('laporan_kredit_macet_' . date('Ymd') . '.pdf');
    }
} 