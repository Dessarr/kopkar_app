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
     * Get data for Simpanan - Data Lengkap untuk Dashboard
     * Menggunakan logic akuntansi: Saldo = Saldo Awal + Penerimaan - Penarikan
     */
    public static function getSimpananData()
    {
        try {
            $currentMonth = Carbon::now()->month;
            $currentYear = Carbon::now()->year;
            $lastMonth = Carbon::now()->subMonth()->month;
            $lastYear = Carbon::now()->subMonth()->year;

            // 1. SALDO BULAN LALU (Saldo sampai akhir bulan lalu)
            // Menggunakan view v_rekap_simpanan sesuai project CI lama
            
            // Simpanan Pokok (ID: 40)
            $saldoPokokBulanLalu = DB::table('v_rekap_simpanan')
                ->where('jenis_id', 40)
                ->whereRaw("DATE(tgl_transaksi) < '{$currentYear}-{$currentMonth}-01'")
                ->selectRaw('SUM(Debet) - SUM(Kredit) as saldo')
                ->first()->saldo ?? 0;

            // Simpanan Wajib (ID: 41)
            $saldoWajibBulanLalu = DB::table('v_rekap_simpanan')
                ->where('jenis_id', 41)
                ->whereRaw("DATE(tgl_transaksi) < '{$currentYear}-{$currentMonth}-01'")
                ->selectRaw('SUM(Debet) - SUM(Kredit) as saldo')
                ->first()->saldo ?? 0;

            // Simpanan Sukarela (ID: 32)
            $saldoSukarelaBulanLalu = DB::table('v_rekap_simpanan')
                ->where('jenis_id', 32)
                ->whereRaw("DATE(tgl_transaksi) < '{$currentYear}-{$currentMonth}-01'")
                ->selectRaw('SUM(Debet) - SUM(Kredit) as saldo')
                ->first()->saldo ?? 0;

            // Simpanan Khusus I (ID: 51)
            $saldoKhusus1BulanLalu = DB::table('v_rekap_simpanan')
                ->where('jenis_id', 51)
                ->whereRaw("DATE(tgl_transaksi) < '{$currentYear}-{$currentMonth}-01'")
                ->selectRaw('SUM(Debet) - SUM(Kredit) as saldo')
                ->first()->saldo ?? 0;

            // Simpanan Khusus II (ID: 52)
            $saldoKhusus2BulanLalu = DB::table('v_rekap_simpanan')
                ->where('jenis_id', 52)
                ->whereRaw("DATE(tgl_transaksi) < '{$currentYear}-{$currentMonth}-01'")
                ->selectRaw('SUM(Debet) - SUM(Kredit) as saldo')
                ->first()->saldo ?? 0;

            // Tabungan Perumahan (ID: 31)
            $tabPerumahanBulanLalu = DB::table('v_rekap_simpanan')
                ->where('jenis_id', 31)
                ->whereRaw("DATE(tgl_transaksi) < '{$currentYear}-{$currentMonth}-01'")
                ->selectRaw('SUM(Debet) - SUM(Kredit) as saldo')
                ->first()->saldo ?? 0;

            // Tagihan Bulan Lalu (ID: 8)
            $tagihanBulanLalu = DB::table('v_tagihan')
                ->where('jenis_id', 8)
                ->whereRaw("DATE(tgl_transaksi) < '{$currentYear}-{$currentMonth}-01'")
                ->sum('jumlah') ?? 0;

            // 2. PENERIMAAN BULAN INI
            // Menggunakan view v_rekap_simpanan sesuai project CI lama
            
            $penerimaanPokokBulanIni = DB::table('v_rekap_simpanan')
                ->where('jenis_id', 40)
                ->whereRaw("YEAR(tgl_transaksi) = '{$currentYear}' AND MONTH(tgl_transaksi) = '{$currentMonth}'")
                ->sum('Debet') ?? 0;

            $penerimaanWajibBulanIni = DB::table('v_rekap_simpanan')
                ->where('jenis_id', 41)
                ->whereRaw("YEAR(tgl_transaksi) = '{$currentYear}' AND MONTH(tgl_transaksi) = '{$currentMonth}'")
                ->sum('Debet') ?? 0;

            $penerimaanSukarelaBulanIni = DB::table('v_rekap_simpanan')
                ->where('jenis_id', 32)
                ->whereRaw("YEAR(tgl_transaksi) = '{$currentYear}' AND MONTH(tgl_transaksi) = '{$currentMonth}'")
                ->sum('Debet') ?? 0;

            $penerimaanKhusus1BulanIni = DB::table('v_rekap_simpanan')
                ->where('jenis_id', 51)
                ->whereRaw("YEAR(tgl_transaksi) = '{$currentYear}' AND MONTH(tgl_transaksi) = '{$currentMonth}'")
                ->sum('Debet') ?? 0;

            $penerimaanKhusus2BulanIni = DB::table('v_rekap_simpanan')
                ->where('jenis_id', 52)
                ->whereRaw("YEAR(tgl_transaksi) = '{$currentYear}' AND MONTH(tgl_transaksi) = '{$currentMonth}'")
                ->sum('Debet') ?? 0;

            $penerimaanPerumahanBulanIni = DB::table('v_rekap_simpanan')
                ->where('jenis_id', 31)
                ->whereRaw("YEAR(tgl_transaksi) = '{$currentYear}' AND MONTH(tgl_transaksi) = '{$currentMonth}'")
                ->sum('Debet') ?? 0;

            $penerimaanTagihanBulanIni = DB::table('v_tagihan')
                ->where('jenis_id', 8)
                ->whereRaw("YEAR(tgl_transaksi) = '{$currentYear}' AND MONTH(tgl_transaksi) = '{$currentMonth}'")
                ->sum('jumlah') ?? 0;

            // 3. PENARIKAN BULAN INI
            // Menggunakan view v_rekap_simpanan sesuai project CI lama
            
            $penarikanPokokBulanIni = DB::table('v_rekap_simpanan')
                ->where('jenis_id', 40)
                ->whereRaw("YEAR(tgl_transaksi) = '{$currentYear}' AND MONTH(tgl_transaksi) = '{$currentMonth}'")
                ->sum('Kredit') ?? 0;

            $penarikanWajibBulanIni = DB::table('v_rekap_simpanan')
                ->where('jenis_id', 41)
                ->whereRaw("YEAR(tgl_transaksi) = '{$currentYear}' AND MONTH(tgl_transaksi) = '{$currentMonth}'")
                ->sum('Kredit') ?? 0;

            $penarikanSukarelaBulanIni = DB::table('v_rekap_simpanan')
                ->where('jenis_id', 32)
                ->whereRaw("YEAR(tgl_transaksi) = '{$currentYear}' AND MONTH(tgl_transaksi) = '{$currentMonth}'")
                ->sum('Kredit') ?? 0;

            $penarikanKhusus1BulanIni = DB::table('v_rekap_simpanan')
                ->where('jenis_id', 51)
                ->whereRaw("YEAR(tgl_transaksi) = '{$currentYear}' AND MONTH(tgl_transaksi) = '{$currentMonth}'")
                ->sum('Kredit') ?? 0;

            $penarikanKhusus2BulanIni = DB::table('v_rekap_simpanan')
                ->where('jenis_id', 52)
                ->whereRaw("YEAR(tgl_transaksi) = '{$currentYear}' AND MONTH(tgl_transaksi) = '{$currentMonth}'")
                ->sum('Kredit') ?? 0;

            $penarikanPerumahanBulanIni = DB::table('v_rekap_simpanan')
                ->where('jenis_id', 31)
                ->whereRaw("YEAR(tgl_transaksi) = '{$currentYear}' AND MONTH(tgl_transaksi) = '{$currentMonth}'")
                ->sum('Kredit') ?? 0;

            $penarikanTagihanBulanIni = DB::table('v_tagihan')
                ->where('jenis_id', 8)
                ->whereRaw("YEAR(tgl_transaksi) = '{$currentYear}' AND MONTH(tgl_transaksi) = '{$currentMonth}'")
                ->sum('jumlah') ?? 0;

            // 4. SALDO BULAN INI (Rumus Akuntansi: Saldo = Saldo Awal + Penerimaan - Penarikan)
            $saldoPokokBulanIni = $saldoPokokBulanLalu + $penerimaanPokokBulanIni - $penarikanPokokBulanIni;
            $saldoWajibBulanIni = $saldoWajibBulanLalu + $penerimaanWajibBulanIni - $penarikanWajibBulanIni;
            $saldoSukarelaBulanIni = $saldoSukarelaBulanLalu + $penerimaanSukarelaBulanIni - $penarikanSukarelaBulanIni;
            $saldoKhusus1BulanIni = $saldoKhusus1BulanLalu + $penerimaanKhusus1BulanIni - $penarikanKhusus1BulanIni;
            $saldoKhusus2BulanIni = $saldoKhusus2BulanLalu + $penerimaanKhusus2BulanIni - $penarikanKhusus2BulanIni;
            $tabPerumahanBulanIni = $tabPerumahanBulanLalu + $penerimaanPerumahanBulanIni - $penarikanPerumahanBulanIni;
            $tagihanBulanIni = $tagihanBulanLalu + $penerimaanTagihanBulanIni - $penarikanTagihanBulanIni;

            Log::info('Simpanan Data calculated successfully');

            return [
                // Saldo Bulan Lalu
                'saldo_pokok' => $saldoPokokBulanLalu,
                'saldo_wajib' => $saldoWajibBulanLalu,
                'simpanan_sukarela' => $saldoSukarelaBulanLalu,
                'simpanan_khusus_1' => $saldoKhusus1BulanLalu,
                'simpanan_khusus_2' => $saldoKhusus2BulanLalu,
                'tab_perumahan' => $tabPerumahanBulanLalu,
                'tagihan_bulan_lalu' => $tagihanBulanLalu,

                // Penerimaan Bulan Ini
                'penerimaan_pokok' => $penerimaanPokokBulanIni,
                'penerimaan_wajib' => $penerimaanWajibBulanIni,
                'penerimaan_sukarela' => $penerimaanSukarelaBulanIni,
                'penerimaan_khusus_1' => $penerimaanKhusus1BulanIni,
                'penerimaan_khusus_2' => $penerimaanKhusus2BulanIni,
                'penerimaan_perumahan' => $penerimaanPerumahanBulanIni,
                'penerimaan_tagihan' => $penerimaanTagihanBulanIni,

                // Penarikan Bulan Ini
                'penarikan_pokok' => $penarikanPokokBulanIni,
                'penarikan_wajib' => $penarikanWajibBulanIni,
                'penarikan_sukarela' => $penarikanSukarelaBulanIni,
                'penarikan_khusus_1' => $penarikanKhusus1BulanIni,
                'penarikan_khusus_2' => $penarikanKhusus2BulanIni,
                'penarikan_perumahan' => $penarikanPerumahanBulanIni,
                'penarikan_tagihan' => $penarikanTagihanBulanIni,

                // Saldo Bulan Ini (Final)
                'saldo_pokok_final' => $saldoPokokBulanIni,
                'saldo_wajib_final' => $saldoWajibBulanIni,
                'saldo_sukarela' => $saldoSukarelaBulanIni,
                'saldo_khusus_1' => $saldoKhusus1BulanIni,
                'saldo_khusus_2' => $saldoKhusus2BulanIni,
                'saldo_perumahan' => $tabPerumahanBulanIni,
                'saldo_tagihan' => $tagihanBulanIni,
            ];

        } catch (\Exception $e) {
            Log::error('Error in getSimpananData: ' . $e->getMessage());
            
            // Return default values jika ada error
            return [
                'saldo_pokok' => 0, 'saldo_wajib' => 0, 'simpanan_sukarela' => 0,
                'simpanan_khusus_1' => 0, 'simpanan_khusus_2' => 0, 'tab_perumahan' => 0,
                'tagihan_bulan_lalu' => 0, 'penerimaan_pokok' => 0, 'penerimaan_wajib' => 0,
                'penerimaan_sukarela' => 0, 'penerimaan_khusus_1' => 0, 'penerimaan_khusus_2' => 0,
                'penerimaan_perumahan' => 0, 'penerimaan_tagihan' => 0, 'penarikan_pokok' => 0,
                'penarikan_wajib' => 0, 'penarikan_sukarela' => 0, 'penarikan_khusus_1' => 0,
                'penarikan_khusus_2' => 0, 'penarikan_perumahan' => 0, 'penarikan_tagihan' => 0,
                'saldo_pokok_final' => 0, 'saldo_wajib_final' => 0, 'saldo_sukarela' => 0,
                'saldo_khusus_1' => 0, 'saldo_khusus_2' => 0, 'saldo_perumahan' => 0, 'saldo_tagihan' => 0
            ];
        }
    }

    /**
     * Get data for Jatuh Tempo
     * Menggunakan data dari tbl_pinjaman_h yang aktif (Belum lunas)
     * LEFT JOIN untuk include semua record, termasuk yang namanya '-'
     */
    public static function getJatuhTempoData()
    {
        try {
            // Gunakan LEFT JOIN untuk include SEMUA record, termasuk yang namanya '-'
            $jatuhTempo = TblPinjamanH::select([
                'tbl_pinjaman_h.id',
                'tbl_pinjaman_h.anggota_id',
                'tbl_pinjaman_h.tgl_pinjam', 
                'tbl_pinjaman_h.jumlah',
                'tbl_anggota.nama',
                'tbl_anggota.file_pic'
            ])
                ->leftJoin('tbl_anggota', 'tbl_pinjaman_h.anggota_id', '=', 'tbl_anggota.id')
                ->where('tbl_pinjaman_h.lunas', 'Belum') // Hanya yang belum lunas
                ->orderBy('tbl_pinjaman_h.tgl_pinjam', 'asc')
                ->get()
                ->map(function ($item) {
                    // Handle missing names (seperti '-')
                    if (empty($item->nama) || $item->nama === '-') {
                        $item->nama = 'Anggota ID: ' . $item->anggota_id;
                    }
                    return $item;
                });
            
            Log::info('Jatuh Tempo from tbl_pinjaman_h (LEFT JOIN): ' . $jatuhTempo->count() . ' items');
            return $jatuhTempo;
            
        } catch (\Exception $e) {
            Log::error('Error in getJatuhTempoData: ' . $e->getMessage());
            return collect([]);
        }
    }
}