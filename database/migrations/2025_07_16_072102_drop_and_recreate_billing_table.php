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
        // Drop the existing billing table
        Schema::dropIfExists('billing');

        // Create a new billing table with the correct structure
        Schema::create('billing', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->string('billing_code')->nullable(); // For custom billing codes if needed
            $table->unsignedBigInteger('id_transaksi')->nullable();
            $table->string('jns_transaksi')->nullable();
            $table->decimal('jumlah', 15, 2)->nullable();
            $table->string('keterangan')->nullable();
            $table->string('no_ktp');
            $table->string('nama');
            $table->string('bulan', 2);
            $table->string('tahun', 4);
            $table->string('bulan_tahun')->nullable();
            $table->string('id_anggota')->nullable();
            $table->decimal('simpanan_wajib', 15, 2)->nullable()->default(0);
            $table->decimal('simpanan_sukarela', 15, 2)->nullable()->default(0);
            $table->decimal('simpanan_khusus_2', 15, 2)->nullable()->default(0);
            $table->decimal('simpanan_pokok', 15, 2)->nullable()->default(0);
            $table->decimal('total_billing', 15, 2)->nullable()->default(0);
            $table->decimal('total_tagihan', 15, 2)->nullable()->default(0);
            $table->unsignedBigInteger('id_akun')->nullable();
            $table->enum('status', ['Y', 'N'])->default('N');
            $table->string('status_bayar')->default('belum');
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
