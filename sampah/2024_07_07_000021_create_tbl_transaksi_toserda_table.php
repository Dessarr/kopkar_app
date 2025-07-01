<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_transaksi_toserda', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->dateTime('tgl_transaksi');
            $table->string('keterangan', 255)->nullable();
            $table->double('jumlah');
            $table->string('id_toserda', 8);
            $table->string('id_cabang', 8)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_transaksi_toserda');
    }
}; 