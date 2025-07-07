<?php

namespace App\Models;

class View_Toserda extends View_Base
{
    protected $table = 'v_toserda';
    
    protected $casts = [
        'tgl_transaksi' => 'date',
        'jumlah' => 'decimal:2',
        'update_data' => 'datetime'
    ];

    public function anggota()
    {
        return $this->belongsTo(data_anggota::class, 'no_ktp', 'no_ktp');
    }

    public function kas()
    {
        return $this->belongsTo(DataKas::class, 'kas_id', 'id');
    }
} 