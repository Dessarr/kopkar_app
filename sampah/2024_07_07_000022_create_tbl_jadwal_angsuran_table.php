<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_jadwal_angsuran', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('pinjaman_id');
            $table->date('tgl_jatuh_tempo');
            $table->double('jumlah_angsuran');
            $table->double('sisa_pinjaman');
            $table->enum('status', ['Lunas', 'Belum'])->default('Belum');
            $table->timestamps();
            $table->index('pinjaman_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_jadwal_angsuran');
    }
}; 