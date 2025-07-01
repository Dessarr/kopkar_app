<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jns_bank', function (Blueprint $table) {
            $table->string('bank', 50)->primary();
            $table->dateTime('lastupdate');
            $table->string('user_id', 20);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jns_bank');
    }
}; 