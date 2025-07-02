<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_pengajuan', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('no_ajuan');
            $table->string('ajuan_id', 255);
            $table->unsignedBigInteger('anggota_id');
            $table->dateTime('tgl_input');
            $table->string('jenis', 255);
            $table->bigInteger('nominal');
            $table->integer('lama_ags');
            $table->string('keterangan', 255);
            $table->tinyInteger('status');
            $table->string('alasan', 255);
            $table->date('tgl_cair');
            $table->dateTime('tgl_update');
            $table->string('id_cabang', 8);
            $table->index('anggota_id', 'user_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_pengajuan');
    }
}; 