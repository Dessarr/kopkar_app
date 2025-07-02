<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jns_pinjaman', function (Blueprint $table) {
            $table->integer('id', false, true)->primary();
            $table->string('opsi_key', 20);
            $table->string('pinjaman', 30);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jns_pinjaman');
    }
}; 