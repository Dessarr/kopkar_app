<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_pinjaman', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('no_pinjam', 255);
            $table->unsignedBigInteger('anggota_id');
            $table->dateTime('tgl_input');
            $table->string('jenis', 255);
            $table->bigInteger('nominal');
            $table->integer('lama_ags');
            $table->double('bunga')->nullable();
            $table->double('angsuran_pokok')->nullable();
            $table->double('angsuran_bunga')->nullable();
            $table->double('total_angsuran')->nullable();
            $table->double('sisa_pinjaman')->nullable();
            $table->tinyInteger('status');
            $table->string('alasan', 255)->nullable();
            $table->date('tgl_cair')->nullable();
            $table->dateTime('tgl_update')->nullable();
            $table->string('id_cabang', 8)->nullable();
            $table->index('anggota_id', 'user_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_pinjaman');
    }
}; 