<?php

namespace App\Models;

class View_LapDariKe extends View_Base
{
    protected $table = 'v_lap_dari_ke';
    
    protected $casts = [
        'tanggal' => 'date',
        'biasa' => 'decimal:2',
        'jasa_biasa' => 'decimal:2',
        'bank' => 'decimal:2',
        'jasa_bank' => 'decimal:2',
        'barang' => 'decimal:2',
        'jasa_barang' => 'decimal:2'
    ];

    public function anggota()
    {
        return $this->belongsTo(data_anggota::class, 'no_ktp', 'no_ktp');
    }
} 