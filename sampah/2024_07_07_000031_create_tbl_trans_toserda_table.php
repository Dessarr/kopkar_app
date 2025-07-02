<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_trans_toserda', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->dateTime('tgl_transaksi')->nullable();
            $table->string('no_ktp', 300)->nullable();
            $table->unsignedBigInteger('anggota_id')->nullable();
            $table->integer('jenis_id')->nullable();
            $table->double('jumlah')->nullable();
            $table->string('keterangan', 255)->nullable();
            $table->enum('dk', ['D', 'K'])->nullable();
            $table->unsignedBigInteger('kas_id')->nullable();
            $table->dateTime('update_data')->nullable();
            $table->string('user_name', 255)->nullable();
            $table->timestamps();
            $table->index('anggota_id');
            $table->index('jenis_id');
            $table->index('kas_id');
            $table->index('no_ktp');
            $table->index('tgl_transaksi');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_trans_toserda');
    }
}; 