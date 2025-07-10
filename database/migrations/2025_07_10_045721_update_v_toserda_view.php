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
        // Create or replace the view
        DB::statement('
            CREATE OR REPLACE VIEW v_toserda AS
            SELECT 
                t.id,
                t.tgl_transaksi,
                t.no_ktp,
                t.anggota_id,
                t.jenis_id,
                t.jumlah,
                t.keterangan,
                t.dk,
                t.kas_id,
                t.jns_trans,
                t.update_data,
                t.user_name,
                a.nama AS nama_anggota,
                k.nama AS nama_kas
            FROM 
                tbl_trans_toserda t
            LEFT JOIN 
                tbl_anggota a ON t.anggota_id = a.id
            LEFT JOIN 
                nama_kas_tbl k ON t.kas_id = k.id
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // In the down method, we'll recreate the view without the jns_trans field
        // But first check if the view exists
        try {
            DB::statement('
                CREATE OR REPLACE VIEW v_toserda AS
                SELECT 
                    t.id,
                    t.tgl_transaksi,
                    t.no_ktp,
                    t.anggota_id,
                    t.jenis_id,
                    t.jumlah,
                    t.keterangan,
                    t.dk,
                    t.kas_id,
                    t.update_data,
                    t.user_name,
                    a.nama AS nama_anggota,
                    k.nama AS nama_kas
                FROM 
                    tbl_trans_toserda t
                LEFT JOIN 
                    tbl_anggota a ON t.anggota_id = a.id
                LEFT JOIN 
                    nama_kas_tbl k ON t.kas_id = k.id
            ');
        } catch (\Exception $e) {
            // If there's an error, just log it - the view might not exist
            \Illuminate\Support\Facades\Log::error('Error reverting v_toserda view: ' . $e->getMessage());
        }
    }
};
