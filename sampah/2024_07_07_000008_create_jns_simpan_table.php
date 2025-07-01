<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jns_simpan', function (Blueprint $table) {
            $table->increments('id');
            $table->string('jns_simpan', 30);
            $table->double('jumlah');
            $table->enum('tampil', ['Y', 'T']);
            $table->integer('urut', false, true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jns_simpan');
    }
}; 