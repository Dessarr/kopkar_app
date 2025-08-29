<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DashboardData extends Model
{
    /**
     * Get data for Pinjaman Kredit Chart
     */
    public static function getPinjamanKreditData()
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        $lastMonth = Carbon::now()->subMonth()->month;
        $lastYear = Carbon::now()->subMonth()->year;

        Log::info('Current Month: ' . $currentMonth . ', Current Year: ' . $currentYear);
        Log::info('Last Month: ' . $lastMonth . ', Last Year: ' . $lastYear);

        // Tagihan belum lunas (total) - dari view v_hitung_pinjaman
        $tagihanBelumLunas = DB::table('v_hitung_pinjaman')
            ->where('lunas', 'Belum')
            ->sum('tagihan');
        Log::info('Tagihan Belum Lunas: ' . $tagihanBelumLunas);

        // Tagihan bulan lalu - dari view v_hitung_pinjaman
        $tagihanBulanLalu = DB::table('v_hitung_pinjaman')
            ->where('lunas', 'Belum')
            ->whereMonth('tgl_pinjam', $lastMonth)
            ->whereYear('tgl_pinjam', $lastYear)
            ->sum('tagihan');
        Log::info('Tagihan Bulan Lalu: ' . $tagihanBulanLalu);

        // Pinjaman bulan ini - dari tbl_pinjaman_h
        $pinjamanBulanIni = TblPinjamanH::whereMonth('tgl_pinjam', $currentMonth)
            ->whereYear('tgl_pinjam', $currentYear)
            ->sum('jumlah');
        Log::info('Pinjaman Bulan Ini: ' . $pinjamanBulanIni);

        // Pembayaran bulan ini - dari tbl_pinjaman_d
        $pembayaranBulanIni = TblPinjamanD::whereMonth('tgl_bayar', $currentMonth)
            ->whereYear('tgl_bayar', $currentYear)
            ->sum('jumlah_bayar');
        Log::info('Pembayaran Bulan Ini: ' . $pembayaranBulanIni);

        return [
            'tagihan_belum_lunas' => $tagihanBelumLunas ?? 0,
            'tagihan_bulan_lalu' => $tagihanBulanLalu ?? 0,
            'pinjaman_bulan_ini' => $pinjamanBulanIni ?? 0,
            'pembayaran_bulan_ini' => $pembayaranBulanIni ?? 0
        ];
    }

    /**
     * Get data for Kas Chart
     */
    public static function getKasData()
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        Log::info('Kas - Current Month: ' . $currentMonth . ', Current Year: ' . $currentYear);

        // Total saldo (selisih total pemasukan - pengeluaran)
        $totalPemasukan = DB::table('tbl_trans_kas')
            ->where('dk', 'D')  // D = Debit (Pemasukan)
            ->sum('jumlah');
        Log::info('Total Pemasukan (D): ' . $totalPemasukan);

        $totalPengeluaran = DB::table('tbl_trans_kas')
            ->where('dk', 'K')  // K = Kredit (Pengeluaran)
            ->sum('jumlah');
        Log::info('Total Pengeluaran (K): ' . $totalPengeluaran);

        $totalSaldo = $totalPemasukan - $totalPengeluaran;
        Log::info('Total Saldo: ' . $totalSaldo);

        // Penerimaan bulan ini
        $penerimaanBulanIni = DB::table('tbl_trans_kas')
            ->where('dk', 'D')  // D = Debit (Pemasukan)
            ->whereMonth('tgl_catat', $currentMonth)
            ->whereYear('tgl_catat', $currentYear)
            ->sum('jumlah');
        Log::info('Penerimaan Bulan Ini: ' . $penerimaanBulanIni);

        // Pengeluaran bulan ini
        $pengeluaranBulanIni = DB::table('tbl_trans_kas')
            ->where('dk', 'K')  // K = Kredit (Pengeluaran)
            ->whereMonth('tgl_catat', $currentMonth)
            ->whereYear('tgl_catat', $currentYear)
            ->sum('jumlah');
        Log::info('Pengeluaran Bulan Ini: ' . $pengeluaranBulanIni);

        return [
            'total_saldo' => $totalSaldo ?? 0,
            'penerimaan_bulan_ini' => $penerimaanBulanIni ?? 0,
            'pengeluaran_bulan_ini' => $pengeluaranBulanIni ?? 0
        ];
    }

    /**
     * Get data for Data Pinjaman Chart
     */
    public static function getDataPinjaman()
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        $lastMonth = Carbon::now()->subMonth()->month;
        $lastYear = Carbon::now()->subMonth()->year;

        // Peminjam bulan lalu - dari tbl_pinjaman_h
        $peminjamBulanLalu = TblPinjamanH::whereMonth('tgl_pinjam', $lastMonth)
            ->whereYear('tgl_pinjam', $lastYear)
            ->count();

        // Peminjam bulan ini - dari tbl_pinjaman_h
        $peminjamBulanIni = TblPinjamanH::whereMonth('tgl_pinjam', $currentMonth)
            ->whereYear('tgl_pinjam', $currentYear)
            ->count();

        // Sudah lunas - dari view v_hitung_pinjaman
        $sudahLunas = DB::table('v_hitung_pinjaman')
            ->where('lunas', 'Lunas')
            ->count();

        // Belum lunas - dari view v_hitung_pinjaman
        $belumLunas = DB::table('v_hitung_pinjaman')
            ->where('lunas', 'Belum')
            ->count();

        return [
            'peminjam_bulan_lalu' => $peminjamBulanLalu ?? 0,
            'peminjam_bulan_ini' => $peminjamBulanIni ?? 0,
            'sudah_lunas' => $sudahLunas ?? 0,
            'belum_lunas' => $belumLunas ?? 0
        ];
    }

    /**
     * Get data for Data Anggota Chart
     */
    public static function getDataAnggota()
    {
        // Debug: Cek total data anggota
        $totalAnggota = data_anggota::count();
        Log::info('Total Anggota: ' . $totalAnggota);
        
        // Debug: Cek data anggota aktif
        $anggotaAktif = data_anggota::where('aktif', 'Y')->count();
        Log::info('Anggota Aktif (Y): ' . $anggotaAktif);
        
        // Debug: Cek data anggota tidak aktif
        $anggotaTidakAktif = data_anggota::where('aktif', 'N')->count();
        Log::info('Anggota Tidak Aktif (T): ' . $anggotaTidakAktif);
        
        // Debug: Cek semua nilai field aktif yang ada
        $allAktifValues = data_anggota::select('aktif')->distinct()->get();
        Log::info('All aktif values: ' . $allAktifValues->pluck('aktif')->toJson());
        
        // Debug: Cek beberapa sample data
        $sampleData = data_anggota::select('id', 'nama', 'aktif')->limit(5)->get();
        Log::info('Sample data: ' . $sampleData->toJson());

        return [
            'anggota_aktif' => $anggotaAktif ?? 0,
            'anggota_tidak_aktif' => $anggotaTidakAktif ?? 0,
            'total_anggota' => $totalAnggota ?? 0
        ];
    }

    /**
     * Get data for Simpanan
     */
    public static function getSimpananData()
    {
        // Saldo simpanan pokok
        $saldoPokok = TransaksiSimpanan::where('jenis_id', 40) // ID 40 = simpanan pokok
            ->where('akun', 'Setoran')
            ->sum('jumlah');

        // Saldo simpanan wajib
        $saldoWajib = TransaksiSimpanan::where('jenis_id', 41) // ID 41 = simpanan wajib
            ->where('akun', 'Setoran')
            ->sum('jumlah');

        return [
            'saldo_pokok' => $saldoPokok ?? 0,
            'saldo_wajib' => $saldoWajib ?? 0
        ];
    }

    /**
     * Get data for Jatuh Tempo
     */
    public static function getJatuhTempoData()
    {
        // Ambil data pinjaman yang jatuh tempo
        $jatuhTempo = TblPinjamanH::select('tbl_anggota.nama', 'tbl_pinjaman_h.tgl_pinjam', 'tbl_pinjaman_h.jumlah')
            ->join('tbl_anggota', 'tbl_pinjaman_h.anggota_id', '=', 'tbl_anggota.id')
            ->where('tbl_pinjaman_h.lunas', 'Belum')
            ->where('tbl_pinjaman_h.tgl_pinjam', '<', Carbon::now()->subDays(30))
            ->limit(5)
            ->get();

        return $jatuhTempo;
    }
}