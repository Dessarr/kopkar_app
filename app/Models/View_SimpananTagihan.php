<?php

namespace App\Models;

class View_SimpananTagihan extends View_Base
{
    protected $table = 'v_simpanan_tagihan';
    
    protected $casts = [
        'jumlah_tagihan' => 'decimal:2'
    ];

    public function anggota()
    {
        return $this->belongsTo(data_anggota::class, 'no_ktp', 'no_ktp');
    }
} 