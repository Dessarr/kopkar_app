<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jns_dk', function (Blueprint $table) {
            $table->string('id', 3)->primary();
            $table->string('nama', 10);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jns_dk');
    }
}; 