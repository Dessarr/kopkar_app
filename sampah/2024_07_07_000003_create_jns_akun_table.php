<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jns_akun', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('kd_aktiva', 5)->nullable();
            $table->string('jns_trans', 50);
            $table->enum('akun', ['Aktiva', 'Pasiva'])->nullable();
            $table->enum('laba_rugi', ['', 'PENDAPATAN', 'BIAYA'])->default('');
            $table->enum('pemasukan', ['Y', 'N'])->nullable();
            $table->enum('pengeluaran', ['Y', 'N'])->nullable();
            $table->enum('aktif', ['Y', 'N']);
            $table->index('kd_aktiva');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jns_akun');
    }
}; 