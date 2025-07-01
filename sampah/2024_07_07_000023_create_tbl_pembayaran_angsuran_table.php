<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_pembayaran_angsuran', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('jadwal_angsuran_id');
            $table->date('tgl_bayar');
            $table->double('jumlah_bayar');
            $table->string('keterangan', 255)->nullable();
            $table->timestamps();
            $table->index('jadwal_angsuran_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_pembayaran_angsuran');
    }
}; 