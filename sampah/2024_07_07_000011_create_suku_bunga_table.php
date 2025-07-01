<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suku_bunga', function (Blueprint $table) {
            $table->increments('id');
            $table->string('opsi_key', 20);
            $table->string('opsi_val', 255)->nullable();
            $table->string('id_cabang', 8)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suku_bunga');
    }
}; 