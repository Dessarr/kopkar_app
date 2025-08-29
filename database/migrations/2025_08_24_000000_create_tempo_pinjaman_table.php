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
        Schema::create('tempo_pinjaman', function (Blueprint $table) {
            $table->id();
            $table->integer('no_urut')->comment('Urutan angsuran ke berapa');
            $table->string('pinjam_id', 50)->comment('ID pinjaman dari tbl_pinjaman_h');
            $table->string('no_ktp', 50)->comment('NIK anggota');
            $table->date('tgl_pinjam')->comment('Tanggal pinjaman');
            $table->date('tempo')->comment('Tanggal jatuh tempo angsuran');
            $table->timestamps();
            
            // Indexes
            $table->index(['pinjam_id', 'no_urut']);
            $table->index(['tempo']);
            $table->index(['pinjam_id', 'tempo']);
            
            // Foreign key constraint
            $table->foreign('pinjam_id')->references('id')->on('tbl_pinjaman_h')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tempo_pinjaman');
    }
};