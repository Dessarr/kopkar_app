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
        // Temporarily disable strict mode to handle invalid datetime values
        DB::statement("SET sql_mode = ''");
        
        // Fix invalid datetime values
        DB::statement("UPDATE tbl_pinjaman_d SET update_data = NULL WHERE update_data = '0000-00-00 00:00:00'");
        
        // Now change the column type to BIGINT
        DB::statement('ALTER TABLE tbl_pinjaman_d MODIFY COLUMN jumlah_bayar BIGINT NOT NULL DEFAULT 0');
        
        // Re-enable strict mode
        DB::statement("SET sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert jumlah_bayar column back to INT
        DB::statement('ALTER TABLE tbl_pinjaman_d MODIFY COLUMN jumlah_bayar INT');
    }
};