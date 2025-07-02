<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_pengajuan_penarikan', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('no_ajuan')->nullable();
            $table->string('ajuan_id', 255)->nullable();
            $table->unsignedBigInteger('anggota_id')->nullable();
            $table->dateTime('tgl_input')->nullable();
            $table->string('jenis', 255)->nullable();
            $table->bigInteger('nominal')->nullable();
            $table->integer('lama_ags')->nullable();
            $table->string('keterangan', 255)->nullable();
            $table->tinyInteger('status')->nullable();
            $table->string('alasan', 255)->nullable();
            $table->date('tgl_cair')->nullable();
            $table->dateTime('tgl_update')->nullable();
            $table->string('id_cabang', 8)->nullable();
            $table->timestamps();
            $table->index('anggota_id', 'anggota_id_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_pengajuan_penarikan');
    }
}; 