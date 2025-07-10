<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::create('billing', function (Blueprint $table) {
        $table->string('id_billing')->primary();         // contoh: 01072501
        $table->string('bulan_tahun');                  // contoh: Juli 2025
        $table->string('id_anggota', 300);              // isinya no_ktp dari tbl_anggota
        $table->decimal('total_tagihan', 15, 2)->default(0);
        $table->enum('status', ['Y', 'N'])->default('N');
        $table->timestamps();

        // Ini hanya bisa berhasil kalau no_ktp di tbl_anggota UNIK
        $table->foreign('id_anggota')->references('no_ktp')->on('tbl_anggota')->onDelete('cascade');
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