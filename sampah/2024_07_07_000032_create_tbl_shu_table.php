<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_shu', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->dateTime('tgl_transaksi')->nullable();
            $table->string('no_ktp', 300)->nullable();
            $table->decimal('jumlah_bayar', 11, 2)->nullable();
            $table->integer('jns_trans');
            $table->enum('dk', ['D', 'K']);
            $table->bigInteger('kas_id');
            $table->dateTime('update_data')->nullable();
            $table->string('user_name', 255)->nullable();
            $table->index('kas_id');
            $table->index('no_ktp');
            $table->index('tgl_transaksi');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_shu');
    }
}; 