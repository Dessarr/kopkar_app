<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_anggota', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nama', 255);
            $table->string('identitas', 255);
            $table->enum('jk', ['L', 'P'])->nullable();
            $table->string('tmp_lahir', 225)->nullable();
            $table->date('tgl_lahir')->nullable();
            $table->string('status', 30)->nullable();
            $table->string('agama', 30)->nullable();
            $table->string('departement', 255)->nullable();
            $table->string('pekerjaan', 30)->nullable();
            $table->text('alamat')->nullable();
            $table->string('kota', 255)->nullable();
            $table->string('notelp', 12)->nullable();
            $table->date('tgl_daftar')->nullable();
            $table->integer('jabatan_id')->nullable();
            $table->enum('aktif', ['Y', 'N'])->nullable();
            $table->string('pass_word', 225)->nullable();
            $table->string('file_pic', 225)->nullable();
            $table->string('no_ktp', 300);
            $table->string('bank', 50)->nullable();
            $table->string('nama_pemilik_rekening', 150)->nullable();
            $table->string('no_rekening', 50)->nullable();
            $table->string('id_tagihan', 30)->nullable();
            $table->decimal('simpanan_wajib', 16, 2);
            $table->decimal('simpanan_sukarela', 16, 2);
            $table->decimal('simpanan_khusus_2', 16, 2);
            $table->string('id_cabang', 8)->nullable();
            $table->index('no_ktp');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_anggota');
    }
}; 