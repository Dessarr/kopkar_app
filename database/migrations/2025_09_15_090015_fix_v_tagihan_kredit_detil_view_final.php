<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop view yang lama
        DB::statement("DROP VIEW IF EXISTS v_tagihan_kredit_detil");
        
        // Buat view yang baru dengan logika tagihan tak terbayar yang benar
        DB::statement("
            CREATE VIEW v_tagihan_kredit_detil AS
            SELECT 
                a.id,
                a.no_ktp,
                a.tgl_pinjam,
                a.lama_angsuran,
                a.jumlah_angsuran,
                a.bunga_rp,
                a.lunas,
                a.jenis_pinjaman,
                a.status,
                a.jumlah as total_pinjaman,
                
                -- Sisa pinjaman = total pinjaman - yang sudah dibayar
                (a.jumlah - COALESCE((
                    SELECT SUM(jumlah_bayar) 
                    FROM tbl_pinjaman_d 
                    WHERE pinjam_id = a.id
                ), 0)) as sisa_pinjaman,
                
                -- Angsuran per bulan (dibulatkan)
                ROUND(a.jumlah / a.lama_angsuran) as angsuran_per_bulan,
                
                -- Sudah bayar berapa bulan (tracker)
                COALESCE((
                    SELECT COUNT(DISTINCT angsuran_ke) 
                    FROM tbl_pinjaman_d 
                    WHERE pinjam_id = a.id 
                    AND angsuran_ke IS NOT NULL
                ), 0) as sudah_bayar,
                
                -- Total yang sudah dibayar
                COALESCE((
                    SELECT SUM(jumlah_bayar) 
                    FROM tbl_pinjaman_d 
                    WHERE pinjam_id = a.id
                ), 0) as total_bayar,
                
                -- Tagihan tak terbayar (sederhana: sisa pinjaman jika ada tempo yang lewat)
                CASE 
                    WHEN a.lunas = 'Belum' AND EXISTS (
                        SELECT 1 FROM tempo_pinjaman tp 
                        WHERE tp.pinjam_id = a.id 
                        AND tp.tempo < CURDATE()
                    ) THEN (a.jumlah - COALESCE((
                        SELECT SUM(jumlah_bayar) 
                        FROM tbl_pinjaman_d 
                        WHERE pinjam_id = a.id
                    ), 0))
                    ELSE 0
                END as tagihan_tak_terbayar,
                
                -- Tempo bulan depan (bulan depan dari tanggal ajuan)
                DATE_ADD(a.tgl_pinjam, INTERVAL 1 MONTH) as tempo_bulan_depan,
                
                -- Status pembayaran
                CASE 
                    WHEN a.lunas = 'Lunas' THEN 'Lunas'
                    WHEN (a.jumlah - COALESCE((
                        SELECT SUM(jumlah_bayar) 
                        FROM tbl_pinjaman_d 
                        WHERE pinjam_id = a.id
                    ), 0)) <= 0 THEN 'Lunas'
                    ELSE 'Belum Lunas'
                END as status_pembayaran

            FROM tbl_pinjaman_h a
            WHERE a.lunas = 'Belum' 
              AND a.jenis_pinjaman IN (1,2,3)  -- 1=Biasa, 2=Bank, 3=Barang
              AND a.status = '1'  -- Status terlaksana/disetujui
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS v_tagihan_kredit_detil");
    }
};
