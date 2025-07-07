<?php

namespace App\Models;

class View_LapBank extends View_Base
{
    protected $table = 'v_lap_bank';
    
    protected $casts = [
        'tgl_pinjam' => 'date',
        'jumlah' => 'decimal:2',
        'jasa' => 'decimal:2'
    ];

    public function anggota()
    {
        return $this->belongsTo(data_anggota::class, 'no_ktp', 'no_ktp');
    }
} 