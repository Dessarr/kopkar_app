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

        // Build query using tempo_pinjaman table with joins and aggregation
        $query = DB::table('tempo_pinjaman as t')
            ->join('tbl_pinjaman_h as h', 't.pinjam_id', '=', 'h.id')
            ->join('tbl_anggota as a', 't.no_ktp', '=', 'a.no_ktp')
            ->leftJoin('tbl_pinjaman_d as d', function($join) {
                $join->on('t.pinjam_id', '=', 'd.pinjam_id');
            })
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
                'a.nama as nama_anggota',
                DB::raw('COALESCE(SUM(d.jumlah_bayar), 0) as total_bayar')
            )
            ->whereYear('t.tempo', $tahun)
            ->whereMonth('t.tempo', $bulan)
            ->where('h.lunas', 'Belum')
            ->groupBy('t.pinjam_id', 't.no_ktp', 't.tempo', 't.tgl_pinjam', 'h.jumlah', 'h.lama_angsuran', 'h.bunga_rp', 'h.biaya_adm', 'h.lunas', 'a.nama');

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

        // Calculate totals and process data
        $totalTagihan = 0;
        $totalDibayar = 0;
        $totalSisa = 0;

        $processedData = [];
        foreach ($dataPinjaman as $pinjaman) {
            // Calculate tagihan (angsuran pokok + bunga + biaya admin)
            $angsuranPokok = $pinjaman->jumlah / $pinjaman->lama_angsuran;
            $angsuranBunga = $pinjaman->bunga_rp / $pinjaman->lama_angsuran;
            $tagihan = $angsuranPokok + $angsuranBunga + $pinjaman->biaya_adm;
            
            $totalBayar = $pinjaman->total_bayar;
            $sisaTagihan = $tagihan - $totalBayar;
            
            $totalTagihan += $tagihan;
            $totalDibayar += $totalBayar;
            $totalSisa += $sisaTagihan;

            // Format kode pinjam
            $kodePinjam = 'TPJ' . str_pad($pinjaman->id, 5, '0', STR_PAD_LEFT);

            $processedData[] = (object) [
                'id' => $pinjaman->id,
                'no_ktp' => $pinjaman->no_ktp,
                'tempo' => $pinjaman->tempo,
                'tgl_pinjam' => $pinjaman->tgl_pinjam,
                'jumlah' => $pinjaman->jumlah,
                'lama_angsuran' => $pinjaman->lama_angsuran,
                'bunga_rp' => $pinjaman->bunga_rp,
                'biaya_adm' => $pinjaman->biaya_adm,
                'lunas' => $pinjaman->lunas,
                'nama_anggota' => $pinjaman->nama_anggota,
                'total_bayar' => $totalBayar,
                'tagihan' => $tagihan,
                'sisa_tagihan' => $sisaTagihan,
                'kode_pinjam' => $kodePinjam
            ];
        }

        return view('laporan.jatuh_tempo', compact(
            'dataPinjaman',
            'processedData',
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

        // Build query using tempo_pinjaman table with joins and aggregation
        $query = DB::table('tempo_pinjaman as t')
            ->join('tbl_pinjaman_h as h', 't.pinjam_id', '=', 'h.id')
            ->join('tbl_anggota as a', 't.no_ktp', '=', 'a.no_ktp')
            ->leftJoin('tbl_pinjaman_d as d', function($join) {
                $join->on('t.pinjam_id', '=', 'd.pinjam_id');
            })
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
                'a.nama as nama_anggota',
                DB::raw('COALESCE(SUM(d.jumlah_bayar), 0) as total_bayar')
            )
            ->whereYear('t.tempo', $tahun)
            ->whereMonth('t.tempo', $bulan)
            ->where('h.lunas', 'Belum')
            ->groupBy('t.pinjam_id', 't.no_ktp', 't.tempo', 't.tgl_pinjam', 'h.jumlah', 'h.lama_angsuran', 'h.bunga_rp', 'h.biaya_adm', 'h.lunas', 'a.nama');

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

        // Calculate totals and process data
        $totalTagihan = 0;
        $totalDibayar = 0;
        $totalSisa = 0;

        $processedData = [];
        foreach ($dataPinjaman as $pinjaman) {
            // Calculate tagihan (angsuran pokok + bunga + biaya admin)
            $angsuranPokok = $pinjaman->jumlah / $pinjaman->lama_angsuran;
            $angsuranBunga = $pinjaman->bunga_rp / $pinjaman->lama_angsuran;
            $tagihan = $angsuranPokok + $angsuranBunga + $pinjaman->biaya_adm;
            
            $totalBayar = $pinjaman->total_bayar;
            $sisaTagihan = $tagihan - $totalBayar;
            
            $totalTagihan += $tagihan;
            $totalDibayar += $totalBayar;
            $totalSisa += $sisaTagihan;

            // Format kode pinjam
            $kodePinjam = 'TPJ' . str_pad($pinjaman->id, 5, '0', STR_PAD_LEFT);

            $processedData[] = (object) [
                'id' => $pinjaman->id,
                'no_ktp' => $pinjaman->no_ktp,
                'tempo' => $pinjaman->tempo,
                'tgl_pinjam' => $pinjaman->tgl_pinjam,
                'jumlah' => $pinjaman->jumlah,
                'lama_angsuran' => $pinjaman->lama_angsuran,
                'bunga_rp' => $pinjaman->bunga_rp,
                'biaya_adm' => $pinjaman->biaya_adm,
                'lunas' => $pinjaman->lunas,
                'nama_anggota' => $pinjaman->nama_anggota,
                'total_bayar' => $totalBayar,
                'tagihan' => $tagihan,
                'sisa_tagihan' => $sisaTagihan,
                'kode_pinjam' => $kodePinjam
            ];
        }

        $periodeText = Carbon::parse($periode . '-01')->format('F Y');

        $pdf = PDF::loadView('laporan.pdf.jatuh_tempo', compact(
            'dataPinjaman',
            'processedData',
            'periodeText',
            'totalTagihan',
            'totalDibayar',
            'totalSisa'
        ));

        return $pdf->download('laporan_jatuh_tempo_' . date('Ymd') . '.pdf');
    }


} 