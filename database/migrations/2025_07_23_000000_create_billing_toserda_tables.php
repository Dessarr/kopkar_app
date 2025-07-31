<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tbl_billing_toserda', function (Blueprint $table) {
            $table->id();
            $table->string('no_ktp');
            $table->string('nama_anggota');
            $table->decimal('total_tagihan', 15, 2);
            $table->date('tanggal_billing');
            $table->enum('status_bayar', ['Belum Lunas', 'Lunas'])->default('Belum Lunas');
            $table->timestamps();
        });

        Schema::create('tbl_billing_processed_toserda', function (Blueprint $table) {
            $table->id();
            $table->foreignId('billing_id')->constrained('tbl_billing_toserda');
            $table->string('no_ktp');
            $table->string('nama_anggota');
            $table->decimal('total_tagihan', 15, 2);
            $table->date('tanggal_bayar');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tbl_billing_processed_toserda');
        Schema::dropIfExists('tbl_billing_toserda');
    }
};