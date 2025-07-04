<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jns_angsuran', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('ket');
            $table->enum('aktif', ['Y', 'T', ''])->default('');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jns_angsuran');
    }
}; 