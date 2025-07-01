<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tempo_pinjaman', function (Blueprint $table) {
            $table->increments('no_urut');
            $table->string('pinjam_id', 10);
            $table->string('no_ktp', 100);
            $table->date('tgl_pinjam');
            $table->date('tempo');
            $table->primary(['no_urut', 'pinjam_id', 'no_ktp']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tempo_pinjaman');
    }
}; 