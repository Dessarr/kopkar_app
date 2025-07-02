<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_trans_tagihans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('anggota_id');
            $table->double('jumlah');
            $table->date('tgl_tagihan');
            $table->timestamps();
            $table->index('anggota_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_trans_tagihans');
    }
}; 