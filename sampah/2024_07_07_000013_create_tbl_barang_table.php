<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_barang', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nm_barang', 255);
            $table->string('type', 50);
            $table->string('merk', 50);
            $table->double('harga');
            $table->integer('jml_brg');
            $table->string('ket', 255);
            $table->string('id_cabang', 8)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_barang');
    }
}; 