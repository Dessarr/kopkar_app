<?php

/**
 * Script untuk memperbaiki data billing yang sudah ada
 * Menjalankan script ini akan meng-generate ulang billing untuk semua jadwal angsuran
 * terlepas dari status lunas, sehingga data konsisten
 */

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== SCRIPT PERBAIKAN DATA BILLING ===\n";
echo "Memperbaiki data billing agar konsisten...\n\n";

try {
    // 1. Hapus semua data billing pinjaman yang ada
    echo "1. Menghapus data billing pinjaman yang ada...\n";
    $deletedCount = DB::table('tbl_trans_tagihan')
        ->where('jenis_id', 999)
        ->delete();
    echo "   - Berhasil menghapus {$deletedCount} data billing pinjaman\n\n";

    // 2. Hapus data di tbl_trans_sp_bayar_temp yang terkait pinjaman
    echo "2. Menghapus data billing utama yang terkait pinjaman...\n";
    $deletedMainCount = DB::table('tbl_trans_sp_bayar_temp')
        ->where('tagihan_pinjaman', '>', 0)
        ->delete();
    echo "   - Berhasil menghapus {$deletedMainCount} data billing utama\n\n";

    // 3. Generate ulang billing untuk semua jadwal angsuran
    echo "3. Meng-generate ulang billing untuk semua jadwal angsuran...\n";
    
    // Ambil semua jadwal angsuran yang ada
    $jadwalAngsuran = DB::table('tempo_pinjaman as t')
        ->join('tbl_pinjaman_h as h', 't.pinjam_id', '=', 'h.id')
        ->select(
            't.pinjam_id',
            't.no_ktp',
            't.tempo',
            'h.jumlah',
            'h.lama_angsuran',
            'h.bunga_rp',
            'h.biaya_adm',
            'h.lunas'
        )
        ->get();

    echo "   - Ditemukan " . count($jadwalAngsuran) . " jadwal angsuran\n";

    $billingData = [];
    $processedCount = 0;

    foreach ($jadwalAngsuran as $jadwal) {
        // Hitung angsuran per bulan
        $angsuranPokok = $jadwal->jumlah / $jadwal->lama_angsuran;
        $angsuranBunga = $jadwal->bunga_rp / $jadwal->lama_angsuran;
        $totalAngsuran = $angsuranPokok + $angsuranBunga;
        
        // Cek apakah angsuran ini sudah dibayar
        $sudahDibayar = DB::table('tbl_pinjaman_d')
            ->where('pinjam_id', $jadwal->pinjam_id)
            ->where('angsuran_ke', DB::raw('(SELECT no_urut FROM tempo_pinjaman WHERE pinjam_id = \'' . $jadwal->pinjam_id . '\' AND tempo = \'' . $jadwal->tempo . '\')'))
            ->whereNotNull('tgl_bayar')
            ->exists();
        
        // Generate tagihan untuk semua jadwal
        $billingData[] = [
            'tgl_transaksi' => $jadwal->tempo,
            'no_ktp' => $jadwal->no_ktp,
            'anggota_id' => null,
            'jenis_id' => 999, // ID untuk jenis Pinjaman
            'jumlah' => $totalAngsuran,
            'keterangan' => 'Tagihan Angsuran Pinjaman - Jatuh Tempo: ' . $jadwal->tempo,
            'akun' => 'Tagihan',
            'dk' => 'K',
            'kas_id' => 1,
            'user_name' => 'admin',
            'status_lunas' => $sudahDibayar ? 'Y' : 'N'
        ];
        
        $processedCount++;
        
        if ($processedCount % 100 == 0) {
            echo "   - Diproses {$processedCount} jadwal...\n";
        }
    }

    // 4. Insert billing data
    echo "4. Menyimpan data billing...\n";
    if (!empty($billingData)) {
        foreach (array_chunk($billingData, 100) as $chunk) {
            DB::table('tbl_trans_tagihan')->insert($chunk);
        }
    }
    echo "   - Berhasil menyimpan " . count($billingData) . " data billing\n\n";

    // 5. Proses ke billing utama
    echo "5. Memproses ke billing utama...\n";
    
    // Ambil semua tagihan pinjaman yang sudah di-generate
    $tagihanPinjaman = DB::table('tbl_trans_tagihan')
        ->select('no_ktp', DB::raw('SUM(jumlah) as total'))
        ->where('jenis_id', 999)
        ->groupBy('no_ktp')
        ->get();

    $mainBillingData = [];
    foreach ($tagihanPinjaman as $tagihan) {
        // Ambil data anggota
        $anggota = DB::table('tbl_anggota')
            ->where('no_ktp', $tagihan->no_ktp)
            ->first();
        
        if ($anggota) {
            // Ambil tanggal transaksi terbaru untuk anggota ini
            $tglTransaksi = DB::table('tbl_trans_tagihan')
                ->where('no_ktp', $tagihan->no_ktp)
                ->where('jenis_id', 999)
                ->max('tgl_transaksi');
            
            $mainBillingData[] = [
                'tgl_transaksi' => $tglTransaksi,
                'no_ktp' => $tagihan->no_ktp,
                'tagihan_simpanan_wajib' => 0,
                'tagihan_simpanan_sukarela' => 0,
                'tagihan_simpanan_khusus_2' => 0,
                'tagihan_pinjaman' => $tagihan->total,
                'tagihan_toserda' => 0,
                'jumlah' => $tagihan->total,
                'keterangan' => 'Billing Pinjaman - ' . $anggota->nama,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
    }

    if (!empty($mainBillingData)) {
        foreach (array_chunk($mainBillingData, 100) as $chunk) {
            DB::table('tbl_trans_sp_bayar_temp')->insert($chunk);
        }
    }
    echo "   - Berhasil memproses " . count($mainBillingData) . " data ke billing utama\n\n";

    echo "=== PERBAIKAN SELESAI ===\n";
    echo "Data billing telah diperbaiki dan konsisten.\n";
    echo "Sekarang semua jadwal angsuran akan muncul di billing sesuai jadwalnya.\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    Log::error('Error in fix billing data script: ' . $e->getMessage());
}
