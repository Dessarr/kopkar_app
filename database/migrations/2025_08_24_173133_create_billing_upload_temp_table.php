<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('billing_upload_temp', function (Blueprint $table) {
            $table->id();
            $table->date('tgl_transaksi');
            $table->string('no_ktp');
            $table->decimal('jumlah', 15, 2);
            $table->string('bulan');
            $table->string('tahun');
            $table->timestamps();

            $table->index(['bulan', 'tahun']);
            $table->index('no_ktp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_upload_temp');
    }
};
