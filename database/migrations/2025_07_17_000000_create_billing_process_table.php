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
        Schema::create('billing_process', function (Blueprint $table) {
            $table->id();
            $table->string('billing_code')->nullable();
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
            $table->enum('status', ['Y', 'N'])->default('Y');
            $table->string('status_bayar')->default('Lunas');
            $table->string('jns_trans')->nullable();
            $table->date('tgl_bayar')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_ref')->nullable();
            $table->unsignedBigInteger('processed_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_process');
    }
}; 