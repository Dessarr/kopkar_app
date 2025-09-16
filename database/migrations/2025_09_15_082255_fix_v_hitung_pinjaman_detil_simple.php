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
        DB::statement("DROP VIEW IF EXISTS v_hitung_pinjaman_detil");
        
        // Buat view yang sederhana dan berfungsi
        DB::statement("
            CREATE VIEW v_hitung_pinjaman_detil AS
            SELECT 
                a.id,
                a.no_ktp,
                a.tgl_pinjam,
                a.lama_angsuran,
                a.jumlah_angsuran,
                a.bunga_rp,
                a.lunas,
                a.jenis_pinjaman,
                a.jumlah as total_pinjaman,
                a.status,
                
                -- Angsuran per bulan (dibulatkan ke bawah)
                FLOOR(a.jumlah / a.lama_angsuran) as angsuran_per_bulan,
                
                -- Sisa pembulatan (masuk ke angsuran terakhir)
                a.jumlah - (FLOOR(a.jumlah / a.lama_angsuran) * a.lama_angsuran) as sisa_pembulatan,
                
                -- Hitung bulan ke berapa dari ajuan
                CASE 
                    WHEN a.lunas = 'Belum' THEN
                        GREATEST(1, 
                            (YEAR(CURDATE()) - YEAR(a.tgl_pinjam)) * 12 + 
                            (MONTH(CURDATE()) - MONTH(a.tgl_pinjam)) + 1
                        )
                    ELSE a.lama_angsuran
                END as bulan_ke,
                
                -- Sudah bayar berapa bulan (tracker)
                CASE 
                    WHEN a.lunas = 'Lunas' THEN a.lama_angsuran
                    ELSE COALESCE((
                        SELECT COUNT(DISTINCT angsuran_ke) 
                        FROM tbl_pinjaman_d 
                        WHERE pinjam_id = a.id 
                        AND angsuran_ke IS NOT NULL
                    ), 0)
                END as sudah_bayar,
                
                -- Total yang sudah dibayar
                COALESCE((
                    SELECT SUM(jumlah_bayar) 
                    FROM tbl_pinjaman_d 
                    WHERE pinjam_id = a.id
                ), 0) as total_bayar,
                
                -- Sisa pinjaman
                a.jumlah - COALESCE((
                    SELECT SUM(jumlah_bayar) 
                    FROM tbl_pinjaman_d 
                    WHERE pinjam_id = a.id
                ), 0) as sisa_pinjaman,
                
                -- Angsuran bulan ini (sederhana)
                CASE 
                    WHEN a.lunas = 'Belum' AND 
                         (YEAR(CURDATE()) - YEAR(a.tgl_pinjam)) * 12 + 
                         (MONTH(CURDATE()) - MONTH(a.tgl_pinjam)) + 1 <= a.lama_angsuran
                    THEN FLOOR(a.jumlah / a.lama_angsuran)
                    ELSE 0
                END as angsuran_bulan_ini,
                
                -- Tag bulan lalu (sederhana)
                CASE 
                    WHEN a.lunas = 'Belum' AND 
                         (YEAR(CURDATE()) - YEAR(a.tgl_pinjam)) * 12 + 
                         (MONTH(CURDATE()) - MONTH(a.tgl_pinjam)) + 1 > a.lama_angsuran
                    THEN a.jumlah - COALESCE((
                        SELECT SUM(jumlah_bayar) 
                        FROM tbl_pinjaman_d 
                        WHERE pinjam_id = a.id
                    ), 0)
                    ELSE 0
                END as tag_bulan_lalu,
                
                -- Tag harus dibayar (sederhana)
                CASE 
                    WHEN a.lunas = 'Belum' THEN
                        CASE 
                            WHEN (YEAR(CURDATE()) - YEAR(a.tgl_pinjam)) * 12 + 
                                 (MONTH(CURDATE()) - MONTH(a.tgl_pinjam)) + 1 <= a.lama_angsuran
                            THEN FLOOR(a.jumlah / a.lama_angsuran)
                            ELSE 0
                        END + 
                        CASE 
                            WHEN (YEAR(CURDATE()) - YEAR(a.tgl_pinjam)) * 12 + 
                                 (MONTH(CURDATE()) - MONTH(a.tgl_pinjam)) + 1 > a.lama_angsuran
                            THEN a.jumlah - COALESCE((
                                SELECT SUM(jumlah_bayar) 
                                FROM tbl_pinjaman_d 
                                WHERE pinjam_id = a.id
                            ), 0)
                            ELSE 0
                        END
                    ELSE 0
                END as tag_harus_dibayar

            FROM tbl_pinjaman_h a
            WHERE a.status = '1'  -- Hanya pinjaman yang disetujui
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS v_hitung_pinjaman_detil");
    }
};
