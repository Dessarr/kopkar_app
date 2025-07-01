<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_transaksi_simpanan', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('anggota_id');
            $table->unsignedInteger('jns_simpan');
            $table->dateTime('tgl_transaksi');
            $table->double('jumlah');
            $table->string('keterangan', 255)->nullable();
            $table->string('id_cabang', 8)->nullable();
            $table->timestamps();
            $table->index('anggota_id', 'user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_transaksi_simpanan');
    }
}; 