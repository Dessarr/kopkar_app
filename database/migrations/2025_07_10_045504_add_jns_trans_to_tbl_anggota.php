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
        Schema::table('tbl_anggota', function (Blueprint $table) {
            $table->string('jns_trans', 50)->nullable()->after('simpanan_khusus_2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_anggota', function (Blueprint $table) {
            $table->dropColumn('jns_trans');
        });
    }
};
