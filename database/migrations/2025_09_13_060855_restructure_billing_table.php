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
        Schema::table('billing', function (Blueprint $table) {
            // Ubah nama kolom billing_code menjadi kode_transaksi
            $table->renameColumn('billing_code', 'kode_transaksi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('billing', function (Blueprint $table) {
            // Kembalikan nama kolom
            $table->renameColumn('kode_transaksi', 'billing_code');
        });
    }
};
