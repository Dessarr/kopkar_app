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
            $table->enum('status_bayar', ['Belum Lunas', 'Lunas'])->default('Belum Lunas')->after('id_cabang');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_anggota', function (Blueprint $table) {
            $table->dropColumn('status_bayar');
        });
    }
};
