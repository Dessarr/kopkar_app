<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_user', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('username', 255)->unique();
            $table->string('password', 255);
            $table->string('nama', 255);
            $table->enum('level', ['admin', 'member', 'superadmin']);
            $table->enum('aktif', ['Y', 'N'])->default('Y');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_user');
    }
}; 