<?php

namespace App\Models;

class View_SimpananGabung extends View_Base
{
    protected $table = 'v_simpanan_gabung';
    
    protected $casts = [
        'jumlah_bayar' => 'decimal:2',
        'jumlah_tagihan' => 'decimal:2',
        'jumlah_tagihan_toserda' => 'decimal:2',
        'tagihan_tak_terbayar' => 'decimal:2'
    ];

    public function anggota()
    {
        return $this->belongsTo(data_anggota::class, 'no_ktp', 'no_ktp');
    }
} 