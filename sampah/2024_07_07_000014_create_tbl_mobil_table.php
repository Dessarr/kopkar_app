<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_mobil', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nama', 255);
            $table->string('jenis', 100)->nullable();
            $table->string('merek', 225)->nullable();
            $table->string('pabrikan', 100)->nullable();
            $table->string('warna', 50)->nullable();
            $table->integer('tahun')->nullable();
            $table->string('no_polisi', 15)->nullable();
            $table->string('no_rangka', 50)->nullable();
            $table->string('no_mesin', 50)->nullable();
            $table->string('no_bpkb', 50)->nullable();
            $table->date('tgl_berlaku_stnk')->nullable();
            $table->string('file_pic', 100)->nullable();
            $table->enum('aktif', ['Y', 'N']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_mobil');
    }
}; 