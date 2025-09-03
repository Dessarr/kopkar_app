<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TempoPinjaman;
use App\Models\TblPinjamanH;
use App\Models\TblPinjamanD;
use App\Models\data_anggota;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanJatuhTempoController extends Controller
{
    public function index(Request $request)
    {
        // Get filter parameters
        $periode = $request->input('periode', date('Y-m'));
        $search = $request->input('search');
        $perPage = 10;

        // Parse periode to get year and month
        $tahun = date('Y', strtotime($periode . '-01'));
        $bulan = date('m', strtotime($periode . '-01'));

        // Build query using tempo_pinjaman table with joins
        $query = DB::table('tempo_pinjaman as t')
            ->join('tbl_pinjaman_h as h', 't.pinjam_id', '=', 'h.id')
            ->join('tbl_anggota as a', 't.no_ktp', '=', 'a.no_ktp')
            ->select(
                't.pinjam_id as id',
                't.no_ktp',
                't.tempo',
                't.tgl_pinjam',
                'h.jumlah',
                'h.lama_angsuran',
                'h.bunga_rp',
                'h.biaya_adm',
                'h.lunas',
                'a.nama as nama_anggota'
            )
            ->whereYear('t.tempo', $tahun)
            ->whereMonth('t.tempo', $bulan)
            ->where('h.lunas', 'Belum');

        // Search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('a.nama', 'like', '%' . $search . '%')
                  ->orWhere('t.no_ktp', 'like', '%' . $search . '%')
                  ->orWhere('t.pinjam_id', 'like', '%' . $search . '%');
            });
        }

        // Get paginated data
        $dataPinjaman = $query->orderBy('t.tempo', 'asc')->paginate($perPage);

        // Calculate totals
        $totalTagihan = 0;
        $totalDibayar = 0;
        $totalSisa = 0;

        foreach ($dataPinjaman as $pinjaman) {
            // Calculate tagihan (angsuran pokok + bunga + biaya admin)
            $angsuranPokok = $pinjaman->jumlah / $pinjaman->lama_angsuran;
            $angsuranBunga = $pinjaman->bunga_rp / $pinjaman->lama_angsuran;
            $tagihan = $angsuranPokok + $angsuranBunga + $pinjaman->biaya_adm;
            
            $totalTagihan += $tagihan;
            
            // Get total payment from tbl_pinjaman_d
            $totalBayar = TblPinjamanD::where('pinjam_id', $pinjaman->id)
                ->sum('jumlah_bayar');
            $totalDibayar += $totalBayar;
            $totalSisa += $tagihan - $totalBayar;
        }

        return view('laporan.jatuh_tempo', compact(
            'dataPinjaman',
            'periode',
            'search',
            'totalTagihan',
            'totalDibayar',
            'totalSisa'
        ));
    }

    public function exportPdf(Request $request)
    {
        // Get filter parameters
        $periode = $request->input('periode', date('Y-m'));
        $search = $request->input('search');

        // Parse periode to get year and month
        $tahun = date('Y', strtotime($periode . '-01'));
        $bulan = date('m', strtotime($periode . '-01'));

        // Build query using tempo_pinjaman table with joins
        $query = DB::table('tempo_pinjaman as t')
            ->join('tbl_pinjaman_h as h', 't.pinjam_id', '=', 'h.id')
            ->join('tbl_anggota as a', 't.no_ktp', '=', 'a.no_ktp')
            ->select(
                't.pinjam_id as id',
                't.no_ktp',
                't.tempo',
                't.tgl_pinjam',
                'h.jumlah',
                'h.lama_angsuran',
                'h.bunga_rp',
                'h.biaya_adm',
                'h.lunas',
                'a.nama as nama_anggota'
            )
            ->whereYear('t.tempo', $tahun)
            ->whereMonth('t.tempo', $bulan)
            ->where('h.lunas', 'Belum');

        // Search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('a.nama', 'like', '%' . $search . '%')
                  ->orWhere('t.no_ktp', 'like', '%' . $search . '%')
                  ->orWhere('t.pinjam_id', 'like', '%' . $search . '%');
            });
        }

        // Get all data for PDF
        $dataPinjaman = $query->orderBy('t.tempo', 'asc')->get();

        // Calculate totals
        $totalTagihan = 0;
        $totalDibayar = 0;
        $totalSisa = 0;

        foreach ($dataPinjaman as $pinjaman) {
            // Calculate tagihan (angsuran pokok + bunga + biaya admin)
            $angsuranPokok = $pinjaman->jumlah / $pinjaman->lama_angsuran;
            $angsuranBunga = $pinjaman->bunga_rp / $pinjaman->lama_angsuran;
            $tagihan = $angsuranPokok + $angsuranBunga + $pinjaman->biaya_adm;
            
            $totalTagihan += $tagihan;
            
            // Get total payment from tbl_pinjaman_d
            $totalBayar = TblPinjamanD::where('pinjam_id', $pinjaman->id)
                ->sum('jumlah_bayar');
            $totalDibayar += $totalBayar;
            $totalSisa += $tagihan - $totalBayar;
        }

        $periodeText = Carbon::parse($periode . '-01')->format('F Y');

        $pdf = PDF::loadView('laporan.pdf.jatuh_tempo', compact(
            'dataPinjaman',
            'periodeText',
            'totalTagihan',
            'totalDibayar',
            'totalSisa'
        ));

        return $pdf->download('laporan_jatuh_tempo_' . date('Ymd') . '.pdf');
    }


} 