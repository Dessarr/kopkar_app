<?php

namespace App\Models;

class View_SimpananBayar extends View_Base
{
    protected $table = 'v_simpanan_bayar';
    
    protected $casts = [
        'jumlah_bayar' => 'decimal:2'
    ];

    public function anggota()
    {
        return $this->belongsTo(data_anggota::class, 'no_ktp', 'no_ktp');
    }
} 