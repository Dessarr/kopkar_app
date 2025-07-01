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
        Schema::create('transaksi_kas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kas_id')->constrained('kas')->onDelete('cascade');
            $table->foreignId('dari_kas_id')->nullable()->constrained('kas')->onDelete('cascade');
            $table->foreignId('untuk_kas_id')->nullable()->constrained('kas')->onDelete('cascade');
            $table->enum('jenis_transaksi', ['masuk', 'keluar', 'transfer']);
            $table->decimal('jumlah', 15, 2);
            $table->text('keterangan')->nullable();
            $table->dateTime('tanggal_transaksi');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_kas');
    }
};
