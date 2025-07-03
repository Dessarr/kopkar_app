<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class data_kas extends Model
{
    protected $table = 'nama_kas_tbl';

    protected $fillable = [
        'nama',
        'aktif',
        'tmpl_simpan',
        'tmpl_penarikan',
        'tmpl_pinjaman',
        'tmpl_bayar',
        'tmpl_bayar',
        'tmpl_pemasukan',
        'tmpl_pengeluaran',
        'tmpl_transfer',
    ];

    
 

    public $timestamps = false;

}