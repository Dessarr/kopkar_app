<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class transaksi_kas extends Model
{
    protected $table = 'tbl_trans_kas';

    protected $fillable = [
        'id',
        'tgl_catat',
        'jumlah',
        'keterangan',
        'akun',
        'dari_kas_id',
        'user_name',
    ];
}