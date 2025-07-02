<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_setting', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('opsi_key', 255);
            $table->string('opsi_val', 255);
            $table->string('id_cabang', 8);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_setting');
    }
}; 