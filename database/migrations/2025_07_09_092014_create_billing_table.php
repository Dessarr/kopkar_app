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
    public function up()
    {
        // Drop the table if it exists
        Schema::dropIfExists('billing');

        Schema::create('billing', function (Blueprint $table) {
            $table->string('id_billing')->primary();
            $table->string('bulan_tahun');
            $table->string('id_anggota');
            $table->string('no_ktp');
            $table->string('nama');
            $table->string('bulan', 2);
            $table->string('tahun', 4);
            $table->decimal('simpanan_wajib', 15, 2);
            $table->decimal('simpanan_sukarela', 15, 2);
            $table->decimal('simpanan_khusus_2', 15, 2);
            $table->decimal('simpanan_pokok', 15, 2);
            $table->decimal('total_billing', 15, 2);
            $table->decimal('total_tagihan', 15, 2);
            $table->unsignedBigInteger('id_akun')->nullable();
            $table->enum('status', ['Y', 'N'])->default('N');
            $table->string('status_bayar')->default('Belum Lunas');
            $table->string('jns_trans')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing');
    }
};