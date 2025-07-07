<?php

namespace App\Models;

class View_LapLainLain extends View_Base
{
    protected $table = 'v_lap_lain_lain';
    
    protected $casts = [
        'tgl_transaksi' => 'date',
        'jumlah_bayar' => 'decimal:2',
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