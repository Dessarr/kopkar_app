<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Perbaiki view v_hitung_pinjaman dengan menambahkan kolom tagihan
        DB::statement("
            CREATE OR REPLACE VIEW v_hitung_pinjaman AS 
            SELECT 
                p.id,
                p.no_ktp,
                p.jenis_pinjaman,
                p.jumlah,
                p.lunas,
                p.status,
                p.tgl_pinjam,
                COALESCE(SUM(d.jumlah_bayar), 0) AS total_bayar,
                (p.jumlah - COALESCE(SUM(d.jumlah_bayar), 0)) AS sisa_pokok,
                p.jumlah AS tagihan
            FROM tbl_pinjaman_h p
            LEFT JOIN tbl_pinjaman_d d ON p.id = d.pinjam_id
            GROUP BY p.id, p.no_ktp, p.jenis_pinjaman, p.jumlah, p.lunas, p.status, p.tgl_pinjam
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan ke view sebelumnya tanpa kolom tagihan
        DB::statement("
            CREATE OR REPLACE VIEW v_hitung_pinjaman AS 
            SELECT 
                p.id,
                p.no_ktp,
                p.jenis_pinjaman,
                p.jumlah,
                p.lunas,
                p.status,
                p.tgl_pinjam,
                COALESCE(SUM(d.jumlah_bayar), 0) AS total_bayar,
                (p.jumlah - COALESCE(SUM(d.jumlah_bayar), 0)) AS sisa_pokok
            FROM tbl_pinjaman_h p
            LEFT JOIN tbl_pinjaman_d d ON p.id = d.pinjam_id
            GROUP BY p.id, p.no_ktp, p.jenis_pinjaman, p.jumlah, p.lunas, p.status, p.tgl_pinjam
        ");
    }
};