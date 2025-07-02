<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_pinjaman_d', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->dateTime('tgl_bayar')->nullable();
            $table->string('pinjam_id', 300);
            $table->bigInteger('angsuran_ke')->nullable();
            $table->decimal('jumlah_bayar', 11, 2)->nullable();
            $table->decimal('bunga', 11, 2)->nullable();
            $table->decimal('denda_rp', 11, 2)->nullable();
            $table->decimal('biaya_adm', 11, 2)->nullable();
            $table->integer('terlambat')->nullable();
            $table->enum('ket_bayar', ['Angsuran','Pelunasan','Bayar Denda'])->default('Angsuran');
            $table->enum('dk', ['D','K'])->default('D');
            $table->bigInteger('kas_id')->default(1);
            $table->bigInteger('jns_trans')->default(48);
            $table->dateTime('update_data')->nullable();
            $table->string('user_name', 255)->nullable();
            $table->string('keterangan', 255)->nullable();
            $table->string('id_cabang', 8)->nullable();
            $table->index('kas_id');
            $table->index('pinjam_id');
            $table->index('jns_trans');
            $table->index('tgl_bayar');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_pinjaman_d');
    }
}; 