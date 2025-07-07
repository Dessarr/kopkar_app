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
        'untuk_kas_id',
        'no_polisi',
        'update_data',
        'id_cabang',
        'user_name',
    ];

    public function dariKas()
    {
         return $this->belongsTo(DataKas::class, 'dari_kas_id', 'id');
    }

    public function untukKas()
    {
        return $this->belongsTo(DataKas::class, 'untuk_kas_id', 'id');
    }
}