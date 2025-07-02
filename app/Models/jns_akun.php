<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class jns_akun extends Model
{
    // Nama tabel jika beda dari nama default (plural lowercase)
    protected $table = 'jns_akun';

    // Kolom-kolom yang bisa diisi (mass assignment)
    protected $fillable = [
        'kd_aktiva',
        'jns_trans',
        'akun',
        'laba_rugi',
        'pemasukan',
        'pengeluaran',
        'aktif',
    ];

    // Jika kolom 'id' adalah primary key (default), tidak perlu disebutkan lagi
    // Jika tidak auto-increment, tambahkan ini:
    // public $incrementing = false;

    // Jika kamu tidak pakai created_at dan updated_at
    public $timestamps = false;

}