<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tbl_trans_sp_bayar_temp', function (Blueprint $table) {
            $table->decimal('tagihan_simpanan_pokok', 15, 2)->default(0.00)->after('tagihan_simpanan_khusus_2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_trans_sp_bayar_temp', function (Blueprint $table) {
            $table->dropColumn('tagihan_simpanan_pokok');
        });
    }
};
