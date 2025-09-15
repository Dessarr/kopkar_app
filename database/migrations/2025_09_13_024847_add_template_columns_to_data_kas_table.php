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
        Schema::table('data_kas', function (Blueprint $table) {
            $table->char('tmpl_simpan', 1)->default('N')->after('aktif');
            $table->char('tmpl_penarikan', 1)->default('N')->after('tmpl_simpan');
            $table->char('tmpl_pinjaman', 1)->default('N')->after('tmpl_penarikan');
            $table->char('tmpl_bayar', 1)->default('N')->after('tmpl_pinjaman');
            $table->char('tmpl_pemasukan', 1)->default('N')->after('tmpl_bayar');
            $table->char('tmpl_pengeluaran', 1)->default('N')->after('tmpl_pemasukan');
            $table->char('tmpl_transfer', 1)->default('N')->after('tmpl_pengeluaran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('data_kas', function (Blueprint $table) {
            $table->dropColumn([
                'tmpl_simpan',
                'tmpl_penarikan', 
                'tmpl_pinjaman',
                'tmpl_bayar',
                'tmpl_pemasukan',
                'tmpl_pengeluaran',
                'tmpl_transfer'
            ]);
        });
    }
};