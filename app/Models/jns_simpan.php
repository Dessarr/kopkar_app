<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class jns_simpan extends Model
{
    protected $table = 'jns_simpan';

    protected $fillable = [
        'jns_simpan',
        'jumlah',
        'tampil',
        'urut',
    ];

 

    public $timestamps = false;

}