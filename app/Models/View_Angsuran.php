<?php

namespace App\Models;

class View_Angsuran extends View_Base
{
    protected $table = 'v_angsuran';
    
    protected $casts = [
        'tgl_pinjam' => 'date',
        'tgl_bayar' => 'date',
        'jumlah' => 'decimal:2',
        'bunga' => 'decimal:2',
        'pokok' => 'decimal:2',
        'bunga_pokok' => 'decimal:2'
    ];

    public function anggota()
    {
        return $this->belongsTo(data_anggota::class, 'no_ktp', 'no_ktp');
    }
} 