<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_notifikasi', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('judul', 255);
            $table->text('pesan');
            $table->enum('status', ['terbaca', 'belum'])->default('belum');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_notifikasi');
    }
}; 