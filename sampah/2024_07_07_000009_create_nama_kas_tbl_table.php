<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nama_kas_tbl', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nama', 225);
            $table->enum('aktif', ['Y', 'T']);
            $table->enum('tmpl_simpan', ['Y', 'T']);
            $table->enum('tmpl_penarikan', ['Y', 'T']);
            $table->enum('tmpl_pinjaman', ['Y', 'T']);
            $table->enum('tmpl_bayar', ['Y', 'T']);
            $table->enum('tmpl_pemasukan', ['Y', 'T']);
            $table->enum('tmpl_pengeluaran', ['Y', 'T']);
            $table->enum('tmpl_transfer', ['Y', 'T']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nama_kas_tbl');
    }
}; 