<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_pinjaman_h', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('no_ktp', 300);
            $table->dateTime('tgl_pinjam');
            $table->string('anggota_id', 300)->nullable();
            $table->bigInteger('barang_id');
            $table->bigInteger('lama_angsuran');
            $table->decimal('jumlah_angsuran', 16, 2)->nullable();
            $table->decimal('jumlah', 16, 2)->nullable();
            $table->decimal('bunga', 16, 2)->nullable();
            $table->decimal('bunga_rp', 16, 2)->nullable();
            $table->integer('biaya_adm')->nullable();
            $table->enum('lunas', ['Belum','Lunas'])->nullable();
            $table->enum('dk', ['D','K'])->nullable();
            $table->bigInteger('kas_id')->nullable();
            $table->bigInteger('jns_trans')->nullable();
            $table->enum('status', ['0','1'])->nullable();
            $table->enum('jenis_pinjaman', ['1','2','3'])->nullable();
            $table->dateTime('update_data')->nullable();
            $table->string('user_name', 255)->nullable();
            $table->string('keterangan', 255)->nullable();
            $table->integer('id_cabang')->nullable();
            $table->index('anggota_id');
            $table->index('kas_id');
            $table->index('jns_trans');
            $table->index('barang_id');
            $table->index('no_ktp');
            $table->index('tgl_pinjam');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_pinjaman_h');
    }
}; 